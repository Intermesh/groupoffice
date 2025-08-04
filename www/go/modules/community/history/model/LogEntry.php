<?php

namespace go\modules\community\history\model;

use DateTime;
use Exception;
use GO\Base\Db\ActiveRecord;
use go\core\acl\model\AclEntity;
use go\core\acl\model\AclInheritEntity;
use go\core\db\Criteria;
use go\core\db\Query as DbQuery;
use go\core\http\Request;
use go\core\http\Response;
use go\core\db\Expression;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\acl\model\AclOwnerEntity;
use GO\Files\Model\Folder;
use go\modules\community\history\Module;

/**
 * LogEntry model
 */
class LogEntry extends AclOwnerEntity {

	static private $actionMap = [
		'create' => 1,
		'update' => 2,
		'delete' => 3,
		'login' => 4,
		'logout' => 5,
		'badlogin' => 6,
		'download' => 7,
		'email' => 8
	];

	public ?string $id;

	protected int $action;

	public ?string $changes;

	public ?\DateTimeInterface $createdAt = null;

	public ?string $createdBy = null;

	public bool $removeAcl = false;

	public int $entityTypeId;

	public int|string|null $entityId;

	protected string $entity;

	public ?string $description;

	public ?string $remoteIp;

	public ?string $requestId;

	protected function init()
	{
		if($this->isNew()) {
			$this->remoteIp = Request::get()->getRemoteIpAddress();
			$this->requestId = go()->getDebugger()->getRequestId();
		}
	}

	protected function createAcl() : void
	{
	 //never create acl for log entry
	}

	public static function checkAcls()
	{
		$table = static::getMapping()->getPrimaryTable();

		//set owner and entity properties of acl
		$aclColumn = static::getMapping()->getColumn(static::$aclColumnName);

		$updates = [
			'acl.entityTypeId' => static::entityType()->getId(),
			'acl.entityId' => new Expression('entity.id'),
			'acl.usedIn' => $aclColumn->table->getName() . '.' . static::$aclColumnName
		];

		$stmt = go()->getDbConnection()->update(
			'core_acl',
			$updates,
			(new Query())
				->tableAlias('acl')
				->join($table->getName(), 'entity', 'entity.' . static::$aclColumnName . ' = acl.id AND removeAcl = true and action='  . self::$actionMap['delete']));

		if(!$stmt->execute()) {
			throw new Exception("Could not update ACL");
		}
	}

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()->addTable('history_log_entry', 'l')
			->addQuery(
				(new Query())
					->select("log_e.clientName AS entity")
					->join('core_entity', 'log_e', 'log_e.id = l.entityTypeId')
			);
	}

	public static function loggable(): bool
	{
		return false;
	}

	protected static function textFilterColumns(): array
	{
		return ['description', 'entityId'];
	}

	protected static function search(Criteria $criteria, string $expression, DbQuery $query): Criteria
	{
		$int = intval($expression);
		if($int == $expression && strlen($int) == strlen($expression)) {
			return $criteria->andWhere('entityId', '=', $expression);
		} else{
			return parent::search($criteria, $expression, $query);
		}
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('actions', function(Criteria $q, $value) {
				if(!empty($value)) {
					$actions = [];
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

	protected static function getAclsToDelete(Query $query): array
	{

		$q = clone $query;
		$q->distinct();
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

	/**
	 * Entity name
	 *
	 * @return string
	 */
	public function getEntity() {
		return $this->entity;
	}

	/**
	 * Set the entity type
	 *
	 * @param Entity | ActiveRecord $entity "note", Entity $note or Entitytype instance
	 * @throws Exception
	 */
	public function setEntity($entity) {
		$this->entityTypeId = $entity->entityType()->getId();
		$this->entity = $entity->entityType()->getName();
		$this->entityId = $entity->id();

		$this->removeAcl = $entity instanceof AclOwnerEntity || ($entity instanceof ActiveRecord && $entity->aclField() && (!$entity->isJoinedAclField || $entity->isAclOverwritten() || ($entity instanceof Folder && !empty($entity->acl_id) && !$entity->readonly)));

		$this->description = $entity->title();
		$this->cutPropertiesToColumnLength();
		$this->setAclId($entity->findAclId());
	}

	public function setAclId($aclId) {
		$this->aclId = $aclId;
	}

	protected static function checkAcl()
	{
		//don't update acl records usedin is history
	}

	protected function internalSave(): bool
	{
		if(!isset($this->aclId)) {
			// if no aclId set then use module's acl
			$this->aclId = \go\core\model\Module::findByName("community", "history")->getShadowAclId();
		}
		if($this->action != self::$actionMap['delete']) {
			$this->removeAcl = false;
		}

		return parent::internalSave();
	}
}