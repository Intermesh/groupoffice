<?php
namespace go\core\model;

use Exception;
use go\core;
use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\App;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\Settings;
use go\core\validate\ErrorCode;
use stdClass;

class Module extends Entity {
	public $id;
	public $name;
	public $package;
	public $sort_order;
	public $admin_menu;
	public $version;
	public $enabled;
	public $permissions = [];

	// for backwards compatibility
	public function getPermissionLevel($userId = null): int
	{

		$rights = $this->getUserRights($userId);

		if($this->name == 'projects2' && $rights->mayFinance && !$rights->mayManage) { // a single exception for this compat method
			return 45;
		}

		return !empty($rights->mayManage) ? 50 : 10;
	}

	protected function canCreate(): bool
	{
		return go()->getAuthState()->isAdmin();
	}
	
	protected static function textFilterColumns(): array
	{
		return ['name', 'package'];
	}

	public $checkDepencencies = true;
	
	protected function internalSave(): bool
	{

		if($this->isModified(['enabled']) || $this->isNew()) {

			//set cache
			self::$modulesByName[$this->package .'/'.$this->name] = $this;

			if($this->enabled) {
				if($this->checkDepencencies) {
					core\Module::installDependencies($this->module());
				}
				self::$modulesByName[$this->package.'/'.$this->name] = $this;
			}else
			{
				unset(self::$modulesByName[$this->package.'/'.$this->name]);

				if ($this->checkDepencencies) {
					$mods = core\Module::getModulesThatDependOn($this->module());
					if (!empty($mods)) {
						$this->setValidationError('name', ErrorCode::DEPENDENCY_NOT_SATISFIED, sprintf(\GO::t("You cannot delete the current module, because the following (installed) modules depend on it: %s."), implode(', ', $mods)));

						return false;
					}
				}
			}
		}
		
		if($this->isNew() || $this->sort_order < 1) {
			$this->sort_order = $this->nextSortOrder();			
		}
		
		if(!parent::internalSave()) {
			return false;
		}
		go()->getCache()->set('module-' . $this->package.'/'.$this->name, $this);
		
		$settings = $this->getSettings();
		if($settings && !$settings->save()) {
			return false;
		}

		if($this->isModified(['enabled'])) {
			go()->rebuildCache();
		}

		// TODO: do groups needs modules or can we set multiple module with new group permissions
		//When module groups change the groups change too. Because the have a "modules" property.
//		$aclChanges = $this->getAclChanges();
//		if(!empty($aclChanges)) {
//			Group::entityType()
//				->changes(
//					go()->getDbConnection()
//						->select('id as entityId, aclId, "0" as destroyed')
//						->from('core_group')
//						->where('id', 'IN', array_keys($aclChanges)
//					)
//				);
//		}
		
		return true;
	}

	
	private function nextSortOrder() {
		$query = new Query();			
		$query->from("core_module");

		if($this->package == "core") {
			return 0;
		} else
		{
			$query->selectSingleValue("COALESCE(MAX(sort_order), 100) + 1")
				->where('package', '!=', "core");
		}

		return $query->single();
	}
	

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('core_module', 'm')
			->addMap('permissions', Permission::class, ['id'=>'moduleId']);
	}

	private function adminRights() {
		$rights = ["mayRead" => true];
		foreach($this->module()->getRights() as $name => $bit){
			$rights[$name] = true;
		}
		return (object) $rights;
	}

	/**
	 * Get's the rights of a user
	 *
	 * @param int|null $userId The user ID to query. defaults to current authorized user.
	 * @return stdClass For example ['mayRead' => true, 'mayManage'=> true, 'mayHaveSuperCowPowers' => true]
	 */
	public function getUserRights(int $userId = null) : stdClass
	{

		if(!isset($userId)) {
			$userId = go()->getAuthState()->getUserId();
			$isAdmin = go()->getAuthState()->isAdmin();
		} else{
			$isAdmin = User::isAdminById($userId);

		}

		if(!$this->isAvailable()) {
			return (object) ['mayRead' => $isAdmin];
		}

		if($isAdmin) {
			return $this->adminRights();
		}

		return $this->userRights($userId);
	}

	private function userRights($userId) {
		$r = go()->getDbConnection()->selectSingleValue("MAX(rights)")
			->from("core_permission")
			->where('moduleId', '=', $this->id)
			->where("groupId", "IN",
				go()->getDbConnection()
					->select("groupId")
					->from("core_user_group")
					->where(['userId' => $userId])
			)->single();

		if($r === null) {
			$rights = ["mayRead" => false];
			foreach($this->module()->getRights() as $name => $bit){
				$rights[$name] = false;
			}
			return (object) $rights;
		}

		$r = decbin($r);

		$rights = ["mayRead" => true];

		foreach ($this->module()->getRights() as $name => $bit) {
			$rights[$name] = !!($r & $bit);
		}

		return (object) $rights;
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()->add("enabled", function(Criteria $criteria, $value) {
			if($value === null) {
				return;
			}
			$criteria->andWhere('enabled', '=', (bool) $value);
		});
	}


	private $module;

	
	/**
	 * Get the module base file object
	 * 
	 * @return core\Module
	 */
	public function module() {
		if($this->package == "core" && $this->name == "core") {
			return App::get();
		}
		
		if(!isset($this->module)) {
			$cls = $this->getModuleClass();
			/** @var \go\core\Module $cls */
			$this->module = $cls::get();
		}
		
		return $this->module;
	}	
	
	private function getModuleClass() {
		if(!isset($this->package)) {
			//legacy module
			return "GO\\" . $this->name ."\\" . $this->name ."Module";
		}
		return "\\go\\modules\\" . $this->package ."\\" . $this->name ."\\Module";
	}

	/**
	 * Get the folder of the module
	 *
	 * @return core\fs\Folder
	 */
	public function folder() {
		$root = go()->getEnvironment()->getInstallFolder();
		if(!isset($this->package)) {
			return $root->getFolder("/modules/" . $this->name . "/");
		} else {
			return $root->getFolder("/go/modules/" . $this->package . "/" . $this->name . "/");
		}
	}	
	
	/**
	 * Check if this module is available and licensed
	 * 
	 * @return bool
	 */
	public function isAvailable() {
		
		if(!isset($this->package)) {
			$moduleFile = $this->folder()->getFile(ucfirst($this->name) . "Module.php");
			if(!$moduleFile->exists() || !core\util\ClassFinder::canBeDecoded($moduleFile)) {
				return false;
			}

			//if module has not been refactored yet package is not set. 
			//handle this with old class
			$cls = "GO\\" . ucfirst($this->name) . "\\" . ucfirst($this->name) .'Module';
			if(!class_exists($cls)){
				return false;
			}
			
			return (new $cls)->isAvailable();
		}else {
			if ($this->package == "core" && $this->name == "core") {
				return true;
			}

			$moduleFile = $this->folder()->getFile("Module.php");
			if(!$moduleFile->exists() || !core\util\ClassFinder::canBeDecoded($moduleFile)) {
				return false;
			}

			//todo, how to handle licenses for future packages?
			$cls = $this->getModuleClass();
			return class_exists($cls) && $cls::get()->isAvailable();
		}
	}

	/**
	 * Finds a module based on the given class name
	 * returns null if it belongs to the core.
	 *
	 * @param string $className
	 * @param array $properties
	 * @return self
	 * @throws Exception
	 */
	public static function findByClass(string $className, array $properties = []): Module
	{
		switch($className) {	
			
			case strpos($className, "go\\core") === 0 || strpos($className, "GO\\Base") === 0:
				$module = self::findByName('core', 'core', null, $properties);
				break;
			
			default:				
				
				$classNameParts = explode('\\', $className);

				if($classNameParts[0] == "GO") {
					//old framework eg. GO\Projects2\Model\TimeEntry
					$name = strtolower($classNameParts[1]);
					$package = null;
				} else
				{
					$package = $classNameParts[2];
					$name = $classNameParts[3];
				}
				
				$module = self::findByName($package, $name, null, $properties);
				// Needed for modules which are partly refactored.
				// For example: The email account entity is required in the n ew framework
				// and the email module itself is not refactored yet.
				// Can be removed when all is refactored.
				if(!$module) {
					$module = self::findByName('legacy', $name, null, $properties);
				}
		}
		
		if(!$module) {
			throw new Exception("Module '" . ($package ?? "legacy") . "/" . ($name ?? "core"). "' not found for ".$className);
		}

		return $module;
	}
	
	protected function internalValidate() {
		
		if(!$this->isNew()) {
			if($this->package == 'core' && $this->isModified('enabled')) {
				throw new \Exception("You can't disable core modules");		
			}
			
			if($this->isModified(['name', 'package'])) {
				$this->setValidationError('name', ErrorCode::FORBIDDEN,"You can't change the module name and package");
			}
		}
		
		
		return parent::internalValidate();
	}
	
	protected static function internalDelete(Query $query): bool
	{

		$query->andWhere('package != "core"');

		//clear cache
		self::$modulesByName = [];
		
		return parent::internalDelete($query);
	}
	
	/**
	 * Get all installed and available modules.
	 * @return self[]
	 */
	public static function getInstalled($properties = []) {
		$modules = Module::find($properties)->where(['enabled' => true])->all();
		
		$available = [];
		foreach($modules as $module) {
			if($module->isAvailable()) {
				$available[] = $module;
			}
		}
		
		return $available;
	}

	/**
	 * @param $rights int bitwise rights
	 * @return array permission name => true for on / false for off
	 */
	public function may($rights) {
		$module = $this->module();
		$capabilities = $module->getRights();
		$result = [];
		foreach($capabilities as $str => $bit) {
			if(go()->getAuthState()->isAdmin() ||  ($rights & $bit)) {
				$result[$str] = true;
			}
		}
		return $result;
	}

	/**
	 * @return string[] static list of available rights
	 */
	public function getRights() {
		if(!$this->isAvailable()) {
			return [];
		}
		$module = $this->module();
		return array_keys($module->getRights());
	}

	/**
	 * Check if a module is available
	 * 
	 * @param string $package
	 * @param string $name
	 * @param int $userId
	 * @param int $level
	 * @return boolean
	 */
	public static function isAvailableFor($package, $name, $userId = null, $level = Acl::LEVEL_READ) {

		if($package == "legacy") {
			$package = null;
		}

		$query = static::find()->where(['package' => $package, 'name' => $name, 'enabled' => true]);
		static::applyAclToQuery($query, $level, $userId);
		
		return !!$query->single();
	}

	private static $modulesByName = [];
	
	/**
	 * Find a module by package and name
	 * 
	 * @param string $package
	 * @param string $name
	 * @param bool $enabled Set to null for both enabled and disabled
	 * @return self|false
	 */
	public static function findByName($package, $name, $enabled = true, $props = []) {
		$cache = $package."/". $name;

		if($package == "legacy") {
			$package = null;
		}

		$mod = go()->getCache()->get('module-' . $cache);
		if(empty($mod)) {

			$mod = static::find($props)->where(['package' => $package, 'name' => $name])->single();

			if(empty($props) && !empty($mod)) {
				go()->getCache()->set('module-' . $cache, $mod);
			}
		}

		if(!$mod) {
			return false;
		}

		if(isset($enabled)) {
			return $mod->enabled == $enabled ? $mod : false;
		} else{
			return $mod;
		}
	}

	/**
	 * Check if a module is installed
	 *
	 * @param string $package
	 * @param string $name
	 * @param null|boolean $enabled If set, then the module's enabled flag will be matched
	 * @return bool
	 */
	public static function isInstalled($package, $name, $enabled = null) {
		if($package == "legacy") {
			$package = null;
		}
		$where = ['package' => $package, 'name' => $name];

		if(isset($enabled)) {
			$where['enabled'] = $enabled;
		}
		return static::find()->where($where)->selectSingleValue('id')->single() != null;
	}
	
	/**
	 * Get module settings
	 * 
	 * @return Settings
	 */
	public function getSettings() {
		if(!$this->isAvailable()) {
			return null;
		}
		$module = $this->module();
		if(!method_exists($module, 'getSettings')) {
			return null;
		}
		return $this->module()->getSettings();
	}
	
	/**
	 * Returns all module entities with info
	 * @return core\orm\EntityType[]
	 */
	public function getEntities() :array
	{
		$es = [];

		foreach(core\orm\EntityType::findAll((new core\orm\Query)->where(['moduleId' => $this->id])) as $e) {
			$es[$e->getName()] = $e;
		}

		return $es;
	}

	public function setEntities($entities) {
		$current = $this->getEntities();

		foreach($entities as $name => $e) {
			if(isset($e['defaultAcl'])) {
				$current[$name]->setDefaultAcl($e['defaultAcl']);
			}
		}
	}
	
	public function setSettings($value) {
		$this->getSettings()->setValues($value);
	}
}
