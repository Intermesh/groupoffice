<?php

namespace go\core\orm;

use Exception;
use go\core\App;
use go\core\data\Model;
use go\core\db\Column;
use go\core\db\Query;
use go\core\event\EventEmitterTrait;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\validate\ValidationTrait;
use PDO;
use PDOException;
use ReflectionClass;

/**
 * Property model
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
	 * The event listeners is called with the {@see Mapping} object.
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
	 * Keeps record of all the saved properties so we can commit or rollback after save.
	 * @var Property[]
	 */
	private $savedPropertyRelations = [];
	
	
	/**
	 * Holds primary keys per table alias. Used to track new state of records.
	 * 
	 * @example
	 * ```
	 * ['tableAlias' => ['id' => 1]]
	 * ```
	 * @var array
	 */
	private $primaryKeys = []; 

	/**
	 * Constructor
	 * 
	 * @param boolean $isNew Indicates if this model is saved to the database.
	 * @param string[] $fetchProperties The properties that were fetched by find. If empty then all properties are fetched
	 */
	public function __construct($isNew = true, $fetchProperties = []) {
		$this->isNew = $isNew;

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		$this->fetchProperties = $fetchProperties;
		
		if($this->isNew) {
			//make sure default values of the database are tracked as modifications. 
			//Otherwise the save function may think nothing needs to be inserted.
			$this->trackModifications();
		}
		
		$this->initDatabaseColumns($this->isNew);
		
		if (!$this->isNew) {			
			$this->initRelations();
			$this->trackModifications();
		}

		$this->init();
	}


	/**
	 * Loads defaults from the database or casts the database value to the right type in PHP
	 * 
	 * @param boolean $loadDefault
	 */
	private function initDatabaseColumns($loadDefault) {
		foreach ($this->getMapping()->getTables() as $table) {
			foreach ($table->getColumns() as $colName => $column) {
				if (in_array($colName, $this->fetchProperties)) {
					$this->$colName = $loadDefault ? $column->castFromDb($column->default) : $column->castFromDb($this->$colName);
				}
			}
			foreach($table->getConstantValues() as $colName => $value) {
				if (in_array($colName, $this->fetchProperties)) {
					$this->$colName  = $value;
				}
			}
		}
	}

	/**
	 * Returns all relations that were requested in "fetchProperties".
	 * 
	 * @return Relation[]
	 */
	private function getFetchedRelations() {

		$fetchedRelations = [];

		$relations = $this->getMapping()->getRelations();
		foreach ($relations as $relation) {
			if (in_array($relation->name, $this->fetchProperties)) {
				$fetchedRelations[] = $relation;
			}
		}
		
		return $fetchedRelations;
	}

	/**
	 * Fetches the related properties when requested
	 */
	private function initRelations() {		
		foreach ($this->getFetchedRelations() as $relation) {
			$cls = $relation->entityName;

			$where = [];
			foreach ($relation->keys as $from => $to) {
				$where[$to] = $this->$from;
			}			

			if ($relation->many) {
				$props = $cls::internalFind()->andWhere($where)->all();
				$this->{$relation->name} = $props;
			} else {
				$prop = $cls::internalFind()->andWhere($where)->single();				
				$this->{$relation->name} = $prop ? $prop : null;
			}
		}
	}
	
	/**
	 * Copies all properties so isModified() can detect changes.
	 */
	private function trackModifications() {
		foreach ($this->getMapping()->getTables() as $table) {
			foreach ($table->getColumns() as $colName => $column) {
				if (in_array($colName, static::getPropNames())) {
					$this->oldProps[$colName] = $this->$colName;
				}
			}
		}
		foreach ($this->getFetchedRelations() as $relation) {
			$this->oldProps[$relation->name] = $this->{$relation->name};
		}
	}

	/**
	 * Override this function to initialize your model
	 */
	protected function init() {
		
	}

	/**
	 * List of tables this entiy uses
	 * 
	 * All tables must have identical primary keys.
	 * eg 
	 * 
	 * ````
	 * 	protected static function defineMapping() {
	 * 		return parent::defineMapping()
	 * 						->addTable('test_a', 'a')
	 * 						->addProperty('sumOfTableBIds', "SUM(b.id)", (new Query())->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']))
	 * 						->addRelation('hasMany', AHasMany::class, ['id' => 'aId'], true)
	 * 						->addRelation('hasOne', AHasOne::class, ['id' => 'aId'], false);
	 * 	}
	 * ````
	 * 
	 * @return Mapping
	 */
	protected static function defineMapping() {
		return new Mapping(static::class);
	}

	/**
	 * Returns the mapping object that is defined in defineMapping()
	 * 
	 * @return Mapping;
	 */
	public final static function getMapping() {

		$cls = static::class;
		
		$cacheKey = 'mapping-' . str_replace('\\', '-', $cls);
		
		$mapping = GO()->getCache()->get($cacheKey);
		if(!$mapping) {			
			$mapping = static::defineMapping();			
			if(!static::fireEvent(self::EVENT_MAPPING, $mapping)) {
				throw new \Exception("Mapping event failed!");
			}
			
			GO()->getCache()->set($cacheKey, $mapping);
		}

		return $mapping;
	}
	
	protected static function getReadableProperties() {
		$props = parent::getReadableProperties();
		
		//add dynamic relations		
		foreach(static::getMapping()->getProperties() as $propName => $type) {
			
			//do property_exists because otherwise it will add protected properties too.
			if(!property_exists(static::class, $propName) && !in_array($propName, $props)) {
				$props[] = $propName;
			}
		}		
		return $props;
	}
	
	private $dynamicProperties = [];
	
	
	//can't return by reference because of [2018-02-09T15:40:46+00:00] ErrorException in /media/sf_Projects/groupoffice-6.3/www/go/core/orm/Property.php at line 252: Only variable references should be returned by reference

	public function __get($name) {
		
		$getter = 'get'.$name;

		if(method_exists($this,$getter)){
			return $this->$getter();
		}		
		
		if(static::getMapping()->hasProperty($name)) {
			return isset($this->dynamicProperties[$name]) ? $this->dynamicProperties[$name] : null;
		}		
		throw new Exception("Can't get not existing property '$name' in '".static::class."'");			
		
	}
	
	public function __isset($name) {
		
		
		$getter = 'get'.$name;

		if(method_exists($this,$getter)){
			return $this->$getter() != null;
		}
		
		if(static::getMapping()->hasProperty($name)) {
			return isset($this->dynamicProperties[$name]);
		}
		return parent::__isset($name);
	}
	
	public function __set($name, $value) {		
		if($this->setPrimaryKey($name, $value)) {
			return ;
		}
		
		$setter = 'set'.$name;
			
		if(method_exists($this,$setter)){
			return $this->$setter($value);
		}
		
		//if(static::getMapping()->getRelation($name)) {		
		//Had to change to hasPropery to make it work for dynamically added tables.
		if(static::getMapping()->hasProperty($name)) {			
			$this->dynamicProperties[$name] = $value;
		} else
		{
			return parent::__set($name, $value);
		}
	}
	
	private function setPrimaryKey($name, $value) {
		if(strpos($name, ".") === false) {
			return false;
		}
		//this is a primary key value. See buildSelect()
		$parts = explode(".", $name);
		if(!isset($this->primaryKeys[$parts[0]])) {
			$this->primaryKeys[$parts[0]] = [];
		}
		$this->primaryKeys[$parts[0]][$parts[1]] = $value;

		return true;
	}

	protected static function getDefaultFetchProperties() {
		return array_filter(static::getReadableProperties(), function($propName) {
			return !in_array($propName, ['modified', 'oldValues', 'validationErrors']);
		});
	}	

	/**
	 * Find entities
	 * 
	 * @return static|Query
	 */
	protected static function internalFind(array $fetchProperties = []) {
		$tables = self::getMapping()->getTables();

		$mainTableName = array_keys($tables)[0];

		$query = (new Query())
						->from($tables[$mainTableName]->getName(), $tables[$mainTableName]->getAlias())
						->fetchMode(PDO::FETCH_CLASS, static::class, [false, $fetchProperties]);

		if (empty($fetchProperties)) {
			$fetchProperties = static::getDefaultFetchProperties();
		}

		self::joinAdditionalTables($tables, $query);
		self::buildSelect($query, $fetchProperties);

		return $query;
	}

	protected static $propNames = [];

	private static function getPropNames() {
		$cls = static::class;
		if (!isset(static::$propNames[$cls])) {
			$reflectionClass = new ReflectionClass($cls);
			$props = $reflectionClass->getProperties();
			static::$propNames[$cls] = [];
			foreach ($props as $prop) {
				static::$propNames[$cls][] = $prop->getName();
			}
			
			//add dynamic relations		
			foreach(static::getMapping()->getProperties() as $name => $type) {
				if(!in_array($name, static::$propNames[$cls])) {
					static::$propNames[$cls][] = $name;
				}
			}
		}

		return static::$propNames[$cls];
	}

	/**
	 * Evaluates the given fetchProperties and configures the query object to fetch them.
	 * 
	 * @param Query $query
	 * @param array $fetchProperties
	 */
	private static function buildSelect(Query $query, array $fetchProperties) {

		foreach (self::getMapping()->getTables() as $table) {
			foreach($table->getMappedColumns() as $column) {				
				$query->select($table->getAlias() . "." . $column->name, true);				
			}
			
			//also select primary key values separately to check if tables were new when saving. They are stored in $this->primaryKeys when they go through the __set function.
			foreach($table->getPrimaryKey() as $pk) {				
				//$query->select("alias.id AS `alias.userId`");
				$query->select($table->getAlias() . "." . $pk . " AS `" . $table->getAlias() . "." . $pk ."`", true);				
			}			
		}

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
	 * @todo implement fetch properties
	 */
	private static function joinAdditionalTables(array $tables, Query $query) {
		$first = array_shift($tables);

		foreach ($tables as $table) {
			foreach ($table->getKeys() as $from => $to) {
				if (!isset($on)) {
					$on = "";
				} else {
					$on .= " AND ";
				}
				
				//find the alias. The default table in the column is not a mapped table
				$fromTableName = static::getMapping()->getColumn($from)->getTable()->getName();				
				//So we fetch the mapped table to get the alias
				$fromAlias = static::getMapping()->getTable($fromTableName)->getAlias();
				$on .= $fromAlias . "." . $from . ' = ';
				$on .= $table->getAlias() . "." . $to;
			}
			
			if(!empty($table->getConstantValues())) {
				$on = \go\core\db\Criteria::normalize($on)->andWhere($table->getConstantValues());
			}
			$query->join($table->getName(), $table->getAlias(), $on, "LEFT");
			unset($on);
		}
	}

	/**
	 * Get all the modified properties with their new and old values.
	 * 
	 * Only database columns and relations are tracked. Not the getters and setters.
	 * 
	 * @return array [newval, oldval]
	 */
	public function getModified($properties = []) {

		$modified = [];
		foreach ($this->oldProps as $key => $oldValue) {		
			if (!empty($properties) && !in_array($key, $properties)) {
				continue;
			}
			
			$newValue = $this->{$key};
			
			if($newValue instanceof Property) {
				if($newValue->isModified()) {
					$modified[$key] = [$newValue, null];
				}
			} else
			{
				if ($newValue !== $oldValue) {
					$modified[$key] = [$newValue, $oldValue];
				}
			}
			
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
		
		if(!is_array($properties)) {
			$properties = [$properties];
		}

		foreach ($this->oldProps as $key => $oldValue) {
			if (!empty($properties) && !in_array($key, $properties)) {
				continue;
			}
			if ($this->{$key} !== $oldValue) {
				return true;
			}
		}
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
			throw new \Exception("Property " . $propName . " does not exist");
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
	 */
	protected function internalSave() {
		
		if (!$this->validate()) {
			return false;
		}

		if($this->isNew() || $this->isModified()){
			$this->setSaveProps();		
		}
		
		$modified = $this->getModified();
		
		foreach ($this->getMapping()->getTables() as $table) {			
			if (!$this->saveTable($table, $modified)) {				
				return false;
			}
		}

		if (!$this->saveRelatedProperties()) {
			return false;
		}

		return true;
	}
	
	private function setSaveProps() {
		if(property_exists($this, "modifiedBy") && !$this->isModified(["modifiedBy"])) {
			$this->modifiedBy = $this->getCreatedBy();
		}
		
		if(property_exists($this, "modifiedAt") && !$this->isModified(["modifiedAt"])) {
			$this->modifiedAt = new DateTime();
		}
		
		if(!$this->isNew()) {
			return;
		}
		
		if(property_exists($this, "createdAt") && !$this->isModified(["createdAt"])) {
			$this->createdAt = new DateTime();
		}
		
		if(property_exists($this, "createdBy") && !$this->isModified(["createdBy"])) {
			$this->createdBy = $this->getCreatedBy();
		}
	}
	
	protected function getCreatedBy() {
		return !App::get()->getAuthState() || !App::get()->getAuthState()->getUserId() ? 1 : App::get()->getAuthState()->getUserId();
	}

	/**
	 * Saves all property relations
	 * 
	 * @return boolean
	 */
	private function saveRelatedProperties() {
		foreach ($this->getFetchedRelations() as $relation) {
			if ($relation->many) {
				if (!$this->saveRelatedHasMany($relation)) {
					$this->setValidationError($relation->name, ErrorCode::RELATIONAL);
					return false;
				}
			} else {
				if (!$this->saveRelatedHasOne($relation)) {
					$this->setValidationError($relation->name, ErrorCode::RELATIONAL);
					return false;
				}
			}

		}
		return true;
	}

	private function saveRelatedHasOne(Relation $relation) {
		
		//remove old model if it's replaced
		$modified = $this->getModified([$relation->name]);
		if (isset($modified[$relation->name][1])) {
			if (!$modified[$relation->name][1]->internalDelete()) {
				return false;
			}
		}

		if (isset($this->{$relation->name})) {			
			$this->applyRelationKeys($relation, $this->{$relation->name});
			if (!$this->{$relation->name}->internalSave()) {
				return false;
			}

			$this->savedPropertyRelations[] = $this->{$relation->name};
		}

		return true;
	}

	private function saveRelatedHasMany(Relation $relation) {
		
		//copy for overloaded properties because __get can't return by reference because we also return null sometimes.
		$models = $this->{$relation->name};
		
		//remove old model if it's replaced
		$modified = $this->getModified([$relation->name]);
		if (isset($modified[$relation->name][1])) {			
			foreach ($modified[$relation->name][1] as $oldProp) {
				
				//if not in current value then delete it.
				if (!in_array($oldProp, $models) && !$oldProp->internalDelete()) {
					return false;
				}
			}
		}

		
		if(isset($models)) {
			foreach ($models as &$newProp) {
				$this->applyRelationKeys($relation, $newProp);
				if (!$newProp->internalSave()) {
					return false;
				}

				$this->savedPropertyRelations[] = $newProp;
			}
			$this->{$relation->name} = $models;
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
		foreach($table->getPrimaryKey() as $pk) {
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
		
		$modifiedForTable = $this->extractModifiedForTable($table, $modified);

		if (empty($modifiedForTable)) {
			return true;
		}		
		
		try {
			if ($this->recordIsNew($table)) {				
				//this if for cases when a second table extends the model but the key is not part of the properties
				//For example Password extends User but the ket "userId" of password is not part of the properties
				foreach($table->getKeys() as $from => $to) {
					$modifiedForTable[$to] = $this->{$from};
				}
				
				foreach($table->getConstantValues() as $colName => $value) {
					$modifiedForTable[$colName] = $value;
				}
				
				if (!App::get()->getDbConnection()->insert($table->getName(), $modifiedForTable)->execute()) {
					throw new \Exception("Could not execute insert query");
				}

				$this->handleAutoIncrement($table, $modified);
				
				//update primary key data for new state
				$this->primaryKeys[$table->getAlias()] = [];
				foreach($table->getKeys() as $from => $to) {
					$this->primaryKeys[$table->getAlias()][$to] = $this->$from;
				}
			} else {	
				if (empty($modifiedForTable)) {
					return true;
				}
				$stmt = App::get()->getDbConnection()->update($table->getName(), $modifiedForTable, $this->primaryKeys[$table->getAlias()]);
				if (!$stmt->execute()) {
					throw new \Exception("Could not execute update query");
				}				
//				if(!$stmt->rowCount()) {			
//					
//					throw new \Exception("No affected rows for update!");
//				}				
			}
		} catch (PDOException $e) {
			
//			var_dump(App::get()->getDebugger()->getEntries());

			//Unique index error = 23000
			if ($e->getCode() == 23000) {
				
				$msg = $e->getMessage();
				App::get()->debug($msg);				
				
				$key = 'id';
				if(preg_match("/key '(.*)'/", $msg, $matches)) {
					$key = $matches[1];
				}
				
				$this->setValidationError($key, ErrorCode::UNIQUE);
				return false;
			} else {
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
	
	private $isDeleted = false;
	
	public function isDeleted() {
		return $this->isDeleted;
	}

	/**
	 * Delete this model
	 * 
	 * @return boolean
	 */
	protected function internalDelete() {
		$tables = $this->getMapping()->getTables();
		$primaryTable = array_shift($tables);
		$pk = [];
		foreach ($primaryTable->getPrimaryKey() as $key) {
			$pk[$key] = $this->{$key};
		}
		if(!App::get()->getDbConnection()->delete($primaryTable->getName(), $pk)->execute()) {			
			return false;
		}
		
		$this->isDeleted = true;
		
		return true;
	}

	private function validateTable(MappedTable $table) {		
		
		if(!$this->tableIsModified($table)) {
			// table record will not be validated and inserted if it has no modifications at all
			// todo: perhaps this should be configurable?
			return true;
		}
		
		foreach ($table->getColumns() as $colName => $column) {
			//Assume constants are correct, and this makes it unessecary to declare the property
			if(array_key_exists($colName, $table->getConstantValues())) {
				continue;
			}

			if (!$this->validateRequired($column)) {
				//only one error per column
				continue;
			}

			if (!empty($column->length) && !empty($this->$colName) && StringUtil::length($this->$colName) > $column->length) {
				$this->setValidationError($colName, ErrorCode::MALFORMED, 'Length can\'t be greater than ' . $column->length);
			}
		}
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

	/**
	 * Set public properties with key value array.
	 * 
	 * This function should also normalize input when you extend this class.
	 * 
	 * For example dates in ISO format should be converted into DateTime objects
	 * and related models should be converted to an instance of their class.
	 * 
	 *
	 * @Example
	 * ```````````````````````````````````````````````````````````````````````````
	 * $model = User::findByIds([1]);
	 * $model->setValues(['username' => 'admin']);
	 * $model->save();
	 * ```````````````````````````````````````````````````````````````````````````
	 *
	 * 
	 * @param array $values  ["propNamne" => "value"]
	 * @return \static
	 */
	public function setValues(array $values) {
		foreach ($values as $propName => &$value) {
			$value = $this->normalizeValue($propName, $value);
		}

		return parent::setValues($values);
	}

	/**
	 * Turns array values into relation models and ISO date strings into DateTime objects
	 * 
	 * @param type $propName
	 * @param type $value
	 * @return type
	 */
	private function normalizeValue($propName, $value) {
		$relation = static::getMapping()->getRelation($propName);
		if ($relation) {
			
			if(!$relation->many && isset($value) && isset($this->$propName)) {				
				//if a has one relation exists then apply the new values to the existing property instead of creating a new one.
				return $this->$propName->setValues($value);
			} else {
				return $relation->normalizeInput($value);
			}
			
		}

		$column = static::getMapping()->getColumn($propName);
		if ($column) {
			return $column->normalizeInput($value);
		}

		return $value;
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
	public function id() {
		
		if($this->isNew()) {
			return null;
		}
		
		$tables = static::getMapping()->getTables();
		$primaryTable = array_shift($tables);
		
		return $this->primaryKeys[$primaryTable->getAlias()];
	}
	
	/**
	 * Checks if the given record is equal to this record
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
		
		$pk1 = $this->id();
		$pk2 = $property->id();
		
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

}
