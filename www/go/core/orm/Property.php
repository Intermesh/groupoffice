<?php

namespace go\core\orm;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use go\core\App;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\db\Statement;
use go\core\db\Utils;
use go\core\event\EventEmitterTrait;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use DateTime as CoreDateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\validate\ValidationTrait;
use InvalidArgumentException;
use LogicException;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionProperty;
use function GO;
use go\core\db\Table;
use go\core\ErrorHandler;
use go\core\jmap\exception\InvalidArguments;

/**
 * Property model
 *
 * Note: when changing database columns you need to run install/upgrade.php to
 * rebuild the cache.
 *
 * A property belongs to a {@see Entity}
 *
 * It can only be saved, deleted or found through an {@see Entity}
 *
 * @method static fetch() Not really a method but helps the IDE to autocomplete when using Property::find()->fetch();
 */
abstract class Property extends Model {

	use ValidationTrait;

	use EventEmitterTrait;

	private static $_mapping;

	/**
	 * For reusing prepared statements
	 *
	 * @var Statement[]
	 */
	private static $cachedRelationStmts = [];
	private static $apiProperties = [];
	private static $requiredProps = [];

	/**
	 * Fires when the mapping is defined. Other modules can add new properties
	 *
	 * The event listener is called with the {@see Mapping} object.
	 */
	const EVENT_MAPPING = "mapping";

	/**
	 * Returns true is the model is new and not saved to the database yet.
	 *
	 * @var boolean
	 */
	private $isNew;

	/**
	 * Associative array with property name => old value.
	 * @var array
	 */
	private $oldProps = [];

	/**
	 * The properties that were fetched by find
	 *
	 * @var string[]
	 */
	protected $fetchProperties;

	/**
	 * Properties that were selected from the database tables
	 *
	 * @var array
	 */
	protected $selectedProperties;

	/**
	 * Keeps record of all the saved properties so we can commit or rollback after save.
	 * @var Property[]
	 */
	private $savedPropertyRelations = [];

	/**
	 * Holds primary keys per table alias. Used to track new state of records.
	 * Only set when not fetched as readonly
	 *
	 * @example
	 * ```
	 * ['tableAlias' => ['id' => 1]]
	 * ```
	 * @var array
	 */
	private $primaryKeys = [];

	/**
	 * Holds dynamic properties mapped by other modules with the EVENT_MAPPING
	 */
	private $dynamicProperties = [];

	/**
	 * Entities can be fetched readonly to improve performance
	 *
	 * @var bool
	 */
	protected $readOnly = false;

	/**
	 * @var Property a reference to the entity this property belongs to
	 */
	protected $owner = null;

	/**
	 * Constructor
	 *
	 * @param ?Property $owner
	 * @param boolean $isNew Indicates if this model is saved to the database.
	 * @param string[] $fetchProperties The properties that were fetched by find. If empty then all properties are fetched
	 * @param bool $readOnly Entities can be fetched readonly to improve performance
	 *
	 * @noinspection PhpMissingParamTypeInspection
	 * @throws Exception
	 */
	public function __construct($owner, bool $isNew = true, array $fetchProperties = [], bool $readOnly = false)
	{
		$this->isNew = $isNew;

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$this->fetchProperties = $fetchProperties;
		$this->readOnly = $readOnly;
		$this->owner = $owner;
		$this->selectedProperties = array_unique(array_merge($this->getRequiredProperties(), $this->fetchProperties));

		$this->loadConstants();

		if ($this->isNew) {
			$this->initRelations();

			$this->loadDatabaseDefaults();

			if(!$readOnly) {
				$this->trackModifications();
			}

			$this->init();
		}

	}

	/**
	 * Populate model with values from database
	 *
	 * Used by {@see Statement} to populate the record.
	 *
	 * @param array $record
	 * @return $this
	 * @throws Exception
	 */
	public function populate(array $record): static
	{
		$m = static::getMapping();
		foreach($record as $colName => $value) {

			if(str_contains($colName, '.')) {
				$this->setPrimaryKey($colName, $value);
			} else {

				$col = $m->getColumn($colName);
				if($col) {
					$value = $col->castFromDb($value);
				}
				$this->$colName = $value;
			}
		}

		$this->initRelations();

		if(!$this->readOnly) {
			$this->trackModifications();
		}

		$this->init();

		return $this;
	}

	/**
	 * Check if this model is read only. Models can be fetched read only to improve performance.
	 *
	 * @return bool
	 */
	public function isReadOnly(): bool
	{
		return $this->readOnly;
	}

	/**
	 * When properties have default values in the model they are overwritten by the database defaults. We change them back
	 * here so the modification is tracked and it will be saved.
	 * @var array
	 */
	private $defaults = [];

	/**
	 * Loads defaults from the database or casts the database value to the right type in PHP
	 *
	 */
	private function loadDatabaseDefaults(): void
	{
		$m = static::getMapping();
		foreach($this->selectedProperties as $propName) {
			$col = $m->getColumn($propName);
			if($col) {
				if(!isset($this->$propName) && $col->default !== null) {
					$this->$propName = $col->castFromDb($col->default);
				}
			}
		}

	}


	private function loadConstants(): void
	{
		$m = static::getMapping();
		foreach ($m->getTables() as $table) {
			foreach($table->getConstantValues() as $colName => $value) {
				$this->$colName  = $value;
			}
		}
	}



  /**
   * Returns all relations that were requested in "fetchProperties".
   *
   * @return Relation[]
   */
	private function getFetchedRelations(): array
	{
		$fetchedRelations = [];

		$relations = $this->getMapping()->getRelations();
		foreach ($relations as $relation) {
			if (in_array($relation->name, $this->selectedProperties)) {
				$fetchedRelations[] = $relation;
			}
		}

		return $fetchedRelations;
	}

  /**
   * Fetches the related properties when requested
   */
	private function initRelations(): void
	{
		foreach ($this->getFetchedRelations() as $relation) {
			$cls = $relation->propertyName;

			$where = $this->buildRelationWhere($relation);

			// Should query when property is not new but also when there's an empty where.
			// this means there is no foreign key and all records can be shown.
			// this is used in {@see \go\modules\business\support\model\Settings} for example.
			$shouldQuery = !$this->isNew() || !count($where);

			switch($relation->type) {

				case Relation::TYPE_HAS_ONE:
					if(!$shouldQuery) {
						$prop = null;
					} else
					{
						$stmt = $this->queryRelation($cls, $where, $relation, $this->readOnly, $this);
						$prop = $stmt->fetch();
						$stmt->closeCursor();
						if(!$prop) {
							$prop = null;
						}
					}

					if(!$prop && $relation->autoCreate) {
						$prop = new $cls($this, true, [], $this->readOnly);

						$this->applyRelationKeys($relation, $prop);
					}
					$this->{$relation->name} = $prop;
				break;

				case Relation::TYPE_ARRAY:

					if(!$shouldQuery) {
						$prop = [];
					} else
					{
						$stmt = $this->queryRelation($cls, $where, $relation, $this->readOnly, $this);

						$prop = $stmt->fetchAll();
						$stmt->closeCursor();
					}

					$this->{$relation->name} = $prop;
				break;

				case Relation::TYPE_MAP:

					if(!$shouldQuery) {
						$prop = null;
					} else
					{
						$stmt = $this->queryRelation($cls, $where, $relation, $this->readOnly, $this);
						$prop = $stmt->fetchAll();
						$stmt->closeCursor();
						if(empty($prop)) {
							$prop = null; //Set to null. Otherwise JSON will be serialized as [] instead of {}
						}	else{
							$o = [];
							foreach($prop as $v) {
								$key = $this->buildMapKey($v, $relation);
								$o[$key] = $v;
							}
							$prop = $o;
						}
					}

					$this->{$relation->name} = $prop;
				break;

				case Relation::TYPE_SCALAR:

					if(!$shouldQuery) {
						$scalar = [];
					} else {
						$stmt = $this->queryScalar($where, $relation);
						$scalar = $stmt->fetchAll();
						$stmt->closeCursor();
					}
					$this->{$relation->name} = $scalar;
				break;
			}
		}
	}

