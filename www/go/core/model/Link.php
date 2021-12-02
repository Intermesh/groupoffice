<?php

namespace go\core\model;

use Faker\Generator;
use GO\Base\Db\ActiveRecord;
use go\core\acl\model\AclItemEntity;
use go\core\App;
use go\core\db\Criteria;
use go\core\orm\exception\SaveException;
use go\core\db\Query as DbQuery;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\community\comments\model\Comment;

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
 * Find links for an Entity:
 *
 * ```
 * $links = Link::findLinks($entity);
 * ```
 * 
 */
class Link extends AclItemEntity
{
	/**
	 * The auto increment primary key
	 * 
	 * @var int
     *
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

	protected $permissionLevel;
	

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

	/**
	 * Find the entity it links to
	 *
	 * @return \go\core\orm\Entity|ActiveRecord
	 * @throws \Exception
	 */
	public function findToEntity() {
		$e = EntityType::findByName($this->toEntity);
		$cls = $e->getClassName();

		if(is_a($cls, Entity::class, true)) {
			return $cls::findById($this->toId);
		} else{
			return $cls::model()->findByPk($this->toId);
		}
	}

	/**
	 * Find the entity it links from
	 *
	 * @return \go\core\orm\Entity|ActiveRecord
	 * @throws \Exception
	 */
	public function findFromEntity() {
		$e = EntityType::findByName($this->fromEntity);
		$cls = $e->getClassName();

		if(is_a($cls, Entity::class, true)) {
			return $cls::findById($this->fromId);
		} else{
			return $cls::model()->findByPk($this->fromId);
		}
	}


	public function setToEntity($entityName) {
		$e = EntityType::findByName($entityName);
		$this->toEntity = $e->getName();
		$this->toEntityTypeId = $e->getId();
	}

	/**
	 * Check if this links from type 1 to type 2
	 *
	 * @param string $entityType1
	 * @param string $entityType2
	 * @return bool
	 */
	public function isBetween($entityType1, $entityType2) {
		return (
			($this->getToEntity() == $entityType1 && 	$this->getFromEntity() == $entityType2) ||
			($this->getToEntity() == $entityType2 && $this->getFromEntity() == $entityType1)
		);
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

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
										->addTable('core_link', 'l')
										->addQuery(
														(new Query())
														->select("eFrom.clientName AS fromEntity, eTo.clientName AS toEntity, search.name as toName, search.description as toDescription, search.aclId, search.id as toSearchId")
														->join('core_entity', 'eFrom', 'eFrom.id = l.fromEntityTypeId')
														->join('core_entity', 'eTo', 'eTo.id = l.toEntityTypeId')
														->join('core_search', 'search', 'search.entityId = l.toId AND search.entityTypeId = l.toEntityTypeId')
		);
	}

	/**
	 * Override because it should not join core_search because we already do this in the mapping
	 *
	 * @param Query $query
	 * @return bool
	 */
	protected static function useSearchableTraitForSearch(Query $query) : bool
	{
		return true;
	}


