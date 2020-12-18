<?php
namespace go\core\model;

use go\core\App;
use go\core\db\Criteria;
use go\core\orm\Entity;
use go\core\jmap\Entity as JmapEntity;
use go\core\model\Group;
use go\core\model\User;
use go\core\orm\Query;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\core\exception\Forbidden;

/**
 * The Acl class
 * 
 * Is an Access Control List to restrict access to data.
 */
class Acl extends Entity {
	
	const LEVEL_READ = 10;
	const LEVEL_CREATE = 20;
	const LEVEL_WRITE = 30;
	const LEVEL_DELETE = 40;
	const LEVEL_MANAGE = 50;
	
	
	public $id;
	
	/**
	 * The table.field this aclId is used in
	 * 
	 * @var string
	 */
	public $usedIn;
	
	/**
	 * The user that owns the ACL
	 * @var int
	 */
	public $ownedBy;
	
	/**
	 * Modification time
	 * 
	 * @var DateTime
	 */
	public $modifiedAt;

	/**
	 * The entity type this ACL belongs to.
	 * 
	 * @var int
	 */
	public $entityTypeId;

	/**
	 * The ID of the entity this ACL belongs to.
	 * 
	 * @var int
	 */
	public $entityId;
	
	/**
	 * The list of groups that have access
	 * 
	 * @var AclGroup[] 
	 */
	public $groups = [];
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_acl')
						->addArray('groups', AclGroup::class, ['id' => 'aclId']);
	}
	
	protected function internalValidate() {
		
		if($this->isModified(['groups']) && !$this->hasAdmins()) {
			$this->setValidationError('groups', ErrorCode::FORBIDDEN, "You can't change the admin permissions");
		}
			
		return parent::internalValidate();
	}
		
	
	protected function internalSave() {		
		
		$adminLevel = $this->hasGroup(Group::ID_ADMINS);
		if($adminLevel < self::LEVEL_MANAGE) {
			$this->removeGroup(Group::ID_ADMINS);
			$this->addGroup(Group::ID_ADMINS, self::LEVEL_MANAGE);
		}

		if(!isset($this->ownedBy)) {
			$this->ownedBy = User::ID_SUPER_ADMIN;
		}
		
		if($this->ownedBy != User::ID_SUPER_ADMIN) {

			$groupId = Group::findPersonalGroupID($this->ownedBy);
			$ownerLevel = $this->hasGroup($groupId);
			if($ownerLevel < self::LEVEL_MANAGE) {
				$this->removeGroup($groupId);
				$this->addGroup($groupId, self::LEVEL_MANAGE);				
			}
		}		
		
		if(!parent::internalSave()) {
			return false;
		}
		
		return $this->logChanges();		
	}
	
	private function hasAdmins() {
		foreach($this->groups as $group) {
			if($group->groupId == Group::ID_ADMINS) {				
				return $group->level == Acl::LEVEL_MANAGE;
			}
		}

		return false;
	}
	
	private function logChanges() {
		
		if(!JmapEntity::$trackChanges) {
			return true;
		}
		
		$modified = $this->getModified(['groups']);
		
		if(!isset($modified['groups'])) {
			return true;
		}
		
		$currentGroupIds = array_column($modified['groups'][0], 'groupId');
		$oldGroupIds = array_column($modified['groups'][1], 'groupId');
		
		$addedGroupIds = array_diff($currentGroupIds, $oldGroupIds);
		$removedGroupIds = array_diff($oldGroupIds, $currentGroupIds);
	
		if(empty($addedGroupIds) && empty($removedGroupIds)) {
			return true;
		}
		
		$modSeq = Acl::entityType()->nextModSeq();
		
		foreach($addedGroupIds as $groupId) {
			$success = App::get()->getDbConnection()
							->insert('core_acl_group_changes', 
											[
													'aclId' => $this->id, 
													'groupId' => $groupId, 
													'grantModSeq' => $modSeq,
													'revokeModSeq' => null
											]
											)->execute();
			if(!$success) {
				return false;
			}
		}
		
		foreach ($removedGroupIds as $groupId) {
			$success = App::get()->getDbConnection()
						->update('core_acl_group_changes', 
										[												
											'revokeModSeq' => $modSeq											
										],
										[
											'aclId' => $this->id, 
											'groupId' => $groupId,
											'revokeModSeq' => null
										]
										)->execute();
			if(!$success) {
				return false;
			}
		}
		
		return true;		
	}
	
	/**
	 * Add a group to the ACL
	 * 
	 * @example
	 * ```
	 * $acl->addGroup(Group::ID_INTERNAL)->save();
	 * ```
	 * 
	 * @param int $groupId
	 * @param int $level
	 * @return $this
	 */
	public function addGroup($groupId, $level = self::LEVEL_READ) {

		if(empty($level)) {
			return $this->removeGroup($groupId);
		}

		$group = $this->findGroup($groupId);
		if($group) {
			$group->level = $level;
			return $this;
		}

		$this->groups[] = (new AclGroup())
								->setValues([
										'groupId' => $groupId, 
										'level' => $level
												]);
		
		return $this;
	}
	
	/**
	 * Remove group
	 * 
	 * @param int $groupId
	 * @return $this
	 */
	public function removeGroup($groupId) {
		$this->groups = array_filter($this->groups, function($group) use ($groupId) {
			return $group->groupId != $groupId;
		});
		
		return $this;
	}
	
	/**
	 * Check if this ACL has a group
	 * 
	 * @param int $groupId
	 * @return bool|int Level
	 */
	public function hasGroup($groupId) {
		$group = $this->findGroup($groupId);
		
		return $group ? $group->level : false;
	}
	
	/**
	 * Find an AclGroup by id
	 * 
	 * @param int $groupId
	 * @return bool|AclGroup
	 */
	public function findGroup($groupId) {
		foreach($this->groups as $group) {
			if($group->groupId == $groupId) {
				return $group;
			}
		}
		
		return false;
	}
	
	/**
	 * Adds a where exists condition so only items that are readable to the current user are returned.
	 * 
	 * @param Query $query
	 * @param string $column eg. t.aclId
	 * @param int $level The required permission level
	 * @param int $userId If null then the current user is used.
	 * @param int[] $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 */
	public static function applyToQuery(Query $query, $column, $level = self::LEVEL_READ, $userId = null, $groups = null) {

		if(!isset($userId)) {
			$userId = App::get()->getAuthState() ? App::get()->getAuthState()->getUserId() : false;

			if(!$userId) {
				throw new Forbidden("Authorization required");
			}
		}

		// WHERE in
//		 $subQuery = (new Query)
//		 				->select('aclId')
//		 				->from('core_acl_group', 'acl_g');
//
//
//		 if(isset($groups)) {
//		 	$subQuery->andWhere('acl_g.groupId', 'IN', $groups);
//		 } else {
//		 	$subQuery->join('core_user_group', 'acl_u' , 'acl_u.groupId = acl_g.groupId')
//		 		->andWhere([
//		 			'acl_u.userId' => $userId
//		 					]);
//		 	}
//
//		 if($level != self::LEVEL_READ) {
//		 	$subQuery->andWhere('acl_g.level', '>=', $level);
//		 }
//
//		 $query->where($column, 'IN', $subQuery);

		//where exists
		// $subQuery = (new Query)
		// 				->select('aclId')
		// 				->from('core_acl_group', 'acl_g')
		// 				->where('acl_g.aclId = '.$column)
		// 				->join('core_user_group', 'acl_u' , 'acl_u.groupId = acl_g.groupId')
		// 				->andWhere([
		// 						'acl_u.userId' => $userId 					
		// 								]);

		// if($level != self::LEVEL_READ) {			
		// 	$subQuery->andWhere('acl_g.level', '>=', $level);
		// }
		
		// $query->whereExists(
		// 				$subQuery
		// 				);


		// join
		$on =  'acl_g.aclId = ' . $column;
		if($level != self::LEVEL_READ) {
			$on .= ' AND level >= ' .$level;
		}

		if(isset($groups)) {
			$on = (new Criteria)->where($on)->andWhere('acl_g.groups', 'IN', $groups);
		}

		$query->join('core_acl_group', 'acl_g', $on)
			->groupBy(['id']);

		if(!isset($groups)) {
			$query->join('core_user_group', 'acl_u', 'acl_u.groupId = acl_g.groupId AND acl_u.userId=' . $userId);
		}
		
	}
	
	private static $permissionLevelCache = [];
	
	/**
	 * Get the maximum permission level a user has for an ACL
	 * 
	 * @param int $aclId
	 * @param int $userId
	 * @return int See the self::LEVEL_* constants
	 */
	public static function getUserPermissionLevel($aclId, $userId) {
		
		$cacheKey = $aclId . "-" . $userId;
		if(!isset(self::$permissionLevelCache[$cacheKey])) {
			$query = (new Query())
							->selectSingleValue('MAX(level)')
							->from('core_acl_group', 'g')
							->join('core_user_group', 'u', 'g.groupId = u.groupId')
							->where(['g.aclId' => $aclId, 'u.userId' => $userId])
							->groupBy(['g.aclId']);

			self::$permissionLevelCache[$cacheKey] = (int) $query->execute()->fetch();
		}	
		
		return self::$permissionLevelCache[$cacheKey];
	}
	
	/**
	 * Get all ACL id's that have been granted since a given state
	 * 
	 * @param int $userId 
	 * @param int $sinceState	 
	 * @return Query
	 */
	public static function findGrantedSince($userId, $sinceState, Query $acls = null) {
		
		//select ag.aclId from core_acl_group ag 
		//inner join core_user_group ug on ag.groupId = ug.groupId
		//where ug.userId = 4
		//
		//and ag.aclId not in (
		//	select agc.aclId from core_acl_group_changes agc 
		//	inner join core_user_group ugc on agc.groupId = ugc.groupId
		//	where ugc.userId = 4 and agc.grantModSeq <= 3  AND (agc.revokeModSeq IS null or agc.revokeModSeq > 3)
		//
		//)

		
		return self::areGranted($userId, $acls)
						->andWhere('ag.aclId', 'NOT IN', self::wereGranted($userId, $sinceState, $acls));		
	}
	
	/**
	 * Get all ACL id's that have been revoked since a given state
	 * 
	 * @param int $userId
	 * @param int $sinceState
	 * @return Query
	 */
	public static function findRevokedSince($userId, $sinceState, Query $acls = null) {
		
		//select agc.aclId from core_acl_group_changes agc 
		//inner join core_user_group ugc on agc.groupId = ugc.groupId
		//where ugc.userId = 4 and agc.grantModSeq <= 3  AND (agc.revokeModSeq IS null or agc.revokeModSeq > 3)
		//
		//and agc.aclId not in (
		//	select ag.aclId from core_acl_group ag 
		//	inner join core_user_group ug on ag.groupId = ug.groupId
		//	where ug.userId = 4
		//)
		
		return self::wereGranted($userId, $sinceState, $acls)
						->andWhere('agc.aclId', 'NOT IN', self::areGranted($userId, $acls));		
	}
	

	
	/**
	 * Get all the Acl IDs that currently include read permissions for the given user.
	 * 
	 * @param int $userId
	 * @param Query $acls Only check the given ACL's. This query should select a single column returning ACL ids.
	 * @return Query
	 */
	public static function areGranted($userId, Query $acls = null) {
		$query = (new Query())						
						->selectSingleValue('ag.aclId')
						->from('core_acl_group', 'ag')
						->join('core_user_group', 'ug', 'ag.groupId = ug.groupId')
						->where('ug.userId', '=', $userId)
						->groupBy(['ag.aclId']);
		
		if(isset($acls)) {
			$query->andWhere('ag.aclId', 'IN', $acls);
		}
		
		return $query;
	}
	
	/**
	 * Get all the Acl IDs that include read permissions for the given user at a given state in the past.
	 * 
	 * @param int $userId
	 * @param stirng $sinceState The state
	 * @param Query $acls Only check the given ACL's. This query should select a single column returning ACL ids.
	 * @return Query
	 */
	public static function wereGranted($userId, $sinceState, Query $acls = null) {
		$query = (new Query())
						->selectSingleValue('agc.aclId')
						->from('core_acl_group_changes', 'agc')
						->join('core_user_group', 'ugc', 'agc.groupId = ugc.groupId')
						->where('ugc.userId', '=', $userId)
						->andWhere('agc.grantModSeq', '<=', $sinceState)
						->andWhere(
										(new Criteria())
										->where('agc.revokeModSeq', 'IS', NULL)
										->orWhere('agc.revokeModSeq', '>', $sinceState)
										)
						->groupBy(['agc.aclId']);
		
		if(isset($acls)) {
			$query->andWhere('agc.aclId', 'IN', $acls);
		}
		
		return $query;
	}


	/**
	 * Get the ACL that can be used to make things read only for everyone.
	 * 
	 * @return static
	 */
	public static function getReadOnlyAcl(){
		
		$acl = static::find()->where(['usedIn' => 'readonly'])->single();
		
		if(!$acl){
			$acl = new static();
			$acl->ownedBy = 1;
			$acl->usedIn='readonly';
			$acl->addGroup(Group::ID_EVERYONE);
			if(!$acl->save()) {
				throw new \Exception("Couldn't save read only acl: " . var_export($acl->getValidationErrors(), true));
			}
		}
		
		return $acl;
	}
}
