<?php

namespace go\core\orm;

use Exception;
use GO;
use go\core\acl\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\jmap\EntityController;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\modules\core\modules\model\Module;

/**
 * Entity model
 * 
 * Note: when changing database columns or creating new entities you need to run install/upgrade.php to 
 * rebuild the cache.
 * 
 * Note: If you want to manually register an entity from a legacy module this code can be used in upgrades.php:
 * 
 * $updates['201805011020'][] = function() {
 * 	$cf = new \go\core\util\ClassFinder();	
 * 	$cf->addNamespace("go\\modules\\community\\email");			
 * 	foreach($cf->findByParent(go\core\orm\Entity::class) as $cls) {
 * 		$cls::getType();
 * 	}
 * };
 * 
 * An entity is a model that is saved to the database. An entity can have 
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 */
abstract class Entity extends Property {
	
	/**
	 * Fires just before the entity will be saved
	 * 
	 * @param Entity $entity The entity that will be saved
	 */
	const EVENT_BEFORESAVE = 'beforesave';
	
	/**
	 * Fires after the entity has been saved
	 * 
	 * @param Entity $entity The entity that has been saved
	 */
	const EVENT_SAVE = 'save';
	
	
	/**
	 * Fires after the entity has been deleted
	 * 
	 * @param Entity $entity The entity that has been deleted
	 */
	const EVENT_DELETE = 'delete';
	
	/**
	 * Fires when the filters are defined. Other modules can extend the filters
	 * 
	 * The event listener is called with the {@see Filters} object.
	 * @see self::defineFilters()
	 */
	const EVENT_FILTER = "filter";

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
	 * Get ID which is are the primary keys combined with a "-".
	 * 
	 * @return string eg. "1" or with multiple keys: "1-2"
	 */
	public function getId() {		
		$keys = $this->primaryKeyValues();
		return count($keys) > 1 ? implode("-", array_values($keys)) : array_values($keys)[0];
	}

	/**
	 * Find by ID's. 
	 * 
	 * It will search on the primary key field of the first mapped table.
	 * 
	 * @exanple
	 * ```
	 * $note = Note::findById(1);
	 * 
	 * //If a key has more than one column they can be combined with a "-". eg. "1-2"
	 * $models = ModelWithDoublePK::findById("1-1");
	 * ```
	 * 
	 * @param string $id 
	 * @param string[] $properties
	 * @return static
	 * @throws Exception
	 */
	public static final function findById($id, array $properties = []) {

		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$keys = $primaryTable->getPrimaryKey();
		
		$query = static::internalFind($properties);		
		
		$ids = explode('-', $id);
		$keys = array_combine($keys, $ids);
		$query->where($keys);
		
		return $query->single();
	}
	
