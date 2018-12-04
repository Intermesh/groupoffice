<?php
namespace go\core\db;

/**
 * PDO Statement
 * 
 * Represents a prepared statement and, after the statement is executed, an
 * associated result set.
 */
class Statement extends \PDOStatement implements \JsonSerializable {
	
	private $query;
	
	public function jsonSerialize() {
		return $this->fetchAll();
	}
	
	public function setQuery(Query $query) {
		$this->query = $query;
	}
	
	/**
	 * Get query object that was used to create this statement
	 * 
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}
	
}
