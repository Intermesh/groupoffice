<?php
namespace go\core\acl\model;

use Exception;
use go\core\ErrorHandler;
use go\core\model\Acl;
use go\core\App;
use go\core\orm\exception\SaveException;
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

	use AclSetterTrait;

	/**
	 * The ID of the {@see Acl}
	 * 
	 * @var int
	 */
	protected $aclId;


	public static $aclColumnName = 'aclId';

	protected function internalSave(): bool
	{
		
		if(!isset($this->{static::$aclColumnName})) {
			$this->createAcl();
		}

		$this->saveAcl();

		if(!parent::internalSave()) {
			return false;
		}

		if($this->isNew() && isset($this->acl)) {
			$this->acl->entityId = $this->id;
			if(!$this->acl->save()) {
				throw new SaveException($this->acl);
			}
		}

		return true;
	}

	protected static function internalRequiredProperties(): array
	{
		$arr =  parent::internalRequiredProperties();
		$arr[] = static::$aclColumnName;
		return $arr;
	}


	/**
	 * @throws Exception
	 */
	protected function createAcl() {
		
		// Copy the default one. When installing the default one can't be accessed yet.
		// When ACL has been provided by the client don't copy the default.
		if(isset($this->setAcl) || go()->getInstaller()->isInProgress()) {
			$this->acl = new Acl();
		} else {
			$defaultAclId = static::entityType()->getDefaultAclId();
			if($defaultAclId && ($defaultAcl = Acl::findById($defaultAclId))) {
				$this->acl = $defaultAcl->copy();
			} else {
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

		$records = $changes->select($tableAlias.'.id as entityId, '.$tableAlias.'.aclId, "1" as destroyed');
		return static::entityType()->changes($records);
	}
	
	protected static function internalDelete(Query $query): bool
	{
		if(!parent::internalDelete($query)) {
			return false;
		}
		
		return true;
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

		if(!isset($this->{static::$aclColumnName})) {
			ErrorHandler::log(static::$aclColumnName .' not set for ' . static::class . '::' . $this->id());
			$this->permissionLevel = 	(go()->getAuthState() && go()->getAuthState()->isAdmin()) ? Acl::LEVEL_MANAGE : 0;
		} else if(!isset($this->permissionLevel)) {
			$this->permissionLevel =
				(go()->getAuthState() && go()->getAuthState()->isAdmin()) ?
					Acl::LEVEL_MANAGE :
					Acl::getUserPermissionLevel($this->{static::$aclColumnName}, go()->getAuthState()->getUserId());
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
//		$tables = static::getMapping()->getTables();

		$col = static::getMapping()->getColumn(static::$aclColumnName);
		$tableAlias = $col->table->getAlias();
//		$firstTable = array_shift($tables);
//		$tableAlias = $firstTable->getAlias();


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
	
	public function findAclId(): ?int {
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


	private static function checkEmptyAcls() {
		foreach(self::find(['id', static::$aclColumnName])->where(static::$aclColumnName, '=', null) as $model) {
			$model->createAcl();
			if(!$model->save()) {
				throw new SaveException($model);
			}
		}

	}

	/**
	 * Fixes broken ACL's
	 *
	 * Executed when a database check is performed
	 *
	 * It registers the table and for which entity the acl is used and updates the ownedBy from
	 * createdBy if present.
	 *
	 * 1. Adds new if missing
	 * 2. Set't the correct values for entityTypeId, entityId and usedIn
	 * 3. Copies ownedBy from createdBy if present
	 * 4. @todo: when old framework ACl is deprecated then it should add's owner to ACL if missing
	 *
	 *
	 * When ACL's are no longer used they may be cleaned up.
	 *
	 * @throws Exception
	 */
	public static function checkAcls() {

		self::checkEmptyAcls();

		$table = static::getMapping()->getPrimaryTable();

		//set owner and entity properties of acl
		$aclColumn = static::getMapping()->getColumn(static::$aclColumnName);

		$updateQuery = 	static::checkAclJoinEntityTable();
		$updateQuery->tableAlias('acl');

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

		$stmt = go()->getDbConnection()->update(
			'core_acl',
			$updates,
			$updateQuery
		);

		if(!$stmt->execute()) {
			throw new Exception("Could not update ACL");
		}
	}


	/**
	 * This function joins the enity table so that the check function can set the usedIn property on the acl.
	 * The table alias must be 'entity'.
	 *
	 * @return \go\core\db\Query|Query
	 */
	protected static function checkAclJoinEntityTable() {
		$table = static::getMapping()->getColumn(static::$aclColumnName)->getTable();
		return (new Query())
			->join($table->getName(), 'entity', 'entity.' . static::$aclColumnName . ' = acl.id');
	}
}
