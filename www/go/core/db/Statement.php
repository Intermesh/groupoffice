<?php
namespace go\core\db;

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
	
	public $debugQueryString;

}
