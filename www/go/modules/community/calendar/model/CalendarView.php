<?php

namespace go\modules\community\calendar\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\model\Module;
use go\core\orm\Mapping;

/**
 * View model
 */
class CalendarView extends AclOwnerEntity {

	public ?string $id;

	public string $name;

	/** @var ?int could be NULL for global categories */
	protected ?int $ownerId = null;
	public ?string $defaultView; // null if not changing

	protected ?string $calendarIds;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("calendar_view", "view");
	}
	public function getCalendarIds() {
		return json_decode($this->calendarIds);
	}

	public function setCalendarIds($arr) {
		$this->calendarIds = json_encode($arr);
	}

	public function setOwnerId($id) {
		if(go()->getAuthState()->isAdmin())
			$this->ownerId = $id; // only admin may create global categories
	}

	public function getOwnerId() {
		return $this->ownerId;
	}

	protected function init()
	{
		parent::init();

		if($this->isNew() )  {
			$this->ownerId = go()->getUserId();
		}
	}

	protected function canCreate(): bool
	{
		return Module::findByName('community', 'calendar')->getUserRights()->mayChangeViews;
	}

	public function getMyRights() {
		$lvl = $this->getPermissionLevel();
		return [
			'mayRead' => $lvl >= 10,
			'mayWrite' => $lvl >= 30,
			'mayAdmin' => $lvl >= 50
		];
	}

}