<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\fs\Blob;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use Sabre\VObject;

class ICalendarHelper {

	static $privacyMap = [
		'public' => 'PUBLIC',
		'private' => 'PRIVATE',
		'secret' => 'CONFIDENTIAL'
	];

	static $roleMap = [
		'chair' => 'CHAIR',
      'attendee' => 'REQ-PARTICIPANT',
      'optional' => 'OPT-PARTICIPANT',
      'informational' => 'NON-PARTICIPANT',
	];

	/**
	 * Parse an Event object to a VObject
	 * @param Event $event
	 * @param VObject\Component\VCalendar $vcalendar The original vcalendar to sync to
	 */
	static function toVObject(CalendarEvent $event, $vcalendar = null) {

		if($vcalendar === null) {
			$vcalendar = new VObject\Component\VCalendar([
				'PRODID' => '-//Intermesh//NONSGML GroupOffice '.go()->getVersion().'//EN',
			]);
		}

		$vcalendar->add('VEVENT', self::toVEvent($event));

		if($event->participants) {
			foreach ($event->participants as $participant) {
				$vcalendar->VEVENT->add(...self::toAttendee($participant));
			}
		}
		//@todo: VALARMS depend on who fetched the event. Need to be implemented when caldav is build
		//@todo: ATTACHMENT Files

		if($event->isRecurring()) {
			$vcalendar->VEVENT->RRULE = self::toRrule($event);
			$exceptions = $event->getRecurrenceOverrides();
			if(!empty($exceptions)) {
				foreach ($exceptions as $recurrenceId => $patch) {
					if (!empty($patch->excluded)) {
						$vcalendar->VEVENT->add('EXDATE', $recurrenceId);
					} else {
						$props = self::toVEvent($patch);
						$props['UID'] = $event->uid;
						$vcalendar->add('VEVENT', $props);
					}
				}
			}
		}

		return $vcalendar;
	}

	static function toVEvent($event) {
		$props = [];
		if(!empty($event->uid)) $props['UID'] = $event->uid;
		if(!empty($event->title)) $props['SUMMARY'] = $event->title;
		if(!empty($event->status)) $props['STATUS'] = strtoupper($event->status);
		if(!empty($event->modifiedAt)) $props['LAST-MODIFIED'] = $event->modifiedAt; // @todo: check if datetime must be UTC
		if(!empty($event->createdAt)) $props['DTSTAMP'] = $event->createdAt;
		if(!empty($event->start)) $props['DTSTART'] = $event->start($event->showWithoutTime);
		if(!empty($event->duration)) $props['DTEND'] = $event->end($event->showWithoutTime);
		// Sequence is for updates on the event its used for ITIP
		if(isset($event->sequence)) $props['SEQUENCE'] = $event->sequence;
		if(!empty($event->description)) $props['DESCRIPTION'] = $event->description;
		if(!empty($event->location)) $props['LOCATION'] = $event->location;
		//empty($event->tag) ?: $vcalendar->VEVENT->CATEGORIES = $event->tag;
		if(!empty($event->privacy) && $event->privacy !== 'public') $props['CLASS'] = self::$privacyMap[$event->privacy];


		return $props;
	}

	static function toAttendee(Participant $participant) {
		$attr = ['CN' => $participant->name];
		($participant->participationStatus === 'needs-action') ?: $attr['PARTSTAT'] = strtoupper($participant->participationStatus);
		if ($participant->expectReply) $attr['RSVP'] = 'TRUE';
		foreach ($participant->roles as $role) {
			if (in_array($role, self::$roleMap)) {
				$attr['ROLE'] = self::$roleMap[$role];
				break;
			}
		}
		return [
			$participant->roles['owner'] ? 'ORGANIZER' : 'ATTENDEE',
			$participant->email,
			$attr
		];
	}

