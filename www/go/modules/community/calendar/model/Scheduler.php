<?php

namespace go\modules\community\calendar\model;

use go\core\mail\Address;
use go\core\mail\Attachment;
use go\core\util\DateTime;
use Sabre\VObject\Component\VCalendar;

class Scheduler {

	const EssentialScheduleProps = ['start', 'duration', 'location', 'title', 'description', 'showWithoutTime'];

	/**
	 * Send all the needed imip schedule messages
	 *
	 * @param CalendarEvent $event
	 * @parma bool $delete if the event is about to be deleted
	 */
	static public function handle(CalendarEvent $event, bool $willDelete = false) {

		$current = $event->calendarParticipant();

		if(empty($current) ||
			(!$event->isOrigin && !$current->isModified('participantionStatus'))) {
			return;
		}

		if ($current->isOwner()) {
			if($event->isRecurring()) {
				throw new \Exception('Need to implement scheduling recurring instances');
			}
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
		$ics = ICalendarHelper::toVObject($event, new VCalendar([
			'PRODID' => ICalendarHelper::PROD,
			'METHOD'=>'REPLY'
		]));

		$attachment = Attachment::fromString($ics->serialize(),'reply.ics', 'text/calendar;method=REPLY;charset=utf-8',Attachment::ENCODING_8BIT)
			->setInline(true);
		$subject = go()->t('Reply').': '.$event->title;
		$lang = go()->t('replyImipBody', 'community', 'calendar');

		$body = strtr($lang[$status], [
			'{name}' => $participant->name??'',
			'{title}' => $event->title,
			'{date}' => implode(' ',$event->humanReadableDate()),
		]);

		$success = go()->getMailer()->compose()
			->setSubject($subject)
			->setFrom($participant->email, $participant->name)
			//->setReplyTo($participant->email)
			->setTo(new Address($event->replyTo, !empty($organizer) ? $organizer->name : null))
			->attach($attachment)
			->setBody($body)
			->send();

		if(isset($old)) {
			go()->getLanguage()->setLanguage($old);
			unset($old);
		}

		return $success;
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
	 * o  For an existing "VEVENT" calendar component, change the role of
	 * "Organizer" to another CU.
	 *
	 * @return boolean
	 */
	private static function organizeImip(CalendarEvent $event, $method, $newOnly = false) {
		$success=true;
		$organizer = $event->calendarParticipant(); // must be organizer at this point
		foreach($event->participants as $participant) {
			/** @var $participant Participant */
			if(($newOnly && !$participant->isNew()) || $participant->isOwner() || $participant->scheduleAgent !== 'server')
				continue;

			if(!empty($participant->language)) {
				$old = go()->getLanguage()->setLanguage($participant->language);
			}

			$subject = go()->t($method=='REQUEST' ? 'Invitation' : 'Cancellation', 'community', 'calendar');
			if($participant->participationStatus !== Participant::NeedsAction) {
				$subject .= ' ('.go()->t('updated', 'community', 'calendar').')';
			}

			$ics = ICalendarHelper::toVObject($event, new VCalendar([
				'PRODID' => ICalendarHelper::PROD,
				'METHOD'=>$method
			]));


			$success = go()->getMailer()->compose()
					->setSubject($subject .': '.$event->title)
					->setFrom(go()->getSettings()->systemEmail, $organizer->name)
					->setReplyTo($organizer->email)
					->setTo(new Address($participant->email, $participant->name))
					->attach(Attachment::fromString($ics->serialize(),
						'invite.ics',
						'text/calendar;method='.$method.';charset=utf-8',Attachment::ENCODING_8BIT)
						->setInline(true)
					)
					->setBody(self::mailBody($event,$method,$participant,$subject), 'text/html')
					->send() && $success;

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


	static function processMessage($vcalendar, $receiver, $sender) {

		$vevent = $vcalendar->VEVENT[0];

		$existingEvent = CalendarEvent::findByUID($receiver, (string)$vevent->uid)->single();
//		if(!$existingEvent) {
//			return "You may not update this event";
//		}
		//$existingEvent = CalendarEvent::find()->where(['uid' => (string)$vevent->uid,'calendarId'=>$calendar->id])->single();

		switch($vcalendar->method){
			case 'REQUEST': return self::processRequest($vcalendar,$receiver,$existingEvent);
			case 'CANCEL': return self::precessCancel($vcalendar,$existingEvent);
			case 'REPLY': return self::processReply($vcalendar,$existingEvent, $sender);
		}
		return "invalid method ".$vcalendar->method;
	}

	private static function processRequest(VCalendar $vcalendar, $receiver, ?CalendarEvent $existingEvent) {
		if(!$existingEvent) {
			$existingEvent = new CalendarEvent();
			$existingEvent->isOrigin = false;
			$existingEvent->replyTo = str_replace('mailto:', '',(string)$vcalendar->VEVENT[0]->{'ORGANIZER'});
		}
		return CalendarEvent::grabInto(
			ICalendarHelper::parseVObject($vcalendar, $existingEvent),
			$receiver
		);
	}

	private static function precessCancel(VCalendar $vcalendar, ?CalendarEvent $existingEvent) {
		if($existingEvent) {
			if ($existingEvent->isRecurring()) {
				foreach($vcalendar->VEVENT as $vevent) {
					if(!empty($vevent->{'RECURRENCE-ID'})) {
						$recurId = $vevent->{'RECURRENCE-ID'}->getValue();
						$existingEvent->recurrenceOverrides[$recurId] = (new RecurrenceOverride($existingEvent))
							->setValues(['status' => CalendarEvent::Cancelled]);
					}
				}
			} else {
				$existingEvent->status = CalendarEvent::Cancelled;
			}
			$existingEvent->sequence = $vcalendar->SEQUENCE;
			$existingEvent->save();
		}
		return $existingEvent;
	}

	/**
	 * The message is a reply. This is for example an attendee telling an organizer he accepted the invite, or declined it.
	 */
	private static function processReply(VCalendar $vcalendar, ?CalendarEvent $existingEvent, $sender) {
		if(!$existingEvent) return "You may not update this event";

		$instances = [];
		$requestStatus = '2.0'; // OK

		foreach($vcalendar->VEVENT as $vevent) {
			$status = strtolower($vevent->ATTENDEE['PARTSTAT']->getValue());
			if (isset($vevent->{'REQUEST-STATUS'})) {
				$responseStatus = strtok((string)$vevent->{'REQUEST-STATUS'}, ";");
			}
			$item = $existingEvent;
			// occurrence
			if(isset($vevent->{'RECURRENCE-ID'})) {
				continue; // Implement processing replies for instances of recurrence overrides
				$recurId = $vevent->{'RECURRENCE-ID'}->getValue();
				if(!isset($item->recurrenceOverrides[$recurId])) {
					// TODO: check if the given RECURRENCE-ID is valid for $existingEvent->recurrenceRule
					$item->recurrenceOverrides[$recurId] = new RecurrenceOverride($item);
				}
				$item = $item->recurrenceOverrides[$recurId];

			}

			$replyStamp = $vevent->DTSTAMP->getDateTime();
			// base event
			$p = $item->participantByScheduleId($sender->email);
			if($p) {
				if(empty($p->scheduleUpdated) || $p->scheduleUpdated <= $replyStamp) {
					$p->participationStatus = $status;
					if(isset($responseStatus))
						$p->scheduleStatus = $responseStatus;
					$p->scheduleUpdated = new DateTime('@'.$replyStamp->getTimestamp());
				}
			} else { // add party crasher
				$item->participants[] = (new Participant($item))->setValues([
					'email' => $sender->email,
					'name' => $sender->name ?? $sender->email,
					'participationStatus' => $status,
					'scheduleUpdated' => $replyStamp,
				]);
			}
		}
		$existingEvent->save();
		return $existingEvent;
	}
}