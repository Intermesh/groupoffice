<?php

namespace go\core;

use Exception;
use GO;
use go\core\App;
use go\core\auth\Password;
use go\core\auth\TemporaryState;
use go\core\cache\Disk;
use go\core\cache\None;
use go\core\db\Query;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\Environment;
use go\core\event\Listeners;
use go\core\jmap;
use go\core\model;
use go\core\model\Group;
use go\core\model\User;
use go\core\model\UserGroup;
use go\core\Module;
use go\core\orm\Entity;
use go\core\orm\Filters;
use go\core\util\ClassFinder;
use go\core\util\Lock;
use PDOException;
use go\modules\community\ldapauthenticator\Module as GoModule;
use go\core\model\Module as GoCoreModule;
use GO\Base\Db\ActiveRecord;
use go\core\orm\EntityType;
use go\core\model\Acl;
use go\core\orm\LoggingTrait;

class Installer {
	
	use event\EventEmitterTrait;
	
	const MIN_UPGRADABLE_VERSION = "6.3.58";
	
	const EVENT_UPGRADE = 'upgrade';

	private static $isInProgress = false;
	private static $isInstalling = false;
	private static $isUpgrading = false;

	/**
	 * Check if it's installing or upgrading
	 * 
	 * @return bool
	 */
	public static function isInProgress() {
		return static::isUpgrading() || static::isInstalling();
	}

	/**
	 * Check if it's installing
	 * 
	 * @return bool
	 */
	public static function isInstalling() {
		return self::$isInstalling || (basename(dirname($_SERVER['PHP_SELF'])) == 'install' && basename($_SERVER['PHP_SELF']) != 'upgrade.php');
	}

	/**
	 * Check if it's upgrading
	 * 
	 * @return bool
	 */
	public static function isUpgrading() {
		return self::$isUpgrading || basename($_SERVER['PHP_SELF']) == 'upgrade.php';
	}

	public function toggleGarbageCollection($enabled) {
		$job = model\CronJobSchedule::findByName("GarbageCollection", "core", "core");
		$job->enabled = $enabled;
		if(!$job->save()) {
			throw new \Exception("Could not toggle garbage collection job");
		}

	}

	/**
	 * 
	 * @param array $adminValues
	 * @param Module[] $installModules
	 * @throws Exception
	 */
	public function install(array $adminValues = [], $installModules = []) {

		ini_set("max_execution_time", 0);
		

		//don't cache on install
		App::get()->getCache()->flush(false);
		$cacheCls = get_class(App::get()->getCache());
		App::get()->setCache(new None());

		LoggingTrait::disable();

		self::$isInProgress = true;
		self::$isInstalling = true;

		ActiveRecord::$log_enabled = false;
		
		jmap\Entity::$trackChanges = false;

		$database = App::get()->getDatabase();

		if (count($database->getTables())) {
			throw new Exception("Database is not empty");
		}

		$database->setUtf8();

		Utils::runSQLFile(Environment::get()->getInstallFolder()->getFile("go/core/install/install.sql"));
		App::get()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=0;");
		
		$this->installGroups();

		$this->installAdminUser($adminValues);		

		$this->installCoreModule();
				
		$this->registerCoreEntities();		

		// Fix chicken / egg problem for acl->entityTypeId
		Group::check();
		GoCoreModule::check();

		$tempAuthState = new TemporaryState();
		$tempAuthState->setUserId(1);
		go()->setAuthState($tempAuthState);
		
		$this->installEmailTemplate();

		foreach ($installModules as $installModule) {
			$installModule->install();
		}

		App::get()->getSettings()->databaseVersion = App::get()->getVersion();
		App::get()->getSettings()->setDefaultGroups([Group::ID_INTERNAL]);
		App::get()->getSettings()->save();

		App::get()->setCache(new $cacheCls);
		Listeners::get()->init();

		//phpunit tests will use change tracking after install
		jmap\Entity::$trackChanges = true;
		LoggingTrait::enable();
		App::get()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=1;");
	}
	
	
	private function registerCoreEntities() {
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\core");

		$entities = $classFinder->findByParent(Entity::class);

		foreach ($entities as $entity) {
			if (!$entity::entityType()) {
				return false;
			}
		}

		//Allow people to read filters by default
		model\EntityFilter::entityType()->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
		//Allow people to read custom fieldsets by default
		model\FieldSet::entityType()->setDefaultAcl([Group::ID_EVERYONE => Acl::LEVEL_READ]);
	}
	
