<?php

namespace go\core\db;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use go\core\util\DateTime as GoDateTime;

/**
 * Represents a Record database column attribute.
 * 
 * <p>Example:</p>
 * ```````````````````````````````````````````````````````````````````````````
 * $model = User::findByPk(1);
 * echo $model->getColumn('username')->length;
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * If you want to override a column parameter then override Record::getColumns():
 * 
 * `````````````````````````````````````````````````````````````````````````````
 * public static function getTable() {
 * 		$table = parent::getTable();		
 * 		$table->getColumn('password')->trimInput = false;
 * 		
 * 		return $table;		
 * 	}
 * `````````````````````````````````````````````````````````````````````````````
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Column {

	/**
	 * false if non unique or an array of columns that should be unique in combination with this column.
	 * 
	 * @var bool|array 
	 */
	public $unique = false;

	/**
	 * Is this part of the primary key
	 * 
	 * @var bool  
	 */
	public $primary = false;

	/**
	 * Name of the column
	 * 
	 * @var string 
	 */
	public $name;

	/**
	 * Length of the column
	 * 
	 * @var int
	 */
	public $length;

	/**
	 * True if null is allowed
	 * 
	 * @var boolean 
	 */
	public $nullAllowed;

	/**
	 * True if this column auto increments
	 * 
	 * @var boolean
	 */
	public $autoIncrement = false;

	/**
	 * Field type in the database without length in lowercase.
	 * 
	 * eg. "varchar";
	 * 
	 * @var string 
	 */
	public $dbType;

	/**
	 * PDO Type
	 * 
	 * @var int 
	 */
	public $pdoType;

	/**
	 * True if field is required
	 * 
	 * @var boolean 
	 */
	public $required;

	/**
	 * Default value of the column
	 * 
	 * @var mixed 
	 */
	public $default;

	/**
	 * The column comment
	 * 
	 * @var string 
	 */
	public $comment;

	/**
	 * Trim white spaces on input
	 * 
	 * @var boolean 
	 */
	public $trimInput = false;
	
	/**
	 *
	 * @var Table
	 */
	public $table;


	/**
	 * MySQL Data type in uppercase with length
	 * 
	 * eg. VARCHAR(100)
	 * 
	 * @var string
	 */
	public $dataType = "";

	/**
	 * The MySQL database datetime format.
	 */
	const DATETIME_FORMAT = "Y-m-d H:i:s";

	/**
	 * The MySQL database date format.
	 */
	const DATE_FORMAT = "Y-m-d";

	/**
	 * Get the SQL string to add / alter this field.
	 * 
	 * eg. "tinyint(1) NOT NULL DEFAULT '0'"
	 * 
	 * @return string
	 */
	public function getCreateSQL() {
		$sql = $this->dataType;		
		
		if(!$this->nullAllowed) {
			$sql .= ' NOT NULL';
		} else
		{
			$sql .= ' NULL';
		}
		
		if($this->autoIncrement) {
			$sql .= ' AUTO_INCREMENT';
		} else if(isset($this->default)) {
			
			if(is_bool($this->default)) {
				$default = $this->default ? "TRUE" : "FALSE";
			} else
			{
				$default = '\'' . str_replace('\'', '\\\'', $this->default). '\'';
			}
			
			$sql .= ' DEFAULT '.$default;
		}
		
		return $sql;
	}

  /**
   * Input formatting for the database.
   * Currently only used for date fields because we want ISO 8601 for I/O.
   *
   * @param mixed $value
   * @return mixed
   * @throws Exception
   */
	public function normalizeInput($value) {
		if (!isset($value)) {
			return null;
		}
		
		switch ($this->dbType) {
			case 'datetime':
				if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
					return $value;
				} else {
					$dt = new GoDateTime($value);
					$dt->setTimezone(new DateTimeZone("UTC")); //UTC
					return $dt;
				}

			case 'date':
				//make sure date is formatted correctly
				if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
					return $value;
				} else {
					$dt = new GoDateTime($value, new DateTimeZone("UTC"));
					$dt->hasTime = false;
					return $dt;
				}
				
			default:
				if ($this->trimInput) {
					
					if(!is_string($value) && !is_numeric($value)) {	 //is_numeric should be gone but gave a problem with custom function fields
						throw new Exception("No string given for ".$this->name);						
					}
					
					$value = trim($value);
				}

				return $value;				
		}
	}
	
	public function castToDb($value) {
		if (!isset($value)) {
			return null;
		}
		
		switch ($this->dbType) {
			case 'datetime':				
				return $value->format(self::DATETIME_FORMAT);

			case 'date':
				return $value->format(self::DATE_FORMAT);
				
			default:
				return $value;
		}
	}

  /**
   * Output formatting for the database.
   * Currently only used for date fields because we want ISO 8601 for I/O.
   *
   * @param mixed $value
   * @return mixed
   * @throws Exception
   */
	public function castFromDb($value) {

		if (!isset($value)) {
			return null;
		}
		switch ($this->dbType) {
			case 'int':
			case 'tinyint':
			case 'bigint':
				if ($this->length === 1) {
					//Boolean fields in mysql are listed at tinyint(1);
					return (bool) $value;
				}
				return $value;


			case 'decimal':
				return doubleval($value);

			case 'date':
			case 'datetime':
				//Work around date problem
				if($value == "0000-00-00") {
					return null;
				}

				if(!($value instanceof GoDateTime)) {
					$value = new GoDateTime($value, new DateTimeZone("UTC"));
				}

				$value->hasTime = $this->dbType == 'datetime';

				return $value;

			default:
				return $value;
		}
	}
	
	/**
	 * Get the table
	 * 
	 * @return Table
	 */
	public function getTable() {
		return $this->table;
	}

}
