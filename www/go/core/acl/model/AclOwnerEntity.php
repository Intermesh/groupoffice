<?php
namespace go\core\acl\model;

use Exception;
use go\core\model\Acl;
use go\core\App;
use go\core\orm\Query;
use go\core\db\Expression;

/**
 * The AclEntity
 * 
 * Is an entity that has an "aclId" property. The ACL is used to restrict access
 * to the entity.
 * 
 * @see Acl
 */
abstract class AclOwnerEntity extends AclEntity {
	
	/**
	 * The ID of the {@see Acl}
	 * 
	 * @var int
	 */
	protected $aclId;
	
	/**
	 * The acl entity
	 * @var Acl 
	 */
	private $acl;

	public static $aclColumnName = 'aclId';

	protected function internalSave(): bool
	{
		
		if(!isset($this->{static::$aclColumnName})) {
			$this->createAcl();
		}

		if(!$this->saveAcl()) {
			return false;
		}
		
		if(!parent::internalSave()) {
			return false;
		}

		if($this->isNew() && isset($this->acl)) {
			$this->acl->entityId = $this->id;
			if(!$this->acl->save()) {
				return false;
			}
		}

		return true;
	}

	private $aclChanges;

	/**
	 * This is set with the new and old groupLevel values
	 * 
	 * @return array [groupId => [newLevel, oldLevel]]
	 */
	protected function getAclChanges(): array
	{
		return $this->aclChanges;
	}


	/**
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function saveAcl(): bool
	{
		if(!isset($this->setAcl)) {
			return true;
		}

		$this->checkManagePermission();

		$a = $this->findAcl();

		foreach($this->setAcl as $groupId => $level) {
			$a->addGroup($groupId, $level);
		}
		
		$mod = $a->getModified(['groups']);
		if(isset($mod['groups'])) {
			$this->aclChanges = [];
			foreach($mod['groups'][0] as $new) {
				$this->aclChanges[$new->groupId] = [$new->level, null];
			}
			foreach($mod['groups'][1] as $old) {
				if(!isset($this->aclChanges[$old->groupId])) {
					$this->aclChanges[$old->groupId] = [null, $old->level];
				} else {
					$this->aclChanges[$old->groupId][1] = $old->level;
				}
			}
		}

		return $a->save();
	}

	/**
	 * Returns an array with group ID as key and permission level as value.
	 *
	 * @return array eg. ["2" => 50, "3" => 10]
	 * @throws Exception
	 */
	public function getAcl(): ?array
	{
		$a = $this->findAcl();

		if(empty($a->groups)) {
			//return null because an empty array is serialzed as [] instead of {}
			return null;
		}
		
		$acl = [];
		if($a) {
			foreach($a->groups as $group) {
				$acl[$group->groupId] = $group->level;
			}
		}

		return $acl;
	}

	protected $setAcl;

	/**
	 * Set the ACL
	 * 
	 * @param array $acl An array with group ID as key and permission level as value. eg. ["2" => 50, "3" => 10]
	 * 
	 * @example
	 * ```
	 * $addressBook->setAcl([
	 * 	Group::ID_INTERNAL => Acl::LEVEL_DELETE
	 * ]);
	 * ```
	 */
	public function setAcl(array $acl)
	{
		$this->setAcl = $acl;		
	}

	/**
	 * Permissions are set via AclOwnerEntity models through setAcl(). When this property is used it will configure the Acl models.
	 * This permission is not checked in the controller as usual but checked on save here.

	 * @return bool
	 */
	protected function checkManagePermission(): bool
	{
		return $this->hasPermissionLevel(Acl::LEVEL_MANAGE);
	}

