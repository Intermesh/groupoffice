CREATE TABLE `addressbook_address` (
                                       `contactId` int(11) NOT NULL,
                                       `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `street` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `street2` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `zipCode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `state` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `country` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `countryCode` char(2) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL COMMENT 'ISO_3166 Alpha 2 code',
                                       `latitude` decimal(10,8) DEFAULT NULL,
                                       `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_addressbook` (
                                           `id` int(11) NOT NULL,
                                           `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
                                           `aclId` int(11) NOT NULL,
                                           `createdBy` int(11) NOT NULL,
                                           `filesFolderId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact` (
                                       `id` int(11) NOT NULL,
                                       `addressBookId` int(11) NOT NULL,
                                       `createdBy` int(11) NOT NULL,
                                       `createdAt` datetime NOT NULL,
                                       `modifiedAt` datetime NOT NULL,
                                       `modifiedBy` int(11) DEFAULT NULL,
                                       `goUserId` int(11) DEFAULT NULL,
                                       `prefixes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prefixes like ''Sir''',
                                       `firstName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `middleName` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `lastName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `suffixes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Suffixes like ''Msc.''',
                                       `gender` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M for Male, F for Female or null for unknown',
                                       `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `isOrganization` tinyint(1) NOT NULL DEFAULT 0,
                                       `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'name field for companies and contacts. It should be the display name of first, middle and last name',
                                       `IBAN` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `registrationNumber` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Company trade registration number',
                                       `vatNo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `vatReverseCharge` tinyint(1) NOT NULL DEFAULT 0,
                                       `debtorNumber` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `photoBlobId` binary(40) DEFAULT NULL,
                                       `language` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `jobTitle` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                       `filesFolderId` int(11) DEFAULT NULL,
                                       `uid` varchar(200) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
                                       `vcardBlobId` binary(40) DEFAULT NULL,
                                       `uri` varchar(200) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact_custom_fields` (
    `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact_group` (
                                             `contactId` int(11) NOT NULL,
                                             `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_contact_star` (
                                            `contactId` int(11) NOT NULL,
                                            `userId` int(11) NOT NULL,
                                            `modSeq` int(11) NOT NULL DEFAULT 0,
                                            `starred` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_date` (
                                    `contactId` int(11) NOT NULL,
                                    `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'birthday',
                                    `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_email_address` (
                                             `contactId` int(11) NOT NULL,
                                             `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                             `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_group` (
                                     `id` int(11) NOT NULL,
                                     `addressBookId` int(11) NOT NULL,
                                     `name` varchar(190) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_phone_number` (
                                            `contactId` int(11) NOT NULL,
                                            `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                                            `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_url` (
                                   `contactId` int(11) NOT NULL,
                                   `type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
                                   `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_filter_contact_map`(
                                `id` int(11) NOT NULL,
                                `addressBookId` INT(11) DEFAULT NULL,
                                `contactId` INT(11) DEFAULT NULL
)ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = COMPACT;

ALTER TABLE `addressbook_address`
    ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_addressbook`
    ADD PRIMARY KEY (`id`),
  ADD KEY `acid` (`aclId`),
  ADD KEY `createdBy` (`createdBy`);

ALTER TABLE `addressbook_contact`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `goUserId` (`goUserId`),
    ADD KEY `owner` (`createdBy`),
    ADD KEY `photoBlobId` (`photoBlobId`),
    ADD KEY `addressBookId` (`addressBookId`),
    ADD KEY `modifiedBy` (`modifiedBy`),
    ADD KEY `vcardBlobId` (`vcardBlobId`);

ALTER TABLE `addressbook_contact_custom_fields`
    ADD PRIMARY KEY (`id`);

ALTER TABLE `addressbook_contact_group`
    ADD PRIMARY KEY (`contactId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

ALTER TABLE `addressbook_contact_star`
    ADD PRIMARY KEY (`contactId`,`userId`),
  ADD KEY `addressbook_contact_star_ibfk_2` (`userId`);

ALTER TABLE `addressbook_date`
    ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_email_address`
    ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_group`
    ADD PRIMARY KEY (`id`),
  ADD KEY `addressBookId` (`addressBookId`);

ALTER TABLE `addressbook_phone_number`
    ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_url`
    ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_filter_contact_map`
    ADD PRIMARY KEY (`id`),
    ADD KEY 'addressBookId' ('addressBookId')
    ADD KEY 'contactId' ('contactId');

ALTER TABLE `addressbook_addressbook`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_contact`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_group`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_filter_contact_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_address`
    ADD CONSTRAINT `addressbook_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_addressbook`
    ADD CONSTRAINT `addressbook_addressbook_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

ALTER TABLE `addressbook_contact`
    ADD CONSTRAINT `addressbook_contact_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_4` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_5` FOREIGN KEY (`goUserId`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_ibfk_6` FOREIGN KEY (`vcardBlobId`) REFERENCES `core_blob` (`id`);

ALTER TABLE `addressbook_contact_custom_fields`
    ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_contact_group`
    ADD CONSTRAINT `addressbook_contact_group_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `addressbook_group` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_contact_star`
    ADD CONSTRAINT `addressbook_contact_star_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_star_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_date`
    ADD CONSTRAINT `addressbook_date_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_email_address`
    ADD CONSTRAINT `addressbook_email_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_group`
    ADD CONSTRAINT `addressbook_group_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`);

ALTER TABLE `addressbook_phone_number`
    ADD CONSTRAINT `addressbook_phone_number_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_url`
    ADD CONSTRAINT `addressbook_url_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_filter_contact_map`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_filter_contact_map`
    ADD CONSTRAINT `addressBookId` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`),
    ADD CONSTRAINT `contactId` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`);