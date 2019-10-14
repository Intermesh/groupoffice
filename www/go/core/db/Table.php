<?php

namespace go\core\db;

use Exception;
use go\core\App;
use go\core\orm\Record;
use PDO;

/**
 * Class that fetches database column information for the ActiveRecord.
 * It detects the length, type, default and required attribute etc.
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Table {

	private static $cache = [];
	
	/**
	 * Get a table instance
	 * 
	 * @param string $name
	 * @return self
	 */
	public static function getInstance($name) {
		if(!isset(self::$cache[$name])) {
			self::$cache[$name] = new Table($name);
		}
		
		return self::$cache[$name];	
	}
	
	public static function destroyInstances() {
		self::$cache = [];
	}
	
	private $name;
	protected $columns;	
	
	public function __construct($name) {
		$this->name = $name;
		
		$this->init();
	}	
	
	/**
	 * Get's the name of the table
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	private function getCacheKey() {
		return 'dbColumns_' . $this->name;
	}

	/**
	 * Clear the columns cache
	 */
	public function clearCache() {
		App::get()->getCache()->delete($this->getCacheKey());
	}

	private function init() {
		
		if (isset($this->columns)) {
			return $this->columns;
		}
		
		$cacheKey = $this->getCacheKey();

		if (($cache = App::get()->getCache()->get($cacheKey))) {
			$this->columns = $cache['columns'];
			$this->pk = $cache['pk'];
			return;
		}	
		
		$this->columns = [];

		$sql = "SHOW FULL COLUMNS FROM `" . $this->name . "`;";
//		\go\core\App::get()->debug($sql, 'sql');
		
		$stmt = App::get()->getDbConnection()->getPDO()->query($sql);
		while ($field = $stmt->fetch()) {
			$this->columns[$field['Field']] = $this->createColumn($field);
		}

		$this->processIndexes($this->name);

		App::get()->getCache()->set($cacheKey, ['columns' => $this->columns, 'pk' => $this->pk]);


		return;
	}
	
	/**
	 * A column name may not have the name of a Record property name.
	 * 
	 * @param type $fieldName
	 * @throws Exception
	 */
	private function checkReservedName($fieldName) {
		if(strpos($fieldName, '@') !== false) {
			throw new \Exception("The @ char is reserved for framework usage.");
		}
		
		if(property_exists(Record::class, $fieldName)) {
			throw new \Exception("The name '$fieldName' is reserved. Please choose another column name.");
		}
	}
	
	private $pk = [];

	private function createColumn($field) {
		
		$this->checkReservedName($field['Field']);
		
			
		$c = new Column();
		$c->table = $this;
		$c->name = $field['Field'];
		$c->pdoType = PDO::PARAM_STR;
		$c->required = false;
		$c->default = $field['Default'];
		$c->comment = $field['Comment'];
		$c->nullAllowed = strtoupper($field['Null']) == 'YES';
		$c->autoIncrement = strpos($field['Extra'], 'auto_increment') !== false;
		$c->trimInput = false;
		
		preg_match('/(.*)\(([1-9].*)\)/', $field['Type'], $matches);		
		if ($matches) {
			$c->length  = intval($matches[2]);
			$c->dbType = strtolower($matches[1]);
		} else {
			$c->dbType = strtolower($field['Type']);
			$c->length = 0;
		}
		
		if($c->default == 'CURRENT_TIMESTAMP') {
			throw new \Exception("Please don't use CURRENT_TIMESTAMP as default mysql value. It's only supported in MySQL 5.6+");
		}
		
		switch ($c->dbType) {
			case 'int':
			case 'tinyint':
			case 'bigint':
				if ($c->length == 1 && $c->dbType == 'tinyint') {
					$c->pdoType = PDO::PARAM_BOOL;
					$c->default = !isset($field['Default']) ? null : (bool) $c->default;
				} else {
					$c->pdoType = PDO::PARAM_INT;
					$c->default = $c->autoIncrement || !isset($field['Default']) ? null : intval($c->default);
				}

				break;

			case 'float':
			case 'double':
			case 'decimal':
				$c->pdoType = PDO::PARAM_STR;
				$c->length = 0;
				$c->default = $c->default == null ? null : floatval($c->default);
				break;
			
			case 'datetime':
				if($c->default == 'CURRENT_TIMESTAMP') {
					$c->default = date(Column::DATETIME_FORMAT);
				}
				break;
			case 'date':
				if($c->default == 'CURRENT_TIMESTAMP') {
					$c->default = date(Column::DATE_FORMAT);
				}				
				break;
			case 'binary':
				$c->pdoType = PDO::PARAM_LOB;
				break;
			default:				
				$c->trimInput = true;
				break;			
		}

		$c->required = is_null($c->default) && $field['Null'] == 'NO' && strpos($field['Extra'], 'auto_increment') === false;

		if ($field['Field'] == 'createdAt' || $field['Field'] == 'modifiedAt' || $field['Field'] == 'createdBy' || $field['Field'] == 'modifiedBy') {
			//don't validate because they will be set by the Record
			$c->required = false;
		}

		return $c;
	}

	private function processIndexes($tableName) {
		$query = "SHOW INDEXES FROM `" . $tableName . "`";

		$unique = [];

		//group keys;
		// ['keyName' => ['col1', 'col2']];

		$stmt = App::get()->getDbConnection()->getPDO()->query($query);
		while ($index = $stmt->fetch()) {

			if ($index['Key_name'] === 'PRIMARY') {

				$this->columns[$index['Column_name']]->primary = true;
				$this->pk[] = $index['Column_name'];
				//don't validate uniqueness on primary key
				continue;
			}

			if ($index['Non_unique'] === "0") {
				if (!isset($unique[$index['Key_name']])) {
					$unique[$index['Key_name']] = [];
				}

				$unique[$index['Key_name']][] = $index['Column_name'];
			}
		}

		foreach ($unique as $cols) {
			foreach ($cols as $colName) {
				$this->columns[$colName]->unique = $cols;
			}
		}
	}

	
	
	/**
	 * Get all column names
	 * 
	 * @param string[]
	 */
	public function getColumnNames() {
		return array_keys($this->columns);
	}
	
	
	/**
	 * Check if column exists
	 * 
	 * @param string $name
	 * @return boolean
	 */
	public function hasColumn($name) {
		return isset($this->columns[$name]);
	}
	
	/**
	 * Get a column
	 * 
	 * @param string $name
	 * @return Column
	 */
	public function getColumn($name) {
		if(!isset($this->columns[$name])) {
			return null;
		}
		
		return $this->columns[$name];
	}
	
	/**
	 * Get the columns of the table
	 * 
	 * The keys of the array are the column names.
	 * 
	 * 
	 * @return Column[]
	 */
	public function getColumns() {
		return $this->columns;
	}
	
	/**
	 * Get the auto incrementing column
	 * 
	 * @return Column|boolean
	 */
	public function getAutoIncrementColumn() {
		foreach($this->getColumns() as $col) {
			if($col->autoIncrement) {
				return $col;
			}
		}
		
		return false;
	}
	
	/**
	 * The primary key columns
	 * 
	 * This value is auto detected from the database. 
	 *
	 * @return string[] eg. ['id']
	 */
	public function getPrimaryKey() {		
		return $this->pk;
	}
	
	/**
	 * Truncate the table
	 * 
	 * @return boolean
	 */
	public function truncate() {
		return GO()->getDbConnection()->query("TRUNCATE TABLE ".$this->getName())->execute();
	}

}
