<?php

namespace go\core\orm;

use Exception;
use go\core\acl\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\event\EventEmitterTrait;
use go\core\jmap\EntityController;
use go\core\module\model\Module;
use go\core\util\DateTime;

/**
 * Entity model
 * 
 * An entity is a model that is saved to the database. An entity can have 
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 */
abstract class Entity extends Property {
	
	const EVENT_BEFORESAVE = 'beforesave';
	
	const EVENT_SAVE = 'save';
	
	const EVENT_DELETE = 'delete';

	/**
	 * Find entities
	 * 
	 * Returns a query object that's also directly iterable:
	 * 
	 * @exanple
	 * ````
	 * $notes = Note::find()->where(['name' => 'Foo']);
	 * 
	 * foreach($notes as $note) {
	 *	echo $note->name;	
	 * }
	 * 
	 * ```
	 * 
	 * For a single value do:
	 * 
	 * @exanple
	 * ````
	 * $note = Note::find()->where(['name' => 'Foo'])->single();
	 * 
	 * ```
	 * 
	 * For more details see the Criteria::where() function description
	 * 
	 * @see Criteria::where()
	 * @return static[]|Query
	 */
	public static final function find(array $properties = []) {
		
		if(count($properties) && !isset($properties[0])) {
			throw new \Exception("Invalid properties given to Entity::find()");
		}
		return static::internalFind($properties);
	}

	/**
	 * Find by ID's. 
	 * 
	 * It will search on the primary key field of the first mapped table.
	 * 
	 * @exanple
	 * ```
	 * $note = Note::findById(1);
	 * ```
	 * 
	 * @param string|int $id
	 * @param string[] $properties
	 * @return static
	 * @throws Exception
	 */
	public static final function findById($id, array $properties = []) {

		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$pkOfPrimaryTable = $primaryTable->getPrimaryKey();

		$query = static::internalFind($properties);		
		
		if (count($pkOfPrimaryTable) > 1) {
			throw new Exception("Can't find by ids because the primary table has more than one primary key. Entities should have only one primary key field.");//		
		}
		
		$query = static::internalFind($properties);		
		$query->where([$pkOfPrimaryTable[0] => $id]);
	
		
//		$ids = explode('-', $id);
//
//		$where = [];
//		foreach($pkOfPrimaryTable as $key) {
//			$where[$key] = array_shift($ids);
//		}
		
		return $query->single();
	}
	
//	
//	public function getId() {		
//		$tables = static::getMapping()->getTables();
//		$primaryTable = array_shift($tables);
//		$pkOfPrimaryTable = $primaryTable->getPrimaryKey();
//		
//		$id = [];
//		
//		foreach($pkOfPrimaryTable as $key) {
//			$id[] = $this->{$key};
//		}
//		
//		
//		return implode("-", $id);		
//	}

	/**
	 * Save the entity
	 * 
	 * @return boolean
	 */
	public final function save() {	
		App::get()->getDbConnection()->beginTransaction();
			
		if (!$this->fireEvent(self::EVENT_BEFORESAVE, $this)) {
			$this->rollback();
			return false;
		}
		
		if (!$this->internalSave()) {
			$this->rollback();
			return false;
		}		
		
		if (!$this->fireEvent(self::EVENT_SAVE, $this)) {
			$this->rollback();
			return false;
		}

		return $this->commit();
	}
	
	protected function internalSave() {
		if(!parent::internalSave()) {
			App::get()->debug(static::class."::internalSave() returned false");
			return false;
		}
		
		//See \go\core\orm\CustomFieldsTrait;
		if(method_exists($this, 'saveCustomFields')) {
			if(!$this->saveCustomFields()) {
				$this->setValidationError("customFields", \go\core\validate\ErrorCode::INVALID_INPUT, "Could not save custom fields");
				return false;
			}
		}
		
		//See \go\core\orm\SearchableTrait;
		if(method_exists($this, 'saveSearch')) {
			if(!$this->saveSearch()) {				
				$this->setValidationError("search", \go\core\validate\ErrorCode::INVALID_INPUT, "Could not save core_search entry");				
				return false;
			}
		}		
		
		return true;
	}

	/**
	 * Delete the entity
	 * 
	 * @return boolean
	 */
	public final function delete() {

		App::get()->getDbConnection()->beginTransaction();

		if (!$this->internalDelete()) {
			$this->rollback();
			return false;
		}

		if (!$this->fireEvent(self::EVENT_DELETE, $this)) {
			$this->rollback();
			return false;
		}

		return $this->commit();
		
	}

	protected function commit() {
		parent::commit();

		return App::get()->getDbConnection()->commit();
	}

	protected function rollback() {
		App::get()->debug("Rolling back save operation for ".static::class);
		parent::rollBack();
		return App::get()->getDbConnection()->rollBack();
	}

	/**
	 * Checks if the current user has a given permission level.
	 * 
	 * @param int $level
	 * @return boolean
	 */
	public function hasPermissionLevel($level = Acl::LEVEL_READ) {
		return $level <= $this->getPermissionLevel();
	}
	
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		return Acl::LEVEL_READ;
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ) {
		
		return $query;
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
		return null;
	}
	
	/**
	 * Finds the ACL id that holds this models permissions.
	 * Defaults to the module permissions it belongs to.
	 * 
	 * @return int
	 */
	public function findAclId() {
		$moduleId = static::getType()->getModuleId();
		
		return Module::findById($moduleId)->findAclId();
	}
	
	
	/**
	 * Gets an ID from the database for this class used in database relations and 
	 * routing short routes like "Note/get"
	 * 
	 * @return EntityType
	 */
	public static function getType() {		
		return EntityType::findByClassName(static::class);
	}


}
