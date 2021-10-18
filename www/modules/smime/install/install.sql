CREATE TABLE IF NOT EXISTS `smi_certs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cert` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB ;

DROP TABLE IF EXISTS `smi_pkcs12`;
CREATE TABLE IF NOT EXISTS `smi_pkcs12` (
  `account_id` int(11) NOT NULL,
  `cert` blob,
  `serial` VARCHAR(100) NOT NULL,
  `valid_until` DATETIME NOT NULL,
  `valid_since` DATETIME NOT NULL,
  `provided_by` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`account_id`),
  INDEX `fk_pks_cert_account_id_email_account_idx` (`account_id` ASC),
  CONSTRAINT `fk_account_id_to_email_account`
      FOREIGN KEY (`account_id`)
          REFERENCES `em_accounts` (`id`)
          ON DELETE CASCADE
          ON UPDATE NO ACTION
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `smi_account_settings` (
    `account_id` int(11) NOT NULL,
    `always_sign` tinyint(1) NOT NULL,
    PRIMARY KEY (`account_id`),
    CONSTRAINT `fk_smi_settings_account_id_to_email_account`
        FOREIGN KEY (`account_id`)
            REFERENCES `em_accounts` (`id`)
            ON DELETE CASCADE
            ON UPDATE NO ACTION
) ENGINE=InnoDB;