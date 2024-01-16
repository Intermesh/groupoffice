<?php
namespace go\core\db;

use Countable;
use GO\Base\Db\ActiveRecord;
use go\core\data\ArrayableInterface;
use go\core\data\Model;
use go\core\ErrorHandler;
use Exception;
use Iterator;
use JsonSerializable;
use PDO;
use PDOException;
use PDOStatement;
use stdClass;

/**
 * PDO Statement
 * 
 * Represents a prepared statement and, after the statement is executed, an
 * associated result set.
 *
 * @template T
 * @implements Iterator<T>
 */
class Statement implements JsonSerializable, ArrayableInterface, Countable, Iterator {


	private PDOStatement $pdoStmt;

	/**
	 * @var ?class-string<T>
	 */
	private string|null $modelClassName = null;
	private array|null $modelConstructorArgs = null;

	public function __construct(PDOStatement $stmt)
	{
		$this->pdoStmt = $stmt;
	}

	/**
	 * The model type this statement result returns.
	 *
	 * @var ActiveRecord
	 * @deprecated Only needed for old ActiveRecord
	 */
	public $model;

	/**
	 * Parameters  that were passed to \GO\BaseDb\activeRecord::find()
	 *
	 * @var array
	 *
	 * @deprecated Only needed for old ActiveRecord
	 */
	public $findParams;

	/**
	 * If the statement was returned by a relational query eg. $model->relationName() then this
	 * is set to the relation name.
	 * @deprecated Only needed for old ActiveRecord
	 * @var String
	 */
	public $relation;

	private $query;
	private mixed $current;
	private int $cursor = 0;

	public function jsonSerialize(): mixed
	{
		return $this->fetchAll();
	}

	public function toArray(array $properties = null): array|null
	{
		return $this->fetchAll();
	}

	/**
	 * Set's the select query object
	 *
	 * @param Query $query
	 */
	public function setQuery(Query $query): void
	{
		$this->query = $query;
	}

	/**
	 * Get query object that was used to create this statement.
	 *
	 * Only available for select queries
	 *
	 * @return Query
	 */
	public function getQuery(): Query
	{
		return $this->query;
	}

	private array $build;

	/**
	 * Set's the build array produced by QueryBuilder. Only used to cast this object
	 * to string when debugging.
	 *
	 * @param array $build
	 */
	public function setBuild(array $build): void
	{
		$this->build = $build;
	}

	public function __toString() {
		try {
			if(!isset($this->build)) {
				return "Can't render SQL. Please check debug log.";
			}
			return QueryBuilder::debugBuild($this->build);
		} catch(Exception $e) {
			ErrorHandler::logException($e);
			return "Error: Could not convert SQL to string: " . $e->getMessage();
		}
	}

	/**
	 * Output query to debugger
	 *
	 * @return $this
	 */
	public function debug(): Statement
	{
		if(go()->getDebugger()->enabled) {
			go()->debug((string)$this);
		}

		return $this;
	}

	public function bindValue($param, $value, $type = PDO::PARAM_STR) : bool
	{
		$param = $this->build['paramMap'][$param] ?? $param;

		return $this->pdoStmt->bindValue($param, $value, $type);
	}