  /**
   * @param $where
   * @param Relation $relation
   * @return Statement
   */
	private static function queryScalar($where, Relation $relation): Statement
	{
		$cacheKey = static::class.':'.$relation->name;

		if(!isset(self::$cachedRelationStmts[$cacheKey])) {
			$key = $relation->getScalarColumn();
			$query = (new Query)->selectSingleValue($key)->from($relation->tableName);
			foreach($where as $field => $value) {
				$query->andWhere($field . '= :'.$field);
			}
			$stmt = $query->createStatement();
			self::$cachedRelationStmts[$cacheKey] = $stmt;
		} else
		{
			$stmt = self::$cachedRelationStmts[$cacheKey] ;
		}

		foreach($where as $field => $value) {
			$stmt->bindValue(':'.$field, $value);
		}
		$stmt->execute();

		return $stmt;
	}




	/**
	 * Needed to close the database connection
	 *
	 * @return void
	 */
	public static function clearCachedRelationStmts() {
		self::$cachedRelationStmts = [];
	}


	private static function queryRelation($cls, array $where, Relation $relation, $readOnly, $owner): Statement
	{
		$cacheKey = static::class.':'.$relation->name;

		if(!isset(self::$cachedRelationStmts[$cacheKey])) {
			/** @var Entity $cls */
			$query = $cls::internalFind([], $readOnly, $owner);

			foreach ($where as $field => $value) {
				$query->andWhere($field . '= :' . $field)
					->bind(':' . $field, $value);
			}

			if (is_a($relation->propertyName, UserProperty::class, true)) {
				$query->andWhere('userId', '=', go()->getAuthState()->getUserId() ?? null);
			}

			if (!empty($relation->orderBy)) {
				$query->orderBy([$relation->orderBy => 'ASC']);
			}

			$stmt = self::$cachedRelationStmts[$cacheKey] = $query->createStatement();
		}else
		{
			$stmt = self::$cachedRelationStmts[$cacheKey] ;
			$query = $stmt->getQuery();
			/** @var Query $query */
//			$stmt->setFetchMode(PDO::FETCH_CLASS, $cls, [$owner, false, [], $query->getReadOnly()]);
			$stmt->fetchTypedModel($cls, [$owner, false, [], $query->getReadOnly()]);

			foreach($where as $field => $value) {
				$stmt->bindValue(':'.$field, $value);
			}
		}


		$stmt->execute();

		return $stmt;

	}

	/**
	 * Builds where SQL conditions based on the relation keys
	 *
	 * @param Relation $relation
	 * @return array
	 */
	private function buildRelationWhere(Relation $relation): array
	{
		$where = [];
		foreach ($relation->keys as $from => $to) {
			$where[$to] = $this->$from ?? null;
		}
		return $where;
	}


  /**
   * Build a key of the primary keys but omit the key from the relation because it's not needed as it's a property.
   *
   * @param Property $v
   * @param Relation $relation
   * @return string
   */
	private function buildMapKey(Property $v, Relation $relation): string
	{

		$pk = $v->getPrimaryKey();

		// //If a mapped relation is only a primary key (link model) then this model can be represented as a boolean.
		// //For example $group->users = [1 => [1, 3]] can be shown as [1 => true].
		// $fetchProps = array_diff($v->getDefaultFetchProperties(), $pk);
		// $asBoolean = empty($fetchProps);

		$diff = array_diff($pk, array_values($relation->keys));

		$id = [];
		foreach($diff as $field) {
			$id[] = $v->$field;
		}

		return implode('-', $id);
	}

	/**
	 * Get all properties to check when saving and using isModified()
	 *
	 * By default, all non-static public and protected properties + dynamically mapped properties.
	 *
	 * @return array
	 * @throws Exception
	 */
	private function watchProperties(): array
	{
		$cacheKey = 'watch-props-' . static::class;

		$ret = App::get()->getCache()->get($cacheKey);
		if ($ret !== null) {
			return $ret;
		}

		$p = array_keys(static::getMapping()->getProperties());

		$reflectionObject = new ReflectionClass(static::class);
		$props = $reflectionObject->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
		foreach ($props as $prop) {
			if (!in_array($prop->getName(), $p) && !$prop->isStatic()) {
				$p[] = $prop->getName();
			}
		}

		$exclude = [
			'isNew',
			'oldProps',
			'fetchProperties',
			'selectedProperties',
			'owner',
			'dontChangeModifiedAt',
			'returnAsText',
			'permissionLevel',
			'readOnly'
		];
		$p = array_unique(array_diff($p, $exclude));

		App::get()->getCache()->set($cacheKey, $p);

		return $p;
	}

	/**
	 * Copies all properties so isModified() can detect changes.
	 * @throws Exception
	 */
	private function trackModifications(): void
	{
		foreach ($this->watchProperties() as $propName) {

			if($this->isNew()) {
				// if this model is new then store the database default as the old value
				$col = static::getMapping()->getColumn($propName);
				if($col) {
					$this->oldProps[$propName] = $col->default;
					continue;
				}
			}

			$v = $this->$propName ?? null;

			if(is_object($v)) {
				$this->oldProps[$propName] = clone $v;
			} else if(is_array($v) && isset($v[0]) && $v[0] instanceof self) {
				$this->oldProps[$propName] = array_map(function($i) {return clone $i;}, $v);
			} else {
				$this->oldProps[$propName] = $v;
			}
		}
	}

	/**
	 * Override this function to initialize your model.
	 * When this method is executed the property already tracks modifications that will be saved if needed.
	 */
	protected function init() {

	}

	/**
	 * List of tables this entity uses
	 *
	 * Note: When making changes to the mapping you need to run install/upgrade.php
	 * to rebuild the cache!
	 *
	 * All tables must have identical primary keys.
	 * eg
	 *
	 * ````
	 * 	protected static function defineMapping() {
	 * 		return parent::defineMapping()
	 * 						->addTable('test_a', 'a')
	 * 						->addProperty('sumOfTableBIds', "SUM(b.id)", (new Query())->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']))
	 * 						->addArray('hasMany', AHasMany::class, ['id' => 'aId'],)
	 * 						->adddHasOne('hasOne', AHasOne::class, ['id' => 'aId'], false);
	 * 	}
	 * ````
	 *
	 * @return Mapping
	 * @throws Exception
	 */
	protected static function defineMapping(): Mapping
	{
		return new Mapping(static::class);
	}

	public static function clearCache() {
//		self::$_mapping = [];
//		self::$requiredProps = [];
		self::$cachedRelationStmts = [];
//		self::$apiProperties = [];
	}

	/**
	 * Returns the mapping object that is defined in defineMapping()
	 *
	 * @return Mapping
	 * @throws Exception
	 */
	public final static function getMapping(): Mapping
	{
		$cls = static::class;

		$cacheKey = 'mapping-' . $cls;

		$map = go()->getCache()->get($cacheKey);
		if($map === null) {
			$map = static::defineMapping();

			$map->dynamic = true;

			static::fireEvent(self::EVENT_MAPPING, $map);

			go()->getCache()->set($cacheKey, $map);
		}

		return $map;
	}

	/**
	 * Get ID which is are the primary keys combined with a "-".
	 *
	 * @return string|null eg. "1" or with multiple keys: "1-2"
	 */
	public function id() : ?string {
		if(property_exists($this, 'id')) {
			return isset($this->id) ? (string) $this->id : null;
		}
		$keys = $this->primaryKeyValues();
		if(empty($keys)) {
			// can we ever get here?
			return "";
		}
		return count($keys) > 1 ? implode("-", array_values($keys)) : array_values($keys)[0];
	}

  /**
   * @inheritDoc
   */
	public static function getApiProperties(): array
	{
		$cls = static::class;

		//this function is called many times. This seems to have a slight performance benefit
//		if(isset(self::$apiProperties[$cls])) {
//			return self::$apiProperties[$cls];
//		}

		$cacheKey = 'property-getApiProperties-' . $cls;

		$props = go()->getCache()->get($cacheKey);

		if(!$props) {
			$props = parent::getApiProperties();

			//add dynamic relations		
			foreach(static::getMapping()->getProperties() as $propName => $type) {
				//do property_exists because otherwise it will add protected properties too.
				if(!isset($props[$propName])) {
					$props[$propName] = ['setter' => false, 'getter' => false, 'access' => self::PROP_PUBLIC, 'dynamic' => true];
				}
				$props[$propName]['db'] = true;
			}

			if(method_exists(static::class, 'getCustomFields')) {
				$props['customFields'] = ['setter' => true, 'getter' => true, 'access' => null];
			}

			go()->getCache()->set($cacheKey, $props);
		}

//		self::$apiProperties[$cls] = $props;
		return $props;
	}

