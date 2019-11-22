<?php
$updates["201108020000"][]="ALTER TABLE `fs_folders` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201108020000"][]="ALTER TABLE `fs_files` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";


$updates["201108190000"][]="RENAME TABLE `go_links_6` TO `go_links_fs_files`;";
$updates["201108190000"][]="ALTER TABLE `go_links_fs_files` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201108190000"][]="ALTER TABLE `go_links_fs_files` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201108190000"][]="RENAME TABLE `cf_6` TO `cf_fs_files` ";
$updates["201108190000"][]="ALTER TABLE `cf_fs_files` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";



$updates["201108190000"][]="RENAME TABLE `go_links_17` TO `go_links_fs_folders`;";
$updates["201108190000"][]="ALTER TABLE `go_links_fs_folders` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201108190000"][]="ALTER TABLE `go_links_fs_folders` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201108190000"][]="RENAME TABLE `cf_17` TO `cf_fs_folders` ";
$updates["201108190000"][]="ALTER TABLE `cf_fs_folders` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201109020000"][]="ALTER TABLE `fs_files` DROP `path`";
$updates["201109020000"][]="ALTER TABLE `fs_folders` DROP `path`";

$updates["201109020000"][]="ALTER TABLE `fs_folders` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201109020000"][]="ALTER TABLE `fs_files` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";


$updates["201109050000"][]="ALTER TABLE `fs_folders` CHANGE `comments` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
$updates["201109050000"][]="ALTER TABLE `fs_files` CHANGE `comments` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";

$updates["201109060000"][]="ALTER TABLE `fs_notifications` DROP `path`";

$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'6', 'GO_Files_Model_File'
);";

//$updates["201108301656"][]="INSERT INTO `go_model_types` (
//`id` ,
//`model_name`
//)
//VALUES (
//'17', 'GO_Files_Model_Folder'
//);";


$updates["201109271656"][]="ALTER TABLE `fs_folders` CHANGE `cm_state` `cm_state` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
$updates["201109271656"][]="ALTER TABLE `fs_templates` DROP `acl_write`";
$updates["201109271656"][]="ALTER TABLE `fs_templates` CHANGE `content` `content` MEDIUMBLOB NOT NULL DEFAULT ''";
$updates["201109271656"][]="ALTER TABLE `fs_templates` CHANGE `extension` `extension` CHAR( 4 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201109271656"][]="ALTER TABLE `fs_templates` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201110211041"][]="ALTER TABLE `fs_files` CHANGE `extension` `extension` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";

$updates["201111180948"][]="DROP TABLE IF EXISTS `cf_folder_content_cf_categories`;";
$updates["201111180948"][]="DROP TABLE IF EXISTS `cf_folder_limits`;";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_fs_files` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_fs_folders` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201202061513"][]="CREATE TABLE IF NOT EXISTS `fs_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$updates["201202071144"][]="ALTER TABLE `fs_files` DROP `status_id`";


$updates["201203061115"][]="ALTER TABLE `fs_folders` CHANGE `readonly` `readonly` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201203061115"][]="UPDATE fs_folders SET readonly=0 where readonly=1";
$updates["201203061115"][]="UPDATE fs_folders SET readonly=1 where readonly=2";

$updates["201203061115"][]="ALTER TABLE `fs_folders` CHANGE `thumbs` `thumbs` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201203061115"][]="UPDATE fs_folders SET thumbs=0 where thumbs=1";
$updates["201203061115"][]="UPDATE fs_folders SET thumbs=1 where thumbs=2";

$updates["201203061115"][]="ALTER TABLE `fs_folders` CHANGE `visible` `visible` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201203061115"][]="UPDATE fs_folders SET visible=0 where visible=1";
$updates["201203061115"][]="UPDATE fs_folders SET visible=1 where visible=2";

$updates["201203091439"][]="CREATE TABLE IF NOT EXISTS `fs_folder_pref` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201205071400"][]="update cf_fields set datatype='GO_Files_Customfieldtype_File' where datatype='file'";

$updates["201205301738"][]="ALTER TABLE `fs_folders` ADD INDEX ( `parent_id` , `name` ) ;";

$updates["201205301738"][]="ALTER TABLE `fs_folders` ADD INDEX ( `visible` ) ;";

$updates["201206121503"][]="CREATE TABLE IF NOT EXISTS `fs_notification_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `modified_user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `arg1` varchar(255) NOT NULL,
  `arg2` varchar(255) NOT NULL,
  `mtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`, `status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201212141445"][]="DELETE FROM `go_model_types` WHERE `model_name`='GO_Files_Model_Folder';";

$updates["201212141445"][]='script:1_set_log_dir.php';

