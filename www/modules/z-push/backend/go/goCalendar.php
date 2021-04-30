<?php

class goCalendar extends GoBaseBackendDiff {

	
	public function DeleteMessage($folderid, $id, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goCalendar->DeleteMessage('.$folderid.','.$id.')');
		
		$event = \GO\Calendar\Model\Event::model()->findByPk($id);
		
//		if(!$event->is_organizer) {
//			
//			//iphone uses delete to decline!
//			if($this->MeetingResponse($id, "a/GroupOfficeCalendar", 4)) {
//				return true;
//			}
//		}
					
		// Only delete from GO when you have the right permissions for it.
		if ($event && $event->checkPermissionLevel(\GO\Base\Model\Acl::DELETE_PERMISSION)) {
			//HTC deletes old appointments. We don't like that so we refuse to delete appointments older then 7 days.
			if($event->start_time<\GO\Base\Util\Date::date_add(time(), -7)){
				return true;
			}  else {
				return $event->delete();
			}						
		} else {
			return true;
		}
	}
	
	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return \SyncAppointment
	 */
	public function GetMessage($folderid, $id, $contentparameters) {
		$event = \GO\Calendar\Model\Event::model()->findByPk($id);
		if($event)
			return $this->_handleEvent($event,$contentparameters);
		else
			return false;
	}
	
	/**
	 * Handle the event request
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param \GO\Calendar\Model\Event $event
	 * @return \SyncAppointment
	 */
	private function _handleEvent($event,$contentparameters, $exception=false) {
		$message = $exception ?  new SyncAppointmentException() : new SyncAppointment();

		$message->timezone = GoSyncUtils::getTimeZoneForClient(); //xP///wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAFAAEAAAAAAAAAxP///w==";
		$message->starttime = $event->start_time;
		$message->subject = $event->name;
		$message->uid = $event->uuid;
		$message->location = $event->location;
		$message->endtime = $event->end_time;
		
		if($event->all_day_event) {
			//correction because GO saves till 23:59
			$message->endtime += 60;
		}
		
		$message->busystatus = $event->busy == 1 ? "2" : "0";
		
		if(!$event->is_organizer)
		{
			$participant = GO\Calendar\Model\Participant::model()->findSingleByAttributes(array('event_id' => $event->id, 'user_id' => GO::user()->id));
			
			if($participant) {
				//iphone uses busy status for events.
				switch($participant->status) {
					case GO\Calendar\Model\Participant::STATUS_ACCEPTED: 
						$message->busystatus = 2;
						break;
					case GO\Calendar\Model\Participant::STATUS_TENTATIVE: 
						$message->busystatus = 1;
						break;
					case GO\Calendar\Model\Participant::STATUS_DECLINED: 
						$message->busystatus = 3;
						break;
				}
				
			}
		}
		
		$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());
		
		if (Request::GetProtocolVersion() >= 12.0) {
			$message->asbody = GoSyncUtils::createASBodyForMessage($event,'description',$bpReturnType);
		} else {
			$message->body = \GO\Base\Util\StringHelper::normalizeCrlf($event->description);
			$message->bodysize = strlen($message->body);
			$message->bodytruncated = 0;
		}
		
//			$message->sensitivity;
//			$message->deleted;
//			$message->categories;
		$message->dtstamp = $event->ctime;
//			$message->rtf;
		// AS 12.0 props
//			$message->nativebodytype;
		// AS 14.0 props
//			$message->disallownewtimeprop;
//			$message->responsetype;
//			$message->responserequested;

		$message->alldayevent = $event->all_day_event;

		if (!empty($event->rrule))
			$message->recurrence = GoSyncUtils::exportRecurrence($event);

		if ($event->reminder !== null){
			
			if(empty($event->reminder)){ // 0
				$message->reminder = 0;
			} else {
				$message->reminder = $event->reminder / 60;
			}
		}

		$exceptions = $this->_handleExceptions($event,$contentparameters);
		if ($exceptions !== false)
			$message->exceptions = $exceptions;

		$this->_handleParticipants($event, $message);

