<?php

namespace go\modules\core\links\model;

use GO\Base\Db\ActiveRecord;
use go\core\acl\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\orm\Entity as Entity2;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\core\search\model\Search;

class Link extends Entity {
	
	/**
	 * The auto increment primary key
	 * 
	 * @var int 
	 */
	public $id;

	protected $fromEntityTypeId;
	protected $toEntityTypeId;
	protected $toEntity;
	protected $fromEntity;	
	protected $toName;
	protected $toDescription;
	
	protected $aclId;
	

	/**
	 * The id of the entity it links from
	 * 
	 * The entity type can be fetched with {@see getFromEntity()}
	 * 
	 * @var int
	 */
	public $fromId;
	
	/**
	 * The entity type of the entity it links from
	 * 
	 * @return string eg. "Contact"
	 */
	public function getFromEntity() {
		return $this->fromEntity;
	}
	
	public function setFromEntity($entityName) {
		$e = EntityType::findByName($entityName);
		$this->fromEntity = $e->getName();
		$this->fromEntityTypeId = $e->getId();
	}

	/**
	 * The id of the entity it links to
	 * 
	 * The entity type can be fetched with {@see Link::getToEntity()}
	 * 
	 * @var int
	 */
	public $toId;

	/**
	 * The entity type of the entity it links to
	 * 
	 * @return string eg. "Contact"
	 */
	public function getToEntity() {
		return $this->toEntity;
	}
	
	public function setToEntity($entityName) {
		$e = EntityType::findByName($entityName);
		$this->toEntity = $e->getName();
		$this->toEntityTypeId = $e->getId();
	}

	/**
	 * Description
	 * 
	 * @var string
	 */
	public $description;

	/**
	 * The date the link was created
	 * 
	 * @var DateTime
	 */
	public $createdAt;

	protected static function defineMapping() {
		return parent::defineMapping()
										->addTable('core_link', 'l')
										->setQuery(
														(new Query())
														->select("eFrom.clientName AS fromEntity, eTo.clientName AS toEntity, s.name as toName, s.description as toDescription, s.aclId")
														->join('core_entity', 'eFrom', 'eFrom.id = l.fromEntityTypeId')
														->join('core_entity', 'eTo', 'eTo.id = l.toEntityTypeId')
														->join('core_search', 's', 's.entityId = l.toId AND s.entityTypeId = l.toEntityTypeId')
		);
	}
	
	
	/**
	 * Create a link between two entities
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord $b
	 * @param string $description
	 * @return Link
	 */
	public static function create($a, $b, $description = null) {
				
		if(static::exists($a, $b)) {
			return true;
		}
		
		$link = new Link();
		$link->fromId = $a->id;
		$link->fromEntityTypeId = $a->getType()->getId();
		$link->toId = $b->id;
		$link->toEntityTypeId = $b->getType()->getId();
		$link->description = $description;		
		
		if(!$link->save()) {
			throw new \Exception("Couldn't create link: ". var_export($link->getValidationErrors(), true));
		}
	}
	
	/**
	 * Check if a link exists.
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord  $b
	 * @return boolean
	 */
	public static function exists($a, $b) {
		return Link::find()->where([
				'fromEntityTypeId' => $a->getType()->getId(),
				'fromId' => $a->id,
				'toEntityTypeId' => $b->getType()->getId(),
				'toId' => $b->id,
		])->single() !== false;
	}
	
	protected function internalValidate() {
		
		parent::internalValidate();
		
		if($this->toId == $this->fromId && $this->toEntityTypeId == $this->fromEntityTypeId) {
			$this->setValidationError("toId", ErrorCode::UNIQUE, "You can't link to the same item");
		}
	}
	
	protected function internalSave() {
		if(!parent::internalSave()) {
			return false;
		}
		
		$reverse = [];
		$reverse['fromEntityTypeId'] = $this->toEntityTypeId;
		$reverse['toEntityTypeId'] = $this->fromEntityTypeId;
		$reverse['toId'] = $this->fromId;
		$reverse['fromId'] = $this->toId;		
		$reverse['description'] = $this->description;
		$reverse['createdAt'] = $this->createdAt;
		
		if($this->isNew()) {			
			//make sure the description and name are set so they are returned to the client
			$search = Search::find()->where(['entityId' => $this->toId, 'entityTypeId' => $this->toEntityTypeId])->single();
			$this->toDescription = $search->description;
			$this->toName = $search->name;
		}
		
		return App::get()->getDbConnection()->insertIgnore('core_link', $reverse)->execute();
	}
	
	protected function internalDelete() {		
		if(!parent::internalDelete()) {
			return false;
		}
		
		$reverse = [];
		$reverse['fromEntityTypeId'] = $this->toEntityTypeId;
		$reverse['toEntityTypeId'] = $this->fromEntityTypeId;
		$reverse['toId'] = $this->fromId;
		$reverse['fromId'] = $this->toId;
		
		return GO()->getDbConnection()->delete('core_link', $reverse)->execute();
	}
	
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null) {
		Acl::applyToQuery($query, 's.aclId', $level, $userId);
		
		return $query;
	}
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		return Acl::getUserPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId());
	}
	
	/**
	 * Checks if the current user has a given permission level.
	 * 
	 * @param int $level
	 * @return boolean
	 */
	public function hasPermissionLevel($level = Acl::LEVEL_READ) {
		return $this->getPermissionLevel() >= $level;
	}
	
	/**
	 * The to properties
	 * 	
	 * @return array ['name' => string, 'description' => 'description']
	 */
	public function getTo() {
		return ['name' => $this->toName, 'description' => $this->toDescription];
	}
	
	public static function filter(Query $query, array $filter) {
		
		if(!empty($filter['entityId']))	{
			$query->where('fromId', '=', $filter['entityId']);
		}

		if(!empty($filter['entity']))	{
			$query->where(['eFrom.name' => $filter['entity']]);		
		}	
		
		// Entity filter consist out of name => "Contact" and an optional "filter" => "isOrganization"
		if(!empty($filter['entities']))	{			
			$sub = (new Criteria);
			
			foreach($filter['entities'] as $e) {
				$w = ['eTo.name' => $e['name']];
				if(isset($e['filter'])) {
					$w['filter'] = $e['filter'];
				}
				
				$sub->orWhere($w);
			}
			
			$query->where($sub);		
		}		
		
		if(!empty($filter['q'])) {
			$query->where('s.keywords', 'LIKE', '%' . $filter['q'] .'%');
		}	

		return parent::filter($query, $filter);
	}
	
	protected static function searchColumns() {
		return ['s.keywords'];
	}
	
}
