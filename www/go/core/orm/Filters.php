<?php
namespace go\core\orm;

use DateInterval;
use Exception;
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
	 * @param Callable $fn The filter function will be called with Criteria $criteria, $value, Query $query, array $filterCondition, Filters $filter
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function add(string $name, callable $fn, $default = self::NO_DEFAULT): Filters
	{
		$this->filters[strtolower($name)] = ['type' => 'generic', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}


	/**
	 * Add a filter on a column name
	 *
	 * Shortcut for:
	 *
	 * ```
	 * ->add('businessId', function(Criteria  $c, $value) {
	 *  $c->andWhere('businessId', $value);
	 * });
	 * ```
	 * @param string $name
	 * @return $this
	 */
	public function addColumn(string $name): Filters
	{
		return $this->add($name, function(Criteria  $c, $value) use ($name) {
			$c->andWhere($name, '=', $value);
		});
	}

	// private function validate(Query $query, array $filter) {
	// 	$invalidFilters = array_diff(array_map('strtolower',array_keys($filter)), array_keys($this->filters));
	// 	if(!empty($invalidFilters)) {
	// 		throw new Exception("Invalid filters supplied for '".$query->getModel()."': '". implode("', '", $invalidFilters) ."'");
	// 	}
	// }

	/**
	 * @throws Exception
	 */
	private function applyDefaults(Query $query, Criteria $criteria) {

		$f = [];

		foreach($this->filters as $name => $value) {

			if(in_array($name, $query->usedFilters)) {
				continue;
			}

			if($value['default'] === self::NO_DEFAULT) {
				continue;
			}

			if(!array_key_exists($value['name'], $f)) {
				$f[$value['name']] = $value['default'];
			}
		}

		$this->internalApply($query, $f, $criteria);

	}


	/**
	 * Apply given filters to query object
	 *
	 * @param Query $query
	 * @param array $filter
	 * @return Filters
	 * @throws Exception
	 */
	public function apply(Query $query, array $filter) :Filters {

		$criteria = new Criteria();
		$this->internalApply($query, $filter, $criteria);

		//apply defaults of unused filters
		$this->applyDefaults($query, $criteria);

		if($criteria->hasConditions()) {
			$query->andWhere($criteria);
		}

		return $this;
	}

	/**
	 * @throws Exception
	 */
	private function internalApply(Query $query, array $filter, Criteria $criteria): void
	{
		if(isset($filter['conditions']) && isset($filter['operator'])) { // is FilterOperator

			foreach($filter['conditions'] as $condition) {
				$subCriteria = new Criteria();
				$this->internalApply($query, $condition, $subCriteria);

				if(!$subCriteria->hasConditions()) {
					continue;
				}

				switch(strtoupper($filter['operator'])) {
					case 'AND':
						$criteria->where($subCriteria);
						break;

					case 'OR':
						$criteria->orWhere($subCriteria);
						break;

					case 'NOT':
						$criteria->andWhereNotOrNull($subCriteria);
						break;
				}
			}

		} else {
			// is FilterCondition
			$subCriteria = new Criteria();

			$this->applyCondition($query, $subCriteria, $filter);

			if($subCriteria->hasConditions()) {
				$criteria->andWhere($subCriteria);
			}
		}

	}

  /**
   * Applies all filters to the query object
   *
   * @param Query $query
   * @param Criteria $criteria
   * @param array $filter
   * @throws Exception
   */
	private function applyCondition(Query $query, Criteria $criteria, array $filter) {
		//$this->validate($query, $filter);		
		foreach($filter as $name => $value) {
			$name = strtolower($name);

			if(!isset($this->filters[$name])) {
				throw new UnsupportedFilter($query->getModel()::entityType()->getName(), $name);
			}

			$query->usedFilters[] = $name;

			$filterConfig = $this->filters[$name];
			
			switch($filterConfig['type']) {
				
				case 'number':					
					$range = $this->checkRange($value);
					if($range) {
						call_user_func($filterConfig['fn'], $criteria, '>=', (int) $range[0], $query, $filter, $this);
						call_user_func($filterConfig['fn'], $criteria, '<=', (int) $range[1], $query, $filter, $this);
					} else
					{
						$v = self::parseNumericValue($value);
						call_user_func($filterConfig['fn'], $criteria, $v['comparator'], (int) $v['query'], $query, $filter, $this);
					}
					break;

				case 'datetime':
				case 'date':					
					$range = $this->checkDateRange($value, $filterConfig['type']=='datetime');
					if($range) {
						call_user_func($filterConfig['fn'], $criteria, '>=', $range[0], $query, $filter, $this);
						call_user_func($filterConfig['fn'], $criteria, '<=', $range[1], $query, $filter, $this);
					} else
					{
						if($value == null) {
							$v = [
								'comparator' => '=',
								'query' => null
							];
						} else {
							$v = self::parseNumericValue($value);
							$v["query"] = new DateTime($v["query"]);
						}
						call_user_func($filterConfig['fn'], $criteria, $v['comparator'], $v["query"], $query, $filter, $this);
					}
					break;
					
				case 'text':
					if(!is_array($value)){
						$value = [$value];
					}
					call_user_func($filterConfig['fn'], $criteria, "LIKE", $value, $query, $filter, $this);
					break;
				
				case 'generic':
					call_user_func($filterConfig['fn'], $criteria, $value, $query, $filter, $this);
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
	 * @param Callable $fn Called with:
	 *    Criteria $criteria,
	 *    $comparator,
	 *    $value,
	 *    Query $query,
	 *    array $filterCondition, // Not the full filter request but. The filter condition currently being processed. See JMAP spec.
	 *    Filters $filters // this filters object
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function addNumber(string $name, Callable $fn, $default = self::NO_DEFAULT): Filters
	{
		$this->filters[strtolower($name)] = ['type' => 'number', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}	
	
	/**
	 * Add date time filter.
	 *
	 * Supports ranges. For example last week..now,  >last year, >2019-01-01
	 *
	 * Values are converted to DateTime objects. Supports all strtotime formats as input.
	 *
	 * NOTE: The time input is converted from the user time zone to UTC.
	 *
	 * @param string $name
	 * @param Callable $fn Called with: Criteria $criteria, $comparator, DateTime $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 *
	 * @return $this
	 * @example
	 *
	 * ->addDateTime('date',function(Criteria $criteria, $comparator, $value){
	 * 	$criteria->where('date', $comparator, $value);
	 * })
	 *
	 * @return $this
	 */
	public function addDateTime(string $name, Callable $fn, $default = self::NO_DEFAULT): Filters
	{
		$this->filters[strtolower($name)] = ['type' => 'datetime', 'fn' => $fn, 'default' => $default, 'name' => $name];

		return $this;
	}


	/**
	 * Add date filter.
	 *
	 * NOTE: this is for fields without time. No timezone conversion takes place. If time is applicable use {@see self::addDateTime()}
	 *
	 * Supports ranges. For example last week..now,  >last year, >2019-01-01
	 *
	 * Values are converted to DateTime objects. Supports all strtotime formats as input.
	 *
	 * @param string $name
	 * @param Callable $fn Called with: Criteria $criteria, $comparator, ?DateTime $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 *
	 * @return $this
	 *@example
	 *
	 * ->addDateTime('date',function(Criteria $criteria, $comparator, ?DateTime $value){
	 * 	$criteria->where('date', $comparator, $value);
	 * })
	 *
	 * @return $this
	 */
	public function addDate(string $name, Callable $fn, $default = self::NO_DEFAULT): Filters
	{
		$this->filters[strtolower($name)] = ['type' => 'date', 'fn' => $fn, 'default' => $default, 'name' => $name];

		return $this;
	}

	/**
	 * Add text filter.
	 * 
	 * Comparator will be LIKE
	 * 
	 * @param string $name
	 * @param Callable $fn Called with: Criteria $criteria, $comparator, $value, Query $query, array $filters
	 * @param mixed $default The default value for the filter. When not set the filter is not applied if no value is given.
	 * 
	 * @return $this
	 */
	public function addText(string $name, Callable $fn, $default = self::NO_DEFAULT): Filters
	{
		$this->filters[strtolower($name)] = ['type' => 'text', 'fn' => $fn, 'default' => $default, 'name' => $name];
		
		return $this;
	}

	/**
	 * Check if a filter is already defined.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function hasFilter(string $name): bool
	{
		return isset($this->filters[strtolower($name)]);
	}
	
	public static function parseNumericValue($value): array
	{
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

	/**
	 * @throws Exception
	 */
	private function checkRange($value): bool|array
	{
		//Operators >, <, =, !=,
		//Range ..

		if($value == null) {
			return false;
		}
		
		$parts = array_map('trim', explode('..', $value));
		if(count($parts) > 2) {
			throw new Exception("Invalid range. Only one .. allowed");
		}
		
		if(count($parts) == 1) {
			//no range given
			return false;
		}

		return $parts;
	}

	/**
	 * @throws Exception
	 */
	private function checkDateRange(?string $value, bool $convertTimezone = true): array|false{
		//Operators >, <, =, !=,
		//Range ..

		if($value == null) {
			return false;
		}

		$parts = array_map('trim', explode('..', $value));
		if(count($parts) > 2) {
			throw new Exception("Invalid range. Only one .. allowed");
		}

		if(count($parts) == 1) {
			//no range given
			return false;
		}

		$endHasTime = strpos($parts[1], ':') !== false;

		if($convertTimezone) {
			$tz = go()->getAuthState()->getUser()->timezone;

			$parts[0] = new DateTime($parts[0], new \DateTimeZone($tz));
			$parts[0]->setTimezone(new \DateTimeZone('UTC'));

			$parts[1] = new DateTime($parts[1], new \DateTimeZone($tz));
			$parts[1]->setTimezone(new \DateTimeZone('UTC'));
		} else {
			$parts[0] = new DateTime($parts[0], new \DateTimeZone('UTC'));
			$parts[1] = new DateTime($parts[1], new \DateTimeZone('UTC'));
		}

		$parts['endHasTime'] = $endHasTime;

		return $parts;
	}
}
