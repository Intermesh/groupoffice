<?php
namespace go\modules\community\calendar;

use go\core;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\ICalendarHelper;

class Module extends core\Module
{

	public function getAuthor(): string
	{
		return "Intermesh BV <mdhart@intermesh.nl>";
	}

	public static function getTitle(): string
	{
		return 'Calendar GOUI';
	}

	public function downloadIcs($key) {
		$ev = CalendarEvent::findById($key);

		header('Content-Type: text/calendar; charset=UTF-8; component=vevent');
		echo ICalendarHelper::toVObject($ev)->serialize();
	}

//	public function pageTest($eventId) {
//
//		go()->setAuthState(new core\jmap\State());
//		$event = CalendarEvent::findById($eventId);
//		echo Scheduler::mailBody($event,
//			(object)['name'=>'test','email'=>'admin@intermesh.nl'],
//			'Update');
//		exit();
//	}

//	protected function rights(): array
//	{
//		return [
//			'mayChangeAddressbooks', // allows AddressBook/set (hide ui elements that use this)
//			'mayExportContacts', // Allows users to export contacts
//		];
//	}
}