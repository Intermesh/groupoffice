<?php
$updates["201109011450"][]="RENAME TABLE `go_links_1` TO `go_links_cal_events`;";
$updates["201109011450"][]="ALTER TABLE `go_links_cal_events` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201109011450"][]="ALTER TABLE `go_links_cal_events` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";

$updates["201109011450"][]="ALTER TABLE `cf_1` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201109011450"][]="RENAME TABLE `cf_1` TO `cf_cal_events` ;";

$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'1', 'GO_Calendar_Model_Event'
);";


$updates["201108301656"][]="delete from cal_events where calendar_id not in(select id from cal_calendars);";

$updates["201109140000"][]="ALTER TABLE `cal_events` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201109140000"][]="ALTER TABLE `cal_events` CHANGE `all_day_event` `all_day_event` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201109140000"][]="ALTER TABLE `cal_events` CHANGE `busy` `busy` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201109140000"][]="ALTER TABLE `cal_events` CHANGE `private` `private` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201109140000"][]="ALTER TABLE `cal_events` CHANGE `read_only` `read_only` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201109160000"][]="ALTER TABLE `cal_exceptions` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201110051633"][]="ALTER TABLE `cal_events` CHANGE `uuid` `uuid` VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201110051633"][]="ALTER TABLE `cal_events` CHANGE `resource_event_id` `resource_event_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110051633"][]="ALTER TABLE `cal_events` CHANGE `rrule` `rrule` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110051633"][]="ALTER TABLE `cal_events` CHANGE `exception_for_event_id` `exception_for_event_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110061133"][]="ALTER TABLE `cal_participants` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201110071512"][]="ALTER TABLE `cal_calendars` ADD `files_folder_id` INT NOT NULL DEFAULT '0'";


$updates["201110071512"][]="ALTER TABLE `cf_21` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110071512"][]="RENAME TABLE `cf_21` TO `cf_cal_calendars` ;";

$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `public` `public` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `shared_acl` `shared_acl` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `show_bdays` `show_bdays` TINYINT( 1 ) NOT NULL DEFAULT '1'";
$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `project_id` `project_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110071512"][]="ALTER TABLE `cal_calendars` CHANGE `tasklist_id` `tasklist_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110131221"][]="ALTER TABLE `cal_events` CHANGE `status` `status` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NEEDS-ACTION'";

$updates["201110131221"][]="ALTER TABLE `cal_events` CHANGE `category_id` `category_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110141221"][]="CREATE TABLE IF NOT EXISTS `cf_cal_calendars` (
  `model_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201110141221"][]="UPDATE cal_events SET private=0 where private=1";
$updates["201110141221"][]="UPDATE cal_events SET private=1 where private=2";

$updates["201110141221"][]="UPDATE cal_events SET all_day_event=0 where all_day_event=1";
$updates["201110141221"][]="UPDATE cal_events SET all_day_event=1 where all_day_event=2";

$updates["201110141221"][]="UPDATE cal_events SET busy=0 where busy=1";
$updates["201110141221"][]="UPDATE cal_events SET busy=1 where busy=2";

$updates["201110141221"][]="UPDATE cal_events SET read_only=0 where read_only=1";
$updates["201110141221"][]="UPDATE cal_events SET read_only=1 where read_only=2";

$updates["201110141221"][]="UPDATE cal_calendars SET public=0 where public=1";
$updates["201110141221"][]="UPDATE cal_calendars SET public=1 where public=2";


$updates["201110171221"][]="ALTER TABLE `cal_events` DROP `mon` ,
DROP `tue` ,
DROP `wed` ,
DROP `thu` ,
DROP `fri` ,
DROP `sat` ,
DROP `sun` ,
DROP `month_time` ;";


$updates["201110171221"][]="ALTER TABLE `cal_events` CHANGE `repeat_forever` `repeat_forever` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110171221"][]="UPDATE cal_events SET repeat_forever=0 where repeat_forever=1";
$updates["201110171221"][]="UPDATE cal_events SET repeat_forever=1 where repeat_forever=2";

$updates["201110281025"][]="ALTER TABLE `cal_calendars` CHANGE `comment` `comment` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201110281025"][]="ALTER TABLE `cal_settings` CHANGE `reminder` `reminder` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110281025"][]="ALTER TABLE `cal_settings` CHANGE `background` `background` CHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'EBF1E2'";
$updates["201110281025"][]="ALTER TABLE `cal_settings` CHANGE `calendar_id` `calendar_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110281025"][]="ALTER TABLE `cal_participants` CHANGE `last_modified` `last_modified` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110281025"][]="ALTER TABLE `cal_participants` CHANGE `role` `role` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201111021004"][]="update `cal_events` set resource_event_id=0 WHERE id=resource_event_id;";
$updates["201111101938"][]="ALTER TABLE `cal_participants` CHANGE `event_id` `event_id` INT( 11 ) NOT NULL";
$updates["201111101938"][]="ALTER TABLE `cal_events` CHANGE `busy` `busy` TINYINT( 1 ) NOT NULL DEFAULT '1'";

