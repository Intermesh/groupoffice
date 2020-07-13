<?php
$updates["201204201630"][] = "ALTER TABLE `pa_domains` CHANGE `aliases` `max_aliases` int(10) NOT NULL default '0';";
$updates["201204201630"][] = "ALTER TABLE `pa_domains` CHANGE `mailboxes` `max_mailboxes` int(10) NOT NULL default '0';";
$updates["201204201630"][] = "ALTER TABLE `pa_domains` CHANGE `quota` `default_quota` bigint(20) NOT NULL default '0';";
$updates["201204201630"][] = "ALTER TABLE `pa_domains` CHANGE `maxquota` `total_quota` bigint(20) NOT NULL default '0';";

$updates["201204231430"][] = "ALTER TABLE `pa_domains` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201204231430"][] = "ALTER TABLE `pa_aliases` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201204231430"][] = "ALTER TABLE `pa_mailboxes` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";

$updates["201204251045"][] = "ALTER TABLE `pa_mailboxes` CHANGE `active` `active` BOOLEAN NOT NULL DEFAULT '1';";
$updates["201204251045"][] = "update pa_mailboxes set active=0 where active=1;";
$updates["201204251045"][] = "update pa_mailboxes set active=1 where active=2;";



$updates["201204251130"][] = "ALTER TABLE `pa_mailboxes` DROP `vacation_active`;";
$updates["201204251130"][] = "ALTER TABLE `pa_mailboxes` DROP `vacation_subject`;";
$updates["201204251130"][] = "ALTER TABLE `pa_mailboxes` DROP `vacation_body`;";

$updates["201204251445"][] = "ALTER TABLE `pa_mailboxes` DROP `domain`;";
$updates["201204251500"][] = "ALTER TABLE `pa_mailboxes` CHANGE `usage` `usage` int(11) NOT NULL default '0';";

$updates["201204251500"][] ="ALTER TABLE `pa_domains` CHANGE `transport` `transport` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'virtual'";

$updates["201204251500"][] = "ALTER TABLE `pa_domains` CHANGE `active` `active` BOOLEAN NOT NULL DEFAULT '1';";
$updates["201204251500"][] = "update pa_domains set active=0 where active=1;";
$updates["201204251500"][] = "update pa_domains set active=1 where active=2;";

$updates["201204251500"][] = "ALTER TABLE `pa_aliases` CHANGE `active` `active` BOOLEAN NOT NULL DEFAULT '1';";
$updates["201204251500"][] = "update pa_aliases set active=0 where active=1;";
$updates["201204251500"][] = "update pa_aliases set active=1 where active=2;";

$updates["201204251500"][] = "script:1_disable_vacation.php";

$updates["201208030852"][] = "ALTER TABLE `pa_mailboxes` DROP `domain`;";

$updates["201212071039"][] = "ALTER TABLE `pa_mailboxes` CHANGE `username` `username` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201212071039"][] = "ALTER TABLE `pa_mailboxes` CHANGE `password` `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201212071039"][] = "ALTER TABLE `pa_mailboxes` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates['201610281659'][] = 'ALTER TABLE `pa_aliases` CHANGE `address` `address` VARCHAR(190);';
$updates['201610281659'][] = 'ALTER TABLE `pa_domains` CHANGE `domain` `domain` VARCHAR(190);';
$updates['201610281659'][] = 'ALTER TABLE `pa_mailboxes` CHANGE `username` `username` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `pa_aliases` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `pa_aliases` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `pa_domains` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `pa_domains` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `pa_mailboxes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `pa_mailboxes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201806010710'][] = 'ALTER TABLE `pa_mailboxes` ADD `homedir` VARCHAR(255) DEFAULT NULL AFTER `maildir`;';
$updates['201806010710'][] = 'update `pa_mailboxes` set homedir = maildir;';

$updates['202007131017'][] = "update `pa_mailboxes` set `password` = concat('{PLAIN-MD5}', `password`) WHERE `password` NOT LIKE '{%' AND `password` NOT LIKE '$%'";

