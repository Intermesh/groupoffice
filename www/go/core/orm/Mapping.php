<?php

namespace go\core\orm;

use Exception;
use go\core\db\Column;
use go\core\db\Query;
use go\core\db\Table;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Sabre\DAV\Xml\Element\Prop;

/**
 * Mapping object 
 * 
 * It maps tables to objects properties.
 * The mapping object is cached. So when you make changes you need to run /install/upgrade.php
 */
class Mapping {


	/**
	 * Dynamic relations or tables can be added by the {@see Property::EVENT_MAPPING} event.
	 * We use this bool to keep track of dynamic relations so we can report an error on undefined relation properties in
	 * {@see Property::__get()}
	 * @var bool
	 */
	public $dynamic = false;
	
	/**
	 * Property class name this mapping is for
	 * 
	 * @var class-string<Property>
	 */
	private $for;

	/**
	 *
	 * @var MappedTable[] 
	 */
	private $tables = [];	


	private $columns = [];
	
	private $relations = [];

	/**
	 * @var Query
	 */
	private $query;

	/**
	 * Mapping has a table per user. 
	 * 
	 * @see addUserTable();
	 * 
	 * @bool
	 */
	public $hasUserTable = false;
	
	/**
	 * Constructor
	 * 
	 * @param class-string<Property> $for Property class name this mapping is for
	 */
	public function __construct(string $for) {
		$this->for = $for;
	}

	/**
	 * Adds a table to the model
	 *
	 * @param string $name The table name
	 * @param string|null $alias The table alias to use in the queries
	 * @param array|null $keys [thiscol => targetcol] If null then it's assumed the key name is identical in
	 *   this and the last added table. eg. ['id' => 'id']
	 * @param array|null $columns Leave this null if you want to automatically build
	 *   this based on the properties the model has. If you're extending a model
	 *   then this is not possible and you must supply all columns you do want to
	 *   make available in the model.
	 * @param array $constantValues If the table that is joined needs to have
	 *   constant values. For example the keys are ['folderId' => 'folderId'] but
	 *   the joined table always needs to have a value
	 *   ['type' => "foo"] then you can set it with this parameter.
	 * @return $this
	 */
	public function addTable(string $name, string $alias = null, array $keys = null, array $columns = null, array $constantValues = []): Mapping
	{
		if(!$alias) {
			$alias = $name;
		}
		$this->internalAddTable($name, $alias, $keys, $columns, $constantValues);
		return $this;
	}


	private function internalAddTable(string $name, string $alias, array $keys = null, array $columns = null, array $constantValues = []) {
		$this->tables[$name] = new MappedTable($name, $alias, $keys, empty($columns) ? $this->buildColumns() : $columns, $constantValues);
		$this->tables[$name]->dynamic = $this->dynamic;
		foreach($this->tables[$name]->getMappedColumns() as $col) {
			$col->dynamic = $this->dynamic;
			if(!isset($this->columns[$col->name] )) { //if two identical columns are mapped the first one will be used. Can happen with "id" when A extends B.
				$this->columns[$col->name] = $col;
			}
		}

		return $this->tables[$name];
	}

  /**
   * Add a user table to the model
   *
   * A user table will be joined with AND userId = <CURRENTUSER>
   *
   * A user table must have an userId (int) and modSeq (int) column.
   *
   * The modSeq value will be used in the Entity::getState().
   *
   * @param string $name
   * @param string $alias
   * @param string[] $keys
   * @param string[] $columns
   * @param string[] $constantValues
   * @return Mapping
   */
	public function addUserTable(string $name, string $alias, array $keys = null, array $columns = null, array $constantValues = []): Mapping
	{
		$table = $this->internalAddTable($name, $alias, $keys, $columns, $constantValues);
		$table->isUserTable = true;

		$this->hasUserTable = true;

		if(!Table::getInstance($name)->getColumn('modSeq')) {
			throw new LogicException("The table ".$name." must have a 'modSeq' column of type INT");
		}
		
		if(!Table::getInstance($name)->getColumn('userId')) {
			throw new LogicException("The table ".$name." must have a 'userId' column of type INT");
		}
		
		return $this;
	}

