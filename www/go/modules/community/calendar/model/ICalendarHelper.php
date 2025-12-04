<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use DateMalformedStringException;
use DateTimeZone;
use go\core\db\Expression;
use go\core\ErrorHandler;
use go\core\exception\JsonPointerException;
use go\core\fs\Blob;
use go\core\model\Principal;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use IntlTimeZone;
use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Component\VTimeZone;
use stdClass;

class UserTimezoneGuesser implements VObject\TimezoneGuesser\TimezoneGuesser {

	public function guess(VTimeZone $vtimezone, bool $failIfUncertain = false): ?DateTimeZone
	{
		if(!go()->getAuthState()) {
			return null;
		}
		return new DateTimeZone(go()->getAuthState()->getUser(['timezone'])->timezone);
	}
}

VObject\TimeZoneUtil::addTimezoneGuesser('gouser', new UserTimezoneGuesser());

class IntlTimezoneFinder implements VObject\TimezoneGuesser\TimezoneFinder {

	public function find(string $tzid, bool $failIfUncertain = false): ?DateTimeZone
	{
		if (!class_exists(IntlTimeZone::class)) {
			return null;
		}

		$iana =  IntlTimeZone::getIDForWindowsID($tzid);
		if(!$iana) {
			return null;
		}

		return new DateTimeZone($iana);
	}
}

