<?php
/**
 * 
 * The EnableWhitelist model
 * 
 * @property int $group_id
 */


namespace GO\Ipwhitelist\Model;


class EnableWhitelist extends \GO\Base\Db\ActiveRecord{
		 
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function primaryKey() {
		return 'group_id';
	}
	
//	public function aclField(){
//		return 'acl_id';	
//	}
	
	public function tableName(){
		return 'wl_enabled_groups';
	}
		
//	public function relations(){
//		return array(
//				'contacts' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'addressbook_id', 'delete'=>true),
//				'companies' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'addressbook_id', 'delete'=>true)
//		);
//	}
	
}
?>