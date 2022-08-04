<?php

$updates['202208031450'][] = "INSERT INTO `oauth2client_default_client` (`name`,`authenticationMethod`,`imapHost`,`imapPort`,`imapEncryption`,`smtpHost`,`smtpPort`,`smtpEncryption`) VALUES
	 ('Azure','Azure','outlook.office365.com',993,'tls','smtp-mail.outlook.com',587,'starttls');";
$updates['202208041158'][] = "ALTER TABLE `oauth2client_account` CHANGE COLUMN `token` `token` VARCHAR(255);";
