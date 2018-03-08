<?php
// holidays with fixed date
$input_holidays['fix']['01-01'] = 'Nieuwjaar';
$input_holidays['fix']['02-14'] = 'Valentijnsdag';
$input_holidays['fix']['05-05'] = 'Bevrijdingsdag';
$input_holidays['fix']['10-04'] = 'Wereld dierendag';
$input_holidays['fix']['11-11'] = 'Sint Maarten';
$input_holidays['fix']['12-25'] = '1e kerstdag';
$input_holidays['fix']['12-26'] = '2e kerstdag';
$input_holidays['fix']['12-31'] = 'Oudjaarsavond';

// holidays with variable date (christian holidays computation is based on the date of easter day)
$input_holidays['var']['-2'] = 'Goede vrijdag';
$input_holidays['var']['0'] = '1e Paasdag';
$input_holidays['var']['1'] = '2e Paasdag';
$input_holidays['var']['39'] = 'Hemelvaartsdag';
$input_holidays['var']['49'] = '1e pinksterdag';
$input_holidays['var']['50'] = '2e pinksterdag';

$input_holidays['fn'][3] = array('Koningsdag',array('GO_Holidays_Nl', 'koningsdag'));

if (!class_exists('GO_Holidays_Nl')) {
	class GO_Holidays_Nl {
		public static function koningsdag($year) {
			$defaultDay = '27';
			$defaultMonth = '4';
			$theDate = mktime(0,0,0,$defaultMonth,$defaultDay,$year);
			if (date('w',$theDate)==0)
				$theDate = GO_Base_Util_Date::date_add($theDate,-1);
			return date('Y-m-d',$theDate);
		}
	}
}
?>