<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: mysqlold.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.database
 */

/**
 * Constants
 */
define('DB_NUM', MYSQL_NUM);
define('DB_BOTH', MYSQL_BOTH);
define('DB_ASSOC', MYSQL_ASSOC);

$GLOBALS['query_count']=0;

/**
 * Class that connects to MySQL using the old MySQL extension
 *
 * @version $Id: mysqlold.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.database
 * @access public
 */



class db extends base_db {
	
	/**
	 * Type of database connector
	 *
	 * @var unknown_type
	 */
	var $type     = "mysql";
	
	/**
	 * Use pconnect
	 *
	 * @var bool
	 */
	var $pconnect     = false;

	/**
	 * Connnects to the database
	 *
	 * @return resource The connection link identifier
	 */
	public function connect() {

		/* establish connection, select database */
		
		if(!empty($this->socket))
		{
			$host = $this->host.':'.$this->socket;
		}else
		{
			$host = $this->host.':'.$this->port;
		}
				
		if ( 0 == $this->link ) {
			if(!$this->pconnect) {
				$this->link = mysql_connect($host, $this->user, $this->password);
			} else {
				$this->link = mysql_pconnect($host, $this->user, $this->password);
			}
			if (!$this->link) {
				$this->halt('Could not connect to MySQL database');
				return false;
			}
				
			$this->query("SET NAMES UTF8");
				
			if (!empty($this->database) && !@mysql_select_db($this->database,$this->link)) {
				$this->halt("cannot use database ".$this->database);
				return false;
			}
		}

		return $this->link;
	}

	/**
	 * Frees the memory associated with a result
	 * return void
	 */
	public function free() {
		if(is_object($this->result))
		{
			@mysql_free_result($this->result);
		}
		$this->result = false;
	}

	/**
	 * Queries the database
	 *
	 * @param StringHelper $sql	 
	 * @param StringHelper $types The types of the parameters. possible values: i, d, s, b for integet, double, string and blob
	 * @param mixed $params If a single or an array of parameters are given in the statement will be prepared
	 * 
	 * @return object The result object
	 */
	public function query($sql, $types='', $params=array())
	{
		/* No empty queries, please, since PHP4 chokes on them. */
		if ($sql == "")
		/* The empty query string is passed on from the constructor,
		 * when calling the class without a query, e.g. in situations
		 * like these: '$db = new DB_Sql_Subclass;'
		 */
		return false;

		if (!$this->connect()) {
			return false; /* we already complained in connect() about that. */
		};

		# New query, discard previous result.
		$this->free();

		# Count queries for debugging purposes
		$GLOBALS['query_count']++;
		
		if(!is_array($params))
		{
			$params=array($params);
		}
				
		$param_count = count($params);
		
		//replace ? with ''?'' becuase that string can never occur in a user submitted value.
		$sql = str_replace('?', "''?''", $sql);
		
		if($param_count>0)
		{
			for($i=0;$i<$param_count;$i++)
			{
				$sql = String::replace_once("''?''", "'".$this->escape($params[$i])."'", $sql);
			}
		}

		$this->result = @mysql_query($sql,$this->link);

		$this->row   = 0;

		if (!$this->result) {
			$this->halt("Invalid SQL: ".$sql);
		}

		# Will return nada if it fails. That's fine.
		return $this->result;
	}

	/**
	 * Walk the result set from a select query
	 *
	 * @param int $result_type DB_ASSOC, DB_BOTH or DB_NUM
	 * @return unknown
	 */
	public function next_record($result_type=DB_ASSOC) {
		if (!$this->result) {
			$this->halt("next_record called with no query pending.");
			return false;
		}

		$this->record = @mysql_fetch_array($this->result, $result_type);
		$this->row   += 1;

		return $this->record;
	}

	/**
	 * Gets the number of affected rows in a previous MySQL operatio
	 *
	 * @return int
	 */
	public function affected_rows() {
		return @mysql_affected_rows($this->link);
	}
	
	/**
	 * Return the number of rows found in the last select statement
	 *
	 * @return int Number of rows
	 */
	
	public function num_rows() {
		return @mysql_num_rows($this->result);
	}

	/**
	 * Get the number of fields in a result
	 *
	 * @return int
	 */
	public function num_fields() {
		return @mysql_num_fields($this->result);
	}

	/**
	 * Sets the error and errno property
	 *
	 * @return void
	 */
	protected function set_error()
	{
		$this->error = @mysql_error($this->link);
		$this->errno = @mysql_errno($this->link);
	}

	/**
	 * Returns the auto generated id used in the last query
	 *
	 * @return int
	 */
	public function insert_id()
	{
		return mysql_insert_id($this->link);
	}

	/**
	 * Escapes a value to make it safe to send to MySQL
	 *
	 * @param mixed $value
	 * @param bool $trim Trim the value
	 * @return mixed the escaped value.
	 */
	public function escape($value, $trim=true)
	{
		$this->connect();
		
		if($trim)
			$value = trim($value);
			
		return mysql_real_escape_string($value, $this->link);
	}
	
	/**
	 * Close the database connection
	 *
	 */
	public function close(){
		return mysql_close($this->link);
	}
}
