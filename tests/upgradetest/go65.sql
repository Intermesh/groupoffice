-- MariaDB dump 10.19  Distrib 10.6.5-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: groupoffice_phpunit
-- ------------------------------------------------------
-- Server version	10.6.5-MariaDB-1:10.6.5+maria~focal

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `addressbook_address`
--

DROP TABLE IF EXISTS `addressbook_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `longitude` decimal(11,8) DEFAULT NULL,
  KEY `contactId` (`contactId`),
  CONSTRAINT `addressbook_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_address`
--

LOCK TABLES `addressbook_address` WRITE;
/*!40000 ALTER TABLE `addressbook_address` DISABLE KEYS */;
INSERT INTO `addressbook_address` VALUES (2,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(3,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(4,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(5,'postal','Street','1','5222 AE','Den Bosch','','Netherlands','NL',NULL,NULL),(7,NULL,'Street','1','5222 AE','Den Bosch','','Germany','DE',NULL,NULL),(8,NULL,'Street','1','5222 AE','Den Bosch','','Netherlands','NL',NULL,NULL);
/*!40000 ALTER TABLE `addressbook_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_addressbook`
--

DROP TABLE IF EXISTS `addressbook_addressbook`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_addressbook` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `salutationTemplate` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `createdBy` (`createdBy`),
  CONSTRAINT `addressbook_addressbook_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `addressbook_addressbook_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_addressbook`
--

LOCK TABLES `addressbook_addressbook` WRITE;
/*!40000 ALTER TABLE `addressbook_addressbook` DISABLE KEYS */;
INSERT INTO `addressbook_addressbook` VALUES (1,'Shared',12,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(2,'Customers',83,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(3,'Test',110,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(4,'Users',111,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}');
/*!40000 ALTER TABLE `addressbook_addressbook` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_contact`
--

DROP TABLE IF EXISTS `addressbook_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addressBookId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `goUserId` int(11) DEFAULT NULL,
  `prefixes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prefixes like ''Sir''',
  `initials` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `firstName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middleName` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastName` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `suffixes` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Suffixes like ''Msc.''',
  `salutation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `department` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `uid` varchar(512) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `vcardBlobId` binary(40) DEFAULT NULL,
  `uri` varchar(512) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `color` char(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `goUserId` (`goUserId`),
  KEY `owner` (`createdBy`),
  KEY `photoBlobId` (`photoBlobId`),
  KEY `addressBookId` (`addressBookId`),
  KEY `modifiedBy` (`modifiedBy`),
  KEY `vcardBlobId` (`vcardBlobId`),
  KEY `isOrganization` (`isOrganization`),
  KEY `name` (`name`),
  KEY `modifiedAt` (`modifiedAt`),
  KEY `lastName` (`lastName`),
  KEY `isOrganization_2` (`isOrganization`),
  KEY `addressbook_contact_addressBookId_lastName_index` (`addressBookId`,`lastName`),
  KEY `addressbook_contact_addressBookId_name_index` (`addressBookId`,`name`),
  KEY `addressbook_contact_isOrganization_index` (`isOrganization`),
  CONSTRAINT `addressbook_contact_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`),
  CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `addressbook_contact_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addressbook_contact_ibfk_4` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addressbook_contact_ibfk_5` FOREIGN KEY (`goUserId`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addressbook_contact_ibfk_6` FOREIGN KEY (`vcardBlobId`) REFERENCES `core_blob` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact`
--

LOCK TABLES `addressbook_contact` WRITE;
/*!40000 ALTER TABLE `addressbook_contact` DISABLE KEYS */;
INSERT INTO `addressbook_contact` VALUES (2,2,1,'2022-01-03 10:26:44','2022-01-03 10:26:44',1,3,'','','John','','Smith','',NULL,NULL,'Just a demo john',0,'John Smith','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'a2b13489e9762bf7d7dfd63d72d45f0f47411c30',NULL,'CEO',NULL,NULL,'2@',NULL,'2@.vcf',NULL),(3,2,1,'2022-01-03 10:26:44','2022-01-03 10:26:51',1,NULL,NULL,'',NULL,NULL,'ACME Corporation',NULL,NULL,NULL,'Just a demo acme',1,'ACME Corporation','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',1,NULL,NULL,NULL,NULL,NULL,NULL,'3@',NULL,'3@.vcf',NULL),(4,2,1,'2022-01-03 10:26:45','2022-01-03 10:26:45',1,NULL,'','','Wile','E.','Coyote','',NULL,NULL,'Just a demo wile',0,'Wile E. Coyote','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'0ec2f1f4f9fb41e8013fcc834991be30a8260750',NULL,'CEO',NULL,NULL,'4@',NULL,'4@.vcf',NULL),(5,3,1,'2022-01-03 10:27:00','2022-01-03 10:27:00',1,NULL,'','','John','','Doe','',NULL,NULL,NULL,0,'John Doe','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'5@',NULL,'5@.vcf',NULL),(6,3,1,'2022-01-03 10:27:00','2022-01-03 10:27:00',1,NULL,'','','Linda','','Smith','',NULL,NULL,NULL,0,'Linda Smith','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'6@',NULL,'6@.vcf',NULL),(7,3,1,'2022-01-03 10:27:06','2022-01-03 10:27:06',1,NULL,'','','John','','Doe','',NULL,NULL,NULL,0,'John Doe','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'7@',NULL,'7@.vcf',NULL),(8,3,1,'2022-01-03 10:27:06','2022-01-03 10:27:06',1,NULL,'','','John','','Doe','',NULL,NULL,NULL,0,'John Doe','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'8@',NULL,'8@.vcf',NULL);
/*!40000 ALTER TABLE `addressbook_contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_contact_custom_fields`
--

DROP TABLE IF EXISTS `addressbook_contact_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_contact_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `addressbook_contact_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact_custom_fields`
--

LOCK TABLES `addressbook_contact_custom_fields` WRITE;
/*!40000 ALTER TABLE `addressbook_contact_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_contact_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_contact_group`
--

DROP TABLE IF EXISTS `addressbook_contact_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_contact_group` (
  `contactId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`contactId`,`groupId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `addressbook_contact_group_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addressbook_contact_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `addressbook_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact_group`
--

LOCK TABLES `addressbook_contact_group` WRITE;
/*!40000 ALTER TABLE `addressbook_contact_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_contact_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_contact_star`
--

DROP TABLE IF EXISTS `addressbook_contact_star`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_contact_star` (
  `contactId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL DEFAULT 0,
  `starred` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`contactId`,`userId`),
  KEY `addressbook_contact_star_ibfk_2` (`userId`),
  CONSTRAINT `addressbook_contact_star_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addressbook_contact_star_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact_star`
--

LOCK TABLES `addressbook_contact_star` WRITE;
/*!40000 ALTER TABLE `addressbook_contact_star` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_contact_star` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_date`
--

DROP TABLE IF EXISTS `addressbook_date`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_date` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'birthday',
  `date` date NOT NULL,
  KEY `contactId` (`contactId`),
  CONSTRAINT `addressbook_date_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_date`
--

LOCK TABLES `addressbook_date` WRITE;
/*!40000 ALTER TABLE `addressbook_date` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_date` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_email_address`
--

DROP TABLE IF EXISTS `addressbook_email_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_email_address` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  KEY `contactId` (`contactId`),
  CONSTRAINT `addressbook_email_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_email_address`
--

LOCK TABLES `addressbook_email_address` WRITE;
/*!40000 ALTER TABLE `addressbook_email_address` DISABLE KEYS */;
INSERT INTO `addressbook_email_address` VALUES (2,'work','john@smith.demo'),(3,'work','info@acme.demo'),(4,'work','wile@smith.demo'),(7,'work','john@doe.test'),(8,'home','john@doe.test');
/*!40000 ALTER TABLE `addressbook_email_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_group`
--

DROP TABLE IF EXISTS `addressbook_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addressBookId` int(11) NOT NULL,
  `name` varchar(190) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `addressBookId` (`addressBookId`),
  CONSTRAINT `addressbook_group_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_group`
--

LOCK TABLES `addressbook_group` WRITE;
/*!40000 ALTER TABLE `addressbook_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_phone_number`
--

DROP TABLE IF EXISTS `addressbook_phone_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_phone_number` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  KEY `contactId` (`contactId`),
  CONSTRAINT `addressbook_phone_number_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_phone_number`
--

LOCK TABLES `addressbook_phone_number` WRITE;
/*!40000 ALTER TABLE `addressbook_phone_number` DISABLE KEYS */;
INSERT INTO `addressbook_phone_number` VALUES (2,'work','+31 (0) 10 - 1234567'),(2,'mobile','+31 (0) 6 - 1234567'),(3,'work','+31 (0) 10 - 1234567'),(3,'mobile','+31 (0) 6 - 1234567'),(4,'work','+31 (0) 10 - 1234567'),(4,'mobile','+31 (0) 6 - 1234567');
/*!40000 ALTER TABLE `addressbook_phone_number` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_portlet_birthday`
--

DROP TABLE IF EXISTS `addressbook_portlet_birthday`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_portlet_birthday` (
  `userId` int(11) NOT NULL,
  `addressBookId` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`addressBookId`),
  KEY `addressbook_portlet_birthday_fk2` (`addressBookId`),
  CONSTRAINT `addressbook_portlet_birthday_fk1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addressbook_portlet_birthday_fk2` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_portlet_birthday`
--

LOCK TABLES `addressbook_portlet_birthday` WRITE;
/*!40000 ALTER TABLE `addressbook_portlet_birthday` DISABLE KEYS */;
/*!40000 ALTER TABLE `addressbook_portlet_birthday` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_url`
--

DROP TABLE IF EXISTS `addressbook_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_url` (
  `contactId` int(11) NOT NULL,
  `type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  KEY `contactId` (`contactId`),
  CONSTRAINT `addressbook_url_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_url`
--

LOCK TABLES `addressbook_url` WRITE;
/*!40000 ALTER TABLE `addressbook_url` DISABLE KEYS */;
INSERT INTO `addressbook_url` VALUES (2,'homepage','http://www.smith.demo'),(3,'homepage','http://www.acme.demo'),(4,'homepage','http://www.smith.demo');
/*!40000 ALTER TABLE `addressbook_url` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `addressbook_user_settings`
--

DROP TABLE IF EXISTS `addressbook_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `addressbook_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultAddressBookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `defaultAddressBookId` (`defaultAddressBookId`),
  CONSTRAINT `addressbook_user_settings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addressbook_user_settings_ibfk_2` FOREIGN KEY (`defaultAddressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_user_settings`
--

LOCK TABLES `addressbook_user_settings` WRITE;
/*!40000 ALTER TABLE `addressbook_user_settings` DISABLE KEYS */;
INSERT INTO `addressbook_user_settings` VALUES (2,NULL),(3,NULL),(4,NULL);
/*!40000 ALTER TABLE `addressbook_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks_bookmark`
--

DROP TABLE IF EXISTS `bookmarks_bookmark`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks_bookmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` binary(40) DEFAULT NULL,
  `openExtern` tinyint(1) NOT NULL DEFAULT 1,
  `behaveAsModule` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `createdBy` (`createdBy`),
  KEY `categoryId` (`categoryId`),
  KEY `core_blob_bookmark_logo_idx` (`logo`),
  CONSTRAINT `bookmarks_bookmark_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookmarks_bookmark_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `bookmarks_category` (`id`),
  CONSTRAINT `core_blob_bookmark_logo` FOREIGN KEY (`logo`) REFERENCES `core_blob` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks_bookmark`
--

LOCK TABLES `bookmarks_bookmark` WRITE;
/*!40000 ALTER TABLE `bookmarks_bookmark` DISABLE KEYS */;
INSERT INTO `bookmarks_bookmark` VALUES (1,1,1,'Group-Office','https://www.group-office.com',NULL,'a277a250ad9fa623fd0c1c9bdbfb5804981d14e4',1,0),(2,1,1,'Intermesh','https://www.intermesh.nl',NULL,'b82d0979d555bd137b33c15021129e06cbeea59a',1,0);
/*!40000 ALTER TABLE `bookmarks_bookmark` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookmarks_category`
--

DROP TABLE IF EXISTS `bookmarks_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookmarks_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdBy` int(11) DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `createdBy` (`createdBy`),
  CONSTRAINT `bookmarks_category_acl_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `bookmarks_category_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks_category`
--

LOCK TABLES `bookmarks_category` WRITE;
/*!40000 ALTER TABLE `bookmarks_category` DISABLE KEYS */;
INSERT INTO `bookmarks_category` VALUES (1,1,109,'General');
/*!40000 ALTER TABLE `bookmarks_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_batchjob_orders`
--

DROP TABLE IF EXISTS `bs_batchjob_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_batchjob_orders` (
  `batchjob_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`batchjob_id`,`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_batchjob_orders`
--

LOCK TABLES `bs_batchjob_orders` WRITE;
/*!40000 ALTER TABLE `bs_batchjob_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_batchjob_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_batchjobs`
--

DROP TABLE IF EXISTS `bs_batchjobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_batchjobs` (
  `id` int(11) NOT NULL DEFAULT 0,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `from_status_id` int(11) NOT NULL DEFAULT 0,
  `to_status_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_batchjobs`
--

LOCK TABLES `bs_batchjobs` WRITE;
/*!40000 ALTER TABLE `bs_batchjobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_batchjobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_books`
--

DROP TABLE IF EXISTS `bs_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `order_id_prefix` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id_length` int(11) NOT NULL DEFAULT 6,
  `show_statuses` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `next_id` int(11) NOT NULL DEFAULT 0,
  `default_vat` double NOT NULL DEFAULT 19,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_csv_template` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_csv_template` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `call_after_days` tinyint(4) NOT NULL DEFAULT 0,
  `sender_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_purchase_orders_book` tinyint(1) NOT NULL DEFAULT 0,
  `backorder_status_id` int(11) NOT NULL DEFAULT 0,
  `delivered_status_id` int(11) NOT NULL DEFAULT 0,
  `reversal_status_id` int(11) NOT NULL DEFAULT 0,
  `addressbook_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `allow_delete` tinyint(1) NOT NULL DEFAULT 0,
  `import_status_id` int(11) NOT NULL DEFAULT 0,
  `auto_paid_status` tinyint(1) NOT NULL DEFAULT 0,
  `import_notify_customer` int(11) NOT NULL DEFAULT 0,
  `import_duplicate_to_book` int(11) NOT NULL DEFAULT 0,
  `import_duplicate_status_id` int(11) NOT NULL DEFAULT 0,
  `show_sales_agents` tinyint(1) NOT NULL DEFAULT 0,
  `default_due_days` int(11) NOT NULL DEFAULT 14,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_books`
--

LOCK TABLES `bs_books` WRITE;
/*!40000 ALTER TABLE `bs_books` DISABLE KEYS */;
INSERT INTO `bs_books` VALUES (1,1,'Quotes',34,'Q%y',6,NULL,2,19,'€',NULL,NULL,'',3,NULL,NULL,0,0,0,0,0,5,0,0,0,0,0,0,0,14),(2,1,'Orders',41,'O%y',6,NULL,2,19,'€',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,6,0,0,0,0,0,0,0,14),(3,1,'Invoices',46,'I%y',6,NULL,2,19,'€',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,7,0,0,0,0,0,0,0,14);
/*!40000 ALTER TABLE `bs_books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_category_languages`
--

DROP TABLE IF EXISTS `bs_category_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_category_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_category_languages`
--

LOCK TABLES `bs_category_languages` WRITE;
/*!40000 ALTER TABLE `bs_category_languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_category_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_cost_codes`
--

DROP TABLE IF EXISTS `bs_cost_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_cost_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_cost_codes`
--

LOCK TABLES `bs_cost_codes` WRITE;
/*!40000 ALTER TABLE `bs_cost_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_cost_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_doc_templates`
--

DROP TABLE IF EXISTS `bs_doc_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_doc_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longblob NOT NULL,
  `extension` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_doc_templates`
--

LOCK TABLES `bs_doc_templates` WRITE;
/*!40000 ALTER TABLE `bs_doc_templates` DISABLE KEYS */;
INSERT INTO `bs_doc_templates` VALUES (1,1,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i�Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz�D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a����������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH�I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!��Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�����Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^�^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt'),(2,2,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i�Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz�D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a����������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH�I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!��Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�����Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^�^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt'),(3,3,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i�Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz�D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a����������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH�I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!��Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�����Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^�^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt');
/*!40000 ALTER TABLE `bs_doc_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_expense_books`
--

DROP TABLE IF EXISTS `bs_expense_books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_expense_books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expense_books`
--

LOCK TABLES `bs_expense_books` WRITE;
/*!40000 ALTER TABLE `bs_expense_books` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_expense_books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_expense_categories`
--

DROP TABLE IF EXISTS `bs_expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_expense_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_book_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `book_id` (`expense_book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expense_categories`
--

LOCK TABLES `bs_expense_categories` WRITE;
/*!40000 ALTER TABLE `bs_expense_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_expenses`
--

DROP TABLE IF EXISTS `bs_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `expense_book_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `supplier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_no` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `btime` int(11) DEFAULT 0,
  `ptime` int(11) DEFAULT NULL,
  `subtotal` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `invoice_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `book_id` (`expense_book_id`,`category_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expenses`
--

LOCK TABLES `bs_expenses` WRITE;
/*!40000 ALTER TABLE `bs_expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_item_product_option`
--

DROP TABLE IF EXISTS `bs_item_product_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_item_product_option` (
  `item_id` int(11) NOT NULL,
  `product_option_value_id` int(11) NOT NULL,
  PRIMARY KEY (`item_id`,`product_option_value_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_item_product_option`
--

LOCK TABLES `bs_item_product_option` WRITE;
/*!40000 ALTER TABLE `bs_item_product_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_item_product_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_items`
--

DROP TABLE IF EXISTS `bs_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `unit_cost` double NOT NULL DEFAULT 0,
  `unit_price` double NOT NULL DEFAULT 0,
  `unit_list` double NOT NULL DEFAULT 0,
  `unit_total` double NOT NULL DEFAULT 0,
  `amount` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `vat_code` varchar(255) DEFAULT NULL,
  `discount` double NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) DEFAULT NULL,
  `tracking_code` varchar(255) DEFAULT NULL,
  `markup` double NOT NULL DEFAULT 0,
  `order_at_supplier` tinyint(1) NOT NULL DEFAULT 0,
  `order_at_supplier_company_id` int(11) NOT NULL DEFAULT 0,
  `amount_delivered` double NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL DEFAULT '',
  `item_group_id` int(11) NOT NULL DEFAULT 0,
  `extra_cost_status_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_items`
--

LOCK TABLES `bs_items` WRITE;
/*!40000 ALTER TABLE `bs_items` DISABLE KEYS */;
INSERT INTO `bs_items` VALUES (1,1,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(2,1,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(3,2,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(4,2,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(5,3,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(6,3,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(7,4,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(8,4,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(9,5,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(10,5,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(11,6,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(12,6,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0);
/*!40000 ALTER TABLE `bs_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_languages`
--

DROP TABLE IF EXISTS `bs_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `language` (`language`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_languages`
--

LOCK TABLES `bs_languages` WRITE;
/*!40000 ALTER TABLE `bs_languages` DISABLE KEYS */;
INSERT INTO `bs_languages` VALUES (1,'en','Default');
/*!40000 ALTER TABLE `bs_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_numbers`
--

DROP TABLE IF EXISTS `bs_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_numbers` (
  `book_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `next_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`book_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_numbers`
--

LOCK TABLES `bs_numbers` WRITE;
/*!40000 ALTER TABLE `bs_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_order_item_groups`
--

DROP TABLE IF EXISTS `bs_order_item_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_order_item_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Item Group',
  `summarize` tinyint(1) NOT NULL DEFAULT 0,
  `show_individual_prices` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_item_groups`
--

LOCK TABLES `bs_order_item_groups` WRITE;
/*!40000 ALTER TABLE `bs_order_item_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_order_item_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_order_payments`
--

DROP TABLE IF EXISTS `bs_order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_order_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `amount` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_payments`
--

LOCK TABLES `bs_order_payments` WRITE;
/*!40000 ALTER TABLE `bs_order_payments` DISABLE KEYS */;
INSERT INTO `bs_order_payments` VALUES (1,1,1641205611,20999.95,'Status: Sent'),(2,2,1641205611,38999.89,'Status: Sent'),(3,3,1641205612,20999.95,'Status: Waiting for payment'),(4,4,1641205612,38999.89,'Status: Waiting for payment'),(5,5,1641205613,20999.95,'Status: Waiting for payment'),(6,6,1641205613,38999.89,'Status: Waiting for payment');
/*!40000 ALTER TABLE `bs_order_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_order_status_history`
--

DROP TABLE IF EXISTS `bs_order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_order_status_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `notified` tinyint(1) NOT NULL DEFAULT 0,
  `notification_email` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`,`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_status_history`
--

LOCK TABLES `bs_order_status_history` WRITE;
/*!40000 ALTER TABLE `bs_order_status_history` DISABLE KEYS */;
INSERT INTO `bs_order_status_history` VALUES (1,1,1,1,1641205611,0,'billing/notifications/1/202201/1/1641205611.eml',NULL),(2,2,1,1,1641205611,0,'billing/notifications/1/202201/2/1641205611.eml',NULL),(3,3,5,1,1641205612,0,'billing/notifications/2/202201/3/1641205612.eml',NULL),(4,4,5,1,1641205612,0,'billing/notifications/2/202201/4/1641205612.eml',NULL),(5,5,9,1,1641205613,0,'billing/notifications/3/202201/5/1641205613.eml',NULL),(6,6,9,1,1641205613,0,'billing/notifications/3/202201/6/1641205613.eml',NULL);
/*!40000 ALTER TABLE `bs_order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_order_statuses`
--

DROP TABLE IF EXISTS `bs_order_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_order_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `max_age` int(11) NOT NULL DEFAULT 0,
  `payment_required` tinyint(1) NOT NULL DEFAULT 0,
  `remove_from_stock` tinyint(1) NOT NULL DEFAULT 0,
  `read_only` tinyint(1) NOT NULL DEFAULT 0,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FFFFFF',
  `required_status_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `apply_extra_cost` tinyint(1) DEFAULT 0,
  `extra_cost_min_value` double DEFAULT NULL,
  `extra_cost_percentage` double DEFAULT NULL,
  `email_bcc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_owner` tinyint(1) NOT NULL DEFAULT 0,
  `ask_to_notify_customer` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_statuses`
--

LOCK TABLES `bs_order_statuses` WRITE;
/*!40000 ALTER TABLE `bs_order_statuses` DISABLE KEYS */;
INSERT INTO `bs_order_statuses` VALUES (1,1,0,0,0,0,'00CCFF',0,36,0,NULL,NULL,'',0,1),(2,1,0,0,0,0,'2AD56F',0,38,0,NULL,NULL,'',0,1),(3,1,0,0,0,0,'FF0000',0,39,0,NULL,NULL,'',0,1),(4,1,0,0,0,0,'FF9900',0,40,0,NULL,NULL,'',0,1),(5,2,0,0,0,0,'FF9900',0,42,0,NULL,NULL,'',0,1),(6,2,0,0,0,0,'FF0000',0,43,0,NULL,NULL,'',0,1),(7,2,0,0,0,0,'2AD56F',0,44,0,NULL,NULL,'',0,1),(8,2,0,0,0,0,'00CCFF',0,45,0,NULL,NULL,'',0,1),(9,3,0,0,0,0,'FF9900',0,47,0,NULL,NULL,'',0,1),(10,3,0,0,0,0,'FF0000',0,48,0,NULL,NULL,'',0,1),(11,3,0,0,0,0,'2AD56F',0,49,0,NULL,NULL,'',0,1),(12,3,0,0,0,0,'00CCFF',0,50,0,NULL,NULL,'',0,1);
/*!40000 ALTER TABLE `bs_order_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_orders`
--

DROP TABLE IF EXISTS `bs_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `language_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `order_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `po_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) DEFAULT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `btime` int(11) NOT NULL DEFAULT 0,
  `ptime` int(11) NOT NULL DEFAULT 0,
  `costs` double NOT NULL DEFAULT 0,
  `subtotal` double NOT NULL DEFAULT 0,
  `vat` double DEFAULT NULL,
  `total` double NOT NULL DEFAULT 0,
  `authcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frontpage_text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `customer_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_salutation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_contact_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_address_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_zip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_state` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_country` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_vat_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_crn` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `customer_email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_extra` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `webshop_id` int(11) NOT NULL DEFAULT 0,
  `recur_type` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `recurred_order_id` int(11) NOT NULL DEFAULT 0,
  `reference` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `order_bonus_points` int(11) DEFAULT NULL,
  `pagebreak` tinyint(1) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_warehouse` tinyint(1) NOT NULL DEFAULT 0,
  `dtime` int(11) NOT NULL DEFAULT 0,
  `total_paid` double NOT NULL DEFAULT 0,
  `due_date` int(11) DEFAULT NULL,
  `other_shipping_address` tinyint(1) NOT NULL DEFAULT 0,
  `shipping_to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_salutation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_zip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_state` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_country` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_extra` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telesales_agent` int(11) DEFAULT NULL,
  `fieldsales_agent` int(11) DEFAULT NULL,
  `extra_costs` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `book_id` (`book_id`),
  KEY `status_id` (`status_id`),
  KEY `recurred_order_id` (`recurred_order_id`),
  KEY `project_id` (`project_id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `bs_orders_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bs_orders_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_orders`
--

LOCK TABLES `bs_orders` WRITE;
/*!40000 ALTER TABLE `bs_orders` DISABLE KEYS */;
INSERT INTO `bs_orders` VALUES (1,0,1,1,1,1,'Q22000001','',NULL,2,1641205610,1641205611,1,1641205610,1641205611,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(2,0,1,1,1,1,'Q22000002','',3,4,1641205611,1641205611,1,1641205611,1641205611,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(3,0,5,2,1,1,'O22000001','',NULL,2,1641205612,1641205612,1,1641205612,1641205612,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(4,0,5,2,1,1,'O22000002','',3,4,1641205612,1641205612,1,1641205612,1641205612,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(5,0,9,3,1,1,'I22000001','',NULL,2,1641205613,1641205613,1,1641205613,1641205613,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(6,0,9,3,1,1,'I22000002','',3,4,1641205613,1641205613,1,1641205613,1641205613,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0);
/*!40000 ALTER TABLE `bs_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_orders_custom_fields`
--

DROP TABLE IF EXISTS `bs_orders_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_orders_custom_fields` (
  `id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `bs_orders_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `bs_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bs_orders_custom_fields_ibfk_2` FOREIGN KEY (`id`) REFERENCES `bs_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_orders_custom_fields`
--

LOCK TABLES `bs_orders_custom_fields` WRITE;
/*!40000 ALTER TABLE `bs_orders_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_orders_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_categories`
--

DROP TABLE IF EXISTS `bs_product_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_categories`
--

LOCK TABLES `bs_product_categories` WRITE;
/*!40000 ALTER TABLE `bs_product_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_product_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_languages`
--

DROP TABLE IF EXISTS `bs_product_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_languages`
--

LOCK TABLES `bs_product_languages` WRITE;
/*!40000 ALTER TABLE `bs_product_languages` DISABLE KEYS */;
INSERT INTO `bs_product_languages` VALUES (1,1,'Master Rocket 1000','Master Rocket 1000. The ultimate rocket to blast rocky mountains.',''),(1,2,'Rocket Launcher 1000','Rocket Launcher 1000. Required to launch rockets.','');
/*!40000 ALTER TABLE `bs_product_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_option`
--

DROP TABLE IF EXISTS `bs_product_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_option`
--

LOCK TABLES `bs_product_option` WRITE;
/*!40000 ALTER TABLE `bs_product_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_product_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_option_language`
--

DROP TABLE IF EXISTS `bs_product_option_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_option_language` (
  `product_option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`product_option_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_option_language`
--

LOCK TABLES `bs_product_option_language` WRITE;
/*!40000 ALTER TABLE `bs_product_option_language` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_product_option_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_option_value`
--

DROP TABLE IF EXISTS `bs_product_option_value`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_option_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_option_id` int(11) NOT NULL,
  `value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_option_value`
--

LOCK TABLES `bs_product_option_value` WRITE;
/*!40000 ALTER TABLE `bs_product_option_value` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_product_option_value` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_product_option_value_language`
--

DROP TABLE IF EXISTS `bs_product_option_value_language`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_product_option_value_language` (
  `product_option_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`product_option_value_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_product_option_value_language`
--

LOCK TABLES `bs_product_option_value_language` WRITE;
/*!40000 ALTER TABLE `bs_product_option_value_language` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_product_option_value_language` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_products`
--

DROP TABLE IF EXISTS `bs_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cost_price` double NOT NULL DEFAULT 0,
  `list_price` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `total_price` double NOT NULL DEFAULT 0,
  `supplier_company_id` int(11) NOT NULL DEFAULT 0,
  `supplier_product_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `required_products` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `stock_min` int(11) NOT NULL DEFAULT 0,
  `article_id` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `unit_stock` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_products`
--

LOCK TABLES `bs_products` WRITE;
/*!40000 ALTER TABLE `bs_products` DISABLE KEYS */;
INSERT INTO `bs_products` VALUES (1,0,0,'',1000,2999.99,0,2999.99,3,NULL,0,'',0,'12345','pcs','',0,NULL,NULL),(2,0,0,'',3000,8999.99,0,8999.99,3,NULL,0,'',0,'234567','pcs','',0,NULL,NULL);
/*!40000 ALTER TABLE `bs_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_products_custom_fields`
--

DROP TABLE IF EXISTS `bs_products_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_products_custom_fields` (
  `id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `bs_products_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `bs_products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bs_products_custom_fields_ibfk_2` FOREIGN KEY (`id`) REFERENCES `bs_products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_products_custom_fields`
--

LOCK TABLES `bs_products_custom_fields` WRITE;
/*!40000 ALTER TABLE `bs_products_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_products_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_status_languages`
--

DROP TABLE IF EXISTS `bs_status_languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_status_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_cost_item_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_template` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `screen_template` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_template_id` int(11) NOT NULL DEFAULT 0,
  `doc_template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`language_id`,`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_status_languages`
--

LOCK TABLES `bs_status_languages` WRITE;
/*!40000 ALTER TABLE `bs_status_languages` DISABLE KEYS */;
INSERT INTO `bs_status_languages` VALUES (1,1,'Sent',NULL,'Your Invoice has status Sent','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,1,1),(1,2,'Accepted',NULL,'Your Invoice has status Accepted','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Accepted.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,1,1),(1,3,'Lost',NULL,'Your Invoice has status Lost','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Lost.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,1,1),(1,4,'In process',NULL,'Your Invoice has status In process','%customer_salutation%,<br />\n<br />\nYour Invoice is in status In process.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,1,1),(1,5,'Waiting for payment',NULL,'Your Invoice has status Waiting for payment','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Waiting for payment.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,2,2),(1,6,'Reminder sent',NULL,'Your Invoice has status Reminder sent','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Reminder sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,2,2),(1,7,'Paid',NULL,'Your Invoice has status Paid','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Paid.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,2,2),(1,8,'Credit',NULL,'Your Invoice has status Credit','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Credit.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,2,2),(1,9,'Waiting for payment',NULL,'Your Invoice has status Waiting for payment','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Waiting for payment.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,3,3),(1,10,'Reminder sent',NULL,'Your Invoice has status Reminder sent','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Reminder sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,3,3),(1,11,'Paid',NULL,'Your Invoice has status Paid','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Paid.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,3,3),(1,12,'Credit',NULL,'Your Invoice has status Credit','%customer_salutation%,<br />\n<br />\nYour Invoice is in status Credit.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;',NULL,3,3);
/*!40000 ALTER TABLE `bs_status_languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_tax_rates`
--

DROP TABLE IF EXISTS `bs_tax_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_tax_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_tax_rates`
--

LOCK TABLES `bs_tax_rates` WRITE;
/*!40000 ALTER TABLE `bs_tax_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_tax_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_templates`
--

DROP TABLE IF EXISTS `bs_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `right_col` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `left_col` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `margin_top` int(11) NOT NULL DEFAULT 30,
  `margin_bottom` int(11) NOT NULL DEFAULT 30,
  `margin_left` int(11) NOT NULL DEFAULT 30,
  `margin_right` int(11) NOT NULL DEFAULT 30,
  `page_format` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stationery_paper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `closing` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_name` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_width` int(11) NOT NULL DEFAULT 0,
  `logo_height` int(11) NOT NULL DEFAULT 0,
  `show_supplier_product_id` tinyint(1) NOT NULL DEFAULT 0,
  `show_nett_total_price` tinyint(1) NOT NULL DEFAULT 1,
  `show_nett_unit_price` tinyint(1) NOT NULL DEFAULT 1,
  `show_summary_totals` tinyint(1) NOT NULL DEFAULT 1,
  `show_vat` tinyint(1) NOT NULL DEFAULT 1,
  `show_units` tinyint(1) NOT NULL DEFAULT 0,
  `book_id` int(11) NOT NULL,
  `logo_top` int(11) NOT NULL DEFAULT 0,
  `logo_left` int(11) NOT NULL DEFAULT 0,
  `left_col_top` int(11) NOT NULL DEFAULT 30,
  `left_col_left` int(11) NOT NULL DEFAULT 30,
  `right_col_top` int(11) NOT NULL DEFAULT 30,
  `right_col_left` int(11) NOT NULL DEFAULT 365,
  `show_amounts` tinyint(1) NOT NULL DEFAULT 1,
  `logo_only_first_page` tinyint(1) NOT NULL DEFAULT 0,
  `use_html_table` tinyint(1) NOT NULL DEFAULT 0,
  `html_table` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat_header` tinyint(1) NOT NULL DEFAULT 0,
  `show_gross_unit_price` tinyint(1) NOT NULL DEFAULT 1,
  `show_unit_cost` tinyint(1) NOT NULL DEFAULT 0,
  `show_gross_total_price` tinyint(1) NOT NULL DEFAULT 1,
  `show_date_sent` tinyint(1) NOT NULL DEFAULT 0,
  `show_page_numbers` tinyint(1) NOT NULL DEFAULT 0,
  `show_total_paid` tinyint(1) NOT NULL DEFAULT 0,
  `show_reference` tinyint(1) NOT NULL DEFAULT 1,
  `show_product_number` tinyint(1) NOT NULL DEFAULT 0,
  `show_item_id` tinyint(1) NOT NULL DEFAULT 0,
  `show_cost_code` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_templates`
--

LOCK TABLES `bs_templates` WRITE;
/*!40000 ALTER TABLE `bs_templates` DISABLE KEYS */;
INSERT INTO `bs_templates` VALUES (1,'Quotes',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the invoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,1,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(2,'Orders',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the invoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,2,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(3,'Invoices',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the invoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,3,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0);
/*!40000 ALTER TABLE `bs_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bs_tracking_codes`
--

DROP TABLE IF EXISTS `bs_tracking_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bs_tracking_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `costcode_id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_tracking_codes`
--

LOCK TABLES `bs_tracking_codes` WRITE;
/*!40000 ALTER TABLE `bs_tracking_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `bs_tracking_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_activity`
--

DROP TABLE IF EXISTS `business_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_activity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` smallint(6) NOT NULL DEFAULT 1,
  `code` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `units` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT 0,
  `billable` tinyint(4) DEFAULT 0,
  `budgetable` tinyint(4) DEFAULT 0,
  `budgetExpires` tinyint(1) DEFAULT 0,
  `budgetTransferable` tinyint(1) DEFAULT 0,
  `sortOrder` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity`
--

LOCK TABLES `business_activity` WRITE;
/*!40000 ALTER TABLE `business_activity` DISABLE KEYS */;
INSERT INTO `business_activity` VALUES (1,2,'1','Holidays',8,NULL,0,0,1,0,0,1),(2,3,'2','Sick',8,NULL,0,0,0,0,0,2);
/*!40000 ALTER TABLE `business_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_activity_budget`
--

DROP TABLE IF EXISTS `business_activity_budget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_activity_budget` (
  `agreementId` int(10) unsigned NOT NULL,
  `activityId` int(10) unsigned NOT NULL,
  `budget` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`agreementId`,`activityId`),
  KEY `fk_business_activity_budget_business_activity1_idx` (`activityId`),
  KEY `fk_business_activity_budget_business_employee_agreement1_idx` (`agreementId`),
  CONSTRAINT `fk_business_activity_budget_business_activity1` FOREIGN KEY (`activityId`) REFERENCES `business_activity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_activity_budget_business_employee_agreement1` FOREIGN KEY (`agreementId`) REFERENCES `business_agreement` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity_budget`
--

LOCK TABLES `business_activity_budget` WRITE;
/*!40000 ALTER TABLE `business_activity_budget` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_activity_budget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_activity_rate`
--

DROP TABLE IF EXISTS `business_activity_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_activity_rate` (
  `employeeId` int(11) NOT NULL,
  `activityId` int(10) unsigned NOT NULL,
  `externalRate` float NOT NULL,
  PRIMARY KEY (`employeeId`,`activityId`),
  KEY `fk_business_employee_activity_business_activity1_idx` (`activityId`),
  KEY `fk_business_employee_activity_business_employee1_idx` (`employeeId`),
  CONSTRAINT `fk_business_employee_activity_business_activity1` FOREIGN KEY (`activityId`) REFERENCES `business_activity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_employee_activity_business_employee1` FOREIGN KEY (`employeeId`) REFERENCES `business_employee` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity_rate`
--

LOCK TABLES `business_activity_rate` WRITE;
/*!40000 ALTER TABLE `business_activity_rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_activity_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_agreement`
--

DROP TABLE IF EXISTS `business_agreement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_agreement` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employeeId` int(11) NOT NULL,
  `start` date NOT NULL,
  `finish` date DEFAULT NULL,
  `mo` smallint(5) unsigned NOT NULL,
  `tu` smallint(5) unsigned NOT NULL,
  `we` smallint(5) unsigned NOT NULL,
  `th` smallint(5) unsigned NOT NULL,
  `fr` smallint(5) unsigned NOT NULL,
  `sa` smallint(5) unsigned NOT NULL,
  `su` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_business_agreement_business_employee1_idx` (`employeeId`),
  CONSTRAINT `fk_business_agreement_business_employee1` FOREIGN KEY (`employeeId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_agreement`
--

LOCK TABLES `business_agreement` WRITE;
/*!40000 ALTER TABLE `business_agreement` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_agreement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_business`
--

DROP TABLE IF EXISTS `business_business`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_business` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourlyRevenue` float DEFAULT NULL,
  `contactId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_business_business_addressbook_contact_idx` (`contactId`),
  CONSTRAINT `fk_business_business_addressbook_contact` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_business`
--

LOCK TABLES `business_business` WRITE;
/*!40000 ALTER TABLE `business_business` DISABLE KEYS */;
INSERT INTO `business_business` VALUES (1,'Default business',NULL,NULL);
/*!40000 ALTER TABLE `business_business` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_business_custom_fields`
--

DROP TABLE IF EXISTS `business_business_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_business_custom_fields` (
  `id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `business_business_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `business_business` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_business_custom_fields`
--

LOCK TABLES `business_business_custom_fields` WRITE;
/*!40000 ALTER TABLE `business_business_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_business_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_employee`
--

DROP TABLE IF EXISTS `business_employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_employee` (
  `id` int(11) NOT NULL,
  `businessId` int(10) unsigned NOT NULL,
  `timeClosedUntil` date DEFAULT NULL,
  `quitAt` date DEFAULT NULL,
  `hourlyRevenue` float NOT NULL DEFAULT 0,
  `hourlyCosts` float NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_business_employee_business_business1_idx` (`businessId`),
  CONSTRAINT `fk_business_employee_business_business1` FOREIGN KEY (`businessId`) REFERENCES `business_business` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_employee_core_user1` FOREIGN KEY (`id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_employee`
--

LOCK TABLES `business_employee` WRITE;
/*!40000 ALTER TABLE `business_employee` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_manager`
--

DROP TABLE IF EXISTS `business_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `business_manager` (
  `subjectId` int(11) NOT NULL,
  `managerId` int(11) NOT NULL,
  `notified` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`subjectId`,`managerId`),
  KEY `fk_business_employeebusiness_employee_business_employee2_idx` (`managerId`),
  KEY `fk_business_employeebusiness_employee_business_employee1_idx` (`subjectId`),
  CONSTRAINT `fk_business_employeecore_user_core_user1` FOREIGN KEY (`subjectId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_employeecore_user_core_user2` FOREIGN KEY (`managerId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_manager`
--

LOCK TABLES `business_manager` WRITE;
/*!40000 ALTER TABLE `business_manager` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_calendar_user_colors`
--

DROP TABLE IF EXISTS `cal_calendar_user_colors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_calendar_user_colors` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendar_user_colors`
--

LOCK TABLES `cal_calendar_user_colors` WRITE;
/*!40000 ALTER TABLE `cal_calendar_user_colors` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_calendar_user_colors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_calendars`
--

DROP TABLE IF EXISTS `cal_calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT 1,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_hour` tinyint(4) NOT NULL DEFAULT 0,
  `end_hour` tinyint(4) NOT NULL DEFAULT 0,
  `background` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 1800,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `shared_acl` tinyint(1) NOT NULL DEFAULT 0,
  `show_bdays` tinyint(1) NOT NULL DEFAULT 0,
  `show_completed_tasks` tinyint(1) NOT NULL DEFAULT 1,
  `comment` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `project_id` int(11) NOT NULL DEFAULT 0,
  `tasklist_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `show_holidays` tinyint(1) NOT NULL DEFAULT 1,
  `enable_ics_import` tinyint(1) NOT NULL DEFAULT 0,
  `ics_import_url` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tooltip` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `project_id` (`project_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendars`
--

LOCK TABLES `cal_calendars` WRITE;
/*!40000 ALTER TABLE `cal_calendars` DISABLE KEYS */;
INSERT INTO `cal_calendars` VALUES (1,1,3,87,'Demo User',0,0,NULL,1800,0,0,0,1,'',0,0,16,1,0,'','',4),(2,1,2,88,'Elmer Fudd',0,0,NULL,1800,0,0,0,1,'',0,0,17,1,0,'','',1),(3,1,4,89,'Linda Smith',0,0,NULL,1800,0,0,0,1,'',0,0,18,1,0,'','',16),(4,2,1,93,'Road Runner Room',0,0,NULL,1800,0,0,0,1,'',0,0,19,1,0,'','',1),(5,2,1,94,'Don Coyote Room',0,0,NULL,1800,0,0,0,1,'',0,0,20,1,0,'','',1);
/*!40000 ALTER TABLE `cal_calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_calendars_custom_fields`
--

DROP TABLE IF EXISTS `cal_calendars_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_calendars_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cal_calendars_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cal_calendars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendars_custom_fields`
--

LOCK TABLES `cal_calendars_custom_fields` WRITE;
/*!40000 ALTER TABLE `cal_calendars_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_calendars_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_categories`
--

DROP TABLE IF EXISTS `cal_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_categories`
--

LOCK TABLES `cal_categories` WRITE;
/*!40000 ALTER TABLE `cal_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_events`
--

DROP TABLE IF EXISTS `cal_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `calendar_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `end_time` int(11) NOT NULL DEFAULT 0,
  `timezone` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `all_day_event` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `repeat_end_time` int(11) NOT NULL DEFAULT 0,
  `reminder` int(11) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `busy` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEEDS-ACTION',
  `resource_event_id` int(11) NOT NULL DEFAULT 0,
  `private` tinyint(1) NOT NULL DEFAULT 0,
  `rrule` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `background` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ebf1e2',
  `files_folder_id` int(11) NOT NULL,
  `read_only` tinyint(1) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `exception_for_event_id` int(11) NOT NULL DEFAULT 0,
  `recurrence_id` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `start_time` (`start_time`),
  KEY `end_time` (`end_time`),
  KEY `repeat_end_time` (`repeat_end_time`),
  KEY `rrule` (`rrule`),
  KEY `calendar_id` (`calendar_id`),
  KEY `busy` (`busy`),
  KEY `category_id` (`category_id`),
  KEY `uuid` (`uuid`),
  KEY `resource_event_id` (`resource_event_id`),
  KEY `recurrence_id` (`recurrence_id`),
  KEY `exception_for_event_id` (`exception_for_event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events`
--

LOCK TABLES `cal_events` WRITE;
/*!40000 ALTER TABLE `cal_events` DISABLE KEYS */;
INSERT INTO `cal_events` VALUES (1,'19218cac-c323-5b91-b28f-f8375ea3dcde',1,3,1641290400,1641294000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(2,'19218cac-c323-5b91-b28f-f8375ea3dcde',3,4,1641290400,1641294000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(3,'0019e88f-9ebd-5413-a0bd-e9c4a36a2df3',1,3,1641297600,1641301200,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(4,'0019e88f-9ebd-5413-a0bd-e9c4a36a2df3',3,4,1641297600,1641301200,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(5,'e04f9d2d-65c0-5ebc-ab9b-c558e0a2e464',1,3,1641304800,1641308400,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(6,'e04f9d2d-65c0-5ebc-ab9b-c558e0a2e464',3,4,1641304800,1641308400,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1641205606,1641205606,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(7,'9c5082d8-7409-575b-8969-019ab8138332',3,4,1641380400,1641384000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1641205606,1641205607,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(8,'0dcda4ed-099c-5ee1-9d88-15ee1bdcf701',3,4,1641387600,1641391200,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1641205607,1641205607,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(9,'4b493ac7-401b-5686-b8d4-41922d5b7e80',3,4,1641398400,1641402000,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1641205607,1641205607,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(10,'942a8326-5c38-57d7-8d9c-16f210b9b6fb',3,4,1641283200,1641286800,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1641205607,1641205607,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(11,'00d0178e-f049-54ba-94e6-87f4825549c7',3,4,1641308400,1641312000,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1641205608,1641205608,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(12,'6fa92c39-eef5-5c2e-a33f-fd3d010bbcf0',3,4,1641322800,1641326400,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1641205608,1641205608,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1);
/*!40000 ALTER TABLE `cal_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_events_custom_fields`
--

DROP TABLE IF EXISTS `cal_events_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_events_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `cal_events_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cal_events` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events_custom_fields`
--

LOCK TABLES `cal_events_custom_fields` WRITE;
/*!40000 ALTER TABLE `cal_events_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_events_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_events_declined`
--

DROP TABLE IF EXISTS `cal_events_declined`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_events_declined` (
  `uid` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`uid`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events_declined`
--

LOCK TABLES `cal_events_declined` WRITE;
/*!40000 ALTER TABLE `cal_events_declined` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_events_declined` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_exceptions`
--

DROP TABLE IF EXISTS `cal_exceptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_exceptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `exception_event_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_exceptions`
--

LOCK TABLES `cal_exceptions` WRITE;
/*!40000 ALTER TABLE `cal_exceptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_exceptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_group_admins`
--

DROP TABLE IF EXISTS `cal_group_admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_group_admins` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_group_admins`
--

LOCK TABLES `cal_group_admins` WRITE;
/*!40000 ALTER TABLE `cal_group_admins` DISABLE KEYS */;
INSERT INTO `cal_group_admins` VALUES (2,2);
/*!40000 ALTER TABLE `cal_group_admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_groups`
--

DROP TABLE IF EXISTS `cal_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fields` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show_not_as_busy` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_groups`
--

LOCK TABLES `cal_groups` WRITE;
/*!40000 ALTER TABLE `cal_groups` DISABLE KEYS */;
INSERT INTO `cal_groups` VALUES (1,1,'Calendars','',0),(2,1,'Meeting rooms','',0);
/*!40000 ALTER TABLE `cal_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_participants`
--

DROP TABLE IF EXISTS `cal_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEEDS-ACTION',
  `last_modified` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_participants`
--

LOCK TABLES `cal_participants` WRITE;
/*!40000 ALTER TABLE `cal_participants` DISABLE KEYS */;
INSERT INTO `cal_participants` VALUES (1,1,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(2,1,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(3,1,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(4,2,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(5,2,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(6,2,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(7,3,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(8,3,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(9,3,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(10,4,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(11,4,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(12,4,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(13,5,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(14,5,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(15,5,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(16,6,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(17,6,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(18,6,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(19,7,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(20,7,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(21,7,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(22,8,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(23,8,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(24,8,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(25,9,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(26,9,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(27,9,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(28,10,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(29,10,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(30,10,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(31,11,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(32,11,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(33,11,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(34,12,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(35,12,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(36,12,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,'');
/*!40000 ALTER TABLE `cal_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_settings`
--

DROP TABLE IF EXISTS `cal_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_settings` (
  `user_id` int(11) NOT NULL,
  `reminder` int(11) DEFAULT NULL,
  `background` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `show_statuses` tinyint(1) NOT NULL DEFAULT 1,
  `check_conflict` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_settings`
--

LOCK TABLES `cal_settings` WRITE;
/*!40000 ALTER TABLE `cal_settings` DISABLE KEYS */;
INSERT INTO `cal_settings` VALUES (2,NULL,'EBF1E2',2,1,1),(3,NULL,'EBF1E2',1,1,1),(4,NULL,'EBF1E2',3,1,1),(5,NULL,'EBF1E2',0,1,1),(6,NULL,'EBF1E2',0,1,1);
/*!40000 ALTER TABLE `cal_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_views`
--

DROP TABLE IF EXISTS `cal_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 1800,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `merge` tinyint(1) NOT NULL DEFAULT 0,
  `owncolor` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_views`
--

LOCK TABLES `cal_views` WRITE;
/*!40000 ALTER TABLE `cal_views` DISABLE KEYS */;
INSERT INTO `cal_views` VALUES (1,1,'Everyone',1800,90,0,1),(2,1,'Everyone (Merge)',1800,92,1,1);
/*!40000 ALTER TABLE `cal_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_views_calendars`
--

DROP TABLE IF EXISTS `cal_views_calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_views_calendars` (
  `view_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `background` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CCFFCC',
  PRIMARY KEY (`view_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_views_calendars`
--

LOCK TABLES `cal_views_calendars` WRITE;
/*!40000 ALTER TABLE `cal_views_calendars` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_views_calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_views_groups`
--

DROP TABLE IF EXISTS `cal_views_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_views_groups` (
  `view_id` int(11) NOT NULL,
  `group_id` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`view_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_views_groups`
--

LOCK TABLES `cal_views_groups` WRITE;
/*!40000 ALTER TABLE `cal_views_groups` DISABLE KEYS */;
INSERT INTO `cal_views_groups` VALUES (1,'2'),(2,'2');
/*!40000 ALTER TABLE `cal_views_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_visible_tasklists`
--

DROP TABLE IF EXISTS `cal_visible_tasklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_visible_tasklists` (
  `calendar_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY (`calendar_id`,`tasklist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_visible_tasklists`
--

LOCK TABLES `cal_visible_tasklists` WRITE;
/*!40000 ALTER TABLE `cal_visible_tasklists` DISABLE KEYS */;
/*!40000 ALTER TABLE `cal_visible_tasklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments_attachment`
--

DROP TABLE IF EXISTS `comments_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments_attachment` (
  `commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`commentId`,`blobId`),
  KEY `fk_comments_attachment_comments_comment1_idx` (`commentId`),
  KEY `fk_comments_attachment_core_blob1_idx` (`blobId`),
  CONSTRAINT `fk_comments_attachment_comments_comment1` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_attachment_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments_attachment`
--

LOCK TABLES `comments_attachment` WRITE;
/*!40000 ALTER TABLE `comments_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments_comment`
--

DROP TABLE IF EXISTS `comments_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `date` datetime NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `text` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_comments_comment_core_entity_type_idx` (`entityId`),
  KEY `fk_comments_comment_core_user1_idx` (`createdBy`),
  KEY `fk_comments_comment_core_user2_idx` (`modifiedBy`),
  KEY `entityTypeId` (`entityTypeId`),
  KEY `section` (`section`),
  KEY `date` (`date`),
  CONSTRAINT `comments_comment_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_comment_core_user1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_comment_core_user2` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments_comment`
--

LOCK TABLES `comments_comment` WRITE;
/*!40000 ALTER TABLE `comments_comment` DISABLE KEYS */;
INSERT INTO `comments_comment` VALUES (1,'2022-01-03 10:26:44','2022-01-03 10:26:44',2,24,3,1,'2022-01-03 10:26:44','Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',NULL),(2,'2022-01-03 10:26:44','2022-01-03 10:26:44',2,24,2,1,'2022-01-03 10:26:44','In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',NULL),(3,'2022-01-03 10:26:45','2022-01-03 10:26:45',4,24,3,1,'2022-01-03 10:26:45','Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',NULL),(4,'2022-01-03 10:26:45','2022-01-03 10:26:45',4,24,2,1,'2022-01-03 10:26:45','In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',NULL);
/*!40000 ALTER TABLE `comments_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments_comment_image`
--

DROP TABLE IF EXISTS `comments_comment_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments_comment_image` (
  `commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  PRIMARY KEY (`commentId`,`blobId`),
  KEY `blobId` (`blobId`),
  CONSTRAINT `comments_comment_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `comments_comment_image_ibfk_2` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments_comment_image`
--

LOCK TABLES `comments_comment_image` WRITE;
/*!40000 ALTER TABLE `comments_comment_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments_comment_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments_comment_label`
--

DROP TABLE IF EXISTS `comments_comment_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments_comment_label` (
  `labelId` int(11) NOT NULL,
  `commentId` int(11) NOT NULL,
  PRIMARY KEY (`labelId`,`commentId`),
  KEY `fk_comments_label_has_comments_comment_comments_comment1_idx` (`commentId`),
  KEY `fk_comments_label_has_comments_comment_comments_label1_idx` (`labelId`),
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_comment1` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_comments_label_has_comments_comment_comments_label1` FOREIGN KEY (`labelId`) REFERENCES `comments_label` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments_comment_label`
--

LOCK TABLES `comments_comment_label` WRITE;
/*!40000 ALTER TABLE `comments_comment_label` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments_comment_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments_label`
--

DROP TABLE IF EXISTS `comments_label`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '243a80',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments_label`
--

LOCK TABLES `comments_label` WRITE;
/*!40000 ALTER TABLE `comments_label` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments_label` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_acl`
--

DROP TABLE IF EXISTS `core_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_acl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownedBy` int(11) DEFAULT NULL,
  `usedIn` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `entityTypeId` int(11) DEFAULT NULL,
  `entityId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `core_acl_ibfk_1` (`entityTypeId`),
  KEY `ownedBy` (`ownedBy`),
  CONSTRAINT `core_acl_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_acl_ibfk_2` FOREIGN KEY (`ownedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=115 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl`
--

LOCK TABLES `core_acl` WRITE;
/*!40000 ALTER TABLE `core_acl` DISABLE KEYS */;
INSERT INTO `core_acl` VALUES (1,1,'core_group.aclId','2022-01-03 10:26:11',11,1),(2,1,'core_group.aclId','2022-01-03 10:26:11',11,2),(3,1,'core_group.aclId','2022-01-03 10:26:11',11,3),(4,1,'core_group.aclId','2022-01-03 10:26:11',11,4),(5,1,'core_module.aclId','2022-01-03 10:26:11',13,1),(6,1,'core_entity.defaultAclId','2022-01-03 10:26:13',NULL,NULL),(7,1,'core_entity.defaultAclId','2022-01-03 10:26:13',NULL,NULL),(8,1,'core_entity.defaultAclId','2022-01-03 10:26:13',NULL,NULL),(9,1,'go_templates.acl_id','2022-01-03 10:26:14',22,1),(10,1,'core_entity.defaultAclId','2022-01-03 10:26:14',NULL,NULL),(11,1,'core_module.aclId','2022-01-03 10:26:17',13,2),(12,1,'addressbook_addressbook.aclId','2022-01-03 10:26:17',23,1),(13,1,'core_module.aclId','2022-01-03 10:26:18',13,3),(14,1,'core_module.aclId','2022-01-03 10:26:18',13,4),(15,1,'core_email_template.aclId','2022-01-03 10:26:18',7,1),(16,1,'core_module.aclId','2022-01-03 10:26:18',13,5),(17,1,'core_module.aclId','2022-01-03 10:26:19',13,6),(18,1,'core_entity.defaultAclId','2022-01-03 10:26:18',NULL,NULL),(19,1,'fs_templates.acl_id','2022-01-03 10:26:20',33,1),(20,1,'core_entity.defaultAclId','2022-01-03 10:26:19',NULL,NULL),(21,1,'fs_templates.acl_id','2022-01-03 10:26:20',33,2),(22,1,'fs_folders.acl_id','2022-01-03 10:26:20',32,2),(23,1,'fs_folders.acl_id','2022-01-03 10:26:20',32,3),(24,1,'core_module.aclId','2022-01-03 10:26:21',13,7),(25,1,'core_module.aclId','2022-01-03 10:26:21',13,8),(26,1,'core_module.aclId','2022-01-03 10:26:21',13,9),(27,1,'core_module.aclId','2022-01-03 10:26:21',13,10),(28,1,'core_module.aclId','2022-01-03 10:26:22',13,11),(29,1,'core_module.aclId','2022-01-03 10:26:22',13,12),(30,1,'notes_note_book.aclId','2022-01-03 10:26:22',41,65),(31,1,'core_module.aclId','2022-01-03 10:26:23',13,13),(32,1,'core_module.aclId','2022-01-03 10:26:26',13,14),(33,1,'core_module.aclId','2022-01-03 10:26:26',13,15),(34,1,'bs_books.acl_id','2022-01-03 10:26:50',47,1),(35,1,'core_entity.defaultAclId','2022-01-03 10:26:27',NULL,NULL),(36,1,'bs_order_statuses.acl_id','2022-01-03 10:26:28',48,1),(37,1,'core_entity.defaultAclId','2022-01-03 10:26:28',NULL,NULL),(38,1,'bs_order_statuses.acl_id','2022-01-03 10:26:28',48,2),(39,1,'bs_order_statuses.acl_id','2022-01-03 10:26:28',48,3),(40,1,'bs_order_statuses.acl_id','2022-01-03 10:26:28',48,4),(41,1,'bs_books.acl_id','2022-01-03 10:26:52',47,2),(42,1,'bs_order_statuses.acl_id','2022-01-03 10:26:29',48,5),(43,1,'bs_order_statuses.acl_id','2022-01-03 10:26:29',48,6),(44,1,'bs_order_statuses.acl_id','2022-01-03 10:26:29',48,7),(45,1,'bs_order_statuses.acl_id','2022-01-03 10:26:29',48,8),(46,1,'bs_books.acl_id','2022-01-03 10:26:53',47,3),(47,1,'bs_order_statuses.acl_id','2022-01-03 10:26:30',48,9),(48,1,'bs_order_statuses.acl_id','2022-01-03 10:26:30',48,10),(49,1,'bs_order_statuses.acl_id','2022-01-03 10:26:30',48,11),(50,1,'bs_order_statuses.acl_id','2022-01-03 10:26:30',48,12),(51,1,'core_module.aclId','2022-01-03 10:26:30',13,16),(52,1,'core_entity.defaultAclId','2022-01-03 10:26:31',NULL,NULL),(53,1,'core_module.aclId','2022-01-03 10:26:31',13,17),(54,1,'core_module.aclId','2022-01-03 10:26:31',13,18),(55,1,'core_module.aclId','2022-01-03 10:26:31',13,19),(56,1,'go_templates.acl_id','2022-01-03 10:26:31',22,2),(57,1,'core_module.aclId','2022-01-03 10:26:32',13,20),(58,1,'core_module.aclId','2022-01-03 10:26:32',13,21),(59,1,'pr2_types.acl_id','2022-01-03 10:26:35',53,1),(60,1,'core_entity.defaultAclId','2022-01-03 10:26:35',NULL,NULL),(61,1,'pr2_types.acl_book','2022-01-03 10:26:35',NULL,NULL),(62,1,'pr2_statuses.acl_id','2022-01-03 10:26:35',54,1),(63,1,'core_entity.defaultAclId','2022-01-03 10:26:35',NULL,NULL),(64,1,'pr2_statuses.acl_id','2022-01-03 10:26:35',54,2),(65,1,'pr2_statuses.acl_id','2022-01-03 10:26:35',54,3),(66,1,'pr2_templates.acl_id','2022-01-03 10:26:35',55,1),(67,1,'core_entity.defaultAclId','2022-01-03 10:26:35',NULL,NULL),(68,1,'pr2_templates.acl_id','2022-01-03 10:26:36',55,2),(69,1,'core_module.aclId','2022-01-03 10:26:36',13,22),(70,1,'core_module.aclId','2022-01-03 10:26:36',13,23),(71,1,'core_module.aclId','2022-01-03 10:26:36',13,24),(72,1,'core_module.aclId','2022-01-03 10:26:36',13,25),(73,1,'core_module.aclId','2022-01-03 10:26:37',13,26),(74,1,'core_module.aclId','2022-01-03 10:26:37',13,27),(75,1,'core_module.aclId','2022-01-03 10:26:37',13,28),(76,1,'core_module.aclId','2022-01-03 10:26:38',13,29),(77,1,'core_module.aclId','2022-01-03 10:26:38',13,30),(78,1,'core_module.aclId','2022-01-03 10:26:38',13,31),(79,1,'ti_types.acl_id','2022-01-03 10:26:54',59,1),(80,1,'core_entity.defaultAclId','2022-01-03 10:26:39',NULL,NULL),(81,1,'ti_types.acl_id','2022-01-03 10:26:39',59,2),(82,1,'core_module.aclId','2022-01-03 10:26:39',13,32),(83,1,'addressbook_addressbook.aclId','2022-01-03 10:26:40',23,2),(84,1,'core_group.aclId','2022-01-03 10:26:43',11,5),(85,1,'core_group.aclId','2022-01-03 10:26:43',11,6),(86,1,'core_group.aclId','2022-01-03 10:26:43',11,7),(87,3,'cal_calendars.acl_id','2022-01-03 10:26:45',49,1),(88,2,'cal_calendars.acl_id','2022-01-03 10:26:45',49,2),(89,4,'cal_calendars.acl_id','2022-01-03 10:26:46',49,3),(90,1,'cal_views.acl_id','2022-01-03 10:26:48',60,1),(91,1,'core_entity.defaultAclId','2022-01-03 10:26:48',NULL,NULL),(92,1,'cal_views.acl_id','2022-01-03 10:26:48',60,2),(93,1,'cal_calendars.acl_id','2022-01-03 10:26:49',49,4),(94,1,'cal_calendars.acl_id','2022-01-03 10:26:49',49,5),(95,1,'ta_tasklists.acl_id','2022-01-03 10:26:49',61,1),(96,1,'core_entity.defaultAclId','2022-01-03 10:26:49',NULL,NULL),(97,3,'ta_tasklists.acl_id','2022-01-03 10:26:49',61,2),(98,4,'ta_tasklists.acl_id','2022-01-03 10:26:49',61,3),(99,2,'ta_tasklists.acl_id','2022-01-03 10:26:49',61,4),(100,1,'readonly','2022-01-03 10:26:51',NULL,NULL),(101,1,'core_module.aclId','2022-01-03 10:26:54',13,33),(102,1,'core_module.aclId','2022-01-03 10:26:55',13,34),(103,1,'su_announcements.acl_id','2022-01-03 10:26:55',64,1),(104,1,'core_entity.defaultAclId','2022-01-03 10:26:55',NULL,NULL),(105,1,'su_announcements.acl_id','2022-01-03 10:26:55',64,2),(106,3,'fs_folders.acl_id','2022-01-03 10:26:55',32,26),(107,1,'pr2_types.acl_id','2022-01-03 10:26:56',53,2),(108,1,'pr2_types.acl_book','2022-01-03 10:26:56',NULL,NULL),(109,1,'bookmarks_category.aclId','2022-01-03 10:26:57',36,1),(110,1,'addressbook_addressbook.aclId','2022-01-03 10:27:00',23,3),(111,1,'addressbook_addressbook.aclId','2022-01-03 10:27:02',23,4),(112,1,'core_group.aclId','2022-01-03 10:27:04',11,8),(113,1,'core_group.aclId','2022-01-03 10:27:04',11,9),(114,1,'core_group.aclId','2022-01-03 10:27:06',11,10);
/*!40000 ALTER TABLE `core_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_acl_group`
--

DROP TABLE IF EXISTS `core_acl_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_acl_group` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL DEFAULT 0,
  `level` tinyint(4) NOT NULL DEFAULT 10,
  PRIMARY KEY (`aclId`,`groupId`),
  KEY `level` (`level`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `core_acl_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_acl_group_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl_group`
--

LOCK TABLES `core_acl_group` WRITE;
/*!40000 ALTER TABLE `core_acl_group` DISABLE KEYS */;
INSERT INTO `core_acl_group` VALUES (2,2,10),(3,3,10),(4,4,10),(5,2,10),(6,2,10),(7,2,10),(8,2,10),(9,3,10),(11,3,10),(17,3,10),(19,3,10),(21,3,10),(24,3,10),(25,3,10),(26,3,10),(27,3,10),(29,3,10),(32,3,10),(33,3,10),(51,3,10),(54,3,10),(55,3,10),(56,3,10),(57,3,10),(58,3,10),(66,2,10),(68,2,10),(69,3,10),(70,3,10),(71,3,10),(72,3,10),(73,3,10),(74,3,10),(75,3,10),(76,3,10),(77,3,10),(78,3,10),(84,5,10),(85,6,10),(86,7,10),(87,3,10),(88,3,10),(89,3,10),(90,3,10),(92,3,10),(93,3,10),(94,3,10),(100,2,10),(101,3,10),(102,3,10),(103,2,10),(105,2,10),(109,3,10),(111,3,10),(114,10,10),(34,5,30),(34,6,30),(41,5,30),(41,6,30),(46,5,30),(46,6,30),(52,3,30),(79,2,30),(81,2,30),(83,3,30),(107,3,30),(12,3,40),(23,3,40),(30,3,40),(93,5,40),(94,5,40),(16,3,50),(36,2,50),(38,2,50),(39,2,50),(40,2,50),(42,2,50),(43,2,50),(44,2,50),(45,2,50),(47,2,50),(48,2,50),(49,2,50),(50,2,50),(79,5,50),(79,6,50),(87,6,50),(88,5,50),(89,7,50),(97,6,50),(98,7,50),(99,5,50),(106,6,50);
/*!40000 ALTER TABLE `core_acl_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_acl_group_changes`
--

DROP TABLE IF EXISTS `core_acl_group_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_acl_group_changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`,`groupId`),
  KEY `group` (`groupId`),
  CONSTRAINT `all` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `group` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl_group_changes`
--

LOCK TABLES `core_acl_group_changes` WRITE;
/*!40000 ALTER TABLE `core_acl_group_changes` DISABLE KEYS */;
INSERT INTO `core_acl_group_changes` VALUES (1,2,2,0,NULL),(2,3,3,0,NULL),(3,4,4,0,NULL),(4,5,2,0,NULL),(5,6,2,0,NULL),(6,7,2,0,NULL),(7,8,2,0,NULL),(8,9,3,0,NULL),(9,11,3,0,NULL),(10,17,3,0,NULL),(11,19,3,0,NULL),(12,21,3,0,NULL),(13,24,3,0,NULL),(14,25,3,0,NULL),(15,26,3,0,NULL),(16,27,3,0,NULL),(17,29,3,0,NULL),(18,32,3,0,NULL),(19,33,3,0,NULL),(20,51,3,0,NULL),(21,54,3,0,NULL),(22,55,3,0,NULL),(23,56,3,0,NULL),(24,57,3,0,NULL),(25,58,3,0,NULL),(26,66,2,0,NULL),(27,68,2,0,NULL),(28,69,3,0,NULL),(29,70,3,0,NULL),(30,71,3,0,NULL),(31,72,3,0,NULL),(32,73,3,0,NULL),(33,74,3,0,NULL),(34,75,3,0,NULL),(35,76,3,0,NULL),(36,77,3,0,NULL),(37,78,3,0,NULL),(38,84,5,0,NULL),(39,85,6,0,NULL),(40,86,7,0,NULL),(41,87,3,0,NULL),(42,88,3,0,NULL),(43,89,3,0,NULL),(44,90,3,0,NULL),(45,92,3,0,NULL),(46,93,3,0,NULL),(47,94,3,0,NULL),(48,100,2,0,NULL),(49,101,3,0,NULL),(50,102,3,0,NULL),(51,103,2,0,NULL),(52,105,2,0,NULL),(53,109,3,0,NULL),(54,34,5,0,NULL),(55,34,6,0,NULL),(56,41,5,0,NULL),(57,41,6,0,NULL),(58,46,5,0,NULL),(59,46,6,0,NULL),(60,52,3,0,NULL),(61,79,2,0,NULL),(62,81,2,0,NULL),(63,83,3,0,NULL),(64,107,3,0,NULL),(65,12,3,0,NULL),(66,23,3,0,NULL),(67,30,3,0,NULL),(68,93,5,0,NULL),(69,94,5,0,NULL),(70,16,3,0,NULL),(71,36,2,0,NULL),(72,38,2,0,NULL),(73,39,2,0,NULL),(74,40,2,0,NULL),(75,42,2,0,NULL),(76,43,2,0,NULL),(77,44,2,0,NULL),(78,45,2,0,NULL),(79,47,2,0,NULL),(80,48,2,0,NULL),(81,49,2,0,NULL),(82,50,2,0,NULL),(83,79,5,0,NULL),(84,79,6,0,NULL),(85,87,6,0,NULL),(86,88,5,0,NULL),(87,89,7,0,NULL),(88,97,6,0,NULL),(89,98,7,0,NULL),(90,99,5,0,NULL),(91,106,6,0,NULL),(128,111,3,1,NULL),(131,114,10,4,NULL);
/*!40000 ALTER TABLE `core_acl_group_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_alert`
--

DROP TABLE IF EXISTS `core_alert`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_alert` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityTypeId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `triggerAt` datetime NOT NULL,
  `alertId` int(11) NOT NULL,
  `recurrenceId` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dk_alert_entityType_idx` (`entityTypeId`),
  KEY `fk_alert_user_idx` (`userId`),
  CONSTRAINT `fk_alert_entityType` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_alert_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_alert`
--

LOCK TABLES `core_alert` WRITE;
/*!40000 ALTER TABLE `core_alert` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_alert` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_auth_allow_group`
--

DROP TABLE IF EXISTS `core_auth_allow_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_auth_allow_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupId` int(11) NOT NULL,
  `ipPattern` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP Address. Wildcards can be used where * matches anything and ? matches exactly one character',
  PRIMARY KEY (`id`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `core_auth_allow_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_allow_group`
--

LOCK TABLES `core_auth_allow_group` WRITE;
/*!40000 ALTER TABLE `core_auth_allow_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_auth_allow_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_auth_method`
--

DROP TABLE IF EXISTS `core_auth_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_auth_method` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleId` int(11) NOT NULL,
  `sortOrder` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moduleId` (`moduleId`),
  KEY `moduleId_sortOrder` (`moduleId`,`sortOrder`),
  CONSTRAINT `core_auth_method_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_method`
--

LOCK TABLES `core_auth_method` WRITE;
/*!40000 ALTER TABLE `core_auth_method` DISABLE KEYS */;
INSERT INTO `core_auth_method` VALUES ('password',1,1),('googleauthenticator',10,2);
/*!40000 ALTER TABLE `core_auth_method` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_auth_password`
--

DROP TABLE IF EXISTS `core_auth_password`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_auth_password` (
  `userId` int(11) NOT NULL,
  `password` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`userId`),
  CONSTRAINT `core_auth_password_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_password`
--

LOCK TABLES `core_auth_password` WRITE;
/*!40000 ALTER TABLE `core_auth_password` DISABLE KEYS */;
INSERT INTO `core_auth_password` VALUES (1,'$2y$10$6NMB8JVg03h42MwwL5LWM.43LQ3FvkYZZeDcpYKCK5QWTz8LpI9Va'),(2,'$2y$10$zu.8w9S6ckjYcyFoWyLEhuW/2/Jhfp36OY1YJvDcmx10E9cGXUA9e'),(3,'$2y$10$TKzi4ssAk/9fPL9pmCjltO.vnjH/stIEqnU.c2f3j6LfyBQaG3x7C'),(4,'$2y$10$Iuxzj61E1Iet9XxldwlYLeJsTQOfgJlNxipM3o1MnrRf6GqPx38eK');
/*!40000 ALTER TABLE `core_auth_password` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_auth_token`
--

DROP TABLE IF EXISTS `core_auth_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_auth_token` (
  `loginToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `lastActiveAt` datetime NOT NULL,
  `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passedAuthenticators` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`loginToken`),
  KEY `userId` (`userId`),
  KEY `accessToken` (`accessToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_token`
--

LOCK TABLES `core_auth_token` WRITE;
/*!40000 ALTER TABLE `core_auth_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_auth_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_blob`
--

DROP TABLE IF EXISTS `core_blob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_blob` (
  `id` binary(40) NOT NULL,
  `type` varchar(129) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `staleAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staleAt` (`staleAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_blob`
--

LOCK TABLES `core_blob` WRITE;
/*!40000 ALTER TABLE `core_blob` DISABLE KEYS */;
INSERT INTO `core_blob` VALUES ('0ec2f1f4f9fb41e8013fcc834991be30a8260750','image/jpeg',27858,'elmer.jpg','2022-01-03 10:26:40','2022-01-03 10:26:41',NULL),('34a992551b8a27a200edc3a563fdf2e946183b47','image/png',39495,'wecoyote.png','2022-01-03 10:26:40','2020-10-01 13:58:25','2022-01-03 11:26:40'),('a277a250ad9fa623fd0c1c9bdbfb5804981d14e4','image/x-icon',171,'www_group-office_com.ico','2022-01-03 10:26:57','2022-01-03 10:26:57',NULL),('a2b13489e9762bf7d7dfd63d72d45f0f47411c30','image/png',31669,'male.png','2022-01-03 10:26:40','2022-01-03 10:26:43',NULL),('b82d0979d555bd137b33c15021129e06cbeea59a','image/x-icon',492,'www_intermesh_nl.ico','2022-01-03 10:26:57','2022-01-03 10:26:57',NULL),('c363a83f50fe2fbe94deff31afee36d8d7923e17','image/png',57187,'female.png','2022-01-03 10:26:40','2022-01-03 10:26:43',NULL);
/*!40000 ALTER TABLE `core_blob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_change`
--

DROP TABLE IF EXISTS `core_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_change` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `destroyed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `entityTypeId` (`entityTypeId`),
  KEY `entityId` (`entityId`),
  CONSTRAINT `core_change_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_change`
--

LOCK TABLES `core_change` WRITE;
/*!40000 ALTER TABLE `core_change` DISABLE KEYS */;
INSERT INTO `core_change` VALUES (1,3,23,1,110,'2022-01-03 10:27:00',0),(2,77,17,1,110,'2022-01-03 10:27:00',0),(3,5,24,1,110,'2022-01-03 10:27:00',0),(4,78,17,2,110,'2022-01-03 10:27:00',0),(5,6,24,2,110,'2022-01-03 10:27:00',0),(6,105,12,1,110,'2022-01-03 10:27:01',0),(7,6,24,3,110,'2022-01-03 10:27:01',0),(8,5,24,4,110,'2022-01-03 10:27:01',0),(9,4,23,2,111,'2022-01-03 10:27:02',0),(10,29,43,1,31,'2022-01-03 10:27:02',0),(11,30,43,2,31,'2022-01-03 10:27:02',0),(12,5,21,1,5,'2022-01-03 10:27:03',0),(13,2,11,1,2,'2022-01-03 10:27:04',0),(14,3,11,1,3,'2022-01-03 10:27:04',0),(16,8,11,2,112,'2022-01-03 10:27:04',0),(17,5,21,2,NULL,'2022-01-03 10:27:04',0),(18,6,21,3,5,'2022-01-03 10:27:04',0),(19,2,11,3,2,'2022-01-03 10:27:04',0),(20,3,11,3,3,'2022-01-03 10:27:04',0),(22,9,11,4,113,'2022-01-03 10:27:04',0),(23,6,21,4,NULL,'2022-01-03 10:27:04',0),(24,8,11,6,112,'2022-01-03 10:27:05',0),(25,9,11,6,113,'2022-01-03 10:27:05',0),(27,5,21,9,NULL,'2022-01-03 10:27:05',1),(28,6,21,9,NULL,'2022-01-03 10:27:05',1),(30,1,43,4,NULL,'2022-01-03 10:27:06',1),(31,29,43,4,NULL,'2022-01-03 10:27:06',1),(32,30,43,4,NULL,'2022-01-03 10:27:06',1),(33,31,43,5,31,'2022-01-03 10:27:06',0),(34,32,43,6,31,'2022-01-03 10:27:06',0),(35,33,42,1,31,'2022-01-03 10:27:06',0),(36,34,42,2,31,'2022-01-03 10:27:06',0),(37,35,42,3,31,'2022-01-03 10:27:06',0),(38,33,42,4,31,'2022-01-03 10:27:06',0),(39,33,42,5,31,'2022-01-03 10:27:06',0),(40,33,42,6,31,'2022-01-03 10:27:06',0),(41,36,42,7,31,'2022-01-03 10:27:06',0),(42,37,42,8,31,'2022-01-03 10:27:06',0),(43,38,42,9,31,'2022-01-03 10:27:06',0),(44,36,42,10,31,'2022-01-03 10:27:06',0),(45,36,42,11,31,'2022-01-03 10:27:06',0),(46,36,42,12,31,'2022-01-03 10:27:06',0),(47,39,43,13,31,'2022-01-03 10:27:06',0),(48,31,43,14,31,'2022-01-03 10:27:06',0),(49,31,43,15,31,'2022-01-03 10:27:06',0),(50,88,44,1,31,'2022-01-03 10:27:06',0),(51,31,43,16,31,'2022-01-03 10:27:06',0),(52,40,43,17,31,'2022-01-03 10:27:06',0),(53,10,11,7,114,'2022-01-03 10:27:06',0),(54,1,21,10,5,'2022-01-03 10:27:06',0),(55,10,11,8,114,'2022-01-03 10:27:06',0),(56,1,21,11,5,'2022-01-03 10:27:06',0),(57,10,11,9,114,'2022-01-03 10:27:06',0),(58,79,17,3,110,'2022-01-03 10:27:06',0),(59,7,24,12,110,'2022-01-03 10:27:06',0),(60,7,24,13,110,'2022-01-03 10:27:06',0),(61,80,17,4,110,'2022-01-03 10:27:06',0),(62,8,24,14,110,'2022-01-03 10:27:06',0),(63,2,24,15,83,'2022-01-03 10:27:06',0),(64,2,12,4,83,'2022-01-03 10:27:06',1),(65,56,12,4,34,'2022-01-03 10:27:06',1),(66,76,12,4,41,'2022-01-03 10:27:06',1),(67,84,12,4,46,'2022-01-03 10:27:06',1),(68,62,12,4,95,'2022-01-03 10:27:06',1),(71,1,24,16,83,'2022-01-03 10:27:06',1),(72,41,43,18,31,'2022-01-03 10:27:16',0);
/*!40000 ALTER TABLE `core_change` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_change_user`
--

DROP TABLE IF EXISTS `core_change_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_change_user` (
  `userId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`entityId`,`entityTypeId`),
  KEY `entityTypeId` (`entityTypeId`),
  CONSTRAINT `core_change_user_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_change_user_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_change_user`
--

LOCK TABLES `core_change_user` WRITE;
/*!40000 ALTER TABLE `core_change_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_change_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_change_user_modseq`
--

DROP TABLE IF EXISTS `core_change_user_modseq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_change_user_modseq` (
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `highestModSeq` int(11) NOT NULL DEFAULT 0,
  `lowestModSeq` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userId`,`entityTypeId`),
  KEY `entityTypeId` (`entityTypeId`),
  CONSTRAINT `core_change_user_modseq_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_change_user_modseq_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_change_user_modseq`
--

LOCK TABLES `core_change_user_modseq` WRITE;
/*!40000 ALTER TABLE `core_change_user_modseq` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_change_user_modseq` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_cron_job`
--

DROP TABLE IF EXISTS `core_cron_job`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_cron_job` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `nextRunAt` datetime DEFAULT NULL,
  `lastRunAt` datetime DEFAULT NULL,
  `runningSince` datetime DEFAULT NULL,
  `lastError` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`),
  KEY `moduleId` (`moduleId`),
  CONSTRAINT `core_cron_job_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_cron_job`
--

LOCK TABLES `core_cron_job` WRITE;
/*!40000 ALTER TABLE `core_cron_job` DISABLE KEYS */;
INSERT INTO `core_cron_job` VALUES (1,1,'Garbage collection','GarbageCollection','0 0 * * *',1,'2022-01-04 00:00:00',NULL,NULL,NULL),(2,4,'Newsletter mailer','Mailer','* * * * *',1,'2022-01-03 10:27:00',NULL,NULL,NULL);
/*!40000 ALTER TABLE `core_cron_job` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_customfields_field`
--

DROP TABLE IF EXISTS `core_customfields_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_customfields_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldSetId` int(11) NOT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `databaseName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Text',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `relatedFieldCondition` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditionallyHidden` tinyint(1) NOT NULL DEFAULT 0,
  `conditionallyRequired` tinyint(1) NOT NULL DEFAULT 0,
  `hint` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT 0,
  `unique_values` tinyint(1) NOT NULL DEFAULT 0,
  `prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hiddenInGrid` tinyint(1) NOT NULL DEFAULT 1,
  `filterable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type` (`fieldSetId`),
  KEY `modSeq` (`modSeq`),
  CONSTRAINT `core_customfields_field_ibfk_1` FOREIGN KEY (`fieldSetId`) REFERENCES `core_customfields_field_set` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field`
--

LOCK TABLES `core_customfields_field` WRITE;
/*!40000 ALTER TABLE `core_customfields_field` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_customfields_field` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_customfields_field_set`
--

DROP TABLE IF EXISTS `core_customfields_field_set`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_customfields_field_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `modSeq` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `entityId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sortOrder` tinyint(4) NOT NULL DEFAULT 0,
  `filter` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isTab` tinyint(1) NOT NULL DEFAULT 0,
  `columns` tinyint(4) NOT NULL DEFAULT 2,
  PRIMARY KEY (`id`),
  KEY `entityId` (`entityId`),
  KEY `aclId` (`aclId`),
  KEY `modSeq` (`modSeq`),
  CONSTRAINT `core_customfields_field_set_ibfk_1` FOREIGN KEY (`entityId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_customfields_field_set_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field_set`
--

LOCK TABLES `core_customfields_field_set` WRITE;
/*!40000 ALTER TABLE `core_customfields_field_set` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_customfields_field_set` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_customfields_select_option`
--

DROP TABLE IF EXISTS `core_customfields_select_option`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_customfields_select_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldId` int(11) NOT NULL,
  `parentId` int(11) DEFAULT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sortOrder` int(11) unsigned DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `field_id` (`fieldId`),
  KEY `parentId` (`parentId`),
  CONSTRAINT `core_customfields_select_option_ibfk_1` FOREIGN KEY (`fieldId`) REFERENCES `core_customfields_field` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_customfields_select_option_ibfk_2` FOREIGN KEY (`fieldId`) REFERENCES `core_customfields_field` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_customfields_select_option_ibfk_3` FOREIGN KEY (`parentId`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_select_option`
--

LOCK TABLES `core_customfields_select_option` WRITE;
/*!40000 ALTER TABLE `core_customfields_select_option` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_customfields_select_option` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_email_template`
--

DROP TABLE IF EXISTS `core_email_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_email_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `aclId` (`aclId`),
  KEY `moduleId` (`moduleId`),
  CONSTRAINT `core_email_template_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `core_email_template_ibfk_2` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_email_template`
--

LOCK TABLES `core_email_template` WRITE;
/*!40000 ALTER TABLE `core_email_template` DISABLE KEYS */;
INSERT INTO `core_email_template` VALUES (1,4,15,'Default','Hi {{contact.firstName}}','Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if] {{contact.lastName}},<div><div><br></div><div><br></div><div>Best regards,</div><div><br></div><div>{{creator.displayName}}</div></div><div>{{creator.profile.organizations[0].name}}</div><div><br /></div><div><a href=\"{{unsubscribeUrl}}\">unsubscribe</a></div>');
/*!40000 ALTER TABLE `core_email_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_email_template_attachment`
--

DROP TABLE IF EXISTS `core_email_template_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_email_template_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emailTemplateId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inline` tinyint(1) NOT NULL DEFAULT 0,
  `attachment` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `templateId` (`emailTemplateId`),
  KEY `blobId` (`blobId`),
  CONSTRAINT `core_email_template_attachment_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `core_email_template_attachment_ibfk_2` FOREIGN KEY (`emailTemplateId`) REFERENCES `core_email_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_email_template_attachment`
--

LOCK TABLES `core_email_template_attachment` WRITE;
/*!40000 ALTER TABLE `core_email_template_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_email_template_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_entity`
--

DROP TABLE IF EXISTS `core_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clientName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `highestModSeq` int(11) NOT NULL DEFAULT 0,
  `defaultAclId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientName` (`clientName`),
  UNIQUE KEY `name` (`name`,`moduleId`) USING BTREE,
  KEY `moduleId` (`moduleId`),
  KEY `defaultAclId` (`defaultAclId`),
  CONSTRAINT `core_entity_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_entity_ibfk_2` FOREIGN KEY (`defaultAclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_entity`
--

LOCK TABLES `core_entity` WRITE;
/*!40000 ALTER TABLE `core_entity` DISABLE KEYS */;
INSERT INTO `core_entity` VALUES (1,1,'Method','Method',0,NULL),(2,1,'Blob','Blob',0,NULL),(3,1,'Acl','Acl',4,NULL),(4,1,'Alert','Alert',0,NULL),(5,1,'AuthAllowGroup','AuthAllowGroup',1,NULL),(6,1,'CronJobSchedule','CronJobSchedule',0,NULL),(7,1,'EmailTemplate','EmailTemplate',0,NULL),(8,1,'EntityFilter','EntityFilter',0,6),(9,1,'Field','Field',0,NULL),(10,1,'FieldSet','FieldSet',0,7),(11,1,'Group','Group',9,8),(12,1,'Link','Link',4,NULL),(13,1,'Module','Module',0,18),(14,1,'OauthAccessToken','OauthAccessToken',0,NULL),(15,1,'OauthAuthCode','OauthAuthCode',0,NULL),(16,1,'OauthClient','OauthClient',0,NULL),(17,1,'Search','Search',4,NULL),(18,1,'SmtpAccount','SmtpAccount',0,NULL),(19,1,'SpreadSheetExport','SpreadSheetExport',1,NULL),(20,1,'Token','Token',0,NULL),(21,1,'User','User',11,NULL),(22,1,'Template','Template',0,10),(23,2,'AddressBook','AddressBook',4,NULL),(24,2,'Contact','Contact',16,NULL),(25,2,'Group','AddressBookGroup',1,NULL),(26,3,'Activity','Activity',0,NULL),(27,3,'Business','Business',2,NULL),(28,3,'EmployeeAgreement','EmployeeAgreement',0,NULL),(29,4,'AddressList','AddressList',0,NULL),(30,4,'Newsletter','Newsletter',1,NULL),(31,6,'File','File',0,NULL),(32,6,'Folder','Folder',0,NULL),(33,6,'Template','FilesTemplate',0,20),(34,7,'Service','WopiService',0,NULL),(35,8,'Bookmark','Bookmark',1,NULL),(36,8,'Category','BookmarksCategory',1,NULL),(37,9,'Comment','Comment',2,NULL),(38,9,'Label','CommentLabel',0,NULL),(39,11,'LogEntry','LogEntry',1,NULL),(40,12,'Note','Note',4,NULL),(41,12,'NoteBook','NoteBook',2,NULL),(42,13,'A','A',12,NULL),(43,13,'B','B',18,NULL),(44,13,'C','C',1,NULL),(45,15,'Order','Order',0,NULL),(46,15,'Product','Product',0,NULL),(47,15,'Book','Book',0,35),(48,15,'OrderStatus','OrderStatus',0,37),(49,16,'Calendar','Calendar',0,52),(50,16,'Event','Event',0,NULL),(51,21,'Project','Project',0,NULL),(52,21,'TimeEntry','TimeEntry',0,NULL),(53,21,'Type','ProjectType',0,60),(54,21,'Status','ProjectStatus',0,63),(55,21,'Template','ProjectTemplate',0,67),(56,26,'LinkedEmail','LinkedEmail',0,NULL),(57,30,'Task','Task',0,NULL),(58,31,'Ticket','Ticket',0,NULL),(59,31,'Type','TicketType',0,80),(60,16,'View','View',0,91),(61,30,'Tasklist','Tasklist',0,96),(62,33,'Content','Content',0,NULL),(63,33,'Site','Site',0,NULL),(64,28,'Announcement','Announcement',0,104);
/*!40000 ALTER TABLE `core_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_entity_filter`
--

DROP TABLE IF EXISTS `core_entity_filter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_entity_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityTypeId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `filter` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `type` enum('fixed','variable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  PRIMARY KEY (`id`),
  KEY `aclid` (`aclId`),
  KEY `createdBy` (`createdBy`),
  KEY `entityTypeId` (`entityTypeId`),
  CONSTRAINT `core_entity_filter_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `core_entity_filter_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_entity_filter`
--

LOCK TABLES `core_entity_filter` WRITE;
/*!40000 ALTER TABLE `core_entity_filter` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_entity_filter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_group`
--

DROP TABLE IF EXISTS `core_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `isUserGroupFor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `isUserGroupFor` (`isUserGroupFor`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `core_group_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `core_group_ibfk_2` FOREIGN KEY (`isUserGroupFor`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_group`
--

LOCK TABLES `core_group` WRITE;
/*!40000 ALTER TABLE `core_group` DISABLE KEYS */;
INSERT INTO `core_group` VALUES (1,'Admins',1,1,NULL),(2,'Everyone',1,2,NULL),(3,'Internal',1,3,NULL),(4,'admin',1,4,1),(5,'elmer',1,84,2),(6,'demo',1,85,3),(7,'linda',1,86,4),(10,'61d2cf7a8658c',1,114,NULL);
/*!40000 ALTER TABLE `core_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_group_default_group`
--

DROP TABLE IF EXISTS `core_group_default_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_group_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`),
  CONSTRAINT `core_group_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_group_default_group`
--

LOCK TABLES `core_group_default_group` WRITE;
/*!40000 ALTER TABLE `core_group_default_group` DISABLE KEYS */;
INSERT INTO `core_group_default_group` VALUES (3);
/*!40000 ALTER TABLE `core_group_default_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_link`
--

DROP TABLE IF EXISTS `core_link`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_link` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fromEntityTypeId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toEntityTypeId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `folderId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fromEntityId` (`fromEntityTypeId`,`fromId`,`toEntityTypeId`,`toId`) USING BTREE,
  KEY `toEntity` (`toEntityTypeId`),
  KEY `fromEntityTypeId` (`fromEntityTypeId`),
  KEY `fromId` (`fromId`),
  KEY `toEntityTypeId` (`toEntityTypeId`),
  KEY `toId` (`toId`),
  CONSTRAINT `fromEntity` FOREIGN KEY (`fromEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `toEntity` FOREIGN KEY (`toEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_link`
--

LOCK TABLES `core_link` WRITE;
/*!40000 ALTER TABLE `core_link` DISABLE KEYS */;
INSERT INTO `core_link` VALUES (3,24,4,24,3,NULL,'2022-01-03 10:26:45',NULL,NULL,NULL),(4,24,3,24,4,NULL,'2022-01-03 10:26:45',NULL,NULL,NULL),(5,50,1,24,4,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(6,24,4,50,1,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(7,50,1,24,2,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(8,24,2,50,1,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(9,50,3,24,4,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(10,24,4,50,3,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(11,50,3,24,2,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(12,24,2,50,3,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(13,50,5,24,4,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(14,24,4,50,5,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(15,50,5,24,2,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(16,24,2,50,5,NULL,'2022-01-03 10:26:46',NULL,NULL,NULL),(17,50,7,24,4,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(18,24,4,50,7,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(19,50,7,24,2,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(20,24,2,50,7,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(21,50,8,24,4,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(22,24,4,50,8,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(23,50,8,24,2,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(24,24,2,50,8,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(25,50,9,24,4,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(26,24,4,50,9,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(27,50,9,24,2,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(28,24,2,50,9,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(29,50,10,24,4,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(30,24,4,50,10,NULL,'2022-01-03 10:26:47',NULL,NULL,NULL),(31,50,10,24,2,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(32,24,2,50,10,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(33,50,11,24,4,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(34,24,4,50,11,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(35,50,11,24,2,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(36,24,2,50,11,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(37,50,12,24,4,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(38,24,4,50,12,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(39,50,12,24,2,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(40,24,2,50,12,NULL,'2022-01-03 10:26:48',NULL,NULL,NULL),(41,57,4,24,4,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(42,24,4,57,4,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(43,57,4,50,12,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(44,50,12,57,4,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(45,57,5,24,4,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(46,24,4,57,5,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(47,57,5,50,12,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(48,50,12,57,5,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(49,57,6,24,4,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(50,24,4,57,6,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(51,57,6,50,12,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(52,50,12,57,6,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(53,45,1,24,2,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(54,24,2,45,1,NULL,'2022-01-03 10:26:50',NULL,NULL,NULL),(57,45,1,57,7,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(58,57,7,45,1,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(59,57,7,24,2,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(60,24,2,57,7,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(63,45,2,24,4,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(64,24,4,45,2,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(65,45,2,24,3,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(66,24,3,45,2,NULL,'2022-01-03 10:26:51',NULL,NULL,NULL),(67,45,2,57,8,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(68,57,8,45,2,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(69,57,8,24,4,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(70,24,4,57,8,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(71,57,8,24,3,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(72,24,3,57,8,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(73,45,3,24,2,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(74,24,2,45,3,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(77,45,4,24,4,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(78,24,4,45,4,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(79,45,4,24,3,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(80,24,3,45,4,NULL,'2022-01-03 10:26:52',NULL,NULL,NULL),(81,45,5,24,2,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(82,24,2,45,5,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(85,45,6,24,4,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(86,24,4,45,6,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(87,45,6,24,3,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(88,24,3,45,6,NULL,'2022-01-03 10:26:53',NULL,NULL,NULL),(89,51,2,24,3,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(90,24,3,51,2,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(91,51,2,24,4,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(92,24,4,51,2,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(93,51,3,24,3,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(94,24,3,51,3,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(95,51,3,24,4,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(96,24,4,51,3,NULL,'2022-01-03 10:26:56',NULL,NULL,NULL),(97,56,1,24,4,NULL,'2022-01-03 10:26:57',NULL,NULL,NULL),(98,24,4,56,1,NULL,'2022-01-03 10:26:57',NULL,NULL,NULL),(99,56,2,24,2,NULL,'2022-01-03 10:26:57',NULL,NULL,NULL),(100,24,2,56,2,NULL,'2022-01-03 10:26:57',NULL,NULL,NULL),(101,56,3,24,4,NULL,'2022-01-03 10:26:58',NULL,NULL,NULL),(102,24,4,56,3,NULL,'2022-01-03 10:26:58',NULL,NULL,NULL),(103,56,4,24,2,NULL,'2022-01-03 10:26:58',NULL,NULL,NULL),(104,24,2,56,4,NULL,'2022-01-03 10:26:58',NULL,NULL,NULL),(105,24,5,24,6,NULL,'2022-01-03 10:27:01',NULL,NULL,NULL),(106,24,6,24,5,NULL,'2022-01-03 10:27:01',NULL,NULL,NULL);
/*!40000 ALTER TABLE `core_link` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_module`
--

DROP TABLE IF EXISTS `core_module`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `package` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `admin_menu` tinyint(1) NOT NULL DEFAULT 0,
  `aclId` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `modifiedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `acl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_module`
--

LOCK TABLES `core_module` WRITE;
/*!40000 ALTER TABLE `core_module` DISABLE KEYS */;
INSERT INTO `core_module` VALUES (1,'core','core',246,0,0,5,1,NULL,NULL,NULL),(2,'addressbook','community',67,101,0,11,1,NULL,NULL,NULL),(3,'business','business',31,102,0,13,1,NULL,NULL,NULL),(4,'newsletters','business',2,103,0,14,1,NULL,NULL,NULL),(5,'onlyoffice','business',2,104,0,16,1,NULL,NULL,NULL),(6,'files',NULL,140,105,0,17,1,'2022-01-03 10:26:18',NULL,NULL),(7,'wopi','business',7,105,0,24,1,NULL,NULL,NULL),(8,'bookmarks','community',11,106,0,25,1,NULL,NULL,NULL),(9,'comments','community',27,107,0,26,1,NULL,NULL,NULL),(10,'googleauthenticator','community',3,108,0,27,1,NULL,NULL,NULL),(11,'history','community',5,109,0,28,1,NULL,NULL,NULL),(12,'notes','community',57,110,0,29,1,NULL,NULL,NULL),(13,'test','community',0,111,0,31,1,NULL,NULL,NULL),(14,'assistant',NULL,0,112,0,32,1,'2022-01-03 10:26:25',NULL,NULL),(15,'billing',NULL,319,112,0,33,1,'2022-01-03 10:26:26',NULL,NULL),(16,'calendar',NULL,184,112,0,51,1,'2022-01-03 10:26:30',NULL,NULL),(17,'cron',NULL,0,112,1,53,1,'2022-01-03 10:26:31',NULL,NULL),(19,'documenttemplates',NULL,0,112,0,55,1,'2022-01-03 10:26:31',NULL,NULL),(20,'email',NULL,104,112,0,57,1,'2022-01-03 10:26:31',NULL,NULL),(21,'projects2',NULL,401,112,0,58,1,'2022-01-03 10:26:32',NULL,NULL),(22,'timeregistration2',NULL,1,112,0,69,1,'2022-01-03 10:26:36',NULL,NULL),(23,'hoursapproval2',NULL,0,112,0,70,1,'2022-01-03 10:26:36',NULL,NULL),(24,'jitsimeet',NULL,0,112,0,71,1,'2022-01-03 10:26:36',NULL,NULL),(25,'leavedays',NULL,37,112,0,72,1,'2022-01-03 10:26:36',NULL,NULL),(26,'savemailas',NULL,14,112,0,73,1,'2022-01-03 10:26:37',NULL,NULL),(27,'sieve',NULL,0,112,0,74,1,'2022-01-03 10:26:37',NULL,NULL),(28,'summary',NULL,31,112,0,75,1,'2022-01-03 10:26:37',NULL,NULL),(29,'sync',NULL,50,112,0,76,1,'2022-01-03 10:26:37',NULL,NULL),(30,'tasks',NULL,60,112,0,77,1,'2022-01-03 10:26:38',NULL,NULL),(31,'tickets',NULL,165,112,0,78,1,'2022-01-03 10:26:38',NULL,NULL),(32,'tools',NULL,0,112,1,82,1,'2022-01-03 10:26:39',NULL,NULL),(33,'site',NULL,18,112,0,101,1,'2022-01-03 10:26:54',NULL,NULL),(34,'defaultsite',NULL,0,112,0,102,1,'2022-01-03 10:26:55',NULL,NULL);
/*!40000 ALTER TABLE `core_module` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_oauth_access_token`
--

DROP TABLE IF EXISTS `core_oauth_access_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_oauth_access_token` (
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expiryDateTime` datetime DEFAULT NULL,
  `userIdentifier` int(11) NOT NULL,
  `clientId` int(11) NOT NULL,
  PRIMARY KEY (`identifier`),
  KEY `userIdentifier` (`userIdentifier`),
  KEY `clientId` (`clientId`),
  CONSTRAINT `core_oauth_access_token_ibfk_2` FOREIGN KEY (`userIdentifier`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_oauth_access_token_ibfk_3` FOREIGN KEY (`clientId`) REFERENCES `core_oauth_client` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_oauth_access_token`
--

LOCK TABLES `core_oauth_access_token` WRITE;
/*!40000 ALTER TABLE `core_oauth_access_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_oauth_access_token` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_oauth_auth_codes`
--

DROP TABLE IF EXISTS `core_oauth_auth_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_oauth_auth_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clientId` int(11) NOT NULL,
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userIdentifier` int(11) NOT NULL,
  `expiryDateTime` datetime NOT NULL,
  `nonce` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_oauth_auth_codes`
--

LOCK TABLES `core_oauth_auth_codes` WRITE;
/*!40000 ALTER TABLE `core_oauth_auth_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_oauth_auth_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_oauth_client`
--

DROP TABLE IF EXISTS `core_oauth_client`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_oauth_client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `isConfidential` tinyint(1) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirectUri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(128) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_oauth_client`
--

LOCK TABLES `core_oauth_client` WRITE;
/*!40000 ALTER TABLE `core_oauth_client` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_oauth_client` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_pdf_block`
--

DROP TABLE IF EXISTS `core_pdf_block`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_pdf_block` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `pdfTemplateId` bigint(20) unsigned NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `align` enum('L','C','R','J') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'L',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  UNIQUE KEY `id` (`id`),
  KEY `pdfTemplateId` (`pdfTemplateId`),
  CONSTRAINT `core_pdf_block_ibfk_1` FOREIGN KEY (`pdfTemplateId`) REFERENCES `core_pdf_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_pdf_block`
--

LOCK TABLES `core_pdf_block` WRITE;
/*!40000 ALTER TABLE `core_pdf_block` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_pdf_block` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_pdf_template`
--

DROP TABLE IF EXISTS `core_pdf_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_pdf_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stationaryBlobId` binary(40) DEFAULT NULL,
  `landscape` tinyint(1) NOT NULL DEFAULT 0,
  `pageSize` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A4',
  `measureUnit` enum('mm','pt','cm','in') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'mm',
  `marginTop` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginRight` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginBottom` decimal(19,4) NOT NULL DEFAULT 10.0000,
  `marginLeft` decimal(19,4) NOT NULL DEFAULT 10.0000,
  UNIQUE KEY `id` (`id`),
  KEY `moduleId` (`moduleId`),
  KEY `stationaryBlobId` (`stationaryBlobId`),
  CONSTRAINT `core_pdf_template_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_pdf_template_ibfk_2` FOREIGN KEY (`stationaryBlobId`) REFERENCES `core_blob` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_pdf_template`
--

LOCK TABLES `core_pdf_template` WRITE;
/*!40000 ALTER TABLE `core_pdf_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_pdf_template` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_search`
--

DROP TABLE IF EXISTS `core_search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityId` int(11) NOT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `entityTypeId` int(11) NOT NULL,
  `filter` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityId` (`entityId`,`entityTypeId`),
  KEY `acl_id` (`aclId`),
  KEY `moduleId` (`moduleId`),
  KEY `entityTypeId` (`entityTypeId`),
  KEY `core_search_entityTypeId_filter_modifiedAt_aclId_index` (`entityTypeId`,`filter`,`modifiedAt`,`aclId`),
  CONSTRAINT `core_search_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_search_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_search`
--

LOCK TABLES `core_search` WRITE;
/*!40000 ALTER TABLE `core_search` DISABLE KEYS */;
INSERT INTO `core_search` VALUES (1,1,6,'users','users',32,NULL,'2022-01-03 10:26:55',17),(2,2,6,'admin','users/admin',32,NULL,'2021-12-23 11:28:42',22),(3,3,6,'Public','users/admin/Public',32,NULL,'2022-01-03 10:26:20',23),(4,4,6,'billing','billing',32,NULL,'2022-01-03 10:26:30',17),(5,5,6,'Quotes','billing/Quotes',32,NULL,'2022-01-03 10:26:28',34),(6,6,6,'Orders','billing/Orders',32,NULL,'2022-01-03 10:26:29',41),(7,7,6,'Invoices','billing/Invoices',32,NULL,'2022-01-03 10:26:29',46),(8,8,6,'product_images','billing/product_images',32,NULL,'2022-01-03 10:26:30',33),(9,9,6,'project_templates','project_templates',32,NULL,'2022-01-03 10:26:36',17),(10,10,6,'Projects folder','project_templates/Projects folder',32,NULL,'2022-01-03 10:26:35',66),(11,11,6,'Standard project','project_templates/Standard project',32,NULL,'2022-01-03 10:26:36',68),(12,12,6,'tickets','tickets',32,NULL,'2022-01-03 10:26:39',17),(14,2,2,'John Smith','Customers - CEO - Smith Inc.',24,'isContact','2022-01-03 10:26:44',83),(15,3,2,'ACME Corporation','Customers',24,'isOrganization','2022-01-03 10:26:51',83),(16,4,2,'Wile E. Coyote','Customers - CEO - ACME Corporation',24,'isContact','2022-01-03 10:26:45',83),(17,15,6,'calendar','calendar',32,NULL,'2021-12-21 13:13:20',17),(18,16,6,'Demo User','calendar/Demo User',32,NULL,'2022-01-03 10:26:45',87),(19,17,6,'Elmer Fudd','calendar/Elmer Fudd',32,NULL,'2022-01-03 10:26:45',88),(20,18,6,'Linda Smith','calendar/Linda Smith',32,NULL,'2022-01-03 10:26:46',89),(21,1,16,'Project meeting','Demo User',50,NULL,'2022-01-04 10:00:00',87),(22,2,16,'Project meeting','Linda Smith',50,NULL,'2022-01-04 10:00:00',89),(23,3,16,'Meet Wile','Demo User',50,NULL,'2022-01-04 12:00:00',87),(24,4,16,'Meet Wile','Linda Smith',50,NULL,'2022-01-04 12:00:00',89),(25,5,16,'MT Meeting','Demo User',50,NULL,'2022-01-04 14:00:00',87),(26,6,16,'MT Meeting','Linda Smith',50,NULL,'2022-01-04 14:00:00',89),(27,7,16,'Project meeting','Linda Smith',50,NULL,'2022-01-03 10:26:47',89),(28,8,16,'Meet John','Linda Smith',50,NULL,'2022-01-03 10:26:47',89),(29,9,16,'MT Meeting','Linda Smith',50,NULL,'2022-01-03 10:26:47',89),(30,10,16,'Rocket testing','Linda Smith',50,NULL,'2022-01-03 10:26:47',89),(31,11,16,'Blast impact test','Linda Smith',50,NULL,'2022-01-03 10:26:48',89),(32,12,16,'Test range extender','Linda Smith',50,NULL,'2022-01-03 10:26:48',89),(33,19,6,'Road Runner Room','calendar/Road Runner Room',32,NULL,'2022-01-03 10:26:48',93),(34,20,6,'Don Coyote Room','calendar/Don Coyote Room',32,NULL,'2022-01-03 10:26:49',94),(35,21,6,'tasks','tasks',32,NULL,'2022-01-03 10:26:50',17),(36,22,6,'System Administrator','tasks/System Administrator',32,NULL,'2022-01-03 10:26:49',95),(37,23,6,'Demo User','tasks/Demo User',32,NULL,'2022-01-03 10:26:49',97),(38,1,30,'Feed the dog','Demo User',57,NULL,'2022-01-05 10:26:49',97),(39,24,6,'Linda Smith','tasks/Linda Smith',32,NULL,'2022-01-03 10:26:49',98),(40,2,30,'Feed the dog','Linda Smith',57,NULL,'2022-01-04 10:26:49',98),(41,25,6,'Elmer Fudd','tasks/Elmer Fudd',32,NULL,'2022-01-03 10:26:50',99),(42,3,30,'Feed the dog','Elmer Fudd',57,NULL,'2022-01-04 10:26:50',99),(43,4,30,'Prepare meeting','Demo User',57,NULL,'2022-01-04 10:26:50',97),(44,5,30,'Prepare meeting','Linda Smith',57,NULL,'2022-01-04 10:26:50',98),(45,6,30,'Prepare meeting','Elmer Fudd',57,NULL,'2022-01-04 10:26:50',99),(46,1,15,'Q22000001','Smith Inc.',45,NULL,'2022-01-03 10:26:51',34),(47,7,30,'Call: Smith Inc. (Q22000001)','System Administrator',57,NULL,'2022-01-07 10:26:51',95),(48,2,15,'Q22000002','ACME Corporation',45,NULL,'2022-01-03 10:26:52',34),(49,8,30,'Call: ACME Corporation (Q22000002)','System Administrator',57,NULL,'2022-01-07 10:26:51',95),(50,3,15,'O22000001','Smith Inc.',45,NULL,'2022-01-03 10:26:52',41),(51,4,15,'O22000002','ACME Corporation',45,NULL,'2022-01-03 10:26:53',41),(52,5,15,'I22000001','Smith Inc.',45,NULL,'2022-01-03 10:26:53',46),(53,6,15,'I22000002','ACME Corporation',45,NULL,'2022-01-03 10:26:54',46),(54,1,31,'Malfunctioning rockets','Wile E. Coyote (ACME Corporation)',58,NULL,'2022-01-03 10:26:54',79),(55,2,31,'Can I speed up my rockets?','Wile E. Coyote (ACME Corporation)',58,NULL,'2022-01-03 10:26:54',79),(56,26,6,'demo','users/demo',32,NULL,'2022-01-03 10:26:56',106),(57,27,6,'empty.docx.sb-951a9b4f-dxxJjl','users/demo/empty.docx.sb-951a9b4f-dxxJjl',32,NULL,'2021-10-04 12:10:28',106),(58,1,6,'noperson.jpg','users/demo/noperson.jpg',31,NULL,'2022-01-03 10:26:55',106),(59,2,6,'Demo letter.docx','users/demo/Demo letter.docx',31,NULL,'2022-01-03 10:26:55',106),(60,28,6,'empty.docx.sb-951a9b4f-pwSOuu','users/demo/empty.docx.sb-951a9b4f-pwSOuu',32,NULL,'2021-10-04 12:10:49',106),(61,29,6,'empty.docx.sb-951a9b4f-UZmMa7','users/demo/empty.docx.sb-951a9b4f-UZmMa7',32,NULL,'2021-10-04 12:10:16',106),(62,3,6,'wecoyote.png','users/demo/wecoyote.png',31,NULL,'2022-01-03 10:26:55',106),(63,4,6,'Vraagouder incasso - 2021-10-03T222240.229.xls','users/demo/Vraagouder incasso - 2021-10-03T222240.229.xls',31,NULL,'2021-10-04 11:49:54',106),(64,5,6,'empty.docx','users/demo/empty.docx',31,NULL,'2022-01-03 10:26:55',106),(65,6,6,'empty.odt','users/demo/empty.odt',31,NULL,'2022-01-03 10:26:55',106),(66,1,21,'Demo','| Demo | Demo',51,NULL,'2022-01-03 10:26:56',107),(67,2,21,'[001] Develop Rocket 2000','ACME Corporation | Demo | Demo/[001] Develop Rocket 2000',51,NULL,'2022-01-03 10:26:56',107),(68,3,21,'[001] Develop Rocket Launcher','ACME Corporation | Demo | Demo/[001] Develop Rocket Launcher',51,NULL,'2022-01-03 10:26:56',107),(69,30,6,'projects2','projects2',32,NULL,'2021-11-05 08:42:34',17),(70,31,6,'template-icons','projects2/template-icons',32,NULL,'2021-06-28 09:00:25',17),(71,7,6,'folder.png','projects2/template-icons/folder.png',31,NULL,'2021-06-28 09:00:25',17),(72,8,6,'project.png','projects2/template-icons/project.png',31,NULL,'2021-06-28 09:00:25',17),(73,1,26,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',56,NULL,'2013-05-17 07:53:08',83),(74,2,26,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',56,NULL,'2013-05-17 07:53:08',83),(75,3,26,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',56,NULL,'2013-05-17 08:06:26',83),(76,4,26,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',56,NULL,'2013-05-17 08:06:26',83),(77,5,2,'John Doe','Test',24,'isContact','2022-01-03 10:27:00',110),(78,6,2,'Linda Smith','Test',24,'isContact','2022-01-03 10:27:00',110),(79,7,2,'John Doe','Test',24,'isContact','2022-01-03 10:27:06',110),(80,8,2,'John Doe','Test',24,'isContact','2022-01-03 10:27:06',110);
/*!40000 ALTER TABLE `core_search` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_search_word`
--

DROP TABLE IF EXISTS `core_search_word`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_search_word` (
  `searchId` int(11) NOT NULL,
  `word` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`word`,`searchId`),
  KEY `searchId` (`searchId`),
  CONSTRAINT `core_search_word_ibfk_1` FOREIGN KEY (`searchId`) REFERENCES `core_search` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_search_word`
--

LOCK TABLES `core_search_word` WRITE;
/*!40000 ALTER TABLE `core_search_word` DISABLE KEYS */;
INSERT INTO `core_search_word` VALUES (67,'[001]'),(68,'[001]'),(54,'+31'),(55,'+31'),(14,'+310101234567'),(15,'+310101234567'),(16,'+310101234567'),(14,'+31061234567'),(15,'+31061234567'),(16,'+31061234567'),(46,'000001'),(50,'000001'),(52,'000001'),(48,'000002'),(51,'000002'),(53,'000002'),(46,'00001'),(50,'00001'),(52,'00001'),(54,'00001'),(48,'00002'),(51,'00002'),(53,'00002'),(55,'00002'),(46,'0001'),(50,'0001'),(52,'0001'),(54,'0001'),(48,'0002'),(51,'0002'),(53,'0002'),(55,'0002'),(46,'001'),(50,'001'),(52,'001'),(54,'001'),(67,'001'),(68,'001'),(67,'001]'),(68,'001]'),(23,'0019e88f-9ebd-5413-a0bd-e9c4a36a2df3'),(24,'0019e88f-9ebd-5413-a0bd-e9c4a36a2df3'),(48,'002'),(51,'002'),(53,'002'),(55,'002'),(31,'00d0178e-f049-54ba-94e6-87f4825549c7'),(67,'01]'),(68,'01]'),(14,'0101234567'),(15,'0101234567'),(16,'0101234567'),(14,'01234567'),(15,'01234567'),(16,'01234567'),(44,'021b6f1d-2f03-5232-8998-8527e457e6e0'),(54,'02200001'),(55,'02200002'),(14,'061234567'),(15,'061234567'),(16,'061234567'),(28,'0dcda4ed-099c-5ee1-9d88-15ee1bdcf701'),(47,'0fec9871-ef4b-56a0-b756-e7952ecd9425'),(1,'1'),(21,'1'),(38,'1'),(58,'1'),(66,'1'),(73,'1'),(10,'10'),(30,'10'),(14,'10101234567'),(15,'10101234567'),(16,'10101234567'),(14,'1012'),(15,'1012'),(16,'1012'),(46,'1012'),(48,'1012'),(50,'1012'),(51,'1012'),(52,'1012'),(53,'1012'),(14,'101234567'),(15,'101234567'),(16,'101234567'),(14,'1061234567'),(15,'1061234567'),(16,'1061234567'),(11,'11'),(31,'11'),(12,'12'),(32,'12'),(14,'1234567'),(15,'1234567'),(16,'1234567'),(54,'1234567'),(55,'1234567'),(46,'123456789b01'),(48,'123456789b01'),(50,'123456789b01'),(51,'123456789b01'),(52,'123456789b01'),(53,'123456789b01'),(73,'13687771885195e1e479413@localhost'),(74,'13687771885195e1e479413@localhost'),(75,'13687779865195e5020b17e@localhost'),(76,'13687779865195e5020b17e@localhost'),(17,'15'),(18,'16'),(38,'16e6662e-08a6-56d3-8cda-5f9641f3056a'),(19,'17'),(20,'18'),(33,'19'),(21,'19218cac-c323-5b91-b28f-f8375ea3dcde'),(22,'19218cac-c323-5b91-b28f-f8375ea3dcde'),(2,'2'),(14,'2'),(22,'2'),(40,'2'),(59,'2'),(67,'2'),(74,'2'),(34,'20'),(67,'2000'),(73,'2000'),(74,'2000'),(46,'2000001'),(50,'2000001'),(52,'2000001'),(48,'2000002'),(51,'2000002'),(53,'2000002'),(54,'200001'),(55,'200002'),(67,'2000acme'),(63,'2021-10-03t222240229xls'),(63,'2021-10-03t222240229xlsusers/demo/vraagouder'),(54,'202200001'),(55,'202200002'),(35,'21'),(36,'22'),(46,'22000001'),(50,'22000001'),(52,'22000001'),(48,'22000002'),(51,'22000002'),(53,'22000002'),(54,'2200001'),(55,'2200002'),(37,'23'),(14,'234567'),(15,'234567'),(16,'234567'),(39,'24'),(41,'25'),(56,'26'),(57,'27'),(60,'28'),(61,'29'),(3,'3'),(15,'3'),(23,'3'),(42,'3'),(50,'3'),(62,'3'),(68,'3'),(75,'3'),(69,'30'),(70,'31'),(14,'310101234567'),(15,'310101234567'),(16,'310101234567'),(14,'31061234567'),(15,'31061234567'),(16,'31061234567'),(14,'34567'),(15,'34567'),(16,'34567'),(42,'39a7f4c5-98c2-5f67-8574-2851959b0fd3'),(4,'4'),(16,'4'),(24,'4'),(43,'4'),(51,'4'),(63,'4'),(76,'4'),(14,'4567'),(15,'4567'),(16,'4567'),(29,'4b493ac7-401b-5686-b8d4-41922d5b7e80'),(5,'5'),(25,'5'),(44,'5'),(52,'5'),(64,'5'),(77,'5'),(45,'511e978b-eabc-5d60-bfe6-7ea9191dc08e'),(77,'5222'),(79,'5222'),(80,'5222'),(14,'567'),(15,'567'),(16,'567'),(6,'6'),(26,'6'),(45,'6'),(53,'6'),(65,'6'),(78,'6'),(14,'61234567'),(15,'61234567'),(16,'61234567'),(32,'6fa92c39-eef5-5c2e-a33f-fd3d010bbcf0'),(7,'7'),(27,'7'),(47,'7'),(71,'7'),(79,'7'),(40,'7616b2c0-1fda-5f1f-bfa6-886d067f2420'),(8,'8'),(28,'8'),(49,'8'),(72,'8'),(80,'8'),(9,'9'),(29,'9'),(49,'91b60873-38fb-5f8d-b76e-85d5df348b84'),(30,'942a8326-5c38-57d7-8d9c-16f210b9b6fb'),(27,'9c5082d8-7409-575b-8969-019ab8138332'),(43,'a29c3c81-633c-51be-b97c-a40a678b78d1'),(67,'accuracy'),(68,'accuracy'),(15,'acme'),(16,'acme'),(21,'acme'),(22,'acme'),(23,'acme'),(24,'acme'),(25,'acme'),(26,'acme'),(27,'acme'),(28,'acme'),(29,'acme'),(30,'acme'),(31,'acme'),(32,'acme'),(48,'acme'),(49,'acme'),(51,'acme'),(53,'acme'),(54,'acme'),(55,'acme'),(67,'acme'),(68,'acme'),(2,'admin'),(36,'administrator'),(47,'administrator'),(49,'administrator'),(36,'administratortasks/system'),(14,'amsterdam'),(15,'amsterdam'),(16,'amsterdam'),(46,'amsterdam'),(48,'amsterdam'),(50,'amsterdam'),(51,'amsterdam'),(52,'amsterdam'),(53,'amsterdam'),(67,'and'),(68,'and'),(67,'better'),(68,'better'),(4,'billing'),(31,'blast'),(77,'bosch'),(79,'bosch'),(80,'bosch'),(17,'calendar'),(47,'call:'),(49,'call:'),(55,'can'),(14,'ceo'),(16,'ceo'),(21,'confirmed'),(22,'confirmed'),(23,'confirmed'),(24,'confirmed'),(25,'confirmed'),(26,'confirmed'),(27,'confirmed'),(28,'confirmed'),(29,'confirmed'),(30,'confirmed'),(31,'confirmed'),(32,'confirmed'),(15,'corporation'),(16,'corporation'),(48,'corporation'),(49,'corporation'),(51,'corporation'),(53,'corporation'),(54,'corporation'),(55,'corporation'),(67,'corporation'),(68,'corporation'),(54,'corporation0'),(55,'corporation0'),(16,'coyote'),(34,'coyote'),(48,'coyote'),(51,'coyote'),(53,'coyote'),(54,'coyote'),(55,'coyote'),(67,'coyote'),(68,'coyote'),(46,'dear'),(48,'dear'),(50,'dear'),(51,'dear'),(52,'dear'),(53,'dear'),(14,'demo'),(15,'demo'),(16,'demo'),(18,'demo'),(37,'demo'),(56,'demo'),(59,'demo'),(66,'demo'),(67,'demo'),(68,'demo'),(73,'demo'),(74,'demo'),(75,'demo'),(76,'demo'),(73,'demo@group-officecom'),(74,'demo@group-officecom'),(75,'demo@group-officecom'),(76,'demo@group-officecom'),(67,'demo/001'),(68,'demo/001'),(77,'den'),(79,'den'),(80,'den'),(67,'develop'),(68,'develop'),(73,'development'),(74,'development'),(59,'docx'),(64,'docx'),(77,'doe'),(79,'doe'),(80,'doe'),(38,'dog'),(40,'dog'),(42,'dog'),(38,'dogdemo'),(42,'dogelmer'),(40,'doglinda'),(34,'don'),(25,'e04f9d2d-65c0-5ebc-ab9b-c558e0a2e464'),(26,'e04f9d2d-65c0-5ebc-ab9b-c558e0a2e464'),(21,'ebf1e2'),(22,'ebf1e2'),(23,'ebf1e2'),(24,'ebf1e2'),(25,'ebf1e2'),(26,'ebf1e2'),(27,'ebf1e2'),(28,'ebf1e2'),(29,'ebf1e2'),(30,'ebf1e2'),(31,'ebf1e2'),(32,'ebf1e2'),(19,'elmer'),(41,'elmer'),(73,'elmer'),(74,'elmer'),(73,'elmer@group-officecom'),(74,'elmer@group-officecom'),(73,'email/fromfile/demo_61d2cf71a4b5feml/demoeml'),(74,'email/fromfile/demo_61d2cf71cedc4eml/demoeml'),(75,'email/fromfile/demo2_61d2cf71eb33feml/demo2eml'),(76,'email/fromfile/demo2_61d2cf7217d9feml/demo2eml'),(75,'emailjust'),(76,'emailjust'),(73,'emailrocket'),(74,'emailrocket'),(66,'emo'),(64,'emptydocx'),(57,'emptydocxsb-951a9b4f-dxxjjl'),(60,'emptydocxsb-951a9b4f-pwsouu'),(61,'emptydocxsb-951a9b4f-uzmma7'),(65,'emptyodt'),(21,'europe/amsterdam'),(22,'europe/amsterdam'),(23,'europe/amsterdam'),(24,'europe/amsterdam'),(25,'europe/amsterdam'),(26,'europe/amsterdam'),(27,'europe/amsterdam'),(28,'europe/amsterdam'),(29,'europe/amsterdam'),(30,'europe/amsterdam'),(31,'europe/amsterdam'),(32,'europe/amsterdam'),(31,'eventblast'),(23,'eventmeet'),(24,'eventmeet'),(28,'eventmeet'),(25,'eventmt'),(26,'eventmt'),(29,'eventmt'),(21,'eventproject'),(22,'eventproject'),(27,'eventproject'),(30,'eventrocket'),(32,'eventtest'),(32,'extender'),(32,'extenderlinda'),(38,'feed'),(40,'feed'),(42,'feed'),(30,'fields'),(31,'fields'),(32,'fields'),(59,'filedemo'),(64,'fileemptydocxusers/demo/emptydocx'),(65,'fileemptyodtusers/demo/emptyodt'),(71,'filefolderpngprojects2/template-icons/folderpng'),(58,'filenopersonjpgusers/demo/nopersonjpg'),(72,'fileprojectpngprojects2/template-icons/projectpng'),(63,'filevraagouder'),(62,'filewecoyotepngusers/demo/wecoyotepng'),(10,'folder'),(2,'folderadminusers/admin'),(4,'folderbillingbilling'),(17,'foldercalendarcalendar'),(18,'folderdemo'),(37,'folderdemo'),(56,'folderdemousers/demo'),(34,'folderdon'),(19,'folderelmer'),(41,'folderelmer'),(57,'folderemptydocxsb-951a9b4f-dxxjjlusers/demo/emptydocxsb-951a9b4f-dxxjjl'),(60,'folderemptydocxsb-951a9b4f-pwsouuusers/demo/emptydocxsb-951a9b4f-pwsouu'),(61,'folderemptydocxsb-951a9b4f-uzmma7users/demo/emptydocxsb-951a9b4f-uzmma7'),(7,'folderinvoicesbilling/invoices'),(20,'folderlinda'),(39,'folderlinda'),(6,'folderordersbilling/orders'),(71,'folderpng'),(8,'folderproduct_imagesbilling/product_images'),(10,'folderproject_templates/projects'),(9,'folderproject_templatesproject_templates'),(10,'folderprojects'),(69,'folderprojects2projects2'),(3,'folderpublicusers/admin/public'),(5,'folderquotesbilling/quotes'),(33,'folderroad'),(11,'folderstandard'),(36,'foldersystem'),(35,'foldertaskstasks'),(70,'foldertemplate-iconsprojects2/template-icons'),(12,'folderticketstickets'),(1,'folderusersusers'),(66,'for'),(19,'fudd'),(41,'fudd'),(42,'fudd'),(45,'fudd'),(19,'fuddcalendar/elmer'),(41,'fuddtasks/elmer'),(79,'germany'),(52,'i22000001'),(53,'i22000002'),(31,'impact'),(14,'inc'),(46,'inc'),(47,'inc'),(50,'inc'),(52,'inc'),(63,'incasso'),(15,'info@acmedemo'),(48,'info@acmedemo'),(51,'info@acmedemo'),(53,'info@acmedemo'),(46,'info@smithdemo'),(50,'info@smithdemo'),(52,'info@smithdemo'),(52,'invoice/quotei22000001smith'),(53,'invoice/quotei22000002acme'),(50,'invoice/quoteo22000001smith'),(51,'invoice/quoteo22000002acme'),(46,'invoice/quoteq22000001smith'),(48,'invoice/quoteq22000002acme'),(7,'invoices'),(14,'john'),(28,'john'),(46,'john'),(50,'john'),(52,'john'),(77,'john'),(79,'john'),(80,'john'),(79,'john@doetest'),(80,'john@doetest'),(14,'john@smithdemo'),(28,'johnlinda'),(58,'jpg'),(14,'just'),(15,'just'),(16,'just'),(66,'just'),(75,'just'),(76,'just'),(46,'kalverstraat'),(48,'kalverstraat'),(50,'kalverstraat'),(51,'kalverstraat'),(52,'kalverstraat'),(53,'kalverstraat'),(68,'launcher'),(68,'launcheracme'),(59,'letterdocx'),(59,'letterdocxusers/demo/demo'),(20,'linda'),(39,'linda'),(78,'linda'),(54,'malfunctioning'),(23,'meet'),(24,'meet'),(28,'meet'),(21,'meeting'),(22,'meeting'),(25,'meeting'),(26,'meeting'),(27,'meeting'),(29,'meeting'),(43,'meeting'),(44,'meeting'),(45,'meeting'),(21,'meetingdemo'),(25,'meetingdemo'),(43,'meetingdemo'),(45,'meetingelmer'),(22,'meetinglinda'),(26,'meetinglinda'),(27,'meetinglinda'),(29,'meetinglinda'),(44,'meetinglinda'),(75,'message'),(76,'message'),(75,'messagefrom:'),(76,'messagefrom:'),(38,'needs-action'),(40,'needs-action'),(42,'needs-action'),(43,'needs-action'),(44,'needs-action'),(45,'needs-action'),(47,'needs-action'),(49,'needs-action'),(14,'netherlands'),(15,'netherlands'),(16,'netherlands'),(77,'netherlands'),(80,'netherlands'),(14,'noord-holland'),(15,'noord-holland'),(16,'noord-holland'),(46,'noord-holland'),(48,'noord-holland'),(50,'noord-holland'),(51,'noord-holland'),(52,'noord-holland'),(53,'noord-holland'),(58,'nopersonjpg'),(50,'o22000001'),(51,'o22000002'),(65,'odt'),(21,'office'),(22,'office'),(23,'office'),(24,'office'),(25,'office'),(26,'office'),(27,'office'),(28,'office'),(29,'office'),(6,'orders'),(66,'placeholder'),(73,'plan'),(74,'plan'),(73,'planfrom:'),(74,'planfrom:'),(62,'png'),(71,'png'),(72,'png'),(43,'prepare'),(44,'prepare'),(45,'prepare'),(8,'product_images'),(11,'project'),(21,'project'),(22,'project'),(27,'project'),(9,'project_templates'),(67,'project001'),(68,'project001'),(66,'projectdemo'),(72,'projectpng'),(11,'projectproject_templates/standard'),(10,'projects'),(66,'projects'),(69,'projects2'),(3,'public'),(46,'q22000001'),(47,'q22000001'),(47,'q22000001system'),(48,'q22000002'),(49,'q22000002'),(49,'q22000002system'),(5,'quotes'),(32,'range'),(67,'range'),(68,'range'),(33,'road'),(30,'rocket'),(67,'rocket'),(68,'rocket'),(73,'rocket'),(74,'rocket'),(54,'rockets'),(55,'rockets'),(54,'rocketswile'),(55,'rocketswile'),(33,'room'),(34,'room'),(34,'roomcalendar/don'),(33,'roomcalendar/road'),(33,'runner'),(46,'sir/madam'),(48,'sir/madam'),(50,'sir/madam'),(51,'sir/madam'),(52,'sir/madam'),(53,'sir/madam'),(14,'smith'),(20,'smith'),(22,'smith'),(24,'smith'),(26,'smith'),(27,'smith'),(28,'smith'),(29,'smith'),(30,'smith'),(31,'smith'),(32,'smith'),(39,'smith'),(40,'smith'),(44,'smith'),(46,'smith'),(47,'smith'),(50,'smith'),(52,'smith'),(78,'smith'),(20,'smithcalendar/linda'),(39,'smithtasks/linda'),(55,'speed'),(11,'standard'),(66,'sub'),(36,'system'),(47,'taskcall:'),(49,'taskcall:'),(38,'taskfeed'),(40,'taskfeed'),(42,'taskfeed'),(43,'taskprepare'),(44,'taskprepare'),(45,'taskprepare'),(35,'tasks'),(70,'template-icons'),(31,'test'),(32,'test'),(30,'testing'),(31,'testing'),(32,'testing'),(30,'testinglinda'),(31,'testlinda'),(38,'the'),(40,'the'),(42,'the'),(55,'ticketcan'),(54,'ticketmalfunctioning'),(12,'tickets'),(73,'to:'),(74,'to:'),(75,'to:'),(76,'to:'),(18,'user'),(21,'user'),(23,'user'),(25,'user'),(37,'user'),(38,'user'),(43,'user'),(73,'user'),(74,'user'),(75,'user'),(76,'user'),(18,'usercalendar/demo'),(1,'users'),(37,'usertasks/demo'),(63,'vraagouder'),(62,'wecoyotepng'),(16,'wile'),(23,'wile'),(24,'wile'),(48,'wile'),(51,'wile'),(53,'wile'),(54,'wile'),(55,'wile'),(67,'wile'),(68,'wile'),(16,'wile@smithdemo'),(54,'wile@smithdemo'),(55,'wile@smithdemo'),(23,'wiledemo'),(24,'wilelinda'),(63,'xls');
/*!40000 ALTER TABLE `core_search_word` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_setting`
--

DROP TABLE IF EXISTS `core_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_setting` (
  `moduleId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`moduleId`,`name`),
  CONSTRAINT `module` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_setting`
--

LOCK TABLES `core_setting` WRITE;
/*!40000 ALTER TABLE `core_setting` DISABLE KEYS */;
INSERT INTO `core_setting` VALUES (1,'cacheClearedAt','1641205619'),(1,'databaseVersion','6.5.96'),(1,'language','en'),(1,'locale',NULL),(1,'passwordMinLength','4'),(1,'smtpPassword',NULL),(1,'systemEmail','admin@intermesh.mailserver'),(1,'userAddressBookId','4');
/*!40000 ALTER TABLE `core_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_smtp_account`
--

DROP TABLE IF EXISTS `core_smtp_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_smtp_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT 587,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `encryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `verifyCertificate` tinyint(1) NOT NULL DEFAULT 1,
  `fromName` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fromEmail` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `moduleId` (`moduleId`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `core_smtp_account_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_smtp_account_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_smtp_account`
--

LOCK TABLES `core_smtp_account` WRITE;
/*!40000 ALTER TABLE `core_smtp_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_smtp_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_spreadsheet_export`
--

DROP TABLE IF EXISTS `core_spreadsheet_export`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_spreadsheet_export` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `columns` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `entityTypeId` (`entityTypeId`),
  KEY `name` (`name`),
  CONSTRAINT `core_spreadsheet_export_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_spreadsheet_export_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_spreadsheet_export`
--

LOCK TABLES `core_spreadsheet_export` WRITE;
/*!40000 ALTER TABLE `core_spreadsheet_export` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_spreadsheet_export` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_user`
--

DROP TABLE IF EXISTS `core_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `displayName` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatarId` binary(40) DEFAULT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recoveryEmail` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recoveryHash` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recoverySendAt` datetime DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `dateFormat` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd-m-Y',
  `shortDateInList` tinyint(1) NOT NULL DEFAULT 1,
  `timeFormat` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'G:i',
  `thousandsSeparator` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '.',
  `decimalSeparator` varchar(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ',',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `loginCount` int(11) NOT NULL DEFAULT 0,
  `max_rows_list` tinyint(4) NOT NULL DEFAULT 20,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Europe/Amsterdam',
  `start_module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'summary',
  `language` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `theme` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Paper',
  `firstWeekday` tinyint(4) NOT NULL DEFAULT 1,
  `sort_name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'first_name',
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `mute_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_reminder_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_new_mail_sound` tinyint(1) NOT NULL DEFAULT 0,
  `show_smilies` tinyint(1) NOT NULL DEFAULT 1,
  `auto_punctuation` tinyint(1) NOT NULL DEFAULT 0,
  `listSeparator` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ';',
  `textSeparator` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '"',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `disk_quota` bigint(20) DEFAULT NULL,
  `disk_usage` bigint(20) NOT NULL DEFAULT 0,
  `mail_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `holidayset` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_email_addresses_by_time` tinyint(1) NOT NULL DEFAULT 0,
  `no_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
  `homeDir` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmOnMove` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_user_avatar_id_idx` (`avatarId`),
  CONSTRAINT `fk_user_avatar_id` FOREIGN KEY (`avatarId`) REFERENCES `core_blob` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_user`
--

LOCK TABLES `core_user` WRITE;
/*!40000 ALTER TABLE `core_user` DISABLE KEYS */;
INSERT INTO `core_user` VALUES (1,'admin','System Administrator',NULL,1,'admin@intermesh.mailserver','admin@intermesh.mailserver',NULL,NULL,NULL,'2022-01-03 10:26:11','2022-01-03 10:27:06','d-m-Y',1,'G:i','.',',','€',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,1519025,0,NULL,0,0,0,0,'users/admin',0),(2,'elmer','Elmer Fudd','0ec2f1f4f9fb41e8013fcc834991be30a8260750',1,'elmer@acmerpp.demo','elmer@acmerpp.demo',NULL,NULL,NULL,'2022-01-03 10:26:41','2022-01-03 10:26:41','d-m-Y',1,'G:i','.',',','€',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/elmer',0),(3,'demo','Demo User','a2b13489e9762bf7d7dfd63d72d45f0f47411c30',1,'demo@acmerpp.demo','demo@acmerpp.demo',NULL,NULL,NULL,'2022-01-03 10:26:43','2022-01-03 10:26:43','d-m-Y',1,'G:i','.',',','€',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/demo',0),(4,'linda','Linda Smith','c363a83f50fe2fbe94deff31afee36d8d7923e17',1,'linda@acmerpp.linda','linda@acmerpp.linda',NULL,NULL,NULL,'2022-01-03 10:26:43','2022-01-03 10:26:43','d-m-Y',1,'G:i','.',',','€',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/linda',0);
/*!40000 ALTER TABLE `core_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_user_custom_fields`
--

DROP TABLE IF EXISTS `core_user_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_user_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `core_user_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_user_custom_fields`
--

LOCK TABLES `core_user_custom_fields` WRITE;
/*!40000 ALTER TABLE `core_user_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_user_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_user_default_group`
--

DROP TABLE IF EXISTS `core_user_default_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_user_default_group` (
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`),
  CONSTRAINT `core_user_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_user_default_group`
--

LOCK TABLES `core_user_default_group` WRITE;
/*!40000 ALTER TABLE `core_user_default_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_user_default_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_user_group`
--

DROP TABLE IF EXISTS `core_user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_user_group` (
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `core_user_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_user_group_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_user_group`
--

LOCK TABLES `core_user_group` WRITE;
/*!40000 ALTER TABLE `core_user_group` DISABLE KEYS */;
INSERT INTO `core_user_group` VALUES (1,1),(2,1),(2,2),(2,3),(2,4),(3,2),(3,3),(3,4),(4,1),(5,2),(6,3),(7,4);
/*!40000 ALTER TABLE `core_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_accounts`
--

DROP TABLE IF EXISTS `em_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `type` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `host` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT 0,
  `deprecated_use_ssl` tinyint(1) NOT NULL DEFAULT 0,
  `novalidate_cert` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imap_encryption` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imap_allow_self_signed` tinyint(1) NOT NULL DEFAULT 1,
  `mbroot` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sent` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Sent',
  `drafts` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Drafts',
  `trash` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Trash',
  `spam` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Spam',
  `smtp_host` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_encryption` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_allow_self_signed` tinyint(1) NOT NULL DEFAULT 0,
  `smtp_username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_password` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `password_encrypted` tinyint(4) NOT NULL DEFAULT 0,
  `ignore_sent_folder` tinyint(1) NOT NULL DEFAULT 0,
  `sieve_port` int(11) NOT NULL,
  `sieve_usetls` tinyint(1) NOT NULL DEFAULT 1,
  `check_mailboxes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `do_not_mark_as_read` tinyint(1) NOT NULL DEFAULT 0,
  `signature_below_reply` tinyint(1) NOT NULL DEFAULT 0,
  `full_reply_headers` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts`
--

LOCK TABLES `em_accounts` WRITE;
/*!40000 ALTER TABLE `em_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_accounts_collapsed`
--

DROP TABLE IF EXISTS `em_accounts_collapsed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_accounts_collapsed` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts_collapsed`
--

LOCK TABLES `em_accounts_collapsed` WRITE;
/*!40000 ALTER TABLE `em_accounts_collapsed` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_accounts_collapsed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_accounts_sort`
--

DROP TABLE IF EXISTS `em_accounts_sort`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_accounts_sort` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts_sort`
--

LOCK TABLES `em_accounts_sort` WRITE;
/*!40000 ALTER TABLE `em_accounts_sort` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_accounts_sort` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_aliases`
--

DROP TABLE IF EXISTS `em_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_aliases`
--

LOCK TABLES `em_aliases` WRITE;
/*!40000 ALTER TABLE `em_aliases` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_aliases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_contacts_last_mail_times`
--

DROP TABLE IF EXISTS `em_contacts_last_mail_times`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_contacts_last_mail_times` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_mail_time` int(11) NOT NULL,
  PRIMARY KEY (`contact_id`,`user_id`),
  KEY `last_mail_time` (`last_mail_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_contacts_last_mail_times`
--

LOCK TABLES `em_contacts_last_mail_times` WRITE;
/*!40000 ALTER TABLE `em_contacts_last_mail_times` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_contacts_last_mail_times` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_filters`
--

DROP TABLE IF EXISTS `em_filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `field` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keyword` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `mark_as_read` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_filters`
--

LOCK TABLES `em_filters` WRITE;
/*!40000 ALTER TABLE `em_filters` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_filters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_folders`
--

DROP TABLE IF EXISTS `em_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_folders` (
  `id` int(11) NOT NULL DEFAULT 0,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscribed` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `delimiter` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `msgcount` int(11) NOT NULL DEFAULT 0,
  `unseen` int(11) NOT NULL DEFAULT 0,
  `auto_check` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `can_have_children` tinyint(1) NOT NULL,
  `no_select` tinyint(1) DEFAULT NULL,
  `sort` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_folders`
--

LOCK TABLES `em_folders` WRITE;
/*!40000 ALTER TABLE `em_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_folders_expanded`
--

DROP TABLE IF EXISTS `em_folders_expanded`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_folders_expanded` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_folders_expanded`
--

LOCK TABLES `em_folders_expanded` WRITE;
/*!40000 ALTER TABLE `em_folders_expanded` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_folders_expanded` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_labels`
--

DROP TABLE IF EXISTS `em_labels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_labels`
--

LOCK TABLES `em_labels` WRITE;
/*!40000 ALTER TABLE `em_labels` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_labels` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_links`
--

DROP TABLE IF EXISTS `em_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `from` varchar(255) DEFAULT NULL,
  `to` text DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT 0,
  `path` varchar(255) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `uid` varchar(350) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `account_id` (`user_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_links`
--

LOCK TABLES `em_links` WRITE;
/*!40000 ALTER TABLE `em_links` DISABLE KEYS */;
INSERT INTO `em_links` VALUES (1,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_61d2cf71a4b5f.eml/demo.eml',1368777188,1368777188,1,83,'<1368777188.5195e1e479413@localhost>'),(2,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_61d2cf71cedc4.eml/demo.eml',1368777188,1368777188,1,83,'<1368777188.5195e1e479413@localhost>'),(3,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_61d2cf71eb33f.eml/demo2.eml',1368777986,1368777986,1,83,'<1368777986.5195e5020b17e@localhost>'),(4,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_61d2cf7217d9f.eml/demo2.eml',1368777986,1368777986,1,83,'<1368777986.5195e5020b17e@localhost>');
/*!40000 ALTER TABLE `em_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_messages_cache`
--

DROP TABLE IF EXISTS `em_messages_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_messages_cache` (
  `folder_id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `new` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `from` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int(11) NOT NULL,
  `udate` int(11) NOT NULL,
  `attachments` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `flagged` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `answered` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `forwarded` tinyint(1) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `to` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `serialized_message_object` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`folder_id`,`uid`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_messages_cache`
--

LOCK TABLES `em_messages_cache` WRITE;
/*!40000 ALTER TABLE `em_messages_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_messages_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `em_portlet_folders`
--

DROP TABLE IF EXISTS `em_portlet_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `em_portlet_folders` (
  `account_id` int(11) NOT NULL,
  `folder_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`folder_name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_portlet_folders`
--

LOCK TABLES `em_portlet_folders` WRITE;
/*!40000 ALTER TABLE `em_portlet_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `em_portlet_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_default_email_account_templates`
--

DROP TABLE IF EXISTS `email_default_email_account_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_default_email_account_templates`
--

LOCK TABLES `email_default_email_account_templates` WRITE;
/*!40000 ALTER TABLE `email_default_email_account_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_default_email_account_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_default_email_templates`
--

DROP TABLE IF EXISTS `email_default_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `email_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_default_email_templates`
--

LOCK TABLES `email_default_email_templates` WRITE;
/*!40000 ALTER TABLE `email_default_email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `email_default_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emp_folders`
--

DROP TABLE IF EXISTS `emp_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `emp_folders` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_folders`
--

LOCK TABLES `emp_folders` WRITE;
/*!40000 ALTER TABLE `emp_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `emp_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_bookmarks`
--

DROP TABLE IF EXISTS `fs_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_bookmarks` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`),
  KEY `fs_bookmarks_core_user_id_fk` (`user_id`),
  CONSTRAINT `fs_bookmarks_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fs_bookmarks_fs_folders_folder_id_fk` FOREIGN KEY (`folder_id`) REFERENCES `fs_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_bookmarks`
--

LOCK TABLES `fs_bookmarks` WRITE;
/*!40000 ALTER TABLE `fs_bookmarks` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_filehandlers`
--

DROP TABLE IF EXISTS `fs_filehandlers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_filehandlers` (
  `user_id` int(11) NOT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cls` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_filehandlers`
--

LOCK TABLES `fs_filehandlers` WRITE;
/*!40000 ALTER TABLE `fs_filehandlers` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_filehandlers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_files`
--

DROP TABLE IF EXISTS `fs_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `locked_user_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `size` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expire_time` int(11) NOT NULL DEFAULT 0,
  `random_code` char(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delete_when_expired` tinyint(1) NOT NULL DEFAULT 0,
  `content_expire_date` int(11) DEFAULT NULL,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_id_2` (`folder_id`,`name`),
  KEY `folder_id` (`folder_id`),
  KEY `name` (`name`),
  KEY `extension` (`extension`),
  KEY `mtime` (`mtime`),
  KEY `content_expire_date` (`content_expire_date`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_files`
--

LOCK TABLES `fs_files` WRITE;
/*!40000 ALTER TABLE `fs_files` DISABLE KEYS */;
INSERT INTO `fs_files` VALUES (1,26,'noperson.jpg',0,0,1641205615,1641205615,1,3015,1,NULL,'jpg',0,NULL,0,NULL,1),(2,26,'Demo letter.docx',0,0,1641205615,1641205615,1,4312,1,NULL,'docx',0,NULL,0,NULL,1),(3,26,'wecoyote.png',0,0,1641205615,1641205615,1,39495,1,NULL,'png',0,NULL,0,NULL,1),(4,26,'Vraagouder incasso - 2021-10-03T222240.229.xls',0,0,1641205616,1633348194,1,1457664,1,NULL,'xls',0,NULL,0,NULL,1),(5,26,'empty.docx',0,0,1641205616,1641205615,1,3726,1,NULL,'docx',0,NULL,0,NULL,1),(6,26,'empty.odt',0,0,1641205616,1641205615,1,6971,1,NULL,'odt',0,NULL,0,NULL,1),(7,31,'folder.png',0,0,1641205617,1624870825,1,611,1,NULL,'png',0,NULL,0,NULL,1),(8,31,'project.png',0,0,1641205617,1624870825,1,3231,1,NULL,'png',0,NULL,0,NULL,1);
/*!40000 ALTER TABLE `fs_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_files_custom_fields`
--

DROP TABLE IF EXISTS `fs_files_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_files_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fs_files_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_files_custom_fields`
--

LOCK TABLES `fs_files_custom_fields` WRITE;
/*!40000 ALTER TABLE `fs_files_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_files_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_folder_pref`
--

DROP TABLE IF EXISTS `fs_folder_pref`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_folder_pref` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folder_pref`
--

LOCK TABLES `fs_folder_pref` WRITE;
/*!40000 ALTER TABLE `fs_folder_pref` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_folder_pref` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_folders`
--

DROP TABLE IF EXISTS `fs_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_folders` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `name` varchar(260) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT 1,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `quota_user_id` int(11) NOT NULL DEFAULT 0,
  `readonly` tinyint(1) NOT NULL DEFAULT 0,
  `cm_state` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `apply_state` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parent_id_3` (`parent_id`,`name`),
  KEY `name` (`name`),
  KEY `parent_id` (`parent_id`),
  KEY `visible` (`visible`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folders`
--

LOCK TABLES `fs_folders` WRITE;
/*!40000 ALTER TABLE `fs_folders` DISABLE KEYS */;
INSERT INTO `fs_folders` VALUES (1,1,0,'users',0,17,NULL,1,1641205580,1641205615,1,1,1,NULL,0),(1,2,1,'admin',1,22,NULL,1,1641205580,1641205580,1,1,1,NULL,0),(1,3,2,'Public',0,23,NULL,1,1641205580,1641205580,1,1,0,NULL,0),(1,4,0,'billing',0,17,NULL,1,1641205587,1641205590,1,1,1,NULL,0),(1,5,4,'Quotes',0,34,NULL,1,1641205588,1641205588,1,1,1,NULL,0),(1,6,4,'Orders',0,41,NULL,1,1641205588,1641205589,1,1,1,NULL,0),(1,7,4,'Invoices',0,46,NULL,1,1641205589,1641205589,1,1,1,NULL,0),(1,8,4,'product_images',0,33,NULL,1,1641205590,1641205590,1,1,0,NULL,0),(1,9,0,'project_templates',0,17,NULL,1,1641205595,1641205596,1,1,1,NULL,0),(1,10,9,'Projects folder',0,66,NULL,1,1641205595,1641205595,1,1,1,NULL,0),(1,11,9,'Standard project',0,68,NULL,1,1641205596,1641205596,1,1,1,NULL,0),(1,12,0,'tickets',0,17,NULL,1,1641205599,1641205599,1,1,1,NULL,0),(1,13,12,'0-IT',0,79,NULL,1,1641205599,1641205599,1,1,1,NULL,0),(1,14,12,'0-Sales',0,81,NULL,1,1641205599,1641205599,1,1,1,NULL,0),(1,15,0,'calendar',0,17,NULL,1,1641205605,1641205609,1,1,1,NULL,0),(1,16,15,'Demo User',0,87,NULL,1,1641205605,1641205605,1,1,1,NULL,0),(1,17,15,'Elmer Fudd',0,88,NULL,1,1641205605,1641205605,1,1,1,NULL,0),(1,18,15,'Linda Smith',0,89,NULL,1,1641205605,1641205606,1,1,1,NULL,0),(1,19,15,'Road Runner Room',0,93,NULL,1,1641205608,1641205608,1,1,1,NULL,0),(1,20,15,'Don Coyote Room',0,94,NULL,1,1641205608,1641205609,1,1,1,NULL,0),(1,21,0,'tasks',0,17,NULL,1,1641205609,1641205610,1,1,1,NULL,0),(1,22,21,'System Administrator',0,95,NULL,1,1641205609,1641205609,1,1,1,NULL,0),(1,23,21,'Demo User',0,97,NULL,1,1641205609,1641205609,1,1,1,NULL,0),(1,24,21,'Linda Smith',0,98,NULL,1,1641205609,1641205609,1,1,1,NULL,0),(1,25,21,'Elmer Fudd',0,99,NULL,1,1641205609,1641205610,1,1,1,NULL,0),(3,26,1,'demo',1,106,NULL,1,1641205615,1633349513,1,1,1,NULL,0),(1,27,26,'empty.docx.sb-951a9b4f-dxxJjl',0,0,NULL,1,1641205615,1633349428,1,3,0,NULL,0),(1,28,26,'empty.docx.sb-951a9b4f-pwSOuu',0,0,NULL,1,1641205615,1633349449,1,3,0,NULL,0),(1,29,26,'empty.docx.sb-951a9b4f-UZmMa7',0,0,NULL,1,1641205615,1633349416,1,3,0,NULL,0),(1,30,0,'projects2',0,17,NULL,1,1641205616,1641205616,1,1,1,NULL,0),(1,31,30,'template-icons',0,0,NULL,1,1641205616,1624870825,1,1,0,NULL,0);
/*!40000 ALTER TABLE `fs_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_folders_custom_fields`
--

DROP TABLE IF EXISTS `fs_folders_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_folders_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fs_folders_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folders_custom_fields`
--

LOCK TABLES `fs_folders_custom_fields` WRITE;
/*!40000 ALTER TABLE `fs_folders_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_folders_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_new_files`
--

DROP TABLE IF EXISTS `fs_new_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_new_files` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `file_id` (`file_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_new_files`
--

LOCK TABLES `fs_new_files` WRITE;
/*!40000 ALTER TABLE `fs_new_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_new_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_notification_messages`
--

DROP TABLE IF EXISTS `fs_notification_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_notification_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `modified_user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `arg1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `arg2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_notification_messages`
--

LOCK TABLES `fs_notification_messages` WRITE;
/*!40000 ALTER TABLE `fs_notification_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_notification_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_notifications`
--

DROP TABLE IF EXISTS `fs_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_notifications` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_notifications`
--

LOCK TABLES `fs_notifications` WRITE;
/*!40000 ALTER TABLE `fs_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_shared_cache`
--

DROP TABLE IF EXISTS `fs_shared_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_shared_cache` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_shared_cache`
--

LOCK TABLES `fs_shared_cache` WRITE;
/*!40000 ALTER TABLE `fs_shared_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_shared_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_shared_root_folders`
--

DROP TABLE IF EXISTS `fs_shared_root_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_shared_root_folders` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_shared_root_folders`
--

LOCK TABLES `fs_shared_root_folders` WRITE;
/*!40000 ALTER TABLE `fs_shared_root_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_shared_root_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_status_history`
--

DROP TABLE IF EXISTS `fs_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_status_history` (
  `id` int(11) NOT NULL DEFAULT 0,
  `link_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_status_history`
--

LOCK TABLES `fs_status_history` WRITE;
/*!40000 ALTER TABLE `fs_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_statuses`
--

DROP TABLE IF EXISTS `fs_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_statuses` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_statuses`
--

LOCK TABLES `fs_statuses` WRITE;
/*!40000 ALTER TABLE `fs_statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_templates`
--

DROP TABLE IF EXISTS `fs_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  `extension` char(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_templates`
--

LOCK TABLES `fs_templates` WRITE;
/*!40000 ALTER TABLE `fs_templates` DISABLE KEYS */;
INSERT INTO `fs_templates` VALUES (1,1,'Microsoft Word document',19,'PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"����S����Bk�RbG����	+��73z��+E��\"#��f�� �<�t�p>�0��������l7����%�>�����jn����)Ȃ3ReW.)h��f\'.C.ܣH��h�έl\n#AW/?��Lm��#i�i��\ZQO�rTε����m��]�/PKe��\"�\0\0\0�\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xml���N�0��<E�;K�Mպ		q�\0���]#%q���=Y۝(�@�%��������Y�c`C���B\n��j�O��8��o���K+9 ���n�ʆ|d��=���m�]����:���Pp�7�5���L�ӡ�j]�*����ܚ��얮qK�.�F����ρ�r7����r�q���x#�@�Ϛl%�B�q��å\ZF���L���C0p�xn�	��>�#�E��֬�,YF-���0�u�-77���-�������7PK��Z]\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�TQo�0~߯��Nh�5T��	��н�A�9��s\Z诟�j��x������;���6�\Z�JF�s�f�X%\\n\"��|l�Y@dBI���=���;��xI�\"b��z����(��ݭ�����ل�2�6*F\"�>a���pɆu Z�I�ʍ+�؝v��``c@�����{����\\C.l��,,�qk��*Qi׿�_\n��b��5J�nkTES��qu�r�&;����˚�t\n�r�jyt�P�yĖ<s�̱�T��)b3�rL�L�7!���΃H��Hґ,ey����������u�֖1��	�g[�ĥOyk:?��5m�KS������@�J�Ӓu�5�l��l_�\n���`���������g�WC�Kx��h\\Gw�Z�kD��k�uA9��[�a|���p���}/Z��h�3��5�~�9������F��t����	�\'{\Zl\r�#���О��p�d�&\0Ơ����?��l3.q�g+7��������wL�g���5�v*�^��]f�l�zG�PKՔq�\0\0�\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�R�n�0��+\"���T�F.��VH�0�&�d{#{!Я�M�K�ś�̾&��\\�N��B���4e	X���Uξ�%K<	[�rv�6�U�(O,%������l�e\rF��Qҡǒ&M�e�$��.g5Q�q�\'M�����x���{��4]pZP��ת�C����F����-��q(��`��]_#����\'�uƌ�΅�C˿�l;����G,�16�g�naOW\rI���Ι� e|�⣢{�7��\"R$uJ�6c%ډ\nb� ��?��ù,��pm4u6{O��S�$�H�&R�*	�_O#�Nt5�b\ZJ�\'9U����A���dݨ�	��2Bwl4v理=J�}����ʅu�!\r�v�c��`����_�PK���ly\0\06\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��TcL�BC�h�t3�tN��sp�}�����xx��v�Sq���wy�)�)��;v�)פH(���wБ+$��-�\0\r�\".u�6�&u+S�c���G+1��H�8\Z�^�Қ���4�2���W�\\���_��!{�����qz�S�Q��bo^4�T���z�7n^��u;�Mq�0�gPH�,[�f3�������#�PK(��\0\0\0h\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xmlm�[O�0���Kﷶ D�m\\h���D���������2���&\Z���}��𖋽�8/��-�@�H�V�u���P��\r\n��E}S\n˄q��$�,��g�Vh�e{��}	��qt-�\\|����9Vx��I��шN�F�J��A�(��cZP��*��8�t\0��UxHFr��H�}_�Ӂ����}��2<5�:}�\0T�\'5x�&�v��9y�><����:��,��5%lv�ncdS���<���W���ҹ.V��1K�8��TiMJ|9����PK��i\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0H�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0H�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0H�Be��\"�\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0H�B��Z]\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0H�BՔq�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0F\0\0word/styles.xmlPK\0\0\0H�B���ly\0\06\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0word/document.xmlPK\0\0\0H�B(��\0\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0S\0\0docProps/app.xmlPK\0\0\0H�B��i\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0s	\0\0docProps/core.xmlPK\0\0\0H�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\n\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0<\0\0\0\0','docx'),(2,1,'Open-Office Text document',21,'PK\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xml\0PK\0\0\0\0\0\0\0\0\0\0\0PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml�V�n!��+F,�c\']$S��JQ�JIMZuK��iyL���_�1N2	�7���s��s/ov�[�\rS���TbE�\\������\nܬ>,U�0L+�p\'��%VҺ�±���l\r:-+�3�D����J�T�*EWa�1vϳ���-��\\�Ǟp�S�����F}.�c��)�Q���e���E�=�bǙ�[���ma����r��\Z.���a��#��4(�!��/f�b��VP�r��ش$ى\'���A�p�l���]OH�7Hg�F\0��{I��$)W ����\n޻��qw�-r�����fm�6#:�+��R=!�P��|�	�q��߄��Y�8~�ǣ�J�&��-�C�t��tl|/�� \\�8=�\r�L����o�@G0{\\2i,�Ge\ZF��0�F^�]K5�6 �4�Q�q)�T���љ9��`5\\��B�@��A���bnV�x��pǾ�\Z<�2L���%��;?T0���G�*��.Aq����5�no�e}�wD��bw�H3y�vi_��R��^��̘s���Я�x@�L*�`2�0{c�x�&8e�!�:�T��!Ob��\"�q���2<o����>&z,B�0�r�/Ug�AK�N��k�0e��yg�v(��=+���󲸟g\'���qp`Z�6R��e$>�#O�&�wQ|x���˴�PK�\0=@�\0\0s	\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�YK��6��W*�m˻����E�����׀�h�	E\n$e�����D˒W�H�=, �p��<���7��MvD**�]O�ф�D��o�?��n�7�?��\rM�*I����F�6s�rĻ��|%��j�qN�J\'+Q�7�B�U�V����-s�[���������-s�;��\Z����M��1v�^1�(y�5��3ʿ�E���j6��jZ]M�����r9��p���d�+Mf��L��i<�9�x,>�B�e�&r�i��\'^U����mL�dX��\r�|�ޫt�{��po�u6����; ��޶� �I���t��~!D�lp	j�.�������앤�Ȁ=9˞`�4y�р/�\";�Ѥ.!Aي�{_�6��\'�$a�����fy⾍�,5yO��?\"�<�@0y֜��]�3.������ɑhÏ��I�Ȳ��rT\';,��$��<�߀��\0��êUE�z��?�g�_9���\Z�H�3�\Z�4��4ra�Ǟ�\r.Y݋��\Z�V�\"�I�y�oTH�A�)8�T��p**��hͧW	��!:D\r�Au%H8�ڎ2!�W���a]ܞe��)+$�X�\'�=2k�08GEu�\\��`��((���B�}��#\\jat@hДǊY�a���XK��)\r.מbʁ����3���((O��R�Vƃ�����E�L��n�\r�Ӕ����U�&�OiYB}��Hѯ�4^ڮ1̷%��gv!%�������DC�C_������DP���V��M��ǋ�ԯ��\'Պ<��#�t>F�pGhC��Ь�֬Gy5&�\Z?Dg\nL���phԂ#��lf����6\'wE�]:���}����(�RHOn����M�f�q�`�6e�_������@��G�q}��Qj7�vV�K�rj��n�I�cʑ��� \\�0��:,�H��+\Z#a��ZH�&蠐C1\\(��U���:�a���_)�[�3s�7���P���O)�i4X(��V\n�A2��u*�/�� ���B�8E�P��ې�#,|Z�?�Ez��PI˱�z&+L׽^خۮ����V\r9^�$kcۍ��ƘU���-Aᨯ��rq����2�W�c3�i�@�3>���C��IH~�����>}ܷ�i�r�31ʌ��s���c|�;.L)|A�J��Xm�%���Ƀ�H���[�����|8�z�[s�*\nwqx[�5�њ��m��}���^��n�A�Q+��^��\n_+Y�ڽ!z��VwG�,���5�92�%�9�ɯzk��95��5P�$�o�$i�kq���.���⺹P\\�.�/���Bq-/W<����B�\\h����\rݖ�>�&\rխm#�6�}��y�Q��Ҡ��F�\n���Ci;/���g\nF�˛�GHx:����EZ}j������rَG��Si���F�4�ih251��Zi��<9A&M�\'������ݣ�M��QO�Ng)M��2��t��	��̼��Wg�X+j$$�����B��{q��v���Ne\rih���\0D9�7�1ϖv�_3(Rxq�\Z��<�m���Ck\'���\'��=<xc�N},8�\\*��b��KHV��1����8�Q8��s�?TF��؏Yx�`�TPy��Vr�\Z��z�H:;�1�!\Z|G������oPK�E�}\0\0�\0\0PK\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0meta.xml<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<office:document-meta xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" office:version=\"1.1\"><office:meta><meta:generator>OpenOffice.org/2.4$Linux OpenOffice.org_project/680m17$Build-9310</meta:generator><meta:initial-creator>Merijn Schering</meta:initial-creator><meta:creation-date>2008-08-26T09:26:02</meta:creation-date><meta:editing-cycles>0</meta:editing-cycles><meta:editing-duration>PT0S</meta:editing-duration><meta:user-defined meta:name=\"Info 1\"/><meta:user-defined meta:name=\"Info 2\"/><meta:user-defined meta:name=\"Info 3\"/><meta:user-defined meta:name=\"Info 4\"/><meta:document-statistic meta:table-count=\"0\" meta:image-count=\"0\" meta:object-count=\"0\" meta:page-count=\"1\" meta:paragraph-count=\"0\" meta:word-count=\"0\" meta:character-count=\"0\"/></office:meta></office:document-meta>PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Thumbnails/thumbnail.png��s���b``���p	�[8؀������{�8�T�y{i#\'�ρ\r|?�?���t�C��Ûw��~�2��9K&xrrV��o�ʓ����Ԏ_y2cTpTp�����3\n�*L���~.��\0PK�׃�|\0\0\0�\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�YQs�8~�_��;Jo�ʴ��챥����7��ձ2�S�ߟ�N�K	~�Mlɒ�믫����T��~^��@Fb~�=N������?�q6�h��1]Q�5-Qg�]�f���K�h\"S�j\n�jꠉ	�Ͷ���M�,{��x��Z\'�ju�\\�/�(�����Uվ�,\rP̢�����oU!�\"�!;�UvQ�]V������o\\S�Z?l�o]�\n��J�!6�9����x������k^Ѿ�{~�z_�`�m��uBo\"��V�+�p�}��\"��z�^����(ԋ\"ᗍ�Ɵ�d��|Qx�z��H��.GR�Ag��Ԗ�)\"&���)��\'��\n�1�}�g����Wb�T\"�\n�]_G��C�!ׇy�nUiI��L0_����Q����\'S�Uє��\\��O��V�h_�4\Z��/�D�Qk���_�8ٿ�	Iڎ���}a�vY�Q��׎�Sc�h�����&��y�/ b9����d���3���I6a\n�N��C�=û��?�A�{��T���o<Ti<��1%���ryLB����%�-N�z��p�\\��n$�&3�G(tO�t񯤬��8��L�url���~�t��J���xW�A�p��Q�Y���N����P�[��f.|e�0�(���n(QlS<��z[S��	&�^��#[tp���]��D{��~�1QGa�A�2�N�C�A��\'o1�7�F勰͙xV�t�tƃ�[zt������p$����#`!\n��AS��?A��.n�t}[�u��d��z�O�~T o�f��5%�˄���\r)��Q����U����|�b\nt�Swt߾���\"Щ�4�y4�c��U����o�ã�W�e�/�m�$-�]�����TJ�&���S�`M����������J��l9���°���[T���EY����nw�?��.[�BL���V�I�d ȭ�.�R��1pV�����D��^�ȍ�L���M�[�I߶Ӿk*�N�W�6����ɂ��W0��ZKSTQ��E\'W�uAUB5�+���_���D��9.��D;L�p�{*��:f\",h�����H��fk�6�)ҋ{&R��س�P#0u�L0O8�f��M��&}rt6��oY��bkL������	\nyj�T�\\Nu�Q��,x�KL���ީ����2��F����t��нXQ��$R�����j;o��|���L��PKt����\0\0h\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml��Kj�0@�=���V�U1q-��&���f��W��6��X;	��F#�h��[S�0���Oͣ��)�k7v�c�^����aa����Ӡ�����HѵHS��\"��Z��^%��ۯ��ɴ|�.�A���x�.2�5�|�	�h��;�7GWs�h�,.��dL����B�%�My�n�c�� �Y\'�@,���`��(U�q:b�bqW�`<0�R�O �G?F�r7=�^�ޛbpmaD���-*긓��_PrS�4I7�Z��O�HN�z����b��K|0H�c-2��x��d7�!ɧa�87|��\"s�ϩ]����PK5b�9>\0\0J\0\0PK\0\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Configurations2/statusbar/PK\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/floater/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0J\0\0Configurations2/progressbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/menubar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/toolbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/images/Bitmaps/PK\0\0\0\0K;\Z9�\0=@�\0\0s	\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0content.xmlPK\0\0\0\0K;\Z9�E�}\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0styles.xmlPK\0\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0meta.xmlPK\0\0\0\0K;\Z9�׃�|\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Thumbnails/thumbnail.pngPK\0\0\0\0K;\Z9t����\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0settings.xmlPK\0\0\0\0K;\Z95b�9>\0\0J\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0�\0\07\0\0\0\0','odt');
/*!40000 ALTER TABLE `fs_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fs_versions`
--

DROP TABLE IF EXISTS `fs_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fs_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `size_bytes` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_versions`
--

LOCK TABLES `fs_versions` WRITE;
/*!40000 ALTER TABLE `fs_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_address_format`
--

DROP TABLE IF EXISTS `go_address_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_address_format` (
  `id` int(11) NOT NULL,
  `format` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_address_format`
--

LOCK TABLES `go_address_format` WRITE;
/*!40000 ALTER TABLE `go_address_format` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_address_format` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_advanced_searches`
--

DROP TABLE IF EXISTS `go_advanced_searches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_advanced_searches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_advanced_searches`
--

LOCK TABLES `go_advanced_searches` WRITE;
/*!40000 ALTER TABLE `go_advanced_searches` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_advanced_searches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_cache`
--

DROP TABLE IF EXISTS `go_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_cache` (
  `user_id` int(11) NOT NULL,
  `key` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`key`),
  KEY `mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cache`
--

LOCK TABLES `go_cache` WRITE;
/*!40000 ALTER TABLE `go_cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_cf_setting_tabs`
--

DROP TABLE IF EXISTS `go_cf_setting_tabs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL,
  PRIMARY KEY (`cf_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cf_setting_tabs`
--

LOCK TABLES `go_cf_setting_tabs` WRITE;
/*!40000 ALTER TABLE `go_cf_setting_tabs` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_cf_setting_tabs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_clients`
--

DROP TABLE IF EXISTS `go_clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `footprint` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `in_use` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_footprint` (`footprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_clients`
--

LOCK TABLES `go_clients` WRITE;
/*!40000 ALTER TABLE `go_clients` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_countries`
--

DROP TABLE IF EXISTS `go_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_countries` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso_code_2` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `iso_code_3` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_countries`
--

LOCK TABLES `go_countries` WRITE;
/*!40000 ALTER TABLE `go_countries` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_cron`
--

DROP TABLE IF EXISTS `go_cron`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `minutes` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `hours` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `monthdays` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*',
  `months` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*',
  `weekdays` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*',
  `years` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*',
  `job` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `runonce` tinyint(1) NOT NULL DEFAULT 0,
  `nextrun` int(11) NOT NULL DEFAULT 0,
  `lastrun` int(11) NOT NULL DEFAULT 0,
  `completedat` int(11) NOT NULL DEFAULT 0,
  `error` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `autodestroy` tinyint(1) NOT NULL DEFAULT 0,
  `params` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nextrun_active` (`nextrun`,`active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron`
--

LOCK TABLES `go_cron` WRITE;
/*!40000 ALTER TABLE `go_cron` DISABLE KEYS */;
INSERT INTO `go_cron` VALUES (1,'Calendar publisher',1,'0','*','*','*','*','*','GO\\Calendar\\Cron\\CalendarPublisher',0,1641207600,0,0,NULL,0,'[]'),(2,'Contract Expiry Notification Cron',1,'2','7','*','*','*','*','GO\\Projects2\\Cron\\IncomeNotification',0,1641279720,0,0,NULL,0,'[]'),(3,'Close inactive tickets',1,'0','2','*','*','*','*','GO\\Tickets\\Cron\\CloseInactive',0,1641261600,0,0,NULL,0,'[]'),(4,'Ticket reminders',1,'*/5','*','*','*','*','*','GO\\Tickets\\Cron\\Reminder',0,1641205800,0,0,NULL,0,'[]'),(5,'Import tickets from IMAP',1,'0,5,10,15,20,25,30,35,40,45,50,55','*','*','*','*','*','GO\\Tickets\\Cron\\ImportImap',0,1641205800,0,0,NULL,0,'[]'),(6,'Sent tickets due reminder',1,'0','1','*','*','*','*','GO\\Tickets\\Cron\\DueMailer',0,1641258000,0,0,NULL,0,'[]');
/*!40000 ALTER TABLE `go_cron` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_cron_groups`
--

DROP TABLE IF EXISTS `go_cron_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_cron_groups` (
  `cronjob_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron_groups`
--

LOCK TABLES `go_cron_groups` WRITE;
/*!40000 ALTER TABLE `go_cron_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_cron_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_cron_users`
--

DROP TABLE IF EXISTS `go_cron_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_cron_users` (
  `cronjob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron_users`
--

LOCK TABLES `go_cron_users` WRITE;
/*!40000 ALTER TABLE `go_cron_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_cron_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_holidays`
--

DROP TABLE IF EXISTS `go_holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `region` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `free_day` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `region` (`region`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_holidays`
--

LOCK TABLES `go_holidays` WRITE;
/*!40000 ALTER TABLE `go_holidays` DISABLE KEYS */;
INSERT INTO `go_holidays` VALUES (1,'2022-01-01','New Years Day','en',1),(2,'2022-01-06','Twelfth Day','en',1),(3,'2022-05-01','May Day','en',1),(4,'2022-08-15','Assumption Day','en',1),(5,'2022-10-03','German Unification Day','en',1),(6,'2022-10-31','Reformation Day','en',1),(7,'2022-11-01','All Saints\' Day','en',1),(8,'2022-12-25','Christmas Day','en',1),(9,'2022-12-26','Boxing Day','en',1),(10,'2022-02-28','Shrove Monday','en',1),(11,'2022-03-01','Shrove Tuesday','en',1),(12,'2022-03-02','Ash Wednesday','en',1),(13,'2022-04-15','Good Friday','en',1),(14,'2022-04-17','Easter Sunday','en',1),(15,'2022-04-18','Easter Monday','en',1),(16,'2022-05-26','Ascension Day','en',1),(17,'2022-06-05','Whit Sunday','en',1),(18,'2022-06-06','Whit Monday','en',1),(19,'2022-06-16','Feast of Corpus Christi','en',1),(20,'2022-11-16','Penance Day','en',1);
/*!40000 ALTER TABLE `go_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_em_links`
--

DROP TABLE IF EXISTS `go_links_em_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_em_links` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_em_links`
--

LOCK TABLES `go_links_em_links` WRITE;
/*!40000 ALTER TABLE `go_links_em_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_em_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_fs_files`
--

DROP TABLE IF EXISTS `go_links_fs_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_fs_files` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_fs_files`
--

LOCK TABLES `go_links_fs_files` WRITE;
/*!40000 ALTER TABLE `go_links_fs_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_fs_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_fs_folders`
--

DROP TABLE IF EXISTS `go_links_fs_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_fs_folders` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_fs_folders`
--

LOCK TABLES `go_links_fs_folders` WRITE;
/*!40000 ALTER TABLE `go_links_fs_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_fs_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_ta_tasks`
--

DROP TABLE IF EXISTS `go_links_ta_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_ta_tasks` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_ta_tasks`
--

LOCK TABLES `go_links_ta_tasks` WRITE;
/*!40000 ALTER TABLE `go_links_ta_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ta_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_log`
--

DROP TABLE IF EXISTS `go_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `model_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `controller_route` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `jsonData` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_log`
--

LOCK TABLES `go_log` WRITE;
/*!40000 ALTER TABLE `go_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_reminders`
--

DROP TABLE IF EXISTS `go_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` int(11) NOT NULL,
  `vtime` int(11) NOT NULL DEFAULT 0,
  `snooze_time` int(11) NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT 0,
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_reminders`
--

LOCK TABLES `go_reminders` WRITE;
/*!40000 ALTER TABLE `go_reminders` DISABLE KEYS */;
INSERT INTO `go_reminders` VALUES (1,7,57,1,'Call: Smith Inc. (Q22000001)',1641551211,0,7200,0,NULL),(2,8,57,1,'Call: ACME Corporation (Q22000002)',1641551211,0,7200,0,NULL);
/*!40000 ALTER TABLE `go_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_reminders_users`
--

DROP TABLE IF EXISTS `go_reminders_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_reminders_users` (
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mail_sent` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`reminder_id`,`user_id`),
  KEY `user_id_time` (`user_id`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_reminders_users`
--

LOCK TABLES `go_reminders_users` WRITE;
/*!40000 ALTER TABLE `go_reminders_users` DISABLE KEYS */;
INSERT INTO `go_reminders_users` VALUES (1,1,1641551211,0),(2,1,1641551211,0);
/*!40000 ALTER TABLE `go_reminders_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_saved_exports`
--

DROP TABLE IF EXISTS `go_saved_exports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_saved_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `export_columns` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orientation` enum('V','H') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT 1,
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_saved_exports`
--

LOCK TABLES `go_saved_exports` WRITE;
/*!40000 ALTER TABLE `go_saved_exports` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_saved_exports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_saved_search_queries`
--

DROP TABLE IF EXISTS `go_saved_search_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_saved_search_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sql` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_saved_search_queries`
--

LOCK TABLES `go_saved_search_queries` WRITE;
/*!40000 ALTER TABLE `go_saved_search_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_saved_search_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_search_sync`
--

DROP TABLE IF EXISTS `go_search_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_search_sync` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_sync_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_search_sync`
--

LOCK TABLES `go_search_sync` WRITE;
/*!40000 ALTER TABLE `go_search_sync` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_search_sync` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_settings`
--

DROP TABLE IF EXISTS `go_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_settings`
--

LOCK TABLES `go_settings` WRITE;
/*!40000 ALTER TABLE `go_settings` DISABLE KEYS */;
INSERT INTO `go_settings` VALUES (0,'uuid_namespace','fa8ce07d-3df5-4fb4-b3b8-84aac6761e87'),(2,'email_always_request_notification','0'),(2,'email_always_respond_to_notifications','0'),(2,'email_font_size','14px'),(2,'email_show_bcc','0'),(2,'email_show_cc','1'),(2,'email_show_from','1'),(2,'email_skip_unknown_recipients','0'),(2,'email_sort_email_addresses_by_time','1'),(2,'email_use_plain_text_markup','0'),(3,'email_always_request_notification','0'),(3,'email_always_respond_to_notifications','0'),(3,'email_font_size','14px'),(3,'email_show_bcc','0'),(3,'email_show_cc','1'),(3,'email_show_from','1'),(3,'email_skip_unknown_recipients','0'),(3,'email_sort_email_addresses_by_time','1'),(3,'email_use_plain_text_markup','0'),(4,'email_always_request_notification','0'),(4,'email_always_respond_to_notifications','0'),(4,'email_font_size','14px'),(4,'email_show_bcc','0'),(4,'email_show_cc','1'),(4,'email_show_from','1'),(4,'email_skip_unknown_recipients','0'),(4,'email_sort_email_addresses_by_time','1'),(4,'email_use_plain_text_markup','0');
/*!40000 ALTER TABLE `go_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_state`
--

DROP TABLE IF EXISTS `go_state`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_state` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`name`),
  CONSTRAINT `go_state_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_state`
--

LOCK TABLES `go_state` WRITE;
/*!40000 ALTER TABLE `go_state` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_templates`
--

DROP TABLE IF EXISTS `go_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `content` longblob NOT NULL,
  `filename` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extension` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_templates`
--

LOCK TABLES `go_templates` WRITE;
/*!40000 ALTER TABLE `go_templates` DISABLE KEYS */;
INSERT INTO `go_templates` VALUES (1,1,0,'Default',9,'Message-ID: <198895b066a76fbd04378369ddf92822@swift.generated>\r\nDate: Mon, 03 Jan 2022 10:26:14 +0000\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_1641205574_84ad48335f0855f575d32e51642d44cf_=_\"\r\nX-Mailer: Group-Office (6.5.96)\r\n\r\n\r\n--_=_swift_1641205574_84ad48335f0855f575d32e51642d44cf_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nHi {contact:firstName},\r\n\r\n{body}\r\n\r\nBest regards\r\n\r\n\r\n{user:displayName}\r\n\r\n--_=_swift_1641205574_84ad48335f0855f575d32e51642d44cf_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nHi<gotpl if=3D\"contact:firstName\"> {contact:firstName},</gotpl><br />\r\n<br />\r\n{body}<br />\r\n<br />\r\nBest regards<br />\r\n<br />\r\n<br />\r\n{user:displayName}<br />\r\n\r\n--_=_swift_1641205574_84ad48335f0855f575d32e51642d44cf_=_--\r\n',NULL,''),(2,1,1,'Letter',56,'PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"�X�H���Bk�RbG����	+��73z�yb����r�� �<�t�p>�[0�������v��\ZA��SH���]57�J�d���+���r��!�Q�NS�+��6������d�&c鑴�8���G�S��s�<C��q����PKvՎ��\0\0\0�\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlŐ�N�0��<E�;K�M��i���x��FJ�*-}{��;Q��&qK������ݧ�����|!V��5UƟ\n�~x��H�|�<r@���n��5��\"�{�C!��\\)�\r:���S��� �o8)�k���C�:�U@1��1-�٭�ƭ�P��42���N~���N���B�C/؋Wr0	t����2ˤ\Z��;\\�a����D�\\�G�疚`ߠo�;�]d�o��\'�2jq-�\r�QW�rs�[��߿�~u����PK�I��\0\0w\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�V[o�0~߯����B�MM+Ƅ@B����!^۲���g����d	c���>�������6!�3\Z�޵k[��,�t�?�&W}ے\nh�Q�;$��/w�P�A���T�����CǑa��׌#���L$��Tl����\")��	q|��:	`jߗZ�8�;�Ѐ���q}7<ηSH�� ��\r�!%�Z\Z\r�ɗ�V���A��?\n3d8b٘Q%)����B[���s�*6��QY�R\"-61m�5a�cJC�q�9\\%�a�o3��1��@�P\Z��gj-�J#B �Hb8Y�GTts!#L����7*(��R�-%��f/#@7Z�>�u��:����:�\\-����S8��{^�M�x��=�H}�G��^��y��ɛ��۠��@�_e�\r��(;�@�ݾ�c�3TH���Q-�HYsL��\\{�_(��$��EX�-M�L�6q�Oڅu��/�=�V1�ժg��B��\'1���`�m�k����ҥ��v� m�U)��A�������?�D����5�����W����2���n��߄�KU���*���&�,�ʚb\Z�.4!0�tr���+�n]Z�\"MV��i�9�����N�����c:�T=�-ޮF\rҌFh�޲�����(h�F��D��i䐾���k:���X[�ܖL�����T��=Z�;eS~���PKT̓�\0\0E\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�W[o�0~�WDy^�tE�5P\r���b���Ib�%�O��i�;����6n�`}�{|>��Ή�����H��X��,�a\0�j�U>?]��^��E�Z�,\\�\r��g�u�4�$(��&zVF%� �IN��:��2�Y�)tM؍0��@,�(����e�H��4y��w\\�IO#���k^�~��}�+)z\\��ZV\ZM�Z�)Z^I�\Z����3�(����(w��[g���/5[��l�4W��Ɋ�Yh��Qz\Z\r������z�I��=o�n_4�1}M%5�\n8Z�\Z�`a4�(ڣར���8�c��\\���Ed\0�G�9�&� �Qp�a�5z�>Ɵt�3\",��.7�n�>6%��]�D�E$��S�k)�o��L3n5�d�+G�Q���g7ڜ $�bB��v���9�!@s\Z,u���̸��}q(���t�����|����pv��B�n������=�����/�\rX��0������,���j%Y�h���������rP��$�fe~�W�L_N\\UR���x�*���b1F�һ&�=*�\Z��\Z�7�\n7F�\r��7�ϋ-u��e%�[��t8�K\"Z����o�_�i�\r4����\\/[�+��Μ��\\����kr�bڄ+��G�R0�6�}�<yF*�]��\\F}�mJ��;PKm�Ø\0\0�\r\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��טd���)�n�f霨XH�����-4�;������b��1�:��)�)��;v�.7�H(���wБ$�w�5�\0\r�\".u���&u+S�c���G+1��H�8\ZO^�Қ���4�2���Wܞ���_z�/!{����b}_sz�C�Q��bo^~<�T���z�7n^��M;�Mq�0侟��6�Y�z�ͤ�L�z�^�$�\0PKI���\0\0\0j\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xml}��N� ��}�����M�v��]����w��mh�p��^�[un����C1ݪ&ـu��%�A	h��R�J�����8�u͛VC�v�д�(�a���h[�KpIiǄ)��{�0vb\r��,:4��U܇Ү��⃯\0_r�x^s�q/L�`D{e-���M�C\n�w�f��J����+�_���Y8vr��@u]�uy�B~�_�O���U	@U�W3a�{�� `������~1CUpLRr��тNX>ft��V�?������jV���׆Q����g�p��a�K	���?E��j����4O�(F&,��\ZG>8b�?��Ҹ�P���U_PKt�G\0\0�\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0�D�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0�D�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0�D�BvՎ��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0�D�B�I��\0\0w\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0�D�BT̓�\0\0E\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0H\0\0word/styles.xmlPK\0\0\0�D�Bm�Ø\0\0�\r\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0)\0\0word/document.xmlPK\0\0\0�D�BI���\0\0\0j\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0docProps/app.xmlPK\0\0\0�D�Bt�G\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\"\0\0docProps/core.xmlPK\0\0\0�D�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0\0\0\0\0',NULL,'docx');
/*!40000 ALTER TABLE `go_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_working_weeks`
--

DROP TABLE IF EXISTS `go_working_weeks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_working_weeks` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `mo_work_hours` double NOT NULL DEFAULT 8,
  `tu_work_hours` double NOT NULL DEFAULT 8,
  `we_work_hours` double NOT NULL DEFAULT 8,
  `th_work_hours` double NOT NULL DEFAULT 8,
  `fr_work_hours` double NOT NULL DEFAULT 8,
  `sa_work_hours` double NOT NULL DEFAULT 0,
  `su_work_hours` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_working_weeks`
--

LOCK TABLES `go_working_weeks` WRITE;
/*!40000 ALTER TABLE `go_working_weeks` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_working_weeks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `googleauth_secret`
--

DROP TABLE IF EXISTS `googleauth_secret`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `googleauth_secret` (
  `userId` int(11) NOT NULL,
  `secret` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`userId`),
  KEY `user` (`userId`),
  CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `googleauth_secret`
--

LOCK TABLES `googleauth_secret` WRITE;
/*!40000 ALTER TABLE `googleauth_secret` DISABLE KEYS */;
/*!40000 ALTER TABLE `googleauth_secret` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `history_log_entry`
--

DROP TABLE IF EXISTS `history_log_entry`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history_log_entry` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `action` int(11) DEFAULT NULL,
  `description` varchar(384) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `aclId` int(11) DEFAULT NULL,
  `removeAcl` tinyint(1) NOT NULL DEFAULT 0,
  `entityTypeId` int(11) NOT NULL,
  `entityId` int(11) DEFAULT NULL,
  `remoteIp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_log_entry_core_user_idx` (`createdBy`),
  KEY `fk_log_entry_core_acl1_idx` (`aclId`),
  KEY `fk_log_entry_core_entity1_idx` (`entityTypeId`),
  KEY `entityId` (`entityId`),
  KEY `history_log_entry_createdAt_index` (`createdAt`),
  CONSTRAINT `fk_log_entry_core_acl1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_entity1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_user` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history_log_entry`
--

LOCK TABLES `history_log_entry` WRITE;
/*!40000 ALTER TABLE `history_log_entry` DISABLE KEYS */;
/*!40000 ALTER TABLE `history_log_entry` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_leave_days`
--

DROP TABLE IF EXISTS `ld_leave_days`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_leave_days` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `first_date` int(11) NOT NULL DEFAULT 0,
  `last_date` int(11) NOT NULL DEFAULT 0,
  `from_time` time DEFAULT NULL,
  `n_hours` double NOT NULL DEFAULT 0,
  `n_nat_holiday_hours` double NOT NULL DEFAULT 0,
  `description` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `ld_credit_type_id` int(11) NOT NULL,
  `special_budget_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ld_leave_day_special_idx` (`special_budget_id`),
  CONSTRAINT `fk_ld_leave_day_special_idx` FOREIGN KEY (`special_budget_id`) REFERENCES `ld_special_leave_budgets` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_leave_days`
--

LOCK TABLES `ld_leave_days` WRITE;
/*!40000 ALTER TABLE `ld_leave_days` DISABLE KEYS */;
/*!40000 ALTER TABLE `ld_leave_days` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_special_leave_budgets`
--

DROP TABLE IF EXISTS `ld_special_leave_budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_special_leave_budgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `agreement_id` int(10) unsigned NOT NULL,
  `activity_id` int(10) unsigned NOT NULL,
  `n_hours` smallint(5) unsigned NOT NULL,
  `start_date` date DEFAULT NULL,
  `finish_date` date DEFAULT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ld_special_leave_budget_activity_idx` (`activity_id`),
  KEY `fk_ld_special_leave_budget_agreement_idx` (`agreement_id`),
  CONSTRAINT `fk_ld_special_leave_budget_activity_idx` FOREIGN KEY (`activity_id`) REFERENCES `business_activity` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_ld_special_leave_budget_agreement_idx` FOREIGN KEY (`agreement_id`) REFERENCES `business_agreement` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_special_leave_budgets`
--

LOCK TABLES `ld_special_leave_budgets` WRITE;
/*!40000 ALTER TABLE `ld_special_leave_budgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `ld_special_leave_budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_address_list`
--

DROP TABLE IF EXISTS `newsletters_address_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletters_address_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entityTypeId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `entityTypeId` (`entityTypeId`) USING BTREE,
  CONSTRAINT `newsletters_address_list_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `newsletters_address_list_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_address_list`
--

LOCK TABLES `newsletters_address_list` WRITE;
/*!40000 ALTER TABLE `newsletters_address_list` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletters_address_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_address_list_entity`
--

DROP TABLE IF EXISTS `newsletters_address_list_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletters_address_list_entity` (
  `addressListId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `token` binary(40) NOT NULL,
  PRIMARY KEY (`addressListId`,`entityId`),
  CONSTRAINT `newsletters_address_list_entity_ibfk_1` FOREIGN KEY (`addressListId`) REFERENCES `newsletters_address_list` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_address_list_entity`
--

LOCK TABLES `newsletters_address_list_entity` WRITE;
/*!40000 ALTER TABLE `newsletters_address_list_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletters_address_list_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_newsletter`
--

DROP TABLE IF EXISTS `newsletters_newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletters_newsletter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addressListId` int(11) NOT NULL,
  `smtpAccountId` int(11) DEFAULT NULL,
  `startedAt` datetime NOT NULL,
  `finishedAt` datetime DEFAULT NULL,
  `lastMessageSentAt` datetime DEFAULT NULL,
  `subject` varchar(192) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `paused` tinyint(1) NOT NULL DEFAULT 0,
  `createdBy` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addressListId` (`addressListId`),
  KEY `newsletters_newsletter_ibfk_2` (`smtpAccountId`),
  KEY `createdBy` (`createdBy`),
  CONSTRAINT `newsletters_newsletter_ibfk_1` FOREIGN KEY (`addressListId`) REFERENCES `newsletters_address_list` (`id`) ON DELETE CASCADE,
  CONSTRAINT `newsletters_newsletter_ibfk_2` FOREIGN KEY (`smtpAccountId`) REFERENCES `core_smtp_account` (`id`) ON UPDATE SET NULL,
  CONSTRAINT `newsletters_newsletter_ibfk_3` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_newsletter`
--

LOCK TABLES `newsletters_newsletter` WRITE;
/*!40000 ALTER TABLE `newsletters_newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletters_newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_newsletter_attachment`
--

DROP TABLE IF EXISTS `newsletters_newsletter_attachment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletters_newsletter_attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blobId` binary(40) NOT NULL,
  `newsletterId` int(11) NOT NULL,
  `name` varchar(192) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inline` tinyint(1) NOT NULL DEFAULT 0,
  `attachment` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `blobId` (`blobId`),
  KEY `newsletterId` (`newsletterId`),
  CONSTRAINT `newsletters_newsletter_attachment_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `newsletters_newsletter_attachment_ibfk_2` FOREIGN KEY (`newsletterId`) REFERENCES `newsletters_newsletter` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_newsletter_attachment`
--

LOCK TABLES `newsletters_newsletter_attachment` WRITE;
/*!40000 ALTER TABLE `newsletters_newsletter_attachment` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletters_newsletter_attachment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletters_newsletter_entity`
--

DROP TABLE IF EXISTS `newsletters_newsletter_entity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `newsletters_newsletter_entity` (
  `newsletterId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `error` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`newsletterId`,`entityId`),
  CONSTRAINT `newsletters_newsletter_entity_ibfk_1` FOREIGN KEY (`newsletterId`) REFERENCES `newsletters_newsletter` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletters_newsletter_entity`
--

LOCK TABLES `newsletters_newsletter_entity` WRITE;
/*!40000 ALTER TABLE `newsletters_newsletter_entity` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletters_newsletter_entity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_note`
--

DROP TABLE IF EXISTS `notes_note`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noteBookId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`createdBy`),
  KEY `category_id` (`noteBookId`),
  KEY `modifiedBy` (`modifiedBy`),
  CONSTRAINT `notes_note_ibfk_1` FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_note_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `notes_note_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note`
--

LOCK TABLES `notes_note` WRITE;
/*!40000 ALTER TABLE `notes_note` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes_note` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_note_book`
--

DROP TABLE IF EXISTS `notes_note_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_note_book` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deletedAt` datetime DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `createdBy` (`createdBy`),
  CONSTRAINT `notes_note_book_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `notes_note_book_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note_book`
--

LOCK TABLES `notes_note_book` WRITE;
/*!40000 ALTER TABLE `notes_note_book` DISABLE KEYS */;
INSERT INTO `notes_note_book` VALUES (65,NULL,1,30,'Shared',NULL);
/*!40000 ALTER TABLE `notes_note_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_note_custom_fields`
--

DROP TABLE IF EXISTS `notes_note_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_note_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `notes_note_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note_custom_fields`
--

LOCK TABLES `notes_note_custom_fields` WRITE;
/*!40000 ALTER TABLE `notes_note_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes_note_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_note_image`
--

DROP TABLE IF EXISTS `notes_note_image`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_note_image` (
  `noteId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  PRIMARY KEY (`noteId`,`blobId`),
  KEY `blobId` (`blobId`),
  CONSTRAINT `notes_note_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `notes_note_image_ibfk_2` FOREIGN KEY (`noteId`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note_image`
--

LOCK TABLES `notes_note_image` WRITE;
/*!40000 ALTER TABLE `notes_note_image` DISABLE KEYS */;
/*!40000 ALTER TABLE `notes_note_image` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes_user_settings`
--

DROP TABLE IF EXISTS `notes_user_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notes_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultNoteBookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `defaultNoteBookId` (`defaultNoteBookId`),
  CONSTRAINT `notes_user_settings_ibfk_1` FOREIGN KEY (`defaultNoteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE,
  CONSTRAINT `notes_user_settings_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_user_settings`
--

LOCK TABLES `notes_user_settings` WRITE;
/*!40000 ALTER TABLE `notes_user_settings` DISABLE KEYS */;
INSERT INTO `notes_user_settings` VALUES (2,NULL),(3,NULL),(4,NULL);
/*!40000 ALTER TABLE `notes_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_default_resources`
--

DROP TABLE IF EXISTS `pr2_default_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_default_resources` (
  `template_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `budgeted_units` double NOT NULL DEFAULT 0,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  `apply_internal_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `apply_external_overtime` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`template_id`,`user_id`),
  KEY `fk_pm_user_fees_pm_templates1_idx` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_default_resources`
--

LOCK TABLES `pr2_default_resources` WRITE;
/*!40000 ALTER TABLE `pr2_default_resources` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_default_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_expense_budgets`
--

DROP TABLE IF EXISTS `pr2_expense_budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_expense_budgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `nett` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `supplier_company_id` int(11) DEFAULT NULL,
  `budget_category_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `comments` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `id_number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `quantity` float NOT NULL DEFAULT 1,
  `unit_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`),
  CONSTRAINT `pr2_expense_budgets_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_expense_budgets`
--

LOCK TABLES `pr2_expense_budgets` WRITE;
/*!40000 ALTER TABLE `pr2_expense_budgets` DISABLE KEYS */;
INSERT INTO `pr2_expense_budgets` VALUES (1,'Machinery',10000,0,1641205616,1641205616,1,NULL,NULL,2,'','',1,'',NULL);
/*!40000 ALTER TABLE `pr2_expense_budgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_expenses`
--

DROP TABLE IF EXISTS `pr2_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `nett` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `date` int(11) NOT NULL DEFAULT 0,
  `invoice_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mtime` int(11) NOT NULL,
  `expense_budget_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `fk_pr2_expenses_pr2_expense_budgets1_idx` (`expense_budget_id`),
  KEY `fk_pr2_expenses_pr2_projects1_idx` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_expenses`
--

LOCK TABLES `pr2_expenses` WRITE;
/*!40000 ALTER TABLE `pr2_expenses` DISABLE KEYS */;
INSERT INTO `pr2_expenses` VALUES (1,2,3000,21,1641205616,'1234','Rocket fuel',1641205616,NULL),(2,2,2000,21,1641205616,'1235','Fuse machine',1641205616,1);
/*!40000 ALTER TABLE `pr2_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_hours`
--

DROP TABLE IF EXISTS `pr2_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_hours` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `duration` int(11) NOT NULL DEFAULT 0,
  `type` int(11) NOT NULL DEFAULT 0,
  `date` int(11) NOT NULL DEFAULT 0,
  `units` double NOT NULL DEFAULT 0,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `income_id` int(11) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `project_id` int(11) DEFAULT NULL,
  `standard_task_id` int(11) DEFAULT NULL,
  `task_id` int(11) NOT NULL DEFAULT 0,
  `travel_distance` float NOT NULL DEFAULT 0,
  `travel_costs` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `income_id` (`income_id`),
  KEY `user_id` (`user_id`),
  KEY `fk_pr2_hours_pr2_projects1_idx` (`project_id`),
  KEY `fk_pr2_hours_pr2_standard_tasks1_idx` (`standard_task_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours`
--

LOCK TABLES `pr2_hours` WRITE;
/*!40000 ALTER TABLE `pr2_hours` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_hours_custom_fields`
--

DROP TABLE IF EXISTS `pr2_hours_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_hours_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `pr2_hours_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pr2_hours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours_custom_fields`
--

LOCK TABLES `pr2_hours_custom_fields` WRITE;
/*!40000 ALTER TABLE `pr2_hours_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_hours_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_income`
--

DROP TABLE IF EXISTS `pr2_income`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_income` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `amount` double NOT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `invoiceable` tinyint(1) NOT NULL DEFAULT 0,
  `period_start` int(11) NOT NULL DEFAULT 0,
  `period_end` int(11) NOT NULL DEFAULT 0,
  `paid_at` int(11) NOT NULL DEFAULT 0,
  `invoice_at` int(11) NOT NULL,
  `invoice_number` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL,
  `project_id` int(11) NOT NULL,
  `reference_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `is_contract` tinyint(1) NOT NULL DEFAULT 0,
  `contract_repeat_amount` int(11) NOT NULL DEFAULT 1,
  `contract_repeat_freq` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contract_end` int(11) NOT NULL DEFAULT 0,
  `contract_end_notification_days` int(11) NOT NULL DEFAULT 10,
  `contract_end_notification_active` tinyint(1) NOT NULL DEFAULT 0,
  `contract_end_notification_template` int(11) DEFAULT NULL,
  `contract_end_notification_sent` int(11) DEFAULT NULL,
  `contact` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_income`
--

LOCK TABLES `pr2_income` WRITE;
/*!40000 ALTER TABLE `pr2_income` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_income` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_income_items`
--

DROP TABLE IF EXISTS `pr2_income_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_income_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `income_id` int(11) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_income_items`
--

LOCK TABLES `pr2_income_items` WRITE;
/*!40000 ALTER TABLE `pr2_income_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_income_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_portlet_statuses`
--

DROP TABLE IF EXISTS `pr2_portlet_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_portlet_statuses` (
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_portlet_statuses`
--

LOCK TABLES `pr2_portlet_statuses` WRITE;
/*!40000 ALTER TABLE `pr2_portlet_statuses` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_portlet_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_projects`
--

DROP TABLE IF EXISTS `pr2_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `customer` varchar(201) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `threshold_mails` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `due_time` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) DEFAULT NULL,
  `contact` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `responsible_user_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `income_type` smallint(2) NOT NULL DEFAULT 1,
  `status_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `parent_project_id` int(11) NOT NULL DEFAULT 0,
  `default_distance` double DEFAULT NULL,
  `travel_costs` double NOT NULL DEFAULT 0,
  `reference_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `responsible_user_id` (`responsible_user_id`),
  KEY `fk_pr2_projects_pr2_statuses1_idx` (`status_id`),
  KEY `fk_pr2_projects_pr2_types1_idx` (`type_id`),
  KEY `fk_pr2_projects_pr2_templates1_idx` (`template_id`),
  KEY `contact_id` (`contact_id`),
  KEY `company_id` (`company_id`),
  CONSTRAINT `pr2_projects_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pr2_projects_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_projects`
--

LOCK TABLES `pr2_projects` WRITE;
/*!40000 ALTER TABLE `pr2_projects` DISABLE KEYS */;
INSERT INTO `pr2_projects` VALUES (1,1,107,'Demo','','Just a placeholder for sub projects.',NULL,1641205616,1641205616,NULL,1,1641205616,0,NULL,NULL,0,0,0,0,'Demo',1,1,2,1,0,NULL,0,''),(2,1,107,'[001] Develop Rocket 2000','ACME Corporation','Better range and accuracy',3,1641205616,1641205616,NULL,1,1641205616,1643884016,4,'Wile E. Coyote',0,0,0,0,'Demo/[001] Develop Rocket 2000',1,1,2,2,1,NULL,0,''),(3,1,107,'[001] Develop Rocket Launcher','ACME Corporation','Better range and accuracy',3,1641205616,1641205616,NULL,1,1641205616,1643884016,4,'Wile E. Coyote',0,0,0,0,'Demo/[001] Develop Rocket Launcher',1,1,2,2,1,NULL,0,'');
/*!40000 ALTER TABLE `pr2_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_projects_custom_fields`
--

DROP TABLE IF EXISTS `pr2_projects_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_projects_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `pr2_projects_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pr2_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_projects_custom_fields`
--

LOCK TABLES `pr2_projects_custom_fields` WRITE;
/*!40000 ALTER TABLE `pr2_projects_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_projects_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_resource_activity_rate`
--

DROP TABLE IF EXISTS `pr2_resource_activity_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_resource_activity_rate` (
  `activity_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `external_rate` float NOT NULL,
  PRIMARY KEY (`activity_id`,`employee_id`,`project_id`),
  KEY `fk_pr2_resource_activity_idx` (`project_id`,`employee_id`),
  CONSTRAINT `fk_pr2_resource_activity` FOREIGN KEY (`project_id`, `employee_id`) REFERENCES `pr2_resources` (`project_id`, `user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_resource_activity_rate`
--

LOCK TABLES `pr2_resource_activity_rate` WRITE;
/*!40000 ALTER TABLE `pr2_resource_activity_rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_resource_activity_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_resources`
--

DROP TABLE IF EXISTS `pr2_resources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_resources` (
  `project_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `budgeted_units` double NOT NULL DEFAULT 0,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  `apply_internal_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `apply_external_overtime` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`project_id`,`user_id`),
  KEY `fk_pm_user_fees_pm_projects1_idx` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_resources`
--

LOCK TABLES `pr2_resources` WRITE;
/*!40000 ALTER TABLE `pr2_resources` DISABLE KEYS */;
INSERT INTO `pr2_resources` VALUES (2,2,16,120,60,0,0),(2,3,100,80,40,0,0),(2,4,16,90,45,0,0),(3,3,16,80,40,0,0);
/*!40000 ALTER TABLE `pr2_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_settings`
--

DROP TABLE IF EXISTS `pr2_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_settings` (
  `userId` int(11) NOT NULL,
  `duplicateRecursively` tinyint(1) NOT NULL DEFAULT 0,
  `duplicateRecursivelyTasks` tinyint(1) NOT NULL DEFAULT 0,
  `duplicateRecursivelyFiles` tinyint(1) NOT NULL DEFAULT 0,
  `deleteProjectsRecursively` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_settings`
--

LOCK TABLES `pr2_settings` WRITE;
/*!40000 ALTER TABLE `pr2_settings` DISABLE KEYS */;
INSERT INTO `pr2_settings` VALUES (2,0,0,0,0),(3,0,0,0,0),(4,0,0,0,0),(5,0,0,0,0),(6,0,0,0,0);
/*!40000 ALTER TABLE `pr2_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_statuses`
--

DROP TABLE IF EXISTS `pr2_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `filterable` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_tree` tinyint(1) NOT NULL DEFAULT 1,
  `make_invoiceable` tinyint(1) NOT NULL DEFAULT 0,
  `not_for_postcalculation` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_statuses`
--

LOCK TABLES `pr2_statuses` WRITE;
/*!40000 ALTER TABLE `pr2_statuses` DISABLE KEYS */;
INSERT INTO `pr2_statuses` VALUES (1,'Ongoing',0,0,1,1,0,0,62),(2,'None',0,0,1,1,0,0,64),(3,'Complete',1,0,1,0,0,0,65);
/*!40000 ALTER TABLE `pr2_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_tasks`
--

DROP TABLE IF EXISTS `pr2_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `percentage_complete` tinyint(4) NOT NULL DEFAULT 0,
  `duration` double NOT NULL DEFAULT 60,
  `due_date` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `has_children` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_tasks`
--

LOCK TABLES `pr2_tasks` WRITE;
/*!40000 ALTER TABLE `pr2_tasks` DISABLE KEYS */;
INSERT INTO `pr2_tasks` VALUES (1,2,3,0,480,NULL,'Design',1,0,1),(2,2,3,100,480,NULL,'Functional design',2,1,0),(3,2,3,50,480,NULL,'Technical design',3,1,0),(4,2,3,0,480,NULL,'Implementation',4,0,1),(5,2,3,0,240,NULL,'Models',5,4,0),(6,2,3,0,120,NULL,'Controllers',6,4,0),(7,2,3,0,360,NULL,'Views',7,4,0),(8,2,3,0,480,NULL,'Testing',8,0,1),(9,2,2,0,480,NULL,'GUI',9,8,0),(10,2,2,0,480,NULL,'Security',10,8,0);
/*!40000 ALTER TABLE `pr2_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_templates`
--

DROP TABLE IF EXISTS `pr2_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `fields` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `project_type` tinyint(4) NOT NULL DEFAULT 0,
  `default_income_email_template` int(11) DEFAULT NULL,
  `default_status_id` int(11) NOT NULL,
  `default_type_id` int(11) DEFAULT NULL,
  `use_name_template` tinyint(1) NOT NULL DEFAULT 0,
  `name_template` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_in_tree` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pr2_templates_pr2_types1_idx` (`default_type_id`),
  KEY `fk_pr2_templates_pr2_statuses1_idx` (`default_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_templates`
--

LOCK TABLES `pr2_templates` WRITE;
/*!40000 ALTER TABLE `pr2_templates` DISABLE KEYS */;
INSERT INTO `pr2_templates` VALUES (1,1,'Projects folder',66,10,'','projects2/template-icons/folder.png',0,NULL,2,1,0,'',1),(2,1,'Standard project',68,11,'responsible_user_id,status_date,customer,budget_fees,contact,expenses','projects2/template-icons/project.png',1,NULL,1,1,0,'',0);
/*!40000 ALTER TABLE `pr2_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_templates_events`
--

DROP TABLE IF EXISTS `pr2_templates_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_templates_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_offset` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `reminder` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `new_template_id` int(11) NOT NULL DEFAULT 0,
  `template_id` int(11) NOT NULL,
  `for_manager` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pr2_templates_events_pr2_templates1_idx` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_templates_events`
--

LOCK TABLES `pr2_templates_events` WRITE;
/*!40000 ALTER TABLE `pr2_templates_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_templates_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_timers`
--

DROP TABLE IF EXISTS `pr2_timers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_timers` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `starttime` int(11) NOT NULL,
  PRIMARY KEY (`project_id`,`user_id`),
  KEY `project_id` (`user_id`,`starttime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_timers`
--

LOCK TABLES `pr2_timers` WRITE;
/*!40000 ALTER TABLE `pr2_timers` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_timers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_types`
--

DROP TABLE IF EXISTS `pr2_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `acl_book` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_types`
--

LOCK TABLES `pr2_types` WRITE;
/*!40000 ALTER TABLE `pr2_types` DISABLE KEYS */;
INSERT INTO `pr2_types` VALUES (1,'Default',1,59,61),(2,'Demo',1,107,108);
/*!40000 ALTER TABLE `pr2_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_content`
--

DROP TABLE IF EXISTS `site_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `ptime` int(11) NOT NULL DEFAULT 0,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `parent_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `template` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_child_template` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'markdown',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`,`site_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_content`
--

LOCK TABLES `site_content` WRITE;
/*!40000 ALTER TABLE `site_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_content_custom_fields`
--

DROP TABLE IF EXISTS `site_content_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_content_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `site_content_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_content_custom_fields`
--

LOCK TABLES `site_content_custom_fields` WRITE;
/*!40000 ALTER TABLE `site_content_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_content_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_menu`
--

DROP TABLE IF EXISTS `site_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `menu_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_menu`
--

LOCK TABLES `site_menu` WRITE;
/*!40000 ALTER TABLE `site_menu` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_menu_item`
--

DROP TABLE IF EXISTS `site_menu_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_menu_item` (
  `menu_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_children` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `target` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_menu_item`
--

LOCK TABLES `site_menu_item` WRITE;
/*!40000 ALTER TABLE `site_menu_item` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_menu_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_multifile_files`
--

DROP TABLE IF EXISTS `site_multifile_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_multifile_files` (
  `model_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`model_id`,`field_id`,`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_multifile_files`
--

LOCK TABLES `site_multifile_files` WRITE;
/*!40000 ALTER TABLE `site_multifile_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_multifile_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_sites`
--

DROP TABLE IF EXISTS `site_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `domain` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ssl` tinyint(1) NOT NULL DEFAULT 0,
  `mod_rewrite` tinyint(1) NOT NULL DEFAULT 0,
  `mod_rewrite_base_path` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `base_path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_sites`
--

LOCK TABLES `site_sites` WRITE;
/*!40000 ALTER TABLE `site_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `site_sites_custom_fields`
--

DROP TABLE IF EXISTS `site_sites_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `site_sites_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `site_sites_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_sites_custom_fields`
--

LOCK TABLES `site_sites_custom_fields` WRITE;
/*!40000 ALTER TABLE `site_sites_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `site_sites_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_announcements`
--

DROP TABLE IF EXISTS `su_announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `due_time` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `due_time` (`due_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_announcements`
--

LOCK TABLES `su_announcements` WRITE;
/*!40000 ALTER TABLE `su_announcements` DISABLE KEYS */;
INSERT INTO `su_announcements` VALUES (1,1,103,0,1641205615,1641205615,'Submit support ticket','Anyone can submit tickets to the support system here:<br /><br /><a href=\"/modules/site/index.php?r=tickets/externalpage/newTicket\">/modules/site/index.php?r=tickets/externalpage/newTicket</a><br /><br />Anonymous ticket posting can be disabled in the ticket module settings.'),(2,1,105,0,1641205615,1641205615,'Welcome to GroupOffice','This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!');
/*!40000 ALTER TABLE `su_announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_latest_read_announcement_records`
--

DROP TABLE IF EXISTS `su_latest_read_announcement_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_latest_read_announcement_records` (
  `user_id` int(11) NOT NULL,
  `announcement_id` int(11) DEFAULT NULL,
  `announcement_ctime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`),
  KEY `su_latest_read_announcement_records_su_announcements_id_fk` (`announcement_id`),
  CONSTRAINT `su_latest_read_announcement_records_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `su_latest_read_announcement_records_su_announcements_id_fk` FOREIGN KEY (`announcement_id`) REFERENCES `su_announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_latest_read_announcement_records`
--

LOCK TABLES `su_latest_read_announcement_records` WRITE;
/*!40000 ALTER TABLE `su_latest_read_announcement_records` DISABLE KEYS */;
/*!40000 ALTER TABLE `su_latest_read_announcement_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_notes`
--

DROP TABLE IF EXISTS `su_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_notes` (
  `user_id` int(11) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  CONSTRAINT `su_notes_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_notes`
--

LOCK TABLES `su_notes` WRITE;
/*!40000 ALTER TABLE `su_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `su_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_rss_feeds`
--

DROP TABLE IF EXISTS `su_rss_feeds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_rss_feeds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `su_rss_feeds_core_user_id_fk` (`user_id`),
  CONSTRAINT `su_rss_feeds_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_rss_feeds`
--

LOCK TABLES `su_rss_feeds` WRITE;
/*!40000 ALTER TABLE `su_rss_feeds` DISABLE KEYS */;
/*!40000 ALTER TABLE `su_rss_feeds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_visible_calendars`
--

DROP TABLE IF EXISTS `su_visible_calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_visible_calendars` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`calendar_id`),
  CONSTRAINT `su_visible_calendars_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_visible_calendars`
--

LOCK TABLES `su_visible_calendars` WRITE;
/*!40000 ALTER TABLE `su_visible_calendars` DISABLE KEYS */;
INSERT INTO `su_visible_calendars` VALUES (2,2),(3,1),(4,3);
/*!40000 ALTER TABLE `su_visible_calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `su_visible_lists`
--

DROP TABLE IF EXISTS `su_visible_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `su_visible_lists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`tasklist_id`),
  CONSTRAINT `su_visible_lists_core_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_visible_lists`
--

LOCK TABLES `su_visible_lists` WRITE;
/*!40000 ALTER TABLE `su_visible_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `su_visible_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_addressbook_user`
--

DROP TABLE IF EXISTS `sync_addressbook_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_addressbook_user` (
  `addressBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`addressBookId`,`userId`),
  KEY `userId` (`userId`),
  CONSTRAINT `sync_addressbook_user_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sync_addressbook_user_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_addressbook_user`
--

LOCK TABLES `sync_addressbook_user` WRITE;
/*!40000 ALTER TABLE `sync_addressbook_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_addressbook_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_calendar_user`
--

DROP TABLE IF EXISTS `sync_calendar_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_calendar_user` (
  `calendar_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_calendar` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`calendar_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_calendar_user`
--

LOCK TABLES `sync_calendar_user` WRITE;
/*!40000 ALTER TABLE `sync_calendar_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_calendar_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_devices`
--

DROP TABLE IF EXISTS `sync_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_devices` (
  `id` int(11) NOT NULL DEFAULT 0,
  `manufacturer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `software_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `uri` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UTC` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `vcalendar_version` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_devices`
--

LOCK TABLES `sync_devices` WRITE;
/*!40000 ALTER TABLE `sync_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_note_categories_user`
--

DROP TABLE IF EXISTS `sync_note_categories_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_note_categories_user` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_category` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`category_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_note_categories_user`
--

LOCK TABLES `sync_note_categories_user` WRITE;
/*!40000 ALTER TABLE `sync_note_categories_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_note_categories_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_settings`
--

DROP TABLE IF EXISTS `sync_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addressbook_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `tasklist_id` int(11) NOT NULL DEFAULT 0,
  `note_category_id` int(11) NOT NULL DEFAULT 0,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `server_is_master` tinyint(1) NOT NULL DEFAULT 1,
  `max_days_old` tinyint(4) NOT NULL DEFAULT 0,
  `delete_old_events` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_settings`
--

LOCK TABLES `sync_settings` WRITE;
/*!40000 ALTER TABLE `sync_settings` DISABLE KEYS */;
INSERT INTO `sync_settings` VALUES (2,0,0,0,0,0,1,0,1),(3,0,0,0,0,0,1,0,1),(4,0,0,0,0,0,1,0,1),(5,0,0,0,0,0,1,0,1),(6,0,0,0,0,0,1,0,1);
/*!40000 ALTER TABLE `sync_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_tasklist_user`
--

DROP TABLE IF EXISTS `sync_tasklist_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_tasklist_user` (
  `tasklist_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_tasklist` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`tasklist_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_tasklist_user`
--

LOCK TABLES `sync_tasklist_user` WRITE;
/*!40000 ALTER TABLE `sync_tasklist_user` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_tasklist_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sync_user_note_book`
--

DROP TABLE IF EXISTS `sync_user_note_book`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sync_user_note_book` (
  `noteBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`noteBookId`,`userId`),
  KEY `user` (`userId`),
  CONSTRAINT `sync_user_note_book_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_user_note_book`
--

LOCK TABLES `sync_user_note_book` WRITE;
/*!40000 ALTER TABLE `sync_user_note_book` DISABLE KEYS */;
/*!40000 ALTER TABLE `sync_user_note_book` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_categories`
--

DROP TABLE IF EXISTS `ta_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_categories`
--

LOCK TABLES `ta_categories` WRITE;
/*!40000 ALTER TABLE `ta_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ta_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_portlet_tasklists`
--

DROP TABLE IF EXISTS `ta_portlet_tasklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_portlet_tasklists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`tasklist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_portlet_tasklists`
--

LOCK TABLES `ta_portlet_tasklists` WRITE;
/*!40000 ALTER TABLE `ta_portlet_tasklists` DISABLE KEYS */;
INSERT INTO `ta_portlet_tasklists` VALUES (2,4),(3,2),(4,3);
/*!40000 ALTER TABLE `ta_portlet_tasklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_settings`
--

DROP TABLE IF EXISTS `ta_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_settings` (
  `user_id` int(11) NOT NULL,
  `reminder_days` int(11) NOT NULL DEFAULT 0,
  `reminder_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `remind` tinyint(1) NOT NULL DEFAULT 0,
  `default_tasklist_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_settings`
--

LOCK TABLES `ta_settings` WRITE;
/*!40000 ALTER TABLE `ta_settings` DISABLE KEYS */;
INSERT INTO `ta_settings` VALUES (1,0,'0',0,1),(2,0,'0',0,4),(3,0,'0',0,2),(4,0,'0',0,3),(5,0,'0',0,0),(6,0,'0',0,0);
/*!40000 ALTER TABLE `ta_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_tasklists`
--

DROP TABLE IF EXISTS `ta_tasklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_tasklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasklists`
--

LOCK TABLES `ta_tasklists` WRITE;
/*!40000 ALTER TABLE `ta_tasklists` DISABLE KEYS */;
INSERT INTO `ta_tasklists` VALUES (1,'System Administrator',1,95,22,3),(2,'Demo User',3,97,23,3),(3,'Linda Smith',4,98,24,3),(4,'Elmer Fudd',2,99,25,3);
/*!40000 ALTER TABLE `ta_tasklists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_tasks`
--

DROP TABLE IF EXISTS `ta_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `tasklist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL,
  `due_time` int(11) NOT NULL,
  `completion_time` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repeat_end_time` int(11) NOT NULL DEFAULT 0,
  `reminder` int(11) NOT NULL DEFAULT 0,
  `rrule` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1,
  `percentage_complete` tinyint(4) NOT NULL DEFAULT 0,
  `project_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `list_id` (`tasklist_id`),
  KEY `rrule` (`rrule`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasks`
--

LOCK TABLES `ta_tasks` WRITE;
/*!40000 ALTER TABLE `ta_tasks` DISABLE KEYS */;
INSERT INTO `ta_tasks` VALUES (1,'16e6662e-08a6-56d3-8cda-5f9641f3056a',2,1,1641205609,1641205609,1,1641205609,1641378409,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(2,'7616b2c0-1fda-5f1f-bfa6-886d067f2420',3,1,1641205609,1641205609,1,1641205609,1641292009,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(3,'39a7f4c5-98c2-5f67-8574-2851959b0fd3',4,1,1641205610,1641205610,1,1641205610,1641292010,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(4,'a29c3c81-633c-51be-b97c-a40a678b78d1',2,1,1641205610,1641205610,1,1641205610,1641292010,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(5,'021b6f1d-2f03-5232-8998-8527e457e6e0',3,1,1641205610,1641205610,1,1641205610,1641292010,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(6,'511e978b-eabc-5d60-bfe6-7ea9191dc08e',4,1,1641205610,1641205610,1,1641205610,1641292010,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(7,'0fec9871-ef4b-56a0-b756-e7952ecd9425',1,1,1641205611,1641205611,1,1641551211,1641551211,0,'Call: Smith Inc. (Q22000001)','','NEEDS-ACTION',0,1641551211,'',0,0,1,0,0),(8,'91b60873-38fb-5f8d-b76e-85d5df348b84',1,1,1641205611,1641205611,1,1641551211,1641551211,0,'Call: ACME Corporation (Q22000002)','','NEEDS-ACTION',0,1641551211,'',0,0,1,0,0);
/*!40000 ALTER TABLE `ta_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_tasks_custom_fields`
--

DROP TABLE IF EXISTS `ta_tasks_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_tasks_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ta_tasks_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ta_tasks` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasks_custom_fields`
--

LOCK TABLES `ta_tasks_custom_fields` WRITE;
/*!40000 ALTER TABLE `ta_tasks_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `ta_tasks_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_a`
--

DROP TABLE IF EXISTS `test_a`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_a` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `propA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a`
--

LOCK TABLES `test_a` WRITE;
/*!40000 ALTER TABLE `test_a` DISABLE KEYS */;
INSERT INTO `test_a` VALUES (31,'61d2cf7a6a762','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(32,'copy 1','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(33,'map-61d2cf7a2d279','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(34,'map-61d2cf7a34a4e','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(35,'map-61d2cf7a36c26','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(36,'map-61d2cf7a4a1df','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(37,'map-61d2cf7a4bffd','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(38,'map-61d2cf7a4dd54','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(39,'string 1 (copy of 31)','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(40,'string 1','2022-01-03 10:27:06','2022-01-03 10:27:06',NULL),(41,'string 1','2022-01-03 10:27:06','2022-01-03 10:27:07',NULL);
/*!40000 ALTER TABLE `test_a` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_a_has_many`
--

DROP TABLE IF EXISTS `test_a_has_many`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_a_has_many` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aId` int(11) NOT NULL,
  `propOfHasManyA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`aId`),
  KEY `aId` (`aId`),
  CONSTRAINT `test_a_has_many_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10073 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a_has_many`
--

LOCK TABLES `test_a_has_many` WRITE;
/*!40000 ALTER TABLE `test_a_has_many` DISABLE KEYS */;
INSERT INTO `test_a_has_many` VALUES (68,39,'string 5'),(69,39,'string 3'),(70,32,'copy 5'),(71,32,'copy 3'),(72,31,'61d2cf7a6a769'),(73,41,'string 5'),(74,41,'string 5'),(75,41,'string 5'),(76,41,'string 5'),(77,41,'string 5'),(78,41,'string 5'),(79,41,'string 5'),(80,41,'string 5'),(81,41,'string 5'),(82,41,'string 5'),(83,41,'string 5'),(84,41,'string 5'),(85,41,'string 5'),(86,41,'string 5'),(87,41,'string 5'),(88,41,'string 5'),(89,41,'string 5'),(90,41,'string 5'),(91,41,'string 5'),(92,41,'string 5'),(93,41,'string 5'),(94,41,'string 5'),(95,41,'string 5'),(96,41,'string 5'),(97,41,'string 5'),(98,41,'string 5'),(99,41,'string 5'),(100,41,'string 5'),(101,41,'string 5'),(102,41,'string 5'),(103,41,'string 5'),(104,41,'string 5'),(105,41,'string 5'),(106,41,'string 5'),(107,41,'string 5'),(108,41,'string 5'),(109,41,'string 5'),(110,41,'string 5'),(111,41,'string 5'),(112,41,'string 5'),(113,41,'string 5'),(114,41,'string 5'),(115,41,'string 5'),(116,41,'string 5'),(117,41,'string 5'),(118,41,'string 5'),(119,41,'string 5'),(120,41,'string 5'),(121,41,'string 5'),(122,41,'string 5'),(123,41,'string 5'),(124,41,'string 5'),(125,41,'string 5'),(126,41,'string 5'),(127,41,'string 5'),(128,41,'string 5'),(129,41,'string 5'),(130,41,'string 5'),(131,41,'string 5'),(132,41,'string 5'),(133,41,'string 5'),(134,41,'string 5'),(135,41,'string 5'),(136,41,'string 5'),(137,41,'string 5'),(138,41,'string 5'),(139,41,'string 5'),(140,41,'string 5'),(141,41,'string 5'),(142,41,'string 5'),(143,41,'string 5'),(144,41,'string 5'),(145,41,'string 5'),(146,41,'string 5'),(147,41,'string 5'),(148,41,'string 5'),(149,41,'string 5'),(150,41,'string 5'),(151,41,'string 5'),(152,41,'string 5'),(153,41,'string 5'),(154,41,'string 5'),(155,41,'string 5'),(156,41,'string 5'),(157,41,'string 5'),(158,41,'string 5'),(159,41,'string 5'),(160,41,'string 5'),(161,41,'string 5'),(162,41,'string 5'),(163,41,'string 5'),(164,41,'string 5'),(165,41,'string 5'),(166,41,'string 5'),(167,41,'string 5'),(168,41,'string 5'),(169,41,'string 5'),(170,41,'string 5'),(171,41,'string 5'),(172,41,'string 5'),(173,41,'string 5'),(174,41,'string 5'),(175,41,'string 5'),(176,41,'string 5'),(177,41,'string 5'),(178,41,'string 5'),(179,41,'string 5'),(180,41,'string 5'),(181,41,'string 5'),(182,41,'string 5'),(183,41,'string 5'),(184,41,'string 5'),(185,41,'string 5'),(186,41,'string 5'),(187,41,'string 5'),(188,41,'string 5'),(189,41,'string 5'),(190,41,'string 5'),(191,41,'string 5'),(192,41,'string 5'),(193,41,'string 5'),(194,41,'string 5'),(195,41,'string 5'),(196,41,'string 5'),(197,41,'string 5'),(198,41,'string 5'),(199,41,'string 5'),(200,41,'string 5'),(201,41,'string 5'),(202,41,'string 5'),(203,41,'string 5'),(204,41,'string 5'),(205,41,'string 5'),(206,41,'string 5'),(207,41,'string 5'),(208,41,'string 5'),(209,41,'string 5'),(210,41,'string 5'),(211,41,'string 5'),(212,41,'string 5'),(213,41,'string 5'),(214,41,'string 5'),(215,41,'string 5'),(216,41,'string 5'),(217,41,'string 5'),(218,41,'string 5'),(219,41,'string 5'),(220,41,'string 5'),(221,41,'string 5'),(222,41,'string 5'),(223,41,'string 5'),(224,41,'string 5'),(225,41,'string 5'),(226,41,'string 5'),(227,41,'string 5'),(228,41,'string 5'),(229,41,'string 5'),(230,41,'string 5'),(231,41,'string 5'),(232,41,'string 5'),(233,41,'string 5'),(234,41,'string 5'),(235,41,'string 5'),(236,41,'string 5'),(237,41,'string 5'),(238,41,'string 5'),(239,41,'string 5'),(240,41,'string 5'),(241,41,'string 5'),(242,41,'string 5'),(243,41,'string 5'),(244,41,'string 5'),(245,41,'string 5'),(246,41,'string 5'),(247,41,'string 5'),(248,41,'string 5'),(249,41,'string 5'),(250,41,'string 5'),(251,41,'string 5'),(252,41,'string 5'),(253,41,'string 5'),(254,41,'string 5'),(255,41,'string 5'),(256,41,'string 5'),(257,41,'string 5'),(258,41,'string 5'),(259,41,'string 5'),(260,41,'string 5'),(261,41,'string 5'),(262,41,'string 5'),(263,41,'string 5'),(264,41,'string 5'),(265,41,'string 5'),(266,41,'string 5'),(267,41,'string 5'),(268,41,'string 5'),(269,41,'string 5'),(270,41,'string 5'),(271,41,'string 5'),(272,41,'string 5'),(273,41,'string 5'),(274,41,'string 5'),(275,41,'string 5'),(276,41,'string 5'),(277,41,'string 5'),(278,41,'string 5'),(279,41,'string 5'),(280,41,'string 5'),(281,41,'string 5'),(282,41,'string 5'),(283,41,'string 5'),(284,41,'string 5'),(285,41,'string 5'),(286,41,'string 5'),(287,41,'string 5'),(288,41,'string 5'),(289,41,'string 5'),(290,41,'string 5'),(291,41,'string 5'),(292,41,'string 5'),(293,41,'string 5'),(294,41,'string 5'),(295,41,'string 5'),(296,41,'string 5'),(297,41,'string 5'),(298,41,'string 5'),(299,41,'string 5'),(300,41,'string 5'),(301,41,'string 5'),(302,41,'string 5'),(303,41,'string 5'),(304,41,'string 5'),(305,41,'string 5'),(306,41,'string 5'),(307,41,'string 5'),(308,41,'string 5'),(309,41,'string 5'),(310,41,'string 5'),(311,41,'string 5'),(312,41,'string 5'),(313,41,'string 5'),(314,41,'string 5'),(315,41,'string 5'),(316,41,'string 5'),(317,41,'string 5'),(318,41,'string 5'),(319,41,'string 5'),(320,41,'string 5'),(321,41,'string 5'),(322,41,'string 5'),(323,41,'string 5'),(324,41,'string 5'),(325,41,'string 5'),(326,41,'string 5'),(327,41,'string 5'),(328,41,'string 5'),(329,41,'string 5'),(330,41,'string 5'),(331,41,'string 5'),(332,41,'string 5'),(333,41,'string 5'),(334,41,'string 5'),(335,41,'string 5'),(336,41,'string 5'),(337,41,'string 5'),(338,41,'string 5'),(339,41,'string 5'),(340,41,'string 5'),(341,41,'string 5'),(342,41,'string 5'),(343,41,'string 5'),(344,41,'string 5'),(345,41,'string 5'),(346,41,'string 5'),(347,41,'string 5'),(348,41,'string 5'),(349,41,'string 5'),(350,41,'string 5'),(351,41,'string 5'),(352,41,'string 5'),(353,41,'string 5'),(354,41,'string 5'),(355,41,'string 5'),(356,41,'string 5'),(357,41,'string 5'),(358,41,'string 5'),(359,41,'string 5'),(360,41,'string 5'),(361,41,'string 5'),(362,41,'string 5'),(363,41,'string 5'),(364,41,'string 5'),(365,41,'string 5'),(366,41,'string 5'),(367,41,'string 5'),(368,41,'string 5'),(369,41,'string 5'),(370,41,'string 5'),(371,41,'string 5'),(372,41,'string 5'),(373,41,'string 5'),(374,41,'string 5'),(375,41,'string 5'),(376,41,'string 5'),(377,41,'string 5'),(378,41,'string 5'),(379,41,'string 5'),(380,41,'string 5'),(381,41,'string 5'),(382,41,'string 5'),(383,41,'string 5'),(384,41,'string 5'),(385,41,'string 5'),(386,41,'string 5'),(387,41,'string 5'),(388,41,'string 5'),(389,41,'string 5'),(390,41,'string 5'),(391,41,'string 5'),(392,41,'string 5'),(393,41,'string 5'),(394,41,'string 5'),(395,41,'string 5'),(396,41,'string 5'),(397,41,'string 5'),(398,41,'string 5'),(399,41,'string 5'),(400,41,'string 5'),(401,41,'string 5'),(402,41,'string 5'),(403,41,'string 5'),(404,41,'string 5'),(405,41,'string 5'),(406,41,'string 5'),(407,41,'string 5'),(408,41,'string 5'),(409,41,'string 5'),(410,41,'string 5'),(411,41,'string 5'),(412,41,'string 5'),(413,41,'string 5'),(414,41,'string 5'),(415,41,'string 5'),(416,41,'string 5'),(417,41,'string 5'),(418,41,'string 5'),(419,41,'string 5'),(420,41,'string 5'),(421,41,'string 5'),(422,41,'string 5'),(423,41,'string 5'),(424,41,'string 5'),(425,41,'string 5'),(426,41,'string 5'),(427,41,'string 5'),(428,41,'string 5'),(429,41,'string 5'),(430,41,'string 5'),(431,41,'string 5'),(432,41,'string 5'),(433,41,'string 5'),(434,41,'string 5'),(435,41,'string 5'),(436,41,'string 5'),(437,41,'string 5'),(438,41,'string 5'),(439,41,'string 5'),(440,41,'string 5'),(441,41,'string 5'),(442,41,'string 5'),(443,41,'string 5'),(444,41,'string 5'),(445,41,'string 5'),(446,41,'string 5'),(447,41,'string 5'),(448,41,'string 5'),(449,41,'string 5'),(450,41,'string 5'),(451,41,'string 5'),(452,41,'string 5'),(453,41,'string 5'),(454,41,'string 5'),(455,41,'string 5'),(456,41,'string 5'),(457,41,'string 5'),(458,41,'string 5'),(459,41,'string 5'),(460,41,'string 5'),(461,41,'string 5'),(462,41,'string 5'),(463,41,'string 5'),(464,41,'string 5'),(465,41,'string 5'),(466,41,'string 5'),(467,41,'string 5'),(468,41,'string 5'),(469,41,'string 5'),(470,41,'string 5'),(471,41,'string 5'),(472,41,'string 5'),(473,41,'string 5'),(474,41,'string 5'),(475,41,'string 5'),(476,41,'string 5'),(477,41,'string 5'),(478,41,'string 5'),(479,41,'string 5'),(480,41,'string 5'),(481,41,'string 5'),(482,41,'string 5'),(483,41,'string 5'),(484,41,'string 5'),(485,41,'string 5'),(486,41,'string 5'),(487,41,'string 5'),(488,41,'string 5'),(489,41,'string 5'),(490,41,'string 5'),(491,41,'string 5'),(492,41,'string 5'),(493,41,'string 5'),(494,41,'string 5'),(495,41,'string 5'),(496,41,'string 5'),(497,41,'string 5'),(498,41,'string 5'),(499,41,'string 5'),(500,41,'string 5'),(501,41,'string 5'),(502,41,'string 5'),(503,41,'string 5'),(504,41,'string 5'),(505,41,'string 5'),(506,41,'string 5'),(507,41,'string 5'),(508,41,'string 5'),(509,41,'string 5'),(510,41,'string 5'),(511,41,'string 5'),(512,41,'string 5'),(513,41,'string 5'),(514,41,'string 5'),(515,41,'string 5'),(516,41,'string 5'),(517,41,'string 5'),(518,41,'string 5'),(519,41,'string 5'),(520,41,'string 5'),(521,41,'string 5'),(522,41,'string 5'),(523,41,'string 5'),(524,41,'string 5'),(525,41,'string 5'),(526,41,'string 5'),(527,41,'string 5'),(528,41,'string 5'),(529,41,'string 5'),(530,41,'string 5'),(531,41,'string 5'),(532,41,'string 5'),(533,41,'string 5'),(534,41,'string 5'),(535,41,'string 5'),(536,41,'string 5'),(537,41,'string 5'),(538,41,'string 5'),(539,41,'string 5'),(540,41,'string 5'),(541,41,'string 5'),(542,41,'string 5'),(543,41,'string 5'),(544,41,'string 5'),(545,41,'string 5'),(546,41,'string 5'),(547,41,'string 5'),(548,41,'string 5'),(549,41,'string 5'),(550,41,'string 5'),(551,41,'string 5'),(552,41,'string 5'),(553,41,'string 5'),(554,41,'string 5'),(555,41,'string 5'),(556,41,'string 5'),(557,41,'string 5'),(558,41,'string 5'),(559,41,'string 5'),(560,41,'string 5'),(561,41,'string 5'),(562,41,'string 5'),(563,41,'string 5'),(564,41,'string 5'),(565,41,'string 5'),(566,41,'string 5'),(567,41,'string 5'),(568,41,'string 5'),(569,41,'string 5'),(570,41,'string 5'),(571,41,'string 5'),(572,41,'string 5'),(573,41,'string 5'),(574,41,'string 5'),(575,41,'string 5'),(576,41,'string 5'),(577,41,'string 5'),(578,41,'string 5'),(579,41,'string 5'),(580,41,'string 5'),(581,41,'string 5'),(582,41,'string 5'),(583,41,'string 5'),(584,41,'string 5'),(585,41,'string 5'),(586,41,'string 5'),(587,41,'string 5'),(588,41,'string 5'),(589,41,'string 5'),(590,41,'string 5'),(591,41,'string 5'),(592,41,'string 5'),(593,41,'string 5'),(594,41,'string 5'),(595,41,'string 5'),(596,41,'string 5'),(597,41,'string 5'),(598,41,'string 5'),(599,41,'string 5'),(600,41,'string 5'),(601,41,'string 5'),(602,41,'string 5'),(603,41,'string 5'),(604,41,'string 5'),(605,41,'string 5'),(606,41,'string 5'),(607,41,'string 5'),(608,41,'string 5'),(609,41,'string 5'),(610,41,'string 5'),(611,41,'string 5'),(612,41,'string 5'),(613,41,'string 5'),(614,41,'string 5'),(615,41,'string 5'),(616,41,'string 5'),(617,41,'string 5'),(618,41,'string 5'),(619,41,'string 5'),(620,41,'string 5'),(621,41,'string 5'),(622,41,'string 5'),(623,41,'string 5'),(624,41,'string 5'),(625,41,'string 5'),(626,41,'string 5'),(627,41,'string 5'),(628,41,'string 5'),(629,41,'string 5'),(630,41,'string 5'),(631,41,'string 5'),(632,41,'string 5'),(633,41,'string 5'),(634,41,'string 5'),(635,41,'string 5'),(636,41,'string 5'),(637,41,'string 5'),(638,41,'string 5'),(639,41,'string 5'),(640,41,'string 5'),(641,41,'string 5'),(642,41,'string 5'),(643,41,'string 5'),(644,41,'string 5'),(645,41,'string 5'),(646,41,'string 5'),(647,41,'string 5'),(648,41,'string 5'),(649,41,'string 5'),(650,41,'string 5'),(651,41,'string 5'),(652,41,'string 5'),(653,41,'string 5'),(654,41,'string 5'),(655,41,'string 5'),(656,41,'string 5'),(657,41,'string 5'),(658,41,'string 5'),(659,41,'string 5'),(660,41,'string 5'),(661,41,'string 5'),(662,41,'string 5'),(663,41,'string 5'),(664,41,'string 5'),(665,41,'string 5'),(666,41,'string 5'),(667,41,'string 5'),(668,41,'string 5'),(669,41,'string 5'),(670,41,'string 5'),(671,41,'string 5'),(672,41,'string 5'),(673,41,'string 5'),(674,41,'string 5'),(675,41,'string 5'),(676,41,'string 5'),(677,41,'string 5'),(678,41,'string 5'),(679,41,'string 5'),(680,41,'string 5'),(681,41,'string 5'),(682,41,'string 5'),(683,41,'string 5'),(684,41,'string 5'),(685,41,'string 5'),(686,41,'string 5'),(687,41,'string 5'),(688,41,'string 5'),(689,41,'string 5'),(690,41,'string 5'),(691,41,'string 5'),(692,41,'string 5'),(693,41,'string 5'),(694,41,'string 5'),(695,41,'string 5'),(696,41,'string 5'),(697,41,'string 5'),(698,41,'string 5'),(699,41,'string 5'),(700,41,'string 5'),(701,41,'string 5'),(702,41,'string 5'),(703,41,'string 5'),(704,41,'string 5'),(705,41,'string 5'),(706,41,'string 5'),(707,41,'string 5'),(708,41,'string 5'),(709,41,'string 5'),(710,41,'string 5'),(711,41,'string 5'),(712,41,'string 5'),(713,41,'string 5'),(714,41,'string 5'),(715,41,'string 5'),(716,41,'string 5'),(717,41,'string 5'),(718,41,'string 5'),(719,41,'string 5'),(720,41,'string 5'),(721,41,'string 5'),(722,41,'string 5'),(723,41,'string 5'),(724,41,'string 5'),(725,41,'string 5'),(726,41,'string 5'),(727,41,'string 5'),(728,41,'string 5'),(729,41,'string 5'),(730,41,'string 5'),(731,41,'string 5'),(732,41,'string 5'),(733,41,'string 5'),(734,41,'string 5'),(735,41,'string 5'),(736,41,'string 5'),(737,41,'string 5'),(738,41,'string 5'),(739,41,'string 5'),(740,41,'string 5'),(741,41,'string 5'),(742,41,'string 5'),(743,41,'string 5'),(744,41,'string 5'),(745,41,'string 5'),(746,41,'string 5'),(747,41,'string 5'),(748,41,'string 5'),(749,41,'string 5'),(750,41,'string 5'),(751,41,'string 5'),(752,41,'string 5'),(753,41,'string 5'),(754,41,'string 5'),(755,41,'string 5'),(756,41,'string 5'),(757,41,'string 5'),(758,41,'string 5'),(759,41,'string 5'),(760,41,'string 5'),(761,41,'string 5'),(762,41,'string 5'),(763,41,'string 5'),(764,41,'string 5'),(765,41,'string 5'),(766,41,'string 5'),(767,41,'string 5'),(768,41,'string 5'),(769,41,'string 5'),(770,41,'string 5'),(771,41,'string 5'),(772,41,'string 5'),(773,41,'string 5'),(774,41,'string 5'),(775,41,'string 5'),(776,41,'string 5'),(777,41,'string 5'),(778,41,'string 5'),(779,41,'string 5'),(780,41,'string 5'),(781,41,'string 5'),(782,41,'string 5'),(783,41,'string 5'),(784,41,'string 5'),(785,41,'string 5'),(786,41,'string 5'),(787,41,'string 5'),(788,41,'string 5'),(789,41,'string 5'),(790,41,'string 5'),(791,41,'string 5'),(792,41,'string 5'),(793,41,'string 5'),(794,41,'string 5'),(795,41,'string 5'),(796,41,'string 5'),(797,41,'string 5'),(798,41,'string 5'),(799,41,'string 5'),(800,41,'string 5'),(801,41,'string 5'),(802,41,'string 5'),(803,41,'string 5'),(804,41,'string 5'),(805,41,'string 5'),(806,41,'string 5'),(807,41,'string 5'),(808,41,'string 5'),(809,41,'string 5'),(810,41,'string 5'),(811,41,'string 5'),(812,41,'string 5'),(813,41,'string 5'),(814,41,'string 5'),(815,41,'string 5'),(816,41,'string 5'),(817,41,'string 5'),(818,41,'string 5'),(819,41,'string 5'),(820,41,'string 5'),(821,41,'string 5'),(822,41,'string 5'),(823,41,'string 5'),(824,41,'string 5'),(825,41,'string 5'),(826,41,'string 5'),(827,41,'string 5'),(828,41,'string 5'),(829,41,'string 5'),(830,41,'string 5'),(831,41,'string 5'),(832,41,'string 5'),(833,41,'string 5'),(834,41,'string 5'),(835,41,'string 5'),(836,41,'string 5'),(837,41,'string 5'),(838,41,'string 5'),(839,41,'string 5'),(840,41,'string 5'),(841,41,'string 5'),(842,41,'string 5'),(843,41,'string 5'),(844,41,'string 5'),(845,41,'string 5'),(846,41,'string 5'),(847,41,'string 5'),(848,41,'string 5'),(849,41,'string 5'),(850,41,'string 5'),(851,41,'string 5'),(852,41,'string 5'),(853,41,'string 5'),(854,41,'string 5'),(855,41,'string 5'),(856,41,'string 5'),(857,41,'string 5'),(858,41,'string 5'),(859,41,'string 5'),(860,41,'string 5'),(861,41,'string 5'),(862,41,'string 5'),(863,41,'string 5'),(864,41,'string 5'),(865,41,'string 5'),(866,41,'string 5'),(867,41,'string 5'),(868,41,'string 5'),(869,41,'string 5'),(870,41,'string 5'),(871,41,'string 5'),(872,41,'string 5'),(873,41,'string 5'),(874,41,'string 5'),(875,41,'string 5'),(876,41,'string 5'),(877,41,'string 5'),(878,41,'string 5'),(879,41,'string 5'),(880,41,'string 5'),(881,41,'string 5'),(882,41,'string 5'),(883,41,'string 5'),(884,41,'string 5'),(885,41,'string 5'),(886,41,'string 5'),(887,41,'string 5'),(888,41,'string 5'),(889,41,'string 5'),(890,41,'string 5'),(891,41,'string 5'),(892,41,'string 5'),(893,41,'string 5'),(894,41,'string 5'),(895,41,'string 5'),(896,41,'string 5'),(897,41,'string 5'),(898,41,'string 5'),(899,41,'string 5'),(900,41,'string 5'),(901,41,'string 5'),(902,41,'string 5'),(903,41,'string 5'),(904,41,'string 5'),(905,41,'string 5'),(906,41,'string 5'),(907,41,'string 5'),(908,41,'string 5'),(909,41,'string 5'),(910,41,'string 5'),(911,41,'string 5'),(912,41,'string 5'),(913,41,'string 5'),(914,41,'string 5'),(915,41,'string 5'),(916,41,'string 5'),(917,41,'string 5'),(918,41,'string 5'),(919,41,'string 5'),(920,41,'string 5'),(921,41,'string 5'),(922,41,'string 5'),(923,41,'string 5'),(924,41,'string 5'),(925,41,'string 5'),(926,41,'string 5'),(927,41,'string 5'),(928,41,'string 5'),(929,41,'string 5'),(930,41,'string 5'),(931,41,'string 5'),(932,41,'string 5'),(933,41,'string 5'),(934,41,'string 5'),(935,41,'string 5'),(936,41,'string 5'),(937,41,'string 5'),(938,41,'string 5'),(939,41,'string 5'),(940,41,'string 5'),(941,41,'string 5'),(942,41,'string 5'),(943,41,'string 5'),(944,41,'string 5'),(945,41,'string 5'),(946,41,'string 5'),(947,41,'string 5'),(948,41,'string 5'),(949,41,'string 5'),(950,41,'string 5'),(951,41,'string 5'),(952,41,'string 5'),(953,41,'string 5'),(954,41,'string 5'),(955,41,'string 5'),(956,41,'string 5'),(957,41,'string 5'),(958,41,'string 5'),(959,41,'string 5'),(960,41,'string 5'),(961,41,'string 5'),(962,41,'string 5'),(963,41,'string 5'),(964,41,'string 5'),(965,41,'string 5'),(966,41,'string 5'),(967,41,'string 5'),(968,41,'string 5'),(969,41,'string 5'),(970,41,'string 5'),(971,41,'string 5'),(972,41,'string 5'),(973,41,'string 5'),(974,41,'string 5'),(975,41,'string 5'),(976,41,'string 5'),(977,41,'string 5'),(978,41,'string 5'),(979,41,'string 5'),(980,41,'string 5'),(981,41,'string 5'),(982,41,'string 5'),(983,41,'string 5'),(984,41,'string 5'),(985,41,'string 5'),(986,41,'string 5'),(987,41,'string 5'),(988,41,'string 5'),(989,41,'string 5'),(990,41,'string 5'),(991,41,'string 5'),(992,41,'string 5'),(993,41,'string 5'),(994,41,'string 5'),(995,41,'string 5'),(996,41,'string 5'),(997,41,'string 5'),(998,41,'string 5'),(999,41,'string 5'),(1000,41,'string 5'),(1001,41,'string 5'),(1002,41,'string 5'),(1003,41,'string 5'),(1004,41,'string 5'),(1005,41,'string 5'),(1006,41,'string 5'),(1007,41,'string 5'),(1008,41,'string 5'),(1009,41,'string 5'),(1010,41,'string 5'),(1011,41,'string 5'),(1012,41,'string 5'),(1013,41,'string 5'),(1014,41,'string 5'),(1015,41,'string 5'),(1016,41,'string 5'),(1017,41,'string 5'),(1018,41,'string 5'),(1019,41,'string 5'),(1020,41,'string 5'),(1021,41,'string 5'),(1022,41,'string 5'),(1023,41,'string 5'),(1024,41,'string 5'),(1025,41,'string 5'),(1026,41,'string 5'),(1027,41,'string 5'),(1028,41,'string 5'),(1029,41,'string 5'),(1030,41,'string 5'),(1031,41,'string 5'),(1032,41,'string 5'),(1033,41,'string 5'),(1034,41,'string 5'),(1035,41,'string 5'),(1036,41,'string 5'),(1037,41,'string 5'),(1038,41,'string 5'),(1039,41,'string 5'),(1040,41,'string 5'),(1041,41,'string 5'),(1042,41,'string 5'),(1043,41,'string 5'),(1044,41,'string 5'),(1045,41,'string 5'),(1046,41,'string 5'),(1047,41,'string 5'),(1048,41,'string 5'),(1049,41,'string 5'),(1050,41,'string 5'),(1051,41,'string 5'),(1052,41,'string 5'),(1053,41,'string 5'),(1054,41,'string 5'),(1055,41,'string 5'),(1056,41,'string 5'),(1057,41,'string 5'),(1058,41,'string 5'),(1059,41,'string 5'),(1060,41,'string 5'),(1061,41,'string 5'),(1062,41,'string 5'),(1063,41,'string 5'),(1064,41,'string 5'),(1065,41,'string 5'),(1066,41,'string 5'),(1067,41,'string 5'),(1068,41,'string 5'),(1069,41,'string 5'),(1070,41,'string 5'),(1071,41,'string 5'),(1072,41,'string 5'),(1073,41,'string 5'),(1074,41,'string 5'),(1075,41,'string 5'),(1076,41,'string 5'),(1077,41,'string 5'),(1078,41,'string 5'),(1079,41,'string 5'),(1080,41,'string 5'),(1081,41,'string 5'),(1082,41,'string 5'),(1083,41,'string 5'),(1084,41,'string 5'),(1085,41,'string 5'),(1086,41,'string 5'),(1087,41,'string 5'),(1088,41,'string 5'),(1089,41,'string 5'),(1090,41,'string 5'),(1091,41,'string 5'),(1092,41,'string 5'),(1093,41,'string 5'),(1094,41,'string 5'),(1095,41,'string 5'),(1096,41,'string 5'),(1097,41,'string 5'),(1098,41,'string 5'),(1099,41,'string 5'),(1100,41,'string 5'),(1101,41,'string 5'),(1102,41,'string 5'),(1103,41,'string 5'),(1104,41,'string 5'),(1105,41,'string 5'),(1106,41,'string 5'),(1107,41,'string 5'),(1108,41,'string 5'),(1109,41,'string 5'),(1110,41,'string 5'),(1111,41,'string 5'),(1112,41,'string 5'),(1113,41,'string 5'),(1114,41,'string 5'),(1115,41,'string 5'),(1116,41,'string 5'),(1117,41,'string 5'),(1118,41,'string 5'),(1119,41,'string 5'),(1120,41,'string 5'),(1121,41,'string 5'),(1122,41,'string 5'),(1123,41,'string 5'),(1124,41,'string 5'),(1125,41,'string 5'),(1126,41,'string 5'),(1127,41,'string 5'),(1128,41,'string 5'),(1129,41,'string 5'),(1130,41,'string 5'),(1131,41,'string 5'),(1132,41,'string 5'),(1133,41,'string 5'),(1134,41,'string 5'),(1135,41,'string 5'),(1136,41,'string 5'),(1137,41,'string 5'),(1138,41,'string 5'),(1139,41,'string 5'),(1140,41,'string 5'),(1141,41,'string 5'),(1142,41,'string 5'),(1143,41,'string 5'),(1144,41,'string 5'),(1145,41,'string 5'),(1146,41,'string 5'),(1147,41,'string 5'),(1148,41,'string 5'),(1149,41,'string 5'),(1150,41,'string 5'),(1151,41,'string 5'),(1152,41,'string 5'),(1153,41,'string 5'),(1154,41,'string 5'),(1155,41,'string 5'),(1156,41,'string 5'),(1157,41,'string 5'),(1158,41,'string 5'),(1159,41,'string 5'),(1160,41,'string 5'),(1161,41,'string 5'),(1162,41,'string 5'),(1163,41,'string 5'),(1164,41,'string 5'),(1165,41,'string 5'),(1166,41,'string 5'),(1167,41,'string 5'),(1168,41,'string 5'),(1169,41,'string 5'),(1170,41,'string 5'),(1171,41,'string 5'),(1172,41,'string 5'),(1173,41,'string 5'),(1174,41,'string 5'),(1175,41,'string 5'),(1176,41,'string 5'),(1177,41,'string 5'),(1178,41,'string 5'),(1179,41,'string 5'),(1180,41,'string 5'),(1181,41,'string 5'),(1182,41,'string 5'),(1183,41,'string 5'),(1184,41,'string 5'),(1185,41,'string 5'),(1186,41,'string 5'),(1187,41,'string 5'),(1188,41,'string 5'),(1189,41,'string 5'),(1190,41,'string 5'),(1191,41,'string 5'),(1192,41,'string 5'),(1193,41,'string 5'),(1194,41,'string 5'),(1195,41,'string 5'),(1196,41,'string 5'),(1197,41,'string 5'),(1198,41,'string 5'),(1199,41,'string 5'),(1200,41,'string 5'),(1201,41,'string 5'),(1202,41,'string 5'),(1203,41,'string 5'),(1204,41,'string 5'),(1205,41,'string 5'),(1206,41,'string 5'),(1207,41,'string 5'),(1208,41,'string 5'),(1209,41,'string 5'),(1210,41,'string 5'),(1211,41,'string 5'),(1212,41,'string 5'),(1213,41,'string 5'),(1214,41,'string 5'),(1215,41,'string 5'),(1216,41,'string 5'),(1217,41,'string 5'),(1218,41,'string 5'),(1219,41,'string 5'),(1220,41,'string 5'),(1221,41,'string 5'),(1222,41,'string 5'),(1223,41,'string 5'),(1224,41,'string 5'),(1225,41,'string 5'),(1226,41,'string 5'),(1227,41,'string 5'),(1228,41,'string 5'),(1229,41,'string 5'),(1230,41,'string 5'),(1231,41,'string 5'),(1232,41,'string 5'),(1233,41,'string 5'),(1234,41,'string 5'),(1235,41,'string 5'),(1236,41,'string 5'),(1237,41,'string 5'),(1238,41,'string 5'),(1239,41,'string 5'),(1240,41,'string 5'),(1241,41,'string 5'),(1242,41,'string 5'),(1243,41,'string 5'),(1244,41,'string 5'),(1245,41,'string 5'),(1246,41,'string 5'),(1247,41,'string 5'),(1248,41,'string 5'),(1249,41,'string 5'),(1250,41,'string 5'),(1251,41,'string 5'),(1252,41,'string 5'),(1253,41,'string 5'),(1254,41,'string 5'),(1255,41,'string 5'),(1256,41,'string 5'),(1257,41,'string 5'),(1258,41,'string 5'),(1259,41,'string 5'),(1260,41,'string 5'),(1261,41,'string 5'),(1262,41,'string 5'),(1263,41,'string 5'),(1264,41,'string 5'),(1265,41,'string 5'),(1266,41,'string 5'),(1267,41,'string 5'),(1268,41,'string 5'),(1269,41,'string 5'),(1270,41,'string 5'),(1271,41,'string 5'),(1272,41,'string 5'),(1273,41,'string 5'),(1274,41,'string 5'),(1275,41,'string 5'),(1276,41,'string 5'),(1277,41,'string 5'),(1278,41,'string 5'),(1279,41,'string 5'),(1280,41,'string 5'),(1281,41,'string 5'),(1282,41,'string 5'),(1283,41,'string 5'),(1284,41,'string 5'),(1285,41,'string 5'),(1286,41,'string 5'),(1287,41,'string 5'),(1288,41,'string 5'),(1289,41,'string 5'),(1290,41,'string 5'),(1291,41,'string 5'),(1292,41,'string 5'),(1293,41,'string 5'),(1294,41,'string 5'),(1295,41,'string 5'),(1296,41,'string 5'),(1297,41,'string 5'),(1298,41,'string 5'),(1299,41,'string 5'),(1300,41,'string 5'),(1301,41,'string 5'),(1302,41,'string 5'),(1303,41,'string 5'),(1304,41,'string 5'),(1305,41,'string 5'),(1306,41,'string 5'),(1307,41,'string 5'),(1308,41,'string 5'),(1309,41,'string 5'),(1310,41,'string 5'),(1311,41,'string 5'),(1312,41,'string 5'),(1313,41,'string 5'),(1314,41,'string 5'),(1315,41,'string 5'),(1316,41,'string 5'),(1317,41,'string 5'),(1318,41,'string 5'),(1319,41,'string 5'),(1320,41,'string 5'),(1321,41,'string 5'),(1322,41,'string 5'),(1323,41,'string 5'),(1324,41,'string 5'),(1325,41,'string 5'),(1326,41,'string 5'),(1327,41,'string 5'),(1328,41,'string 5'),(1329,41,'string 5'),(1330,41,'string 5'),(1331,41,'string 5'),(1332,41,'string 5'),(1333,41,'string 5'),(1334,41,'string 5'),(1335,41,'string 5'),(1336,41,'string 5'),(1337,41,'string 5'),(1338,41,'string 5'),(1339,41,'string 5'),(1340,41,'string 5'),(1341,41,'string 5'),(1342,41,'string 5'),(1343,41,'string 5'),(1344,41,'string 5'),(1345,41,'string 5'),(1346,41,'string 5'),(1347,41,'string 5'),(1348,41,'string 5'),(1349,41,'string 5'),(1350,41,'string 5'),(1351,41,'string 5'),(1352,41,'string 5'),(1353,41,'string 5'),(1354,41,'string 5'),(1355,41,'string 5'),(1356,41,'string 5'),(1357,41,'string 5'),(1358,41,'string 5'),(1359,41,'string 5'),(1360,41,'string 5'),(1361,41,'string 5'),(1362,41,'string 5'),(1363,41,'string 5'),(1364,41,'string 5'),(1365,41,'string 5'),(1366,41,'string 5'),(1367,41,'string 5'),(1368,41,'string 5'),(1369,41,'string 5'),(1370,41,'string 5'),(1371,41,'string 5'),(1372,41,'string 5'),(1373,41,'string 5'),(1374,41,'string 5'),(1375,41,'string 5'),(1376,41,'string 5'),(1377,41,'string 5'),(1378,41,'string 5'),(1379,41,'string 5'),(1380,41,'string 5'),(1381,41,'string 5'),(1382,41,'string 5'),(1383,41,'string 5'),(1384,41,'string 5'),(1385,41,'string 5'),(1386,41,'string 5'),(1387,41,'string 5'),(1388,41,'string 5'),(1389,41,'string 5'),(1390,41,'string 5'),(1391,41,'string 5'),(1392,41,'string 5'),(1393,41,'string 5'),(1394,41,'string 5'),(1395,41,'string 5'),(1396,41,'string 5'),(1397,41,'string 5'),(1398,41,'string 5'),(1399,41,'string 5'),(1400,41,'string 5'),(1401,41,'string 5'),(1402,41,'string 5'),(1403,41,'string 5'),(1404,41,'string 5'),(1405,41,'string 5'),(1406,41,'string 5'),(1407,41,'string 5'),(1408,41,'string 5'),(1409,41,'string 5'),(1410,41,'string 5'),(1411,41,'string 5'),(1412,41,'string 5'),(1413,41,'string 5'),(1414,41,'string 5'),(1415,41,'string 5'),(1416,41,'string 5'),(1417,41,'string 5'),(1418,41,'string 5'),(1419,41,'string 5'),(1420,41,'string 5'),(1421,41,'string 5'),(1422,41,'string 5'),(1423,41,'string 5'),(1424,41,'string 5'),(1425,41,'string 5'),(1426,41,'string 5'),(1427,41,'string 5'),(1428,41,'string 5'),(1429,41,'string 5'),(1430,41,'string 5'),(1431,41,'string 5'),(1432,41,'string 5'),(1433,41,'string 5'),(1434,41,'string 5'),(1435,41,'string 5'),(1436,41,'string 5'),(1437,41,'string 5'),(1438,41,'string 5'),(1439,41,'string 5'),(1440,41,'string 5'),(1441,41,'string 5'),(1442,41,'string 5'),(1443,41,'string 5'),(1444,41,'string 5'),(1445,41,'string 5'),(1446,41,'string 5'),(1447,41,'string 5'),(1448,41,'string 5'),(1449,41,'string 5'),(1450,41,'string 5'),(1451,41,'string 5'),(1452,41,'string 5'),(1453,41,'string 5'),(1454,41,'string 5'),(1455,41,'string 5'),(1456,41,'string 5'),(1457,41,'string 5'),(1458,41,'string 5'),(1459,41,'string 5'),(1460,41,'string 5'),(1461,41,'string 5'),(1462,41,'string 5'),(1463,41,'string 5'),(1464,41,'string 5'),(1465,41,'string 5'),(1466,41,'string 5'),(1467,41,'string 5'),(1468,41,'string 5'),(1469,41,'string 5'),(1470,41,'string 5'),(1471,41,'string 5'),(1472,41,'string 5'),(1473,41,'string 5'),(1474,41,'string 5'),(1475,41,'string 5'),(1476,41,'string 5'),(1477,41,'string 5'),(1478,41,'string 5'),(1479,41,'string 5'),(1480,41,'string 5'),(1481,41,'string 5'),(1482,41,'string 5'),(1483,41,'string 5'),(1484,41,'string 5'),(1485,41,'string 5'),(1486,41,'string 5'),(1487,41,'string 5'),(1488,41,'string 5'),(1489,41,'string 5'),(1490,41,'string 5'),(1491,41,'string 5'),(1492,41,'string 5'),(1493,41,'string 5'),(1494,41,'string 5'),(1495,41,'string 5'),(1496,41,'string 5'),(1497,41,'string 5'),(1498,41,'string 5'),(1499,41,'string 5'),(1500,41,'string 5'),(1501,41,'string 5'),(1502,41,'string 5'),(1503,41,'string 5'),(1504,41,'string 5'),(1505,41,'string 5'),(1506,41,'string 5'),(1507,41,'string 5'),(1508,41,'string 5'),(1509,41,'string 5'),(1510,41,'string 5'),(1511,41,'string 5'),(1512,41,'string 5'),(1513,41,'string 5'),(1514,41,'string 5'),(1515,41,'string 5'),(1516,41,'string 5'),(1517,41,'string 5'),(1518,41,'string 5'),(1519,41,'string 5'),(1520,41,'string 5'),(1521,41,'string 5'),(1522,41,'string 5'),(1523,41,'string 5'),(1524,41,'string 5'),(1525,41,'string 5'),(1526,41,'string 5'),(1527,41,'string 5'),(1528,41,'string 5'),(1529,41,'string 5'),(1530,41,'string 5'),(1531,41,'string 5'),(1532,41,'string 5'),(1533,41,'string 5'),(1534,41,'string 5'),(1535,41,'string 5'),(1536,41,'string 5'),(1537,41,'string 5'),(1538,41,'string 5'),(1539,41,'string 5'),(1540,41,'string 5'),(1541,41,'string 5'),(1542,41,'string 5'),(1543,41,'string 5'),(1544,41,'string 5'),(1545,41,'string 5'),(1546,41,'string 5'),(1547,41,'string 5'),(1548,41,'string 5'),(1549,41,'string 5'),(1550,41,'string 5'),(1551,41,'string 5'),(1552,41,'string 5'),(1553,41,'string 5'),(1554,41,'string 5'),(1555,41,'string 5'),(1556,41,'string 5'),(1557,41,'string 5'),(1558,41,'string 5'),(1559,41,'string 5'),(1560,41,'string 5'),(1561,41,'string 5'),(1562,41,'string 5'),(1563,41,'string 5'),(1564,41,'string 5'),(1565,41,'string 5'),(1566,41,'string 5'),(1567,41,'string 5'),(1568,41,'string 5'),(1569,41,'string 5'),(1570,41,'string 5'),(1571,41,'string 5'),(1572,41,'string 5'),(1573,41,'string 5'),(1574,41,'string 5'),(1575,41,'string 5'),(1576,41,'string 5'),(1577,41,'string 5'),(1578,41,'string 5'),(1579,41,'string 5'),(1580,41,'string 5'),(1581,41,'string 5'),(1582,41,'string 5'),(1583,41,'string 5'),(1584,41,'string 5'),(1585,41,'string 5'),(1586,41,'string 5'),(1587,41,'string 5'),(1588,41,'string 5'),(1589,41,'string 5'),(1590,41,'string 5'),(1591,41,'string 5'),(1592,41,'string 5'),(1593,41,'string 5'),(1594,41,'string 5'),(1595,41,'string 5'),(1596,41,'string 5'),(1597,41,'string 5'),(1598,41,'string 5'),(1599,41,'string 5'),(1600,41,'string 5'),(1601,41,'string 5'),(1602,41,'string 5'),(1603,41,'string 5'),(1604,41,'string 5'),(1605,41,'string 5'),(1606,41,'string 5'),(1607,41,'string 5'),(1608,41,'string 5'),(1609,41,'string 5'),(1610,41,'string 5'),(1611,41,'string 5'),(1612,41,'string 5'),(1613,41,'string 5'),(1614,41,'string 5'),(1615,41,'string 5'),(1616,41,'string 5'),(1617,41,'string 5'),(1618,41,'string 5'),(1619,41,'string 5'),(1620,41,'string 5'),(1621,41,'string 5'),(1622,41,'string 5'),(1623,41,'string 5'),(1624,41,'string 5'),(1625,41,'string 5'),(1626,41,'string 5'),(1627,41,'string 5'),(1628,41,'string 5'),(1629,41,'string 5'),(1630,41,'string 5'),(1631,41,'string 5'),(1632,41,'string 5'),(1633,41,'string 5'),(1634,41,'string 5'),(1635,41,'string 5'),(1636,41,'string 5'),(1637,41,'string 5'),(1638,41,'string 5'),(1639,41,'string 5'),(1640,41,'string 5'),(1641,41,'string 5'),(1642,41,'string 5'),(1643,41,'string 5'),(1644,41,'string 5'),(1645,41,'string 5'),(1646,41,'string 5'),(1647,41,'string 5'),(1648,41,'string 5'),(1649,41,'string 5'),(1650,41,'string 5'),(1651,41,'string 5'),(1652,41,'string 5'),(1653,41,'string 5'),(1654,41,'string 5'),(1655,41,'string 5'),(1656,41,'string 5'),(1657,41,'string 5'),(1658,41,'string 5'),(1659,41,'string 5'),(1660,41,'string 5'),(1661,41,'string 5'),(1662,41,'string 5'),(1663,41,'string 5'),(1664,41,'string 5'),(1665,41,'string 5'),(1666,41,'string 5'),(1667,41,'string 5'),(1668,41,'string 5'),(1669,41,'string 5'),(1670,41,'string 5'),(1671,41,'string 5'),(1672,41,'string 5'),(1673,41,'string 5'),(1674,41,'string 5'),(1675,41,'string 5'),(1676,41,'string 5'),(1677,41,'string 5'),(1678,41,'string 5'),(1679,41,'string 5'),(1680,41,'string 5'),(1681,41,'string 5'),(1682,41,'string 5'),(1683,41,'string 5'),(1684,41,'string 5'),(1685,41,'string 5'),(1686,41,'string 5'),(1687,41,'string 5'),(1688,41,'string 5'),(1689,41,'string 5'),(1690,41,'string 5'),(1691,41,'string 5'),(1692,41,'string 5'),(1693,41,'string 5'),(1694,41,'string 5'),(1695,41,'string 5'),(1696,41,'string 5'),(1697,41,'string 5'),(1698,41,'string 5'),(1699,41,'string 5'),(1700,41,'string 5'),(1701,41,'string 5'),(1702,41,'string 5'),(1703,41,'string 5'),(1704,41,'string 5'),(1705,41,'string 5'),(1706,41,'string 5'),(1707,41,'string 5'),(1708,41,'string 5'),(1709,41,'string 5'),(1710,41,'string 5'),(1711,41,'string 5'),(1712,41,'string 5'),(1713,41,'string 5'),(1714,41,'string 5'),(1715,41,'string 5'),(1716,41,'string 5'),(1717,41,'string 5'),(1718,41,'string 5'),(1719,41,'string 5'),(1720,41,'string 5'),(1721,41,'string 5'),(1722,41,'string 5'),(1723,41,'string 5'),(1724,41,'string 5'),(1725,41,'string 5'),(1726,41,'string 5'),(1727,41,'string 5'),(1728,41,'string 5'),(1729,41,'string 5'),(1730,41,'string 5'),(1731,41,'string 5'),(1732,41,'string 5'),(1733,41,'string 5'),(1734,41,'string 5'),(1735,41,'string 5'),(1736,41,'string 5'),(1737,41,'string 5'),(1738,41,'string 5'),(1739,41,'string 5'),(1740,41,'string 5'),(1741,41,'string 5'),(1742,41,'string 5'),(1743,41,'string 5'),(1744,41,'string 5'),(1745,41,'string 5'),(1746,41,'string 5'),(1747,41,'string 5'),(1748,41,'string 5'),(1749,41,'string 5'),(1750,41,'string 5'),(1751,41,'string 5'),(1752,41,'string 5'),(1753,41,'string 5'),(1754,41,'string 5'),(1755,41,'string 5'),(1756,41,'string 5'),(1757,41,'string 5'),(1758,41,'string 5'),(1759,41,'string 5'),(1760,41,'string 5'),(1761,41,'string 5'),(1762,41,'string 5'),(1763,41,'string 5'),(1764,41,'string 5'),(1765,41,'string 5'),(1766,41,'string 5'),(1767,41,'string 5'),(1768,41,'string 5'),(1769,41,'string 5'),(1770,41,'string 5'),(1771,41,'string 5'),(1772,41,'string 5'),(1773,41,'string 5'),(1774,41,'string 5'),(1775,41,'string 5'),(1776,41,'string 5'),(1777,41,'string 5'),(1778,41,'string 5'),(1779,41,'string 5'),(1780,41,'string 5'),(1781,41,'string 5'),(1782,41,'string 5'),(1783,41,'string 5'),(1784,41,'string 5'),(1785,41,'string 5'),(1786,41,'string 5'),(1787,41,'string 5'),(1788,41,'string 5'),(1789,41,'string 5'),(1790,41,'string 5'),(1791,41,'string 5'),(1792,41,'string 5'),(1793,41,'string 5'),(1794,41,'string 5'),(1795,41,'string 5'),(1796,41,'string 5'),(1797,41,'string 5'),(1798,41,'string 5'),(1799,41,'string 5'),(1800,41,'string 5'),(1801,41,'string 5'),(1802,41,'string 5'),(1803,41,'string 5'),(1804,41,'string 5'),(1805,41,'string 5'),(1806,41,'string 5'),(1807,41,'string 5'),(1808,41,'string 5'),(1809,41,'string 5'),(1810,41,'string 5'),(1811,41,'string 5'),(1812,41,'string 5'),(1813,41,'string 5'),(1814,41,'string 5'),(1815,41,'string 5'),(1816,41,'string 5'),(1817,41,'string 5'),(1818,41,'string 5'),(1819,41,'string 5'),(1820,41,'string 5'),(1821,41,'string 5'),(1822,41,'string 5'),(1823,41,'string 5'),(1824,41,'string 5'),(1825,41,'string 5'),(1826,41,'string 5'),(1827,41,'string 5'),(1828,41,'string 5'),(1829,41,'string 5'),(1830,41,'string 5'),(1831,41,'string 5'),(1832,41,'string 5'),(1833,41,'string 5'),(1834,41,'string 5'),(1835,41,'string 5'),(1836,41,'string 5'),(1837,41,'string 5'),(1838,41,'string 5'),(1839,41,'string 5'),(1840,41,'string 5'),(1841,41,'string 5'),(1842,41,'string 5'),(1843,41,'string 5'),(1844,41,'string 5'),(1845,41,'string 5'),(1846,41,'string 5'),(1847,41,'string 5'),(1848,41,'string 5'),(1849,41,'string 5'),(1850,41,'string 5'),(1851,41,'string 5'),(1852,41,'string 5'),(1853,41,'string 5'),(1854,41,'string 5'),(1855,41,'string 5'),(1856,41,'string 5'),(1857,41,'string 5'),(1858,41,'string 5'),(1859,41,'string 5'),(1860,41,'string 5'),(1861,41,'string 5'),(1862,41,'string 5'),(1863,41,'string 5'),(1864,41,'string 5'),(1865,41,'string 5'),(1866,41,'string 5'),(1867,41,'string 5'),(1868,41,'string 5'),(1869,41,'string 5'),(1870,41,'string 5'),(1871,41,'string 5'),(1872,41,'string 5'),(1873,41,'string 5'),(1874,41,'string 5'),(1875,41,'string 5'),(1876,41,'string 5'),(1877,41,'string 5'),(1878,41,'string 5'),(1879,41,'string 5'),(1880,41,'string 5'),(1881,41,'string 5'),(1882,41,'string 5'),(1883,41,'string 5'),(1884,41,'string 5'),(1885,41,'string 5'),(1886,41,'string 5'),(1887,41,'string 5'),(1888,41,'string 5'),(1889,41,'string 5'),(1890,41,'string 5'),(1891,41,'string 5'),(1892,41,'string 5'),(1893,41,'string 5'),(1894,41,'string 5'),(1895,41,'string 5'),(1896,41,'string 5'),(1897,41,'string 5'),(1898,41,'string 5'),(1899,41,'string 5'),(1900,41,'string 5'),(1901,41,'string 5'),(1902,41,'string 5'),(1903,41,'string 5'),(1904,41,'string 5'),(1905,41,'string 5'),(1906,41,'string 5'),(1907,41,'string 5'),(1908,41,'string 5'),(1909,41,'string 5'),(1910,41,'string 5'),(1911,41,'string 5'),(1912,41,'string 5'),(1913,41,'string 5'),(1914,41,'string 5'),(1915,41,'string 5'),(1916,41,'string 5'),(1917,41,'string 5'),(1918,41,'string 5'),(1919,41,'string 5'),(1920,41,'string 5'),(1921,41,'string 5'),(1922,41,'string 5'),(1923,41,'string 5'),(1924,41,'string 5'),(1925,41,'string 5'),(1926,41,'string 5'),(1927,41,'string 5'),(1928,41,'string 5'),(1929,41,'string 5'),(1930,41,'string 5'),(1931,41,'string 5'),(1932,41,'string 5'),(1933,41,'string 5'),(1934,41,'string 5'),(1935,41,'string 5'),(1936,41,'string 5'),(1937,41,'string 5'),(1938,41,'string 5'),(1939,41,'string 5'),(1940,41,'string 5'),(1941,41,'string 5'),(1942,41,'string 5'),(1943,41,'string 5'),(1944,41,'string 5'),(1945,41,'string 5'),(1946,41,'string 5'),(1947,41,'string 5'),(1948,41,'string 5'),(1949,41,'string 5'),(1950,41,'string 5'),(1951,41,'string 5'),(1952,41,'string 5'),(1953,41,'string 5'),(1954,41,'string 5'),(1955,41,'string 5'),(1956,41,'string 5'),(1957,41,'string 5'),(1958,41,'string 5'),(1959,41,'string 5'),(1960,41,'string 5'),(1961,41,'string 5'),(1962,41,'string 5'),(1963,41,'string 5'),(1964,41,'string 5'),(1965,41,'string 5'),(1966,41,'string 5'),(1967,41,'string 5'),(1968,41,'string 5'),(1969,41,'string 5'),(1970,41,'string 5'),(1971,41,'string 5'),(1972,41,'string 5'),(1973,41,'string 5'),(1974,41,'string 5'),(1975,41,'string 5'),(1976,41,'string 5'),(1977,41,'string 5'),(1978,41,'string 5'),(1979,41,'string 5'),(1980,41,'string 5'),(1981,41,'string 5'),(1982,41,'string 5'),(1983,41,'string 5'),(1984,41,'string 5'),(1985,41,'string 5'),(1986,41,'string 5'),(1987,41,'string 5'),(1988,41,'string 5'),(1989,41,'string 5'),(1990,41,'string 5'),(1991,41,'string 5'),(1992,41,'string 5'),(1993,41,'string 5'),(1994,41,'string 5'),(1995,41,'string 5'),(1996,41,'string 5'),(1997,41,'string 5'),(1998,41,'string 5'),(1999,41,'string 5'),(2000,41,'string 5'),(2001,41,'string 5'),(2002,41,'string 5'),(2003,41,'string 5'),(2004,41,'string 5'),(2005,41,'string 5'),(2006,41,'string 5'),(2007,41,'string 5'),(2008,41,'string 5'),(2009,41,'string 5'),(2010,41,'string 5'),(2011,41,'string 5'),(2012,41,'string 5'),(2013,41,'string 5'),(2014,41,'string 5'),(2015,41,'string 5'),(2016,41,'string 5'),(2017,41,'string 5'),(2018,41,'string 5'),(2019,41,'string 5'),(2020,41,'string 5'),(2021,41,'string 5'),(2022,41,'string 5'),(2023,41,'string 5'),(2024,41,'string 5'),(2025,41,'string 5'),(2026,41,'string 5'),(2027,41,'string 5'),(2028,41,'string 5'),(2029,41,'string 5'),(2030,41,'string 5'),(2031,41,'string 5'),(2032,41,'string 5'),(2033,41,'string 5'),(2034,41,'string 5'),(2035,41,'string 5'),(2036,41,'string 5'),(2037,41,'string 5'),(2038,41,'string 5'),(2039,41,'string 5'),(2040,41,'string 5'),(2041,41,'string 5'),(2042,41,'string 5'),(2043,41,'string 5'),(2044,41,'string 5'),(2045,41,'string 5'),(2046,41,'string 5'),(2047,41,'string 5'),(2048,41,'string 5'),(2049,41,'string 5'),(2050,41,'string 5'),(2051,41,'string 5'),(2052,41,'string 5'),(2053,41,'string 5'),(2054,41,'string 5'),(2055,41,'string 5'),(2056,41,'string 5'),(2057,41,'string 5'),(2058,41,'string 5'),(2059,41,'string 5'),(2060,41,'string 5'),(2061,41,'string 5'),(2062,41,'string 5'),(2063,41,'string 5'),(2064,41,'string 5'),(2065,41,'string 5'),(2066,41,'string 5'),(2067,41,'string 5'),(2068,41,'string 5'),(2069,41,'string 5'),(2070,41,'string 5'),(2071,41,'string 5'),(2072,41,'string 5'),(2073,41,'string 5'),(2074,41,'string 5'),(2075,41,'string 5'),(2076,41,'string 5'),(2077,41,'string 5'),(2078,41,'string 5'),(2079,41,'string 5'),(2080,41,'string 5'),(2081,41,'string 5'),(2082,41,'string 5'),(2083,41,'string 5'),(2084,41,'string 5'),(2085,41,'string 5'),(2086,41,'string 5'),(2087,41,'string 5'),(2088,41,'string 5'),(2089,41,'string 5'),(2090,41,'string 5'),(2091,41,'string 5'),(2092,41,'string 5'),(2093,41,'string 5'),(2094,41,'string 5'),(2095,41,'string 5'),(2096,41,'string 5'),(2097,41,'string 5'),(2098,41,'string 5'),(2099,41,'string 5'),(2100,41,'string 5'),(2101,41,'string 5'),(2102,41,'string 5'),(2103,41,'string 5'),(2104,41,'string 5'),(2105,41,'string 5'),(2106,41,'string 5'),(2107,41,'string 5'),(2108,41,'string 5'),(2109,41,'string 5'),(2110,41,'string 5'),(2111,41,'string 5'),(2112,41,'string 5'),(2113,41,'string 5'),(2114,41,'string 5'),(2115,41,'string 5'),(2116,41,'string 5'),(2117,41,'string 5'),(2118,41,'string 5'),(2119,41,'string 5'),(2120,41,'string 5'),(2121,41,'string 5'),(2122,41,'string 5'),(2123,41,'string 5'),(2124,41,'string 5'),(2125,41,'string 5'),(2126,41,'string 5'),(2127,41,'string 5'),(2128,41,'string 5'),(2129,41,'string 5'),(2130,41,'string 5'),(2131,41,'string 5'),(2132,41,'string 5'),(2133,41,'string 5'),(2134,41,'string 5'),(2135,41,'string 5'),(2136,41,'string 5'),(2137,41,'string 5'),(2138,41,'string 5'),(2139,41,'string 5'),(2140,41,'string 5'),(2141,41,'string 5'),(2142,41,'string 5'),(2143,41,'string 5'),(2144,41,'string 5'),(2145,41,'string 5'),(2146,41,'string 5'),(2147,41,'string 5'),(2148,41,'string 5'),(2149,41,'string 5'),(2150,41,'string 5'),(2151,41,'string 5'),(2152,41,'string 5'),(2153,41,'string 5'),(2154,41,'string 5'),(2155,41,'string 5'),(2156,41,'string 5'),(2157,41,'string 5'),(2158,41,'string 5'),(2159,41,'string 5'),(2160,41,'string 5'),(2161,41,'string 5'),(2162,41,'string 5'),(2163,41,'string 5'),(2164,41,'string 5'),(2165,41,'string 5'),(2166,41,'string 5'),(2167,41,'string 5'),(2168,41,'string 5'),(2169,41,'string 5'),(2170,41,'string 5'),(2171,41,'string 5'),(2172,41,'string 5'),(2173,41,'string 5'),(2174,41,'string 5'),(2175,41,'string 5'),(2176,41,'string 5'),(2177,41,'string 5'),(2178,41,'string 5'),(2179,41,'string 5'),(2180,41,'string 5'),(2181,41,'string 5'),(2182,41,'string 5'),(2183,41,'string 5'),(2184,41,'string 5'),(2185,41,'string 5'),(2186,41,'string 5'),(2187,41,'string 5'),(2188,41,'string 5'),(2189,41,'string 5'),(2190,41,'string 5'),(2191,41,'string 5'),(2192,41,'string 5'),(2193,41,'string 5'),(2194,41,'string 5'),(2195,41,'string 5'),(2196,41,'string 5'),(2197,41,'string 5'),(2198,41,'string 5'),(2199,41,'string 5'),(2200,41,'string 5'),(2201,41,'string 5'),(2202,41,'string 5'),(2203,41,'string 5'),(2204,41,'string 5'),(2205,41,'string 5'),(2206,41,'string 5'),(2207,41,'string 5'),(2208,41,'string 5'),(2209,41,'string 5'),(2210,41,'string 5'),(2211,41,'string 5'),(2212,41,'string 5'),(2213,41,'string 5'),(2214,41,'string 5'),(2215,41,'string 5'),(2216,41,'string 5'),(2217,41,'string 5'),(2218,41,'string 5'),(2219,41,'string 5'),(2220,41,'string 5'),(2221,41,'string 5'),(2222,41,'string 5'),(2223,41,'string 5'),(2224,41,'string 5'),(2225,41,'string 5'),(2226,41,'string 5'),(2227,41,'string 5'),(2228,41,'string 5'),(2229,41,'string 5'),(2230,41,'string 5'),(2231,41,'string 5'),(2232,41,'string 5'),(2233,41,'string 5'),(2234,41,'string 5'),(2235,41,'string 5'),(2236,41,'string 5'),(2237,41,'string 5'),(2238,41,'string 5'),(2239,41,'string 5'),(2240,41,'string 5'),(2241,41,'string 5'),(2242,41,'string 5'),(2243,41,'string 5'),(2244,41,'string 5'),(2245,41,'string 5'),(2246,41,'string 5'),(2247,41,'string 5'),(2248,41,'string 5'),(2249,41,'string 5'),(2250,41,'string 5'),(2251,41,'string 5'),(2252,41,'string 5'),(2253,41,'string 5'),(2254,41,'string 5'),(2255,41,'string 5'),(2256,41,'string 5'),(2257,41,'string 5'),(2258,41,'string 5'),(2259,41,'string 5'),(2260,41,'string 5'),(2261,41,'string 5'),(2262,41,'string 5'),(2263,41,'string 5'),(2264,41,'string 5'),(2265,41,'string 5'),(2266,41,'string 5'),(2267,41,'string 5'),(2268,41,'string 5'),(2269,41,'string 5'),(2270,41,'string 5'),(2271,41,'string 5'),(2272,41,'string 5'),(2273,41,'string 5'),(2274,41,'string 5'),(2275,41,'string 5'),(2276,41,'string 5'),(2277,41,'string 5'),(2278,41,'string 5'),(2279,41,'string 5'),(2280,41,'string 5'),(2281,41,'string 5'),(2282,41,'string 5'),(2283,41,'string 5'),(2284,41,'string 5'),(2285,41,'string 5'),(2286,41,'string 5'),(2287,41,'string 5'),(2288,41,'string 5'),(2289,41,'string 5'),(2290,41,'string 5'),(2291,41,'string 5'),(2292,41,'string 5'),(2293,41,'string 5'),(2294,41,'string 5'),(2295,41,'string 5'),(2296,41,'string 5'),(2297,41,'string 5'),(2298,41,'string 5'),(2299,41,'string 5'),(2300,41,'string 5'),(2301,41,'string 5'),(2302,41,'string 5'),(2303,41,'string 5'),(2304,41,'string 5'),(2305,41,'string 5'),(2306,41,'string 5'),(2307,41,'string 5'),(2308,41,'string 5'),(2309,41,'string 5'),(2310,41,'string 5'),(2311,41,'string 5'),(2312,41,'string 5'),(2313,41,'string 5'),(2314,41,'string 5'),(2315,41,'string 5'),(2316,41,'string 5'),(2317,41,'string 5'),(2318,41,'string 5'),(2319,41,'string 5'),(2320,41,'string 5'),(2321,41,'string 5'),(2322,41,'string 5'),(2323,41,'string 5'),(2324,41,'string 5'),(2325,41,'string 5'),(2326,41,'string 5'),(2327,41,'string 5'),(2328,41,'string 5'),(2329,41,'string 5'),(2330,41,'string 5'),(2331,41,'string 5'),(2332,41,'string 5'),(2333,41,'string 5'),(2334,41,'string 5'),(2335,41,'string 5'),(2336,41,'string 5'),(2337,41,'string 5'),(2338,41,'string 5'),(2339,41,'string 5'),(2340,41,'string 5'),(2341,41,'string 5'),(2342,41,'string 5'),(2343,41,'string 5'),(2344,41,'string 5'),(2345,41,'string 5'),(2346,41,'string 5'),(2347,41,'string 5'),(2348,41,'string 5'),(2349,41,'string 5'),(2350,41,'string 5'),(2351,41,'string 5'),(2352,41,'string 5'),(2353,41,'string 5'),(2354,41,'string 5'),(2355,41,'string 5'),(2356,41,'string 5'),(2357,41,'string 5'),(2358,41,'string 5'),(2359,41,'string 5'),(2360,41,'string 5'),(2361,41,'string 5'),(2362,41,'string 5'),(2363,41,'string 5'),(2364,41,'string 5'),(2365,41,'string 5'),(2366,41,'string 5'),(2367,41,'string 5'),(2368,41,'string 5'),(2369,41,'string 5'),(2370,41,'string 5'),(2371,41,'string 5'),(2372,41,'string 5'),(2373,41,'string 5'),(2374,41,'string 5'),(2375,41,'string 5'),(2376,41,'string 5'),(2377,41,'string 5'),(2378,41,'string 5'),(2379,41,'string 5'),(2380,41,'string 5'),(2381,41,'string 5'),(2382,41,'string 5'),(2383,41,'string 5'),(2384,41,'string 5'),(2385,41,'string 5'),(2386,41,'string 5'),(2387,41,'string 5'),(2388,41,'string 5'),(2389,41,'string 5'),(2390,41,'string 5'),(2391,41,'string 5'),(2392,41,'string 5'),(2393,41,'string 5'),(2394,41,'string 5'),(2395,41,'string 5'),(2396,41,'string 5'),(2397,41,'string 5'),(2398,41,'string 5'),(2399,41,'string 5'),(2400,41,'string 5'),(2401,41,'string 5'),(2402,41,'string 5'),(2403,41,'string 5'),(2404,41,'string 5'),(2405,41,'string 5'),(2406,41,'string 5'),(2407,41,'string 5'),(2408,41,'string 5'),(2409,41,'string 5'),(2410,41,'string 5'),(2411,41,'string 5'),(2412,41,'string 5'),(2413,41,'string 5'),(2414,41,'string 5'),(2415,41,'string 5'),(2416,41,'string 5'),(2417,41,'string 5'),(2418,41,'string 5'),(2419,41,'string 5'),(2420,41,'string 5'),(2421,41,'string 5'),(2422,41,'string 5'),(2423,41,'string 5'),(2424,41,'string 5'),(2425,41,'string 5'),(2426,41,'string 5'),(2427,41,'string 5'),(2428,41,'string 5'),(2429,41,'string 5'),(2430,41,'string 5'),(2431,41,'string 5'),(2432,41,'string 5'),(2433,41,'string 5'),(2434,41,'string 5'),(2435,41,'string 5'),(2436,41,'string 5'),(2437,41,'string 5'),(2438,41,'string 5'),(2439,41,'string 5'),(2440,41,'string 5'),(2441,41,'string 5'),(2442,41,'string 5'),(2443,41,'string 5'),(2444,41,'string 5'),(2445,41,'string 5'),(2446,41,'string 5'),(2447,41,'string 5'),(2448,41,'string 5'),(2449,41,'string 5'),(2450,41,'string 5'),(2451,41,'string 5'),(2452,41,'string 5'),(2453,41,'string 5'),(2454,41,'string 5'),(2455,41,'string 5'),(2456,41,'string 5'),(2457,41,'string 5'),(2458,41,'string 5'),(2459,41,'string 5'),(2460,41,'string 5'),(2461,41,'string 5'),(2462,41,'string 5'),(2463,41,'string 5'),(2464,41,'string 5'),(2465,41,'string 5'),(2466,41,'string 5'),(2467,41,'string 5'),(2468,41,'string 5'),(2469,41,'string 5'),(2470,41,'string 5'),(2471,41,'string 5'),(2472,41,'string 5'),(2473,41,'string 5'),(2474,41,'string 5'),(2475,41,'string 5'),(2476,41,'string 5'),(2477,41,'string 5'),(2478,41,'string 5'),(2479,41,'string 5'),(2480,41,'string 5'),(2481,41,'string 5'),(2482,41,'string 5'),(2483,41,'string 5'),(2484,41,'string 5'),(2485,41,'string 5'),(2486,41,'string 5'),(2487,41,'string 5'),(2488,41,'string 5'),(2489,41,'string 5'),(2490,41,'string 5'),(2491,41,'string 5'),(2492,41,'string 5'),(2493,41,'string 5'),(2494,41,'string 5'),(2495,41,'string 5'),(2496,41,'string 5'),(2497,41,'string 5'),(2498,41,'string 5'),(2499,41,'string 5'),(2500,41,'string 5'),(2501,41,'string 5'),(2502,41,'string 5'),(2503,41,'string 5'),(2504,41,'string 5'),(2505,41,'string 5'),(2506,41,'string 5'),(2507,41,'string 5'),(2508,41,'string 5'),(2509,41,'string 5'),(2510,41,'string 5'),(2511,41,'string 5'),(2512,41,'string 5'),(2513,41,'string 5'),(2514,41,'string 5'),(2515,41,'string 5'),(2516,41,'string 5'),(2517,41,'string 5'),(2518,41,'string 5'),(2519,41,'string 5'),(2520,41,'string 5'),(2521,41,'string 5'),(2522,41,'string 5'),(2523,41,'string 5'),(2524,41,'string 5'),(2525,41,'string 5'),(2526,41,'string 5'),(2527,41,'string 5'),(2528,41,'string 5'),(2529,41,'string 5'),(2530,41,'string 5'),(2531,41,'string 5'),(2532,41,'string 5'),(2533,41,'string 5'),(2534,41,'string 5'),(2535,41,'string 5'),(2536,41,'string 5'),(2537,41,'string 5'),(2538,41,'string 5'),(2539,41,'string 5'),(2540,41,'string 5'),(2541,41,'string 5'),(2542,41,'string 5'),(2543,41,'string 5'),(2544,41,'string 5'),(2545,41,'string 5'),(2546,41,'string 5'),(2547,41,'string 5'),(2548,41,'string 5'),(2549,41,'string 5'),(2550,41,'string 5'),(2551,41,'string 5'),(2552,41,'string 5'),(2553,41,'string 5'),(2554,41,'string 5'),(2555,41,'string 5'),(2556,41,'string 5'),(2557,41,'string 5'),(2558,41,'string 5'),(2559,41,'string 5'),(2560,41,'string 5'),(2561,41,'string 5'),(2562,41,'string 5'),(2563,41,'string 5'),(2564,41,'string 5'),(2565,41,'string 5'),(2566,41,'string 5'),(2567,41,'string 5'),(2568,41,'string 5'),(2569,41,'string 5'),(2570,41,'string 5'),(2571,41,'string 5'),(2572,41,'string 5'),(2573,41,'string 5'),(2574,41,'string 5'),(2575,41,'string 5'),(2576,41,'string 5'),(2577,41,'string 5'),(2578,41,'string 5'),(2579,41,'string 5'),(2580,41,'string 5'),(2581,41,'string 5'),(2582,41,'string 5'),(2583,41,'string 5'),(2584,41,'string 5'),(2585,41,'string 5'),(2586,41,'string 5'),(2587,41,'string 5'),(2588,41,'string 5'),(2589,41,'string 5'),(2590,41,'string 5'),(2591,41,'string 5'),(2592,41,'string 5'),(2593,41,'string 5'),(2594,41,'string 5'),(2595,41,'string 5'),(2596,41,'string 5'),(2597,41,'string 5'),(2598,41,'string 5'),(2599,41,'string 5'),(2600,41,'string 5'),(2601,41,'string 5'),(2602,41,'string 5'),(2603,41,'string 5'),(2604,41,'string 5'),(2605,41,'string 5'),(2606,41,'string 5'),(2607,41,'string 5'),(2608,41,'string 5'),(2609,41,'string 5'),(2610,41,'string 5'),(2611,41,'string 5'),(2612,41,'string 5'),(2613,41,'string 5'),(2614,41,'string 5'),(2615,41,'string 5'),(2616,41,'string 5'),(2617,41,'string 5'),(2618,41,'string 5'),(2619,41,'string 5'),(2620,41,'string 5'),(2621,41,'string 5'),(2622,41,'string 5'),(2623,41,'string 5'),(2624,41,'string 5'),(2625,41,'string 5'),(2626,41,'string 5'),(2627,41,'string 5'),(2628,41,'string 5'),(2629,41,'string 5'),(2630,41,'string 5'),(2631,41,'string 5'),(2632,41,'string 5'),(2633,41,'string 5'),(2634,41,'string 5'),(2635,41,'string 5'),(2636,41,'string 5'),(2637,41,'string 5'),(2638,41,'string 5'),(2639,41,'string 5'),(2640,41,'string 5'),(2641,41,'string 5'),(2642,41,'string 5'),(2643,41,'string 5'),(2644,41,'string 5'),(2645,41,'string 5'),(2646,41,'string 5'),(2647,41,'string 5'),(2648,41,'string 5'),(2649,41,'string 5'),(2650,41,'string 5'),(2651,41,'string 5'),(2652,41,'string 5'),(2653,41,'string 5'),(2654,41,'string 5'),(2655,41,'string 5'),(2656,41,'string 5'),(2657,41,'string 5'),(2658,41,'string 5'),(2659,41,'string 5'),(2660,41,'string 5'),(2661,41,'string 5'),(2662,41,'string 5'),(2663,41,'string 5'),(2664,41,'string 5'),(2665,41,'string 5'),(2666,41,'string 5'),(2667,41,'string 5'),(2668,41,'string 5'),(2669,41,'string 5'),(2670,41,'string 5'),(2671,41,'string 5'),(2672,41,'string 5'),(2673,41,'string 5'),(2674,41,'string 5'),(2675,41,'string 5'),(2676,41,'string 5'),(2677,41,'string 5'),(2678,41,'string 5'),(2679,41,'string 5'),(2680,41,'string 5'),(2681,41,'string 5'),(2682,41,'string 5'),(2683,41,'string 5'),(2684,41,'string 5'),(2685,41,'string 5'),(2686,41,'string 5'),(2687,41,'string 5'),(2688,41,'string 5'),(2689,41,'string 5'),(2690,41,'string 5'),(2691,41,'string 5'),(2692,41,'string 5'),(2693,41,'string 5'),(2694,41,'string 5'),(2695,41,'string 5'),(2696,41,'string 5'),(2697,41,'string 5'),(2698,41,'string 5'),(2699,41,'string 5'),(2700,41,'string 5'),(2701,41,'string 5'),(2702,41,'string 5'),(2703,41,'string 5'),(2704,41,'string 5'),(2705,41,'string 5'),(2706,41,'string 5'),(2707,41,'string 5'),(2708,41,'string 5'),(2709,41,'string 5'),(2710,41,'string 5'),(2711,41,'string 5'),(2712,41,'string 5'),(2713,41,'string 5'),(2714,41,'string 5'),(2715,41,'string 5'),(2716,41,'string 5'),(2717,41,'string 5'),(2718,41,'string 5'),(2719,41,'string 5'),(2720,41,'string 5'),(2721,41,'string 5'),(2722,41,'string 5'),(2723,41,'string 5'),(2724,41,'string 5'),(2725,41,'string 5'),(2726,41,'string 5'),(2727,41,'string 5'),(2728,41,'string 5'),(2729,41,'string 5'),(2730,41,'string 5'),(2731,41,'string 5'),(2732,41,'string 5'),(2733,41,'string 5'),(2734,41,'string 5'),(2735,41,'string 5'),(2736,41,'string 5'),(2737,41,'string 5'),(2738,41,'string 5'),(2739,41,'string 5'),(2740,41,'string 5'),(2741,41,'string 5'),(2742,41,'string 5'),(2743,41,'string 5'),(2744,41,'string 5'),(2745,41,'string 5'),(2746,41,'string 5'),(2747,41,'string 5'),(2748,41,'string 5'),(2749,41,'string 5'),(2750,41,'string 5'),(2751,41,'string 5'),(2752,41,'string 5'),(2753,41,'string 5'),(2754,41,'string 5'),(2755,41,'string 5'),(2756,41,'string 5'),(2757,41,'string 5'),(2758,41,'string 5'),(2759,41,'string 5'),(2760,41,'string 5'),(2761,41,'string 5'),(2762,41,'string 5'),(2763,41,'string 5'),(2764,41,'string 5'),(2765,41,'string 5'),(2766,41,'string 5'),(2767,41,'string 5'),(2768,41,'string 5'),(2769,41,'string 5'),(2770,41,'string 5'),(2771,41,'string 5'),(2772,41,'string 5'),(2773,41,'string 5'),(2774,41,'string 5'),(2775,41,'string 5'),(2776,41,'string 5'),(2777,41,'string 5'),(2778,41,'string 5'),(2779,41,'string 5'),(2780,41,'string 5'),(2781,41,'string 5'),(2782,41,'string 5'),(2783,41,'string 5'),(2784,41,'string 5'),(2785,41,'string 5'),(2786,41,'string 5'),(2787,41,'string 5'),(2788,41,'string 5'),(2789,41,'string 5'),(2790,41,'string 5'),(2791,41,'string 5'),(2792,41,'string 5'),(2793,41,'string 5'),(2794,41,'string 5'),(2795,41,'string 5'),(2796,41,'string 5'),(2797,41,'string 5'),(2798,41,'string 5'),(2799,41,'string 5'),(2800,41,'string 5'),(2801,41,'string 5'),(2802,41,'string 5'),(2803,41,'string 5'),(2804,41,'string 5'),(2805,41,'string 5'),(2806,41,'string 5'),(2807,41,'string 5'),(2808,41,'string 5'),(2809,41,'string 5'),(2810,41,'string 5'),(2811,41,'string 5'),(2812,41,'string 5'),(2813,41,'string 5'),(2814,41,'string 5'),(2815,41,'string 5'),(2816,41,'string 5'),(2817,41,'string 5'),(2818,41,'string 5'),(2819,41,'string 5'),(2820,41,'string 5'),(2821,41,'string 5'),(2822,41,'string 5'),(2823,41,'string 5'),(2824,41,'string 5'),(2825,41,'string 5'),(2826,41,'string 5'),(2827,41,'string 5'),(2828,41,'string 5'),(2829,41,'string 5'),(2830,41,'string 5'),(2831,41,'string 5'),(2832,41,'string 5'),(2833,41,'string 5'),(2834,41,'string 5'),(2835,41,'string 5'),(2836,41,'string 5'),(2837,41,'string 5'),(2838,41,'string 5'),(2839,41,'string 5'),(2840,41,'string 5'),(2841,41,'string 5'),(2842,41,'string 5'),(2843,41,'string 5'),(2844,41,'string 5'),(2845,41,'string 5'),(2846,41,'string 5'),(2847,41,'string 5'),(2848,41,'string 5'),(2849,41,'string 5'),(2850,41,'string 5'),(2851,41,'string 5'),(2852,41,'string 5'),(2853,41,'string 5'),(2854,41,'string 5'),(2855,41,'string 5'),(2856,41,'string 5'),(2857,41,'string 5'),(2858,41,'string 5'),(2859,41,'string 5'),(2860,41,'string 5'),(2861,41,'string 5'),(2862,41,'string 5'),(2863,41,'string 5'),(2864,41,'string 5'),(2865,41,'string 5'),(2866,41,'string 5'),(2867,41,'string 5'),(2868,41,'string 5'),(2869,41,'string 5'),(2870,41,'string 5'),(2871,41,'string 5'),(2872,41,'string 5'),(2873,41,'string 5'),(2874,41,'string 5'),(2875,41,'string 5'),(2876,41,'string 5'),(2877,41,'string 5'),(2878,41,'string 5'),(2879,41,'string 5'),(2880,41,'string 5'),(2881,41,'string 5'),(2882,41,'string 5'),(2883,41,'string 5'),(2884,41,'string 5'),(2885,41,'string 5'),(2886,41,'string 5'),(2887,41,'string 5'),(2888,41,'string 5'),(2889,41,'string 5'),(2890,41,'string 5'),(2891,41,'string 5'),(2892,41,'string 5'),(2893,41,'string 5'),(2894,41,'string 5'),(2895,41,'string 5'),(2896,41,'string 5'),(2897,41,'string 5'),(2898,41,'string 5'),(2899,41,'string 5'),(2900,41,'string 5'),(2901,41,'string 5'),(2902,41,'string 5'),(2903,41,'string 5'),(2904,41,'string 5'),(2905,41,'string 5'),(2906,41,'string 5'),(2907,41,'string 5'),(2908,41,'string 5'),(2909,41,'string 5'),(2910,41,'string 5'),(2911,41,'string 5'),(2912,41,'string 5'),(2913,41,'string 5'),(2914,41,'string 5'),(2915,41,'string 5'),(2916,41,'string 5'),(2917,41,'string 5'),(2918,41,'string 5'),(2919,41,'string 5'),(2920,41,'string 5'),(2921,41,'string 5'),(2922,41,'string 5'),(2923,41,'string 5'),(2924,41,'string 5'),(2925,41,'string 5'),(2926,41,'string 5'),(2927,41,'string 5'),(2928,41,'string 5'),(2929,41,'string 5'),(2930,41,'string 5'),(2931,41,'string 5'),(2932,41,'string 5'),(2933,41,'string 5'),(2934,41,'string 5'),(2935,41,'string 5'),(2936,41,'string 5'),(2937,41,'string 5'),(2938,41,'string 5'),(2939,41,'string 5'),(2940,41,'string 5'),(2941,41,'string 5'),(2942,41,'string 5'),(2943,41,'string 5'),(2944,41,'string 5'),(2945,41,'string 5'),(2946,41,'string 5'),(2947,41,'string 5'),(2948,41,'string 5'),(2949,41,'string 5'),(2950,41,'string 5'),(2951,41,'string 5'),(2952,41,'string 5'),(2953,41,'string 5'),(2954,41,'string 5'),(2955,41,'string 5'),(2956,41,'string 5'),(2957,41,'string 5'),(2958,41,'string 5'),(2959,41,'string 5'),(2960,41,'string 5'),(2961,41,'string 5'),(2962,41,'string 5'),(2963,41,'string 5'),(2964,41,'string 5'),(2965,41,'string 5'),(2966,41,'string 5'),(2967,41,'string 5'),(2968,41,'string 5'),(2969,41,'string 5'),(2970,41,'string 5'),(2971,41,'string 5'),(2972,41,'string 5'),(2973,41,'string 5'),(2974,41,'string 5'),(2975,41,'string 5'),(2976,41,'string 5'),(2977,41,'string 5'),(2978,41,'string 5'),(2979,41,'string 5'),(2980,41,'string 5'),(2981,41,'string 5'),(2982,41,'string 5'),(2983,41,'string 5'),(2984,41,'string 5'),(2985,41,'string 5'),(2986,41,'string 5'),(2987,41,'string 5'),(2988,41,'string 5'),(2989,41,'string 5'),(2990,41,'string 5'),(2991,41,'string 5'),(2992,41,'string 5'),(2993,41,'string 5'),(2994,41,'string 5'),(2995,41,'string 5'),(2996,41,'string 5'),(2997,41,'string 5'),(2998,41,'string 5'),(2999,41,'string 5'),(3000,41,'string 5'),(3001,41,'string 5'),(3002,41,'string 5'),(3003,41,'string 5'),(3004,41,'string 5'),(3005,41,'string 5'),(3006,41,'string 5'),(3007,41,'string 5'),(3008,41,'string 5'),(3009,41,'string 5'),(3010,41,'string 5'),(3011,41,'string 5'),(3012,41,'string 5'),(3013,41,'string 5'),(3014,41,'string 5'),(3015,41,'string 5'),(3016,41,'string 5'),(3017,41,'string 5'),(3018,41,'string 5'),(3019,41,'string 5'),(3020,41,'string 5'),(3021,41,'string 5'),(3022,41,'string 5'),(3023,41,'string 5'),(3024,41,'string 5'),(3025,41,'string 5'),(3026,41,'string 5'),(3027,41,'string 5'),(3028,41,'string 5'),(3029,41,'string 5'),(3030,41,'string 5'),(3031,41,'string 5'),(3032,41,'string 5'),(3033,41,'string 5'),(3034,41,'string 5'),(3035,41,'string 5'),(3036,41,'string 5'),(3037,41,'string 5'),(3038,41,'string 5'),(3039,41,'string 5'),(3040,41,'string 5'),(3041,41,'string 5'),(3042,41,'string 5'),(3043,41,'string 5'),(3044,41,'string 5'),(3045,41,'string 5'),(3046,41,'string 5'),(3047,41,'string 5'),(3048,41,'string 5'),(3049,41,'string 5'),(3050,41,'string 5'),(3051,41,'string 5'),(3052,41,'string 5'),(3053,41,'string 5'),(3054,41,'string 5'),(3055,41,'string 5'),(3056,41,'string 5'),(3057,41,'string 5'),(3058,41,'string 5'),(3059,41,'string 5'),(3060,41,'string 5'),(3061,41,'string 5'),(3062,41,'string 5'),(3063,41,'string 5'),(3064,41,'string 5'),(3065,41,'string 5'),(3066,41,'string 5'),(3067,41,'string 5'),(3068,41,'string 5'),(3069,41,'string 5'),(3070,41,'string 5'),(3071,41,'string 5'),(3072,41,'string 5'),(3073,41,'string 5'),(3074,41,'string 5'),(3075,41,'string 5'),(3076,41,'string 5'),(3077,41,'string 5'),(3078,41,'string 5'),(3079,41,'string 5'),(3080,41,'string 5'),(3081,41,'string 5'),(3082,41,'string 5'),(3083,41,'string 5'),(3084,41,'string 5'),(3085,41,'string 5'),(3086,41,'string 5'),(3087,41,'string 5'),(3088,41,'string 5'),(3089,41,'string 5'),(3090,41,'string 5'),(3091,41,'string 5'),(3092,41,'string 5'),(3093,41,'string 5'),(3094,41,'string 5'),(3095,41,'string 5'),(3096,41,'string 5'),(3097,41,'string 5'),(3098,41,'string 5'),(3099,41,'string 5'),(3100,41,'string 5'),(3101,41,'string 5'),(3102,41,'string 5'),(3103,41,'string 5'),(3104,41,'string 5'),(3105,41,'string 5'),(3106,41,'string 5'),(3107,41,'string 5'),(3108,41,'string 5'),(3109,41,'string 5'),(3110,41,'string 5'),(3111,41,'string 5'),(3112,41,'string 5'),(3113,41,'string 5'),(3114,41,'string 5'),(3115,41,'string 5'),(3116,41,'string 5'),(3117,41,'string 5'),(3118,41,'string 5'),(3119,41,'string 5'),(3120,41,'string 5'),(3121,41,'string 5'),(3122,41,'string 5'),(3123,41,'string 5'),(3124,41,'string 5'),(3125,41,'string 5'),(3126,41,'string 5'),(3127,41,'string 5'),(3128,41,'string 5'),(3129,41,'string 5'),(3130,41,'string 5'),(3131,41,'string 5'),(3132,41,'string 5'),(3133,41,'string 5'),(3134,41,'string 5'),(3135,41,'string 5'),(3136,41,'string 5'),(3137,41,'string 5'),(3138,41,'string 5'),(3139,41,'string 5'),(3140,41,'string 5'),(3141,41,'string 5'),(3142,41,'string 5'),(3143,41,'string 5'),(3144,41,'string 5'),(3145,41,'string 5'),(3146,41,'string 5'),(3147,41,'string 5'),(3148,41,'string 5'),(3149,41,'string 5'),(3150,41,'string 5'),(3151,41,'string 5'),(3152,41,'string 5'),(3153,41,'string 5'),(3154,41,'string 5'),(3155,41,'string 5'),(3156,41,'string 5'),(3157,41,'string 5'),(3158,41,'string 5'),(3159,41,'string 5'),(3160,41,'string 5'),(3161,41,'string 5'),(3162,41,'string 5'),(3163,41,'string 5'),(3164,41,'string 5'),(3165,41,'string 5'),(3166,41,'string 5'),(3167,41,'string 5'),(3168,41,'string 5'),(3169,41,'string 5'),(3170,41,'string 5'),(3171,41,'string 5'),(3172,41,'string 5'),(3173,41,'string 5'),(3174,41,'string 5'),(3175,41,'string 5'),(3176,41,'string 5'),(3177,41,'string 5'),(3178,41,'string 5'),(3179,41,'string 5'),(3180,41,'string 5'),(3181,41,'string 5'),(3182,41,'string 5'),(3183,41,'string 5'),(3184,41,'string 5'),(3185,41,'string 5'),(3186,41,'string 5'),(3187,41,'string 5'),(3188,41,'string 5'),(3189,41,'string 5'),(3190,41,'string 5'),(3191,41,'string 5'),(3192,41,'string 5'),(3193,41,'string 5'),(3194,41,'string 5'),(3195,41,'string 5'),(3196,41,'string 5'),(3197,41,'string 5'),(3198,41,'string 5'),(3199,41,'string 5'),(3200,41,'string 5'),(3201,41,'string 5'),(3202,41,'string 5'),(3203,41,'string 5'),(3204,41,'string 5'),(3205,41,'string 5'),(3206,41,'string 5'),(3207,41,'string 5'),(3208,41,'string 5'),(3209,41,'string 5'),(3210,41,'string 5'),(3211,41,'string 5'),(3212,41,'string 5'),(3213,41,'string 5'),(3214,41,'string 5'),(3215,41,'string 5'),(3216,41,'string 5'),(3217,41,'string 5'),(3218,41,'string 5'),(3219,41,'string 5'),(3220,41,'string 5'),(3221,41,'string 5'),(3222,41,'string 5'),(3223,41,'string 5'),(3224,41,'string 5'),(3225,41,'string 5'),(3226,41,'string 5'),(3227,41,'string 5'),(3228,41,'string 5'),(3229,41,'string 5'),(3230,41,'string 5'),(3231,41,'string 5'),(3232,41,'string 5'),(3233,41,'string 5'),(3234,41,'string 5'),(3235,41,'string 5'),(3236,41,'string 5'),(3237,41,'string 5'),(3238,41,'string 5'),(3239,41,'string 5'),(3240,41,'string 5'),(3241,41,'string 5'),(3242,41,'string 5'),(3243,41,'string 5'),(3244,41,'string 5'),(3245,41,'string 5'),(3246,41,'string 5'),(3247,41,'string 5'),(3248,41,'string 5'),(3249,41,'string 5'),(3250,41,'string 5'),(3251,41,'string 5'),(3252,41,'string 5'),(3253,41,'string 5'),(3254,41,'string 5'),(3255,41,'string 5'),(3256,41,'string 5'),(3257,41,'string 5'),(3258,41,'string 5'),(3259,41,'string 5'),(3260,41,'string 5'),(3261,41,'string 5'),(3262,41,'string 5'),(3263,41,'string 5'),(3264,41,'string 5'),(3265,41,'string 5'),(3266,41,'string 5'),(3267,41,'string 5'),(3268,41,'string 5'),(3269,41,'string 5'),(3270,41,'string 5'),(3271,41,'string 5'),(3272,41,'string 5'),(3273,41,'string 5'),(3274,41,'string 5'),(3275,41,'string 5'),(3276,41,'string 5'),(3277,41,'string 5'),(3278,41,'string 5'),(3279,41,'string 5'),(3280,41,'string 5'),(3281,41,'string 5'),(3282,41,'string 5'),(3283,41,'string 5'),(3284,41,'string 5'),(3285,41,'string 5'),(3286,41,'string 5'),(3287,41,'string 5'),(3288,41,'string 5'),(3289,41,'string 5'),(3290,41,'string 5'),(3291,41,'string 5'),(3292,41,'string 5'),(3293,41,'string 5'),(3294,41,'string 5'),(3295,41,'string 5'),(3296,41,'string 5'),(3297,41,'string 5'),(3298,41,'string 5'),(3299,41,'string 5'),(3300,41,'string 5'),(3301,41,'string 5'),(3302,41,'string 5'),(3303,41,'string 5'),(3304,41,'string 5'),(3305,41,'string 5'),(3306,41,'string 5'),(3307,41,'string 5'),(3308,41,'string 5'),(3309,41,'string 5'),(3310,41,'string 5'),(3311,41,'string 5'),(3312,41,'string 5'),(3313,41,'string 5'),(3314,41,'string 5'),(3315,41,'string 5'),(3316,41,'string 5'),(3317,41,'string 5'),(3318,41,'string 5'),(3319,41,'string 5'),(3320,41,'string 5'),(3321,41,'string 5'),(3322,41,'string 5'),(3323,41,'string 5'),(3324,41,'string 5'),(3325,41,'string 5'),(3326,41,'string 5'),(3327,41,'string 5'),(3328,41,'string 5'),(3329,41,'string 5'),(3330,41,'string 5'),(3331,41,'string 5'),(3332,41,'string 5'),(3333,41,'string 5'),(3334,41,'string 5'),(3335,41,'string 5'),(3336,41,'string 5'),(3337,41,'string 5'),(3338,41,'string 5'),(3339,41,'string 5'),(3340,41,'string 5'),(3341,41,'string 5'),(3342,41,'string 5'),(3343,41,'string 5'),(3344,41,'string 5'),(3345,41,'string 5'),(3346,41,'string 5'),(3347,41,'string 5'),(3348,41,'string 5'),(3349,41,'string 5'),(3350,41,'string 5'),(3351,41,'string 5'),(3352,41,'string 5'),(3353,41,'string 5'),(3354,41,'string 5'),(3355,41,'string 5'),(3356,41,'string 5'),(3357,41,'string 5'),(3358,41,'string 5'),(3359,41,'string 5'),(3360,41,'string 5'),(3361,41,'string 5'),(3362,41,'string 5'),(3363,41,'string 5'),(3364,41,'string 5'),(3365,41,'string 5'),(3366,41,'string 5'),(3367,41,'string 5'),(3368,41,'string 5'),(3369,41,'string 5'),(3370,41,'string 5'),(3371,41,'string 5'),(3372,41,'string 5'),(3373,41,'string 5'),(3374,41,'string 5'),(3375,41,'string 5'),(3376,41,'string 5'),(3377,41,'string 5'),(3378,41,'string 5'),(3379,41,'string 5'),(3380,41,'string 5'),(3381,41,'string 5'),(3382,41,'string 5'),(3383,41,'string 5'),(3384,41,'string 5'),(3385,41,'string 5'),(3386,41,'string 5'),(3387,41,'string 5'),(3388,41,'string 5'),(3389,41,'string 5'),(3390,41,'string 5'),(3391,41,'string 5'),(3392,41,'string 5'),(3393,41,'string 5'),(3394,41,'string 5'),(3395,41,'string 5'),(3396,41,'string 5'),(3397,41,'string 5'),(3398,41,'string 5'),(3399,41,'string 5'),(3400,41,'string 5'),(3401,41,'string 5'),(3402,41,'string 5'),(3403,41,'string 5'),(3404,41,'string 5'),(3405,41,'string 5'),(3406,41,'string 5'),(3407,41,'string 5'),(3408,41,'string 5'),(3409,41,'string 5'),(3410,41,'string 5'),(3411,41,'string 5'),(3412,41,'string 5'),(3413,41,'string 5'),(3414,41,'string 5'),(3415,41,'string 5'),(3416,41,'string 5'),(3417,41,'string 5'),(3418,41,'string 5'),(3419,41,'string 5'),(3420,41,'string 5'),(3421,41,'string 5'),(3422,41,'string 5'),(3423,41,'string 5'),(3424,41,'string 5'),(3425,41,'string 5'),(3426,41,'string 5'),(3427,41,'string 5'),(3428,41,'string 5'),(3429,41,'string 5'),(3430,41,'string 5'),(3431,41,'string 5'),(3432,41,'string 5'),(3433,41,'string 5'),(3434,41,'string 5'),(3435,41,'string 5'),(3436,41,'string 5'),(3437,41,'string 5'),(3438,41,'string 5'),(3439,41,'string 5'),(3440,41,'string 5'),(3441,41,'string 5'),(3442,41,'string 5'),(3443,41,'string 5'),(3444,41,'string 5'),(3445,41,'string 5'),(3446,41,'string 5'),(3447,41,'string 5'),(3448,41,'string 5'),(3449,41,'string 5'),(3450,41,'string 5'),(3451,41,'string 5'),(3452,41,'string 5'),(3453,41,'string 5'),(3454,41,'string 5'),(3455,41,'string 5'),(3456,41,'string 5'),(3457,41,'string 5'),(3458,41,'string 5'),(3459,41,'string 5'),(3460,41,'string 5'),(3461,41,'string 5'),(3462,41,'string 5'),(3463,41,'string 5'),(3464,41,'string 5'),(3465,41,'string 5'),(3466,41,'string 5'),(3467,41,'string 5'),(3468,41,'string 5'),(3469,41,'string 5'),(3470,41,'string 5'),(3471,41,'string 5'),(3472,41,'string 5'),(3473,41,'string 5'),(3474,41,'string 5'),(3475,41,'string 5'),(3476,41,'string 5'),(3477,41,'string 5'),(3478,41,'string 5'),(3479,41,'string 5'),(3480,41,'string 5'),(3481,41,'string 5'),(3482,41,'string 5'),(3483,41,'string 5'),(3484,41,'string 5'),(3485,41,'string 5'),(3486,41,'string 5'),(3487,41,'string 5'),(3488,41,'string 5'),(3489,41,'string 5'),(3490,41,'string 5'),(3491,41,'string 5'),(3492,41,'string 5'),(3493,41,'string 5'),(3494,41,'string 5'),(3495,41,'string 5'),(3496,41,'string 5'),(3497,41,'string 5'),(3498,41,'string 5'),(3499,41,'string 5'),(3500,41,'string 5'),(3501,41,'string 5'),(3502,41,'string 5'),(3503,41,'string 5'),(3504,41,'string 5'),(3505,41,'string 5'),(3506,41,'string 5'),(3507,41,'string 5'),(3508,41,'string 5'),(3509,41,'string 5'),(3510,41,'string 5'),(3511,41,'string 5'),(3512,41,'string 5'),(3513,41,'string 5'),(3514,41,'string 5'),(3515,41,'string 5'),(3516,41,'string 5'),(3517,41,'string 5'),(3518,41,'string 5'),(3519,41,'string 5'),(3520,41,'string 5'),(3521,41,'string 5'),(3522,41,'string 5'),(3523,41,'string 5'),(3524,41,'string 5'),(3525,41,'string 5'),(3526,41,'string 5'),(3527,41,'string 5'),(3528,41,'string 5'),(3529,41,'string 5'),(3530,41,'string 5'),(3531,41,'string 5'),(3532,41,'string 5'),(3533,41,'string 5'),(3534,41,'string 5'),(3535,41,'string 5'),(3536,41,'string 5'),(3537,41,'string 5'),(3538,41,'string 5'),(3539,41,'string 5'),(3540,41,'string 5'),(3541,41,'string 5'),(3542,41,'string 5'),(3543,41,'string 5'),(3544,41,'string 5'),(3545,41,'string 5'),(3546,41,'string 5'),(3547,41,'string 5'),(3548,41,'string 5'),(3549,41,'string 5'),(3550,41,'string 5'),(3551,41,'string 5'),(3552,41,'string 5'),(3553,41,'string 5'),(3554,41,'string 5'),(3555,41,'string 5'),(3556,41,'string 5'),(3557,41,'string 5'),(3558,41,'string 5'),(3559,41,'string 5'),(3560,41,'string 5'),(3561,41,'string 5'),(3562,41,'string 5'),(3563,41,'string 5'),(3564,41,'string 5'),(3565,41,'string 5'),(3566,41,'string 5'),(3567,41,'string 5'),(3568,41,'string 5'),(3569,41,'string 5'),(3570,41,'string 5'),(3571,41,'string 5'),(3572,41,'string 5'),(3573,41,'string 5'),(3574,41,'string 5'),(3575,41,'string 5'),(3576,41,'string 5'),(3577,41,'string 5'),(3578,41,'string 5'),(3579,41,'string 5'),(3580,41,'string 5'),(3581,41,'string 5'),(3582,41,'string 5'),(3583,41,'string 5'),(3584,41,'string 5'),(3585,41,'string 5'),(3586,41,'string 5'),(3587,41,'string 5'),(3588,41,'string 5'),(3589,41,'string 5'),(3590,41,'string 5'),(3591,41,'string 5'),(3592,41,'string 5'),(3593,41,'string 5'),(3594,41,'string 5'),(3595,41,'string 5'),(3596,41,'string 5'),(3597,41,'string 5'),(3598,41,'string 5'),(3599,41,'string 5'),(3600,41,'string 5'),(3601,41,'string 5'),(3602,41,'string 5'),(3603,41,'string 5'),(3604,41,'string 5'),(3605,41,'string 5'),(3606,41,'string 5'),(3607,41,'string 5'),(3608,41,'string 5'),(3609,41,'string 5'),(3610,41,'string 5'),(3611,41,'string 5'),(3612,41,'string 5'),(3613,41,'string 5'),(3614,41,'string 5'),(3615,41,'string 5'),(3616,41,'string 5'),(3617,41,'string 5'),(3618,41,'string 5'),(3619,41,'string 5'),(3620,41,'string 5'),(3621,41,'string 5'),(3622,41,'string 5'),(3623,41,'string 5'),(3624,41,'string 5'),(3625,41,'string 5'),(3626,41,'string 5'),(3627,41,'string 5'),(3628,41,'string 5'),(3629,41,'string 5'),(3630,41,'string 5'),(3631,41,'string 5'),(3632,41,'string 5'),(3633,41,'string 5'),(3634,41,'string 5'),(3635,41,'string 5'),(3636,41,'string 5'),(3637,41,'string 5'),(3638,41,'string 5'),(3639,41,'string 5'),(3640,41,'string 5'),(3641,41,'string 5'),(3642,41,'string 5'),(3643,41,'string 5'),(3644,41,'string 5'),(3645,41,'string 5'),(3646,41,'string 5'),(3647,41,'string 5'),(3648,41,'string 5'),(3649,41,'string 5'),(3650,41,'string 5'),(3651,41,'string 5'),(3652,41,'string 5'),(3653,41,'string 5'),(3654,41,'string 5'),(3655,41,'string 5'),(3656,41,'string 5'),(3657,41,'string 5'),(3658,41,'string 5'),(3659,41,'string 5'),(3660,41,'string 5'),(3661,41,'string 5'),(3662,41,'string 5'),(3663,41,'string 5'),(3664,41,'string 5'),(3665,41,'string 5'),(3666,41,'string 5'),(3667,41,'string 5'),(3668,41,'string 5'),(3669,41,'string 5'),(3670,41,'string 5'),(3671,41,'string 5'),(3672,41,'string 5'),(3673,41,'string 5'),(3674,41,'string 5'),(3675,41,'string 5'),(3676,41,'string 5'),(3677,41,'string 5'),(3678,41,'string 5'),(3679,41,'string 5'),(3680,41,'string 5'),(3681,41,'string 5'),(3682,41,'string 5'),(3683,41,'string 5'),(3684,41,'string 5'),(3685,41,'string 5'),(3686,41,'string 5'),(3687,41,'string 5'),(3688,41,'string 5'),(3689,41,'string 5'),(3690,41,'string 5'),(3691,41,'string 5'),(3692,41,'string 5'),(3693,41,'string 5'),(3694,41,'string 5'),(3695,41,'string 5'),(3696,41,'string 5'),(3697,41,'string 5'),(3698,41,'string 5'),(3699,41,'string 5'),(3700,41,'string 5'),(3701,41,'string 5'),(3702,41,'string 5'),(3703,41,'string 5'),(3704,41,'string 5'),(3705,41,'string 5'),(3706,41,'string 5'),(3707,41,'string 5'),(3708,41,'string 5'),(3709,41,'string 5'),(3710,41,'string 5'),(3711,41,'string 5'),(3712,41,'string 5'),(3713,41,'string 5'),(3714,41,'string 5'),(3715,41,'string 5'),(3716,41,'string 5'),(3717,41,'string 5'),(3718,41,'string 5'),(3719,41,'string 5'),(3720,41,'string 5'),(3721,41,'string 5'),(3722,41,'string 5'),(3723,41,'string 5'),(3724,41,'string 5'),(3725,41,'string 5'),(3726,41,'string 5'),(3727,41,'string 5'),(3728,41,'string 5'),(3729,41,'string 5'),(3730,41,'string 5'),(3731,41,'string 5'),(3732,41,'string 5'),(3733,41,'string 5'),(3734,41,'string 5'),(3735,41,'string 5'),(3736,41,'string 5'),(3737,41,'string 5'),(3738,41,'string 5'),(3739,41,'string 5'),(3740,41,'string 5'),(3741,41,'string 5'),(3742,41,'string 5'),(3743,41,'string 5'),(3744,41,'string 5'),(3745,41,'string 5'),(3746,41,'string 5'),(3747,41,'string 5'),(3748,41,'string 5'),(3749,41,'string 5'),(3750,41,'string 5'),(3751,41,'string 5'),(3752,41,'string 5'),(3753,41,'string 5'),(3754,41,'string 5'),(3755,41,'string 5'),(3756,41,'string 5'),(3757,41,'string 5'),(3758,41,'string 5'),(3759,41,'string 5'),(3760,41,'string 5'),(3761,41,'string 5'),(3762,41,'string 5'),(3763,41,'string 5'),(3764,41,'string 5'),(3765,41,'string 5'),(3766,41,'string 5'),(3767,41,'string 5'),(3768,41,'string 5'),(3769,41,'string 5'),(3770,41,'string 5'),(3771,41,'string 5'),(3772,41,'string 5'),(3773,41,'string 5'),(3774,41,'string 5'),(3775,41,'string 5'),(3776,41,'string 5'),(3777,41,'string 5'),(3778,41,'string 5'),(3779,41,'string 5'),(3780,41,'string 5'),(3781,41,'string 5'),(3782,41,'string 5'),(3783,41,'string 5'),(3784,41,'string 5'),(3785,41,'string 5'),(3786,41,'string 5'),(3787,41,'string 5'),(3788,41,'string 5'),(3789,41,'string 5'),(3790,41,'string 5'),(3791,41,'string 5'),(3792,41,'string 5'),(3793,41,'string 5'),(3794,41,'string 5'),(3795,41,'string 5'),(3796,41,'string 5'),(3797,41,'string 5'),(3798,41,'string 5'),(3799,41,'string 5'),(3800,41,'string 5'),(3801,41,'string 5'),(3802,41,'string 5'),(3803,41,'string 5'),(3804,41,'string 5'),(3805,41,'string 5'),(3806,41,'string 5'),(3807,41,'string 5'),(3808,41,'string 5'),(3809,41,'string 5'),(3810,41,'string 5'),(3811,41,'string 5'),(3812,41,'string 5'),(3813,41,'string 5'),(3814,41,'string 5'),(3815,41,'string 5'),(3816,41,'string 5'),(3817,41,'string 5'),(3818,41,'string 5'),(3819,41,'string 5'),(3820,41,'string 5'),(3821,41,'string 5'),(3822,41,'string 5'),(3823,41,'string 5'),(3824,41,'string 5'),(3825,41,'string 5'),(3826,41,'string 5'),(3827,41,'string 5'),(3828,41,'string 5'),(3829,41,'string 5'),(3830,41,'string 5'),(3831,41,'string 5'),(3832,41,'string 5'),(3833,41,'string 5'),(3834,41,'string 5'),(3835,41,'string 5'),(3836,41,'string 5'),(3837,41,'string 5'),(3838,41,'string 5'),(3839,41,'string 5'),(3840,41,'string 5'),(3841,41,'string 5'),(3842,41,'string 5'),(3843,41,'string 5'),(3844,41,'string 5'),(3845,41,'string 5'),(3846,41,'string 5'),(3847,41,'string 5'),(3848,41,'string 5'),(3849,41,'string 5'),(3850,41,'string 5'),(3851,41,'string 5'),(3852,41,'string 5'),(3853,41,'string 5'),(3854,41,'string 5'),(3855,41,'string 5'),(3856,41,'string 5'),(3857,41,'string 5'),(3858,41,'string 5'),(3859,41,'string 5'),(3860,41,'string 5'),(3861,41,'string 5'),(3862,41,'string 5'),(3863,41,'string 5'),(3864,41,'string 5'),(3865,41,'string 5'),(3866,41,'string 5'),(3867,41,'string 5'),(3868,41,'string 5'),(3869,41,'string 5'),(3870,41,'string 5'),(3871,41,'string 5'),(3872,41,'string 5'),(3873,41,'string 5'),(3874,41,'string 5'),(3875,41,'string 5'),(3876,41,'string 5'),(3877,41,'string 5'),(3878,41,'string 5'),(3879,41,'string 5'),(3880,41,'string 5'),(3881,41,'string 5'),(3882,41,'string 5'),(3883,41,'string 5'),(3884,41,'string 5'),(3885,41,'string 5'),(3886,41,'string 5'),(3887,41,'string 5'),(3888,41,'string 5'),(3889,41,'string 5'),(3890,41,'string 5'),(3891,41,'string 5'),(3892,41,'string 5'),(3893,41,'string 5'),(3894,41,'string 5'),(3895,41,'string 5'),(3896,41,'string 5'),(3897,41,'string 5'),(3898,41,'string 5'),(3899,41,'string 5'),(3900,41,'string 5'),(3901,41,'string 5'),(3902,41,'string 5'),(3903,41,'string 5'),(3904,41,'string 5'),(3905,41,'string 5'),(3906,41,'string 5'),(3907,41,'string 5'),(3908,41,'string 5'),(3909,41,'string 5'),(3910,41,'string 5'),(3911,41,'string 5'),(3912,41,'string 5'),(3913,41,'string 5'),(3914,41,'string 5'),(3915,41,'string 5'),(3916,41,'string 5'),(3917,41,'string 5'),(3918,41,'string 5'),(3919,41,'string 5'),(3920,41,'string 5'),(3921,41,'string 5'),(3922,41,'string 5'),(3923,41,'string 5'),(3924,41,'string 5'),(3925,41,'string 5'),(3926,41,'string 5'),(3927,41,'string 5'),(3928,41,'string 5'),(3929,41,'string 5'),(3930,41,'string 5'),(3931,41,'string 5'),(3932,41,'string 5'),(3933,41,'string 5'),(3934,41,'string 5'),(3935,41,'string 5'),(3936,41,'string 5'),(3937,41,'string 5'),(3938,41,'string 5'),(3939,41,'string 5'),(3940,41,'string 5'),(3941,41,'string 5'),(3942,41,'string 5'),(3943,41,'string 5'),(3944,41,'string 5'),(3945,41,'string 5'),(3946,41,'string 5'),(3947,41,'string 5'),(3948,41,'string 5'),(3949,41,'string 5'),(3950,41,'string 5'),(3951,41,'string 5'),(3952,41,'string 5'),(3953,41,'string 5'),(3954,41,'string 5'),(3955,41,'string 5'),(3956,41,'string 5'),(3957,41,'string 5'),(3958,41,'string 5'),(3959,41,'string 5'),(3960,41,'string 5'),(3961,41,'string 5'),(3962,41,'string 5'),(3963,41,'string 5'),(3964,41,'string 5'),(3965,41,'string 5'),(3966,41,'string 5'),(3967,41,'string 5'),(3968,41,'string 5'),(3969,41,'string 5'),(3970,41,'string 5'),(3971,41,'string 5'),(3972,41,'string 5'),(3973,41,'string 5'),(3974,41,'string 5'),(3975,41,'string 5'),(3976,41,'string 5'),(3977,41,'string 5'),(3978,41,'string 5'),(3979,41,'string 5'),(3980,41,'string 5'),(3981,41,'string 5'),(3982,41,'string 5'),(3983,41,'string 5'),(3984,41,'string 5'),(3985,41,'string 5'),(3986,41,'string 5'),(3987,41,'string 5'),(3988,41,'string 5'),(3989,41,'string 5'),(3990,41,'string 5'),(3991,41,'string 5'),(3992,41,'string 5'),(3993,41,'string 5'),(3994,41,'string 5'),(3995,41,'string 5'),(3996,41,'string 5'),(3997,41,'string 5'),(3998,41,'string 5'),(3999,41,'string 5'),(4000,41,'string 5'),(4001,41,'string 5'),(4002,41,'string 5'),(4003,41,'string 5'),(4004,41,'string 5'),(4005,41,'string 5'),(4006,41,'string 5'),(4007,41,'string 5'),(4008,41,'string 5'),(4009,41,'string 5'),(4010,41,'string 5'),(4011,41,'string 5'),(4012,41,'string 5'),(4013,41,'string 5'),(4014,41,'string 5'),(4015,41,'string 5'),(4016,41,'string 5'),(4017,41,'string 5'),(4018,41,'string 5'),(4019,41,'string 5'),(4020,41,'string 5'),(4021,41,'string 5'),(4022,41,'string 5'),(4023,41,'string 5'),(4024,41,'string 5'),(4025,41,'string 5'),(4026,41,'string 5'),(4027,41,'string 5'),(4028,41,'string 5'),(4029,41,'string 5'),(4030,41,'string 5'),(4031,41,'string 5'),(4032,41,'string 5'),(4033,41,'string 5'),(4034,41,'string 5'),(4035,41,'string 5'),(4036,41,'string 5'),(4037,41,'string 5'),(4038,41,'string 5'),(4039,41,'string 5'),(4040,41,'string 5'),(4041,41,'string 5'),(4042,41,'string 5'),(4043,41,'string 5'),(4044,41,'string 5'),(4045,41,'string 5'),(4046,41,'string 5'),(4047,41,'string 5'),(4048,41,'string 5'),(4049,41,'string 5'),(4050,41,'string 5'),(4051,41,'string 5'),(4052,41,'string 5'),(4053,41,'string 5'),(4054,41,'string 5'),(4055,41,'string 5'),(4056,41,'string 5'),(4057,41,'string 5'),(4058,41,'string 5'),(4059,41,'string 5'),(4060,41,'string 5'),(4061,41,'string 5'),(4062,41,'string 5'),(4063,41,'string 5'),(4064,41,'string 5'),(4065,41,'string 5'),(4066,41,'string 5'),(4067,41,'string 5'),(4068,41,'string 5'),(4069,41,'string 5'),(4070,41,'string 5'),(4071,41,'string 5'),(4072,41,'string 5'),(4073,41,'string 5'),(4074,41,'string 5'),(4075,41,'string 5'),(4076,41,'string 5'),(4077,41,'string 5'),(4078,41,'string 5'),(4079,41,'string 5'),(4080,41,'string 5'),(4081,41,'string 5'),(4082,41,'string 5'),(4083,41,'string 5'),(4084,41,'string 5'),(4085,41,'string 5'),(4086,41,'string 5'),(4087,41,'string 5'),(4088,41,'string 5'),(4089,41,'string 5'),(4090,41,'string 5'),(4091,41,'string 5'),(4092,41,'string 5'),(4093,41,'string 5'),(4094,41,'string 5'),(4095,41,'string 5'),(4096,41,'string 5'),(4097,41,'string 5'),(4098,41,'string 5'),(4099,41,'string 5'),(4100,41,'string 5'),(4101,41,'string 5'),(4102,41,'string 5'),(4103,41,'string 5'),(4104,41,'string 5'),(4105,41,'string 5'),(4106,41,'string 5'),(4107,41,'string 5'),(4108,41,'string 5'),(4109,41,'string 5'),(4110,41,'string 5'),(4111,41,'string 5'),(4112,41,'string 5'),(4113,41,'string 5'),(4114,41,'string 5'),(4115,41,'string 5'),(4116,41,'string 5'),(4117,41,'string 5'),(4118,41,'string 5'),(4119,41,'string 5'),(4120,41,'string 5'),(4121,41,'string 5'),(4122,41,'string 5'),(4123,41,'string 5'),(4124,41,'string 5'),(4125,41,'string 5'),(4126,41,'string 5'),(4127,41,'string 5'),(4128,41,'string 5'),(4129,41,'string 5'),(4130,41,'string 5'),(4131,41,'string 5'),(4132,41,'string 5'),(4133,41,'string 5'),(4134,41,'string 5'),(4135,41,'string 5'),(4136,41,'string 5'),(4137,41,'string 5'),(4138,41,'string 5'),(4139,41,'string 5'),(4140,41,'string 5'),(4141,41,'string 5'),(4142,41,'string 5'),(4143,41,'string 5'),(4144,41,'string 5'),(4145,41,'string 5'),(4146,41,'string 5'),(4147,41,'string 5'),(4148,41,'string 5'),(4149,41,'string 5'),(4150,41,'string 5'),(4151,41,'string 5'),(4152,41,'string 5'),(4153,41,'string 5'),(4154,41,'string 5'),(4155,41,'string 5'),(4156,41,'string 5'),(4157,41,'string 5'),(4158,41,'string 5'),(4159,41,'string 5'),(4160,41,'string 5'),(4161,41,'string 5'),(4162,41,'string 5'),(4163,41,'string 5'),(4164,41,'string 5'),(4165,41,'string 5'),(4166,41,'string 5'),(4167,41,'string 5'),(4168,41,'string 5'),(4169,41,'string 5'),(4170,41,'string 5'),(4171,41,'string 5'),(4172,41,'string 5'),(4173,41,'string 5'),(4174,41,'string 5'),(4175,41,'string 5'),(4176,41,'string 5'),(4177,41,'string 5'),(4178,41,'string 5'),(4179,41,'string 5'),(4180,41,'string 5'),(4181,41,'string 5'),(4182,41,'string 5'),(4183,41,'string 5'),(4184,41,'string 5'),(4185,41,'string 5'),(4186,41,'string 5'),(4187,41,'string 5'),(4188,41,'string 5'),(4189,41,'string 5'),(4190,41,'string 5'),(4191,41,'string 5'),(4192,41,'string 5'),(4193,41,'string 5'),(4194,41,'string 5'),(4195,41,'string 5'),(4196,41,'string 5'),(4197,41,'string 5'),(4198,41,'string 5'),(4199,41,'string 5'),(4200,41,'string 5'),(4201,41,'string 5'),(4202,41,'string 5'),(4203,41,'string 5'),(4204,41,'string 5'),(4205,41,'string 5'),(4206,41,'string 5'),(4207,41,'string 5'),(4208,41,'string 5'),(4209,41,'string 5'),(4210,41,'string 5'),(4211,41,'string 5'),(4212,41,'string 5'),(4213,41,'string 5'),(4214,41,'string 5'),(4215,41,'string 5'),(4216,41,'string 5'),(4217,41,'string 5'),(4218,41,'string 5'),(4219,41,'string 5'),(4220,41,'string 5'),(4221,41,'string 5'),(4222,41,'string 5'),(4223,41,'string 5'),(4224,41,'string 5'),(4225,41,'string 5'),(4226,41,'string 5'),(4227,41,'string 5'),(4228,41,'string 5'),(4229,41,'string 5'),(4230,41,'string 5'),(4231,41,'string 5'),(4232,41,'string 5'),(4233,41,'string 5'),(4234,41,'string 5'),(4235,41,'string 5'),(4236,41,'string 5'),(4237,41,'string 5'),(4238,41,'string 5'),(4239,41,'string 5'),(4240,41,'string 5'),(4241,41,'string 5'),(4242,41,'string 5'),(4243,41,'string 5'),(4244,41,'string 5'),(4245,41,'string 5'),(4246,41,'string 5'),(4247,41,'string 5'),(4248,41,'string 5'),(4249,41,'string 5'),(4250,41,'string 5'),(4251,41,'string 5'),(4252,41,'string 5'),(4253,41,'string 5'),(4254,41,'string 5'),(4255,41,'string 5'),(4256,41,'string 5'),(4257,41,'string 5'),(4258,41,'string 5'),(4259,41,'string 5'),(4260,41,'string 5'),(4261,41,'string 5'),(4262,41,'string 5'),(4263,41,'string 5'),(4264,41,'string 5'),(4265,41,'string 5'),(4266,41,'string 5'),(4267,41,'string 5'),(4268,41,'string 5'),(4269,41,'string 5'),(4270,41,'string 5'),(4271,41,'string 5'),(4272,41,'string 5'),(4273,41,'string 5'),(4274,41,'string 5'),(4275,41,'string 5'),(4276,41,'string 5'),(4277,41,'string 5'),(4278,41,'string 5'),(4279,41,'string 5'),(4280,41,'string 5'),(4281,41,'string 5'),(4282,41,'string 5'),(4283,41,'string 5'),(4284,41,'string 5'),(4285,41,'string 5'),(4286,41,'string 5'),(4287,41,'string 5'),(4288,41,'string 5'),(4289,41,'string 5'),(4290,41,'string 5'),(4291,41,'string 5'),(4292,41,'string 5'),(4293,41,'string 5'),(4294,41,'string 5'),(4295,41,'string 5'),(4296,41,'string 5'),(4297,41,'string 5'),(4298,41,'string 5'),(4299,41,'string 5'),(4300,41,'string 5'),(4301,41,'string 5'),(4302,41,'string 5'),(4303,41,'string 5'),(4304,41,'string 5'),(4305,41,'string 5'),(4306,41,'string 5'),(4307,41,'string 5'),(4308,41,'string 5'),(4309,41,'string 5'),(4310,41,'string 5'),(4311,41,'string 5'),(4312,41,'string 5'),(4313,41,'string 5'),(4314,41,'string 5'),(4315,41,'string 5'),(4316,41,'string 5'),(4317,41,'string 5'),(4318,41,'string 5'),(4319,41,'string 5'),(4320,41,'string 5'),(4321,41,'string 5'),(4322,41,'string 5'),(4323,41,'string 5'),(4324,41,'string 5'),(4325,41,'string 5'),(4326,41,'string 5'),(4327,41,'string 5'),(4328,41,'string 5'),(4329,41,'string 5'),(4330,41,'string 5'),(4331,41,'string 5'),(4332,41,'string 5'),(4333,41,'string 5'),(4334,41,'string 5'),(4335,41,'string 5'),(4336,41,'string 5'),(4337,41,'string 5'),(4338,41,'string 5'),(4339,41,'string 5'),(4340,41,'string 5'),(4341,41,'string 5'),(4342,41,'string 5'),(4343,41,'string 5'),(4344,41,'string 5'),(4345,41,'string 5'),(4346,41,'string 5'),(4347,41,'string 5'),(4348,41,'string 5'),(4349,41,'string 5'),(4350,41,'string 5'),(4351,41,'string 5'),(4352,41,'string 5'),(4353,41,'string 5'),(4354,41,'string 5'),(4355,41,'string 5'),(4356,41,'string 5'),(4357,41,'string 5'),(4358,41,'string 5'),(4359,41,'string 5'),(4360,41,'string 5'),(4361,41,'string 5'),(4362,41,'string 5'),(4363,41,'string 5'),(4364,41,'string 5'),(4365,41,'string 5'),(4366,41,'string 5'),(4367,41,'string 5'),(4368,41,'string 5'),(4369,41,'string 5'),(4370,41,'string 5'),(4371,41,'string 5'),(4372,41,'string 5'),(4373,41,'string 5'),(4374,41,'string 5'),(4375,41,'string 5'),(4376,41,'string 5'),(4377,41,'string 5'),(4378,41,'string 5'),(4379,41,'string 5'),(4380,41,'string 5'),(4381,41,'string 5'),(4382,41,'string 5'),(4383,41,'string 5'),(4384,41,'string 5'),(4385,41,'string 5'),(4386,41,'string 5'),(4387,41,'string 5'),(4388,41,'string 5'),(4389,41,'string 5'),(4390,41,'string 5'),(4391,41,'string 5'),(4392,41,'string 5'),(4393,41,'string 5'),(4394,41,'string 5'),(4395,41,'string 5'),(4396,41,'string 5'),(4397,41,'string 5'),(4398,41,'string 5'),(4399,41,'string 5'),(4400,41,'string 5'),(4401,41,'string 5'),(4402,41,'string 5'),(4403,41,'string 5'),(4404,41,'string 5'),(4405,41,'string 5'),(4406,41,'string 5'),(4407,41,'string 5'),(4408,41,'string 5'),(4409,41,'string 5'),(4410,41,'string 5'),(4411,41,'string 5'),(4412,41,'string 5'),(4413,41,'string 5'),(4414,41,'string 5'),(4415,41,'string 5'),(4416,41,'string 5'),(4417,41,'string 5'),(4418,41,'string 5'),(4419,41,'string 5'),(4420,41,'string 5'),(4421,41,'string 5'),(4422,41,'string 5'),(4423,41,'string 5'),(4424,41,'string 5'),(4425,41,'string 5'),(4426,41,'string 5'),(4427,41,'string 5'),(4428,41,'string 5'),(4429,41,'string 5'),(4430,41,'string 5'),(4431,41,'string 5'),(4432,41,'string 5'),(4433,41,'string 5'),(4434,41,'string 5'),(4435,41,'string 5'),(4436,41,'string 5'),(4437,41,'string 5'),(4438,41,'string 5'),(4439,41,'string 5'),(4440,41,'string 5'),(4441,41,'string 5'),(4442,41,'string 5'),(4443,41,'string 5'),(4444,41,'string 5'),(4445,41,'string 5'),(4446,41,'string 5'),(4447,41,'string 5'),(4448,41,'string 5'),(4449,41,'string 5'),(4450,41,'string 5'),(4451,41,'string 5'),(4452,41,'string 5'),(4453,41,'string 5'),(4454,41,'string 5'),(4455,41,'string 5'),(4456,41,'string 5'),(4457,41,'string 5'),(4458,41,'string 5'),(4459,41,'string 5'),(4460,41,'string 5'),(4461,41,'string 5'),(4462,41,'string 5'),(4463,41,'string 5'),(4464,41,'string 5'),(4465,41,'string 5'),(4466,41,'string 5'),(4467,41,'string 5'),(4468,41,'string 5'),(4469,41,'string 5'),(4470,41,'string 5'),(4471,41,'string 5'),(4472,41,'string 5'),(4473,41,'string 5'),(4474,41,'string 5'),(4475,41,'string 5'),(4476,41,'string 5'),(4477,41,'string 5'),(4478,41,'string 5'),(4479,41,'string 5'),(4480,41,'string 5'),(4481,41,'string 5'),(4482,41,'string 5'),(4483,41,'string 5'),(4484,41,'string 5'),(4485,41,'string 5'),(4486,41,'string 5'),(4487,41,'string 5'),(4488,41,'string 5'),(4489,41,'string 5'),(4490,41,'string 5'),(4491,41,'string 5'),(4492,41,'string 5'),(4493,41,'string 5'),(4494,41,'string 5'),(4495,41,'string 5'),(4496,41,'string 5'),(4497,41,'string 5'),(4498,41,'string 5'),(4499,41,'string 5'),(4500,41,'string 5'),(4501,41,'string 5'),(4502,41,'string 5'),(4503,41,'string 5'),(4504,41,'string 5'),(4505,41,'string 5'),(4506,41,'string 5'),(4507,41,'string 5'),(4508,41,'string 5'),(4509,41,'string 5'),(4510,41,'string 5'),(4511,41,'string 5'),(4512,41,'string 5'),(4513,41,'string 5'),(4514,41,'string 5'),(4515,41,'string 5'),(4516,41,'string 5'),(4517,41,'string 5'),(4518,41,'string 5'),(4519,41,'string 5'),(4520,41,'string 5'),(4521,41,'string 5'),(4522,41,'string 5'),(4523,41,'string 5'),(4524,41,'string 5'),(4525,41,'string 5'),(4526,41,'string 5'),(4527,41,'string 5'),(4528,41,'string 5'),(4529,41,'string 5'),(4530,41,'string 5'),(4531,41,'string 5'),(4532,41,'string 5'),(4533,41,'string 5'),(4534,41,'string 5'),(4535,41,'string 5'),(4536,41,'string 5'),(4537,41,'string 5'),(4538,41,'string 5'),(4539,41,'string 5'),(4540,41,'string 5'),(4541,41,'string 5'),(4542,41,'string 5'),(4543,41,'string 5'),(4544,41,'string 5'),(4545,41,'string 5'),(4546,41,'string 5'),(4547,41,'string 5'),(4548,41,'string 5'),(4549,41,'string 5'),(4550,41,'string 5'),(4551,41,'string 5'),(4552,41,'string 5'),(4553,41,'string 5'),(4554,41,'string 5'),(4555,41,'string 5'),(4556,41,'string 5'),(4557,41,'string 5'),(4558,41,'string 5'),(4559,41,'string 5'),(4560,41,'string 5'),(4561,41,'string 5'),(4562,41,'string 5'),(4563,41,'string 5'),(4564,41,'string 5'),(4565,41,'string 5'),(4566,41,'string 5'),(4567,41,'string 5'),(4568,41,'string 5'),(4569,41,'string 5'),(4570,41,'string 5'),(4571,41,'string 5'),(4572,41,'string 5'),(4573,41,'string 5'),(4574,41,'string 5'),(4575,41,'string 5'),(4576,41,'string 5'),(4577,41,'string 5'),(4578,41,'string 5'),(4579,41,'string 5'),(4580,41,'string 5'),(4581,41,'string 5'),(4582,41,'string 5'),(4583,41,'string 5'),(4584,41,'string 5'),(4585,41,'string 5'),(4586,41,'string 5'),(4587,41,'string 5'),(4588,41,'string 5'),(4589,41,'string 5'),(4590,41,'string 5'),(4591,41,'string 5'),(4592,41,'string 5'),(4593,41,'string 5'),(4594,41,'string 5'),(4595,41,'string 5'),(4596,41,'string 5'),(4597,41,'string 5'),(4598,41,'string 5'),(4599,41,'string 5'),(4600,41,'string 5'),(4601,41,'string 5'),(4602,41,'string 5'),(4603,41,'string 5'),(4604,41,'string 5'),(4605,41,'string 5'),(4606,41,'string 5'),(4607,41,'string 5'),(4608,41,'string 5'),(4609,41,'string 5'),(4610,41,'string 5'),(4611,41,'string 5'),(4612,41,'string 5'),(4613,41,'string 5'),(4614,41,'string 5'),(4615,41,'string 5'),(4616,41,'string 5'),(4617,41,'string 5'),(4618,41,'string 5'),(4619,41,'string 5'),(4620,41,'string 5'),(4621,41,'string 5'),(4622,41,'string 5'),(4623,41,'string 5'),(4624,41,'string 5'),(4625,41,'string 5'),(4626,41,'string 5'),(4627,41,'string 5'),(4628,41,'string 5'),(4629,41,'string 5'),(4630,41,'string 5'),(4631,41,'string 5'),(4632,41,'string 5'),(4633,41,'string 5'),(4634,41,'string 5'),(4635,41,'string 5'),(4636,41,'string 5'),(4637,41,'string 5'),(4638,41,'string 5'),(4639,41,'string 5'),(4640,41,'string 5'),(4641,41,'string 5'),(4642,41,'string 5'),(4643,41,'string 5'),(4644,41,'string 5'),(4645,41,'string 5'),(4646,41,'string 5'),(4647,41,'string 5'),(4648,41,'string 5'),(4649,41,'string 5'),(4650,41,'string 5'),(4651,41,'string 5'),(4652,41,'string 5'),(4653,41,'string 5'),(4654,41,'string 5'),(4655,41,'string 5'),(4656,41,'string 5'),(4657,41,'string 5'),(4658,41,'string 5'),(4659,41,'string 5'),(4660,41,'string 5'),(4661,41,'string 5'),(4662,41,'string 5'),(4663,41,'string 5'),(4664,41,'string 5'),(4665,41,'string 5'),(4666,41,'string 5'),(4667,41,'string 5'),(4668,41,'string 5'),(4669,41,'string 5'),(4670,41,'string 5'),(4671,41,'string 5'),(4672,41,'string 5'),(4673,41,'string 5'),(4674,41,'string 5'),(4675,41,'string 5'),(4676,41,'string 5'),(4677,41,'string 5'),(4678,41,'string 5'),(4679,41,'string 5'),(4680,41,'string 5'),(4681,41,'string 5'),(4682,41,'string 5'),(4683,41,'string 5'),(4684,41,'string 5'),(4685,41,'string 5'),(4686,41,'string 5'),(4687,41,'string 5'),(4688,41,'string 5'),(4689,41,'string 5'),(4690,41,'string 5'),(4691,41,'string 5'),(4692,41,'string 5'),(4693,41,'string 5'),(4694,41,'string 5'),(4695,41,'string 5'),(4696,41,'string 5'),(4697,41,'string 5'),(4698,41,'string 5'),(4699,41,'string 5'),(4700,41,'string 5'),(4701,41,'string 5'),(4702,41,'string 5'),(4703,41,'string 5'),(4704,41,'string 5'),(4705,41,'string 5'),(4706,41,'string 5'),(4707,41,'string 5'),(4708,41,'string 5'),(4709,41,'string 5'),(4710,41,'string 5'),(4711,41,'string 5'),(4712,41,'string 5'),(4713,41,'string 5'),(4714,41,'string 5'),(4715,41,'string 5'),(4716,41,'string 5'),(4717,41,'string 5'),(4718,41,'string 5'),(4719,41,'string 5'),(4720,41,'string 5'),(4721,41,'string 5'),(4722,41,'string 5'),(4723,41,'string 5'),(4724,41,'string 5'),(4725,41,'string 5'),(4726,41,'string 5'),(4727,41,'string 5'),(4728,41,'string 5'),(4729,41,'string 5'),(4730,41,'string 5'),(4731,41,'string 5'),(4732,41,'string 5'),(4733,41,'string 5'),(4734,41,'string 5'),(4735,41,'string 5'),(4736,41,'string 5'),(4737,41,'string 5'),(4738,41,'string 5'),(4739,41,'string 5'),(4740,41,'string 5'),(4741,41,'string 5'),(4742,41,'string 5'),(4743,41,'string 5'),(4744,41,'string 5'),(4745,41,'string 5'),(4746,41,'string 5'),(4747,41,'string 5'),(4748,41,'string 5'),(4749,41,'string 5'),(4750,41,'string 5'),(4751,41,'string 5'),(4752,41,'string 5'),(4753,41,'string 5'),(4754,41,'string 5'),(4755,41,'string 5'),(4756,41,'string 5'),(4757,41,'string 5'),(4758,41,'string 5'),(4759,41,'string 5'),(4760,41,'string 5'),(4761,41,'string 5'),(4762,41,'string 5'),(4763,41,'string 5'),(4764,41,'string 5'),(4765,41,'string 5'),(4766,41,'string 5'),(4767,41,'string 5'),(4768,41,'string 5'),(4769,41,'string 5'),(4770,41,'string 5'),(4771,41,'string 5'),(4772,41,'string 5'),(4773,41,'string 5'),(4774,41,'string 5'),(4775,41,'string 5'),(4776,41,'string 5'),(4777,41,'string 5'),(4778,41,'string 5'),(4779,41,'string 5'),(4780,41,'string 5'),(4781,41,'string 5'),(4782,41,'string 5'),(4783,41,'string 5'),(4784,41,'string 5'),(4785,41,'string 5'),(4786,41,'string 5'),(4787,41,'string 5'),(4788,41,'string 5'),(4789,41,'string 5'),(4790,41,'string 5'),(4791,41,'string 5'),(4792,41,'string 5'),(4793,41,'string 5'),(4794,41,'string 5'),(4795,41,'string 5'),(4796,41,'string 5'),(4797,41,'string 5'),(4798,41,'string 5'),(4799,41,'string 5'),(4800,41,'string 5'),(4801,41,'string 5'),(4802,41,'string 5'),(4803,41,'string 5'),(4804,41,'string 5'),(4805,41,'string 5'),(4806,41,'string 5'),(4807,41,'string 5'),(4808,41,'string 5'),(4809,41,'string 5'),(4810,41,'string 5'),(4811,41,'string 5'),(4812,41,'string 5'),(4813,41,'string 5'),(4814,41,'string 5'),(4815,41,'string 5'),(4816,41,'string 5'),(4817,41,'string 5'),(4818,41,'string 5'),(4819,41,'string 5'),(4820,41,'string 5'),(4821,41,'string 5'),(4822,41,'string 5'),(4823,41,'string 5'),(4824,41,'string 5'),(4825,41,'string 5'),(4826,41,'string 5'),(4827,41,'string 5'),(4828,41,'string 5'),(4829,41,'string 5'),(4830,41,'string 5'),(4831,41,'string 5'),(4832,41,'string 5'),(4833,41,'string 5'),(4834,41,'string 5'),(4835,41,'string 5'),(4836,41,'string 5'),(4837,41,'string 5'),(4838,41,'string 5'),(4839,41,'string 5'),(4840,41,'string 5'),(4841,41,'string 5'),(4842,41,'string 5'),(4843,41,'string 5'),(4844,41,'string 5'),(4845,41,'string 5'),(4846,41,'string 5'),(4847,41,'string 5'),(4848,41,'string 5'),(4849,41,'string 5'),(4850,41,'string 5'),(4851,41,'string 5'),(4852,41,'string 5'),(4853,41,'string 5'),(4854,41,'string 5'),(4855,41,'string 5'),(4856,41,'string 5'),(4857,41,'string 5'),(4858,41,'string 5'),(4859,41,'string 5'),(4860,41,'string 5'),(4861,41,'string 5'),(4862,41,'string 5'),(4863,41,'string 5'),(4864,41,'string 5'),(4865,41,'string 5'),(4866,41,'string 5'),(4867,41,'string 5'),(4868,41,'string 5'),(4869,41,'string 5'),(4870,41,'string 5'),(4871,41,'string 5'),(4872,41,'string 5'),(4873,41,'string 5'),(4874,41,'string 5'),(4875,41,'string 5'),(4876,41,'string 5'),(4877,41,'string 5'),(4878,41,'string 5'),(4879,41,'string 5'),(4880,41,'string 5'),(4881,41,'string 5'),(4882,41,'string 5'),(4883,41,'string 5'),(4884,41,'string 5'),(4885,41,'string 5'),(4886,41,'string 5'),(4887,41,'string 5'),(4888,41,'string 5'),(4889,41,'string 5'),(4890,41,'string 5'),(4891,41,'string 5'),(4892,41,'string 5'),(4893,41,'string 5'),(4894,41,'string 5'),(4895,41,'string 5'),(4896,41,'string 5'),(4897,41,'string 5'),(4898,41,'string 5'),(4899,41,'string 5'),(4900,41,'string 5'),(4901,41,'string 5'),(4902,41,'string 5'),(4903,41,'string 5'),(4904,41,'string 5'),(4905,41,'string 5'),(4906,41,'string 5'),(4907,41,'string 5'),(4908,41,'string 5'),(4909,41,'string 5'),(4910,41,'string 5'),(4911,41,'string 5'),(4912,41,'string 5'),(4913,41,'string 5'),(4914,41,'string 5'),(4915,41,'string 5'),(4916,41,'string 5'),(4917,41,'string 5'),(4918,41,'string 5'),(4919,41,'string 5'),(4920,41,'string 5'),(4921,41,'string 5'),(4922,41,'string 5'),(4923,41,'string 5'),(4924,41,'string 5'),(4925,41,'string 5'),(4926,41,'string 5'),(4927,41,'string 5'),(4928,41,'string 5'),(4929,41,'string 5'),(4930,41,'string 5'),(4931,41,'string 5'),(4932,41,'string 5'),(4933,41,'string 5'),(4934,41,'string 5'),(4935,41,'string 5'),(4936,41,'string 5'),(4937,41,'string 5'),(4938,41,'string 5'),(4939,41,'string 5'),(4940,41,'string 5'),(4941,41,'string 5'),(4942,41,'string 5'),(4943,41,'string 5'),(4944,41,'string 5'),(4945,41,'string 5'),(4946,41,'string 5'),(4947,41,'string 5'),(4948,41,'string 5'),(4949,41,'string 5'),(4950,41,'string 5'),(4951,41,'string 5'),(4952,41,'string 5'),(4953,41,'string 5'),(4954,41,'string 5'),(4955,41,'string 5'),(4956,41,'string 5'),(4957,41,'string 5'),(4958,41,'string 5'),(4959,41,'string 5'),(4960,41,'string 5'),(4961,41,'string 5'),(4962,41,'string 5'),(4963,41,'string 5'),(4964,41,'string 5'),(4965,41,'string 5'),(4966,41,'string 5'),(4967,41,'string 5'),(4968,41,'string 5'),(4969,41,'string 5'),(4970,41,'string 5'),(4971,41,'string 5'),(4972,41,'string 5'),(4973,41,'string 5'),(4974,41,'string 5'),(4975,41,'string 5'),(4976,41,'string 5'),(4977,41,'string 5'),(4978,41,'string 5'),(4979,41,'string 5'),(4980,41,'string 5'),(4981,41,'string 5'),(4982,41,'string 5'),(4983,41,'string 5'),(4984,41,'string 5'),(4985,41,'string 5'),(4986,41,'string 5'),(4987,41,'string 5'),(4988,41,'string 5'),(4989,41,'string 5'),(4990,41,'string 5'),(4991,41,'string 5'),(4992,41,'string 5'),(4993,41,'string 5'),(4994,41,'string 5'),(4995,41,'string 5'),(4996,41,'string 5'),(4997,41,'string 5'),(4998,41,'string 5'),(4999,41,'string 5'),(5000,41,'string 5'),(5001,41,'string 5'),(5002,41,'string 5'),(5003,41,'string 5'),(5004,41,'string 5'),(5005,41,'string 5'),(5006,41,'string 5'),(5007,41,'string 5'),(5008,41,'string 5'),(5009,41,'string 5'),(5010,41,'string 5'),(5011,41,'string 5'),(5012,41,'string 5'),(5013,41,'string 5'),(5014,41,'string 5'),(5015,41,'string 5'),(5016,41,'string 5'),(5017,41,'string 5'),(5018,41,'string 5'),(5019,41,'string 5'),(5020,41,'string 5'),(5021,41,'string 5'),(5022,41,'string 5'),(5023,41,'string 5'),(5024,41,'string 5'),(5025,41,'string 5'),(5026,41,'string 5'),(5027,41,'string 5'),(5028,41,'string 5'),(5029,41,'string 5'),(5030,41,'string 5'),(5031,41,'string 5'),(5032,41,'string 5'),(5033,41,'string 5'),(5034,41,'string 5'),(5035,41,'string 5'),(5036,41,'string 5'),(5037,41,'string 5'),(5038,41,'string 5'),(5039,41,'string 5'),(5040,41,'string 5'),(5041,41,'string 5'),(5042,41,'string 5'),(5043,41,'string 5'),(5044,41,'string 5'),(5045,41,'string 5'),(5046,41,'string 5'),(5047,41,'string 5'),(5048,41,'string 5'),(5049,41,'string 5'),(5050,41,'string 5'),(5051,41,'string 5'),(5052,41,'string 5'),(5053,41,'string 5'),(5054,41,'string 5'),(5055,41,'string 5'),(5056,41,'string 5'),(5057,41,'string 5'),(5058,41,'string 5'),(5059,41,'string 5'),(5060,41,'string 5'),(5061,41,'string 5'),(5062,41,'string 5'),(5063,41,'string 5'),(5064,41,'string 5'),(5065,41,'string 5'),(5066,41,'string 5'),(5067,41,'string 5'),(5068,41,'string 5'),(5069,41,'string 5'),(5070,41,'string 5'),(5071,41,'string 5'),(5072,41,'string 5'),(5073,41,'string 5'),(5074,41,'string 5'),(5075,41,'string 5'),(5076,41,'string 5'),(5077,41,'string 5'),(5078,41,'string 5'),(5079,41,'string 5'),(5080,41,'string 5'),(5081,41,'string 5'),(5082,41,'string 5'),(5083,41,'string 5'),(5084,41,'string 5'),(5085,41,'string 5'),(5086,41,'string 5'),(5087,41,'string 5'),(5088,41,'string 5'),(5089,41,'string 5'),(5090,41,'string 5'),(5091,41,'string 5'),(5092,41,'string 5'),(5093,41,'string 5'),(5094,41,'string 5'),(5095,41,'string 5'),(5096,41,'string 5'),(5097,41,'string 5'),(5098,41,'string 5'),(5099,41,'string 5'),(5100,41,'string 5'),(5101,41,'string 5'),(5102,41,'string 5'),(5103,41,'string 5'),(5104,41,'string 5'),(5105,41,'string 5'),(5106,41,'string 5'),(5107,41,'string 5'),(5108,41,'string 5'),(5109,41,'string 5'),(5110,41,'string 5'),(5111,41,'string 5'),(5112,41,'string 5'),(5113,41,'string 5'),(5114,41,'string 5'),(5115,41,'string 5'),(5116,41,'string 5'),(5117,41,'string 5'),(5118,41,'string 5'),(5119,41,'string 5'),(5120,41,'string 5'),(5121,41,'string 5'),(5122,41,'string 5'),(5123,41,'string 5'),(5124,41,'string 5'),(5125,41,'string 5'),(5126,41,'string 5'),(5127,41,'string 5'),(5128,41,'string 5'),(5129,41,'string 5'),(5130,41,'string 5'),(5131,41,'string 5'),(5132,41,'string 5'),(5133,41,'string 5'),(5134,41,'string 5'),(5135,41,'string 5'),(5136,41,'string 5'),(5137,41,'string 5'),(5138,41,'string 5'),(5139,41,'string 5'),(5140,41,'string 5'),(5141,41,'string 5'),(5142,41,'string 5'),(5143,41,'string 5'),(5144,41,'string 5'),(5145,41,'string 5'),(5146,41,'string 5'),(5147,41,'string 5'),(5148,41,'string 5'),(5149,41,'string 5'),(5150,41,'string 5'),(5151,41,'string 5'),(5152,41,'string 5'),(5153,41,'string 5'),(5154,41,'string 5'),(5155,41,'string 5'),(5156,41,'string 5'),(5157,41,'string 5'),(5158,41,'string 5'),(5159,41,'string 5'),(5160,41,'string 5'),(5161,41,'string 5'),(5162,41,'string 5'),(5163,41,'string 5'),(5164,41,'string 5'),(5165,41,'string 5'),(5166,41,'string 5'),(5167,41,'string 5'),(5168,41,'string 5'),(5169,41,'string 5'),(5170,41,'string 5'),(5171,41,'string 5'),(5172,41,'string 5'),(5173,41,'string 5'),(5174,41,'string 5'),(5175,41,'string 5'),(5176,41,'string 5'),(5177,41,'string 5'),(5178,41,'string 5'),(5179,41,'string 5'),(5180,41,'string 5'),(5181,41,'string 5'),(5182,41,'string 5'),(5183,41,'string 5'),(5184,41,'string 5'),(5185,41,'string 5'),(5186,41,'string 5'),(5187,41,'string 5'),(5188,41,'string 5'),(5189,41,'string 5'),(5190,41,'string 5'),(5191,41,'string 5'),(5192,41,'string 5'),(5193,41,'string 5'),(5194,41,'string 5'),(5195,41,'string 5'),(5196,41,'string 5'),(5197,41,'string 5'),(5198,41,'string 5'),(5199,41,'string 5'),(5200,41,'string 5'),(5201,41,'string 5'),(5202,41,'string 5'),(5203,41,'string 5'),(5204,41,'string 5'),(5205,41,'string 5'),(5206,41,'string 5'),(5207,41,'string 5'),(5208,41,'string 5'),(5209,41,'string 5'),(5210,41,'string 5'),(5211,41,'string 5'),(5212,41,'string 5'),(5213,41,'string 5'),(5214,41,'string 5'),(5215,41,'string 5'),(5216,41,'string 5'),(5217,41,'string 5'),(5218,41,'string 5'),(5219,41,'string 5'),(5220,41,'string 5'),(5221,41,'string 5'),(5222,41,'string 5'),(5223,41,'string 5'),(5224,41,'string 5'),(5225,41,'string 5'),(5226,41,'string 5'),(5227,41,'string 5'),(5228,41,'string 5'),(5229,41,'string 5'),(5230,41,'string 5'),(5231,41,'string 5'),(5232,41,'string 5'),(5233,41,'string 5'),(5234,41,'string 5'),(5235,41,'string 5'),(5236,41,'string 5'),(5237,41,'string 5'),(5238,41,'string 5'),(5239,41,'string 5'),(5240,41,'string 5'),(5241,41,'string 5'),(5242,41,'string 5'),(5243,41,'string 5'),(5244,41,'string 5'),(5245,41,'string 5'),(5246,41,'string 5'),(5247,41,'string 5'),(5248,41,'string 5'),(5249,41,'string 5'),(5250,41,'string 5'),(5251,41,'string 5'),(5252,41,'string 5'),(5253,41,'string 5'),(5254,41,'string 5'),(5255,41,'string 5'),(5256,41,'string 5'),(5257,41,'string 5'),(5258,41,'string 5'),(5259,41,'string 5'),(5260,41,'string 5'),(5261,41,'string 5'),(5262,41,'string 5'),(5263,41,'string 5'),(5264,41,'string 5'),(5265,41,'string 5'),(5266,41,'string 5'),(5267,41,'string 5'),(5268,41,'string 5'),(5269,41,'string 5'),(5270,41,'string 5'),(5271,41,'string 5'),(5272,41,'string 5'),(5273,41,'string 5'),(5274,41,'string 5'),(5275,41,'string 5'),(5276,41,'string 5'),(5277,41,'string 5'),(5278,41,'string 5'),(5279,41,'string 5'),(5280,41,'string 5'),(5281,41,'string 5'),(5282,41,'string 5'),(5283,41,'string 5'),(5284,41,'string 5'),(5285,41,'string 5'),(5286,41,'string 5'),(5287,41,'string 5'),(5288,41,'string 5'),(5289,41,'string 5'),(5290,41,'string 5'),(5291,41,'string 5'),(5292,41,'string 5'),(5293,41,'string 5'),(5294,41,'string 5'),(5295,41,'string 5'),(5296,41,'string 5'),(5297,41,'string 5'),(5298,41,'string 5'),(5299,41,'string 5'),(5300,41,'string 5'),(5301,41,'string 5'),(5302,41,'string 5'),(5303,41,'string 5'),(5304,41,'string 5'),(5305,41,'string 5'),(5306,41,'string 5'),(5307,41,'string 5'),(5308,41,'string 5'),(5309,41,'string 5'),(5310,41,'string 5'),(5311,41,'string 5'),(5312,41,'string 5'),(5313,41,'string 5'),(5314,41,'string 5'),(5315,41,'string 5'),(5316,41,'string 5'),(5317,41,'string 5'),(5318,41,'string 5'),(5319,41,'string 5'),(5320,41,'string 5'),(5321,41,'string 5'),(5322,41,'string 5'),(5323,41,'string 5'),(5324,41,'string 5'),(5325,41,'string 5'),(5326,41,'string 5'),(5327,41,'string 5'),(5328,41,'string 5'),(5329,41,'string 5'),(5330,41,'string 5'),(5331,41,'string 5'),(5332,41,'string 5'),(5333,41,'string 5'),(5334,41,'string 5'),(5335,41,'string 5'),(5336,41,'string 5'),(5337,41,'string 5'),(5338,41,'string 5'),(5339,41,'string 5'),(5340,41,'string 5'),(5341,41,'string 5'),(5342,41,'string 5'),(5343,41,'string 5'),(5344,41,'string 5'),(5345,41,'string 5'),(5346,41,'string 5'),(5347,41,'string 5'),(5348,41,'string 5'),(5349,41,'string 5'),(5350,41,'string 5'),(5351,41,'string 5'),(5352,41,'string 5'),(5353,41,'string 5'),(5354,41,'string 5'),(5355,41,'string 5'),(5356,41,'string 5'),(5357,41,'string 5'),(5358,41,'string 5'),(5359,41,'string 5'),(5360,41,'string 5'),(5361,41,'string 5'),(5362,41,'string 5'),(5363,41,'string 5'),(5364,41,'string 5'),(5365,41,'string 5'),(5366,41,'string 5'),(5367,41,'string 5'),(5368,41,'string 5'),(5369,41,'string 5'),(5370,41,'string 5'),(5371,41,'string 5'),(5372,41,'string 5'),(5373,41,'string 5'),(5374,41,'string 5'),(5375,41,'string 5'),(5376,41,'string 5'),(5377,41,'string 5'),(5378,41,'string 5'),(5379,41,'string 5'),(5380,41,'string 5'),(5381,41,'string 5'),(5382,41,'string 5'),(5383,41,'string 5'),(5384,41,'string 5'),(5385,41,'string 5'),(5386,41,'string 5'),(5387,41,'string 5'),(5388,41,'string 5'),(5389,41,'string 5'),(5390,41,'string 5'),(5391,41,'string 5'),(5392,41,'string 5'),(5393,41,'string 5'),(5394,41,'string 5'),(5395,41,'string 5'),(5396,41,'string 5'),(5397,41,'string 5'),(5398,41,'string 5'),(5399,41,'string 5'),(5400,41,'string 5'),(5401,41,'string 5'),(5402,41,'string 5'),(5403,41,'string 5'),(5404,41,'string 5'),(5405,41,'string 5'),(5406,41,'string 5'),(5407,41,'string 5'),(5408,41,'string 5'),(5409,41,'string 5'),(5410,41,'string 5'),(5411,41,'string 5'),(5412,41,'string 5'),(5413,41,'string 5'),(5414,41,'string 5'),(5415,41,'string 5'),(5416,41,'string 5'),(5417,41,'string 5'),(5418,41,'string 5'),(5419,41,'string 5'),(5420,41,'string 5'),(5421,41,'string 5'),(5422,41,'string 5'),(5423,41,'string 5'),(5424,41,'string 5'),(5425,41,'string 5'),(5426,41,'string 5'),(5427,41,'string 5'),(5428,41,'string 5'),(5429,41,'string 5'),(5430,41,'string 5'),(5431,41,'string 5'),(5432,41,'string 5'),(5433,41,'string 5'),(5434,41,'string 5'),(5435,41,'string 5'),(5436,41,'string 5'),(5437,41,'string 5'),(5438,41,'string 5'),(5439,41,'string 5'),(5440,41,'string 5'),(5441,41,'string 5'),(5442,41,'string 5'),(5443,41,'string 5'),(5444,41,'string 5'),(5445,41,'string 5'),(5446,41,'string 5'),(5447,41,'string 5'),(5448,41,'string 5'),(5449,41,'string 5'),(5450,41,'string 5'),(5451,41,'string 5'),(5452,41,'string 5'),(5453,41,'string 5'),(5454,41,'string 5'),(5455,41,'string 5'),(5456,41,'string 5'),(5457,41,'string 5'),(5458,41,'string 5'),(5459,41,'string 5'),(5460,41,'string 5'),(5461,41,'string 5'),(5462,41,'string 5'),(5463,41,'string 5'),(5464,41,'string 5'),(5465,41,'string 5'),(5466,41,'string 5'),(5467,41,'string 5'),(5468,41,'string 5'),(5469,41,'string 5'),(5470,41,'string 5'),(5471,41,'string 5'),(5472,41,'string 5'),(5473,41,'string 5'),(5474,41,'string 5'),(5475,41,'string 5'),(5476,41,'string 5'),(5477,41,'string 5'),(5478,41,'string 5'),(5479,41,'string 5'),(5480,41,'string 5'),(5481,41,'string 5'),(5482,41,'string 5'),(5483,41,'string 5'),(5484,41,'string 5'),(5485,41,'string 5'),(5486,41,'string 5'),(5487,41,'string 5'),(5488,41,'string 5'),(5489,41,'string 5'),(5490,41,'string 5'),(5491,41,'string 5'),(5492,41,'string 5'),(5493,41,'string 5'),(5494,41,'string 5'),(5495,41,'string 5'),(5496,41,'string 5'),(5497,41,'string 5'),(5498,41,'string 5'),(5499,41,'string 5'),(5500,41,'string 5'),(5501,41,'string 5'),(5502,41,'string 5'),(5503,41,'string 5'),(5504,41,'string 5'),(5505,41,'string 5'),(5506,41,'string 5'),(5507,41,'string 5'),(5508,41,'string 5'),(5509,41,'string 5'),(5510,41,'string 5'),(5511,41,'string 5'),(5512,41,'string 5'),(5513,41,'string 5'),(5514,41,'string 5'),(5515,41,'string 5'),(5516,41,'string 5'),(5517,41,'string 5'),(5518,41,'string 5'),(5519,41,'string 5'),(5520,41,'string 5'),(5521,41,'string 5'),(5522,41,'string 5'),(5523,41,'string 5'),(5524,41,'string 5'),(5525,41,'string 5'),(5526,41,'string 5'),(5527,41,'string 5'),(5528,41,'string 5'),(5529,41,'string 5'),(5530,41,'string 5'),(5531,41,'string 5'),(5532,41,'string 5'),(5533,41,'string 5'),(5534,41,'string 5'),(5535,41,'string 5'),(5536,41,'string 5'),(5537,41,'string 5'),(5538,41,'string 5'),(5539,41,'string 5'),(5540,41,'string 5'),(5541,41,'string 5'),(5542,41,'string 5'),(5543,41,'string 5'),(5544,41,'string 5'),(5545,41,'string 5'),(5546,41,'string 5'),(5547,41,'string 5'),(5548,41,'string 5'),(5549,41,'string 5'),(5550,41,'string 5'),(5551,41,'string 5'),(5552,41,'string 5'),(5553,41,'string 5'),(5554,41,'string 5'),(5555,41,'string 5'),(5556,41,'string 5'),(5557,41,'string 5'),(5558,41,'string 5'),(5559,41,'string 5'),(5560,41,'string 5'),(5561,41,'string 5'),(5562,41,'string 5'),(5563,41,'string 5'),(5564,41,'string 5'),(5565,41,'string 5'),(5566,41,'string 5'),(5567,41,'string 5'),(5568,41,'string 5'),(5569,41,'string 5'),(5570,41,'string 5'),(5571,41,'string 5'),(5572,41,'string 5'),(5573,41,'string 5'),(5574,41,'string 5'),(5575,41,'string 5'),(5576,41,'string 5'),(5577,41,'string 5'),(5578,41,'string 5'),(5579,41,'string 5'),(5580,41,'string 5'),(5581,41,'string 5'),(5582,41,'string 5'),(5583,41,'string 5'),(5584,41,'string 5'),(5585,41,'string 5'),(5586,41,'string 5'),(5587,41,'string 5'),(5588,41,'string 5'),(5589,41,'string 5'),(5590,41,'string 5'),(5591,41,'string 5'),(5592,41,'string 5'),(5593,41,'string 5'),(5594,41,'string 5'),(5595,41,'string 5'),(5596,41,'string 5'),(5597,41,'string 5'),(5598,41,'string 5'),(5599,41,'string 5'),(5600,41,'string 5'),(5601,41,'string 5'),(5602,41,'string 5'),(5603,41,'string 5'),(5604,41,'string 5'),(5605,41,'string 5'),(5606,41,'string 5'),(5607,41,'string 5'),(5608,41,'string 5'),(5609,41,'string 5'),(5610,41,'string 5'),(5611,41,'string 5'),(5612,41,'string 5'),(5613,41,'string 5'),(5614,41,'string 5'),(5615,41,'string 5'),(5616,41,'string 5'),(5617,41,'string 5'),(5618,41,'string 5'),(5619,41,'string 5'),(5620,41,'string 5'),(5621,41,'string 5'),(5622,41,'string 5'),(5623,41,'string 5'),(5624,41,'string 5'),(5625,41,'string 5'),(5626,41,'string 5'),(5627,41,'string 5'),(5628,41,'string 5'),(5629,41,'string 5'),(5630,41,'string 5'),(5631,41,'string 5'),(5632,41,'string 5'),(5633,41,'string 5'),(5634,41,'string 5'),(5635,41,'string 5'),(5636,41,'string 5'),(5637,41,'string 5'),(5638,41,'string 5'),(5639,41,'string 5'),(5640,41,'string 5'),(5641,41,'string 5'),(5642,41,'string 5'),(5643,41,'string 5'),(5644,41,'string 5'),(5645,41,'string 5'),(5646,41,'string 5'),(5647,41,'string 5'),(5648,41,'string 5'),(5649,41,'string 5'),(5650,41,'string 5'),(5651,41,'string 5'),(5652,41,'string 5'),(5653,41,'string 5'),(5654,41,'string 5'),(5655,41,'string 5'),(5656,41,'string 5'),(5657,41,'string 5'),(5658,41,'string 5'),(5659,41,'string 5'),(5660,41,'string 5'),(5661,41,'string 5'),(5662,41,'string 5'),(5663,41,'string 5'),(5664,41,'string 5'),(5665,41,'string 5'),(5666,41,'string 5'),(5667,41,'string 5'),(5668,41,'string 5'),(5669,41,'string 5'),(5670,41,'string 5'),(5671,41,'string 5'),(5672,41,'string 5'),(5673,41,'string 5'),(5674,41,'string 5'),(5675,41,'string 5'),(5676,41,'string 5'),(5677,41,'string 5'),(5678,41,'string 5'),(5679,41,'string 5'),(5680,41,'string 5'),(5681,41,'string 5'),(5682,41,'string 5'),(5683,41,'string 5'),(5684,41,'string 5'),(5685,41,'string 5'),(5686,41,'string 5'),(5687,41,'string 5'),(5688,41,'string 5'),(5689,41,'string 5'),(5690,41,'string 5'),(5691,41,'string 5'),(5692,41,'string 5'),(5693,41,'string 5'),(5694,41,'string 5'),(5695,41,'string 5'),(5696,41,'string 5'),(5697,41,'string 5'),(5698,41,'string 5'),(5699,41,'string 5'),(5700,41,'string 5'),(5701,41,'string 5'),(5702,41,'string 5'),(5703,41,'string 5'),(5704,41,'string 5'),(5705,41,'string 5'),(5706,41,'string 5'),(5707,41,'string 5'),(5708,41,'string 5'),(5709,41,'string 5'),(5710,41,'string 5'),(5711,41,'string 5'),(5712,41,'string 5'),(5713,41,'string 5'),(5714,41,'string 5'),(5715,41,'string 5'),(5716,41,'string 5'),(5717,41,'string 5'),(5718,41,'string 5'),(5719,41,'string 5'),(5720,41,'string 5'),(5721,41,'string 5'),(5722,41,'string 5'),(5723,41,'string 5'),(5724,41,'string 5'),(5725,41,'string 5'),(5726,41,'string 5'),(5727,41,'string 5'),(5728,41,'string 5'),(5729,41,'string 5'),(5730,41,'string 5'),(5731,41,'string 5'),(5732,41,'string 5'),(5733,41,'string 5'),(5734,41,'string 5'),(5735,41,'string 5'),(5736,41,'string 5'),(5737,41,'string 5'),(5738,41,'string 5'),(5739,41,'string 5'),(5740,41,'string 5'),(5741,41,'string 5'),(5742,41,'string 5'),(5743,41,'string 5'),(5744,41,'string 5'),(5745,41,'string 5'),(5746,41,'string 5'),(5747,41,'string 5'),(5748,41,'string 5'),(5749,41,'string 5'),(5750,41,'string 5'),(5751,41,'string 5'),(5752,41,'string 5'),(5753,41,'string 5'),(5754,41,'string 5'),(5755,41,'string 5'),(5756,41,'string 5'),(5757,41,'string 5'),(5758,41,'string 5'),(5759,41,'string 5'),(5760,41,'string 5'),(5761,41,'string 5'),(5762,41,'string 5'),(5763,41,'string 5'),(5764,41,'string 5'),(5765,41,'string 5'),(5766,41,'string 5'),(5767,41,'string 5'),(5768,41,'string 5'),(5769,41,'string 5'),(5770,41,'string 5'),(5771,41,'string 5'),(5772,41,'string 5'),(5773,41,'string 5'),(5774,41,'string 5'),(5775,41,'string 5'),(5776,41,'string 5'),(5777,41,'string 5'),(5778,41,'string 5'),(5779,41,'string 5'),(5780,41,'string 5'),(5781,41,'string 5'),(5782,41,'string 5'),(5783,41,'string 5'),(5784,41,'string 5'),(5785,41,'string 5'),(5786,41,'string 5'),(5787,41,'string 5'),(5788,41,'string 5'),(5789,41,'string 5'),(5790,41,'string 5'),(5791,41,'string 5'),(5792,41,'string 5'),(5793,41,'string 5'),(5794,41,'string 5'),(5795,41,'string 5'),(5796,41,'string 5'),(5797,41,'string 5'),(5798,41,'string 5'),(5799,41,'string 5'),(5800,41,'string 5'),(5801,41,'string 5'),(5802,41,'string 5'),(5803,41,'string 5'),(5804,41,'string 5'),(5805,41,'string 5'),(5806,41,'string 5'),(5807,41,'string 5'),(5808,41,'string 5'),(5809,41,'string 5'),(5810,41,'string 5'),(5811,41,'string 5'),(5812,41,'string 5'),(5813,41,'string 5'),(5814,41,'string 5'),(5815,41,'string 5'),(5816,41,'string 5'),(5817,41,'string 5'),(5818,41,'string 5'),(5819,41,'string 5'),(5820,41,'string 5'),(5821,41,'string 5'),(5822,41,'string 5'),(5823,41,'string 5'),(5824,41,'string 5'),(5825,41,'string 5'),(5826,41,'string 5'),(5827,41,'string 5'),(5828,41,'string 5'),(5829,41,'string 5'),(5830,41,'string 5'),(5831,41,'string 5'),(5832,41,'string 5'),(5833,41,'string 5'),(5834,41,'string 5'),(5835,41,'string 5'),(5836,41,'string 5'),(5837,41,'string 5'),(5838,41,'string 5'),(5839,41,'string 5'),(5840,41,'string 5'),(5841,41,'string 5'),(5842,41,'string 5'),(5843,41,'string 5'),(5844,41,'string 5'),(5845,41,'string 5'),(5846,41,'string 5'),(5847,41,'string 5'),(5848,41,'string 5'),(5849,41,'string 5'),(5850,41,'string 5'),(5851,41,'string 5'),(5852,41,'string 5'),(5853,41,'string 5'),(5854,41,'string 5'),(5855,41,'string 5'),(5856,41,'string 5'),(5857,41,'string 5'),(5858,41,'string 5'),(5859,41,'string 5'),(5860,41,'string 5'),(5861,41,'string 5'),(5862,41,'string 5'),(5863,41,'string 5'),(5864,41,'string 5'),(5865,41,'string 5'),(5866,41,'string 5'),(5867,41,'string 5'),(5868,41,'string 5'),(5869,41,'string 5'),(5870,41,'string 5'),(5871,41,'string 5'),(5872,41,'string 5'),(5873,41,'string 5'),(5874,41,'string 5'),(5875,41,'string 5'),(5876,41,'string 5'),(5877,41,'string 5'),(5878,41,'string 5'),(5879,41,'string 5'),(5880,41,'string 5'),(5881,41,'string 5'),(5882,41,'string 5'),(5883,41,'string 5'),(5884,41,'string 5'),(5885,41,'string 5'),(5886,41,'string 5'),(5887,41,'string 5'),(5888,41,'string 5'),(5889,41,'string 5'),(5890,41,'string 5'),(5891,41,'string 5'),(5892,41,'string 5'),(5893,41,'string 5'),(5894,41,'string 5'),(5895,41,'string 5'),(5896,41,'string 5'),(5897,41,'string 5'),(5898,41,'string 5'),(5899,41,'string 5'),(5900,41,'string 5'),(5901,41,'string 5'),(5902,41,'string 5'),(5903,41,'string 5'),(5904,41,'string 5'),(5905,41,'string 5'),(5906,41,'string 5'),(5907,41,'string 5'),(5908,41,'string 5'),(5909,41,'string 5'),(5910,41,'string 5'),(5911,41,'string 5'),(5912,41,'string 5'),(5913,41,'string 5'),(5914,41,'string 5'),(5915,41,'string 5'),(5916,41,'string 5'),(5917,41,'string 5'),(5918,41,'string 5'),(5919,41,'string 5'),(5920,41,'string 5'),(5921,41,'string 5'),(5922,41,'string 5'),(5923,41,'string 5'),(5924,41,'string 5'),(5925,41,'string 5'),(5926,41,'string 5'),(5927,41,'string 5'),(5928,41,'string 5'),(5929,41,'string 5'),(5930,41,'string 5'),(5931,41,'string 5'),(5932,41,'string 5'),(5933,41,'string 5'),(5934,41,'string 5'),(5935,41,'string 5'),(5936,41,'string 5'),(5937,41,'string 5'),(5938,41,'string 5'),(5939,41,'string 5'),(5940,41,'string 5'),(5941,41,'string 5'),(5942,41,'string 5'),(5943,41,'string 5'),(5944,41,'string 5'),(5945,41,'string 5'),(5946,41,'string 5'),(5947,41,'string 5'),(5948,41,'string 5'),(5949,41,'string 5'),(5950,41,'string 5'),(5951,41,'string 5'),(5952,41,'string 5'),(5953,41,'string 5'),(5954,41,'string 5'),(5955,41,'string 5'),(5956,41,'string 5'),(5957,41,'string 5'),(5958,41,'string 5'),(5959,41,'string 5'),(5960,41,'string 5'),(5961,41,'string 5'),(5962,41,'string 5'),(5963,41,'string 5'),(5964,41,'string 5'),(5965,41,'string 5'),(5966,41,'string 5'),(5967,41,'string 5'),(5968,41,'string 5'),(5969,41,'string 5'),(5970,41,'string 5'),(5971,41,'string 5'),(5972,41,'string 5'),(5973,41,'string 5'),(5974,41,'string 5'),(5975,41,'string 5'),(5976,41,'string 5'),(5977,41,'string 5'),(5978,41,'string 5'),(5979,41,'string 5'),(5980,41,'string 5'),(5981,41,'string 5'),(5982,41,'string 5'),(5983,41,'string 5'),(5984,41,'string 5'),(5985,41,'string 5'),(5986,41,'string 5'),(5987,41,'string 5'),(5988,41,'string 5'),(5989,41,'string 5'),(5990,41,'string 5'),(5991,41,'string 5'),(5992,41,'string 5'),(5993,41,'string 5'),(5994,41,'string 5'),(5995,41,'string 5'),(5996,41,'string 5'),(5997,41,'string 5'),(5998,41,'string 5'),(5999,41,'string 5'),(6000,41,'string 5'),(6001,41,'string 5'),(6002,41,'string 5'),(6003,41,'string 5'),(6004,41,'string 5'),(6005,41,'string 5'),(6006,41,'string 5'),(6007,41,'string 5'),(6008,41,'string 5'),(6009,41,'string 5'),(6010,41,'string 5'),(6011,41,'string 5'),(6012,41,'string 5'),(6013,41,'string 5'),(6014,41,'string 5'),(6015,41,'string 5'),(6016,41,'string 5'),(6017,41,'string 5'),(6018,41,'string 5'),(6019,41,'string 5'),(6020,41,'string 5'),(6021,41,'string 5'),(6022,41,'string 5'),(6023,41,'string 5'),(6024,41,'string 5'),(6025,41,'string 5'),(6026,41,'string 5'),(6027,41,'string 5'),(6028,41,'string 5'),(6029,41,'string 5'),(6030,41,'string 5'),(6031,41,'string 5'),(6032,41,'string 5'),(6033,41,'string 5'),(6034,41,'string 5'),(6035,41,'string 5'),(6036,41,'string 5'),(6037,41,'string 5'),(6038,41,'string 5'),(6039,41,'string 5'),(6040,41,'string 5'),(6041,41,'string 5'),(6042,41,'string 5'),(6043,41,'string 5'),(6044,41,'string 5'),(6045,41,'string 5'),(6046,41,'string 5'),(6047,41,'string 5'),(6048,41,'string 5'),(6049,41,'string 5'),(6050,41,'string 5'),(6051,41,'string 5'),(6052,41,'string 5'),(6053,41,'string 5'),(6054,41,'string 5'),(6055,41,'string 5'),(6056,41,'string 5'),(6057,41,'string 5'),(6058,41,'string 5'),(6059,41,'string 5'),(6060,41,'string 5'),(6061,41,'string 5'),(6062,41,'string 5'),(6063,41,'string 5'),(6064,41,'string 5'),(6065,41,'string 5'),(6066,41,'string 5'),(6067,41,'string 5'),(6068,41,'string 5'),(6069,41,'string 5'),(6070,41,'string 5'),(6071,41,'string 5'),(6072,41,'string 5'),(6073,41,'string 5'),(6074,41,'string 5'),(6075,41,'string 5'),(6076,41,'string 5'),(6077,41,'string 5'),(6078,41,'string 5'),(6079,41,'string 5'),(6080,41,'string 5'),(6081,41,'string 5'),(6082,41,'string 5'),(6083,41,'string 5'),(6084,41,'string 5'),(6085,41,'string 5'),(6086,41,'string 5'),(6087,41,'string 5'),(6088,41,'string 5'),(6089,41,'string 5'),(6090,41,'string 5'),(6091,41,'string 5'),(6092,41,'string 5'),(6093,41,'string 5'),(6094,41,'string 5'),(6095,41,'string 5'),(6096,41,'string 5'),(6097,41,'string 5'),(6098,41,'string 5'),(6099,41,'string 5'),(6100,41,'string 5'),(6101,41,'string 5'),(6102,41,'string 5'),(6103,41,'string 5'),(6104,41,'string 5'),(6105,41,'string 5'),(6106,41,'string 5'),(6107,41,'string 5'),(6108,41,'string 5'),(6109,41,'string 5'),(6110,41,'string 5'),(6111,41,'string 5'),(6112,41,'string 5'),(6113,41,'string 5'),(6114,41,'string 5'),(6115,41,'string 5'),(6116,41,'string 5'),(6117,41,'string 5'),(6118,41,'string 5'),(6119,41,'string 5'),(6120,41,'string 5'),(6121,41,'string 5'),(6122,41,'string 5'),(6123,41,'string 5'),(6124,41,'string 5'),(6125,41,'string 5'),(6126,41,'string 5'),(6127,41,'string 5'),(6128,41,'string 5'),(6129,41,'string 5'),(6130,41,'string 5'),(6131,41,'string 5'),(6132,41,'string 5'),(6133,41,'string 5'),(6134,41,'string 5'),(6135,41,'string 5'),(6136,41,'string 5'),(6137,41,'string 5'),(6138,41,'string 5'),(6139,41,'string 5'),(6140,41,'string 5'),(6141,41,'string 5'),(6142,41,'string 5'),(6143,41,'string 5'),(6144,41,'string 5'),(6145,41,'string 5'),(6146,41,'string 5'),(6147,41,'string 5'),(6148,41,'string 5'),(6149,41,'string 5'),(6150,41,'string 5'),(6151,41,'string 5'),(6152,41,'string 5'),(6153,41,'string 5'),(6154,41,'string 5'),(6155,41,'string 5'),(6156,41,'string 5'),(6157,41,'string 5'),(6158,41,'string 5'),(6159,41,'string 5'),(6160,41,'string 5'),(6161,41,'string 5'),(6162,41,'string 5'),(6163,41,'string 5'),(6164,41,'string 5'),(6165,41,'string 5'),(6166,41,'string 5'),(6167,41,'string 5'),(6168,41,'string 5'),(6169,41,'string 5'),(6170,41,'string 5'),(6171,41,'string 5'),(6172,41,'string 5'),(6173,41,'string 5'),(6174,41,'string 5'),(6175,41,'string 5'),(6176,41,'string 5'),(6177,41,'string 5'),(6178,41,'string 5'),(6179,41,'string 5'),(6180,41,'string 5'),(6181,41,'string 5'),(6182,41,'string 5'),(6183,41,'string 5'),(6184,41,'string 5'),(6185,41,'string 5'),(6186,41,'string 5'),(6187,41,'string 5'),(6188,41,'string 5'),(6189,41,'string 5'),(6190,41,'string 5'),(6191,41,'string 5'),(6192,41,'string 5'),(6193,41,'string 5'),(6194,41,'string 5'),(6195,41,'string 5'),(6196,41,'string 5'),(6197,41,'string 5'),(6198,41,'string 5'),(6199,41,'string 5'),(6200,41,'string 5'),(6201,41,'string 5'),(6202,41,'string 5'),(6203,41,'string 5'),(6204,41,'string 5'),(6205,41,'string 5'),(6206,41,'string 5'),(6207,41,'string 5'),(6208,41,'string 5'),(6209,41,'string 5'),(6210,41,'string 5'),(6211,41,'string 5'),(6212,41,'string 5'),(6213,41,'string 5'),(6214,41,'string 5'),(6215,41,'string 5'),(6216,41,'string 5'),(6217,41,'string 5'),(6218,41,'string 5'),(6219,41,'string 5'),(6220,41,'string 5'),(6221,41,'string 5'),(6222,41,'string 5'),(6223,41,'string 5'),(6224,41,'string 5'),(6225,41,'string 5'),(6226,41,'string 5'),(6227,41,'string 5'),(6228,41,'string 5'),(6229,41,'string 5'),(6230,41,'string 5'),(6231,41,'string 5'),(6232,41,'string 5'),(6233,41,'string 5'),(6234,41,'string 5'),(6235,41,'string 5'),(6236,41,'string 5'),(6237,41,'string 5'),(6238,41,'string 5'),(6239,41,'string 5'),(6240,41,'string 5'),(6241,41,'string 5'),(6242,41,'string 5'),(6243,41,'string 5'),(6244,41,'string 5'),(6245,41,'string 5'),(6246,41,'string 5'),(6247,41,'string 5'),(6248,41,'string 5'),(6249,41,'string 5'),(6250,41,'string 5'),(6251,41,'string 5'),(6252,41,'string 5'),(6253,41,'string 5'),(6254,41,'string 5'),(6255,41,'string 5'),(6256,41,'string 5'),(6257,41,'string 5'),(6258,41,'string 5'),(6259,41,'string 5'),(6260,41,'string 5'),(6261,41,'string 5'),(6262,41,'string 5'),(6263,41,'string 5'),(6264,41,'string 5'),(6265,41,'string 5'),(6266,41,'string 5'),(6267,41,'string 5'),(6268,41,'string 5'),(6269,41,'string 5'),(6270,41,'string 5'),(6271,41,'string 5'),(6272,41,'string 5'),(6273,41,'string 5'),(6274,41,'string 5'),(6275,41,'string 5'),(6276,41,'string 5'),(6277,41,'string 5'),(6278,41,'string 5'),(6279,41,'string 5'),(6280,41,'string 5'),(6281,41,'string 5'),(6282,41,'string 5'),(6283,41,'string 5'),(6284,41,'string 5'),(6285,41,'string 5'),(6286,41,'string 5'),(6287,41,'string 5'),(6288,41,'string 5'),(6289,41,'string 5'),(6290,41,'string 5'),(6291,41,'string 5'),(6292,41,'string 5'),(6293,41,'string 5'),(6294,41,'string 5'),(6295,41,'string 5'),(6296,41,'string 5'),(6297,41,'string 5'),(6298,41,'string 5'),(6299,41,'string 5'),(6300,41,'string 5'),(6301,41,'string 5'),(6302,41,'string 5'),(6303,41,'string 5'),(6304,41,'string 5'),(6305,41,'string 5'),(6306,41,'string 5'),(6307,41,'string 5'),(6308,41,'string 5'),(6309,41,'string 5'),(6310,41,'string 5'),(6311,41,'string 5'),(6312,41,'string 5'),(6313,41,'string 5'),(6314,41,'string 5'),(6315,41,'string 5'),(6316,41,'string 5'),(6317,41,'string 5'),(6318,41,'string 5'),(6319,41,'string 5'),(6320,41,'string 5'),(6321,41,'string 5'),(6322,41,'string 5'),(6323,41,'string 5'),(6324,41,'string 5'),(6325,41,'string 5'),(6326,41,'string 5'),(6327,41,'string 5'),(6328,41,'string 5'),(6329,41,'string 5'),(6330,41,'string 5'),(6331,41,'string 5'),(6332,41,'string 5'),(6333,41,'string 5'),(6334,41,'string 5'),(6335,41,'string 5'),(6336,41,'string 5'),(6337,41,'string 5'),(6338,41,'string 5'),(6339,41,'string 5'),(6340,41,'string 5'),(6341,41,'string 5'),(6342,41,'string 5'),(6343,41,'string 5'),(6344,41,'string 5'),(6345,41,'string 5'),(6346,41,'string 5'),(6347,41,'string 5'),(6348,41,'string 5'),(6349,41,'string 5'),(6350,41,'string 5'),(6351,41,'string 5'),(6352,41,'string 5'),(6353,41,'string 5'),(6354,41,'string 5'),(6355,41,'string 5'),(6356,41,'string 5'),(6357,41,'string 5'),(6358,41,'string 5'),(6359,41,'string 5'),(6360,41,'string 5'),(6361,41,'string 5'),(6362,41,'string 5'),(6363,41,'string 5'),(6364,41,'string 5'),(6365,41,'string 5'),(6366,41,'string 5'),(6367,41,'string 5'),(6368,41,'string 5'),(6369,41,'string 5'),(6370,41,'string 5'),(6371,41,'string 5'),(6372,41,'string 5'),(6373,41,'string 5'),(6374,41,'string 5'),(6375,41,'string 5'),(6376,41,'string 5'),(6377,41,'string 5'),(6378,41,'string 5'),(6379,41,'string 5'),(6380,41,'string 5'),(6381,41,'string 5'),(6382,41,'string 5'),(6383,41,'string 5'),(6384,41,'string 5'),(6385,41,'string 5'),(6386,41,'string 5'),(6387,41,'string 5'),(6388,41,'string 5'),(6389,41,'string 5'),(6390,41,'string 5'),(6391,41,'string 5'),(6392,41,'string 5'),(6393,41,'string 5'),(6394,41,'string 5'),(6395,41,'string 5'),(6396,41,'string 5'),(6397,41,'string 5'),(6398,41,'string 5'),(6399,41,'string 5'),(6400,41,'string 5'),(6401,41,'string 5'),(6402,41,'string 5'),(6403,41,'string 5'),(6404,41,'string 5'),(6405,41,'string 5'),(6406,41,'string 5'),(6407,41,'string 5'),(6408,41,'string 5'),(6409,41,'string 5'),(6410,41,'string 5'),(6411,41,'string 5'),(6412,41,'string 5'),(6413,41,'string 5'),(6414,41,'string 5'),(6415,41,'string 5'),(6416,41,'string 5'),(6417,41,'string 5'),(6418,41,'string 5'),(6419,41,'string 5'),(6420,41,'string 5'),(6421,41,'string 5'),(6422,41,'string 5'),(6423,41,'string 5'),(6424,41,'string 5'),(6425,41,'string 5'),(6426,41,'string 5'),(6427,41,'string 5'),(6428,41,'string 5'),(6429,41,'string 5'),(6430,41,'string 5'),(6431,41,'string 5'),(6432,41,'string 5'),(6433,41,'string 5'),(6434,41,'string 5'),(6435,41,'string 5'),(6436,41,'string 5'),(6437,41,'string 5'),(6438,41,'string 5'),(6439,41,'string 5'),(6440,41,'string 5'),(6441,41,'string 5'),(6442,41,'string 5'),(6443,41,'string 5'),(6444,41,'string 5'),(6445,41,'string 5'),(6446,41,'string 5'),(6447,41,'string 5'),(6448,41,'string 5'),(6449,41,'string 5'),(6450,41,'string 5'),(6451,41,'string 5'),(6452,41,'string 5'),(6453,41,'string 5'),(6454,41,'string 5'),(6455,41,'string 5'),(6456,41,'string 5'),(6457,41,'string 5'),(6458,41,'string 5'),(6459,41,'string 5'),(6460,41,'string 5'),(6461,41,'string 5'),(6462,41,'string 5'),(6463,41,'string 5'),(6464,41,'string 5'),(6465,41,'string 5'),(6466,41,'string 5'),(6467,41,'string 5'),(6468,41,'string 5'),(6469,41,'string 5'),(6470,41,'string 5'),(6471,41,'string 5'),(6472,41,'string 5'),(6473,41,'string 5'),(6474,41,'string 5'),(6475,41,'string 5'),(6476,41,'string 5'),(6477,41,'string 5'),(6478,41,'string 5'),(6479,41,'string 5'),(6480,41,'string 5'),(6481,41,'string 5'),(6482,41,'string 5'),(6483,41,'string 5'),(6484,41,'string 5'),(6485,41,'string 5'),(6486,41,'string 5'),(6487,41,'string 5'),(6488,41,'string 5'),(6489,41,'string 5'),(6490,41,'string 5'),(6491,41,'string 5'),(6492,41,'string 5'),(6493,41,'string 5'),(6494,41,'string 5'),(6495,41,'string 5'),(6496,41,'string 5'),(6497,41,'string 5'),(6498,41,'string 5'),(6499,41,'string 5'),(6500,41,'string 5'),(6501,41,'string 5'),(6502,41,'string 5'),(6503,41,'string 5'),(6504,41,'string 5'),(6505,41,'string 5'),(6506,41,'string 5'),(6507,41,'string 5'),(6508,41,'string 5'),(6509,41,'string 5'),(6510,41,'string 5'),(6511,41,'string 5'),(6512,41,'string 5'),(6513,41,'string 5'),(6514,41,'string 5'),(6515,41,'string 5'),(6516,41,'string 5'),(6517,41,'string 5'),(6518,41,'string 5'),(6519,41,'string 5'),(6520,41,'string 5'),(6521,41,'string 5'),(6522,41,'string 5'),(6523,41,'string 5'),(6524,41,'string 5'),(6525,41,'string 5'),(6526,41,'string 5'),(6527,41,'string 5'),(6528,41,'string 5'),(6529,41,'string 5'),(6530,41,'string 5'),(6531,41,'string 5'),(6532,41,'string 5'),(6533,41,'string 5'),(6534,41,'string 5'),(6535,41,'string 5'),(6536,41,'string 5'),(6537,41,'string 5'),(6538,41,'string 5'),(6539,41,'string 5'),(6540,41,'string 5'),(6541,41,'string 5'),(6542,41,'string 5'),(6543,41,'string 5'),(6544,41,'string 5'),(6545,41,'string 5'),(6546,41,'string 5'),(6547,41,'string 5'),(6548,41,'string 5'),(6549,41,'string 5'),(6550,41,'string 5'),(6551,41,'string 5'),(6552,41,'string 5'),(6553,41,'string 5'),(6554,41,'string 5'),(6555,41,'string 5'),(6556,41,'string 5'),(6557,41,'string 5'),(6558,41,'string 5'),(6559,41,'string 5'),(6560,41,'string 5'),(6561,41,'string 5'),(6562,41,'string 5'),(6563,41,'string 5'),(6564,41,'string 5'),(6565,41,'string 5'),(6566,41,'string 5'),(6567,41,'string 5'),(6568,41,'string 5'),(6569,41,'string 5'),(6570,41,'string 5'),(6571,41,'string 5'),(6572,41,'string 5'),(6573,41,'string 5'),(6574,41,'string 5'),(6575,41,'string 5'),(6576,41,'string 5'),(6577,41,'string 5'),(6578,41,'string 5'),(6579,41,'string 5'),(6580,41,'string 5'),(6581,41,'string 5'),(6582,41,'string 5'),(6583,41,'string 5'),(6584,41,'string 5'),(6585,41,'string 5'),(6586,41,'string 5'),(6587,41,'string 5'),(6588,41,'string 5'),(6589,41,'string 5'),(6590,41,'string 5'),(6591,41,'string 5'),(6592,41,'string 5'),(6593,41,'string 5'),(6594,41,'string 5'),(6595,41,'string 5'),(6596,41,'string 5'),(6597,41,'string 5'),(6598,41,'string 5'),(6599,41,'string 5'),(6600,41,'string 5'),(6601,41,'string 5'),(6602,41,'string 5'),(6603,41,'string 5'),(6604,41,'string 5'),(6605,41,'string 5'),(6606,41,'string 5'),(6607,41,'string 5'),(6608,41,'string 5'),(6609,41,'string 5'),(6610,41,'string 5'),(6611,41,'string 5'),(6612,41,'string 5'),(6613,41,'string 5'),(6614,41,'string 5'),(6615,41,'string 5'),(6616,41,'string 5'),(6617,41,'string 5'),(6618,41,'string 5'),(6619,41,'string 5'),(6620,41,'string 5'),(6621,41,'string 5'),(6622,41,'string 5'),(6623,41,'string 5'),(6624,41,'string 5'),(6625,41,'string 5'),(6626,41,'string 5'),(6627,41,'string 5'),(6628,41,'string 5'),(6629,41,'string 5'),(6630,41,'string 5'),(6631,41,'string 5'),(6632,41,'string 5'),(6633,41,'string 5'),(6634,41,'string 5'),(6635,41,'string 5'),(6636,41,'string 5'),(6637,41,'string 5'),(6638,41,'string 5'),(6639,41,'string 5'),(6640,41,'string 5'),(6641,41,'string 5'),(6642,41,'string 5'),(6643,41,'string 5'),(6644,41,'string 5'),(6645,41,'string 5'),(6646,41,'string 5'),(6647,41,'string 5'),(6648,41,'string 5'),(6649,41,'string 5'),(6650,41,'string 5'),(6651,41,'string 5'),(6652,41,'string 5'),(6653,41,'string 5'),(6654,41,'string 5'),(6655,41,'string 5'),(6656,41,'string 5'),(6657,41,'string 5'),(6658,41,'string 5'),(6659,41,'string 5'),(6660,41,'string 5'),(6661,41,'string 5'),(6662,41,'string 5'),(6663,41,'string 5'),(6664,41,'string 5'),(6665,41,'string 5'),(6666,41,'string 5'),(6667,41,'string 5'),(6668,41,'string 5'),(6669,41,'string 5'),(6670,41,'string 5'),(6671,41,'string 5'),(6672,41,'string 5'),(6673,41,'string 5'),(6674,41,'string 5'),(6675,41,'string 5'),(6676,41,'string 5'),(6677,41,'string 5'),(6678,41,'string 5'),(6679,41,'string 5'),(6680,41,'string 5'),(6681,41,'string 5'),(6682,41,'string 5'),(6683,41,'string 5'),(6684,41,'string 5'),(6685,41,'string 5'),(6686,41,'string 5'),(6687,41,'string 5'),(6688,41,'string 5'),(6689,41,'string 5'),(6690,41,'string 5'),(6691,41,'string 5'),(6692,41,'string 5'),(6693,41,'string 5'),(6694,41,'string 5'),(6695,41,'string 5'),(6696,41,'string 5'),(6697,41,'string 5'),(6698,41,'string 5'),(6699,41,'string 5'),(6700,41,'string 5'),(6701,41,'string 5'),(6702,41,'string 5'),(6703,41,'string 5'),(6704,41,'string 5'),(6705,41,'string 5'),(6706,41,'string 5'),(6707,41,'string 5'),(6708,41,'string 5'),(6709,41,'string 5'),(6710,41,'string 5'),(6711,41,'string 5'),(6712,41,'string 5'),(6713,41,'string 5'),(6714,41,'string 5'),(6715,41,'string 5'),(6716,41,'string 5'),(6717,41,'string 5'),(6718,41,'string 5'),(6719,41,'string 5'),(6720,41,'string 5'),(6721,41,'string 5'),(6722,41,'string 5'),(6723,41,'string 5'),(6724,41,'string 5'),(6725,41,'string 5'),(6726,41,'string 5'),(6727,41,'string 5'),(6728,41,'string 5'),(6729,41,'string 5'),(6730,41,'string 5'),(6731,41,'string 5'),(6732,41,'string 5'),(6733,41,'string 5'),(6734,41,'string 5'),(6735,41,'string 5'),(6736,41,'string 5'),(6737,41,'string 5'),(6738,41,'string 5'),(6739,41,'string 5'),(6740,41,'string 5'),(6741,41,'string 5'),(6742,41,'string 5'),(6743,41,'string 5'),(6744,41,'string 5'),(6745,41,'string 5'),(6746,41,'string 5'),(6747,41,'string 5'),(6748,41,'string 5'),(6749,41,'string 5'),(6750,41,'string 5'),(6751,41,'string 5'),(6752,41,'string 5'),(6753,41,'string 5'),(6754,41,'string 5'),(6755,41,'string 5'),(6756,41,'string 5'),(6757,41,'string 5'),(6758,41,'string 5'),(6759,41,'string 5'),(6760,41,'string 5'),(6761,41,'string 5'),(6762,41,'string 5'),(6763,41,'string 5'),(6764,41,'string 5'),(6765,41,'string 5'),(6766,41,'string 5'),(6767,41,'string 5'),(6768,41,'string 5'),(6769,41,'string 5'),(6770,41,'string 5'),(6771,41,'string 5'),(6772,41,'string 5'),(6773,41,'string 5'),(6774,41,'string 5'),(6775,41,'string 5'),(6776,41,'string 5'),(6777,41,'string 5'),(6778,41,'string 5'),(6779,41,'string 5'),(6780,41,'string 5'),(6781,41,'string 5'),(6782,41,'string 5'),(6783,41,'string 5'),(6784,41,'string 5'),(6785,41,'string 5'),(6786,41,'string 5'),(6787,41,'string 5'),(6788,41,'string 5'),(6789,41,'string 5'),(6790,41,'string 5'),(6791,41,'string 5'),(6792,41,'string 5'),(6793,41,'string 5'),(6794,41,'string 5'),(6795,41,'string 5'),(6796,41,'string 5'),(6797,41,'string 5'),(6798,41,'string 5'),(6799,41,'string 5'),(6800,41,'string 5'),(6801,41,'string 5'),(6802,41,'string 5'),(6803,41,'string 5'),(6804,41,'string 5'),(6805,41,'string 5'),(6806,41,'string 5'),(6807,41,'string 5'),(6808,41,'string 5'),(6809,41,'string 5'),(6810,41,'string 5'),(6811,41,'string 5'),(6812,41,'string 5'),(6813,41,'string 5'),(6814,41,'string 5'),(6815,41,'string 5'),(6816,41,'string 5'),(6817,41,'string 5'),(6818,41,'string 5'),(6819,41,'string 5'),(6820,41,'string 5'),(6821,41,'string 5'),(6822,41,'string 5'),(6823,41,'string 5'),(6824,41,'string 5'),(6825,41,'string 5'),(6826,41,'string 5'),(6827,41,'string 5'),(6828,41,'string 5'),(6829,41,'string 5'),(6830,41,'string 5'),(6831,41,'string 5'),(6832,41,'string 5'),(6833,41,'string 5'),(6834,41,'string 5'),(6835,41,'string 5'),(6836,41,'string 5'),(6837,41,'string 5'),(6838,41,'string 5'),(6839,41,'string 5'),(6840,41,'string 5'),(6841,41,'string 5'),(6842,41,'string 5'),(6843,41,'string 5'),(6844,41,'string 5'),(6845,41,'string 5'),(6846,41,'string 5'),(6847,41,'string 5'),(6848,41,'string 5'),(6849,41,'string 5'),(6850,41,'string 5'),(6851,41,'string 5'),(6852,41,'string 5'),(6853,41,'string 5'),(6854,41,'string 5'),(6855,41,'string 5'),(6856,41,'string 5'),(6857,41,'string 5'),(6858,41,'string 5'),(6859,41,'string 5'),(6860,41,'string 5'),(6861,41,'string 5'),(6862,41,'string 5'),(6863,41,'string 5'),(6864,41,'string 5'),(6865,41,'string 5'),(6866,41,'string 5'),(6867,41,'string 5'),(6868,41,'string 5'),(6869,41,'string 5'),(6870,41,'string 5'),(6871,41,'string 5'),(6872,41,'string 5'),(6873,41,'string 5'),(6874,41,'string 5'),(6875,41,'string 5'),(6876,41,'string 5'),(6877,41,'string 5'),(6878,41,'string 5'),(6879,41,'string 5'),(6880,41,'string 5'),(6881,41,'string 5'),(6882,41,'string 5'),(6883,41,'string 5'),(6884,41,'string 5'),(6885,41,'string 5'),(6886,41,'string 5'),(6887,41,'string 5'),(6888,41,'string 5'),(6889,41,'string 5'),(6890,41,'string 5'),(6891,41,'string 5'),(6892,41,'string 5'),(6893,41,'string 5'),(6894,41,'string 5'),(6895,41,'string 5'),(6896,41,'string 5'),(6897,41,'string 5'),(6898,41,'string 5'),(6899,41,'string 5'),(6900,41,'string 5'),(6901,41,'string 5'),(6902,41,'string 5'),(6903,41,'string 5'),(6904,41,'string 5'),(6905,41,'string 5'),(6906,41,'string 5'),(6907,41,'string 5'),(6908,41,'string 5'),(6909,41,'string 5'),(6910,41,'string 5'),(6911,41,'string 5'),(6912,41,'string 5'),(6913,41,'string 5'),(6914,41,'string 5'),(6915,41,'string 5'),(6916,41,'string 5'),(6917,41,'string 5'),(6918,41,'string 5'),(6919,41,'string 5'),(6920,41,'string 5'),(6921,41,'string 5'),(6922,41,'string 5'),(6923,41,'string 5'),(6924,41,'string 5'),(6925,41,'string 5'),(6926,41,'string 5'),(6927,41,'string 5'),(6928,41,'string 5'),(6929,41,'string 5'),(6930,41,'string 5'),(6931,41,'string 5'),(6932,41,'string 5'),(6933,41,'string 5'),(6934,41,'string 5'),(6935,41,'string 5'),(6936,41,'string 5'),(6937,41,'string 5'),(6938,41,'string 5'),(6939,41,'string 5'),(6940,41,'string 5'),(6941,41,'string 5'),(6942,41,'string 5'),(6943,41,'string 5'),(6944,41,'string 5'),(6945,41,'string 5'),(6946,41,'string 5'),(6947,41,'string 5'),(6948,41,'string 5'),(6949,41,'string 5'),(6950,41,'string 5'),(6951,41,'string 5'),(6952,41,'string 5'),(6953,41,'string 5'),(6954,41,'string 5'),(6955,41,'string 5'),(6956,41,'string 5'),(6957,41,'string 5'),(6958,41,'string 5'),(6959,41,'string 5'),(6960,41,'string 5'),(6961,41,'string 5'),(6962,41,'string 5'),(6963,41,'string 5'),(6964,41,'string 5'),(6965,41,'string 5'),(6966,41,'string 5'),(6967,41,'string 5'),(6968,41,'string 5'),(6969,41,'string 5'),(6970,41,'string 5'),(6971,41,'string 5'),(6972,41,'string 5'),(6973,41,'string 5'),(6974,41,'string 5'),(6975,41,'string 5'),(6976,41,'string 5'),(6977,41,'string 5'),(6978,41,'string 5'),(6979,41,'string 5'),(6980,41,'string 5'),(6981,41,'string 5'),(6982,41,'string 5'),(6983,41,'string 5'),(6984,41,'string 5'),(6985,41,'string 5'),(6986,41,'string 5'),(6987,41,'string 5'),(6988,41,'string 5'),(6989,41,'string 5'),(6990,41,'string 5'),(6991,41,'string 5'),(6992,41,'string 5'),(6993,41,'string 5'),(6994,41,'string 5'),(6995,41,'string 5'),(6996,41,'string 5'),(6997,41,'string 5'),(6998,41,'string 5'),(6999,41,'string 5'),(7000,41,'string 5'),(7001,41,'string 5'),(7002,41,'string 5'),(7003,41,'string 5'),(7004,41,'string 5'),(7005,41,'string 5'),(7006,41,'string 5'),(7007,41,'string 5'),(7008,41,'string 5'),(7009,41,'string 5'),(7010,41,'string 5'),(7011,41,'string 5'),(7012,41,'string 5'),(7013,41,'string 5'),(7014,41,'string 5'),(7015,41,'string 5'),(7016,41,'string 5'),(7017,41,'string 5'),(7018,41,'string 5'),(7019,41,'string 5'),(7020,41,'string 5'),(7021,41,'string 5'),(7022,41,'string 5'),(7023,41,'string 5'),(7024,41,'string 5'),(7025,41,'string 5'),(7026,41,'string 5'),(7027,41,'string 5'),(7028,41,'string 5'),(7029,41,'string 5'),(7030,41,'string 5'),(7031,41,'string 5'),(7032,41,'string 5'),(7033,41,'string 5'),(7034,41,'string 5'),(7035,41,'string 5'),(7036,41,'string 5'),(7037,41,'string 5'),(7038,41,'string 5'),(7039,41,'string 5'),(7040,41,'string 5'),(7041,41,'string 5'),(7042,41,'string 5'),(7043,41,'string 5'),(7044,41,'string 5'),(7045,41,'string 5'),(7046,41,'string 5'),(7047,41,'string 5'),(7048,41,'string 5'),(7049,41,'string 5'),(7050,41,'string 5'),(7051,41,'string 5'),(7052,41,'string 5'),(7053,41,'string 5'),(7054,41,'string 5'),(7055,41,'string 5'),(7056,41,'string 5'),(7057,41,'string 5'),(7058,41,'string 5'),(7059,41,'string 5'),(7060,41,'string 5'),(7061,41,'string 5'),(7062,41,'string 5'),(7063,41,'string 5'),(7064,41,'string 5'),(7065,41,'string 5'),(7066,41,'string 5'),(7067,41,'string 5'),(7068,41,'string 5'),(7069,41,'string 5'),(7070,41,'string 5'),(7071,41,'string 5'),(7072,41,'string 5'),(7073,41,'string 5'),(7074,41,'string 5'),(7075,41,'string 5'),(7076,41,'string 5'),(7077,41,'string 5'),(7078,41,'string 5'),(7079,41,'string 5'),(7080,41,'string 5'),(7081,41,'string 5'),(7082,41,'string 5'),(7083,41,'string 5'),(7084,41,'string 5'),(7085,41,'string 5'),(7086,41,'string 5'),(7087,41,'string 5'),(7088,41,'string 5'),(7089,41,'string 5'),(7090,41,'string 5'),(7091,41,'string 5'),(7092,41,'string 5'),(7093,41,'string 5'),(7094,41,'string 5'),(7095,41,'string 5'),(7096,41,'string 5'),(7097,41,'string 5'),(7098,41,'string 5'),(7099,41,'string 5'),(7100,41,'string 5'),(7101,41,'string 5'),(7102,41,'string 5'),(7103,41,'string 5'),(7104,41,'string 5'),(7105,41,'string 5'),(7106,41,'string 5'),(7107,41,'string 5'),(7108,41,'string 5'),(7109,41,'string 5'),(7110,41,'string 5'),(7111,41,'string 5'),(7112,41,'string 5'),(7113,41,'string 5'),(7114,41,'string 5'),(7115,41,'string 5'),(7116,41,'string 5'),(7117,41,'string 5'),(7118,41,'string 5'),(7119,41,'string 5'),(7120,41,'string 5'),(7121,41,'string 5'),(7122,41,'string 5'),(7123,41,'string 5'),(7124,41,'string 5'),(7125,41,'string 5'),(7126,41,'string 5'),(7127,41,'string 5'),(7128,41,'string 5'),(7129,41,'string 5'),(7130,41,'string 5'),(7131,41,'string 5'),(7132,41,'string 5'),(7133,41,'string 5'),(7134,41,'string 5'),(7135,41,'string 5'),(7136,41,'string 5'),(7137,41,'string 5'),(7138,41,'string 5'),(7139,41,'string 5'),(7140,41,'string 5'),(7141,41,'string 5'),(7142,41,'string 5'),(7143,41,'string 5'),(7144,41,'string 5'),(7145,41,'string 5'),(7146,41,'string 5'),(7147,41,'string 5'),(7148,41,'string 5'),(7149,41,'string 5'),(7150,41,'string 5'),(7151,41,'string 5'),(7152,41,'string 5'),(7153,41,'string 5'),(7154,41,'string 5'),(7155,41,'string 5'),(7156,41,'string 5'),(7157,41,'string 5'),(7158,41,'string 5'),(7159,41,'string 5'),(7160,41,'string 5'),(7161,41,'string 5'),(7162,41,'string 5'),(7163,41,'string 5'),(7164,41,'string 5'),(7165,41,'string 5'),(7166,41,'string 5'),(7167,41,'string 5'),(7168,41,'string 5'),(7169,41,'string 5'),(7170,41,'string 5'),(7171,41,'string 5'),(7172,41,'string 5'),(7173,41,'string 5'),(7174,41,'string 5'),(7175,41,'string 5'),(7176,41,'string 5'),(7177,41,'string 5'),(7178,41,'string 5'),(7179,41,'string 5'),(7180,41,'string 5'),(7181,41,'string 5'),(7182,41,'string 5'),(7183,41,'string 5'),(7184,41,'string 5'),(7185,41,'string 5'),(7186,41,'string 5'),(7187,41,'string 5'),(7188,41,'string 5'),(7189,41,'string 5'),(7190,41,'string 5'),(7191,41,'string 5'),(7192,41,'string 5'),(7193,41,'string 5'),(7194,41,'string 5'),(7195,41,'string 5'),(7196,41,'string 5'),(7197,41,'string 5'),(7198,41,'string 5'),(7199,41,'string 5'),(7200,41,'string 5'),(7201,41,'string 5'),(7202,41,'string 5'),(7203,41,'string 5'),(7204,41,'string 5'),(7205,41,'string 5'),(7206,41,'string 5'),(7207,41,'string 5'),(7208,41,'string 5'),(7209,41,'string 5'),(7210,41,'string 5'),(7211,41,'string 5'),(7212,41,'string 5'),(7213,41,'string 5'),(7214,41,'string 5'),(7215,41,'string 5'),(7216,41,'string 5'),(7217,41,'string 5'),(7218,41,'string 5'),(7219,41,'string 5'),(7220,41,'string 5'),(7221,41,'string 5'),(7222,41,'string 5'),(7223,41,'string 5'),(7224,41,'string 5'),(7225,41,'string 5'),(7226,41,'string 5'),(7227,41,'string 5'),(7228,41,'string 5'),(7229,41,'string 5'),(7230,41,'string 5'),(7231,41,'string 5'),(7232,41,'string 5'),(7233,41,'string 5'),(7234,41,'string 5'),(7235,41,'string 5'),(7236,41,'string 5'),(7237,41,'string 5'),(7238,41,'string 5'),(7239,41,'string 5'),(7240,41,'string 5'),(7241,41,'string 5'),(7242,41,'string 5'),(7243,41,'string 5'),(7244,41,'string 5'),(7245,41,'string 5'),(7246,41,'string 5'),(7247,41,'string 5'),(7248,41,'string 5'),(7249,41,'string 5'),(7250,41,'string 5'),(7251,41,'string 5'),(7252,41,'string 5'),(7253,41,'string 5'),(7254,41,'string 5'),(7255,41,'string 5'),(7256,41,'string 5'),(7257,41,'string 5'),(7258,41,'string 5'),(7259,41,'string 5'),(7260,41,'string 5'),(7261,41,'string 5'),(7262,41,'string 5'),(7263,41,'string 5'),(7264,41,'string 5'),(7265,41,'string 5'),(7266,41,'string 5'),(7267,41,'string 5'),(7268,41,'string 5'),(7269,41,'string 5'),(7270,41,'string 5'),(7271,41,'string 5'),(7272,41,'string 5'),(7273,41,'string 5'),(7274,41,'string 5'),(7275,41,'string 5'),(7276,41,'string 5'),(7277,41,'string 5'),(7278,41,'string 5'),(7279,41,'string 5'),(7280,41,'string 5'),(7281,41,'string 5'),(7282,41,'string 5'),(7283,41,'string 5'),(7284,41,'string 5'),(7285,41,'string 5'),(7286,41,'string 5'),(7287,41,'string 5'),(7288,41,'string 5'),(7289,41,'string 5'),(7290,41,'string 5'),(7291,41,'string 5'),(7292,41,'string 5'),(7293,41,'string 5'),(7294,41,'string 5'),(7295,41,'string 5'),(7296,41,'string 5'),(7297,41,'string 5'),(7298,41,'string 5'),(7299,41,'string 5'),(7300,41,'string 5'),(7301,41,'string 5'),(7302,41,'string 5'),(7303,41,'string 5'),(7304,41,'string 5'),(7305,41,'string 5'),(7306,41,'string 5'),(7307,41,'string 5'),(7308,41,'string 5'),(7309,41,'string 5'),(7310,41,'string 5'),(7311,41,'string 5'),(7312,41,'string 5'),(7313,41,'string 5'),(7314,41,'string 5'),(7315,41,'string 5'),(7316,41,'string 5'),(7317,41,'string 5'),(7318,41,'string 5'),(7319,41,'string 5'),(7320,41,'string 5'),(7321,41,'string 5'),(7322,41,'string 5'),(7323,41,'string 5'),(7324,41,'string 5'),(7325,41,'string 5'),(7326,41,'string 5'),(7327,41,'string 5'),(7328,41,'string 5'),(7329,41,'string 5'),(7330,41,'string 5'),(7331,41,'string 5'),(7332,41,'string 5'),(7333,41,'string 5'),(7334,41,'string 5'),(7335,41,'string 5'),(7336,41,'string 5'),(7337,41,'string 5'),(7338,41,'string 5'),(7339,41,'string 5'),(7340,41,'string 5'),(7341,41,'string 5'),(7342,41,'string 5'),(7343,41,'string 5'),(7344,41,'string 5'),(7345,41,'string 5'),(7346,41,'string 5'),(7347,41,'string 5'),(7348,41,'string 5'),(7349,41,'string 5'),(7350,41,'string 5'),(7351,41,'string 5'),(7352,41,'string 5'),(7353,41,'string 5'),(7354,41,'string 5'),(7355,41,'string 5'),(7356,41,'string 5'),(7357,41,'string 5'),(7358,41,'string 5'),(7359,41,'string 5'),(7360,41,'string 5'),(7361,41,'string 5'),(7362,41,'string 5'),(7363,41,'string 5'),(7364,41,'string 5'),(7365,41,'string 5'),(7366,41,'string 5'),(7367,41,'string 5'),(7368,41,'string 5'),(7369,41,'string 5'),(7370,41,'string 5'),(7371,41,'string 5'),(7372,41,'string 5'),(7373,41,'string 5'),(7374,41,'string 5'),(7375,41,'string 5'),(7376,41,'string 5'),(7377,41,'string 5'),(7378,41,'string 5'),(7379,41,'string 5'),(7380,41,'string 5'),(7381,41,'string 5'),(7382,41,'string 5'),(7383,41,'string 5'),(7384,41,'string 5'),(7385,41,'string 5'),(7386,41,'string 5'),(7387,41,'string 5'),(7388,41,'string 5'),(7389,41,'string 5'),(7390,41,'string 5'),(7391,41,'string 5'),(7392,41,'string 5'),(7393,41,'string 5'),(7394,41,'string 5'),(7395,41,'string 5'),(7396,41,'string 5'),(7397,41,'string 5'),(7398,41,'string 5'),(7399,41,'string 5'),(7400,41,'string 5'),(7401,41,'string 5'),(7402,41,'string 5'),(7403,41,'string 5'),(7404,41,'string 5'),(7405,41,'string 5'),(7406,41,'string 5'),(7407,41,'string 5'),(7408,41,'string 5'),(7409,41,'string 5'),(7410,41,'string 5'),(7411,41,'string 5'),(7412,41,'string 5'),(7413,41,'string 5'),(7414,41,'string 5'),(7415,41,'string 5'),(7416,41,'string 5'),(7417,41,'string 5'),(7418,41,'string 5'),(7419,41,'string 5'),(7420,41,'string 5'),(7421,41,'string 5'),(7422,41,'string 5'),(7423,41,'string 5'),(7424,41,'string 5'),(7425,41,'string 5'),(7426,41,'string 5'),(7427,41,'string 5'),(7428,41,'string 5'),(7429,41,'string 5'),(7430,41,'string 5'),(7431,41,'string 5'),(7432,41,'string 5'),(7433,41,'string 5'),(7434,41,'string 5'),(7435,41,'string 5'),(7436,41,'string 5'),(7437,41,'string 5'),(7438,41,'string 5'),(7439,41,'string 5'),(7440,41,'string 5'),(7441,41,'string 5'),(7442,41,'string 5'),(7443,41,'string 5'),(7444,41,'string 5'),(7445,41,'string 5'),(7446,41,'string 5'),(7447,41,'string 5'),(7448,41,'string 5'),(7449,41,'string 5'),(7450,41,'string 5'),(7451,41,'string 5'),(7452,41,'string 5'),(7453,41,'string 5'),(7454,41,'string 5'),(7455,41,'string 5'),(7456,41,'string 5'),(7457,41,'string 5'),(7458,41,'string 5'),(7459,41,'string 5'),(7460,41,'string 5'),(7461,41,'string 5'),(7462,41,'string 5'),(7463,41,'string 5'),(7464,41,'string 5'),(7465,41,'string 5'),(7466,41,'string 5'),(7467,41,'string 5'),(7468,41,'string 5'),(7469,41,'string 5'),(7470,41,'string 5'),(7471,41,'string 5'),(7472,41,'string 5'),(7473,41,'string 5'),(7474,41,'string 5'),(7475,41,'string 5'),(7476,41,'string 5'),(7477,41,'string 5'),(7478,41,'string 5'),(7479,41,'string 5'),(7480,41,'string 5'),(7481,41,'string 5'),(7482,41,'string 5'),(7483,41,'string 5'),(7484,41,'string 5'),(7485,41,'string 5'),(7486,41,'string 5'),(7487,41,'string 5'),(7488,41,'string 5'),(7489,41,'string 5'),(7490,41,'string 5'),(7491,41,'string 5'),(7492,41,'string 5'),(7493,41,'string 5'),(7494,41,'string 5'),(7495,41,'string 5'),(7496,41,'string 5'),(7497,41,'string 5'),(7498,41,'string 5'),(7499,41,'string 5'),(7500,41,'string 5'),(7501,41,'string 5'),(7502,41,'string 5'),(7503,41,'string 5'),(7504,41,'string 5'),(7505,41,'string 5'),(7506,41,'string 5'),(7507,41,'string 5'),(7508,41,'string 5'),(7509,41,'string 5'),(7510,41,'string 5'),(7511,41,'string 5'),(7512,41,'string 5'),(7513,41,'string 5'),(7514,41,'string 5'),(7515,41,'string 5'),(7516,41,'string 5'),(7517,41,'string 5'),(7518,41,'string 5'),(7519,41,'string 5'),(7520,41,'string 5'),(7521,41,'string 5'),(7522,41,'string 5'),(7523,41,'string 5'),(7524,41,'string 5'),(7525,41,'string 5'),(7526,41,'string 5'),(7527,41,'string 5'),(7528,41,'string 5'),(7529,41,'string 5'),(7530,41,'string 5'),(7531,41,'string 5'),(7532,41,'string 5'),(7533,41,'string 5'),(7534,41,'string 5'),(7535,41,'string 5'),(7536,41,'string 5'),(7537,41,'string 5'),(7538,41,'string 5'),(7539,41,'string 5'),(7540,41,'string 5'),(7541,41,'string 5'),(7542,41,'string 5'),(7543,41,'string 5'),(7544,41,'string 5'),(7545,41,'string 5'),(7546,41,'string 5'),(7547,41,'string 5'),(7548,41,'string 5'),(7549,41,'string 5'),(7550,41,'string 5'),(7551,41,'string 5'),(7552,41,'string 5'),(7553,41,'string 5'),(7554,41,'string 5'),(7555,41,'string 5'),(7556,41,'string 5'),(7557,41,'string 5'),(7558,41,'string 5'),(7559,41,'string 5'),(7560,41,'string 5'),(7561,41,'string 5'),(7562,41,'string 5'),(7563,41,'string 5'),(7564,41,'string 5'),(7565,41,'string 5'),(7566,41,'string 5'),(7567,41,'string 5'),(7568,41,'string 5'),(7569,41,'string 5'),(7570,41,'string 5'),(7571,41,'string 5'),(7572,41,'string 5'),(7573,41,'string 5'),(7574,41,'string 5'),(7575,41,'string 5'),(7576,41,'string 5'),(7577,41,'string 5'),(7578,41,'string 5'),(7579,41,'string 5'),(7580,41,'string 5'),(7581,41,'string 5'),(7582,41,'string 5'),(7583,41,'string 5'),(7584,41,'string 5'),(7585,41,'string 5'),(7586,41,'string 5'),(7587,41,'string 5'),(7588,41,'string 5'),(7589,41,'string 5'),(7590,41,'string 5'),(7591,41,'string 5'),(7592,41,'string 5'),(7593,41,'string 5'),(7594,41,'string 5'),(7595,41,'string 5'),(7596,41,'string 5'),(7597,41,'string 5'),(7598,41,'string 5'),(7599,41,'string 5'),(7600,41,'string 5'),(7601,41,'string 5'),(7602,41,'string 5'),(7603,41,'string 5'),(7604,41,'string 5'),(7605,41,'string 5'),(7606,41,'string 5'),(7607,41,'string 5'),(7608,41,'string 5'),(7609,41,'string 5'),(7610,41,'string 5'),(7611,41,'string 5'),(7612,41,'string 5'),(7613,41,'string 5'),(7614,41,'string 5'),(7615,41,'string 5'),(7616,41,'string 5'),(7617,41,'string 5'),(7618,41,'string 5'),(7619,41,'string 5'),(7620,41,'string 5'),(7621,41,'string 5'),(7622,41,'string 5'),(7623,41,'string 5'),(7624,41,'string 5'),(7625,41,'string 5'),(7626,41,'string 5'),(7627,41,'string 5'),(7628,41,'string 5'),(7629,41,'string 5'),(7630,41,'string 5'),(7631,41,'string 5'),(7632,41,'string 5'),(7633,41,'string 5'),(7634,41,'string 5'),(7635,41,'string 5'),(7636,41,'string 5'),(7637,41,'string 5'),(7638,41,'string 5'),(7639,41,'string 5'),(7640,41,'string 5'),(7641,41,'string 5'),(7642,41,'string 5'),(7643,41,'string 5'),(7644,41,'string 5'),(7645,41,'string 5'),(7646,41,'string 5'),(7647,41,'string 5'),(7648,41,'string 5'),(7649,41,'string 5'),(7650,41,'string 5'),(7651,41,'string 5'),(7652,41,'string 5'),(7653,41,'string 5'),(7654,41,'string 5'),(7655,41,'string 5'),(7656,41,'string 5'),(7657,41,'string 5'),(7658,41,'string 5'),(7659,41,'string 5'),(7660,41,'string 5'),(7661,41,'string 5'),(7662,41,'string 5'),(7663,41,'string 5'),(7664,41,'string 5'),(7665,41,'string 5'),(7666,41,'string 5'),(7667,41,'string 5'),(7668,41,'string 5'),(7669,41,'string 5'),(7670,41,'string 5'),(7671,41,'string 5'),(7672,41,'string 5'),(7673,41,'string 5'),(7674,41,'string 5'),(7675,41,'string 5'),(7676,41,'string 5'),(7677,41,'string 5'),(7678,41,'string 5'),(7679,41,'string 5'),(7680,41,'string 5'),(7681,41,'string 5'),(7682,41,'string 5'),(7683,41,'string 5'),(7684,41,'string 5'),(7685,41,'string 5'),(7686,41,'string 5'),(7687,41,'string 5'),(7688,41,'string 5'),(7689,41,'string 5'),(7690,41,'string 5'),(7691,41,'string 5'),(7692,41,'string 5'),(7693,41,'string 5'),(7694,41,'string 5'),(7695,41,'string 5'),(7696,41,'string 5'),(7697,41,'string 5'),(7698,41,'string 5'),(7699,41,'string 5'),(7700,41,'string 5'),(7701,41,'string 5'),(7702,41,'string 5'),(7703,41,'string 5'),(7704,41,'string 5'),(7705,41,'string 5'),(7706,41,'string 5'),(7707,41,'string 5'),(7708,41,'string 5'),(7709,41,'string 5'),(7710,41,'string 5'),(7711,41,'string 5'),(7712,41,'string 5'),(7713,41,'string 5'),(7714,41,'string 5'),(7715,41,'string 5'),(7716,41,'string 5'),(7717,41,'string 5'),(7718,41,'string 5'),(7719,41,'string 5'),(7720,41,'string 5'),(7721,41,'string 5'),(7722,41,'string 5'),(7723,41,'string 5'),(7724,41,'string 5'),(7725,41,'string 5'),(7726,41,'string 5'),(7727,41,'string 5'),(7728,41,'string 5'),(7729,41,'string 5'),(7730,41,'string 5'),(7731,41,'string 5'),(7732,41,'string 5'),(7733,41,'string 5'),(7734,41,'string 5'),(7735,41,'string 5'),(7736,41,'string 5'),(7737,41,'string 5'),(7738,41,'string 5'),(7739,41,'string 5'),(7740,41,'string 5'),(7741,41,'string 5'),(7742,41,'string 5'),(7743,41,'string 5'),(7744,41,'string 5'),(7745,41,'string 5'),(7746,41,'string 5'),(7747,41,'string 5'),(7748,41,'string 5'),(7749,41,'string 5'),(7750,41,'string 5'),(7751,41,'string 5'),(7752,41,'string 5'),(7753,41,'string 5'),(7754,41,'string 5'),(7755,41,'string 5'),(7756,41,'string 5'),(7757,41,'string 5'),(7758,41,'string 5'),(7759,41,'string 5'),(7760,41,'string 5'),(7761,41,'string 5'),(7762,41,'string 5'),(7763,41,'string 5'),(7764,41,'string 5'),(7765,41,'string 5'),(7766,41,'string 5'),(7767,41,'string 5'),(7768,41,'string 5'),(7769,41,'string 5'),(7770,41,'string 5'),(7771,41,'string 5'),(7772,41,'string 5'),(7773,41,'string 5'),(7774,41,'string 5'),(7775,41,'string 5'),(7776,41,'string 5'),(7777,41,'string 5'),(7778,41,'string 5'),(7779,41,'string 5'),(7780,41,'string 5'),(7781,41,'string 5'),(7782,41,'string 5'),(7783,41,'string 5'),(7784,41,'string 5'),(7785,41,'string 5'),(7786,41,'string 5'),(7787,41,'string 5'),(7788,41,'string 5'),(7789,41,'string 5'),(7790,41,'string 5'),(7791,41,'string 5'),(7792,41,'string 5'),(7793,41,'string 5'),(7794,41,'string 5'),(7795,41,'string 5'),(7796,41,'string 5'),(7797,41,'string 5'),(7798,41,'string 5'),(7799,41,'string 5'),(7800,41,'string 5'),(7801,41,'string 5'),(7802,41,'string 5'),(7803,41,'string 5'),(7804,41,'string 5'),(7805,41,'string 5'),(7806,41,'string 5'),(7807,41,'string 5'),(7808,41,'string 5'),(7809,41,'string 5'),(7810,41,'string 5'),(7811,41,'string 5'),(7812,41,'string 5'),(7813,41,'string 5'),(7814,41,'string 5'),(7815,41,'string 5'),(7816,41,'string 5'),(7817,41,'string 5'),(7818,41,'string 5'),(7819,41,'string 5'),(7820,41,'string 5'),(7821,41,'string 5'),(7822,41,'string 5'),(7823,41,'string 5'),(7824,41,'string 5'),(7825,41,'string 5'),(7826,41,'string 5'),(7827,41,'string 5'),(7828,41,'string 5'),(7829,41,'string 5'),(7830,41,'string 5'),(7831,41,'string 5'),(7832,41,'string 5'),(7833,41,'string 5'),(7834,41,'string 5'),(7835,41,'string 5'),(7836,41,'string 5'),(7837,41,'string 5'),(7838,41,'string 5'),(7839,41,'string 5'),(7840,41,'string 5'),(7841,41,'string 5'),(7842,41,'string 5'),(7843,41,'string 5'),(7844,41,'string 5'),(7845,41,'string 5'),(7846,41,'string 5'),(7847,41,'string 5'),(7848,41,'string 5'),(7849,41,'string 5'),(7850,41,'string 5'),(7851,41,'string 5'),(7852,41,'string 5'),(7853,41,'string 5'),(7854,41,'string 5'),(7855,41,'string 5'),(7856,41,'string 5'),(7857,41,'string 5'),(7858,41,'string 5'),(7859,41,'string 5'),(7860,41,'string 5'),(7861,41,'string 5'),(7862,41,'string 5'),(7863,41,'string 5'),(7864,41,'string 5'),(7865,41,'string 5'),(7866,41,'string 5'),(7867,41,'string 5'),(7868,41,'string 5'),(7869,41,'string 5'),(7870,41,'string 5'),(7871,41,'string 5'),(7872,41,'string 5'),(7873,41,'string 5'),(7874,41,'string 5'),(7875,41,'string 5'),(7876,41,'string 5'),(7877,41,'string 5'),(7878,41,'string 5'),(7879,41,'string 5'),(7880,41,'string 5'),(7881,41,'string 5'),(7882,41,'string 5'),(7883,41,'string 5'),(7884,41,'string 5'),(7885,41,'string 5'),(7886,41,'string 5'),(7887,41,'string 5'),(7888,41,'string 5'),(7889,41,'string 5'),(7890,41,'string 5'),(7891,41,'string 5'),(7892,41,'string 5'),(7893,41,'string 5'),(7894,41,'string 5'),(7895,41,'string 5'),(7896,41,'string 5'),(7897,41,'string 5'),(7898,41,'string 5'),(7899,41,'string 5'),(7900,41,'string 5'),(7901,41,'string 5'),(7902,41,'string 5'),(7903,41,'string 5'),(7904,41,'string 5'),(7905,41,'string 5'),(7906,41,'string 5'),(7907,41,'string 5'),(7908,41,'string 5'),(7909,41,'string 5'),(7910,41,'string 5'),(7911,41,'string 5'),(7912,41,'string 5'),(7913,41,'string 5'),(7914,41,'string 5'),(7915,41,'string 5'),(7916,41,'string 5'),(7917,41,'string 5'),(7918,41,'string 5'),(7919,41,'string 5'),(7920,41,'string 5'),(7921,41,'string 5'),(7922,41,'string 5'),(7923,41,'string 5'),(7924,41,'string 5'),(7925,41,'string 5'),(7926,41,'string 5'),(7927,41,'string 5'),(7928,41,'string 5'),(7929,41,'string 5'),(7930,41,'string 5'),(7931,41,'string 5'),(7932,41,'string 5'),(7933,41,'string 5'),(7934,41,'string 5'),(7935,41,'string 5'),(7936,41,'string 5'),(7937,41,'string 5'),(7938,41,'string 5'),(7939,41,'string 5'),(7940,41,'string 5'),(7941,41,'string 5'),(7942,41,'string 5'),(7943,41,'string 5'),(7944,41,'string 5'),(7945,41,'string 5'),(7946,41,'string 5'),(7947,41,'string 5'),(7948,41,'string 5'),(7949,41,'string 5'),(7950,41,'string 5'),(7951,41,'string 5'),(7952,41,'string 5'),(7953,41,'string 5'),(7954,41,'string 5'),(7955,41,'string 5'),(7956,41,'string 5'),(7957,41,'string 5'),(7958,41,'string 5'),(7959,41,'string 5'),(7960,41,'string 5'),(7961,41,'string 5'),(7962,41,'string 5'),(7963,41,'string 5'),(7964,41,'string 5'),(7965,41,'string 5'),(7966,41,'string 5'),(7967,41,'string 5'),(7968,41,'string 5'),(7969,41,'string 5'),(7970,41,'string 5'),(7971,41,'string 5'),(7972,41,'string 5'),(7973,41,'string 5'),(7974,41,'string 5'),(7975,41,'string 5'),(7976,41,'string 5'),(7977,41,'string 5'),(7978,41,'string 5'),(7979,41,'string 5'),(7980,41,'string 5'),(7981,41,'string 5'),(7982,41,'string 5'),(7983,41,'string 5'),(7984,41,'string 5'),(7985,41,'string 5'),(7986,41,'string 5'),(7987,41,'string 5'),(7988,41,'string 5'),(7989,41,'string 5'),(7990,41,'string 5'),(7991,41,'string 5'),(7992,41,'string 5'),(7993,41,'string 5'),(7994,41,'string 5'),(7995,41,'string 5'),(7996,41,'string 5'),(7997,41,'string 5'),(7998,41,'string 5'),(7999,41,'string 5'),(8000,41,'string 5'),(8001,41,'string 5'),(8002,41,'string 5'),(8003,41,'string 5'),(8004,41,'string 5'),(8005,41,'string 5'),(8006,41,'string 5'),(8007,41,'string 5'),(8008,41,'string 5'),(8009,41,'string 5'),(8010,41,'string 5'),(8011,41,'string 5'),(8012,41,'string 5'),(8013,41,'string 5'),(8014,41,'string 5'),(8015,41,'string 5'),(8016,41,'string 5'),(8017,41,'string 5'),(8018,41,'string 5'),(8019,41,'string 5'),(8020,41,'string 5'),(8021,41,'string 5'),(8022,41,'string 5'),(8023,41,'string 5'),(8024,41,'string 5'),(8025,41,'string 5'),(8026,41,'string 5'),(8027,41,'string 5'),(8028,41,'string 5'),(8029,41,'string 5'),(8030,41,'string 5'),(8031,41,'string 5'),(8032,41,'string 5'),(8033,41,'string 5'),(8034,41,'string 5'),(8035,41,'string 5'),(8036,41,'string 5'),(8037,41,'string 5'),(8038,41,'string 5'),(8039,41,'string 5'),(8040,41,'string 5'),(8041,41,'string 5'),(8042,41,'string 5'),(8043,41,'string 5'),(8044,41,'string 5'),(8045,41,'string 5'),(8046,41,'string 5'),(8047,41,'string 5'),(8048,41,'string 5'),(8049,41,'string 5'),(8050,41,'string 5'),(8051,41,'string 5'),(8052,41,'string 5'),(8053,41,'string 5'),(8054,41,'string 5'),(8055,41,'string 5'),(8056,41,'string 5'),(8057,41,'string 5'),(8058,41,'string 5'),(8059,41,'string 5'),(8060,41,'string 5'),(8061,41,'string 5'),(8062,41,'string 5'),(8063,41,'string 5'),(8064,41,'string 5'),(8065,41,'string 5'),(8066,41,'string 5'),(8067,41,'string 5'),(8068,41,'string 5'),(8069,41,'string 5'),(8070,41,'string 5'),(8071,41,'string 5'),(8072,41,'string 5'),(8073,41,'string 5'),(8074,41,'string 5'),(8075,41,'string 5'),(8076,41,'string 5'),(8077,41,'string 5'),(8078,41,'string 5'),(8079,41,'string 5'),(8080,41,'string 5'),(8081,41,'string 5'),(8082,41,'string 5'),(8083,41,'string 5'),(8084,41,'string 5'),(8085,41,'string 5'),(8086,41,'string 5'),(8087,41,'string 5'),(8088,41,'string 5'),(8089,41,'string 5'),(8090,41,'string 5'),(8091,41,'string 5'),(8092,41,'string 5'),(8093,41,'string 5'),(8094,41,'string 5'),(8095,41,'string 5'),(8096,41,'string 5'),(8097,41,'string 5'),(8098,41,'string 5'),(8099,41,'string 5'),(8100,41,'string 5'),(8101,41,'string 5'),(8102,41,'string 5'),(8103,41,'string 5'),(8104,41,'string 5'),(8105,41,'string 5'),(8106,41,'string 5'),(8107,41,'string 5'),(8108,41,'string 5'),(8109,41,'string 5'),(8110,41,'string 5'),(8111,41,'string 5'),(8112,41,'string 5'),(8113,41,'string 5'),(8114,41,'string 5'),(8115,41,'string 5'),(8116,41,'string 5'),(8117,41,'string 5'),(8118,41,'string 5'),(8119,41,'string 5'),(8120,41,'string 5'),(8121,41,'string 5'),(8122,41,'string 5'),(8123,41,'string 5'),(8124,41,'string 5'),(8125,41,'string 5'),(8126,41,'string 5'),(8127,41,'string 5'),(8128,41,'string 5'),(8129,41,'string 5'),(8130,41,'string 5'),(8131,41,'string 5'),(8132,41,'string 5'),(8133,41,'string 5'),(8134,41,'string 5'),(8135,41,'string 5'),(8136,41,'string 5'),(8137,41,'string 5'),(8138,41,'string 5'),(8139,41,'string 5'),(8140,41,'string 5'),(8141,41,'string 5'),(8142,41,'string 5'),(8143,41,'string 5'),(8144,41,'string 5'),(8145,41,'string 5'),(8146,41,'string 5'),(8147,41,'string 5'),(8148,41,'string 5'),(8149,41,'string 5'),(8150,41,'string 5'),(8151,41,'string 5'),(8152,41,'string 5'),(8153,41,'string 5'),(8154,41,'string 5'),(8155,41,'string 5'),(8156,41,'string 5'),(8157,41,'string 5'),(8158,41,'string 5'),(8159,41,'string 5'),(8160,41,'string 5'),(8161,41,'string 5'),(8162,41,'string 5'),(8163,41,'string 5'),(8164,41,'string 5'),(8165,41,'string 5'),(8166,41,'string 5'),(8167,41,'string 5'),(8168,41,'string 5'),(8169,41,'string 5'),(8170,41,'string 5'),(8171,41,'string 5'),(8172,41,'string 5'),(8173,41,'string 5'),(8174,41,'string 5'),(8175,41,'string 5'),(8176,41,'string 5'),(8177,41,'string 5'),(8178,41,'string 5'),(8179,41,'string 5'),(8180,41,'string 5'),(8181,41,'string 5'),(8182,41,'string 5'),(8183,41,'string 5'),(8184,41,'string 5'),(8185,41,'string 5'),(8186,41,'string 5'),(8187,41,'string 5'),(8188,41,'string 5'),(8189,41,'string 5'),(8190,41,'string 5'),(8191,41,'string 5'),(8192,41,'string 5'),(8193,41,'string 5'),(8194,41,'string 5'),(8195,41,'string 5'),(8196,41,'string 5'),(8197,41,'string 5'),(8198,41,'string 5'),(8199,41,'string 5'),(8200,41,'string 5'),(8201,41,'string 5'),(8202,41,'string 5'),(8203,41,'string 5'),(8204,41,'string 5'),(8205,41,'string 5'),(8206,41,'string 5'),(8207,41,'string 5'),(8208,41,'string 5'),(8209,41,'string 5'),(8210,41,'string 5'),(8211,41,'string 5'),(8212,41,'string 5'),(8213,41,'string 5'),(8214,41,'string 5'),(8215,41,'string 5'),(8216,41,'string 5'),(8217,41,'string 5'),(8218,41,'string 5'),(8219,41,'string 5'),(8220,41,'string 5'),(8221,41,'string 5'),(8222,41,'string 5'),(8223,41,'string 5'),(8224,41,'string 5'),(8225,41,'string 5'),(8226,41,'string 5'),(8227,41,'string 5'),(8228,41,'string 5'),(8229,41,'string 5'),(8230,41,'string 5'),(8231,41,'string 5'),(8232,41,'string 5'),(8233,41,'string 5'),(8234,41,'string 5'),(8235,41,'string 5'),(8236,41,'string 5'),(8237,41,'string 5'),(8238,41,'string 5'),(8239,41,'string 5'),(8240,41,'string 5'),(8241,41,'string 5'),(8242,41,'string 5'),(8243,41,'string 5'),(8244,41,'string 5'),(8245,41,'string 5'),(8246,41,'string 5'),(8247,41,'string 5'),(8248,41,'string 5'),(8249,41,'string 5'),(8250,41,'string 5'),(8251,41,'string 5'),(8252,41,'string 5'),(8253,41,'string 5'),(8254,41,'string 5'),(8255,41,'string 5'),(8256,41,'string 5'),(8257,41,'string 5'),(8258,41,'string 5'),(8259,41,'string 5'),(8260,41,'string 5'),(8261,41,'string 5'),(8262,41,'string 5'),(8263,41,'string 5'),(8264,41,'string 5'),(8265,41,'string 5'),(8266,41,'string 5'),(8267,41,'string 5'),(8268,41,'string 5'),(8269,41,'string 5'),(8270,41,'string 5'),(8271,41,'string 5'),(8272,41,'string 5'),(8273,41,'string 5'),(8274,41,'string 5'),(8275,41,'string 5'),(8276,41,'string 5'),(8277,41,'string 5'),(8278,41,'string 5'),(8279,41,'string 5'),(8280,41,'string 5'),(8281,41,'string 5'),(8282,41,'string 5'),(8283,41,'string 5'),(8284,41,'string 5'),(8285,41,'string 5'),(8286,41,'string 5'),(8287,41,'string 5'),(8288,41,'string 5'),(8289,41,'string 5'),(8290,41,'string 5'),(8291,41,'string 5'),(8292,41,'string 5'),(8293,41,'string 5'),(8294,41,'string 5'),(8295,41,'string 5'),(8296,41,'string 5'),(8297,41,'string 5'),(8298,41,'string 5'),(8299,41,'string 5'),(8300,41,'string 5'),(8301,41,'string 5'),(8302,41,'string 5'),(8303,41,'string 5'),(8304,41,'string 5'),(8305,41,'string 5'),(8306,41,'string 5'),(8307,41,'string 5'),(8308,41,'string 5'),(8309,41,'string 5'),(8310,41,'string 5'),(8311,41,'string 5'),(8312,41,'string 5'),(8313,41,'string 5'),(8314,41,'string 5'),(8315,41,'string 5'),(8316,41,'string 5'),(8317,41,'string 5'),(8318,41,'string 5'),(8319,41,'string 5'),(8320,41,'string 5'),(8321,41,'string 5'),(8322,41,'string 5'),(8323,41,'string 5'),(8324,41,'string 5'),(8325,41,'string 5'),(8326,41,'string 5'),(8327,41,'string 5'),(8328,41,'string 5'),(8329,41,'string 5'),(8330,41,'string 5'),(8331,41,'string 5'),(8332,41,'string 5'),(8333,41,'string 5'),(8334,41,'string 5'),(8335,41,'string 5'),(8336,41,'string 5'),(8337,41,'string 5'),(8338,41,'string 5'),(8339,41,'string 5'),(8340,41,'string 5'),(8341,41,'string 5'),(8342,41,'string 5'),(8343,41,'string 5'),(8344,41,'string 5'),(8345,41,'string 5'),(8346,41,'string 5'),(8347,41,'string 5'),(8348,41,'string 5'),(8349,41,'string 5'),(8350,41,'string 5'),(8351,41,'string 5'),(8352,41,'string 5'),(8353,41,'string 5'),(8354,41,'string 5'),(8355,41,'string 5'),(8356,41,'string 5'),(8357,41,'string 5'),(8358,41,'string 5'),(8359,41,'string 5'),(8360,41,'string 5'),(8361,41,'string 5'),(8362,41,'string 5'),(8363,41,'string 5'),(8364,41,'string 5'),(8365,41,'string 5'),(8366,41,'string 5'),(8367,41,'string 5'),(8368,41,'string 5'),(8369,41,'string 5'),(8370,41,'string 5'),(8371,41,'string 5'),(8372,41,'string 5'),(8373,41,'string 5'),(8374,41,'string 5'),(8375,41,'string 5'),(8376,41,'string 5'),(8377,41,'string 5'),(8378,41,'string 5'),(8379,41,'string 5'),(8380,41,'string 5'),(8381,41,'string 5'),(8382,41,'string 5'),(8383,41,'string 5'),(8384,41,'string 5'),(8385,41,'string 5'),(8386,41,'string 5'),(8387,41,'string 5'),(8388,41,'string 5'),(8389,41,'string 5'),(8390,41,'string 5'),(8391,41,'string 5'),(8392,41,'string 5'),(8393,41,'string 5'),(8394,41,'string 5'),(8395,41,'string 5'),(8396,41,'string 5'),(8397,41,'string 5'),(8398,41,'string 5'),(8399,41,'string 5'),(8400,41,'string 5'),(8401,41,'string 5'),(8402,41,'string 5'),(8403,41,'string 5'),(8404,41,'string 5'),(8405,41,'string 5'),(8406,41,'string 5'),(8407,41,'string 5'),(8408,41,'string 5'),(8409,41,'string 5'),(8410,41,'string 5'),(8411,41,'string 5'),(8412,41,'string 5'),(8413,41,'string 5'),(8414,41,'string 5'),(8415,41,'string 5'),(8416,41,'string 5'),(8417,41,'string 5'),(8418,41,'string 5'),(8419,41,'string 5'),(8420,41,'string 5'),(8421,41,'string 5'),(8422,41,'string 5'),(8423,41,'string 5'),(8424,41,'string 5'),(8425,41,'string 5'),(8426,41,'string 5'),(8427,41,'string 5'),(8428,41,'string 5'),(8429,41,'string 5'),(8430,41,'string 5'),(8431,41,'string 5'),(8432,41,'string 5'),(8433,41,'string 5'),(8434,41,'string 5'),(8435,41,'string 5'),(8436,41,'string 5'),(8437,41,'string 5'),(8438,41,'string 5'),(8439,41,'string 5'),(8440,41,'string 5'),(8441,41,'string 5'),(8442,41,'string 5'),(8443,41,'string 5'),(8444,41,'string 5'),(8445,41,'string 5'),(8446,41,'string 5'),(8447,41,'string 5'),(8448,41,'string 5'),(8449,41,'string 5'),(8450,41,'string 5'),(8451,41,'string 5'),(8452,41,'string 5'),(8453,41,'string 5'),(8454,41,'string 5'),(8455,41,'string 5'),(8456,41,'string 5'),(8457,41,'string 5'),(8458,41,'string 5'),(8459,41,'string 5'),(8460,41,'string 5'),(8461,41,'string 5'),(8462,41,'string 5'),(8463,41,'string 5'),(8464,41,'string 5'),(8465,41,'string 5'),(8466,41,'string 5'),(8467,41,'string 5'),(8468,41,'string 5'),(8469,41,'string 5'),(8470,41,'string 5'),(8471,41,'string 5'),(8472,41,'string 5'),(8473,41,'string 5'),(8474,41,'string 5'),(8475,41,'string 5'),(8476,41,'string 5'),(8477,41,'string 5'),(8478,41,'string 5'),(8479,41,'string 5'),(8480,41,'string 5'),(8481,41,'string 5'),(8482,41,'string 5'),(8483,41,'string 5'),(8484,41,'string 5'),(8485,41,'string 5'),(8486,41,'string 5'),(8487,41,'string 5'),(8488,41,'string 5'),(8489,41,'string 5'),(8490,41,'string 5'),(8491,41,'string 5'),(8492,41,'string 5'),(8493,41,'string 5'),(8494,41,'string 5'),(8495,41,'string 5'),(8496,41,'string 5'),(8497,41,'string 5'),(8498,41,'string 5'),(8499,41,'string 5'),(8500,41,'string 5'),(8501,41,'string 5'),(8502,41,'string 5'),(8503,41,'string 5'),(8504,41,'string 5'),(8505,41,'string 5'),(8506,41,'string 5'),(8507,41,'string 5'),(8508,41,'string 5'),(8509,41,'string 5'),(8510,41,'string 5'),(8511,41,'string 5'),(8512,41,'string 5'),(8513,41,'string 5'),(8514,41,'string 5'),(8515,41,'string 5'),(8516,41,'string 5'),(8517,41,'string 5'),(8518,41,'string 5'),(8519,41,'string 5'),(8520,41,'string 5'),(8521,41,'string 5'),(8522,41,'string 5'),(8523,41,'string 5'),(8524,41,'string 5'),(8525,41,'string 5'),(8526,41,'string 5'),(8527,41,'string 5'),(8528,41,'string 5'),(8529,41,'string 5'),(8530,41,'string 5'),(8531,41,'string 5'),(8532,41,'string 5'),(8533,41,'string 5'),(8534,41,'string 5'),(8535,41,'string 5'),(8536,41,'string 5'),(8537,41,'string 5'),(8538,41,'string 5'),(8539,41,'string 5'),(8540,41,'string 5'),(8541,41,'string 5'),(8542,41,'string 5'),(8543,41,'string 5'),(8544,41,'string 5'),(8545,41,'string 5'),(8546,41,'string 5'),(8547,41,'string 5'),(8548,41,'string 5'),(8549,41,'string 5'),(8550,41,'string 5'),(8551,41,'string 5'),(8552,41,'string 5'),(8553,41,'string 5'),(8554,41,'string 5'),(8555,41,'string 5'),(8556,41,'string 5'),(8557,41,'string 5'),(8558,41,'string 5'),(8559,41,'string 5'),(8560,41,'string 5'),(8561,41,'string 5'),(8562,41,'string 5'),(8563,41,'string 5'),(8564,41,'string 5'),(8565,41,'string 5'),(8566,41,'string 5'),(8567,41,'string 5'),(8568,41,'string 5'),(8569,41,'string 5'),(8570,41,'string 5'),(8571,41,'string 5'),(8572,41,'string 5'),(8573,41,'string 5'),(8574,41,'string 5'),(8575,41,'string 5'),(8576,41,'string 5'),(8577,41,'string 5'),(8578,41,'string 5'),(8579,41,'string 5'),(8580,41,'string 5'),(8581,41,'string 5'),(8582,41,'string 5'),(8583,41,'string 5'),(8584,41,'string 5'),(8585,41,'string 5'),(8586,41,'string 5'),(8587,41,'string 5'),(8588,41,'string 5'),(8589,41,'string 5'),(8590,41,'string 5'),(8591,41,'string 5'),(8592,41,'string 5'),(8593,41,'string 5'),(8594,41,'string 5'),(8595,41,'string 5'),(8596,41,'string 5'),(8597,41,'string 5'),(8598,41,'string 5'),(8599,41,'string 5'),(8600,41,'string 5'),(8601,41,'string 5'),(8602,41,'string 5'),(8603,41,'string 5'),(8604,41,'string 5'),(8605,41,'string 5'),(8606,41,'string 5'),(8607,41,'string 5'),(8608,41,'string 5'),(8609,41,'string 5'),(8610,41,'string 5'),(8611,41,'string 5'),(8612,41,'string 5'),(8613,41,'string 5'),(8614,41,'string 5'),(8615,41,'string 5'),(8616,41,'string 5'),(8617,41,'string 5'),(8618,41,'string 5'),(8619,41,'string 5'),(8620,41,'string 5'),(8621,41,'string 5'),(8622,41,'string 5'),(8623,41,'string 5'),(8624,41,'string 5'),(8625,41,'string 5'),(8626,41,'string 5'),(8627,41,'string 5'),(8628,41,'string 5'),(8629,41,'string 5'),(8630,41,'string 5'),(8631,41,'string 5'),(8632,41,'string 5'),(8633,41,'string 5'),(8634,41,'string 5'),(8635,41,'string 5'),(8636,41,'string 5'),(8637,41,'string 5'),(8638,41,'string 5'),(8639,41,'string 5'),(8640,41,'string 5'),(8641,41,'string 5'),(8642,41,'string 5'),(8643,41,'string 5'),(8644,41,'string 5'),(8645,41,'string 5'),(8646,41,'string 5'),(8647,41,'string 5'),(8648,41,'string 5'),(8649,41,'string 5'),(8650,41,'string 5'),(8651,41,'string 5'),(8652,41,'string 5'),(8653,41,'string 5'),(8654,41,'string 5'),(8655,41,'string 5'),(8656,41,'string 5'),(8657,41,'string 5'),(8658,41,'string 5'),(8659,41,'string 5'),(8660,41,'string 5'),(8661,41,'string 5'),(8662,41,'string 5'),(8663,41,'string 5'),(8664,41,'string 5'),(8665,41,'string 5'),(8666,41,'string 5'),(8667,41,'string 5'),(8668,41,'string 5'),(8669,41,'string 5'),(8670,41,'string 5'),(8671,41,'string 5'),(8672,41,'string 5'),(8673,41,'string 5'),(8674,41,'string 5'),(8675,41,'string 5'),(8676,41,'string 5'),(8677,41,'string 5'),(8678,41,'string 5'),(8679,41,'string 5'),(8680,41,'string 5'),(8681,41,'string 5'),(8682,41,'string 5'),(8683,41,'string 5'),(8684,41,'string 5'),(8685,41,'string 5'),(8686,41,'string 5'),(8687,41,'string 5'),(8688,41,'string 5'),(8689,41,'string 5'),(8690,41,'string 5'),(8691,41,'string 5'),(8692,41,'string 5'),(8693,41,'string 5'),(8694,41,'string 5'),(8695,41,'string 5'),(8696,41,'string 5'),(8697,41,'string 5'),(8698,41,'string 5'),(8699,41,'string 5'),(8700,41,'string 5'),(8701,41,'string 5'),(8702,41,'string 5'),(8703,41,'string 5'),(8704,41,'string 5'),(8705,41,'string 5'),(8706,41,'string 5'),(8707,41,'string 5'),(8708,41,'string 5'),(8709,41,'string 5'),(8710,41,'string 5'),(8711,41,'string 5'),(8712,41,'string 5'),(8713,41,'string 5'),(8714,41,'string 5'),(8715,41,'string 5'),(8716,41,'string 5'),(8717,41,'string 5'),(8718,41,'string 5'),(8719,41,'string 5'),(8720,41,'string 5'),(8721,41,'string 5'),(8722,41,'string 5'),(8723,41,'string 5'),(8724,41,'string 5'),(8725,41,'string 5'),(8726,41,'string 5'),(8727,41,'string 5'),(8728,41,'string 5'),(8729,41,'string 5'),(8730,41,'string 5'),(8731,41,'string 5'),(8732,41,'string 5'),(8733,41,'string 5'),(8734,41,'string 5'),(8735,41,'string 5'),(8736,41,'string 5'),(8737,41,'string 5'),(8738,41,'string 5'),(8739,41,'string 5'),(8740,41,'string 5'),(8741,41,'string 5'),(8742,41,'string 5'),(8743,41,'string 5'),(8744,41,'string 5'),(8745,41,'string 5'),(8746,41,'string 5'),(8747,41,'string 5'),(8748,41,'string 5'),(8749,41,'string 5'),(8750,41,'string 5'),(8751,41,'string 5'),(8752,41,'string 5'),(8753,41,'string 5'),(8754,41,'string 5'),(8755,41,'string 5'),(8756,41,'string 5'),(8757,41,'string 5'),(8758,41,'string 5'),(8759,41,'string 5'),(8760,41,'string 5'),(8761,41,'string 5'),(8762,41,'string 5'),(8763,41,'string 5'),(8764,41,'string 5'),(8765,41,'string 5'),(8766,41,'string 5'),(8767,41,'string 5'),(8768,41,'string 5'),(8769,41,'string 5'),(8770,41,'string 5'),(8771,41,'string 5'),(8772,41,'string 5'),(8773,41,'string 5'),(8774,41,'string 5'),(8775,41,'string 5'),(8776,41,'string 5'),(8777,41,'string 5'),(8778,41,'string 5'),(8779,41,'string 5'),(8780,41,'string 5'),(8781,41,'string 5'),(8782,41,'string 5'),(8783,41,'string 5'),(8784,41,'string 5'),(8785,41,'string 5'),(8786,41,'string 5'),(8787,41,'string 5'),(8788,41,'string 5'),(8789,41,'string 5'),(8790,41,'string 5'),(8791,41,'string 5'),(8792,41,'string 5'),(8793,41,'string 5'),(8794,41,'string 5'),(8795,41,'string 5'),(8796,41,'string 5'),(8797,41,'string 5'),(8798,41,'string 5'),(8799,41,'string 5'),(8800,41,'string 5'),(8801,41,'string 5'),(8802,41,'string 5'),(8803,41,'string 5'),(8804,41,'string 5'),(8805,41,'string 5'),(8806,41,'string 5'),(8807,41,'string 5'),(8808,41,'string 5'),(8809,41,'string 5'),(8810,41,'string 5'),(8811,41,'string 5'),(8812,41,'string 5'),(8813,41,'string 5'),(8814,41,'string 5'),(8815,41,'string 5'),(8816,41,'string 5'),(8817,41,'string 5'),(8818,41,'string 5'),(8819,41,'string 5'),(8820,41,'string 5'),(8821,41,'string 5'),(8822,41,'string 5'),(8823,41,'string 5'),(8824,41,'string 5'),(8825,41,'string 5'),(8826,41,'string 5'),(8827,41,'string 5'),(8828,41,'string 5'),(8829,41,'string 5'),(8830,41,'string 5'),(8831,41,'string 5'),(8832,41,'string 5'),(8833,41,'string 5'),(8834,41,'string 5'),(8835,41,'string 5'),(8836,41,'string 5'),(8837,41,'string 5'),(8838,41,'string 5'),(8839,41,'string 5'),(8840,41,'string 5'),(8841,41,'string 5'),(8842,41,'string 5'),(8843,41,'string 5'),(8844,41,'string 5'),(8845,41,'string 5'),(8846,41,'string 5'),(8847,41,'string 5'),(8848,41,'string 5'),(8849,41,'string 5'),(8850,41,'string 5'),(8851,41,'string 5'),(8852,41,'string 5'),(8853,41,'string 5'),(8854,41,'string 5'),(8855,41,'string 5'),(8856,41,'string 5'),(8857,41,'string 5'),(8858,41,'string 5'),(8859,41,'string 5'),(8860,41,'string 5'),(8861,41,'string 5'),(8862,41,'string 5'),(8863,41,'string 5'),(8864,41,'string 5'),(8865,41,'string 5'),(8866,41,'string 5'),(8867,41,'string 5'),(8868,41,'string 5'),(8869,41,'string 5'),(8870,41,'string 5'),(8871,41,'string 5'),(8872,41,'string 5'),(8873,41,'string 5'),(8874,41,'string 5'),(8875,41,'string 5'),(8876,41,'string 5'),(8877,41,'string 5'),(8878,41,'string 5'),(8879,41,'string 5'),(8880,41,'string 5'),(8881,41,'string 5'),(8882,41,'string 5'),(8883,41,'string 5'),(8884,41,'string 5'),(8885,41,'string 5'),(8886,41,'string 5'),(8887,41,'string 5'),(8888,41,'string 5'),(8889,41,'string 5'),(8890,41,'string 5'),(8891,41,'string 5'),(8892,41,'string 5'),(8893,41,'string 5'),(8894,41,'string 5'),(8895,41,'string 5'),(8896,41,'string 5'),(8897,41,'string 5'),(8898,41,'string 5'),(8899,41,'string 5'),(8900,41,'string 5'),(8901,41,'string 5'),(8902,41,'string 5'),(8903,41,'string 5'),(8904,41,'string 5'),(8905,41,'string 5'),(8906,41,'string 5'),(8907,41,'string 5'),(8908,41,'string 5'),(8909,41,'string 5'),(8910,41,'string 5'),(8911,41,'string 5'),(8912,41,'string 5'),(8913,41,'string 5'),(8914,41,'string 5'),(8915,41,'string 5'),(8916,41,'string 5'),(8917,41,'string 5'),(8918,41,'string 5'),(8919,41,'string 5'),(8920,41,'string 5'),(8921,41,'string 5'),(8922,41,'string 5'),(8923,41,'string 5'),(8924,41,'string 5'),(8925,41,'string 5'),(8926,41,'string 5'),(8927,41,'string 5'),(8928,41,'string 5'),(8929,41,'string 5'),(8930,41,'string 5'),(8931,41,'string 5'),(8932,41,'string 5'),(8933,41,'string 5'),(8934,41,'string 5'),(8935,41,'string 5'),(8936,41,'string 5'),(8937,41,'string 5'),(8938,41,'string 5'),(8939,41,'string 5'),(8940,41,'string 5'),(8941,41,'string 5'),(8942,41,'string 5'),(8943,41,'string 5'),(8944,41,'string 5'),(8945,41,'string 5'),(8946,41,'string 5'),(8947,41,'string 5'),(8948,41,'string 5'),(8949,41,'string 5'),(8950,41,'string 5'),(8951,41,'string 5'),(8952,41,'string 5'),(8953,41,'string 5'),(8954,41,'string 5'),(8955,41,'string 5'),(8956,41,'string 5'),(8957,41,'string 5'),(8958,41,'string 5'),(8959,41,'string 5'),(8960,41,'string 5'),(8961,41,'string 5'),(8962,41,'string 5'),(8963,41,'string 5'),(8964,41,'string 5'),(8965,41,'string 5'),(8966,41,'string 5'),(8967,41,'string 5'),(8968,41,'string 5'),(8969,41,'string 5'),(8970,41,'string 5'),(8971,41,'string 5'),(8972,41,'string 5'),(8973,41,'string 5'),(8974,41,'string 5'),(8975,41,'string 5'),(8976,41,'string 5'),(8977,41,'string 5'),(8978,41,'string 5'),(8979,41,'string 5'),(8980,41,'string 5'),(8981,41,'string 5'),(8982,41,'string 5'),(8983,41,'string 5'),(8984,41,'string 5'),(8985,41,'string 5'),(8986,41,'string 5'),(8987,41,'string 5'),(8988,41,'string 5'),(8989,41,'string 5'),(8990,41,'string 5'),(8991,41,'string 5'),(8992,41,'string 5'),(8993,41,'string 5'),(8994,41,'string 5'),(8995,41,'string 5'),(8996,41,'string 5'),(8997,41,'string 5'),(8998,41,'string 5'),(8999,41,'string 5'),(9000,41,'string 5'),(9001,41,'string 5'),(9002,41,'string 5'),(9003,41,'string 5'),(9004,41,'string 5'),(9005,41,'string 5'),(9006,41,'string 5'),(9007,41,'string 5'),(9008,41,'string 5'),(9009,41,'string 5'),(9010,41,'string 5'),(9011,41,'string 5'),(9012,41,'string 5'),(9013,41,'string 5'),(9014,41,'string 5'),(9015,41,'string 5'),(9016,41,'string 5'),(9017,41,'string 5'),(9018,41,'string 5'),(9019,41,'string 5'),(9020,41,'string 5'),(9021,41,'string 5'),(9022,41,'string 5'),(9023,41,'string 5'),(9024,41,'string 5'),(9025,41,'string 5'),(9026,41,'string 5'),(9027,41,'string 5'),(9028,41,'string 5'),(9029,41,'string 5'),(9030,41,'string 5'),(9031,41,'string 5'),(9032,41,'string 5'),(9033,41,'string 5'),(9034,41,'string 5'),(9035,41,'string 5'),(9036,41,'string 5'),(9037,41,'string 5'),(9038,41,'string 5'),(9039,41,'string 5'),(9040,41,'string 5'),(9041,41,'string 5'),(9042,41,'string 5'),(9043,41,'string 5'),(9044,41,'string 5'),(9045,41,'string 5'),(9046,41,'string 5'),(9047,41,'string 5'),(9048,41,'string 5'),(9049,41,'string 5'),(9050,41,'string 5'),(9051,41,'string 5'),(9052,41,'string 5'),(9053,41,'string 5'),(9054,41,'string 5'),(9055,41,'string 5'),(9056,41,'string 5'),(9057,41,'string 5'),(9058,41,'string 5'),(9059,41,'string 5'),(9060,41,'string 5'),(9061,41,'string 5'),(9062,41,'string 5'),(9063,41,'string 5'),(9064,41,'string 5'),(9065,41,'string 5'),(9066,41,'string 5'),(9067,41,'string 5'),(9068,41,'string 5'),(9069,41,'string 5'),(9070,41,'string 5'),(9071,41,'string 5'),(9072,41,'string 5'),(9073,41,'string 5'),(9074,41,'string 5'),(9075,41,'string 5'),(9076,41,'string 5'),(9077,41,'string 5'),(9078,41,'string 5'),(9079,41,'string 5'),(9080,41,'string 5'),(9081,41,'string 5'),(9082,41,'string 5'),(9083,41,'string 5'),(9084,41,'string 5'),(9085,41,'string 5'),(9086,41,'string 5'),(9087,41,'string 5'),(9088,41,'string 5'),(9089,41,'string 5'),(9090,41,'string 5'),(9091,41,'string 5'),(9092,41,'string 5'),(9093,41,'string 5'),(9094,41,'string 5'),(9095,41,'string 5'),(9096,41,'string 5'),(9097,41,'string 5'),(9098,41,'string 5'),(9099,41,'string 5'),(9100,41,'string 5'),(9101,41,'string 5'),(9102,41,'string 5'),(9103,41,'string 5'),(9104,41,'string 5'),(9105,41,'string 5'),(9106,41,'string 5'),(9107,41,'string 5'),(9108,41,'string 5'),(9109,41,'string 5'),(9110,41,'string 5'),(9111,41,'string 5'),(9112,41,'string 5'),(9113,41,'string 5'),(9114,41,'string 5'),(9115,41,'string 5'),(9116,41,'string 5'),(9117,41,'string 5'),(9118,41,'string 5'),(9119,41,'string 5'),(9120,41,'string 5'),(9121,41,'string 5'),(9122,41,'string 5'),(9123,41,'string 5'),(9124,41,'string 5'),(9125,41,'string 5'),(9126,41,'string 5'),(9127,41,'string 5'),(9128,41,'string 5'),(9129,41,'string 5'),(9130,41,'string 5'),(9131,41,'string 5'),(9132,41,'string 5'),(9133,41,'string 5'),(9134,41,'string 5'),(9135,41,'string 5'),(9136,41,'string 5'),(9137,41,'string 5'),(9138,41,'string 5'),(9139,41,'string 5'),(9140,41,'string 5'),(9141,41,'string 5'),(9142,41,'string 5'),(9143,41,'string 5'),(9144,41,'string 5'),(9145,41,'string 5'),(9146,41,'string 5'),(9147,41,'string 5'),(9148,41,'string 5'),(9149,41,'string 5'),(9150,41,'string 5'),(9151,41,'string 5'),(9152,41,'string 5'),(9153,41,'string 5'),(9154,41,'string 5'),(9155,41,'string 5'),(9156,41,'string 5'),(9157,41,'string 5'),(9158,41,'string 5'),(9159,41,'string 5'),(9160,41,'string 5'),(9161,41,'string 5'),(9162,41,'string 5'),(9163,41,'string 5'),(9164,41,'string 5'),(9165,41,'string 5'),(9166,41,'string 5'),(9167,41,'string 5'),(9168,41,'string 5'),(9169,41,'string 5'),(9170,41,'string 5'),(9171,41,'string 5'),(9172,41,'string 5'),(9173,41,'string 5'),(9174,41,'string 5'),(9175,41,'string 5'),(9176,41,'string 5'),(9177,41,'string 5'),(9178,41,'string 5'),(9179,41,'string 5'),(9180,41,'string 5'),(9181,41,'string 5'),(9182,41,'string 5'),(9183,41,'string 5'),(9184,41,'string 5'),(9185,41,'string 5'),(9186,41,'string 5'),(9187,41,'string 5'),(9188,41,'string 5'),(9189,41,'string 5'),(9190,41,'string 5'),(9191,41,'string 5'),(9192,41,'string 5'),(9193,41,'string 5'),(9194,41,'string 5'),(9195,41,'string 5'),(9196,41,'string 5'),(9197,41,'string 5'),(9198,41,'string 5'),(9199,41,'string 5'),(9200,41,'string 5'),(9201,41,'string 5'),(9202,41,'string 5'),(9203,41,'string 5'),(9204,41,'string 5'),(9205,41,'string 5'),(9206,41,'string 5'),(9207,41,'string 5'),(9208,41,'string 5'),(9209,41,'string 5'),(9210,41,'string 5'),(9211,41,'string 5'),(9212,41,'string 5'),(9213,41,'string 5'),(9214,41,'string 5'),(9215,41,'string 5'),(9216,41,'string 5'),(9217,41,'string 5'),(9218,41,'string 5'),(9219,41,'string 5'),(9220,41,'string 5'),(9221,41,'string 5'),(9222,41,'string 5'),(9223,41,'string 5'),(9224,41,'string 5'),(9225,41,'string 5'),(9226,41,'string 5'),(9227,41,'string 5'),(9228,41,'string 5'),(9229,41,'string 5'),(9230,41,'string 5'),(9231,41,'string 5'),(9232,41,'string 5'),(9233,41,'string 5'),(9234,41,'string 5'),(9235,41,'string 5'),(9236,41,'string 5'),(9237,41,'string 5'),(9238,41,'string 5'),(9239,41,'string 5'),(9240,41,'string 5'),(9241,41,'string 5'),(9242,41,'string 5'),(9243,41,'string 5'),(9244,41,'string 5'),(9245,41,'string 5'),(9246,41,'string 5'),(9247,41,'string 5'),(9248,41,'string 5'),(9249,41,'string 5'),(9250,41,'string 5'),(9251,41,'string 5'),(9252,41,'string 5'),(9253,41,'string 5'),(9254,41,'string 5'),(9255,41,'string 5'),(9256,41,'string 5'),(9257,41,'string 5'),(9258,41,'string 5'),(9259,41,'string 5'),(9260,41,'string 5'),(9261,41,'string 5'),(9262,41,'string 5'),(9263,41,'string 5'),(9264,41,'string 5'),(9265,41,'string 5'),(9266,41,'string 5'),(9267,41,'string 5'),(9268,41,'string 5'),(9269,41,'string 5'),(9270,41,'string 5'),(9271,41,'string 5'),(9272,41,'string 5'),(9273,41,'string 5'),(9274,41,'string 5'),(9275,41,'string 5'),(9276,41,'string 5'),(9277,41,'string 5'),(9278,41,'string 5'),(9279,41,'string 5'),(9280,41,'string 5'),(9281,41,'string 5'),(9282,41,'string 5'),(9283,41,'string 5'),(9284,41,'string 5'),(9285,41,'string 5'),(9286,41,'string 5'),(9287,41,'string 5'),(9288,41,'string 5'),(9289,41,'string 5'),(9290,41,'string 5'),(9291,41,'string 5'),(9292,41,'string 5'),(9293,41,'string 5'),(9294,41,'string 5'),(9295,41,'string 5'),(9296,41,'string 5'),(9297,41,'string 5'),(9298,41,'string 5'),(9299,41,'string 5'),(9300,41,'string 5'),(9301,41,'string 5'),(9302,41,'string 5'),(9303,41,'string 5'),(9304,41,'string 5'),(9305,41,'string 5'),(9306,41,'string 5'),(9307,41,'string 5'),(9308,41,'string 5'),(9309,41,'string 5'),(9310,41,'string 5'),(9311,41,'string 5'),(9312,41,'string 5'),(9313,41,'string 5'),(9314,41,'string 5'),(9315,41,'string 5'),(9316,41,'string 5'),(9317,41,'string 5'),(9318,41,'string 5'),(9319,41,'string 5'),(9320,41,'string 5'),(9321,41,'string 5'),(9322,41,'string 5'),(9323,41,'string 5'),(9324,41,'string 5'),(9325,41,'string 5'),(9326,41,'string 5'),(9327,41,'string 5'),(9328,41,'string 5'),(9329,41,'string 5'),(9330,41,'string 5'),(9331,41,'string 5'),(9332,41,'string 5'),(9333,41,'string 5'),(9334,41,'string 5'),(9335,41,'string 5'),(9336,41,'string 5'),(9337,41,'string 5'),(9338,41,'string 5'),(9339,41,'string 5'),(9340,41,'string 5'),(9341,41,'string 5'),(9342,41,'string 5'),(9343,41,'string 5'),(9344,41,'string 5'),(9345,41,'string 5'),(9346,41,'string 5'),(9347,41,'string 5'),(9348,41,'string 5'),(9349,41,'string 5'),(9350,41,'string 5'),(9351,41,'string 5'),(9352,41,'string 5'),(9353,41,'string 5'),(9354,41,'string 5'),(9355,41,'string 5'),(9356,41,'string 5'),(9357,41,'string 5'),(9358,41,'string 5'),(9359,41,'string 5'),(9360,41,'string 5'),(9361,41,'string 5'),(9362,41,'string 5'),(9363,41,'string 5'),(9364,41,'string 5'),(9365,41,'string 5'),(9366,41,'string 5'),(9367,41,'string 5'),(9368,41,'string 5'),(9369,41,'string 5'),(9370,41,'string 5'),(9371,41,'string 5'),(9372,41,'string 5'),(9373,41,'string 5'),(9374,41,'string 5'),(9375,41,'string 5'),(9376,41,'string 5'),(9377,41,'string 5'),(9378,41,'string 5'),(9379,41,'string 5'),(9380,41,'string 5'),(9381,41,'string 5'),(9382,41,'string 5'),(9383,41,'string 5'),(9384,41,'string 5'),(9385,41,'string 5'),(9386,41,'string 5'),(9387,41,'string 5'),(9388,41,'string 5'),(9389,41,'string 5'),(9390,41,'string 5'),(9391,41,'string 5'),(9392,41,'string 5'),(9393,41,'string 5'),(9394,41,'string 5'),(9395,41,'string 5'),(9396,41,'string 5'),(9397,41,'string 5'),(9398,41,'string 5'),(9399,41,'string 5'),(9400,41,'string 5'),(9401,41,'string 5'),(9402,41,'string 5'),(9403,41,'string 5'),(9404,41,'string 5'),(9405,41,'string 5'),(9406,41,'string 5'),(9407,41,'string 5'),(9408,41,'string 5'),(9409,41,'string 5'),(9410,41,'string 5'),(9411,41,'string 5'),(9412,41,'string 5'),(9413,41,'string 5'),(9414,41,'string 5'),(9415,41,'string 5'),(9416,41,'string 5'),(9417,41,'string 5'),(9418,41,'string 5'),(9419,41,'string 5'),(9420,41,'string 5'),(9421,41,'string 5'),(9422,41,'string 5'),(9423,41,'string 5'),(9424,41,'string 5'),(9425,41,'string 5'),(9426,41,'string 5'),(9427,41,'string 5'),(9428,41,'string 5'),(9429,41,'string 5'),(9430,41,'string 5'),(9431,41,'string 5'),(9432,41,'string 5'),(9433,41,'string 5'),(9434,41,'string 5'),(9435,41,'string 5'),(9436,41,'string 5'),(9437,41,'string 5'),(9438,41,'string 5'),(9439,41,'string 5'),(9440,41,'string 5'),(9441,41,'string 5'),(9442,41,'string 5'),(9443,41,'string 5'),(9444,41,'string 5'),(9445,41,'string 5'),(9446,41,'string 5'),(9447,41,'string 5'),(9448,41,'string 5'),(9449,41,'string 5'),(9450,41,'string 5'),(9451,41,'string 5'),(9452,41,'string 5'),(9453,41,'string 5'),(9454,41,'string 5'),(9455,41,'string 5'),(9456,41,'string 5'),(9457,41,'string 5'),(9458,41,'string 5'),(9459,41,'string 5'),(9460,41,'string 5'),(9461,41,'string 5'),(9462,41,'string 5'),(9463,41,'string 5'),(9464,41,'string 5'),(9465,41,'string 5'),(9466,41,'string 5'),(9467,41,'string 5'),(9468,41,'string 5'),(9469,41,'string 5'),(9470,41,'string 5'),(9471,41,'string 5'),(9472,41,'string 5'),(9473,41,'string 5'),(9474,41,'string 5'),(9475,41,'string 5'),(9476,41,'string 5'),(9477,41,'string 5'),(9478,41,'string 5'),(9479,41,'string 5'),(9480,41,'string 5'),(9481,41,'string 5'),(9482,41,'string 5'),(9483,41,'string 5'),(9484,41,'string 5'),(9485,41,'string 5'),(9486,41,'string 5'),(9487,41,'string 5'),(9488,41,'string 5'),(9489,41,'string 5'),(9490,41,'string 5'),(9491,41,'string 5'),(9492,41,'string 5'),(9493,41,'string 5'),(9494,41,'string 5'),(9495,41,'string 5'),(9496,41,'string 5'),(9497,41,'string 5'),(9498,41,'string 5'),(9499,41,'string 5'),(9500,41,'string 5'),(9501,41,'string 5'),(9502,41,'string 5'),(9503,41,'string 5'),(9504,41,'string 5'),(9505,41,'string 5'),(9506,41,'string 5'),(9507,41,'string 5'),(9508,41,'string 5'),(9509,41,'string 5'),(9510,41,'string 5'),(9511,41,'string 5'),(9512,41,'string 5'),(9513,41,'string 5'),(9514,41,'string 5'),(9515,41,'string 5'),(9516,41,'string 5'),(9517,41,'string 5'),(9518,41,'string 5'),(9519,41,'string 5'),(9520,41,'string 5'),(9521,41,'string 5'),(9522,41,'string 5'),(9523,41,'string 5'),(9524,41,'string 5'),(9525,41,'string 5'),(9526,41,'string 5'),(9527,41,'string 5'),(9528,41,'string 5'),(9529,41,'string 5'),(9530,41,'string 5'),(9531,41,'string 5'),(9532,41,'string 5'),(9533,41,'string 5'),(9534,41,'string 5'),(9535,41,'string 5'),(9536,41,'string 5'),(9537,41,'string 5'),(9538,41,'string 5'),(9539,41,'string 5'),(9540,41,'string 5'),(9541,41,'string 5'),(9542,41,'string 5'),(9543,41,'string 5'),(9544,41,'string 5'),(9545,41,'string 5'),(9546,41,'string 5'),(9547,41,'string 5'),(9548,41,'string 5'),(9549,41,'string 5'),(9550,41,'string 5'),(9551,41,'string 5'),(9552,41,'string 5'),(9553,41,'string 5'),(9554,41,'string 5'),(9555,41,'string 5'),(9556,41,'string 5'),(9557,41,'string 5'),(9558,41,'string 5'),(9559,41,'string 5'),(9560,41,'string 5'),(9561,41,'string 5'),(9562,41,'string 5'),(9563,41,'string 5'),(9564,41,'string 5'),(9565,41,'string 5'),(9566,41,'string 5'),(9567,41,'string 5'),(9568,41,'string 5'),(9569,41,'string 5'),(9570,41,'string 5'),(9571,41,'string 5'),(9572,41,'string 5'),(9573,41,'string 5'),(9574,41,'string 5'),(9575,41,'string 5'),(9576,41,'string 5'),(9577,41,'string 5'),(9578,41,'string 5'),(9579,41,'string 5'),(9580,41,'string 5'),(9581,41,'string 5'),(9582,41,'string 5'),(9583,41,'string 5'),(9584,41,'string 5'),(9585,41,'string 5'),(9586,41,'string 5'),(9587,41,'string 5'),(9588,41,'string 5'),(9589,41,'string 5'),(9590,41,'string 5'),(9591,41,'string 5'),(9592,41,'string 5'),(9593,41,'string 5'),(9594,41,'string 5'),(9595,41,'string 5'),(9596,41,'string 5'),(9597,41,'string 5'),(9598,41,'string 5'),(9599,41,'string 5'),(9600,41,'string 5'),(9601,41,'string 5'),(9602,41,'string 5'),(9603,41,'string 5'),(9604,41,'string 5'),(9605,41,'string 5'),(9606,41,'string 5'),(9607,41,'string 5'),(9608,41,'string 5'),(9609,41,'string 5'),(9610,41,'string 5'),(9611,41,'string 5'),(9612,41,'string 5'),(9613,41,'string 5'),(9614,41,'string 5'),(9615,41,'string 5'),(9616,41,'string 5'),(9617,41,'string 5'),(9618,41,'string 5'),(9619,41,'string 5'),(9620,41,'string 5'),(9621,41,'string 5'),(9622,41,'string 5'),(9623,41,'string 5'),(9624,41,'string 5'),(9625,41,'string 5'),(9626,41,'string 5'),(9627,41,'string 5'),(9628,41,'string 5'),(9629,41,'string 5'),(9630,41,'string 5'),(9631,41,'string 5'),(9632,41,'string 5'),(9633,41,'string 5'),(9634,41,'string 5'),(9635,41,'string 5'),(9636,41,'string 5'),(9637,41,'string 5'),(9638,41,'string 5'),(9639,41,'string 5'),(9640,41,'string 5'),(9641,41,'string 5'),(9642,41,'string 5'),(9643,41,'string 5'),(9644,41,'string 5'),(9645,41,'string 5'),(9646,41,'string 5'),(9647,41,'string 5'),(9648,41,'string 5'),(9649,41,'string 5'),(9650,41,'string 5'),(9651,41,'string 5'),(9652,41,'string 5'),(9653,41,'string 5'),(9654,41,'string 5'),(9655,41,'string 5'),(9656,41,'string 5'),(9657,41,'string 5'),(9658,41,'string 5'),(9659,41,'string 5'),(9660,41,'string 5'),(9661,41,'string 5'),(9662,41,'string 5'),(9663,41,'string 5'),(9664,41,'string 5'),(9665,41,'string 5'),(9666,41,'string 5'),(9667,41,'string 5'),(9668,41,'string 5'),(9669,41,'string 5'),(9670,41,'string 5'),(9671,41,'string 5'),(9672,41,'string 5'),(9673,41,'string 5'),(9674,41,'string 5'),(9675,41,'string 5'),(9676,41,'string 5'),(9677,41,'string 5'),(9678,41,'string 5'),(9679,41,'string 5'),(9680,41,'string 5'),(9681,41,'string 5'),(9682,41,'string 5'),(9683,41,'string 5'),(9684,41,'string 5'),(9685,41,'string 5'),(9686,41,'string 5'),(9687,41,'string 5'),(9688,41,'string 5'),(9689,41,'string 5'),(9690,41,'string 5'),(9691,41,'string 5'),(9692,41,'string 5'),(9693,41,'string 5'),(9694,41,'string 5'),(9695,41,'string 5'),(9696,41,'string 5'),(9697,41,'string 5'),(9698,41,'string 5'),(9699,41,'string 5'),(9700,41,'string 5'),(9701,41,'string 5'),(9702,41,'string 5'),(9703,41,'string 5'),(9704,41,'string 5'),(9705,41,'string 5'),(9706,41,'string 5'),(9707,41,'string 5'),(9708,41,'string 5'),(9709,41,'string 5'),(9710,41,'string 5'),(9711,41,'string 5'),(9712,41,'string 5'),(9713,41,'string 5'),(9714,41,'string 5'),(9715,41,'string 5'),(9716,41,'string 5'),(9717,41,'string 5'),(9718,41,'string 5'),(9719,41,'string 5'),(9720,41,'string 5'),(9721,41,'string 5'),(9722,41,'string 5'),(9723,41,'string 5'),(9724,41,'string 5'),(9725,41,'string 5'),(9726,41,'string 5'),(9727,41,'string 5'),(9728,41,'string 5'),(9729,41,'string 5'),(9730,41,'string 5'),(9731,41,'string 5'),(9732,41,'string 5'),(9733,41,'string 5'),(9734,41,'string 5'),(9735,41,'string 5'),(9736,41,'string 5'),(9737,41,'string 5'),(9738,41,'string 5'),(9739,41,'string 5'),(9740,41,'string 5'),(9741,41,'string 5'),(9742,41,'string 5'),(9743,41,'string 5'),(9744,41,'string 5'),(9745,41,'string 5'),(9746,41,'string 5'),(9747,41,'string 5'),(9748,41,'string 5'),(9749,41,'string 5'),(9750,41,'string 5'),(9751,41,'string 5'),(9752,41,'string 5'),(9753,41,'string 5'),(9754,41,'string 5'),(9755,41,'string 5'),(9756,41,'string 5'),(9757,41,'string 5'),(9758,41,'string 5'),(9759,41,'string 5'),(9760,41,'string 5'),(9761,41,'string 5'),(9762,41,'string 5'),(9763,41,'string 5'),(9764,41,'string 5'),(9765,41,'string 5'),(9766,41,'string 5'),(9767,41,'string 5'),(9768,41,'string 5'),(9769,41,'string 5'),(9770,41,'string 5'),(9771,41,'string 5'),(9772,41,'string 5'),(9773,41,'string 5'),(9774,41,'string 5'),(9775,41,'string 5'),(9776,41,'string 5'),(9777,41,'string 5'),(9778,41,'string 5'),(9779,41,'string 5'),(9780,41,'string 5'),(9781,41,'string 5'),(9782,41,'string 5'),(9783,41,'string 5'),(9784,41,'string 5'),(9785,41,'string 5'),(9786,41,'string 5'),(9787,41,'string 5'),(9788,41,'string 5'),(9789,41,'string 5'),(9790,41,'string 5'),(9791,41,'string 5'),(9792,41,'string 5'),(9793,41,'string 5'),(9794,41,'string 5'),(9795,41,'string 5'),(9796,41,'string 5'),(9797,41,'string 5'),(9798,41,'string 5'),(9799,41,'string 5'),(9800,41,'string 5'),(9801,41,'string 5'),(9802,41,'string 5'),(9803,41,'string 5'),(9804,41,'string 5'),(9805,41,'string 5'),(9806,41,'string 5'),(9807,41,'string 5'),(9808,41,'string 5'),(9809,41,'string 5'),(9810,41,'string 5'),(9811,41,'string 5'),(9812,41,'string 5'),(9813,41,'string 5'),(9814,41,'string 5'),(9815,41,'string 5'),(9816,41,'string 5'),(9817,41,'string 5'),(9818,41,'string 5'),(9819,41,'string 5'),(9820,41,'string 5'),(9821,41,'string 5'),(9822,41,'string 5'),(9823,41,'string 5'),(9824,41,'string 5'),(9825,41,'string 5'),(9826,41,'string 5'),(9827,41,'string 5'),(9828,41,'string 5'),(9829,41,'string 5'),(9830,41,'string 5'),(9831,41,'string 5'),(9832,41,'string 5'),(9833,41,'string 5'),(9834,41,'string 5'),(9835,41,'string 5'),(9836,41,'string 5'),(9837,41,'string 5'),(9838,41,'string 5'),(9839,41,'string 5'),(9840,41,'string 5'),(9841,41,'string 5'),(9842,41,'string 5'),(9843,41,'string 5'),(9844,41,'string 5'),(9845,41,'string 5'),(9846,41,'string 5'),(9847,41,'string 5'),(9848,41,'string 5'),(9849,41,'string 5'),(9850,41,'string 5'),(9851,41,'string 5'),(9852,41,'string 5'),(9853,41,'string 5'),(9854,41,'string 5'),(9855,41,'string 5'),(9856,41,'string 5'),(9857,41,'string 5'),(9858,41,'string 5'),(9859,41,'string 5'),(9860,41,'string 5'),(9861,41,'string 5'),(9862,41,'string 5'),(9863,41,'string 5'),(9864,41,'string 5'),(9865,41,'string 5'),(9866,41,'string 5'),(9867,41,'string 5'),(9868,41,'string 5'),(9869,41,'string 5'),(9870,41,'string 5'),(9871,41,'string 5'),(9872,41,'string 5'),(9873,41,'string 5'),(9874,41,'string 5'),(9875,41,'string 5'),(9876,41,'string 5'),(9877,41,'string 5'),(9878,41,'string 5'),(9879,41,'string 5'),(9880,41,'string 5'),(9881,41,'string 5'),(9882,41,'string 5'),(9883,41,'string 5'),(9884,41,'string 5'),(9885,41,'string 5'),(9886,41,'string 5'),(9887,41,'string 5'),(9888,41,'string 5'),(9889,41,'string 5'),(9890,41,'string 5'),(9891,41,'string 5'),(9892,41,'string 5'),(9893,41,'string 5'),(9894,41,'string 5'),(9895,41,'string 5'),(9896,41,'string 5'),(9897,41,'string 5'),(9898,41,'string 5'),(9899,41,'string 5'),(9900,41,'string 5'),(9901,41,'string 5'),(9902,41,'string 5'),(9903,41,'string 5'),(9904,41,'string 5'),(9905,41,'string 5'),(9906,41,'string 5'),(9907,41,'string 5'),(9908,41,'string 5'),(9909,41,'string 5'),(9910,41,'string 5'),(9911,41,'string 5'),(9912,41,'string 5'),(9913,41,'string 5'),(9914,41,'string 5'),(9915,41,'string 5'),(9916,41,'string 5'),(9917,41,'string 5'),(9918,41,'string 5'),(9919,41,'string 5'),(9920,41,'string 5'),(9921,41,'string 5'),(9922,41,'string 5'),(9923,41,'string 5'),(9924,41,'string 5'),(9925,41,'string 5'),(9926,41,'string 5'),(9927,41,'string 5'),(9928,41,'string 5'),(9929,41,'string 5'),(9930,41,'string 5'),(9931,41,'string 5'),(9932,41,'string 5'),(9933,41,'string 5'),(9934,41,'string 5'),(9935,41,'string 5'),(9936,41,'string 5'),(9937,41,'string 5'),(9938,41,'string 5'),(9939,41,'string 5'),(9940,41,'string 5'),(9941,41,'string 5'),(9942,41,'string 5'),(9943,41,'string 5'),(9944,41,'string 5'),(9945,41,'string 5'),(9946,41,'string 5'),(9947,41,'string 5'),(9948,41,'string 5'),(9949,41,'string 5'),(9950,41,'string 5'),(9951,41,'string 5'),(9952,41,'string 5'),(9953,41,'string 5'),(9954,41,'string 5'),(9955,41,'string 5'),(9956,41,'string 5'),(9957,41,'string 5'),(9958,41,'string 5'),(9959,41,'string 5'),(9960,41,'string 5'),(9961,41,'string 5'),(9962,41,'string 5'),(9963,41,'string 5'),(9964,41,'string 5'),(9965,41,'string 5'),(9966,41,'string 5'),(9967,41,'string 5'),(9968,41,'string 5'),(9969,41,'string 5'),(9970,41,'string 5'),(9971,41,'string 5'),(9972,41,'string 5'),(9973,41,'string 5'),(9974,41,'string 5'),(9975,41,'string 5'),(9976,41,'string 5'),(9977,41,'string 5'),(9978,41,'string 5'),(9979,41,'string 5'),(9980,41,'string 5'),(9981,41,'string 5'),(9982,41,'string 5'),(9983,41,'string 5'),(9984,41,'string 5'),(9985,41,'string 5'),(9986,41,'string 5'),(9987,41,'string 5'),(9988,41,'string 5'),(9989,41,'string 5'),(9990,41,'string 5'),(9991,41,'string 5'),(9992,41,'string 5'),(9993,41,'string 5'),(9994,41,'string 5'),(9995,41,'string 5'),(9996,41,'string 5'),(9997,41,'string 5'),(9998,41,'string 5'),(9999,41,'string 5'),(10000,41,'string 5'),(10001,41,'string 5'),(10002,41,'string 5'),(10003,41,'string 5'),(10004,41,'string 5'),(10005,41,'string 5'),(10006,41,'string 5'),(10007,41,'string 5'),(10008,41,'string 5'),(10009,41,'string 5'),(10010,41,'string 5'),(10011,41,'string 5'),(10012,41,'string 5'),(10013,41,'string 5'),(10014,41,'string 5'),(10015,41,'string 5'),(10016,41,'string 5'),(10017,41,'string 5'),(10018,41,'string 5'),(10019,41,'string 5'),(10020,41,'string 5'),(10021,41,'string 5'),(10022,41,'string 5'),(10023,41,'string 5'),(10024,41,'string 5'),(10025,41,'string 5'),(10026,41,'string 5'),(10027,41,'string 5'),(10028,41,'string 5'),(10029,41,'string 5'),(10030,41,'string 5'),(10031,41,'string 5'),(10032,41,'string 5'),(10033,41,'string 5'),(10034,41,'string 5'),(10035,41,'string 5'),(10036,41,'string 5'),(10037,41,'string 5'),(10038,41,'string 5'),(10039,41,'string 5'),(10040,41,'string 5'),(10041,41,'string 5'),(10042,41,'string 5'),(10043,41,'string 5'),(10044,41,'string 5'),(10045,41,'string 5'),(10046,41,'string 5'),(10047,41,'string 5'),(10048,41,'string 5'),(10049,41,'string 5'),(10050,41,'string 5'),(10051,41,'string 5'),(10052,41,'string 5'),(10053,41,'string 5'),(10054,41,'string 5'),(10055,41,'string 5'),(10056,41,'string 5'),(10057,41,'string 5'),(10058,41,'string 5'),(10059,41,'string 5'),(10060,41,'string 5'),(10061,41,'string 5'),(10062,41,'string 5'),(10063,41,'string 5'),(10064,41,'string 5'),(10065,41,'string 5'),(10066,41,'string 5'),(10067,41,'string 5'),(10068,41,'string 5'),(10069,41,'string 5'),(10070,41,'string 5'),(10071,41,'string 5'),(10072,41,'string 5');
/*!40000 ALTER TABLE `test_a_has_many` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_a_has_one`
--

DROP TABLE IF EXISTS `test_a_has_one`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_a_has_one` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aId` int(11) NOT NULL,
  `propA` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`aId`),
  KEY `aId` (`aId`),
  CONSTRAINT `test_a_has_one_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a_has_one`
--

LOCK TABLES `test_a_has_one` WRITE;
/*!40000 ALTER TABLE `test_a_has_one` DISABLE KEYS */;
INSERT INTO `test_a_has_one` VALUES (1,39,'string 4'),(3,40,'123');
/*!40000 ALTER TABLE `test_a_has_one` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_a_map`
--

DROP TABLE IF EXISTS `test_a_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_a_map` (
  `aId` int(11) NOT NULL,
  `anotherAId` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`aId`,`anotherAId`),
  KEY `anotherAId` (`anotherAId`),
  CONSTRAINT `test_a_map_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_a_map_ibfk_2` FOREIGN KEY (`anotherAId`) REFERENCES `test_a` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a_map`
--

LOCK TABLES `test_a_map` WRITE;
/*!40000 ALTER TABLE `test_a_map` DISABLE KEYS */;
INSERT INTO `test_a_map` VALUES (33,35,'link to a3'),(36,38,'link to a3');
/*!40000 ALTER TABLE `test_a_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_b`
--

DROP TABLE IF EXISTS `test_b`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_b` (
  `id` int(11) NOT NULL,
  `propB` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cId` int(11) DEFAULT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cId` (`cId`),
  KEY `userId` (`userId`),
  CONSTRAINT `test_b_ibfk_1` FOREIGN KEY (`id`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  CONSTRAINT `test_b_ibfk_2` FOREIGN KEY (`cId`) REFERENCES `test_c` (`id`) ON DELETE SET NULL,
  CONSTRAINT `test_b_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_b`
--

LOCK TABLES `test_b` WRITE;
/*!40000 ALTER TABLE `test_b` DISABLE KEYS */;
INSERT INTO `test_b` VALUES (31,'string 2',88,1),(32,'copy 2',NULL,1),(39,'string 2',NULL,1),(40,'string 2',NULL,1),(41,'string 2',NULL,1);
/*!40000 ALTER TABLE `test_b` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_c`
--

DROP TABLE IF EXISTS `test_c`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_c` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_c`
--

LOCK TABLES `test_c` WRITE;
/*!40000 ALTER TABLE `test_c` DISABLE KEYS */;
INSERT INTO `test_c` VALUES (88,'Test name');
/*!40000 ALTER TABLE `test_c` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `test_d`
--

DROP TABLE IF EXISTS `test_d`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test_d` (
  `id` int(11) NOT NULL,
  `propD` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_d`
--

LOCK TABLES `test_d` WRITE;
/*!40000 ALTER TABLE `test_d` DISABLE KEYS */;
INSERT INTO `test_d` VALUES (29,NULL),(30,NULL),(31,NULL),(32,NULL),(33,NULL),(34,NULL),(35,NULL),(36,NULL),(37,NULL),(38,NULL),(39,NULL),(40,'string 3'),(41,NULL);
/*!40000 ALTER TABLE `test_d` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_groups`
--

DROP TABLE IF EXISTS `ti_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acl_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_groups`
--

LOCK TABLES `ti_groups` WRITE;
/*!40000 ALTER TABLE `ti_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ti_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_messages`
--

DROP TABLE IF EXISTS `ti_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) NOT NULL DEFAULT 0,
  `has_status` tinyint(1) NOT NULL DEFAULT 0,
  `has_type` tinyint(1) NOT NULL DEFAULT 0,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachments` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_note` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `rate_id` int(11) NOT NULL DEFAULT 0,
  `rate_amount` double NOT NULL DEFAULT 0,
  `rate_hours` double NOT NULL DEFAULT 0,
  `rate_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `rate_cost_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_messages`
--

LOCK TABLES `ti_messages` WRITE;
/*!40000 ALTER TABLE `ti_messages` DISABLE KEYS */;
INSERT INTO `ti_messages` VALUES (1,1,0,1,0,0,'My rocket always circles back right at me? How do I aim right?','',0,0,1641205614,1641205614,0,0,0,'',NULL,NULL),(2,1,0,1,0,0,'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.','',1,2,1641205614,1641205614,0,0,0,'',NULL,NULL),(3,1,-1,1,1,0,'Gee I don\'t know how that can happen. I\'ll send you some new ones!','',0,2,1641205614,1641205614,0,0,0,'',NULL,NULL),(4,2,0,1,0,0,'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?','',0,0,1641032814,1641032814,0,0,0,'',NULL,NULL),(5,2,0,1,0,0,'Please respond faster. Can\'t you see this ticket is marked in red?','',0,0,1641205614,1641205614,0,0,0,'',NULL,NULL);
/*!40000 ALTER TABLE `ti_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_rates`
--

DROP TABLE IF EXISTS `ti_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_rates`
--

LOCK TABLES `ti_rates` WRITE;
/*!40000 ALTER TABLE `ti_rates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ti_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_settings`
--

DROP TABLE IF EXISTS `ti_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `use_alternative_url` tinyint(1) NOT NULL DEFAULT 0,
  `alternative_url` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_type` int(11) NOT NULL DEFAULT 0,
  `logo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `response_message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notify_contact` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expire_days` int(11) NOT NULL DEFAULT 0,
  `never_close_status_id` int(11) DEFAULT NULL,
  `disable_reminder_assigned` tinyint(1) NOT NULL DEFAULT 0,
  `disable_reminder_unanswered` tinyint(1) NOT NULL DEFAULT 0,
  `enable_external_page` tinyint(1) NOT NULL DEFAULT 0,
  `allow_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `external_page_css` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `leave_type_blank_by_default` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_to_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notify_agent` tinyint(1) NOT NULL DEFAULT 0,
  `notify_agent_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notify_due_date` tinyint(1) NOT NULL DEFAULT 0,
  `notify_due_date_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_reopen_ticket_only` tinyint(1) NOT NULL DEFAULT 0,
  `show_close_confirm` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_settings`
--

LOCK TABLES `ti_settings` WRITE;
/*!40000 ALTER TABLE `ti_settings` DISABLE KEYS */;
INSERT INTO `ti_settings` VALUES (1,'admin@intermesh.mailserver','Group-Office Customer Support',1,'/modules/site/index.php?r=tickets/externalpage/ticket','{SUBJECT}',1,'groupoffice.png','This is our support system. Please enter your contact information and describe your problem.','Thank you for contacting us. We have received your question and created a ticket for you. we will respond as soon as possible. For future reference, your question has been assigned the following ticket number: {TICKET_NUMBER}.',0,'en',0,NULL,0,0,1,1,NULL,0,0,NULL,1,'{AGENT} just picked up your ticket. We\'ll keep you up to date about our progress.',1,'Number: {NUMBER}\nSubject: {SUBJECT}\nCreated by: {CREATEDBY}\nCompany: {COMPANY}\n\n\nURL: {LINK}\n\n\n{MESSAGE}',0,NULL,0,0);
/*!40000 ALTER TABLE `ti_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_statuses`
--

DROP TABLE IF EXISTS `ti_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_statuses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_statuses`
--

LOCK TABLES `ti_statuses` WRITE;
/*!40000 ALTER TABLE `ti_statuses` DISABLE KEYS */;
INSERT INTO `ti_statuses` VALUES (1,'In progress',1,NULL),(2,'Not resolved',1,NULL);
/*!40000 ALTER TABLE `ti_statuses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_templates`
--

DROP TABLE IF EXISTS `ti_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `autoreply` tinyint(1) NOT NULL DEFAULT 0,
  `default_template` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_created_for_client` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_mail_for_agent` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_claim_notification` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_templates`
--

LOCK TABLES `ti_templates` WRITE;
/*!40000 ALTER TABLE `ti_templates` DISABLE KEYS */;
INSERT INTO `ti_templates` VALUES (1,'Default response','Dear sir/madam\nThank you for your response,\n{MESSAGE}\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.',1,0,1,0,0,0),(2,'Default ticket created by client','Dear sir/madam\nWe have received your question and a ticket has been created.\nWe will respond as soon as possible.\nThe message you sent to us was:\n---------------------------------------------------------------------------\n{MESSAGE}\n---------------------------------------------------------------------------\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.',1,1,0,0,0,0),(3,'Default ticket created for client','Dear sir/madam\nWe have created a ticket for you.\nWe will respond as soon as possible.\nThe ticket is about:\n---------------------------------------------------------------------------\n{MESSAGE}\n---------------------------------------------------------------------------\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.',1,0,0,1,0,0);
/*!40000 ALTER TABLE `ti_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_tickets`
--

DROP TABLE IF EXISTS `ti_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ticket_verifier` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `agent_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `company` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT 0,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `middle_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `unseen` int(1) NOT NULL DEFAULT 1,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `last_response_time` int(11) NOT NULL DEFAULT 0,
  `cc_addresses` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cuser_id` int(11) NOT NULL DEFAULT 0,
  `due_date` int(11) DEFAULT NULL,
  `due_reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `last_agent_response_time` int(11) NOT NULL DEFAULT 0,
  `last_contact_response_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `user_id` (`user_id`),
  KEY `status_id` (`status_id`),
  KEY `unseen_type_id_agent_id` (`unseen`,`type_id`,`agent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_tickets`
--

LOCK TABLES `ti_tickets` WRITE;
/*!40000 ALTER TABLE `ti_tickets` DISABLE KEYS */;
INSERT INTO `ti_tickets` VALUES (1,'202200001',27603570,1,-1,1,1,0,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Malfunctioning rockets',1641205614,1641205614,1,0,0,0,0,1641205614,'',1,NULL,0,1641205614,1641205614),(2,'202200002',29675799,1,0,1,1,0,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Can I speed up my rockets?',1641032814,1641205614,1,0,1,0,0,1641205614,'',1,NULL,0,1641205614,1641205614);
/*!40000 ALTER TABLE `ti_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_tickets_custom_fields`
--

DROP TABLE IF EXISTS `ti_tickets_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_tickets_custom_fields` (
  `id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `ti_tickets_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ti_tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_tickets_custom_fields`
--

LOCK TABLES `ti_tickets_custom_fields` WRITE;
/*!40000 ALTER TABLE `ti_tickets_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `ti_tickets_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_type_groups`
--

DROP TABLE IF EXISTS `ti_type_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_type_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_index` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_type_groups`
--

LOCK TABLES `ti_type_groups` WRITE;
/*!40000 ALTER TABLE `ti_type_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `ti_type_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ti_types`
--

DROP TABLE IF EXISTS `ti_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ti_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `show_statuses` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_from_others` tinyint(1) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `email_on_new` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_to_agent` tinyint(1) NOT NULL DEFAULT 0,
  `custom_sender_field` tinyint(1) NOT NULL DEFAULT 0,
  `sender_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_email` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publish_on_site` tinyint(1) NOT NULL DEFAULT 0,
  `type_group_id` int(11) DEFAULT NULL,
  `email_account_id` int(11) NOT NULL DEFAULT 0,
  `enable_templates` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_to_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notify_agent` tinyint(1) NOT NULL DEFAULT 0,
  `notify_agent_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_cache_acl_id` int(11) NOT NULL DEFAULT 0,
  `email_on_new_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_types`
--

LOCK TABLES `ti_types` WRITE;
/*!40000 ALTER TABLE `ti_types` DISABLE KEYS */;
INSERT INTO `ti_types` VALUES (1,'IT',NULL,1,79,NULL,0,13,NULL,0,0,NULL,NULL,1,0,0,0,0,NULL,0,NULL,0,NULL,0,NULL),(2,'Sales',NULL,1,81,NULL,0,14,NULL,0,0,NULL,NULL,0,0,0,0,0,NULL,0,NULL,0,NULL,0,NULL);
/*!40000 ALTER TABLE `ti_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeregistration2_settings`
--

DROP TABLE IF EXISTS `timeregistration2_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `timeregistration2_settings` (
  `userId` int(11) NOT NULL,
  `selectProjectOnTimerStart` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeregistration2_settings`
--

LOCK TABLES `timeregistration2_settings` WRITE;
/*!40000 ALTER TABLE `timeregistration2_settings` DISABLE KEYS */;
INSERT INTO `timeregistration2_settings` VALUES (2,0),(3,0),(4,0),(5,0),(6,0);
/*!40000 ALTER TABLE `timeregistration2_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wopi_action`
--

DROP TABLE IF EXISTS `wopi_action`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wopi_action` (
  `serviceId` int(11) NOT NULL,
  `app` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ext` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  KEY `serviceId` (`serviceId`),
  CONSTRAINT `wopi_action_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wopi_action`
--

LOCK TABLES `wopi_action` WRITE;
/*!40000 ALTER TABLE `wopi_action` DISABLE KEYS */;
/*!40000 ALTER TABLE `wopi_action` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wopi_lock`
--

DROP TABLE IF EXISTS `wopi_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wopi_lock` (
  `id` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `serviceId` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `expiresAt` datetime NOT NULL,
  PRIMARY KEY (`id`,`serviceId`),
  KEY `fileId` (`fileId`),
  KEY `expiresAt` (`expiresAt`),
  KEY `wopi_lock_ibfk_1` (`serviceId`),
  CONSTRAINT `wopi_lock_ibfk_1` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wopi_lock_ibfk_2` FOREIGN KEY (`fileId`) REFERENCES `fs_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wopi_lock`
--

LOCK TABLES `wopi_lock` WRITE;
/*!40000 ALTER TABLE `wopi_lock` DISABLE KEYS */;
/*!40000 ALTER TABLE `wopi_lock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wopi_service`
--

DROP TABLE IF EXISTS `wopi_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wopi_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wopiClientUri` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wopi_service`
--

LOCK TABLES `wopi_service` WRITE;
/*!40000 ALTER TABLE `wopi_service` DISABLE KEYS */;
/*!40000 ALTER TABLE `wopi_service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wopi_token`
--

DROP TABLE IF EXISTS `wopi_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wopi_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serviceId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `token` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiresAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `serviceId` (`serviceId`),
  KEY `userId` (`userId`),
  KEY `token` (`token`),
  KEY `expiresAt` (`expiresAt`),
  CONSTRAINT `wopi_token_ibfk_2` FOREIGN KEY (`serviceId`) REFERENCES `wopi_service` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wopi_token_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wopi_token`
--

LOCK TABLES `wopi_token` WRITE;
/*!40000 ALTER TABLE `wopi_token` DISABLE KEYS */;
/*!40000 ALTER TABLE `wopi_token` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-01-03 10:28:13
