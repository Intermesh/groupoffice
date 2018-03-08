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

/**
 * 
 * The Group model
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl>
 * @package GO.base.model
 * 
 * @property int $acl_id
 * @property int $user_id
 * @property int $group_id
 * @property int $level {@see Acl::READ_PERMISSION etc}
 */

namespace GO\Base\Model;


class AclUsersGroups extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return AclUsersGroups 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'go_acl';
	}
	
	/**
	 * The ACL record itself never has an ACL field so always return false
	 * @return boolean
	 */
	public function aclField() {
	  return false;
	}
  
  public function primaryKey() {
    return array('acl_id','user_id','group_id');
  }
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['user_id'] = 0;
		return $attr;
	}
	
	public function relations() {
		return array('aclItem'=>array(
			"type"=>self::BELONGS_TO,
			"model"=>"GO\Base\Model\Acl",
			"field"=>'acl_id'
		));
	}
	
	protected function afterDelete() {
		
		if($this->aclItem){
			$this->aclItem->touch();
		}
		
		return parent::afterDelete();
	}
	
	protected function afterSave($wasNew) {
		if($this->aclItem){
			//Add log message for activitylog here
			if(\GO::modules()->isInstalled("log")){
				\GO\Log\Model\Log::create("acl", $this->aclItem->description,$this->aclItem->className(),$this->aclItem->id);
			}
		
			$this->aclItem->touch();
		}
		
		return parent::afterSave($wasNew);
	}
}