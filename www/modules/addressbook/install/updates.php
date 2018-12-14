<?php
$updates["201108131011"][]="ALTER TABLE `ab_companies` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` ADD `go_user_id` INT NOT NULL , ADD INDEX ( `go_user_id` )";
$updates["201108131011"][]="ALTER TABLE `ab_addressbooks` DROP `acl_write`";
$updates["201108131011"][]="ALTER TABLE `ab_addressbooks` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201108131011"][]="ALTER TABLE `ab_addressbooks` ADD `files_folder_id` INT NOT NULL";
$updates["201108131011"][]="ALTER TABLE `ab_addressbooks` ADD `users` BOOLEAN NOT NULL ";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` DROP `source_id`"; 
$updates["201108131011"][]="ALTER TABLE `ab_contacts` DROP `link_id` ";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` CHANGE `email2` `email2` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` CHANGE `email3` `email3` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` CHANGE `email` `email` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` CHANGE `email_allowed` `email_allowed` ENUM( '0', '1' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1'";
$updates["201108131011"][]="ALTER TABLE `ab_contacts` DROP `color`"; 
$updates["201108131011"][]="ALTER TABLE `ab_contacts` DROP `sid`"; 


$updates["201109011450"][]="RENAME TABLE `go_links_2` TO `go_links_ab_contacts`;";
$updates["201109011450"][]="ALTER TABLE `go_links_ab_contacts` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201109011450"][]="ALTER TABLE `go_links_ab_contacts` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201109011450"][]="RENAME TABLE `go_links_3` TO `go_links_ab_companies`;";
$updates["201109011450"][]="ALTER TABLE `go_links_ab_companies` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201109011450"][]="ALTER TABLE `go_links_ab_companies` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201109011450"][]="ALTER TABLE `cf_2` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201109011450"][]="RENAME TABLE `cf_2` TO `cf_ab_contacts` ;";

$updates["201109011450"][]="ALTER TABLE `cf_3` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201109011450"][]="RENAME TABLE `cf_3` TO `cf_ab_companies` ;";


$updates["201109021000"][]="ALTER TABLE `ab_contacts` DROP `iso_address_format` ";

$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'2', 'GO_Addressbook_Model_Contact'
);";
$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'3', 'GO_Addressbook_Model_Company'
);";


$updates["201110031344"][]="ALTER TABLE `ab_companies` DROP `iso_address_format`";
$updates["201110031344"][]="ALTER TABLE `ab_companies` DROP `post_iso_address_format` ";
$updates["201110031344"][]="ALTER TABLE `ab_companies` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110031344"][]="ALTER TABLE `ab_addressbooks` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` CHANGE `go_user_id` `go_user_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` DROP `link_id` ";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` CHANGE `email_allowed` `email_allowed` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` DROP `sid` ";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` DROP `color` ";
$updates["201110031344"][]="ALTER TABLE `ab_contacts` DROP `source_id`";


$updates["201110141221"][]="UPDATE ab_contacts SET email_allowed=0 where email_allowed=1";
$updates["201110141221"][]="UPDATE ab_contacts SET email_allowed=1 where email_allowed=2";

$updates["201110170846"][]="ALTER TABLE `ab_addressbooks` DROP `default_iso_address_format`";

$updates["201110281132"][]="ALTER TABLE `ab_addressbooks` CHANGE `users` `users` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201110281132"][]="update `ab_contacts` set birthday=null where birthday='0000-00-00'";
$updates["201110281132"][]="ALTER TABLE `ab_companies` CHANGE `phone` `phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL";
$updates["201110281132"][]="ALTER TABLE `ab_companies` CHANGE `fax` `fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";

$updates["201110281132"][]="ALTER TABLE `ab_companies` CHANGE `email_allowed` `email_allowed` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201110281132"][]="UPDATE ab_companies SET email_allowed=0 where email_allowed=1";
$updates["201110281132"][]="UPDATE ab_companies SET email_allowed=1 where email_allowed=2";

$updates["201110281132"][]="ALTER TABLE `ab_companies` CHANGE `crn` `crn` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";
$updates["201110281132"][]="ALTER TABLE `ab_companies` CHANGE `iban` `iban` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";
$updates["201110281132"][]="ALTER TABLE `ab_contacts` DROP `default_salutation`";

$updates["201111141132"][]="RENAME TABLE `ml_default_templates` TO `ab_default_email_templates` ;";

