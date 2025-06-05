<?php
/** @noinspection PhpUndefinedMethodInspection */
/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace go\core\orm;

use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\data\convert\AbstractConverter;
use go\core\data\convert\Json;
use go\core\db\Column;
use go\core\ErrorHandler;
use go\core\exception\Forbidden;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\model\Link;
use go\core\model\Search;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\model\Module;
use GO\Files\Model\Folder;
use InvalidArgumentException;
use function go;
use go\core\db\Query as DbQuery;
use go\core\util\ArrayObject;

/**
 * Entity model
 *
 * Note: when changing database columns or creating new entities you need to run install/upgrade.php to
 * rebuild the cache.
 *
 * Note: If you want to manually register an entity from a legacy module this code can be used in upgrades.php:
 *
 * $updates['201805011020'][] = function() {
 *  $cf = new \go\core\util\ClassFinder();
 *  $cf->addNamespace("go\\modules\\community\\email");
 *  foreach($cf->findByParent(go\core\orm\Entity::class) as $cls) {
 *    $cls::entityType();
 *  }
 * };
 *
 * An entity is a model that is saved to the database. An entity can have
 * multiple database tables. It can be extended with has one related tables and
 * it can also have properties in other tables.
 *
 */
abstract class Entity extends Property {

	/**
	 * Fires on validation
	 *
	 * @param Entity $entity The entity that will be saved
	 */
	const EVENT_VALIDATE = 'validate';

	/**
	 * Fires just before the entity will be saved
	 * 
	 * @param Entity $entity The entity that will be saved
	 */
	const EVENT_BEFORE_SAVE = 'beforesave';

	/**
	 * Fires after the entity has been saved
	 * 
	 * @param Entity $entity The entity that has been saved
	 */
	const EVENT_SAVE = 'save';

	/**
	 * Fires before the entity has been deleted
	 *
	 * @param Query $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 *  So you can do for example: go()->getDbConnection()->delete('another_table', (new Query()->where('id', 'in' $query)) or
	 *  fetch the entities: $entities = $cls::find()->mergeWith(clone $query);
	 *
	 * Please beware that altering the query object can cause problems in the delete process.
	 *  You might need to use "clone $query".
	 *
	 * @param string $cls The static class name the function was called on.
	 */
	const EVENT_BEFORE_DELETE = 'beforedelete';
	
	/**
	 * Fires after the entity has been deleted
	 * 
	 * @param Query $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 * @param string $cls The static class name the function was called on.
	 *
	 */
	const EVENT_DELETE = 'delete';
	
	/**
	 * Fires when the filters are defined. Other modules can extend the filters
	 * 
	 * The event listener is called with the {@see Filters} object.
	 * @see self::defineFilters()
	 * @see \go\modules\business\newsletters\Module::onContactFilter()
	 */
	const EVENT_FILTER = "filter";


	/**
	 * Fires when sorting. Other modules can alter sort behavior.
	 *
	 * @param Query $query
	 * #param array $sort
	 */
	const EVENT_SORT = "sort";

	/**
	 * Fires when permission level is requested
	 *
	 *
	 * @param Entity $entity;
	 */
	const EVENT_PERMISSION_LEVEL = 'permissionlevel';


	/**
	 * Fires when filtering on permission level.
	 *
	 * Normal behaviour can be overridden by returning false in your listener.
	 *
	 * @param Criteria $criteria
	 * @param int $value
	 * @param Query $query
	 * @param $filter
	 *
	 */
	const EVENT_FILTER_PERMISSION_LEVEL = "filterpermissionlevel";
	private static array $entityType = [];

	/**
	 * Constructor
	 *
	 * @param boolean $isNew Indicates if this model is saved to the database.
	 * @param string[] $fetchProperties The properties that were fetched by find. If empty then all properties are fetched
	 * @param bool $readOnly Entities can be fetched readonly to improve performance
	 * @throws Exception
	 */
	public function __construct($isNew = true, $fetchProperties = [], $readOnly = false)
	{
		parent::__construct(null, $isNew, $fetchProperties, $readOnly);
	}

	/**
	 * Find entities
	 *
	 * Returns a query object that's also directly iterable:
	 *
	 * @param string[] $properties Specify the columns for optimal performance. You can also use the mapping to only fetch table columns Note::getMapping()->getColumnNames()
	 * @param bool $readOnly Readonly has less overhead
	 * @return Query<$this>
	 * @throws Exception
	 * @example
	 * ````
	 * $note = Note::find()->where(['name' => 'Foo'])->single();
	 *
	 * ```
	 *
	 * For more details see the Criteria::where() function description
	 *
	 * @see Criteria::where()
	 * @example
	 * ````
	 * $notes = Note::find()->where(['name' => 'Foo']);
	 *
	 * foreach($notes as $note) {
	 *  echo $note->name;
	 * }
	 *
	 * ```
	 *
	 * For a single value do:
	 *
	 */
	public static final function find(array $properties = [], bool $readOnly = false): Query
	{
		return static::internalFind($properties, $readOnly);
	}

