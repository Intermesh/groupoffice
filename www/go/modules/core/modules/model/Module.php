<?php
namespace go\modules\core\modules\model;

use Exception;
use go\core\acl\model\AclEntity;
use go\core\App;
use go\modules\core\groups\model\Group;
use go\modules\core\users\model\User;
use go\core\db\Utils;
use go\modules\core\links\model\Link;
use go\core\module\Base;
use go\core\orm\Entity;
use go\modules\core\search\model\Search;

class Module extends AclEntity {
	public $id;
	public $name;
	public $package;
	public $sort_order;
	public $admin_menu;
	public $version;
	public $enabled;
	

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
		
		if(!$this->isNew() && $this->package == 'core' && $this->isModified('enabled')) {
			throw new \Exception("You can't disable core modules");		
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
}
