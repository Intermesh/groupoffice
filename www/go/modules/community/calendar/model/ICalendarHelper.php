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
use go\core\model\Principal;
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
	 * @param CalendarEvent $event
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

		if(!$event->useDefaultAlerts && is_array($event->alerts)) {
			foreach($event->alerts as $id => $alert) {
				$vevent->add('VALARM', [
					'TRIGGER' => $alert->offset, // 15 minutes before the event
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
						self::addExceptionVEvent($vcalendar, $event, $patch, $recurrenceId);

//						$props = self::toVEvent((new CalendarEvent())->setValues($patch->toArray()), true);
//						$props['UID'] = $event->uid;
//						//$props['RECURRENCE-ID'] = new DateTime($recurrenceId, $event->timeZone());
//						$exVEvent = $vcalendar->add('VEVENT', $props);
//						$rId = $exVEvent->add('RECURRENCE-ID', new DateTime($recurrenceId, $event->timeZone()));
//						if($event->showWithoutTime) {
//							$rId['VALUE'] = 'DATE';
//							if(isset($props['DTSTART'])) $vevent->DTSTART['VALUE'] = 'DATE';
//							if(isset($props['DTEND'])) $vevent->DTEND['VALUE'] = 'DATE';
//						}
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
		if(!empty($event->color)) $props['COLOR'] = $event->color;
		if(!empty($event->categoryIds)) $props['CATEGORIES'] = implode(',',$event->categoryNames());
		return $props;
	}
	static function addExceptionVEvent(&$vcalendar, $event, $patch, $recurrenceId) {
		$props = ['UID' => $event->uid];
		if(isset($patch->title)) $props['SUMMARY'] = $patch->title;
		if(isset($patch->start)) $props['DTSTART'] = new DateTime($patch->start, $event->timeZone());
		if(isset($patch->duration)) {
			$end = new DateTime($patch->start,$event->timeZone());
			$end->add(new \DateInterval($event->duration));
			$props['DTEND'] = $end;
		}
		if(isset($patch->description)) $props['DESCRIPTION'] = $patch->description;
		if(isset($patch->location)) $props['LOCATION'] = $patch->location;
		if(isset($patch->color)) $props['COLOR'] = $patch->color;
		//if(isset($patch->categoryIds)) $props['CATEGORIES'] = implode(',',$patch->categoryNames());

		$exVEvent = $vcalendar->add('VEVENT', $props);
		$rId = $exVEvent->add('RECURRENCE-ID', new DateTime($recurrenceId, $event->timeZone()));
		if($event->showWithoutTime) {
			$rId['VALUE'] = 'DATE';
			if(isset($props['DTSTART'])) $exVEvent->DTSTART['VALUE'] = 'DATE';
			if(isset($props['DTEND'])) $exVEvent->DTEND['VALUE'] = 'DATE';
		}
		if(isset($patch->participants)) {
			foreach($patch->participants as $p) {
				$exVEvent->add(...self::toAttendee((new Participant($event))->setValues((array)$p)));
			}
		}
		//loop attendees
		return $exVEvent;
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
		$recurrenceRule = isset($event->recurrenceRule) ? $event->recurrenceRule :  $event->getRecurrenceRule();
		$rule = [];
		foreach(self::$ruleMap as $iKey => $jKey) {
			if(!empty($recurrenceRule->{$jKey})) {
				$val = $recurrenceRule->{$jKey};
				if($jKey == 'until') {
					if(strlen($val) > 10) { // with time
						$tz = $event->timeZone ? new \DateTimeZone($event->timeZone) : null;;
						$dt = DateTime::createFromFormat('Y-m-d H:i:s', $val, $tz);
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
	//	$vCalendar = VObject\Reader::read(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING);
		$splitter = new VObject\Splitter\ICalendar(StringUtil::cleanUtf8($data), VObject\Reader::OPTION_FORGIVING + VObject\Reader::OPTION_IGNORE_INVALID_LINES);
		if($vevent = $splitter->getNext())
			return self::parseVObject($vevent, $event ?? new CalendarEvent());
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
		$prodId = $vcalendar->PRODID;

		foreach($vcalendar->VEVENT as $vevent) {
			if(!empty($vevent->{'RECURRENCE-ID'})) {
				$exceptions[] = self::parseOccurrence($vevent, (object)[
					'recurrenceId' => $vevent->{'RECURRENCE-ID'}->getJsonValue()[0],
					'uid' => (string)$vevent->UID // unset after merge
				]);
				continue;
			}
			// todo
			// if(!$event->isNew && $event->blobId) // merge vevent into current blob

			$event = self::parseOccurrence($vevent, $event); // title, description, start, duration, location, status, privacy
			$event->prodId = $prodId;
			$event->uid = (string)$vevent->UID;
			$baseEvents[$event->uid] = $event;
			if(!$vevent->DTSTART->isFloating())
				$event->timeZone = $event->start->getTimezone()->getName();
			if($event->isNew())
				$event->createdAt = $vevent->DTSTAMP->getDateTime();
			if(isset($vevent->{'LAST-MODIFIED'}))
				$event->modifiedAt = $vevent->{'LAST-MODIFIED'}->getDateTime();
			$event->showWithoutTime = !$vevent->DTSTART->hasTime();
			if(isset($vevent->SEQUENCE))
				$event->sequence = (int)$vevent->SEQUENCE->getValue();
			$event->privacy = array_flip(self::$privacyMap)[(string)$vevent->CLASS] ?? 'public';
			//$event->organizerEmail = str_replace('mailto:', '',(string)$vevent->ORGANIZER);

			if(isset($vevent->attendee)) {
				foreach ($vevent->ATTENDEE as $vattendee) {
					list($key,$attendee) = self::parseAttendee(new Participant($event), $vattendee);
					$event->participants[$key] = $attendee;
				}
			}
			if(isset($vevent->ORGANIZER)) {
				list($key,$organizer) = self::parseAttendee(new Participant($event), $vevent->ORGANIZER);
				$event->replyTo = str_replace('mailto:', '',(string)$vevent->ORGANIZER);
				$organizer->setRoles(['owner' => true]);
				$event->participants[$key] = $organizer;
			}

			if(!empty((string)$vevent->RRULE)) {
				$event->setRecurrenceRule(self::parseRrule($vevent->RRULE, $event));
				if(!empty($vevent->EXDATE)) {
					foreach ($vevent->EXDATE as $exdate) {
						$event->recurrenceOverrides[$exdate->getJsonValue()[0]] = null;
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
						'action'=> $valarm->ACTION,
						'trigger'=> ['offset' => $valarm->TRIGGER, 'relativeTo'=>'start']
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
			if(isset($baseEvents[$uid]) && $baseEvents[$uid]->isRecurring()) {
				$recurrenceId = $props->recurrenceId;
				unset($props->recurrenceId, $props->uid);
				$baseEvents[$uid]->recurrenceOverrides[$recurrenceId] = (new RecurrenceOverride($event))->setValues((array)$props);
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
		$key = str_replace('mailto:', '',(string)$vattendee);
		$principalId = Principal::find()->selectSingleValue('id')->where('email','=',$key)->orderBy(['entityTypeId'=>'ASC'])->single();
		$p->setValues([
			'email'=>$key
		]);
		if(!empty($vattendee['EMAIL'])) $p->email = (string)$vattendee['EMAIL'];
		$p->kind = !empty($vattendee['CUTYPE']) ? strtolower($vattendee['CUTYPE']) : 'individual';
		if(!empty($vattendee['CN'])) $p->name = (string)$vattendee['CN'];
		if(!empty($vattendee['RSVP'])) $p->expectReply = $vattendee['RSVP']->getValue() ? 1: 0; // bool
		$p->participationStatus = !empty($vattendee['PARTSTAT']) ? strtolower($vattendee['PARTSTAT']) : 'needs-action';
		if(!empty($vattendee['ROLE'])) {
			$map = array_flip(self::$roleMap);
			if (in_array((string)$vattendee['ROLE'], $map)) {
				$p->setRoles([$map[(string)$vattendee['ROLE']] => true]);
			}
		}
		return [$principalId ?? $key,$p];
	}

	static private function parseOccurrence($vevent, $props) {

		if(isset($vevent->DTSTART)) {
			$props->start = $vevent->DTSTART->getDateTime();
			if($vevent->DTSTART->hasTime()) {
				$props->timeZone = !empty($vevent->DTSTART['TZID']) ? $vevent->DTSTART['TZID'] : 'Etc/UTC';
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
		return $props;
	}

	static public function makeRecurrenceIterator($event) {
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
				$values->until = $dt->format('Y-m-d H:i:s');
			} else {
				// add dashes and append 0-time
				$values->until = preg_replace('/(\d{4})(\d{2})(\d{2})/', '$1-$2-$3', $parts['UNTIL']) . ' 00:00:00';
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
