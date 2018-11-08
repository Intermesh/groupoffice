<?php
$updates["201807311018"][] = "ALTER TABLE `multi_instance_instance` ADD `adminDisplayName` VARCHAR(190) NULL DEFAULT NULL AFTER `removeAt`, ADD `adminEmail` VARCHAR(190) NULL DEFAULT NULL AFTER `adminDisplayName`, ADD `userCount` INT NULL DEFAULT NULL AFTER `adminEmail`, ADD `loginCount` INT NULL DEFAULT NULL AFTER `userCount`, ADD `lastLogin` DATETIME NULL DEFAULT NULL AFTER `loginCount`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `lastLogin`;";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `usersMax` INT NULL DEFAULT NULL AFTER `userCount`;";
$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `storageUsage` BIGINT NULL DEFAULT NULL AFTER `modifiedAt`, ADD `storageQuota` BIGINT NULL DEFAULT NULL AFTER `storageUsage`;";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance`
  DROP `deletedAt`,
  DROP `removeAt`;";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `isTrial` BOOLEAN NOT NULL DEFAULT FALSE AFTER `storageQuota`;";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `enabled` BOOLEAN NOT NULL DEFAULT TRUE AFTER `isTrial`;";

$updates["201811081350"][] = "INSERT INTO `core_cron_job` (`moduleId`, `description`, `name`, `expression`, `enabled`, `nextRunAt`, `lastRunAt`, `runningSince`, `lastError`) VALUES
((select id from core_module where name='multi_instance' and package='community'), 'Deactivate trials', 'DeactivateTrials', '0 10 * * *', 1, now(), now(), NULL, NULL);";

$updates["201811081350"][] = "ALTER TABLE `multi_instance_instance` ADD `welcomeMessage` TEXT NULL DEFAULT NULL AFTER `enabled`;";
