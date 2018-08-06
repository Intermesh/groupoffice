DROP TABLE IF EXISTS `addressbook_address`;
CREATE TABLE IF NOT EXISTS `addressbook_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `zipCode` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contactId` (`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_contact`;
CREATE TABLE IF NOT EXISTS `addressbook_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `userId` int(11) DEFAULT NULL COMMENT 'Set to user ID if this contact is a profile for that user',
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
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
  `debtorNumber` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `organizationContactId` int(11) DEFAULT NULL,
  `photoBlobId` char(40) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`) USING BTREE,
  KEY `deleted` (`deleted`),
  KEY `companyContactId` (`organizationContactId`),
  KEY `owner` (`createdBy`),
  KEY `photoBlobId` (`photoBlobId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `addressbook_contact_organization`;
CREATE TABLE IF NOT EXISTS `addressbook_contact_organization` (
  `contactId` int(11) NOT NULL,
  `organizationContactId` int(11) NOT NULL,
  PRIMARY KEY (`contactId`,`organizationContactId`),
  KEY `organizationContactId` (`organizationContactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_custom_fields`;
CREATE TABLE IF NOT EXISTS `addressbook_custom_fields` (
  `id` int(11) NOT NULL,
  `textfield1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `number1` double DEFAULT NULL,
  `select` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'test',
  `select1` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Option 1',
  `textfield2` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `textfield3` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `textfield 1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `t1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `t12` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `t5` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_date`;
CREATE TABLE IF NOT EXISTS `addressbook_date` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'birthday',
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contactId` (`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_email_address`;
CREATE TABLE IF NOT EXISTS `addressbook_email_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_phone`;
CREATE TABLE IF NOT EXISTS `addressbook_phone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `contactId` (`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `addressbook_url`;
CREATE TABLE IF NOT EXISTS `addressbook_url` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contactId` int(11) NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`contactId`),
  KEY `contactId` (`contactId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;
