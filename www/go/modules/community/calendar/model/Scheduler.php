<?php

namespace go\modules\community\calendar\model;

use Exception;
use go\core\ErrorHandler;
use go\core\exception\JsonPointerException;
use go\core\mail\Address;
use go\core\mail\Attachment;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;
use GO\Email\Model\ImapMessage;
use Sabre\VObject\Component\VCalendar;

class Scheduler {

	const EssentialScheduleProps = ['start', 'duration', 'location', 'title', 'description', 'showWithoutTime', 'recurrenceRule'];

	/**
	 * Send all the needed imip schedule messages
	 *
	 * @param CalendarEvent $event
	 * @parma bool $delete if the event is about to be deleted
	 */
	static public function handle(CalendarEvent $event, bool $willDelete = false) {

		if($event->isInPast())
			return;

		$current = $event->calendarParticipant();
		if(!empty($current) && ($willDelete || $event->isModified('participants') || $event->isModified(self::EssentialScheduleProps))) {
			if ($current->isOwner()) {
				$newOnly = !$willDelete && $event->isModified('participants') && !$event->isModified(self::EssentialScheduleProps);
				$method = $willDelete ? 'CANCEL' : 'REQUEST';
				self::organizeImip($event, $current, $method, $newOnly);
			} else if (!empty($event->replyTo) && $current->isModified('participationStatus')) {
				$status = $willDelete ? Participant::Declined : $current->participationStatus;
				self::replyImip($event, $current, $status);
			}
		}

		foreach ($event->overrides(true) as $exception) {
			self::handle($exception, $willDelete || $exception->excluded);
		}

	}

	private static function replyImip(CalendarEvent $event, $participant, $status) {

		$organizer = $event->organizer();
		if(!empty($participant->language)) {
			$old = go()->getLanguage()->setLanguage($participant->language);
		}

		$participant->participationStatus = $status;
		// needed so organizer can find last response
		$event->createdAt = new DateTime();
		$event->modifiedAt = new DateTime();
		$ics = ICalendarHelper::toInvite('REPLY', $event);

		// filter our other participants
		foreach($ics->vevent as $vevent) {
			if(isset($vevent->attendee)) {
				foreach($vevent->attendee as $a) {
					if('mailto:'.strtolower($participant->email) !== (string) $a) {
						$vevent->remove($a);
					}
				}
			}
		}

		$subject = go()->t('Reply').': '.$event->title;
		$lang = go()->t('replyImipBody', 'community', 'calendar');

		$body = strtr($lang[$status], [
			'{name}' => $participant->name??'',
			'{title}' => $event->title,
			'{date}' => implode(' ',$event->humanReadableDate()),
		]);

		$icsStr = $ics->serialize();

		$mailer = go()->getMailer($participant->email, $participant->name);
		$mailer->compose()
			->setSubject($subject)
			->setTo(new Address($event->replyTo, !empty($organizer) ? $organizer->name : null))
			->attach(Attachment::fromString($icsStr,'reply.ics', 'text/calendar;method=REPLY;charset=utf-8',Attachment::ENCODING_8BIT))
			->setBody(nl2br($body), 'text/html')
			->setAlternateBody($body)
			->setIcalendar($icsStr)
			->send();

		if(isset($old)) {
			go()->getLanguage()->setLanguage($old);
			unset($old);
		}

		return true;
	}

