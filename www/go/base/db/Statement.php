<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * This class is used to create the SQL statement that is executed against the database
 * It is usually create by the PDO::createCommand function
 * 
 * To execute non-query SQL (insert, delete, update). call execute()
 * To execute a SQL statement that returns results (select) call query()
 * 
 * STILL TODO: JOINING, GROUPING, UNIONING, DATABASE SPESIFIC LIKE DROP, TRUNCATE, CREATE
 *
 * @package GO.db
 * @copyright Copyright Intermesh
 * @version $Id Command.php 2012-06-14 10:22:40 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Base\Db;
use IteratorIterator;

class Statement implements \IteratorAggregate
{
	private $_connection; //The database connection the belongs to this statement
	private $_statement; //The PDOStatement for this DBCommand
	private $_query; // the sql query to be executed is an array with query options
	private $_text; //the string of sql to be executed
	private $_fetchMode = array(PDO::FETCH_ASSOC); //how the fetch rows defaults to ASSOC
	private $_totalRows; 
	
	public $params = array(); //array of parameters to be bound to the query (name=>value)
	
	/**
	 * Can only be constructed with a connection
	 * @param PDO $connection 
	 */
	public function __construct(Connection $connection)
	{
		$this->_connection=$connection;
	}
	
	public function setFetchMode($mode)
	{
		$this->_fetchMode = $mode;
	}
	
	/**
		* @return StringHelper the SQL statement to be executed
		*/
	public function getText()
	{
		if ($this->_text == '' && !empty($this->_query))
			$this->setText($this->buildQuery($this->_query));
		return $this->_text;
	}
	
	/**
	 * Specifies the SQL statement to be executed.
	 * The previous statement will be set to null
	 * @param StringHelper $value the SQL statement
	 * @return Statement Myself for chaining 
	 */
	protected function setText($value)
	{
		$this->_statement=null;
		$this->_text = $value;
		return $this;
	}
	
	public function reset()
	{
			$this->_text=null;
			$this->_query=null;
			$this->_statement=null;
			return $this;
	}


	/**
		* Prepares the SQL statement to be executed.
		* For complex SQL statement that is to be executed multiple times,
		* For SQL statement with binding parameters, this method is invoked
		* automatically.
		*/
	public function prepare() {
		if ($this->_statement == null) 
		{
			try {
				$this->_statement = $this->_connection->getPdoInstance()->prepare($this->getText());
				$this->_paramLog = array();
			} catch (\Exception $e) {
				$errorInfo = $e instanceof PDOException ? $e->errorInfo : null;
				throw new \GO\Base\Exception\Database('DbCommand failed to prepare the SQL statement: '. $e->getMessage(), $e->getCode(), $errorInfo);
			}
		}
	}
	
	/**
	 * Executes the SQL statement.
	 * This method is meant only for executing non-query SQL statement.
	 * No result set will be returned.
	 * @param array $params input parameters (name=>value) for the SQL execution. This is an alternative
	 * @return integer number of rows affected by the execution.
	 * @throws \GO\Base\Exception\Database execution failed
	 */
	public function execute($params = array())
	{
		$p=array();
		foreach($params as $name=>$value)
			$p[$name]=$name.'='.var_export($value,true);
		$par='. Bound with ' .implode(', ',$p);
		try
		{
			$this->prepare();
			//Set fetchmode before executing for feting in the stements iterator
			call_user_func_array(array($this->_statement, 'setFetchMode'), $this->_fetchMode); 
			if ($params === array())
				$this->_statement->execute();
			else
				$this->_statement->execute($params);
			$n = $this->_statement->rowCount();
			return $n;
		}
		catch (\Exception $e)
		{
			$errorInfo = $e instanceof PDOException ? $e->errorInfo : null;
			$message = $e->getMessage();
			\GO::debug('DbCommand::execute() failed: '.$message.' The SQL statement executed was: '.$this->getText() . $par);
			throw new \GO\Base\Exception\Database('DbStatement failed to execute the SQL statement: '.$message, (int) $e->getCode(), $errorInfo);
		}
	}
	
	/**
	 * Executes SQL statement en returns all rows
	 * @param array $params input params (name=>value)
	 * @return array all rows of the query result
	 * @throws \GO\Base\Exception\Database execution failed 
	 */
	public function queryAll($params=array())
	{
		$this->_query['calc_found_rows'] = "SQL_CALC_FOUND_ROWS"; //TODO: IS MYSQL ONLY
		return $this->queryInternal('fetchAll',$this->_fetchMode, $params);
	}
	
