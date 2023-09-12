<?php
// 2G RDIS

$input_holidays['fix']['01-01'] = '元日';
$input_holidays['fix']['02-11'] = '建国記念の日';
$input_holidays['fix']['02-23'] = '天皇誕生日';
$input_holidays['fix']['04-29'] = '昭和の日';
$input_holidays['fix']['05-03'] = '憲法記念日	';
$input_holidays['fix']['05-04'] = 'みどりの日';
$input_holidays['fix']['05-05'] = 'こどもの日';
$input_holidays['fix']['08-11'] = '山の日';
$input_holidays['fix']['11-03'] = '文化の日';
$input_holidays['fix']['11-23'] = '勤労感謝の日';

function getSpringHoliDay($year) {
	return floor(20.8431 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}
function getAutumnHoliDay($year) {
	return floor(23.2488 + 0.242194 * ($year - 1980) - floor(($year - 1980) / 4));
}

$spDay = '3-'.getSpringHoliDay($year);
$amDay = '9-'.getAutumnHoliDay($year);
$input_holidays['fix'][$spDay] = '春分の日';
$input_holidays['fix'][$amDay] = '秋分の日';
$yspDay = $year.'-'.$spDay;
$yamDay = $year.'-'.$amDay;

$agD = (new DateTime('third mon of September '.$year))->format('d');
$amD = getAutumnHoliDay($year);
$amg = $amD - $agD;
$amgx = '09-'.($amD-1);
if($amg == 2) {
	$input_holidays['fix'][$amgx] = '休日';
}

//

$input_holidays['fn'][] = array('元日(振替休日)',array('GOHolidaysJP', 'newyear'));
$input_holidays['fn'][] = array('建国記念の日(振替休日)',array('GOHolidaysJP', 'nationalFoundDay'));
$input_holidays['fn'][] = array('天皇誕生日(振替休日)',array('GOHolidaysJP', 'empeBirthDay'));
$input_holidays['fn'][] = array('昭和の日(振替休日)',array('GOHolidaysJP', 'showaDay'));
$input_holidays['fn'][] = array('憲法記念日(振替休日)',array('GOHolidaysJP', 'constMemDay'));
$input_holidays['fn'][] = array('みどりの日(振替休日)',array('GOHolidaysJP', 'greenDay'));
$input_holidays['fn'][] = array('こどもの日(振替休日)',array('GOHolidaysJP', 'childDay'));
$input_holidays['fn'][] = array('山の日(振替休日)',array('GOHolidaysJP', 'mountDay'));
$input_holidays['fn'][] = array('文化の日(振替休日)',array('GOHolidaysJP', 'cultureDay'));
$input_holidays['fn'][] = array('勤労感謝の日(振替休日)',array('GOHolidaysJP', 'labthankDay'));
//
$input_holidays['fn'][] = array('春分の日(振替休日)',array('GOHolidaysJP', 'springDay'));
$input_holidays['fn'][] = array('秋分の日(振替休日)',array('GOHolidaysJP', 'autumnDay'));
//
$input_holidays['fn'][] = array('成人の日',array('GOHolidaysJP', 'comAgeDay'));
$input_holidays['fn'][] = array('海の日',array('GOHolidaysJP', 'marineDay'));
$input_holidays['fn'][] = array('敬老の日',array('GOHolidaysJP', 'agedDay'));
$input_holidays['fn'][] = array('スポーツの日',array('GOHolidaysJP', 'sportsDay'));

if (!class_exists('GOHolidaysJP')) {
	class GOHolidaysJP {
//
		public static function comAgeDay($year) {
			return (new DateTime('second mon of Jan '.$year))->format('Y-m-d');
		}
		public static function marineDay($year) {
			return (new DateTime('third mon of July '.$year))->format('Y-m-d');
		}
		public static function agedDay($year) {
			return (new DateTime('third mon of September '.$year))->format('Y-m-d');
		}
		public static function sportsDay($year) {
			return (new DateTime('second mon of October '.$year))->format('Y-m-d');
		}
//
		public static function newyear($year) {
			$date = new DateTime($year . '-01-01');
			return self::substitute($date);
		}
		public static function nationalFoundDay($year) {
			$date = new DateTime($year . '-02-11');
			return self::substitute($date);
		}
		public static function empeBirthDay($year) {
			$date = new DateTime($year . '-02-23');
			return self::substitute($date);
		}
		public static function showaDay($year) {
			$date = new DateTime($year . '-04-29');
			return self::substitute($date);
		}
		public static function constMemDay($year) {
			$date = new DateTime($year . '-05-03');
			return self::substitute3($date);
		}
		public static function greenDay($year) {
			$date = new DateTime($year . '-05-04');
			return self::substitute2($date);
		}
		public static function childDay($year) {
			$date = new DateTime($year . '-05-05');
			return self::substitute($date);
		}
		public static function mountDay($year) {
			$date = new DateTime($year . '-08-11');
			return self::substitute($date);
		}
		public static function cultureDay($year) {
			$date = new DateTime($year . '-11-03');
			return self::substitute($date);
		}
		public static function labthankDay($year) {
			$date = new DateTime($year . '-11-23');
			return self::substitute($date);
		}
		public static function springDay($yspDay) {
			$date = new DateTime($yspDay);
			return self::substitute($date);
		}
		public static function autumnDay($yamDay) {
			$date = new DateTime($yamDay);
			return self::substitute($date);
		}
//
		private static function substitute(DateTime $date, int $moveDays = null) : ?string {
			$dayOfWeek = $date->format("w");
			if($dayOfWeek == 0) {
				if(!isset($moveDays)) {
					$moveDays = 1;
				}
				$date->add(new DateInterval("P" . $moveDays . "D"));

				return $date->format("Y-m-d");

			}

			return null;

		}
		private static function substitute2(DateTime $date, int $moveDays = null) : ?string {
			$dayOfWeek = $date->format("w");
			if($dayOfWeek == 0) {
				if(!isset($moveDays)) {
					$moveDays = 2;
				}
				$date->add(new DateInterval("P" . $moveDays . "D"));

				return $date->format("Y-m-d");

			}

			return null;

		}
		private static function substitute3(DateTime $date, int $moveDays = null) : ?string {
			$dayOfWeek = $date->format("w");
			if($dayOfWeek == 0) {
				if(!isset($moveDays)) {
					$moveDays = 3;
				}
				$date->add(new DateInterval("P" . $moveDays . "D"));

				return $date->format("Y-m-d");

			}

			return null;

		}

	}
}