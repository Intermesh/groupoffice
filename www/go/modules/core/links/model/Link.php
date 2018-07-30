<?php

namespace go\modules\core\links\model;

use go\core\acl\model\Acl;
use go\core\App;
use go\core\db\Query;
use go\core\orm\Entity;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\core\search\model\Search;

class Link extends Entity {
	
	public $id;

	protected $fromEntityTypeId;
	protected $toEntityTypeId;
	protected $toEntity;
	protected $fromEntity;
	public $fromId;
	
	protected $toName;
	protected $toDescription;
	
	protected $aclId;
	

	public function getFromEntity() {
		return $this->fromEntity;
	}
	public function setFromEntity($entityName) {
		$e = EntityType::findByName($entityName);
		$this->fromEntity = $e->getName();
		$this->fromEntityTypeId = $e->getId();
	}

	public $toId;

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
	
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ) {
		Acl::applyToQuery($query, 's.aclId', $level);
		
		return $query;
	}
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		return Acl::getPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId());
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
	 * @return array
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
	
		return parent::filter($query, $filter);
	}
}
