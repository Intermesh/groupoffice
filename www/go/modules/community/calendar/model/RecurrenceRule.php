<?php

/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\orm\Property;

/**
 * This class defines how an Event needs to be recurred
 *
 * @property Event $event The event this rule applies to. 
 * @property-read Datetime $startAt time of first occurence
 * @property int $occurences The amount of times the event will recur.
 * @property string $rrule the rrule string is text @see setRRule() and getRRule()
 * @property-read bool $isInfinite when there is no end to the recurring rule
 * @property-read int $weekStart on what day the week start occuring to the localization (0 = sunday, 1 = monday, etc)
 * 
 * @property Event[] $exceptions exceptions of the event indexed by exception date
 */
class RecurrenceRule extends Property {

	/**
	 * 
	 * @var int
	 */							
	public $eventId;

	/**
	 * see constants.
	 * @var string
	 */							
	public $frequency;

	/**
	 * till when the event will recur.
	 * @var \DateTime
	 */							
	public $until;

	/**
	 * 
	 * @var int
	 */							
	public $count;

	/**
	 * recur every nth time this rule specifies. eg once every 3 weeks instead of every week
	 * @var int
	 */							
	public $interval = 0;

	/**
	 * binary integer were last bit is 'monday' true
	 * @var int
	 */							
	public $byDay;

	/**
	 * binary integer were last bit is 'january' true
	 * @var int
	 */							
	public $byMonth;

	/**
	 * 
	 * @var int
	 */							
	public $byYearday;

	/**
	 * binary integer were last bit is '1st' true
	 * @var int
	 */							
	public $byMonthday;

	/**
	 * binary integer were last bit is '00:xx' true
	 * @var int
	 */							
	public $byHour;

	/**
	 * binary integer were last bit is 'xx:00' true
	 * @var int
	 */							
	public $byMinute;

	/**
	 * binary integer were last bit is 'xx:xx:00' true (only implemented for display)
	 * @var int
	 */							
	public $bySecond;

	/**
	 * binary integer were last bit is '366' true and the first bit '-366' specified the nth occurence
	 * @var int
	 */							
	public $bySetPos = 0;

	const Secondly = 'S';
	const Minutely = 'I';
	const Hourly = 'H';
	const Daily = 'D';
	const Weekly = 'W';
	const Monthly = 'M';
	const Annually = 'Y';
	
	/**
	 * RRule parser that is internally used
	 * @see getIterator()
	 * @var RRuleIterator
	 */
	private $iterator = null;

	/**
	 * the calendar event we are calculation occurrences for
	 * @var CalendarEvent
	 */
	protected $forAttendee = null;

	// DEFINITION
	public static function tableName() {
		return 'calendar_recurrence_rule';
	}
	protected static function defineRelations() {
		self::hasOne('event', Event::class, ['eventId' => 'id']);
	}

	protected function internaValidate() {

		if($this->frequency === null && !$this->markDeleted) {
			return true; // save nothing when there is no frequency
		}
		//return parent::internalValidate();
	}


	public function forAttendee($calendarEvent) {
		$this->forAttendee = $calendarEvent;
	}

	/**
	 * Close the series including this day
	 * @param DateTime $date
	 */
	public function stopBefore(DateTime $date) {
		$this->until = clone $date;
		$this->until->sub(new \DateInterval('PT1S')); // until is in an inclusive manner
	}

	/**
	 * When the RRule has no end time
	 * @return bool true when this recurs forever
	 */
	public function isInfinite() {
		return $this->getIterator()->isInfinite();
	}

	private function getIterator() {
		if(empty($this->iterator)) {
			$this->iterator = ICalendarHelper::makeRecurrenceIterator($this);
		}
		return $this->iterator;
	}

	/**
	 *
	 * @param Datetime $start
	 * @param Datetime $end
	 * @return Datetime[Event]
	 */
	public function getOccurences($start, $end) {
		if($this->forAttendee === null) {
			throw new \Exception('Can get occurrences, select Attendee first');
		}
		$result = [];
		$this->getIterator()->fastForward($start);
		while($recurrenceId = $this->getIterator()->current()) {
			if($recurrenceId > $end)
				break;
			$calEvent = new CalendarEvent();
			$calEvent->setValues($this->forAttendee->toArray());
			$calEvent->addRecurrenceId($recurrenceId);
			$result[$recurrenceId->format('Y-m-d').$this->forAttendee->calendarId.'-'.$this->eventId] = $calEvent;
			$this->getIterator()->next();
		}
		$overrides = $this->event->overrides($start, $end);
		foreach($overrides as $override) {
			$id = $override->recurrenceId->format('Y-m-d').$this->forAttendee->calendarId.'-'.$override->eventId;
			if(isset($result[$id])) {
				if($override->isPatched()) { // PATCH
					// @todo: move the patched to the none recurring list.
					// The patch could be outside $start - $end timespan
					$result[$id]->instance = $override;
					$result[$id]->event = $override->patch();
				} else { //EXDATE
					unset($result[$id]);
				}
			} else { 
				// RDATE, not yet supported
			}
		}
		return $result;
	}
	
}