	/**
	 * The "REQUEST" method in a "VEVENT" component provides the following
	 * scheduling functions:
	 *
	 * o  Invite "Attendees" to an event.
	 * o  Reschedule an existing event.
	 * o  Response to a "REFRESH" request.
	 * o  Update the details of an existing event, without rescheduling it.
	 * o  Update the status of "Attendees" of an existing event, without rescheduling it.
	 * o  Reconfirm an existing event, without rescheduling it.
	 * o  Forward a "VEVENT" to another uninvited CU.
	 * o  For an existing "VEVENT" calendar component, delegate the role of "Attendee" to another CU.
	 * o  For an existing "VEVENT" calendar component, change the role of "Organizer" to another CU.
	 *
	 * @param $event CalendarEvent an instance that needs scheduling
	 * @param $method string 'REQUEST' or 'CANCEL' depending on what is happing to the event
	 * @param $newOnly boolean Only send if participant was added
	 * @return boolean
	 */
	private static function organizeImip(CalendarEvent $event, $organizer, $method, $newOnly = false) {
		$success=true;

		// allow lots of time for sending invites
		go()->getEnvironment()->setMaxExecutionTime(300);

		// This does not only build the ics file but also changes event to an occurence if an occurence was modified. A
		// participant could have been added as well.
		$ics = ICalendarHelper::toInvite($method,$event);

		$mailer = go()->getMailer($organizer->email, $organizer->name);

		foreach($event->participants as $participant) {
			/** @var $participant Participant */
			if(($newOnly && !$participant->isNew()) || $participant->isOwner())
				continue;

			if(!empty($participant->language)) {
				$old = go()->getLanguage()->setLanguage($participant->language);
			}

			$subject = go()->t($method=='REQUEST' ? ($participant->kind == 'resource' ? 'Resource request' : 'Invitation') : 'Cancellation', 'community', 'calendar');
			if($method==='REQUEST' && $participant->participationStatus !== Participant::NeedsAction) {
				$subject .= ' ('.go()->t('updated', 'community', 'calendar').')';
			}

			try {
				$msg = $mailer->compose();

				if($participant->kind !== 'resource') {
					$msg->attach(Attachment::fromString($ics->serialize(),
						'invite.ics',
						'text/calendar;method=' . $method . ';charset=utf-8', Attachment::ENCODING_8BIT)
					);
				}

				// Message will be sent after closing client connection to avoid timeouts.
				$msg->setSubject($subject . ': ' . $event->title)
						->setTo(new Address($participant->email, $participant->name))
						->setBody(self::mailBody($event, $method, $participant, $subject), 'text/html')
						->sendAfterResponse();

			} catch(\Exception $e) {
				go()->log($e->getMessage());
				$success=false;
			}
			if(isset($old)) {
				go()->getLanguage()->setLanguage($old);
				unset($old);
			}
		}
		return $success;
	}

	private static function mailBody($event, $method, Participant $participant, $title) {
		if(!$event) {
			return false;
		}
		ob_start();
		$url = '';
		if($method ==='REQUEST') {
			$url = go()->getAuthState()->getPageUrl().'/community/calendar/invite/'.$event->uid.'/'.$participant->expectReply(true);
		}
		include __DIR__.'/../views/imip.php';
		return ob_get_clean();
	}


