<?php

namespace go\core\db;

use Exception;
use go\core\db\Column;
use go\core\db\Criteria;

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
	private $buildBindParameters;

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
	 * Constructor
	 *
	 * @param string $tableName The table to operate on
	 */
	public function setTableName($tableName) {
		$this->tableName = $tableName;
		$this->table = Table::getInstance($tableName);
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
	 * @param string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * @return bool
	 */
	public function buildInsert($tableName, $data, $command = "INSERT") {

		$this->reset();
		$this->setTableName($tableName);
		$this->aliasMap[$tableName] = Table::getInstance($this->tableName);

		$sql = $command . " ";

		$sql .= "INTO `{$this->tableName}` ";

		if ($data instanceof \go\core\db\Query) {
			$build = $data->build();

			$sql .= ' ' . $build['sql'];
			$this->buildBindParameters = array_merge($this->buildBindParameters, $data->getBindParameters());
		} else if ($data instanceof \go\core\orm\Store) { //TODO
			$builder = $data->getQuery()->getBuilder($data->getRecordClassName());
			$builder->mergeAliasMap($this->aliasMap);

			$build = $builder->buildSelect($data->getQuery());

			$sql .= ' ' . $build['sql'];
			//import subquery bind params
			foreach ($build['params'] as $v) {
				$this->buildBindParameters[] = $v;
			}
		} else {

			$tags = [];
			foreach ($data as $colName => $value) {
				$paramTag = $this->getParamTag();
				$tags[] = $paramTag;
				$this->addBuildBindParameter($paramTag, $value, $this->tableName, $colName);
			}

			$sql .= " (\n\t`" . implode("`,\n\t`", array_keys($data)) . "`\n)\n" .
							"VALUES (\n\t" . implode(",\n\t", $tags) . "\n)";
		}
		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	public function buildUpdate($tableName, $data, Query $query) {

		$this->reset();

		$this->setTableName($tableName);

		$this->query = $query;
		$this->tableAlias = $this->query->getTableAlias();
		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName);

		if (is_array($data)) {
			$updates = [];
			foreach ($data as $colName => $value) {
				$paramTag = $this->getParamTag();
				$updates[] = '`' . $colName . '` = ' . $paramTag;

				$this->addBuildBindParameter($paramTag, $value, $this->tableAlias, $colName);
			}
			$set = implode(",\n\t", $updates);
		} else if ($data instanceof Expression) {
			$set = (string) $data;
		}
		
		$sql = "UPDATE `{$this->tableName}` `" . $this->tableAlias . "` SET\n\t" . $set;

		$where = $this->buildWhere($this->getQuery()->getWhere());

		if (!empty($where)) {
			$sql .= "\nWHERE " . $where;
		}

		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	public function buildDelete($tableName, Criteria $query) {

		$this->setTableName($tableName);
		$this->reset();
		$this->query = $query;
		$this->tableAlias = $this->query->getTableAlias();
		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName);

		$sql = "DELETE FROM `" . $this->tableAlias . "` USING `" . $this->tableName . "` AS `" . $this->tableAlias . "` ";

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
	 * Build the SQL string
	 *
	 * @param boolean $replaceBindParameters Will replace all :paramName tags with the values. Used for debugging the SQL string.
	 * @return string
	 */
	public function buildSelect(Query $query = null, $prefix = '') {

		$this->setTableName($query->getFrom());
		$this->reset();
		$this->tableAlias = $query->getTableAlias();
		$this->query = $query;
		$this->buildBindParameters = $query->getBindParameters();

		$this->aliasMap[$this->tableAlias] = Table::getInstance($this->tableName);

		$joins = "";
		foreach ($this->query->getJoins() as $join) {
			$joins .= "\n" . $prefix . $this->join($join, $prefix);
		}

		$select = "\n" . $prefix . $this->buildSelectFields();
		$select .= "\n" . $prefix . "FROM `" . $this->tableName . '` `' . $this->tableAlias . "`";

		$where = $this->buildWhere($this->query->getWhere(), $prefix);

		if (!empty($where)) {
			$where = "\n" . $prefix . "WHERE " . $where;
		}
		$group = "\n" . $prefix . $this->buildGroupBy();
		$having = "\n" . $prefix . $this->buildHaving();
		$orderBy = "\n" . $prefix . $this->buildOrderBy();

		$limit = "";
		if ($this->query->getLimit() > 0) {
			$limit .= "\n" . $prefix . "LIMIT " . $this->query->getOffset() . ',' . $this->query->getLimit();
		}

		$sql = trim($prefix . $select . $joins . $where . $group . $having . $orderBy . $limit);

		if ($this->query->getForUpdate()) {
			$sql .= "\n" . $prefix . "FOR UPDATE";
		}

		return ['sql' => $sql, 'params' => $this->buildBindParameters];
	}

	protected function buildSelectFields() {
		$select = "SELECT ";

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

	/**
	 * Build the where part of the SQL string
	 * 
	 * @param \go\core\db\Criteria $query
	 * @param string $prefix Simple string prefix not really functional but only used to add some tab spaces to the SQL string for pretty formatting.
	 * @param string
	 */
	protected function buildWhere(array $conditions, $prefix = "") {



		if (isset($conditions[0])) {
			$conditions[0][1] = "";
		}

		$where = "";
		foreach ($conditions as $condition) {
			$where .= $prefix . $this->buildCondition($condition, $prefix) . "\n";
		}

		return trim($where);
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

	private function buildTokens($tokens, $prefix) {
		$str = "";
		foreach ($tokens as $token) {
			$str .= $this->buildToken($token, $prefix) . " ";
		}

		return $str;
	}

	private function buildToken($token, $prefix) {
		if (is_string($token)) {
			return $token;
		} else {
			switch (get_class($token)) {
				case Expression::class:
					return (string) $token;

				case Query::class:
					return $this->buildSubQuery($token, $prefix);

				case Criteria::class:
					$this->buildBindParameters = array_merge($this->buildBindParameters, $token->getBindParameters());
					return "(\n" . $prefix . "\t" . $this->buildWhere($token->getWhere(), $prefix . "\t") . $prefix . "\n)";
			}
		}
	}

	private function buildColumn($condition, $prefix) {

		list(, $logicalOperator, $columnName, $comparisonOperator, $value) = $condition;

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
			} elseif ($comparisonOperator == '!=' || $comparisonOperator == 'NOT IS') {
				$tokens[] = "IS NOT NULL";
			} else {
				throw new Exception('Null value not possible with comparator ' . $tokens[3]);
			}
		} else if (is_array($value)) {
			$tokens[] = $comparisonOperator;
			$tokens[] = $this->buildInValues($columnParts, $value);
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

	private function buildSubQuery(\go\core\db\Query $query, $prefix) {

		//subquery
		if ($query->getTableAlias() == 't' && $this->getQuery()->getTableAlias() == 't') {
			$query->tableAlias('sub');
		}

		$builder = new QueryBuilder();
		$builder->aliasMap = $this->aliasMap;

		$build = $builder->buildSelect($query, $prefix . "\t");

		$str = "(\n" . $prefix . "\t" . $build['sql'] . "\n" . $prefix . ")";

		$this->buildBindParameters = array_merge($this->buildBindParameters, $build['params']);

		return $str;
	}

	private function splitTableAndColumn($column) {
		$parts = explode('.', $column);

		$c = count($parts);
		if ($c > 1) {
			$column = array_pop($parts);
			$alias = array_pop($parts);
			return [trim($alias, ' `'), trim($column, ' `')];
		} else {
			$colName = trim($column, ' `');
			
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

		//disallow \ ` and \00  : http://stackoverflow.com/questions/1542627/escaping-field-names-in-pdo-statements
		if (preg_match("/[`\\\\\\000\(\),]/", $tableName)) {
			throw new Exception("Invalid characters found in column name: " . $tableName);
		}

		return '`' . str_replace('`', '``', $tableName) . '`';
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

	private function buildOrderBy() {
		$oBy = $this->query->getOrderBy();
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
			$builder = new QueryBuilder();
			$builder->aliasMap = $this->aliasMap;

			$build = $builder->buildSelect($config['src'], $prefix . "\t");
			$joinTableName = "(\n" . $prefix . "\t" . $build['sql'] . "\n" . $prefix . ')';

			$this->buildBindParameters = array_merge($build['params']);
		} else {
			$this->aliasMap[$config['joinTableAlias']] = Table::getInstance($config['src']);
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
