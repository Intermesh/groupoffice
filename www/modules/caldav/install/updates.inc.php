<?php
$updates["201309101354"][]="delete from dav_events where id not in (select id from cal_events);";
$updates["201309101354"][]="delete from dav_tasks where id not in (select id from ta_tasks);";

$updates["201402141216"][]="TRUNCATE dav_tasks";
$updates["201402141216"][]="TRUNCATE dav_events";

$updates["201411061210"][]="DELETE FROM `dav_events` WHERE `uri` LIKE '%/%';";

$updates['201501221443'][]="ALTER TABLE `dav_events` CHANGE `uri` `uri` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL; ";
$updates['201501221443'][]="ALTER TABLE `dav_tasks` CHANGE `uri` `uri` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL; ";

$updates['201508211200'][]="CREATE TABLE dav_calendar_changes (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";




$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281659'][] = '';//ALTER TABLE `dav_contacts` CHANGE `uri` `uri` VARCHAR(190);';

$updates['201610281659'][] = 'ALTER TABLE `cal_events` CHANGE `uuid` `uuid` VARCHAR(190);';
$updates['201610281659'][] = 'ALTER TABLE `cal_events_declined` CHANGE `uid` `uid` VARCHAR(190);';

$updates['201610281659'][] = 'ALTER TABLE `dav_events` CHANGE `uri` `uri` VARCHAR(190);';
$updates['201610281659'][] = 'ALTER TABLE `dav_tasks` CHANGE `uri` `uri` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `dav_calendar_changes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `dav_calendar_changes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = '';//'ALTER TABLE `dav_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = '';//'ALTER TABLE `dav_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `dav_events` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `dav_events` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `dav_locks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `dav_locks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `dav_tasks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `dav_tasks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'ALTER TABLE `dav_events` CHANGE `uri` `uri` VARCHAR(190);';
$updates['201610281659'][] = 'SET foreign_key_checks = 1;';


$updates['201711161544'][] = 'delete FROM `dav_events` WHERE id not in(select id from cal_events);';
$updates['201711161544'][] = 'ALTER TABLE `dav_events` ADD FOREIGN KEY (`id`) REFERENCES `cal_events`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';

$updates['201711161544'][] = 'delete FROM `dav_tasks` WHERE id not in(select id from ta_tasks);';
$updates['201711161544'][] = 'ALTER TABLE `dav_tasks` ADD FOREIGN KEY (`id`) REFERENCES `ta_tasks`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';


$updates['202004011205'][] = "ALTER TABLE `dav_events` CHANGE `uri` `uri` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";
$updates['202004011205'][] = "ALTER TABLE `dav_tasks` CHANGE `uri` `uri` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";