<?php
$updates["201108291013"][]="ALTER TABLE `co_comments` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ";
$updates["201109070000"][]="ALTER TABLE `co_comments` CHANGE `link_id` `model_id` INT( 11 ) NOT NULL";
$updates["201109070000"][]="ALTER TABLE `co_comments` CHANGE `link_type` `model_type_id` INT( 11 ) NOT NULL";

$updates["201209181100"][]="CREATE TABLE IF NOT EXISTS `co_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
$updates["201209181100"][]="ALTER TABLE `co_comments` ADD `category_id` int(11) NOT NULL DEFAULT '0';";


$updates["201506291333"][]="INSERT INTO `go_model_types` (
`id` ,
`model_name`
)
VALUES (
null, 'GO\\Comments\\Model\\Comment'
);";

$updates["201507300945"][]="DELETE FROM `go_model_types` WHERE `model_name` = 'GO\\Comments\\Model\\Comment';";


$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `co_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `co_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `co_comments` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `co_comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates['201710191659'][] = "ALTER TABLE `co_comments` CHANGE `comments` `comments` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;";

//final server update for old module
$updates['201806041700'][] = 'update core_module set package=\'community\', version=0 where name=\'comments\'';
