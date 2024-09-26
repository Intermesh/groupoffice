<?php

namespace go\modules\community\calendar\model;

use go\core\model\Acl;
use go\core\model\User;
use go\core\orm\Query;
use go\modules\community\tasks\convert\VCalendar;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use Sabre\CalDAV;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\DAV;
use Sabre\DAV\PropPatch;
use Sabre\VObject;

class CalDAVBackend extends AbstractBackend implements
//		Sabre\CalDAV\Backend\SyncSupport
	CalDAV\Backend\SchedulingSupport
{
	// Only increase this number if all CalDAV client need a full resync after the upgrade
	const VERSION = 1;

	public $propertyMap = [
		'{DAV:}displayname' => 'name',
		'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description',
		//'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => 'timezone',
		'{http://apple.com/ns/ical/}calendar-order' => 'sortOrder',
		'{http://apple.com/ns/ical/}calendar-color' => 'color',
	];

	public function getCalendarsForUser($principalUri)
	{
		go()->debug("CalDAVBackend::getCalendarsForUser($principalUri)");
		$result = [];
		$tz = new \GO\Base\VObject\VTimezone(); // same for each?
		// using logged in user, but should use PrincipalUri
		$calendars = Calendar::find()->where(['isSubscribed'=>1, 'groupId'=>null]);
		$username = basename($principalUri);
		$u = User::find(['id'])->where(['username'=>$username])->single();
		foreach($calendars as $calendar) {

			$uri = 'c-'.$calendar->id;
			$result[] = [
				'id' => $uri,
				'uri' => $uri,
				'principaluri' => $principalUri, // echo back
				'{DAV:}displayname' => $calendar->name,
				//'{http://apple.com/ns/ical/}refreshrate' => '0',
				'{http://apple.com/ns/ical/}calendar-order' => $calendar->sortOrder,
				'{http://apple.com/ns/ical/}calendar-color' => '#'.$calendar->color,
				'{urn:ietf:params:xml:ns:caldav}calendar-description' => $calendar->description,
				'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\r\n" . $tz->serialize() . "END:VCALENDAR",
				'{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VEVENT']),
				// free when calendar does not belong to the user
				'{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar->ownerId == $u->id ? 'opaque' : 'transparent'),

				'{http://calendarserver.org/ns/}getctag' => 'GroupOffice/calendar/'.self::VERSION.'/'.$calendar->highestItemModSeq(),
				//'{http://calendarserver.org/ns/}subscribed-strip-todos' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-alarms' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-attachments' => '0',
				'{http://sabredav.org/ns}sync-token' => self::VERSION.'-'.$calendar->highestItemModSeq(),
				'share-resource-uri' => '/ns/share/'.$uri,
				// 1 = owner, 2 = readonly, 3 = readwrite
				'share-access' => $calendar->getPermissionLevel() == Acl::LEVEL_MANAGE ? 1 : ($calendar->getPermissionLevel() >= Acl::LEVEL_WRITE ? 3 : 2),
			];
		}
		$tasklists = TaskList::find()->where(['isSubscribed'=>1, 'role'=>1])
			->filter(["permissionLevel" => Acl::LEVEL_READ]);
		foreach($tasklists as $tasklist) {

			$uri = 't-'.$tasklist->id;
			$result[] = [
				'id' => $uri,
				'uri' => $uri,
				'principaluri' => $principalUri, // echo back
				'{DAV:}displayname' => $tasklist->name,
				//'{http://apple.com/ns/ical/}refreshrate' => '0',
				'{http://apple.com/ns/ical/}calendar-order' => $tasklist->sortOrder,
				'{http://apple.com/ns/ical/}calendar-color' => '#'.$tasklist->color,
				'{urn:ietf:params:xml:ns:caldav}calendar-description' => $tasklist->description,
				'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\r\n" . $tz->serialize() . "END:VCALENDAR",
				'{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VTODO']),
				// free when calendar does not belong to the user
				'{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($tasklist->ownerId == $u->id ? 'opaque' : 'transparent'),

				'{http://calendarserver.org/ns/}getctag' => 'GroupOffice/calendar/'.self::VERSION.'/'.$tasklist->highestItemModSeq(),
				//'{http://calendarserver.org/ns/}subscribed-strip-todos' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-alarms' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-attachments' => '0',
				'{http://sabredav.org/ns}sync-token' => self::VERSION.'-'.$tasklist->highestItemModSeq(),
				'share-resource-uri' => '/ns/share/'.$uri,
				// 1 = owner, 2 = readonly, 3 = readwrite
				'share-access' => $tasklist->getPermissionLevel() == Acl::LEVEL_MANAGE ? 1 : ($tasklist->getPermissionLevel() >= Acl::LEVEL_WRITE ? 3 : 2),
			];
		}

		return $result;
	}

	public function createCalendar($principalUri, $calendarUri, array $properties)
	{
		go()->debug("CalDAVBackend::createCalendar($principalUri, $calendarUri)");
		$sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
		$type = 'VEVENT';
		if (isset($properties[$sccs])) {
			if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
				throw new DAV\Exception('The '.$sccs.' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
			}
			$type = $properties[$sccs]->getValue();
		}
		$transp = '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp';
		if (isset($properties[$transp])) {
			$ownerId = $properties[$transp]->getValue() === 'transparent' ? null : go()->getUserId();
		}
		$values = ['ownerId' => $ownerId];
		foreach ($this->propertyMap as $xmlName => $dbName) {
			if (isset($properties[$xmlName])) {
				$values[$dbName] = $properties[$xmlName];
			}
		}
		if($values['color']) {
			$values['color'] = substr($values['color'], 1); // remove #
		}
		switch($type[0]) {
			case 'VEVENT': //
				$prefix = 'c-';
				$cal = new Calendar();
				break;
			case 'VTODO': // task
				$prefix = 't-';
				$values['ownerId'] = go()->getUserId(); //required
				$cal = new TaskList();
				break;
			default: // combined?
				return false;
		}

		$cal->setValues($values);
		if(!$cal->save()) {
			throw new DAV\Exception('Could not create calendar '.var_export($cal->getValidationErrors(), true));
		}

		return $prefix.$cal->id;
	}

	public function updateCalendar($calendarId, PropPatch $propPatch)
	{
		go()->debug("CalDAVBackend::updateCalendar($calendarId)");
		list($type, $id) = explode('-', $calendarId,2);

		$supportedProperties = array_keys($this->propertyMap);
		$supportedProperties[] = '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp';

		$propPatch->handle($supportedProperties, function ($mutations) use ($id, $type) {
			$newValues = [];
			foreach ($mutations as $propertyName => $propertyValue) {
				switch ($propertyName) {
					case '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp':
						if($type == 'c')
							$newValues['includeInAvailability'] = 'transparent' === $propertyValue->getValue() ? 'none' : 'all';
						break;
					case '{http://apple.com/ns/ical/}calendar-color':
						if($type == 'c')
							$newValues['color'] = substr($propertyValue, 1);
						break;
					default:
						$newValues[$this->propertyMap[$propertyName]] = $propertyValue;
						break;
				}
			}
			switch($type) {
				case 'c':
					$cal = Calendar::findById($id);
					break;
				case 't':
					$cal = Tasklist::findById($id);
					unset($newValues['sortOrder']); // not supported for tasklist
					break;
			}
			$cal->setValues($newValues);
			$cal->save();

			return true;
		});
	}

	public function deleteCalendar($calendarId)
	{
		list($type, $id) = explode('-', $calendarId,2);
		switch($type) {
			case 'c': Calendar::delete(['id' => $id]); break;
			case 't': TaskList::delete(['id' => $id]); break;
		}
	}

	public function getCalendarObjects($calendarId)
	{
		//id, uri, lastmodified, etag, calendarid, size, componenttype
		list($type, $id) = explode('-', $calendarId,2);

		$maxMonthsOld = isset(\GO::config()->caldav_max_months_old) ? abs(\GO::config()->caldav_max_months_old) : 6;
		$start = date('Y-m-d', strtotime('-'.$maxMonthsOld.' months'));
		$end = date('Y-m-d', strtotime('+3 years'));

		$result = [];

		switch($type) {
			case 'c': $component = 'vevent';
				$stmt = CalendarEvent::find(['id', 'modifiedAt', 'uid'])
					->select(['cce.id as id','uid','eventdata.modifiedAt as modified','veventBlobId as etag, uri'])
					->where(['calendarId' => $id])
					->filter(['before'=> $end, 'after' => $start])
					->fetchMode(\PDO::FETCH_OBJ);
				break;
			case 't' : $component = 'vtodo';
				$stmt =  Task::find(['id', 'modifiedAt', 'uid'])
					->select(['task.id as id','task.uid','task.modifiedAt as modified','vcalendarBlobId as etag, uri'])
					->filter(['tasklistId' => $id])->fetchMode(\PDO::FETCH_OBJ);
				break;
			default: return $result;
		}

		foreach ($stmt as $object) {
			$result[] = [
				'id' => $object->id,
				'calendarid' => $type.'-'.$id, // needed for bug in local delivery scheduler
				'uri' => $object->uri ?? (strtr($object->uid, '+/=', '-_.') . '.ics'),
				'lastmodified' => strtotime($object->modified),
				'etag' => '"' . $object->etag . '"',
				'component' => $component
			];
		}

		return $result;
	}

	/**
	 * Check if this is only called when the getCalendarObjects does not provide the calendardata
	 */
	public function getCalendarObject($calendarId, $objectUri)
	{
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);
		list($type, $id) = explode('-', $calendarId,2);

		switch($type) {
			case 'c': // calendar
				$component = 'vevent';
				$object = CalendarEvent::find()->where(['cce.calendarId'=> $id, 'eventdata.uri'=>$objectUri])->single();
				break;
			case 't': // tasklist
				$component = 'vtodo';
				$object = Task::find()->where(['task.tasklistId'=> $id, 'task.uid'=>$uid])->single();
				break;
			default:
				go()->log("incorrect calendarId ".$calendarId. ' for '.$objectUri);
				return false;
		}


		if (!$object) {
			go()->log($component. " $objectUri not found in calendar $calendarId!");
			return false;
		}

		$blob = $object->icsBlob();
		$data = $blob->getFile()->getContents();

		go()->debug("CalDAVBackend::getCalendarObject($calendarId, $objectUri, ");
		go()->debug($data);
		go()->debug(")");

		return [
			'id' => $object->id,
			'uri' => $objectUri,
			'lastmodified' => strtotime($object->modifiedAt),
			'etag' => '"' . $blob->id . '"',
			'size' => $blob->size,
			'calendardata' => $data,
			'component' => $component,
		];
	}

	public function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		list($type, $id) = explode('-', $calendarId,2);

		go()->debug("CalDAVBackend::createCalendarObject($calendarId, $objectUri, ");
		go()->debug($calendarData);
		go()->debug(")");

		switch($type) {
			case 'c': // calendar
				$object = new CalendarEvent();
				//$object->uid = $uid;
				$object = ICalendarHelper::parseVObject($calendarData, $object);
				$object->uri($objectUri);
				// The attached blob must be identical to the data used to create the event
				$object->attachBlob(ICalendarHelper::makeBlob($object, $calendarData)->id);
				if(Calendar::addEvent($object, $id) === null) {
					throw new \Exception('Could not create calendar event');
				}
				$etag = $object->icsBlobId();
				break;
			case 't': // tasklist
				$object = new Task();
				$object = (new VCalendar)->vtodoToTask(VObject\Reader::read($calendarData, VObject\Reader::OPTION_FORGIVING), $id, $object);
				$object->save();
				$etag = $object->vcalendarBlobId;
				break;
			default:
				go()->log("incorrect calendarId ".$calendarId. ' for '.$objectUri);
				return false;
		}


		return '"' . $etag . '"';
	}

	public function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		list($type, $id) = explode('-', $calendarId,2);
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);

		go()->debug("CalDAVBackend::updateCalendarObject($calendarId, $objectUri, ");
		go()->debug($calendarData);
		go()->debug(")");

		//$extraData = $this->getDenormalizedData($calendarData);
		/** @var CalendarEvent $object */
		$object = $type==='c' ? CalendarEvent::find()->where(['uri'=>$objectUri, 'calendarId'=>$id])->single() :
			Task::find()->where(['task.tasklistId' => $id, 'task.uid' => $uid])->single();
		if(!$object){
			go()->log("Object $objectUri not found in calendar $calendarId");
			return false;
		}
		switch($type) {
			case 'c':
				$object = ICalendarHelper::parseVObject($calendarData, $object);
				// The attached blob must be identical to the data used to create the event
				$object->attachBlob(ICalendarHelper::makeBlob($object, $calendarData)->id);
				$etag = $object->icsBlobId();

				go()->debug($object->getModified());
				break;
			case 't':
				$object = (new VCalendar)->vtodoToTask(VObject\Reader::read($calendarData), $id, $object);
				$etag = $object->vcalendarBlobId;
				break;
		}


		if(!$object->save()) {
			go()->log("Failed to update event at ".$objectUri);
			return false;
		}

		return '"'.$etag.'"';
	}

	public function deleteCalendarObject($calendarId, $objectUri)
	{
		list($type, $id) = explode('-', $calendarId,2);
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);

		$query = (new Query())->select('id');
		switch($type) {
			case 'c' :
				$query->from('calendar_calendar_event','cce')
					->join('calendar_event', 'ev', 'ev.eventId = cce.eventId')
					->where(['calendarId' => $id, 'ev.uri'=> $objectUri]);
				CalendarEvent::delete($query);
				break;
			case 't':
				$query->from('tasks_task','task')
					->where(['task.tasklistId' => $id, 'task.uid'=> $uid]);
				Task::delete($query);
				break;
		}

	}

	public function getSchedulingObject($principalUri, $objectUri)
	{
		return null;
	}

	public function getSchedulingObjects($principalUri)
	{
		return [];
	}

	public function deleteSchedulingObject($principalUri, $objectUri)
	{
		return null;
	}

	public function createSchedulingObject($principalUri, $objectUri, $objectData)
	{
		return null;
	}
}