<?php
namespace go\core\orm;

use DateInterval;
use DateTimeZone;
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
	 * @var class-string<Entity>
	 */
	private string $entityCls;

	/**
	 * @param class-string<Entity> $entityCls
	 */
	public function __construct(string $entityCls)
	{
		$this->entityCls = $entityCls;
	}


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
	 *
	 * ```
	 *
	 * If the column is a date or datetime it will use {@link addDate())
	 *
	 * @param string $name
	 * @return $this
	 */
	public function addColumn(string $name): Filters
	{
		/**
		 * @var class-string<Entity> $cls;
		 */
		$cls = $this->entityCls;

		$col = $cls::getMapping()->getColumn($name);

		if(in_array($col->dbType, ["localdatetime", "datetime", "date"])) {
			return $this->addDate($name, function (Criteria $c, $comparator, $value) use ($name) {
				$c->andWhere($name, $comparator, $value);
			});
		} else {
			return $this->add($name, function (Criteria $c, $value) use ($name) {
				$c->andWhere($name, '=', $value);
			});
		}
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
						call_user_func($filterConfig['fn'], $criteria, '>=', (float) $range[0], $query, $filter, $this);
						call_user_func($filterConfig['fn'], $criteria, '<=', (float) $range[1], $query, $filter, $this);
					} else
					{
						$v = self::parseNumericValue($value);
						call_user_func($filterConfig['fn'], $criteria, $v['comparator'], (float) $v['query'], $query, $filter, $this);
					}
					break;

				case 'datetime':
				case 'date':					
					$range = static::parseDateRange($value, $filterConfig['type']=='datetime');
					foreach($range as $rule) {
						call_user_func($filterConfig['fn'], $criteria, $rule['comparator'], $rule['date'], $query, $filter, $this);
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
	 * Parse a date range value
	 *
	 * now...2025-01-01 will return [['comparator' => '>=', 'date' => DateTime('now')], ['comparator' => '<', 'date' => new DateTime('2025-01-02')]]
	 *
	 * @param string|null $value
	 * @param bool $convertTimeZone
	 * @return array<int, array{comparator: string, date: DateTime}>
	 * @throws \DateMalformedStringException
	 */
	public static function parseDateRange(?string $value, bool $convertTimeZone = true): array  {
		$range = static::checkDateRange($value, $convertTimeZone);
		if($range) {
			return $range;
		} else
		{
			if($value == null) {
				return [];
			} else {
				$v = self::parseNumericValue($value);
				return [['comparator' => $v['comparator'], 'date' => new DateTime($v["query"])]];
			}
		}
	}

	/**
	 * @param array<int, array{comparator: string, date: DateTime}> $range
	 * @return string
	 * @throws \DateInvalidOperationException
	 */
	public static function dateRangeToString(array $range) : string {
		if(count($range) == 2) {
			return $range[0]['date']->toUserFormat() .' - ' .$range[01]['date']->toUserFormat();
		} else {
			switch($range[0]['comparator']) {
				case '>':
					return go()->t("from") .' ' . $range[0]['date']->add(new DateInterval("P1D"))->toUserFormat() ;
				case '>=':
					return go()->t("from") .' ' . $range[0]['date']->toUserFormat() ;

				case '<':
					return go()->t("until") .' ' . $range[0]['date']->sub(new DateInterval("P1D"))->toUserFormat() ;
				case '<=':
					return go()->t("until") .' ' . $range[0]['date']->toUserFormat() ;
			}

			return $range[0]['comparator'].' '.$range[0]['date']->toUserFormat();
		}
	}

	/**
	 * @throws Exception
	 * @return array<int, array{comparator: string, date: DateTime}>
	 */
	private static function checkDateRange(?string $value, bool $convertTimezone = true): array {
		//Operators >, <, =, !=,
		//Range ..

		$range = [
			['comparator' => '>=', 'date' => null],
			['comparator' => '<=', 'date' => null]
		];

		if($value == null) {
			return [];
		}

		$parts = array_map('trim', explode('..', $value));
		if(count($parts) > 2) {
			throw new Exception("Invalid range. Only one .. allowed");
		}

		if(count($parts) == 1) {
			//no range given
			return [];
		}

		$endHasTime = str_contains($parts[1], ':');

		if($convertTimezone) {
			$tz = go()->getAuthState()->getUser()->timezone;

			$parts[0] = new DateTime($parts[0], new DateTimeZone($tz));
			$parts[0]->setTimezone(new DateTimeZone('UTC'));

			$parts[1] = new DateTime($parts[1], new DateTimeZone($tz));
			$parts[1]->setTimezone(new DateTimeZone('UTC'));
		} else {
			$parts[0] = new DateTime($parts[0], new DateTimeZone('UTC'));
			$parts[1] = new DateTime($parts[1], new DateTimeZone('UTC'));
		}

		if(!$endHasTime) {
			$parts[1]->modify('+1 day');
			$range[1]['comparator'] = '<';
		}

		$range[0]['date'] = $parts[0];
		$range[1]['date'] = $parts[1];

		return $range;
	}
}
