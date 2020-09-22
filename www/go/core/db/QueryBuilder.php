<?php

namespace go\core\db;

use Exception;
use go\core\db\Column;
use go\core\db\Criteria;
use go\core\util\ArrayUtil;

/**
 * QueryBuilder
 *
 * Builds or executes an SQL string with a {@see Query} object anmd {@see AbstractRecord}
 *
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class QueryBuilder {

	

	/**
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * The main table name
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * The main table alias
	 * 
	 * @var string
	 */
	protected $tableAlias;

	/**
	 * Key value array of parameters to bind to the SQL Statement
	 *
	 * Query::where() parameters will be put in here to bind.
	 *
	 * @var array[]
	 */
	private $buildBindParameters = [];

	/**
	 * To generate unique param tags for binding
	 * 
	 * @var int
	 */
	private static $paramCount = 0;

	/**
	 * Prefix of the bind parameter tag
	 * @var type
	 */
	private static $paramPrefix = ':go';

	/**
	 * Key value array with [tableAlias => Table()]
	 *
	 * Used to find the model that belongs to an alias to find column types
	 * @var Table[]
	 */
	protected $aliasMap = [];

	/**
	 *
	 * @var Table 
	 */
	private $table;


	/**
	 * @var Connection
	 */
	private $conn;

	public function __construct(Connection $conn) {
		$this->conn = $conn;
	}


  /**
   * Constructor
   *
   * @param string $tableName The table to operate on
   * @throws Exception
   */
	public function setTableName($tableName) {
		
		if(!isset($tableName)) {
			throw new \Exception("No from() table set for the select query");
			
		}
		$this->tableName = $tableName;
		$this->table = Table::getInstance($tableName, $this->conn);
	}

	/**
	 * Used when building sub queries for when aliases of the main query are used
	 * in the subquery.
	 *
	 * @param array $aliasMap
	 */
	public function mergeAliasMap($aliasMap) {
		$this->aliasMap = array_merge($this->aliasMap, $aliasMap);
	}

	/**
	 * Get the query parameters
	 *
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Get the name of the record this query builder is for.
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

  /**
   * @param $tableName
   * @param $data
   * @param array $columns
   * @param string $command
   * @return array
   * @throws Exception
   */
	public function buildInsert($tableName, $data, $columns = [], $command = "INSERT") {

		$this->reset();
		$this->setTableName($tableName);
		$this->aliasMap[$tableName] = Table::getInstance($this->tableName, $this->conn);

		$sql = $command . " ";

		$sql .= "INTO `{$this->tableName}` ";

		if ($data instanceof \go\core\db\Query) {			
			if(!empty($columns)) {
				$sql .= " (`" . implode("`, `", $columns ) . "`)\n";
			}
			
			$build = $data->build();

			$sql .= ' ' . $build['sql'];
			$this->buildBindParameters = array_merge($this->buildBindParameters, $build['params']);
		} else {
			if(ArrayUtil::isAssociative($data)) {
				$data = [$data];
			}
			if(empty($columns)) {
				reset($data);
				$columns = array_keys(current($data));
			}
			$sql .= " (\n\t`" . implode("`,\n\t`", $columns) . "`\n)\n" .
				"VALUES \n";

			foreach($data as $record) {
				$tags = [];
				foreach ($record as $colName => $value) {
					if(is_int($colName)) {
						$colName = $columns[$colName];
					}
					
					if($value instanceof Expression) {
						$tags[] = (string) $value;
					} else
					{				
						$paramTag = $this->getParamTag();
						$tags[] = $paramTag;
						$this->addBuildBindParameter($paramTag, $value, $this->tableName, $colName);
					}
				}

				$sql .= "(\n\t" . implode(",\n\t", $tags) . "\n), ";
			}

			$sql = substr($sql, 0, -2); //strip off last ', '
		}
		
		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	public function buildUpdate($tableName, $data, Query $query, $command = "UPDATE") {

		$this->reset();

		$this->setTableName($tableName);

		$this->query = $query;
		$this->buildBindParameters = $query->getBindParameters();
		$this->tableAlias = $this->query->getTableAlias();
		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName, $this->conn);

		if (is_array($data)) {
			$updates = [];
			foreach ($data as $colName => $value) {

				$tableAndCol = $this->splitTableAndColumn($colName);
				$colName = '`' . $tableAndCol[0] .'`.`'.$tableAndCol[1].'`';
				if($value instanceof Expression) {
					$updates[] = $colName . ' = ' . $value;
				} elseif($value instanceof Query) {
					$build = $value->build();

					$updates[] = $colName . ' = (' . $build['sql'] .')';
					
					$this->buildBindParameters = array_merge($this->buildBindParameters, $build['params']);
				} else
				{				
					$paramTag = $this->getParamTag();
					$updates[] = $colName . ' = ' . $paramTag;
					$this->addBuildBindParameter($paramTag, $value, $tableAndCol[0], $tableAndCol[1]);
				}
			}
			$set = implode(",\n\t", $updates);
		} else if ($data instanceof Expression) {
			$set = (string) $data;
		}
		
		$sql = $command . " `{$this->tableName}` `" . $this->tableAlias . "`";
		
		foreach ($this->query->getJoins() as $join) {
			$sql .= "\n" . $this->join($join, "");
		}
		
		$sql .= "\nSET\n\t" . $set;

		$where = $this->buildWhere($this->getQuery()->getWhere());

		if (!empty($where)) {
			$sql .= "\nWHERE " . $where;
		}

		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	public function buildDelete($tableName, Query $query) {

		$this->setTableName($tableName);
		$this->reset();
		$this->query = $query;
		$this->buildBindParameters = $query->getBindParameters();
		$this->tableAlias = $this->query->getTableAlias();
		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName, $this->conn);

		$sql = "DELETE FROM `" . $this->tableAlias . "` USING `" . $this->tableName . "` AS `" . $this->tableAlias . "` ";

		foreach ($this->query->getJoins() as $join) {
			$sql .= "\n" . $this->join($join, "");
		}

		$where = $this->buildWhere($this->getQuery()->getWhere());	

		if (!empty($where)) {
			$sql .= "\nWHERE " . $where;
		}

		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	private function reset() {
		$this->query = null;
		$this->buildBindParameters = [];
		$this->aliasMap = [];
	}

	/**
	 * Build the select SQL and params
	 */
	public function buildSelect(Query $query = null, $prefix = "") {

		$unions = $query->getUnions();
		
		$r = $this->internalBuildSelect($query, empty($unions) ? $prefix : $prefix . "\t");
		
		$unions = $query->getUnions();
		if(empty($unions)) {
			return $r;
		}
		
		$r['sql'] = "(\n" . $r['sql'];
		
		foreach($unions as $q) {
			$u = $this->internalBuildSelect($q, "\t");
			$r['sql'] .=  "\n) UNION (\n" . $u['sql'];
			$r['params'] = array_merge($r['params'], $u['params']);
		}
		
		$r['sql'] .= "\n)";

		//reset to the main query object
		$this->query = $query;
		// Unions can't have aliases in the global scope
		$this->aliasMap = [];
		
		$orderBy = $this->buildOrderBy(true);
		if(!empty($orderBy)) {
			$r['sql'] .= "\n" . $orderBy;
		}
		
		if ($query->getUnionLimit() > 0) {
			$r['sql'] .= "\nLIMIT " . $query->getUnionOffset() . ',' . $query->getUnionLimit();
		}
		
		return $r;
		
	}
	
	protected function internalBuildSelect(Query $query, $prefix = '') {
		$this->reset();
		$this->setTableName($query->getFrom());		
		$this->tableAlias = $query->getTableAlias();
		$this->query = $query;
		$this->buildBindParameters = $query->getBindParameters();

		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName, $this->conn);

		$joins = "";
		foreach ($this->query->getJoins() as $join) {
			$joins .= "\n" . $prefix . $this->join($join, $prefix);
		}

		$select = $prefix . $this->buildSelectFields();
		$select .= "\n" . $prefix . "FROM `" . $this->tableName . '`';
		
		if(isset($this->tableAlias) && $this->tableAlias != $this->tableName) {
			$select .= ' `' . $this->tableAlias . "`";
		}

		$where = $this->buildWhere($this->query->getWhere(), $prefix);

		if (!empty($where)) {
			$where = "\n" . $prefix . "WHERE " . $where;
		}
		$group = $this->buildGroupBy();		
		if(!empty($group)) {
			$group = "\n" . $prefix . $group;
		}
		
		$having = $this->buildHaving();
		if(!empty($having)) {
			$having = "\n" . $prefix . $having;
		}
		$orderBy = $this->buildOrderBy();
		if(!empty($orderBy)) {
			$orderBy = "\n" . $prefix . $orderBy;
		}

		$limit = "";
		if ($this->query->getLimit() > 0) {
			$limit .= "\n" . $prefix . "LIMIT " . $this->query->getOffset() . ',' . $this->query->getLimit();
		}

		$sql = $select . $joins . $where . $group . $having . $orderBy . $limit;

		if ($this->query->getForUpdate()) {
			$sql .= "\n" . $prefix . "FOR UPDATE";
		}

		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}
	
	/**
	 * Will replace all :paramName tags with the values. Used for debugging the SQL string.
	 *
	 * @param array $build
	 * @param string
	 */
	public static function debugBuild($build) {
		$sql = $build['sql'];
		$binds = [];
		if(isset($build['params'])) {
			foreach ($build['params'] as $p) {
				if (is_string($p['value']) && !mb_check_encoding($p['value'], 'utf8')) {
					$queryValue = "[NON UTF8 VALUE]";
				} else {
					$queryValue = var_export($p['value'], true);
				}
				$binds[$p['paramTag']] = $queryValue;
			}
		}

		//sort so $binds :param1 does not replace :param11 first.
		krsort($binds);

		foreach ($binds as $tag => $value) {
			$sql = str_replace($tag, $value, $sql);
		}

		return $sql;
	}

	protected function buildSelectFields() {
		$select = "SELECT ";

		if ($this->query->getCalcFoundRows()) {
			$select .= "SQL_CALC_FOUND_ROWS ";
		}

		if ($this->query->getDistinct()) {
			$select .= "DISTINCT ";
		}

		$s = $this->query->getSelect();
		if (!empty($s)) {
			$select .= implode(', ', $s);
		} else {
			$select .= '*';
		}

		return $select . ' ';
	}

	/**
	 *
	 * @param string $tableAlias
	 * @param string $column
	 * @return Column
	 * @throws Exception
	 */
	private function findColumn($tableAlias, $column) {

		if (!isset($this->aliasMap[$tableAlias])) {

//			var_dump($this);
			throw new Exception("Alias '" . $tableAlias . "'  not found in the aliasMap for " . $column);
		}

		if ($this->aliasMap[$tableAlias]->getColumn($column) == null) {
			throw new Exception("Column '" . $column . "' not found in table " . $this->aliasMap[$tableAlias]->getName());
		}
		return $this->aliasMap[$tableAlias]->getColumn($column);
	}

	private function buildGroupBy() {

		$qroupBy = $this->query->getGroupBy();

		if (empty($qroupBy)) {
			return '';
		}

		$groupBy = "GROUP BY ";

		foreach (array_unique($qroupBy) as $column) {
			if ($column instanceof Expression) {
				$groupBy .= $column . ', ';
			} else {
				$groupBy .= $this->quoteTableAndColumnName($column) . ', ';
			}
		}

		$groupBy = trim($groupBy, ' ,');

		return $groupBy . "\n";
	}
	//Clear first AND or OR. Other wise WHERE AND ... will be generated
	private $firstWhereCondition = true;

	/**
	 * Build the where part of the SQL string
	 * 
	 * @param \go\core\db\Criteria $query
	 * @param string $prefix Simple string prefix not really functional but only used to add some tab spaces to the SQL string for pretty formatting.
	 * @param string
	 */
	protected function buildWhere(array $conditions, $prefix = "") {
		$this->firstWhereCondition = true;	

		$where = "";
		foreach ($conditions as $condition) {
			$str = $this->buildCondition($condition, $prefix);
			$where .= "\n" . $prefix . $str;
		}

		return rtrim($where);
	}

	/**
	 * Convert where condition to string
	 *
	 * {@see Criteria::where()}
	 *
	 *
	 * @param string|array|Criteria $condition
	 * @param string $prefix
	 * @return string
	 * @throws Exception
	 */
	private function buildCondition($condition, $prefix = "") {

		switch ($condition[0]) {
			case 'column':
				return $this->buildColumn($condition, $prefix);
			default:
				array_shift($condition);
				return $this->buildTokens($condition, $prefix);
		}
	}

	/**
	 * Tokens is always:
	 * 
	 * return ["tokens", AND/OR, string/expression/query/criteria];		
	 * 
	 * @param type $tokens
	 * @param type $prefix
	 * @return string
	 */
	private function buildTokens($tokens, $prefix) {
		$str = "";
		
		if(stripos($tokens[0], "NOT_OR_NULL") !== false) {
			$tokens[0] = str_replace('NOT_OR_NULL', 'NOT IFNULL(', $tokens[0]);
			$tokens[] = ', false)';
		}
		
		if($this->firstWhereCondition) {
			//clear first AND/OR to avoid WHERE AND to be generated
			$tokens[0] = str_ireplace(['AND', 'OR'], '', $tokens[0]);
			$this->firstWhereCondition = false;
		}
		
		foreach ($tokens as $token) {			
			$str .= $this->buildToken($token, $prefix) . " ";
		}

		return $str;
	}

	private function buildToken($token, $prefix) {
		if (is_string($token)) {
			return $token;
		} else {
			if($token instanceof Expression) {
				return (string) $token;
			}
			
			if($token instanceof Query) {
				return $this->buildSubQuery($token, $prefix);
			}
			
			if($token instanceof Criteria) {
				$this->buildBindParameters = array_merge($this->buildBindParameters, $token->getBindParameters());
				$where = $this->buildWhere($token->getWhere(), $prefix . "\t");
				
				return "(" . $where  . "\n" . $prefix . ")";
			}
			
			throw new \Exception("Invalid token?");
		}
	}
	
	private function buildColumnArrayValue($logicalOperator, $columnName, $comparisonOperator, $value) {
		//If the value is an array and it's not an IN query we do:		
		// (foo like array1 OR foo like array2)

		if(empty($value)) {
			return "";
		}

		if ($this->firstWhereCondition) {
			//Clear first AND or OR. Other wise WHERE AND ... will be generated
			$this->firstWhereCondition = false;				
			$logicalOperator = stripos($logicalOperator, "NOT") !== false ? "NOT" : "";
		}

		$str = $logicalOperator . " (";

		for($i = 0, $c = count($value); $i < $c; $i++) {
			$str .= $this->buildColumn([null, $i == 0 ? "" : "OR", $columnName, $comparisonOperator, $value[$i]], "");
		}

		return $str .= ")";
	}

	private function buildColumn($condition, $prefix) {

		list(, $logicalOperator, $columnName, $comparisonOperator, $value) = $condition;
		
		
		if(is_array($value) && $comparisonOperator != "=" && stripos($comparisonOperator, 'IN') === false) {
			//single array value can be simplified and handled like non array value
			if(count($value) == 1) {
				$value = $value[0];
			} else
			{
				return $prefix . $this->buildColumnArrayValue($logicalOperator, $columnName, $comparisonOperator, $value);				
			}
		}
		
		if ($this->firstWhereCondition) {
			//Clear first AND or OR. Other wise WHERE AND ... will be generated
			$this->firstWhereCondition = false;
			$logicalOperator = stripos($logicalOperator, "NOT") !== false ? "NOT" : "";		
		}

		$tokens = [$logicalOperator]; //AND / OR

		$columnParts = $this->splitTableAndColumn($columnName);

		if (empty($columnParts[0])) {
			$tables = [];
			foreach($this->aliasMap as $table) {
				$tables[] = $table->getName();
			}
			throw new \Exception("Invalid column name '" . $columnName . "'. Not a column of any table: ".implode(', ', $tables));
		}

		$tokens[] = $this->quoteTableName($columnParts[0]) . '.' . $this->quoteColumnName($columnParts[1]); //column name
		
		if (!isset($value)) {
			if ($comparisonOperator == '=' || $comparisonOperator == 'IS') {
				$tokens[] = "IS NULL";
			} elseif ($comparisonOperator == '!=' || $comparisonOperator == 'IS NOT') {
				$tokens[] = "IS NOT NULL";
			} else {
				throw new Exception('Null value not possible with comparator: ' . $comparisonOperator);
			}
		} else if (is_array($value)) {
			$this->buildColumnIn($value, $columnParts, $comparisonOperator, $tokens);
		} else if ($value instanceof \go\core\db\Query) {
			$tokens[] = $comparisonOperator;
			$tokens[] = $value;
		} else {
			$paramTag = $this->getParamTag();

			$this->addBuildBindParameter($paramTag, $value, $columnParts[0], $columnParts[1]);

			$tokens[] = $comparisonOperator;
			$tokens[] = $paramTag;
		}

		return $this->buildTokens($tokens, $prefix);
	}

	private function buildColumnIn($value, $columnParts, $comparisonOperator, &$tokens){
		if(in_array(null, $value)) {
			//null value not possible with IN() so build (col is null or col in (..))
			$value = array_filter($value, function ($v) {
				return $v !== null;
			});

			array_splice($tokens, -1, 0, '(');

			$tokens[] = 'IS NULL';
			if(count($value)) {
				$tokens[] = 'OR';
				$tokens[] = $this->quoteTableName($columnParts[0]) . '.' . $this->quoteColumnName($columnParts[1]); //column name
				$tokens[] = $comparisonOperator == "=" ? "IN" : $comparisonOperator;
				$tokens[] = $this->buildInValues($columnParts, $value);
			}
			$tokens[] = ')';
		} else{
			$tokens[] = $comparisonOperator == "=" ? "IN" : $comparisonOperator;
			$tokens[] = $this->buildInValues($columnParts, $value);
		}
	}

	private function buildSubQuery(\go\core\db\Query $query, $prefix) {

		//subquery
		if ($query->getTableAlias() == 't' && $this->getQuery()->getTableAlias() == 't') {
			$query->tableAlias('sub');
		}

		$builder = new QueryBuilder($this->conn);
		$builder->aliasMap = $this->aliasMap;

		$build = $builder->buildSelect($query, $prefix . "\t");

		$str = "(\n" . $prefix . $build['sql'] . "\n" . $prefix . ")";

		$this->buildBindParameters = array_merge($this->buildBindParameters, $build['params']);

		return $str;
	}

	private function splitTableAndColumn($tableAndCol) {
		$dot = strpos($tableAndCol, '.');
		
		if ($dot !== false) {
			$column = substr($tableAndCol, $dot + 1);
			$alias = substr($tableAndCol, 0, $dot);
			return [trim($alias, ' `'), trim($column, ' `')];
		} else {
			$colName = trim($tableAndCol, ' `');
						
			//if column not found then don'use an alias. It could be an alias defined in the select part or a function.
			$alias = null;
			
			//find table for column
			foreach($this->aliasMap as $tableAlias => $table) {
				$columnObject = $table->getColumn($colName);
				if ($columnObject) {
					$alias = $tableAlias;
					break;
				}
			}
			
			return [$alias, $colName];
		}
	}

	/**
	 * Put's quotes around the table name and checks for injections
	 *
	 * @param string $tableName
	 * @param string
	 * @throws Exception
	 */
	protected function quoteTableName($tableName) {
		return Utils::quoteTableName($tableName);
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains prefix, the prefix will also be properly quoted.
	 * If the column name is already quoted or contains '(', '[[' or '{{',
	 * then this method will do nothing.
	 *
	 * @param string $columnName column name
	 * @param string the properly quoted column name
	 */
	protected function quoteColumnName($columnName) {
		return $this->quoteTableName($columnName);
	}

	/**
	 * Splits table and column on the . separator and quotes them both.
	 *
	 * @param string $columnName
	 * @param string
	 */
	protected function quoteTableAndColumnName($columnName) {

		$parts = $this->splitTableAndColumn($columnName);

		if (isset($parts[0])) {
			return $this->quoteTableName($parts[0]) . '.' . $this->quoteColumnName($parts[1]);
		} else {
			return $this->quoteColumnName($parts[1]);
		}
	}

	private function buildInValues($columnParts, $values) {

		if (empty($values)) {
			throw new \Exception("IN condition can not be empty!");
		}

		$str = "(";

		foreach ($values as $value) {
			$paramTag = $this->getParamTag();
			$this->addBuildBindParameter($paramTag, $value, $columnParts[0], $columnParts[1]);

			$str .= $paramTag . ', ';
		}

		$str = rtrim($str, ', ') . ")";

		return $str;
	}

	private function buildOrderBy($forUnion = false) {
		$oBy = $forUnion ? $this->query->getUnionOrderBy() : $this->query->getOrderBy();
		if (empty($oBy)) {
			return '';
		}

		$orderBy = "ORDER BY ";

		foreach ($oBy as $column => $direction) {

			if ($direction instanceof Expression) {
				$orderBy .= $direction . ', ';
			} else {
				$direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
				$orderBy .= $this->quoteTableAndColumnName($column) . ' ' . $direction . ', ';
			}
		}

		return trim($orderBy, ' ,');
	}

	private function buildHaving() {

		$h = $this->query->getHaving();
		if (empty($h)) {
			return '';
		}

		return "HAVING" . $this->buildWhere($h);
	}

	private function addBuildBindParameter($paramTag, $value, $tableAlias, $columnName) {

		$columnObj = $this->findColumn($tableAlias, $columnName);

		$this->buildBindParameters[] = [
				'paramTag' => $paramTag,
				'value' => $columnObj->castToDb($value),
				'pdoType' => $columnObj->pdoType
		];		
	}

	/**
	 * Private function to get the current parameter prefix.
	 *
	 * @param string The next available parameter prefix.
	 */
	private function getParamTag() {
		self::$paramCount++;
		return self::$paramPrefix . self::$paramCount;
	}

	private function join($config, $prefix) {
		$join = "";

		if ($config['src'] instanceof \go\core\db\Query) {
			$builder = new QueryBuilder($this->conn);
			$builder->aliasMap = $this->aliasMap;

			$build = $builder->buildSelect($config['src'], $prefix . "\t");
			$joinTableName = "(\n" . $prefix . "\t" . $build['sql'] . "\n" . $prefix . ')';

			$this->buildBindParameters = array_merge($build['params']);
		} else {
			$this->aliasMap[$config['joinTableAlias']] = Table::getInstance($config['src'], $this->query->getDbConnection());
			$joinTableName = '`' . $config['src'] . '`';
		}

		$join .= $config['type'] . ' JOIN ' . $joinTableName . ' ';

		if (!empty($config['joinTableAlias'])) {
			$join .= '`' . $config['joinTableAlias'] . '` ';
		}


		//import new params
		$this->buildBindParameters = array_merge($this->buildBindParameters, $config['on']->getBindParameters());
		$join .= 'ON ' . $this->buildWhere($config['on']->getWhere(), $prefix);

		return $join;
	}

}
