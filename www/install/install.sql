SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

DROP TABLE IF EXISTS `core_user_custom_fields`;
CREATE TABLE `core_user_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


DROP TABLE IF EXISTS `core_acl`;
CREATE TABLE `core_acl` (
  `id` int(11) NOT NULL,
  `ownedBy` int(11) NOT NULL,
  `usedIn` varchar(190) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_acl_group`;
CREATE TABLE `core_acl_group` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL DEFAULT '0',
  `level` tinyint(4) NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_acl_group_changes`;
CREATE TABLE `core_acl_group_changes` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_auth_method`;
CREATE TABLE `core_auth_method` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
	`moduleId` INT NOT NULL,
  `sortOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_auth_password`;
CREATE TABLE `core_auth_password` (
  `userId` int(11) NOT NULL,
  `password` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `digest` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_auth_token`;
CREATE TABLE `core_auth_token` (
  `loginToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `expiresAt` datetime NOT NULL,
	`lastActiveAt` DATETIME NOT NULL,
  `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passedMethods` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_customfields_field`;
CREATE TABLE `core_customfields_field` (
  `id` int(11) NOT NULL,
  `fieldSetId` int(11) NOT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `databaseName` varchar(190) NOT NULL,
  `datatype` varchar(100) NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text',
  `sortOrder` int(11) NOT NULL DEFAULT '0',
  `required` tinyint(1) NOT NULL DEFAULT '0',
  `helptext` varchar(100) NOT NULL DEFAULT '',
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT '0',
  `unique_values` tinyint(1) NOT NULL DEFAULT '0',
  `prefix` varchar(32) NOT NULL DEFAULT '',
  `suffix` varchar(32) NOT NULL DEFAULT '',
  `options` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_customfields_field_set`;
CREATE TABLE `core_customfields_field_set` (
  `id` int(11) NOT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `entityId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `sortOrder` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_entity`;
CREATE TABLE `core_entity` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `clientName` varchar(190) DEFAULT NULL,
  `highestModSeq` INT NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




DROP TABLE IF EXISTS `core_group`;
CREATE TABLE `core_group` (
  `id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `isUserGroupFor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_link`;
CREATE TABLE `core_link` (
  `id` int(11) NOT NULL,
  `fromEntityTypeId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toEntityTypeId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `folderId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_module`;
CREATE TABLE `core_module` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `package` varchar(100) DEFAULT NULL,
  `version` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `admin_menu` tinyint(1) NOT NULL DEFAULT '0',
  `aclId` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `modifiedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_search`;
CREATE TABLE `core_search` (
  `id` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `entityTypeId` int(11) NOT NULL,
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `modifiedAt` datetime DEFAULT NULL,
  `aclId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_setting`;
CREATE TABLE `core_setting` (
  `moduleId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `core_user`;
CREATE TABLE `core_user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `displayName` varchar(190) DEFAULT '',
  `avatarId` binary(40) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(100) NOT NULL,
  `recoveryEmail` varchar(100) NOT NULL,
  `recoveryHash` varchar(40) DEFAULT NULL,
  `recoverySendAt` datetime DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `dateFormat` varchar(20) NOT NULL DEFAULT 'd-m-Y',
	`shortDateInList` BOOLEAN NOT NULL DEFAULT TRUE,
  `timeFormat` varchar(10) NOT NULL DEFAULT 'G:i',
  `thousandsSeparator` varchar(1) NOT NULL DEFAULT '.',
  `decimalSeparator` varchar(1) NOT NULL DEFAULT ',',
  `currency` char(3) NOT NULL DEFAULT '',
  `loginCount` int(11) NOT NULL DEFAULT 0,
  `max_rows_list` tinyint(4) NOT NULL DEFAULT 20,
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/Amsterdam',
  `start_module` varchar(50) NOT NULL DEFAULT 'summary',
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `theme` varchar(20) NOT NULL DEFAULT 'Default',
  `firstWeekday` tinyint(4) NOT NULL DEFAULT 0,
  `sort_name` varchar(20) NOT NULL DEFAULT 'first_name',
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `mute_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_reminder_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_new_mail_sound` tinyint(1) NOT NULL DEFAULT 0,
  `show_smilies` tinyint(1) NOT NULL DEFAULT 1,
  `auto_punctuation` tinyint(1) NOT NULL DEFAULT 0,
  `listSeparator` char(3) NOT NULL DEFAULT ';',
  `textSeparator` char(3) NOT NULL DEFAULT '"',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `disk_quota` bigint(20) DEFAULT NULL,
  `disk_usage` bigint(20) NOT NULL DEFAULT 0,
  `mail_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_emails` tinyint(1) NOT NULL DEFAULT 0,
  `holidayset` varchar(10) DEFAULT NULL,
  `sort_email_addresses_by_time` tinyint(1) NOT NULL DEFAULT 0,
  `no_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `core_user_group`;
CREATE TABLE `core_user_group` (
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_address_format`;
CREATE TABLE `go_address_format` (
  `id` int(11) NOT NULL,
  `format` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_advanced_searches`;
CREATE TABLE `go_advanced_searches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `data` text,
  `model_name` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_cache`;
CREATE TABLE `go_cache` (
  `user_id` int(11) NOT NULL,
  `key` varchar(190) NOT NULL DEFAULT '',
  `content` longtext,
  `mtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_cf_setting_tabs`;
CREATE TABLE `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_clients`;
CREATE TABLE `go_clients` (
  `id` int(11) NOT NULL,
  `footprint` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `in_use` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_countries`;
CREATE TABLE `go_countries` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) DEFAULT NULL,
  `iso_code_2` char(2) NOT NULL DEFAULT '',
  `iso_code_3` char(3) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_cron`;
CREATE TABLE `go_cron` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `minutes` varchar(100) NOT NULL DEFAULT '1',
  `hours` varchar(100) NOT NULL DEFAULT '1',
  `monthdays` varchar(100) NOT NULL DEFAULT '*',
  `months` varchar(100) NOT NULL DEFAULT '*',
  `weekdays` varchar(100) NOT NULL DEFAULT '*',
  `years` varchar(100) NOT NULL DEFAULT '*',
  `job` varchar(255) NOT NULL,
  `runonce` tinyint(1) NOT NULL DEFAULT '0',
  `nextrun` int(11) NOT NULL DEFAULT '0',
  `lastrun` int(11) NOT NULL DEFAULT '0',
  `completedat` int(11) NOT NULL DEFAULT '0',
  `error` text,
  `autodestroy` tinyint(1) NOT NULL DEFAULT '0',
  `params` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_cron_groups`;
CREATE TABLE `go_cron_groups` (
  `cronjob_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_cron_users`;
CREATE TABLE `go_cron_users` (
  `cronjob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_holidays`;
CREATE TABLE `go_holidays` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `region` varchar(10) NOT NULL DEFAULT '',
  `free_day` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_link_descriptions`;
CREATE TABLE `go_link_descriptions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_link_folders`;
CREATE TABLE `go_link_folders` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `model_id` int(11) NOT NULL DEFAULT '0',
  `model_type_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_log`;
CREATE TABLE `go_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `model_id` varchar(255) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL,
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `controller_route` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT '',
  `message` varchar(255) NOT NULL DEFAULT '',
  `jsonData` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_reminders`;
CREATE TABLE `go_reminders` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `vtime` int(11) NOT NULL DEFAULT '0',
  `snooze_time` int(11) NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT '0',
  `text` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_reminders_users`;
CREATE TABLE `go_reminders_users` (
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mail_sent` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_saved_exports`;
CREATE TABLE `go_saved_exports` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `export_columns` text,
  `orientation` enum('V','H') NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT '1',
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_saved_search_queries`;
CREATE TABLE `go_saved_search_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sql` text NOT NULL,
  `type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_search_sync`;
CREATE TABLE `go_search_sync` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '',
  `last_sync_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_settings`;
CREATE TABLE `go_settings` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_state`;
CREATE TABLE `go_state` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `go_working_weeks`;
CREATE TABLE `go_working_weeks` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `mo_work_hours` double NOT NULL DEFAULT '8',
  `tu_work_hours` double NOT NULL DEFAULT '8',
  `we_work_hours` double NOT NULL DEFAULT '8',
  `th_work_hours` double NOT NULL DEFAULT '8',
  `fr_work_hours` double NOT NULL DEFAULT '8',
  `sa_work_hours` double NOT NULL DEFAULT '0',
  `su_work_hours` double NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `core_user_custom_fields`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `core_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_avatar_id_idx` (`avatarId`);


ALTER TABLE `core_acl`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `core_acl_group`
  ADD PRIMARY KEY (`aclId`,`groupId`),
  ADD KEY `level` (`level`),
  ADD KEY `groupId` (`groupId`);

ALTER TABLE `core_acl_group_changes`
  ADD PRIMARY KEY (`aclId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

ALTER TABLE `core_auth_method`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `core_auth_password`
  ADD PRIMARY KEY (`userId`);

ALTER TABLE `core_auth_token`
  ADD PRIMARY KEY (`loginToken`),
  ADD KEY `userId` (`userId`),
  ADD KEY `accessToken` (`accessToken`);

ALTER TABLE `core_customfields_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`fieldSetId`),
  ADD KEY `modSeq` (`modSeq`);

ALTER TABLE `core_customfields_field_set`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entityId` (`entityId`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `modSeq` (`modSeq`);

ALTER TABLE `core_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `isUserGroupFor` (`isUserGroupFor`),
  ADD KEY `aclId` (`aclId`);

ALTER TABLE `core_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fromEntityId` (`fromEntityTypeId`,`fromId`,`toEntityTypeId`,`toId`) USING BTREE,
  ADD KEY `toEntity` (`toEntityTypeId`);

ALTER TABLE `core_module`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `aclId` (`aclId`);

ALTER TABLE `core_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clientName` (`clientName`),
	ADD UNIQUE KEY `name` (`name`, `moduleId`) USING BTREE,
  ADD KEY `moduleId` (`moduleId`);

ALTER TABLE `core_entity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `core_search`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entityId` (`entityId`,`entityTypeId`),
  ADD KEY `acl_id` (`aclId`),
  ADD KEY `name` (`name`),
  ADD KEY `moduleId` (`moduleId`);

ALTER TABLE `core_setting`
  ADD PRIMARY KEY (`moduleId`,`name`);

ALTER TABLE `core_user_group`
  ADD PRIMARY KEY (`groupId`,`userId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `go_address_format`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_advanced_searches`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_cache`
  ADD PRIMARY KEY (`user_id`,`key`),
  ADD KEY `mtime` (`mtime`);

ALTER TABLE `go_cf_setting_tabs`
  ADD PRIMARY KEY (`cf_category_id`);

ALTER TABLE `go_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_footprint` (`footprint`);

ALTER TABLE `go_countries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_cron`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_cron_groups`
  ADD PRIMARY KEY (`cronjob_id`,`group_id`);

ALTER TABLE `go_cron_users`
  ADD PRIMARY KEY (`cronjob_id`,`user_id`);

ALTER TABLE `go_holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region` (`region`);

ALTER TABLE `go_link_descriptions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_link_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `link_id` (`model_id`,`model_type_id`),
  ADD KEY `parent_id` (`parent_id`);

ALTER TABLE `go_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `go_reminders_users`
  ADD PRIMARY KEY (`reminder_id`,`user_id`);

ALTER TABLE `go_saved_exports`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `go_saved_search_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

ALTER TABLE `go_search_sync`
  ADD PRIMARY KEY (`user_id`,`module`);

ALTER TABLE `go_settings`
  ADD PRIMARY KEY (`user_id`,`name`);

ALTER TABLE `go_state`
  ADD PRIMARY KEY (`user_id`,`name`);

ALTER TABLE `go_working_weeks`
  ADD PRIMARY KEY (`user_id`);


ALTER TABLE `core_acl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_customfields_field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_customfields_field_set`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_module`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_search`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `core_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_advanced_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_cron`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_link_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `go_saved_exports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `core_acl_group`
  ADD CONSTRAINT `core_acl_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_acl_group_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_acl_group_changes`
  ADD CONSTRAINT `core_acl_group_changes_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_acl_group_changes_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_customfields_field`
  ADD CONSTRAINT `core_customfields_field_ibfk_1` FOREIGN KEY (`fieldSetId`) REFERENCES `core_customfields_field_set` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_customfields_field_set`
  ADD CONSTRAINT `core_customfields_field_set_ibfk_1` FOREIGN KEY (`entityId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_customfields_field_set_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

ALTER TABLE `core_group`
  ADD CONSTRAINT `core_group_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `core_group_ibfk_2` FOREIGN KEY (`isUserGroupFor`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_link`
  ADD CONSTRAINT `fromEntity` FOREIGN KEY (`fromEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `toEntity` FOREIGN KEY (`toEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_module`
  ADD CONSTRAINT `acl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

ALTER TABLE `core_setting`
  ADD CONSTRAINT `module` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_user_group`
  ADD CONSTRAINT `core_user_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_user_group_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_auth_method` ADD INDEX(`moduleId`);
ALTER TABLE `core_auth_method` ADD FOREIGN KEY (`moduleId`) REFERENCES `core_module`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

ALTER TABLE `core_auth_password` ADD FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

CREATE TABLE `core_blob` (
  `id` BINARY(40) NOT NULL,
  `type` varchar(129) NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT '0',
  `modified` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `createdAt` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

ALTER TABLE `core_search` ADD FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

	
ALTER TABLE `core_user`
  ADD CONSTRAINT `fk_user_avatar_id` FOREIGN KEY (`avatarId`) REFERENCES `core_blob` (`id`) ON UPDATE NO ACTION;



ALTER TABLE `core_entity`
  ADD CONSTRAINT `core_entity_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

ALTER TABLE `core_user_custom_fields`
  ADD CONSTRAINT `core_user_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;





CREATE TABLE `core_cron_job` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `nextRunAt` datetime DEFAULT NULL,
  `lastRunAt` datetime DEFAULT NULL,
  `runningSince` datetime DEFAULT NULL,
  `lastError` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `core_cron_job`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`),
  ADD KEY `moduleId` (`moduleId`);


ALTER TABLE `core_cron_job`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `core_cron_job`
  ADD CONSTRAINT `core_cron_job_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;


CREATE TABLE `core_change` (
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `destroyed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;


ALTER TABLE `core_change`
  ADD PRIMARY KEY (`entityId`,`entityTypeId`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `entityTypeId` (`entityTypeId`);



ALTER TABLE `core_change`
  ADD CONSTRAINT `core_change_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE;


DROP TABLE IF EXISTS `core_user_default_group`;
CREATE TABLE IF NOT EXISTS `core_user_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `core_user_default_group`
  ADD CONSTRAINT `core_user_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

DROP TABLE IF EXISTS `core_group_default_group`;
CREATE TABLE IF NOT EXISTS `core_group_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `core_group_default_group`
  ADD CONSTRAINT `core_group_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;
