<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\Property;

class Preferences extends Property
{

	public $userId;

	/** @var bool  If true, enables multiple time zone support. */
	public $useTimeZones;

	/** @var bool If true, shows week number in calendar. */
	public $showWeekNumbers;

	/** @var bool If true, show events that you have RSVPed "no" to */
	public $showDeclined;

	/** @var bool Show birthdays on the calendar? */
	public $birthdaysAreVisible;

	/** @var int The id of the user's default calendar. */
	public $defaultCalendarId;


	/**
	 * If true, whenever an event invitation is received, add the event to the
	 * user's calendar with the id given in *autoAddCalendarId*.
	 * @var bool
	 */
	public $autoAddInvitations;

	/** @var int The id of the calendar to auto-add to. */
	public $autoAddCalendarId;

	/**
	 * If true, only automatically add the event if the sender of the invitation is
	 * in the contact group given by the *autoAddGroupId* preference.
	 * @var bool
	 */
	public $onlyAutoAddIfInGroup;

	/** @var int|null The id of the contact group to auto-add events from, or null for All Contacts. */
	public $autoAddGroupId;

	/**
	 * If true, for emails where the event is auto-added to the calendar, mark
	 * the email as read and file in the folder specified by *autoAddFileIn*.
	 * @var bool
	 */
	public $markReadAndFileAutoAdd;

	/** @var int The id of the mailbox to file event invitations in; should default to the Archive folder. */
	public $autoAddFileIn;

	/**
	 * If true, whenever an update to an event already in the user's calendar
	 * is received, update the event in the user's calendar, or delete it if
	 * the event is cancelled.
	 * @var bool
	 */
	public $autoUpdate;

	/**
	 * If true, for emails where the event is auto-updated, mark the email
	 * as read and file in the folder specified by *autoUpdateFileIn*.
	 * @var boolean
	 */
	public $markReadAndFileAutoUpdate;

	/**
	 * @var int The id of the mailbox to file event updates in; should default to the Archive folder.
	 */
	public $autoUpdateFileIn;

	/** @var int the amount of minutes the event should snap to when dragged over the weekview. */
	public $weekViewGridSnap;

	/** @var string Which view to show first when the user opens the calendar. */
	public $startIn;

	/** @var string ISO signed duration for a new event or null for full day. */
	public $defaultDuration;

	protected static function defineMapping(): Mapping {
		return parent::defineMapping()->addTable("calendar_client_settings", "ccs");
	}

}