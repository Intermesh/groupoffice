<?php
$updates["201108010000"][]="UPDATE go_modules SET version=0";

$updates["201108120000"][]="ALTER TABLE `go_users` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201108120000"][]="ALTER TABLE `go_acl_items` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201108120000"][]="ALTER TABLE `go_search_cache` DROP `table`";
$updates["201108120000"][]="ALTER TABLE `go_search_cache` DROP `url`";
$updates["201108120000"][]="ALTER TABLE `go_search_cache` DROP `link_count`";
$updates["201108120000"][]="ALTER TABLE `go_search_cache` DROP `acl_read`";
	

$updates["201108120000"][]="ALTER TABLE `go_users` CHANGE `max_rows_list` `max_rows_list` TINYINT( 4 ) NOT NULL DEFAULT '20'";
$updates["201108120000"][]="ALTER TABLE `go_users` CHANGE `registration_time` `ctime` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201108120000"][]="ALTER TABLE `go_groups` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201108181012"][]="ALTER TABLE `go_search_cache` CHANGE `description` `description` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201108181012"][]="script:11_users_to_addressbook.inc.php";

$updates["201108181012"][]="ALTER TABLE `go_users`
  DROP `initials`,
  DROP `title`,
  DROP `sex`,
  DROP `birthday`,
  DROP `department`,
  DROP `function`,
  DROP `home_phone`,
  DROP `work_phone`,
  DROP `fax`,
  DROP `cellular`,
  DROP `homepage`,
  DROP `contact_id`;";




$updates["201108301656"][]="CREATE TABLE IF NOT EXISTS `go_model_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";



$updates["201108301656"][]="ALTER TABLE `go_search_cache` CHANGE `id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201108301656"][]="ALTER TABLE `go_search_cache` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL DEFAULT '0' ";
	
$updates["201108301656"][]="ALTER TABLE `go_search_cache` CHANGE `type` `model_name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL  ";
$updates["201108301656"][]="ALTER TABLE `go_search_cache` DROP PRIMARY KEY  ";
$updates["201108301656"][]="ALTER TABLE `go_search_cache` ADD PRIMARY KEY ( `model_id` , `model_type_id` ) ;";



$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'8', 'GO_Base_Model_User'
);";

$updates["201108301656"][]="ALTER TABLE `go_search_cache` ADD `type` VARCHAR( 20 ) NOT NULL ";



$updates["201108190000"][]="RENAME TABLE `go_links_8` TO `go_links_go_users`;";
$updates["201108190000"][]="ALTER TABLE `go_links_go_users` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201108190000"][]="ALTER TABLE `go_links_go_users` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";

$updates["201109280000"][]="ALTER TABLE `go_search_cache` DROP `table`";
$updates["201109280000"][]="ALTER TABLE `go_search_cache` DROP `link_count`";
$updates["201109301050"][]="ALTER TABLE `go_users` CHANGE `show_smilies` `show_smilies` BOOL NOT NULL DEFAULT '1'";

$updates["201110050822"][]="ALTER TABLE `go_reminders` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201110050822"][]="ALTER TABLE `go_reminders` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201110050822"][]="ALTER TABLE `go_reminders` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL ";

$updates["201110050822"][]="ALTER TABLE `go_reminders` CHANGE `manual` `manual` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201110050822"][]="ALTER TABLE `go_reminders` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110050822"][]="ALTER TABLE `go_reminders_users` CHANGE `mail_sent` `mail_sent` TINYINT( 1 ) NOT NULL DEFAULT '0'";

$updates["201110070822"][]="ALTER TABLE `go_users` CHANGE `mail_reminders` `mail_reminders` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201110070822"][]="ALTER TABLE `go_users` CHANGE `popup_reminders` `popup_reminders` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201110070822"][]="ALTER TABLE `go_users` CHANGE `cache` `cache` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201110140822"][]="ALTER TABLE `go_users` DROP `cache`";

$updates["201110140822"][]="ALTER TABLE `go_users` CHANGE `mute_sound` `mute_sound` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110140822"][]="ALTER TABLE `go_users` CHANGE `enabled` `enabled` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201110140822"][]="ALTER TABLE `go_users` CHANGE `mute_reminder_sound` `mute_reminder_sound` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110140822"][]="ALTER TABLE `go_users` CHANGE `mute_new_mail_sound` `mute_new_mail_sound` BOOLEAN NOT NULL DEFAULT '0'";

