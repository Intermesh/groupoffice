<?php
/**
 * @copyright (c) 2015, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\ErrorHandler;
use go\core\exception\JsonPointerException;
use go\core\fs\Blob;
use go\core\mail\Address;
use go\core\mail\Attachment;
use go\core\model\Acl;
use go\core\model\Principal;
use go\core\util\DateTime;
use go\core\util\StringUtil;
use Sabre\VObject;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use stdClass;

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
	 * @param CalendarEvent $event
	 * @param VCalendar|null $vcalendar The original vcalendar to sync to
	 * @return VCalendar
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
	 */
	static function toVObject(CalendarEvent $event, ?VCalendar $vcalendar = null): VCalendar
	{

		if($vcalendar === null) {
			$vcalendar = new VCalendar(['PRODID' => $event->prodId]);
		}

		$vevent = $vcalendar->add(self::toVEvent($vcalendar->createComponent('VEVENT'),$event));

		if(!$event->useDefaultAlerts && is_array($event->alerts)) {
			foreach($event->alerts as $id => $alert) {
				if(!empty($alert->offset)) {
					$vevent->add('VALARM', [
						'TRIGGER' => $alert->offset, // 15 minutes before the event
						'DESCRIPTION' => 'Alarm',
						'ACTION' => $alert->action,
					]);
				} else if (!empty($alert->when)) {
					$vevent->add('VALARM', [
						'TRIGGER' => $alert->when, // 15 minutes before the event
						'DESCRIPTION' => 'Alarm',
						'ACTION' => $alert->action,
					]);
				}
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

		return $vcalendar;
	}

	static function toInvite(string $method, CalendarEvent &$event) : VCalendar {
		$c = new VCalendar(['PRODID' => $event->prodId, 'METHOD' => $method]);
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

	 * @throws \DateMalformedStringException
	 */
	static function toVEvent(VEvent $vevent, CalendarEvent $event, ?string $recurrenceId = null): VEvent
	{

		if(!$recurrenceId) {
			$recurrenceId = $event->recurrenceId;
		}

		if(!$recurrenceId) {
			if(!empty($event->privacy) && $event->privacy !== 'public') $vevent->CLASS = self::$privacyMap[$event->privacy];
			if(!empty($event->modifiedAt)) $vevent->{'LAST-MODIFIED'} = $event->modifiedAt->format('Ymd\THis\Z'); // @todo: check if datetime must be UTC
			if(!empty($event->createdAt)) $vevent->DTSTAMP = $event->createdAt;
		} else {
			$rId = $vevent->add('RECURRENCE-ID', new DateTime($recurrenceId, $event->timeZone()));
			if(!empty($event->showWithoutTime)) {
				$rId['VALUE'] = 'DATE';
			}
		}
		if(!empty($event->uid)) $vevent->UID = $event->uid;
		if(!empty($event->title)) $vevent->SUMMARY = $event->title;
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
		if(!empty($event->description)) $vevent->DESCRIPTION = $event->description;
		if(!empty($event->location)) $vevent->LOCATION = $event->location;
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
			$attr['CUTYPE'] = strtoupper($participant->kind);
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
	 * @param CalendarEvent $event
	 * @return string \Sabre\VObject\Property\ICalendar\Recur $rule
	 * @throws \DateInvalidTimeZoneException
	 * @throws \DateMalformedStringException
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

	static function calendarEventFromFile(string $blobId) {
		$data = file_get_contents(Blob::buildPath($blobId));
		$splitter = new VObject\Splitter\ICalendar(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		while($vevent = $splitter->getNext()) {
			try {
				yield self::parseVObject($vevent, new CalendarEvent());
			} catch(\Throwable $e) {
				yield ['error'=>$e, 'vevent'=>$vevent];
			}
		}
	}

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
	 * @param int $calendarId
	 * @param CalendarEvent $event the event to insert the data into
	 * @return CalendarEvent updated or new Event if not found
	 */
	static public function parseVObject($vcalendar, CalendarEvent $event): CalendarEvent
	{

		if(is_string($vcalendar)) {
			$vcalendar = VObject\Reader::read($vcalendar, VObject\Reader::OPTION_FORGIVING);
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
				$obj->recurrenceId = $vevent->{'RECURRENCE-ID'}->getDateTime()->format('Y-m-d\TH:i:s');
				$exceptions[] = $obj;
				continue;
			}
			// todo
			// if(!$event->isNew && $event->blobId) // merge vevent into current blob

			$event->setValues((array)$obj); // title, description, start, duration, location, status, privacy
			$event->prodId = $prodId;
			$baseEvents[$event->uid] = $event;
			if($event->isNew())
				$event->createdAt = $vevent->DTSTAMP->getDateTime();
			if(isset($vevent->{'LAST-MODIFIED'}))
				$event->modifiedAt = $vevent->{'LAST-MODIFIED'}->getDateTime();
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
						$a->acknowledged = !!$valarm->ACKNOWLEDGED;
					$event->alerts[] = $a;
				}
			}
		}
		//Attach exceptions found in VCALENDAR
		foreach($exceptions as $props) {
			$uid = $props->uid;
			$recurrenceId = $props->recurrenceId;
			unset($props->recurrenceId, $props->uid);
			go()->debug("UID: " . $uid);
			go()->debug("RecurrenceID: " . $recurrenceId);

			if(isset($baseEvents[$uid]) && $baseEvents[$uid]->isRecurring()) {
				if(!isset($baseEvents[$uid]->recurrenceOverrides[$recurrenceId])) {
					$baseEvents[$uid]->recurrenceOverrides[$recurrenceId] = new RecurrenceOverride($baseEvents[$uid]);
				}
				$baseEvents[$uid]->recurrenceOverrides[$recurrenceId]->patchProps($props);

				go()->debug($baseEvents[$uid]->recurrenceOverrides[$recurrenceId]->isModified());
			} else {

				go()->debug("No recurring event");
				// ICS contains exception but no base event.
				// You must be invited to a single occurrence
				$event->setValues((array)$props); // title, description, start, duration, location, status, privacy
				$event->prodId = $prodId;

				// this leads to issues as the UID must stay the same for caldav etc.
				// But removing this leads to another issue. If a participant is invited for
				// a single occurrence it's added tto the whole series. Because
				//in Calendar::addEvent() the original base event is attached
				$event->uid = $uid;
				$event->recurrenceId = $recurrenceId;
			}
		}
		// All exceptions that do not have the recurrence ID are ignored here

		return $event;
	}

	static function makeBlob(CalendarEvent $event, string $data = null): Blob
	{
		$blob = Blob::fromString($data ?? ICalendarHelper::toVObject($event)->serialize());
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
		$principalId = Principal::find()->selectSingleValue('id')->where('email','=',$key)->orderBy(['entityTypeId'=>'ASC'])->single();
		$p = (object)['email' => $key];
		if(!empty($vattendee['EMAIL'])) $p->email = (string)$vattendee['EMAIL'];
		$p->kind = !empty($vattendee['CUTYPE']) ? strtolower($vattendee['CUTYPE']) : 'individual';
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
			$principalId ?? $key,
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
		//empty($vevent->DTSTART) ?: $props->start = $vevent->DTSTART->getDateTime()->format(DateTime::FORMAT_API_LOCAL);
		if(!empty($vevent->SUMMARY)) $props->title = (string)$vevent->SUMMARY;
		if(!empty($vevent->DESCRIPTION)) $props->description = (string)$vevent->DESCRIPTION;
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
			$days = $parts['BYDAY'];
			foreach($days as $day) {
				$bd = (object)['day' => strtolower(substr($day, -2))];
				if(strlen($day) > 2) {
					$bd->nthOfPeriod = substr($day, 0, -2);
				}
				$values->byDay[] = $bd;
			}
		}
		if(isset($parts['BYMONTHDAY'])) $values->byMonthDay = array_map('intval',explode(',', $parts['BYMONTHDAY']));
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
		return VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);
	}

}