	/**
	 * @throws Exception
	 */
	protected function createAcl() {
		
		// Copy the default one. When installing the default one can't be accessed yet.
		// When ACL has been provided by the client don't copy the default.
		if(isset($this->setAcl) || go()->getInstaller()->isInProgress()) {
			$this->acl = new Acl();
		} else
		{
			$defaultAcl = Acl::findById(static::entityType()->getDefaultAclId());		
			if($defaultAcl) {
				$this->acl = $defaultAcl->copy();
			} else{
				$this->acl = new Acl();
			}
		}

		$this->setAclProps();

		if(!$this->acl->save()) {	
			throw new Exception("Could not create ACL");
		}

		$this->{static::$aclColumnName} = $this->acl->id;
	}

	protected function isAclChanged()
	{
		return $this->isModified([static::$aclColumnName]);
	}


	/**
	 * @throws Exception
	 */
	private function setAclProps() {
		$aclColumn = $this->getMapping()->getColumn(static::$aclColumnName);

		if(!$aclColumn) {
			throw new Exception("Column aclId is required for AclOwnerEntity ". static::class);
		}

		$this->acl->usedIn = $aclColumn->table->getName() . '.' . static::$aclColumnName;
		$this->acl->ownedBy = !empty($this->createdBy) ? $this->createdBy : $this->getDefaultCreatedBy();

		try {
			$this->acl->entityTypeId = $this->entityType()->getId();
		} catch(Exception $e) {

			//During install this will throw a module not found error due to chicken / egg problem.
			//We'll fix the data with the Group::check() function in the installer.
			if(!go()->getInstaller()->isInProgress()) {
				throw $e;
			}
			$this->acl->entityTypeId = null;
		}
	}

	/**
	 * Log's deleted entities for JMAP sync
	 *
	 * @param Query $query The query to select entities in the delete statement
	 * @return boolean
	 * @throws Exception
	 */
	protected static function logDeleteChanges(Query $query): bool
	{

		$changes = clone $query;

		$tableAlias = $query->getTableAlias();

		$changes->select($tableAlias.'.id as entityId, '.$tableAlias.'.aclId, "1" as destroyed');
		
		return static::entityType()->changes($changes);
	}
	
	protected static function internalDelete(Query $query): bool
	{

		$aclsToDelete = static::getAclsToDelete($query);

		if(!parent::internalDelete($query)) {
			return false;
		}
		
		if(!empty($aclsToDelete)) {
			if(!Acl::delete((new Query)->where('id', 'IN', $aclsToDelete))) {
				throw new Exception("Could not delete ACL");
			}
		}
		
		return true;
	}

	private static $keepAcls = [];

	/**
	 * Keep acl's when deleting. This is used by the community/history module because it wasnts to take over the ACL
	 * on delete. It will remove the acl when the log entry is delete.
	 */
	public static function keepAcls() {
		self::$keepAcls[static::class] = true;
	}

	/**
	 * @param Query $query
	 * @return array
	 * @throws Exception
	 */
	protected static function getAclsToDelete(Query $query): array
	{

		if(!empty(self::$keepAcls[static::class])) {
			return [];
		}

		$q = clone $query;
		$q->select(static::$aclColumnName);
		return $q->all();

	}

	/**
	 * Get the ACL entity
	 *
	 * @return Acl
	 * @throws Exception
	 */
	public function findAcl(): ?Acl
	{
		if(empty($this->{static::$aclColumnName})) {
			return null;
		}
		if(!isset($this->acl)) {
			$this->acl = Acl::internalFind()->where(['id' => $this->{static::$aclColumnName}])->single();
		}
		
		return $this->acl;
	}
	
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	protected function internalGetPermissionLevel() : int
	{

		if($this->isNew() && !$this->{static::$aclColumnName}) {
			return parent::internalGetPermissionLevel();
		}

		if(!isset($this->permissionLevel)) {
			$this->permissionLevel = Acl::getUserPermissionLevel($this->{static::$aclColumnName}, App::get()->getAuthState()->getUserId());
		}

		return $this->permissionLevel;
	}

