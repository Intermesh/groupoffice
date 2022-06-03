<?php

namespace go\core;

use Exception;
use GO;
use GO\Base\Exception\AccessDenied;
use GO\Base\Mail\Message;
use GO\Base\Model\Template;
use go\core\auth\Password;
use go\core\auth\TemporaryState;
use go\core\cache\None;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\event\Listeners;
use go\core\fs\File;
use go\core\jmap;
use go\core\model;
use go\core\model\Group;
use go\core\model\User;
use go\core\orm\Entity;
use go\core\orm\Property;
use go\core\util\ClassFinder;
use go\core\util\Lock;
use PDO;
use PDOException;
use go\core\model\Module as GoCoreModule;
use GO\Base\Db\ActiveRecord;
use go\core\model\Acl;

class Installer {
	
	use event\EventEmitterTrait;
	
	const MIN_UPGRADABLE_VERSION = "6.5.94";
	
	const EVENT_UPGRADE = 'upgrade';

	private static $isInstalling = false;
	private static $isUpgrading = false;

	/**
	 * Check if it's installing or upgrading
	 * 
	 * @return bool
	 */
	public static function isInProgress(): bool
	{
		return static::isUpgrading() || static::isInstalling();
	}

	/**
	 * Check if it's installing
	 * 
	 * @return bool
	 */
	public static function isInstalling(): bool
	{
		return self::$isInstalling || (basename(dirname($_SERVER['PHP_SELF'])) == 'install' && basename($_SERVER['PHP_SELF']) != 'upgrade.php');
	}

	/**
	 * Check if it's upgrading
	 * 
	 * @return bool
	 */
	public static function isUpgrading(): bool
	{
		return self::$isUpgrading || basename($_SERVER['PHP_SELF']) == 'upgrade.php';
	}

	/**
	 * @throws Exception
	 */
	public function enableGarbageCollection() {
		$job = model\CronJobSchedule::findByName("GarbageCollection", "core", "core");
		if(!$job) {
			$this->createGarbageCollection();
		}

	}

	/**
	 * @throws Exception
	 */
	private function createGarbageCollection(): model\CronJobSchedule
	{

		$module = model\Module::findByName("core", "core");

		$cron = new model\CronJobSchedule();
		$cron->moduleId = $module->id;
		$cron->name = "GarbageCollection";
		$cron->expression = "0 0 * * *";
		$cron->description = "Garbage collection";

		if(!$cron->save()) {
			throw new Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}

		return $cron;
	}

	/**
	 * 
	 * @param array $adminValues
	 * @param Module[]|null $installModules
	 * @throws Exception
	 */
	public function install(array $adminValues = [], array $installModules = null) {

		ini_set("max_execution_time", 0);
		

		//don't cache on install
		go()->clearCache();
		$cacheCls = get_class(go()->getCache());
		go()->setCache(new None());


		self::$isInstalling = true;

		ActiveRecord::$log_enabled = false;
		
		jmap\Entity::$trackChanges = false;

		$database = go()->getDatabase();

		if (count($database->getTables())) {
			throw new Exception("Database is not empty");
		}

		$database->setUtf8();

		Utils::runSQLFile(Environment::get()->getInstallFolder()->getFile("go/core/install/install.sql"));
		go()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=0;");
		
		$this->installGroups();

		$admin = $this->installAdminUser($adminValues);

		$this->installCoreModule();
				
		$this->registerCoreEntities();		

		// Fix chicken / egg problem for acl->entityTypeId
		Group::check();
		GoCoreModule::check();

		$tempAuthState = new TemporaryState();
		$tempAuthState->setUserId(1);
		go()->setAuthState($tempAuthState);
		
		$this->installEmailTemplate();

		if(!isset($installModules)) {
			$installModules = $this->getAutoInstallModules();
		}

		foreach ($installModules as $installModule) {
			if(!$installModule->isInstalled()) {
				if(!$installModule->install()) {
					throw new Exception("Failed to install module " .get_class($installModule));
				}
			}
		}

		go()->getSettings()->systemEmail = $admin->email;
		go()->getSettings()->databaseVersion = go()->getVersion();
		go()->getSettings()->setDefaultGroups([Group::ID_INTERNAL]);
		go()->getSettings()->save();

		go()->setCache(new $cacheCls);
		go()->rebuildCache();

		//phpunit tests will use change tracking after install
		jmap\Entity::$trackChanges = true;
		go()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=1;");
		self::$isInstalling = false;
	}

