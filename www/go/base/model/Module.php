<?php
namespace GO\Base\Model;

use GO;
use go\core\model\User;
use go\modules\business\license\exception\LicenseException;

/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * The Module model
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model 
 * 
 * @property StringHelper $id The id of the module which is identical to the folder name inside the "modules" folder.
 * @property String $path The absolute filesystem path to module.
 * @property \GO\Base\Module $moduleManager The module class to install, initialize etc the module.
 * @property int $aclId
 * @property boolean $admin_menu
 * @property int $sort_order
 * @property int $version
 * @property int $acl_write
 * @property boolean $enabled
 */

class Module extends \GO\Base\Db\ActiveRecord {

	private $_moduleManager;
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Module 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}

	public function getPermissionLevel($userId = null) {
		if(!\GO::user()) {
			return 0;
		}

		if(\GO::user()->isAdmin())
			return 50;

		if(!isset($userId)) {
			$userId = GO::user()->id;
			if(\go\core\model\User::isAdminById($userId)) {
				return 50;
			}
		} else{
			if(\GO::user()->isAdmin())
				return 50;
		}
		$moduleId = $this->id;

		$groupedRights = "SELECT BIT_OR(rights) as rights FROM core_permission WHERE groupId IN (SELECT groupId from core_user_group WHERE userId = ".$userId.") AND moduleId = ".$moduleId.";";

		$rights = \go()->getDbConnection()->query($groupedRights)->fetch(\PDO::FETCH_COLUMN);
		if($rights === false) {
			return 0;
		}
		if($rights & 1) { // we only have mayManage for old modules
			return 50;
		}

		if($this->name == 'projects2' && ($rights & 2)) { // a single exception for this compat method
			return 45;
		}
		return 10;
	}

	private function adminRights() {
		$rights = ["mayRead" => true];
		foreach($this->getModuleManager()->getRights() as $name => $bit){
			$rights[$name] = true;
		}
		return (object) $rights;
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
			foreach($this->getModuleManager()->getRights() as $name => $bit){
				$rights[$name] = false;
			}
			return (object) $rights;
		}

		$r = decbin($r);

		$rights = ["mayRead" => true];

		foreach ($this->getModuleManager()->getRights() as $name => $bit) {
			$rights[$name] = !!($r & $bit);
		}

		return (object) $rights;
	}

	/**
	 * Get's the rights of a user
	 *
	 * @param int|null $userId The user ID to query. defaults to current authorized user.
	 * @return stdClass For example ['mayRead' => true, 'mayManage'=> true, 'mayHaveSuperCowPowers' => true]
	 */
	public function getUserRights(int|null $userId = null)
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
	
	protected function nextSortOrder() {
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

		return max($query->single(), 100);
	}
	
	/**
	 * Install's a module with all it's dependencies
	 * 
	 * @param string $name
	 * @return \GO\Base\Model\Module
	 * @throws \GO\Base\Exception\Save
	 */
	public static function install($name,$ignoreDependentModule=false, $sort_order = null){
		
		
		GO::debug("install($name,$ignoreDependentModule)");
		
		if(!($module = Module::model()->findByName($name))){
			$module = new Module();
			$module->name=$name;
			$module->sort_order = $sort_order;

			if(!$ignoreDependentModule) {

				\go\core\Module::installDependencies($module->moduleManager);
			}

			if(!$module->save())
				throw new \GO\Base\Exception\Save();
		}else
		{
			if(!$module->enabled){
				$module->enabled=true;

				if(!$ignoreDependentModule) {
					\go\core\Module::installDependencies($module->moduleManager);
				}

				if(!$module->save())
					throw new \GO\Base\Exception\Save();
			}
		}
			
		return $module;
	}


	public function tableName() {
		return 'core_module';
	}
	
	public function primaryKey() {
		return 'id';
	}
	
	protected function getPath(){
		
		if(!empty($this->package)) {
			if($this->package == "core" && $this->name == "core") {
				return \GO::config()->root_path . 'go/core/';
			} else
			{
				return \GO::config()->root_path . 'go/modules/'.$this->package. '/' . $this->name . '/';
			}
		} else {		
			return \GO::config()->root_path . 'modules/' . $this->name . '/';
		}
	}
	
	protected function getModuleManager(){
		if(!isset($this->_moduleManager))	{
			
			if(!isset($this->package)) {
				$this->_moduleManager = \GO\Base\Module::findByModuleName ($this->name);
			} else if($this->package == "core" && $this->name == "core") {
				$this->_moduleManager = \go\core\App::get();
			}else{
				$cls = "go\\modules\\" . $this->package ."\\" . $this->name . "\\Module";
				$this->_moduleManager = $cls::get();
			}
		}
		
		return $this->_moduleManager;
	}
	
	public function getWarning(){
			return '';
	}
	
	public function getSortOrderColumn() {
		return 'sort_order';
	}
	
	public function validate() {
		
		if($this->name=='modules' && $this->enabled==0){
			$this->setValidationError('enabled', GO::t("The module \"Modules\" cannot be deleted!.", "modules"));
		}
		
		return parent::validate();
	}
	protected function beforeSave() {
		if($this->isNew){			
			$this->version = $this->moduleManager->databaseVersion();		
			$this->admin_menu = $this->moduleManager->adminModule();
		}

		if($this->isModified('enabled')) {
			if(!$this->enabled) {
				$this->_checkDependencies();
			} else {
				try {
					\go\core\Module::installDependencies($this->moduleManager);
				} catch(LicenseException $e) {
					return false;
				}
			}
		}
		return parent::beforeSave();
	}
	
	protected function afterSave($wasNew) {
		
		if(!$this->admin_menu && $wasNew) {
			go()->getDbConnection()->insert('core_permission', ['moduleId' => $this->id, 'groupId' => \go\core\model\Group::ID_INTERNAL])->execute();
		}

		
		if($wasNew){			
			if($this->moduleManager)
				$this->moduleManager->install();
		}		
		return parent::afterSave($wasNew);
	}
	
	/**
	 * Checks if all default modules are created for each user.
	 */
	public function checkDefaultModels(){
		$models = array();
		$modMan = $this->moduleManager;
		if ($modMan) {
			$classes = $modMan->findClasses('model');
			foreach ($classes as $class) {
				if ($class->isSubclassOf('GO\Base\Model\AbstractUserDefaultModel')) {
					$models[] = GO::getModel($class->getName());
				}
			}
		}

		$users = User::model()->find(
			(new \GO\Base\Db\FindParams())
				->join('core_user_group', 'u.id = ug.userId', 'ug')
				->join('core_permission', 'p.groupId = ug.groupId', 'ug')
				->getCriteria()
				->addRawCondition('p.moduleId = '.$this->id)
				->addCondition('u.enabled', '=', true)
			);
		

		foreach($users as $user) {
			foreach ($models as $model) {
				$model->getDefault($user);
			}
		}
	}
	

	protected function beforeDelete()
	{
		if($this->name=='modules'){
			$this->setValidationError('delete', GO::t("The module \"Modules\" cannot be deleted!.", "modules"));
		}
		
		$this->_checkDependencies();
		return parent::beforeDelete();
		
	}
	
	private function _checkDependencies() {
		
		$dependentModuleNames = \go\core\Module::getModulesThatDependOn($this->moduleManager);
		
		if (count($dependentModuleNames)>0)
			throw new \Exception(sprintf(\GO::t("You cannot delete the current module, because the following (installed) modules depend on it: %s."),implode(', ',$dependentModuleNames)));
		
	}

	protected function afterDelete() {
		if($this->moduleManager) {
			$this->moduleManager->uninstall();
		}
		
		return parent::afterDelete();
	}
	
	/**
	 * Check if the module is available on disk.
	 * 
	 * @return boolean 
	 */
	public function isAvailable(){
		
		if(!$this->enabled) {
			return false;
		}
		
		if(!empty($this->package)) {
			return $this->isAvailableJmap();
		}
		
		$ucfirst = ucfirst($this->name);
		$moduleClass = 'GO\\'.$ucfirst.'\\'.$ucfirst.'Module';

		if(!class_exists($moduleClass)){
			return false;
		}

		$mod = new $moduleClass;
		return $mod->isAvailable();
	}
	
	private function isAvailableJmap() {
		return is_dir($this->getPath());
	}
	
	public function isAllowed() {
		return \GO\Base\ModuleCollection::isAllowed($this->name);
	}
	
	/**
	 * Finds module by name without checking ACL
	 * 
	 * @param string $name
	 * @return self
	 */
	public function findByName($name) {
		return $this->findSingleByAttributes(['name'=> $name, 'package' => null], (new \GO\Base\Db\FindParams())->ignoreAcl());
	}
}
