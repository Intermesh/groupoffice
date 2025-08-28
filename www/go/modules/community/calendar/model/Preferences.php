<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

// User.calendarPreferences
// go.User.calendarPreferences

class Preferences extends Property
{

	public int $userId;

	/** @var bool  If true, enables multiple time zone support. */
	public bool $useTimeZones = false;

	/** @var bool If true, shows week number in calendar. */
	public bool $showWeekNumbers = true;

	/** If true, show tool tip popup when the mouse hovers over events and tasks. */
	public bool $showTooltips = true;

	/** @var bool If true, show events that you have RSVPed "no" to */
	public bool $showDeclined = true;

	/** @var bool Show birthdays on the calendar */
	public bool $birthdaysAreVisible = false;
	/** @var bool Show birthdays on the calendar */
	public bool $tasksAreVisible = false;
	/** @var bool Show birthdays on the calendar */
	public bool $holidaysAreVisible = false;

	/** @var ?string The id of the user's default calendar. */
	public ?string $defaultCalendarId = null;

	/** @var ?string The id of the calendar where the user's invite go into  */
	public ?string $personalCalendarId = null;

	/**
	 * If true, whenever an event invitation is received, add the event to the
	 * user's calendar with the id given in *autoAddCalendarId*.
	 * @var bool
	 */
	public bool $autoAddInvitations = true;


	/**
	 * If true, for emails where the event is auto-added to the calendar, mark
	 * the email as read and file in the folder specified by *autoAddFileIn*.
	 * @var bool
	 */
	public bool $markReadAndFileAutoAdd = false;

	/**
	 * If true, whenever an update to an event already in the user's calendar
	 * is received, update the event in the user's calendar, or delete it if
	 * the event is cancelled.
	 * @var bool
	 */
	public bool $autoUpdateInvitations = false;

	/**
	 * If true, for emails where the event is auto-updated, mark the email
	 * as read and file in the folder specified by *autoUpdateFileIn*.
	 * @var boolean
	 */
	public bool $markReadAndFileAutoUpdate = false;

	/** @var ?string date last scan was performed in internal_date format eg: "1-Mar-2019" */
	public ?string $lastProcessed = null;
	/** @var ?int UID of the last processed email */
	public ?int $lastProcessedUid = null;


	/** @var int the amount of minutes the event should snap to when dragged over the weekview. */
	public int $weekViewGridSnap = 15;

	/** @var int a percentage of view height a single hour should be */
	public int $weekViewGridSize = 8; //vh

	/** @var ?string Which view to show first when the user opens the calendar. */
	public ?string $startView = null;

	/** @var ?string ISO signed duration for a new event or null for full day. */
	public ?string $defaultDuration = null;

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()->addTable("calendar_preferences", "ccs");
	}

}