	/**
	 * @return Module[]
	 */
	private function getAutoInstallModules(): array
	{
		$availableModules = Module::findAvailable();
		$installModules = [];
		foreach($availableModules as $modCls) {
			$mod = $modCls::get();
			if($mod->autoInstall() && $mod->isInstallable()) {
				$installModules[] = $mod;
			}
		}

		return $installModules;
	}


	/**
	 * @throws orm\exception\SaveException
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 */
	private function registerCoreEntities() {
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\core");

		$entities = $classFinder->findByParent(Entity::class);

		foreach ($entities as $entity) {
			if (!$entity::entityType()) {
				return;
			}
		}

		//Allow people to read filters by default
		model\EntityFilter::entityType()->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
		//Allow people to read custom fieldsets by default
		model\FieldSet::entityType()->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
		//groups readble to everyone
		Group::entityType()->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
	}

	/**
	 * @throws Exception
	 */
	private function installCoreModule() {

		$module = new model\Module();
		$module->name = 'core';
		$module->package = 'core';
		$module->version = go()->getUpdateCount();

		//Share core with everyone
		$module->permissions[Group::ID_EVERYONE] = (new model\Permission($module))
			->setRights(['mayRead' => true]);
		if(!$module->save()) {
			throw new Exception("Could not save core module: " . var_export($module->getValidationErrors(), true));
		}


		$this->createGarbageCollection();

		if(!Password::register()) {
			throw new Exception("Failed to register Password authenticator");
		}
	}

	/**
	 * @throws Exception
	 */
	private function installAdminUser($adminValues): User
	{
		$admin = new User();
		$admin->id = 1; //fixed ID for clustered setups
		$admin->displayName = "System administrator";
		$admin->username = "admin";
		$admin->email = "admin@localhost.localdomain";
		$admin->setPassword("admin");

		$admin->setValues($adminValues);

		if (!isset($admin->recoveryEmail)) {
			$admin->recoveryEmail = $admin->email;
		}

		$admin->groups[] = Group::ID_ADMINS;		

		if (!$admin->save()) {
			throw new Exception("Failed to create admin user: " . var_export($admin->getValidationErrors(), true));
		}

		return $admin;
	}

	/**
	 * @throws Exception
	 */
	private function installGroups() {
		$id = 1;
		foreach (["Admins", "Everyone", "Internal"] as $groupName) {
			$group = new Group();
			//fixed ID for cluster setups. See https://github.com/Intermesh/groupoffice/issues/742
			$group->id = $id++;
			$group->name = $groupName;
			if (!$group->save()) {
				throw new Exception("Could not create group: " . $group->getValidationErrorsAsString());
			}
		}
	}