$updates["201111141132"][]="CREATE TABLE IF NOT EXISTS `ab_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;"; // Added because with an existing installation with the addressbook this table was not created (Wesley).

$updates["201111141037"][]="ALTER TABLE `ab_contacts` CHANGE `comment` `comment` TEXT NOT NULL DEFAULT '';";
$updates["201111141037"][]="ALTER TABLE `ab_companies` CHANGE `comment` `comment` TEXT NOT NULL DEFAULT '';";

$updates["201111180945"][]="DROP TABLE IF EXISTS `cf_addressbook_limits`;";

$updates["201111180945"][]="DROP TABLE IF EXISTS `cf_companies_cf_categories`;";
$updates["201111180945"][]="DROP TABLE IF EXISTS `cf_contacts_cf_categories`;";

$updates["201111180945"][]="ALTER TABLE `ab_contacts` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";
$updates["201111180945"][]="ALTER TABLE `ab_companies` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ";

$updates["201111211405"][]="RENAME TABLE `ml_sendmailing_contacts` TO `ab_sendmailing_contacts`;";
$updates["201111211405"][]="RENAME TABLE `ml_sendmailing_companies` TO `ab_sendmailing_companies`;";
$updates["201111211405"][]="ALTER TABLE `ab_sendmailing_contacts` CHANGE `mailing_id` `addresslist_id` INT(11) NOT NULL DEFAULT '0' ";

$updates["201111211405"][]="ALTER TABLE `ab_sendmailing_companies` CHANGE `mailing_id` `addresslist_id` INT(11) NOT NULL DEFAULT '0' ";

$updates["201111211405"][]="ALTER TABLE `ab_sendmailing_companies` CHANGE `mailing_id` `addresslist_id` INT(11) NOT NULL DEFAULT '0' ";

$updates["201111211405"][]="RENAME TABLE `ml_templates` TO `ab_email_templates` ;";


$updates["201111211405"][]="CREATE TABLE IF NOT EXISTS `ab_sent_mailings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message_path` varchar(255) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `addresslist_id` int(11) NOT NULL,
  `alias_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `total` int(11) DEFAULT NULL,
  `sent` int(11) DEFAULT NULL,
  `errors` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201111211405"][]="CREATE TABLE IF NOT EXISTS `ab_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `addresslist_id` (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201111211405"][]="CREATE TABLE IF NOT EXISTS `ab_sendmailing_companies` (
  `addresslist_id` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201111211405"][]="CREATE TABLE IF NOT EXISTS `ab_sendmailing_contacts` (
  `addresslist_id` int(11) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201111211715"][]="ALTER TABLE `ab_sent_mailings` CHANGE `status` `status` tinyint(4) DEFAULT '0' ";
$updates["201111211715"][]="ALTER TABLE `ab_sent_mailings` CHANGE `total` `total` tinyint(4) DEFAULT '0' ";
$updates["201111211715"][]="ALTER TABLE `ab_sent_mailings` CHANGE `sent` `sent` tinyint(4) DEFAULT '0' ";
$updates["201111211715"][]="ALTER TABLE `ab_sent_mailings` CHANGE `errors` `errors` tinyint(4) DEFAULT '0' ";

$updates["201111221545"][]="RENAME TABLE `ab_sql` to `ab_search_queries` ";
$updates["201111221610"][]="ALTER TABLE `ab_search_queries` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201112011557"][]="ALTER TABLE `ab_email_templates` CHANGE `extension` `extension` varchar(4) NOT NULL DEFAULT '';";
$updates["201112011632"][]="ALTER TABLE `ab_email_templates` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201112011632"][]="ALTER TABLE `ab_sent_mailings` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201112011632"][]="ALTER TABLE `ab_search_queries` CHANGE `id` `id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201112021640"][]="ALTER TABLE `ab_contacts` CHANGE `email` `email` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201112051545"][]="ALTER TABLE `ab_contacts` ADD `aftername_title` varchar(50) NOT NULL DEFAULT '';";

$updates["201112091545"][]="ALTER TABLE `ab_contacts` CHANGE `home_phone` `home_phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201112091545"][]="ALTER TABLE `ab_contacts` CHANGE `work_phone` `work_phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201112091545"][]="ALTER TABLE `ab_contacts` CHANGE `fax` `fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201112091545"][]="ALTER TABLE `ab_contacts` CHANGE `work_fax` `work_fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201112091545"][]="ALTER TABLE `ab_contacts` CHANGE `cellular` `cellular` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201112121545"][]="ALTER TABLE `ab_contacts` CHANGE `aftername_title` `suffix` varchar(50) NOT NULL DEFAULT '';";

