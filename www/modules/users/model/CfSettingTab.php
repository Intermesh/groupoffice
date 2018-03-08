<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */
 
/**
 * The CfSettingTab model
 *
 * @package GO.modules.Users
 * @version $Id: CfSettingTab.php 7607 2011-11-03 10:28:32Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 *
 * @property int $cf_category_id
 */


namespace GO\Users\Model;


class CfSettingTab extends \GO\Base\Db\ActiveRecord{

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return \GO\Ads\Model\Format
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}


	public function primaryKey() {
		return 'cf_category_id';
	}
	
	/**
	 * Enable this function if you want this model to check the acl's automatically.
	 */
	// public function aclField(){
	//	 return 'acl_id';	
	// }

	/**
	 * Returns the table name
	 */
	 public function tableName() {
		 return 'go_cf_setting_tabs';
	 }

	/**
	 * Here you can define the relations of this model with other models.
	 * See the parent class for a more detailed description of the relations.
	 */
	 public function relations() {
		 return array();
	 }
	 
	 /**
	  * Get an activestatement witch includes all the customfieldCategories that 
	  * will be showed in the settings tab.
	  * 
	  * @return \GO\Base\Db\ActiveStatement
	  */
	 public function getSettingTabs(){
		 		 
		 $findParams = \GO\Base\Db\FindParams::newInstance()
						 ->ignoreAcl()
						 ->joinModel(array(
								'model'=>'GO\Users\Model\CfSettingTab',
								'localTableAlias'=>'t', //defaults to "t"
								'localField'=>'id', //defaults to "id"			
								'foreignField'=>'cf_category_id', //defaults to primary key of the remote model
								'tableAlias'=>'cfs', //Optional table alias					
								'type'=>'INNER' //defaults to INNER,
						 ))
						 ->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('extends_model', "GO\Addressbook\Model\Contact"))
						 ->order('sort_index');
		 
		 $stmt = \GO\Customfields\Model\Category::model()->find($findParams);
		 return $stmt;
	 }
	 
	 
	 
	 
}