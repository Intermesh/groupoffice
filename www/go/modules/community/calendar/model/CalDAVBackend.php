<?php

namespace go\modules\community\calendar\model;

use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\orm\Query;
use go\modules\community\tasks\model\TaskList;
use Sabre\CalDAV\Backend\AbstractBackend;
use Sabre\CalDAV;
use Sabre\DAV\PropPatch;
use Sabre\VObject;
use Sabre\DAV;

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
		$result = [];
		$tz = new \GO\Base\VObject\VTimezone(); // same for each?
		// using logged in user, but should use PrincipalUri
		$calendars = Calendar::find()->where(['isSubscribed'=>1]);
		foreach($calendars as $calendar) {

			$uri = preg_replace('/[^\w-]*/', '', (strtolower(str_replace(' ', '-', $calendar->name)))).'-'.$calendar->id;
			$result[] = [
				'id' => $calendar->id,
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
				'{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar->isOwned() ? 'opaque' : 'transparent'),

				'{http://calendarserver.org/ns/}getctag' => 'GroupOffice/calendar/'.self::VERSION.'/'.$calendar->highestItemModSeq(),
				//'{http://calendarserver.org/ns/}subscribed-strip-todos' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-alarms' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-attachments' => '0',
				'{http://sabredav.org/ns}sync-token' => self::VERSION.'-'.$calendar->highestItemModSeq(),
				'share-resource-uri' => '/ns/share/'.$calendar->id,
				// 1 = owner, 2 = readonly, 3 = readwrite
				'share-access' => $calendar->getPermissionLevel() == Acl::LEVEL_MANAGE ? 1 : ($calendar->getPermissionLevel() >= Acl::LEVEL_WRITE ? 3 : 2),
			];

		}

		return $result;
	}

	public function createCalendar($principalUri, $calendarUri, array $properties)
	{
		$sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
		$type = 'VEVENT';
		if (isset($properties[$sccs])) {
			if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
				throw new DAV\Exception('The '.$sccs.' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
			}
			$type = $properties[$sccs]->getValue();
		}

		switch($type) {
			case 'VEVENT': //
				$cal = new Calendar();
				break;
			case 'VTODO': // task
				$cal = new TaskList();
				break;
			default: // combined?
				$cal = new Calendar(); // and attach tasklist?
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
		$cal->setValues($values);
		$cal->save();

		return $cal->id;
	}

	public function updateCalendar($calendarId, PropPatch $propPatch)
	{
		$supportedProperties = array_keys($this->propertyMap);
		$supportedProperties[] = '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp';

		$propPatch->handle($supportedProperties, function ($mutations) use ($calendarId) {
			$newValues = [];
			foreach ($mutations as $propertyName => $propertyValue) {
				switch ($propertyName) {
					case '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp':
						$newValues['includeInAvailability'] = 'transparent' === $propertyValue->getValue() ? 'none' : 'all';
						break;
					default:
						$newValues[$this->propertyMap[$propertyName]] = $propertyValue;
						break;
				}
			}

			$cal = Calendar::findById($calendarId);
			$cal->setValues($newValues);
			$cal->save();

			return true;
		});
	}

	public function deleteCalendar($calendarId)
	{
		list($type, $id) = explode('-', $calendarId);
		switch($type) {
			case 'c': Calendar::delete(['id' => $id]); break;
			case 't': TaskList::delete(['id' => $id]); break;
		}
	}

	public function getCalendarObjects($calendarId)
	{
		//id, uri, lastmodified, etag, calendarid, size, componenttype
		$events = CalendarEvent::find()
			->select(['cce.id as id','uid','eventdata.modifiedAt as modified','size','veventBlobId'])
			->join('core_blob', 'b','b.id = veventBlobId', 'LEFT')
			->where(['calendarId'=> $calendarId])
			->fetchMode(\PDO::FETCH_OBJ);

		$result = [];
		foreach($events as $event) {
			//$blob = $event->icsBlob();
			$result[] = [
				'id' => $event->id,
				'calendarid' => $calendarId, // needed for bug in local delivery schedualer
				'uri' => str_replace('/', '+', $event->uid) . '.ics',
				'lastmodified' => strtotime($event->modified),
				'etag' => '"' . $event->veventBlobId . '"',
				'size' => $event->size,
				'component' => 'vevent'
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

		/** @var CalendarEvent $event */
		$event = CalendarEvent::find()
			//->join('core_blob', 'b','b.id = veventBlobId', 'LEFT')
			//->join('calendar_event_user', 'u', 'u.eventId = eventdata.id')
			->where(['cce.calendarId'=> $calendarId, 'eventdata.uid'=>$uid])->single();
//if($event->uid === '0a3b1bbb-dcbc-42d2-8762-6ad7493eef34') {
//	$e=0;
//}
		if (!$event) {
			go()->log("Event $objectUri not found in calendar $calendarId!");
			return false;
		}

		$blob = $event->icsBlob();

		return [
			'id' => $event->id,
			'uri' => $objectUri,
			'lastmodified' => strtotime($event->modifiedAt),
			'etag' => '"' . $blob->id . '"',
			'size' => $blob->size,
			'calendardata' => $blob->getFile()->getContents(),
			'component' => 'vevent',
		];
	}

	// TODO: implement `public function getMultipleCalendarObjects($calendarId, array $uris)` for speedup

	public function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		//$calendar = Calendar::findById($calendarId);
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);
		$event = new CalendarEvent();
		$event->uid = $uid;
		$event->calendarId = $calendarId;
		$event = ICalendarHelper::fromICal($calendarData, $event);
		if(!$event->save()) {
			throw new \Exception('Could not create calendar event');
		}

		return '"' . $event->icsBlobId() . '"';
	}

	public function updateCalendarObject($calendarId, $objectUri, $calendarData)
	{
		//$extraData = $this->getDenormalizedData($calendarData);
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);
		/** @var CalendarEvent $event */
		$event = CalendarEvent::find()->where(['uid'=>$uid, 'calendarId'=>$calendarId])->single();if(!$event){
			go()->log("Event $objectUri not found in calendar $calendarId!");
			return false;
		}
		$event = ICalendarHelper::fromICal($calendarData, $event);
		if(!$event->save()) {
			go()->log("Failed to update event at ".$objectUri);
			return false;
		}

		return '"'.$event->icsBlobId().'"';
	}

	public function deleteCalendarObject($calendarId, $objectUri)
	{
		// objectUri = uid + '.ics' ?
		$uid = pathinfo($objectUri, PATHINFO_FILENAME);
		$query = (new Query())->select('id')->from('calendar_calendar_event','t')
			->join('calendar_event', 'ev', 'ev.id = eventId')
			->where(['calendarId' => $calendarId, 'ev.uid'=> $uid]);
		CalendarEvent::delete($query);
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