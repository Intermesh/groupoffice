<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\fs\Blob;
use go\core\mail\Address;
use go\core\mail\Attachment;
use go\core\model\Acl;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;

class ICalendarHelper {

	const PROD = '-//Intermesh//NONSGML GroupOffice 68//EN';

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
	 * @param VCalendar $vcalendar The original vcalendar to sync to
	 */
	static function toVObject(CalendarEvent $event, $vcalendar = null) {

		if($vcalendar === null) {
			$vcalendar = new VCalendar([
				'PRODID' => self::PROD,
			]);
		}

		$vevent = $vcalendar->add('VEVENT', self::toVEvent($event));
		if($event->showWithoutTime) {
			$vevent->DTSTART['VALUE'] = 'DATE';
			$vevent->DTEND['VALUE'] = 'DATE';
		}

		if($event->participants) {
			foreach ($event->participants as $participant) {
				$vevent->add(...self::toAttendee($participant));
			}
		}

		if(!$event->useDefaultAlerts && is_array($event->alarms)) {
			foreach($event->alarms as $id => $alarm) {
				$vevent->add('VALARM', [
					'TRIGGER' => $alarm->offset, // 15 minutes before the event
					'DESCRIPTION' => 'Alarm',
					'ACTION' => 'DISPLAY',
				]);
			}
		}
		//@todo: ATTACHMENT Files?

		if($event->isRecurring()) {
			$vevent->RRULE = self::toRrule($event);
			if(!empty($event->recurrenceOverrides)) {
				foreach ($event->recurrenceOverrides as $recurrenceId => $patch) {
					if (isset($patch->excluded)) {
						$exdate = $vevent->add('EXDATE', new DateTime($recurrenceId, $event->timeZone()));
						if($event->showWithoutTime)
							$exdate['VALUE'] = 'DATE';
					} else {
						$props = self::toVEvent((new CalendarEvent())->setValues($patch->toArray()), true);
						$props['UID'] = $event->uid;
						$props['RECURRENCE-ID'] = new DateTime($recurrenceId, $event->timeZone());
						$vcalendar->add('VEVENT', $props);
						if($event->showWithoutTime) {
							$vevent->{'RECURRENCE-ID'}['VALUE'] = 'DATE';
							if(isset($props['DTSTART'])) $vevent->DTSTART['VALUE'] = 'DATE';
							if(isset($props['DTEND'])) $vevent->DTEND['VALUE'] = 'DATE';
						}
					}
				}
			}
		}

		return $vcalendar;
	}

	private static function toInvite($method, $event, $occurrence) {
		$c = new VCalendar([
			'PRODID' => '-//Intermesh//NONSGML GroupOffice '.go()->getVersion().'//EN',
			'METHOD' => $method
		]);
		$c->add('VEVENT', self::toVEvent($event));
		return $c;
	}

	/**
	 * @param CalendarEvent $event
	 * @return array
	 */
	static function toVEvent($event, $isException=false) {
		$props = [];
		if(!$isException) {
			if(!empty($event->privacy) && $event->privacy !== 'public') $props['CLASS'] = self::$privacyMap[$event->privacy];
			if(!empty($event->status)) $props['STATUS'] = strtoupper($event->status);
			if(!empty($event->uid)) $props['UID'] = $event->uid;
			if(!empty($event->modifiedAt)) $props['LAST-MODIFIED'] = $event->modifiedAt; // @todo: check if datetime must be UTC
			if(!empty($event->createdAt)) $props['DTSTAMP'] = $event->createdAt;
		}
		if(!empty($event->title)) $props['SUMMARY'] = $event->title;
		if(!empty($event->start)) $props['DTSTART'] = $event->start($event->showWithoutTime);
		if(!empty($event->duration)) $props['DTEND'] = $event->end($event->showWithoutTime);
		// Sequence is for updates on the event its used for ITIP
		if(isset($event->sequence)) $props['SEQUENCE'] = $event->sequence;
		if(!empty($event->description)) $props['DESCRIPTION'] = $event->description;
		if(!empty($event->location)) $props['LOCATION'] = $event->location;
		//empty($event->tag) ?: $vcalendar->VEVENT->CATEGORIES = $event->tag;


		return $props;
	}