$updates["201111101938"][]="ALTER TABLE `cal_exceptions` ADD `exception_event_id` INT NOT NULL DEFAULT '0'";

$updates["201111101938"][]="ALTER TABLE `cal_events` ADD `owner_status` TINYINT NOT NULL DEFAULT '1'";
$updates["201112061629"][]="ALTER TABLE `cal_events` CHANGE `recurrence_id` `recurrence_id` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201112121448"][]="ALTER TABLE `cal_events` ADD `is_organizer` BOOLEAN NOT NULL DEFAULT '1'";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_cal_calendars` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_cal_events` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201201100902"][]="CREATE TABLE IF NOT EXISTS `cal_calendar_user_colors` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `color` varchar(6) NOT NULL,
  PRIMARY KEY (`user_id`,`calendar_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201201100902"][]="ALTER TABLE `cal_groups` CHANGE `fields` `fields` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201201100902"][]="ALTER TABLE `cal_groups` CHANGE `show_not_as_busy` `show_not_as_busy` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201201100902"][]="ALTER TABLE `cal_groups` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201201260928"][]="ALTER TABLE `cal_participants` CHANGE `name` `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";

$updates["201202211344"][]="update cal_calendars set project_id=0;";



$updates["201203101510"][]="ALTER TABLE `cal_events` CHANGE `category_id` `category_id` INT( 11 ) NULL;";
$updates["201203101510"][]="ALTER TABLE `cal_categories` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT;";

$updates["201203101514"][]="ALTER TABLE `cal_categories` CHANGE `user_id` `calendar_id` INT( 11 ) NOT NULL;";
$updates["201203101515"][]="script:1_convert_categories.php";



$updates["201204121221"][]="ALTER TABLE `cal_views` DROP `start_hour`";
$updates["201204121221"][]="ALTER TABLE `cal_views` DROP `end_hour`";
$updates["201204121221"][]="ALTER TABLE `cal_views` DROP `event_colors_override`";
$updates["201204121221"][]="ALTER TABLE `cal_views` DROP `acl_write`";
$updates["201204121221"][]="ALTER TABLE `cal_views` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201205031333"][]="delete FROM `cal_participants` WHERE event_id=0;";

$updates["201206131104"][]="ALTER TABLE `cal_categories` CHANGE `color` `color` CHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'EBF1E2'";

$updates["201207090855"][]="ALTER TABLE `cal_calendars` CHANGE `show_bdays` `show_bdays` TINYINT( 1 ) NOT NULL DEFAULT '0'";

$updates["201208010855"][]="ALTER TABLE `cal_events` CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL"; 

//database could have been corrupted by an earlier bug that was fixed. this query will take care of the issue.
$updates['201208101000'][]="UPDATE `cal_events` SET `rrule` = replace(rrule, '=MONTHLY_DATE', '=MONTHLY')"; 

//Fix to sync recurring events again, this is needed to fix the events on the phone
$updates['201209031500'][]="UPDATE `cal_events` SET `mtime`= UNIX_TIMESTAMP() WHERE `rrule` != '';"; 

$updates['201211241425'][]="update cal_events set mtime=unix_timestamp() where description!='' and id in (select id from dav_events);";

$updates['201210161843'][]="update cal_events set rrule='' where rrule='RRULE'";

