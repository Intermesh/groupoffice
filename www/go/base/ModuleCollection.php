<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

/**
 * A collection that holds all the installed modules.
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base 
 */

namespace GO\Base;

use GO\Base\Model\Acl;

class ModuleCollection extends Model\ModelCollection{
	
	private $_allowedModules;
	
	public function __construct($model='GO\Base\Model\Module'){

		parent::__construct($model);
	}
	
	
	
	
	
	private function _isAllowed($name){
		
		if(!isset($this->_allowedModules)) {
			if(!empty(\GO::config()->allowed_modules)) {
				$this->_allowedModules = explode(',', \GO::config()->allowed_modules);		
				$this->_allowedModules = array_merge($this->_allowedModules, ['core', 'links', 'search', 'users', 'modules', 'groups', 'customfields']);
				
			} else
			{
				$this->_allowedModules = [];
			}
		}
		
		return empty($this->_allowedModules) || in_array($name, $this->_allowedModules);			
	}
	
	/**
	 * Returns an array of all module classes as string found in the modules folder.
	 * 
	 * This function does not check the module isAvailable function. So pro modules
	 * will be returned even if they can't be decoded. Check the availability manually
	 * if needed.
	 * 
	 * @return array Module class names eg. \GO\Calendar\Module
	 */
	public function getAvailableModules($returnInstalled=false){
		$folder = new Fs\Folder(\GO::config()->root_path.'modules');
		
		$folders = $folder->ls();
		$modules = array();
		foreach($folders as $folder){
			if($folder->isFolder()){
				$ucfirst = ucfirst($folder->name());
//				$moduleClass = $folder->path().'/'.$ucfirst.'Module.php';
				if($this->isAvailable($folder->name(), false) && ($returnInstalled || !Model\Module::model()->findByPk($folder->name(), false, true))){
					$modules[]='GO\\'.$ucfirst.'\\'.$ucfirst.'Module';
				} Else  {
					if(\GO::config()->debug) {
						if(! $this->isAvailable($folder->name(), false)) {
							\GO::debug("************ Model load error ************");
							\GO::debug("Folder is not a module :: ". $folder->name());
							\GO::debug("Class  :: GO\\".$ucfirst."\\".$ucfirst."Module");
							\GO::debug("file  :: ".$folder->path()."/".$ucfirst."Module.php");
						}
					}
				}
				
			}
		}
		
		//for new framework
		$classFinder = new \go\core\util\ClassFinder(false);
		$classFinder->addNamespace("go\\modules");
		$mods = $classFinder->findByParent(\go\core\Module::class);
		$mods = array_filter($mods, function($mod) {
			return $this->_isAllowed($mod::getName());
		});
		$modules = array_merge($modules, $mods);
		
		
		sort($modules);
		
		return $modules;		
	}
	
	/**
	 * Check if a module is available
	 * 
	 * @param StringHelper $moduleId
	 * @param boolean Check the module manager class isAvailable function too. (Used in pro modules to check license for example).
	 * @return boolean
	 */
	public function isAvailable($moduleId, $checkModuleAvailabiltiy=true){
		
		if(!$this->_isAllowed($moduleId))
			return false;
		
		$folder = new Fs\Folder(\GO::config()->root_path.'modules/'.$moduleId);
		
		$ucfirst = ucfirst($moduleId);
		$moduleClassPath = $folder->path().'/'.$ucfirst.'Module.php';
		
		if(!file_exists($moduleClassPath)){
			return false;
		}

		$moduleClass = 'GO\\'.$ucfirst.'\\'.$ucfirst.'Module';

		if(!class_exists($moduleClass)){
			return false;
		}

		if($checkModuleAvailabiltiy){
			$mod = new $moduleClass;
			return $mod->isAvailable();			
		}else
		{
			return true;
		}
		

	}
	
	
	
	

	/**
	 * Call a method of a module class. eg. \GO\Notes\NotesModule::firstRun
	 * 
	 * @deprecated Preferrably use events with listeners because it has better performance
	 * @param StringHelper $method
	 * @param array $params 
	 */
	public function callModuleMethod($method, $params=array(), $ignoreAclPermissions=true){
		
		$oldIgnore = \GO::setIgnoreAclPermissions($ignoreAclPermissions);
		$modules = $this->getAllModules();
		
		foreach($modules as $module)
		{	
				$object = $module->moduleManager;
				if(method_exists($object, $method)){					
//						\GO::debug('Calling '.$class.'::'.$method);
					call_user_func_array(array($object, $method), $params);
					//$object->$method($params);
				}
				
//			}
		}
		
		\GO::setIgnoreAclPermissions($oldIgnore);
	}
	
	private $_modules;
	
	public function __get($name) {
		
		if(!isset($this->_modules[$name])) {	
			$model = $this->model->findSingleByAttribute('name', $name);
			
			if($model && (!$model->isAvailable() || !$model->checkPermissionLevel(Acl::READ_PERMISSION))) {
				$model = false;
			}
			
			if(!$model || !$model->enabled){
				$model=false;
			}

			$this->_modules[$name]=$model;
		}
		
		$module = $this->_modules[$name];
		
		if(\GO::$ignoreAclPermissions){
			unset($this->_modules[$name]);
		}
		
		return $module;
	}
	
	/**
	 * Check if a module is installed.
   * Default check if module is enabled an treat a disabled module as not installed. When checking from within moduleController return the model if record is in core_module
	 * 
	 * @param StringHelper $name
   * @param boolean $checkEnabled
	 * @return Model\Module 
	 */
	public function isInstalled($name, $checkEnabled = true)
	{
			$model = $this->model->findByName($name);

			if (!$model || !$this->_isAllowed($model->name))
					return false;

			if ($checkEnabled && !$model->enabled)
					return false;

			return $model;
	}
	
	
	public function __isset($name){
		try{
			$module = $this->$name;
			return isset($module);
		}catch(Exception\AccessDenied $e){
			return false;
		}
	}
	
	/**
	 * Query all modules.
	 * 
	 * @return Model\Module[]
	 */
	public function getAllModules($ignoreAcl=false){
		
		$cacheKey = $ignoreAcl ? 'all-modules-ignore' : 'all-modules';
		
		if(!$ignoreAcl && \GO::user()) {
			$cacheKey .= '-'. \GO::user()->id;
		}
		
		if(($modules = \GO::cache()->get($cacheKey))) {
			return $modules;
		}
		
		$findParams = Db\FindParams::newInstance()->order("sort_order");
		
		if($ignoreAcl)
			$findParams->ignoreAcl ();
		
		$stmt = $this->model->find($findParams);
		$modules = array();
		while($module = $stmt->fetch()){
			if($this->_isAllowed($module->name) && $module->isAvailable())
				$modules[]=$module;
		}
		
		\GO::cache()->set($cacheKey, $modules);
		
		return $modules;
	}
	
	/**
	 * Find all classes in a folder of all modules.
	 * 
	 * For example findClassses("model") finds all models.
	 * 
	 * @param StringHelper $subfolder
	 * @return ReflectionClass array
	 */
	public function findClasses($subfolder){
		
		$classes =array();
		$modules = $this->getAllModules();
		
		foreach($modules as $module)
			$classes = array_merge($classes, $module->moduleManager->findClasses($subfolder));
		
		return $classes;
	}
}
