<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use go\core\db\Column;
use go\core\ErrorHandler;
use go\core\model\Acl;
use go\core\model\User;
use go\core\orm\exception\SaveException;
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
		$tz = new \GO\Base\VObject\VTimezone();

		$username = basename($principalUri);
		$u = User::find(['id'])->where(['username'=>$username])->single();

		$calendars = Calendar::findFor($u->id)
			->where(['isSubscribed'=>1, 'groupId'=>null]);


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
				'share-access' => $calendar->getPermissionLevel() == Acl::LEVEL_MANAGE ? 1 : (($calendar->getPermissionLevel() >= Acl::LEVEL_WRITE && empty($calendar->webcalUri)) ? 3 : 2),
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
		go()->debug("getCalendarObjects($calendarId)");
		//id, uri, lastmodified, etag, calendarid, size, componenttype
		list($type, $id) = explode('-', $calendarId,2);

		$maxMonthsOld = isset(\GO::config()->caldav_max_months_old) ? abs(\GO::config()->caldav_max_months_old) : 6;
		$start = date('Y-m-d', strtotime('-'.$maxMonthsOld.' months'));
		$end = date('Y-m-d', strtotime('+3 years'));

		$result = [];

		switch($type) {
			case 'c': $component = 'vevent';
				$stmt = CalendarEvent::find(['id', 'modifiedAt', 'uid'])
					->select(['cce.id as id','uid','eventdata.modifiedAt as modified', 'uri'])
					->filter(['inCalendars'=>$id, 'before'=> $end, 'after' => $start])
					->fetchMode(\PDO::FETCH_OBJ);
				break;
			case 't' : $component = 'vtodo';
				$stmt =  Task::find(['id', 'modifiedAt', 'uid'])
					->select(['task.id as id','task.uid','task.modifiedAt as modified','uri'])
					->filter(['tasklistId' => $id])->fetchMode(\PDO::FETCH_OBJ);
				break;
			default: return $result;
		}

		go()->debug($stmt);

		foreach ($stmt as $object) {
			$lastModified = strtotime($object->modified);
			$result[] = [
				'id' => $object->id,
				'calendarid' => $type.'-'.$id, // needed for bug in local delivery scheduler
				'uri' => $object->uri ?? (strtr($object->uid, '+/=', '-_.') . '.ics'),
				'lastmodified' => $lastModified,
				'etag' => '"' . $lastModified . '"',
				'component' => $component
			];
		}

		go()->debug($result);

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
				return null;
		}


		if (!$object) {
			go()->log($component. " $objectUri not found in calendar $calendarId!");
			return null;
		}

		$blob = $object->icsBlob();
		try {
			$data = $blob->getFile()->getContents();
		} catch(\Exception$e) {
			ErrorHandler::logException($e);
			$object->vcalendarBlobId = null;
			$blob = $object->icsBlob();
			$data = $blob->getFile()->getContents();
		}

		go()->debug("CalDAVBackend::getCalendarObject($calendarId, $objectUri, ");
		go()->debug($data);
		go()->debug(")");

		$lastModified = strtotime($object->modifiedAt);
		return [
			'id' => $object->id,
			'uri' => $objectUri,
			'lastmodified' => $lastModified,
			'etag' => '"' . $lastModified . '"',
			'size' => $blob->size,
			'calendardata' => $data,
			'component' => $component,
		];
	}

	private static function eventByVEvent($vcalendar, $calendarId) {
		$vevent = $vcalendar->VEVENT[0];
		$uid = (string)$vevent->uid;

		$existingEvent = CalendarEvent::find()->where(['uid'=>$uid, 'calendarId' => $calendarId])->single();
		if($existingEvent){
			return $existingEvent;
		}

		$eventCalendars = go()->getDbConnection()->select(['t.eventId, GROUP_CONCAT(calendarId) as calendarIds'])
			->from('calendar_event', 't')
			->join('calendar_calendar_event', 'c', 'c.eventId = t.eventId', 'LEFT')
			->where(['uid'=>$uid, 'recurrenceId' => null])->single();

		if(!empty($eventCalendars['eventId'])) {
			// add it to the current receivers default calendar
			$added = go()->getDbConnection()->insert('calendar_calendar_event', [
				['calendarId'=>$calendarId, 'eventId'=>$eventCalendars['eventId']]
			])->execute();
			if(!$added) {
				go()->debug('Could not add event to '.$calendarId);
			}
			$event = CalendarEvent::findById(go()->getDbConnection()->getPDO()->lastInsertId());
		} else {
			$event = new CalendarEvent();
			$organizerEmail = str_replace('mailto:', '',(string)$vcalendar->VEVENT[0]->{'ORGANIZER'});
			$event->isOrigin = true;//go()->getAuthState()->getUser(['email'])->email === $organizerEmail; // if you created this event by yourself.
			$event->replyTo = $organizerEmail;
			$event->calendarId = $calendarId;
		}
		return $event;
	}

	public function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		list($type, $id) = explode('-', $calendarId,2);

		go()->debug("CalDAVBackend::createCalendarObject($calendarId, $objectUri, ");
		go()->debug($calendarData);
		go()->debug(")");

		$vCalendar = VObject\Reader::read($calendarData, VObject\Reader::OPTION_FORGIVING);

		switch($type) {
			case 'c': // calendar
				$object = self::eventByVEvent($vCalendar, $id);
				$object = ICalendarHelper::parseVObject($calendarData, $object);
				$object->uri($objectUri);
				$object->attachBlob(ICalendarHelper::makeBlob($object, $calendarData)->id);

				break;
			case 't': // tasklist
				$object = new Task();
				$object = (new VCalendar)->vtodoToTask($vCalendar, $id, $object);

				break;
			default:
				go()->log("incorrect calendarId ".$calendarId. ' for '.$objectUri);
				return false;
		}
		if(!$object->save()) {
			throw new SaveException($object);
		}

		go()->debug("URI: " . $object->uri());

		$etag = $object->modifiedAt->getTimestamp();

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
				go()->debug($object->getModified());
				break;
			case 't':
				$object = (new VCalendar)->vtodoToTask(VObject\Reader::read($calendarData), $id, $object);
				break;
		}


		if(!$object->save()) {
			go()->log("Failed to update event at ".$objectUri);
			return false;
		}

		$etag = $object->modifiedAt->getTimestamp();

		return '"' . $etag . '"';
	}

	public function deleteCalendarObject($calendarId, $objectUri)
	{
		go()->debug("CalDAVBackend::deleteCalendarObject($calendarId, $objectUri)");

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

	/**
	 * Returns a single scheduling object.
	 *
	 * The returned array should contain the following elements:
	 *   * uri - A unique basename for the object. This will be used to
	 *           construct a full uri.
	 *   * calendardata - The iCalendar object
	 *   * lastmodified - The last modification date. Can be an int for a unix
	 *                    timestamp, or a PHP DateTime object.
	 *   * etag - A unique token that must change if the object changed.
	 *   * size - The size of the object, in bytes.
	 *
	 * @param string $principalUri
	 * @param string $objectUri
	 *
	 * @return array
	 */
	public function getSchedulingObject($principalUri, $objectUri)
	{
		go()->debug("CalDAVBackend::getSchedulingObject($principalUri, $objectUri)");

		$stmt = go()->getDbConnection()->getPDO()
			->prepare('SELECT uri, calendardata, lastmodified, etag, size FROM calendar_schedule_object WHERE principaluri = ? AND uri = ?');
		$stmt->execute([$principalUri, $objectUri]);
		$row = $stmt->fetch(\PDO::FETCH_ASSOC);

		if (!$row) {
			return null;
		}

		return [
			'uri' => $row['uri'],
			'calendardata' => $row['calendardata'],
			'lastmodified' => $row['lastmodified'],
			'etag' => '"'.$row['etag'].'"',
			'size' => (int) $row['size'],
		];
	}

	/**
	 * Returns all scheduling objects for the inbox collection.
	 *
	 * These objects should be returned as an array. Every item in the array
	 * should follow the same structure as returned from getSchedulingObject.
	 *
	 * The main difference is that 'calendardata' is optional.
	 *
	 * @param string $principalUri
	 *
	 * @return array
	 */
	public function getSchedulingObjects($principalUri)
	{

		go()->debug("CalDAVBackend::getSchedulingObjects($principalUri)");

		$stmt = go()->getDbConnection()->getPDO()->prepare('SELECT id, calendardata, uri, lastmodified, etag, size FROM calendar_schedule_object WHERE principaluri = ?');
		$stmt->execute([$principalUri]);

		$result = [];
		foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
			$result[] = [
				'calendardata' => $row['calendardata'],
				'uri' => $row['uri'],
				'lastmodified' => $row['lastmodified'],
				'etag' => '"'.$row['etag'].'"',
				'size' => (int) $row['size'],
			];
		}

		return $result;
	}

	/**
	 * Deletes a scheduling object.
	 *
	 * @param string $principalUri
	 * @param string $objectUri
	 */
	public function deleteSchedulingObject($principalUri, $objectUri)
	{
		go()->debug("CalDAVBackend::deleteSchedulingObject($principalUri, $objectUri)");

		$stmt = go()->getDbConnection()->getPDO()->prepare('DELETE FROM calendar_schedule_object WHERE principaluri = ? AND uri = ?');
		$stmt->execute([$principalUri, $objectUri]);
	}

	/**
	 * Creates a new scheduling object. This should land in a users' inbox.
	 *
	 * @param string          $principalUri
	 * @param string          $objectUri
	 * @param string|resource $objectData
	 */
	public function createSchedulingObject($principalUri, $objectUri, $objectData)
	{

		go()->debug("CalDAVBackend::createSchedulingObject($principalUri, $objectUri, ...)");

		$stmt = go()->getDbConnection()->getPDO()->prepare('INSERT INTO calendar_schedule_object (principaluri, calendardata, uri, lastmodified, etag, size) VALUES (?, ?, ?, ?, ?, ?)');

		if (is_resource($objectData)) {
			$objectData = stream_get_contents($objectData);
		}

		$stmt->execute([$principalUri, $objectData, $objectUri, time(), md5($objectData), strlen($objectData)]);
	}


	/**
	 * Searches through all of a users calendars and calendar objects to find
	 * an object with a specific UID.
	 *
	 * This method should return the path to this object, relative to the
	 * calendar home, so this path usually only contains two parts:
	 *
	 * calendarpath/objectpath.ics
	 *
	 * If the uid is not found, return null.
	 *
	 * This method should only consider * objects that the principal owns, so
	 * any calendars owned by other principals that also appear in this
	 * collection should be ignored.
	 *
	 * @param string $principalUri
	 * @param string $uid
	 *
	 * @return string|null
	 */
	public function getCalendarObjectByUID($principalUri, $uid)
	{
		go()->debug("getCalendarObjectByUID($principalUri, $uid)");

		$username = str_replace("principals/", "", $principalUri);

		$userId = User::find()->selectSingleValue("id")->where('username', '=', $username)->single();

		$event = CalendarEvent::find()
			->join('calendar_calendar', 'cal', 'cal.id=cce.calendarId')
			->select('cce.calendarId, uri')
			->where(['uid' => $uid, 'cal.ownerId'=>$userId])
			->fetchMode(\PDO::FETCH_OBJ)
			->single();


		if($event) {
			$path = "c-" . $event->calendarId . "/" . $event->uri;
				go()->debug($path);
			return $path;
		} else {
			return null;
		}


	}
}