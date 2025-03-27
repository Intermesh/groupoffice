<?php

namespace go\core\orm;

use go\core\db\Table;
use InvalidArgumentException;
use LogicException;

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
	 * @see Mapping::$dynamic;
	 * @var bool
	 */
	public $dynamic = false;


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

	/**
	 * Constant values that are the same for every record in the relation.
	 * Will be added the where() when fetching
	 * Will be added to set when saving
	 *
	 * ```
	 * ['columnName'=> constantValue]
	 * ```
	 *
	 * @var array
	 */
	public $constants;

	/**
	 * If set this is the column that will be returned in a scalar Relation
	 * When not set the first none primary key column will be used.
	 * @see getScalarColumn()
	 *
	 * @var string
	 */
	public $scalarColumn;

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
	public function __construct(string $name, array $keys, int $type = self::TYPE_HAS_ONE) {
		$this->name = $name;
		$this->keys = $keys;
		$this->type = $type;
	}

	/**
	 * Set the primaryKey => foreignKey to define this relation
	 *
	 * @todo We could adapt a default of ['pk'=>'fk'] for identifying relations in our database schema so this method becomes optional
	 * @param array $keys The keys of the relation. eg. ['id' => 'articleId']
	 * @return $this
	 */
	public function keys(array $keys) {
		$this->keys = $keys;
		return $this;
	}

	public function constants(array $constants) {
		$this->constants = $constants;
		return $this;
	}

	/**
	 * Create a scalar relation. For example an array of ID's.
	 *
	 * A scalar is always an array. It can't be null.
	 *
	 * Sort order of scalars are not guaranteed! MySQL may return it in a different order then it was written.
	 *
	 * Note: When an entity with scalar relations is saved it automatically looks for other entities referencing the same
	 * scalar relation for tracking changes.
	 * @todo maybe this is unneeded and scalars should only be defined in one entity?
	 *
	 * eg. When a group's users[] change. It will mark all users as changed too because they have a scalar groups[] property.
	 *
	 * @param string $tableName
	 * @param array $columnName manualy set the column to select as scaler value
	 *   If empty it will be the first non-pk-column
	 *
	 * @return $this
	 */
	static function scalar(string $tableName, ?string $columnName = null): self {
		$relation = new self('',[],self::TYPE_SCALAR);
		$relation->setTableName($tableName);
		if(!empty($columnName))
			$relation->scalarColumn = $columnName;
		return $relation;
	}


	/**
	 * Create a mapped relation. Index is the ID of the {@see Property}.
	 *
	 * - Map objects are unsorted!
	 * - If the map is empty the value is null and not an empty object
	 * - When updating a map the client must send the full property value. Everything that is not included will be removed.
	 * - Setting a value to null or false will remove it from the map.
	 *
	 * @param class-string<Property> $propertyClsName
	 *
	 * @return $this
	 */
	static function map(string $propertyClsName): self {
		$relation = new self('',[],self::TYPE_MAP);
		$relation->setPropertyName($propertyClsName);
		return $relation;
	}

	/**
	 * Create an array relation.
	 *
	 * - Array's can be sorted. When the property does not have a primary key, the whole array will be deleted and rewritten
	 *   when saved. This will retain the sort order automatically.
	 * - If the property does have a primary key. The client can send it along to retain it. In this case the sort order must
	 *   be stored in an int column. The framework does this automatically when you specify this. See the $options parameter.
	 * - The property can't be null. An empty value is an empty array.
	 * - When updating the array property, the client must send all items. Items not included will be removed.
	 *
	 * @param class-string<Property> $propertyClsName The name of the Property model
	 * @param string $orderBy pass 'sortOrder' to save the sort order in this int column. This property can
	 *   be a protected property because the client does not need to know of its existence.
	 *
	 * @return $this;
	 */
	static function array(string $propertyClsName, ?string $orderBy = null): self {
		$relation = new self('',[],self::TYPE_ARRAY);
		$relation->setPropertyName($propertyClsName);
		if(!empty($orderBy))
			$relation->orderBy = $orderBy;
		return $relation;
	}

	/**
	 * Create has one relation
	 *
	 * An empty value is null and not an empty object. Set to null to remove.
	 *
	 * @param class-string<Property> $propertyClsName The class name of the property model
	 * @param bool $autoCreate If not found then automatically create an empty object
	 *
	 * @return $this;
	 */
	static function one($propertyClsName, $autoCreate = false) {
		$relation = new self('',[],self::TYPE_HAS_ONE);
		$relation->setPropertyName($propertyClsName);
		$relation->autoCreate = $autoCreate;
		return $relation;
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

//		if(is_subclass_of($propertyName, Entity::class, true)) {
//			throw new InvalidArgumentException($propertyName . ' may not be an '. Entity::class .'. Only '. Property::class .' objects can be mapped.');
//		}

		$this->propertyName = $propertyName;

		return $this;
	}

	public function setTableName($name): Relation
	{
		$this->tableName = $name;

		return $this;
	}

	/**
	 * Get the column of the table to select for the scalar relation
	 *
	 * @return string
	 */
	public function getScalarColumn(): string
	{
		if(isset($this->scalarColumn))
			return $this->scalarColumn;

		$table = Table::getInstance($this->tableName);
		$diff = array_diff($table->getPrimaryKey(), $this->keys);

		if(empty($diff)) {
			throw new LogicException("Can't determine column for scalar relation " . $this->propertyName . "->" . $this->name ." . Please check the given keys.");
		}

		return array_shift($diff);
	}


}
