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

	protected $data;

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

	/**
	 * Get arbitrary notification data
	 *
	 * @return array
	 */
	public function getData() {
		return empty($this->data) ? [] : json_decode($this->data, true);
	}

	/**
	 * Set arbitrary notification data
	 *
	 * @param $data
	 */
	public function setData(array $data) {
		$this->data = json_encode(array_merge($this->getData(), $data));
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