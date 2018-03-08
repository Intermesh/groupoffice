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
 * @version $Id: mysql.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.database
 */

/**
 * Constants
 */
define('DB_NUM', MYSQLI_NUM);
define('DB_BOTH', MYSQLI_BOTH);
define('DB_ASSOC', MYSQLI_ASSOC);

$GLOBALS['query_count']=0;

/**
 * Class that connects to MySQL using the MySQLi extension
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.database
 * @access public
 */

class db extends base_db{
	/**
	 * Type of database connector
	 *
	 * @var unknown_type
	 */
	var $type     = "mysqli";

	/**
	 * Set to true when are working with a prepared statement
	 *
	 * @var bool
	 */
	var $prepared_statement = false;

	/**
	 * Connnects to the database
	 *
	 * @return resource The connection link identifier
	 */

	public function connect()
	{
		global $GO_DB_LINK;

		$link_hash = md5($this->host.$this->user.$this->password.$this->database.$this->port.$this->socket);

		if(!$this->link)
		{
			if(isset($GLOBALS['GO_DB_LINK'][$link_hash])){
				$this->link = $GLOBALS['GO_DB_LINK'][$link_hash];
			}else
			{
				@$this->link = new MySQLi($this->host, $this->user, $this->password, $this->database, $this->port, $this->socket);

				//workaround for PHP bug: http://bugs.php.net/bug.php?id=45940&edit=2
				//$this->link->connect_error does not work
				$this->errno = mysqli_connect_errno();
				$this->error = mysqli_connect_error();

				if(!empty($this->error))
				{
					$this->link=false;
					$this->halt('Could not connect to MySQL database');
				}else
				{
					$this->link->set_charset("utf8");
					$GLOBALS['GO_DB_LINK'][$link_hash] = $this->link;
				}
			}
		}
		return $this->link;
	}

	/**
	 * Frees the memory associated with a result
	 * return void
	 */
	function free() {
		if(is_object($this->result))
		{
			if($this->prepared_statement)
			{
				$this->result->free_result();
			}else
			{
				$this->result->free();
			}
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
		if(empty($sql))
		return false;

		$this->connect();

		# New query, discard previous result.
		$this->free();

		
		# Count queries for debugging purposes
		$GLOBALS['query_count']++;

		if ($this->debug)
		{
			go_debug($sql);
		}

		//a single parameter does not need to be an array.
		if(!is_array($params))
		$params=array($params);

		$param_count = count($params);
		$this->prepared_statement=$param_count>0;

		if($this->prepared_statement)
		{
			$this->result = $this->link->prepare($sql);

			if(!$this->result)
			{
				$this->halt('Could not prepare statement SQL: '.$sql.' types:'.$types.' params: '.var_export($params, true));
				return false;
			}

			if(is_array($types))
			{
				$types = $this->get_types_string($params,$types);
			}
			if ($this->debug)
			{
				go_debug($params);
				go_debug($types);
			}

			//bind parameters
			$param_args=array($types);
			for($i=0;$i<$param_count;$i++)
			{
				$param_args[]=&$params[$i];
			}
			call_user_func_array(array(&$this->result, 'bind_param'), $param_args);

			$pos = 0;
			$keys = array_keys($params);
			while($pos = strpos($types,'b', $pos+1))
			{
				//go_debug('Send long data:'.$keys[$pos]);
				//foreach(str_split($params[$keys[$pos]], 8192) as $packet )
					$this->result->send_long_data($pos, $params[$keys[$pos]]);
					$params[$keys[$pos]]=NULL;
			}
			
			$ret = $this->result->execute();
			if(!$ret)
			{
				$this->halt("Invalid SQL: ".$sql."<br />\nParams: ".implode(',', $param_args));
				return false;
			}

			//bind result
			$meta = $this->result->result_metadata();
			if($meta)
			{
				//we got results so we need to bind them and store it.
				$this->result->store_result();

				$this->record=array();
				while ($field = $meta->fetch_field())
				{
					$result_args[] = &$this->record[$field->name];
				}
				call_user_func_array(array(&$this->result, 'bind_result'), $result_args);
			}

			return $ret;
		}else
		{
			$this->result = $this->link->query($sql);
			if(!$this->result)
			{
				$this->halt("Invalid SQL: ".$sql);
			}
			return $this->result;
		}
	}


	/**
	 * Walk the result set from a select query
	 *
	 * @param int $result_type DB_ASSOC, DB_BOTH or DB_NUM
	 * @return unknown
	 */
	public function next_record($result_type=DB_ASSOC) {

		if($this->result)
		{
			if ($this->prepared_statement) {
				if(!$this->result->fetch())
				{
					return false;
				}else
				{
					if($result_type==DB_BOTH || $result_type==DB_NUM)
						$this->add_indexed_values($this->record);

					$record = array();
					$i=0;
					foreach($this->record as $key=>$value)
					{
						$record[$key]=$value;
					}
					return $record;
				}
			}else
			{
				$this->record = $this->result->fetch_array($result_type);
				return $this->record;
			}
		}else
		{
			$this->halt("next_record called with no query pending.");
			return false;
		}
	}

	private function add_indexed_values(&$record)
	{
		if($record)
		{
			$i=0;
			foreach($this->record as $key=>$value)
			{
				$this->record[$i]=$value;
				$i++;
			}
		}
	}

	/**
	 * Return the number of rows found in the last select statement
	 *
	 * @return int Number of rows
	 */

	public function num_rows() {
		return $this->result ? $this->result->num_rows : false;
	}

	/**
	 * Gets the number of affected rows in a previous MySQL operatio
	 *
	 * @return int
	 */
	function affected_rows() {
		return $this->link ? $this->link->affected_rows : false;
	}


	/**
	 * Get the number of fields in a result
	 *
	 * @return int
	 */
	function num_fields() {
		return $this->result->field_count;
	}

	/**
	 * Sets the error and errno property
	 *
	 * @return void
	 */
	protected function set_error()
	{
		if($this->link)
		{
			$this->error = $this->link->error;
			$this->errno = $this->link->errno;
		}
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

		if($this->debug && is_array($value))
		{
			var_dump($value);
		}

		if($trim)
		$value = trim($value);

		return $this->link->real_escape_string($value);
	}

	/**
	 * Returns the auto generated id used in the last query
	 *
	 * @return int
	 */
	function insert_id()
	{
		return $this->link->insert_id;
	}

	/**
	 * When this object is stored in a session it must reconnect when
	 * created by the session again.
	 *
	 */
	public function __wakeup()
	{
		$this->link=false;
		$this->result=false;
	}

/**
	 * Close the database connection
	 *
	 */
	public function close(){
		return $this->link->close();
	}
}
