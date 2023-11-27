<?php
$updates["201303271710"][]="DELETE FROM go_model_types where model_name='GO_Zpushadmin_Model_Device';";
$updates["201305271635"][]="ALTER TABLE `zpa_devices` CHANGE `comments` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";
$updates["201307081322"][]="ALTER TABLE  `zpa_devices` ADD  `as_version` VARCHAR( 10 ) NULL DEFAULT NULL";

$updates["201308161048"][]="ALTER TABLE  `zpa_devices` ADD INDEX (  `device_id` ,  `username` ) ;";


$updates['201610281659'][] = 'ALTER TABLE `zpa_devices` CHANGE `username` `username` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `zpa_devices` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `zpa_devices` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['202304241649'][] = function() {

	// fix for softaculous reporting this table was somehow missing
	go()->getDbConnection()->exec("CREATE TABLE IF NOT EXISTS `zpa_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(50) NOT NULL,
  `device_type` varchar(50) NOT NULL,
  `remote_addr` varchar(45) NOT NULL,
  `can_connect` tinyint(1) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `new` tinyint(1) NOT NULL DEFAULT '1',
  `username` varchar(190) NOT NULL DEFAULT '',
  `comment` text,
  `as_version` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
	KEY `device_id` (`device_id`,`username`)
) ENGINE=InnoDB ;");

	go()->getDbConnection()->exec('alter table zpa_devices
    modify remote_addr varchar(45) not null;');
};