	public function totalRows()
	{
		if($this->_statement == null)
			$this->execute($this->params);
		if(!empty($this->_query['calc_found_rows'])){
			$this->setText("SELECT FOUND_ROWS() as found"); //MYSQL only
		  $record = $this->queryRow();
			return $record['found'];
		}
		else
			throw new \GO\Base\Exception\Database('Cannot know total rows before queryAll is executed');
	}
	
	/**
	 * Executes SQL statement and returns the first row
	 * @param array $params input params (name=>value)
	 * @return first row of the query result
	 * @throws \GO\Base\Exception\Database execution failed 
	 */
	public function queryRow()
	{
		return $this->queryInternal('fetch',$this->_fetchMode);
	}
	
	/**
	 * executes the query on the PDOStatement
	 * TODO: Log executes queries in debug mode, cache query results in later releases
	 * @param StringHelper $method method of PDOStatement to be called (fetch, fetchAll)
	 * @param mixed $mode parameters to be passed to the method
	 * @param array $params input parameters (name=>value) for the SQL execution.
	 * @return mixed the method execution result
	 */
	private function queryInternal($method, $mode, $params = array())
	{
		$params = array_merge($this->params, $params); //Params set in where() get merged

		$p = array();
		foreach ($params as $name => $value)
			$p[$name] = $name . '=' . var_export($value, true);
		$par = '. Bound with ' . implode(', ', $p);

		try
		{
			$this->prepare();
			if ($params === array())
				$this->_statement->execute();
			else
				$this->_statement->execute($params);

			$mode = (array) $mode;
			$result = call_user_func_array(array($this->_statement, $method), $mode); //fetchAll or fetch
			$this->_statement->closeCursor();

			return $result;
		}
		catch (\Exception $e)
		{
			$errorInfo = $e instanceof PDOException ? $e->errorInfo : null;
			$message = $e->getMessage();
			if (\GO::config()->debug)
				$message .= '. The SQL statement executed was: ' . $this->getText() . $par;
			throw new \GO\Base\Exception\Database('DbCommand failed to execute the SQL statement: '.$message, (int) $e->getCode(), $errorInfo);
		}
	}

	
	/**
		* Builds a SQL SELECT statement from the given query specification.
		* @return StringHelper the SQL statement
		*/
	public function buildQuery($query)
	{
		$sql = isset($query['distinct']) && $query['distinct'] ? 'SELECT DISTINCT' : 'SELECT';
		
		$sql.= isset($query['calc_found_rows']) ? " ".$query['calc_found_rows'] : "";
		
		$sql.=' ' . (isset($query['select']) ? $query['select'] : '* ');

		if (isset($query['from']))
			$sql.="\nFROM " . $query['from'];
		else
			throw new \GO\Base\Exception\Database('The DB query must contain the "from" portion.');

		if(isset($query['join']))
      $sql.="\n".(is_array($query['join']) ? implode("\n",$query['join']) : $query['join']);

		
		if (isset($query['where']))
			$sql.="\nWHERE " . $query['where'];

		if (isset($query['order']))
			$sql.="\nORDER BY " . $query['order'];

		$limit = isset($query['limit']) ? (int) $query['limit'] : -1;
		$offset = isset($query['offset']) ? (int) $query['offset'] : -1;
		if ($limit >= 0 || $offset > 0)
			$sql = $this->_connection->applyLimit($sql, $limit, $offset); //TODO: works for mysql progressql and sqlite only

		return $sql;
	}

