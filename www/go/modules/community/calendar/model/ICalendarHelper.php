<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace GO\Modules\GroupOffice\Calendar\Model;

use Sabre\VObject;
use GO\Core\Email\Model\Message;
use Sabre\VObject\Recur\RRuleIterator;

class ICalendarHelper {

	/**
	 * Parse an Event object to a VObject
	 * @param Event $event
	 * @param VObject\Component\VCalendar $vcalendar The original vcalendar to sync to
	 */
	static public function toVObject(Event $event, $vcalendar = null) {

		if($vcalendar === null) {
			$vcalendar = new VObject\Component\VCalendar([
				'VEVENT' => [
					'UID' => $event->uuid,
					'SUMMARY' => $event->title,
					'STATUS' => EventStatus::$text[$event->status],
					'LAST-MODIFIED' => $event->modifiedAt, // @todo: check if datetime must be UTC
					'DTSTAMP' => $event->createdAt,
					'DTSTART' => $event->startAt,
					'DTEND' => $event->endAt,
				]
			]);
		}
		if($event->allDay) {
			$vcalendar->VEVENT->DTSTART['VALUE'] = 'DATE';
			$vcalendar->VEVENT->DTEND['VALUE'] = 'DATE';
		}

		// Sequence is for updates on the event its used for ITIP
		!isset($event->sequence) ?: $vcalendar->VEVENT->SEQUENCE = $event->sequence; // @todo implement in Event
		empty($event->description) ?: $vcalendar->VEVENT->DESCRIPTION = $event->description;
		empty($event->location) ?: $vcalendar->VEVENT->LOCATION = $event->location;
		empty($event->tag) ?: $vcalendar->VEVENT->CATEGORIES = $event->tag;
		($event->visibility === 2) ?: $vcalendar->VEVENT->CLASS = Visibility::$text[$event->visibility];

		foreach($event->attendees as $attendee) {
			$attr = ['cn' => $attendee->getName()];
			($attendee->responseStatus === AttendeeStatus::__default) ?: $attr['partstat'] = AttendeeStatus::$text[$attendee->responseStatus];
			($attendee->role === Role::__default) ?: $attr['role'] = Role::$text[$attendee->role];

			$vcalendar->VEVENT->add(
				$event->organizerEmail == $attendee->email ? 'ORGANIZER' : 'ATTENDEE',
				$attendee->email, $attr
			);
		}
		//@todo: VALARMS depend on who fetched the event. Need to be implemented when caldav is build
		//@todo: ATTACHMENT Files

		if($event->getIsRecurring()) {
			$vcalendar->VEVENT->RRULE = self::createRrule($event->recurrenceRule);
			foreach($event->recurrenceRule->exceptions as $exception) {
				if($exception->isRemoved) {
					$vcalendar->VEVENT->add('EXDATE',$exception->recurrenceId);
				} else {
					$vcalendar->add('VEVENT',$exception->toVEVENT());
				}
			}
		}

		return $vcalendar;
	}

	/**
	 * Parse a VObject to an Event object
	 * @param VObject\Component\VCalendar $vcalendar
	 * @param int $calendarId
	 * @return Event updated or new Event if not found
	 */
	static public function fromVObject(VObject\Component\VCalendar $vcalendar, $calendarId) {
		
		$calendar = Calendar::findByPk($calendarId);
		$emailId = $calendar->owner->getEmail();
		$groupId = $calendar->ownedBy;

		$mainEvents = [];
		$exceptions = [];

		foreach($vcalendar->VEVENT as $vevent) {
			$uid = (string)$vevent->UID;
			if(!empty($vevent->{'RECURRENCE-ID'})) {
				$exceptions[] = self::parseException($vevent);
			}
			$event = Event::findByUUID($uid);
			if(empty($event)) {
				$event = new Event();
				$event->uuid = $uid;
			}
			$mainEvents[$uid] = $event;

			$event->createdAt = $vevent->DTSTAMP->getDateTime();
			$event->modifiedAt = $vevent->{'LAST-MODIFIED'}->getDateTime();
			$event->startAt = $vevent->DTSTART->getDateTime();
			$event->endAt = $vevent->DTEND->getDateTime();
			$event->allDay = !$vevent->DTSTART->hasTime();
			$event->status = EventStatus::fromText($vevent->STATUS);
			$event->title = (string)$vevent->SUMMARY;
			$event->description = (string)$vevent->DESCRIPTION;
			$event->location = (string)$vevent->LOCATION;
			$event->vevent = $vevent->serialize();
			$event->sequence = (string)$vevent->SEQUENCE;
			$event->visibility = Visibility::fromText($vevent->CLASS);

			$event->organizerEmail = str_replace('mailto:', '',(string)$vevent->ORGANIZER);
			$event->attendees[] = self::parseAttendee($vevent->ORGANIZER, [$emailId, $calendarId, $groupId]);
			foreach($vevent->ATTENDEE as $vattendee) {
				$event->attendees[] = self::parseAttendee($vattendee, [$emailId, $calendarId, $groupId]);
			}
			//TODO VALARM for (attendee specific)
			if(!empty((string)$vevent->RRULE)) {
				$event->recurrenceRule = self::rruleToObject($vevent->RRULE);
				foreach($vevent->EXDATE as $exdate) {
					$event->recurrenceRule->addException($exdate->getDateTime());
				}
			}
		}
		//Attach exceptions found in VCALENDAR
		foreach($exceptions as $props) {
			$uid = $props['uid'];
			if(isset($mainEvents[$uid]) && $mainEvents[$uid]->getIsRecurring()) {
				unset($props['uid']);
				$recurrenceId = $props['recurrenceId'];
				$mainEvents[$uid]->recurrenceRule->addException($recurrenceId, $props);
			}
		}

		$vcalendar->destroy();
		return $mainEvents;
	}

