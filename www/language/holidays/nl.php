<?php
// holidays with fixed date
$input_holidays['fix']['01-01'] = 'Nieuwjaar';
$input_holidays['fix']['02-14'] = array('name' => 'Valentijnsdag','free'=>false);
$input_holidays['fix']['10-04'] = array('name' => 'Wereld dierendag','free'=>false);
$input_holidays['fix']['11-11'] = array('name' => 'Sint Maarten','free'=>false);
$input_holidays['fix']['12-25'] = '1e kerstdag';
$input_holidays['fix']['12-26'] = '2e kerstdag';
$input_holidays['fix']['12-31'] = array('name' => 'Oudjaarsavond','free'=>false);

// holidays with variable date (christian holidays computation is based on the date of easter day)
$input_holidays['var']['-2'] = array('name' => 'Goede vrijdag','free'=>false);
$input_holidays['var']['0'] = '1e Paasdag';
$input_holidays['var']['1'] = '2e Paasdag';
$input_holidays['var']['39'] = 'Hemelvaartsdag';
$input_holidays['var']['49'] = '1e pinksterdag';
$input_holidays['var']['50'] = '2e pinksterdag';

$input_holidays['fn'][] = array('Koningsdag',array('GOHolidaysNl', 'koningsdag'));

$input_holidays['fn'][] = array('Bevrijdingsdag',array('GOHolidaysNl', 'bevrijdingsdag'));

if (!class_exists('GOHolidaysNl')) {
	class GOHolidaysNl {
		public static function koningsdag($year) {
			$defaultDay = '27';
			$defaultMonth = '4';
			$theDate = mktime(0,0,0,$defaultMonth,$defaultDay,$year);
			if (date('w',$theDate)==0)
				$theDate = \GO\Base\Util\Date::date_add($theDate,-1);
			return date('Y-m-d',$theDate);
		}
		
		public static function bevrijdingsdag($year){
			// Iedere 5 hele jaren
			if($year % 5 ==0){
				return date('Y-m-d',mktime(0,0,0,5,5,$year));
			}
			return ;
		}
	}
}
?>
