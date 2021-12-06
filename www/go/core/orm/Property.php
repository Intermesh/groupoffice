<?php

namespace go\core\orm;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use go\core\App;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\db\Statement;
use go\core\db\Utils;
use go\core\event\EventEmitterTrait;
use go\core\fs\Blob;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use DateTime as CoreDateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\validate\ValidationTrait;
use InvalidArgumentException;
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
	 * @param Property $owner
	 * @param boolean $isNew Indicates if this model is saved to the database.
	 * @param string[] $fetchProperties The properties that were fetched by find. If empty then all properties are fetched
	 * @param bool $readOnly Entities can be fetched readonly to improve performance
	 * @throws Exception
	 * @noinspection PhpMissingParamTypeInspection
	 */
	public function __construct($owner, bool $isNew = true, array $fetchProperties = [], bool $readOnly = false) {
		$this->isNew = $isNew;

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$this->fetchProperties = $fetchProperties;
		$this->readOnly = $readOnly;
		$this->owner = $owner;
		$this->selectedProperties = array_unique(array_merge($this->getRequiredProperties(), $this->fetchProperties));

		$this->initDatabaseColumns($this->isNew);
		$this->initRelations();
		if(!$readOnly) {
			$this->trackModifications();
		}

		// When properties have default values in the model they are overwritten by the database defaults. We change them back here so the
		// modification is tracked and it will be saved.
		if($this->isNew) {
			foreach($this->defaults as $key => $value) {
				$this->$key = $value;
			}
		}
		$this->init();
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
   * @param boolean $loadDefault
   * @throws Exception
   */
	private function initDatabaseColumns(bool $loadDefault) {
		$m = static::getMapping();
		foreach($this->selectedProperties as $propName) {
			$col = $m->getColumn($propName);
			if($col) {
				if($loadDefault) {
					if(isset($this->$propName)) {
						$this->defaults[$propName] = $this->$propName;
					}
					$this->$propName = $col->castFromDb($col->default);
				} else{
					$this->$propName = $col->castFromDb($this->$propName);
				}				
			}
		}
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
   * @throws Exception
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
   * @throws Exception
   */
	private function initRelations() {		
		foreach ($this->getFetchedRelations() as $relation) {
			$cls = $relation->propertyName;

			$where = $this->buildRelationWhere($relation);

			switch($relation->type) {

				case Relation::TYPE_HAS_ONE:
					if($this->isNew() ) {
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
						$prop = new $cls($this);
						$this->applyRelationKeys($relation, $prop);
					}
					$this->{$relation->name} = $prop;
				break;

				case Relation::TYPE_ARRAY:

					if($this->isNew() ) {
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

					if($this->isNew() ) {
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

					if($this->isNew()) {
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
   * @return Statement|mixed
   * @throws Exception
   */
	private static function queryScalar($where, Relation $relation) {
		$cacheKey = static::class.':'.$relation->name;

		if(!isset(self::$cachedRelations[$cacheKey])) {
			$key = $relation->getScalarColumn();
			$query = (new Query)->selectSingleValue($key)->from($relation->tableName);
			foreach($where as $field => $value) {
				$query->andWhere($field . '= :'.$field);
			}
			$stmt = $query->createStatement();
			self::$cachedRelations[$cacheKey] = $stmt;
		} else
		{
			$stmt = self::$cachedRelations[$cacheKey] ;			
		}

		foreach($where as $field => $value) {
			$stmt->bindValue(':'.$field, $value);
		}
		$stmt->execute();

		return $stmt;
	}

	/**
	 * For reusing prepared statements
	 */
	private static $cachedRelations = [];


	private static function queryRelation($cls, array $where, Relation $relation, $readOnly, $owner): Statement
	{

		/** @var Entity $cls */
		$query = $cls::internalFind([], $readOnly, $owner);

		foreach($where as $field => $value) {
			$query->andWhere($field . '= :'.$field);
		}

		if(is_a($relation->propertyName, UserProperty::class, true)){
			$query->andWhere('userId', '=', go()->getAuthState()->getUserId() ?? null);
		}

		if(!empty($relation->orderBy)) {
			$query->orderBy([$relation->orderBy => 'ASC']);
		}

		$stmt = $query->createStatement();

		foreach($where as $field => $value) {
			$stmt->bindValue(':'.$field, $value);
		}
		$stmt->execute();

		return $stmt;

	}

	private function buildRelationWhere(Relation $relation): array
	{
		$where = [];
		foreach ($relation->keys as $from => $to) {
			$where[$to] = $this->$from;
		}
		return $where;
	}


  /**
   * Build a key of the primary keys but omit the key from the relation because it's not needed as it's a property.
   *
   * @param Property $v
   * @param Relation $relation
   * @return string
   * @throws Exception
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
	 * By default all non-static public and protected properties + dynamically mapped properties.
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
			if (!$prop->isStatic()) {
				$p[] = $prop->getName();
			}
		}

		$exclude = ['isNew', 'oldProps', 'fetchProperties', 'selectedProperties', 'owner'];
		$p = array_diff($p, $exclude);

		App::get()->getCache()->set($cacheKey, $p);

		return array_unique($p);
	}

  /**
   * Copies all properties so isModified() can detect changes.
   * @throws Exception
   */
	private function trackModifications() {
		foreach ($this->watchProperties() as $propName) {
			$v = $this->$propName;
			$this->oldProps[$propName] = $v;
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

	private static $mapping;



	public static function clearCache() {
		self::$mapping = [];
	}

  /**
   * Returns the mapping object that is defined in defineMapping()
   *
   * @return Mapping
   */
	public final static function getMapping(): Mapping
	{
		$cls = static::class;
		if(isset(self::$mapping[$cls])) {
			return self::$mapping[$cls];
		}		
		$cacheKey = 'mapping-' . $cls;
		
		self::$mapping[$cls] = go()->getCache()->get($cacheKey);
		if(self::$mapping[$cls] === null) {
			self::$mapping[$cls] = static::defineMapping();

			static::fireEvent(self::EVENT_MAPPING, self::$mapping[$cls]);
			
			go()->getCache()->set($cacheKey, self::$mapping[$cls]);
		}

		return self::$mapping[$cls];
	}

	/**
	 * Get ID which is are the primary keys combined with a "-".
	 *
	 * @return string eg. "1" or with multiple keys: "1-2"
	 */
	public function id() {
		if(property_exists($this, 'id')) {
			return $this->id;
		}
		$keys = $this->primaryKeyValues();
		if(empty($keys)) {
			return false;
		}
		return count($keys) > 1 ? implode("-", array_values($keys)) : array_values($keys)[0];
	}

  /**
   * Get all API properties
   *
   * @return array
   */
	public static function getApiProperties(): array
	{
		$cacheKey = 'property-getApiProperties-' . static::class;

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
		if(!$this->readOnly && $this->setPrimaryKey($name, $value)) {
			return ;
		}

		//Support for dynamically mapped props via EVENT_MAP
		$props = static::getApiProperties();
		if(isset($props[$name]) && !empty($props[$name]['dynamic'])) {			
			$this->dynamicProperties[$name] = $value;
		} else
		{
			throw new Exception("Can't set not existing property '$name' in '".static::class."'");
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
		return ['modified', 'oldValues', 'validationErrors', 'modifiedCustomFields', 'validationErrorsAsString'];
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
		
		$cacheKey = 'property-getDefaultFetchProperties-' . static::class;
		
		$props = go()->getCache()->get($cacheKey);
		
		if($props === null) {
			$props = array_diff(static::getReadableProperties(), static::atypicalApiProperties());

			go()->getCache()->set($cacheKey, $props);
		}
		return $props;
	}	

	private static $findCache = [];

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

		$mainTableName = array_keys($tables)[0];
		
		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$query = (new Query())
						->from($tables[$mainTableName]->getName(), $tables[$mainTableName]->getAlias())						
						->setModel(static::class, $fetchProperties, $readOnly, $owner);

		self::joinAdditionalTables($tables, $query);
		self::buildSelect($query, $fetchProperties, $readOnly);

		return clone $query;
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
	 * @throws PDOException
	 * @return static|false
	 */
	protected static function internalFindById(string $id, array $properties = [], bool $readOnly = false)
	{
		$query = static::internalFind($properties, $readOnly);
		$keys = static::idToPrimaryKeys($id);
		$query->where($keys);
		
		return $query->single();
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

		$cacheKey = $cls . '-required-props';

		$required = go()->getCache()->get($cacheKey);

		if($required !== null) {
			return $required;
		}

		$props = static::getApiProperties();

		$required = array_merge(static::getPrimaryKey(), static::internalRequiredProperties());

		//include these for title() in log entries
		$titleProps = ['title', 'name', 'subject', 'description', 'displayName'];

		foreach($props as $name => $meta) {
			if(in_array($name, $titleProps) || ($meta['access'] === self::PROP_PROTECTED && !empty($meta['db']))) {
				$required[] = $name;
			}
		}

		$required = array_unique($required);

		go()->getCache()->set($cacheKey, $required);
		
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

		$mappedQuery = static::getMapping()->getQuery();
		if (isset($mappedQuery)) {
			$query->mergeWith($mappedQuery);
		}
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
   * @param DateTime|null $a
   * @param DateTime|null $b
   * @return bool
   */
	private function datesAreDifferent(?CoreDateTime $a, ?CoreDateTime $b): bool
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
	private function internalGetModified($properties = [], bool $forIsModified = false) {

		if(!is_array($properties)) {
			$properties = [$properties];
		}

		if(empty($properties)) {
			$properties = array_keys($this->oldProps);
		} else{

			//only check fetched properties
			$properties = array_intersect($properties, $this->fetchProperties);
		}

		$modified = [];

		foreach($properties as $key) {

			$oldValue = $this->oldProps[$key] ?? null;
			
			$newValue = $this->{$key};
			
			if($newValue instanceof self) {
				if($newValue->isModified()) {
					if($forIsModified) {
						return true;
					}
					$modified[$key] = [$newValue, null];
				}
			} else 
			{			
				if($newValue instanceof CoreDateTime) {
					if($this->datesAreDifferent($oldValue, $newValue)) {
						if($forIsModified) {
							return true;
						}

						$modified[$key] = [$newValue, $oldValue];	
					}
				}	else if ($newValue !== $oldValue) {
					if($forIsModified) {
						return true;
					}
					$modified[$key] = [$newValue, $oldValue];
				} else if(is_array($newValue) && (($v = array_values($newValue)) && isset($v[0]) && $v[0] instanceof self)) {
					// Array comparison above might return false because the array contains identical objects but the objects itself might have changed.
					foreach($newValue as $v) {
						if($v->isModified()) {
							if($forIsModified) {
								return true;
							}

							$modified[$key] = [$newValue, $oldValue];
							break;
						}
					}
				}
			}			
		}

		if($forIsModified) {
			return false;
		}

		return $modified;
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
	 * @throws Exception
	 */
	public function getOldValue(string $propName) {
		if(!array_key_exists($propName, $this->oldProps)){
			throw new Exception("Property " . $propName . " does not exist");
		}
		return $this->oldProps[$propName];
	}
	
	/**
	 * Get old values before they were modified
	 * 
	 * @return array [Name => value]
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

		$this->checkBlobs();

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
   * Get all columns containing blob id's
   *
   * @return string[]
   */
	private static function getBlobColumns(): array
	{

		$cacheKey = 'property-getBlobColumns-' . static::class;

		$cols = go()->getCache()->get($cacheKey);

		if($cols !== null) {
			return $cols;
		}
		
		$refs = Blob::getReferences();
		$cols = [];
		foreach(static::getMapping()->getTables() as $table) {
			foreach($table->getMappedColumns() as $col) {
				foreach($refs as $r) {
					if($r['table'] == $table->getName() && $r['column'] == $col->name) {
						$cols[] = $col->name;
					}
				}
			}
		}

		//check scalar blobs
		foreach(static::getMapping()->getRelations() as $rel) {
			if($rel->type != Relation::TYPE_SCALAR) {
				continue;
			}

			foreach($refs as $r) {
				if ($r['table'] == $rel->tableName && $r['column'] == $rel->getScalarColumn()) {
					$cols[] = $rel->name;
				}
			}
		}

		go()->getCache()->set($cacheKey, $cols);
		
		return $cols;
	}

  /**
   * @throws Exception
   */
	private function checkBlobs() {
		$blobIds = [];
		foreach(static::getBlobColumns() as $col) {
			if($this->isModified([$col])) {
				$mod = array_values($this->getModified([$col]))[0];
				
				if(isset($mod[0])) {
					$v = $mod[0];

					if (is_array($v)) {
						$blobIds = array_merge($blobIds, $v);
					} else {
						$blobIds[] = $v;
					}
				}
				
				if(isset($mod[1])) {
					$v = $mod[1];

					if(is_array($v)) {
						$blobIds = array_merge($blobIds, $v);
					} else {
						$blobIds[] = $v;
					}
				}
			}
		}
		
		foreach($blobIds as $id) {
			Blob::findById($id)->setStaleIfUnused();
		}
	}

	/**
	 * Sets some default values such as modifiedAt and modifiedBy
	 * @throws Exception
	 */
	private function setSaveProps(Table $table, $modifiedForTable) {
		
		if($table->getColumn("modifiedBy") && !isset($modifiedForTable["modifiedBy"])) {
			/** @noinspection PhpUndefinedFieldInspection */
			$this->modifiedBy = $modifiedForTable['modifiedBy'] = $this->getDefaultCreatedBy();
		}
		
		if($table->getColumn("modifiedAt") && !isset($modifiedForTable["modifiedAt"])) {
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
		
		//remove old model if it's replaced
		if(!$this->isNew()) {
			$modified = $this->getModified([$relation->name]);
			if (isset($modified[$relation->name][1])) {
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

		$hasPk = !empty($relation->propertyName::getPrimaryKey());
		if($hasPk) {
			$this->removeRelated($relation, $models, $modified[$relation->name][1]);
		} else{
			//without pk remove all
			$this->removeAllRelated($relation);

			//reset models to new state because current ones think they're existing
			$models = array_map(function($model) {
				return $model->internalCopy();
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
		$where = $this->buildRelationWhere($relation);
		$query = new Query();
		$query->where($where);

		if(!isset($oldModels)) {
			return true;
		}

		$removeKeys = new Criteria();
		$pk = $cls::getPrimaryKey();

		foreach($oldModels as $model) {
			if(in_array($model, $models)) {
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

			// This wen't bad when creating new map values with "ext-gen1" as key.
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
			$property->$to = $this->$from;
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
					$modifiedForTable[$to] = $this->{$from};
				}

				if($table->isUserTable || $this instanceof UserProperty) {
					$modifiedForTable["userId"] = go()->getUserId();
				}

				$this->insertTableRecord($table, $modifiedForTable);

				$this->handleAutoIncrement($table, $modified);
				
				//update primary key data for new state
				$this->primaryKeys[$table->getAlias()] = [];
				foreach($table->getKeys() as $from => $to) {
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

				$this->setValidationError($index['Column_name'], ErrorCode::UNIQUE);				
				return false;
			} else {
				if(isset($stmt)) {
					go()->error("Failed SQL: " . $stmt);
                    go()->error($e->getMessage());
                    go()->error($e->getTraceAsString());
				}
				throw $e;
			}
		} catch (Exception $e) {
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
	 * Delete this model
	 *
	 * When finding the models to delete in an override use mergeWith():
	 *
	 * self::find()->mergeWith($query);
	 *
	 * @throws SaveException
	 */
	protected static function internalDelete(Query $query): bool
	{

		$primaryTable = static::getMapping()->getPrimaryTable();

		$blobIds = static::getBlobsToCheckAfterDelete($query);
		
		$stmt = go()->getDbConnection()->delete($primaryTable->getName(), $query);
		if(!$stmt->execute()) {			
			return false;
		}

		go()->debug("Deleted " . $stmt->rowCount() ." models of type " .static::class);

		if(count($blobIds)) {
			$blobs = Blob::find()->where('id', '=', $blobIds);
			foreach($blobs as $blob) {
				$blob->setStaleIfUnused();
			}
		}
		return true;
	}

  /**
   * @param Query $query
   * @return array
   */
	private static function getBlobsToCheckAfterDelete(Query $query): array
	{
		
		$blobCols = static::getBlobColumns();
		if(!count($blobCols)) {
			return [];
		}
		$clnQry = clone($query);
		$clnQry->select([],false); // reset $query in order to prevent ambiguous ID

		$entities = static::internalFind($blobCols)->mergeWith($clnQry);

		$blobIds = [];
		foreach($entities as $entity) {
			foreach($blobCols as $c) {
				if(isset($entity->$c) && !in_array($entity->$c, $blobIds)) {

					$v = $entity->$c;

					if(is_array($v)) {
						$blobIds = array_merge($blobIds, $v);
					} else {
						$blobIds[] = $entity->$c;
					}

				}
			}
		}

		return $blobIds;		
	}

	private function validateTable(MappedTable $table) {		
		
		if(!$this->tableIsModified($table)) {
			// table record will not be validated and inserted if it has no modifications at all
			// todo: perhaps this should be configurable?
			return;
		}
		
		foreach ($table->getMappedColumns() as $colName => $column) {
			//Assume constants are correct, and this makes it unessecary to declare the property
			if(array_key_exists($colName, $table->getConstantValues())) {
				continue;
			}

			$this->validateColumn($column, $this->$colName);
		}
	}

  /**
   * Check's if the database conditions are met.
   *
   * @param Column $column
   * @param $value
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
				if(!$column->required && $value == null) {
					return;
				}

				if(!preg_match('/enum\((.*)\)/i', $column->dataType, $matches)) {
					$this->setValidationError($column->name, ErrorCode::GENERAL, "Enum column has no values specified in database");
					return;
				}

				$enumValues = str_getcsv(strtolower($matches[1]), ',' , "'");

				if(!in_array(strtolower($value), $enumValues)) {
					$this->setValidationError($column->name, ErrorCode::MALFORMED, "Invalid value for " . $column->dataType);
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

	public function toArray(array $properties = null): array
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
	protected function normalizeValue(string $propName, $value) {
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

		//build map for lookup
		$mapped = [];
		foreach($old as $prop) {
			$id = $prop->id();
			if($id) {
				$mapped[$id] = $prop;
			}
		}

		$this->$propName = [];
		if(isset($value)) {
			foreach ($value as $patch) {
				//check if we can find an existing model to patch.
				$temp = new $relation->propertyName($this);
				$temp->setValues($patch);
				$id = $temp->id();

				if (isset($mapped[$id])) {
					$mapped[$id]->setValues($patch);
					$this->{$propName}[] = $mapped[$id];
				} else {
					//create new model
					$this->{$propName}[] = $temp;
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
				if (!isset($patch) || $patch === false) {
					if (!array_key_exists($id, $old)) {
						go()->warn("Key $id does not exist in " . static::class . '->' . $propName);
					}
					continue;
				}
				if (is_array($old) && isset($old[$id])) {
					$this->$propName[$id] = $old[$id];
					if (is_array($patch)) { //may be given as bool
						$this->$propName[$id]->setValues($patch);
					}
				} else {

					$this->$propName[$id] = $this->internalNormalizeRelation($relation, $patch);

					//Why?
//					if (is_bool($patch)) {
						// if($relation->type == Relation::TYPE_MAP) {
						//Only change key to values when using booleans. Key can also be made up by the client.
						foreach ($this->mapKeyToValues($id, $relation) as $key => $value) {
							if(empty($this->$propName[$id]->$key)) {
								$this->$propName[$id]->$key = $value;
							}
						}
//					}
				}
			}
		}

		return $this->$propName;
	}

  /**
   * Takes a map key eg. ['1-2' => ['propa' => 'foo']]
   * and converts the key ('1-2') to a key value areay of properties.
   *
   * @param $id
   * @param Relation $relation
   * @return array
   * @throws Exception
   */
	private function mapKeyToValues($id, Relation $relation): array
	{
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
		$keys = $this->getPrimaryKey();
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
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		return $primaryTable->getPrimaryKey();
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
   * @param self $property
   * @return boolean
   * @throws Exception
   */
	public function equals(Property $property): bool
	{
		if(get_class($property) != get_class($this)) {
			return false;
		}
		
		if($property->isNew() || $this->isNew()) {
			return false;
		}
		
		$pk1 = $this->primaryKeyValues();
		$pk2 = $property->primaryKeyValues();
		
		$diff = array_diff($pk1, $pk2);
		
		return empty($diff);
	}
	
	/**
	 * Cuts all properties to make sure they are not longer than the database can store.
	 * Useful when importing or syncing
   * @throws Exception
	 */
	public function cutPropertiesToColumnLength() {
		
		$tables = self::getMapping()->getTables();
		foreach($tables as $table) {
			foreach ($table->getColumns() as $column) {
        if (($column->pdoType == PDO::PARAM_STR) && $column->length && isset($this->{$column->name})) {
					$this->{$column->name} = StringUtil::cutString($this->{$column->name}, $column->length, false, null);
				}
			}
		}
	}

  /**
   * Copy the property.
   *
   * The property will not be saved to the database.
   * The primary key values will not be copied.
   *
   * @return $this
   * @throws Exception
   */
	protected function internalCopy(): Property
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
			$col = static::getMapping()->getColumn($name);
			if(isset($p['access']) && (!$col || $col->autoIncrement == false)) {
				$copy->$name = $this->$name;
			}
		}

		return $copy;
	}
}