	/**
	 * @param ImapMessage $imapMessage
	 * @param null $ifMethod
	 * @return array{method:string, feedback:string, event:CalendarEvent, scheduleId: int, status:string}|false
	 * @throws SaveException
	 * @throws JsonPointerException
	 * @throws \go\core\http\Exception
	 */
	static function handleIMIP(ImapMessage $imapMessage, $ifMethod=null): bool|array
	{
		// old framework sets user timezone :(
		date_default_timezone_set("UTC");

		$vcalendar = $imapMessage->getInvitationVcalendar();
		if(!$vcalendar) {
			return false;
		}
		$method = $vcalendar->method ? $vcalendar->method->getValue() : "NONE";
		if($ifMethod !== null && $ifMethod != $method) {
			return false;
		}
		$vevent = $vcalendar->VEVENT[0];

		$alreadyProcessed = false;
		$accountEmail = false;
		if($method ==='REPLY') {
			$uid =(string) $vevent->UID;
			// Find event data's replyTo by UID, we don't trust the organizer in the VEVENT
			$replyTo = go()->getDbConnection()->selectSingleValue('replyTo')->from('calendar_event')->where('uid', '=', $uid)->single();
			if($replyTo) {
				$userId = User::findIdByEmail($replyTo);
				if ($userId == $imapMessage->account->user_id) {
					$accountEmail = $replyTo;
				}
			}
		} else {
			if (isset($vevent->attendee)) {
				foreach ($vevent->attendee as $vattendee) {
					$attendeeEmail = str_replace('mailto:', '', strtolower((string)$vattendee));
					$userId = User::findIdByEmail($attendeeEmail);

					if ($userId == $imapMessage->account->user_id) {
						$accountEmail = $attendeeEmail;
					}
				}
			}
		}

		if (!$accountEmail || $method === 'NONE') {
			return [
				'method' => $method,
				'feedback' => $accountEmail ? "" : ($method ==='REPLY' ? go()->t('Event not found', "email") : go()->t('You are not invited to this event', "email")),
				'event' => ICalendarHelper::parseVObject($vcalendar, new CalendarEvent())
			];
		} else {
			try {
				$from = $imapMessage->from->getAddress();
				$event = Scheduler::processMessage($vcalendar, $imapMessage->account->user_id, (object)[
					'email' => $from['email'],
					'name' => $from['personal']
				], $alreadyProcessed);
			}catch(Exception $e) {
				ErrorHandler::logException($e, "Failed to process invitation");
				return ['method' => $method,
					'feedback' => $e->getMessage()];
			}
		}

		$itip = [
			'alreadyProcessed' => $alreadyProcessed,
			'method' => $method,
			'scheduleId' => $accountEmail,
			'event' => $event,
			'recurrenceId' => empty($vevent->{"RECURRENCE-ID"}) ? null : $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s')
		];
		if($method === 'REPLY' && isset($event)) {

			if (!empty($itip['recurrenceId'])) {
				$event = $event->patchedInstance($itip['recurrenceId']);
			}

			$p = $event->participantByScheduleId($from['email']);
			if ($p) {
				$lang = go()->t('replyImipBody', 'community', 'calendar');
				$itip['status'] = $p->participationStatus;
				$itip['feedback'] = strtr($lang[$p->participationStatus], [
					'{name}' => $p->name ?? '',
					'{title}' => $event->title,
					'{date}' => implode(' ', $event->humanReadableDate()),
				]);
			}

		}
		return $itip;
	}

	/**
	 * Will save the event to the calendar and return the calendar event.
	 *
	 * If it's a series it will return the occurrence where this message is about
	 *
	 * @param VCalendar $vcalendar
	 * @param int $userId
	 * @param object $sender
	 * @param bool $alreadyProcessed
	 * @return CalendarEvent|null
	 * @throws SaveException
	 */
	private static function processMessage(VCalendar $vcalendar, int $userId, object $sender, bool &$alreadyProcessed) : ?CalendarEvent{

		if(!isset($vcalendar->method)) {
			return null;
		}

		$method = $vcalendar->method->getValue();
		$event = self::eventByVEvent($vcalendar, $userId);

		if($event->isNew() && $method !== 'REQUEST')
			return null;
		switch($method){
			case 'REQUEST': return self::processRequest($vcalendar,$event, $alreadyProcessed);
			case 'CANCEL': return self::processCancel($vcalendar,$event, $alreadyProcessed);
			case 'REPLY': return self::processReply($vcalendar,$event, $sender, $alreadyProcessed);
		}
		go()->debug("invalid method " . $method);
		return null;
	}