	/**
	 * Applies conditions to the query so that only entities with the given
	 * permission level are fetched.
	 *
	 * Note: when you join another table with an acl ID you can use Acl::applyToQuery():
	 *
	 * ```
	 * $query = User::find();
	 *
	 * $query  ->join('applications_application', 'a', 'a.createdBy = u.id')
	 * ->groupBy(['u.id']);
	 * //We don't want to use the Users acl but the applications acl.
	 * \go\core\model\Acl::applyToQuery($query, 'a.aclId');
	 *
	 * ```
	 *
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId
	 * @param int[]|null $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 * @return Query
	 * @throws Exception
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int $userId = null, array $groups = null): Query
	{
		$tables = static::getMapping()->getTables();
		$firstTable = array_shift($tables);
		$tableAlias = $firstTable->getAlias();
		Acl::applyToQuery($query, $tableAlias . '.' . static::$aclColumnName, $level, $userId, $groups);
		
		return $query;
	}

	/**
	 * Finds all aclId's for this entity
	 *
	 * This query is used in the "getFooUpdates" methods of entities to determine if any of the ACL's has been changed.
	 * If so then the server will respond that it cannot calculate the updates.
	 *
	 * @return Query
	 * @throws Exception
	 * @see \go\core\jmap\EntityController::getUpdates()
	 *
	 */
	public static function findAcls(): Query
	{
		$tables = static::getMapping()->getTables();
		$firstTable = array_shift($tables);
		return (new Query)->selectSingleValue(static::$aclColumnName)->distinct()->from($firstTable->getName());
	}
	
	public function findAclId():int {
		return $this->{static::$aclColumnName};
	}

	/**
	 * Get the table alias holding the aclId
	 * @throws Exception
	 */
	public static function getAclEntityTableAlias() {
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		return static::getMapping()->getColumn(static::$aclColumnName)->table->getAlias();
	}

	/**
	 * Check database integrity
	 * @throws Exception
	 */
	public static function check()
	{
		static::checkAcls();

		parent::check();
	}

	/**
	 * Executed when a database check is performed
	 *
	 * It registers the table and for which entity the acl is used and updates the ownedBy from
	 * createdBy if present.
	 *
	 * When ACL's are no longer used they may be cleaned up.
	 *
	 * @throws Exception
	 */
	public static function checkAcls() {
		$table = static::getMapping()->getPrimaryTable();

		//set owner and entity properties of acl
		$aclColumn = static::getMapping()->getColumn(static::$aclColumnName);

		$updates = [
			'acl.entityTypeId' => static::entityType()->getId(),
			'acl.entityId' => new Expression('entity.id'),
			'acl.usedIn' => $aclColumn->table->getName() . '.' . static::$aclColumnName
		];

		$createdByColumn = static::getMapping()->getColumn('createdBy');

		if($createdByColumn) {

			//correct deleted users. Created by sometimes doesn't have a correct foreign key

			go()->getDbConnection()->update(
				$table->getName(),
				['createdBy' => $createdByColumn->nullAllowed ? null : 1],
				(new Query())
					->where("createdBy not in (select id from core_user)"))
				->execute();

			$updates['acl.ownedBy'] = new Expression('coalesce(entity.createdBy, 1)');
		}

		$updateQuery = static::getCheckAclUpdateQuery();

		$stmt = go()->getDbConnection()->update(
			'core_acl',
			$updates,
			$updateQuery);

		if(!$stmt->execute()) {
			throw new Exception("Could not update ACL");
		}
	}

	/**
	 * Builds the query to update ACL's on the database check
	 *
	 * @return Query
	 * @throws Exception
	 */
	protected static function getCheckAclUpdateQuery() {
		$table = static::getMapping()->getPrimaryTable();
		$updateQuery = 	(new Query())
			->tableAlias('acl')
			->join($table->getName(), 'entity', 'entity.' . static::$aclColumnName . ' = acl.id');
		return $updateQuery;
	}

	protected function removeAclOnDelete() {
		return true;
	}
}
