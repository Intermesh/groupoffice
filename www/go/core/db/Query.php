<?php

namespace go\core\db;

use go\core\App;
use go\core\db\Criteria;
use PDO;
use ReflectionClass;

/**
 * The Query class to select database records
 * 
 * @example 
 * ```
 * $query = (new Query())
 * 					->select('a.id, a.name')
 * 					->from('test_a', 'a')
 * 					->where('id', '=', 1)
 * 					->limit(1)
 * 					->offset(0)
 * 					->orderBy(['id' => 'ASC']);
 * 
 * 	$stmt = $query->execute();
 * 
 * 	$record = $stmt->fetch();
 * ```
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Query extends Criteria implements \IteratorAggregate, \JsonSerializable, \go\core\data\ArrayableInterface {

	private $tableAlias;
	private $distinct;
	private $select = [];
	private $orderBy = [];
	private $groupBy = [];
	private $having = [];
	private $limit;
	private $offset = 0;
	protected $joins = [];
	private $fetchMode;
	private $forUpdate;
	private $tableName;

	public function getTableAlias() {
		return isset($this->tableAlias) ? $this->tableAlias : 't';
	}

	public function getHaving() {
		return $this->having;
	}

	public function getDistinct() {
		return $this->distinct;
	}

	public function getSelect() {
		if (empty($this->select)) {
			$this->select = ['*'];
		}
		return $this->select;
	}

	public function getOrderBy() {
		return $this->orderBy;
	}

	public function getGroupBy() {
		return $this->groupBy;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function getOffset() {
		return $this->offset;
	}

	public function getJoins() {
		return $this->joins;
	}

	public function getFetchMode() {
		if (!isset($this->fetchMode)) {
			return [PDO::FETCH_ASSOC];
		}
		return $this->fetchMode;
	}

	public function getFrom() {
		return $this->tableName;
	}

	/**
	 * Set the table name and alias
	 * 
	 * @param string $tableName
	 * @param string $tableAlias Will default to 't' if not set
	 * @return $this
	 */
	public function from($tableName, $tableAlias = null) {
		$this->tableName = $tableName;
		$this->tableAlias = $tableAlias;

		return $this;
	}

	/**
	 * Set the PDO fetch mode
	 * 
	 * By default the PDO::FETCH_ASSOC is used.
	 * 
	 * The arg1 and arg2 param depends on the $mode argument. See the PHP documentation:
	 * 
	 * {@see http://php.net/manual/en/pdostatement.setfetchmode.php}
	 * 
	 * @param int $mode
	 * @param mixed $arg1 
	 * @param mixed $arg2
	 * @return static
	 */
	public function fetchMode($mode, $arg1 = null, $arg2 = null) {
		$this->fetchMode = [$mode];

		if (isset($arg1)) {
			$this->fetchMode[] = $arg1;

			if (isset($arg2)) {
				$this->fetchMode[] = $arg2;
			}
		}

		return $this;
	}

	/**
	 * Select a single column or count(*) for example.
	 * 
	 * Shortcut for:
	 * $query->fetchMode(\PDO::FETCH_COLUMN,0)->select($select)
	 * 
	 * @param string $select
	 * @return static
	 */
	public function selectSingleValue($select) {
		return $this->fetchMode(PDO::FETCH_COLUMN, 0)->select($select, false);
	}

	/**
	 * Set the distinct select option
	 *
	 * @param boolean $useDistinct
	 * @return static
	 */
	public function distinct($useDistinct = true) {
		$this->distinct = $useDistinct;
		return $this;
	}

	/**
	 * Merge this with another Query object.
	 *
	 * @param Query $query
	 * @return static
	 */
	public function mergeWith(Query $query) {

		$reflection = new ReflectionClass(Query::class);

		$props = $reflection->getProperties();

		foreach ($props as $prop) {
			$key = $prop->getName();
			$value = $query->$key;

			if (!isset($value)) {
				continue;
			}

			if (is_array($value)) {
				$this->$key = isset($this->$key) ? array_merge($this->$key, $value) : $value;
			} else {
				$this->$key = $value;
			}
		}

		return $this;
	}

	/**
	 * Set the selected fields for the select query.
	 *
	 * Remember the default table alias is 't'.
	 *
	 * @param string|array $select
	 * @return static
	 */
	public function select($select = '*', $append = false) {
		if (!is_array($select)) {
			$select = [$select];
		}

		$this->select = $append ? array_merge($this->select, $select) : $select;

		return $this;
	}

	/**
	 * Set the main table alias.
	 * 
	 * @param string $alias
	 * @return $this
	 */
	public function tableAlias($alias) {
		$this->tableAlias = $alias;
		return $this;
	}

	/**
	 * Set sort order
	 *
	 * @param array $by eg. ['field1'=>'ASC','field2'=>'DESC', new go\core\db\Expression('ISNULL(column) ASC')] for multiple values	 
	 * 
	 * @return static
	 */
	public function orderBy(array $by, $append = false) {
		$this->orderBy = $append ? array_merge($this->orderBy, $by) : $by;
		return $this;
	}

	/**
	 * Adds a group by clause.
	 *
	 * @param array $columns eg. array('t.id');
	 * @return static
	 */
	public function groupBy(array $columns, $append = false) {
		$this->groupBy = $append ? array_merge($this->groupBy, $columns) : $columns;
		return $this;
	}

	/**
	 * Adds a having clause. 
	 *
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function having($condition, $operator = 'AND') {
		$this->having[] = [$operator, $this->normalizeCondition($condition)];
		return $this;
	}

	/**
	 * Adds a having clause group with AND.
	 * 
	 * {@see having()}
	 * 
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function andHaving($condition) {
		return $this->having($condition);
	}

	/**
	 * Adds a having clause group with OR.
	 * 
	 * {@see having()}
	 * 
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function orHaving($condition) {
		return $this->having($condition, 'OR');
	}

	/**
	 * Join a table
	 *
	 * @Example
	 * ```````````````````````````````````````````````````````````````````````````
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a', "a")
	 * 				->join("test_b", "b", "a.id = b.id")
	 * 				->where('id', '=', 1);
	 * 
	 * $stmt = $query->execute();
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * @Example Join a query
	 * 
	 * ```
	 * $query = (new Query())
	 * 				->select('*')
	 * 				->from('test_a', "a")
	 * 				->join(
	 * 								(new Query)
	 * 								->select('id')
	 * 								->from("test_b", 'sub_b'), "subjoin", "subjoin.id = a.id"
	 * 				);						
	 * ```
	 *
	 * @param string|\go\core\db\Query $tableName The record class name or sub query to join
	 * @param string $joinTableAlias Leave empty for none.
	 * @param Criteria|array|string $on The criteria used in the ON clause {@see Criteria::normalize()}
	 * @param string $type The join type. INNER, LEFT or RIGHT
	 * @return static
	 */
	public function join($tableName, $joinTableAlias, $on, $type = 'INNER') {

		$this->joins[] = [
				'src' => $tableName,
				'on' => Criteria::normalize($on),
				'joinTableAlias' => $joinTableAlias,
				'type' => $type
		];

		return $this;
	}

	/**
	 * Skip this number of records
	 *
	 * @param int $offset
	 * @return static
	 */
	public function offset($offset = 0) {
		$this->offset = (int) $offset;
		return $this;
	}

	/**
	 * Limit the number of models returned
	 *
	 * @param int $limit
	 * @return static
	 */
	public function limit($limit = 0) {
		$this->limit = (int) $limit;
		return $this;
	}

	public function __toString() {
		//todo
		//return $this->createCommand()->toString();
	}
	
	/**
	 *
	 * @var Connection
	 */
	private $dbConn;
	
	/**
	 * Set database connection.
	 * 
	 * Default to App::get()->getDbConnection();
	 * 
	 * @param \go\core\db\Connection $conn
	 * @return $this
	 */
	public function setDbConnection(Connection $conn) {
		$this->dbConn = $conn;
		
		return $this;
	}
	
	private function getDbConnection() {
		if(!isset($this->dbConn)) {
			$this->dbConn = App::get()->getDbConnection();
		}
		
		return $this->dbConn;
	}

	/**
	 * Executes the query and returns the statement
	 * 
	 * @return Statement
	 */
	public function execute() {
		$statement = $this->getDbConnection()->select($this);
		if (!$statement->execute()) {
			return false;
		}
		return $statement;
	}
	
	/**
	 * Executes the query and returns a single object
	 * 
	 * @return mixed
	 */
	public function single() {		
		return $this->offset(0)
						->limit(1)
						->execute()
						->fetch();
	}
	
	/**
	 * Get all records as an array
	 * 
	 * @return mixed[]
	 */
	public function all() {
		return $this->execute()->fetchAll();
	}

	/**
	 * Build the query and return SQL and bind parameters
	 * 
	 * @return array eg ['sql' => 'select...', 'params' => []]
	 */
	public function build() {
		$queryBuilder = new QueryBuilder();
		return $queryBuilder->buildSelect($this);
	}

	/**
	 * Lock rows for update
	 * 
	 * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
	 * @param boolean $value
	 * @return $this
	 */
	public function forUpdate($value = true) {
		$this->forUpdate = $value;
		return $this;
	}

	public function getForUpdate() {
		return $this->forUpdate;
	}

	public function getIterator() {
		return $this->execute();
	}

	public function jsonSerialize() {
		return $this->toArray();
	}

	public function toArray($properties = null) {
		$arr = [];
		foreach($this->execute() as $entity) {
			if($entity instanceof \go\core\data\ArrayableInterface) {
				$arr[] = $entity->toArray($properties);
			} else
			{
				$arr[] = $entity;
			}
		}
		
		return $arr;
	}

}