		//ZLog::Write(LOGLEVEL_DEBUG, 'MESSAGE '.var_export($message,true));
		return $message;
	}

	private function _handleExceptions($event,$contentparameters) {
		$stmt = $event->exceptions();
		$exceptions = false;

		if ($stmt->rowCount() > 0) {
			
			Zlog::Write(LOGLEVEL_DEBUG, "Found exceptions");
			$exceptions = array();
			while ($exception = $stmt->fetch()) {
				if ($exception->event) {
					$exceptionEvent = $exception->event;

					$xcp = $this->_handleEvent($exceptionEvent,$contentparameters, true);
					$xcp->exceptionstarttime = $exception->getStartTime();
					$exceptions[] = $xcp;
				} else {

					$xcp = new SyncAppointmentException();
					$xcp->exceptionstarttime = $exception->getStartTime();				
					$xcp->deleted = 1;
					$exceptions[] = $xcp;
				}
			}
		}

		return $exceptions;
	}

	/**
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param \GO\Calendar\Model\Event $event
	 * @param type $message
	 */
	private function _handleParticipants($event, &$message) {
		// Status 0 = no meeting, status 1 = organizer, status 2/3/4/5 = tentative/accepted/declined/notresponded
		$message->meetingstatus = 0;
		
		$organizer = $event->getOrganizer();
//		ZLog::Write(LOGLEVEL_DEBUG, '[SERVER -> PHONE]'.var_export($organizer,true));
		if(!empty($organizer)) {
			$message->organizername = $organizer->name;
			$message->organizeremail = $organizer->email;
			ZLog::Write(LOGLEVEL_DEBUG, '[SERVER -> PHONE] ORGANIZER '.$organizer->name.' : '.$organizer->email);
						
			$stmt = $event->participants();
			ZLog::Write(LOGLEVEL_DEBUG, '[SERVER -> PHONE] _handleParticipants rows:'.$stmt->rowCount());
			if ($stmt->rowCount() > 0) {
				
				
				$message->responsetype = $event->is_organizer ? 1 : 0;
				
				if($event->status == \GO\Calendar\Model\Event::STATUS_CANCELLED) {
					$message->meetingstatus = $event->is_organizer ? 5 : 7;
				}else
				{
					$message->meetingstatus = $event->is_organizer ? 1 : 3;
				}

				while ($participant = $stmt->fetch()) {
					ZLog::Write(LOGLEVEL_DEBUG, '[SERVER -> PHONE] PARTICIPANT'.$participant->is_organizer?'(organizer)':''.' '.$participant->name.' : '.$participant->email);
					if (!$participant->is_organizer) {
						
						
						
						$att = new SyncAttendee();
						$att->name = $participant->name;
						$att->email = $participant->email;
						$att->attendeetype = 1; //not supported by z-push 1
						switch ($participant->status) { //not supported by z-push 1
							case \GO\Calendar\Model\Participant::STATUS_ACCEPTED:
								$att->attendeestatus = 3;
								break;
							case \GO\Calendar\Model\Participant::STATUS_DECLINED:
								$att->attendeestatus = 4;
								break;
							case \GO\Calendar\Model\Participant::STATUS_TENTATIVE:
								$att->attendeestatus = 2;
								break;
							default:
								$att->attendeestatus = 0;
								break;
							}
							$message->attendees[] = $att;
							
							if($participant->user_id == GO::user()->id) {
								$message->responsetype = $att->attendeestatus;
							}
					}
				}
			}
		}
	}

	/**
	 * Handle the participants of the incoming appointment
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param \SyncAppointment $message
	 * @param \GO\Calendar\Model\Event $event
	 */
	private function _handleAppointmentParticipants(SyncAppointment $message, $event) {

		// Remove existing participants
		// this function is not called on updates because it's unreliable
		if (isset($message->attendees)) {
			$stmt = $event->participants();

			$existingParticipants = array();
			$hasOrganizer=false;
			foreach($stmt as $participant){
				if($participant->is_organizer){
					$hasOrganizer = true;
				}else
				{
					$existingParticipants[$participant->email]=$participant;
				}
			}

			///ZLog::Write(LOGLEVEL_DEBUG, '[PHONE -> SERVER] goCalendar->handleAppointmentParticipants('.  var_export($message,true).','.  var_export($event,true).')');

		
			
			if(isset($message->organizeremail)){
				
				if(!$hasOrganizer){
					$organizer = $event->getOrganizer();

					if($organizer){

						$organizer->email = $message->organizeremail;

						if(isset($message->organizername))
							$organizer->name = $message->organizername;

					} else {

						$organizer = new \GO\Calendar\Model\Participant();

						$organizer->email = $message->organizeremail;
						$organizer->is_organizer = true;

						if(isset($message->organizername))
							$organizer->name = $message->organizername;

						if(!$event->addParticipant($organizer))
							ZLog::Write(LOGLEVEL_ERROR, '[PHONE -> SERVER] Could not add the organizer('.$message->organizeremail.') to the event('.$event->name.')!');
					}
				}
			}elseif(!$hasOrganizer){
				$organizer = $event->getDefaultOrganizerParticipant();
				$organizer->save();
			}

			foreach ($message->attendees as $attendee) {

				
				if(isset($existingParticipants[$attendee->email])){
					$participant = $existingParticipants[$attendee->email];
					unset($existingParticipants[$attendee->email]);
				}  else {
					$participant = new \GO\Calendar\Model\Participant();
				}
				
				$participant->event_id = $event->id;
				$participant->email = $attendee->email;
				$participant->name = $attendee->name;
				$participant->status = \GO\Calendar\Model\Participant::STATUS_PENDING;
				
				if(isset($attendee->attendeestatus)) {
					switch($attendee->attendeestatus) {
						case 2:
							$participant->status = \GO\Calendar\Model\Participant::STATUS_TENTATIVE;
							break;
						case 3:
							$participant->status = \GO\Calendar\Model\Participant::STATUS_ACCEPTED;
							break;
						case 4:
							$participant->status = \GO\Calendar\Model\Participant::STATUS_DECLINED;
							break;
					}
				}
				ZLog::Write(LOGLEVEL_DEBUG, '[PHONE -> SERVER] PARTICIPANT '.$participant->name.' : '.$participant->email);
				$success = $participant->save();
				ZLog::Write(LOGLEVEL_DEBUG, '[PHONE -> SERVER] PARTICIPANT SAVE ~~ '.$success?'OK':'ERROR');
			}
		
		
			foreach($existingParticipants as $notIncludedParticipant){

				ZLog::Write(LOGLEVEL_DEBUG, "DELETE participant: ".$notIncludedParticipant->email);
				$notIncludedParticipant->delete();
			}
		}
	}
	
	private $timezone;
	private function getDefaultTimeZone() {
		if(!isset($this->timezone)) {
			$this->timezone = go()->getAuthState()->getUser(['timezone'])->timezone;
		}
		return $this->timezone;
	}

	private function importAllDayTime($time) {
		$dt = new \DateTime('@'.$time, new \DateTimeZone("UTC"));		
		$dt->setTimezone(new \DateTimeZone($this->getDefaultTimeZone()));
		$dt->setTime(0, 0);
		$newTime = $dt->format("U");

		return $newTime;
	}
	
	/**
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param \SyncAppointment $message
	 * @param \GO\Calendar\Model\Event $event
	 * @return type
	 */
	private function _handleAppointment($message, $event) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goCalendar->_handleAppointment() MESSAGE ~~~ ');

		
