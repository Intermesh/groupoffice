<?php

namespace go\core\model;

use GO\Base\Db\ActiveRecord;
use go\core\acl\model\SingleOwnerEntity;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

class Alert extends SingleOwnerEntity
{
	public $id;

	protected $entityTypeId;

	public $entityId;

	public $userId;
	public $triggerAt;

	public $recurrenceId;
	public $tag;

	protected $data;

	public $sendMail = false;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_alert", "alert");
	}

	public function getEntity() {
		return EntityType::findById($this->entityTypeId)->getName();
	}

	public function setEntity($name) {
		$this->entityTypeId = EntityType::findByName($name)->getId();
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('userId', function(Criteria $criteria, $value) {
				$criteria->where('userId', '=', $value);
			});
	}

	/**
	 * Get arbitrary notification data
	 *
	 * @return StdClass
	 */
	public function getData() {
		return empty($this->data) ? (Object) [] : json_decode($this->data, false);
	}

	/**
	 * Find the entity this alert belongs to.
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


	/**
	 * Set arbitrary notification data
	 *
	 * @param array $data
	 * @return Alert
	 */
	public function setData(array $data) {
		$this->data = json_encode(array_merge((array) $this->getData(), $data));

		return $this;
	}

	protected function internalSave(): bool
	{
		if($this->isNew()) {

			$this->sendMail = User::findById($this->userId, ['mail_reminders'])->mail_reminders;

			if(isset($this->tag)) {
				//skip dismiss action below in internal delete
				$query = Query::normalize([
					'entityTypeId' => $this->entityTypeId,
					'entityId' => $this->entityId,
					'tag' => $this->tag,
					'userId' => $this->userId
				])
					// Skip dismiss update in internalDelete below
					->setData(['preventDismiss' => true]);

				if (!static::delete($query)) {
					return false;
				}
			}
		}
		return parent::internalSave();
	}

	protected static function internalDelete(Query $query): bool
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

	private $props;

	private function getProps() {
		if(!isset($this->props)) {
			$e = $this->findEntity();
			if (!$e) {
				$this->props = ['title' => null, 'body' => null];
			} else{
				$this->props = $e->alertProps($this);
			}
		}

		return $this->props;
	}

	public function getTitle() {
		return $this->getProps()['title'];
	}
	public function getBody() {
		return $this->getProps()['body'];
	}

}