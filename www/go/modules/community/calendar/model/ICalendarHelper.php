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
		if(!empty($event->color)) $props['COLOR'] = $event->color;
		if(!empty($event->categoryIds)) $props['CATEGORIES'] = implode(',',$event->categoryNames());


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

			$event = self::parseOccurrence($vevent, $event); // title, description, start, duration, location, status, privacy
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
			$event->privacy = array_flip(self::$privacyMap)[$vevent->CLASS] ?? 'public';
			//$event->organizerEmail = str_replace('mailto:', '',(string)$vevent->ORGANIZER);
			if(isset($vevent->ORGANIZER)) {
				$organizer = self::parseAttendee(new Participant($event), $vevent->ORGANIZER);
				$event->replyTo = str_replace('mailto:', '',(string)$vevent->ORGANIZER);
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
		if(!empty($vattendee['EMAIL'])) $p->email = $vattendee['EMAIL'];
		$p->kind = !empty($vattendee['CUTYPE']) ? strtolower($vattendee['CUTYPE']) : 'individual';
		if(!empty($vattendee['CN'])) $p->name = $vattendee['CN'];
		if(!empty($vattendee['ROLE'])) $p->roles[] = $vattendee['ROLE'];
		if(!empty($vattendee['RSVP'])) $p->expectReply = $vattendee['RSVP']->getValue() ? 1: 0; // bool
		$p->participationStatus = !empty($vattendee['PARTSTAT']) ? strtolower($vattendee['PARTSTAT']) : 'needs-action';
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
		if(!empty($vevent->COLOR)) $props->color = (string)$vevent->COLOR;
		if(!empty($vevent->DURATION)) {
			$props->duration = (string)$vevent->DURATION;
		} else if (!empty($vevent->DTEND) && !empty($props->start)) {
			$props->duration = DateTime::intervalToISO($vevent->DTEND->getDateTime()->diff($props->start));
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