  /**
   * Magic getter to make dynamically mapped properties possible in other modules
   *
   * @param $name
   * @return mixed
   * @throws Exception
   */
	public function &__get($name) {
		$prop = static::getMapping()->getProperty($name);
		if($prop) {

			if(!$prop->dynamic && go()->getDebugger()->enabled) {
				throw new LogicException("You should define '$name' in " . static::class);
			}

			if(!isset($this->dynamicProperties[$name])) {
//				if($prop instanceof Relation && !in_array($name, $this->fetchProperties)) {
//					throw new Exception("Relation '$name' was not fetched so can't be accessed");
//				}
				$this->dynamicProperties[$name] = null;
			}
			return $this->dynamicProperties[$name];
		}
		throw new Exception("Can't get not existing property '$name' in '".static::class."'");

	}

  /**
   * Magic getter to make dynamically mapped properties possible in other modules
   * @param $name
   * @return bool
   * @throws Exception
   */
	public function __isset($name) {

		if(static::getMapping()->hasProperty($name)) {
			return isset($this->dynamicProperties[$name]);
		}
		return false;
	}

  /**
   * Magic setter to make dynamically mapped properties possible in other modules
   *
   * @param $name
   * @param $value
   * @throws Exception
   */
	public function __set($name, $value) {
		//Support for dynamically mapped props via EVENT_MAP
		$props = static::getApiProperties();
		if(isset($props[$name]) && !empty($props[$name]['dynamic'])) {
			$this->dynamicProperties[$name] = $value;
		} else
		{
			throw new Exception("Cannot set non-existing property '$name' in '".static::class."'");
		}
	}

	private function setPrimaryKey($name, $value): bool
	{
		$pos = strpos($name, ".");
		if($pos === false) {
			return false;
		}
		//this is a primary key value. See buildSelect()
		$alias = substr($name, 0, $pos);
		$col = substr($name, $pos + 1);
		if(!isset($this->primaryKeys[$alias])) {
			$this->primaryKeys[$alias] = [];
		}
		$this->primaryKeys[$alias][$col] = $value;

		return true;
	}

	/**
	 * Returns property names that are not returned to the client by default
	 *
	 * You may override and extend this.
	 * @return string[]
	 *@example
	 * ```
	 * protected static function atypicalApiProperties()
	 * {
	 *	 return array_merge(parent::atypicalApiProperties(), ['file']);
	 * }
	 * ```
		  *
	 	 */
	public static function atypicalApiProperties(): array
	{
		return ['modified', 'oldValues', 'validationErrors', 'modifiedCustomFields', 'validationErrorsAsString', 'searchDescription', 'returnAsText', 'dontChangeModifiedAt'];
	}

	/**
	 * Get the properties to fetch when using the find() method.
	 * These properties will be preloaded including related properties from other
	 * tables. They will also be returned to the client.
	 *
	 * If you have getters that typically shouldn't be returned by the API then list them in the
	 * @see atypicalApiProperties() method.
	 *
	 * @return string[]
	 */
	protected static function getDefaultFetchProperties(): array
	{
		$cls = static::class;

		$cacheKey = 'property-getDefaultFetchProperties-' . $cls;

		$props = go()->getCache()->get($cacheKey);

		if($props === null) {
			$props = array_diff(static::getReadableProperties(), static::atypicalApiProperties());

			go()->getCache()->set($cacheKey, $props);
		}

		return $props;
	}


	/**
	 * Find entities
	 *
	 * @param array $fetchProperties
	 * @param bool $readOnly
	 * @param Property|null $owner When finding relations the owner or parent Entity / Property is passed so the children can access it.
	 * @return static[]|Query
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	protected static function internalFind(array $fetchProperties = [], bool $readOnly = false, Property $owner = null) {

		$tables = self::getMapping()->getTables();

		if(empty($tables)) {
			throw new LogicException("No table defined for " . static::class);
		}

		$mainTableName = array_keys($tables)[0];

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$query = (new Query())
						->from($tables[$mainTableName]->getName(), $tables[$mainTableName]->getAlias())
						->setModel(static::class, $fetchProperties, $readOnly, $owner);


		$mappedQuery = static::getMapping()->getQuery();
		if (isset($mappedQuery)) {
			$query->mergeWith($mappedQuery);
		}

		self::joinAdditionalTables($tables, $query);
		self::buildSelect($query, $fetchProperties, $readOnly);

		return clone $query;
	}



	/**
	 * Changes the string key "1-2" into ['primaryKey1' => 1', 'primaryKey2' => '2]
	 *
	 * @param string $id eg. "1-2"
	 * @return array eg. ['primaryKey1' => 1', 'primaryKey2' => '2]
	 */
	public static function idToPrimaryKeys(string $id): array
	{
		$primaryTable = static::getMapping()->getPrimaryTable();

		//Used count check here because a customer managed to get negative ID's in the database.
		$keys = $primaryTable->getPrimaryKey();
		$ids = count($keys) == 1 ? [$id] : explode('-', $id);
		return array_combine($keys, $ids);
	}


  /**
   * Get properties that are minimally required to load for the object to function properly.
   *
   * @return string[]
   */
	protected static final function getRequiredProperties(): array
	{

		$cls = static::class;
//		if(isset(self::$requiredProps[$cls])) {
//			return self::$requiredProps[$cls];
//		}

		$cacheKey = $cls . '-required-props';

		$required = go()->getCache()->get($cacheKey);

		if($required === null) {


			$props = static::getApiProperties();

			$required = array_merge(static::getPrimaryKey(), static::internalRequiredProperties());

			//include these for title() in log entries
			$titleProps = ['title', 'name', 'subject', 'description', 'displayName'];

			foreach ($props as $name => $meta) {
				if (in_array($name, $titleProps) || ($meta['access'] === self::PROP_PROTECTED && !empty($meta['db']))) {
					$required[] = $name;
				}
			}

			$required = array_unique($required);

			go()->getCache()->set($cacheKey, $required);
		}

//		self::$requiredProps[$cls] = $required;

		return $required;
	}

	/**
	 * Override to always select these properties.
	 *
	 * This is cached so after changing this run /install/upgrade.php to reset the cache.
	 *
	 * @return string[]
	 */
	protected static function internalRequiredProperties(): array
	{
		return [];
	}

	/**
	 * Evaluates the given fetchProperties and configures the query object to fetch them.
	 *
	 * @param Query $query
	 * @param array $fetchProperties
	 * @param $readOnly
	 */
	private static function buildSelect(Query $query, array $fetchProperties, $readOnly) {

		$select = [];
		$selectProps = array_unique(array_merge(static::getRequiredProperties(), $fetchProperties));
		foreach (self::getMapping()->getTables() as $table) {

			if($table->isUserTable && !go()->getUserId()) {
				continue;
			}

			foreach($table->getMappedColumns() as $column) {
				if(in_array($column->name, $selectProps)) {
					$select[] = $table->getAlias() . "." . $column->name;
				}
			}

			//also select primary key values separately to check if tables were new when saving. They are stored in $this->primaryKeys when they go through the __set function.
			if(!$readOnly) {
				foreach($table->getPrimaryKey() as $pk) {
					//$query->select("alias.id AS `alias.userId`");
					$select[] = $table->getAlias() . "." . $pk . " AS `" . $table->getAlias() . "." . $pk ."`";
				}
			}

			if(!empty($table->getConstantValues())) {
				$query->andWhere($table->getConstantValues());
			}
		}

		$query->select($select, true);


	}

  /**
   *
   * It's not possible to use fetchproperties to determine if they need to be joined. Because the props
   * can also be used in the where or order by part of the query.
   *
   * @param array $tables
   * @param Query $query
   *
   */
	private static function joinAdditionalTables(array $tables, Query $query) {
		$first = array_shift($tables);

		$alias = $first->getAlias();
		foreach ($tables as $joinedTable) {
			static::joinTable($alias, $joinedTable, $query);
			$alias = $joinedTable->getAlias();
		}
	}

