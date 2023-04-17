<?php
namespace GO\Base;

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
 * Module manager
 * 
 * This class is used to manage a module. It performs tasks such as
 * installing, uninstalling and initializing.
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base 
 */

class Module extends Observable {
	
	const PACKAGE_UNSUPPORTED = '3rd party (Not supported by Intermesh)';
	
	const PACKAGE_COMMUNITY = 'Community (AGPL)';
	
	const PACKAGE_CUSTOM = 'Custom made';
	
	const PACKAGE_IN_DEVELOPMENT = 'In development';

	private $_id;
	/**
	 * Get the id of the module which is identical to 
	 * the folder name in the modules folder.
	 * 
	 * eg. notes, calendar  etc.
	 * @return StringHelper 
	 */
	public function id() {
		
		if(!isset($this->_id)){
			$className = get_class($this);

			$arr = explode('\\', $className);
			$this->_id=strtolower($arr[1]);
		}
		return $this->_id;
	}
	
	public function setId($id){
		$this->_id=$id;
	}
	
	/**
	 * Get the absolute filesystem path to the module.
	 * 
	 * @return StringHelper 
	 */
	public function path(){
		return \GO::config()->root_path . 'modules/' . $this->id() . '/';
	}

	/**
	 * Return the localized name
	 * 
	 * @return String 
	 */
	public function name() {
		$name = \GO::t('name', $this->id());
		if($name=='name')
			$name = $this->id();
		return $name;
	}
	
	/**
	 * Get URL to module icon
	 * 
	 * @return StringHelper 
	 */
	public function icon(){
		
		$icon = $this->_findIconByTheme(\GO::user()->theme);
		if(!$icon)
			$icon = $this->_findIconByTheme("Default");
		
		if(!$icon)
			$icon = \GO::config()->host.'views/Extjs3/themes/Default/images/16x16/unknown.png';
		
		return $icon;
	}
	
	public function package(){
		return self::PACKAGE_COMMUNITY;
	}
	
	private function _findIconByTheme($theme){
		$path = $this->path();
		if(file_exists($path.'/themes/'.$theme.'/images/'.$this->id().'.png')){
			return \GO::config()->host.'modules/'.$this->id().'/themes/'.$theme.'/images/'.$this->id().'.png';
		}elseif(file_exists($path.'views/Extjs3/themes/'.$theme.'/images/'.$this->id().'.png')){
			return \GO::config()->host.'modules/'.$this->id().'/views/Extjs3/themes/'.$theme.'/images/'.$this->id().'.png';
		}  else {
			return false;
		}
	}

	/**
	 * Return the localized description
	 * 
	 * @return String 
	 */
	public function description() {
		return \GO::t('description', $this->id());
	}
	
	/**
	 * Return the name of the author.
	 * 
	 * @return String 
	 */
	public function author(){
		return '';
	}
	
	/**
	 * Return the e-mail address of the author.
	 * 
	 * @return String 
	 */
	public function authorEmail(){
		return 'info@intermesh.nl';
	}
	
	/**
	 * Return copyright information
	 * 
	 * @return String 
	 */
	public function copyright(){
		return 'Copyright Intermesh BV';
	}
	
	/**
	 * Return true if this module belongs in the admin menu.
	 * 
	 * @return boolean 
	 */
	public function adminModule(){
		return false;
	}
	
	/**
	 * Return true if this module has a GUI
	 * 
	 * @return boolean 
	 */
	public function hasInterface(){
		return true;
	}
	
	/**
	 * Return true if this module can be bought in the Group-Office app center
	 * 
	 * @return boolean
	 */
	public function appCenter(){
		return false;
	}
	
	/**
	 * Automatically install this module on installation.
	 * 
	 * @return boolean 
	 */
	public function autoInstall(){
		return false;
	}
	
	/**
	 * Return an array of modules this module depends on.
	 * 
	 * @return array 
	 */
	public function depends(){
		return array();
	}
	
	/**
	 * Override this function if for some reason this module can becomes 
	 * unavailable.
	 * 
	 * @return boolean
	 */
	public function isAvailable(){
		return true;
	}
	
	/**
	 * Return false is for some reason this module is not instalable.
	 * 
	 * @return boolean
	 */
	public function isInstallable(){
		return true;
	}
	
	/**
	 * Find the module manager class by id.
	 * 
	 * @param StringHelper $moduleId eg. "addressbook"
	 * @return \Module|boolean 
	 */
	public static function findByModuleId($moduleId){
		$className = 'GO\\'.ucfirst($moduleId).'\\'.ucfirst($moduleId).'Module';
		if(class_exists($className))
			return new $className;
		else{
			$modMan =  new Module();
			$modMan->setId($moduleId);
			return $modMan;
		}
	}
	
