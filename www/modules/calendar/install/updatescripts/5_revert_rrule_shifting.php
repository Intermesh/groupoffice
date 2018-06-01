<?php

//include_once('../../../../GO.php');

$rruleparams = \GO\Base\Db\FindParams::newInstance()->ignoreAcl();
$rruleparams->getCriteria()->addCondition('rrule', 'RRULE:%', 'LIKE');
$recurringEvents = \GO\Calendar\Model\Event::model()->find($rruleparams);

foreach($recurringEvents as $event) {

	//echo $event->rrule."\n";

	try {
		$rrule = new \GO\Base\Util\Icalendar\Rrule();
		$rrule->readIcalendarRruleString($event->start_time, $event->rrule,false);
		$rrule->shiftDays(false);
		$unShiftedRule = $rrule->createRrule();

		//echo $unShiftedRule;
		//Just update the Rrule because saving the model is to slow for tons of events
		\GO::getDbConnection()->query("UPDATE `cal_events` SET `rrule` = '$unShiftedRule' WHERE `id` = $event->id");

		//$event->rrule = $unShiftedRule;
		//$event->save(true);
	}catch(\Exception $e) {
		echo $e->getMessage()."\n";
	}
	 
}
