<?php
namespace go\core\db;

use Exception;
use go\core\App;
use go\core\Debugger;
use PDO;
use PDOException;
use PDOStatement;

class Command {
	
	const TYPE_INSERT = 0;
	const TYPE_UPDATE = 1;
	const TYPE_DELETE = 2;
	const TYPE_SELECT = 4;
	const TYPE_INSERT_IGNORE = 5;
	
	/**
	 *
	 * @var int 
	 */
	private $type;
	
	/**
	 * Table to perform command on
	 * 
	 * @var string 
	 */
	private $tableName;
	
	/**
	 * Command data
	 * 
	 * @var mixed 
	 */
	private $data;
	
	private $sql;
	
	private $params;
	
	
	
	
	
	/**
	 * Create select command
	 * 
	 * @param Criteria $query
	 * @return $this
	 */
	public function select(Query $query) {		
		$this->type = self::TYPE_SELECT;
		$this->query = $query;
		
		return $this;
	}
	
	
	
	public function __toString() {
			
		return $this->toString();
	}
	
	/**
	 * Build SQL string and replace bind parameters for debugging purposes
	 * 
	 * @return string
	 */
	public function toString() {
		$build = $this->build();
		
		return $this->replaceBindParameters($build['sql'], $build['params']);		
	}
	
	/**
	 * Builds the SQL and bind parameters
	 * 
	 * @return array ['sql' => 'SELECT...', 'params' => [':ifw1' => 'value']]
	 * @throws Exception
	 */
	public function build($prefix = '') {
		
		$queryBuilder = new QueryBuilder();
		switch($this->type) {
			case self::TYPE_INSERT:				
				return  $queryBuilder->buildInsert($this->tableName, $this->data);
				
			case self::TYPE_INSERT_IGNORE:				
				return  $queryBuilder->buildInsert($this->tableName, $this->data, true);
				
			case self::TYPE_UPDATE:				
				return $queryBuilder->buildUpdate($this->tableName, $this->data, $this->query);
				
			case self::TYPE_DELETE:				
				return $queryBuilder->buildDelete($this->tableName, $this->query);
				
			case self::TYPE_SELECT:			
				return $queryBuilder->buildSelect($this->query, $prefix);
			
			default:				
				throw new Exception("Please call insert, update or delete first");
		}
	}
	
	/**
	 * Execute the command
	 * 
	 * @return PDOStatement
	 * @throws PDOException
	 */
	public function execute() {		
	
		$build = $this->build();
		
		App::get()->debug($this->replaceBindParameters($build['sql'], $build['params']), Debugger::TYPE_SQL, 3);
		
		try {
			
			$binds = [];
			$stmt = App::get()->getDbConnection()->getPDO()->prepare($build['sql']);

			foreach ($build['params'] as $p) {
				
				if(!is_scalar($p['value'])) {
					throw new \Exception("Invalid value ".var_export($p['value'], true));
}
				
				$binds[$p['paramTag']] = $p['value'];
				$stmt->bindValue($p['paramTag'], $p['value'], $p['pdoType']);
			}

			if($this->type == self::TYPE_SELECT) {
				if (!$this->query->getFetchMode()) {
					$stmt->setFetchMode(PDO::FETCH_ASSOC);
				} else {
					call_user_func_array([$stmt, 'setFetchMode'], $this->query->getFetchMode());
				}
			}
			
			if(!$stmt->execute()) {
				throw new \Exception("Failed to execute SQL command: ".$this->replaceBindParameters($build['sql'], $build['params']));
			}

		} catch (Exception $e) {			
			App::get()->debug("FAILED SQL: ".$this->replaceBindParameters($build['sql'], $build['params']));
			
			throw $e;
		}
		
		return $stmt;
	}	
}