	private static function eventByVEvent($vcalendar, $userId) {
		$vevent = $vcalendar->VEVENT[0];
		$uid = (string)$vevent->uid;
		$recurId = !empty($vevent->{'RECURRENCE-ID'}) ? $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s') : null;

		$existingEvent = CalendarEvent::findForUser($uid, $userId)
			->andWhere('recurrenceId','=', null)
			->single();

		// if the current user doesn't have the main event of a recurrence we might have it saved for a single recurrence ID
		if(!$existingEvent && $recurId !== null) {
			$existingEvent = CalendarEvent::findForUser($uid, $userId)
				->andWhere('recurrenceId','=', $recurId)->single();
		}

		if($existingEvent) {
			go()->debug("Found event ID" . $existingEvent->id);
			return $existingEvent;
		}

		go()->debug("NOT Found");

		// still not found. See if an event with the same UID exists in someone else its calendar and add it to ours
		$eventCalendars = go()->getDbConnection()->select(['t.eventId, GROUP_CONCAT(calendarId) as calendarIds'])
			->from('calendar_event', 't')
			->join('calendar_calendar_event', 'c', 'c.eventId = t.eventId', 'LEFT')
			->where(['uid'=>$uid, 'recurrenceId' => null])->single();
		if(!$eventCalendars && $recurId !== null) {
			// this happens when multiple users are invites to the same instance of a recurrence (but not too the series)
			// we are then reusing the single instance eventdata for multiple calendars.
			$eventCalendars = go()->getDbConnection()->select(['t.eventId, GROUP_CONCAT(calendarId) as calendarIds'])
				->from('calendar_event', 't')
				->join('calendar_calendar_event', 'c', 'c.eventId = t.eventId', 'LEFT')
				->where(['uid'=>$uid, 'recurrenceId' => $recurId])->single();
		}
		$calendarId = Calendar::fetchPersonal($userId);
		if(!$calendarId) {
			throw new Exception("No personal calendar yet");
		}

		if(!empty($eventCalendars['eventId'])) {
			// add it to the current receivers personal calendar
			go()->getDbConnection()->insertIgnore('calendar_calendar_event', [
				['calendarId'=>$calendarId, 'eventId'=>$eventCalendars['eventId']]
			])->execute();
			// if added then
			$event = CalendarEvent::findById(go()->getDbConnection()->getPDO()->lastInsertId());
			if($event) {
				return $event;
			}
		}

		$event = new CalendarEvent();
		$event->calendarId = $calendarId;
		$event->isOrigin = false;
		$event->replyTo = str_replace('mailto:', '',(string)$vcalendar->VEVENT[0]->{'ORGANIZER'});

		return $event;
	}

	private static function processRequest(VCalendar $vcalendar, ?CalendarEvent $event, bool &$alreadyProcessed) {
		if(!static::requestIsProcessed($vcalendar, $event)) {
			$event = ICalendarHelper::parseVObject($vcalendar, $event);
			if (!$event->save()) {
				throw new SaveException($event);
			}
		} else {
			$alreadyProcessed = true;
		}
		return $event;
	}

	/**
	 * // If the event already exists then We already processed the request. But it could be a REQUEST with an update
	 * // for a series' instance with a recurrence-id
	 *
	 * @param VCalendar $vcalendar
	 * @param CalendarEvent|null $event
	 * @return bool
	 */
	private static function requestIsProcessed(VCalendar $vcalendar, ?CalendarEvent $event) : bool {
		if($event->isNew() || $event->sequence < (isset($vcalendar->VEVENT[0]->SEQUENCE) ? (int)$vcalendar->VEVENT[0]->SEQUENCE->getValue() : 0)) {
			return false;
		}
		return true;
	}

	private static function processCancel(VCalendar $vcalendar, CalendarEvent $existingEvent, bool &$alreadyProcessed) : CalendarEvent {

		if ($existingEvent->isRecurring()) {
			foreach($vcalendar->VEVENT as $vevent) {
				if(!empty($vevent->{'RECURRENCE-ID'})) {
					$recurId = $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s');
					if(!isset($existingEvent->recurrenceOverrides[$recurId])) {
						$existingEvent->recurrenceOverrides[$recurId] = (new RecurrenceOverride($existingEvent));
					}
					$existingEvent->recurrenceOverrides[$recurId]->patchProps((object)['excluded' => true]);
				} else {
					$existingEvent->status = CalendarEvent::Cancelled;
				}
			}
		} else {
			$existingEvent->status = CalendarEvent::Cancelled;
		}
		if(isset($vcalendar->SEQUENCE)) {
			$existingEvent->sequence = (int) $vcalendar->SEQUENCE;
		}

		if(!$existingEvent->isModified())
		{
			$alreadyProcessed = true;
			return $existingEvent;
		}
		if(!$existingEvent->save()) {
			throw new SaveException($existingEvent);
		}
		return $existingEvent;
	}

