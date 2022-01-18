<?php
namespace go\core\util;

use DateTimeInterface;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Recur\RRuleIterator;

class Recurrence extends RRuleIterator {
	public $interval = 1;

	/**
	 * @var array {day:string, nthOfPeriod: int}
	 * day = "mo"|"tu"|"we"|"th"|"fr"|"sa"|"su"
	 * nthOfPeriod can be negative -1 is last day of period
	 */
	public $byDay;
	/**
	 * @var int[]
	 */
	public $byMonthDay;
	/**
	 * @var string[]
	 */
	public $byMonth;
	/**
	 * @var int[]
	 */
	public $byYearDay;
	/**
	 * @var int[]
	 */
	public $byWeekNo;

	public $count;
	public $until;

	/**
	 * Recurrence constructor.
	 * @param string $rrule RRULE FORMAT AS IN
	 * @param DateTimeInterface $start
	 * @throws InvalidDataException
	 */
	public function __construct(DateTimeInterface $start) {
		$this->startDate = $start;
		$this->currentDate = clone $this->startDate;
	}

	/**
	 * @throws InvalidDataException
	 */
	public static function fromString(string $rrule, DateTimeInterface $start) : Recurrence
	{
		$r = new Recurrence($start);
		$r->parseRRule($rrule);

		return $r;
	}

	/**
	 * Create JSON rrule from this iterator object (saved encoded to database)
	 * @param bool $allDay
	 * @return array
	 */
	public function toArray(bool $allDay = true): array
	{
		$data = [];
		foreach(['frequency', 'interval', 'count',
				  'byMonth', 'byYearDay', 'byWeekNo', 'byMonthDay'] as $key) {
			if(!empty($this->{$key})) {
				$data[$key] = $this->{$key};
			}
		}
		if(!empty($this->bySetPos)) {
			$data['bySetPosition'] = $this->bySetPos;
		}
		if(!empty($this->until)) {
			$data['until'] = $this->until->format($allDay ? "Ymd" : "Ymd\THis\Z");
		}
		if ($this->byDay) {
			$data['byDay'] = [];
			foreach ($this->byDay as $day) {
				$nthOfPeriod = substr($day, 0, -2);
				$day = ['day' => substr($day, -2)];
				if(!empty($nthOfPeriod)) {
					$day['nthOfPeriod'] = $nthOfPeriod;
				}
				$data['byDay'][] = $day;
			}
		}
		return $data;
	}

	/**
	 * Create rrule iterator from JSON rule format
	 *
	 * @param array $rule json data
	 * @param $start DateTimeInterface start of task
	 * @return Recurrence
	 */
	static function fromArray(array $rule, DateTimeInterface $start): Recurrence
	{
		$me = new self($start);
		foreach(['frequency', 'interval', 'count',
					  'byMonth', 'byYearDay', 'byWeekNo', 'byMonthDay'] as $key) {
			if(!empty($rule[$key])) {
				$me->{$key} = $rule[$key];
			}
		}
		if(isset($rule->until)) {
			$me->until = DateTimeParser::parse($rule['until'], $me->startDate->getTimezone());
			if ($me->until < $me->startDate) {
				$me->until = $me->startDate;
			}
		}

		if(isset($rule['bySetPosition'])) {
			$me->bySetPos = $rule['bySetPosition'];
		}

		if(!empty($rule['byDay'])) {
			foreach($rule['byDay'] as $key => $nday) {
				if(is_object($nday) || is_array($nday)) {
					$nday = (array)$nday;
					$position = $nday['nthOfPeriod'] ?? '';
					$me->byDay[$key] = $position . strtoupper($nday['day']);
				} else {
					$me->byDay[$key] = $nday;
				}
			}
		}
		return $me;
	}

	/**
	 * Generate RRULE
	 * @param boolean $allDay
	 * @return string
	 */
	public function toString(bool $allDay = true): string
	{
		$rrule = ["FREQ=".strtoupper($this->frequency)];
		if($this->interval) {
			$rrule[] = "INTERVAL=".$this->interval;
		}
		if (!empty($this->until)) {
			$rrule[] = "UNTIL=".$this->until->format($allDay ? "Ymd" : "Ymd\THis\Z");
		}
		if (isset($this->count)) {
			$rrule[] = "COUNT=" . $this->count;
		}
		if (isset($this->interval)) {
			$rrule[] = "INTERVAL=" . $this->interval;
		}
		foreach(['byDay', 'byYearDay', 'byWeekNo', 'byMonthDay', 'byMonth', 'bySetPos'] as $k) {
			if(!empty($this->{$k})) {
				$rrule[] = strtoupper($k)."=" .implode(',',$this->{$k});
			}
		}
		return implode(";", $rrule);
	}

}