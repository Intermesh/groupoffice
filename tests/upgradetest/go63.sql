
-- MariaDB dump 10.17  Distrib 10.4.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: groupoffice
-- ------------------------------------------------------
-- Server version	10.4.8-MariaDB-1:10.4.8+maria~bionic

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
-- Table structure for table `ab_addressbooks`
--

DROP TABLE IF EXISTS `ab_addressbooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_addressbooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `default_salutation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `users` tinyint(1) NOT NULL DEFAULT 0,
  `create_folder` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addressbooks`
--

LOCK TABLES `ab_addressbooks` WRITE;
/*!40000 ALTER TABLE `ab_addressbooks` DISABLE KEYS */;
INSERT INTO `ab_addressbooks` VALUES (1,1,'Prospects',15,'Dear {first_name}',39,0,0),(2,1,'Suppliers',16,'Dear {first_name}',0,0,0),(3,1,'Customers',17,'Dear {first_name}',0,0,0),(4,1,'Users',86,'Dear {first_name}',18,1,0),(5,2,'Elmer Fudd',89,'Dear {first_name}',21,0,0),(6,3,'Demo User',94,'Dear {first_name}',26,0,0),(7,4,'Linda Smith',99,'Dear {first_name}',30,0,0);
/*!40000 ALTER TABLE `ab_addressbooks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_addresslist_companies`
--

DROP TABLE IF EXISTS `ab_addresslist_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_addresslist_companies` (
  `addresslist_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`addresslist_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslist_companies`
--

LOCK TABLES `ab_addresslist_companies` WRITE;
/*!40000 ALTER TABLE `ab_addresslist_companies` DISABLE KEYS */;
INSERT INTO `ab_addresslist_companies` VALUES (1,1),(1,2),(1,3),(2,1),(2,2);
/*!40000 ALTER TABLE `ab_addresslist_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_addresslist_contacts`
--

DROP TABLE IF EXISTS `ab_addresslist_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_addresslist_contacts` (
  `addresslist_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`addresslist_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslist_contacts`
--

LOCK TABLES `ab_addresslist_contacts` WRITE;
/*!40000 ALTER TABLE `ab_addresslist_contacts` DISABLE KEYS */;
INSERT INTO `ab_addresslist_contacts` VALUES (1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(2,1),(2,2),(2,3),(2,4);
/*!40000 ALTER TABLE `ab_addresslist_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_addresslist_group`
--

DROP TABLE IF EXISTS `ab_addresslist_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_addresslist_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslist_group`
--

LOCK TABLES `ab_addresslist_group` WRITE;
/*!40000 ALTER TABLE `ab_addresslist_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_addresslist_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_addresslists`
--

DROP TABLE IF EXISTS `ab_addresslists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_addresslists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `addresslist_group_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_salutation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslists`
--

LOCK TABLES `ab_addresslists` WRITE;
/*!40000 ALTER TABLE `ab_addresslists` DISABLE KEYS */;
INSERT INTO `ab_addresslists` VALUES (1,NULL,1,119,'Newsletter','Geachte heer/mevrouw',1562610294,1562610294),(2,NULL,1,120,'Release notes','Geachte heer/mevrouw',1562610317,1562610336);
/*!40000 ALTER TABLE `ab_addresslists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_companies`
--

DROP TABLE IF EXISTS `ab_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addressbook_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `name2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `address_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `zip` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `state` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_address` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_address_no` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_latitude` decimal(10,8) DEFAULT NULL,
  `post_longitude` decimal(11,8) DEFAULT NULL,
  `post_city` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_state` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_country` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `post_zip` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `fax` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `email` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `homepage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `bank_bic` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `vat_no` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `invoice_email` varchar(75) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `email_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `crn` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `iban` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000',
  PRIMARY KEY (`id`),
  KEY `addressbook_id` (`addressbook_id`),
  KEY `addressbook_id_2` (`addressbook_id`),
  KEY `link_id` (`link_id`),
  KEY `link_id_2` (`link_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_companies`
--

LOCK TABLES `ab_companies` WRITE;
/*!40000 ALTER TABLE `ab_companies` DISABLE KEYS */;
INSERT INTO `ab_companies` VALUES (1,NULL,1,3,'Smith Inc','','Kalverstraat','1',NULL,NULL,'1012 NX','Amsterdam','Noord-Holland','NL','Kalverstraat','1',NULL,NULL,'Amsterdam','Noord-Brabant','NL','1012 NX','+31 (0) 10 - 1234567','+31 (0) 1234567','info@smith.demo','http://www.smith.demo','Just a demo company','','','NL 1234.56.789.B01','',1561972053,1561972053,1,1,0,'','','','000000'),(2,NULL,1,3,'ACME Corporation','','1111 Broadway','',NULL,NULL,'10019','New York','NY','US','1111 Broadway','',NULL,NULL,'New York','NY','US','10019','(555) 123-4567','(555) 123-4567','info@acme.demo','http://www.acme.demo','The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]','','','US 1234.56.789.B01','',1561972054,1561972054,1,1,0,'','','','000000'),(3,NULL,1,4,'ACME Rocket Powered Products','','1111 Broadway','',NULL,NULL,'10019','New York','NY','US','1111 Broadway','',NULL,NULL,'New York','NY','US','10019','(555) 123-4567','(555) 123-4567','info@acmerpp.demo','http://www.acmerpp.demo','The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]','','','US 1234.56.789.B01','',1561972055,1561972055,1,1,0,'','','','000000');
/*!40000 ALTER TABLE `ab_companies` ENABLE KEYS */;

INSERT INTO `ab_companies` VALUES (NULL,NULL,1,10000,'Orphaned Company','','Kalverstraat','1',NULL,NULL,'1012 NX','Amsterdam','Noord-Holland','NL','Kalverstraat','1',NULL,NULL,'Amsterdam','Noord-Brabant','NL','1012 NX','+31 (0) 10 - 1234567','+31 (0) 1234567','info@smith.demo','http://www.smith.demo','Just a demo company','','','NL 1234.56.789.B01','',1561972053,1561972053,1,1,0,'','','','000000');

UNLOCK TABLES;

--
-- Table structure for table `ab_contacts`
--

DROP TABLE IF EXISTS `ab_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addressbook_id` int(11) NOT NULL DEFAULT 0,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `middle_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `initials` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffix` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sex` enum('M','F') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'M',
  `birthday` date DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email2` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email3` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT 0,
  `department` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `function` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `home_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `work_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `fax` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `work_fax` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cellular` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `cellular2` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `homepage` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `state` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `city` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `zip` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_no` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `salutation` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `go_user_id` int(11) NOT NULL DEFAULT 0,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `action_date` int(11) NOT NULL DEFAULT 0,
  `url_linkedin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_facebook` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url_twitter` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000',
  PRIMARY KEY (`id`),
  KEY `addressbook_id` (`addressbook_id`),
  KEY `email` (`email`),
  KEY `email2` (`email2`),
  KEY `email3` (`email3`),
  KEY `last_name` (`last_name`),
  KEY `go_user_id` (`go_user_id`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_contacts`
--

LOCK TABLES `ab_contacts` WRITE;
/*!40000 ALTER TABLE `ab_contacts` DISABLE KEYS */;
INSERT INTO `ab_contacts` VALUES (1,'04d1b2d9-f7ec-531d-b58e-ad314c70ec56',1,3,'John','','Smith','','','','M',NULL,'john@smith.demo','','',1,'Management','CEO','','','','','06-12345678','','','NL','Noord-Holland','Amsterdam','1012 NX','Kalverstraat','1',NULL,NULL,'',1561972053,1570702804,1,'Dear Mr. Smith',1,46,0,'addressbook/photos/3/con_1.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','echo123','000000'),(2,'c1042689-977c-5ed9-b4bd-c7a75d8c9eb4',1,3,'Wile','E.','Coyote','','','','M',NULL,'wile@acme.demo','','',2,'','CEO','','','','','06-12345678','','','US','NY','New York','10019','1111 Broadway','',NULL,NULL,'',1561972054,1563544477,1,'Dear Mr. Coyote',1,17,0,'addressbook/photos/3/con_2.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','test','000000'),(3,'afa6fc35-4b0d-501f-afe5-329fe1f74370',1,4,'System','','Administrator','','','','M',NULL,'admin@intermesh.localhost','','',3,'','','','','','','','','','','','','','','',NULL,NULL,'',1561972055,1571047810,1,'Dear System',1,0,1,'',0,'','','','','000000'),(4,'f304612d-11ad-5fe2-ac2c-022d6a613472',1,4,'Elmer','','Fudd','','','','M',NULL,'elmer@group-office.com','','',3,'','CEO','','','','','06-12345678','',NULL,'US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1561972055,1562657999,1,'Dear Elmer',1,0,2,'4.jpg',0,NULL,NULL,NULL,NULL,'000000'),(5,'d6000469-ad98-5256-bd93-cb653cdde744',1,4,'Demo','','User','','','','M',NULL,'demo@acmerpp.demo','demo@group-office.com','',3,'','CEO','','','','','06-12345678','',NULL,'US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1561972056,1562249536,1,'Dear Demo',1,0,3,'',0,NULL,NULL,NULL,NULL,'000000'),(6,'5ed8a0d7-8e5d-5b56-8642-29e2aabdeb4d',1,4,'Linda','','Smith','','','','M',NULL,'linda@acmerpp.demo','','',3,'','CEO','','','','','06-12345678','',NULL,'US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1561972058,1561972058,1,'Dear Linda',1,0,4,'',0,NULL,NULL,NULL,NULL,'000000'),(7,'ebab8d17-c68e-5c5e-9dce-76f70b326b3f',1,1,'Read','','Only','','','','M',NULL,'','','',0,'','','','','','','','','','','','','','','',NULL,NULL,'',1562226096,1562226096,1,'Dear Read',1,0,0,'',0,'','','','','000000'),(8,'f5d69d89-114f-5333-a972-b88f6de70969',1,1,'piet','','test','','','','M',NULL,'','','',0,'','','','','','','','','','','','','','','',NULL,NULL,'',1572537426,1572537426,1,'Dear piet',1,0,0,'',0,'','','','','000000');
INSERT INTO `ab_contacts` VALUES (NULL,'04d1b2d9-f7ec-531d-b58e-ad314c70ec563',1,100000,'John','','Orphan','','','','M',NULL,'john@smith.demo','','',1,'Management','CEO','','','','','06-12345678','','','NL','Noord-Holland','Amsterdam','1012 NX','Kalverstraat','1',NULL,NULL,'',1561972053,1570702804,1,'Dear Mr. Smith',1,46,0,'addressbook/photos/3/con_1.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','echo123','000000');

INSERT INTO `ab_contacts` VALUES (NULL,'04d1b2d9-f7ec-531d-b58e-ad314c70ec563',1,3,';;ART-test;info@art-test.com;;;;;;;;;;;','','','','','','M',NULL,'john@smith.demo','','',1,'Management','CEO','','','','','06-12345678','','','NL','Noord-Holland','Amsterdam','1012 NX','Kalverstraat','1',NULL,NULL,'',1561972053,1570702804,1,'Dear Mr. Smith',1,46,0,'addressbook/photos/3/con_1.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','echo123','000000');


/*!40000 ALTER TABLE `ab_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_contacts_vcard_props`
--

DROP TABLE IF EXISTS `ab_contacts_vcard_props`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_contacts_vcard_props` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `parameters` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_contacts_vcard_props`
--

LOCK TABLES `ab_contacts_vcard_props` WRITE;
/*!40000 ALTER TABLE `ab_contacts_vcard_props` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_contacts_vcard_props` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_default_email_account_templates`
--

DROP TABLE IF EXISTS `ab_default_email_account_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_default_email_account_templates`
--

LOCK TABLES `ab_default_email_account_templates` WRITE;
/*!40000 ALTER TABLE `ab_default_email_account_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_default_email_account_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_default_email_templates`
--

DROP TABLE IF EXISTS `ab_default_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_default_email_templates`
--

LOCK TABLES `ab_default_email_templates` WRITE;
/*!40000 ALTER TABLE `ab_default_email_templates` DISABLE KEYS */;
INSERT INTO `ab_default_email_templates` VALUES (1,1);
/*!40000 ALTER TABLE `ab_default_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_email_templates`
--

DROP TABLE IF EXISTS `ab_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `content` longblob NOT NULL,
  `extension` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_email_templates`
--

LOCK TABLES `ab_email_templates` WRITE;
/*!40000 ALTER TABLE `ab_email_templates` DISABLE KEYS */;
INSERT INTO `ab_email_templates` VALUES (1,1,0,'Default',18,'Message-ID: <89a518d23758afc3433dccbc31ef9bdf@localhost>\r\nDate: Mon, 01 Jul 2019 09:06:26 +0000\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_1561971986_f2622e5201a98615f758458cf7b3624a_=_\"\r\n\r\n\r\n--_=_swift_1561971986_f2622e5201a98615f758458cf7b3624a_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n{salutation},\r\n\r\n{body}\r\n\r\nBest regards\r\n\r\n\r\n{user:name}\r\n{usercompany:name}\r\n\r\n--_=_swift_1561971986_f2622e5201a98615f758458cf7b3624a_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n{salutation},<br />\r\n<br />\r\n{body}<br />\r\n<br />\r\nBest regards<br />\r\n<br />\r\n<br />\r\n{user:name}<br />\r\n{usercompany:name}<br />\r\n\r\n--_=_swift_1561971986_f2622e5201a98615f758458cf7b3624a_=_--\r\n',''),(2,1,1,'Letter',19,'PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"�X�H���Bk�RbG����	+��73z�yb����r�� �<�t�p>�[0�������v��\ZA��SH���]57�J�d���+���r��!�Q�NS�+��6������d�&c鑴�8���G�S��s�<C��q����PKvՎ��\0\0\0�\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlŐ�N�0��<E�;K�M��i���x��FJ�*-}{��;Q��&qK������ݧ�����|!V��5UƟ\n�~x��H�|�<r@���n��5��\"�{�C!��\\)�\r:���S��� �o8)�k���C�:�U@1��1-�٭�ƭ�P��42���N~���N���B�C/؋Wr0	t����2ˤ\Z��;\\�a����D�\\�G�疚`ߠo�;�]d�o��\'�2jq-�\r�QW�rs�[��߿�~u����PK�I��\0\0w\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�V[o�0~߯����B�MM+Ƅ@B����!^۲���g����d	c���>�������6!�3\Z�޵k[��,�t�?�&W}ے\nh�Q�;$��/w�P�A���T�����CǑa��׌#���L$��Tl����\")��	q|��:	`jߗZ�8�;�Ѐ���q}7<ηSH�� ��\r�!%�Z\Z\r�ɗ�V���A��?\n3d8b٘Q%)����B[���s�*6��QY�R\"-61m�5a�cJC�q�9\\%�a�o3��1��@�P\Z��gj-�J#B �Hb8Y�GTts!#L����7*(��R�-%��f/#@7Z�>�u��:����:�\\-����S8��{^�M�x��=�H}�G��^��y��ɛ��۠��@�_e�\r��(;�@�ݾ�c�3TH���Q-�HYsL��\\{�_(��$��EX�-M�L�6q�Oڅu��/�=�V1�ժg��B��\'1���`�m�k����ҥ��v� m�U)��A�������?�D����5�����W����2���n��߄�KU���*���&�,�ʚb\Z�.4!0�tr���+�n]Z�\"MV��i�9�����N�����c:�T=�-ޮF\rҌFh�޲�����(h�F��D��i䐾���k:���X[�ܖL�����T��=Z�;eS~���PKT̓�\0\0E\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�W[o�0~�WDy^�tE�5P\r���b���Ib�%�O��i�;����6n�`}�{|>��Ή�����H��X��,�a\0�j�U>?]��^��E�Z�,\\�\r��g�u�4�$(��&zVF%� �IN��:��2�Y�)tM؍0��@,�(����e�H��4y��w\\�IO#���k^�~��}�+)z\\��ZV\ZM�Z�)Z^I�\Z����3�(����(w��[g���/5[��l�4W��Ɋ�Yh��Qz\Z\r������z�I��=o�n_4�1}M%5�\n8Z�\Z�`a4�(ڣར���8�c��\\���Ed\0�G�9�&� �Qp�a�5z�>Ɵt�3\",��.7�n�>6%��]�D�E$��S�k)�o��L3n5�d�+G�Q���g7ڜ $�bB��v���9�!@s\Z,u���̸��}q(���t�����|����pv��B�n������=�����/�\rX��0������,���j%Y�h���������rP��$�fe~�W�L_N\\UR���x�*���b1F�һ&�=*�\Z��\Z�7�\n7F�\r��7�ϋ-u��e%�[��t8�K\"Z����o�_�i�\r4����\\/[�+��Μ��\\����kr�bڄ+��G�R0�6�}�<yF*�]��\\F}�mJ��;PKm�Ø\0\0�\r\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��טd���)�n�f霨XH�����-4�;������b��1�:��)�)��;v�.7�H(���wБ$�w�5�\0\r�\".u���&u+S�c���G+1��H�8\ZO^�Қ���4�2���Wܞ���_z�/!{����b}_sz�C�Q��bo^~<�T���z�7n^��M;�Mq�0侟��6�Y�z�ͤ�L�z�^�$�\0PKI���\0\0\0j\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xml}��N� ��}�����M�v��]����w��mh�p��^�[un����C1ݪ&ـu��%�A	h��R�J�����8�u͛VC�v�д�(�a���h[�KpIiǄ)��{�0vb\r��,:4��U܇Ү��⃯\0_r�x^s�q/L�`D{e-���M�C\n�w�f��J����+�_���Y8vr��@u]�uy�B~�_�O���U	@U�W3a�{�� `������~1CUpLRr��тNX>ft��V�?������jV���׆Q����g�p��a�K	���?E��j����4O�(F&,��\ZG>8b�?��Ҹ�P���U_PKt�G\0\0�\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0�D�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0�D�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0�D�BvՎ��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0�D�B�I��\0\0w\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0�D�BT̓�\0\0E\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0H\0\0word/styles.xmlPK\0\0\0�D�Bm�Ø\0\0�\r\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0)\0\0word/document.xmlPK\0\0\0�D�BI���\0\0\0j\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0docProps/app.xmlPK\0\0\0�D�Bt�G\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\"\0\0docProps/core.xmlPK\0\0\0�D�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0\0\0\0\0','docx');
/*!40000 ALTER TABLE `ab_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_portlet_birthdays`
--

DROP TABLE IF EXISTS `ab_portlet_birthdays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_portlet_birthdays` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`addressbook_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_portlet_birthdays`
--

LOCK TABLES `ab_portlet_birthdays` WRITE;
/*!40000 ALTER TABLE `ab_portlet_birthdays` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_portlet_birthdays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_search_queries`
--

DROP TABLE IF EXISTS `ab_search_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_search_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `companies` tinyint(1) NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies` (`companies`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_search_queries`
--

LOCK TABLES `ab_search_queries` WRITE;
/*!40000 ALTER TABLE `ab_search_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_search_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_sent_mailing_companies`
--

DROP TABLE IF EXISTS `ab_sent_mailing_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_sent_mailing_companies` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT 0,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `campaigns_opened` tinyint(1) NOT NULL DEFAULT 0,
  `has_error` tinyint(1) NOT NULL DEFAULT 0,
  `error_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_sent_mailing_companies`
--

LOCK TABLES `ab_sent_mailing_companies` WRITE;
/*!40000 ALTER TABLE `ab_sent_mailing_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_sent_mailing_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_sent_mailing_contacts`
--

DROP TABLE IF EXISTS `ab_sent_mailing_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_sent_mailing_contacts` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `campaigns_opened` tinyint(1) NOT NULL DEFAULT 0,
  `has_error` tinyint(1) NOT NULL DEFAULT 0,
  `error_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_sent_mailing_contacts`
--

LOCK TABLES `ab_sent_mailing_contacts` WRITE;
/*!40000 ALTER TABLE `ab_sent_mailing_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_sent_mailing_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_sent_mailings`
--

DROP TABLE IF EXISTS `ab_sent_mailings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_sent_mailings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `addresslist_id` int(11) NOT NULL,
  `alias_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT 0,
  `total` int(11) DEFAULT 0,
  `sent` int(11) DEFAULT 0,
  `errors` int(11) DEFAULT 0,
  `opened` int(11) DEFAULT 0,
  `campaign_id` int(11) NOT NULL DEFAULT 0,
  `campaigns_status_id` int(11) NOT NULL DEFAULT 0,
  `temp_pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_sent_mailings`
--

LOCK TABLES `ab_sent_mailings` WRITE;
/*!40000 ALTER TABLE `ab_sent_mailings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ab_sent_mailings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ab_settings`
--

DROP TABLE IF EXISTS `ab_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ab_settings` (
  `user_id` int(11) NOT NULL,
  `default_addressbook_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_settings`
--

LOCK TABLES `ab_settings` WRITE;
/*!40000 ALTER TABLE `ab_settings` DISABLE KEYS */;
INSERT INTO `ab_settings` VALUES (1,1),(2,5),(3,6),(4,7);
/*!40000 ALTER TABLE `ab_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `abr_relation`
--

DROP TABLE IF EXISTS `abr_relation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abr_relation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_model_type_id` int(11) NOT NULL,
  `parent_model_id` int(11) NOT NULL,
  `child_model_type_id` int(11) NOT NULL,
  `child_model_id` int(11) NOT NULL,
  `relationgroup_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `relationgroup` (`relationgroup_id`),
  CONSTRAINT `relationgroup` FOREIGN KEY (`relationgroup_id`) REFERENCES `abr_relationgroup` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abr_relation`
--

LOCK TABLES `abr_relation` WRITE;
/*!40000 ALTER TABLE `abr_relation` DISABLE KEYS */;
INSERT INTO `abr_relation` VALUES (1,4,1,4,7,2),(2,4,1,4,1,4),(3,4,1,4,34,2),(8,4,1,4,33,2),(9,4,34,4,185,3),(10,4,63,4,63,4),(11,4,64,4,64,4),(12,4,64,4,73,2),(13,4,64,4,66,2),(14,4,64,4,65,2),(15,4,64,4,74,2),(17,4,71,4,85,2),(20,4,71,4,39,2),(21,4,71,4,41,2),(22,4,71,4,42,2),(23,4,71,4,75,2),(25,4,71,4,52,2),(26,4,71,4,76,2),(27,4,71,4,77,2),(28,4,71,4,53,2),(29,4,71,4,46,2),(31,4,71,4,79,2),(32,4,71,4,80,2),(33,4,71,4,81,2),(34,4,71,4,82,2),(35,4,71,4,83,2),(36,4,71,4,84,2),(37,4,71,4,86,2),(38,4,71,4,87,2),(39,4,71,4,50,2),(40,4,71,4,51,2),(41,4,71,4,67,2),(42,4,71,4,90,2),(43,4,71,4,47,2),(67,4,10,4,10,2),(70,4,68,4,68,4),(71,4,68,4,70,2),(73,4,68,4,11,2),(74,4,68,4,10,2),(75,4,68,4,12,2),(76,4,68,4,31,2),(77,4,68,4,13,2),(78,4,70,4,14,3),(79,4,70,4,95,3),(80,4,70,4,15,3),(81,4,70,4,96,3),(82,4,70,4,16,3),(83,4,70,4,97,3),(84,4,70,4,18,3),(85,4,70,4,19,3),(86,4,70,4,17,3),(87,4,70,4,21,3),(88,4,70,4,22,3),(89,4,70,4,23,3),(90,4,70,4,98,3),(91,4,70,4,26,3),(93,4,70,4,99,3),(94,4,70,4,24,3),(96,4,70,4,70,2),(97,4,70,4,27,3),(98,4,10,4,32,3),(99,4,10,4,100,3),(100,4,10,4,101,3),(101,4,10,4,102,3),(102,4,10,4,103,3),(103,4,10,4,104,3),(104,4,10,4,105,3),(105,4,10,4,106,3),(106,4,10,4,107,3),(107,4,10,4,108,3),(108,4,10,4,109,3),(109,4,10,4,110,3),(110,4,11,4,11,2),(111,4,11,4,186,3),(112,4,11,4,211,3),(113,4,11,4,194,3),(114,4,11,4,111,3),(115,4,11,4,28,3),(116,4,11,4,112,3),(117,4,11,4,113,3),(118,4,11,4,114,3),(119,4,11,4,115,3),(120,4,11,4,29,3),(121,4,12,4,12,2),(122,4,12,4,116,3),(123,4,12,4,117,3),(124,4,12,4,118,3),(125,4,12,4,119,3),(126,4,12,4,120,3),(127,4,12,4,121,3),(128,4,12,4,122,3),(129,4,12,4,123,3),(130,4,70,4,25,3),(131,4,189,4,189,4),(133,4,189,4,57,2),(134,4,189,4,3,2),(141,4,71,4,71,4),(142,4,70,4,20,3),(143,4,1,4,179,2),(145,4,63,4,216,2),(146,4,0,4,63,4),(147,4,0,4,63,4),(152,4,189,4,171,2),(154,4,189,4,197,2),(155,4,70,4,219,3),(157,4,52,4,208,3),(158,4,52,4,203,3),(159,4,52,4,204,3),(160,4,52,4,205,3),(161,4,52,4,206,3),(162,4,52,4,207,3),(163,4,52,4,209,3),(165,2,0,4,129,3),(168,4,189,4,227,2),(169,4,227,4,61,3),(170,4,227,4,129,3),(171,4,227,4,228,3),(172,4,227,4,169,3),(173,4,227,4,223,3),(174,4,71,4,174,2),(175,4,71,4,45,2),(176,4,71,4,91,2),(177,4,71,4,166,2),(178,4,71,4,92,2),(179,4,71,4,93,2),(180,4,71,4,44,2),(181,4,71,4,231,2),(182,4,71,4,191,2),(183,4,71,4,48,2),(184,4,71,4,49,2),(185,4,71,4,172,2),(186,4,71,4,192,2),(187,4,71,4,170,2),(188,4,71,4,181,2),(189,4,71,4,232,2),(190,4,71,4,93,2),(191,4,71,4,78,2),(192,4,71,4,94,2),(193,4,71,4,233,2),(194,4,71,4,234,2),(195,4,71,4,54,2),(196,4,71,4,55,2),(197,4,34,4,235,3),(198,4,236,4,236,4),(199,4,64,4,238,2),(200,4,64,4,237,2),(201,4,64,4,239,2),(202,4,10,4,240,3),(203,4,11,4,241,3),(204,4,11,4,242,3),(205,4,11,4,243,3),(206,4,11,4,244,3),(207,4,11,4,188,3),(208,4,13,4,190,3),(209,4,189,4,56,2),(210,4,13,4,245,3),(211,4,189,4,250,2),(212,4,250,4,58,3),(213,4,250,4,59,3),(214,4,250,4,247,3),(215,4,250,4,187,3),(216,4,250,4,248,3),(217,4,250,4,249,3),(218,4,250,4,62,3),(219,4,197,4,252,3),(220,4,197,4,253,3),(221,4,197,4,251,3),(222,4,171,4,255,3),(223,4,171,4,254,3),(225,4,3,4,196,3),(226,2,0,4,256,2),(227,4,64,4,256,2),(228,4,38,4,38,4),(229,4,64,4,259,2),(230,4,7,4,35,3),(231,4,71,4,261,2),(232,4,64,4,263,2),(233,4,265,4,265,4);
/*!40000 ALTER TABLE `abr_relation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `abr_relationgroup`
--

DROP TABLE IF EXISTS `abr_relationgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abr_relationgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_label` varchar(255) NOT NULL,
  `child_label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `abr_relationgroup`
--

LOCK TABLES `abr_relationgroup` WRITE;
/*!40000 ALTER TABLE `abr_relationgroup` DISABLE KEYS */;
INSERT INTO `abr_relationgroup` VALUES (2,'Tier 1','Tier 2'),(3,'Tier 2','Tier 3'),(4,'Sony','Tier 1');
/*!40000 ALTER TABLE `abr_relationgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bm_bookmarks`
--

DROP TABLE IF EXISTS `bm_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_icon` tinyint(1) NOT NULL DEFAULT 1,
  `open_extern` tinyint(1) NOT NULL DEFAULT 1,
  `behave_as_module` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bm_bookmarks`
--

LOCK TABLES `bm_bookmarks` WRITE;
/*!40000 ALTER TABLE `bm_bookmarks` DISABLE KEYS */;
INSERT INTO `bm_bookmarks` VALUES (1,1,1,'Google Search','http://www.google.com','Search the web','icons/viewmag.png',1,1,0),(2,1,1,'Wikipedia','http://www.wikipedia.com','The Free Encyclopedia','icons/agt_web.png',1,1,1);
/*!40000 ALTER TABLE `bm_bookmarks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bm_categories`
--

DROP TABLE IF EXISTS `bm_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_in_startmenu` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `show_in_startmenu` (`show_in_startmenu`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bm_categories`
--

LOCK TABLES `bm_categories` WRITE;
/*!40000 ALTER TABLE `bm_categories` DISABLE KEYS */;
INSERT INTO `bm_categories` VALUES (1,1,38,'General',0);
/*!40000 ALTER TABLE `bm_categories` ENABLE KEYS */;
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
INSERT INTO `bs_books` VALUES (1,1,'Quotes',22,'Q%y',6,NULL,2,19,'€',NULL,NULL,'',3,NULL,NULL,0,0,0,0,0,36,0,0,0,0,0,0,0,14),(2,1,'Orders',27,'O%y',6,NULL,2,19,'€',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,37,0,0,0,0,0,0,0,14),(3,1,'Invoices',32,'I%y',6,NULL,2,19,'€',NULL,NULL,'',0,NULL,NULL,0,0,0,0,0,38,0,0,0,0,0,0,0,14);
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
INSERT INTO `bs_doc_templates` VALUES (1,1,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i��Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz��D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a������������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH��I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!����Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�������Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^��^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt'),(2,2,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i��Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz��D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a������������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH��I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!����Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�������Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^��^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt'),(3,3,1,'Invoice','PK\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0Thumbnails/thumbnail.png�PNG\r\n\Z\n\0\0\0\rIHDR\0\0\0�\0\0\0\0\0\0zA��\0\0!�IDATx��w@G�Ƿ�^��p�޻\n\"��ǂ�hL11�)�y�}ӓ_�)��K�D��h�Xc{�<D��8w��B�L��������3s�_f�yfv`��z\0����\0z5�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0��CW�Ӽ�7��U��[��ζ���%��x����r[�5��7\"�>�_<Tz�ׇ̎߶�H��qO��\'�-���|�K_�_{+]D�&�~����go�p���7\\�}�>#��m��./������c���Ԋ�h�4���94ݓ2��$���|+7�9��XQ�%^|�tç\'��L�xౢ�`8��|��Y�Z���+Ȉ��ß���|��yK�+\"1]��~�W�@Vm�F��Ж}�8t�k���-��\n(&���������>�_���E�NQo��\n<���^���5��x�`��[�#!�����������r7����r9]�y�%ڣ�H�a����:�eyC�;[������1�5G.؎O�`�a�R���d�̇}+�7a��pt��lF������/@��g�_S�������k_,���?o(���ʚr�Χmc`����pe�~܉C��ˮ����\'�cg����*� I� ,�x�$e#}��z�ޅ�Ή㓜�k��L�d���![q�f�p{�۪6�a�8�vC[}�PK4�0�$0�0YR��9�FZ�?$X��eƊOͻ�g\Z>sϘ���9ˌ?���Յ���,1l���X��!�ߞ��Y��Xsʲ�[�\\?Ӽ���o��R����;�*�)\\����O�E�w�O5�g&�6�G?�3���a�\\>�3���CA�i7����w�\\,��i��Gy?��\'�U�>O���}\0(@\0\n�������^Li����T\r�cwuQK��UK�,���~�����������y�l���+r�P]�]�������u��qnE�r�Lq�Ԑ?�,���u����\n��ܸӐ�q�X�T��D�Z=hXv��%�|��^�t�9y�L��vU?҉���	��z��cbjU#�w``8?(5FDbZ\'\"ߘ��ǯ����vЀ<`X��G��V:��4{;�Կ�>��9�CVS���XS�2��w��Q����\nm�x���9>��6�P����3BLu��m�B%ٵ�TlhP�����[k&�H6����R���c%��$m��	���B9�l@{,ԇ�֪�������nU�);�b�*eBX��>HPp�:�sMNwܷF���Ju�3c<H��՟m�X��ӭtڬQ5o�)�鄉�ٻY���k~f�}Ə�Ե5~�캥9�W��ݟ{�d=�����f?~�Xz��˜��N�/��r�/�U�Ļ�斊�cZ׽?�C�e���VPl�$��%����c}x�SJ5��1��?<�Mj�Z����I��(g�߲��R���Sh1�$�`؇E�kI����D>�C{�DF��*�[�߼(���W0	��\Z�q�jTa�ú2��\\H��h�V�2ՓMޮ�`���3@;,��i8|���E�,�aH�{$txě�+4yهd��G�|���K�bw>!;hh�G�H\Z��0��0�P����%{��]h���5�ՠ��F�`2���j�.��C�p!����\\p6[�6oUV�餦��N�qC5Nr<CS\")��d(k���a%>_ؾ	�kWY�|�\"�yk�8�w�ã	��F\Z����9Sc>z���%s=>����V�g�^e������o����D	�G�,�2�5ة��t��z/�Pߦ��/UyW�;�������و��T9����]�ΐ������K�Elp*��<#�\'>_��JZ��0=_���\';dk�|���K��&�jؐ�̃���ۙ�Gj\Z�H�c�b����-��6�K2�`�}���-�$�� ��mv�W�0��ܶGL�/�X�1_��p��t lc&�������t[���:�8�����f��P]ɓ��]-����������^O��fׁ��z\n7\\R�- XL!Ezf^�R���@7�f���L�L0]ݟ��0p&����4���fp>��$���Kd���;�6������~^��/�ܿ�m\Zwl���F�h�=�5��~��d@V���}.�����t�\Z�L��m���Y��<!���<OJ�1Z�����[�3bj5e+b�6A�5��R5�-W�;�F�c�3��0��m�!%�P�04e��l�vw\Z��ّ\Z��i>;*�dM���,|�p#�[�\n��G�)���d��5�of�(@\0\n��Am^�k��+��>��]P�ů.�=���o�J�l�woz���?�X�~���.���xs������NWZ�\\\n�^�**Z��EJ��b\\�$��0ch�y\"��Fô�o����-�E���a���r���ԛ�H����T���\\n\\��(v�Dv\n��?�X�����/�9мÏql��{�v�䣛O�2N[ρi�6���Ƽ��˓��y��5�@����\0}\0(zR8��_���}`�Vm�J����s7n�����T������:y�-m��iu��ͥ?nO|i���[��̍�ں%�Z!��<)�m�h�A�ߌ�����1e������=�)�^�>��o��#�Mi��C����R}t�T۬<��ܱ�\'�?ߏ�uY�1�.���%k?*\Z�d��%����Ɇ�X͆�������\0t��^S���Wp{;���c�.�䗤��D�s��b:~���M?�����bۚ�ϥ8�w�*��ᇞ�������g�W�H�NZ?}���\'�jݟ����5<�cd\'�C�s�~,��t�)f��Ѡ��מ|w�/�݋��ߝv��~WĖ��oE�b5ª3{�^8��h@G$��+X�|!�A�G+6m(p�`<}��%��qfW窺\'l���n�4�1E�-��狂S�s*������35�f�x9����+5Z�BAs��ٛ��<ҸR�ii\ZF1�d���Y��8�\r�\r�z�����k��pK�fxև7�aXs+m~7\Z��4�XTxǫ;��c_)���4{Ӓ�cb����R�%��M��NH�<fo\\��F:��g���Y��/��l�b5�^���f�7.V�����X���pA-$*Bߑ�c��0�K�B��\Z,��C����(@\0\n����\0}\0(@\0\n�����J����}�x��d��g�X��.޷�>T|�,bF���D�P�{w\\գ�kW-�<Ըd�^쬗�����]�w���`�>�\\���D:�Z/�֯L�����aL��I���!W[�J�k瘄f�\'oܤ��$�zr@��b��(\\l��7n5�|�ߓ�y����6g�1�v���m:p����/�,[qF�1*���k]�xƼ8�o;��H�Y���t����#x��ߗ�{�k���V�[��^w8��v�����fe$8wr_Z��7o��.��T&�޾x�\n]�va�6+��a��-��!~6:���U���bz��D�h��Y�_�؟d�G8SX\rM\n�:�^Oӄm`�����\ZHa��#�wi�^�Ǵ4f�\0��P����w@��/���\Z��}W:�F��>�n9�:�뜸0����j��׮0,�u�r��{��+{��9C&YQ����q�9�\'s�)��p��J톍^9����U��B�@��+��\Z��]vx{���33�C��B�AA|�ۣ�)���LmE	+,̾��e�)��Ua���Xt_SsKn�Ω+W�5���m��sר�L�!���w�V��������v��_�9ۻR̻dӫ�o���Tp��������L��7U������l����~��?�j��*���8/���?�\\��B���L��yu�����+��W(:1՗h���`g���n�v�}�l�����r�&<)�����$�QS\"U�8p���4��Ӫ�T\'��q���D�wVVܐz�xd���n���NA�l�؎\'(����\r=i\'8|�\\�#���A�r^������ر.>���	G��A�|����y�}�68<s�!���\"!;���}��ƜI���yo\'���\'���a!�����a�&S\"f��?1��رF$��|귪\"f��53�_|Sm�r�	����b�6b�? %�k��r��mw%CЩ�9�a����y��2����iÚ�6��sK�orȚ��vN��BQ$qE���y$T]��\0�6�EV��3ԫ�\r;N�c��r\r`� �9k�,�u��ҩ\Z�n�=�qK�L��w��׈�!V���i;�Ge����n��&��~��\"n�K�)����G�IO����Af\'L�Vl�կ����ǃ=��W��1��M�<\'MT�s���)1�6��\'-�M�,,�l�.��kU|��YY�$��8s��C��o���kʫI?��Wu�|��-0���R��?�*P��{��Ӧ�k�j����1���*$)G?V}E��t�Q�vhv�pSӄ���/)�z��	���$er-�@�uuZ.��J���upni�d�����j%��o*��b �V�����ƅ��A�i�f��B~}��N��8��Jo���]�1�^M�?��֟��<�}{��?��5\\{�R���y�́ϼ��|*@���=�PyMQ}tf���W���D�Żu=@g�>4E�}�K�jKxGٜ�{�>t�ȈҜS��[Z4pD\\w\'�:q�H�����ֹg�6,��^j�b��#p��S��#c�߯s=<�bCN]k�c�.�� 1���/򴩮ckk�Նf��*/hpd�%��@��lB[�w8���Jr�)q�VZM�Od?R\\E��Fơ^{�F�g�4Ϧ��Y>�m�l��/\0z����f��cQ���a�hy�4<��b;����^zvk���dg��m0�Ic�k��+����`�����������چ�ư�_~ďeJ�9\"��ǫz�\'9~FJ�E	�2T{�fM4�>�u�Sc߉���Uש\r���j|;���K��A�#<!S� ���ƚ���;����涙Wbo|��ڨ�zr׃�Aך-�8�Yv��\Z�ǀM�6��\'[e�-Xp�{���u�}_���`��<���\\��ɏ̼s�0`�>p��((��h�9�۠b7\n˥JmwU�Q�{�P��)M���M�HH���P/�w`�>��\\}å=��t�`z�������V90Yw�B��G������V6z\Z����V��<6�8�ϞIB��7��Q��v��U{�h�J��%�y�����WZ�jYɁ��2f޷��@��+-8�g��s�)��i�Je�;d�)����M�,�>M�B|S�	���Z\0}<�t�nQ+w�j�>\0�\0�@�\0P�>\0�\0�@q��<4��+j��.ܵ_�&n0��J7�ux��S�a����Ptɞ��B�c���R�@cji5.��r]�Nh\Z\Z�х+����B�� 8����s~O]��5�׉��<\\.�1���Xv��q�NR!gMr���M%i�f]QMC:!��1��R+�Ku>���(��2.P\\/�5��\Z��R^/�2}�v�Q1�Q͇�=������9�6\r\n$���1�`�q�������C��˸\0p������/�>�B����@�c�>���~<\'�폝8�\Z >��1j���W�����_��}�������9�>�>��Օ������R���Tr䷛dFT�&<�GP!������!̣�H^�H��\\]�y,��g�H�v̼~ƍ���Çe]Qm�/:�K�+oe���]�:�	�)zC�z+���OE1r�NL�6ԫr,�k�,�Gc��3:?7\'/q��2U�7�\"�����eG�]�8�֞\n}<��H��\'p�qG��2}����Z�Mѩsu������{��?陴ʭ��i\"�j矲��J�seK�u%.W�j������2}�S������ĹCL�3&[QT[�wN�>ư�z��x�������/�7�u\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�@�\0P�>\0�\0�e���[��Unܤ)�ܔ���:BMƎ��I���U�w�E\rw��Vv�#U�h�1/�y�l�Mjt�é�)�T�֥�T�\'�Orc\Z�4^�t�n\\��%����=㗕�L����[k&�rZ���=��6̀a����J���\nd�;^�X�NVpQ�!�,��\Z/�\\W�l�b*�/`�>���\"����7�_v�o�3}n�h;~(��:�eyC��Zrlw��W=��$С$�\n~���o�NJ`+i6���s�3~ܐ�(��a$[H�_��kO~�z�Vu��U�����U���a����!�ǎ�#���o祇���6(�UY\"�ۊ�J�o~m;!�hW��ya�3�M_��lca\0���������t�$�zܢiI-���b�[�4Aʎ�̜�ܟ{2;w�S�����H��]�Nyf�vk���Hbƺ�;x繦N�9��F��]p��ܾ�e��k%��W��]���u*=F�����q�b������=[�����Jnk\Z�lùne�����\rb`\ZAPjB�f�F�^I5ʫ�)6I���i��?<(���J�\\Qv�PK�����<�^|coM�#^Յ�r���\'�*=�,��`<��d?r4Y�i�9��5>�A���>�rh�\Zj���0d����W�ɱ�S�8vS�R��8�p�>,��Фht�C��p�MjT��q3Xs`7�۝���`�>.�O=m�}�))a�i3酧0]ݟ��Qc�ݛ~��L[����K�5�Mt1|f��<�]�Vq�J�h�,WÍ�~�9���^ل	ü��S��1�o��N�&�iJJ´%{ΉK�F�[aΙݶԩc4�*�J�����Ɣ8��D?�>���0㧦\"/��L:r��v\ZH07���*�o��{�S�6fb�=]ɍ��qg\Z�+c��I�aY�������;uyֺrx,����Z�g�����[�~|1u�e��>|<�_���Z��xwf�|�d�GEc�%9P4ԏ}���jkwdM�����?�Ly�daW�Y2��S-h�9��9$\";��_�I��&	�W����io��L=� ����;p�l�կO������ڪ3�w����L��k�_�̱1r��Ɨ_Y�E��o�g�(�/����Q��;%��w|��\\<$��ܼSIۇ,{��wo�z��4���E���������\Z��i�]y�����E�?}V��˩j��U\"H�հ}�#gG�=�䌔�l&�t˘�w��	ݲY�ULZ���%��1����<���QNV�Cym��:oǪ�_�����3(�����y��}u �V���nc�(�ǉ������ݗX\\ՉZ�iaLL+\Z�Pt�3��k(���)/�\Z4$��R�x9����?z������jii\rM���1����0\\�H��jC��~�wa{��\Z�Z�Pq�HcD����cb��[�~e���D�&��|�-�����C����Y_�S��M�D����m��h��~g���as_]�r��S��؎V��뗵�y�ys��ڪ�ǟ\\�(07�Ln�f���\rz5V��Ҧ�v�>y9\no1������7F@��c�y��*�s0[�ӕ�I���U[���^�{|����0oc��ecb�L���a�>�!H���c��7��E��Z\r&�3!�>��+��9Ϗ3z�Z�Mqv��U� ��+�9c6��s}�W�X��O�>2-iY�+��o�s��y��>���ۃ7�8YU������獵}���\'�^t��Œ�j��Q��Ww��v?V�#Z��A�1\"F1��0S��W0�)��4�h�:���O�y����80�G�B�o|�ڼ��Ş� �1�`����)a�+Lᐸ�Y��3���$]�pH������s�L��nrׯ>�&f��c��2x��}]��Տ\Z#ؾfg>#���#��u�����yb�e�Њ�<�T�H��׃��]Z���8�ٹ�DDU٪�����C$Aʎ���F=a������������;��y3�8���n�$O���[���������ސ�>yP0*�r��+���,I)it����r9���Iq]���\nZ��]D;P��	��+�b�߽�H.��(t�T_�*dL/��T�5�1����0M������)k|��7_7���x�7h�Z�c�W�2��\r�v\'X<��B���WhYv.<}�(������e�`{�^�Py[r�Z��c\Z��_��̎8}�Jq�����i��Tq��v6kJJd�0��~����/�5���緦{GeN|�]��4���lL��ͳφ�6o�zM�\rxd�\0�^t���d�t���m��t�\\sZ��g���X�b�>�����&�=�X����ꝵzh�T]�Onz�_�Z@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0\n����\0}\0(@\0����H�V|~�\0\0\0\0IEND�B`�PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml��r۸��_�r�}�,����v�ػݙ$��(mg:�LB2���,��}���W�Kz\0^̻H��M��H�9��_@�~�݃��1���-%]Ѥ�L߲��R���^�I߭~���ll/,�ܹ�c��{�\0�G��R�o�#jӅ�\\L�\\��b�E\Zz!�\nG({t\Z��46��)2�����;�4�Eо)2����7~S�������9*��y)�1,Tu��+��⓭���sU�&�	\\�#���L;�oFU]����5��æI�v�-&�E�*h��o[���B4�\"�mC\0g�;���wh�q]��*t2S��������-��^6#*��Ac6C�4���	�!tPA��i#5����ׂ��0I����&r�D�[&4��U���=7��� h����	0�*�������;�\'`�0�l{�!�I2�+��ӱJp��f�<`�����;�:���gc�-��RP g������6�#e\"y�A�s!��!������r�č�D��<�&yh��<`rW$@�`b�)��Ef�L����w��2@��R�6X�,R��`N܇f�qK��M~ŜW��Y���?�|N��u�S*��*Nء�S5�@�7�Ĳ�M��^��7��9�K�o��x%@`}ec0�v��Q��?�`�i�Y���[�\0 �E^\"��	��;4�����d��������Fw��^Bl8s��δ-4x�<:���P�J�K`K�g�����4 ����O�����u���]�SBP<^�5�۔����-\"��W�$\r�@��R�2\ZG;be�)�uO?s6s��a�_Dypc,�M �`�l�o8��-^�LcfLl�aX�\"��B$q�B��W�y]v�җ�Cdv+I�u�H媔L�ޜ���\r�DG��2��vb;rb<��?����9(�g��Ύ��U甎���u�\'�l֞��3u6��H�i�#(��RH��I�����=�Wz��q��P�̍������\rg\'Fo}ba\";x���=�%���d��Aq��g����2؀��m\r��Ŀ���Ǹ��nLл��DЖ��.��~N\"��!�_0��J��).\0�\n��>������|8M�?{`+��?��Ժ��ot�>�^�.#�a�(\0%�Aj�u=`��ʇe蠐W2�\'~(�&r2/Л�@�\Z{�]�cn��~�.�:]�\'�t�\\أc���s�����8ЍX�-1Ċ��g5�q\'�+�.!Ө`�^�ֿv�ٛG���¢{��Ki���ȿLuLz7�g>�����mwhCVX����c���y&r��(�Y�����	a�J�>m�YN����W�2����tU�U�wjX��X���<ւ����ߢ�u��M��6�q�����\"�����S���)�u~RN���)z7-��b!<�\'K)>9�B*e���K��\r~�U \n�-�.�BP���G�잧w8�U:�λ���y{��E�\Z=\0�+�P��N�a��A��|8={��0�N�L��-u�+]&��#\"�|]�q\":�\'�q��u�x�^J�~�m�����>\Zu?_X�׋\ZG?T��u�ǵFQ�\\�7�\r�a�4�/$�&�\r�9���3�v���Ȩb0��3Sd{>q�j��F\'[e� ��2��b��H�gɚ������k4=O�L	Y� k����o�ș�g�e��/���5��W�{z��ܔ�n\Z�f��\\�*9�N��.���պ6�[�\'�a*�}�l�m\r���p�[�1��(�Dж�\'�S-��ϝ�\rG�]�3��b��T�K*<�?�DzT����j�=��Qg��%�b]�gz�����t&濣�\0}��|M��Jv:c���O:��QG�\\�SϨ��)��H���ZmE0�괃Z�5��>t�$6:�#�Q+���,�j���U�0~��*��[�V,N:d����d�p<х7<�+��Ag�}Wׅ����>�]T2�F��Y�I�\Z�����^�Yo�~�R+Y=hwMr]�$ځX;�&wBO�.^�w�cl�|w[�������e��`��s�u�R`g�O��v��O+%��F����8<KI׌�޵ۮh�$�Ŏ�ʨ�#��\'� �Δ���eF��a9�Q�xI���C��b�v����$2��K�fȱͬ���X��|zۥ�c�m��q�.�V&�j�`�	~ ���I�܉�\\h�Mao|sG�*�d8�������׹$WC�)��{O�)Đe��A���c�A����I�t(�gG��*ߡ�b���9���Uu�\"������(��=�ᛋw툟�{w��|±4b4�A��!R	�\\��:��-«��mn����V��S#��=i��a9|�V�X�����ryĉtpe���%F<K�\r�W����g�s�U��M�8|���@�;x�2�Vo1cJ-!j��_� �����<xy�Ïo��b3	G���		\'��gy�\\���W�.�+K�O֓�NT����߁�ǀ�B�4A�����#��2��_��ݼ��Z�fؕ1��J�+%�Q�/h��wb鉑�4��H�>3��V�#b!^�\"�s������}�Sٛ-0z f(�h	9��s�yԎ�x�v����.�_ÿ|qhO]Z-BP|\\��9c�s�%�\\�����W�g&�{Ğ�`~ >�}�fP#�L*Ru�o�F|��<䨙35s\\�����PK`��u\n\0\0�c\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�=ے�6���+XJ�y�$Rw��l9{7U�O6�S	I\\S���F�l�e?cr�4@��\0Ii&N�*�\Z@�Ѹ5�����՞p:�w�3�Þ�=˷os����>�������k��K۷;�Ez�\\j���q�M�xK�N������Z�{��FKzI��Khg��)��:�ϑlc�j�V�#S`����lc<��}��ϡ��}��w{9,�]��r��F�~9���q�����X,Z� l%p�C�R(�\Z`���7v�#$��Q���Y�\"��j���ֈ�M	k�-\n�u���;���;�Ŷ;mKd2�C%����Y���X6�*+p��d��b{��TI��@)��p8Ŀ�c%�1p\"�V%��\\+ḿ+b\Z����QS�K{����(Ad-;fb^�h疛�堛��A��\0L\r]r��^�sV`�\0uCuM(��*��I�Drv��&q�k��0U0��=R�\\�l��!e�a8���������7��aӃ0%��[>��}�{��º�-7��>�I��&���~K�Pa���`;�=�������������$��{@%�q���;����\'�PGJ���g=<�V��T#���0�Q�l\\S����rl�}F^���90��R�`�%O��r��t\rZj�Q��	���\\\'B�A�\r�����ã����>� ���,�$�!�Ԡ� Yyr�m�F�E��g��&@��c�8,���pSA�4��������K�|���i�i-�-�������7�a�0��֟����:�IX��g�o����\'N-�6�U�OM�\0f�~s�E�2ι@�щ�z�F�AP�=\n���������d���~���61O��*���0��x\r��	n;�7=7УUJQ��d>%+����\Z�!Nd�肿�&�����s�B\\��lcw�~��AT���o��a�#Z�\"os@(�\\Z`���?�(!�t���[3ްx�o�ϼ�u�k<���.I����N�ڂn�:��q)ے1��ӽJ��lO�-��|��ȶq�Sl����I(�Ԭ����C�!�`�tP�z��*����G�G���li��C�V�@�ѕLAm��t�եxB���*��;�XQ�0wY-�9�����9��!�f@Z�C)��Ţ�ċ���\'�9��b���X\'wzp(�����z�op�%�jbgu������F��+u\\|.\nC@,��C�X�r)0��W������|�Yo�E�U���ĆU&��R��FP�\ZEx\0�?��Ǖo�x��T�.:�gM�.�(Hv�b=+R���3B愆���~�\\.�Z����6�z�(���*ƵC̍`�{\ZN�\'f�X�QD6L���|49σ��? 7%΄F���{D���*�c˚���E&�dn+�Ha�\Z7?��QlB3.�\"2�R���L̜�12ں	��j.���ٌ�ݚ�)��2t��f]�|�NA��紺	�G�<�:�ǫ	��\0�����0r�\'�EoC!,��oKKJW���	�.(�K��?��۸Zn�W\'���V����:��%�٠����	�ct���� ���T��)�ł�:	73 /�c\"\Z�C0-;G�v`u�D�*b�t)i�ى�P3���� bLT���=�ө�UF����xڟ��EW�,����������@��dޒp�ky{\n�ea:2�zQh��D|�O�t�d��5�I�����\"�S�Y��b�V�dy4�G�9W\ZP��w<�8 bI�1�$�[U���;.� Q�h��?\"�R�)����T��H���s��ϡ�uko��W�H8�n�k��Ă�v?�G�ݏ�-2�[Ud��Ae��T��X�r��,�����dܷ�6���4rв�7�ov�N�{XJ��pDq�˟�(�Ζ�ii�����+��\'��C|�&T<���\0������_�أ�KA�T���DM��j\'@�:_�,B�Ci\"H\'�I����N�u֑����dl����P�J�@KX��X:�·����<[�XCs�f�bg��tW�?qwN~�>����pM���F��F��hh�<k�z�.\'��͒�8r؅��7�-��@�]\'�c��c[Oz��*�\'��� �U�*~=H�U,��s9�>Ϙ,��`�Hq�iS�-�FZ���4�_���U��d�<�~���Oޔ�0�A���>8��\'�	��={��qM/��\'�2�8�\'{	f����׉ �^&kz����o�Q�u�\"��_d�ɋ�:}�Qg/2��EF]�Ȩư�ay7�p���wsXGl���[��y�[t�ޚ��dp��/<��L#�\"Z�jb�^w�8��\ZN���AN\n��bL��+��\rSc13��YF���vL���+��#h|Y�&���M.K�l>_���e	Z����4�$AFߘ��4�,A#й���,A����n;tO�lfv��j1� M!Yݯ��!����Bg��k�Ã\'�o�j6��<`d��TO�=W�\n�0�~>���9�6I�����kyZp��VEJ�6]�~²T\'c�!($���3)E8��W�/��W��z9�����9��\0dj�����O��V(��X:�([�?��]�yh��&b}���ٌ���_���i��Q��@��`�S�#��=��qacV�7��ܜ2CTA��Q����\0����p<[4@�sT��b25@�M�8�n(���1e��lk�G69Չr���?��5A|ţ�*>�&G㬜�^�Hc�R�y��v�-��\'�H��\'\Z����]3t�ض��yj,ƥL�שs��#�\\�k��<����? ���.��7ų�e�~Y�F�/�?���Kk�єŕ\r��y ��[b��x%����[���1�xB�\\����]xab\\`ab���X}ab\\`a2�M��SE�$��}BW��-��6��s�pj<���i��	Z��.\r^	�_:h5����_;l5���.K�vכ�r�U��e%]n��ܓ�p�}R0��e��2�vN��S���1n׏<����\Z�4�?�KI�4�\0{{b�oOʟ��OO��ۑ�w~�E�����)�Jޏ�O�rKd��\r�d�tY.������a��|X��nl��ws��0jzEF%�c�������� tl�K�Cc�X�b�ٻfaJx9o�ʹ\Z\'g/�rYB_�	�����&���h�ؘ\Z�\Z�d0����z���[W�%f���$9_�z��<	ͤ���e�.�^K\rϽ��z�_�J�YP�i#}>�W��d����h7-ZMsEj�ɮC�fի?�T�\\�����p�ъ$��Q�����u���f���Og���Wi�伛�ܐC��AEJ��;�c2�Ssb����e�,�����-�:\rn:7�/� ���I���?����%�8.�b�.M�|����\r��2p�?��C/ˮ�S�Ǘm���*���\Z�����h����>�v���g��!�uF_�bT�!^�5���z�y��C�c�k�#+�aF���\\���e����Õl+p.s�JC�����*C^\"̒�Tv��]�6���]��$<V���m|u���v��R_.&Rޣe)�K�݊���dzK�� ���!/��/Q�q�������Kg�]�Sm�V�`��3s]�x�]#9���4k��N�!J�`�3z@��%Z����t6J.�n��9�?]��91�(����C�_�Ҍ�A \'�Z:=O4�.HAo��4a�:~�7�*��[���Է�#6�s��5$�A�L!�QQ�\n�8�\nj,0I��\08�LJ\0c|�l��nV������\"�CE=K���Yb�z>c�ߤ%\0!���Ij��p&|.�S���!\r(�h1/\0Bk���F�.-���:H�cE|+�S��������x���,8�[z �������G|�_X\0eU!�}I����[b	�N`�$Y��L��[��$�mb�}�~����)(�\"I@\Zټ�$���ODW���u���k�ǫj^�~5�7(��$4d��5��`E\Z�����)�O�ߑJ�{�\n�쌗�]�R\Z��揈sA�#�2��y��SrL^�Z��^�\0���?����lG\0kڀ����F��&��$z\0���ǽ[_����㧂��/���ѱ�ƠZ��0�ݮ\\dkJ-��h2<!/b�]lETEnz�! ��[�u-���\r\ZcG�<��/��S�JIJ���*��KFD�*� �����;�v��i3� �p~�rE��\\wc��ߜ�ؕ�D\'�NؚT�����|�M�#v�lP!\\C��w{�\",�3%��������m���SQ�h��8�	���A��y�ǸLl�jRMY��R����X�\r~Pm�V��]�A�3=`׭ ��-K�	�}�#�H��^����Ie�\Z�=K�P�rưw�)��	��X�~�D&Q�����\r�\Z0��GU5���g��0�[�+y7F&q}+��B�����,.z|��1�2��92��t>4�9	�U�#1Z��|�2�bϖ�#��m�3����Q�J��cJ�XRI�Q{�3;S�5Te�fU\0Aߥ�9�\Z?c���V\0\'+��I��X):1 ���s���{�G�=zVcJ�Pէ�\Z=+�f1�)��d��w�ܛ��5&��O����Q\Z\ZJ���\Z\Z�X<E9t�\n�f���4j8��v�\'�4;��Xv┍�ѿ7k�]�\0���2��2�ՆX@�gD�=K>ʪ�t:p�I��-.�Z˪9.�#���8j��\\�Ph�����֙����m�.Fú ��X��Ro �.8�C���(RٙA�c��U*P��������t�-U�\0����`~��D7��x:��p�r!�{\Z���<���1@��@�)���EuY7��P��H��I��۾u�%�����PK6��M)\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�Z�n#I��)�2�����d�v����m;1pQ�.۝TW5�ձ�ԛٕvaY@	��]v��Gp�Ό4��~�~�=�v���&�vI�\"N�U��9u�w�S�Ϟ�M�t�m�`t+{�,a�1ݠ��HM�.?�<���3�n\ZN�LsML���9�!�L�Nr�x+��4ɐc8I�L�$��d��Ӓ�G\'Ce�o�Ġ�[�.�Vre���=�>avg%�H$V§�C5F�F籪F�o�b��(F�	�ţѵ������\"o�&پ�õ����\nF�Ǧ����k����L��w�ȤyߝS�񊍑ʬ��>��	a�َ>[�/��bs��e�m:�N��4\Z_�O�6:��ˎ�WWWg�^��^�c8�E���;\ZZ��hd��.�M�>M٬��<��4�mD�G�_6��lP��~�Y�,��a��}��Rn��\"��o��؋E�7�g�;-U�6�g<�h��d	�.<�C���9\"2p�me��̜���ل73Uu7ں��sR\r��ӌ�&��ԋ��b�taY}�/Y�qfO^{,:����*&X�X����K���mt��xX�@q||9}�ڈCq�_�j	 �+�2N��u�EF(��:8��ӎ�\\zEPNB%*��A\Z�2��1��\0�����P&KCƴ�@�LB�m���]�!�J5\0Y���̬b��-\Z3#� {�`D�la�:p�A�L�0�ibX�K��]{�F��$ܩ,����QZ��B`[��,a�sU��Ldw�{%rZj���4T�Bؒ�	7��9xc-ePd\"�\'��OVb�n�k�\Z/G��95�/�+��P�~����c�r��R��s��Ns�B�����U����f��¡\rE�+vg<��d�[��2:���	����W�~�����h�h?Q�g��Zw��؎����yf5���ͣ�y�?l��\'�=>�X��Z����{]tT><�\'z�_%5s�jd�8q���_�G�h�Qw��h/��8�t��6g-��ܭ���N\"R>��\nN�IZ0�t�4+D7�IS��o���F��T尖Me�����B[�V����Z�l4U+U�vR;j4V.�+�J�^V3��h�T�ԊJ�R�e��F6QWOyQ���T�2g\r��9����P�OoB|�\n,� ��a�LcBD,^M�����p�,��9�t)樨�p�W�\\:�=Y]g��GZ��&5\'���\r�� @V��43Mt����Ǭ�qX+��n׵8����R琯\nد��!�2H��\0�+�i\"�H����Q^���	�gж�bv�I��\n���%���`�S��XJ0)ǐ^��f��xL�|�,$��؍:�C|όۅ��Vsp��c&�ǐ������t��R�t��:�ޢ���\\���6�MXO��0t+\Zq��\n綰\n���2x��(~����2��!�ZH�*MT_ S4�0v�]����Dbs.j,@K܏\0r�N���B|,Cň+qF���=`�4�\Z&t|�����h`�B�.$�ްa��%��lKJO�ă\\o\r��8\r��M aj�+�C�m�ZYM��Vy��������S&�@%�*��I!jnt�R��Is$�w����2����>��V��%������8���E�����J�\rLtGf\n����\"M�� a�ۅ	�!a��H�\"A��.LZ�}K�D�\0wuJ��r ���C���Ci�e����b�\"�@�;|9��AqDK.ո+�����m�2�i����*�\"L��xl��7=�=wю{���&C\'h�����H=(�x�l�)��P��Ћ��2e]����$����ͻ/�����ە��ʃ?�}���������]���������~�{�?��_����ŧ��+��3�����K�ś`�^0� ~?��ÿ×���`x\\�\n.�.�\n.�\\��|��<x������Y?�����w_�{}�������>�����Ǿ���������ï�˗����/����\\|>9��7�T\"��7IV\n1:��*g�5a�tTr�:�h��;\r�w󈺈�ą��ʃ�����8�\"�qig9�;Z^�mF���M�3��h7%�\0P�X؞�7��sY�W9h]��<���\'�\r����8�=����V����9v��K�9c6��Ӄ�1� l��KdxvB���bYd\0]�-���}-��Q:M�d�RS߆Y����ʴq��PK���{\0\0�+\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0meta.xml��Mo�0���+,�W0&�4VB�=��U�[�Y��*Ǟ���FƄ�߯��z�������;�����X��Jj��Hz�B�l���~�����Z�����GP�?�eȥ�����WE5�dE;BE-��5��9M�F��^H�o��֖�i�fh�a�Z�pQ�\'��M�Q�c(��Pa<���kM��ܒ�zj���]�1��G:3B�\r��v�e�IB��C����G^:n����;3(0�j�>ɝ�_�� � �y��~�{w�v��V\Z}\0nq����[-�Gk|Qr-8���>H0;y@�@�Ɲj�Ka�-�ڢ,*\\и��)$\0�t�J�C��x��ۖJZ�\n��:=2����{>��8�<��\\�}��έ�w[��ݠ�.�ܒ%�\Z�C�9�N4$���$ْ��	���dOu����i⇱�-!4�(I��f\\[�`*�Y�*��O]�I��p�����*%q<T��Ӣ6� ��v������׋������>�G0�=��\\����x�{\\�3yP��n���Owe]��J�:ݲ]>׵�o���<��G1D�k߾K���d��+��X��F���vy�xn����h5��{�\\Z�J�]�K2q$&�+��Kb�v���C�5\"�a�u��:,n1|�������PK�1�6�\0\0�\0\0PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0manifest.rdf͓�n�0D�|�e�`���9�\\�_�\Z�X/�.%�}]\'���*͡�]�f�h���8�C;4`k��g�*���|�>y��&ڸ��^��j���j~ �*!�eI���^�eY��E�xE��%yL,Ƽ��F��D>�}��\rf�y�%�N:�9�0;���:P��D�	LچL���(-��&)��}܂�Gm��-���c1����su����_5R`�����\"\"���?^v��}��㧓���F��z��{\r�?��V�G5�\'PK�=��\0\0\0�\0\0PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/toolpanel/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml�T�n� ��+,���ͩBqr��/H?�ⵃ�%���8jU�*V}����6����b2�Y�}k�o����~e��j���KP�9L״a9��*�$Q9H�����:;@�?��t����vU��:c�.�q���lm\Z&�Hne�Q5\r\Z�B�F+*0qĖ�\r�{���DL��?d����$�����Tb��R�i�W�q�xt.��,�D���<-�Z����I�k<���SPO�5�<v���L��Bi\rJ��9ƿ/�Z>��q������a߈_��PK�\\�J\Z\0\0>\0\0PK\0\0\0\0\0�`�B^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0�`�B,��S�!\0\0�!\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Thumbnails/thumbnail.pngPK\0\0\0�`�B`��u\n\0\0�c\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0m\"\0\0content.xmlPK\0\0\0�`�B6��M)\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0styles.xmlPK\0\0\0�`�B���{\0\0�+\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0|@\0\0settings.xmlPK\0\0\0�`�B�1�6�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\01I\0\0meta.xmlPK\0\0\0�`�B�=��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�K\0\0manifest.rdfPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0%M\0\0Configurations2/popupmenu/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0]M\0\0Configurations2/images/Bitmaps/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/progressbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�M\0\0Configurations2/toolbar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\nN\0\0Configurations2/toolpanel/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0BN\0\0Configurations2/floater/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0xN\0\0Configurations2/menubar/PK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0�`�B\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0�N\0\0Configurations2/statusbar/PK\0\0\0�`�B�\\�J\Z\0\0>\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0+O\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0p\0\0�P\0\0\0\0','odt');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expense_books`
--

LOCK TABLES `bs_expense_books` WRITE;
/*!40000 ALTER TABLE `bs_expense_books` DISABLE KEYS */;
INSERT INTO `bs_expense_books` VALUES (1,1,134,'Expesnse','€',21);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expense_categories`
--