	private function installCoreModule() {

		$module = new model\Module();
		$module->name = 'core';
		$module->package = 'core';
		$module->version = App::get()->getUpdateCount();
		if(!$module->save()) {
			throw new \Exception("Could not save core module: " . var_export($module->getValidationErrors(), true));
		}

		//Share core with everyone
		$module->findAcl()->addGroup(Group::ID_EVERYONE)->save();
		
		$cron = new model\CronJobSchedule();
		$cron->moduleId = $module->id;
		$cron->name = "GarbageCollection";
		$cron->expression = "0 * * * *";
		$cron->description = "Garbage collection";
		
		if(!$cron->save()) {
			throw new Exception("Failed to save cron job: " . var_export($cron->getValidationErrors(), true));
		}
		
		$acl = model\Acl::findById(Group::entityType()->getDefaultAclId());
		$acl->addGroup(model\Group::ID_EVERYONE);
		if(!$acl->save()) {
			throw new \Exception("Could not save default ACL for groups");
		}
		
		if(!Password::register()) {
			throw new \Exception("Failed to register Password authenticator");
		}
	}
	
	private function installAdminUser($adminValues) {
		$admin = new User();
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
	}
	
	private function installGroups() {
		foreach (["Admins", "Everyone", "Internal"] as $groupName) {
			$group = new Group();
			$group->name = $groupName;
			if (!$group->save()) {
				throw new Exception("Could not create group");
			}
		}
	}
	
	private function installEmailTemplate() {
		$message = new \GO\Base\Mail\Message();
		$message->setHtmlAlternateBody('Hi<gotpl if="contact:firstName"> {contact:firstName},</gotpl><br />
<br />
{body}<br />
<br />
'.\go()->t("Best regards").'<br />
<br />
<br />
{user:displayName}<br />');
		
		$template = new \GO\Base\Model\Template();
		$template->setAttributes(array(
			'content' => $message->toString(),
			'name' => go()->t("Default"),
			'type' => \GO\Base\Model\Template::TYPE_EMAIL,
			'user_id' => 1
		));
		$template->save(true);
		$template->acl->addGroup(\GO::config()->group_internal);
	}


	public function isValidDb() {
		if (!go()->getDatabase()->hasTable("core_module")) {
			throw new \Exception("This is not a Group-Office 6.3+ database. Please upgrade to " . self::MIN_UPGRADABLE_VERSION . " first.");
		}

		if (version_compare(go()->getSettings()->databaseVersion, self::MIN_UPGRADABLE_VERSION) === -1) {
			throw new \Exception("Your version is " . go()->getSettings()->databaseVersion . ". Please upgrade to " . self::MIN_UPGRADABLE_VERSION . " first.");
		}

		$clientVersion = go()->getDbConnection()->getPDO()->getAttribute(\PDO::ATTR_CLIENT_VERSION);
		if (strpos($clientVersion, 'mysqlnd') === false) {
			throw new \Exception("PDO is not using the mysqlnd driver. Please make sure PDO uses mysqlnd. It's now using: " . $clientVersion);

		}
	}

	public function getUnavailableModules() {
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

				if (!class_exists($moduleCls)) {
					$unavailable[] = ["package" => $module['package'], "name" => $module['name']];
					continue;
				}

			} else
			{

				$moduleCls = "GO\\" . ucfirst($module['name']) . "\\" . ucfirst($module['name']) . "Module";

				// if (is_dir(__DIR__ . '/../go/modules/core/' . $module['name']) || is_dir(__DIR__ . '/../go/modules/community/' . $module['name'])) {
				// 	continue;
				// }

				if (!class_exists($moduleCls)) {
					$unavailable[] = ["package" => $module['package'], "name" => $module['name']];
					continue;
				}			
			}

			$mod = $moduleCls::get();

			if (!$mod->isAvailable()) {
				$unavailable[] = ["package" => $module['package'], "name" => $module['name']];
			}
		}
		