	static function toAttendee(Participant $participant) {
		$attr = ['CN' => $participant->name];
		if($participant->participationStatus !== 'needs-action') {
			$attr['PARTSTAT'] = strtoupper($participant->participationStatus);
		}
		if($participant->expectReply) {
			$attr['RSVP'] = 'TRUE';
		}
		foreach ($participant->getRoles() as $role => $true) {
			if (in_array($role, self::$roleMap)) {
				$attr['ROLE'] = self::$roleMap[$role];
				break;
			}
		}
		return [
			!empty($participant->roles['owner']) ? 'ORGANIZER' : 'ATTENDEE',
			'mailto:'.$participant->email,
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
			return self::parseVObject($vevent, $event);
		return false;
	}

	/**
	 * Parse a VObject to an Event object
	 * @param VCalendar $vcalendar
	 * @param int $calendarId
	 * @param CalendarEvent $event the event to insert the data into
	 * @return CalendarEvent updated or new Event if not found
	 */
	static public function parseVObject(VCalendar $vcalendar, CalendarEvent $event) {

		$exceptions = [];
		$baseEvents = [];

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
			$baseEvents[$event->uid] = $event;
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
				$organizer = self::parseAttendee(new Participant($event), $vevent->ORGANIZER);
				$organizer->setRoles(['owner' => true]);
				$event->participants[] = $organizer;
			}
			if(isset($vevent->attendee)) {
				foreach ($vevent->ATTENDEE as $vattendee) {
					$event->participants[] = self::parseAttendee(new Participant($event), $vattendee);
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
			if(isset($baseEvents[$uid]) && $baseEvents[$uid]->getIsRecurring()) {
				$recurrenceId = $props['recurrenceId'];
				unset($props['recurrenceId'], $props['uid']);
				$baseEvents[$uid]->recurrenceOverrides[$recurrenceId] = (new RecurrenceOverride($event))->setValues($props);
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

	static private function parseAttendee(Participant $p, $vattendee) {
		$p->setValues([
			'email'=>str_replace('mailto:', '',(string)$vattendee)
		]);
		$p->kind = !empty($vattendee['CUTYPE']) ? strtolower($vattendee['CUTYPE']) : 'induvidual';
		if(!empty($vattendee['CN'])) $p->name = $vattendee['CN'];
		if(!empty($vattendee['ROLE'])) $p->roles[] = $vattendee['ROLE'];
		if(!empty($vattendee['RSVP'])) $p->expectReply = $vattendee['RSVP'];
		$p->participationStatus = strtolower($vattendee['PARTSTAT']);
		$map = array_flip(self::$roleMap);
		if(in_array((string)$vattendee['ROLE'], $map)) {
			$p->setRoles([$map[(string)$vattendee['ROLE']] => true]);
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

	static public function makeRecurrenceIterator(CalendarEvent $event) {
		return new VObject\Recur\RRuleIterator(self::toRrule($event), $event->start);
	}


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
		if(isset($parts['COUNT'])) {
			$values->count = intval($parts['COUNT']);
		} elseif(isset($parts['UNTIL'])) {
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

	const EssentialScheduleProps = ['start', 'duration', 'location', 'title', 'description', 'showWithoutTime'];
	/**
	 * Send all the needed imip schedule messages
	 * @param CalendarEvent $event
	 * @parma bool $delete if the event is about to be deleted
	 */
	static public function handleScheduling(CalendarEvent $event, bool $willDelete = false) {

		$current = $event->calendarParticipant();

		if(empty($current) ||
			(!$event->isOrigin && !$current->isModified('participantionStatus'))) {
			return;
		}

		if(!$event->isOrigin) {
			$status = $willDelete ? Participant::Declined : $current->participationStatus;
			self::replyImip($event, $status);
			$title = ucfirst($status);
		} elseif ($current->isOwner()) {
			if($event->isRecurring()) {
				throw new \Exception('Need to implement scheduling recurring instances');
			}

			$newOnly = !$willDelete && $event->isModified('participants') && !$event->isModified(self::EssentialScheduleProps);

			self::sendImip($event, $willDelete ? 'CANCEL': 'REQUEST', $newOnly);

		}

	}

	private static function replyImip($event, $status) {

	}

	private static function sendImip(CalendarEvent $event, $method, $newOnly = false) {
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

			$ics = self::toVObject($event, new VCalendar([
				'PRODID' => self::PROD,
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

	static function mailBody($event, $method, $recipient, $title) {
		if(!$event) {
			return false;
		}
		ob_start();
		$url = 'https://group-office.com/event';
		include __DIR__.'/../views/imip.php';
		return ob_get_clean();
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
	 * @return void
	 */
	static function sendRequest($event) {

	}

	static function processMessage($vcalendar, Calendar $calendar, $sender) {

		if(!$calendar->hasPermissionLevel(Acl::LEVEL_WRITE)) {
			return false;
		}
		$vevent = $vcalendar->VEVENT[0];
		$existingEvent = CalendarEvent::find(['calendarId'=>$calendar->id, 'uid'=>(string)$vevent->uid])->single();
		switch($vcalendar->method){
			case 'REQUEST': return self::processRequest($vcalendar,$sender,$existingEvent);
			case 'CANCEL': return self::precessCancel($vcalendar,$existingEvent);
			case 'REPLY': return self::processReply($vcalendar,$sender,$existingEvent);
		}
		return false;
	}

	private static function processRequest(VCalendar $vcalendar, $sender, CalendarEvent $existingEvent = null) {
		if(!$existingEvent) {
			$existingEvent = new CalendarEvent();
			$existingEvent->isOrigin = false;
			$existingEvent->replyTo = $sender->email;
		}
		return self::parseVObject($vcalendar, $existingEvent);
	}

	private static function precessCancel(VCalendar $vcalendar, CalendarEvent $existingEvent = null) {
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
		}
		return $existingEvent;
	}

	/**
	 * The message is a reply. This is for example an attendee telling an organizer he accepted the invite, or declined it.
	 */
	private static function processReply(VCalendar $vcalendar, $sender, CalendarEvent $existingEvent = null) {
		if(!$existingEvent) return;

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
				$recurId = $vevent->{'RECURRENCE-ID'}->getValue();
				if(!isset($item->recurrenceOverrides[$recurId])) {
					// TODO: check if the given RECURRENCE-ID is valid for $existingEvent->recurrenceRule
					$item->recurrenceOverrides[$recurId] = new RecurrenceOverride($item);
				}
				$item = $item->recurrenceOverrides[$recurId];

			}
			// base event
			$found = false;
			foreach($item->participants as $participant) {
				if($participant->email === $sender->email) {
					$participant->participationStatus = $status;
					$found = true;
					break;
				}
			}
			if(!$found) {
				$item->participants[] = (new Participant($item))->setValues([
					'email' => $sender->email,
					'name' => $sender->name ?? $sender->email,
					'participationStatus' => $status
				]);
			}

		}
		return $existingEvent;
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
