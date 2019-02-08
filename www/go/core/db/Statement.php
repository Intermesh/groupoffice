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
	
	/**
	 * Set's the select query object
	 * 
	 * @param \go\core\db\Query $query
	 */
	public function setQuery(Query $query) {
		$this->query = $query;
	}
	
	/**
	 * Get query object that was used to create this statement.
	 * 
	 * Only available for select queries
	 *  
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}
	
	private $build;
	
	/**
	 * Set's the build array produced by QueryBuilder. Only used to cast this object
	 * to string when debugging.
	 * 
	 * @param array $build
	 */
	public function setBuild(array $build) {
		$this->build = $build;
	}
	
	public function __toString() {
		return QueryBuilder::debugBuild($this->build);
	}
	
}
