<?php
$updates = [];

$updates['201811272011'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};
$updates['201811272011'][] = "DROP TABLE addressbook_contact_custom_fields";

$updates['201811272011'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->run();
};