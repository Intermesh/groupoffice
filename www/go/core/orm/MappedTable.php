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
	
	/**
	 * 
	 * @param string $name
	 * @param array $columns List the columns that are allowed to be mapped
	 */
	public function __construct($name, $alias, $keys = null, array $columns = []) {
		parent::__construct($name);
		
		$this->alias = $alias;

		if (!isset($keys)) {
			$keys = $this->buildDefaultKeys();
		}

		$this->keys = $keys;
		
		$this->mappedColumns = $columns;
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
	
	private function buildDefaultKeys() {
		$keys = [];
		foreach ($this->getPrimaryKey() as $pkName) {
			$keys[$pkName] = $pkName;
		}
		
		return $keys;
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


