<?php

use GO\Base\Util\Icalendar\Rrule;
use go\core\orm\Query;

$updates['201911061630'][] = function() {

	// Merijn: this can't be good because destroys all links
//	go()->getDbConnection()
//		->delete('core_entity', (new Query)->where(['clientName' => 'Task']))->execute();
//
//	go()->getDbConnection()
//		->delete('core_entity', (new Query)->where(['clientName' => 'Tasklist']))->execute();
//
//	go()->getDbConnection()
//		->delete('core_module', (new Query)->where(['name' => 'tasks']))->execute();

	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/tasks/install/upgrade.sql"));
};

// insert function
$updates['201911061630'][] = function(){

	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_portlet_tasklists");

	while($row = $stmt->fetch()){
		$data = [];
		$data['createdBy'] = $row["user_id"];
		$data['tasklistId'] = $row["tasklist_id"];
		GO()->getDbConnection()->insertIgnore('tasks_portlet_tasklist', $data)->execute();
	}

	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_tasks_custom_fields");

	while($row = $stmt->fetch()){
		$data = [];
		$data['id'] = $row["id"];
		GO()->getDbConnection()->insert('tasks_tasks_custom_field', $data)->execute();
	}

	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_tasklists");

	while($row = $stmt->fetch()){
		$data = [];
		$data['id'] = $row["id"];
		$data['role'] = 1;
		$data['name'] = $row["name"];
		$data['createdBy'] = $row["user_id"];
		$data['aclId'] = $row["acl_id"];
		$data['filesFolderId'] = $row["files_folder_id"];
		$data['version'] = $row["version"];
		GO()->getDbConnection()->insertIgnore('tasks_tasklist', $data)->execute();
	}

//	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_settings");
//
//	while($row = $stmt->fetch()){
//		$data = [];
//		$data['createdBy'] = $row["user_id"];
//		$data['reminderDays'] = $row["reminder_days"];
//		$data['reminderTime'] = $row["reminder_time"];
//		$data['remind'] = $row["remind"];
//		$data['defaultTasklistId'] = $row["default_tasklist_id"];
//		GO()->getDbConnection()->insert('tasks_settings', $data)->execute();
//	}

	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_categories");

	while($row = $stmt->fetch()){
		$data = [];
		$data['id'] = $row["id"];
		$data['name'] = $row["name"];
		$data['createdBy'] = $row["user_id"];
		GO()->getDbConnection()->insertIgnore('tasks_category', $data)->execute();
	}

	$stmt =\GO::getDbConnection()->query("SELECT * FROM ta_tasks");

	while($row = $stmt->fetch()) {
		$needles = ["COUNT","UNTIL","INTERVAL","FREQ","BYDAY"];
		$haystack = ["count","until","interval","frequency","byDay"];
		$data = [];
		$data['id'] = $row["id"];
		$data['uid'] = $row["uuid"];
		$data['tasklistId'] = $row["tasklist_id"];
		$data['createdBy'] = $row["user_id"];
		$data['createdAt'] = DateTime::createFromFormat( 'U', $row["ctime"]);
		$data['modifiedAt'] = DateTime::createFromFormat( 'U', $row["mtime"]);
		$data['modifiedBy'] = $row["muser_id"];
		$data['start'] = DateTime::createFromFormat( 'U', $row["start_time"]);
		$data['due'] = DateTime::createFromFormat( 'U', $row["due_time"]);
		if(!empty($row["completion_time"])) {
			$data['progress'] = \go\modules\community\tasks\model\Progress::Completed;
		}
		$data['progressUpdated'] =  DateTime::createFromFormat( 'U', $row["completion_time"]);
		$data['title'] = $row["name"];
		$data['description'] = $row["description"];
		$data['recurrenceRule'] = str_replace($needles,$haystack,$row["rrule"]);
		$data['filesFolderId'] = $row["files_folder_id"];
		$data['priority'] = $row["priority"];
		$data['percentComplete'] = $row["percentage_complete"];

		$rrule = new Rrule();
		$rrule->readIcalendarRruleString($row["start_time"], $row['rrule']);

		$days = $rrule->byday;
		$newDays = [];
		foreach($days as $day) {
			$day = str_replace($rrule->bysetpos,"",$day);
			$newDays[] = ['day' => $day, 'position' => $rrule->bysetpos];
		}

		$rrule->byday = $newDays;

		$recurrencePattern = [
			'frequency' => $rrule->freq,
			'bySetPosition' => $rrule->bysetpos,
			'interval' => $rrule->interval,
			'byDay' =>  $rrule->byday
		];

		if($rrule->until) {
			$rrule->until = DateTime::createFromFormat( 'U', $rrule->until);
			$recurrencePattern['until'] = $rrule->until;
		} else {
			$recurrencePattern['count'] = $rrule->count;
		}

		$data['recurrenceRule'] = json_encode($recurrencePattern);
		GO()->getDbConnection()->insertIgnore('tasks_task', $data)->execute();
	}

	$stmt =\GO::getDbConnection()->query("SELECT id,category_id FROM ta_tasks");

	while($row = $stmt->fetch()) {
		$data = [];
		$data['taskId'] = $row["id"];
		$data['categoryId'] = $row["category_id"];
		GO()->getDbConnection()->insertIgnore('tasks_task_category', $data)->execute();
	}

	$stmt =\GO::getDbConnection()->query("SELECT id,reminder,user_id FROM ta_tasks");

	while($row = $stmt->fetch()) {
		$data = [];
		$data['taskId'] = $row["id"];
		$data['userId'] = $row['user_id'];
		$data['when'] = DateTime::createFromFormat( 'U', $row["reminder"]);
		GO()->getDbConnection()->insertIgnore('tasks_alert', $data)->execute();
	}
};
