<?php

use go\core\mail\Util;
use go\core\model\Principal;
use go\modules\community\calendar\model\Alert;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\calendar\model\Participant;
use go\modules\community\calendar\model\RecurrenceOverride;
use go\core\util\DateTime;

class CalendarConvertor
{
	static $meetingResponseMap = [
		1 => Participant::Accepted,
		2 => Participant::Tentative,
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
	 * @param SyncAppointment|null $exception
	 * @param $params
	 * @return SyncAppointment
	 * @throws DateInvalidTimeZoneException
	 * @throws DateMalformedStringException
	 */
	static function toSyncAppointment(CalendarEvent $event, ?SyncAppointment $exception, $params): SyncAppointment
	{
		$message = $exception ?? new SyncAppointment();
		if(!$exception && !empty($event->timeZone))
			$message->timezone =  self::mstzFromTZID($event->timeZone);
		$message->alldayevent = empty($event->showWithoutTime) ? 0 : 1;
		if(!empty($event->createdAt))
			$message->dtstamp = $event->createdAt->getTimestamp();
		$message->starttime = $event->start()->getTimestamp();
		$message->subject = $event->title;
		$message->uid = $event->uid;
		$message->location = $event->location;
		if(!empty($event->duration))
		$message->endtime = $event->end()->getTimeStamp();
		if(!empty($event->privacy))
		$message->sensitivity = ['public'=> '0', 'private' => '2', 'secret'=> '3'][$event->privacy];
		if(!empty($event->freeBusyStatus))
		$message->busystatus = $event->freeBusyStatus == 'busy' ? "2" : "0";
		$message->meetingstatus = 0;
		if (!empty($event->participants)) {
			$calPart = $event->calendarParticipant();
			$message->meetingstatus = $event->status == CalendarEvent::Cancelled ? 5 : 1;
			self::toSyncAttendee($event, $message, $calPart);
			// check if user is not organizer but attendee to detect received meeting
			if($calPart && !$calPart->isOwner()) {
				// apply received flag
				$message->meetingstatus |= 0x2; // IS NOT ORANIZER
				//iphone uses busy status for events.
				$stat = array_search($calPart->participationStatus, self::$meetingResponseMap);
				if($stat !== false) {
					$message->busystatus = $stat;
				}
			}
		}


		$message->asbody = GoSyncUtils::createASBody($event->description, $params);


		if($event->isRecurring()) {
			$message->recurrence = self::toSyncRecurrence($event);
			if(!empty($event->recurrenceOverrides)) {
				$message->exceptions = [];
				foreach ($event->recurrenceOverrides as $recurrenceId => $override) {
					$msgException = new SyncAppointmentException();
					if ($override->excluded) {
						$msgException->deleted = 1;
					} else {
						$exEvent = $event->patchedInstance($recurrenceId);// (new CalendarEvent())->setValues($override->toArray());
						$exEvent->uid = $event->uid; // required
						$exEvent->timeZone = $event->timeZone;
						if(empty($exEvent->start)) {
							$exEvent->start = new DateTime($override->recurrenceId);
						}
						if(empty($exEvent->duration)) {
							$exEvent->duration = $event->duration; // z-push needs this for its compare to endtime check to pass.
						}
						$msgException = self::toSyncAppointment($exEvent, $msgException, $params);
					}
					$msgException->exceptionstarttime = (new DateTime($recurrenceId, new \DateTimeZone($event->timeZone)))->getTimestamp();
					$message->exceptions[] = $msgException;
				}
			}
		}


		if (empty($exception) && $message->meetingstatus > 0) {
			// set meetingstatus if not already set
			$message->meetingstatus = isset($message->meetingstatus) ? $message->meetingstatus : 1;
			// check if meeting has an organizer set, otherwise fallback to current user
			if (!isset($message->organizeremail)) {
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCalDAV->toSyncAppointment(): No organizeremail defined, using user details"));
				$u = go()->getAuthState()->getUser(['displayName', 'email']);
				$message->organizeremail = $u->email;
				$message->organizername = $u->displayName;
			}
			// Ensure the organizer name is set
			if (!isset($message->organizername)) {
				$message->organizername = Utils::GetLocalPartFromEmail($message->organizeremail);
			}
		}
		//$message->reminder = 0; // timestamp or 0
		if(!empty($event->alerts)) {
			$firstAlert = array_shift($event->alerts);

			$coreAlert = $firstAlert->buildCoreAlert();
			if($coreAlert) {
				$triggerU = $coreAlert->triggerAt->format("U");
				$message->reminder = ($message->starttime - $triggerU) / 60; // Reminder is in minutes before start

				if($message->reminder < 0) {
					//iphone and GO allows a reminder after the start time when using an all day event.
					//EAS does not support this. We'll set a reminder at 9:00 the day before so it's not
					//completely lost.
					if($event->showWithoutTime) {
						$message->reminder = 900;
					} else {
						$message->reminder = 30;
					}
				}
			}
		}
		return $message;
	}

	public static function mstzFromTZID($timeZone) {
		$msId = str_replace('.','',intltz_get_windows_id($timeZone));
		$tz = TimezoneUtil::GetFullTZFromTZName($msId);
		return base64_encode(TimezoneUtil::GetSyncBlobFromTZ($tz));
	}

	private static function toSyncAttendee(CalendarEvent $event, &$message, $me = null) {

		$message->organizername = '';
		if(!empty($event->replyTo))
			$message->organizeremail = $event->replyTo;

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
			$recur->until = strtotime($rule->until);
		if(!empty($rule->count))
			$recur->occurrences = $rule->count;

		switch ($rule->frequency) {
			case 'daily':
				$recur->type = "0";
				break;
			case 'weekly':
				$recur->type = "1";
				$recur->dayofweek = self::nDayToAS($rule->byDay??[], $event->start());
				break;
			case 'monthly':
				if (isset($rule->byDay[0])) {
					$recur->type = "3";
					$recur->weekofmonth = $rule->bySetPosition ?? $rule->byDay[0]->nthOfPeriod;
					if($recur->weekofmonth == -1) { // last of month is supported by EAS but using 5.
						$recur->weekofmonth = 5;
					}
					$recur->dayofweek = self::nDayToAS($rule->byDay??[], $event->start());
				} else {
					$recur->dayofmonth = $event->start()->format('j');
					$recur->type = "2";
				}
				break;
			case 'yearly':
				$recur->type = "5";
				$recur->monthofyear = $event->start()->format('n');
				$recur->dayofmonth = $event->start()->format('j');
				break;
		}

		return $recur;
	}

	private static function nDayToAS($ndays, \DateTime $start) {
		static $dayOfWeekBits = ['su'=>0x01,'mo'=>0x02,'tu'=>0x04,'we'=>0x08,'th'=>0x10,'fr'=>0x20,'sa'=>0x40];
		// add the start day os this is required
		$r = $dayOfWeekBits[strtolower(substr($start->format('l'),0,2))];
		if($ndays) {
			foreach ((array)$ndays as $nday) {
				$r |= $dayOfWeekBits[strtolower($nday->day)];
			}
		}
		return $r;
	}


	/**
	 * PHONE => SERVER
	 * @param SyncAppointment $message
	 * @param CalendarEvent $event
	 * @return CalendarEvent
	 * @throws DateMalformedStringException
	 */
	static function toCalendarEvent(SyncAppointment $message, CalendarEvent $event) : CalendarEvent
	{
		if (isset($message->uid))
			$event->uid = $message->uid;
		//if (!empty($message->timezone))
			$event->timeZone = self::tzid(); // ActiveSync timezone is guessable but for now we expect it to be the same as in GroupOffice
		if ($event->isNew())
			$event->createdAt = new DateTime('@'.$message->dtstamp);
		$event->showWithoutTime = $message->alldayevent;
		$event->title = $message->subject ?? "No subject";
		$event->description = GoSyncUtils::getBodyFromMessage($message);
		$dtstart = new DateTime('@'.(isset($message->starttime) ? $message->starttime : $message->dtstamp), new \DateTimeZone('etc/UTC'));
		if (isset($message->endtime)) {
			$dtend = new DateTime('@' . $message->endtime, new \DateTimeZone('etc/UTC'));
			$event->duration = DateTime::intervalToISO($dtend->diff($dtstart));
		}
		if($event->timeZone && !$event->showWithoutTime) {
			$dtstart->setTimezone($event->timeZone());
		}

		if($event->showWithoutTime) {
			// times for all day event are in UTC. For example in NL the day starts at the day before 23:00 in UTC time.
			// we have to set the local timezone to get the correct date
			$dtstart->setTimezone(new DateTimeZone($event->timeZone));
			$dtend->setTimezone(new DateTimeZone($event->timeZone));
		}

		$event->start = $dtstart;
		if (isset($message->location))
			$event->location = $message->location;
		if (isset($message->busystatus))
			$event->freeBusyStatus = empty($message->busystatus) ? 'free' : 'busy';
		if (isset($message->sensitivity))
			$event->privacy = self::$privacyMap[$message->sensitivity];

		$principal = Principal::currentUser();

		if (isset($message->attendees)) {
			if($event->isNew() || !$event->organizer()) {
				$organizer = $event->generatedOrganizer($principal);
				if (!empty($message->organizeremail) && Util::validateEmail($message->organizeremail)) $organizer->email = $message->organizeremail;
				if (!empty($message->organizername)) $organizer->name = $message->organizername;
			}

			foreach ($message->attendees as $attendee) {
				$key = $attendee->email;
				if(!Util::validateEmail($key))
					continue; // do not att attendee if client does not send a valid email address (TBSync uses login name)
				$principalId = Principal::find()->selectSingleValue('id')->where('email','=',$key)->orderBy(['entityTypeId'=>'ASC'])->single();
				if(!isset($event->participants[$principalId ?? $key])) {
					$p = new Participant($event);
					$p->email = $attendee->email;
					$p->name = $attendee->name;
					$p->expectReply = true;
					$p->kind = Participant::Individual; // todo: read from $attendee->attendeetype
					$event->participants[$principalId ?? $key] = $p;
				} else {
					$p = &$event->participants[$principalId ?? $key];
				}

				if(isset($attendee->attendeestatus)) {
					$newStatus = array_search($attendee->attendeestatus, self::$participationStatusMap);
					if($newStatus !== false) {
						$p->participationStatus = $newStatus;
					}
				}
			}
		}

		if (isset($message->recurrence)) {
			$event->setRecurrenceRule(self::toRecurrenceRule($message->recurrence));
		}
		if (isset($message->exceptions)) {
			$event->recurrenceOverrides = []; // empty first to delete what is not present
			foreach ($message->exceptions as $v) {
				$rDT = new DateTime('@'.$v->exceptionstarttime, new \DateTimeZone('etc/UTC'));
				if($event->timeZone && !$event->showWithoutTime) {
					$rDT->setTimezone($event->timeZone());
				}
				$recurrenceId = $rDT->format('Y-m-d\TH:i:s');
				if(!isset($event->recurrenceOverrides[$recurrenceId])) {
					$event->recurrenceOverrides[$recurrenceId] = (new RecurrenceOverride($event));
				}
				$event->recurrenceOverrides[$recurrenceId]->patchProps(self::toOverride($v, $event));
			}
		}

		if (isset($message->reminder)){
			$event->alerts = [(new Alert($event))->setValues([
				'action' => 'display',
				'trigger' => ['offset' => '-PT'.$message->reminder.'M', 'relativeTo' => 'start']
			])];
		}

		return $event;
	}

	private static function tzid(): string
	{
		return go()->getAuthState()->getUser(['timezone'])->timezone;
	}

	static private function toRecurrenceRule(SyncRecurrence $recur): object
	{
		static $recurType = [0=>'daily',1=>'weekly',2=>'monthly',3=>'monthly',4=>'yearly',5=>'yearly'];
		$r = (object)['frequency'=>$recurType[$recur->type]];
		if(!empty($recur->interval) && $recur->interval !== '1') // 1 = default
			$r->interval = (int)$recur->interval;
		if (!empty($recur->until)) {

			$untilDT = new DateTime('@'.$recur->until, new \DateTimeZone('etc/UTC'));
			$untilDT->setTimezone(new DateTimeZone(self::tzid()));
			$r->until = $untilDT->format('Y-m-d\TH:i:s');
		}
		if(!empty($recur->dayofweek))
			$r->byDay = self::aSync2Nday($recur->dayofweek);

		if (!empty($recur->weekofmonth))
			$r->bySetPosition = $recur->weekofmonth == 5 ? -1 : $recur->weekofmonth;
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
	 * @param SyncAppointmentException $values
	 * @param CalendarEvent $event
	 * @return stdClass
	 * @throws DateMalformedStringException
	 */
	private static function toOverride(SyncAppointmentException $values, CalendarEvent $event): stdClass
	{
		if($values->deleted)
			return (object) ['excluded' => true];
		$ex = new \stdClass;
		if(isset($values->subject))
			$ex->title = $values->subject;
		$description = GoSyncUtils::getBodyFromMessage($values);
		if(!empty($description))
			$ex->description = $description;
		if(isset($values->starttime))
			$ex->start = new DateTime('@'.$values->starttime, new \DateTimeZone('etc/UTC'));
		if (isset($values->endtime)) {
			// timezone needs to be the same for correct duration of exception
			$ex->duration = DateTime::intervalToISO((new DateTime('@' . $values->endtime, new \DateTimeZone('etc/UTC')))
				->diff($ex->start ?? new DateTime('@' . $values->exceptionstarttime, new \DateTimeZone('etc/UTC'))));
		}
		if (isset($values->location))
			$ex->location = $values->location;
		if (isset($values->busystatus))
			$ex->freeBusyStatus = empty($values->busystatus) ? 'free' : 'busy';
		if (isset($values->sensitivity))
			$ex->privacy = self::$privacyMap[$values->sensitivity];
		if(isset($ex->start)) {
			$ex->start->setTimezone($event->timeZone());
			$ex->start = $ex->start->format('Y-m-d\TH:i:s');
		}
		return $ex;
	}

}