	/**
	 * Sets the SELECT part of the query
	 * @param mixed $columns the columns to be selected. Defaults to '*', meaning all columns.
	 * Columns can be specified in either a string (e.g. "id, name") or an array (e.g. array('id', 'name'))
	 * The method will automatically quote the column names unless a column contains some parenthesis ()
	 * 
	 */
	public function select($columns = '*', $option = '')
	{
		if (is_string($columns) && strpos($columns, '(') !== false)
			$this->_query['select'] = $columns;
		else
		{
			if (!is_array($columns))
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);

			foreach ($columns as $i => $column)
			{
				if (is_object($column))
					$columns[$i] = (string) $column;
				else if (strpos($column, '(') === false)
				{
					if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $column, $matches))
						$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' AS ' . $this->_connection->quoteColumnName($matches[2]);
					else
						$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
			$this->_query['select'] = implode(', ', $columns);
		}
		if ($option != '')
			$this->_query['select'] = $option . ' ' . $this->_query['select'];
		return $this;
	}
	
	/**
	 * Sets the SELECT part of the query with the DISTINCT flag turned on.
   * This is the same as select() except that the DISTINCT flag is turned on.
	 * @param mixed $columns column name in array or string form
	 * @return Statement myself for chaining
	 */
	public function selectDistinct($columns='*')
	{
					$this->_query['distinct']=true;
					return $this->select($columns);
	}

	/**
	 * Sets the FROM part of the query
	 * @param mixed $tables to be selected from. This can be either a string (e.g. 'tbl_user')
	 * or an array (e.g. array('tbl_user', 'tbl_profile')) specifying one or several table names.
   * Table names can contain schema prefixes (e.g. 'public.tbl_user') and/or table aliases (e.g. 'tbl_user u').
   * The method will automatically quote the table names unless it contains some parenthesis
	 */
	public function from($tables)
  {
		if (is_string($tables) && strpos($tables, '(') !== false)
			$this->_query['from'] = $tables;
		else
		{
			if (!is_array($tables))
				$tables = preg_split('/\s*,\s*/', trim($tables), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($tables as $i => $table)
			{
				if (strpos($table, '(') === false)
				{
					if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches))	// with alias
						$tables[$i] = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
					else
						$tables[$i] = $this->_connection->quoteTableName($table);
				}
			}
			$this->_query['from'] = implode(', ', $tables);
		}
		return $this;
	}
	
	/**
	 * Sets the WHERE part of the query.
	 * 
	 * 
	 * 
	 * @param StringHelper $conditions the conditions that should be put in the WHERE part. (eg.: 'id=:id')
	 * @param array $params the paremeters (name=>value) to be bound to the query
	 * @return Statement mysql for chaining
	 */
	public function where($conditions, $params = array())
	{
		$this->_query['where'] = $this->processConditions($conditions);
		foreach ($params as $name => $value)
			$this->params[$name] = $value;
		return $this;
	}
	
	public function join($table, $conditions, $params = array())
	{
		return $this->joinInternal('join', $table, $conditions, $params);
	}
	public function leftJoin($table, $conditions, $params = array())
	{
		return $this->joinInternal('left join', $table, $conditions, $params);
	}
	
	public function rightJoin($table, $conditions, $params = array())
	{
		return $this->joinInternal('right join', $table, $conditions, $params);
	}
	/**
	 * Appends an JOIN part to the query.
	 * @param StringHelper $type the join type ('join', 'left join', 'right join', 'cross join', 'natural join')
	 * @param StringHelper $table the table to be joined.
	 * Table name can contain schema prefix (e.g. 'public.tbl_user') and/or table alias (e.g. 'tbl_user u').
	 * The method will automatically quote the table name unless it contains some parenthesis
	 * (which means the table is given as a sub-query or DB expression).
	 * @param mixed $conditions the join condition that should appear in the ON part.
	 * @param array $params the parameters (name=>value) to be bound to the query
	 * @return Statement myself for chaining
	 */
	private function joinInternal($type, $table, $conditions = '', $params = array())
	{
		if (strpos($table, '(') === false)
		{
			if (preg_match('/^(.*?)(?i:\s+as\s+|\s+)(.*)$/', $table, $matches))	// with alias
				$table = $this->_connection->quoteTableName($matches[1]) . ' ' . $this->_connection->quoteTableName($matches[2]);
			else
				$table = $this->_connection->quoteTableName($table);
		}

		$conditions = $this->processConditions($conditions);
		if ($conditions != '')
			$conditions = ' ON ' . $conditions;

		if (isset($this->_query['join']) && is_string($this->_query['join']))
			$this->_query['join'] = array($this->_query['join']);

		$this->_query['join'][] = strtoupper($type) . ' ' . $table . $conditions;

		foreach ($params as $name => $value)
			$this->params[$name] = $value;
		return $this;
	}

	/**
		* Sets the ORDER BY part of the query.
		* @param mixed $columns the columns (and the directions) to be ordered by.
		* Columns can be specified in either a string (e.g. "id ASC, name DESC") or an array (e.g. array('id ASC', 'name DESC')).
		* The method will automatically quote the column names
		* @return Statement myself for chaining
		*/
  public function order($columns)
	{
		if (is_string($columns) && strpos($columns, '(') !== false)
			$this->_query['order'] = $columns;
		else
		{
			if (!is_array($columns))
				$columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
			foreach ($columns as $i => $column)
			{
				if (is_object($column))
					$columns[$i] = (string) $column;
				else if (strpos($column, '(') === false)
				{
					if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches))
						$columns[$i] = $this->_connection->quoteColumnName($matches[1]) . ' ' . strtoupper($matches[2]);
					else
						$columns[$i] = $this->_connection->quoteColumnName($column);
				}
			}
			$this->_query['order'] = implode(', ', $columns);
		}
		return $this;
	}
	
	/**
	 * Sets the LIMIT part of the query.
	 * @param integer $limit the limit
	 * @param integer $offset the offset
	 * @return Statement myself for chaining
	 */
	public function limit($limit, $offset=null)
	{
					$this->_query['limit']=(int)$limit;
					if($offset!==null)
									$this->offset($offset);
					return $this;
	}

	/**
	 * Sets the OFFSET part of the query.
	 * @param integer $offset the offset
	 * @return Statement myself for chaining
	 */
	public function offset($offset)
	{
					$this->_query['offset']=(int)$offset;
					return $this;
	}
	
	/**
		* Creates and executes an INSERT SQL statement.
		* The method will properly escape the column names, and bind the values to be inserted.
		* @param StringHelper $table the table that new rows will be inserted into.
		* @param array $columns the column data (name=>value) to be inserted into the table.
		* @return integer number of rows affected by the execution.
		*/
	public function insert($table, $columns)
	{
		$params = array();
		$names = array();
		$placeholders = array();
		foreach ($columns as $name => $value)
		{
			$names[] = $this->_connection->quoteColumnName($name);
			$placeholders[] = ':' . $name;
			$params[':' . $name] = $value;
		}
		$sql = 'INSERT INTO ' . $this->_connection->quoteTableName($table)
						. ' (' . implode(', ', $names) . ') VALUES ('
						. implode(', ', $placeholders) . ')';
		return $this->setText($sql)->execute($params);
	}
	
	/**
	 * Creates and executes in INSERT SQL statement for inserting multiple rows.
	 * @param StringHelper $table the table to insert to rows into
	 * @param array $data an array of insertable rows
	 * @return integet number of rows affected by the execution 
	 */
	public function insertMany($table, $data)
	{
		$params = array();
		$names = array();
		$placeholders = array();
		
		foreach(array_keys($data[0]) as $name)
		{
			$names[] = $this->_connection->quoteColumnName($name);
		}
		foreach($data as $r => $row)
		{
			foreach($row as $name => $value)
			{
				$key = ':row'.$r."_".$name;
				$placeholders[$r][] = $key;
				$params[$key] = $value;
			}
		}
		
		$sql = 'INSERT INTO ' . $this->_connection->quoteTableName($table)
						. ' (' . implode(', ', $names) . ') VALUES ';
		foreach($placeholders as $placeholder)
			$sql .= '('. implode(', ', $placeholder) .'), ';
		$sql = substr($sql, 0, -2);
		
		return $this->setText($sql)->execute($params);
	}

	/**
	 * Creates and executes an UPDATE sql statement
	 * @param StringHelper $table the table to update
	 * @param array $columns the column data (name=>value) to be updated
	 * @param mixed $conditions the condition that will be put in the WHERE part.
	 * @param array $params the params to be bound to the query
	 * @return int number of rows affected 
	 */
	public function update($table, $columns, $conditions = '', $params = array())
	{
		$lines = array();
		foreach ($columns as $name => $value)
		{
				$lines[] = $this->_connection->quoteColumnName($name) . '=:' . $name;
				$params[':' . $name] = $value;
		}
		$sql = 'UPDATE ' . $this->_connection->quoteTableName($table) . ' SET ' . implode(', ', $lines);
		if (($where = $this->processConditions($conditions)) != '')
			$sql.=' WHERE ' . $where;
		
		return $this->setText($sql)->execute($params);
	}
	
	/**
		* Creates and executes a DELETE SQL statement.
		* @param StringHelper $table the table where the data will be deleted from.
		* @param mixed $conditions the conditions that will be put in the WHERE part.
		* @param array $params the parameters to be bound to the query.
		* @return integer number of rows affected by the execution.
		*/
	public function delete($table, $conditions = '', $params = array())
	{
		$sql = 'DELETE FROM ' . $this->_connection->quoteTableName($table);
		if (($where = $this->processConditions($conditions)) != '')
			$sql.=' WHERE ' . $where;
		return $this->setText($sql)->execute($params);
	}
	
	/**
		* Generates the condition string that will be put in the WHERE part
		* @param mixed $conditions the conditions that will be put in the WHERE part.
		* @return StringHelper the condition string to put in the WHERE part
		*/
  private function processConditions($conditions)
	{
		if (!is_array($conditions))
			return $conditions;
		else
		  throw new \GO\Base\Exception\Database('condition should be a string');
	}

	/**
	 * Builds and executes a SQL statement for truncating a DB table.
	 * @param StringHelper $table the table to be truncated. The name will be properly quoted by the method.
	 * @return integer number of rows affected by the execution.
	 */
	public function truncateTable($table)
	{
		$n = $this->setText("TRUNCATE TABLE ".$this->_connection->quoteTableName($table))->execute();
		return $n;
	}
	
	/**
	 * Making the activefinder iterable
	 * @return IteratorIterator
	 */
	public function getIterator()
	{
		if($this->_statement == null)
			$this->execute($this->params);
		//if($this->_statement instanceof PDOStatement)
			//var_dump($this->_statement);
		return new IteratorIterator($this->_statement);
	}

}
?>