VObject\TimeZoneUtil::addTimezoneFinder('intltimezone', new IntlTimezoneFinder());

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

	const kinds = [
		'individual', 'group', 'location', 'resource','unknown'
	];

	/**
	 * Parse an Event object to a VObject
	 * @param CalendarEvent $event
	 * @param VCalendar|null $vcalendar The original vcalendar to sync to
	 * @return VCalendar
	 * @throws \DateInvalidTimeZoneException
	 * @throws DateMalformedStringException
	 */
	static function toVObject(CalendarEvent $event, ?VCalendar $vcalendar = null): VCalendar
	{

		if($vcalendar === null) {
			$vcalendar = new VCalendar();
		}

		$vcalendar->PRODID = CalendarEvent::prodId();

		//TODO prodid always GO??? gmail doesn't process our reply but it does work with the sabre imipplugin


		$vevent = $vcalendar->add(self::toVEvent($vcalendar->createComponent('VEVENT'),$event));

		foreach($event->alerts() as $id => $alert) {
			if(!empty($alert->getTrigger()['offset'])) {
				$vevent->add('VALARM', [
					'TRIGGER' => $alert->getTrigger()['offset'], // 15 minutes before the event
					'DESCRIPTION' => 'Alarm',
					'ACTION' => $alert->action,
				]);
			} else if (!empty($alert->getTrigger()['when'])) {
				$vevent->add('VALARM', [
					'TRIGGER' => $alert->getTrigger()['when'], // 15 minutes before the event
					'DESCRIPTION' => 'Alarm',
					'ACTION' => $alert->action,
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
						try {
							$exEvent = $event->patchedInstance($recurrenceId);
							$vcalendar->add(self::toVEvent($vcalendar->createComponent('VEVENT'), $exEvent, $recurrenceId));
						}catch(JsonPointerException $e) {
							// There was a case where /partipants/<NOTEXISTINGID>/participantStatus was incorrectly patched
							ErrorHandler::logException($e, "Failed to patch event id: ". $event->id. " with patch " . $recurrenceId);
						}
					}
				}
			}
		}
		// this will remove the invalid UTF-8 characters for XML response in caldav.
		$vcalendar->validate(VObject\Node::REPAIR);

		return $vcalendar;
	}

	static function toInvite(string $method, CalendarEvent &$event) : VCalendar {
		// Prodid must be set to Group-Office's otherwise gmail won't process the reply
		$c = new VCalendar(['PRODID' => CalendarEvent::prodId(), 'METHOD' => $method]);
		if(isset($event->timeZone))
			$c->add(new \go\modules\community\calendar\model\VTimezone($event->timeZone));
		$forBody = $event;
		$baseVEvent = null;
		if($method == 'CANCEL' || $event->isModified(array_merge(CalendarEvent::EventProperties,['participants']))) {
			// base event
			$baseVEvent = $c->add(self::toVEvent($c->createComponent('VEVENT'), $event));
			if($event->isRecurring()) {
				$baseVEvent->RRULE = self::toRrule($event);
			}
		}
		foreach($event->overrides(true) as $recurrenceId => $override) {
			if(!empty($override->excluded) && !empty($baseVEvent)) {
				$exdate = $baseVEvent->add('EXDATE', new DateTime($recurrenceId, $event->timeZone()));
				if($event->showWithoutTime)
					$exdate['VALUE'] = 'DATE';
			}
			$forBody = $override;
			$c->add(self::toVEvent($c->createComponent('VEVENT'), $override));
		}
		// &$event is displayed in the email body
		$event = $forBody;

		if(empty($c->vevent)) {
			throw new \LogicException("Invite with no events in ICS!");
		}

		return $c;
	}

	/**
	 * @param VEvent $vevent
	 * @param CalendarEvent $event
	 * @param ?string $recurrenceId
	 * @return VEvent
	 * @throws DateMalformedStringException
	 */
	static function toVEvent(VEvent $vevent, CalendarEvent $event, ?string $recurrenceId = null): VEvent
	{
		if(!$recurrenceId) {
			$recurrenceId = $event->recurrenceId;
		}

		if(!$recurrenceId) {
			if(!empty($event->privacy) && $event->privacy !== 'public') $vevent->CLASS = self::$privacyMap[$event->privacy];
			if(!empty($event->modifiedAt)) $vevent->{'LAST-MODIFIED'} = $event->modifiedAt->format('Ymd\THis\Z');
			$vevent->DTSTAMP = new DateTime();
		} else {
			$rId = $vevent->add('RECURRENCE-ID', new DateTime($recurrenceId, $event->timeZone()));
			if(!empty($event->showWithoutTime)) {
				$rId['VALUE'] = 'DATE';
			}
		}
		if(!empty($event->uid)) $vevent->UID = $event->uid;
		//$vevent->add('DTSTART', $event->start($event->showWithoutTime)->format("Ymd\THis"), ['value' => $event->showWithoutTime ? 'DATE' : 'DATE-TIME']);
		if(!empty($event->start)) $vevent->DTSTART = $event->start($event->showWithoutTime);
		if(!empty($event->duration)) $vevent->DTEND = $event->end($event->showWithoutTime);
		if($event->showWithoutTime) {
			$vevent->DTSTART['VALUE'] = 'DATE';
			$vevent->DTEND['VALUE'] = 'DATE';
		}

		if(!empty($event->status)) $vevent->STATUS = strtoupper($event->status);
		// Sequence is for updates on the event it's used for ITIP
		if(isset($event->sequence)) $vevent->SEQUENCE = $event->sequence;

		$showAsPrivate = $event->isPrivate() && !$event->currentUserIsOwner();
		if(!empty($event->title)) $vevent->SUMMARY = $showAsPrivate ? '('.go()->t('Private', 'community', 'calendar').')' : $event->title;
		if(!empty($event->description) && !$showAsPrivate) $vevent->DESCRIPTION = $event->description;
		if(!empty($event->location) && !$showAsPrivate) $vevent->LOCATION = $event->location;

		if(!empty($event->color)) $vevent->COLOR = $event->color;
		if(!empty($event->categoryIds)) $vevent->CATEGORIES = implode(',',$event->categoryNames());

		if($event->participants) {
			foreach ($event->participants as $participant) {
				$vevent->add(...self::toAttendee($participant));
			}
		}

		return $vevent;
	}

	static function toAttendee(Participant $participant) {
		$attr = ['CN' => $participant->name];
		if($participant->participationStatus !== 'needs-action') {
			$attr['PARTSTAT'] = strtoupper($participant->participationStatus);
		}
		if($participant->expectReply) {
			$attr['RSVP'] = 'TRUE';
		}
		if($participant->kind) {
			//ENUM('individual', 'group', 'location', 'resource','unknown') NOT NULL,
			$attr['CUTYPE'] = $participant->kind == 'location' ? "ROOM" : strtoupper($participant->kind);
		}
		foreach ($participant->getRoles() as $role => $true) {
			if (in_array($role, self::$roleMap)) {
				$attr['ROLE'] = self::$roleMap[$role];
				break;
			}
		}
		return [
			!empty($participant->isOwner()) ? 'ORGANIZER' : 'ATTENDEE',
			'mailto:'.strtolower($participant->email),
			$attr
		];
	}

	static private $ruleMap = [
		'FREQ' => 'frequency',
		'COUNT' => 'count',
		'UNTIL' => 'until',
		'INTERVAL' => 'interval',
		'BYSETPOS' => 'bySetPosition',
		'BYSECOND' => 'bySecond',
		'BYMINUTE' => 'byMinute',
		'BYHOUR' => 'byHour',
		'BYDAY' => 'byDay',
		'BYMONTHDAY' => 'byMonthDay',
		'BYMONTH' => 'byMonth',
		'BYWEEKNO' => 'byWeekNo',
		'BYYEARDAY' => 'byYearDay'
	];

	/**
	 * Create an iCalendar RRule from a RecurrenceRule object
	 * @param CalendarEvent $event
	 * @return string \Sabre\VObject\Property\ICalendar\Recur $rule
	 * @throws \DateInvalidTimeZoneException
	 * @throws DateMalformedStringException
	 */
	static private function toRrule(CalendarEvent $event) {
		$recurrenceRule = $event->getRecurrenceRule();
		$rule = [];
		foreach(self::$ruleMap as $iKey => $jKey) {
			if(!empty($recurrenceRule->{$jKey})) {
				$val = $recurrenceRule->{$jKey};
				if($jKey == 'until') {
					if(strlen($val) > 10) { // with time
						$dt = DateTime::createFromFormat('Y-m-d\TH:i:s', $val, $event->timeZone());
						$dt->setTimezone(new \DateTimeZone('UTC'));
						$val = $dt->format('Ymd\THis\Z');
					} else {
						$val = strtr($val, ['-' => '']);
					}
				} elseif($jKey == 'byDay') {
					// paste back together
					$val = implode(',', array_map(fn($v) => ($v->nthOfPeriod??'').$v->day, $val));
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

	/**
	 * Generates CalendarEvent models from an icalendar file
	 *
	 * @param string $blobId
	 * @param array|null $values
	 * @return \Generator
	 * @throws VObject\ParseException
	 */
	static function calendarEventFromFile(string $blobId, array|null $values = null) {

		$data = file_get_contents(Blob::buildPath($blobId));
		$splitter = new VObject\Splitter\ICalendar(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		while($vevent = $splitter->getNext()) {
			try {
				$event = new CalendarEvent();
				if(isset($values)) {
					$event->setValues($values);
				}
				yield self::parseVObject($vevent, $event);
			} catch(\Throwable $e) {
				ErrorHandler::logException($e, "Failed to import event");
				yield ['error'=>$e, 'vevent'=>$vevent];
			}
		}
	}

	/**
	 * Returns the first vobject from icalendar data
	 *
	 * @param string $data
	 * @param $event
	 * @return false|CalendarEvent
	 * @throws VObject\ParseException
	 */
	static function fromICal(string $data, $event = null) {
	//	$vcalendar = VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);
		$splitter = new VObject\Splitter\ICalendar(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		if($vcalendar = $splitter->getNext())
			return self::parseVObject($vcalendar, $event ?? new CalendarEvent());
		return false;
	}

	/**
	 * Parse a VObject to an Event object
	 * @param VCalendar|string $vcalendar
	 * @param CalendarEvent $event the event to insert the data into
	 * @return CalendarEvent updated or new Event if not found
	 * @throws \Exception
	 */
	static public function parseVObject($vcalendar, CalendarEvent $event): CalendarEvent
	{

		if(is_string($vcalendar)) {
			$vcalendar = VObject\Reader::read($vcalendar, VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		}

		$exceptions = [];
		$baseEvents = [];
		$prodId = (string) $vcalendar->PRODID;


		if(empty($event->uid)) {
			$event->uid = ((string) $vcalendar->VEVENT[0]->uid) ?? null;
		}

		if(!empty($event->uid)) {
			$baseEvents[$event->uid] = $event; // so we can attach exceptions if that all we got
		}

		foreach($vcalendar->VEVENT as $vevent) {

			$obj = self::parseOccurrence($vevent, (object)[
				'uid' => (string) $vevent->UID // unset after merge
			]);


			if(!empty($vevent->{'RECURRENCE-ID'})) {
				Scheduler::fixRecurrenceId($event, $vevent);

				$obj->recurrenceId =$vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s');
				$exceptions[] = $obj;
				continue;
			}
			// todo
			// if(!$event->isNew && $event->blobId) // merge vevent into current blob

			$event->setValues((array)$obj); // title, description, start, duration, location, status, privacy
			$event->prodId = $prodId;
			$baseEvents[$event->uid] = $event;
			if($event->isNew() && isset($vevent->{'DTSTAMP'})) {
				try {$event->createdAt = $vevent->DTSTAMP->getDateTime();}
				catch(VObject\InvalidDataException $e) {$event->createdAt = new \DateTime();}
			}
			if(isset($vevent->{'LAST-MODIFIED'})) {
				try{$event->modifiedAt = $vevent->{'LAST-MODIFIED'}->getDateTime();}
				catch(VObject\InvalidDataException $e) {$event->modifiedAt = new \DateTime(); }
			}
			$event->showWithoutTime = !$vevent->DTSTART->hasTime();
			if(isset($vevent->SEQUENCE))
				$event->sequence = (int)$vevent->SEQUENCE->getValue();
			$event->privacy = array_flip(self::$privacyMap)[(string)$vevent->CLASS] ?? 'public';
			//$event->organizerEmail = str_replace('mailto:', '',(string)$vevent->ORGANIZER);

			if(!empty((string)$vevent->RRULE)) {
				$event->setRecurrenceRule(self::parseRrule($vevent->RRULE, $event));
				if(!empty($vevent->EXDATE)) {
					foreach ($vevent->EXDATE as $exdate) {
						$rId = $exdate->getDateTime()->format('Y-m-d\TH:i:s');
						if(!isset($event->recurrenceOverrides[$rId])) {
							$event->recurrenceOverrides[$rId] = new RecurrenceOverride($event);
						}
						$event->recurrenceOverrides[$rId]->setValues(['excluded'=>true]);
					}
				}
			}
			if(!empty((string)$vevent->CATEGORIES)) {
				$names = explode(',', $vevent->CATEGORIES);
				if(!empty($names))
					$event->categoryIdsByName($names);
			}
			if(isset($vevent->VALARM)) {
				$event->alerts = [];
				foreach ($vevent->VALARM as $valarm) {
					$a = (new Alert($event))->setValues([
						'action' => (string)$valarm->ACTION,
						'trigger' => ('DATE-TIME' === (string) $valarm->TRIGGER['VALUE']) ?
							['when' => $valarm->TRIGGER->getDateTime()] : // non-relative trigger
							['offset' => (string)$valarm->TRIGGER, 'relativeTo' => 'start']
					]);
					if(isset($valarm->ACKNOWLEDGED))
						$a->acknowledged = $valarm->ACKNOWLEDGED->getDateTime();
					$event->alerts[] = $a;
				}
			}
		}
		//Attach exceptions found in VCALENDAR
		foreach($exceptions as $props) {
			$uid = $props->uid;
			$recurrenceId = $props->recurrenceId;
			unset($props->recurrenceId, $props->uid);

			if(isset($baseEvents[$uid]) && $baseEvents[$uid]->isRecurring()) {
				if(!isset($baseEvents[$uid]->recurrenceOverrides[$recurrenceId])) {
					$baseEvents[$uid]->recurrenceOverrides[$recurrenceId] = new RecurrenceOverride($baseEvents[$uid]);
				}
				$baseEvents[$uid]->recurrenceOverrides[$recurrenceId]->patchProps($props);
			} else {
				// ICS contains exception but no base event.
				// You must be invited to a single occurrence
				$event->setValues((array)$props); // title, description, start, duration, location, status, privacy
				$event->prodId = $prodId;
				$event->uid = $uid;
				$event->recurrenceId = $recurrenceId;
			}
		}
		// All exceptions that do not have the recurrence ID are ignored here

		return $event;
	}

	static function makeBlob(CalendarEvent $event, string|null $data = null): Blob
	{
		$blob = Blob::fromString($data ?? $event->toVObject());
		$blob->type = 'text/calendar';
		// these must stay in sync!
		$blob->modifiedAt = $event->modifiedAt;
		$blob->name = $event->uid . '.ics';
		if(!$blob->save()) {
			throw new \Exception('could not save blob');
		}

		return $blob;
	}

	static private function parseAttendee($vattendee) {
		$key = str_ireplace('mailto:', '',(string)$vattendee);
		$principalIds = Principal::findIdsByEmail($key);

		$p = (object)['email' => $key];
		if(!empty($vattendee['EMAIL'])) $p->email = (string)$vattendee['EMAIL'];

		if(!empty($vattendee['CUTYPE'])) {
			$k = strtolower($vattendee['CUTYPE']);
			if($k == 'room') {
				$p->kind = 'location';
			} else {
				$p->kind = in_array($k, self::kinds) ? $k : 'individual';
			}
		}else {
			$p->kind = 'individual';
		}

		if(!empty($vattendee['CN'])) $p->name = (string)$vattendee['CN'];
		if(!empty($vattendee['RSVP'])) $p->expectReply = $vattendee['RSVP']->getValue() ? 1: 0; // bool
		$p->participationStatus = !empty($vattendee['PARTSTAT']) ? strtolower($vattendee['PARTSTAT']) : 'needs-action';
		if(!empty($vattendee['ROLE'])) {
			$map = array_flip(self::$roleMap);
			if (in_array((string)$vattendee['ROLE'], $map)) {
				$p->roles = [$map[(string)$vattendee['ROLE']] => true];
			}
		}
		return [
			$principalIds[0] ?? $key,
			$p
		];
	}

	static private function parseOccurrence(VEvent $vevent, stdClass $props) : stdClass {

		if(isset($vevent->DTSTART)) {
			$props->start = $vevent->DTSTART->getDateTime();
			if($vevent->DTSTART->hasTime() && !$vevent->DTSTART->isFloating()) {
				$props->timeZone = $props->start->getTimezone()->getName();
			}
		}
		go()->log($vevent->DESCRIPTION);
		//empty($vevent->DTSTART) ?: $props->start = $vevent->DTSTART->getDateTime()->format(DateTime::FORMAT_API_LOCAL);
		if(!empty($vevent->SUMMARY)) $props->title = (string)$vevent->SUMMARY;
		if(!empty($vevent->DESCRIPTION)) $props->description = str_replace('\n',"\n", $vevent->DESCRIPTION->getValue());
		if(!empty($vevent->LOCATION)) $props->location = (string)$vevent->LOCATION;
		if(!empty($vevent->STATUS)) {
			$status = strtolower($vevent->STATUS);
			if(in_array($status, ['confirmed', 'cancelled', 'tentative'])) {
				$props->status = $status;
			}
		//} else {
			// confirmed is already the db default and overrides do not always need to override the status.
			//$props->status = 'confirmed';
		}
		if(!empty($vevent->CLASS)) $props->privacy = array_flip(self::$privacyMap)[(string)$vevent->CLASS] ?? 'public';
		if(!empty($vevent->COLOR)) $props->color = (string)$vevent->COLOR;
		if(!empty($vevent->DURATION)) {
			$props->duration = (string)$vevent->DURATION;
		} else if (!empty($vevent->DTEND) && !empty($props->start)) {
			$props->duration = DateTime::intervalToISO($vevent->DTEND->getDateTime()->diff($props->start));
		} else {
			$props->duration = 'PT0S'; // Import events with no DTEND or DURATION anyway
		}

		if(isset($vevent->ATTENDEE)) {
			foreach ($vevent->ATTENDEE as $vattendee) {
				list($key,$attendee) = self::parseAttendee($vattendee);
				$props->participants[$key] = (array)$attendee;
			}
		}
		if(isset($vevent->ORGANIZER)) {
			$props->replyTo = str_ireplace('mailto:', '',(string)$vevent->ORGANIZER);

			list($key,$organizer) = self::parseAttendee($vevent->ORGANIZER);

			if(!isset($props->participants[$key] )) {
				// thunderbird sends organizer and participant but only the participants contains the correct "partstat".
				$props->participants[$key] = (array)$organizer;
			}
			$props->participants[$key]['roles']['owner'] = true;
		}

		return $props;
	}

	static public function makeRecurrenceIterator(CalendarEvent $event): VObject\Recur\RRuleIterator
	{
		return new VObject\Recur\RRuleIterator(self::toRrule($event), $event->start());
	}

	static private function parseRrule(VObject\Property\ICalendar\Recur $rule, CalendarEvent $event) {
		$parts = $rule->getParts();
		$values = (object)['frequency' => strtolower($parts['FREQ'])];
		if(isset($parts['INTERVAL']) && $parts['INTERVAL'] != 1) {
			$values->interval = intval($parts['INTERVAL']);
		}
		if(isset($parts['RSCALE'])) $values->rscale = strtolower($parts['RSCALE']);
		if(isset($parts['SKIP'])) $values->skip = strtolower($parts['SKIP']);
		if(isset($parts['WKST'])) $values->firstDayOfWeek = strtolower($parts['WKST']);
		if(!empty($parts['BYDAY'])) {
			$values->byDay = [];
			$days =array_map('trim',(array) $parts['BYDAY']);
			foreach($days as $day) {
				$bd = (object)['day' => strtolower(substr($day, -2))];
				if(strlen($day) > 2) {
					$bd->nthOfPeriod = substr($day, 0, -2);
				}
				$values->byDay[] = $bd;
			}
		}
		if(isset($parts['BYMONTHDAY'])) $values->byMonthDay = array_map('intval', (array) $parts['BYMONTHDAY']);
		if(isset($parts['BYMONTH'])) $values->byMonth = (array) $parts['BYMONTH']; // is string, could have L suffix
		if(isset($parts['BYYEARDAY'])) $values->byYearDay = array_map('intval', (array) $parts['BYYEARDAY']);
		if(isset($parts['BYWEEKNO'])) $values->byWeekNo = array_map('intval', (array) $parts['BYWEEKNO']);
		// skip byHour, byMinute, bySecond
		if(isset($parts['BYSETPOS'])) $values->bySetPosition = array_map('intval', (array) $parts['BYSETPOS']);
		if(isset($parts['COUNT'])) {
			$values->count = intval($parts['COUNT']);
		} elseif(isset($parts['UNTIL'])) {
			// could be "20240824T063000Z" or "20240824"
			if(strlen($parts['UNTIL']) > 10) { // has time
				// convert to localtime
				$isUtc = substr($parts['UNTIL'], -1,1) === 'Z';
				$dt = DateTime::createFromFormat('Ymd\THis', substr($parts['UNTIL'],0,15), new \DateTimeZone('etc/UTC'));

				$tz = $event->timeZone();
				if(isset($tz)) {
					$dt->setTimezone($tz);
				}

				$values->until = $dt->format('Y-m-d\TH:i:s');
			} else {
				// add dashes and append 0-time
				$values->until = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $parts['UNTIL']) . 'T00:00:00';
			}
		}
		return $values;
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
		return VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
	}

}
