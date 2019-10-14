<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */
namespace GO\Base\Model;

/**
 * A model has an ID stored in the database that is used for faster searches
 * of links.
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model 
 * @property string $name
 * @property int $moduleId
 * @property int $id
 */
class ModelType extends \GO\Base\Db\ActiveRecord {


	public function tableName(){
		return "core_entity";
	}

	/**
	 * @deprecated (for search controller to find links etc)
	 * @return type
	 */
	public function relations() {
		return [
			'moduleRel' => array('type'=>self::BELONGS_TO, 'model'=>'GO\Base\Model\Module', 'field'=>'moduleId')
		];
	}
	
	public function findByModelName($modelName){
		
		if(empty($modelName))
			throw new \Exception("Model name may not be empty");
		
		$shortName = ucfirst(self::getShortName($modelName));
		
		$model = $this->findSingleByAttribute('name', $shortName);
		if($model)
			return $model->id;
		
		//Use new framework EntityType
		$modelName::entityType();
		
		return $this->findByModelName($modelName);
	}
	
	private static function getShortName($cls) {		
		return lcfirst(substr($cls, strrpos($cls, '\\') + 1));
	}
	
	/**
	 * @deprecated since 6.3
	 * Added to be backwards compatible
	 * 
	 * @return name
	 */
	public function getModel_name(){
		if($this->moduleRel->package) {
			
			switch($this->name) {
				
				case 'user':
					//todo
					break;
				
				default:
					return 'go\\modules\\' . $this->moduleRel->package . '\\' . $this->moduleRel->name . '\\model\\' . ucfirst($this->name);
			}			
		} else
		{
			switch($this->name) {
				
				case 'user':
					return User::class;
									
				default:
					return 'GO\\' . ucfirst($this->moduleRel->name) . '\\Model\\' . ucfirst($this->name);
			}
		}
		return $this->name;
	}
	
//  Database check is no longer valid for 6.3 since name does not contain full namespace anymore.
//	public function checkDatabase() {
//		
//		//delete if module is no longer installed. This should happen automatically
//		//after module uninstall but in some cases this went wrong.
//		$parts = explode('\\',$this->name);
//		$module = strtolower($parts[1]);
//		if($module!='base' && !\GO::modules()->isInstalled($module)){
//			$this->delete();
//		}else
//		{		
//			return parent::checkDatabase();
//		}
//	}
}
