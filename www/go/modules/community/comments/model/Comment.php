<?php
namespace go\modules\community\comments\model;

use go\core\db\Query;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\util\DateTime;

class Comment extends Entity {

	public $text;
	public $labelIds;
	public $entityId;
	protected $entity;
	
	public $entityTypeId;
	
	/** @var DateTime */
	public $createdAt;
	/** @var DateTime */
	public $modifiedAt;
	public $createdBy;
	public $modifiedBy;
	
	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("comments_comment")
			->addRelation('labels', Label::class, ['id' => 'commentId'])
			->addRelation('attachments', Attachment::class, ['id' => 'commentId'])
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
	
	public static function filter(Query $query, array $filter) {
		
		if(isset($filter['entityId'])){
			$query->where('t.entityId', '=', $filter['entityId']);
		}
		
		if(isset($filter['entity'])){
			$query->where(['e.name' => $filter['entity']]);	
		}
		
		return parent::filter($query, $filter);	
	}
	
}