	static private function parseAttendee($vattendee, $current = []) {
		$attendee = new Attendee();
		$email = str_replace('mailto:', '',(string)$vattendee); // Will link to userId when found
		if(count($current[0])==3 && $current[0] === $email) {
			$attendee->calendarId = $current[1]; //calendarId;
			$attendee->groupId = $current[2]; //groupId;
		}
		empty($vattendee['CN']) ?: $attendee->name = $vattendee['CN'];
		$attendee->email = $email;
		$attendee->responseStatus = AttendeeStatus::fromText($vattendee['PARTSTAT']);
		$attendee->role = Role::fromText($vattendee['ROLE']);
		return $attendee;
	}

	static private function parseException($vevent) {
		$props = [
			'recurrenceId' => $vevent->{'RECURRENCE-ID'},
			'uid' => $vevent->UID
		];
		empty($vevent->TITLE) ?: $props['title'] = $vevent->TITLE;
		empty($vevent->DTSTART) ?: $props['startAt'] = $vevent->DTSTART->getDateTime();
		empty($vevent->DTEND) ?: $props['endAt'] = $vevent->DTEND->getDateTime();
		empty($vevent->TITLE) ?: $props['title'] = $vevent->TITLE;
		empty($vevent->SUMMARY) ?: $props['description'] = $vevent->SUMMARY;
		empty($vevent->LOCATION) ?: $props['location'] = $vevent->LOCATION;
		empty($vevent->STATUS) ?: $props['status'] = EventStatus::fromText($vevent->STATUS);
		empty($vevent->CLASS) ?: $props['classification'] = Visibility::fromText($vevent->CLASS);
		return $props;
	}

	static public function makeRecurrenceIterator(RecurrenceRule $rule) {
		$values = ['FREQ' => $rule->frequency];
		empty($rule->occurrences) ?: $values['COUNT'] = $rule->occurrences;
		empty($rule->until) ?:	$values['UNTIL'] = $rule->until->format('Ymd');
		empty($rule->interval) ?: $values['INTERVAL'] = $rule->interval;
		empty($rule->bySetPos) ?: $values['BYSETPOS'] = $rule->bySetPos;
		empty($rule->bySecond) ?: $values['BYSECOND'] = $rule->bySecond;
		empty($rule->byMinute) ?: $values['BYMINUTE'] = $rule->byMinute;
		empty($rule->byHour) ?: $values['BYHOUR'] = $rule->byHour;
		empty($rule->byDay) ?: $values['BYDAY'] = $rule->byDay;
		empty($rule->byMonthday) ?: $values['BYMONTHDAY'] = $rule->byMonthday;
		empty($rule->byMonth) ?: $values['BYMONTH'] = $rule->byMonth;
		return new RRuleIterator($values, $rule->event->startAt);
	}
	
	static private $ruleMap = [
			'FREQ' => 'frequency',
			'COUNT' => 'occurrences',
			'UNTIL' => 'until',
			'INTERVAL' => 'interval',
			'BYSETPOS' => 'bySetPos',
			'BYSECOND' => 'bySecond',
			'BYMINUTE' => 'byMinute',
			'BYHOUR' => 'byHour',
			'BYDAY' => 'byDay',
			'BYMONTHDAY' => 'byMonthday',
			'BYMONTH' => 'byMonth',
		];

