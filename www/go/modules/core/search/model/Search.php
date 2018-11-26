<?php

namespace go\modules\core\search\model;

use go\core\acl\model\Acl;
use go\core\orm\Query;
use go\core\orm\Entity;
use go\core\orm\EntityType;
use go\core\util\DateTime;

class Search extends \go\core\acl\model\AclOwnerEntity {

	public $id;
	public $entityId;
	protected $entityTypeId;
	/**
	 * @var EntityType
	 */
	protected $entity;
	protected $moduleId;
	
	public function getEntity() {
		return $this->entity;
	}
	
	public function setAclId($aclId) {
		$this->aclId = $aclId;
	}
	
	//don't delete acl on search
	protected function deleteAcl() {
		
	}
	
	
	/**
	 * Set the entity type
	 * 
	 * @param string|EntityType $entity "note" or entitytype instance
	 */
	public function setEntity($entity) {
		
		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}	
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();
		$this->moduleId = $entity->getModuleId();
	}

	public $name;
	public $description;
	public $filter;
	protected $keywords;

	public function setKeywords($keywords) {
		$this->keywords = $keywords;
	}
	
	protected function internalValidate() {
		
		$this->name = \go\core\util\StringUtil::cutString($this->name, $this->getMapping()->getColumn('name')->length, false);
		$this->description = \go\core\util\StringUtil::cutString($this->description, $this->getMapping()->getColumn('description')->length);		
		$this->keywords = \go\core\util\StringUtil::cutString($this->keywords, $this->getMapping()->getColumn('description')->length, true, "");
		
		return parent::internalValidate();
	}

	/**
	 *
	 * @var DateTime
	 */
	public $modifiedAt;

	protected static function defineMapping() {
		return parent::defineMapping()
										->addTable('core_search', 's')
										->setQuery(
														(new Query())
														->select("e.clientName AS entity")
														->join('core_entity', 'e', 'e.id = s.entityTypeId')
		);
	}

	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null) {
		Acl::applyToQuery($query, 's.aclId', $level, $userId);
		
		return $query;
	}
	
	public static function filter(Query $query, array $filter) {		
		
		if (!empty($filter['q'])) {
			$query->where('keywords', 'LIKE', "%" . $filter['q'] . "%");
		}	

		// Entity filter consist out of name => "Contact" and an optional "filter" => "isOrganization"
		if(!empty($filter['entities']))	{			
			$sub = (new \go\core\db\Criteria);
			
			foreach($filter['entities'] as $e) {
				$w = ['e.name' => $e['name']];
				if(isset($e['filter'])) {
					$w['filter'] = $e['filter'];
				}
				
				$sub->orWhere($w);
			}
			
			$query->where($sub);	
		}
		
		if(!empty($filter['entityId'])) {
			$query->where('entityId', '=', $filter['entityId']);
		}
		
		return parent::filter($query, $filter);
	}
	
	protected static function searchColumns() {
		return ['keywords'];
	}
	
}
