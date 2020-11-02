<?php

namespace go\core;

use Exception;
use go\core\db\Utils;
use go\core\exception\NotFound;
use go\core\fs\File;
use go\core\fs\Folder;
use go\core\model;
use go\core\jmap\Entity;
use go\core\util\ClassFinder;
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

	/**
	 * Find module class file by name
	 * 
	 * @param string $moduleName
	 * @return self|false
	 */
	public static function findByName($moduleName) {
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace("go\\modules");
		$mods = $classFinder->findByParent(self::class);
		
		foreach($mods as $mod) {
			if($mod::getName() == $moduleName) {
				return new $mod;
			}
		}
		
		return false;
	}

	public function isInstallable() {
		return $this->isLicensed();
	}

	/**
	 * For example "groupoffice-pro"
	 */
	public function requiredLicense(){
		return null;
	}

	public function isLicensed() {
		
		$lic = $this->requiredLicense();
		if(!isset($lic)) {
			return true;
		}

		$file = go()->getEnvironment()->getInstallFolder()->getFile('licensechecks/'.$lic. '.php');

		//Check if file is encoded
		$data = $file->getContents(0, 100);
		if(strpos($data, '<?php //004fb') === false) {	
			return true;
		}

		if(!extension_loaded('ionCube Loader')) {
			return false;
		}

		if(!go()->getEnvironment()->getInstallFolder()->getFile($lic . '-' . substr(go()->getVersion(), 0, 3) .'-license.txt')->exists()) {
			return false;
		}

		return require($file->getPath());
		
	}


	/**
	 * Install the module
	 *
	 * @return model\Module|false;
	 * @throws Exception
	 */
	public final function install() {

		try{
			go()->getDbConnection()->pauseTransactions();
			$this->installDatabase();
			go()->getDbConnection()->resumeTransactions();
					
			go()->rebuildCache(true);

			go()->getDbConnection()->beginTransaction();
		
			$model = new model\Module();
			$model->name = static::getName();
			$model->package = static::getPackage();
			$model->version = $this->getUpdateCount();

			if(!$model->save()) {
				$this->rollBack();
				return false;
			}

			if(!$this->registerEntities()) {
				$this->rollBack();				
				return false;
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
	 * Uninstall the module
	 *
	 * @return bool
	 * @throws NotFound
	 * @throws Exception
	 */
	public function uninstall() {
		
		if(!$this->beforeUninstall()) {
			return false;
		}
		
		if(!$this->uninstallDatabase()) {
			return false;
		}
		
		$model = model\Module::find()->where(['name' => static::getName(), 'package' => static::getPackage()])->single();
		if(!$model) {
			throw new NotFound("Module not found: ". static::getName() . "/" . static::getPackage());
		}
		$model->enabled = false;
		
		if(!$model->save()) {
			return false;
		}
		
		go()->rebuildCache(true);

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
	public function registerEntities() {
		$entities = $this->getClassFinder()->findByParent(Entity::class);
		if(!count($entities)) {
			return true;
		}
		
		$moduleModel = $this->getModel();
		foreach($entities as $entity) {
			$type = $entity::entityType();
			if(!$type) {
				throw new Exception("Could not register entity type for module ". $this->getName() . " with name " . $entity::getClientName());
			}
			$typeModuleModel = $type->getModule();
			
			if(!$typeModuleModel) {
				throw new Exception("Could not register entity type for module ". $this->getName() . " with name " . $entity::getClientName() .' because existing type with ID = '.$type->getId().' had no module.' );
			}
			
			if($typeModuleModel->id != $moduleModel->id) {
				throw new Exception("Can't register entity '".$entity::getClientName()."' because it's already registered for module " . ($typeModuleModel->package ?? "legacy") . "/" .$typeModuleModel->name);
			}
		}		
		
		return true;
	}

	/**
	 * Installs the database for the module. This happens before the core_module entry has been inserted.
	 * @return boolean
	 * @throws Exception
	 */
	private function installDatabase() {
		$sqlFile = $this->getFolder()->getFile('install/install.sql');
		
		if ($sqlFile->exists()) {
			Utils::runSQLFile($sqlFile);			
		}
				
		return true;
	}

	/**
	 * This will delete the module's database tables
	 *
	 * @return boolean
	 * @throws Exception
	 */
	private function uninstallDatabase() {
		$sqlFile = $this->getFolder()->getFile('install/uninstall.sql');
		
		if ($sqlFile->exists()) {
			//disable foreign keys
			go()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=0;");
			Utils::runSQLFile($sqlFile);
			go()->getDbConnection()->exec("SET FOREIGN_KEY_CHECKS=1;");
		}
		
		return true;
	}

	/**
	 * Override to implement installation routines after the database has been
	 * created. Share the module with group "Internal" for example.
	 *
	 * @param model\Module $model
	 * @return bool
	 */
	protected function afterInstall(model\Module $model) {
		return true;
	}
	
	/**
	 * Override to implement uninstallation routines before the database will be destroyed.
	 * @return bool
	 */
	protected function beforeUninstall() {
		return true;
	}
	
	/**
	 * Get a class finder instance that only searches this module
	 * 
	 * @return ClassFinder
	 */
	public function getClassFinder() {
		$classFinder = new ClassFinder(false);
		$classFinder->addNamespace(substr(static::class, 0, strrpos(static::class, "\\")));
		
		return $classFinder;
	}
	
	/**
	 * Get the updates.php file
	 * 
	 * @return File
	 */
	public function getUpdatesFile() {
		return $this->getFolder()->getFile('install/updates.php');
	}
	
	/**
	 * Counts the number of queries in the updates file
	 * 
	 * @return int
	 */
	public function getUpdateCount() {
		$updateFile = $this->getUpdatesFile();
		
		$count = 0;
		if($updateFile->exists()) {
			require($updateFile->getPath());
			
			if(isset($updates)){
				foreach($updates as $timestamp=>$queries)
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
	abstract function getAuthor();

	/**
	 * Get dependent modules.
	 * 
	 * @return string[] eg. ["community/notes"]
	 */
	public function getDependencies() {
		return [];
	}

	/**
	 *
	 * @todo make non static when old framework modules are gone.
	 *
	 * @param static|GO\Base\Module $module
	 * @return static|GO\Base\Module[]
	 *
	 */
	public static function resolveDependencies($module) {
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
				throw new Exception("Module $dependency is not licensed!");
			}

			if(!in_array($manager, $resolved)) {
				$resolved[] = $manager;
			}
		}

		return $resolved;
	}

	/**
	 * @param static|GO\Base\Module $module

	 */
	public static function installDependencies($module) {
		foreach(self::resolveDependencies($module) as $dependency) {

			$installed = model\Module::findByName($dependency->getPackage(), $dependency->getName(), null);

			if (!$installed) {

				if($dependency instanceof self) {
					if (!$dependency->install()) {
						throw new Exception("Could not install '" . get_class($dependency) . "'");
					}
				} else{
					if (!\GO\Base\Model\Module::install($dependency->getName(), true)) {
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
	 * @param static|GO\Base\Module $module
	 * @return static|GO\Base\Module[]
	 */
	public static function getModulesThatDependOn($module) {

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
	public function getConflicts() {
		return [];
	}

	/**
	 * 
	 * @deprecated
	 * @return string
	 */
	public function path() {
		return $this->getPath() . '/';
	}

	/**
	 * Get the filesystem path to the module
	 * 
	 * @return string
	 */
	public static function getPath() {
		return Environment::get()->getInstallFolder() . '/' . dirname(str_replace('\\', '/', static::class));
	}

	/**
	 * Get the folder of this module
	 *
	 * @return Folder
	 * @throws Exception
	 */
	public static function getFolder() {
		return new Folder(static::getPath());
	}
	
	/**
	 * 
	 * Get the name of this module
	 * 
	 * @return string
	 */
	public static function getName() {
		$parts = explode("\\", static::class);
		
		return $parts[3];
	}
	
	/**
	 * // backwards compatible 6.2
	 * 
	 * @deprecated since version number
	 * @return string
	 */
	public static function name() {
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
	public static function getPackage() {
		$parts = explode("\\", static::class);		
		return $parts[2];
	}
	
	/**
	 * Get localized module title
	 * 
	 * @return string
	 */
	public static function getTitle() {
		
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
	public static function getDescription() {
		
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
	public static function getIcon() {
		$icon = static::getFolder()->getFile('icon.png');
		
		if(!$icon->exists()) {
			$icon = Environment::get()->getInstallFolder()->getFile('views/Extjs3/themes/Paper/img/default-avatar.svg');
		}
		
		return 'data:'.$icon->getContentType().';base64,'. base64_encode($icon->getContents());
	}

	private $model;
	
	/**
	 * Get the module entity model
	 * 
	 * @return model\Module
	 */
	public function getModel() {

		if(!$this->model) {
			$this->model = model\Module::findByName($this->getPackage(), $this->getName());
		}

		return $this->model;
	}

	/**
	 * Check if this module is installed, available and licensed
	 * 
	 * @return bool
	 */
	public function isAvailable() {

		$model = $this->getModel();
		if(!$model) {
			return false;
		}

		return $model->isAvailable();
	}
	
	/**
	 * Get the module settings
	 * 
	 * A module must override this function and implement a \go\core\Settings object
	 * to store settings.
	 * 
	 * @return Settings
	 */
	public function getSettings() {
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

	public function __toString() {
		return static::getPackage() . '/' . static::getName();
	}

}
