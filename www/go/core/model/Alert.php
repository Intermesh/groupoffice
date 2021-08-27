<?php

namespace go\core\model;

use go\core\acl\model\SingleOwnerEntity;

use go\core\db\Criteria;
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
	public $tag;

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

	protected static function defineFilters()
	{
		return parent::defineFilters()
			->add('userId', function(Criteria $criteria, $value) {
				$criteria->where('userId', '=', $value);
			});
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
	 * @param array $data
	 * @return Alert
	 */
	public function setData(array $data) {
		$this->data = json_encode(array_merge($this->getData(), $data));

		return $this;
	}

	protected function internalSave()
	{
		if($this->isNew() && isset($this->tag)) {
			//skip dismiss action below in internal delete
			$query = Query::normalize([
				'entityTypeId' => $this->entityTypeId,
				'entityId' => $this->entityId,
				'tag' => $this->tag,
				'userId' => $this->userId
			])
				// Skip dismiss update in internalDelete below
				->setData(['preventDismiss' => true]);

			if(!static::delete($query)) {
				return false;
			}
		}
		return parent::internalSave();
	}

	protected static function internalDelete(Query $query)
	{
		if(empty($query->getData()['preventDismiss'])) {
			$alerts = Alert::find()->mergeWith($query);

			$grouped = [];
			foreach ($alerts as $alert) {
				$entityName = $alert->getEntity();
				if (!isset($grouped[$entityName])) {
					$grouped[$entityName] = [];
				}

				$grouped[$entityName][] = $alert;
			}

			foreach ($grouped as $entityName => $alerts) {
				$cls = EntityType::findByName($entityName)->getClassName();
				$cls::dismissAlerts($alerts);
			}
		}

		return parent::internalDelete($query);
	}

}