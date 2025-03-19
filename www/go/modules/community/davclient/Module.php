<?php
namespace go\modules\community\davclient;

use go\core;

use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\model;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\davclient\model\DavAccount;

class Module extends core\Module
{

	static $IS_SYNCING = false;

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public function defineListeners()
	{
		CalendarEvent::on(CalendarEvent::EVENT_BEFORE_SAVE, self::class, 'onBeforeEventSave');
		CalendarEvent::on(CalendarEvent::EVENT_BEFORE_DELETE, self::class, 'onBeforeDelete');
		Calendar::on(Calendar::EVENT_MAPPING, self::class, 'onCalendarMap');
	}

	public static function onCalendarMap(Mapping $mapping) {
		$mapping->addScalarProperty('davaccountId','int')->addQuery((new Query())
			->select("davc.davaccountId")
			->join('davclient_calendar', 'davc', 'davc.id=calendar_calendar.id', 'LEFT')
		);
	}

	public static function onBeforeEventSave($event) {
		if(self::$IS_SYNCING) {
			return true;
		}
		$davAccount = DavAccount::findByCalendarId($event->calendarId);
		return !empty($davAccount) ? $davAccount->put($event) : true;
	}

	public static function onBeforeDelete($query) {
		if(self::$IS_SYNCING) {
			return true;
		}

		$success = true;
		$events = CalendarEvent::find(['calendarId', 'uri'])->mergeWith($query);

		foreach($events as $event) {
			$davAccount = DavAccount::findByCalendarId($event->calendarId);
			if(!empty($davAccount)) {
				$success = $davAccount->remove($event) && $success;
			}
		}
		return $success;
	}

	protected function afterInstall(model\Module $model): bool
	{
		cron\RefreshDav::install("*/5 * * * *");
		return parent::afterInstall($model);
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