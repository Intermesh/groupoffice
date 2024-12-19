<?php

use go\modules\community\tasks\install\Migrator;

$updates['201911061630'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/tasks/install/upgrade.sql"));
};

$updates['201911061630'][] = function(){
	$stmt =\GO::getDbConnection()->query("SELECT id, rrule,`start_time` FROM ta_tasks WHERE rrule != ''");

	while($row = $stmt->fetch()) {
		try {
			$rrule = \go\core\util\Recurrence::fromString($row['rrule'], new DateTime("@" . $row["start_time"]));
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

$updates['202106011409'][] = "ALTER TABLE `tasks_task` ADD COLUMN `startTime` TIME NULL DEFAULT NULL";





$updates['202106101432'][] = "alter table tasks_tasklist
	add projectId int null;";

$updates['202106181401'][] = "create table if not exists tasks_user_settings
(
    userId int not null,
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

$updates['202107051416'][] = "create index tasks_task_progress_index
	on tasks_task (progress);";

$updates['202107251024'][] = "ALTER TABLE `tasks_category` DROP FOREIGN KEY `tasks_category_ibfk_1`;";
$updates['202107251024'][] = "ALTER TABLE `tasks_category` CHANGE COLUMN `createdBy` `ownerId` INT(11) NULL ;";
$updates['202107251024'][] = "ALTER TABLE `tasks_category` ADD CONSTRAINT `tasks_category_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `core_user` (`id`);";

$updates['202108101005'][] = "ALTER TABLE `tasks_task` ADD COLUMN `location` TEXT NULL;";

$updates['202109301005'][] = "ALTER TABLE `tasks_category`
ADD COLUMN `tasklistId` INT(11) NULL DEFAULT NULL AFTER `ownerId`,
ADD INDEX `tasks_category_tasklist_ibfk_9_idx` (`tasklistId` ASC);";

$updates['202109301006'][] = "ALTER TABLE `tasks_category`
ADD CONSTRAINT `tasks_category_tasklist_ibfk_9`
  FOREIGN KEY (`tasklistId`)
  REFERENCES `tasks_tasklist` (`createdBy`)
  ON DELETE CASCADE;";


$updates['202111251126'][] = "alter table tasks_category drop foreign key tasks_category_ibfk_1;";

$updates['202111251126'][] = "alter table tasks_category
	add constraint tasks_category_ibfk_1
		foreign key (ownerId) references core_user (id)
			on delete cascade;";

$updates['202201261056'][] = "ALTER TABLE `tasks_portlet_tasklist` DROP FOREIGN KEY `tasks_portlet_tasklist_ibfk_1`;";
$updates['202201261056'][] = "ALTER TABLE `tasks_portlet_tasklist` CHANGE COLUMN `createdBy` `userId` INT(11) NOT NULL ;";
$updates['202201261056'][] = "ALTER TABLE `tasks_portlet_tasklist` ADD CONSTRAINT `tasks_portlet_tasklist_ibfk_1` FOREIGN KEY (`userId`)  REFERENCES `core_user` (`id`) ON DELETE CASCADE;";

$updates['202201271056'][] = "delete from tasks_task_category where categoryId not in (select id from tasks_category)";

$updates['202202041432'][] = "alter table tasks_category
    drop foreign key tasks_category_tasklist_ibfk_9;";

$updates['202202041432'][] = "alter table tasks_category
    modify tasklistId int(11) unsigned null;";

$updates['202202041432'][] = "alter table tasks_category
    add constraint tasks_category_tasklist_ibfk_9
        foreign key (tasklistId) references tasks_tasklist (id)
            on delete cascade;";

$updates['202202081432'][] = "ALTER TABLE `tasks_task` CHANGE COLUMN `description` `description` TEXT NULL DEFAULT null;";

$updates['202202241617'][] = "alter table tasks_user_settings
    add defaultDate bool default false null;";



$updates['202205101237'][] = "update tasks_task set filesFolderId = null where filesFolderId=0;";


$updates['202205311153'][] = "update tasks_task set responsibleUserId = null where responsibleUserId not in (select id from core_user);";

$updates['202205311153'][] = "alter table tasks_task
    add constraint tasks_task_core_user_id_fk
        foreign key (responsibleUserId) references core_user (id)
            on delete set null;";

$updates['202206031355'][] = 'ALTER TABLE `tasks_task` ADD COLUMN `latitude` decimal(10,8) DEFAULT NULL, ' .
	'ADD COLUMN `longitude` decimal(11,8) DEFAULT NULL;';

$updates['202206201417'][] = 'alter table tasks_tasklist_group
    add progressChange tinyint(2) null;';

$updates['202301301230'][] = function () {
	if (\go\core\model\Module::isInstalled('legacy', 'projects2')) {
		echo "Cleaning up orphaned project lists..." . PHP_EOL;
		$q = "DELETE FROM `tasks_tasklist` WHERE `role` = 3 AND `projectId` NOT IN(SELECT `id` FROM `pr2_projects`);";
		go()->getDbConnection()->exec($q);
	}
};




//6.7

$updates['202301301230'][] = "alter table tasks_task
	add aclId int null;";

$updates['202301301230'][] = "update tasks_task t set t.aclId = (select aclId from tasks_tasklist where id = t.tasklistId);";

$updates['202301301230'][] = "alter table tasks_task
	add constraint tasks_task_core_acl_id_fk
		foreign key (aclId) references core_acl (id)  ON DELETE RESTRICT;";


$updates['202301301230'][] = "update core_entity set name='TaskList', clientName='TaskList' where name='Tasklist'";


$updates['202301301230'][] = "create table tasks_tasklist_grouping
(
	id      int unsigned auto_increment,
    name    varchar(190) not null,
    `order` int unsigned null,
    constraint tasks_tasklist_grouping_pk
        primary key (id),
    constraint tasks_tasklist_grouping_name
        unique (name)
);";


$updates['202301301230'][] = "alter table tasks_tasklist
                    add groupingId int unsigned null;";

$updates['202301301230'][] = "alter table tasks_tasklist
                    add constraint tasks_tasklist_tasks_tasklist_grouping_null_fk
                        foreign key (groupingId) references tasks_tasklist_grouping (id)
                            on delete set null;";
$updates['202305231613'][] = "ALTER TABLE `tasks_task` DROP FOREIGN KEY `tasks_task_core_acl_id_fk`;";
$updates['202305231613'][] = "ALTER TABLE `tasks_task` DROP COLUMN `aclId`, DROP INDEX `tasks_task_core_acl_id_fk` ;";


$updates['202408061358'][] = "create index tasks_task_start_index
    on tasks_task (start);";

$updates['202408061358'][] = "create index tasks_tasklist_name_index
    on tasks_tasklist (name);";

$updates['202412090921'][] = "alter table tasks_tasklist_group
    add constraint tasks_tasklist_group_pk
        unique (id);";

// 6.9


// Fix being able to delete the calendar module. because the tasklists acl's belonged to the TasklistCompat entity which belongs to Calendar
$updates['202412090921'][] = "UPDATE core_acl a join core_entity e on a.entityTypeId = e.id 
SET a.entityTypeId = (SELECT id FROM core_entity WHERE clientName = 'Tasklist')
WHERE e.clientName = 'TasklistCompat';";

$updates['202412090921'][] = "ALTER TABLE `tasks_tasklist` 
ADD COLUMN `defaultColor` VARCHAR(21) NOT NULL DEFAULT '' AFTER `createdBy`,
ADD COLUMN `highestItemModSeq` VARCHAR(32) NOT NULL DEFAULT 0 AFTER `defaultColor`;";

$updates['202412090921'][] = "ALTER TABLE `tasks_tasklist` DROP COLUMN `version`;";
$updates['202412090921'][] = "ALTER TABLE `tasks_tasklist_user` CHANGE COLUMN `color` `color` VARCHAR(21) NULL DEFAULT NULL ;";
$updates['202412090921'][] = "ALTER TABLE `tasks_tasklist_user` CHANGE COLUMN `modSeq` `modSeq` INT NOT NULL DEFAULT 0 ;";
// set random default color for the color field
$updates['202412090921'][] = "UPDATE tasks_tasklist SET defaultColor = SUBSTRING('#CDAD00#E74C3C#9B59B6#8E44AD#2980B9#3498DB#1ABC9C#16A085#27AE60#2ECC71#F1C40F#F39C12#E67E22#D35400#95A5A6#34495E#808B96#1652a1', (id MOD 18) * 7 + 2 ,6);";
// subscribe to the tasklists the user has access to
$updates['202412090921'][] = "INSERT IGNORE INTO tasks_tasklist_user
(tasklistId, userId, isSubscribed, isVisible, color, sortOrder, modSeq)
select tl.id, ug.userId, 1, 1, tl.defaultColor,0,1 from core_acl_group ag
inner join core_user_group ug on ug.groupId = ag.groupId
inner join tasks_tasklist tl on tl.aclId = ag.aclId where tl.projectId is not null group by tl.id,ug.userId";

$updates['202412090921'][] = "update tasks_task set progressUpdated = modifiedAt where progressUpdated is null and progress = 3;";

$updates['202412090921'][] = "alter table tasks_task
    add projectId int unsigned null;";

$updates['202412090921'][] = "alter table tasks_task
    add constraint tasks_task_business_projects3_project3_id_fk
        foreign key (projectId) references business_projects3_project3 (id)
            on delete set null;";


$updates['202412090921'][] = "alter table tasks_task
    add mileStoneId int unsigned null;";

$updates['202412090921'][] = "alter table tasks_task
    add constraint tasks_task_business_projects3_project3_milestone_id_fk
        foreign key (mileStoneId) references business_projects3_project3_milestone (id)
            on delete set null;";
