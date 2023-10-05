CREATE TABLE IF NOT EXISTS `calendar_calendar` (
   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(80) NOT NULL,
   `description` TEXT NULL,
    `color` CHAR(6) NOT NULL,
    `sortOrder` INT NOT NULL DEFAULT 0,
    `aclId` INT NOT NULL,
    `createdBy` INT NULL,
    `ownedBy` INT NULL,
    `highestItemModSeq` VARCHAR(32) NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_calendar_calendar_core_acl1`
    FOREIGN KEY (`aclId`)
    REFERENCES `core_acl` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_calendar_core_user_creator`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_calendar_core_user_owner`
    FOREIGN KEY (`ownedBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_calendar_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_calendar_user` (
    `calendarId` INT UNSIGNED NOT NULL,
    `userId` INT NOT NULL,
    `isSubscribed` TINYINT(1) NOT NULL DEFAULT 0,
    `isVisible` TINYINT(1) NOT NULL DEFAULT 0,
    `sortOrder` INT NOT NULL DEFAULT 0,
    `includeInAvailability` ENUM('all', 'attending', 'none') NOT NULL,
    PRIMARY KEY (`calendarId`, `userId`),
    CONSTRAINT `fk_calendar_calendar_user_calendar_calendar1`
    FOREIGN KEY (`calendarId`)
    REFERENCES `calendar_calendar` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_calendar_user_core_user1`
    FOREIGN KEY (`userId`)
    REFERENCES `core_user` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_default_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_default_alert` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `offset` VARCHAR(20) NULL,
    `relativeTo` ENUM('start', 'end') NOT NULL DEFAULT 'start',
    `when` DATE NULL,
    `calendarId` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`, `calendarId`),
    CONSTRAINT `fk_calendar_default_alert_calendar_calendar1`
    FOREIGN KEY (`calendarId`)
    REFERENCES `calendar_calendar` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_default_alert_with_time`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_default_alert_with_time` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `offset` VARCHAR(20) NULL,
    `relativeTo` ENUM('start', 'end') NOT NULL DEFAULT 'start',
    `when` DATETIME NULL,
    `calendarId` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`, `calendarId`),
    CONSTRAINT `fk_calendar_default_alert_with_time_calendar_calendar1`
    FOREIGN KEY (`calendarId`)
    REFERENCES `calendar_calendar` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_event` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `prodId` VARCHAR(100) NOT NULL DEFAULT 'GroupOffice',
    `uid` VARCHAR(45) NOT NULL,
    `sequence` INT UNSIGNED NOT NULL DEFAULT 1,
    `title` VARCHAR(45) NOT NULL,
    `description` TEXT NULL,
    `locale` VARCHAR(6) NULL,
    `showWithoutTime` TINYINT(1) NOT NULL DEFAULT 0,
    `start` DATETIME NOT NULL COMMENT '@dbType=localdatetime',
    `timeZone` VARCHAR(45) NULL,
    `duration` VARCHAR(32) NOT NULL,
    `priority` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `privacy` ENUM('public', 'private', 'secret') NOT NULL DEFAULT 'public',
    `status` ENUM('confirmed', 'cancelled', 'tentative') NOT NULL DEFAULT 'confirmed',
    `recurrenceRule` TEXT NULL DEFAULT NULL,
    `lastOccurrence` DATETIME NULL DEFAULT NULL,
    `createdAt` DATETIME NULL,
    `modifiedAt` DATETIME NULL,
    `createdBy` INT NULL,
    `modifiedBy` INT NULL,
    `isOrigin` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    INDEX `fk_calendar_event_core_user1_idx` (`createdBy` ASC),
    INDEX `fk_calendar_event_core_user2_idx` (`modifiedBy` ASC),
    CONSTRAINT `fk_calendar_event_core_user1`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_event_core_user2`
    FOREIGN KEY (`modifiedBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_calendar_event` (
     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
     `eventId` INT UNSIGNED NOT NULL,
     `calendarId` INT UNSIGNED NOT NULL,
     PRIMARY KEY (`id`),
    INDEX `fk_calendar_calendar_event_calendar_event1_idx` (`eventId` ASC),
    INDEX `fk_calendar_calendar_event_calendar_calendar1_idx` (`calendarId` ASC),
    CONSTRAINT `fk_calendar_calendar_event_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_calendar_event_calendar_calendar1`
    FOREIGN KEY (`calendarId`)
    REFERENCES `calendar_calendar` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `calendar_participant`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_participant` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `eventId` INT UNSIGNED NOT NULL,
      `name` VARCHAR(45) NULL,
    `email` VARCHAR(45) NOT NULL,
    `kind` ENUM('individual', 'group', 'location', 'resource') NOT NULL,
    `rolesMask` INT NOT NULL DEFAULT 0,
    `participationStatus` ENUM('needs-action', 'tentative', 'accepted', 'declined', 'delegated') NULL DEFAULT 'needs-action',
    `schedulePriority` INT NULL,
    `expectReply` TINYINT(1) NOT NULL DEFAULT 0,
    `scheduleUpdated` TIMESTAMP NULL,
    PRIMARY KEY (`id`, `eventId`),
    INDEX `fk_participant_calendar_event1_idx` (`eventId` ASC),
    CONSTRAINT `fk_participant_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_user` (
     `eventId` INT UNSIGNED NOT NULL,
     `userId` INT NOT NULL,
     `freeBusyStatus` ENUM('free', 'busy') NULL DEFAULT 'busy',
    `color` CHAR(6) NULL DEFAULT NULL,
    `useDefaultAlerts` TINYINT(1) NULL DEFAULT 1,
    `veventBlobId` BINARY(40) NULL,
    `modSeq` INT NOT NULL DEFAULT 0,
    PRIMARY KEY (`eventId`, `userId`),
    INDEX `fk_calendar_event_user_calendar_event1_idx` (`eventId` ASC),
    INDEX `fk_calendar_event_user_core_user1_idx` (`userId` ASC),
    INDEX `fk_calendar_event_user_core_blob1_idx` (`veventBlobId` ASC),
    CONSTRAINT `fk_calendar_event_user_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE NO ACTION,
    CONSTRAINT `fk_calendar_event_user_core_user1`
    FOREIGN KEY (`userId`)
    REFERENCES `core_user` (`id`)
    ON DELETE CASCADE,
    CONSTRAINT `fk_calendar_event_user_core_blob1`
    FOREIGN KEY (`veventBlobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE RESTRICT
    ON UPDATE NO ACTION
) ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_alert`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_alert` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `offset` VARCHAR(20) NULL,
    `relativeTo` ENUM('start', 'end') NULL DEFAULT 'start',
    `when` DATETIME NULL,
    `eventId` INT UNSIGNED NOT NULL,
    `userId` INT NOT NULL,
    PRIMARY KEY (`id`, `eventId`, `userId`),
    INDEX `fk_calendar_event_alert_calendar_event_user1_idx` (`eventId` ASC, `userId` ASC),
    CONSTRAINT `fk_calendar_event_alert_calendar_event_user1`
    FOREIGN KEY (`eventId` , `userId`)
    REFERENCES `calendar_event_user` (`eventId` , `userId`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_recurrence_override`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_recurrence_override` (
      `fk` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `recurrenceId` DATETIME NOT NULL COMMENT '@dbType=localdatetime',
      `patch` TEXT NOT NULL,
      PRIMARY KEY (`fk`, `recurrenceId`),
    INDEX `fk_recurrence_override_calendar_event1_idx` (`fk` ASC),
    CONSTRAINT `fk_recurrence_override_calendar_event1`
    FOREIGN KEY (`fk`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_related`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_related` (
    `type` INT NOT NULL,
    `uid` VARCHAR(100) NOT NULL,
    `eventId` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`type`, `uid`),
    INDEX `fk_calendar_event_related_calendar_event1_idx` (`eventId` ASC),
    CONSTRAINT `fk_calendar_event_related_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_link`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_link` (
     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
     `eventId` INT UNSIGNED NOT NULL,
     `href` VARCHAR(100) NULL,
    `type` VARCHAR(129) NOT NULL,
    `size` INT NOT NULL,
    `rel` INT NOT NULL,
    `blobId` BINARY(40) NULL,
    PRIMARY KEY (`id`, `eventId`),
    INDEX `fk_event_attachment_calendar_event1_idx` (`eventId` ASC),
    INDEX `fk_calendar_event_link_core_blob1_idx` (`blobId` ASC),
    CONSTRAINT `fk_event_attachment_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
    CONSTRAINT `fk_calendar_event_link_core_blob1`
    FOREIGN KEY (`blobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `calendar_event_location`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `calendar_event_location` (
     `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
     `eventId` INT UNSIGNED NOT NULL,
     `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `relativeTo` INT NULL,
    `latitude` DECIMAL(10,8) NULL,
    `longitude` DECIMAL(11,8) NULL,
    PRIMARY KEY (`id`, `eventId`),
    INDEX `fk_event_location_calendar_event1_idx` (`eventId` ASC),
    CONSTRAINT `fk_event_location_calendar_event1`
    FOREIGN KEY (`eventId`)
    REFERENCES `calendar_event` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
    ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `calendar_event_custom_fields` (
    `id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_event_calendar_event_cf1` FOREIGN KEY (`id`) REFERENCES `calendar_event` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB;