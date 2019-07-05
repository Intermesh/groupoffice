<?php
namespace go\modules\community\comments\model;

use go\core\model\Acl;
use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\util\DateTime;
use go\core\orm\EntityType;
use GO\Base\Db\ActiveRecord;
use go\core\util\StringUtil;
use go\core\validate\ErrorCode;

class Comment extends Entity {

	public $id; // was removed from jmap\Entity?
	
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
	
	private $_labels;
	
	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("comments_comment", 't')
			->setQuery(
				(new Query())
					->select("e.name AS entity")
					->join('core_entity', 'e', 'e.id = t.entityTypeId')
		);
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
	}
	
	protected static function defineFilters() {
		return parent::defineFilters()
			->add('entityId', function(\go\core\db\Criteria $criteria, $value) {
				$criteria->where('t.entityId', '=', $value);
			})->add('entity', function(\go\core\db\Criteria $criteria, $value) {
				$criteria->where(['e.name' => $value]);	
			});
	}
	
	public static function sort(Query $query, array $sort) {	
		if(!empty($sort['id'])) {
			$sort = ['createdAt' => 'DESC'];
		}

		return parent::sort($query, $sort);		
	}	
	
	protected function internalSave() {
		$success = parent::internalSave();
		
		if(isset($this->_labels)) {
			$success = $success && GO()->getDbConnection()->delete('comments_comment_label', ['commentId' => $this->id])->execute();
			foreach ($this->_labels as $labelId) {
				$success = $success && GO()->getDbConnection()->insert('comments_comment_label', ['labelId'=>$labelId,'commentId'=>$this->id])->execute();
			}
		}
		return $success;
	}
	
	public function setLabelIds($ids) {
		$this->_labels = $ids;
	}
	
	public function getLabelIds() {		
		return (new Query)
			->selectSingleValue('labelId')
			->from('comments_comment_label')
			->where(['commentId' => $this->id])
			->execute()
			->fetchAll();
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

		if(GO()->getAuthState()->getUser()->isAdmin()) {
			return Acl::LEVEL_MANAGE;
		}

		if($this->isNew()) {
			return $this->findEntity()->getPermissionLevel();
		}

		if($this->createdBy == GO()->getAuthState()->getUserId()) {
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
	public static function applyAclToQuery(Query $query, $level = Acl::LEVEL_READ, $userId = null) {
		
		return $query;
	}
	

	protected function internalValidate()
	{
		if($this->isModified(['text']) && StringUtil::detectXSS($this->text)) {
			$this->setValidationError('text', ErrorCode::INVALID_INPUT, "You're not allowed to use scripts in the content");
		}
		return parent::internalValidate();
	}
}