$updates['201210161843'][]="insert ignore into cal_groups (id,name, user_id) values (1, 'Calendars', 1)";

$updates['201210300847'][]="ALTER TABLE `cal_events` CHANGE `location` `location` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates['201210300847'][]="ALTER TABLE `cal_events` CHANGE `name` `name` VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";

$updates['201212031644'][]="update cal_events set rrule='FREQ=YEARLY' WHERE rrule='RRULE:YM1 #0';";

$updates['201212040946'][]="ALTER TABLE `cal_participants` CHANGE `status` `status` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NEEDS-ACTION'";

$updates['201212040946'][]="ALTER TABLE `cal_participants` CHANGE `status` `status` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'NEEDS-ACTION'";

$updates['201212040946'][]="update cal_participants set status='NEEDS-ACTION' where status='0';";
$updates['201212040946'][]="update cal_participants set status='ACCEPTED' where status='1';";
$updates['201212040946'][]="update cal_participants set status='DECLINED' where status='2';";
$updates['201212040946'][]="update cal_participants set status='TENTATIVE' where status='3';";

$updates["201212040946"][]="ALTER TABLE `cal_events` DROP `owner_status`";

$updates["201212040946"][]="ALTER TABLE `cal_events` DROP `sequence`";

$updates['201212040946'][] ="CREATE  TABLE IF NOT EXISTS `cal_views_groups` (
  `view_id` INT NOT NULL ,
  `group_id` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`view_id`, `group_id`) )
ENGINE = InnoDB";

$updates['201212040946'][]="update cal_events set rrule='FREQ=YEARLY' WHERE rrule='RRULE:YM1 #0';";


$updates['201301211137'][]="delete FROM `cal_participants` WHERE event_id=0;";

$updates["201301251628"][]="script:2_fix_project_calendars.php";

$updates["201301291507"][]="UPDATE `cal_events` SET `status`='CONFIRMED' WHERE `status`='ACCEPTED';";

$updates["201301311600"][]="ALTER TABLE `cal_calendars` ADD `show_holidays` tinyint(1) NOT NULL DEFAULT '1';";


$updates['201303051512'][] ="CREATE  TABLE IF NOT EXISTS `cal_views_groups` (
  `view_id` INT NOT NULL ,
  `group_id` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`view_id`, `group_id`) )
ENGINE = InnoDB";

$updates['201303191307'][] ="ALTER TABLE `cal_participants` ADD INDEX ( `event_id` , `user_id` ) ;";

$updates["201305141646"][]="update cal_events set mtime=unix_timestamp(), end_time=start_time+3600 where end_time<start_time;";
$updates["201305231136"][]="ALTER IGNORE TABLE cal_participants ADD UNIQUE(event_id,email );";
$updates['201305241136'][]="ALTER TABLE `cal_events` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";

$updates['201307081131'][]="ALTER TABLE  `cal_calendars` ADD  `show_completed_tasks` BOOLEAN NOT NULL DEFAULT TRUE AFTER  `show_bdays`";

$updates['201307081131'][]="update cal_events set rrule='' where rrule='RRULE';";

$updates['201308261102'][]="ALTER TABLE  `cal_settings` ADD  `show_statuses` BOOLEAN NOT NULL DEFAULT TRUE";
$updates['201309121148'][]="ALTER TABLE  `cal_participants` ADD  `contact_id` INT NOT NULL DEFAULT  '0' AFTER  `user_id`";

$updates['201310141130'][]="ALTER TABLE `cal_calendars` ADD `enable_ics_import` TINYINT( 1 ) NOT NULL DEFAULT '0',
ADD `ics_import_url` VARCHAR( 512 ) NOT NULL DEFAULT '';";

$updates["201310141130"][]="script:3_install_cron.php";

$updates["201402171130"][]="ALTER TABLE `cal_calendars` ADD `tooltip` varchar(127) NOT NULL DEFAULT '';";

$updates["201404011700"][]="script:4_nl_koningsdag.php";
$updates['201404011700'][]="ALTER TABLE `cal_categories` ADD `acl_id` int(11) NOT NULL DEFAULT '0';";
$updates['201404011700'][]="ALTER TABLE `cal_categories` ADD `acl_id` int(11) NOT NULL DEFAULT '0';";
$updates['201404011700'][]="script:3_preset_category_acls.php";

