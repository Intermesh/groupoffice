<?php
namespace go\modules\core\customfields\datatype;

use go\core\util\ClassFinder;
use go\modules\core\customfields\model\Field;

/**
 * Abstract data type class
 * 
 * @todo Implement all types when all of custom fields will be refactored
 * 
 */
abstract class Base {
	
	/**
	 * Get column definition for SQL
	 * 
	 * @return string
	 */
	public function getFieldSQL() {
		return "VARCHAR(".($this->field->getOption('maxLength') ?? 190).") DEFAULT " . GO()->getDbConnection()->getPDO()->quote($this->field->getDefault() ?? "NULL");
	}
	
	/**
	 *
	 * @var Field 
	 */
	protected $field;
	
	public function __construct(Field $field) {
		$this->field = $field;
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
			throw new \Exception("Custom field type '$name' not found");
		}
		
		return $all[$name];
	}
}
