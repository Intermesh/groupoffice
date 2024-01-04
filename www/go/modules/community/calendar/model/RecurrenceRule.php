<?php

/*
 * @copyright (c) 2016, Intermesh BV http://www.intermesh.nl
 * @author Michael de Hart <mdhart@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
namespace go\modules\community\calendar\model;

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
	
}