$updates["201110141221"][]="UPDATE go_users SET mute_sound=0 where mute_sound=1";
$updates["201110141221"][]="UPDATE go_users SET mute_sound=1 where mute_sound=2";

$updates["201110141221"][]="UPDATE go_users SET enabled=0 where enabled=1";
$updates["201110141221"][]="UPDATE go_users SET enabled=1 where enabled=2";

$updates["201110141221"][]="UPDATE go_users SET mute_reminder_sound=0 where mute_reminder_sound=1";
$updates["201110141221"][]="UPDATE go_users SET mute_reminder_sound=1 where mute_reminder_sound=2";

$updates["201110141221"][]="UPDATE go_users SET mute_new_mail_sound=0 where mute_new_mail_sound=1";
$updates["201110141221"][]="UPDATE go_users SET mute_new_mail_sound=1 where mute_new_mail_sound=2";

$updates["201110311221"][]="ALTER TABLE `go_modules` CHANGE `admin_menu` `admin_menu` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201110311221"][]="UPDATE go_modules SET admin_menu=0 where admin_menu=1";
$updates["201110311221"][]="UPDATE go_modules SET admin_menu=1 where admin_menu=2";
	
$updates["201111011221"][]="ALTER TABLE `go_reminders` CHANGE `text` `text` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";

$updates["201111112300"][]="ALTER TABLE `go_users` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201111112300"][]="ALTER TABLE `go_users` CHANGE `password` `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";

$updates["201111112300"][]="DROP TABLE IF EXISTS `go_iso_address_format`;";

$updates["201111112300"][]="ALTER TABLE `go_groups` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$updates["201111112300"][]="ALTER TABLE `go_groups` CHANGE `admin_only` `admin_only` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201112021417"][]="UPDATE `go_users` SET `date_separator` = '/' WHERE `date_format` = 'mdY';";

$updates["201112021417"][]="CREATE TABLE IF NOT EXISTS `cf_go_users` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201201091109"][]="ALTER DATABASE CHARACTER SET utf8 COLLATE utf8_general_ci;";


$updates["201201091109"][]="script:12_users_to_companies.php";

$updates["201201091109"][]="ALTER TABLE `go_users`
	DROP `company`,
	DROP `country`,
  DROP `state`,
  DROP `city`,
  DROP `zip`,
  DROP `address`,
  DROP `address_no`,
  
  DROP `work_address`,
  DROP `work_address_no`,
  DROP `work_zip`,
  DROP `work_country`,
  DROP `work_state`,
  DROP `work_city`,
  DROP `work_fax`;";

$updates["201202131145"][]= "CREATE TABLE IF NOT EXISTS `go_advanced_searches` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`acl_id` int(11) NOT NULL DEFAULT '0',
	`data` TEXT NULL DEFAULT '',
	`model_name` VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201202131153"][]= "ALTER TABLE `go_users` CHANGE `middle_name` `middle_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201203070921"][]= "ALTER TABLE `go_users` CHANGE `sort_name` `sort_name` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'first_name'";

$updates["201203070921"][]= "update go_users set sort_name='first_name' where sort_name!='first_name' AND sort_name!='last_name'";

$updates["201203261017"][]= "ALTER TABLE `go_modules` DROP `acl_write`";

$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `username` `username` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `password` `password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `first_name` `first_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `last_name` `last_name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `email` `email` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `date_format` `date_format` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'dmY'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `time_format` `time_format` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'G:i'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `timezone` `timezone` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Europe/Amsterdam'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `start_module` `start_module` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'summary'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `language` `language` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'en'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `theme` `theme` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Default'";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `bank` `bank` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `bank_no` `bank_no` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203261017"][]= "ALTER TABLE `go_users` CHANGE `password_type` `password_type` VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'crypt'";


$updates["201203261017"][]= "ALTER TABLE `go_users`
  DROP `bank`,
  DROP `bank_no`;";


$updates["201204051001"][]= "ALTER TABLE `go_modules` ADD `enabled` BOOLEAN NOT NULL DEFAULT '1'";

$updates["201204251613"][]= "DROP TABLE IF EXISTS `go_log`;";
$updates["201204251613"][]= "CREATE TABLE IF NOT EXISTS `go_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `model_id` varchar(255) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL,
  `user_agent` varchar(100) NOT NULL DEFAULT '',
  `ip` varchar(15) NOT NULL DEFAULT '',
  `controller_route` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT '',
  `message` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

$updates["201204251613"][]= "ALTER TABLE `go_log` CHANGE `user_agent` `user_agent` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

