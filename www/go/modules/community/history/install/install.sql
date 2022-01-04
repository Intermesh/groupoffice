CREATE TABLE IF NOT EXISTS `history_log_entry` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` INT NULL,
  `description` VARCHAR(384),
  `changes` TEXT NULL,
  `createdAt` DATETIME NULL,
  `createdBy` INT NULL,
  `aclId` INT NULL,
  `removeAcl` TINYINT(1) NOT NULL DEFAULT 0,
  `entityTypeId` INT NOT NULL,
  `entityId` INT DEFAULT NULL,
  `remoteIp` varchar(50) null,
  PRIMARY KEY (`id`),
  INDEX `fk_log_entry_core_user_idx` (`createdBy` ASC),
  INDEX `fk_log_entry_core_acl1_idx` (`aclId` ASC),
  INDEX `fk_log_entry_core_entity1_idx` (`entityTypeId` ASC),
  CONSTRAINT `fk_log_entry_core_user`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_acl1`
    FOREIGN KEY (`aclId`)
    REFERENCES `core_acl` (`id`)
    ON DELETE SET NULL
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_entity1`
    FOREIGN KEY (`entityTypeId`)
    REFERENCES `core_entity` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

ALTER TABLE `history_log_entry` ADD INDEX(`entityId`);
create index history_log_entry_createdAt_index
    on history_log_entry (createdAt);