  /**
   * @return array
   */
	private function buildColumns(): array
	{
		try {
			$reflectionClass = new ReflectionClass($this->for);
		} catch(ReflectionException $e) {
			throw new InvalidArgumentException("Class '" . $this->for . "' could not be loaded. Does it exist?");
		}
		$rProps = $reflectionClass->getProperties();
		$props = [];
		foreach ($rProps as $prop) {
			// check if the column is already mapped by another table
			if(!isset($this->columns[$prop->getName()])) {
				$props[] = $prop->getName();
			}
		}		
		
		return $props;
	}

	/**
	 * Get all mapped tables
	 * 
	 * @return MappedTable[]
	 */
	public function getTables(): array
	{
		return $this->tables;
	}	
	
	/**
	 * Get the table by name
	 * 
	 * @param string $name
	 * @return MappedTable
	 */
	public function getTable(string $name): MappedTable
	{
		return $this->tables[$name];
	}


  /**
   * Get the first table from the mapping
   *
   * @return MappedTable|null
   */
	public function getPrimaryTable(): ?MappedTable
	{
		return array_values($this->tables)[0] ?? null;
	}

  /**
   * Check if this mapping has the given table or one of it's property relations has it.
   *
   * @param $name
   * @param array $path
   * @param array $paths
   * @return string[] path's of properties
   * @throws Exception
   */
	public function hasTable($name, array $path = [], array &$paths = []): array
	{
		
		if(isset($this->tables[$name])) {
			$paths[] = $path;
		}

		foreach($this->getRelations() as $r) {
			if(!isset($r->propertyName)) {
				//for scalar
				if($r->tableName == $name) {
					$paths[] = array_merge($path, [$r->name]);
				}
				continue;
			}
			/** @var Property $cls */
			$cls = $r->propertyName;
			$cls::getMapping()->hasTable($name, array_merge($path, [$r->name]), $paths);			
		}

		return $paths;
	}

