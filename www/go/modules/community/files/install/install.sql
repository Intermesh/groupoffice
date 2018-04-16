-- -----------------------------------------------------
-- Table `files_storage`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files_storage` ;

CREATE TABLE IF NOT EXISTS `files_storage` (
  `id` INT NOT NULL COMMENT 'Only id\'s of files_node\'s that are \"Folders\"(blobId is NULL)',
  `aclId` INT NOT NULL,
  `quota` INT NOT NULL DEFAULT 0,
  `usage` INT NOT NULL DEFAULT 0,
  `ownedBy` INT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_files_storage_files_node1`
    FOREIGN KEY (`id`)
    REFERENCES `files_node` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_storage_core_acl1`
    FOREIGN KEY (`aclId`)
    REFERENCES `core_acl` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_files_storage_files_node1_idx` ON `files_storage` (`id` ASC);

CREATE INDEX `fk_files_storage_core_acl1_idx` ON `files_storage` (`aclId` ASC);


-- -----------------------------------------------------
-- Table `files_node`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files_node` ;

CREATE TABLE IF NOT EXISTS `files_node` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `storageId` INT NOT NULL,
  `blobId` BINARY(40) NULL COMMENT 'When blobId is NULL then deletedAt OR isDirectory need to be set\n',
  `parentId` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `createdAt` DATETIME NOT NULL,
  `modifiedAt` DATETIME NOT NULL,
  `ownedBy` INT(11) NULL,
  `modifiedBy` INT(11) NULL,
  `comment` TEXT NULL,
  `deletedAt` DATETIME NULL,
  `deletedBy` INT(11) NULL,
  `isDirectory` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_files_node_core_blob1`
    FOREIGN KEY (`blobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_node_files_node1`
    FOREIGN KEY (`parentId`)
    REFERENCES `files_node` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_node_files_storage1`
    FOREIGN KEY (`storageId`)
    REFERENCES `files_storage` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_files_node_core_blob1_idx` ON `files_node` (`blobId` ASC);

CREATE INDEX `fk_files_node_files_node1_idx` ON `files_node` (`parentId` ASC);

CREATE UNIQUE INDEX `parentId_UNIQUE` ON `files_node` (`parentId` ASC, `name` ASC);

CREATE INDEX `fk_files_node_files_storage1_idx` ON `files_node` (`storageId` ASC);


-- -----------------------------------------------------
-- Table `files_node_user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files_node_user` ;

CREATE TABLE IF NOT EXISTS `files_node_user` (
  `nodeId` INT NOT NULL,
  `userId` INT(11) NOT NULL,
  `bookmarked` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
  `touchedAt` DATETIME NULL COMMENT 'Determine the recent touched files',
  PRIMARY KEY (`nodeId`, `userId`),
  CONSTRAINT `fk_files_node_has_core_user_files_node1`
    FOREIGN KEY (`nodeId`)
    REFERENCES `files_node` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_node_has_core_user_core_user1`
    FOREIGN KEY (`userId`)
    REFERENCES `core_user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_files_node_has_core_user_core_user1_idx` ON `files_node_user` (`userId` ASC);

CREATE INDEX `fk_files_node_has_core_user_files_node1_idx` ON `files_node_user` (`nodeId` ASC);


-- -----------------------------------------------------
-- Table `files_version`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files_version` ;

CREATE TABLE IF NOT EXISTS `files_version` (
  `id` INT NOT NULL,
  `blobId` BINARY(40) NOT NULL,
  `createdAt` DATETIME NOT NULL,
  `createdBy` INT NULL,
  PRIMARY KEY (`id`, `blobId`),
  CONSTRAINT `fk_files_version_files_node1`
    FOREIGN KEY (`id`)
    REFERENCES `files_node` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_files_version_core_blob1`
    FOREIGN KEY (`blobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE INDEX `fk_files_version_core_blob1_idx` ON `files_version` (`blobId` ASC);


-- -----------------------------------------------------
-- Table `files_share`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `files_share` ;

CREATE TABLE IF NOT EXISTS `files_share` (
  `id` INT NOT NULL,
  `token` VARCHAR(45) NOT NULL,
  `tokenPassword` VARCHAR(45) NULL,
  `tokenExpiresAt` DATETIME NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_files_share_files_node1`
    FOREIGN KEY (`id`)
    REFERENCES `files_node` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;