	/**
	 * Find entities by ids.
	 * 
	 * @exanple
	 * ```
	 * $notes = Note::findByIds([1, 2, 3]);
	 * ```
	 * @exanple
	 * ```
	 * $models = ModelWithDoublePK::findByIds(["1-1", "2-1", "3-3"]);
	 * ```
	 * @param array $ids
	 * @param array $properties
	 * @throws Exception
	 */
	public static final function findByIds(array $ids, array $properties = []) {
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$keys = $primaryTable->getPrimaryKey();
		$keyCount = count($keys);
		
		$query = static::internalFind($properties);
		
		$idArr = [];
		for($i = 0; $i < $keyCount; $i++) {			
			$idArr[$i] = [];
		}
		
		foreach($ids as $id) {
			$idParts = explode('-', $id);
			if(count($idParts) != $keyCount) {
				throw new \Exception("Given id is invalid (" . $id . ")");
			}
			for($i = 0; $i < $keyCount; $i++) {			
				$idArr[$i][] = $idParts[$i];
			}
		}
		
		for($i = 0; $i < $keyCount; $i++) {			
			$query->where($keys[$i], 'IN', $idArr[$i]);
		}
		
		return $query;
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
	
	/**
	 * Saves the model and property relations to the database
	 * 
	 * Important: When you override this make sure you call this parent function first so
	 * that validation takes place!
	 * 
	 * @return boolean
	 */
	protected function internalSave() {
		if(!parent::internalSave()) {
			App::get()->debug(static::class."::internalSave() returned false");
			return false;
		}
		
		//See \go\core\orm\CustomFieldsTrait;
		if(method_exists($this, 'saveCustomFields')) {
			if(!$this->saveCustomFields()) {
				$this->setValidationError("customFields", ErrorCode::INVALID_INPUT, "Could not save custom fields");
				return false;
			}
		}
		
		//See \go\core\orm\SearchableTrait;
		if(method_exists($this, 'saveSearch')) {
			if(!$this->saveSearch()) {				
				$this->setValidationError("search", ErrorCode::INVALID_INPUT, "Could not save core_search entry");				
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
		
		//See \go\core\orm\SearchableTrait;
		if(method_exists($this, 'deleteSearchAndLinks')) {
			if(!$this->deleteSearchAndLinks()) {				
				$this->setValidationError("search", ErrorCode::INVALID_INPUT, "Could not delete core_search entry");		
				$this->rollback();
				return false;
			}
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
		return GO()->getAuthState() && GO()->getAuthState()->getUser() && GO()->getAuthState()->getUser()->isAdmin() ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
	}
	
	/**
	 * Check if the current user is allowed to create new entities
	 * 
	 * @return boolean
	 */
	public static function canCreate() {
		return true;
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 * @param int $userId Leave to null for the current user
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null) {
		
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
	
	
	private static $typeCache = [];
	
	/**
	 * Gets an ID from the database for this class used in database relations and 
	 * routing short routes like "Note/get"
	 * 
	 * @return EntityType
	 */
	public static function getType() {		
		if(!isset(self::$typeCache[static::class])) {
			self::$typeCache[static::class] = EntityType::findByClassName(static::class);;
		}
		return self::$typeCache[static::class];
	}
  
  /**
   * Return the unique (!) client name for this entity. Each entity must have a unique name for the client.
   * 
   * For main entities this is not a problem like "Note", "Contact", "Project" etc.
   * 
   * But common names like "Category" or "Folder" should be avoided. Use 
   * "CommentCategory" for example as client name. By default the class name without namespace is used as clientName().
   * @return string
   */
  public static function getClientName() {
		$cls = static::class;
    return substr($cls, strrpos($cls, '\\') + 1);
  }		
	
	/**
	 * Defines JMAP filters
	 * 
	 * This also fires the self::EVENT_FILTER event so modules can extend the
	 * filters.
	 * 
	 * @example
	 * ```
	 * protected static function defineFilters() {
	 * 
	 * 		return parent::defineFilters()
	 * 										->add("addressBookId", function(Query $query, $value, array $filter) {
	 * 											$query->andWhere('addressBookId', '=', $value);
	 * 										})
	 * 										->add("groupId", function(Query $query, $value, array $filter) {
	 * 											$query->join('addressbook_contact_group', 'g', 'g.contactId = c.id')
	 * 											   ->andWhere('g.groupId', '=', $value);
	 * 										});
	 * }
	 * ```
	 * 
	 * @link https://jmap.io/spec-core.html#/query
	 * 
	 * @return Filters
	 */
	protected static function defineFilters() {
		$filters = new Filters();

		$filters->add('q', function(Query $query, $value, $filter) {
							if (!empty($value)) {
								static::search($query, $value);
							}
						})->add('exclude', function(Query $query, $value, $filter) {
							if (!empty($value)) {
								$query->andWhere('id', 'NOT IN', $value);
							}
						});
						
		static::fireEvent(self::EVENT_FILTER, $filters);
		
		return $filters;
	}

	/**
	 * Filter entities See JMAP spec for details on the $filter array.
	 * 
	 * By default these filters are implemented:
	 * 
	 * q: Will search on multiple fields defined in {@see searchColumns()}
	 * exclude: Exclude this array of id's
	 * 
	 * @link https://jmap.io/spec-core.html#/query	 
	 * @param Query $query
	 * @param array $filter key value array eg. ["q" => "foo"]
	 * @return Query
	 */

	public static function filter(Query $query, array $filter) {		
		static::defineFilters()->apply($query, $filter);	
		return $query;
	}
	
	/**
	 * Return columns to search on with the "q" filter. {@see filter()}
	 * 
	 * @return string[]
	 */
	protected static function searchColumns() {
		return [];
	}
	
	/**
	 * Applies a search expression to the given database query
	 * 
	 * @param Query $query
	 * @param string $expression
	 * @return Query
	 */
	protected static function search(Query $query, $expression) {
		
		$columns = static::searchColumns();
		
		//Explode string into tokens and wrap in wildcard signs to search within the texts.
		$tokens = StringUtil::explodeSearchExpression($expression);
		
		if(!empty($columns)) {
			
			$tokensWithWildcard = array_map(
											function($t){
												return '%' . $t . '%';
											}, 
											$tokens
											);

			$searchConditions = (new Criteria());

			foreach($columns as $column) {
				$columnConditions = (new Criteria());
				foreach($tokensWithWildcard as $token) {
					$columnConditions->andWhere($column, 'LIKE', $token);
				}
				$searchConditions->orWhere($columnConditions);
			}		
		}
		
		//Search on the "id" field if the search phrase is an int.
		if(static::getMapping()->getColumn('id')){
			foreach($tokens as $token) {
				$int = (int) $token;
				if((string) $int === $token) {
					$searchConditions->orWhere('id', '=', $int);
				}
			}
		}

		$query->andWhere($searchConditions);

		return $query;
	}
	
	/**
	 * Sort entities.
	 * 
	 * By default you can sort on all database columns. But you can override this
	 * to implement custom logic.
	 * 
	 * @example
	 * ```
	 * public static function sort(Query $query, array $sort) {
	 * 		
	 * 		if(isset($sort['creator'])) {			
	 * 			$query->join('core_user', 'u', 'n.createdBy = u.id', 'LEFT')->orderBy(['u.displayName' => $sort['creator']]);			
	 * 		} 
	 * 
	 * 		
	 * 		return parent::sort($query, $sort);
	 * 		
	 * 	}
	 * 
	 * ```
	 * 
	 * @param Query $query
	 * @param array $sort eg. ['field' => 'ASC']
	 * @return Query
	 */
	public static function sort(Query $query, array $sort) {	
		
		//filter by columns
//		$query->orderBy(array_filter($sort, function($key) {
//				return static::getMapping()->getColumn($key) !== false;
//			}, ARRAY_FILTER_USE_KEY)
//							, true
//		);
		
		
		//Enable sorting on customfields with ['customFields.fieldName' => 'DESC']
		foreach($sort as $field => $dir) {
			if(substr($field, 0, 13) == "customFields.") {
				$query->join(static::customFieldsTableName(), 'customFields', 'customFields.id = '.$query->getTableAlias().'.id', 'LEFT');
				break;
			}
		}
		
		$query->orderBy($sort, true);
		
		return $query;
	}

	/**
	 * Get the current state of this entity
	 * 
	 * @return string
	 */
	public static function getState () {
		return null;
	}
	
	/**
	 * Copy the entity
	 *
	 * @return static
	 */	
	public function copy() {
		return $this->internalCopy();
	}
	
	
	/**
	 * Map of file types to a converter class for importing and exporting.
	 * 
	 * Override to add more.
	 * 
	 * @return array
	 */
	public static function converters() {
		return [
				'application/json' => \go\core\data\convert\JSON::class			
		];
	}
}
