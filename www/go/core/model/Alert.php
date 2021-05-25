<?php

namespace go\core\model;

use go\core\acl\model\SingleOwnerEntity;
use go\core\orm\EntityType;

class Alert extends SingleOwnerEntity
{
	public $id;

	protected $entityTypeId;

	public $entityId;

	public $title;
	public $body;

	public $userId;
	public $triggerAt;

	public $recurrenceId;
	public $alertId;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("core_alert", "alert");
	}

	public function getEntity() {
		return EntityType::findById($this->entityTypeId)->getName();
	}

	public function setEntity($name) {
		$this->entityTypeId = EntityType::findByName($name)->getId();
	}
}