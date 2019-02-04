<?php
namespace go\core\orm;

use Exception;

/**
 * Filters
 * 
 * Holds all filters for an entity
 */
class Filters {
	
	private $functions = [];
	
	/**
	 * Add a filter function
	 * 
	 * @param string $name The name of the filter.
	 * @param Callable $fn The filter function will be called with Query $query, $value, array $filter 
	 * @return $this
	 */
	public function add($name, $fn) {
		$this->functions[strtolower($name)] = $fn;
		
		return $this;
	}
	
	private function validate(Query $query, array $filter) {
		$invalidFilters = array_diff(array_map('strtolower',array_keys($filter)), array_keys($this->functions));
		if(!empty($invalidFilters)) {
			throw new Exception("Invalid filters supplied for '".$query->getModel()."': '". implode("', '", $invalidFilters) ."'");
		}
	}
	
	/**
	 * Applies all filters to the query object
	 * 
	 * @param Query $query
	 * @param array $filter
	 */
	public function apply(Query $query, array $filter) {
		$this->validate($query, $filter);		
		foreach($filter as $name => $value) {
			call_user_func($this->functions[strtolower($name)], $query, $value, $filter);
		}
	}
}
