<?php
$updates["201108190000"][]='ALTER TABLE `no_categories` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ';
$updates["201108190000"][]='ALTER TABLE `no_notes` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ';

$updates["201108190000"][]="RENAME TABLE `go_links_4` TO `go_links_no_notes`;";
$updates["201108190000"][]="ALTER TABLE `go_links_no_notes` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201108190000"][]="ALTER TABLE `go_links_no_notes` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";


$updates["201109011450"][]="RENAME TABLE `cf_4` TO `cf_no_notes` ";
$updates["201109011450"][]="ALTER TABLE `cf_no_notes` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL DEFAULT '0'";


$updates["201108301656"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
'4', 'GO_Notes_Model_Note'
);";


$updates["201109230000"][]="ALTER TABLE `no_categories` ADD `files_folder_id` INT NOT NULL";
$updates["201109280000"][]="ALTER TABLE `no_notes` CHANGE `files_folder_id` `files_folder_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201110181438"][]="ALTER TABLE `no_categories` DROP `acl_write`";

$updates["201112221547"][]="CREATE TABLE IF NOT EXISTS `cf_no_notes` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201207191545"][]="ALTER TABLE `no_notes` ADD `password` varchar(255) DEFAULT '';";

$updates["201301111137"][]="ALTER TABLE `no_categories` CHANGE `name` `name` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ";

$updates["201301111137"][]="ALTER TABLE  `no_categories` CHANGE  `files_folder_id`  `files_folder_id` INT( 11 ) NOT NULL DEFAULT  '0'";

$updates['201304231330'][]="ALTER TABLE `no_notes` ADD `muser_id` int(11) NOT NULL DEFAULT '0';";


$updates['201312061138'][]="ALTER TABLE no_notes DROP INDEX content;";

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `cf_no_notes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_no_notes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_no_notes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_no_notes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `no_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `no_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `no_notes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `no_notes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

//final server update for old module
$updates['201711071212'][] = 'update core_module set package=\'community\', version=0 where name=\'notes\'';