	/**
	 * Create an iCalendar RRule from a RecurrenceRule object
	 * @param RecurrenceRule $recurrenceRule
	 * @return \Sabre\VObject\Property\ICalendar\Recur $rule
	 */
	static private function createRrule($recurrenceRule) {
		$rule = '';
		foreach(self::$ruleMap as $key => $value) {
			if(!empty($recurrenceRule->{$value})) {
				$rule .= $key . '=';
				$rule .= ($value == 'until') ? $recurrenceRule->{$value}->format('Ymd') : $recurrenceRule->{$value};
				$rule .= ';';
			}
		}
		return $rule;
	}

	
	static private function rruleToObject(VObject\Property\ICalendar\Recur $rule) {
		
		$recurrenceRule = new RecurrenceRule();
		foreach($rule as $key => $value) {
			$mappedKey = self::$ruleMap[$key];
			$recurrenceRule[$mappedKey] = $value;
		}
		return $recurrenceRule;
	}

	/**
	 * Read the VObject string data and return an Event object
	 * If a Blob object is passed and the mimeType is text/calendar teh contact will be fetched
	 * 
	 * @param string|Blob $data VObject data
	 * @return VObject\Document
	 */
	static public function read($data) {
		if($data instanceof \GO\Core\Blob\Model\Blob && $data->contentType === 'text/calendar') {
			$data = file_get_contents($data->getPath());
		}
		return VObject\Reader::read(\IFW\Util\StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);		
	}

	/**
	 * Sending invite, cancel, reply update via email
	 *
	 * @param Event $event the new, deleted or modified event
	 * @param string $fromEmail Sending on behalf of
	 */
	static public function sendItip(Event $event, $status = null) {
		$oldCalendar = null;
		$calendar = null;
		if(!$event->isNew()) {
			$oldAttributes = $event->getModifiedAttributes();
			$oldCalendar = $oldAttributes['vevent'];
		}
		if(!$event->markDeleted) {
			$calendar = $event->vevent;
			if($status !== null) {
				$calendar->VEVENT->ATTENDEE['PARTSTAT'] = $status;
			}
		}
		

		$broker = new VObject\ITip\Broker();
		$messages = $broker->parseEvent(
			$calendar,
			\GO()->getAuth()->user()->email,
			$oldCalendar
		);
	
		//\GO()->debug(\GO()->getAuth()->user()->email);
		
		foreach($messages as $message) {
			self::sendMail($message);
		}
	}

	/**
	 * Just send the RSVP mail (used in Messages module)
	 * @param type $vcal
	 * @param type $status
	 */
	static public function rsvp($vcal, $status, $currentUser) {

		$broker = new VObject\ITip\Broker();

		$old = clone $vcal;

		//$vcal->VEVENT->ATTENDEE['RSVP'] = 'TRUE';
		$vcal->VEVENT->ATTENDEE['PARTSTAT'] = $status;

		$messages = $broker->parseEvent(
			$vcal,
			$currentUser, //'michael@intermesh.dev',//\GO()->getAuth()->user()->email,
			$old
		);

		foreach($messages as $message) {
			echo $message->message->serialize();
			//self::sendMail($message);
		}

	}

//	static protected function createItipMessage($vcal) { // MOVe to RSVP
//		$message = new \Sabre\VObject\ITip\Message();
//		$message->message = $vcal;
//		$message->method = 'REQUEST';
//		$message->component = 'VEVENT';
//		$message->uid = $vcal->VEVENT->UID;
//		$message->sequence = isset($vcal->VEVENT[0]) ? (string)$vcal->VEVENT[0]->SEQUENCE : null;
//
//		$message->sender = $vcal->VEVENT->ATTENDEE->getValue();
//		$message->senderName = isset($vcal->VEVENT->ATTENDEE['CN']) ? $vcal->VEVENT->ATTENDEE['CN']->getValue() : null;
//		$message->recipient = $vcal->VEVENT->ORGANIZER->getValue();
//		$message->recipientName = isset($vcal->VEVENT->ORGANIZER['CN']) ? $vcal->VEVENT->ORGANIZER['CN'] : null;
//
//		return $message;
//	}

	/**
	 * Use Swift to send the ITIP message
	 * @param VObject\ITip\Message $itip
	 */
	static public function sendMail(VObject\ITip\Message $itip) {

		$invite = new \Swift_Attachment($itip->message->serialize(), 'invite.ics','text/calendar');
		
		$message = new Message(GO()->getSettings()->smtpAccount);
		$message
			->setSubject($itip->message->VEVENT->SUMMARY)
			->setFrom($itip->sender, (string)$itip->senderName)
			->setTo($itip->recipient, (string)$itip->recipientName)
			->attach($invite)
			->setBody('tada')
			->send();
	}

	/**
	 * Process an incomming Email VEVENT with ITIP
	 *
	 * @param Message $message the message containing ITIP information
	 */
	static public function processItip($message) {

		$oldCal = null; //TodoL check if invite was not new

		$broker = new VObject\ITip\Broker();
		return $broker->processMessage($message, $oldCal);

	}

}
