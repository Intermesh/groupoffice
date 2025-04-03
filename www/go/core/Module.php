<?php

namespace go\core;

use Exception;
use Faker\Generator;
use GO\Base\Model\Module as LegacyModuleModel;
use GO\Base\Module as LegacyModule;
use go\core\model\Group;
use go\core\model\Module as GoModule;
use go\core\model\Permission;
use go\core\Module as CoreModule;
use go\core\orm\EntityType;
use go\core\orm\Query;
use go\core\acl\model\AclOwnerEntity;
use go\core\db\Utils;
use go\core\exception\NotFound;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\jmap\Entity;
use go\core\util\ClassFinder;
use go\modules\business\license\exception\LicenseException;
use go\modules\business\license\model\License;
use function GO;

/**
 * Base module class
 * 
 * Handles:
 * 
 * 1. Installation and uninstall of the module
 * 2. Registering Event listeners
 * 3. You can implement custom download methods prefixed with "download". For 
 *    example method go\modules\community\addressbook\Module::downloadVcard($contactId) 
 *    can be accessed with: "download.php?blob=community/addressbook/vcard/1"
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
abstract class Module extends Singleton {

	const STATUS_STABLE = "stable";
	const STATUS_BETA = "beta";
	const STATUS_DEPRECATED = "deprecated";

	/**
	 * Find module class file by name
	 * 
	 * @param string $moduleName
	 * @return ?self
	 */
	public static function findByName(string $moduleName): ?Module
	{
		$mods = self::findAvailable();
		
		foreach($mods as $mod) {
			if($mod::getName() == $moduleName) {
				return new $mod;
			}
		}
		
		return null;
	}

	/**
	 * The development status of this module
	 * @return string
	 */
	public function getStatus() : string{
		return self::STATUS_BETA;
	}


	/**
	 * Find available module class names
	 *
	 * @return class-string<self>[] eg. ['go\modules\community\addressbook\Module', 'go\modules\community\notes\Module']
	 */
	public static function findAvailable(): array
	{
		//for new framework
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\modules");

		return $classFinder->findByParent(CoreModule::class);
	}

	/**
	 * Returns if this module will be installed by default
	 *
	 * @return bool
	 */
	public function autoInstall(): bool
	{
		return false;
	}

	/**
	 * Check if this module can be installed.
	 *
	 * @return bool
	 */
	public function isInstallable(): bool
	{
		return $this->isLicensed();
	}

	/**
	 * For example "groupoffice-pro"
	 */
	public function requiredLicense(): ?string
	{
		return null;
	}

	/**
	 * Is this module licensed?
	 *
	 * @return bool
	 */
	public function isLicensed(): bool
	{
		
		$license = $this->requiredLicense();
		if(!isset($license)) {
			return true;
		}

		if(!go()->getEnvironment()->hasIoncube()) {
			return false;
		}

		return License::has($license);
		
	}

	/**
	 * Install the module
	 *
	 * @return model\Module|false;
	 * @throws Exception
	 */
	public final function install() {

		if(model\Module::findByName($this->getPackage(), $this->getName(), null)) {
			throw new Exception("This module has already been installed!");
		}

		$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
		try{

			$model = new model\Module();
			$model->name = static::getName();
			$model->package = static::getPackage();
			$model->version = $this->getUpdateCount();
			$model->checkDepencencies = false;

			if(!$this->beforeInstall($model)) {
				go()->warn(static::class .'::beforeInstall returned false');
				return false;
			}

			go()->getDbConnection()->pauseTransactions();

			try {

				self::installDependencies($this);
				$this->installDatabase();

			} catch(Exception $e) {
				ErrorHandler::logException($e);
				go()->getDbConnection()->resumeTransactions();
				$this->rollBack();
				return false;
			}

			go()->getDbConnection()->resumeTransactions();

			go()->getDbConnection()->beginTransaction();

			if(!$model->save()) {
				$this->rollBack();
				return false;
			}

			if(!$this->registerEntities()) {
				$this->rollBack();				
				return false;
			}

			if(!Installer::isInstalling()) {
				go()->rebuildCache();
			}

			if(!$this->afterInstall($model)) {
				go()->warn(static::class .'::afterInstall returned false');
				$this->rollBack();				
				return false;
			}		

			if(!go()->getDbConnection()->commit()) {
				$this->rollBack();
				$this->uninstallDatabase();
				return false;
			}		
		} catch(Exception $e) {			
			$this->rollBack();
			throw $e;
		} finally {
			if($oldFKchecks) {
				go()->getDbConnection()->foreignKeyChecks(true);
			}
		}
		
		return $model;
	}

	/**
	 * @throws Exception
	 */
	private function rollBack() {

		// Transaction is probably aborted by the install.sql file of the module. Any structure change will automatically abort the transaction.			
		if(go()->getDbConnection()->inTransaction()) {
			go()->getDbConnection()->rollBack();
		}
		try {
			$this->uninstallDatabase();
		}catch(Exception $e) {}
	}

	/**
	 * @throws Exception
	 */
	private function checkDependenciesForUninstall() {
		$dependentModuleNames = Module::getModulesThatDependOn($this);

		if (count($dependentModuleNames)>0) {
			throw new Exception(sprintf(\GO::t("You cannot delete the current module, because the following (installed) modules depend on it: %s."), implode(', ', $dependentModuleNames)));
		}
	}

	/**
	 * Uninstall the module
	 *
	 * @return bool
	 * @throws NotFound
	 * @throws Exception
	 */
	public function uninstall(): bool
	{
		$this->checkDependenciesForUninstall();

		$oldTC = \go\core\jmap\Entity::$trackChanges;
		\go\core\jmap\Entity::$trackChanges = false;
		$oldHist = \go\modules\community\history\Module::$enabled;
		\go\modules\community\history\Module::$enabled = false;

		try {
			$ret = $this->beforeUninstall();
		} finally {
			if(empty($ret)) {
				\go\core\jmap\Entity::$trackChanges = $oldTC;
				\go\modules\community\history\Module::$enabled = $oldHist;
			}
		}

		if(!$ret) {
			return false;
		}
		
		$this->uninstallDatabase();
		
		$model = model\Module::find()->where(['name' => static::getName(), 'package' => static::getPackage()])->single();
		if(!$model) {
			throw new NotFound("Module not found: ".  static::getPackage() . "/" . static::getName());
		}
		$model->enabled = false;
		
		if(!$model->save()) {
			return false;
		}

		if(!Installer::isInstalling()) {
			go()->rebuildCache();
		}

		foreach(EntityType::findAll((new Query)->where(['moduleId' => $model->id])) as $e) {
			if($e->getDefaultAclId()) {
				go()->getDbConnection()->update('core_entity',
					['defaultAclId' => null], ['id' => $e->getId()])
					->execute();
			}
		}


		if(!model\Module::delete(['name' => static::getName(), 'package' => static::getPackage()])) {
			return false;
		}	
		
		return true;
	}


	/**
	 * Registers all entity in the core_entity table. This happens after the
	 * core_module entry has been inserted.
	 *
	 * De-registration is not necessary when the module is uninstalled because they
	 * will be deleted by Mysql because of a cascading relation.
	 * @throws Exception
	 */
	public function registerEntities(): bool
	{
		$entities = $this->getClassFinder()->findByParent(Entity::class);

		if(static::class === App::class) {
			$arModels = $this->findLegacyModels();
			$entities = array_merge($entities, $arModels);
		}

		if(!count($entities)) {
			return true;
		}
		
		$moduleModel = $this->getModel(['id']);
		if(!$moduleModel) {
			throw new Exception("Module not installed " . static::class);
		}
		foreach($entities as $entity) {
			$type = $entity::entityType();
			if(!$type) {
				throw new Exception("Could not register entity type for module ". $this->getName() . " with name " . $entity::getClientName());
			}
			$typeModuleModel = $type->getModule(['id']);
			
			if(!$typeModuleModel) {
				throw new Exception("Could not register entity type for module ". $this->getName() . " with name " . $entity::getClientName() .' because existing type with ID = '.$type->getId().' had no module.' );
			}
			
			if($typeModuleModel->id != $moduleModel->id) {
				throw new Exception("Can't register entity '".$entity::getClientName()."' because it's already registered for module " . ($typeModuleModel->package ?? "legacy") . "/" .$typeModuleModel->name);
			}
		}		
		
		return true;
	}

	private function findLegacyModels():array {
//		$classFinder = new ClassFinder(false);
//		$classFinder->addNamespace("GO\\Base\\Model", Environment::get()->getInstallFolder()->getFolder("go/base/model"));
//		return array_filter($classFinder->findByParent(\GO\Base\Db\ActiveRecord::class), function($arCls) {
//			if($arCls == "GO\\Base\\Model\\Module") {
//				return false;
//			}
//
//			if(!$arCls::model()->hasLinks() && (!$arCls::model()->aclField() || $arCls::model()->isJoinedAclField)) {
//				return false;
//			}
//
//			return true;
//		});

		return ["GO\\Base\\Model\\Template"];

	}

	/**
	 * Installs the database for the module. This happens before the core_module entry has been inserted.
	 *
	 * @throws Exception
	 */
	private function installDatabase()
	{
		$sqlFile = $this->getFolder()->getFile('install/install.sql');
		
		if ($sqlFile->exists()) {
			Utils::runSQLFile($sqlFile);			
		}
	}

	/**
	 * This will delete the module's database tables
	 *
	 * @throws Exception
	 */
	private function uninstallDatabase()
	{
		$sqlFile = $this->getFolder()->getFile('install/uninstall.sql');
		
		if ($sqlFile->exists()) {
			//disable foreign keys
			$oldFKchecks = go()->getDbConnection()->foreignKeyChecks(false);
			try {
				Utils::runSQLFile($sqlFile);
			} finally {
				if($oldFKchecks) {
					go()->getDbConnection()->foreignKeyChecks(true);
				}
			}

		}
	}

	/**
	 * Override to implement installation routines after the database has been
	 * created. Share the module with group "Internal" for example.
	 *
	 * @param model\Module $model
	 * @return bool
	 */
	protected function afterInstall(model\Module $model): bool
	{
		return true;
	}

	/**
	 * Override to implement installation routines after the database has been
	 * created. Share the module with group "Internal" for example.
	 *
	 * @param model\Module $model
	 * @return bool
	 * @example
	 *
	 * protected function beforeInstall(GoModule $model): bool
	 * {
	 * // Share module with Internal group
	 * $model->permissions[Group::ID_INTERNAL] = (new Permission($model))
	 * ->setRights(['mayRead' => true]);
	 *
	 * return parent::beforeInstall($model);
	 * }
	 *
	 */
	protected function beforeInstall(model\Module $model): bool
	{
		return true;
	}
	
	/**
	 * Override to implement uninstallation routines before the database will be destroyed.
	 * @return bool
	 */
	protected function beforeUninstall(): bool
	{
		return true;
	}
	
	/**
	 * Get a class finder instance that only searches this module
	 * 
	 * @return ClassFinder
	 */
	public function getClassFinder(): ClassFinder
	{
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace(substr(static::class, 0, strrpos(static::class, "\\")));
		
		return $classFinder;
	}
	
	/**
	 * Get the updates.php file
	 * 
	 * @return File
	 */
	public function getUpdatesFile(): File
	{
		return $this->getFolder()->getFile('install/updates.php');
	}
	
	/**
	 * Counts the number of queries in the updates file
	 * 
	 * @return int
	 */
	public function getUpdateCount(): int
	{
		$updateFile = $this->getUpdatesFile();
		
		$count = 0;
		if($updateFile->exists()) {
			require($updateFile->getPath());
			
			if(isset($updates)){
				/** @noinspection PhpUnusedLocalVariableInspection */
				foreach($updates as $timestamp=> $queries)
					$count+=count($queries);
			}
		}
		
		return $count;			
	}

	/**
	 * Override to attach listeners
	 */
	public function defineListeners() {		
	}

	/**
	 * Get the author
	 * 
	 * @return string eg. "Intermesh BV <info@intermesh.nl>";
	 */
	abstract function getAuthor(): string;

	/**
	 * The names of the properties that can be set as permission. The value will be a label (to be translated by client)
	 * When this is not overriden there are no extra permissions. Groups van still be added
	 * @return array name => label
	 */
	public final function getRights(): array
	{
		$types = $this->rights();
		$result = [];
		foreach($types as $i => $name) {
			$result[$name] = pow(2, $i);
		}
		return $result;
	}

	/**
	 * Returns the flags that can be set for module rights
	 *
	 * For existing modules always add new types to the end for migration purposes Otherwise
	 * permissions will mix
	 *
	 * @return string[]
	 */
	protected function rights(): array
	{
		return ['mayManage'];
	}
	/**
	 * Get dependent modules.
	 * 
	 * @return array e.g. ["community/notes"]
	 */
	public function getDependencies(): array
	{
		return [];
	}

	/**
	 *
	 * @param static[]|LegacyModule $module
	 * @return static[]|LegacyModule[]
	 *
	 * @throws LicenseException
	 * @throws Exception
	 * @todo make non static when old framework modules are gone.
	 *
	 */
	public static function resolveDependencies($module): array
	{
		$resolved = [];
		foreach($module->getDependencies() as $dependency) {
			$d = explode("/",  $dependency);
			if(count($d) == 1) {
				array_unshift($d, "legacy");
			}

			if($d[0] == "legacy") {
				$cls = "GO\\" . $d[1] . "\\" . $d[1] . "Module";

			} else{
				$cls = "go\\modules\\" . $d[0] . "\\" . $d[1] . "\\Module";
			}

			if(!class_exists($cls)) {
				throw new Exception("Module $dependency is not available!");
			}
			$manager = new $cls;

			if(!$manager->isLicensed()) {
				throw new LicenseException("Module $dependency is not licensed!");
			}

			if(!in_array($manager, $resolved)) {
				$resolved[] = $manager;
			}
		}

		return $resolved;
	}

	/**
	 * Install modules that depend on the given module
	 * 
	 * @param static|LegacyModule $module
	 * @throws Exception
	 */
	public static function installDependencies($module) {
		foreach(self::resolveDependencies($module) as $dependency) {

			$installed = model\Module::findByName($dependency->getPackage(), $dependency->getName(), null);

			if (!$installed) {

				if($dependency instanceof self) {
					if (!$dependency->isInstallable() || !$dependency->install()) {
						throw new Exception("Could not install '" . get_class($dependency) . "'");
					}
				} else{
					if (!LegacyModuleModel::install($dependency->getName(), true)) {
						throw new Exception("Could not install '" . get_class($dependency) . "'");
					}
				}
			} else if (!$installed->enabled) {
				$installed->enabled = true;
				if (!$installed->save()) {
					throw new Exception("Could not enable '" . get_class($dependency) . "'");
				}
			}

		}
	}


	/**
	 * Find the modules that depend on the given module
	 *
	 * @param static|LegacyModule $module
	 * @return static[]|LegacyModule[]
	 */
	public static function getModulesThatDependOn($module): array
	{

		$depStr = $module->getPackage() . '/' . $module->getName();

		$installedModules = model\Module::find()->where(['enabled' => true]);

		$modules = [];

		foreach($installedModules as $installedModule) {

			$installedModuleManager = $installedModule->module();

			if(in_array($depStr, $installedModuleManager->getDependencies())) {
				$modules[] = $installedModuleManager;
			}
			if($module->getPackage() == 'legacy' && in_array($module->getName(), $installedModuleManager->getDependencies())){
				$modules[] = $installedModuleManager;
			}
		}

		return $modules;
	}

	/**
	 * get conflicting modules.
	 * 
	 * @return string[] eg. ["community/notes"]
	 */
	public function getConflicts(): array
	{
		return [];
	}

	/**
	 * 
	 * @deprecated
	 * @return string
	 */
	public function path(): string
	{
		return $this->getPath() . '/';
	}

	/**
	 * Get the filesystem path to the module
	 * 
	 * @return string
	 */
	public static function getPath(): string
	{
		return Environment::get()->getInstallFolder() . '/' . dirname(str_replace('\\', '/', static::class));
	}

	/**
	 * Get the folder of this module
	 *
	 * @return Folder
	 */
	public static function getFolder(): Folder
	{
		return new Folder(static::getPath());
	}
	
	/**
	 * 
	 * Get the name of this module
	 * 
	 * @return string
	 */
	public static function getName(): string
	{
		$parts = explode("\\", static::class);
		
		return $parts[3];
	}
	
	/**
	 * // backwards compatible 6.2
	 * 
	 * @deprecated since version number
	 * @return string
	 */
	public static function name(): string
	{
		return self::getName();
	}
	
	/**
	 * Get package name 
	 * 
	 * The package is a group of modules that belong to each other. It is used 
	 * to group modules per type or per customer.
	 * 
	 * @return string
	 */
	public static function getPackage(): string
	{
		$parts = explode("\\", static::class);		
		return $parts[2];
	}

	/**
	 * @return string
	 */
	public static function getLocalizedPackage()
	{
		return ucfirst(static::getPackage());
	}
	
	/**
	 * Get localized module title
	 * 
	 * @return string
	 */
	public static function getTitle(): string
	{
		
		$pkg = static::getPackage();
		$name = static::getName();
		
		if(!go()->getLanguage()->translationExists("name", $pkg, $name)) {
			return $name;
		}
		
		return go()->t("name", $pkg, $name);
	
	}
	
	
	/**
	 * Get localized module description
	 * 
	 * @return string
	 */
	public static function getDescription(): string
	{
		
		$pkg = static::getPackage();
		$name = static::getName();
		
		if(!go()->getLanguage()->translationExists("name", $pkg, $name)) {
			return "No description";
		}
		
		return go()->t("description", static::getPackage(), static::getName());		
	
	}
	
	/**
	 * Get icon URI
	 * 
	 * @return string
	 */
	public static function getIcon(): string
	{
		return go()->getAuthState()->getDownloadUrl('core/moduleIcon/'. static::getPackage() . '/' . static::getName().'&mtime='.go()->getSettings()->cacheClearedAt);
//		$icon = static::getFolder()->getFile('icon.png');
//
//		if(!$icon->exists()) {
//			$icon = Environment::get()->getInstallFolder()->getFile('views/Extjs3/themes/Paper/img/default-avatar.svg');
//		}
//
//		return 'data:'.$icon->getContentType().';base64,'. base64_encode($icon->getContents());
	}

	private $model;

	/**
	 * Get the module entity model
	 *
	 * @param array $props
	 * @return ?model\Module
	 */
	public function getModel(array $props = []): ?model\Module
	{
		if(!$this->model) {
			$this->model = model\Module::findByName($this->getPackage(), $this->getName(), null, $props);
		}

		return $this->model;
	}

	/**
	 * Check if the module has been installed
	 *
	 * @return bool
	 */
	public function isInstalled(bool $andEnabled = true): bool
	{
		$model = $this->getModel();

		if(!$andEnabled) {
			return !!$model;
		}else {
			return $model && $model->enabled;
		}
	}

	/**
	 * Check if this module is allowed via config.php and licensed.
	 *
	 * It does not check it's installed!
	 * 
	 * @return bool
	 */
	public function isAvailable(): bool
	{
		if(!self::isAllowed($this->getName(), $this->getPackage())) {
			return false;
		}

		return $this->isLicensed();
	}
	private static $allowedModules;

	/**
	 * Check if a given module is allowed to use by the config.php value "allowed_modules".
	 *
	 * @param string $name
	 * @param string|null $package
	 * @param string|array $allowedModules If not given the current configuration file is used.
	 * @return bool
	 */
	public static function isAllowed(string $name, string $package = null, $allowedModules = null): bool
	{

		if(!isset($allowedModules)) {
			if (!isset(self::$allowedModules)) {
				self::$allowedModules = self::normalizeAllowedModules(go()->getConfig()['allowed_modules']);
			}
			$allowedModules = self::$allowedModules;
		} else {
			$allowedModules = self::normalizeAllowedModules($allowedModules);
		}

		if (empty($allowedModules)) {
			return true;
		}

		if(isset($package) && $package != "legacy") {
			$name = $package . "/" . $name;
			return in_array($name, $allowedModules) || in_array($package . "/*", $allowedModules);
		} else{
			return in_array($name, $allowedModules) || in_array('legacy/' . $name, $allowedModules) || in_array(  "legacy/*", $allowedModules);
		}
	}

	/**
	 * @param $allowedModules
	 * @return string[]
	 */
	private static function normalizeAllowedModules($allowedModules) : array {
		if( empty($allowedModules)) {
			return [];
		}

		if(!is_array($allowedModules)) {
			$allowedModules = explode(',', $allowedModules);
		}

		$allowedModules[] = 'core/core';

		return $allowedModules;
	}
	
	/**
	 * Get the module settings
	 * 
	 * A module must override this function and implement a \go\core\Settings object
	 * to store settings.
	 * 
	 * @return Settings|SettingsEntity|null
	 */
	public function getSettings()
	{
		return null;
	}

	/**
	 * Check the module's data
	 */
	public function checkDatabase() {
		$entities = $this->getClassFinder()->findByParent(Entity::class);
		foreach($entities as $entity) {
			echo "Checking " . $entity . "\n";
			$entity::check();
			echo "Done\n";
		}
	}


	/**
	 * Check and fixes all enitties ACL's
	 * @see AclOwnerEntity::checkAcls()
	 * @return void
	 */
	public function checkAcls() {
		echo "Finding AclOwner entities";
		$entities = $this->getClassFinder()->findByParent(Entity::class);
		foreach($entities as $entity) {
			if($entity == model\Search::class) {
				continue;
			}

			echo "Checking " . $entity . "\n";
			$entity::checkAcls();
			echo "Done\n";
		}
	}

	public function __toString() {
		return static::getPackage() . '/' . static::getName();
	}

	/**
	 * Generate data for demo purposes
	 */
	public function demo(Generator $faker) {

	}
}
