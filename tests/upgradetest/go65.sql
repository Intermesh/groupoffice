-- MariaDB dump 10.19  Distrib 10.5.11-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: groupoffice
-- ------------------------------------------------------
-- Server version	10.5.11-MariaDB-1:10.5.11+maria~focal

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
INSERT INTO `addressbook_address` VALUES (1,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(2,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(3,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(4,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(15,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(16,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(17,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL),(18,'postal','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','Netherlands','NL',NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_addressbook`
--

LOCK TABLES `addressbook_addressbook` WRITE;
/*!40000 ALTER TABLE `addressbook_addressbook` DISABLE KEYS */;
INSERT INTO `addressbook_addressbook` VALUES (1,'Shared',11,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(2,'Users',31,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(3,'Customers',76,1,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(4,'Elmer Fudd',80,2,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(5,'Demo User',85,3,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(6,'Linda Smith',90,4,NULL,'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),(10,'Peter Smith',194,9,NULL,'Geachte [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]heer/mevrouw[else][if {{contact.gender}}==\"M\"]heer[else]mevrouw[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}');
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact`
--

LOCK TABLES `addressbook_contact` WRITE;
/*!40000 ALTER TABLE `addressbook_contact` DISABLE KEYS */;
INSERT INTO `addressbook_contact` VALUES (1,3,1,'2021-06-28 08:56:41','2021-06-28 08:56:43',1,NULL,NULL,'',NULL,NULL,'Smith Inc.',NULL,NULL,NULL,'Just a demo company',1,'Smith Inc.','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',1,NULL,NULL,NULL,NULL,NULL,NULL,'1@host.docker.internal:8080',NULL,'1@host.docker.internal:8080.vcf',NULL),(2,3,1,'2021-06-28 08:56:41','2021-06-28 08:56:41',1,3,'','','John','','Smith','','Dear Ms./Mr. Smith',NULL,'Just a demo john',0,'John Smith','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'a2b13489e9762bf7d7dfd63d72d45f0f47411c30',NULL,'CEO',NULL,NULL,'2@host.docker.internal:8080',NULL,'2@host.docker.internal:8080.vcf',NULL),(3,3,1,'2021-06-28 08:56:41','2021-06-28 08:56:43',1,NULL,NULL,'',NULL,NULL,'ACME Corporation',NULL,NULL,NULL,'Just a demo acme',1,'ACME Corporation','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',1,NULL,NULL,NULL,NULL,NULL,NULL,'3@host.docker.internal:8080',NULL,'3@host.docker.internal:8080.vcf',NULL),(4,3,1,'2021-06-28 08:56:41','2021-06-28 08:56:41',1,NULL,'','','Wile','E.','Coyote','','Dear Ms./Mr. E. Coyote',NULL,'Just a demo wile',0,'Wile E. Coyote','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'0ec2f1f4f9fb41e8013fcc834991be30a8260750',NULL,'CEO',NULL,NULL,'4@host.docker.internal:8080',NULL,'4@host.docker.internal:8080.vcf',NULL),(5,1,1,'2021-06-28 10:24:29','2021-07-23 07:24:19',1,NULL,'','','info','','','','Dear ',NULL,'',0,'info','','','',0,'',NULL,'en_uk','','',NULL,'5@host.docker.internal:8080','250ea8dbd7a92824a81eacc8125c376f32a87f4e','5@host.docker.internal:8080.vcf',NULL),(6,1,1,'2021-06-28 15:10:14','2021-07-20 11:33:24',1,NULL,'','','Admin','','','','Dear Ms./Mr. ',NULL,'',0,'Admin','','','',0,'',NULL,'en_uk','','',NULL,'6@host.docker.internal:8080','7b6417a12cfd91021a114b03333214c1c5ee9ad7','6@host.docker.internal:8080.vcf',NULL),(7,1,1,'2021-06-28 15:12:24','2021-07-20 11:33:24',1,NULL,'','','Jantje','','Beton','','Dear Ms./Mr. Beton',NULL,'',0,'Jantje Beton','','','',0,'',NULL,'en_uk','','',NULL,'7@host.docker.internal:8080','b7ffc18c76e66d0fdcb8de8c892a29d9213d1c7b','7@host.docker.internal:8080.vcf',NULL),(8,3,1,'2021-07-12 12:28:09','2021-07-12 12:28:09',1,NULL,'','','Piet','','Jansen','','Dear Ms./Mr. Jansen',NULL,'',0,'Piet Jansen','','','',0,'',NULL,'en_uk','','',NULL,'8@host.docker.internal:8080',NULL,'8@host.docker.internal:8080.vcf',NULL),(9,1,1,'2021-07-13 12:21:25','2021-07-20 11:33:25',1,NULL,'','','gjhghj','','','','Dear Ms./Mr. ',NULL,'',0,'gjhghj','','','',0,'',NULL,'en_uk','','',NULL,'9@host.docker.internal:8080','55ffc4ca8945d6cbe420e3d5566ccb57128266fc','9@host.docker.internal:8080.vcf',NULL),(12,1,1,'2021-07-16 12:45:27','2021-07-20 11:33:25',1,NULL,'','','','','ABC & Co','','Dear Ms./Mr. ABC & Co',NULL,NULL,0,'ABC & Co','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'12@host.docker.internal:8080','fe464d739fd83bbe7aa26f21d7a097fb1d1a753b','12@host.docker.internal:8080.vcf',NULL),(14,1,1,'2021-07-16 12:46:03','2021-08-24 12:17:41',1,NULL,'','','Cont','','YOYO & Co','','Dear Ms./Mr. YOYO & Co',NULL,'Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\n\n**********************************************************************************************************************************\n\nEn nu nog langer\n\njaja',0,'Cont YOYO & Co','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'14@host.docker.internal:8080','54356b3c7d716b48ab13b77b3f4d61012d361c27','14@host.docker.internal:8080.vcf',NULL),(15,4,1,'2021-07-16 12:48:29','2021-07-19 13:07:18',1,NULL,NULL,'',NULL,NULL,'Smith Inc.',NULL,'Dear sir/madam',NULL,'Just a demo company',1,'Smith Inc.','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',1,NULL,NULL,NULL,NULL,NULL,NULL,'1@host.docker.internal:8080',NULL,'1@host.docker.internal:8080.vcf',NULL),(16,4,1,'2021-07-16 12:48:29','2021-07-16 12:48:29',1,NULL,NULL,'',NULL,NULL,'ACME Corporation',NULL,'Dear sir/madam',NULL,'Just a demo acme',1,'ACME Corporation','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',1,NULL,NULL,NULL,NULL,NULL,NULL,'3@host.docker.internal:8080',NULL,'3@host.docker.internal:8080.vcf',NULL),(17,4,1,'2021-07-16 12:48:29','2021-07-16 12:48:29',1,NULL,'','','John','','Smith','','Dear Ms./Mr. Smith',NULL,'Just a demo john',0,'John Smith','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'a2b13489e9762bf7d7dfd63d72d45f0f47411c30',NULL,'CEO',NULL,NULL,'2@host.docker.internal:8080',NULL,'2@host.docker.internal:8080.vcf',NULL),(18,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','Wile','E.','Coyote','','Dear Ms./Mr. E. Coyote',NULL,'Just a demo wile',0,'Wile E. Coyote','NL 00 ABCD 0123 34 1234','','NL 1234.56.789.B01',0,NULL,'0ec2f1f4f9fb41e8013fcc834991be30a8260750',NULL,'CEO',NULL,NULL,'4@host.docker.internal:8080',NULL,'4@host.docker.internal:8080.vcf',NULL),(19,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','info','','','','Dear Ms./Mr. ',NULL,NULL,0,'info','','',NULL,0,NULL,NULL,'en_uk',NULL,NULL,NULL,'5@host.docker.internal:8080',NULL,'5@host.docker.internal:8080.vcf',NULL),(20,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','Admin','','','','Dear Ms./Mr. ',NULL,NULL,0,'Admin','','',NULL,0,NULL,NULL,'en_uk',NULL,NULL,NULL,'6@host.docker.internal:8080',NULL,'6@host.docker.internal:8080.vcf',NULL),(21,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','Jantje','','Beton','','Dear Ms./Mr. Beton',NULL,NULL,0,'Jantje Beton','','',NULL,0,NULL,NULL,'en_uk',NULL,NULL,NULL,'7@host.docker.internal:8080',NULL,'7@host.docker.internal:8080.vcf',NULL),(22,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','Piet','','Jansen','','Dear Ms./Mr. Jansen',NULL,NULL,0,'Piet Jansen','','',NULL,0,NULL,NULL,'en_uk',NULL,NULL,NULL,'8@host.docker.internal:8080',NULL,'8@host.docker.internal:8080.vcf',NULL),(23,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','gjhghj','','','','Dear Ms./Mr. ',NULL,NULL,0,'gjhghj','','',NULL,0,NULL,NULL,'en_uk',NULL,NULL,NULL,'9@host.docker.internal:8080',NULL,'9@host.docker.internal:8080.vcf',NULL),(24,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','','','ABC & Co','','Dear Ms./Mr. ABC & Co',NULL,NULL,0,'ABC & Co','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'12@host.docker.internal:8080',NULL,'12@host.docker.internal:8080.vcf',NULL),(25,4,1,'2021-07-16 12:48:30','2021-07-16 12:48:30',1,NULL,'','','','','YOYO & Co','','Dear Ms./Mr. YOYO & Co',NULL,NULL,0,'YOYO & Co','','',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'14@host.docker.internal:8080',NULL,'14@host.docker.internal:8080.vcf',NULL),(26,1,1,'2021-07-16 14:20:12','2021-07-20 11:33:26',1,NULL,NULL,'',NULL,NULL,'Test',NULL,NULL,NULL,'',1,'Test','','','',0,'',NULL,'nl','','',NULL,'26@localhost:8080','47dcc09cd5ab65e913115e6004abfb4f06d9a0d2','26@localhost:8080.vcf',NULL),(27,1,1,'2021-07-19 11:35:48','2021-07-20 11:33:27',1,NULL,'','','Peter','','Clemens','','Dear Ms./Mr. Clemens',NULL,'',0,'Peter Clemens','','','',0,'',NULL,'nl','','',NULL,'27@localhost:8080','524aff24c8a2818ba4c1d8c2b8b2b22bc1425b4a','27@localhost:8080.vcf',NULL),(28,5,1,'2021-07-20 11:42:18','2021-07-20 11:47:41',1,NULL,'','','From','','Demo 1','','Dear Ms./Mr. Demo',NULL,'',0,'From Demo 1','','','',0,'',NULL,'nl','','',NULL,'28@localhost:8080','7bfbcf7bd8eca79efd7a49593c535fd18f91205d','28@localhost:8080.vcf',NULL),(29,5,1,'2021-07-20 11:45:27','2021-07-20 11:45:33',1,NULL,'','','Demo','','2','','Dear Ms./Mr. 2',NULL,'',0,'Demo 2','','','',0,'',NULL,'nl','','',NULL,'29@localhost:8080','fc32a048a4f726fb116421a619aa55adbe859944','29@localhost:8080.vcf',NULL),(30,5,1,'2021-07-20 11:46:10','2021-07-20 11:46:10',1,NULL,'','','Demo','','3','','Dear Ms./Mr. 3',NULL,'',0,'Demo 3','','','',0,'',NULL,'nl','','',NULL,'30@localhost:8080',NULL,'30@localhost:8080.vcf',NULL);
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
  `Only_in_shared` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `For_piet` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `go_check` tinyint(1) NOT NULL DEFAULT 0,
  `Manager` int(11) DEFAULT NULL,
  `Action_date` datetime DEFAULT NULL,
  `date` date DEFAULT NULL,
  `Month` int(11) DEFAULT NULL,
  `For_piet1` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `addressbook_contact_custom_fields_ibfk_go_11` (`Manager`),
  KEY `addressbook_contact_custom_fields_ibfk_go_16` (`Month`),
  CONSTRAINT `addressbook_contact_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_11` FOREIGN KEY (`Manager`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_16` FOREIGN KEY (`Month`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `addressbook_contact_custom_fields`
--

LOCK TABLES `addressbook_contact_custom_fields` WRITE;
/*!40000 ALTER TABLE `addressbook_contact_custom_fields` DISABLE KEYS */;
INSERT INTO `addressbook_contact_custom_fields` VALUES (5,'','',0,NULL,NULL,NULL,NULL,''),(6,'','',0,NULL,NULL,NULL,NULL,''),(7,'sdfsedd','',0,NULL,NULL,NULL,NULL,''),(8,'','sdasd',1,NULL,NULL,NULL,NULL,''),(9,'','',0,NULL,NULL,NULL,NULL,''),(12,'','',0,NULL,'2021-07-19 11:27:23','2021-07-19',NULL,''),(14,'','',0,NULL,NULL,NULL,11,''),(15,'','',0,NULL,NULL,NULL,NULL,''),(20,'','',0,NULL,NULL,NULL,NULL,''),(21,'sdfsedd','',0,NULL,NULL,NULL,NULL,''),(22,'','sdasd',1,NULL,NULL,NULL,NULL,''),(23,'','',0,NULL,NULL,NULL,NULL,''),(26,'','',0,22,NULL,NULL,NULL,''),(27,'','',0,NULL,NULL,NULL,NULL,''),(28,'','',0,NULL,NULL,NULL,NULL,''),(29,'','',0,NULL,NULL,NULL,NULL,''),(30,'','',0,NULL,NULL,NULL,NULL,'');
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
INSERT INTO `addressbook_contact_star` VALUES (12,1,0,1),(15,1,0,1);
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
INSERT INTO `addressbook_email_address` VALUES (1,'work','info@smith.demo'),(2,'work','john@smith.demo'),(3,'work','info@acme.demo'),(4,'work','wile@smith.demo'),(5,'work','info@indonesiahijau.co.id'),(6,'work','admin@intermesh.localhost'),(15,'work','info@smith.demo'),(16,'work','info@acme.demo'),(17,'work','john@smith.demo'),(18,'work','wile@smith.demo'),(19,'work','info@indonesiahijau.co.id'),(20,'work','admin@intermesh.localhost');
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
INSERT INTO `addressbook_phone_number` VALUES (1,'work','+31 (0) 10 - 1234567'),(1,'mobile','+31 (0) 6 - 1234567'),(2,'work','+31 (0) 10 - 1234567'),(2,'mobile','+31 (0) 6 - 1234567'),(3,'work','+31 (0) 10 - 1234567'),(3,'mobile','+31 (0) 6 - 1234567'),(4,'work','+31 (0) 10 - 1234567'),(4,'mobile','+31 (0) 6 - 1234567'),(15,'work','+31 (0) 10 - 1234567'),(15,'mobile','+31 (0) 6 - 1234567'),(16,'work','+31 (0) 10 - 1234567'),(16,'mobile','+31 (0) 6 - 1234567'),(17,'work','+31 (0) 10 - 1234567'),(17,'mobile','+31 (0) 6 - 1234567'),(18,'work','+31 (0) 10 - 1234567'),(18,'mobile','+31 (0) 6 - 1234567');
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
INSERT INTO `addressbook_url` VALUES (1,'homepage','http://www.smith.demo'),(2,'homepage','http://www.smith.demo'),(3,'homepage','http://www.acme.demo'),(4,'homepage','http://www.smith.demo'),(15,'homepage','http://www.smith.demo'),(16,'homepage','http://www.acme.demo'),(17,'homepage','http://www.smith.demo'),(18,'homepage','http://www.smith.demo');
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
INSERT INTO `addressbook_user_settings` VALUES (1,1),(2,4),(3,5),(4,6),(9,10);
/*!40000 ALTER TABLE `addressbook_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apikeys_key`
--

DROP TABLE IF EXISTS `apikeys_key`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `apikeys_key` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `accessToken` (`accessToken`),
  CONSTRAINT `apikeys_key_ibfk_1` FOREIGN KEY (`accessToken`) REFERENCES `core_auth_token` (`accessToken`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apikeys_key`
--

LOCK TABLES `apikeys_key` WRITE;
/*!40000 ALTER TABLE `apikeys_key` DISABLE KEYS */;
/*!40000 ALTER TABLE `apikeys_key` ENABLE KEYS */;
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
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `openExtern` tinyint(1) NOT NULL DEFAULT 1,
  `behaveAsModule` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `createdBy` (`createdBy`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `bookmarks_bookmark_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bookmarks_bookmark_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `bookmarks_category` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookmarks_bookmark`
--

LOCK TABLES `bookmarks_bookmark` WRITE;
/*!40000 ALTER TABLE `bookmarks_bookmark` DISABLE KEYS */;
INSERT INTO `bookmarks_bookmark` VALUES (1,1,1,'Group-Office','https://www.group-office.com','Group-Office is an enterprise CRM and groupware tool. Share projects, calendars, files and e-mail online with co-workers and clients. Easy to use and fully customizable.','a277a250ad9fa623fd0c1c9bdbfb5804981d14e4',1,0),(2,1,1,'Intermesh','https://www.intermesh.nl','Intermesh - Solide software ontwikeling sinds 2003','b82d0979d555bd137b33c15021129e06cbeea59a',1,0);
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
INSERT INTO `bookmarks_category` VALUES (1,1,125,'General');
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
INSERT INTO `bs_books` VALUES (1,1,'Quotes',42,'Q%y',6,NULL,4,19,'‚Ç¨',NULL,NULL,'',3,NULL,NULL,0,0,0,0,0,4,0,0,0,0,0,0,0,14),(2,1,'Orders',47,'O%y',6,NULL,4,19,'‚Ç¨',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,5,0,0,0,0,0,0,0,14),(3,1,'Invoices',52,'I%y',6,NULL,4,19,'‚Ç¨',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,6,0,0,0,0,0,0,0,14);
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
INSERT INTO `bs_doc_templates` VALUES (1,1,1,'Invoice','PK\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0Thumbnails/thumbnail.pngâPNG\r\n\Z\n\0\0\0\rIHDR\0\0\0µ\0\0\0\0\0\0zA†å\0\0!±IDATxúÌùw@G˚«∑‹^ø£pÙﬁª\n\"äΩ«Ç∆hL11â)æyı}”ì_ä)ÔõÚ¶K‘D£—h‘Xc{Ï<DÈı8Ó∏≤w˜ªBÔLîÁÛ«ÌÓÏÏ3s∑_füyfv`Ëız\0∫Ä—”\0z5†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0ñÈCW˙”º•7ä≤UØ˝[∫£Œ∂∏ˆë%ƒøxûÛÌÚr[å5¸›7\"˜>˘_<Tz¥◊áÃéﬂ∂¯HÙàƒqOÕÍ\'ƒ-≠âÆ|ÀK_Ú_{+]D¥&Í´~ùˇ•Ágo∆p⁄Â’7\\ﬁ}Œ>#Å¸m˛ó./¯≠€Ú˛øcπññ‘ä∫hû4à˛„94›ì2§®$ªéË|+7˛9•ÈæXQŒ%^|®t√ß\'åáLÎx‡±¢˝`8ÿÁ|¥ÜYZ¯ŒÂ+»à¨§√üÖø∏|Œ’yKŒ+\"1]˝È≥~ÛWº@Vm⁄Fêñ–ñ}ß8t•kßøß-°÷\n(&º„·˘á±Ùë>∆_ûÆπEƒNQoÂ©˝\n<¯π∞^ùÒ˘5—‡xù`˛≠[#!¨¯úÂØˇôÓÃ–Àr7¨Ω¯Àr9]¬yŒ%⁄£∫Hâa˜†åÆ:µeyCÇ;[òªÍÕÌ¬1Ò5G.ÿéOÌ`¯aÙçRµ¿âdÚÃá}+æ7aìºptÓ‚lF¿Ï¿≥…ˇ/@˘˘gã_Sπå˘àÉï¬ò»k_,ö◊Ë?o(¶”“ örôŒßmc`Ñ¥ãëpeÕ~‹âCóÏÀÆ¿úÃ\'Ëícgô¡≥Ò*ú IÃ ,Üx$e#}«Ùz ﬁÖ§Œâ„ìúõkåìLód‡Â‡![q™f‰p{ã€™6ıaÒ8ÜvC[}πPK4Í0Ç$0Ç0YRñó9πFZ‚?$X¶¬e∆äOÕª√g\Z>sœò≠¯∂9Àå?ÕœÔü’ÖùÈﬂ,1láÕÓX˜Ù!’ﬂû¢ﬁY˙ıXs ≤è[œ\\?”º£∫πo˝·Rö¡ß;ô*Ø)\\ı≠Õ¯OÔE”w“O5Ìg&õ6ÉG?Ω3åüÅa¶\\>√3Ó¡¯CAÔi7û≥óΩw◊\\,ØÙiÌµGy?ˆ›\'›U©>OÔ—–}\0(@\0\nı°º∏Ïïì^LiÎÍÍÚT\rıcwuQKÕıUK«,Œı›~¿ÉäÖ˙–‘◊”«ﬁyılÊﬁ—+råP]˛]˘Ù‰ ›‘Ôu∑ÖqnEárïLq∆‘ê?æ,ú¥–u˚·®ß\n∑≠‹∏”ê¡q¬XûTÆ≈D–Z=hXv«‘%Á´|º§^≈t®9yîLÓÉˇvU?“â∏©√	ú‡z«ıcbjU#∆w``8?(5FDbZ\'\"ﬂòÅ…«Ø‰ÀÙûv–Ä<`X¶¶Gå®V:˜”4{;–‘øú>›¯9§CVS¯¿•XSÂò2Ò”wå°QıµÔﬂ\nm‚x∞∞≈«9>ÒÆ¬6±Pù¨‡¢¬3BLuöΩmîB%ŸµØTlhPàŸË“˝[k&˘H6≥óÊ“R¥Òåc%‰–$mÎË	Ç¶≤B9Ël@{,‘áé÷™éˇ∞äåã•nU”);ºbã*eBX£Ã>HPp‚:ŒsMNw‹∑F“îËJu 3c<HÈŸ’üm·èXπ·”≠t⁄¨Q5oó)äÈÑâŒŸªY≥¶“k~fÃ}∆è®‘µ5~„Ï∫•9£WΩ†›ü{Úd= ¯ÚóÁˇf?~∆XzÕÈÀú∞ÚNå/ö‡rÎó/∑UÖƒªïÊñäìcZ◊Ω?ÊCàe˙–‘VPlí$ç˘%ßØäìc}xÚSJ5≠«1“∆?<àMj¥Zå„»√I˚∞(g√ﬂ≤∫ÚR°ñÚSh1ú$í`ÿáEÆ™kI∂´áˆD>»C{¥DFñ◊*Ï[çﬂº(≥—‰W0	∞õ\Z¥qöjTaÑ√∫2Æƒ\\Hõ¿h∂V©2’ìMﬁÆ’`ºª¥3@;,”≈i8|⁄˚ÉE—,ãaH—{$txƒõÜ+4yŸád¬–Gﬁ|á“…KÀbw>!;hh€G¯H\Zª‰0ó¥0„P«‡◊µ%{÷Ò]hëíù5À’†ê»Fº`2ïË◊jñ.À˛C∑p!Àåÿ\\p6[¢6oUV”È§¶Ì”NãqC5Nr<CS\")Éüd(kΩÿƒa%>_ÿæ	·kWY¸|â\"•ykﬁ8‰wÒ√£	ãÊF\Z€ˆ©„á9Sc>zÙÎ%s=>˚∞ìÁVºgı^e¥˝Øˇ…ÀoË«ıÏD	˝GÚ,©2Â5ÿ©ıêtñÂz/øPﬂ¶˚û/UyW≠;ûßîÀ’ÎéÁàŸàáˆT9Â»≈Ù]—ŒêÜ˛≤ã£´K…Elp*ßÙ<#Ó\'>_Ï≈JZ‘Ù0=_∞∏î\';dkˇ|ô˛ﬁKî˙&˘jÿê°ÃÉÜ∂ù€ô√Gj\Z¬HÍc≥bª™Óœ-ß≈6“K2Ò`«}ƒ“˛-…$Ò∂… ∫†mv¶W∆0„÷‹∂GLç/∏XÆ1_ÖÓpﬁÒt lc&¶∂Óå∏øt[ˇ÷Ï:»8Ú√˜Á√féˆP]…ì˜Á]-ÿ˝ªîÙˆ≥ÛÌŒ^O∑˘f◊Å÷“z\n7\\RÔ- XL!Ezf^›RùŒƒ@7˙f◊ã˚L€L0]›ü≤ò0p&∫ŸˇË4ß¡ôfp>í≤$õˆﬂKd˝éú;Ô6∫Õˇ–Ïÿ~^Í÷/¯‹ø¶m\Zwl◊ëÊFáh˜=∏5∑∂~¸çd@V¯üü}.â…‡ªÚˇt \ZÆL˜–m˛ÊÊYæ±<!ÙÚÔ<OJ©1ZÈÌÓπª[£3bj5e+b·6AÆ5ßÆR5≈-W¶;ËFˇc‡3ˇå0æ˝mñ!%ÁPæ04eÙ”lévw\ZπõŸë\Zççi>;*ÕdMÎ‚Ë,|æp#¶[¸\n»˛Gß)Õ—Óød∂É5‡of¸(@\0\nÀÙAm^˙kï»+„±Ò>å∂]PÂ≈Ø.Ω=Ûã˜¬o®JÂülåwoz—¶≠?¯Xÿ~ê¬êÃ¡.áˆ¨xsï„∞¿íÚ€NWZ¯\\\nª^·**ZıèEJ©ßb\\Á$ºË0chÄy\"˚ÙèF√¥ıoù∂˛ÚÆ-•Eâñ≤aàÖ˙r⁄–Á‘õÁµHùÇÈ‡TØ”„\\n\\ø»(v”Dv\n¶≠?ËX¶ÜÁƒ≈/¥9–º√èqlÙı{„ºvá‰£õOç2N[œÅiÎ6±ÈÁ∆º≤§ÀìÃ¿yÔ˛5˚@ÆÄÙ†\0}\0(zR8ÆÎ_•ªˇ}`∑VmˆJÆπ≠¢s7nï¯øÚÔT…Ó„ıÓì:y≥-mÌ˝iuçãÕ•?nO|i¯ïï[ÍÊÃçÆ⁄∫%ªZ!àõ<) m¿h·Aˇﬂå˜·¨áı1eˆ„∆√¢Õ=„†)ﬁ^õ>›ÙäoäÒ#Ú•óMiˆ¶CèÈÔƒR}tõT€¨<£Ø‹±‰\'◊?ﬂè◊uYÕ1˚.çò°%k?*\Zªdà∞%ãÆÓ¬…Ü¿XÕÜ˜˛®°º≤Ó¥\0téÌá^S¥ÂìıWp{;ıç∆cú.®‰ó§±£DÁs•äb:~¨ÀŸM?Á⁄Õ¸◊b€ö¬ÑÖœ•8íw∑*´ó·áûü≥¨¶÷˝âgπWØHÁNZ?}°ÎÊ\'´j›ü˙á˜ˆ5<”cd\'•C«s÷~,ôætå)fﬂ˛—†©Ø◊û|w¡/∂›ãä‚ﬂùv˚É~Wƒñœ˙oEÀb5¬™3{è^8õıh@G$˜·+Xı|!¯A√G+6m(pÙ`<}úä%ï¥qfWÁ™∫\'l£‹ÛnË¥4¡1Eﬂ-¡°ÁãÇS„äs*¢¿∞®í™35¶f‚x9ï∆ˆÇ+5ZµBAsªäŸõ¬¸<“∏Rçii\ZF1…díÜÍ∂Y¨∆8É\r«\rÈzÉÖãı∑kÙ¡pKÔfx÷á7áaXs+m~7\ZãÀ4πXTx«´;áÈc_)ù˚œ4{”íπcbö“«öR∆%öéMÀ„NHÓ<fo\\æ¶F:˜Ûgõ√¸Yü¸/´ÂlÛb5”^õ≈ƒfÕ7.V£Å®øÖX°èÓpA-$*BﬂëŒcˆ»0ˇKÍB‘ﬂ\Z,’«C¥Óàü(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†∞J˙∫˝Ø}¬xıùd¡ùgÓX∑É.ﬁ∑Ω>T|∂,bFÆº¥D÷Pã{w\\’£ÌkW-ˇ<‘∏d™^Ï¨óúØ§ù‹]˘wô«Ò`¯>ﬁ\\Œ˜˘D:≥Z/Ó÷ØL˛¯∆¡‡aLºﬂIπÜ¯!W[±JÚkÁòÑf◊\'o‹§´Û$çzr@Î¬bÖÑ(\\l€Ç7n5ï|Òﬂì˝yÎïÖ«6gõ1ΩvÂ¥˘Ûm:p“·≈Â/˜,[qFò1*éìøk]éx∆º8Óo;æ⁄HèY¸ ¸tóá∫≠#xéöﬂóæ{ËkÚ€”VÄ[ÂË^w8Ø™v˚÷›◊„fe$8wr_Z±Ê7oÃœ.·∞éT&‚ﬁæx˝\n]‘va±6+¥êaÂÊ-∑Æ!~6:µû‰ªUù≠ﬂbzÌ™D‡hÔ‡Yü_“ÿüdŸG8SX\rM\nŸ:≠^O”Ñm`¥ªÆ”À\ZHaë#òwiÆ^ «¥4f˘\0∏ıPâ¡«ˇw@‡Á/ä ‚Ç\Zæø}W:∞FúËó>än9ö:∂Îú∏0ÓâÁ‚ÓjŒ◊Æ0,˘u„ròÍõ{ôæ+{õû9C&YQ≈„¿∏qõ9Œ\'sú)≈ÚpÎÀJÌÜç^9¬¬Ï›UèøBÛ™©@è”+ıÙ\Z¨—]vx{æÆÅ33∂C¶™¬úBáAA|É€£©)™°ôLmE	+,ÃæÉªe»)°®Uaì£ÓXt_SsKn„Œ©+WË5∏ùámÛ•öÚs◊®ŒLı!ÙıÁwûVáƒıÛ¸•ûév¨õ_»9€ªRÃªd”´ÎoŒ˛ÂTpÑ˝âü∑îÑçLºÆ7UØñïùπ¢lÙ¥Õﬁ~ç’?¢j€ˆ*∑∞Ë8/…˙ï?˛\\ˇÒBˇõïLú∆yuµÆÇì˚+ì•W(:1’óhîú™`g¶˝Ân∞v¨}ælûÚ¬¡√rÃ&<)™ãõÑ„$ÉQS\"UÜ8p¯çµ4ó°”™µT\'µ¿qΩÙñD∆wVV‹êz†xd˘±´n˝ºôNAŒlÖÿé\'(Æê◊À\r=i\'8|µ\\≠#µ¯√A¬r^ê´¥˚Øπÿ±.>¶™ï	Gπ„AË|îç˜êyì}π68<sÆ!ÅÆæ\"!;ª©î}‰î±∆úIë∆„yo\'õ“√\'È‘Ï∏a!æÏﬂ÷‹a¡&S\"f◊È?1Â˛ÿ±F$°∏|Í∑™\"fïÃ53û_|Sm√rç	∫˙’Œbï6b§? %ÄkÃ»rèımw%C–©…9õa˙éù–yÖª2ı†”∆i√ö˝6ûÉsK„or»ö¸∞vNòÒBQ$qEÍÁﬁy$T]∏Î\0ñ6ÃEVÆ±3‘´™\r;N cøﬁr\r`⁄ ù9kÙÅ≥,ÕuÓ√“©\Zän®=ÜqKıLÅÉwúÎç◊àÒ!V⁄”∆i;ıGe‚ˇãÁnû£&˜~≥µ\"nòKÓ)ﬂÕ¡ôG˙IOùë±≈Af\'LØVl›’ØˇÔ‡Ä«É=˙áW¸≤1ó∞Mò<\'MT∂sÕ…¿)1€6≠⁄\'-‡Mˆ,,°lÍ•.°‹kU|¸ÙYYÍ$ˇª8s÷ËCØ¨o§ÏÌk ´I? ¡WuÈ|Å“-0§æ¶RÈŸ?ë*P¿Ê{ßù”¶ñk¥júÀ◊Î1ãÔÏ*$)G?V}EôÑtÓØ©∏Q«vhv¬pS”ÑÄ´©/)êz∆È	Æà«$er-Ê@ÒuuZ.ªÆJÀ˜Ûupni™d§¿üÀ≈j%∑´o*¥Üb ÷VµéôãÁµ∆ÖõõAái¶f∞€B~}É∂NõÒ8¡”Jo‰º®]Ã1˙^MÑ?ÔŸ÷ü£¯<ú}{ÛÔ?œ«5\\{·Rë÷√y˚ÕÅœºø—|*@∞˜„=úPyMQ}tfˇÜÕWèÏ◊Dá≈ªu=@gç>4Eø}ªKÓjKxGŸúÿ{ﬁ>t“»à“úS∑µ[Z4pD\\w\'ı:q≈Hﬂ˛„ıÔò∑ù÷πg∏6,¨™^j«b∂∆#pü•SËÌ#cÒﬂØs=<ΩbCN]k‘cú.Ë¨Î 1ºÆì/Ú¥©ÆckkÍ’Üfê•*/hpdï%‡˛@∞ÖlB[ìw8Ø≥èJrì)qñVZMıOd?R\\E⁄˙F∆°^{≥FîgÊ4œ¶˝¿Y>¶m¿løÆ/\0zΩ™∂¡f∞ÖcQ©©Ê§aˇhyÒ4<™Ûb;˛Ú¯ã^zvkûÀËdg…Èm0ƒIcÌkä +⁄ıì€`¡Ω≥ ˇ∏µ„Îıπ⁄Ü∫∆∞ß_~ƒèeJƒ9\"ªè«´zë\'9~FJâE	„2T{œfM4ﬁ>ÓùuÒScﬂâ–◊‘U◊©\róöüj|;ÉÆ—KË⁄A¨#<!S¶ Ïùﬁ∆öº£›;Î¸èÛÊ∂ôWbo|™ô⁄®ázr◊É A◊ö-»8‰ÅYvÔ¨Û\Z⁄«ÄM‡6˝∆\'[eË-XpÔ¨{æ®ÂuÁ}_ñë—`‚éŒ<˘Û \\á‘…èÃºs0`ï>púÆ((Æ«hÆ9à€†b7\nÀ•JmwUËQ¨{æP¢à)M√ÒÅ˛MéHH¿ô›P/†w`ù>å°\\}√•=ùÜtÄ`z¿∫¯«ÔﬂˇV90YwB¬–Gü¿ ¯áØV6z\ZÉÂˇ˙Véø<6€8√œûIB„—7∏áQìávÜ–U{∏hÛJã¶%òyüÊüΩü÷WZÙjY…Åùø2fﬁ∑˘ß@ÔßÕ+-8Ægë˜s˛)–˚iÛJeÎ;d¶)ò˘Ã˚M£,Å>MŸB|S∞	à˜íZ\0}<¥t˘nQ+wÔjÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@qı—<4äü+jô‰¨.‹µ_Ì&n0ÏËJ7øux¿ÎS›aî∏ß±Pt…ûÛ¬BÇcºπòR⁄@cji5.ˆÌr]’Nh\Z\Z‡—Ö+ﬁ˘Õ∆Bæª 8‹ˆ¸Ás~O]òÅ5ç◊â¥Ò<\\.ß1Ù—”Xvµ≤q∏NR!gMrÑ¸ÙM%iÏf]QMC:!¶¶1ä«R+ÀKu>ëÓ‚ê(÷–2.P\\/î5ÍÎ\ZıR^/¿2}êvÅQ1ÅQÕá£=Ó•®Ê°ïåá‚‰9Ø6\r\n$•¥‰1é`∆qÅßá°«Ä˚C¯ßÀ∏\0pÄ˛Ä¢ß˚/∆>ãB›≈Í @èc°>¥•˚~<\'ËÌèù8Œ\Z >≥∑1jêãìW◊ˇ¢⁄ˆ_àÚ}Àˆû´©çô9™>ª>¢Û’ïÅû«“˛≠R„‡‰Tr‰∑õdFTç&<—GP!∑Æ®∂˝¬!Ã£¸H^’Hâ∫\\]Ëy,‘ÀgƒH„vÃº~∆çóó·√áe]Qm˚/:ÜKÊ+oeö“˝]Ô:ã	Ë)zCˇz+ΩóÚOE1r⁄NL’6‘´r,òk‹,‘Gc˛˛3:?7\'/qªµ2UÂ7‰\"£ÏÌ‚ÎeG˜]°8Á÷û\n}<∫îHõƒ\'p˛qGÔ√2}Ë‰Â’Z°M—©su˝áàÆÔ€{¨Ä?Èô¥ ≠â˛i\"ãjÁü≤§¶J¡seKçu%.WŸjÙËπˆ¿˝«2}âSå—à„ëÎƒπCL…3&[QT[ˇwNô>∆∞ﬂzΩüx©Ωêﬁ‡üö/µ7Òu\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Öe˙–’[˝ÎUn‹§)ë‹î¢íÏ:BM∆é‰‡I©Ü—UßwêE\rwﬂ˜Vv¬#UøhÊø1/Ωy„lÅMjt≈√©—)ﬁT…÷•õTÉ\'çOrc\Z≠4^⁄tÃn\\öã%µ–’‰¨=„óïÓLöÈ“˝[k&ÖrZäŒÙ=ÛÂ6ÕÄa£“˘ÊJÍ‰Ê\nd§;^ÔXêNVpQ·!¶,ˇµ\Z/¨\\WÓl„íb*∑/`ô>Æì≠\"ßÏ˙Ê7ﬂ_v÷o˙3}næh;~(¶´:µeyCº´ZrlwÌÙW=¸•$–°$˜\n~„¯õoîNJ`+i6Ω˜´sæ3~‹êø(ããa$[Hü_˛ÏãkO~˚z§Vu¸áU‰¿ÅåÏUˇ˘πa‡ËØ§!∂«é©#íä÷oÁ•áﬁ˙≥6(÷UY\"ë€ä§JÂo~m;!©hWÂ»ya≠3‘M_π«lca\0Ü·ó“‹¯ÂÛçóÙtπ$Ô¨z‹¢iI-–ÊÇ‚b©[’4A éÊÃú†‹ü{2;wÂSπ…Èé˚÷H˙è]©NyfåvkÎ«ﬂHb∆∫ú;xÁπ¶Nå9˜›F†Ùä]pÇ©‹æÅe˙†k%ÂîØW≤ú]É¢‹u*=FêÑÒ…‚q∏b∑õ˚ΩÉ±=[Îÿ£Á≥Â∑Jnk\Zµl√πneª∑ñ∞˚\rb`\ZAPjBàfÛF•^I5 ´ )6Ií≠•i¬÷?<(Ã”ÍµJπ\\Qv±PK˘™˘Å—< ^|coMÙ#^’Ö◊rµ≥æ\'‘*=ã,Ø’`<ä◊d?r4Yéi‰9˚™5>·Aî‰ÿ>örh‘\Zj©©≠0d¯æí”W≈…±⁄SÃ8vSﬁRÆ„8Úp“>, Ÿ–§ht¶C¬∆pñMjTóıq3Xs`7ï€ù∑•◊`ô>.ÈO=mﬁ}™))aîi3ÈÖß0]›ü“¯Qc“›õ~≤ËL[º®ÌıK˙5Ô•Mt1|fΩª<Î]√Vq˛Jëh÷,W√çã~¨9á˙Ê^ŸÑ	√ºô£SöÆ1ÿoúÍNÖ&çiJJ¬¥%{ŒâK‰FÃ[aŒô›∂‘©c4Ô*ƒJ∫©†»‡∆î8£ıD?õ>ªÂÇ¯0„ß¶\"/˚êL:r·¬v\ZH07îªæ*®oà˚{¸S¬6fb˙=]…çòûqg\Z”+còˆI◊aYÆ©†Æ†ú¢;uy÷∫rx,’é„›Z†g—Îıù¶[—~|1uˆeó§>|<œ_ΩÙ≤Zøÿxwf«|¥dÌGEcˇ%9P4‘è}è∂ÉjkwdMª˙Ï”≈?‚LyÎùdaWıY2§ìS-hÆ9Î£Íå9$\";ûÕ_˝IÈ¯&	’Wø˚øíio¶¢L=Ä ˛¯≠–;pÍlß’ØOﬁ·ı¯À¡⁄™3ªw›ÚÁßÊL˝ıkÔ_æÃ±1r…Œ∆ó_Y¸Eˆ€o˚gÁ(ø/–œ˙‰QÆ ;%ò˜w|ìé\\<$Á›‹ºSI€á,{‰…woåzˆâ4óÏÂEì∫˛∂µûì˝˙\Z¡îiä]y±™÷˝…EÓ?}VΩÀ©j©ÁU\"Hä’∞}¡#gGº=·‰åî˜l&ŒtÀòÏw‰◊	›≤YüULZË≠…ˇÓâü%ûÈ1≤ì“Ø<üÍ‘QNVËCym√⁄:o«™¶_ÑÂß˘û3(Üã·¸®yèó}u ÍVÅÎÎnc™(««â∏‹‡–›∏›óX\\’âZﬂiaLL+\ZêPtñ3áùk(Á˚Ö)/¬\Z4$âÛÉR„x9ï§¿À?z°íÏ–È¥jii\rM®ÎãÛÆ´1ª–»»0\\’H´ïjCà¿~”wa{¡ï\Z≠Z—PqˆHcDí´õøcb≈Ì[∞~eÎ¡∏D”&£¿|®-˝ÌÕ˛£C∏Âùı≈Y_òSáˇMïDÒˆ∂ıméÊºhﬁ∆~g¸üÑas_]◊r÷‘SÛÀÿéV®ÄÎóµÊôy·ys∫˙⁄™˙«ü\\î(07ŸLnæfÊﬂÙ\rz5VË„“¶˝vô>y9\no1áÎË‰Ó 7F@ö„òcﬂy£€*Ÿs0[ÿ”ïËI¨–≠U[∑µÄ^ë{|«ﬁ‚ÄÃ0ocÃÒõ≠ecb˚LºπØaÖ>å!HÉ«∫cÃ—7∆ùEå≤Z\r&Ï3!£>Ö˙+µ¢9œè3zßZÉMqvúıUé éá+Ù9c6£ãs}¬WÎãX°èOﬂ>2-iY“+Óﬂoüs˚Ìy≥æ>ÛÙØ€É7Ø8YUÎ˛‘‚–ﬂÔ¶ß}Û©«Í\'ó^tÈÔ≈í›jÁ„Qˆ›WwàÍv?VË#Z∑ÓΩüËAÈô1\"F1…ƒ0Så¡W0‹)í…4áhı:å„ﬂO˙yÅˇá80–G∞B©o|ï⁄ºüı≈û¶ á1∆`äå¯®)a¡+L·ê∏ŸY—›3Ó›ﬁ$]ŒpHﬂ√ ¯òÖs€L˛Înr◊Ø>ß&fç˜cµü2x”ª}]ˆ÷’è\Z#ÿæfg>#Ò≈˘#òíuüûàùÂyb«eÒ†–äú<µT˙HÃ˘◊ÉÜ˙]ZøÍë8ÔŸπÉDDUŸ™¯òÍƒÓC$A é¸∏ßF=aºËÚÌ†°˛í›€˜Á;åõy3«8Ô˛ÕnŸ$OõàÔ[ÒÍ¸çÙòó«€ﬁêù>yP0*ærÎÍ+…ˇ˚,I)itÙ≥ÔÛ∞µr9ó•íIq]É±î\nZèÒ]D;Pé±	é◊+›b¸ﬂΩ©H.ª÷(tÂT_ì*dL/õûT∏5Ò1ñÄêú0MŒ”‰õ¶‚)k|›Í7_7™˘Ê©x˜7hÊZ∫c˘Wı2ö∞\råv\'X<Æ‰B°ñÚWhYv.<}Ôö(ä≥ÌÏ€eö`{é^ŸPy[r≤ZÁÈâc\Z…œ_úÂÃé8}Jqç¥∞úìißëTq¸ƒv6kJJd⁄0·–~àç¢˘/è5∆«‚≤Á∑¶{GeN|º]÷˚4ãô˚lLªÑÕ≥œÜå6o˝zMËü\rxdŒ\0„^t‚Ù÷d„t¥ˇºm‹Î∑tÔ\\sZ‘„gˇ¥ŸXœbÖ>¨ò¢◊«&·=ƒX°èﬁåÍùµzh∞T]ÕOnzÖ_ÙZ@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0äˇÇæHÁV|~Ω\0\0\0\0IENDÆB`ÇPK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xmlÌ€r€∏ıΩ_°rß}Ë,Ø∫´ëvúÿª›ô$ìŸ(mg:ùLB2ªºÄ,ªô}ŸÔÈWıKz\0^ÃªHâùMÚ‡H¿9¿π_@ò~Ò›ÉÎÓ1°∂Ô-%]—§ˆLﬂ≤ΩÌR˙∞˛^ûIﬂ≠~˜¬ﬂll/,ﬂ‹πÿc≤È{˛\0∂G·ÏR⁄o·#j”Öá\\LÃ\\¯ˆb¨E\Zz!ˆ\nG({t\Z£‡46√¨)2áÕ‡¢€Ê;‡4∂E–æ)2á°¶—7~S‰Í»§ÓàŸ9*€˚y)›1,Tuøﬂ+˚°‚ì≠™œÁsUÃ&õ	\\∞#éÄ≤L;òoFU]—’÷≈5•è√¶IÚvÓ-&çEÉ*hïﬁo[ƒ˝∂B4Ê\"çmC\0g’;¥ö´wh•q]ƒÓ*t2Sﬂ¿§¯ÒÊıì-∑È^6#*ìÿAc6CË4æÔ˚	©!tPAÆ°i#5¸ûÇﬁ◊ÇÔâÕ0IÅõµ‡&rÃD‚æ[&4Ä”UÄêÒ=7”ƒπ hÇ°Ü”	0µ*ó˛˚õ◊ÔÕ;Ï¢\'`˚0∞l{î!ÔI2Ñ+°í”±Jp‡ñf”<`Ç∂åÑ∂;Ê:’ÓŒgc–-±¨RP g®ÇÎÉ„…˜6ﬁ#e\"yΩAÃs!¬‚!îéõµ∫¶ròƒç¡DûÇ<Ÿ&yh„Ô<`rW$@¸`bÛ)‰¥EfÖLÙµ±ªw≤Ÿ2@©ÏR–6Xµ,RÿŸ`N‹áfÀqKˆ≠M~≈úWõîYôÚ÷?©|NÊπ¢u¥S*á“*Nÿ°≥S5ÿ@‚ñ7»ƒ≤ÖMáÆ^ÑÅ7Ñﬂ9›KÈoê∞x%@`}ec0◊vó“Q‡”?ß`¬iêYí√ [ÏÅ\0 ﬂE^\"∞ô	ëÚ;4ë‘è»Éd˙Ëﬁ˙é§÷ºFw∞Å^Bl8sˇıŒ¥-4xè<:¯‡ŸP·J∆K`KêgØ’Ùü≤4 º≈˚¡Oë ˘ ¡u¢‘§]òSBP<^Ω5›€îûºı‡-\"ƒﬂWä$\r‘@çàR´2\ZG;be∂)ãuO?s6sãùa≤_Dypc,ÒM ⁄`¬l–o8º∑-^¶LcfLl–aX‚\"«ﬁB$qŸBéãWÊy]v¡“óíCdv+I—uàHÂ™îL®ﬁúùÎÂ®\rãDG„Ìö2é¶vb;rb<’ı?µ¶ÙÂ9(çgµîŒé°ÙUÁîéÕ–«uî\'˙l÷û‘Î3u6çÍHùi£#(ΩÈúRHÌ≥I≠˙Á”È¥=•Wz©ÿqÚÑ¬PöÃçøêˆ˙ö¢\rg\'Fo}ba\";x…◊Û=ú%ˆˆÆdò˘Aq÷gå◊Ùö2ÿÄ˙ém\ræ—ƒøˆåñ«∏çËnL–ªÇ»D–ñ†‡.ûÄ~N\"æ»!÷_0ÇΩJî°).\0’\nÒÍ>µô®ˆÜ |8MŸ?{`+°¢?©ı‘∫›≤ot¿>Ø^ã.#≤a¶(\0%âAjˇu=`ôÃ áeË†êW2…\'~(´&r2/–õ€@¡\Z{≥]ôcn—Ì~í.¥:]‰\'Àt—\\ÿ£cÑ˝∫s´õã«ˆ8–çXÕ-1ƒäß∏g5‘q\'≤+≥.!”®`ƒ^¬÷øvîŸõG†‹€¬¢{à≥KiÉö™»øLuLz7Âg>ßΩã∆‰mwhCVX∂ò˛Œc®πæy&röı(ßYçµ‰Á⁄	a÷JÛ>mÂÇYN◊Œ™€WÜ2ôŒıﬂtU°U«wjXÌÚXû˝í<÷Ç˚£ ¯ﬂ¢µuÆÑMÙ®6£qıÊÿñÔ\"∂ı±ˆáS´≥˘)ôu~RNô∑ )z7-≈ b!<«\'K)>9˘B*e˝ºùK€œ\r~öU \n”-≈.–BP›ÙΩGÚ¶Ïûßw8•U:ÕŒª©Òõ€y{•ÇEÜ\Z=\0ß+⁄P◊ÅNîaÂ±ÂA‘…|8={™Ü0–NéLüõ-u”+]&≠ı#\"„|]÷q\":∆\'£q¡¯uâxÑ^Jﬂ~˛m˝¨˝—–>\Zu?_Xı◊ã\ZG?Tâ¯uú«µFQ√\\õ7Õ\r≥a≥4¬/$å&Á\r—9Õ’ı3©vûÖâ»®b0ıƒ3Sd{>qÀjºäF\'[eó ü≥2Œ€bÜ∑H¢g…ö¢ç∆√Ë·k4=OŒL	Y€ kèÖñ¬o≈»ô…gõeéÍ/◊¸Ò5˜ÊW·{zûå‹îán\Z∞fûó\\¬*9ÉNÆ•.µÅà’∫6ü[Èô\'Âa*Ù}…l„®m\rÇ„∆pÎ[è1òã(√D–∂ù\'∆S-∫ùœù©\rGµ]ç3‘≈b≈◊TÿK*<ã?ØDzTˇµÑ˚j∑=€ÌQg≠˚%öb]—gzıπŒÏÚãt&Êø£—\0}§õ|M¡âJv:cÛ˛’O:Òì·QG«\\˘Sœ®Ê∞Õ)—÷HüìıZmE0⁄Í¥ÉZÔ5øß>tµ$6:Î#±Q+ôâŒ,ìjß˛§Uº0~ä¥*≠•[çV,N:d±ëÚ˚dæp<—Ö7<õ+íÎ¬ôAgÏ}W◊ÖÛÜÀÒﬂ>Ó]T2ÖFÛ˘Y∆I¸\ZíÀÒ˜Ÿ^óYoè~πR+Y=hwMr]º$⁄ÅX;Ω&wBO≥.^Ωw≠clß|w[û»˜ÿÿ‡˘e¯Ó∂`º¯sÚuÒR`g¥O˙≥vñÇO+%ˆ≤F˘·˝≈8<KI◊å√ﬁµ€Æh´$∑≈éÌ ® #°«\'ñ ôŒî«÷ÊeF˝Ÿa9ÍëQµxI©ñÔC∂⁄b„vÈ˚†ì$2ÁËK…f»±Õ¨ò¯«Xà—|z€•›cÈmÊÎßq“.ëV&˝¬éjÂ`¢	~ ˘¬˜IΩ‹â∏\\hƒMao|sG„ß*—d8è≤Ö©Ωı¢◊π$WCƒ)˛˜{OÔ§)ƒêe”¿Aè≤øc‚AÅÉÔ±ÃI·t(™gGØÈ*ﬂ°≈bÎ¯˝9ß≠¬UuÍ\"◊·Ö´•Ñ(°±=›·õãwÌàü—{w≈˚|¬±4b4ìAçﬁ!R	≠\\Âˆ:à≤-¬´∂◊mnÚƒﬂÁV¿éS#˝È=i»Ÿa9|ÊV…Xß©©¥∫ryƒâtpeÁˆΩ%F<K√\rêWÑ‰Ö∆ÍÉg«s®UÔ‘M◊8|ÑÅÁ@Œ;xΩ2óVo1cJ-!j≠È_Ã Ú¸„˜≤<xyÛ√èo∞≠b3	Gñˇ˘		\'˘•gyñ\\ü¸¥WËõ.˛+K´O÷ìÙNTÍ´ˇ˝˙ﬂÅœ«Ä˚BÔ4Aø“ƒÕÍ#››2™≤_ÑÕ›ºΩŒZ‹fÿï1™ßJˆ+%ùQ¬/h˜∂wbÈâë∑4ÁÙH›>3©∂VØ#b!^Ê\"®sÉÜ∏®«„}ÁSŸõ-0z f(íh	9·ÁsÌy‘é◊xÉvé∏÷˝.æ_√ø|qhO]Z-BP|\\äè9c¢sπ%˝\\™™Û ÊØWÎg&ù{ƒûÉ`~ >•}àfP#õL*Ru∫oüF|Á°Ê<‰®ô35s\\Àˇç’ˇPK`˜√u\n\0\0Çc\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xmlÌ=€í€6≤ÔÁ+XJÌy£$RwùÃl9{7U«O6èS	I\\S§ä§F£l˘e?cr·4@ÄÔ\0Ii&Nú*«\Z@ﬂ—∏5øˇÎÛŒ’ûp:æw”3˙√ûÜ=À∑os”˚Â·Ω>Ô˝ıˆæ˜◊k«¬K€∑;ÏEzù\\j–ÿóqÂMÔxKÖN∏Ù–áÀ»Z˙{ÏÒFKzIáäKhg≤Õ)∞ÿ:¬œëlcõjãVÚ#S`±µ†£lc<õØ}Ÿ∆œ°´Ø}›Úw{9,û]«˚r”€F—~9è«˛q‘˜ÉÕ¿X,Zõ l%p˚C‡R(€\Z`ì¡¬Å—7vá#$ãÅQÚª§YÉ\"îìj¯¥ë÷àßM	k¨-\n§uÉß≈;≤Â≈;≤≈∂;mKd2‹C%˝Î˛√YÇùÏX6≈*+pˆ“d∆–b{ﬂ˜TIÉÿ@)∫Êp8ƒøËc%¯1p\"‡V%∏Ö\\+·∏ø+b\Z¿Ä–ÒQS¢K{ûº˜É(Ad-Ô†Ä;fb^€hÁñõ©Â†õ¿∂Aù—\0L\r]rÒª^ sV`ë\0uCuM(êËß*√ÅIÃDrv™¡&q˚kˇ‡0U0‚Á=RÖ\\⁄lôÍ!eãa8ääòÛÛÄ‘Èƒ7É˜a”É0%ôΩ[>ˇ¨}ò{÷»¬∫ç-7º˝>ˆI±ˇ&»›Ù~KÊ±PaÇì‡`;«=›Ù˛Ì˝ˇò∏†ß•∫$∞˙{@%®q‡ÔêóÇÿ;ëÜ˛Ñ\'Ê∏PGJàÅÊg=<ÌVæ€T#¸Ä∂0ÄQÄl\\S◊˛√¡rl§}F^®˝‚90ã„R¬`ê%OäÄr¸€t\rZjÒQ˚ô	°òÆ\\\'B≠AÌ\rÄπÒÚÚ°√£Ü≠á÷>¢ è•,Å$¯!Ö‘†Ã Yyr‰mºFóEçºgÜ„&@˚≠cı8,˚≠ÔpSA‰Ä4◊˛Ú•∫øè®K|ù¸Óiƒi-√-≤˝£É±Èœ7Ωaﬂ0ÊÜ„÷üÚıÃ˚:ÑIX˜»g†o˝¿˘Õ\'N-Ü6∆U–OM´\0fÈ~s∞EΩ2Œπ@Œ—â∂z˙F¡APî=\nÂ°»¡∏äÄÎË˘d–«∆~ä‹˝61Oä∆*¿¢ 0ùàx\rô⁄	n;7=7–£UJQœ∆d>%+ëòõﬁ\Zπ!Nd≥ËÇøâ&ï£ùÄºs‘B\\àlcwÎª~¿∏ATÜÜŒoÄ©aÓ#ZÊ\"os@(Ú\\Z`¡å†?§(!Ìtàê«[3ﬁ∞x›oœœºäu≈k<ﬂ√˘.IúÍ‚ÁíNì⁄Çnì:⁄Òôq)€í1∏Ñ”ΩJïÊlO˚-ˆË|Øª»∂q†Sl®∫ŒŒI(ê‘¨˝¡≥¢C‹!±`ÄtPêz’„*£€ò†GˆGã—ÏliÌ‹CœV—@Ö—ïLAmïåtÀ’•xÓæ®B“·µ*ò¢;’XQ°0wY-9çú∏™ô9†˝!‹f@ZòC)û…≈¢¢ƒã˛ïÌ\'ö9ƒƒbàÆµX\'wzp(…ÿ·å˜z‰op¥%´jbguã∆⁄˚¨∆FÅ›+u\\|.\nC@,¶÷C¯X∑r)0•¯W˘ÑÒ˚±…|ÃYoèEñU◊ÕŸƒÜU&ÜÇR˚¸FPà\ZEx\0∂?ö√«ïoüxá‡T˜.:ÈgM¨.’(Hvıb=+Rß¨‰3BÊÑÜΩ¥–~˘\\.“Zö«»Êü6ÑzÑ(†à—*∆µCÃç`¯{\ZNß\'f´X˘QD6LÜ˝·|49œÉ∞˙? 7%ŒÑFèçë{Dß∞Œ*ìcÀöåÃ∆E&–dn+ÏHañ\Z7?ëíQlB3.¢\"2∆R¶™ÇLÃú¥12⁄∫	óÕj.õﬂóŸåı›ö˛)ò¥2tƒŒf]Ø|◊NA«úÁ¥∫	œG’<Ω:û«´	◊Ÿ\0’ååìâÔüá0r÷\'–EoC!,ª§oKKJW»˙≤	».(èKæ“?óê€∏Zn„W\'∑ˆÁV˝ŸéÆ:ﬂÛ%ÛŸ†–ÏÀ¡	≥ct⁄ôëê ∑∆¥TÒﬁ)Ñ≈ÇÔ:	73 /ìc\"\Z–C0-;G¬v`u›DØ*bót)iÅŸâﬁP3ú≥£Ë bLT©ÛÍä=Íõ”©ëUFÊ„Í’x⁄üååEW∫,«◊˜æ˝…◊Ó˘˙@˚ƒdﬁípùky{\n¶ea:2ÕzQhÙ∆D|ûO∑t√dæ•5‘IÒ„˛°¬\"îSüYÄﬂbçV dy4éGî9W\ZP∆·w<ù8 bIÃ1ÿ$¿[Uœ˘Êâ;.Ô Qõhﬁ?\"´R˝)òñÖÈT˝ÂH˛ÛsáÆœ°˝ukoóûWúH8◊nœkãˆƒÇ‘v?¯G´›èÆ-2Ω[Ud•¢Ae˜®TåµXÜrâÊ,≠ü´®©d‹∑ 6ô¨Æ4r–≤Ú7êov´Næ{XJèÑpDqÑÀü„õ(≈Œñ¡ii†‰¿Ç¨+ºÂ\'¥°C|§&T<Å—“\0©ÓÀ’Ë◊_ıÿ£ÄKAƒTëÓ‚DMÇµj\'@¶:_Ü,BåCi\"H\'®IÏëÑÛN—u÷ë“⁄Û¬dlˆâ¨¸P J÷@KX˙·X:ñŒáØî•Ô<[∆XCs¨f°bg›ıtW⁄?qwN~º>≥Åµ–pMÑïóFíŸFóèhhÄ<kÎzº.\'ΩßÕí‹8rÿÖ¥ˇ7Ì-æ‚@¿]\'Úc©‹c[OzÛ◊*Ë≠\'–¯á âU‚*~=HáU,ÔÔs9™>œò,ÜÊ`Á´HqÈiS®-ÈFZ◊ˇ≥4ª_Éı‚ïUÜµdî<É~˙ÓOﬁî˘0˝A¿üõ>8á†\' 	ˇù={öΩqM/Ë‚\'Ï28˙\'{	f™ìõˆ◊â »^&kzñ°¶¨o…QÕuÙ\"£é_d‘…ãå:}ëQg/2Í¸EF]º»®∆∞Îay7∆p°ùÖwsXGlÔ¶Â[á»y‚[t„Éﬁö˚∆dpÂﬁ/<¨◊L#˝\"Zçjb„^wπ8∫Ç\ZNìÉΩAN\nõ›bLÔØÍ+çË\rSc13…ÃYFé±òvL–Ë≤Ò+≥◊#h|YÇ&ã·‚∫M.K–l>_ó†Èe	ZÃ«”Î4ª$AFﬂòìÎ4ø,A#–πÎ¥∏,AìŸ‰ n;tO—lfv„Ñj1  M!Y›ØùÕ!†Ìµ§Bg˚Òk∂AÃÉ\'äo‰≥j6õ”<`d“ÁTO»=W¡\n˘0°~>¢ßœ9ƒ6Iî£ì˛¯kyZpºπVEJ¡6]Ã~¬≤T\'c¶!($∫Â·3)E8óûW≈/⁄·WåŒz9Û”≈Îà’9û–\0djﬁˆ—ﬁŒO˙»V(ÙÈX:Ø([µ?’Ã]Â¨yh¡ˆ&b}ûˆçŸåﬁÁàﬂ_∞≥ùiæãQ¯¥@˙˙`ÿSà#ŸÕ=Ïº™qacVŒ7˙Ê‹ú2CTAÂáŒQˆÁÛπŸ\0ï∑‡ p<[4@ÂÆsTÃ˛b25@ÂMÓ8ªn( „ëÏ•1e¶€lkπG69’âr”„Ï?ÈÊ5A|≈£®*>œ&G„¨úΩ^äHc±R÷y∂òvú-Œ‹\'˚H£Á\'\Zø´¨Ò]3tÁÿ∂ããyj,∆•LÕ◊©sµê#í\\ÆkÀŒ<¸î¥†? óÎ¿.œı7≈≥ﬂe∏~Y—F≥/Î?Æ…„Kk∂—î≈ï\r˘êy Âò‡[b∂ x%˛ﬁ˝¯[… Î1ªxBî\\òÃ˙Ê¬ò]xab\\`abˆÕÈX}ab\\`a2ÈM∫∏SE•$î©}BWë¬-ÊäŒ6ˆïsÕpj<üñ⁄iæÓõ	ZçÎ.\r^	ó_:h5Æ¥æÆ_;l5Æ∂æ.KÈv◊õÈrÓUÛ∑œe%]nƒ’‹ì„p£}R0üæe¡∂2üvNÎ˛S£ƒÏ1n◊è<£ôêÔ\ZØ4Ô?µKI—4Û\0{{bàoO ü˙âOOåÃ€ëöw~ÈóE˘îïôó)ÜJﬁè˚OçrKdîß\rÎdôtY.¥À‘ÌaÚ‚|Xã˘nlúŒws˜Óµ0jzEF%Ÿc”¸öÇìÙ‘À tlòKÜCc∏XÿbÕŸªfaJx9o≈ π\Z\'g/®rYB_å	Ûº∞‘ﬁ&ù´úh‰ÿò\Z≥\Zçd0≤÷›ŒzùËúÃ[WÏ%f÷‰°Î$9_ÕzÌ…<	Õ§ï˙¶e‘.ø^K\rœΩñØz´_ƒJªYPîi#}>¿WŸ∆d¯ó∂Êøh7-ZMsEjó…ÆCŒf’´?©T∞\\µ¢≠≈®p™—ä$ï∆Q¡ÖΩ§±u≥‰Ëfˇ¬ÏOgãÙπWiÁ‰ºõ⁄‹êCÉπAEJç÷;Ÿc2˙SsbæêåÆe›,äæ£∏√-¿:\rn:7Ú/∑ ∫™æI“˚ê?º∆πœ%•8.ób˘.Mô|≥˘Ö∫\r˝ï2pﬂ?‰œC/ÀÆﬂSÚ«ómò¢í*∂ç¶\Zç˘ìºÓhî≤â•>ôv°ˆØg≥ˇ!”uF_ÌbT›!^ó5π¯Îz¨y»≈C◊c¿k€#+ËΩaF’˚á\\úı«e´◊Ú€√ïl+p.sçJC™Öà•á*C^\"Ãí€Tv«Ÿ]√6ªÇ˘]¡ó$<VïÎﬁm|uÅ›‡v‰µ†R_.&Rﬁ£e)´KÆ›äå¨dzKè¨ ï˙ï!/í´/Qîq≥€Ìßá¸˛Kg¥]âSmäV°`›Ê3s]íxë]#9Ø™°4kÜãN˛!J≥`ø3z@ô√%Z√Õ˚Êt6J.‹nÑ„9£?]òÂ91ÿ(Å∏˚ÅCæ_œ“å¯A \'ÊZ:=O4µ.HAoŒ‚4aÁ:~¡7ﬂ*π‘[ıı⁄‘∑ï#6Ås˛Ïî5$ˇA∞L!„QQÂ\nÖ8ı\nj,0I´ì\08úLJ\0c|≈l¢πnVÿıèô¸µ\"ïCE=KúÅ‡YbÙz>cÍúﬂ§%\0!Ê˚¿Ijå·p&|.ÜSòÂ€!\r(–h1/\0Bkí˝•FÃ.-≤Ÿ‰:HúcE|+úS¯òìø“Ã™x´™»,8Ω[z ¶ß¨çï≠ÈG|≤_X\0eU!˚}I¶¬≈⁄[b	ªN`◊$YˇˇL˘†[Ü¶$±mbÔ}¿~‹˝¯Ò)(Õ\"I@\ZŸºÑ$û˘™ODW‰Úu‹·Âká«´j^”~5¶7(“Ê¬é$4dÙ™5Ñœ`E\ZíØì–È)ÒOÁﬂëJ≥{±\nñÏåó∆]–R\Zõ˝ÊèàsAò#Õ2°Ìyû¥SrL^±Z¬^¿\0¸¯Ò?˝¯ˆÀlG\0k⁄Ä€’À˛F≤î&©Û$z\0≥Ù‰°«Ω[_©Ô˛í„ßÇêı/˙ˆÎ—±ø∆†Z©Ù0È›Æ\\dkJ-òÙh2<!/bà]lETEnz÷! ¸Ω[Éu-Ä´·ß\r\ZcG◊<ÖŒ/çÖSƒJIJÁÃ˙*¿ËKFDï*≥ ¢∂¿©¯;êv‰ïi3€ €p~’rEèû\\wc±ªﬂúΩÿïÂD\'ıNÿöTΩ·ä÷ò|ŸM¢#válP!\\C©Î¥w{á\",Ø3%≠ôΩ´»Ÿ·ØÚmÁÿˇSQÒhÇ»8ı	˝õ•Aâ´yﬁ«∏Ll»jRMY∂êRË˛õÃXµ\r~PmVµ¡]∂A‡3=`◊≠ …‡ª-Kö	î}∫#åHˆœ^„ßÒ¸îIeÏ\Zè=KîP£r∆∞w˚)ˇ	ûøX≥~¸D&QÕÛ˚†·Æ\r≈\Z0®‘GU5õ¢Ígº∆0Õ[∏+y7F&q}+ﬂˇBæ£”Ùª,.z|º≥1˘2Ÿ„£92ç˘t>4£9	ˇU£#1Z©é|’2£bœñ≥#ûΩmÃ3¢˘Á¯QÎJﬂ„cJÎªXRI°Q{Ì3;SÈ5Te∑fU\0Aﬂ•‘9ò\Z?cÖÈ“V\0\'+û’I°¡X):1 ˛Ôøˇsπ˛ˇ{‰∏GÈ=zVcJ¢P’ßÂ\Z=+ƒf1í)È¬dΩ”wä‹õäÒ5&≠øOÜï±†Q\Z\ZJ±†°\Z\Z˘X<E9tù\nüf¥à‚à4j8° vù\'ú4;≥¢Xv‚îçÅ—ø7kÚ]Å\0ëÂ‚2∑ë2È’ÜX@‚ªgDµ=K> ™çt:pÛIîé-.‘ZÀ™9.ç#∆Œ¯8jå˚\\∆Ph†éÌÃ‘÷ôï¥·¸m÷.F√∫ ØÖX∆◊Ro ¨.8êCàÉ•(RŸôAåcø®U*Pª∑æ°Ã˙µ∫t´-U∂\0áÈù˘Ü`~ì…D7Ã—x:ù∂púr!Û{\Z˚ÊŒ<ìü¬1@›…@Ê)ÖÊ˘EuY7∏ˇPŸ«H¸ÇIÊÏÇ€æuÿ%ÔÕ¬€ˇPK6“ƒM)\0\0ß\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xmlÕZ›n#IæÁ)Ç2±ùø±ôd‘vÏ¸˘ﬂm;1pQÓ.€ùTW5›’±Ñ‘õŸïvaY@	òù]vóπGpπŒå4èí~Ä~Ö=’v≤Ÿƒ&¡vI‰\"N‹UÁ‘9uŒwæS’œû˜M≤tÜm«`t+{ç,a™1›†ù≠HMÕ.?ç<ﬂ˛ﬁ3÷n\ZNÍLsML˘≤É9á!ŒLßNrÙx+‚⁄4…êc8IäLÏ$πñd¶◊”í∑G\'Ce£o˙ƒ†ß[ë.ÁVre•◊Î=È≠>avg%ñH$V¬ß◊C5F€FÁ±™F£o´bå›(Fã	ï≈£—µï—ˇë•Ò\"oπ&Ÿæˆ√µ˘€œ∆\nFÀ«¶Õ“¯k±¥≠®Lû∏w„µ»§yﬂùSáÒäçë ¨»ı>∞‡	a¥Ÿé>[π/‚Òbs∏Õe»m:ÔNºˆ4\Z_õO¯6:›…Àé«WWWgì^Ì≤^Îc8›E¥Éù;\ZZååhdõ€.ûM«>MŸ¨Á‡<”Ò4ÈmDúGã_6ëµlP˜±~ﬂYì,ú©aÁÚ}˝ŒRn¬Ÿ\"ñ„≥oÂ‘ÿãE◊7◊gó;-U÷6◊g<«hº¯d	≈.<µC©ï©9\"2pÊmeßÁÃúöÄ±ŸÑ73Uu7⁄∫ÃÊsR\ròÀ”å∏&Ωõ‘ãíûbÏtaY}ﬂ/Y§qfO^{,:„Í˜ù*&X„Xœ⁄≈KüÂmtôˆxXì@q||9}·⁄àCq˛_Íj	 å+î2Nù∞uÛEF(øÑ:8Ö¥”éÕ\\zEPNB%*ÓÛA\ZÓ2¢„ª1≤à\0’Ï∫é©P&KC∆¥¯@∏LB·máÕ‚]ñ!‰J5\0YÿŒ⁄Ã¨bÓﬁ-\Z3#Õ {ë`D∆laΩ:p‡AîL’0áibX÷KÜ∆]{˙FÃÎ£$‹©,æÖå≤QZèıB`[º≤,aàsUΩ¿Ldwå{%rZj¥ƒ˝4T„Bÿí¨	7€Û9xc-ePd\"€\' ﬁOVbÖnÎ®kô\Z/G˚«95ﬂ/ú+ºØP£~Æ¸˝îcırÌ‹Rè±sΩ±NsµBæëéûÁU≠ó´àf¢ı¬°\rE…+vg<±£d•[Öø2:¸™ö	£≤õçWï~ö¶¿ŒıhÛh?Qãgπ∂ZwõÉÿéöÌ¸yf5üé©Õ£´yî?l≠¬\'å=>™X≠¯Z¢∂õË{]tT><é\'z¬_%5sÿjdÕ8qõªŸ_ÈGÖh´Qwıùh/∑£8˘tØó6g-ò€‹≠˜Ù›N\"R>‘ˆ\nNÛ®IZ0÷t⁄4+D7…ISçˆoçè£FÅîTÂ∞ñMe ÒÑ´Ô÷◊B[ V˘∏°ìZúl4U+U∆vR;j4V.◊+ÌJ∂^V3ı„£h¨T…‘äJ¶RØe˙ŸF6QWOyQçÈ≈Tá2g\r€·9Ë˜Å◊PæOoB|Ò\n,ñ À¡a±LcBD,^Mçˆ≥Ã÷p€,”Á9åt)Ê®®•p—Wç\\:Ö=Y]güÊGZ±æ&5\'£π∂\rˆÏ @V¿ù43Mtèé˚Ëï«¨òqX+∏Án◊µ8ÚÇ≈û RÁêØ\nÿØ∞ﬁ!∆2H«\0Ê+∏i\"ßHã≠ììQ^¿ßÇ	¥g–∂èbvÒI±√\nåßë%å®¸`éS§ÇXJ0)«ê^ÅÙfî‰xLò|»,$»üÿç:πC|œå€Öˇ…VspëËc&ë«ê§öå£¿∞tàÄR®tíû:êﬁ¢ä§—\\∂•Ú6§MXOÇÙ0t+\ZqÅ„\nÁ∂∞\nÍ°‡ 2x•É(~õ¸ñî2•Ë!ØZH˘*MT_ S4´0v◊]éëÌçÕDbs.j,@K‹è\0rçNÆ¶ÓB|,C≈à+qF∑êÍ=`≠4¢\Z&t|äìÓ¬˛h`üBµ.$ñﬁ∞a®ù%ÉlKJO‚ƒÉ\\o\rñé8\røäM ajò+¥C€m»ZYMø¨VyüñÒ≥°˛è≥S& @%Ü*‹¬I!jntâRÄIs$Õw˘©¯¨2◊÷ÓÇ>ö¢V—ﬁ%¨Ö»Œ¯Í8ï±ÁEóãíü¬Jé\rLtGf\nªÍ£Àﬂ\"MÊ» aì€Ö	ó!aòÒH˙\"Aº–.LZ¯}K’DÑ\0wuJ¢âr ≠íéC´∞„CiëeÄã–¯bõ\"Ú@˜;|9á†AqDK.’∏+ãıÖ˛⁄mµ2õi®∏úç*÷\"LøŸxlÖó7=ˇ=w—é{ø¡∫&C\'h÷÷‘˙ßH=(ÕxÎàlò)ƒÌPù¬–ãâÚ2e]ﬂˇ·è˙$˘≥Áø¸Õª/ﬂ˝Î ˚€ï˜Í É?˛}Â˝ÁÍ˝æ˜ûÔ]¯ﬁæ˜ëÔ˝Œ˜~Ô{Ω?˘ﬁ_¸ãó˛≈ß˛≈+ˇ‚3ˇ≈◊˛ãKˇ≈õ`¯^0¸ ~?Üü√ø√ó¡Û`x\\æ\n.ø.ø\n.ˇ\\æﬁ|º˘<x˚è©’ˇãY?¯—œÒÎw_Ω{}Â˝ı ˚‘˜>ÙΩﬂ˙ﬁ«æ˜âÔ˝—˜˛ﬂÜ√ØÉÀó¡Âó¡€/Ç∑ØÂ\\|>9‘¬7•T\"àì7IV\n1:˙§*g÷5aítTr‡:‹hƒ÷;\rÉwÛà∫à§ƒÖÉ§ ÉÌ¬˝ÛÎ8´\"”qig9ù;Z^çmFó´¿M∞3œØh7%Ïë\0PæXÿûÿ7áÎsYÖW9h]ºÆ<‚›ê\'¡\r√‡ìî8¢=üƒ ÷V£â¯”9v˘°Kæ9c6ã˙”Éˆ1ü l∑ëKdxvB¥»–bYd\0]ñ-ÿˆ‚}-∫ûQ:Mßd RSﬂÜYπ˜∫È ¥q∑øPKåÖ≠{\0\0 +\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xmlçîMo£0ÜÔ˚+,∂W0&–4VB•=¨∫Uª[©YÌﬁ*«ûÄ≥ƒF∆ÑˆﬂØ˘âzàƒ¡ºÛÃÃ;√«˙˛˝X†òJjµÒHz◊B™l„˝ﬁ~˜Ôº˚ÙÀZÔ˜íö◊GP÷?Çe»•™äˆ°çWE5´dE;BE-ß∫5¶–9MªFΩÚ^Hıo„Â÷ñ„¶iÇfhìa≤Z≠pQ¡\'Æ¨M—QÇc(†ÌPa<≤≠√kMµÏ‹í÷zj‘‚ΩÈÆ]Ü1ÓÔG:3Bü\r‡ÿvôe˛IBÛ’C√¯≥ÖG^:n∑µëÆ;3(0Ãjì>…ùÅ_Ä„ ¢ ∫yí™~˚{w˚v£V\Z}\0nqÜ«Ê[-·Gk|Qr-8µ“ê>H0;y@ú@°∆ùj§Kaœ-≤⁄¢,*\\–∏â)$\0ùt·JπC¶◊x™‘€ñJZ…\nüË:=2ÂˇëÖ{>Ë§8Ç<Ä¸\\≤}ÖÓŒ≠∆w[É¥›†Ô.≤‹í%ç\ZÜCˆ9◊N4$óê¯$ŸíòÜ	ç¢ŒdOuô•ëÓi‚á±í-!4â(IÇÑf\\[æ`*´Y©*¸üO]—IÍÉp©ÃÁºÄ*%q<T∫ú”¢6› ÈÀv˘∞àüìª◊ã¥âò˘·Ô>“G0⁄=òó\\≤¨Êæ˚xÎ{\\Ô3yPËïÁÓ†≤nÄÛ’Owe]ø Jé:›≤]>◊µ≤o·ı¢<∫¡G1DΩkﬂæKµúëd“À+Û©ÓX∏—Få‚ÚvyÓxn¡åë€h5Ñî{ö\\Z®J∆]üK2q$&¨+ßÿKb»vÖç˜CÌ5\"◊a—uÿ‚:,n1|ˆ˝„œ˛µÈPK©1¸6Ñ\0\0©\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdfÕì¡nÉ0DÔ|ÖeŒ`†óÇ9Â\\µ_‡\ZìX/Ú.%¸}]\'™¢™*Õ°«]èfﬁhÂÕˆ8ÏC;4`kûßg⁄*Ëå›◊|¶>y‰€&⁄∏ÆØ^⁄Ûjãïüj~ ö*!ñeIóá‹^‰eYä¨EëxEÇ´%yL,∆ºâ≠FÂÃD>ç}ÕÚ\rf™yú%¥N:º9ç0;•ø£:PòÇDÉ	L⁄ÜLã˙ﬁ(-Ú¥£&)¶˜}‹ÇöGmâã-Æ≤Àc1´ü•£suøπÖá_5R`…„•ﬁÙø\"\"≠É∆?^vºÁ}°Î„ßì˜Œ˙F∫ãz˜Ü{\r÷?≤ùV˛G5—\'PK˝=´π\0\0\0É\0\0PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml≠TÀn√ ºÁ+,Æï°Õ©Bqr®‘/H?Ä‚µÉÇ%äˇæ8jUÂ*V}€«ÏÃ6ªì≥’b2ˆ¬üY®}k∞oÿ«˛Ω~eªÌj„ö…KPï9L◊¥a9¢Ù*ô$Q9Hí¥Ù∞ı:;@í?ÒÚ¨tÕÓ¨ŸvU›Ù:c°.Ûq∏°ªlm\Z&¶Hne≠Q5\r\Z¶B∞F+*0qƒñü\rÛ{üú‡DLÃÒ∞?d˜â ÿ$ËÚÄ˝Ñ„TbÏœR—iÙWŒqÇxt.∆ˆ,ﬁDÉÖ¥<-ïZûÿ©ÂIøk<∂›´SPO≥5ﬁ<v¶œÒLë÷Bi\rJÍ£–9∆ø/˜Z>áîq¥¿≥·˙ûaﬂà_¿ˆPKã\\ßJ\Z\0\0>\0\0PK\0\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0∂`ØB`˜√u\n\0\0Çc\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0∂`ØB6“ƒM)\0\0ß\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0∂`ØBåÖ≠{\0\0 +\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0∂`ØB©1¸6Ñ\0\0©\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0∂`ØB˝=´π\0\0\0É\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÎK\0\0manifest.rdfPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0öM\0\0Configurations2/progressbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0‘M\0\0Configurations2/toolbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0ÆN\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0ÛN\0\0Configurations2/statusbar/PK\0\0\0∂`ØBã\\ßJ\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0àP\0\0\0\0','odt'),(2,2,1,'Invoice','PK\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0Thumbnails/thumbnail.pngâPNG\r\n\Z\n\0\0\0\rIHDR\0\0\0µ\0\0\0\0\0\0zA†å\0\0!±IDATxúÌùw@G˚«∑‹^ø£pÙﬁª\n\"äΩ«Ç∆hL11â)æyı}”ì_ä)ÔõÚ¶K‘D£—h‘Xc{Ï<DÈı8Ó∏≤w˜ªBÔLîÁÛ«ÌÓÏÏ3s∑_füyfv`Ëız\0∫Ä—”\0z5†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0ñÈCW˙”º•7ä≤UØ˝[∫£Œ∂∏ˆë%ƒøxûÛÌÚr[å5¸›7\"˜>˘_<Tz¥◊áÃéﬂ∂¯HÙàƒqOÕÍ\'ƒ-≠âÆ|ÀK_Ú_{+]D¥&Í´~ùˇ•Ágo∆p⁄Â’7\\ﬁ}Œ>#Å¸m˛ó./¯≠€Ú˛øcπññ‘ä∫hû4à˛„94›ì2§®$ªéË|+7˛9•ÈæXQŒ%^|®t√ß\'åáLÎx‡±¢˝`8ÿÁ|¥ÜYZ¯ŒÂ+»à¨§√üÖø∏|Œ’yKŒ+\"1]˝È≥~ÛWº@Vm⁄Fêñ–ñ}ß8t•kßøß-°÷\n(&º„·˘á±Ùë>∆_ûÆπEƒNQoÂ©˝\n<¯π∞^ùÒ˘5—‡xù`˛≠[#!¨¯úÂØˇôÓÃ–Àr7¨Ω¯Àr9]¬yŒ%⁄£∫Hâa˜†åÆ:µeyCÇ;[òªÍÕÌ¬1Ò5G.ÿéOÌ`¯aÙçRµ¿âdÚÃá}+æ7aìºptÓ‚lF¿Ï¿≥…ˇ/@˘˘gã_Sπå˘àÉï¬ò»k_,ö◊Ë?o(¶”“ örôŒßmc`Ñ¥ãëpeÕ~‹âCóÏÀÆ¿úÃ\'Ëícgô¡≥Ò*ú IÃ ,Üx$e#}«Ùz ﬁÖ§Œâ„ìúõkåìLód‡Â‡![q™f‰p{ã€™6ıaÒ8ÜvC[}πPK4Í0Ç$0Ç0YRñó9πFZ‚?$X¶¬e∆äOÕª√g\Z>sœò≠¯∂9Àå?ÕœÔü’ÖùÈﬂ,1láÕÓX˜Ù!’ﬂû¢ﬁY˙ıXs ≤è[œ\\?”º£∫πo˝·Rö¡ß;ô*Ø)\\ı≠Õ¯OÔE”w“O5Ìg&õ6ÉG?Ω3åüÅa¶\\>√3Ó¡¯CAÔi7û≥óΩw◊\\,ØÙiÌµGy?ˆ›\'›U©>OÔ—–}\0(@\0\nı°º∏Ïïì^LiÎÍÍÚT\rıcwuQKÕıUK«,Œı›~¿ÉäÖ˙–‘◊”«ﬁyılÊﬁ—+råP]˛]˘Ù‰ ›‘Ôu∑ÖqnEárïLq∆‘ê?æ,ú¥–u˚·®ß\n∑≠‹∏”ê¡q¬XûTÆ≈D–Z=hXv«‘%Á´|º§^≈t®9yîLÓÉˇvU?“â∏©√	ú‡z«ıcbjU#∆w``8?(5FDbZ\'\"ﬂòÅ…«Ø‰ÀÙûv–Ä<`X¶¶Gå®V:˜”4{;–‘øú>›¯9§CVS¯¿•XSÂò2Ò”wå°QıµÔﬂ\nm‚x∞∞≈«9>ÒÆ¬6±Pù¨‡¢¬3BLuöΩmîB%ŸµØTlhPàŸË“˝[k&˘H6≥óÊ“R¥Òåc%‰–$mÎË	Ç¶≤B9Ël@{,‘áé÷™éˇ∞äåã•nU”);ºbã*eBX£Ã>HPp‚:ŒsMNw‹∑F“îËJu 3c<HÈŸ’üm·èXπ·”≠t⁄¨Q5oó)äÈÑâŒŸªY≥¶“k~fÃ}∆è®‘µ5~„Ï∫•9£WΩ†›ü{Úd= ¯ÚóÁˇf?~∆XzÕÈÀú∞ÚNå/ö‡rÎó/∑UÖƒªïÊñäìcZ◊Ω?ÊCàe˙–‘VPlí$ç˘%ßØäìc}xÚSJ5≠«1“∆?<àMj¥Zå„»√I˚∞(g√ﬂ≤∫ÚR°ñÚSh1ú$í`ÿáEÆ™kI∂´áˆD>»C{¥DFñ◊*Ï[çﬂº(≥—‰W0	∞õ\Z¥qöjTaÑ√∫2Æƒ\\Hõ¿h∂V©2’ìMﬁÆ’`ºª¥3@;,”≈i8|⁄˚ÉE—,ãaH—{$txƒõÜ+4yŸád¬–Gﬁ|á“…KÀbw>!;hh€G¯H\Zª‰0ó¥0„P«‡◊µ%{÷Ò]hëíù5À’†ê»Fº`2ïË◊jñ.À˛C∑p!Àåÿ\\p6[¢6oUV”È§¶Ì”NãqC5Nr<CS\")Éüd(kΩÿƒa%>_ÿæ	·kWY¸|â\"•ykﬁ8‰wÒ√£	ãÊF\Z€ˆ©„á9Sc>zÙÎ%s=>˚∞ìÁVºgı^e¥˝Øˇ…ÀoË«ıÏD	˝GÚ,©2Â5ÿ©ıêtñÂz/øPﬂ¶˚û/UyW≠;ûßîÀ’ÎéÁàŸàáˆT9Â»≈Ù]—ŒêÜ˛≤ã£´K…Elp*ßÙ<#Ó\'>_Ï≈JZ‘Ù0=_∞∏î\';dkˇ|ô˛ﬁKî˙&˘jÿê°ÃÉÜ∂ù€ô√Gj\Z¬HÍc≥bª™Óœ-ß≈6“K2Ò`«}ƒ“˛-…$Ò∂… ∫†mv¶W∆0„÷‹∂GLç/∏XÆ1_ÖÓpﬁÒt lc&¶∂Óå∏øt[ˇ÷Ï:»8Ú√˜Á√féˆP]…ì˜Á]-ÿ˝ªîÙˆ≥ÛÌŒ^O∑˘f◊Å÷“z\n7\\RÔ- XL!Ezf^›RùŒƒ@7˙f◊ã˚L€L0]›ü≤ò0p&∫ŸˇË4ß¡ôfp>í≤$õˆﬂKd˝éú;Ô6∫Õˇ–Ïÿ~^Í÷/¯‹ø¶m\Zwl◊ëÊFáh˜=∏5∑∂~¸çd@V¯üü}.â…‡ªÚˇt \ZÆL˜–m˛ÊÊYæ±<!ÙÚÔ<OJ©1ZÈÌÓπª[£3bj5e+b·6AÆ5ßÆR5≈-W¶;ËFˇc‡3ˇå0æ˝mñ!%ÁPæ04eÙ”lévw\ZπõŸë\Zççi>;*ÕdMÎ‚Ë,|æp#¶[¸\n»˛Gß)Õ—Óød∂É5‡of¸(@\0\nÀÙAm^˙kï»+„±Ò>å∂]PÂ≈Ø.Ω=Ûã˜¬o®JÂülåwoz—¶≠?¯Xÿ~ê¬êÃ¡.áˆ¨xsï„∞¿íÚ€NWZ¯\\\nª^·**ZıèEJ©ßb\\Á$ºË0chÄy\"˚ÙèF√¥ıoù∂˛ÚÆ-•Eâñ≤aàÖ˙r⁄–Á‘õÁµHùÇÈ‡TØ”„\\n\\ø»(v”Dv\n¶≠?ËX¶ÜÁƒ≈/¥9–º√èqlÙı{„ºvá‰£õOç2N[œÅiÎ6±ÈÁ∆º≤§ÀìÃ¿yÔ˛5˚@ÆÄÙ†\0}\0(zR8ÆÎ_•ªˇ}`∑VmˆJÆπ≠¢s7nï¯øÚÔT…Ó„ıÓì:y≥-mÌ˝iuçãÕ•?nO|i¯ïï[ÍÊÃçÆ⁄∫%ªZ!àõ<) m¿h·Aˇﬂå˜·¨áı1eˆ„∆√¢Õ=„†)ﬁ^õ>›ÙäoäÒ#Ú•óMiˆ¶CèÈÔƒR}tõT€¨<£Ø‹±‰\'◊?ﬂè◊uYÕ1˚.çò°%k?*\Zªdà∞%ãÆÓ¬…Ü¿XÕÜ˜˛®°º≤Ó¥\0téÌá^S¥ÂìıWp{;ıç∆cú.®‰ó§±£DÁs•äb:~¨ÀŸM?Á⁄Õ¸◊b€ö¬ÑÖœ•8íw∑*´ó·áûü≥¨¶÷˝âgπWØHÁNZ?}°ÎÊ\'´j›ü˙á˜ˆ5<”cd\'•C«s÷~,ôætå)fﬂ˛—†©Ø◊û|w¡/∂›ãä‚ﬂùv˚É~Wƒñœ˙oEÀb5¬™3{è^8õıh@G$˜·+Xı|!¯A√G+6m(pÙ`<}úä%ï¥qfWÁ™∫\'l£‹ÛnË¥4¡1Eﬂ-¡°ÁãÇS„äs*¢¿∞®í™35¶f‚x9ï∆ˆÇ+5ZµBAsªäŸõ¬¸<“∏Rçii\ZF1…díÜÍ∂Y¨∆8É\r«\rÈzÉÖãı∑kÙ¡pKÔfx÷á7áaXs+m~7\ZãÀ4πXTx«´;áÈc_)ù˚œ4{”íπcbö“«öR∆%öéMÀ„NHÓ<fo\\æ¶F:˜Ûgõ√¸Yü¸/´ÂlÛb5”^õ≈ƒfÕ7.V£Å®øÖX°èÓpA-$*BﬂëŒcˆ»0ˇKÍB‘ﬂ\Z,’«C¥Óàü(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†∞J˙∫˝Ø}¬xıùd¡ùgÓX∑É.ﬁ∑Ω>T|∂,bFÆº¥D÷Pã{w\\’£ÌkW-ˇ<‘∏d™^Ï¨óúØ§ù‹]˘wô«Ò`¯>ﬁ\\Œ˜˘D:≥Z/Ó÷ØL˛¯∆¡‡aLºﬂIπÜ¯!W[±JÚkÁòÑf◊\'o‹§´Û$çzr@Î¬bÖÑ(\\l€Ç7n5ï|Òﬂì˝yÎïÖ«6gõ1ΩvÂ¥˘Ûm:p“·≈Â/˜,[qFò1*éìøk]éx∆º8Óo;æ⁄HèY¸ ¸tóá∫≠#xéöﬂóæ{ËkÚ€”VÄ[ÂË^w8Ø™v˚÷›◊„fe$8wr_Z±Ê7oÃœ.·∞éT&‚ﬁæx˝\n]‘va±6+¥êaÂÊ-∑Æ!~6:µû‰ªUù≠ﬂbzÌ™D‡hÔ‡Yü_“ÿüdŸG8SX\rM\nŸ:≠^O”Ñm`¥ªÆ”À\ZHaë#òwiÆ^ «¥4f˘\0∏ıPâ¡«ˇw@‡Á/ä ‚Ç\Zæø}W:∞FúËó>än9ö:∂Îú∏0ÓâÁ‚ÓjŒ◊Æ0,˘u„ròÍõ{ôæ+{õû9C&YQ≈„¿∏qõ9Œ\'sú)≈ÚpÎÀJÌÜç^9¬¬Ï›UèøBÛ™©@è”+ıÙ\Z¨—]vx{æÆÅ33∂C¶™¬úBáAA|É€£©)™°ôLmE	+,ÃæÉªe»)°®Uaì£ÓXt_SsKn„Œ©+WË5∏ùámÛ•öÚs◊®ŒLı!ÙıÁwûVáƒıÛ¸•ûév¨õ_»9€ªRÃªd”´ÎoŒ˛ÂTpÑ˝âü∑îÑçLºÆ7UØñïùπ¢lÙ¥Õﬁ~ç’?¢j€ˆ*∑∞Ë8/…˙ï?˛\\ˇÒBˇõïLú∆yuµÆÇì˚+ì•W(:1’óhîú™`g¶˝Ân∞v¨}ælûÚ¬¡√rÃ&<)™ãõÑ„$ÉQS\"UÜ8p¯çµ4ó°”™µT\'µ¿qΩÙñD∆wVV‹êz†xd˘±´n˝ºôNAŒlÖÿé\'(Æê◊À\r=i\'8|µ\\≠#µ¯√A¬r^ê´¥˚Øπÿ±.>¶™ï	Gπ„AË|îç˜êyì}π68<sÆ!ÅÆæ\"!;ª©î}‰î±∆úIë∆„yo\'õ“√\'È‘Ï∏a!æÏﬂ÷‹a¡&S\"f◊È?1Â˛ÿ±F$°∏|Í∑™\"fïÃ53û_|Sm√rç	∫˙’Œbï6b§? %ÄkÃ»rèımw%C–©…9õa˙éù–yÖª2ı†”∆i√ö˝6ûÉsK„or»ö¸∞vNòÒBQ$qEÍÁﬁy$T]∏Î\0ñ6ÃEVÆ±3‘´™\r;N cøﬁr\r`⁄ ù9kÙÅ≥,ÕuÓ√“©\Zän®=ÜqKıLÅÉwúÎç◊àÒ!V⁄”∆i;ıGe‚ˇãÁnû£&˜~≥µ\"nòKÓ)ﬂÕ¡ôG˙IOùë±≈Af\'LØVl›’ØˇÔ‡Ä«É=˙áW¸≤1ó∞Mò<\'MT∂sÕ…¿)1€6≠⁄\'-‡Mˆ,,°lÍ•.°‹kU|¸ÙYYÍ$ˇª8s÷ËCØ¨o§ÏÌk ´I? ¡WuÈ|Å“-0§æ¶RÈŸ?ë*P¿Ê{ßù”¶ñk¥júÀ◊Î1ãÔÏ*$)G?V}EôÑtÓØ©∏Q«vhv¬pS”ÑÄ´©/)êz∆È	Æà«$er-Ê@ÒuuZ.ªÆJÀ˜Ûupni™d§¿üÀ≈j%∑´o*¥Üb ÷VµéôãÁµ∆ÖõõAái¶f∞€B~}É∂NõÒ8¡”Jo‰º®]Ã1˙^MÑ?ÔŸ÷ü£¯<ú}{ÛÔ?œ«5\\{·Rë÷√y˚ÕÅœºø—|*@∞˜„=úPyMQ}tfˇÜÕWèÏ◊Dá≈ªu=@gç>4Eø}ªKÓjKxGŸúÿ{ﬁ>t“»à“úS∑µ[Z4pD\\w\'ı:q≈Hﬂ˛„ıÔò∑ù÷πg∏6,¨™^j«b∂∆#pü•SËÌ#cÒﬂØs=<ΩbCN]k‘cú.Ë¨Î 1ºÆì/Ú¥©ÆckkÍ’Üfê•*/hpdï%‡˛@∞ÖlB[ìw8Ø≥èJrì)qñVZMıOd?R\\E⁄˙F∆°^{≥FîgÊ4œ¶˝¿Y>¶m¿løÆ/\0zΩ™∂¡f∞ÖcQ©©Ê§aˇhyÒ4<™Ûb;˛Ú¯ã^zvkûÀËdg…Èm0ƒIcÌkä +⁄ıì€`¡Ω≥ ˇ∏µ„Îıπ⁄Ü∫∆∞ß_~ƒèeJƒ9\"ªè«´zë\'9~FJâE	„2T{œfM4ﬁ>ÓùuÒScﬂâ–◊‘U◊©\róöüj|;ÉÆ—KË⁄A¨#<!S¶ Ïùﬁ∆öº£›;Î¸èÛÊ∂ôWbo|™ô⁄®ázr◊É A◊ö-»8‰ÅYvÔ¨Û\Z⁄«ÄM‡6˝∆\'[eË-XpÔ¨{æ®ÂuÁ}_ñë—`‚éŒ<˘Û \\á‘…èÃºs0`ï>púÆ((Æ«hÆ9à€†b7\nÀ•JmwUËQ¨{æP¢à)M√ÒÅ˛MéHH¿ô›P/†w`ù>å°\\}√•=ùÜtÄ`z¿∫¯«ÔﬂˇV90YwB¬–Gü¿ ¯áØV6z\ZÉÂˇ˙Véø<6€8√œûIB„—7∏áQìávÜ–U{∏hÛJã¶%òyüÊüΩü÷WZÙjY…Åùø2fﬁ∑˘ß@ÔßÕ+-8Ægë˜s˛)–˚iÛJeÎ;d¶)ò˘Ã˚M£,Å>MŸB|S∞	à˜íZ\0}<¥t˘nQ+wÔjÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@qı—<4äü+jô‰¨.‹µ_Ì&n0ÏËJ7øux¿ÎS›aî∏ß±Pt…ûÛ¬BÇcºπòR⁄@cji5.ˆÌr]’Nh\Z\Z‡—Ö+ﬁ˘Õ∆Bæª 8‹ˆ¸Ás~O]òÅ5ç◊â¥Ò<\\.ß1Ù—”Xvµ≤q∏NR!gMrÑ¸ÙM%iÏf]QMC:!¶¶1ä«R+ÀKu>ëÓ‚ê(÷–2.P\\/î5ÍÎ\ZıR^/¿2}êvÅQ1ÅQÕá£=Ó•®Ê°ïåá‚‰9Ø6\r\n$•¥‰1é`∆qÅßá°«Ä˚C¯ßÀ∏\0pÄ˛Ä¢ß˚/∆>ãB›≈Í @èc°>¥•˚~<\'ËÌèù8Œ\Z >≥∑1jêãìW◊ˇ¢⁄ˆ_àÚ}Àˆû´©çô9™>ª>¢Û’ïÅû«“˛≠R„‡‰Tr‰∑õdFTç&<—GP!∑Æ®∂˝¬!Ã£¸H^’Hâ∫\\]Ëy,‘ÀgƒH„vÃº~∆çóó·√áe]Qm˚/:ÜKÊ+oeö“˝]Ô:ã	Ë)zCˇz+ΩóÚOE1r⁄NL’6‘´r,òk‹,‘Gc˛˛3:?7\'/qªµ2UÂ7‰\"£ÏÌ‚ÎeG˜]°8Á÷û\n}<∫îHõƒ\'p˛qGÔ√2}Ë‰Â’Z°M—©su˝áàÆÔ€{¨Ä?Èô¥ ≠â˛i\"ãjÁü≤§¶J¡seKçu%.WŸjÙËπˆ¿˝«2}âSå—à„ëÎƒπCL…3&[QT[ˇwNô>∆∞ﬂzΩüx©Ωêﬁ‡üö/µ7Òu\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Öe˙–’[˝ÎUn‹§)ë‹î¢íÏ:BM∆é‰‡I©Ü—UßwêE\rwﬂ˜Vv¬#UøhÊø1/Ωy„lÅMjt≈√©—)ﬁT…÷•õTÉ\'çOrc\Z≠4^⁄tÃn\\öã%µ–’‰¨=„óïÓLöÈ“˝[k&ÖrZäŒÙ=ÛÂ6ÕÄa£“˘ÊJÍ‰Ê\nd§;^ÔXêNVpQ·!¶,ˇµ\Z/¨\\WÓl„íb*∑/`ô>Æì≠\"ßÏ˙Ê7ﬂ_v÷o˙3}næh;~(¶´:µeyCº´ZrlwÌÙW=¸•$–°$˜\n~„¯õoîNJ`+i6Ω˜´sæ3~‹êø(ããa$[Hü_˛ÏãkO~˚z§Vu¸áU‰¿ÅåÏUˇ˘πa‡ËØ§!∂«é©#íä÷oÁ•áﬁ˙≥6(÷UY\"ë€ä§JÂo~m;!©hWÂ»ya≠3‘M_π«lca\0Ü·ó“‹¯ÂÛçóÙtπ$Ô¨z‹¢iI-–ÊÇ‚b©[’4A éÊÃú†‹ü{2;wÂSπ…Èé˚÷H˙è]©NyfåvkÎ«ﬂHb∆∫ú;xÁπ¶Nå9˜›F†Ùä]pÇ©‹æÅe˙†k%ÂîØW≤ú]É¢‹u*=FêÑÒ…‚q∏b∑õ˚ΩÉ±=[Îÿ£Á≥Â∑Jnk\Zµl√πneª∑ñ∞˚\rb`\ZAPjBàfÛF•^I5 ´ )6Ií≠•i¬÷?<(Ã”ÍµJπ\\Qv±PK˘™˘Å—< ^|coMÙ#^’Ö◊rµ≥æ\'‘*=ã,Ø’`<ä◊d?r4Yéi‰9˚™5>·Aî‰ÿ>örh‘\Zj©©≠0d¯æí”W≈…±⁄SÃ8vSﬁRÆ„8Úp“>, Ÿ–§ht¶C¬∆pñMjTóıq3Xs`7ï€ù∑•◊`ô>.ÈO=mﬁ}™))aîi3ÈÖß0]›ü“¯Qc“›õ~≤ËL[º®ÌıK˙5Ô•Mt1|fΩª<Î]√Vq˛Jëh÷,W√çã~¨9á˙Ê^ŸÑ	√ºô£SöÆ1ÿoúÍNÖ&çiJJ¬¥%{ŒâK‰FÃ[aŒô›∂‘©c4Ô*ƒJ∫©†»‡∆î8£ıD?õ>ªÂÇ¯0„ß¶\"/˚êL:r·¬v\ZH07îªæ*®oà˚{¸S¬6fb˙=]…çòûqg\Z”+còˆI◊aYÆ©†Æ†ú¢;uy÷∫rx,’é„›Z†g—Îıù¶[—~|1uˆeó§>|<œ_ΩÙ≤Zøÿxwf«|¥dÌGEcˇ%9P4‘è}è∂ÉjkwdMª˙Ï”≈?‚LyÎùdaWıY2§ìS-hÆ9Î£Íå9$\";ûÕ_˝IÈ¯&	’Wø˚øíio¶¢L=Ä ˛¯≠–;pÍlß’ØOﬁ·ı¯À¡⁄™3ªw›ÚÁßÊL˝ıkÔ_æÃ±1r…Œ∆ó_Y¸Eˆ€o˚gÁ(ø/–œ˙‰QÆ ;%ò˜w|ìé\\<$Á›‹ºSI€á,{‰…woåzˆâ4óÏÂEì∫˛∂µûì˝˙\Z¡îiä]y±™÷˝…EÓ?}VΩÀ©j©ÁU\"Hä’∞}¡#gGº=·‰åî˜l&ŒtÀòÏw‰◊	›≤YüULZË≠…ˇÓâü%ûÈ1≤ì“Ø<üÍ‘QNVËCym√⁄:o«™¶_ÑÂß˘û3(Üã·¸®yèó}u ÍVÅÎÎnc™(««â∏‹‡–›∏›óX\\’âZﬂiaLL+\ZêPtñ3áùk(Á˚Ö)/¬\Z4$âÛÉR„x9ï§¿À?z°íÏ–È¥jii\rM®ÎãÛÆ´1ª–»»0\\’H´ïjCà¿~”wa{¡ï\Z≠Z—PqˆHcDí´õøcb≈Ì[∞~eÎ¡∏D”&£¿|®-˝ÌÕ˛£C∏Âùı≈Y_òSáˇMïDÒˆ∂ıméÊºhﬁ∆~g¸üÑas_]◊r÷‘SÛÀÿéV®ÄÎóµÊôy·ys∫˙⁄™˙«ü\\î(07ŸLnæfÊﬂÙ\rz5VË„“¶˝vô>y9\no1áÎË‰Ó 7F@ö„òcﬂy£€*Ÿs0[ÿ”ïËI¨–≠U[∑µÄ^ë{|«ﬁ‚ÄÃ0ocÃÒõ≠ecb˚LºπØaÖ>å!HÉ«∫cÃ—7∆ùEå≤Z\r&Ï3!£>Ö˙+µ¢9œè3zßZÉMqvúıUé éá+Ù9c6£ãs}¬WÎãX°èOﬂ>2-iY“+Óﬂoüs˚Ìy≥æ>ÛÙØ€É7Ø8YUÎ˛‘‚–ﬂÔ¶ß}Û©«Í\'ó^tÈÔ≈í›jÁ„Qˆ›WwàÍv?VË#Z∑ÓΩüËAÈô1\"F1…ƒ0Så¡W0‹)í…4áhı:å„ﬂO˙yÅˇá80–G∞B©o|ï⁄ºüı≈û¶ á1∆`äå¯®)a¡+L·ê∏ŸY—›3Ó›ﬁ$]ŒpHﬂ√ ¯òÖs€L˛Înr◊Ø>ß&fç˜cµü2x”ª}]ˆ÷’è\Z#ÿæfg>#Ò≈˘#òíuüûàùÂyb«eÒ†–äú<µT˙HÃ˘◊ÉÜ˙]ZøÍë8ÔŸπÉDDUŸ™¯òÍƒÓC$A é¸∏ßF=aºËÚÌ†°˛í›€˜Á;åõy3«8Ô˛ÕnŸ$OõàÔ[ÒÍ¸çÙòó«€ﬁêù>yP0*ærÎÍ+…ˇ˚,I)itÙ≥ÔÛ∞µr9ó•íIq]É±î\nZèÒ]D;Pé±	é◊+›b¸ﬂΩ©H.ª÷(tÂT_ì*dL/õûT∏5Ò1ñÄêú0MŒ”‰õ¶‚)k|›Í7_7™˘Ê©x˜7hÊZ∫c˘Wı2ö∞\råv\'X<Æ‰B°ñÚWhYv.<}Ôö(ä≥ÌÏ€eö`{é^ŸPy[r≤ZÁÈâc\Z…œ_úÂÃé8}Jqç¥∞úìißëTq¸ƒv6kJJd⁄0·–~àç¢˘/è5∆«‚≤Á∑¶{GeN|º]÷˚4ãô˚lLªÑÕ≥œÜå6o˝zMËü\rxdŒ\0„^t‚Ù÷d„t¥ˇºm‹Î∑tÔ\\sZ‘„gˇ¥ŸXœbÖ>¨ò¢◊«&·=ƒX°èﬁåÍùµzh∞T]ÕOnzÖ_ÙZ@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0äˇÇæHÁV|~Ω\0\0\0\0IENDÆB`ÇPK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xmlÌ€r€∏ıΩ_°rß}Ë,Ø∫´ëvúÿª›ô$ìŸ(mg:ùLB2ªºÄ,ªô}ŸÔÈWıKz\0^ÃªHâùMÚ‡H¿9¿π_@ò~Ò›ÉÎÓ1°∂Ô-%]—§ˆLﬂ≤ΩÌR˙∞˛^ûIﬂ≠~˜¬ﬂll/,ﬂ‹πÿc≤È{˛\0∂G·ÏR⁄o·#j”Öá\\LÃ\\¯ˆb¨E\Zz!ˆ\nG({t\Z£‡46√¨)2áÕ‡¢€Ê;‡4∂E–æ)2á°¶—7~S‰Í»§ÓàŸ9*€˚y)›1,Tuøﬂ+˚°‚ì≠™œÁsUÃ&õ	\\∞#éÄ≤L;òoFU]—’÷≈5•è√¶IÚvÓ-&çEÉ*hïﬁo[ƒ˝∂B4Ê\"çmC\0g’;¥ö´wh•q]ƒÓ*t2Sﬂ¿§¯ÒÊıì-∑È^6#*ìÿAc6CË4æÔ˚	©!tPAÆ°i#5¸ûÇﬁ◊ÇÔâÕ0IÅõµ‡&rÃD‚æ[&4Ä”UÄêÒ=7”ƒπ hÇ°Ü”	0µ*ó˛˚õ◊ÔÕ;Ï¢\'`˚0∞l{î!ÔI2Ñ+°í”±Jp‡ñf”<`Ç∂åÑ∂;Ê:’ÓŒgc–-±¨RP g®ÇÎÉ„…˜6ﬁ#e\"yΩAÃs!¬‚!îéõµ∫¶ròƒç¡DûÇ<Ÿ&yh„Ô<`rW$@¸`bÛ)‰¥EfÖLÙµ±ªw≤Ÿ2@©ÏR–6Xµ,RÿŸ`N‹áfÀqKˆ≠M~≈úWõîYôÚ÷?©|NÊπ¢u¥S*á“*Nÿ°≥S5ÿ@‚ñ7»ƒ≤ÖMáÆ^ÑÅ7Ñﬂ9›KÈoê∞x%@`}ec0◊vó“Q‡”?ß`¬iêYí√ [ÏÅ\0 ﬂE^\"∞ô	ëÚ;4ë‘è»Éd˙Ëﬁ˙é§÷ºFw∞Å^Bl8sˇıŒ¥-4xè<:¯‡ŸP·J∆K`KêgØ’Ùü≤4 º≈˚¡Oë ˘ ¡u¢‘§]òSBP<^Ω5›€îûºı‡-\"ƒﬂWä$\r‘@çàR´2\ZG;be∂)ãuO?s6sãùa≤_Dypc,ÒM ⁄`¬l–o8º∑-^¶LcfLl–aX‚\"«ﬁB$qŸBéãWÊy]v¡“óíCdv+I—uàHÂ™îL®ﬁúùÎÂ®\rãDG„Ìö2é¶vb;rb<’ı?µ¶ÙÂ9(çgµîŒé°ÙUÁîéÕ–«uî\'˙l÷û‘Î3u6çÍHùi£#(ΩÈúRHÌ≥I≠˙Á”È¥=•Wz©ÿqÚÑ¬PöÃçøêˆ˙ö¢\rg\'Fo}ba\";x…◊Û=ú%ˆˆÆdò˘Aq÷gå◊Ùö2ÿÄ˙ém\ræ—ƒøˆåñ«∏çËnL–ªÇ»D–ñ†‡.ûÄ~N\"æ»!÷_0ÇΩJî°).\0’\nÒÍ>µô®ˆÜ |8MŸ?{`+°¢?©ı‘∫›≤ot¿>Ø^ã.#≤a¶(\0%âAjˇu=`ôÃ áeË†êW2…\'~(´&r2/–õ€@¡\Z{≥]ôcn—Ì~í.¥:]‰\'Àt—\\ÿ£cÑ˝∫s´õã«ˆ8–çXÕ-1ƒäß∏g5‘q\'≤+≥.!”®`ƒ^¬÷øvîŸõG†‹€¬¢{à≥KiÉö™»øLuLz7Âg>ßΩã∆‰mwhCVX∂ò˛Œc®πæy&röı(ßYçµ‰Á⁄	a÷JÛ>mÂÇYN◊Œ™€WÜ2ôŒıﬂtU°U«wjXÌÚXû˝í<÷Ç˚£ ¯ﬂ¢µuÆÑMÙ®6£qıÊÿñÔ\"∂ı±ˆáS´≥˘)ôu~RNô∑ )z7-≈ b!<«\'K)>9˘B*e˝ºùK€œ\r~öU \n”-≈.–BP›ÙΩGÚ¶Ïûßw8•U:ÕŒª©Òõ€y{•ÇEÜ\Z=\0ß+⁄P◊ÅNîaÂ±ÂA‘…|8={™Ü0–NéLüõ-u”+]&≠ı#\"„|]÷q\":∆\'£q¡¯uâxÑ^Jﬂ~˛m˝¨˝—–>\Zu?_Xı◊ã\ZG?Tâ¯uú«µFQ√\\õ7Õ\r≥a≥4¬/$å&Á\r—9Õ’ı3©vûÖâ»®b0ıƒ3Sd{>qÀjºäF\'[eó ü≥2Œ€bÜ∑H¢g…ö¢ç∆√Ë·k4=OŒL	Y€ kèÖñ¬o≈»ô…gõeéÍ/◊¸Ò5˜ÊW·{zûå‹îán\Z∞fûó\\¬*9ÉNÆ•.µÅà’∫6ü[Èô\'Âa*Ù}…l„®m\rÇ„∆pÎ[è1òã(√D–∂ù\'∆S-∫ùœù©\rGµ]ç3‘≈b≈◊TÿK*<ã?ØDzTˇµÑ˚j∑=€ÌQg≠˚%öb]—gzıπŒÏÚãt&Êø£—\0}§õ|M¡âJv:cÛ˛’O:Òì·QG«\\˘Sœ®Ê∞Õ)—÷HüìıZmE0⁄Í¥ÉZÔ5øß>tµ$6:Î#±Q+ôâŒ,ìjß˛§Uº0~ä¥*≠•[çV,N:d±ëÚ˚dæp<—Ö7<õ+íÎ¬ôAgÏ}W◊ÖÛÜÀÒﬂ>Ó]T2ÖFÛ˘Y∆I¸\ZíÀÒ˜Ÿ^óYoè~πR+Y=hwMr]º$⁄ÅX;Ω&wBO≥.^Ωw≠clß|w[û»˜ÿÿ‡˘e¯Ó∂`º¯sÚuÒR`g¥O˙≥vñÇO+%ˆ≤F˘·˝≈8<KI◊å√ﬁµ€Æh´$∑≈éÌ ® #°«\'ñ ôŒî«÷ÊeF˝Ÿa9ÍëQµxI©ñÔC∂⁄b„vÈ˚†ì$2ÁËK…f»±Õ¨ò¯«Xà—|z€•›cÈmÊÎßq“.ëV&˝¬éjÂ`¢	~ ˘¬˜IΩ‹â∏\\hƒMao|sG„ß*—d8è≤Ö©Ωı¢◊π$WCƒ)˛˜{OÔ§)ƒêe”¿Aè≤øc‚AÅÉÔ±ÃI·t(™gGØÈ*ﬂ°≈bÎ¯˝9ß≠¬UuÍ\"◊·Ö´•Ñ(°±=›·õãwÌàü—{w≈˚|¬±4b4ìAçﬁ!R	≠\\Âˆ:à≤-¬´∂◊mnÚƒﬂÁV¿éS#˝È=i»Ÿa9|ÊV…Xß©©¥∫ryƒâtpeÁˆΩ%F<K√\rêWÑ‰Ö∆ÍÉg«s®UÔ‘M◊8|ÑÅÁ@Œ;xΩ2óVo1cJ-!j≠È_Ã Ú¸„˜≤<xyÛ√èo∞≠b3	Gñˇ˘		\'˘•gyñ\\ü¸¥WËõ.˛+K´O÷ìÙNTÍ´ˇ˝˙ﬂÅœ«Ä˚BÔ4Aø“ƒÕÍ#››2™≤_ÑÕ›ºΩŒZ‹fÿï1™ßJˆ+%ùQ¬/h˜∂wbÈâë∑4ÁÙH›>3©∂VØ#b!^Ê\"®sÉÜ∏®«„}ÁSŸõ-0z f(íh	9·ÁsÌy‘é◊xÉvé∏÷˝.æ_√ø|qhO]Z-BP|\\äè9c¢sπ%˝\\™™Û ÊØWÎg&ù{ƒûÉ`~ >•}àfP#õL*Ru∫oüF|Á°Ê<‰®ô35s\\Àˇç’ˇPK`˜√u\n\0\0Çc\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xmlÌ=€í€6≤ÔÁ+XJÌy£$RwùÃl9{7U«O6èS	I\\S§ä§F£l˘e?cr·4@ÄÔ\0Ii&Nú*«\Z@ﬂ—∏5øˇÎÛŒ’ûp:æw”3˙√ûÜ=À∑os”˚Â·Ω>Ô˝ıˆæ˜◊k«¬K€∑;ÏEzù\\j–ÿóqÂMÔxKÖN∏Ù–áÀ»Z˙{ÏÒFKzIáäKhg≤Õ)∞ÿ:¬œëlcõjãVÚ#S`±µ†£lc<õØ}Ÿ∆œ°´Ø}›Úw{9,û]«˚r”€F—~9è«˛q‘˜ÉÕ¿X,Zõ l%p˚C‡R(€\Z`ì¡¬Å—7vá#$ãÅQÚª§YÉ\"îìj¯¥ë÷àßM	k¨-\n§uÉß≈;≤Â≈;≤≈∂;mKd2‹C%˝Î˛√YÇùÏX6≈*+pˆ“d∆–b{ﬂ˜TIÉÿ@)∫Êp8ƒøËc%¯1p\"‡V%∏Ö\\+·∏ø+b\Z¿Ä–ÒQS¢K{ûº˜É(Ad-Ô†Ä;fb^€hÁñõ©Â†õ¿∂Aù—\0L\r]rÒª^ sV`ë\0uCuM(êËß*√ÅIÃDrv™¡&q˚kˇ‡0U0‚Á=RÖ\\⁄lôÍ!eãa8ääòÛÛÄ‘Èƒ7É˜a”É0%ôΩ[>ˇ¨}ò{÷»¬∫ç-7º˝>ˆI±ˇ&»›Ù~KÊ±PaÇì‡`;«=›Ù˛Ì˝ˇò∏†ß•∫$∞˙{@%®q‡ÔêóÇÿ;ëÜ˛Ñ\'Ê∏PGJàÅÊg=<ÌVæ€T#¸Ä∂0ÄQÄl\\S◊˛√¡rl§}F^®˝‚90ã„R¬`ê%OäÄr¸€t\rZjÒQ˚ô	°òÆ\\\'B≠AÌ\rÄπÒÚÚ°√£Ü≠á÷>¢ è•,Å$¯!Ö‘†Ã Yyr‰mºFóEçºgÜ„&@˚≠cı8,˚≠ÔpSA‰Ä4◊˛Ú•∫øè®K|ù¸Óiƒi-√-≤˝£É±Èœ7Ωaﬂ0ÊÜ„÷üÚıÃ˚:ÑIX˜»g†o˝¿˘Õ\'N-Ü6∆U–OM´\0fÈ~s∞EΩ2Œπ@Œ—â∂z˙F¡APî=\nÂ°»¡∏äÄÎË˘d–«∆~ä‹˝61Oä∆*¿¢ 0ùàx\rô⁄	n;7=7–£UJQœ∆d>%+ëòõﬁ\Zπ!Nd≥ËÇøâ&ï£ùÄºs‘B\\àlcwÎª~¿∏ATÜÜŒoÄ©aÓ#ZÊ\"os@(Ú\\Z`¡å†?§(!Ìtàê«[3ﬁ∞x›oœœºäu≈k<ﬂ√˘.IúÍ‚ÁíNì⁄Çnì:⁄Òôq)€í1∏Ñ”ΩJïÊlO˚-ˆË|Øª»∂q†Sl®∫ŒŒI(ê‘¨˝¡≥¢C‹!±`ÄtPêz’„*£€ò†GˆGã—ÏliÌ‹CœV—@Ö—ïLAmïåtÀ’•xÓæ®B“·µ*ò¢;’XQ°0wY-9çú∏™ô9†˝!‹f@ZòC)û…≈¢¢ƒã˛ïÌ\'ö9ƒƒbàÆµX\'wzp(…ÿ·å˜z‰op¥%´jbguã∆⁄˚¨∆FÅ›+u\\|.\nC@,¶÷C¯X∑r)0•¯W˘ÑÒ˚±…|ÃYoèEñU◊ÕŸƒÜU&ÜÇR˚¸FPà\ZEx\0∂?ö√«ïoüxá‡T˜.:ÈgM¨.’(Hvıb=+Rß¨‰3BÊÑÜΩ¥–~˘\\.“Zö«»Êü6ÑzÑ(†à—*∆µCÃç`¯{\ZNß\'f´X˘QD6LÜ˝·|49œÉ∞˙? 7%ŒÑFèçë{Dß∞Œ*ìcÀöåÃ∆E&–dn+ÏHañ\Z7?ëíQlB3.¢\"2∆R¶™ÇLÃú¥12⁄∫	óÕj.õﬂóŸåı›ö˛)ò¥2tƒŒf]Ø|◊NA«úÁ¥∫	œG’<Ω:û«´	◊Ÿ\0’ååìâÔüá0r÷\'–EoC!,ª§oKKJW»˙≤	».(èKæ“?óê€∏Zn„W\'∑ˆÁV˝ŸéÆ:ﬂÛ%ÛŸ†–ÏÀ¡	≥ct⁄ôëê ∑∆¥TÒﬁ)Ñ≈ÇÔ:	73 /ìc\"\Z–C0-;G¬v`u›DØ*bót)iÅŸâﬁP3ú≥£Ë bLT©ÛÍä=Íõ”©ëUFÊ„Í’x⁄üååEW∫,«◊˜æ˝…◊Ó˘˙@˚ƒdﬁípùky{\n¶ea:2ÕzQhÙ∆D|ûO∑t√dæ•5‘IÒ„˛°¬\"îSüYÄﬂbçV dy4éGî9W\ZP∆·w<ù8 bIÃ1ÿ$¿[Uœ˘Êâ;.Ô Qõhﬁ?\"´R˝)òñÖÈT˝ÂH˛ÛsáÆœ°˝ukoóûWúH8◊nœkãˆƒÇ‘v?¯G´›èÆ-2Ω[Ud•¢Ae˜®TåµXÜrâÊ,≠ü´®©d‹∑ 6ô¨Æ4r–≤Ú7êov´Næ{XJèÑpDqÑÀü„õ(≈Œñ¡ii†‰¿Ç¨+ºÂ\'¥°C|§&T<Å—“\0©ÓÀ’Ë◊_ıÿ£ÄKAƒTëÓ‚DMÇµj\'@¶:_Ü,BåCi\"H\'®IÏëÑÛN—u÷ë“⁄Û¬dlˆâ¨¸P J÷@KX˙·X:ñŒáØî•Ô<[∆XCs¨f°bg›ıtW⁄?qwN~º>≥Åµ–pMÑïóFíŸFóèhhÄ<kÎzº.\'ΩßÕí‹8rÿÖ¥ˇ7Ì-æ‚@¿]\'Úc©‹c[OzÛ◊*Ë≠\'–¯á âU‚*~=HáU,ÔÔs9™>œò,ÜÊ`Á´HqÈiS®-ÈFZ◊ˇ≥4ª_Éı‚ïUÜµdî<É~˙ÓOﬁî˘0˝A¿üõ>8á†\' 	ˇù={öΩqM/Ë‚\'Ï28˙\'{	f™ìõˆ◊â »^&kzñ°¶¨o…QÕuÙ\"£é_d‘…ãå:}ëQg/2Í¸EF]º»®∆∞Îay7∆p°ùÖwsXGlÔ¶Â[á»y‚[t„Éﬁö˚∆dpÂﬁ/<¨◊L#˝\"Zçjb„^wπ8∫Ç\ZNìÉΩAN\nõ›bLÔØÍ+çË\rSc13…ÃYFé±òvL–Ë≤Ò+≥◊#h|YÇ&ã·‚∫M.K–l>_ó†Èe	ZÃ«”Î4ª$AFﬂòìÎ4ø,A#–πÎ¥∏,AìŸ‰ n;tO—lfv„Ñj1  M!Y›ØùÕ!†Ìµ§Bg˚Òk∂AÃÉ\'äo‰≥j6õ”<`d“ÁTO»=W¡\n˘0°~>¢ßœ9ƒ6Iî£ì˛¯kyZpºπVEJ¡6]Ã~¬≤T\'c¶!($∫Â·3)E8óûW≈/⁄·WåŒz9Û”≈Îà’9û–\0djﬁˆ—ﬁŒO˙»V(ÙÈX:Ø([µ?’Ã]Â¨yh¡ˆ&b}ûˆçŸåﬁÁàﬂ_∞≥ùiæãQ¯¥@˙˙`ÿSà#ŸÕ=Ïº™qacVŒ7˙Ê‹ú2CTAÂáŒQˆÁÛπŸ\0ï∑‡ p<[4@ÂÆsTÃ˛b25@ÂMÓ8ªn( „ëÏ•1e¶€lkπG69’âr”„Ï?ÈÊ5A|≈£®*>œ&G„¨úΩ^äHc±R÷y∂òvú-Œ‹\'˚H£Á\'\Zø´¨Ò]3tÁÿ∂ããyj,∆•LÕ◊©sµê#í\\ÆkÀŒ<¸î¥†? óÎ¿.œı7≈≥ﬂe∏~Y—F≥/Î?Æ…„Kk∂—î≈ï\r˘êy Âò‡[b∂ x%˛ﬁ˝¯[… Î1ªxBî\\òÃ˙Ê¬ò]xab\\`abˆÕÈX}ab\\`a2ÈM∫∏SE•$î©}BWë¬-ÊäŒ6ˆïsÕpj<üñ⁄iæÓõ	ZçÎ.\r^	ó_:h5Æ¥æÆ_;l5Æ∂æ.KÈv◊õÈrÓUÛ∑œe%]nƒ’‹ì„p£}R0üæe¡∂2üvNÎ˛S£ƒÏ1n◊è<£ôêÔ\ZØ4Ô?µKI—4Û\0{{bàoO ü˙âOOåÃ€ëöw~ÈóE˘îïôó)ÜJﬁè˚OçrKdîß\rÎdôtY.¥À‘ÌaÚ‚|Xã˘nlúŒws˜Óµ0jzEF%Ÿc”¸öÇìÙ‘À tlòKÜCc∏XÿbÕŸªfaJx9o≈ π\Z\'g/®rYB_å	Ûº∞‘ﬁ&ù´úh‰ÿò\Z≥\Zçd0≤÷›ŒzùËúÃ[WÏ%f÷‰°Î$9_ÕzÌ…<	Õ§ï˙¶e‘.ø^K\rœΩñØz´_ƒJªYPîi#}>¿WŸ∆d¯ó∂Êøh7-ZMsEjó…ÆCŒf’´?©T∞\\µ¢≠≈®p™—ä$ï∆Q¡ÖΩ§±u≥‰Ëfˇ¬ÏOgãÙπWiÁ‰ºõ⁄‹êCÉπAEJç÷;Ÿc2˙SsbæêåÆe›,äæ£∏√-¿:\rn:7Ú/∑ ∫™æI“˚ê?º∆πœ%•8.ób˘.Mô|≥˘Ö∫\r˝ï2pﬂ?‰œC/ÀÆﬂSÚ«ómò¢í*∂ç¶\Zç˘ìºÓhî≤â•>ôv°ˆØg≥ˇ!”uF_ÌbT›!^ó5π¯Îz¨y»≈C◊c¿k€#+ËΩaF’˚á\\úı«e´◊Ú€√ïl+p.sçJC™Öà•á*C^\"Ãí€Tv«Ÿ]√6ªÇ˘]¡ó$<VïÎﬁm|uÅ›‡v‰µ†R_.&Rﬁ£e)´KÆ›äå¨dzKè¨ ï˙ï!/í´/Qîq≥€Ìßá¸˛Kg¥]âSmäV°`›Ê3s]íxë]#9Ø™°4kÜãN˛!J≥`ø3z@ô√%Z√Õ˚Êt6J.‹nÑ„9£?]òÂ91ÿ(Å∏˚ÅCæ_œ“å¯A \'ÊZ:=O4µ.HAoŒ‚4aÁ:~¡7ﬂ*π‘[ıı⁄‘∑ï#6Ås˛Ïî5$ˇA∞L!„QQÂ\nÖ8ı\nj,0I´ì\08úLJ\0c|≈l¢πnVÿıèô¸µ\"ïCE=KúÅ‡YbÙz>cÍúﬂ§%\0!Ê˚¿Ijå·p&|.ÜSòÂ€!\r(–h1/\0Bkí˝•FÃ.-≤Ÿ‰:HúcE|+úS¯òìø“Ã™x´™»,8Ω[z ¶ß¨çï≠ÈG|≤_X\0eU!˚}I¶¬≈⁄[b	ªN`◊$YˇˇL˘†[Ü¶$±mbÔ}¿~‹˝¯Ò)(Õ\"I@\ZŸºÑ$û˘™ODW‰Úu‹·Âká«´j^”~5¶7(“Ê¬é$4dÙ™5Ñœ`E\ZíØì–È)ÒOÁﬂëJ≥{±\nñÏåó∆]–R\Zõ˝ÊèàsAò#Õ2°Ìyû¥SrL^±Z¬^¿\0¸¯Ò?˝¯ˆÀlG\0k⁄Ä€’À˛F≤î&©Û$z\0≥Ù‰°«Ω[_©Ô˛í„ßÇêı/˙ˆÎ—±ø∆†Z©Ù0È›Æ\\dkJ-òÙh2<!/bà]lETEnz÷! ¸Ω[Éu-Ä´·ß\r\ZcG◊<ÖŒ/çÖSƒJIJÁÃ˙*¿ËKFDï*≥ ¢∂¿©¯;êv‰ïi3€ €p~’rEèû\\wc±ªﬂúΩÿïÂD\'ıNÿöTΩ·ä÷ò|ŸM¢#válP!\\C©Î¥w{á\",Ø3%≠ôΩ´»Ÿ·ØÚmÁÿˇSQÒhÇ»8ı	˝õ•Aâ´yﬁ«∏Ll»jRMY∂êRË˛õÃXµ\r~PmVµ¡]∂A‡3=`◊≠ …‡ª-Kö	î}∫#åHˆœ^„ßÒ¸îIeÏ\Zè=KîP£r∆∞w˚)ˇ	ûøX≥~¸D&QÕÛ˚†·Æ\r≈\Z0®‘GU5õ¢Ígº∆0Õ[∏+y7F&q}+ﬂˇBæ£”Ùª,.z|º≥1˘2Ÿ„£92ç˘t>4£9	ˇU£#1Z©é|’2£bœñ≥#ûΩmÃ3¢˘Á¯QÎJﬂ„cJÎªXRI°Q{Ì3;SÈ5Te∑fU\0Aﬂ•‘9ò\Z?cÖÈ“V\0\'+û’I°¡X):1 ˛Ôøˇsπ˛ˇ{‰∏GÈ=zVcJ¢P’ßÂ\Z=+ƒf1í)È¬dΩ”wä‹õäÒ5&≠øOÜï±†Q\Z\ZJ±†°\Z\Z˘X<E9tù\nüf¥à‚à4j8° vù\'ú4;≥¢Xv‚îçÅ—ø7kÚ]Å\0ëÂ‚2∑ë2È’ÜX@‚ªgDµ=K> ™çt:pÛIîé-.‘ZÀ™9.ç#∆Œ¯8jå˚\\∆Ph†éÌÃ‘÷ôï¥·¸m÷.F√∫ ØÖX∆◊Ro ¨.8êCàÉ•(RŸôAåcø®U*Pª∑æ°Ã˙µ∫t´-U∂\0áÈù˘Ü`~ì…D7Ã—x:ù∂púr!Û{\Z˚ÊŒ<ìü¬1@›…@Ê)ÖÊ˘EuY7∏ˇPŸ«H¸ÇIÊÏÇ€æuÿ%ÔÕ¬€ˇPK6“ƒM)\0\0ß\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xmlÕZ›n#IæÁ)Ç2±ùø±ôd‘vÏ¸˘ﬂm;1pQÓ.€ùTW5›’±Ñ‘õŸïvaY@	òù]vóπGpπŒå4èí~Ä~Ö=’v≤Ÿƒ&¡vI‰\"N‹UÁ‘9uŒwæS’œû˜M≤tÜm«`t+{ç,a™1›†ù≠HMÕ.?ç<ﬂ˛ﬁ3÷n\ZNÍLsML˘≤É9á!ŒLßNrÙx+‚⁄4…êc8IäLÏ$πñd¶◊”í∑G\'Ce£o˙ƒ†ß[ë.ÁVre•◊Î=È≠>avg%ñH$V¬ß◊C5F€FÁ±™F£o´bå›(Fã	ï≈£—µï—ˇë•Ò\"oπ&Ÿæˆ√µ˘€œ∆\nFÀ«¶Õ“¯k±¥≠®Lû∏w„µ»§yﬂùSáÒäçë ¨»ı>∞‡	a¥Ÿé>[π/‚Òbs∏Õe»m:ÔNºˆ4\Z_õO¯6:›…Àé«WWWgì^Ì≤^Îc8›E¥Éù;\ZZååhdõ€.ûM«>MŸ¨Á‡<”Ò4ÈmDúGã_6ëµlP˜±~ﬂYì,ú©aÁÚ}˝ŒRn¬Ÿ\"ñ„≥oÂ‘ÿãE◊7◊gó;-U÷6◊g<«hº¯d	≈.<µC©ï©9\"2pÊmeßÁÃúöÄ±ŸÑ73Uu7⁄∫ÃÊsR\ròÀ”å∏&Ωõ‘ãíûbÏtaY}ﬂ/Y§qfO^{,:„Í˜ù*&X„Xœ⁄≈KüÂmtôˆxXì@q||9}·⁄àCq˛_Íj	 å+î2Nù∞uÛEF(øÑ:8Ö¥”éÕ\\zEPNB%*ÓÛA\ZÓ2¢„ª1≤à\0’Ï∫é©P&KC∆¥¯@∏LB·máÕ‚]ñ!‰J5\0YÿŒ⁄Ã¨bÓﬁ-\Z3#Õ {ë`D∆laΩ:p‡AîL’0áibX÷KÜ∆]{˙FÃÎ£$‹©,æÖå≤QZèıB`[º≤,aàsUΩ¿Ldwå{%rZj¥ƒ˝4T„Bÿí¨	7€Û9xc-ePd\"€\' ﬁOVbÖnÎ®kô\Z/G˚«95ﬂ/ú+ºØP£~Æ¸˝îcırÌ‹Rè±sΩ±NsµBæëéûÁU≠ó´àf¢ı¬°\rE…+vg<±£d•[Öø2:¸™ö	£≤õçWï~ö¶¿ŒıhÛh?Qãgπ∂ZwõÉÿéöÌ¸yf5üé©Õ£´yî?l≠¬\'å=>™X≠¯Z¢∂õË{]tT><é\'z¬_%5sÿjdÕ8qõªŸ_ÈGÖh´Qwıùh/∑£8˘tØó6g-ò€‹≠˜Ù›N\"R>‘ˆ\nNÛ®IZ0÷t⁄4+D7…ISçˆoçè£FÅîTÂ∞ñMe ÒÑ´Ô÷◊B[ V˘∏°ìZúl4U+U∆vR;j4V.◊+ÌJ∂^V3ı„£h¨T…‘äJ¶RØe˙ŸF6QWOyQçÈ≈Tá2g\r€·9Ë˜Å◊PæOoB|Ò\n,ñ À¡a±LcBD,^Mçˆ≥Ã÷p€,”Á9åt)Ê®®•p—Wç\\:Ö=Y]güÊGZ±æ&5\'£π∂\rˆÏ @V¿ù43Mtèé˚Ëï«¨òqX+∏Án◊µ8ÚÇ≈û RÁêØ\nÿØ∞ﬁ!∆2H«\0Ê+∏i\"ßHã≠ììQ^¿ßÇ	¥g–∂èbvÒI±√\nåßë%å®¸`éS§ÇXJ0)«ê^ÅÙfî‰xLò|»,$»üÿç:πC|œå€Öˇ…VspëËc&ë«ê§öå£¿∞tàÄR®tíû:êﬁ¢ä§—\\∂•Ú6§MXOÇÙ0t+\ZqÅ„\nÁ∂∞\nÍ°‡ 2x•É(~õ¸ñî2•Ë!ØZH˘*MT_ S4´0v◊]éëÌçÕDbs.j,@K‹è\0rçNÆ¶ÓB|,C≈à+qF∑êÍ=`≠4¢\Z&t|äìÓ¬˛h`üBµ.$ñﬁ∞a®ù%ÉlKJO‚ƒÉ\\o\rñé8\røäM ajò+¥C€m»ZYMø¨VyüñÒ≥°˛è≥S& @%Ü*‹¬I!jntâRÄIs$Õw˘©¯¨2◊÷ÓÇ>ö¢V—ﬁ%¨Ö»Œ¯Í8ï±ÁEóãíü¬Jé\rLtGf\nªÍ£Àﬂ\"MÊ» aì€Ö	ó!aòÒH˙\"Aº–.LZ¯}K’DÑ\0wuJ¢âr ≠íéC´∞„CiëeÄã–¯bõ\"Ú@˜;|9á†AqDK.’∏+ãıÖ˛⁄mµ2õi®∏úç*÷\"LøŸxlÖó7=ˇ=w—é{ø¡∫&C\'h÷÷‘˙ßH=(ÕxÎàlò)ƒÌPù¬–ãâÚ2e]ﬂˇ·è˙$˘≥Áø¸Õª/ﬂ˝Î ˚€ï˜Í É?˛}Â˝ÁÍ˝æ˜ûÔ]¯ﬁæ˜ëÔ˝Œ˜~Ô{Ω?˘ﬁ_¸ãó˛≈ß˛≈+ˇ‚3ˇ≈◊˛ãKˇ≈õ`¯^0¸ ~?Üü√ø√ó¡Û`x\\æ\n.ø.ø\n.ˇ\\æﬁ|º˘<x˚è©’ˇãY?¯—œÒÎw_Ω{}Â˝ı ˚‘˜>ÙΩﬂ˙ﬁ«æ˜âÔ˝—˜˛ﬂÜ√ØÉÀó¡Âó¡€/Ç∑ØÂ\\|>9‘¬7•T\"àì7IV\n1:˙§*g÷5aítTr‡:‹hƒ÷;\rÉwÛà∫à§ƒÖÉ§ ÉÌ¬˝ÛÎ8´\"”qig9ù;Z^çmFó´¿M∞3œØh7%Ïë\0PæXÿûÿ7áÎsYÖW9h]ºÆ<‚›ê\'¡\r√‡ìî8¢=üƒ ÷V£â¯”9v˘°Kæ9c6ã˙”Éˆ1ü l∑ëKdxvB¥»–bYd\0]ñ-ÿˆ‚}-∫ûQ:Mßd RSﬂÜYπ˜∫È ¥q∑øPKåÖ≠{\0\0 +\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xmlçîMo£0ÜÔ˚+,∂W0&–4VB•=¨∫Uª[©YÌﬁ*«ûÄ≥ƒF∆ÑˆﬂØ˘âzàƒ¡ºÛÃÃ;√«˙˛˝X†òJjµÒHz◊B™l„˝ﬁ~˜Ôº˚ÙÀZÔ˜íö◊GP÷?Çe»•™äˆ°çWE5´dE;BE-ß∫5¶–9MªFΩÚ^Hıo„Â÷ñ„¶iÇfhìa≤Z≠pQ¡\'Æ¨M—QÇc(†ÌPa<≤≠√kMµÏ‹í÷zj‘‚ΩÈÆ]Ü1ÓÔG:3Bü\r‡ÿvôe˛IBÛ’C√¯≥ÖG^:n∑µëÆ;3(0Ãjì>…ùÅ_Ä„ ¢ ∫yí™~˚{w˚v£V\Z}\0nqÜ«Ê[-·Gk|Qr-8µ“ê>H0;y@ú@°∆ùj§Kaœ-≤⁄¢,*\\–∏â)$\0ùt·JπC¶◊x™‘€ñJZ…\nüË:=2ÂˇëÖ{>Ë§8Ç<Ä¸\\≤}ÖÓŒ≠∆w[É¥›†Ô.≤‹í%ç\ZÜCˆ9◊N4$óê¯$ŸíòÜ	ç¢ŒdOuô•ëÓi‚á±í-!4â(IÇÑf\\[æ`*´Y©*¸üO]—IÍÉp©ÃÁºÄ*%q<T∫ú”¢6› ÈÀv˘∞àüìª◊ã¥âò˘·Ô>“G0⁄=òó\\≤¨Êæ˚xÎ{\\Ô3yPËïÁÓ†≤nÄÛ’Owe]ø Jé:›≤]>◊µ≤o·ı¢<∫¡G1DΩkﬂæKµúëd“À+Û©ÓX∏—Få‚ÚvyÓxn¡åë€h5Ñî{ö\\Z®J∆]üK2q$&¨+ßÿKb»vÖç˜CÌ5\"◊a—uÿ‚:,n1|ˆ˝„œ˛µÈPK©1¸6Ñ\0\0©\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdfÕì¡nÉ0DÔ|ÖeŒ`†óÇ9Â\\µ_‡\ZìX/Ú.%¸}]\'™¢™*Õ°«]èfﬁhÂÕˆ8ÏC;4`kûßg⁄*Ëå›◊|¶>y‰€&⁄∏ÆØ^⁄Ûjãïüj~ ö*!ñeIóá‹^‰eYä¨EëxEÇ´%yL,∆ºâ≠FÂÃD>ç}ÕÚ\rf™yú%¥N:º9ç0;•ø£:PòÇDÉ	L⁄ÜLã˙ﬁ(-Ú¥£&)¶˜}‹ÇöGmâã-Æ≤Àc1´ü•£suøπÖá_5R`…„•ﬁÙø\"\"≠É∆?^vºÁ}°Î„ßì˜Œ˙F∫ãz˜Ü{\r÷?≤ùV˛G5—\'PK˝=´π\0\0\0É\0\0PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml≠TÀn√ ºÁ+,Æï°Õ©Bqr®‘/H?Ä‚µÉÇ%äˇæ8jUÂ*V}€«ÏÃ6ªì≥’b2ˆ¬üY®}k∞oÿ«˛Ω~eªÌj„ö…KPï9L◊¥a9¢Ù*ô$Q9Hí¥Ù∞ı:;@í?ÒÚ¨tÕÓ¨ŸvU›Ù:c°.Ûq∏°ªlm\Z&¶Hne≠Q5\r\Z¶B∞F+*0qƒñü\rÛ{üú‡DLÃÒ∞?d˜â ÿ$ËÚÄ˝Ñ„TbÏœR—iÙWŒqÇxt.∆ˆ,ﬁDÉÖ¥<-ïZûÿ©ÂIøk<∂›´SPO≥5ﬁ<v¶œÒLë÷Bi\rJÍ£–9∆ø/˜Z>áîq¥¿≥·˙ûaﬂà_¿ˆPKã\\ßJ\Z\0\0>\0\0PK\0\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0∂`ØB`˜√u\n\0\0Çc\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0∂`ØB6“ƒM)\0\0ß\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0∂`ØBåÖ≠{\0\0 +\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0∂`ØB©1¸6Ñ\0\0©\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0∂`ØB˝=´π\0\0\0É\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÎK\0\0manifest.rdfPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0öM\0\0Configurations2/progressbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0‘M\0\0Configurations2/toolbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0ÆN\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0ÛN\0\0Configurations2/statusbar/PK\0\0\0∂`ØBã\\ßJ\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0àP\0\0\0\0','odt'),(3,3,1,'Invoice','PK\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0Thumbnails/thumbnail.pngâPNG\r\n\Z\n\0\0\0\rIHDR\0\0\0µ\0\0\0\0\0\0zA†å\0\0!±IDATxúÌùw@G˚«∑‹^ø£pÙﬁª\n\"äΩ«Ç∆hL11â)æyı}”ì_ä)ÔõÚ¶K‘D£—h‘Xc{Ï<DÈı8Ó∏≤w˜ªBÔLîÁÛ«ÌÓÏÏ3s∑_füyfv`Ëız\0∫Ä—”\0z5†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0ñÈCW˙”º•7ä≤UØ˝[∫£Œ∂∏ˆë%ƒøxûÛÌÚr[å5¸›7\"˜>˘_<Tz¥◊áÃéﬂ∂¯HÙàƒqOÕÍ\'ƒ-≠âÆ|ÀK_Ú_{+]D¥&Í´~ùˇ•Ágo∆p⁄Â’7\\ﬁ}Œ>#Å¸m˛ó./¯≠€Ú˛øcπññ‘ä∫hû4à˛„94›ì2§®$ªéË|+7˛9•ÈæXQŒ%^|®t√ß\'åáLÎx‡±¢˝`8ÿÁ|¥ÜYZ¯ŒÂ+»à¨§√üÖø∏|Œ’yKŒ+\"1]˝È≥~ÛWº@Vm⁄Fêñ–ñ}ß8t•kßøß-°÷\n(&º„·˘á±Ùë>∆_ûÆπEƒNQoÂ©˝\n<¯π∞^ùÒ˘5—‡xù`˛≠[#!¨¯úÂØˇôÓÃ–Àr7¨Ω¯Àr9]¬yŒ%⁄£∫Hâa˜†åÆ:µeyCÇ;[òªÍÕÌ¬1Ò5G.ÿéOÌ`¯aÙçRµ¿âdÚÃá}+æ7aìºptÓ‚lF¿Ï¿≥…ˇ/@˘˘gã_Sπå˘àÉï¬ò»k_,ö◊Ë?o(¶”“ örôŒßmc`Ñ¥ãëpeÕ~‹âCóÏÀÆ¿úÃ\'Ëícgô¡≥Ò*ú IÃ ,Üx$e#}«Ùz ﬁÖ§Œâ„ìúõkåìLód‡Â‡![q™f‰p{ã€™6ıaÒ8ÜvC[}πPK4Í0Ç$0Ç0YRñó9πFZ‚?$X¶¬e∆äOÕª√g\Z>sœò≠¯∂9Àå?ÕœÔü’ÖùÈﬂ,1láÕÓX˜Ù!’ﬂû¢ﬁY˙ıXs ≤è[œ\\?”º£∫πo˝·Rö¡ß;ô*Ø)\\ı≠Õ¯OÔE”w“O5Ìg&õ6ÉG?Ω3åüÅa¶\\>√3Ó¡¯CAÔi7û≥óΩw◊\\,ØÙiÌµGy?ˆ›\'›U©>OÔ—–}\0(@\0\nı°º∏Ïïì^LiÎÍÍÚT\rıcwuQKÕıUK«,Œı›~¿ÉäÖ˙–‘◊”«ﬁyılÊﬁ—+råP]˛]˘Ù‰ ›‘Ôu∑ÖqnEárïLq∆‘ê?æ,ú¥–u˚·®ß\n∑≠‹∏”ê¡q¬XûTÆ≈D–Z=hXv«‘%Á´|º§^≈t®9yîLÓÉˇvU?“â∏©√	ú‡z«ıcbjU#∆w``8?(5FDbZ\'\"ﬂòÅ…«Ø‰ÀÙûv–Ä<`X¶¶Gå®V:˜”4{;–‘øú>›¯9§CVS¯¿•XSÂò2Ò”wå°QıµÔﬂ\nm‚x∞∞≈«9>ÒÆ¬6±Pù¨‡¢¬3BLuöΩmîB%ŸµØTlhPàŸË“˝[k&˘H6≥óÊ“R¥Òåc%‰–$mÎË	Ç¶≤B9Ël@{,‘áé÷™éˇ∞äåã•nU”);ºbã*eBX£Ã>HPp‚:ŒsMNw‹∑F“îËJu 3c<HÈŸ’üm·èXπ·”≠t⁄¨Q5oó)äÈÑâŒŸªY≥¶“k~fÃ}∆è®‘µ5~„Ï∫•9£WΩ†›ü{Úd= ¯ÚóÁˇf?~∆XzÕÈÀú∞ÚNå/ö‡rÎó/∑UÖƒªïÊñäìcZ◊Ω?ÊCàe˙–‘VPlí$ç˘%ßØäìc}xÚSJ5≠«1“∆?<àMj¥Zå„»√I˚∞(g√ﬂ≤∫ÚR°ñÚSh1ú$í`ÿáEÆ™kI∂´áˆD>»C{¥DFñ◊*Ï[çﬂº(≥—‰W0	∞õ\Z¥qöjTaÑ√∫2Æƒ\\Hõ¿h∂V©2’ìMﬁÆ’`ºª¥3@;,”≈i8|⁄˚ÉE—,ãaH—{$txƒõÜ+4yŸád¬–Gﬁ|á“…KÀbw>!;hh€G¯H\Zª‰0ó¥0„P«‡◊µ%{÷Ò]hëíù5À’†ê»Fº`2ïË◊jñ.À˛C∑p!Àåÿ\\p6[¢6oUV”È§¶Ì”NãqC5Nr<CS\")Éüd(kΩÿƒa%>_ÿæ	·kWY¸|â\"•ykﬁ8‰wÒ√£	ãÊF\Z€ˆ©„á9Sc>zÙÎ%s=>˚∞ìÁVºgı^e¥˝Øˇ…ÀoË«ıÏD	˝GÚ,©2Â5ÿ©ıêtñÂz/øPﬂ¶˚û/UyW≠;ûßîÀ’ÎéÁàŸàáˆT9Â»≈Ù]—ŒêÜ˛≤ã£´K…Elp*ßÙ<#Ó\'>_Ï≈JZ‘Ù0=_∞∏î\';dkˇ|ô˛ﬁKî˙&˘jÿê°ÃÉÜ∂ù€ô√Gj\Z¬HÍc≥bª™Óœ-ß≈6“K2Ò`«}ƒ“˛-…$Ò∂… ∫†mv¶W∆0„÷‹∂GLç/∏XÆ1_ÖÓpﬁÒt lc&¶∂Óå∏øt[ˇ÷Ï:»8Ú√˜Á√féˆP]…ì˜Á]-ÿ˝ªîÙˆ≥ÛÌŒ^O∑˘f◊Å÷“z\n7\\RÔ- XL!Ezf^›RùŒƒ@7˙f◊ã˚L€L0]›ü≤ò0p&∫ŸˇË4ß¡ôfp>í≤$õˆﬂKd˝éú;Ô6∫Õˇ–Ïÿ~^Í÷/¯‹ø¶m\Zwl◊ëÊFáh˜=∏5∑∂~¸çd@V¯üü}.â…‡ªÚˇt \ZÆL˜–m˛ÊÊYæ±<!ÙÚÔ<OJ©1ZÈÌÓπª[£3bj5e+b·6AÆ5ßÆR5≈-W¶;ËFˇc‡3ˇå0æ˝mñ!%ÁPæ04eÙ”lévw\ZπõŸë\Zççi>;*ÕdMÎ‚Ë,|æp#¶[¸\n»˛Gß)Õ—Óød∂É5‡of¸(@\0\nÀÙAm^˙kï»+„±Ò>å∂]PÂ≈Ø.Ω=Ûã˜¬o®JÂülåwoz—¶≠?¯Xÿ~ê¬êÃ¡.áˆ¨xsï„∞¿íÚ€NWZ¯\\\nª^·**ZıèEJ©ßb\\Á$ºË0chÄy\"˚ÙèF√¥ıoù∂˛ÚÆ-•Eâñ≤aàÖ˙r⁄–Á‘õÁµHùÇÈ‡TØ”„\\n\\ø»(v”Dv\n¶≠?ËX¶ÜÁƒ≈/¥9–º√èqlÙı{„ºvá‰£õOç2N[œÅiÎ6±ÈÁ∆º≤§ÀìÃ¿yÔ˛5˚@ÆÄÙ†\0}\0(zR8ÆÎ_•ªˇ}`∑VmˆJÆπ≠¢s7nï¯øÚÔT…Ó„ıÓì:y≥-mÌ˝iuçãÕ•?nO|i¯ïï[ÍÊÃçÆ⁄∫%ªZ!àõ<) m¿h·Aˇﬂå˜·¨áı1eˆ„∆√¢Õ=„†)ﬁ^õ>›ÙäoäÒ#Ú•óMiˆ¶CèÈÔƒR}tõT€¨<£Ø‹±‰\'◊?ﬂè◊uYÕ1˚.çò°%k?*\Zªdà∞%ãÆÓ¬…Ü¿XÕÜ˜˛®°º≤Ó¥\0téÌá^S¥ÂìıWp{;ıç∆cú.®‰ó§±£DÁs•äb:~¨ÀŸM?Á⁄Õ¸◊b€ö¬ÑÖœ•8íw∑*´ó·áûü≥¨¶÷˝âgπWØHÁNZ?}°ÎÊ\'´j›ü˙á˜ˆ5<”cd\'•C«s÷~,ôætå)fﬂ˛—†©Ø◊û|w¡/∂›ãä‚ﬂùv˚É~Wƒñœ˙oEÀb5¬™3{è^8õıh@G$˜·+Xı|!¯A√G+6m(pÙ`<}úä%ï¥qfWÁ™∫\'l£‹ÛnË¥4¡1Eﬂ-¡°ÁãÇS„äs*¢¿∞®í™35¶f‚x9ï∆ˆÇ+5ZµBAsªäŸõ¬¸<“∏Rçii\ZF1…díÜÍ∂Y¨∆8É\r«\rÈzÉÖãı∑kÙ¡pKÔfx÷á7áaXs+m~7\ZãÀ4πXTx«´;áÈc_)ù˚œ4{”íπcbö“«öR∆%öéMÀ„NHÓ<fo\\æ¶F:˜Ûgõ√¸Yü¸/´ÂlÛb5”^õ≈ƒfÕ7.V£Å®øÖX°èÓpA-$*BﬂëŒcˆ»0ˇKÍB‘ﬂ\Z,’«C¥Óàü(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†∞J˙∫˝Ø}¬xıùd¡ùgÓX∑É.ﬁ∑Ω>T|∂,bFÆº¥D÷Pã{w\\’£ÌkW-ˇ<‘∏d™^Ï¨óúØ§ù‹]˘wô«Ò`¯>ﬁ\\Œ˜˘D:≥Z/Ó÷ØL˛¯∆¡‡aLºﬂIπÜ¯!W[±JÚkÁòÑf◊\'o‹§´Û$çzr@Î¬bÖÑ(\\l€Ç7n5ï|Òﬂì˝yÎïÖ«6gõ1ΩvÂ¥˘Ûm:p“·≈Â/˜,[qFò1*éìøk]éx∆º8Óo;æ⁄HèY¸ ¸tóá∫≠#xéöﬂóæ{ËkÚ€”VÄ[ÂË^w8Ø™v˚÷›◊„fe$8wr_Z±Ê7oÃœ.·∞éT&‚ﬁæx˝\n]‘va±6+¥êaÂÊ-∑Æ!~6:µû‰ªUù≠ﬂbzÌ™D‡hÔ‡Yü_“ÿüdŸG8SX\rM\nŸ:≠^O”Ñm`¥ªÆ”À\ZHaë#òwiÆ^ «¥4f˘\0∏ıPâ¡«ˇw@‡Á/ä ‚Ç\Zæø}W:∞FúËó>än9ö:∂Îú∏0ÓâÁ‚ÓjŒ◊Æ0,˘u„ròÍõ{ôæ+{õû9C&YQ≈„¿∏qõ9Œ\'sú)≈ÚpÎÀJÌÜç^9¬¬Ï›UèøBÛ™©@è”+ıÙ\Z¨—]vx{æÆÅ33∂C¶™¬úBáAA|É€£©)™°ôLmE	+,ÃæÉªe»)°®Uaì£ÓXt_SsKn„Œ©+WË5∏ùámÛ•öÚs◊®ŒLı!ÙıÁwûVáƒıÛ¸•ûév¨õ_»9€ªRÃªd”´ÎoŒ˛ÂTpÑ˝âü∑îÑçLºÆ7UØñïùπ¢lÙ¥Õﬁ~ç’?¢j€ˆ*∑∞Ë8/…˙ï?˛\\ˇÒBˇõïLú∆yuµÆÇì˚+ì•W(:1’óhîú™`g¶˝Ân∞v¨}ælûÚ¬¡√rÃ&<)™ãõÑ„$ÉQS\"UÜ8p¯çµ4ó°”™µT\'µ¿qΩÙñD∆wVV‹êz†xd˘±´n˝ºôNAŒlÖÿé\'(Æê◊À\r=i\'8|µ\\≠#µ¯√A¬r^ê´¥˚Øπÿ±.>¶™ï	Gπ„AË|îç˜êyì}π68<sÆ!ÅÆæ\"!;ª©î}‰î±∆úIë∆„yo\'õ“√\'È‘Ï∏a!æÏﬂ÷‹a¡&S\"f◊È?1Â˛ÿ±F$°∏|Í∑™\"fïÃ53û_|Sm√rç	∫˙’Œbï6b§? %ÄkÃ»rèımw%C–©…9õa˙éù–yÖª2ı†”∆i√ö˝6ûÉsK„or»ö¸∞vNòÒBQ$qEÍÁﬁy$T]∏Î\0ñ6ÃEVÆ±3‘´™\r;N cøﬁr\r`⁄ ù9kÙÅ≥,ÕuÓ√“©\Zän®=ÜqKıLÅÉwúÎç◊àÒ!V⁄”∆i;ıGe‚ˇãÁnû£&˜~≥µ\"nòKÓ)ﬂÕ¡ôG˙IOùë±≈Af\'LØVl›’ØˇÔ‡Ä«É=˙áW¸≤1ó∞Mò<\'MT∂sÕ…¿)1€6≠⁄\'-‡Mˆ,,°lÍ•.°‹kU|¸ÙYYÍ$ˇª8s÷ËCØ¨o§ÏÌk ´I? ¡WuÈ|Å“-0§æ¶RÈŸ?ë*P¿Ê{ßù”¶ñk¥júÀ◊Î1ãÔÏ*$)G?V}EôÑtÓØ©∏Q«vhv¬pS”ÑÄ´©/)êz∆È	Æà«$er-Ê@ÒuuZ.ªÆJÀ˜Ûupni™d§¿üÀ≈j%∑´o*¥Üb ÷VµéôãÁµ∆ÖõõAái¶f∞€B~}É∂NõÒ8¡”Jo‰º®]Ã1˙^MÑ?ÔŸ÷ü£¯<ú}{ÛÔ?œ«5\\{·Rë÷√y˚ÕÅœºø—|*@∞˜„=úPyMQ}tfˇÜÕWèÏ◊Dá≈ªu=@gç>4Eø}ªKÓjKxGŸúÿ{ﬁ>t“»à“úS∑µ[Z4pD\\w\'ı:q≈Hﬂ˛„ıÔò∑ù÷πg∏6,¨™^j«b∂∆#pü•SËÌ#cÒﬂØs=<ΩbCN]k‘cú.Ë¨Î 1ºÆì/Ú¥©ÆckkÍ’Üfê•*/hpdï%‡˛@∞ÖlB[ìw8Ø≥èJrì)qñVZMıOd?R\\E⁄˙F∆°^{≥FîgÊ4œ¶˝¿Y>¶m¿løÆ/\0zΩ™∂¡f∞ÖcQ©©Ê§aˇhyÒ4<™Ûb;˛Ú¯ã^zvkûÀËdg…Èm0ƒIcÌkä +⁄ıì€`¡Ω≥ ˇ∏µ„Îıπ⁄Ü∫∆∞ß_~ƒèeJƒ9\"ªè«´zë\'9~FJâE	„2T{œfM4ﬁ>ÓùuÒScﬂâ–◊‘U◊©\róöüj|;ÉÆ—KË⁄A¨#<!S¶ Ïùﬁ∆öº£›;Î¸èÛÊ∂ôWbo|™ô⁄®ázr◊É A◊ö-»8‰ÅYvÔ¨Û\Z⁄«ÄM‡6˝∆\'[eË-XpÔ¨{æ®ÂuÁ}_ñë—`‚éŒ<˘Û \\á‘…èÃºs0`ï>púÆ((Æ«hÆ9à€†b7\nÀ•JmwUËQ¨{æP¢à)M√ÒÅ˛MéHH¿ô›P/†w`ù>å°\\}√•=ùÜtÄ`z¿∫¯«ÔﬂˇV90YwB¬–Gü¿ ¯áØV6z\ZÉÂˇ˙Véø<6€8√œûIB„—7∏áQìávÜ–U{∏hÛJã¶%òyüÊüΩü÷WZÙjY…Åùø2fﬁ∑˘ß@ÔßÕ+-8Ægë˜s˛)–˚iÛJeÎ;d¶)ò˘Ã˚M£,Å>MŸB|S∞	à˜íZ\0}<¥t˘nQ+wÔjÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@qı—<4äü+jô‰¨.‹µ_Ì&n0ÏËJ7øux¿ÎS›aî∏ß±Pt…ûÛ¬BÇcºπòR⁄@cji5.ˆÌr]’Nh\Z\Z‡—Ö+ﬁ˘Õ∆Bæª 8‹ˆ¸Ás~O]òÅ5ç◊â¥Ò<\\.ß1Ù—”Xvµ≤q∏NR!gMrÑ¸ÙM%iÏf]QMC:!¶¶1ä«R+ÀKu>ëÓ‚ê(÷–2.P\\/î5ÍÎ\ZıR^/¿2}êvÅQ1ÅQÕá£=Ó•®Ê°ïåá‚‰9Ø6\r\n$•¥‰1é`∆qÅßá°«Ä˚C¯ßÀ∏\0pÄ˛Ä¢ß˚/∆>ãB›≈Í @èc°>¥•˚~<\'ËÌèù8Œ\Z >≥∑1jêãìW◊ˇ¢⁄ˆ_àÚ}Àˆû´©çô9™>ª>¢Û’ïÅû«“˛≠R„‡‰Tr‰∑õdFTç&<—GP!∑Æ®∂˝¬!Ã£¸H^’Hâ∫\\]Ëy,‘ÀgƒH„vÃº~∆çóó·√áe]Qm˚/:ÜKÊ+oeö“˝]Ô:ã	Ë)zCˇz+ΩóÚOE1r⁄NL’6‘´r,òk‹,‘Gc˛˛3:?7\'/qªµ2UÂ7‰\"£ÏÌ‚ÎeG˜]°8Á÷û\n}<∫îHõƒ\'p˛qGÔ√2}Ë‰Â’Z°M—©su˝áàÆÔ€{¨Ä?Èô¥ ≠â˛i\"ãjÁü≤§¶J¡seKçu%.WŸjÙËπˆ¿˝«2}âSå—à„ëÎƒπCL…3&[QT[ˇwNô>∆∞ﬂzΩüx©Ωêﬁ‡üö/µ7Òu\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Ë@˙\0PÄ>\0†\0Öe˙–’[˝ÎUn‹§)ë‹î¢íÏ:BM∆é‰‡I©Ü—UßwêE\rwﬂ˜Vv¬#UøhÊø1/Ωy„lÅMjt≈√©—)ﬁT…÷•õTÉ\'çOrc\Z≠4^⁄tÃn\\öã%µ–’‰¨=„óïÓLöÈ“˝[k&ÖrZäŒÙ=ÛÂ6ÕÄa£“˘ÊJÍ‰Ê\nd§;^ÔXêNVpQ·!¶,ˇµ\Z/¨\\WÓl„íb*∑/`ô>Æì≠\"ßÏ˙Ê7ﬂ_v÷o˙3}næh;~(¶´:µeyCº´ZrlwÌÙW=¸•$–°$˜\n~„¯õoîNJ`+i6Ω˜´sæ3~‹êø(ããa$[Hü_˛ÏãkO~˚z§Vu¸áU‰¿ÅåÏUˇ˘πa‡ËØ§!∂«é©#íä÷oÁ•áﬁ˙≥6(÷UY\"ë€ä§JÂo~m;!©hWÂ»ya≠3‘M_π«lca\0Ü·ó“‹¯ÂÛçóÙtπ$Ô¨z‹¢iI-–ÊÇ‚b©[’4A éÊÃú†‹ü{2;wÂSπ…Èé˚÷H˙è]©NyfåvkÎ«ﬂHb∆∫ú;xÁπ¶Nå9˜›F†Ùä]pÇ©‹æÅe˙†k%ÂîØW≤ú]É¢‹u*=FêÑÒ…‚q∏b∑õ˚ΩÉ±=[Îÿ£Á≥Â∑Jnk\Zµl√πneª∑ñ∞˚\rb`\ZAPjBàfÛF•^I5 ´ )6Ií≠•i¬÷?<(Ã”ÍµJπ\\Qv±PK˘™˘Å—< ^|coMÙ#^’Ö◊rµ≥æ\'‘*=ã,Ø’`<ä◊d?r4Yéi‰9˚™5>·Aî‰ÿ>örh‘\Zj©©≠0d¯æí”W≈…±⁄SÃ8vSﬁRÆ„8Úp“>, Ÿ–§ht¶C¬∆pñMjTóıq3Xs`7ï€ù∑•◊`ô>.ÈO=mﬁ}™))aîi3ÈÖß0]›ü“¯Qc“›õ~≤ËL[º®ÌıK˙5Ô•Mt1|fΩª<Î]√Vq˛Jëh÷,W√çã~¨9á˙Ê^ŸÑ	√ºô£SöÆ1ÿoúÍNÖ&çiJJ¬¥%{ŒâK‰FÃ[aŒô›∂‘©c4Ô*ƒJ∫©†»‡∆î8£ıD?õ>ªÂÇ¯0„ß¶\"/˚êL:r·¬v\ZH07îªæ*®oà˚{¸S¬6fb˙=]…çòûqg\Z”+còˆI◊aYÆ©†Æ†ú¢;uy÷∫rx,’é„›Z†g—Îıù¶[—~|1uˆeó§>|<œ_ΩÙ≤Zøÿxwf«|¥dÌGEcˇ%9P4‘è}è∂ÉjkwdMª˙Ï”≈?‚LyÎùdaWıY2§ìS-hÆ9Î£Íå9$\";ûÕ_˝IÈ¯&	’Wø˚øíio¶¢L=Ä ˛¯≠–;pÍlß’ØOﬁ·ı¯À¡⁄™3ªw›ÚÁßÊL˝ıkÔ_æÃ±1r…Œ∆ó_Y¸Eˆ€o˚gÁ(ø/–œ˙‰QÆ ;%ò˜w|ìé\\<$Á›‹ºSI€á,{‰…woåzˆâ4óÏÂEì∫˛∂µûì˝˙\Z¡îiä]y±™÷˝…EÓ?}VΩÀ©j©ÁU\"Hä’∞}¡#gGº=·‰åî˜l&ŒtÀòÏw‰◊	›≤YüULZË≠…ˇÓâü%ûÈ1≤ì“Ø<üÍ‘QNVËCym√⁄:o«™¶_ÑÂß˘û3(Üã·¸®yèó}u ÍVÅÎÎnc™(««â∏‹‡–›∏›óX\\’âZﬂiaLL+\ZêPtñ3áùk(Á˚Ö)/¬\Z4$âÛÉR„x9ï§¿À?z°íÏ–È¥jii\rM®ÎãÛÆ´1ª–»»0\\’H´ïjCà¿~”wa{¡ï\Z≠Z—PqˆHcDí´õøcb≈Ì[∞~eÎ¡∏D”&£¿|®-˝ÌÕ˛£C∏Âùı≈Y_òSáˇMïDÒˆ∂ıméÊºhﬁ∆~g¸üÑas_]◊r÷‘SÛÀÿéV®ÄÎóµÊôy·ys∫˙⁄™˙«ü\\î(07ŸLnæfÊﬂÙ\rz5VË„“¶˝vô>y9\no1áÎË‰Ó 7F@ö„òcﬂy£€*Ÿs0[ÿ”ïËI¨–≠U[∑µÄ^ë{|«ﬁ‚ÄÃ0ocÃÒõ≠ecb˚LºπØaÖ>å!HÉ«∫cÃ—7∆ùEå≤Z\r&Ï3!£>Ö˙+µ¢9œè3zßZÉMqvúıUé éá+Ù9c6£ãs}¬WÎãX°èOﬂ>2-iY“+Óﬂoüs˚Ìy≥æ>ÛÙØ€É7Ø8YUÎ˛‘‚–ﬂÔ¶ß}Û©«Í\'ó^tÈÔ≈í›jÁ„Qˆ›WwàÍv?VË#Z∑ÓΩüËAÈô1\"F1…ƒ0Så¡W0‹)í…4áhı:å„ﬂO˙yÅˇá80–G∞B©o|ï⁄ºüı≈û¶ á1∆`äå¯®)a¡+L·ê∏ŸY—›3Ó›ﬁ$]ŒpHﬂ√ ¯òÖs€L˛Înr◊Ø>ß&fç˜cµü2x”ª}]ˆ÷’è\Z#ÿæfg>#Ò≈˘#òíuüûàùÂyb«eÒ†–äú<µT˙HÃ˘◊ÉÜ˙]ZøÍë8ÔŸπÉDDUŸ™¯òÍƒÓC$A é¸∏ßF=aºËÚÌ†°˛í›€˜Á;åõy3«8Ô˛ÕnŸ$OõàÔ[ÒÍ¸çÙòó«€ﬁêù>yP0*ærÎÍ+…ˇ˚,I)itÙ≥ÔÛ∞µr9ó•íIq]É±î\nZèÒ]D;Pé±	é◊+›b¸ﬂΩ©H.ª÷(tÂT_ì*dL/õûT∏5Ò1ñÄêú0MŒ”‰õ¶‚)k|›Í7_7™˘Ê©x˜7hÊZ∫c˘Wı2ö∞\råv\'X<Æ‰B°ñÚWhYv.<}Ôö(ä≥ÌÏ€eö`{é^ŸPy[r≤ZÁÈâc\Z…œ_úÂÃé8}Jqç¥∞úìißëTq¸ƒv6kJJd⁄0·–~àç¢˘/è5∆«‚≤Á∑¶{GeN|º]÷˚4ãô˚lLªÑÕ≥œÜå6o˝zMËü\rxdŒ\0„^t‚Ù÷d„t¥ˇºm‹Î∑tÔ\\sZ‘„gˇ¥ŸXœbÖ>¨ò¢◊«&·=ƒX°èﬁåÍùµzh∞T]ÕOnzÖ_ÙZ@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0\n–ÄÙ†\0}\0(@\0äˇÇæHÁV|~Ω\0\0\0\0IENDÆB`ÇPK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xmlÌ€r€∏ıΩ_°rß}Ë,Ø∫´ëvúÿª›ô$ìŸ(mg:ùLB2ªºÄ,ªô}ŸÔÈWıKz\0^ÃªHâùMÚ‡H¿9¿π_@ò~Ò›ÉÎÓ1°∂Ô-%]—§ˆLﬂ≤ΩÌR˙∞˛^ûIﬂ≠~˜¬ﬂll/,ﬂ‹πÿc≤È{˛\0∂G·ÏR⁄o·#j”Öá\\LÃ\\¯ˆb¨E\Zz!ˆ\nG({t\Z£‡46√¨)2áÕ‡¢€Ê;‡4∂E–æ)2á°¶—7~S‰Í»§ÓàŸ9*€˚y)›1,Tuøﬂ+˚°‚ì≠™œÁsUÃ&õ	\\∞#éÄ≤L;òoFU]—’÷≈5•è√¶IÚvÓ-&çEÉ*hïﬁo[ƒ˝∂B4Ê\"çmC\0g’;¥ö´wh•q]ƒÓ*t2Sﬂ¿§¯ÒÊıì-∑È^6#*ìÿAc6CË4æÔ˚	©!tPAÆ°i#5¸ûÇﬁ◊ÇÔâÕ0IÅõµ‡&rÃD‚æ[&4Ä”UÄêÒ=7”ƒπ hÇ°Ü”	0µ*ó˛˚õ◊ÔÕ;Ï¢\'`˚0∞l{î!ÔI2Ñ+°í”±Jp‡ñf”<`Ç∂åÑ∂;Ê:’ÓŒgc–-±¨RP g®ÇÎÉ„…˜6ﬁ#e\"yΩAÃs!¬‚!îéõµ∫¶ròƒç¡DûÇ<Ÿ&yh„Ô<`rW$@¸`bÛ)‰¥EfÖLÙµ±ªw≤Ÿ2@©ÏR–6Xµ,RÿŸ`N‹áfÀqKˆ≠M~≈úWõîYôÚ÷?©|NÊπ¢u¥S*á“*Nÿ°≥S5ÿ@‚ñ7»ƒ≤ÖMáÆ^ÑÅ7Ñﬂ9›KÈoê∞x%@`}ec0◊vó“Q‡”?ß`¬iêYí√ [ÏÅ\0 ﬂE^\"∞ô	ëÚ;4ë‘è»Éd˙Ëﬁ˙é§÷ºFw∞Å^Bl8sˇıŒ¥-4xè<:¯‡ŸP·J∆K`KêgØ’Ùü≤4 º≈˚¡Oë ˘ ¡u¢‘§]òSBP<^Ω5›€îûºı‡-\"ƒﬂWä$\r‘@çàR´2\ZG;be∂)ãuO?s6sãùa≤_Dypc,ÒM ⁄`¬l–o8º∑-^¶LcfLl–aX‚\"«ﬁB$qŸBéãWÊy]v¡“óíCdv+I—uàHÂ™îL®ﬁúùÎÂ®\rãDG„Ìö2é¶vb;rb<’ı?µ¶ÙÂ9(çgµîŒé°ÙUÁîéÕ–«uî\'˙l÷û‘Î3u6çÍHùi£#(ΩÈúRHÌ≥I≠˙Á”È¥=•Wz©ÿqÚÑ¬PöÃçøêˆ˙ö¢\rg\'Fo}ba\";x…◊Û=ú%ˆˆÆdò˘Aq÷gå◊Ùö2ÿÄ˙ém\ræ—ƒøˆåñ«∏çËnL–ªÇ»D–ñ†‡.ûÄ~N\"æ»!÷_0ÇΩJî°).\0’\nÒÍ>µô®ˆÜ |8MŸ?{`+°¢?©ı‘∫›≤ot¿>Ø^ã.#≤a¶(\0%âAjˇu=`ôÃ áeË†êW2…\'~(´&r2/–õ€@¡\Z{≥]ôcn—Ì~í.¥:]‰\'Àt—\\ÿ£cÑ˝∫s´õã«ˆ8–çXÕ-1ƒäß∏g5‘q\'≤+≥.!”®`ƒ^¬÷øvîŸõG†‹€¬¢{à≥KiÉö™»øLuLz7Âg>ßΩã∆‰mwhCVX∂ò˛Œc®πæy&röı(ßYçµ‰Á⁄	a÷JÛ>mÂÇYN◊Œ™€WÜ2ôŒıﬂtU°U«wjXÌÚXû˝í<÷Ç˚£ ¯ﬂ¢µuÆÑMÙ®6£qıÊÿñÔ\"∂ı±ˆáS´≥˘)ôu~RNô∑ )z7-≈ b!<«\'K)>9˘B*e˝ºùK€œ\r~öU \n”-≈.–BP›ÙΩGÚ¶Ïûßw8•U:ÕŒª©Òõ€y{•ÇEÜ\Z=\0ß+⁄P◊ÅNîaÂ±ÂA‘…|8={™Ü0–NéLüõ-u”+]&≠ı#\"„|]÷q\":∆\'£q¡¯uâxÑ^Jﬂ~˛m˝¨˝—–>\Zu?_Xı◊ã\ZG?Tâ¯uú«µFQ√\\õ7Õ\r≥a≥4¬/$å&Á\r—9Õ’ı3©vûÖâ»®b0ıƒ3Sd{>qÀjºäF\'[eó ü≥2Œ€bÜ∑H¢g…ö¢ç∆√Ë·k4=OŒL	Y€ kèÖñ¬o≈»ô…gõeéÍ/◊¸Ò5˜ÊW·{zûå‹îán\Z∞fûó\\¬*9ÉNÆ•.µÅà’∫6ü[Èô\'Âa*Ù}…l„®m\rÇ„∆pÎ[è1òã(√D–∂ù\'∆S-∫ùœù©\rGµ]ç3‘≈b≈◊TÿK*<ã?ØDzTˇµÑ˚j∑=€ÌQg≠˚%öb]—gzıπŒÏÚãt&Êø£—\0}§õ|M¡âJv:cÛ˛’O:Òì·QG«\\˘Sœ®Ê∞Õ)—÷HüìıZmE0⁄Í¥ÉZÔ5øß>tµ$6:Î#±Q+ôâŒ,ìjß˛§Uº0~ä¥*≠•[çV,N:d±ëÚ˚dæp<—Ö7<õ+íÎ¬ôAgÏ}W◊ÖÛÜÀÒﬂ>Ó]T2ÖFÛ˘Y∆I¸\ZíÀÒ˜Ÿ^óYoè~πR+Y=hwMr]º$⁄ÅX;Ω&wBO≥.^Ωw≠clß|w[û»˜ÿÿ‡˘e¯Ó∂`º¯sÚuÒR`g¥O˙≥vñÇO+%ˆ≤F˘·˝≈8<KI◊å√ﬁµ€Æh´$∑≈éÌ ® #°«\'ñ ôŒî«÷ÊeF˝Ÿa9ÍëQµxI©ñÔC∂⁄b„vÈ˚†ì$2ÁËK…f»±Õ¨ò¯«Xà—|z€•›cÈmÊÎßq“.ëV&˝¬éjÂ`¢	~ ˘¬˜IΩ‹â∏\\hƒMao|sG„ß*—d8è≤Ö©Ωı¢◊π$WCƒ)˛˜{OÔ§)ƒêe”¿Aè≤øc‚AÅÉÔ±ÃI·t(™gGØÈ*ﬂ°≈bÎ¯˝9ß≠¬UuÍ\"◊·Ö´•Ñ(°±=›·õãwÌàü—{w≈˚|¬±4b4ìAçﬁ!R	≠\\Âˆ:à≤-¬´∂◊mnÚƒﬂÁV¿éS#˝È=i»Ÿa9|ÊV…Xß©©¥∫ryƒâtpeÁˆΩ%F<K√\rêWÑ‰Ö∆ÍÉg«s®UÔ‘M◊8|ÑÅÁ@Œ;xΩ2óVo1cJ-!j≠È_Ã Ú¸„˜≤<xyÛ√èo∞≠b3	Gñˇ˘		\'˘•gyñ\\ü¸¥WËõ.˛+K´O÷ìÙNTÍ´ˇ˝˙ﬂÅœ«Ä˚BÔ4Aø“ƒÕÍ#››2™≤_ÑÕ›ºΩŒZ‹fÿï1™ßJˆ+%ùQ¬/h˜∂wbÈâë∑4ÁÙH›>3©∂VØ#b!^Ê\"®sÉÜ∏®«„}ÁSŸõ-0z f(íh	9·ÁsÌy‘é◊xÉvé∏÷˝.æ_√ø|qhO]Z-BP|\\äè9c¢sπ%˝\\™™Û ÊØWÎg&ù{ƒûÉ`~ >•}àfP#õL*Ru∫oüF|Á°Ê<‰®ô35s\\Àˇç’ˇPK`˜√u\n\0\0Çc\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xmlÌ=€í€6≤ÔÁ+XJÌy£$RwùÃl9{7U«O6èS	I\\S§ä§F£l˘e?cr·4@ÄÔ\0Ii&Nú*«\Z@ﬂ—∏5øˇÎÛŒ’ûp:æw”3˙√ûÜ=À∑os”˚Â·Ω>Ô˝ıˆæ˜◊k«¬K€∑;ÏEzù\\j–ÿóqÂMÔxKÖN∏Ù–áÀ»Z˙{ÏÒFKzIáäKhg≤Õ)∞ÿ:¬œëlcõjãVÚ#S`±µ†£lc<õØ}Ÿ∆œ°´Ø}›Úw{9,û]«˚r”€F—~9è«˛q‘˜ÉÕ¿X,Zõ l%p˚C‡R(€\Z`ì¡¬Å—7vá#$ãÅQÚª§YÉ\"îìj¯¥ë÷àßM	k¨-\n§uÉß≈;≤Â≈;≤≈∂;mKd2‹C%˝Î˛√YÇùÏX6≈*+pˆ“d∆–b{ﬂ˜TIÉÿ@)∫Êp8ƒøËc%¯1p\"‡V%∏Ö\\+·∏ø+b\Z¿Ä–ÒQS¢K{ûº˜É(Ad-Ô†Ä;fb^€hÁñõ©Â†õ¿∂Aù—\0L\r]rÒª^ sV`ë\0uCuM(êËß*√ÅIÃDrv™¡&q˚kˇ‡0U0‚Á=RÖ\\⁄lôÍ!eãa8ääòÛÛÄ‘Èƒ7É˜a”É0%ôΩ[>ˇ¨}ò{÷»¬∫ç-7º˝>ˆI±ˇ&»›Ù~KÊ±PaÇì‡`;«=›Ù˛Ì˝ˇò∏†ß•∫$∞˙{@%®q‡ÔêóÇÿ;ëÜ˛Ñ\'Ê∏PGJàÅÊg=<ÌVæ€T#¸Ä∂0ÄQÄl\\S◊˛√¡rl§}F^®˝‚90ã„R¬`ê%OäÄr¸€t\rZjÒQ˚ô	°òÆ\\\'B≠AÌ\rÄπÒÚÚ°√£Ü≠á÷>¢ è•,Å$¯!Ö‘†Ã Yyr‰mºFóEçºgÜ„&@˚≠cı8,˚≠ÔpSA‰Ä4◊˛Ú•∫øè®K|ù¸Óiƒi-√-≤˝£É±Èœ7Ωaﬂ0ÊÜ„÷üÚıÃ˚:ÑIX˜»g†o˝¿˘Õ\'N-Ü6∆U–OM´\0fÈ~s∞EΩ2Œπ@Œ—â∂z˙F¡APî=\nÂ°»¡∏äÄÎË˘d–«∆~ä‹˝61Oä∆*¿¢ 0ùàx\rô⁄	n;7=7–£UJQœ∆d>%+ëòõﬁ\Zπ!Nd≥ËÇøâ&ï£ùÄºs‘B\\àlcwÎª~¿∏ATÜÜŒoÄ©aÓ#ZÊ\"os@(Ú\\Z`¡å†?§(!Ìtàê«[3ﬁ∞x›oœœºäu≈k<ﬂ√˘.IúÍ‚ÁíNì⁄Çnì:⁄Òôq)€í1∏Ñ”ΩJïÊlO˚-ˆË|Øª»∂q†Sl®∫ŒŒI(ê‘¨˝¡≥¢C‹!±`ÄtPêz’„*£€ò†GˆGã—ÏliÌ‹CœV—@Ö—ïLAmïåtÀ’•xÓæ®B“·µ*ò¢;’XQ°0wY-9çú∏™ô9†˝!‹f@ZòC)û…≈¢¢ƒã˛ïÌ\'ö9ƒƒbàÆµX\'wzp(…ÿ·å˜z‰op¥%´jbguã∆⁄˚¨∆FÅ›+u\\|.\nC@,¶÷C¯X∑r)0•¯W˘ÑÒ˚±…|ÃYoèEñU◊ÕŸƒÜU&ÜÇR˚¸FPà\ZEx\0∂?ö√«ïoüxá‡T˜.:ÈgM¨.’(Hvıb=+Rß¨‰3BÊÑÜΩ¥–~˘\\.“Zö«»Êü6ÑzÑ(†à—*∆µCÃç`¯{\ZNß\'f´X˘QD6LÜ˝·|49œÉ∞˙? 7%ŒÑFèçë{Dß∞Œ*ìcÀöåÃ∆E&–dn+ÏHañ\Z7?ëíQlB3.¢\"2∆R¶™ÇLÃú¥12⁄∫	óÕj.õﬂóŸåı›ö˛)ò¥2tƒŒf]Ø|◊NA«úÁ¥∫	œG’<Ω:û«´	◊Ÿ\0’ååìâÔüá0r÷\'–EoC!,ª§oKKJW»˙≤	».(èKæ“?óê€∏Zn„W\'∑ˆÁV˝ŸéÆ:ﬂÛ%ÛŸ†–ÏÀ¡	≥ct⁄ôëê ∑∆¥TÒﬁ)Ñ≈ÇÔ:	73 /ìc\"\Z–C0-;G¬v`u›DØ*bót)iÅŸâﬁP3ú≥£Ë bLT©ÛÍä=Íõ”©ëUFÊ„Í’x⁄üååEW∫,«◊˜æ˝…◊Ó˘˙@˚ƒdﬁípùky{\n¶ea:2ÕzQhÙ∆D|ûO∑t√dæ•5‘IÒ„˛°¬\"îSüYÄﬂbçV dy4éGî9W\ZP∆·w<ù8 bIÃ1ÿ$¿[Uœ˘Êâ;.Ô Qõhﬁ?\"´R˝)òñÖÈT˝ÂH˛ÛsáÆœ°˝ukoóûWúH8◊nœkãˆƒÇ‘v?¯G´›èÆ-2Ω[Ud•¢Ae˜®TåµXÜrâÊ,≠ü´®©d‹∑ 6ô¨Æ4r–≤Ú7êov´Næ{XJèÑpDqÑÀü„õ(≈Œñ¡ii†‰¿Ç¨+ºÂ\'¥°C|§&T<Å—“\0©ÓÀ’Ë◊_ıÿ£ÄKAƒTëÓ‚DMÇµj\'@¶:_Ü,BåCi\"H\'®IÏëÑÛN—u÷ë“⁄Û¬dlˆâ¨¸P J÷@KX˙·X:ñŒáØî•Ô<[∆XCs¨f°bg›ıtW⁄?qwN~º>≥Åµ–pMÑïóFíŸFóèhhÄ<kÎzº.\'ΩßÕí‹8rÿÖ¥ˇ7Ì-æ‚@¿]\'Úc©‹c[OzÛ◊*Ë≠\'–¯á âU‚*~=HáU,ÔÔs9™>œò,ÜÊ`Á´HqÈiS®-ÈFZ◊ˇ≥4ª_Éı‚ïUÜµdî<É~˙ÓOﬁî˘0˝A¿üõ>8á†\' 	ˇù={öΩqM/Ë‚\'Ï28˙\'{	f™ìõˆ◊â »^&kzñ°¶¨o…QÕuÙ\"£é_d‘…ãå:}ëQg/2Í¸EF]º»®∆∞Îay7∆p°ùÖwsXGlÔ¶Â[á»y‚[t„Éﬁö˚∆dpÂﬁ/<¨◊L#˝\"Zçjb„^wπ8∫Ç\ZNìÉΩAN\nõ›bLÔØÍ+çË\rSc13…ÃYFé±òvL–Ë≤Ò+≥◊#h|YÇ&ã·‚∫M.K–l>_ó†Èe	ZÃ«”Î4ª$AFﬂòìÎ4ø,A#–πÎ¥∏,AìŸ‰ n;tO—lfv„Ñj1  M!Y›ØùÕ!†Ìµ§Bg˚Òk∂AÃÉ\'äo‰≥j6õ”<`d“ÁTO»=W¡\n˘0°~>¢ßœ9ƒ6Iî£ì˛¯kyZpºπVEJ¡6]Ã~¬≤T\'c¶!($∫Â·3)E8óûW≈/⁄·WåŒz9Û”≈Îà’9û–\0djﬁˆ—ﬁŒO˙»V(ÙÈX:Ø([µ?’Ã]Â¨yh¡ˆ&b}ûˆçŸåﬁÁàﬂ_∞≥ùiæãQ¯¥@˙˙`ÿSà#ŸÕ=Ïº™qacVŒ7˙Ê‹ú2CTAÂáŒQˆÁÛπŸ\0ï∑‡ p<[4@ÂÆsTÃ˛b25@ÂMÓ8ªn( „ëÏ•1e¶€lkπG69’âr”„Ï?ÈÊ5A|≈£®*>œ&G„¨úΩ^äHc±R÷y∂òvú-Œ‹\'˚H£Á\'\Zø´¨Ò]3tÁÿ∂ããyj,∆•LÕ◊©sµê#í\\ÆkÀŒ<¸î¥†? óÎ¿.œı7≈≥ﬂe∏~Y—F≥/Î?Æ…„Kk∂—î≈ï\r˘êy Âò‡[b∂ x%˛ﬁ˝¯[… Î1ªxBî\\òÃ˙Ê¬ò]xab\\`abˆÕÈX}ab\\`a2ÈM∫∏SE•$î©}BWë¬-ÊäŒ6ˆïsÕpj<üñ⁄iæÓõ	ZçÎ.\r^	ó_:h5Æ¥æÆ_;l5Æ∂æ.KÈv◊õÈrÓUÛ∑œe%]nƒ’‹ì„p£}R0üæe¡∂2üvNÎ˛S£ƒÏ1n◊è<£ôêÔ\ZØ4Ô?µKI—4Û\0{{bàoO ü˙âOOåÃ€ëöw~ÈóE˘îïôó)ÜJﬁè˚OçrKdîß\rÎdôtY.¥À‘ÌaÚ‚|Xã˘nlúŒws˜Óµ0jzEF%Ÿc”¸öÇìÙ‘À tlòKÜCc∏XÿbÕŸªfaJx9o≈ π\Z\'g/®rYB_å	Ûº∞‘ﬁ&ù´úh‰ÿò\Z≥\Zçd0≤÷›ŒzùËúÃ[WÏ%f÷‰°Î$9_ÕzÌ…<	Õ§ï˙¶e‘.ø^K\rœΩñØz´_ƒJªYPîi#}>¿WŸ∆d¯ó∂Êøh7-ZMsEjó…ÆCŒf’´?©T∞\\µ¢≠≈®p™—ä$ï∆Q¡ÖΩ§±u≥‰Ëfˇ¬ÏOgãÙπWiÁ‰ºõ⁄‹êCÉπAEJç÷;Ÿc2˙SsbæêåÆe›,äæ£∏√-¿:\rn:7Ú/∑ ∫™æI“˚ê?º∆πœ%•8.ób˘.Mô|≥˘Ö∫\r˝ï2pﬂ?‰œC/ÀÆﬂSÚ«ómò¢í*∂ç¶\Zç˘ìºÓhî≤â•>ôv°ˆØg≥ˇ!”uF_ÌbT›!^ó5π¯Îz¨y»≈C◊c¿k€#+ËΩaF’˚á\\úı«e´◊Ú€√ïl+p.sçJC™Öà•á*C^\"Ãí€Tv«Ÿ]√6ªÇ˘]¡ó$<VïÎﬁm|uÅ›‡v‰µ†R_.&Rﬁ£e)´KÆ›äå¨dzKè¨ ï˙ï!/í´/Qîq≥€Ìßá¸˛Kg¥]âSmäV°`›Ê3s]íxë]#9Ø™°4kÜãN˛!J≥`ø3z@ô√%Z√Õ˚Êt6J.‹nÑ„9£?]òÂ91ÿ(Å∏˚ÅCæ_œ“å¯A \'ÊZ:=O4µ.HAoŒ‚4aÁ:~¡7ﬂ*π‘[ıı⁄‘∑ï#6Ås˛Ïî5$ˇA∞L!„QQÂ\nÖ8ı\nj,0I´ì\08úLJ\0c|≈l¢πnVÿıèô¸µ\"ïCE=KúÅ‡YbÙz>cÍúﬂ§%\0!Ê˚¿Ijå·p&|.ÜSòÂ€!\r(–h1/\0Bkí˝•FÃ.-≤Ÿ‰:HúcE|+úS¯òìø“Ã™x´™»,8Ω[z ¶ß¨çï≠ÈG|≤_X\0eU!˚}I¶¬≈⁄[b	ªN`◊$YˇˇL˘†[Ü¶$±mbÔ}¿~‹˝¯Ò)(Õ\"I@\ZŸºÑ$û˘™ODW‰Úu‹·Âká«´j^”~5¶7(“Ê¬é$4dÙ™5Ñœ`E\ZíØì–È)ÒOÁﬂëJ≥{±\nñÏåó∆]–R\Zõ˝ÊèàsAò#Õ2°Ìyû¥SrL^±Z¬^¿\0¸¯Ò?˝¯ˆÀlG\0k⁄Ä€’À˛F≤î&©Û$z\0≥Ù‰°«Ω[_©Ô˛í„ßÇêı/˙ˆÎ—±ø∆†Z©Ù0È›Æ\\dkJ-òÙh2<!/bà]lETEnz÷! ¸Ω[Éu-Ä´·ß\r\ZcG◊<ÖŒ/çÖSƒJIJÁÃ˙*¿ËKFDï*≥ ¢∂¿©¯;êv‰ïi3€ €p~’rEèû\\wc±ªﬂúΩÿïÂD\'ıNÿöTΩ·ä÷ò|ŸM¢#válP!\\C©Î¥w{á\",Ø3%≠ôΩ´»Ÿ·ØÚmÁÿˇSQÒhÇ»8ı	˝õ•Aâ´yﬁ«∏Ll»jRMY∂êRË˛õÃXµ\r~PmVµ¡]∂A‡3=`◊≠ …‡ª-Kö	î}∫#åHˆœ^„ßÒ¸îIeÏ\Zè=KîP£r∆∞w˚)ˇ	ûøX≥~¸D&QÕÛ˚†·Æ\r≈\Z0®‘GU5õ¢Ígº∆0Õ[∏+y7F&q}+ﬂˇBæ£”Ùª,.z|º≥1˘2Ÿ„£92ç˘t>4£9	ˇU£#1Z©é|’2£bœñ≥#ûΩmÃ3¢˘Á¯QÎJﬂ„cJÎªXRI°Q{Ì3;SÈ5Te∑fU\0Aﬂ•‘9ò\Z?cÖÈ“V\0\'+û’I°¡X):1 ˛Ôøˇsπ˛ˇ{‰∏GÈ=zVcJ¢P’ßÂ\Z=+ƒf1í)È¬dΩ”wä‹õäÒ5&≠øOÜï±†Q\Z\ZJ±†°\Z\Z˘X<E9tù\nüf¥à‚à4j8° vù\'ú4;≥¢Xv‚îçÅ—ø7kÚ]Å\0ëÂ‚2∑ë2È’ÜX@‚ªgDµ=K> ™çt:pÛIîé-.‘ZÀ™9.ç#∆Œ¯8jå˚\\∆Ph†éÌÃ‘÷ôï¥·¸m÷.F√∫ ØÖX∆◊Ro ¨.8êCàÉ•(RŸôAåcø®U*Pª∑æ°Ã˙µ∫t´-U∂\0áÈù˘Ü`~ì…D7Ã—x:ù∂púr!Û{\Z˚ÊŒ<ìü¬1@›…@Ê)ÖÊ˘EuY7∏ˇPŸ«H¸ÇIÊÏÇ€æuÿ%ÔÕ¬€ˇPK6“ƒM)\0\0ß\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xmlÕZ›n#IæÁ)Ç2±ùø±ôd‘vÏ¸˘ﬂm;1pQÓ.€ùTW5›’±Ñ‘õŸïvaY@	òù]vóπGpπŒå4èí~Ä~Ö=’v≤Ÿƒ&¡vI‰\"N‹UÁ‘9uŒwæS’œû˜M≤tÜm«`t+{ç,a™1›†ù≠HMÕ.?ç<ﬂ˛ﬁ3÷n\ZNÍLsML˘≤É9á!ŒLßNrÙx+‚⁄4…êc8IäLÏ$πñd¶◊”í∑G\'Ce£o˙ƒ†ß[ë.ÁVre•◊Î=È≠>avg%ñH$V¬ß◊C5F€FÁ±™F£o´bå›(Fã	ï≈£—µï—ˇë•Ò\"oπ&Ÿæˆ√µ˘€œ∆\nFÀ«¶Õ“¯k±¥≠®Lû∏w„µ»§yﬂùSáÒäçë ¨»ı>∞‡	a¥Ÿé>[π/‚Òbs∏Õe»m:ÔNºˆ4\Z_õO¯6:›…Àé«WWWgì^Ì≤^Îc8›E¥Éù;\ZZååhdõ€.ûM«>MŸ¨Á‡<”Ò4ÈmDúGã_6ëµlP˜±~ﬂYì,ú©aÁÚ}˝ŒRn¬Ÿ\"ñ„≥oÂ‘ÿãE◊7◊gó;-U÷6◊g<«hº¯d	≈.<µC©ï©9\"2pÊmeßÁÃúöÄ±ŸÑ73Uu7⁄∫ÃÊsR\ròÀ”å∏&Ωõ‘ãíûbÏtaY}ﬂ/Y§qfO^{,:„Í˜ù*&X„Xœ⁄≈KüÂmtôˆxXì@q||9}·⁄àCq˛_Íj	 å+î2Nù∞uÛEF(øÑ:8Ö¥”éÕ\\zEPNB%*ÓÛA\ZÓ2¢„ª1≤à\0’Ï∫é©P&KC∆¥¯@∏LB·máÕ‚]ñ!‰J5\0YÿŒ⁄Ã¨bÓﬁ-\Z3#Õ {ë`D∆laΩ:p‡AîL’0áibX÷KÜ∆]{˙FÃÎ£$‹©,æÖå≤QZèıB`[º≤,aàsUΩ¿Ldwå{%rZj¥ƒ˝4T„Bÿí¨	7€Û9xc-ePd\"€\' ﬁOVbÖnÎ®kô\Z/G˚«95ﬂ/ú+ºØP£~Æ¸˝îcırÌ‹Rè±sΩ±NsµBæëéûÁU≠ó´àf¢ı¬°\rE…+vg<±£d•[Öø2:¸™ö	£≤õçWï~ö¶¿ŒıhÛh?Qãgπ∂ZwõÉÿéöÌ¸yf5üé©Õ£´yî?l≠¬\'å=>™X≠¯Z¢∂õË{]tT><é\'z¬_%5sÿjdÕ8qõªŸ_ÈGÖh´Qwıùh/∑£8˘tØó6g-ò€‹≠˜Ù›N\"R>‘ˆ\nNÛ®IZ0÷t⁄4+D7…ISçˆoçè£FÅîTÂ∞ñMe ÒÑ´Ô÷◊B[ V˘∏°ìZúl4U+U∆vR;j4V.◊+ÌJ∂^V3ı„£h¨T…‘äJ¶RØe˙ŸF6QWOyQçÈ≈Tá2g\r€·9Ë˜Å◊PæOoB|Ò\n,ñ À¡a±LcBD,^Mçˆ≥Ã÷p€,”Á9åt)Ê®®•p—Wç\\:Ö=Y]güÊGZ±æ&5\'£π∂\rˆÏ @V¿ù43Mtèé˚Ëï«¨òqX+∏Án◊µ8ÚÇ≈û RÁêØ\nÿØ∞ﬁ!∆2H«\0Ê+∏i\"ßHã≠ììQ^¿ßÇ	¥g–∂èbvÒI±√\nåßë%å®¸`éS§ÇXJ0)«ê^ÅÙfî‰xLò|»,$»üÿç:πC|œå€Öˇ…VspëËc&ë«ê§öå£¿∞tàÄR®tíû:êﬁ¢ä§—\\∂•Ú6§MXOÇÙ0t+\ZqÅ„\nÁ∂∞\nÍ°‡ 2x•É(~õ¸ñî2•Ë!ØZH˘*MT_ S4´0v◊]éëÌçÕDbs.j,@K‹è\0rçNÆ¶ÓB|,C≈à+qF∑êÍ=`≠4¢\Z&t|äìÓ¬˛h`üBµ.$ñﬁ∞a®ù%ÉlKJO‚ƒÉ\\o\rñé8\røäM ajò+¥C€m»ZYMø¨VyüñÒ≥°˛è≥S& @%Ü*‹¬I!jntâRÄIs$Õw˘©¯¨2◊÷ÓÇ>ö¢V—ﬁ%¨Ö»Œ¯Í8ï±ÁEóãíü¬Jé\rLtGf\nªÍ£Àﬂ\"MÊ» aì€Ö	ó!aòÒH˙\"Aº–.LZ¯}K’DÑ\0wuJ¢âr ≠íéC´∞„CiëeÄã–¯bõ\"Ú@˜;|9á†AqDK.’∏+ãıÖ˛⁄mµ2õi®∏úç*÷\"LøŸxlÖó7=ˇ=w—é{ø¡∫&C\'h÷÷‘˙ßH=(ÕxÎàlò)ƒÌPù¬–ãâÚ2e]ﬂˇ·è˙$˘≥Áø¸Õª/ﬂ˝Î ˚€ï˜Í É?˛}Â˝ÁÍ˝æ˜ûÔ]¯ﬁæ˜ëÔ˝Œ˜~Ô{Ω?˘ﬁ_¸ãó˛≈ß˛≈+ˇ‚3ˇ≈◊˛ãKˇ≈õ`¯^0¸ ~?Üü√ø√ó¡Û`x\\æ\n.ø.ø\n.ˇ\\æﬁ|º˘<x˚è©’ˇãY?¯—œÒÎw_Ω{}Â˝ı ˚‘˜>ÙΩﬂ˙ﬁ«æ˜âÔ˝—˜˛ﬂÜ√ØÉÀó¡Âó¡€/Ç∑ØÂ\\|>9‘¬7•T\"àì7IV\n1:˙§*g÷5aítTr‡:‹hƒ÷;\rÉwÛà∫à§ƒÖÉ§ ÉÌ¬˝ÛÎ8´\"”qig9ù;Z^çmFó´¿M∞3œØh7%Ïë\0PæXÿûÿ7áÎsYÖW9h]ºÆ<‚›ê\'¡\r√‡ìî8¢=üƒ ÷V£â¯”9v˘°Kæ9c6ã˙”Éˆ1ü l∑ëKdxvB¥»–bYd\0]ñ-ÿˆ‚}-∫ûQ:Mßd RSﬂÜYπ˜∫È ¥q∑øPKåÖ≠{\0\0 +\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xmlçîMo£0ÜÔ˚+,∂W0&–4VB•=¨∫Uª[©YÌﬁ*«ûÄ≥ƒF∆ÑˆﬂØ˘âzàƒ¡ºÛÃÃ;√«˙˛˝X†òJjµÒHz◊B™l„˝ﬁ~˜Ôº˚ÙÀZÔ˜íö◊GP÷?Çe»•™äˆ°çWE5´dE;BE-ß∫5¶–9MªFΩÚ^Hıo„Â÷ñ„¶iÇfhìa≤Z≠pQ¡\'Æ¨M—QÇc(†ÌPa<≤≠√kMµÏ‹í÷zj‘‚ΩÈÆ]Ü1ÓÔG:3Bü\r‡ÿvôe˛IBÛ’C√¯≥ÖG^:n∑µëÆ;3(0Ãjì>…ùÅ_Ä„ ¢ ∫yí™~˚{w˚v£V\Z}\0nqÜ«Ê[-·Gk|Qr-8µ“ê>H0;y@ú@°∆ùj§Kaœ-≤⁄¢,*\\–∏â)$\0ùt·JπC¶◊x™‘€ñJZ…\nüË:=2ÂˇëÖ{>Ë§8Ç<Ä¸\\≤}ÖÓŒ≠∆w[É¥›†Ô.≤‹í%ç\ZÜCˆ9◊N4$óê¯$ŸíòÜ	ç¢ŒdOuô•ëÓi‚á±í-!4â(IÇÑf\\[æ`*´Y©*¸üO]—IÍÉp©ÃÁºÄ*%q<T∫ú”¢6› ÈÀv˘∞àüìª◊ã¥âò˘·Ô>“G0⁄=òó\\≤¨Êæ˚xÎ{\\Ô3yPËïÁÓ†≤nÄÛ’Owe]ø Jé:›≤]>◊µ≤o·ı¢<∫¡G1DΩkﬂæKµúëd“À+Û©ÓX∏—Få‚ÚvyÓxn¡åë€h5Ñî{ö\\Z®J∆]üK2q$&¨+ßÿKb»vÖç˜CÌ5\"◊a—uÿ‚:,n1|ˆ˝„œ˛µÈPK©1¸6Ñ\0\0©\0\0PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdfÕì¡nÉ0DÔ|ÖeŒ`†óÇ9Â\\µ_‡\ZìX/Ú.%¸}]\'™¢™*Õ°«]èfﬁhÂÕˆ8ÏC;4`kûßg⁄*Ëå›◊|¶>y‰€&⁄∏ÆØ^⁄Ûjãïüj~ ö*!ñeIóá‹^‰eYä¨EëxEÇ´%yL,∆ºâ≠FÂÃD>ç}ÕÚ\rf™yú%¥N:º9ç0;•ø£:PòÇDÉ	L⁄ÜLã˙ﬁ(-Ú¥£&)¶˜}‹ÇöGmâã-Æ≤Àc1´ü•£suøπÖá_5R`…„•ﬁÙø\"\"≠É∆?^vºÁ}°Î„ßì˜Œ˙F∫ãz˜Ü{\r÷?≤ùV˛G5—\'PK˝=´π\0\0\0É\0\0PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml≠TÀn√ ºÁ+,Æï°Õ©Bqr®‘/H?Ä‚µÉÇ%äˇæ8jUÂ*V}€«ÏÃ6ªì≥’b2ˆ¬üY®}k∞oÿ«˛Ω~eªÌj„ö…KPï9L◊¥a9¢Ù*ô$Q9Hí¥Ù∞ı:;@í?ÒÚ¨tÕÓ¨ŸvU›Ù:c°.Ûq∏°ªlm\Z&¶Hne≠Q5\r\Z¶B∞F+*0qƒñü\rÛ{üú‡DLÃÒ∞?d˜â ÿ$ËÚÄ˝Ñ„TbÏœR—iÙWŒqÇxt.∆ˆ,ﬁDÉÖ¥<-ïZûÿ©ÂIøk<∂›´SPO≥5ﬁ<v¶œÒLë÷Bi\rJÍ£–9∆ø/˜Z>áîq¥¿≥·˙ûaﬂà_¿ˆPKã\\ßJ\Z\0\0>\0\0PK\0\0\0\0\0∂`ØB^∆2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0∂`ØB,¯√SÍ!\0\0Í!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0∂`ØB`˜√u\n\0\0Çc\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0∂`ØB6“ƒM)\0\0ß\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0∂`ØBåÖ≠{\0\0 +\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0∂`ØB©1¸6Ñ\0\0©\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0∂`ØB˝=´π\0\0\0É\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0ÎK\0\0manifest.rdfPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0öM\0\0Configurations2/progressbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0‘M\0\0Configurations2/toolbar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0ÆN\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0∂`ØB\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0ÛN\0\0Configurations2/statusbar/PK\0\0\0∂`ØBã\\ßJ\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0àP\0\0\0\0','odt');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_items`
--

LOCK TABLES `bs_items` WRITE;
/*!40000 ALTER TABLE `bs_items` DISABLE KEYS */;
INSERT INTO `bs_items` VALUES (1,1,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(2,1,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(3,2,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(4,2,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(5,3,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(6,3,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(7,4,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(8,4,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(9,5,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(10,5,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(11,6,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(12,6,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(13,7,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(14,7,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(15,8,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(16,8,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(17,9,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(18,9,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(19,10,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(20,10,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(21,11,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(22,11,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,4,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(23,12,2,'Rocket Launcher 1000. Required to launch rockets.',3000,8999.99,8999.99,8999.99,1,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0),(24,12,1,'Master Rocket 1000. The ultimate rocket to blast rocky mountains.',1000,2999.99,2999.99,2999.99,10,0,NULL,0,0,NULL,NULL,0,0,0,0,NULL,'',0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_payments`
--

LOCK TABLES `bs_order_payments` WRITE;
/*!40000 ALTER TABLE `bs_order_payments` DISABLE KEYS */;
INSERT INTO `bs_order_payments` VALUES (1,1,1624870603,20999.95,'Status: Sent'),(2,2,1624870603,38999.89,'Status: Sent'),(3,3,1624870603,20999.95,'Status: Waiting for payment'),(4,4,1624870603,38999.89,'Status: Waiting for payment'),(5,5,1624870604,20999.95,'Status: Waiting for payment'),(6,6,1624870604,38999.89,'Status: Waiting for payment'),(7,7,1624871090,20999.95,'Status: Sent'),(8,8,1624871090,38999.89,'Status: Sent'),(9,9,1624871090,20999.95,'Status: Waiting for payment'),(10,10,1624871091,38999.89,'Status: Waiting for payment'),(11,11,1624871091,20999.95,'Status: Waiting for payment'),(12,12,1624871091,38999.89,'Status: Waiting for payment');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_status_history`
--

LOCK TABLES `bs_order_status_history` WRITE;
/*!40000 ALTER TABLE `bs_order_status_history` DISABLE KEYS */;
INSERT INTO `bs_order_status_history` VALUES (1,1,1,1,1624870603,0,'billing/notifications/1/202106/1/1624870603.eml',NULL),(2,2,1,1,1624870603,0,'billing/notifications/1/202106/2/1624870603.eml',NULL),(3,3,5,1,1624870603,0,'billing/notifications/2/202106/3/1624870603.eml',NULL),(4,4,5,1,1624870604,0,'billing/notifications/2/202106/4/1624870604.eml',NULL),(5,5,9,1,1624870604,0,'billing/notifications/3/202106/5/1624870604.eml',NULL),(6,6,9,1,1624870604,0,'billing/notifications/3/202106/6/1624870604.eml',NULL),(7,7,1,1,1624871090,0,'billing/notifications/1/202106/7/1624871090.eml',NULL),(8,8,1,1,1624871090,0,'billing/notifications/1/202106/8/1624871090.eml',NULL),(9,9,5,1,1624871090,0,'billing/notifications/2/202106/9/1624871090.eml',NULL),(10,10,5,1,1624871091,0,'billing/notifications/2/202106/10/1624871091.eml',NULL),(11,11,9,1,1624871091,0,'billing/notifications/3/202106/11/1624871091.eml',NULL),(12,12,9,1,1624871091,0,'billing/notifications/3/202106/12/1624871091.eml',NULL);
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
INSERT INTO `bs_order_statuses` VALUES (1,1,0,0,0,0,'00CCFF',0,43,0,NULL,NULL,'',0,1),(2,1,0,0,0,0,'2AD56F',0,44,0,NULL,NULL,'',0,1),(3,1,0,0,0,0,'FF0000',0,45,0,NULL,NULL,'',0,1),(4,1,0,0,0,0,'FF9900',0,46,0,NULL,NULL,'',0,1),(5,2,0,0,0,0,'FF9900',0,48,0,NULL,NULL,'',0,1),(6,2,0,0,0,0,'FF0000',0,49,0,NULL,NULL,'',0,1),(7,2,0,0,0,0,'2AD56F',0,50,0,NULL,NULL,'',0,1),(8,2,0,0,0,0,'00CCFF',0,51,0,NULL,NULL,'',0,1),(9,3,0,0,0,0,'FF9900',0,53,0,NULL,NULL,'',0,1),(10,3,0,0,0,0,'FF0000',0,54,0,NULL,NULL,'',0,1),(11,3,0,0,0,0,'2AD56F',0,55,0,NULL,NULL,'',0,1),(12,3,0,0,0,0,'00CCFF',0,56,0,NULL,NULL,'',0,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_orders`
--

LOCK TABLES `bs_orders` WRITE;
/*!40000 ALTER TABLE `bs_orders` DISABLE KEYS */;
INSERT INTO `bs_orders` VALUES (1,0,1,1,1,1,'Q21000001','',1,2,1624870603,1624870603,1,1624870603,1624870603,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(2,0,1,1,1,1,'Q21000002','',3,4,1624870603,1624870603,1,1624870603,1624870603,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(3,0,5,2,1,1,'O21000001','',1,2,1624870603,1624870603,1,1624870603,1624870603,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(4,0,5,2,1,1,'O21000002','',3,4,1624870603,1624870603,1,1624870603,1624870603,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(5,0,9,3,1,1,'I21000001','',1,2,1624870604,1624870604,1,1624870604,1624870604,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(6,0,9,3,1,1,'I21000002','',3,4,1624870604,1624870604,1,1624870604,1624870604,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(7,0,1,1,1,1,'Q21000003','',1,2,1624871090,1624871090,1,1624871090,1624871090,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(8,0,1,1,1,1,'Q21000004','',3,4,1624871090,1624871090,1,1624871090,1624871090,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(9,0,5,2,1,1,'O21000003','',1,2,1624871090,1624871090,1,1624871090,1624871090,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(10,0,5,2,1,1,'O21000004','',3,4,1624871090,1624871091,1,1624871090,1624871091,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(11,0,9,3,1,1,'I21000003','',1,2,1624871091,1624871091,1,1624871091,1624871091,7000,20999.95,0,20999.95,NULL,'','Smith Inc.','Smith Inc.','Dear sir/madam','John Smith','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(12,0,9,3,1,1,'I21000004','',3,4,1624871091,1624871091,1,1624871091,1624871091,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear sir/madam','Wile E. Coyote','Kalverstraat','1','1012 NX','Amsterdam','Noord-Holland','NL','NL 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0);
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity`
--

LOCK TABLES `business_activity` WRITE;
/*!40000 ALTER TABLE `business_activity` DISABLE KEYS */;
INSERT INTO `business_activity` VALUES (1,2,'1','Holidays',8,NULL,0,0,1,0),(2,3,'2','Sick',8,NULL,0,0,0,0),(3,1,'1','Programmeren',1,'',0,1,1,0),(4,1,'2','Testen',1,'',0,0,0,0),(5,1,'3','Epibreren',1,'',0,2,0,0);
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
  CONSTRAINT `fk_business_activity_budget_business_activity1` FOREIGN KEY (`activityId`) REFERENCES `business_activity` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_activity_budget_business_employee_agreement1` FOREIGN KEY (`agreementId`) REFERENCES `business_agreement` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity_budget`
--

LOCK TABLES `business_activity_budget` WRITE;
/*!40000 ALTER TABLE `business_activity_budget` DISABLE KEYS */;
INSERT INTO `business_activity_budget` VALUES (1,1,160);
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
  CONSTRAINT `fk_business_employee_activity_business_activity1` FOREIGN KEY (`activityId`) REFERENCES `business_activity` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_employee_activity_business_employee1` FOREIGN KEY (`employeeId`) REFERENCES `business_employee` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
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
  `mo` smallint(5) unsigned NOT NULL,
  `tu` smallint(5) unsigned NOT NULL,
  `we` smallint(5) unsigned NOT NULL,
  `th` smallint(5) unsigned NOT NULL,
  `fr` smallint(5) unsigned NOT NULL,
  `sa` smallint(5) unsigned NOT NULL,
  `su` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_business_agreement_business_employee1_idx` (`employeeId`),
  CONSTRAINT `fk_business_agreement_business_employee1` FOREIGN KEY (`employeeId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_agreement`
--

LOCK TABLES `business_agreement` WRITE;
/*!40000 ALTER TABLE `business_agreement` DISABLE KEYS */;
INSERT INTO `business_agreement` VALUES (1,1,'2019-07-12',480,480,480,480,0,0,0);
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
  CONSTRAINT `fk_business_employee_business_business1` FOREIGN KEY (`businessId`) REFERENCES `business_business` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_business_employee_core_user1` FOREIGN KEY (`id`) REFERENCES `core_user` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_employee`
--

LOCK TABLES `business_employee` WRITE;
/*!40000 ALTER TABLE `business_employee` DISABLE KEYS */;
INSERT INTO `business_employee` VALUES (1,1,NULL,NULL,0,0),(9,1,NULL,NULL,0,0);
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
  CONSTRAINT `fk_business_employeecore_user_core_user2` FOREIGN KEY (`managerId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_manager`
--

LOCK TABLES `business_manager` WRITE;
/*!40000 ALTER TABLE `business_manager` DISABLE KEYS */;
INSERT INTO `business_manager` VALUES (9,1,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendars`
--

LOCK TABLES `cal_calendars` WRITE;
/*!40000 ALTER TABLE `cal_calendars` DISABLE KEYS */;
INSERT INTO `cal_calendars` VALUES (1,1,1,39,'System Administrator',0,0,NULL,1800,0,0,0,1,'',0,0,2,1,0,'','',5),(2,1,2,78,'Elmer Fudd',0,0,NULL,1800,0,0,0,1,'',0,0,11,1,0,'','',1),(3,1,3,83,'Demo User',0,0,NULL,1800,0,0,0,1,'',0,0,14,1,0,'','',7),(4,1,4,88,'Linda Smith',0,0,NULL,1800,0,0,0,1,'',0,0,16,1,0,'','',31),(5,2,1,94,'Road Runner Room',0,0,NULL,1800,0,0,0,1,'',0,0,18,1,0,'','',1),(6,2,1,95,'Don Coyote Room',0,0,NULL,1800,0,0,0,1,'',0,0,19,1,0,'','',1),(10,1,9,193,'Peter Smith',0,0,NULL,1800,0,0,0,1,'',0,0,39,1,0,'','',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events`
--

LOCK TABLES `cal_events` WRITE;
/*!40000 ALTER TABLE `cal_events` DISABLE KEYS */;
INSERT INTO `cal_events` VALUES (1,'b958dfa0-6144-57fe-8810-7d4dd68e01b3',3,3,1624953600,1624957200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(2,'b958dfa0-6144-57fe-8810-7d4dd68e01b3',4,4,1624953600,1624957200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(3,'977ba999-a93c-5444-be0b-314d02bafe13',3,3,1624960800,1624964400,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(4,'977ba999-a93c-5444-be0b-314d02bafe13',4,4,1624960800,1624964400,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(5,'c37e1cc5-85e2-52a0-b0cc-e249878b3146',3,3,1624968000,1624971600,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(6,'c37e1cc5-85e2-52a0-b0cc-e249878b3146',4,4,1624968000,1624971600,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(7,'0ccbec67-03f0-527d-94e7-b63fb03bd62c',4,4,1625043600,1625047200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624870601,1624870601,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(8,'82992635-4cd9-576e-858d-858c44436a93',4,4,1625050800,1625054400,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1624870601,1624870602,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(9,'5aec17d5-0553-5674-9bd4-9ecfc7494992',4,4,1625061600,1625065200,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624870602,1624870602,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(10,'ea3c1220-96e9-563f-a875-c9c3127408f6',4,4,1624946400,1624950000,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1624870602,1624870602,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(11,'ac6d7c0e-cca8-5645-ba00-f4c569af4750',4,4,1624971600,1624975200,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1624870602,1624870602,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(12,'bc836fda-554b-5023-90c3-2fc316c39e1c',4,4,1624986000,1624989600,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1624870602,1624870602,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(13,'39b398bf-4e89-5b16-8e73-9a55b08bdbf0',3,3,1624953600,1624957200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624871088,1624871088,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(14,'39b398bf-4e89-5b16-8e73-9a55b08bdbf0',4,4,1624953600,1624957200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624871089,1624871088,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(15,'ccb3b5d1-89ff-59d7-8f6f-344f73eca67f',3,3,1624960800,1624964400,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(16,'ccb3b5d1-89ff-59d7-8f6f-344f73eca67f',4,4,1624960800,1624964400,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(17,'e0e2aa9d-d16e-550f-b3df-5c439f220088',3,3,1624968000,1624971600,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(18,'e0e2aa9d-d16e-550f-b3df-5c439f220088',4,4,1624968000,1624971600,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(19,'abed776f-733b-54ec-b0b1-3d36c9514ce5',4,4,1625043600,1625047200,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(20,'e7eab014-6a0f-549d-b11c-e25b30422c45',4,4,1625050800,1625054400,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(21,'f84dd5cb-6976-542a-ba54-5779100287f0',4,4,1625061600,1625065200,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(22,'b6cf3809-d8ef-58e2-8e00-5b43ac6ec200',4,4,1624946400,1624950000,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(23,'2908495d-c762-5638-89f7-fcf153f39fa5',4,4,1624971600,1624975200,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(24,'72d0c9b1-14ea-5585-b3fd-5031f6f4cfe5',4,4,1624986000,1624989600,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1624871089,1624871089,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(25,'4af4950a-0af4-5024-ae28-5fd88f47a707',1,1,1626696900,1626702300,'Europe/Amsterdam',0,'Recurring 3 times','','',0,NULL,1626700237,1626700237,1,1,'CONFIRMED',0,0,'FREQ=DAILY;COUNT=3','EBF1E2',0,0,NULL,0,'',1),(26,'48375d28-3b7b-5317-8944-34fff7b5abd0',1,1,1626713100,1626715800,'Europe/Amsterdam',0,'Recurring until','','',1626991140,NULL,1626700729,1626700729,1,1,'CONFIRMED',0,0,'FREQ=DAILY;UNTIL=20210722T235900','EBF1E2',0,0,NULL,0,'',1),(27,'acaf173a-344b-5fca-a182-037470b10d39',1,1,1626683400,1626687900,'Europe/Amsterdam',0,'No recurence','','',0,NULL,1626701136,1626701136,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(28,'c0a57c58-3c29-51d6-a455-f5112a56faa7',1,1,1626690600,1626693300,'Europe/Amsterdam',0,'repeat forever','','',0,NULL,1626701966,1626701966,1,1,'CONFIRMED',0,0,'FREQ=DAILY','EBF1E2',0,0,NULL,0,'',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_participants`
--

LOCK TABLES `cal_participants` WRITE;
/*!40000 ALTER TABLE `cal_participants` DISABLE KEYS */;
INSERT INTO `cal_participants` VALUES (1,1,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(2,1,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(3,1,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(4,2,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(5,2,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(6,2,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(7,3,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(8,3,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(9,3,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(10,4,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(11,4,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(12,4,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(13,5,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(14,5,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(15,5,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(16,6,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(17,6,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(18,6,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(19,7,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(20,7,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(21,7,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(22,8,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(23,8,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(24,8,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(25,9,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(26,9,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(27,9,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(28,10,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(29,10,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(30,10,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(31,11,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(32,11,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(33,11,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(34,12,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(35,12,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(36,12,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(37,13,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(38,13,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(39,13,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(40,14,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(41,14,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(42,14,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(43,15,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(44,15,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(45,15,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(46,16,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(47,16,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(48,16,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(49,17,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(50,17,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(51,17,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(52,18,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(53,18,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(54,18,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(55,19,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(56,19,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(57,19,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(58,20,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(59,20,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(60,20,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(61,21,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(62,21,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(63,21,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(64,22,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(65,22,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(66,22,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(67,23,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(68,23,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(69,23,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,''),(70,24,'Demo User','demo@acmerpp.demo',3,0,'ACCEPTED','',1,''),(71,24,'John Smith','john@smith.demo',0,0,'NEEDS-ACTION','',0,''),(72,24,'Linda Smith','linda@acmerpp.linda',4,0,'NEEDS-ACTION','',0,'');
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
INSERT INTO `cal_settings` VALUES (1,NULL,'EBF1E2',1,1,1),(2,NULL,'EBF1E2',2,1,1),(3,NULL,'EBF1E2',3,1,1),(4,NULL,'EBF1E2',4,1,1),(9,NULL,'EBF1E2',10,1,1);
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
INSERT INTO `cal_views` VALUES (1,1,'Everyone',1800,92,0,1),(2,1,'Everyone (Merge)',1800,93,1,1);
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
INSERT INTO `comments_comment` VALUES (1,'2021-06-28 08:56:41','2021-06-28 10:56:41',2,24,3,1,'2021-06-28 08:56:41','Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',NULL),(2,'2021-06-28 08:56:41','2021-06-28 10:56:41',2,24,2,1,'2021-06-28 08:56:41','In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',NULL),(3,'2021-06-28 08:56:41','2021-06-28 10:56:41',4,24,3,1,'2021-06-28 08:56:41','Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',NULL),(4,'2021-06-28 08:56:41','2021-06-28 10:56:41',4,24,2,1,'2021-06-28 08:56:41','In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=196 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl`
--

LOCK TABLES `core_acl` WRITE;
/*!40000 ALTER TABLE `core_acl` DISABLE KEYS */;
INSERT INTO `core_acl` VALUES (1,1,'core_group.aclId','2021-06-28 08:52:47',11,1),(2,1,'core_group.aclId','2021-06-28 08:52:47',11,2),(3,1,'core_group.aclId','2021-06-28 08:52:47',11,3),(4,1,'core_group.aclId','2021-06-28 08:52:47',11,4),(5,1,'core_module.aclId','2021-06-28 08:52:48',13,1),(6,1,'core_entity.defaultAclId','2021-06-28 08:52:53',NULL,NULL),(7,1,'core_entity.defaultAclId','2021-06-28 08:52:53',NULL,NULL),(8,1,'core_entity.defaultAclId','2021-06-28 08:52:53',NULL,NULL),(9,1,'go_templates.acl_id','2021-06-28 08:52:53',22,1),(10,1,'core_module.aclId','2021-06-28 08:52:59',13,2),(11,1,'addressbook_addressbook.aclId','2021-06-28 08:52:59',23,1),(12,1,'core_module.aclId','2021-06-28 08:52:59',13,3),(13,1,'core_module.aclId','2021-06-28 08:53:00',13,4),(14,1,'core_module.aclId','2021-06-28 08:53:00',13,5),(15,1,'core_module.aclId','2021-07-09 06:55:46',13,6),(16,1,'core_module.aclId','2021-06-28 08:53:01',13,7),(17,1,'core_module.aclId','2021-06-28 08:53:01',13,8),(18,1,'notes_note_book.aclId','2021-06-28 08:53:01',35,65),(19,1,'core_module.aclId','2021-06-28 08:53:07',13,9),(20,1,'core_entity.defaultAclId','2021-06-28 08:53:07',NULL,NULL),(21,1,'core_module.aclId','2021-06-28 08:53:07',13,10),(22,1,'core_module.aclId','2021-06-28 08:53:07',13,11),(23,1,'core_module.aclId','2021-06-28 08:53:07',13,12),(24,1,'core_module.aclId','2021-06-28 08:53:08',13,13),(25,1,'core_module.aclId','2021-06-28 08:53:14',13,14),(26,1,'core_module.aclId','2021-06-28 08:53:14',13,15),(27,1,'core_module.aclId','2021-06-28 08:53:14',13,16),(28,1,'core_module.aclId','2021-06-28 08:53:14',13,17),(29,1,'core_module.aclId','2021-06-28 08:53:15',13,18),(30,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(31,1,'addressbook_addressbook.aclId','2021-06-28 08:54:00',23,2),(32,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(33,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(34,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(35,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(36,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(37,1,'core_entity.defaultAclId','2021-06-28 08:54:00',NULL,NULL),(38,1,'core_entity.defaultAclId','2021-06-28 08:54:01',NULL,NULL),(39,1,'cal_calendars.acl_id','2021-06-28 08:54:01',36,1),(40,1,'core_module.aclId','2021-06-28 08:54:54',13,19),(41,1,'core_module.aclId','2021-06-28 08:54:54',13,20),(42,1,'bs_books.acl_id','2021-06-28 08:56:43',43,1),(43,1,'bs_order_statuses.acl_id','2021-06-28 08:54:54',44,1),(44,1,'bs_order_statuses.acl_id','2021-06-28 08:54:54',44,2),(45,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,3),(46,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,4),(47,1,'bs_books.acl_id','2021-06-28 08:56:43',43,2),(48,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,5),(49,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,6),(50,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,7),(51,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,8),(52,1,'bs_books.acl_id','2021-06-28 08:56:44',43,3),(53,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,9),(54,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,10),(55,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,11),(56,1,'bs_order_statuses.acl_id','2021-06-28 08:54:55',44,12),(57,1,'core_module.aclId','2021-06-28 08:54:55',13,21),(58,1,'go_templates.acl_id','2021-06-28 08:54:55',22,2),(59,1,'core_module.aclId','2021-06-28 08:54:55',13,22),(60,1,'core_module.aclId','2021-06-28 08:55:27',13,23),(61,1,'core_module.aclId','2021-06-28 08:55:27',13,24),(62,1,'core_module.aclId','2021-06-28 08:55:27',13,25),(63,1,'core_module.aclId','2021-06-28 08:55:27',13,26),(64,1,'core_module.aclId','2021-06-28 08:55:27',13,27),(65,1,'ti_types.acl_id','2021-06-28 08:56:44',47,1),(66,1,'ti_types.acl_id','2021-06-28 08:55:28',47,2),(67,1,'core_module.aclId','2021-06-28 08:55:28',13,28),(68,1,'core_email_template.aclId','2021-06-28 08:55:28',7,1),(69,1,'core_module.aclId','2021-06-28 08:55:28',13,29),(70,1,'core_module.aclId','2021-06-28 08:55:29',13,30),(71,1,'core_entity.defaultAclId','2021-06-28 08:55:36',NULL,NULL),(72,1,'core_entity.defaultAclId','2021-06-28 08:55:36',NULL,NULL),(73,1,'core_entity.defaultAclId','2021-06-28 08:55:36',NULL,NULL),(74,1,'core_entity.defaultAclId','2021-06-28 08:55:36',NULL,NULL),(75,1,'core_entity.defaultAclId','2021-06-28 08:55:36',NULL,NULL),(76,1,'addressbook_addressbook.aclId','2021-06-28 08:56:38',23,3),(77,1,'core_group.aclId','2021-06-28 08:56:40',11,5),(78,2,'cal_calendars.acl_id','2021-06-28 08:56:41',36,2),(79,2,'ta_tasklists.acl_id','2021-06-28 08:56:40',51,1),(80,2,'addressbook_addressbook.aclId','2021-06-28 08:56:40',23,4),(81,2,'notes_note_book.aclId','2021-06-28 08:56:40',35,66),(82,1,'core_group.aclId','2021-06-28 08:56:40',11,6),(83,3,'cal_calendars.acl_id','2021-06-28 08:56:41',36,3),(84,3,'ta_tasklists.acl_id','2021-06-28 08:56:40',51,2),(85,3,'addressbook_addressbook.aclId','2021-06-28 08:56:41',23,5),(86,3,'notes_note_book.aclId','2021-06-28 08:56:41',35,67),(87,1,'core_group.aclId','2021-06-28 08:56:41',11,7),(88,4,'cal_calendars.acl_id','2021-06-28 08:56:41',36,4),(89,4,'ta_tasklists.acl_id','2021-06-28 08:56:41',51,3),(90,4,'addressbook_addressbook.aclId','2021-06-28 08:56:41',23,6),(91,4,'notes_note_book.aclId','2021-06-28 08:56:41',35,68),(92,1,'cal_views.acl_id','2021-06-28 08:56:42',52,1),(93,1,'cal_views.acl_id','2021-06-28 08:56:42',52,2),(94,1,'cal_calendars.acl_id','2021-06-28 08:56:42',36,5),(95,1,'cal_calendars.acl_id','2021-06-28 08:56:42',36,6),(96,1,'ta_tasklists.acl_id','2021-06-28 08:56:42',51,4),(97,1,'readonly','2021-06-28 08:56:43',NULL,NULL),(98,1,'core_module.aclId','2021-06-28 08:56:44',13,31),(99,1,'core_module.aclId','2021-06-28 08:56:44',13,32),(100,1,'su_announcements.acl_id','2021-06-28 08:56:44',55,1),(101,1,'su_announcements.acl_id','2021-06-28 08:56:44',55,2),(102,3,'fs_folders.acl_id','2021-06-28 08:56:44',39,22),(104,1,'pr2_types.acl_book','2021-06-28 08:56:45',NULL,NULL),(105,1,'core_entity.defaultAclId','2021-06-28 08:57:36',NULL,NULL),(106,1,'core_module.aclId','2021-06-28 09:00:23',13,33),(107,1,'pr2_types.acl_id','2021-06-28 09:00:24',59,1),(108,1,'pr2_types.acl_book','2021-06-28 09:00:24',NULL,NULL),(109,1,'pr2_statuses.acl_id','2021-06-28 09:00:25',60,1),(110,1,'pr2_statuses.acl_id','2021-06-28 09:00:25',60,2),(111,1,'pr2_statuses.acl_id','2021-06-28 09:00:25',60,3),(112,1,'pr2_templates.acl_id','2021-06-28 09:00:26',61,1),(113,1,'pr2_templates.acl_id','2021-06-28 09:00:26',61,2),(114,1,'core_module.aclId','2021-06-28 09:04:17',13,34),(115,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(116,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(117,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(118,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(119,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(120,1,'core_entity.defaultAclId','2021-06-28 09:04:24',NULL,NULL),(121,1,'ti_types.search_cache_acl_id','2021-06-28 09:04:25',NULL,NULL),(122,1,'ti_types.search_cache_acl_id','2021-06-28 09:04:25',NULL,NULL),(123,1,'pr2_types.acl_id','2021-06-28 09:04:52',59,2),(124,1,'pr2_types.acl_book','2021-06-28 09:04:52',NULL,NULL),(125,1,'bookmarks_category.aclId','2021-06-28 09:04:55',30,1),(126,1,'core_module.aclId','2021-06-28 09:07:04',13,35),(127,1,'pa_domains.acl_id','2021-06-28 09:07:04',62,1),(128,1,'core_entity.defaultAclId','2021-06-28 09:07:23',NULL,NULL),(129,1,'em_accounts.acl_id','2021-06-28 09:13:41',63,0),(130,1,'em_accounts.acl_id','2021-07-22 14:55:05',63,1),(131,1,'fs_folders.acl_id','2021-06-28 10:11:18',39,29),(132,1,'core_entity.defaultAclId','2021-06-28 11:21:54',NULL,NULL),(133,1,'core_customfields_field_set.aclId','2021-06-28 15:10:34',10,1),(134,2,'history_log_entry.aclId','2021-07-08 11:01:02',33,453),(135,2,'history_log_entry.aclId','2021-07-08 11:30:50',33,457),(136,2,'history_log_entry.aclId','2021-07-08 11:37:41',33,471),(137,2,'history_log_entry.aclId','2021-07-08 11:39:06',33,476),(138,1,'history_log_entry.aclId','2021-07-08 11:39:54',33,481),(139,2,'history_log_entry.aclId','2021-07-08 12:35:53',33,506),(140,2,'fs_folders.acl_id','2021-07-08 13:06:30',39,31),(141,1,'fs_folders.acl_id','2021-07-08 13:07:17',39,33),(142,1,'history_log_entry.aclId','2021-07-08 13:17:43',33,588),(143,1,'history_log_entry.aclId','2021-07-08 13:17:51',33,589),(144,1,'core_module.aclId','2021-07-12 08:25:05',13,36),(145,1,'core_module.aclId','2021-07-12 08:25:14',13,37),(146,1,'core_module.aclId','2021-07-12 08:25:26',13,38),(147,1,'core_module.aclId','2021-07-12 08:25:26',13,39),(148,1,'core_group.aclId','2021-07-12 09:17:42',11,8),(149,NULL,'cal_calendars.acl_id','2021-07-12 09:17:59',36,7),(150,NULL,'addressbook_addressbook.aclId','2021-07-12 10:11:13',23,7),(151,NULL,'notes_note_book.aclId','2021-07-12 10:11:13',35,69),(152,1,'core_customfields_field_set.aclId','2021-07-12 11:55:14',10,2),(153,1,'core_module.aclId','2021-07-12 14:01:40',13,40),(154,1,'core_module.aclId','2021-07-12 15:10:45',13,41),(155,1,'core_customfields_field_set.aclId','2021-07-13 07:59:13',10,3),(156,1,'core_group.aclId','2021-07-15 12:08:32',11,9),(157,NULL,'ta_tasklists.acl_id','2021-07-15 12:08:32',51,5),(158,NULL,'cal_calendars.acl_id','2021-07-15 12:08:33',36,8),(159,NULL,'addressbook_addressbook.aclId','2021-07-15 12:08:33',23,8),(160,NULL,'notes_note_book.aclId','2021-07-15 12:08:33',35,70),(161,1,'core_module.aclId','2021-07-16 10:01:30',13,42),(162,1,'core_module.aclId','2021-07-16 10:06:15',13,43),(163,1,'core_customfields_field_set.aclId','2021-07-16 10:06:30',10,4),(164,1,'core_entity_filter.aclId','2021-07-16 13:18:19',8,1),(165,1,'core_module.aclId','2021-07-16 13:40:20',13,44),(166,1,'core_module.aclId','2021-07-19 09:40:54',13,45),(167,1,'core_customfields_field_set.aclId','2021-07-19 10:05:07',10,5),(168,1,'core_module.aclId','2021-07-19 10:15:25',13,46),(169,1,'core_customfields_field_set.aclId','2021-07-19 10:15:38',10,6),(171,1,'core_module.aclId','2021-07-19 10:49:56',13,48),(172,1,'core_module.aclId','2021-07-19 10:51:15',13,49),(173,1,'core_module.aclId','2021-07-19 11:00:34',13,50),(174,1,'core_module.aclId','2021-07-19 11:01:26',13,51),(175,1,'core_module.aclId','2021-07-19 11:04:50',13,52),(176,1,'core_customfields_field_set.aclId','2021-07-20 07:58:56',10,7),(177,1,'core_module.aclId','2021-07-20 11:30:56',13,53),(178,1,'core_module.aclId','2021-07-22 11:40:55',13,54),(180,1,'core_group.aclId','2021-07-22 12:48:11',11,11),(181,1,'go_templates.acl_id','2021-07-22 14:56:18',22,3),(183,1,'core_group.aclId','2021-07-22 14:52:58',11,13),(184,1,'go_templates.acl_id','2021-07-22 14:56:25',22,NULL),(185,NULL,'addressbook_addressbook.aclId','2021-07-22 14:53:18',23,9),(186,NULL,'notes_note_book.aclId','2021-07-22 14:53:18',35,71),(187,NULL,'cal_calendars.acl_id','2021-07-22 14:55:18',36,9),(188,1,'core_module.aclId','2021-08-24 13:14:28',13,55),(189,1,'core_module.aclId','2021-08-24 13:18:09',13,56),(190,1,'core_group.aclId','2021-08-31 07:43:12',11,14),(191,1,'core_group.aclId','2021-08-31 07:43:43',11,15),(192,9,'ta_tasklists.acl_id','2021-08-31 07:43:44',51,6),(193,9,'cal_calendars.acl_id','2021-08-31 07:43:44',36,10),(194,9,'addressbook_addressbook.aclId','2021-08-31 07:43:44',23,10),(195,9,'notes_note_book.aclId','2021-08-31 07:43:44',35,72);
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
INSERT INTO `core_acl_group` VALUES (2,2,10),(3,3,10),(4,4,10),(5,2,10),(6,2,10),(7,2,10),(9,3,10),(10,3,10),(13,3,10),(14,3,10),(15,2,10),(17,3,10),(19,3,10),(22,3,10),(23,3,10),(24,3,10),(25,3,10),(26,3,10),(27,3,10),(28,3,10),(31,3,10),(40,3,10),(41,3,10),(57,3,10),(58,3,10),(59,3,10),(60,3,10),(61,3,10),(62,3,10),(63,3,10),(64,3,10),(70,3,10),(77,2,10),(77,5,10),(78,3,10),(82,6,10),(83,3,10),(87,7,10),(88,3,10),(92,3,10),(93,3,10),(94,3,10),(95,3,10),(97,2,10),(98,3,10),(99,3,10),(100,2,10),(101,2,10),(106,3,10),(112,2,10),(113,2,10),(114,3,10),(125,3,10),(126,3,10),(133,2,10),(144,3,10),(146,3,10),(147,3,10),(152,2,10),(155,2,10),(163,2,10),(164,2,10),(167,2,10),(169,2,10),(176,2,10),(178,3,10),(180,11,10),(181,2,10),(184,2,10),(190,14,10),(191,15,10),(130,2,20),(20,3,30),(42,5,30),(42,6,30),(47,5,30),(47,6,30),(52,5,30),(52,6,30),(65,2,30),(66,2,30),(76,3,30),(123,3,30),(11,3,40),(18,3,40),(94,5,40),(95,5,40),(43,2,50),(44,2,50),(45,2,50),(46,2,50),(48,2,50),(49,2,50),(50,2,50),(51,2,50),(53,2,50),(54,2,50),(55,2,50),(56,2,50),(65,5,50),(65,6,50),(69,3,50),(78,5,50),(79,5,50),(80,5,50),(81,5,50),(83,6,50),(84,6,50),(85,6,50),(86,6,50),(88,7,50),(89,7,50),(90,7,50),(91,7,50),(102,6,50),(121,5,50),(121,6,50),(130,4,50),(134,5,50),(135,5,50),(136,5,50),(137,5,50),(139,5,50),(140,5,50),(192,15,50),(193,15,50),(194,15,50),(195,15,50);
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
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl_group_changes`
--

LOCK TABLES `core_acl_group_changes` WRITE;
/*!40000 ALTER TABLE `core_acl_group_changes` DISABLE KEYS */;
INSERT INTO `core_acl_group_changes` VALUES (1,2,2,0,NULL),(2,3,3,0,NULL),(3,4,4,0,NULL),(4,5,2,0,NULL),(5,6,2,0,NULL),(6,7,2,0,NULL),(7,9,3,0,NULL),(8,10,3,0,NULL),(9,13,3,0,NULL),(10,14,3,0,NULL),(11,15,2,0,NULL),(12,17,3,0,NULL),(13,19,3,0,NULL),(14,22,3,0,NULL),(15,23,3,0,NULL),(16,24,3,0,NULL),(17,25,3,0,NULL),(18,26,3,0,NULL),(19,27,3,0,NULL),(20,28,3,0,NULL),(21,31,3,0,NULL),(22,40,3,0,NULL),(23,41,3,0,NULL),(24,57,3,0,NULL),(25,58,3,0,NULL),(26,59,3,0,NULL),(27,60,3,0,NULL),(28,61,3,0,NULL),(29,62,3,0,NULL),(30,63,3,0,NULL),(31,64,3,0,NULL),(32,70,3,0,NULL),(33,77,2,0,NULL),(34,77,5,0,NULL),(35,78,3,0,NULL),(36,82,6,0,NULL),(37,83,3,0,NULL),(38,87,7,0,NULL),(39,88,3,0,NULL),(40,92,3,0,NULL),(41,93,3,0,NULL),(42,94,3,0,NULL),(43,95,3,0,NULL),(44,97,2,0,NULL),(45,98,3,0,NULL),(46,99,3,0,NULL),(47,100,2,0,NULL),(48,101,2,0,NULL),(49,106,3,0,NULL),(50,112,2,0,NULL),(51,113,2,0,NULL),(52,114,3,0,NULL),(53,125,3,0,NULL),(54,126,3,0,NULL),(55,133,2,0,NULL),(56,144,3,0,NULL),(57,146,3,0,NULL),(58,147,3,0,NULL),(59,152,2,0,NULL),(60,155,2,0,NULL),(61,163,2,0,NULL),(62,164,2,0,NULL),(63,167,2,0,NULL),(64,169,2,0,NULL),(65,176,2,0,NULL),(66,178,3,0,NULL),(67,180,11,0,NULL),(68,181,2,0,NULL),(69,184,2,0,NULL),(70,190,14,0,NULL),(71,191,15,0,NULL),(72,130,2,0,NULL),(73,20,3,0,NULL),(74,42,5,0,NULL),(75,42,6,0,NULL),(76,47,5,0,NULL),(77,47,6,0,NULL),(78,52,5,0,NULL),(79,52,6,0,NULL),(80,65,2,0,NULL),(81,66,2,0,NULL),(82,76,3,0,NULL),(83,123,3,0,NULL),(84,11,3,0,NULL),(85,18,3,0,NULL),(86,94,5,0,NULL),(87,95,5,0,NULL),(88,43,2,0,NULL),(89,44,2,0,NULL),(90,45,2,0,NULL),(91,46,2,0,NULL),(92,48,2,0,NULL),(93,49,2,0,NULL),(94,50,2,0,NULL),(95,51,2,0,NULL),(96,53,2,0,NULL),(97,54,2,0,NULL),(98,55,2,0,NULL),(99,56,2,0,NULL),(100,65,5,0,NULL),(101,65,6,0,NULL),(102,69,3,0,NULL),(103,78,5,0,NULL),(104,79,5,0,NULL),(105,80,5,0,NULL),(106,81,5,0,NULL),(107,83,6,0,NULL),(108,84,6,0,NULL),(109,85,6,0,NULL),(110,86,6,0,NULL),(111,88,7,0,NULL),(112,89,7,0,NULL),(113,90,7,0,NULL),(114,91,7,0,NULL),(115,102,6,0,NULL),(116,121,5,0,NULL),(117,121,6,0,NULL),(118,130,4,0,NULL),(119,134,5,0,NULL),(120,135,5,0,NULL),(121,136,5,0,NULL),(122,137,5,0,NULL),(123,139,5,0,NULL),(124,140,5,0,NULL),(125,192,15,0,NULL),(126,193,15,0,NULL),(127,194,15,0,NULL),(128,195,15,0,NULL);
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
INSERT INTO `core_auth_method` VALUES ('password',1,1),('googleauthenticator',6,2);
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
INSERT INTO `core_auth_password` VALUES (1,'$2y$10$I0kNz3Du1/fUjm4i/DSGjOXHg59LYmfujsZrUwYcQcDMvdHHwEwpK'),(2,'$2y$10$ZL0hKQfI0fX/ePRz/Oi13.Dz5ZnM.bRurhZmIk5e7y5D/SgVIrkmS'),(3,'$2y$10$uQ.OZninvckLAhcyytmLquRWDh5LXrnQcvP9kIMXGPYvW8dKFxCji'),(4,'$2y$10$lblVdE6RLvGDqM6BqKgnC.3lTaQQ2wpbzZpzgu5m7pn.T9XfOoDIO'),(9,'$2y$10$eoAH9ueLwHEjoaXI6ShZoeQJgIzEi1IG.jU4pwxyhmULTllyP2pUK');
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
INSERT INTO `core_auth_token` VALUES ('6123750c82098abc59b903213ff039b51b9c154980e65','6123750ce00506110af6c23bb91715aa5b1f84fa6e3e3',1,'2021-08-23 10:14:36','2021-08-30 11:35:23','2021-08-23 11:35:23','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('6123881d578447dcdcaa77b86a01992a16890c28c0a8b','6123881d757e9fe6ba71d87027d65b98c40fd59bcf8f1',1,'2021-08-23 11:35:57','2021-08-31 07:03:31','2021-08-24 07:03:31','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('6124b25fbca5848cf7a29f9b0ba4526111e604f951a15','6124b25ff18958e453dc75af46361bc622e307c970bf6',1,'2021-08-24 08:48:31','2021-08-31 13:14:04','2021-08-24 13:14:04','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('612742568195f09c1c93de49560cf134b08a2b0b44541','61274256ad9391054d81187bb12d873aa707b9d12fae1',1,'2021-08-26 07:27:18','2021-09-02 09:10:57','2021-08-26 09:10:57','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('612dd86cb14a9e90eebd039b4111aa3eebb44274d2d56','612dd86d445dea8ff958dc147aab011bd942cd12a98bc',1,'2021-08-31 07:21:16','2021-09-07 10:49:53','2021-08-31 10:49:53','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('612e095118d15a6c30ea79a755f42ddf133a440ee01a9','612e095157d7fd009eeb746a6eff718427ee9cf844f22',1,'2021-08-31 10:49:53','2021-09-07 10:49:53','2021-08-31 10:49:53','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password'),('61308176aa61b59f181b51f98bcefd3cf7c6a0bfad82b','61308176da438d6af3c5af5d458d1c8c40d0b2f4fdc7e',1,'2021-09-02 07:47:02','2021-09-09 08:25:32','2021-09-02 08:25:32','172.20.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36','Macintosh','Chrome','password');
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
INSERT INTO `core_blob` VALUES ('0ec2f1f4f9fb41e8013fcc834991be30a8260750','image/jpeg',27858,'elmer.jpg','2021-06-28 08:56:39','2021-06-28 08:56:39',NULL),('15457cf0c9556e24adbf79d611a57f23c18e31a0','image/png',2702,'system-administrator.png','2021-08-31 07:26:12','2021-08-31 07:28:37','2021-08-31 07:28:37'),('250ea8dbd7a92824a81eacc8125c376f32a87f4e','text/vcard',246,'5@host.docker.internal:8080.vcf','2021-07-20 11:33:22','2021-07-20 11:33:22',NULL),('40aad53af7f7e6d88982a25577286cf727cf18f0','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',6727,'Document-2021-08-23-11:36:10.xlsx','2021-08-23 11:36:10','2021-08-23 11:36:10','2021-08-23 12:36:10'),('47dcc09cd5ab65e913115e6004abfb4f06d9a0d2','text/vcard',237,'26@localhost:8080.vcf','2021-07-20 11:33:26','2021-07-20 11:33:26',NULL),('524aff24c8a2818ba4c1d8c2b8b2b22bc1425b4a','text/vcard',210,'27@localhost:8080.vcf','2021-07-20 11:33:27','2021-07-20 11:33:27',NULL),('54356b3c7d716b48ab13b77b3f4d61012d361c27','text/vcard',223,'14@host.docker.internal:8080.vcf','2021-07-20 11:49:47','2021-07-20 11:49:47',NULL),('55ffc4ca8945d6cbe420e3d5566ccb57128266fc','text/vcard',207,'9@host.docker.internal:8080.vcf','2021-07-20 11:33:25','2021-07-20 11:33:25',NULL),('64d5f732477ccf666a17e2544dc5f2516025433c','image/png',12648,'male.png','2021-08-31 07:43:58','2021-08-31 07:44:24',NULL),('7b6417a12cfd91021a114b03333214c1c5ee9ad7','text/vcard',248,'6@host.docker.internal:8080.vcf','2021-07-20 11:33:24','2021-07-20 11:33:24',NULL),('7bfbcf7bd8eca79efd7a49593c535fd18f91205d','text/vcard',206,'28@localhost:8080.vcf','2021-07-20 11:47:41','2021-07-20 11:47:41',NULL),('a277a250ad9fa623fd0c1c9bdbfb5804981d14e4','image/x-icon',171,'www_group-office_com.ico','2021-06-28 09:04:56','2021-06-28 09:04:56',NULL),('a2b13489e9762bf7d7dfd63d72d45f0f47411c30','image/png',31669,'male.png','2021-06-28 08:56:39','2020-10-01 13:58:25',NULL),('b7ffc18c76e66d0fdcb8de8c892a29d9213d1c7b','text/vcard',218,'7@host.docker.internal:8080.vcf','2021-07-20 11:33:24','2021-07-20 11:33:24',NULL),('b82d0979d555bd137b33c15021129e06cbeea59a','image/x-icon',492,'www_intermesh_nl.ico','2021-06-28 09:04:56','2021-06-28 09:04:56',NULL),('c363a83f50fe2fbe94deff31afee36d8d7923e17','image/png',57187,'female.png','2021-06-28 08:56:39','2020-10-01 13:58:25',NULL),('c9270970b441cac135735894dacd3bc21a01b0e2','image/png',34218,'icon-administrator-2.jpg','2021-08-31 07:28:34','2021-08-31 07:28:37',NULL),('da39a3ee5e6b4b0d3255bfef95601890afd80709','application/octet-stream',0,'unknown','2021-08-31 07:25:53','2021-08-31 07:25:53','2021-08-31 08:25:53'),('fc32a048a4f726fb116421a619aa55adbe859944','text/vcard',196,'29@localhost:8080.vcf','2021-07-20 11:45:33','2021-07-20 11:45:33',NULL),('fe464d739fd83bbe7aa26f21d7a097fb1d1a753b','text/vcard',212,'12@host.docker.internal:8080.vcf','2021-07-20 11:33:25','2021-07-20 11:33:25',NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_change`
--

LOCK TABLES `core_change` WRITE;
/*!40000 ALTER TABLE `core_change` DISABLE KEYS */;
INSERT INTO `core_change` VALUES (1,1,21,1,5,'2021-08-31 10:49:53',0),(2,1554,33,1,5,'2021-08-31 10:49:53',0),(3,1555,33,2,5,'2021-08-31 10:49:53',0),(4,14,11,1,190,'2021-08-31 10:50:10',0),(5,9,21,2,NULL,'2021-08-31 10:50:10',0),(6,3,21,2,NULL,'2021-08-31 10:50:10',0),(8,1556,33,3,190,'2021-08-31 10:50:10',0),(9,1,21,3,5,'2021-09-02 07:47:02',0),(10,1557,33,4,5,'2021-09-02 07:47:02',0),(11,1558,33,5,5,'2021-09-02 07:47:02',0);
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
INSERT INTO `core_change_user` VALUES (1,12,24,0),(1,15,24,1);
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
INSERT INTO `core_change_user_modseq` VALUES (1,24,1,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_cron_job`
--

LOCK TABLES `core_cron_job` WRITE;
/*!40000 ALTER TABLE `core_cron_job` DISABLE KEYS */;
INSERT INTO `core_cron_job` VALUES (1,1,'Garbage collection','GarbageCollection','0 0 * * *',1,'2021-08-24 00:00:00','2021-08-23 11:52:12',NULL,NULL),(2,28,'Newsletter mailer','Mailer','* * * * *',1,'2021-08-23 11:53:00','2021-08-23 11:52:09',NULL,NULL),(3,40,'Cron for instances','InstanceCron','* * * * *',1,'2021-08-23 11:53:00','2021-08-23 11:52:13',NULL,NULL),(4,40,'Deactivate trials','DeactivateTrials','0 10 * * *',1,'2021-08-24 10:00:00','2021-08-23 11:52:13',NULL,NULL);
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
  `relatedFieldCondition` text COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field`
--

LOCK TABLES `core_customfields_field` WRITE;
/*!40000 ALTER TABLE `core_customfields_field` DISABLE KEYS */;
INSERT INTO `core_customfields_field` VALUES (1,1,NULL,'2021-06-28 15:10:59','2021-07-13 13:32:07',NULL,'Only in shared','Only_in_shared','Text',0,0,'',0,0,'',0,0,'','','{\"maxLength\":50}',1,0),(2,2,NULL,'2021-07-12 12:13:18','2021-07-12 12:26:26',NULL,'For piet','For_piet','Text',0,0,'go_check = 1 AND firstName = Piet',1,0,'',0,0,'','','{\"maxLength\":50}',1,0),(3,2,NULL,'2021-07-12 12:13:35','2021-07-12 12:13:35',NULL,'check','go_check','Checkbox',0,0,'',0,0,'',0,0,'','',NULL,1,0),(4,3,NULL,'2021-07-13 07:59:25','2021-07-13 07:59:25',NULL,'WBSO','WBSO','Checkbox',0,0,'',0,0,'',0,0,'','',NULL,1,0),(5,3,NULL,'2021-07-13 07:59:57','2021-07-13 07:59:57',NULL,'Bedrijf','Bedrijf','Select',0,0,'WBSO = 1',1,1,'',0,0,'','',NULL,1,0),(6,1,NULL,'2021-07-13 08:57:26','2021-07-13 09:30:39',NULL,'Customer type','Customer_type','MultiSelect',0,1,'',0,0,'',0,0,'','','[]',1,0),(7,4,NULL,'2021-07-16 10:06:42','2021-08-24 11:30:41',NULL,'Client','Client','Contact',0,0,'',0,0,'',0,0,'','','{\"isOrganization\":true,\"addressBookId\":[]}',1,0),(8,4,NULL,'2021-07-16 10:07:04','2021-07-16 10:08:59',NULL,'Date in','Date_in','Date',0,0,'',0,0,'',0,0,'','','[]',0,0),(9,4,NULL,'2021-07-16 10:07:14','2021-07-16 10:07:14',NULL,'Date out','Date_out','Date',0,0,'',0,0,'',0,0,'','',NULL,1,0),(10,4,NULL,'2021-07-16 10:07:37','2021-07-16 11:38:21',NULL,'Location','Location','Select',0,0,'',0,0,'',0,0,'','','[]',1,0),(11,1,NULL,'2021-07-16 14:19:11','2021-07-16 14:19:11',NULL,'Manager','Manager','Contact',0,0,'',0,0,'',0,0,'','','{\"isOrganization\":false,\"addressBookId\":[]}',1,0),(14,1,NULL,'2021-07-19 11:26:53','2021-07-19 11:26:53',NULL,'Action date','Action_date','DateTime',0,0,'',0,0,'',0,0,'','',NULL,0,0),(15,1,NULL,'2021-07-19 11:28:52','2021-07-19 11:28:52',NULL,'date','date','Date',0,0,'',0,0,'',0,0,'','',NULL,1,0),(16,7,NULL,'2021-07-20 07:59:20','2021-07-20 07:59:20',NULL,'Month','Month','Select',0,0,'',0,0,'',0,0,'','',NULL,1,0),(17,2,NULL,'2021-07-29 12:03:23','2021-07-29 12:03:23',NULL,'For piet','For_piet','Text',0,0,'',0,0,'',0,0,'','','{\"maxLength\":50}',1,0),(20,2,NULL,'2021-07-29 12:05:28','2021-07-29 12:05:28',NULL,'For piet1','For_piet1','Text',0,0,'',0,0,'',0,0,'','','{\"maxLength\":50}',1,0),(28,4,NULL,'2021-08-24 12:16:46','2021-08-24 12:29:03',NULL,'Client Month','Client_Month','TemplateField',0,0,'',0,0,NULL,0,0,'','','{\"template\":\"[assign contact = entity.customFields.Client | entity:Contact]\\n{{contact.customFields.asText.Month}}\"}',1,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field_set`
--

LOCK TABLES `core_customfields_field_set` WRITE;
/*!40000 ALTER TABLE `core_customfields_field_set` DISABLE KEYS */;
INSERT INTO `core_customfields_field_set` VALUES (1,NULL,NULL,NULL,NULL,24,133,'Shared','',0,'{\"addressBookId\":[1]}',1,1),(2,NULL,NULL,NULL,NULL,24,152,'Customers','',0,'{\"addressBookId\":[3]}',1,2),(3,NULL,NULL,NULL,NULL,58,155,'WBSO','',0,NULL,1,2),(4,NULL,NULL,NULL,NULL,69,163,'Main','',0,NULL,0,2),(7,NULL,NULL,NULL,NULL,24,176,'Group','',0,'[]',0,1);
/*!40000 ALTER TABLE `core_customfields_field_set` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_customfields_multiselect_6`
--

DROP TABLE IF EXISTS `core_customfields_multiselect_6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_customfields_multiselect_6` (
  `id` int(11) NOT NULL,
  `optionId` int(11) NOT NULL,
  PRIMARY KEY (`id`,`optionId`),
  KEY `optionId` (`optionId`),
  CONSTRAINT `core_customfields_multiselect_6_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact_custom_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_customfields_multiselect_6_ibfk_2` FOREIGN KEY (`optionId`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_multiselect_6`
--

LOCK TABLES `core_customfields_multiselect_6` WRITE;
/*!40000 ALTER TABLE `core_customfields_multiselect_6` DISABLE KEYS */;
INSERT INTO `core_customfields_multiselect_6` VALUES (5,4),(6,4),(9,3),(12,3),(14,3),(20,4),(23,3),(26,4),(27,3);
/*!40000 ALTER TABLE `core_customfields_multiselect_6` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_select_option`
--

LOCK TABLES `core_customfields_select_option` WRITE;
/*!40000 ALTER TABLE `core_customfields_select_option` DISABLE KEYS */;
INSERT INTO `core_customfields_select_option` VALUES (1,5,NULL,'Intermesh BV',0,1),(2,5,NULL,'Intermesh Holding BV',1,1),(3,6,NULL,'IT',0,1),(4,6,NULL,'Partner',1,1),(5,6,NULL,'Small',2,1),(6,10,NULL,'Rack A',0,1),(7,10,NULL,'Rack B',1,1),(8,10,NULL,'Rack C',2,1),(9,10,NULL,'Rack A, 1',3,1),(10,10,NULL,'Rack A, 2',0,1),(11,16,NULL,'January',0,1),(12,16,NULL,'February',1,1);
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
INSERT INTO `core_email_template` VALUES (1,28,68,'Default','Hi {{contact.firstName}}','Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if] {{contact.lastName}},<div><div><br></div><div><br></div><div>Best regards,</div><div><br></div><div>{{creator.displayName}}</div></div><div>{{creator.profile.organizations[0].name}}</div><div><br /></div><div><a href=\"{{unsubscribeUrl}}\">unsubscribe</a></div>');
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
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_entity`
--

LOCK TABLES `core_entity` WRITE;
/*!40000 ALTER TABLE `core_entity` DISABLE KEYS */;
INSERT INTO `core_entity` VALUES (1,1,'Method','Method',0,NULL),(2,1,'Blob','Blob',0,NULL),(3,1,'Acl','Acl',0,NULL),(4,1,'Alert','Alert',0,NULL),(5,1,'AuthAllowGroup','AuthAllowGroup',0,NULL),(6,1,'CronJobSchedule','CronJobSchedule',0,NULL),(7,1,'EmailTemplate','EmailTemplate',0,32),(8,1,'EntityFilter','EntityFilter',0,6),(9,1,'Field','Field',0,NULL),(10,1,'FieldSet','FieldSet',0,7),(11,1,'Group','Group',1,8),(12,1,'Link','Link',0,NULL),(13,1,'Module','Module',0,33),(14,1,'OauthAccessToken','OauthAccessToken',0,NULL),(15,1,'OauthAuthCode','OauthAuthCode',0,NULL),(16,1,'OauthClient','OauthClient',0,NULL),(17,1,'Search','Search',0,NULL),(18,1,'SmtpAccount','SmtpAccount',0,34),(19,1,'SpreadSheetExport','SpreadSheetExport',0,NULL),(20,1,'Token','Token',0,NULL),(21,1,'User','User',3,NULL),(22,1,'Template','Template',0,35),(23,2,'AddressBook','AddressBook',0,30),(24,2,'Contact','Contact',0,NULL),(25,2,'Group','AddressBookGroup',0,NULL),(26,3,'Activity','Activity',0,NULL),(27,3,'Business','Business',0,NULL),(28,3,'EmployeeAgreement','EmployeeAgreement',0,NULL),(29,4,'Bookmark','Bookmark',0,NULL),(30,4,'Category','BookmarksCategory',0,36),(31,5,'Comment','Comment',0,NULL),(32,5,'Label','CommentLabel',0,NULL),(33,7,'LogEntry','LogEntry',5,37),(34,8,'Note','Note',0,NULL),(35,8,'NoteBook','NoteBook',0,38),(36,9,'Calendar','Calendar',0,20),(37,9,'Event','Event',0,NULL),(38,13,'File','File',0,NULL),(39,13,'Folder','Folder',0,NULL),(40,17,'Task','Task',0,NULL),(41,20,'Order','Order',0,NULL),(42,20,'Product','Product',0,NULL),(43,20,'Book','Book',0,71),(44,20,'OrderStatus','OrderStatus',0,72),(45,26,'LinkedEmail','LinkedEmail',0,NULL),(46,27,'Ticket','Ticket',0,NULL),(47,27,'Type','TicketType',0,73),(48,28,'AddressList','AddressList',0,74),(49,28,'Newsletter','Newsletter',0,NULL),(50,30,'Service','WopiService',0,75),(51,17,'Tasklist','Tasklist',0,117),(52,9,'View','View',0,115),(53,31,'Content','Content',0,NULL),(54,31,'Site','Site',0,105),(55,15,'Announcement','Announcement',0,116),(57,33,'Project','Project',0,NULL),(58,33,'TimeEntry','TimeEntry',0,NULL),(59,33,'Type','ProjectType',0,118),(60,33,'Status','ProjectStatus',0,119),(61,33,'Template','ProjectTemplate',0,120),(62,35,'Domain','Domain',0,128),(63,12,'Account','Account',0,132),(64,12,'Alias','Alias',0,NULL),(65,13,'Version','Version',0,NULL),(66,37,'Key','Key',0,NULL),(67,40,'Instance','Instance',0,NULL),(68,42,'Studio','Studio',0,NULL),(69,43,'Document','Document',0,NULL),(72,48,'A','A',0,NULL),(73,48,'B','B',0,NULL),(74,48,'C','C',0,NULL),(79,55,'Test2','Test2',0,NULL),(80,56,'Test3','Test3',0,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_entity_filter`
--

LOCK TABLES `core_entity_filter` WRITE;
/*!40000 ALTER TABLE `core_entity_filter` DISABLE KEYS */;
INSERT INTO `core_entity_filter` VALUES (1,69,'date_in',1,NULL,164,'variable');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_group`
--

LOCK TABLES `core_group` WRITE;
/*!40000 ALTER TABLE `core_group` DISABLE KEYS */;
INSERT INTO `core_group` VALUES (1,'Admins',1,1,NULL),(2,'Everyone',1,2,NULL),(3,'Internal',1,3,NULL),(4,'admin',1,4,1),(5,'elmer',1,77,2),(6,'demo',1,82,3),(7,'linda',1,87,4),(11,'Management',1,180,NULL),(14,'Finance',1,190,NULL),(15,'peter',1,191,9);
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
-- Table structure for table `core_group_default_template`
--

DROP TABLE IF EXISTS `core_group_default_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_group_default_template` (
  `groupId` int(11) NOT NULL,
  `templateId` int(11) NOT NULL,
  PRIMARY KEY (`groupId`,`templateId`),
  KEY `groupId` (`groupId`),
  KEY `templateId` (`templateId`),
  CONSTRAINT `core_group_default_template_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `core_group_default_template_ibfk_2` FOREIGN KEY (`templateId`) REFERENCES `go_templates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_group_default_template`
--

LOCK TABLES `core_group_default_template` WRITE;
/*!40000 ALTER TABLE `core_group_default_template` DISABLE KEYS */;
INSERT INTO `core_group_default_template` VALUES (3,1),(11,3);
/*!40000 ALTER TABLE `core_group_default_template` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_link`
--

LOCK TABLES `core_link` WRITE;
/*!40000 ALTER TABLE `core_link` DISABLE KEYS */;
INSERT INTO `core_link` VALUES (1,24,2,24,1,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(2,24,1,24,2,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(3,24,4,24,3,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(4,24,3,24,4,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(5,37,1,24,4,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(6,24,4,37,1,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(7,37,1,24,2,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(8,24,2,37,1,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(9,37,3,24,4,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(10,24,4,37,3,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(11,37,3,24,2,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(12,24,2,37,3,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(13,37,5,24,4,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(14,24,4,37,5,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(15,37,5,24,2,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(16,24,2,37,5,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(17,37,7,24,4,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(18,24,4,37,7,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(19,37,7,24,2,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(20,24,2,37,7,NULL,'2021-06-28 08:56:41',NULL,NULL,NULL),(21,37,8,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(22,24,4,37,8,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(23,37,8,24,2,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(24,24,2,37,8,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(25,37,9,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(26,24,4,37,9,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(27,37,9,24,2,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(28,24,2,37,9,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(29,37,10,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(30,24,4,37,10,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(31,37,10,24,2,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(32,24,2,37,10,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(33,37,11,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(34,24,4,37,11,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(35,37,11,24,2,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(36,24,2,37,11,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(37,37,12,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(38,24,4,37,12,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(39,37,12,24,2,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(40,24,2,37,12,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(41,40,4,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(42,24,4,40,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(43,40,4,37,12,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(44,37,12,40,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(45,40,5,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(46,24,4,40,5,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(47,40,5,37,12,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(48,37,12,40,5,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(49,40,6,24,4,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(50,24,4,40,6,NULL,'2021-06-28 08:56:42',NULL,NULL,NULL),(51,40,6,37,12,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(52,37,12,40,6,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(53,41,1,24,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(54,24,2,41,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(55,41,1,24,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(56,24,1,41,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(57,41,1,40,7,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(58,40,7,41,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(59,40,7,24,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(60,24,2,40,7,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(61,40,7,24,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(62,24,1,40,7,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(63,41,2,24,4,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(64,24,4,41,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(65,41,2,24,3,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(66,24,3,41,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(67,41,2,40,8,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(68,40,8,41,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(69,40,8,24,4,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(70,24,4,40,8,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(71,40,8,24,3,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(72,24,3,40,8,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(73,41,3,24,2,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(74,24,2,41,3,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(75,41,3,24,1,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(76,24,1,41,3,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(77,41,4,24,4,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(78,24,4,41,4,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(79,41,4,24,3,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(80,24,3,41,4,NULL,'2021-06-28 08:56:43',NULL,NULL,NULL),(81,41,5,24,2,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(82,24,2,41,5,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(83,41,5,24,1,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(84,24,1,41,5,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(85,41,6,24,4,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(86,24,4,41,6,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(87,41,6,24,3,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(88,24,3,41,6,NULL,'2021-06-28 08:56:44',NULL,NULL,NULL),(89,37,13,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(90,24,4,37,13,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(91,37,13,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(92,24,2,37,13,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(93,37,15,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(94,24,4,37,15,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(95,37,15,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(96,24,2,37,15,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(97,37,17,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(98,24,4,37,17,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(99,37,17,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(100,24,2,37,17,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(101,37,19,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(102,24,4,37,19,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(103,37,19,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(104,24,2,37,19,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(105,37,20,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(106,24,4,37,20,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(107,37,20,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(108,24,2,37,20,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(109,37,21,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(110,24,4,37,21,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(111,37,21,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(112,24,2,37,21,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(113,37,22,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(114,24,4,37,22,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(115,37,22,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(116,24,2,37,22,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(117,37,23,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(118,24,4,37,23,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(119,37,23,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(120,24,2,37,23,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(121,37,24,24,4,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(122,24,4,37,24,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(123,37,24,24,2,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(124,24,2,37,24,NULL,'2021-06-28 09:04:49',NULL,NULL,NULL),(125,40,12,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(126,24,4,40,12,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(127,40,12,37,24,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(128,37,24,40,12,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(129,40,13,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(130,24,4,40,13,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(131,40,13,37,24,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(132,37,24,40,13,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(133,40,14,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(134,24,4,40,14,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(135,40,14,37,24,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(136,37,24,40,14,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(137,41,7,24,2,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(138,24,2,41,7,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(139,41,7,24,1,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(140,24,1,41,7,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(141,41,7,40,15,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(142,40,15,41,7,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(143,40,15,24,2,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(144,24,2,40,15,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(145,40,15,24,1,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(146,24,1,40,15,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(147,41,8,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(148,24,4,41,8,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(149,41,8,24,3,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(150,24,3,41,8,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(151,41,8,40,16,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(152,40,16,41,8,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(153,40,16,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(154,24,4,40,16,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(155,40,16,24,3,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(156,24,3,40,16,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(157,41,9,24,2,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(158,24,2,41,9,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(159,41,9,24,1,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(160,24,1,41,9,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(161,41,10,24,4,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(162,24,4,41,10,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(163,41,10,24,3,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(164,24,3,41,10,NULL,'2021-06-28 09:04:50',NULL,NULL,NULL),(165,41,11,24,2,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(166,24,2,41,11,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(167,41,11,24,1,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(168,24,1,41,11,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(169,41,12,24,4,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(170,24,4,41,12,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(171,41,12,24,3,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(172,24,3,41,12,NULL,'2021-06-28 09:04:51',NULL,NULL,NULL),(173,57,2,24,3,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(174,24,3,57,2,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(175,57,2,24,4,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(176,24,4,57,2,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(177,57,3,24,3,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(178,24,3,57,3,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(179,57,3,24,4,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(180,24,4,57,3,NULL,'2021-06-28 09:04:55',NULL,NULL,NULL),(181,45,1,24,4,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(182,24,4,45,1,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(183,45,2,24,2,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(184,24,2,45,2,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(185,45,3,24,4,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(186,24,4,45,3,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(187,45,4,24,2,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(188,24,2,45,4,NULL,'2021-06-28 09:04:56',NULL,NULL,NULL),(189,24,17,24,1,NULL,'2021-07-16 12:48:30',NULL,NULL,NULL),(190,24,1,24,17,NULL,'2021-07-16 12:48:30',NULL,NULL,NULL),(191,24,18,24,3,NULL,'2021-07-16 12:48:30',NULL,NULL,NULL),(192,24,3,24,18,NULL,'2021-07-16 12:48:30',NULL,NULL,NULL);
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
  UNIQUE KEY `name` (`name`,`package`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `acl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_module`
--

LOCK TABLES `core_module` WRITE;
/*!40000 ALTER TABLE `core_module` DISABLE KEYS */;
INSERT INTO `core_module` VALUES (1,'core','core',241,0,0,5,1,NULL,NULL,NULL),(2,'addressbook','community',67,101,0,10,1,NULL,NULL,NULL),(3,'business','business',11,102,0,12,1,NULL,NULL,NULL),(4,'bookmarks','community',8,103,0,13,1,NULL,NULL,NULL),(5,'comments','community',27,104,0,14,1,NULL,NULL,NULL),(6,'googleauthenticator','community',3,105,0,15,1,NULL,NULL,NULL),(7,'history','community',3,106,0,16,1,NULL,NULL,NULL),(8,'notes','community',57,107,0,17,1,NULL,NULL,NULL),(9,'calendar',NULL,184,108,0,19,1,'2021-06-28 08:55:27',NULL,NULL),(10,'cron',NULL,0,108,1,21,1,'2021-06-28 08:55:27',NULL,NULL),(12,'email',NULL,104,108,0,23,1,'2021-06-28 08:55:27',NULL,NULL),(13,'files',NULL,137,108,0,24,1,'2021-06-28 08:55:27',NULL,NULL),(14,'sieve',NULL,0,108,0,25,1,'2021-06-28 08:55:27',NULL,NULL),(15,'summary',NULL,31,108,0,26,1,'2021-06-28 08:55:27',NULL,NULL),(16,'sync',NULL,50,108,0,27,1,'2021-06-28 08:55:27',NULL,NULL),(17,'tasks',NULL,60,108,0,28,1,'2021-06-28 08:55:27',NULL,NULL),(18,'tools',NULL,0,108,1,29,1,'2021-06-28 08:55:28',NULL,NULL),(19,'assistant',NULL,0,108,0,40,1,'2021-06-28 08:55:27',NULL,NULL),(20,'billing',NULL,319,108,0,41,1,'2021-06-28 08:55:27',NULL,NULL),(21,'documenttemplates',NULL,0,108,0,57,1,'2021-06-28 08:55:27',NULL,NULL),(23,'timeregistration2',NULL,1,108,0,60,1,'2021-06-28 08:59:40',NULL,NULL),(25,'leavedays',NULL,31,108,0,62,1,'2021-06-28 08:55:27',NULL,NULL),(26,'savemailas',NULL,12,108,0,63,1,'2021-06-28 08:55:27',NULL,NULL),(27,'tickets',NULL,165,108,0,64,1,'2021-06-28 08:55:27',NULL,NULL),(28,'newsletters','business',2,108,0,67,0,NULL,NULL,NULL),(29,'onlyoffice','business',1,109,0,69,1,NULL,NULL,NULL),(30,'wopi','business',7,110,0,70,1,NULL,NULL,NULL),(31,'site',NULL,18,111,0,98,1,'2021-06-28 08:56:44',NULL,NULL),(32,'defaultsite',NULL,0,111,0,99,1,'2021-06-28 08:56:44',NULL,NULL),(33,'projects2',NULL,401,111,0,106,1,'2021-06-28 09:00:26',NULL,NULL),(35,'postfixadmin',NULL,45,111,0,126,1,'2021-06-28 09:07:05',NULL,NULL),(36,'hoursapproval2',NULL,0,111,0,144,1,'2021-07-12 08:25:05',NULL,NULL),(37,'apikeys','community',2,111,0,145,1,NULL,NULL,NULL),(38,'dav',NULL,1,112,0,146,1,'2021-07-12 08:25:26',NULL,NULL),(39,'caldav',NULL,32,112,0,147,1,'2021-07-22 12:17:42',NULL,NULL),(40,'multi_instance','community',9,112,0,153,1,NULL,NULL,NULL),(41,'grouptemplates','invicta',0,113,0,154,1,NULL,NULL,NULL),(42,'studio','business',0,114,0,161,1,NULL,NULL,NULL),(43,'documents','studio',0,115,0,162,1,NULL,NULL,NULL),(44,'notesencrypt','community',0,116,0,165,1,NULL,NULL,NULL),(48,'test','community',0,119,0,171,1,NULL,NULL,NULL),(53,'carddav','community',0,120,0,177,1,NULL,NULL,NULL),(54,'zpushadmin',NULL,7,121,0,178,1,'2021-07-22 11:40:55',NULL,NULL),(55,'test2','studio',0,121,0,188,1,NULL,NULL,NULL),(56,'test3','studio',0,122,0,189,1,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=252 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_search`
--

LOCK TABLES `core_search` WRITE;
/*!40000 ALTER TABLE `core_search` DISABLE KEYS */;
INSERT INTO `core_search` VALUES (1,1,13,'calendar','calendar',39,NULL,'2021-08-31 07:43:44',24),(2,2,13,'System Administrator','calendar/System Administrator',39,NULL,'2021-06-28 08:54:02',39),(3,3,13,'billing','billing',39,NULL,'2021-06-28 09:04:25',24),(4,4,13,'Quotes','billing/Quotes',39,NULL,'2021-06-28 08:54:54',42),(5,5,13,'Orders','billing/Orders',39,NULL,'2021-06-28 08:54:55',47),(6,6,13,'Invoices','billing/Invoices',39,NULL,'2021-06-28 08:54:55',52),(7,7,13,'product_images','billing/product_images',39,NULL,'2021-06-28 08:54:55',41),(8,8,13,'tickets','tickets',39,NULL,'2021-06-28 08:55:28',24),(9,11,13,'Elmer Fudd','calendar/Elmer Fudd',39,NULL,'2021-06-28 08:56:40',78),(10,12,13,'tasks','tasks',39,NULL,'2021-08-31 07:43:44',24),(11,13,13,'Elmer Fudd','tasks/Elmer Fudd',39,NULL,'2021-06-28 08:56:40',79),(12,14,13,'Demo User','calendar/Demo User',39,NULL,'2021-06-28 08:56:40',83),(13,15,13,'Demo User','tasks/Demo User',39,NULL,'2021-06-28 08:56:41',84),(14,16,13,'Linda Smith','calendar/Linda Smith',39,NULL,'2021-06-28 08:56:41',88),(15,17,13,'Linda Smith','tasks/Linda Smith',39,NULL,'2021-06-28 08:56:41',89),(16,1,2,'Smith Inc.','Customers',24,'isOrganization','2021-06-28 08:56:43',76),(17,2,2,'John Smith','Customers - CEO - Smith Inc.',24,'isContact','2021-06-28 08:56:41',76),(18,3,2,'ACME Corporation','Customers',24,'isOrganization','2021-06-28 08:56:43',76),(19,4,2,'Wile E. Coyote','Customers - CEO - ACME Corporation',24,'isContact','2021-06-28 08:56:41',76),(20,1,9,'Project meeting','Demo User',37,NULL,'2021-06-29 08:00:00',83),(21,2,9,'Project meeting','Linda Smith',37,NULL,'2021-06-29 08:00:00',88),(22,3,9,'Meet Wile','Demo User',37,NULL,'2021-06-29 10:00:00',83),(23,4,9,'Meet Wile','Linda Smith',37,NULL,'2021-06-29 10:00:00',88),(24,5,9,'MT Meeting','Demo User',37,NULL,'2021-06-29 12:00:00',83),(25,6,9,'MT Meeting','Linda Smith',37,NULL,'2021-06-29 12:00:00',88),(26,7,9,'Project meeting','Linda Smith',37,NULL,'2021-06-28 08:56:41',88),(27,8,9,'Meet John','Linda Smith',37,NULL,'2021-06-28 08:56:42',88),(28,9,9,'MT Meeting','Linda Smith',37,NULL,'2021-06-28 08:56:42',88),(29,10,9,'Rocket testing','Linda Smith',37,NULL,'2021-06-28 08:56:42',88),(30,11,9,'Blast impact test','Linda Smith',37,NULL,'2021-06-28 08:56:42',88),(31,12,9,'Test range extender','Linda Smith',37,NULL,'2021-06-28 08:56:42',88),(32,18,13,'Road Runner Room','calendar/Road Runner Room',39,NULL,'2021-06-28 08:56:42',94),(33,19,13,'Don Coyote Room','calendar/Don Coyote Room',39,NULL,'2021-06-28 08:56:42',95),(34,20,13,'System Administrator','tasks/System Administrator',39,NULL,'2021-06-28 08:56:42',96),(35,1,17,'Feed the dog','Demo User',40,NULL,'2021-06-30 08:56:42',84),(36,2,17,'Feed the dog','Linda Smith',40,NULL,'2021-06-29 08:56:42',89),(37,3,17,'Feed the dog','Elmer Fudd',40,NULL,'2021-06-29 08:56:42',79),(38,4,17,'Prepare meeting','Demo User',40,NULL,'2021-06-29 08:56:42',84),(39,5,17,'Prepare meeting','Linda Smith',40,NULL,'2021-06-29 08:56:42',89),(40,6,17,'Prepare meeting','Elmer Fudd',40,NULL,'2021-06-29 08:56:42',79),(41,1,20,'Q21000001','Smith Inc.',41,NULL,'2021-06-28 08:56:43',42),(42,7,17,'Call: Smith Inc. (Q21000001)','System Administrator',40,NULL,'2021-07-01 08:56:43',96),(43,2,20,'Q21000002','ACME Corporation',41,NULL,'2021-06-28 08:56:43',42),(44,8,17,'Call: ACME Corporation (Q21000002)','System Administrator',40,NULL,'2021-07-01 08:56:43',96),(45,3,20,'O21000001','Smith Inc.',41,NULL,'2021-06-28 08:56:43',47),(46,4,20,'O21000002','ACME Corporation',41,NULL,'2021-06-28 08:56:44',47),(47,5,20,'I21000001','Smith Inc.',41,NULL,'2021-06-28 08:56:44',52),(48,6,20,'I21000002','ACME Corporation',41,NULL,'2021-06-28 08:56:44',52),(49,1,27,'Malfunctioning rockets','Wile E. Coyote (ACME Corporation)',46,NULL,'2021-06-28 08:56:44',65),(50,2,27,'Can I speed up my rockets?','Wile E. Coyote (ACME Corporation)',46,NULL,'2021-06-28 08:56:44',65),(51,21,13,'users','users',39,NULL,'2021-07-08 13:06:30',24),(52,22,13,'demo','users/demo',39,NULL,'2021-06-28 09:04:52',102),(53,1,13,'noperson.jpg','users/demo/noperson.jpg',38,NULL,'2021-06-28 09:04:52',102),(54,2,13,'Demo letter.docx','users/demo/Demo letter.docx',38,NULL,'2021-06-28 09:04:52',102),(55,3,13,'wecoyote.png','users/demo/wecoyote.png',38,NULL,'2021-06-28 09:04:52',102),(56,4,13,'empty.docx','users/demo/empty.docx',38,NULL,'2021-06-28 09:04:52',102),(57,5,13,'empty.odt','users/demo/empty.odt',38,NULL,'2021-06-28 09:04:52',102),(58,23,13,'project_templates','project_templates',39,NULL,'2021-06-28 09:00:26',24),(59,24,13,'Projects folder','project_templates/Projects folder',39,NULL,'2021-06-28 09:00:26',112),(60,25,13,'Standard project','project_templates/Standard project',39,NULL,'2021-06-28 09:00:26',113),(61,26,13,'stationery-papers','billing/stationery-papers',39,NULL,'2021-06-28 09:04:25',24),(62,27,13,'projects2','projects2',39,NULL,'2021-06-28 09:00:25',24),(63,28,13,'template-icons','projects2/template-icons',39,NULL,'2021-06-28 09:00:25',106),(64,13,9,'Project meeting','Demo User',37,NULL,'2021-06-29 08:00:00',83),(65,14,9,'Project meeting','Linda Smith',37,NULL,'2021-06-29 08:00:00',88),(66,15,9,'Meet Wile','Demo User',37,NULL,'2021-06-29 10:00:00',83),(67,16,9,'Meet Wile','Linda Smith',37,NULL,'2021-06-29 10:00:00',88),(68,17,9,'MT Meeting','Demo User',37,NULL,'2021-06-29 12:00:00',83),(69,18,9,'MT Meeting','Linda Smith',37,NULL,'2021-06-29 12:00:00',88),(70,19,9,'Project meeting','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(71,20,9,'Meet John','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(72,21,9,'MT Meeting','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(73,22,9,'Rocket testing','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(74,23,9,'Blast impact test','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(75,24,9,'Test range extender','Linda Smith',37,NULL,'2021-06-28 09:04:49',88),(76,9,17,'Feed the dog','Demo User',40,NULL,'2021-06-30 09:04:49',84),(77,10,17,'Feed the dog','Linda Smith',40,NULL,'2021-06-29 09:04:49',89),(78,11,17,'Feed the dog','Elmer Fudd',40,NULL,'2021-06-29 09:04:50',79),(79,12,17,'Prepare meeting','Demo User',40,NULL,'2021-06-29 09:04:50',84),(80,13,17,'Prepare meeting','Linda Smith',40,NULL,'2021-06-29 09:04:50',89),(81,14,17,'Prepare meeting','Elmer Fudd',40,NULL,'2021-06-29 09:04:50',79),(82,7,20,'Q21000003','Smith Inc.',41,NULL,'2021-06-28 09:04:50',42),(83,15,17,'Call: Smith Inc. (Q21000003)','System Administrator',40,NULL,'2021-07-01 09:04:50',96),(84,8,20,'Q21000004','ACME Corporation',41,NULL,'2021-06-28 09:04:50',42),(85,16,17,'Call: ACME Corporation (Q21000004)','System Administrator',40,NULL,'2021-07-01 09:04:50',96),(86,9,20,'O21000003','Smith Inc.',41,NULL,'2021-06-28 09:04:50',47),(87,10,20,'O21000004','ACME Corporation',41,NULL,'2021-06-28 09:04:51',47),(88,11,20,'I21000003','Smith Inc.',41,NULL,'2021-06-28 09:04:51',52),(89,12,20,'I21000004','ACME Corporation',41,NULL,'2021-06-28 09:04:51',52),(90,3,27,'Malfunctioning rockets','Wile E. Coyote (ACME Corporation)',46,NULL,'2021-06-28 09:04:52',121),(91,4,27,'Can I speed up my rockets?','Wile E. Coyote (ACME Corporation)',46,NULL,'2021-06-28 09:04:52',121),(92,1,33,'Demo','| Demo | Demo',57,NULL,'2021-06-28 09:04:54',123),(93,2,33,'[001] Develop Rocket 2000','ACME Corporation | Demo | Demo/[001] Develop Rocket 2000',57,NULL,'2021-07-12 12:40:13',123),(94,3,33,'[001] Develop Rocket Launcher','ACME Corporation | Demo | Demo/[001] Develop Rocket Launcher',57,NULL,'2021-07-19 12:39:07',123),(95,6,13,'folder.png','projects2/template-icons/folder.png',38,NULL,'2021-06-28 09:00:25',106),(96,7,13,'project.png','projects2/template-icons/project.png',38,NULL,'2021-06-28 09:00:25',106),(97,1,26,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',45,NULL,'2013-05-17 07:53:08',76),(98,2,26,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',45,NULL,'2013-05-17 07:53:08',76),(99,3,26,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',45,NULL,'2013-05-17 08:06:26',76),(100,4,26,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',45,NULL,'2013-05-17 08:06:26',76),(101,29,13,'admin','users/admin',39,NULL,'2021-06-28 14:07:54',131),(102,30,13,'log','log',39,NULL,'2021-06-28 10:11:18',24),(103,8,13,'Re change HS code RB\'s - 18-06-2021 0858.eml','users/admin/Re change HS code RB\'s - 18-06-2021 0858.eml',38,NULL,'2021-06-28 10:11:27',131),(104,5,2,'info','Shared',24,'isContact','2021-07-23 07:24:19',11),(105,9,13,'Test.odt','users/admin/Test.odt',38,NULL,'2021-06-28 14:07:54',131),(106,6,2,'Admin','Shared',24,'isContact','2021-07-20 11:33:24',11),(107,7,2,'Jantje Beton','Shared',24,'isContact','2021-07-20 11:33:24',11),(108,31,13,'elmer','users/elmer',39,NULL,'2021-07-08 13:06:31',140),(109,32,13,'tmp','tmp',39,NULL,'2021-07-08 13:07:17',24),(110,33,13,'1','tmp/1',39,NULL,'2021-07-08 13:07:17',141),(197,8,2,'Piet Jansen','Customers',24,'isContact','2021-07-12 12:28:09',76),(198,95,13,'I2021-004713.pdf','users/admin/I2021-004713.pdf',38,NULL,'2021-07-12 14:08:05',131),(199,9,2,'gjhghj','Shared',24,'isContact','2021-07-20 11:33:25',11),(202,1,43,'1','Wile E. Coyote - 16-07-2021',69,NULL,'2021-07-16 13:15:02',162),(206,12,2,'ABC & Co','Shared',24,'isContact','2021-07-20 11:33:25',11),(207,5,43,'5','ABC & Co - 12-07-2021',69,NULL,'2021-07-16 13:14:55',162),(209,6,43,'6','',69,NULL,'2021-08-24 12:29:31',162),(210,14,2,'Cont YOYO & Co','Shared',24,'isContact','2021-08-24 12:17:41',11),(211,7,43,'7','',69,NULL,'2021-08-24 12:29:28',162),(212,15,2,'Smith Inc.','Elmer Fudd',24,'isOrganization','2021-07-19 13:07:18',80),(213,16,2,'ACME Corporation','Elmer Fudd',24,'isOrganization','2021-07-16 12:48:29',80),(214,17,2,'John Smith','Elmer Fudd - CEO - Smith Inc.',24,'isContact','2021-07-16 12:48:30',80),(215,18,2,'Wile E. Coyote','Elmer Fudd - CEO - ACME Corporation',24,'isContact','2021-07-16 12:48:30',80),(216,19,2,'info','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(217,20,2,'Admin','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(218,21,2,'Jantje Beton','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(219,22,2,'Piet Jansen','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(220,23,2,'gjhghj','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(221,24,2,'ABC & Co','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(222,25,2,'YOYO & Co','Elmer Fudd',24,'isContact','2021-07-16 12:48:30',80),(223,173,8,'Test','{ENCRYPTED}5b33a2dc4cc8e0743a320861tvBzK8ufC4OOvEAI9S8VBAk+BzYfDnKeNA==',34,NULL,'2021-07-16 13:46:40',18),(224,174,8,'ewre','',34,NULL,'2021-07-16 13:52:44',18),(225,26,2,'Test','Shared',24,'isOrganization','2021-07-20 11:33:26',11),(239,27,2,'Peter Clemens','Shared',24,'isContact','2021-07-20 11:33:27',11),(240,25,9,'Recurring 3 times','System Administrator',37,NULL,'2021-07-19 12:15:00',39),(241,26,9,'Recurring until','System Administrator',37,NULL,'2021-07-19 16:45:00',39),(242,27,9,'No recurence','System Administrator',37,NULL,'2021-07-19 08:30:00',39),(243,28,9,'repeat forever','System Administrator',37,NULL,'2021-07-19 10:30:00',39),(244,28,2,'From Demo 1','Demo User',24,'isContact','2021-07-20 11:47:41',85),(245,29,2,'Demo 2','Demo User',24,'isContact','2021-07-20 11:45:33',85),(246,30,2,'Demo 3','Demo User',24,'isContact','2021-07-20 11:46:10',85),(249,38,13,'Peter Smith','tasks/Peter Smith',39,NULL,'2021-08-31 07:43:44',192),(250,39,13,'Peter Smith','calendar/Peter Smith',39,NULL,'2021-08-31 07:43:44',193),(251,109,13,'powiadomienie.html','tmp/1/powiadomienie.html',38,NULL,'2021-08-31 07:49:07',141);
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
INSERT INTO `core_search_word` VALUES (93,'[001]'),(94,'[001]'),(49,'+31'),(50,'+31'),(90,'+31'),(91,'+31'),(16,'+310101234567'),(17,'+310101234567'),(18,'+310101234567'),(19,'+310101234567'),(212,'+310101234567'),(213,'+310101234567'),(214,'+310101234567'),(215,'+310101234567'),(16,'+31061234567'),(17,'+31061234567'),(18,'+31061234567'),(19,'+31061234567'),(212,'+31061234567'),(213,'+31061234567'),(214,'+31061234567'),(215,'+31061234567'),(41,'000001'),(45,'000001'),(47,'000001'),(43,'000002'),(46,'000002'),(48,'000002'),(82,'000003'),(86,'000003'),(88,'000003'),(84,'000004'),(87,'000004'),(89,'000004'),(41,'00001'),(45,'00001'),(47,'00001'),(49,'00001'),(43,'00002'),(46,'00002'),(48,'00002'),(50,'00002'),(82,'00003'),(86,'00003'),(88,'00003'),(90,'00003'),(84,'00004'),(87,'00004'),(89,'00004'),(91,'00004'),(41,'0001'),(45,'0001'),(47,'0001'),(49,'0001'),(43,'0002'),(46,'0002'),(48,'0002'),(50,'0002'),(82,'0003'),(86,'0003'),(88,'0003'),(90,'0003'),(84,'0004'),(87,'0004'),(89,'0004'),(91,'0004'),(41,'001'),(45,'001'),(47,'001'),(49,'001'),(93,'001'),(94,'001'),(93,'001]'),(94,'001]'),(43,'002'),(46,'002'),(48,'002'),(50,'002'),(82,'003'),(86,'003'),(88,'003'),(90,'003'),(84,'004'),(87,'004'),(89,'004'),(91,'004'),(93,'01]'),(94,'01]'),(39,'01c275eb-c1aa-5d4c-abb3-20500f83262f'),(49,'02100001'),(50,'02100002'),(90,'02100003'),(91,'02100004'),(202,'08-07-2021'),(103,'0858eml'),(103,'0858emlusers/admin/re'),(37,'08ceb7a3-96e3-5fa5-98a5-7df6644bbae3'),(26,'0ccbec67-03f0-527d-94e7-b63fb03bd62c'),(1,'1'),(16,'1'),(20,'1'),(53,'1'),(92,'1'),(97,'1'),(202,'1'),(29,'10'),(77,'10'),(87,'10'),(41,'1000001'),(45,'1000001'),(47,'1000001'),(43,'1000002'),(46,'1000002'),(48,'1000002'),(82,'1000003'),(86,'1000003'),(88,'1000003'),(84,'1000004'),(87,'1000004'),(89,'1000004'),(49,'100001'),(50,'100002'),(90,'100003'),(91,'100004'),(16,'1012'),(17,'1012'),(18,'1012'),(19,'1012'),(41,'1012'),(43,'1012'),(45,'1012'),(46,'1012'),(47,'1012'),(48,'1012'),(82,'1012'),(84,'1012'),(86,'1012'),(87,'1012'),(88,'1012'),(89,'1012'),(212,'1012'),(213,'1012'),(214,'1012'),(215,'1012'),(251,'109'),(9,'11'),(30,'11'),(78,'11'),(88,'11'),(10,'12'),(31,'12'),(79,'12'),(89,'12'),(206,'12'),(207,'12-07-2021'),(209,'12-07-2021'),(49,'1234567'),(50,'1234567'),(90,'1234567'),(91,'1234567'),(41,'123456789b01'),(43,'123456789b01'),(45,'123456789b01'),(46,'123456789b01'),(47,'123456789b01'),(48,'123456789b01'),(82,'123456789b01'),(84,'123456789b01'),(86,'123456789b01'),(87,'123456789b01'),(88,'123456789b01'),(89,'123456789b01'),(11,'13'),(64,'13'),(80,'13'),(206,'13:27'),(97,'13687771885195e1e479413@localhost'),(98,'13687771885195e1e479413@localhost'),(99,'13687779865195e5020b17e@localhost'),(100,'13687779865195e5020b17e@localhost'),(12,'14'),(65,'14'),(81,'14'),(210,'14'),(211,'14-07-1980'),(13,'15'),(66,'15'),(83,'15'),(212,'15'),(83,'152d23db-35ab-5436-b37e-5a2aac509494'),(14,'16'),(67,'16'),(85,'16'),(213,'16'),(202,'16-07-2021'),(15,'17'),(68,'17'),(214,'17'),(223,'173'),(224,'174'),(32,'18'),(69,'18'),(215,'18'),(103,'18-06-2021'),(33,'19'),(70,'19'),(216,'19'),(206,'19-07-2021'),(35,'1d2da469-7816-553c-bbf6-7864babab97c'),(2,'2'),(17,'2'),(21,'2'),(36,'2'),(54,'2'),(93,'2'),(98,'2'),(34,'20'),(71,'20'),(217,'20'),(93,'2000'),(97,'2000'),(98,'2000'),(93,'2000acme'),(49,'202100001'),(50,'202100002'),(90,'202100003'),(91,'202100004'),(51,'21'),(72,'21'),(218,'21'),(41,'21000001'),(45,'21000001'),(47,'21000001'),(43,'21000002'),(46,'21000002'),(48,'21000002'),(82,'21000003'),(86,'21000003'),(88,'21000003'),(84,'21000004'),(87,'21000004'),(89,'21000004'),(49,'2100001'),(50,'2100002'),(90,'2100003'),(91,'2100004'),(52,'22'),(73,'22'),(219,'22'),(58,'23'),(74,'23'),(220,'23'),(59,'24'),(75,'24'),(221,'24'),(60,'25'),(222,'25'),(240,'25'),(61,'26'),(225,'26'),(241,'26'),(62,'27'),(239,'27'),(242,'27'),(63,'28'),(243,'28'),(244,'28'),(101,'29'),(245,'29'),(74,'2908495d-c762-5638-89f7-fcf153f39fa5'),(3,'3'),(18,'3'),(22,'3'),(37,'3'),(45,'3'),(55,'3'),(94,'3'),(99,'3'),(102,'30'),(246,'30'),(108,'31'),(109,'32'),(110,'33'),(249,'38'),(250,'39'),(64,'39b398bf-4e89-5b16-8e73-9a55b08bdbf0'),(65,'39b398bf-4e89-5b16-8e73-9a55b08bdbf0'),(78,'3f96e54b-6b3c-5b68-9dfc-33785217cf88'),(4,'4'),(19,'4'),(23,'4'),(38,'4'),(46,'4'),(56,'4'),(100,'4'),(241,'48375d28-3b7b-5317-8944-34fff7b5abd0'),(240,'4af4950a-0af4-5024-ae28-5fd88f47a707'),(5,'5'),(24,'5'),(39,'5'),(47,'5'),(57,'5'),(104,'5'),(207,'5'),(28,'5aec17d5-0553-5674-9bd4-9ecfc7494992'),(79,'5c8b89bb-3367-537a-b569-ba58e43314af'),(6,'6'),(25,'6'),(40,'6'),(48,'6'),(95,'6'),(106,'6'),(209,'6'),(7,'7'),(26,'7'),(42,'7'),(82,'7'),(96,'7'),(107,'7'),(211,'7'),(75,'72d0c9b1-14ea-5585-b3fd-5031f6f4cfe5'),(8,'8'),(27,'8'),(44,'8'),(84,'8'),(103,'8'),(197,'8'),(27,'82992635-4cd9-576e-858d-858c44436a93'),(38,'8eee1e38-5e21-5a7c-b481-abab75bdcc33'),(28,'9'),(76,'9'),(86,'9'),(105,'9'),(199,'9'),(198,'95'),(22,'977ba999-a93c-5444-be0b-314d02bafe13'),(23,'977ba999-a93c-5444-be0b-314d02bafe13'),(206,'abc'),(207,'abc'),(209,'abc'),(221,'abc'),(70,'abed776f-733b-54ec-b0b1-3d36c9514ce5'),(30,'ac6d7c0e-cca8-5645-ba00-f4c569af4750'),(242,'acaf173a-344b-5fca-a182-037470b10d39'),(93,'accuracy'),(94,'accuracy'),(18,'acme'),(19,'acme'),(20,'acme'),(21,'acme'),(22,'acme'),(23,'acme'),(24,'acme'),(25,'acme'),(26,'acme'),(27,'acme'),(28,'acme'),(29,'acme'),(30,'acme'),(31,'acme'),(43,'acme'),(44,'acme'),(46,'acme'),(48,'acme'),(49,'acme'),(50,'acme'),(64,'acme'),(65,'acme'),(66,'acme'),(67,'acme'),(68,'acme'),(69,'acme'),(70,'acme'),(71,'acme'),(72,'acme'),(73,'acme'),(74,'acme'),(75,'acme'),(84,'acme'),(85,'acme'),(87,'acme'),(89,'acme'),(90,'acme'),(91,'acme'),(93,'acme'),(94,'acme'),(213,'acme'),(215,'acme'),(101,'admin'),(106,'admin'),(217,'admin'),(106,'admin@intermeshlocalhost'),(217,'admin@intermeshlocalhost'),(2,'administrator'),(34,'administrator'),(42,'administrator'),(44,'administrator'),(83,'administrator'),(85,'administrator'),(240,'administrator'),(241,'administrator'),(242,'administrator'),(243,'administrator'),(2,'administratorcalendar/system'),(34,'administratortasks/system'),(242,'afspraakno'),(240,'afspraakrecurring'),(241,'afspraakrecurring'),(243,'afspraakrepeat'),(16,'amsterdam'),(17,'amsterdam'),(18,'amsterdam'),(19,'amsterdam'),(41,'amsterdam'),(43,'amsterdam'),(45,'amsterdam'),(46,'amsterdam'),(47,'amsterdam'),(48,'amsterdam'),(82,'amsterdam'),(84,'amsterdam'),(86,'amsterdam'),(87,'amsterdam'),(88,'amsterdam'),(89,'amsterdam'),(212,'amsterdam'),(213,'amsterdam'),(214,'amsterdam'),(215,'amsterdam'),(93,'and'),(94,'and'),(73,'b6cf3809-d8ef-58e2-8e00-5b43ac6ec200'),(20,'b958dfa0-6144-57fe-8810-7d4dd68e01b3'),(21,'b958dfa0-6144-57fe-8810-7d4dd68e01b3'),(31,'bc836fda-554b-5023-90c3-2fc316c39e1c'),(198,'bestandi2021-004713pdfusers/admin/i2021-004713pdf'),(251,'bestandpowiadomieniehtmltmp/1/powiadomieniehtml'),(107,'beton'),(218,'beton'),(93,'better'),(94,'better'),(3,'billing'),(30,'blast'),(74,'blast'),(243,'c0a57c58-3c29-51d6-a455-f5112a56faa7'),(24,'c37e1cc5-85e2-52a0-b0cc-e249878b3146'),(25,'c37e1cc5-85e2-52a0-b0cc-e249878b3146'),(42,'c399502c-63d8-5cb9-bd9d-5d007b421ea1'),(80,'ca872636-a830-52d9-8930-4f4c2da30919'),(1,'calendar'),(42,'call:'),(44,'call:'),(83,'call:'),(85,'call:'),(50,'can'),(91,'can'),(66,'ccb3b5d1-89ff-59d7-8f6f-344f73eca67f'),(67,'ccb3b5d1-89ff-59d7-8f6f-344f73eca67f'),(17,'ceo'),(19,'ceo'),(214,'ceo'),(215,'ceo'),(103,'change'),(239,'clemens'),(103,'code'),(16,'company'),(212,'company'),(20,'confirmed'),(21,'confirmed'),(22,'confirmed'),(23,'confirmed'),(24,'confirmed'),(25,'confirmed'),(26,'confirmed'),(27,'confirmed'),(28,'confirmed'),(29,'confirmed'),(30,'confirmed'),(31,'confirmed'),(64,'confirmed'),(65,'confirmed'),(66,'confirmed'),(67,'confirmed'),(68,'confirmed'),(69,'confirmed'),(70,'confirmed'),(71,'confirmed'),(72,'confirmed'),(73,'confirmed'),(74,'confirmed'),(75,'confirmed'),(240,'confirmed'),(241,'confirmed'),(242,'confirmed'),(243,'confirmed'),(210,'cont'),(211,'cont'),(18,'corporation'),(19,'corporation'),(43,'corporation'),(44,'corporation'),(46,'corporation'),(48,'corporation'),(49,'corporation'),(50,'corporation'),(84,'corporation'),(85,'corporation'),(87,'corporation'),(89,'corporation'),(90,'corporation'),(91,'corporation'),(93,'corporation'),(94,'corporation'),(213,'corporation'),(215,'corporation'),(49,'corporation0'),(50,'corporation0'),(90,'corporation121'),(91,'corporation121'),(19,'coyote'),(33,'coyote'),(43,'coyote'),(46,'coyote'),(48,'coyote'),(49,'coyote'),(50,'coyote'),(84,'coyote'),(87,'coyote'),(89,'coyote'),(90,'coyote'),(91,'coyote'),(93,'coyote'),(94,'coyote'),(202,'coyote'),(215,'coyote'),(44,'d535e0f4-9c9a-5a10-afd3-9fbd4745e2e1'),(76,'d69217b3-c24f-56f3-8a64-9ce00023afc7'),(41,'dear'),(43,'dear'),(45,'dear'),(46,'dear'),(47,'dear'),(48,'dear'),(82,'dear'),(84,'dear'),(86,'dear'),(87,'dear'),(88,'dear'),(89,'dear'),(12,'demo'),(13,'demo'),(16,'demo'),(17,'demo'),(18,'demo'),(19,'demo'),(52,'demo'),(54,'demo'),(92,'demo'),(93,'demo'),(94,'demo'),(97,'demo'),(98,'demo'),(99,'demo'),(100,'demo'),(212,'demo'),(213,'demo'),(214,'demo'),(215,'demo'),(244,'demo'),(245,'demo'),(246,'demo'),(97,'demo@group-officecom'),(98,'demo@group-officecom'),(99,'demo@group-officecom'),(100,'demo@group-officecom'),(93,'demo/001'),(94,'demo/001'),(93,'develop'),(94,'develop'),(97,'development'),(98,'development'),(36,'df5d008c-5576-5e1e-a536-4e88afc0b587'),(54,'docx'),(56,'docx'),(35,'dog'),(36,'dog'),(37,'dog'),(76,'dog'),(77,'dog'),(78,'dog'),(35,'dogdemo'),(76,'dogdemo'),(37,'dogelmer'),(78,'dogelmer'),(36,'doglinda'),(77,'doglinda'),(33,'don'),(68,'e0e2aa9d-d16e-550f-b3df-5c439f220088'),(69,'e0e2aa9d-d16e-550f-b3df-5c439f220088'),(71,'e7eab014-6a0f-549d-b11c-e25b30422c45'),(40,'e9d08cfc-7196-5e96-bdcb-d19928cd5c86'),(29,'ea3c1220-96e9-563f-a875-c9c3127408f6'),(20,'ebf1e2'),(21,'ebf1e2'),(22,'ebf1e2'),(23,'ebf1e2'),(24,'ebf1e2'),(25,'ebf1e2'),(26,'ebf1e2'),(27,'ebf1e2'),(28,'ebf1e2'),(29,'ebf1e2'),(30,'ebf1e2'),(31,'ebf1e2'),(64,'ebf1e2'),(65,'ebf1e2'),(66,'ebf1e2'),(67,'ebf1e2'),(68,'ebf1e2'),(69,'ebf1e2'),(70,'ebf1e2'),(71,'ebf1e2'),(72,'ebf1e2'),(73,'ebf1e2'),(74,'ebf1e2'),(75,'ebf1e2'),(240,'ebf1e2'),(241,'ebf1e2'),(242,'ebf1e2'),(243,'ebf1e2'),(77,'ee289c2c-6917-50e4-bd6a-825be922c55d'),(210,'een'),(9,'elmer'),(11,'elmer'),(97,'elmer'),(98,'elmer'),(108,'elmer'),(97,'elmer@group-officecom'),(98,'elmer@group-officecom'),(97,'email/fromfile/demo_60d990b865619eml/demoeml'),(98,'email/fromfile/demo_60d990b87560eeml/demoeml'),(99,'email/fromfile/demo2_60d990b87e51beml/demo2eml'),(100,'email/fromfile/demo2_60d990b887b25eml/demo2eml'),(99,'emailjust'),(100,'emailjust'),(97,'emailrocket'),(98,'emailrocket'),(103,'eml'),(92,'emo'),(56,'emptydocx'),(57,'emptyodt'),(223,'encrypted5b33a2dc4cc8e0743a320861tvbzk8ufc4ooveai9s8vbak+bzyfdnkena'),(210,'erg'),(20,'europe/amsterdam'),(21,'europe/amsterdam'),(22,'europe/amsterdam'),(23,'europe/amsterdam'),(24,'europe/amsterdam'),(25,'europe/amsterdam'),(26,'europe/amsterdam'),(27,'europe/amsterdam'),(28,'europe/amsterdam'),(29,'europe/amsterdam'),(30,'europe/amsterdam'),(31,'europe/amsterdam'),(64,'europe/amsterdam'),(65,'europe/amsterdam'),(66,'europe/amsterdam'),(67,'europe/amsterdam'),(68,'europe/amsterdam'),(69,'europe/amsterdam'),(70,'europe/amsterdam'),(71,'europe/amsterdam'),(72,'europe/amsterdam'),(73,'europe/amsterdam'),(74,'europe/amsterdam'),(75,'europe/amsterdam'),(240,'europe/amsterdam'),(241,'europe/amsterdam'),(242,'europe/amsterdam'),(243,'europe/amsterdam'),(30,'eventblast'),(74,'eventblast'),(22,'eventmeet'),(23,'eventmeet'),(27,'eventmeet'),(66,'eventmeet'),(67,'eventmeet'),(71,'eventmeet'),(24,'eventmt'),(25,'eventmt'),(28,'eventmt'),(68,'eventmt'),(69,'eventmt'),(72,'eventmt'),(20,'eventproject'),(21,'eventproject'),(26,'eventproject'),(64,'eventproject'),(65,'eventproject'),(70,'eventproject'),(29,'eventrocket'),(73,'eventrocket'),(31,'eventtest'),(75,'eventtest'),(224,'ewre'),(31,'extender'),(75,'extender'),(31,'extenderlinda'),(75,'extenderlinda'),(72,'f84dd5cb-6976-542a-ba54-5779100287f0'),(35,'feed'),(36,'feed'),(37,'feed'),(76,'feed'),(77,'feed'),(78,'feed'),(81,'ff98bfa3-2ed6-5a57-8579-79699a4c46b5'),(85,'fff03b14-3576-58d0-8849-091c1840ae34'),(29,'fields'),(30,'fields'),(31,'fields'),(73,'fields'),(74,'fields'),(75,'fields'),(54,'filedemo'),(56,'fileemptydocxusers/demo/emptydocx'),(57,'fileemptyodtusers/demo/emptyodt'),(95,'filefolderpngprojects2/template-icons/folderpng'),(53,'filenopersonjpgusers/demo/nopersonjpg'),(96,'fileprojectpngprojects2/template-icons/projectpng'),(103,'filere'),(105,'filetestodtusers/admin/testodt'),(55,'filewecoyotepngusers/demo/wecoyotepng'),(59,'folder'),(101,'folderadminusers/admin'),(3,'folderbillingbilling'),(12,'folderdemo'),(13,'folderdemo'),(52,'folderdemousers/demo'),(33,'folderdon'),(9,'folderelmer'),(11,'folderelmer'),(108,'folderelmerusers/elmer'),(6,'folderinvoicesbilling/invoices'),(14,'folderlinda'),(15,'folderlinda'),(102,'folderloglog'),(5,'folderordersbilling/orders'),(95,'folderpng'),(7,'folderproduct_imagesbilling/product_images'),(59,'folderproject_templates/projects'),(58,'folderproject_templatesproject_templates'),(59,'folderprojects'),(62,'folderprojects2projects2'),(4,'folderquotesbilling/quotes'),(32,'folderroad'),(60,'folderstandard'),(61,'folderstationery-papersbilling/stationery-papers'),(2,'foldersystem'),(34,'foldersystem'),(63,'foldertemplate-iconsprojects2/template-icons'),(8,'folderticketstickets'),(51,'folderusersusers'),(92,'for'),(243,'forever'),(243,'foreversystem'),(243,'freqdaily'),(240,'freqdailycount3'),(241,'freqdailyuntil20210722t235900'),(244,'from'),(9,'fudd'),(11,'fudd'),(37,'fudd'),(40,'fudd'),(78,'fudd'),(81,'fudd'),(9,'fuddcalendar/elmer'),(11,'fuddtasks/elmer'),(199,'gjhghj'),(220,'gjhghj'),(251,'html'),(198,'i2021-004713pdf'),(47,'i21000001'),(48,'i21000002'),(88,'i21000003'),(89,'i21000004'),(30,'impact'),(74,'impact'),(16,'inc'),(17,'inc'),(41,'inc'),(42,'inc'),(45,'inc'),(47,'inc'),(82,'inc'),(83,'inc'),(86,'inc'),(88,'inc'),(212,'inc'),(214,'inc'),(104,'info'),(216,'info'),(18,'info@acmedemo'),(43,'info@acmedemo'),(46,'info@acmedemo'),(48,'info@acmedemo'),(84,'info@acmedemo'),(87,'info@acmedemo'),(89,'info@acmedemo'),(213,'info@acmedemo'),(104,'info@indonesiahijaucoid'),(216,'info@indonesiahijaucoid'),(16,'info@smithdemo'),(41,'info@smithdemo'),(45,'info@smithdemo'),(47,'info@smithdemo'),(82,'info@smithdemo'),(86,'info@smithdemo'),(88,'info@smithdemo'),(212,'info@smithdemo'),(47,'invoice/quotei21000001smith'),(48,'invoice/quotei21000002acme'),(88,'invoice/quotei21000003smith'),(89,'invoice/quotei21000004acme'),(45,'invoice/quoteo21000001smith'),(46,'invoice/quoteo21000002acme'),(86,'invoice/quoteo21000003smith'),(87,'invoice/quoteo21000004acme'),(41,'invoice/quoteq21000001smith'),(43,'invoice/quoteq21000002acme'),(82,'invoice/quoteq21000003smith'),(84,'invoice/quoteq21000004acme'),(6,'invoices'),(199,'it'),(206,'it'),(210,'it'),(239,'it'),(210,'jaja'),(197,'jansen'),(219,'jansen'),(225,'jansen'),(107,'jantje'),(218,'jantje'),(210,'january'),(211,'january'),(17,'john'),(27,'john'),(41,'john'),(45,'john'),(47,'john'),(71,'john'),(82,'john'),(86,'john'),(88,'john'),(214,'john'),(17,'john@smithdemo'),(214,'john@smithdemo'),(27,'johnlinda'),(71,'johnlinda'),(53,'jpg'),(16,'just'),(17,'just'),(18,'just'),(19,'just'),(92,'just'),(99,'just'),(100,'just'),(212,'just'),(213,'just'),(214,'just'),(215,'just'),(41,'kalverstraat'),(43,'kalverstraat'),(45,'kalverstraat'),(46,'kalverstraat'),(47,'kalverstraat'),(48,'kalverstraat'),(82,'kalverstraat'),(84,'kalverstraat'),(86,'kalverstraat'),(87,'kalverstraat'),(88,'kalverstraat'),(89,'kalverstraat'),(210,'lange'),(210,'langer'),(94,'launcher'),(94,'launcheracme'),(54,'letterdocx'),(54,'letterdocxusers/demo/demo'),(14,'linda'),(15,'linda'),(102,'log'),(49,'malfunctioning'),(90,'malfunctioning'),(110,'map1tmp/1'),(1,'mapcalendarcalendar'),(249,'mappeter'),(250,'mappeter'),(10,'maptaskstasks'),(109,'maptmptmp'),(22,'meet'),(23,'meet'),(27,'meet'),(66,'meet'),(67,'meet'),(71,'meet'),(20,'meeting'),(21,'meeting'),(24,'meeting'),(25,'meeting'),(26,'meeting'),(28,'meeting'),(38,'meeting'),(39,'meeting'),(40,'meeting'),(64,'meeting'),(65,'meeting'),(68,'meeting'),(69,'meeting'),(70,'meeting'),(72,'meeting'),(79,'meeting'),(80,'meeting'),(81,'meeting'),(20,'meetingdemo'),(24,'meetingdemo'),(38,'meetingdemo'),(64,'meetingdemo'),(68,'meetingdemo'),(79,'meetingdemo'),(40,'meetingelmer'),(81,'meetingelmer'),(21,'meetinglinda'),(25,'meetinglinda'),(26,'meetinglinda'),(28,'meetinglinda'),(39,'meetinglinda'),(65,'meetinglinda'),(69,'meetinglinda'),(70,'meetinglinda'),(72,'meetinglinda'),(80,'meetinglinda'),(99,'message'),(100,'message'),(99,'messagefrom:'),(100,'messagefrom:'),(35,'needs-action'),(36,'needs-action'),(37,'needs-action'),(38,'needs-action'),(39,'needs-action'),(40,'needs-action'),(42,'needs-action'),(44,'needs-action'),(76,'needs-action'),(77,'needs-action'),(78,'needs-action'),(79,'needs-action'),(80,'needs-action'),(81,'needs-action'),(83,'needs-action'),(85,'needs-action'),(16,'netherlands'),(17,'netherlands'),(18,'netherlands'),(19,'netherlands'),(212,'netherlands'),(213,'netherlands'),(214,'netherlands'),(215,'netherlands'),(210,'nog'),(16,'noord-holland'),(17,'noord-holland'),(18,'noord-holland'),(19,'noord-holland'),(41,'noord-holland'),(43,'noord-holland'),(45,'noord-holland'),(46,'noord-holland'),(47,'noord-holland'),(48,'noord-holland'),(82,'noord-holland'),(84,'noord-holland'),(86,'noord-holland'),(87,'noord-holland'),(88,'noord-holland'),(89,'noord-holland'),(212,'noord-holland'),(213,'noord-holland'),(214,'noord-holland'),(215,'noord-holland'),(53,'nopersonjpg'),(45,'o21000001'),(46,'o21000002'),(86,'o21000003'),(87,'o21000004'),(57,'odt'),(105,'odt'),(20,'office'),(21,'office'),(22,'office'),(23,'office'),(24,'office'),(25,'office'),(26,'office'),(27,'office'),(28,'office'),(64,'office'),(65,'office'),(66,'office'),(67,'office'),(68,'office'),(69,'office'),(70,'office'),(71,'office'),(72,'office'),(5,'orders'),(104,'partner'),(106,'partner'),(225,'partner'),(198,'pdf'),(239,'peter'),(249,'peter'),(250,'peter'),(197,'piet'),(219,'piet'),(225,'piet'),(92,'placeholder'),(97,'plan'),(98,'plan'),(97,'planfrom:'),(98,'planfrom:'),(55,'png'),(95,'png'),(96,'png'),(251,'powiadomieniehtml'),(38,'prepare'),(39,'prepare'),(40,'prepare'),(79,'prepare'),(80,'prepare'),(81,'prepare'),(7,'product_images'),(20,'project'),(21,'project'),(26,'project'),(60,'project'),(64,'project'),(65,'project'),(70,'project'),(58,'project_templates'),(93,'project001'),(94,'project001'),(92,'projectdemo'),(96,'projectpng'),(60,'projectproject_templates/standard'),(59,'projects'),(92,'projects'),(62,'projects2'),(41,'q21000001'),(42,'q21000001'),(42,'q21000001system'),(43,'q21000002'),(44,'q21000002'),(44,'q21000002system'),(82,'q21000003'),(83,'q21000003'),(83,'q21000003system'),(84,'q21000004'),(85,'q21000004'),(85,'q21000004system'),(4,'quotes'),(202,'rack'),(207,'rack'),(209,'rack'),(211,'rack'),(31,'range'),(75,'range'),(93,'range'),(94,'range'),(103,'rbs'),(242,'recurence'),(242,'recurencesystem'),(240,'recurring'),(241,'recurring'),(243,'repeat'),(32,'road'),(29,'rocket'),(73,'rocket'),(93,'rocket'),(94,'rocket'),(97,'rocket'),(98,'rocket'),(49,'rockets'),(50,'rockets'),(90,'rockets'),(91,'rockets'),(49,'rocketswile'),(50,'rocketswile'),(90,'rocketswile'),(91,'rocketswile'),(32,'room'),(33,'room'),(33,'roomcalendar/don'),(32,'roomcalendar/road'),(32,'runner'),(197,'sdasd'),(219,'sdasd'),(107,'sdfsedd'),(218,'sdfsedd'),(41,'sir/madam'),(43,'sir/madam'),(45,'sir/madam'),(46,'sir/madam'),(47,'sir/madam'),(48,'sir/madam'),(82,'sir/madam'),(84,'sir/madam'),(86,'sir/madam'),(87,'sir/madam'),(88,'sir/madam'),(89,'sir/madam'),(14,'smith'),(15,'smith'),(16,'smith'),(17,'smith'),(21,'smith'),(23,'smith'),(25,'smith'),(26,'smith'),(27,'smith'),(28,'smith'),(29,'smith'),(30,'smith'),(31,'smith'),(36,'smith'),(39,'smith'),(41,'smith'),(42,'smith'),(45,'smith'),(47,'smith'),(65,'smith'),(67,'smith'),(69,'smith'),(70,'smith'),(71,'smith'),(72,'smith'),(73,'smith'),(74,'smith'),(75,'smith'),(77,'smith'),(80,'smith'),(82,'smith'),(83,'smith'),(86,'smith'),(88,'smith'),(212,'smith'),(214,'smith'),(249,'smith'),(250,'smith'),(14,'smithcalendar/linda'),(250,'smithcalendar/peter'),(15,'smithtasks/linda'),(249,'smithtasks/peter'),(50,'speed'),(91,'speed'),(60,'standard'),(61,'stationery-papers'),(92,'sub'),(2,'system'),(34,'system'),(42,'taskcall:'),(44,'taskcall:'),(83,'taskcall:'),(85,'taskcall:'),(35,'taskfeed'),(36,'taskfeed'),(37,'taskfeed'),(76,'taskfeed'),(77,'taskfeed'),(78,'taskfeed'),(38,'taskprepare'),(39,'taskprepare'),(40,'taskprepare'),(79,'taskprepare'),(80,'taskprepare'),(81,'taskprepare'),(10,'tasks'),(210,'tekst'),(63,'template-icons'),(30,'test'),(31,'test'),(74,'test'),(75,'test'),(223,'test'),(225,'test'),(29,'testing'),(30,'testing'),(31,'testing'),(73,'testing'),(74,'testing'),(75,'testing'),(29,'testinglinda'),(73,'testinglinda'),(30,'testlinda'),(74,'testlinda'),(105,'testodt'),(35,'the'),(36,'the'),(37,'the'),(76,'the'),(77,'the'),(78,'the'),(50,'ticketcan'),(91,'ticketcan'),(49,'ticketmalfunctioning'),(90,'ticketmalfunctioning'),(8,'tickets'),(240,'times'),(240,'timessystem'),(109,'tmp'),(97,'to:'),(98,'to:'),(99,'to:'),(100,'to:'),(241,'until'),(241,'untilsystem'),(12,'user'),(13,'user'),(20,'user'),(22,'user'),(24,'user'),(35,'user'),(38,'user'),(64,'user'),(66,'user'),(68,'user'),(76,'user'),(79,'user'),(97,'user'),(98,'user'),(99,'user'),(100,'user'),(12,'usercalendar/demo'),(51,'users'),(13,'usertasks/demo'),(55,'wecoyotepng'),(19,'wile'),(22,'wile'),(23,'wile'),(43,'wile'),(46,'wile'),(48,'wile'),(49,'wile'),(50,'wile'),(66,'wile'),(67,'wile'),(84,'wile'),(87,'wile'),(89,'wile'),(90,'wile'),(91,'wile'),(93,'wile'),(94,'wile'),(202,'wile'),(215,'wile'),(19,'wile@smithdemo'),(49,'wile@smithdemo'),(50,'wile@smithdemo'),(90,'wile@smithdemo'),(91,'wile@smithdemo'),(215,'wile@smithdemo'),(22,'wiledemo'),(66,'wiledemo'),(23,'wilelinda'),(67,'wilelinda'),(210,'yoyo'),(211,'yoyo'),(222,'yoyo');
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
INSERT INTO `core_setting` VALUES (1,'cacheClearedAt','1630403818'),(1,'databaseVersion','6.5.74'),(1,'language','en'),(1,'license','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJob3N0bmFtZSI6ImxvY2FsaG9zdCxob3N0LmRvY2tlci5pbnRlcm5hbCwqLmdyb3VwLW9mZmljZS5jb20iLCJ2ZXJzaW9uIjoiNi42IiwibGljZW5zZXMiOnsiZ3JvdXBvZmZpY2UtcHJvIjp7ImV4cGlyZXNBdCI6bnVsbH0sImJpbGxpbmciOnsiZXhwaXJlc0F0IjpudWxsfSwiZG9jdW1lbnRzIjp7ImV4cGlyZXNBdCI6bnVsbH19fQ.jsPXTnwNfC96Gi9yEOzDRAR20lbxC85dEXL7LrL-QmE'),(1,'locale','C.UTF-8'),(1,'passwordMinLength','4'),(1,'smtpPassword',NULL),(1,'systemEmail','admin@intermesh.localhost'),(1,'URL','http://localhost:8080/'),(1,'userAddressBookId','2'),(1,'welcomeShown','1'),(6,'block',''),(6,'countDown','0'),(6,'enforceForGroupId',NULL),(29,'authorizationHeader','AuthorizationJwt'),(29,'documentServerSecret','s3cr3t'),(29,'documentServerUrl','http://host.docker.internal:9080/');
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
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_user`
--

LOCK TABLES `core_user` WRITE;
/*!40000 ALTER TABLE `core_user` DISABLE KEYS */;
INSERT INTO `core_user` VALUES (1,'admin','System Administrator','c9270970b441cac135735894dacd3bc21a01b0e2',1,'admin@intermesh.localhost','admin@intermesh.localhost',NULL,NULL,'2021-09-02 07:47:02','2021-06-28 08:52:47','2021-09-02 07:47:02','d-m-Y',0,'G:i','.',',','‚Ç¨',55,20,'Europe/Amsterdam','summary','nl','Paper',1,'last_name',0,1,0,0,1,0,';','\"',0,NULL,217591,0,NULL,0,0,0,0,'users/admin',0),(2,'elmer','Elmer Fudd','0ec2f1f4f9fb41e8013fcc834991be30a8260750',1,'elmer@acmerpp.demo','elmer@acmerpp.demo',NULL,NULL,'2021-07-09 06:57:09','2021-06-28 08:56:39','2021-07-09 07:45:43','d-m-Y',1,'G:i','.',',','‚Ç¨',12,20,'Europe/Amsterdam','summary','en_uk','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/elmer',0),(3,'demo','Demo User','a2b13489e9762bf7d7dfd63d72d45f0f47411c30',1,'demo@acmerpp.demo','demo@acmerpp.demo',NULL,NULL,NULL,'2021-06-28 08:56:40','2021-06-28 08:56:40','d-m-Y',1,'G:i','.',',','‚Ç¨',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/demo',0),(4,'linda','Linda Smith','c363a83f50fe2fbe94deff31afee36d8d7923e17',1,'linda@acmerpp.linda','linda@acmerpp.linda',NULL,NULL,NULL,'2021-06-28 08:56:41','2021-06-28 08:56:41','d-m-Y',1,'G:i','.',',','‚Ç¨',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/linda',0),(9,'peter','Peter Smith','64d5f732477ccf666a17e2544dc5f2516025433c',1,'peter@intermesh.nl','peter@intermesh.nl',NULL,NULL,NULL,'2021-08-31 07:43:43','2021-08-31 07:44:24','d-m-Y',1,'G:i','.',',','‚Ç¨',0,20,'Europe/Amsterdam','summary','en','Paper',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,NULL,0,0,0,0,'users/peter',0);
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
INSERT INTO `core_user_group` VALUES (1,1),(2,1),(2,2),(2,3),(2,4),(2,9),(3,2),(3,3),(3,4),(3,9),(4,1),(5,2),(6,3),(7,4),(11,2),(11,4),(14,3),(14,4),(15,9);
/*!40000 ALTER TABLE `core_user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dav_calendar_changes`
--

DROP TABLE IF EXISTS `dav_calendar_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dav_calendar_changes` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varbinary(200) NOT NULL,
  `synctoken` int(11) unsigned NOT NULL,
  `calendarid` int(11) unsigned NOT NULL,
  `operation` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `calendarid_synctoken` (`calendarid`,`synctoken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dav_calendar_changes`
--

LOCK TABLES `dav_calendar_changes` WRITE;
/*!40000 ALTER TABLE `dav_calendar_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `dav_calendar_changes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dav_events`
--

DROP TABLE IF EXISTS `dav_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dav_events` (
  `id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dav_events`
--

LOCK TABLES `dav_events` WRITE;
/*!40000 ALTER TABLE `dav_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `dav_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dav_locks`
--

DROP TABLE IF EXISTS `dav_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dav_locks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timeout` int(10) unsigned DEFAULT NULL,
  `created` int(11) DEFAULT NULL,
  `token` varbinary(100) DEFAULT NULL,
  `scope` tinyint(4) DEFAULT NULL,
  `depth` tinyint(4) DEFAULT NULL,
  `uri` varbinary(1000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `token` (`token`),
  KEY `uri` (`uri`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dav_locks`
--

LOCK TABLES `dav_locks` WRITE;
/*!40000 ALTER TABLE `dav_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `dav_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dav_tasks`
--

DROP TABLE IF EXISTS `dav_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dav_tasks` (
  `id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `uri` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dav_tasks`
--

LOCK TABLES `dav_tasks` WRITE;
/*!40000 ALTER TABLE `dav_tasks` DISABLE KEYS */;
/*!40000 ALTER TABLE `dav_tasks` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts`
--

LOCK TABLES `em_accounts` WRITE;
/*!40000 ALTER TABLE `em_accounts` DISABLE KEYS */;
INSERT INTO `em_accounts` VALUES (1,1,130,NULL,'mailserver',143,0,0,'admin@intermesh.localhost','{GOCRYPT2}def502002f7c451276728ebbacf4fd39175217766bc37e6a3ec186050a580ffd717b50e1bf8239b8345d3a28a6792812db55ee1cffb47e0b5fd1023bf00e66539d223056bb25234114e64b83d5d35dc14e9bddbd660c8ba84ec776e363a8','',0,'','Sent','Drafts','Trash','Spam','mailserver',25,'',0,'','',2,0,4190,1,'INBOX',0,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_aliases`
--

LOCK TABLES `em_aliases` WRITE;
/*!40000 ALTER TABLE `em_aliases` DISABLE KEYS */;
INSERT INTO `em_aliases` VALUES (1,1,'Admin','admin@intermesh.localhost','',1),(2,1,'pipo@pipoos.nl <script>alert(\'hoi\');</script>','admin@intermesh.localhost','',0),(3,1,'admin@intermesh.localhost','admin@intermesh.localhost','',0);
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
INSERT INTO `em_contacts_last_mail_times` VALUES (6,1,1629968491),(20,1,1629968491);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_labels`
--

LOCK TABLES `em_labels` WRITE;
/*!40000 ALTER TABLE `em_labels` DISABLE KEYS */;
INSERT INTO `em_labels` VALUES (1,'Label 1','$label1','7A7AFF',1,1),(2,'Label 2','$label2','59BD59',1,1),(3,'Label 3','$label3','FFBD59',1,1),(4,'Label 4','$label4','FF5959',1,1),(5,'Label 5','$label5','BD7ABD',1,1);
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
  `uid` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
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
INSERT INTO `em_links` VALUES (1,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_60d990b865619.eml/demo.eml',1624871096,1624871096,1,76,'<1368777188.5195e1e479413@localhost>'),(2,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_60d990b87560e.eml/demo.eml',1624871096,1624871096,1,76,'<1368777188.5195e1e479413@localhost>'),(3,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_60d990b87e51b.eml/demo2.eml',1624871096,1624871096,1,76,'<1368777986.5195e5020b17e@localhost>'),(4,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_60d990b887b25.eml/demo2.eml',1624871096,1624871096,1,76,'<1368777986.5195e5020b17e@localhost>');
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
INSERT INTO `email_default_email_account_templates` VALUES (1,1);
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
INSERT INTO `email_default_email_templates` VALUES (1,1),(9,1),(8,4);
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
INSERT INTO `fs_filehandlers` VALUES (1,'odt','go\\modules\\business\\onlyoffice\\filehandler\\Onlyoffice'),(1,'pdf','GO\\Files\\Filehandler\\Inline'),(1,'xlsx','GO\\Assistant\\Filehandler\\Assistant');
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
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_files`
--

LOCK TABLES `fs_files` WRITE;
/*!40000 ALTER TABLE `fs_files` DISABLE KEYS */;
INSERT INTO `fs_files` VALUES (1,22,'noperson.jpg',0,0,1624870604,1624871092,1,3015,1,NULL,'jpg',0,NULL,0,NULL,1),(2,22,'Demo letter.docx',0,0,1624870604,1624871092,1,4312,1,NULL,'docx',0,NULL,0,NULL,1),(3,22,'wecoyote.png',0,0,1624870604,1624871092,1,39495,1,NULL,'png',0,NULL,0,NULL,1),(4,22,'empty.docx',0,0,1624870604,1624871092,1,3726,1,NULL,'docx',0,NULL,0,NULL,1),(5,22,'empty.odt',0,0,1624870605,1624871092,1,6971,1,NULL,'odt',0,NULL,0,NULL,1),(6,28,'folder.png',0,0,1624871095,1624870825,1,611,1,NULL,'png',0,NULL,0,NULL,1),(7,28,'project.png',0,0,1624871095,1624870825,1,3231,1,NULL,'png',0,NULL,0,NULL,1),(8,29,'Re change HS code RB\'s - 18-06-2021 0858.eml',0,0,1624875088,1624875087,1,17653,1,NULL,'eml',0,NULL,0,NULL,1),(9,29,'Test.odt',0,0,1624885909,1624889274,1,4485,1,NULL,'odt',0,NULL,0,NULL,3),(95,29,'I2021-004713.pdf',0,0,1626098741,1626098741,1,119120,1,'','pdf',0,NULL,0,NULL,1),(109,33,'powiadomienie.html',0,0,1630396147,1630396147,1,2225,1,NULL,'html',0,NULL,0,NULL,1);
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
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folders`
--

LOCK TABLES `fs_folders` WRITE;
/*!40000 ALTER TABLE `fs_folders` DISABLE KEYS */;
INSERT INTO `fs_folders` VALUES (1,1,0,'calendar',0,24,NULL,1,1624870441,1630395824,1,1,1,NULL,0),(1,2,1,'System Administrator',0,39,NULL,1,1624870442,1624870442,1,1,1,NULL,0),(1,3,0,'billing',0,24,NULL,1,1624870494,1624871065,1,1,1,NULL,0),(1,4,3,'Quotes',0,42,NULL,1,1624870494,1624870494,1,1,1,NULL,0),(1,5,3,'Orders',0,47,NULL,1,1624870495,1624870495,1,1,1,NULL,0),(1,6,3,'Invoices',0,52,NULL,1,1624870495,1624870495,1,1,1,NULL,0),(1,7,3,'product_images',0,41,NULL,1,1624870495,1624870495,1,1,0,NULL,0),(1,8,0,'tickets',0,24,NULL,1,1624870528,1624870528,1,1,1,NULL,0),(1,9,8,'0-IT',0,65,NULL,1,1624870528,1624870528,1,1,1,NULL,0),(1,10,8,'0-Sales',0,66,NULL,1,1624870528,1624870528,1,1,1,NULL,0),(1,11,1,'Elmer Fudd',0,78,NULL,1,1624870600,1624870600,1,1,1,NULL,0),(1,12,0,'tasks',0,24,NULL,1,1624870600,1630395824,1,1,1,NULL,0),(1,13,12,'Elmer Fudd',0,79,NULL,1,1624870600,1624870600,1,1,1,NULL,0),(1,14,1,'Demo User',0,83,NULL,1,1624870600,1624870600,1,1,1,NULL,0),(1,15,12,'Demo User',0,84,NULL,1,1624870600,1624870601,1,1,1,NULL,0),(1,16,1,'Linda Smith',0,88,NULL,1,1624870601,1624870601,1,1,1,NULL,0),(1,17,12,'Linda Smith',0,89,NULL,1,1624870601,1624870601,1,1,1,NULL,0),(1,18,1,'Road Runner Room',0,94,NULL,1,1624870602,1624870602,1,1,1,NULL,0),(1,19,1,'Don Coyote Room',0,95,NULL,1,1624870602,1624870602,1,1,1,NULL,0),(1,20,12,'System Administrator',0,96,NULL,1,1624870602,1624870602,1,1,1,NULL,0),(1,21,0,'users',0,24,NULL,1,1624870604,1625749590,2,1,1,NULL,0),(3,22,21,'demo',1,102,NULL,1,1624870604,1624870604,1,1,1,NULL,0),(1,23,0,'project_templates',0,24,NULL,1,1624870825,1624870826,1,1,1,NULL,0),(1,24,23,'Projects folder',0,112,NULL,1,1624870826,1624870826,1,1,1,NULL,0),(1,25,23,'Standard project',0,113,NULL,1,1624870826,1624870826,1,1,1,NULL,0),(1,26,3,'stationery-papers',0,0,NULL,1,1624871065,1624871065,1,1,0,NULL,0),(1,27,0,'projects2',0,24,NULL,1,1624871066,1624871066,1,1,1,NULL,0),(1,28,27,'template-icons',0,106,NULL,1,1624871066,1624870825,1,1,0,NULL,0),(1,29,21,'admin',1,131,NULL,1,1624875078,1626098741,1,1,1,NULL,0),(1,30,0,'log',0,24,NULL,1,1624875078,1624875078,1,1,1,NULL,0),(2,31,21,'elmer',1,140,NULL,1,1625749590,1625749590,2,1,1,NULL,0),(1,32,0,'tmp',0,24,NULL,1,1625749637,1625749637,1,1,1,NULL,0),(1,33,32,'1',0,141,NULL,1,1625749637,1630396147,1,1,0,NULL,0),(1,38,12,'Peter Smith',0,192,NULL,1,1630395824,1630395824,1,1,1,NULL,0),(1,39,1,'Peter Smith',0,193,NULL,1,1630395824,1630395824,1,1,1,NULL,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_templates`
--

LOCK TABLES `fs_templates` WRITE;
/*!40000 ALTER TABLE `fs_templates` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_versions`
--

LOCK TABLES `fs_versions` WRITE;
/*!40000 ALTER TABLE `fs_versions` DISABLE KEYS */;
INSERT INTO `fs_versions` VALUES (1,9,1624885909,1,'versioning/9/20210628_151149_Test.odt',1,8205),(2,9,1624886060,1,'versioning/9/20210628_151420_Test.odt',2,4542);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron`
--

LOCK TABLES `go_cron` WRITE;
/*!40000 ALTER TABLE `go_cron` DISABLE KEYS */;
INSERT INTO `go_cron` VALUES (1,'Calendar publisher',1,'0','*','*','*','*','*','GO\\Calendar\\Cron\\CalendarPublisher',0,1629720000,1629719533,1629719533,NULL,0,'[]'),(2,'Email Reminders',1,'*/5','*','*','*','*','*','GO\\Base\\Cron\\EmailReminders',0,1629719700,1629719533,1629719533,NULL,0,'[]'),(3,'Calculate disk usage',1,'0','0','*','*','*','*','GO\\Base\\Cron\\CalculateDiskUsage',0,1629763200,1629719533,1629719533,NULL,0,'[]'),(5,'Close inactive tickets',1,'0','2','*','*','*','*','GO\\Tickets\\Cron\\CloseInactive',0,1629770400,1629719534,1629719534,NULL,0,'[]'),(6,'Ticket reminders',1,'*/5','*','*','*','*','*','GO\\Tickets\\Cron\\Reminder',0,1629719700,1629719533,1629719533,NULL,0,'[]'),(7,'Import tickets from IMAP',1,'0,5,10,15,20,25,30,35,40,45,50,55','*','*','*','*','*','GO\\Tickets\\Cron\\ImportImap',0,1629719700,1629719533,1629719533,NULL,0,'[]'),(8,'Sent tickets due reminder',1,'0','1','*','*','*','*','GO\\Tickets\\Cron\\DueMailer',0,1629766800,1629719534,1629719534,NULL,0,'[]'),(9,'Contract Expiry Notification Cron',1,'2','7','*','*','*','*','GO\\Projects2\\Cron\\IncomeNotification',0,1629788520,1629719534,1629719534,NULL,0,'[]');
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
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_holidays`
--

LOCK TABLES `go_holidays` WRITE;
/*!40000 ALTER TABLE `go_holidays` DISABLE KEYS */;
INSERT INTO `go_holidays` VALUES (1,'2021-01-01','New Years Day','en',1),(2,'2021-01-06','Twelfth Day','en',1),(3,'2021-05-01','May Day','en',1),(4,'2021-08-15','Assumption Day','en',1),(5,'2021-10-03','German Unification Day','en',1),(6,'2021-10-31','Reformation Day','en',1),(7,'2021-11-01','All Saints\' Day','en',1),(8,'2021-12-25','Christmas Day','en',1),(9,'2021-12-26','Boxing Day','en',1),(10,'2021-02-15','Shrove Monday','en',1),(11,'2021-02-16','Shrove Tuesday','en',1),(12,'2021-02-17','Ash Wednesday','en',1),(13,'2021-04-02','Good Friday','en',1),(14,'2021-04-04','Easter Sunday','en',1),(15,'2021-04-05','Easter Monday','en',1),(16,'2021-05-13','Ascension Day','en',1),(17,'2021-05-23','Whit Sunday','en',1),(18,'2021-05-24','Whit Monday','en',1),(19,'2021-06-03','Feast of Corpus Christi','en',1),(20,'2021-11-17','Penance Day','en',1),(21,'2021-01-01','New Years Day','en_uk',1),(22,'2021-12-25','Christmas Day','en_uk',1),(23,'2021-12-26','Boxing Day','en_uk',1),(24,'2021-04-02','Good Friday','en_uk',1),(25,'2021-04-04','Easter Sunday','en_uk',1),(26,'2021-04-05','Easter Monday','en_uk',1),(27,'2021-08-30','Summer bank holiday','en_uk',1),(28,'2021-05-31','Spring bank holiday','en_uk',1),(29,'2021-05-03','Early May bank holiday','en_uk',1),(30,'2021-01-01','Nieuwjaar','nl',1),(31,'2021-02-14','Valentijnsdag','nl',0),(32,'2021-10-04','Wereld dierendag','nl',0),(33,'2021-11-11','Sint Maarten','nl',0),(34,'2021-12-25','1e kerstdag','nl',1),(35,'2021-12-26','2e kerstdag','nl',1),(36,'2021-12-31','Oudjaarsavond','nl',0),(37,'2021-04-02','Goede vrijdag','nl',0),(38,'2021-04-04','1e Paasdag','nl',1),(39,'2021-04-05','2e Paasdag','nl',1),(40,'2021-05-13','Hemelvaartsdag','nl',1),(41,'2021-05-23','1e pinksterdag','nl',1),(42,'2021-05-24','2e pinksterdag','nl',1),(43,'2021-04-27','Koningsdag','nl',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_reminders`
--

LOCK TABLES `go_reminders` WRITE;
/*!40000 ALTER TABLE `go_reminders` DISABLE KEYS */;
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
INSERT INTO `go_settings` VALUES (0,'cron_last_run','1629719534'),(0,'database_usage','10240000'),(0,'file_storage_usage','91700703'),(0,'mailbox_usage','0'),(0,'projects_bill_item_template','{project_name}: {registering_user_name} worked {units} hours on {date}'),(0,'projects_detailed_printout_on','true'),(0,'projects_payout_item_template','{project_name}: {description} of {responsible_user_name} worked {units} hours in {days} days\n\nTotal: {total_price}. (You can use custom fields of the manager in this template with {col_x})'),(0,'projects_summary_bill_item_template','{project_name} {description} at {registering_user_name}\nUnits:{units}\nDays:{days}'),(0,'projects_summary_payout_item_template','{project_name} {description} of {responsible_user_name}\nUnits: {units}, Days: {days}'),(0,'tickets_bill_item_template','{date} #{number} rate: {rate_name}\n{subject}'),(0,'uuid_namespace','ac3a60fc-2e3d-423e-bd51-d2fb832e9bf0'),(0,'zpushadmin_can_connect','1'),(1,'email_always_request_notification','0'),(1,'email_always_respond_to_notifications','0'),(1,'email_defaultTemplateId',NULL),(1,'email_font_size','14px'),(1,'email_show_bcc','0'),(1,'email_show_cc','1'),(1,'email_show_from','1'),(1,'email_skip_unknown_recipients','0'),(1,'email_sort_email_addresses_by_time','1'),(1,'email_use_plain_text_markup','0'),(1,'ms_3order_statuses','12,11,10,9'),(1,'ms_calendars','1'),(1,'ms_categories',''),(1,'ms_pm-status-filter','1,2,3'),(1,'ms_pr2_statuses',''),(1,'ms_ta-taskslists','1,2,3,4'),(1,'ms_ti-types-grid','1,2'),(1,'projects2_tree_state','[\"root\",1]'),(1,'tasks_filter','active'),(2,'email_always_request_notification','0'),(2,'email_always_respond_to_notifications','0'),(2,'email_defaultTemplateId',NULL),(2,'email_font_size','14px'),(2,'email_show_bcc','0'),(2,'email_show_cc','1'),(2,'email_show_from','1'),(2,'email_skip_unknown_recipients','0'),(2,'email_sort_email_addresses_by_time','1'),(2,'email_use_plain_text_markup','0'),(2,'files_shared_cache_ctime','1625749591'),(2,'ms_calendars','2'),(2,'ms_ta-taskslists','1'),(2,'ms_ti-types-grid','1,2'),(2,'tasks_filter','active'),(3,'email_always_request_notification','0'),(3,'email_always_respond_to_notifications','0'),(3,'email_defaultTemplateId',NULL),(3,'email_font_size','14px'),(3,'email_show_bcc','0'),(3,'email_show_cc','1'),(3,'email_show_from','1'),(3,'email_skip_unknown_recipients','0'),(3,'email_sort_email_addresses_by_time','1'),(3,'email_use_plain_text_markup','0'),(4,'email_always_request_notification','0'),(4,'email_always_respond_to_notifications','0'),(4,'email_defaultTemplateId',NULL),(4,'email_font_size','14px'),(4,'email_show_bcc','0'),(4,'email_show_cc','1'),(4,'email_show_from','1'),(4,'email_skip_unknown_recipients','0'),(4,'email_sort_email_addresses_by_time','1'),(4,'email_use_plain_text_markup','0'),(9,'email_always_request_notification','0'),(9,'email_always_respond_to_notifications','0'),(9,'email_defaultTemplateId',NULL),(9,'email_font_size','14px'),(9,'email_show_bcc','0'),(9,'email_show_cc','1'),(9,'email_show_from','1'),(9,'email_skip_unknown_recipients','0'),(9,'email_sort_email_addresses_by_time','1'),(9,'email_use_plain_text_markup','0');
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
INSERT INTO `go_state` VALUES (1,'addressbook-contact-dialog','o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A700%5Eheight%3Dn%253A875'),(1,'contact-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aindex%25255Ewidth%25253Dn%2525253A42%255Eo%25253Aid%25253Ds%2525253Aid%25255Ewidth%25253Dn%2525253A52.5%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A262.5%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aorganizations%25255Ewidth%25253Dn%2525253A262.5%255Eo%25253Aid%25253Ds%2525253Aaddressbook%25255Ewidth%25253Dn%2525253A175%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AcreatedAt%25255Ewidth%25253Dn%2525253A147%255Eo%25253Aid%25253Ds%2525253AmodifiedAt%25255Ewidth%25253Dn%2525253A147%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A10%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A11%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A12%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A13%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A14%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A15%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AphoneNumbers%25255Ewidth%25253Dn%2525253A262.5%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AemailAddresses%25255Ewidth%25253Dn%2525253A262.5%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AfirstName%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AmiddleName%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AlastName%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Abirthday%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AactionDate%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Only_in_shared%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Customer_type%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Manager%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Action_date%25255Ewidth%25253Dn%2525253A147%255Eo%25253Aid%25253Ds%2525253Acustom-field-date%25255Ewidth%25253Dn%2525253A112%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-For_piet%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-go_check%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Month%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253AlastName%255Edirection%253Ds%25253AASC'),(1,'Document-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Acustom-field-Client%25255Ewidth%25253Dn%2525253A1000%255Eo%25253Aid%25253Ds%2525253Acustom-field-Date_in%25255Ewidth%25253Dn%2525253A112%255Eo%25253Aid%25253Ds%2525253Acustom-field-Date_out%25255Ewidth%25253Dn%2525253A112%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Location%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Acustom-field-Client_Month%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253AshowID%25255Ewidth%25253Dn%2525253A151.5%255Eo%25253Aid%25253Ds%2525253AshowCreator%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AshowCreationDate%25255Ewidth%25253Dn%2525253A147%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AshowModifier%25255Ewidth%25253Dn%2525253A140%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253AshowModificationDate%25255Ewidth%25253Dn%2525253A147%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Aid%255Edirection%253Ds%25253ADESC'),(1,'go-email-west','o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A631'),(1,'history-detail','o%3Acollapsed%3Db%253A1'),(1,'open-modules','a%3As%253Aaddressbook%5Es%253Atickets%5Es%253Aemail%5Es%253Asummary%5Es%253Astudio%5Es%253Adocuments%5Es%253Atest2%5Es%253Atest3%5Es%253Atasks'),(1,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A35%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A601%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(2,'calendar-state','s%3A%7B%22displayType%22%3A%22days%22%2C%22days%22%3A5%2C%22calendars%22%3A%5B2%5D%2C%22view_id%22%3A0%2C%22group_id%22%3A1%7D'),(2,'list-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A832%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday');
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_templates`
--

LOCK TABLES `go_templates` WRITE;
/*!40000 ALTER TABLE `go_templates` DISABLE KEYS */;
INSERT INTO `go_templates` VALUES (1,1,0,'Default',9,'Message-ID: <6c11a986262d30d605330aedaaff1d0a@host.docker.internal>\r\nDate: Mon, 28 Jun 2021 08:52:53 +0000\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_1624870373_b38dd7d49158d1cd56a9c6235c4d46cd_=_\"\r\nX-Mailer: Group-Office (6.5.61)\r\n\r\n\r\n--_=_swift_1624870373_b38dd7d49158d1cd56a9c6235c4d46cd_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nHi {contact:firstName},\r\n\r\n{body}\r\n\r\nBest regards\r\n\r\n\r\n{user:displayName}\r\n\r\n--_=_swift_1624870373_b38dd7d49158d1cd56a9c6235c4d46cd_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nHi<gotpl if=3D\"contact:firstName\"> {contact:firstName},</gotpl><br />\r\n<br />\r\n{body}<br />\r\n<br />\r\nBest regards<br />\r\n<br />\r\n<br />\r\n{user:displayName}<br />\r\n\r\n--_=_swift_1624870373_b38dd7d49158d1cd56a9c6235c4d46cd_=_--\r\n',NULL,''),(2,1,1,'Letter',58,'PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels≠íMKAÜÔ˝CÓ›l+à»Œˆ\"Bo\"ıÑôÏÓ–Œ3i≠ˇﬁA\n∫Pä†«ºyÛ“mŒ˛†NúããA√™iAq0—∫0jx€=/`”/∫W>ê‘Jô\\*™ﬁÑ¢aIèà≈LÏ©41q®õ!fOR«<b\"≥ßëq›∂˜ò2†ü1’÷j»[ªµ˚H¸76z≤$Ñ&f^¶\\Ø≥8.Nyd—`£y©q˘j4ïx]h˝{°8ŒS4GœAÆyÒY8X∂∑ï(•[Fwˇi4o|Àº«l—^‚ãÕ¢√ŸÙüPKË–#Ÿ\0\0\0=\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels≠ëM\n¬0Ö˜û\"Ãﬁ¶Uë¶nDp+ı\01ù∂¡6	…(z{äZ(‚¬Â¸}Ô1/__˚é]–mçÄ,IÅ°Q∂“¶p(∑”%¨ãIæ«NR\\	≠vÅ≈¥Dn≈yP-ˆ2$÷°âì⁄˙^R,}√ùT\'Ÿ ü•ÈÇ˚O&€U¸Æ Äï7áø∞m]kÖ´Œ=\Z\Zë‡ÅnÜHîæA®ì»>.?˚ß|m\rïÚÿ·€¡´ıÕƒ¸Ø?@¢òÂÁûùßÖIŒ·wPK˘/0¿≈\0\0\0\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlEéK¬0D˜ú\"ÚX©Hª„¿Bk†RbG±°¿È	+ñ£73z˚Óï¢ybëë…√r·¿ ı<åtÛp>Ê[0¢ÅÜô–√∫v∂ü\ZA’⁄SHö…√]57÷J«d¡©≤+ó¥∆r≥ó!ÓQ§NS¥+Á÷6Öë†≠óÊd¶&cÈë¥Í8ˆºÜG‘S∏ïs≠<CÙ∞qª∂óˆPKv’é≠•\0\0\0–\0\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xml≈ê¡N√0ÜÔ<Eî;KŸM’⁄i‚»∆x©ªFJ‚*-}{≤∂;Q§Å&qKÏﬂˇ˜€€›ß≥¢√¿Ü|!VôË5U∆ü\n˘~xæﬂH¡|ñ<r@ñªÚn€Á5˘»\"ç{ŒC!õ€\\)÷\r:‡µËSØ¶‡ ¶o8)™k£ÒâÙáC’:ÀU@1°π1-ÀŸ≠ø∆≠ßPµÅ42ß¨ŒN~åóÂúNÙπóBåC/ÿãWr0	tÅÒ¨È¿2À§\ZÁ¿;\\™aîèç÷D›\\ÍGãÁñö`ﬂ†oÉ;í]d≠oÕ⁄\'…2jq-Ó\rÛQW‹rsÛ[˛∏ﬂø£~u ˘¡ÂPKÃIóä\0\0w\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xmlΩV[o⁄0~ﬂØàÚﬁÊBÀMM+∆Ñ@B¨›ﬁâ!^€≤ù˙ÎgáÑñ“d	c„««>ˆ˜ùõœ›√6!÷3\Zÿﬁµk[àÜ,¬tÿ?û&W}€í\nhÑQÿ;$Ìá˚/wŸP™A““˙T≥¿éï‚C«ëaåê◊å#™◊÷L$†ÙTlúåâà\")ıÒ	q|◊Ì:	`jﬂóZ≈8ã;ˇ–Ä≤°⁄q}7<Œ∑SHÃÓ Å˝\r≠!% Z\Z\r€…ó—VïÀ≈Aπú?\n3d8bŸòQ%)∑≠Å»B[¶úçsî*6›ÒQYÓR\"-61m∑5aŸcJCıqŸ9\\%äa¢o3ßÄ1Ïë¿@πP\Zø‡gj-ÅJ#B ’Hb8YàGTts!#LîóªÊ7*(ºñRﬂ-%„ˇf/#@7Z∂¬ë>ˆuªΩ:ææî‰:î\\-Ê∑úîS8´⁄{^ÔMòx≥º=§H}ßGŒÛ∫^ıﬁyıÉ…õ£Ù€†Ùœ@È_eß\r Œ(;ó@È›æ¡cç3THú¿úQ-•HYsLü´\\{ª_(ÚÁ$–˚EXß-MºLˇ6qùO⁄Öu∑Ö/™=·V1Ô’™gÑ¯BÔŸ\'1áÁ‹`≠m®k∂üó»“•ôƒvÀ¬†mºU)êÂAÂ∏Ã˝ØıÆı?©D˝ñ∂Ô5±˝ì±ÈWÌ˛⁄˙2¯πÊn¡∏ﬂÑÒKUìÙΩ*∂˝Í¨&Ê,∆ öb\Z·ñ.4!0ÆtrÜ√«+ﬁn]Z–\"MV∫πiî9ûÅÃ˘ƒNŸÔˇ«Úc:‹T=Ã-ﬁÆF\r“åFh€ﬁ≤æ€¿≤ˇ(h¸FΩ√Dòâi‰êæÛå¯˜k:àöäX[Â‹ñLı¶¢’TÒ´È=Z;eS~…˚ﬂPKTÃì§\0\0E\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xmlÌW[o”0~ÁWDy^ótE¢5P\rÅ¥©b„πˆIb%≤Oöïiˇ;∑∂¢ö6nï`}à{|>˚˚ŒâÌ¯úû›H¨¿XÆ’,«a\0äj∆U>?]üè^ÜÅE¢Z¡,\\É\rœ“gßu¬4≠$(‹ &zVF%ñ âINç∂:√’2—Y∆)tMÿç0≥∞@,ì(ÍÎîÛe⁄HÇŒ4y‘ôw\\—IO#Ç†”k^⁄~∂’}¸+)z\\˝÷ZV\ZM¡Zó)Z^I∏\Z¶«ÿœ3å(¬Ã©∑(wÖÃ[gò∫Ù/5[˚∂l”4W∏‘…äàYhΩáQz\Z\ràˆ—˛∑ﬂz‰IÉÚ=oÌn_4†1}M%5˝\n8ZË\Z∞`a4´(⁄£‡Ω¢«éç8µc∆Ó\\∏ºπEd\0Gƒ9·&„ ÿQp˘a◊5z£>∆ütŸ3\",¥°.7ënı>6%∑Ó]óD≠E$‹ÌSˇk)Êoó˚L3n5Ùdø+GèQˆƒ˘g7⁄ú $√bB≤åv⁄Ùñ9¿!@s\Z,uÖ˜™Ã∏ÄÌ}q(•Óå˙tü÷Ù∂Ï|á÷˘øpváöB‚ínâ®∞˘íﬂ=Â˛ü‰Ù/ı\rX‰ƒ0˚Ùûˇ“´,òÉüj%Y•h≥—˜™±ÓÓÎrP¢‡$ÔÆfe~ÂôW§L_N\\UR˚´¸x¸*ûˆÄb1F‘“ª&œ=*”\Z¡’\Z±7Ú\n7FÑ\rÜÄ7Éœã-uŸ’e%Ø[©ôt8îK\"ZØø´πoÓπ_¢iŒ\r4πË˝¬\\/[∑+©ﬁŒúÉƒ\\ïÑ∫äkrÚb⁄Ñ+∏ÇGÍR0ô6ö}∂<yF*Å]‚˚\\F}âmJµÙ;PKmà√ò\0\0Ô\r\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xmlù–=k√0‡Ωø¬à¨∂◊òdÖñ“)–nÈfÈú®XHÁ‡¸˚™-4ô;ÔÒﬁÒ›bß‚1Ô:≤Æ)¿)Øç;v‰≠.7§H(ùñìw–ë$≤w¸5˙\0\r§\".u‰Ñ∂î&u+Sïcóì—G+1èÒH˝8\ZO^Õ“ö±ñ¬Ç‡4Ë2¸Å‰W‹ûÒø®ˆÍª_zÔ/!{Ç˜Â‘b}_sz˘CìQÛ˘bo^~<⁄T¨™´zµ7n^ÜèM;¥Mq≥0‰æü†ê6åY∂zúÕ§ÀLﬂzú^ø$æ\0PKIﬂ«‰\0\0\0j\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xml}í—N√ ÜÔ}äÜ˚∞ŒM“vâö]πƒƒçwÑûmh°p›ﬁ^ä[unÒÆßÁ„„áC1›™&ŸÄu≤’%¢A	h—÷RØJÙºò•î8œuÕõVCâv‡–¥∫(Ña¢µh[÷KpIi«Ñ)—⁄{√0vb\räª,:4ó≠U‹á“Æ∞·‚ÉØ\0_rçx^sœq/LÕ`D{e-•˘¥M‘C\n¥wòfˇ∞J˙ùÅ≥+Õ_¥´‹Y8vrÎ‰@u]óuy‰B~ä_ÁOÒ®©‘˝U	@U±W3aÅ{®ì `ﬂ¡ùó¸Ó~1CUpLRrì“—ÇNX>ftîÚV‡?äﬁ˘˝›⁄jVæÎ§ﬂ◊ÜQı–Îg”pÁÁaäK	ıÌÓ?EÜƒjˇÔﬂ»4O…(F&,§æ\ZG>8bŸ?Øä“∏ÒP«Í¯U_PKtèG\0\0ë\0\0PK\0\0†DØB\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xmlΩî1O√0Ö˜˛ä»+JBI: 1Bá0#c_ãƒ∂|¶¥ˇûsh#®¥∞X≤¸Ó}œÁìãÂfËì5x‘÷îÏ<ÀYFZ•M[≤á˙6ΩbÀjQ‘[òê÷`…∫‹5Á(;f÷Å°ì∆˙A⁄˙ñ;!üE¸\"œ/π¥&Ä	ià¨*Ó	ÁµÇd%|∏îå?zËëgqe…Õ{AdñL8◊k)Â„k£>—“)Vé\ZÏ¥√30˛=È’zµ√)+_e$ˇ74B‘\\å–üÒl”h	SËËÊºïÄH~tÉΩÛlÑÜ†µxÍ·Ù&Î˘>Ñm—Ö—wˇÒÌO`:Ñ6árêpÂ≠CN¿£c¿Ü*®î≤8AÓ¡ƒñ÷ˇbˆ£´øˇãÍ\rPKcÓ§a*\0\0^\0\0PK\0\0\0†DØBË–#Ÿ\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0†DØB˘/0¿≈\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0†DØBv’é≠•\0\0\0–\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0†DØBÃIóä\0\0w\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0†DØBTÃì§\0\0E\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0H\0\0word/styles.xmlPK\0\0\0†DØBmà√ò\0\0Ô\r\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0)\0\0word/document.xmlPK\0\0\0†DØBIﬂ«‰\0\0\0j\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0docProps/app.xmlPK\0\0\0†DØBtèG\0\0ë\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\"\0\0docProps/core.xmlPK\0\0\0†DØBcÓ§a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0®\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0\0\0\0\0',NULL,'docx'),(3,1,0,'Super template',181,'Message-ID: <0502947191438cb9985a0f2997c16951@localhost>\r\nDate: Thu, 22 Jul 2021 16:53:39 +0200\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\"\r\nX-Mailer: Group-Office (6.5.70)\r\n\r\n\r\n--_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nSuper=E2=80=8B 1\r\n\r\n--_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n<html>\r\n<head>\r\n<style type=3D\"text/css\" id=3D\"groupoffice-email-style\">\r\n/*\r\n * Convert transparent color to hex value by given background\r\n */\r\nh6 {\r\n  font-size: 11px;\r\n  line-height: 14px;\r\n  font-weight: bold;\r\n  color: rgba(0, 0, 0, 0.64);\r\n}\r\nh4 {\r\n  font-size: 14px;\r\n  line-height: 21px;\r\n  letter-spacing: 0.4px;\r\n  color: rgba(0, 0, 0, 0.87);\r\n  font-weight: normal;\r\n}\r\nh5 {\r\n  font-size: 12px;\r\n  color: rgba(0, 0, 0, 0.64);\r\n  font-weight: normal;\r\n}\r\nh3 {\r\n  font-size: 16px;\r\n  line-height: 21px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nh2 {\r\n  font-size: 21px;\r\n  line-height: 28px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nh1 {\r\n  font-size: 30px;\r\n  line-height: 35px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nbody, p, span, div {\r\n  font-family: Helvetica, Arial, sans-serif;\r\n  font-size: 14px;\r\n  color: rgba(0, 0, 0, 0.87);\r\n  font-weight: normal;\r\n  line-height: 21px;\r\n}\r\ncode {\r\n  border: 1px solid rgba(0, 0, 0, 0.12);\r\n  background-color: #fafafa;\r\n  padding: 7px;\r\n  margin: 14px 0;\r\n  display: block;\r\n  font-family: \"Courier New\", Courier, monospace;\r\n  color: black;\r\n  border-radius: 3.5px;\r\n}\r\np {\r\n  margin: 0;\r\n}\r\nul {\r\n  display: block;\r\n  list-style-type: disc;\r\n  list-style-position: outside;\r\n  margin: 0;\r\n  padding: 0 0 0 2em;\r\n}\r\nol {\r\n  display: block;\r\n  list-style-type: decimal;\r\n  list-style-position: outside;\r\n  margin: 0;\r\n  padding: 0 0 0 2em;\r\n}\r\nol > ol {\r\n  list-style-type: lower-alpha;\r\n}\r\nol > ol > ol {\r\n  list-style-type: lower-roman;\r\n}\r\n\r\n</style>\r\n</head>\r\n<body>\r\nSuper=E2=80=8B 1</body></html>\r\n\r\n--_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_--\r\n',NULL,''),(4,8,0,'Super template 2',184,'Message-ID: <019d30537f812ba87e4dfa7ce0899bd7@localhost>\r\nDate: Thu, 22 Jul 2021 16:53:45 +0200\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\"\r\nX-Mailer: Group-Office (6.5.70)\r\n\r\n\r\n--_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\nSuper=E2=80=8B\r\n\r\n--_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n<html>\r\n<head>\r\n<style type=3D\"text/css\" id=3D\"groupoffice-email-style\">\r\n/*\r\n * Convert transparent color to hex value by given background\r\n */\r\nh6 {\r\n  font-size: 11px;\r\n  line-height: 14px;\r\n  font-weight: bold;\r\n  color: rgba(0, 0, 0, 0.64);\r\n}\r\nh4 {\r\n  font-size: 14px;\r\n  line-height: 21px;\r\n  letter-spacing: 0.4px;\r\n  color: rgba(0, 0, 0, 0.87);\r\n  font-weight: normal;\r\n}\r\nh5 {\r\n  font-size: 12px;\r\n  color: rgba(0, 0, 0, 0.64);\r\n  font-weight: normal;\r\n}\r\nh3 {\r\n  font-size: 16px;\r\n  line-height: 21px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nh2 {\r\n  font-size: 21px;\r\n  line-height: 28px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nh1 {\r\n  font-size: 30px;\r\n  line-height: 35px;\r\n  font-weight: normal;\r\n  letter-spacing: 0.6px;\r\n  color: black;\r\n}\r\nbody, p, span, div {\r\n  font-family: Helvetica, Arial, sans-serif;\r\n  font-size: 14px;\r\n  color: rgba(0, 0, 0, 0.87);\r\n  font-weight: normal;\r\n  line-height: 21px;\r\n}\r\ncode {\r\n  border: 1px solid rgba(0, 0, 0, 0.12);\r\n  background-color: #fafafa;\r\n  padding: 7px;\r\n  margin: 14px 0;\r\n  display: block;\r\n  font-family: \"Courier New\", Courier, monospace;\r\n  color: black;\r\n  border-radius: 3.5px;\r\n}\r\np {\r\n  margin: 0;\r\n}\r\nul {\r\n  display: block;\r\n  list-style-type: disc;\r\n  list-style-position: outside;\r\n  margin: 0;\r\n  padding: 0 0 0 2em;\r\n}\r\nol {\r\n  display: block;\r\n  list-style-type: decimal;\r\n  list-style-position: outside;\r\n  margin: 0;\r\n  padding: 0 0 0 2em;\r\n}\r\nol > ol {\r\n  list-style-type: lower-alpha;\r\n}\r\nol > ol > ol {\r\n  list-style-type: lower-roman;\r\n}\r\n\r\n</style>\r\n</head>\r\n<body>\r\nSuper=E2=80=8B\r\n</body></html>\r\n\r\n--_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_--\r\n',NULL,'');
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
  CONSTRAINT `fk_log_entry_core_acl1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_entity1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `fk_log_entry_core_user` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=1559 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `history_log_entry`
--

LOCK TABLES `history_log_entry` WRITE;
/*!40000 ALTER TABLE `history_log_entry` DISABLE KEYS */;
INSERT INTO `history_log_entry` VALUES (1,1,'notes','{\"id\":8,\"name\":\"notes\",\"package\":\"community\",\"version\":57,\"sort_order\":107,\"checkDepencencies\":false}','2021-06-28 08:53:01',1,17,1,13,8,'172.18.0.1'),(2,1,'Shared','{\"id\":65,\"name\":\"Shared\",\"setAcl\":{\"3\":40}}','2021-06-28 08:53:01',1,18,1,35,65,'172.18.0.1'),(3,2,'System Administrator','{\"lastLogin\":[\"2021-06-28T08:53:59+00:00\",null],\"loginCount\":[1,0],\"language\":[\"en_uk\",\"en\"],\"addressBookSettings\":[{\"defaultAddressBookId\":1,\"sortBy\":\"name\",\"userId\":1},null],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"notesSettings\":[{\"defaultNoteBookId\":65,\"userId\":1},null]}','2021-06-28 08:53:59',1,5,0,21,1,'172.18.0.1'),(4,4,'admin [172.18.0.1]',NULL,'2021-06-28 08:53:59',1,5,0,21,1,'172.18.0.1'),(5,1,'Users','{\"id\":2,\"name\":\"Users\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-06-28 08:54:00',1,31,1,23,2,'172.18.0.1'),(6,1,'calendar','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"calendar\",\"parent_id\":0,\"mtime\":1624870441,\"ctime\":1624870441,\"id\":1}','2021-06-28 08:54:01',1,24,1,39,1,'172.18.0.1'),(7,1,'System Administrator','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"System Administrator\",\"parent_id\":1,\"mtime\":1624870442,\"ctime\":1624870442,\"id\":2}','2021-06-28 08:54:02',1,24,1,39,2,'172.18.0.1'),(8,2,'System Administrator','{\"acl_id\":[0,39]}','2021-06-28 08:54:02',1,39,1,39,2,'172.18.0.1'),(9,1,'System Administrator','{\"group_id\":1,\"user_id\":1,\"acl_id\":39,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":2,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"System Administrator\",\"id\":1}','2021-06-28 08:54:05',1,39,1,36,1,'172.18.0.1'),(10,1,'assistant','{\"name\":\"assistant\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:54:54\",\"aclId\":40,\"version\":0,\"id\":19}','2021-06-28 08:54:54',1,40,1,13,19,'172.18.0.1'),(11,1,'billing','{\"name\":\"billing\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:54:54\",\"aclId\":41,\"version\":319,\"id\":20}','2021-06-28 08:54:54',1,41,1,13,20,'172.18.0.1'),(12,1,'billing','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"billing\",\"parent_id\":0,\"mtime\":1624870494,\"ctime\":1624870494,\"id\":3}','2021-06-28 08:54:54',1,24,1,39,3,'172.18.0.1'),(13,1,'Quotes','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Quotes\",\"parent_id\":3,\"mtime\":1624870494,\"ctime\":1624870494,\"id\":4}','2021-06-28 08:54:54',1,24,1,39,4,'172.18.0.1'),(14,2,'Quotes','{\"acl_id\":[0,42]}','2021-06-28 08:54:54',1,42,1,39,4,'172.18.0.1'),(15,1,'Quotes','{\"user_id\":1,\"acl_id\":42,\"order_id_length\":6,\"next_id\":0,\"default_vat\":\"19\",\"call_after_days\":3,\"is_purchase_orders_book\":0,\"backorder_status_id\":0,\"delivered_status_id\":0,\"reversal_status_id\":0,\"addressbook_id\":0,\"files_folder_id\":4,\"allow_delete\":0,\"import_status_id\":0,\"auto_paid_status\":0,\"import_notify_customer\":0,\"import_duplicate_to_book\":0,\"import_duplicate_status_id\":0,\"show_sales_agents\":0,\"default_due_days\":14,\"currency\":\"\\u20ac\",\"name\":\"Quotes\",\"order_id_prefix\":\"Q%y\",\"id\":1}','2021-06-28 08:54:54',1,42,1,43,1,'172.18.0.1'),(16,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":1,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"00CCFF\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":43,\"email_bcc\":\"\",\"id\":1}','2021-06-28 08:54:54',1,43,1,44,1,'172.18.0.1'),(17,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":1,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"2AD56F\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":44,\"email_bcc\":\"\",\"id\":2}','2021-06-28 08:54:54',1,44,1,44,2,'172.18.0.1'),(18,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":1,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF0000\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":45,\"email_bcc\":\"\",\"id\":3}','2021-06-28 08:54:55',1,45,1,44,3,'172.18.0.1'),(19,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":1,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF9900\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":46,\"email_bcc\":\"\",\"id\":4}','2021-06-28 08:54:55',1,46,1,44,4,'172.18.0.1'),(20,1,'Orders','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Orders\",\"parent_id\":3,\"mtime\":1624870495,\"ctime\":1624870495,\"id\":5}','2021-06-28 08:54:55',1,24,1,39,5,'172.18.0.1'),(21,2,'Orders','{\"acl_id\":[0,47]}','2021-06-28 08:54:55',1,47,1,39,5,'172.18.0.1'),(22,1,'Orders','{\"user_id\":1,\"acl_id\":47,\"order_id_length\":6,\"next_id\":0,\"default_vat\":\"19\",\"call_after_days\":0,\"is_purchase_orders_book\":0,\"backorder_status_id\":0,\"delivered_status_id\":0,\"reversal_status_id\":0,\"addressbook_id\":0,\"files_folder_id\":5,\"allow_delete\":0,\"import_status_id\":0,\"auto_paid_status\":0,\"import_notify_customer\":0,\"import_duplicate_to_book\":0,\"import_duplicate_status_id\":0,\"show_sales_agents\":0,\"default_due_days\":14,\"currency\":\"\\u20ac\",\"name\":\"Orders\",\"order_id_prefix\":\"O%y\",\"id\":2}','2021-06-28 08:54:55',1,47,1,43,2,'172.18.0.1'),(23,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":2,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF9900\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":48,\"email_bcc\":\"\",\"id\":5}','2021-06-28 08:54:55',1,48,1,44,5,'172.18.0.1'),(24,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":2,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF0000\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":49,\"email_bcc\":\"\",\"id\":6}','2021-06-28 08:54:55',1,49,1,44,6,'172.18.0.1'),(25,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":2,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"2AD56F\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":50,\"email_bcc\":\"\",\"id\":7}','2021-06-28 08:54:55',1,50,1,44,7,'172.18.0.1'),(26,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":2,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"00CCFF\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":51,\"email_bcc\":\"\",\"id\":8}','2021-06-28 08:54:55',1,51,1,44,8,'172.18.0.1'),(27,1,'Invoices','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Invoices\",\"parent_id\":3,\"mtime\":1624870495,\"ctime\":1624870495,\"id\":6}','2021-06-28 08:54:55',1,24,1,39,6,'172.18.0.1'),(28,2,'Invoices','{\"acl_id\":[0,52]}','2021-06-28 08:54:55',1,52,1,39,6,'172.18.0.1'),(29,1,'Invoices','{\"user_id\":1,\"acl_id\":52,\"order_id_length\":6,\"next_id\":0,\"default_vat\":\"19\",\"call_after_days\":0,\"is_purchase_orders_book\":0,\"backorder_status_id\":0,\"delivered_status_id\":0,\"reversal_status_id\":0,\"addressbook_id\":0,\"files_folder_id\":6,\"allow_delete\":0,\"import_status_id\":0,\"auto_paid_status\":0,\"import_notify_customer\":0,\"import_duplicate_to_book\":0,\"import_duplicate_status_id\":0,\"show_sales_agents\":0,\"default_due_days\":14,\"currency\":\"\\u20ac\",\"name\":\"Invoices\",\"order_id_prefix\":\"I%y\",\"id\":3}','2021-06-28 08:54:55',1,52,1,43,3,'172.18.0.1'),(30,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":3,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF9900\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":53,\"email_bcc\":\"\",\"id\":9}','2021-06-28 08:54:55',1,53,1,44,9,'172.18.0.1'),(31,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":3,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"FF0000\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":54,\"email_bcc\":\"\",\"id\":10}','2021-06-28 08:54:55',1,54,1,44,10,'172.18.0.1'),(32,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":3,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"2AD56F\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":55,\"email_bcc\":\"\",\"id\":11}','2021-06-28 08:54:55',1,55,1,44,11,'172.18.0.1'),(33,1,'GO\\Billing\\Model\\OrderStatus','{\"book_id\":3,\"max_age\":0,\"payment_required\":0,\"remove_from_stock\":0,\"read_only\":0,\"color\":\"00CCFF\",\"required_status_id\":0,\"apply_extra_cost\":0,\"email_owner\":0,\"ask_to_notify_customer\":1,\"acl_id\":56,\"email_bcc\":\"\",\"id\":12}','2021-06-28 08:54:55',1,56,1,44,12,'172.18.0.1'),(34,1,'product_images','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"product_images\",\"parent_id\":3,\"mtime\":1624870495,\"ctime\":1624870495,\"id\":7}','2021-06-28 08:54:55',1,24,1,39,7,'172.18.0.1'),(35,2,'product_images','{\"acl_id\":[0,41]}','2021-06-28 08:54:55',1,41,1,39,7,'172.18.0.1'),(36,2,'calendar','{\"modifiedAt\":[\"2021-06-28 08:53:07\",\"2021-06-28 08:54:55\"]}','2021-06-28 08:54:55',1,19,1,13,9,'172.18.0.1'),(37,2,'cron','{\"modifiedAt\":[\"2021-06-28 08:53:07\",\"2021-06-28 08:54:55\"]}','2021-06-28 08:54:55',1,21,1,13,10,'172.18.0.1'),(38,2,'demodata','{\"modifiedAt\":[\"2021-06-28 08:53:07\",\"2021-06-28 08:54:55\"]}','2021-06-28 08:54:55',1,22,1,13,11,'172.18.0.1'),(39,1,'documenttemplates','{\"name\":\"documenttemplates\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:54:55\",\"aclId\":57,\"version\":0,\"id\":21}','2021-06-28 08:54:55',1,57,1,13,21,'172.18.0.1'),(40,1,'Letter','','2021-06-28 08:54:55',1,58,1,22,2,'172.18.0.1'),(41,2,'email','{\"modifiedAt\":[\"2021-06-28 08:53:07\",\"2021-06-28 08:54:55\"]}','2021-06-28 08:54:55',1,23,1,13,12,'172.18.0.1'),(42,2,'files','{\"modifiedAt\":[\"2021-06-28 08:53:08\",\"2021-06-28 08:54:55\"]}','2021-06-28 08:54:55',1,24,1,13,13,'172.18.0.1'),(43,1,'projects2','{\"name\":\"projects2\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:54:55\",\"aclId\":59,\"version\":398,\"id\":22}','2021-06-28 08:54:55',1,59,1,13,22,'172.18.0.1'),(44,2,'assistant','{\"modifiedAt\":[\"2021-06-28 08:54:54\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,40,1,13,19,'172.18.0.1'),(45,2,'billing','{\"modifiedAt\":[\"2021-06-28 08:54:54\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,41,1,13,20,'172.18.0.1'),(46,2,'calendar','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,19,1,13,9,'172.18.0.1'),(47,2,'cron','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,21,1,13,10,'172.18.0.1'),(48,2,'demodata','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,22,1,13,11,'172.18.0.1'),(49,2,'documenttemplates','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,57,1,13,21,'172.18.0.1'),(50,2,'email','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,23,1,13,12,'172.18.0.1'),(51,2,'files','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,24,1,13,13,'172.18.0.1'),(52,1,'timeregistration2','{\"name\":\"timeregistration2\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:55:27\",\"aclId\":60,\"version\":1,\"id\":23}','2021-06-28 08:55:27',1,60,1,13,23,'172.18.0.1'),(53,1,'hoursapproval2','{\"name\":\"hoursapproval2\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:55:27\",\"aclId\":61,\"version\":0,\"id\":24}','2021-06-28 08:55:27',1,61,1,13,24,'172.18.0.1'),(54,1,'leavedays','{\"name\":\"leavedays\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:55:27\",\"aclId\":62,\"version\":31,\"id\":25}','2021-06-28 08:55:27',1,62,1,13,25,'172.18.0.1'),(55,2,'projects2','{\"modifiedAt\":[\"2021-06-28 08:54:55\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,59,1,13,22,'172.18.0.1'),(56,1,'savemailas','{\"name\":\"savemailas\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:55:27\",\"aclId\":63,\"version\":12,\"id\":26}','2021-06-28 08:55:27',1,63,1,13,26,'172.18.0.1'),(57,2,'sieve','{\"modifiedAt\":[\"2021-06-28 08:53:14\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,25,1,13,14,'172.18.0.1'),(58,2,'summary','{\"modifiedAt\":[\"2021-06-28 08:53:14\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,26,1,13,15,'172.18.0.1'),(59,2,'sync','{\"modifiedAt\":[\"2021-06-28 08:53:14\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,27,1,13,16,'172.18.0.1'),(60,2,'tasks','{\"modifiedAt\":[\"2021-06-28 08:53:14\",\"2021-06-28 08:55:27\"]}','2021-06-28 08:55:27',1,28,1,13,17,'172.18.0.1'),(61,1,'tickets','{\"name\":\"tickets\",\"sort_order\":108,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:55:27\",\"aclId\":64,\"version\":163,\"id\":27}','2021-06-28 08:55:27',1,64,1,13,27,'172.18.0.1'),(62,1,'tickets','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"tickets\",\"parent_id\":0,\"mtime\":1624870528,\"ctime\":1624870528,\"id\":8}','2021-06-28 08:55:28',1,24,1,39,8,'172.18.0.1'),(63,1,'0-IT','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"0-IT\",\"parent_id\":8,\"mtime\":1624870528,\"ctime\":1624870528,\"id\":9}','2021-06-28 08:55:28',1,24,1,39,9,'172.18.0.1'),(64,2,'0-IT','{\"acl_id\":[0,65]}','2021-06-28 08:55:28',1,65,1,39,9,'172.18.0.1'),(65,1,'IT','{\"show_from_others\":0,\"files_folder_id\":9,\"email_to_agent\":0,\"custom_sender_field\":0,\"publish_on_site\":1,\"email_account_id\":0,\"enable_templates\":0,\"new_ticket\":0,\"assigned_to\":0,\"notify_agent\":0,\"search_cache_acl_id\":0,\"user_id\":1,\"name\":\"IT\",\"acl_id\":65,\"id\":1,\"type_group_id\":0}','2021-06-28 08:55:28',1,65,1,47,1,'172.18.0.1'),(66,1,'0-Sales','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"0-Sales\",\"parent_id\":8,\"mtime\":1624870528,\"ctime\":1624870528,\"id\":10}','2021-06-28 08:55:28',1,24,1,39,10,'172.18.0.1'),(67,2,'0-Sales','{\"acl_id\":[0,66]}','2021-06-28 08:55:28',1,66,1,39,10,'172.18.0.1'),(68,1,'Sales','{\"show_from_others\":0,\"files_folder_id\":10,\"email_to_agent\":0,\"custom_sender_field\":0,\"publish_on_site\":0,\"email_account_id\":0,\"enable_templates\":0,\"new_ticket\":0,\"assigned_to\":0,\"notify_agent\":0,\"search_cache_acl_id\":0,\"user_id\":1,\"name\":\"Sales\",\"acl_id\":66,\"id\":2,\"type_group_id\":0}','2021-06-28 08:55:28',1,66,1,47,2,'172.18.0.1'),(69,2,'timeregistration2','{\"modifiedAt\":[\"2021-06-28 08:55:27\",\"2021-06-28 08:55:28\"]}','2021-06-28 08:55:28',1,60,1,13,23,'172.18.0.1'),(70,2,'tools','{\"modifiedAt\":[\"2021-06-28 08:53:15\",\"2021-06-28 08:55:28\"]}','2021-06-28 08:55:28',1,29,1,13,18,'172.18.0.1'),(71,1,'newsletters','{\"id\":28,\"name\":\"newsletters\",\"package\":\"business\",\"version\":2,\"sort_order\":108,\"checkDepencencies\":false}','2021-06-28 08:55:28',1,67,1,13,28,'172.18.0.1'),(72,1,'Default','{\"id\":1,\"moduleId\":28,\"name\":\"Default\",\"subject\":\"Hi {{contact.firstName}}\",\"body\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if] {{contact.lastName}},<div><div><br><\\/div><div><br><\\/div><div>Best regards,<\\/div><div><br><\\/div><div>{{creator.displayName}}<\\/div><\\/div><div>{{creator.profile.organizations[0].name}}<\\/div><div><br \\/><\\/div><div><a href=\\\"{{unsubscribeUrl}}\\\">unsubscribe<\\/a><\\/div>\"}','2021-06-28 08:55:28',1,68,1,7,1,'172.18.0.1'),(73,1,'onlyoffice','{\"id\":29,\"name\":\"onlyoffice\",\"package\":\"business\",\"version\":1,\"sort_order\":109,\"checkDepencencies\":false}','2021-06-28 08:55:28',1,69,1,13,29,'172.18.0.1'),(74,2,'onlyoffice','{\"setAcl\":[{\"3\":50},null]}','2021-06-28 08:55:28',1,69,1,13,29,'172.18.0.1'),(75,1,'wopi','{\"id\":30,\"name\":\"wopi\",\"package\":\"business\",\"version\":7,\"sort_order\":110,\"checkDepencencies\":false}','2021-06-28 08:55:28',1,70,1,13,30,'172.18.0.1'),(76,2,'wopi','{\"setAcl\":[{\"3\":10},null]}','2021-06-28 08:55:29',1,70,1,13,30,'172.18.0.1'),(77,1,'Customers','{\"id\":3,\"name\":\"Customers\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\",\"setAcl\":{\"3\":30}}','2021-06-28 08:56:38',1,76,1,23,3,'172.18.0.1'),(78,1,'elmer','{\"id\":5,\"name\":\"elmer\",\"isUserGroupFor\":2,\"users\":[2]}','2021-06-28 08:56:40',1,77,1,11,5,'172.18.0.1'),(79,1,'Elmer Fudd','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Elmer Fudd\",\"parent_id\":1,\"mtime\":1624870600,\"ctime\":1624870600,\"id\":11}','2021-06-28 08:56:40',1,24,1,39,11,'172.18.0.1'),(80,2,'Elmer Fudd','{\"acl_id\":[0,78]}','2021-06-28 08:56:40',1,78,1,39,11,'172.18.0.1'),(81,1,'Elmer Fudd','{\"group_id\":1,\"user_id\":2,\"acl_id\":78,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":11,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Elmer Fudd\",\"id\":2}','2021-06-28 08:56:40',1,78,1,36,2,'172.18.0.1'),(82,1,'tasks','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"tasks\",\"parent_id\":0,\"mtime\":1624870600,\"ctime\":1624870600,\"id\":12}','2021-06-28 08:56:40',1,24,1,39,12,'172.18.0.1'),(83,1,'Elmer Fudd','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Elmer Fudd\",\"parent_id\":12,\"mtime\":1624870600,\"ctime\":1624870600,\"id\":13}','2021-06-28 08:56:40',1,24,1,39,13,'172.18.0.1'),(84,2,'Elmer Fudd','{\"acl_id\":[0,79]}','2021-06-28 08:56:40',1,79,1,39,13,'172.18.0.1'),(85,1,'Elmer Fudd','{\"files_folder_id\":13,\"version\":1,\"user_id\":2,\"name\":\"Elmer Fudd\",\"acl_id\":79,\"id\":1}','2021-06-28 08:56:40',1,79,1,51,1,'172.18.0.1'),(86,1,'Elmer Fudd','{\"id\":4,\"name\":\"Elmer Fudd\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-06-28 08:56:40',1,80,1,23,4,'172.18.0.1'),(87,1,'Elmer Fudd','{\"id\":66,\"name\":\"Elmer Fudd\"}','2021-06-28 08:56:40',1,81,1,35,66,'172.18.0.1'),(88,1,'Elmer Fudd','{\"id\":2,\"username\":\"elmer\",\"displayName\":\"Elmer Fudd\",\"avatarId\":\"0ec2f1f4f9fb41e8013fcc834991be30a8260750\",\"email\":\"elmer@acmerpp.demo\",\"recoveryEmail\":\"elmer@acmerpp.demo\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/elmer\",\"password\":\"$2y$10$ZL0hKQfI0fX\\/ePRz\\/Oi13.Dz5ZnM.bRurhZmIk5e7y5D\\/SgVIrkmS\",\"groups\":[3,2,5],\"addressBookSettings\":{\"defaultAddressBookId\":4,\"sortBy\":\"name\",\"userId\":2},\"employee\":{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":66,\"userId\":2},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":2},\"emailSettings\":{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":2,\"account_id\":0,\"noteBooks\":[{\"userId\":2,\"noteBookId\":66,\"isDefault\":true}],\"addressBooks\":[{\"userId\":2,\"addressBookId\":4,\"isDefault\":true}]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":2},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":2},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false}}','2021-06-28 08:56:40',1,5,0,21,2,'172.18.0.1'),(89,1,'demo','{\"id\":6,\"name\":\"demo\",\"isUserGroupFor\":3,\"users\":[3]}','2021-06-28 08:56:40',1,82,1,11,6,'172.18.0.1'),(90,1,'Demo User','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Demo User\",\"parent_id\":1,\"mtime\":1624870600,\"ctime\":1624870600,\"id\":14}','2021-06-28 08:56:40',1,24,1,39,14,'172.18.0.1'),(91,2,'Demo User','{\"acl_id\":[0,83]}','2021-06-28 08:56:40',1,83,1,39,14,'172.18.0.1'),(92,1,'Demo User','{\"group_id\":1,\"user_id\":3,\"acl_id\":83,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":14,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Demo User\",\"id\":3}','2021-06-28 08:56:40',1,83,1,36,3,'172.18.0.1'),(93,1,'Demo User','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Demo User\",\"parent_id\":12,\"mtime\":1624870600,\"ctime\":1624870600,\"id\":15}','2021-06-28 08:56:41',1,24,1,39,15,'172.18.0.1'),(94,2,'Demo User','{\"acl_id\":[0,84]}','2021-06-28 08:56:41',1,84,1,39,15,'172.18.0.1'),(95,1,'Demo User','{\"files_folder_id\":15,\"version\":1,\"user_id\":3,\"name\":\"Demo User\",\"acl_id\":84,\"id\":2}','2021-06-28 08:56:41',1,84,1,51,2,'172.18.0.1'),(96,1,'Demo User','{\"id\":5,\"name\":\"Demo User\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-06-28 08:56:41',1,85,1,23,5,'172.18.0.1'),(97,1,'Demo User','{\"id\":67,\"name\":\"Demo User\"}','2021-06-28 08:56:41',1,86,1,35,67,'172.18.0.1'),(98,1,'Demo User','{\"id\":3,\"username\":\"demo\",\"displayName\":\"Demo User\",\"avatarId\":\"a2b13489e9762bf7d7dfd63d72d45f0f47411c30\",\"email\":\"demo@acmerpp.demo\",\"recoveryEmail\":\"demo@acmerpp.demo\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/demo\",\"password\":\"$2y$10$uQ.OZninvckLAhcyytmLquRWDh5LXrnQcvP9kIMXGPYvW8dKFxCji\",\"groups\":[3,2,6],\"addressBookSettings\":{\"defaultAddressBookId\":5,\"sortBy\":\"name\",\"userId\":3},\"employee\":{\"id\":3,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":67,\"userId\":3},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":3},\"emailSettings\":{\"id\":3,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":3,\"account_id\":0,\"noteBooks\":[{\"userId\":3,\"noteBookId\":67,\"isDefault\":true}],\"addressBooks\":[{\"userId\":3,\"addressBookId\":5,\"isDefault\":true}]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":3},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":3},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false}}','2021-06-28 08:56:41',1,5,0,21,3,'172.18.0.1'),(99,1,'linda','{\"id\":7,\"name\":\"linda\",\"isUserGroupFor\":4,\"users\":[4]}','2021-06-28 08:56:41',1,87,1,11,7,'172.18.0.1'),(100,1,'Linda Smith','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Linda Smith\",\"parent_id\":1,\"mtime\":1624870601,\"ctime\":1624870601,\"id\":16}','2021-06-28 08:56:41',1,24,1,39,16,'172.18.0.1'),(101,2,'Linda Smith','{\"acl_id\":[0,88]}','2021-06-28 08:56:41',1,88,1,39,16,'172.18.0.1'),(102,1,'Linda Smith','{\"group_id\":1,\"user_id\":4,\"acl_id\":88,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":16,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Linda Smith\",\"id\":4}','2021-06-28 08:56:41',1,88,1,36,4,'172.18.0.1'),(103,1,'Linda Smith','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Linda Smith\",\"parent_id\":12,\"mtime\":1624870601,\"ctime\":1624870601,\"id\":17}','2021-06-28 08:56:41',1,24,1,39,17,'172.18.0.1'),(104,2,'Linda Smith','{\"acl_id\":[0,89]}','2021-06-28 08:56:41',1,89,1,39,17,'172.18.0.1'),(105,1,'Linda Smith','{\"files_folder_id\":17,\"version\":1,\"user_id\":4,\"name\":\"Linda Smith\",\"acl_id\":89,\"id\":3}','2021-06-28 08:56:41',1,89,1,51,3,'172.18.0.1'),(106,1,'Linda Smith','{\"id\":6,\"name\":\"Linda Smith\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-06-28 08:56:41',1,90,1,23,6,'172.18.0.1'),(107,1,'Linda Smith','{\"id\":68,\"name\":\"Linda Smith\"}','2021-06-28 08:56:41',1,91,1,35,68,'172.18.0.1'),(108,1,'Linda Smith','{\"id\":4,\"username\":\"linda\",\"displayName\":\"Linda Smith\",\"avatarId\":\"c363a83f50fe2fbe94deff31afee36d8d7923e17\",\"email\":\"linda@acmerpp.linda\",\"recoveryEmail\":\"linda@acmerpp.linda\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/linda\",\"password\":\"$2y$10$lblVdE6RLvGDqM6BqKgnC.3lTaQQ2wpbzZpzgu5m7pn.T9XfOoDIO\",\"groups\":[3,2,7],\"addressBookSettings\":{\"defaultAddressBookId\":6,\"sortBy\":\"name\",\"userId\":4},\"employee\":{\"id\":4,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":68,\"userId\":4},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":4},\"emailSettings\":{\"id\":4,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":4,\"account_id\":0,\"noteBooks\":[{\"userId\":4,\"noteBookId\":68,\"isDefault\":true}],\"addressBooks\":[{\"userId\":4,\"addressBookId\":6,\"isDefault\":true}]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":4},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":4},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false}}','2021-06-28 08:56:41',1,5,0,21,4,'172.18.0.1'),(109,1,'Smith Inc.','{\"id\":1,\"addressBookId\":3,\"lastName\":\"Smith Inc.\",\"notes\":\"Just a demo company\",\"isOrganization\":true,\"name\":\"Smith Inc.\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"uid\":\"1@host.docker.internal:8080\",\"uri\":\"1@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-06-28 08:56:41',1,76,0,24,1,'172.18.0.1'),(110,1,NULL,'{\"id\":1,\"fromEntityTypeId\":24,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,12,1,'172.18.0.1'),(111,1,'John Smith','{\"id\":2,\"addressBookId\":3,\"goUserId\":3,\"firstName\":\"John\",\"lastName\":\"Smith\",\"notes\":\"Just a demo john\",\"name\":\"John Smith\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"photoBlobId\":\"a2b13489e9762bf7d7dfd63d72d45f0f47411c30\",\"jobTitle\":\"CEO\",\"uid\":\"2@host.docker.internal:8080\",\"uri\":\"2@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"john@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-06-28 08:56:41',1,76,0,24,2,'172.18.0.1'),(112,1,'John Smith','{\"id\":1,\"date\":\"2021-06-28T10:56:41+02:00\",\"entityId\":2,\"entityTypeId\":24,\"text\":\"Wile E. Coyote (also known simply as \\\"The Coyote\\\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.\",\"entity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,31,1,'172.18.0.1'),(113,1,'John Smith','{\"id\":2,\"date\":\"2021-06-28T10:56:41+02:00\",\"entityId\":2,\"entityTypeId\":24,\"text\":\"In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.\",\"entity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,31,2,'172.18.0.1'),(114,1,'ACME Corporation','{\"id\":3,\"addressBookId\":3,\"lastName\":\"ACME Corporation\",\"notes\":\"Just a demo acme\",\"isOrganization\":true,\"name\":\"ACME Corporation\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"uid\":\"3@host.docker.internal:8080\",\"uri\":\"3@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@acme.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.acme.demo\"}]}','2021-06-28 08:56:41',1,76,0,24,3,'172.18.0.1'),(115,1,NULL,'{\"id\":3,\"fromEntityTypeId\":24,\"fromId\":4,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,12,3,'172.18.0.1'),(116,1,'Wile E. Coyote','{\"id\":4,\"addressBookId\":3,\"firstName\":\"Wile\",\"middleName\":\"E.\",\"lastName\":\"Coyote\",\"notes\":\"Just a demo wile\",\"name\":\"Wile E. Coyote\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"photoBlobId\":\"0ec2f1f4f9fb41e8013fcc834991be30a8260750\",\"jobTitle\":\"CEO\",\"uid\":\"4@host.docker.internal:8080\",\"uri\":\"4@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"wile@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-06-28 08:56:41',1,76,0,24,4,'172.18.0.1'),(117,1,'Wile E. Coyote','{\"id\":3,\"date\":\"2021-06-28T10:56:41+02:00\",\"entityId\":4,\"entityTypeId\":24,\"text\":\"Wile E. Coyote (also known simply as \\\"The Coyote\\\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.\",\"entity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,31,3,'172.18.0.1'),(118,1,'Wile E. Coyote','{\"id\":4,\"date\":\"2021-06-28T10:56:41+02:00\",\"entityId\":4,\"entityTypeId\":24,\"text\":\"In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.\",\"entity\":\"Contact\"}','2021-06-28 08:56:41',1,76,0,31,4,'172.18.0.1'),(119,1,'Project meeting','{\"uuid\":\"b958dfa0-6144-57fe-8810-7d4dd68e01b3\",\"user_id\":3,\"start_time\":1624953600,\"end_time\":1624957200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":1}','2021-06-28 08:56:41',1,83,0,37,1,'172.18.0.1'),(120,1,'Project meeting','{\"uuid\":\"b958dfa0-6144-57fe-8810-7d4dd68e01b3\",\"user_id\":4,\"start_time\":1624953600,\"end_time\":1624957200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":2}','2021-06-28 08:56:41',1,88,0,37,2,'172.18.0.1'),(121,1,NULL,'{\"id\":5,\"fromEntityTypeId\":37,\"fromId\":1,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,5,'172.18.0.1'),(122,1,NULL,'{\"id\":7,\"fromEntityTypeId\":37,\"fromId\":1,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,7,'172.18.0.1'),(123,1,'Meet Wile','{\"uuid\":\"977ba999-a93c-5444-be0b-314d02bafe13\",\"user_id\":3,\"start_time\":1624960800,\"end_time\":1624964400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"Meet Wile\",\"files_folder_id\":0,\"id\":3}','2021-06-28 08:56:41',1,83,0,37,3,'172.18.0.1'),(124,1,'Meet Wile','{\"uuid\":\"977ba999-a93c-5444-be0b-314d02bafe13\",\"user_id\":4,\"start_time\":1624960800,\"end_time\":1624964400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"Meet Wile\",\"files_folder_id\":0,\"id\":4}','2021-06-28 08:56:41',1,88,0,37,4,'172.18.0.1'),(125,1,NULL,'{\"id\":9,\"fromEntityTypeId\":37,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,9,'172.18.0.1'),(126,1,NULL,'{\"id\":11,\"fromEntityTypeId\":37,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,11,'172.18.0.1'),(127,1,'MT Meeting','{\"uuid\":\"c37e1cc5-85e2-52a0-b0cc-e249878b3146\",\"user_id\":3,\"start_time\":1624968000,\"end_time\":1624971600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":5}','2021-06-28 08:56:41',1,83,0,37,5,'172.18.0.1'),(128,1,'MT Meeting','{\"uuid\":\"c37e1cc5-85e2-52a0-b0cc-e249878b3146\",\"user_id\":4,\"start_time\":1624968000,\"end_time\":1624971600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":6}','2021-06-28 08:56:41',1,88,0,37,6,'172.18.0.1'),(129,1,NULL,'{\"id\":13,\"fromEntityTypeId\":37,\"fromId\":5,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,13,'172.18.0.1'),(130,1,NULL,'{\"id\":15,\"fromEntityTypeId\":37,\"fromId\":5,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,83,0,12,15,'172.18.0.1'),(131,1,'Project meeting','{\"uuid\":\"0ccbec67-03f0-527d-94e7-b63fb03bd62c\",\"user_id\":4,\"start_time\":1625043600,\"end_time\":1625047200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":7}','2021-06-28 08:56:41',1,88,0,37,7,'172.18.0.1'),(132,1,NULL,'{\"id\":17,\"fromEntityTypeId\":37,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,88,0,12,17,'172.18.0.1'),(133,1,NULL,'{\"id\":19,\"fromEntityTypeId\":37,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:41',1,88,0,12,19,'172.18.0.1'),(134,1,'Meet John','{\"uuid\":\"82992635-4cd9-576e-858d-858c44436a93\",\"user_id\":4,\"start_time\":1625050800,\"end_time\":1625054400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870601,\"mtime\":1624870601,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Meet John\",\"files_folder_id\":0,\"id\":8}','2021-06-28 08:56:42',1,88,0,37,8,'172.18.0.1'),(135,1,NULL,'{\"id\":21,\"fromEntityTypeId\":37,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,21,'172.18.0.1'),(136,1,NULL,'{\"id\":23,\"fromEntityTypeId\":37,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,23,'172.18.0.1'),(137,1,'MT Meeting','{\"uuid\":\"5aec17d5-0553-5674-9bd4-9ecfc7494992\",\"user_id\":4,\"start_time\":1625061600,\"end_time\":1625065200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624870602,\"mtime\":1624870602,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":9}','2021-06-28 08:56:42',1,88,0,37,9,'172.18.0.1'),(138,1,NULL,'{\"id\":25,\"fromEntityTypeId\":37,\"fromId\":9,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,25,'172.18.0.1'),(139,1,NULL,'{\"id\":27,\"fromEntityTypeId\":37,\"fromId\":9,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,27,'172.18.0.1'),(140,1,'Rocket testing','{\"uuid\":\"ea3c1220-96e9-563f-a875-c9c3127408f6\",\"user_id\":4,\"start_time\":1624946400,\"end_time\":1624950000,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624870602,\"mtime\":1624870602,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Rocket testing\",\"files_folder_id\":0,\"id\":10}','2021-06-28 08:56:42',1,88,0,37,10,'172.18.0.1'),(141,1,NULL,'{\"id\":29,\"fromEntityTypeId\":37,\"fromId\":10,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,29,'172.18.0.1'),(142,1,NULL,'{\"id\":31,\"fromEntityTypeId\":37,\"fromId\":10,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,31,'172.18.0.1'),(143,1,'Blast impact test','{\"uuid\":\"ac6d7c0e-cca8-5645-ba00-f4c569af4750\",\"user_id\":4,\"start_time\":1624971600,\"end_time\":1624975200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624870602,\"mtime\":1624870602,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Blast impact test\",\"files_folder_id\":0,\"id\":11}','2021-06-28 08:56:42',1,88,0,37,11,'172.18.0.1'),(144,1,NULL,'{\"id\":33,\"fromEntityTypeId\":37,\"fromId\":11,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,33,'172.18.0.1'),(145,1,NULL,'{\"id\":35,\"fromEntityTypeId\":37,\"fromId\":11,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,35,'172.18.0.1'),(146,1,'Test range extender','{\"uuid\":\"bc836fda-554b-5023-90c3-2fc316c39e1c\",\"user_id\":4,\"start_time\":1624986000,\"end_time\":1624989600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624870602,\"mtime\":1624870602,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Test range extender\",\"files_folder_id\":0,\"id\":12}','2021-06-28 08:56:42',1,88,0,37,12,'172.18.0.1'),(147,1,NULL,'{\"id\":37,\"fromEntityTypeId\":37,\"fromId\":12,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,37,'172.18.0.1'),(148,1,NULL,'{\"id\":39,\"fromEntityTypeId\":37,\"fromId\":12,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 08:56:42',1,88,0,12,39,'172.18.0.1'),(149,1,'Everyone','{\"user_id\":1,\"time_interval\":1800,\"acl_id\":92,\"merge\":0,\"owncolor\":1,\"name\":\"Everyone\",\"id\":1}','2021-06-28 08:56:42',1,92,1,52,1,'172.18.0.1'),(150,1,'Everyone (Merge)','{\"user_id\":1,\"time_interval\":1800,\"acl_id\":93,\"merge\":1,\"owncolor\":1,\"name\":\"Everyone (Merge)\",\"id\":2}','2021-06-28 08:56:42',1,93,1,52,2,'172.18.0.1'),(151,1,'Road Runner Room','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Road Runner Room\",\"parent_id\":1,\"mtime\":1624870602,\"ctime\":1624870602,\"id\":18}','2021-06-28 08:56:42',1,24,1,39,18,'172.18.0.1'),(152,2,'Road Runner Room','{\"acl_id\":[0,94]}','2021-06-28 08:56:42',1,94,1,39,18,'172.18.0.1'),(153,1,'Road Runner Room','{\"group_id\":2,\"user_id\":1,\"acl_id\":94,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":18,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Road Runner Room\",\"id\":5}','2021-06-28 08:56:42',1,94,1,36,5,'172.18.0.1'),(154,1,'Don Coyote Room','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Don Coyote Room\",\"parent_id\":1,\"mtime\":1624870602,\"ctime\":1624870602,\"id\":19}','2021-06-28 08:56:42',1,24,1,39,19,'172.18.0.1'),(155,2,'Don Coyote Room','{\"acl_id\":[0,95]}','2021-06-28 08:56:42',1,95,1,39,19,'172.18.0.1'),(156,1,'Don Coyote Room','{\"group_id\":2,\"user_id\":1,\"acl_id\":95,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":19,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Don Coyote Room\",\"id\":6}','2021-06-28 08:56:42',1,95,1,36,6,'172.18.0.1'),(157,1,'System Administrator','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"System Administrator\",\"parent_id\":12,\"mtime\":1624870602,\"ctime\":1624870602,\"id\":20}','2021-06-28 08:56:42',1,24,1,39,20,'172.18.0.1'),(158,2,'System Administrator','{\"acl_id\":[0,96]}','2021-06-28 08:56:42',1,96,1,39,20,'172.18.0.1'),(159,1,'System Administrator','{\"files_folder_id\":20,\"version\":1,\"user_id\":1,\"name\":\"System Administrator\",\"acl_id\":96,\"id\":4}','2021-06-28 08:56:42',1,96,1,51,4,'172.18.0.1'),(160,1,'Feed the dog','{\"uuid\":\"1d2da469-7816-553c-bbf6-7864babab97c\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1625043402,\"tasklist_id\":2,\"name\":\"Feed the dog\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":1}','2021-06-28 08:56:42',1,84,0,40,1,'172.18.0.1'),(161,1,'Feed the dog','{\"uuid\":\"df5d008c-5576-5e1e-a536-4e88afc0b587\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1624957002,\"tasklist_id\":3,\"name\":\"Feed the dog\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":2}','2021-06-28 08:56:42',1,89,0,40,2,'172.18.0.1'),(162,1,'Feed the dog','{\"uuid\":\"08ceb7a3-96e3-5fa5-98a5-7df6644bbae3\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1624957002,\"tasklist_id\":1,\"name\":\"Feed the dog\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":3}','2021-06-28 08:56:42',1,79,0,40,3,'172.18.0.1'),(163,1,'Prepare meeting','{\"uuid\":\"8eee1e38-5e21-5a7c-b481-abab75bdcc33\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1624957002,\"tasklist_id\":2,\"name\":\"Prepare meeting\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":4}','2021-06-28 08:56:42',1,84,0,40,4,'172.18.0.1'),(164,1,NULL,'{\"id\":41,\"fromEntityTypeId\":40,\"fromId\":4,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:42',1,84,0,12,41,'172.18.0.1'),(165,1,NULL,'{\"id\":43,\"fromEntityTypeId\":40,\"fromId\":4,\"toEntityTypeId\":37,\"toId\":12,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:42',1,84,0,12,43,'172.18.0.1'),(166,1,'Prepare meeting','{\"uuid\":\"01c275eb-c1aa-5d4c-abb3-20500f83262f\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1624957002,\"tasklist_id\":3,\"name\":\"Prepare meeting\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":5}','2021-06-28 08:56:42',1,89,0,40,5,'172.18.0.1'),(167,1,NULL,'{\"id\":45,\"fromEntityTypeId\":40,\"fromId\":5,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:42',1,89,0,12,45,'172.18.0.1'),(168,1,NULL,'{\"id\":47,\"fromEntityTypeId\":40,\"fromId\":5,\"toEntityTypeId\":37,\"toId\":12,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:42',1,89,0,12,47,'172.18.0.1'),(169,1,'Prepare meeting','{\"uuid\":\"e9d08cfc-7196-5e96-bdcb-d19928cd5c86\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624870602,\"due_time\":1624957002,\"tasklist_id\":1,\"name\":\"Prepare meeting\",\"mtime\":1624870602,\"ctime\":1624870602,\"id\":6}','2021-06-28 08:56:42',1,79,0,40,6,'172.18.0.1'),(170,1,NULL,'{\"id\":49,\"fromEntityTypeId\":40,\"fromId\":6,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,79,0,12,49,'172.18.0.1'),(171,1,NULL,'{\"id\":51,\"fromEntityTypeId\":40,\"fromId\":6,\"toEntityTypeId\":37,\"toId\":12,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,79,0,12,51,'172.18.0.1'),(172,2,'Smith Inc.','{\"vatReverseCharge\":[true,false]}','2021-06-28 08:56:43',1,76,0,24,1,'172.18.0.1'),(173,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":1,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870603,\"mtime\":1624870603,\"muser_id\":1,\"btime\":1624870603,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":1}','2021-06-28 08:56:43',1,42,0,41,1,'172.18.0.1'),(174,1,NULL,'{\"id\":53,\"fromEntityTypeId\":41,\"fromId\":1,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,53,'172.18.0.1'),(175,1,NULL,'{\"id\":55,\"fromEntityTypeId\":41,\"fromId\":1,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,55,'172.18.0.1'),(176,2,'Quotes','{\"next_id\":[0,1]}','2021-06-28 08:56:43',1,42,1,43,1,'172.18.0.1'),(177,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 08:56:43',1,42,0,41,1,'172.18.0.1'),(178,2,'Q21000001','{\"status_id\":[0,1],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"Q21000001\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624870603]}','2021-06-28 08:56:43',1,42,0,41,1,'172.18.0.1'),(179,1,'Call: Smith Inc. (Q21000001)','{\"uuid\":\"c399502c-63d8-5cb9-bd9d-5d007b421ea1\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":1625129803,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1625129803,\"due_time\":1625129803,\"tasklist_id\":4,\"name\":\"Call: Smith Inc. (Q21000001)\",\"description\":\"\",\"mtime\":1624870603,\"ctime\":1624870603,\"id\":7}','2021-06-28 08:56:43',1,96,0,40,7,'172.18.0.1'),(180,1,NULL,'{\"id\":57,\"fromEntityTypeId\":41,\"fromId\":1,\"toEntityTypeId\":40,\"toId\":7,\"toEntity\":\"Task\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,57,'172.18.0.1'),(181,1,NULL,'{\"id\":59,\"fromEntityTypeId\":40,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,96,0,12,59,'172.18.0.1'),(182,1,NULL,'{\"id\":61,\"fromEntityTypeId\":40,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,96,0,12,61,'172.18.0.1'),(183,2,'ACME Corporation','{\"vatReverseCharge\":[true,false]}','2021-06-28 08:56:43',1,76,0,24,3,'172.18.0.1'),(184,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":1,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870603,\"mtime\":1624870603,\"muser_id\":1,\"btime\":1624870603,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":2}','2021-06-28 08:56:43',1,42,0,41,2,'172.18.0.1'),(185,1,NULL,'{\"id\":63,\"fromEntityTypeId\":41,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,63,'172.18.0.1'),(186,1,NULL,'{\"id\":65,\"fromEntityTypeId\":41,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,65,'172.18.0.1'),(187,2,'Quotes','{\"next_id\":[1,2]}','2021-06-28 08:56:43',1,42,1,43,1,'172.18.0.1'),(188,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 08:56:43',1,42,0,41,2,'172.18.0.1'),(189,2,'Q21000002','{\"status_id\":[0,1],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"Q21000002\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624870603]}','2021-06-28 08:56:43',1,42,0,41,2,'172.18.0.1'),(190,1,'Call: ACME Corporation (Q21000002)','{\"uuid\":\"d535e0f4-9c9a-5a10-afd3-9fbd4745e2e1\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":1625129803,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1625129803,\"due_time\":1625129803,\"tasklist_id\":4,\"name\":\"Call: ACME Corporation (Q21000002)\",\"description\":\"\",\"mtime\":1624870603,\"ctime\":1624870603,\"id\":8}','2021-06-28 08:56:43',1,96,0,40,8,'172.18.0.1'),(191,1,NULL,'{\"id\":67,\"fromEntityTypeId\":41,\"fromId\":2,\"toEntityTypeId\":40,\"toId\":8,\"toEntity\":\"Task\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,42,0,12,67,'172.18.0.1'),(192,1,NULL,'{\"id\":69,\"fromEntityTypeId\":40,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,96,0,12,69,'172.18.0.1'),(193,1,NULL,'{\"id\":71,\"fromEntityTypeId\":40,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 08:56:43',1,96,0,12,71,'172.18.0.1'),(194,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":2,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870603,\"mtime\":1624870603,\"muser_id\":1,\"btime\":1624870603,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":3}','2021-06-28 08:56:43',1,47,0,41,3,'172.18.0.1'),(195,1,NULL,'{\"id\":73,\"fromEntityTypeId\":41,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,47,0,12,73,'172.18.0.1'),(196,1,NULL,'{\"id\":75,\"fromEntityTypeId\":41,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,47,0,12,75,'172.18.0.1'),(197,2,'Orders','{\"next_id\":[0,1]}','2021-06-28 08:56:43',1,47,1,43,2,'172.18.0.1'),(198,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 08:56:43',1,47,0,41,3,'172.18.0.1'),(199,2,'O21000001','{\"status_id\":[0,5],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"O21000001\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624870603]}','2021-06-28 08:56:43',1,47,0,41,3,'172.18.0.1'),(200,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":2,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870603,\"mtime\":1624870603,\"muser_id\":1,\"btime\":1624870603,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":4}','2021-06-28 08:56:43',1,47,0,41,4,'172.18.0.1'),(201,1,NULL,'{\"id\":77,\"fromEntityTypeId\":41,\"fromId\":4,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,47,0,12,77,'172.18.0.1'),(202,1,NULL,'{\"id\":79,\"fromEntityTypeId\":41,\"fromId\":4,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:43',1,47,0,12,79,'172.18.0.1'),(203,2,'Orders','{\"next_id\":[1,2]}','2021-06-28 08:56:43',1,47,1,43,2,'172.18.0.1'),(204,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 08:56:43',1,47,0,41,4,'172.18.0.1'),(205,2,'O21000002','{\"status_id\":[0,5],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"O21000002\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624870603]}','2021-06-28 08:56:43',1,47,0,41,4,'172.18.0.1'),(206,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":3,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"btime\":1624870604,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":5}','2021-06-28 08:56:44',1,52,0,41,5,'172.18.0.1'),(207,1,NULL,'{\"id\":81,\"fromEntityTypeId\":41,\"fromId\":5,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:44',1,52,0,12,81,'172.18.0.1'),(208,1,NULL,'{\"id\":83,\"fromEntityTypeId\":41,\"fromId\":5,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:44',1,52,0,12,83,'172.18.0.1'),(209,2,'Invoices','{\"next_id\":[0,1]}','2021-06-28 08:56:44',1,52,1,43,3,'172.18.0.1'),(210,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 08:56:44',1,52,0,41,5,'172.18.0.1'),(211,2,'I21000001','{\"status_id\":[0,9],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"I21000001\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624870604]}','2021-06-28 08:56:44',1,52,0,41,5,'172.18.0.1'),(212,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":3,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"btime\":1624870604,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":6}','2021-06-28 08:56:44',1,52,0,41,6,'172.18.0.1'),(213,1,NULL,'{\"id\":85,\"fromEntityTypeId\":41,\"fromId\":6,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:44',1,52,0,12,85,'172.18.0.1'),(214,1,NULL,'{\"id\":87,\"fromEntityTypeId\":41,\"fromId\":6,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 08:56:44',1,52,0,12,87,'172.18.0.1'),(215,2,'Invoices','{\"next_id\":[1,2]}','2021-06-28 08:56:44',1,52,1,43,3,'172.18.0.1'),(216,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 08:56:44',1,52,0,41,6,'172.18.0.1'),(217,2,'I21000002','{\"status_id\":[0,9],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"I21000002\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624870604]}','2021-06-28 08:56:44',1,52,0,41,6,'172.18.0.1'),(218,1,'Malfunctioning rockets','{\"ticket_verifier\":91970135,\"priority\":1,\"status_id\":0,\"type_id\":1,\"user_id\":1,\"agent_id\":0,\"contact_id\":4,\"company\":\"ACME Corporation\",\"company_id\":3,\"first_name\":\"Wile\",\"middle_name\":\"E.\",\"last_name\":\"Coyote\",\"email\":\"wile@smith.demo\",\"phone\":\"+31 (0) 10 - 1234567\",\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"files_folder_id\":0,\"unseen\":1,\"group_id\":0,\"order_id\":0,\"last_response_time\":1624870604,\"cc_addresses\":\"\",\"cuser_id\":1,\"due_reminder_sent\":0,\"last_agent_response_time\":1624870604,\"last_contact_response_time\":1624870604,\"subject\":\"Malfunctioning rockets\",\"id\":1,\"ticket_number\":\"202100001\"}','2021-06-28 08:56:44',1,65,0,46,1,'172.18.0.1'),(219,2,'Malfunctioning rockets','{\"status_id\":[0,-1],\"unseen\":[true,0]}','2021-06-28 08:56:44',1,65,0,46,1,'172.18.0.1'),(220,1,'Can I speed up my rockets?','{\"ticket_verifier\":44842259,\"priority\":1,\"status_id\":0,\"type_id\":1,\"user_id\":1,\"agent_id\":0,\"contact_id\":4,\"company\":\"ACME Corporation\",\"company_id\":3,\"first_name\":\"Wile\",\"middle_name\":\"E.\",\"last_name\":\"Coyote\",\"email\":\"wile@smith.demo\",\"phone\":\"+31 (0) 10 - 1234567\",\"ctime\":1624697804,\"mtime\":1624697804,\"muser_id\":1,\"files_folder_id\":0,\"unseen\":1,\"group_id\":0,\"order_id\":0,\"last_response_time\":1624870604,\"cc_addresses\":\"\",\"cuser_id\":1,\"due_reminder_sent\":0,\"last_agent_response_time\":1624870604,\"last_contact_response_time\":1624870604,\"subject\":\"Can I speed up my rockets?\",\"id\":2,\"ticket_number\":\"202100002\"}','2021-06-28 08:56:44',1,65,0,46,2,'172.18.0.1'),(221,1,'site','{\"name\":\"site\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:56:44\",\"aclId\":98,\"version\":18,\"id\":31}','2021-06-28 08:56:44',1,98,1,13,31,'172.18.0.1'),(222,1,'defaultsite','{\"name\":\"defaultsite\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 08:56:44\",\"aclId\":99,\"version\":0,\"id\":32}','2021-06-28 08:56:44',1,99,1,13,32,'172.18.0.1'),(223,1,'Submit support ticket','{\"user_id\":1,\"due_time\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"title\":\"Submit support ticket\",\"content\":\"Anyone can submit tickets to the support system here:<br \\/><br \\/><a href=\\\"http:\\/\\/host.docker.internal:8080\\/modules\\/site\\/index.php?r=tickets\\/externalpage\\/newTicket\\\">http:\\/\\/host.docker.internal:8080\\/modules\\/site\\/index.php?r=tickets\\/externalpage\\/newTicket<\\/a><br \\/><br \\/>Anonymous ticket posting can be disabled in the ticket module settings.\",\"acl_id\":100,\"id\":1}','2021-06-28 08:56:44',1,100,1,55,1,'172.18.0.1'),(224,1,'Welcome to GroupOffice','{\"user_id\":1,\"due_time\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"title\":\"Welcome to GroupOffice\",\"content\":\"This is a demo announcements that administrators can set.<br \\/>Have a look around.<br \\/><br \\/>We hope you\'ll enjoy Group-Office as much as we do!\",\"acl_id\":101,\"id\":2}','2021-06-28 08:56:44',1,101,1,55,2,'172.18.0.1'),(225,1,'users','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"users\",\"parent_id\":0,\"mtime\":1624870604,\"ctime\":1624870604,\"id\":21}','2021-06-28 08:56:44',1,24,1,39,21,'172.18.0.1'),(226,1,'demo','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"demo\",\"parent_id\":21,\"mtime\":1624870604,\"ctime\":1624870604,\"id\":22}','2021-06-28 08:56:44',1,24,1,39,22,'172.18.0.1'),(227,2,'demo','{\"acl_id\":[0,102],\"user_id\":[1,3],\"visible\":[0,1],\"readonly\":[0,1]}','2021-06-28 08:56:44',1,102,1,39,22,'172.18.0.1'),(228,1,'users/demo/noperson.jpg','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":22,\"name\":\"noperson.jpg\",\"extension\":\"jpg\",\"size\":3015,\"id\":1}','2021-06-28 08:56:44',1,102,0,38,1,'172.18.0.1'),(229,2,'System Administrator','{\"disk_usage\":[0,3015],\"modifiedAt\":[\"2021-06-28 08:53:59\",\"2021-06-28 08:56:44\"]}','2021-06-28 08:56:44',1,4,0,21,1,'172.18.0.1'),(230,1,'users/demo/Demo letter.docx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":22,\"name\":\"Demo letter.docx\",\"extension\":\"docx\",\"size\":4312,\"id\":2}','2021-06-28 08:56:44',1,102,0,38,2,'172.18.0.1'),(231,2,'System Administrator','{\"disk_usage\":[3015,7327]}','2021-06-28 08:56:44',1,4,0,21,1,'172.18.0.1'),(232,1,'users/demo/wecoyote.png','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":22,\"name\":\"wecoyote.png\",\"extension\":\"png\",\"size\":39495,\"id\":3}','2021-06-28 08:56:44',1,102,0,38,3,'172.18.0.1'),(233,2,'System Administrator','{\"disk_usage\":[7327,46822]}','2021-06-28 08:56:44',1,4,0,21,1,'172.18.0.1'),(234,1,'users/demo/empty.docx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624870604,\"mtime\":1624870604,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":22,\"name\":\"empty.docx\",\"extension\":\"docx\",\"size\":3726,\"id\":4}','2021-06-28 08:56:45',1,102,0,38,4,'172.18.0.1'),(235,2,'System Administrator','{\"disk_usage\":[46822,50548],\"modifiedAt\":[\"2021-06-28 08:56:44\",\"2021-06-28 08:56:45\"]}','2021-06-28 08:56:45',1,4,0,21,1,'172.18.0.1'),(236,1,'users/demo/empty.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624870605,\"mtime\":1624870604,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":22,\"name\":\"empty.odt\",\"extension\":\"odt\",\"size\":6971,\"id\":5}','2021-06-28 08:56:45',1,102,0,38,5,'172.18.0.1'),(237,2,'System Administrator','{\"disk_usage\":[50548,57519]}','2021-06-28 08:56:45',1,4,0,21,1,'172.18.0.1'),(238,3,'demodata','null','2021-06-28 08:57:40',1,22,1,13,11,'172.18.0.1'),(239,2,'hoursapproval2','{\"enabled\":[1,0],\"modifiedAt\":[\"2021-06-28 08:55:27\",\"2021-06-28 08:59:28\"]}','2021-06-28 08:59:30',1,61,1,13,24,'172.18.0.1'),(240,2,'timeregistration2','{\"modifiedAt\":[\"2021-06-28 08:55:28\",\"2021-06-28 08:59:35\"]}','2021-06-28 08:59:36',1,60,1,13,23,'172.18.0.1'),(241,2,'timeregistration2','{\"enabled\":[1,0],\"modifiedAt\":[\"2021-06-28 08:59:35\",\"2021-06-28 08:59:40\"]}','2021-06-28 08:59:41',1,60,1,13,23,'172.18.0.1'),(242,3,'projects2','null','2021-06-28 09:00:02',1,59,1,13,22,'172.18.0.1'),(243,1,'projects2','{\"name\":\"projects2\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 09:00:23\",\"aclId\":106,\"version\":398,\"id\":33}','2021-06-28 09:00:23',1,106,1,13,33,'172.18.0.1'),(244,1,'Default','{\"acl_id\":107,\"acl_book\":108,\"user_id\":1,\"name\":\"Default\",\"id\":1}','2021-06-28 09:00:25',1,107,1,59,1,'172.18.0.1'),(245,1,'Ongoing','{\"name\":\"Ongoing\",\"complete\":0,\"sort_order\":0,\"filterable\":1,\"show_in_tree\":1,\"make_invoiceable\":0,\"not_for_postcalculation\":0,\"acl_id\":109,\"id\":1}','2021-06-28 09:00:25',1,109,1,60,1,'172.18.0.1'),(246,1,'None','{\"name\":\"None\",\"complete\":0,\"sort_order\":0,\"filterable\":1,\"show_in_tree\":1,\"make_invoiceable\":0,\"not_for_postcalculation\":0,\"acl_id\":110,\"id\":2}','2021-06-28 09:00:25',1,110,1,60,2,'172.18.0.1'),(247,1,'Complete','{\"name\":\"Complete\",\"complete\":1,\"sort_order\":0,\"filterable\":1,\"show_in_tree\":0,\"make_invoiceable\":0,\"not_for_postcalculation\":0,\"acl_id\":111,\"id\":3}','2021-06-28 09:00:25',1,111,1,60,3,'172.18.0.1'),(248,1,'project_templates','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"project_templates\",\"parent_id\":0,\"mtime\":1624870825,\"ctime\":1624870825,\"id\":23}','2021-06-28 09:00:25',1,24,1,39,23,'172.18.0.1'),(249,1,'Projects folder','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Projects folder\",\"parent_id\":23,\"mtime\":1624870826,\"ctime\":1624870826,\"id\":24}','2021-06-28 09:00:26',1,24,1,39,24,'172.18.0.1'),(250,2,'Projects folder','{\"acl_id\":[0,112]}','2021-06-28 09:00:26',1,112,1,39,24,'172.18.0.1'),(251,1,'Projects folder','{\"user_id\":1,\"name\":\"Projects folder\",\"acl_id\":112,\"files_folder_id\":24,\"fields\":\"\",\"icon\":\"projects2\\/template-icons\\/folder.png\",\"project_type\":0,\"use_name_template\":0,\"show_in_tree\":1,\"name_template\":\"\",\"default_status_id\":2,\"default_type_id\":1,\"id\":1}','2021-06-28 09:00:26',1,112,1,61,1,'172.18.0.1'),(252,1,'Standard project','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Standard project\",\"parent_id\":23,\"mtime\":1624870826,\"ctime\":1624870826,\"id\":25}','2021-06-28 09:00:26',1,24,1,39,25,'172.18.0.1'),(253,2,'Standard project','{\"acl_id\":[0,113]}','2021-06-28 09:00:26',1,113,1,39,25,'172.18.0.1'),(254,1,'Standard project','{\"user_id\":1,\"name\":\"Standard project\",\"acl_id\":113,\"files_folder_id\":25,\"fields\":\"responsible_user_id,status_date,customer,budget_fees,contact,expenses\",\"icon\":\"projects2\\/template-icons\\/project.png\",\"project_type\":1,\"use_name_template\":0,\"show_in_tree\":0,\"name_template\":\"\",\"default_status_id\":1,\"default_type_id\":1,\"id\":2}','2021-06-28 09:00:26',1,113,1,61,2,'172.18.0.1'),(255,2,'projects2','{\"modifiedAt\":[\"2021-06-28 09:00:23\",\"2021-06-28 09:00:26\"]}','2021-06-28 09:00:26',1,106,1,13,33,'172.18.0.1'),(256,1,'demodata','{\"name\":\"demodata\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 09:04:17\",\"aclId\":114,\"version\":0,\"id\":34}','2021-06-28 09:04:17',1,114,1,13,34,'172.18.0.1'),(257,1,'stationery-papers','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"stationery-papers\",\"parent_id\":3,\"mtime\":1624871065,\"ctime\":1624871065,\"id\":26}','2021-06-28 09:04:25',1,24,1,39,26,'172.18.0.1'),(258,2,'IT','{\"search_cache_acl_id\":[0,121]}','2021-06-28 09:04:25',1,65,1,47,1,'172.18.0.1'),(259,2,'Sales','{\"search_cache_acl_id\":[0,122]}','2021-06-28 09:04:25',1,66,1,47,2,'172.18.0.1'),(260,1,'projects2','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"projects2\",\"parent_id\":0,\"mtime\":1624871066,\"ctime\":1624871066,\"id\":27}','2021-06-28 09:04:26',1,24,1,39,27,'172.18.0.1'),(261,1,'template-icons','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"template-icons\",\"parent_id\":27,\"mtime\":1624871066,\"ctime\":1624871066,\"id\":28}','2021-06-28 09:04:26',1,24,1,39,28,'172.18.0.1'),(262,2,'template-icons','{\"acl_id\":[0,106]}','2021-06-28 09:04:26',1,106,1,39,28,'172.18.0.1'),(263,1,'Project meeting','{\"uuid\":\"39b398bf-4e89-5b16-8e73-9a55b08bdbf0\",\"user_id\":3,\"start_time\":1624953600,\"end_time\":1624957200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871088,\"mtime\":1624871088,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":13}','2021-06-28 09:04:48',1,83,0,37,13,'172.18.0.1'),(264,1,'Project meeting','{\"uuid\":\"39b398bf-4e89-5b16-8e73-9a55b08bdbf0\",\"user_id\":4,\"start_time\":1624953600,\"end_time\":1624957200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871088,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":14}','2021-06-28 09:04:49',1,88,0,37,14,'172.18.0.1'),(265,1,NULL,'{\"id\":89,\"fromEntityTypeId\":37,\"fromId\":13,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,89,'172.18.0.1'),(266,1,NULL,'{\"id\":91,\"fromEntityTypeId\":37,\"fromId\":13,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,91,'172.18.0.1'),(267,1,'Meet Wile','{\"uuid\":\"ccb3b5d1-89ff-59d7-8f6f-344f73eca67f\",\"user_id\":3,\"start_time\":1624960800,\"end_time\":1624964400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"Meet Wile\",\"files_folder_id\":0,\"id\":15}','2021-06-28 09:04:49',1,83,0,37,15,'172.18.0.1'),(268,1,'Meet Wile','{\"uuid\":\"ccb3b5d1-89ff-59d7-8f6f-344f73eca67f\",\"user_id\":4,\"start_time\":1624960800,\"end_time\":1624964400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"Meet Wile\",\"files_folder_id\":0,\"id\":16}','2021-06-28 09:04:49',1,88,0,37,16,'172.18.0.1'),(269,1,NULL,'{\"id\":93,\"fromEntityTypeId\":37,\"fromId\":15,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,93,'172.18.0.1'),(270,1,NULL,'{\"id\":95,\"fromEntityTypeId\":37,\"fromId\":15,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,95,'172.18.0.1'),(271,1,'MT Meeting','{\"uuid\":\"e0e2aa9d-d16e-550f-b3df-5c439f220088\",\"user_id\":3,\"start_time\":1624968000,\"end_time\":1624971600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":3,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":17}','2021-06-28 09:04:49',1,83,0,37,17,'172.18.0.1'),(272,1,'MT Meeting','{\"uuid\":\"e0e2aa9d-d16e-550f-b3df-5c439f220088\",\"user_id\":4,\"start_time\":1624968000,\"end_time\":1624971600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":0,\"calendar_id\":4,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":18}','2021-06-28 09:04:49',1,88,0,37,18,'172.18.0.1'),(273,1,NULL,'{\"id\":97,\"fromEntityTypeId\":37,\"fromId\":17,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,97,'172.18.0.1'),(274,1,NULL,'{\"id\":99,\"fromEntityTypeId\":37,\"fromId\":17,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,83,0,12,99,'172.18.0.1'),(275,1,'Project meeting','{\"uuid\":\"abed776f-733b-54ec-b0b1-3d36c9514ce5\",\"user_id\":4,\"start_time\":1625043600,\"end_time\":1625047200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Project meeting\",\"files_folder_id\":0,\"id\":19}','2021-06-28 09:04:49',1,88,0,37,19,'172.18.0.1'),(276,1,NULL,'{\"id\":101,\"fromEntityTypeId\":37,\"fromId\":19,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,101,'172.18.0.1'),(277,1,NULL,'{\"id\":103,\"fromEntityTypeId\":37,\"fromId\":19,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,103,'172.18.0.1'),(278,1,'Meet John','{\"uuid\":\"e7eab014-6a0f-549d-b11c-e25b30422c45\",\"user_id\":4,\"start_time\":1625050800,\"end_time\":1625054400,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Meet John\",\"files_folder_id\":0,\"id\":20}','2021-06-28 09:04:49',1,88,0,37,20,'172.18.0.1'),(279,1,NULL,'{\"id\":105,\"fromEntityTypeId\":37,\"fromId\":20,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,105,'172.18.0.1'),(280,1,NULL,'{\"id\":107,\"fromEntityTypeId\":37,\"fromId\":20,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,107,'172.18.0.1'),(281,1,'MT Meeting','{\"uuid\":\"f84dd5cb-6976-542a-ba54-5779100287f0\",\"user_id\":4,\"start_time\":1625061600,\"end_time\":1625065200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME NY Office\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"MT Meeting\",\"files_folder_id\":0,\"id\":21}','2021-06-28 09:04:49',1,88,0,37,21,'172.18.0.1'),(282,1,NULL,'{\"id\":109,\"fromEntityTypeId\":37,\"fromId\":21,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,109,'172.18.0.1'),(283,1,NULL,'{\"id\":111,\"fromEntityTypeId\":37,\"fromId\":21,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,111,'172.18.0.1'),(284,1,'Rocket testing','{\"uuid\":\"b6cf3809-d8ef-58e2-8e00-5b43ac6ec200\",\"user_id\":4,\"start_time\":1624946400,\"end_time\":1624950000,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Rocket testing\",\"files_folder_id\":0,\"id\":22}','2021-06-28 09:04:49',1,88,0,37,22,'172.18.0.1'),(285,1,NULL,'{\"id\":113,\"fromEntityTypeId\":37,\"fromId\":22,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,113,'172.18.0.1'),(286,1,NULL,'{\"id\":115,\"fromEntityTypeId\":37,\"fromId\":22,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,115,'172.18.0.1'),(287,1,'Blast impact test','{\"uuid\":\"2908495d-c762-5638-89f7-fcf153f39fa5\",\"user_id\":4,\"start_time\":1624971600,\"end_time\":1624975200,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Blast impact test\",\"files_folder_id\":0,\"id\":23}','2021-06-28 09:04:49',1,88,0,37,23,'172.18.0.1'),(288,1,NULL,'{\"id\":117,\"fromEntityTypeId\":37,\"fromId\":23,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,117,'172.18.0.1'),(289,1,NULL,'{\"id\":119,\"fromEntityTypeId\":37,\"fromId\":23,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,119,'172.18.0.1'),(290,1,'Test range extender','{\"uuid\":\"72d0c9b1-14ea-5585-b3fd-5031f6f4cfe5\",\"user_id\":4,\"start_time\":1624986000,\"end_time\":1624989600,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"ACME Testing fields\",\"repeat_end_time\":0,\"ctime\":1624871089,\"mtime\":1624871089,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":4,\"name\":\"Test range extender\",\"files_folder_id\":0,\"id\":24}','2021-06-28 09:04:49',1,88,0,37,24,'172.18.0.1'),(291,1,NULL,'{\"id\":121,\"fromEntityTypeId\":37,\"fromId\":24,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,121,'172.18.0.1'),(292,1,NULL,'{\"id\":123,\"fromEntityTypeId\":37,\"fromId\":24,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Event\"}','2021-06-28 09:04:49',1,88,0,12,123,'172.18.0.1'),(293,1,'Feed the dog','{\"uuid\":\"d69217b3-c24f-56f3-8a64-9ce00023afc7\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871089,\"due_time\":1625043889,\"tasklist_id\":2,\"name\":\"Feed the dog\",\"mtime\":1624871089,\"ctime\":1624871089,\"id\":9}','2021-06-28 09:04:49',1,84,0,40,9,'172.18.0.1'),(294,1,'Feed the dog','{\"uuid\":\"ee289c2c-6917-50e4-bd6a-825be922c55d\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871089,\"due_time\":1624957489,\"tasklist_id\":3,\"name\":\"Feed the dog\",\"mtime\":1624871089,\"ctime\":1624871089,\"id\":10}','2021-06-28 09:04:49',1,89,0,40,10,'172.18.0.1'),(295,1,'Feed the dog','{\"uuid\":\"3f96e54b-6b3c-5b68-9dfc-33785217cf88\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871090,\"due_time\":1624957490,\"tasklist_id\":1,\"name\":\"Feed the dog\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":11}','2021-06-28 09:04:50',1,79,0,40,11,'172.18.0.1'),(296,1,'Prepare meeting','{\"uuid\":\"5c8b89bb-3367-537a-b569-ba58e43314af\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871090,\"due_time\":1624957490,\"tasklist_id\":2,\"name\":\"Prepare meeting\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":12}','2021-06-28 09:04:50',1,84,0,40,12,'172.18.0.1'),(297,1,NULL,'{\"id\":125,\"fromEntityTypeId\":40,\"fromId\":12,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,84,0,12,125,'172.18.0.1'),(298,1,NULL,'{\"id\":127,\"fromEntityTypeId\":40,\"fromId\":12,\"toEntityTypeId\":37,\"toId\":24,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,84,0,12,127,'172.18.0.1'),(299,1,'Prepare meeting','{\"uuid\":\"ca872636-a830-52d9-8930-4f4c2da30919\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871090,\"due_time\":1624957490,\"tasklist_id\":3,\"name\":\"Prepare meeting\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":13}','2021-06-28 09:04:50',1,89,0,40,13,'172.18.0.1'),(300,1,NULL,'{\"id\":129,\"fromEntityTypeId\":40,\"fromId\":13,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,89,0,12,129,'172.18.0.1'),(301,1,NULL,'{\"id\":131,\"fromEntityTypeId\":40,\"fromId\":13,\"toEntityTypeId\":37,\"toId\":24,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,89,0,12,131,'172.18.0.1'),(302,1,'Prepare meeting','{\"uuid\":\"ff98bfa3-2ed6-5a57-8579-79699a4c46b5\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":0,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1624871090,\"due_time\":1624957490,\"tasklist_id\":1,\"name\":\"Prepare meeting\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":14}','2021-06-28 09:04:50',1,79,0,40,14,'172.18.0.1'),(303,1,NULL,'{\"id\":133,\"fromEntityTypeId\":40,\"fromId\":14,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,79,0,12,133,'172.18.0.1'),(304,1,NULL,'{\"id\":135,\"fromEntityTypeId\":40,\"fromId\":14,\"toEntityTypeId\":37,\"toId\":24,\"toEntity\":\"Event\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,79,0,12,135,'172.18.0.1'),(305,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":1,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871090,\"mtime\":1624871090,\"muser_id\":1,\"btime\":1624871090,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":7}','2021-06-28 09:04:50',1,42,0,41,7,'172.18.0.1'),(306,1,NULL,'{\"id\":137,\"fromEntityTypeId\":41,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,137,'172.18.0.1'),(307,1,NULL,'{\"id\":139,\"fromEntityTypeId\":41,\"fromId\":7,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,139,'172.18.0.1'),(308,2,'Quotes','{\"next_id\":[2,3]}','2021-06-28 09:04:50',1,42,1,43,1,'172.18.0.1'),(309,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 09:04:50',1,42,0,41,7,'172.18.0.1'),(310,2,'Q21000003','{\"status_id\":[0,1],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"Q21000003\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624871090]}','2021-06-28 09:04:50',1,42,0,41,7,'172.18.0.1'),(311,1,'Call: Smith Inc. (Q21000003)','{\"uuid\":\"152d23db-35ab-5436-b37e-5a2aac509494\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":1625130290,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1625130290,\"due_time\":1625130290,\"tasklist_id\":4,\"name\":\"Call: Smith Inc. (Q21000003)\",\"description\":\"\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":15}','2021-06-28 09:04:50',1,96,0,40,15,'172.18.0.1'),(312,1,NULL,'{\"id\":141,\"fromEntityTypeId\":41,\"fromId\":7,\"toEntityTypeId\":40,\"toId\":15,\"toEntity\":\"Task\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,141,'172.18.0.1'),(313,1,NULL,'{\"id\":143,\"fromEntityTypeId\":40,\"fromId\":15,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,96,0,12,143,'172.18.0.1'),(314,1,NULL,'{\"id\":145,\"fromEntityTypeId\":40,\"fromId\":15,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,96,0,12,145,'172.18.0.1'),(315,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":1,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871090,\"mtime\":1624871090,\"muser_id\":1,\"btime\":1624871090,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":8}','2021-06-28 09:04:50',1,42,0,41,8,'172.18.0.1'),(316,1,NULL,'{\"id\":147,\"fromEntityTypeId\":41,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,147,'172.18.0.1'),(317,1,NULL,'{\"id\":149,\"fromEntityTypeId\":41,\"fromId\":8,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,149,'172.18.0.1'),(318,2,'Quotes','{\"next_id\":[3,4]}','2021-06-28 09:04:50',1,42,1,43,1,'172.18.0.1'),(319,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 09:04:50',1,42,0,41,8,'172.18.0.1'),(320,2,'Q21000004','{\"status_id\":[0,1],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"Q21000004\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624871090]}','2021-06-28 09:04:50',1,42,0,41,8,'172.18.0.1'),(321,1,'Call: ACME Corporation (Q21000004)','{\"uuid\":\"fff03b14-3576-58d0-8849-091c1840ae34\",\"muser_id\":1,\"completion_time\":0,\"repeat_end_time\":0,\"reminder\":1625130290,\"rrule\":\"\",\"files_folder_id\":0,\"category_id\":0,\"priority\":1,\"percentage_complete\":0,\"project_id\":0,\"user_id\":1,\"status\":\"NEEDS-ACTION\",\"start_time\":1625130290,\"due_time\":1625130290,\"tasklist_id\":4,\"name\":\"Call: ACME Corporation (Q21000004)\",\"description\":\"\",\"mtime\":1624871090,\"ctime\":1624871090,\"id\":16}','2021-06-28 09:04:50',1,96,0,40,16,'172.18.0.1'),(322,1,NULL,'{\"id\":151,\"fromEntityTypeId\":41,\"fromId\":8,\"toEntityTypeId\":40,\"toId\":16,\"toEntity\":\"Task\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,42,0,12,151,'172.18.0.1'),(323,1,NULL,'{\"id\":153,\"fromEntityTypeId\":40,\"fromId\":16,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,96,0,12,153,'172.18.0.1'),(324,1,NULL,'{\"id\":155,\"fromEntityTypeId\":40,\"fromId\":16,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Task\"}','2021-06-28 09:04:50',1,96,0,12,155,'172.18.0.1'),(325,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":2,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871090,\"mtime\":1624871090,\"muser_id\":1,\"btime\":1624871090,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":9}','2021-06-28 09:04:50',1,47,0,41,9,'172.18.0.1'),(326,1,NULL,'{\"id\":157,\"fromEntityTypeId\":41,\"fromId\":9,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,47,0,12,157,'172.18.0.1'),(327,1,NULL,'{\"id\":159,\"fromEntityTypeId\":41,\"fromId\":9,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,47,0,12,159,'172.18.0.1'),(328,2,'Orders','{\"next_id\":[2,3]}','2021-06-28 09:04:50',1,47,1,43,2,'172.18.0.1'),(329,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 09:04:50',1,47,0,41,9,'172.18.0.1'),(330,2,'O21000003','{\"status_id\":[0,5],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"O21000003\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624871090]}','2021-06-28 09:04:50',1,47,0,41,9,'172.18.0.1'),(331,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":2,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871090,\"mtime\":1624871090,\"muser_id\":1,\"btime\":1624871090,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":10}','2021-06-28 09:04:50',1,47,0,41,10,'172.18.0.1'),(332,1,NULL,'{\"id\":161,\"fromEntityTypeId\":41,\"fromId\":10,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,47,0,12,161,'172.18.0.1'),(333,1,NULL,'{\"id\":163,\"fromEntityTypeId\":41,\"fromId\":10,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:50',1,47,0,12,163,'172.18.0.1'),(334,2,'Orders','{\"next_id\":[3,4]}','2021-06-28 09:04:51',1,47,1,43,2,'172.18.0.1'),(335,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 09:04:51',1,47,0,41,10,'172.18.0.1'),(336,2,'O21000004','{\"status_id\":[0,5],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"O21000004\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624871091]}','2021-06-28 09:04:51',1,47,0,41,10,'172.18.0.1'),(337,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":3,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871091,\"mtime\":1624871091,\"muser_id\":1,\"btime\":1624871091,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"Smith Inc.\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":2,\"customer_to\":\"Smith Inc.\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@smith.demo\",\"company_id\":1,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"John Smith\",\"id\":11}','2021-06-28 09:04:51',1,52,0,41,11,'172.18.0.1'),(338,1,NULL,'{\"id\":165,\"fromEntityTypeId\":41,\"fromId\":11,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:51',1,52,0,12,165,'172.18.0.1'),(339,1,NULL,'{\"id\":167,\"fromEntityTypeId\":41,\"fromId\":11,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:51',1,52,0,12,167,'172.18.0.1'),(340,2,'Invoices','{\"next_id\":[2,3]}','2021-06-28 09:04:51',1,52,1,43,3,'172.18.0.1'),(341,2,'Scheduled order','{\"total_paid\":[0,\"20999.95\"]}','2021-06-28 09:04:51',1,52,0,41,11,'172.18.0.1'),(342,2,'I21000003','{\"status_id\":[0,9],\"costs\":[0,\"7000\"],\"subtotal\":[0,\"20999.95\"],\"total\":[0,\"20999.95\"],\"order_id\":[\"\",\"I21000003\"],\"total_paid\":[\"0\",\"20999.95\"],\"ptime\":[0,1624871091]}','2021-06-28 09:04:51',1,52,0,41,11,'172.18.0.1'),(343,1,'Scheduled order','{\"project_id\":0,\"status_id\":0,\"book_id\":3,\"language_id\":1,\"user_id\":1,\"order_id\":\"\",\"po_id\":\"\",\"ctime\":1624871091,\"mtime\":1624871091,\"muser_id\":1,\"btime\":1624871091,\"ptime\":0,\"costs\":\"0\",\"subtotal\":\"0\",\"total\":\"0\",\"customer_name\":\"ACME Corporation\",\"customer_crn\":\"\",\"customer_extra\":\"\",\"webshop_id\":0,\"recur_type\":\"\",\"payment_method\":\"\",\"recurred_order_id\":0,\"reference\":\"\",\"pagebreak\":0,\"files_folder_id\":0,\"for_warehouse\":0,\"dtime\":0,\"total_paid\":\"0\",\"other_shipping_address\":0,\"extra_costs\":\"0\",\"customer_salutation\":\"Dear sir\\/madam\",\"frontpage_text\":\"\",\"customer_country\":\"NL\",\"telesales_agent\":1,\"fieldsales_agent\":1,\"contact_id\":4,\"customer_to\":\"ACME Corporation\",\"customer_address\":\"Kalverstraat\",\"customer_address_no\":\"1\",\"customer_zip\":\"1012 NX\",\"customer_city\":\"Amsterdam\",\"customer_state\":\"Noord-Holland\",\"customer_email\":\"info@acme.demo\",\"company_id\":3,\"customer_vat_no\":\"NL 1234.56.789.B01\",\"customer_contact_name\":\"Wile E. Coyote\",\"id\":12}','2021-06-28 09:04:51',1,52,0,41,12,'172.18.0.1'),(344,1,NULL,'{\"id\":169,\"fromEntityTypeId\":41,\"fromId\":12,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:51',1,52,0,12,169,'172.18.0.1'),(345,1,NULL,'{\"id\":171,\"fromEntityTypeId\":41,\"fromId\":12,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Order\"}','2021-06-28 09:04:51',1,52,0,12,171,'172.18.0.1'),(346,2,'Invoices','{\"next_id\":[3,4]}','2021-06-28 09:04:51',1,52,1,43,3,'172.18.0.1'),(347,2,'Scheduled order','{\"total_paid\":[0,\"38999.89\"]}','2021-06-28 09:04:51',1,52,0,41,12,'172.18.0.1'),(348,2,'I21000004','{\"status_id\":[0,9],\"costs\":[0,\"13000\"],\"subtotal\":[0,\"38999.89\"],\"total\":[0,\"38999.89\"],\"order_id\":[\"\",\"I21000004\"],\"total_paid\":[\"0\",\"38999.89\"],\"ptime\":[0,1624871091]}','2021-06-28 09:04:51',1,52,0,41,12,'172.18.0.1'),(349,1,'Malfunctioning rockets','{\"ticket_verifier\":99427432,\"priority\":1,\"status_id\":0,\"type_id\":1,\"user_id\":1,\"agent_id\":0,\"contact_id\":4,\"company\":\"ACME Corporation\",\"company_id\":3,\"first_name\":\"Wile\",\"middle_name\":\"E.\",\"last_name\":\"Coyote\",\"email\":\"wile@smith.demo\",\"phone\":\"+31 (0) 10 - 1234567\",\"ctime\":1624871091,\"mtime\":1624871091,\"muser_id\":1,\"files_folder_id\":0,\"unseen\":1,\"group_id\":0,\"order_id\":0,\"last_response_time\":1624871091,\"cc_addresses\":\"\",\"cuser_id\":1,\"due_reminder_sent\":0,\"last_agent_response_time\":1624871091,\"last_contact_response_time\":1624871091,\"subject\":\"Malfunctioning rockets\",\"id\":3,\"ticket_number\":\"202100003\"}','2021-06-28 09:04:51',1,65,0,46,3,'172.18.0.1'),(350,2,'Malfunctioning rockets','{\"agent_id\":[0,2]}','2021-06-28 09:04:51',1,65,0,46,3,'172.18.0.1'),(351,2,'Malfunctioning rockets','{\"last_agent_response_time\":[1624871091,1624871092],\"status_id\":[0,-1],\"last_response_time\":[1624871091,1624871092],\"unseen\":[true,0]}','2021-06-28 09:04:52',1,65,0,46,3,'172.18.0.1'),(352,1,'Can I speed up my rockets?','{\"ticket_verifier\":67653384,\"priority\":1,\"status_id\":0,\"type_id\":1,\"user_id\":1,\"agent_id\":0,\"contact_id\":4,\"company\":\"ACME Corporation\",\"company_id\":3,\"first_name\":\"Wile\",\"middle_name\":\"E.\",\"last_name\":\"Coyote\",\"email\":\"wile@smith.demo\",\"phone\":\"+31 (0) 10 - 1234567\",\"ctime\":1624698292,\"mtime\":1624698292,\"muser_id\":1,\"files_folder_id\":0,\"unseen\":1,\"group_id\":0,\"order_id\":0,\"last_response_time\":1624871092,\"cc_addresses\":\"\",\"cuser_id\":1,\"due_reminder_sent\":0,\"last_agent_response_time\":1624871092,\"last_contact_response_time\":1624871092,\"subject\":\"Can I speed up my rockets?\",\"id\":4,\"ticket_number\":\"202100004\"}','2021-06-28 09:04:52',1,65,0,46,4,'172.18.0.1'),(353,1,'Demo','{\"acl_id\":123,\"acl_book\":124,\"user_id\":1,\"name\":\"Demo\",\"id\":2}','2021-06-28 09:04:52',1,123,1,59,2,'172.18.0.1'),(354,1,'Demo','{\"user_id\":1,\"name\":\"Demo\",\"customer\":\"\",\"ctime\":1624871094,\"mtime\":1624871094,\"muser_id\":1,\"start_time\":1624871092,\"due_time\":0,\"files_folder_id\":0,\"responsible_user_id\":0,\"calendar_id\":0,\"event_id\":0,\"path\":\"Demo\",\"income_type\":\"1\",\"parent_project_id\":0,\"travel_costs\":\"0\",\"reference_no\":\"\",\"description\":\"Just a placeholder for sub projects.\",\"template_id\":1,\"type_id\":2,\"acl_id\":123,\"status_id\":1,\"id\":1}','2021-06-28 09:04:54',1,123,0,57,1,'172.18.0.1'),(355,1,'[001] Develop Rocket 2000','{\"user_id\":1,\"name\":\"[001] Develop Rocket 2000\",\"customer\":\"ACME Corporation\",\"ctime\":1624871095,\"mtime\":1624871095,\"muser_id\":1,\"start_time\":1624871094,\"due_time\":1627463094,\"files_folder_id\":0,\"responsible_user_id\":0,\"calendar_id\":0,\"event_id\":0,\"path\":\"Demo\\/[001] Develop Rocket 2000\",\"income_type\":\"1\",\"parent_project_id\":1,\"travel_costs\":\"0\",\"reference_no\":\"\",\"type_id\":2,\"acl_id\":123,\"status_id\":1,\"description\":\"Better range and accuracy\",\"template_id\":2,\"company_id\":3,\"contact_id\":4,\"contact\":\"Wile E. Coyote\",\"id\":2}','2021-06-28 09:04:55',1,123,0,57,2,'172.18.0.1'),(356,1,NULL,'{\"id\":173,\"fromEntityTypeId\":57,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Project\"}','2021-06-28 09:04:55',1,123,0,12,173,'172.18.0.1'),(357,1,NULL,'{\"id\":175,\"fromEntityTypeId\":57,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Project\"}','2021-06-28 09:04:55',1,123,0,12,175,'172.18.0.1'),(358,1,'[001] Develop Rocket Launcher','{\"user_id\":1,\"name\":\"[001] Develop Rocket Launcher\",\"customer\":\"ACME Corporation\",\"ctime\":1624871095,\"mtime\":1624871095,\"muser_id\":1,\"start_time\":1624871095,\"due_time\":1627463095,\"files_folder_id\":0,\"responsible_user_id\":0,\"calendar_id\":0,\"event_id\":0,\"path\":\"Demo\\/[001] Develop Rocket Launcher\",\"income_type\":\"1\",\"parent_project_id\":1,\"travel_costs\":\"0\",\"reference_no\":\"\",\"type_id\":2,\"acl_id\":123,\"status_id\":1,\"description\":\"Better range and accuracy\",\"template_id\":2,\"company_id\":3,\"contact_id\":4,\"contact\":\"Wile E. Coyote\",\"id\":3}','2021-06-28 09:04:55',1,123,0,57,3,'172.18.0.1'),(359,1,NULL,'{\"id\":177,\"fromEntityTypeId\":57,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Project\"}','2021-06-28 09:04:55',1,123,0,12,177,'172.18.0.1'),(360,1,NULL,'{\"id\":179,\"fromEntityTypeId\":57,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"Project\"}','2021-06-28 09:04:55',1,123,0,12,179,'172.18.0.1'),(361,1,'projects2/template-icons/folder.png','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624871095,\"mtime\":1624870825,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":28,\"name\":\"folder.png\",\"extension\":\"png\",\"size\":611,\"id\":6}','2021-06-28 09:04:55',1,106,0,38,6,'172.18.0.1'),(362,2,'System Administrator','{\"disk_usage\":[57519,58130],\"modifiedAt\":[\"2021-06-28 08:56:45\",\"2021-06-28 09:04:55\"]}','2021-06-28 09:04:55',1,4,0,21,1,'172.18.0.1'),(363,1,'projects2/template-icons/project.png','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624871095,\"mtime\":1624870825,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":28,\"name\":\"project.png\",\"extension\":\"png\",\"size\":3231,\"id\":7}','2021-06-28 09:04:55',1,106,0,38,7,'172.18.0.1'),(364,2,'System Administrator','{\"disk_usage\":[58130,61361]}','2021-06-28 09:04:55',1,4,0,21,1,'172.18.0.1'),(365,1,'General','{\"id\":1,\"name\":\"General\",\"setAcl\":{\"3\":10}}','2021-06-28 09:04:55',1,125,1,30,1,'172.18.0.1'),(366,1,'Group-Office','{\"id\":1,\"categoryId\":1,\"name\":\"Group-Office\",\"content\":\"https:\\/\\/www.group-office.com\",\"description\":\"Group-Office is an enterprise CRM and groupware tool. Share projects, calendars, files and e-mail online with co-workers and clients. Easy to use and fully customizable.\",\"logo\":\"a277a250ad9fa623fd0c1c9bdbfb5804981d14e4\"}','2021-06-28 09:04:56',1,125,0,29,1,'172.18.0.1'),(367,1,'Intermesh','{\"id\":2,\"categoryId\":1,\"name\":\"Intermesh\",\"content\":\"https:\\/\\/www.intermesh.nl\",\"description\":\"Intermesh - Solide software ontwikeling sinds 2003\",\"logo\":\"b82d0979d555bd137b33c15021129e06cbeea59a\"}','2021-06-28 09:04:56',1,125,0,29,2,'172.18.0.1'),(368,1,'Rocket 2000 development plan','{\"user_id\":1,\"time\":1368777188,\"mtime\":1624871096,\"muser_id\":1,\"uid\":\"<1368777188.5195e1e479413@localhost>\",\"from\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"to\":\"\\\"Elmer\\\" <elmer@group-office.com>\",\"subject\":\"Rocket 2000 development plan\",\"acl_id\":76,\"path\":\"email\\/fromfile\\/demo_60d990b865619.eml\\/demo.eml\",\"ctime\":1624871096,\"id\":1}','2021-06-28 09:04:56',1,76,1,45,1,'172.18.0.1'),(369,1,NULL,'{\"id\":181,\"fromEntityTypeId\":45,\"fromId\":1,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"LinkedEmail\"}','2021-06-28 09:04:56',1,76,0,12,181,'172.18.0.1'),(370,1,'Rocket 2000 development plan','{\"user_id\":1,\"time\":1368777188,\"mtime\":1624871096,\"muser_id\":1,\"uid\":\"<1368777188.5195e1e479413@localhost>\",\"from\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"to\":\"\\\"Elmer\\\" <elmer@group-office.com>\",\"subject\":\"Rocket 2000 development plan\",\"acl_id\":76,\"path\":\"email\\/fromfile\\/demo_60d990b87560e.eml\\/demo.eml\",\"ctime\":1624871096,\"id\":2}','2021-06-28 09:04:56',1,76,1,45,2,'172.18.0.1'),(371,1,NULL,'{\"id\":183,\"fromEntityTypeId\":45,\"fromId\":2,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"LinkedEmail\"}','2021-06-28 09:04:56',1,76,0,12,183,'172.18.0.1'),(372,1,'Just a demo message','{\"user_id\":1,\"time\":1368777986,\"mtime\":1624871096,\"muser_id\":1,\"uid\":\"<1368777986.5195e5020b17e@localhost>\",\"from\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"to\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"subject\":\"Just a demo message\",\"acl_id\":76,\"path\":\"email\\/fromfile\\/demo2_60d990b87e51b.eml\\/demo2.eml\",\"ctime\":1624871096,\"id\":3}','2021-06-28 09:04:56',1,76,1,45,3,'172.18.0.1'),(373,1,NULL,'{\"id\":185,\"fromEntityTypeId\":45,\"fromId\":3,\"toEntityTypeId\":24,\"toId\":4,\"toEntity\":\"Contact\",\"fromEntity\":\"LinkedEmail\"}','2021-06-28 09:04:56',1,76,0,12,185,'172.18.0.1'),(374,1,'Just a demo message','{\"user_id\":1,\"time\":1368777986,\"mtime\":1624871096,\"muser_id\":1,\"uid\":\"<1368777986.5195e5020b17e@localhost>\",\"from\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"to\":\"\\\"User, Demo\\\" <demo@group-office.com>\",\"subject\":\"Just a demo message\",\"acl_id\":76,\"path\":\"email\\/fromfile\\/demo2_60d990b887b25.eml\\/demo2.eml\",\"ctime\":1624871096,\"id\":4}','2021-06-28 09:04:56',1,76,1,45,4,'172.18.0.1'),(375,1,NULL,'{\"id\":187,\"fromEntityTypeId\":45,\"fromId\":4,\"toEntityTypeId\":24,\"toId\":2,\"toEntity\":\"Contact\",\"fromEntity\":\"LinkedEmail\"}','2021-06-28 09:04:56',1,76,0,12,187,'172.18.0.1'),(376,3,'demodata','null','2021-06-28 09:04:56',1,114,1,13,34,'172.18.0.1'),(377,1,'postfixadmin','{\"name\":\"postfixadmin\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-06-28 09:07:04\",\"aclId\":126,\"version\":45,\"id\":35}','2021-06-28 09:07:04',1,126,1,13,35,'172.18.0.1'),(378,1,NULL,'{\"max_aliases\":0,\"max_mailboxes\":0,\"total_quota\":10485760,\"default_quota\":524288,\"transport\":\"virtual\",\"backupmx\":0,\"ctime\":1624871224,\"mtime\":1624871224,\"active\":1,\"user_id\":1,\"domain\":\"intermesh.localhost\",\"acl_id\":127,\"id\":1}','2021-06-28 09:07:05',1,127,1,62,1,'172.18.0.1'),(379,2,'postfixadmin','{\"modifiedAt\":[\"2021-06-28 09:07:04\",\"2021-06-28 09:07:05\"]}','2021-06-28 09:07:05',1,126,1,13,35,'172.18.0.1'),(380,1,'GO\\Email\\Model\\Account','{\"user_id\":1,\"acl_id\":130,\"port\":143,\"deprecated_use_ssl\":0,\"novalidate_cert\":0,\"imap_allow_self_signed\":0,\"mbroot\":\"\",\"sent\":\"Sent\",\"drafts\":\"Drafts\",\"trash\":\"Trash\",\"spam\":\"Spam\",\"smtp_allow_self_signed\":0,\"smtp_password\":\"\",\"password_encrypted\":2,\"ignore_sent_folder\":0,\"sieve_usetls\":1,\"do_not_mark_as_read\":0,\"signature_below_reply\":0,\"full_reply_headers\":0,\"check_mailboxes\":\"INBOX\",\"sieve_port\":4190,\"id\":1,\"host\":\"mailserver\",\"username\":\"admin@intermesh.localhost\",\"password\":\"{GOCRYPT2}def502002f7c451276728ebbacf4fd39175217766bc37e6a3ec186050a580ffd717b50e1bf8239b8345d3a28a6792812db55ee1cffb47e0b5fd1023bf00e66539d223056bb25234114e64b83d5d35dc14e9bddbd660c8ba84ec776e363a8\",\"imap_encryption\":\"\",\"smtp_host\":\"mailserver\",\"smtp_port\":25,\"smtp_encryption\":\"\",\"smtp_username\":\"\"}','2021-06-28 09:14:18',1,130,1,63,1,'172.19.0.1'),(381,1,'Admin','{\"default\":1,\"account_id\":1,\"email\":\"admin@intermesh.localhost\",\"name\":\"Admin\",\"signature\":\"\",\"id\":1}','2021-06-28 09:14:19',1,130,0,64,1,'172.19.0.1'),(382,1,'admin','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"admin\",\"parent_id\":21,\"mtime\":1624875078,\"ctime\":1624875078,\"id\":29}','2021-06-28 10:11:18',1,24,1,39,29,'172.19.0.1'),(383,2,'admin','{\"acl_id\":[0,131],\"visible\":[0,1],\"readonly\":[0,1]}','2021-06-28 10:11:18',1,131,1,39,29,'172.19.0.1'),(384,1,'log','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"log\",\"parent_id\":0,\"mtime\":1624875078,\"ctime\":1624875078,\"id\":30}','2021-06-28 10:11:18',1,24,1,39,30,'172.19.0.1'),(385,1,'users/admin/Re change HS code RB\'s - 18-06-2021 0858.eml','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624875088,\"mtime\":1624875087,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":29,\"name\":\"Re change HS code RB\'s - 18-06-2021 0858.eml\",\"extension\":\"eml\",\"size\":17653,\"id\":8}','2021-06-28 10:11:28',1,131,0,38,8,'172.19.0.1'),(386,2,'System Administrator','{\"disk_usage\":[61361,79014],\"modifiedAt\":[\"2021-06-28 09:04:55\",\"2021-06-28 10:11:28\"]}','2021-06-28 10:11:28',1,4,0,21,1,'172.19.0.1'),(387,1,'info','{\"id\":5,\"addressBookId\":1,\"firstName\":\"info\",\"name\":\"info\",\"language\":\"en_uk\",\"uid\":\"5@host.docker.internal:8080\",\"uri\":\"5@host.docker.internal:8080.vcf\",\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@indonesiahijau.co.id\"}]}','2021-06-28 10:24:29',1,11,0,24,5,'172.19.0.1'),(388,2,'System Administrator','{\"lastLogin\":[\"2021-06-28T11:28:40+00:00\",\"2021-06-28T08:53:59+00:00\"],\"loginCount\":[2,1],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"projectsSettings\":[{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":1},null]}','2021-06-28 11:28:40',1,5,0,21,1,'172.20.0.1'),(389,4,'admin [172.20.0.1]',NULL,'2021-06-28 11:28:40',1,5,0,21,1,'172.20.0.1'),(390,1,'users/admin/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1624885909,\"mtime\":1624885909,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":29,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":8205,\"id\":9}','2021-06-28 13:11:49',1,131,0,38,9,'172.20.0.1'),(391,2,'System Administrator','{\"disk_usage\":[79014,87219],\"modifiedAt\":[\"2021-06-28 11:28:40\",\"2021-06-28 13:11:49\"]}','2021-06-28 13:11:49',1,4,0,21,1,'172.20.0.1'),(392,1,'GO\\Files\\Model\\Version','{\"version\":1,\"size_bytes\":8205,\"user_id\":1,\"file_id\":9,\"mtime\":1624885909,\"path\":\"versioning\\/9\\/20210628_151149_Test.odt\",\"id\":1}','2021-06-28 13:14:20',1,131,0,65,1,'172.20.0.1'),(393,2,'System Administrator','{\"disk_usage\":[87219,95424],\"modifiedAt\":[\"2021-06-28 13:11:49\",\"2021-06-28 13:14:20\"]}','2021-06-28 13:14:20',1,4,0,21,1,'172.20.0.1'),(394,2,'users/admin/Test.odt','{\"version\":[1,2],\"size\":[8205,4542]}','2021-06-28 13:14:20',1,131,0,38,9,'172.20.0.1'),(395,2,'System Administrator','{\"disk_usage\":[95424,91761]}','2021-06-28 13:14:20',1,4,0,21,1,'172.20.0.1'),(396,1,'GO\\Files\\Model\\Version','{\"version\":2,\"size_bytes\":4542,\"user_id\":1,\"file_id\":9,\"mtime\":1624886060,\"path\":\"versioning\\/9\\/20210628_151420_Test.odt\",\"id\":2}','2021-06-28 14:07:54',1,131,0,65,2,'172.20.0.1'),(397,2,'System Administrator','{\"disk_usage\":[91761,96303],\"modifiedAt\":[\"2021-06-28 13:14:20\",\"2021-06-28 14:07:54\"]}','2021-06-28 14:07:54',1,4,0,21,1,'172.20.0.1'),(398,2,'users/admin/Test.odt','{\"version\":[2,3],\"size\":[4542,4485]}','2021-06-28 14:07:54',1,131,0,38,9,'172.20.0.1'),(399,2,'System Administrator','{\"disk_usage\":[96303,96246]}','2021-06-28 14:07:54',1,4,0,21,1,'172.20.0.1'),(400,1,'Admin','{\"id\":6,\"addressBookId\":1,\"firstName\":\"Admin\",\"name\":\"Admin\",\"language\":\"en_uk\",\"uid\":\"6@host.docker.internal:8080\",\"uri\":\"6@host.docker.internal:8080.vcf\",\"emailAddresses\":[{\"type\":\"work\",\"email\":\"admin@intermesh.localhost\"}]}','2021-06-28 15:10:14',1,11,0,24,6,'172.20.0.1'),(401,1,'Test','{\"id\":1,\"entityId\":24,\"name\":\"Test\",\"filter\":\"[]\",\"entity\":\"Contact\",\"setAcl\":{\"2\":10}}','2021-06-28 15:10:34',1,133,1,10,1,'172.20.0.1'),(402,1,'Only in shared','{\"id\":1,\"fieldSetId\":1,\"name\":\"Only in shared\",\"databaseName\":\"Only_in_shared\",\"relatedFieldCondition\":\"addressBookId = Shared\",\"options\":\"{\\\"maxLength\\\":50}\",\"forceAlterTable\":true}','2021-06-28 15:10:59',1,133,0,9,1,'172.20.0.1'),(403,2,'Only in shared','{\"conditionallyHidden\":[true,false],\"forceAlterTable\":[true,false]}','2021-06-28 15:11:50',1,133,0,9,1,'172.20.0.1'),(404,1,'Jantje Beton','{\"id\":7,\"addressBookId\":1,\"firstName\":\"Jantje\",\"lastName\":\"Beton\",\"name\":\"Jantje Beton\",\"language\":\"en_uk\",\"uid\":\"7@host.docker.internal:8080\",\"uri\":\"7@host.docker.internal:8080.vcf\"}','2021-06-28 15:12:24',1,11,0,24,7,'172.20.0.1'),(405,5,'admin [172.20.0.1]',NULL,'2021-06-29 08:32:06',1,5,0,21,1,'172.20.0.1'),(406,2,'System Administrator','{\"lastLogin\":[\"2021-06-29T08:32:57+00:00\",\"2021-06-28T11:28:40+00:00\"],\"loginCount\":[3,2],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-06-29 08:32:57',1,5,0,21,1,'172.20.0.1'),(407,4,'admin [172.20.0.1]',NULL,'2021-06-29 08:32:58',1,5,0,21,1,'172.20.0.1'),(408,5,'admin [172.20.0.1]',NULL,'2021-06-29 08:33:19',1,5,0,21,1,'172.20.0.1'),(409,2,'System Administrator','{\"lastLogin\":[\"2021-06-29T08:34:46+00:00\",\"2021-06-29T08:32:57+00:00\"],\"loginCount\":[4,3],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-06-29 08:34:47',1,5,0,21,1,'172.20.0.1'),(410,4,'admin [172.20.0.1]',NULL,'2021-06-29 08:34:47',1,5,0,21,1,'172.20.0.1'),(411,5,'admin [172.20.0.1]',NULL,'2021-06-29 08:36:44',1,5,0,21,1,'172.20.0.1'),(412,2,'core','{\"version\":[240,239]}','2021-07-01 07:33:12',1,5,1,13,1,'172.20.0.1'),(413,2,'System Administrator','{\"lastLogin\":[\"2021-07-01T07:33:23+00:00\",\"2021-06-29T08:34:46+00:00\"],\"loginCount\":[5,4],\"language\":[\"en\",\"en_uk\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-01 07:33:23',1,5,0,21,1,'127.0.0.1'),(414,4,'admin [127.0.0.1]',NULL,'2021-07-01 07:33:24',1,5,0,21,1,'127.0.0.1'),(415,2,'System Administrator','{\"lastLogin\":[\"2021-07-01T07:33:41+00:00\",\"2021-07-01T07:33:23+00:00\"],\"loginCount\":[6,5],\"language\":[\"en_uk\",\"en\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-01 07:33:41',1,5,0,21,1,'172.20.0.1'),(416,4,'admin [172.20.0.1]',NULL,'2021-07-01 07:33:41',1,5,0,21,1,'172.20.0.1'),(417,2,'System Administrator','{\"lastLogin\":[\"2021-07-06T08:27:39+00:00\",\"2021-07-01T07:33:41+00:00\"],\"loginCount\":[7,6],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-06 08:27:39',1,5,0,21,1,'172.20.0.1'),(418,4,'admin [172.20.0.1]',NULL,'2021-07-06 08:27:39',1,5,0,21,1,'172.20.0.1'),(419,2,'System Administrator','{\"lastLogin\":[\"2021-07-06T08:45:45+00:00\",\"2021-07-06T08:27:39+00:00\"],\"loginCount\":[8,7],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-06 08:45:45',1,5,0,21,1,'172.20.0.1'),(420,4,'admin [172.20.0.1]',NULL,'2021-07-06 08:45:45',1,5,0,21,1,'172.20.0.1'),(421,2,'core','{\"version\":[241,240]}','2021-07-06 08:56:55',1,5,1,13,1,'172.20.0.1'),(422,2,'System Administrator','{\"lastLogin\":[\"2021-07-06T08:57:03+00:00\",\"2021-07-06T08:45:45+00:00\"],\"loginCount\":[9,8],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-06 08:57:03',1,5,0,21,1,'172.20.0.1'),(423,4,'admin [172.20.0.1]',NULL,'2021-07-06 08:57:03',1,5,0,21,1,'172.20.0.1'),(424,2,'Only in shared','{\"relatedFieldCondition\":[\"addressBookId != B\\u00e4rbel Paulus AND addressBookId != Clients AND addressBookId != Consultants AND addressBookId != Denis Ceyra Yildiz AND addressBookId != Ileana Clapham AND addressBookId != Intern AND addressBookId != Kent Pacheco AND addressBookId != Makler AND addressBookId != Maria Laura Caruano AND addressBookId != Michael Winter AND addressBookId != Nicole Clapham AND addressBookId != Nina Claire T\\u00e4ubrich AND addressBookId != Nina Volk AND addressBookId != Petra Stein AND addressBookId != Roman Clapham AND addressBookId != Sonstige\",\"addressBookId = Shared\"],\"forceAlterTable\":[true,false]}','2021-07-06 08:57:25',1,133,0,9,1,'172.20.0.1'),(425,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T09:21:47+00:00\",\"2021-07-06T08:57:03+00:00\"],\"loginCount\":[10,9],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:21:47',1,5,0,21,1,'172.20.0.1'),(426,4,'admin [172.20.0.1]',NULL,'2021-07-08 09:21:47',1,5,0,21,1,'172.20.0.1'),(427,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"JX3W2DFXRITN6MDO\",\"isEnabled\":false,\"qrBlobId\":\"cf2e7ff5f031bf9a625b8e4afb62ca49180f61d1\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"syncSettings\":[{\"user_id\":1,\"account_id\":1,\"noteBooks\":[{\"userId\":1,\"noteBookId\":65,\"isDefault\":true}],\"addressBooks\":[{\"userId\":1,\"addressBookId\":1,\"isDefault\":true}]},null]}','2021-07-08 09:21:48',1,5,0,21,1,'172.20.0.1'),(428,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"KNPXONIWABCGEYLV\",\"isEnabled\":false,\"qrBlobId\":\"d33cd8eff76b8163b182e19388c71083a772288b\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:22:06',1,5,0,21,1,'172.20.0.1'),(429,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"SDB2JJYIPRJUIJC7\",\"isEnabled\":false,\"qrBlobId\":\"353aae95cd75caef59af507ea1a7f60a9806b051\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:22:14',1,5,0,21,1,'172.20.0.1'),(430,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"W4T6EJIUABMEG3D3\",\"isEnabled\":false,\"qrBlobId\":\"e1cf4a405c10972a531ad599a340efee863d3deb\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:23:45',1,5,0,21,1,'172.20.0.1'),(431,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"7HHDPGHBHQGEODPT\",\"isEnabled\":false,\"qrBlobId\":\"c447e83b73d5dd205ae2933141c3c42da3085bb8\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:23:54',1,5,0,21,1,'172.20.0.1'),(432,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"RV4CJ5NPJS44CTWR\",\"isEnabled\":false,\"qrBlobId\":\"8c8c582a29ad5ee0cbc5758fd4f1a33ee5f7a659\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:24:03',1,5,0,21,1,'172.20.0.1'),(433,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"IEOHU2AVFU45Y5Z5\",\"isEnabled\":false,\"qrBlobId\":\"2546fa903a0ed0dbf8a9423abcb4e81ca1fe2cf7\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:24:47',1,5,0,21,1,'172.20.0.1'),(434,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"AR242QU3D2X5IUTU\",\"isEnabled\":false,\"qrBlobId\":\"a0de04459ad7e5fe2fe706b587aaaee9e521e57c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:25:04',1,5,0,21,1,'172.20.0.1'),(435,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"AR242QU3D2X5IUTU\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":1,\"createdAt\":\"2021-07-08T09:26:35+00:00\"},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 09:26:35',1,5,0,21,1,'172.20.0.1'),(436,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[null,{\"secret\":\"AR242QU3D2X5IUTU\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":1,\"createdAt\":\"2021-07-08T09:26:35+00:00\"}],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 10:48:15',1,5,0,21,1,'172.20.0.1'),(437,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 10:48:20',1,5,0,21,1,'172.20.0.1'),(438,5,'admin [172.20.0.1]',NULL,'2021-07-08 10:48:25',1,5,0,21,1,'172.20.0.1'),(439,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T10:48:30+00:00\",\"2021-07-08T09:21:47+00:00\"],\"loginCount\":[11,10],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 10:48:30',1,5,0,21,1,'172.20.0.1'),(440,4,'admin [172.20.0.1]',NULL,'2021-07-08 10:48:30',1,5,0,21,1,'172.20.0.1'),(441,5,'admin [172.20.0.1]',NULL,'2021-07-08 10:48:34',1,5,0,21,1,'172.20.0.1'),(442,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T10:48:40+00:00\",null],\"loginCount\":[1,0],\"language\":[\"en_uk\",\"en\"],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"projectsSettings\":[{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":2},null]}','2021-07-08 10:48:40',1,5,0,21,2,'172.20.0.1'),(443,4,'elmer [172.20.0.1]',NULL,'2021-07-08 10:48:40',1,5,0,21,2,'172.20.0.1'),(444,5,'elmer [172.20.0.1]',NULL,'2021-07-08 10:48:46',2,5,0,21,2,'172.20.0.1'),(445,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T10:49:01+00:00\",\"2021-07-08T10:48:40+00:00\"],\"loginCount\":[2,1],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 10:49:01',1,5,0,21,2,'172.20.0.1'),(446,4,'elmer [172.20.0.1]',NULL,'2021-07-08 10:49:01',1,5,0,21,2,'172.20.0.1'),(447,5,'elmer [172.20.0.1]',NULL,'2021-07-08 10:49:32',2,5,0,21,2,'172.20.0.1'),(448,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T10:49:39+00:00\",\"2021-07-08T10:49:01+00:00\"],\"loginCount\":[3,2],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 10:49:39',1,5,0,21,2,'172.20.0.1'),(449,4,'elmer [172.20.0.1]',NULL,'2021-07-08 10:49:39',1,5,0,21,2,'172.20.0.1'),(450,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:00:52',2,5,0,21,2,'172.20.0.1'),(451,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:00:58+00:00\",\"2021-07-08T10:49:39+00:00\"],\"loginCount\":[4,3],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:00:58',1,5,0,21,2,'172.20.0.1'),(452,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:00:58',1,5,0,21,2,'172.20.0.1'),(453,6,'elmer [172.20.0.1]',NULL,'2021-07-08 11:01:02',2,134,0,21,NULL,'172.20.0.1'),(454,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:01:18',2,5,0,21,2,'172.20.0.1'),(455,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:01:22+00:00\",\"2021-07-08T11:00:58+00:00\"],\"loginCount\":[5,4],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:01:22',1,5,0,21,2,'172.20.0.1'),(456,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:01:22',1,5,0,21,2,'172.20.0.1'),(457,6,'elmer [172.20.0.1]',NULL,'2021-07-08 11:30:50',2,135,0,21,NULL,'172.20.0.1'),(458,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:30:56',2,5,0,21,2,'172.20.0.1'),(459,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:31:04+00:00\",\"2021-07-08T11:01:22+00:00\"],\"loginCount\":[6,5],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:31:04',1,5,0,21,2,'172.20.0.1'),(460,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:31:04',1,5,0,21,2,'172.20.0.1'),(461,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"HPUIMODHX4CD26A2\",\"isEnabled\":false,\"qrBlobId\":\"3cc2d7e9be0723dae3b675868d95a45e5f121ce4\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:31:29',2,5,0,21,2,'172.20.0.1'),(462,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:32:25',2,5,0,21,2,'172.20.0.1'),(463,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:32:30+00:00\",\"2021-07-08T11:31:04+00:00\"],\"loginCount\":[7,6],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:32:30',1,5,0,21,2,'172.20.0.1'),(464,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:32:30',1,5,0,21,2,'172.20.0.1'),(465,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"QAJEZ663F2WWVAW5\",\"isEnabled\":false,\"qrBlobId\":\"2403e2acf267d76124d804257912def45939f137\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:32:34',2,5,0,21,2,'172.20.0.1'),(466,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"D2ZBQCGV3OZXGNAV\",\"isEnabled\":false,\"qrBlobId\":\"0042e11fc31f21d05883b39d9b56944e72102c2b\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:37:03',2,5,0,21,2,'172.20.0.1'),(467,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:37:13',2,5,0,21,2,'172.20.0.1'),(468,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:37:19+00:00\",\"2021-07-08T11:32:30+00:00\"],\"loginCount\":[8,7],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:37:19',1,5,0,21,2,'172.20.0.1'),(469,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:37:19',1,5,0,21,2,'172.20.0.1'),(470,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"AZ2JDXZQEPTRRT7O\",\"isEnabled\":false,\"qrBlobId\":\"f599804d7f1ecc6e1de4783fc2ed962e1aa83f28\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:37:22',2,5,0,21,2,'172.20.0.1'),(471,6,'elmer [172.20.0.1]',NULL,'2021-07-08 11:37:41',2,136,0,21,NULL,'172.20.0.1'),(472,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"Z4PKJANYBLO6XQT5\",\"isEnabled\":false,\"qrBlobId\":\"c084d05eb19ccd2c350a92ac3ebd1f15c618fc5c\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:37:44',2,5,0,21,2,'172.20.0.1'),(473,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"Z4PKJANYBLO6XQT5\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":2,\"createdAt\":\"2021-07-08T11:38:11+00:00\"},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:38:12',2,5,0,21,2,'172.20.0.1'),(474,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[null,{\"secret\":\"Z4PKJANYBLO6XQT5\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":2,\"createdAt\":\"2021-07-08T11:38:11+00:00\"}],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:38:20',2,5,0,21,2,'172.20.0.1'),(475,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"FEZKTXRSH5DQZISP\",\"isEnabled\":false,\"qrBlobId\":\"fd251682ed7b743e9e1c8b258cedcb7f08137a30\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:38:58',2,5,0,21,2,'172.20.0.1'),(476,6,'elmer [172.20.0.1]',NULL,'2021-07-08 11:39:06',2,137,0,21,NULL,'172.20.0.1'),(477,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"POX2FI5UAY2AQGXP\",\"isEnabled\":false,\"qrBlobId\":\"e574cfe167c1339671cd6798c654525aa10332f1\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:39:09',2,5,0,21,2,'172.20.0.1'),(478,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"POX2FI5UAY2AQGXP\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":2,\"createdAt\":\"2021-07-08T11:39:36+00:00\"},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:39:36',2,5,0,21,2,'172.20.0.1'),(479,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:39:40',2,5,0,21,2,'172.20.0.1'),(480,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:39:48',2,5,0,21,2,'172.20.0.1'),(481,6,'demo [172.20.0.1]',NULL,'2021-07-08 11:39:54',1,138,0,21,NULL,'172.20.0.1'),(482,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T11:40:05+00:00\",\"2021-07-08T11:37:19+00:00\"],\"loginCount\":[9,8],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:40:05',1,5,0,21,2,'172.20.0.1'),(483,4,'elmer [172.20.0.1]',NULL,'2021-07-08 11:40:05',1,5,0,21,2,'172.20.0.1'),(484,5,'elmer [172.20.0.1]',NULL,'2021-07-08 11:40:16',2,5,0,21,2,'172.20.0.1'),(485,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T11:40:20+00:00\",\"2021-07-08T10:48:30+00:00\"],\"loginCount\":[12,11],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 11:40:20',1,5,0,21,1,'172.20.0.1'),(486,4,'admin [172.20.0.1]',NULL,'2021-07-08 11:40:20',1,5,0,21,1,'172.20.0.1'),(487,5,'admin [172.20.0.1]',NULL,'2021-07-08 11:46:42',1,5,0,21,1,'172.20.0.1'),(488,6,'elmer [172.20.0.1]',NULL,'2021-07-08 12:22:30',1,5,0,21,2,'172.20.0.1'),(489,6,'elmer [172.20.0.1]',NULL,'2021-07-08 12:23:53',1,5,0,21,2,'172.20.0.1'),(490,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T12:24:38+00:00\",\"2021-07-08T11:40:20+00:00\"],\"loginCount\":[13,12],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:24:38',1,5,0,21,1,'172.20.0.1'),(491,4,'admin [172.20.0.1]',NULL,'2021-07-08 12:24:38',1,5,0,21,1,'172.20.0.1'),(492,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[null,{\"secret\":\"POX2FI5UAY2AQGXP\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":2,\"createdAt\":\"2021-07-08T11:39:36+00:00\"}],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:25:03',1,5,0,21,2,'172.20.0.1'),(493,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:25:07',1,5,0,21,2,'172.20.0.1'),(494,5,'admin [172.20.0.1]',NULL,'2021-07-08 12:25:14',1,5,0,21,1,'172.20.0.1'),(495,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T12:25:20+00:00\",\"2021-07-08T11:40:05+00:00\"],\"loginCount\":[10,9],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:25:20',1,5,0,21,2,'172.20.0.1'),(496,4,'elmer [172.20.0.1]',NULL,'2021-07-08 12:25:20',1,5,0,21,2,'172.20.0.1'),(497,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"QCAUS65Y3QKW3YYA\",\"isEnabled\":false,\"qrBlobId\":\"d391dbae1e0ce5e49b81b27f837955a9ae315144\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:25:24',2,5,0,21,2,'172.20.0.1'),(498,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"3PDHEXTMUPYFQNFC\",\"isEnabled\":false,\"qrBlobId\":\"372a99b65ad5ae080006a0b8b658508218b7ef08\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:30:00',2,5,0,21,2,'172.20.0.1'),(499,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"ZJ3JD74AAJCC7Z64\",\"isEnabled\":false,\"qrBlobId\":\"22566b2172047bdfe12d2229237afad619d6910b\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:30:38',2,5,0,21,2,'172.20.0.1'),(500,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"2FIQKOSIHLSDKHC3\",\"isEnabled\":false,\"qrBlobId\":\"ed36ab7d57d59e25ccfc0e9461c51ce623c724d2\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:31:06',2,5,0,21,2,'172.20.0.1'),(501,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"6E4UQRUBETIALHK2\",\"isEnabled\":false,\"qrBlobId\":\"8818051028c7efcab49daba2a8b0886ba3710c98\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:31:50',2,5,0,21,2,'172.20.0.1'),(502,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"7MIWTURWB2LNT5EN\",\"isEnabled\":false,\"qrBlobId\":\"aefaa4fd7d4cfc7608733122cb7ec244401501f3\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:32:21',2,5,0,21,2,'172.20.0.1'),(503,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"ZK42PN6XBHNBFKG2\",\"isEnabled\":false,\"qrBlobId\":\"4219c2e5a613b3b7c1bd1961c5eb85ad113b4d64\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:32:39',2,5,0,21,2,'172.20.0.1'),(504,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"L4BK3CKO3NSZYMSJ\",\"isEnabled\":false,\"qrBlobId\":\"619002860f5ce3771ae08775be99873227852e57\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:33:45',2,5,0,21,2,'172.20.0.1'),(505,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"RCSJWGS6QGQLW4PY\",\"isEnabled\":false,\"qrBlobId\":\"5e4ade0aacb88c7df7348f31f158b8874355042b\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:34:23',2,5,0,21,2,'172.20.0.1'),(506,6,'elmer [172.20.0.1]',NULL,'2021-07-08 12:35:53',2,139,0,21,NULL,'172.20.0.1'),(507,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"BUTCM5D6AGEDZOAX\",\"isEnabled\":false,\"qrBlobId\":\"2b57e6747ec57bbba9f2463d11cb12de5efa777e\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:35:56',2,5,0,21,2,'172.20.0.1'),(508,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"O7EP5EESNEGOTNPD\",\"isEnabled\":false,\"qrBlobId\":\"fe7d60bd352a8baa25213b5f324e3bbfc643a880\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:36:08',2,5,0,21,2,'172.20.0.1'),(509,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"VPWN7UIBAW4NAEKY\",\"isEnabled\":false,\"qrBlobId\":\"ef97ee26a94fbeae2985f3b644704457d94f6027\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:36:52',2,5,0,21,2,'172.20.0.1'),(510,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"N2NRBAAPSHT3NZ6K\",\"isEnabled\":false,\"qrBlobId\":\"01168888ff62ccf6577c7e4a799771e78722db28\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:37:28',2,5,0,21,2,'172.20.0.1'),(511,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"IX7FW7VGHMK4EUGN\",\"isEnabled\":false,\"qrBlobId\":\"76eb251bfadd23ce9a975465a83f8bbf07e89f37\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:38:00',2,5,0,21,2,'172.20.0.1'),(512,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"JSAIQ4TNU2BGLBAT\",\"isEnabled\":false,\"qrBlobId\":\"d44383427cea43dc2bf2249cdf629400fe31bd60\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:38:45',2,5,0,21,2,'172.20.0.1'),(513,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"2KSDQUSBKETL7WCP\",\"isEnabled\":false,\"qrBlobId\":\"9b2763f9370836ad539ee0258e65973d2e69652f\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:40:24',2,5,0,21,2,'172.20.0.1'),(514,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"HRNAHWXOZRLE6BRE\",\"isEnabled\":false,\"qrBlobId\":\"f891bc600b7b89d71eae56e643b6e649592d3156\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:41:08',2,5,0,21,2,'172.20.0.1'),(515,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"BBYCB47FIBQEV6EB\",\"isEnabled\":false,\"qrBlobId\":\"64355b403703bb013cf3d4c994d65abdaa8da701\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:44:42',2,5,0,21,2,'172.20.0.1'),(516,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"NZDRQ2QRET5PUHFK\",\"isEnabled\":false,\"qrBlobId\":\"818bb127e8f74e0398176f1cd1307aae9729e623\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:47:20',2,5,0,21,2,'172.20.0.1'),(517,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"DBL6DEILSGBJ47WQ\",\"isEnabled\":false,\"qrBlobId\":\"3901ba55e50a825c96482a287659ea91dc82f34a\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:48:25',2,5,0,21,2,'172.20.0.1'),(518,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"DHTY4ZTCNLGDGBPC\",\"isEnabled\":false,\"qrBlobId\":\"a60a1a14802b78bc7e4b961ee71ef3a98aa27675\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 12:49:19',2,5,0,21,2,'172.20.0.1'),(519,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-08T13:05:23+00:00\",\"2021-07-08T12:25:20+00:00\"],\"loginCount\":[11,10],\"language\":[\"nl\",\"en_uk\"],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:05:23',1,5,0,21,2,'172.20.0.1'),(520,4,'elmer [172.20.0.1]',NULL,'2021-07-08 13:05:23',1,5,0,21,2,'172.20.0.1'),(521,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"NCC3ULAINYB4BQTH\",\"isEnabled\":false,\"qrBlobId\":\"b67fd67c3ed05cc6f5f7a933b82df39250d2e220\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:05:26',2,5,0,21,2,'172.20.0.1'),(522,5,'elmer [172.20.0.1]',NULL,'2021-07-08 13:05:56',2,5,0,21,2,'172.20.0.1'),(523,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T13:06:06+00:00\",\"2021-07-08T12:24:38+00:00\"],\"loginCount\":[14,13],\"language\":[\"nl\",\"en_uk\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:06:06',1,5,0,21,1,'172.20.0.1'),(524,4,'admin [172.20.0.1]',NULL,'2021-07-08 13:06:06',1,5,0,21,1,'172.20.0.1'),(525,1,'elmer','{\"user_id\":2,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":2,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"elmer\",\"parent_id\":21,\"mtime\":1625749590,\"ctime\":1625749590,\"id\":31}','2021-07-08 13:06:30',2,24,1,39,31,'172.20.0.1'),(526,2,'users','{\"muser_id\":[1,2]}','2021-07-08 13:06:30',2,24,1,39,21,'172.20.0.1'),(527,2,'elmer','{\"acl_id\":[0,140],\"visible\":[0,1],\"readonly\":[0,1]}','2021-07-08 13:06:31',2,140,1,39,31,'172.20.0.1'),(528,5,'elmer [172.20.0.1]',NULL,'2021-07-08 13:06:34',2,5,0,21,2,'172.20.0.1'),(529,1,'tmp','{\"user_id\":1,\"visible\":0,\"acl_id\":24,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"tmp\",\"parent_id\":0,\"mtime\":1625749637,\"ctime\":1625749637,\"id\":32}','2021-07-08 13:07:17',1,24,1,39,32,'172.20.0.1'),(530,1,'1','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":0,\"apply_state\":0,\"name\":\"1\",\"parent_id\":32,\"mtime\":1625749637,\"ctime\":1625749637,\"id\":33}','2021-07-08 13:07:17',1,24,1,39,33,'172.20.0.1'),(531,2,'1','{\"acl_id\":[0,141]}','2021-07-08 13:07:17',1,141,1,39,33,'172.20.0.1'),(532,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749637,\"mtime\":1625749637,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":10}','2021-07-08 13:07:17',1,141,0,38,10,'172.20.0.1'),(533,2,'System Administrator','{\"disk_usage\":[96246,290369],\"modifiedAt\":[\"2021-07-08 13:06:06\",\"2021-07-08 13:07:17\"]}','2021-07-08 13:07:17',1,4,0,21,1,'172.20.0.1'),(534,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:08:21',1,141,0,38,10,'172.20.0.1'),(535,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:07:17\",\"2021-07-08 13:08:21\"]}','2021-07-08 13:08:21',1,4,0,21,1,'172.20.0.1'),(536,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749701,\"mtime\":1625749701,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":11}','2021-07-08 13:08:21',1,141,0,38,11,'172.20.0.1'),(537,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:08:21',1,4,0,21,1,'172.20.0.1'),(538,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:08:23',1,141,0,38,11,'172.20.0.1'),(539,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:08:21\",\"2021-07-08 13:08:23\"]}','2021-07-08 13:08:23',1,4,0,21,1,'172.20.0.1'),(540,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749703,\"mtime\":1625749703,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":12}','2021-07-08 13:08:23',1,141,0,38,12,'172.20.0.1'),(541,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:08:23',1,4,0,21,1,'172.20.0.1'),(542,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:08:24',1,141,0,38,12,'172.20.0.1'),(543,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:08:23\",\"2021-07-08 13:08:24\"]}','2021-07-08 13:08:24',1,4,0,21,1,'172.20.0.1'),(544,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749704,\"mtime\":1625749704,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":13}','2021-07-08 13:08:24',1,141,0,38,13,'172.20.0.1'),(545,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:08:24',1,4,0,21,1,'172.20.0.1'),(546,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:08:26',1,141,0,38,13,'172.20.0.1'),(547,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:08:24\",\"2021-07-08 13:08:26\"]}','2021-07-08 13:08:26',1,4,0,21,1,'172.20.0.1'),(548,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749706,\"mtime\":1625749706,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":14}','2021-07-08 13:08:26',1,141,0,38,14,'172.20.0.1'),(549,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:08:26',1,4,0,21,1,'172.20.0.1'),(550,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:08:27',1,141,0,38,14,'172.20.0.1'),(551,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:08:26\",\"2021-07-08 13:08:27\"]}','2021-07-08 13:08:27',1,4,0,21,1,'172.20.0.1'),(552,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749707,\"mtime\":1625749707,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":15}','2021-07-08 13:08:27',1,141,0,38,15,'172.20.0.1'),(553,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:08:27',1,4,0,21,1,'172.20.0.1'),(554,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:09:33',1,141,0,38,15,'172.20.0.1'),(555,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:08:27\",\"2021-07-08 13:09:33\"]}','2021-07-08 13:09:33',1,4,0,21,1,'172.20.0.1'),(556,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749773,\"mtime\":1625749773,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":16}','2021-07-08 13:09:33',1,141,0,38,16,'172.20.0.1'),(557,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:09:33',1,4,0,21,1,'172.20.0.1'),(558,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:09:35',1,141,0,38,16,'172.20.0.1'),(559,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:09:33\",\"2021-07-08 13:09:35\"]}','2021-07-08 13:09:35',1,4,0,21,1,'172.20.0.1'),(560,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749775,\"mtime\":1625749775,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":17}','2021-07-08 13:09:35',1,141,0,38,17,'172.20.0.1'),(561,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:09:35',1,4,0,21,1,'172.20.0.1'),(562,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:10:04',1,141,0,38,17,'172.20.0.1'),(563,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:09:35\",\"2021-07-08 13:10:04\"]}','2021-07-08 13:10:04',1,4,0,21,1,'172.20.0.1'),(564,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749804,\"mtime\":1625749804,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":18}','2021-07-08 13:10:04',1,141,0,38,18,'172.20.0.1'),(565,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:10:04',1,4,0,21,1,'172.20.0.1'),(566,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:10:06',1,141,0,38,18,'172.20.0.1'),(567,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:10:04\",\"2021-07-08 13:10:06\"]}','2021-07-08 13:10:06',1,4,0,21,1,'172.20.0.1'),(568,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749806,\"mtime\":1625749806,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":19}','2021-07-08 13:10:06',1,141,0,38,19,'172.20.0.1'),(569,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:10:06',1,4,0,21,1,'172.20.0.1'),(570,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:10:07',1,141,0,38,19,'172.20.0.1'),(571,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:10:06\",\"2021-07-08 13:10:07\"]}','2021-07-08 13:10:07',1,4,0,21,1,'172.20.0.1'),(572,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749807,\"mtime\":1625749807,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":20}','2021-07-08 13:10:07',1,141,0,38,20,'172.20.0.1'),(573,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:10:07',1,4,0,21,1,'172.20.0.1'),(574,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:11:58',1,141,0,38,20,'172.20.0.1'),(575,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:10:07\",\"2021-07-08 13:11:58\"]}','2021-07-08 13:11:58',1,4,0,21,1,'172.20.0.1'),(576,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749918,\"mtime\":1625749918,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":21}','2021-07-08 13:11:58',1,141,0,38,21,'172.20.0.1'),(577,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:11:58',1,4,0,21,1,'172.20.0.1'),(578,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T13:12:33+00:00\",\"2021-07-08T13:06:06+00:00\"],\"loginCount\":[15,14],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:12:33',1,5,0,21,1,'172.20.0.1'),(579,4,'admin [172.20.0.1]',NULL,'2021-07-08 13:12:33',1,5,0,21,1,'172.20.0.1'),(580,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:12:38',1,141,0,38,21,'172.20.0.1'),(581,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:12:33\",\"2021-07-08 13:12:38\"]}','2021-07-08 13:12:38',1,4,0,21,1,'172.20.0.1'),(582,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749958,\"mtime\":1625749958,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":22}','2021-07-08 13:12:38',1,141,0,38,22,'172.20.0.1'),(583,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:12:38',1,4,0,21,1,'172.20.0.1'),(584,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:12:39',1,141,0,38,22,'172.20.0.1'),(585,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:12:38\",\"2021-07-08 13:12:39\"]}','2021-07-08 13:12:39',1,4,0,21,1,'172.20.0.1'),(586,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625749959,\"mtime\":1625749959,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":23}','2021-07-08 13:12:39',1,141,0,38,23,'172.20.0.1'),(587,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:12:39',1,4,0,21,1,'172.20.0.1'),(588,6,'admin [172.20.0.1]',NULL,'2021-07-08 13:17:43',1,142,0,21,NULL,'172.20.0.1'),(589,6,'admin [172.20.0.1]',NULL,'2021-07-08 13:17:51',1,143,0,21,NULL,'172.20.0.1'),(590,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T13:18:00+00:00\",\"2021-07-08T13:12:33+00:00\"],\"loginCount\":[16,15],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:18:00',1,5,0,21,1,'172.20.0.1'),(591,4,'admin [172.20.0.1]',NULL,'2021-07-08 13:18:00',1,5,0,21,1,'172.20.0.1'),(592,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:25:11',1,141,0,38,23,'172.20.0.1'),(593,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:18:00\",\"2021-07-08 13:25:11\"]}','2021-07-08 13:25:11',1,4,0,21,1,'172.20.0.1'),(594,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625750711,\"mtime\":1625750711,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":24}','2021-07-08 13:25:12',1,141,0,38,24,'172.20.0.1'),(595,2,'System Administrator','{\"disk_usage\":[96246,290369],\"modifiedAt\":[\"2021-07-08 13:25:11\",\"2021-07-08 13:25:12\"]}','2021-07-08 13:25:12',1,4,0,21,1,'172.20.0.1'),(596,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:25:25',1,141,0,38,24,'172.20.0.1'),(597,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:25:12\",\"2021-07-08 13:25:25\"]}','2021-07-08 13:25:26',1,4,0,21,1,'172.20.0.1'),(598,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625750726,\"mtime\":1625750726,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":25}','2021-07-08 13:25:26',1,141,0,38,25,'172.20.0.1'),(599,2,'System Administrator','{\"disk_usage\":[96246,290369],\"modifiedAt\":[\"2021-07-08 13:25:25\",\"2021-07-08 13:25:26\"]}','2021-07-08 13:25:26',1,4,0,21,1,'172.20.0.1'),(600,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:26:14',1,141,0,38,25,'172.20.0.1'),(601,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:25:26\",\"2021-07-08 13:26:14\"]}','2021-07-08 13:26:14',1,4,0,21,1,'172.20.0.1'),(602,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625750774,\"mtime\":1625750774,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":26}','2021-07-08 13:26:14',1,141,0,38,26,'172.20.0.1'),(603,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:26:14',1,4,0,21,1,'172.20.0.1'),(604,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:53:32',1,141,0,38,26,'172.20.0.1'),(605,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:26:14\",\"2021-07-08 13:53:32\"]}','2021-07-08 13:53:32',1,4,0,21,1,'172.20.0.1'),(606,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752412,\"mtime\":1625752412,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":27}','2021-07-08 13:53:32',1,141,0,38,27,'172.20.0.1'),(607,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:53:32',1,4,0,21,1,'172.20.0.1'),(608,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T13:54:28+00:00\",\"2021-07-08T13:18:00+00:00\"],\"loginCount\":[17,16],\"language\":[\"en_uk\",\"nl\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:54:28',1,5,0,21,1,'172.20.0.1'),(609,4,'admin [172.20.0.1]',NULL,'2021-07-08 13:54:28',1,5,0,21,1,'172.20.0.1'),(610,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:55:01',1,141,0,38,27,'172.20.0.1'),(611,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:54:28\",\"2021-07-08 13:55:01\"]}','2021-07-08 13:55:01',1,4,0,21,1,'172.20.0.1'),(612,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752502,\"mtime\":1625752502,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":28}','2021-07-08 13:55:02',1,141,0,38,28,'172.20.0.1'),(613,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-08 13:55:01\",\"2021-07-08 13:55:02\"]}','2021-07-08 13:55:02',1,4,0,21,1,'172.20.0.1'),(614,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 13:55:15',1,141,0,38,28,'172.20.0.1'),(615,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 13:55:02\",\"2021-07-08 13:55:15\"]}','2021-07-08 13:55:15',1,4,0,21,1,'172.20.0.1'),(616,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752515,\"mtime\":1625752515,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":29}','2021-07-08 13:55:15',1,141,0,38,29,'172.20.0.1'),(617,2,'System Administrator','{\"disk_usage\":[96246,290369],\"modifiedAt\":[\"2021-07-08 13:55:15\",\"2021-07-08 13:55:16\"]}','2021-07-08 13:55:16',1,4,0,21,1,'172.20.0.1'),(618,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:55:23',1,141,0,38,29,'172.20.0.1'),(619,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:55:16\",\"2021-07-08 13:55:23\"]}','2021-07-08 13:55:23',1,4,0,21,1,'172.20.0.1'),(620,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752523,\"mtime\":1625752523,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":30}','2021-07-08 13:55:23',1,141,0,38,30,'172.20.0.1'),(621,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:55:23',1,4,0,21,1,'172.20.0.1'),(622,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:55:31',1,141,0,38,30,'172.20.0.1'),(623,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:55:23\",\"2021-07-08 13:55:31\"]}','2021-07-08 13:55:31',1,4,0,21,1,'172.20.0.1'),(624,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752531,\"mtime\":1625752531,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":31}','2021-07-08 13:55:31',1,141,0,38,31,'172.20.0.1'),(625,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:55:31',1,4,0,21,1,'172.20.0.1'),(626,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T13:56:27+00:00\",\"2021-07-08T13:54:28+00:00\"],\"loginCount\":[18,17],\"language\":[\"en\",\"en_uk\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 13:56:27',1,5,0,21,1,'172.20.0.1'),(627,4,'admin [172.20.0.1]',NULL,'2021-07-08 13:56:27',1,5,0,21,1,'172.20.0.1'),(628,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 13:57:28',1,141,0,38,31,'172.20.0.1'),(629,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:56:27\",\"2021-07-08 13:57:28\"]}','2021-07-08 13:57:28',1,4,0,21,1,'172.20.0.1'),(630,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752649,\"mtime\":1625752649,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":32}','2021-07-08 13:57:29',1,141,0,38,32,'172.20.0.1'),(631,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-08 13:57:28\",\"2021-07-08 13:57:29\"]}','2021-07-08 13:57:29',1,4,0,21,1,'172.20.0.1'),(632,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 13:58:21',1,141,0,38,32,'172.20.0.1'),(633,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 13:57:29\",\"2021-07-08 13:58:21\"]}','2021-07-08 13:58:21',1,4,0,21,1,'172.20.0.1'),(634,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752701,\"mtime\":1625752701,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":33}','2021-07-08 13:58:21',1,141,0,38,33,'172.20.0.1'),(635,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 13:58:21',1,4,0,21,1,'172.20.0.1'),(636,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:02:34',1,141,0,38,33,'172.20.0.1'),(637,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 13:58:21\",\"2021-07-08 14:02:34\"]}','2021-07-08 14:02:34',1,4,0,21,1,'172.20.0.1'),(638,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625752954,\"mtime\":1625752954,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":34}','2021-07-08 14:02:34',1,141,0,38,34,'172.20.0.1'),(639,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:02:34',1,4,0,21,1,'172.20.0.1'),(640,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:03:50',1,141,0,38,34,'172.20.0.1'),(641,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:02:34\",\"2021-07-08 14:03:50\"]}','2021-07-08 14:03:50',1,4,0,21,1,'172.20.0.1'),(642,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753030,\"mtime\":1625753030,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":35}','2021-07-08 14:03:50',1,141,0,38,35,'172.20.0.1'),(643,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:03:50',1,4,0,21,1,'172.20.0.1'),(644,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:03:52',1,141,0,38,35,'172.20.0.1'),(645,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:03:50\",\"2021-07-08 14:03:52\"]}','2021-07-08 14:03:52',1,4,0,21,1,'172.20.0.1'),(646,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753032,\"mtime\":1625753032,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":36}','2021-07-08 14:03:52',1,141,0,38,36,'172.20.0.1'),(647,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:03:52',1,4,0,21,1,'172.20.0.1'),(648,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:03:55',1,141,0,38,36,'172.20.0.1'),(649,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:03:52\",\"2021-07-08 14:03:55\"]}','2021-07-08 14:03:55',1,4,0,21,1,'172.20.0.1'),(650,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753035,\"mtime\":1625753035,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":37}','2021-07-08 14:03:55',1,141,0,38,37,'172.20.0.1'),(651,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:03:55',1,4,0,21,1,'172.20.0.1'),(652,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:04:07',1,141,0,38,37,'172.20.0.1'),(653,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:03:55\",\"2021-07-08 14:04:07\"]}','2021-07-08 14:04:07',1,4,0,21,1,'172.20.0.1'),(654,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753047,\"mtime\":1625753047,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":38}','2021-07-08 14:04:07',1,141,0,38,38,'172.20.0.1'),(655,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:04:07',1,4,0,21,1,'172.20.0.1'),(656,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:04:18',1,141,0,38,38,'172.20.0.1'),(657,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:04:07\",\"2021-07-08 14:04:18\"]}','2021-07-08 14:04:18',1,4,0,21,1,'172.20.0.1'),(658,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753058,\"mtime\":1625753058,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":39}','2021-07-08 14:04:18',1,141,0,38,39,'172.20.0.1'),(659,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:04:18',1,4,0,21,1,'172.20.0.1'),(660,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:04:24',1,141,0,38,39,'172.20.0.1'),(661,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:04:18\",\"2021-07-08 14:04:24\"]}','2021-07-08 14:04:24',1,4,0,21,1,'172.20.0.1'),(662,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753064,\"mtime\":1625753064,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":40}','2021-07-08 14:04:24',1,141,0,38,40,'172.20.0.1'),(663,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:04:24',1,4,0,21,1,'172.20.0.1'),(664,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:04:32',1,141,0,38,40,'172.20.0.1'),(665,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:04:24\",\"2021-07-08 14:04:32\"]}','2021-07-08 14:04:32',1,4,0,21,1,'172.20.0.1'),(666,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753072,\"mtime\":1625753072,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":41}','2021-07-08 14:04:32',1,141,0,38,41,'172.20.0.1'),(667,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:04:32',1,4,0,21,1,'172.20.0.1'),(668,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:04:39',1,141,0,38,41,'172.20.0.1'),(669,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:04:32\",\"2021-07-08 14:04:39\"]}','2021-07-08 14:04:39',1,4,0,21,1,'172.20.0.1'),(670,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753079,\"mtime\":1625753079,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":42}','2021-07-08 14:04:39',1,141,0,38,42,'172.20.0.1'),(671,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:04:39',1,4,0,21,1,'172.20.0.1'),(672,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:05:01',1,141,0,38,42,'172.20.0.1'),(673,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:04:39\",\"2021-07-08 14:05:01\"]}','2021-07-08 14:05:01',1,4,0,21,1,'172.20.0.1'),(674,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753101,\"mtime\":1625753101,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":43}','2021-07-08 14:05:01',1,141,0,38,43,'172.20.0.1'),(675,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:05:01',1,4,0,21,1,'172.20.0.1'),(676,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:06:47',1,141,0,38,43,'172.20.0.1'),(677,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:05:01\",\"2021-07-08 14:06:47\"]}','2021-07-08 14:06:47',1,4,0,21,1,'172.20.0.1'),(678,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753207,\"mtime\":1625753207,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":44}','2021-07-08 14:06:47',1,141,0,38,44,'172.20.0.1'),(679,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:06:47',1,4,0,21,1,'172.20.0.1'),(680,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:06:52',1,141,0,38,44,'172.20.0.1'),(681,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:06:47\",\"2021-07-08 14:06:53\"]}','2021-07-08 14:06:53',1,4,0,21,1,'172.20.0.1'),(682,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753213,\"mtime\":1625753213,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":45}','2021-07-08 14:06:53',1,141,0,38,45,'172.20.0.1'),(683,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:06:53',1,4,0,21,1,'172.20.0.1'),(684,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:08:57',1,141,0,38,45,'172.20.0.1'),(685,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:06:53\",\"2021-07-08 14:08:58\"]}','2021-07-08 14:08:58',1,4,0,21,1,'172.20.0.1'),(686,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753338,\"mtime\":1625753338,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":46}','2021-07-08 14:08:58',1,141,0,38,46,'172.20.0.1'),(687,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:08:58',1,4,0,21,1,'172.20.0.1'),(688,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:09:04',1,141,0,38,46,'172.20.0.1'),(689,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:08:58\",\"2021-07-08 14:09:04\"]}','2021-07-08 14:09:04',1,4,0,21,1,'172.20.0.1'),(690,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753344,\"mtime\":1625753344,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":47}','2021-07-08 14:09:04',1,141,0,38,47,'172.20.0.1'),(691,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:09:04',1,4,0,21,1,'172.20.0.1'),(692,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:11:13',1,141,0,38,47,'172.20.0.1'),(693,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:09:04\",\"2021-07-08 14:11:14\"]}','2021-07-08 14:11:14',1,4,0,21,1,'172.20.0.1'),(694,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753474,\"mtime\":1625753474,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":48}','2021-07-08 14:11:14',1,141,0,38,48,'172.20.0.1'),(695,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:11:14',1,4,0,21,1,'172.20.0.1'),(696,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:11:51',1,141,0,38,48,'172.20.0.1'),(697,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:11:14\",\"2021-07-08 14:11:51\"]}','2021-07-08 14:11:51',1,4,0,21,1,'172.20.0.1'),(698,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753511,\"mtime\":1625753511,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":49}','2021-07-08 14:11:51',1,141,0,38,49,'172.20.0.1'),(699,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:11:51',1,4,0,21,1,'172.20.0.1'),(700,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:12:04',1,141,0,38,49,'172.20.0.1'),(701,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:11:51\",\"2021-07-08 14:12:04\"]}','2021-07-08 14:12:04',1,4,0,21,1,'172.20.0.1'),(702,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753524,\"mtime\":1625753524,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":50}','2021-07-08 14:12:04',1,141,0,38,50,'172.20.0.1'),(703,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:12:04',1,4,0,21,1,'172.20.0.1'),(704,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:13:00',1,141,0,38,50,'172.20.0.1'),(705,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:12:04\",\"2021-07-08 14:13:00\"]}','2021-07-08 14:13:00',1,4,0,21,1,'172.20.0.1'),(706,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753580,\"mtime\":1625753580,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":51}','2021-07-08 14:13:00',1,141,0,38,51,'172.20.0.1'),(707,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:13:00',1,4,0,21,1,'172.20.0.1'),(708,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:13:12',1,141,0,38,51,'172.20.0.1'),(709,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:13:00\",\"2021-07-08 14:13:12\"]}','2021-07-08 14:13:12',1,4,0,21,1,'172.20.0.1'),(710,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753592,\"mtime\":1625753592,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":52}','2021-07-08 14:13:12',1,141,0,38,52,'172.20.0.1'),(711,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:13:12',1,4,0,21,1,'172.20.0.1'),(712,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:13:36',1,141,0,38,52,'172.20.0.1'),(713,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:13:12\",\"2021-07-08 14:13:36\"]}','2021-07-08 14:13:36',1,4,0,21,1,'172.20.0.1'),(714,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753616,\"mtime\":1625753616,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":53}','2021-07-08 14:13:36',1,141,0,38,53,'172.20.0.1'),(715,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:13:36',1,4,0,21,1,'172.20.0.1'),(716,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:14:10',1,141,0,38,53,'172.20.0.1'),(717,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:13:36\",\"2021-07-08 14:14:10\"]}','2021-07-08 14:14:10',1,4,0,21,1,'172.20.0.1'),(718,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753650,\"mtime\":1625753650,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":54}','2021-07-08 14:14:10',1,141,0,38,54,'172.20.0.1'),(719,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:14:10',1,4,0,21,1,'172.20.0.1'),(720,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:14:17',1,141,0,38,54,'172.20.0.1'),(721,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:14:10\",\"2021-07-08 14:14:17\"]}','2021-07-08 14:14:17',1,4,0,21,1,'172.20.0.1'),(722,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753657,\"mtime\":1625753657,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":55}','2021-07-08 14:14:17',1,141,0,38,55,'172.20.0.1'),(723,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:14:17',1,4,0,21,1,'172.20.0.1'),(724,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:14:22',1,141,0,38,55,'172.20.0.1'),(725,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:14:17\",\"2021-07-08 14:14:22\"]}','2021-07-08 14:14:22',1,4,0,21,1,'172.20.0.1'),(726,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753662,\"mtime\":1625753662,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":56}','2021-07-08 14:14:22',1,141,0,38,56,'172.20.0.1'),(727,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:14:22',1,4,0,21,1,'172.20.0.1'),(728,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:14:24',1,141,0,38,56,'172.20.0.1'),(729,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:14:22\",\"2021-07-08 14:14:24\"]}','2021-07-08 14:14:24',1,4,0,21,1,'172.20.0.1'),(730,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753665,\"mtime\":1625753665,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":57}','2021-07-08 14:14:25',1,141,0,38,57,'172.20.0.1'),(731,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-08 14:14:24\",\"2021-07-08 14:14:25\"]}','2021-07-08 14:14:25',1,4,0,21,1,'172.20.0.1'),(732,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:14:46',1,141,0,38,57,'172.20.0.1'),(733,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:14:25\",\"2021-07-08 14:14:46\"]}','2021-07-08 14:14:46',1,4,0,21,1,'172.20.0.1'),(734,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753686,\"mtime\":1625753686,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":58}','2021-07-08 14:14:46',1,141,0,38,58,'172.20.0.1'),(735,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:14:46',1,4,0,21,1,'172.20.0.1'),(736,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:15:25',1,141,0,38,58,'172.20.0.1'),(737,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:14:46\",\"2021-07-08 14:15:25\"]}','2021-07-08 14:15:25',1,4,0,21,1,'172.20.0.1'),(738,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753725,\"mtime\":1625753725,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":59}','2021-07-08 14:15:25',1,141,0,38,59,'172.20.0.1'),(739,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:15:25',1,4,0,21,1,'172.20.0.1'),(740,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:15:38',1,141,0,38,59,'172.20.0.1'),(741,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:15:25\",\"2021-07-08 14:15:38\"]}','2021-07-08 14:15:38',1,4,0,21,1,'172.20.0.1'),(742,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753738,\"mtime\":1625753738,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":60}','2021-07-08 14:15:38',1,141,0,38,60,'172.20.0.1'),(743,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:15:38',1,4,0,21,1,'172.20.0.1'),(744,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:17:14',1,141,0,38,60,'172.20.0.1'),(745,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:15:38\",\"2021-07-08 14:17:14\"]}','2021-07-08 14:17:14',1,4,0,21,1,'172.20.0.1'),(746,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753834,\"mtime\":1625753834,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":61}','2021-07-08 14:17:14',1,141,0,38,61,'172.20.0.1'),(747,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:17:14',1,4,0,21,1,'172.20.0.1'),(748,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:18:54',1,141,0,38,61,'172.20.0.1'),(749,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:17:14\",\"2021-07-08 14:18:54\"]}','2021-07-08 14:18:54',1,4,0,21,1,'172.20.0.1'),(750,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625753935,\"mtime\":1625753935,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":62}','2021-07-08 14:18:55',1,141,0,38,62,'172.20.0.1'),(751,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-08 14:18:54\",\"2021-07-08 14:18:55\"]}','2021-07-08 14:18:55',1,4,0,21,1,'172.20.0.1'),(752,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:20:00',1,141,0,38,62,'172.20.0.1'),(753,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:18:55\",\"2021-07-08 14:20:00\"]}','2021-07-08 14:20:00',1,4,0,21,1,'172.20.0.1'),(754,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754000,\"mtime\":1625754000,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":63}','2021-07-08 14:20:00',1,141,0,38,63,'172.20.0.1'),(755,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:20:00',1,4,0,21,1,'172.20.0.1'),(756,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:20:21',1,141,0,38,63,'172.20.0.1'),(757,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:20:00\",\"2021-07-08 14:20:21\"]}','2021-07-08 14:20:21',1,4,0,21,1,'172.20.0.1'),(758,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754021,\"mtime\":1625754021,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":64}','2021-07-08 14:20:21',1,141,0,38,64,'172.20.0.1'),(759,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:20:21',1,4,0,21,1,'172.20.0.1'),(760,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:20:47',1,141,0,38,64,'172.20.0.1'),(761,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:20:21\",\"2021-07-08 14:20:47\"]}','2021-07-08 14:20:47',1,4,0,21,1,'172.20.0.1'),(762,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754048,\"mtime\":1625754048,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":65}','2021-07-08 14:20:48',1,141,0,38,65,'172.20.0.1'),(763,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-08 14:20:47\",\"2021-07-08 14:20:48\"]}','2021-07-08 14:20:48',1,4,0,21,1,'172.20.0.1'),(764,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:23:07',1,141,0,38,65,'172.20.0.1'),(765,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:20:48\",\"2021-07-08 14:23:08\"]}','2021-07-08 14:23:08',1,4,0,21,1,'172.20.0.1'),(766,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754188,\"mtime\":1625754188,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":66}','2021-07-08 14:23:08',1,141,0,38,66,'172.20.0.1'),(767,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:23:08',1,4,0,21,1,'172.20.0.1'),(768,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:23:41',1,141,0,38,66,'172.20.0.1'),(769,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:23:08\",\"2021-07-08 14:23:41\"]}','2021-07-08 14:23:41',1,4,0,21,1,'172.20.0.1'),(770,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754221,\"mtime\":1625754221,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":67}','2021-07-08 14:23:41',1,141,0,38,67,'172.20.0.1'),(771,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:23:41',1,4,0,21,1,'172.20.0.1'),(772,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:25:35',1,141,0,38,67,'172.20.0.1'),(773,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:23:41\",\"2021-07-08 14:25:35\"]}','2021-07-08 14:25:35',1,4,0,21,1,'172.20.0.1'),(774,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754335,\"mtime\":1625754335,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":68}','2021-07-08 14:25:35',1,141,0,38,68,'172.20.0.1'),(775,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:25:35',1,4,0,21,1,'172.20.0.1'),(776,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:25:38',1,141,0,38,68,'172.20.0.1'),(777,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:25:35\",\"2021-07-08 14:25:38\"]}','2021-07-08 14:25:38',1,4,0,21,1,'172.20.0.1'),(778,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754338,\"mtime\":1625754338,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":69}','2021-07-08 14:25:38',1,141,0,38,69,'172.20.0.1'),(779,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:25:38',1,4,0,21,1,'172.20.0.1'),(780,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:26:05',1,141,0,38,69,'172.20.0.1'),(781,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:25:38\",\"2021-07-08 14:26:05\"]}','2021-07-08 14:26:05',1,4,0,21,1,'172.20.0.1'),(782,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754365,\"mtime\":1625754365,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":70}','2021-07-08 14:26:05',1,141,0,38,70,'172.20.0.1'),(783,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:26:05',1,4,0,21,1,'172.20.0.1'),(784,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:27:38',1,141,0,38,70,'172.20.0.1'),(785,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:26:05\",\"2021-07-08 14:27:39\"]}','2021-07-08 14:27:39',1,4,0,21,1,'172.20.0.1'),(786,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754459,\"mtime\":1625754459,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":71}','2021-07-08 14:27:39',1,141,0,38,71,'172.20.0.1'),(787,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:27:39',1,4,0,21,1,'172.20.0.1'),(788,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:31:50',1,141,0,38,71,'172.20.0.1'),(789,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:27:39\",\"2021-07-08 14:31:50\"]}','2021-07-08 14:31:50',1,4,0,21,1,'172.20.0.1'),(790,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754710,\"mtime\":1625754710,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":72}','2021-07-08 14:31:50',1,141,0,38,72,'172.20.0.1'),(791,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:31:51',1,4,0,21,1,'172.20.0.1'),(792,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:31:58',1,141,0,38,72,'172.20.0.1'),(793,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:31:50\",\"2021-07-08 14:31:58\"]}','2021-07-08 14:31:58',1,4,0,21,1,'172.20.0.1'),(794,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754718,\"mtime\":1625754718,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":73}','2021-07-08 14:31:58',1,141,0,38,73,'172.20.0.1'),(795,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:31:58',1,4,0,21,1,'172.20.0.1'),(796,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:33:03',1,141,0,38,73,'172.20.0.1'),(797,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:31:58\",\"2021-07-08 14:33:03\"]}','2021-07-08 14:33:03',1,4,0,21,1,'172.20.0.1'),(798,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754783,\"mtime\":1625754783,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":74}','2021-07-08 14:33:03',1,141,0,38,74,'172.20.0.1'),(799,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:33:03',1,4,0,21,1,'172.20.0.1'),(800,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:33:25',1,141,0,38,74,'172.20.0.1'),(801,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:33:03\",\"2021-07-08 14:33:25\"]}','2021-07-08 14:33:25',1,4,0,21,1,'172.20.0.1'),(802,1,'tmp/1/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754805,\"mtime\":1625754805,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":4485,\"id\":75}','2021-07-08 14:33:25',1,141,0,38,75,'172.20.0.1'),(803,2,'System Administrator','{\"disk_usage\":[96246,100731]}','2021-07-08 14:33:25',1,4,0,21,1,'172.20.0.1'),(804,3,'tmp/1/Test.odt','null','2021-07-08 14:33:38',1,141,0,38,75,'172.20.0.1'),(805,2,'System Administrator','{\"disk_usage\":[100731,96246],\"modifiedAt\":[\"2021-07-08 14:33:25\",\"2021-07-08 14:33:38\"]}','2021-07-08 14:33:38',1,4,0,21,1,'172.20.0.1'),(806,1,'tmp/1/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754818,\"mtime\":1625754818,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":4485,\"id\":76}','2021-07-08 14:33:38',1,141,0,38,76,'172.20.0.1'),(807,2,'System Administrator','{\"disk_usage\":[96246,100731]}','2021-07-08 14:33:38',1,4,0,21,1,'172.20.0.1'),(808,3,'tmp/1/Test.odt','null','2021-07-08 14:33:50',1,141,0,38,76,'172.20.0.1'),(809,2,'System Administrator','{\"disk_usage\":[100731,96246],\"modifiedAt\":[\"2021-07-08 14:33:38\",\"2021-07-08 14:33:50\"]}','2021-07-08 14:33:50',1,4,0,21,1,'172.20.0.1'),(810,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754830,\"mtime\":1625754830,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":77}','2021-07-08 14:33:50',1,141,0,38,77,'172.20.0.1'),(811,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:33:50',1,4,0,21,1,'172.20.0.1'),(812,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:33:54',1,141,0,38,77,'172.20.0.1'),(813,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:33:50\",\"2021-07-08 14:33:54\"]}','2021-07-08 14:33:54',1,4,0,21,1,'172.20.0.1'),(814,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754834,\"mtime\":1625754834,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":78}','2021-07-08 14:33:54',1,141,0,38,78,'172.20.0.1'),(815,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:33:54',1,4,0,21,1,'172.20.0.1'),(816,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:34:04',1,141,0,38,78,'172.20.0.1'),(817,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:33:54\",\"2021-07-08 14:34:04\"]}','2021-07-08 14:34:04',1,4,0,21,1,'172.20.0.1'),(818,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754844,\"mtime\":1625754844,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":79}','2021-07-08 14:34:04',1,141,0,38,79,'172.20.0.1'),(819,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:34:04',1,4,0,21,1,'172.20.0.1'),(820,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:34:17',1,141,0,38,79,'172.20.0.1'),(821,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:34:04\",\"2021-07-08 14:34:17\"]}','2021-07-08 14:34:17',1,4,0,21,1,'172.20.0.1'),(822,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754857,\"mtime\":1625754857,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":80}','2021-07-08 14:34:17',1,141,0,38,80,'172.20.0.1'),(823,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:34:17',1,4,0,21,1,'172.20.0.1'),(824,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:35:22',1,141,0,38,80,'172.20.0.1'),(825,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:34:17\",\"2021-07-08 14:35:22\"]}','2021-07-08 14:35:22',1,4,0,21,1,'172.20.0.1'),(826,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754922,\"mtime\":1625754922,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":81}','2021-07-08 14:35:22',1,141,0,38,81,'172.20.0.1'),(827,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:35:22',1,4,0,21,1,'172.20.0.1'),(828,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:35:31',1,141,0,38,81,'172.20.0.1'),(829,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:35:22\",\"2021-07-08 14:35:31\"]}','2021-07-08 14:35:31',1,4,0,21,1,'172.20.0.1'),(830,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625754931,\"mtime\":1625754931,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":82}','2021-07-08 14:35:31',1,141,0,38,82,'172.20.0.1'),(831,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:35:31',1,4,0,21,1,'172.20.0.1'),(832,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:49:47',1,141,0,38,82,'172.20.0.1'),(833,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:35:31\",\"2021-07-08 14:49:47\"]}','2021-07-08 14:49:47',1,4,0,21,1,'172.20.0.1'),(834,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625755787,\"mtime\":1625755787,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":83}','2021-07-08 14:49:47',1,141,0,38,83,'172.20.0.1'),(835,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:49:47',1,4,0,21,1,'172.20.0.1'),(836,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:50:24',1,141,0,38,83,'172.20.0.1'),(837,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:49:47\",\"2021-07-08 14:50:25\"]}','2021-07-08 14:50:25',1,4,0,21,1,'172.20.0.1'),(838,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625755825,\"mtime\":1625755825,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":84}','2021-07-08 14:50:25',1,141,0,38,84,'172.20.0.1'),(839,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:50:25',1,4,0,21,1,'172.20.0.1'),(840,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:51:20',1,141,0,38,84,'172.20.0.1'),(841,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:50:25\",\"2021-07-08 14:51:20\"]}','2021-07-08 14:51:20',1,4,0,21,1,'172.20.0.1'),(842,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625755880,\"mtime\":1625755880,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":85}','2021-07-08 14:51:20',1,141,0,38,85,'172.20.0.1'),(843,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:51:20',1,4,0,21,1,'172.20.0.1'),(844,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:53:13',1,141,0,38,85,'172.20.0.1'),(845,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:51:20\",\"2021-07-08 14:53:13\"]}','2021-07-08 14:53:13',1,4,0,21,1,'172.20.0.1'),(846,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625755993,\"mtime\":1625755993,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":86}','2021-07-08 14:53:13',1,141,0,38,86,'172.20.0.1'),(847,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:53:13',1,4,0,21,1,'172.20.0.1'),(848,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:53:22',1,141,0,38,86,'172.20.0.1'),(849,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:53:13\",\"2021-07-08 14:53:22\"]}','2021-07-08 14:53:22',1,4,0,21,1,'172.20.0.1'),(850,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756002,\"mtime\":1625756002,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":87}','2021-07-08 14:53:22',1,141,0,38,87,'172.20.0.1'),(851,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:53:22',1,4,0,21,1,'172.20.0.1'),(852,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:54:02',1,141,0,38,87,'172.20.0.1'),(853,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:53:22\",\"2021-07-08 14:54:02\"]}','2021-07-08 14:54:02',1,4,0,21,1,'172.20.0.1'),(854,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756042,\"mtime\":1625756042,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":88}','2021-07-08 14:54:02',1,141,0,38,88,'172.20.0.1'),(855,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:54:02',1,4,0,21,1,'172.20.0.1'),(856,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:54:24',1,141,0,38,88,'172.20.0.1'),(857,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:54:02\",\"2021-07-08 14:54:24\"]}','2021-07-08 14:54:24',1,4,0,21,1,'172.20.0.1'),(858,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756065,\"mtime\":1625756065,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":89}','2021-07-08 14:54:25',1,141,0,38,89,'172.20.0.1'),(859,2,'System Administrator','{\"disk_usage\":[96246,290369],\"modifiedAt\":[\"2021-07-08 14:54:24\",\"2021-07-08 14:54:25\"]}','2021-07-08 14:54:25',1,4,0,21,1,'172.20.0.1'),(860,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:55:15',1,141,0,38,89,'172.20.0.1'),(861,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:54:25\",\"2021-07-08 14:55:15\"]}','2021-07-08 14:55:15',1,4,0,21,1,'172.20.0.1'),(862,1,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756115,\"mtime\":1625756115,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"20-08-17 teamindeling JO8-JO7-JO6.xlsx\",\"extension\":\"xlsx\",\"size\":194123,\"id\":90}','2021-07-08 14:55:15',1,141,0,38,90,'172.20.0.1'),(863,2,'System Administrator','{\"disk_usage\":[96246,290369]}','2021-07-08 14:55:15',1,4,0,21,1,'172.20.0.1'),(864,3,'tmp/1/20-08-17 teamindeling JO8-JO7-JO6.xlsx','null','2021-07-08 14:55:29',1,141,0,38,90,'172.20.0.1'),(865,2,'System Administrator','{\"disk_usage\":[290369,96246],\"modifiedAt\":[\"2021-07-08 14:55:15\",\"2021-07-08 14:55:29\"]}','2021-07-08 14:55:29',1,4,0,21,1,'172.20.0.1'),(866,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756129,\"mtime\":1625756129,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":91}','2021-07-08 14:55:29',1,141,0,38,91,'172.20.0.1'),(867,2,'System Administrator','{\"disk_usage\":[96246,1969734]}','2021-07-08 14:55:29',1,4,0,21,1,'172.20.0.1'),(868,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-08 14:55:38',1,141,0,38,91,'172.20.0.1'),(869,2,'System Administrator','{\"disk_usage\":[1969734,96246],\"modifiedAt\":[\"2021-07-08 14:55:29\",\"2021-07-08 14:55:38\"]}','2021-07-08 14:55:38',1,4,0,21,1,'172.20.0.1'),(870,1,'tmp/1/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625756138,\"mtime\":1625756138,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":4485,\"id\":92}','2021-07-08 14:55:38',1,141,0,38,92,'172.20.0.1'),(871,2,'System Administrator','{\"disk_usage\":[96246,100731]}','2021-07-08 14:55:38',1,4,0,21,1,'172.20.0.1'),(872,2,'System Administrator','{\"lastLogin\":[\"2021-07-08T15:02:46+00:00\",\"2021-07-08T13:56:27+00:00\"],\"loginCount\":[19,18],\"language\":[\"nl\",\"en\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-08 15:02:46',1,5,0,21,1,'172.20.0.1'),(873,4,'admin [172.20.0.1]',NULL,'2021-07-08 15:02:47',1,5,0,21,1,'172.20.0.1'),(874,2,'System Administrator','{\"lastLogin\":[\"2021-07-09T06:54:22+00:00\",\"2021-07-08T15:02:46+00:00\"],\"loginCount\":[20,19],\"language\":[\"en_uk\",\"nl\"],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 06:54:22',1,5,0,21,1,'172.20.0.1'),(875,4,'admin [172.20.0.1]',NULL,'2021-07-09 06:54:22',1,5,0,21,1,'172.20.0.1'),(876,2,'Elmer Fudd','{\"lastLogin\":[\"2021-07-09T06:57:09+00:00\",\"2021-07-08T13:05:23+00:00\"],\"loginCount\":[12,11],\"language\":[\"en_uk\",\"nl\"],\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 06:57:09',2,5,0,21,2,'172.20.0.1'),(877,4,'elmer [172.20.0.1]',NULL,'2021-07-09 06:57:09',2,5,0,21,2,'172.20.0.1'),(878,2,'googleauthenticator','{\"version\":[3,2]}','2021-07-09 07:10:01',1,15,1,13,6,'172.20.0.1'),(879,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"YKDFOU7SMLNDG5EP\",\"isEnabled\":false,\"qrBlobId\":\"3467aeea81d4a542a6d4c5c4d530f377bd274e2c\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:12:41',2,5,0,21,2,'172.20.0.1'),(880,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"67FPY3NCKNKZH5PN\",\"isEnabled\":false,\"qrBlobId\":\"1a22604408bc37778c022f2e8106d487a1b1f2df\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:14:09',2,5,0,21,2,'172.20.0.1'),(881,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"V6P74ZR5GJ4TLEA3\",\"isEnabled\":false,\"qrBlobId\":\"b07bdcfb0135828d389a8841e17d0c30e74b37c7\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:15:38',2,5,0,21,2,'172.20.0.1'),(882,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"A6OWKXK7SDKUMQPL\",\"isEnabled\":false,\"qrBlobId\":\"3c84c7bb3ce47c2ac8873e624bb9760364aa3e8f\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:16:14',2,5,0,21,2,'172.20.0.1'),(883,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"6U7O4UUV4ZCHXZFP\",\"isEnabled\":false,\"qrBlobId\":\"26c9aabc7d2f499015153a43197dcadf5ba161b3\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:23:52',2,5,0,21,2,'172.20.0.1'),(884,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"3ME2FIYAXSXRNXML\",\"isEnabled\":false,\"qrBlobId\":\"87fe0aa8d61ae36c5e6267e3a5f0b47a66852c45\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:26:52',2,5,0,21,2,'172.20.0.1'),(885,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"5NYJCDBHK724LBRP\",\"isEnabled\":false,\"qrBlobId\":\"5861130a8fc173071baff7f9dc494744ab50fd75\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:29:09',2,5,0,21,2,'172.20.0.1'),(886,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"5OQFLY6I5ISUC6AJ\",\"isEnabled\":false,\"qrBlobId\":\"0693c5f2fb0b9cce1b6a44a6e54d402eb4c7410f\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:30:06',2,5,0,21,2,'172.20.0.1'),(887,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"MYQ5X35HGHWW5OH6\",\"isEnabled\":false,\"qrBlobId\":\"2819bb123004781c7dc86cc447d608cc3894e8fb\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:35:51',2,5,0,21,2,'172.20.0.1'),(888,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"FPIRWRR7RVNNFBTR\",\"isEnabled\":false,\"qrBlobId\":\"41e7e958c3558401f94f08cfa694111818c8fe04\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:36:19',2,5,0,21,2,'172.20.0.1'),(889,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"O5X4Z2TRAPW2IQ44\",\"isEnabled\":false,\"qrBlobId\":\"0901dc60cc8653a7247dd196c62f543c0703a2bf\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:37:15',2,5,0,21,2,'172.20.0.1'),(890,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"CIRJK4ULDABUR2I5\",\"isEnabled\":false,\"qrBlobId\":\"d14cb26221e99a1c14cea94cf734c1c35966b30a\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:38:00',2,5,0,21,2,'172.20.0.1'),(891,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"HNRCHUZOLIDIMW7A\",\"isEnabled\":false,\"qrBlobId\":\"cd8f7e8258ad5f2190063688041841f30b10a610\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:38:23',2,5,0,21,2,'172.20.0.1'),(892,2,'Elmer Fudd','{\"employee\":[{\"id\":2,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"googleauthenticator\":[{\"secret\":\"3ZA2DNXTMVBMHJEV\",\"isEnabled\":false,\"qrBlobId\":\"eec366efe40b73ce5be409f3285bb458c1cbbf91\",\"userId\":2,\"createdAt\":null},null],\"emailSettings\":[{\"id\":2,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:45:43',2,5,0,21,2,'172.20.0.1'),(893,5,'elmer [172.20.0.1]',NULL,'2021-07-09 07:45:51',2,5,0,21,2,'172.20.0.1'),(894,2,'System Administrator','{\"lastLogin\":[\"2021-07-09T07:45:56+00:00\",\"2021-07-09T06:54:22+00:00\"],\"loginCount\":[21,20],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-09 07:45:56',1,5,0,21,1,'172.20.0.1'),(895,4,'admin [172.20.0.1]',NULL,'2021-07-09 07:45:56',1,5,0,21,1,'172.20.0.1'),(896,3,'tmp/1/Test.odt','null','2021-07-09 07:46:13',1,141,0,38,92,'172.20.0.1'),(897,2,'System Administrator','{\"disk_usage\":[100731,96246],\"modifiedAt\":[\"2021-07-09 07:45:56\",\"2021-07-09 07:46:13\"]}','2021-07-09 07:46:13',1,4,0,21,1,'172.20.0.1'),(898,1,'tmp/1/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625816774,\"mtime\":1625816774,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":4485,\"id\":93}','2021-07-09 07:46:14',1,141,0,38,93,'172.20.0.1'),(899,2,'System Administrator','{\"disk_usage\":[96246,100731],\"modifiedAt\":[\"2021-07-09 07:46:13\",\"2021-07-09 07:46:14\"]}','2021-07-09 07:46:14',1,4,0,21,1,'172.20.0.1'),(900,3,'tmp/1/Test.odt','null','2021-07-09 07:46:20',1,141,0,38,93,'172.20.0.1'),(901,2,'System Administrator','{\"disk_usage\":[100731,96246],\"modifiedAt\":[\"2021-07-09 07:46:14\",\"2021-07-09 07:46:20\"]}','2021-07-09 07:46:20',1,4,0,21,1,'172.20.0.1'),(902,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1625816781,\"mtime\":1625816781,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":94}','2021-07-09 07:46:21',1,141,0,38,94,'172.20.0.1'),(903,2,'System Administrator','{\"disk_usage\":[96246,1969734],\"modifiedAt\":[\"2021-07-09 07:46:20\",\"2021-07-09 07:46:21\"]}','2021-07-09 07:46:21',1,4,0,21,1,'172.20.0.1'),(904,2,'files','{\"version\":[136,135]}','2021-07-12 07:35:18',1,24,1,13,13,'172.20.0.1'),(905,3,'hoursapproval2','null','2021-07-12 08:24:53',1,61,1,13,24,'172.20.0.1'),(906,2,'timeregistration2','{\"enabled\":[true,false]}','2021-07-12 08:25:04',1,60,1,13,23,'172.20.0.1'),(907,1,'hoursapproval2','{\"name\":\"hoursapproval2\",\"sort_order\":111,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-07-12 08:25:04\",\"aclId\":144,\"version\":0,\"id\":36}','2021-07-12 08:25:05',1,144,1,13,36,'172.20.0.1'),(908,2,'hoursapproval2','{\"modifiedAt\":[\"2021-07-12 08:25:04\",\"2021-07-12 08:25:05\"]}','2021-07-12 08:25:05',1,144,1,13,36,'172.20.0.1'),(909,1,'apikeys','{\"id\":37,\"name\":\"apikeys\",\"package\":\"community\",\"version\":2,\"sort_order\":111,\"checkDepencencies\":false}','2021-07-12 08:25:14',1,145,1,13,37,'172.20.0.1'),(910,1,'dav','{\"name\":\"dav\",\"sort_order\":112,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-07-12 08:25:26\",\"aclId\":146,\"version\":1,\"id\":38}','2021-07-12 08:25:26',1,146,1,13,38,'172.20.0.1'),(911,1,'caldav','{\"name\":\"caldav\",\"sort_order\":112,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-07-12 08:25:26\",\"aclId\":147,\"version\":32,\"id\":39}','2021-07-12 08:25:26',1,147,1,13,39,'172.20.0.1'),(912,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"timeRegistrationSettings\":[{\"selectProjectOnTimerStart\":false},null],\"projectsSettings\":[{\"duplicateRecursively\":true,\"duplicateRecursivelyTasks\":true,\"duplicateRecursivelyFiles\":true,\"deleteProjectsRecursively\":true,\"userId\":1},null]}','2021-07-12 08:25:42',1,5,0,21,1,'172.20.0.1'),(913,2,'Only in shared','{\"relatedFieldCondition\":[\"\",\"addressBookId != B\\u00e4rbel Paulus AND addressBookId != Clients AND addressBookId != Consultants AND addressBookId != Denis Ceyra Yildiz AND addressBookId != Ileana Clapham AND addressBookId != Intern AND addressBookId != Kent Pacheco AND addressBookId != Makler AND addressBookId != Maria Laura Caruano AND addressBookId != Michael Winter AND addressBookId != Nicole Clapham AND addressBookId != Nina Claire T\\u00e4ubrich AND addressBookId != Nina Volk AND addressBookId != Petra Stein AND addressBookId != Roman Clapham AND addressBookId != Sonstige\"],\"conditionallyHidden\":[false,true],\"forceAlterTable\":[true,false]}','2021-07-12 08:55:06',1,133,0,9,1,'172.20.0.1'),(914,2,'Shared','{\"name\":[\"Shared\",\"Test\"],\"filter\":[\"{\\\"addressBookId\\\":[1]}\",\"[]\"]}','2021-07-12 08:55:20',1,133,1,10,1,'172.20.0.1'),(915,1,'test','{\"id\":8,\"name\":\"test\",\"isUserGroupFor\":5,\"users\":[5]}','2021-07-12 09:17:42',1,148,1,11,8,'172.20.0.1'),(916,1,'test','{\"id\":5,\"username\":\"test\",\"displayName\":\"test\",\"email\":\"test@intermesh.nl\",\"recoveryEmail\":\"test@intermesh.nl\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/test\",\"password\":\"$2y$10$i2EpXTVrwB0CaoMrlLJGu.Ft\\/OzYuNTapdElsyI3FrNNIz1NBVZLq\",\"groups\":[2,8],\"addressBookSettings\":{\"defaultAddressBookId\":null,\"sortBy\":\"name\",\"userId\":5},\"employee\":{\"id\":5,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":null,\"userId\":5},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":5},\"emailSettings\":{\"id\":5,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":5,\"account_id\":0,\"noteBooks\":[],\"addressBooks\":[]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":5},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":5}}','2021-07-12 09:17:42',1,5,0,21,5,'172.20.0.1'),(917,2,'test','{\"employee\":[{\"id\":5,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":5,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 09:17:54',1,5,0,21,5,'172.20.0.1'),(918,2,'test','{\"lastLogin\":[\"2021-07-12T09:17:56+00:00\",null],\"loginCount\":[1,0],\"language\":[\"en_uk\",\"en\"],\"employee\":[{\"id\":5,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":5,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 09:17:56',NULL,5,0,21,5,'172.20.0.1'),(919,4,'test [172.20.0.1]',NULL,'2021-07-12 09:17:56',NULL,5,0,21,5,'172.20.0.1'),(920,1,'test','{\"user_id\":5,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":5,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"test\",\"parent_id\":1,\"mtime\":1626081479,\"ctime\":1626081479,\"id\":34}','2021-07-12 09:17:59',1,24,1,39,34,'172.20.0.1'),(921,2,'calendar','{\"muser_id\":[1,5]}','2021-07-12 09:17:59',1,24,1,39,1,'172.20.0.1'),(922,2,'test','{\"acl_id\":[0,149]}','2021-07-12 09:17:59',1,149,1,39,34,'172.20.0.1'),(923,1,'test','{\"group_id\":1,\"user_id\":5,\"acl_id\":149,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":34,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"test\",\"id\":7}','2021-07-12 09:17:59',1,149,1,36,7,'172.20.0.1'),(924,5,'test [172.20.0.1]',NULL,'2021-07-12 09:20:42',NULL,5,0,21,5,'172.20.0.1'),(925,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T10:10:56+00:00\",\"2021-07-09T07:45:56+00:00\"],\"loginCount\":[22,21],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 10:10:56',1,5,0,21,1,'172.20.0.1'),(926,4,'admin [172.20.0.1]',NULL,'2021-07-12 10:10:56',1,5,0,21,1,'172.20.0.1'),(927,1,'test','{\"id\":7,\"name\":\"test\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-07-12 10:11:13',1,150,1,23,7,'172.20.0.1'),(928,1,'test','{\"id\":69,\"name\":\"test\"}','2021-07-12 10:11:13',1,151,1,35,69,'172.20.0.1'),(929,2,'test','{\"groups\":[[2,8,3],[2,8]],\"addressBookSettings\":[{\"defaultAddressBookId\":7,\"sortBy\":\"name\",\"userId\":5},null],\"employee\":[{\"id\":5,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"notesSettings\":[{\"defaultNoteBookId\":69,\"userId\":5},null],\"emailSettings\":[{\"id\":5,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"syncSettings\":[{\"user_id\":5,\"account_id\":0,\"noteBooks\":[{\"userId\":5,\"noteBookId\":69,\"isDefault\":true}],\"addressBooks\":[{\"userId\":5,\"addressBookId\":7,\"isDefault\":true}]},null]}','2021-07-12 10:11:13',1,5,0,21,5,'172.20.0.1'),(930,2,'test','{\"lastLogin\":[\"2021-07-12T10:11:19+00:00\",\"2021-07-12T09:17:56+00:00\"],\"loginCount\":[2,1],\"employee\":[{\"id\":5,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":5,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 10:11:19',NULL,5,0,21,5,'172.20.0.1'),(931,4,'test [172.20.0.1]',NULL,'2021-07-12 10:11:19',NULL,5,0,21,5,'172.20.0.1'),(932,5,'test [172.20.0.1]',NULL,'2021-07-12 11:15:49',NULL,5,0,21,5,'172.20.0.1'),(933,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T11:15:53+00:00\",\"2021-07-12T10:10:56+00:00\"],\"loginCount\":[23,22],\"employee\":[{\"id\":1,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 11:15:53',1,5,0,21,1,'172.20.0.1'),(934,4,'admin [172.20.0.1]',NULL,'2021-07-12 11:15:53',1,5,0,21,1,'172.20.0.1'),(935,1,'go\\modules\\business\\business\\model\\EmployeeAgreement','{\"id\":1,\"employeeId\":1,\"start\":\"2021-07-12\",\"mo\":480,\"tu\":480,\"we\":0,\"th\":480,\"fr\":0,\"sa\":0,\"su\":0,\"budgets\":{\"1\":{\"activityId\":1,\"budget\":160}}}','2021-07-12 11:16:36',1,12,0,28,1,'172.20.0.1'),(936,2,'go\\modules\\business\\business\\model\\EmployeeAgreement','{\"we\":[480,0]}','2021-07-12 11:16:53',1,12,0,28,1,'172.20.0.1'),(937,1,'Customers','{\"id\":2,\"entityId\":24,\"name\":\"Customers\",\"filter\":\"{\\\"addressBookId\\\":[3]}\",\"isTab\":true,\"entity\":\"Contact\",\"setAcl\":{\"2\":10}}','2021-07-12 11:55:14',1,152,1,10,2,'172.20.0.1'),(938,2,'Shared','{\"isTab\":[true,false]}','2021-07-12 11:55:21',1,133,1,10,1,'172.20.0.1'),(939,1,'For piet','{\"id\":2,\"fieldSetId\":2,\"name\":\"For piet\",\"databaseName\":\"For_piet\",\"relatedFieldCondition\":\"check = true AND firstName = Piet\",\"conditionallyHidden\":true,\"options\":\"{\\\"maxLength\\\":50}\",\"forceAlterTable\":true}','2021-07-12 12:13:18',1,152,0,9,2,'172.20.0.1'),(940,1,'check','{\"id\":3,\"fieldSetId\":2,\"name\":\"check\",\"databaseName\":\"go_check\",\"type\":\"Checkbox\",\"forceAlterTable\":true}','2021-07-12 12:13:35',1,152,0,9,3,'172.20.0.1'),(941,2,'For piet','{\"relatedFieldCondition\":[\"go_check = true AND firstName = Piet\",\"check = true AND firstName = Piet\"],\"forceAlterTable\":[true,false]}','2021-07-12 12:13:45',1,152,0,9,2,'172.20.0.1'),(942,2,'For piet','{\"relatedFieldCondition\":[\"go_check = 1 AND firstName = Piet\",\"go_check = true AND firstName = Piet\"],\"forceAlterTable\":[true,false]}','2021-07-12 12:26:26',1,152,0,9,2,'172.20.0.1'),(943,1,'Piet Jansen','{\"id\":8,\"addressBookId\":3,\"firstName\":\"Piet\",\"lastName\":\"Jansen\",\"name\":\"Piet Jansen\",\"language\":\"en_uk\",\"uid\":\"8@host.docker.internal:8080\",\"uri\":\"8@host.docker.internal:8080.vcf\"}','2021-07-12 12:28:09',1,76,0,24,8,'172.20.0.1'),(944,1,'Programmeren','{\"id\":3,\"code\":\"1\",\"name\":\"Programmeren\",\"units\":1,\"billable\":1,\"budgetable\":true}','2021-07-12 12:38:26',1,12,0,26,3,'172.20.0.1'),(945,1,'Testen','{\"id\":4,\"code\":\"2\",\"name\":\"Testen\",\"units\":1,\"budgetable\":false}','2021-07-12 12:38:34',1,12,0,26,4,'172.20.0.1'),(946,1,'Epibreren','{\"id\":5,\"code\":\"3\",\"name\":\"Epibreren\",\"units\":1,\"billable\":2,\"budgetable\":false}','2021-07-12 12:38:45',1,12,0,26,5,'172.20.0.1'),(947,2,'System Administrator','{\"employee\":[{\"id\":1,\"businessId\":1,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 12:39:32',1,5,0,21,1,'172.20.0.1'),(948,2,'[001] Develop Rocket 2000','{\"start_time\":[1624871094,1624831200],\"due_time\":[1627463094,1627423200]}','2021-07-12 12:40:13',1,123,0,57,2,'172.20.0.1'),(949,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:25:14',1,5,0,21,1,'172.20.0.1'),(950,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:25:21+00:00\",\"2021-07-12T11:15:53+00:00\"],\"loginCount\":[24,23],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:25:21',1,5,0,21,1,'172.20.0.1'),(951,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:25:21',1,5,0,21,1,'172.20.0.1'),(952,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:27:58',1,5,0,21,1,'172.20.0.1'),(953,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:28:20+00:00\",\"2021-07-12T13:25:21+00:00\"],\"loginCount\":[25,24],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:28:20',1,5,0,21,1,'172.20.0.1'),(954,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:28:20',1,5,0,21,1,'172.20.0.1'),(955,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:32:41',1,5,0,21,1,'172.20.0.1'),(956,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:32:48+00:00\",\"2021-07-12T13:28:20+00:00\"],\"loginCount\":[26,25],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:32:48',1,5,0,21,1,'172.20.0.1'),(957,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:32:48',1,5,0,21,1,'172.20.0.1'),(958,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:35:27',1,5,0,21,1,'172.20.0.1'),(959,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:35:38+00:00\",\"2021-07-12T13:32:48+00:00\"],\"loginCount\":[27,26],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:35:38',1,5,0,21,1,'172.20.0.1'),(960,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:35:38',1,5,0,21,1,'172.20.0.1'),(961,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:38:21',1,5,0,21,1,'172.20.0.1'),(962,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:38:29+00:00\",\"2021-07-12T13:35:38+00:00\"],\"loginCount\":[28,27],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:38:29',1,5,0,21,1,'172.20.0.1'),(963,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:38:29',1,5,0,21,1,'172.20.0.1'),(964,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:44:17',1,5,0,21,1,'172.20.0.1'),(965,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:44:27+00:00\",\"2021-07-12T13:38:29+00:00\"],\"loginCount\":[29,28],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:44:27',1,5,0,21,1,'172.20.0.1'),(966,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:44:27',1,5,0,21,1,'172.20.0.1'),(967,5,'admin [172.20.0.1]',NULL,'2021-07-12 13:47:49',1,5,0,21,1,'172.20.0.1'),(968,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T13:47:55+00:00\",\"2021-07-12T13:44:27+00:00\"],\"loginCount\":[30,29],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 13:47:55',1,5,0,21,1,'172.20.0.1'),(969,4,'admin [172.20.0.1]',NULL,'2021-07-12 13:47:55',1,5,0,21,1,'172.20.0.1'),(970,1,'multi_instance','{\"id\":40,\"name\":\"multi_instance\",\"package\":\"community\",\"version\":9,\"sort_order\":112,\"checkDepencencies\":false}','2021-07-12 14:01:40',1,153,1,13,40,'172.20.0.1'),(971,1,'go\\modules\\community\\multi_instance\\model\\Instance','{\"id\":1,\"hostname\":\"test.group-office.com\",\"usersMax\":10,\"storageQuota\":10737418240}','2021-07-12 14:02:09',1,153,0,67,1,'172.20.0.1'),(972,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T14:05:27+00:00\",\"2021-07-12T13:47:55+00:00\"],\"loginCount\":[31,30],\"language\":[\"nl\",\"en_uk\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 14:05:27',1,5,0,21,1,'172.20.0.1'),(973,4,'admin [172.20.0.1]',NULL,'2021-07-12 14:05:27',1,5,0,21,1,'172.20.0.1'),(974,1,'users/admin/I2021-004713.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626098741,\"mtime\":1626098741,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":29,\"name\":\"I2021-004713.pdf\",\"extension\":\"pdf\",\"size\":119120,\"id\":95}','2021-07-12 14:05:41',1,131,0,38,95,'172.20.0.1'),(975,2,'System Administrator','{\"disk_usage\":[1969734,2088854],\"modifiedAt\":[\"2021-07-12 14:05:27\",\"2021-07-12 14:05:41\"]}','2021-07-12 14:05:41',1,4,0,21,1,'172.20.0.1'),(976,2,'go\\modules\\business\\business\\model\\EmployeeAgreement','{\"start\":[\"2019-07-12\",\"2021-07-12\"]}','2021-07-12 14:32:11',1,12,0,28,1,'172.20.0.1'),(977,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"adminDisplayName\":[\"System Administrator\",null],\"adminEmail\":[\"admin@intermesh.localhost\",null],\"userCount\":[1,null],\"loginCount\":[1,null],\"lastLogin\":[\"2021-07-12T14:02:57+00:00\",null],\"storageUsage\":[0,null],\"version\":[\"6.5.64\",null]}','2021-07-12 14:37:17',1,153,0,67,1,'172.20.0.1'),(978,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.65\",\"6.5.64\"]}','2021-07-12 15:10:04',1,153,0,67,1,'172.20.0.1'),(979,2,'System Administrator','{\"lastLogin\":[\"2021-07-12T15:10:14+00:00\",\"2021-07-12T14:05:27+00:00\"],\"loginCount\":[32,31],\"language\":[\"en_uk\",\"nl\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-12 15:10:15',1,5,0,21,1,'172.20.0.1'),(980,4,'admin [172.20.0.1]',NULL,'2021-07-12 15:10:15',1,5,0,21,1,'172.20.0.1'),(981,1,'grouptemplates','{\"id\":41,\"name\":\"grouptemplates\",\"package\":\"invicta\",\"version\":0,\"sort_order\":113,\"checkDepencencies\":false}','2021-07-12 15:10:45',1,154,1,13,41,'172.20.0.1'),(982,2,'System Administrator','{\"lastLogin\":[\"2021-07-13T07:58:53+00:00\",\"2021-07-12T15:10:14+00:00\"],\"loginCount\":[33,32],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-13 07:58:53',1,5,0,21,1,'172.20.0.1'),(983,4,'admin [172.20.0.1]',NULL,'2021-07-13 07:58:53',1,5,0,21,1,'172.20.0.1'),(984,1,'WBSO','{\"id\":3,\"entityId\":58,\"name\":\"WBSO\",\"isTab\":true,\"entity\":\"TimeEntry\",\"setAcl\":{\"2\":10}}','2021-07-13 07:59:13',1,155,1,10,3,'172.20.0.1'),(985,1,'WBSO','{\"id\":4,\"fieldSetId\":3,\"name\":\"WBSO\",\"databaseName\":\"WBSO\",\"type\":\"Checkbox\",\"forceAlterTable\":true}','2021-07-13 07:59:25',1,155,0,9,4,'172.20.0.1'),(986,1,'Bedrijf','{\"id\":5,\"fieldSetId\":3,\"name\":\"Bedrijf\",\"databaseName\":\"Bedrijf\",\"type\":\"Select\",\"relatedFieldCondition\":\"WBSO = 1\",\"conditionallyHidden\":true,\"conditionallyRequired\":true,\"forceAlterTable\":true}','2021-07-13 07:59:57',1,155,0,9,5,'172.20.0.1'),(987,1,'go\\modules\\community\\multi_instance\\model\\Instance','{\"id\":2,\"hostname\":\"test2.group-office.com\",\"usersMax\":10,\"storageQuota\":10737418240}','2021-07-13 08:14:23',1,153,0,67,2,'172.20.0.1'),(988,3,'go\\modules\\community\\multi_instance\\model\\Instance',NULL,'2021-07-13 08:16:06',1,153,0,67,2,'172.20.0.1'),(989,1,'go\\modules\\community\\multi_instance\\model\\Instance','{\"id\":3,\"hostname\":\"test2.group-office.com\"}','2021-07-13 08:16:21',1,153,0,67,3,'172.20.0.1'),(990,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.66\",\"6.5.65\"]}','2021-07-13 08:18:07',1,153,0,67,1,'172.20.0.1'),(991,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"loginCount\":[2,1],\"lastLogin\":[\"2021-07-13T08:39:31+00:00\",\"2021-07-12T14:02:57+00:00\"]}','2021-07-13 08:47:06',1,153,0,67,1,'172.20.0.1'),(992,1,'Customer type','{\"id\":6,\"fieldSetId\":1,\"name\":\"Customer type\",\"databaseName\":\"Customer_type\",\"type\":\"MultiSelect\",\"required\":true,\"forceAlterTable\":true}','2021-07-13 08:57:26',1,133,0,9,6,'172.20.0.1'),(993,2,'Customer type','{\"options\":[\"[]\",null],\"forceAlterTable\":[true,false]}','2021-07-13 09:30:39',1,133,0,9,6,'172.20.0.1'),(994,1,'gjhghj','{\"id\":9,\"addressBookId\":1,\"firstName\":\"gjhghj\",\"name\":\"gjhghj\",\"language\":\"en_uk\",\"uid\":\"9@host.docker.internal:8080\",\"uri\":\"9@host.docker.internal:8080.vcf\"}','2021-07-13 12:21:26',1,11,0,24,9,'172.20.0.1'),(995,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"loginCount\":[4,2],\"lastLogin\":[\"2021-07-13T08:48:59+00:00\",\"2021-07-13T08:39:31+00:00\"]}','2021-07-13 13:29:00',1,153,0,67,1,'172.20.0.1'),(996,2,'Only in shared','{\"forceAlterTable\":[true,false]}','2021-07-13 13:29:17',1,133,0,9,1,'172.20.0.1'),(997,2,'Only in shared','{\"forceAlterTable\":[true,false]}','2021-07-13 13:32:07',1,133,0,9,1,'172.20.0.1'),(998,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"adminDisplayName\":[\"System Administrator\",null],\"adminEmail\":[\"admin@intermesh.localhost\",null],\"userCount\":[1,null],\"loginCount\":[0,null],\"storageUsage\":[0,null],\"version\":[\"6.5.66\",null]}','2021-07-15 08:15:58',1,153,0,67,3,NULL),(999,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"loginCount\":[1,0],\"lastLogin\":[\"2021-07-15T08:19:09+00:00\",null]}','2021-07-15 10:34:32',1,153,0,67,3,'172.20.0.1'),(1000,2,'System Administrator','{\"lastLogin\":[\"2021-07-15T10:34:43+00:00\",\"2021-07-13T07:58:53+00:00\"],\"loginCount\":[34,33],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-15 10:34:43',1,5,0,21,1,'172.20.0.1'),(1001,4,'admin [172.20.0.1]',NULL,'2021-07-15 10:34:43',1,5,0,21,1,'172.20.0.1'),(1002,1,'go\\modules\\community\\multi_instance\\model\\Instance','{\"id\":4,\"hostname\":\"test3.group-office.com\"}','2021-07-15 10:35:13',1,153,0,67,4,'172.20.0.1'),(1003,1,'test@test.nl','{\"id\":9,\"name\":\"test@test.nl\",\"isUserGroupFor\":6,\"users\":[6]}','2021-07-15 12:08:32',1,156,1,11,9,'172.20.0.1'),(1004,1,'test','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"test\",\"parent_id\":12,\"mtime\":1626350912,\"ctime\":1626350912,\"id\":35}','2021-07-15 12:08:32',1,24,1,39,35,'172.20.0.1'),(1005,2,'test','{\"acl_id\":[0,157]}','2021-07-15 12:08:32',1,157,1,39,35,'172.20.0.1'),(1006,1,'test','{\"files_folder_id\":35,\"version\":1,\"user_id\":6,\"name\":\"test\",\"acl_id\":157,\"id\":5}','2021-07-15 12:08:33',1,157,1,51,5,'172.20.0.1'),(1007,1,'test (1)','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"test (1)\",\"parent_id\":1,\"mtime\":1626350913,\"ctime\":1626350913,\"id\":36}','2021-07-15 12:08:33',1,24,1,39,36,'172.20.0.1'),(1008,2,'calendar','{\"muser_id\":[5,1]}','2021-07-15 12:08:33',1,24,1,39,1,'172.20.0.1'),(1009,2,'test (1)','{\"acl_id\":[0,158]}','2021-07-15 12:08:33',1,158,1,39,36,'172.20.0.1'),(1010,1,'test (1)','{\"group_id\":1,\"user_id\":6,\"acl_id\":158,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":36,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"test (1)\",\"id\":8}','2021-07-15 12:08:33',1,158,1,36,8,'172.20.0.1'),(1011,1,'test','{\"id\":8,\"name\":\"test\",\"salutationTemplate\":\"Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms.\\/Mr.[else][if {{contact.gender}}==\\\"M\\\"]Mr.[else]Ms.[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-07-15 12:08:33',1,159,1,23,8,'172.20.0.1'),(1012,1,'test','{\"id\":70,\"name\":\"test\"}','2021-07-15 12:08:33',1,160,1,35,70,'172.20.0.1'),(1013,1,'test','{\"id\":6,\"username\":\"test@test.nl\",\"displayName\":\"test\",\"email\":\"test@test.nl\",\"recoveryEmail\":\"test@test.nl\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/test@test.nl\",\"password\":\"$2y$10$\\/dv3cEakaLD73fB0P8RCteQIity8OoWiMr9SerlY8mf6h1EGgYohq\",\"groups\":[3,2,9],\"addressBookSettings\":{\"defaultAddressBookId\":8,\"sortBy\":\"name\",\"userId\":6},\"employee\":{\"id\":6,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":70,\"userId\":6},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":6},\"emailSettings\":{\"id\":6,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":6,\"account_id\":0,\"noteBooks\":[{\"userId\":6,\"noteBookId\":70,\"isDefault\":true}],\"addressBooks\":[{\"userId\":6,\"addressBookId\":8,\"isDefault\":true}]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":6},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":6}}','2021-07-15 12:08:33',1,5,0,21,6,'172.20.0.1'),(1014,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"adminDisplayName\":[\"System Administrator\",null],\"adminEmail\":[\"admin@intermesh.localhost\",null],\"userCount\":[1,null],\"loginCount\":[0,null],\"storageUsage\":[0,null],\"version\":[\"6.5.66\",null]}','2021-07-15 12:11:21',1,153,0,67,4,NULL),(1015,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"loginCount\":[1,0],\"lastLogin\":[\"2021-07-15T12:42:11+00:00\",null]}','2021-07-15 12:43:48',1,153,0,67,4,'172.20.0.1'),(1016,1,'studio','{\"id\":42,\"name\":\"studio\",\"package\":\"business\",\"version\":0,\"sort_order\":114,\"checkDepencencies\":false}','2021-07-16 10:01:30',1,161,1,13,42,'172.20.0.1'),(1017,1,'documents','{\"id\":43,\"name\":\"documents\",\"package\":\"studio\",\"version\":0,\"sort_order\":115,\"checkDepencencies\":false}','2021-07-16 10:06:15',1,162,1,13,43,'172.20.0.1'),(1018,1,'studio-documents','{\"id\":1,\"name\":\"studio-documents\",\"description\":\"Documens module\",\"moduleId\":43,\"locked\":true,\"package\":\"studio\"}','2021-07-16 10:06:15',1,161,0,68,1,'172.20.0.1'),(1019,1,'Main','{\"id\":4,\"entityId\":69,\"name\":\"Main\",\"entity\":\"Document\",\"setAcl\":{\"2\":10}}','2021-07-16 10:06:30',1,163,1,10,4,'172.20.0.1'),(1020,1,'Client','{\"id\":7,\"fieldSetId\":4,\"name\":\"Client\",\"databaseName\":\"Client\",\"type\":\"Contact\",\"options\":\"{\\\"isOrganization\\\":false,\\\"addressBookId\\\":[]}\",\"forceAlterTable\":true}','2021-07-16 10:06:42',1,163,0,9,7,'172.20.0.1'),(1021,1,'Date in','{\"id\":8,\"fieldSetId\":4,\"name\":\"Date in\",\"databaseName\":\"Date_in\",\"type\":\"Date\",\"forceAlterTable\":true}','2021-07-16 10:07:04',1,163,0,9,8,'172.20.0.1'),(1022,1,'Date out','{\"id\":9,\"fieldSetId\":4,\"name\":\"Date out\",\"databaseName\":\"Date_out\",\"type\":\"Date\",\"forceAlterTable\":true}','2021-07-16 10:07:14',1,163,0,9,9,'172.20.0.1'),(1023,1,'Location','{\"id\":10,\"fieldSetId\":4,\"name\":\"Location\",\"databaseName\":\"Location\",\"type\":\"Select\",\"forceAlterTable\":true}','2021-07-16 10:07:37',1,163,0,9,10,'172.20.0.1'),(1024,2,'documents','{\"enabled\":[1,true]}','2021-07-16 10:08:07',1,162,1,13,43,'172.20.0.1'),(1025,2,'studio-documents','{\"locked\":[0,true]}','2021-07-16 10:08:07',1,161,0,68,1,'172.20.0.1'),(1026,1,'1','{\"id\":1}','2021-07-16 10:08:29',1,162,0,69,1,'172.20.0.1'),(1027,2,'Client','{\"hiddenInGrid\":[false,true],\"forceAlterTable\":[true,false]}','2021-07-16 10:08:54',1,163,0,9,7,'172.20.0.1'),(1028,2,'Date in','{\"options\":[\"[]\",null],\"hiddenInGrid\":[false,true],\"forceAlterTable\":[true,false]}','2021-07-16 10:08:59',1,163,0,9,8,'172.20.0.1'),(1029,1,'2','{\"id\":2}','2021-07-16 11:33:15',1,162,0,69,2,'172.20.0.1'),(1030,2,'Location','{\"options\":[\"[]\",null],\"forceAlterTable\":[true,false]}','2021-07-16 11:38:21',1,163,0,9,10,'172.20.0.1'),(1032,3,'2',NULL,'2021-07-16 11:59:40',1,162,0,69,2,'172.20.0.1'),(1034,2,'System Administrator','{\"lastLogin\":[\"2021-07-16T12:43:27+00:00\",\"2021-07-15T10:34:43+00:00\"],\"loginCount\":[35,34],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-16 12:43:27',1,5,0,21,1,'172.20.0.1'),(1035,4,'admin [172.20.0.1]',NULL,'2021-07-16 12:43:27',1,5,0,21,1,'172.20.0.1'),(1036,1,'ABC & Co','{\"id\":12,\"addressBookId\":1,\"lastName\":\"ABC & Co\",\"name\":\"ABC & Co\",\"uid\":\"12@host.docker.internal:8080\",\"uri\":\"12@host.docker.internal:8080.vcf\"}','2021-07-16 12:45:28',1,11,0,24,12,'172.20.0.1'),(1037,1,'5','{\"id\":5}','2021-07-16 12:45:28',1,162,0,69,5,'172.20.0.1'),(1039,1,'6','{\"id\":6}','2021-07-16 12:46:03',1,162,0,69,6,'172.20.0.1'),(1040,1,'YOYO & Co','{\"id\":14,\"addressBookId\":1,\"lastName\":\"YOYO & Co\",\"name\":\"YOYO & Co\",\"uid\":\"14@host.docker.internal:8080\",\"uri\":\"14@host.docker.internal:8080.vcf\"}','2021-07-16 12:46:03',1,11,0,24,14,'172.20.0.1'),(1041,1,'7','{\"id\":7}','2021-07-16 12:46:03',1,162,0,69,7,'172.20.0.1'),(1042,2,'Client','{\"options\":[\"{\\\"isOrganization\\\":true,\\\"addressBookId\\\":[]}\",\"{\\\"isOrganization\\\":false,\\\"addressBookId\\\":[]}\"],\"forceAlterTable\":[true,false]}','2021-07-16 12:46:59',1,163,0,9,7,'172.20.0.1'),(1043,1,'Smith Inc.','{\"id\":15,\"addressBookId\":4,\"lastName\":\"Smith Inc.\",\"salutation\":\"Dear sir\\/madam\",\"notes\":\"Just a demo company\",\"isOrganization\":true,\"name\":\"Smith Inc.\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"vatReverseCharge\":true,\"uid\":\"1@host.docker.internal:8080\",\"uri\":\"1@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-07-16 12:48:29',1,80,0,24,15,'172.20.0.1'),(1044,1,'ACME Corporation','{\"id\":16,\"addressBookId\":4,\"lastName\":\"ACME Corporation\",\"salutation\":\"Dear sir\\/madam\",\"notes\":\"Just a demo acme\",\"isOrganization\":true,\"name\":\"ACME Corporation\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"vatReverseCharge\":true,\"uid\":\"3@host.docker.internal:8080\",\"uri\":\"3@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@acme.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.acme.demo\"}]}','2021-07-16 12:48:29',1,80,0,24,16,'172.20.0.1'),(1045,1,NULL,'{\"id\":189,\"fromEntityTypeId\":24,\"fromId\":17,\"toEntityTypeId\":24,\"toId\":1,\"toEntity\":\"Contact\",\"fromEntity\":\"Contact\"}','2021-07-16 12:48:30',1,80,0,12,189,'172.20.0.1'),(1046,1,'John Smith','{\"id\":17,\"addressBookId\":4,\"firstName\":\"John\",\"lastName\":\"Smith\",\"salutation\":\"Dear Ms.\\/Mr. Smith\",\"notes\":\"Just a demo john\",\"name\":\"John Smith\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"photoBlobId\":\"a2b13489e9762bf7d7dfd63d72d45f0f47411c30\",\"jobTitle\":\"CEO\",\"uid\":\"2@host.docker.internal:8080\",\"uri\":\"2@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"john@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-07-16 12:48:30',1,80,0,24,17,'172.20.0.1'),(1047,1,NULL,'{\"id\":191,\"fromEntityTypeId\":24,\"fromId\":18,\"toEntityTypeId\":24,\"toId\":3,\"toEntity\":\"Contact\",\"fromEntity\":\"Contact\"}','2021-07-16 12:48:30',1,80,0,12,191,'172.20.0.1'),(1048,1,'Wile E. Coyote','{\"id\":18,\"addressBookId\":4,\"firstName\":\"Wile\",\"middleName\":\"E.\",\"lastName\":\"Coyote\",\"salutation\":\"Dear Ms.\\/Mr. E. Coyote\",\"notes\":\"Just a demo wile\",\"name\":\"Wile E. Coyote\",\"IBAN\":\"NL 00 ABCD 0123 34 1234\",\"vatNo\":\"NL 1234.56.789.B01\",\"photoBlobId\":\"0ec2f1f4f9fb41e8013fcc834991be30a8260750\",\"jobTitle\":\"CEO\",\"uid\":\"4@host.docker.internal:8080\",\"uri\":\"4@host.docker.internal:8080.vcf\",\"phoneNumbers\":[{\"type\":\"work\",\"number\":\"+31 (0) 10 - 1234567\"},{\"type\":\"mobile\",\"number\":\"+31 (0) 6 - 1234567\"}],\"emailAddresses\":[{\"type\":\"work\",\"email\":\"wile@smith.demo\"}],\"addresses\":[{\"formatted\":\"Kalverstraat 1\\n1012 NX Amsterdam\\n\",\"combinedStreet\":\"Kalverstraat 1\",\"type\":\"postal\",\"street\":\"Kalverstraat\",\"street2\":\"1\",\"zipCode\":\"1012 NX\",\"city\":\"Amsterdam\",\"state\":\"Noord-Holland\",\"country\":\"Netherlands\",\"countryCode\":\"NL\",\"latitude\":null,\"longitude\":null}],\"urls\":[{\"type\":\"homepage\",\"url\":\"http:\\/\\/www.smith.demo\"}]}','2021-07-16 12:48:30',1,80,0,24,18,'172.20.0.1'),(1049,1,'info','{\"id\":19,\"addressBookId\":4,\"firstName\":\"info\",\"salutation\":\"Dear Ms.\\/Mr. \",\"name\":\"info\",\"language\":\"en_uk\",\"uid\":\"5@host.docker.internal:8080\",\"uri\":\"5@host.docker.internal:8080.vcf\",\"emailAddresses\":[{\"type\":\"work\",\"email\":\"info@indonesiahijau.co.id\"}]}','2021-07-16 12:48:30',1,80,0,24,19,'172.20.0.1'),(1050,1,'Admin','{\"id\":20,\"addressBookId\":4,\"firstName\":\"Admin\",\"salutation\":\"Dear Ms.\\/Mr. \",\"name\":\"Admin\",\"language\":\"en_uk\",\"uid\":\"6@host.docker.internal:8080\",\"uri\":\"6@host.docker.internal:8080.vcf\",\"emailAddresses\":[{\"type\":\"work\",\"email\":\"admin@intermesh.localhost\"}]}','2021-07-16 12:48:30',1,80,0,24,20,'172.20.0.1'),(1051,1,'Jantje Beton','{\"id\":21,\"addressBookId\":4,\"firstName\":\"Jantje\",\"lastName\":\"Beton\",\"salutation\":\"Dear Ms.\\/Mr. Beton\",\"name\":\"Jantje Beton\",\"language\":\"en_uk\",\"uid\":\"7@host.docker.internal:8080\",\"uri\":\"7@host.docker.internal:8080.vcf\"}','2021-07-16 12:48:30',1,80,0,24,21,'172.20.0.1'),(1052,1,'Piet Jansen','{\"id\":22,\"addressBookId\":4,\"firstName\":\"Piet\",\"lastName\":\"Jansen\",\"salutation\":\"Dear Ms.\\/Mr. Jansen\",\"name\":\"Piet Jansen\",\"language\":\"en_uk\",\"uid\":\"8@host.docker.internal:8080\",\"uri\":\"8@host.docker.internal:8080.vcf\"}','2021-07-16 12:48:30',1,80,0,24,22,'172.20.0.1'),(1053,1,'gjhghj','{\"id\":23,\"addressBookId\":4,\"firstName\":\"gjhghj\",\"salutation\":\"Dear Ms.\\/Mr. \",\"name\":\"gjhghj\",\"language\":\"en_uk\",\"uid\":\"9@host.docker.internal:8080\",\"uri\":\"9@host.docker.internal:8080.vcf\"}','2021-07-16 12:48:30',1,80,0,24,23,'172.20.0.1'),(1054,1,'ABC & Co','{\"id\":24,\"addressBookId\":4,\"lastName\":\"ABC & Co\",\"salutation\":\"Dear Ms.\\/Mr. ABC & Co\",\"name\":\"ABC & Co\",\"uid\":\"12@host.docker.internal:8080\",\"uri\":\"12@host.docker.internal:8080.vcf\"}','2021-07-16 12:48:30',1,80,0,24,24,'172.20.0.1'),(1055,1,'YOYO & Co','{\"id\":25,\"addressBookId\":4,\"lastName\":\"YOYO & Co\",\"salutation\":\"Dear Ms.\\/Mr. YOYO & Co\",\"name\":\"YOYO & Co\",\"uid\":\"14@host.docker.internal:8080\",\"uri\":\"14@host.docker.internal:8080.vcf\"}','2021-07-16 12:48:30',1,80,0,24,25,'172.20.0.1'),(1056,2,'documents','{\"enabled\":[1,true]}','2021-07-16 12:52:44',1,162,1,13,43,'172.20.0.1'),(1057,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 12:52:45',1,161,0,68,1,'172.20.0.1'),(1058,2,'documents','{\"enabled\":[1,true]}','2021-07-16 12:58:01',1,162,1,13,43,'172.20.0.1'),(1059,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 12:58:01',1,161,0,68,1,'172.20.0.1'),(1060,2,'documents','{\"enabled\":[1,true]}','2021-07-16 12:58:32',1,162,1,13,43,'172.20.0.1'),(1061,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 12:58:32',1,161,0,68,1,'172.20.0.1'),(1062,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:07:08',1,162,1,13,43,'172.20.0.1'),(1063,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:07:08',1,161,0,68,1,'172.20.0.1'),(1064,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:07:35',1,162,1,13,43,'172.20.0.1'),(1065,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:07:36',1,161,0,68,1,'172.20.0.1'),(1066,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:08:17',1,162,1,13,43,'172.20.0.1'),(1067,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:08:17',1,161,0,68,1,'172.20.0.1'),(1068,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:08:58',1,162,1,13,43,'172.20.0.1'),(1069,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:08:58',1,161,0,68,1,'172.20.0.1'),(1070,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:09:49',1,162,1,13,43,'172.20.0.1'),(1071,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:09:49',1,161,0,68,1,'172.20.0.1'),(1072,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:10:15',1,162,1,13,43,'172.20.0.1'),(1073,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:10:15',1,161,0,68,1,'172.20.0.1'),(1074,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:16:55',1,162,1,13,43,'172.20.0.1'),(1075,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:16:55',1,161,0,68,1,'172.20.0.1'),(1076,2,'documents','{\"enabled\":[1,true]}','2021-07-16 13:18:02',1,162,1,13,43,'172.20.0.1'),(1077,2,'studio-documents','{\"locked\":[0,false]}','2021-07-16 13:18:02',1,161,0,68,1,'172.20.0.1'),(1078,1,'date_in','{\"id\":1,\"entityTypeId\":69,\"name\":\"date_in\",\"type\":\"variable\",\"setAcl\":{\"2\":10}}','2021-07-16 13:18:19',1,164,1,8,1,'172.20.0.1'),(1079,1,'notesencrypt','{\"id\":44,\"name\":\"notesencrypt\",\"package\":\"community\",\"version\":0,\"sort_order\":116,\"checkDepencencies\":false}','2021-07-16 13:40:20',1,165,1,13,44,'172.20.0.1'),(1080,1,'Test','{\"id\":173,\"noteBookId\":65,\"name\":\"Test\",\"content\":\"test\\u200b\"}','2021-07-16 13:41:14',1,18,0,34,173,'172.20.0.1'),(1081,2,'Test','{\"content\":[\"test\\u200b 2\",\"test\\u200b\"]}','2021-07-16 13:43:59',1,18,0,34,173,'172.20.0.1'),(1082,2,'System Administrator','{\"lastLogin\":[\"2021-07-16T13:45:52+00:00\",\"2021-07-16T12:43:27+00:00\"],\"loginCount\":[36,35],\"language\":[\"nl\",\"en_uk\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-16 13:45:52',1,5,0,21,1,'172.20.0.1'),(1083,4,'admin [172.20.0.1]',NULL,'2021-07-16 13:45:52',1,5,0,21,1,'172.20.0.1'),(1084,2,'Test','{\"content\":[\"{ENCRYPTED}5b33a2dc4cc8e0743a320861tvBzK8ufC4OOvEAI9S8VBAk+BzYfDnKeNA==\",\"test\\u200b 2\"]}','2021-07-16 13:46:40',1,18,0,34,173,'172.20.0.1'),(1085,1,'ewre','{\"id\":174,\"noteBookId\":65,\"name\":\"ewre\"}','2021-07-16 13:52:44',1,18,0,34,174,'172.20.0.1'),(1086,1,'Manager','{\"id\":11,\"fieldSetId\":1,\"name\":\"Manager\",\"databaseName\":\"Manager\",\"type\":\"Contact\",\"options\":\"{\\\"isOrganization\\\":false,\\\"addressBookId\\\":[]}\",\"forceAlterTable\":true}','2021-07-16 14:19:11',1,133,0,9,11,'172.20.0.1'),(1087,1,'Test','{\"id\":26,\"addressBookId\":1,\"lastName\":\"Test\",\"isOrganization\":true,\"name\":\"Test\",\"language\":\"nl\",\"uid\":\"26@localhost:8080\",\"uri\":\"26@localhost:8080.vcf\"}','2021-07-16 14:20:12',1,11,0,24,26,'172.20.0.1'),(1088,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:09:08',1,141,0,38,94,'172.20.0.1'),(1089,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-16 13:45:52\",\"2021-07-19 09:09:09\"]}','2021-07-19 09:09:09',1,4,0,21,1,'172.20.0.1'),(1090,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685749,\"mtime\":1626685749,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":96}','2021-07-19 09:09:09',1,141,0,38,96,'172.20.0.1'),(1091,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:09:09',1,4,0,21,1,'172.20.0.1'),(1092,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:09:14',1,141,0,38,96,'172.20.0.1'),(1093,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:09:09\",\"2021-07-19 09:09:14\"]}','2021-07-19 09:09:14',1,4,0,21,1,'172.20.0.1'),(1094,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685755,\"mtime\":1626685755,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":97}','2021-07-19 09:09:15',1,141,0,38,97,'172.20.0.1'),(1095,2,'System Administrator','{\"disk_usage\":[215366,2088854],\"modifiedAt\":[\"2021-07-19 09:09:14\",\"2021-07-19 09:09:15\"]}','2021-07-19 09:09:15',1,4,0,21,1,'172.20.0.1'),(1096,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:11:10',1,141,0,38,97,'172.20.0.1'),(1097,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:09:15\",\"2021-07-19 09:11:10\"]}','2021-07-19 09:11:10',1,4,0,21,1,'172.20.0.1'),(1098,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685870,\"mtime\":1626685870,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":98}','2021-07-19 09:11:10',1,141,0,38,98,'172.20.0.1'),(1099,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:11:10',1,4,0,21,1,'172.20.0.1'),(1100,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:11:14',1,141,0,38,98,'172.20.0.1'),(1101,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:11:10\",\"2021-07-19 09:11:14\"]}','2021-07-19 09:11:14',1,4,0,21,1,'172.20.0.1'),(1102,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685874,\"mtime\":1626685874,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":99}','2021-07-19 09:11:14',1,141,0,38,99,'172.20.0.1'),(1103,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:11:14',1,4,0,21,1,'172.20.0.1'),(1104,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:11:53',1,141,0,38,99,'172.20.0.1'),(1105,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:11:14\",\"2021-07-19 09:11:53\"]}','2021-07-19 09:11:53',1,4,0,21,1,'172.20.0.1'),(1106,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685913,\"mtime\":1626685913,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":100}','2021-07-19 09:11:53',1,141,0,38,100,'172.20.0.1'),(1107,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:11:53',1,4,0,21,1,'172.20.0.1'),(1108,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:11:58',1,141,0,38,100,'172.20.0.1'),(1109,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:11:53\",\"2021-07-19 09:11:58\"]}','2021-07-19 09:11:58',1,4,0,21,1,'172.20.0.1'),(1110,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685918,\"mtime\":1626685918,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":101}','2021-07-19 09:11:58',1,141,0,38,101,'172.20.0.1'),(1111,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:11:58',1,4,0,21,1,'172.20.0.1'),(1112,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:13:08',1,141,0,38,101,'172.20.0.1'),(1113,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:11:58\",\"2021-07-19 09:13:08\"]}','2021-07-19 09:13:08',1,4,0,21,1,'172.20.0.1'),(1114,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685988,\"mtime\":1626685988,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":102}','2021-07-19 09:13:09',1,141,0,38,102,'172.20.0.1'),(1115,2,'System Administrator','{\"disk_usage\":[215366,2088854],\"modifiedAt\":[\"2021-07-19 09:13:08\",\"2021-07-19 09:13:09\"]}','2021-07-19 09:13:09',1,4,0,21,1,'172.20.0.1'),(1116,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:13:13',1,141,0,38,102,'172.20.0.1'),(1117,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:13:09\",\"2021-07-19 09:13:13\"]}','2021-07-19 09:13:13',1,4,0,21,1,'172.20.0.1'),(1118,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626685994,\"mtime\":1626685993,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":103}','2021-07-19 09:13:14',1,141,0,38,103,'172.20.0.1'),(1119,2,'System Administrator','{\"disk_usage\":[215366,2088854],\"modifiedAt\":[\"2021-07-19 09:13:13\",\"2021-07-19 09:13:14\"]}','2021-07-19 09:13:14',1,4,0,21,1,'172.20.0.1'),(1120,2,'System Administrator','{\"lastLogin\":[\"2021-07-19T09:13:42+00:00\",\"2021-07-16T13:45:52+00:00\"],\"loginCount\":[37,36],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-19 09:13:42',1,5,0,21,1,'172.20.0.1'),(1121,4,'admin [172.20.0.1]',NULL,'2021-07-19 09:13:42',1,5,0,21,1,'172.20.0.1'),(1122,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:13:48',1,141,0,38,103,'172.20.0.1'),(1123,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:13:42\",\"2021-07-19 09:13:48\"]}','2021-07-19 09:13:48',1,4,0,21,1,'172.20.0.1'),(1124,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626686028,\"mtime\":1626686028,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":104}','2021-07-19 09:13:48',1,141,0,38,104,'172.20.0.1'),(1125,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:13:48',1,4,0,21,1,'172.20.0.1'),(1126,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:13:53',1,141,0,38,104,'172.20.0.1'),(1127,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:13:48\",\"2021-07-19 09:13:53\"]}','2021-07-19 09:13:53',1,4,0,21,1,'172.20.0.1'),(1128,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626686033,\"mtime\":1626686033,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":105}','2021-07-19 09:13:53',1,141,0,38,105,'172.20.0.1'),(1129,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:13:53',1,4,0,21,1,'172.20.0.1'),(1130,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:14:01',1,141,0,38,105,'172.20.0.1'),(1131,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:13:53\",\"2021-07-19 09:14:01\"]}','2021-07-19 09:14:01',1,4,0,21,1,'172.20.0.1'),(1132,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626686041,\"mtime\":1626686041,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":106}','2021-07-19 09:14:01',1,141,0,38,106,'172.20.0.1'),(1133,2,'System Administrator','{\"disk_usage\":[215366,2088854]}','2021-07-19 09:14:01',1,4,0,21,1,'172.20.0.1'),(1134,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-19 09:14:35',1,141,0,38,106,'172.20.0.1'),(1135,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-19 09:14:01\",\"2021-07-19 09:14:35\"]}','2021-07-19 09:14:35',1,4,0,21,1,'172.20.0.1'),(1136,1,'tmp/1/Triumph certifcaat.pdf','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626686078,\"mtime\":1626686077,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Triumph certifcaat.pdf\",\"extension\":\"pdf\",\"size\":1873488,\"id\":107}','2021-07-19 09:14:38',1,141,0,38,107,'172.20.0.1'),(1137,2,'System Administrator','{\"disk_usage\":[215366,2088854],\"modifiedAt\":[\"2021-07-19 09:14:35\",\"2021-07-19 09:14:38\"]}','2021-07-19 09:14:38',1,4,0,21,1,'172.20.0.1'),(1138,1,'test','{\"id\":45,\"name\":\"test\",\"package\":\"studio\",\"version\":0,\"sort_order\":117,\"checkDepencencies\":false}','2021-07-19 09:40:54',1,166,1,13,45,'172.20.0.1'),(1139,1,'studio-test','{\"id\":2,\"name\":\"studio-test\",\"description\":\"test\",\"moduleId\":45,\"locked\":true,\"package\":\"studio\"}','2021-07-19 09:40:54',1,161,0,68,2,'172.20.0.1'),(1140,2,'test','{\"enabled\":[1,true]}','2021-07-19 09:41:14',1,166,1,13,45,'172.20.0.1'),(1141,2,'studio-test','{\"locked\":[0,true]}','2021-07-19 09:41:14',1,161,0,68,2,'172.20.0.1'),(1142,2,'test','{\"enabled\":[1,true]}','2021-07-19 09:57:39',1,166,1,13,45,'172.20.0.1'),(1143,2,'studio-test','{\"locked\":[0,false]}','2021-07-19 09:58:25',1,161,0,68,2,'172.20.0.1'),(1144,1,'Test','{\"id\":5,\"entityId\":70,\"name\":\"Test\",\"entity\":\"Test\",\"setAcl\":{\"2\":10}}','2021-07-19 10:05:07',1,167,1,10,5,'172.20.0.1'),(1145,1,'Description','{\"id\":12,\"fieldSetId\":5,\"name\":\"Description\",\"databaseName\":\"Description\",\"options\":\"{\\\"maxLength\\\":50}\",\"hiddenInGrid\":false,\"forceAlterTable\":true}','2021-07-19 10:05:20',1,167,0,9,12,'172.20.0.1'),(1146,2,'test','{\"enabled\":[1,true]}','2021-07-19 10:06:00',1,166,1,13,45,'172.20.0.1'),(1147,2,'studio-test','{\"locked\":[0,false]}','2021-07-19 10:06:02',1,161,0,68,2,'172.20.0.1'),(1148,2,'Description','{\"forceAlterTable\":[true,false]}','2021-07-19 10:06:21',1,167,0,9,12,'172.20.0.1'),(1149,2,'test','{\"enabled\":[1,true]}','2021-07-19 10:06:32',1,166,1,13,45,'172.20.0.1'),(1150,2,'studio-test','{\"locked\":[0,false]}','2021-07-19 10:06:34',1,161,0,68,2,'172.20.0.1'),(1151,2,'test','{\"enabled\":[1,true]}','2021-07-19 10:07:01',1,166,1,13,45,'172.20.0.1'),(1152,2,'studio-test','{\"locked\":[0,false]}','2021-07-19 10:07:03',1,161,0,68,2,'172.20.0.1'),(1153,2,'test','{\"enabled\":[1,true]}','2021-07-19 10:09:24',1,166,1,13,45,'172.20.0.1'),(1154,2,'studio-test','{\"locked\":[0,false]}','2021-07-19 10:09:25',1,161,0,68,2,'172.20.0.1'),(1155,1,'test2','{\"id\":46,\"name\":\"test2\",\"package\":\"studio\",\"version\":0,\"sort_order\":118,\"checkDepencencies\":false}','2021-07-19 10:15:25',1,168,1,13,46,'172.20.0.1'),(1156,1,'studio-test2','{\"id\":3,\"name\":\"studio-test2\",\"description\":\"sdfds\",\"moduleId\":46,\"locked\":true,\"package\":\"studio\"}','2021-07-19 10:15:25',1,161,0,68,3,'172.20.0.1'),(1157,1,'Test','{\"id\":6,\"entityId\":71,\"name\":\"Test\",\"description\":\"test\",\"entity\":\"Test2\",\"setAcl\":{\"2\":10}}','2021-07-19 10:15:38',1,169,1,10,6,'172.20.0.1'),(1158,1,'Test','{\"id\":13,\"fieldSetId\":6,\"name\":\"Test\",\"databaseName\":\"Test\",\"options\":\"{\\\"maxLength\\\":50}\",\"forceAlterTable\":true}','2021-07-19 10:15:47',1,169,0,9,13,'172.20.0.1'),(1159,2,'test2','{\"enabled\":[1,true]}','2021-07-19 10:17:57',1,168,1,13,46,'172.20.0.1'),(1160,2,'studio-test2','{\"locked\":[0,true]}','2021-07-19 10:17:58',1,161,0,68,3,'172.20.0.1'),(1162,2,'test2','{\"enabled\":[1,true]}','2021-07-19 10:19:57',1,168,1,13,46,'172.20.0.1'),(1163,2,'studio-test2','{\"locked\":[0,false]}','2021-07-19 10:19:57',1,161,0,68,3,'172.20.0.1'),(1164,2,'test2','{\"enabled\":[1,true]}','2021-07-19 10:21:38',1,168,1,13,46,'172.20.0.1'),(1165,2,'studio-test2','{\"locked\":[0,false]}','2021-07-19 10:21:38',1,161,0,68,3,'172.20.0.1'),(1166,1,'test','{\"id\":48,\"name\":\"test\",\"package\":\"community\",\"version\":0,\"sort_order\":119,\"checkDepencencies\":false}','2021-07-19 10:49:56',1,171,1,13,48,'172.20.0.1'),(1167,1,'test','{\"id\":49,\"name\":\"test\",\"package\":\"test\",\"version\":0,\"sort_order\":120,\"checkDepencencies\":false}','2021-07-19 10:51:15',1,172,1,13,49,'172.20.0.1'),(1168,1,'test-test','{\"id\":4,\"name\":\"test-test\",\"description\":\"Another test oh no\",\"moduleId\":49,\"locked\":true,\"package\":\"test\"}','2021-07-19 10:51:15',1,161,0,68,4,'172.20.0.1'),(1169,2,'test','{\"enabled\":[1,true]}','2021-07-19 10:51:31',1,172,1,13,49,'172.20.0.1'),(1170,2,'test-test','{\"locked\":[0,true]}','2021-07-19 10:51:31',1,161,0,68,4,'172.20.0.1'),(1171,1,'test','{\"id\":50,\"name\":\"test\",\"package\":\"test2\",\"version\":0,\"sort_order\":121,\"checkDepencencies\":false}','2021-07-19 11:00:34',1,173,1,13,50,'172.20.0.1'),(1172,1,'test2-test','{\"id\":5,\"name\":\"test2-test\",\"description\":\"dfds\",\"moduleId\":50,\"locked\":true,\"package\":\"test2\"}','2021-07-19 11:00:35',1,161,0,68,5,'172.20.0.1'),(1173,1,'test','{\"id\":51,\"name\":\"test\",\"package\":\"test3\",\"version\":0,\"sort_order\":122,\"checkDepencencies\":false}','2021-07-19 11:01:26',1,174,1,13,51,'172.20.0.1'),(1174,1,'test3-test','{\"id\":6,\"name\":\"test3-test\",\"description\":\"asdas\",\"moduleId\":51,\"locked\":true,\"package\":\"test3\"}','2021-07-19 11:02:40',1,161,0,68,6,'172.20.0.1'),(1175,1,'test','{\"id\":52,\"name\":\"test\",\"package\":\"test4\",\"version\":0,\"sort_order\":123,\"checkDepencencies\":false}','2021-07-19 11:04:50',1,175,1,13,52,'172.20.0.1'),(1176,2,'test','{\"enabled\":[false,true]}','2021-07-19 11:09:39',1,172,1,13,49,'172.20.0.1'),(1177,3,'test',NULL,'2021-07-19 11:09:39',1,172,1,13,49,'172.20.0.1'),(1178,2,'test','{\"enabled\":[false,true]}','2021-07-19 11:09:45',1,173,1,13,50,'172.20.0.1'),(1179,3,'test',NULL,'2021-07-19 11:09:45',1,173,1,13,50,'172.20.0.1'),(1180,2,'test','{\"enabled\":[false,true]}','2021-07-19 11:09:49',1,174,1,13,51,'172.20.0.1'),(1181,3,'test',NULL,'2021-07-19 11:09:49',1,174,1,13,51,'172.20.0.1'),(1182,2,'test','{\"enabled\":[false,true]}','2021-07-19 11:09:53',1,175,1,13,52,'172.20.0.1'),(1183,3,'test',NULL,'2021-07-19 11:09:53',1,175,1,13,52,'172.20.0.1'),(1184,2,'test','{\"enabled\":[false,true]}','2021-07-19 11:10:03',1,166,1,13,45,'172.20.0.1'),(1185,3,'test',NULL,'2021-07-19 11:10:03',1,166,1,13,45,'172.20.0.1'),(1186,2,'test2','{\"enabled\":[false,true]}','2021-07-19 11:10:07',1,168,1,13,46,'172.20.0.1'),(1187,3,'test2',NULL,'2021-07-19 11:10:07',1,168,1,13,46,'172.20.0.1'),(1188,1,'Action date','{\"id\":14,\"fieldSetId\":1,\"name\":\"Action date\",\"databaseName\":\"Action_date\",\"type\":\"DateTime\",\"hiddenInGrid\":false,\"forceAlterTable\":true}','2021-07-19 11:26:53',1,133,0,9,14,'172.20.0.1'),(1189,1,'date','{\"id\":15,\"fieldSetId\":1,\"name\":\"date\",\"databaseName\":\"date\",\"type\":\"Date\",\"forceAlterTable\":true}','2021-07-19 11:28:52',1,133,0,9,15,'172.20.0.1'),(1190,2,'System Administrator','{\"sort_name\":[\"last_name\",\"first_name\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-19 11:35:00',1,5,0,21,1,'172.20.0.1'),(1191,1,'Peter Clemens','{\"id\":27,\"addressBookId\":1,\"firstName\":\"Peter\",\"lastName\":\"Clemens\",\"name\":\"Peter Clemens\",\"language\":\"nl\",\"uid\":\"27@localhost:8080\",\"uri\":\"27@localhost:8080.vcf\"}','2021-07-19 11:35:48',1,11,0,24,27,'172.20.0.1'),(1192,2,'System Administrator','{\"sort_name\":[\"first_name\",\"last_name\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-19 11:41:32',1,5,0,21,1,'172.20.0.1'),(1193,2,'System Administrator','{\"sort_name\":[\"last_name\",\"first_name\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-19 12:03:56',1,5,0,21,1,'172.20.0.1'),(1194,2,'Standard project','{\"fields\":[\"responsible_user_id,status_date,customer,budget_fees,contact,expenses\",\"responsible_user_id,expenses,std_task_required,customer,contact,budget_fees\"],\"name_template\":[\"\",\"%y-{autoid}\"]}','2021-07-19 12:38:42',1,113,1,61,2,'172.20.0.1'),(1195,2,'[001] Develop Rocket Launcher','{\"start_time\":[1624871095,1624831200],\"due_time\":[1627463095,1627423200]}','2021-07-19 12:39:07',1,123,0,57,3,'172.20.0.1'),(1196,2,'ABC & Co','{\"starred\":[true,null]}','2021-07-19 13:06:14',1,11,0,24,12,'172.20.0.1'),(1197,2,'Smith Inc.','{\"starred\":[true,null]}','2021-07-19 13:07:18',1,80,0,24,15,'172.20.0.1'),(1198,1,'Recurring 3 times','{\"uuid\":\"4af4950a-0af4-5024-ae28-5fd88f47a707\",\"user_id\":1,\"start_time\":1626696900,\"end_time\":1626702300,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"\",\"repeat_end_time\":0,\"ctime\":1626700237,\"mtime\":1626700237,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"FREQ=DAILY;COUNT=3\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":1,\"id\":25,\"name\":\"Recurring 3 times\",\"description\":\"\",\"files_folder_id\":0}','2021-07-19 13:10:37',1,39,0,37,25,'172.20.0.1'),(1199,1,'Recurring until','{\"uuid\":\"48375d28-3b7b-5317-8944-34fff7b5abd0\",\"user_id\":1,\"start_time\":1626713100,\"end_time\":1626715800,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"\",\"repeat_end_time\":1626991140,\"ctime\":1626700729,\"mtime\":1626700729,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"FREQ=DAILY;UNTIL=20210722T235900\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":1,\"id\":26,\"name\":\"Recurring until\",\"description\":\"\",\"files_folder_id\":0}','2021-07-19 13:18:49',1,39,0,37,26,'172.20.0.1'),(1200,1,'No recurence','{\"uuid\":\"acaf173a-344b-5fca-a182-037470b10d39\",\"user_id\":1,\"start_time\":1626683400,\"end_time\":1626687900,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"\",\"repeat_end_time\":0,\"ctime\":1626701136,\"mtime\":1626701136,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":1,\"id\":27,\"name\":\"No recurence\",\"description\":\"\",\"files_folder_id\":0}','2021-07-19 13:25:36',1,39,0,37,27,'172.20.0.1'),(1201,1,'repeat forever','{\"uuid\":\"c0a57c58-3c29-51d6-a455-f5112a56faa7\",\"user_id\":1,\"start_time\":1626690600,\"end_time\":1626693300,\"timezone\":\"Europe\\/Amsterdam\",\"all_day_event\":0,\"location\":\"\",\"repeat_end_time\":0,\"ctime\":1626701966,\"mtime\":1626701966,\"muser_id\":1,\"busy\":1,\"status\":\"CONFIRMED\",\"resource_event_id\":0,\"private\":0,\"rrule\":\"FREQ=DAILY\",\"background\":\"EBF1E2\",\"read_only\":0,\"exception_for_event_id\":0,\"recurrence_id\":\"\",\"is_organizer\":1,\"calendar_id\":1,\"id\":28,\"name\":\"repeat forever\",\"description\":\"\",\"files_folder_id\":0}','2021-07-19 13:39:26',1,39,0,37,28,'172.20.0.1'),(1202,2,'tickets','{\"version\":[164,163]}','2021-07-19 14:55:24',1,64,1,13,27,'172.20.0.1'),(1203,2,'tickets','{\"version\":[165,164]}','2021-07-19 14:55:24',1,64,1,13,27,'172.20.0.1'),(1204,2,'projects2','{\"version\":[399,398]}','2021-07-19 14:55:24',1,106,1,13,33,'172.20.0.1'),(1205,2,'projects2','{\"version\":[400,399]}','2021-07-19 14:55:24',1,106,1,13,33,'172.20.0.1'),(1206,2,'projects2','{\"version\":[401,400]}','2021-07-19 14:55:24',1,106,1,13,33,'172.20.0.1'),(1207,2,'files','{\"version\":[137,136]}','2021-07-19 14:55:24',1,24,1,13,13,'172.20.0.1'),(1208,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.67\",\"6.5.66\"]}','2021-07-19 14:55:26',1,153,0,67,1,'172.20.0.1'),(1209,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.67\",\"6.5.66\"]}','2021-07-19 14:55:27',1,153,0,67,3,'172.20.0.1'),(1210,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.67\",\"6.5.66\"]}','2021-07-19 14:55:28',1,153,0,67,4,'172.20.0.1'),(1211,2,'System Administrator','{\"lastLogin\":[\"2021-07-20T07:36:08+00:00\",\"2021-07-19T09:13:42+00:00\"],\"loginCount\":[38,37],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-20 07:36:08',1,5,0,21,1,'172.20.0.1'),(1212,4,'admin [172.20.0.1]',NULL,'2021-07-20 07:36:08',1,5,0,21,1,'172.20.0.1'),(1213,1,'Group','{\"id\":7,\"entityId\":24,\"name\":\"Group\",\"filter\":\"[]\",\"entity\":\"Contact\",\"setAcl\":{\"2\":10}}','2021-07-20 07:58:56',1,176,1,10,7,'172.20.0.1'),(1214,1,'Month','{\"id\":16,\"fieldSetId\":7,\"name\":\"Month\",\"databaseName\":\"Month\",\"type\":\"Select\",\"forceAlterTable\":true}','2021-07-20 07:59:20',1,176,0,9,16,'172.20.0.1'),(1215,2,'Shared','{\"columns\":[1,2]}','2021-07-20 07:59:55',1,133,1,10,1,'172.20.0.1'),(1216,2,'Group','{\"columns\":[1,2]}','2021-07-20 08:00:01',1,176,1,10,7,'172.20.0.1'),(1217,2,'System Administrator','{\"lastLogin\":[\"2021-07-20T08:01:21+00:00\",\"2021-07-20T07:36:08+00:00\"],\"loginCount\":[39,38],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-20 08:01:21',1,5,0,21,1,'172.20.0.1'),(1218,4,'admin [172.20.0.1]',NULL,'2021-07-20 08:01:21',1,5,0,21,1,'172.20.0.1'),(1219,2,'newsletters','{\"enabled\":[false,true]}','2021-07-20 08:06:34',1,67,1,13,28,'172.20.0.1'),(1220,2,'System Administrator','{\"lastLogin\":[\"2021-07-20T08:10:39+00:00\",\"2021-07-20T08:01:21+00:00\"],\"loginCount\":[40,39],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-20 08:10:39',1,5,0,21,1,'172.20.0.1'),(1221,4,'admin [172.20.0.1]',NULL,'2021-07-20 08:10:39',1,5,0,21,1,'172.20.0.1'),(1222,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.68\",\"6.5.67\"]}','2021-07-20 11:29:11',1,153,0,67,1,'172.20.0.1'),(1223,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.68\",\"6.5.67\"]}','2021-07-20 11:29:12',1,153,0,67,3,'172.20.0.1'),(1224,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.68\",\"6.5.67\"]}','2021-07-20 11:29:13',1,153,0,67,4,'172.20.0.1'),(1225,2,'System Administrator','{\"lastLogin\":[\"2021-07-20T11:29:23+00:00\",\"2021-07-20T08:10:39+00:00\"],\"loginCount\":[41,40],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-20 11:29:23',1,5,0,21,1,'172.20.0.1'),(1226,4,'admin [172.20.0.1]',NULL,'2021-07-20 11:29:23',1,5,0,21,1,'172.20.0.1'),(1227,1,'carddav','{\"id\":53,\"name\":\"carddav\",\"package\":\"community\",\"version\":0,\"sort_order\":120,\"checkDepencencies\":false}','2021-07-20 11:30:56',1,177,1,13,53,'172.20.0.1'),(1228,2,'System Administrator','{\"lastLogin\":[\"2021-07-20T11:32:07+00:00\",\"2021-07-20T11:29:23+00:00\"],\"loginCount\":[42,41],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-20 11:32:07',1,5,0,21,1,'172.20.0.1'),(1229,4,'admin [172.20.0.1]',NULL,'2021-07-20 11:32:07',1,5,0,21,1,'172.20.0.1'),(1230,2,'info','{\"vcardBlobId\":[\"250ea8dbd7a92824a81eacc8125c376f32a87f4e\",null]}','2021-07-20 11:33:24',1,11,0,24,5,'172.20.0.1'),(1231,2,'Admin','{\"vcardBlobId\":[\"7b6417a12cfd91021a114b03333214c1c5ee9ad7\",null]}','2021-07-20 11:33:24',1,11,0,24,6,'172.20.0.1'),(1232,2,'Jantje Beton','{\"vcardBlobId\":[\"b7ffc18c76e66d0fdcb8de8c892a29d9213d1c7b\",null]}','2021-07-20 11:33:25',1,11,0,24,7,'172.20.0.1'),(1233,2,'gjhghj','{\"vcardBlobId\":[\"55ffc4ca8945d6cbe420e3d5566ccb57128266fc\",null]}','2021-07-20 11:33:25',1,11,0,24,9,'172.20.0.1'),(1234,2,'ABC & Co','{\"vcardBlobId\":[\"fe464d739fd83bbe7aa26f21d7a097fb1d1a753b\",null]}','2021-07-20 11:33:26',1,11,0,24,12,'172.20.0.1'),(1235,2,'YOYO & Co','{\"vcardBlobId\":[\"deea31054247d8f8bc6baa4d1f555208a846a226\",null]}','2021-07-20 11:33:26',1,11,0,24,14,'172.20.0.1'),(1236,2,'Test','{\"vcardBlobId\":[\"47dcc09cd5ab65e913115e6004abfb4f06d9a0d2\",null]}','2021-07-20 11:33:27',1,11,0,24,26,'172.20.0.1'),(1237,2,'Peter Clemens','{\"vcardBlobId\":[\"524aff24c8a2818ba4c1d8c2b8b2b22bc1425b4a\",null]}','2021-07-20 11:33:28',1,11,0,24,27,'172.20.0.1'),(1238,1,'From Demo','{\"id\":28,\"addressBookId\":5,\"firstName\":\"From\",\"lastName\":\"Demo\",\"name\":\"From Demo\",\"language\":\"nl\",\"uid\":\"28@localhost:8080\",\"uri\":\"28@localhost:8080.vcf\"}','2021-07-20 11:42:18',1,85,0,24,28,'172.20.0.1'),(1239,2,'System Administrator','{\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"syncSettings\":[{\"user_id\":1,\"account_id\":1,\"noteBooks\":[{\"userId\":1,\"noteBookId\":65,\"isDefault\":true}],\"addressBooks\":[{\"userId\":1,\"addressBookId\":1,\"isDefault\":true},{\"userId\":1,\"addressBookId\":5,\"isDefault\":false}]},null]}','2021-07-20 11:44:21',1,5,0,21,1,'172.20.0.1'),(1240,2,'From Demo','{\"vcardBlobId\":[\"f978eb2a6dca767fd517d0e9aaac771b99300ce6\",null]}','2021-07-20 11:44:38',1,85,0,24,28,'172.20.0.1'),(1241,1,'Demo 2','{\"id\":29,\"addressBookId\":5,\"firstName\":\"Demo\",\"lastName\":\"2\",\"name\":\"Demo 2\",\"language\":\"nl\",\"uid\":\"29@localhost:8080\",\"uri\":\"29@localhost:8080.vcf\"}','2021-07-20 11:45:27',1,85,0,24,29,'172.20.0.1'),(1242,2,'Demo 2','{\"vcardBlobId\":[\"fc32a048a4f726fb116421a619aa55adbe859944\",null]}','2021-07-20 11:45:35',1,85,0,24,29,'172.20.0.1'),(1243,1,'Demo 3','{\"id\":30,\"addressBookId\":5,\"firstName\":\"Demo\",\"lastName\":\"3\",\"name\":\"Demo 3\",\"language\":\"nl\",\"uid\":\"30@localhost:8080\",\"uri\":\"30@localhost:8080.vcf\"}','2021-07-20 11:46:10',1,85,0,24,30,'172.20.0.1'),(1244,2,'From Demo 1','{\"lastName\":[\"Demo 1\",\"Demo\"],\"name\":[\"From Demo 1\",\"From Demo\"]}','2021-07-20 11:47:35',1,85,0,24,28,'172.20.0.1'),(1245,2,'From Demo 1','{\"vcardBlobId\":[\"7bfbcf7bd8eca79efd7a49593c535fd18f91205d\",\"f978eb2a6dca767fd517d0e9aaac771b99300ce6\"]}','2021-07-20 11:48:04',1,85,0,24,28,'172.20.0.1'),(1246,2,'Cont YOYO & Co','{\"firstName\":[\"Cont\",\"\"],\"name\":[\"Cont YOYO & Co\",\"YOYO & Co\"]}','2021-07-20 11:49:22',1,11,0,24,14,'172.20.0.1'),(1247,2,'Cont YOYO & Co','{\"vcardBlobId\":[\"54356b3c7d716b48ab13b77b3f4d61012d361c27\",\"deea31054247d8f8bc6baa4d1f555208a846a226\"]}','2021-07-20 11:49:50',1,11,0,24,14,'172.20.0.1'),(1248,2,'Cont YOYO & Co','{\"notes\":[\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\",null]}','2021-07-20 12:20:24',1,11,0,24,14,'172.20.0.1'),(1249,2,'Cont YOYO & Co','{\"notes\":[\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\\n\\n----------------------------------------\\n\\nEn nu nog langer\",\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\"]}','2021-07-20 12:20:59',1,11,0,24,14,'172.20.0.1'),(1250,2,'Cont YOYO & Co','{\"notes\":[\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\\n\\n**********************************************************************************************************************************\\n\\nEn nu nog langer\",\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\\n\\n----------------------------------------\\n\\nEn nu nog langer\"]}','2021-07-20 12:21:42',1,11,0,24,14,'172.20.0.1'),(1251,2,'Cont YOYO & Co','{\"notes\":[\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\\n\\n**********************************************************************************************************************************\\n\\nEn nu nog langer\\n\\njaja\",\"Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst. Een erg lange tekst.\\n\\n**********************************************************************************************************************************\\n\\nEn nu nog langer\"]}','2021-07-20 12:21:57',1,11,0,24,14,'172.20.0.1'),(1252,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.69\",\"6.5.68\"]}','2021-07-22 11:16:31',1,153,0,67,1,'172.20.0.1'),(1253,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.69\",\"6.5.68\"]}','2021-07-22 11:16:33',1,153,0,67,3,'172.20.0.1'),(1254,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.69\",\"6.5.68\"]}','2021-07-22 11:16:35',1,153,0,67,4,'172.20.0.1'),(1255,2,'System Administrator','{\"lastLogin\":[\"2021-07-22T11:16:46+00:00\",\"2021-07-20T11:32:07+00:00\"],\"loginCount\":[43,42],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 11:16:46',1,5,0,21,1,'172.20.0.1'),(1256,4,'admin [172.20.0.1]',NULL,'2021-07-22 11:16:46',1,5,0,21,1,'172.20.0.1'),(1257,1,'zpushadmin','{\"name\":\"zpushadmin\",\"sort_order\":121,\"admin_menu\":0,\"enabled\":1,\"modifiedAt\":\"2021-07-22 11:40:55\",\"aclId\":178,\"version\":7,\"id\":54}','2021-07-22 11:40:55',1,178,1,13,54,'172.20.0.1'),(1258,2,'System Administrator','{\"mute_sound\":[true,false],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 11:47:37',1,5,0,21,1,'172.20.0.1'),(1259,2,'caldav','{\"enabled\":[1,0],\"modifiedAt\":[\"2021-07-12 08:25:26\",\"2021-07-22 12:17:40\"]}','2021-07-22 12:17:40',1,147,1,13,39,'172.20.0.1'),(1260,2,'caldav','{\"enabled\":[0,1],\"modifiedAt\":[\"2021-07-22 12:17:40\",\"2021-07-22 12:17:42\"]}','2021-07-22 12:17:42',1,147,1,13,39,'172.20.0.1'),(1261,2,'System Administrator','{\"lastLogin\":[\"2021-07-22T12:47:14+00:00\",\"2021-07-22T11:16:46+00:00\"],\"loginCount\":[44,43],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 12:47:14',1,5,0,21,1,'172.20.0.1'),(1262,4,'admin [172.20.0.1]',NULL,'2021-07-22 12:47:14',1,5,0,21,1,'172.20.0.1'),(1263,1,'Super Template','{\"id\":11,\"name\":\"Super Template\",\"setAcl\":[]}','2021-07-22 12:48:11',1,180,1,11,11,'172.20.0.1'),(1264,1,'Super template','{\"user_id\":1,\"type\":0,\"acl_id\":181,\"extension\":\"\",\"id\":3,\"name\":\"Super template\",\"content\":\"Message-ID: <53ec9b60b00d3940cdfab6220d9ee072@localhost>\\r\\nDate: Thu, 22 Jul 2021 14:48:48 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent- ..Cut off at 500 chars.\"}','2021-07-22 12:48:49',1,181,1,22,3,'172.20.0.1'),(1265,2,'Super Template','{\"templateId\":[3,null]}','2021-07-22 12:49:09',1,180,1,11,11,'172.20.0.1'),(1266,2,'Internal','{\"templateId\":[1,null]}','2021-07-22 12:49:12',1,3,1,11,3,'172.20.0.1'),(1268,2,'grouptemplates','{\"enabled\":[false,true]}','2021-07-22 12:51:12',1,154,1,13,41,'172.20.0.1'),(1269,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.69\"]}','2021-07-22 14:45:44',1,153,0,67,1,'172.20.0.1'),(1270,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.69\"]}','2021-07-22 14:45:45',1,153,0,67,3,'172.20.0.1'),(1271,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.69\"]}','2021-07-22 14:45:46',1,153,0,67,4,'172.20.0.1'),(1272,3,'tmp/1/Triumph certifcaat.pdf','null','2021-07-22 14:46:09',1,141,0,38,107,'172.20.0.1'),(1273,2,'System Administrator','{\"disk_usage\":[2088854,215366],\"modifiedAt\":[\"2021-07-22 12:47:14\",\"2021-07-22 14:46:10\"]}','2021-07-22 14:46:10',1,4,0,21,1,'172.20.0.1'),(1274,1,'tmp/1/Test.odt','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1626965170,\"mtime\":1626965170,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"Test.odt\",\"extension\":\"odt\",\"size\":4485,\"id\":108}','2021-07-22 14:46:10',1,141,0,38,108,'172.20.0.1'),(1275,2,'System Administrator','{\"disk_usage\":[215366,219851]}','2021-07-22 14:46:10',1,4,0,21,1,'172.20.0.1'),(1276,2,'grouptemplates','{\"enabled\":[true,false]}','2021-07-22 14:52:16',1,154,1,13,41,'172.20.0.1'),(1277,1,'super','{\"id\":13,\"name\":\"super\",\"isUserGroupFor\":8,\"users\":[8]}','2021-07-22 14:52:58',1,183,1,11,13,'172.20.0.1'),(1278,1,'Super template','{\"user_id\":8,\"type\":0,\"acl_id\":184,\"extension\":\"\",\"name\":\"Super template\",\"content\":\"Message-ID: <53ec9b60b00d3940cdfab6220d9ee072@localhost>\\r\\nDate: Thu, 22 Jul 2021 14:48:48 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent- ..Cut off at 500 chars.\",\"id\":4}','2021-07-22 14:52:58',1,184,1,22,4,'172.20.0.1'),(1279,1,'super','{\"id\":8,\"username\":\"super\",\"displayName\":\"super\",\"email\":\"super@intermesh.nl\",\"recoveryEmail\":\"super@intermesh.nl\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/super\",\"password\":\"$2y$10$Fb7a7cANLwJM8YpmIT7zG.xc9PRSadJSECtogjT\\/OjvLd5DoKVPCy\",\"groups\":[2,11,13],\"addressBookSettings\":{\"defaultAddressBookId\":null,\"sortBy\":\"name\",\"userId\":8},\"employee\":{\"id\":8,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":null,\"userId\":8},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":8},\"emailSettings\":{\"id\":8,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":8,\"account_id\":0,\"noteBooks\":[],\"addressBooks\":[]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":8},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":8}}','2021-07-22 14:52:58',1,5,0,21,8,'172.20.0.1'),(1280,1,'super','{\"id\":9,\"name\":\"super\",\"salutationTemplate\":\"Geachte [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]heer\\/mevrouw[else][if {{contact.gender}}==\\\"M\\\"]heer[else]mevrouw[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-07-22 14:53:18',1,185,1,23,9,'172.20.0.1'),(1281,1,'super','{\"id\":71,\"name\":\"super\"}','2021-07-22 14:53:18',1,186,1,35,71,'172.20.0.1'),(1282,2,'super','{\"groups\":[[2,11,13,3],[2,11,13]],\"addressBookSettings\":[{\"defaultAddressBookId\":9,\"sortBy\":\"name\",\"userId\":8},null],\"employee\":[{\"id\":8,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"notesSettings\":[{\"defaultNoteBookId\":71,\"userId\":8},null],\"emailSettings\":[{\"id\":8,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null],\"syncSettings\":[{\"user_id\":8,\"account_id\":0,\"noteBooks\":[{\"userId\":8,\"noteBookId\":71,\"isDefault\":true}],\"addressBooks\":[{\"userId\":8,\"addressBookId\":9,\"isDefault\":true}]},null]}','2021-07-22 14:53:18',1,5,0,21,8,'172.20.0.1'),(1283,2,'Super template','{\"content\":[\"Message-ID: <53ec9b60b00d3940cdfab6220d9ee072@localhost>\\r\\nDate: Thu, 22 Jul 2021 14:48:48 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent- ..Cut off at 500 chars.\",\"Message-ID: <0502947191438cb9985a0f2997c16951@localhost>\\r\\nDate: Thu, 22 Jul 2021 16:53:39 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B 1\\r\\n\\r\\n--_=_swift_1626965619_0ba69a3217d5101e39effffba9d88c88_=_\\r\\nConten ..Cut off at 500 chars.\"]}','2021-07-22 14:53:40',1,181,1,22,3,'172.20.0.1'),(1284,2,'Super template 2','{\"content\":[\"Message-ID: <53ec9b60b00d3940cdfab6220d9ee072@localhost>\\r\\nDate: Thu, 22 Jul 2021 14:48:48 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B\\r\\n\\r\\n--_=_swift_1626958128_2b140120cb26249a99d8d4ff147ab668_=_\\r\\nContent- ..Cut off at 500 chars.\",\"Message-ID: <019d30537f812ba87e4dfa7ce0899bd7@localhost>\\r\\nDate: Thu, 22 Jul 2021 16:53:45 +0200\\r\\nFrom: \\r\\nMIME-Version: 1.0\\r\\nContent-Type: multipart\\/alternative;\\r\\n boundary=\\\"_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\\\"\\r\\nX-Mailer: Group-Office (6.5.70)\\r\\n\\r\\n\\r\\n--_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\\r\\nContent-Type: text\\/plain; charset=UTF-8\\r\\nContent-Transfer-Encoding: quoted-printable\\r\\n\\r\\nSuper=E2=80=8B\\r\\n\\r\\n--_=_swift_1626965625_011ece55eaa910f8f6e1ac0c7fced69a_=_\\r\\nContent- ..Cut off at 500 chars.\"],\"name\":[\"Super template\",\"Super template 2\"]}','2021-07-22 14:53:45',1,184,1,22,4,'172.20.0.1'),(1285,2,'super','{\"lastLogin\":[\"2021-07-22T14:55:15+00:00\",null],\"loginCount\":[1,0],\"language\":[\"nl\",\"en\"],\"employee\":[{\"id\":8,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":8,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 14:55:15',NULL,5,0,21,8,'172.20.0.1'),(1286,4,'super [172.20.0.1]',NULL,'2021-07-22 14:55:15',NULL,5,0,21,8,'172.20.0.1'),(1287,1,'super','{\"user_id\":8,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":8,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"super\",\"parent_id\":1,\"mtime\":1626965718,\"ctime\":1626965718,\"id\":37}','2021-07-22 14:55:19',1,24,1,39,37,'172.20.0.1'),(1288,2,'calendar','{\"muser_id\":[1,8]}','2021-07-22 14:55:19',1,24,1,39,1,'172.20.0.1'),(1289,2,'super','{\"acl_id\":[0,187]}','2021-07-22 14:55:19',1,187,1,39,37,'172.20.0.1'),(1290,1,'super','{\"group_id\":1,\"user_id\":8,\"acl_id\":187,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":37,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"super\",\"id\":9}','2021-07-22 14:55:19',1,187,1,36,9,'172.20.0.1'),(1291,5,'super [172.20.0.1]',NULL,'2021-07-22 14:55:46',NULL,5,0,21,8,'172.20.0.1'),(1292,2,'System Administrator','{\"lastLogin\":[\"2021-07-22T14:55:50+00:00\",\"2021-07-22T12:47:14+00:00\"],\"loginCount\":[45,44],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 14:55:50',1,5,0,21,1,'172.20.0.1'),(1293,4,'admin [172.20.0.1]',NULL,'2021-07-22 14:55:50',1,5,0,21,1,'172.20.0.1'),(1294,2,'super','{\"lastLogin\":[\"2021-07-22T14:56:47+00:00\",\"2021-07-22T14:55:15+00:00\"],\"loginCount\":[2,1],\"employee\":[{\"id\":8,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":8,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 14:56:47',NULL,5,0,21,8,'172.20.0.1'),(1295,4,'super [172.20.0.1]',NULL,'2021-07-22 14:56:47',NULL,5,0,21,8,'172.20.0.1'),(1296,5,'super [172.20.0.1]',NULL,'2021-07-22 14:57:12',NULL,5,0,21,8,'172.20.0.1'),(1297,2,'System Administrator','{\"lastLogin\":[\"2021-07-22T14:57:16+00:00\",\"2021-07-22T14:55:50+00:00\"],\"loginCount\":[46,45],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-22 14:57:16',1,5,0,21,1,'172.20.0.1'),(1298,4,'admin [172.20.0.1]',NULL,'2021-07-22 14:57:16',1,5,0,21,1,'172.20.0.1'),(1299,2,'System Administrator','{\"shortDateInList\":[false,true],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:23:04',1,5,0,21,1,'172.20.0.1'),(1300,2,'info','{\"salutation\":[\"Dear \",\"Dear Ms.\\/Mr. \"]}','2021-07-23 07:24:19',1,11,0,24,5,'172.20.0.1'),(1301,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SUT6DJXDXXTUVDIH\",\"isEnabled\":false,\"qrBlobId\":\"58d780249283ba9e61755c77dc6acf9b43259e97\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:43:51',1,5,0,21,1,'172.20.0.1'),(1302,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"H2KDCXSUPEVDGUVW\",\"isEnabled\":false,\"qrBlobId\":\"18d28d2bbd951194bf9321b6abe1d61b015363a4\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:47:57',1,5,0,21,1,'172.20.0.1'),(1303,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"NOANXFBTH2BM66NC\",\"isEnabled\":false,\"qrBlobId\":\"3f37a117775ab9b1ce9a9eafb5478b509b1397c3\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:50:30',1,5,0,21,1,'172.20.0.1'),(1304,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"NM3QNU4RYF7MBKLF\",\"isEnabled\":false,\"qrBlobId\":\"38edaae4263a27569db4604d98f3f9b968bc5f5f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:50:59',1,5,0,21,1,'172.20.0.1'),(1305,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DCQRCSILOLQO5XU7\",\"isEnabled\":false,\"qrBlobId\":\"a260b122a7d51e33ff5a143d8610c560d41f01b4\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:50:59',1,5,0,21,1,'172.20.0.1'),(1306,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"QE55XRB53FZWLP6I\",\"isEnabled\":false,\"qrBlobId\":\"c4d3502c7665fecf991f6e1ec722e811811af7b0\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:00',1,5,0,21,1,'172.20.0.1'),(1307,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3Z6GXEWJJEKGDAHD\",\"isEnabled\":false,\"qrBlobId\":\"8e35391e19e3229518efb0797a92d874e11d234b\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:01',1,5,0,21,1,'172.20.0.1'),(1308,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"HHNSQQ7Y6UL6HTQX\",\"isEnabled\":false,\"qrBlobId\":\"b86b03759cbf1da9a4f7d4fd850655786f9b28a4\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:01',1,5,0,21,1,'172.20.0.1'),(1309,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"JGXLSW4D6A3BPCHC\",\"isEnabled\":false,\"qrBlobId\":\"a7ac6e62c95fa3fd2ac6186822a448954036a730\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:02',1,5,0,21,1,'172.20.0.1'),(1310,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3IHYZQWJFJJSNCIY\",\"isEnabled\":false,\"qrBlobId\":\"0a6bae4019c89c15fde7302ca0b704c417390656\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:02',1,5,0,21,1,'172.20.0.1'),(1311,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GWVFGJO7XWOBIJ43\",\"isEnabled\":false,\"qrBlobId\":\"be65465840a023f5b5f19c5b59ffb6fcaca27170\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:03',1,5,0,21,1,'172.20.0.1'),(1312,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"AAMC7Y4TQOLDFGSF\",\"isEnabled\":false,\"qrBlobId\":\"ca4078e6ccb1632141ba9163b96221ba52bdbccd\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:04',1,5,0,21,1,'172.20.0.1'),(1313,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FLZ7X6BJTTZSGIEZ\",\"isEnabled\":false,\"qrBlobId\":\"fdfe2f9d183a9ce79ac5c59ebc274e195d9e2f78\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:04',1,5,0,21,1,'172.20.0.1'),(1314,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"LDEA5ZLHEQSZMEAF\",\"isEnabled\":false,\"qrBlobId\":\"c84ead3e3b7b82b3a24ee71c5ec70d2a86314201\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:05',1,5,0,21,1,'172.20.0.1'),(1315,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"IM73LT2RAQO43LMR\",\"isEnabled\":false,\"qrBlobId\":\"0f4c4c95c9dd512058889efee8c3bc74a13297f5\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:06',1,5,0,21,1,'172.20.0.1'),(1316,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"X437FCS44GSUWWEI\",\"isEnabled\":false,\"qrBlobId\":\"b6c28511313175fca5fa5ed506d7a8f8b865fbc6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:06',1,5,0,21,1,'172.20.0.1'),(1317,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VLIAIW5EEBE64LYW\",\"isEnabled\":false,\"qrBlobId\":\"68c1e3da905294db98f9ce36ac0034ebaec26bb9\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:07',1,5,0,21,1,'172.20.0.1'),(1318,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VSWCNO6TU4VUJYPR\",\"isEnabled\":false,\"qrBlobId\":\"aa107e4fb1c5b61680e7054fbe675e6fb2254aeb\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:08',1,5,0,21,1,'172.20.0.1'),(1319,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GEBFVQMVNIZXRWCZ\",\"isEnabled\":false,\"qrBlobId\":\"a31230a0961860caa4a5270e9c66eabd46bc29c8\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:08',1,5,0,21,1,'172.20.0.1'),(1320,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"HT5YF4332F5R7ETY\",\"isEnabled\":false,\"qrBlobId\":\"4b48d0aaa8f2d5ef1ee8f665f077ca7901120c31\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:09',1,5,0,21,1,'172.20.0.1'),(1321,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"6CUYRCG2KSNMKKQ5\",\"isEnabled\":false,\"qrBlobId\":\"1c7e29ff659df8fa215de64e6abdfc4938050092\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:10',1,5,0,21,1,'172.20.0.1'),(1322,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"JS4S7PARZVFKXB2U\",\"isEnabled\":false,\"qrBlobId\":\"1c73d06fee581edfaa00cf22434928fba4cc5fbc\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:10',1,5,0,21,1,'172.20.0.1'),(1323,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GS5UP4N24ZFRCAVN\",\"isEnabled\":false,\"qrBlobId\":\"5af613d92717fdc59a5eedcfcda1404d240e73d6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:11',1,5,0,21,1,'172.20.0.1'),(1324,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"JW5ZOSII37CR3XVS\",\"isEnabled\":false,\"qrBlobId\":\"b03231fd8dc56119ada033d5b341437326ea5dca\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:12',1,5,0,21,1,'172.20.0.1'),(1325,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RXUC6OOFACPBFBJB\",\"isEnabled\":false,\"qrBlobId\":\"a8aec0389643f10b71ecd24ed43de053e4277818\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:13',1,5,0,21,1,'172.20.0.1'),(1326,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"TPNUXAOXYEQ57UP3\",\"isEnabled\":false,\"qrBlobId\":\"8da45db93b150e4369ca006ac93d5759b6585586\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:14',1,5,0,21,1,'172.20.0.1'),(1327,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"B66DI2IT5YZ36I7M\",\"isEnabled\":false,\"qrBlobId\":\"8aacdc6e0aae37553602251d7ad2f89e16eb9ef5\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:14',1,5,0,21,1,'172.20.0.1'),(1328,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"R7GDMLUFU5BLVGCI\",\"isEnabled\":false,\"qrBlobId\":\"9d01d781302d67965b5afa1ba9908207bcff6e74\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:15',1,5,0,21,1,'172.20.0.1'),(1329,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"2BXHEX6VH5OYRZRV\",\"isEnabled\":false,\"qrBlobId\":\"dc667c11f96f2610fb28897396cc3cb7fce29635\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:16',1,5,0,21,1,'172.20.0.1'),(1330,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GEJ4QXHJZNSGSV33\",\"isEnabled\":false,\"qrBlobId\":\"538f8a0960d12e85c9c0038faec7fba1bd33f7a8\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:16',1,5,0,21,1,'172.20.0.1'),(1331,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"UCHRO5EAYYBJ6TNA\",\"isEnabled\":false,\"qrBlobId\":\"641ef6b9a88bd12dbea14274a491390564dd6727\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:17',1,5,0,21,1,'172.20.0.1'),(1332,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"TDAQVFL7XBFCTOVP\",\"isEnabled\":false,\"qrBlobId\":\"dad3d06a2b507657fbd43da5f503252fe2ec67b2\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:18',1,5,0,21,1,'172.20.0.1'),(1333,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VQOP6HGLFKFOWBT3\",\"isEnabled\":false,\"qrBlobId\":\"6e78fa0d1b1313f5f6a6a47857a5f6e7b7a5fda6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:19',1,5,0,21,1,'172.20.0.1'),(1334,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FJFTEQRMVBVEVM4D\",\"isEnabled\":false,\"qrBlobId\":\"0f27611f5fc457ea301779f3170434f43f744d64\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:20',1,5,0,21,1,'172.20.0.1'),(1335,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"CZQHHJHSICNEY2P4\",\"isEnabled\":false,\"qrBlobId\":\"2fbcf43653d5b33351a58b79708cc5498dca8775\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:20',1,5,0,21,1,'172.20.0.1'),(1336,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"CI67TMD4ICDVCZU7\",\"isEnabled\":false,\"qrBlobId\":\"f2c89197507119a989a3573843c31ca82b4ca75c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:21',1,5,0,21,1,'172.20.0.1'),(1337,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"E7R5JQ5NR6AMVMVD\",\"isEnabled\":false,\"qrBlobId\":\"c9a1d10d1472e69d59809dadb383d9630f76d8b1\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:22',1,5,0,21,1,'172.20.0.1'),(1338,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"U2DJ7XGDKWPBI6I6\",\"isEnabled\":false,\"qrBlobId\":\"5438166c417f3d1c42f1926580b2d2ed88543f1a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:23',1,5,0,21,1,'172.20.0.1'),(1339,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"PQHRB2EGPKRFUYF6\",\"isEnabled\":false,\"qrBlobId\":\"ae26436619d3c68bcb0336e4305a60a75a7621f0\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:23',1,5,0,21,1,'172.20.0.1'),(1340,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GBI7WAZO2V5Y4DKB\",\"isEnabled\":false,\"qrBlobId\":\"a7dc9f64a379a2517e111677e072ec2f4fefbd0c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:24',1,5,0,21,1,'172.20.0.1'),(1341,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DFRD66FA7E2T2ISO\",\"isEnabled\":false,\"qrBlobId\":\"55d723be2d107d6517f73a55e9c5c78dab4b4d07\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:25',1,5,0,21,1,'172.20.0.1'),(1342,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"OC4B5LEMCATMMWGM\",\"isEnabled\":false,\"qrBlobId\":\"dd43aad814da418d2646fa8422c07c0726dd1f51\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:26',1,5,0,21,1,'172.20.0.1'),(1343,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"2GEQCAR5UF43SCJ3\",\"isEnabled\":false,\"qrBlobId\":\"d68e270502a215d49c7c54026b4835a7e2808d5a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:27',1,5,0,21,1,'172.20.0.1'),(1344,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3O23F2SVTN6MAJG3\",\"isEnabled\":false,\"qrBlobId\":\"f863ea2c1366ce0d8697a74f741c2a794980752a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:27',1,5,0,21,1,'172.20.0.1'),(1345,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VBXVSPTYGYTQNLOW\",\"isEnabled\":false,\"qrBlobId\":\"38c6142d2e31d53904d997879414c7e2d03d8235\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:28',1,5,0,21,1,'172.20.0.1'),(1346,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"KWP2BBORD54OA254\",\"isEnabled\":false,\"qrBlobId\":\"d53b9b9dc8aca6f5b3e4ea71c918c7e5387af492\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:29',1,5,0,21,1,'172.20.0.1'),(1347,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"CJ4LHPM7S7MXWKEG\",\"isEnabled\":false,\"qrBlobId\":\"f3fc0f0176669de709aae5aacc85c4e823b5f7e0\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:30',1,5,0,21,1,'172.20.0.1'),(1348,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FQO6B5KLYHRBGKOT\",\"isEnabled\":false,\"qrBlobId\":\"d357b6f3155759c1839303cbb5c6cfec1dfba1d6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:31',1,5,0,21,1,'172.20.0.1'),(1349,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"5LOZUC3OXGJW276Y\",\"isEnabled\":false,\"qrBlobId\":\"9f164d3bfdee631e126ee14bd670fee7068d453e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:31',1,5,0,21,1,'172.20.0.1'),(1350,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RBENL247NYAENVL7\",\"isEnabled\":false,\"qrBlobId\":\"ee25ccdfb93330c9a492cab5ef9d2ce72b519a9e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:32',1,5,0,21,1,'172.20.0.1'),(1351,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"IIBVECJ2UEYXSPR6\",\"isEnabled\":false,\"qrBlobId\":\"36d10ede5c922ae3838191f290fd30ae523b6d7b\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:33',1,5,0,21,1,'172.20.0.1'),(1352,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3TPGFTKNPT6CQOP7\",\"isEnabled\":false,\"qrBlobId\":\"b6ee63424fc65d868e37ce8226569388721d3087\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:34',1,5,0,21,1,'172.20.0.1'),(1353,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BKPLANUM4OUECMRD\",\"isEnabled\":false,\"qrBlobId\":\"30e4784b11a7483fa1581080901101b6b808fadc\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:34',1,5,0,21,1,'172.20.0.1'),(1354,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"CG2A4OMTOT3YV4GW\",\"isEnabled\":false,\"qrBlobId\":\"ad462be1b7ed58a6a322c107b65064b28094af93\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:35',1,5,0,21,1,'172.20.0.1'),(1355,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"YXX7Q5GCJRNSBBHC\",\"isEnabled\":false,\"qrBlobId\":\"1b813606cb9cd16f7eb48972e5cc292ac9b6cfcb\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:36',1,5,0,21,1,'172.20.0.1'),(1356,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"4DRD2QZGOTYXLDNR\",\"isEnabled\":false,\"qrBlobId\":\"2315824bb79ae2fc68f1213cadaa917617f52163\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:37',1,5,0,21,1,'172.20.0.1'),(1357,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"EKKWDKIBDC3JBZOO\",\"isEnabled\":false,\"qrBlobId\":\"59dd29e5e9e14749f027b39352756f94ce11b11e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:37',1,5,0,21,1,'172.20.0.1'),(1358,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"2CMTWUV52G7QYRHE\",\"isEnabled\":false,\"qrBlobId\":\"df08b1aff03adf22f60679269dedabcbd65bb703\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:38',1,5,0,21,1,'172.20.0.1'),(1359,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"UKSHDOL5NGRRAX4E\",\"isEnabled\":false,\"qrBlobId\":\"5faa5094b2a2520248eb1791b04c758b56c1d738\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:39',1,5,0,21,1,'172.20.0.1'),(1360,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FJXQDNKUUZNQ7LDK\",\"isEnabled\":false,\"qrBlobId\":\"ce3396d595fa127d4ac528ff296e6b855675612f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:39',1,5,0,21,1,'172.20.0.1'),(1361,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"IMHHXEELC7TNKLOO\",\"isEnabled\":false,\"qrBlobId\":\"eaab67012c43f391f20196285060c16d88f974c7\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:40',1,5,0,21,1,'172.20.0.1'),(1362,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"C6TB7PPI67UDBBBO\",\"isEnabled\":false,\"qrBlobId\":\"38e27e23690e6b62b096828defb205712c6df536\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:40',1,5,0,21,1,'172.20.0.1'),(1363,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"J4JXJ52GUNLQPAID\",\"isEnabled\":false,\"qrBlobId\":\"caa812229e41c93ee39f308ca67cca99ee519641\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:41',1,5,0,21,1,'172.20.0.1'),(1364,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"4BHJCJSTSSVCTHDB\",\"isEnabled\":false,\"qrBlobId\":\"12a76049a412e501bf32dcd2bec96b217ddfa5bf\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:41',1,5,0,21,1,'172.20.0.1'),(1365,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"IBJNZK3ZIMBXLBOZ\",\"isEnabled\":false,\"qrBlobId\":\"508d66fadcba65323c3a565947c6ee24610e3eb8\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:42',1,5,0,21,1,'172.20.0.1'),(1366,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"Y3GWQPSHXFZD3ICE\",\"isEnabled\":false,\"qrBlobId\":\"0b8da651afa7d37e7715926c5d5a7361f41cfb51\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:42',1,5,0,21,1,'172.20.0.1'),(1367,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BWIDDZU2URNE2CRR\",\"isEnabled\":false,\"qrBlobId\":\"1bfd9ec7a868f556ffe765c3adb6270bd437730d\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:43',1,5,0,21,1,'172.20.0.1'),(1368,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RAZD5Z6JMLEONVMT\",\"isEnabled\":false,\"qrBlobId\":\"a2a436a19b8c5ffa1d2a30f76285f913ae1356ea\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:43',1,5,0,21,1,'172.20.0.1'),(1369,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DZY7ZZI5ZGSB7W7O\",\"isEnabled\":false,\"qrBlobId\":\"03cab888557a48e4b283181786165b3a0053f914\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:44',1,5,0,21,1,'172.20.0.1'),(1370,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"Y3AK7U4DW327PZDQ\",\"isEnabled\":false,\"qrBlobId\":\"4446d6fcad263fa366bc0799cfab328c46c71814\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:44',1,5,0,21,1,'172.20.0.1'),(1371,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"OQL5LQNRBNLOKKVC\",\"isEnabled\":false,\"qrBlobId\":\"404ad590f48c45846daca91db2d0dd5c69a79499\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:45',1,5,0,21,1,'172.20.0.1'),(1372,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"P6GIU7RH2DBNDYWH\",\"isEnabled\":false,\"qrBlobId\":\"114ec8b70b696d55dccaab0f241f322e1fadf58b\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:45',1,5,0,21,1,'172.20.0.1'),(1373,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BA4AFOS2UP7GRE3H\",\"isEnabled\":false,\"qrBlobId\":\"d989a4c12ac17823753a409e2e4373ceff9b94ec\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:46',1,5,0,21,1,'172.20.0.1'),(1374,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"LBOVKVS6KFBGD77Y\",\"isEnabled\":false,\"qrBlobId\":\"2bded0f86998682df7788b61b2c8000d25427ee5\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:46',1,5,0,21,1,'172.20.0.1'),(1375,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BF37BBQ5YSFQ4G5T\",\"isEnabled\":false,\"qrBlobId\":\"ed8aa2fed7e633093c9b4a076f470ee29e9f9ce5\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:47',1,5,0,21,1,'172.20.0.1'),(1376,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"GK4JBQVJ3A5HT5OU\",\"isEnabled\":false,\"qrBlobId\":\"11a1ddd5685423d18be53bb507af1c3e276ac281\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:47',1,5,0,21,1,'172.20.0.1'),(1377,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"62BKNIP3VUE7TWU4\",\"isEnabled\":false,\"qrBlobId\":\"e7bf689c0745e9fe44931bb80bcc361f2a6f0434\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:48',1,5,0,21,1,'172.20.0.1'),(1378,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"5RUEDMKXIIBFRGDX\",\"isEnabled\":false,\"qrBlobId\":\"ed7e9ce3d21be7a56f3a49b70fefb8bc2c2f07e8\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:49',1,5,0,21,1,'172.20.0.1'),(1379,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"MADE2LCJTLQZB2GO\",\"isEnabled\":false,\"qrBlobId\":\"81baee6513a3967cca0dcff6b89b80b1650cce6c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:50',1,5,0,21,1,'172.20.0.1'),(1380,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"WFW6YJKP7EQK626X\",\"isEnabled\":false,\"qrBlobId\":\"49dd01dddfcfed72f39a83d2d84dcfa60f9ef1de\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:51',1,5,0,21,1,'172.20.0.1'),(1381,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3G3I5S5CN6RCKQ2I\",\"isEnabled\":false,\"qrBlobId\":\"a16696d4687e9987a91652561f71ab7e23bf6886\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:51',1,5,0,21,1,'172.20.0.1'),(1382,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VP2QC5R7SN4PVG4G\",\"isEnabled\":false,\"qrBlobId\":\"e1f7695ce02e69de1e01507390ec463edae0805a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:52',1,5,0,21,1,'172.20.0.1'),(1383,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RNQ2TS7DEGV4E6U4\",\"isEnabled\":false,\"qrBlobId\":\"eb73dcdeca9f9ab33b22b300d4a6d5b0f5f8c2f3\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:53',1,5,0,21,1,'172.20.0.1'),(1384,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BF3ADTKIIDPZW5YF\",\"isEnabled\":false,\"qrBlobId\":\"760753af45b694a22b18027d9bfcdd4e3f3041f0\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:54',1,5,0,21,1,'172.20.0.1'),(1385,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"UU7PVKB6BWEK6YF7\",\"isEnabled\":false,\"qrBlobId\":\"34bcdecd00937e515a1039e4b717e6f1e07dfa09\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:54',1,5,0,21,1,'172.20.0.1'),(1386,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"6SSNYLVAW4FTZEFV\",\"isEnabled\":false,\"qrBlobId\":\"5ff2584fe55dfeb4c3daa58a0f39839b7f7f13f2\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:55',1,5,0,21,1,'172.20.0.1'),(1387,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BUS4YTAWQ3JICHO2\",\"isEnabled\":false,\"qrBlobId\":\"6bea02feba28475fc0fd6b4d15d6202e4a8b544e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:56',1,5,0,21,1,'172.20.0.1'),(1388,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"CRVDH3V2BX3PBHIZ\",\"isEnabled\":false,\"qrBlobId\":\"c6fe0a64074891835b6b867aeac23b704f82b8cd\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:56',1,5,0,21,1,'172.20.0.1'),(1389,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RS7YSAPNJACGFGHO\",\"isEnabled\":false,\"qrBlobId\":\"16d6dd795e42454b540a025ac1d6e27a919da6ba\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:57',1,5,0,21,1,'172.20.0.1'),(1390,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BVZAJ2FYO4RMTNIW\",\"isEnabled\":false,\"qrBlobId\":\"e573b2be3038b3d3d5aea417e7e6aeea20e83934\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:57',1,5,0,21,1,'172.20.0.1'),(1391,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"U4NIGUSLCKFW6MOS\",\"isEnabled\":false,\"qrBlobId\":\"0bc4c72dde079959bf0ecbe4e113adccb432819e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:58',1,5,0,21,1,'172.20.0.1'),(1392,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"X3VR2ORM4DSKFKIB\",\"isEnabled\":false,\"qrBlobId\":\"4dc9b742f8fe3e4b6f085e409793815e527eae08\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:59',1,5,0,21,1,'172.20.0.1'),(1393,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"P5LWWY3OVBNAPN6G\",\"isEnabled\":false,\"qrBlobId\":\"2e28529eb5a903838623e506b29369fd2b983edf\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:51:59',1,5,0,21,1,'172.20.0.1'),(1394,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DJCFW6XXYRRAUKUR\",\"isEnabled\":false,\"qrBlobId\":\"7329543c8855bfb4c6ed894cf7655d6af037ca1e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:00',1,5,0,21,1,'172.20.0.1'),(1395,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"MYH55TLD4UZR7V6E\",\"isEnabled\":false,\"qrBlobId\":\"64b9d18b23842909ef2c363d5f3542d72af767e6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:00',1,5,0,21,1,'172.20.0.1'),(1396,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"ADTRCM65BBTRD5X4\",\"isEnabled\":false,\"qrBlobId\":\"dd6d40365223ca67afdb1d11c45e05fc843d1377\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:01',1,5,0,21,1,'172.20.0.1'),(1397,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"32MAVPGRL4LXUO5R\",\"isEnabled\":false,\"qrBlobId\":\"ebf0061573488bec820e0843fdf60a7133fc4c10\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:02',1,5,0,21,1,'172.20.0.1'),(1398,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FEEGTABKPNA5G6RX\",\"isEnabled\":false,\"qrBlobId\":\"00203fc8f83fcf8eea1b30d75bb9daaeff28fb7e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:02',1,5,0,21,1,'172.20.0.1'),(1399,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DL7BRQFLS46YIKBN\",\"isEnabled\":false,\"qrBlobId\":\"49a8ac1ae98c39af6b7181bb68db6c531eb07fad\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:03',1,5,0,21,1,'172.20.0.1'),(1400,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"C7OFQL6THCFQF7YY\",\"isEnabled\":false,\"qrBlobId\":\"fe3219cf48aa02226f743f59e3432afa219b4a5f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:04',1,5,0,21,1,'172.20.0.1'),(1401,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"QPQAXHDDDFLLANSZ\",\"isEnabled\":false,\"qrBlobId\":\"2510eabd840da593d02a017315607a29028c58e5\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:04',1,5,0,21,1,'172.20.0.1'),(1402,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"AIYMLKANQBU7WPMQ\",\"isEnabled\":false,\"qrBlobId\":\"292f1f4e8117af9ab850082b9e8703e117350179\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:05',1,5,0,21,1,'172.20.0.1'),(1403,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"XNHJLTWVZFVEVA6E\",\"isEnabled\":false,\"qrBlobId\":\"52ba02273a2435d3c0b85972b963400659a564b6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:06',1,5,0,21,1,'172.20.0.1'),(1404,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"7DLBSHYKOOWY6CKX\",\"isEnabled\":false,\"qrBlobId\":\"61dc5ae5146d9f56ffd76aadf220ec56cc4a4865\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:06',1,5,0,21,1,'172.20.0.1'),(1405,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"Z7WVFC53EHKH22YY\",\"isEnabled\":false,\"qrBlobId\":\"f59840235ee14b9f419695430995648350e51bea\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:07',1,5,0,21,1,'172.20.0.1'),(1406,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"HKO2UWTFGAAPL3H7\",\"isEnabled\":false,\"qrBlobId\":\"832321f811affbd5f7e7ea4a283fde11d1621c3c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:07',1,5,0,21,1,'172.20.0.1'),(1407,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"W7DFOVWA2HYZTD5W\",\"isEnabled\":false,\"qrBlobId\":\"fe96b3f5cf453e820d5d84bb26b0e8f94e65780a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:09',1,5,0,21,1,'172.20.0.1'),(1408,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3IVFUTFJ74TW6EHJ\",\"isEnabled\":false,\"qrBlobId\":\"af5b9f02b5cc5cc20cdbc99d6ee4432e609529ed\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:10',1,5,0,21,1,'172.20.0.1'),(1409,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SKG4TRXBNREYN2BU\",\"isEnabled\":false,\"qrBlobId\":\"5467db52a11cb34489e0cddb1c126e0c7bb747f7\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:12',1,5,0,21,1,'172.20.0.1'),(1410,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"EKM4NLX4J2JHGW24\",\"isEnabled\":false,\"qrBlobId\":\"26b9264f7672080cb887d567f9aa62bd1df4b07c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:52:16',1,5,0,21,1,'172.20.0.1'),(1411,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"NALMNNLE7N5AIVXN\",\"isEnabled\":false,\"qrBlobId\":\"0afbb99134e6e3c513ae4f490c7ad82e4a5ba440\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:53:06',1,5,0,21,1,'172.20.0.1'),(1412,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"BRITF72FRTQ24Z43\",\"isEnabled\":false,\"qrBlobId\":\"2c493a6959c38973b2e59c34fd7c21f11c6ded4c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:53:16',1,5,0,21,1,'172.20.0.1'),(1413,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"33QZIGC6DHXRXIM3\",\"isEnabled\":false,\"qrBlobId\":\"5448b4d2182924b5c27cae4a6ceb88f5e0201a73\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:57:43',1,5,0,21,1,'172.20.0.1'),(1414,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"MC4J2Z5SOQQV2DTR\",\"isEnabled\":false,\"qrBlobId\":\"03836b63f03e3d3dd7553f9c1535bed0aca1a528\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:58:10',1,5,0,21,1,'172.20.0.1'),(1415,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"EY6W6H2SOJYVWIHX\",\"isEnabled\":false,\"qrBlobId\":\"d0ada53c043ff565538751eddb2874d71f34751f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 07:59:17',1,5,0,21,1,'172.20.0.1'),(1416,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SK2TQRVCP2ADNHZI\",\"isEnabled\":false,\"qrBlobId\":\"5d1f5f42e8cd7e886d286a997bb463b2c0270f12\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:00',1,5,0,21,1,'172.20.0.1'),(1417,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DOVTET37LTXMMSMO\",\"isEnabled\":false,\"qrBlobId\":\"05d8eb635678ff4e21654bb243918cc97b05a39e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:47',1,5,0,21,1,'172.20.0.1'),(1418,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"QRKGWABKRCWFU5UA\",\"isEnabled\":false,\"qrBlobId\":\"5bf6497c29db1e0b17e2b70fbc468fc36dbb5b88\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:48',1,5,0,21,1,'172.20.0.1'),(1419,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"JBMLJJ7INUZFVOA5\",\"isEnabled\":false,\"qrBlobId\":\"fa4e682556d580b10d8b5143e3e83284473bbbab\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:49',1,5,0,21,1,'172.20.0.1'),(1420,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"2MGQVBZ7GFX2BUVH\",\"isEnabled\":false,\"qrBlobId\":\"07be838729a63fb8856491df21da17db8568abf6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:49',1,5,0,21,1,'172.20.0.1'),(1421,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"AUOVXGBGBTG5UT7W\",\"isEnabled\":false,\"qrBlobId\":\"5176f85ea2f7b7f004d49704bfc4baa257051503\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:50',1,5,0,21,1,'172.20.0.1'),(1422,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"Q53Z6D4JKQVCRDS5\",\"isEnabled\":false,\"qrBlobId\":\"c1b727b4aa8705fa2703eecaad04adcddee23b34\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:51',1,5,0,21,1,'172.20.0.1'),(1423,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"OKOUOOJOM5IHOVUC\",\"isEnabled\":false,\"qrBlobId\":\"4e554c23f320a4b72fd14dd9e9d0f245eeaef64d\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:51',1,5,0,21,1,'172.20.0.1'),(1424,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"PN3RJBDPVG6Q6C6U\",\"isEnabled\":false,\"qrBlobId\":\"fd3a1b489e135ab0702657702ae929af1dac5e2f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:52',1,5,0,21,1,'172.20.0.1'),(1425,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"OY6XSVR2YDAEDRJT\",\"isEnabled\":false,\"qrBlobId\":\"f0acbef925bafdcef71153804e52485ae62ae718\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:52',1,5,0,21,1,'172.20.0.1'),(1426,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"B35MTQV3WY4YQQKL\",\"isEnabled\":false,\"qrBlobId\":\"fa6828e41dae720125a842508edbad7be7cae5b7\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:53',1,5,0,21,1,'172.20.0.1'),(1427,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VFQ5XTLZABFM3J6M\",\"isEnabled\":false,\"qrBlobId\":\"32aa939ffe934beee412a67c5b136e09624dc5c9\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:54',1,5,0,21,1,'172.20.0.1'),(1428,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"IN4YARRGTG7W6OQY\",\"isEnabled\":false,\"qrBlobId\":\"085e806fd5bd769d63f5794b858a207d78b2aede\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:55',1,5,0,21,1,'172.20.0.1'),(1429,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"VFYLSNXT6XEMSPSO\",\"isEnabled\":false,\"qrBlobId\":\"1b9e6e72e77f75302dd6865cd057d005cf61847f\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:55',1,5,0,21,1,'172.20.0.1'),(1430,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"C6ZU2YWE4R3BHIT5\",\"isEnabled\":false,\"qrBlobId\":\"7e1df2f2c26f99a7aa9deabcce6040fc7e695272\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:01:58',1,5,0,21,1,'172.20.0.1'),(1431,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"AQGGZL3HDX4KHJ6C\",\"isEnabled\":false,\"qrBlobId\":\"4f1cc6d9b907f3c3099e67a058898d3a2243962b\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:02:00',1,5,0,21,1,'172.20.0.1'),(1432,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"KPOVBISCXW2RRB5B\",\"isEnabled\":false,\"qrBlobId\":\"f17eca400e2862a58def7265f923e0d0d2241337\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:12:20',1,5,0,21,1,'172.20.0.1'),(1433,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"ZWTRFNC7PP2HQNIF\",\"isEnabled\":false,\"qrBlobId\":\"4c86cb73372907e43a1e1a180a4b5390d098e21c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:14:50',1,5,0,21,1,'172.20.0.1'),(1434,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3PDED7YFZQDHM2W7\",\"isEnabled\":false,\"qrBlobId\":\"662867979c6e5b4e0aa22c55fb29e01f38552130\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:15:26',1,5,0,21,1,'172.20.0.1'),(1435,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"TUU6ALPUFE6SBA7C\",\"isEnabled\":false,\"qrBlobId\":\"24056bd0ce390d7db1816c6c984e59928a1dbf55\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:19:46',1,5,0,21,1,'172.20.0.1'),(1436,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"L4SFUK67ZPVVUU5C\",\"isEnabled\":false,\"qrBlobId\":\"bdab62958d9189704787269b653580ee0d0a9655\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:21:06',1,5,0,21,1,'172.20.0.1'),(1437,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"TNO6J5BWZMF72IBT\",\"isEnabled\":false,\"qrBlobId\":\"f92ca61a498642e3e92ba88afe82fe68cf39e421\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:21:13',1,5,0,21,1,'172.20.0.1'),(1438,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"ATSUQWFVTGVAHUMH\",\"isEnabled\":false,\"qrBlobId\":\"f64e7ee3082f2697658be738b5dff4d890fa8243\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:21:30',1,5,0,21,1,'172.20.0.1'),(1439,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"KEL6M6FKW5ZNJZSW\",\"isEnabled\":false,\"qrBlobId\":\"253d7e75bfc0a57199dc4eae54bda5ff07a32e13\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:21:34',1,5,0,21,1,'172.20.0.1'),(1440,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"FK4GW6U6YPI3KQA7\",\"isEnabled\":false,\"qrBlobId\":\"90a551cdba36a385562d0ff6df502fc41c99a7cb\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:26:58',1,5,0,21,1,'172.20.0.1'),(1441,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"63EBG4K3Q4VT2XUV\",\"isEnabled\":false,\"qrBlobId\":\"149776f3a5112f83aedbf7bc102fbe324bf478c2\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:30:43',1,5,0,21,1,'172.20.0.1'),(1442,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"L6E3PRCIJONKJORF\",\"isEnabled\":false,\"qrBlobId\":\"60740176a5f4b9e2f96596f7aa6c5ed6002a7e1d\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:31:55',1,5,0,21,1,'172.20.0.1'),(1443,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"NP4KCGYXOIVDBVFD\",\"isEnabled\":false,\"qrBlobId\":\"a6c05ab2d6b3ad113d25aafd205a2bbb05b2f5e9\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:34:01',1,5,0,21,1,'172.20.0.1'),(1444,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"KSWDRJFS5BHBUAMP\",\"isEnabled\":false,\"qrBlobId\":\"3961a20ec39dcef094a379afb8850ebea5a1c0ca\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:34:24',1,5,0,21,1,'172.20.0.1'),(1445,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SBBFE5GNTZ4UEOZU\",\"isEnabled\":false,\"qrBlobId\":\"049e41a8bb10cf034002e0eb82689f4194be17a3\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:34:51',1,5,0,21,1,'172.20.0.1'),(1446,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SBBFE5GNTZ4UEOZU\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":1,\"createdAt\":\"2021-07-23T08:35:19+00:00\"},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:35:20',1,5,0,21,1,'172.20.0.1'),(1447,2,'System Administrator','{\"googleauthenticator\":[null,{\"secret\":\"SBBFE5GNTZ4UEOZU\",\"isEnabled\":true,\"qrBlobId\":null,\"userId\":1,\"createdAt\":\"2021-07-23T08:35:19+00:00\"}],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:37:31',1,5,0,21,1,'172.20.0.1'),(1448,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SE4CKTWBSPVHOLPS\",\"isEnabled\":false,\"qrBlobId\":\"ca232d48a2a24bd1490a915ac2375a706bb42e3a\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-23 08:37:38',1,5,0,21,1,'172.20.0.1'),(1449,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.72\",\"6.5.70\"]}','2021-07-29 08:36:37',1,153,0,67,1,'172.20.0.1'),(1450,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.72\",\"6.5.70\"]}','2021-07-29 08:36:38',1,153,0,67,3,'172.20.0.1'),(1451,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.72\",\"6.5.70\"]}','2021-07-29 08:36:39',1,153,0,67,4,'172.20.0.1'),(1452,2,'System Administrator','{\"lastLogin\":[\"2021-07-29T08:36:46+00:00\",\"2021-07-22T14:57:16+00:00\"],\"loginCount\":[47,46],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:36:46',1,5,0,21,1,'172.20.0.1'),(1453,4,'admin [172.20.0.1]',NULL,'2021-07-29 08:36:46',1,5,0,21,1,'172.20.0.1'),(1454,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"DEZ4G6YPNCFHWQ4X\",\"isEnabled\":false,\"qrBlobId\":\"9d7267a87a3f6456094944805b45d2dd64c91852\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:36:48',1,5,0,21,1,'172.20.0.1'),(1455,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"S74CAH7NU3DLFEG3\",\"isEnabled\":false,\"qrBlobId\":\"44c7c237b4c6c0fc7b75e3aad6490e12a2cd5484\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:37:06',1,5,0,21,1,'172.20.0.1'),(1456,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"M7KOXCJUC7PZAY5F\",\"isEnabled\":false,\"qrBlobId\":\"dd597dada831e1377e34a1e0c9195c1a16db00f3\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:37:58',1,5,0,21,1,'172.20.0.1'),(1457,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"SU2NWSZINWMPPFIS\",\"isEnabled\":false,\"qrBlobId\":\"87d19b50f541d53e8f0996860d4b0cea8adb081c\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:38:04',1,5,0,21,1,'172.20.0.1'),(1458,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"RQLQEC5NAYFX5F2B\",\"isEnabled\":false,\"qrBlobId\":\"025a5ed74cd632a03933367e1a2f6dcb62cc7fd6\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:38:53',1,5,0,21,1,'172.20.0.1'),(1459,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"G4SZ3SIRNMVDWIV4\",\"isEnabled\":false,\"qrBlobId\":\"22a96430a8c7618cb609e0f60a561c1e4438ea2d\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:39:11',1,5,0,21,1,'172.20.0.1'),(1460,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"QKRGHIWT2WAQFPJO\",\"isEnabled\":false,\"qrBlobId\":\"6bda2678fe2c234a7e58ffd14507dee9b011072e\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:39:37',1,5,0,21,1,'172.20.0.1'),(1461,2,'System Administrator','{\"googleauthenticator\":[{\"secret\":\"3EAOKMLSMZH2ZIK5\",\"isEnabled\":false,\"qrBlobId\":\"d3c91a2a8066dff0512164fcb4527f525997c904\",\"userId\":1,\"createdAt\":null},null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 08:40:12',1,5,0,21,1,'172.20.0.1'),(1462,1,'Per Conto di:','{\"default\":0,\"account_id\":1,\"id\":2,\"name\":\"Per Conto di:\",\"email\":\"admin@intermesh.localhost\",\"signature\":\"\"}','2021-07-29 08:41:25',1,130,0,64,2,'172.20.0.1'),(1463,2,'Per Conto di: pipo@pipoos.nl','{\"name\":[\"Per Conto di:\",\"Per Conto di: pipo@pipoos.nl\"]}','2021-07-29 08:42:49',1,130,0,64,2,'172.20.0.1'),(1464,2,'pipo@pipoos.nl','{\"name\":[\"Per Conto di: pipo@pipoos.nl\",\"pipo@pipoos.nl\"]}','2021-07-29 08:45:49',1,130,0,64,2,'172.20.0.1'),(1465,2,'pipo@pipoos.nl <script>alert(\'hoi\');</script>','{\"name\":[\"pipo@pipoos.nl\",\"pipo@pipoos.nl <script>alert(\'hoi\');<\\/script>\"]}','2021-07-29 08:57:14',1,130,0,64,2,'172.20.0.1'),(1466,1,'For piet1','{\"id\":20,\"fieldSetId\":2,\"name\":\"For piet1\",\"databaseName\":\"For_piet1\",\"options\":\"{\\\"maxLength\\\":50}\",\"forceAlterTable\":true}','2021-07-29 12:05:28',1,152,0,9,20,'172.20.0.1'),(1467,2,'System Administrator','{\"lastLogin\":[\"2021-07-29T12:13:33+00:00\",\"2021-07-29T08:36:46+00:00\"],\"loginCount\":[48,47],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-07-29 12:13:33',1,5,0,21,1,'172.20.0.1'),(1468,4,'admin [172.20.0.1]',NULL,'2021-07-29 12:13:33',1,5,0,21,1,'172.20.0.1'),(1469,2,'System Administrator','{\"lastLogin\":[\"2021-08-23T10:14:36+00:00\",\"2021-07-29T12:13:33+00:00\"],\"loginCount\":[49,48],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-23 10:14:36',1,5,0,21,1,'172.20.0.1'),(1470,4,'admin [172.20.0.1]',NULL,'2021-08-23 10:14:36',1,5,0,21,1,'172.20.0.1'),(1471,2,'documents','{\"enabled\":[1,true]}','2021-08-23 11:35:51',1,162,1,13,43,'172.20.0.1'),(1472,2,'studio-documents','{\"locked\":[0,false]}','2021-08-23 11:35:51',1,161,0,68,1,'172.20.0.1'),(1473,2,'System Administrator','{\"lastLogin\":[\"2021-08-23T11:35:57+00:00\",\"2021-08-23T10:14:36+00:00\"],\"loginCount\":[50,49],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-23 11:35:57',1,5,0,21,1,'172.20.0.1'),(1474,4,'admin [172.20.0.1]',NULL,'2021-08-23 11:35:57',1,5,0,21,1,'172.20.0.1'),(1475,1,'go\\modules\\community\\multi_instance\\model\\Instance','{\"id\":5,\"hostname\":\"test1.group-office.com\",\"usersMax\":10,\"storageQuota\":10737418240}','2021-08-23 11:51:28',1,153,0,67,5,'172.20.0.1'),(1476,1,'admin@intermesh.localhost','{\"default\":0,\"account_id\":1,\"id\":3,\"name\":\"admin@intermesh.localhost\",\"email\":\"admin@intermesh.localhost\",\"signature\":\"\"}','2021-08-23 13:58:38',1,130,0,64,3,'172.20.0.1'),(1477,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"storageUsage\":[661819,0]}','2021-08-24 08:48:19',1,153,0,67,1,'172.20.0.1'),(1478,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"storageUsage\":[662114,0]}','2021-08-24 08:48:20',1,153,0,67,3,'172.20.0.1'),(1479,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"storageUsage\":[661606,0]}','2021-08-24 08:48:21',1,153,0,67,4,'172.20.0.1'),(1480,2,'System Administrator','{\"lastLogin\":[\"2021-08-24T08:48:31+00:00\",\"2021-08-23T11:35:57+00:00\"],\"loginCount\":[51,50],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-24 08:48:31',1,5,0,21,1,'172.20.0.1'),(1481,4,'admin [172.20.0.1]',NULL,'2021-08-24 08:48:31',1,5,0,21,1,'172.20.0.1'),(1482,2,'documents','{\"enabled\":[1,true]}','2021-08-24 11:26:17',1,162,1,13,43,'172.20.0.1'),(1483,2,'studio-documents','{\"locked\":[0,false]}','2021-08-24 11:26:17',1,161,0,68,1,'172.20.0.1'),(1484,2,'Client','{\"hiddenInGrid\":[true,false],\"forceAlterTable\":[true,false]}','2021-08-24 11:30:41',1,163,0,9,7,'172.20.0.1'),(1485,1,'Client Month','{\"id\":28,\"fieldSetId\":4,\"name\":\"Client Month\",\"databaseName\":\"Client_Month\",\"type\":\"TemplateField\",\"options\":\"{\\\"template\\\":\\\"[assign contact = entity.customFields.Client | entity:Contact]\\\\n{{contact.customFields.Month}}\\\"}\",\"forceAlterTable\":true}','2021-08-24 12:16:46',1,163,0,9,28,'172.20.0.1'),(1486,2,'Client Month','{\"options\":[\"{\\\"template\\\":\\\"[assign contact = entity.customFields.Client | entity:Contact]\\\\n{{contact.customFields.asText.Month}}\\\"}\",\"{\\\"template\\\":\\\"[assign contact = entity.customFields.Client | entity:Contact]\\\\n{{contact.customFields.Month}}\\\"}\"],\"forceAlterTable\":[true,false]}','2021-08-24 12:29:03',1,163,0,9,28,'172.20.0.1'),(1487,1,'test2','{\"id\":55,\"name\":\"test2\",\"package\":\"studio\",\"version\":0,\"sort_order\":121,\"checkDepencencies\":false}','2021-08-24 13:14:28',1,188,1,13,55,'172.20.0.1'),(1488,1,'studio-test2','{\"id\":7,\"name\":\"studio-test2\",\"description\":\"test\",\"moduleId\":55,\"locked\":true,\"package\":\"studio\"}','2021-08-24 13:14:28',1,161,0,68,7,'172.20.0.1'),(1489,2,'test2','{\"enabled\":[1,true]}','2021-08-24 13:15:00',1,188,1,13,55,'172.20.0.1'),(1490,2,'studio-test2','{\"locked\":[0,true]}','2021-08-24 13:15:00',1,161,0,68,7,'172.20.0.1'),(1491,2,'test2','{\"enabled\":[1,true]}','2021-08-24 13:17:43',1,188,1,13,55,'172.20.0.1'),(1492,2,'studio-test2','{\"locked\":[0,false]}','2021-08-24 13:17:43',1,161,0,68,7,'172.20.0.1'),(1493,1,'test3','{\"id\":56,\"name\":\"test3\",\"package\":\"studio\",\"version\":0,\"sort_order\":122,\"checkDepencencies\":false}','2021-08-24 13:18:09',1,189,1,13,56,'172.20.0.1'),(1494,1,'studio-test3','{\"id\":8,\"name\":\"studio-test3\",\"description\":\"test3\",\"moduleId\":56,\"locked\":true,\"package\":\"studio\"}','2021-08-24 13:18:09',1,161,0,68,8,'172.20.0.1'),(1495,2,'test3','{\"enabled\":[1,true]}','2021-08-24 13:18:27',1,189,1,13,56,'172.20.0.1'),(1496,2,'studio-test3','{\"locked\":[0,true]}','2021-08-24 13:18:27',1,161,0,68,8,'172.20.0.1'),(1497,2,'System Administrator','{\"lastLogin\":[\"2021-08-26T07:27:18+00:00\",\"2021-08-24T08:48:31+00:00\"],\"loginCount\":[52,51],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-26 07:27:18',1,5,0,21,1,'172.20.0.1'),(1498,4,'admin [172.20.0.1]',NULL,'2021-08-26 07:27:18',1,5,0,21,1,'172.20.0.1'),(1499,2,'System Administrator','{\"theme\":[\"Compact\",\"Paper\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-26 08:42:10',1,5,0,21,1,'172.20.0.1'),(1500,2,'System Administrator','{\"theme\":[\"Dark\",\"Compact\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-26 09:02:59',1,5,0,21,1,'172.20.0.1'),(1501,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.72\"]}','2021-08-27 11:33:11',1,153,0,67,1,'172.20.0.1'),(1502,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.72\"]}','2021-08-27 11:33:12',1,153,0,67,3,'172.20.0.1'),(1503,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.72\"]}','2021-08-27 11:33:14',1,153,0,67,4,'172.20.0.1'),(1504,2,'System Administrator','{\"lastLogin\":[\"2021-08-31T07:21:17+00:00\",\"2021-08-26T07:27:18+00:00\"],\"loginCount\":[53,52],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 07:21:17',1,5,0,21,1,'172.20.0.1'),(1505,4,'admin [172.20.0.1]',NULL,'2021-08-31 07:21:17',1,5,0,21,1,'172.20.0.1'),(1506,2,'System Administrator','{\"theme\":[\"Paper\",\"Dark\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 07:23:32',1,5,0,21,1,'172.20.0.1'),(1507,2,'Management','{\"name\":[\"Management\",\"Super Template\"]}','2021-08-31 07:24:48',1,180,1,11,11,'172.20.0.1'),(1508,3,'test',NULL,'2021-08-31 07:24:55',1,150,1,23,7,'172.20.0.1'),(1509,3,'test',NULL,'2021-08-31 07:24:55',1,159,1,23,8,'172.20.0.1'),(1510,3,'super',NULL,'2021-08-31 07:24:55',1,185,1,23,9,'172.20.0.1'),(1511,3,'test',NULL,'2021-08-31 07:24:55',1,151,1,35,69,'172.20.0.1'),(1512,3,'test',NULL,'2021-08-31 07:24:55',1,160,1,35,70,'172.20.0.1'),(1513,3,'super',NULL,'2021-08-31 07:24:55',1,186,1,35,71,'172.20.0.1'),(1514,3,'test',NULL,'2021-08-31 07:24:55',1,5,0,21,5,'172.20.0.1'),(1515,3,'test',NULL,'2021-08-31 07:24:55',1,5,0,21,6,'172.20.0.1'),(1516,3,'super',NULL,'2021-08-31 07:24:55',1,5,0,21,8,'172.20.0.1'),(1517,3,'test',NULL,'2021-08-31 07:24:55',1,148,1,11,8,'172.20.0.1'),(1518,3,'test@test.nl',NULL,'2021-08-31 07:24:55',1,156,1,11,9,'172.20.0.1'),(1519,3,'super',NULL,'2021-08-31 07:24:55',1,183,1,11,13,'172.20.0.1'),(1520,3,'test','null','2021-08-31 07:24:56',1,149,1,36,7,'172.20.0.1'),(1521,3,'test','null','2021-08-31 07:24:56',1,149,1,39,34,'172.20.0.1'),(1522,3,'test','null','2021-08-31 07:24:56',1,157,1,51,5,'172.20.0.1'),(1523,3,'test','null','2021-08-31 07:24:56',1,157,1,39,35,'172.20.0.1'),(1524,3,'test (1)','null','2021-08-31 07:24:56',1,158,1,36,8,'172.20.0.1'),(1525,3,'test (1)','null','2021-08-31 07:24:56',1,158,1,39,36,'172.20.0.1'),(1526,3,'super','null','2021-08-31 07:24:56',1,187,1,36,9,'172.20.0.1'),(1527,3,'super','null','2021-08-31 07:24:56',1,187,1,39,37,'172.20.0.1'),(1528,2,'System Administrator','{\"avatarId\":[\"15457cf0c9556e24adbf79d611a57f23c18e31a0\",null],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 07:26:14',1,5,0,21,1,'172.20.0.1'),(1529,2,'System Administrator','{\"avatarId\":[\"c9270970b441cac135735894dacd3bc21a01b0e2\",\"15457cf0c9556e24adbf79d611a57f23c18e31a0\"],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 07:28:37',1,5,0,21,1,'172.20.0.1'),(1530,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.73\"]}','2021-08-31 07:42:44',1,153,0,67,1,'172.20.0.1'),(1531,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.73\"]}','2021-08-31 07:42:45',1,153,0,67,3,'172.20.0.1'),(1532,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.70\",\"6.5.73\"]}','2021-08-31 07:42:46',1,153,0,67,4,'172.20.0.1'),(1533,2,'Management','{\"users\":[[2,4],[]]}','2021-08-31 07:43:02',1,180,1,11,11,'172.20.0.1'),(1534,1,'Finance','{\"id\":14,\"name\":\"Finance\",\"users\":[4],\"setAcl\":[]}','2021-08-31 07:43:12',1,190,1,11,14,'172.20.0.1'),(1535,1,'peter','{\"id\":15,\"name\":\"peter\",\"isUserGroupFor\":9,\"users\":[9]}','2021-08-31 07:43:43',1,191,1,11,15,'172.20.0.1'),(1536,1,'Peter Smith','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Peter Smith\",\"parent_id\":12,\"mtime\":1630395824,\"ctime\":1630395824,\"id\":38}','2021-08-31 07:43:44',1,24,1,39,38,'172.20.0.1'),(1537,2,'Peter Smith','{\"acl_id\":[0,192]}','2021-08-31 07:43:44',1,192,1,39,38,'172.20.0.1'),(1538,1,'Peter Smith','{\"files_folder_id\":38,\"version\":1,\"user_id\":9,\"name\":\"Peter Smith\",\"acl_id\":192,\"id\":6}','2021-08-31 07:43:44',1,192,1,51,6,'172.20.0.1'),(1539,1,'Peter Smith','{\"user_id\":1,\"visible\":0,\"acl_id\":0,\"thumbs\":1,\"muser_id\":1,\"quota_user_id\":1,\"readonly\":1,\"apply_state\":0,\"name\":\"Peter Smith\",\"parent_id\":1,\"mtime\":1630395824,\"ctime\":1630395824,\"id\":39}','2021-08-31 07:43:44',1,24,1,39,39,'172.20.0.1'),(1540,2,'calendar','{\"muser_id\":[8,1]}','2021-08-31 07:43:44',1,24,1,39,1,'172.20.0.1'),(1541,2,'Peter Smith','{\"acl_id\":[0,193]}','2021-08-31 07:43:44',1,193,1,39,39,'172.20.0.1'),(1542,1,'Peter Smith','{\"group_id\":1,\"user_id\":9,\"acl_id\":193,\"start_hour\":0,\"end_hour\":0,\"time_interval\":1800,\"public\":0,\"shared_acl\":0,\"show_bdays\":0,\"show_completed_tasks\":1,\"comment\":\"\",\"project_id\":0,\"tasklist_id\":0,\"files_folder_id\":39,\"show_holidays\":1,\"enable_ics_import\":0,\"ics_import_url\":\"\",\"tooltip\":\"\",\"version\":1,\"name\":\"Peter Smith\",\"id\":10}','2021-08-31 07:43:44',1,193,1,36,10,'172.20.0.1'),(1543,1,'Peter Smith','{\"id\":10,\"name\":\"Peter Smith\",\"salutationTemplate\":\"Geachte [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]heer\\/mevrouw[else][if {{contact.gender}}==\\\"M\\\"]heer[else]mevrouw[\\/if][\\/if][\\/if][if {{contact.middleName}}] {{contact.middleName}}[\\/if] {{contact.lastName}}\"}','2021-08-31 07:43:44',1,194,1,23,10,'172.20.0.1'),(1544,1,'Peter Smith','{\"id\":72,\"name\":\"Peter Smith\"}','2021-08-31 07:43:44',1,195,1,35,72,'172.20.0.1'),(1545,1,'Peter Smith','{\"id\":9,\"username\":\"peter\",\"displayName\":\"Peter Smith\",\"email\":\"peter@intermesh.nl\",\"recoveryEmail\":\"peter@intermesh.nl\",\"currency\":\"\\u20ac\",\"homeDir\":\"users\\/peter\",\"password\":\"$2y$10$eoAH9ueLwHEjoaXI6ShZoeQJgIzEi1IG.jU4pwxyhmULTllyP2pUK\",\"groups\":[3,2,14,15],\"addressBookSettings\":{\"defaultAddressBookId\":10,\"sortBy\":\"name\",\"userId\":9},\"employee\":{\"id\":9,\"businessId\":null,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[],\"activityRates\":[]},\"notesSettings\":{\"defaultNoteBookId\":72,\"userId\":9},\"calendarSettings\":{\"calendar_id\":0,\"background\":\"EBF1E2\",\"reminder\":null,\"show_statuses\":true,\"check_conflict\":true,\"user_id\":9},\"emailSettings\":{\"id\":9,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},\"syncSettings\":{\"user_id\":9,\"account_id\":0,\"noteBooks\":[{\"userId\":9,\"noteBookId\":72,\"isDefault\":true}],\"addressBooks\":[{\"userId\":9,\"addressBookId\":10,\"isDefault\":true}]},\"taskSettings\":{\"reminder_days\":0,\"reminder_time\":\"0\",\"remind\":false,\"default_tasklist_id\":0,\"user_id\":9},\"timeRegistrationSettings\":{\"selectProjectOnTimerStart\":false},\"projectsSettings\":{\"duplicateRecursively\":false,\"duplicateRecursivelyTasks\":false,\"duplicateRecursivelyFiles\":false,\"deleteProjectsRecursively\":false,\"userId\":9}}','2021-08-31 07:43:44',1,5,0,21,9,'172.20.0.1'),(1546,2,'Peter Smith','{\"avatarId\":[\"64d5f732477ccf666a17e2544dc5f2516025433c\",null],\"employee\":[{\"id\":9,\"businessId\":1,\"timeClosedUntil\":null,\"quitAt\":null,\"hourlyRevenue\":0,\"hourlyCosts\":0,\"agreementIds\":[],\"managers\":[{\"subjectId\":9,\"managerId\":1,\"notified\":true}],\"activityRates\":[]},null],\"emailSettings\":[{\"id\":9,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 07:44:24',1,5,0,21,9,'172.20.0.1'),(1547,3,'tmp/1/Test.odt','null','2021-08-31 07:49:07',1,141,0,38,108,'172.20.0.1'),(1548,2,'System Administrator','{\"disk_usage\":[219851,215366],\"modifiedAt\":[\"2021-08-31 07:28:37\",\"2021-08-31 07:49:07\"]}','2021-08-31 07:49:07',1,4,0,21,1,'172.20.0.1'),(1549,1,'tmp/1/powiadomienie.html','{\"locked_user_id\":0,\"status_id\":0,\"ctime\":1630396147,\"mtime\":1630396147,\"muser_id\":1,\"expire_time\":0,\"delete_when_expired\":0,\"version\":1,\"user_id\":1,\"folder_id\":33,\"name\":\"powiadomienie.html\",\"extension\":\"html\",\"size\":2225,\"id\":109}','2021-08-31 07:49:07',1,141,0,38,109,'172.20.0.1'),(1550,2,'System Administrator','{\"disk_usage\":[215366,217591]}','2021-08-31 07:49:07',1,4,0,21,1,'172.20.0.1'),(1551,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.70\"]}','2021-08-31 09:56:58',1,153,0,67,1,'172.20.0.1'),(1552,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.70\"]}','2021-08-31 09:56:59',1,153,0,67,3,'172.20.0.1'),(1553,2,'go\\modules\\community\\multi_instance\\model\\Instance','{\"version\":[\"6.5.73\",\"6.5.70\"]}','2021-08-31 09:57:00',1,153,0,67,4,'172.20.0.1'),(1554,2,'System Administrator','{\"lastLogin\":[\"2021-08-31T10:49:53+00:00\",\"2021-08-31T07:21:17+00:00\"],\"loginCount\":[54,53],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-08-31 10:49:53',1,5,0,21,1,'172.20.0.1'),(1555,4,'admin [172.20.0.1]',NULL,'2021-08-31 10:49:53',1,5,0,21,1,'172.20.0.1'),(1556,2,'Finance','{\"users\":[[4,3],[4,9]]}','2021-08-31 10:50:10',1,190,1,11,14,'172.20.0.1'),(1557,2,'System Administrator','{\"lastLogin\":[\"2021-09-02T07:47:02+00:00\",\"2021-08-31T10:49:53+00:00\"],\"loginCount\":[55,54],\"emailSettings\":[{\"id\":1,\"use_html_markup\":true,\"show_from\":true,\"show_cc\":true,\"show_bcc\":false,\"skip_unknown_recipients\":false,\"always_request_notification\":false,\"always_respond_to_notifications\":false,\"font_size\":\"14px\",\"sort_email_addresses_by_time\":true,\"defaultTemplateId\":null},null]}','2021-09-02 07:47:02',1,5,0,21,1,'172.20.0.1'),(1558,4,'admin [172.20.0.1]',NULL,'2021-09-02 07:47:02',1,5,0,21,1,'172.20.0.1');
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
  PRIMARY KEY (`id`)
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
-- Table structure for table `multi_instance_instance`
--

DROP TABLE IF EXISTS `multi_instance_instance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `multi_instance_instance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `adminDisplayName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adminEmail` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userCount` int(11) DEFAULT NULL,
  `usersMax` int(11) DEFAULT NULL,
  `loginCount` int(11) DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `storageUsage` bigint(20) DEFAULT NULL,
  `storageQuota` bigint(20) DEFAULT NULL,
  `isTrial` tinyint(1) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `welcomeMessage` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `multi_instance_instance`
--

LOCK TABLES `multi_instance_instance` WRITE;
/*!40000 ALTER TABLE `multi_instance_instance` DISABLE KEYS */;
INSERT INTO `multi_instance_instance` VALUES (1,'test.group-office.com','2021-07-12 14:02:07','System Administrator','admin@intermesh.localhost',1,10,4,'2021-07-13 08:48:59','2021-08-31 09:56:58',661819,10737418240,0,1,NULL,'6.5.73'),(3,'test2.group-office.com','2021-07-13 08:16:20','System Administrator','admin@intermesh.localhost',1,NULL,1,'2021-07-15 08:19:09','2021-08-31 09:56:59',662114,NULL,0,1,NULL,'6.5.73'),(4,'test3.group-office.com','2021-07-15 10:35:11','System Administrator','admin@intermesh.localhost',1,NULL,1,'2021-07-15 12:42:11','2021-08-31 09:57:00',661606,NULL,0,1,NULL,'6.5.73'),(5,'test1.group-office.com','2021-08-23 11:51:26',NULL,NULL,NULL,10,NULL,NULL,'2021-08-23 11:51:26',NULL,10737418240,0,1,NULL,NULL);
/*!40000 ALTER TABLE `multi_instance_instance` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=175 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note`
--

LOCK TABLES `notes_note` WRITE;
/*!40000 ALTER TABLE `notes_note` DISABLE KEYS */;
INSERT INTO `notes_note` VALUES (173,65,1,1,'Test','{ENCRYPTED}5b33a2dc4cc8e0743a320861tvBzK8ufC4OOvEAI9S8VBAk+BzYfDnKeNA==',NULL,'','2021-07-16 13:41:14','2021-07-16 13:46:40'),(174,65,1,1,'ewre','',NULL,'','2021-07-16 13:52:44','2021-07-16 13:52:44');
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
) ENGINE=InnoDB AUTO_INCREMENT=73 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note_book`
--

LOCK TABLES `notes_note_book` WRITE;
/*!40000 ALTER TABLE `notes_note_book` DISABLE KEYS */;
INSERT INTO `notes_note_book` VALUES (65,NULL,1,18,'Shared',NULL),(66,NULL,2,81,'Elmer Fudd',NULL),(67,NULL,3,86,'Demo User',NULL),(68,NULL,4,91,'Linda Smith',NULL),(72,NULL,9,195,'Peter Smith',NULL);
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
INSERT INTO `notes_user_settings` VALUES (1,65),(2,66),(3,67),(4,68),(9,72);
/*!40000 ALTER TABLE `notes_user_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_aliases`
--

DROP TABLE IF EXISTS `pa_aliases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pa_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `address` varchar(190) DEFAULT NULL,
  `goto` text DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `active` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `address` (`address`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Aliases';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_aliases`
--

LOCK TABLES `pa_aliases` WRITE;
/*!40000 ALTER TABLE `pa_aliases` DISABLE KEYS */;
INSERT INTO `pa_aliases` VALUES (1,1,'test@intermesh.localhost','test@intermesh.localhost',1627564701,1627564701,'0'),(2,1,'trashed@intermesh.localhost','trashed@intermesh.localhost',1627564713,1627564713,'0');
/*!40000 ALTER TABLE `pa_aliases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_domains`
--

DROP TABLE IF EXISTS `pa_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pa_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `domain` varchar(190) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `max_aliases` int(10) NOT NULL DEFAULT 0,
  `max_mailboxes` int(10) NOT NULL DEFAULT 0,
  `total_quota` bigint(20) NOT NULL DEFAULT 0,
  `default_quota` bigint(20) NOT NULL DEFAULT 0,
  `transport` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'virtual',
  `backupmx` tinyint(1) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `acl_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Domains';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_domains`
--

LOCK TABLES `pa_domains` WRITE;
/*!40000 ALTER TABLE `pa_domains` DISABLE KEYS */;
INSERT INTO `pa_domains` VALUES (1,1,'intermesh.localhost',NULL,0,0,10485760,524288,'virtual',0,1624871224,1624871224,1,127);
/*!40000 ALTER TABLE `pa_domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pa_mailboxes`
--

DROP TABLE IF EXISTS `pa_mailboxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pa_mailboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `go_installation_id` varchar(50) DEFAULT NULL,
  `username` varchar(190) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `maildir` varchar(255) DEFAULT NULL,
  `homedir` varchar(255) DEFAULT NULL,
  `quota` bigint(20) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `usage` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `go_installation_id` (`go_installation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Mailboxes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_mailboxes`
--

LOCK TABLES `pa_mailboxes` WRITE;
/*!40000 ALTER TABLE `pa_mailboxes` DISABLE KEYS */;
INSERT INTO `pa_mailboxes` VALUES (1,1,NULL,'admin@intermesh.localhost','$5$rounds=5000$UwzCSr8tySOM/KaV$Yw.uS/oSpeFGThKpv37F0Z6TQBvi77ow67vpoEcuAu6','System administrator','intermesh.localhost/admin/Maildir/','intermesh.localhost/admin/',1048576,1624871225,1624871654,1,0),(2,1,NULL,'test@intermesh.localhost','$5$rounds=5000$OJvNFhHF8xtx.MFg$78qWzclafMPdLolWey5YYnEycXXgowfh9ffeXcpVrD.','test','intermesh.localhost/test/Maildir/','intermesh.localhost/test/',524288,1627564701,1627564701,1,0),(3,1,NULL,'trashed@intermesh.localhost','$5$rounds=5000$VFerpnsF0zKh8yHO$jDKGvY7aNY7.2yGVDIWU/cSO16G0SyX/XnFTuRG7DGA','trashed','intermesh.localhost/trashed/Maildir/','intermesh.localhost/trashed/',524288,1627564713,1627564713,1,0);
/*!40000 ALTER TABLE `pa_mailboxes` ENABLE KEYS */;
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
INSERT INTO `pr2_expense_budgets` VALUES (1,'Machinery',10000,0,1624871095,1624871095,1,NULL,NULL,2,'','',1,'',NULL);
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
INSERT INTO `pr2_expenses` VALUES (1,2,3000,21,1624871095,'1234','Rocket fuel',1624871095,NULL),(2,2,2000,21,1624871095,'1235','Fuse machine',1624871095,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours`
--

LOCK TABLES `pr2_hours` WRITE;
/*!40000 ALTER TABLE `pr2_hours` DISABLE KEYS */;
INSERT INTO `pr2_hours` VALUES (1,1,120,0,1626069600,2,'Helemaal niets',100,50,0,NULL,1626093625,1626093625,2,5,2,0,0),(2,1,135,0,1626076800,2.25,'dsfsdf',0,50,0,NULL,1626162576,1626162576,2,NULL,0,0,0),(3,1,165,0,1626156000,2.75,'',0,50,0,NULL,1626163337,1626163337,2,NULL,0,0,0);
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
  `test` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `test2` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `WBSO` tinyint(1) NOT NULL DEFAULT 0,
  `Bedrijf` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pr2_hours_custom_fields_ibfk_go_5` (`Bedrijf`),
  CONSTRAINT `pr2_hours_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pr2_hours` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pr2_hours_custom_fields_ibfk_go_5` FOREIGN KEY (`Bedrijf`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours_custom_fields`
--

LOCK TABLES `pr2_hours_custom_fields` WRITE;
/*!40000 ALTER TABLE `pr2_hours_custom_fields` DISABLE KEYS */;
INSERT INTO `pr2_hours_custom_fields` VALUES (3,'','',1,1);
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
INSERT INTO `pr2_projects` VALUES (1,1,123,'Demo','','Just a placeholder for sub projects.',NULL,1624871094,1624871094,NULL,1,1624871092,0,NULL,NULL,0,0,0,0,'Demo',1,1,2,1,0,NULL,0,''),(2,1,123,'[001] Develop Rocket 2000','ACME Corporation','Better range and accuracy',3,1624871095,1626093613,NULL,1,1624831200,1627423200,4,'Wile E. Coyote',0,0,0,0,'Demo/[001] Develop Rocket 2000',1,1,2,2,1,NULL,0,''),(3,1,123,'[001] Develop Rocket Launcher','ACME Corporation','Better range and accuracy',3,1624871095,1626698347,NULL,1,1624831200,1627423200,4,'Wile E. Coyote',0,0,0,0,'Demo/[001] Develop Rocket Launcher',1,1,2,2,1,NULL,0,'');
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
INSERT INTO `pr2_resources` VALUES (2,1,0,100,50,0,0),(2,2,16,120,60,0,0),(2,3,100,80,40,0,0),(2,4,16,90,45,0,0),(3,1,0,0,0,0,0),(3,3,16,80,40,0,0);
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
INSERT INTO `pr2_settings` VALUES (1,1,1,1,1),(2,0,0,0,0),(5,0,0,0,0),(6,0,0,0,0),(8,0,0,0,0),(9,0,0,0,0);
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
INSERT INTO `pr2_statuses` VALUES (1,'Ongoing',0,0,1,1,0,0,109),(2,'None',0,0,1,1,0,0,110),(3,'Complete',1,0,1,0,0,0,111);
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
INSERT INTO `pr2_templates` VALUES (1,1,'Projects folder',112,24,'','projects2/template-icons/folder.png',0,NULL,2,1,0,'',1),(2,1,'Standard project',113,25,'responsible_user_id,expenses,std_task_required,customer,contact,budget_fees','projects2/template-icons/project.png',1,NULL,1,1,0,'%y-{autoid}',0);
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
INSERT INTO `pr2_types` VALUES (1,'Default',1,107,108),(2,'Demo',1,123,124);
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
-- Table structure for table `studio_documents_document`
--

DROP TABLE IF EXISTS `studio_documents_document`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_documents_document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filesFolderId` int(11) DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_documents_document`
--

LOCK TABLES `studio_documents_document` WRITE;
/*!40000 ALTER TABLE `studio_documents_document` DISABLE KEYS */;
INSERT INTO `studio_documents_document` VALUES (1,NULL,1,'2021-07-16 10:08:29',1,'2021-07-16 10:08:29'),(5,NULL,1,'2021-07-16 12:45:28',1,'2021-07-16 12:45:28'),(6,NULL,1,'2021-07-16 12:46:03',1,'2021-07-16 12:46:03'),(7,NULL,1,'2021-07-16 12:46:03',1,'2021-07-16 12:46:03');
/*!40000 ALTER TABLE `studio_documents_document` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_documents_document_custom_fields`
--

DROP TABLE IF EXISTS `studio_documents_document_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_documents_document_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Client` int(11) DEFAULT NULL,
  `Date_in` date DEFAULT NULL,
  `Date_out` date DEFAULT NULL,
  `Location` int(11) DEFAULT NULL,
  `Client_Month` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `studio_documents_document_custom_fields_ibfk_go_7` (`Client`),
  KEY `studio_documents_document_custom_fields_ibfk_go_10` (`Location`),
  CONSTRAINT `studio_documents_document_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `studio_documents_document` (`id`) ON DELETE CASCADE,
  CONSTRAINT `studio_documents_document_custom_fields_ibfk_go_10` FOREIGN KEY (`Location`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL,
  CONSTRAINT `studio_documents_document_custom_fields_ibfk_go_7` FOREIGN KEY (`Client`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_documents_document_custom_fields`
--

LOCK TABLES `studio_documents_document_custom_fields` WRITE;
/*!40000 ALTER TABLE `studio_documents_document_custom_fields` DISABLE KEYS */;
INSERT INTO `studio_documents_document_custom_fields` VALUES (1,4,'2021-07-16','2021-07-08',6,'\n'),(5,12,'2021-07-12',NULL,9,'\n'),(6,12,'2021-07-12',NULL,9,'\n'),(7,14,'1980-07-14','1980-07-14',10,'\nJanuary');
/*!40000 ALTER TABLE `studio_documents_document_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_studio`
--

DROP TABLE IF EXISTS `studio_studio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_studio` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `moduleId` int(11) NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `studio_studio_core_module_fk1` (`moduleId`),
  KEY `studio_studio_core_user_fk1` (`createdBy`),
  KEY `studio_studio_core_user_fk2` (`modifiedBy`),
  CONSTRAINT `studio_studio_core_module_fk1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  CONSTRAINT `studio_studio_core_user_fk1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `studio_studio_core_user_fk2` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_studio`
--

LOCK TABLES `studio_studio` WRITE;
/*!40000 ALTER TABLE `studio_studio` DISABLE KEYS */;
INSERT INTO `studio_studio` VALUES (1,'studio-documents','Documens module',43,0,1,'2021-07-16 10:06:15',1,'2021-08-24 11:26:17',NULL),(7,'studio-test2','test',55,0,1,'2021-08-24 13:14:28',1,'2021-08-24 13:17:43',NULL),(8,'studio-test3','test3',56,0,1,'2021-08-24 13:18:09',1,'2021-08-24 13:18:27',NULL);
/*!40000 ALTER TABLE `studio_studio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_test2_test2`
--

DROP TABLE IF EXISTS `studio_test2_test2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_test2_test2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filesFolderId` int(11) DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_test2_test2`
--

LOCK TABLES `studio_test2_test2` WRITE;
/*!40000 ALTER TABLE `studio_test2_test2` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_test2_test2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_test2_test2_custom_fields`
--

DROP TABLE IF EXISTS `studio_test2_test2_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_test2_test2_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  CONSTRAINT `studio_test2_test2_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `studio_test2_test2` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_test2_test2_custom_fields`
--

LOCK TABLES `studio_test2_test2_custom_fields` WRITE;
/*!40000 ALTER TABLE `studio_test2_test2_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_test2_test2_custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_test3_test3`
--

DROP TABLE IF EXISTS `studio_test3_test3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_test3_test3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filesFolderId` int(11) DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_test3_test3`
--

LOCK TABLES `studio_test3_test3` WRITE;
/*!40000 ALTER TABLE `studio_test3_test3` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_test3_test3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `studio_test3_test3_custom_fields`
--

DROP TABLE IF EXISTS `studio_test3_test3_custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `studio_test3_test3_custom_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`),
  CONSTRAINT `studio_test3_test3_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `studio_test3_test3` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `studio_test3_test3_custom_fields`
--

LOCK TABLES `studio_test3_test3_custom_fields` WRITE;
/*!40000 ALTER TABLE `studio_test3_test3_custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `studio_test3_test3_custom_fields` ENABLE KEYS */;
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
INSERT INTO `su_announcements` VALUES (1,1,100,0,1624870604,1624870604,'Submit support ticket','Anyone can submit tickets to the support system here:<br /><br /><a href=\"http://host.docker.internal:8080/modules/site/index.php?r=tickets/externalpage/newTicket\">http://host.docker.internal:8080/modules/site/index.php?r=tickets/externalpage/newTicket</a><br /><br />Anonymous ticket posting can be disabled in the ticket module settings.'),(2,1,101,0,1624870604,1624870604,'Welcome to GroupOffice','This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!');
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
INSERT INTO `su_latest_read_announcement_records` VALUES (1,1,1624870604),(2,2,1624870604);
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
INSERT INTO `su_notes` VALUES (1,NULL),(2,NULL);
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
INSERT INTO `su_visible_calendars` VALUES (1,1),(2,2),(3,3),(4,4),(9,10);
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
INSERT INTO `sync_addressbook_user` VALUES (1,1,1),(4,2,1),(5,1,0),(5,3,1),(6,4,1),(10,9,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_calendar_user`
--

LOCK TABLES `sync_calendar_user` WRITE;
/*!40000 ALTER TABLE `sync_calendar_user` DISABLE KEYS */;
INSERT INTO `sync_calendar_user` VALUES (1,1,1);
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
INSERT INTO `sync_settings` VALUES (1,0,0,0,0,1,1,0,1),(2,0,0,0,0,0,1,0,1),(3,0,0,0,0,0,1,0,1),(4,0,0,0,0,0,1,0,1),(5,0,0,0,0,0,1,0,1),(6,0,0,0,0,0,1,0,1),(8,0,0,0,0,0,1,0,1),(9,0,0,0,0,0,1,0,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_tasklist_user`
--

LOCK TABLES `sync_tasklist_user` WRITE;
/*!40000 ALTER TABLE `sync_tasklist_user` DISABLE KEYS */;
INSERT INTO `sync_tasklist_user` VALUES (4,1,1);
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
INSERT INTO `sync_user_note_book` VALUES (65,1,1),(66,2,1),(67,3,1),(68,4,1),(72,9,1);
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
INSERT INTO `ta_portlet_tasklists` VALUES (2,1),(3,2),(4,3),(9,6);
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
INSERT INTO `ta_settings` VALUES (1,0,'0',0,4),(2,0,'0',0,1),(3,0,'0',0,2),(4,0,'0',0,3),(9,0,'0',0,6);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasklists`
--

LOCK TABLES `ta_tasklists` WRITE;
/*!40000 ALTER TABLE `ta_tasklists` DISABLE KEYS */;
INSERT INTO `ta_tasklists` VALUES (1,'Elmer Fudd',2,79,13,5),(2,'Demo User',3,84,15,5),(3,'Linda Smith',4,89,17,5),(4,'System Administrator',1,96,20,5),(6,'Peter Smith',9,192,38,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasks`
--

LOCK TABLES `ta_tasks` WRITE;
/*!40000 ALTER TABLE `ta_tasks` DISABLE KEYS */;
INSERT INTO `ta_tasks` VALUES (1,'1d2da469-7816-553c-bbf6-7864babab97c',2,1,1624870602,1624870602,1,1624870602,1625043402,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(2,'df5d008c-5576-5e1e-a536-4e88afc0b587',3,1,1624870602,1624870602,1,1624870602,1624957002,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(3,'08ceb7a3-96e3-5fa5-98a5-7df6644bbae3',1,1,1624870602,1624870602,1,1624870602,1624957002,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(4,'8eee1e38-5e21-5a7c-b481-abab75bdcc33',2,1,1624870602,1624870602,1,1624870602,1624957002,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(5,'01c275eb-c1aa-5d4c-abb3-20500f83262f',3,1,1624870602,1624870602,1,1624870602,1624957002,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(6,'e9d08cfc-7196-5e96-bdcb-d19928cd5c86',1,1,1624870602,1624870602,1,1624870602,1624957002,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(7,'c399502c-63d8-5cb9-bd9d-5d007b421ea1',4,1,1624870603,1624870603,1,1625129803,1625129803,0,'Call: Smith Inc. (Q21000001)','','NEEDS-ACTION',0,1625129803,'',0,0,1,0,0),(8,'d535e0f4-9c9a-5a10-afd3-9fbd4745e2e1',4,1,1624870603,1624870603,1,1625129803,1625129803,0,'Call: ACME Corporation (Q21000002)','','NEEDS-ACTION',0,1625129803,'',0,0,1,0,0),(9,'d69217b3-c24f-56f3-8a64-9ce00023afc7',2,1,1624871089,1624871089,1,1624871089,1625043889,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(10,'ee289c2c-6917-50e4-bd6a-825be922c55d',3,1,1624871089,1624871089,1,1624871089,1624957489,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(11,'3f96e54b-6b3c-5b68-9dfc-33785217cf88',1,1,1624871090,1624871090,1,1624871090,1624957490,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(12,'5c8b89bb-3367-537a-b569-ba58e43314af',2,1,1624871090,1624871090,1,1624871090,1624957490,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(13,'ca872636-a830-52d9-8930-4f4c2da30919',3,1,1624871090,1624871090,1,1624871090,1624957490,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(14,'ff98bfa3-2ed6-5a57-8579-79699a4c46b5',1,1,1624871090,1624871090,1,1624871090,1624957490,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(15,'152d23db-35ab-5436-b37e-5a2aac509494',4,1,1624871090,1624871090,1,1625130290,1625130290,0,'Call: Smith Inc. (Q21000003)','','NEEDS-ACTION',0,1625130290,'',0,0,1,0,0),(16,'fff03b14-3576-58d0-8849-091c1840ae34',4,1,1624871090,1624871090,1,1625130290,1625130290,0,'Call: ACME Corporation (Q21000004)','','NEEDS-ACTION',0,1625130290,'',0,0,1,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a`
--

LOCK TABLES `test_a` WRITE;
/*!40000 ALTER TABLE `test_a` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a_has_many`
--

LOCK TABLES `test_a_has_many` WRITE;
/*!40000 ALTER TABLE `test_a_has_many` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_a_has_one`
--

LOCK TABLES `test_a_has_one` WRITE;
/*!40000 ALTER TABLE `test_a_has_one` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `test_c`
--

LOCK TABLES `test_c` WRITE;
/*!40000 ALTER TABLE `test_c` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_messages`
--

LOCK TABLES `ti_messages` WRITE;
/*!40000 ALTER TABLE `ti_messages` DISABLE KEYS */;
INSERT INTO `ti_messages` VALUES (1,1,0,1,0,0,'My rocket always circles back right at me? How do I aim right?','',0,0,1624870604,1624870604,0,0,0,'',NULL,NULL),(2,1,0,1,0,0,'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.','',1,2,1624870604,1624870604,0,0,0,'',NULL,NULL),(3,1,-1,1,1,0,'Gee I don\'t know how that can happen. I\'ll send you some new ones!','',0,2,1624870604,1624870604,0,0,0,'',NULL,NULL),(4,2,0,1,0,0,'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?','',0,0,1624697804,1624697804,0,0,0,'',NULL,NULL),(5,2,0,1,0,0,'Please respond faster. Can\'t you see this ticket is marked in red?','',0,0,1624870604,1624870604,0,0,0,'',NULL,NULL),(6,3,0,1,0,0,'My rocket always circles back right at me? How do I aim right?','',0,0,1624871091,1624871091,0,0,0,'',NULL,NULL),(7,3,0,1,0,0,'Assigned to: Elmer Fudd','',1,1,1624871092,1624871092,0,0,0,'',NULL,NULL),(8,3,0,1,0,0,'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.','',1,2,1624871092,1624871092,0,0,0,'',NULL,NULL),(9,3,-1,1,1,0,'Gee I don\'t know how that can happen. I\'ll send you some new ones!','',0,2,1624871092,1624871092,0,0,0,'',NULL,NULL),(10,4,0,1,0,0,'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?','',0,0,1624698292,1624698292,0,0,0,'',NULL,NULL),(11,4,0,1,0,0,'Please respond faster. Can\'t you see this ticket is marked in red?','',0,0,1624871092,1624871092,0,0,0,'',NULL,NULL);
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
INSERT INTO `ti_settings` VALUES (1,'admin@intermesh.localhost','Group-Office Customer Support',1,'http://host.docker.internal:8080/modules/site/index.php?r=tickets/externalpage/ticket','{SUBJECT}',1,'groupoffice.png','This is our support system. Please enter your contact information and describe your problem.','Thank you for contacting us. We have received your question and created a ticket for you. we will respond as soon as possible. For future reference, your question has been assigned the following ticket number: {TICKET_NUMBER}.',0,'en',0,NULL,0,0,1,1,NULL,0,0,NULL,1,'{AGENT} just picked up your ticket. We\'ll keep you up to date about our progress.',1,'Number: {NUMBER}\nSubject: {SUBJECT}\nCreated by: {CREATEDBY}\nCompany: {COMPANY}\n\n\nURL: {LINK}\n\n\n{MESSAGE}',0,NULL,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_tickets`
--

LOCK TABLES `ti_tickets` WRITE;
/*!40000 ALTER TABLE `ti_tickets` DISABLE KEYS */;
INSERT INTO `ti_tickets` VALUES (1,'202100001',91970135,1,-1,1,1,0,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Malfunctioning rockets',1624870604,1624870604,1,0,0,0,0,1624870604,'',1,NULL,0,1624870604,1624870604),(2,'202100002',44842259,1,0,1,1,0,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Can I speed up my rockets?',1624697804,1624870604,1,0,1,0,0,1624870604,'',1,NULL,0,1624870604,1624870604),(3,'202100003',99427432,1,-1,1,1,2,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Malfunctioning rockets',1624871091,1624871092,1,0,0,0,0,1624871092,'',1,NULL,0,1624871092,1624871091),(4,'202100004',67653384,1,0,1,1,0,4,'ACME Corporation',3,'Wile','E.','Coyote','wile@smith.demo','+31 (0) 10 - 1234567','Can I speed up my rockets?',1624698292,1624871092,1,0,1,0,0,1624871092,'',1,NULL,0,1624871092,1624871092);
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
INSERT INTO `ti_types` VALUES (1,'IT',NULL,1,65,NULL,0,9,NULL,0,0,NULL,NULL,1,0,0,0,0,NULL,0,NULL,0,NULL,121,NULL),(2,'Sales',NULL,1,66,NULL,0,10,NULL,0,0,NULL,NULL,0,0,0,0,0,NULL,0,NULL,0,NULL,122,NULL);
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
INSERT INTO `timeregistration2_settings` VALUES (1,0),(2,0),(3,0),(4,0),(5,0),(6,0),(8,0),(9,0);
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

--
-- Table structure for table `zpa_devices`
--

DROP TABLE IF EXISTS `zpa_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zpa_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remote_addr` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `can_connect` tinyint(1) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `new` tinyint(1) NOT NULL DEFAULT 1,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `as_version` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`,`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zpa_devices`
--

LOCK TABLES `zpa_devices` WRITE;
/*!40000 ALTER TABLE `zpa_devices` DISABLE KEYS */;
/*!40000 ALTER TABLE `zpa_devices` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-09-02  8:26:28
