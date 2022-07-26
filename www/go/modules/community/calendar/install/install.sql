CREATE TABLE IF NOT EXISTS `calendar_calendar` (
   `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
   `name` VARCHAR(80) NOT NULL,
    `color` CHAR(6) NOT NULL,
    `sortOrder` INT NOT NULL DEFAULT 0,
    `aclId` INT NOT NULL,
    `createdBy` INT NULL,
    `ownedBy` INT NULL,
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
    `calenderId` INT UNSIGNED NOT NULL,
    `userId` INT NOT NULL,
    `isSubscribed` TINYINT(1) NOT NULL DEFAULT 0,
    `isVisible` TINYINT(1) NOT NULL DEFAULT 0,
    `sortOrder` INT NOT NULL DEFAULT 0,
    `includeInAvailability` ENUM('all', 'attending', 'none') NOT NULL,
    PRIMARY KEY (`calenderId`, `userId`),
    CONSTRAINT `fk_calendar_calendar_user_calendar_calendar1`
    FOREIGN KEY (`calenderId`)
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