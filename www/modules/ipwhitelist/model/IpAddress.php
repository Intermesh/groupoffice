<?php
/**
 * 
 * The IpAddress model
 * 
 * @property int $id
 * @property String $ip_address
 * @property String $description
 * @property int $group_id
 * @property int $ctime
 * @property int $mtime
 * @property int $user_id
 * @property int $muser_id
 */


namespace GO\Ipwhitelist\Model;


class IpAddress extends \GO\Base\Db\ActiveRecord{
		 
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
//	public function aclField(){
//		return 'acl_id';	
//	}
	
	public function tableName(){
		return 'wl_ip_addresses';
	}
		
//	public function relations(){
//		return array(
//				'contacts' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Contact', 'field'=>'addressbook_id', 'delete'=>true),
//				'companies' => array('type'=>self::HAS_MANY, 'model'=>'GO\Addressbook\Model\Company', 'field'=>'addressbook_id', 'delete'=>true)
//		);
//	}
	
}
?>