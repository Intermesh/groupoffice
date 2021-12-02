<?php

namespace go\core\db;

use Exception;
use go\core\App;
use go\core\data\ArrayableInterface;
use IteratorAggregate;
use JsonSerializable;
use PDO;
use PDOException;

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
 * // Query objects can be stringified for debugging:
 * echo $query;
 * 
 * $stmt = $query->execute();
 * 
 * $record = $stmt->fetch();
 * ```
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Query extends Criteria implements IteratorAggregate, JsonSerializable, ArrayableInterface {

	private $tableAlias;
	private $distinct;
	private $select = [];
	private $orderBy = [];
	private $unionOrderBy = [];
	private $groupBy = [];
	private $having = [];
	private $limit;
	private $unionLimit;
	private $offset = 0;
	private $unionOffset = 0;
	protected $joins = [];
	private $fetchMode;
	private $forUpdate;
	private $tableName;
	private $calcFoundRows = false;
	private $noCache = false;

	private $indexHintList;

	public function getIndexHintList() {
		return $this->indexHintList;
	}

	public function getTableAlias(): string
	{
		return $this->tableAlias ?? 't';
	}

	public function getHaving(): array
	{
		return $this->having;
	}

	public function getDistinct() {
		return $this->distinct;
	}

	public function getSelect(): array
	{
		if (empty($this->select)) {
			$this->select = ['*'];
		}
		return $this->select;
	}

	public function getOrderBy(): array
	{
		return $this->orderBy;
	}

	public function getGroupBy(): array
	{
		return $this->groupBy;
	}

	public function getLimit() {
		return $this->limit;
	}

	public function getOffset(): int
	{
		return $this->offset;
	}

	public function getJoins(): array
	{
		return $this->joins;
	}
	
	public function getUnions(): array
	{
		return $this->unions;
	}
	
	public function getUnionOffset(): int
	{
		return $this->unionOffset;
	}
	
	public function getUnionLimit() {
		return $this->unionLimit;
	}
	
	public function getUnionOrderBy(): array
	{
		return $this->unionOrderBy;
	}

	public function getCalcFoundRows(): bool
	{
		return $this->calcFoundRows;
	}

	/**
	 * When calcFoundERows() is used this function will return the total found rows.
	 *
	 * @return int
	 */
	public function foundRows(): int
	{
		return (int) go()->getDbConnection()->query("SELECT FOUND_ROWS()")->fetch(PDO::FETCH_COLUMN, 0);
	}

	public function getNoCache(): bool
	{
		return $this->noCache;
	}

	public function getFetchMode(): array
	{
		if (!isset($this->fetchMode)) {
			return [PDO::FETCH_ASSOC];
		}
		return $this->fetchMode;
	}

	public function getFrom() {
		return $this->tableName;
	}

	/**
	 * Set the table name / subquery source and alias
	 * 
	 * @param string|Query $source Table name or Subquery
	 * @param string|null $tableAlias Will default to 't' if not set
	 * @return $this
	 */
	public function from($source, string $tableAlias = null): Query
	{
		$this->tableName = $source;
		return $this->tableAlias($tableAlias);
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
	public function fetchMode(int $mode, $arg1 = null, $arg2 = null): Query
	{
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
	public function selectSingleValue(string $select): Query
	{
		return $this->fetchMode(PDO::FETCH_COLUMN, 0)->select($select);
	}

	/**
	 * Set the distinct select option
	 *
	 * @param boolean $useDistinct
	 * @return static
	 */
	public function distinct(bool $useDistinct = true): Query
	{
		$this->distinct = $useDistinct;
		return $this;
	}

	/**
	 * Merge this with another Query object.
	 *
	 * @param Query $query
	 * @return static
	 */
	public function mergeWith(Query $query): Query
	{

		//Used to generate propnames
		// $reflection = new ReflectionClass(Query::class);
		// $props = $reflection->getProperties();
		// $propNames = array_map(function($p){return $p->getName();}, $props);
		// var_export($propNames);
		// exit();

		$propNames = array ( 
			'bindParameters',
			'where',
			'tableAlias', 
			'distinct',
			'select', 
			'orderBy', 
			'unionOrderBy',
			'groupBy', 
			'having', 
			'limit', 
			'unionLimit', 
			'offset', 
			'unionOffset', 
			'joins', 
			'forUpdate', 
			'tableName', 
			'unions'
			 );

		foreach ($propNames as $key) {
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
	 * @param string|array $select Pass null to reset.
	 * @return static
	 */
	public function select($select = '*', $append = false): Query
	{

		if(!isset($select)) {
			$this->select = [];
			return $this;
		}

		if (!is_array($select)) {
			$select = [$select];
		}

		$this->select = $append ? array_merge($this->select, $select) : $select;

		return $this;
	}

	/**
	 * Use SQL_CALC_FOUND_ROWS
	 * 
	 * @param bool $v
	 * 
	 * @return static
	 */
	public function calcFoundRows(bool $v = true): Query
	{
		$this->calcFoundRows = $v;

		return $this;
	}

	/**
	 * Use SQL_NO_CACHE
	 *
	 * @param bool $v
	 *
	 * @return static
	 */
	public function noCache(bool $v = true): Query
	{
		$this->noCache = $v;

		return $this;
	}
	
	private $unions = [];
	
	/**
	 * Create a union query
	 * 
	 * Calling limit(), offset() and orderBy() after the union will apply to the 
	 * global union scope and not the individual query.
	 * 
	 * @param Query $query
	 * @return $this
	 */
	public function union(Query $query): Query
	{
		$this->unions[] = $query;
		
		return $this;
	}

	/**
	 * Set the main table alias.
	 * 
	 * @param string|null $alias
	 * @return $this
	 */
	public function tableAlias(?string $alias): Query
	{
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
	public function orderBy(array $by, $append = false): Query
	{
		if(empty($this->unions)) {
			$this->orderBy = $append ? array_merge($this->orderBy, $by) : $by;
		} else
		{
			$this->unionOrderBy = $append ? array_merge($this->unionOrderBy, $by) : $by;
		}
		return $this;
	}

	/**
	 * Adds a group by clause.
	 *
	 * @param array $columns eg. array('t.id');
	 * @return static
	 */
	public function groupBy(array $columns, $append = false): Query
	{
		$this->groupBy = $append ? array_merge($this->groupBy, $columns) : $columns;
		return $this;
	}

	/**
	 * Adds a having clause. 
	 *
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function having($condition, $operator = null, $value = null): Query
	{
		return $this->andHaving($condition, $operator, $value);
	}

	/**
	 * Adds a having clause group with AND.
	 * 
	 * {@see having()}
	 * 
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function andHaving($condition, $operator = null, $value = null): Query
	{
		$this->having[] = $this->internalWhere($condition, $operator, $value, 'AND');
		return $this;
	}

	/**
	 * Adds a having clause group with OR.
	 * 
	 * {@see having()}
	 * 
	 * @param Criteria|array|string $condition {@see Criteria::normalize()}
	 * @return static
	 */
	public function orHaving($condition, $operator = null, $value = null): Query
	{
		$this->having[] = $this->internalWhere($condition, $operator, $value, 'OR');
		return $this;
	}

  /**
   * Join a table
   *
   * @Example
   * ```````````````````````````````````````````````````````````````````````````
   * $query = (new Query())
   *        ->select('*')
   *        ->from('test_a', "a")
   *        ->join("test_b", "b", "a.id = b.id")
   *        ->where('id', '=', 1);
   *
   * $stmt = $query->execute();
   * ```````````````````````````````````````````````````````````````````````````
   *
   * @Example Join a query
   *
   * ```
   * $query = (new Query())
   *        ->select('*')
   *        ->from('test_a', "a")
   *        ->join(
   *                (new Query)
   *                ->select('id')
   *                ->from("test_b", 'sub_b'), "subjoin", "subjoin.id = a.id"
   *        );
   * ```
   *
   * @param string|Query $tableName The record class name or sub query to join
   * @param mixed $joinTableAlias Leave empty for none.
   * @param Criteria|array|string $on The criteria used in the ON clause {@see Criteria::normalize()}
   * @param string $type The join type. INNER, LEFT or RIGHT
   * @return static
   */
	public function join($tableName, $joinTableAlias, $on, string $type = 'INNER', $indexHint = null): Query
	{

		$this->joins[] = [
				'src' => $tableName,
				'on' => Criteria::normalize($on),
				'joinTableAlias' => $joinTableAlias,
				'type' => $type,
				'indexHint' => $indexHint
		];

		return $this;
	}

	/**
	 * Specify index hints for mysql
	 *
	 * @see https://dev.mysql.com/doc/refman/8.0/en/index-hints.html
	 * @param string $hintList eg. "USE INDEX (col1_index,col2_index)"
	 * @return $this
	 */
	public function useIndex(string $hintList): Query
	{
		$this->indexHintList = $hintList;

		return $this;
	}
	
	/**
	 * Check if table is joined
	 * 
	 * @param string $tableName
	 * @param string|null $joinTableAlias If given the alias of the existing join must match too.
	 * @return boolean
	 */
	public function isJoined(string $tableName, string $joinTableAlias = null): bool
	{
		foreach($this->joins as $join) {
			if($join['src'] != $tableName) {
				continue;
			}
			
			if(!isset($joinTableAlias)) {
				return true;
			}
			
			if($joinTableAlias == $join['joinTableAlias']) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Remove joined table
	 *
	 * @param string $tableName
	 * @param string|null $joinTableAlias If given the alias of the existing join must match too.
	 * @return static
	 */
	public function removeJoin(string $tableName, string $joinTableAlias = null): Query
	{
		$new = [];
		foreach($this->joins as $join) {
			if($join['src'] == $tableName && (!isset($joinTableAlias) || $joinTableAlias == $join['joinTableAlias'])) {
				continue;
			}

			$new[] = $join;
		}

		$this->joins = $new;

		return $this;
	}

	/**
	 * Skip this number of records
	 *
	 * @param int $offset
	 * @return static
	 */
	public function offset(int $offset = 0): Query
	{
		if(empty($this->unions)) {
			$this->offset = $offset;
		} else
		{
			$this->unionOffset = $offset;
		}
		return $this;
	}

	/**
	 * Limit the number of models returned
	 *
	 * @param int $limit
	 * @return static
	 */
	public function limit(int $limit = 0): Query
	{
		if(empty($this->unions)) {
			$this->limit = $limit;
		} else
		{			
			$this->unionLimit = $limit;
		}
		return $this;
	}

	public function __toString() {
		$queryBuilder = new QueryBuilder($this->getDbConnection());
		$build = $queryBuilder->buildSelect($this);
		
		return $queryBuilder->debugBuild($build);
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
	 * @param Connection $conn
	 * @return $this
	 */
	public function setDbConnection(Connection $conn): Query
	{
		$this->dbConn = $conn;
		
		return $this;
	}
	
	/**
	 * Get the database connection
	 * 
	 * @return Connection
	 */
	public function getDbConnection(): Connection
	{
		if(!isset($this->dbConn)) {
			$this->dbConn = App::get()->getDbConnection();
		}
		
		return $this->dbConn;
	}

	public function createStatement(): Statement
	{

		$queryBuilder = new QueryBuilder($this->getDbConnection());
		$build = $queryBuilder->buildSelect($this);
		$build['start'] = go()->getDebugger()->getTimeStamp();

		$stmt = $this->getDbConnection()->createStatement($build);
		call_user_func_array([$stmt, 'setFetchMode'], $this->getFetchMode());
		$stmt->setQuery($this);		

		return $stmt;
	}

  /**
   * Executes the query and returns the statement
   *
   * @return Statement Returns false on failure.
   * @throws PDOException
   */
	public function execute(): Statement
	{
		
		try {
			$stmt = $this->createStatement();
			/**
			 * @var Statement $stmt
			 */
			$stmt->execute();
		} catch(PDOException $e) {
			try {
				go()->error("SQL FAILED: " . $this->__toString());
			} catch(PDOException $e2) {
				go()->error("SQL FAILED AND FAILED TO BUILD STRING " . $e2->getMessage());
			}
			
			throw $e;
		}
		return $stmt;
	}
//
//	private $debug = false;
//
//	/**
//	 * Output query to debugger
//	 *
//	 * @return $this
//	 */
//	public function debug() {
//		$this->debug = true;
//
//		return $this;
//	}

  /**
   * Executes the query and returns a single object
   *
   * @return mixed|boolean The queries record, column or object. Returns false
   *   when nothing is found
   * @throws PDOException
   */
	public function single() {		
		return $this->offset()
						->limit(1)
						->execute()
						->fetch();
	}

  /**
   * Get all records as an array
   *
   * @return array
   * @throws PDOException
   */
	public function all() : array {
		return $this->execute()->fetchAll();
	}

	/**
	 * Build the query and return SQL and bind parameters
	 * 
	 * @return array eg ['sql' => 'select...', 'params' => []]
	 */
	public function build(): array
	{
		$queryBuilder = new QueryBuilder($this->getDbConnection());
		return $queryBuilder->buildSelect($this);
	}

	/**
	 * Lock rows for update
	 * 
	 * @link https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
	 * @param bool $value
	 * @return $this
	 */
	public function forUpdate(bool $value = true): Query
	{
		$this->forUpdate = $value;
		return $this;
	}

	public function getForUpdate() {
		return $this->forUpdate;
	}

  /**
   * Executes the query
   *
   * @return Statement
   * @throws Exception
   */
	public function getIterator(): Statement
	{
		return $this->execute();
	}

  /**
   * Serializes the query to JSON by executing it and fetching into an array
   *
   * @return array
   * @throws Exception
   */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

  /**
   * Convert all results of this query to arrays
   *
   * @param array|null $properties
   * @return array
   * @throws Exception
   */
  public function toArray(array $properties = null): array
  {
		$arr = [];
		foreach($this->execute() as $entity) {
			if($entity instanceof ArrayableInterface) {
				$arr[] = $entity->toArray($properties);
			} else
			{
				$arr[] = $entity;
			}
		}
		
		return $arr;
	}

}
