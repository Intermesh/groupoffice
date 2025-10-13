<?php
namespace go\modules\community\calendar\model;
use DateTime;
use DateTimeZone;
use Sabre\VObject\Document;

class VTimezone extends Document {

	static public $defaultName = 'VTIMEZONE';

	static public $propertyMap = array(
		'RRULE' => 'Sabre\\VObject\\Property\\ICalendar\\Recur',
	);

	public function __construct(string $timezone) {

		parent::__construct();

		$tz = new DateTimeZone($timezone);
		$transitions = $tz->getTransitions();

		$start_of_year = mktime(0, 0, 0, 1, 1);

		$to = $tz->getOffset(new DateTime()) / 3600;

		if ($to < 0) {
			if (strlen($to) == 2)
				$to = '-0' . ($to * -1);
		}else {
			if (strlen($to) == 1)
				$to = '0' . $to;

			$to = '+' . $to;
		}

		$STANDARD_TZOFFSETFROM = $STANDARD_TZOFFSETTO = $DAYLIGHT_TZOFFSETFROM = $DAYLIGHT_TZOFFSETTO = $to;

		$STANDARD_RRULE = '';
		$DAYLIGHT_RRULE = '';

		for ($i = 0, $max = count($transitions) - 1; $i < $max; $i++) {
			if ($transitions[$i]['ts'] > $start_of_year) {

				$weekday1 = $this->getDay($transitions[$i]['time']);
				$weekday2 = $this->getDay($transitions[$i+1]['time']);

				if($transitions[$i]['isdst']){
					$dst_start = $transitions[$i];
					$dst_end = $transitions[$i + 1];
				}else
				{
					$dst_end = $transitions[$i];
					$dst_start = $transitions[$i + 1];
				}

				$STANDARD_TZOFFSETFROM = $this->formatVtimezoneTransitionHour($dst_start['offset'] / 3600);
				$STANDARD_TZOFFSETTO = $this->formatVtimezoneTransitionHour($dst_end['offset'] / 3600);

				$DAYLIGHT_TZOFFSETFROM = $this->formatVtimezoneTransitionHour($dst_end['offset'] / 3600);
				$DAYLIGHT_TZOFFSETTO = $this->formatVtimezoneTransitionHour($dst_start['offset'] / 3600);

				$DAYLIGHT_RRULE = "FREQ=YEARLY;BYDAY=$weekday1;BYMONTH=" . date('n', $dst_start['ts']);
				$STANDARD_RRULE = "FREQ=YEARLY;BYDAY=$weekday2;BYMONTH=" . date('n', $dst_end['ts']);


				break;
			}
		}

		$this->tzid = $tz->getName();

		if(!empty($STANDARD_RRULE)) {
			$rrule = new \Sabre\VObject\Recur\RRuleIterator($STANDARD_RRULE, new DateTime('1970-01-01 '.substr($STANDARD_TZOFFSETFROM, 1).':00'));
			$rrule->next();
			$rrule->next();

			$this->add($this->createComponent("standard", array(
				'dtstart'=>$rrule->current()->format('Ymd\THis'),
				'rrule' => $STANDARD_RRULE,
				'tzoffsetfrom'=>$STANDARD_TZOFFSETFROM. "00",
				'tzoffsetto' => $STANDARD_TZOFFSETTO . "00"
			)));
		}

		if(!empty($DAYLIGHT_RRULE)) {
			$rrule = new \Sabre\VObject\Recur\RRuleIterator($DAYLIGHT_RRULE, new DateTime('1970-01-01 '.substr($DAYLIGHT_TZOFFSETFROM, 1).':00'));
			$rrule->next();
			$rrule->next();

			$this->add($this->createComponent("daylight", array(
				'dtstart'=>$rrule->current()->format('Ymd\THis'),
				'rrule'=>$DAYLIGHT_RRULE,
				'tzoffsetfrom'=>$DAYLIGHT_TZOFFSETFROM. "00",
				'tzoffsetto' => $DAYLIGHT_TZOFFSETTO . "00"
			)));
		}

	}

	private function getDay($date){

		$time = new DateTime($date);
		$dayOfMonth = $time->format('j');
		$nth = ceil($dayOfMonth/7);
		if($nth>2)
			$weekday = '-1SU';
		else
			$weekday = $nth.'SU';

		return $weekday;
	}

	private function formatVtimezoneTransitionHour($hour){

		if($hour < 0){
			$prefix = '-';
			$hour = $hour * -1;
		}else
		{
			$prefix = '+';
		}

		if($hour<10)
			$hour = '0'.$hour;

		return $prefix.$hour;
	}

}

