set foreign_key_checks =0;
-- -----------------------------------------------------
-- Table `tasks_tasklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_tasklist` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role` TINYINT(2) UNSIGNED NULL DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `createdBy` INT(11) default NULL,
	`defaultColor` VARCHAR(21) NOT NULL, # lightgoldenrodyellow
	`highestItemModSeq` VARCHAR(32) NULL DEFAULT 0,
  `aclId` INT(11) NOT NULL,
  `ownerId` INT(11) NOT NULL DEFAULT 1,
  `filesFolderId` INT(11) DEFAULT null,
  projectId int(11) null,
  groupingId int unsigned null,
  PRIMARY KEY (`id`),
  INDEX `fkCreatedBy` (`createdBy` ASC),
  INDEX `fkAcl` (`aclId` ASC),
  CONSTRAINT `tasks_tasklist_ibfk1`
    FOREIGN KEY (`aclId`)
    REFERENCES `core_acl` (`id`),
  CONSTRAINT `tasks_tasklist_ibfk2`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_task`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_task` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` VARCHAR(190) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NOT NULL DEFAULT '',
  `tasklistId` INT(11) UNSIGNED NOT NULL,
  `groupId` INT UNSIGNED NULL DEFAULT NULL,
  `responsibleUserId` INT(11) DEFAULT NULL,
  `createdBy` INT(11) default NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  `modifiedBy` INT(11) NULL DEFAULT null,
  `filesFolderId` INT(11) DEFAULT null,
  `due` DATE NULL,
  `start` DATE NULL,
   estimatedDuration int null comment 'Duration in seconds',
  `progress` TINYINT(2) NOT NULL DEFAULT 1,
  `progressUpdated` DATETIME NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `color` CHAR(6) NULL,
  `recurrenceRule` VARCHAR(400) NULL DEFAULT NULL,
  `priority` INT(11) NOT NULL DEFAULT 1,
  `freeBusyStatus` CHAR(4) NULL DEFAULT 'busy',
  `privacy` VARCHAR(7) NULL DEFAULT 'public',
  `percentComplete` TINYINT(4) NOT NULL DEFAULT 0,
  `uri` VARCHAR(190) CHARACTER SET 'ascii' COLLATE 'ascii_bin' NULL DEFAULT NULL,
  `vcalendarBlobId` BINARY(40) NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
    `location` TEXT DEFAULT NULL,
	`projectId` INT UNSIGNED NULL,
	`mileStoneId` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `list_id` (`tasklistId` ASC),
    INDEX `groupId` (`groupId` ASC),
  INDEX `rrule` (`recurrenceRule`(191) ASC),
  INDEX `uuid` (`uid` ASC),
  INDEX `fkModifiedBy` (`modifiedBy` ASC),
  INDEX `createdBy` (`createdBy` ASC),
  INDEX `filesFolderId` (`filesFolderId` ASC),
  INDEX `tasks_task_groupId_idx` (`groupId` ASC),
  INDEX `tasks_vcalendar_blob_idx` (`vcalendarBlobId` ASC),
  CONSTRAINT `tasks_task_fkModifiedBy`
    FOREIGN KEY (`modifiedBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `tasks_task_ibfk_1`
    FOREIGN KEY (`tasklistId`)
    REFERENCES `tasks_tasklist` (`id`) on delete cascade,
  CONSTRAINT `tasks_task_ibfk_2`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE SET NULL,
  CONSTRAINT `tasks_task_groupId`
    FOREIGN KEY (`groupId`)
    REFERENCES `tasks_tasklist_group` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE,
  CONSTRAINT `tasks_vcalendar_blob`
    FOREIGN KEY (`vcalendarBlobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE RESTRICT
    ON UPDATE SET NULL,
  constraint tasks_task_core_user_id_fk
    foreign key (responsibleUserId)
    references core_user (id)
    on DELETE set null
    )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_task_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_task_user` (
  `taskId` INT(11) UNSIGNED NOT NULL,
  `userId` INT NOT NULL,
  `modSeq` INT NOT NULL DEFAULT 0,
  `freeBusyStatus` CHAR(4) NOT NULL DEFAULT 'busy',
  PRIMARY KEY (`taskId`, `userId`),
  INDEX `fk_tasks_task_user_tasks_task1_idx` (`taskId` ASC),
  CONSTRAINT `fk_tasks_task_user_tasks_task1`
    FOREIGN KEY (`taskId`)
    REFERENCES `tasks_task` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tasks_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_alert` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `when` DATETIME NOT NULL,
  `acknowledged` DATETIME DEFAULT NULL,
  `relatedTo` TEXT NULL,
  `action` SMALLINT(2) NOT NULL DEFAULT 1,
  `offset` VARCHAR(45) NULL,
  `relativeTo` VARCHAR(5) NULL DEFAULT 'start',
  `taskId` INT(11) UNSIGNED NOT NULL,
  `userId` INT NOT NULL,
  PRIMARY KEY (`id`, `taskId`, `userId`),
  INDEX `fk_tasks_alert_tasks_task_user1_idx` (`taskId` ASC, `userId` ASC),
  CONSTRAINT `fk_tasks_alert_tasks_task_user1`
    FOREIGN KEY (`taskId` , `userId`)
    REFERENCES `tasks_task_user` (`taskId` , `userId`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `ownerId` INT(11) NULL,
  `tasklistId` INT(11) UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `user_id` (`ownerId` ASC),
  constraint tasks_category_ibfk_1
      foreign key (ownerId) references core_user (id)
          on delete cascade,
  constraint tasks_category_tasklist_ibfk_9
      foreign key (tasklistId) references tasks_tasklist (id)
          on delete cascade)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;



