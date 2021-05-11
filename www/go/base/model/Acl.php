<?php
/**
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model
 */

namespace GO\Base\Model;

/**
 * The ACL model
 * 
 * 
 * Add group to ACL:
 * 
 * replace into go_acl (acl_id, group_id, level) select acl_id,<group_id>,4 from core_user;
 * 
 * @version $Id: Group.php 7607 2011-08-04 13:41:42Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.model
 * @property string $usedIn
 * @property int $ownedBy
 * @property int $id
 * @property string $modifiedAt
 */
class Acl extends \GO\Base\Db\ActiveRecord {
	
	/**
	 * Permission level constants.
	 */
	const READ_PERMISSION=10;
	const	CREATE_PERMISSION=20;
	const WRITE_PERMISSION=30;
	const DELETE_PERMISSION=40;
	const MANAGE_PERMISSION=50;
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Acl 
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	protected function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['ownedBy'] = \go\core\model\Group::ID_ADMINS;
		
		return $attr;
	}
	
	public function getLogMessage($action) {
		
//		if($action == 'delete'){
//			$old = \GO::config()->debug;
//			
//			\GO::config()->debug = true;
//			\GO::debug('ACL '.$this->id.' '.$this->usedIn.' removed');
//			\GO::debug($_POST);
//			\GO::debugCalledFrom(10);
//			
//			\GO::config()->debug = $old;
//		}
		
		return 'ACL '.$this->usedIn;
	}

	
	public function relations() {
		return array(
				'records' => array(
						'type'=>self::HAS_MANY, 
						'model'=>'GO\Base\Model\AclUsersGroups', 
						'field'=>'aclId', 
						'delete'=>self::DELETE_CASCADE),
		);
	}
	

	/**
	 * Check for permissionlevel and return a boolean if it's OK or not
	 * 
	 * @param int $level The permissionlevel that is currently in use
	 * @param int $requiredLevel The minimal permissionlevel that is needed
	 * @return boolean Has permission or not 
	 */
	public static function hasPermission($level, $requiredLevel) {
		return $level>=$requiredLevel;
	}
	
	public function tableName(){
		return "core_acl";
	}
	
	/**
	 * Return the permission level that a user has for this ACL.
	 *  
	 * @param in $acl_id The ID of the acl to check
	 * @param int $userId If omitted then it will check the currently logged in user and return manage permission if \GO::$ignoreAclPermissions is set.
	 * @param bool $checkGroupPermissionOnly
	 * @return int Permission level. See constants in Acl for values. 
	 */
	public static function getUserPermissionLevel($aclId, $userId=false, $checkGroupPermissionOnly=false) {
		
		if(\go\core\model\User::isAdminById($userId ? $userId : \GO::user()->id)) {
			return self::MANAGE_PERMISSION;
		}
		
		//only ignore when no explicit user is checked. Otherwise you can never check the real permissionlevel when \GO::$ignoreAclPermissions is set to true.
		if(\GO::$ignoreAclPermissions && $userId===false)
			return self::MANAGE_PERMISSION;
		
		if($userId===false){
			if(\GO::user())
				$userId=\GO::user()->id;
			else
				return false;
		}
		
		$bindParams = array(':acl_id'=>$aclId, ':user_id1'=>$userId);
		$where = 't.aclId=:acl_id AND ug.userId=:user_id1';
		
		
		$findParams=array(
			'join'=>"LEFT JOIN core_user_group ug ON t.groupId=ug.groupId",
			'where'=>$where,
			'order'=>'t.level',
			'orderDirection'=>'DESC',
			'bindParams'=>$bindParams
		);
		
		if($checkGroupPermissionOnly) {
			$findParams['join'] .= ' INNER JOIN core_group g ON ug.groupId = g.id';
			$findParams['where'] .= ' AND isUserGroupFor IS NULL';
		}
		
		$model = AclUsersGroups::model()->findSingle($findParams);
		if($model)
			return intval($model->level);
		else 
			return false;
	}
	
	/**
	 * Return the permission level that a user has for this ACL.
	 *  
	 * @param int $userId If omitted then it will check the currently logged in user and return manage permission if \GO::$ignoreAclPermissions is set.
	 * @param bool $checkGroupPermissionOnly
	 * @return int Permission level. See constants in Acl for values. 
	 */
	public function getUserLevel($userId = false){
		return Acl::getUserPermissionLevel($this->id, $userId);
	}

	

	/**
	 * Add a group to the ACL with a permission level.
	 *  
	 * @param int $groupId
	 * @param int $level See constants in Acl for values. 
	 * @return bool True on success
	 */
	public function addGroup($groupId, $level=Acl::READ_PERMISSION) {	
		if($groupId<1)
			return false;
		
		if($groupId==\GO::config()->group_root)
			$level = Acl::MANAGE_PERMISSION;
		
		$usersGroup = $this->hasGroup($groupId);

		if($usersGroup){
			if($level>0){
				$usersGroup->level=$level;			
				return !$usersGroup->isModified() || $usersGroup->save();
			}else{
				return $usersGroup->delete();
			}
		}else
		{	
			if($level==0)
				return true;
			
			$usersGroup = new AclUsersGroups();
			$usersGroup->aclId = $this->id;
			$usersGroup->groupId = $groupId;
			$usersGroup->level = $level;
			return $usersGroup->save();
		}
	}
	
	/**
	 * Returns the links table model if the acl has the group
	 * 
	 * @param int $groupId
	 * @return AclUsersGroups 
	 */
	public function hasGroup($groupId){
		return AclUsersGroups::model()->findByPk(array(
				'aclId'=>$this->id,
				'groupId'=>$groupId
						));
	}
	



	/**
	 * Remove a group from the ACL
	 * 
	 * @param int $groupId
	 * @return bool 
	 */
	public function removeGroup($groupId) {
		
		if($groupId==\GO::config()->group_root)
			return false;
		
		$model = $this->hasGroup($groupId);
		if($model)
			return $model->delete();
		else
			return true;
	}

	protected function afterSave($wasNew) {

		if($wasNew){
			if($this->usedIn!='readonly'){
				if($this->ownedBy != 1) { //not for admin
					$userGroup = Group::model()->findSingleByAttribute('isUserGroupFor', $this->ownedBy);
					if($userGroup) {
						$this->addGroup($userGroup->id, Acl::MANAGE_PERMISSION);						
					}
				}
			}
		}elseif($this->isModified('ownedBy')){
			$group = Group::model()->findSingleByAttribute('isUserGroupFor', $this->ownedBy);
			if(!empty($group)) {
				$this->addGroup($group->id, Acl::MANAGE_PERMISSION);
			}
		}

		return parent::afterSave($wasNew);
	}
	
	
	
	/**
	 * Get all groups in this acl. The group models will contain an extra
	 * permission_level property.
	 * 
	 * @return \GO\Base\Db\ActiveStatement 
	 */
	public function getGroups(){
		
		$findParams = \GO\Base\Db\FindParams::newInstance()
						->select('t.*,a.level as permission_level')
						->joinModel(array(
								'model'=>"GO\Base\Model\AclUsersGroups",
								'foreignField'=>'groupId',
								'tableAlias'=>'a'								
						));
		
		$findParams->getCriteria()->addCondition('aclId', $this->id, '=','a');
		
		return Group::model()->find($findParams);
	}
	
	/**
	 * Copy the permissions of this acl to another.
	 * 
	 * @param Acl $targetAcl
	 */
	public function copyPermissions(Acl $targetAcl){
		//$this->duplicateRelation('records', $targetAcl);
		
		$stmt = $this->records;
		foreach($stmt as $r){
			if($r->groupId){
				$targetAcl->addGroup($r->groupId, $r->level);
			}
		}
	}
		
	
	/**
	 * Get all users that have access to an acl.
	 * 
	 * @param int $aclId
	 * @param int $level 
	 * @params Array $callback Call a function with the user as argument. 
	 * This was added to save memory so that not all users have to be in memory. 
	 * If you pass this argument this function will return void.
	 * @return Array of User 
	 */
	public static function getAuthorizedUsers($aclId, $level=Acl::READ_PERMISSION, $callback=false, $callbackArguments=array()){
		

		
		$joinCriteria  = \GO\Base\Db\FindCriteria::newInstance()
										->addModel(AclUsersGroups::model(),'a')										
										->addCondition('groupId', 'ug.groupId','=','a',true,true)
										->addCondition('aclId', $aclId,'=','a');
		
		if($level > Acl::READ_PERMISSION) {
			$joinCriteria->addCondition('level', $level,'>=','a');
		}
		
		
		$stmt =  User::model()->find(\GO\Base\Db\FindParams::newInstance()				
						->ignoreAcl()
						->join(UserGroup::model()->tableName(),  \GO\Base\Db\FindCriteria::newInstance()		
										->addCondition('id', 'ug.userId','=','t',true,true),
										'ug')
						->join(AclUsersGroups::model()->tableName(),$joinCriteria
										,'a')
						->order('a.level')
						->group('t.id'));

		$users = [];
		
		while($user = $stmt->fetch()) {				
			if($callback){
				call_user_func_array($callback, array_merge(array($user), $callbackArguments));
			} else {
				$users[]=$user;
			}
		}
		
		if(!$callback)
			return $users;
	}
	
	
	
	public function checkDatabase() {
		
		if(empty($this->ownedBy))
			$this->ownedBy = 1;

		if($this->usedIn!='readonly'){
			if($this->ownedBy != 1) { //not for admin
				$group = Group::model()->findSingleByAttribute('isUserGroupFor', $this->ownedBy);
				if(empty($group)) {
					$this->ownedBy = 1;
					$this->save();
				} else {
					$this->addGroup($group->id, Acl::MANAGE_PERMISSION);
				}
			}
		}
		
		return parent::checkDatabase();
	}
	
	/**
	 * 
	 * /!\ Be careful when using this!
	 * 
	 * Makes sure that this ACL's permissions are only the manage permissions for
	 * the admin user and admin group. Other permissions will be deleted.
	 */
	public function clear(){
		
		if (!\GO::user()->isAdmin())
			throw new \GO\Base\Exception\AccessDenied();
		
		$adminGroupRecordExists = false;
		
		foreach ($this->records as $aclRecord) {
			
			if($aclRecord->groupId==\GO::config()->group_root) {
				$adminGroupRecordExists=true;
			} else {
				$aclRecord->delete();
			}
			
		}

		if($this->usedIn!='readonly'){
			if (!$adminGroupRecordExists) {
				$aclRecord = new AclUsersGroups();
				$aclRecord->aclId = $this->id;
				$aclRecord->groupId = \GO::config()->group_root;
				$aclRecord->level = Acl::MANAGE_PERMISSION;
				$aclRecord->save();
			}
		}		
	}
	
	/**
	 * Get the ACL that can be used to make things read only for everyone.
	 * 
	 * @deprecated
	 * @return \Acl
	 */
	public function getReadOnlyAcl(){
		$acl_id = \GO::config()->get_setting('readonly_acl_id');
		
		$acl = Acl::model()->findByPk($acl_id);
		
		if(!$acl){
			$acl = new Acl();
			$acl->usedIn='readonly';
			$acl->save();
			
			$acl->addGroup(\GO::config()->group_everyone, Acl::READ_PERMISSION);			
			\GO::config()->save_setting('readonly_acl_id', $acl->id);
		}
		
		return $acl;
	}
	
	/**
	 * For backwards compatibility
	 * 
	 * @return int
	 */
	public function getMtime() {
		return strtotime($this->modifiedAt);
	}
}
