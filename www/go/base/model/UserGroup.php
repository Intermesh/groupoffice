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
 * @property int $user_id
 * @property int $group_id
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
		return 'go_users_groups';
	}
  
	public function relations() {
		return array(
			'user' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\User', 'field' => 'user_id'),
			'group' => array('type' => self::BELONGS_TO, 'model' => 'GO\Base\Model\Group', 'field' => 'group_id'),
		);
	}
  public function primaryKey() {
    return array('user_id','group_id');
  }
	
	private function updateAclMtime(){
		$sql = "UPDATE go_acl_items SET mtime=unix_timestamp() WHERE id IN (SELECT acl_id FROM go_acl WHERE group_id=".$this->group_id.")";		
		\GO::getDbConnection()->query($sql);
	}
	
	protected function afterSave($wasNew) {
		
		$this->updateAclMtime();
		
		return parent::afterSave($wasNew);
	}
	
	protected function beforeDelete() {
		
		if($this->group_id == \GO::config()->group_root && $this->user_id == 1) {
			throw new \Exception("You can't remove the administrator from the administrators group.");
		}
		
		return parent::beforeDelete();
	}
	
	protected function afterDelete() {
		$this->updateAclMtime();
		
		return parent::afterDelete();
	}
}