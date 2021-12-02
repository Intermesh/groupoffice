<?php
namespace go\core\orm;

use Exception;
use go\core\db\Criteria;
use go\core\db\Query as DbQuery;
use PDO;

/**
 *
 *
 * @package go\core\orm
 */
class Query extends DbQuery {
  /**
   * @var string
   */
	private $model;

	/**
   * Set's the entity or property model this query is for.
   *
   * Used internally by go\core\orm\Property::internalFind();
   *
   * @param string $cls The Entity class name
   * @param array $fetchProperties The entity properties to fetch
   * @param bool $readOnly Entity's will be read only. This improves performance.
   * @param Property|null $owner When finding relations the owner or parent Entity / Property is passed so the children can access it.
   * @return $this
   */
	public function setModel(string $cls, array $fetchProperties = [], bool $readOnly = false, Property $owner = null): Query
	{
		$this->model = $cls;
		$fetchProperties1 = $fetchProperties;
		$this->readOnly = $readOnly;

		$args = [false, $fetchProperties1, $this->readOnly];

		if(isset($owner)) {
			array_unshift($args, $owner);
		}

		return $this->fetchMode(PDO::FETCH_CLASS, $this->model, $args);
	}
	
	/**
	 * Get class name of the model to find
	 * 
	 * @return string
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
		/** @noinspection PhpUndefinedMethodInspection */
		$this->join($cls::customFieldsTableName(), $alias, $alias . '.id = '.$this->getTableAlias().'.id', 'LEFT');

		return $this;
	}

  /**
   * Join properties on the main model. The table will be aliased as the property name
   *
   * @param string[] $path eg. ['emailAddreses']
   * @return $this;
   * @throws Exception
   */
	public function joinProperties(array $path): Query
	{
		$cls = $this->model;
		$alias = $this->getTableAlias();

		foreach($path as $part) {
			$relation = $cls::getMapping()->getRelation($part);
			/** @var Relation $relation */

			if(isset($relation->propertyName)) {
				$cls = $relation->propertyName;
				
				//TODO: What if the property has more than one table in the mapping? Also might be a problem in Entity::changeReferencedEntities()
				$table = array_values($cls::getMapping()->getTables())[0]->getName();
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