	/**
	 * Add has one relation
	 *
	 * An empty value is null and not an empty object. Set to null to remove.
	 *
	 * @param string $name Property name
	 * @param class-string<Property> $propertyClsName The class name of the property model
	 * @param array $keys
	 * @param bool $autoCreate If not found then automatically create an empty object
	 *
	 * @return $this;
	 */
	public function addHasOne(string $name, string $propertyClsName, array $keys, bool $autoCreate = false): Mapping
	{
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_HAS_ONE);
		$this->relations[$name]->setPropertyName($propertyClsName);
		$this->relations[$name]->autoCreate = $autoCreate;
		$this->relations[$name]->dynamic = $this->dynamic;
		return $this;
	}

	/**
	 * Add an array relation.
	 *
	 * - Array's can be sorted. When the property does not have a primary key, the whole array will be deleted and rewritten
	 *   when saved. This will retain the sort order automatically.
	 * - If the property does have a primary key. The client can send it along to retain it. In this case the sort order must
	 *   be stored in an int column. The framework does this automatically when you specify this. See the $options parameter.
	 * - The property can't be null. An empty value is an empty array.
	 * - When updating the array property, the client must send all items. Items not included will be removed.
	 *
	 * @param string $name Name of the property
	 * @param class-string<Property> $propertyClsName The name of the Property model
	 * @param array $keys The keys of the relation. eg. ['id' => 'articleId']
	 * @param array $options pass ['orderBy' => 'sortOrder'] to save the sort order in this int column. This property can
	 *   be a protected property because the client does not need to know of it's existence.
	 *
	 * @return $this;
	 */
	public function addArray(string $name, string $propertyClsName, array $keys, array $options = []): Mapping
	{
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_ARRAY);
		$this->relations[$name]->setPropertyName($propertyClsName);
		$this->relations[$name]->dynamic = $this->dynamic;
		foreach($options as $option => $value) {
			$this->relations[$name]->$option = $value;
		}
		return $this;
	}

	/**
	 * Add a mapped relation. Index is the ID of the {@see Property}.
	 *
	 * - Map objects are unsorted!
	 * - If the map is empty the value is null and not an empty object
	 * - When updating a map the client must send the full property value. Everything that is not included will be removed.
	 * - Setting a value to null or false will remove it from the map.
	 *
	 * @param string $name
	 * @param class-string<Property> $propertyClsName
	 * @param array $keys
	 *
	 * @return $this
	 */
	public function addMap(string $name, string $propertyClsName, array $keys): Mapping
	{
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_MAP);
		$this->relations[$name]->setPropertyName($propertyClsName);
		$this->relations[$name]->dynamic = $this->dynamic;
		return $this;
	}

	/**
	 * Add a scalar relation. For example an array of ID's.
	 *
	 * A scalar is always an array. It can't be null.
	 *
	 * Sort order of scalars are not guaranteed! MySQL may return it in a different order then it was written.
	 * 
	 * Note: When an entity with scalar relations is saved it automatically looks for other entities referencing the same
	 * scalar relation for tracking changes.
	 * @todo maybe this is unneeded and scalars should only be defined in one entity?
	 *
	 * eg. When a group's users[] change. It will mark all users as changed too because they have a scalar groups[] property.
	 * 
	 * @param string $name
	 * @param string $tableName
	 * @param array $keys
	 * 
	 * @return $this
	 */
	public function addScalar(string $name, string $tableName, array $keys): Mapping
	{
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_SCALAR);
		$this->relations[$name]->setTableName($tableName);
		$this->relations[$name]->dynamic = $this->dynamic;
		return $this;
	}
	
	/**
	 * Get all relational properties
	 * 
	 * @return Relation[]
	 */
	public function getRelations(): array
	{
		return $this->relations;
	}

	private function hasUserTable(): bool
	{
		foreach($this->tables as $table) {
			if($table->isUserTable) {
				return true;
			}
		}
		return false;
	}

	/**
	 * When the model has a map or array property that depends on the user table. For example
	 * a task alert with key id => taskId and userId=>userId.
	 *
	 * @return boolean
	 */
	public function hasUserTableRelation(): bool
	{

		if(!$this->hasUserTable()) {
			return false;
		}

		foreach($this->getRelations() as $relation) {
			if(is_a($relation->propertyName, UserProperty::class, true)){
				return true;
			}
		}

		return false;
	}
	
	/**
	 * Get a relational property by name.
	 * 
	 * @param string $name
	 * @return Relation|boolean
	 */
	public function getRelation(string $name) {
		if(!isset($this->relations[$name])) {
			return false;
		}
		
		return $this->relations[$name];
	}


	/**
	 * Add additional DB query options
	 *
	 * @deprecated use addQuery instead
	 * @param Query $query
	 * @return $this
	 */
	public function setQuery(Query $query): Mapping
	{
		return $this->addQuery($query);
	}


	/**
	 * Add additional DB Query options, merge with current query options if possible
	 *
	 * For example:
	 * ```
	 * $mapping->addQuery((new Query())->select("SUM(b.id) AS sumOfTableBIds")->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']))
	 * ```
	 *
	 * @param Query $q
	 * @return $this
	 */
	public function addQuery(Query $q): Mapping
	{
		if (!empty($this->query)) {
			$this->query->mergeWith($q);
		} else {
			$this->query = $q;
		}

		return $this;
	}
	
	/**
	 * Get the mappings query object.
	 *
	 * @see addQuery()
	 * @return Query|null
	 */
	public function getQuery()
	{
		return $this->query;
	}
	
	/**
	 * Get a column by property name. Returns false if not found in any of the 
	 * mapped tables.
	 * 
	 * @param string $propName
	 * @return boolean|Column
	 */
	public function getColumn(string $propName) {
		return $this->columns[$propName] ?? false;
	}

	/**
	 * Get all columns in the mapping
	 *
	 * @return Column[]
	 */
	public function getColumns(): array
	{
		return array_values($this->columns);
	}

	/**
	 * Get column names
	 *
	 * @return string[]
	 */
	public function getColumnNames(): array
	{
		return array_map(function($c) {
			return $c->name;
		}, $this->getColumns());
	}
	
	/**
	 * Check if a property name is mapped
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasProperty(string $name): bool
	{
		return $this->getRelation($name) != false || $this->getColumn($name) != false;
	}

	/**
	 * Get a column or relation property
	 *
	 * @param $name
	 * @return bool|Column|Relation
	 */
	public function getProperty($name) {
		$relation = $this->getRelation($name);
		if($relation) {
			return $relation;
		}

		$col = $this->getColumn($name);
		if($col) {
			return $col;
		}

		return false;
	}

	/**
	 * Get all mapped property objects in a key value array. This is a mix of columns 
	 * and relations.
	 * 
	 * @return Column[] | Relation[]
	 */
	public function getProperties(): array
	{
		$props = [];
		foreach($this->getTables() as $table) {
			foreach($table->getMappedColumns() as $col) {
				$props[$col->name] = $col;
			}
		}
		
		foreach($this->getRelations() as $relation) {
			$props[$relation->name] = $relation;
		}
		
		return $props;
	}
}
