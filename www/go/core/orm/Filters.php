<?php
namespace go\core\orm;

use DateInterval;
use DateTimeZone;
use Exception;
use LogicException;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\UnsupportedFilter;
use go\core\util\DateTime;

/**
 * Filters
 * 
 * Holds all filters for an entity
 */
class Filters {
	
	
	private $filters = [];

	/**
	 * Lower case name of the filter that was added last. Used by {@see describe()}.
	 */
	private ?string $lastAdded = null;


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
		$this->lastAdded = strtolower($name);
		
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
	 * If the column is a datetime it will use {@see addDateTime()} and for date or localdatetime {@see addDate()}
	 *
	 * @param string $name
	 * @return $this
	 * @throws \InvalidArgumentException When the column does not exist
	 */
	public function addColumn(string $name): Filters
	{
		/**
		 * @var class-string<Entity> $cls;
		 */
		$cls = $this->entityCls;

		$col = $cls::getMapping()->getColumn($name);

		if(!$col) {
			throw new \InvalidArgumentException("Column '$name' does not exist in " . $cls);
		}

		if($col->dbType == "datetime") {
			// datetime columns are stored in UTC so the user's input must be converted
			return $this->addDateTime($name, function (Criteria $c, $comparator, $value) use ($name) {
				$c->andWhere($name, $comparator, $value);
			});
		} else if(in_array($col->dbType, ["localdatetime", "date"])) {
			return $this->addDate($name, function (Criteria $c, $comparator, $value) use ($name) {
				$c->andWhere($name, $comparator, $value);
			});
		} else {
			$this->add($name, function (Criteria $c, $value) use ($name) {
				$c->andWhere($name, '=', $value);
			});
			$this->filters[strtolower($name)]['schema'] = self::columnSchema($col);
			return $this;
		}
	}

	/**
	 * JSON schema for the value of a column filter. An array value is matched with IN and null with IS NULL.
	 *
	 * @param Column $col
	 * @return array
	 */
	private static function columnSchema(Column $col): array
	{
		$type = $col->getJsonType() ?? 'string';

		$single = ['type' => $col->nullAllowed ? [$type, 'null'] : $type];

		return ['anyOf' => [$single, ['type' => 'array', 'items' => ['type' => $type]]]];
	}

	/**
	 * Describe the filter that was added last. The description is used in the API documentation and in
	 * {@see toJsonSchema()}.
	 *
	 * The description is not translated. It's developer documentation for API clients, so write it in English.
	 *
	 * @example
	 * ```
	 * $filters->add('addressBookId', function(Criteria $c, $value) {
	 *   $c->andWhere('addressBookId', '=', $value);
	 * })->describe("Only contacts in the given address book", ['type' => 'integer']);
	 * ```
	 *
	 * @param string $description What the filter does and what the value means
	 * @param array|null $schema JSON schema of the filter value. When null the schema derived from the filter type is kept.
	 * @return $this
	 * @throws LogicException When no filter was added yet
	 */
	public function describe(string $description, ?array $schema = null): Filters
	{
		if (!isset($this->lastAdded)) {
			throw new LogicException("Add a filter before calling describe()");
		}

		$this->filters[$this->lastAdded]['description'] = $description;
		if (isset($schema)) {
			$this->filters[$this->lastAdded]['schema'] = $schema;
		}

		return $this;
	}

	/**
	 * Get the JSON schema (draft 2020-12) of the "filter" argument of the Foo/query method.
	 *
	 * The filter is either a FilterCondition object with the filters below or a FilterOperator object
	 * with "operator" and "conditions" as described in the JMAP spec. The definitions are in "$defs" and referenced
	 * with "#/$defs/...". When embedding this schema in another schema, move "$defs" to the root of that schema.
	 *
	 * Each filter property has an "x-filterType" keyword with the filter type (generic, text, number, date or datetime).
	 *
	 * @link https://jmap.io/spec-core.html#query
	 * @param bool $withTypeSyntax Append the value syntax of the filter type ({@see getTypeSyntax()}) to each description.
	 *   Pass false when the syntax is documented once elsewhere.
	 * @return array
	 */
	public function toJsonSchema(bool $withTypeSyntax = true): array
	{
		$properties = [];
		foreach ($this->filters as $filter) {
			$properties[$filter['name']] = $this->filterSchema($filter, $withTypeSyntax);
		}
		ksort($properties, SORT_STRING | SORT_FLAG_CASE);

		return [
			'$schema' => 'https://json-schema.org/draft/2020-12/schema',
			'$ref' => '#/$defs/Filter',
			'$defs' => [
				'Filter' => [
					'anyOf' => [
						['$ref' => '#/$defs/FilterOperator'],
						['$ref' => '#/$defs/FilterCondition']
					]
				],
				'FilterOperator' => [
					'type' => 'object',
					'description' => 'Combines filters. NOT matches when none of the conditions match.',
					'properties' => [
						'operator' => ['enum' => ['AND', 'OR', 'NOT']],
						'conditions' => ['type' => 'array', 'items' => ['$ref' => '#/$defs/Filter']]
					],
					'required' => ['operator', 'conditions'],
					'additionalProperties' => false
				],
				'FilterCondition' => [
					'type' => 'object',
					'description' => 'All given filters must match. Filter names are case insensitive.',
					'properties' => (object) $properties,
					'additionalProperties' => false
				]
			]
		];
	}

