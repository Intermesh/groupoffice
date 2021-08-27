<?php
namespace go\modules\community\comments\model;

use GO\Base\Exception\Save;
use go\core\acl\model\AclItemEntity;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\orm\exception\SaveException;
use go\core\model\Search;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\util\DateTime;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;
use go\core\db\Criteria;

class Comment extends AclItemEntity {

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
	/** @var DateTime */
	public $date;

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
	 * @param mixed $entity "note", Entity $note or Entitytype instance
	 * @throws \Exception
	 *
	 * @return self
	 */
	public function setEntity($entity) {

		if($entity instanceof Entity || $entity instanceof ActiveRecord) {
			$this->entityTypeId = $entity->entityType()->getId();
			$this->entity = $entity->entityType()->getName();
			$this->entityId = $entity->id;
			return $this;
		}
		
		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}	
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();

		return $this;
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
		if(empty($sort)) {
			$sort = ['c.date' => 'ASC'];
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

	protected function getAclEntity()
	{
		return $this->findEntity();
	}

	/**
	 * @param $entity
	 * @param array $properties
	 * @return Query|Comment[]
	 * @throws \Exception
	 */
	public static function findFor($entity, $properties = []) {
		$entityTypeId = $entity->entityType()->getId();
		$entityId = $entity->id;

		return self::find($properties)->where(['entityTypeId' => $entityTypeId, 'entityId' => $entityId]);
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
			return $this->findEntity()->getPermissionLevel() ? Acl::LEVEL_WRITE : false;
		}

		if($this->createdBy == go()->getAuthState()->getUserId()) {
			return Acl::LEVEL_MANAGE;
		}

		return $this->findEntity()->hasPermissionLevel(Acl::LEVEL_READ) ? Acl::LEVEL_WRITE : false;

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

		if(!isset($this->date)) {
			$this->date = new DateTime();
		}
		return parent::internalValidate();
	}

	protected function internalSave()
	{

		if($this->isNew()) {
			$this->createAlerts();
		}
		$this->images = Blob::parseFromHtml($this->text);
		return parent::internalSave();
	}

	private function createAlerts() {
		$entity = $this->findEntity();
		$aclId = $entity->findAclId();
		if(!$aclId) {
			return;
		}

		$excerpt = StringUtil::cutString(strip_tags($this->text), 50);

		$userIds = go()->getDbConnection()->selectSingleValue('userId')
			->from('core_user_group', 'ug')
			->join('core_acl_group', 'ag', 'ag.groupId = ug.groupId')
			->where('ag.aclId', '=', $aclId);

		foreach($userIds as $userId) {

			if($userId == go()->getAuthState()->getUserId()) {
				continue;
			}

			$alert = $entity->createAlert(new DateTime(), 'comment', $userId)
				->setData([
					'type' => 'comment',
					'createdBy' => go()->getAuthState()->getUserId(),
					'excerpt' => $excerpt
				]);

			if(!$alert->save()) {
				throw new SaveException($alert);
			}
		}
	}

	public function title()
	{
		$entity = $this->findEntity();

		if($entity) {
			return $entity->title();

		} else {
			return $this->id;
		}
	}

	protected static function aclEntityClass()
	{
		return Search::class;
	}

	protected static function aclEntityKeys()
	{
		return ['entityId' => 'entityId', 'entityTypeId' => 'entityTypeId'];
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
			foreach (Comment::findFor($from) as $comment) {
				if (!$comment->copy()->setEntity($to)->save()) {
					throw new SaveException();
				}
			}
		} catch(\Exception $e) {
			go()->getDbConnection()->rollBack();
			throw $e;
		}

		return go()->getDbConnection()->commit();
	}
}