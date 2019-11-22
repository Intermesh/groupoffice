<?php
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `use_ssl` `use_ssl` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201201031630"][]="UPDATE em_accounts SET use_ssl=0 where use_ssl=1";
$updates["201201031630"][]="UPDATE em_accounts SET use_ssl=1 where use_ssl=2";

$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `novalidate_cert` `novalidate_cert` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201201031630"][]="UPDATE em_accounts SET novalidate_cert=0 where novalidate_cert=1";
$updates["201201031630"][]="UPDATE em_accounts SET novalidate_cert=1 where novalidate_cert=2";

$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `password_encrypted` `password_encrypted` TINYINT( 4 ) NOT NULL DEFAULT '0'";

$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `spamtag`;";
$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `examine_headers`;";
$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `auto_check`;";
$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `forward_enabled`;";
$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `forward_to`;";
$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `forward_local_copy`;";

$updates["201201031630"][]="ALTER TABLE `em_accounts` DROP `signature`;";


$updates["201201031630"][]="ALTER TABLE `em_aliases` CHANGE `default` `default` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201201031630"][]="UPDATE em_aliases SET `default`=0 where `default`=1";
$updates["201201031630"][]="UPDATE em_aliases SET `default`=1 where `default`=2";

$updates["201201031630"][]="ALTER TABLE `em_aliases` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201201031630"][]="ALTER TABLE `em_aliases` CHANGE `signature` `signature` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";

$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `mbroot` `mbroot` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `sent` `sent` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Sent'";
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `drafts` `drafts` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Drafts'";
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `trash` `trash` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Trash'";
$updates["201201031630"][]="ALTER TABLE `em_accounts` CHANGE `spam` `spam` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'Spam'";
$updates["201205011100"][]="UPDATE `em_accounts` SET password=CONCAT('{GOCRYPT}',`password`);";
$updates["201205011230"][]="ALTER TABLE `em_accounts` CHANGE `smtp_password` `smtp_password` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';";
$updates["201205011400"][]="script:encrypt.inc.php";
$updates["201206051342"][]="ALTER TABLE `em_links` ADD `mtime` INT NOT NULL DEFAULT '0' AFTER `ctime` ";

$updates["201206121446"][]="ALTER TABLE `em_accounts` ADD `ignore_sent_folder` TINYINT( 1 ) NOT NULL DEFAULT '0'";


$updates["201206141446"][]="";
$updates["201206141446"][]="";
$updates["201206141446"][]="";

$updates["201207040933"][]="ALTER TABLE `em_links` ADD `uid` VARCHAR( 100 ) NOT NULL DEFAULT '', ADD INDEX ( `uid` ) ";

$updates["201207191730"][]="ALTER TABLE `em_accounts` ADD `sieve_port` int(11) NOT NULL;";
$updates["201207191730"][]="ALTER TABLE `em_accounts` ADD `sieve_usetls` tinyint(1) NOT NULL DEFAULT '1';";

$updates["201207191730"][]="UPDATE `em_accounts` SET `sieve_port`='2000'";

$updates["201209060935"][]="ALTER TABLE `em_filters` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201209061100"][]="CREATE TABLE IF NOT EXISTS `em_portlet_folders` (
  `account_id` int(11) NOT NULL,
	`folder_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`folder_name`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201209061400"][]="ALTER TABLE `em_accounts` ADD `check_mailboxes` TEXT;";

$updates["201209111400"][]="update `em_accounts` set check_mailboxes='INBOX';";

$updates["201209211112"][]="ALTER TABLE `em_links` CHANGE `link_id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201212171547"][]="ALTER TABLE  `em_links` CHANGE  `uid`  `uid` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''";

$updates["201303011412"][]="delete from go_state where name='em-pnl-west' or name='eml-pnl-north';";

$updates["201303011412"][]="delete from go_state where name='em-pnl-west' or name='eml-pnl-north';";

// All acls for email accounts with read permission will be updated to create permissions
$updates["201304081400"][]="UPDATE go_acl SET level=20 WHERE level = 10 AND acl_id IN (select acl_id from em_accounts) AND user_id>0;";

$updates['201304231330'][]="ALTER TABLE `em_links` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";

$updates['201306251122'][]="ALTER TABLE  `em_accounts` ADD  `do_not_mark_as_read` BOOLEAN NOT NULL DEFAULT FALSE";

$updates['201306251600'][]="ALTER TABLE `em_links` CHANGE `time` `time` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201401061330'][]="CREATE TABLE IF NOT EXISTS `em_contacts_last_mail_times` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_mail_time` int(11) NOT NULL,
  PRIMARY KEY (`contact_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates['201403100000'][]="CREATE TABLE IF NOT EXISTS `em_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  `color` varchar(6) NOT NULL,
  `user_id` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201410280000"][]="ALTER TABLE `em_labels` CHANGE `user_id` `account_id` INT( 11 ) NOT NULL";
