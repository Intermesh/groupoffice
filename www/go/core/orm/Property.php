<?php

namespace go\core\orm;

use Exception;
use go\core\App;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\db\Statement;
use go\core\event\EventEmitterTrait;
use go\core\fs\Blob;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\validate\ValidationTrait;
use PDO;
use PDOException;
use ReflectionClass;
use ReflectionException;
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

	protected $readOnly = false;

  /**
   * Constructor
   *
   * @param boolean $isNew Indicates if this model is saved to the database.
   * @param string[] $fetchProperties The properties that were fetched by find. If empty then all properties are fetched
   * @throws Exception
   */
	public function __construct($isNew = true, $fetchProperties = [], $readOnly = false) {
		$this->isNew = $isNew;

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$this->fetchProperties = $fetchProperties;
		$this->readOnly = $readOnly;
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

	private $defaults = [];

  /**
   * Loads defaults from the database or casts the database value to the right type in PHP
   *
   * @param boolean $loadDefault
   * @throws Exception
   */
	private function initDatabaseColumns($loadDefault) {	
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
	private function getFetchedRelations() {

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
			$cls = $relation->entityName;

			$where = $this->buildRelationWhere($relation);

			switch($relation->type) {

				case Relation::TYPE_HAS_ONE:
					if($this->isNew() ) {
						$prop = null;
					} else
					{
						$stmt = $this->queryRelation($cls, $where, $relation->name, $this->readOnly);
						$prop = $stmt->fetch();
						$stmt->closeCursor();	
						if(!$prop) {
							$prop = null;
						}					
					}

					if(!$prop && $relation->autoCreate) {
						$prop = new $cls;
						$this->applyRelationKeys($relation, $prop);
					}
					$this->{$relation->name} = $prop;
				break;

				case Relation::TYPE_ARRAY:

					if($this->isNew() ) {
						$prop = [];
					} else
					{
						$stmt = $this->queryRelation($cls, $where, $relation->name, $this->readOnly);
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
						$stmt = $this->queryRelation($cls, $where, $relation->name, $this->readOnly);
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
					$stmt =$this->queryScalar($where, $relation);
					$scalar = $stmt->fetchAll();
					$stmt->closeCursor();
					$this->{$relation->name} = $scalar;
				break;
			}
		}
	}

  /**
   * @param $where
   * @param $relation
   * @return Statement|mixed
   * @throws Exception
   */
	private static function queryScalar($where, $relation) {
		$cacheKey = static::class.':'.$relation->name;

		if(!isset(self::$cachedRelations[$cacheKey])) {
			$key = self::getScalarKey($relation);
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

  /**
   * @param string $cls
   * @param $where
   * @param $relationName
   * @param $readOnly
   * @return Statement|mixed
   * @throws Exception
   */
	private static function queryRelation($cls, $where, $relationName, $readOnly) {

		$cacheKey = static::class.':'.$relationName;

		if(!isset(self::$cachedRelations[$cacheKey])) {

      /** @var Query $query */
      /** @var self $cls */
      $query = $cls::internalFind([], $readOnly);

			foreach($where as $field => $value) {
				$query->andWhere($field . '= :'.$field);
			}
			$stmt = $query->createStatement();
			self::$cachedRelations[$cacheKey] = $stmt;
		} else
		{
			$stmt = self::$cachedRelations[$cacheKey];			
		}

		foreach($where as $field => $value) {
			$stmt->bindValue(':'.$field, $value);
		}
		$stmt->execute();

		return $stmt;

	}

	private function buildRelationWhere(Relation $relation) {
		$where = [];
		foreach ($relation->keys as $from => $to) {
			$where[$to] = $this->$from;
		}
		return $where;
	}
	private static function getScalarKey(Relation $relation) {
		$table = Table::getInstance($relation->tableName, go()->getDbConnection());
		$diff = array_diff($table->getPrimaryKey(), $relation->keys);

		return array_shift($diff);
	}

	/**
	 * Build a key of the primary keys but omit the key from the releation because it's not needed as it's a property,
	 * 
	 */
	private function buildMapKey(Property $v, Relation $relation) {

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
   * Copies all properties so isModified() can detect changes.
   * @throws Exception
   */
	private function trackModifications() {
		//Watch db cols and relations
		$watch = array_keys($this->getMapping()->getProperties());
		
		//watch other props
		$watch = array_merge($watch, static::getPropNames());
		$watch = array_unique($watch);

		foreach ($watch as $propName) {
			$v = $this->$propName;
			$this->oldProps[$propName] = is_object($v) ? clone $v : $v;
		}
	}

	/**
	 * Override this function to initialize your model
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
	protected static function defineMapping() {
		return new Mapping(static::class);
	}

	private static $mapping;

  /**
   * Returns the mapping object that is defined in defineMapping()
   *
   * @return Mapping
   * @throws Exception
   */
	public final static function getMapping() {		
		$cls = static::class;
		if(isset(self::$mapping[$cls])) {
			return self::$mapping[$cls];
		}		
		$cacheKey = 'mapping-' . $cls;
		
		self::$mapping[$cls] = go()->getCache()->get($cacheKey);
		if(!self::$mapping[$cls]) {			
			self::$mapping[$cls] = static::defineMapping();			
			if(!static::fireEvent(self::EVENT_MAPPING, self::$mapping[$cls])) {
				throw new Exception("Mapping event failed!");
			}
			
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
		$keys = $this->primaryKeyValues();
		return count($keys) > 1 ? implode("-", array_values($keys)) : array_values($keys)[0];
	}

	static $c;

  /**
   * Get all API properties
   *
   * @return array|mixed
   * @throws Exception
   */
	public static function getApiProperties() {		
		$cacheKey = 'property-getApiProperties-' . static::class;
		
		if(isset(self::$c[$cacheKey])) {
			return self::$c[$cacheKey];
		}
		$props = go()->getCache()->get($cacheKey);
		
		if(!$props) {
		
			$props = parent::getApiProperties();

			//add dynamic relations		
			foreach(static::getMapping()->getProperties() as $propName => $type) {
				//do property_exists because otherwise it will add protected properties too.
				if(!isset($props[$propName])) {
					$props[$propName] = ['setter' => false, 'getter' => false, 'access' => self::PROP_PUBLIC, 'dynamic' => true];
				}
			}

			if(method_exists(static::class, 'getCustomFields')) {
				$props['customFields'] = ['setter' => true, 'getter' => true, 'access' => null];
			}
			
			go()->getCache()->set($cacheKey, $props);
		}
		self::$c[$cacheKey] = $props;
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
		if(static::getMapping()->hasProperty($name)) {
			if(!isset($this->dynamicProperties[$name])) {
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

	private function setPrimaryKey($name, $value) {
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
   * Get the properties to fetch when using the find() method.
   * These properties will be preloaded including related properties from other
   * tables. They will also be returned to the client.
   *
   * @return string[]
   * @throws ReflectionException
   */
	protected static function getDefaultFetchProperties() {
		
		$cacheKey = 'property-getDefaultFetchProperties-' . static::class;
		
		$props = go()->getCache()->get($cacheKey);
		
		if(!$props) {
			$props = array_filter(static::getReadableProperties(), function($propName) {
				return !in_array($propName, ['modified', 'oldValues', 'validationErrors']);
			});

			go()->getCache()->set($cacheKey, $props);
		}
		return $props;
	}	

	private static $findCache = [];

  /**
   * Find entities
   *
   * @return static|Query
   * @throws Exception
   */
	protected static function internalFind(array $fetchProperties = [], $readOnly = false) {

		$cacheKey = static::class . '-' . implode("-", $fetchProperties);

		if(isset(self::$findCache[$cacheKey])) {
			return clone self::$findCache[$cacheKey];
		}

		$tables = self::getMapping()->getTables();

		if(empty($tables)) {
			throw new Exception("No tables defined for ". static::class);
		}

		$mainTableName = array_keys($tables)[0];
		
		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$query = (new Query())
						->from($tables[$mainTableName]->getName(), $tables[$mainTableName]->getAlias())						
						->setModel(static::class, $fetchProperties, $readOnly);

		self::joinAdditionalTables($tables, $query);
		self::buildSelect($query, $fetchProperties, $readOnly);


		self::$findCache[$cacheKey] = $query;

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
	 * @return static
	 * @throws Exception
	 */
	protected static function internalFindById($id, array $properties = [], $readOnly = false) {
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$keys = $primaryTable->getPrimaryKey();
		
		$query = static::internalFind($properties, $readOnly);		
		
		//Used count check here because a customer managed to get negative ID's in the database.
		$ids = count($keys) == 1 ? [$id] : explode('-', $id);
		$keys = array_combine($keys, $ids);
		$query->where($keys);
		
		return $query->single();
	}

  /**
   * Get all property names
   *
   * @return array|mixed
   * @throws ReflectionException
   * @throws Exception
   */
	private static function getPropNames() {
		$cls = static::class;
		$cacheKey = $cls . '-getPropNames';

		$propNames = go()->getCache()->get($cacheKey);

		if (!$propNames) {
			$reflectionClass = new ReflectionClass($cls);
			$props = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);
			$propNames = [];
			foreach ($props as $prop) {
				if(!$prop->isStatic()) {
					$propNames[] = $prop->getName();
				}
			}
			
			//add dynamic relations		
			foreach(static::getMapping()->getProperties() as $name => $type) {
				if(!in_array($name, $propNames)) {
					$propNames[] = $name;
				}
			}

			go()->getCache()->set($cacheKey, $propNames);
		}

		return $propNames;
	}

  /**
   *
   * @return string[]
   * @throws Exception
   */
	protected static function getRequiredProperties() {	

		$cls = static::class;

		$cacheKey = $cls . '-required-props';

		$required = go()->getCache()->get($cacheKey);

		if($required != false) {
			return $required;
		}

		$props = static::getApiProperties();

		$required = static::getPrimaryKey();
		foreach($props as $name => $meta) {
			if($meta['access'] === self::PROP_PROTECTED) {
				$required[] = $name;
			}
		}

		$required = array_unique($required);

		go()->getCache()->set($cacheKey, $required);
		
		return $required;
	}

  /**
   * Evaluates the given fetchProperties and configures the query object to fetch them.
   *
   * @param Query $query
   * @param array $fetchProperties
   * @throws Exception
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
   * @param MappedTable $tables
   * @param Query $query
   *
   * @throws Exception
   * @todo implement fetch properties
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
   * @throws Exception
   */
	private static function joinTable($lastAlias, MappedTable $joinedTable, Query $query) {
		foreach ($joinedTable->getKeys() as $from => $to) {
			if (!isset($on)) {
				$on = "";
			} else {
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
	private function datesAreDifferent($a, $b) {
		if(!isset($a) && isset($b)) {
			return true;
		}

		if(!isset($b) && isset($a)) {
			return true;
		}

		return $a->format('U') != $b->format('U');
	}

	private function internalGetModified($properties = [], $forIsModified = false) {

		if(!is_array($properties)) {
			$properties = [$properties];
		}
		$modified = [];
		foreach ($this->oldProps as $key => $oldValue) {		
			if (!empty($properties) && !in_array($key, $properties)) {
				continue;
			}
			
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
				if($newValue instanceof \DateTime) {
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
	public function getOldValue($propName) {
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
	public function getOldValues() {
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
	protected function internalSave() {

		if($this->readOnly) {
			throw new Exception("Models are fetched read only");
		}
		
		if (!$this->validate()) {
			return false;
		}
		
		if(!$this->saveTables()) {
			return false;
		}
		
		$this->checkBlobs();

		if (!$this->saveRelatedProperties()) {
			return false;
		}

		return true;
	}

  /**
   * Saves all modified properties to the database.
   * @throws Exception
   */
	protected function saveTables() {
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
   * @return Column[]
   * @throws Exception
   */
	private static function getBlobColumns() {
		
		$refs = Blob::getReferences();
		$cols = [];
		foreach(static::getMapping()->getTables() as $table) {
			foreach($table->getMappedColumns() as $col) {
				foreach($refs as $r) {
					if($r['table'] == $table->getName() && $r['column'] == $col->name) {
						$cols[] = $col;
					}
				}
			}
		}
		
		return $cols;
	}

  /**
   * @throws Exception
   */
	private function checkBlobs() {
		$blobs = [];
		foreach($this->getBlobColumns() as $col) {
			if($this->isModified([$col->name])) {				
				$mod = array_values($this->getModified([$col->name]))[0];
				
				if(isset($mod[0])) {
					$blobs[] = $mod[0];
				}
				
				if(isset($mod[1])) {
					$blobs[] = $mod[1];
				}
			}
		}
		
		foreach($blobs as $id) {
			Blob::findById($id)->setStaleIfUnused();
		}
	}
	
	/**
	 * Sets some default values such as modifiedAt and modifiedBy
	 */
	private function setSaveProps(Table $table, $modifiedForTable) {
		
		if($table->getColumn("modifiedBy") && !isset($modifiedForTable["modifiedBy"])) {
			$this->modifiedBy = $modifiedForTable['modifiedBy'] = $this->getDefaultCreatedBy();
		}
		
		if($table->getColumn("modifiedAt") && !isset($modifiedForTable["modifiedAt"])) {
			$this->modifiedAt = $modifiedForTable['modifiedAt'] = new DateTime();
		}
		
		if(!$this->isNew()) {
			return $modifiedForTable;
		}
		
		if($table->getColumn("createdAt") && !isset($modifiedForTable["createdAt"])) {
			$this->createdAt = $modifiedForTable['createdAt'] = new DateTime();
		}
		
		if($table->getColumn("createdBy") && !isset($modifiedForTable["createdBy"])) {
			$this->createdBy = $modifiedForTable['createdBy']= $this->getDefaultCreatedBy();
		}
		
		return $modifiedForTable;
	}
	
	protected function getDefaultCreatedBy() {
		return !App::get()->getAuthState() || !App::get()->getAuthState()->getUserId() ? 1 : App::get()->getAuthState()->getUserId();
	}

  /**
   * Saves all property relations
   *
   * @return boolean
   * @throws Exception
   */
	private function saveRelatedProperties() {
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
	private function saveRelatedHasOne(Relation $relation) {
		
		//remove old model if it's replaced
		$modified = $this->getModified([$relation->name]);
		if (isset($modified[$relation->name][1])) {
			if (!$modified[$relation->name][1]->internalDelete((new Query)->where($modified[$relation->name][1]->primaryKeyValues()))) {
				$this->relatedValidationErrors = $modified[$relation->name][1]->getValidationErrors();
				return false;
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
	private function saveRelatedArray(Relation $relation) {
	
		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}

		//copy for overloaded properties because __get can't return by reference because we also return null sometimes.
		$models = $this->{$relation->name} ?? [];		
		$this->relatedValidationErrorIndex = 0;


		$this->removeRelated($relation, $models, $modified[$relation->name][1]);
		
		//set state to new for all models. Models could have been saved if save() is called multiple times.
		$models = array_map(function($model) {
			return $model->internalCopy();
		}, $models);		
		
		$this->{$relation->name} = [];
		foreach ($models as $newProp) {
			
			//Check for invalid input
			if(!($newProp instanceof Property)) {
				throw new Exception("Invalid value given for '". $relation->name ."'. Should be a GO\Orm\Property");
			}
			
			$this->applyRelationKeys($relation, $newProp);
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
   * @param self[] $models
   * @param self[] $oldModels
   * @return bool
   * @throws Exception
   */
	private function removeRelated(Relation $relation, $models, $oldModels) {

		$cls = $relation->entityName;
		$where = $this->buildRelationWhere($relation);
		$query = new Query();
		$query->where($where);

		if($relation->type == Relation::TYPE_MAP) {

			if(!isset($oldModels)) {
				return true;
			}
			
			$keepKeys = array_map(function($model){return isset($model) ? $model->id() : '-';}, $models);
			$removeKeys = new Criteria();
			$pk = $cls::getPrimaryKey();
			
			foreach($oldModels as $model) {
				$id = $model->id();
				if(in_array($id, $keepKeys)) {
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
		}

		return $cls::internalDelete($query);
	}

  /**
   * @param Relation $relation
   * @return bool
   * @throws Exception
   */
	private function saveRelatedScalar(Relation $relation) {
		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}
	
		$where = $this->buildRelationWhere($relation);

		$key = $this->getScalarKey($relation);
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
			$data = array_map(function($v) use($key, $where) {
				return array_merge($where, [$key => $v]);
			}, $insertIds);	

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
	private function saveRelatedMap(Relation $relation) {		
		
		$modified = $this->getModified([$relation->name]);
		if(empty($modified)) {
			return true;
		}

		//copy for overloaded properties because __get can't return by reference because we also return null sometimes.
		$models = $this->{$relation->name} ?? [];		
		$this->relatedValidationErrorIndex = 0;
		
		$this->removeRelated($relation, $models, $modified[$relation->name][1]);		
		
		$this->{$relation->name} = [];
		foreach ($models as $newProp) {
			
			if($newProp === null) {
				//deleted model
				continue;
			}
			
			//Check for invalid input
			if(!($newProp instanceof Property)) {
				throw new Exception("Invalid value given for '". $relation->name ."'. Should be a GO\Orm\Property");
			}
			
			$this->applyRelationKeys($relation, $newProp);
			if (!$newProp->internalSave()) {
				$this->relatedValidationErrors = $newProp->getValidationErrors();
				return false;
			}

			$this->savedPropertyRelations[] = $newProp;
			$this->relatedValidationErrorIndex++;
			
			$key = $this->buildMapKey($newProp, $relation);
			$this->{$relation->name}[$key] = $newProp;
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
	 * @param string $relation
	 * @param Property $property
	 */
	private function applyRelationKeys($relation, Property $property) {

		foreach ($relation->keys as $from => $to) {
			$property->$to = $this->$from;
		}
	}
	
	private function extractModifiedForTable(MappedTable $table, array $modified) {
		$modifiedForTable = [];

		$columns = $table->getColumns();
		foreach ($columns as $column) {
			if (isset($modified[$column->name])) {
				$modifiedForTable[$column->name] = $modified[$column->name][0];
			}
		}
		
		return $modifiedForTable;
	}
	
	private function recordIsNew(MappedTable $table) {		
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
	 * Saves properties to the mapped table
	 * 
	 * @param MappedTable $table
	 * @param array $modified
	 * @return boolean
	 * @throws Exception
	 */
	private function saveTable(MappedTable $table, array &$modified) {

		if($table->isUserTable && !go()->getAuthState()->isAuthenticated()) {
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

				if(empty($modifiedForTable)) {
					//if there's no primary key we might get here.
					return true;
				}

				//this if for cases when a second table extends the model but the key is not part of the properties
				//For example Password extends User but the ket "userId" of password is not part of the properties
				foreach($table->getKeys() as $from => $to) {
					$modifiedForTable[$to] = $this->{$from};
				}

				if($table->isUserTable) {
					$modifiedForTable["userId"] = go()->getUserId();
				}
				
				$stmt = App::get()->getDbConnection()->insert($table->getName(), $modifiedForTable);
				if (!$stmt->execute()) {
					throw new Exception("Could not execute insert query");
				}

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
				
				$stmt = App::get()->getDbConnection()->update($table->getName(), $modifiedForTable, $keys);
				if (!$stmt->execute()) {
					throw new Exception("Could not execute update query");
				}				
//				if(!$stmt->rowCount()) {			
//					
//					throw new \Exception("No affected rows for update!");
//				}				
			}
		} catch (PDOException $e) {
			ErrorHandler::logException($e);
			$uniqueKey = \go\core\db\Utils::isUniqueKeyException($e);
			
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
		}

		return true;
	}

	/**
	 * Get's the auto increment ID after an insert query and sets the property in this model
	 * 
	 * @param MappedTable $table
	 * @param type $modified
	 * @throws Exception
	 */
	private function handleAutoIncrement(MappedTable $table, &$modified) {
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
	protected function commit() {
		
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
	protected function rollBack() {

		foreach ($this->savedPropertyRelations as $property) {
			$property->rollBack();
		}

		$this->savedPropertyRelations = [];

		foreach ($this->getMapping()->getTables() as $table) {
			$this->rollBackAutoIncrement($table);
		}

		return true;
	}
	
	// private $isDeleted = false;
	
	// /**
	//  * Check if this property was just deleted.
	//  * 
	//  * @var bool
	//  */
	// public function isDeleted() {
	// 	return $this->isDeleted;
	// }

  /**
   * Parses ID into query
   *
   * eg. "1-1" into ['col1=>1, 'col2'=>1];
   *
   * @param string $id
   * @return array
   * @throws InvalidArguments
   */
	public static function parseId($id) {
		$primaryTable = static::getMapping()->getPrimaryTable();
		$pk = $primaryTable->getPrimaryKey();

		$props = [];
		$keys = explode('-', $id);	
		
		if(count($keys)  != count($pk)) {
			throw new InvalidArguments("Invalid ID given for " . static::class.' : '.$id);
		}
		foreach ($pk as $key) {
			$props[$key] = array_shift($keys);
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
   * @return boolean
   * @throws Exception
   */
	protected static function internalDelete(Query $query) {

		$primaryTable = static::getMapping()->getPrimaryTable();

		$blobIds = static::getBlobsToCheckAfterDelete($query);
		
		$stmt = go()->getDbConnection()->delete($primaryTable->getName(), $query);
		if(!$stmt->execute()) {			
			return false;
		}	

		if(count($blobIds)) {			
			$blobs = Blob::find()->where('id', '=', $blobIds);
			foreach($blobs as $blob) {
				$blob->setStaleIfUnused();
			}
		}
		return true;
	}

	private static function getBlobsToCheckAfterDelete(Query $query) {
		
		$blobCols = static::getBlobColumns();
		if(!count($blobCols)) {
			return [];
		}

		$blobCols = array_map(function($col) {return $col->name;}, $blobCols);

		$entities = static::internalFind($blobCols)->mergeWith($query);

		$blobIds = [];
		foreach($entities as $entity) {
			foreach($blobCols as $c) {
				if(isset($entity->$c) && !in_array($entity->$c, $blobIds)) {
					$blobIds[] = $entity->$c;
				}
			}
		}

		return $blobIds;		
	}

	private function validateTable(MappedTable $table) {		
		
		if(!$this->tableIsModified($table)) {
			// table record will not be validated and inserted if it has no modifications at all
			// todo: perhaps this should be configurable?
			return true;
		}
		
		foreach ($table->getMappedColumns() as $colName => $column) {
			//Assume constants are correct, and this makes it unessecary to declare the property
			if(array_key_exists($colName, $table->getConstantValues())) {
				continue;
			}

			if (!$this->validateColumn($column, $this->$colName)) {
				//only one error per column
				continue;
			}			
		}
	}
	
	
	private function validateColumn(Column $column, $value) {
		if (!$this->validateRequired($column)) {
			return false;
		}
		
		//Null is allowed because we checked this above.
		if(empty($value)) {
			return true;
		}

		switch ($column->dbType) {
			case 'date':
			case 'datetime':
				return $value instanceof DateTime || $value instanceof DateTimeImmutable;
				
			default:				
				return $this->validateColumnString($column, $value);		
		}
	}
	
	private function validateColumnString(Column $column, $value) {
		if(!is_scalar($value) && (!is_object($value) || !method_exists($value, '__toString'))) {
			$this->setValidationError($column->name, ErrorCode::MALFORMED, "Non scalar value given. Type: ". gettype($value));
			return false;
		} 

		if (!empty($column->length)){				
			if(StringUtil::length($value) > $column->length) {
				$this->setValidationError($column->name, ErrorCode::MALFORMED, 'Length can\'t be greater than ' . $column->length);
				return false;
			}
		}
		return true;		
	}
	
	private function tableIsModified(MappedTable $table) {
		return $this->isModified(array_keys($table->getColumns()));
	}

	private function validateRequired(Column $column) {

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
	 */
	protected function internalValidate() {
		foreach ($this->getMapping()->getTables() as $table) {
			$this->validateTable($table);
		}
	}

	public function toArray($properties = []) {
		if (empty($properties)) {
			$properties = $this->fetchProperties;
		}

		return parent::toArray($properties);
	}


	// protected function propToArray($name) {

	// 	$value = $this->getValue($name);

	// 	if(is_array($value) && empty($value)) {
	// 		$relation = $this->getMapping()->getRelation($name);

	// 		if($relation && $relation->type == Relation::TYPE_MAP) {
	// 			$value = new ArrayObject();
	// 			$value->serializeJsonAsObject = true;
	// 		}
	// 	}		
	// 	return $this->convertValue($value);
	// }

	/**
	 * Normalizes API input for this model.
	 * 
	 * @param string $propName
	 * @param mixed $value
	 * @return mixed
	 */
	protected function normalizeValue($propName, $value) {
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
				break;

				// case Relation::TYPE_ARRAY:
				// 	foreach($value as $key => $item) {
				// 		$value[$key] = $this->internalNormalizeRelation($relation, $item);
				// 	}
				// 	return $value;
				// break;

				case Relation::TYPE_ARRAY:
				case Relation::TYPE_MAP:
					return $this->patch($relation, $propName, $value);
				break;

				case Relation::TYPE_SCALAR:
					return $value;
				break;
			}
		}

		$column = static::getMapping()->getColumn($propName);
		if ($column && !static::isProtectedProperty($column->name)) {
			return $column->normalizeInput($value);
		}

		return $value;
	}
//
	protected function patch(Relation $relation, $propName, $value) {
		$old = $this->$propName;
		$this->$propName = [];
		foreach($value as $id => $patch) {
			if(!isset($patch) || $patch === false) {
				if(!array_key_exists($id, $old)) {
					go()->warn("Key $id does not exist in ". static::class .'->'.$propName);
				}				
				continue;
			}
			if(is_array($old) && isset($old[$id])) {
				$this->$propName[$id] = $old[$id];
				if(is_array($patch)) { //may be given as bool
					$this->$propName[$id]->setValues($patch);
				}
			} else {

				$this->$propName[$id] = $this->internalNormalizeRelation($relation, $patch);	

				if(is_bool($patch)) {
				// if($relation->type == Relation::TYPE_MAP) {
					//Only change key to values when using booleans. Key can also be made up by the client.
					foreach($this->mapKeyToValues($id, $relation) as $key => $value) {
						$this->$propName[$id]->$key = $value;
					}				
				}
			}
			
		}

		return $this->$propName;
	}


	private function mapKeyToValues($id, Relation $relation) {


		$values = explode("-", $id);

		$cls = $relation->entityName;

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

	private function internalNormalizeRelation(Relation $relation, $value) {
		$cls = $relation->entityName;
		if ($value instanceof $cls) {
			return $value;
		}

		if(is_bool($value)) {
			$value = $value ? [] : null;
		}

		if (is_array($value)) {
			$o = new $cls;			
			$o->setValues($value);

			return $o;
		} else if (is_null($value)) {
			return null;
		} else {
			throw new Exception("Invalid value given to relation '" . $this->name . "'. Should be an array or an object of type '" . $relation->entityName . "': " . var_export($value, true));
		}
	}

	/**
	 * Returns true is the model is new and not saved to the database yet.
	 * 
	 * @return boolean
	 */
	public function isNew() {
		return $this->isNew;
	}
	
	/**
	 * Get the primary key value
	 * 
	 * @return array eg ['id' => 1]
	 */
	public function primaryKeyValues() {
		
		$keys = $this->getPrimaryKey();
		$v = [];
		foreach($keys as $key) {			
			$v[$key] = $this->$key;			
		}
		
		return $v;
	}
	
	/**
	 * Get the primary key column names.
	 * 
	 * If you need the values {@see primaryKeyValues()}
	 * 
	 * @param boolean $withTableAlias 
	 * @return string[]
	 */
	public static function getPrimaryKey($withTableAlias = false) {
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$keys = $primaryTable->getPrimaryKey();
		if(!$withTableAlias) {
			return $keys;
		}
		$keysWithAlias = [];
		foreach($keys as $key) {
			$keysWithAlias[] = $primaryTable->getAlias() . '.' . $key;
		}
		return $keysWithAlias;
	}
	
	/**
	 * Checks if the given property or entity is equal to this
	 * 
	 * @param self $property
	 * @return boolean
	 */
	public function equals($property) {
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
	 */
	public function cutPropertiesToColumnLength() {
		
		$tables = self::getMapping()->getTables();
		foreach($tables as $table) {
			foreach ($table->getColumns() as $column) {
				if ($column->pdoType == PDO::PARAM_STR && $column->length) {
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
	 * @return \static
	 */
	protected function internalCopy() {
		$copy = new static;

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
