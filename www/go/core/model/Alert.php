<?php

namespace go\core\model;

use BadMethodCallException;
use Exception;
use GO\Base\Cron\EmailReminders;
use GO\Base\Db\ActiveRecord;
use GO\Base\Exception\AccessDenied;
use go\core\acl\model\SingleOwnerEntity;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\orm\EntityType;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\JSON;
use go\modules\community\comments\model\Comment;
use JsonException;
use stdClass;

class Alert extends SingleOwnerEntity
{
	public static $enabled = true;

	public $id;

	protected $entityTypeId;

	public $entityId;

	public $userId;
	public $triggerAt;

	public $recurrenceId;
	public $tag;

	protected $data;

	/**
	 * Set to true if user has mail reminders enabled
	 * The cron  job sends them
	 *
	 * @see EmailReminders
	 * @var bool
	 */
	public $sendMail = false;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("core_alert", "alert");
	}

	/**
	 * @throws Exception
	 */
	public function getEntity(): string
	{
		return EntityType::findById($this->entityTypeId)->getName();
	}


	/**
	 * Set the entity type
	 *
	 * @param mixed $entity "note", Entity $note or Entitytype instance
	 * @throws Exception
	 *
	 * @return self
	 */
	public function setEntity($entity) {

		if($entity instanceof Entity || $entity instanceof ActiveRecord) {
			$this->entityTypeId = $entity->entityType()->getId();
			$this->entityId = $entity->id;
			return $this;
		}

		if(!($entity instanceof EntityType)) {
			$entity = EntityType::findByName($entity);
		}
		$this->entity = $entity->getName();
		$this->entityTypeId = $entity->getId();

		return $this;
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
	 * @throws JsonException
	 */
	public function getData() {
		return empty($this->data) ? (Object) [] : JSON::decode($this->data, false);
	}


	private $relatedEntity;

	/**
	 * Find the entity this alert belongs to.
	 *
	 * @return Entity|ActiveRecord
	 * @throws AccessDenied
	 * @throws Exception
	 */
	public function findEntity() {

		if(!isset($this->relatedEntity)) {
			$e = EntityType::findById($this->entityTypeId);
			$cls = $e->getClassName();
			if (is_a($cls, ActiveRecord::class, true)) {
				$this->relatedEntity = $cls::model()->findByPk($this->entityId);
			} else {
				$this->relatedEntity = $cls::findById($this->entityId);
			}
		}

		return $this->relatedEntity;
	}


	/**
	 * Set arbitrary notification data
	 *
	 * If this data contains a "title" and "description" property, then this will be
	 * used as such.
	 *
	 * @param array $data
	 * @return Alert
	 * @throws JsonException
	 */
	public function setData(array $data): Alert
	{
		$this->data = JSON::encode(array_merge((array) $this->getData(), $data));

		return $this;
	}

	protected function internalSave(): bool
	{
		if(!self::$enabled) {
			throw new BadMethodCallException("Alerts are disabled. Please check this before creating alerts");
		}

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
		if(!parent::internalSave()) {
			return false;
		}

		return true;
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

	/**
	 * @throws AccessDenied
	 * @throws JsonException
	 */
	private function getProps(): array
	{
		if(!isset($this->props)) {

			$data = $this->getData();

			if(!empty($data->title) && !empty($data->body)) {
				$this->props = ['title' => $data->title, 'body' => $data->body];
			} else {

				$e = $this->findEntity();
				if (!$e) {
					$this->props = ['title' => null, 'body' => null];
				} else {
					$this->props = $e->alertProps($this);
				}
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