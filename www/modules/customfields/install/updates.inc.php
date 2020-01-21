<?php
$updates["201108131415"][]="ALTER TABLE `cf_fields` ADD `exclude_from_grid` BOOLEAN NOT NULL";

$updates["201108131415"][]="ALTER TABLE `cf_fields` ADD `height` INT NOT NULL DEFAULT '0'";
$updates["201108131415"][] = "CREATE TABLE IF NOT EXISTS `cf_addressbook_limits` (
	`addressbook_id` int(11) NOT NULL,
	`limit_contacts_cf_categories` ENUM('0','1') NOT NULL DEFAULT '0',
	`limit_companies_cf_categories` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY (`addressbook_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201108131415"][] = "CREATE TABLE IF NOT EXISTS `cf_contacts_cf_categories` (
	`addressbook_id` int(11) NOT NULL,
	`category_id` int(11) NOT NULL,
	PRIMARY KEY (`addressbook_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201108131415"][] = "CREATE TABLE IF NOT EXISTS `cf_companies_cf_categories` (
	`addressbook_id` int(11) NOT NULL,
	`category_id` int(11) NOT NULL,
	PRIMARY KEY (`addressbook_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

//-- -------------------------------------------------------------
//-- The following two tables are named with 'folder', but they actually
//-- apply to the files contained in the mentioned folders
//-- -------------------------------------------------------------

$updates["201108131415"][] = "CREATE TABLE IF NOT EXISTS `cf_folder_limits` (
	`folder_id` int(11) NOT NULL,
	`limit` ENUM('0','1') NOT NULL DEFAULT '0',
	PRIMARY KEY (`folder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201108131415"][] = "CREATE TABLE IF NOT EXISTS `cf_folder_content_cf_categories` (
	`folder_id` int(11) NOT NULL,
	`category_id` int(11) NOT NULL,
	PRIMARY KEY (`folder_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201108231700"][]="ALTER TABLE `cf_fields` CHANGE `datatype` `datatype` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ";
$updates["201108231700"][]="ALTER TABLE `cf_fields` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Text' where datatype='text'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Textarea' where datatype='textarea'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Select' where datatype='select'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Checkbox' where datatype='checkbox'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Date' where datatype='date'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Datetime' where datatype='datetime'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Function' where datatype='function'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Heading' where datatype='heading'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Html' where datatype='html'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Infotext' where datatype='infotext'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Number' where datatype='number'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_Treeselect' where datatype='treeselect'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_TreeselectSlave' where datatype='treeselect_slave'";
$updates["201108231700"][]="update cf_fields set datatype='GO_Customfields_Customfieldtype_User' where datatype='user'";


$updates["201108231700"][]="ALTER TABLE `cf_select_options` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201108231700"][]="ALTER TABLE `cf_categories` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";



$updates["201108291450"][]="ALTER TABLE `cf_categories` CHANGE `type` `type` VARCHAR( 50 ) NOT NULL DEFAULT '0'";
$updates["201108291450"][]="ALTER TABLE `cf_categories` CHANGE `type` `extends_model` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'";

$updates["201108291450"][]="ALTER TABLE `cf_8` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201108291450"][]="RENAME TABLE `cf_8` TO `cf_core_user` ;";
$updates["201108291450"][]="update cf_categories set extends_model='GO_Base_Model_User' where extends_model=8;";


$updates["201109011450"][]="update cf_categories set extends_model='GO_Files_Model_Folder' where extends_model=17;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Tasks_Model_Task' where extends_model=12;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Licenses_Model_License' where extends_model=11;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Email_Model_Email' where extends_model=9;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Base_Model_User' where extends_model=8;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Billing_Model_Order' where extends_model=7;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Files_Model_File' where extends_model=6;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Projects_Model_Project' where extends_model=5;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Notes_Model_Note' where extends_model=4;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Addressbook_Model_Contact' where extends_model=2;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Addressbook_Model_Company' where extends_model=3;";
$updates["201109011450"][]="update cf_categories set extends_model='GO_Calendar_Model_Event' where extends_model=1;";


$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `required` `required` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `exclude_from_grid` `exclude_from_grid` TINYINT( 1 ) NOT NULL DEFAULT '0'";
$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `multiselect` `multiselect` TINYINT( 1 ) NOT NULL DEFAULT '0'";

$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `validation_regex` `validation_regex` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";

$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `nesting_level` `nesting_level` TINYINT( 4 ) NOT NULL DEFAULT '0'";

$updates["201109301430"][]="ALTER TABLE `cf_fields` CHANGE `treemaster_field_id` `treemaster_field_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110121505"][]="CREATE TABLE IF NOT EXISTS `cf_disable_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201110121505"][]="CREATE TABLE IF NOT EXISTS `cf_enabled_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";


$updates["201110141221"][]="UPDATE cf_fields SET exclude_from_grid=0 where exclude_from_grid=1";
$updates["201110141221"][]="UPDATE cf_fields SET exclude_from_grid=1 where exclude_from_grid=2";

$updates["201111031026"][]="ALTER TABLE `cf_fields` CHANGE `helptext` `helptext` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT ''";
$updates["201111031026"][]="ALTER TABLE `cf_fields` CHANGE `datatype` `datatype` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text'";
$updates["201111031026"][]="ALTER TABLE `cf_fields` CHANGE `name` `name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";
$updates["201111031026"][]="ALTER TABLE `cf_fields` CHANGE `max` `max` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201112211219"][]="ALTER TABLE `cf_fields` CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";

$updates["201205251127"][]="update cf_categories set extends_model='GO_Calendar_Model_Calendar' where extends_model=21;";

$updates["201205300840"][]="update cf_categories set extends_model='GO_Tickets_Model_Ticket' where extends_model=20;";

$updates["201205311702"][]="update `cf_fields` set height=100 where datatype='GO_Customfields_Customfieldtype_Textarea'";

$updates['201302041130'][]="ALTER TABLE `cf_fields` ADD `number_decimals` tinyint(4) NOT NULL DEFAULT '2'";

$updates['201304171600'][]="ALTER TABLE  `cf_fields` CHANGE  `sort_index`  `sort_index` INT NOT NULL DEFAULT  '0'";

$updates['201304180930'][]="CREATE TABLE IF NOT EXISTS `cf_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '',
	`field_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates['201304180930'][]="CREATE TABLE IF NOT EXISTS `cf_enabled_blocks` (
	`block_id` int(11) NOT NULL DEFAULT 0,
	`model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`block_id`,`model_id`,`model_type_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates['201304241300'][]="ALTER TABLE `cf_fields` ADD `unique_values` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `number_decimals`;";

$updates['201309181440'][]="ALTER TABLE `cf_fields` ADD `max_length` INT( 5 ) NOT NULL DEFAULT '50';";

$updates['201309191000'][]="UPDATE `cf_fields` SET max_length=255;";



$updates['201402110930'][]="ALTER TABLE `cf_fields` ADD `addressbook_ids` VARCHAR(64) NOT NULL DEFAULT '';";

$updates['201402111315'][]="ALTER TABLE `cf_fields` CHANGE `addressbook_ids` `addressbook_ids` VARCHAR(255) NOT NULL DEFAULT '';";

$updates['201403251524'][]="ALTER TABLE `cf_fields` ADD COLUMN `extra_options` VARCHAR(255) NOT NULL DEFAULT '';";
$updates['201405131536'][]="ALTER TABLE `cf_fields` CHANGE COLUMN `extra_options` `extra_options` TEXT;";

				
$updates['201405131536'][]="update `cf_categories` set `extends_model` = replace(`extends_model`,'_','\\\\');";
$updates['201405131536'][]="update `cf_fields` set `datatype` = replace(`datatype`,'_','\\\\');";


$updates['201409161000'][]="ALTER TABLE `cf_fields` ADD `prefix` VARCHAR( 32 ) NOT NULL DEFAULT '',
ADD `suffix` VARCHAR( 32 ) NOT NULL DEFAULT '';";

$updates['201503311128'][]="ALTER TABLE `cf_fields` CHANGE `category_id` `category_id` INT(11) NOT NULL DEFAULT '0';";

$updates['201506241200'][]="update `cf_enabled_blocks` set `model_type_name` = replace(`model_type_name`,'_','\\\\');";
$updates['201506271325'][]="update `cf_enabled_categories` set `model_name` = replace(`model_name`,'_','\\\\');";
$updates['201506291410'][]="update `cf_disable_categories` set `model_name` = replace(`model_name`,'_','\\\\');";

$updates['201508311114'][]="UPDATE `cf_fields` SET datatype='GO\\\\Phpcustomfield\\\\Customfieldtype\\\\Php' WHERE datatype='GO\\\\Customfields\\\\Customfieldtype\\\\Php';";

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `cf_blocks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_blocks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_disable_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_disable_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_enabled_blocks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_enabled_blocks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_enabled_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_enabled_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_fields` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_fields` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_select_options` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_select_options` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `cf_select_tree_options` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_select_tree_options` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `cf_tree_select_options` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_tree_select_options` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates["201812201715"][] = "ALTER TABLE `cf_categories` CHANGE `extends_model` `extends_model` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0';";


$updates['201911120000'][] = "ALTER TABLE `cf_fields` ADD `required_condition` varchar(255) NOT NULL DEFAULT '' AFTER `required`";