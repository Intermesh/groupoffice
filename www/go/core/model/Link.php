<?php

namespace go\core\model;

use GO\Base\Db\ActiveRecord;
use go\core\model\Acl;
use go\core\App;
use go\core\db\Criteria;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\orm\Entity as Entity2;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\core\model\Search;

/**
 * Link model
 * 
 * @example Find organization links for a contact. When you know which entity you're looking for you can use withLink() in the find query.
 * ```
 * $companies = \go\modules\community\addressbook\model\Contact::find()
							->withLink($contact)
							->andWhere('isOrganization', '=', true)
							->selectSingleValue('name')
							->all();
 * ```
 * 
 * Find links using the JMAP filter:
 * 
 * ```
 * $query = Link::find()->filter([
				'entityId' => $contact->id,
				'entity' => "Contact",
				'entities' => [
						['name' => "Contact", "filter" => "isOrganization"]
				]
		]);
 * ```
 * 
 * 
 */
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
	public $toSearchId;
	
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
														->select("eFrom.clientName AS fromEntity, eTo.clientName AS toEntity, s.name as toName, s.description as toDescription, s.aclId, s.id as toSearchId")
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
		
		$existingLink = static::findLink($a, $b);
		if($existingLink) {
			return $existingLink;
		}
		
		$link = new Link();
		$link->fromId = $a->id;
		$link->fromEntity = $a->entityType()->getName();
		$link->fromEntityTypeId = $a->entityType()->getId();
		$link->toId = $b->id;
		$link->toEntity = $b->entityType()->getName();
		$link->toEntityTypeId = $b->entityType()->getId();
		$link->description = $description;		
		
		if(!$link->save()) {
			throw new \Exception("Couldn't create link: ". var_export($link->getValidationErrors(), true));
		}
		
		return $link;
	}
	
	/**
	 * Check if a link exists.
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord  $b
	 * @return boolean
	 */
	public static function exists($a, $b) {
		return static::findLink($a, $b) !== false;
	}
	/**
	 * Find a link
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord  $b
	 * @return Link|boolean
	 */
	public static function findLink($a, $b) {
		return Link::find()->where([
				'fromEntityTypeId' => $a->entityType()->getId(),
				'fromId' => $a->id,
				'toEntityTypeId' => $b->entityType()->getId(),
				'toId' => $b->id,
		])->single();
	}
	
	/**
	 * Delete a link between two entities
	 * 
	 * Warning: This will not fire the Link::EVENT_DELETE
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord  $b
	 * @return boolean
	 */
	public static function deleteLink($a, $b) {
		return self::deleteLinkWithIds($a->entityType()->getId(), $a->id, $b->id, $b->entityType()->getId());
	}
	
	/**
	 * Delete link with id and entity type id's
	 * 
	 * Warning: This will not fire the Link::EVENT_DELETE
	 * 
	 * @param int $aId
	 * @param int $aTypeId
	 * @param int $bId
	 * @param int $bTypeId
	 * @return boolean
	 */
	public static function deleteLinkWithIds($aId, $aTypeId, $bId, $bTypeId) {
			if(!GO()->getDbConnection()
						->delete('core_link',[
				'fromEntityTypeId' => $aTypeId,
				'fromId' => $aId,
				'toEntityTypeId' => $bTypeId,
				'toId' => $bId,
		])->execute()) {
			return false;
		}
		
		if(!GO()->getDbConnection()
						->delete('core_link',[
				'fromEntityTypeId' => $bTypeId,
				'fromId' => $bId,
				'toEntityTypeId' => $aTypeId,
				'toId' => $aId,
		])->execute()) {
			return false;
		}
		
		return true;						
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
			$this->updateDataFromSearch();
		}
		
		return App::get()->getDbConnection()->insertIgnore('core_link', $reverse)->execute();
	}

	private function updateDataFromSearch() {
		//make sure the description and name are set so they are returned to the client
		if(!isset($this->toSearchId) || !isset($this->aclId)) {
			$search = Search::find()->where(['entityId' => $this->toId, 'entityTypeId' => $this->toEntityTypeId])->single();
			$this->toDescription = $search->description;
			$this->toName = $search->name;
			$this->toSearchId = $search->id;
			$this->aclId = $search->findAclId();
		}
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
		$level = Acl::LEVEL_READ;
		Acl::applyToQuery($query, 's.aclId', $level, $userId);
		
		return $query;
	}
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {
		if($this->isNew()) {			
			$this->updateDataFromSearch();
		}
		//Readable items may be linked!
		return Acl::getUserPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId()) ?  Acl::LEVEL_DELETE : false;
	}
	
//	
//	/**
//	 * The to properties
//	 * 	
//	 * @return array ['name' => string, 'description' => 'description']
//	 */
//	public function getTo() {
//		return ['name' => $this->toName, 'description' => $this->toDescription];
//	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
						->add('entityId', function (Criteria $crtiteria, $value){
							$crtiteria->where('fromId', '=', $value);
						})
						->add('entity', function (Criteria $criteria, $value){
							$criteria->where(['eFrom.clientName' => $value]);		
						})
						->add('entities', function (Criteria $criteria, $value){
							// Entity filter consist out of name => "Contact" and an optional "filter" => "isOrganization"
							if(empty($value)) {
								return;
							}
							
							$sub = (new Criteria);

							foreach($value as $e) {
								$w = ['eTo.clientName' => $e['name']];
								if(isset($e['filter'])) {
									$w['filter'] = $e['filter'];
								}

								$sub->orWhere($w);
							}

							$criteria->where($sub);		
							
						});
					
	}
	
	protected static function textFilterColumns() {
		return ['s.keywords'];
	}

	public static function sort(\go\core\orm\Query $query, array $sort)
	{
		if(isset($sort['modifiedAt'])) {
			$sort['s.modifiedAt'] = $sort['modifiedAt'];
			unset($sort['modifiedAt']);
		}

		if(isset($sort['toEntity'])) {
			$sort['eTo.name'] = $sort['toEntity'];
			unset($sort['toEntity']);
		}
		return parent::sort($query, $sort);
	}

	
	
}