	/**
	 * Same as {@see find()} but join user tables {@see Mapping::addUserTable()} as another user than the logged in user.
	 *
	 * @throws Exception
	 * @return Query<$this>
	 */
	public static final function findFor(int $userId, array $properties = [], bool $readOnly = false): Query
	{
		return static::internalFind($properties, $readOnly, null, $userId);
	}

	public static final function createFor(int $userId): static {
		$instance = new static();
		$instance->_forUserId = $userId;
		return $instance;
	}
	/**
	 * Find or create an entity
	 *
	 * @param string $key $businessId . "-" . $contactId
	 * @param string|null $keyField Field to search on. If null then findById() is used.
	 * @param array $values Values to apply if it needs to be created.
	 * @param bool $update Update the found entity with new data
	 * @return static
	 * @throws SaveException
	 */
	public static function findOrCreate(string $key, string|null $keyField = null, array $values = [], bool $update = false): Entity
	{
		if($keyField === null) {
			$entity = static::findById($key);
		} else {
			$entity = static::find()->where($keyField, '=', $key)->single();
		}

		if($entity) {
			if(!$update) {
				return $entity;
			}
		} else {
			$entity = new static();
		}

		if($keyField === null) {
			$entity->setValues(static::idToPrimaryKeys($key));
		} else {
			$entity->$keyField = $key;
		}
		$entity->setValues($values, false);

		if(!$entity->save()) {
			throw new SaveException($entity);
		}

		return $entity;
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
	 * @param string|int|null $id
	 * @param string[] $properties
	 * @param bool $readOnly
	 * @return ?static
	 * @throws Exception
	 */
	public static final function findById(string|int|null $id, array $properties = [], bool $readOnly = false): ?Entity
	{
		if($id == null) {
			return null;
		}

		$query = static::internalFind($properties, $readOnly);
		$keys = static::idToPrimaryKeys($id);
		$query->where($keys);

		return $query->single();
	}

	private static $existingIds = [];

	/**
	 * Check if an ID exists in the database in the most efficient way. It also caches the result
	 * during the same request.
	 *
	 * @param string|int|null $id
	 * @return bool
	 * @throws Exception
	 */
	public static function exists(string|int|null $id): bool
	{
		if(empty($id)) {
			return false;
		}

		$key = static::class . ":" .$id;

		if(in_array($key, self::$existingIds)) {
			return true;
		}
		$user = go()->getDbConnection()
			->selectSingleValue('id')
			->from(self::getMapping()->getPrimaryTable()->getName())
			->where('id', '=', $id)->single();

		if($user) {
			self::$existingIds[] = $key;
		}

		return $user != false;

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
	 * @param bool $readOnly
	 * @return Query<$this>
	 * @throws Exception
	 */
	public static final function findByIds(array $ids, array $properties = [], bool $readOnly = false): Query
	{
		$query = static::internalFind($properties, $readOnly);

		$keyCondition = new Criteria();
		foreach($ids as $id) {
			$keys = static::idToPrimaryKeys($id);
			$keyCondition->orWhere($keys);
		}
		$query->where($keyCondition);
		
		return $query;
	}

	/**
	 * Find entities linked to the given entity
	 *
	 * @param ActiveRecord|self $entity
	 * @param array $properties
	 * @param bool $readOnly
	 * @return Query<$this>
	 * @throws Exception
	 */
	public static function findByLink(Entity|ActiveRecord $entity, array $properties = [], bool $readOnly = false): Query
	{
		$query = static::find($properties, $readOnly);
		/** @noinspection PhpPossiblePolymorphicInvocationInspection */

		return Link::joinLinks($query, $entity, static::entityType()->getId());

	}


  /**
   * Save the entity
   *
   * @return boolean
   * @throws Exception
   */
	public final function save(): bool
	{

		$this->isSaving = true;

		App::get()->getDbConnection()->beginTransaction();

		try {
			
			if ($this->fireEvent(self::EVENT_BEFORE_SAVE, $this) === false) {

				$this->rollback();
				return false;
			}
			
			if (!$this->internalSave()) {
				go()->warn(static::class .'::internalSave() returned false');
				$this->rollback();
				return false;
			}		
			
			if ($this->fireEvent(self::EVENT_SAVE, $this) === false) {
				$this->rollback();
				return false;
			}

			return $this->commit() && !$this->hasValidationErrors();
		} catch(\Throwable $e) {
			ErrorHandler::logException($e);
			$this->rollback();
			throw $e;
		}
	}

	private $isSaving = false;

	/**
	 * Check if this entity is saving
	 * 
	 * @return bool
	 */
	public function isSaving(): bool
	{
		return $this->isSaving;
	}

	protected function internalValidate()
	{
		if(method_exists($this, 'getCustomFields')) {
			if(!$this->getCustomFields()->validate()) {
				return;
			}
		}

		static::fireEvent(static::EVENT_VALIDATE, $this);
		parent::internalValidate();
	}

	/**
	 * Saves the model and property relations to the database
	 *
	 * Important: When you override this make sure you call this parent function first so
	 * that validation takes place!
	 *
	 * @return boolean
	 * @throws Exception
	 */
	protected function internalSave(): bool
	{
		if (property_exists($this, 'filesFolderId') && count($this->attachments)) {
			$folder = Folder::model()->findForEntity($this, false);
			if (!isset($this->filesFolderId)) {
				$this->filesFolderId = $folder->id;
			}
			foreach ($this->attachments as $attachment) {
				$b = Blob::findById($attachment['blobId']);
				if (!$b) {
					throw new Exception("No blob found");
				}
				$dest = go()->getDataFolder()->getFile($folder->path . '/' . $b->name);
				$dest->appendNumberToNameIfExists();
				$f = $b->getFile();
				$f->copy($dest);
			}
		}


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
		if(method_exists($this, 'saveSearch') && $this->isModified()) {
			if(!$this->saveSearch()) {				
				$this->setValidationError("search", ErrorCode::INVALID_INPUT, "Could not save core_search entry");				
				return false;
			}
		}

		//See \go\core\orm\PrincipalTrait;
		if(method_exists($this, 'savePrincipal') && $this->isModified()) {
			if(!$this->savePrincipal()) {
				$this->setValidationError("principal", ErrorCode::INVALID_INPUT, "Could not save core_principal entry");
				return false;
			}
		}

		return true;
	}

	/**
	 * Normalize a query value passed to delete()
	 *
	 * @param mixed $query
	 * @return Query
	 * @throws Exception
	 */
	protected static function normalizeDeleteQuery($query): Query
	{

		if($query instanceof Entity) {
			$query = $query->primaryKeyValues();
		}

		$query = Query::normalize($query);
		//Set select for overrides.
		$primaryTable = static::getMapping()->getPrimaryTable();

		$query->select( static::getPrimaryKey(true))
			->from($primaryTable->getName(), $primaryTable->getAlias());

		return $query;
	}


	/**
	 * Delete the entity
	 *
	 * The statement is kept in {@see self::$lastDeleteStmt}
	 *
	 * So you can get the number with self::$lastDeleteStmt->rowCount();
	 *
	 * @param DbQuery|Entity|array $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 *  So you can do for example: go()->getDbConnection()->delete('another_table', (new Query()->where('id', 'in' $query))
	 *  Or pass ['id' => $id];
	 *
	 *  Or:
	 *
	 *  SomeEntity::delete($instance->primaryKeyValues());
	 *
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static final function delete($query): bool
	{

		$query = self::normalizeDeleteQuery($query);

		go()->getDbConnection()->beginTransaction();

		try {

			if(static::fireEvent(static::EVENT_BEFORE_DELETE, $query, static::class) === false) {
				go()->getDbConnection()->rollBack();
				return false;
			}

			if(method_exists(static::class, 'logDelete')) {
				if(!static::logDelete($query)) {
					go()->getDbConnection()->rollBack();
					return false;
				}
			}
			
			//See \go\core\orm\SearchableTrait;
			if(method_exists(static::class, 'deleteSearchAndLinks')) {
				if(!static::deleteSearchAndLinks($query)) {				
					go()->getDbConnection()->rollBack();
					return false;
				}
			}

			//See \go\core\orm\PrincipalTrait;
			if(method_exists(static::class, 'deletePrincipal')) {
				if(!static::deletePrincipal($query)) {
					go()->getDbConnection()->rollBack();
					return false;
				}
			}

			if (!static::internalDelete($query)) {
				go()->getDbConnection()->rollBack();
				return false;
			}

			if(static::fireEvent(static::EVENT_DELETE, $query, static::class) === false) {
				go()->getDbConnection()->rollBack();
				return false;			
			}

			if(!go()->getDbConnection()->commit()) {
				return false;
			}

			return true;
		} catch(\Throwable $e) {
			if(go()->getDbConnection()->inTransaction()) {
				go()->getDbConnection()->rollBack();
			}
			throw $e;
		}
	}


	protected function commitToDatabase() : bool {
		return go()->getDbConnection()->commit();
	}

  /**
   * @inheritDoc
   */
	protected function commit(): bool
	{
		if(!$this->commitToDatabase()) {
			return false;
		}

		if(!parent::commit()) {
			return false;
		}

		$this->isSaving = false;

		return true;
	}

  /**
   * @inheritDoc
   */
	protected function rollback(): bool
	{
		App::get()->debug("Rolling back save operation for " . static::class, 1);
		parent::rollBack();
		// $this->isDeleting = false;
		$this->isSaving = false;
		return !go()->getDbConnection()->inTransaction() || App::get()->getDbConnection()->rollBack();
	}

	/**
	 * Checks if the current user has a given permission level.
	 * 
	 * @param int $level
	 * @return boolean
	 */
	public final function hasPermissionLevel(int $level = Acl::LEVEL_READ): bool
	{
		return $level <= $this->getPermissionLevel();
	}
	
	/**
	 * Get the permission level of the current user
	 *
	 * Note: when overriding this function you also need to override applyAclToQuery() so that queries return only
	 * readable entities.
	 *
	 * @todo make final but there's a backwards compatibility override in model/Module.php
	 * @return int
	 */
	public function getPermissionLevel(): int
	{

		$permissionLevel = static::fireEvent(self::EVENT_PERMISSION_LEVEL, $this);

		if(is_int($permissionLevel)) {
			return $permissionLevel;
		}

		return $this->internalGetPermissionLevel();
	}

	protected function internalGetPermissionLevel(): int
	{
		if($this->isNew()) {
			return $this->canCreate() ? Acl::LEVEL_CREATE : 0;
		}
		return go()->getAuthState() && go()->getAuthState()->isAdmin() ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
	}

	/**
	 * Check if the current user is allowed to create new entities
	 *
	 * @return boolean
	 */
	protected function canCreate(): bool
	{
		return go()->getAuthState() && go()->getAuthState()->isAdmin();
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 * @param int|null $userId Leave to null for the current user
	 * @param int[]|null $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int|null $userId = null, array|null $groups = null): Query
	{
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
	public static function findAcls(): ?Query
	{
		return null;
	}

	/**
	 * Finds the ACL id that holds this models permissions.
	 * Defaults to the entity types' default ACL
	 *
	 * @return ?int
	 * @throws Exception
	 */
	public function findAclId(): ?int
	{
		$mod = Module::findByName(static::getModulePackageName(), static::getModuleName());

		return $mod->getShadowAclId();
	}


	public static function clearCache() : void
	{
		parent::clearCache();

		static::$entityType = [];
	}


	/**
	 * Gets an ID from the database for this class used in database relations and
	 * routing short routes like "Note/get"
	 *
	 * @return EntityType
	 * @throws Exception
	 */
	public static function entityType(): EntityType
	{
		// We don't use go()->getCache() here because in SSE / PushDispatcher we want to disable cache in memory to keep
		// memory as low as possible. But we must still cache these as it will lead to many overhead if we do not reuse it.
		$cls = static::class;

		if(isset(self::$entityType[$cls])) {
			return self::$entityType[$cls];
		}

		self::$entityType[$cls] = EntityType::findByClassName(static::class);

		return self::$entityType[$cls];
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
  public static function getClientName(): string
  {
		$cls = static::class;
    return substr($cls, strrpos($cls, '\\') + 1);
  }

  private static $filters = [];



  /**
   * Defines JMAP filters
   *
   * This also fires the self::EVENT_FILTER event so modules can extend the
   * filters.
   *
   * By default a q, modifiedsince, modiffiedbefore and excluded filter is added
   *
   * @return Filters
   * @throws Exception
   * @example
   * ```
   * protected static function defineFilters() {
   *
   *    return parent::defineFilters()
   *                    ->add("addressBookId", function(Criteria $criteria, $value) {
   *                      $criteria->andWhere('addressBookId', '=', $value);
   *                    })
   *                    ->add("groupId", function(Criteria $criteria, $value, Query $query) {
   *                      $query->join('addressbook_contact_group', 'g', 'g.contactId = c.id');
   *
   *                       $criteria->andWhere('g.groupId', '=', $value);
   *                    });
   * }
   * ```
   *
   * @link https://jmap.io/spec-core.html#/query
   *
   */
	protected static function defineFilters(): Filters
	{

		$filters = new Filters(static::class);

		$filters
			->add("permissionLevelUserId", function() {
				//dummy used in permissionLevel filter.
			})
			->add("permissionLevelGroups", function() {
				//dummy used in permissionLevel filter.
			})
			->add("permissionLevel", function(Criteria $criteria, $value, Query $query, $filterCondition, $filters) {

				// security check. Only admins may query on behalf of others permissions
				if(!empty($filter['permissionLevelUserId']) && $filter['permissionLevelUserId'] != go()->getUserId() && !go()->getAuthState()->isAdmin()) {
					throw new Forbidden("Only admins can pass 'permissionLevelUserId'");
				}

				// security check. Perhaps extend this later to check if the given groups are accessible by the user
				if(!empty($filter['permissionLevelGroups']) && !go()->getAuthState()->isAdmin()) {
					throw new Forbidden("Only admins can pass 'permissionLevelGroups'");
				}

				if(self::fireEvent(self::EVENT_FILTER_PERMISSION_LEVEL, $criteria, $value, $query, $filterCondition, $filters) === false) {
					// event may override this by returning false
					return;
				}

				//Permission level is always added to the main query so that it's always applied with AND
				static::applyAclToQuery($query, $value, $filterCondition['permissionLevelUserId'] ?? null, $filterCondition['permissionLevelGroups'] ?? null);
			})

			->add('text', function (Criteria $criteria, $value, Query $query) {
			if (!is_array($value)) {
				$value = [$value];
			}

			foreach ($value as $q) {
				if (!empty($q)) {
					static::search($criteria, $q, $query);
				}
			}
		})
			->add('exclude', function (Criteria $criteria, $value) {
				if (!empty($value)) {
					$criteria->andWhere('id', 'NOT IN', $value);
				}
			});

		if (static::getMapping()->getColumn('modifiedAt')) {
			$filters->addDateTime("modifiedAt", function (Criteria $criteria, $comparator, $value) {
				$criteria->where('modifiedAt', $comparator, $value);
			});
		}


		if (static::getMapping()->getColumn('modifiedBy')) {
			$filters->addText("modifiedBy", function (Criteria $criteria, $comparator, $value, Query $query) {

				foreach($value as &$v) {
					if ($v == "{{me}}") {
						$v = go()->getUserId();
					}
				}

				if(is_numeric($value[0])) {
					$criteria->andWhere('modifiedBy', '=', $value);
				} else {

					if (!$query->isJoined('core_user', 'modifier')) {
						$query->join('core_user', 'modifier', 'modifier.id = ' . $query->getTableAlias() . '.modifiedBy');
					}

					$criteria->where('modifier.displayName', $comparator, $value);
				}
			});
		}

		if (static::getMapping()->getColumn('createdAt')) {
			$filters->addDateTime("createdAt", function (Criteria $criteria, $comparator, $value) {
				$criteria->where('createdAt', $comparator, $value);
			});
		}


		if (static::getMapping()->getColumn('createdBy')) {
			$filters->add("createdByMe", function (Criteria $criteria, $value, Query $query) {
				//operator must always be = as this filter is also used in conjunction with permission queries
				$criteria->where('createdBy', '=', go()->getAuthState()->getUserId());
			});
			$filters->addText("createdBy", function (Criteria $criteria, $comparator, $value, Query $query) {

				foreach($value as &$v) {
					if ($v == "{{me}}") {
						$v = go()->getUserId();
					}
				}

				if(is_numeric($value[0])) {
					$criteria->andWhere('createdBy', '=', $value);
				} else {
					if (!$query->isJoined('core_user', 'creator')) {
						$query->join('core_user', 'creator', 'creator.id = ' . $query->getTableAlias() . '.createdBy');
					}

					$criteria->where('creator.displayName', $comparator, $value);
				}
			});
		}

		self::defineLegacyFilters($filters);

		if (method_exists(static::class, 'defineCustomFieldFilters')) {
			static::defineCustomFieldFilters($filters);
		}

		$filters->addDateTime('commentedAt', function (Criteria $criteria, $comparator, $value, Query $query) {

			if (!$query->isJoined('comments_comment', 'comment')) {
				$query->join('comments_comment', 'comment', 'comment.entityId = ' . $query->getTableAlias() . '.id AND comment.entityTypeId=' . static::entityType()->getId());
			}

			$tag = ":commentedAt" . uniqid();

			$query->having('MAX(comment.date) ' . $comparator . ' ' . $tag)
				->bind($tag, $value->format(Column::DATETIME_FORMAT))
				->groupBy(['id']);

		});

		$filters->addText('comment', function (Criteria $criteria, $comparator, $value, Query $query) {
			if (!$query->isJoined('comments_comment', 'comment')) {
				$query->join('comments_comment', 'comment', 'comment.entityId = ' . $query->getTableAlias() . '.id AND comment.entityTypeId=' . static::entityType()->getId());
			}

			$query->groupBy(['id']);
			$criteria->where('comment.text ', $comparator, $value);
		});


		/*
			find all items with link to:

		link : {
			entity: "Contact",
			id: 1
		}

		or leave id empty to find items that link to any contact

		*/
		$filters->add("link", function (Criteria $criteria, $value, Query $query) {

			if(!isset($value['entity']) || (count($value) > 1 && !isset($value['id']))) {
				throw new \LogicException("link filter must have 'entity' and an optional 'id' parameter");
			}


			$linkAlias = 'link_' . uniqid();
			$on = $query->getTableAlias() . '.id =  ' . $linkAlias . '.toId  AND ' . $linkAlias . '.toEntityTypeId = ' . static::entityType()->getId() . ' AND ' . $linkAlias . '.fromEntityTypeId = ' . EntityType::findByName($value['entity'])->getId();

			$query->join('core_link', $linkAlias, $on, "LEFT");
			$criteria->where('toId', '!=');

			if (!empty($value['id'])) {
				$criteria->andWhere('fromId', '=', $value['id']);
			}

			$query->groupBy([$query->getTableAlias() . '.id']);
		});

		$filters->add("customrelations", function(Criteria $criteria, $value, Query $query) {
			$cfRelationAlias = 'relation_' . uniqid();
			$on =  $cfRelationAlias .'entityTypeId=' . static::entityType()->getId();
			$query->join('core_customfields_relation', $cfRelationAlias,$on, 'LEFT');
			if (!empty($value['id'])) {
				$criteria->andWhere('entityId', '=', $value['id']);
			}
			$query->groupBy([$query->getTableAlias() . '.id']);
		});

		static::fireEvent(self::EVENT_FILTER, $filters);

		return $filters;
	}

	/**
	 * Support for old framework columns. May be removed if all modules are refactored.
	 *
	 * @param Filters $filters
	 * @throws Exception
	 */
	private static function defineLegacyFilters(Filters $filters) {
		if (static::getMapping()->getColumn('ctime')) {
			$filters->addDateTime('createdAt', function (Criteria $criteria, $comparator, DateTime $value) {
				$criteria->andWhere('ctime', $comparator, $value->format("U"));
			});
		}

		if (static::getMapping()->getColumn('mtime')) {
			$filters->addDateTime('modifiedAt', function (Criteria $criteria, $comparator, DateTime $value) {
				$criteria->andWhere('mtime', $comparator, $value->format("U"));
			});
		}

		if (static::getMapping()->getColumn('user_id')) {
			$filters->addText('createdBy', function (Criteria $criteria, $comparator, $value, Query $query) {
				$query->join('core_user', 'creator', 'creator.id = p.user_id');
				$query->andWhere('creator.displayName', $comparator, $value);
			});
		}

		if (static::getMapping()->getColumn('muser_id')) {
			$filters->addText('modifiedBy', function (Criteria $criteria, $comparator, $value, Query $query) {
				$query->join('core_user', 'modifier', 'creator.id = p.muser_id');
				$query->andWhere('modifier.displayName', $comparator, $value);
			});
		}
	}

  /**
   * Filter entities See JMAP spec for details on the $filter array.
   *
   * By default these filters are implemented:
   *
   * text: Will search on multiple fields defined in {@see textFilterColumns()}
   * exclude: Exclude this array of id's
   *
   * modifiedsince: YYYY-MM-DD (HH:MM) modified since
   *
   * modifiedbefore: YYYY-MM-DD (HH:MM) modified since
   *
   * exclude: array of id's to exclude
   *
   * @link https://jmap.io/spec-core.html#/query
   * @param Query $query
   * @param array $filter key value array eg. ['text' => "foo"]
   * @return Query
   * @throws Exception
   */
	public static function filter(Query $query, array $filter): Query
	{
		static::getFilters()->apply($query, $filter);
		return $query;
	}

	/**
	 * @return Filters
	 * @throws Exception
	 */
	public static function getFilters(): Filters
	{
		$cls = static::class;

		if(!isset(self::$filters[$cls])) {
			self::$filters[$cls] = static::defineFilters();
		}

		return self::$filters[$cls];
	}
	
	/**
	 * Return columns to search on with the "text" filter. {@see filter()}
     *
     * If you need joins you can override {@see search()}. You can access the joined table with ['alias.colName']
	 * 
	 * @return string[]
	 */
	protected static function textFilterColumns(): array
	{
		return [];
	}

	/**
	 * Checks if this entity supports SearchableTrait and uses that for the "text" query filter.
	 * Override and return false to disable this behaviour.
	 *
	 * @param Query $query
	 * @return bool
	 * @throws Exception
	 */
	protected static function useSearchableTraitForSearch(Query $query): bool
	{
		// Join search cache on when searchable trait is used
		if(!method_exists(static::class, 'getSearchKeywords')) {
			return false;
		}
		if(!$query->isJoined('core_search', 'search')) {
			$query->join(
				'core_search',
				'search',
				'search.entityId = ' . $query->getTableAlias() . '.id and search.entityTypeId = ' . static::entityType()->getId()
			);
		}

		return true;
	}

  /**
   * Applies a search expression to the given database query
   *
   * @param Criteria $criteria
   * @param string $expression
   * @param Query $query
   * @return Criteria
   * @throws Exception
   */
	protected static function search(Criteria $criteria, string $expression, DbQuery $query): Criteria
	{
		if(static::useSearchableTraitForSearch($query)) {
			Search::addCriteria( $criteria, $query, $expression);
			return $criteria;
		}

    $columns = static::textFilterColumns();

    if(empty($columns)) {
			go()->warn(static::class . ' entity has no textFilterColumns() defined. The "text" filter will not work.');
		}
		
		//Explode string into tokens and wrap in wildcard signs to search within the texts.
		$tokens = StringUtil::explodeSearchExpression($expression);
		
		$searchConditions = (new Criteria());
		
		if(!empty($columns)) {			
			$tokensWithWildcard = array_map(
											function($t){
												return '%' . $t . '%';
											}, 
											$tokens
											);

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

		if($searchConditions->hasConditions()) {
			$criteria->andWhere($searchConditions);
		}

		return $criteria;
	}

  /**
   * Sort entities.
   *
   * By default you can sort on
   *
   * - all database columns
   * - All Customfields with "customField.<databasName>"
   * - creator Will join core_user.displayName on createdBy
   * - modifier Will join core_user.displayName on modifiedBy
   *
   *  You can override this to implement custom logic.
   *
   * @param Query $query
   * @param ArrayObject $sort Key value where field name is the key and value is ASC or DESC. eg. ['field' => 'ASC']
   * @return Query
   * @throws Exception
   * @example
   * ```
   * public static function sort(Query $query, array $sort) {
   *
   *    if(isset($sort['special'])) {
   *      $query->join('core_user', 'u', 'n.createdBy = u.id', 'LEFT');
   *      $sort->renameKey('special', 'u.displayName');
   *    }
   *
   *
   *    return parent::sort($query, $sort);
   *
   *  }
   *
   * ```
   *
   */
	public static function sort(Query $query, ArrayObject $sort): Query
	{
		if(empty($sort->getArray())) {
			$sort->exchangeArray(static::defaultSort());
		}

		if(isset($sort['modifier'])) {
			$query->join('core_user', 'modifier', 'modifier.id = '.$query->getTableAlias() . '.modifiedBy');
			$sort->renameKey('modifier', 'modifier.displayName');
		}

		if(isset($sort['creator'])) {
			$query->join('core_user', 'creator', 'creator.id = '.$query->getTableAlias() . '.createdBy');
			$sort->renameKey('creator', 'creator.displayName');
		}
		
		//Enable sorting on customfields with ['customFields.fieldName' => 'DESC']
		foreach($sort as $field => $dir) {
			if(substr($field, 0, 13) == "customFields.") {
				$query->joinCustomFields();				
				break;
			}
		}

		static::fireEvent(self::EVENT_SORT, $query, $sort);
		
		$query->orderBy($sort->getArrayCopy(), true);

		return $query;
	}


	/**
	 * Return default sort array
	 *
	 * @return array eg. ['field' => 'ASC']
	 */
	protected static function defaultSort() : array {
		return [];
	}

	/**
	 * Get the current state of this entity
	 * 
	 * @return string
	 */
	public static function getState (): ?string
	{
		return null;
	}
	
	/**
	 * Map of file types to a converter class for importing and exporting.
	 * 
	 * Override to add more.
	 *
	 * @return string[] Of type AbstractConverter
	 	 *@see AbstractConverter
	 */
	public static function converters(): array
	{
		return [Json::class];
	}


	/**
	 * Find a converter for exporting or importing
	 *
	 * @param string $extension
	 * @return AbstractConverter
	 */
	public static function findConverter(string $extension): AbstractConverter
	{
		foreach(static::converters() as $converter) {
			if($converter::supportsExtension($extension)) {
				return new $converter($extension, static::class);
			}
		}

		throw new InvalidArgumentException("Converter for file extension '" . $extension .'" is not found');
	}

	/**
	 * Check database integrity
   *
	 * NOTE: this function may not output as it's used by install.php
	 *
   * @throws Exception
	 */
	public static function check() {

	}


	/**
	 * Merge a single property.
	 *
	 * Can be overridden to handle specific merge logic.
	 *
	 * @param static $entity
	 * @param string $name
	 * @param array $p
	 * @throws Exception
	 */
	protected function mergeProp(Entity $entity, string $name, array $p) {
		$col = static::getMapping()->getColumn($name);
		if(!isset($p['access']) || ($col && $col->autoIncrement == true)) {
			return;
		}
		if(empty($entity->$name)) {
			return;
		}

		$relation = static::getMapping()->getRelation($name);

		if(!$relation) {
			if(!is_array($this->$name)) {
				$this->$name = $entity->$name;
			} else{
				$this->$name = array_merge($this->$name, $entity->$name);
			}
			return;
		}


		switch($relation->type) {
			case Relation::TYPE_MAP:
				$this->$name = is_array($this->$name) ? array_replace($this->$name, $entity->$name) : $entity->$name;
				break;

			case Relation::TYPE_HAS_ONE:

				$copy = $entity->$name->toArray();

				//unset the foreign key. The new model will apply the right key on save
				foreach($relation->keys as $from => $to) {
					unset($copy[$to]);
				}

				// has one or map might be null
				if(!isset($this->$name)) {
					$this->$name = new $relation->propertyName($this);
				}

				$this->$name = $this->$name->setValues($copy);
				break;

			case Relation::TYPE_SCALAR:
				$this->$name = isset($this->$name) ? array_unique(array_merge($this->$name, $entity->$name)) : $entity->$name;
				break;

			case Relation::TYPE_ARRAY:

				$copies = [];
				//unset the foreign key. The new model will apply the right key on save
				for($i = 0, $l = count($entity->$name); $i < $l; $i ++) {
					$copy = $entity->$name[$i]->toArray();
					foreach ($relation->keys as $from => $to) {
						unset($copy[$to]);
					}
					$copies[] = $copy;
				}

				//set values will normalize array value into a model
				$this->setValue($name, array_merge($this->$name, $copies));

				break;


		}


	}

  /**
   * Merge this entity with another.
   *
   * This will happen:
   *
   * 1. Scalar properties of the given entity will overwrite the properties of this
   *    entity.
   * 2. Array properties will be merged.
   * 3. Comments, files and links will be merged
   * 4. foreign key fields will be updated
   * 5. The given entity will be deletedf
   *
   *
   * @param Entity $entity
   * @return bool
   * @throws Exception
   * @noinspection PhpUndefinedMethodInspection
   */
	public function merge(self $entity): bool
	{

		if($this->equals($entity)) {
			throw new Exception("Can't merge with myself!");
		}

		//copy public and protected columns except for auto increments.
		$props = $this->getApiProperties();
		foreach($props as $name => $p) {
			if($name == 'filesFolderId') {
				continue;
			}
			$this->mergeProp($entity, $name, $p);
		}

		if(method_exists($entity, 'getCustomFields')) {
			$cf = $entity->getCustomFields();
			foreach($cf as $name => $v) {
				if(empty($v)) {
					unset($cf[$name]);
				}
			}
			$this->setCustomFields($cf);
		}

		go()->getDbConnection()->beginTransaction();

		//move links
		if(!go()->getDbConnection()
						->updateIgnore('core_link', 
										['fromId' => $this->id],
										['fromEntityTypeId' => static::entityType()->getId(), 'fromId' => $entity->id]
										)->execute()) {
			go()->getDbConnection()->rollBack();
			return false;
		}
		
		if(!go()->getDbConnection()
						->updateIgnore('core_link', 
										['toId' => $this->id],
										['toEntityTypeId' => static::entityType()->getId(), 'toId' => $entity->id]
										)->execute()) {
			go()->getDbConnection()->rollBack();
			return false;
		}

		//move comments

		if(Module::isInstalled('community', 'comments')) {
			if(!go()->getDbConnection()
						->update('comments_comment', 
										['entityId' => $this->id],
										['entityTypeId' => static::entityType()->getId(), 'entityId' => $entity->id]
										)->execute()) {
				go()->getDbConnection()->rollBack();
				return false;
			}
		}


		//move files
		$this->mergeFiles($entity);

		$this->mergeRelated($entity);
		if(!static::delete(['id' => $entity->id])) {
			go()->getDbConnection()->rollBack();
				return false;
		}

		if(!$this->save()) {
			go()->getDbConnection()->rollBack();
			return false;
		}

		return go()->getDbConnection()->commit();
	}

  /**
   * @param Entity $entity
   * @throws Exception
   */
	private function mergeFiles(self $entity) {
		if(!Module::isInstalled('legacy', 'files') || !$entity->getMapping()->getColumn('filesFolderId')) {
			return;
		}
		$sourceFolder = Folder::model()->findByPk($entity->filesFolderId);
		if (!$sourceFolder) {
			return;
		}
		$folder = Folder::model()->findForEntity($this);
	
		$folder->moveContentsFrom($sourceFolder);		
	}

  /**
   * updates foreign key fields in other tables
   *
   * @param Entity $entity
   * @throws Exception
   */
	private function mergeRelated(Entity $entity) {

		$refs = static::getTableReferences();

		$cfTable = method_exists($this, 'customFieldsTableName') ? $this->customFieldsTableName() : null;

		foreach($refs as $r) {
			if($r['table'] == $cfTable) {
				continue;
			}

			go()->getDbConnection()
				->updateIgnore(
					$r['table'], 
					[$r['column'] => $this->id], 
					[$r['column'] => $entity->id])
				->execute();
		}
	}


  /**
   * Find's all tables that reference this items primary table
	 *
   * @return array [['column'=>'contactId', 'table'=>'foo']]
   * @throws Exception
   */
	protected static function getTableReferences(): array
	{
		$cacheKey = "refs-table-" . static::class;
		$refs = go()->getCache()->get($cacheKey);
		if($refs === null) {

			$refs = static::getMapping()->getPrimaryTable()->getReferences();

			//don't find the entity itself
			$refs = array_filter($refs, function($r) {
				return !static::getMapping()->hasTable($r['table']);
			});

			go()->getCache()->set($cacheKey, $refs);			
		}

		return $refs;
	}

	/**
	 * A title for this entity used in search cache and logging for example.
	 *
	 * @return string
	 */
	public function title(): string
	{
		if(property_exists($this,'name') && !empty($this->name)) {
			return $this->name;
		}

		if(property_exists($this,'title') && !empty($this->title)) {
			return $this->title;
		}

		if(property_exists($this,'subject') && !empty($this->subject)) {
			return $this->subject;
		}

		if(property_exists($this,'description') && !empty($this->description)) {
			return $this->description;
		}

		if(property_exists($this,'displayName') && !empty($this->displayName)) {
			return $this->displayName;
		}

		return static::class;
	}


	/**
	 * @var array
	 */
	protected $attachments = [];

	public function setAttachments(array $attachments)
	{
		$this->attachments = $attachments;
	}
	
}
