<?php

$updates['201907161224'][] = 'ALTER TABLE `bookmarks_bookmark` CHANGE `createdBy` `createdBy` INT(11) NULL DEFAULT NULL;';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_category` CHANGE `createdBy` `createdBy` INT(11) NULL DEFAULT NULL;';

$updates['201907161224'][] = 'ALTER TABLE `bookmarks_bookmark` DROP FOREIGN KEY `bookmarks_user_ibfk_2`;';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_bookmark` DROP FOREIGN KEY `bookmarks_bookmark_ibfk_1`';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_bookmark` ADD CONSTRAINT `bookmarks_bookmark_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_category` DROP FOREIGN KEY `bookmarks_category_user_ibfk_2`;';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_category` DROP FOREIGN KEY `bookmarks_category_ibfk_2`;';
$updates['201907161224'][] = 'ALTER TABLE `bookmarks_category` ADD CONSTRAINT `bookmarks_category_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;';
