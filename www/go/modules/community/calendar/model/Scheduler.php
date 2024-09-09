<?php

namespace go\modules\community\calendar\model;

use Exception;
use go\core\mail\Address;
use go\core\mail\Attachment;
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

		// TODO: Series has no participants but override does?!?
		if(empty($event->participants) || $event->isInPast())
			return;

		$current = $event->calendarParticipant();

		if(empty($current) ||
			(!$event->isOrigin && !$current->isModified('participantionStatus'))) {
			return;
		}

		if ($current->isOwner()) {
			$newOnly = !$willDelete && $event->isModified('participants') && !$event->isModified(self::EssentialScheduleProps);
			self::organizeImip($event, $willDelete ? 'CANCEL': 'REQUEST', $newOnly);
		} else if(!empty($event->replyTo)) { // !$event->isOrigin

			$status = $willDelete ? Participant::Declined : $current->participationStatus;
			self::replyImip($event, $status);
			//$title = ucfirst($status);
		}

	}

	private static function replyImip(CalendarEvent $event, $status) {

		$participant = $event->calendarParticipant();
		$organizer = $event->organizer();
		if(!empty($participant->language)) {
			$old = go()->getLanguage()->setLanguage($participant->language);
		}

		$participant->participationStatus = $status;
		// needed so organizer can find last response
		$event->createdAt = new DateTime();
		$event->modifiedAt = new DateTime();
		$ics = ICalendarHelper::toInvite('REPLY', $event);
		$subject = go()->t('Reply').': '.$event->title;
		$lang = go()->t('replyImipBody', 'community', 'calendar');

		$body = strtr($lang[$status], [
			'{name}' => $participant->name??'',
			'{title}' => $event->title,
			'{date}' => implode(' ',$event->humanReadableDate()),
		]);

		go()->getMailer()->compose()
			->setSubject($subject)
			->setFrom($participant->email, $participant->name)
			//->setReplyTo($participant->email)
			->setTo(new Address($event->replyTo, !empty($organizer) ? $organizer->name : null))
			->attach(Attachment::fromString($ics->serialize(),'reply.ics', 'text/calendar;method=REPLY;charset=utf-8',Attachment::ENCODING_8BIT))
			->setBody($body)
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
	private static function organizeImip(CalendarEvent $event, $method, $newOnly = false) {
		$success=true;


		// This does not only build the ics file but also changes event to an occurence if an occurence was modified. A
		// participant could have been added as well.
		$ics = ICalendarHelper::toInvite($method,$event);

		$organizer = $event->calendarParticipant(); // must be organizer at this point
		foreach($event->participants as $participant) {
			/** @var $participant Participant */
			if(($newOnly && !$participant->isNew()) || $participant->isOwner() || $participant->scheduleAgent !== 'server')
				continue;

			if(!empty($participant->language)) {
				$old = go()->getLanguage()->setLanguage($participant->language);
			}

			$subject = go()->t($method=='REQUEST' ? ($participant->kind == 'resource' ? 'Resource request' : 'Invitation') : 'Cancellation', 'community', 'calendar');
			if($method==='REQUEST' && $participant->participationStatus !== Participant::NeedsAction) {
				$subject .= ' ('.go()->t('updated', 'community', 'calendar').')';
			}



			try {
				go()->getMailer()->compose()
						->setSubject($subject . ': ' . $event->title)
						->setFrom(go()->getSettings()->systemEmail, $organizer->name)
						->setReplyTo($organizer->email)
						->setTo(new Address($participant->email, $participant->name))
						->attach(Attachment::fromString($ics->serialize(),
							'invite.ics',
							'text/calendar;method=' . $method . ';charset=utf-8', Attachment::ENCODING_8BIT)
							//->setInline(true)
						)
						->setBody(self::mailBody($event, $method, $participant, $subject), 'text/html')
						->send();
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

	private static function mailBody($event, $method, $participant, $title) {
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
	 * @param $ifMethod
	 * @return array{method:string, feedback:string, event:CalendarEvent, scheduleId: int, status:string}|false
	 * @throws \go\core\http\Exception
	 *
	 */
	static function handleIMIP(ImapMessage $imapMessage, $ifMethod=null) {
		$vcalendar = $imapMessage->getInvitationVcalendar();
		if(!$vcalendar) {
			return false;
		}
		$method = $vcalendar->method ? $vcalendar->method->getValue() : "REQUEST";
		if($ifMethod !== null && $ifMethod != $method) {
			return false;
		}
		$vevent = $vcalendar->VEVENT[0];

		$aliases = \GO\Email\Model\Alias::model()->find(
			\GO\Base\Db\FindParams::newInstance()
				->select('email')
				->criteria(\GO\Base\Db\FindCriteria::newInstance()->addCondition('account_id', $imapMessage->account->id))
		)->fetchAll(\PDO::FETCH_COLUMN, 0);

		// for case insensitive match
		$aliases = array_map('strtolower', $aliases);

		$accountEmail = false;
		if($method ==='REPLY') {
			if (isset($vevent->ORGANIZER)) {
				$attendeeEmail = str_replace('mailto:', '', strtolower((string)$vevent->ORGANIZER));
				if (in_array($attendeeEmail, $aliases)) {
					$accountEmail = $attendeeEmail;
				}
			} else { // Find event data's replyTo by UID when organizer is missing in VEVENT
				$replyTo = go()->getDbConnection()->selectSingleValue('replyTo')->from('calendar_event')->where('uid', '=', (string) $vevent->UID)->single();
				if(in_array($replyTo, $aliases)) {
					$accountEmail = $replyTo;
				}
//				// Microsoft ActiveSync does not (always?) send organizer
//				$existingEvent = CalendarEvent::findByUID((string) $vevent->uid)->single();
//				if ($existingEvent && in_array($existingEvent->organizer()->email, $aliases)) {
//					$accountEmail = $existingEvent->organizer()->email;
//				}
			}
		} else {
			if (isset($vevent->attendee)) {
				foreach ($vevent->attendee as $vattendee) {
					$attendeeEmail = str_replace('mailto:', '', strtolower((string)$vattendee));
					if (in_array($attendeeEmail, $aliases)) {
						$accountEmail = $attendeeEmail;
					}
				}
			}
		}

		if (!$accountEmail) {
			return ['method' => $method, 'event' => go()->t("None of the participants match your e-mail aliases for this e-mail account.", "email")];
		}
		$from = $imapMessage->from->getAddress();
		$event = Scheduler::processMessage($vcalendar, $accountEmail, (object)[
			'email'=>$from['email'],
			'name'=>$from['personal']
		]);


		$itip = [
			'method' => $method,
			'scheduleId' => $accountEmail,
			'event' => $event
		];
		if($method ==='REPLY' && isset($event)) {
			$p = $event->participantByScheduleId($from['email']);
			if($p) {
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

	static function processMessage(VCalendar $vcalendar, string $receiver, object $sender) : ?CalendarEvent{

		$vevent = $vcalendar->VEVENT[0];

		$existingEvent = CalendarEvent::findByUID((string)$vevent->uid, $receiver)->single();
		// If the existing event has isOrigin=true all below does is add new REQUESTS to the calendar.
		// We might do that up front and skip all the below processing instead.



		switch($vcalendar->method->getValue()){
			case 'REQUEST': return self::processRequest($vcalendar,$receiver,$existingEvent);
			case 'CANCEL': return $existingEvent ? self::processCancel($vcalendar,$existingEvent) : null;
			case 'REPLY': return $existingEvent ? self::processReply($vcalendar,$existingEvent, $sender) : null;
		}
		go()->debug("invalid method ".$vcalendar->method);
		return null;
	}

	private static function processRequest(VCalendar $vcalendar, $receiver, ?CalendarEvent $existingEvent) {
		if(!$existingEvent) {
			$existingEvent = new CalendarEvent();
			$existingEvent->isOrigin = false;
			$existingEvent->replyTo = str_replace('mailto:', '',(string)$vcalendar->VEVENT[0]->{'ORGANIZER'});
		}
		$calId = Calendar::fetchDefault($receiver);
		$event = ICalendarHelper::parseVObject($vcalendar, $existingEvent);
		foreach($event->participants as $p) {
			if($p->email == $receiver && $p->kind == 'resource') {
				return $event; // Do not put the event in the resource admin its calendar
			}
		}
		return Calendar::addEvent($event, $calId);
	}

	private static function processCancel(VCalendar $vcalendar, CalendarEvent $existingEvent) : CalendarEvent {

		if ($existingEvent->isRecurring()) {
			foreach($vcalendar->VEVENT as $vevent) {
				if(!empty($vevent->{'RECURRENCE-ID'})) {
					$recurId = $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s');
					if(!isset($existingEvent->recurrenceOverrides[$recurId])) {
						$existingEvent->recurrenceOverrides[$recurId] = (new RecurrenceOverride($existingEvent));
					}
					$existingEvent->recurrenceOverrides[$recurId]->setValues(['status' => CalendarEvent::Cancelled]);
				} else {
					$existingEvent->status = CalendarEvent::Cancelled;
				}
			}
		} else {
			$existingEvent->status = CalendarEvent::Cancelled;
		}
		if(isset($vcalendar->SEQUENCE)) {
			$existingEvent->sequence = $vcalendar->SEQUENCE;
		}
		$success = $existingEvent->save();


		return $existingEvent;
	}

	/**
	 * The message is a reply. This is for example an attendee telling an organizer he accepted the invite, or declined it.
	 */
	private static function processReply(VCalendar $vcalendar, CalendarEvent $existingEvent, $sender) : CalendarEvent {

		foreach($vcalendar->VEVENT as $vevent) {
			if(!isset($vevent->ATTENDEE['PARTSTAT'])) {
				continue;
			}
			$status = strtolower($vevent->ATTENDEE['PARTSTAT']->getValue());
//			if (isset($vevent->{'REQUEST-STATUS'})) {
//				$responseStatus = strtok((string)$vevent->{'REQUEST-STATUS'}, ";");
//			}

			$replyStamp = $vevent->DTSTAMP->getDateTime();

			// MAKE PATCH
			if(isset($vevent->{'RECURRENCE-ID'})) {// occurrence
				$recurId = $vevent->{'RECURRENCE-ID'}->getValue();
				if(!isset($existingEvent->recurrenceOverrides[$recurId])) {
					// TODO: check if the given RECURRENCE-ID is valid for $existingEvent->recurrenceRule
					// If it is not valid an extra instance would be created (RDATE in iCal) GroupOffice does not display these at the moment.
					$existingEvent->recurrenceOverrides[$recurId] = new RecurrenceOverride($existingEvent);
					$p = $existingEvent->participantByScheduleId($sender->email);
				} else {
					$exEvent = $existingEvent->copyPatched($existingEvent->recurrenceOverrides[$recurId], $recurId);
					$p = $exEvent->participantByScheduleId($sender->email);
				}
				if(!$p) continue;
				if (empty($p->scheduleUpdated) || $p->scheduleUpdated < $replyStamp) {
					$k = 'participants/'.$p->pid();
					$existingEvent->recurrenceOverrides[$recurId]->patchProps([
						$k.'/participationStatus' => $status,
						$k.'/scheduleUpdated' => $replyStamp->format("Y-m-d\TH:i:s"),
					]);
				}

			} else {
				// APPLY EVENT
				$p = $existingEvent->participantByScheduleId($sender->email);
				if (!$p) continue; // no party crashers
				if (empty($p->scheduleUpdated) || $p->scheduleUpdated < $replyStamp) {
					$p->participationStatus = $status;
					$p->scheduleUpdated = new DateTime($replyStamp->format("Y-m-d H:i:s"), $replyStamp->getTimezone());
//					if (isset($responseStatus))
//						$p->scheduleStatus = $responseStatus;
				}
			}
		}

		$existingEvent->save();
		return $existingEvent;
	}
}