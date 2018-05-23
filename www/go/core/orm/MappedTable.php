<?php
namespace go\core\orm;

use go\core\db\Column;
use go\core\db\Table;

class MappedTable extends Table {
	
	/**
	 * The table alias used in the mapping
	 * @var sring
	 */
	private $alias;
	
	/**
	 * The keys used to join to other tables
	 * @var array eg ['id' => 'userId']
	 */
	private $keys;
	
	private $mappedColumns = [];
	
	
	private $constantValues = [];
	
	/**
	 * Mapped table constructor
	 * 
	 * @param string $name The table name
	 * @param sring $alias The table alias to use in the queries
	 * @param array $keys If empty then it's assumed the key name is identical in 
	 *   this and the last added table. eg. ['id' => 'id']
	 * @params array $columns Leave this empty if you want to automatically build 
	 *   this based on the properties the model has. If you're extending a model 
	 *   then this is not possinble and you must supply all columns you do want to 
	 *   make available in the model.
	 * @params array $constantValues If the table that is joined needs to have 
	 *   constant values. For example the keys are ['folderId' => 'folderId'] but 
	 *   the joined table always needs to have a value 
	 *   ['userId' => GO()->getUserId()] then you can set it with this parameter.
	 */
	public function __construct($name, $alias, $keys = null, array $columns = [], array $constantValues = []) {
		parent::__construct($name);
		
		$this->alias = $alias;

		if (!isset($keys)) {
			$keys = $this->buildDefaultKeys();
		}

		$this->keys = $keys;		
		$this->mappedColumns = $columns;
		$this->constantValues = $constantValues;
	}
	
	
	/**
	 * Get the columns that are mapped
	 * 
	 * @return Column[]
	 */
	public function getMappedColumns() {
		return array_filter($this->columns, function($c) {
			return in_array($c->name, $this->mappedColumns);
		});
	}
	
	public function getColumn($name) {
		$cols = $this->getMappedColumns();
		return $cols[$name] ?? null;
	}
	
	private function buildDefaultKeys() {
		$keys = [];
		foreach ($this->getPrimaryKey() as $pkName) {
			$keys[$pkName] = $pkName;
		}
		
		return $keys;
	}
	
	/**
	 * Get the constant values.
	 * 
	 * @see __construct()
	 * 
	 * @return array ['col' => 'value']
	 */
	public function getConstantValues() {
		return $this->constantValues;
	}
	
	/**
	 * Get the table alias used in the mapping
	 * 
	 * @return string
	 */
	public function getAlias() {
		return $this->alias;
	}
	
	/**
	 * The keys used to join to other tables
	 * 
	 * @return array eg ['id' => 'userId']
	 */
	public function getKeys() {
		return $this->keys;
	}
}


