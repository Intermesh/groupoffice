<?php
namespace go\modules\community\comments\model;

use go\core\orm\Query;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\util\DateTime;

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
			//->addRelation('labels', Label::class, ['id' => 'commentId'])
		//	->addRelation('attachments', Attachment::class, ['id' => 'commentId'])
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
			->add('entityId', function(Query $query, $value, array $filter) {
				$query->where('t.entityId', '=', $value);
			})->add('entity', function(Query $query, $value, array $filter) {
				$query->where(['e.name' => $value]);	
			});
	}
	
//	public static function filter(Query $query, array $filter) {
//		
//		if(isset($filter['entityId'])){
//			$query->where('t.entityId', '=', $filter['entityId']);
//		}
//		
//		if(isset($filter['entity'])){
//			$query->where(['e.name' => $filter['entity']]);	
//		}
//		
//		return parent::filter($query, $filter);	
//	}
	
	public static function sort(Query $query, array $sort) {	
		//GO()->debug($sort);
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
		//TODO: turn off ATTR_EMULATE_PREPARES to fetch Integers
		return (new Query)
			->selectSingleValue('labelId')
			->from('comments_comment_label')
			->where(['commentId' => $this->id])
			->execute()
			->fetchAll();
	}
	
}