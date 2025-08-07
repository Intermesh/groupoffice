<?php

use go\core\model\Acl;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Participant;
use go\modules\community\calendar\model\RecurrenceOverride;

class CalendarStore extends Store {

	public function GetFolder($id)
	{
		$calendar = Calendar::findById($id);
		if (!$calendar || !$calendar->isSubscribed) {
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $calendar->name;

		$user = go()->getAuthState()->getUser(['calendarPreferences']);

		if($user && $user->calendarPreferences->defaultCalendarId == $id) {
			$folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;
		} else {
			$folder->type = SYNC_FOLDER_TYPE_USER_APPOINTMENT;
		}

		return $folder;
	}

	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
		//remove t/ from the folder
		$oldid = substr($oldid, 2);
		if($oldid) {
			$calendar = Calendar::findById($oldid);
			if(!$calendar) {
				ZLog::Write(LOGLEVEL_DEBUG, "Calendar with $oldid not found");
				return false;
			}
		} else{
			$calendar = new Calendar();
		}
		$calendar->name = $displayname;
		return !$calendar->save() ? false : $this->StatFolder($calendar->id);
	}

	public function GetFolderList()
	{
		return Calendar::find()->select('caluser.id, name as "mod", "0" as parent')
			->andWhere('isSubscribed', '=', 1)
			->fetchMode(\PDO::FETCH_ASSOC)
			->all();
	}

	public function GetMessageList($folderid, $cutoffdate)
	{
		$query = CalendarEvent::find()->select(['cce.id', 'UNIX_TIMESTAMP(modifiedAt) as "mod"', '1 as flags']);
		$filter = [
			'inCalendars'=>$folderid,
			'after' => date('Y-m-d H:i:s', $cutoffdate),
			'hideSecret'=>1
		];
		if (!empty($cutoffdate)) {
			$filter['after'] = date('Y-m-d H:i:s', $cutoffdate);
		}
		$query->filter($filter);
		ZLog::Write(LOGLEVEL_INFO, "GetMessageList ".$folderid. ' '. $cutoffdate);
		return $query->fetchMode(\PDO::FETCH_ASSOC)->all();
	}

	public function StatMessage($folderid, $id)
	{
		$event = CalendarEvent::find()
			->select(['cce.id', 'modifiedAt'])
			->where(['cce.id'=>$id,'cce.calendarId'=>$folderid])
			->single();

		return $event ? [
			'id' => $event->id,
			'flags' => 1,
			'mod' => $event->modifiedAt->getTimestamp()
		] : false;
	}

	public function GetMessage($folderid, $id, $contentparameters)
	{
		$event = CalendarEvent::findById($id);
		ZLog::Write(LOGLEVEL_INFO, "GetMessage ".$event->id . ' - '.$event->title);

		if(!$event) {
			return false;
		}

		try {
			$msg = CalendarConvertor::toSyncAppointment($event, null, $contentparameters);
			ZLog::Write(LOGLEVEL_DEBUG, "reminder: " . ($msg->reminder ?? "-"));
			return $msg;
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
		}

		return false;
	}

	public function ChangeMessage($folderid, $id, $message, $contentParameters)
	{
		ZLog::Write(LOGLEVEL_DEBUG, "ChangeMessage($folderid, $id, ..., ...)");

		$event = CalendarEvent::findById($id);

		CalendarEvent::$sendSchedulingMessages = true;

		if (!$event) {
			$event = new CalendarEvent();
			$event->prodId = 'GroupOffice (EAS)';
		} else if (!$event->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			ZLog::Write(LOGLEVEL_DEBUG, "Skipping update of read-only event " . $event->title);
			return $this->StatMessage($folderid, $id);
		}

		// what if it's already in this calendar ???? ticket #36205
		// Can't replicate it with an iphone as it's impossible to move an event that is in multiple calendars. Perhaps
		// with android. I hope the exception handling below will catch the error and will undo the change.
		$event->calendarId = $folderid;

		try {
			$event = CalendarConvertor::toCalendarEvent($message, $event);

			if(!$event->save()){
				ZLog::Write(LOGLEVEL_WARN, "Failed to save event: " . var_export($event->getValidationErrors(), true));
				return false;
			}

		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
		}

		return $this->StatMessage($folderid, $event->id);
	}

	/**
	 * Resolves recipients
	 *
	 * @todo
	 *
	 * @param SyncObject        $resolveRecipients
	 *
	 * @access public
	 * @return SyncObject       $resolveRecipients
	 */
	public function ResolveRecipients($resolveRecipients) {
		$r = new SyncResolveRecipients();
		$r->status = SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR;
		return $r;
	}

	public function MeetingResponse($requestid, $folderid, $response, $instanceId) {

		ZLog::Write(LOGLEVEL_DEBUG, "MeetingResponse($requestid, $folderid, $response, $instanceId)");

		$event = CalendarEvent::findById($requestid);

		if($event->isRecurring() && isset($instanceId)) {

			$recurId = (new \go\core\util\DateTime($instanceId))->setTimezone($event->timeZone())->format('Y-m-d\TH:i:s');

			$status = CalendarConvertor::$meetingResponseMap[$response] ?? Participant::Accepted;;

			$email = Calendar::find()
				->join('core_user', 'u', 'calendar_calendar.ownerId = u.id')
				->where(['id' => $event->calendarId])
				->selectSingleValue('u.email')->single();

			\go\modules\community\calendar\model\Scheduler::updateRecurrenceStatus($event, $recurId, $email, $status, new \go\core\util\DateTime());

		} else {
			$me = $event->calendarParticipant();
			if (!$me) {
				throw new StatusException("Participant not found!");
			}
			$me->participationStatus = CalendarConvertor::$meetingResponseMap[$response] ?? Participant::Accepted;
		}
		if(!$event->save()) {
			throw new StatusException("Failed to save participant");
		}
	//	ZLog::Write(LOGLEVEL_DEBUG, 'Participant '.$me->email.' set to status '.$me->participationStatus);
		return $requestid;
	}

	public function DeleteMessage($folderid, $id, $contentParameters)
	{
		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			ZLog::Write(LOGLEVEL_INFO, 'Deleting by sync is disabled in user settings');
			throw new StatusException(SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}
		$event = CalendarEvent::findById($id);

		if(!$event ||
			!$event->hasPermissionLevel(Acl::LEVEL_DELETE) ||  // Only delete from GO when you have the right permissions for it.
			$event->start < (new DateTime())->modify('-7 days') //HTC deletes old appointments. We don't like that so we refuse to delete appointments older then 7 days.
		) {
			return true;
		}

		try {
			return CalendarEvent::delete(['id'=>$event->id]);
		} catch (Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Calender::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

	}

	public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {

		$event = CalendarEvent::findById($id);
		if(!$event || !$event->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			ZLog::Write(LOGLEVEL_WARN, "Event moved with id = " . $id ." in folder ". $folderid);
			return false;
		}

		$event->calendarId = $newfolderid;
		return !$event->save() ? false : $event->id;
	}

	public function getNotification($folder=null) {

		$newstate = CalendarEvent::find()
			->selectSingleValue('CONCAT("M",IFNULL(UNIX_TIMESTAMP(MAX(modifiedAt)),0),":C",COUNT(*))')
			->where('calendarId', '=', $folder)->single();

		ZLog::Write(LOGLEVEL_DEBUG,'CalendarStore->getNotification('.$folder.') State: '.$newstate);

		return $newstate;
	}

}