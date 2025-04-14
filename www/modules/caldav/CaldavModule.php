<?php

namespace GO\Caldav;

use GO\Caldav\Model\DavEvent;
use go\core\http\Exception;
use Sabre\VObject\StringUtil;

class CaldavModule extends \GO\Base\Module {
	
	public function depends() {
		return array("dav","sync","calendar");
	}

	public function autoInstall()
	{
		return true;
	}

	
	public static function saveEvent(\GO\Calendar\Model\Event $event, $davEvent, $data = null) {
		if($event->isException()) {
			return;
		}
		
		if(!$davEvent) {		
			$davEvent = new DavEvent();
			$davEvent->id = $event->id;	
			$davEvent->mtime = $event->mtime;
			$davEvent->calendarId = $event->calendar_id;
			$davEvent->data = isset($data) ? $data : self::exportCalendarEvent($event);
			$davEvent->uri = $event->getUri();
			if($davEvent->save()){
				return $davEvent;
			}else{
				throw new Exception("Could not save DAV event");
			}

		} else  {			
			$davEvent->data = isset($data) ? $data : self::exportCalendarEvent($event);
			$davEvent->mtime = $event->mtime;
			if($davEvent->save()) {
				return $davEvent;
			} else{
				throw new Exception("Could not save DAV event");
			}
		}		
	}

	
	public static function deleteEvent(\GO\Calendar\Model\Event $event){
		$davEvent = Model\DavEvent::model()->findByPk($event->id);
		if(!$davEvent)
			return;
		$davEvent->calendarId = $event->calendar_id;
		$davEvent->delete();
	}

	
	/**
	 * Event to VObject data
	 * Copied from CalendarBackend
	 * @param \GO\Calendar\Model\Event $event
	 * @return string
	 */
	static public function exportCalendarEvent($event): string {
		return StringUtil::convertToUTF8($event->exportFullRecurrenceICS()->serialize());
	}


}
