<?php
namespace go\core\model;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\db\Criteria;
use go\core\orm\Entity;
use go\core\jmap\Entity as JmapEntity;
use go\core\orm\EntityType;
use go\core\orm\exception\SaveException;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\DateTime;
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

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable('core_acl')
						->addArray('groups', AclGroup::class, ['id' => 'aclId']);
	}

	
	protected function internalSave(): bool
	{

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

	/**
	 * @throws Exception
	 */
	private function logChanges(): bool
	{
		
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
	 * @param int $groupId
	 * @param int|null $level
	 * @return $this
	 *
	 * @example
	 * ```
	 * $acl->addGroup(Group::ID_INTERNAL)->save();
	 * ```
	 */
	public function addGroup(int $groupId, ?int $level = self::LEVEL_READ): Acl
	{

		if(empty($level)) {
			return $this->removeGroup($groupId);
		}

		$group = $this->findGroup($groupId);
		if($group) {
			$group->level = $level;
			return $this;
		}

		$this->groups[] = (new AclGroup($this))
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
	public function removeGroup(int $groupId): Acl
	{
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
	public function hasGroup(int $groupId) {
		$group = $this->findGroup($groupId);
		
		return $group ? $group->level : false;
	}
	
	/**
	 * Find an AclGroup by id
	 * 
	 * @param int $groupId
	 * @return bool|AclGroup
	 */
	public function findGroup(int $groupId) {
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
	 * @param int|null $userId If null then the current user is used.
	 * @param int[]|null $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 * @throws Forbidden
	 */
	public static function applyToQuery(Query $query, string $column, int $level = self::LEVEL_READ, int $userId = null, array $groups = null) {

		if(!isset($userId)) {

			// no acl for admins
			if(go()->getAuthState()->isAdmin()) {
				return;
			}

			$userId = App::get()->getAuthState() ? App::get()->getAuthState()->getUserId() : false;

			if(!$userId) {
				throw new Forbidden("Authorization required");
			}
		} else{
			if(User::isAdminById($userId)) {
				return;
			}
		}

		// WHERE in
		 $subQuery = (new Query)
		 				->select('aclId')
		 				->from('core_acl_group', 'acl_g');


		 if(isset($groups)) {
		 	$subQuery->andWhere('acl_g.groupId', 'IN', $groups);
		 } else {
		 	$subQuery->join('core_user_group', 'acl_u' , 'acl_u.groupId = acl_g.groupId')
		 		->andWhere([
		 			'acl_u.userId' => $userId
		 					]);
		 	}

		 if($level != self::LEVEL_READ) {
		 	$subQuery->andWhere('acl_g.level', '>=', $level);
		 }

		 $query->where($column, 'IN', $subQuery);

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
//		$on =  'acl_g.aclId = ' . $column;
//		if($level != self::LEVEL_READ) {
//			$on .= ' AND level >= ' .$level;
//		}
//
//		if(isset($groups)) {
//			$on = (new Criteria)->where($on)->andWhere('acl_g.groups', 'IN', $groups);
//		}
//
//		$query->join('core_acl_group', 'acl_g', $on)
//			->groupBy(['id']);
//
//		if(!isset($groups)) {
//			$query->join('core_user_group', 'acl_u', 'acl_u.groupId = acl_g.groupId AND acl_u.userId=' . $userId);
//		}
		
	}
	
	private static $permissionLevelCache = [];
	
	/**
	 * Get the maximum permission level a user has for an ACL
	 * 
	 * @param int $aclId
	 * @param int $userId
	 * @return int See the self::LEVEL_* constants
	 */
	public static function getUserPermissionLevel(int $aclId, ?int $userId): int
	{
		if(!isset($userId)) {
			return 0;
		}

		if(User::isAdminById($userId)) {
			return self::LEVEL_MANAGE;
		}
		
		$cacheKey = $aclId . "-" . $userId;
		if(!isset(self::$permissionLevelCache[$cacheKey])) {
			$stmt = go()->getDbConnection()->getCachedStatment('acl-getUserPermissionLevel');
			if(!$stmt) {

				$query = (new Query())
					->selectSingleValue('MAX(level)')
					->from('core_acl_group', 'g')
					->join('core_user_group', 'u', 'g.groupId = u.groupId')
					->where('g.aclId = :aclId AND u.userId = :userId')
					->groupBy(['g.aclId'])
					->bind(':aclId', $aclId)
					->bind(':userId', $userId);

				$stmt = $query->createStatement();
				go()->getDbConnection()->cacheStatement('acl-getUserPermissionLevel', $stmt);
			} else {
				$stmt->bindValue(':aclId', $aclId);
				$stmt->bindValue(':userId', $userId);
			}
			$stmt->execute();
			self::$permissionLevelCache[$cacheKey] = (int) $stmt->fetch();

			$stmt->closeCursor();
		}	
		
		return self::$permissionLevelCache[$cacheKey];
	}


	/**
	 * @return UserDisplay[]|Query
	 */
	public function findAuthorizedUsers() {
		return UserDisplay::find()
			->join('core_user_group', 'ug', 'ug.userId = u.id')
			->join('core_acl_group', 'ag', 'ag.groupId = ug.id')
			->where('ag.aclId', '=', $this->id);
	}

	/**
	 * Get all ACL id's that have been granted since a given state
	 * 
	 * @param int $userId 
	 * @param int $sinceState	 
	 * @return Query
	 */
	public static function findGrantedSince(int $userId, $sinceState, Query $acls = null): Query
	{
		
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
						->distinct()
						->selectSingleValue('ag.aclId')
						->from('core_acl_group', 'ag')
						->join('core_user_group', 'ug', 'ag.groupId = ug.groupId')
						->where('ug.userId', '=', $userId);
		
		if(isset($acls)) {
			$query->andWhere('ag.aclId', 'IN', $acls);
		}
		
		return $query;
	}
	
	/**
	 * Get all the Acl IDs that include read permissions for the given user at a given state in the past.
	 * 
	 * @param int $userId
	 * @param string $sinceState The state
	 * @param Query $acls Only check the given ACL's. This query should select a single column returning ACL ids.
	 * @return Query
	 */
	public static function wereGranted($userId, $sinceState, Query $acls = null) {
		$query = (new Query())
						->selectSingleValue('agc.aclId')
						->distinct()
						->from('core_acl_group_changes', 'agc')
						->join('core_user_group', 'ugc', 'agc.groupId = ugc.groupId')
						->where('ugc.userId', '=', $userId)
						->andWhere('agc.grantModSeq', '<=', $sinceState)
						->andWhere(
										(new Criteria())
										->where('agc.revokeModSeq', 'IS', NULL)
										->orWhere('agc.revokeModSeq', '>', $sinceState)
										);
		
		if(isset($acls)) {
			$query->andWhere('agc.aclId', 'IN', $acls);
		}
		
		return $query;
	}


	/**
	 * Get the ACL that can be used to make things read only for everyone.
	 *
	 * @return int
	 * @throws SaveException
	 */
	public static function getReadOnlyAclId() : int{

		$id = go()->getCache()->get('readonlyaclid');

		if($id) {
			return $id;
		}
		
		$acl = static::find()->where(['usedIn' => 'readonly'])->single();
		
		if(!$acl){
			$acl = new static();
			$acl->ownedBy = 1;
			$acl->usedIn='readonly';
			$acl->addGroup(Group::ID_EVERYONE);
			if(!$acl->save()) {
				throw new SaveException($acl);
			}
		}

		go()->getCache()->set('readonlyaclid', $acl->id);

		return $acl->id;
	}


	/**
	 * Get all tables referencing the acl's
	 *
	 * New framework uses foreign keys to do this but for the old ActiveRecords
	 * we use the EntityType's to find them
	 *
	 * @return array
	 * @throws Exception
	 */
	public static function getReferences(): array
	{
		$refs = self::getMapping()->getPrimaryTable()->getReferences();

		// old framework does not have foreign keys
		$entities = EntityType::findAll();
		foreach ($entities as $et) {
			$cls = $et->getClassName();
			if (!is_subclass_of($cls, ActiveRecord::class, true)) {
				continue;
			}

			if ($et->isAclOwner()) {
				$table = $cls::model()->tableName();
				$column = $cls::model()->aclField();

				$refs[] = ['table' => $table, 'column' => $column];
			}
		}

		return $refs;
	}

	public static function findStale() {
		$refs = self::getReferences();

		foreach($refs as $ref) {
			$q = 	(new Query())
				->select($ref['column'].' as aclId')
				->from($ref['table'])
				->where($ref['column'], '!=', null);

			if(!isset($refsQuery)) {
				$refsQuery = $q;
			} else {
				$refsQuery->union($q);
			}
		}

		return static::find()
			->join($refsQuery, 'refs', 'core_acl.id = refs.aclId', 'left')
			->where('refs.aclId is null');
	}
}