LOCK TABLES `bs_expense_categories` WRITE;
/*!40000 ALTER TABLE `bs_expense_categories` DISABLE KEYS */;
INSERT INTO `bs_expense_categories` VALUES (1,1,'Internet');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_expenses`
--

LOCK TABLES `bs_expenses` WRITE;
/*!40000 ALTER TABLE `bs_expenses` DISABLE KEYS */;
INSERT INTO `bs_expenses` VALUES (1,1,1,0,'',0,1571047599,1571047664,1571004000,1571004000,57851.24,12148.76,0);
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
INSERT INTO `bs_order_payments` VALUES (1,1,1561972062,20999.95,'Status: Sent'),(2,2,1561972063,38999.89,'Status: Sent'),(3,3,1561972063,20999.95,'Status: Waiting for payment'),(4,4,1561972064,38999.89,'Status: Waiting for payment'),(5,5,1561972064,20999.95,'Status: Waiting for payment'),(6,6,1561972064,38999.89,'Status: Waiting for payment');
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
INSERT INTO `bs_order_status_history` VALUES (1,1,1,1,1561972062,0,'billing/notifications/1/201907/1/1561972062.eml',NULL),(2,2,1,1,1561972063,0,'billing/notifications/1/201907/2/1561972063.eml',NULL),(3,3,5,1,1561972063,0,'billing/notifications/2/201907/3/1561972063.eml',NULL),(4,4,5,1,1561972064,0,'billing/notifications/2/201907/4/1561972064.eml',NULL),(5,5,9,1,1561972064,0,'billing/notifications/3/201907/5/1561972064.eml',NULL),(6,6,9,1,1561972064,0,'billing/notifications/3/201907/6/1561972064.eml',NULL);
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
INSERT INTO `bs_order_statuses` VALUES (1,1,0,0,0,0,'FFFFFF',0,23,0,NULL,NULL,'',0,1),(2,1,0,0,0,0,'FFFFFF',0,24,0,NULL,NULL,'',0,1),(3,1,0,0,0,0,'FFFFFF',0,25,0,NULL,NULL,'',0,1),(4,1,0,0,0,0,'FFFFFF',0,26,0,NULL,NULL,'',0,1),(5,2,0,0,0,0,'FFFFFF',0,28,0,NULL,NULL,'',0,1),(6,2,0,0,0,0,'FFFFFF',0,29,0,NULL,NULL,'',0,1),(7,2,0,0,0,0,'FFFFFF',0,30,0,NULL,NULL,'',0,1),(8,2,0,0,0,0,'FFFFFF',0,31,0,NULL,NULL,'',0,1),(9,3,0,0,0,0,'FFFFFF',0,33,0,NULL,NULL,'',0,1),(10,3,0,0,0,0,'FFFFFF',0,34,0,NULL,NULL,'',0,1),(11,3,0,0,0,0,'FFFFFF',0,35,0,NULL,NULL,'',0,1),(12,3,0,0,0,0,'FFFFFF',0,36,0,NULL,NULL,'',0,1);
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
  `company_id` int(11) NOT NULL DEFAULT 0,
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
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_orders`
--

