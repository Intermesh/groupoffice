<?php

namespace go\core\model;

use go\core\orm\Entity;

class Alert extends Entity
{
	public $id;

	public $entityTypeId;
	public $entityId;

	public $userId;
	public $triggerAt;
	public $sentAt;
	public $recurrenceId;
	public $alertId;

	protected static function defineMapping() {
		return parent::defineMapping()
			->addTable("core_alert", "alert");
	}
}