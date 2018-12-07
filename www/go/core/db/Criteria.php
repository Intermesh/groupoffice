<?php
namespace go\core\db;

/**
 * Create "where", "having" or "join on" part of the query for {@see \go\core\db\Query}
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Criteria {
	
	private $where = [];	
	
	/**
	 * Key value array of bind parameters.
	 * 
	 * @var array eg. ['paramTag' => ':someTag', 'value' => 'Some value', 'pdoType' => PDO::PARAM_STR]
	 */
	private $bindParameters = [];
	
	/**
	 * Creates a new Criteria or Query object from different input:
	 * 
	 * * null => new Criteria();
	 * * Array: ['key'= > value] = (new Criteria())->where(['key'= > value]);
	 * * String: "col=:val" = (new Criteria())->where("col=:val"); 
	 * * A Query object is returned as is.
	 * 
	 * @param \go\core\db\Criteria $criteria
	 * @return \go\core\db\Criteria
	 * @throws \Exception
	 */
	public static function normalize($criteria = null) {
		if (!isset($criteria)) {
			return new static;
		}
		
		if($criteria instanceof static) {
			return $criteria;
		}
		
		if(is_object($criteria)) {
			throw new \Exception("Invalid query object passed: ".get_class($criteria).". Should be an go\core\orm\Query object, array or string.");
		}
		
		return (new static)->where($criteria);
	}
	
	/**
	 * The where conditions
	 * 
	 * Use {@see where()} to add new.
	 * 
	 * @return array 
	 */	
	public function getWhere() {
		return $this->where;
	}
	
	/**
	 * Key value array of bind parameters.
	 * 
	 * @return array eg. ['paramTag' => ':someTag', 'value' => 'Some value', 'pdoType' => PDO::PARAM_STR]
	 */
	public function getBindParameters() {
		return $this->bindParameters;
	}	

	/**
	 * Set where parameters. 
	 * 
	 * Basic usage
	 * ===========
	 * 
	 * There are 3 ways to use this function:
	 * 
	 * 1. Specify column, operator and value.
	 * 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a')
	 * 				->where('id', '=', 1)
	 * 				
	 * ```
	 * 
	 * 2. Provide a key value array with column name value. 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a')
	 * 				->where(['id' => 1, 'name' => 'merijn']); //WHERE id=1 and name='merijn'
	 * ```
	 * 
	 * 3. Provide a raw string. 
	 * Note that you MUST use {@see bind()} for binding values to prevent SQL 
	 * injection. Do not concatenate values.
	 * 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a')
	 * 				->where('id = :id')
	 * 				->bind(':id', 1);
	 * ```
	 * 
	 * Parameter grouping
	 * ==================
	 * 
	 * You can group parameters by passing another Criteria object:
	 * 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a')
	 * 				->where('id', '=', 1)
	 * 				->andWhere(
	 * 								(new Criteria())
	 * 								->where("id", "=", 2)
	 * 								->orWhere("id", '>', 1)
	 * 								);
	 * ```
	 * 
	 * Sub queries
	 * ===========
	 * 
	 * The query builder also handles sub queries.
	 * 
	 * An IN sub query:
	 * ```	 
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a', "a")
	 * 				->join("test_b", "b", "a.id = b.id")
	 * 				->where('id', 'IN', 
	 * 							(new Query)
	 * 								->select('id')
	 * 								->from("test_b", 'sub_b')
	 * 				);
	 * ````
	 * 
	 * An EXISTS sub query:
	 * 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a', "a")
	 * 				->whereExists(
	 * 					(new Query)
	 * 					->select('id')
	 * 					->from("test_b", 'sub_b')
	 * 					->where("sub_b.id = a.id")
	 *  );
	 * ```
	 * 
	 * @param string|array|Criteria $condition
	 * @param string $comparisonOperator =, !=, IN, NOT IN etc. Defaults to '=' OR 'IN' (for arrays)
	 * @param mixed $value
	 * 
	 * @return static
	 */
	public function where($condition, $comparisonOperator = null, $value = null) {
		return $this->andWhere($condition, $comparisonOperator, $value);
	}
	
	protected function internalWhere($condition, $comparisonOperator, $value, $logicalOperator) {				
		if(!isset($comparisonOperator) && (is_string($condition) || $condition instanceof Criteria)) {
			//condition is raw string
			$this->where[] = ["tokens", $logicalOperator, $condition];
			return $this;
		}		
		
		if(isset($comparisonOperator)) {
			$comparisonOperator = preg_replace("/[\s]+/",' ',strtoupper($comparisonOperator));
		} else
		{
			$comparisonOperator = '=';
		}
		
		if(is_array($condition)) {
			foreach($condition as $colName => $value) {
				if(is_array($value)) {
					$comparisonOperator = $this->convertComparisonOperatorForArray($value, $comparisonOperator);
				}
				$this->where[] = ["column", $logicalOperator, $colName, $comparisonOperator, $value];
			}			
		} else {
			if(is_array($value)) {
				$comparisonOperator = $this->convertComparisonOperatorForArray($value, $comparisonOperator);
			}
			$this->where[] = ["column", $logicalOperator, $condition, $comparisonOperator, $value];
		}
		
		return $this;
	}
	
	private function convertComparisonOperatorForArray($value, $comparisonOperator) {
		switch($comparisonOperator) {
			case 'IN':
			case '=':
				return 'IN';
			
			case 'NOT IN':
			case '!=':
				return 'NOT IN';
			
			default:
				throw new Exception("Illegal comparison operator '" . $comparisonOperator . "' for array value");
				
		}
	}
	
	protected function internalWhereExists(Query $subQuery, $not = false, $logicalOperator = "AND") {
		$this->where[] = ["tokens", $logicalOperator, $not ? "NOT EXISTS" : "EXISTS", $subQuery];
		return $this;
	}
	
	public function whereExists(Query $subQuery, $not = false) {
		return $this->andWhereExists($subQuery, $not);
	}
	
	public function andWhereExists(Query $subQuery, $not = false) {
		return $this->internalWhereExists($subQuery, $not);
	}
	
	public function orWhereExists(Query $subQuery, $not = false) {
		return $this->internalWhereExists($subQuery, $not , "OR");
	}

	/**
	 * Concatenate where condition with AND
	 * 
	 * {@see where()}
	 * 
	 * @param String|array|Criteria $column
	 * @return static
	 */
	public function andWhere($column, $operator = null, $value = null) {
		return $this->internalWhere($column, $operator, $value, 'AND');
	}
	
	/**
	 * Concatenate where condition with OR
	 * 
	 * {@see where()}
	 * 
	 * @param String|array|Criteria $condition
	 * @return static
	 */
	public function orWhere($column, $operator = null, $value = null) {
		return $this->internalWhere($column, $operator, $value, 'OR');
	}

	/**
	 * Add a parameter to bind to the SQL query
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 * $query->where("userId = :userId")
	 *   ->bind(':userId', $userId, \PDO::PARAM_INT);
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * OR as array:
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 * $query->where("name = :name1 OR name = :name2")
	 *     ->bind([':name1' => 'Pete', ':name2' => 'John']);
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * @param string|array $tag eg. ":userId" or [':userId' => 1]
	 * @param mixed $value
	 * @param int $pdoType {@see \PDO} Autodetected based on the type of $value if omitted.
	 * @return static
	 */
	public function bind($tag, $value = null, $pdoType = null) {
		
		if(is_array($tag)) {
			foreach($tag as $key => $value) {
				$this->bind($key, $value);
			}			
			return $this;
		}		
		
		if (!isset($pdoType)) {			
			$pdoType = Utils::getPdoParamType($value);			
		}
		
		$this->bindParameters[] = ['paramTag' => $tag, 'value' => $value, 'pdoType' => $pdoType];
		
		return $this;
	}
}