  /**
   * @param $lastAlias
   * @param MappedTable $joinedTable
   * @param Query $query
   */
	private static function joinTable($lastAlias, MappedTable $joinedTable, Query $query) {

		$on = "";
		foreach ($joinedTable->getKeys() as $from => $to) {
			if (!empty($on)) {
				$on .= " AND ";
			}

			if(strpos($from, '.') === false) {
				$from = $lastAlias . "." . $from;
			}

			if(strpos($to, '.') === false) {
				$to = $joinedTable->getAlias() . "." . $to;
			}

			$on .= $from . ' = ' . $to;
		}

		if($joinedTable->isUserTable) {
			if(!go()->getUserId()) {
				//throw new \Exception("Can't join user table when not authenticated");
				go()->debug("Can't join user table when not authenticated");
				return;
			}
			$on .= " AND " . $joinedTable->getAlias() . ".userId = " . go()->getUserId();
		}

		if(!empty($joinedTable->getConstantValues())) {
			$on = Criteria::normalize($on)->andWhere($joinedTable->getConstantValues());
		}
		$query->join($joinedTable->getName(), $joinedTable->getAlias(), $on, "LEFT");
	}

	/**
	 * Get all the modified properties with their new and old values.
	 *
	 * Only database columns and relations are tracked. Not the getters and setters.
	 *
	 * @example getting removed id's from array properry
	 *
	 * ```
	 * foreach($modified[1] as $model) {
			if(!in_array($model, $modified[0])) {
				CoreAlert::delete(['entityTypeId' => Task::entityType()->getId(), 'tag' => $model->id]);
			}
	  }
	 * ```
	 * @param array|string $properties If given only these properties will be checked for modifications.
	 * @return array ["propName" => [newval, oldval]]
	 */
	public function getModified($properties = []) {
		return $this->internalGetModified($properties);
	}

	/**
	 * Compare two dates
	 *
	 * @param DateTimeInterface|null $a
	 * @param DateTimeInterface|null $b
	 * @return bool
	 */
	private function datesAreDifferent(?DateTimeInterface $a, ?DateTimeInterface $b): bool
	{
		if(!isset($a) && isset($b)) {
			return true;
		}

		if(!isset($b) && isset($a)) {
			return true;
		}

		return $a->format('U') != $b->format('U');
	}

	/**
	 * @param array|string $properties
	 * @param bool $forIsModified
	 * @return array|bool eg. ["propName" => [newval, oldval]]
	 */
	protected function internalGetModified(array|string &$properties = [], bool $forIsModified = false): bool|array
	{

		if($this->readOnly) {
			return $forIsModified ? false : [];
		}

		if(!is_array($properties)) {
			$properties = [$properties];
		}

		if(empty($properties)) {
			$properties = array_keys($this->oldProps);

			if(method_exists($this, 'getCustomFields') && $this->getCustomFields()->isModified()) {
				if ($forIsModified) {
					return true;
				}
				$modified['customFields'] = $this->getCustomFields()->getModified();
			}
		}

		$modified = [];

		foreach($properties as $key) {

			$oldValue = $this->oldProps[$key] ?? null;
			$newValue = $this->{$key} ?? null;

			$propModified = $this->internalIsModified($newValue, $oldValue, static::isScalarRelation($key));
			if ($propModified) {
				if ($forIsModified) {
						return true;
				}
				$modified[$key] = [$newValue, $oldValue];
			}
		}

		if($forIsModified) {
			return false;
		}

		return $modified;
	}

	private static function isScalarRelation(string $propName) : bool{
		$relation = static::getMapping()->getRelation($propName);
		if(!$relation) {
			return false;
		}

		return $relation->type == Relation::TYPE_SCALAR;
	}

