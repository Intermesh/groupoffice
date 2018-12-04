<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @property int $user_id
 * @property int $acl_id
 * @property string $data
 * @property string $model_name
 */


namespace GO\Base\Model;


class AdvancedSearch extends \GO\Base\Db\ActiveRecord {
  
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return AdvancedSearch
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {
		$this->columns['data']['gotype']='html';
		return parent::init();
	}
	
	public function aclField() {
		return 'acl_id';
	}

	public function tableName() {
		return 'go_advanced_searches';
	}

	public function relations() {
		return array();
	}
	
	protected function getLocalizedName() {
		return \GO::t("Advanced search");
	}	
	
	protected function getPermissionLevelForNewModel() {
		//everybody may create new advanced searches.
		return Acl::WRITE_PERMISSION;
	}
	
	protected function beforeSave() {
		
		// Check for date field and format the value when needed
		if(\GO::modules()->isAvailable('customfields')){
			$this->_formatCustomfield(true);
		}

		return parent::beforeSave();
	}
	
	/**
	 * Format the data before it is saved and loaded
	 * Needed to format for example dates to the correct user defined format
	 * 
	 * @param boolean $toDb
	 */
	
	
	public function getData(){
	
		
		return $this->data;
	}
	
}