-- -----------------------------------------------------
-- Table `tasks_portlet_tasklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_portlet_tasklist` (
  `userId` INT(11) NOT NULL,
  `tasklistId` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`userId`, `tasklistId`),
  INDEX `tasklistId` (`tasklistId` ASC),
  CONSTRAINT `tasks_portlet_tasklist_ibfk_1`
    FOREIGN KEY (`userId`)
    REFERENCES `core_user` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `tasks_portlet_tasklist_ibfk_2`
    FOREIGN KEY (`tasklistId`)
    REFERENCES `tasks_tasklist` (`id`)
        ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_task_category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_task_category` (
  `taskId` INT(11) UNSIGNED NOT NULL,
  `categoryId` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`taskId`, `categoryId`),
  INDEX `tasks_task_category_ibfk_2` (`categoryId` ASC),
  CONSTRAINT `tasks_task_category_ibfk_1`
    FOREIGN KEY (`taskId`)
    REFERENCES `tasks_task` (`id`)
    ON DELETE CASCADE,
  CONSTRAINT `tasks_task_category_ibfk_2`
    FOREIGN KEY (`categoryId`)
    REFERENCES `tasks_category` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_task_custom_fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_task_custom_fields` (
  `id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tasks_task_custom_field1`
    FOREIGN KEY (`id`)
    REFERENCES `tasks_task` (`id`)
    ON DELETE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


-- -----------------------------------------------------
-- Table `tasks_tasklist_group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_tasklist_group` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `color` CHAR(6) NULL,
  `sortOrder` SMALLINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `tasklistId` INT(11) UNSIGNED NOT NULL,
  `progressChange` TINYINT(2) NULL,
  PRIMARY KEY (`id`, `tasklistId`),
UNIQUE INDEX `fk_tasks_column_tasks_id_idx` (`id` ASC),
  INDEX `fk_tasks_column_tasks_tasklist1_idx` (`tasklistId` ASC),
  CONSTRAINT `fk_tasks_column_tasks_tasklist1`
    FOREIGN KEY (`tasklistId`)
    REFERENCES `tasks_tasklist` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tasks_tasklist_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_tasklist_user` (
  `tasklistId` INT(11) UNSIGNED NOT NULL,
  `userId` INT NOT NULL,
  `modSeq` INT NOT NULL DEFAULT 0,
  `color` VARCHAR(21) NULL,
  `sortOrder` INT NULL,
  `isVisible` TINYINT(1) NOT NULL DEFAULT 0,
  `isSubscribed` TINYINT(1) NOT NULL DEFAULT 0,
	`syncToDevice` tinyint default 1 not null,
  PRIMARY KEY (`tasklistId`, `userId`),
  INDEX `fk_tasks_tasklist_user_tasks_tasklist1_idx` (`tasklistId` ASC),
  CONSTRAINT `fk_tasks_tasklist_user_tasks_tasklist1`
    FOREIGN KEY (`tasklistId`)
    REFERENCES `tasks_tasklist` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tasks_default_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks_default_alert` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `when` DATETIME NOT NULL,
  `relatedTo` TEXT NULL,
  `action` SMALLINT(2) NOT NULL DEFAULT 1,
  `offset` VARCHAR(45) NULL,
  `relativeTo` VARCHAR(5) NULL DEFAULT 'start',
  `withTime` TINYINT(1) NOT NULL DEFAULT 1,
  `tasklistId` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`, `tasklistId`),
  INDEX `fk_tasks_default_alert_tasks_tasklist1_idx` (`tasklistId` ASC),
  CONSTRAINT `fk_tasks_default_alert_tasks_tasklist1`
    FOREIGN KEY (`tasklistId`)
    REFERENCES `tasks_tasklist` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_unicode_ci;


create table tasks_user_settings
(
    userId int(11) not null,
    defaultTasklistId int(11) unsigned null,
    rememberLastItems boolean not null default false,
    lastTasklistIds varchar(255) null,
    defaultDate bool default false null,
    constraint tasks_user_settings_pk
        primary key (userId),
    constraint tasks_user_settings_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

alter table tasks_user_settings
    add constraint tasks_user_settings_tasks_tasklist_id_fk
        foreign key (defaultTasklistId) references tasks_tasklist (id)
            on delete set null;

create index tasks_task_progress_index
    on tasks_task (progress);




create table tasks_tasklist_grouping
(
    id      int unsigned auto_increment,
    name    varchar(190) not null,
    `order` int unsigned null,
    constraint tasks_tasklist_grouping_pk
        primary key (id),
    constraint tasks_tasklist_grouping_name
        unique (name)
);




alter table tasks_tasklist
    add constraint tasks_tasklist_tasks_tasklist_grouping_null_fk
        foreign key (groupingId) references tasks_tasklist_grouping (id)
            on delete set null;

alter table tasks_tasklist_group
    add constraint tasks_tasklist_group_pk
        unique (id);