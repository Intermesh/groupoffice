<?php
namespace go\modules\community\calendar\model;

use go\core\acl\model\AclOwnerEntity;
use go\core\orm\Mapping;

/**
 * Calendar entity
 *
 */
class Calendar extends AclOwnerEntity {

	/* Include In Availability */
	const All = 'all';
	const Attending = 'attending';
	const None = 'none';

	const UserProperties = ['name', 'color', 'sortOrder', 'isVisible', 'isSubscribed'];

	public $id;
	/** @var string The user-visible name of the calendar */
	public $name;
	public $description;
	/** @var string Any valid CSS color value. The color to be used when displaying events associated with the calendar */
	public $color;
	/** @var int uint32 Defines the sort order of calendars when presented in the client’s UI, so it is consistent between devices */
	public $sortOrder = 0;
	/** @var bool Has the user indicated they wish to see this Calendar in their client */
	public $isSubscribed;
	/** @var bool Should the calendar’s events be displayed to the user at the moment? */
	public $isVisible = true; // per user
	/**
	 * @var string (default: all) Should the calendar’s events be used as part of availability calculation?
	 * This MUST be one of:
	 *	- all: all events are considered.
	 *	- attending: events the user is a confirmed or tentative participant of are considered.
	 *	- none: all events are ignored (but may be considered if also in another calendar).
	 */
	public $includeInAvailability = self::All;

	/** @var ?string default for event. If NULL client will use the Users default timeZone  */
	public $timeZone;

	public $defaultAlertsWithTime;
	public $defaultAlertsWithoutTime;
	public $ownerId;
	public $createdBy;

	/**
	 *  per-user OR default = true ONLY IF current user is the owner
	 * @return bool
	 */
	public function getIsSubscribed() {
		return ($this->isSubscribed === NULL) ? $this->isOwner() :  $this->isSubscribed;
	}

	private function isOwner() {
		return $this->ownerId === go()->getUserId();
	}

	public function getMyRights() {
		$lvl = $this->getPermissionLevel();
		return [
			'mayReadFreeBusy' => $lvl >= 5,
			'mayReadItems' => $lvl >= 10,
			'mayWriteAll' => $lvl >= 15,
			'mayWriteOwn' => $lvl >= 20,
			'mayUpdatePrivate' => $lvl >= 25,
			'mayRSVP' => $lvl >= 30,
			'mayAdmin' => $lvl >= 50,
			'mayDelete' => $this->isOwner(),
		];
	}
	
	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable("calendar_calendar")
			->addUserTable('calendar_calendar_user', 'caluser',['id' => 'calendarId'], self::UserProperties)
			->addMap('defaultAlertsWithTime', DefaultAlert::class,  ['id'=>'calendarId'])
			->addMap('defaultAlertsWithoutTime', DefaultAlertWT::class,  ['id'=>'calendarId']);
	}

	public function shareesActAs() {
		return $this->isOwner() ? 'self' : 'secretary';
	}
}
