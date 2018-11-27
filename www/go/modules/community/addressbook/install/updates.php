<?php

$updates = [];

$updates['201811241647'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};
