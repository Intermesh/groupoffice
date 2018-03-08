<?php
$updates["201801151357"][] = "UPDATE `core_user` SET date_format = CONCAT_WS(`date_separator`, SUBSTR(`date_format`,1,1), SUBSTR(`date_format`,2,1), SUBSTR(`date_format`,3,1) );";
$updates["201801151358"][] = "ALTER TABLE `core_user` DROP COLUMN `date_separator`,
		  CHANGE COLUMN `date_format` `date_format` VARCHAR(20) NOT NULL DEFAULT 'd-m-Y';";
$updates["201801221035"][] = "ALTER TABLE `core_user` 
	ADD COLUMN `recoveryHash` VARCHAR(40) NULL AFTER `recoveryEmail`,
	ADD COLUMN `recoverySendAt` DATETIME NULL AFTER `recoveryHash`;";