	/**
	 * Return the number of update queries.
	 * 
	 * @return integer 
	 */
	public function databaseVersion(){
		$updatesFile = $this->path() . 'install/updates.php';
		if(!file_exists($updatesFile))
			$updatesFile = $this->path() . 'install/updates.inc.php';
		
		return Util\Common::countUpgradeQueries($updatesFile);
	}
	
	public function checkDependenciesForInstallation($modulesToBeInstalled=array()){
		$depends = $this->depends();
		
		foreach($depends as $moduleId){
			if(!\GO::modules()->isInstalled($moduleId) && !in_array($moduleId,$modulesToBeInstalled)){
				
				$moduleNames = array();
				foreach($depends as $moduleId){
					$modManager = Module::findByModuleId($moduleId);
					$moduleNames[]=$modManager ? $modManager->name () : $moduleId;
				}				
				
				throw new \Exception("Module ".$this->name()." depends on ".implode(",",$moduleNames).". Please make sure all dependencies are installed.");
			}
		}
	}
	
	
	public function getDependencies(){
		$depends = $this->depends();
		
		$moduleIds = array();
		
		foreach($depends as $moduleId){
			if(!($module = \GO::modules()->isInstalled($moduleId)) || $module->isAvailable()){
				foreach($depends as $moduleId){
					$moduleIds[]=$moduleId;
				}				
			}
		}
		
		return $moduleIds;
	}

	/**
	 * Installs the module's tables etc
	 * 
	 * @return boolean
	 */
	public function install() {		
		
		$sqlFile = $this->path().'install/install.sql';
		
		try{
			if(file_exists($sqlFile))
			{
				$queries = Util\SQL::getSqlQueries($sqlFile);

				foreach($queries as $query)
					\GO::getDbConnection ()->query($query);
			}
		}catch(\Exception $e){
			throw new \Exception("SQL query failed: ".$query."\n\n".$e->getMessage());
		}
		
		\GO::clearCache();
		Observable::cacheListeners();
		//call saveUser for each user
//		$stmt = Model\User::model()->find(array('ignoreAcl'=>true));		
//		while($user = $stmt->fetch()){
//			call_user_func(array(get_class($this),'saveUser'), $user, true);
//		}
		
		return true;
	}
	
	/**
	 * Run this code when the module is disabled
	 * 
	 * @return boolean
	 */
	public function disable() {		
		return true;
	}
	
	/**
	 * Run this code when the module is enabled 
	 * NOTE: This is also called after doing a fresh install of this module. (First time)
	 * 
	 * @return boolean
	 */
	public function enable() {		
		return true;
	}

	/**
	 * Delete's the module's tables etc.
	 * 
	 * @return boolean
	 */
	public function uninstall() {
		
		$oldIgnore = \GO::setIgnoreAclPermissions();
		
		
//		//call deleteUser for each user
//		$stmt = Model\User::model()->find(array('ignoreAcl'=>true));		
//		while($user = $stmt->fetch()){
//			call_user_func(array(get_class($this),'deleteUser'), $user);
//		}
		
		//Uninstall cron jobs for this module
		$cronClasses = $this->findClasses('cron');
		foreach($cronClasses as $class){
			
			$jobs = Cron\CronJob::model()->findByAttribute('job', $class->getName());
			foreach($jobs as $job)
				$job->delete();			
		}
		
		
		//delete all models from the Model\ModelType table.
		//They are used for faster linking and search cache. Each linkable model is mapped to an id in this table.
		$models = $this->getModels();
		
		$modelTypes = array();
		foreach($models as $model){			
			$modelType = Model\ModelType::model()->findSingleByAttribute('model_name', $model->getName());			
			if($modelType){
				
				$modelTypes[]=$modelType->id;
				$modelType->delete();
			}
		}
		
		if(!empty($modelTypes)){			
			
			$sql = "DELETE FROM  `go_search_cache` WHERE model_type_id IN (".implode(',', $modelTypes).")";
			\GO::getDbConnection()->query($sql);
			
			
			$stmt = \GO::getDbConnection()->query('SHOW TABLES');
			while ($r = $stmt->fetch()) {
				$tableName = $r[0];

				if (substr($tableName, 0, 9) == 'go_links_' && !is_numeric(substr($tableName, 9, 1))) {			
					$sql = "DELETE FROM  `$tableName` WHERE model_type_id IN (".implode(',', $modelTypes).")";
					\GO::getDbConnection()->query($sql);
				}
			}
		}
		
		
		
		
		$sqlFile = $this->path().'install/uninstall.sql';
		
		if(file_exists($sqlFile))
		{
			$queries = Util\SQL::getSqlQueries($sqlFile);
			foreach($queries as $query)
				\GO::getDbConnection ()->query($query);
		}
		
		\GO::clearCache();
		Observable::cacheListeners();
		
		\GO::setIgnoreAclPermissions($oldIgnore);
		
		return true;
	}

