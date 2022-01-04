<?php
namespace go\modules\community\comments\model;

use go\core\acl\model\AclItemEntity;
use go\core\fs\Blob;
use go\core\model\Acl;
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
		$this->images = Blob::parseFromHtml($this->text);
		return parent::internalSave();
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
}