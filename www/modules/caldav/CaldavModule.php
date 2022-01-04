<?php

namespace GO\Caldav;

use GO\Caldav\Model\DavEvent;
use GO\Caldav\Model\DavTask;
use go\core\http\Exception;

class CaldavModule extends \GO\Base\Module {
	
	public function depends() {
		return array("dav","sync","calendar");
	}
	
	public static function initListeners() {
		
//		if(\GO::modules()->isInstalled('calendar')) {
//			\GO\Calendar\Model\Event::model()->addListener("save", "GO\Caldav\CaldavModule", "saveEvent");
//			\GO\Calendar\Model\Event::model()->addListener("delete", "GO\Caldav\CaldavModule", "deleteEvent");
//		}
		
//		if(\GO::modules()->isInstalled('tasks'))	{
//			\GO\Tasks\Model\Task::model()->addListener("save", "GO\Caldav\CaldavModule", "saveTask");
//			\GO\Tasks\Model\Task::model()->addListener("delete", "GO\Caldav\CaldavModule", "deleteTask");
//		}
			
		
	}
	
	public static function saveEvent(\GO\Calendar\Model\Event $event, $davEvent, $data = null) {
//		return;
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
//			$davEvent->uri = $event->getUri();			
			$davEvent->data = isset($data) ? $data : self::exportCalendarEvent($event);			
			$davEvent->mtime = $event->mtime;
//			$davEvent->calendarId = $event->calendar_id;
			if($davEvent->save()){
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
	 * @param type $event
	 * @return type
	 */
	static private function exportCalendarEvent($event){
		
		$events=array();
		if(empty($event->rrule)){
			$events[]=$event;
		}else{
			//a recurring event must be sent with all it's exceptions in the same data
			
			$fp = \GO\Base\Db\FindParams::newInstance()
				->order('start_time','ASC')
				->select('t.*')
				->debugSql()
				->ignoreAcl();
			$fp->getCriteria()
				->addCondition('calendar_id', $event->calendar_id)
				->addCondition('uuid', $event->uuid);
			
			$stmt = \GO\Calendar\Model\Event::model()->find($fp);
			
			$sequence=0;
			while($e=$stmt->fetch()){
				
				if($e->private && $e->calendar->user_id != \GO::user()->id){
					$e->name=\GO::t("Private", "calendar");
					$e->location='';
					$e->description='';
				}
				$e->sequence=$sequence;
				$sequence++;
				$events[]=$e;

			}			
		}
		
		$c = new \GO\Base\VObject\VCalendar();		
		$c->add(new \GO\Base\VObject\VTimezone());
		foreach($events as $event){
			$c->add($event->toVObject('REQUEST', false));		
		}
		
		return $c->serialize();
	}
}
