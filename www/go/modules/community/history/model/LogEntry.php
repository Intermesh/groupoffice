<?php

namespace go\modules\community\history\model;

use GO\Base\Db\ActiveRecord;
use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Query;
use go\core\acl\model\AclOwnerEntity;

class LogEntry extends AclOwnerEntity {

	static private $actionMap = [
		'create' => 1,
		'update' => 2,
		'delete' => 3,
		'login' => 4,
		'logout' => 5 // maybe?
	];

	public $id;

	protected $action;

	public $changes;

	public $createdAt;

	public $createdBy;

	public $removeAcl;

	public $entityTypeId;

	public $entityId;

	protected $entity;

	public $description;

	protected static function defineMapping() {
		return parent::defineMapping()->addTable('history_log_entry', 'l')
			->setQuery(
				(new Query())
					->select("e.clientName AS entity")
					->join('core_entity', 'e', 'e.id = l.entityTypeId')
			);
	}

	protected static function defineFilters()
	{
		return parent::defineFilters()
			->addDate('date', function(Criteria $q, $value){
				$q->andWhere('data', $value);
			})
			->add('actions', function(Criteria $q, $value) {
				if(!empty($value)) {
					$actionsr = [];
					foreach ($value as $v) {
						$actions[] = self::$actionMap[$v];
					}
					$q->andWhere('action', 'IN', $actions);
				}
			})
			->add('entities', function (Criteria $q, $value){
				if(!empty($value)) {
					$ids = EntityType::namesToIds($value);
					$q->andWhere('entityTypeId', 'IN', $ids);
				}
			})
			->add('entityId', function(Criteria $q, $value) {
				$q->andWhere('entityId', '=', $value);
			})
			->add('entity', function(Criteria $q, $value) {
				$type = EntityType::findByName($value);
				if(!empty($type)) {
					$q->andWhere('entityTypeId', '=', $type->getId());
				}
			})
			->add('user', function(Criteria $q, $value) {
				$q->andWhere('createdBy', '=', $value);
			});
	}

	protected static function getAclsToDelete(Query $query) {

		$q = clone $query;
		$q->andWhere('removeAcl', '=',1)
		->andWhere('action', '=', self::$actionMap['delete']);

		return parent::getAclsToDelete($q);
	}

	public function setAction($action) {
		$this->action = self::$actionMap[$action];
	}
	public function getAction() {
		return array_flip(self::$actionMap)[$this->action];
	}

	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Set the entity type
	 *
	 * @param Entity | ActiveRecord $entity "note", Entity $note or Entitytype instance
	 * @throws \Exception
	 */
	public function setEntity($entity) {
		$this->entityTypeId = $entity->entityType()->getId();
		$this->entity = $entity->entityType()->getName();
		$this->entityId = $entity->id();
		$this->removeAcl = $entity instanceof AclOwnerEntity || ($entity instanceof ActiveRecord && !$entity->IsJoinedAclField);
		$this->description = $entity->title();
		$this->setAclId($entity->findAclId());
	}

	public function setAclId($aclId) {
		$this->aclId = $aclId;
	}
}