$updates["201112141253"][]="ALTER TABLE `ab_sendmailing_companies` CHANGE `addresslist_id` `sent_mailing_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201112141253"][]="RENAME TABLE `ab_sendmailing_companies` TO `ab_sent_mailing_companies` ;";

$updates["201112141253"][]="ALTER TABLE `ab_sendmailing_contacts` CHANGE `addresslist_id` `sent_mailing_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201112141253"][]="RENAME TABLE `ab_sendmailing_contacts` TO `ab_sent_mailing_contacts` ;";

$updates["201112221547"][]="RENAME TABLE `ml_mailing_companies` TO `ab_addresslist_companies` ;";


$updates["201112221547"][]="RENAME TABLE `ml_mailing_contacts` TO `ab_addresslist_contacts` ;";


$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_ab_contacts` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_ab_companies` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201112221547"][]="RENAME TABLE `ml_mailing_groups` TO `ab_addresslists` ;";


$updates["201201170902"][]="CREATE TABLE IF NOT EXISTS `ab_email_templates` (
  `id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `content` longblob NOT NULL,
  `extension` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201201170902"][]="update ab_email_templates set content=replace(content, '{salutation}','{contact:salutation}') where type=0;";
$updates["201201170902"][]="update ab_email_templates set content=replace(content, '{my_','{user:') where type=0;";


$updates["201202011207"][]="CREATE TABLE IF NOT EXISTS `ab_addresslists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `default_salutation` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201202011207"][]="CREATE TABLE IF NOT EXISTS `ab_addresslist_companies` (
  `addresslist_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`company_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201202011207"][]="CREATE TABLE IF NOT EXISTS `ab_addresslist_contacts` (
  `addresslist_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`contact_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201202011207"][]="ALTER TABLE `ab_addresslists` DROP `acl_write`";
$updates["201202011207"][]="ALTER TABLE `ab_addresslists` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201202011207"][]="ALTER TABLE `ab_addresslist_companies` CHANGE `group_id` `addresslist_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201202011207"][]="ALTER TABLE `ab_addresslist_contacts` CHANGE `group_id` `addresslist_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `name` `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `name2` `name2` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `address` `address` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `address_no` `address_no` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `zip` `zip` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `city` `city` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `state` `state` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `country` `country` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_address` `post_address` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_address_no` `post_address_no` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_city` `post_city` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_state` `post_state` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_country` `post_country` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `post_zip` `post_zip` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `phone` `phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `fax` `fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `email` `email` VARCHAR( 75 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `homepage` `homepage` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `bank_no` `bank_no` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `vat_no` `vat_no` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `crn` `crn` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_companies` CHANGE `iban` `iban` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT ''";

$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `middle_name` `middle_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `initials` `initials` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `title` `title` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `department` `department` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `function` `function` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `home_phone` `home_phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `work_phone` `work_phone` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `fax` `fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `work_fax` `work_fax` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `cellular` `cellular` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `country` `country` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `state` `state` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `city` `city` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `zip` `zip` VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `address` `address` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `address_no` `address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201203260900"][]="ALTER TABLE `ab_contacts` CHANGE `salutation` `salutation` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201203260900"][]="ALTER TABLE `ab_addressbooks` DROP `shared_acl`";

$updates["201203271433"][]="ALTER TABLE `ab_sent_mailings` CHANGE `total` `total` INT NOT NULL DEFAULT '0'";
$updates["201203271433"][]="ALTER TABLE `ab_sent_mailings` CHANGE `sent` `sent` INT NOT NULL DEFAULT '0'";
$updates["201203271433"][]="ALTER TABLE `ab_sent_mailings` CHANGE `errors` `errors` INT NOT NULL DEFAULT '0'";

$updates["201203271433"][]="ALTER TABLE `ab_email_templates` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";

$updates["201204180924"][]="ALTER TABLE `ab_companies` CHANGE `address_no` `address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";
$updates["201204180924"][]="ALTER TABLE `ab_companies` CHANGE `post_address_no` `post_address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL";

$updates["201204201205"][]="ALTER TABLE `ab_companies` ADD `invoice_email` varchar(75) DEFAULT '';";

