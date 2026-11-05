<?php
$updates['201906171103'][] = 'update core_entity set name = "WopiService" where name="Service" and moduleId = (select id from core_module where package="business" and name="wopi")';
$updates['201906171103'][] = 'ALTER TABLE `wopi_service` DROP INDEX `type`;';
$updates['201906171103'][] = 'ALTER TABLE `wopi_service` ADD UNIQUE(`type`);';
$updates['202006251434'][] = 'ALTER TABLE `wopi_service` ADD `wopiClientUri` TEXT NULL DEFAULT NULL AFTER `type`;';
$updates['202012211721'][] = "ALTER TABLE `fs_files` ADD `version` INT UNSIGNED NOT NULL DEFAULT '1' AFTER `content_expire_date`;";

$updates['202101161516'][] = function() {
	\go\modules\community\wopi\Module::configureIntermeshCloud();
};


$updates['202105181215'][] = "update core_acl_group set level = 30 where aclId in (select aclId from wopi_service);";


$updates['202405310855'][] = "alter table wopi_service modify aclId int null;";
$updates['202405310855'][] = "update wopi_service set aclId = null where aclId not in (select id from core_acl);";
$updates['202405310855'][] = "alter table wopi_service add constraint wopi_service_core_acl_id_fk foreign key (aclId) references core_acl (id);";


$updates['202405310855'][] = function() {
	// fixes missing acl's that were cleaned up by garbage collection because foreign key was missing
	\go\modules\community\wopi\model\Service::check();
};

$updates['202405310855'][] = "alter table wopi_service modify aclId int not null;";

$updates['202601151128'][] = "UPDATE fs_filehandlers
SET cls = 'go\\\\modules\\\\community\\\\wopi\\\\filehandler\\\\Collabora' where cls = 'go\\\\modules\\\\business\\\\wopi\\\\filehandler\\\\Collabora';";
$updates['202601151128'][] = "UPDATE fs_filehandlers
SET cls = 'go\\\\modules\\\\community\\\\wopi\\\\filehandler\\\\Office365' where cls = 'go\\\\modules\\\\business\\\\wopi\\\\filehandler\\\\Office365';";
