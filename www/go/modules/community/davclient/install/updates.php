<?php

$updates['202502040946'][] = 'ALTER TABLE `davclient_davaccount` CHANGE COLUMN `password` `password` VARCHAR(255) NULL DEFAULT NULL ;';
$updates['202503111342'][] = function(){
	\go\modules\community\davclient\cron\RefreshDav::install("*/5 * * * *");
};

$updates['202506161040'][] = "alter table davclient_calendar
    add lastSync DATETIME null,
    add lastError text null";
