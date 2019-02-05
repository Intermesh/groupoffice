<?php
namespace go\core\orm;

use Exception;
use go\core\acl\model\Acl;
use go\core\db\Query as DbQuery;

class Query extends DbQuery {
	private $model;
	
	/**
	 * Set's the entity or property model this query is for.
	 * 
	 * Used internally by go\core\orm\Propery::internalFind();
	 * 
	 * @param string $cls
	 * @return $this
	 */
	public function setModel($cls) {
		$this->model = $cls;
		return $this;
	}
	
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
		$this->andWhere($criteria);
		$cls::filter($this, $criteria, $filters);
		
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
		$c->where(['link.fromEntityTypeId' => $entity->getType()->getId(),
				'link.fromId' => $entity->id,
				'link.toEntityTypeId' => $cls::getType()->getId()
				])->andWhere('link.toId = '.$this->getTableAlias().'.id');
						
		return $this->join('core_link', 'link', $c);
	}

}
