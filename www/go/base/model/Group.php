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
 * @property int $id
 * @property String $name
 * @property int $createdBy
 * @property int $aclId
 * @property int $isUserGroupFor
 * 
 * @method User users
 *
 */

namespace GO\Base\Model;

use GO;

class Group extends \GO\Base\Db\ActiveRecord {


	const GROUP_EVERYONE = 'GROUP_EVERYONE';
	const GROUP_ADMINS = 'GROUP_ADMINS';
	const GROUP_INTERNAL = 'GROUP_INTERNAL';
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Group 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function init() {		
		$this->columns['name']['unique']=true;
		return parent::init();
	}
	
	protected function getLocalizedName() {
		return \GO::t("User group");
	}
	
  public function aclField(){
		return 'aclId';	
	}
  
	public function tableName() {
		return 'core_group';
	}
	
	/**
	 * Check module permission for creating new Groups
	 * Needs overwrite because ActiveRecord check if module belongs to base
	 * @return int permission level of moduel
	 */
	protected function getPermissionLevelForNewModel(){
		return \GO::modules()->groups->permissionLevel;
	}
	
	protected function beforeDelete() {
		if($this->id==\GO::config()->group_root){
			throw new \Exception(\GO::t("You can't delete the group Admins", "groups"));
		}	
		if($this->id==\GO::config()->group_everyone){
			throw new \Exception(\GO::t("You can't delete the group Everyone", "groups"));
		}
		return parent::beforeDelete();
	}
	
	protected function afterSave($wasNew) {
		
		if($wasNew){
			$this->acl->addGroup($this->id, Acl::READ_PERMISSION);
		}
		
		return parent::afterSave($wasNew);
	}
  
//  public function searchFields() {
//    return array(
//      'concat(first_name,last_name)',
//      'username'
//      );
//  }
  
  public function relations() {
    
    return array(
				'users' => array('type'=>self::MANY_MANY, 'model'=>'GO\Base\Model\User', 'field'=>'groupId', 'linkModel' => 'GO\Base\Model\UserGroup'),
				'user_group' => array('type'=>self::HAS_MANY, 'model'=>'GO\Base\Model\UserGroup', 'field'=>'groupId'),
				'aclGroups' => array('type'=>self::HAS_MANY, 
						'model'=>'GO\Base\Model\AclUsersGroups', 
						'field'=>'groupId')
			);
  }
  
  public function addUser($user_id){
		if(!$this->hasUser($user_id)){
			$userGroup = new UserGroup();
			$userGroup->groupId = $this->id;
			$userGroup->userId = $user_id;
			return $userGroup->save();
		}else
		{
			return true;
		}
  }
	
	public function removeUser($user_id){
		$model = UserGroup::model()->findByPk(array('userId'=>$user_id, 'groupId'=>$this->pk));
		if($model)
			return $model->delete();
		else
			return true;
	}
  
  /**
   * Check if this group has a user
   * 
   * @param type $user_id
   * @return UserGroup or false 
   */
  public function hasUser($user_id){
    return UserGroup::model()->findByPk(array('userId'=>$user_id, 'groupId'=>$this->pk));
  }
	
	public function checkDatabase() {
		
		if($this->id==\GO::config()->group_everyone){
			$stmt = User::model()->find(\GO\Base\Db\FindParams::newInstance()->ignoreAcl());
			while($user = $stmt->fetch())
				$this->addUser ($user->id);
		}
		
		if($this->id==\GO::config()->group_root){
			$this->addUser(1);
		}
		
		return parent::checkDatabase();
	}
	
	/**
	 * 
	 * @param String $groupName
	 * @return self
	 */
	public function findByName($groupName) {
		
			switch (trim($groupName)) {
				case Group::GROUP_EVERYONE:
					$group = Group::model()->findByPk(GO::config()->group_everyone);
					break;
				case Group::GROUP_ADMINS:
					$group = Group::model()->findByPk(GO::config()->group_root);
					break;
				case Group::GROUP_INTERNAL:
					$group = Group::model()->findByPk(GO::config()->group_internal);
					break;
				default:
					$group = Group::model()->findSingleByAttribute('name', trim($groupName));
					break;
			}
			return $group;
		
	}
  
}
