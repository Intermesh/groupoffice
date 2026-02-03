CREATE TABLE IF NOT EXISTS `davclient_davaccount` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(145) NOT NULL,
	`host` VARCHAR(245) NULL,
	`username` VARCHAR(60) NULL,
	`password` VARCHAR(255) NULL,
	`basePath` VARCHAR(100) NOT NULL DEFAULT '/',
	`principalUri` VARCHAR(512) NOT NULL DEFAULT '',
	`refreshInterval` INT NOT NULL,
	`lastSync` DATETIME NULL,
	`lastError` TEXT NULL,
	`active` TINYINT(1) DEFAULT 1 NOT NULL,
	`aclId` INT NOT NULL,
    verifySSL bool default true not null,
	PRIMARY KEY (`id`),
	INDEX `fk_davclient_davaccount_core_acl_idx` (`aclId` ASC),
	CONSTRAINT `fk_davclient_davaccount_core_acl`
		FOREIGN KEY (`aclId`)
		REFERENCES `core_acl` (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `davclient_calendar` (
	`id` INT UNSIGNED NOT NULL,
	`davaccountId` INT UNSIGNED NOT NULL,
	`uri` VARCHAR(255) NOT NULL,
	`ctag` VARCHAR(100) NOT NULL,
	`synctoken` VARCHAR(100) NOT NULL DEFAULT '',
	`lastSync` DATETIME NULL,
	`lastError` TEXT NULL,
	PRIMARY KEY (`id`,`davaccountId`),
	CONSTRAINT `fk_davclient_calendar_davaccount`
		FOREIGN KEY (`davaccountId`) REFERENCES `davclient_davaccount` (`id`)
		ON DELETE CASCADE,
	CONSTRAINT `fk_davclient_calendar_calendarl`
		FOREIGN KEY (`id`) REFERENCES `calendar_calendar` (`id`)
		ON DELETE RESTRICT
) ENGINE = InnoDB;