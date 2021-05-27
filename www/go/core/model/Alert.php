<?php

namespace go\core\model;

use go\core\acl\model\SingleOwnerEntity;

use go\core\orm\EntityType;
use go\core\orm\Query;

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

	protected static function internalDelete(Query $query)
	{
		$alerts = Alert::find()->mergeWith($query);

		$grouped = [];
		foreach($alerts as $alert) {
			$entityName = $alert->getEntity();
			if(!isset($grouped[$entityName])) {
				$grouped[$entityName] = [];
			}

			$grouped[$entityName][] = $alert;
		}

		foreach($grouped as $entityName => $alerts) {
			$cls = EntityType::findByName($entityName)->getClassName();
			$cls::dismissAlerts($alerts);
		}

		return parent::internalDelete($query);
	}
}