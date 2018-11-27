<?php

$updates = [];

$updates['201811241647'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};

//$updates['201811241647'][] = "DROP TABLE addressbook_contact_custom_fields";
//
//$updates['201811270837'][] = "ALTER TABLE `cf_cal_calendars` CHANGE `model_id` `id` INT(11) NULL DEFAULT NULL;";
//$updates['201811270837'][] = "RENAME TABLE `cf_cal_calendars` TO `addressbook_contact_custom_fields`;";
//$updates['201811270837'][] = "delete from addressbook_contact_custom_fields where id not in (select id from cal_calendars);";
//$updates['201811270837'][] = "ALTER TABLE `addressbook_contact_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `cal_calendars`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
