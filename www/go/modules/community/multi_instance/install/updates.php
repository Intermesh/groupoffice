<?php
$updates["201807311018"][] = "ALTER TABLE `multi_instance_instance` ADD `adminDisplayName` VARCHAR(190) NULL DEFAULT NULL AFTER `removeAt`, ADD `adminEmail` VARCHAR(190) NULL DEFAULT NULL AFTER `adminDisplayName`, ADD `userCount` INT NULL DEFAULT NULL AFTER `adminEmail`, ADD `loginCount` INT NULL DEFAULT NULL AFTER `userCount`, ADD `lastLogin` DATETIME NULL DEFAULT NULL AFTER `loginCount`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `lastLogin`;";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `usersMax` INT NULL DEFAULT NULL AFTER `userCount`;";
$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `storageUsage` BIGINT NULL DEFAULT NULL AFTER `modifiedAt`, ADD `storageQuota` BIGINT NULL DEFAULT NULL AFTER `storageUsage`;";

