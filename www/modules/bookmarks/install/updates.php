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

$updates['201901301035'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/bookmarks/install/install.sql"));
};

$updates['201905241053'][] = function(){

	GO()->getDbConnection()->exec('ALTER TABLE `bm_categories` CHANGE `user_id` `user_id` INT(11) NULL;');

	GO()->getDbConnection()->exec('update bm_categories set user_id = null where user_id not in (select id from core_user);');

	GO()->getDbConnection()->exec("ALTER TABLE `bm_bookmarks` CHANGE `user_id` `user_id` INT(11) NULL DEFAULT '0';");

	GO()->getDbConnection()->exec('update bm_bookmarks set user_id = null where user_id not in (select id from core_user);');

	GO()->getDbConnection()->exec('INSERT INTO bookmarks_category (id,createdBy, aclId, name) 
	SELECT id, user_id, acl_id, name FROM bm_categories');
};

$updates['201905241053'][] = function() {
	\go\modules\community\bookmarks\controller\Bookmark::updateLogos();
};

$updates['201905241125'][] = "update core_entity set clientName='BookmarksCategory' where name='Category' and moduleId=(select id from core_module where name='bookmarks' and package='community')";
$updates['201905241125'][] = 'update core_module set package=\'community\', version=0, sort_order = sort_order + 100 where name=\'bookmarks\'';