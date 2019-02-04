-- -----------------------------------------------------
-- Table `comments_thread`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_thread` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `entityId` INT NOT NULL,
  `entityTypeId` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_thread_core_entity1_idx` (`entityTypeId` ASC),
  CONSTRAINT `fk_comments_thread_core_entity1`
    FOREIGN KEY (`entityTypeId`)
    REFERENCES `core_entity` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments_comment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_comment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `threadId` INT NOT NULL,
  `text` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `createdAt` DATETIME NOT NULL,
  `createdBy` INT NOT NULL,
  `modifiedBy` INT NULL,
  `modifiedAt` DATETIME NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_comments_comment_comments_thread_idx` (`threadId` ASC),
  INDEX `fk_comments_comment_core_user1_idx` (`createdBy` ASC),
  INDEX `fk_comments_comment_core_user2_idx` (`modifiedBy` ASC),
  CONSTRAINT `fk_comments_comment_comments_thread`
    FOREIGN KEY (`threadId`)
    REFERENCES `comments_thread` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_comment_core_user1`
    FOREIGN KEY (`createdBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_comment_core_user2`
    FOREIGN KEY (`modifiedBy`)
    REFERENCES `core_user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments_attachment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_attachment` (
  `commentId` INT NOT NULL,
  `blobId` BINARY(40) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`commentId`, `blobId`),
  INDEX `fk_comments_attachment_comments_comment1_idx` (`commentId` ASC),
  INDEX `fk_comments_attachment_core_blob1_idx` (`blobId` ASC),
  CONSTRAINT `fk_comments_attachment_comments_comment1`
    FOREIGN KEY (`commentId`)
    REFERENCES `comments_comment` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_attachment_core_blob1`
    FOREIGN KEY (`blobId`)
    REFERENCES `core_blob` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments_label`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_label` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(127) NOT NULL DEFAULT '',
  `color` CHAR(6) NOT NULL DEFAULT '243a80',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `comments_comment_label`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `comments_comment_label` (
  `labelId` INT NOT NULL,
  `commentId` INT NOT NULL,
  PRIMARY KEY (`labelId`, `commentId`),
  INDEX `fk_comments_label_has_comments_comment_comments_comment1_idx` (`commentId` ASC),
  INDEX `fk_comments_label_has_comments_comment_comments_label1_idx` (`labelId` ASC),
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_label1`
    FOREIGN KEY (`labelId`)
    REFERENCES `comments_label` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_comment1`
    FOREIGN KEY (`commentId`)
    REFERENCES `comments_comment` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;