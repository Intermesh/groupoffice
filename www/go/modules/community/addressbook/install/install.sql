
CREATE TABLE `addressbook_address` (
  `id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `street2` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zipCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `countryCode` char(2) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL COMMENT 'ISO_3166 Alpha 2 code'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_addressbook` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact` (
  `id` int(11) NOT NULL,
  `addressBookId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `goUserId` int(11) DEFAULT NULL,
  `prefixes` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Prefixes like ''Sir''',
  `firstName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `middleName` varchar(55) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `lastName` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffixes` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Suffixes like ''Msc.''',
  `gender` enum('M','F') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'M for Male, F for Female or null for unknown',
  `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isOrganization` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'name field for companies and contacts. It should be the display name of first, middle and last name',
  `IBAN` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `registrationNumber` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Company trade registration number',
  `vatNo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vatReverseCharge` tinyint(1) NOT NULL DEFAULT 0,
  `debtorNumber` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photoBlobId` binary(40) DEFAULT NULL,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jobTitle` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_contact_group` (
  `contactId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `addressbook_contact_star` (
  `contactId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL DEFAULT 0,
  `starred` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `addressbook_date` (
  `id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'birthday',
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_email_address` (
  `id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `addressbook_group` (
  `id` int(11) NOT NULL,
  `addressBookId` int(11) NOT NULL,
  `name` varchar(190) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_phone_number` (
  `id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

CREATE TABLE `addressbook_smart_addressbook` (
  `id` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `ownedBy` int(11) NOT NULL,
  `matchAny` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'true to show contact matching any of the conditions instead of all.'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `addressbook_smart_addressbook_filter` (
  `id` int(11) NOT NULL,
  `smartAddressBookId` int(11) NOT NULL,
  `property` varchar(190) NOT NULL,
  `operator` varchar(50) NOT NULL,
  `value` varchar(190) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `addressbook_url` (
  `id` int(11) NOT NULL,
  `contactId` int(11) NOT NULL,
  `type` varchar(190) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `addressbook_address`
  ADD PRIMARY KEY (`id`),
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
  ADD KEY `addressBookId` (`addressBookId`);

ALTER TABLE `addressbook_contact_custom_fields`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `addressbook_contact_group`
  ADD PRIMARY KEY (`contactId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

ALTER TABLE `addressbook_contact_star`
  ADD PRIMARY KEY (`contactId`,`userId`),
  ADD KEY `userId` (`userId`);

ALTER TABLE `addressbook_date`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_email_address`
  ADD PRIMARY KEY (`id`,`contactId`),
  ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressBookId` (`addressBookId`);

ALTER TABLE `addressbook_phone_number`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contactId` (`contactId`);

ALTER TABLE `addressbook_smart_addressbook`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `addressbook_smart_addressbook_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `smartAddressBookId` (`smartAddressBookId`);

ALTER TABLE `addressbook_url`
  ADD PRIMARY KEY (`id`,`contactId`),
  ADD KEY `contactId` (`contactId`);


ALTER TABLE `addressbook_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_addressbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_date`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_email_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_phone_number`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_smart_addressbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_smart_addressbook_filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `addressbook_url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `addressbook_address`
  ADD CONSTRAINT `addressbook_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_addressbook`
  ADD CONSTRAINT `addressbook_addressbook_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

ALTER TABLE `addressbook_contact`
  ADD CONSTRAINT `addressbook_contact_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`);

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

ALTER TABLE `addressbook_smart_addressbook_filter`
  ADD CONSTRAINT `addressbook_smart_addressbook_filter_ibfk_1` FOREIGN KEY (`smartAddressBookId`) REFERENCES `addressbook_smart_addressbook` (`id`) ON DELETE CASCADE;

ALTER TABLE `addressbook_url`
  ADD CONSTRAINT `addressbook_url_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;
