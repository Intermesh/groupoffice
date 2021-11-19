<?php

namespace go\core\acl\model;

use Exception;
use go\core\exception\Forbidden;
use go\core\model\Acl;
use go\core\model\User;
use go\core\orm\Query;
use go\core\db\Query as DbQuery;
use go\core\jmap\EntityController;
use go\core\jmap\Entity;

/**
 * The AclItemEntity class
 * 
 * Is used for items that belong to an entity which is an {@see AclEntity}.
 * For examples a Note is an AclItemEntity because it belongs to the NoteBook AclEntity.
 * 
 * It's main purpose is to provide the {@see applyAclToQuery()} function so you 
 * can easily query items which a user has read permissions for.
 * 
 * You can also specify another AclItemEntity so it will recurse.
 * 
 * @see AclOwnerEntity
 */
abstract class AclItemEntity extends AclEntity {

	/**
	 * Fires when the ACL has changed.
	 *
	 * Not when changes were made to the acl but when the complete list has been replaced when for example
	 * a contact has been moved to another address book.	 *
	 */
	const EVENT_ACL_CHANGED = 'aclchanged';

	/**
	 * Get the {@see AclOwnerEntity} or {@see AclItemEntity} class name that it 
	 * depends on.
	 * 
	 * @return string 
	 */
	abstract protected static function aclEntityClass();

