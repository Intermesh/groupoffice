<?php
require('../../www/GO.php');

//$rruleString = 'FREQ=MONTHLY;INTERVAL=3;BYDAY=MO,WE;BYSETPOS=2';

$rruleString = 'FREQ=MONTHLY;INTERVAL=2;BYDAY=2MO,2WE';

//$rruleString = 'FREQ=WEEKLY;INTERVAL=3;BYDAY=FR,SU';
//$rruleString = 'FREQ=DAILY;INTERVAL=2';

$rruleString = 'FREQ=MONTHLY;INTERVAL=2';

//$rruleString = 'FREQ=YEARLY;INTERVAL=2';

$start = '02-11-2011 19:00:00';

$rrule = new \GO\Base\Util\Icalendar\Rrule();
$rrule->readIcalendarRruleString(strtotime($start), $rruleString);

//$fromTime=\GO\Base\Util\Date::clear_time(time());
$next = $rrule->getNextRecurrence();
for($i=0;$i<10;$i++){
	

	echo date('Y-m-d', $next)."\n";
	$next = $rrule->getNextRecurrence();
	//echo "---\n\n";
	
}




//$params = array(
//		'byday' => '',
//		'bymonth' => '',
//		'bymonthday' => '',
//		'byday' => '',
//		'freq' => '',
//		'interval' => '',
//		'eventStartTime' => '',
//		'bysetpos' => '',
//		'until' => '',
//);
//
//$Recurrence_pattern = new \GO\Base\Util\Date_RecurrencePattern($params);

//
//$date1 = new DateTime("2011-11-01");
//$date2 = new DateTime("2012-02-10");
//
//$d = $date1->diff($date2);
//var_dump($d);