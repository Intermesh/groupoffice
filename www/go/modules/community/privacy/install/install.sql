CREATE TABLE IF NOT EXISTS `community_privacy_settings` (
       `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
       `warnXDaysBeforeDeletion` MEDIUMINT UNSIGNED DEFAULT NULL,
       `monitorAddressBooks` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
       `trashAddressBook` INT(11) UNSIGNED DEFAULT NULL,
       `trashAfterXMonths` MEDIUMINT UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;