$updates["201205021029"][]="insert into ab_addresslist_contacts SELECT m.group_id,c.id FROM `ml_mailing_users`m inner join ab_contacts c on c.go_user_id=m.user_id";


$updates["201205031447"][]="update cf_fields set datatype='GO_Addressbook_Customfieldtype_Contact' where datatype='contact'";

$updates["201205031447"][]="update `ab_email_templates` set content = replace(content, '{my_', '{user:') WHERE type=0";
$updates["201205031447"][]="update `ab_email_templates` set content = replace(content, '{user:company','{usercompany:name') WHERE type=0";
$updates["201205031447"][]="UPDATE `ab_email_templates` SET content = replace( content, '{user:work_', '{usercompany:' ) WHERE type =0";

$updates["201205231030"][]="CREATE TABLE IF NOT EXISTS `ab_contacts_vcard_props` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
	`parameters` varchar(1023) NOT NULL DEFAULT '',
  `value` VARCHAR( 1023 ) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";


$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{first_name}","{contact:first_name}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{middle_name}","{contact:middle_name}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{last_name}","{contact:last_name}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{initials}","{contact:initials}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{title}","{contact:title}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{email}","{contact:email}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{home_phone}","{contact:home_phone}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{fax}","{contact:fax}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{cellular}","{contact:cellular}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{address}","{contact:address}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{address_no}","{contact:address_no}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{zip}","{contact:zip}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{city}","{contact:city}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{state}","{contact:state}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{country}","{contact:country}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{company}","{contact:company}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{department}","{contact:department}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{function}","{contact:function}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_phone}","{contact:work_phone}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_fax}","{contact:work_fax}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_address}","{contact:work_address}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_address_no}","{contact:work_address_no}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_city}","{contact:work_city}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_zip}","{contact:work_zip}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_state}","{contact:work_state}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_country}","{contact:work_country}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_post_address}","{contact:work_post_address}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_address_no}","{contact:work_post_address_no}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_post_city}","{contact:work_post_city}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_post_zip}","{contact:work_post_zip}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_post_state}","{contact:work_post_state}") WHERE type =0';
$updates["201206131531"][]='UPDATE `ab_email_templates` SET content = replace(content,"{work_post_country}","{contact:work_post_country}") WHERE type =0';


//Added website field to contact main form
$updates["201207241127"][]="ALTER TABLE `ab_contacts` ADD `homepage` VARCHAR( 255 ) NULL AFTER `cellular`";

$updates["201207271144"][]="ALTER TABLE `ab_contacts` ADD `uuid` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `id` ,
ADD INDEX ( `uuid` ) ";


$updates["201210251115"][]="ALTER TABLE `ab_contacts` CHANGE `last_name` `last_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201210251115"][]="ALTER TABLE `ab_contacts` CHANGE `first_name` `first_name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201211261235"][]=""; //dummy don not remove
$updates["201211261235"][]="ALTER TABLE `ab_sent_mailings` ADD `errors` INT NOT NULL DEFAULT '0'";
$updates["201211261235"][]="update `ab_sent_mailings` set `errors`=total-sent where errors=0;";
$updates['201212031617'][]="update fs_folders set acl_id =(select acl_id from go_modules where id='files') where name='addressbook' and parent_id=0;";
$updates['201212061709'][]="ALTER TABLE `ab_companies` CHANGE `address_no` `address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates['201301111135'][]="ALTER TABLE `ab_addressbooks` CHANGE `name` `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";

$updates['201302040945'][]="ALTER TABLE `ab_contacts` ADD `cellular2` varchar(30) NOT NULL DEFAULT '' ";

$updates['201303051525'][]="ALTER TABLE  `ab_contacts` CHANGE  `salutation`  `salutation` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  ''";
$updates['201303051525'][]="ALTER TABLE `ab_default_email_templates` CHANGE `template_id` `template_id` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201304231330'][]="ALTER TABLE `ab_contacts` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";
$updates['201304231330'][]="ALTER TABLE `ab_companies` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";

$updates['201305271506'][]="ALTER TABLE  `ab_contacts` ADD  `photo` VARCHAR( 255 ) NOT NULL";
$updates['201305271506'][]="update ab_contacts set photo=concat('contacts/contact_photos/',id,'.jpg');";

$updates['201307111403'][]="script:4_set_photo_permissions.php";

$updates['201307221000'][]="ALTER TABLE  `ab_companies` ADD  `bank_bic` VARCHAR( 11 ) NOT NULL DEFAULT '' AFTER `bank_no` ;";

$updates['201307231600'][]="CREATE TABLE IF NOT EXISTS `ab_portlet_birthdays` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`addressbook_id`)
) ENGINE=InnoDB;";

$updates['201308161215'][]="ALTER TABLE  `ab_companies` ADD  `photo` VARCHAR( 255 ) NOT NULL";
$updates['201308161215'][]="script:5_set_photo_permissions_2.php";

$updates['201309161020'][]="ALTER TABLE `ab_contacts` ADD `action_date` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201309250930'][]="ALTER TABLE `ab_contacts` ADD `url_linkedin` VARCHAR( 100 ) NULL DEFAULT NULL ,
ADD `url_facebook` VARCHAR( 100 ) NULL DEFAULT NULL ,
ADD `url_twitter` VARCHAR( 100 ) NULL DEFAULT NULL;";

$updates['201310171145'][]="CREATE TABLE IF NOT EXISTS `ab_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`),
  KEY `template_id` (`template_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates['201310221010'][]="ALTER TABLE `ab_contacts` ADD `skype_name` VARCHAR( 100 ) NULL DEFAULT NULL;";


