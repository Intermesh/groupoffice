<?php

/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

use go\core\util\DateTime;

class RecurrenceRule {

	const Yearly = 'yearly'; // not sure why spec doesn't use annualy
	const Monthly = 'monthly';
	const Weekly = 'weekly';
	const Daily = 'daily';
	const Hourly = 'hourly';
	const Minutely = 'minutely';
	const Secondly = 'secondly';

	public $frequency; // Consts

	public $interval = 1;

	/** @var string ""mo"|"tu"|"we"|"th"|"fr"|"sa"|"su"" */
	public $firstDayOfWeek = 'mo';

	/** @var object[] {day, nthOfPeriod} */
	public $byDay;

	/** @var int[]  */
	public $byMonthDay;

	/** @var string[] */
	public $byMonth;

	/** @var int[]  */
	public $byYearDay;

	/** @var int[]  */
	public $byWeekNo;

	/** @var int[]  */
	public $bySetPosition;

	/** @var int */
	public $count = null;

	/** @var string LocalDate */
	public $until = null;

	static function expand(CalendarEvent $p, string $from, string $until) {
		$it = ICalendarHelper::makeRecurrenceIterator((object)[
			'start'=>$p->start,
			'recurrenceRule'=> $p->getRecurrenceRule(),
			'timezone'=>$p->timeZone
		]);
		$it->fastForward(new DateTime($from));
		if(!empty($p->lastOccurrence)) {
			$until = min($until, $p->lastOccurrence->format('Y-m-d'));
		}
		$maxDate = new \DateTime($until);
		while ($it->valid() && $it->current() < $maxDate) {
			$recurrenceId = $it->current();
			$instance = clone $p;
			$instance->utcStart = $recurrenceId;
			$o = @$p->recurrenceOverrides[$recurrenceId->format('Y-m-d\TH:i:s')];
			$duration = $p->duration;
			if(isset($o)) {
				if($o->excluded) {
					$it->next();
					continue;
				}
				if($o->start) {
					$instance->utcStart = new \DateTime($o->start);
				}
				if($o->duration) {
					$duration = $o->duration;
				}
			}

			$end = clone $instance->utcStart;
			$end->add(new \DateInterval($duration));
			$instance->utcEnd = $end;

			yield $recurrenceId->format('Y-m-d\TH:i:s') => $instance;
			$it->next();
		}
	}

	static function humanReadable(CalendarEvent $event) {

		$t = fn($str) => go()->t($str,'community','calendar');
		$frequencies = [
			'daily' => [$t("day"), $t('days'), $t('Daily')],
			'weekly' => [$t("week"), $t('weeks'), $t('Weekly')],
			'monthly' => [$t("month"), $t('months'), $t('Monthly')],
			'yearly' => [$t("year"), $t('years'), $t('Annually')]
		];
		$suffix = [$t("first"),$t("second"),$t("third"),$t("fourth")];
		$dayNumbers = ['su'=>0,'mo'=>1,'tu'=>2,'we'=>3,'th'=>4,'fr'=>5,'sa'=>6];

		$rr = $event->getRecurrenceRule();
		$start = $event->start();

		if (!$rr || !isset($rr->frequency)) {
			return $t('Not recurring');
		}
		$record = $frequencies[$rr->frequency];
		if (!$record) {
			return "Unsupported frequency: " . $rr->frequency;
		}
		$str = $record[2];
		if (isset($rr->interval) && $rr->interval !== 1) {
			$str = strtolower($t('Every')) . ' ' . $rr->interval . ' ' . $record[$rr->interval > 1 ? 1 : 0];
		}
		if (!empty($rr->byDay)) {
			$days = [];
			$workdays = (count($rr->byDay) === 5);
			foreach ($rr->byDay as $day) {
				if ($day->day == 'sa' || $day->day == 'su') {
					$workdays = false;
				}
				$nthDay = '';
				if (isset($day->nthOfPeriod)) {
					$nthDay = $t('the') . ' ' . ($suffix[$day->nthOfPeriod] || $t('last')) . ' ';
				}
				$days[] = $nthDay . $t('full_days')[$dayNumbers[$day->day]];
			}
			if ($workdays) {
				$days = [$t('Workdays')];
			}
			$str .= (' ' . $t('at ') . implode(', ', $days));
		} elseif ($rr->frequency == 'weekly') {
			$str .= (' ' . $t('at ') . $start->format('l'));
		}
		if (isset($rr->byMonthDay)) {
			$str .= (' ' . $t('at day') . ' ' . implode(', ', $rr->byMonthDay));
		}

		if (!empty($rr->count)) {
			$str .= ', ' . $rr->count . ' ' . $t('times');
		}
		if (!empty($rr->until)) {
			$str .= ', ' . $t('until') . ' ' . (new DateTime($rr->until))->format("d-m-Y");
		}

		return $t('Repeats'). ' '.$str;
	}
}