	/**
	 * Get the keys for joining the aclEntityClass table.
	 * 
	 * @return array eg. ['folderId' => 'id']
	 */
	abstract protected static function aclEntityKeys();

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 *
	 * @param Query $query
	 * @param int $level
	 * @param int $userId Defaults to current user ID
	 * @param int[] $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 * @return Query
	 * @throws Exception
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null, $groups = null) {

		if(User::isAdminById($userId ?? go()->getAuthState()->getUserId())) {
			return;
		}
		/**
		 * SELECT SQL_CALC_FOUND_ROWS SQL_NO_CACHE c.id
		FROM `addressbook_contact` `c`
		where exists (select id from `addressbook_addressbook` `a`
		INNER JOIN `core_acl_group` `acl_g` ON
		acl_g.aclId = a.aclId
		INNER JOIN `core_user_group` `acl_u` ON
		acl_u.groupId = acl_g.groupId AND acl_u.userId=1
		where a.id=c.addressBookId
		)
		ORDER BY `c`.`modifiedAt` DESC

		LIMIT 200,40
		 *
		 * 1.6s
		 *
		 *
		 *
		 * SELECT SQL_CALC_FOUND_ROWS SQL_NO_CACHE c.id
		FROM `addressbook_contact` `c`
		where addressBookId in (select id from `addressbook_addressbook` `a`
		INNER JOIN `core_acl_group` `acl_g` ON
		acl_g.aclId = a.aclId
		INNER JOIN `core_user_group` `acl_u` ON
		acl_u.groupId = acl_g.groupId AND acl_u.userId=1)
		ORDER BY `c`.`modifiedAt` DESC

		LIMIT 200,40
		 *
		 * 1.5s
		 *
		 *
		 *
		 * SELECT SQL_CALC_FOUND_ROWS SQL_NO_CACHE c.id
		FROM `addressbook_contact` `c`
		INNER JOIN `addressbook_addressbook` `a` ON
		c.addressBookId = a . id
		INNER JOIN `core_acl_group` `acl_g` ON
		acl_g.aclId = a.aclId
		INNER JOIN `core_user_group` `acl_u` ON
		acl_u.groupId = acl_g.groupId AND acl_u.userId=1
		GROUP BY `c`.`id`

		ORDER BY `c`.`modifiedAt` DESC
		LIMIT 200,40
		 *
		 * 2.6s
		 */


		//Old way (3rd query above)
//		$alias = self::joinAclEntity($query);
//		Acl::applyToQuery($query, $alias, $level, $userId, $groups);

		//using where exists
		$cls = static::aclEntityClass();

		/* @var $cls Entity */

		$subQuery = $cls::find();


		if(!isset($fromAlias)) {
			$fromAlias = $query->getTableAlias();
		}

		//Exists
//		$subQuery->selectSingleValue($subQuery->getTableAlias() . '.id');
//		foreach (static::aclEntityKeys() as $from => $to) {
//			$column = $cls::getMapping()->getColumn($to);
//
//			$subQuery->where($fromAlias . '.' . $from . ' = ' . $column->table->getAlias() . ' . '. $to);
//			$subQuery->filter(['permissionLevel' => Acl::LEVEL_READ]);
//			$subQuery->groupBy([])->select('id');
//		}
//
//		$query->whereExists($subQuery);

		//where in

		foreach (static::aclEntityKeys() as $from => $to) {
			$column = $cls::getMapping()->getColumn($to);

			$subQuery->filter(['permissionLevel' => $level]);
			$subQuery->select($column->table->getAlias() . ' . '. $to);
			$subQuery->groupBy([]);
			$query->where($fromAlias . '.' . $from, 'IN', $subQuery);
			break;
		}

		
		return $query;
	}

	/**
	 * Log's deleted entities for JMAP sync
	 *
	 * @param Query $query The query to select entities in the delete statement
	 * @return boolean
	 * @throws Exception
	 */
	protected static function logDeleteChanges(Query $query) {

		$table = self::getMapping()->getPrimaryTable();
		$changes = clone $query;
		$changes->select($table->getAlias() . '.id as entityId');

		$alias = static::joinAclEntity($changes);

		$changes->select($alias . ', "1" as destroyed', true);
	
		return static::entityType()->changes($changes);
	}

	/**
	 * Join's the ACL owner entity primary table
	 *
	 * @param DbQuery $query
	 * @param null $fromAlias
	 * @return string Alias for the acl column. For example: "addressbook.aclId"
	 * @throws Exception
	 */
	public static function joinAclEntity(DbQuery $query, $fromAlias = null) {
		$cls = static::aclEntityClass();

		/* @var $cls Entity */
		
		if(!isset($fromAlias)) {
			$fromAlias = $query->getTableAlias();
		}

		$keys = [];
		foreach (static::aclEntityKeys() as $from => $to) {
			$column = $cls::getMapping()->getColumn($to);
			
			$keys[] = $fromAlias . '.' . $from . ' = ' . $column->table->getAlias() . ' . '. $to;
		}

		// Override didn't work because on delete it did need to be joined.
//		if($query->isJoined($column->table->getName(), $column->table->getAlias())) {
//			throw new \Exception(
//				"The ACL owner table `". $column->table->getName() .
//				"` was already joined with alias `" .  $column->table->getAlias() .
//				"` in class " . static::class . ". If you joined this table via defineMapping() then override the method joinAclEntity() and return '" . $column->table->getAlias() . '.' . $cls::$aclColumnName ."'.") ;
//		}

		if(!$query->isJoined($column->table->getName(), $column->table->getAlias())) {
			$query->join($column->table->getName(), $column->table->getAlias(), implode(' AND ', $keys));
		}
		
		
		//If this is another AclItemEntity then recurse
		if(is_a($cls, AclItemEntity::class, true)) {
			return $cls::joinAclEntity($query,  $column->table->getAlias());
		} else
		{
			//otherwise this must hold the aclId column
			$aclColumn = $cls::getMapping()->getColumn($cls::$aclColumnName);
			if(!$aclColumn) {
				throw new Exception("Column 'aclId' is required for AclEntity '$cls'");
			}

			return $column->table->getAlias() . '.' . $cls::$aclColumnName;
		}
	}	

	/**
	 * Get the table alias holding the aclId
	 */
	public static function getAclEntityTableAlias() {

		$cls = static::aclEntityClass();	

		/* @var $cls Entity */
		
		//If this is another AclItemEntity then recurse
		if(is_a($cls, AclItemEntity::class, true)) {
			return $cls::getAclEntityTableAlias();
		} else
		{
			//otherwise this must hold the aclId column
			$aclColumn = $cls::getMapping()->getColumn('aclId');
			if(!$aclColumn) {
				throw new Exception("Column 'aclId' is required for AclEntity '$cls'");
			}
			
			return $aclColumn->table->getAlias();
		}
	}
	
	/**
	 * Get the entity that holds the acl id.
	 * 
	 * @return Entity
	 */
	protected function getAclEntity() {
		$cls = static::aclEntityClass();

		/* @var $cls Entity */


		$keys = [];
		foreach (static::aclEntityKeys() as $from => $to) {
			if(!isset($this->{$from})) {
				throw new Exception("Required property '".static::class."::$from' not fetched");
			}
			$keys[$to] = $this->{$from};
		}

		$aclEntity = $cls::find($cls::getMapping()->getColumnNames())->where($keys)->single();

		if(!$aclEntity) {
			throw new Exception("Can't find related ACL entity. The keys for class '$cls' must be invalid: " . var_export($keys, true));
		}
	
		return $aclEntity;
	}

	protected function isAclChanged()
	{
		return $this->isModified(array_keys(static::aclEntityKeys()));
	}

	/**
	 * @inheritDoc
	 */
	protected function internalGetPermissionLevel() {

		if(!isset($this->permissionLevel)) {
			$aclEntity = $this->getAclEntity();

			$this->permissionLevel = $aclEntity->getPermissionLevel();
		}

		return $this->permissionLevel;
	}

	/**
	 * Finds all aclId's for this entity
	 * 
	 * This query is used in the "getFooUpdates" methods of entities to determine if any of the ACL's has been changed.
	 * If so then the server will respond that it cannot calculate the updates.
	 * 
	 * @see EntityController::getUpdates()
	 * 
	 * @return Query
	 */
	public static function findAcls() {

		$cls = static::aclEntityClass();

		return $cls::findAcls();
	}

	/**
	 * Find the ACL id that holds the permissions for this item
	 *
	 * @return int
	 * @throws Exception
	 */
	public function findAclId() {
		return $this->getAclEntity()->findAclId();
	}

}
