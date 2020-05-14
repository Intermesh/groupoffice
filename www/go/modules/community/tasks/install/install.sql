-- create tasklist table
DROP TABLE IF EXISTS `tasks_settings`;
DROP TABLE IF EXISTS `tasks_portlet_tasklist`;
DROP TABLE IF EXISTS `tasks_tasks_custom_field`;
DROP TABLE IF EXISTS `tasks_alert`;
DROP TABLE IF EXISTS `tasks_task_category`;
DROP TABLE IF EXISTS `tasks_task`;
DROP TABLE IF EXISTS `tasks_category`;
DROP TABLE IF EXISTS `tasks_tasklist`;

CREATE TABLE `tasks_tasklist` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `filesFolderId` int(11) NOT NULL DEFAULT '0',
  `version` int(10) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_tasklist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkCreatedBy` (`createdBy`),
  ADD KEY `fkAcl` (`aclId`);

ALTER TABLE `tasks_tasklist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks_tasklist`
  ADD CONSTRAINT `fkAcl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `fkCreatedBy` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`);

-- create task category table
CREATE TABLE `tasks_category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`createdBy`);

ALTER TABLE `tasks_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks_category`
  ADD CONSTRAINT `tasks_category_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`);

-- create task table
CREATE TABLE `tasks_task` (
  `id` int(11) NOT NULL,
  `uid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `tasklistId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `modifiedBy` int(11) NOT NULL DEFAULT '0',
  `start` date NOT NULL,
  `due` date NOT NULL,
  `completed` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `recurrenceRule` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `filesFolderId` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '1',
  `percentageComplete` tinyint(4) NOT NULL DEFAULT '0',
  `projectId` int(11) NOT NULL DEFAULT '0',
  `uri` varchar(190) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_task`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`tasklistId`),
  ADD KEY `rrule` (`recurrenceRule`(191)),
  ADD KEY `uuid` (`uid`),
  ADD KEY `fkModifiedBy` (`modifiedBy`),
  ADD KEY `fkProject` (`projectId`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `fk_tasks_task_ownerId` (`ownerId`),
  ADD KEY `filesFolderId` (`filesFolderId`);

ALTER TABLE `tasks_task`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks_task`
  ADD CONSTRAINT `fkModifiedBy` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`),
  ADD CONSTRAINT `tasks_task_ibfk_1` FOREIGN KEY (`tasklistId`) REFERENCES `tasks_tasklist` (`id`),
  ADD CONSTRAINT `tasks_task_owner_ibfk_2` FOREIGN KEY (`ownerId`) REFERENCES `core_user` (`id`),
  ADD CONSTRAINT `tasks_task_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`);

-- create category / task lookup table
CREATE TABLE `tasks_task_category` (
  `taskId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_task_category`
  ADD PRIMARY KEY (`taskId`,`categoryId`),
  ADD KEY `tasks_task_category_ibfk_2` (`categoryId`);

ALTER TABLE `tasks_task_category`
  ADD CONSTRAINT `tasks_task_category_ibfk_1` FOREIGN KEY (`taskId`) REFERENCES `tasks_task` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_task_category_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `tasks_category` (`id`) ON DELETE CASCADE;

-- create task alert table
CREATE TABLE `tasks_alert` (
  `id` int(11) NOT NULL,
  `taskId` int(11) NOT NULL,
  `remindDate` date NOT NULL,
  `remindTime` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_alert`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkTaskId` (`taskId`);

ALTER TABLE `tasks_alert`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tasks_alert`
  ADD CONSTRAINT `fkTaskId` FOREIGN KEY (`taskId`) REFERENCES `tasks_task` (`id`) ON DELETE CASCADE;

-- create task portlet table
CREATE TABLE `tasks_portlet_tasklist` (
  `createdBy` int(11) NOT NULL,
  `tasklistId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_portlet_tasklist`
  ADD PRIMARY KEY (`createdBy`,`tasklistId`),
  ADD KEY `tasklistId` (`tasklistId`);

ALTER TABLE `tasks_portlet_tasklist`
  ADD CONSTRAINT `tasks_portlet_tasklist_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`),
  ADD CONSTRAINT `tasks_portlet_tasklist_ibfk_2` FOREIGN KEY (`tasklistId`) REFERENCES `tasks_tasklist` (`id`);

-- create task settings table
CREATE TABLE `tasks_settings` (
  `createdBy` int(11) NOT NULL,
  `reminderDays` int(11) NOT NULL DEFAULT '0',
  `reminderTime` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `remind` tinyint(1) NOT NULL DEFAULT '0',
  `defaultTasklistId` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `tasks_settings`
  ADD PRIMARY KEY (`createdBy`);

ALTER TABLE `tasks_settings`
  ADD CONSTRAINT `tasks_settings_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`);

-- create task custom field table
CREATE TABLE `tasks_task_custom_fields` (
  `id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tasks_task_custom_field1`
     FOREIGN KEY (`id`)
     REFERENCES `tasks_task` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;