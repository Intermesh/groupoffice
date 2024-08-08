CREATE TABLE IF NOT EXISTS `davclient_davaccount` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(145) NOT NULL,
	`connectionDetails` VARCHAR(245) NULL,
	`username` VARCHAR(60) NULL,
	`password` VARCHAR(60) NULL,
	`capabilities` VARCHAR(45) NULL,
	`aclId` INT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `fk_davclient_davaccount_core_acl_idx` (`aclId` ASC),
	CONSTRAINT `fk_davclient_davaccount_core_acl`
		FOREIGN KEY (`aclId`)
			REFERENCES `core_acl` (`id`))
	ENGINE = InnoDB;