	/**
	 * @throws AccessDenied
	 */
	private function installEmailTemplate() {
		$message = new Message();
		$message->setHtmlAlternateBody('Hi<gotpl if="contact:firstName"> {contact:firstName},</gotpl><br />
<br />
{body}<br />
<br />
'. go()->t("Best regards").'<br />
<br />
<br />
{user:displayName}<br />');
		
		$template = new Template();
		$template->setAttributes(array(
			'content' => $message->toString(),
			'name' => go()->t("Default"),
			'type' => Template::TYPE_EMAIL,
			'user_id' => 1
		));
		$template->save(true);
		/** @noinspection PhpUndefinedFieldInspection */
		$template->acl->addGroup(GO::config()->group_internal);
	}


	/**
	 * @throws Exception
	 */
	public function isValidDb() {
		if (!go()->getDatabase()->hasTable("core_module")) {
			throw new Exception("This is not a Group-Office 6.3+ database. Please upgrade to " . self::MIN_UPGRADABLE_VERSION . " first.");
		}

		if (version_compare(go()->getSettings()->databaseVersion, self::MIN_UPGRADABLE_VERSION) === -1) {
			throw new Exception("Your version is " . go()->getSettings()->databaseVersion . ". Please upgrade to " . self::MIN_UPGRADABLE_VERSION . " first.");
		}

		$clientVersion = go()->getDbConnection()->getPDO()->getAttribute(PDO::ATTR_CLIENT_VERSION);
		if (strpos($clientVersion, 'mysqlnd') === false) {
			throw new Exception("PDO is not using the mysqlnd driver. Please make sure PDO uses mysqlnd. It's now using: " . $clientVersion);

		}
	}

	private function removeObsoleteModules() {
		$stmt = go()->getDbConnection()->delete('core_module',
			['name' =>
				[
					'cron',
					'tools',
					'log',
					'calexceptiongrid',
					'calignoreuuid',
					'displaypermissions'
				],
				'package' => null
			]);

		$stmt->execute();

	}

	public function getUnavailableModules(): array
	{
		$this->removeObsoleteModules();

		$modules = (new Query)
						->select('name, package')
						->from('core_module')
						->where('enabled', '=', true)
						->all();


		$unavailable = [];
		foreach ($modules as $module) {

			//core modules from 6.3 are removed. Only core/core remains but is not at the usual location.
			if($module['package'] == "core") {
				continue;
			}

			if (isset($module['package']) && $module['package'] != 'legacy') {
				$moduleCls = "go\\modules\\" . $module['package'] . "\\" . $module['name'] . "\\Module";
			} else
			{
				$moduleCls = "GO\\" . ucfirst($module['name']) . "\\" . ucfirst($module['name']) . "Module";
			}
			if (!class_exists($moduleCls)) {
				$unavailable[] = ["package" => $module['package'], "name" => $module['name']];
				continue;
			}

			$mod = $moduleCls::get();

			if (!$mod->isAvailable()) {
				$unavailable[] = ["package" => $module['package'], "name" => $module['name']];
			}
		}
		
		return $unavailable;
		
	}

	/**
	 * Disable modules that are no longer available
	 *
	 * @return bool true if modules were disabled
	 * @throws Exception
	 */
	public function disableUnavailableModules(): bool
	{

		$unavailable = $this->getUnavailableModules();
		if(count($unavailable)) {

			$where = (new Query);
			foreach($unavailable as $m) {
				$where->orWhere($m);
			}
			$stmt = go()->getDbConnection()->update("core_module", ['enabled' => false], $where);
			$stmt->execute();

			return $stmt->rowCount() > 0;
		}

		return false;
	}

	/**
	 * @throws Exception
	 */
	private function initLogFile() {
		$logDir = go()->getDataFolder()->getFolder('log/upgrade/')->create();


		static::$logFile = $logDir->getFile(date('Ymd_His') . '.log');

		if(!static::$logFile->isWritable()){
			throw new Exception('Fatal error: Could not write to log file');
		}

		echo "Upgrade output will be logged into: " . static::$logFile->getRelativePath(go()->getDataFolder()) ."\n\n";
	}


	/**
	 * @var File
	 */
	private static $logFile;


	private static function upgradeLog($buffer)
	{
		self::$logFile->putContents($buffer, FILE_APPEND);
		return $buffer;
	}

	/**
	 * @throws Exception
	 */
	public function upgrade() {
		self::$isUpgrading = true;

		go()->setAuthState((new TemporaryState())->setUserId(1));
		GO::session()->runAsRoot();
		GO::$ignoreAclPermissions = true;
		

		$this->isValidDb();
		go()->getCache()->flush(true, false);
		GO::clearCache(); //legacy framework
		go()->setCache(new None());
		
//		$unavailable = go()->getInstaller()->getUnavailableModules();
//		if(!empty($unavailable)) {
//			throw new \Exception("There are unavailable modules: " . var_export($unavailable, true));
//		}

		$this->disableUnavailableModules();

		$lock = new Lock("upgrade", false);
		if (!$lock->lock()) {
			throw new Exception("Upgrade is already in progress");
		}

		$this->initLogFile();
		ob_start([static::class, 'upgradeLog'], 128);
		
		ini_set("max_execution_time", 0);
		ini_set("memory_limit", -1);

		//don't be strict in upgrade
		go()->getDbConnection()->exec("SET sql_mode=''");
		
		jmap\Entity::$trackChanges = false;

		ActiveRecord::$log_enabled = false;

		$database = go()->getDatabase();
		$database->setUtf8();
		
		go()->getDbConnection()->delete("core_entity", ['name' => 'GO\\Projects\\Model\\Project'])->execute();

		while (!$this->upgradeModules()) {
			echo "\n\nA module was refactored. Rerunning...\n\n";			
		}

		echo "Rebuilding cache\n";

		//reset new cache
		$cls = go()->getConfig()['cache'];
		go()->setCache(new $cls);


		go()->getSettings()->databaseVersion = go()->getVersion();
		go()->getSettings()->save();

		go()->rebuildCache();

		echo "Registering all entities\n";		
		$modules = model\Module::find(['id', 'name', 'package', 'version', 'enabled'])->where(['enabled' => true])->all();
		foreach($modules as $module) {
			if(isset($module->package) && $module->isAvailable()) {
				$module->module()->registerEntities();
			}
		}
	
		// Make sure core module is accessible for everyone
		$module  = GoCoreModule::findByName("core", "core");
		if(!isset($module->permissions[Group::ID_EVERYONE])) {
			$everyone = new model\Permission($module);
			$module->permissions[Group::ID_EVERYONE] = $everyone;
			$module->save();
		}
//		$acl = $module->findAcl();
//		if(!$acl->hasGroup(Group::ID_EVERYONE)) {
//			$acl->addGroup(Group::ID_EVERYONE);
//			$acl->save();
//		}

		$this->fireEvent(static::EVENT_UPGRADE);


		//phpunit tests will use change tracking after install
		jmap\Entity::$trackChanges = true;

		self::$isUpgrading = false;

		$this->enableGarbageCollection();
		echo "Done!\n";

		ob_flush();
		flush();

		ob_end_clean();
	}
	
	/**
	 * Use full for dev when you want to check what's going to happen.
	 * You can use this in /install/upgrade.php
	 */
	public function checkVersions() {
		$modules = model\Module::find()->all();

		/* @var $module model\Module */
		foreach ($modules as $module) {

			if (!$module->isAvailable()) {
				echo "Skipping module " . $module->name . " because it's not available.\n";
				continue;
			}
			
			$updatesFile = $this->getUpdatesFile($module);
			if(!$updatesFile) {
				continue;
			}
			
			$updates = array();
			require($updatesFile);			

			//put the updates in an extra array dimension so we know to which module
			//they belong too.
			$all = [];
			foreach ($updates as $updatequeries) {
				$all = array_merge($all, $updatequeries);
			}
			
			echo ($module->package ?? "legacy") . "/" . $module->name .': ' . $module->version . '/' .count($all);
			
			$next = $module->version + 1;
			if(isset($all[$next])) {
				echo ". Next: ".str_replace("\n", "\n\t\t", $all[$next]) ."\n";
			}
			
			echo "\n";
		}
	}
	
	private function getUpdatesFile(model\Module $module) {
		if ($module->package == null) {
			$root = go()->getEnvironment()->getInstallFolder();
			//old not refactored yet
			$file = $root->getFile('modules/' . $module->name . '/install/updates.php');
			if (!$file->exists()) {
				$file = $root->getFile('modules/' . $module->name . '/install/updates.inc.php');
			}
		} else {
			$file = $module->module()->getFolder()->getFile('install/updates.php');
		}	

		if (!$file->exists()) {
			return false;
		}
		
		return $file;
	}

	/**
	 * @throws Exception
	 */
	private function upgradeModules() {
		$u = [];

		$modules = model\Module::find(['id', 'name', 'package', 'version', 'enabled'])->all();

		$modulesById = [];
		/* @var $module model\Module */
		foreach ($modules as $module) {

			if (!$module->isAvailable()) {
				echo "Skipping module " . $module->name . " because it's not available.\n";
				continue;
			}

			$modulesById[$module->id] = $module;

			$updatesFile = $this->getUpdatesFile($module);
			if(!$updatesFile) {
				continue;
			}
			
			$updates = array();
			require($updatesFile);
			
			//put the updates in an extra array dimension so we know to which module
			//they belong too.
			foreach ($updates as $timestamp => $updatequeries) {
				//somehow this doesn't always match on some installations with Ioncube !?
			  if(go()->getDebugger()->enabled && !preg_match("/^[0-9]{12}$/", $timestamp)) {
			    throw new Exception("Invalid timestamp '$timestamp' in file '$updatesFile'");
        }
				$u["$timestamp"][$module->id] = $updatequeries;
			}
		}

		ksort($u);

		$counts = array();

		$aModuleWasUpgradedToNewBackend = false;		
		
		foreach ($u as $updateQuerySet) {

			foreach ($updateQuerySet as $moduleId => $queries) {

				//echo "Getting updates for ".$module."\n";
				$module = $modulesById[$moduleId];

				$modStr = '[' . ($module->package ?? "legacy") .'/'. $module->name .'] ';

				if (!is_array($queries)) {
					exit("Invalid queries in module: " . $module->name);
				}

				if (!isset($counts[$moduleId])) {
					$counts[$moduleId] = 0;
				}

				foreach ($queries as $query) {
					$counts[$moduleId] ++;
					if ($counts[$moduleId] <= $module->version) {
						continue;
					}

					if (is_callable($query)) {
						
						//upgrades may have modified tables so rebuild model and table cache
						Table::destroyInstances();
						go()->getCache()->flush(true, false);
										
						echo $modStr . "Running callable function\n";
						call_user_func($query);
					} else if (substr($query, 0, 7) == 'script:') {
						
						//upgrades may have modified tables so rebuild model and table cache
						Table::destroyInstances();
						go()->getCache()->flush(true, false);
						
						$root = go()->getEnvironment()->getInstallFolder();
						$updateScript = $root->getFile('modules/' . $module->name . '/install/updatescripts/' . substr($query, 7));
						
						if (!$updateScript->exists()) {	
							die($updateScript . ' not found!');
						}

						//if(!$quiet)
						echo $modStr . 'Running ' . $updateScript . "\n";
						call_user_func(function() use ($updateScript) {
							require_once($updateScript);
						});
					} else {
						echo $modStr . 'Excuting query: ' . $query . "\n";
						flush();
						try {
							if (!empty($query))
								go()->getDbConnection()->query($query);
						} catch (PDOException $e) {

							if (
								$e->getCode() == '23000' ||
								$e->getCode() == '42S21' || //duplicate col
								$e->getCode() == '42S01' || //table exists
								$e->getCode() == '42S22' || //col not found
								strstr($e->getMessage(), 'errno: 121 ') || // (errno: 121 "Duplicate key on write or update")
								strstr($e->getMessage(), ' 1826 ') || //HY000: SQLSTATE[HY000]: General error: 1826 Duplicate foreign key constraint
								strstr($e->getMessage(), ' 1091 ')  || //42000: SQLSTATE[42000]: Syntax error or access violation: 1091 Can't DROP 'type'; check that column/key exists
								strstr($e->getMessage(), ' 1022 ')  || //Integrity constraint violation: 1022 Can't write; duplicate key in table '#sql-509_19b'/
								strstr($e->getMessage(), ' 1061 ') ||  //  SQLSTATE[42000]: Syntax error or access violation: 1061 Duplicate key name
								strstr($e->getMessage(), ' 1068 ') //  1068 Multiple primary key defined
								) {

								//duplicate and drop errors. Ignore those on updates.
								echo "IGNORE: " . $e->getMessage() ."\n";

							} else {

								$msg = $e->getCode() . ': '.$e->getMessage() . "\n".
								  "Query: " . $query . "\n".
								  "Package: " . ($module->package ?? "legacy") . "\n".
								  "Module: " . $module->name . "\n".
								  "Module installed version: " . $module->version . "\n".
								  "Module source version: " . $counts[$moduleId] . "\n".
									"ABORTING: Please contact support";

								throw new Exception($msg);
							}
						}
					}

					flush();

					echo ($module->package ?? "legacy") . "/" . $module->name . ' updated from ' . $module->version . ' to ' . $counts[$moduleId] . "\n";


					//$moduleModel = GO\Base\Model\Module::model()->findByName($module);
					//refetch module to see if package was updated
					if (!$module->package) {
						$module = model\Module::findById($moduleId, ['id', 'name', 'package', 'version', 'enabled']);
						$newBackendUpgrade = $module->package != null;
						if ($newBackendUpgrade) {
							$module->version = $counts[$moduleId] = 0;
							$aModuleWasUpgradedToNewBackend = true;
							
						} else {
							$module->version = $counts[$moduleId];
						}
					} else {
						$module->version = $counts[$moduleId];
					}

					//exit();

					if (!$module->save()) {
						throw new Exception("Failed to save module");
					}
					if($aModuleWasUpgradedToNewBackend) {
						return false;
					}
				}
			}
		}

		return true;//!$aModuleWasUpgradedToNewBackend;
	}

//	private function setDefaultCollation() {
//		$sql = "ALTER DATABASE `".go()->getConfig()['db_name']."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
//		go()->getDbConnection()->exec($sql);
//	}

	public static function fixCollations() {
		go()->getDbConnection()->exec("SET foreign_key_checks = 0");
		$stmt = go()->getDbConnection()->query("SHOW TABLE STATUS");	
		
		foreach($stmt as $record){

			if(!isset($record['Engine'])) {
				//Skip views.
				continue;
			}
			
			if($record['Engine'] != 'InnoDB' && $record["Name"] != 'fs_filesearch' && $record["Name"] != 'cms_files') {
				echo "Converting ". $record["Name"] . " to InnoDB\n";
				flush();
				$sql = "ALTER TABLE `".$record["Name"]."` ENGINE=InnoDB;";
				go()->getDbConnection()->query($sql);	
			}
			
			if($record["Collation"] != "utf8mb4_unicode_ci" ) {
				echo "Converting ". $record["Name"] . " to utf8mb4\n";
				flush();

				if($record['Name'] == 'fs_files') {
					go()->getDbConnection()->exec("ALTER TABLE `fs_files` DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;");
					go()->getDbConnection()->query("ALTER TABLE `fs_files` CHANGE `name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
					go()->getDbConnection()->query("ALTER TABLE `fs_files` CHANGE `comment` `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
					go()->getDbConnection()->query("ALTER TABLE `fs_files` CHANGE `extension` `extension` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;");
					go()->getDbConnection()->query("ALTER TABLE `fs_files` CHANGE `random_code` `random_code` CHAR(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");

					continue;
				}

				if($record['Name'] == 'fs_folders') {
					go()->getDbConnection()->exec("ALTER TABLE `fs_folders` DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;");					
					go()->getDbConnection()->query("ALTER TABLE `fs_folders` CHANGE `name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;");
					go()->getDbConnection()->query("ALTER TABLE `fs_folders` CHANGE `comment` `comment` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
					go()->getDbConnection()->query("ALTER TABLE `fs_folders` CHANGE `cm_state` `cm_state` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;");
					
					continue;
				}
				
				if($record['Name'] === 'em_links') {
					go()->getDbConnection()->query("ALTER TABLE `em_links` DROP INDEX `uid`");
				}			
				$sql = "ALTER TABLE `".$record["Name"]."` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
				go()->getDbConnection()->query($sql);	
				
				if($record['Name'] === 'em_links') {
					go()->getDbConnection()->query("ALTER TABLE `em_links` CHANGE `uid` `uid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';");
					go()->getDbConnection()->query("ALTER TABLE `em_links` ADD INDEX(`uid`);");
				}
			}	
		}
		go()->getDbConnection()->exec("SET foreign_key_checks = 1");
	}

}
