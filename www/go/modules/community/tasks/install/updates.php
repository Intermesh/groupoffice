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
		try {
			$rrule = new \go\modules\community\tasks\model\Recurrence($row['rrule'], new DateTime("@" . $row["start_time"]));
			$data = ['recurrenceRule' => json_encode($rrule->toArray())];
			go()->getDbConnection()->updateIgnore('tasks_task', $data, ['id' => $row['id']])->execute();
		} catch(Exception $e) {
			echo "RRULE Exception:  " . $e->getMessage() ."\n";
		}
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

	if(\go\core\model\Module::isInstalled('legacy', 'projects2')) {
		$m = new Migrator();
		$m->job2task();
	}
};

$updates['202105211543'][] = "ALTER TABLE `tasks_task`  ADD `progressChange` TINYINT(2) NULL";

$updates['202106011409'][] = "ALTER TABLE `tasks_task` ADD COLUMN IF NOT EXISTS `startTime` TIME NULL DEFAULT NULL";





$updates['202106101432'][] = "alter table tasks_tasklist
	add projectId int null;";

$updates['202106181401'][] = "create table if not exists tasks_user_settings
(
    userId int null,
    defaultTasklistId int(11) unsigned null,
    rememberLastItems boolean not null default false,
    lastTasklistIds varchar(255) null,
    constraint tasks_user_settings_pk
        primary key (userId),
    constraint tasks_user_settings_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";


$updates['202106181401'][] = "alter table tasks_task drop foreign key tasks_task_ibfk_1;";

$updates['202106181401'][] = "alter table tasks_task
	add constraint tasks_task_ibfk_1
		foreign key (tasklistId) references tasks_tasklist (id)
			on DELETE cascade;";

$updates['202106181401'][] = "alter table tasks_user_settings
	add constraint tasks_user_settings_tasks_tasklist_id_fk
		foreign key (defaultTasklistId) references tasks_tasklist (id)
			on delete set null;";

