<?php

use GO\Base\Util\Icalendar\Rrule;
use go\core\orm\Query;
use go\modules\community\tasks\install\Migrator;

$updates['201911061630'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/tasks/install/upgrade.sql"));
};

$updates['201911061630'][] = function(){
	$stmt =\GO::getDbConnection()->query("SELECT id, rrule,`start_time` FROM ta_tasks WHERE rrule != ''");

	while($row = $stmt->fetch()) {
		$rrule = new \go\modules\community\tasks\model\Recurrence($row['rrule'], new DateTime("@" . $row["start_time"]));
		$data = ['recurrenceRule' => json_encode($rrule->toArray())];
		go()->getDbConnection()->updateIgnore('tasks_task', $data, ['id'=>$row['id']])->execute();
	}
};

// insert function
$updates['201911061630'][] = function(){
// MS: Is this code still relevant?

//	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_tasks");
//
//	while($row = $stmt->fetch()) {
//		$needles = ["COUNT","UNTIL","INTERVAL","FREQ","BYDAY"];
//		$haystack = ["count","until","interval","frequency","byDay"];
//		$data = [];
//		$data['recurrenceRule'] = str_replace($needles,$haystack,$row["rrule"]);
//
//		$rrule = new Rrule();
//		$rrule->readIcalendarRruleString($row["start_time"], $row['rrule']);
//
//		$days = $rrule->byday;
//		$newDays = [];
//		foreach($days as $day) {
//			$day = str_replace($rrule->bysetpos,"",$day);
//			$newDays[] = ['day' => $day, 'position' => $rrule->bysetpos];
//		}
//
//		$rrule->byday = $newDays;
//
//		$recurrencePattern = [
//			'frequency' => $rrule->freq,
//			'bySetPosition' => $rrule->bysetpos,
//			'interval' => $rrule->interval,
//			'byDay' =>  $rrule->byday
//		];
//
//		if($rrule->until) {
//			$rrule->until = DateTime::createFromFormat( 'U', $rrule->until);
//			$recurrencePattern['until'] = $rrule->until;
//		} else {
//			$recurrencePattern['count'] = $rrule->count;
//		}
//
//		$data['recurrenceRule'] = json_encode($recurrencePattern);
//		GO()->getDbConnection()->insertIgnore('tasks_task', $data)->execute();
//	}

};

$updates['202101011630'][] = "ALTER TABLE `tasks_task` CHANGE COLUMN `description` `description` TEXT NULL DEFAULT '';";
$updates['202104301506'][] = function() {
	$m = new Migrator();
	$m->job2task();
};


$updates['202105211543'][] = "ALTER TABLE `tasks_task`  ADD `progressChange` TINYINT(2) NULL";