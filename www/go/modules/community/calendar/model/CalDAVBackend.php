<?php

namespace go\modules\community\calendar\model;

use GO\Base\Html\Error;
use go\core\auth\TemporaryState;
use go\core\db\Column;
use go\core\ErrorHandler;
use go\core\model\Acl;
use go\core\model\Principal;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\orm\Query;
use go\core\util\DateTime;
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
		$u = User::find(['id', 'calendarPreferences'])->where(['username'=>$username])->single();
		if(!$u) {
			throw new DAV\Exception\NotFound("Principal $principalUri not found");
		}

		// We want to sort the personal calendar on top for caldav scheduling as it takes the first one for invites
		// See schedule-default-calendar-URL handling in  Sabre\CalDAV\Schedule\Plugin line 222
		$personalCalendarId = $u->calendarPreferences->personalCalendarId ?? null;

		// The calendars of the principal, not of the logged in user
		$calendars = Calendar::findFor($u->id)
			->where(['isSubscribed'=>1,'syncToDevice'=>1, 'groupId'=>null]);
		Calendar::applyAclToQuery($calendars, Acl::LEVEL_READ, $u->id);

		// Another user's calendars are only requested by Sabre for the free / busy information of that user. Use the same
		// rules as BusyPeriod::fetch(): only the calendars of that user and only when the logged in user may see their
		// free / busy information.
		$forOtherUser = $u->id != go()->getUserId();
		if($forOtherUser) {
			$calendars->andWhere('calendar_calendar.ownerId', '=', [$u->id, null]);
			Calendar::applyAclToQuery($calendars, Calendar::LEVEL_FREEBUSY);
		}

		foreach($calendars as $calendar) {
			$level = Acl::getUserPermissionLevel($calendar->findAclId(), $u->id);

			$uri = 'c-'.$calendar->id;
			$record = [
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
				'{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp($calendar->includeInAvailability === 'none' ? 'transparent' : 'opaque'),

				'{http://calendarserver.org/ns/}getctag' => 'GroupOffice/calendar/'.self::VERSION.'/'.$calendar->highestItemModSeq(),
				//'{http://calendarserver.org/ns/}subscribed-strip-todos' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-alarms' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-attachments' => '0',
				'{http://sabredav.org/ns}sync-token' => self::VERSION.'-'.$calendar->highestItemModSeq(),
				'share-resource-uri' => '/ns/share/'.$uri,
				// 1 = owner, 2 = readonly, 3 = readwrite: Unused as we don't have the sharing plugin enabled
				'share-access' => $calendar->getOwnerId() == $u->id ? 1 : (($level >= Acl::LEVEL_WRITE && empty($calendar->webcalUri)) ? 3 : 2),
				// Sabre grants the principal write access unless the calendar is read-only
				'{http://sabredav.org/ns}read-only' => $level < Acl::LEVEL_WRITE || !empty($calendar->webcalUri),
			];

			if($calendar->id == $personalCalendarId) {
				array_unshift($result, $record);
			} else {
				$result[] = $record;
			}
		}
		if($forOtherUser) {
			// Task lists have no free / busy information
			return $result;
		}

		$tasklists = TaskList::findFor($u->id)->where(['isSubscribed'=>1,'syncToDevice'=>1, 'role'=>1]);
		TaskList::applyAclToQuery($tasklists, Acl::LEVEL_READ, $u->id);
		foreach($tasklists as $tasklist) {
			$level = Acl::getUserPermissionLevel($tasklist->findAclId(), $u->id);

			$uri = 't-'.$tasklist->id;
			$result[] = [
				'id' => $uri,
				'uri' => $uri,
				'principaluri' => $principalUri, // echo back
				'{DAV:}displayname' => $tasklist->name,
				//'{http://apple.com/ns/ical/}refreshrate' => '0',
				'{http://apple.com/ns/ical/}calendar-order' => $tasklist->sortOrder ?: $tasklist->id,
				'{http://apple.com/ns/ical/}calendar-color' => '#'.$tasklist->color,
				'{urn:ietf:params:xml:ns:caldav}calendar-description' => $tasklist->description,
				'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\r\n" . $tz->serialize() . "END:VCALENDAR",
				'{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set' => new CalDAV\Xml\Property\SupportedCalendarComponentSet(['VTODO']),
				// free when calendar does not belong to the user
				'{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp' => new CalDAV\Xml\Property\ScheduleCalendarTransp('transparent'),

				'{http://calendarserver.org/ns/}getctag' => 'GroupOffice/calendar/'.self::VERSION.'/'.$tasklist->highestItemModSeq(),
				//'{http://calendarserver.org/ns/}subscribed-strip-todos' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-alarms' => '0',
				//'{http://calendarserver.org/ns/}subscribed-strip-attachments' => '0',
				'{http://sabredav.org/ns}sync-token' => self::VERSION.'-'.$tasklist->highestItemModSeq(),
				'share-resource-uri' => '/ns/share/'.$uri,
				// 1 = owner, 2 = readonly, 3 = readwrite
				'share-access' => $level == Acl::LEVEL_MANAGE ? 1 : ($level >= Acl::LEVEL_WRITE ? 3 : 2),
				'{http://sabredav.org/ns}read-only' => $level < Acl::LEVEL_WRITE,
			];
		}

		return $result;
	}

	public function createCalendar($principalUri, $calendarUri, array $properties)
	{
		go()->debug("CalDAVBackend::createCalendar($principalUri, $calendarUri)");
		$sccs = '{urn:ietf:params:xml:ns:caldav}supported-calendar-component-set';
		$components = ['VEVENT'];
		if (isset($properties[$sccs])) {
			if (!($properties[$sccs] instanceof CalDAV\Xml\Property\SupportedCalendarComponentSet)) {
				throw new DAV\Exception('The '.$sccs.' property must be of type: \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet');
			}
			$components = $properties[$sccs]->getValue();
		}
		$transp = '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp';

		$ownerId = isset($properties[$transp]) && $properties[$transp]->getValue() === 'transparent' ? null : go()->getUserId();

		// Make it show up for the user that created it
		$values = ['ownerId' => $ownerId, 'name' => $calendarUri, 'isSubscribed' => true, 'syncToDevice' => true];
		foreach ($this->propertyMap as $xmlName => $dbName) {
			if (isset($properties[$xmlName])) {
				$values[$dbName] = $properties[$xmlName];
			}
		}
		if(isset($values['color'])) {
			$values['color'] = substr($values['color'], 1); // remove #
		}
		if(in_array('VEVENT', $components)) {
			// A collection for events and tasks becomes a calendar. GroupOffice keeps tasks in task lists.
			$prefix = 'c-';
			$cal = new Calendar();
		} else if(in_array('VTODO', $components)) {
			$prefix = 't-';
			unset($values['ownerId']);
			$values['createdBy'] = go()->getUserId();
			$cal = new TaskList();
		} else {
			throw new DAV\Exception\BadRequest('Only calendars with VEVENT or VTODO components are supported');
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
			$cal = match($type) {
				'c' => Calendar::findById($id),
				't' => TaskList::findById($id),
				default => null
			};
			if(!$cal) {
				return array_fill_keys(array_keys($mutations), 404);
			}

			$newValues = [];
			foreach ($mutations as $propertyName => $propertyValue) {
				switch ($propertyName) {
					case '{urn:ietf:params:xml:ns:caldav}schedule-calendar-transp':
						if($type == 'c')
							$newValues['includeInAvailability'] = 'transparent' === $propertyValue->getValue() ? 'none' : ($cal->isOwner() ? 'all' : 'attending');
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

			$cal->setValues($newValues);
			if(!$cal->save()) {
				// Sabre responds with 403 for every property
				go()->log("Could not update calendar $type-$id: " . $cal->getValidationErrorsAsString());
				return false;
			}

			return true;
		});
	}

	public function deleteCalendar($calendarId)
	{
		go()->debug("CalDAVBackend::deleteCalendar($calendarId)");
		list($type, $id) = explode('-', $calendarId,2);
		switch($type) {
			case 'c':

				$e = Calendar::findById($id, []);
				if(!$e) {
					throw new DAV\Exception\NotFound("Calendar not found");
				}

				// don't delete it but exclude it from sync
				$e->syncToDevice = 0;
				$e->save();
				break;
			case 't':
				$e = TaskList::findById($id, []);
				if(!$e) {
					throw new DAV\Exception\NotFound("Task list not found");
				}
				// don't delete it but exclude it from sync
				$e->syncToDevice = 0;
				$e->save();
				break;
		}
	}

	public function  getCalendarObjects($calendarId)
	{
		go()->debug("CalDAVBackend::getCalendarObjects($calendarId)");
		//id, uri, lastmodified, etag, calendarid, size, componenttype
		list($type, $id) = explode('-', $calendarId,2);

		$maxMonthsOld = isset(\GO::config()->caldav_max_months_old) ? abs(\GO::config()->caldav_max_months_old) : 6;
		$start = date('Y-m-d', strtotime('-'.$maxMonthsOld.' months'));
		$end = date('Y-m-d', strtotime('+3 years'));

		$result = [];

		switch($type) {
			case 'c': $component = 'vevent';
				$stmt = CalendarEvent::find()
					->filter(['hideSecret' => 1, 'inCalendars' => $id, 'before' => $end, 'after' => $start]);
				break;
			case 't' : $component = 'vtodo';
				$stmt =  Task::find()
					->filter(['tasklistId' => $id]);
				break;
			default: return $result;
		}
		foreach ($stmt as $object) {
			try {
				$result[] = $this->toCalendarObject($object, $calendarId, $object->uri(), $component, false);
			} catch (\Exception $e) {
				// event that cannot be converted will not go into the result array. Sync will continue, error is logged.
				ErrorHandler::logException($e);
			}
		}

		return $result;
	}

	/**
	 * Returns a list of calendar objects.
	 *
	 * This method should work identical to getCalendarObject, but instead
	 * return all the calendar objects in the list as an array.
	 *
	 * If the backend supports this, it may allow for some speed-ups.
	 *
	 * @param mixed $calendarId
	 */
	public function getMultipleCalendarObjects($calendarId, array $uris)
	{
		go()->debug("CalDAVBackend::getMultipleCalendarObjects($calendarId, 'uris')");

		list($type, $id) = explode('-', $calendarId,2);

		foreach (array_chunk($uris, 900) as $chunk) {

			switch ($type) {
				case 'c': // calendar
					$component = 'vevent';
					$query = CalendarEvent::find()->filter(['hideSecret' => 1, 'inCalendars' => $id])->where(['cce.uri' => $chunk]);
					break;
				case 't': // tasklist
					$component = 'vtodo';
					$query = Task::find()->filter(['tasklistId' => $id])->where(['task.uri' => $chunk]);
					break;
				default:
					go()->log("incorrect calendarId " . $calendarId);
					return null;
			}

			foreach ($query as $item) {
				$item = $this->toCalendarObject($item, $calendarId, $item->uri(), $component);
				if($item) yield $item;
			}
		}
	}

	/**
	 * Check if this is only called when the getCalendarObjects does not provide the calendardata
	 */
	public function getCalendarObject($calendarId, $objectUri)
	{
		go()->debug("CalDAVBackend::getCalendarObject($calendarId, $objectUri)");
		list($type, $id) = explode('-', $calendarId,2);

		switch($type) {
			case 'c': // calendar
				$component = 'vevent';
				$q = CalendarEvent::find()->filter(['hideSecret'=>1, 'inCalendars' => $id])->where(['cce.uri'=>$objectUri]);
				$object = $q->single();
				break;
			case 't': // tasklist
				$component = 'vtodo';
				$object = Task::find()->filter(['tasklistId' => $id])->where(['task.uri' => $objectUri])->single();
				break;
			default:
				go()->log("incorrect calendarId ".$calendarId. ' for '.$objectUri);
				return null;
		}

		if (!$object) {
			go()->log($component. " $objectUri not found in calendar $calendarId!");
			return null;
		}

		return $this->toCalendarObject($object, $calendarId, $objectUri, $component);
	}

	/**
	 * Find the event a client writes to the calendar by UID. When the event is already in another calendar, it's added
	 * to this calendar too, because GroupOffice stores an event once for all calendars it's in.
	 *
	 * @param VObject\Component\VCalendar $vcalendar
	 * @param int|string $calendarId
	 * @param bool $mayChange Set to false when the user may only change their own participation status
	 * @return CalendarEvent
	 * @throws DAV\Exception\Forbidden When the event exists, but the user can't read it
	 */
	private static function eventByVEvent(VObject\Component\VCalendar $vcalendar, int|string $calendarId, bool &$mayChange): CalendarEvent {
		$vevent = $vcalendar->select('VEVENT')[0];
		$uid = (string)$vevent->uid;

		$existingEvent = CalendarEvent::find()->where(['uid'=>$uid, 'calendarId' => $calendarId])->single();
		if($existingEvent){
			$mayChange = self::mayChange($existingEvent, $vcalendar);
			return $existingEvent;
		}

		$eventId = go()->getDbConnection()->selectSingleValue('eventId')
			->from('calendar_event')
			->where(['uid'=>$uid, 'recurrenceId' => null])->single();

		if(!empty($eventId)) {
			// Knowing the UID is not enough. The user must be able to read the event in one of its calendars and only
			// changes it when allowed there.
			$readable = CalendarEvent::find()
				->where(['cce.eventId' => $eventId])
				->filter(['permissionLevel' => Acl::LEVEL_READ])
				->all();
			if(empty($readable)) {
				throw new DAV\Exception\Forbidden("Event with UID $uid already exists");
			}
			$mayChange = false;
			foreach($readable as $instance) {
				if(self::mayChange($instance, $vcalendar)) {
					$mayChange = true;
					break;
				}
			}

			// add it to the current receivers default calendar
			$added = go()->getDbConnection()->insert('calendar_calendar_event', [
				['calendarId'=>$calendarId, 'eventId'=>$eventId, 'uri' => CalendarEvent::defaultUri($uid)]
			])->execute();
			if(!$added) {
				go()->debug('Could not add event to '.$calendarId);
			}
			$event = CalendarEvent::findById(go()->getDbConnection()->getPDO()->lastInsertId());
		} else {
			$mayChange = true;
			$event = new CalendarEvent();
			$organizerEmail = str_replace('mailto:', '',strtolower((string)$vevent->{'ORGANIZER'}));
			$event->isOrigin = !self::isExternalInvitation($vevent, $calendarId);
			$event->replyTo = $organizerEmail;
			$event->calendarId = $calendarId;
		}
		return $event;
	}

	/**
	 * Whether the client data may change the event itself and not only the user's participation status, alerts and
	 * free / busy status.
	 *
	 * An invitation from an organizer outside GroupOffice (isOrigin is false) changes only by iTip. When the user reads
	 * their mail in the CalDAV client, the client applies the organizer's update and sends it to us. The organizer
	 * raises the SEQUENCE for such an update, a change by the attendee doesn't.
	 *
	 * @param CalendarEvent $event
	 * @param VObject\Component\VCalendar $vcalendar
	 * @return bool
	 */
	private static function mayChange(CalendarEvent $event, VObject\Component\VCalendar $vcalendar): bool
	{
		if($event->mayChangeEventData()) {
			return true;
		}
		if($event->isOrigin || !$event->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			return false;
		}
		foreach($vcalendar->select('VEVENT') as $vevent) {
			if(!isset($vevent->{'RECURRENCE-ID'})) {
				return isset($vevent->SEQUENCE) && (int) $vevent->SEQUENCE->getValue() > $event->sequence;
			}
		}
		return false;
	}

	/**
	 * Whether a new event is an invitation from an organizer outside GroupOffice, for example when an attendee accepts
	 * an e-mail invitation in their CalDAV client. Such an event is not created by this system (isOrigin is false), so
	 * only the organizer can change it and their cancellation is processed.
	 *
	 * Only true when the organizer is not a local user or resource and the owner of the calendar is an attendee, so a
	 * user who organizes with an address that GroupOffice doesn't know keeps their event.
	 *
	 * @param VObject\Component $vevent
	 * @param int|string $calendarId
	 * @return bool
	 */
	private static function isExternalInvitation(VObject\Component $vevent, int|string $calendarId): bool
	{
		$organizer = $vevent->select('ORGANIZER');
		if(empty($organizer)) {
			return false;
		}
		$organizerEmail = strtolower(str_ireplace('mailto:', '', (string)$organizer[0]));
		if($organizerEmail === '' || !empty(Principal::findIdsByEmail($organizerEmail))) {
			return false;
		}

		// The same user as CalendarEvent::calendarParticipant()
		$ownerId = Calendar::findById($calendarId, ['ownerId'])?->getOwnerId() ?? go()->getUserId();
		$aliases = User::findEmailAliases($ownerId);
		foreach($vevent->select('ATTENDEE') as $attendee) {
			if(in_array(strtolower(str_ireplace('mailto:', '', (string)$attendee)), $aliases)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Take only what belongs to the user from the client data: the participation status of the calendar owner, the
	 * alerts and the free / busy status. Used when the user may not change the event itself, for example an attendee
	 * accepting an invitation.
	 *
	 * @param CalendarEvent $event
	 * @param VObject\Component\VCalendar $vcalendar
	 */
	private static function applyUserData(CalendarEvent $event, VObject\Component\VCalendar $vcalendar): void
	{
		$participant = $event->calendarParticipant();
		$valid = [Participant::NeedsAction, Participant::Tentative, Participant::Accepted, Participant::Declined];
		foreach($vcalendar->select('VEVENT') as $vevent) {
			if(isset($vevent->{'RECURRENCE-ID'})) {
				continue;
			}
			ICalendarHelper::parseAlerts($vevent, $event);
			if(isset($vevent->TRANSP)) {
				$event->freeBusyStatus = ICalendarHelper::parseFreeBusyStatus($vevent);
			}
			if(!$participant || !isset($vevent->ATTENDEE)) {
				continue;
			}
			foreach($vevent->ATTENDEE as $attendee) {
				$email = str_ireplace('mailto:', '', (string)$attendee);
				if(strcasecmp($email, $participant->email) !== 0 || empty($attendee['PARTSTAT'])) {
					continue;
				}
				$status = strtolower((string)$attendee['PARTSTAT']);
				if(in_array($status, $valid)) {
					$participant->participationStatus = $status;
				}
			}
		}
		// The participants are another table, so the ORM does not update modifiedAt. It's the etag.
		$event->modifiedAt = new DateTime('now', new \DateTimeZone('UTC'));
	}

	/**
	 * @param string $type "c" for a calendar or "t" for a task list
	 * @param int|string $id
	 * @param int $level
	 * @throws DAV\Exception\Forbidden
	 */
	private static function checkCollectionPermission(string $type, int|string $id, int $level): void
	{
		$collection = $type === 'c' ? Calendar::findById($id) : TaskList::findById($id);
		if(!$collection || !$collection->hasPermissionLevel($level)) {
			throw new DAV\Exception\Forbidden("Permission denied");
		}
	}

	public function createCalendarObject($calendarId, $objectUri, $calendarData)
	{
		list($type, $id) = explode('-', $calendarId,2);

		go()->debug("CalDAVBackend::createCalendarObject($calendarId, $objectUri, ");
		go()->debug($calendarData);
		go()->debug(")");

		$vCalendar = VObject\Reader::read($calendarData, VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);

		switch($type) {
			case 'c': // calendar
				self::checkCollectionPermission($type, $id, Acl::LEVEL_WRITE);
				$mayChange = true;
				$object = self::eventByVEvent($vCalendar, $id, $mayChange);
				if(!$mayChange) {
					self::applyUserData($object, $vCalendar);
					$object->uri($objectUri);
					break;
				}
				$object = ICalendarHelper::parseVObject($calendarData, $object);
				$object->uri($objectUri);
				$object->attachBlob(ICalendarHelper::makeBlob($object, $calendarData)->id);

				break;
			case 't': // tasklist
				self::checkCollectionPermission($type, $id, Acl::LEVEL_WRITE);
				$object = new Task();
				$object = (new VCalendar)->vtodoToTask($vCalendar, $id, $object);
				$object->setUri($objectUri);

				break;
			default:
				throw new DAV\Exception\NotFound("Calendar $calendarId not found");
		}
		if(!$object->save()) {
			throw new SaveException($object);
		}

		if(isset($mayChange) && !$mayChange) {
			$object->resetICalBlobsOfAllUsers();
			// The stored event differs from what the client sent. Without an etag the client fetches it again.
			return null;
		}

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
		$object = $type==='c' ? CalendarEvent::find()->where(['cce.uri'=>$objectUri, 'calendarId'=>$id])->single() :
			Task::find()->where(['task.tasklistId' => $id, 'task.uid' => $uid])->single();
		if(!$object){
			throw new DAV\Exception\NotFound("Object $objectUri not found in calendar $calendarId");
		}
		if(!$object->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			throw new DAV\Exception\Forbidden("Permission denied");
		}
		$mayChange = true;
		switch($type) {
			case 'c':
				$vCalendar = VObject\Reader::read($calendarData, VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
				if(!self::mayChange($object, $vCalendar)) {
					$mayChange = false;
					self::applyUserData($object, $vCalendar);
					break;
				}
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
			throw new SaveException($object);
		}

		if(!$mayChange) {
			$object->resetICalBlobsOfAllUsers();
			// The stored event differs from what the client sent. Without an etag the client fetches it again.
			return null;
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
					->where(['calendarId' => $id, 'cce.uri'=> $objectUri]);
				self::checkCollectionPermission($type, $id, Acl::LEVEL_DELETE);
				CalendarEvent::delete($query);
				break;
			case 't':
				$query->from('tasks_task','task')
					->where(['task.tasklistId' => $id, 'task.uid'=> $uid]);
				self::checkCollectionPermission($type, $id, Acl::LEVEL_DELETE);
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
	 * @param array $filters
	 * @param mixed $calendarId
	 *
	 * This specific implementation (for the PDO) backend optimizes filters on
	 *  specific components, and VEVENT time-ranges.
	 *
	 * @return array
	 *
	 */
	public function calendarQuery($calendarId, array $filters)
	{
		go()->debug("CalDAVBackend::calendarQuery($calendarId, 'filters')");
		list($type, $id) = explode('-', $calendarId,2);

		$componentType = null;
		$requireCalendarData = true;
		$timeRange = null;

		// if no filters were specified, we don't need to filter after a query
		if (!$filters['prop-filters'] && !$filters['comp-filters']) {
			$requireCalendarData = false;
		}

		// Figuring out if there's a component filter
		if (count($filters['comp-filters']) > 0 && !$filters['comp-filters'][0]['is-not-defined']) {
			$componentType = $filters['comp-filters'][0]['name'];

			// Checking if we need post-filters
			$has_time_range = array_key_exists('time-range', $filters['comp-filters'][0]) && $filters['comp-filters'][0]['time-range'];
			if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$has_time_range && !$filters['comp-filters'][0]['prop-filters']) {
				$requireCalendarData = false;
			}
			// There was a time-range filter
			if ('VEVENT' == $componentType && $has_time_range) {
				$timeRange = $filters['comp-filters'][0]['time-range'];

				// If start time OR the end time is not specified, we can do a
				// 100% accurate mysql query.
				if (!$filters['prop-filters'] && !$filters['comp-filters'][0]['comp-filters'] && !$filters['comp-filters'][0]['prop-filters'] && $timeRange) {
					if ((array_key_exists('start', $timeRange) && !$timeRange['start']) || (array_key_exists('end', $timeRange) && !$timeRange['end'])) {
						$requireCalendarData = false;
					}
				}
			}
		}

		if(($componentType === 'VTODO' && $type !== 't') || $componentType ==='VEVENT' && $type !== 'c'){
			return []; // there are no events in tasklists or tasks in calendars
		}

		switch($type) {
			case 'c':
				$stmt = CalendarEvent::find()->filter(['hideSecret' => 1, 'inCalendars' => $id]);
				if ($timeRange && array_key_exists('start', $timeRange) && $timeRange['start']) {
					$stmt->filter(['after' => $timeRange['start']->format('Y-m-d H:i:s')]);
				}
				if ($timeRange && array_key_exists('end', $timeRange) && $timeRange['end']) {
					$stmt->filter(['before' => $timeRange['end']->format('Y-m-d H:i:s')]);
				}
				break;
			case 't' :
				$stmt =  Task::find()->filter(['tasklistId' => $id]);
				break;
			default:
				return [];
		}

		if(!$requireCalendarData) {
			$stmt->select('uri');
		}

		$result = [];
		foreach ($stmt as $item) {
			if ($requireCalendarData) {
				try {
					$blob = $item->icsBlob();
					$data = $blob->getFile()->getContents();
				} catch (\Exception $e) {
					ErrorHandler::logException($e);
					continue;
				}
				if (!$this->validateFilterForObject(['calendardata'=>$data], $filters)) {
					continue;
				}
			}
			$result[] = $item->uri();
		}

		return $result;
	}

	/**
	 * @param mixed $object
	 * @param mixed $calendarId
	 * @param string $objectUri
	 * @param string $component
	 * @param bool $returnCalendarData To optimize performance data is not included in getCalendarObjects() so the client can determine if the full data is needed after fetching etags.
	 * @return array
	 */
	private function toCalendarObject(mixed $object, mixed $calendarId, string $objectUri, string $component, bool $returnCalendarData = true): array|null
	{
		if(empty($objectUri)) {
			ErrorHandler::log("Object URI is empty of ". $object->id ."  in calendar  ". $calendarId);
		}

//		go()->debug("CalDAVBackend::getCalendarObject($calendarId, $objectUri, ");
//		go()->debug($data);
//		go()->debug(")");


		$obj = [
			'id' => $object->id,
			'uri' => $objectUri,
			'calendarid' => $calendarId,
			'component' => $component,
		];

		if($returnCalendarData) {

			try {
				$blob = $object->icsBlob();
				$data = $blob->getFile()->getContents();
			} catch (\Exception $e) {
				ErrorHandler::logException($e);
				// log error and return null to prevent client errors
				return null;
			}

			$obj['size'] = $blob->size;
			$obj['calendardata'] = $data;
		}

		// set these after generating icsBlob() because lastModified may change.
		$lastModified = $object->modifiedAt->getTimestamp();
		$obj['lastmodified'] = $lastModified;
		$obj['etag'] = '"' . $lastModified . '"';

		return $obj;
	}
}