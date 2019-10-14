<?php
namespace go\core\acl\model;

use go\core\App;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\orm\Property;
use go\core\util\DateTime;
use go\modules\core\groups\model\Group;
use go\modules\core\users\model\User;

/**
 * The Acl class
 * 
 * Is an Access Control List to restrict access to data.
 */
class Acl extends Property {
	
	const LEVEL_READ = 10;
	const LEVEL_CREATE = 20;
	const LEVEL_WRITE = 30;
	const LEVEL_DELETE = 40;
	const LEVEL_MANAGE =50;
	
	
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
	 * The list of groups that have access
	 * 
	 * @var AclGroup[] 
	 */
	public $groups = [];
	
	protected static function defineMapping() {
		return parent::defineMapping()
						->addTable('core_acl')
						->addRelation('groups', AclGroup::class, ['id' => 'aclId'], true);
	}
	
	
	protected function internalSave() {
		
		if($this->isNew() && empty($this->groups)) {		
					
			$this->addGroup(Group::ID_ADMINS, self::LEVEL_MANAGE);
			
			if($this->ownedBy != User::ID_SUPER_ADMIN) {
				
				$groupId = Group::find()
								->where(['isUserGroupFor' => $this->ownedBy])
								->selectSingleValue('id')
								->single();
				
				$this->addGroup($groupId, self::LEVEL_MANAGE);
			}
		} else
		{
			$adminLevel = $this->hasGroup(Group::ID_ADMINS);
			if($adminLevel < self::LEVEL_MANAGE) {
				$this->removeGroup(Group::ID_ADMINS);
				$this->addGroup(Group::ID_ADMINS, self::LEVEL_MANAGE);
			}
		}
		
		return parent::internalSave();
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
	 * @return boolean|int Level
	 */
	public function hasGroup($groupId) {
		foreach($this->groups as $group) {
			if($group->groupId == $groupId) {
				return $group->level;
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
	 */
	public static function applyToQuery(Query $query, $column, $level = self::LEVEL_READ) {
		
		$subQuery = (new Query)
						->select('aclId')
						->from('core_acl_group', 'acl_g')
						->where('acl_g.aclId = '.$column)
						->join('core_user_group', 'acl_u' , 'acl_u.groupId = acl_g.groupId')
						->andWhere([
								'acl_u.userId' => App::get()->getAuthState()->getUserId()						
										]);

		if($level != self::LEVEL_READ) {
			$subQuery->andWhere('acl_g.level', '>=', $level);
		}
		
		$query->whereExists(
						$subQuery
						);
	}
	
	private static $permissionLevelCache = [];
	
	/**
	 * Get the maximum permission level a user has for an ACL
	 * 
	 * @param int $aclId
	 * @param int $userId
	 * @return int See the self::LEVEL_* constants
	 */
	public static function getPermissionLevel($aclId, $userId) {
		
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
		
		App::get()->debug("Permission level ($cacheKey) = " . self::$permissionLevelCache[$cacheKey]);
		
		return self::$permissionLevelCache[$cacheKey];
	}
	
	/**
	 * Get all ACL id's that have been granted since a given state
	 * 
	 * @param int $userId 
	 * @param int $state	 
	 * @return Query
	 */
	public static function findGrantedSince($userId, $state, Query $acls = null) {
		
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

		
		return self::getCurrentAclGroups($userId, $acls)
						->andWhere('ag.aclId', 'NOT IN', self::getOldAclGroups($userId, $state, $acls));		
	}
	
	/**
	 * Get all ACL id's that have been revoked since a given state
	 * 
	 * @param int $userId
	 * @param int $state
	 * @return Query
	 */
	public static function findRevokedSince($userId, $state, Query $acls = null) {
		
		//select agc.aclId from core_acl_group_changes agc 
		//inner join core_user_group ugc on agc.groupId = ugc.groupId
		//where ugc.userId = 4 and agc.grantModSeq <= 3  AND (agc.revokeModSeq IS null or agc.revokeModSeq > 3)
		//
		//and agc.aclId not in (
		//	select ag.aclId from core_acl_group ag 
		//	inner join core_user_group ug on ag.groupId = ug.groupId
		//	where ug.userId = 4
		//)
		
		return self::getOldAclGroups($userId, $state, $acls)
						->andWhere('agc.aclId', 'NOT IN', self::getCurrentAclGroups($userId, $acls));		
	}
	
	 
	/**
	 * 
	 * @param int $userId
	 * @return Query
	 */
	private static function getCurrentAclGroups($userId, Query $acls = null) {
		$query = (new Query())
						->selectSingleValue('ag.aclId')
						->from('core_acl_group', 'ag')
						->join('core_user_group', 'ug', 'ag.groupId = ug.groupId')
						->where('ug.userId', '=', $userId);
		
		if(isset($acls)) {
			$query->andWhere('ag.aclId', 'IN', $acls);
		}
		
		return $query;
	}
	
	private static function getOldAclGroups($userId, $state, Query $acls = null) {
		$query = (new Query())
						->selectSingleValue('agc.aclId')
						->from('core_acl_group_changes', 'agc')
						->join('core_user_group', 'ugc', 'agc.groupId = ugc.groupId')
						->where('ugc.userId', '=', $userId)
						->andWhere('agc.grantModSeq', '<', $state)
						->andWhere(
										(new Criteria())
										->where('agc.revokeModSeq', 'IS', NULL)
										->orWhere('agc.revokeModSeq', '>', $state)
										);
		
		if(isset($acls)) {
			$query->andWhere('agc.aclId', 'IN', $acls);
		}
		
		return $query;
	}
	
		
	
	
	/**
	 * @todo TEMPORARY HACK REMOVE IN MASTER
	 * @return type
	 */
	public function save() {
		return $this->internalSave();
	}
}
