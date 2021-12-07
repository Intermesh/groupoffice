<?php

namespace go\core\orm;

use go\core\db\Table;
use InvalidArgumentException;

/**
 * Relation class
 * 
 * Defines a relation from one model to another. * 
 */
class Relation {

	const TYPE_HAS_ONE = 0;
	const TYPE_ARRAY = 1;
	const TYPE_MAP = 2;
	const TYPE_SCALAR = 3;


	/**
	 * The name of the relation
	 * 
	 * @var string 
	 */
	public $name;

	/**
	 * The class name of the {@see Property} this relation points to.
	 *
	 * @var class-string<Property>
	 */
	public $propertyName;

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

	public $tableName;

	/**
	 * Type of relation. See TYPE_* constants.
	 */
	public $type;


	/**
	 * Only for has one relations. Auto create it when not yet in the database.
	 * 
	 * @var bool
	 */
	public $autoCreate = false;


	/**
	 * Only used by array relations. Used for ordering the relation on save and when fetching.
	 *
	 * @var string
	 */
	public $orderBy;

  /**
   * Constructor
   *
   * @param string $name The name of the relation
   * @param array $keys Associative array with key map
   * ```
   * ['fromColumn' => 'toColumn']
   * ```
   *
   * @param int $type
   */
	public function __construct($name, array $keys, $type = self::TYPE_HAS_ONE) {
		$this->name = $name;
		$this->keys = $keys;
		$this->type = $type;
	}

  /**
   * Set the entity name.
   *
   * @param class-string<Property> $propertyName
   * @return $this
   */
	public function setPropertyName(string $propertyName): Relation
	{
		if(!class_exists($propertyName)) {
			throw new InvalidArgumentException($propertyName . ' class not found');
		}
		if(!is_subclass_of($propertyName, Property::class, true)) {
			throw new InvalidArgumentException($propertyName . ' must extend '. Property::class);
		}
		
		if(is_subclass_of($propertyName, Entity::class, true)) {
			throw new InvalidArgumentException($propertyName . ' may not be an '. Entity::class .'. Only '. Property::class .' objects can be mapped.');
		}
		
		$this->propertyName = $propertyName;

		return $this;
	}

	public function setTableName($name) 
	{
		$this->tableName = $name;

		return $this;
	}

	/**
	 * Get the column of the table to select for the scalar relation
	 *
	 * @return string
	 */
	public function getScalarColumn() {
		$table = Table::getInstance($this->tableName, go()->getDbConnection());
		$diff = array_diff($table->getPrimaryKey(), $this->keys);

		return array_shift($diff);
	}


}
