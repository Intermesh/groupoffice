<?php
namespace go\modules\community\comments\model;

use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\util\DateTime;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\db\Criteria;

class Comment extends Entity {

	public $id;
	
	public $text;
	public $entityId;
	protected $entity;
	
	public $entityTypeId;
	
	/** @var DateTime */
	public $createdAt;
	/** @var DateTime */
	public $modifiedAt;
	public $createdBy;
	public $modifiedBy;

	/**
	 * Label ID's
	 * 
	 * @var int[]
	 */
	public $labels;
	
	/**
	 * By default the section is NULL. This property can be used to create multiple comment blocks per entity. 
	 * This works with a 'section' filter that defaults to NULL.
	 * 
	 * @var string
	 */
	public $section;

	/**
	 *
	 * @var string[]
	 */
	protected $images = [];

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("comments_comment", 'c')
			->addScalar('labels', 'comments_comment_label', ['id' => 'commentId'])
			->addScalar('images', 'comments_comment_image', ['id' => 'commentId'])
			->setQuery(
				(new Query())
					->select("e.clientName AS entity")
					->join('core_entity', 'e', 'e.id = c.entityTypeId')
		);
	}

	/**
	 * Set the entity type
	 *
	 * @param string|Entity|EntityType $entity "note" or entitytype instance
	 * @throws \Exception
	 */
	public function setEntity($entity) {

		if($entity instanceof Entity) {
			$this->entityTypeId = $entity->entityType()->getId();
			$this->entity = $entity->entityType()->getName();
			$this->entityId = $entity->id;
			return;
		}
		
		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}	
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
			->add('entityId', function(Criteria $criteria, $value) {
				$criteria->where('c.entityId', '=', $value);
			})
			
			->add('entity', function(Criteria $criteria, $value) {
				$criteria->where(['e.clientName' => $value]);	
			})

			->add('section', function(Criteria $criteria, $value){
				$criteria->where(['c.section' => $value]);
			}, null);
	}
	
	public static function sort(Query $query, array $sort) {	
		if(!empty($sort['id'])) {
			$sort = ['c.createdAt' => 'DESC'];
		}

		return parent::sort($query, $sort);		
	}	
	
	/**
	 * Find the entity this comment belongs to.
	 * 
	 * @return Entity|ActiveRecord
	 */
	public function findEntity() {
		$e = EntityType::findById($this->entityTypeId);
		$cls = $e->getClassName();
		if(is_a($cls, ActiveRecord::class, true)) {
			return $cls::model()->findByPk($this->entityId);			
		} else {
			return $cls::findById($this->entityId);					
		}
	}

	/**
	 * Get the permission level of the current user
	 * 
	 * @return int
	 */
	public function getPermissionLevel() {

		if(go()->getAuthState()->isAdmin()) {
			return Acl::LEVEL_MANAGE;
		}

		if($this->isNew()) {
			return $this->findEntity()->getPermissionLevel();
		}

		if($this->createdBy == go()->getAuthState()->getUserId()) {
			return Acl::LEVEL_MANAGE;
		}

		return $this->findEntity()->hasPermissionLevel(Acl::LEVEL_READ) ? Acl::LEVEL_READ : false;
		
	}
	
	/**
	 * Applies conditions to the query so that only entities with the given permission level are fetched.
	 * 
	 * @param Query $query
	 * @param int $level
	 * @param int $userId Leave to null for the current user
	 * @return Query $query;
	 */
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null, $groups = null) {
		
		return $query;
	}
	

	protected function internalValidate()
	{
		if($this->isModified(['text']) && StringUtil::detectXSS($this->text)) {
			$this->setValidationError('text', ErrorCode::INVALID_INPUT, "You're not allowed to use scripts in the content");
		}
		return parent::internalValidate();
	}


	protected function internalSave()
	{
		$this->images = Blob::parseFromHtml($this->text);
		return parent::internalSave();
	}
}