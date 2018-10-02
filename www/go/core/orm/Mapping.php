<?php

namespace go\core\orm;

use go\core\db\Column;
use go\core\db\Query;
use ReflectionClass;

/**
 * Mapping object 
 * 
 * It maps tables to objects properties.
 * The mapping object is cached. So when you make changes you need to run /install/upgrade.php
 */
class Mapping {
	
	
	
	private $for;

	/**
	 *
	 * @var MappedTable[] 
	 */
	private $tables = [];
	
	
	private $relations = [];
	
//	private $selectAliases = [];
	
	
	private $query;
	
	public function __construct($for) {
		$this->for = $for;
	}

	/**
	 * Adds a table to the model
	 * 
	 * @param string $name The table name
	 * @param sring $alias The table alias to use in the queries
	 * @param array $keys If null then it's assumed the key name is identical in 
	 *   this and the last added table. eg. ['id' => 'id']
	 * @params array $columns Leave this null if you want to automatically build 
	 *   this based on the properties the model has. If you're extending a model 
	 *   then this is not possinble and you must supply all columns you do want to 
	 *   make available in the model.
	 * @params array $constantValues If the table that is joined needs to have 
	 *   constant values. For example the keys are ['folderId' => 'folderId'] but 
	 *   the joined table always needs to have a value 
	 *   ['type' => "foo"] then you can set it with this parameter.
	 * @return $this
	 */
	public function addTable($name, $alias = 't', array $keys = null, array $columns = null, array $constantValues = []) {
		$this->tables[$name] = new MappedTable($name, $alias, $keys, empty($columns) ? $this->buildColumns() : $columns, $constantValues);
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
	 */
	public function addUserTable($name, $alias, array $keys = null, array $columns = null, array $constantValues = []) {
		$this->tables[$name] = new MappedTable($name, $alias, $keys, empty($columns) ? $this->buildColumns() : $columns, $constantValues);
		$this->tables[$name]->isUserTable = true;
		if(!\go\core\db\Table::getInstance($name)->getColumn('modSeq')) {
			throw new \Exception("The table ".$name." must have a 'modSeq' column of type INT");
		}
		
		if(!\go\core\db\Table::getInstance($name)->getColumn('userId')) {
			throw new \Exception("The table ".$name." must have a 'userId' column of type INT");
		}
		
		return $this;
	}
	
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
	 * 
	 * @return MappedTable[]
	 */
	public function getTables() {
		return $this->tables;
	}	
	
	/**
	 * 
	 * @param string $name
	 * @return MappedTable
	 */
	public function getTable($name) {
		return $this->tables[$name];
	}	
	
	/**
	 * 
	 * @param type $name
	 * @param type $entityName
	 * @param array $keys
	 * @param type $many
	 * @return $this
	 */
	public function addRelation($name, $entityName, array $keys, $many = true) {
		$this->relations[$name] = new Relation($name, $entityName, $keys, $many);
		return $this;
	}
	
	/**
	 * 
	 * @return Relation[]
	 */
	public function getRelations() {
		return $this->relations;
	}
	
	/**
	 * 
	 * @param stgring $name
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
	 * 
	 * @param type $propName
	 * @return boolean|Column
	 */
	public function getColumn($propName) {
		foreach($this->getTables() as $table) {
			$column = $table->getColumn($propName);
			if($column) {
				return $column;
			}
		}
		
		return false;
	}
	
	public function hasProperty($name) {
		return $this->getRelation($name) != false || $this->getColumn($name) != false;
	}
	
	/**
	 * 
	 * @return array
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
