<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: CalendarsBackend.php 23072 2018-01-12 10:43:04Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

namespace GO\Caldav;
use GO\Calendar\Model\Event;
use go\core\fs\Blob;
use go\core\model\Acl;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use go\modules\community\tasks\convert\VCalendar;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\TaskList;
use GO\Calendar\Model\Calendar;
use Sabre;
use GO;
use GO\Caldav\Model\CalendarChange;
use GO\Caldav\Model\DavEvent;
use GO\Base\Db\FindParams;
use GO\Base\Db\FindCriteria;
use Sabre\DAV\Exception\Forbidden;
use Sabre\VObject\Reader;
use Sabre\DAV\Exception;

class CalendarsBackend extends Sabre\CalDAV\Backend\AbstractBackend
	implements
//		Sabre\CalDAV\Backend\SyncSupport,
	Sabre\CalDAV\Backend\SchedulingSupport
{
	private $_cachedCalendars;

	private function fromBlob(Task $task) {
		$blob = $task->vcalendarBlobId ? Blob::findById($task->vcalendarBlobId) : null;
		if(!$blob || $blob->modifiedAt < $task->modifiedAt) {

			//Important to set exactly the same modifiedAt on both blob and contact.
			//We compare these to check if vcards need to be updated.
			//utc timezone is required because we're in old framework using user defined timezone as default.
			$task->modifiedAt = new DateTime('now', new \DateTimeZone('utc'));
			// task to vtodo
			$parser = new VCalendar();
			$data = $parser->export($task)->serialize();

			$blob = Blob::fromString($data);
			$blob->type = 'text/vcalendar';
			$blob->name = $task->getUri();
			$blob->modifiedAt = $task->modifiedAt;
			if(!$blob->save()) {
				throw new \Exception("could not save VCalendar blob for task '" . $task->id() . "'. Validation error: " . $blob->getValidationErrorsAsString());
			}

			$task->vcalendarBlobId = $blob->id;
			if(!$task->save()) {
				throw new SaveException($task);
			}
		} else {
			$data = $blob->getFile()->getContents();
		}
		return $data;
	}



	/**
	 * Get the user model by principal URI
	 *
	 * @param string $principalUri
	 * @return \GO\Base\Model\User
	 */
	private function _getUser($principalUri) {

		$username = basename($principalUri);

		return \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
	}

	/**
	 * Returns a list of calendars for a principal
	 *
	 * @param string $userUri
	 * @return array
	 */
	public function getCalendarsForUser($principalUri) {


		\GO::debug("c:getCalendarsForUser($principalUri)");

		if(!isset($this->_cachedCalendars[$principalUri])){

			$user = $this->_getUser($principalUri);

			$findParams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl()
				->joinModel(array(
					'model' => 'GO\Sync\Model\UserCalendar',
					'localTableAlias' => 't', //defaults to "t"
					'localField' => 'id', //defaults to "id"
					'foreignField' => 'calendar_id', //defaults to primary key of the remote model
					'tableAlias' => 'l', //Optional table alias
				))
				->criteria(FindCriteria::newInstance()->addCondition('user_id', $user->id, '=', 'l'));

			$stmt = Calendar::model()->find($findParams);

			if(!$stmt->rowCount()){
				//If the sync settings dialog for this user is never opened no default settings are created
				\GO\Sync\Model\Settings::model()->findForUser($user); //create default settings
				$stmt = Calendar::model()->find($findParams);
			}

			$this->_cachedCalendars[$principalUri] = array();
			while ($calendar = $stmt->fetch()) {
				$this->_cachedCalendars[$principalUri][] = $this->_modelToDAVCalendar($calendar, $principalUri);
			}
		}

		return $this->_cachedCalendars[$principalUri];
	}

	public function getCalendar($principalUri, $calendarUri){
		\GO::debug("c:getCalendar($principalUri, $calendarUri)");

		preg_match('/-([0-9]+)$/', $calendarUri, $matches);
		$id=$matches[1];

		$calendar = Calendar::model()->findByPk($id);

		if(!$calendar)
			throw new Exception\NotFound('File not found: ' . $calendarUri);

		if(!$calendar->checkPermissionLevel(\GO\Base\Model\Acl::READ_PERMISSION))
			throw new Exception\Forbidden ('Access denied for '.$calendarUri);

		return $this->_modelToDAVCalendar($calendar, $principalUri);

	}


	private function _modelToDAVCalendar(Calendar $calendar, $principalUri){

		$supportedComponents = array('VEVENT');
		$version = $calendar->version;
		if($calendar->tasklist_id > 0) {
			$supportedComponents[] = 'VTODO';

			$version .= "-" . Task::getState();
		}

//		\GO::debug("Version: ".$version);

		$tz = new \GO\Base\VObject\VTimezone();
		//$owner = User::findById($calendar->user_id, ['username']);
		$level = $calendar->getPermissionLevel();

		return array(
			'id' => $calendar->id,
			'uri' => $calendar->getUri(),
			'principaluri' => $principalUri,
			//'principaluri' => 'principals/'.$owner->username,
			'{'.Sabre\CalDAV\Plugin::NS_CALENDARSERVER.'}getctag' => 'GroupOffice/calendar/'.$version,
//			'{http://sabredav.org/ns}sync-token' => $version,
			'{'.Sabre\CalDAV\Plugin::NS_CALDAV.'}supported-calendar-component-set' => new Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet($supportedComponents),
			'{'.Sabre\CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp' => new Sabre\CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
			'{DAV:}displayname' => $calendar->name,
			'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'User calendar',
			'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\r\n" . $tz->serialize() . "END:VCALENDAR",
			// To prevent sync errors. Calendars with READ+CREATE will show as read only. DAV does not have read+create.
			// therefor user could write existing events and will have an error on sync and the change is later reverted.
			// Also, the create-only permission would have to be checked manually is made available
			'{http://sabredav.org/ns}read-only' => $level <= Acl::LEVEL_CREATE,
			// 1 = owner, 2 = readonly, 3 = readwrite
			'share-access' => $level == Acl::LEVEL_MANAGE ? 1 : ($level >= Acl::LEVEL_WRITE ? 3 : 2),
			//'access'=> $calendar->getPermissionLevel() < Acl::LEVEL_CREATE ? \Sabre\DAV\Sharing\Plugin::ACCESS_READ : \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE
//			'{http://apple.com/ns/ical/}calendar-order' => $calendar->id,
//			'{http://apple.com/ns/ical/}calendar-color' => '#'.$this->nextBackgroundColor()
		);
	}

	/**
	 * Creates a new calendar for a principal.
	 *
	 * If the creation was a success, an id must be returned that can be used to reference
	 * this calendar in other methods, such as updateCalendar
	 *
	 * @param string $principalUri
	 * @param string $calendarUri
	 * @param array $properties
	 * @return mixed
	 */
	public function createCalendar($principalUri, $calendarUri, array $properties) {
		throw new Sabre\DAV\Exception\Forbidden("Create calendar is not supported");
	}

	/**
	 * Updates a calendars properties
	 *
	 * The properties array uses the propertyName in clark-notation as key,
	 * and the array value for the property value. In the case a property
	 * should be deleted, the property value will be null.
	 *
	 * This method must be atomic. If one property cannot be changed, the
	 * entire operation must fail.
	 *
	 * If the operation was successful, true can be returned.
	 * If the operation failed, false can be returned.
	 *
	 * Deletion of a non-existant property is always succesful.
	 *
	 * Lastly, it is optional to return detailed information about any
	 * failures. In this case an array should be returned with the following
	 * structure:
	 *
	 * array(
	 *   403 => array(
	 *      '{DAV:}displayname' => null,
	 *   ),
	 *   424 => array(
	 *      '{DAV:}owner' => null,
	 *   )
	 * )
	 *
	 * In this example it was forbidden to update {DAV:}displayname.
	 * (403 Forbidden), which in turn also caused {DAV:}owner to fail
	 * (424 Failed Dependency) because the request needs to be atomic.
	 *
	 * @param string $calendarId
	 * @param \Sabre\DAV\PropPatch $properties
	 * @return bool|array
	 */
	public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $properties) {

		\GO::debug("updateCalendar($calendarId,[data])");

		// we don't really support this but if we don't act like we do then apple calendar will show an error.
		$supported = [
			'{DAV:}displayname',
			'{urn:ietf:params:xml:ns:caldav}calendar-description',
			'{urn:ietf:params:xml:ns:caldav}calendar-timezone',
			'{http://apple.com/ns/ical/}calendar-order',
			'{http://apple.com/ns/ical/}calendar-color'
		];

		$properties->handle($supported, function($mutations) {
			return true;
		});
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param string $calendarId
	 * @return void
	 */
	public function deleteCalendar($calendarId) {
		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
	 * Get the event by DAV client URI
	 *
	 * @param string $uri
	 * @param string $calendarId
	 * @return \GO\Calendar\Model\Event
	 */
	private function getEventByUri($uri, $calendarId){

		$joinCriteria = FindCriteria::newInstance()
			->addRawCondition('t.id', 'd.id');

		$whereCriteria = FindCriteria::newInstance()
			->addModel(DavEvent::model(),'d')
			->addCondition('calendar_id', $calendarId)
			->addCondition('uri', $uri,'=','d');

		$findParams = FindParams::newInstance()
			->single()
			->join(DavEvent::model()->tableName(),$joinCriteria, 'd')
			->criteria($whereCriteria);

		return \GO\Calendar\Model\Event::model()->find($findParams);
	}


	/**
	 * Get the event by DAV client URI
	 *
	 * @param string $uri
	 * @return Task
	 */
	private function getTaskByUri($uri, $calendarId){
		$uid = str_replace('.ics', '',$uri);
		return Task::find()
			->join('cal_calendars', 'c','c.tasklist_id = task.tasklistId')
			->where(['c.id' => $calendarId, 'task.uri' => $uri])
			->single();
	}



	/**
	 * Returns all calendar objects within a calendar object.
	 * Will create the DavEvent record when they do not exist and update them when the mtime differs
	 *
	 * @param string $calendarId
	 * @return array
	 */
	public function getCalendarObjects($calendarId)
	{
		//weird bug?
		if(!\GO::user()) {
			throw new Exception\NotAuthenticated();
		}

		//Get the calendar object and check if the user has delete permission.
		$calendar = Calendar::model()->findByPk($calendarId, false, true);

		\GO::config()->caldav_max_months_old=isset(\GO::config()->caldav_max_months_old) ? \GO::config()->caldav_max_months_old : 6;
		\GO::config()->caldav_max_months_old=\GO::config()->caldav_max_months_old*-1;


		$objects = array();

		$whereCriteria = FindCriteria::newInstance()
			->addModel(\GO\Calendar\Model\Event::model())
			->addCondition('exception_for_event_id', 1, '<')
			->addCondition('calendar_id', $calendarId);

		$findParams = FindParams::newInstance()
			->ignoreAcl()
			->criteria($whereCriteria);

		$stmt = \GO\Calendar\Model\Event::model()->findForPeriod(
			$findParams,
			\GO\Base\Util\Date::date_add(time(), 0, GO::config()->caldav_max_months_old),
			\GO\Base\Util\Date::date_add(time(),0,0,3)
		); //Outlook crashes on dates far in the future.

		\GO::debug("Found ".$stmt->rowCount()." events");

		while ($event = $stmt->fetch()) {


			// Check if all occurences of this rrule are removed with an exception.
			// If so, then continue to the next event. (Sabredav cannot handle rrules where all possible dates are removed by exceptions)
			if(!empty($event->rrule)) {
				try{
					$vobject = $event->toVObject();
					$it = new \Sabre\VObject\Recur\EventIterator($vobject);
				}catch(\Exception $e) {
					\GO::debug("Invalid rrule event ID: ".$event->id.' : '.$event->rrule.' '.$e->getMessage());
					\GO::debug("All possible RRULE dates are removed by an RRULE Exception event so there is no event to display");
					continue;
				}
			}

			//if(!$event->isPrivate()){

				$davEvent = DavEvent::model()->findByPk($event->id);
				if(!$davEvent || $davEvent->mtime != $event->mtime){
					$davEvent = CaldavModule::saveEvent($event, $davEvent);
				}
				$objects[] = array(
					'uri' => $davEvent->uri,
//					'calendardata' => $davEvent->data,
					'calendarid' => $calendarId, //if data is not present then calendarid must be there. See AbstractBackend line 133
					'lastmodified' => $event->mtime,
					'etag'=> $event->getEtag(),
					'size' => strlen($davEvent->data),
					'component' => 'vevent'
				);

			//}
		}


		if ($calendar->tasklist_id > 0) {
			$tasklist = TaskList::findById($calendar->tasklist_id); // ignore acl?

			if($tasklist) {
				$tasks = Task::find()->filter(['tasklistId' => $calendar->tasklist_id])
					//->andWhere('due > (NOW() - INTERVAL '.$monthsOld.' MONTH)') // DUE can be NULL
					->all();

				\GO::debug("Found ".count($tasks)." tasks");

				foreach ($tasks as $task) {
					$data = $this->fromBlob($task);
					$objects[] = array(
						'uri' => $task->getUri(),
						'calendardata' => $data,
						'lastmodified' => $task->modifiedAt->getTimestamp(),
						'etag' => $task->etag(),
						'size' => strlen($data),
						'component' => 'vtodo'
					);
				}
			}
		}

		return $objects;
	}

	/**
	 * Get's an array with free busy info for a given time period.
	 *
	 * @param String $email
	 * @param int $start Unix time stamp of the time period
	 * @param int $end Unix time stamp of the time period
	 * @return Array Free busy information
	 */
	public function getFreeBusy($email, $start, $end){

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/users.class.inc.php');
		$core_user = new core_user();

		\GO::debug("getFreeBusy($email, $start, $end)");

		$user = $core_user->get_user_by_email($email);

		if(!$user) {
			return false;
		}

		if(!empty($GLOBALS['GO_CONFIG']->require_calendar_access_for_freebusy)){
			//Only show availability if user has access to the default calendar
			$default_calendar = $this->cal->get_default_calendar($user['id']);

			if(!$GLOBALS['GO_SECURITY']->has_permission($GLOBALS['GO_SECURITY']->user_id, $default_calendar['acl_id']))
				return false;
		}

		$events = $this->cal->get_events_in_array(0, $user['id'], $start, $end,true);

		$fb=array();

		while ($event = array_shift($events)) {
			$fb[]=array('start'=>$event['start_time'],'end'=>$event['end_time'], 'busyType'=>'BUSY');
		}
		return $fb;
	}

	/**
	 * Returns information from a single calendar object, based on it's object uri.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @return array
	 */
	public function getCalendarObject($calendarId, $objectUri) {

//		\GO::debug("c:getCalendarObject($calendarId,$objectUri)");

		/*
		 * When a client adds or updates an event, the server must return the
		 * data identical to what the client sent. That's why we store the
		 * client data in a separate table and if the mtime's match we use that.
		 */

		//select on calendar id is necessary because somehow thunderbird tries
		//to get an event that's an invitation. It must not return the organizers event.

		$whereCriteria = FindCriteria::newInstance()
			->addModel(\GO\Calendar\Model\Event::model())
			->addModel(DavEvent::model(),'d')
			->addCondition('calendar_id', $calendarId)
			->addCondition('uri', $objectUri,'=','d');

		$joinCriteria = FindCriteria::newInstance()
			->addRawCondition('t.id', 'd.id');

		$findParams = FindParams::newInstance()
			//->single()
			->limit(1)
			->ignoreAcl()
			->select("t.*, d.uri, d.mtime AS client_mtime, d.data")
			->criteria($whereCriteria)
			->join(DavEvent::model()->tableName(), $joinCriteria,'d', 'LEFT');

		$event = \GO\Calendar\Model\Event::model()->find($findParams)->fetch();

		if ($event) {

			$data = ($event->mtime==$event->client_mtime && !empty($event->data) && !$event->isPrivate()) ? $event->data : CaldavModule::exportCalendarEvent($event);


			$object = array(
				'uri' => $event->uri,
				'calendardata' => $data,
				'lastmodified' => $event->mtime,
				'etag'=> $event->getEtag(),
				'size' => strlen($data),
				'component' => 'vevent'
			);
			\GO::debug($object);
			return $object;
		}
		else {
			$task = $this->getTaskByUri($objectUri, $calendarId);

			if ($task) {
//				\GO::debug('Found task '.$objectUri);
				$data = $this->fromBlob($task);

				$object = array(
					'uri' => $task->getUri(),
					'calendardata' => $data,
					'lastmodified' => $task->modifiedAt->getTimestamp(),
					'etag' => $task->etag(),
					'size' => strlen($data),
					'component' => 'vtodo'
				);

				return $object;
			}
		}
		throw new Sabre\DAV\Exception\NotFound('File not found '.$objectUri);
	}

	/**
	 * Creates a new calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri NOTE: uri can be the same when importing the same ics file into multiple calendars
	 * @param string $calendarData
	 * @return void
	 */
	public function createCalendarObject($calendarId, $objectUri, $calendarData) {

		\GO::debug("createCalendarObject($calendarId,$objectUri,[data)");
		\GO::debug($calendarData);

		try{


			$vcalendar = \GO\Base\VObject\Reader::read($calendarData);

			\GO::debug((string) $vcalendar->uid);
			$file = new \GO\Base\Fs\File($objectUri);

			if(strpos($calendarData, 'VEVENT')!==false){

				\GO::debug('item is an event');

				$event=false;
				foreach($vcalendar->vevent as $vevent) {
					$uuid = (string) $vevent->uid;

					$recurrenceDate=false;
					$recurrence = $vevent->select('recurrence-id');
					if(count($recurrence)){
						$firstMatch = array_shift($recurrence);
						$recurrenceDate=intval($firstMatch->getDateTime()->format('U'));
					}

					//Lookup existing events. TB may create an event by e-mail invitation that is not yet synced to TB.
					$event = \GO\Calendar\Model\Event::model()->findByUuid($uuid, 0, $calendarId, $recurrenceDate);

					if(!$event)
						$event = new \GO\Calendar\Model\Event();

					$event->setUri($objectUri);
					$event->importVObject($vevent, array('calendar_id' => $calendarId, 'uuid' => $uuid));

					if(!$recurrenceDate) {
						CaldavModule::saveEvent($event, false, $vcalendar->serialize());
					}
				}

				if (!$event) {
					return false;
				}else {
					return $event->getETag();
				}
			} else { // VTODO
				$calendar = Calendar::model()->findByPk($calendarId);
				$parser = new VCalendar();
				$task = $parser->vtodoToTask($vcalendar, $calendar->tasklist_id);
				$task->setUri($objectUri);
				if($task->save()){
					return $task->etag();
				} else {
					throw new \Exception(var_export($task->getValidationErrors(),true));
				}
			}
		}catch(\GO\Base\Exception\AccessDenied $e){

			go()->debug("Access denied in caldav create");

			// when inviting local principal. DAV tries local delivery
			// which only succeeds if the user may write in the invite's calendar
//			return false;
			throw new Sabre\DAV\Exception\Forbidden;
		}catch (\Exception $e) {
			\go\core\ErrorHandler::logException($e);
		}
		return false;
	}



	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 *
	 * BEGIN:VCALENDAR
	CALSCALE:GREGORIAN
	PRODID:-//Apple Inc.//iOS 12.3.1//EN
	VERSION:2.0
	BEGIN:VTODO
	COMPLETED:20220523T083538Z
	CREATED:20220523T082243Z
	DESCRIPTION:
	DTSTAMP:20220523T083539Z
	LAST-MODIFIED:20220523T083538Z
	LOCATION:
	PERCENT-COMPLETE:100
	STATUS:COMPLETED
	SUMMARY:test GO
	UID:7614441d-b30f-4ce9-b18c-b84ccc52bbfc
	END:VTODO
	END:VCALENDAR


	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @param string $calendarData
	 * @return string|null
	 */
	public function updateCalendarObject($calendarId, $objectUri, $calendarData) {

		\GO::debug("updateCalendarObject($calendarId,$objectUri,[data])");

		try{

			if(strpos($calendarData,'VEVENT')!==false){

				\GO::debug('item is an event');

				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);

				$event = $this->getEventByUri($objectUri, $calendarId);
				if(!$event){
					\GO::debug("Event $objectUri not found in calendar $calendarId!");
					return false;
				}

				$exceptionVEvents=array();
				$VEvent=false;

				if($vcalendar->vevent->count()>1) {
					\GO::debug("Object has multiple VEVENT objects");
					//recurrence
					foreach($vcalendar->vevent as $e){
						if((string) $e->rrule!=''){
							$VEvent=$e;
						}else {
							$exceptionVEvents[]=$e;
						}
					}

				}else {
					\GO::debug("Object is a sinlge VEVENT object");
					$VEvent=$vcalendar->vevent[0];
				}


				if (!$VEvent){
					\GO::debug("Can't find VEVENT in data");
					return false;
				}

				$event->exceptions()->callOnEach('delete');
				$event->exceptionEvents()->callOnEach('delete');

				$event->importVObject($VEvent);

				$davEvent = DavEvent::model()->findByPk($event->id);

				CaldavModule::saveEvent($event, $davEvent, $vcalendar->serialize());

				if(count($exceptionVEvents)) {

					foreach($exceptionVEvents as $exceptionVEvent){

						$recurrenceDate=false;
						$recurrence = $exceptionVEvent->select('recurrence-id');
						//var_dump($recurrence);exit();
						if(count($recurrence)){
							$firstMatch = array_shift($recurrence);
							$recurrenceDate=intval($firstMatch->getDateTime()->format('U'));
						}

						if(!$recurrenceDate)
							return false;


						$existing = Event::model()->findByUuid($exceptionVEvent->uid, 0, $calendarId, $recurrenceDate);

						if(!$existing) {
							\GO::debug("Creating exception for date: ".$recurrenceDate);
							$exceptionEvent= $event->createExceptionEvent($recurrenceDate, array(), true);
							$exceptionEvent->importVObject($exceptionVEvent);
						} else {
							\GO::debug("Exception already exists for date: ".$recurrenceDate);
						}

					}
				}

				return $event->getETag();

			} else {
				$calendar = Calendar::model()->findByPk($calendarId);
				\GO::debug('item is a task');

				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);
				$task = $this->getTaskByUri($objectUri, $calendarId);


				$parser = new VCalendar();
				$task = $parser->vtodoToTask($vcalendar, $calendar->tasklist_id, $task);
				if($task->save()){
					return $task->etag();
				}
			}

		}catch(\GO\Base\Exception\AccessDenied $e){
			throw new Exception\Forbidden;
		} catch(\GO\Base\Exception\Validation $e){
			GO::debug($e->getMessage());
			throw new Exception\Forbidden('Validation errors: '.$e->getMessage());
		}	catch(\Exception $e){
			\go\core\ErrorHandler::logException($e);
			return false;
		}
	}

	/**
	 * Deletes an existing calendar object.
	 *
	 * @param string $calendarId
	 * @param string $objectUri
	 * @return void
	 */
	public function deleteCalendarObject($calendarId, $objectUri) {
		\GO::debug("deleteCalendarObject($calendarId,$objectUri)");

		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			go()->debug("Deleting is disabled by user sync settings");
			throw new Forbidden("Deleting is disabled by user sync settings");
		}

		try{
			$event = $this->getEventByUri($objectUri, $calendarId);
			if($event){
				$event->delete(); // will delete the DavEvent with an event
			}else{
				$task = $this->getTaskByUri($objectUri, $calendarId);
				if($task) {
					Task::delete($task->primaryKeyValues());
				}
			}
		}catch(\GO\Base\Exception\AccessDenied $e){
			throw new Sabre\DAV\Exception\Forbidden;
		}
	}

	/**
	 * The getChanges method returns all the changes that have happened, since
	 * the specified syncToken in the specified calendar.
	 *
	 * This function should return an array, such as the following:
	 *
	 * [
	 *   'syncToken' => 'The current synctoken',
	 *   'added'   => [
	 *      'new.txt',
	 *   ],
	 *   'modified'   => [
	 *      'modified.txt',
	 *   ],
	 *   'deleted' => [
	 *      'foo.php.bak',
	 *      'old.txt'
	 *   ]
	 * ];
	 * @param string $calendarId
	 * @param string $syncToken
	 * @param int $syncLevel
	 * @param int $limit
	 * @return array
	 */
	public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {
//		\GO::debug("getChangesForCalendar($calendarId,$syncToken)");
		// Current synctoken
		$calendar = GO\Calendar\Model\Calendar::model()->findByPk($calendarId);

		$currentToken = $calendar->version;

		if($calendar->tasklist_id) {
			$tasklist = TaskList::findById($calendar->tasklist_id);

			if($tasklist) {
				$currentToken += $tasklist->version;
			}
		}

		if (is_null($currentToken))
			return null;
		$result = [
			'syncToken' => $currentToken,
			'added' => [],
			'modified' => [],
			'deleted' => [],
		];
		if ($syncToken) {
			$findParams = FindParams::newInstance()
				->select('uri, operation')
				->criteria(FindCriteria::newInstance()
					->addCondition('synctoken', $syncToken, '>=')
					->addCondition('synctoken', $currentToken, '<')
					->addCondition('calendarid', $calendarId, '>=')
				)
				->order('synctoken');
			if ($limit > 0) {
				$findParams->limit((int) $limit);
			}
			$stmt = CalendarChange::model()->find($findParams);
			$changes = [];
			// This loop ensures that any duplicates are overwritten, only the
			// last change on a node is relevant.
			while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				$changes[$row['uri']] = $row['operation'];
			}
			foreach ($changes as $uri => $operation) {
				switch ($operation) {
					case 1 :
						$result['added'][] = $uri;
						break;
					case 2 :
						$result['modified'][] = $uri;
						break;
					case 3 :
						$result['deleted'][] = $uri;
						break;
				}
			}
		} else {
			// No synctoken supplied, this is the initial sync.

			$joinCriteria = FindCriteria::newInstance()
				->addRawCondition('t.id', 'e.id');
			$findParams = FindParams::newInstance()
				->select('uri')
				->join(GO\Calendar\Model\Event::model()->tableName(), $joinCriteria, 'e')
				->criteria(
					FindCriteria::newInstance()->addCondition('calendar_id', $calendarId, '=', 'e')
				);
			$stmt = DavEvent::model()->find($findParams);

			$result['added'] = $stmt->fetchAll(\PDO::FETCH_COLUMN);

			if($calendar->tasklist_id>0)
			{
				$result['added'] = array_merge($result['added'],  Task::find(['uri'])->filter(['tasklistId' => $calendar->tasklist_id])->execute()->fetchColumn());
			}
		}
		return $result;
	}

	public function createSchedulingObject($principalUri, $objectUri, $objectData) {
		return null;
	}

	public function deleteSchedulingObject($principalUri, $objectUri) {
		return null;
	}

	public function getSchedulingObject($principalUri, $objectUri) {
		return null;
	}

	public function getSchedulingObjects($principalUri) {
		return array();
	}

}
