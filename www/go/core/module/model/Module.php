<?php
namespace go\core\module\model;

use Exception;
use go\core\acl\model\AclEntity;
use go\core\App;
use go\core\auth\model\Group;
use go\core\auth\model\User;
use go\core\db\Utils;
use go\core\links\Link;
use go\core\module\Base;
use go\core\orm\Entity;
use go\core\search\Search;

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
		
		//if module has not been refactored yet package is not set. 
		if(!isset($this->package)) {
			return false;
		}
		
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
			//case \go\core\auth\Method::class:
			case strpos($className, "go\core\auth") === 0:
				$module = Module::find()->where(['name' => "users"])->single();				
				break;
			case \go\core\auth\model\Module::class:				
				$module = Module::find()->where(['name' => "modules"])->single();
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
				
				$classNameParts = explode('\\', $className);
				
				if($classNameParts[0] == "GO") {
					//old framework eg. GO\Projects2\Model\TimeEntry
					$name = strtolower($classNameParts[1]);
				} else
				{
					$name = $classNameParts[3];
				}				
				
				$module = Module::find()->where(['name' => $name])->single();
		}
		
		if(!$module) {
			throw new Exception("Module not found for ".$className);
			
		}
		return $module;
	}
	
	
	
	protected function internalDelete() {
		
	
		//hard delete!
		return Entity::internalDelete();
	}
}
