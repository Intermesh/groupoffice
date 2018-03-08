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
 * @property int $acl_id
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
	
	/**
	 * Install's a module with all it's dependencies
	 * 
	 * @param StringHelper $moduleId
	 * @return \GO\Base\Model\Module
	 * @throws \GO\Base\Exception\Save
	 */
	public static function install($moduleId,$ignoreDependentModule=false){
		
		
		GO::debug("install($moduleId,$ignoreDependentModule)");
		
		if(!($module = Module::model()->findByPk($moduleId, false, true))){
			$module = new Module();
			$module->id=$moduleId;
			
			$dependencies = $module->moduleManager->getDependencies();	
			
			foreach($dependencies as $dependency){
				if($ignoreDependentModule!==$dependency){
					self::install($dependency, $moduleId);
				}
			}

			if(!$module->save())
				throw new \GO\Base\Exception\Save();
		}else
		{
			if(!$module->enabled){
				$module->enabled=true;
				
				$dependencies = $module->moduleManager->getDependencies();	
				
				GO::debug($dependencies);
			
				foreach($dependencies as $dependency){
					if($ignoreDependentModule!==$dependency){
						self::install($dependency, $moduleId);
					}
				}
				$module->save();				
			}
		}
			
		return $module;
	}

	public function aclField() {
		return 'acl_id';
	}

	public function tableName() {
		return 'go_modules';
	}
	
	protected function getPath(){
		return \GO::config()->root_path . 'modules/' . $this->id . '/';
	}
	
	protected function getModuleManager(){
		if(!isset($this->_moduleManager))	
			$this->_moduleManager = \GO\Base\Module::findByModuleId ($this->id);
		
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
		
		if($this->id=='modules' && $this->enabled==0){
			$this->setValidationError('enabled', GO::t('cmdModulesCannotBeDeleted','modules'));
		}
		
		return parent::validate();
	}
	protected function beforeSave() {
		if($this->isNew){			
			$this->version = $this->moduleManager->databaseVersion();		
			$this->admin_menu = $this->moduleManager->adminModule();
		}		
		return parent::beforeSave();
	}
	
	protected function afterSave($wasNew) {
		
		if(!$this->admin_menu)
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
						$this->acl_id, 
						Acl::READ_PERMISSION, 
						function($user, $models){		
							foreach ($models as $model)
								$model->getDefault($user);		
						}, array($models));
	}
	
	protected function beforeDelete() {
		
		
		if($this->id=='modules'){
			$this->setValidationError('delete', GO::t('cmdModulesCannotBeDeleted','modules'));
		}
		
		$this->_checkDependencies();
		return parent::beforeDelete();
		
	}
	
	private function _checkDependencies() {
		
		$dependentModuleNames = array();
		$modules = \GO::modules()->getAllModules(true);
		foreach ($modules as $module) {
			$depends = $module->moduleManager->depends();
			if (in_array($this->id,$depends))
				$dependentModuleNames[] = $module->moduleManager->name();
		}
		
		if (count($dependentModuleNames)>0)
			throw new \Exception(sprintf(\GO::t('dependenciesCannotDelete'),implode(', ',$dependentModuleNames)));
		
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
		
		$ucfirst = ucfirst($this->id);
		$moduleClassPath = $this->path.'/'.$ucfirst.'Module.php';
		
		if(!file_exists($moduleClassPath)){
			return false;
		}

		$moduleClass = 'GO\\'.$ucfirst.'\\'.$ucfirst.'Module';

		if(!class_exists($moduleClass)){
			return false;
		}

		$mod = new $moduleClass;
		return $mod->isAvailable();	
		
		
	}
	
	public function isAllowed(){
		$allowedModules=empty(\GO::config()->allowed_modules) ? array() : explode(',', \GO::config()->allowed_modules);
		
		return empty($allowedModules) || in_array($this->id, $allowedModules);
	}

//	protected function getName() {
//		return \GO::t('name', $this->id);// isset($lang[$this->id]['name']) ? $lang[$this->id]['name'] : $this->id;
//	}
//
//	protected function getDescription() {
//		return \GO::t('description', $this->id);
//	}
	}
