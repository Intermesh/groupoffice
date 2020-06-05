<?php

namespace go\core\db;

use Exception;
use go\core\App;
use go\core\Debugger;
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

	const SQL_MODE = "STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"; // "ONLY_FULL_GROUP_BY,STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION";

	private $dsn;
	private $username;
	private $password;
	private $options;
	
	/**
	 *
	 * @var PDO
	 */
	private $pdo;

	public function __construct($dsn, $username, $password, $options = []) {
		$this->dsn = $dsn;
		$this->username = $username;
		$this->password = $password;
		$this->options = $options;
	}

	/**
	 * Gets the global database connection object.
	 * 
	 * {@link http://php.net/manual/en/pdo.construct.php}
	 *
	 * @return PDO Database connection object
	 */
	public function getPDO() {
		if (!isset($this->pdo)) {
			$this->setPDO();
		}
		return $this->pdo;
	}

	/**
	 * Close the database connection. Beware that all active PDO statements must be set to null too
	 * in the current scope.
	 * 
	 * Wierd things happen when using fsockopen. This test case leaves the conneciton open. When removing the fputs call it seems to work.
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
	public function disconnect() {
		$this->pdo = null;
	}

	/**
	 * Set's a new PDO object base on the current connection settings
	 */
	private function setPDO() {
		$this->pdo = null;
		$this->pdo = new PDO($this->dsn, $this->username, $this->password, $this->options);
		$this->getPdo()->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->getPdo()->setAttribute(PDO::ATTR_PERSISTENT, true);
//		$this->getPdo()->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //for native data types int, bool etc. We can't use this because we need fetch_class
		$this->getPdo()->query("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
		$this->getPdo()->query("SET sql_mode='" . self::SQL_MODE . "'");
		$this->getPdo()->query("SET time_zone = '+00:00'");
		$this->getPdo()->query("SET lc_messages = 'en_US';"); //unique key error is caught and parsed and relies on english
		
		$this->getPdo()->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Statement::class]);
	}

	/**
	 * Execute an SQL string
	 * 
	 * Should be properly escaped!
	 * {@link http://php.net/manual/en/pdo.query.php}
	 * 
	 * @param string $sql
	 * @return PDOStatement
	 */
	public function query($sql) {
		\go\core\App::get()->getDebugger()->debug($sql, Debugger::TYPE_SQL);
		return $this->getPdo()->query($sql);
	}

	/**
	 * UNLOCK TABLES explicitly releases any table locks held by the current session
	 */
	public function unlockTables() {
		return $this->getPdo()->query("UNLOCK TABLES");
	}

	private $transactionSavePointLevel = 0;

	/**
	 * Start a database transation
	 * 
	 * @return boolean
	 */
	public function beginTransaction() {
//		\go\core\App::get()->debug("Begin DB transation");		
//		\go\core\App::get()->getDebugger()->debugCalledFrom();
		if($this->transactionSavePointLevel == 0) {
			//$ret = null;
			//if (!$this->inTransaction())
			GO()->debug("START TRANSACTION", Debugger::TYPE_SQL);
			$ret = $this->getPdo()->beginTransaction();

		}else
		{
			$ret = $this->query("SAVEPOINT LEVEL".$this->transactionSavePointLevel);			
		}		
		
		$this->transactionSavePointLevel++;		
		return $ret;
	}

	/**
	 * Rollback the DB transaction
	 * 
	 * @return boolean
	 */
	public function rollBack() {
//		\go\core\App::get()->debug("Rollback DB transation");
//		\go\core\App::get()->getDebugger()->debugCalledFrom();
		
		if($this->transactionSavePointLevel == 0) {
			throw new \Exception("Not in transaction!");
		}
		
		$this->transactionSavePointLevel--;	
		if($this->transactionSavePointLevel == 0) {
			GO()->debug("ROLLBACK TRANSACTION", Debugger::TYPE_SQL);
			return $this->getPdo()->rollBack();
		}else
		{
			return $this->query("ROLLBACK TO SAVEPOINT LEVEL".$this->transactionSavePointLevel);						
		}
	}

	/**
	 * Commit the database transaction
	 * 
	 * @return boolean
	 */
	public function commit() {

//		\go\core\App::get()->debug("Commit DB transation");
//		\go\core\App::get()->getDebugger()->debugCalledFrom();
		
		if($this->transactionSavePointLevel == 0) {
			throw new \Exception("Not in transaction!");
		}
		
		$this->transactionSavePointLevel--;
		if($this->transactionSavePointLevel == 0) {
			GO()->debug("COMMIT TRANSACTION", Debugger::TYPE_SQL);
			return $this->getPdo()->commit();
		}else
		{
			return $this->query("RELEASE SAVEPOINT LEVEL".$this->transactionSavePointLevel);			
		}
	}

	/**
	 * Check if a transaction is active
	 * 
	 * @return boolean
	 */
	public function inTransaction() {
		return $this->getPdo()->inTransaction();
	}

	/**
	 * Lock the table of this model and also for the 't' alias.
	 * 
	 * The locks array should be indexed by model name and the value is an array with two optional values.
	 * THe first is a boolean that enables a write lock and the second is a table alias.
	 * 
	 * @param array $locks eg. [GO\Core\Modules\Users\Model\User::tableName() => [true, 't']]
	 *
	 * @return boolean
	 */
	public function lock($locks) {

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

		App::get()->debug($sql);
		return App::get()->getDbConnection()->query($sql);
	}

	/**
	 * Create a delete statement
	 * 
	 * @example
	 * ```
	 * $success = App::get()
	 * 				->getDbConnection()
	 * 				->delete("test_a", ['id' => 1])
	 * 				->execute();
	 * ```
	 * @param string $tableName
	 * @param Query $query
	 * @return Statement
	 */
	public function delete($tableName, $query = null) {
		$query = Query::normalize($query);

		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildDelete($tableName, $query);

		return $this->createStatement($build);
	}

	/**
	 * Create an insert statement
	 * 
	 * @example
	 * ```
	 * $data = [
	 * 		"propA" => "string 1",
	 * 		"createdAt" => new \DateTime(),
	 * 		"modifiedAt" => new \DateTime()
	 * ];
	 * 
	 * $result = App::get()
	 * 				->getDbConnection()
	 * 				->insert("test_a", $data)
	 * 				->execute();
	 * ```
	 * 
	 * Get the ID if it has an auto increment column:
	 * ```
	 * $id = App::get()->getDbConnection()->getPDO()->lastInsertId();
	 * ```
	 * 
	 * Or with an expression:
	 * 
	 * ```
	 * App::get()->getDbConnection()
	 *	->update("core_state", new \go\core\db\Expression("highestModSeq = highestModSeq + 1"), $query);
   * ````
	 * 
	 * @param string $tableName
	 * @param array|Query $data Key value array or select query
	 * @return Statement
	 */
	public function insert($tableName, $data) {

		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildInsert($tableName, $data);

		return $this->createStatement($build);
	}
	
	public function insertIgnore($tableName, $data) {

		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildInsert($tableName, $data, "INSERT IGNORE");

		return $this->createStatement($build);
	}
	
	public function replace($tableName, $data) {

		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildInsert($tableName, $data, "REPLACE");

		return $this->createStatement($build);
	}

	/**
	 * Create an update command
	 * 
	 * @example
	 * ```
	 * $data = [
	 * 		"propA" => "string 3"
	 * ];
	 * 
	 * $stmt = App::get()->getDbConnection()->update("test_a", $data, ['id' => 1]);
	 * $stmt->execute();
	 * ````
	 * 
	 * @param string $tableName
	 * @param array|Expression
	 * @param Criteria $query
	 * @return Statement
	 */
	public function update($tableName, $data, $query = null) {
		$query = Query::normalize($query);

		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildUpdate($tableName, $data, $query);

		return $this->createStatement($build);
	}
	

	/**
	 * Create a select statement. 
	 * 
	 * You don't need to use this function directly. You can select like this:
	 * 
	 * @example 
	 * ```
	 * $query = (new Query())
	 * 						->select('*')
	 * 						->from('test_a')
	 * 						->where('id', '=', 1);
	 * 
	 * 	$stmt = $query->execute();
	 * 
	 * ```
	 * 
	 * @see Query
	 * 
	 * @param Query $query
	 * @return Statement
	 */
	public function select(Query $query) {
		$queryBuilder = new QueryBuilder();
		$build = $queryBuilder->buildSelect($query);

		$stmt = $this->createStatement($build);
		call_user_func_array([$stmt, 'setFetchMode'], $query->getFetchMode());

		$stmt->setQuery($query);
		return $stmt;
	}

	/**
	 * Execute the command
	 * 
	 * @return Statement
	 * @throws PDOException
	 */
	private function createStatement($build) {
	
		$debugQueryString = $this->replaceBindParameters($build['sql'], $build['params']);
		
//		Code is useful to find where a query was made.
//		if(strpos($debugQueryString, "SELECT t.userId, t.secret, t.createdAt, t.userId AS `t.userId") === 0 ) {
//			GO()->getDebugger()->debugCalledFrom();
//		}
		//App::get()->debug($debugQueryString, Debugger::TYPE_SQL);

		$stmt = $this->getPDO()->prepare($build['sql']);
		$stmt->debugQueryString = $debugQueryString;
		foreach ($build['params'] as $p) {

			if (isset($p['value']) && !is_scalar($p['value'])) {
				throw new Exception("Invalid value " . var_export($p['value'], true));
			}
			$stmt->bindValue($p['paramTag'], $p['value'], $p['pdoType']);
		}
		return $stmt;
	}

	/**
	 * Will replace all :paramName tags with the values. Used for debugging the SQL string.
	 *
	 * @param string $sql
	 * @param string
	 */
	private function replaceBindParameters($sql, $bindParams) {
		$binds = [];
		foreach ($bindParams as $p) {
			if (is_string($p['value']) && !mb_check_encoding($p['value'], 'utf8')) {
				$queryValue = "[NON UTF8 VALUE]";
			} else {
				$queryValue = var_export($p['value'], true);
			}
			$binds[$p['paramTag']] = $queryValue;
		}

		//sort so $binds :param1 does not replace :param11 first.
		krsort($binds);

		foreach ($binds as $tag => $value) {
			$sql = str_replace($tag, $value, $sql);
		}

		return $sql;
	}
}
