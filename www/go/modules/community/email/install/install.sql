-- -----------------------------------------------------
-- Table `email_account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_account` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(125) NOT NULL,
	`email` VARCHAR(45) NOT NULL,
	`quota` BIGINT NULL,
	`highestModSeq` BIGINT NOT NULL DEFAULT 1,
	`lowestModSeq` BIGINT NOT NULL DEFAULT 1,
	`mtaDsn` MEDIUMTEXT NOT NULL,
	`mdaDsn` MEDIUMTEXT NOT NULL,
	`mdaCapabilities` MEDIUMTEXT NOT NULL,
	`modifiedAt` DATETIME NULL,
	`createdAt` DATETIME NOT NULL,
	`aclId` INT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_email_account_core_acl1_idx` (`aclId` ASC),
	CONSTRAINT `fk_email_account_core_acl1`
		FOREIGN KEY (`aclId`)
			REFERENCES `core_acl` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_mailbox`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_mailbox` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`parentId` INT UNSIGNED NULL,
	`accountId` INT UNSIGNED NOT NULL,
	`name` VARCHAR(200) NOT NULL,
	`role` VARCHAR(60) NULL,
	`sortOrder` INT NOT NULL DEFAULT 0,
	`highestUID` BIGINT NULL,
	`uid` VARCHAR(45) NULL,
	`emailHighestModSeq` BIGINT NOT NULL DEFAULT 0,
	PRIMARY KEY (`id`),
	INDEX `fk_mailbox_mailbox1_idx` (`parentId` ASC),
	INDEX `fk_mailbox_account1_idx` (`accountId` ASC),
	CONSTRAINT `fk_mailbox_account1`
		FOREIGN KEY (`accountId`)
			REFERENCES `email_account` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_mailbox_mailbox1`
		FOREIGN KEY (`parentId`)
			REFERENCES `email_mailbox` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_thread`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_thread` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`subjectHash` CHAR(40) NOT NULL,
	`accountId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_thread_account1_idx` (`accountId` ASC),
	CONSTRAINT `fk_thread_account1`
		FOREIGN KEY (`accountId`)
			REFERENCES `email_account` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_email` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`uid` BIGINT(11) NULL DEFAULT NULL,
	`threadId` INT NOT NULL,
	`seen` TINYINT NOT NULL DEFAULT 0,
	`flagged` TINYINT NOT NULL DEFAULT 0,
	`answered` TINYINT NOT NULL DEFAULT 0,
	`keywords` VARCHAR(512) NOT NULL DEFAULT '{}',
	`size` INT NOT NULL DEFAULT 0,
	`receivedAt` DATETIME NULL DEFAULT NULL,
	`sentAt` DATETIME NULL,
	`subject` VARCHAR(255) CHARACTER SET 'utf8mb4' NOT NULL DEFAULT '',
	`hasAttachment` TINYINT(1) NOT NULL DEFAULT 0,
	`preview` VARCHAR(255) CHARACTER SET 'utf8mb4' NULL,
	`accountId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_message_thread1_idx` (`threadId` ASC),
	INDEX `fk_email_account1_idx` (`accountId` ASC),
	CONSTRAINT `fk_email_account1`
		FOREIGN KEY (`accountId`)
			REFERENCES `email_account` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_message_thread1`
		FOREIGN KEY (`threadId`)
			REFERENCES `email_thread` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_map`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_map` (
	`fk` INT UNSIGNED NOT NULL,
	`mailboxId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`fk`, `mailboxId`),
	INDEX `fk_message_has_mailbox_mailbox1_idx` (`mailboxId` ASC),
	INDEX `fk_message_has_mailbox_message1_idx` (`fk` ASC),
	CONSTRAINT `fk_message_has_mailbox_message1`
		FOREIGN KEY (`fk`)
			REFERENCES `email_email` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_message_has_mailbox_mailbox1`
		FOREIGN KEY (`mailboxId`)
			REFERENCES `email_mailbox` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_identity`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_identity` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`accountId` INT UNSIGNED NOT NULL,
	`name` VARCHAR(128) NOT NULL,
	`email` VARCHAR(255) NOT NULL,
	`replyTo` TEXT NULL,
	`bcc` TEXT NULL,
	`textSignature` MEDIUMTEXT NOT NULL,
	`htmlSignature` MEDIUMTEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_identity_account1_idx` (`accountId` ASC),
	CONSTRAINT `fk_identity_account1`
		FOREIGN KEY (`accountId`)
			REFERENCES `email_account` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_submission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_submission` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`identityId` INT UNSIGNED NOT NULL,
	`emailId` INT UNSIGNED NOT NULL,
	`threadId` INT NOT NULL,
	`sendAt` DATETIME NOT NULL,
	`undoStatus` ENUM('pending', 'final', 'canceled') NOT NULL,
	`deliveryStatus` TEXT NULL,
	`blobs` TINYINT(1) NOT NULL DEFAULT 0,
	`accountId` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_email_submission_identity1_idx` (`identityId` ASC),
	INDEX `fk_email_submission_account1_idx` (`accountId` ASC),
	INDEX `fk_email_submission_email_thread1_idx` (`threadId` ASC),
	INDEX `fk_email_submission_email1_idx` (`emailId` ASC),
	INDEX `fk_email_submission_send_at1_idx` (`sendAt` ASC),
	CONSTRAINT `fk_email_submission_identity1`
		FOREIGN KEY (`identityId`)
			REFERENCES `email_identity` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_email_submission_account1`
		FOREIGN KEY (`accountId`)
			REFERENCES `email_account` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_email_submission_email_thread1`
		FOREIGN KEY (`threadId`)
			REFERENCES `email_thread` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_email_submission_email1`
		FOREIGN KEY (`emailId`)
			REFERENCES `email_email` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_id`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_id` (
	`fk` INT UNSIGNED NOT NULL,
	`type` ENUM('references', 'inReplyTo', 'messageId') NOT NULL,
	`messageId` VARCHAR(255) NOT NULL,
	PRIMARY KEY (`fk`, `type`, `messageId`),
	INDEX `fk_email_id_map_email1_idx` (`fk` ASC),
	CONSTRAINT `fk_email_id_map_email1`
		FOREIGN KEY (`fk`)
			REFERENCES `email_email` (`id`)
			ON DELETE CASCADE
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_address` (
	`fk` INT UNSIGNED NOT NULL,
	`type` ENUM('sender', 'from', 'to', 'cc', 'bcc', 'replyTo') NOT NULL,
	`email` VARCHAR(128) NOT NULL,
	`name` VARCHAR(128) NULL,
	PRIMARY KEY (`fk`, `type`, `email`),
	CONSTRAINT `fk_email_address_email1`
		FOREIGN KEY (`fk`)
			REFERENCES `email_email` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email_mailbox_user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_mailbox_user` (
	`mailboxId` INT UNSIGNED NOT NULL,
	`userId` INT NOT NULL,
	`isSubscribed` TINYINT(1) NOT NULL,
	`modSeq` INT UNSIGNED NOT NULL DEFAULT 0,
	PRIMARY KEY (`mailboxId`, `userId`),
	INDEX `fk_email_mailbox_user_core_user1_idx` (`userId` ASC),
	CONSTRAINT `fk_email_mailbox_user_email_mailbox1`
		FOREIGN KEY (`mailboxId`)
			REFERENCES `email_mailbox` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION,
	CONSTRAINT `fk_email_mailbox_user_core_user1`
		FOREIGN KEY (`userId`)
			REFERENCES `core_user` (`id`)
			ON DELETE NO ACTION
			ON UPDATE NO ACTION)
	ENGINE = InnoDB;