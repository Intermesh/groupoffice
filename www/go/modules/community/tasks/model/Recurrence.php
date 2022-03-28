<?php
namespace go\modules\community\tasks\model;

use DateTimeInterface;
use Sabre\VObject\DateTimeParser;
use Sabre\VObject\InvalidDataException;

class Recurrence extends \Sabre\VObject\Recur\RRuleIterator {
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
//	public $byHour;
//	public $byMinute;
//	public $bySecond;
	/**
	 * @var int[]
	 * The occurrences within the recurrence interval to include in the final results.
	 * Negative values offset from the end of the list of occurrences.
	 */
	public $bySetPosition;
	public $count;
	public $until;

	/**
	 * Recurrence constructor.
	 * @param string $rrule RRULE FORMAT AS IN
	 * @param DateTimeInterface $start
	 * @throws InvalidDataException
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct($rrule, DateTimeInterface $start) {
		$this->startDate = $start;
		if($rrule)
			$this->parseRRule($rrule);
		$this->currentDate = clone $this->startDate;
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
				$dayArr = ['day' => substr($day, -2)];
				$nthOfPeriod = substr($day, 0, -2);
				if(!empty($nthOfPeriod)) {
					$dayArr['nthOfPeriod'] = $nthOfPeriod;
				}
				$data['byDay'][] = $dayArr;
			}
		}
		return $data;
	}

	/**
	 * Create rrule itterator from JSON rule format
	 * @param array $rule json data
	 * @param DateTimeInterface $start start of task
	 * @return Recurrence
	 * @throws InvalidDataException
	 */
	static function fromArray(array $rule, DateTimeInterface $start): Recurrence
	{
		$me = new self(null, $start);
		foreach(['frequency', 'interval', 'count',
					  'byMonth', 'byYearDay', 'byWeekNo', 'byMonthDay'] as $key) {
			if(!empty($rule[$key])) {
				$me->{$key} = $rule[$key];
			}
		}
		if(isset($rule['until'])) {
			$me->until = DateTimeParser::parse(str_replace('-','', $rule['until']) , $me->startDate->getTimezone());
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