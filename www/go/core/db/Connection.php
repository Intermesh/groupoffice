<?php

namespace go\core\db;

use Exception;
use go\core\App;
use go\core\orm\Property;
use LogicException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * The database connection object. It uses PDO to connect to the database.
 * 
 * The app instance of this connection is available by calling:
 * 
 * ````````````````````````````````````````````````````````````````````````````
 * \go\core\App::get()->getDbConnection();
 * ````````````````````````````````````````````````````````````````````````````
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Connection {
	
	private $dsn;
	private $username;
	private $password;
	private $options;
	
	/**
	 *
	 * @var PDO
	 */
	private $pdo;
	
	/**
	 * Output all SQL to the debugger
	 * 
	 * @var bool 
	 */
	public $debug = false;
	
	public function __construct($dsn, $username, $password) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = [
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
				PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION',time_zone = '+00:00',lc_messages = 'en_US'",
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_PERSISTENT => false, // Unit test on closing DB connection fails. We need this to work for long running processes
				PDO::ATTR_EMULATE_PREPARES => false, //for native data types int, bool etc.
				PDO::ATTR_STRINGIFY_FETCHES => false,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		];
	}

	public function getDsn() {
		return $this->dsn;
	}

	/**
	 * Gets the global database connection object.
	 * 
	 * {@link http://php.net/manual/en/pdo.construct.php}
	 *
	 * @return PDO Database connection object
	 */
	public function getPDO(): PDO
	{
		if (!isset($this->pdo)) {
			$this->setPDO();
		}
		return $this->pdo;
	}

	private $database;

	/**
	 * Get the database instance
	 *
	 * @return Database
	 */
	public function getDatabase(): Database
	{
		if(!isset($this->database)) {
			$this->database = new Database($this);
		}

		return $this->database;
	}

	/**
	 * Close the database connection. Beware that all active PDO statements must be set to null too
	 * in the current scope.
	 * 
	 * Weird things happen when using fsockopen. This test case leaves the conneciton open. When removing the fputs call it seems to work.
	 * 
	 * 			

	  $settings = \GO\Sync\Model\Settings::model()->findForUser(\go\core\App::get()->user());
	  $account = \GO\Email\Model\Account::model()->findByPk($settings->account_id);


	  $handle = stream_socket_client("tcp://localhost:143");
	  $login = 'A1 LOGIN "admin@intermesh.dev" "admin"'."\r\n";
	  fputs($handle, $login);
	  fclose($handle);
	  $handle=null;

	  echo "Test\n";

	  \go\core\App::get()->unsetDbConnection();
	  sleep(10);
	 */
	public function disconnect(): void
	{
		$this->pdo = null;

		Property::clearCachedRelationStmts();
		Property::$lastDeleteStmt = null;
		self::$cachedStatements = [];
	}


	/**
	 * Checks if connection still exists
	 *
	 * @param int $id
	 * @return bool
	 * @throws DbException
	 */
	public static function exists(int $id) : bool {

		$dsn = go()->getDbConnection()->getDsn();
		$config = go()->getConfig();

		$watchConn = new Connection(
			$dsn, $config['db_user'], $config['db_pass']
		);
		$processes = $watchConn->query("SHOW PROCESSLIST")->fetchAll(\PDO::FETCH_ASSOC);

		foreach($processes as $process) {
			if($process['Id'] == $id) {
				return true;
			}
		}
		return false;
	}

	private static array $cachedStatements = [];

	/**
	 * Register a cached statement that will be unset when we want to disconnect from the database.
	 * If you don't unset these statement the connection will be kept open.
	 * @param string $name
	 * @param Statement $statement
	 * @return void
	 */
	public function cacheStatement(string $name, Statement $statement) {
		self::$cachedStatements[$name] = $statement;
	}

	public function getCachedStatment(string $name) : ?Statement {
		return self::$cachedStatements[$name] ?? null;
	}

	/**
	 * Set's a new PDO object base on the current connection settings
	 */
	private function setPDO() {
		$this->pdo = null;
		$this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);	
		// go()->debug("PDO Driver: " . $this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION));
		// if (strpos($this->pdo->getAttribute(PDO::ATTR_CLIENT_VERSION), 'mysqlnd') !== false) {
		// 	echo 'PDO MySQLnd enabled!';
		// }
	}


	/**
	 * Get MySQL connection ID
	 *
	 * @return int
	 */
	public function getId() : int {
		return (int) $this->query("select connection_id()")->fetch(PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Execute an SQL string
	 * 
	 * Should be properly escaped!
	 * {@link http://php.net/manual/en/pdo.query.php}
	 * 
	 * @param string $sql
	 * @return PDOStatement
	 * @throws DbException
	 */
	public function query(string $sql): PDOStatement
	{
		if($this->debug) {
			go()->getDebugger()->debug($sql, 1 ,false);
		}
		try {
			return $this->getPdo()->query($sql);
		}
		catch(PDOException $e) {
			throw new DbException($e, $sql);
		}
	}

	/**
	 * Execute an SQL statement and return the number of affected rows
	 * <p><b>PDO::exec()</b> executes an SQL statement in a single function call, returning the number of rows affected by the statement.</p><p><b>PDO::exec()</b> does not return results from a SELECT statement. For a SELECT statement that you only need to issue once during your program, consider issuing <code>PDO::query()</code>. For a statement that you need to issue multiple times, prepare a PDOStatement object with <code>PDO::prepare()</code> and issue the statement with <code>PDOStatement::execute()</code>.</p>
	 * @param string $sql <p>The SQL statement to prepare and execute.</p> <p>Data inside the query should be properly escaped.</p>
	 * @return int <p><b>PDO::exec()</b> returns the number of rows that were modified or deleted by the SQL statement you issued. If no rows were affected, <b>PDO::exec()</b> returns <i>0</i>.</p><p><b>Warning</b></p><p>This function may return Boolean <b><code>FALSE</code></b>, but may also return a non-Boolean value which evaluates to <b><code>FALSE</code></b>. Please read the section on Booleans for more information. Use the === operator for testing the return value of this function.</p><p>The following example incorrectly relies on the return value of <b>PDO::exec()</b>, wherein a statement that affected 0 rows results in a call to <code>die()</code>:</p> <code> &lt;&#63;php<br>$db-&gt;exec()&nbsp;or&nbsp;die(print_r($db-&gt;errorInfo(),&nbsp;true));&nbsp;//&nbsp;incorrect<br>&#63;&gt;  </code>
	 * @link http://php.net/manual/en/pdo.exec.php
	 *
	 * @throws DbException
	 */
	public function exec(string $sql): int
	{
		if($this->debug) {
			go()->getDebugger()->debug($sql, 1, true);
		}
		try {
			return $this->getPdo()->exec($sql);
		}
		catch(PDOException $e) {
			throw new DbException($e, $sql);
		}
	}
	

	/**
	 * UNLOCK TABLES explicitly releases any table locks held by the current session
	 */
	public function unlockTables(): bool
	{
		return $this->getPdo()->exec("UNLOCK TABLES") !== false;
	}

	private $transactionSavePointLevel = 0;

	/**
	 * Start a database transation
	 * 
	 * @return boolean
	 */
	public function beginTransaction(): bool
	{
		if($this->transactionSavePointLevel == 0) {
			$ret = $this->getPdo()->beginTransaction();

		}else
		{
			$ret = true;		
		}		
		
		$this->transactionSavePointLevel++;

		if($this->debug) {
			go()->debug("beginTransaction " . $this->transactionSavePointLevel, 1);
		}

		return $ret;
	}

	private $resumeLevels = 0;

	public function isPaused(): bool
	{
	  return $this->resumeLevels > 0;
  }

  /**
   * Commits the transaction but remembers the nesting of transaction nesting level. Must be resumed with resumeTransactions().
   * This is used by custom fields that do database structure changes. MySQL commits transactions automatically when the database
   * structure changes.
   *
   */
	public function pauseTransactions() {
	  if($this->isPaused()) {
	    return;
    }

		$this->resumeLevels = $this->transactionSavePointLevel;
		while($this->transactionSavePointLevel > 0) {
			$this->commit();
		}
	}

  /**
   * @see pauseTransactions()
   */
	public function resumeTransactions() {
		while($this->resumeLevels > 0) {
			$this->beginTransaction();
			$this->resumeLevels--;
		}
	}

  /**
   * Rollback the DB transaction
   *
   * @return boolean
   */
	public function rollBack(): bool
	{
		if($this->transactionSavePointLevel == 0) {
			throw new LogicException("Not in transaction!");
		}

		$this->transactionSavePointLevel--;

		if($this->debug) {
			go()->warn("ROLLBACK DB TRANSACTION " . $this->transactionSavePointLevel, 1);
		}

		if($this->transactionSavePointLevel == 0) {			
			return $this->getPdo()->rollBack();
		}else
		{
			return true;
		}
	}

  /**
   * Commit the database transaction
   *
   * @return boolean
   * @throws PDOException
   */
	public function commit(): bool
	{
		if($this->transactionSavePointLevel == 0) {
			throw new PDOException("Not in transaction!");
		}

		$this->transactionSavePointLevel--;

		if($this->debug) {
			go()->debug("COMMIT DB TRANSACTION " . $this->transactionSavePointLevel, 1);
		}


		if($this->transactionSavePointLevel == 0) {
			return $this->getPdo()->commit();
		}else
		{
			return true;
		}
	}

	/**
	 * Check if a transaction is active
	 * 
	 * @return boolean
	 */
	public function inTransaction(): bool
	{
		return $this->getPdo()->inTransaction();
	}

	/**
	 * Lock the table of this model and also for the 't' alias.
	 *
	 * The locks array should be indexed by model name and the value is an array with two optional values.
	 * THe first is a boolean that enables a write-lock and the second is a table alias.
	 *
	 * @param array $locks eg. [go\cores\Users\Model\User::tableName() => [true, 't']]
	 *
	 * @throws PDOException
	 */
	public function lock(array $locks)
	{
		$sql = "LOCK TABLES ";

		foreach ($locks as $tableName => $lockInfo) {
			$sql .= $tableName . " ";

			if (isset($lockInfo[1])) {
				$sql .= ' AS ' . $lockInfo[1] . ' ';
			}
			$sql .= empty($lockInfo[0]) ? 'READ' : 'WRITE';

			$sql .= ', ';
		}

		$sql = rtrim($sql, ', ');

		App::get()->getDbConnection()->exec($sql);
	}

	/**
	 * Create a delete statement
	 *
	 * @param string $tableName
	 * @param Query|array|string|null $query
	 * @return Statement
	 * @example
	 * ```
	 * $success = App::get()
	 *        ->getDbConnection()
	 *        ->delete("test_a", ['id' => 1])
	 *        ->execute();
	 * ```
	 */
	public function delete(string $tableName, $query = null): Statement
	{
		$query = Query::normalize($query);

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildDelete($tableName, $query);

		return $this->createStatement($build);
	}

  /**
   * Create an insert statement
   *
   * @param string $tableName
   * @param array|Query $data Key value array, a numeric array with key value arrays or select query
   * @param string[] $columns If $data is a query object then you can supply the
   *  selected columns with this parameter. If not given all columns must be
   *  selected in the correct order.
   *
   * @return Statement
   * @throws PDOException
   * @example Single record
   * ```
   * $data = [
   *    "propA" => "string 1",
   *    "createdAt" => new \DateTime(),
   *    "modifiedAt" => new \DateTime()
   * ];
   *
   * $result = App::get()
   *        ->getDbConnection()
   *        ->insert("test_a", $data)
   *        ->execute();
   * ```
   *
   * Get the ID if it has an auto increment column:
   * ```
   * $id = App::get()->getDbConnection()->getPDO()->lastInsertId();
   * ```
   *
   *
   * @example Multiple records
   * ```
   * $data = [[
   *    "propA" => "string 1",
   *    "createdAt" => new \DateTime(),
   *    "modifiedAt" => new \DateTime()
   * ],[
   *    "propA" => "string 2",
   *    "createdAt" => new \DateTime(),
   *    "modifiedAt" => new \DateTime()
   * ]];
   *
   * $result = App::get()
   *        ->getDbConnection()
   *        ->insert("test_a", $data)
   *        ->execute();
   * ```
   *
   * Or with an expression:
   *
   * ```
   * App::get()->getDbConnection()
   *  ->update("core_state", new \go\core\db\Expression("highestModSeq = highestModSeq + 1"), $query);
   * ````
   *
   */
	public function insert(string $tableName, $data, array $columns = []): Statement
	{

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildInsert($tableName, $data, $columns);

		return $this->createStatement($build);
	}

  /**
   * Insert data in the database and ignore if the records exist
   *
   * @param string $tableName
   * @param array|Query $data Key value array or select query
   * @param string[] $columns If $data is a query object then you can supply the
   *  selected columns with this parameter. If not given all columns must be
   *  selected in the correct order.
   *
   * @return Statement
   * @throws DbException
   * @see insert()
   */
	public function insertIgnore(string $tableName, $data, array $columns = []): Statement
	{

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildInsert($tableName, $data, $columns, "INSERT IGNORE");

		return $this->createStatement($build);
	}

  /**
   * Replace data in the database
   *
   * @param string $tableName
   * @param array|Query $data Key value array, array of key value arrays for multi insert or select query
   * @param string[] $columns If $data is a query object then you can supply the
   *  selected columns with this parameter. If not given all columns must be
   *  selected in the correct order.
   *
   * @return Statement
   * @throws DbException
   * @see insert()
   */
	public function replace(string $tableName, $data, array $columns = []): Statement
	{

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildInsert($tableName, $data, $columns, "REPLACE");

		return $this->createStatement($build);
	}

  /**
   * Create an update statement
   *
   * @param string $tableName
   * @param $data
   * @param Query|string|array $query {@see Query::normalize()}
   * @return Statement
   * @throws PDOException
   * @example with join
   * ```
   * go()->getDbConnection()->update(
   *     'core_acl',
   *     [
   *       'acl.entityTypeId' => $entityTypeId,
   *       'acl.entityId' => new Expression('ab.id')], // Use go\core\db\Expression for references to tables
   *     (new Query())
   *       ->tableAlias('acl') // set alias for core_acl table
   *       ->join('addressbook_addressbook, 'ab', 'ab.aclId = acl.id'))
   *   ->execute();
   * ```
   *
   *
   * @example
   * ```
   * $data = [
   *    "propA" => "string 3",
   *    "count" => new \go\core\db\Expression('count + 1'),    //Example for expression
   * ];
   *
   * $stmt = App::get()->getDbConnection()->update("test_a", $data, ['id' => 1]);
   * $stmt->execute();
   * ````
   *
   */
	public function update(string $tableName, $data, $query = null): Statement
	{
		$query = Query::normalize($query);

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildUpdate($tableName, $data, $query);

		return $this->createStatement($build);
	}

  /**
   * Update but with ignore
   *
   * @param $tableName
   * @param $data
   * @param null $query
   * @return Statement
   * @throws Exception
   * @see update()
   */
	public function updateIgnore($tableName, $data, $query = null): Statement
	{
		$query = Query::normalize($query);

		$queryBuilder = new QueryBuilder($this);
		$build = $queryBuilder->buildUpdate($tableName, $data, $query, "UPDATE IGNORE");

		return $this->createStatement($build);
	}

  /**
   * Create a select statement.
   *
   * @param string|string[] $select
   * @return Query
   * @example
   * ```
   * $query = go()->getDbConnection()
   *            ->select('*')
   *            ->from('test_a')
   *            ->where('id', '=', 1);
   *
   *  $stmt = $query->execute();
   *
   * ```
   *
   * @see Query
   */
	public function select($select = "*"): Query
	{
		$query = new Query();
		return $query->setDbConnection($this)->select($select);
	}
	
	/**
	 * Select a single column or count(*) for example.
	 * 
	 * Shortcut for:
	 * $query->fetchMode(\PDO::FETCH_COLUMN,0)->select($select)
	 * 
	 * @param string $select
	 * @return Query
	 */
	public function selectSingleValue(string $select): Query
	{
		$query = new Query();
		return $query->setDbConnection($this)->selectSingleValue($select);
	}

	/**
	 * Create a statement from a QueryBuilder result.
	 *
	 * For internal use of the API.
	 *
	 * @param array $build
	 * @return Statement
	 * @throws DbException
	 */
	public function createStatement(array &$build): Statement
	{
		try {
			if($this->debug) {
				$build['start'] = go()->getDebugger()->getMicroTime();
			}

			$pdoStmt = $this->getPDO()->prepare($build['sql']);

			$stmt = new Statement($pdoStmt);
			$stmt->setBuild($build);

			for ($i =0, $l = count($build['params']); $i < $l; $i++) {
				$stmt->bindValue($i + 1, $build['params'][$i]['value'], $build['params'][$i]['pdoType']);
			}

			return $stmt;
		} catch(PDOException $e) {
			throw new DbException($e, go()->getDebugger()->enabled ? QueryBuilder::debugBuild($build) : null);
		} catch(Exception $e) {
			if(go()->getDebugger()->enabled) {
				go()->debug(QueryBuilder::debugBuild($build));
			}
			throw $e;
		}
	}


	/**
	 * Get or set foreign key checks
	 *
	 * @param bool|null $value Provide to set new value
	 * @return bool The current or old value
	 * @throws DbException
	 */
	public function foreignKeyChecks(bool|null $value = null): bool
	{
		$stmt = $this->query("SELECT @@SESSION.foreign_key_checks");
		$current = !!$stmt->fetch(PDO::FETCH_COLUMN, 0);

		if(isset($value) && $value != $current) {
			$this->exec("set foreign_key_checks = " . ($value ? "1" : "0") . ";");
		}

		return $current;
	}


}
