<?php

namespace go\core\orm;

use Exception;
use go\core\db\Table;

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
	 * @todo rename to propertyName. This must actually be a Property class.
	 * 
	 * @var Property
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
   * @todo rename to propertyName. This must actually be a Property class.
   *
   * @param $entityName
   * @return $this
   * @throws Exception
   */
	public function setEntityName ($entityName) {
		if(!class_exists($entityName)) {
			throw new Exception($entityName . ' class not found');
		}
		if(!is_subclass_of($entityName, Property::class, true)) {
			throw new Exception($entityName . ' must extend '. Property::class);
		}
		
		if(is_subclass_of($entityName, Entity::class, true)) {
			throw new Exception($entityName . ' may not be an '. Entity::class .'. Only '. Property::class .' objects can be mapped.');
		}
		
		$this->entityName = $entityName;

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
