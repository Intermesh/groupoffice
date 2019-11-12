<?php
namespace go\core\orm;

use Exception;
use go\core\model\Acl;
use go\core\db\Query as DbQuery;
use PDO;

class Query extends DbQuery {
	private $model;
	private $fetchProperties;
	
	/**
	 * Set's the entity or property model this query is for.
	 * 
	 * Used internally by go\core\orm\Propery::internalFind();
	 * 
	 * @param string $cls
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
	 * @example:
	 * 
	 * $stmt = Contact::find()
	 * 						->filter([
	 * 								"permissionLevel" => Acl::LEVEL_READ
	 * 						]);
	 * 
	 * @param array $filters
	 * 
	 * @return $this
	 */
	public function filter(array $filters) {		
		$cls = $this->model;		
		$criteria = new \go\core\db\Criteria();		
		$cls::filter($this, $criteria, $filters);
		if($criteria->hasConditions()) {
			$this->andWhere($criteria);
		}
		
		return $this;
	}
	
	/**
	 * Select models linked to the given entity
	 * 
	 * @param \go\core\orm\Entity $entity
	 * @return $this
	 */
	public function withLink(Entity $entity) {
		
		$c = new \go\core\db\Criteria();
		$cls = $this->model;
		$c->where(['link.fromEntityTypeId' => $entity->entityType()->getId(),
				'link.fromId' => $entity->id,
				'link.toEntityTypeId' => $cls::entityType()->getId()
				])->andWhere('link.toId = '.$this->getTableAlias().'.id');
						
		return $this->join('core_link', 'link', $c);
	}

	/**
	 * Delete's all entities in the query
	 * @return bool
	 */
	public function delete() {
		go()->getDbConnection()->beginTransaction();
		foreach($this->getIterator() as $entity) {
			if(!$entity->delete()) {
				go()->getDbConnection()->rollBack();
				return false;
			}
		}
		go()->getDbConnection()->commit();
		return true;
	}

	/**
	 * Join the custom fields table
	 * 
	 * @param string $alias The table alias to use.
	 * @return $this
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

	private $readOnly = false;

	// /**
	//  * Set models read only. This improves performance too.
	//  * 
	//  * @return self
	//  */
	// public function readOnly ($readOnly = true) {
	// 	$this->readOnly = $readOnly;
	// 	return $this->fetchMode(PDO::FETCH_CLASS, $this->model, [false, $this->fetchProperties, $this->readOnly]);
	// }

	public function getReadOnly() {
		return $this->readOnly;
	}

}