//		$message->timezone;
//    $message->dtstamp;
//    $message->sensitivity;
//    $message->rtf;
//    $message->meetingstatus;
//    $message->attendees;
//    $message->bodytruncated;
//    $message->exception;
//    $message->deleted;
//    $message->exceptionstarttime;
//    $message->categories;
//
//    // AS 12.0 props
//    $message->nativebodytype;
//
//    // AS 14.0 props
//    $message->disallownewtimeprop;
//    $message->responsetype;
//    $message->responserequested;
		
		if (isset($message->uid))
			$event->uuid = $message->uid;
		if (isset($message->starttime)) {
			$event->start_time = $message->starttime;
			if($message->alldayevent) {
				$event->start_time = $this->importAllDayTime($event->start_time);
			}
		}
		if (isset($message->endtime)){
			$event->end_time = $message->endtime;
			if($message->alldayevent) {
				$event->end_time = $this->importAllDayTime($event->end_time);
			}
		}
		if (isset($message->location))
			$event->location = $message->location;
		if (isset($message->reminder))
			$event->reminder = $message->reminder * 60;
		if (isset($message->busystatus))
			$event->busy = !empty($message->busystatus) ? 2 : 0;
		if (isset($message->sensitivity))
			$event->private = empty($message->sensitivity) ? 0 : 1;
		if (!empty($message->alldayevent)) {
			$event->end_time -= 60;
		}
		$event->all_day_event = $message->alldayevent;
		$event->name = !empty($message->subject) ? $message->subject : "No subject";
		
		$event->description = GoSyncUtils::getBodyFromMessage($message);
		
		if (isset($message->recurrence)) {
			if (!empty($message->recurrence->until))
				$event->repeat_end_time = $message->recurrence->until;
			$event->rrule = GoSyncUtils::importRecurrence($message->recurrence, $event->start_time);
		}
		
		$new = $event->isNew;

		$event->cutAttributeLengths();
		if(!$event->save()){
			ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2EVENT::Could not save ' . $event->id);				
			ZLog::Write(LOGLEVEL_WARN, var_export($event->getValidationErrors(), true));
			return false;
		}	

		$event->exceptions()->callOnEach('delete');
		$event->exceptionEvents()->callOnEach('delete');

		
		
		//don't update existing participants because it is unreliable data from the phone
		if($event->is_organizer)
			$this->_handleAppointmentParticipants($message, $event);
		else
		{
			//iphone sends busy status for tentative
			if($message->busystatus == 1 || $message->busystatus == 2) //tentative
			{
				$this->MeetingResponse($event->id, 'a/GroupOfficeCalendar', $message->busystatus == 1 ? 2 : 3);
			}
		}

		if (isset($message->exceptions)) {
			foreach ($message->exceptions as $k => $v) {
				if (!$v->deleted) {
					$e = $event->createExceptionEvent($v->exceptionstarttime, array(), true);
					$e->calendar_id = $event->calendar_id;
					$e->exception_for_event_id = $event->id;
					$e->uuid = $event->uuid;
					// Recursive add the appointment exceptions
					ZLog::Write(LOGLEVEL_DEBUG, "Creating exception");
					$e = $this->_handleAppointment($v, $e);
				} else {
					$event->createException($v->exceptionstarttime);
				}
			}
		}
		return $event;
	}

	/**
	 * Save the information from the phone to Group-Office.
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param \SyncAppointment $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message,$contentParameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goCalendar->ChangeMessage('.$folderid.','.$id.',)');
		try {

			$event = \GO\Calendar\Model\Event::model()->findByPk($id);

			if ($event) {
				if ($event->permissionLevel < \GO\Base\Model\Acl::WRITE_PERMISSION) {
					ZLog::Write(LOGLEVEL_DEBUG, "Skipping update of read-only event " . $event->name);
					return $this->StatMessage($folderid, $id);
				}
			} else {
				$calendar = GoSyncUtils::getUserSettings()->getDefaultCalendar();

				if (!$calendar)
					throw new \Exception("FATAL: No default calendar configured");

				$event = new \GO\Calendar\Model\Event();
				$event->calendar_id = $calendar->id;
			}

			$event = $this->_handleAppointment($message, $event);
			if(!$event)
				return false;
			
			$id = $event->id;
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2CALENDAR::EXCEPTION ~~ ' . (string) $e);
		}

		return $this->StatMessage($folderid, $id);
	}

	/**
	 * Get the status of an item
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {

		$event = \GO\Calendar\Model\Event::model()->findByPk($id);
		$stat = false;
		if ($event) {
			$stat = array();
			$stat["id"] = $event->id;
			$stat["flags"] = 1;
			$stat["mod"] = $event->mtime;
		}

		return $stat;
	}
	
	
	/**
     * Processes a response to a meeting request.
     * CalendarID is a reference and has to be set if a new calendar item is created
     *
     * @param string        $requestid      id of the object containing the request
     * @param string        $folderid       id of the parent folder of $requestid
     * @param string        $response
     *
     * @access public
     * @return string       id of the created/updated calendar obj
     * @throws StatusException
     */
    public function MeetingResponse($requestid, $folderid, $response) {
			
			ZLog::Write(LOGLEVEL_DEBUG, 'goCalendar->MeetingResponse('.$requestid.', '.$folderid.', '.$response.')');
			
			$event = \GO\Calendar\Model\Event::model()->findByPk($requestid);
			
			$participant = GO\Calendar\Model\Participant::model()->findSingleByAttributes(array('user_id' => GO::user()->id, 'event_id' => $requestid));
			if(!$participant) {
				throw new StatusException("Participant not found!");
			}
			
			switch($response) {				
				case 2:
					$participant->status = \GO\Calendar\Model\Participant::STATUS_TENTATIVE;
					break;
				case 1: //???
				case 3:
					$participant->status = \GO\Calendar\Model\Participant::STATUS_ACCEPTED;
					break;
				case 4:
					$participant->status = \GO\Calendar\Model\Participant::STATUS_DECLINED;
					break;
			}
			
			if(!$participant->save(false)) {
				throw new StatusException("Failed to save participant");
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'Participant '.$participant->id.' set to status '.$participant->status);
			
			return $requestid;
	}

	/**
	 * Get the list of the items that need to be synced
	 * 
	 * @param StringHelper $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

		$messages = array();
		if (!\GO::modules()->calendar) {
			return $messages;
		}
		
		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->select('t.id,t.mtime,t.private,t.calendar_id')
//						->joinModel(array(
//								'model' => 'GO\Sync\Model\UserCalendar',
//								'tableAlias' => 'ua',
//								'localTableAlias' => 't',
//								'localField' => 'calendar_id',
//								'foreignField' => 'calendar_id'
//						))
						->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('calendar_id', $folderid)
//							->addCondition('user_id', \GO::user()->id, '=', 'ua')
							->addCondition('exception_for_event_id', 0)
										);

		if (!empty($cutoffdate)) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Client sent cutoff date for calendar: ' . \GO\Base\Util\Date::get_timestamp($cutoffdate));

			$params->getCriteria()->mergeWith(\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('end_time', $cutoffdate, '>=')
							->mergeWith(
											\GO\Base\Db\FindCriteria::newInstance()
											->addCondition('rrule', '', '!=')
											->mergeWith(
															\GO\Base\Db\FindCriteria::newInstance()
															->addCondition('repeat_end_time', 0)
															->addCondition('repeat_end_time', $cutoffdate, '>=', 't', false))
											, false)
			);
		}

		$stmt = \GO\Calendar\Model\Event::model()->find($params);

		while ($event = $stmt->fetch()) {

			if(!$event->private || $event->calendar->user_id == \GO::user()->id){
				$message = array();
				$message['id'] = $event->id;
				$message['mod'] = $event->mtime;
				$message['flags'] = 1;
				$messages[] = $message;
			}
		}

		return $messages;
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param StringHelper $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {

		$calendar = \GO\Calendar\Model\Calendar::model()->findByPk($id);
		if(!$calendar) {
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $calendar->name;
		$folder->type = SYNC_FOLDER_TYPE_APPOINTMENT;

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 * 
	 * @return array
	 */
	public function GetFolderList() {
		$folders = array();

		$params = \GO\Base\Db\FindParams::newInstance()
			->ignoreAcl()
			->join(\GO\Sync\Model\UserCalendar::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
				->addCondition('id', 's.calendar_id', '=', 't', true, true)
				->addCondition('user_id', \GO::user()->id, '=', 's')
				, 's');

		$calendars = \GO\Calendar\Model\Calendar::model()->find($params);
		foreach($calendars as $calendar) {
			$folder = $this->StatFolder($calendar->id);

			$folders[] = $folder;
		}

		return $folders;
	}
	
	
	public function getNotification($folder=null) {

		
		$params = \GO\Base\Db\FindParams::newInstance()
						->ignoreAcl()
						->single(true, true)
						->select('count(*) AS count, max(mtime) AS lastmtime')
						->join(\GO\Sync\Model\UserCalendar::model()->tableName(), \GO\Base\Db\FindCriteria::newInstance()
						->addCondition('calendar_id', 's.calendar_id', '=', 't', true, true)
						->addCondition('user_id', \GO::user()->id, '=', 's')
						, 's');
		

		$record = \GO\Calendar\Model\Event::model()->find($params);

		$lastmtime = isset($record->lastmtime) ? $record->lastmtime : 0;
		$newstate = 'M'.$lastmtime.':C'.$record->count;
		
		ZLog::Write(LOGLEVEL_DEBUG,'goCalendar->getNotification() State: '.$newstate);

		return $newstate;
	}

}
