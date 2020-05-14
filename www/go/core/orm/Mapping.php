<?php

namespace go\core\orm;

use Exception;
use go\core\db\Column;
use go\core\db\Query;
use go\core\db\Table;
use ReflectionClass;
use ReflectionException;

/**
 * Mapping object 
 * 
 * It maps tables to objects properties.
 * The mapping object is cached. So when you make changes you need to run /install/upgrade.php
 */
class Mapping {	
	
	/**
	 * Property class name this mapping is for
	 * 
	 * @var string 
	 */
	private $for;

	/**
	 *
	 * @var MappedTable[] 
	 */
	private $tables = [];	


	private $columns = [];
	
	private $relations = [];
	
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
	 * @param string $for Property class name this mapping is for
	 */
	public function __construct($for) {
		$this->for = $for;
	}

  /**
   * Adds a table to the model
   *
   * @param string $name The table name
   * @param string $alias The table alias to use in the queries
   * @param array $keys If null then it's assumed the key name is identical in
   *   this and the last added table. eg. ['id' => 'id']
   * @param array $columns Leave this null if you want to automatically build
   *   this based on the properties the model has. If you're extending a model
   *   then this is not possinble and you must supply all columns you do want to
   *   make available in the model.
   * @param array $constantValues If the table that is joined needs to have
   *   constant values. For example the keys are ['folderId' => 'folderId'] but
   *   the joined table always needs to have a value
   *   ['type' => "foo"] then you can set it with this parameter.
   * @return $this
   * @throws ReflectionException
   */
	public function addTable($name, $alias = null, array $keys = null, array $columns = null, array $constantValues = []) {
		
		if(!$alias) {
			$alias = $name;
		}
		$this->tables[$name] = new MappedTable($name, $alias, $keys, empty($columns) ? $this->buildColumns() : $columns, $constantValues);
		foreach($this->tables[$name]->getMappedColumns() as $col) {
			if(!isset($this->columns[$col->name] )) { //if two identical columns are mapped the first one will be used. Can happen with "id" when A extends B.
				$this->columns[$col->name] = $col;
			}
		}
		return $this;
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
   * @throws Exception
   */
	public function addUserTable($name, $alias, array $keys = null, array $columns = null, array $constantValues = []) {
		$this->tables[$name] = new MappedTable($name, $alias, $keys, empty($columns) ? $this->buildColumns() : $columns, $constantValues);
		$this->tables[$name]->isUserTable = true;
		$this->hasUserTable = true;
		if(!Table::getInstance($name)->getColumn('modSeq')) {
			throw new Exception("The table ".$name." must have a 'modSeq' column of type INT");
		}
		
		if(!Table::getInstance($name)->getColumn('userId')) {
			throw new Exception("The table ".$name." must have a 'userId' column of type INT");
		}
		
		return $this;
	}

  /**
   * @return array
   * @throws ReflectionException
   */
	private function buildColumns() {
		$reflectionClass = new ReflectionClass($this->for);
		$rProps = $reflectionClass->getProperties();
		$props = [];
		foreach ($rProps as $prop) {
			$props[] = $prop->getName();
		}		
		
		return $props;
	}

	/**
	 * Get all mapped tables
	 * 
	 * @return MappedTable[]
	 */
	public function getTables() {
		return $this->tables;
	}	
	
	/**
	 * Get the table by name
	 * 
	 * @param string $name
	 * @return MappedTable
	 */
	public function getTable($name) {
		return $this->tables[$name];
	}


  /**
   * Get the first table from the mapping
   *
   * @return MappedTable
   * @throws Exception
   */
	public function getPrimaryTable() {
		if(empty($this->tables)) {
			throw new Exception("No table mapped");
		}
		return array_values($this->tables)[0];
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
	public function hasTable($name, $path = [], &$paths = []) {
		
		if(isset($this->tables[$name])) {
			$paths[] = $path;
		}

		foreach($this->getRelations() as $r) {
			if(!isset($r->entityName)) {
				//for scalar
				if($r->tableName == $name) {
					$paths[] = array_merge($path, [$r->name]);
				}
				continue;
			}
			/** @var Property $cls */
			$cls = $r->entityName;
			$cls::getMapping()->hasTable($name, array_merge($path, [$r->name]), $paths);			
		}

		return $paths;
	}

	/**
	 * Add has one relation
	 * 
	 * @param string $name
	 * @param string $entityName
	 * @param array $keys
	 * @param bool $autoCreate If not found then automatically create an empty object
	 * 
	 * @return $this;
	 */
	public function addHasOne($name, $entityName, array $keys, $autoCreate = false) {
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_HAS_ONE);
		$this->relations[$name]->setEntityName($entityName);
		$this->relations[$name]->autoCreate = $autoCreate;
		return $this;
	}

	/**
	 * Add an array relation.
	 * 
	 * @param string $name
	 * @param string $entityName
	 * @param array $keys
	 * 
	 * @return $this;
	 */
	public function addArray($name, $entityName, array $keys) {
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_ARRAY);
		$this->relations[$name]->setEntityName($entityName);
		return $this;
	}

	/**
	 * Add a mapped relation. Index is the ID.
	 * 
	 * @param string $name
	 * @param string $entityName
	 * @param array $keys
	 * 
	 * @return $this;
	 */
	public function addMap($name, $entityName, array $keys) {
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_MAP);
		$this->relations[$name]->setEntityName($entityName);
		return $this;
	}

	/**
	 * Add a scalar relation. For example an array of ID's.
	 * 
	 * Note: When an entity with scalar relations is saved it automatically looks for other entities referencing the same scalar relation for trracking changes.
	 * 
	 * eg. When a group's users[] change. It will mark all users as changed too because they have a scalar groups[] property.
	 * 
	 * @param string $name
	 * @param string $tableName
	 * @param array $keys
	 * 
	 * @return $this;
	 */
	public function addScalar($name, $tableName, array $keys) {
		$this->relations[$name] = new Relation($name, $keys, Relation::TYPE_SCALAR);
		$this->relations[$name]->setTableName($tableName);
		return $this;
	}
	
	/**
	 * Get all relational properties
	 * 
	 * @return Relation[]
	 */
	public function getRelations() {
		return $this->relations;
	}
	
	/**
	 * Get a relational property by name.
	 * 
	 * @param string $name
	 * @return Relation|boolean
	 */
	public function getRelation($name) {
		if(!isset($this->relations[$name])) {
			return false;
		}
		
		return $this->relations[$name];
	}
	
	/**
	 * Add additional DB query options
	 * 
	 * For example:
	 * 
	 * ```
	 * $mapping->setQuery((new Query())->select("SUM(b.id) AS sumOfTableBIds")->join('test_b', 'bc', 'bc.id=a.id')->groupBy(['a.id']))
	 * ```
	 *
	 * @param Query $query
	 * @return $this
	 */
	
	
	public function setQuery(Query $query) {
		$this->query = $query;
		
		return $this;
	}
	
	/**
	 * 
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}
	
	/**
	 * Get a column by property name. Returns false if not found in any of the 
	 * mapped tables.
	 * 
	 * @param string $propName
	 * @return boolean|Column
	 */
	public function getColumn($propName) {
		return $this->columns[$propName] ?? false;
	}
	
	/**
	 * Check if a property name is mapped
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasProperty($name) {
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
	 * @return Column[] | Relation
	 */
	public function getProperties() {
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
