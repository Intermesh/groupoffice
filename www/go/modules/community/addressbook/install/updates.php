<?php
$updates = [];

$updates['201811272011'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};

$updates['201811272011'][] = "DROP TABLE addressbook_contact_custom_fields";

$updates['201811272011'][] = "ALTER TABLE `cf_ab_contacts` CHANGE `model_id` `id` INT(11) NULL DEFAULT NULL;";
$updates['201811272011'][] = "RENAME TABLE `cf_ab_contacts` TO `addressbook_contact_custom_fields`;";

$updates['201811272011'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->run();
};


$updates['201811272011'][] = "delete from addressbook_contact_custom_fields where id not in (select id from addressbook_contact);";
$updates['201811272011'][] = "ALTER TABLE `addressbook_contact_custom_fields` ADD FOREIGN KEY (`id`) REFERENCES `addressbook_contact`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";


$updates['201811282011'][] = function() {	
	$m = new \go\modules\core\customfields\install\Migrate63to64();
	$m->migrateEntity("Contact");	
};

//todo merge company custom fields