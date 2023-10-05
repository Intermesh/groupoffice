<?php

use go\core\model\Acl;
use go\modules\community\calendar\model\Calendar;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Participant;

class CalendarStore2 extends Store {

	public function GetFolder($id)
	{
		$calendar = Calendar::findById($id);
		if (!$calendar) {
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $calendar->name;
		$folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;

		return $folder;
	}

	public function GetFolderList()
	{
		return Calendar::find()->select(['id', 'name as mod', '"0" as parent'])
			->andWhere('isSubscribed', '=', '1')
			->fetchMode(\PDO::FETCH_ASSOC)
			->all();
	}

	public function GetMessage($folderid, $id, $contentparameters)
	{
		$event = CalendarEvent::findById($id);
		return !$event ? false : CalendarConvertor::toSyncAppointment($event, null, $contentparameters);
	}

	public function ChangeMessage($folderid, $id, $message, $contentParameters)
	{
		$event = CalendarEvent::findById($id);

		if (!$event) {
			$event = new CalendarEvent();
		} else if (!$event->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			ZLog::Write(LOGLEVEL_DEBUG, "Skipping update of read-only event " . $event->title);
			return $this->StatMessage($folderid, $id);
		}

		$event->calendarId = $folderid;
		$event = CalendarConvertor::toCalendarEvent($message, $event);

		return !$event->save() ? false : $this->StatMessage($folderid, $event->id);
	}

	public function StatMessage($folderid, $id)
	{
		$event = CalendarEvent::find()
			->select('id', 'modifiedAt')
			->where(['id'=>$id,'calendarId'=>$folderid])
			->single();
		return $event ? [
			'id' => $event->id,
			'flags' => 1,
			'mod' => $event->modifiedAt->format('c')
		] : false;
	}


	public function MeetingResponse($requestid, $folderid, $response) {
		$event = CalendarEvent::findById($requestid);
		$me = $event->currentUserParticipant();
		if(!$me) {
			throw new StatusException("Participant not found!");
		}
		$me->participationStatus = CalendarConvertor::$meetingResponseMap[$response] ?? Participant::Accepted;

		if(!$event->save()) {
			throw new StatusException("Failed to save participant");
		}
		ZLog::Write(LOGLEVEL_DEBUG, 'Participant '.$me->email.' set to status '.$me->participationStatus);
		return $requestid;
	}

	public function GetMessageList($folderid, $cutoffdate)
	{
		$query = CalendarEvent::find()
			->select(['id', 'modifiedAt as mod', '1 as flags'])
			->where(['calendarId' => $folderid]);

		if (!empty($cutoffdate)) {
			$query->andWhere('lastOccurrence > ' . date('Y-m-d H:i:s', $cutoffdate));
		}

		if(Calendar::findById($folderid)->ownerId != go()->getUserId()) {
			$query->andWhere('visibility', '=', 'public');
		}

		return $query->all();
	}

	public function DeleteMessage($folderid, $id, $contentParameters)
	{
		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Deleting by sync is disabled in user settings');
			throw new StatusException(SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}
		$event = CalendarEvent::findById($id);

		if(!$event ||
			!$event->hasPermissionLevel(Acl::LEVEL_DELETE) ||  // Only delete from GO when you have the right permissions for it.
			$event->start < (new DateTime())->modify('-7 days') //HTC deletes old appointments. We don't like that so we refuse to delete appointments older then 7 days.
		) {
			return true;
		}

		return CalendarEvent::delete(['id'=>$event->id]);
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

}