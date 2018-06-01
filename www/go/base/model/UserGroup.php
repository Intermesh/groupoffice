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
 * The UserGroup model
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z wsmits $
 * @copyright Copyright Intermesh
 * @author Wesley Smits <wsmits@intermesh.nl>
 * 
 * @package GO.base.model
 * 
 * @property int $userId
 * @property int $groupId
 */

namespace GO\Base\Model;


class UserGroup extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return UserGroup 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName() {
		return 'core_user_group';
	}
  
	public function relations() {
		return array(
			'user' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'userId'),
			'group' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\Group', 'field' => 'groupId'),
		);
	}
  public function primaryKey() {
    return array('userId','groupId');
  }
	
	private function updateAclMtime(){
		$sql = "UPDATE core_acl SET modifiedAt=now() WHERE id IN (SELECT aclId FROM core_acl_group WHERE groupId=".$this->groupId.")";		
		\GO::getDbConnection()->query($sql);
	}
	
	protected function afterSave($wasNew) {
		
		$this->updateAclMtime();
		
		return parent::afterSave($wasNew);
	}
	
	protected function beforeDelete() {
		
		if($this->groupId == \GO::config()->group_root && $this->userId == 1) {
			throw new \Exception("You can't remove the administrator from the administrators group.");
		}
		
		return parent::beforeDelete();
	}
	
	protected function afterDelete() {
		$this->updateAclMtime();
		
		return parent::afterDelete();
	}
}
