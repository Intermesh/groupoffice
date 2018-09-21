<?php
namespace go\modules\core\customfields\datatype;

use Exception;
use GO;
use go\core\data\Model;
use go\core\db\Table;
use go\core\db\Utils;
use go\core\ErrorHandler;
use go\core\util\ClassFinder;
use go\modules\core\customfields\model\Field;


/**
 * Abstract data type class
 * 
 * @todo Implement all types when all of custom fields will be refactored
 * 
 */
abstract class Base extends Model {
	
	/**
	 *
	 * @var Field 
	 */
	protected $field;
	
	public function __construct(Field $field) {
		$this->field = $field;
	}
	
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	protected function getFieldSQL() {
		return "VARCHAR(".($this->field->getOption('maxLength') ?? 190).") DEFAULT " . GO()->getDbConnection()->getPDO()->quote($this->field->getDefault() ?? "NULL");
	}
	
	public function onFieldSave() {
		
		$table = $this->field->tableName();
		$fieldSql = $this->getFieldSQL();
		
		$quotedDbName = Utils::quoteColumnName($this->field->databaseName);
	
		if ($this->field->isNew()) {
			$sql = "ALTER TABLE `" . $table . "` ADD " . $quotedDbName . " " . $fieldSql . ";";
			GO()->getDbConnection()->query($sql);
			if($this->field->getUnique()) {
				$sql = "ALTER TABLE `" . $table . "` ADD UNIQUE(". $quotedDbName  . ");";
				GO()->getDbConnection()->query($sql);
			}			
		} else {
			
			
			$oldName = $this->field->isModified('databaseName') ? $this->field->getOldValue("databaseName") : $this->field->databaseName;
			$col = Table::getInstance($table)->getColumn($oldName);
			
			$sql = "ALTER TABLE `" . $table . "` CHANGE " . Utils::quoteColumnName($oldName) . " " . $quotedDbName . " " . $fieldSql;
			GO()->getDbConnection()->query($sql);
			
			if($this->field->getUnique() && !$col->unique) {
				$sql = "ALTER TABLE `" . $table . "` ADD UNIQUE(". $quotedDbName  . ");";
				GO()->getDbConnection()->query($sql);
			} else if(!$this->field->getUnique() && $col->unique) {
				$sql = "ALTER TABLE `" . $table . "` DROP INDEX " . $quotedDbName;
				GO()->getDbConnection()->query($sql);
			}
		}
		
		Table::getInstance($table)->clearCache();
		
		return true;
	}

	public function onFieldDelete() {
		$table = $this->field->tableName();
		$sql = "ALTER TABLE `" . $table . "` DROP " . Utils::quoteColumnName($this->field->databaseName) ;

		try {
			GO()->getDbConnection()->query($sql);
		} catch (Exception $e) {
			ErrorHandler::logException($e);
		}
		
		Table::getInstance($table)->clearCache();
		
		return true;
	}
					
	public function apiToDb($value, $values) {
		return $value;
	}
	
	public function dbToApi($value, $values) {
		return $value;
	}
	
	public static function getName() {
		$cls = static::class;
		return substr($cls, strrpos($cls, '\\') + 1);
	}
	
	/**
	 * Get all field types
	 * 
	 * @return string[] eg ['functionField' => "go\modules\core\customfields\datatype\FunctionField"];
	 */
	public static function findAll() {
		$classFinder = new ClassFinder();
		$classes = $classFinder->findByParent(self::class);
		
		$types = [];
		
		foreach($classes as $class) {
			$types[$class::getName()] = $class;
		}
		
		return $types;		
	}
	
	/**
	 * Find the class for a type
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function findByName($name) {
		
		//for compatibility with old version
		//TODO remove when refactored completely
		$pos = strrpos($name, '\\');
		if($pos !== false) {
			$name = lcfirst(substr($name, $pos + 1));
		}
		$all = static::findAll();

		if(!isset($all[$name])) {
			throw new Exception("Custom field type '$name' not found");
		}
		
		return $all[$name];
	}
}
