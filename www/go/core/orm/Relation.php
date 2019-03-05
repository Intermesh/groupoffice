<?php

namespace go\core\orm;

use Exception;
use go\core\db\Query;

/**
 * Relation class
 * 
 * Defines a relation from one model to another. * 
 */
class Relation {

	/**
	 * Indicates if this relation is one to many
	 * 
	 * @var boolean 
	 */
	public $many = true;

	/**
	 * The name of the relation
	 * 
	 * @var string 
	 */
	public $name;

	/**
	 * The class name of the {@see Property} or {@see Entity} this relation points to.
	 * 
	 * @var string 
	 */
	public $entityName;

	/**
	 * Associative array with key map
	 * 
	 * ```
	 * ['fromColumn' => 'toColumn']
	 * ```
	 * 
	 * @var array 
	 */
	public $keys;

	/**
	 * Constructor
	 * 
	 * @param string $name The name of the relation
	 * @param string $entityName The class name of the {@see Property} this relation points to.
	 * @param array $keys Associative array with key map
	 * ```
	 * ['fromColumn' => 'toColumn']
	 * ```
	 * 
	 * @param boolean $many Indicates if this relation is one to many
	 */
	public function __construct($name, $entityName, array $keys, $many = false) {
		$this->name = $name;
		
		if(!is_subclass_of($entityName, Property::class, true)) {
			throw new \Exception($entityName . ' must extend '. Property::class);
		}
		
		if(is_subclass_of($entityName, Entity::class, true)) {
			throw new \Exception($entityName . ' may not be an '. Entity::class .'. Only '. Property::class .' objects can be mapped.');
		}
		
		$this->entityName = $entityName;
		$this->keys = $keys;
		$this->many = $many;
	}

	/**
	 * Normalizes input for related properties. A key value array or an object 
	 * may be given to a relation.
	 * 
	 * @param static|array $value
	 * @return \static
	 * @throws Exception
	 */
	public function normalizeInput($value) {

		if ($this->many) {
			foreach ($value as &$v) {
				$v = $this->internalNormalizeInput($v);
			}
			return $value;
		} else {
			return $this->internalNormalizeInput($value);
		}
	}

	private function internalNormalizeInput($value) {
		$cls = $this->entityName;
		if ($value instanceof $cls) {
			return $value;
		}

		if (is_array($value)) {
			$o = new $cls;
			$o->setValues($value);

			return $o;
		} else if (is_null($value)) {
			return null;
		} else {
			throw new Exception("Invalid value given to relation '" . $this->name . "'. Should be an array or an object of type '" . $this->entityName . "': " . var_export($value, true));
		}
	}
}
