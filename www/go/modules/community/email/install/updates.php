<?php


$updates['202504021144'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/email/install/migrate.sql"));
};

//$updates['202504021144'][] = function(){
//	// find capabilities
//};