	/**
	 * Executes a prepared statement
	 *
	 * @param array|null $params An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as PDO::PARAM_STR.
	 *
	 * Multiple values cannot be bound to a single parameter; for example, it is not allowed to bind two values to a single named parameter in an IN() clause.
	 *
	 * Binding more values than specified is not possible; if more keys exist in input_parameters than in the SQL specified in the PDO::prepare(), then the statement will fail and an error is emitted.
	 *
	 * @return bool Always returns true but must be compatible with PHP function
	 * @throws PDOException
	 */
	public function execute(array $params = null): bool
	{
		try {

			if(isset($params) && isset($this->build['params'])) {
				$keys = array_keys($this->build['params']);
				foreach($params as $v) {
					$key = array_shift($keys);
					$this->build[$key] = $v;
				}
			}

			if(go()->getDbConnection()->debug && isset($this->build) && go()->getDebugger()->enabled) {
				$sql = QueryBuilder::debugBuild($this->build);
				go()->debug(str_replace(["\n","\t"], [" ", ""], $sql) , 5);
			}

			$this->pdoStmt->execute($params);

			if(go()->getDbConnection()->debug && isset($this->build) && go()->getDebugger()->enabled) {
				$duration = number_format((go()->getDebugger()->getMicrotime() * 1000) - ($this->build['start'] * 1000), 2);
				go()->debug("Query took " . $duration . "ms");
			}

			return true;
		}
		catch(PDOException $e) {
			go()->error("SQL FAILURE: " . $this);
			throw $e;
		}
	}

	public function count(): int
	{
		return $this->rowCount();
	}

	/**
	 * @return T
	 */
	public function current(): mixed
	{
		return $this->current;
	}

	public function next(): void
	{
		$this->current = $this->fetch();
		$this->cursor++;
	}

	public function key(): mixed
	{
		return $this->cursor;
	}

	public function valid(): bool
	{
		return $this->cursor <= $this->count();
	}

	public function rewind(): void
	{
		if ($this->cursor > 0) {
			throw new Exception('Rewind is not possible');
		}
		$this->next();
	}

	/**
	 * Set the default fetch mode for this statement
	 * @link https://php.net/manual/en/pdostatement.setfetchmode.php
	 * @param int $mode <p>
	 * The fetch mode must be one of the PDO::FETCH_* constants.
	 * </p>
	 * @param null|string|object $className [optional] <p>
	 * Class name or object
	 * </p>
	 * @param array|null $params [optional] <p> Constructor arguments. </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */

	public function setFetchMode(int $mode, null|string|object|int $className = null, array $params = null): bool
	{
		$args = [$mode];
		if(isset($className)) {
			$args[] = $className;
		}
		if(isset($params)) {
			$args[] = $params;
		}
		$this->modelClassName = null;
		$this->modelConstructorArgs = null;

		return $this->pdoStmt->setFetchMode(...$args);
	}

	/**
	 * @param class-string<Model>  $modelClassName
	 */
	public function fetchTypedModel(string $modelClassName, array $constructorArgs = []): static
	{
		$this->setFetchMode(PDO::FETCH_ASSOC);
		$this->modelClassName = $modelClassName;
		$this->modelConstructorArgs = $constructorArgs;
		return $this;
	}

	/**
	 * Fetches the next row from a result set
	 *
	 * @return T
	 */

