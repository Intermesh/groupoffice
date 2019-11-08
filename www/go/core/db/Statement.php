<?php
namespace go\core\db;
use go\core\ErrorHandler;

/**
 * PDO Statement
 * 
 * Represents a prepared statement and, after the statement is executed, an
 * associated result set.
 */
class Statement extends \PDOStatement implements \JsonSerializable {
	
	private $query;
	
	public function jsonSerialize() {
		return $this->fetchAll();
	}
	
	/**
	 * Set's the select query object
	 * 
	 * @param \go\core\db\Query $query
	 */
	public function setQuery(Query $query) {
		$this->query = $query;
	}
	
	/**
	 * Get query object that was used to create this statement.
	 * 
	 * Only available for select queries
	 *  
	 * @return Query
	 */
	public function getQuery() {
		return $this->query;
	}
	
	private $build;
	
	/**
	 * Set's the build array produced by QueryBuilder. Only used to cast this object
	 * to string when debugging.
	 * 
	 * @param array $build
	 */
	public function setBuild(array $build) {
		$this->build = $build;
	}
	
	public function __toString() {
		try {
			return QueryBuilder::debugBuild($this->build);
		} catch(\Exception $e) {
			ErrorHandler::logException($e);
			return "Error: Could not convert SQL to string: " . $e->getMessage();
		}
	}

	/**
	 * Executes a prepared statement
	 * 
	 * @param $input_parameters An array of values with as many elements as there are bound parameters in the SQL statement being executed. All values are treated as PDO::PARAM_STR.
	 * 
	 * Multiple values cannot be bound to a single parameter; for example, it is not allowed to bind two values to a single named parameter in an IN() clause.
	 * 
	 * Binding more values than specified is not possible; if more keys exist in input_parameters than in the SQL specified in the PDO::prepare(), then the statement will fail and an error is emitted.
	 * 
	 * @return bool
	 */
	public function execute($input_parameters = null)
	{
		try {
			
			$ret = parent::execute($input_parameters);
			if(go()->getDebugger()->enabled && go()->getDbConnection()->debug && isset($this->build)) {
				$duration  = number_format((go()->getDebugger()->getMicrotime() * 1000) - ($this->build['start'] * 1000), 2);
				go()->debug(QueryBuilder::debugBuild($this->build).' ('.$duration.'ms)', 3);			
			}
			if(!$ret) {
				go()->error("SQL FAILURE: " . $this);
			}
			return $ret;
		}
		catch(\Exception $e) {
			go()->error("SQL FAILURE: " . $this);
			throw $e;
		}
	}
	
}
