<?php

$updates["201108190000"][]="RENAME TABLE `go_links_12` TO `go_links_ta_tasks`;";
$updates["201108190000"][]="ALTER TABLE `go_links_ta_tasks` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201108190000"][]="ALTER TABLE `go_links_ta_tasks` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201108190000"][]="RENAME TABLE `cf_12` TO `cf_ta_tasks` ";
$updates["201108190000"][]="ALTER TABLE `cf_ta_tasks` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'12', 'GO_Tasks_Model_Task'
);";

$updates["201109070000"][]="ALTER TABLE `ta_tasks` CHANGE `rrule` `rrule` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$updates["201109140000"][]="ALTER TABLE `ta_tasks` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201109190000"][]="ALTER TABLE `ta_categories` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201109190001"][]="ALTER TABLE `ta_lists` DROP `shared_acl`";
$updates["201109190002"][]="ALTER TABLE `ta_lists` ADD `files_folder_id` INT NOT NULL DEFAULT '0'";

// SQL strict checks
$updates["201110040002"][]="ALTER TABLE `ta_tasks` CHANGE `uuid` `uuid` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110040002"][]="ALTER TABLE `ta_tasks` CHANGE `repeat_end_time` `repeat_end_time` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110040002"][]="ALTER TABLE `ta_tasks` CHANGE `reminder` `reminder` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110040002"][]="ALTER TABLE `ta_tasks` CHANGE `rrule` `rrule` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110040002"][]="ALTER TABLE `ta_tasks` CHANGE `project_name` `project_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201110170002"][]="ALTER TABLE `ta_tasks`
  DROP `repeat_type`,
  DROP `repeat_every`,
  DROP `mon`,
  DROP `tue`,
  DROP `wed`,
  DROP `thu`,
  DROP `fri`,
  DROP `sat`,
  DROP `sun`,
  DROP `month_time`;";

$updates["201110211221"][]="ALTER TABLE `ta_settings` CHANGE `remind` `remind` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110211221"][]="UPDATE ta_settings SET remind=0 where remind=1";
$updates["201110211221"][]="UPDATE ta_settings SET remind=1 where remind=2";

$updates["201110281135"][]="ALTER TABLE `ta_lists` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201110281135"][]="RENAME TABLE `ta_lists` TO `ta_tasklists` ;";
$updates["201110281135"][]="ALTER TABLE `ta_settings` CHANGE `reminder_time` `reminder_time` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'";
$updates["201110281135"][]="ALTER TABLE `ta_settings` CHANGE `reminder_days` `reminder_days` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110281135"][]="ALTER TABLE `ta_settings` CHANGE `default_tasklist_id` `default_tasklist_id` INT( 11 ) NOT NULL DEFAULT '0'";


$updates["201110281135"][]="ALTER TABLE `ta_tasks` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110281135"][]="ALTER TABLE `ta_tasks` CHANGE `category_id` `category_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201111021035"][]="RENAME TABLE `su_visible_lists` TO `ta_portlet_tasklists`";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_ta_tasks` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201112221547"][]="ALTER TABLE `ta_tasks` ADD `percentage_complete` TINYINT NOT NULL DEFAULT '0'";
$updates["201112221547"][]="ALTER TABLE `ta_tasks` ADD `project_id` INT NOT NULL DEFAULT '0'";
$updates["201201121400"][]="ALTER TABLE `ta_tasklists` DROP `acl_write`";

$updates["201204231436"][]="ALTER TABLE `ta_tasks` DROP `project_name`;";

$updates["201305141646"][]="update ta_tasks set mtime=unix_timestamp(), due_time=start_time where due_time<start_time;";

$updates['201305151646'][]="ALTER TABLE `ta_tasks` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";

$updates['201501221443'][]="ALTER TABLE `ta_tasks` CHANGE `uuid` `uuid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";

$updates['201508121510'][]="ALTER TABLE `ta_tasklists` ADD `version` INT UNSIGNED NOT NULL DEFAULT '1';";

$updates['201609301235'][]="ALTER TABLE `ta_tasks` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";


$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281659'][] = 'ALTER TABLE `ta_tasks` CHANGE `uuid` `uuid` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `cf_ta_tasks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_ta_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_ta_tasks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_ta_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `ta_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ta_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ta_portlet_tasklists` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ta_portlet_tasklists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ta_settings` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ta_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ta_tasklists` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ta_tasklists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ta_tasks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ta_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281659'][] = 'SET foreign_key_checks = 1;';



$updates['201810161450'][] = "ALTER TABLE `cf_ta_tasks` CHANGE `model_id` `id` INT(11) NOT NULL;";
$updates['201810161450'][] = "RENAME TABLE `cf_ta_tasks` TO `ta_tasks_custom_fields`;";
$updates['201810161450'][] = "delete from ta_tasks_custom_fields where id not in (select id from ta_tasks);";
$updates['201810161450'][] = "ALTER TABLE `ta_tasks_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `ta_tasks`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201903291350'][] = function() {	
	$m = new \go\core\install\MigrateCustomFields63to64();
	$m->migrateEntity("Task");
};
//final server update for old module
$updates['201903291351'][] = 'update core_module set package=\'community\', version=0 where name=\'tasks\'';