$updates['201504010834'][]="ALTER TABLE `em_accounts` ADD INDEX(`acl_id`);";

$updates['201505111534'][]="ALTER TABLE `em_labels` ENGINE = InnoDB;";

$updates['201508271535'][]="ALTER TABLE `em_accounts` ADD `signature_below_reply` BOOLEAN NOT NULL DEFAULT FALSE ;";


// Implemented TLS for IMAP and use "use_ssl"->"deprecated_use_ssl" as backup.
$updates['201609151215'][]="ALTER TABLE `em_accounts` ADD `imap_encryption` CHAR(3) NOT NULL AFTER `password`;";
$updates['201609151216'][]="ALTER TABLE `em_accounts` CHANGE `use_ssl` `deprecated_use_ssl` TINYINT(1) NOT NULL DEFAULT '0';";
$updates["201609151217"][]='script:2_imap_encryption.php';

$updates['201609201400'][]="ALTER TABLE `em_accounts` ADD `smtp_allow_self_signed` BOOLEAN NOT NULL DEFAULT FALSE AFTER `smtp_encryption`;";
$updates['201609201430'][]="ALTER TABLE `em_accounts` ADD `imap_allow_self_signed` BOOLEAN NOT NULL DEFAULT TRUE AFTER `imap_encryption`;";


$updates['201610281650'][] = 'SET foreign_key_checks = 0;';
$updates['201610281659'][] = 'ALTER TABLE `em_links` CHANGE `uid` `uid` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `em_accounts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_accounts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_accounts_collapsed` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_accounts_collapsed` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_accounts_sort` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_accounts_sort` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_aliases` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_aliases` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_contacts_last_mail_times` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_contacts_last_mail_times` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_filters` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_filters` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_folders_expanded` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_folders_expanded` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_labels` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_labels` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_links` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_links` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_messages_cache` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_messages_cache` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `em_portlet_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `em_portlet_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `emp_folders` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `emp_folders` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_links_em_links` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_em_links` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';


$updates['201611221615'][] = 'ALTER TABLE `em_accounts` ADD `full_reply_headers` BOOLEAN NOT NULL DEFAULT FALSE;';
$updates['201801191400'][] = 'script:3_re_encrypt.php';

$updates['201801191400'][] = 'update em_accounts set `password` = concat("{GOCRYPT2}", `password`) where `password` like "def50200%"';
$updates['201801191400'][] = 'update em_accounts set `smtp_password` = concat("{GOCRYPT2}", `smtp_password`) where `smtp_password` like "def50200%"';

$updates['201801221524'][] = "ALTER TABLE `em_links` CHANGE `uid` `uid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";

$updates['201805011020'][] = "ALTER TABLE `em_accounts` 
CHANGE COLUMN `password` `password` VARCHAR(512) NULL DEFAULT NULL ,
CHANGE COLUMN `smtp_password` `smtp_password` VARCHAR(512) NOT NULL DEFAULT '';";

$updates['201811181020'][] = function() {
	$cf = new \go\core\util\ClassFinder(false);	
	$cf->addNamespace("go\\modules\\community\\email");			
	foreach($cf->findByParent(go\core\orm\Entity::class) as $cls) {
		$cls::entityType();
	}
};


$updates['201811181020'][] = "CREATE TABLE `email_template` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `ownedBy` int(11) DEFAULT NULL,
 `aclId` int(11) NOT NULL,
 `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
 `subject` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
 `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `name` (`name`),
 KEY `ownedBy` (`ownedBy`),
 KEY `aclId` (`aclId`),
 CONSTRAINT `email_template_ibfk_1` FOREIGN KEY (`ownedBy`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
 CONSTRAINT `email_template_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT";

$updates['201811181020'][] = "CREATE TABLE `email_template_attachment` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `templateId` int(11) NOT NULL,
 `blobId` binary(40) NOT NULL,
 PRIMARY KEY (`id`),
 KEY `templateId` (`templateId`),
 KEY `blobId` (`blobId`),
 CONSTRAINT `email_template_attachment_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
 CONSTRAINT `email_template_attachment_ibfk_2` FOREIGN KEY (`templateId`) REFERENCES `email_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT";

$updates['201905111651'][] = "DROP TABLE `email_template_attachment`;";
$updates['201905111651'][] = "DROP TABLE `email_template`;";
$updates['201906271420'][] = "DELETE FROM go_state WHERE name='em-pnl-west'";