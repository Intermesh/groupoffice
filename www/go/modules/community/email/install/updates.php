<?php


$updates['202504021144'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/email/install/migrate.sql"));
};

// thread lookup by Message-ID / References and base subject
$updates['202610230000'][] = "ALTER TABLE `email_id` ADD INDEX `email_id_messageId_idx` (`messageId` ASC);";
$updates['202610230000'][] = "ALTER TABLE `email_thread` ADD INDEX `email_thread_subjectHash_idx` (`accountId` ASC, `subjectHash` ASC);";
// addresses must go with their email like email_id and email_map, otherwise resync and vanished mail can't be deleted
$updates['202610230000'][] = "ALTER TABLE `email_address` DROP FOREIGN KEY `fk_email_address_email1`;";
$updates['202610230000'][] = "ALTER TABLE `email_address` ADD CONSTRAINT `fk_email_address_email1` FOREIGN KEY (`fk`) REFERENCES `email_email` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";

//$updates['202504021144'][] = function(){
//	// find capabilities
//};
