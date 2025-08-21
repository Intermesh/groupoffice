<?php
namespace go\core\model;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\App;
use go\core\db\Criteria;
use go\core\db\DbException;
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
 *
 * @example
 * ```
 * $acl = new Acl();
 * $acl->ownedBy = 1;
 * $acl->usedIn = "some_table";
 * $acl->save();
 * ```
 */
class Acl extends Entity {

	const LEVEL_READ = 10;
	const LEVEL_CREATE = 20;
	const LEVEL_WRITE = 30;
	const LEVEL_DELETE = 40;
	const LEVEL_MANAGE = 50;
	
	
	public int $id;
	
	/**
	 * The table.field this aclId is used in
	 * 
	 * @var ?string
	 */
	public ?string $usedIn;
	
	/**
	 * The user that owns the ACL
	 * @var int
	 */
	public int $ownedBy;
	
	/**
	 * Modification time
	 */
	public ?\DateTimeInterface $modifiedAt;

	/**
	 * The entity type this ACL belongs to.
	 * 
	 * @var ?int
	 */
	public ?int $entityTypeId;

	/**
	 * The ID of the entity this ACL belongs to.
	 * 
	 * @var ?int
	 */
	public ?int $entityId;
	
	/**
	 * The list of groups that have access
	 * 
	 * @var AclGroup[] 
	 */
	public array $groups = [];

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
													'modSeq' => $modSeq,
													'granted' => true
											]
											)->execute();
			if(!$success) {
				return false;
			}
		}
		
		foreach ($removedGroupIds as $groupId) {
			$success = App::get()->getDbConnection()
				->insert('core_acl_group_changes',
					[
						'aclId' => $this->id,
						'groupId' => $groupId,
						'modSeq' => $modSeq,
						'granted' => false
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
	public static function applyToQuery(Query $query, string $column, int $level = self::LEVEL_READ, int|null $userId = null, array|null $groups = null): void
	{

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
		$subQuery->andWhere('acl_g.level', '>=', $level);
		$query->where($column, 'IN', $subQuery);
		
	}
	
	private static array $permissionLevelCache = [];
	
	/**
	 * Get the maximum permission level a user has for an ACL
	 * 
	 * @param int $aclId
	 * @param ?int $userId
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
	 * Get ACL changes
	 *
	 * @param int $userId
	 * @param string $sinceState
	 * @param Query|null $acls
	 * @return array<boolean> AclId as key and granted as value.
	 */
	public static function changeLog(int $userId, string $sinceState, Query|null $acls = null): array {
		$query = (new Query())
			->select('agc.aclId, granted')
			->from('core_acl_group_changes', 'agc')
			->join('core_user_group', 'ugc', 'agc.groupId = ugc.groupId')
			->where('ugc.userId', '=', $userId)
			->andWhere('agc.modSeq', '>', $sinceState);

		if(isset($acls)) {
			$query->andWhere('agc.aclId', 'IN', $acls);
		}

		go()->debug($query);

		$map = [];
		foreach($query as $rec) {
			$map[$rec['aclId']] = $rec['granted'];
		}

		go()->debug($map);
		return $map;
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

		// remove child ref
		$refs = array_filter($refs, function($r) {
			return $r['table'] != 'core_acl_group';
		});

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


	/**
	 * Copy permissions from another ACL
	 *
	 * @throws DbException
	 */
	public function copyFrom(int $sourceAclId) : void {
		go()->getDbConnection()
				->insertIgnore("core_acl_group",
					go()->getDbConnection()
						->select($this->id . " as aclId, groupId, level")
						->from("core_acl_group")
						->where(['aclId' => $sourceAclId])
				)->execute();
	}
}