		return $unavailable;
		
	}

	public function disableUnavailableModules() {

		$unavailable = $this->getUnavailableModules();
		if(count($unavailable)) {

			$where = (new Query);
			foreach($unavailable as $m) {
				$where->orWhere($m);
			}
			$stmt = go()->getDbConnection()->update("core_module", ['enabled' => false], $where);
			$stmt->execute();
		}
	}

	public function upgrade() {
		self::$isInProgress = true;
		self::$isUpgrading = true;

		LoggingTrait::disable();

		go()->setAuthState((new TemporaryState())->setUserId(1));
		\GO::session()->runAsRoot();
		GO::$ignoreAclPermissions = true;
		

		$this->isValidDb();
		go()->getCache()->flush(false);
		\GO::clearCache(); //legacy framework
		go()->setCache(new None());
		
//		$unavailable = go()->getInstaller()->getUnavailableModules();
//		if(!empty($unavailable)) {
//			throw new \Exception("There are unavailable modules: " . var_export($unavailable, true));
//		}

		$this->disableUnavailableModules();

		$lock = new Lock("upgrade");
		if (!$lock->lock()) {
			throw new \Exception("Upgrade is already in progress");
		}
		
		ini_set("max_execution_time", 0);
		ini_set("memory_limit", -1);

		$this->toggleGarbageCollection(false);

		go()->getDbConnection()->query("SET sql_mode=''");
		
		jmap\Entity::$trackChanges = false;

		ActiveRecord::$log_enabled = false;
		
		go()->getDbConnection()->delete("core_entity", ['name' => 'GO\\Projects\\Model\\Project'])->execute();

		
		while (!$this->upgradeModules()) {
			echo "\n\nA module was refactored. Rerunning...\n\n";			
		}

		echo "Rebuilding cache\n";

		//reset new cache
		$cls = go()->getConfig()['core']['general']['cache'];
		go()->setCache(new $cls);

		go()->rebuildCache();
		App::get()->getSettings()->databaseVersion = App::get()->getVersion();
		App::get()->getSettings()->save();
		
		echo "Registering all entities\n";		
		$modules = model\Module::find()->where(['enabled' => true])->all();
		foreach($modules as $module) {
			if(isset($module->package) && $module->isAvailable()) {
				$module->module()->registerEntities();
			}
		}
	
		// Make sure core module is accessible for everyone
		$module  = GoCoreModule::findByName("core", "core");
		$acl = $module->findAcl();
		if(!$acl->hasGroup(Group::ID_EVERYONE)) {
			$acl->addGroup(Group::ID_EVERYONE);
			$acl->save();
		}

		$this->fireEvent(static::EVENT_UPGRADE);


		//phpunit tests will use change tracking after install
		jmap\Entity::$trackChanges = true;
		LoggingTrait::enable();

		$this->toggleGarbageCollection(true);
		echo "Done!\n";
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

	private function upgradeModules() {
		$u = [];

		$modules = model\Module::find()->all();		

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
		
		foreach ($u as $timestamp => $updateQuerySet) {

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
						go()->getCache()->flush(false);
										
						echo $modStr . "Running callable function\n";
						call_user_func($query);
					} else if (substr($query, 0, 7) == 'script:') {
						
						//upgrades may have modified tables so rebuild model and table cache
						Table::destroyInstances();
						go()->getCache()->flush(false);
						
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
							//var_dump($e);		
							$errorsOccurred = true;						

							if ($e->getCode() == 42000 || $e->getCode() == '42S21' || $e->getCode() == '42S01' || $e->getCode() == '42S22') {
								//duplicate and drop errors. Ignore those on updates
								
								go()->debug("IGNORING: ". $e->getMessage()." from query: ".$query);
								
							} else {

								echo $e->getCode() . ': '.$e->getMessage() . "\n";
								echo "Query: " . $query . "\n";
								echo "Package: " . ($module->package ?? "legacy") . "\n";
								echo "Module: " . $module->name . "\n";
								echo "Module installed version: " . $module->version . "\n";
								echo "Module source version: " . $counts[$moduleId] . "\n";
								
								die("ABORTING: Please contact support");
							}
						}
					}

					flush();

					echo ($module->package ?? "legacy") . "/" . $module->name . ' updated from ' . $module->version . ' to ' . $counts[$moduleId] . "\n";


					//$moduleModel = GO\Base\Model\Module::model()->findByName($module);
					//refetch module to see if package was updated
					if (!$module->package) {
						$module = model\Module::findById($moduleId);
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
						throw new \Exception("Failed to save module");
					}
					if($aModuleWasUpgradedToNewBackend) {
						return false;
					}
				}
			}
		}

		return true;//!$aModuleWasUpgradedToNewBackend;
	}

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