	static private $ruleMap = [
		'FREQ' => 'frequency',
		'COUNT' => 'count',
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
	 * @return string \Sabre\VObject\Property\ICalendar\Recur $rule
	 */
	static private function toRrule($event) {
		$recurrenceRule = $event->getRecurrenceRule();
		$rule = [];
		foreach(self::$ruleMap as $iKey => $jKey) {
			if(!empty($recurrenceRule->{$jKey})) {
				$val = $recurrenceRule->{$jKey};
				if($jKey == 'until') {
					if(strlen($val) > 10) { // with time
						$dt = DateTime::createFromFormat('Y-m-d H:i:s', $val, $event->timeZone());
						$dt->setTimezone(new \DateTimeZone('UTC'));
						$val = $dt->format('Ymd\THis\Z');
					} else {
						$val = strtr($val, ['-' => '']);
					}
				} elseif($jKey == 'byDay') {
					// paste back together
					$val = implode(',', array_map(fn($v) => $v->nthOfPeriod.$v->day, $val));
				} elseif(is_array($val)) {
					$val = implode(',', $val);
				} else { //string?
					$val = strtoupper($val);
				}

				$rule[] = $iKey.'='.$val;
			}
		}
		return implode(';',$rule);
	}









	static function fromICal(string $data, $event = null) {
	//	$vCalendar = VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);
		$splitter = new VObject\Splitter\ICalendar(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		if($vevent = $splitter->getNext())
			return self::fromVObject($vevent, $event);
		return false;
	}

	/**
	 * Parse a VObject to an Event object
	 * @param VObject\Component\VCalendar $vcalendar
	 * @param int $calendarId
	 * @param CalendarEvent $event the event to insert the data into
	 * @return CalendarEvent updated or new Event if not found
	 */
	static public function fromVObject($vcalendar, $event) {

		$exceptions = [];

		foreach($vcalendar->VEVENT as $vevent) {
			if(!empty($vevent->{'RECURRENCE-ID'})) {
				$exceptions[] = self::parseOccurrence($vevent, (object)[
					'recurrenceId' => $vevent->{'RECURRENCE-ID'},
					'uid' => (string)$vevent->UID // unset after merge
				]);
				continue;
			}
			// todo
			// if(!$event->isNew && $event->blobId) // merge vevent into current blob

			$event = self::parseOccurrence($vevent,$event); // title, description, start, duration, location, status, privacy
			if(!$vevent->DTSTART->isFloating())
				$event->timeZone = $event->start->getTimezone()->getName();
			if($event->isNew())
				$event->createdAt = $vevent->DTSTAMP->getDateTime();
			$event->modifiedAt = $vevent->{'LAST-MODIFIED'}->getDateTime();
			$event->showWithoutTime = !$vevent->DTSTART->hasTime();
			if(isset($vevent->SEQUENCE))
				$event->sequence = (int)$vevent->SEQUENCE->getValue();
			$event->privacy = array_flip(self::$privacyMap)[$vevent->CLASS] ?? 'public';
			//$event->organizerEmail = str_replace('mailto:', '',(string)$vevent->ORGANIZER);
			if(isset($vevent->ORGANIZER)) {
				$organizer = self::parseAttendee($vevent->ORGANIZER);
				$organizer->roles['owner'] = true;
				$event->participants[] = $organizer;
			}
			if(isset($vevent->attendee)) {
				foreach ($vevent->ATTENDEE as $vattendee) {
					$event->participants[] = self::parseAttendee($vattendee);
				}
			}

			if(!empty((string)$vevent->RRULE)) {
				$event->setRecurrenceRule(self::parseRrule($vevent->RRULE, $event));
				if(!empty($vevent->EXDATE)) {
					foreach ($vevent->EXDATE as $exdate) {
						$event->recurrenceOverrides[$exdate->getJsonValue()] = null;
					}
				}
			}

			//TODO VALARM
		}
		//Attach exceptions found in VCALENDAR
		foreach($exceptions as $props) {
			$uid = $props->uid;
			if(isset($mainEvents[$uid]) && $mainEvents[$uid]->getIsRecurring()) {
				unset($props['uid']);
				$recurrenceId = $props['recurrenceId'];
				$mainEvents[$uid]->recurrenceOverrides[$recurrenceId] = $props;
			}
		}
		// All exceptions that do not have the recurrence ID are ignored here

		// $vcalendar could contain more data then is in event
		$blob = self::makeBlob($event, $vcalendar->serialize());
		$event->attachBlob($blob->id);
		$vcalendar->destroy();
		return $event;
	}

	static function makeBlob(CalendarEvent $event, string $data = null) {
		$blob = Blob::fromString($data ?? ICalendarHelper::toVObject($event)->serialize());
		$blob->type = 'text/calendar';
		$blob->name = $event->uid . '.ics';
		if(!$blob->save()) {
			throw new \Exception('could not save blob');
		}

		return $blob;
	}

	static private function parseAttendee($vattendee) {
		$p = (object)[
			'email'=>str_replace('mailto:', '',(string)$vattendee),
			'roles' => [],
			'kind' =>'individual'
		];
		if(!empty($vattendee['CN'])) $p->name = $vattendee['CN'];
		if(!empty($vattendee['ROLE'])) $p->roles[] = $vattendee['ROLE'];
		if(!empty($vattendee['RSVP'])) $p->expectReply = $vattendee['RSVP'];
		$p->participationStatus = strtolower($vattendee['PARTSTAT']);
		$roles = array_flip(self::$roleMap);
		if(is_array((string)$vattendee['ROLE'], $roles)) {
			$p->roles[$roles[(string)$vattendee['ROLE']]] = true;
		}
		return $p;
	}

	static private function parseOccurrence($vevent, $props) {

		if(isset($vevent->DTSTART)) {
			$props->start = $vevent->DTSTART->getDateTime();
		}
		//empty($vevent->DTSTART) ?: $props->start = $vevent->DTSTART->getDateTime()->format(DateTime::FORMAT_API_LOCAL);
		if(!empty($vevent->SUMMARY)) $props->title = (string)$vevent->SUMMARY;
		if(!empty($vevent->DESCRIPTION)) $props->description = (string)$vevent->DESCRIPTION;
		if(!empty($vevent->LOCATION)) $props->location = (string)$vevent->LOCATION;
		if(!empty($vevent->STATUS)) $props->status = strtolower($vevent->STATUS);
		if(!empty($vevent->CLASS)) $props->privacy = array_flip(self::$privacyMap)[$vevent->CLASS] ?? 'public';
		if(!empty($vevent->DURATION)) {
			$props->duration = (string)$vevent->DURATION;
		} else if (!empty($vevent->DTEND) && !empty($props->start)) {
			$props->duration = self::dateIntervalToISO($vevent->DTEND->getDateTime()->diff($props->start));
		}
		return $props;
	}

//	static public function makeRecurrenceIterator(RecurrenceRule $rule) {
//		$values = ['FREQ' => $rule->frequency];
//		if(!empty($rule->count)) $values['COUNT'] = $rule->count;
//		if(!empty($rule->until))	$values['UNTIL'] = $rule->until->format('Ymd');
//		if(!empty($rule->interval)) $values['INTERVAL'] = $rule->interval;
//		if(!empty($rule->bySetPos)) $values['BYSETPOS'] = $rule->bySetPos;
//		if(!empty($rule->bySecond)) $values['BYSECOND'] = $rule->bySecond;
//		if(!empty($rule->byMinute)) $values['BYMINUTE'] = $rule->byMinute;
//		if(!empty($rule->byHour)) $values['BYHOUR'] = $rule->byHour;
//		if(!empty($rule->byDay)) $values['BYDAY'] = $rule->byDay;
//		if(!empty($rule->byMonthday)) $values['BYMONTHDAY'] = $rule->byMonthday;
//		if(!empty($rule->byMonth)) $values['BYMONTH'] = $rule->byMonth;
//		return new RRuleIterator($values, $rule->event->start);
//	}


	static private function parseRrule(VObject\Property\ICalendar\Recur $rule, $event) {
		$parts = $rule->getParts();
		$values = (object)['frequency' => strtolower($parts['FREQ'])];
		if(isset($parts['INTERVAL']) && $parts['INTERVAL'] != 1) {
			$values->interval = intval($parts['INTERVAL']);
		}
		if(isset($parts['RSCALE'])) $values->rscale = strtolower(isset($parts['RSCALE']));
		if(isset($parts['SKIP'])) $values->skip = strtolower(isset($parts['SKIP']));
		if(isset($parts['WKST'])) $values->firstDayOfWeek = strtolower(isset($parts['WKST']));
		if(isset($parts['BYDAY'])) {
			$values->byDay = [];
			$days = explode(',', $parts['BYDAY']);
			foreach($days as $day) {
				$bd = (object)['day' => strtolower(substr($day, -2))];
				if(strlen($day) > 2) {
					$bd->nthOfPeriod = substr($day, 0, -2);
				}
				$values->byDay[] = $bd;
			}
		}
		if(isset($parts['BYMONTHDAY'])) $values->byMonthDay = array_map('intval',explode(',', $parts['BYDAY']));
		if(isset($patrs['BYMONTH'])) $values->byMonth = explode(',', $parts['BYMONTH']); // is string, could have L suffix
		if(isset($patrs['BYYEARDAY'])) $values->byYearDay = array_map('intval',explode(',', $parts['BYYEARDAY']));
		if(isset($patrs['BYWEEKNO'])) $values->byWeekNo = array_map('intval',explode(',', $parts['BYWEEKNO']));
		// skip byHour, byMinute, bySecond
		if(isset($patrs['BYSETPOS'])) $values->bySetPosition = array_map('intval',explode(',', $parts['BYSETPOS']));
		if(isset($parts['COUNT'])) $values->count = intval($parts['COUNT']);
		elseif(isset($parts['UNTIL'])) {
			// could be "20240824T063000Z" or "20240824"
			if(strlen($parts['UNTIL']) > 10) { // has time
				// convert to localtime
				$dt = DateTime::createFromFormat('Ymd\THis\Z', $parts['UNTIL'], new \DateTimeZone('etc/UTC'));
				if(!empty($event->timeZone))
					$dt->setTimezone(new \DateTimeZone($event->timeZone));
				$values->until = $dt->format('Y-m-d H:i:s');
			} else {
				// add dashes and append 0-time
				$values->until = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $parts['UNTIL']) . ' 00:00:00';
			}
		}
		return $values;
	}

	static function dateIntervalToISO(\DateInterval $interval, $default = 'PT0S') {
		static $f = ['M0S', 'H0M', 'DT0H', 'M0D', 'P0Y', 'Y0M', 'P0M'];
		static $r = ['M', 'H', 'DT', 'M', 'P', 'Y', 'P'];

		return rtrim(str_replace($f, $r, $interval->format('P%yY%mM%dDT%hH%iM%sS')), 'PT') ?: $default;
	}

	/**
	 * Read the VObject string data and return an Event object
	 * If a Blob object is passed and the mimeType is text/calendar teh contact will be fetched
	 * 
	 * @param string|Blob $data VObject data
	 * @return VObject\Document
	 */
	static public function read($data) {
		if($data instanceof Blob && $data->contentType === 'text/calendar') {
			$data = file_get_contents($data->path());
		}
		return VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);
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
	 * @param VObject\Component\VCalendar $vcal
	 * @param string $status the participation status
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


	/**
	 * Use Swift to send the ITIP message
	 * @param VObject\ITip\Message $itip
	 */
	static public function sendMail(VObject\ITip\Message $itip) {

		$invite = new \Swift_Attachment($itip->message->serialize(), 'invite.ics','text/calendar;method=CANCEL;charset=utf-8');
		$invite->setDisposition('inline');
		
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