$updates["201310221010"][]="ALTER TABLE `ab_companies` CHANGE `post_address_no` `post_address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201310251010"][]="ALTER TABLE `ab_companies` CHANGE `post_address_no` `post_address_no` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";//Double because first query had an error before

$updates['201312111215'][]="ALTER TABLE `ab_sent_mailings` ADD `opened` int(11) DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailings` ADD `campaign_id` int(11) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailings` ADD `campaigns_status_id` int(11) NOT NULL DEFAULT '0';";

$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_companies` ADD `sent` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_companies` ADD `campaigns_opened` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_companies` ADD `has_error` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_companies` ADD `error_description` varchar(255) NOT NULL DEFAULT '';";

$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_contacts` ADD `sent` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_contacts` ADD `campaigns_opened` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_contacts` ADD `has_error` tinyint(1) NOT NULL DEFAULT '0';";
$updates['201312111215'][]="ALTER TABLE `ab_sent_mailing_contacts` ADD `error_description` varchar(255) NOT NULL DEFAULT '';";

$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_contacts` CHANGE `sent_mailing_id` `will_be_contact_id` INT( 11 ) NOT NULL DEFAULT '0';";
$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_contacts` CHANGE `contact_id` `sent_mailing_id` INT( 11 ) NOT NULL DEFAULT '0';";
$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_contacts` CHANGE `will_be_contact_id` `contact_id` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_companies` CHANGE `sent_mailing_id` `will_be_company_id` INT( 11 ) NOT NULL DEFAULT '0';";
$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_companies` CHANGE `company_id` `sent_mailing_id` INT( 11 ) NOT NULL DEFAULT '0';";
$updates['201312121200'][]="ALTER TABLE `ab_sent_mailing_companies` CHANGE `will_be_company_id` `company_id` INT( 11 ) NOT NULL DEFAULT '0';";

$updates['201401031040'][]="ALTER TABLE `ab_contacts` ADD `last_email_time` int(11) NOT NULL DEFAULT '0';";
$updates['201401061330'][]="ALTER TABLE `ab_contacts` DROP `last_email_time`;";


$updates['201402211238'][]="ALTER TABLE  `ab_contacts` CHANGE  `photo`  `photo` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';";

$updates['201402271100'][]="ALTER TABLE `ab_companies` CHANGE `photo` `photo` VARCHAR( 255 ) NOT NULL DEFAULT '';";

$updates['201405061600'][]="CREATE TABLE IF NOT EXISTS `ab_portlet_birthdays` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`addressbook_id`)
) ENGINE=InnoDB;";

$updates['201407151015'][]="ALTER TABLE `ab_sent_mailings` CHANGE `subject` `subject` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";

$updates['201407241350'][]="ALTER TABLE `ab_contacts` CHANGE `email` `email` varchar(100) NOT NULL DEFAULT '';";
		
$updates['201407241350'][]="ALTER TABLE  `ab_contacts` CHANGE  `photo`  `photo` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  '';";

$updates['201408211030'][]="ALTER TABLE `ab_contacts` ADD `color` CHAR( 6 ) NOT NULL DEFAULT '';";
$updates['201408211030'][]="ALTER TABLE `ab_companies` ADD `color` CHAR( 6 ) NOT NULL DEFAULT '';";