$updates['201409161300'][]="ALTER TABLE `cal_events` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201501221443'][]="ALTER TABLE `cal_events` CHANGE `uuid` `uuid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";

$updates['201503181443'][]="insert ignore into go_acl (acl_id, user_id, level) select cat.acl_id, cal.user_id, '50' from cal_categories cat inner join cal_calendars cal on cat.calendar_id=cal.id";

$updates['201504291209'][]="ALTER TABLE `cal_categories` ADD `user_id` INT NOT NULL ; ";
$updates['201504291209'][]="update cal_categories set user_id = (select user_id from cal_calendars where id=cal_categories.calendar_id) where calendar_id>0;";

$updates['201507161208'][]="update cal_events set end_time=end_time+60 where all_day_event=1 and date_format(from_unixtime(end_time), '%H:%i')='23:58';";

$updates['201508111510'][]="ALTER TABLE `cal_calendars` ADD `version` INT UNSIGNED NOT NULL DEFAULT '1';";

$updates['201609141003'][]="ALTER TABLE `cal_events` ADD COLUMN `timezone` VARCHAR(64) NOT NULL DEFAULT '' AFTER `end_time`;";
$updates['201609141004'][]="UPDATE cal_events JOIN core_user ON cal_events.user_id = core_user.id SET cal_events.timezone = core_user.timezone;";
$updates["201609141005"][]="script:5_revert_rrule_shifting.php";

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `cal_calendar_user_colors` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_calendar_user_colors` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_calendars` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_calendars` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_events` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_events_declined` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_events_declined` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_exceptions` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_exceptions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_group_admins` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_group_admins` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_groups` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_participants` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_participants` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_settings` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views_calendars` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views_calendars` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views_groups` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_views_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cal_visible_tasklists` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cal_visible_tasklists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_cal_calendars` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_cal_calendars` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_cal_events` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_cal_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281650'][] = 'ALTER TABLE `go_links_cal_events` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_cal_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates['201701310910'][] = 'UPDATE cal_events SET timezone = (SELECT timezone FROM core_user WHERE id = 1) WHERE user_id = 0;';

$updates['201701310910'][] = "ALTER TABLE `cal_exceptions` ADD "
				. "`user_id` INT NOT NULL DEFAULT '0' AFTER `exception_event_id`, ADD "
				. "`muser_id` INT NOT NULL DEFAULT '0' AFTER `user_id`, ADD "
				. "`ctime` INT NOT NULL DEFAULT '0' AFTER `muser_id`, ADD "
				. "`mtime` INT NOT NULL DEFAULT '0' AFTER `ctime`;";

$updates['201701310910'][] = 'UPDATE cal_events SET timezone = (SELECT timezone FROM core_user WHERE id = 1) WHERE user_id = 0;';
$updates['201702060910'][] = 'UPDATE cal_events SET timezone = (SELECT timezone FROM core_user WHERE id = 1) WHERE timezone = "";';

$updates['201706010945'][] = 'ALTER TABLE `cal_events` CHANGE `reminder` `reminder` INT(11) NULL DEFAULT NULL;';
$updates['201706011000'][] = 'UPDATE `cal_events` SET `reminder` = NULL WHERE `reminder`=0;';

$updates['201706011015'][] = 'ALTER TABLE `cal_settings` CHANGE `reminder` `reminder` INT(11) NULL DEFAULT NULL;';
$updates['201706011015'][] = 'UPDATE `cal_settings` SET `reminder` = NULL WHERE `reminder`=0;';

$updates['201707131221'][] = 'ALTER TABLE `cal_settings` ADD `chack_conflict` BOOLEAN NOT NULL DEFAULT TRUE;';
$updates['201708151100'][] = 'ALTER TABLE `cal_settings` CHANGE `chack_conflict` `check_conflict` BOOLEAN NOT NULL DEFAULT TRUE;';
$updates['201809171650'][] = 'ALTER TABLE `cal_events_declined` CHANGE `uid` `uid` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;';