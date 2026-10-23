<?php


$updates['202504021144'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/email/install/migrate.sql"));
};

// thread lookup by Message-ID / References and base subject
$updates['202610230000'][] = "ALTER TABLE `email_id` ADD INDEX `email_id_messageId_idx` (`messageId` ASC);";
$updates['202610230000'][] = "ALTER TABLE `email_thread` ADD INDEX `email_thread_subjectHash_idx` (`accountId` ASC, `subjectHash` ASC);";

//$updates['202504021144'][] = function(){
//	// find capabilities
//};
