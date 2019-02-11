<?php
namespace go\core\model;

use Exception;
use go\core\acl\model\AclOwnerEntity;
use go\core\App;
use go\core\model\Group;
use go\core\model\User;
use go\core\db\Utils;
use go\core\model\Link;
use go\core\module\Base;
use go\core\orm\Entity;
use go\core\model\Search;

class Module extends AclOwnerEntity {
	public $id;
	public $name;
	public $package;
	public $sort_order;
	public $admin_menu;
	public $version;
	public $enabled;
	
	
	
	protected function internalSave() {
		
		if($this->isNew()) {
			$this->sort_order = $this->nextSortOrder();			
		}
		
		if(!parent::internalSave()) {
			return false;
		}
		
		$settings = $this->getSettings();
		if($settings && !$settings->save()) {
			return false;
		}
		
		return true;
	}
	
	
	private function nextSortOrder() {
		$query = new \go\core\db\Query();			
		$query->from("core_module");

		if($this->package == "core") {
			$query->selectSingleValue("COALESCE(MAX(sort_order), 0) + 1")
				->where(['package' => "core"]);
		} else
		{
			$query->selectSingleValue("COALESCE(MAX(sort_order), 100) + 1")
				->where('package', '!=', "core");
		}

		return $query->single();
	}
	

	protected static function defineMapping() {
		return parent::defineMapping()->addTable('core_module');
	}
	
	
	/**
	 * Get the module base file object
	 * 
	 * @return Base
	 */
	public function module() {
		$cls = $this->getModuleClass();
		
		return new $cls;
	}	
	
	private function getModuleClass() {
		if($this->package == "core" && $this->name == "core") {
			return "\\go\\core\\Module";
		}
		return "\\go\\modules\\" . $this->package ."\\" . $this->name ."\\Module";
	}	
	
	public function isAvailable() {
		
		
		if(!isset($this->package)) {
			//if module has not been refactored yet package is not set. 
			//handle this with old class
			$cls = "GO\\" . ucfirst($this->name) . "\\" . ucfirst($this->name) .'Module';
			if(!class_exists($cls)){
				return false;
			}
			
			return (new $cls)->isAvailable();
		}
		
		//todo, how to handle licenses for future packages?
		$cls = $this->getModuleClass();
		return class_exists($cls);
	}

	/**
	 * Finds a module based on the given class name
	 * returns null if it belongs to the core.
	 * 
	 * @param string $className
	 * @return self
	 * @throws Exception
	 */
	public static function findByClass($className) {
		
		switch($className) {	
			
			case strpos($className, "go\core\auth") === 0:
				$module = Module::find()->where(['name' => "users"])->single();				
				break;
			case Link::class:				
				$module = Module::find()->where(['name' => "links"])->single();
				break;
			case Search::class:			
				$module = Module::find()->where(['name' => "search"])->single();				
				break;
			
			case Module::class:
				$module = Module::find()->where(['name' => "modules"])->single();				
			break;				
			
			default:
				
				if(strstr($className, 'go\core')) {
					$name = 'core';
				} else
				{
				
					$classNameParts = explode('\\', $className);

					if($classNameParts[0] == "GO") {
						//old framework eg. GO\Projects2\Model\TimeEntry
						$name = strtolower($classNameParts[1]);
					} else
					{
						$name = $classNameParts[3];
					}				
				}
				
				$module = Module::find()->where(['name' => $name])->single();
		}
		
		if(!$module) {
			throw new Exception("Module not found for ".$className);
			
		}
		return $module;
	}
	
	protected function internalValidate() {
		
		if(!$this->isNew()) {
			if($this->package == 'core' && $this->isModified('enabled')) {
				throw new \Exception("You can't disable core modules");		
			}
			
			if($this->isModified(['name', 'package'])) {
				$this->setValidationError('name', \go\core\validate\ErrorCode::FORBIDDEN,"You can't change the module name and package");
			}
		}
		
		
		return parent::internalValidate();
	}
	
	protected function internalDelete() {
		
		if($this->package == "core") {
			throw new \Exception("You can't delete core modules");
		}
	
		//hard delete!
		return Entity::internalDelete();
	}
	
	/**
	 * Get all installed and available modules.
	 * @return self[]
	 */
	public static function getInstalled() {
		$modules = \go\core\model\Module::find()->where(['enabled' => true])->all();
		
		$available = [];
		foreach($modules as $module) {
			if($module->isAvailable()) {
				$available[] = $module;
			}
		}
		
		return $available;
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
	public static function isAvailableFor($package, $name, $userId = null, $level = \go\core\acl\model\Acl::LEVEL_READ) {
		$query = static::find()->where(['package' => $package, 'name' => $name]);
		static::applyAclToQuery($query, $level, $userId);
		
		return $query->single() !== false;
	}
	
	/**
	 * Get module settings
	 * 
	 * @return \go\core\Settings
	 */
	public function getSettings() {
		if(!isset($this->package)) {
			return null;
		}
		
		return $this->module()->getSettings();
	}
	
	public function setSettings($value) {
		$this->getSettings()->setValues($value);
	}
}