	/**
	 * Get the value syntax per filter type: text, number, date and datetime. Generic filters have no fixed syntax.
	 *
	 * @return array<string, string> Filter type => syntax description
	 */
	public static function getTypeSyntax(): array
	{
		$date = 'A date with an optional comparator (>, >=, <, <=) or a range "2025-01-01..2025-01-31". ' .
			'Relative dates like "now" and "-1 week" are supported. A date without time in a range end includes that whole day.';

		return [
			'text' => 'Matched with SQL LIKE, use % as wildcard. An array matches any of the values.',
			'number' => 'A number with an optional comparator (>, >=, <, <=, !=) or a range "1..10".',
			'date' => $date,
			'datetime' => $date . ' Times are in the time zone of the user.'
		];
	}

	/**
	 * JSON schema of a single filter value
	 */
	private function filterSchema(array $filter, bool $withTypeSyntax): array
	{
		$typeSchema = match ($filter['type']) {
			'text' => ['anyOf' => [['type' => 'string'], ['type' => 'array', 'items' => ['type' => 'string']]]],
			'number' => ['type' => ['number', 'string']],
			'date', 'datetime' => ['type' => ['string', 'null']],
			default => []
		};

		$schema = $filter['schema'] ?? $typeSchema;

		$syntax = $withTypeSyntax ? (self::getTypeSyntax()[$filter['type']] ?? '') : '';
		$description = trim(($filter['description'] ?? '') . ' ' . $syntax);
		if ($description !== '') {
			$schema['description'] = $description;
		}

		if ($filter['default'] !== self::NO_DEFAULT && (is_scalar($filter['default']) || is_array($filter['default']) || $filter['default'] === null)) {
			$schema['default'] = $filter['default'];
		}

		$schema['x-filterType'] = $filter['type'];

		return $schema;
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

		$this->checkValues($query, $filter);

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
	 * Check the given filter values against the schema of the filter, set with {@see describe()} or {@see addColumn()}.
	 *
	 * Only the types are checked, leniently: an integer may be a string of digits (JMAP IDs are strings), a string
	 * may be a number and null is always allowed. "enum" isn't enforced. Filters without a schema and the defaults
	 * of filters aren't checked.
	 *
	 * @param Query $query
	 * @param array $filter A FilterCondition or a FilterOperator
	 * @throws InvalidArguments When a value doesn't match the schema of its filter
	 */
	private function checkValues(Query $query, array $filter): void
	{
		if (isset($filter['conditions']) && isset($filter['operator'])) {
			foreach ($filter['conditions'] as $condition) {
				if (is_array($condition)) {
					$this->checkValues($query, $condition);
				}
			}
			return;
		}

		foreach ($filter as $name => $value) {
			$config = $this->filters[strtolower($name)] ?? null;
			if (!isset($config['schema']) || $value === null) {
				continue;
			}

			if (!self::matchesSchema($value, $config['schema'])) {
				throw new InvalidArguments("Invalid value for filter '" . $config['name'] . "' of entity '" .
					$query->getModel()::entityType()->getName() . "'. Expected: " . json_encode(self::stripDescriptions($config['schema'])));
			}
		}
	}

	/**
	 * Whether a value matches a JSON schema. Supports "anyOf", "type", "items", "properties" and "required".
	 * Other keywords are ignored.
	 */
	private static function matchesSchema(mixed $value, array $schema): bool
	{
		if (isset($schema['anyOf'])) {
			foreach ($schema['anyOf'] as $sub) {
				if (self::matchesSchema($value, $sub)) {
					return true;
				}
			}
			return false;
		}

		if (isset($schema['type'])) {
			$matchesType = false;
			foreach ((array) $schema['type'] as $type) {
				if (self::matchesType($value, $type)) {
					$matchesType = true;
					break;
				}
			}
			if (!$matchesType) {
				return false;
			}
		}

		if (!is_array($value)) {
			return true;
		}

		if (isset($schema['items']) && array_is_list($value)) {
			foreach ($value as $item) {
				if (!self::matchesSchema($item, $schema['items'])) {
					return false;
				}
			}
		}

		foreach ($schema['required'] ?? [] as $key) {
			if (!array_key_exists($key, $value)) {
				return false;
			}
		}

		foreach ($schema['properties'] ?? [] as $key => $sub) {
			if (isset($value[$key]) && !self::matchesSchema($value[$key], $sub)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Whether a value matches a JSON type, leniently: values a JSON client sends for that type also match
	 */
	private static function matchesType(mixed $value, string $type): bool
	{
		return match ($type) {
			'null' => $value === null,
			'integer' => is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', $value) === 1),
			'number' => is_int($value) || is_float($value) || (is_string($value) && is_numeric($value)),
			'string' => is_string($value) || is_int($value) || is_float($value),
			'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1'], true),
			'array' => is_array($value) && array_is_list($value),
			'object' => is_array($value) && ($value === [] || !array_is_list($value)),
			default => true
		};
	}

	/**
	 * The schema without descriptions, for error messages
	 */
	private static function stripDescriptions(array $schema): array
	{
		unset($schema['description']);
		foreach ($schema as $key => $sub) {
			if (is_array($sub)) {
				$schema[$key] = self::stripDescriptions($sub);
			}
		}
		return $schema;
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
		$this->lastAdded = strtolower($name);
		
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
		$this->lastAdded = strtolower($name);

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
		$this->lastAdded = strtolower($name);

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
		$this->lastAdded = strtolower($name);
		
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
	 * @example
	 * ```
	 * $range = Filters::parseDateRange('now..2025-01-01');
	 * foreach($range as $r) {
	 *   $query->andWhere('due', $r['comparator'], $r['date']);
	 * }
	 *
	 * ```
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
				return [['comparator' => '=', 'date' => null]];
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