	/**
	 * Create a link between two entities
	 * 
	 * @param Entity|ActiveRecord $a
	 * @param Entity|ActiveRecord $b
	 * @param string $description
	 * @return Link
	 */
	public static function create($a, $b, $description = null, $checkExisting = true) {
		
		$existingLink = $checkExisting ? static::findLink($a, $b) : false;
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
		$link->aclId = $a->findAclId();
		
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
	public static function linkExists($a, $b) {
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
	 * Find all links for a given entity
	 *
	 * @param Entity|ActiveRecord $a
	 * @return Link[]
	 */
	public static function findLinks($a) {
		return Link::find()->where([
			'fromEntityTypeId' => $a->entityType()->getId(),
			'fromId' => $a->id
		]);
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
		return self::deleteLinkWithIds($a->id, $a->entityType()->getId(), $b->id, $b->entityType()->getId());
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
			if(!go()->getDbConnection()
						->delete('core_link',[
				'fromEntityTypeId' => $aTypeId,
				'fromId' => $aId,
				'toEntityTypeId' => $bTypeId,
				'toId' => $bId,
		])->execute()) {
			return false;
		}
		
		if(!go()->getDbConnection()
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
		if(!empty($this->description) && strlen($this->description) > 190) {
			$this->setValidationError("description", ErrorCode::INVALID_INPUT, "Description field too long");
		}
	}
	
	protected function internalSave(): bool
	{
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
//			$this->updateDataFromSearch();
			return App::get()->getDbConnection()->insertIgnore('core_link', $reverse)->execute();
		}

		return App::get()->getDbConnection()->updateIgnore('core_link',
			['description' => $this->description],
			['toId' => $this->fromId, 'toEntityTypeId' => $this->fromEntityTypeId, 'fromId' => $this->toId, 'fromEntityTypeId' => $this->toEntityTypeId]
		)->execute();
	}

//	private function updateDataFromSearch() {
//		//make sure the aclId, description and name are set so they are returned to the client
//		if(!isset($this->toSearchId) || !isset($this->aclId)) {
//			$search = Search::find()->where(['entityId' => $this->toId, 'entityTypeId' => $this->toEntityTypeId])->single();
//			if(!$search) {
//				throw new \Exception("Could not find entity from search cache. Please run System settings -> Tools -> Update search index");
//			}
//			$this->toDescription = $search->description;
//			$this->toName = $search->name;
//			$this->toSearchId = $search->id;
//			$this->aclId = $search->findAclId();
//		}
//	}
//
	protected static function internalDelete(Query $query): bool
	{

		//delete the reverse links
		$join = new Query();
		$joinSubQuery = clone $query;
		$joinSubQuery->select("*");
		$join->join($joinSubQuery, 'rev', 
			'rev.fromEntityTypeId = t.toEntityTypeId AND rev.toEntityTypeId = t.fromEntityTypeId AND rev.toId = t.fromId AND rev.fromId = t.toId');
		go()->getDbConnection()->delete('core_link', $join)->execute();

		return parent::internalDelete($query);

	}
	
	public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int $userId = null, array $groups = null): Query
	{
		$level = Acl::LEVEL_READ;
		//return parent::applyAclToQuery($query, $level, $userId, $groups);
		Acl::applyToQuery($query, 'search.aclId', $level, $userId, $groups);
		return $query;
	}
	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	protected function internalGetPermissionLevel() {
		if($this->isNew() && empty($this->aclId)) {
			$e = $this->findToEntity();
			if(!$e) {
				throw new \Exception("Could not find to entity in link");
			}
			$this->aclId = $e->findAclId();
		}

		if(!isset($this->permissionLevel)) {
			$this->permissionLevel = Acl::getUserPermissionLevel($this->aclId, App::get()->getAuthState()->getUserId());
		}
		//Readable items may be linked!
		return $this->permissionLevel ?  Acl::LEVEL_DELETE : false;
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
	
	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
						->add('entityId', function (Criteria $crtiteria, $value){
							$crtiteria->where('fromId', '=', $value);
						})
						->add('entity', function (Criteria $criteria, $value){

							$e = EntityType::findByName($value);
							if(!$e) {
								throw new \Exception("Entity type " . $value .' not found');
							}

							$criteria->where(['l.fromEntityTypeId' => $e->getId()]);
						})
						->add('entities', function (Criteria $criteria, $value){
							// Entity filter consist out of name => "Contact" and an optional "filter" => "isOrganization"
							if(empty($value)) {
								return;
							}
							
							$sub = (new Criteria);

							foreach($value as $e) {
								$et = EntityType::findByName($e['name']);
								if(!$et) {
									throw new \Exception("Entity type " . $e['name'] .' not found');
								}
								$w = ['l.toEntityTypeId' => $et->getId()];
								if(isset($e['filter'])) {
									$w['filter'] = $e['filter'];
								}

								$sub->orWhere($w);
							}

							$criteria->where($sub);		
							
						});
					
	}


	public static function sort(\go\core\orm\Query $query, array $sort): Query
	{
		if(isset($sort['modifiedAt'])) {
			$sort['search.modifiedAt'] = $sort['modifiedAt'];
			unset($sort['modifiedAt']);
		}

		if(isset($sort['toEntity'])) {
			$sort['eTo.name'] = $sort['toEntity'];
			unset($sort['toEntity']);
		}
		return parent::sort($query, $sort);
	}

	protected static function aclEntityClass(): string
	{
		return Search::class;
	}

	protected function getAclEntity()
	{
		return $this->findFromEntity();
	}

	protected static function aclEntityKeys(): array
	{
		return ['toId' => 'entityId', 'toEntityTypeId' => 'entityTypeId'];
	}

	/**
	 * Copy comments from one entity to another.
	 *
	 * @param Entity|ActiveRecord $from
	 * @param Entity|ActiveRecord  $to
	 * @return bool
	 * @throws SaveException
	 */
	public static function copyTo($from, $to) {
		go()->getDbConnection()->beginTransaction();
		try {
			foreach (Link::findLinks($from) as $link) {
				$copy = $link->copy();
				$copy->fromEntityTypeId = $to::entityType()->getId();
				$copy->fromId = $to->id;

				if (!$copy->save()) {
					throw new SaveException($copy);
				}
			}
		} catch(\Exception $e) {
			go()->getDbConnection()->rollBack();
			throw $e;
		}

		return go()->getDbConnection()->commit();
	}


	public static function demo(Generator $faker, $model) {
		$searchCount = go()->getDbConnection()->selectSingleValue('count(*)')
			->from('core_search')->single();

		$offset = $faker->numberBetween(0, $searchCount);
		$limit = min($searchCount - $offset, 4);

		$search = Search::find()->limit($limit)->offset($offset);

		foreach($search as $search) {
			$entity = $search->findEntity();
			if($entity && !$entity->equals($model)) {
				Link::create($entity, $model);
			}
		}

	}
}
