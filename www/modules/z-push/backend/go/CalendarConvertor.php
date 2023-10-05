<?php

use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Participant;

class CalendarConvertor
{
	static $meetingResponseMap = [
		1 => Participant::Accepted,
		2 => Participant::Tentative,
		3 => Participant::Declined,
	];

	// $message->sensitivity
	static $privacyeMap = [
		0 => CalendarEvent::Public, // Normal
		1 => CalendarEvent::Public, // Personal
		2 => CalendarEvent::Private, // Private
		3 => CalendarEvent::Secret, // Confident
	];

	// $message->busystatus
	static $busyMap = [
		0 => 'free', // Free
		1 => 'busy', // Tentative
		2 => 'busy', // Busy
		3 => 'busy', // Out of office
		4 => 'busy', // working Elsewhere
	];

	static $participationStatusMap = [
		Participant::Declined => 4,
		Participant::Accepted => 3,
		Participant::Tentative => 2,
		Participant::NeedsAction => 0
	];


	/**
	 * SERVER -> PHONE
	 * @param CalendarEvent $event
	 * @param SyncAppointment|SyncAppointmentException $apppointment
	 * @return SyncAppointment
	 */
	static function toSyncAppointment($event, $apppointment = null, $params) {
		$message = $apppointment ?? new SyncAppointment();
		$message->timezone = GoSyncUtils::getTimeZoneForClient();
		$message->starttime = $event->start()->getTimestamp();
		$message->subject = $event->title;
		$message->uid = $event->uid;
		$message->location = $event->location;
		$message->endtime = $event->end()->getTimeStamp();
		$message->busystatus = $event->freeBusyStatus == 'busy' ? "2" : "0";
		$message->asbody = GoSyncUtils::createASBody($event->description, $params);
		$me = $event->currentUserParticipant();
		if($me && $me->roles['owner']) {
			//iphone uses busy status for events.
			$message->busystatus = array_search($me->part, self::$meetingResponseMap);
		}
		$message->dtstamp = $event->createdAt->getTimestamp();
		$message->alldayevent = $event->showWithoutTime;
		if($event->isRecurring()) {
			$message->recurrence = self::toSyncRecurrence($event);
			$message->exceptions = [];
			foreach($event->getRecurrenceOverrides() as $recurrenceId => $patch) {
				$xcp = new SyncAppointmentException();
				if($patch->excluded) {
					$xcp->deleted = 1;
				} else {
					$xcp = self::toSyncAppointment($patch, $xcp, $params);
				}
				$xcp->exceptionstarttime = $recurrenceId;
				$message->exceptions[] = $xcp;
			}
		}

		self::toSyncAttendee($event, $message, $me);

		$message->reminder = 0; // timestamp or 0
		if(!empty($event->alerts)) {
			foreach($event->alerts as $alert) {
				//todo
			}
		}
		return $message;
	}

	private static function toSyncAttendee($event, &$message, $me = null) {
		$message->meetingstatus = 0;
		if($me)
			$message->responsetype = self::$participationStatusMap[$me->participationStatus] ?? 0;
		if($me && $me->roles['owner']) {
			$message->organizername = $me->name;
			$message->organizeremail = $me->email;
			$message->responsetype = 1;
			$message->meetingstatus = $event->status === CalendarEvent::Cancelled ? 5 : 1;
		} else {
			$message->responsetype = 0;
			$message->meetingstatus = $event->status === CalendarEvent::Cancelled ? 7 : 3;
		}
		foreach($event->participants as $k => $participant) {
			/** @var $participant Participant */
			if($participant->roles['owner'])
				continue;

			$att = new SyncAttendee();
			$att->name = $participant->name;
			$att->email = $participant->email;
			$att->attendeetype = 1;
			$att->attendeestatus = self::$participationStatusMap[$participant->participationStatus] ?? 0;
			$message->attendees[] = $att;
		}
	}


