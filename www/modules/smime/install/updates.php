<?php
$updates["201202020834"][]="ALTER TABLE `smi_pkcs12` CHANGE `cert` `cert` MEDIUMBLOB NULL";
$updates["201203160943"][]="ALTER TABLE `smi_certs` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT";


$updates['201610281650'][] = 'ALTER TABLE `smi_certs` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `smi_certs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `smi_pkcs12` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `smi_pkcs12` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['202107201635'][] = 'CREATE TABLE `smi_account_settings` (
	`account_id` int(11) NOT NULL,
   `always_sign` tinyint(1) NOT NULL,
    PRIMARY KEY (`account_id`)
) ENGINE=InnoDB;';

$updates['202107201635'][] = 'INSERT INTO `smi_account_settings` (account_id, always_sign) SELECT account_id, always_sign FROM smi_pkcs12;';
$updates['202107201635'][] = 'ALTER TABLE `smi_pkcs12` DROP COLUMN always_sign;';

$updates['202107201635'][] = 'ALTER TABLE `smi_pkcs12` 
ADD COLUMN `id` INT UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`);';

$updates['202107211515'][] = 'ALTER TABLE `smi_pkcs12` 
ADD COLUMN `serial` VARCHAR(100) NOT NULL DEFAULT "000-000" AFTER `cert`,
ADD COLUMN `valid_until` DATETIME NOT NULL DEFAULT "2000-01-01 11:11:11" AFTER `serial`,
ADD COLUMN `valid_since` DATETIME NOT NULL DEFAULT "2000-01-01 00:00:00" AFTER `valid_until`,
ADD COLUMN `provided_by` VARCHAR(128) NOT NULL DEFAULT "Unknown" AFTER `valid_since`;';

$updates['202107211515'][] = 'DELETE FROM `smi_account_settings` WHERE account_id NOT IN (SELECT id FROM em_accounts);';
$updates['202107211515'][] = 'ALTER TABLE `smi_account_settings` 
ADD CONSTRAINT `fk_account_id_to_email_account`
  FOREIGN KEY (`account_id`)
  REFERENCES `em_accounts` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;';

$updates['202107211515'][] = 'DELETE FROM `smi_pkcs12` WHERE account_id NOT IN (SELECT id FROM em_accounts);';
$updates['202107211515'][] = 'ALTER TABLE `smi_pkcs12` ADD INDEX `fk_pks_cert_account_id_email_account_idx` (`account_id` ASC);';
$updates['202107211515'][] = 'ALTER TABLE `smi_pkcs12` 
ADD CONSTRAINT `fk_pks_cert_account_id_email_account`
  FOREIGN KEY (`account_id`)
  REFERENCES `em_accounts` (`id`)
  ON DELETE CASCADE
  ON UPDATE NO ACTION;';
