<?php
namespace go\core\orm;

use Exception;
use go\core\db\Criteria;
use go\core\db\Query as DbQuery;
use go\core\db\Statement;
use PDO;

/**
 * Query object for entities
 *
 * @template T
 * @extends DbQuery<T>
 */
class Query extends DbQuery {
	/**
	 * @var class-string<Entity>
	 */
	private $model;
	private array $modelConstructorArgs;

	/**
   * Set's the entity or property model this query is for.
   *
   * Used internally by go\core\orm\Property::internalFind();
   *
   * @param class-string<Entity> $cls The Entity class name
   * @param array $fetchProperties The entity properties to fetch
   * @param bool $readOnly Entity's will be read only. This improves performance.
   * @param Property|null $owner When finding relations the owner or parent Entity / Property is passed so the children can access it.
   * @return $this
   */
	public function setModel(string $cls, array $fetchProperties = [], bool $readOnly = false, Property|null$owner = null): Query
	{
		$this->model = $cls;
		$this->readOnly = $readOnly;


		$args = [false, $fetchProperties, $this->readOnly];

		if(isset($owner)) {
			array_unshift($args, $owner);
		}

		$this->modelConstructorArgs = $args;

		return $this;//->fetchMode(PDO::FETCH_CLASS, $this->model, $args);
	}

	public function createStatement(): Statement
	{
		$stmt = parent::createStatement(); // TODO: Change the autogenerated stub

		if(isset($this->model) && $this->getFetchMode() == null) {
			$stmt->fetchTypedModel($this->model, $this->modelConstructorArgs);
		}

		return $stmt;
	}

	/**
	 * Get class name of the model to find
	 * 
	 * @return class-string<T>
	 */
	public function getModel(): string
	{
		return $this->model;
	}

	/**
	 * Apply JMAP filters
	 *
	 * @see \go\core\jmap\Entity::filter()
	 * @param array $filters
	 * @return $this
	 * @throws Exception
	 */
	public function filter(array $filters): Query
	{
		$cls = $this->model;
		/**
		 * @var Entity $cls
		 */
		$cls::filter($this, $filters);
		return $this;
	}

	/**
	 * Check if filter was used by last apply() call
	 *
	 * @param $name
	 * @return boolean
	 */
	public function isFilterUsed($name): bool
	{
		return in_array(strtolower($name), $this->usedFilters);
	}

	public $usedFilters = [];

  /**
   * Select models linked to the given entity
   *
   * @param Entity $entity
   * @return $this
   * @throws Exception
   */
	public function withLink(Entity $entity): Query
	{
		
		$c = new Criteria();
		$cls = $this->model;
		/**
		 * @var Entity $cls
		 */

		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$c->where(['link.fromEntityTypeId' => $entity->entityType()->getId(),
				'link.fromId' => $entity->id,
				'link.toEntityTypeId' => $cls::entityType()->getId()
				])->andWhere('link.toId = '.$this->getTableAlias().'.id');
						
		return $this->join('core_link', 'link', $c);
	}

  /**
   * Join the custom fields table
   *
   * @param string $alias The table alias to use.
   * @return $this
   * @throws Exception
   */
	public function joinCustomFields(string $alias = 'customFields'): Query
	{
		$cls = $this->model;
		/**
		 * @var Entity $cls
		 */
		$this->join($cls::customFieldsTableName(), $alias, $alias . '.id = '.$this->getTableAlias().'.id', 'LEFT');

		return $this;
	}

  /**
   * Join relational properties on the main model. The table will be aliased as the property name.
	 *
	 * For example the emailAddresses on the contact model.
   *
   * @param string[] $path eg. ['emailAddreses']
   * @return $this;
   * @throws Exception
   */
	public function joinRelation(array $path): Query
	{
		$cls = $this->model;
		$alias = $this->getTableAlias();

		foreach($path as $part) {
			$relation = $cls::getMapping()->getRelation($part);
			/** @var Relation $relation */

			if(isset($relation->propertyName)) {
				$cls = $relation->propertyName;
				
				//TODO: What if the property has more than one table in the mapping? Also might be a problem in Entity::changeReferencedEntities()
				$table = $cls::getMapping()->getPrimaryTable()->getName();
			} else {
				$table = $relation->tableName;
			}
			$on = [];
			foreach($relation->keys as $from => $to) {
				$on[] = $alias . '.' .$from . ' = ' . $part . '.' . $to;
			}
			$this->join($table, $part, implode(' AND ', $on));

			$alias = $part;
		}

		return $this;
	}

	/**
	 * Find's table aliases for the given table name
	 *
	 * @param string $tableName
	 * @return string[]
	 */
	public function findTableAliases(string $tableName) : array {
		$aliases = [];
		if($this->getFrom() == $tableName) {
			$aliases[] = $this->getTableAlias();
		}

		foreach($this->joins as $join) {
			if($join['src'] == $tableName) {
				$aliases[] = $join['joinTableAlias'];
			}
		}

		return $aliases;
	}

  /**
   * Can be set by {@see setModel()}
   *
   * Entity's will be read only. This improves performance.
   *
   * @var bool
   */
	private $readOnly = false;

	public function getReadOnly(): bool
	{
		return $this->readOnly;
	}

	private $data = [];

	/**
	 * Set arbitrary data to the query object.
	 *
	 * Models may implement functionality to do something with it.
	 *
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data): Query
	{

		$this->data = array_merge($this->data, $data);

		return $this;
	}

	/**
	 * Get the arbitrary data aray
	 *
	 * @see setData()
	 * @return array
	 */
	public function getData(): array
	{
		return $this->data;
	}
}
