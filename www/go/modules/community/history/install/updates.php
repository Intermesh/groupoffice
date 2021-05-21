<?php
$updates['202102051329'][] = "ALTER TABLE `history_log_entry` ADD INDEX(`entityId`);";
$updates['202104261531'][] = "alter table history_log_entry modify entityId int default null null;";

$updates['202104261531'][] = "alter table history_log_entry
	add remoteIp varchar(50) null;";
