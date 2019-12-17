<?php
namespace go\core\orm;

use go\core\db\Column;
use go\core\db\Table;
use go\core\db\Connection;

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
	 * When set to true this table will be joined with AND userId = <CURRENTUSERID>
	 * 
	 * @var boolean 
	 */
	public $isUserTable = false;
	
	/**
	 * Mapped table constructor
	 * 
	 * @param string $name The table name
	 * @param string $alias The table alias to use in the queries
	 * @param array $keys If empty then it's assumed the key name is identical in 
	 *   this and the last added table. eg. ['id' => 'id']
	 * @params array $columns Leave this empty if you want to automatically build 
	 *   this based on the properties the model has. If you're extending a model 
	 *   then this is not possinble and you must supply all columns you do want to 
	 *   make available in the model.
	 * @params array $constantValues If the table that is joined needs to have 
	 *   constant values. For example the keys are ['folderId' => 'folderId'] but 
	 *   the joined table always needs to have a value 
	 *   ['type' => "foo"] then you can set it with this parameter.
	 */
	public function __construct($name, $alias, $keys = null, array $columns = [], array $constantValues = [], Connection $conn = null) {
		parent::__construct($name, $conn ?? go()->getDbConnection());
		
		$this->alias = $alias;

		if (!isset($keys)) {
			$keys = $this->buildDefaultKeys();
		}

		$this->keys = $keys;		
		foreach($this->columns as $col) {
			$col->table = $this;
		}

		$this->mappedColumns = array_filter($this->columns, function($c) use ($columns) {
			return in_array($c->name, $columns);
		});	
		
		$this->constantValues = $constantValues;
	}
	
	
	/**
	 * Get the columns that are mapped.	 
	 * 
	 * @return Column[]
	 */
	public function getMappedColumns() {
		return $this->mappedColumns;
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