$updates['201411101445'][]="UPDATE ab_contacts SET color='000000' WHERE color IS NULL OR color='';";
$updates['201411101500'][]="ALTER TABLE `ab_contacts` CHANGE `color` `color` CHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '000000';";

$updates['201411191645'][]="UPDATE ab_companies SET color='000000' WHERE color IS NULL OR color='';";
$updates['201411191645'][]="ALTER TABLE `ab_companies` CHANGE `color` `color` CHAR( 6 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '000000';";

$updates['201501221443'][]="ALTER TABLE `ab_contacts` CHANGE `uuid` `uuid` VARCHAR(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '';";
$updates['201508171030'][]="ALTER TABLE `ab_sent_mailings` ADD `temp_pass` VARCHAR(255) NULL ;";


$updates['201508271428'][]="ALTER TABLE `ab_contacts` CHANGE `department` `department` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '';";

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281659'][] = 'ALTER TABLE `ab_contacts` CHANGE `uuid` `uuid` VARCHAR(190);';

$updates['201610281650'][] = 'ALTER TABLE `ab_addressbooks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addressbooks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslist_companies` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslist_companies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslist_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslist_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslists` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_addresslists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_companies` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_companies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_contacts_vcard_props` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_contacts_vcard_props` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_default_email_account_templates` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_default_email_account_templates` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_default_email_templates` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_default_email_templates` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_email_templates` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_email_templates` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_portlet_birthdays` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_portlet_birthdays` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_search_queries` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_search_queries` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_sent_mailing_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_sent_mailing_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `ab_sent_mailings` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `ab_sent_mailings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_ab_companies` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_ab_companies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_ab_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_ab_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `go_links_ab_companies` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_ab_companies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_ab_contacts` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_ab_contacts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates['201612130830'][] = 'ALTER TABLE `ab_sent_mailings` CHANGE `subject` `subject` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;';

$updates['201703311130'][] = "CREATE TABLE IF NOT EXISTS `ab_settings` (
  `user_id` int(11) NOT NULL,
  `default_addressbook_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


$updates['201707111519'][] = "ALTER TABLE `ab_addressbooks` ADD `create_folder` BOOLEAN NOT NULL DEFAULT FALSE;";

$updates['201709131640'][]="ALTER TABLE `ab_addresslists` ADD `ctime` INT NOT NULL DEFAULT '0' AFTER `default_salutation`, ADD `mtime` INT NOT NULL DEFAULT '0' AFTER `ctime`;";
$updates['201709131645'][]="CREATE TABLE IF NOT EXISTS `go_links_ab_addresslists` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY `model_id` (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$updates['201711091453'][] = "";
$updates['201711091453'][] = "ALTER TABLE `ab_contacts` 
	ADD `latitude` DECIMAL(10,8) NULL AFTER `address_no`,
	ADD `longitude` DECIMAL(11,8) NULL AFTER `latitude`;";

$updates['201712061453'][] = "ALTER TABLE `ab_companies` 
	ADD `latitude` DECIMAL(10,8) NULL AFTER `address_no`,
	ADD `longitude` DECIMAL(11,8) NULL AFTER `latitude`,
	ADD `post_latitude` DECIMAL(10,8) NULL AFTER `post_address_no`,
	ADD `post_longitude` DECIMAL(11,8) NULL AFTER `post_latitude`;";

$updates['201803081042'][] = "CREATE TABLE `ab_addresslist_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['201803081043'][] = "ALTER TABLE `ab_addresslist_group`
  ADD PRIMARY KEY (`id`);";

$updates['201803081044'][] = "ALTER TABLE `ab_addresslist_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$updates['201803081045'][] = "ALTER TABLE `ab_addresslists` ADD `addresslist_group_id` INT NULL AFTER `id`;";
$updates['201806251632'][] = "ALTER TABLE `ab_contacts` CHANGE `initials` `initials` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';";

$updates['201812141203'][] = 'rename table ab_default_email_templates to email_default_email_templates';
$updates['201812141203'][] = 'rename table ab_default_email_account_templates to email_default_email_account_templates';
$updates['201812141203'][] = 'rename table ab_email_templates to go_templates';


$updates['201812141203'][] = 'update core_module set package=\'community\', version=0, sort_order = sort_order + 100 where name=\'addressbook\'';