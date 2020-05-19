<?php
// holidays with fixed date
$input_holidays['fix']['01-01'] = 'New Years Day';
$input_holidays['fix']['12-25'] = 'Christmas Day';
$input_holidays['fix']['12-26'] = 'Boxing Day';

// holidays with variable date (christian holidays computation is based on the date of easter day)
$input_holidays['var']['-2'] = 'Good Friday';
$input_holidays['var']['0'] = 'Easter Sunday';
$input_holidays['var']['1'] = 'Easter Monday';

$input_holidays['fn'][] = array('Summer bank holiday',array('GOHolidaysUK', 'summerBank'));
$input_holidays['fn'][] = array('Spring bank holiday',array('GOHolidaysUK', 'springBank'));
$input_holidays['fn'][] = array('Early May bank holiday',array('GOHolidaysUK', 'earlyMayBank'));

if (!class_exists('GOHolidaysUK')) {
	class GOHolidaysUK {
		public static function summerBank($year) {
			return (new \DateTime('last mon of August '.$year))->format('Y-m-d');
		}

		public static function springBank($year){
			return (new \DateTime('last mon of May '.$year))->format('Y-m-d');
		}

		public static function earlyMayBank($year){
			if($year == 2020) {
				return "2020-05-08";
			}
			return (new \DateTime('first mon of May '.$year))->format('Y-m-d');
		}
	}
}
