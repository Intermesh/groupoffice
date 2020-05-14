<?php
namespace go\core\orm;

use Exception;
use go\core\db\Criteria;
use go\core\model\Acl;
use go\core\db\Query as DbQuery;
use PDO;

/**
 * @inheritDoc
 *
 * @package go\core\orm
 */
class Query extends DbQuery {
  /**
   * @var Entity
   */
	private $model;

  /**
   * @var array
   */
	private $fetchProperties;

  /**
   * Set's the entity or property model this query is for.
   *
   * Used internally by go\core\orm\Property::internalFind();
   *
   * @param string $cls The Entity class name
   * @param array $fetchProperties The entity properties to fetch
   * @param bool $readOnly Entity's will be read only. This improves performance.
   * @return $this
   */
	public function setModel($cls, $fetchProperties = [], $readOnly = false) {
		$this->model = $cls;
		$this->fetchProperties = $fetchProperties;
		$this->readOnly = $readOnly;

		return $this->fetchMode(PDO::FETCH_CLASS, $this->model, [false, $this->fetchProperties, $this->readOnly]);
	}
	
	/**
	 * Get class name of the model to find
	 * 
	 * @return string
	 */
	public function getModel() {
		return $this->model;
	}

  /**
   * Applies JMAP filters to the query
   *
   * @param array $filters
   *
   * @return $this
   * @throws Exception
   * @example:
   *
   * $stmt = Contact::find()
   *            ->filter([
   *                "permissionLevel" => Acl::LEVEL_READ
   *            ]);
   *
   */

  private $permissionLevelFoundInFilters = false;

  public function getPermissionLevelFoundInFilters() {
    return $this->permissionLevelFoundInFilters;
	}

	/**
	 *
	 * @param array $filter
	 * @param Query $query
	 * @param null $criteria
	 * @return void
	 * @throws Exception
	 */
	private function internalFilter($filter, Criteria $criteria)  {

		$cls = $this->model;
		if(isset($filter['conditions']) && isset($filter['operator'])) { // is FilterOperator

			foreach($filter['conditions'] as $condition) {
				$subCriteria = new Criteria();
				$this->internalFilter($condition, $subCriteria);

				if(!$subCriteria->hasConditions()) {
					continue;
				}

				switch(strtoupper($filter['operator'])) {
					case 'AND':
						$criteria->where($subCriteria);
						break;

					case 'OR':
						$criteria->orWhere($subCriteria);
						break;

					case 'NOT':
						$criteria->andWhereNotOrNull($subCriteria);
						break;
				}
			}

		} else {
			// is FilterCondition
			$subCriteria = new Criteria();

			if(!$this->permissionLevelFoundInFilters) {
				$this->permissionLevelFoundInFilters = !empty($filter['permissionLevel']);
			}

			$cls::filter($this, $subCriteria, $filter);

			if($subCriteria->hasConditions()) {
				$criteria->andWhere($subCriteria);
			}
		}

		return $this;
	}

	public function filter(array $filters) {

		return $this->internalFilter($filters, $this);

//		$cls = $this->model;
//		$criteria = new Criteria();
//		$cls::filter($this, $criteria, $filters);
//		if($criteria->hasConditions()) {
//			$this->andWhere($criteria);
//		}
//
//		return $this;
	}

  /**
   * Select models linked to the given entity
   *
   * @param Entity $entity
   * @return $this
   * @throws Exception
   */
	public function withLink(Entity $entity) {
		
		$c = new Criteria();
		$cls = $this->model;
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
	public function joinCustomFields($alias = 'customFields') {
		$cls = $this->model;
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
	public function joinProperties(array $path) {
		$cls = $this->model;
		$alias = $this->getTableAlias();

		foreach($path as $part) {
			$relation = $cls::getMapping()->getRelation($part);
			/** @var Relation $relation */

			if(isset($relation->entityName)) {
				$cls = $relation->entityName;
				
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

	public function getReadOnly() {
		return $this->readOnly;
	}

}