	private static function toSyncRecurrence($event): SyncRecurrence
	{

		$rule = $event->recurrenceRule;

		$recur = new SyncRecurrence();
		$recur->interval = $rule->interval ?? null;
		$recur->until = $rule->until ?? null;
		$recur->occurrences = $rule->count ? null;

		switch ($rule->frequency) {
			case 'daily':
				$recur->type = 0;
				break;
			case 'weekly':
				$recur->type = 1;
				$recur->dayofweek = self::nDayToAS($rule->byDay);
				break;
			case 'monthly':
				if (isset($rrule->byday[0])) {
					$recur->type = 3;
					$recur->weekofmonth = $rule->bySetPosition;
					$recur->dayofweek = self::nDayToAS($rule->byDay);
				} else {
					$recur->dayofmonth = $event->start()->format('j');
					$recur->type = 2;
				}
				break;
			case 'yearly':
				$recur->type = 5;
				$recur->monthofyear = $event->start()->format('n');
				$recur->dayofmonth = $event->start()->format('j');
				break;
		}

		return $recur;
	}

	private static function nDayToAS($ndays) {
		static $v = ['su'=>1,'mo'=>2,'tu'=>4,'we'=>8,'th'=>16,'fr'=>32,'sa'=>64];
		$r = 0;
		foreach($ndays as $nday) {
			$r += $v[$nday->day];
		}
		return $r;
	}

	/**
	 * PHONE => SERVER
	 * @param \SyncAppointment $message
	 * @param CalendarEvent $event
	 * @return false|CalendarEvent
	 * @throws \Exception
	 */
	static function toCalendarEvent($message, $event) {

		if (isset($message->uid))
			$event->uid = $message->uid;
		if (isset($message->starttime)) {
			$event->start = new DateTime('@'.$message->starttime, $message->timezone);
			if($message->alldayevent) {
				$event->start_time = $this->importAllDayTime($event->start_time, $message->timezone);
			}
		}
		if (isset($message->endtime)){
			$event->end_time = $message->endtime;
			if($message->alldayevent) {
				$event->end_time = $this->importAllDayTime($event->end_time, $message->timezone);
			}
		}
		if (isset($message->location))
			$event->location = $message->location;
		if (isset($message->reminder))
			$event->reminder = $message->reminder * 60;

		if (isset($message->busystatus)) {
			$event->freeBusyStatus = empty($message->busystatus) ? 'free' : 'busy';
		}

		if (isset($message->sensitivity))
			$event->privacy = self::$privacyeMap[$message->sensitivity];

		$event->showWithoutTime = $message->alldayevent;
		$event->title = $message->subject ?? "No subject";
		$event->description = GoSyncUtils::getBodyFromMessage($message);

		if (isset($message->recurrence)) {
			$event->rrule = GoSyncUtils::importRecurrence($message->recurrence, $event->start_time);
		}

		//$event->cutAttributeLengths();
		if(!$event->save()){
			ZLog::Write(LOGLEVEL_WARN, var_export($event->getValidationErrors(), true));
			return false;
		}

		$event->exceptions()->callOnEach('delete');
		$event->exceptionEvents()->callOnEach('delete');

		$me = $event->currentUserParticipant();
		$isOrganizer = $me->roles['owner'];
		//don't update existing participants because it is unreliable data from the phone
		if($isOrganizer) {
			if (isset($message->attendees)) {
				self::toParticipants($message, $event, $me);
			}
		} elseif($message->busystatus == 1 || $message->busystatus == 2) { //tentative
			//iphone sends busy status for tentative
			$this->MeetingResponse($event->id, 'a/GroupOfficeCalendar', $message->busystatus == 1 ? 2 : 3);
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

	private static function toParticipants(SyncAppointment $message, $event, $me) {
		if(isset($message->organizeremail) && !$event->isOrigin) {
			// todo?
		}

		$participant = new Participant($event);
		$participant->email = $message->organizeremail;
		$participant->name = $message->organizername;
		//$participant->participationStatus =

		foreach ($message->attendees as $attendee) {

			$participant = new Participant($event);
			$participant->email = $attendee->email;
			$participant->name = $attendee->name;
			$participant->participationStatus = array_search($attendee->attendeestatus, self::$participationStatusMap) ?? Participant::NeedsAction;
		}

	}

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

}