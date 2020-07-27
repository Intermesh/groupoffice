<?php
namespace go\core\orm;

use Exception;
use Closure;
use go\core\db\Criteria;
use go\core\jmap\exception\UnsupportedFilter;
use go\core\util\DateTime;

/**
 * Filters
 * 
 * Holds all filters for an entity
 */
class Filters {
	
	private $filters = [];

	const NO_DEFAULT = '__NO_DEFAULT__';

	
	/**
	 * Add generic filter function
	 * 
	 * See also addText(), addNumber() and addDate() for different types
	 * 
	 * @param string $name The name of the filter.
	 * @param Callable $fn The filter function will be called with Criteria $criteria, $value, Query $query, array $filter 
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function add($name, $fn, $default = self::NO_DEFAULT) {
		$this->filters[strtolower($name)] = ['type' => 'generic', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}
	
	// private function validate(Query $query, array $filter) {
	// 	$invalidFilters = array_diff(array_map('strtolower',array_keys($filter)), array_keys($this->filters));
	// 	if(!empty($invalidFilters)) {
	// 		throw new Exception("Invalid filters supplied for '".$query->getModel()."': '". implode("', '", $invalidFilters) ."'");
	// 	}
	// }

	private function applyDefaults(array $filter) {

		$f = [];
		foreach($filter as $k => $v) {

			$index = strtolower($k);

			if(!isset($this->filters[$index])) {
				throw new Exception("Filter '". $k."' is invalid");
			}

			$f[$this->filters[$index]['name']] = $v;
		}

		foreach($this->filters as $value) {
			if($value['default'] === self::NO_DEFAULT) {
				continue;
			}

			if(!array_key_exists($value['name'], $f)) {
				$f[$value['name']] = $value['default'];
			}
		}

		return $f;
	}

  /**
   * Applies all filters to the query object
   *
   * @param Query $query
   * @param Criteria $criteria
   * @param array $filter
   * @throws Exception
   */
	public function apply(Query $query, Criteria $criteria, array $filter) {

		$filter = $this->applyDefaults($filter);

		//$this->validate($query, $filter);		
		foreach($filter as $name => $value) {
			$name = strtolower($name);

			if(!isset($this->filters[$name])) {
				throw new UnsupportedFilter();
			}

			$filterConfig = $this->filters[$name];
			
			switch($filterConfig['type']) {
				
				case 'number':					
					$range = $this->checkRange($value);
					if($range) {
						call_user_func($filterConfig['fn'], $criteria, '>=', (int) $range[0], $query, $filter);
						call_user_func($filterConfig['fn'], $criteria, '<=', (int) $range[1], $query, $filter);
					} else
					{
						$v = self::parseNumericValue($value);
						call_user_func($filterConfig['fn'], $criteria, $v['comparator'], (int) $v['query'], $query, $filter);
					}
					break;
					
				case 'date':					
					$range = $this->checkRange($value);
					if($range) {
						$range[0] = new DateTime($range[0]);
						$range[1] = new DateTime($range[1]);
						
						call_user_func($filterConfig['fn'], $criteria, '>=', $range[0], $query, $filter);
						call_user_func($filterConfig['fn'], $criteria, '<=', $range[1], $query, $filter);
					} else
					{
						$v = self::parseNumericValue($value);
						$v["query"] = new DateTime($v["query"]);
						call_user_func($filterConfig['fn'], $criteria, $v['comparator'], $v["query"], $query, $filter);
					}
					break;
					
				case 'text':
					if(!is_array($value)){
						$value = [$value];
					}
					call_user_func($filterConfig['fn'], $criteria, "LIKE", $value, $query, $filter);
					break;
				
				case 'generic':
					call_user_func($filterConfig['fn'], $criteria, $value, $query, $filter);
					break;
			}
			
		}
	}
	
	/**
	 * Add number filter.
	 * 
	 * Supports ranges 1..4 between 1 and 4 and >=, <> != = operators
	 * 
	 * @param string $name
	 * @param Closure $fn Called with: Criteria $criteria, $comparator, $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function addNumber($name, $fn, $default = self::NO_DEFAULT) {
		$this->filters[strtolower($name)] = ['type' => 'number', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}	
	
	/**
	 * Add date filter.
	 * 
	 * Supports ranges. For example last week..now,  >last year, >2019-01-01
	 * 
	 * Values are converted to DateTime objects. Supports all strtotime formats as input.
	 *
	 * @example
	 *
	 * ->addDate('date',function(Criteria $criteria, $comparator, $value){
	 * 	$criteria->where('date', $comparator, $value);
	 * })
	 * 
	 * @param string $name
	 * @param Closure $fn Called with: Criteria $criteria, $comparator, DateTime $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function addDate($name, $fn, $default = self::NO_DEFAULT) {
		$this->filters[strtolower($name)] = ['type' => 'date', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}	
	
	/**
	 * Add text filter.
	 * 
	 * Values are wrapped with %..% and comparator will be LIKE or NOT LIKE
	 * 
	 * @param string $name
	 * @param Closure $fn Called with: Criteria $criteria, $comparator, $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function addText($name, $fn, $default = self::NO_DEFAULT) {
		$this->filters[strtolower($name)] = ['type' => 'text', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}

	/**
	 * Check if a filter is already defined.
	 *
	 * @param $name
	 * @return bool
	 */
	public function hasFilter($name) {
		return isset($this->filters[strtolower($name)]);
	}
	
	public static function parseNumericValue($value) {
		$regex = '/\s*(>=|<=|>|<|!=|<>|=)\s*(.*)/';
		if(preg_match($regex, $value, $matches)) {
			list(,$comparator, $v) = $matches;
		} else
		{
			$comparator = '=';
			$v = $value;
		}
		
		return ['comparator' => $comparator, 'query' => $v];
	}
	
	// public static function parseStringValue($value) {
	// 	if(!is_array($value)) {
	// 		$value = [$value];
	// 	}
		
	// 	$regex = '/\s*(!=|=)?\s*(.*)/';
	// 	if(preg_match($regex, $value, $matches)) {
	// 		list(,$comparator, $v) = $matches;
	// 	} else
	// 	{
	// 		$comparator = '=';
	// 		$v = '%'.$value.'%';
	// 	}
		
	// 	return [
	// 			['comparator' => $comparator == '=' ? 'LIKE' : 'NOT LIKE', 'query' => $v]
	// 	];
	// }
	
	private function checkRange($value) {
		//Operators >, <, =, !=,
		//Range ..
		
		$parts = array_map('trim', explode('..', $value));
		if(count($parts) > 2) {
			throw new \Exception("Invalid range. Only one .. allowed");
		}
		
		if(count($parts) == 1) {
			//no range given
			return false;
		}
		
		return $parts;
	}
}