	/**
	 * This class can be overriden by a module class to add listeners to objects
	 * that extend the Observable class.
	 * 
	 * eg. Model\User::model()->addListener('save','SomeClass','someStaticFunction');
	 * 	 
	 */
	public static function initListeners() {
		
	}
	
	/**
	 * This function is called when the first request is made to the module.
	 * Useful to check for a default calendar, tasklist etc.
	 * 
	 * The response is added to the controller action parameters with index
	 * 'firstRun'.
	 */
	public static function firstRun(){		
		return '';
	}
	
	/**
	 * This function is called when the search index needs to be rebuilt.
	 * 
	 * You want to use MyModel::model()->rebuildSearchCache();
	 * 
	 * @param array $response Array of output lines
	 */
	public function buildSearchCache(&$response){		
		
		$response[]  = "Building search cache for ".$this->id()."\n";		
				
		$models=$this->getModels();

		foreach($models as $model){
			if($model->isSubclassOf("GO\Base\Db\ActiveRecord")){
				echo $response[] = "Processing ".$model->getName()."\n";
				$stmt = \GO::getModel($model->getName())->rebuildSearchCache();
			
			}
		}
	}
	
	/**
	 * This function is called when a database check is performed
	 * 
	 * @param array $response Array of output lines
	 */
	public function checkDatabase(&$response){				
		
		//echo "<pre>";
		
		echo "Checking database for ".$this->id()."\n";		
				
		$models=$this->getModels();
		
			
		
		foreach($models as $model){	
			if($model->isSubclassOf("GO\Base\Db\ActiveRecord")){
				$m = \GO::getModel($model->getName());
				
				if($m->checkDatabaseSupported()){					
					
					echo "Checking ".$model->getName()."\n";
					flush();
					
					//to avoid memory errors
					$start = 0;
					
					//per thousands to keep memory low
					$stmt = $m->find(array(
							'ignoreAcl'=>true,
							'start' => $start,
							'limit' => 1000
					));
					
					while($stmt->rowCount()) {					
						$stmt->callOnEach('checkDatabase');
						
						$stmt = $m->find(array(
								'ignoreAcl'=>true,
								'start' => $start+=1000,
								'limit' => 1000
						));
					}
					
					unset($stmt);
				}else
				{
					echo "No check needed for ".$model->getName()."\n";
					flush();
				}
			}
		}
	}
	
	/**
	 * Get all model class names.
	 * 
	 * @return ReflectionClass[] Names of all model classes 
	 */
	public function getModels(){		
	
		$models=array();
		$classes=$this->findClasses('model');
		foreach($classes as $class){
				if(!$class->isAbstract()){					
					$models[] = $class;
				}
		}		
		return $models;
	}
	
	/**
	 * Find all classes in a folder.
	 * 
	 * @param StringHelper $subfolder
	 * @return \ReflectionClass array
	 */
	public function findClasses($subfolder){
		
		$classes=array();
		$folder = new Fs\Folder($this->path().$subfolder);
		if($folder->exists()){
			
			$items = $folder->ls();
			
			foreach($items as $item){
				if($item instanceof Fs\File){
					
					$subParts = explode('/', $subfolder);
					$subParts=array_map("ucfirst", $subParts);
					
					$className = 'GO\\'.ucfirst($this->id()).'\\'.implode('\\',$subParts).'\\'.$item->nameWithoutExtension();			
					if(class_exists($className)){
						$reflectionClass = new \ReflectionClass($className);
						if(!$reflectionClass->isAbstract())
							$classes[] = $reflectionClass;					
					}
				}
			}
		}
		
		return $classes;
	}
	
	
	/**
	 * Called when the main settings are loaded.
	 * 
	 * @param \GO\Core\Controller\Settings $settingsController
	 * @param array $params Request params
	 * 
	 * $params['id'] is the current logged in user id.
	 * 
	 * @param array $response 
	 */
	public static function loadSettings($settingsController, &$params, &$response, $user){		
	}
	
	/**
	 * Called when the main settings are submitted.
	 * 
	 * @param \GO\Core\Controller\Settings $settingsController
	 * @param array $params Request params
	 * 
	 * $params['id'] is the current logged in user id.
	 * 
	 * @param array $response 
	 */
	public static function submitSettings($settingsController, &$params, &$response, $user){		
	}
}