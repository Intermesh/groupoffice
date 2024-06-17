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
 * The databaseconnection object. Handels al database specific actions
 *
 * @package GO.
 * @copyright Copyright Intermesh
 * @version $Id Connection.php 2012-06-14 14:35:41 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Base\Db;


class Connection
{

	public $charset = 'utf8';
	public $connectionString;
	public $username;
	public $password;
	public $emulatePrepare = true; //if true PDO emulates prepare statement, mysql native prepare is buggy
	private $_active = false;
	private $_pdo; //Instance of the PDO Class
	private $_transaction; //Instance of the Transaction class
	private $_schema; //database schema depending on the driver

	public function __construct($dsn = '', $username = '', $password = '')
	{
		$this->connectionString = $dsn;
		$this->username = $username;
		$this->password = $password;
	}

	/**
	 * Returns the ID of the last inserted row or sequence value.
	 * @param string $sequenceName name of the sequence object (required by some DBMS)
	 * @return string the row ID of the last row inserted, or the last value retrieved from the sequence object
	 */
	public function getLastInsertID($sequenceName = '')
	{
		$this->setActive(true);
		return $this->_pdo->lastInsertId($sequenceName);
	}

	public function init()
	{
		if ($this->autoConnect)
			$this->setActive(true);
	}

	/**
	 * Open or close the DB connection.
	 * @param boolean $value whether to open or close DB connection
	 * @throws CException if connection fails
	 */
	public function setActive($value)
	{
		if ($value != $this->_active)
		{
			if ($value)
				$this->open();
			else
				$this->close();
		}
	}

	/**
	 * Returns whether the DB connection is established.
	 * @return boolean whether the DB connection is established
	 */
	public function getActive()
	{
		return $this->_active;
	}

	/**
	 * Opens DB connection if it is currently not
	 * @throws \GO\Base\Exception\Database if connection fails
	 */
	protected function open()
	{
		if ($this->_pdo === null)
		{
//			if (empty($this->connectionString))
//				throw new \GO\Base\Exception\Database('Connection.connectionString cannot be empty.');
			try
			{
				$this->_pdo = $this->createPdoInstance();
				//$this->initConnection($this->_pdo);
				$this->_active = true;
			}
			catch (PDOException $e)
			{
				throw new \GO\Base\Exception\Database('Connection failed to open the DB connection.' . $e->getMessage(), (int) $e->getCode(), $e->errorInfo);
			}
		}
	}

	/**
	 * Closes the currently active DB connection.
	 * It does nothing if the connection is already closed.
	 */
	protected function close()
	{
		$this->_pdo = null;
		$this->_active = false;
		$this->_schema = null;
	}

	/**
	 * Creates the PDO instance.
	 * When some functionalities are missing in the pdo driver, we may use
	 * an adapter class to provides them. mssql will not work correct with PDO
	 * @return PDO the pdo instance
	 */
	protected function createPdoInstance()
	{
		if(empty($this->connectionString))
			return \GO::getDbConnection ();
		else	
			return new PDO($this->connectionString, $this->username, $this->password);
	}

//	/**
//	 * Initializes the open db connection.
//	 * This method is invoked right after the db connection is established.
//	 * The default implementation is to set the charset for MySQL and PostgreSQL database connections.
//	 * @param PDO $pdo the PDO instance
//	 */
//	protected function initConnection($pdo)
//	{
//		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//		//$pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, array('GO\Base\Db\ActiveStatement', array()));
//		$driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
//
//		if ($this->emulatePrepare !== null && constant('PDO::ATTR_EMULATE_PREPARES'))
//			$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, $this->emulatePrepare);
//		if ($this->charset !== null)
//		{
//			if (in_array($driver, array('pgsql', 'mysql', 'mysqli')))
//				$pdo->exec('SET NAMES ' . $pdo->quote($this->charset));
//		}
//
//		if (in_array($driver, array('mysql', 'mysqli')))
//			$this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true); //todo: needed for foundRows
//		if (\GO::config()->debug)
//		{
//			$pdo->exec("SET sql_mode='TRADITIONAL'");
//		}
//	}

	/**
	 * Returns the PDO instance.
	 * @return PDO the PDO instance, null if the connection is not established yet
	 */
	public function getPdoInstance()
	{
		return $this->_pdo;
	}

	public function createStatement()
	{
		$this->setActive(true);
		return new Statement($this);
	}
	
	/**
		* Alters the SQL to apply LIMIT and OFFSET.
		* Default implementation is applicable for PostgreSQL, MySQL and SQLite.
		* @param string $sql SQL query string without LIMIT and OFFSET.
		* @param integer $limit maximum number of rows, -1 to ignore limit.
		* @param integer $offset row offset, -1 to ignore offset.
		* @return string SQL with LIMIT and OFFSET
		*/
	public function applyLimit($sql,$limit,$offset)
	{
					if($limit>=0)
									$sql.=' LIMIT '.(int)$limit;
					if($offset>0)
									$sql.=' OFFSET '.(int)$offset;
					return $sql;
	}


	/**
	 * Starts a transaction.
	 * @return Transaction the transaction initiated
	 */
	public function beginTransaction()
	{
		$this->setActive(true);
		$this->_pdo->beginTransaction();
		return $this->_transaction = new Transaction($this);
	}

	/**
	 * Quotes a string value for use in a query.
	 * @param string $str string to be quoted
	 * @return string the properly quoted string
	 */
	public function quoteValue($str)
	{
		if (is_int($str) || is_float($str))
			return $str;

		$this->setActive(true);
		if (($value = $this->_pdo->quote($str)) !== false)
			return $value;
		else // the driver doesn't support quote (e.g. oci)
			return "'" . addcslashes(str_replace("'", "''", $str), "\000\n\r\\\032") . "'";
	}

	/**
	 * Quotes a table name for use in a query.
	 * If the table name contains schema prefix, the prefix will also be properly quoted.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		if (strpos($name, '.') === false)
			return "`" . $name . "`";
		$parts = explode('.', $name);
		foreach ($parts as $i => $part)
			$parts[$i] = "`" . $part . "`";
		return implode('.', $parts);
	}

	/**
	 * Quotes a column name for use in a query.
	 * If the column name contains prefix, the prefix will also be properly quoted.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		if (($pos = strrpos($name, '.')) !== false)
		{
			$prefix = $this->quoteTableName(substr($name, 0, $pos)) . '.';
			$name = substr($name, $pos + 1);
		}
		else
			$prefix = '';
		return $prefix . ($name === '*' ? $name : '`'.$name.'`');
	}

	/**
	 * Sets an attribute on the database connection.
	 * @param integer $name the attribute to be set
	 * @param mixed $value the attribute value
	 */
	public function setAttribute($name, $value)
	{
		$this->_pdo->setAttribute($name, $value);
	}

}

?>