	public function fetch(): mixed {
		if(!isset($this->modelClassName)) {
			return $this->pdoStmt->fetch();
		} else{
			$arr = $this->pdoStmt->fetch();

			if(!$arr) {
				return false;
			}

			//todo correct constuctor args
			$model = new $this->modelClassName(...$this->modelConstructorArgs);
			$model->populate($arr);

			return $model;
		}
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
	 * Binds a parameter to the specified variable name
	 * @link https://php.net/manual/en/pdostatement.bindparam.php
	 * @param mixed $param <p>
	 * Parameter identifier. For a prepared statement using named
	 * placeholders, this will be a parameter name of the form
	 * :name. For a prepared statement using
	 * question mark placeholders, this will be the 1-indexed position of
	 * the parameter.
	 * </p>
	 * @param mixed &$var <p>
	 * Name of the PHP variable to bind to the SQL statement parameter.
	 * </p>
	 * @param int $type [optional] <p>
	 * Explicit data type for the parameter using the PDO::PARAM_*
	 * constants.
	 * To return an INOUT parameter from a stored procedure,
	 * use the bitwise OR operator to set the PDO::PARAM_INPUT_OUTPUT bits
	 * for the <i>data_type</i> parameter.
	 * </p>
	 * @param int $maxLength [optional] <p>
	 * Length of the data type. To indicate that a parameter is an OUT
	 * parameter from a stored procedure, you must explicitly set the
	 * length.
	 * </p>
	 * @param mixed $driverOptions [optional] <p>
	 * </p>
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */

	public function bindParam(
		int|string $param,
		mixed &$var,
		int $type = PDO::PARAM_STR,
		int $maxLength = null,
		mixed $driverOptions = null
	): bool {
		return $this->pdoStmt->bindParam($param, $var, $type, $maxLength, $driverOptions);
	}



	/**
	 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.1.0)<br/>
	 * Returns the number of rows affected by the last SQL statement
	 * @link https://php.net/manual/en/pdostatement.rowcount.php
	 * @return int the number of rows.
	 */
	public function rowCount(): int {
		return $this->pdoStmt->rowCount();
	}

	/**
	 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.9.0)<br/>
	 * Returns a single column from the next row of a result set
	 * @link https://php.net/manual/en/pdostatement.fetchcolumn.php
	 * @param int $column [optional] <p>
	 * 0-indexed number of the column you wish to retrieve from the row. If
	 * no value is supplied, <b>PDOStatement::fetchColumn</b>
	 * fetches the first column.
	 * </p>
	 * @return mixed Returns a single column from the next row of a result
	 * set or FALSE if there are no more rows.
	 * </p>
	 * <p>
	 * There is no way to return another column from the same row if you
	 * use <b>PDOStatement::fetchColumn</b> to retrieve data.
	 */

	public function fetchColumn(int $column = 0): mixed {
		return $this->pdoStmt->fetchColumn($column);
	}

	/**

	 * Returns an array containing all of the result set rows
	 * @return T[]|false <b>PDOStatement::fetchAll</b> returns an array containing
	 * all of the remaining rows in the result set. The array represents each
	 * row as either an array of column values or an object with properties
	 * corresponding to each column name.
	 * An empty array is returned if there are zero results to fetch, or false on failure.
	 * </p>
	 * <p>
	 * Using this method to fetch large result sets will result in a heavy
	 * demand on system and possibly network resources. Rather than retrieving
	 * all of the data and manipulating it in PHP, consider using the database
	 * server to manipulate the result sets. For example, use the WHERE and
	 * ORDER BY clauses in SQL to restrict results before retrieving and
	 * processing them with PHP.
	 */

	public function fetchAll(
	): array {
		if(!isset($this->modelClassName)) {
			return $this->pdoStmt->fetchAll();
		} else {
			$arr = [];
			while($r = $this->fetch()) {
				$arr[] = $r;
			}
			return $arr;
		}
	}

	/**
	 * @template TObj
	 *
	 * (PHP 5 &gt;= 5.1.0, PHP 7, PECL pdo &gt;= 0.2.4)<br/>
	 * Fetches the next row and returns it as an object.
	 * @link https://php.net/manual/en/pdostatement.fetchobject.php
	 * @param class-string<TObj>|null $class [optional] <p>
	 * Name of the created class.
	 * </p>
	 * @param array $constructorArgs [optional] <p>
	 * Elements of this array are passed to the constructor.
	 * </p>
	 * @return TObj|stdClass|null an instance of the required class with property names that
	 * correspond to the column names or <b>FALSE</b> on failure.
	 */

	public function fetchObject(
		string|null $class = "stdClass",
		array $constructorArgs = []
	): object|false {
		return $this->pdoStmt->fetchObject($class, $constructorArgs);
	}

	/**
	 *
	 * Closes the cursor, enabling the statement to be executed again.
	 * Returns:
	 * bool TRUE on success or FALSE on failure.
	 * Links:
	 * https://php.net/manual/en/pdostatement.closecursor.php
	 */
	public function closeCursor(): bool
	{
		return $this->pdoStmt->closeCursor();
	}
}