	private function internalIsModified($newValue, $oldValue, bool $isScalarRelation): bool
	{
		if($isScalarRelation) {
			//scalars must be checked without taking sort into regard
			$newValue = $newValue ?? [];
			$oldValue = $oldValue ?? [];

			if(count($newValue) != count($oldValue)) {
				return true;
			}
			sort($newValue);
			sort($oldValue);

			return $newValue != $oldValue;
		}

		if($newValue instanceof self) {
			if($newValue->isModified()) {
				return true;
			}
		} else
		{
			if($newValue instanceof CoreDateTime && $oldValue instanceof CoreDateTime) {
				if($this->datesAreDifferent($oldValue, $newValue)) {
					return true;
				}
			}	else if(is_array($newValue) && (($v = array_values($newValue)) && isset($v[0]) && $v[0] instanceof self)) {
				// Array comparison above might return false because the array contains identical objects but the objects itself might have changed.
				if(!is_array($oldValue) || count($oldValue) != count($newValue)) {

					return true;

				} else {
					foreach ($newValue as $v) {
						if ($v->isModified()) {
							return true;
						}
					}
				}
			} else if ($newValue !== $oldValue) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if entity or any of given property list is modified
	 *
	 * Only database columns and relations are tracked. Not the getters and setters.
	 *
	 * @param array|string $properties If empty then all properties are checked.
	 * @return boolean
	 */
	public function isModified($properties = []) {
		return $this->internalGetModified($properties, true);
	}

	/**
	 * Get a property value before it was modified
	 *
	 * @param string $propName
	 * @return mixed
	 */
	public function getOldValue(string $propName) {
		if(!array_key_exists($propName, $this->oldProps)){
			throw new InvalidArgumentException("Property " . $propName . " does not exist on " . static::class);
		}
		return $this->oldProps[$propName];
	}

	/**
	 * Get old values before they were modified
	 *
	 * @return array [Name => value]
	 * @noinspection PhpUnused
	 */
	public function getOldValues(): array
	{
		return $this->oldProps;
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

		if($this->readOnly) {
			throw new Exception("Models are fetched read only");
		}

		if (!$this->validate()) {
			return false;
		}

		if(!$this->saveTables()) {
			return false;
		}

		if (!$this->saveRelatedProperties()) {
			return false;
		}

		return true;
	}

  /**
   * Saves all modified properties to the database.
   * @throws Exception
   */
	protected function saveTables(): bool
	{
		if($this->readOnly) {
			throw new Exception("Can't save in read only mode");
		}
		$modified = $this->getModified();

		// make sure auto incremented values come first
		$tables = $this->getMapping()->getTables();
		usort($tables, function(Table $a, Table $b) {
			$aHasAI = $a->getAutoIncrementColumn();
			$bHasAI = $b->getAutoIncrementColumn();
			if($aHasAI && !$bHasAI) {
				return -1;
			}

			if($bHasAI && !$aHasAI) {
				return 1;
			}

			return 0;

		});

		foreach ($tables as $table) {
			if (!$this->saveTable($table, $modified)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * In some cases you don't want to change modifiedAt on save. Like when migrating data or building search cache that
	 * needs to copy that date.
	 *
	 * @var bool
	 */
	public $dontChangeModifiedAt = false;

	/**
	 * Sets some default values such as modifiedAt and modifiedBy
	 * @throws Exception
	 */
	private function setSaveProps(Table $table, $modifiedForTable) {

		if($table->getColumn("modifiedBy") && !isset($modifiedForTable["modifiedBy"])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->modifiedBy = $modifiedForTable['modifiedBy'] = $this->getDefaultCreatedBy();
		}

		if($table->getColumn("modifiedAt") && !isset($modifiedForTable["modifiedAt"]) && (!$this->dontChangeModifiedAt || !isset($this->modifiedAt))) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->modifiedAt = $modifiedForTable['modifiedAt'] = new DateTime('now', new DateTimeZone('UTC'));
		}

		if(!$this->isNew()) {
			return $modifiedForTable;
		}

		if($table->getColumn("createdAt") && !isset($modifiedForTable["createdAt"])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->createdAt = $modifiedForTable['createdAt'] = new DateTime('now', new DateTimeZone('UTC'));
		}

		if($table->getColumn("createdBy") && !isset($modifiedForTable["createdBy"])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->createdBy = $modifiedForTable['createdBy']= $this->getDefaultCreatedBy();
		}

		return $modifiedForTable;
	}

	protected function getDefaultCreatedBy(): ?int
	{
		return !App::get()->getAuthState() || !App::get()->getAuthState()->getUserId() ? 1 : App::get()->getAuthState()->getUserId();
	}

  /**
   * Saves all property relations
   *
   * @return boolean
   * @throws Exception
   */
	private function saveRelatedProperties(): bool
	{
		foreach ($this->getFetchedRelations() as $relation) {

			switch($relation->type) {
				case Relation::TYPE_HAS_ONE:
					if (!$this->saveRelatedHasOne($relation)) {
						$this->setValidationError($relation->name, ErrorCode::RELATIONAL, null, ['validationErrors' => $this->relatedValidationErrors, 'index' => $this->relatedValidationErrorIndex]);
						return false;
					}
				break;

				case Relation::TYPE_ARRAY:
					if (!$this->saveRelatedArray($relation)) {
						$this->setValidationError($relation->name, ErrorCode::RELATIONAL, null, ['validationErrors' => $this->relatedValidationErrors, 'index' => $this->relatedValidationErrorIndex]);
						return false;
					}
				break;

				case Relation::TYPE_MAP:
					if (!$this->saveRelatedMap($relation)) {
						$this->setValidationError($relation->name, ErrorCode::RELATIONAL, null, ['validationErrors' => $this->relatedValidationErrors, 'index' => $this->relatedValidationErrorIndex]);
						return false;
					}
				break;

				case Relation::TYPE_SCALAR:
					if (!$this->saveRelatedScalar($relation)) {
						$this->setValidationError($relation->name, ErrorCode::RELATIONAL, null, ['validationErrors' => $this->relatedValidationErrors, 'index' => $this->relatedValidationErrorIndex]);
						return false;
					}
				break;
			}
		}
		return true;
	}

  /**
   * @param Relation $relation
   * @return bool
   * @throws Exception
   */
	private function saveRelatedHasOne(Relation $relation): bool
	{
		if(!$this->isNew()) {
			//remove old model if it's replaced
			$modified = $this->getModified([$relation->name]);
			if (isset($modified[$relation->name][1]) && (!isset($modified[$relation->name][0]) || $modified[$relation->name][0]->isNew())) {
				if (!$modified[$relation->name][1]->internalDelete((new Query)->where($modified[$relation->name][1]->primaryKeyValues()))) {
					$this->relatedValidationErrors = $modified[$relation->name][1]->getValidationErrors();
					return false;
				}
			}
		}

		if (isset($this->{$relation->name})) {
			$prop = $this->{$relation->name};

			if(go()->getDebugger()->enabled) {
				$tables = $prop->getMapping()->getTables();
				$firstTable = array_shift($tables);
				if(!$firstTable->getPrimaryKey()) {
					throw new Exception("No primary key defined for ". $firstTable->getName());
				}
			}

			$this->applyRelationKeys($relation, $prop);
			if (!$prop->internalSave()) {
				$this->relatedValidationErrors = $prop->getValidationErrors();
				return false;
			}

			$this->savedPropertyRelations[] = $this->{$relation->name};
		}

		return true;
	}

	/**
	 * Keeps record of the index when a related has many prop save fails. This
	 * will be returned to the client.
	 *
	 * @var int
	 */
	private $relatedValidationErrorIndex = 0;

	/**
	 *
	 * @var array
	 */
	private $relatedValidationErrors = [];

  /**
   * @param Relation $relation
   * @return bool
   * @throws Exception
   */
	private function saveRelatedArray(Relation $relation): bool
	{

		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}

		//copy for overloaded properties because __get can't return by reference because we also return null sometimes.
		$models = $this->{$relation->name} ?? [];
		$this->relatedValidationErrorIndex = 0;

		$hasPk = $relation->propertyName::hasPrimaryKey();
		if($hasPk) {
			$this->removeRelated($relation, $models, $modified[$relation->name][1]);
		} else{
			//without pk remove all
			$this->removeAllRelated($relation);

			//reset models to new state because current ones think they're existing
			$models = array_map(function($model) {
				return $model->copy();
			}, $models);
		}

		$sortOrder = 0;
		$this->{$relation->name} = [];
		foreach ($models as $newProp) {

			//Check for invalid input
			if(!($newProp instanceof Property)) {
				throw new Exception("Invalid value given for '". $relation->name ."'. Should be a go\core\orm\Property");
			}

			$this->applyRelationKeys($relation, $newProp);

			if(isset($relation->orderBy)) {
				$newProp->{$relation->orderBy} = $sortOrder++;
			}

			if (!$newProp->internalSave()) {
				$this->relatedValidationErrors = $newProp->getValidationErrors();
				return false;
			}

			$this->savedPropertyRelations[] = $newProp;
			$this->relatedValidationErrorIndex++;

			$this->{$relation->name}[] = $newProp;
		}

		return true;
	}

	/**
	 * @param Relation $relation
	 * @return bool
	 * @throws SaveException
	 */
	private function removeAllRelated(Relation $relation): bool
	{
		$cls = $relation->propertyName;
		$where = $this->buildRelationWhere($relation);
		$query = new Query();
		$query->where($where);
		return $cls::internalDelete($query);
	}

  /**
   * Removes related models no longer in the object
   *
   * @param Relation $relation
   * @param self[] $models
   * @param self[]|null $oldModels
   * @return bool
   * @throws Exception
   */
	private function removeRelated(Relation $relation, array $models, ?array $oldModels): bool
	{
		$cls = $relation->propertyName;

		$query = new Query();

		$where = $this->buildRelationWhere($relation);
		if(!empty($where)) {
			$query->where($where);
		}

		if(!isset($oldModels)) {
			return true;
		}

		$removeKeys = new Criteria();
		$pk = $cls::getPrimaryKey();

		foreach($oldModels as $model) {
			if(self::arrayContains($models, $model)) {
				//if object is still present then don't remove
				continue;
			}

			$diff = array_diff($pk, array_values($relation->keys));
			$where = [];
			foreach($diff as $key) {
				$where[$key] = $model->$key;
			}
			$removeKeys->orWhere($where);
		}

		if(!$removeKeys->hasConditions()) {
			return true;
		}

		$query->andWhere($removeKeys);

		return $cls::internalDelete($query);
	}

	/**
	 * Check if an array of models contains a given property
	 * Also works for cloned objects because it checks class name and primary key
	 *
	 * @param Property[] $models
	 * @param Property $model
	 * @throws Exception
	 */
	private static function arrayContains(array $models, self $model) {
		foreach($models as $m) {
			if($m->equals($model)) {
				return true;
			}
		}
		return false;
	}

  /**
   * @param Relation $relation
   * @return bool
   * @throws Exception
   */
	private function saveRelatedScalar(Relation $relation): bool
	{
		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}

		$where = $this->buildRelationWhere($relation);

		$key = $relation->getScalarColumn();
		$old = $modified[$relation->name][1] ?? [];
		$new = $modified[$relation->name][0] ?? [];
		$removeIds = array_diff($old, $new);
		if(!empty($removeIds)) {

			$query = (new Query())->where($where);
			$query->andWhere($key, 'IN', $removeIds);
			if(!go()->getDbConnection()->delete($relation->tableName, $query)->execute()) {
				throw new Exception("Could not delete scalar relation ids");
			}
		}

		$insertIds = array_diff($new, $old);

		if(!empty($insertIds)) {
			$data = array_values(array_map(function($v) use($key, $where) {
				return array_merge($where, [$key => $v]);
			}, $insertIds));

			if(!go()->getDbConnection()->insert($relation->tableName, $data)->execute()) {
				throw new Exception("Could not insert scalar relation ids");
			}
		}

		return true;
	}

  /**
   * Save's a related map
   *
   * @param Relation $relation
   * @return bool
   * @throws Exception
   */
	private function saveRelatedMap(Relation $relation): bool
	{

		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}

		//copy for overloaded properties because __get can't return by reference because we also return null sometimes.
		$models = $this->{$relation->name} ?? [];
		$this->relatedValidationErrorIndex = 0;

		if(!$this->isNew()) {
			$this->removeRelated($relation, $models, $modified[$relation->name][1]);
		}

		$this->{$relation->name} = [];
		foreach ($models as $mapKey => $newProp) {

			if($newProp === null) {
				//deleted model
				continue;
			}

			//Check for invalid input
			if(!($newProp instanceof Property)) {
				throw new Exception("Invalid value given for '". $relation->name ."'. Should be a GO\Orm\Property");
			}

			$this->applyRelationKeys($relation, $newProp);

			// This went bad when creating new map values with "ext-gen1" as key.
			// Fixed it by recognizing _NEW_* as a map key that should not be applied as property
			foreach ($this->mapKeyToValues($mapKey, $relation) as $propName => $value) {
				if(empty($newProp->$propName)) {
					$newProp->$propName = $value;
				}
			}

			if (!$newProp->internalSave()) {
				$this->relatedValidationErrors = $newProp->getValidationErrors();
				return false;
			}

			$this->savedPropertyRelations[] = $newProp;
			$this->relatedValidationErrorIndex++;

			//$key = $this->buildMapKey($newProp, $relation);
			$this->{$relation->name}[$mapKey] = $newProp;
		}

		//If the array is empty then set it to null because an empty array will be converted to an array in JSON while a map should be an object.
		//We use null in this case.
		if(empty($this->{$relation->name})) {
			$this->{$relation->name} = null;
		}

		return true;
	}

	/**
	 * When the entity is saved and was new, the auto increment ID must be set to identifying relations
	 *
	 * @param Relation $relation
	 * @param Property $property
	 */
	private function applyRelationKeys(Relation $relation, Property $property) {

		foreach ($relation->keys as $from => $to) {
			$property->$to = $this->$from ?? null;
		}
	}

	private function extractModifiedForTable(MappedTable $table, array $modified): array
	{
		$modifiedForTable = [];

		$columns = $table->getColumns();
		foreach ($columns as $column) {
			if (isset($modified[$column->name])) {
				$modifiedForTable[$column->name] = $modified[$column->name][0];
			}
		}

		return $modifiedForTable;
	}

	private function recordIsNew(MappedTable $table): bool
	{
		$primaryKeys = $table->getPrimaryKey();
		if(empty($primaryKeys)) {
			//no primary key. Always insert.
			return true;
		}
		foreach($primaryKeys as $pk) {
			if(empty($this->primaryKeys[$table->getAlias()][$pk])) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Inserts the record into the database table when save() is performed
	 *
	 * @param Table $table
	 * @param array $record
	 * @throws Exception
	 */
	protected function insertTableRecord(Table $table, array $record) {
		$stmt = go()->getDbConnection()->insert($table->getName(), $record);
		if (!$stmt->execute()) {
			throw new Exception("Could not execute insert query");
		}
	}

	/**
	 * Updates the record in the database table when save() is performed
	 *
	 * @param Table $table
	 * @param array $record
	 * @param Query $query
	 * @throws Exception
	 */
	protected function updateTableRecord(Table $table, array $record, Query $query) {
		$stmt = go()->getDbConnection()->update($table->getName(), $record, $query);
		if (!$stmt->execute()) {
			throw new Exception("Could not execute update query");
		}
	}

	/**
	 * Saves properties to the mapped table
	 *
	 * @param MappedTable $table
	 * @param array $modified
	 * @return boolean
	 * @throws Exception
	 */
	private function saveTable(MappedTable $table, array &$modified): bool
	{

		if($table->isUserTable && (!go()->getAuthState() || !go()->getAuthState()->isAuthenticated())) {
			//ignore user tables when not logged in.
			return true;
		}

		$modifiedForTable = $this->extractModifiedForTable($table, $modified);
		$recordIsNew = $this->recordIsNew($table);
		if(!empty($modified) || $recordIsNew) {
			$modifiedForTable = $this->setSaveProps($table, $modifiedForTable);
		}

		if (empty($modifiedForTable) && !$recordIsNew) {
			return true;
		}

		// if(empty($table->getPrimaryKey())) {
		// 	throw new Exception("No primary key defined for table: '" . $table->getName() . "'");
		// }

		try {
			if ($recordIsNew) {

				foreach($table->getConstantValues() as $colName => $value) {
					$modifiedForTable[$colName] = $value;
				}

				if(empty($modifiedForTable) && (!$table->isUserTable || !static::getMapping()->hasUserTableRelation())) {
					//if there's no primary key we might get here.
					return true;
				}

				//this if for cases when a second table extends the model but the key is not part of the properties
				//For example Password extends User but the ket "userId" of password is not part of the properties
				foreach($table->getKeys() as $from => $to) {
					$to = Utils::splitTableAndColumn($to)->name;
					$from = Utils::splitTableAndColumn($from)->name;
					$modifiedForTable[$to] = $this->{$from} ?? null;
				}

				if($table->isUserTable || $this instanceof UserProperty) {
					$modifiedForTable["userId"] = go()->getUserId();
				}

				$this->insertTableRecord($table, $modifiedForTable);

				$this->handleAutoIncrement($table, $modified);

				//update primary key data for new state
				$this->primaryKeys[$table->getAlias()] = [];
				foreach($table->getKeys() as $from => $to) {
					$to = Utils::splitTableAndColumn($to)->name;
					$from = Utils::splitTableAndColumn($from)->name;
					$this->primaryKeys[$table->getAlias()][$to] = $this->$from;
				}
				if($table->isUserTable) {
					$this->primaryKeys[$table->getAlias()]['userId'] = go()->getUserId();
				}
			} else {
				if (empty($modifiedForTable)) {
					return true;
				}

				$keys = $this->primaryKeys[$table->getAlias()];
				if($table->isUserTable) {
					$keys['userId'] = go()->getUserId();
				}

				$query = Query::normalize($keys)->tableAlias($table->getAlias());

				$this->updateTableRecord($table, $modifiedForTable, $query);
			}
		} catch (PDOException $e) {
			ErrorHandler::logException($e);
			$uniqueKey = Utils::isUniqueKeyException($e);

			if ($uniqueKey) {
				$index = $table->getIndex($uniqueKey);

				$this->setValidationError($index ? $index['Column_name'] : $uniqueKey, ErrorCode::UNIQUE);
				return false;
			} else {
				if(isset($stmt)) {
					go()->error("Failed SQL: " . $stmt);
                    go()->error($e->getMessage());
                    go()->error($e->getTraceAsString());
				}
				throw $e;
			}
		}

    return true;
	}

	/**
	 * Get's the auto increment ID after an insert query and sets the property in this model
	 *
	 * @param MappedTable $table
	 * @param array $modified
	 * @throws Exception
	 */
	private function handleAutoIncrement(MappedTable $table, array &$modified) {
		$aiCol = $table->getAutoIncrementColumn();

		if ($aiCol) {
			$lastInsertId = intval(App::get()->getDbConnection()->getPDO()->lastInsertId());

			if (empty($lastInsertId)) {
				throw new Exception("Auto increment column didn't increment!");
			}
			$modified[$aiCol->name] = [$lastInsertId, null];
			$this->{$aiCol->name} = $lastInsertId;
		}
	}

	/**
	 * Rollback the insert ID after a save failed
	 *
	 * @param MappedTable $table
	 */
	private function rollBackAutoIncrement(MappedTable $table) {
		if(!$this->recordIsNew($table)) {
			return;
		}
		$aiCol = $table->getAutoIncrementColumn();

		if ($aiCol) {
			$this->{$aiCol->name} = null;
		}
	}

  /**
   * Sets the isNew prop and reset oldDbProps so that the record is no longer
   * in a modified state.
   * This happens after all related tables and properties are saved.
   *
   * @return boolean
   * @throws Exception
   */
	protected function commit(): bool
	{

		foreach ($this->savedPropertyRelations as $property) {
			$property->commit();
		}

		$this->savedPropertyRelations = [];
		$this->isNew = false;
		$this->trackModifications();

		return true;
	}

  /**
   * Rollback is called when something fails in the save operation.
   *
   * @return boolean
   * @throws Exception
   */
	protected function rollBack(): bool
	{

		foreach ($this->savedPropertyRelations as $property) {
			$property->rollBack();
		}

		$this->savedPropertyRelations = [];

		foreach ($this->getMapping()->getTables() as $table) {
			$this->rollBackAutoIncrement($table);
		}

		return true;
	}

  /**
   * Parses ID into query
   *
   * eg. "1-1" into ['`alias`.`col1`' => 1, '`alias`.`col2`'' => 1];
   *
   * @param string $id
   * @return array
   * @throws InvalidArguments
   * @throws Exception
   */
	public static function parseId(string $id): array
	{
		$primaryTable = static::getMapping()->getPrimaryTable();
		$pk = $primaryTable->getPrimaryKey();

		$props = [];
		$keys = explode('-', $id);

		if(count($keys)  != count($pk)) {
			throw new InvalidArguments("Invalid ID given for " . static::class.' : '.$id);
		}
		foreach ($pk as $key) {
			$props['`' . $primaryTable->getAlias() . '`.`' . $key . '`'] = array_shift($keys);
		}

		return $props;
	}

	/**
	 * Statement for last delete() call.
	 *
	 * @var Statement
	 */
	public static $lastDeleteStmt;

	/**
	 * Delete this model
	 *
	 * When finding the models to delete in an override use mergeWith():
	 *
	 * self::find()->mergeWith($query);
	 *
	 */
	protected static function internalDelete(Query $query): bool
	{
		$primaryTable = static::getMapping()->getPrimaryTable();

		self::$lastDeleteStmt = go()->getDbConnection()->delete($primaryTable->getName(), $query);
		if(!self::$lastDeleteStmt->execute()) {
			return false;
		}
//		if(go()->getDebugger()->enabled) {
//			go()->debug("Deleted " . self::$lastDeleteStmt->rowCount() . " models of type " . static::class);
//		}
		return true;
	}

	private function validateTable(MappedTable $table) {

		if(!$this->tableIsModified($table)) {
			// table record will not be validated and inserted if it has no modifications at all
			// todo: perhaps this should be configurable?
			return;
		}

		foreach ($table->getMappedColumns() as $colName => $column) {
			//Assume constants are correct, and this makes it unnecessary to declare the property
			if(array_key_exists($colName, $table->getConstantValues()) || !in_array($colName, $this->selectedProperties)) {
				continue;
			}

			$this->validateColumn($column, $this->$colName ?? null);
		}
	}

  /**
   * Check's if the database conditions are met.
   *
   * @param Column $column
   * @param mixed $value
   */
	private function validateColumn(Column $column, $value): void
	{
		if (!$this->validateRequired($column)) {
			return;
		}

		//Null is allowed because we checked this above.
		if(empty($value)) {
			return;
		}

		switch ($column->dbType) {
			case 'date':
			case 'datetime':
				if(!($value instanceof CoreDateTime) && !($value instanceof DateTimeImmutable)){
					$this->setValidationError($column->name, ErrorCode::MALFORMED, "No date object given for date column");
				}
				break;

			case 'enum':
				if(!preg_match('/enum\((.*)\)/i', $column->dataType, $matches)) {
					$this->setValidationError($column->name, ErrorCode::GENERAL, "Enum column has no values specified in database");
					return;
				}

				$enumValues = str_getcsv(strtolower($matches[1]), ',' , "'");

				if(!in_array(strtolower($value), $enumValues)) {
					$this->setValidationError($column->name, ErrorCode::MALFORMED, "Invalid value (".$value.") for " . $column->dataType);
					return;
				}
				break;

			default:
				$this->validateColumnString($column, $value);
		}
	}

  /**
   * Check's if the given value is a string and not to long
   *
   * @param Column $column
   * @param $value
   * @return bool
   */
	private function validateColumnString(Column $column, $value): bool
	{
		if(!is_scalar($value) && (!is_object($value) || !method_exists($value, '__toString'))) {
			$this->setValidationError($column->name, ErrorCode::MALFORMED, "Non scalar value given. Type: ". gettype($value));
			return false;
		}

		if (!empty($column->length)){
			if(StringUtil::length($value) > $column->length) {
				$this->setValidationError($column->name, ErrorCode::MALFORMED, 'Length can\'t be greater than ' . $column->length . '. Value given: ' . $value);
				return false;
			}
		}
		return true;
	}

  /**
   * @param MappedTable $table
   * @return bool
   */
	private function tableIsModified(MappedTable $table) {
		return $this->isModified(array_keys($table->getMappedColumns()));
	}

  /**
   * Check's if required columns are set
   *
   * @param Column $column
   * @return bool
   */
	private function validateRequired(Column $column): bool
	{

		if (!$column->required || $column->primary) {
			return true;
		}

		switch ($column->dbType) {

			case 'int':
			case 'tinyint':
			case 'smallint':
			case 'bigint':

			case 'float':
			case 'double':
			case 'decimal':

			case 'datetime':

			case 'date':

			case 'binary':
				if (!isset($this->{$column->name})) {
					$this->setValidationError($column->name, ErrorCode::REQUIRED);
					return false;
				}
				break;
			default:
				if (empty($this->{$column->name})) {
					$this->setValidationError($column->name, ErrorCode::REQUIRED);
					return false;
				}
				break;
		}


		return true;
	}

  /**
   * Set's validation errors on this model if there are any
   *
   * Override this and implement custom validation
   * @throws Exception
   */
	protected function internalValidate() {
		foreach ($this->getMapping()->getTables() as $table) {
			$this->validateTable($table);
		}
	}

	public function toArray(array $properties = null): array|null
	{
		if (empty($properties)) {
			$properties = $this->fetchProperties;
		}

		return parent::toArray($properties);
	}

  /**
   * Normalizes API input for this model.
   *
   * @param string $propName
   * @param mixed $value
   * @return mixed
   * @throws Exception
   */
	protected function normalizeValue(string $propName, $value): mixed
	{
		$relation = static::getMapping()->getRelation($propName);
		if ($relation) {

			switch($relation->type) {

				case Relation::TYPE_HAS_ONE:
					if(isset($value) && isset($this->$propName)) {
						//if a has one relation exists then apply the new values to the existing property instead of creating a new one.
						return $this->$propName->setValues($value);
					} else {
						return $this->internalNormalizeRelation($relation, $value);
					}

				case Relation::TYPE_ARRAY:
					return $this->patchArray($relation, $propName, $value);

				case Relation::TYPE_MAP:
					return $this->patchMap($relation, $propName, $value);

				case Relation::TYPE_SCALAR:
					return $value;
			}
		}

		$column = static::getMapping()->getColumn($propName);
		if ($column && !static::isProtectedProperty($column->name)) {
			return $column->normalizeInput($value);
		}

		return $value;
	}

	/**
	 * Patches an array relation with new objects or arrays
	 *
	 * @param Relation $relation
	 * @param string $propName
	 * @param array|null $value
	 * @return mixed
	 * @throws Exception
	 */
	protected function patchArray(Relation $relation, string $propName, ?array $value) {
		$old = $this->$propName;
		/** @var self[] $old */

		$hasPK = $relation->propertyName::hasPrimaryKey();

		//build map for lookup
		if($hasPK) {
			$mapped = [];
			foreach ($old as $prop) {
				$id = $prop->id();
				if ($id) {
					$mapped[$id] = $prop;
				}
			}
		} else{
			// use index to update existing
			// this will avoid delete and inserts if you overwrite contacts emailAddresses with identical values.
			// in example when syncing the same LDAP profile values
			$mapped = $old;
		}

		$this->$propName = [];
		if(isset($value)) {
			for ($i = 0, $c = count($value); $i < $c; $i++) {

				$patch = $value[$i];
				//if it's already a Property model then use it and continue
				if($patch instanceof  $relation->propertyName) {
					$this->{$propName}[] = $patch;
					continue;
				}

				//check if we can find an existing property model to patch.
				if($hasPK) {
					$temp = new $relation->propertyName($this);
					$temp->setValues($patch);
					$id = $temp->id();
				} else{
					// without PK update by index
					$id = $i;
				}

				if (isset($mapped[$id])) {
					$mapped[$id]->setValues($patch);
					$this->{$propName}[] = $mapped[$id];
				} else {
					//create new model
					$this->{$propName}[] = $hasPK ? $temp : (new $relation->propertyName($this))->setValues($patch);
				}
			}
		}


		return $this->$propName;
	}

  /**
   * Patches a map relation with new objects or arrays
   *
   * @param Relation $relation
   * @param string $propName
   * @param array|null $value
   * @return mixed
   * @throws Exception
   */
	protected function patchMap(Relation $relation, string $propName, ?array $value) {
		$old = $this->$propName;
		$this->$propName = [];
		if(isset($value)) {
			foreach ($value as $id => $patch) {
				if (is_array($old) && isset($old[$id])) {
					$this->$propName[$id] = $old[$id];
					if (is_array($patch)) { //may be given as bool
						$this->$propName[$id]->setValues($patch);
					} else if($patch === false || $patch === null) {
						unset($this->$propName[$id]);
					}
				} else {

					$this->$propName[$id] = $this->internalNormalizeRelation($relation, $patch);

					//might be null when map keys are set to false or null
					if($this->$propName[$id] != null) {

						foreach ($this->mapKeyToValues($id, $relation) as $key => $value) {
							if (empty($this->$propName[$id]->$key)) {
								$this->$propName[$id]->$key = $value;
							}
						}
					} else {
						unset($this->$propName[$id]);
					}
				}
			}
		}

		return $this->$propName;
	}

  /**
   * Takes a map key eg. ['1-2' => ['propa' => 'foo']]
   * and converts the key ('1-2') to a key value areay of properties.
   *
   * @param string $id
   * @param Relation $relation
   * @return array
   * @throws Exception
   */
	private function mapKeyToValues(string $id, Relation $relation): array
	{
		if(substr($id, 0, 5) == "_NEW_") {
			//hacky for new items
			return [];
		}

		$values = explode("-", $id);

		$cls = $relation->propertyName;

		$pk = $cls::getPrimaryKey();

		$diff = array_diff($pk, array_values($relation->keys));

		$id = [];
		foreach($diff as $field) {
			$id[$field] = array_shift($values);
			if(is_numeric($id[$field])) {
				$id[$field] = (int) $id[$field];
			}
		}
		return $id;
	}

  /**
   * Check's API input and converts it to properties
   *
   * @param Relation $relation
   * @param $value
   * @return self|null
   * @throws InvalidArgumentException
   */
	private function internalNormalizeRelation(Relation $relation, $value): ?Property
	{
		$cls = $relation->propertyName;
		if ($value instanceof $cls) {
			throw new InvalidArgumentException("Deprecated use of setValues with object");
		}

		if(is_bool($value)) {
			// for maps with {1: false}
			$value = $value ? [] : null;
		}

		if (is_array($value)) {
			$o = new $cls($this);
			/** @var self $o */
			$o->setValues($value);

			return $o;
		} else if (is_null($value)) {
			return null;
		} else {
			throw new InvalidArgumentException("Invalid value given to relation '" . $relation->name . "'. Should be an array or an object of type '" . $relation->propertyName . "': " . var_export($value, true));
		}
	}

	/**
	 * Returns true is the model is new and not saved to the database yet.
	 *
	 * @return boolean
	 */
	public function isNew(): bool
	{
		return $this->isNew;
	}

  /**
   * Get the primary key value
   *
   * @return array eg ['id' => 1]
   */
	public function primaryKeyValues(): array
	{
		$keys = static::getPrimaryKey();
		$v = [];
		foreach($keys as $key) {
			$v[$key] = $this->$key;
		}

		return $v;
	}

	/**
	 * By the default the primary key of the first mapped table is used.
	 * If you're composing a complex model or extended model you might need to override this
	 * to allow a key from multiple tables.
	 *
	 * @example
	 * ```
	 *
	 * protected static function defineMapping()
	 * {
	 * 	return parent::defineMapping()
	 * 		->addTable("business_finance_contact_business", "biz", ["c.id" => "biz.contactId"])
	 * 		->setQuery((new Query())
	 * 		->select('min(doc.expiresAt) AS expiresAt', true)
	 * 		->join(
	 * 		"business_finance_document",
	 * 		"doc",
	 * 		"doc.organizationId = c.id AND doc.businessId = biz.businessId AND doc.type = '". FinanceDocument::TYPE_SALES_INVOICE ."'",
	 * 		"INNER")
	 * 		->groupBy(['c.id'])
	 * 	  );
	 * 	}
	 *
	 * 	protected static function definePrimaryKey()
	 * 	{
	 * 	  return ['id', 'businessId'];
	 * 	}
	 *
	 * ```
	 * @return string[]
	 */
	protected static function definePrimaryKey(): array
	{
		$primaryTable = static::getMapping()->getPrimaryTable();
		return $primaryTable ? $primaryTable->getPrimaryKey() : [];
	}


	/**
	 * Check if property has a primary key
	 *
	 * @return bool
	 */
	public static final function hasPrimaryKey() : bool {
		return !empty(static::getPrimaryKey());
	}

  /**
   * Get the primary key column names.
   *
   * If you need the values {@see primaryKeyValues()}
   *
   * @param boolean $withTableAlias
   * @return string[]
   */
	public static final function getPrimaryKey(bool $withTableAlias = false): array
	{

		$keys = static::definePrimaryKey();

		if(!$withTableAlias) {
			return $keys;
		}

		$keysWithAlias = [];
		foreach($keys as $key) {
			/** @noinspection PhpPossiblePolymorphicInvocationInspection */
			$alias = static::getMapping()->getColumn($key)->table->getAlias();
			$keysWithAlias[] = $alias . '.' . $key;
		}
		return $keysWithAlias;
	}

  /**
   * Checks if the given property or entity is equal to this
   *
   * @param mixed $property
   * @return boolean
   * @throws Exception
   */
	public function equals($property): bool
	{
		if(get_class($property) != get_class($this)) {
			return false;
		}

		if($property->isNew() || $this->isNew()) {
			return false;
		}

		return $this->id() == $property->id();
	}

	/**
	 * Cuts all properties to make sure they are not longer than the database can store.
	 * Useful when importing or syncing
	 */
	public function cutPropertiesToColumnLength() {

		$tables = self::getMapping()->getTables();
		foreach($tables as $table) {
			foreach ($table->getColumns() as $column) {
        if (($column->pdoType == PDO::PARAM_STR) && $column->length && isset($this->{$column->name})) {
					$this->{$column->name} = StringUtil::cutString($this->{$column->name}, $column->length, false, "");
				}
			}
		}
	}

	/**
	 * Copy the property or entity.
	 *
	 * The property will not be saved to the database.
	 * The primary key values will not be copied.
	 *
	 * @example
	 * $sourceAcl = \go\core\model\Acl::findById($type->acl_id);
	 * $newAcl = $sourceAcl->copy();
	 *
	 * @example Copy relation array
	 * $sourceAcl = \go\core\model\Acl::findById($type->acl_id);
	 * $targetAcl = $tasklist->findAcl();
	 * $targetAcl->groups = array_map(function($g) {
	 * 	return $g->copy;
	 * }, $sourceAcl->groups);
	 *
	 * @return static
	 * @throws Exception
	 */
	public function copy() : static
	{

		if($this instanceof Entity) {
			$copy = new static();
		} else
		{
			$copy = new static($this);
		}

		//copy public and protected columns except for auto increments.
		$props = $this->getApiProperties();

		foreach($props as $name => $p) {
			if(!isset($p['access']) && (!$p['getter'] || !$p['setter'])) {
				continue;
			}

			if($p['getter'] && $p['setter']) {
				$setter = "set".$name;
				$getter = "get".$name;
				$copy->$setter($this->$getter());
				continue;
			}
			$col = static::getMapping()->getColumn($name);
			if($col) {
				if(!$col->autoIncrement) {
					$copy->$name = $this->$name;
				}
			} else {
				$rel = static::getMapping()->getRelation($name);
				if($rel) {
					if(is_array($this->$name)) {
						foreach($this->$name as $key => $value) {
							$copy->$name[$key] = $value instanceof self ? $value->copy() : $value;
						}
					} else{
						$copy->$name = $this->$name instanceof self ? $this->$name->copy() : $this->$name;
					}
				} else {
					// protected prop that's neither a column or relation
					$copy->$name = $this->$name;
				}
			}
		}

		return $copy;
	}
}