// Change permission levels to new values
$updates["201205020900"][]="UPDATE `go_acl` SET `level`=10 WHERE `level`=1;";
$updates["201205020900"][]="UPDATE `go_acl` SET `level`=30 WHERE `level`=2;";
$updates["201205020900"][]="UPDATE `go_acl` SET `level`=40 WHERE `level`=3;";
$updates["201205020900"][]="UPDATE `go_acl` SET `level`=50 WHERE `level`=4;";

$updates["201204251613"][]= "ALTER TABLE `go_advanced_searches` ADD `model_name` VARCHAR( 100 ) NOT NULL DEFAULT ''";

$updates["201204251613"][]="ALTER TABLE `go_advanced_searches` CHANGE `model_name` `model_name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201204251613"][]="ALTER TABLE `go_acl` ADD INDEX ( `acl_id` , `user_id` ) ;";
$updates["201204251613"][]="ALTER TABLE `go_acl` ADD INDEX ( `acl_id` , `group_id` ) ;";


$updates["201204251613"][]="ALTER TABLE `go_search_cache` CHANGE `keywords` `keywords` VARCHAR( 254 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201204251613"][]="ALTER TABLE `go_search_cache` ADD INDEX ( `acl_id` ) ";
$updates["201204251613"][]="ALTER TABLE `go_search_cache` ADD INDEX ( `keywords` ) ";

$updates["201204251613"][]="ALTER TABLE `go_search_cache` CHANGE `keywords` `keywords` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201204251613"][]="update go_users set language='en' where language='';";

$updates["201206051617"][]="ALTER TABLE `go_search_cache` ADD FULLTEXT ft_keywords(
`name` ,
`keywords`
);";

$updates["201206051617"][]="ALTER TABLE go_search_cache DROP INDEX name";
$updates["201206051617"][]="ALTER TABLE go_search_cache DROP INDEX keywords";
$updates["201206051617"][]="ALTER TABLE go_search_cache DROP INDEX name_2";

$updates["201206110852"][]="ALTER TABLE `go_search_cache` ADD INDEX name( `name` ) ";

$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL ";
$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL ";

$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `model_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `model_type_id` `model_type_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201206110852"][]="ALTER TABLE `go_link_folders` CHANGE `parent_id` `parent_id` INT( 11 ) NOT NULL DEFAULT '0'";


// Change permission levels to new values
$updates["201206191425"][]="UPDATE `go_acl` SET `level`=10 WHERE `level`=1;";
$updates["201206191425"][]="UPDATE `go_acl` SET `level`=30 WHERE `level`=2;";
$updates["201206191425"][]="UPDATE `go_acl` SET `level`=40 WHERE `level`=3;";
$updates["201206191425"][]="UPDATE `go_acl` SET `level`=50 WHERE `level`=4;";


$updates["201206191755"][]="ALTER TABLE `go_acl` CHANGE `level` `level` TINYINT( 4 ) NOT NULL DEFAULT '10'";
$updates["201206191755"][]="ALTER TABLE `go_acl` CHANGE `level` `level` TINYINT( 4 ) NOT NULL DEFAULT '10'";
$updates["201206191755"][]="ALTER TABLE `go_users` ADD `digest` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `username`";

$updates["201206191755"][]="ALTER TABLE `go_users` ADD `digest` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `username`";
$updates["201208300840"][]="ALTER TABLE `go_log` CHANGE `ip` `ip` VARCHAR( 45 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201209141358"][]="update go_acl set level=50 where acl_id in (select acl_id from go_groups);";

$updates["201210021548"][]="ALTER TABLE `go_holidays` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201210021548"][]="ALTER TABLE `go_holidays` CHANGE `region` `region` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";


$updates["201201150948"][]="ALTER TABLE `go_holidays` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201201150948"][]="ALTER TABLE `go_holidays` CHANGE `region` `region` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201301170847"][]="DELETE FROM go_modules WHERE id='z-push';";

$updates["201303081706"][]="UPDATE go_users SET time_format = replace(time_format,'g:','h:');";
$updates["201303081706"][]="UPDATE go_users SET time_format = replace(time_format,'G:','H:');";

$updates["201303081706"][]="UPDATE go_users SET time_format = replace(time_format,'g:','h:');";
$updates["201303081706"][]="UPDATE go_users SET time_format = replace(time_format,'G:','H:');";

