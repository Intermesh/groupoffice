<?php

use go\modules\community\calendar\model\Alert;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Participant;
use go\modules\community\calendar\model\RecurrenceOverride;
use go\core\util\DateTime;

class CalendarConvertor
{
	static $meetingResponseMap = [
		1 => Participant::Tentative,
		2 => Participant::Accepted,
		3 => Participant::Declined,
	];

	// $message->sensitivity
	static $privacyMap = [
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
	 * @param SyncAppointment|SyncAppointmentException $appointment
	 * @return SyncAppointment
	 */
	static function toSyncAppointment($event, $appointment = null, $params) {
		$message = $appointment ?? new SyncAppointment();
		if(!empty($event->timeZone))
			$message->timezone =  self::mstzFromTZID($event->timeZone);
		$message->starttime = $event->start()->getTimestamp();
		$message->subject = $event->title;
		$message->uid = $event->uid;
		$message->location = $event->location;
		$message->endtime = $event->end()->getTimeStamp();
		$message->busystatus = $event->freeBusyStatus == 'busy' ? "2" : "0";
		$message->asbody = GoSyncUtils::createASBody($event->description, $params);
		$calPart = $event->calendarParticipant();
		if($calPart && !$calPart->isOwner()) {
			//iphone uses busy status for events.
			$stat = array_search($calPart->participationStatus, self::$meetingResponseMap);
			if($stat !== false) {
				$message->busystatus = $stat;
			}
		}
		$message->dtstamp = $event->createdAt->getTimestamp();
		if($event->showWithoutTime)
			$message->alldayevent = 1;
		if($event->isRecurring()) {
			$message->recurrence = self::toSyncRecurrence($event);
			if(!empty($event->recurrenceOverrides)) {
				$message->exceptions = [];
				foreach ($event->recurrenceOverrides as $recurrenceId => $patch) {
					$xcp = new SyncAppointmentException();
					if ($patch->excluded) {
						$xcp->deleted = 1;
					} else {
						$xcp = self::toSyncAppointment($patch, $xcp, $params);
					}
					$xcp->exceptionstarttime = $recurrenceId;
					$message->exceptions[] = $xcp;
				}
			}
		}

		self::toSyncAttendee($event, $message, $calPart);

		//$message->reminder = 0; // timestamp or 0
//		if(!empty($event->alerts)) {
//			foreach($event->alerts as $alert) {
//				//todo
//			}
//		}
		return $message;
	}

	private static function mstzFromTZID($timeZone) {
		$msId = str_replace('.','',intltz_get_windows_id($timeZone));
		$tz = TimezoneUtil::GetFullTZFromTZName($msId);
		return base64_encode(TimezoneUtil::GetSyncBlobFromTZ($tz));
	}

	private static function toSyncAttendee($event, &$message, $me = null) {
		$message->meetingstatus = 0;
		if(empty($event->participants))
			return;

		if($me)
			$message->responsetype = self::$participationStatusMap[$me->participationStatus] ?? 0;
		if($me && $me->isOwner()) {
			$message->responsetype = 1;
			$message->meetingstatus = $event->status === CalendarEvent::Cancelled ? 5 : 1;
		} else {
			$message->responsetype = 0;
			$message->meetingstatus = $event->status === CalendarEvent::Cancelled ? 7 : 3;
		}
		foreach($event->participants as $k => $participant) {
			/** @var $participant Participant */
			if ($participant->isOwner()) {
				$message->organizername = $participant->name;
				$message->organizeremail = $participant->email;
				//continue;
			}

			$att = new SyncAttendee();
			$att->name = $participant->name;
			$att->email = $participant->email;
			$att->attendeetype = 1; // 1=required, 2=optional, 3=resource
			$att->attendeestatus = self::$participationStatusMap[$participant->participationStatus] ?? 0;
			$message->attendees[] = $att;
			if($participant == $me) {
				$message->responsetype = self::$participationStatusMap[$participant->participationStatus] ?? 0;
			}
		}
	}

	private static function toSyncRecurrence(CalendarEvent $event): SyncRecurrence
	{
		$rule = $event->getRecurrenceRule();

		$recur = new SyncRecurrence();
		if(isset($rule->interval))
			$recur->interval = $rule->interval;
		if(!empty($rule->until))
			$recur->until = (new DateTime($rule->until))->getTimestamp();
		if(!empty($rule->count))
			$recur->occurrences = $rule->count;

		switch ($rule->frequency) {
			case 'daily':
				$recur->type = 0;
				break;
			case 'weekly':
				$recur->type = 1;
				if(isset($rule->byDay))
					$recur->dayofweek = self::nDayToAS($rule->byDay);
				break;
			case 'monthly':
				if (isset($rule->byDay[0])) {
					$recur->type = 3;
					$recur->weekofmonth = $rule->bySetPosition;
					if(isset($rule->byDay))
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
		if($ndays) {
			foreach ($ndays as $nday) {
				$r += $v[$nday->day];
			}
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
	static function toCalendarEvent($message, $event)
	{
		if (isset($message->uid))
			$event->uid = $message->uid;
		if (!empty($message->timezone))
			$event->timeZone = self::tzid();
		if ($event->isNew())
			$event->createdAt = new DateTime('@'.$message->dtstamp);
		$event->showWithoutTime = $message->alldayevent;
		$event->title = $message->subject ?? "No subject";
		$event->description = GoSyncUtils::getBodyFromMessage($message);
		$dtstart = new DateTime('@'.(isset($message->starttime) ? $message->starttime : $message->dtstamp));
		if (isset($message->endtime)) {
			$dtend = new DateTime('@' . $message->endtime);
			$event->duration = DateTime::intervalToISO($dtend->diff($dtstart));
		}
		$event->start = $dtstart->setTimezone($event->timeZone());
		if (isset($message->location))
			$event->location = $message->location;
		if (isset($message->busystatus))
			$event->freeBusyStatus = empty($message->busystatus) ? 'free' : 'busy';
		if (isset($message->sensitivity))
			$event->privacy = self::$privacyMap[$message->sensitivity];

		$calPart = $event->calendarParticipant();
		//don't update existing participants because it is unreliable data from the phone
		if ($calPart && $calPart->isOwner()) {
			if (isset($message->attendees)) {
				self::toParticipants($message, $event, $calPart);
			}
		//} elseif ($message->busystatus == 1 || $message->busystatus == 2) { //tentative or busy
			// iPhone sends busy status for tentative
			//self::MeetingResponse($event->id, 'a/GroupOfficeCalendar', $message->busystatus == 1 ? 2 : 3);
		}

		if (isset($message->recurrence)) {
			$event->setRecurrenceRule(self::toRecurrenceRule($message->recurrence));
		}
		if (isset($message->exceptions)) {
			$event->recurrenceOverrides = []; // empty first to delete what is not present
			foreach ($message->exceptions as $v) {
				$recurrenceId = $v->exceptionstarttime->format('Y-m-d\Th:i:s');
				$event->recurrenceOverrides[$recurrenceId] = (new RecurrenceOverride($event))
					->setValues(self::toOverride($v));
			}
		}

		if (isset($message->reminder)){
			$event->alerts = [(new Alert($event))->setValues([
				'action' => 'display',
				'offset' => $message->reminder
			])];
		}

//		$event->exceptions()->callOnEach('delete');
//		$event->exceptionEvents()->callOnEach('delete');

		return $event;
	}

	private static function tzid() {
		return go()->getAuthState()->getUser(['timezone'])->timezone;
	}

	static private function toRecurrenceRule(SyncRecurrence $recur) {
		static $recurType = [0=>'daily',1=>'weekly',2=>'monthy',3=>'montly',4=>'yearly',5=>'yearly'];
		$r = (object)['frequency'=>$recurType[$recur->type]];
		if(!empty($recur->interval))
			$r->interval = $recur->interval;
		if (!empty($recur->until))
			$r->until = $recur->until;
		$r->byday = self::aSync2Nday($recur->dayofweek);

		if (!empty($recur->weekofmonth))
			$r->bysetpos = $recur->weekofmonth;
		return $r;
	}

	static private function aSync2Nday(?int $bitmask) {
		static $days = ['su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'];
		$byday = [];
		for ($i = 0; $i < 7; $i++) {
			if (($bitmask & (1 << $i)) > 0) {
				$byday[] = (object)['day'=>$days[$i]];
			}
		}
		return $byday;
	}
	/**
	 * Used for recurrenceOverrides only
	 * @param $values
	 * @return array
	 */
	private static function toOverride(SyncAppointmentException $values) {
		if($values->deleted)
			return ['excluded' => true];
		$ex = new \stdClass;
		if(isset($values->subject))
			$ex->title = $values->subject;
		$description = GoSyncUtils::getBodyFromMessage($values);
		if(!empty($description))
			$ex->description = $description;
		if(isset($values->starttime))
			$ex->start = $values->starttime;
		if (isset($values->endtime))
			$ex->duration = \go\core\util\DateTime::intervalToISO($values->endtime->getDateTime()
				->diff($ex->start ?? $values->exceptionstarttime));
		if (isset($values->location))
			$ex->location = $values->location;
		if (isset($values->busystatus))
			$ex->freeBusyStatus = empty($values->busystatus) ? 'free' : 'busy';
		if (isset($values->sensitivity))
			$ex->privacy = self::$privacyMap[$values->sensitivity];

		return (array)$ex;
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

}