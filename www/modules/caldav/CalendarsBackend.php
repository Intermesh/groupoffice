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
use go\core\fs\Blob;
use go\modules\community\tasks\convert\VCalendar;
use go\modules\community\tasks\model\Task;
use go\modules\community\tasks\model\Tasklist;
use GO\Calendar\Model\Calendar;
use Sabre;
use GO;
use GO\Caldav\Model\CalendarChange;
use GO\Caldav\Model\DavEvent;
use GO\Caldav\Model\DavTask;
use GO\Base\Db\FindParams;
use GO\Base\Db\FindCriteria;
use Sabre\VObject\Reader;
use Sabre\DAV\Exception;

class CalendarsBackend extends Sabre\CalDAV\Backend\AbstractBackend
	implements
//		Sabre\CalDAV\Backend\SyncSupport
	Sabre\CalDAV\Backend\SchedulingSupport
{
	private $_cachedCalendars;

	private $background_colors = array('F0AE67','FFCC00','FFFF00','CCFF00','66FF00',
		'00FFCC','00CCFF','0066FF','95C5D3','6704FB',
		'CC00FF','FF00CC','CC99FF','FB0404','FF6600',
		'C43B3B','996600','66FF99','999999','00FFFF');

	private $colorIndex=-1;

	private function nextBackgroundColor(){
		$this->colorIndex++;
		if(!isset($this->background_colors[$this->colorIndex])){
			$this->colorIndex=0;
		}
		return $this->background_colors[$this->colorIndex];
	}

	/**
	 * Get the user model by principal URI
	 *
	 * @param StringHelper $principalUri
	 * @return \GO\Base\Model\User
	 */
	private function _getUser($principalUri) {

		$username = basename($principalUri);

		return \GO\Base\Model\User::model()->findSingleByAttribute('username', $username);
	}

	/**
	 * Returns a list of calendars for a principal
	 *
	 * @param StringHelper $userUri
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

		\GO::debug($this->_cachedCalendars[$principalUri]);

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

		// BEFORE DAV SYNC
//		$db = new db();
//		$db->query("SELECT max(mtime) AS mtime, COUNT(*) AS count FROM cal_events WHERE calendar_id=?", 'i', $gocal['id']);
//		$r = $db->next_record();

		$findParams = FindParams::newInstance()
			->select('version')
			->single()
			->criteria(FindCriteria::newInstance()
				//->addModel(\GO\Calendar\Model\Event::model())
				->addCondition('id', $calendar->id));

		$r = Calendar::model()->find($findParams);

		$supportedComponents = array('VEVENT');
//		$ctagCount = $r->count;
//		$ctagMtime = $r->mtime;
		$version = $calendar->version;
		if($calendar->tasklist_id>0) {
			$supportedComponents[]='VTODO';

//			$findParams = FindParams::newInstance()
//				->select('version')
//				->single()
//				->criteria(FindCriteria::newInstance()->addCondition('id', $calendar->tasklist_id));
//			$t = \GO\Tasks\Model\Tasklist::model()->find($findParams);
			$tasklist = Tasklist::find()->select('version')->where(['id'=>$calendar->tasklist_id])->single();
			if($tasklist) {
				$version += $tasklist->version;
			}
//			$ctagCount += $t->count;
//			$ctagMtime = max(array($ctagMtime, $t->mtime));

		}

//		if (isset(GO::config()->caldav_max_months_old))
//			$ctag = $ctagCount . ':' . $ctagMtime . ':' . GO::config()->caldav_max_months_old;
//		else
//			$ctag = $ctagCount . ':' . $ctagMtime;
//		$ctag = $version;
//		if(\GO::config()->debug)
//			$ctag=time();

		\GO::debug("Version: ".$version);

		$tz = new \GO\Base\VObject\VTimezone();

		return array(
			'id' => $calendar->id,
			'uri' => $calendar->getUri(),
			'principaluri' => $principalUri,
			'{'.Sabre\CalDAV\Plugin::NS_CALENDARSERVER.'}getctag' => 'GroupOffice/calendar/'.$version,
//			'{http://sabredav.org/ns}sync-token' => $version,
			'{'.Sabre\CalDAV\Plugin::NS_CALDAV.'}supported-calendar-component-set' => new Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet($supportedComponents),
			'{'.Sabre\CalDAV\Plugin::NS_CALDAV.'}schedule-calendar-transp' => new Sabre\CalDAV\Xml\Property\ScheduleCalendarTransp('opaque'),
			'{DAV:}displayname' => $calendar->name,
			'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'User calendar',
			'{urn:ietf:params:xml:ns:caldav}calendar-timezone' => "BEGIN:VCALENDAR\r\n" . $tz->serialize() . "END:VCALENDAR",
//			'read-only' => false,
//			'access'=> \Sabre\DAV\Sharing\Plugin::ACCESS_READWRITE
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
	 * @param StringHelper $principalUri
	 * @param StringHelper $calendarUri
	 * @param array $properties
	 * @return mixed
	 */
	public function createCalendar($principalUri, $calendarUri, array $properties) {
		throw new Sabre\DAV\Exception\Forbidden();
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
	 * @param StringHelper $calendarId
	 * @param \Sabre\DAV\PropPatch $properties
	 * @return bool|array
	 */
	public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $properties) {

//		\GO::debug($properties);

//		throw new Sabre\DAV\Exception\Forbidden();

		return true;
	}

	/**
	 * Delete a calendar and all it's objects
	 *
	 * @param StringHelper $calendarId
	 * @return void
	 */
	public function deleteCalendar($calendarId) {
		throw new Sabre\DAV\Exception\Forbidden();
	}

	/**
	 * Get the event by DAV client URI
	 *
	 * @param StringHelper $uri
	 * @param StringHelper $calendarId
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
	 * @param StringHelper $uri
	 * @return \GO\Calendar\Model\Event
	 */
	private function getTaskByUri($uri, $calendarId){
		//$this->db->query("SELECT e.* FROM cal_events e INNER JOIN dav_events d ON d.id=e.id WHERE d.uri=?","s",$uri);

		$joinCriteria = FindCriteria::newInstance()
			->addRawCondition('t.id', 'd.id');

		$whereCriteria = FindCriteria::newInstance()
			->addModel(DavTask::model(),'d')
			->addCondition('tasklist_id', $calendarId)
			->addCondition('uri', $uri,'=','d');

		$findParams = FindParams::newInstance()
			->single()
			->join(DavTask::model()->tableName(),$joinCriteria, 'd')
			->criteria($whereCriteria);

		return \GO\Tasks\Model\Task::model()->find($findParams);
	}


	private function exportCalendarEvent($event){

		$events=array();
		if(empty($event->rrule)){
			$events[]=$event;
		}else{
			//a recurring event must be sent with all it's exceptions in the same data

			$fp = FindParams::newInstance()
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

//				if((string) $e->rrule==""){
				if($e->private && $e->calendar->user_id != \GO::user()->id){
					$e->name=\GO::t("Private", "calendar");
					$e->location='';
					$e->description='';
				}
				$e->sequence=$sequence;
				$sequence++;
				$events[]=$e;

//				}


			}
		}

		$c = new \GO\Base\VObject\VCalendar();
		$c->add(new \GO\Base\VObject\VTimezone());
		foreach($events as $event){
//			\GO::debug(date('c',$event->start_time).' '.$event->rrule);
			$c->add($event->toVObject('REQUEST', false));
		}

		return $c->serialize();
	}


	/**
	 * Returns all calendar objects within a calendar object.
	 * Will create the DavEvent record when they do not exist and update them when the mtime differs
	 *
	 * @param StringHelper $calendarId
	 * @return array
	 */
	public function getCalendarObjects($calendarId) {
		\GO::debug("c:getCalendarObjects($calendarId)");

		//weird bug?
		if(!\GO::user()) {
			throw new Exception\NotAuthenticated();
		}

		//Get the calendar object and check if the user has delete permission.
		$calendar = Calendar::model()->findByPk($calendarId, false, true);
//		if(!$calendar->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION))
//			throw new Sabre\DAV\Exception\Forbidden();

		\GO::config()->caldav_max_months_old=isset(\GO::config()->caldav_max_months_old) ? \GO::config()->caldav_max_months_old : 6;
		\GO::config()->caldav_max_months_old=\GO::config()->caldav_max_months_old*-1;


		$objects = array();

		$whereCriteria = FindCriteria::newInstance()
			->addModel(\GO\Calendar\Model\Event::model())
			->addCondition('exception_for_event_id', 0)
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

			$calendar_user_id = $event->calendar->user_id;
			$user_id = \GO::user()->id;

			if(!$event->private || $calendar_user_id == $user_id){

				$davEvent = DavEvent::model()->findByPk($event->id);
				if(!$davEvent || $davEvent->mtime != $event->mtime){
					$davEvent = CaldavModule::saveEvent($event, $davEvent);
				}

				$objects[] = array(
					'id' => $event->id,
					'uri' => $davEvent->uri,
					'calendardata' => $davEvent->data,
					'calendarid' => 'c:'.$calendarId,
					'lastmodified' => $event->mtime,
					'etag'=>'"' . date('Ymd H:i:s', $event->mtime). '-'.$event->id.'"'
				);

			}
		}


		if($calendar->tasklist_id>0)
		{
			$tasklist = Tasklist::findById($calendar->tasklist_id); // ignore acl?
			//$tasklist = \GO\Tasks\Model\Tasklist::model()->findByPk($calendar->tasklist_id, false, true);

			if($tasklist) {

//				$whereCriteria = FindCriteria::newInstance()
//					->addModel(\GO\Tasks\Model\Task::model())
//					->addCondition('tasklist_id', $calendar->tasklist_id)
//					->mergeWith(FindCriteria::newInstance()
//						->addModel(\GO\Tasks\Model\Task::model())
//						->addCondition('completion_time', 0)
//						->addCondition('due_time', \GO\Base\Util\Date::date_add(time(), 0, \GO::config()->caldav_max_months_old),'>','t',false));
//
//				$findParams = \GO\Base\Db\FindParams::newInstance()
//					->select('t.*')
//					->ignoreAcl()
//					->criteria($whereCriteria);
//
//				$stmt = \GO\Tasks\Model\Task::model()->find($findParams);
				$monthsOld = \GO::config()->caldav_max_months_old;
				$monthsOld = 0 - $monthsOld;

				$tasks = Task::find()
					->where(['tasklistId'=>$calendar->tasklist_id])
					//->andWhere('due > (NOW() - INTERVAL '.$monthsOld.' MONTH)') // DUE can be NULL
					->all();

				\GO::debug("Found ".count($tasks)." tasks");


				//			$sql = "SELECT * FROM ta_tasks WHERE tasklist_id=? AND (completion_time=0 OR due_time>?)";
				//			$count = $this->tasks->query($sql, 'ii', array($calendar['tasklist_id'], Date::date_add(time(),0,\GO::config()->caldav_max_months_old)));

				foreach ($tasks as $task) {
					//$davTask = DavTask::model()->findByPk($task->id);
					$blob = Blob::findById($task->vcalendarBlobId);
					$VCalendar = Reader::read($blob->getFile()->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
					$data = $VCalendar->serialize();

					$objects[] = array(
						'id' => $task->id,
						'uri' => $task->getUri(),
						'calendardata' => $data,
						'calendarid' => 'c:'.$calendarId,
						'lastmodified' => $task->modifiedAt->getTimestamp(),
						'etag'=>'"' .$task->etag().'"'
					);
				}
			}
		}

		//\GO::debug($objects);

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

		if(!$user)
			return false;

		if(!empty($GLOBALS['GO_CONFIG']->require_calendar_access_for_freebusy)){
			//Only show availability if user has access to the default calendar
			$default_calendar = $this->cal->get_default_calendar($user['id']);

			if(!$GLOBALS['GO_SECURITY']->has_permission($GLOBALS['GO_SECURITY']->user_id, $default_calendar['acl_id']))
				return false;
		}

		$events = $this->cal->get_events_in_array(0, $user['id'], $start, $end,true);

		$fb=array();

		while ($event = array_shift($events)) {
			//\GO::debug($event['name'].' - '.$event['uuid']);
			//if($event['uuid']!=$ignore_uuid)
			$fb[]=array('start'=>$event['start_time'],'end'=>$event['end_time'], 'busyType'=>'BUSY');
		}
		return $fb;
	}

	/**
	 * Returns information from a single calendar object, based on it's object uri.
	 *
	 * @param StringHelper $calendarId
	 * @param StringHelper $objectUri
	 * @return array
	 */
	public function getCalendarObject($calendarId, $objectUri) {

		\GO::debug("c:getCalendarObject($calendarId,$objectUri)");

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

//		$sql = "SELECT d.uri,e.*, d.mtime AS client_mtime, d.data FROM cal_events e INNER JOIN dav_events d ON d.id=e.id WHERE d.uri=? AND e.calendar_id=?";
//		$this->cal->query($sql, 'si', array($objectUri,$calendarId));
//		$event = $this->cal->next_record();

		//\GO::debug($event);

		$event = \GO\Calendar\Model\Event::model()->find($findParams)->fetch();

		if ($event) {

			\GO::debug('Found event');

			$data = ($event->mtime==$event->client_mtime && !empty($event->data)) ? $event->data : $this->exportCalendarEvent($event);
			\GO::debug($event->mtime==$event->client_mtime ? "Returning client data (mtime)" : "Returning server data (mtime)");


//			$data = $this->exportCalendarEvent($event);

			\GO::debug($data);

			$object = array(
				'id' => $event->id,
				'uri' => $event->uri,
				'calendardata' => $data,
				'calendarid' => $calendarId,
				'lastmodified' => $event->mtime,
				'etag'=>'"' . date('Ymd H:i:s', $event->mtime). '-'.$event->id.'"'
			);
			//\GO::debug($object);
			return $object;
		}
		else {
			$calendar = GO\Calendar\Model\Calendar::model()->findByPk($calendarId, false, true);
			//$calendar = $this->cal->get_calendar($calendarId);

//			$sql = "SELECT e.*, d.uri, d.mtime AS client_mtime, d.data  FROM ta_tasks e INNER JOIN dav_tasks d ON d.id=e.id WHERE d.uri=?";// AND e.tasklist_id=?";
//			$this->tasks->query($sql, 's', array($objectUri));
//			$task = $this->tasks->next_record();

//			$whereCriteria = FindCriteria::newInstance()
//				->addModel(\GO\Tasks\Model\Task::model())
//				->addModel(DavTask::model(),'d')
//				//->addCondition('tasklist_id', $calendarId) //Not necessary with the inner join on dav_tasks
//				->addCondition('uri', $objectUri,'=','d');
//
//			$joinCriteria = FindCriteria::newInstance()
//				->addRawCondition('t.id', 'd.id');
//
//			$findParams = FindParams::newInstance()
//				->single()
//				->select("t.*, d.uri, d.mtime AS client_mtime, d.data")
//				->criteria($whereCriteria)
//				->join(DavTask::model()->tableName(), $joinCriteria,'d', 'LEFT');
//			$task = \GO\Tasks\Model\Task::model()->find($findParams);
			$uid = str_replace('.vcf', '',$objectUri);
			$task = Task::find()->where(['uid'=>$uid, 'tasklistId'=>$calendar->tasklist_id])->single();

			if ($task) {

				\GO::debug('Found task');

				if(!empty($task->vcalendarBlobId)) {
					//task has a stored VCalendar
					$blob = Blob::findById($task->vcalendarBlobId);
					$VCalendar = Reader::read($blob->getFile()->open("r"), Reader::OPTION_FORGIVING + Reader::OPTION_IGNORE_INVALID_LINES);
					$data = $VCalendar->serialize();
				} else {
					$parser = new VCalendar();
					$data = $parser->export($task);
				}

				\GO::debug($data);

				$object = array(
					'id' => $task->id,
					'uri' => $task->getUri(),
					'calendardata' => $data,
					'calendarid' => $calendarId,
					'lastmodified' => $task->modifiedAt->getTimestamp(),
					'etag'=>'"' .$task->etag().'"'
				);


				return $object;
			}
		}
		throw new Sabre\DAV\Exception\NotFound('File not found');
	}

	/**
	 * Creates a new calendar object.
	 *
	 * @param StringHelper $calendarId
	 * @param StringHelper $objectUri NOTE: uri can be the same when importing the same ics file into multiple calendars
	 * @param StringHelper $calendarData
	 * @return void
	 */
	public function createCalendarObject($calendarId, $objectUri, $calendarData) {

		\GO::debug("createCalendarObject($calendarId,$objectUri,$calendarData)");

		try{

			$file = new \GO\Base\Fs\File($objectUri);
			$uuid = $file->nameWithoutExtension();

			if(strpos($calendarData, 'VEVENT')!==false){

				\GO::debug('item is an event');

				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);
				$event=false;
				foreach($vcalendar->vevent as $vevent){

					//$recurrenceId = isset($vevent->recurren)

					$recurrenceDate=false;
					$recurrence = $vevent->select('recurrence-id');
					//var_dump($recurrence);exit();
					if(count($recurrence)){
						$firstMatch = array_shift($recurrence);
						$recurrenceDate=intval($firstMatch->getDateTime()->format('U'));
					}


					//Lookup existing events. TB may create an event by e-mail invitation that is not yet synced to TB.
					$event = \GO\Calendar\Model\Event::model()->findByUuid($uuid, 0, $calendarId, $recurrenceDate);

					if(!$event)
						$event = new \GO\Calendar\Model\Event();

					$event->setUri($objectUri);
					$event->importVObject($vevent, array('calendar_id'=>$calendarId,'uuid'=>$uuid));

					if(!$recurrenceDate) {
						CaldavModule::saveEvent($event, false, $vcalendar->serialize());
					}
				}

				if (!$event)
					return false;
				else
					return $event->getETag();

				//store calendar data because we need to reply with the exact client data


			}else { // VTODO
				$calendar = Calendar::model()->findByPk($calendarId);
//				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);
//
//				$task = new Task();
				$parser = new VCalendar();
				$tasks = $parser->tasksFromVTodo($calendarData, $calendar->tasklist_id);



				$blob = Blob::fromString($calendarData);
				$blob->save();
				$task = $tasks[0];
				$task->vcalendarBlobId = $blob->id;
				$task->setUri($objectUri);
				if($task->save()){
					return $task->etag();
				} else {
					throw new \Exception(var_export($task->getValidationErrors(),true));
				}

//				$task = new \GO\Tasks\Model\Task();
//				$task->setUri($objectUri);
//				$task->importVObject($vcalendar->vtodo[0], array('tasklist_id'=>$calendar->tasklist_id,'uuid'=>$uuid));
//
//				CaldavModule::saveTask($task, false, $vcalendar->serialize());


			}
		}catch(\GO\Base\Exception\AccessDenied $e){
//			\GO::debug($e);
			throw new Sabre\DAV\Exception\Forbidden;
		}catch (\Exception $e) {
			\go\core\ErrorHandler::logException($e);
		}
		return false;
	}



	/**
	 * Updates an existing calendarobject, based on it's uri.
	 *
	 * @param StringHelper $calendarId
	 * @param StringHelper $objectUri
	 * @param StringHelper $calendarData
	 * @return string|null
	 */
	public function updateCalendarObject($calendarId, $objectUri, $calendarData) {

		\GO::debug("updateCalendarObject($calendarId,$objectUri,[data])");

		\GO::debug($calendarData);


		try{

			if(strpos($calendarData,'VEVENT')!==false){

				\GO::debug('item is an event');

				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);

				$event = $this->getEventByUri($objectUri, $calendarId);


				//			\GO::debug($event->getAttributes());

				if(!$event){
					\GO::debug("Event $objectUri not found in calendar $calendarId!");
					return false;
				}

				$exceptionVEvents=array();
				$VEvent=false;

				if($vcalendar->vevent->count()>1)
				{
					\GO::debug("Object has multiple VEVENT objects");
					//recurrence
					foreach($vcalendar->vevent as $e){
						if((string) $e->rrule!=''){
							$VEvent=$e;
						}else
						{
							$exceptionVEvents[]=$e;
						}
					}

				}else
				{
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


				$touched_event_ids=array($event->id);




				if(count($exceptionVEvents))
				{

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


						\GO::debug("Creating exception for date: ".$recurrenceDate);
						$exceptionEvent= $event->createExceptionEvent($recurrenceDate, array(), true);

						$exceptionEvent->importVObject($exceptionVEvent);

						$touched_event_ids[]=$exceptionEvent->id;

					}
				}

				return $event->getETag();

			}else
			{
				$calendar = Calendar::model()->findByPk($calendarId);
				\GO::debug('item is a task');

				$vcalendar = \GO\Base\VObject\Reader::read($calendarData);


				$parser = new VCalendar();
				$tasks = $parser->tasksFromVTodo($calendarData, $calendar->tasklist_id);
				if($tasks[0]->save()){
					return $tasks[0]->etag();
				}

				//$task = $this->getTaskByUri($objectUri, $calendar->tasklist_id);


//				$task->importVObject($vcalendar->vtodo[0]);
//
//				$davTask = DavTask::model()->findByPk($task->id);
//				CaldavModule::saveTask($task, $davTask, $vcalendar->serialize());
//
//				return $task->getETag();
			}

		}catch(\GO\Base\Exception\AccessDenied $e){
//			\GO::debug($e);
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
	 * @param StringHelper $calendarId
	 * @param StringHelper $objectUri
	 * @return void
	 */
	public function deleteCalendarObject($calendarId, $objectUri) {
		\GO::debug("deleteCalendarObject($calendarId,$objectUri)");

		try{
			$event = $this->getEventByUri($objectUri, $calendarId);
			if($event){
				$event->delete(); // will delete the DavEvent with an event
			}else{
				$task = $this->getTaskByUri($objectUri, $calendarId);
				if($task)
					$task->delete();  // will delete the DavTask with an event
			}
		}catch(\GO\Base\Exception\AccessDenied $e){
//			\GO::debug($e);
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
	 * @param StringHelper $calendarId
	 * @param StringHelper $syncToken
	 * @param int $syncLevel
	 * @param int $limit
	 * @return array
	 */
	public function getChangesForCalendar($calendarId, $syncToken, $syncLevel, $limit = null) {
		\GO::debug("getChangesForCalendar($calendarId,$syncToken)");
		// Current synctoken
		$calendar = GO\Calendar\Model\Calendar::model()->findByPk($calendarId);

		$currentToken = $calendar->version;

		if($calendar->tasklist_id) {
			$tasklist = Tasklist::findById($calendar->tasklist_id);

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
				$result['added'] = array_merge($result['added'],  Task::find(['uri'])->where(['tasklistId'=>$calendar->tasklist_id])->execute()->fetchColumn());
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
