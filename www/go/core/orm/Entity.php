<?php

namespace go\core\orm;

use Exception;
use GO\Base\Exception\AccessDenied;
use go\core\data\convert\AbstractConverter;
use go\core\data\convert\Json;
use go\core\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\model\Module;
use GO\Files\Model\Folder;
use function go;

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
	 * Fires before the entity has been deleted
	 *
	 * @param $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 *  So you can do for example: go()->getDbConnection()->delete('another_table', (new Query()->where('id', 'in' $query))
	 */
	const EVENT_BEFORE_DELETE = 'beforedelete';
	
	/**
	 * Fires after the entity has been deleted
	 * 
	 * @param $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 *
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
	 * Fires when sorting. Other modules can alter sort behavior.
	 *
	 * @param Query $query
	 * #param array $sort
	 */
	const EVENT_SORT = "sort";

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
   *  echo $note->name;
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
   * @return static[]|Query
   * @throws Exception
   * @see Criteria::where()
   */
	public static final function find(array $properties = [], $readOnly = false) {
		
		if(count($properties) && !isset($properties[0])) {
			throw new Exception("Invalid properties given to Entity::find()");
		}
		return static::internalFind($properties, $readOnly);
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
	 * @param bool $readOnly
	 * @return static
	 * @throws Exception
	 */
	public static final function findById($id, array $properties = [], $readOnly = false) {

		return static::internalFindById($id, $properties, $readOnly);
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
	 * @return Entity|\go\core\orm\Query
	 * @throws Exception
	 */
	public static final function findByIds(array $ids, array $properties = [], $readOnly = false) {
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$keys = $primaryTable->getPrimaryKey();
		$keyCount = count($keys);
		
		$query = static::internalFind($properties, $readOnly);
		
		$idArr = [];
		for($i = 0; $i < $keyCount; $i++) {			
			$idArr[$i] = [];
		}
		
		foreach($ids as $id) {
			$idParts = explode('-', $id);
			if(count($idParts) != $keyCount) {
				throw new Exception("Given id is invalid (" . $id . ")");
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

	/**
	 * Find entities linked to the given entity
	 *
	 * @param $entity
	 * @param array $properties
	 * @param bool $readOnly
	 * @return Query|static[]
	 * @throws Exception
	 */
	public static function findByLink($entity, $properties = [], $readOnly = false) {
		$query = static::find($properties, $readOnly);
		$query->join(
			'core_link',
			'l',
			$query->getTableAlias() . '.id = l.toId and l.toEntityTypeId = '.static::entityType()->getId())

			->andWhere('fromEntityTypeId = '. $entity::entityType()->getId())
			->andWhere('fromId', '=', $entity->id);

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
   * @throws Exception
   */
	public final function save() {	

		$this->isSaving = true;

//		go()->debug(static::class.'::save()' . $this->id());
		App::get()->getDbConnection()->beginTransaction();

		try {
			
			if (!$this->fireEvent(self::EVENT_BEFORESAVE, $this)) {
				$this->rollback();
				return false;
			}
			
			if (!$this->internalSave()) {
				go()->warn(static::class .'::internalSave() returned false');
				$this->rollback();
				return false;
			}		
			
			if (!$this->fireEvent(self::EVENT_SAVE, $this)) {
				$this->rollback();
				return false;
			}

			return $this->commit() && !$this->hasValidationErrors();
		} catch(Exception $e) {
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
	public function isSaving() {
		return $this->isSaving;
	}

	protected function internalValidate()
	{
		if(method_exists($this, 'getCustomFields')) {
			if(!$this->getCustomFields()->validate()) {
				return false;
			}
		}
		return parent::internalValidate();
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
		
		//see \go\core\orm\LoggingTrait
		if(method_exists($this, 'log')) {
			if(!$this->log($this->isNew() ? \go\core\model\Log::ACTION_ADD : \go\core\model\Log::ACTION_UPDATE)) {				
				$this->setValidationError("log", ErrorCode::INVALID_INPUT, "Could not save log entry");				
				return false;
			}
		}	
		
		return true;
	}

	// private $isDeleting = false;

	// /**
	//  * Check if this entity is being deleted.
	//  * 
	//  * @return bool
	//  */
	// public function isDeleting() {
	// 	return $this->isDeleting;
	// }

	/**
	 * Delete the entity
	 *
	 * @param $query The query argument that selects the entities to delete. The query is also populated with "select id from `primary_table`".
	 *  So you can do for example: go()->getDbConnection()->delete('another_table', (new Query()->where('id', 'in' $query))
	 *  Or pass ['id' => $id];
	 *
	 *  Or:
	 *
	 *  SomeEntity::delete($instance->primaryKeyValues());
	 *
	 * @return boolean
	 * @throws Exception
	 */
	public static final function delete($query) {

		$query = Query::normalize($query);
		//Set select for overrides.
		$primaryTable = static::getMapping()->getPrimaryTable();
		$query->selectSingleValue( '`' . $primaryTable->getAlias() . '`.`id`')->from($primaryTable->getName(), $primaryTable->getAlias());


		App::get()->getDbConnection()->beginTransaction();

		try {

			if(!static::fireEvent(static::EVENT_BEFORE_DELETE, $query)) {
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

			if (!static::internalDelete($query)) {
				go()->getDbConnection()->rollBack();
				return false;
			}

			if(!static::fireEvent(static::EVENT_DELETE, $query)) {
				go()->getDbConnection()->rollBack();
				return false;			
			}

			return go()->getDbConnection()->commit();
		} catch(Exception $e) {			
			go()->getDbConnection()->rollBack();
			throw $e;
		}
	}

  /**
   * @inheritDoc
   */
	protected function commit() {
		parent::commit();

		//$this->isDeleting = false;
		$this->isSaving = false;

		return App::get()->getDbConnection()->commit();
	}

  /**
   * @inheritDoc
   */
	protected function rollback() {
		App::get()->debug("Rolling back save operation for " . static::class, 1);
		parent::rollBack();
		// $this->isDeleting = false;
		$this->isSaving = false;
		return App::get()->getDbConnection()->rollBack();
	}

	/**
	 * Checks if the current user has a given permission level.
	 * 
	 * @param int $level
	 * @return boolean
	 */
	public final function hasPermissionLevel($level = Acl::LEVEL_READ) {
		return $level <= $this->getPermissionLevel();
	}
	
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		if($this->isNew()) {
			return $this->canCreate() ? Acl::LEVEL_CREATE : false;
		}
		return go()->getAuthState() && go()->getAuthState()->isAdmin() ? Acl::LEVEL_MANAGE : Acl::LEVEL_READ;
	}
	
	/**
	 * Check if the current user is allowed to create new entities
	 * 
	 * @param array $values The values that will be applied to the new model
	 * @return boolean
	 */
	protected function canCreate() {
		return true;
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 * @param int $userId Leave to null for the current user
	 * @param int[] $groups Supply user groups to check. $userId must be null when usoing this. Leave to null for the current user
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null, $groups = null) {
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
   * @throws Exception
   */
	public function findAclId() {
		$moduleId = static::entityType()->getModuleId();
		
		return Module::findById($moduleId)->findAclId();
	}


  /**
   * Gets an ID from the database for this class used in database relations and
   * routing short routes like "Note/get"
   *
   * @return EntityType
   * @throws Exception
   */
	public static function entityType() {		

		$cls = static::class;
		$cacheKey = 'entity-type-' . $cls;

		$t = go()->getCache()->get($cacheKey);
		if($t) {
			return $t;
		}
	

		$t = EntityType::findByClassName($cls);
		go()->getCache()->set($cacheKey, $t, false);			
		
		return $t;
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
	protected static function defineFilters() {

		$cls = static::class;

		if(!isset(self::$filters[$cls])) {

			self::$filters[$cls] = new Filters();

			self::$filters[$cls]->add('text', function (Criteria $criteria, $value, Query $query) {
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
				self::$filters[$cls]->addDate("modifiedAt", function (Criteria $criteria, $comparator, $value) {
					$criteria->where('modifiedAt', $comparator, $value);
				});
			}


			if (static::getMapping()->getColumn('modifiedBy')) {
				self::$filters[$cls]->addText("modifiedBy", function (Criteria $criteria, $comparator, $value, Query $query) {
					if (!$query->isJoined('core_user', 'modifier')) {
						$query->join('core_user', 'modifier', 'modifier.id = ' . $query->getTableAlias() . '.modifiedBy');
					}

					$criteria->where('modifier.displayName', $comparator, $value);
				});
			}

			if (static::getMapping()->getColumn('createdAt')) {
				self::$filters[$cls]->addDate("createdAt", function (Criteria $criteria, $comparator, $value) {
					$criteria->where('createdAt', $comparator, $value);
				});
			}


			if (static::getMapping()->getColumn('createdBy')) {
				self::$filters[$cls]->addText("createdBy", function (Criteria $criteria, $comparator, $value, Query $query) {
					if (!$query->isJoined('core_user', 'creator')) {
						$query->join('core_user', 'creator', 'creator.id = ' . $query->getTableAlias() . '.createdBy');
					}

					$criteria->where('creator.displayName', $comparator, $value);
				});
			}

			self::defineLegacyFilters(self::$filters[$cls]);

			if (method_exists(static::class, 'defineCustomFieldFilters')) {
				static::defineCustomFieldFilters(self::$filters[$cls]);
			}

			self::$filters[$cls]->addDate('commentedAt', function (Criteria $criteria, $comparator, $value, Query $query) {
				if (!$query->isJoined('comments_comment', 'comment')) {
					$query->join('comments_comment', 'comment', 'comment.entityId = ' . $query->getTableAlias() . '.id AND comment.entityTypeId=' . static::entityType()->getId());
				}

				$tag = ":commentedAt" . uniqid();

				$query->having('MAX(comment.modifiedAt) ' . $comparator . ' ' . $tag)
					->bind($tag, $value->format(\go\core\db\Column::DATETIME_FORMAT))
					->groupBy(['id']);
			});


			/*
				find all items with link to:

			link : {
				entity: "Contact",
				id: 1
			}

			or leave id empty to find items that link to any contact

			*/
			self::$filters[$cls]->add("link", function (Criteria $criteria, $value, Query $query) {
				$linkAlias = 'link_' . uniqid();
				$on = $query->getTableAlias() . '.id =  ' . $linkAlias . '.toId  AND ' . $linkAlias . '.toEntityTypeId = ' . static::entityType()->getId() . ' AND ' . $linkAlias . '.fromEntityTypeId = ' . EntityType::findByName($value['entity'])->getId();

				$query->join('core_link', $linkAlias, $on, "LEFT");
				$criteria->where('toId', '!=', null);

				if (!empty($value['id'])) {
					$criteria->andWhere('fromId', '=', $value['id']);
				}
			});

			static::fireEvent(self::EVENT_FILTER, self::$filters[$cls]);
		}
		
		return self::$filters[$cls];
	}

	/**
	 * Support for old framework columns. May be removed if all modules are refactored.
	 *
	 * @param Filters $filters
	 * @throws Exception
	 */
	private static function defineLegacyFilters(Filters $filters) {
		if (static::getMapping()->getColumn('ctime')) {
			$filters->addDate('createdAt', function (Criteria $criteria, $comparator, DateTime $value, Query $query) {
				$criteria->andWhere('ctime', $comparator, $value->format("U"));
			});
		}

		if (static::getMapping()->getColumn('mtime')) {
			$filters->addDate('modifiedAt', function (Criteria $criteria, $comparator, DateTime $value, Query $query) {
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

	public static function filter(Query $query, Criteria $criteria, array $filter) {		
		static::defineFilters()->apply($query, $criteria, $filter);	
		return $query;
	}
	
	/**
	 * Return columns to search on with the "text" filter. {@see filter()}
	 * 
	 * @return string[]
	 */
	protected static function textFilterColumns() {
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
	protected static function useSearchableTraitForSearch(Query $query) {
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
	protected static function search(Criteria $criteria, $expression, Query $query) {

		$columns = static::textFilterColumns();

		if(static::useSearchableTraitForSearch($query)) {
			$columns[] = 'search.keywords';
		}

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
   * @param array $sort eg. ['field' => 'ASC']
   * @return Query
   * @throws Exception
   * @example
   * ```
   * public static function sort(Query $query, array $sort) {
   *
   *    if(isset($sort['special'])) {
   *      $query->join('core_user', 'u', 'n.createdBy = u.id', 'LEFT')->orderBy(['u.displayName' => $sort['creator']]);
   *      unset($sort['special']);
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
	public static function sort(Query $query, array $sort) {	
		

		if(isset($sort['modifier'])) {
			$query->join('core_user', 'modifier', 'modifier.id = '.$query->getTableAlias() . '.modifiedBy');
			$query->orderBy(['modifier.displayName' => $sort['modifier']], true);
			unset($sort['modifier']);
		}

		if(isset($sort['creator'])) {
			$query->join('core_user', 'creator', 'creator.id = '.$query->getTableAlias() . '.createdBy');
			$query->orderBy(['creator.displayName' => $sort['creator']], true);
			unset($sort['creator']);
		}
		
		//Enable sorting on customfields with ['customFields.fieldName' => 'DESC']
		foreach($sort as $field => $dir) {
			if(substr($field, 0, 13) == "customFields.") {
				$query->joinCustomFields();				
				break;
			}
		}

		static::fireEvent(self::EVENT_SORT, $query, $sort);
		
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
	 * @throws Exception
	 */
	public function copy() {
		return $this->internalCopy();
	}
	
	
	/**
	 * Map of file types to a converter class for importing and exporting.
	 * 
	 * Override to add more.
	 * 
	 * @return AbstractConverter[]
	 */
	public static function converters() {
		return [Json::class];
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
	protected function mergeProp($entity, $name, $p) {
		$col = static::getMapping()->getColumn($name);
		if(!isset($p['access']) || ($col && $col->autoIncrement == true)) {
			return;
		}
		if(empty($entity->$name)) {
			return;
		}
		if(!empty($this->$name) && is_array($this->$name)) {
			$relation = static::getMapping()->getRelation($name);

			$type = $relation ? $relation->type : null;
			switch($type) {
				case Relation::TYPE_MAP:
				case Relation::TYPE_HAS_ONE:
					$this->$name = array_replace($this->$name, $entity->$name);
					break;

				case Relation::TYPE_SCALAR:
					$this->$name = array_unique(array_merge($this->$name, $entity->$name));
					break;

				case Relation::TYPE_ARRAY:
					$this->$name = array_merge($this->$name, $entity->$name);
					break;

				default:
					$this->$name = array_merge($this->$name, $entity->$name);

					break;
			}

		} else{
			$this->$name = $entity->$name;
		}
	}

  /**
   * Merge this entity with another
   *
   * @param Entity $entity
   * @return bool
   * @throws Exception
   */
	public function merge(self $entity) {

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

		if(method_exists($this, 'getCustomFields')) {
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
   * @throws AccessDenied
   */
	private function mergeFiles(self $entity) {
		if(!Module::isInstalled('legacy', 'files') && $entity->getMapping()->getColumn('filesFolderId')) {
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
				->update(
					$r['table'], 
					[$r['column'] => $this->id], 
					[$r['column'] => $entity->id])
				->execute();
		}
	}


  /**
   * Find's all tables that reference this items primary changesdt
   * @return array [['column'=>'contactId', 'table'=>'foo']]
   * @throws Exception
   */
	protected static function getTableReferences() {
		$cacheKey = "refs-table-" . static::class;;
		$refs = go()->getCache()->get($cacheKey);
		if($refs === null) {
			$tableName = array_values(static::getMapping()->getTables())[0]->getName();
			$dbName = go()->getDatabase()->getName();
			try {
				go()->getDbConnection()->exec("USE information_schema");
				//somehow bindvalue didn't work here
				$sql = "SELECT `TABLE_NAME` as `table`, `COLUMN_NAME` as `column` FROM `KEY_COLUMN_USAGE` where ".
					"table_schema=" . go()->getDbConnection()->getPDO()->quote($dbName) . 
					" and referenced_table_name=".go()->getDbConnection()->getPDO()->quote($tableName)." and referenced_column_name = 'id'";

				$stmt = go()->getDbConnection()->getPDO()->query($sql);
				$refs = $stmt->fetchAll(\PDO::FETCH_ASSOC);
			}
			finally{
				go()->getDbConnection()->exec("USE `" . $dbName . "`");	
			}	

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
	public function title() {
		if(property_exists($this,'name')) {
			return $this->name;
		}

		if(property_exists($this,'title')) {
			return $this->title;
		}

		if(property_exists($this,'subject')) {
			return $this->subject;
		}

		if(property_exists($this,'description')) {
			return $this->description;
		}

		if(property_exists($this,'displayName')) {
			return $this->displayName;
		}

		return static::class;
	}
	
}