$updates["201212141445"][]='CREATE TABLE IF NOT EXISTS `fs_bookmarks` (
	`folder_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
$updates["201212141445"][]='script:1_set_log_dir.php';
$updates["201212141445"][]="DELETE FROM `go_model_types` WHERE `model_name`='GO_Files_Model_Folder';";

$updates["201212141445"][]='CREATE TABLE IF NOT EXISTS `fs_bookmarks` (
	`folder_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;';


$updates["201301281613"][]='CREATE TABLE IF NOT EXISTS `fs_filehandlers` (
  `user_id` int(11) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `cls` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

$updates["201304030859"][]='ALTER TABLE  `fs_files` CHANGE  `size`  `size` BIGINT NOT NULL';

$updates["201305031326"][]='CREATE TABLE IF NOT EXISTS `fs_shared_root_folders` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;';


$updates["201305161114"][]="ALTER TABLE  `fs_folders` CHANGE  `thumbs`  `thumbs` TINYINT( 1 ) NOT NULL DEFAULT  '1'";

$updates['201305161114'][]="ALTER TABLE `fs_files` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";
$updates['201305161114'][]="ALTER TABLE `fs_folders` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";

$updates['201402201215'][]="ALTER TABLE `fs_files` ADD `delete_when_expired` tinyint(1) NOT NULL DEFAULT '0';";
$updates["201402221215"][]='script:3_install_cron.php';


$updates['201403071043'][]="update `fs_filehandlers` set `cls` = replace(`cls`,'_','\\\\');";

$updates['201509211504'][] = "ALTER TABLE `fs_versions` ADD `size_bytes` BIGINT NOT NULL DEFAULT '0' ;";
$updates['201509211706'][] = "ALTER TABLE `fs_folders` ADD `quota_user_id` INT NOT NULL DEFAULT '0' AFTER `muser_id`;";
$updates['201509221527'][] = 'update fs_folders set quota_user_id=user_id';
$updates['201509221527'][] = 'script:4_install_quota_cron.php';

$updates['201609191400'][] = "ALTER TABLE `fs_files` ADD `content_expire_date` int(11) NULL DEFAULT NULL AFTER `delete_when_expired`;";



$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281659'][] = 'ALTER TABLE `fs_files` CHANGE `name` `name` VARCHAR(190);';
$updates['201610281659'][] = 'ALTER TABLE `fs_folders` CHANGE `name` `name` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `cf_fs_files` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_fs_files` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_fs_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_fs_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_bookmarks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_bookmarks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_filehandlers` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_filehandlers` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_files` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_files` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_folder_pref` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_folder_pref` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_new_files` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_new_files` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_notification_messages` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_notification_messages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_notifications` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_notifications` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_search_queries` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_search_queries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_shared_cache` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_shared_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_shared_root_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_shared_root_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_status_history` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_status_history` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_statuses` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_statuses` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_templates` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_templates` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fs_versions` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fs_versions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_links_fs_files` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_fs_files` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_links_fs_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_fs_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';




$updates['201706121115'][] = "ALTER TABLE `fs_folders` CHANGE `parent_id` `parent_id` INT(11) NOT NULL DEFAULT '0';";

$updates['201901281546'][] = "delete from go_settings where name ='files_shared_cache_ctime';";

$updates['201903070922'][] = "ALTER TABLE `fs_files` CHANGE `name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;";
$updates['201903070922'][] = "ALTER IGNORE TABLE `fs_files` ADD UNIQUE( `folder_id`, `name`);";


$updates['201903070922'][] = "ALTER TABLE `fs_folders` CHANGE `name` `name` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL;";
$updates['201903070922'][] = "ALTER IGNORE TABLE `fs_folders` ADD UNIQUE( `parent_id`, `name`);";

$updates['201903070922'][] = "ALTER TABLE `fs_folders` DROP INDEX `parent_id_2`;";

//master


$updates['201903070922'][] = "ALTER TABLE `cf_fs_folders` CHANGE `model_id` `id` INT(11) NOT NULL;";
$updates['201903070922'][] = "RENAME TABLE `cf_fs_folders` TO `fs_folders_custom_fields`;";
$updates['201903070922'][] = "delete from fs_folders_custom_fields where id not in (select id from fs_folders);";
$updates['201903070922'][] = "ALTER TABLE `fs_folders_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `fs_folders`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['201903070922'][] = "ALTER TABLE `cf_fs_files` CHANGE `model_id` `id` INT(11) NOT NULL;";
$updates['201903070922'][] = "RENAME TABLE `cf_fs_files` TO `fs_files_custom_fields`;";
$updates['201903070922'][] = "delete from fs_files_custom_fields where id not in (select id from fs_files);";
$updates['201903070922'][] = "ALTER TABLE `fs_files_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `fs_files`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$updates['201903291350'][] = function() {	
	$m = new \go\core\install\MigrateCustomFields63to64();
	$m->migrateEntity("File");	
	$m->migrateEntity("Folder");	
};


$updates['201911221720'][] = "delete FROM `go_state` WHERE `name` LIKE 'popupfb%'";