LOCK TABLES `bs_orders` WRITE;
/*!40000 ALTER TABLE `bs_orders` DISABLE KEYS */;
INSERT INTO `bs_orders` VALUES (1,0,1,1,1,1,'Q19000001','',1,1,1561972062,1561972062,1,1561972062,1561972062,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms',NULL,'Kalverstraat','1','1012 NX','Amsterdam',NULL,'NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(2,0,1,1,1,1,'Q19000002','',2,2,1561972062,1561972063,1,1561972062,1561972063,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(3,0,5,2,1,1,'O19000001','',1,1,1561972063,1561972063,1,1561972063,1561972063,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms',NULL,'Kalverstraat','1','1012 NX','Amsterdam',NULL,'NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(4,0,5,2,1,1,'O19000002','',2,2,1561972063,1561972064,1,1561972063,1561972064,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(5,0,9,3,1,1,'I19000001','',1,1,1561972064,1562249995,1,1561932000,1561932000,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms','','Kalverstraat','1','1012 NX','Amsterdam','','NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,'',0,0,20999.95,1562191200,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(6,0,9,3,1,1,'I19000002','',2,2,1561972064,1561972064,1,1561972064,1561972064,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0);
/*!40000 ALTER TABLE `bs_orders` ENABLE KEYS */;
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
INSERT INTO `bs_products` VALUES (1,0,0,'',1000,2999.99,0,2999.99,2,NULL,0,'',0,'12345','pcs','',0,NULL,NULL),(2,0,0,'',3000,8999.99,0,8999.99,2,NULL,0,'',0,'234567','pcs','',0,NULL,NULL);
/*!40000 ALTER TABLE `bs_products` ENABLE KEYS */;
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
INSERT INTO `bs_templates` VALUES (1,'Quotes',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,1,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(2,'Orders',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,2,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(3,'Invoices','','Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}\n\nDue: {due_date}',30,30,30,30,'','','Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,3,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendars`
--

LOCK TABLES `cal_calendars` WRITE;
/*!40000 ALTER TABLE `cal_calendars` DISABLE KEYS */;
INSERT INTO `cal_calendars` VALUES (1,1,1,70,'System Administrator',0,0,NULL,1800,0,0,0,1,'',0,0,10,1,0,'','',4),(2,1,2,90,'Elmer Fudd',0,0,NULL,1800,0,0,0,1,'',0,0,22,1,0,'','',4),(3,1,3,95,'Demo User',0,0,NULL,1800,0,0,0,1,'',0,0,27,1,0,'','',10),(4,1,4,100,'Linda Smith',0,0,NULL,1800,0,0,0,1,'',0,0,31,1,0,'','',10),(5,2,1,104,'Road Runner Room',0,0,NULL,1800,0,0,0,1,'',0,0,33,1,0,'','',1),(6,2,1,105,'Don Coyote Room',0,0,NULL,1800,0,0,0,1,'',0,0,34,1,0,'','',1),(8,1,6,129,'foo',0,0,NULL,1800,0,0,0,1,'',0,0,42,1,0,'','',1);
/*!40000 ALTER TABLE `cal_calendars` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events`
--

LOCK TABLES `cal_events` WRITE;
/*!40000 ALTER TABLE `cal_events` DISABLE KEYS */;
INSERT INTO `cal_events` VALUES (1,'08daba0e-9ef3-59c1-933a-03ca01a8ad22',3,3,1562054400,1562058000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1561972058,1561972058,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(2,'08daba0e-9ef3-59c1-933a-03ca01a8ad22',4,4,1562054400,1562058000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1561972058,1561972058,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(3,'08daba0e-9ef3-59c1-933a-03ca01a8ad22',2,2,1562054400,1562058000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1561972058,1561972058,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(4,'6c4dbd0f-a214-59e2-851a-aa287656176e',3,3,1562061600,1562065200,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(5,'6c4dbd0f-a214-59e2-851a-aa287656176e',4,4,1562061600,1562065200,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(6,'6c4dbd0f-a214-59e2-851a-aa287656176e',2,2,1562061600,1562065200,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(7,'4d87f73c-7bf0-5c90-b450-2689c963888e',3,3,1562068800,1562072400,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(8,'4d87f73c-7bf0-5c90-b450-2689c963888e',4,4,1562068800,1562072400,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(9,'4d87f73c-7bf0-5c90-b450-2689c963888e',2,2,1562068800,1562072400,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(10,'ccc9622a-e00d-5643-99f2-7c2dc88494b1',4,4,1562144400,1562148000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1561972059,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(11,'ccc9622a-e00d-5643-99f2-7c2dc88494b1',3,3,1562144400,1562148000,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1561972060,1561972059,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(12,'71d09bf8-00e9-5c29-9889-b4db5de35525',4,4,1562151600,1562155200,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(13,'71d09bf8-00e9-5c29-9889-b4db5de35525',3,3,1562151600,1562155200,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(14,'379d9f35-23dd-5c09-8da6-2c1ae967386c',4,4,1562162400,1562166000,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(15,'379d9f35-23dd-5c09-8da6-2c1ae967386c',3,3,1562162400,1562166000,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(16,'20ef5123-708b-590e-9d95-6c963b0accb4',4,4,1562047200,1562050800,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(17,'20ef5123-708b-590e-9d95-6c963b0accb4',3,3,1562047200,1562050800,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(18,'f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa',4,4,1562072400,1562076000,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(19,'f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa',3,3,1562072400,1562076000,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1561972060,1561972060,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(20,'0212980f-bfec-55a8-8fba-b3232a097705',4,4,1562086800,1562090400,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1561972061,1561972061,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(21,'0212980f-bfec-55a8-8fba-b3232a097705',3,3,1562086800,1562090400,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1561972061,1561972061,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(22,'d4153d3b-0988-59af-b3f9-d2129564ef76',1,1,1572537600,1572541200,'Europe/Amsterdam',0,'mnbmhb','','',0,NULL,1572537446,1572610878,1,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,0,'',1);
/*!40000 ALTER TABLE `cal_events` ENABLE KEYS */;
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
INSERT INTO `cal_participants` VALUES (1,1,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(2,1,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(3,2,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(4,2,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(5,1,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(6,3,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(7,3,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(8,3,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(9,2,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(10,1,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(11,2,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(12,3,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(13,4,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(14,4,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(15,5,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(16,5,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(17,4,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(18,6,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(19,6,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(20,6,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(21,5,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(22,4,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(23,5,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(24,6,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(25,7,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(26,7,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(27,8,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(28,8,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(29,7,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(30,9,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(31,9,'Demo User','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(32,9,'Linda Smith','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(33,8,'Elmer Fudd','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(34,7,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(35,8,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(36,9,'Wile E. Coyote','wile@acme.demo',0,2,'NEEDS-ACTION','',0,''),(37,10,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(38,10,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(39,11,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(40,11,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(41,10,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(42,11,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(43,12,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(44,12,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(45,13,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(46,13,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(47,12,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(48,13,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(49,14,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(50,14,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(51,15,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(52,15,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(53,14,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(54,15,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(55,16,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(56,16,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(57,17,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(58,17,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(59,16,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(60,17,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(61,18,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(62,18,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(63,19,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(64,19,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(65,18,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(66,19,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(67,20,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(68,20,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(69,21,'Demo User','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(70,21,'Linda Smith','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(71,20,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,''),(72,21,'John Smith','john@smith.demo',0,1,'NEEDS-ACTION','',0,'');
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
INSERT INTO `cal_settings` VALUES (1,NULL,'EBF1E2',1,1,1),(2,NULL,'EBF1E2',2,1,1),(3,NULL,'EBF1E2',3,1,1),(4,NULL,'EBF1E2',4,1,1),(6,NULL,'EBF1E2',8,1,1);
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
INSERT INTO `cal_views` VALUES (1,1,'Everyone',1800,102,0,1),(2,1,'Everyone (Merge)',1800,103,1,1);
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
-- Table structure for table `cf_ab_companies`
--

DROP TABLE IF EXISTS `cf_ab_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_ab_companies` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Company` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Contact` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `File` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Checkbox` tinyint(1) NOT NULL DEFAULT 0,
  `Number` double DEFAULT NULL,
  `User` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `HTML` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Infotext` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Heading` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Select` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Treeselect` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Textarea` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Function` double DEFAULT NULL,
  `Text` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ab_companies`
--

LOCK TABLES `cf_ab_companies` WRITE;
/*!40000 ALTER TABLE `cf_ab_companies` DISABLE KEYS */;
INSERT INTO `cf_ab_companies` VALUES (1,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,''),(2,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,''),(3,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,'');
/*!40000 ALTER TABLE `cf_ab_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_ab_contacts`
--

DROP TABLE IF EXISTS `cf_ab_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_ab_contacts` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Company` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Contact` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `File` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Checkbox` tinyint(1) NOT NULL DEFAULT 0,
  `Number` double DEFAULT NULL,
  `User` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `HTML` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Infotext` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Heading` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Select` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Treeselect` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Textarea` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Function` double DEFAULT NULL,
  `Text` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Treeselect1` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Treeselect2` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `multiselect` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ab_contacts`
--

LOCK TABLES `cf_ab_contacts` WRITE;
/*!40000 ALTER TABLE `cf_ab_contacts` DISABLE KEYS */;
INSERT INTO `cf_ab_contacts` VALUES (1,'','','',0,NULL,'','','','','Option 1','1:O 2','',NULL,0,'','3:O 2.1','','Option 1|Option 4'),(2,'','','',0,NULL,'','','','','Option 2','2:O 1','',NULL,0,'','6:O 1.2','7:O 1.2.3',NULL),(3,'','','',0,NULL,'','','','','Option 3','','',NULL,0,'','','','Option 1|Option 2|Option 3|Option 4'),(4,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,'','','',NULL),(5,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,'','','',NULL),(6,'','','',0,NULL,'',NULL,'','','','',NULL,NULL,NULL,'','','',NULL),(7,'','','',0,NULL,'','','','','','','',NULL,0,'','','',NULL),(8,'','','',0,NULL,'','','','','','','',NULL,0,'','','','');
/*!40000 ALTER TABLE `cf_ab_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_blocks`
--

DROP TABLE IF EXISTS `cf_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_blocks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `field_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_blocks`
--

LOCK TABLES `cf_blocks` WRITE;
/*!40000 ALTER TABLE `cf_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_bs_orders`
--

DROP TABLE IF EXISTS `cf_bs_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_bs_orders` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_bs_orders`
--

LOCK TABLES `cf_bs_orders` WRITE;
/*!40000 ALTER TABLE `cf_bs_orders` DISABLE KEYS */;
INSERT INTO `cf_bs_orders` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,'');
/*!40000 ALTER TABLE `cf_bs_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_bs_products`
--

DROP TABLE IF EXISTS `cf_bs_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_bs_products` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_bs_products`
--

LOCK TABLES `cf_bs_products` WRITE;
/*!40000 ALTER TABLE `cf_bs_products` DISABLE KEYS */;
INSERT INTO `cf_bs_products` VALUES (1,''),(2,'');
/*!40000 ALTER TABLE `cf_bs_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_cal_calendars`
--

DROP TABLE IF EXISTS `cf_cal_calendars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_cal_calendars` (
  `model_id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_cal_calendars`
--

LOCK TABLES `cf_cal_calendars` WRITE;
/*!40000 ALTER TABLE `cf_cal_calendars` DISABLE KEYS */;
INSERT INTO `cf_cal_calendars` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,''),(8,'');
/*!40000 ALTER TABLE `cf_cal_calendars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_cal_events`
--

DROP TABLE IF EXISTS `cf_cal_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_cal_events` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_cal_events`
--

LOCK TABLES `cf_cal_events` WRITE;
/*!40000 ALTER TABLE `cf_cal_events` DISABLE KEYS */;
INSERT INTO `cf_cal_events` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,''),(7,''),(8,''),(9,''),(10,''),(11,''),(12,''),(13,''),(14,''),(15,''),(16,''),(17,''),(18,''),(19,''),(20,''),(21,''),(22,'');
/*!40000 ALTER TABLE `cf_cal_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_categories`
--

DROP TABLE IF EXISTS `cf_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extends_model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_index` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type` (`extends_model`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_categories`
--

LOCK TABLES `cf_categories` WRITE;
/*!40000 ALTER TABLE `cf_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_disable_categories`
--

DROP TABLE IF EXISTS `cf_disable_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_disable_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_disable_categories`
--

LOCK TABLES `cf_disable_categories` WRITE;
/*!40000 ALTER TABLE `cf_disable_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_disable_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_enabled_blocks`
--

DROP TABLE IF EXISTS `cf_enabled_blocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_enabled_blocks` (
  `block_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`block_id`,`model_id`,`model_type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_enabled_blocks`
--

LOCK TABLES `cf_enabled_blocks` WRITE;
/*!40000 ALTER TABLE `cf_enabled_blocks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_enabled_blocks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_enabled_categories`
--

DROP TABLE IF EXISTS `cf_enabled_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_enabled_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_enabled_categories`
--

LOCK TABLES `cf_enabled_categories` WRITE;
/*!40000 ALTER TABLE `cf_enabled_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_enabled_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_fields`
--

DROP TABLE IF EXISTS `cf_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datatype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text',
  `sort_index` int(11) NOT NULL DEFAULT 0,
  `function` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `validation_regex` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `helptext` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `multiselect` tinyint(1) NOT NULL DEFAULT 0,
  `max` int(11) NOT NULL DEFAULT 0,
  `nesting_level` tinyint(4) NOT NULL DEFAULT 0,
  `treemaster_field_id` int(11) NOT NULL DEFAULT 0,
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT 0,
  `height` int(11) NOT NULL DEFAULT 0,
  `number_decimals` tinyint(4) NOT NULL DEFAULT 2,
  `unique_values` tinyint(1) NOT NULL DEFAULT 0,
  `max_length` int(5) NOT NULL DEFAULT 50,
  `addressbook_ids` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extra_options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fields`
--

LOCK TABLES `cf_fields` WRITE;
/*!40000 ALTER TABLE `cf_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_fs_files`
--

DROP TABLE IF EXISTS `cf_fs_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_fs_files` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fs_files`
--

LOCK TABLES `cf_fs_files` WRITE;
/*!40000 ALTER TABLE `cf_fs_files` DISABLE KEYS */;
INSERT INTO `cf_fs_files` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,''),(7,''),(8,''),(9,''),(10,'');
/*!40000 ALTER TABLE `cf_fs_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_fs_folders`
--

DROP TABLE IF EXISTS `cf_fs_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_fs_folders` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fs_folders`
--

LOCK TABLES `cf_fs_folders` WRITE;
/*!40000 ALTER TABLE `cf_fs_folders` DISABLE KEYS */;
INSERT INTO `cf_fs_folders` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,''),(7,''),(8,''),(9,''),(10,''),(11,''),(12,''),(13,''),(14,''),(15,''),(16,''),(17,''),(18,''),(19,''),(20,''),(21,''),(22,''),(23,''),(24,''),(25,''),(26,''),(27,''),(28,''),(29,''),(30,''),(31,''),(32,''),(33,''),(34,''),(35,''),(36,''),(37,''),(38,''),(39,''),(42,''),(43,''),(44,''),(45,''),(46,''),(47,''),(48,'');
/*!40000 ALTER TABLE `cf_fs_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_pr2_hours`
--

DROP TABLE IF EXISTS `cf_pr2_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_pr2_hours` (
  `model_id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_pr2_hours`
--

LOCK TABLES `cf_pr2_hours` WRITE;
/*!40000 ALTER TABLE `cf_pr2_hours` DISABLE KEYS */;
INSERT INTO `cf_pr2_hours` VALUES (1,'');
/*!40000 ALTER TABLE `cf_pr2_hours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_pr2_projects`
--

DROP TABLE IF EXISTS `cf_pr2_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_pr2_projects` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_pr2_projects`
--

LOCK TABLES `cf_pr2_projects` WRITE;
/*!40000 ALTER TABLE `cf_pr2_projects` DISABLE KEYS */;
INSERT INTO `cf_pr2_projects` VALUES (1,''),(2,''),(3,''),(4,''),(5,'');
/*!40000 ALTER TABLE `cf_pr2_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_select_options`
--

DROP TABLE IF EXISTS `cf_select_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_select_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_id` int(11) NOT NULL DEFAULT 0,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_select_options`
--

LOCK TABLES `cf_select_options` WRITE;
/*!40000 ALTER TABLE `cf_select_options` DISABLE KEYS */;
INSERT INTO `cf_select_options` VALUES (1,25,'Option 1',0),(3,25,'Option 3',1),(4,43,'Option 1',0),(5,43,'Option 2',1),(7,43,'Option 4',2);
/*!40000 ALTER TABLE `cf_select_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_select_tree_options`
--

DROP TABLE IF EXISTS `cf_select_tree_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_select_tree_options` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_select_tree_options`
--

LOCK TABLES `cf_select_tree_options` WRITE;
/*!40000 ALTER TABLE `cf_select_tree_options` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_select_tree_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_site_content`
--

DROP TABLE IF EXISTS `cf_site_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_site_content` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_site_content`
--

LOCK TABLES `cf_site_content` WRITE;
/*!40000 ALTER TABLE `cf_site_content` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_site_content` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_site_sites`
--

DROP TABLE IF EXISTS `cf_site_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_site_sites` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_site_sites`
--

LOCK TABLES `cf_site_sites` WRITE;
/*!40000 ALTER TABLE `cf_site_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_site_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_ta_tasks`
--

DROP TABLE IF EXISTS `cf_ta_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_ta_tasks` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ta_tasks`
--

LOCK TABLES `cf_ta_tasks` WRITE;
/*!40000 ALTER TABLE `cf_ta_tasks` DISABLE KEYS */;
INSERT INTO `cf_ta_tasks` VALUES (1,''),(2,''),(3,''),(4,''),(5,''),(6,''),(7,''),(8,''),(9,''),(10,''),(11,''),(12,''),(13,'');
/*!40000 ALTER TABLE `cf_ta_tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_ti_tickets`
--

DROP TABLE IF EXISTS `cf_ti_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_ti_tickets` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ti_tickets`
--

LOCK TABLES `cf_ti_tickets` WRITE;
/*!40000 ALTER TABLE `cf_ti_tickets` DISABLE KEYS */;
INSERT INTO `cf_ti_tickets` VALUES (1,''),(2,'');
/*!40000 ALTER TABLE `cf_ti_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_tree_select_options`
--

DROP TABLE IF EXISTS `cf_tree_select_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_tree_select_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_tree_select_options`
--

LOCK TABLES `cf_tree_select_options` WRITE;
/*!40000 ALTER TABLE `cf_tree_select_options` DISABLE KEYS */;
INSERT INTO `cf_tree_select_options` VALUES (1,0,26,'O 2',0),(2,0,26,'O 1',0),(4,1,26,'O 2.2',0),(5,2,26,'O 1.1',0),(6,2,26,'O 1.2',0),(7,6,26,'O 1.2.3',0);
/*!40000 ALTER TABLE `cf_tree_select_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `co_categories`
--

DROP TABLE IF EXISTS `co_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `co_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `co_categories`
--

LOCK TABLES `co_categories` WRITE;
/*!40000 ALTER TABLE `co_categories` DISABLE KEYS */;
INSERT INTO `co_categories` VALUES (1,'Blauw'),(2,'Groen');
/*!40000 ALTER TABLE `co_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `co_comments`
--

DROP TABLE IF EXISTS `co_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `co_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `comments` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `link_id` (`model_id`,`model_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `co_comments`
--

LOCK TABLES `co_comments` WRITE;
/*!40000 ALTER TABLE `co_comments` DISABLE KEYS */;
INSERT INTO `co_comments` VALUES (1,2,15,1,1561972054,1561972054,'The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate which produces every product type imaginable, no matter how elaborate or extravagant - none of which ever work as desired or expected. In the Road Runner cartoon Beep, Beep, it was referred to as \"Acme Rocket-Powered Products, Inc.\" based in Fairfield, New Jersey. Many of its products appear to be produced specifically for Wile E. Coyote; for example, the Acme Giant Rubber Band, subtitled \"(For Tripping Road Runners)\".',0),(2,2,15,1,1561972054,1561972054,'Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat, which had been used on Elmer Fudd and Sam Sheepdog (which doesn\'t maul its intended victim); and Acme Bumblebees in one-fifth bottles (which sting Wile E. Coyote). The Wild Cat was used in the shorts Don\'t Give Up the Sheep and A Mutt in a Rut, while the bees were used in the short Zoom and Bored.',0),(3,2,16,1,1561972054,1561972054,'Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',0),(4,2,16,1,1561972054,1561972054,'In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',0),(5,1,17,1,1561972062,1561972062,'Scheduled call at 04-07-2019 11:07',0),(6,2,17,1,1561972063,1561972063,'Scheduled call at 04-07-2019 11:07',0),(7,1,16,1,1563449408,1563449408,'Dit is een test',1),(8,2,15,1,1563449426,1563449426,'Test bij een bedrijf',2);
/*!40000 ALTER TABLE `co_comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_acl`
--

DROP TABLE IF EXISTS `core_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_acl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ownedBy` int(11) NOT NULL,
  `usedIn` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl`
--

LOCK TABLES `core_acl` WRITE;
/*!40000 ALTER TABLE `core_acl` DISABLE KEYS */;
INSERT INTO `core_acl` VALUES (1,1,'core_group.aclId','2019-07-01 09:06:23'),(2,1,'core_group.aclId','2019-07-01 09:07:37'),(3,1,'core_group.aclId','2019-07-01 09:07:38'),(4,1,'core_group.aclId','2019-07-01 09:06:23'),(5,1,'core_module.aclId','2019-07-01 09:06:23'),(6,1,'core_module.aclId','2019-07-01 09:06:23'),(7,1,'core_module.aclId','2019-07-01 09:06:24'),(8,1,'core_module.aclId','2019-07-01 09:06:24'),(9,1,'core_module.aclId','2019-07-01 09:06:24'),(10,1,'core_module.aclId','2019-07-01 09:06:24'),(11,1,'core_module.aclId','2019-07-01 09:06:24'),(12,1,'core_module.aclId','2019-07-01 09:07:38'),(13,1,'core_module.aclId','2019-07-01 09:07:38'),(14,1,'core_module.aclId','2019-07-01 09:07:38'),(15,1,'ab_addressbooks.acl_id','2019-07-04 07:41:23'),(16,1,'ab_addressbooks.acl_id','2019-07-01 09:07:38'),(17,1,'ab_addressbooks.acl_id','2019-07-01 09:07:38'),(18,1,'ab_email_templates.acl_id','2019-07-01 09:07:38'),(19,1,'ab_email_templates.acl_id','2019-07-01 09:07:38'),(20,1,'core_module.aclId','2019-07-01 09:07:38'),(21,1,'core_module.aclId','2019-07-01 09:07:38'),(22,1,'bs_books.acl_id','2019-07-01 09:07:42'),(23,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(24,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(25,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(26,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(27,1,'bs_books.acl_id','2019-07-01 09:07:43'),(28,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(29,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(30,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(31,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(32,1,'bs_books.acl_id','2019-07-01 09:07:44'),(33,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(34,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(35,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(36,1,'bs_order_statuses.acl_id','2019-07-01 09:07:37'),(37,1,'core_module.aclId','2019-07-01 09:07:38'),(38,1,'bm_categories.acl_id','2019-07-01 09:07:38'),(39,1,'core_module.aclId','2019-07-01 09:07:38'),(40,1,'core_module.aclId','2019-07-01 09:07:38'),(41,1,'core_module.aclId','2019-07-01 09:06:27'),(43,1,'core_module.aclId','2019-07-01 09:07:38'),(44,1,'core_module.aclId','2019-07-01 09:07:38'),(45,1,'core_module.aclId','2019-07-01 09:07:38'),(46,1,'fs_templates.acl_id','2019-07-01 09:07:38'),(47,1,'fs_templates.acl_id','2019-07-01 09:07:38'),(48,1,'core_module.aclId','2019-07-01 09:07:38'),(49,1,'core_module.aclId','2019-07-01 09:07:38'),(50,1,'core_module.aclId','2019-07-01 09:07:38'),(51,1,'core_module.aclId','2019-07-01 09:07:38'),(52,1,'pr2_types.acl_id','2019-07-01 09:06:29'),(53,1,'pr2_types.acl_book','2019-07-01 09:06:29'),(54,1,'pr2_statuses.acl_id','2019-07-01 09:06:29'),(55,1,'pr2_statuses.acl_id','2019-07-01 09:06:29'),(56,1,'pr2_statuses.acl_id','2019-07-01 09:06:29'),(57,1,'pr2_templates.acl_id','2019-07-01 09:07:37'),(58,1,'pr2_templates.acl_id','2019-07-01 09:07:37'),(59,1,'core_module.aclId','2019-07-01 09:07:38'),(60,1,'core_module.aclId','2019-07-01 09:07:38'),(61,1,'core_module.aclId','2019-07-01 09:07:38'),(62,1,'core_module.aclId','2019-07-01 09:07:38'),(63,1,'core_module.aclId','2019-07-01 09:07:38'),(64,1,'core_module.aclId','2019-07-01 09:07:38'),(65,1,'ti_types.acl_id','2019-07-01 09:07:45'),(66,1,'ti_types.acl_id','2019-07-01 09:07:37'),(67,1,'core_module.aclId','2019-07-01 09:07:38'),(68,1,'core_module.aclId','2019-07-01 09:06:31'),(69,1,'go_settings','2019-07-01 09:06:47'),(70,1,'cal_calendars.acl_id','2019-07-01 09:06:47'),(71,1,'ti_types.search_cache_acl_id','2019-07-01 09:07:51'),(72,1,'ti_types.search_cache_acl_id','2019-07-01 09:07:52'),(74,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(75,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(76,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(77,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(78,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(79,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(80,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(81,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(82,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(83,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(84,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(85,1,'core_customfields_field_set.aclId','2019-07-01 09:07:38'),(86,1,'ab_addressbooks.acl_id','2019-07-01 09:07:38'),(87,1,'core_group.aclId','2019-07-01 09:07:35'),(88,2,'fs_folders.acl_id','2019-07-01 09:07:35'),(89,2,'ab_addressbooks.acl_id','2019-07-01 09:07:36'),(90,2,'cal_calendars.acl_id','2019-07-01 09:07:38'),(91,2,'ta_tasklists.acl_id','2019-07-01 09:07:36'),(92,1,'core_group.aclId','2019-07-01 09:07:36'),(93,3,'fs_folders.acl_id','2019-07-01 09:07:36'),(94,3,'ab_addressbooks.acl_id','2019-07-01 09:07:37'),(95,3,'cal_calendars.acl_id','2019-07-01 09:07:38'),(96,3,'ta_tasklists.acl_id','2019-07-01 09:07:37'),(97,1,'core_group.aclId','2019-07-01 09:07:37'),(98,4,'fs_folders.acl_id','2019-07-01 09:07:38'),(99,4,'ab_addressbooks.acl_id','2019-07-01 09:07:38'),(100,4,'cal_calendars.acl_id','2019-07-01 09:07:38'),(101,4,'ta_tasklists.acl_id','2019-07-01 09:07:38'),(102,1,'cal_views.acl_id','2019-07-01 09:07:41'),(103,1,'cal_views.acl_id','2019-07-01 09:07:41'),(104,1,'cal_calendars.acl_id','2019-07-01 09:07:41'),(105,1,'cal_calendars.acl_id','2019-07-01 09:07:41'),(106,1,'ta_tasklists.acl_id','2019-07-01 09:07:41'),(107,1,'core_module.aclId','2019-07-01 09:07:45'),(108,1,'core_module.aclId','2019-07-01 09:07:45'),(109,1,'su_announcements.acl_id','2019-07-01 09:07:45'),(110,1,'su_announcements.acl_id','2019-07-01 09:07:45'),(111,1,'pr2_types.acl_id','2019-07-01 09:07:46'),(112,1,'pr2_types.acl_book','2019-07-01 09:07:46'),(113,3,'em_accounts.acl_id','2019-07-04 14:13:09'),(114,2,'em_accounts.acl_id','2019-07-01 09:07:47'),(115,4,'em_accounts.acl_id','2019-07-01 09:07:48'),(116,1,'core_module.aclId','2019-07-04 12:54:24'),(117,1,'pa_domains.acl_id','2019-07-04 12:54:40'),(118,1,'em_accounts.acl_id','2019-07-05 15:02:58'),(119,1,'ab_addresslists.acl_id','2019-07-08 18:24:54'),(120,1,'ab_addresslists.acl_id','2019-07-08 18:25:17'),(121,1,'pa_domains.acl_id','2019-07-09 13:02:17'),(122,1,'core_module.aclId','2019-07-09 14:09:10'),(123,1,'core_group.aclId','2019-07-09 14:10:21'),(124,5,'em_accounts.acl_id','2019-07-09 14:10:21'),(127,1,'core_group.aclId','2019-07-18 07:15:24'),(128,6,'em_accounts.acl_id','2019-07-18 07:15:25'),(129,6,'cal_calendars.acl_id','2019-07-18 07:15:25'),(130,1,'fs_folders.acl_id','2019-07-18 07:22:41'),(131,1,'pa_domains.acl_id','2019-09-10 09:14:32'),(132,1,'pa_domains.acl_id','2019-09-10 09:36:28'),(133,1,'pa_domains.acl_id','2019-09-16 11:37:46'),(134,1,'bs_expense_books.acl_id','2019-10-14 10:06:07');
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
INSERT INTO `core_acl_group` VALUES (2,2,10),(3,3,10),(4,4,10),(12,3,10),(13,3,10),(14,3,10),(15,3,10),(18,3,10),(19,3,10),(20,3,10),(21,3,10),(37,3,10),(38,3,10),(39,3,10),(40,3,10),(43,3,10),(44,3,10),(45,3,10),(46,3,10),(47,3,10),(48,3,10),(49,3,10),(50,3,10),(51,3,10),(57,2,10),(58,2,10),(59,3,10),(60,3,10),(61,3,10),(62,3,10),(63,3,10),(64,3,10),(67,3,10),(86,3,10),(87,5,10),(90,3,10),(92,6,10),(95,3,10),(97,7,10),(100,3,10),(102,3,10),(103,3,10),(104,3,10),(105,3,10),(107,3,10),(108,3,10),(109,2,10),(110,2,10),(116,3,10),(123,2,10),(127,2,10),(127,9,10),(16,3,30),(17,3,30),(22,5,30),(22,6,30),(27,5,30),(27,6,30),(32,5,30),(32,6,30),(65,2,30),(66,2,30),(74,3,30),(75,3,30),(76,3,30),(77,3,30),(78,3,30),(79,3,30),(80,3,30),(81,3,30),(82,3,30),(83,3,30),(84,3,30),(85,3,30),(111,3,30),(104,5,40),(105,5,40),(1,1,50),(2,1,50),(3,1,50),(4,1,50),(5,1,50),(6,1,50),(7,1,50),(8,1,50),(9,1,50),(10,1,50),(11,1,50),(12,1,50),(13,1,50),(14,1,50),(15,1,50),(16,1,50),(17,1,50),(18,1,50),(19,1,50),(20,1,50),(21,1,50),(22,1,50),(23,1,50),(23,2,50),(24,1,50),(24,2,50),(25,1,50),(25,2,50),(26,1,50),(26,2,50),(27,1,50),(28,1,50),(28,2,50),(29,1,50),(29,2,50),(30,1,50),(30,2,50),(31,1,50),(31,2,50),(32,1,50),(33,1,50),(33,2,50),(34,1,50),(34,2,50),(35,1,50),(35,2,50),(36,1,50),(36,2,50),(37,1,50),(38,1,50),(39,1,50),(40,1,50),(41,1,50),(43,1,50),(44,1,50),(45,1,50),(46,1,50),(47,1,50),(48,1,50),(49,1,50),(50,1,50),(51,1,50),(52,1,50),(53,1,50),(54,1,50),(55,1,50),(56,1,50),(57,1,50),(58,1,50),(59,1,50),(60,1,50),(61,1,50),(62,1,50),(63,1,50),(64,1,50),(65,1,50),(65,5,50),(65,6,50),(66,1,50),(67,1,50),(68,1,50),(69,1,50),(70,1,50),(71,1,50),(71,5,50),(71,6,50),(72,1,50),(74,1,50),(75,1,50),(76,1,50),(77,1,50),(78,1,50),(79,1,50),(80,1,50),(81,1,50),(82,1,50),(83,1,50),(84,1,50),(85,1,50),(86,1,50),(87,1,50),(88,1,50),(88,5,50),(89,1,50),(89,5,50),(90,1,50),(90,5,50),(91,1,50),(91,5,50),(92,1,50),(93,1,50),(93,6,50),(94,1,50),(94,6,50),(95,1,50),(95,6,50),(96,1,50),(96,6,50),(97,1,50),(98,1,50),(98,7,50),(99,1,50),(99,7,50),(100,1,50),(100,7,50),(101,1,50),(101,7,50),(102,1,50),(103,1,50),(104,1,50),(105,1,50),(106,1,50),(107,1,50),(108,1,50),(109,1,50),(110,1,50),(111,1,50),(112,1,50),(113,1,50),(113,4,50),(113,6,50),(114,1,50),(114,5,50),(115,1,50),(115,7,50),(116,1,50),(117,1,50),(118,1,50),(118,4,50),(119,1,50),(120,1,50),(121,1,50),(122,1,50),(123,1,50),(124,1,50),(127,1,50),(128,1,50),(128,9,50),(129,1,50),(129,9,50),(130,1,50),(131,1,50),(132,1,50),(133,1,50),(134,1,50);
/*!40000 ALTER TABLE `core_acl_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_acl_group_changes`
--

DROP TABLE IF EXISTS `core_acl_group_changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_acl_group_changes` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL,
  PRIMARY KEY (`aclId`,`groupId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `core_acl_group_changes_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_acl_group_changes_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_acl_group_changes`
--

LOCK TABLES `core_acl_group_changes` WRITE;
/*!40000 ALTER TABLE `core_acl_group_changes` DISABLE KEYS */;
/*!40000 ALTER TABLE `core_acl_group_changes` ENABLE KEYS */;
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
  KEY `moduleId_sortOrder` (`moduleId`,`sortOrder`),
  KEY `moduleId` (`moduleId`),
  CONSTRAINT `core_auth_method_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_method`
--

LOCK TABLES `core_auth_method` WRITE;
/*!40000 ALTER TABLE `core_auth_method` DISABLE KEYS */;
INSERT INTO `core_auth_method` VALUES ('password',7,1),('googleauthenticator',9,2),('imap',37,3);
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
  `digest` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`userId`),
  CONSTRAINT `core_auth_password_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_auth_password`
--

LOCK TABLES `core_auth_password` WRITE;
/*!40000 ALTER TABLE `core_auth_password` DISABLE KEYS */;
INSERT INTO `core_auth_password` VALUES (1,'$2y$10$/nmOuAWDynoKAxaJtwqVvuaB0fRYSYPm5N8XMGWzigq.mku0ImR8K','8df055ff0e87be4b572e43753ba7e0ba'),(2,'$2y$10$wNLG7JPpkgPaKRuB0tZN8eV2MsJfc1ZS23./PvmQFX1cFcQZCC3eG','3fef042c88bd11be71b5d4ffca823598'),(3,'$2y$10$YTbnf9LECDMzXT5dVxVtxujxzsaQUTygkOR62tC9W4VMXZSxMzQ3e','84ea262ebf688db5afce7ee9a23918aa'),(4,'$2y$10$h0KajOpoLnj4KqESX9pVBOkI/p/R2ZmAykibv2j/f8IZN5fgCmA1e','a3333616f033a687d159f076d8877b93'),(6,'nT6uJzatS4wNc','');
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
  `expiresAt` datetime NOT NULL,
  `lastActiveAt` datetime NOT NULL,
  `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passedMethods` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
INSERT INTO `core_auth_token` VALUES ('5d1dad7de0e98ebefa1e1f737556faa2874d7554b1154','5d1dad7e1cf6a530fe2ea6722ce75026b5ecda2077493',2,'2019-07-04 07:40:45','2019-07-11 08:07:00','2019-07-04 08:07:00','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d1de0321e180b789399ead2188fb0bebd983928dbeed','5d1de03241760fa76f41df980c99dde664942eb0bd137',1,'2019-07-04 11:17:06','2019-07-11 13:17:10','2019-07-04 13:17:10','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d1df6ed0f7886999fce866af14a8aae32dac41e9f4a6','5d1df6ed3d8f5f8d1ff4d3615f3466d417c34a16e2692',1,'2019-07-04 12:54:05','2019-07-11 12:54:05','2019-07-04 12:54:05','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d1e09257f4754152f0044aae20f00e11beb8ea0a934a','5d1e0925a1da6cc498765775a22f011cf0fe4a02f6c55',1,'2019-07-04 14:11:49','2019-07-11 15:10:10','2019-07-04 15:10:10','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d1f668e70bf716231091dd6cbf029afaa0a318ed1674','5d1f668e92ea1a6122af58d04a888d5e3e376e5eb47db',1,'2019-07-05 15:02:38','2019-07-12 15:08:46','2019-07-05 15:08:46','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d234976698970a94b60a96816edfe516bd7bf956e847','5d2349768ff2f4e8b97724b3f09e67541add6b4882299',1,'2019-07-08 13:47:34','2019-07-15 13:47:34','2019-07-08 13:47:34','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d238a64487b71900f8a184157e7d534980f2fdf5688c','5d238a646c752850b65651a42b28d8f987e1698e226d0',1,'2019-07-08 18:24:36','2019-07-15 18:24:36','2019-07-08 18:24:36','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d2440808057392fb34623a8d078cafbe77263b8c9890','5d244080a2c6d6e25ebb9bcc5c98199fcad8a218138e8',1,'2019-07-09 07:21:36','2019-07-16 07:39:57','2019-07-09 07:39:57','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d248ef4e20f5811e641a6f209d52c2b9bbb8527e41e9','5d248ef520fdcd2ce62fb5f79df8e23d639c8df6c1d58',1,'2019-07-09 12:56:20','2019-07-16 12:56:21','2019-07-09 12:56:20','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0','password'),('5d24904d06b9d0f27a85f1c8bdc0e19e61fb5c28b8acb','5d24904d29b15b03c4cdccc8f9cbddca58f122dba7067',1,'2019-07-09 13:02:05','2019-07-16 13:02:05','2019-07-09 13:02:05','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0','password'),('5d24a0542e3dd6e89aed8585961c4cf7f7a00a4c8eb4c','5d24a05453a95b5868f5e9fd97ed856baad6ad255bfa4',1,'2019-07-09 14:10:28','2019-07-16 14:10:28','2019-07-09 14:10:28','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d24a08e57979dbcf23b6b4a5ec5ddf4bbed18fb6b4c0','5d24a08e7c67ba88aeea2724578d6f5d04eb1f606674b',5,'2019-07-09 14:11:26','2019-07-16 15:07:46','2019-07-09 15:07:46','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0','imap'),('5d26fcf09a7a60bf1f36c0e8fcebad402593f747c53d4','5d26fcf0a845e194430a6f87068bfaa3c9a599ab3ca3b',4,'2019-07-11 09:10:08','2019-07-18 09:10:59','2019-07-11 09:10:08','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d301d4b3d484511b63bf47b7db68b2c65b4d5aca9671',NULL,6,'2019-07-18 07:18:35','2019-07-18 07:28:35','2019-07-18 07:18:35','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','imap'),('5d301dda7657fc5ad9c1c514ca947fc6f0c3842c98cf8','5d301ddaa1b8aec157ee3a21d136868573cede87766f3',1,'2019-07-18 07:20:58','2019-07-25 07:20:58','2019-07-18 07:20:58','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d302471ee15a6ce9e678bfc5f3e9f211b5cf50d49bbf','5d30247215852e1c161a4ef829a0bb6324d8940c56366',1,'2019-07-18 07:49:05','2019-07-25 07:49:06','2019-07-18 07:49:05','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d3058174380f482b068a2a25e5b0bf2a8ee6c4e276c4','5d3058175fc2870d4d0b8518ec146401110a200ee6e98',1,'2019-07-18 11:29:27','2019-07-25 11:29:27','2019-07-18 11:29:27','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d31caa2bb186dede5e21297d25633faf45a3777eadc1','5d31caa2debf2806c08819170172b2e09faf9987e480c',1,'2019-07-19 13:50:26','2019-07-26 13:56:02','2019-07-19 13:56:02','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15','password'),('5d528c5bb3b2ab1131e5e5e5f208541899c442b538f41','5d528c5bd78a05ae7fe27c74f344a635ba416b3e25645',1,'2019-08-13 10:09:31','2019-08-20 10:14:31','2019-08-13 10:14:31','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d52ce474b410e6c5854d1185841b6fcb2ac39f13b48c','5d52ce476bd0ce6130a3ff0a637ac5b937a9ef7364b60',1,'2019-08-13 14:50:47','2019-08-20 14:50:47','2019-08-13 14:50:47','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d554d71894a7c355167994061ba02886ea09dc0694e5','5d554d7192247ac0e6c966aa8346cc98f9d6a85090c61',1,'2019-08-15 12:17:53','2019-08-22 12:17:53','2019-08-15 12:17:53','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d77695e00861ccac1a17dd461c1ba85c7cd4433662b7','5d77695e24eda21e5a55fe1d1fb733a3a6fe79ddcc369',1,'2019-09-10 09:14:06','2019-09-17 09:14:06','2019-09-10 09:14:06','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d7f761132004433eb503d3869da9cffb6221538ba537','5d7f76115d8dbbb6e8b1d3d9427c590bc7430edff3a71',1,'2019-09-16 11:46:25','2019-09-23 11:46:25','2019-09-16 11:46:25','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d83816b5f842beddee0a388d19875ee3285856b1b988','5d83816b9080cef40e8d49b11d08bff92edf60d5b7d4c',1,'2019-09-19 13:23:55','2019-09-26 13:23:55','2019-09-19 13:23:55','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15','password'),('5d8cb20647b3bdcec734dbb46a993a2a1c8d42f2b14fc','5d8cb206707c787346e011750babb31073209ec904efb',1,'2019-09-26 12:41:42','2019-10-03 12:41:42','2019-09-26 12:41:42','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Safari/605.1.15','password'),('5d9f05603977ff34f90f58182e44e6a3067788a42717b','5d9f056066374691d2b341bdf06ea03ced1dfdf84676e',1,'2019-10-10 10:18:08','2019-10-17 12:00:12','2019-10-10 12:00:12','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password'),('5da4486cdb78997447f3dabb38956cd5c6f450649d01d','5da4486d25eab2fe7d0b4e6e93ffb5d2f1b8a6250d2cf',1,'2019-10-14 10:05:32','2019-10-21 12:08:02','2019-10-14 12:08:02','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password'),('5da595a6db3a784e935d16946d954bdb812f4ed65a2f3','5da595a71e3be2a20e9ffad35e94973aece64684d46e8',1,'2019-10-15 09:47:18','2019-10-22 10:12:44','2019-10-15 10:12:44','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password'),('5dad73c8cbd67b01bda0475a617f08669edd9fd1e770c','5dad73c9175d225195fd7e9233c6368f1e935489dedf5',1,'2019-10-21 09:00:56','2019-10-28 09:00:57','2019-10-21 09:00:56','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password'),('5dbb02b13f54d00608e00fe87d9f717dadd97de7aaec8','5dbb02b1793e7433b989b8fa302b409f3b53dbd450291',1,'2019-10-31 15:50:09','2019-11-07 17:05:38','2019-10-31 17:05:38','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password'),('5dbc17eaa977cbaf74084bc4703f54dca850e78b371ea','5dbc17eadd2bce3af7468f4a2cb66a4280189f66ec639',1,'2019-11-01 11:32:58','2019-11-08 13:21:27','2019-11-01 13:21:27','172.29.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15','password');
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
  `modified` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_blob`
--

LOCK TABLES `core_blob` WRITE;
/*!40000 ALTER TABLE `core_blob` DISABLE KEYS */;
INSERT INTO `core_blob` VALUES ('3d2793f79e87bc3fcfcc6a78c8a5feae1bf5ba19','image/png',470,1563434145,'Group-Office','2019-07-18 07:15:45'),('43c2f0d8150e47c0051b18b16eed52d3855c700d','image/png',467,1562656945,'Group-Office','2019-07-09 07:22:25');
/*!40000 ALTER TABLE `core_blob` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `core_change`
--

DROP TABLE IF EXISTS `core_change`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `core_change` (
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `destroyed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`entityId`,`entityTypeId`),
  KEY `aclId` (`aclId`),
  KEY `entityTypeId` (`entityTypeId`),
  CONSTRAINT `core_change_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_change`
--

LOCK TABLES `core_change` WRITE;
/*!40000 ALTER TABLE `core_change` DISABLE KEYS */;
INSERT INTO `core_change` VALUES (1,8,51,11,'2019-11-01 11:32:59',0),(2,8,2,11,'2019-07-01 11:07:49',0),(3,8,2,11,'2019-07-01 11:07:49',0),(3,32,2,122,'2019-07-09 14:10:39',0),(4,8,2,11,'2019-07-01 11:07:49',0),(5,7,2,45,'2019-07-01 11:07:49',0),(5,8,28,11,'2019-07-18 07:15:10',1),(6,7,1,45,'2019-07-01 09:06:48',0),(6,8,33,11,'2019-07-18 07:16:32',0),(7,7,15,70,'2019-07-18 07:15:26',0),(8,4,1,123,'2019-07-09 14:10:21',0),(8,7,1,70,'2019-07-01 09:06:48',0),(9,4,2,127,'2019-07-18 07:15:25',0),(9,7,34,45,'2019-10-10 14:00:15',0),(10,7,2,51,'2019-07-01 11:07:49',0),(11,7,2,17,'2019-07-01 11:07:49',0),(12,7,30,17,'2019-10-10 12:20:05',0),(13,7,2,17,'2019-07-01 11:07:49',0),(14,7,2,40,'2019-07-01 11:07:49',0),(15,7,2,40,'2019-07-01 11:07:49',0),(16,7,23,17,'2019-07-19 15:54:37',0),(17,7,2,40,'2019-07-01 11:07:49',0),(18,7,2,40,'2019-07-01 11:07:49',0),(19,7,3,17,'2019-07-04 09:41:24',0),(20,7,2,17,'2019-07-01 11:07:49',0),(21,7,30,17,'2019-10-10 12:20:05',0),(22,7,2,17,'2019-07-01 11:07:49',0),(23,7,2,17,'2019-07-01 11:07:49',0),(24,7,2,86,'2019-07-01 11:07:49',0),(25,7,36,86,'2019-10-14 12:10:10',0),(26,7,2,17,'2019-07-01 11:07:49',0),(27,7,14,45,'2019-07-18 07:15:10',0),(28,7,2,88,'2019-07-01 11:07:49',0),(29,7,10,86,'2019-07-09 09:39:59',0),(30,7,2,86,'2019-07-01 11:07:49',0),(31,7,2,89,'2019-07-01 11:07:49',0),(32,7,2,90,'2019-07-01 11:07:49',0),(33,7,2,91,'2019-07-01 11:07:49',0),(34,7,2,91,'2019-07-01 11:07:49',0),(35,7,2,93,'2019-07-01 11:07:49',0),(36,7,5,86,'2019-07-04 16:12:16',0),(37,6,1,122,'2019-07-09 14:09:10',0),(37,7,2,94,'2019-07-01 11:07:49',0),(38,7,2,95,'2019-07-01 11:07:49',0),(39,7,2,96,'2019-07-01 11:07:49',0),(40,7,2,98,'2019-07-01 11:07:49',0),(41,7,2,86,'2019-07-01 11:07:49',0),(42,7,2,99,'2019-07-01 11:07:49',0),(43,7,2,100,'2019-07-01 11:07:49',0),(44,7,2,101,'2019-07-01 11:07:49',0),(45,7,2,95,'2019-07-01 11:07:49',0),(46,7,2,100,'2019-07-01 11:07:49',0),(47,7,2,90,'2019-07-01 11:07:49',0),(48,7,2,95,'2019-07-01 11:07:49',0),(49,7,2,100,'2019-07-01 11:07:49',0),(50,7,2,90,'2019-07-01 11:07:49',0),(51,7,2,95,'2019-07-01 11:07:49',0),(52,7,2,100,'2019-07-01 11:07:49',0),(53,7,2,90,'2019-07-01 11:07:49',0),(54,7,2,100,'2019-07-01 11:07:49',0),(55,7,2,95,'2019-07-01 11:07:49',0),(56,7,2,100,'2019-07-01 11:07:49',0),(57,7,2,95,'2019-07-01 11:07:49',0),(58,7,2,100,'2019-07-01 11:07:49',0),(59,7,2,95,'2019-07-01 11:07:49',0),(60,7,2,100,'2019-07-01 11:07:49',0),(61,7,2,95,'2019-07-01 11:07:49',0),(62,7,2,100,'2019-07-01 11:07:49',0),(63,7,2,95,'2019-07-01 11:07:49',0),(64,7,2,100,'2019-07-01 11:07:49',0),(65,7,2,95,'2019-07-01 11:07:49',0),(66,7,2,104,'2019-07-01 11:07:49',0),(67,7,2,105,'2019-07-01 11:07:49',0),(68,7,2,106,'2019-07-01 11:07:49',0),(69,7,2,96,'2019-07-01 11:07:49',0),(70,7,2,101,'2019-07-01 11:07:49',0),(71,7,2,91,'2019-07-01 11:07:49',0),(72,7,2,96,'2019-07-01 11:07:49',0),(73,7,2,101,'2019-07-01 11:07:49',0),(74,7,2,91,'2019-07-01 11:07:49',0),(75,7,2,22,'2019-07-01 11:07:49',0),(76,7,2,22,'2019-07-01 11:07:49',0),(77,7,2,106,'2019-07-01 11:07:49',0),(78,7,2,40,'2019-07-01 11:07:49',0),(79,7,2,22,'2019-07-01 11:07:49',0),(80,7,2,106,'2019-07-01 11:07:49',0),(81,7,2,40,'2019-07-01 11:07:49',0),(82,7,2,27,'2019-07-01 11:07:49',0),(83,7,2,27,'2019-07-01 11:07:49',0),(84,7,2,27,'2019-07-01 11:07:49',0),(85,7,6,32,'2019-07-04 16:19:55',0),(86,7,2,32,'2019-07-01 11:07:49',0),(87,7,2,32,'2019-07-01 11:07:49',0),(88,7,2,65,'2019-07-01 11:07:49',0),(89,7,2,65,'2019-07-01 11:07:49',0),(90,7,2,93,'2019-07-01 11:07:49',0),(91,7,2,93,'2019-07-01 11:07:49',0),(92,7,2,93,'2019-07-01 11:07:49',0),(93,7,2,93,'2019-07-01 11:07:49',0),(94,7,2,93,'2019-07-01 11:07:49',0),(95,7,34,111,'2019-10-10 14:00:15',0),(96,7,34,111,'2019-10-10 14:00:15',0),(97,7,2,111,'2019-07-01 11:07:49',0),(98,7,2,51,'2019-07-01 11:07:49',0),(99,7,2,51,'2019-07-01 11:07:49',0),(100,7,2,17,'2019-07-01 11:07:49',0),(101,7,2,17,'2019-07-01 11:07:49',0),(102,7,2,17,'2019-07-01 11:07:49',0),(103,7,2,17,'2019-07-01 11:07:49',0),(104,7,3,15,'2019-07-04 09:41:24',0),(105,7,4,15,'2019-07-04 09:41:36',0),(106,7,7,119,'2019-07-08 20:24:54',0),(107,7,9,120,'2019-07-08 20:25:36',0),(109,7,12,111,'2019-07-11 11:10:24',0),(110,7,13,111,'2019-07-11 11:11:37',0),(111,7,14,45,'2019-07-18 07:15:10',1),(112,7,15,129,'2019-07-18 07:15:26',0),(113,7,16,130,'2019-07-18 09:22:41',0),(114,7,16,130,'2019-07-18 09:22:41',0),(115,7,17,40,'2019-07-18 13:30:08',0),(116,7,18,40,'2019-07-18 13:30:26',0),(117,7,28,52,'2019-08-13 16:52:06',0),(118,7,30,17,'2019-10-10 12:20:05',0),(119,7,30,17,'2019-10-10 12:20:05',0),(120,7,31,17,'2019-10-10 12:20:09',0),(121,7,32,17,'2019-10-10 12:20:20',0),(122,7,33,17,'2019-10-10 12:21:15',0),(123,7,34,111,'2019-10-10 14:00:15',0),(124,7,34,111,'2019-10-10 14:00:15',0),(125,7,35,111,'2019-10-10 14:00:23',0),(126,7,37,52,'2019-10-14 12:11:16',0),(127,7,38,52,'2019-10-14 12:11:17',0),(128,7,39,106,'2019-10-31 16:56:07',0),(129,7,40,15,'2019-10-31 16:57:06',0),(130,7,41,70,'2019-10-31 16:57:26',0),(131,7,42,106,'2019-10-31 16:58:41',0),(132,7,43,106,'2019-10-31 17:00:03',0),(133,7,44,106,'2019-10-31 17:00:54',0),(134,7,45,106,'2019-10-31 17:09:23',0);
/*!40000 ALTER TABLE `core_change` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_cron_job`
--

LOCK TABLES `core_cron_job` WRITE;
/*!40000 ALTER TABLE `core_cron_job` DISABLE KEYS */;
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
  `databaseName` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `datatype` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `helptext` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT 0,
  `unique_values` tinyint(1) NOT NULL DEFAULT 0,
  `prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`fieldSetId`),
  KEY `modSeq` (`modSeq`),
  CONSTRAINT `core_customfields_field_ibfk_1` FOREIGN KEY (`fieldSetId`) REFERENCES `core_customfields_field_set` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field`
--

LOCK TABLES `core_customfields_field` WRITE;
/*!40000 ALTER TABLE `core_customfields_field` DISABLE KEYS */;
INSERT INTO `core_customfields_field` VALUES (1,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Company','Company','GO\\Addressbook\\Customfieldtype\\Company',0,0,'',0,0,'','','{\"maxLength\":190}'),(2,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Contact','Contact','GO\\Addressbook\\Customfieldtype\\Contact',1,0,'',0,0,'','','{\"maxLength\":190}'),(3,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'File','File','GO\\Files\\Customfieldtype\\File',2,0,'',0,0,'','','{\"maxLength\":190}'),(4,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Checkbox','Checkbox','GO\\Customfields\\Customfieldtype\\Checkbox',3,0,'',0,0,'','','{\"maxLength\":190}'),(5,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Number','Number','GO\\Customfields\\Customfieldtype\\Number',4,0,'',0,0,'','','{\"maxLength\":190}'),(6,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'User','User','GO\\Customfields\\Customfieldtype\\User',5,0,'',0,0,'','',NULL),(7,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'HTML','HTML','GO\\Customfields\\Customfieldtype\\Html',6,0,'',0,0,'','',NULL),(8,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Infotext','Infotext','GO\\Customfields\\Customfieldtype\\Infotext',7,0,'',0,0,'','',NULL),(9,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Heading','Heading','GO\\Customfields\\Customfieldtype\\Heading',8,0,'',0,0,'','',NULL),(10,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Select','Select','GO\\Customfields\\Customfieldtype\\Select',9,0,'',0,0,'','',NULL),(11,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Treeselect','Treeselect','GO\\Customfields\\Customfieldtype\\Treeselect',10,0,'',0,0,'','',NULL),(12,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Textarea','Textarea','GO\\Customfields\\Customfieldtype\\Textarea',11,0,'',0,0,'','',NULL),(13,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Date','Date','GO\\Customfields\\Customfieldtype\\Date',12,0,'',0,0,'','','{\"maxLength\":190}'),(14,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Function','Function','GO\\Customfields\\Customfieldtype\\FunctionField',13,0,'',0,0,'','','{\"maxLength\":190}'),(15,1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Text','Text','GO\\Customfields\\Customfieldtype\\Text',14,0,'Some help text for this field',0,0,'','',NULL),(16,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Company','Company','GO\\Addressbook\\Customfieldtype\\Company',15,0,'',0,0,'','','{\"maxLength\":190}'),(17,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Contact','Contact','GO\\Addressbook\\Customfieldtype\\Contact',16,0,'',0,0,'','','{\"maxLength\":190}'),(18,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'File','File','GO\\Files\\Customfieldtype\\File',17,0,'',0,0,'','','{\"maxLength\":190}'),(19,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Checkbox','Checkbox','GO\\Customfields\\Customfieldtype\\Checkbox',18,0,'',0,0,'','','{\"maxLength\":190}'),(20,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Number','Number','GO\\Customfields\\Customfieldtype\\Number',19,0,'',0,0,'','','{\"maxLength\":190}'),(21,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'User','User','GO\\Customfields\\Customfieldtype\\User',20,0,'',0,0,'','',NULL),(22,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'HTML','HTML','GO\\Customfields\\Customfieldtype\\Html',21,0,'',0,0,'','',NULL),(23,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Infotext','Infotext','GO\\Customfields\\Customfieldtype\\Infotext',22,0,'',0,0,'','',NULL),(24,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Heading','Heading','GO\\Customfields\\Customfieldtype\\Heading',23,0,'',0,0,'','',NULL),(25,2,NULL,'2019-07-01 09:07:32','2019-07-19 13:56:34',NULL,'Select','Select','GO\\Customfields\\Customfieldtype\\Select',24,0,'',0,0,'','','{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\"}'),(26,2,NULL,'2019-07-01 09:07:32','2019-07-19 13:55:36',NULL,'Treeselect','Treeselect','GO\\Customfields\\Customfieldtype\\Treeselect',25,0,'',0,0,'','','{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\"}'),(27,2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,'Textarea','Textarea','GO\\Customfields\\Customfieldtype\\Textarea',26,0,'',0,0,'','',NULL),(28,2,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Date','Date','GO\\Customfields\\Customfieldtype\\Date',27,0,'',0,0,'','','{\"maxLength\":190}'),(29,2,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Function','Function','GO\\Customfields\\Customfieldtype\\FunctionField',28,0,'',0,0,'','','{\"maxLength\":190}'),(30,2,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Text','Text','GO\\Customfields\\Customfieldtype\\Text',29,0,'Some help text for this field',0,0,'','',NULL),(31,3,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',30,0,'Some help text for this field',0,0,'','',NULL),(32,4,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',31,0,'Some help text for this field',0,0,'','',NULL),(33,5,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',32,0,'Some help text for this field',0,0,'','',NULL),(34,6,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',33,0,'Some help text for this field',0,0,'','',NULL),(35,7,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',34,0,'Some help text for this field',0,0,'','',NULL),(36,8,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',35,0,'Some help text for this field',0,0,'','',NULL),(37,9,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',36,0,'Some help text for this field',0,0,'','',NULL),(38,10,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',37,0,'Some help text for this field',0,0,'','',NULL),(39,11,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',38,0,'Some help text for this field',0,0,'','',NULL),(40,12,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,'Custom','Custom','GO\\Customfields\\Customfieldtype\\Text',39,0,'Some help text for this field',0,0,'','',NULL),(41,2,NULL,'2019-07-19 13:51:58','2019-07-19 13:51:58',NULL,'Treeselect 1','Treeselect1','GO\\Customfields\\Customfieldtype\\TreeselectSlave',40,0,'',0,0,'','','{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\",\"treeMasterFieldId\":26,\"nestingLevel\":1}'),(42,2,NULL,'2019-07-19 13:52:45','2019-07-19 13:52:45',NULL,'Treeselect 2','Treeselect2','GO\\Customfields\\Customfieldtype\\TreeselectSlave',41,0,'',0,0,'','','{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\",\"treeMasterFieldId\":26,\"nestingLevel\":2}'),(43,2,NULL,'2019-08-13 10:13:13','2019-08-13 10:14:47',NULL,'Multiselect','multiselect','GO\\Customfields\\Customfieldtype\\Select',42,0,'',0,0,'','','{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":true,\"numberDecimals\":\"2\",\"function\":\"\"}');
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
  `sortOrder` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `entityId` (`entityId`),
  KEY `aclId` (`aclId`),
  KEY `modSeq` (`modSeq`),
  CONSTRAINT `core_customfields_field_set_ibfk_1` FOREIGN KEY (`entityId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `core_customfields_field_set_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_customfields_field_set`
--

LOCK TABLES `core_customfields_field_set` WRITE;
/*!40000 ALTER TABLE `core_customfields_field_set` DISABLE KEYS */;
INSERT INTO `core_customfields_field_set` VALUES (1,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,15,74,'Demo Custom fields',0),(2,NULL,'2019-07-01 09:07:32','2019-07-01 09:07:32',NULL,16,75,'Demo Custom fields',1),(3,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,17,76,'Demo Custom fields',2),(4,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,18,77,'Demo Custom fields',3),(5,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,19,78,'Demo Custom fields',4),(6,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,20,79,'Demo Custom fields',5),(7,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,21,80,'Demo Custom fields',6),(8,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,14,81,'Demo Custom fields',7),(9,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,22,82,'Demo Custom fields',8),(10,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,23,83,'Demo Custom fields',9),(11,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,24,84,'Demo Custom fields',10),(12,NULL,'2019-07-01 09:07:33','2019-07-01 09:07:33',NULL,25,85,'Demo Custom fields',11);
/*!40000 ALTER TABLE `core_customfields_field_set` ENABLE KEYS */;
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
  `highestModSeq` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clientName` (`clientName`),
  UNIQUE KEY `name` (`name`,`moduleId`) USING BTREE,
  KEY `moduleId` (`moduleId`),
  CONSTRAINT `core_entity_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_entity`
--

LOCK TABLES `core_entity` WRITE;
/*!40000 ALTER TABLE `core_entity` DISABLE KEYS */;
INSERT INTO `core_entity` VALUES (1,1,'CronJobSchedule','CronJobSchedule',NULL),(2,2,'Field','Field',NULL),(3,2,'FieldSet','FieldSet',NULL),(4,3,'Group','Group',2),(5,4,'Link','Link',NULL),(6,5,'Module','Module',1),(7,6,'Search','Search',45),(8,7,'User','User',51),(9,7,'Method','Method',NULL),(10,7,'Token','Token',NULL),(11,1,'Blob','Blob',NULL),(12,8,'Note','Note',NULL),(13,8,'NoteBook','NoteBook',NULL),(14,20,'Folder','Folder',NULL),(15,10,'Company','Company',NULL),(16,10,'Contact','Contact',NULL),(17,12,'Order','Order',NULL),(18,12,'Product','Product',NULL),(19,14,'Event','Event',NULL),(20,14,'Calendar','Calendar',NULL),(21,20,'File','File',NULL),(22,24,'Project','Project',NULL),(23,24,'TimeEntry','TimeEntry',NULL),(24,29,'Task','Task',NULL),(25,30,'Ticket','Ticket',NULL),(26,15,'Comment','Comment',NULL),(27,25,'LinkedEmail','LinkedEmail',NULL),(28,34,'Content','Content',NULL),(29,34,'Site','Site',NULL),(30,19,'Account','Account',NULL),(31,10,'Addresslist','Addresslist',NULL),(32,37,'Server','ImapAuthServer',2);
/*!40000 ALTER TABLE `core_entity` ENABLE KEYS */;
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
  KEY `isUserGroupFor` (`isUserGroupFor`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `core_group_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `core_group_ibfk_2` FOREIGN KEY (`isUserGroupFor`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_group`
--

LOCK TABLES `core_group` WRITE;
/*!40000 ALTER TABLE `core_group` DISABLE KEYS */;
INSERT INTO `core_group` VALUES (1,'Admins',1,1,NULL),(2,'Everyone',1,2,NULL),(3,'Internal',1,3,NULL),(4,'admin',1,4,1),(5,'elmer',1,87,2),(6,'demo',1,92,3),(7,'linda',1,97,4),(9,'foo@intermesh.localhost',1,127,6);
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
INSERT INTO `core_group_default_group` VALUES (2);
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
  CONSTRAINT `fromEntity` FOREIGN KEY (`fromEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `toEntity` FOREIGN KEY (`toEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=123 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_link`
--

LOCK TABLES `core_link` WRITE;
/*!40000 ALTER TABLE `core_link` DISABLE KEYS */;
INSERT INTO `core_link` VALUES (1,16,2,19,1,'','2019-07-01 09:07:39',NULL,NULL,NULL),(2,19,1,16,2,'','2019-07-01 09:07:39',NULL,NULL,NULL),(3,16,2,19,4,'','2019-07-01 09:07:39',NULL,NULL,NULL),(4,19,4,16,2,'','2019-07-01 09:07:39',NULL,NULL,NULL),(5,16,2,19,7,'','2019-07-01 09:07:39',NULL,NULL,NULL),(6,19,7,16,2,'','2019-07-01 09:07:39',NULL,NULL,NULL),(7,16,1,19,10,'','2019-07-01 09:07:40',NULL,NULL,NULL),(8,19,10,16,1,'','2019-07-01 09:07:40',NULL,NULL,NULL),(9,16,1,19,12,'','2019-07-01 09:07:40',NULL,NULL,NULL),(10,19,12,16,1,'','2019-07-01 09:07:40',NULL,NULL,NULL),(11,16,1,19,14,'','2019-07-01 09:07:40',NULL,NULL,NULL),(12,19,14,16,1,'','2019-07-01 09:07:40',NULL,NULL,NULL),(13,16,1,19,16,'','2019-07-01 09:07:40',NULL,NULL,NULL),(14,19,16,16,1,'','2019-07-01 09:07:40',NULL,NULL,NULL),(15,16,1,19,18,'','2019-07-01 09:07:41',NULL,NULL,NULL),(16,19,18,16,1,'','2019-07-01 09:07:41',NULL,NULL,NULL),(17,16,1,19,20,'','2019-07-01 09:07:41',NULL,NULL,NULL),(18,19,20,16,1,'','2019-07-01 09:07:41',NULL,NULL,NULL),(19,24,4,16,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(20,16,2,24,4,'','2019-07-01 09:07:42',NULL,NULL,NULL),(21,24,4,19,20,'','2019-07-01 09:07:42',NULL,NULL,NULL),(22,19,20,24,4,'','2019-07-01 09:07:42',NULL,NULL,NULL),(23,24,5,16,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(24,16,2,24,5,'','2019-07-01 09:07:42',NULL,NULL,NULL),(25,24,5,19,20,'','2019-07-01 09:07:42',NULL,NULL,NULL),(26,19,20,24,5,'','2019-07-01 09:07:42',NULL,NULL,NULL),(27,24,6,16,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(28,16,2,24,6,'','2019-07-01 09:07:42',NULL,NULL,NULL),(29,24,6,19,20,'','2019-07-01 09:07:42',NULL,NULL,NULL),(30,19,20,24,6,'','2019-07-01 09:07:42',NULL,NULL,NULL),(31,17,1,16,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(32,16,1,17,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(33,17,1,15,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(34,15,1,17,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(35,24,7,17,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(36,17,1,24,7,'','2019-07-01 09:07:42',NULL,NULL,NULL),(37,24,7,16,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(38,16,1,24,7,'','2019-07-01 09:07:42',NULL,NULL,NULL),(39,24,7,15,1,'','2019-07-01 09:07:42',NULL,NULL,NULL),(40,15,1,24,7,'','2019-07-01 09:07:42',NULL,NULL,NULL),(41,17,2,16,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(42,16,2,17,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(43,17,2,15,2,'','2019-07-01 09:07:42',NULL,NULL,NULL),(44,15,2,17,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(45,24,8,17,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(46,17,2,24,8,'','2019-07-01 09:07:43',NULL,NULL,NULL),(47,24,8,16,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(48,16,2,24,8,'','2019-07-01 09:07:43',NULL,NULL,NULL),(49,24,8,15,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(50,15,2,24,8,'','2019-07-01 09:07:43',NULL,NULL,NULL),(51,17,3,16,1,'','2019-07-01 09:07:43',NULL,NULL,NULL),(52,16,1,17,3,'','2019-07-01 09:07:43',NULL,NULL,NULL),(53,17,3,15,1,'','2019-07-01 09:07:43',NULL,NULL,NULL),(54,15,1,17,3,'','2019-07-01 09:07:43',NULL,NULL,NULL),(55,17,4,16,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(56,16,2,17,4,'','2019-07-01 09:07:43',NULL,NULL,NULL),(57,17,4,15,2,'','2019-07-01 09:07:43',NULL,NULL,NULL),(58,15,2,17,4,'','2019-07-01 09:07:43',NULL,NULL,NULL),(59,17,5,16,1,'','2019-07-01 09:07:44',NULL,NULL,NULL),(60,16,1,17,5,'','2019-07-01 09:07:44',NULL,NULL,NULL),(61,17,5,15,1,'','2019-07-01 09:07:44',NULL,NULL,NULL),(62,15,1,17,5,'','2019-07-01 09:07:44',NULL,NULL,NULL),(63,17,6,16,2,'','2019-07-01 09:07:44',NULL,NULL,NULL),(64,16,2,17,6,'','2019-07-01 09:07:44',NULL,NULL,NULL),(65,17,6,15,2,'','2019-07-01 09:07:44',NULL,NULL,NULL),(66,15,2,17,6,'','2019-07-01 09:07:44',NULL,NULL,NULL),(67,22,2,15,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(68,15,2,22,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(69,22,2,16,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(70,16,2,22,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(71,22,3,15,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(72,15,2,22,3,'','2019-07-01 09:07:46',NULL,NULL,NULL),(73,22,3,16,2,'','2019-07-01 09:07:46',NULL,NULL,NULL),(74,16,2,22,3,'','2019-07-01 09:07:46',NULL,NULL,NULL),(75,27,1,16,2,'','2019-07-01 09:07:47',NULL,NULL,NULL),(76,16,2,27,1,'','2019-07-01 09:07:47',NULL,NULL,NULL),(77,27,2,16,1,'','2019-07-01 09:07:47',NULL,NULL,NULL),(78,16,1,27,2,'','2019-07-01 09:07:47',NULL,NULL,NULL),(79,27,3,16,2,'','2019-07-01 09:07:47',NULL,NULL,NULL),(80,16,2,27,3,'','2019-07-01 09:07:47',NULL,NULL,NULL),(81,27,4,16,1,'','2019-07-01 09:07:47',NULL,NULL,NULL),(82,16,1,27,4,'','2019-07-01 09:07:47',NULL,NULL,NULL),(83,16,7,16,6,NULL,'2019-07-04 07:41:59',NULL,NULL,NULL),(84,16,6,16,7,NULL,'2019-07-04 07:41:59',NULL,NULL,NULL),(85,27,5,22,1,'','2019-07-11 09:10:24',NULL,NULL,NULL),(86,22,1,27,5,'','2019-07-11 09:10:24',NULL,NULL,NULL),(87,22,2,27,5,NULL,'2019-07-11 09:10:37',NULL,NULL,NULL),(88,27,5,22,2,NULL,'2019-07-11 09:10:37',NULL,NULL,NULL),(89,27,6,22,1,'','2019-07-11 09:11:37',NULL,NULL,NULL),(90,22,1,27,6,'','2019-07-11 09:11:37',NULL,NULL,NULL),(91,22,3,27,6,NULL,'2019-07-11 09:11:51',NULL,NULL,NULL),(92,27,6,22,3,NULL,'2019-07-11 09:11:51',NULL,NULL,NULL),(93,27,7,16,1,'','2019-10-10 10:20:20',NULL,NULL,NULL),(94,16,1,27,7,'','2019-10-10 10:20:20',NULL,NULL,NULL),(95,27,8,16,1,'','2019-10-10 10:21:15',NULL,NULL,NULL),(96,16,1,27,8,'','2019-10-10 10:21:15',NULL,NULL,NULL),(97,22,5,15,2,'','2019-10-14 10:11:16',NULL,NULL,NULL),(98,15,2,22,5,'','2019-10-14 10:11:16',NULL,NULL,NULL),(99,27,9,22,5,'','2019-10-14 10:11:17',NULL,NULL,NULL),(100,22,5,27,9,'','2019-10-14 10:11:17',NULL,NULL,NULL),(101,24,9,22,3,NULL,'2019-10-31 15:56:07',NULL,NULL,NULL),(102,22,3,24,9,NULL,'2019-10-31 15:56:07',NULL,NULL,NULL),(103,24,9,22,2,NULL,'2019-10-31 15:56:07',NULL,NULL,NULL),(104,22,2,24,9,NULL,'2019-10-31 15:56:07',NULL,NULL,NULL),(105,22,3,16,8,NULL,'2019-10-31 15:57:07',NULL,NULL,NULL),(106,16,8,22,3,NULL,'2019-10-31 15:57:07',NULL,NULL,NULL),(107,19,22,22,2,NULL,'2019-10-31 15:57:27',NULL,NULL,NULL),(108,22,2,19,22,NULL,'2019-10-31 15:57:27',NULL,NULL,NULL),(109,24,10,22,2,NULL,'2019-10-31 15:58:42',NULL,NULL,NULL),(110,22,2,24,10,NULL,'2019-10-31 15:58:42',NULL,NULL,NULL),(111,24,10,22,3,NULL,'2019-10-31 15:58:42',NULL,NULL,NULL),(112,22,3,24,10,NULL,'2019-10-31 15:58:42',NULL,NULL,NULL),(113,24,11,22,2,NULL,'2019-10-31 16:00:03',NULL,NULL,NULL),(114,22,2,24,11,NULL,'2019-10-31 16:00:03',NULL,NULL,NULL),(115,24,11,22,3,NULL,'2019-10-31 16:00:03',NULL,NULL,NULL),(116,22,3,24,11,NULL,'2019-10-31 16:00:03',NULL,NULL,NULL),(117,24,12,22,2,NULL,'2019-10-31 16:00:57',NULL,NULL,NULL),(118,22,2,24,12,NULL,'2019-10-31 16:00:57',NULL,NULL,NULL),(119,24,12,22,3,NULL,'2019-10-31 16:00:57',NULL,NULL,NULL),(120,22,3,24,12,NULL,'2019-10-31 16:00:57',NULL,NULL,NULL),(121,24,13,22,3,NULL,'2019-10-31 16:09:23',NULL,NULL,NULL),(122,22,3,24,13,NULL,'2019-10-31 16:09:23',NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_module`
--

LOCK TABLES `core_module` WRITE;
/*!40000 ALTER TABLE `core_module` DISABLE KEYS */;
INSERT INTO `core_module` VALUES (1,'core','core',62,0,0,5,1,NULL,NULL,NULL),(2,'customfields','core',0,0,0,6,1,NULL,NULL,NULL),(3,'groups','core',0,0,0,7,1,NULL,NULL,NULL),(4,'links','core',0,0,0,8,1,NULL,NULL,NULL),(5,'modules','core',0,0,0,9,1,NULL,NULL,NULL),(6,'search','core',0,0,0,10,1,NULL,NULL,NULL),(7,'users','core',5,0,0,11,1,NULL,NULL,NULL),(8,'notes','community',35,0,0,12,1,NULL,NULL,NULL),(9,'googleauthenticator','community',0,0,0,13,1,NULL,NULL,NULL),(10,'addressbook',NULL,307,9,0,14,1,'2019-07-01 09:06:25',NULL,NULL),(11,'assistant',NULL,0,10,0,20,1,'2019-07-01 09:06:26',NULL,NULL),(12,'billing',NULL,302,11,0,21,1,'2019-07-01 09:06:26',NULL,NULL),(13,'bookmarks',NULL,19,12,0,37,1,'2019-07-01 09:06:27',NULL,NULL),(14,'calendar',NULL,175,13,0,39,1,'2019-07-01 09:06:27',NULL,NULL),(15,'comments',NULL,14,14,0,40,1,'2019-07-01 09:06:27',NULL,NULL),(16,'cron',NULL,0,15,1,41,1,'2019-07-01 09:06:27',NULL,NULL),(18,'documenttemplates',NULL,0,17,0,43,1,'2019-07-01 09:06:28',NULL,NULL),(19,'email',NULL,97,18,0,44,1,'2019-10-10 10:18:43',NULL,NULL),(20,'files',NULL,115,19,0,45,1,'2019-07-01 09:06:28',NULL,NULL),(21,'hoursapproval2',NULL,0,20,0,48,1,'2019-07-01 09:06:28',NULL,NULL),(22,'intermeshtrials',NULL,0,21,0,49,1,'2019-07-01 09:06:28',NULL,NULL),(23,'leavedays',NULL,27,22,0,50,1,'2019-07-01 09:06:28',NULL,NULL),(24,'projects2',NULL,363,23,0,51,1,'2019-07-01 09:06:28',NULL,NULL),(25,'savemailas',NULL,9,24,0,59,1,'2019-07-01 09:06:29',NULL,NULL),(26,'sieve',NULL,0,25,0,60,1,'2019-07-01 09:06:30',NULL,NULL),(27,'summary',NULL,17,26,0,61,1,'2019-07-01 09:06:30',NULL,NULL),(28,'sync',NULL,43,27,0,62,1,'2019-07-01 09:06:30',NULL,NULL),(29,'tasks',NULL,55,28,0,63,1,'2019-07-01 09:06:30',NULL,NULL),(30,'tickets',NULL,151,29,0,64,1,'2019-07-01 09:06:30',NULL,NULL),(31,'timeregistration2',NULL,0,30,0,67,1,'2019-07-01 09:06:31',NULL,NULL),(32,'tools',NULL,0,31,1,68,1,'2019-07-01 09:06:31',NULL,NULL),(34,'site',NULL,10,32,0,107,1,'2019-07-01 09:07:45',NULL,NULL),(35,'defaultsite',NULL,0,33,0,108,1,'2019-07-01 09:07:45',NULL,NULL),(36,'postfixadmin',NULL,38,33,0,116,1,'2019-07-04 12:54:24',NULL,NULL),(37,'imapauthenticator','community',1,0,0,122,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `core_module` ENABLE KEYS */;
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
  `keywords` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `modifiedAt` datetime DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `entityId` (`entityId`,`entityTypeId`),
  KEY `acl_id` (`aclId`),
  KEY `name` (`name`),
  KEY `moduleId` (`moduleId`),
  KEY `keywords` (`keywords`),
  KEY `entityTypeId` (`entityTypeId`),
  CONSTRAINT `core_search_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=135 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `core_search`
--

LOCK TABLES `core_search` WRITE;
/*!40000 ALTER TABLE `core_search` DISABLE KEYS */;
INSERT INTO `core_search` VALUES (1,1,20,'project_templates','project_templates',14,'Folder,project_templates','2019-02-06 19:13:08',57),(2,2,20,'Projects folder','project_templates/Projects folder',14,'Folder,Projects folder,project_templates/Projects folder','2019-07-01 09:06:29',57),(3,3,20,'Standard project','project_templates/Standard project',14,'Folder,Standard project,project_templates/Standard project','2019-07-01 09:06:29',58),(4,4,20,'tickets','tickets',14,'Folder,tickets','2019-05-31 12:26:43',65),(5,7,20,'billing','billing',14,'Folder,billing','2019-02-25 11:06:18',45),(6,8,20,'stationery-papers','billing/stationery-papers',14,'Folder,stationery-papers,billing/stationery-papers','2019-07-01 09:06:47',45),(7,9,20,'calendar','calendar',14,'Folder,calendar','2019-07-18 07:15:25',70),(8,10,20,'System Administrator','calendar/System Administrator',14,'Folder,System Administrator,calendar/System Administrator','2019-07-01 09:06:47',70),(9,11,20,'projects2','projects2',14,'Map,projects2','2018-12-20 08:25:04',45),(10,12,20,'template-icons','projects2/template-icons',14,'Folder,template-icons,projects2/template-icons','2019-01-08 10:16:35',51),(11,1,10,'Smith Inc','Customers',15,'Company,Smith Inc,Customers,Kalverstraat,1,1012 NX,Amsterdam,Noord-Holland,NL,Noord-Brabant,+31 (0) 10 - 1234567,+31 (0) 1234567,info@smith.demo,http://www.smith.demo,Just a demo company,NL','2019-07-01 09:07:33',17),(12,1,10,'John Smith','Customers - Smith Inc',16,'Contactpersoon,John Smith,Customers - Smith Inc,04d1b2d9-f7ec-531d-b58e-ad314c70ec56,John,Smith,M,john@smith.demo,CEO,06-12345678,NL,Noord-Holland,Amsterdam,1012 NX,Kalverstraat,1,Dear Mr.','2019-10-10 10:20:04',17),(13,2,10,'ACME Corporation','Customers',15,'Company,ACME Corporation,Customers,1111 Broadway,10019,New York,NY,US,(555) 123-4567,info@acme.demo,http://www.acme.demo,The name Acme became popular for businesses by the 1920s, when','2019-07-01 09:07:34',17),(14,1,15,'The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...','',26,'Comment,The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...,17,The company is never clearly defined in Road Runner cartoons but appears to be a','2019-07-01 09:07:34',40),(15,2,15,'Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...','',26,'Comment,Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...,17, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat,','2019-07-01 09:07:34',40),(16,2,10,'Wile E. Coyote','Customers - ACME Corporation',16,'Contact,Wile E. Coyote,Customers - ACME Corporation,c1042689-977c-5ed9-b4bd-c7a75d8c9eb4,Wile,E.,Coyote,M,wile@acme.demo,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Mr.','2019-07-19 13:54:37',17),(17,3,15,'Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...','',26,'Comment,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...,17,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of','2019-07-01 09:07:34',40),(18,4,15,'In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...','',26,'Comment,In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...,17, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube','2019-07-01 09:07:34',40),(19,13,20,'addressbook','addressbook',14,'Folder,addressbook','2019-07-04 07:41:24',17),(20,14,20,'Customers','addressbook/Customers',14,'Folder,Customers,addressbook/Customers','2019-02-06 19:13:42',17),(21,15,20,'contacts','addressbook/Customers/contacts',14,'Map,contacts,addressbook/Customers/contacts','2019-10-10 10:20:04',17),(22,16,20,'C','addressbook/Customers/contacts/C',14,'Folder,C,addressbook/Customers/contacts/C','2019-07-01 09:07:34',17),(23,17,20,'Wile E. Coyote (2)','addressbook/Customers/contacts/C/Wile E. Coyote (2)',14,'Folder,Wile E. Coyote (2),addressbook/Customers/contacts/C/Wile E. Coyote (2)','2019-07-01 09:07:34',17),(24,18,20,'Users','addressbook/Users',14,'Folder,Users,addressbook/Users','2019-07-01 09:07:35',86),(25,3,10,'System Administrator','Users - ACME Rocket Powered Products',16,'Contactpersoon,System Administrator,Users - ACME Rocket Powered Products,afa6fc35-4b0d-501f-afe5-329fe1f74370,System,Administrator,M,admin@intermesh.localhost,Dear System,Option 3,Option','2019-10-14 10:10:10',86),(26,1,20,'Demo letter.docx','addressbook/Customers/contacts/C/Wile E. Coyote (2)/Demo letter.docx',21,'File,Demo letter.docx,addressbook/Customers/contacts/C/Wile E. Coyote (2)/Demo letter.docx,docx','2019-07-01 09:07:34',17),(27,19,20,'users','users',14,'Folder,users','2019-07-18 07:15:09',45),(28,20,20,'elmer','users/elmer',14,'Folder,elmer,users/elmer','2019-07-01 09:07:35',88),(29,4,10,'Elmer Fudd','Users - ACME Rocket Powered Products',16,'Contactpersoon,Elmer Fudd,Users - ACME Rocket Powered Products,f304612d-11ad-5fe2-ac2c-022d6a613472,Elmer,Fudd,M,elmer@group-office.com,CEO,06-12345678,US,NY,New York,10019,1111','2019-07-09 07:39:59',86),(30,3,10,'ACME Rocket Powered Products','Users',15,'Company,ACME Rocket Powered Products,Users,1111 Broadway,10019,New York,NY,US,(555) 123-4567,info@acmerpp.demo,http://www.acmerpp.demo,The name Acme became popular for businesses by the','2019-07-01 09:07:35',86),(31,21,20,'Elmer Fudd','addressbook/Elmer Fudd',14,'Folder,Elmer Fudd,addressbook/Elmer Fudd','2019-07-01 09:07:36',89),(32,22,20,'Elmer Fudd','calendar/Elmer Fudd',14,'Folder,Elmer Fudd,calendar/Elmer Fudd','2019-07-01 09:07:36',90),(33,23,20,'tasks','tasks',14,'Folder,tasks','2019-02-28 15:07:18',91),(34,24,20,'Elmer Fudd','tasks/Elmer Fudd',14,'Folder,Elmer Fudd,tasks/Elmer Fudd','2019-07-01 09:07:36',91),(35,25,20,'demo','users/demo',14,'Folder,demo,users/demo','2019-02-06 19:13:52',93),(36,5,10,'Demo User','Users - ACME Rocket Powered Products',16,'Contact,Demo User,Users - ACME Rocket Powered Products,d6000469-ad98-5256-bd93-cb653cdde744,Demo,User,M,demo@acmerpp.demo,demo@group-office.com,CEO,06-12345678,US,NY,New York,10019,1111','2019-07-04 14:12:16',86),(37,26,20,'Demo User','addressbook/Demo User',14,'Folder,Demo User,addressbook/Demo User','2019-07-01 09:07:37',94),(38,27,20,'Demo User','calendar/Demo User',14,'Folder,Demo User,calendar/Demo User','2019-07-01 09:07:37',95),(39,28,20,'Demo User','tasks/Demo User',14,'Folder,Demo User,tasks/Demo User','2019-07-01 09:07:37',96),(40,29,20,'linda','users/linda',14,'Folder,linda,users/linda','2019-07-01 09:07:38',98),(41,6,10,'Linda Smith','Users - ACME Rocket Powered Products',16,'Contact,Linda Smith,Users - ACME Rocket Powered Products,5ed8a0d7-8e5d-5b56-8642-29e2aabdeb4d,Linda,Smith,M,linda@acmerpp.demo,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Linda','2019-07-01 09:07:38',86),(42,30,20,'Linda Smith','addressbook/Linda Smith',14,'Folder,Linda Smith,addressbook/Linda Smith','2019-07-01 09:07:38',99),(43,31,20,'Linda Smith','calendar/Linda Smith',14,'Folder,Linda Smith,calendar/Linda Smith','2019-07-01 09:07:38',100),(44,32,20,'Linda Smith','tasks/Linda Smith',14,'Folder,Linda Smith,tasks/Linda Smith','2019-07-01 09:07:38',101),(45,1,14,'Project meeting (02-07-2019, Demo User)','',19,'Event,Project meeting (02-07-2019, Demo User),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:38',95),(46,2,14,'Project meeting (02-07-2019, Linda Smith)','',19,'Event,Project meeting (02-07-2019, Linda Smith),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:38',100),(47,3,14,'Project meeting (02-07-2019, Elmer Fudd)','',19,'Event,Project meeting (02-07-2019, Elmer Fudd),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:38',90),(48,4,14,'Meet Wile (02-07-2019, Demo User)','',19,'Event,Meet Wile (02-07-2019, Demo User),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',95),(49,5,14,'Meet Wile (02-07-2019, Linda Smith)','',19,'Event,Meet Wile (02-07-2019, Linda Smith),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',100),(50,6,14,'Meet Wile (02-07-2019, Elmer Fudd)','',19,'Event,Meet Wile (02-07-2019, Elmer Fudd),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',90),(51,7,14,'MT Meeting (02-07-2019, Demo User)','',19,'Event,MT Meeting (02-07-2019, Demo User),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',95),(52,8,14,'MT Meeting (02-07-2019, Linda Smith)','',19,'Event,MT Meeting (02-07-2019, Linda Smith),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',100),(53,9,14,'MT Meeting (02-07-2019, Elmer Fudd)','',19,'Event,MT Meeting (02-07-2019, Elmer Fudd),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',90),(54,10,14,'Project meeting (03-07-2019, Linda Smith)','',19,'Event,Project meeting (03-07-2019, Linda Smith),@1562144400,ccc9622a-e00d-5643-99f2-7c2dc88494b1,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',100),(55,11,14,'Project meeting (03-07-2019, Demo User)','',19,'Event,Project meeting (03-07-2019, Demo User),@1562144400,ccc9622a-e00d-5643-99f2-7c2dc88494b1,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:39',95),(56,12,14,'Meet John (03-07-2019, Linda Smith)','',19,'Event,Meet John (03-07-2019, Linda Smith),@1562151600,71d09bf8-00e9-5c29-9889-b4db5de35525,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:40',100),(57,13,14,'Meet John (03-07-2019, Demo User)','',19,'Event,Meet John (03-07-2019, Demo User),@1562151600,71d09bf8-00e9-5c29-9889-b4db5de35525,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:40',95),(58,14,14,'MT Meeting (03-07-2019, Linda Smith)','',19,'Event,MT Meeting (03-07-2019, Linda Smith),@1562162400,379d9f35-23dd-5c09-8da6-2c1ae967386c,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:40',100),(59,15,14,'MT Meeting (03-07-2019, Demo User)','',19,'Event,MT Meeting (03-07-2019, Demo User),@1562162400,379d9f35-23dd-5c09-8da6-2c1ae967386c,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2','2019-07-01 09:07:40',95),(60,16,14,'Rocket testing (02-07-2019, Linda Smith)','',19,'Event,Rocket testing (02-07-2019, Linda Smith),@1562047200,20ef5123-708b-590e-9d95-6c963b0accb4,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:40',100),(61,17,14,'Rocket testing (02-07-2019, Demo User)','',19,'Event,Rocket testing (02-07-2019, Demo User),@1562047200,20ef5123-708b-590e-9d95-6c963b0accb4,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:40',95),(62,18,14,'Blast impact test (02-07-2019, Linda Smith)','',19,'Event,Blast impact test (02-07-2019, Linda Smith),@1562072400,f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:40',100),(63,19,14,'Blast impact test (02-07-2019, Demo User)','',19,'Event,Blast impact test (02-07-2019, Demo User),@1562072400,f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:40',95),(64,20,14,'Test range extender (02-07-2019, Linda Smith)','',19,'Event,Test range extender (02-07-2019, Linda Smith),@1562086800,0212980f-bfec-55a8-8fba-b3232a097705,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:41',100),(65,21,14,'Test range extender (02-07-2019, Demo User)','',19,'Event,Test range extender (02-07-2019, Demo User),@1562086800,0212980f-bfec-55a8-8fba-b3232a097705,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2','2019-07-01 09:07:41',95),(66,33,20,'Road Runner Room','calendar/Road Runner Room',14,'Folder,Road Runner Room,calendar/Road Runner Room','2019-07-01 09:07:41',104),(67,34,20,'Don Coyote Room','calendar/Don Coyote Room',14,'Folder,Don Coyote Room,calendar/Don Coyote Room','2019-07-01 09:07:41',105),(68,35,20,'System Administrator','tasks/System Administrator',14,'Folder,System Administrator,tasks/System Administrator','2019-07-01 09:07:41',106),(69,1,29,'Feed the dog','',24,'Task,Feed the dog,69afb1ee-4123-5162-a477-c9256d9d6d0f,NEEDS-ACTION','2019-07-01 09:07:41',96),(70,2,29,'Feed the dog','',24,'Task,Feed the dog,9a2db52a-912b-521d-94d6-336a72e0a531,NEEDS-ACTION','2019-07-01 09:07:41',101),(71,3,29,'Feed the dog','',24,'Task,Feed the dog,ee97359f-e341-5292-b9d9-63be3f9b3db9,NEEDS-ACTION','2019-07-01 09:07:41',91),(72,4,29,'Prepare meeting','',24,'Task,Prepare meeting,511706f1-b2b6-51d8-9925-af89876d42f8,NEEDS-ACTION','2019-07-01 09:07:41',96),(73,5,29,'Prepare meeting','',24,'Task,Prepare meeting,9374d8e8-3dcc-5586-a23e-a0885e667e6d,NEEDS-ACTION','2019-07-01 09:07:42',101),(74,6,29,'Prepare meeting','',24,'Task,Prepare meeting,dd44ae8c-8528-597c-aed1-e9f8bc5e21ee,NEEDS-ACTION','2019-07-01 09:07:42',91),(75,1,12,'Q19000001','Smith Inc',17,'Invoice/Quote,Q19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo','2019-07-01 09:07:42',22),(76,36,20,'Quotes','billing/Quotes',14,'Folder,Quotes,billing/Quotes','2019-07-01 09:07:42',22),(77,7,29,'Call: Smith Inc (Q19000001)','',24,'Task,Call: Smith Inc (Q19000001),da0f4e45-4aa8-5a7a-8035-5a5742fd4285,NEEDS-ACTION','2019-07-01 09:07:42',106),(78,5,15,'Scheduled call at 04-07-2019 11:07','',26,'Comment,Scheduled call at 04-07-2019 11:07,22','2019-07-01 09:07:42',40),(79,2,12,'Q19000002','ACME Corporation',17,'Invoice/Quote,Q19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo','2019-07-01 09:07:43',22),(80,8,29,'Call: ACME Corporation (Q19000002)','',24,'Task,Call: ACME Corporation (Q19000002),92b31122-f509-595e-bf5e-d7364cda75a7,NEEDS-ACTION','2019-07-01 09:07:43',106),(81,6,15,'Scheduled call at 04-07-2019 11:07','',26,'Comment,Scheduled call at 04-07-2019 11:07,22','2019-07-01 09:07:43',40),(82,3,12,'O19000001','Smith Inc',17,'Invoice/Quote,O19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo','2019-07-01 09:07:43',27),(83,37,20,'Orders','billing/Orders',14,'Folder,Orders,billing/Orders','2019-07-01 09:07:43',27),(84,4,12,'O19000002','ACME Corporation',17,'Invoice/Quote,O19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo','2019-07-01 09:07:44',27),(85,5,12,'I19000001','Smith Inc',17,'Invoice/Quote,I19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo','2019-07-04 14:19:55',32),(86,38,20,'Invoices','billing/Invoices',14,'Folder,Invoices,billing/Invoices','2019-07-01 09:07:44',32),(87,6,12,'I19000002','ACME Corporation',17,'Invoice/Quote,I19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo','2019-07-01 09:07:44',32),(88,1,30,'Malfunctioning rockets','Wile E. Coyote (ACME Corporation)',25,'Ticket,Malfunctioning rockets,Wile E. Coyote (ACME Corporation),71,201900001,ACME Corporation,Wile,E.,Coyote,wile@acme.demo','2019-07-01 09:07:45',65),(89,2,30,'Can I speed up my rockets?','Wile E. Coyote (ACME Corporation)',25,'Ticket,Can I speed up my rockets?,Wile E. Coyote (ACME Corporation),71,201900002,ACME Corporation,Wile,E.,Coyote,wile@acme.demo','2019-07-01 09:07:45',65),(90,2,20,'noperson.jpg','users/demo/noperson.jpg',21,'File,noperson.jpg,users/demo/noperson.jpg,jpg','2019-07-01 09:07:45',93),(91,3,20,'empty.docx','users/demo/empty.docx',21,'File,empty.docx,users/demo/empty.docx,docx','2019-07-01 09:07:45',93),(92,4,20,'wecoyote.png','users/demo/wecoyote.png',21,'File,wecoyote.png,users/demo/wecoyote.png,png','2019-07-01 09:07:45',93),(93,5,20,'Demo letter.docx','users/demo/Demo letter.docx',21,'File,Demo letter.docx,users/demo/Demo letter.docx,docx','2019-07-01 09:07:45',93),(94,6,20,'empty.odt','users/demo/empty.odt',21,'File,empty.odt,users/demo/empty.odt,odt','2019-07-01 09:07:45',93),(95,1,24,'Demo','| Demo | Demo',22,'Project,Demo, | Demo | Demo,Just a placeholder for sub projects.,1','2019-10-10 12:00:15',111),(96,2,24,'[001] Develop Rocket 2000','| Demo | Demo/[001] Develop Rocket 2000',22,'Project,[001] Develop Rocket 2000, | Demo | Demo/[001] Develop Rocket 2000,Better range and accuracy,Demo/[001] Develop Rocket 2000,1','2019-10-10 12:00:15',111),(97,3,24,'[001] Develop Rocket Launcher','| Demo | Demo/[001] Develop Rocket Launcher',22,'Project,[001] Develop Rocket Launcher, | Demo | Demo/[001] Develop Rocket Launcher,Better range and accuracy,Demo/[001] Develop Rocket Launcher,1','2019-07-01 09:07:46',111),(98,7,20,'project.png','projects2/template-icons/project.png',21,'File,project.png,projects2/template-icons/project.png,png','2019-01-08 10:16:32',51),(99,8,20,'folder.png','projects2/template-icons/folder.png',21,'File,folder.png,projects2/template-icons/folder.png,png','2019-01-08 10:16:32',51),(100,1,25,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',27,'Email,Rocket 2000 development plan,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,@1368777188,\"User, Demo\" <demo@group-office.com>,\"Elmer\"','2019-07-01 09:07:47',17),(101,2,25,'Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',27,'Email,Rocket 2000 development plan,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,@1368777188,\"User, Demo\" <demo@group-office.com>,\"Elmer\"','2019-07-01 09:07:47',17),(102,3,25,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',27,'Email,Just a demo message,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\"','2019-07-01 09:07:47',17),(103,4,25,'Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',27,'Email,Just a demo message,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\"','2019-07-01 09:07:47',17),(104,39,20,'Prospects','addressbook/Prospects',14,'Folder,Prospects,addressbook/Prospects','2019-07-04 07:41:24',15),(105,7,10,'Read Only','Prospects',16,'Contact,Read Only,Prospects,ebab8d17-c68e-5c5e-9dce-76f70b326b3f,Read,Only,M,Dear Read','2019-07-04 07:41:36',15),(106,1,10,'Newsletter','',31,'Adreslijst,Newsletter,Geachte heer/mevrouw','2019-07-08 18:24:54',119),(107,2,10,'Release notes','',31,'Adreslijst,Release notes,Geachte heer/mevrouw','2019-07-08 18:25:36',120),(109,5,25,'test','From: \"Demo User\" <demo@group-office.com>\nTo: \"Demo User\" <demo@group-office.com>',27,'Email,test,From: \"Demo User\" <demo@group-office.com>\nTo: \"Demo User\" <demo@group-office.com>,@1562249681,\"Demo User\"','2019-07-11 09:10:24',111),(110,6,25,'test','From: \"Alexander Hu\" <hu.alexander@web.de>\nTo: linda@group-office.com',27,'Email,test,From: \"Alexander Hu\" <hu.alexander@web.de>\nTo: linda@group-office.com,@1531483030,\"Alexander Hu\"','2019-07-11 09:11:37',111),(112,42,20,'foo','calendar/foo',14,'Folder,foo,calendar/foo','2019-07-18 07:15:25',129),(113,43,20,'admin','users/admin',14,'Folder,admin,users/admin','2019-07-18 07:22:41',130),(114,44,20,'Reports','users/admin/Reports',14,'Folder,Reports,users/admin/Reports','2019-07-18 07:22:41',130),(115,7,15,'Dit is een test','',26,'Comment,Dit is een test,17','2019-07-18 11:30:08',40),(116,8,15,'Test bij een bedrijf','',26,'Comment,Test bij een bedrijf,17','2019-07-18 11:30:26',40),(117,4,24,'t1','| Default | Demo/t1',22,'Project,t1, | Default | Demo/t1,Demo/t1,1','2019-08-13 14:52:05',52),(118,45,20,'S','addressbook/Customers/contacts/S',14,'Map,S,addressbook/Customers/contacts/S','2019-10-10 10:20:04',17),(119,46,20,'John Smith (1)','addressbook/Customers/contacts/S/John Smith (1)',14,'Map,John Smith (1),addressbook/Customers/contacts/S/John Smith (1)','2019-10-10 10:20:04',17),(120,9,20,'Hi Hubert.eml','addressbook/Customers/contacts/S/John Smith (1)/Hi Hubert.eml',21,'Bestand,Hi Hubert.eml,addressbook/Customers/contacts/S/John Smith (1)/Hi Hubert.eml,eml','2019-10-10 10:20:09',17),(121,7,25,'Hi Hubert','From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost',27,'E-mail,Hi Hubert,From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost,@1570435819,\"Intermesh\"','2019-10-10 10:20:20',17),(122,8,25,'test image attachment','From: \"System Administrator\" <admin@intermesh.localhost>\nTo: \"Merijn Schering\" <admin@intermesh.localhost>',27,'E-mail,test image attachment,From: \"System Administrator\" <admin@intermesh.localhost>\nTo: \"Merijn Schering\" <admin@intermesh.localhost>,@1567515504,\"System Administrator\"','2019-10-10 10:21:15',17),(123,47,20,'Demo','projects2/Demo',14,'Map,Demo,projects2/Demo','2019-10-10 12:00:15',111),(124,48,20,'[001] Develop Rocket 2000','projects2/Demo/[001] Develop Rocket 2000',14,'Map,[001] Develop Rocket 2000,projects2/Demo/[001] Develop Rocket 2000','2019-10-10 12:00:15',111),(125,10,20,'lang.csv','projects2/Demo/[001] Develop Rocket 2000/lang.csv',21,'Bestand,lang.csv,projects2/Demo/[001] Develop Rocket 2000/lang.csv,csv','2019-10-10 12:00:22',111),(126,5,24,'Hi Hubert','ACME Corporation | Default | Hi Hubert',22,'Project,Hi Hubert,ACME Corporation | Default | Hi Hubert,ACME Corporation,System Administrator (Users),1','2019-10-14 10:11:16',52),(127,9,25,'Hi Hubert','From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost',27,'E-mail,Hi Hubert,From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost,@1570435819,\"Intermesh\"','2019-10-14 10:11:17',52),(128,9,29,'ghjgj','',24,'Taak,ghjgj,a5e3930e-4249-50ea-ac4b-4e00601dc116,NEEDS-ACTION','2019-10-31 15:56:07',106),(129,8,10,'piet test','Prospects',16,'Contactpersoon,piet test,Prospects,f5d69d89-114f-5333-a972-b88f6de70969,piet,test,M,Dear piet','2019-10-31 15:57:06',15),(130,22,14,'mnbmhb (31-10-2019, System Administrator)','',19,'Afspraak,mnbmhb (31-10-2019, System Administrator),@1572537600,d4153d3b-0988-59af-b3f9-d2129564ef76,Europe/Amsterdam,mnbmhb,CONFIRMED,EBF1E2','2019-10-31 15:57:26',70),(131,10,29,'t1','',24,'Taak,t1,78738742-a351-5271-b1aa-b850902b6dc3,NEEDS-ACTION','2019-10-31 15:58:41',106),(132,11,29,'t3','',24,'Taak,t3,5bcf1d4c-2a23-57b9-a3b0-e2b723ffb6d3,NEEDS-ACTION','2019-10-31 16:00:03',106),(133,12,29,'ghjgjhgjh1','',24,'Taak,ghjgjhgjh1,6872de0c-9e71-53c2-9611-03a438197e19,NEEDS-ACTION','2019-10-31 16:00:54',106),(134,13,29,'t4','',24,'Taak,t4,4c2934cd-16dc-5925-9763-854eeea13dc5,NEEDS-ACTION','2019-10-31 16:09:23',106);
/*!40000 ALTER TABLE `core_search` ENABLE KEYS */;
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
INSERT INTO `core_setting` VALUES (1,'databaseVersion','6.3.84'),(1,'debugEmail',NULL),(1,'defaultAuthenticationDomain',NULL),(1,'language','en'),(1,'locale','af_ZA.UTF-8'),(1,'loginMessage','Thank you for trying Group-Office! You can login with:<br><ul><li>Username: demo, Password: demo</li><li>Username: elmer, Password: demo</li><li>Username: linda, Password: demo</li></ul><br>Select your language below to make sure the demo starts in your language.<br>This demo is reset every day at midnight Central European Time.'),(1,'loginMessageEnabled',''),(1,'logoId',NULL),(1,'maintenanceMode',''),(1,'passwordMinLength','6'),(1,'primaryColor',NULL),(1,'smtpEncryption','tls'),(1,'smtpEncryptionVerifyCertificate','1'),(1,'smtpHost','localhost'),(1,'smtpPassword',NULL),(1,'smtpPort','587'),(1,'smtpUsername',NULL),(1,'systemEmail','admin@intermesh.dev'),(1,'title','Group-Office'),(1,'URL','https://localhost:63/');
/*!40000 ALTER TABLE `core_setting` ENABLE KEYS */;
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
  `displayName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT '',
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
  `theme` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default',
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
  `popup_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_emails` tinyint(1) NOT NULL DEFAULT 0,
  `holidayset` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_email_addresses_by_time` tinyint(1) NOT NULL DEFAULT 0,
  `no_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
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
INSERT INTO `core_user` VALUES (1,'admin','System Administrator',NULL,1,'admin@intermesh.localhost','admin@intermesh.localhost',NULL,NULL,'2019-11-01 11:32:58','2019-07-01 09:06:23','2019-11-01 11:32:58','d-m-Y',1,'G:i','.',',','€',37,20,'Europe/Amsterdam','summary','nl','Default',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,382096,0,0,0,NULL,0,0,0,0),(2,'elmer','Elmer Fudd',NULL,1,'elmer@group-office.com','elmer@acmerpp.demo',NULL,NULL,NULL,'2019-07-01 09:07:35','2019-07-01 11:07:48','d-m-Y',1,'G:i','.',',','€',0,30,'Europe/Amsterdam','summary','en','Paper',1,'displayName',1,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'en',0,0,0,0),(3,'demo','Demo User',NULL,1,'demo@group-office.com','demo@acmerpp.demo',NULL,NULL,NULL,'2019-07-01 09:07:36','2019-07-01 11:07:47','d-m-Y',1,'G:i','.',',','€',0,30,'Europe/Amsterdam','summary','en','Paper',1,'displayName',1,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'en',0,0,0,0),(4,'linda','Linda Smith',NULL,1,'linda@group-office.com','linda@acmerpp.demo',NULL,NULL,NULL,'2019-07-01 09:07:37','2019-07-01 11:07:49','d-m-Y',1,'G:i','.',',','€',0,30,'Europe/Amsterdam','summary','en','Paper',1,'displayName',1,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'en',0,0,0,0),(6,'foo@intermesh.localhost','foo',NULL,1,'foo@intermesh.localhost','foo@intermesh.localhost',NULL,NULL,'2019-07-18 07:16:31','2019-07-18 07:15:24','2019-07-18 07:16:31','d-m-Y',1,'G:i','.',',','€',2,20,'Europe/Amsterdam','summary','en_uk','Default',1,'first_name',0,0,0,0,1,0,';','\"',0,NULL,0,0,0,0,NULL,0,0,0,0);
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
INSERT INTO `core_user_custom_fields` VALUES (2),(3),(4);
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
INSERT INTO `core_user_default_group` VALUES (2);
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
INSERT INTO `core_user_group` VALUES (1,1),(2,1),(2,2),(2,3),(2,4),(2,6),(3,2),(3,3),(3,4),(3,6),(4,1),(5,2),(6,3),(7,4),(9,6);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts`
--

LOCK TABLES `em_accounts` WRITE;
/*!40000 ALTER TABLE `em_accounts` DISABLE KEYS */;
INSERT INTO `em_accounts` VALUES (1,3,113,NULL,'imap.group-office.com',143,0,0,'demo@group-office.com','{GOCRYPT2}def50200e98d0636001956342ad2325748ba44e412d3d6899825cbd801496797b88c8bae22185babe846fff61b2436b3cba2eeaf7736196b729628a95eeaf6ff44a1c33d5ecac3e02aa709d71eb4a6ea1a9122357470cd34c4ce4ea1','tls',1,'','Sent','Drafts','Trash','Spam','smtp.group-office.com',587,'tls',0,'','',2,0,4190,1,'INBOX',0,0,0),(2,2,114,NULL,'imap.group-office.com',143,0,0,'elmer@group-office.com','{GOCRYPT2}def502000d03ba9b03d08744b04862883944c924a8e85c9e37b3d7300e66f3182336c798d2d86ad7db9559a9a9562b287afa751c435bbbb361b944fe7fb78fcd9313eda1dc43738ccce61b7de00290cc5508574a343ebdbea9701515','tls',1,'','Sent','Drafts','Trash','Spam','smtp.group-office.com',587,'tls',0,NULL,'',2,0,4190,1,'INBOX',0,0,0),(3,4,115,NULL,'imap.group-office.com',143,0,0,'linda@group-office.com','{GOCRYPT2}def5020084f74e63b9d75c6a3246093a961163283b117a95809297f43408e94e4257de33c896fc5c7eed81c220160e205a8da32d77607d4bedd65b917cda5ceadeeb6c8928a2852386a0da641d3af966b5899cb6f754c3b4871d3e58','tls',1,'','Sent','Drafts','Trash','Spam','smtp.group-office.com',587,'tls',0,NULL,'',2,0,4190,1,'INBOX',0,0,0),(4,1,118,NULL,'mailserver',143,0,0,'admin@intermesh.localhost','{GOCRYPT2}def502008e24c8b8e3bc1847ab80ca5a89b1515ebc866ab68b107981ed0e7eb685ebb633a2f9d646031202b03ab5668f8b4d4ef0147afbe50d89711b0ab7db82009267b6fa4c4a0233eb656ebe0f899a22dff0ffb6c10744830195150ff6','',0,'','Sent','Drafts','Trash','Spam','localhost',25,'',0,'','',2,0,4190,1,'INBOX',0,0,0),(6,6,128,NULL,'mailserver',143,0,0,'foo@intermesh.localhost','{GOCRYPT2}def50200c2a8afd349706d94ca12fc974b045764740c4c0d50d10a40f4360116a56739a7b462875b1b94d873138be7e7917ff37007fd3b68931111f9bbc2c7daa7e5128bafe7e9f2b53baee03aa0268c47d947fd1d7b08c49c6c','',1,'','Sent','Drafts','Trash','Spam','mailserver',25,'',1,'','',2,0,4190,1,'INBOX',0,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_aliases`
--

LOCK TABLES `em_aliases` WRITE;
/*!40000 ALTER TABLE `em_aliases` DISABLE KEYS */;
INSERT INTO `em_aliases` VALUES (1,1,'Demo User','demo@group-office.com','',1),(2,2,'Elmer Fudd','elmer@group-office.com','',1),(3,3,'Linda Smith','linda@group-office.com','',1),(4,4,'Admin','admin@intermesh.localhost','',1),(6,6,'foo','foo@intermesh.localhost','',1);
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
  PRIMARY KEY (`contact_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_contacts_last_mail_times`
--

LOCK TABLES `em_contacts_last_mail_times` WRITE;
/*!40000 ALTER TABLE `em_contacts_last_mail_times` DISABLE KEYS */;
INSERT INTO `em_contacts_last_mail_times` VALUES (5,1,1562249681);
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_labels`
--

LOCK TABLES `em_labels` WRITE;
/*!40000 ALTER TABLE `em_labels` DISABLE KEYS */;
INSERT INTO `em_labels` VALUES (1,'Label 1','$label1','7A7AFF',1,1),(2,'Label 2','$label2','59BD59',1,1),(3,'Label 3','$label3','FFBD59',1,1),(4,'Label 4','$label4','FF5959',1,1),(5,'Label 5','$label5','BD7ABD',1,1),(6,'Label 1','$label1','7A7AFF',2,1),(7,'Label 2','$label2','59BD59',2,1),(8,'Label 3','$label3','FFBD59',2,1),(9,'Label 4','$label4','FF5959',2,1),(10,'Label 5','$label5','BD7ABD',2,1),(11,'Label 1','$label1','7A7AFF',3,1),(12,'Label 2','$label2','59BD59',3,1),(13,'Label 3','$label3','FFBD59',3,1),(14,'Label 4','$label4','FF5959',3,1),(15,'Label 5','$label5','BD7ABD',3,1),(16,'Label 1','$label1','7A7AFF',4,1),(17,'Label 2','$label2','59BD59',4,1),(18,'Label 3','$label3','FFBD59',4,1),(19,'Label 4','$label4','FF5959',4,1),(20,'Label 5','$label5','BD7ABD',4,1),(26,'Label 1','$label1','7A7AFF',6,1),(27,'Label 2','$label2','59BD59',6,1),(28,'Label 3','$label3','FFBD59',6,1),(29,'Label 4','$label4','FF5959',6,1),(30,'Label 5','$label5','BD7ABD',6,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_links`
--

LOCK TABLES `em_links` WRITE;
/*!40000 ALTER TABLE `em_links` DISABLE KEYS */;
INSERT INTO `em_links` VALUES (1,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_5d19cd632a254.eml/demo.eml',1561972067,1561972067,1,17,'<1368777188.5195e1e479413@localhost>'),(2,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_5d19cd633af8d.eml/demo.eml',1561972067,1561972067,1,17,'<1368777188.5195e1e479413@localhost>'),(3,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_5d19cd63486b5.eml/demo2.eml',1561972067,1561972067,1,17,'<1368777986.5195e5020b17e@localhost>'),(4,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_5d19cd6354ce5.eml/demo2.eml',1561972067,1561972067,1,17,'<1368777986.5195e5020b17e@localhost>'),(5,1,'\"Demo User\" <demo@group-office.com>','\"Demo User\" <demo@group-office.com>','test',1562249681,'email/1/1642_1562249681.eml',1562836224,1562836224,1,111,'<bf39601e1bb9fb8b1626175afa1db673@localhost>'),(6,4,'\"Alexander Hu\" <hu.alexander@web.de>','linda@group-office.com','test',1531483030,'email/3/3263_1531483030.eml',1562836297,1562836297,4,111,'<!&!AAAAAAAAAAAYAAAAAAAAAENzv3v0LbBEsQn1midpPTjCgAAAEAAAABNm4QSxPCRClBCd7ODYwJkBAAAAAA==@web.de>'),(7,1,'\"Intermesh\" <admin@intermesh.localhost>','admin@intermesh.localhost','Hi Hubert',1570435819,'email/4/777_1570435819.eml',1570702820,1570702820,1,17,'<2311b1b961ae168315d64a6a7879d459@office.group-office.com>'),(8,1,'\"System Administrator\" <admin@intermesh.localhost>','\"Merijn Schering\" <admin@intermesh.localhost>','test image attachment',1567515504,'email/4/54_1567515504.eml',1570702875,1570702875,1,17,'<cec5d0203e766866cdaef2efe4427306@office.group-office.com>'),(9,1,'\"Intermesh\" <admin@intermesh.localhost>','admin@intermesh.localhost','Hi Hubert',1570435819,'email/4/777_1570435819.eml',1571047877,1571047877,1,52,'<2311b1b961ae168315d64a6a7879d459@office.group-office.com>');
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
  PRIMARY KEY (`folder_id`,`user_id`)
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `folder_id_2` (`folder_id`,`name`),
  KEY `folder_id` (`folder_id`),
  KEY `name` (`name`),
  KEY `extension` (`extension`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_files`
--

LOCK TABLES `fs_files` WRITE;
/*!40000 ALTER TABLE `fs_files` DISABLE KEYS */;
INSERT INTO `fs_files` VALUES (1,17,'Demo letter.docx',0,0,1561972054,1561972054,1,4312,1,NULL,'docx',0,NULL,0,NULL),(2,25,'noperson.jpg',0,0,1561972065,1561972065,1,3015,1,NULL,'jpg',0,NULL,0,NULL),(3,25,'empty.docx',0,0,1561972065,1561972065,1,3726,1,NULL,'docx',0,NULL,0,NULL),(4,25,'wecoyote.png',0,0,1561972066,1561972065,1,39495,1,NULL,'png',0,NULL,0,NULL),(5,25,'Demo letter.docx',0,0,1561972066,1561972065,1,4312,1,NULL,'docx',0,NULL,0,NULL),(6,25,'empty.odt',0,0,1561972066,1561972065,1,6971,1,NULL,'odt',0,NULL,0,NULL),(7,12,'project.png',0,0,1561972066,1546942592,1,3231,1,NULL,'png',0,NULL,0,NULL),(8,12,'folder.png',0,0,1561972067,1546942592,1,611,1,NULL,'png',0,NULL,0,NULL),(9,46,'Hi Hubert.eml',0,0,1570702809,1570702809,1,96437,1,NULL,'eml',0,NULL,0,NULL),(10,48,'lang.csv',0,0,1570708822,1570708822,1,219986,1,NULL,'csv',0,NULL,0,NULL);
/*!40000 ALTER TABLE `fs_files` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folders`
--

LOCK TABLES `fs_folders` WRITE;
/*!40000 ALTER TABLE `fs_folders` DISABLE KEYS */;
INSERT INTO `fs_folders` VALUES (1,1,0,'project_templates',0,57,NULL,1,1561971989,1561971989,1,1,1,NULL,0),(1,2,1,'Projects folder',0,57,NULL,1,1561971989,1561971989,1,1,1,NULL,0),(1,3,1,'Standard project',0,58,NULL,1,1561971989,1561971989,1,1,1,NULL,0),(1,4,0,'tickets',0,65,NULL,1,1561971990,1561971990,1,1,1,NULL,0),(1,5,4,'0-IT',0,65,NULL,1,1561971990,1561971990,1,1,1,NULL,0),(1,6,4,'0-Sales',0,66,NULL,1,1561971990,1561971990,1,1,1,NULL,0),(1,7,0,'billing',0,45,NULL,1,1561972007,1561972064,1,1,1,NULL,0),(1,8,7,'stationery-papers',0,0,NULL,1,1561972007,1561972007,1,1,0,NULL,0),(1,9,0,'calendar',0,70,NULL,1,1561972007,1563434125,6,1,1,NULL,0),(1,10,9,'System Administrator',0,70,NULL,1,1561972007,1561972007,1,1,1,NULL,0),(1,11,0,'projects2',0,45,NULL,1,1561972008,1570708815,1,1,1,NULL,0),(1,12,11,'template-icons',0,51,NULL,1,1561972008,1546942595,1,1,0,NULL,0),(1,13,0,'addressbook',0,17,NULL,1,1561972054,1562226084,1,1,1,NULL,0),(1,14,13,'Customers',0,17,NULL,1,1561972054,1561972054,1,1,1,NULL,0),(1,15,14,'contacts',0,17,NULL,1,1561972054,1570702804,1,1,1,NULL,0),(1,16,15,'C',0,17,NULL,1,1561972054,1561972054,1,1,1,NULL,0),(1,17,16,'Wile E. Coyote (2)',0,17,NULL,1,1561972054,1561972055,1,1,1,NULL,0),(1,18,13,'Users',0,86,NULL,1,1561972055,1561972055,1,1,1,NULL,0),(1,19,0,'users',0,45,NULL,1,1561972055,1563434561,1,1,1,NULL,0),(2,20,19,'elmer',1,88,NULL,1,1561972055,1561972055,1,1,1,NULL,0),(1,21,13,'Elmer Fudd',0,89,NULL,1,1561972056,1561972056,1,1,1,NULL,0),(1,22,9,'Elmer Fudd',0,90,NULL,1,1561972056,1561972056,1,1,1,NULL,0),(1,23,0,'tasks',0,91,NULL,1,1561972056,1561972061,1,1,1,NULL,0),(1,24,23,'Elmer Fudd',0,91,NULL,1,1561972056,1561972056,1,1,1,NULL,0),(3,25,19,'demo',1,93,NULL,1,1561972056,1549480432,1,1,1,NULL,0),(1,26,13,'Demo User',0,94,NULL,1,1561972057,1561972057,1,1,1,NULL,0),(1,27,9,'Demo User',0,95,NULL,1,1561972057,1561972057,1,1,1,NULL,0),(1,28,23,'Demo User',0,96,NULL,1,1561972057,1561972057,1,1,1,NULL,0),(4,29,19,'linda',1,98,NULL,1,1561972057,1561972058,1,1,1,NULL,0),(1,30,13,'Linda Smith',0,99,NULL,1,1561972058,1561972058,1,1,1,NULL,0),(1,31,9,'Linda Smith',0,100,NULL,1,1561972058,1561972058,1,1,1,NULL,0),(1,32,23,'Linda Smith',0,101,NULL,1,1561972058,1561972058,1,1,1,NULL,0),(1,33,9,'Road Runner Room',0,104,NULL,1,1561972061,1561972061,1,1,1,NULL,0),(1,34,9,'Don Coyote Room',0,105,NULL,1,1561972061,1561972061,1,1,1,NULL,0),(1,35,23,'System Administrator',0,106,NULL,1,1561972061,1561972061,1,1,1,NULL,0),(1,36,7,'Quotes',0,22,NULL,1,1561972062,1561972062,1,1,1,NULL,0),(1,37,7,'Orders',0,27,NULL,1,1561972063,1561972063,1,1,1,NULL,0),(1,38,7,'Invoices',0,32,NULL,1,1561972064,1561972064,1,1,1,NULL,0),(1,39,13,'Prospects',0,15,NULL,1,1562226084,1562226084,1,1,1,NULL,0),(6,42,9,'foo',0,129,NULL,1,1563434125,1563434125,6,1,1,NULL,0),(1,43,19,'admin',1,130,NULL,1,1563434561,1563434561,1,1,1,NULL,0),(1,44,43,'Reports',0,0,NULL,1,1563434561,1563434561,1,1,0,NULL,0),(1,45,15,'S',0,17,NULL,1,1570702804,1570702804,1,1,1,NULL,0),(1,46,45,'John Smith (1)',0,17,NULL,1,1570702804,1570702809,1,1,1,NULL,0),(1,47,11,'Demo',0,111,NULL,1,1570708814,1570708815,1,1,1,NULL,0),(1,48,47,'[001] Develop Rocket 2000',0,111,NULL,1,1570708815,1570708822,1,1,1,NULL,0);
/*!40000 ALTER TABLE `fs_folders` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
INSERT INTO `fs_templates` VALUES (1,1,'Microsoft Word document',46,'PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"����S����Bk�RbG����	+��73z��+E��\"#��f�� �<�t�p>�0��������l7����%�>�����jn����)Ȃ3ReW.)h��f\'.C.ܣH��h�έl\n#AW/?��Lm��#i�i��\ZQO�rTε����m��]�/PKe��\"�\0\0\0�\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xml���N�0��<E�;K�Mպ		q�\0���]#%q���=Y۝(�@�%��������Y�c`C���B\n��j�O��8��o���K+9 ���n�ʆ|d��=���m�]����:���Pp�7�5���L�ӡ�j]�*����ܚ��얮qK�.�F����ρ�r7����r�q���x#�@�Ϛl%�B�q��å\ZF���L����C0p�xn�	��>�#�E��֬�,YF-���0�u�-77���-�������7PK��Z]\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�TQo�0~߯��Nh�5T��	��н�A�9��s\Z诟�j��x������;���6�\Z�JF�s�f�X%\\n\"��|l�Y@dBI���=���;��xI�\"b��z����(��ݭ�����ل�2�6*F\"�>a���pɆu Z�I�ʍ+�؝v��``c@�����{����\\C.l��,,�qk��*Qi׿�_\n��b��5J�nkTES��qu�r�&;����˚�t\n�r�jyt�P�yĖ<s�̱�T��)b3�rL�L�7!���΃H��Hґ,ey����������u�֖1��	�g[�ĥOyk:?��5m�KS������@�J�Ӓu�5�l��l_�\n���`���������g�WC�Kx��h\\Gw�Z�kD��k�uA9��[�a|���p���}/Z��h�3��5�~�9������F��t����	�\'{\Zl\r�#���О��p�d�&\0Ơ����?��l3.q�g+7��������wL�g���5�v*�^��]f�l�zG�PKՔq�\0\0�\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�R�n�0��+\"���T�F.��VH�0�&�d{#{!Я�M�K�ś�̾&��\\�N��B���4e	X���Uξ�%K<	[�rv�6�U�(O,%������l�e\rF��Qҡǒ&M�e�$��.g5Q�q�\'M�����x���{��4]pZP��ת�C����F����-��q(��`��]_#����\'�uƌ�΅�C˿�l;����G,�16�g�naOW\rI���Ι� e|�⣢{�7��\"R$uJ�6c%ډ\nb� ��?��ù,��pm4u6{O��S�$�H�&R�*	�_O#�Nt5�b\ZJ�\'9U����A���dݨ�	��2Bwl4v理=J�}����ʅu�!\r�v�c��`����_�PK���ly\0\06\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��TcL�BC�h�t3�tN��sp�}�����xx��v�Sq���wy�)�)��;v�)פH(���wБ+$��-�\0\r�\".u�6�&u+S�c���G+1��H�8\Z�^�Қ���4�2���W�\\���_��!{�����qz�S�Q��bo^4�T���z�7n^��u;�Mq�0�gPH�,[�f3�������#�PK(��\0\0\0h\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xmlm�[O�0���Kﷶ D�m\\h���D���������2���&\Z���}��𖋽�8/��-�@�H�V�u���P��\r\n��E}S\n˄q��$�,��g�Vh�e{��}	��qt-�\\|����9Vx��I��шN�F�J���A�(��cZP��*��8�t\0��UxHFr��H�}_�Ӂ����}��2<5�:}�\0T�\'5x�&�v��9y�><����:��,��5%lv�ncdS���<���W���ҹ.V��1K�8��TiMJ|9����PK��i\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0H�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0H�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0H�Be��\"�\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0H�B��Z]\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0H�BՔq�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0F\0\0word/styles.xmlPK\0\0\0H�B���ly\0\06\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0word/document.xmlPK\0\0\0H�B(��\0\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0S\0\0docProps/app.xmlPK\0\0\0H�B��i\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0s	\0\0docProps/core.xmlPK\0\0\0H�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\n\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0<\0\0\0\0','docx'),(2,1,'Open-Office Text document',47,'PK\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xml\0PK\0\0\0\0\0\0\0\0\0\0\0PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml�V�n!��+F,�c\']$S��JQ�JIMZuK��iyL���_�1N2	�7���s��s/ov�[�\rS���TbE�\\������\nܬ>,U�0L+�p\'��%VҺ�±���l\r:-+�3�D����J�T�*EWa�1vϳ���-��\\�Ǟp�S�����F}.�c��)�Q���e���E�=�bǙ�[���ma����r��\Z.���a��#��4(�!��/f�b��VP�r��ش$ى\'���A�p�l���]OH�7Hg�F\0��{I���$)W ����\n޻��qw�-r�����fm�6#:�+��R=!�P��|�	�q��߄��Y�8~�ǣ�J�&��-�C�t��tl|/�� \\�8=�\r�L����o�@G0{\\2i,�Ge\ZF��0�F^�]K5�6 �4�Q�q)�T���љ9��`5\\��B�@��A���bnV�x��pǾ�\Z<�2L���%��;?T0���G�*��.Aq����5�no�e}�wD��bw�H3y�vi_��R��^��̘s���Я�x@�L*�`2�0{c�x�&8e�!�:�T��!Ob��\"�q���2<o����>&z,B�0�r�/Ug�AK�N��k�0e��yg�v(��=+���󲸟g\'���qp`Z�6R��e$>�#O�&�wQ|x���˴�PK�\0=@�\0\0s	\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�YK��6��W*�m˻����E�����׀�h�	E\n$e�����D˒W�H�=, �p��<���7��MvD**�]O�ф�D��o�?��n�7�?��\rM�*I����F�6s�rĻ��|%��j�qN�J\'+Q�7�B�U�V����-s�[���������-s�;��\Z����M��1v�^1�(y�5���3ʿ�E���j6��jZ]M�����r9��p���d�+Mf��L��i<�9�x,>�B�e�&r�i��\'^U����mL�dX��\r�|�ޫt�{��po�u6����; ��޶� �I���t��~!D�lp	j�.�������앤�Ȁ=9˞`�4y�р/�\";�Ѥ.!Aي�{_�6��\'�$a�����fy⾍�,5yO��?\"�<�@0y֜��]�3.������ɑhÏ��I�Ȳ��rT\';,��$��<�߀��\0��êUE�z��?�g�_9���\Z�H�3�\Z�4��4ra�Ǟ�\r.Y݋��\Z�V�\"�I�y�oTH�A�)8�T��p**��hͧW	��!:D\r�Au%H8�ڎ2!�W���a]ܞe��)+$�X�\'�=2k�08GEu�\\��`��((���B�}��#\\jat@hДǊY�a���XK��)\r.מbʁ����3���((O��R�Vƃ�����E�L��n�\r�Ӕ����U�&�OiYB}��Hѯ�4^ڮ1̷%��gv!%�������DC�C_������DP���V��M��ǋ�ԯ��\'Պ<��#�t>F�pGhC��Ь�֬Gy5&�\Z?Dg\nL���phԂ#��lf����6\'wE�]:���}����(�RHOn����M�f�q�`�6e�_������@��G�q}��Qj7�vV�K�rj��n�I�cʑ��� \\�0��:,�H��+\Z#a��ZH�&蠐C1\\(��U���:�a���_)�[�3s�7���P���O)�i4X(��V\n�A2��u*�/�� ���B�8E�P��ې�#,|Z�?�Ez��PI˱�z&+L׽^خۮ����V\r9^�$kcۍ��ƘU���-Aᨯ��rq����2�W�c3�i�@�3>���C��IH~�����>}ܷ�i�r�31ʌ��s���c|�;.L)|A�J��Xm�%����Ƀ�H���[�����|8�z�[s�*\nwqx[�5�њ��m��}���^����n�A�Q+��^��\n_+Y�ڽ!z��VwG�,���5�92�%�9�ɯzk��95��5P�$�o�$i�kq���.���⺹P\\�.�/���Bq-/W<����B�\\h����\rݖ�>�&\rխm#�6�}��y�Q��Ҡ��F�\n���Ci;/���g\nF�˛�GHx:����EZ}j������rَG��Si���F�4�ih251��Zi��<9A&M�\'������ݣ�M��QO�Ng)M��2��t��	��̼��Wg�X+j$$�����B��{q��v���Ne\rih���\0D9�7�1ϖv�_3(Rxq�\Z��<�m���Ck\'���\'��=<xc�N},8�\\*���b��KHV��1����8�Q8��s�?TF��؏Yx�`�TPy��Vr�\Z��z�H:;�1�!\Z|G������oPK�E�}\0\0�\0\0PK\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0meta.xml<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<office:document-meta xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" office:version=\"1.1\"><office:meta><meta:generator>OpenOffice.org/2.4$Linux OpenOffice.org_project/680m17$Build-9310</meta:generator><meta:initial-creator>Merijn Schering</meta:initial-creator><meta:creation-date>2008-08-26T09:26:02</meta:creation-date><meta:editing-cycles>0</meta:editing-cycles><meta:editing-duration>PT0S</meta:editing-duration><meta:user-defined meta:name=\"Info 1\"/><meta:user-defined meta:name=\"Info 2\"/><meta:user-defined meta:name=\"Info 3\"/><meta:user-defined meta:name=\"Info 4\"/><meta:document-statistic meta:table-count=\"0\" meta:image-count=\"0\" meta:object-count=\"0\" meta:page-count=\"1\" meta:paragraph-count=\"0\" meta:word-count=\"0\" meta:character-count=\"0\"/></office:meta></office:document-meta>PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Thumbnails/thumbnail.png��s���b``���p	�[8؀������{�8�T�y{i#\'�ρ\r|?�?���t�C��Ûw��~�2��9K&xrrV��o�ʓ����Ԏ_y2cTpTp�����3\n�*L���~.��\0PK�׃�|\0\0\0�\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�YQs�8~�_��;Jo�ʴ��챥����7��ձ2�S�ߟ�N�K	~�Mlɒ�믫����T��~^��@Fb~�=N������?�q6�h��1]Q�5-Qg�]�f���K�h\"S�j\n�jꠉ	�Ͷ���M�,{��x��Z\'�ju�\\�/�(�����Uվ�,\rP̢�����oU!�\"�!;�UvQ�]V������o\\S�Z?l�o]�\n��J�!6�9����x������k^Ѿ�{~�z_�`�m��uBo\"��V���+�p�}��\"��z�^����(ԋ\"ᗍ�Ɵ�d��|Qx�z��H��.GR�Ag��Ԗ�)\"&���)��\'��\n�1�}�g����Wb�T\"�\n�]_G��C�!ׇy�nUiI��L0_����Q����\'S�Uє��\\��O��V�h_�4\Z��/�D�Qk���_�8ٿ�	Iڎ���}a�vY�Q��׎�Sc�h�����&��y�/ b9����d���3���I6a\n�N��C�=û��?�A�{��T���o<Ti<��1%���ryLB����%�-N�z��p�\\��n$�&3�G(tO�t񯤬��8��L�url���~�t��J���xW�A�p��Q�Y���N����P�[��f.|e�0�(���n(QlS<��z[S��	&�^��#[tp���]��D{��~�1QGa�A�2�N�C�A��\'o1�7�F勰͙xV�t�tƃ�[zt������p$����#`!\n��AS��?A��.n�t}[�u��d��z�O�~T o�f��5%�˄���\r)��Q����U����|�b\nt�Swt߾���\"Щ�4�y4�c��U����o�ã�W�e�/�m�$-�]�����TJ�&���S�`M����������J��l9���°���[T���EY����nw�?��.[�BL���V�I�d ȭ�.�R��1pV�����D��^�ȍ�L���M�[�I߶Ӿk*�N�W�6����ɂ��W0��ZKSTQ��E\'W�uAUB5�+���_���D��9.��D;L�p�{*��:f\",h�����H��fk�6�)ҋ{&R��س�P#0u�L0O8�f��M��&}rt6��oY��bkL������	\nyj�T�\\Nu�Q��,x�KL���ީ����2��F����t��нXQ��$R�����j;o��|���L��PKt����\0\0h\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml��Kj�0@�=���V�U1q-��&���f��W��6��X;	��F#�h��[S�0���Oͣ��)�k7v�c�^����aa����Ӡ�����HѵHS��\"��Z��^%��ۯ��ɴ|�.�A���x�.2�5�|�	�h��;�7GWs�h�,.��dL����B�%�My�n�c�� �Y\'�@,���`��(U�q:b�bqW�`<0�R�O �G?F�r7=�^�ޛbpmaD���-*긓��_PrS�4I7�Z��O�HN�z����b��K|0H�c-2��x��d7�!ɧa�87|��\"s�ϩ]����PK5b�9>\0\0J\0\0PK\0\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Configurations2/statusbar/PK\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/floater/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0J\0\0Configurations2/progressbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/menubar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/toolbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/images/Bitmaps/PK\0\0\0\0K;\Z9�\0=@�\0\0s	\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0content.xmlPK\0\0\0\0K;\Z9�E�}\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0styles.xmlPK\0\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0meta.xmlPK\0\0\0\0K;\Z9�׃�|\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Thumbnails/thumbnail.pngPK\0\0\0\0K;\Z9t����\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0settings.xmlPK\0\0\0\0K;\Z95b�9>\0\0J\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0�\0\07\0\0\0\0','odt');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron`
--

LOCK TABLES `go_cron` WRITE;
/*!40000 ALTER TABLE `go_cron` DISABLE KEYS */;
INSERT INTO `go_cron` VALUES (1,'Calendar publisher',1,'0','*','*','*','*','*','GO\\Calendar\\Cron\\CalendarPublisher',0,1561975200,0,0,NULL,0,'[]'),(2,'Contract Expiry Notification Cron',1,'2','7','*','*','*','*','GO\\Projects2\\Cron\\IncomeNotification',0,1562050920,0,0,NULL,0,'[]'),(3,'Close inactive tickets',1,'0','2','*','*','*','*','GO\\Tickets\\Cron\\CloseInactive',0,1562032800,0,0,NULL,0,'[]'),(4,'Ticket reminders',1,'*/5','*','*','*','*','*','GO\\Tickets\\Cron\\Reminder',0,1561972200,0,0,NULL,0,'[]'),(5,'Import tickets from IMAP',1,'0,5,10,15,20,25,30,35,40,45,50,55','*','*','*','*','*','GO\\Tickets\\Cron\\ImportImap',0,1561972200,0,0,NULL,0,'[]'),(6,'Sent tickets due reminder',1,'0','1','*','*','*','*','GO\\Tickets\\Cron\\DueMailer',0,1562029200,0,0,NULL,0,'[]'),(7,'Email Reminders',1,'*/5','*','*','*','*','*','GO\\Base\\Cron\\EmailReminders',0,1561972200,0,0,NULL,0,'[]'),(8,'Calculate disk usage',1,'0','0','*','*','*','*','GO\\Base\\Cron\\CalculateDiskUsage',0,1562025600,0,0,NULL,0,'[]');
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
INSERT INTO `go_holidays` VALUES (1,'2019-01-01','New Years Day','en',1),(2,'2019-01-06','Twelfth Day','en',1),(3,'2019-05-01','May Day','en',1),(4,'2019-08-15','Assumption Day','en',1),(5,'2019-10-03','German Unification Day','en',1),(6,'2019-10-31','Reformation Day','en',1),(7,'2019-11-01','All Saints\' Day','en',1),(8,'2019-12-25','Christmas Day','en',1),(9,'2019-12-26','Boxing Day','en',1),(10,'2019-03-04','Shrove Monday','en',1),(11,'2019-03-05','Shrove Tuesday','en',1),(12,'2019-03-06','Ash Wednesday','en',1),(13,'2019-04-19','Good Friday','en',1),(14,'2019-04-21','Easter Sunday','en',1),(15,'2019-04-22','Easter Monday','en',1),(16,'2019-05-30','Ascension Day','en',1),(17,'2019-06-09','Whit Sunday','en',1),(18,'2019-06-10','Whit Monday','en',1),(19,'2019-06-20','Feast of Corpus Christi','en',1),(20,'2019-11-20','Penance Day','en',1);
/*!40000 ALTER TABLE `go_holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_link_descriptions`
--

DROP TABLE IF EXISTS `go_link_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_link_descriptions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_link_descriptions`
--

LOCK TABLES `go_link_descriptions` WRITE;
/*!40000 ALTER TABLE `go_link_descriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_link_descriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_link_folders`
--

DROP TABLE IF EXISTS `go_link_folders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_link_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`model_id`,`model_type_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_link_folders`
--

LOCK TABLES `go_link_folders` WRITE;
/*!40000 ALTER TABLE `go_link_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_link_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_ab_addresslists`
--

DROP TABLE IF EXISTS `go_links_ab_addresslists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_ab_addresslists` (
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
-- Dumping data for table `go_links_ab_addresslists`
--

LOCK TABLES `go_links_ab_addresslists` WRITE;
/*!40000 ALTER TABLE `go_links_ab_addresslists` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ab_addresslists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_ab_companies`
--

DROP TABLE IF EXISTS `go_links_ab_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_ab_companies` (
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
-- Dumping data for table `go_links_ab_companies`
--

LOCK TABLES `go_links_ab_companies` WRITE;
/*!40000 ALTER TABLE `go_links_ab_companies` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ab_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_ab_contacts`
--

DROP TABLE IF EXISTS `go_links_ab_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_ab_contacts` (
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
-- Dumping data for table `go_links_ab_contacts`
--

LOCK TABLES `go_links_ab_contacts` WRITE;
/*!40000 ALTER TABLE `go_links_ab_contacts` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ab_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_bs_orders`
--

DROP TABLE IF EXISTS `go_links_bs_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_bs_orders` (
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
-- Dumping data for table `go_links_bs_orders`
--

LOCK TABLES `go_links_bs_orders` WRITE;
/*!40000 ALTER TABLE `go_links_bs_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_bs_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_cal_events`
--

DROP TABLE IF EXISTS `go_links_cal_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_cal_events` (
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
-- Dumping data for table `go_links_cal_events`
--

LOCK TABLES `go_links_cal_events` WRITE;
/*!40000 ALTER TABLE `go_links_cal_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_cal_events` ENABLE KEYS */;
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
-- Table structure for table `go_links_pr2_projects`
--

DROP TABLE IF EXISTS `go_links_pr2_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_pr2_projects` (
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
-- Dumping data for table `go_links_pr2_projects`
--

LOCK TABLES `go_links_pr2_projects` WRITE;
/*!40000 ALTER TABLE `go_links_pr2_projects` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_pr2_projects` ENABLE KEYS */;
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
-- Table structure for table `go_links_ti_tickets`
--

DROP TABLE IF EXISTS `go_links_ti_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_ti_tickets` (
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
-- Dumping data for table `go_links_ti_tickets`
--

LOCK TABLES `go_links_ti_tickets` WRITE;
/*!40000 ALTER TABLE `go_links_ti_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ti_tickets` ENABLE KEYS */;
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
INSERT INTO `go_settings` VALUES (0,'go_addressbook_export','69'),(0,'projects_bill_item_template','{project_name}: {registering_user_name} worked {units} hours on {date}'),(0,'projects_detailed_printout_on','true'),(0,'projects_payout_item_template','{project_name}: {description} of {responsible_user_name} worked {units} hours in {days} days\n\nTotal: {total_price}. (You can use custom fields of the manager in this template with {col_x})'),(0,'projects_summary_bill_item_template','{project_name} {description} at {registering_user_name}\nUnits:{units}\nDays:{days}'),(0,'projects_summary_payout_item_template','{project_name} {description} of {responsible_user_name}\nUnits: {units}, Days: {days}'),(0,'tickets_bill_item_template','{date} #{number} rate: {rate_name}\n{subject}'),(0,'uuid_namespace','ffd75afa-f2e0-4a70-9b98-f3b6bce0b967'),(1,'email_accounts_tree','[\"root\",\"Zl8xX0lOQk9Y\",\"Zl8xX0lOQk9YLlNwYW0=\",\"Zl8xX0lOQk9YLmdpYW50\",\"Zl8xX0lOQk9YLlNlbnQ=\",\"Zl8xX0lOQk9YLnRlc3Q=\",\"Zl8xX0lOQk9YLnRlc3QuVGVzdE1haWw=\",\"Zl8xX0lOQk9YLlRlc3R5\",\"Zl8xX0lOQk9YLlRyYXNo\",\"Zl8xX1NlbnQ=\",\"Zl8xX0RyYWZ0cw==\",\"Zl8xX1RyYXNo\",\"Zl8xX1NwYW0=\",\"Zl8xX2RhamU=\",\"Zl8xX0hkZXNr\",\"Zl8xX0hlbGVu\",\"Zl8xX2h1c3RlbnNhZnQ=\",\"Zl8xX1Jvb3Q=\",\"Zl8xX1NBTEVTLUxlYWRz\",\"Zl8xX1RFU1QgQU4=\",\"Zl8xX3Rlc3QwMw==\",\"Zl8xX3Rlc3RhcmU=\",\"Zl8xX1RJQUdv\",\"Zl8xX1RvRG8=\",\"Zl8xX3Zwc2xhYl9vcmc=\",\"YWNjb3VudF80\",\"Zl80X0lOQk9Y\",\"Zl80X0lOQk9YL3Rlc3Q=\",\"Zl80X1NlbnQ=\",\"Zl80X1RyYXNo\",\"Zl80X1NwYW0=\",\"Zl80X09tc29yZ1xWYXJkT21zb3Jnc2tvbnRvciBOb3Jya8O2cGluZw==\",\"Zl80X3Rlc3Q=\"]'),(1,'email_always_request_notification','0'),(1,'email_always_respond_to_notifications','0'),(1,'email_font_size','14px'),(1,'email_show_bcc','0'),(1,'email_show_cc','0'),(1,'email_skip_unknown_recipients','0'),(1,'email_sort_email_addresses_by_time','0'),(1,'email_use_plain_text_markup','0'),(1,'GO\\Projects2\\Report\\ProjectsReport','[]'),(1,'GO\\Projects2\\Report\\ProjectsReportLarge','[]'),(1,'ms_3order_statuses','12,11,10,9'),(1,'ms_books','3,6,5,7,1,2,4'),(1,'ms_calendars','1'),(1,'ms_pm-status-filter','1,2,3'),(1,'ms_pr2_statuses',''),(1,'ms_ti-types-grid','1,2'),(1,'pr2_all_incomes_end','0'),(1,'pr2_all_incomes_start_date','0'),(1,'pr2_invoiceable_end','1568549984'),(1,'pr2_invoiceable_start_date','0'),(1,'projects2_tree_state','[\"root\",1,2,3,4,5]'),(2,'email_always_request_notification','0'),(2,'email_always_respond_to_notifications','0'),(2,'email_font_size','14px'),(2,'email_show_bcc','0'),(2,'email_show_cc','0'),(2,'email_skip_unknown_recipients','0'),(2,'email_sort_email_addresses_by_time','0'),(2,'email_use_plain_text_markup','0'),(2,'ms_books','5'),(2,'ms_ta-taskslists','1'),(2,'tasks_filter','active'),(3,'email_always_request_notification','0'),(3,'email_always_respond_to_notifications','0'),(3,'email_font_size','14px'),(3,'email_show_bcc','0'),(3,'email_show_cc','0'),(3,'email_skip_unknown_recipients','0'),(3,'email_sort_email_addresses_by_time','0'),(3,'email_use_plain_text_markup','0'),(4,'email_always_request_notification','0'),(4,'email_always_respond_to_notifications','0'),(4,'email_font_size','14px'),(4,'email_show_bcc','0'),(4,'email_show_cc','0'),(4,'email_skip_unknown_recipients','0'),(4,'email_sort_email_addresses_by_time','0'),(4,'email_use_plain_text_markup','0'),(4,'ms_pm-status-filter',''),(4,'ms_pr2_statuses',''),(4,'projects2_tree_state','[\"root\",1,2,3]'),(5,'email_always_request_notification','0'),(5,'email_always_respond_to_notifications','0'),(5,'email_font_size','14px'),(5,'email_show_bcc','0'),(5,'email_show_cc','0'),(5,'email_skip_unknown_recipients','0'),(5,'email_sort_email_addresses_by_time','0'),(5,'email_use_plain_text_markup','0'),(6,'email_always_request_notification','0'),(6,'email_always_respond_to_notifications','0'),(6,'email_font_size','14px'),(6,'email_show_bcc','0'),(6,'email_show_cc','0'),(6,'email_skip_unknown_recipients','0'),(6,'email_sort_email_addresses_by_time','0'),(6,'email_use_plain_text_markup','0');
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
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_state`
--

LOCK TABLES `go_state` WRITE;
/*!40000 ALTER TABLE `go_state` DISABLE KEYS */;
INSERT INTO `go_state` VALUES (1,'addressbook-window-new-contact','o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A820%5Eheight%3Dn%253A640'),(1,'bs-items-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aitem_group_name%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aamount%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Aunit%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A250%255Eo%25253Aid%25253Ds%2525253Acost_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Atracking_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_cost%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_price%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Aunit_total%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_list%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Atotal-price%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Aitem_total%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Avat%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Avat_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Adiscount%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Amarkup%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A16%25255Ewidth%25253Dn%2525253A120%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Anote%25255Ewidth%25253Dn%2525253A250%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Aitem_group_name'),(1,'calendar-state','s%3A%7B%22displayType%22%3A%22days%22%2C%22days%22%3A7%2C%22calendars%22%3A%5B1%5D%2C%22view_id%22%3A0%2C%22group_id%22%3A1%7D'),(1,'entity-grid-selected-link','a%3As%253AContact'),(1,'go-checker-panel','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A28%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A105%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A330.75%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A54.25%5Egroup%3Ds%253Atype'),(1,'go-email-west','o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A698'),(1,'go-module-panel-modules','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A1000%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Asort_order%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Apackage%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A35.875%5Esort%3Do%253Afield%253Ds%25253Aname%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Apackage'),(1,'list-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A1288%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(1,'open-modules','a%3As%253Aaddressbook%5Es%253Abilling%5Es%253Abookmarks%5Es%253Acalendar%5Es%253Aemail%5Es%253Afiles%5Es%253Ahoursapproval2%5Es%253Aleavedays%5Es%253Aprojects2%5Es%253Asummary%5Es%253Atasks%5Es%253Atickets%5Es%253Atimeregistration2%5Es%253Apostfixadmin'),(1,'pm-tasks','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A196%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A56%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A35.875%5Egroup%3Ds%253Aparent_description%5Ecollapsed%3Db%253A1'),(1,'popupfbfs-east-panel','o%3Acollapsed%3Db%253A1'),(1,'saveas-filebrowserfs-east-panel','o%3Acollapsed%3Db%253A1'),(1,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(1,'ti-types-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A35%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A224%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Agroup_name'),(1,'tr-entry-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Ds%2525253Aproject%25255Ewidth%25253Dn%2525253A300%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A150%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(2,'entity-grid-selected-link','a%3As%253AContact'),(2,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(3,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(4,'entity-grid-selected-link','a%3As%253ALinkedEmail'),(4,'pm-tasks','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A196%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A56%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A35.875%5Egroup%3Ds%253Aparent_description%5Ecollapsed%3Db%253A1'),(4,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(5,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(6,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name');
/*!40000 ALTER TABLE `go_state` ENABLE KEYS */;
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
INSERT INTO `go_working_weeks` VALUES (1,8,8,8,8,8,0,0),(2,8,8,8,8,8,0,0),(3,8,8,8,8,8,0,0);
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
  CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `googleauth_secret`
--

LOCK TABLES `googleauth_secret` WRITE;
/*!40000 ALTER TABLE `googleauth_secret` DISABLE KEYS */;
INSERT INTO `googleauth_secret` VALUES (6,'DKZH26YEIBAHLX26','2019-07-18 07:16:08');
/*!40000 ALTER TABLE `googleauth_secret` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imapauth_server`
--

DROP TABLE IF EXISTS `imapauth_server`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imapauth_server` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imapHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imapPort` int(11) NOT NULL DEFAULT 143,
  `imapEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `imapValidateCertificate` tinyint(1) NOT NULL DEFAULT 1,
  `removeDomainFromUsername` tinyint(1) NOT NULL DEFAULT 0,
  `smtpHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtpPort` int(11) NOT NULL DEFAULT 587,
  `smtpUsername` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPassword` varchar(512) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `smtpUseUserCredentials` tinyint(1) NOT NULL DEFAULT 0,
  `smtpEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpValidateCertificate` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imapauth_server`
--

LOCK TABLES `imapauth_server` WRITE;
/*!40000 ALTER TABLE `imapauth_server` DISABLE KEYS */;
INSERT INTO `imapauth_server` VALUES (3,'mailserver',143,NULL,0,0,'mailserver',25,'','{GOCRYPT2}def50200bac6f5dd846bf022d00646f7a38f9e0af13331b25d1b452917ee9b42891a63d9339ace472a0475f12665a8e976a5586ee73d22305cb66b097f8d64d46f00f7893ce8219b4abe2afe410ce470f71eda59',0,NULL,0);
/*!40000 ALTER TABLE `imapauth_server` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imapauth_server_domain`
--

DROP TABLE IF EXISTS `imapauth_server_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imapauth_server_domain` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `serverId` (`serverId`),
  CONSTRAINT `imapauth_server_domain_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imapauth_server_domain`
--

LOCK TABLES `imapauth_server_domain` WRITE;
/*!40000 ALTER TABLE `imapauth_server_domain` DISABLE KEYS */;
INSERT INTO `imapauth_server_domain` VALUES (3,3,'intermesh.localhost');
/*!40000 ALTER TABLE `imapauth_server_domain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imapauth_server_group`
--

DROP TABLE IF EXISTS `imapauth_server_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `imapauth_server_group` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`serverId`,`groupId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `imapauth_server_group_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `imapauth_server_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imapauth_server_group`
--

LOCK TABLES `imapauth_server_group` WRITE;
/*!40000 ALTER TABLE `imapauth_server_group` DISABLE KEYS */;
INSERT INTO `imapauth_server_group` VALUES (3,3);
/*!40000 ALTER TABLE `imapauth_server_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_credit_types`
--

DROP TABLE IF EXISTS `ld_credit_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_credit_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_doesnt_expired` tinyint(1) NOT NULL DEFAULT 0,
  `sort_index` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_credit_types`
--

LOCK TABLES `ld_credit_types` WRITE;
/*!40000 ALTER TABLE `ld_credit_types` DISABLE KEYS */;
INSERT INTO `ld_credit_types` VALUES (1,'Holidays','Holidays',1,1,1);
/*!40000 ALTER TABLE `ld_credit_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_credits`
--

DROP TABLE IF EXISTS `ld_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_credits` (
  `ld_year_credit_id` int(11) NOT NULL,
  `ld_credit_type_id` int(11) NOT NULL,
  `n_hours` double DEFAULT NULL,
  PRIMARY KEY (`ld_year_credit_id`,`ld_credit_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_credits`
--

LOCK TABLES `ld_credits` WRITE;
/*!40000 ALTER TABLE `ld_credits` DISABLE KEYS */;
INSERT INTO `ld_credits` VALUES (1,1,200);
/*!40000 ALTER TABLE `ld_credits` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_leave_days`
--

LOCK TABLES `ld_leave_days` WRITE;
/*!40000 ALTER TABLE `ld_leave_days` DISABLE KEYS */;
INSERT INTO `ld_leave_days` VALUES (1,1,1572562800,1572649200,'01:00:00',0,8,'Test 1',1572608007,1572608007,0,1),(2,1,1573513200,1573513200,'01:00:00',8,0,'test2',1572608032,1572608037,2,1),(3,1,1573599600,1573599600,'01:00:00',8,0,'',1572608045,1572608048,1,1);
/*!40000 ALTER TABLE `ld_leave_days` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_year_credits`
--

DROP TABLE IF EXISTS `ld_year_credits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_year_credits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `year` int(4) NOT NULL DEFAULT 0,
  `comments` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `manager_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_year_credits`
--

LOCK TABLES `ld_year_credits` WRITE;
/*!40000 ALTER TABLE `ld_year_credits` DISABLE KEYS */;
INSERT INTO `ld_year_credits` VALUES (1,1,2019,'0',1);
/*!40000 ALTER TABLE `ld_year_credits` ENABLE KEYS */;
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
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`createdBy`),
  KEY `category_id` (`noteBookId`),
  CONSTRAINT `notes_note_ibfk_1` FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE
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
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fileFolderId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  CONSTRAINT `notes_note_book_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes_note_book`
--

LOCK TABLES `notes_note_book` WRITE;
/*!40000 ALTER TABLE `notes_note_book` DISABLE KEYS */;
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
  KEY `address` (`address`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Aliases';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_aliases`
--

LOCK TABLES `pa_aliases` WRITE;
/*!40000 ALTER TABLE `pa_aliases` DISABLE KEYS */;
INSERT INTO `pa_aliases` VALUES (1,1,'admin@intermesh.localhost','admin@intermesh.localhost',1562244891,1562244891,'0'),(2,1,'test@intermesh.localhost','test@intermesh.localhost',1562244900,1562244900,'0'),(3,1,'schering@intermesh.localhost','admin@intermesh.localhost',1562593674,1562593674,'1'),(4,1,'merijn@intermesh.localhost','admin@intermesh.localhost',1562593679,1562593679,'1'),(5,1,'foo@intermesh.localhost','foo@intermesh.localhost',1562673376,1562673376,'0'),(6,1,'zoidberg@intermesh.localhost','zoidberg@intermesh.localhost',1562677035,1562677035,'0'),(7,2,'zoidberg@planetexpress.com','zoidberg@planetexpress.com',1562677348,1562677348,'0'),(8,3,'t3@1','t3@1',1568106872,1568106872,'0'),(9,3,'t4@1','t4@1',1568107447,1568107447,'0'),(10,3,'t5@1','t5@1',1568108068,1568108068,'0'),(11,1,'t6@intermesh.localhost','t6@intermesh.localhost',1568108151,1568108151,'0'),(12,4,'t7@','t7@',1568108188,1568108188,'0'),(13,1,'demo2@intermesh.localhost','demo2@intermesh.localhost',1568108312,1568108312,'0'),(14,1,'t12@intermesh.localhost','t12@intermesh.localhost',1568632558,1568632558,'0'),(15,5,'tom-ketil.sundseth@tbb.no','tom-ketil.sundseth@tbb.no',1568633866,1568633866,'0'),(16,5,'tom-ketil@tbb.no','tom-ketil.sundseth@tbb.no',1568633866,1568633866,'0'),(17,5,'vidar.storloes@tbb.no','vidar.storloes@tbb.no',1568634312,1568634312,'0'),(18,5,'vidar@tbb.no','vidar.storloes@tbb.no',1568634312,1568634312,'0'),(19,5,'tommy.nielsen@tbb.no','tommy.nielsen@tbb.no',1568634313,1568634313,'0'),(20,5,'tommy@tbb.no','tommy.nielsen@tbb.no',1568634313,1568634313,'0'),(21,5,'tom.erik.hermansen@tbb.no','tom.erik.hermansen@tbb.no',1568634314,1568634314,'0'),(22,5,'tom.erik@tbb.no','tom.erik.hermansen@tbb.no',1568634314,1568634314,'0'),(23,5,'thomas.syvertsen@tbb.no','thomas.syvertsen@tbb.no',1568634314,1568634314,'0'),(24,5,'thomas.malmo@tbb.no','thomas.malmo@tbb.no',1568634315,1568634315,'0'),(25,5,'thomas.johansen@tbb.no','thomas.johansen@tbb.no',1568634315,1568634315,'0'),(26,5,'terje.sorum@tbb.no','terje.sorum@tbb.no',1568634316,1568634316,'0'),(27,5,'terje@tbb.no','terje.sorum@tbb.no',1568634316,1568634316,'0'),(28,5,'svein.storoy@tbb.no','svein.storoy@tbb.no',1568634316,1568634316,'0'),(29,5,'svein@tbb.no','svein.storoy@tbb.no',1568634316,1568634316,'0'),(30,5,'stian.trebekk@tbb.no','stian.trebekk@tbb.no',1568634317,1568634317,'0'),(31,5,'stian@tbb.no','stian.trebekk@tbb.no',1568634317,1568634317,'0'),(32,5,'steve.hermansen@tbb.no','steve.hermansen@tbb.no',1568634317,1568634317,'0'),(33,5,'steve@tbb.no','steve.hermansen@tbb.no',1568634317,1568634317,'0'),(34,5,'staale.lilledahl@tbb.no','staale.lilledahl@tbb.no',1568634318,1568634318,'0'),(35,5,'steffen.kristensen@tbb.no','steffen.kristensen@tbb.no',1568634319,1568634319,'0'),(36,5,'steffen@tbb.no','steffen.kristensen@tbb.no',1568634319,1568634319,'0'),(37,5,'stig.borgersrud@tbb.no','stig.borgersrud@tbb.no',1568634319,1568634319,'0'),(38,5,'stig@tbb.no','stig.borgersrud@tbb.no',1568634319,1568634319,'0'),(39,5,'jan-egil.ottosen@tbb.no','jan-egil.ottosen@tbb.no',1568634320,1568634320,'0'),(40,5,'jan-egil@tbb.no','jan-egil.ottosen@tbb.no',1568634320,1568634320,'0'),(41,5,'roy.lilliedahl@tbb.no','roy.lilliedahl@tbb.no',1568634320,1568634320,'0'),(42,5,'ronnie.husberg@tbb.no','ronnie.husberg@tbb.no',1568634321,1568634321,'0'),(43,5,'ronnie@tbb.no','ronnie.husberg@tbb.no',1568634321,1568634321,'0'),(44,5,'per.bjerkengen@tbb.no','per.bjerkengen@tbb.no',1568634321,1568634321,'0'),(45,5,'per@tbb.no','per.bjerkengen@tbb.no',1568634321,1568634321,'0'),(46,5,'oivind.johnsen@tbb.no','oivind.johnsen@tbb.no',1568634322,1568634322,'0'),(47,5,'oivindj@tbb.no','oivind.johnsen@tbb.no',1568634322,1568634322,'0'),(48,5,'odd-egil.torgersen@tbb.no','odd-egil.torgersen@tbb.no',1568634322,1568634322,'0'),(49,5,'odd-egil@tbb.no','odd-egil.torgersen@tbb.no',1568634322,1568634322,'0'),(50,5,'monica.loftskjær@tbb.no','monica.loftskjær@tbb.no',1568634323,1568634323,'0'),(51,5,'monica@tbb.no','monica.loftskjær@tbb.no',1568634323,1568634323,'0'),(52,5,'martin.olsen@tbb.no','martin.olsen@tbb.no',1568634323,1568634323,'0'),(53,5,'martin@tbb.no','martin.olsen@tbb.no',1568634323,1568634323,'0'),(54,5,'lars.sletta@tbb.no','lars.sletta@tbb.no',1568634324,1568634324,'0'),(55,5,'lars@tbb.no','lars.sletta@tbb.no',1568634324,1568634324,'0'),(56,5,'lars.kristiansen@tbb.no','lars.kristiansen@tbb.no',1568634324,1568634324,'0'),(57,5,'ken.henriksen@tbb.no','ken.henriksen@tbb.no',1568634325,1568634325,'0'),(58,5,'ken@tbb.no','ken.henriksen@tbb.no',1568634325,1568634325,'0'),(59,5,'jan-tore.borresen@tbb.no','jan-tore.borresen@tbb.no',1568634325,1568634325,'0'),(60,5,'jan-tore@tbb.no','jan-tore.borresen@tbb.no',1568634325,1568634325,'0'),(61,5,'thomas.simonsen@tbb.no','thomas.simonsen@tbb.no',1568634325,1568634325,'0'),(62,5,'giedrius.navagruckas@tbb.no','giedrius.navagruckas@tbb.no',1568634326,1568634326,'0'),(63,5,'giedrius@tbb.no','giedrius.navagruckas@tbb.no',1568634326,1568634326,'0'),(64,5,'frode.pedersen@tbb.no','frode.pedersen@tbb.no',1568634326,1568634326,'0'),(65,5,'frode@tbb.no','frode.pedersen@tbb.no',1568634326,1568634326,'0'),(66,5,'finn.poulsen@tbb.no','finn.poulsen@tbb.no',1568634326,1568634326,'0'),(67,5,'espen.simonsen@tbb.no','espen.simonsen@tbb.no',1568634327,1568634327,'0'),(68,5,'elisabeth.thun@tbb.no','elisabeth.thun@tbb.no',1568634327,1568634327,'0'),(69,5,'dace.pedersen@tbb.no','dace.pedersen@tbb.no',1568634328,1568634328,'0'),(70,5,'dace@tbb.no','dace.pedersen@tbb.no',1568634328,1568634328,'0'),(71,5,'carl.wiborg@tbb.no','carl.wiborg@tbb.no',1568634328,1568634328,'0'),(72,5,'carl@tbb.no','carl.wiborg@tbb.no',1568634328,1568634328,'0'),(73,5,'borre.lilledahl@tbb.no','borre.lilledahl@tbb.no',1568634328,1568634328,'0'),(74,5,'borre@tbb.no','borre.lilledahl@tbb.no',1568634328,1568634328,'0'),(75,5,'benjamin.vinland@tbb.no','benjamin.vinland@tbb.no',1568634329,1568634329,'0'),(76,5,'frode.johnsen@tbb.no','frode.johnsen@tbb.no',1568634329,1568634329,'0'),(77,5,'einar.kristensen@tbb.no','einar.kristensen@tbb.no',1568634329,1568634329,'0'),(78,5,'mayvellyn.musken@tbb.no','mayvellyn.musken@tbb.no',1568634330,1568634330,'0'),(79,5,'rodrigo.almario@tbb.no','rodrigo.almario@tbb.no',1568634330,1568634330,'0'),(80,5,'dejan.ogorelica@tbb.no','dejan.ogorelica@tbb.no',1568634330,1568634330,'0'),(81,5,'jenderi.salazar@tbb.no','jenderi.salazar@tbb.no',1568634331,1568634331,'0'),(82,5,'ania.wentowska@tbb.no','ania.wentowska@tbb.no',1568634331,1568634331,'0'),(83,5,'bjorn.alknes@tbb.no','bjorn.alknes@tbb.no',1568634332,1568634332,'0'),(84,5,'richard.bjornstad@tbb.no','richard.bjornstad@tbb.no',1568634332,1568634332,'0'),(85,5,'terje.carlsen@tbb.no','terje.carlsen@tbb.no',1568634332,1568634332,'0'),(86,5,'kim.hjemstad@tbb.no','kim.hjemstad@tbb.no',1568634333,1568634333,'0'),(87,5,'roger.haaland@tbb.no','roger.haaland@tbb.no',1568634333,1568634333,'0'),(88,5,'oivind.lindbaek@tbb.no','oivind.lindbaek@tbb.no',1568634333,1568634333,'0'),(89,5,'jakub jan.markowicz@tbb.no','jakub jan.markowicz@tbb.no',1568634334,1568634334,'0'),(90,5,'jakub.markowicz@tbb.no','jakub jan.markowicz@tbb.no',1568634334,1568634334,'0'),(91,5,'emmannouil.nikolaras@tbb.no','emmannouil.nikolaras@tbb.no',1568634334,1568634334,'0'),(92,5,'boye.sandbakken@tbb.no','boye.sandbakken@tbb.no',1568634335,1568634335,'0'),(93,5,'rupert.sheridan@tbb.no','rupert.sheridan@tbb.no',1568634335,1568634335,'0'),(94,5,'oskar.sjodal@tbb.no','oskar.sjodal@tbb.no',1568634335,1568634335,'0'),(95,5,'oskar.sjødal@tbb.no','oskar.sjodal@tbb.no',1568634335,1568634335,'0'),(96,5,'jonas.synnestvedt@tbb.no','jonas.synnestvedt@tbb.no',1568634336,1568634336,'0'),(97,5,'sander.solvberg@tbb.no','sander.solvberg@tbb.no',1568634336,1568634336,'0'),(98,5,'arne.sorensen@tbb.no','arne.sorensen@tbb.no',1568634336,1568634336,'0'),(99,5,'andrzej.walenttek@tbb.no','andrzej.walenttek@tbb.no',1568634337,1568634337,'0'),(100,1,'t13@intermesh.localhost','t13@intermesh.localhost',1568799892,1568799892,'0'),(101,1,'t14@intermesh.localhost','t14@intermesh.localhost',1568799932,1568799932,'0'),(102,1,'t16@intermesh.localhost','t16@intermesh.localhost',1568800247,1568800247,'0'),(103,1,'t17@intermesh.localhost','t17@intermesh.localhost',1568801006,1568801006,'0'),(104,1,'t18@intermesh.localhost','t18@intermesh.localhost',1568982413,1568982413,'0'),(105,1,'t19@intermesh.localhost','t19@intermesh.localhost',1568982656,1568982656,'0'),(106,1,'t21@intermesh.localhost','t21@intermesh.localhost',1568983553,1568983553,'0'),(107,1,'t23@intermesh.localhost','t23@intermesh.localhost',1568983703,1568983703,'0'),(108,1,'t24@intermesh.localhost','t24@intermesh.localhost',1568993496,1568993496,'0'),(109,1,'t25@intermesh.localhost','t25@intermesh.localhost',1568993541,1568993541,'0'),(110,1,'t26@intermesh.localhost','t26@intermesh.localhost',1568993554,1568993554,'0'),(111,1,'t27@intermesh.localhost','t27@intermesh.localhost',1568993577,1568993577,'0'),(112,1,'t31@intermesh.localhost','t31@intermesh.localhost',1569500801,1569500801,'0'),(113,1,'t43@intermesh.localhost','t43@intermesh.localhost',1570548993,1570548993,'0'),(114,1,'t100@intermesh.localhost','t100@intermesh.localhost',1573485965,1573485965,'0'),(115,1,'Y39@intermesh.localhost','Y39@intermesh.localhost',1573749826,1573749826,'0');
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
  KEY `domain` (`domain`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Domains';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_domains`
--

LOCK TABLES `pa_domains` WRITE;
/*!40000 ALTER TABLE `pa_domains` DISABLE KEYS */;
INSERT INTO `pa_domains` VALUES (1,1,'intermesh.localhost','',0,0,9461760,524288,'virtual',0,1562244880,1569501718,1,117),(2,1,'planetexpress.com','',0,0,10485760,524288,'virtual',0,1562677337,1562677337,1,121),(3,1,'1',NULL,0,0,10485760,524288,'virtual',0,1568106872,1568106872,1,131),(4,1,'',NULL,0,0,10485760,524288,'virtual',0,1568108188,1568108188,1,132),(5,1,'tbb.no','',0,0,0,524288,'virtual',0,1568633866,1568634400,1,133);
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
  KEY `username` (`username`),
  KEY `go_installation_id` (`go_installation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Mailboxes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pa_mailboxes`
--

LOCK TABLES `pa_mailboxes` WRITE;
/*!40000 ALTER TABLE `pa_mailboxes` DISABLE KEYS */;
INSERT INTO `pa_mailboxes` VALUES (1,1,NULL,'admin@intermesh.localhost','$5$rounds=5000$rbJe.xCJoG2EBXAK$4Cd06/EO8WSB5JfPOwDV0.e2LEBoqjbLGsYtV9Bt475','admin','intermesh.localhost/admin/Maildir/','intermesh.localhost/admin/',524288,1562244891,1562244891,1,0),(2,1,NULL,'test@intermesh.localhost','$5$rounds=5000$IpOuuHcOdWsKPwo6$8csK9rJdcU3yS7cWBWyx5RBF.eqDxht5mm9PsPWN9/C','test','intermesh.localhost/test/Maildir/','intermesh.localhost/test/',524288,1562244900,1562244900,1,0),(3,1,NULL,'foo@intermesh.localhost','$5$rounds=5000$rW2JjSOjEDOnM5qP$ktCJ87QMiO60OhtvDplWxiCTphUdwN/P.AT8L2HuyV6','foo','intermesh.localhost/foo/Maildir/','intermesh.localhost/foo/',524288,1562673383,1562673383,1,0),(4,1,NULL,'zoidberg@intermesh.localhost','$5$rounds=5000$uDFVh0gmRSij5wKg$LsgyJsafOe.9YVtHD9W1v2H8eRuInBXch3qliFSp9m6','zoidberg','intermesh.localhost/zoidberg/Maildir/','intermesh.localhost/zoidberg/',524288,1562677037,1562677037,1,0),(5,2,NULL,'zoidberg@planetexpress.com','$5$rounds=5000$TkCJ6cFPenIyvntP$8/hc1lfYtbj2KKQVK7VWDdWVfipPalcwJFTkXVyS9k8','zoidberg','planetexpress.com/zoidberg/Maildir/','planetexpress.com/zoidberg/',524288,1562677348,1562677348,1,0),(6,3,NULL,'t3@1','$5$rounds=5000$JI2EHfQGxOAJpi90$gRoBc35O6fe4rKShumFLLQ8sYj38SOtFgXfqGzeGf98','t3','1/t3/Maildir/','1/t3/',524288,1568106872,1568106872,1,0),(7,3,NULL,'t4@1','$5$rounds=5000$5B1CnU/I0C6HYEK5$ycjmo2wGygqUAsr/Jk4p5jCEoXUlhQrYxXyNLThZ/A8','t4','1/t4/Maildir/','1/t4/',524288,1568107447,1568107447,1,0),(8,3,NULL,'t5@1','$5$rounds=5000$goQX1WR4b98b1KqG$cdQf.IEm5RWiBC7DzjleprGB4n/oCTPE2nRXCYYe4l5','t5','1/t5/Maildir/','1/t5/',524288,1568108068,1568108068,1,0),(9,1,NULL,'t6@intermesh.localhost','$5$rounds=5000$ZUV9Gg/QM0Cxd.TX$zIy4z9U6wOxWz3yrQtC/t7.bi/.B9ZwdQ1eLLC4TI00','t6','intermesh.localhost/t6/Maildir/','intermesh.localhost/t6/',524288,1568108151,1568108151,1,0),(10,4,NULL,'t7@','$5$rounds=5000$PkeHsIAYV5claS1z$MB8YhWkXU7EK9kg/zPvlT7Jiv8vSh7LTiHaBQ/KK4X3','t7','/t7/Maildir/','/t7/',524288,1568108188,1568108188,1,0),(11,1,NULL,'demo2@intermesh.localhost','$5$rounds=5000$queE91lYHB1DHUQ3$3zNDxh0kaH6boNBX6b6cKfiaplpXTpSi8m08TLbvMy8','Demo User','intermesh.localhost/demo2/Maildir/','intermesh.localhost/demo2/',524288,1568108674,1568108674,1,0),(12,1,NULL,'t12@intermesh.localhost','$5$rounds=5000$qVTdj9Nq/IChDwAP$9NQI4n4iZhrv/jf6RBa2WI1z.6l8Z4FVydr3gyvpp3/','t12','intermesh.localhost/t12/Maildir/','intermesh.localhost/t12/',524288,1568632558,1568632558,1,0),(13,5,NULL,'tom-ketil.sundseth@tbb.no','$5$rounds=5000$wDhJ4/R.QeBnyLpY$KX0vc2Texuo4JlzuJDJczpnIAJpzuheWHszzMcM4k/D','Tom Ketil Sundseth','tbb.no/tom-ketil.sundseth/Maildir/','tbb.no/tom-ketil.sundseth/',524288,1568633866,1568633866,1,0),(14,5,NULL,'vidar.storloes@tbb.no','$5$rounds=5000$OGZUF8/N3tb6ZzKO$K6/.mhIRZ./m2Qn0geFA.uzC06PzmmV0cd1qD9GZeL1','Vidar Storløs','tbb.no/vidar.storloes/Maildir/','tbb.no/vidar.storloes/',524288,1568634312,1568634312,1,0),(15,5,NULL,'tommy.nielsen@tbb.no','$5$rounds=5000$svt0OKOFLgVW6NkW$0yzz/dMv4NQrrM.3.1uKmU9pZNJGDB861I/RfLXq7J7','Tommy-Nicolai Nielsen','tbb.no/tommy.nielsen/Maildir/','tbb.no/tommy.nielsen/',524288,1568634313,1568634313,1,0),(16,5,NULL,'tom.erik.hermansen@tbb.no','$5$rounds=5000$TXxjhf9ay5eiIM35$O6iwABHX0Dgaoqlmxhi6qHJZbsQ5EPCIQ4afCKLTrG1','Tom Erik Hermansen','tbb.no/tom.erik.hermansen/Maildir/','tbb.no/tom.erik.hermansen/',524288,1568634314,1568634314,1,0),(17,5,NULL,'thomas.syvertsen@tbb.no','$5$rounds=5000$waEoGguVEnP/R8Bp$D.JDvT6QEXFcIyH5KZ8QOusNb2XViktentSZzWB0SM0','Thomas Syvertsen','tbb.no/thomas.syvertsen/Maildir/','tbb.no/thomas.syvertsen/',524288,1568634314,1568634314,1,0),(18,5,NULL,'thomas.malmo@tbb.no','$5$rounds=5000$LnMvbHcWTGf32Zio$x7a2yJB/Y3trfvvWF6YNwSZtA2N9Jtx.GTGcgIrYDw1','Thomas Malmo','tbb.no/thomas.malmo/Maildir/','tbb.no/thomas.malmo/',524288,1568634315,1568634315,1,0),(19,5,NULL,'thomas.johansen@tbb.no','$5$rounds=5000$HVpR7E/tIf.g.8uP$rOR2wjlqhLsI2ipYEB7RxIZzTzajxv49hHwoh0Cl3a5','Thomas Johansen','tbb.no/thomas.johansen/Maildir/','tbb.no/thomas.johansen/',524288,1568634315,1568634315,1,0),(20,5,NULL,'terje.sorum@tbb.no','$5$rounds=5000$aEyDOMtockLDoU50$QA0FiauPwCry/j/aI4IdC3Rd0cPqZryPibuJ5rHMF30','Terje Sørum','tbb.no/terje.sorum/Maildir/','tbb.no/terje.sorum/',524288,1568634316,1568634316,1,0),(21,5,NULL,'svein.storoy@tbb.no','$5$rounds=5000$Xw7zqdQD.vNdwBue$jkMhM6ERfi3d7S8pFWl7Bp3ItfX8YjuTInwzFU8B4oA','Svein-Martin Storøy','tbb.no/svein.storoy/Maildir/','tbb.no/svein.storoy/',524288,1568634316,1568634316,1,0),(22,5,NULL,'stian.trebekk@tbb.no','$5$rounds=5000$gK7RyNt8DN77w/el$Y9NcV/cYk2tUMFDz4imZXqe.zCLK9OZuaiYkF9Guca6','Stian Trebekk','tbb.no/stian.trebekk/Maildir/','tbb.no/stian.trebekk/',524288,1568634317,1568634317,1,0),(23,5,NULL,'steve.hermansen@tbb.no','$5$rounds=5000$H6h5ddTwJUwS7fUi$PCRc614u26B6PdNWAxdlHFUSL4bH8JFJMGWyeKiaPT/','Steve Romsdal Hermansen','tbb.no/steve.hermansen/Maildir/','tbb.no/steve.hermansen/',524288,1568634317,1568634317,1,0),(24,5,NULL,'staale.lilledahl@tbb.no','$5$rounds=5000$SviITAyPgvmXhLBX$6wSVjiNc.ScfnI2DALsq2vQU6j8oNyLQWm6TqYQu5V1','Ståle Lilledahl','tbb.no/staale.lilledahl/Maildir/','tbb.no/staale.lilledahl/',524288,1568634318,1568634318,1,0),(25,5,NULL,'steffen.kristensen@tbb.no','$5$rounds=5000$ZIB5RCnC9wURq3aK$gXBztJvobTy19CkzNuN8l7wxOEXRasAh.Xbq55z0FI4','Steffen Kristensen','tbb.no/steffen.kristensen/Maildir/','tbb.no/steffen.kristensen/',524288,1568634319,1568634319,1,0),(26,5,NULL,'stig.borgersrud@tbb.no','$5$rounds=5000$/8EoUZAGtl2B22VN$6onX97tWyS/zh50z5OtCTF3rgjkxSbjPNKAZg8QRL94','Stig-Arild Borgersrud','tbb.no/stig.borgersrud/Maildir/','tbb.no/stig.borgersrud/',524288,1568634319,1568634319,1,0),(27,5,NULL,'jan-egil.ottosen@tbb.no','$5$rounds=5000$5ex58IVSPTEkxYur$Pst2rGi.cHF94dKgkltPQmgiwAcT52GP04FtwFY.Sb2','Jan Egil Ottosen','tbb.no/jan-egil.ottosen/Maildir/','tbb.no/jan-egil.ottosen/',524288,1568634320,1568634320,1,0),(28,5,NULL,'roy.lilliedahl@tbb.no','$5$rounds=5000$0SCImYT.jIteYc6o$MP49eoSnyq3Gp/V5ZdD8f/ma8QXWZUW6lBAihG1LAS6','Roy Lilliedahl','tbb.no/roy.lilliedahl/Maildir/','tbb.no/roy.lilliedahl/',524288,1568634320,1568634320,1,0),(29,5,NULL,'ronnie.husberg@tbb.no','$5$rounds=5000$fZTwLEXWRJOLQIiG$g8vVucjzsZjLmrXfVdHic0.wNU2jMca0SIRraJ6GEI3','Ronnie Husberg','tbb.no/ronnie.husberg/Maildir/','tbb.no/ronnie.husberg/',524288,1568634321,1568634321,1,0),(30,5,NULL,'per.bjerkengen@tbb.no','$5$rounds=5000$ZY4jWyS7LOhYXtjT$NdScPKiiByWxi1CQw93UccrNFdrZPMHVXDnL3YW8dA3','Per Olav Bjerkengen','tbb.no/per.bjerkengen/Maildir/','tbb.no/per.bjerkengen/',524288,1568634321,1568634321,1,0),(31,5,NULL,'oivind.johnsen@tbb.no','$5$rounds=5000$Dlnsq1Mi.S5nch5W$q0tAZAfi9uoaWdZy9OMzuS7TIJkoTxHy5LFrx1fcpj9','Øivind Johnsen','tbb.no/oivind.johnsen/Maildir/','tbb.no/oivind.johnsen/',524288,1568634322,1568634322,1,0),(32,5,NULL,'odd-egil.torgersen@tbb.no','$5$rounds=5000$9B4q7t0AUbmfUccT$Ffw/NhahVCtoSYipMwqcg4.8X4vAXsgohuIknQoaBP5','Odd-Egil Torgersen','tbb.no/odd-egil.torgersen/Maildir/','tbb.no/odd-egil.torgersen/',524288,1568634323,1568634323,1,0),(33,5,NULL,'monica.loftskjær@tbb.no','$5$rounds=5000$x1fXo41RDOV4vy4a$72c4Kj8Sr0yst0XaAf9iTwPARazg5eZaaapUNNh1gb3','Monica Loftskjær','tbb.no/monica.loftskjær/Maildir/','tbb.no/monica.loftskjær/',524288,1568634417,1568634417,1,0),(34,5,NULL,'martin.olsen@tbb.no','$5$rounds=5000$3wYIBnVF.Ig4qWN/$gFBodXk5qd9CZJ0S6qbKf5.pqxQyNa2oaMuyNV3co10','Martin Olsen','tbb.no/martin.olsen/Maildir/','tbb.no/martin.olsen/',524288,1568634419,1568634419,1,0),(35,5,NULL,'lars.sletta@tbb.no','$5$rounds=5000$JIOUaswvML8T/QlE$AxdRqGiywNgfY9Fh.vGZyfP0H0BWf5FZVjGLFvUga6D','Lars Erik Sletta','tbb.no/lars.sletta/Maildir/','tbb.no/lars.sletta/',524288,1568634424,1568634424,1,0),(36,5,NULL,'lars.kristiansen@tbb.no','$5$rounds=5000$6iAat5lOZO4YIkCf$7pByn0d9Pd0CW3.aVesnQs2qPC/lmc.LYQBa8Bv.iJ4','Lars Kristiansen','tbb.no/lars.kristiansen/Maildir/','tbb.no/lars.kristiansen/',524288,1568634424,1568634424,1,0),(37,5,NULL,'ken.henriksen@tbb.no','$5$rounds=5000$.j6XX7aRlS3CSACF$PiOVX62zDmNcNoUCs5GvbX6Qa06.mFFDR8CviVLiaw4','Ken Nicolai Henriksen','tbb.no/ken.henriksen/Maildir/','tbb.no/ken.henriksen/',524288,1568634425,1568634425,1,0),(38,5,NULL,'jan-tore.borresen@tbb.no','$5$rounds=5000$vBcIFU5aRjPYPxRA$JQ5aymKouAVsuBPzibf83kr/Dyj4BbGFsP8thkFKnV4','Jan-Tore Børresen','tbb.no/jan-tore.borresen/Maildir/','tbb.no/jan-tore.borresen/',524288,1568634426,1568634426,1,0),(39,5,NULL,'thomas.simonsen@tbb.no','$5$rounds=5000$jCHQ2w62DGgGiabs$v3tN2X51ubX.xdEfcwtiYUs8yhiW.QA37KHmDTBPw/B','Thomas Raasoch Simonsen','tbb.no/thomas.simonsen/Maildir/','tbb.no/thomas.simonsen/',524288,1568634426,1568634426,1,0),(40,5,NULL,'giedrius.navagruckas@tbb.no','$5$rounds=5000$krGbQ.zCRShNb45/$Ks0Sis3owE8V4zvJc5JRBmRrzFnH0/Z74PrTUGCWAF4','Giedrius Navagruckas','tbb.no/giedrius.navagruckas/Maildir/','tbb.no/giedrius.navagruckas/',524288,1568634427,1568634427,1,0),(41,5,NULL,'frode.pedersen@tbb.no','$5$rounds=5000$87i8XtXVEc6ORgAs$KlqELjoJe2xa9nLzCjYS/5DgQSOGtXGI/nAp8Zjz/a8','Frode Berg Pedersen','tbb.no/frode.pedersen/Maildir/','tbb.no/frode.pedersen/',524288,1568634427,1568634427,1,0),(42,5,NULL,'finn.poulsen@tbb.no','$5$rounds=5000$qbLDFluTEhNn.KaS$.xOaliqgNTnNxbRDWViZbmuAjWJajbfrAs6dQyGwxc7','Finn Poulsen','tbb.no/finn.poulsen/Maildir/','tbb.no/finn.poulsen/',524288,1568634428,1568634428,1,0),(43,5,NULL,'espen.simonsen@tbb.no','$5$rounds=5000$IiJ9YgiaZAzlqzJU$l0k98UPDLsdK/tBxbqIw8b.Q6iserrWSSa.MPi8Skc5','Espen Simonsen','tbb.no/espen.simonsen/Maildir/','tbb.no/espen.simonsen/',524288,1568634429,1568634429,1,0),(44,5,NULL,'elisabeth.thun@tbb.no','$5$rounds=5000$CObXYKr/JdNzQ3vG$zxt2qoO6VVWB6ybcSa7bYKLLDpWCJhsKFU9R7dzopU8','Elisabeth Thun','tbb.no/elisabeth.thun/Maildir/','tbb.no/elisabeth.thun/',524288,1568634429,1568634429,1,0),(45,5,NULL,'dace.pedersen@tbb.no','$5$rounds=5000$ufrL//EBjsHvbXC8$wZ4NFrVWIFeYvVVoFneRBiXv5N.RWXBKtxjgXOSao1/','Dace Pedersen','tbb.no/dace.pedersen/Maildir/','tbb.no/dace.pedersen/',524288,1568634430,1568634430,1,0),(46,5,NULL,'carl.wiborg@tbb.no','$5$rounds=5000$amSVBaLynzyNp20K$tvG7vAreJFqSH2b5CeE6LSipm1n2mHHU1cBKavw34hD','Carl Andre Wiborg','tbb.no/carl.wiborg/Maildir/','tbb.no/carl.wiborg/',524288,1568634430,1568634430,1,0),(47,5,NULL,'borre.lilledahl@tbb.no','$5$rounds=5000$t9/l2MrKzR5SOvYt$SLlii5wQ5on4OzsdwgbXgFH2Vw8cVJEGN1q6eeSAX1A','Børre Lilledahl','tbb.no/borre.lilledahl/Maildir/','tbb.no/borre.lilledahl/',524288,1568634432,1568634432,1,0),(48,5,NULL,'benjamin.vinland@tbb.no','$5$rounds=5000$FkzHJJyw4JlVP2r7$5jM8klXCSeiy4IFO9c0Wd7RrnLEFEYkkwyXfltQlqR7','Benjamin Vinland Årrestad','tbb.no/benjamin.vinland/Maildir/','tbb.no/benjamin.vinland/',524288,1568634437,1568634437,1,0),(49,5,NULL,'frode.johnsen@tbb.no','$5$rounds=5000$RElew580VHZiUjPe$F8yV5DzCuF2paGamPl3qJhwzBqr4sEIUxSSeHFoyl/3','Frode Gullbekk Johnsen','tbb.no/frode.johnsen/Maildir/','tbb.no/frode.johnsen/',524288,1568634437,1568634437,1,0),(50,5,NULL,'einar.kristensen@tbb.no','$5$rounds=5000$2BPAGkbV.Er19Xt.$B2h3jg6M2CIlHsZqSFI0t6MHNuh313iUNtybdUmpmZ5','Einar Kristensen','tbb.no/einar.kristensen/Maildir/','tbb.no/einar.kristensen/',524288,1568634438,1568634438,1,0),(51,5,NULL,'mayvellyn.musken@tbb.no','$5$rounds=5000$51J5Da5zOtRh4UzB$9qZ89CWOKw/1IiChtRfXo422FuwYcRuxeO2wNDUIFI1','Mayvellyn Malacas Musken','tbb.no/mayvellyn.musken/Maildir/','tbb.no/mayvellyn.musken/',524288,1568634438,1568634438,1,0),(52,5,NULL,'rodrigo.almario@tbb.no','$5$rounds=5000$zTfFqLCJvDt5tMHJ$amqIyljoS6oD3WrJbULYsymDoCdKKwRH8Cj6wINQbD.','Almario Rodrigo Arturo Martinez','tbb.no/rodrigo.almario/Maildir/','tbb.no/rodrigo.almario/',524288,1568634439,1568634439,1,0),(53,5,NULL,'dejan.ogorelica@tbb.no','$5$rounds=5000$iFGHU9o6BkqH7zaZ$rV0yFLaLESSIOYmGxv2iaRW7v4xYUIOXPg7y4bXoTTD','Dejan Ogorelica','tbb.no/dejan.ogorelica/Maildir/','tbb.no/dejan.ogorelica/',524288,1568634439,1568634439,1,0),(54,5,NULL,'jenderi.salazar@tbb.no','$5$rounds=5000$7.pBnD8heUmP2Y4H$hCZ9JwAp5WwHon8pSEoHJSevoYcOOI5fyVk9oiLBf8D','J`enderi C O Ryan Salazar','tbb.no/jenderi.salazar/Maildir/','tbb.no/jenderi.salazar/',524288,1568634440,1568634440,1,0),(55,5,NULL,'ania.wentowska@tbb.no','$5$rounds=5000$ODf5luvL7ka6PUn3$1VSxCRxxYaHVM1u/bl.3glG3pFP/RlPOvinLtGcplT/','Ania Wentowska','tbb.no/ania.wentowska/Maildir/','tbb.no/ania.wentowska/',524288,1568634440,1568634440,1,0),(56,5,NULL,'bjorn.alknes@tbb.no','$5$rounds=5000$tXS9a.LUYbH6OPI4$I709KOsz.4mRjZAIRTJFVHn3wMGPLmEK7SQcRalr0A.','Bjørn Alknes','tbb.no/bjorn.alknes/Maildir/','tbb.no/bjorn.alknes/',524288,1568634441,1568634441,1,0),(57,5,NULL,'richard.bjornstad@tbb.no','$5$rounds=5000$UcRg7AhP4RZ4R.iV$LSiMnT2AeRegG0UVtJAtir0Bxm7iwKJ42xDDSbAnSM.','Richard Bjørnstad','tbb.no/richard.bjornstad/Maildir/','tbb.no/richard.bjornstad/',524288,1568634441,1568634441,1,0),(58,5,NULL,'terje.carlsen@tbb.no','$5$rounds=5000$zE40P3H05w31bNi1$z/bTZM6Fwka3ht/scSrIbe0bJ5NdhVdGv49NUR8FRM7','Terje Carlsen','tbb.no/terje.carlsen/Maildir/','tbb.no/terje.carlsen/',524288,1568634442,1568634442,1,0),(59,5,NULL,'kim.hjemstad@tbb.no','$5$rounds=5000$sV0Tq/1DUgjTraOp$lO.b62m6BTimkM8Kfz9bH2AHaiHIf6NZHaSJwwIyQZ2','Kim Hjemstad','tbb.no/kim.hjemstad/Maildir/','tbb.no/kim.hjemstad/',524288,1568634443,1568634443,1,0),(60,5,NULL,'roger.haaland@tbb.no','$5$rounds=5000$XfYfjCjbvkkENuKx$JmU4FyUG5MMxB.LerTBnAKELbGLniRfAQIS5f6xEyt4','Roger Haaland','tbb.no/roger.haaland/Maildir/','tbb.no/roger.haaland/',524288,1568634443,1568634443,1,0),(61,5,NULL,'oivind.lindbaek@tbb.no','$5$rounds=5000$j6Gw.jZErh50H0.k$0cvvrpiTKB4kAijsL3h2GCayO4gHFk3AzunAUCnXjPD','Øivind Lukas Lindbæk','tbb.no/oivind.lindbaek/Maildir/','tbb.no/oivind.lindbaek/',524288,1568634444,1568634444,1,0),(62,5,NULL,'jakub jan.markowicz@tbb.no','$5$rounds=5000$p2UO20q015EIhAHp$LhHG3LZ02NDd7PojBpXw5Of7ZuC76yhg3lrY8SxxKs4','Jakub Jan Markowicz','tbb.no/jakub jan.markowicz/Maildir/','tbb.no/jakub jan.markowicz/',524288,1568634444,1568634444,1,0),(63,5,NULL,'emmannouil.nikolaras@tbb.no','$5$rounds=5000$EtU2tXhklZVwD4kg$B4S.MOn03srFz/VEu9dbdpD3bQOenp.ugwy5fEeG.z4','Emmannouil Nikolaras','tbb.no/emmannouil.nikolaras/Maildir/','tbb.no/emmannouil.nikolaras/',524288,1568634446,1568634446,1,0),(64,5,NULL,'boye.sandbakken@tbb.no','$5$rounds=5000$Hp5CKNKXQN/M6eZU$Vz4/aYhasFpZE7FbXVpjJ/M6AlC5kBmvAqt7sNbPoh3','Boye Sandbakken','tbb.no/boye.sandbakken/Maildir/','tbb.no/boye.sandbakken/',524288,1568634451,1568634451,1,0),(65,5,NULL,'rupert.sheridan@tbb.no','$5$rounds=5000$cz9V7k52HF.3bamZ$xxU/BEEshBuqIVlUG7doApYH2gVaa2qzB/EIGnSucA.','Rupert Ridewood Sheridan','tbb.no/rupert.sheridan/Maildir/','tbb.no/rupert.sheridan/',524288,1568634451,1568634451,1,0),(66,5,NULL,'oskar.sjodal@tbb.no','$5$rounds=5000$0puYdoFBePsUdwFZ$IDqmOuqSVvqaczuAH8dEHBW7uNXU.ktqGKaVI.lhfV6','Per Oskar Sjødal','tbb.no/oskar.sjodal/Maildir/','tbb.no/oskar.sjodal/',524288,1568634452,1568634452,1,0),(67,5,NULL,'jonas.synnestvedt@tbb.no','$5$rounds=5000$IYtMXUlx7KWRqjOO$JfdovnZb7g3XH6LnV/GeiOklNwtvAD5GlXFrkW1ZYM3','Jonas Synnestvedt','tbb.no/jonas.synnestvedt/Maildir/','tbb.no/jonas.synnestvedt/',524288,1568634452,1568634452,1,0),(68,5,NULL,'sander.solvberg@tbb.no','$5$rounds=5000$iaX6Dm8DFeC4g5Mf$dPgnnmi.JF2x9dgfPrALeLTmNcipddxZOeRuuP4LDk1','Sander Sølvberg','tbb.no/sander.solvberg/Maildir/','tbb.no/sander.solvberg/',524288,1568634453,1568634453,1,0),(69,5,NULL,'arne.sorensen@tbb.no','$5$rounds=5000$fdhFnMXfDBrXoQBa$Px.LQERlfyd6pzM7ofzhRdzG4cH8NtiO7PNuzjbjJr6','Arne Sørensen','tbb.no/arne.sorensen/Maildir/','tbb.no/arne.sorensen/',524288,1568634453,1568634453,1,0),(70,5,NULL,'andrzej.walenttek@tbb.no','$5$rounds=5000$RRHuvoo5hMTRNk/Q$3RD3qE8eU2ulgbUcWUTTWowtR.VJUOsg3AUcAEcwrM1','Andrzej Walenttek','tbb.no/andrzej.walenttek/Maildir/','tbb.no/andrzej.walenttek/',524288,1568634454,1568634454,1,0),(71,1,NULL,'t13@intermesh.localhost','$5$rounds=5000$71R4nLF8y5jXV9VK$/Z1Pk5f3PQL2TosM8Upok2lBFLGExj.BZddhofCyJuB','t13','intermesh.localhost/t13/Maildir/','intermesh.localhost/t13/',524288,1568799892,1568799892,1,0),(72,1,NULL,'t14@intermesh.localhost','$5$rounds=5000$IhZqndeQN.hVM6NS$xsuHVbD9hwwwKae6wd7FBL8t.IULjzBoVs4EXzZhQI6','t14','intermesh.localhost/t14/Maildir/','intermesh.localhost/t14/',524288,1568799932,1568799932,1,0),(73,1,NULL,'t16@intermesh.localhost','$5$rounds=5000$OOdtklFAn5WJWlDo$1/54M224FPhlIL30p9Eh/6R.UeczUBl6vuCO4xTMzCD','t16','intermesh.localhost/t16/Maildir/','intermesh.localhost/t16/',524288,1568800247,1568800247,1,0),(74,1,NULL,'t17@intermesh.localhost','$5$rounds=5000$C9WgT1lkHBOa2n7o$yd66FqlbUi1H/oqNBruitG3hYJ24lmbQhsWCGRyQhNC','t17','intermesh.localhost/t17/Maildir/','intermesh.localhost/t17/',524288,1568801006,1568801006,1,0),(75,1,NULL,'t18@intermesh.localhost','$5$rounds=5000$8LH1Bgn0RzW/OVtd$.jwqLysLvZg/A8zyBDW1qnt.nb97DD08dzWJ0Cint0A','t18','intermesh.localhost/t18/Maildir/','intermesh.localhost/t18/',524288,1568982413,1568982413,1,0),(76,1,NULL,'t19@intermesh.localhost','$5$rounds=5000$e2hQ3eMNiITtzqAi$cYl/GLINPg5lwEd0wvxJDFevg9Gf6.AeTpubRryp/u/','t19','intermesh.localhost/t19/Maildir/','intermesh.localhost/t19/',524288,1568982656,1568982656,1,0),(77,1,NULL,'t21@intermesh.localhost','$5$rounds=5000$b2k6NeUbLnWjBqlR$JQbyFBlKsUo.sUpC3cYICPhcHRxbeWJsNw4pKG8qOB7','t21','intermesh.localhost/t21/Maildir/','intermesh.localhost/t21/',524288,1568983553,1568983553,1,0),(78,1,NULL,'t23@intermesh.localhost','$5$rounds=5000$9wMOXjZjlXqH53RH$XpWb2L3nJCjKj8U.44bEFWqcjX3mSjMdhFohN3I7sD6','t23','intermesh.localhost/t23/Maildir/','intermesh.localhost/t23/',524288,1568983703,1568983703,1,0),(79,1,NULL,'t24@intermesh.localhost','$5$rounds=5000$ybx8TNTseTrXb1oo$wEXS32.K9vpR8XXzqVncL9sQTatVzNYDsUYMD8zjo.4','t24','intermesh.localhost/t24/Maildir/','intermesh.localhost/t24/',524288,1568993496,1568993496,1,0),(80,1,NULL,'t25@intermesh.localhost','$5$rounds=5000$UQHvgUFEYtkmOyzs$BHLGFFra3D5yjbuwvRUiZg4A3/M044zuobHf24QzTJ9','t25','intermesh.localhost/t25/Maildir/','intermesh.localhost/t25/',524288,1568993541,1568993541,1,0),(81,1,NULL,'t26@intermesh.localhost','$5$rounds=5000$PhOlpWvNuJxt.FpV$.GZs3wVl.dG4Xu4EqiGibTcyKBMHoV649NIOTYX6dx5','t26','intermesh.localhost/t26/Maildir/','intermesh.localhost/t26/',524288,1568993554,1568993554,1,0),(82,1,NULL,'t27@intermesh.localhost','$5$rounds=5000$SugEXFig9zh91jtV$1hISkQXrnp63UVpaILoNREQOdaLktOUggN2KAtNiQI6','t27','intermesh.localhost/t27/Maildir/','intermesh.localhost/t27/',524288,1568993577,1568993577,1,0),(83,1,NULL,'t31@intermesh.localhost','$5$rounds=5000$.feYGrC.YiMdITlY$EPQ7LubevftYSxZf0VlDPn/LBJLfILu8s1EHbpPs4p8','t31','intermesh.localhost/t31/Maildir/','intermesh.localhost/t31/',524288,1569500801,1569500801,1,0);
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
-- Table structure for table `pr2_employee_activity_rate`
--

DROP TABLE IF EXISTS `pr2_employee_activity_rate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_employee_activity_rate` (
  `activity_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `external_rate` float NOT NULL,
  PRIMARY KEY (`activity_id`,`employee_id`),
  KEY `fk_pr2_employee_activity_idx` (`employee_id`),
  CONSTRAINT `fk_pr2_employee_activity` FOREIGN KEY (`employee_id`) REFERENCES `pr2_employees` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_employee_activity_rate`
--

LOCK TABLES `pr2_employee_activity_rate` WRITE;
/*!40000 ALTER TABLE `pr2_employee_activity_rate` DISABLE KEYS */;
/*!40000 ALTER TABLE `pr2_employee_activity_rate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_employees`
--

DROP TABLE IF EXISTS `pr2_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_employees` (
  `user_id` int(11) NOT NULL,
  `closed_entries_time` int(11) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_employees`
--

LOCK TABLES `pr2_employees` WRITE;
/*!40000 ALTER TABLE `pr2_employees` DISABLE KEYS */;
INSERT INTO `pr2_employees` VALUES (1,NULL,1565707867,1565707867,0,0),(2,NULL,1561972066,1561972066,120,60),(3,NULL,1561972066,1561972066,80,40),(4,NULL,1561972066,1561972066,90,45);
/*!40000 ALTER TABLE `pr2_employees` ENABLE KEYS */;
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
  `supplier_company_id` int(11) DEFAULT NULL,
  `budget_category_id` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL,
  `comments` varchar(1024) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `id_number` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `quantity` float NOT NULL DEFAULT 1,
  `unit_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `contact_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_expense_budgets`
--

LOCK TABLES `pr2_expense_budgets` WRITE;
/*!40000 ALTER TABLE `pr2_expense_budgets` DISABLE KEYS */;
INSERT INTO `pr2_expense_budgets` VALUES (1,'Machinery',10000,0,1561972066,1561972066,NULL,NULL,2,'','',1,'',NULL);
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
  `invoice_id` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `mtime` int(11) NOT NULL,
  `expense_budget_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  KEY `fk_pr2_expenses_pr2_expense_budgets1_idx` (`expense_budget_id`),
  KEY `fk_pr2_expenses_pr2_projects1_idx` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_expenses`
--

LOCK TABLES `pr2_expenses` WRITE;
/*!40000 ALTER TABLE `pr2_expenses` DISABLE KEYS */;
INSERT INTO `pr2_expenses` VALUES (1,2,3000,21,1561972066,'1234','Rocket fuel',1561972066,NULL),(2,2,2000,21,1561972066,'1235','Fuse machine',1561972066,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours`
--

LOCK TABLES `pr2_hours` WRITE;
/*!40000 ALTER TABLE `pr2_hours` DISABLE KEYS */;
INSERT INTO `pr2_hours` VALUES (1,1,534,0,1565676000,8.9,'',0,0,0,NULL,1565708049,1565708049,4,NULL,0,50,50);
/*!40000 ALTER TABLE `pr2_hours` ENABLE KEYS */;
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
  `company_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `threshold_mails` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `due_time` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
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
  KEY `fk_pr2_projects_pr2_templates1_idx` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_projects`
--

LOCK TABLES `pr2_projects` WRITE;
/*!40000 ALTER TABLE `pr2_projects` DISABLE KEYS */;
INSERT INTO `pr2_projects` VALUES (1,1,111,'Demo','','Just a placeholder for sub projects.',0,1561972066,1570708815,NULL,1,1561972066,0,0,NULL,47,0,0,0,'Demo',1,1,2,1,0,NULL,0,''),(2,1,111,'[001] Develop Rocket 2000','','Better range and accuracy',2,1561972066,1570708815,NULL,1,1561932000,1564610400,2,'',48,0,0,0,'Demo/[001] Develop Rocket 2000',1,1,2,2,1,10,10,''),(3,1,111,'[001] Develop Rocket Launcher','','Better range and accuracy',2,1561972066,1561972066,NULL,1,1561972066,1564650466,2,NULL,0,0,0,0,'Demo/[001] Develop Rocket Launcher',1,1,2,2,1,NULL,0,''),(4,1,52,'t1','','',0,1565707915,1565707925,NULL,1,1565647200,0,0,'',0,1,0,0,'Demo/t1',1,1,1,2,1,50,50,''),(5,1,52,'Hi Hubert','ACME Corporation','',2,1571047876,1571047876,NULL,1,1571004000,0,0,'System Administrator (Users)',0,1,0,0,'Hi Hubert',1,1,1,2,0,NULL,0,'');
/*!40000 ALTER TABLE `pr2_projects` ENABLE KEYS */;
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
INSERT INTO `pr2_resources` VALUES (2,1,0,0,0,0,0),(2,2,16,120,60,0,0),(2,3,100,80,40,0,0),(2,4,16,90,45,0,0),(3,1,0,0,0,0,0),(3,3,16,80,40,0,0),(4,1,0,0,0,0,0);
/*!40000 ALTER TABLE `pr2_resources` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pr2_standard_tasks`
--

DROP TABLE IF EXISTS `pr2_standard_tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pr2_standard_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `units` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_billable` tinyint(1) NOT NULL DEFAULT 1,
  `is_always_billable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_standard_tasks`
--

LOCK TABLES `pr2_standard_tasks` WRITE;
/*!40000 ALTER TABLE `pr2_standard_tasks` DISABLE KEYS */;
INSERT INTO `pr2_standard_tasks` VALUES (1,'3','ccc',1,'',0,1,0),(2,'1','aaa',1,'',0,1,0),(3,'2','bbb',0,'',0,1,0);
/*!40000 ALTER TABLE `pr2_standard_tasks` ENABLE KEYS */;
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
INSERT INTO `pr2_statuses` VALUES (1,'Ongoing',0,0,1,1,0,0,54),(2,'None',0,0,1,1,0,0,55),(3,'Complete',1,0,1,0,0,0,56);
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
INSERT INTO `pr2_templates` VALUES (1,1,'Projects folder',57,2,'','projects2/template-icons/folder.png',0,NULL,2,1,0,''),(2,1,'Standard project',58,3,'responsible_user_id,expenses,customer,default_distance,contact,budget_fees,travel_costs','projects2/template-icons/project.png',1,NULL,1,1,0,'%y-{autoid}');
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
INSERT INTO `pr2_types` VALUES (1,'Default',1,52,53),(2,'Demo',1,111,112);
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
INSERT INTO `su_announcements` VALUES (1,1,109,0,1561972065,1561972065,'Submit support ticket','Anyone can submit tickets to the support system here:<br /><br /><a href=\"https://localhost:63/modules/site/index.php?r=tickets/externalpage/newTicket\">https://localhost:63/modules/site/index.php?r=tickets/externalpage/newTicket</a><br /><br />Anonymous ticket posting can be disabled in the ticket module settings.'),(2,1,110,0,1561972065,1561972065,'Welcome to GroupOffice','This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!');
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
  `announcement_id` int(11) NOT NULL DEFAULT 0,
  `announcement_ctime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_latest_read_announcement_records`
--

LOCK TABLES `su_latest_read_announcement_records` WRITE;
/*!40000 ALTER TABLE `su_latest_read_announcement_records` DISABLE KEYS */;
INSERT INTO `su_latest_read_announcement_records` VALUES (1,2,1561972065),(2,2,1561972065),(3,2,1561972065),(4,2,1561972065),(5,2,1561972065),(6,2,1561972065);
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
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_notes`
--

LOCK TABLES `su_notes` WRITE;
/*!40000 ALTER TABLE `su_notes` DISABLE KEYS */;
INSERT INTO `su_notes` VALUES (1,NULL),(2,NULL),(3,NULL),(4,NULL),(5,NULL),(6,NULL);
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
  PRIMARY KEY (`id`)
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
  PRIMARY KEY (`user_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_visible_calendars`
--

LOCK TABLES `su_visible_calendars` WRITE;
/*!40000 ALTER TABLE `su_visible_calendars` DISABLE KEYS */;
INSERT INTO `su_visible_calendars` VALUES (1,1),(2,2),(3,3),(4,4),(5,7),(6,8);
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
  PRIMARY KEY (`user_id`,`tasklist_id`)
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
  `addressbook_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_addressbook` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`addressbook_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
INSERT INTO `ta_portlet_tasklists` VALUES (2,1),(3,2),(4,3);
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
INSERT INTO `ta_settings` VALUES (1,0,'0',0,4),(2,0,'0',0,1),(3,0,'0',0,2),(4,0,'0',0,3);
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
INSERT INTO `ta_tasklists` VALUES (1,'Elmer Fudd',2,91,24,3),(2,'Demo User',3,96,28,3),(3,'Linda Smith',4,101,32,3),(4,'System Administrator',1,106,35,8);
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasks`
--

LOCK TABLES `ta_tasks` WRITE;
/*!40000 ALTER TABLE `ta_tasks` DISABLE KEYS */;
INSERT INTO `ta_tasks` VALUES (1,'69afb1ee-4123-5162-a477-c9256d9d6d0f',2,1,1561972061,1561972061,1,1561972061,1562144861,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(2,'9a2db52a-912b-521d-94d6-336a72e0a531',3,1,1561972061,1561972061,1,1561972061,1562058461,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(3,'ee97359f-e341-5292-b9d9-63be3f9b3db9',1,1,1561972061,1561972061,1,1561972061,1562058461,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(4,'511706f1-b2b6-51d8-9925-af89876d42f8',2,1,1561972061,1561972061,1,1561972061,1562058461,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(5,'9374d8e8-3dcc-5586-a23e-a0885e667e6d',3,1,1561972062,1561972062,1,1561972062,1562058462,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(6,'dd44ae8c-8528-597c-aed1-e9f8bc5e21ee',1,1,1561972062,1561972062,1,1561972062,1562058462,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(7,'da0f4e45-4aa8-5a7a-8035-5a5742fd4285',4,1,1561972062,1561972062,1,1562231262,1562231262,0,'Call: Smith Inc (Q19000001)','','NEEDS-ACTION',0,1562231262,'',0,0,1,0,0),(8,'92b31122-f509-595e-bf5e-d7364cda75a7',4,1,1561972063,1561972063,1,1562231263,1562231263,0,'Call: ACME Corporation (Q19000002)','','NEEDS-ACTION',0,1562231263,'',0,0,1,0,0),(9,'a5e3930e-4249-50ea-ac4b-4e00601dc116',4,1,1572537367,1572537367,1,1572476400,1572476400,0,'ghjgj','','NEEDS-ACTION',0,0,'',0,0,1,0,0),(10,'78738742-a351-5271-b1aa-b850902b6dc3',4,1,1572537521,1572537521,1,1572476400,1572476400,0,'t1','','NEEDS-ACTION',0,0,'',0,0,1,0,0),(11,'5bcf1d4c-2a23-57b9-a3b0-e2b723ffb6d3',4,1,1572537603,1572537603,1,1572476400,1572476400,0,'t3','','NEEDS-ACTION',0,0,'',0,0,1,0,0),(12,'6872de0c-9e71-53c2-9611-03a438197e19',4,1,1572537654,1572537654,1,1572476400,1572476400,0,'ghjgjhgjh1','','NEEDS-ACTION',0,0,'',0,0,1,0,0),(13,'4c2934cd-16dc-5925-9763-854eeea13dc5',4,1,1572538163,1572538163,1,1572476400,1572476400,0,'t4','','NEEDS-ACTION',0,0,'',0,0,1,0,0);
/*!40000 ALTER TABLE `ta_tasks` ENABLE KEYS */;
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
INSERT INTO `ti_messages` VALUES (1,1,0,1,0,0,'My rocket always circles back right at me? How do I aim right?','',0,0,1561972065,1561972065,0,0,0,'',NULL,NULL),(2,1,0,1,0,0,'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.','',1,2,1561972065,1561972065,0,0,0,'',NULL,NULL),(3,1,-1,1,1,0,'Gee I don\'t know how that can happen. I\'ll send you some new ones!','',0,2,1561972065,1561972065,0,0,0,'',NULL,NULL),(4,2,0,1,0,0,'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?','',0,0,1561799265,1561799265,0,0,0,'',NULL,NULL),(5,2,0,1,0,0,'Please respond faster. Can\'t you see this ticket is marked in red?','',0,0,1561972065,1561972065,0,0,0,'',NULL,NULL);
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
INSERT INTO `ti_settings` VALUES (1,'admin@intermesh.dev','Group-Office Customer Support',1,'https://localhost:63/modules/site/index.php?r=tickets/externalpage/ticket','{SUBJECT}',1,'groupoffice.png','This is our support system. Please enter your contact information and describe your problem.','Thank you for contacting us. We have received your question and created a ticket for you. we will respond as soon as possible. For future reference, your question has been assigned the following ticket number: {TICKET_NUMBER}.',0,'en',0,NULL,0,0,1,1,NULL,0,0,NULL,1,'{AGENT} just picked up your ticket. We\'ll keep you up to date about our progress.',1,'Number: {NUMBER}\nSubject: {SUBJECT}\nCreated by: {CREATEDBY}\nCompany: {COMPANY}\n\n\nURL: {LINK}\n\n\n{MESSAGE}',0,NULL,0,0);
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_statuses`
--

LOCK TABLES `ti_statuses` WRITE;
/*!40000 ALTER TABLE `ti_statuses` DISABLE KEYS */;
INSERT INTO `ti_statuses` VALUES (1,'In progress',1),(2,'Not resolved',1);
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
INSERT INTO `ti_tickets` VALUES (1,'201900001',77510612,1,-1,1,1,0,2,'ACME Corporation',2,'Wile','E.','Coyote','wile@acme.demo','','Malfunctioning rockets',1561972064,1561972065,1,0,0,0,0,1561972065,'',1,NULL,0,1561972065,1561972065),(2,'201900002',17512177,1,0,1,1,0,2,'ACME Corporation',2,'Wile','E.','Coyote','wile@acme.demo','','Can I speed up my rockets?',1561799265,1561972065,1,0,1,0,0,1561972065,'',1,NULL,0,1561972065,1561972065);
/*!40000 ALTER TABLE `ti_tickets` ENABLE KEYS */;
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
INSERT INTO `ti_types` VALUES (1,'IT',NULL,1,65,NULL,0,5,NULL,0,0,NULL,NULL,1,0,0,0,0,NULL,0,NULL,0,NULL,71,NULL),(2,'Sales',NULL,1,66,NULL,0,6,NULL,0,0,NULL,NULL,0,0,0,0,0,NULL,0,NULL,0,NULL,72,NULL);
/*!40000 ALTER TABLE `ti_types` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-11-21 15:03:05