$updates["201303111600"][]="DROP TABLE IF EXISTS `go_cron`;";
$updates["201303111600"][]="CREATE TABLE IF NOT EXISTS `go_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `minutes` varchar(100) DEFAULT NULL,
  `hours` varchar(100) DEFAULT NULL,
  `monthdays` varchar(100) DEFAULT NULL,
  `months` varchar(100) DEFAULT NULL,
  `weekdays` varchar(100) DEFAULT NULL,
  `years` varchar(100) DEFAULT NULL,
  `job` varchar(255) NOT NULL,
  `runonce` tinyint(1) NOT NULL DEFAULT '0',
  `nextrun` int(11) NOT NULL DEFAULT '0',
  `lastrun` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";


$updates["201303111600"][]="CREATE TABLE IF NOT EXISTS `go_cron_groups` (
  `cronjob_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";


$updates["201303111600"][]="CREATE TABLE IF NOT EXISTS `go_cron_users` (
  `cronjob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$updates["201303111600"][]="script:13_insert_system_cron.php";

$updates["201303121400"][]="CREATE TABLE IF NOT EXISTS `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL,
  PRIMARY KEY (`cf_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `years`  `years` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '*'";
$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `weekdays`  `weekdays` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '*'";
$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `months`  `months` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '*'";
$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `monthdays`  `monthdays` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '*'";
$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `hours`  `hours` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1'";
$updates["201303140900"][]="ALTER TABLE  `go_cron` CHANGE  `minutes`  `minutes` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '1'";
$updates["201303140900"][]="UPDATE `go_cron` SET `years` = '*';";


$updates["201303181730"][]="CREATE TABLE IF NOT EXISTS `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL,
  PRIMARY KEY (`cf_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$updates["201303201500"][]="ALTER TABLE  `go_cron` ADD  `completedat` INT NOT NULL DEFAULT  '0'";

$updates["201303201600"][]="script:14_insert_disk_usage_cron.php";

$updates['201303281655'][] ="TRUNCATE TABLE `go_holidays`";
$updates['201303281655'][] ="ALTER TABLE  `go_holidays` CHANGE  `date`  `date` DATE NOT NULL";
$updates['201304111000'][] ="TRUNCATE TABLE `go_holidays`";

$updates['201305031458'][] = "ALTER TABLE  `go_acl_items` ADD  `mtime` INT NOT NULL DEFAULT  '0'";

$updates['201305031735'][]="ALTER TABLE `go_users` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";
$updates['201305240933'][]="ALTER TABLE `go_users` ADD `holidayset` VARCHAR( 10 ) NULL ";

$updates['201308151229'][]="UPDATE go_users SET theme='Group-Office' WHERE theme='Default' or theme='ExtJS';";

$updates['201309051015'][]="CREATE TABLE IF NOT EXISTS `go_working_weeks` (
	`user_id` int(11) NOT NULL DEFAULT '0',
	`mo_work_hours` int(2) NOT NULL DEFAULT '0',
	`tu_work_hours` int(2) NOT NULL DEFAULT '0',
	`we_work_hours` int(2) NOT NULL DEFAULT '0',
	`th_work_hours` int(2) NOT NULL DEFAULT '0',
	`fr_work_hours` int(2) NOT NULL DEFAULT '0',
	`sa_work_hours` int(2) NOT NULL DEFAULT '0',
	`su_work_hours` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates['201309111400'][]="ALTER TABLE `go_working_weeks` CHANGE `mo_work_hours` `mo_work_hours` INT( 2 ) NOT NULL DEFAULT '8',
CHANGE `tu_work_hours` `tu_work_hours` INT( 2 ) NOT NULL DEFAULT '8',
CHANGE `we_work_hours` `we_work_hours` INT( 2 ) NOT NULL DEFAULT '8',
CHANGE `th_work_hours` `th_work_hours` INT( 2 ) NOT NULL DEFAULT '8',
CHANGE `fr_work_hours` `fr_work_hours` INT( 2 ) NOT NULL DEFAULT '8'";
$updates['201309111400'][]="UPDATE go_working_weeks SET mo_work_hours=8 WHERE mo_work_hours=0;";
$updates['201309111400'][]="UPDATE go_working_weeks SET tu_work_hours=8 WHERE tu_work_hours=0;";
$updates['201309111400'][]="UPDATE go_working_weeks SET we_work_hours=8 WHERE we_work_hours=0;";
$updates['201309111400'][]="UPDATE go_working_weeks SET th_work_hours=8 WHERE th_work_hours=0;";
$updates['201309111400'][]="UPDATE go_working_weeks SET fr_work_hours=8 WHERE fr_work_hours=0;";
$updates['201309111700'][]="ALTER TABLE `go_working_weeks` CHANGE `mo_work_hours` `mo_work_hours` DOUBLE NOT NULL DEFAULT '8',
CHANGE `tu_work_hours` `tu_work_hours` DOUBLE NOT NULL DEFAULT '8',
CHANGE `we_work_hours` `we_work_hours` DOUBLE NOT NULL DEFAULT '8',
CHANGE `th_work_hours` `th_work_hours` DOUBLE NOT NULL DEFAULT '8',
CHANGE `fr_work_hours` `fr_work_hours` DOUBLE NOT NULL DEFAULT '8',
CHANGE `sa_work_hours` `sa_work_hours` DOUBLE NOT NULL DEFAULT '0',
CHANGE `su_work_hours` `su_work_hours` DOUBLE NOT NULL DEFAULT '0';";

$updates['201312061137'][]="ALTER TABLE go_search_cache DROP INDEX ft_keywords;";

$updates['201401031210'][]="ALTER TABLE `go_users` ADD `sort_email_addresses_by_time` TINYINT(1) NOT NULL DEFAULT '0';";

$updates['201401071347'][]="ALTER TABLE `go_users` ADD COLUMN `auto_punctuation` TINYINT(1) NOT NULL DEFAULT 0 AFTER `show_smilies`;";


$updates['201404171400'][]="UPDATE `go_acl` set `level`=50 WHERE `level` > 10 AND `acl_id` IN (SELECT `acl_id` FROM `go_modules`);";

$updates['201409231341'][]="ALTER TABLE `go_settings` CHANGE COLUMN `value` `value` LONGTEXT NULL DEFAULT NULL;";

$updates['201404171400'][]="ALTER TABLE `go_users` ADD COLUMN `disk_quota` INT NULL AFTER `files_folder_id`;";
$updates['201404171400'][]="ALTER TABLE `go_users` ADD COLUMN `disk_usage` INT NOT NULL DEFAULT '0' AFTER `files_folder_id`;";

$updates['201409251000'][]="ALTER TABLE `go_settings` CHANGE COLUMN `value` `value` LONGTEXT NULL DEFAULT NULL;";

$updates['201404171400'][]="update `go_model_types` set `model_name` = replace(`model_name`,'_','\\\\');";
$updates['201404171400'][]="update `go_search_cache` set `model_name` = replace(`model_name`,'_','\\\\');";
$updates['201404171400'][]="update `go_cron` set `job` = replace(`job`,'_','\\\\');";

$updates['201404171400'][]="ALTER TABLE `go_cron` ADD `error` TEXT NULL ,
ADD `autodestroy` BOOLEAN NOT NULL DEFAULT FALSE ;";

$updates['201405221000'][]="ALTER TABLE `go_holidays` ADD `free_day` TINYINT( 1 ) NOT NULL DEFAULT '1';";
$updates['201405221330'][]="script:15_update_dutch_holidays.php";

$updates['201405281445'][]="ALTER TABLE `go_users` ADD COLUMN `no_reminders` TINYINT(1) NOT NULL DEFAULT '0';";

$updates['201406040910'][] = "CREATE TABLE IF NOT EXISTS `go_saved_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `export_columns` text,
  `orientation` enum('V','H') NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT '1',
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;";

$updates["201409261000"][]="ALTER TABLE `go_users` CHANGE  `disk_quota`  `disk_quota` BIGINT NULL";
$updates["201409261000"][]="ALTER TABLE `go_users` CHANGE  `disk_usage`  `disk_usage` BIGINT NOT NULL DEFAULT '0'";

$updates['201409261000'][]="ALTER TABLE `go_settings` CHANGE COLUMN `value` `value` LONGTEXT NULL DEFAULT NULL;";

//Somehow these were missing on some installs so we just attempt to add them again.
$updates["201410220900"][]="ALTER TABLE `go_users` CHANGE  `disk_quota`  `disk_quota` BIGINT NULL";
$updates["201410220900"][]="ALTER TABLE `go_users` CHANGE  `disk_usage`  `disk_usage` BIGINT NOT NULL DEFAULT '0'";

$updates['201410220900'][]="ALTER TABLE `go_users` ADD COLUMN `disk_quota` BIGINT NULL";
$updates['201410220900'][]="ALTER TABLE `go_users` ADD COLUMN `disk_usage` BIGINT NOT NULL DEFAULT '0'";

$updates['201411171630'][]="UPDATE `go_users` SET `timezone` = 'Europe/Amsterdam' WHERE `go_users`.`timezone` = 'localtime';";

$updates['201503121000'][] ="TRUNCATE TABLE `go_holidays`;";

$updates['201508171535'][] ="DELETE FROM `go_state` WHERE `name` = 'go-checker-panel'";

$updates['201510081222'][] ="delete FROM `go_users_groups` where group_id not in (select id from go_groups);";
$updates['201510081222'][] ="delete FROM `go_acl` where user_id=0 AND group_id not in (select id from go_groups)";

$updates['201510229853'][] ="ALTER TABLE `go_cron` ADD `params` TEXT NULL ;";

$updates['201510231130'][] ="TRUNCATE TABLE `go_holidays`;";

$updates['201602180833'][] ="DELETE FROM `go_state` WHERE name='go-checker-panel';";

$updates['201604190915'][] = "ALTER TABLE `go_users` CHANGE `thousands_separator` `thousands_separator` VARCHAR(1) NOT NULL DEFAULT '.';";
$updates['201604190916'][] = "ALTER TABLE `go_users` CHANGE `decimal_separator` `decimal_separator` VARCHAR(1) NOT NULL DEFAULT ','";

$updates['201604291006'][] = "ALTER TABLE `go_users` ADD `popup_emails` tinyint(1) NOT NULL DEFAULT '0' AFTER `popup_reminders`;";
$updates['201604291006'][] = "UPDATE `go_users` SET `popup_emails` = `popup_reminders`";


$updates['201608051533'][] = "ALTER TABLE `go_users` ADD `email2` VARCHAR(100) NULL DEFAULT NULL;";
$updates['201608051533'][] = "ALTER TABLE `go_users` CHANGE `email2` `email2` VARCHAR(100) NULL DEFAULT NULL;";



//$updates["201610071659"][]="script:16_update_db_collation_utf8_general_ci_to_utf8mb4_unicode_ci.php";
$updates["201610071659"][]="";


$updates['201610281650'][] = 'ALTER DATABASE `'. \GO::config()->db_name .'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281659'][] = 'ALTER TABLE `go_cache` CHANGE `key` `key` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `cf_go_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_go_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_acl` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_acl` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_acl_items` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_acl_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_address_format` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_address_format` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_advanced_searches` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_advanced_searches` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_cache` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_cf_setting_tabs` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_cf_setting_tabs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_countries` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_countries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_cron` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_cron` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_cron_groups` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_cron_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_cron_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_cron_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_db_sequence` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_db_sequence` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_groups` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_holidays` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_holidays` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_link_descriptions` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_link_descriptions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_link_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_link_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_links_go_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_go_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_log` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_log` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_mail_counter` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_mail_counter` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_model_types` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_model_types` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_modules` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_modules` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_reminders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_reminders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_reminders_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_reminders_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_saved_exports` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_saved_exports` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_saved_search_queries` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_saved_search_queries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_search_cache` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_search_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_search_sync` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_search_sync` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_settings` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_state` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_state` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_users_groups` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_users_groups` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_working_weeks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_working_weeks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';


$updates["201611031131"][]="script:16_update_db_collation_utf8_general_ci_to_utf8mb4_unicode_ci.php";

$updates["201703301520"][]="ALTER TABLE `go_users` ADD `last_password_change` INT NOT NULL DEFAULT '0';";
$updates["201704051420"][]="ALTER TABLE `go_users` ADD `force_password_change` BOOLEAN NOT NULL DEFAULT FALSE;";

$updates["201704111345"][]="CREATE TABLE `go_clients` (
  `id` int(11) NOT NULL,
  `footprint` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `in_use` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$updates["201704111346"][]="ALTER TABLE `go_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_footprint` (`footprint`);";

$updates["201704111347"][]="ALTER TABLE `go_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$updates["201704191030"][]="ALTER TABLE `go_clients` CHANGE `footprint` `footprint` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";

$updates["201704241230"][]="ALTER TABLE `go_users` CHANGE `email2` `recovery_email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;";
$updates["201704241630"][]="UPDATE `go_users` SET `recovery_email`=`email` WHERE `recovery_email`='' OR `recovery_email` IS NULL;";

$updates["201707111530"][]="ALTER TABLE `go_modules` CHANGE `id` `id` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '';";

$updates["201802281015"][]="ALTER TABLE `go_log` ADD `jsonData` TEXT NULL AFTER `message`;";

$updates["201809211500"][]="ALTER TABLE `go_search_cache` CHANGE `keywords` `keywords` VARCHAR(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';";
