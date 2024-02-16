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

	/** @var NDay[] {day, nthOfPeriod} */
	public $byDay;

	/** @var int[]  */
	public $byMonthday;

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
			$o = @$p->recurrenceOverrides[$recurrenceId];
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

			$end = new DateTime($instance->utcStart);
			$end->add(new \DateInterval($duration));
			$instance->utcEnd = $end;

			yield $recurrenceId->format('Y-m-d\TH:i:s') => $instance;
			$it->next();
		}
	}
}