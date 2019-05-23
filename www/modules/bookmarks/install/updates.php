<?php
$updates["201110140934"][]="ALTER TABLE `bm_bookmarks` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";
$updates["201110140934"][]="ALTER TABLE `bm_categories` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";

$updates["201202080800"][]="ALTER TABLE `bm_bookmarks` CHANGE `behave_as_module` `behave_as_module` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201202080800"][]="ALTER TABLE `bm_bookmarks` CHANGE `open_extern` `open_extern` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201202080800"][]="ALTER TABLE `bm_bookmarks` CHANGE `public_icon` `public_icon` BOOLEAN NOT NULL DEFAULT '0'";
$updates["201203011316"][]="script:1_fixPermissions.php";

$updates["201305161323"][]="ALTER TABLE  `bm_bookmarks` CHANGE  `behave_as_module`  `behave_as_module` TINYINT( 1 ) NOT NULL DEFAULT  '0'";
$updates["201305161323"][]="ALTER TABLE  `bm_bookmarks` CHANGE  `open_extern`  `open_extern` TINYINT( 1 ) NOT NULL DEFAULT  '1'";
$updates["201305161323"][]="ALTER TABLE  `bm_bookmarks` CHANGE  `public_icon`  `public_icon` TINYINT( 1 ) NOT NULL DEFAULT  '1'";


$updates["201312061136"][]="ALTER TABLE bm_bookmarks DROP INDEX content;";

$updates["201504221336"][]="ALTER TABLE `bm_categories` ADD `show_in_startmenu` BOOLEAN NOT NULL DEFAULT FALSE ;";


$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `bm_bookmarks` DROP INDEX content;';
$updates['201610281650'][] = 'ALTER TABLE `bm_bookmarks` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `bm_bookmarks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `bm_categories` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `bm_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates['201901301035'][] =  "ALTER TABLE `bm_categories` ADD INDEX `show_in_startmenu` (`show_in_startmenu`);";
$updates['201905221222'][] = 'update core_module set package=\'community\', version=0, sort_order = sort_order + 100 where name=\'bookmarks\'';
$updates['201905221222'][] = '
INSERT INTO bookmarks_category (createdBy, aclId, name) 
SELECT user_id, acl_id, name FROM bm_categories
';
$updates['201905221222'][] = '
INSERT INTO bookmarks_bookmark (categoryId, createdBy, name,content,description,logo,openExtern,behaveAsModule) 
SELECT category_id,user_id, name, content, description, logo, open_extern, behave_as_module FROM bm_bookmarks
';