	/**
	 * The message is a reply. This is for example an attendee telling an organizer he accepted the invite, or declined it.
	 */
	private static function processReply(VCalendar $vcalendar, CalendarEvent $existingEvent, $sender, bool &$alreadyProcessed) : CalendarEvent {

		foreach($vcalendar->VEVENT as $vevent) {
			if(!isset($vevent->ATTENDEE['PARTSTAT'])) {
				continue;
			}
			$status = strtolower($vevent->ATTENDEE['PARTSTAT']->getValue());

			$replyStamp = $vevent->DTSTAMP->getDateTime();

			// MAKE PATCH
			if(isset($vevent->{'RECURRENCE-ID'})) {// occurrence
				$recurId = $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s');

				$alreadyProcessed = !self::updateRecurrenceStatus($existingEvent, $recurId, $sender->email, $status, $replyStamp);
				if($alreadyProcessed) {
					return $existingEvent;
				}

			} else {
				// APPLY EVENT
				$p = $existingEvent->participantByScheduleId($sender->email);
				if (!$p) continue; // no party crashers
				if (empty($p->scheduleUpdated) || $p->scheduleUpdated < $replyStamp) {
					$p->participationStatus = $status;
					$p->scheduleUpdated = new DateTime($replyStamp->format("Y-m-d H:i:s"), $replyStamp->getTimezone());
				} else {
					$alreadyProcessed = true;
					return $existingEvent;
				}
			}
		}

		if(!$existingEvent->save()) {
			throw new SaveException($existingEvent);
		}
		return $existingEvent;
	}


	/**
	 * Update participant status in a recurring series instance
	 *
	 * @param CalendarEvent $existingEvent
	 * @param string $recurId
	 * @param string $email
	 * @param string $status
	 * @param \DateTimeInterface $replyStamp
	 * @return bool True if a modification was made. False if already up to date.
	 * @throws JsonPointerException
	 */
	public static function updateRecurrenceStatus(CalendarEvent $existingEvent, string $recurId, string $email, string $status, \DateTimeInterface $replyStamp): bool
	{
		if(!isset($existingEvent->recurrenceOverrides[$recurId])) {
			// TODO: check if the given RECURRENCE-ID is valid for $existingEvent->recurrenceRule
			// If it is not valid an extra instance would be created (RDATE in iCal) GroupOffice does not display these at the moment.
			$existingEvent->recurrenceOverrides[$recurId] = new RecurrenceOverride($existingEvent);
			$exEvent = $existingEvent->patchedInstance($recurId);
		} else {
			$exEvent = $existingEvent->patchedInstance($recurId);
		}

		$hasModification = false;
		if( isset($exEvent->participants)) {
			$modifiedParticipants = $exEvent->participants;
			$modified = false;
			foreach ($modifiedParticipants as &$p) {
				if ($p->email != $email) {
					continue;
				}
				if (empty($p->scheduleUpdated) || $p->scheduleUpdated < $replyStamp) {

					$p->scheduleUpdated = new DateTime($replyStamp->format("Y-m-d H:i:s"), $replyStamp->getTimezone());;
					$p->participationStatus = $status;

					$hasModification = true;
					$modified = true;
				}
			}
			if ($modified) {
				$existingEvent->recurrenceOverrides[$recurId]->patchProps(
					(object)['participants' => $modifiedParticipants]
				);
			}
		}
		return $hasModification;
	}
}