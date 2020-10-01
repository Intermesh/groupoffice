<?php
namespace GO\Base\Model;

use GO;

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

		return $query->single();
	}
	
	/**
	 * Install's a module with all it's dependencies
	 * 
	 * @param StringHelper $name
	 * @return \GO\Base\Model\Module
	 * @throws \GO\Base\Exception\Save
	 */
	public static function install($name,$ignoreDependentModule=false){
		
		
		GO::debug("install($name,$ignoreDependentModule)");
		
		if(!($module = Module::model()->findByName($name))){
			$module = new Module();
			$module->name=$name;

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


				$module->save();				
			}
		}
			
		return $module;
	}

	public function aclField() {
		return 'aclId';
	}

	public function tableName() {
		return 'core_module';
	}
	
	public function primaryKey() {
		return 'id';
	}
	
	protected function getPath(){
		
		if(!empty($this->package)) {
			if($this->package == "core") {
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
				$this->_moduleManager = new $cls;
			}
		}
		
		return $this->_moduleManager;
	}
	
	public function getWarning(){
//		if(!$this->moduleManager->appCentre() || $this->moduleManager->checkPermissionsWithLicense()){
			return '';
//		}else
//		{
//			return 'You have unlicensed users. Double click to buy more licenses.';
//		}
	}
	
//	public function getBuyEnabled(){
//		return $this->moduleManager->appCentre() && \GO\Professional\License::moduleIsRestricted($this->id);
//	}
	
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
				\go\core\Module::installDependencies($this->moduleManager);
			}
		}
		return parent::beforeSave();
	}
	
	protected function afterSave($wasNew) {
		
		if(!$this->admin_menu && $wasNew)
			$this->acl->addGroup(\GO::config()->group_internal);
		
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
		
		$this->acl->getAuthorizedUsers(
						$this->aclId, 
						Acl::READ_PERMISSION, 
						function($user, $models){		
							foreach ($models as $model)
								$model->getDefault($user);		
						}, array($models));
	}
	
	/**
	 * @deprecated since 6.3
	 * Added to be backwards compatible
	 * 
	 * @return ACL ID
	 */
	public function getAcl_id(){
		return $this->aclId;
	}
	
	protected function beforeDelete() {
		
		
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
		if($this->moduleManager)
			$this->moduleManager->uninstall();
		
		return parent::afterDelete();
	}
	
	/**
	 * Check if the module is available on disk.
	 * 
	 * @return boolean 
	 */
	public function isAvailable(){
		
		if(!$this->enabled)
			return false;
		
		if(!empty($this->package)) {
			return $this->isAvailableJmap();
		}
		
		$ucfirst = ucfirst($this->name);
//		$moduleClassPath = $this->path.'/'.$ucfirst.'Module.php';
//		
//		if(!file_exists($moduleClassPath)){
//			return false;
//		}

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
		return $this->findSingleByAttribute('name', $name, (new \GO\Base\Db\FindParams())->ignoreAcl());
	}

//	protected function getName() {
//		return \GO::t('name', $this->id);// isset($lang[$this->id]['name']) ? $lang[$this->id]['name'] : $this->id;
//	}
//
//	protected function getDescription() {
//		return \GO::t('description', $this->id);
//	}
	}
