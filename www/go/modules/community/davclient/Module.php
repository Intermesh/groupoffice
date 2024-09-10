<?php
namespace go\modules\community\davclient;

use GO\Base\Exception\AccessDenied;
use go\core;
use go\core\cron\GarbageCollection;
use go\core\model\User;
use go\core\orm\Property;
use go\core\orm\Query;
use go\core\model\Module as CoreModule;
use go\modules\community\calendar\cron;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\Preferences;
use go\modules\community\calendar\model\BusyPeriod;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;
use go\modules\community\calendar\model\Settings;
use go\modules\community\davclient\model\DavAccount;
use Sabre\VObject\Component\VCalendar;

class Module extends core\Module
{
	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public function defineListeners()
	{
		CalendarEvent::on(CalendarEvent::EVENT_BEFORE_SAVE, self::class, 'onBeforeEventSave');
	}

	public static function onBeforeEventSave($event) {
		$davAccount = DavAccount::findByCalendarId($event->calendarId);
		return !empty($davAccount) ? $davAccount->put($event) : true;
	}

	public static function getTitle(): string
	{
		return 'DAV client';
	}

	protected function rights(): array
	{
		return [
			'mayChangeAccount', // allows DavAcount/set or (hide ui elements that use this)
		];
	}

}