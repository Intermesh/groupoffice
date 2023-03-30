CREATE TABLE IF NOT EXISTS `community_privacy_settings` (
       `id` INT UNSIGNED AUTO_INCREMENT NOT NULL,
       `warnXDaysBeforeDeletion` MEDIUMINT UNSIGNED DEFAULT NULL,
       `monitorAddressBooks` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
       `trashAddressBook` INT(11) DEFAULT NULL,
       `trashAfterXMonths` MEDIUMINT UNSIGNED DEFAULT NULL,
        PRIMARY KEY (`id`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `community_privacy_settings` ADD CONSTRAINT `community_privacy_settings_ibfk1` FOREIGN KEY (`trashAddressBook`)
    REFERENCES `addressbook_addressbook`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

CREATE TABLE IF NOT EXISTS `community_privacy_contact` (
    `contactId` INT(11) NOT NULL,
    `deleteAt`   DATE DEFAULT NULL,
    PRIMARY KEY (`contactId`)
)
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `community_privacy_contact` ADD CONSTRAINT `community_privacy_contact_ibfk1` FOREIGN KEY (`contactId`)
    REFERENCES `addressbook_contact`(`id`) ON DELETE CASCADE;

