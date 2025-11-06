drop database if exists go62test;
create database go62test;
use go62test;

-- MariaDB dump 10.18  Distrib 10.5.8-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: groupoffice_62
-- ------------------------------------------------------
-- Server version	10.5.8-MariaDB-1:10.5.8+maria~focal

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
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `default_salutation` varchar(255) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `users` tinyint(1) NOT NULL DEFAULT 0,
  `create_folder` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addressbooks`
--

LOCK TABLES `ab_addressbooks` WRITE;
/*!40000 ALTER TABLE `ab_addressbooks` DISABLE KEYS */;
INSERT INTO `ab_addressbooks` VALUES (1,1,'Prospects',5,'Dear {first_name}',0,0,0),(2,1,'Suppliers',6,'Dear {first_name}',0,0,0),(3,1,'Customers',7,'Dear {first_name}',0,0,0),(4,1,'Users',69,'Dear {first_name}',10,1,0),(5,2,'Elmer Fudd',93,'Dear {first_name}',27,0,0),(6,3,'Demo User',99,'Dear {first_name}',32,0,0),(7,4,'Linda Smith',105,'Dear {first_name}',37,0,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslist_companies`
--

LOCK TABLES `ab_addresslist_companies` WRITE;
/*!40000 ALTER TABLE `ab_addresslist_companies` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslist_contacts`
--

LOCK TABLES `ab_addresslist_contacts` WRITE;
/*!40000 ALTER TABLE `ab_addresslist_contacts` DISABLE KEYS */;
INSERT INTO `ab_addresslist_contacts` VALUES (1,4),(1,5),(1,6);
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
  `name` varchar(255) DEFAULT NULL,
  `default_salutation` varchar(50) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_addresslists`
--

LOCK TABLES `ab_addresslists` WRITE;
/*!40000 ALTER TABLE `ab_addresslists` DISABLE KEYS */;
INSERT INTO `ab_addresslists` VALUES (1,NULL,1,123,'Test','Dear sir/madam',1591260411,1591260411);
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
  `name` varchar(100) DEFAULT '',
  `name2` varchar(100) DEFAULT '',
  `address` varchar(100) DEFAULT '',
  `address_no` varchar(100) DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `zip` varchar(10) DEFAULT '',
  `city` varchar(50) DEFAULT '',
  `state` varchar(50) DEFAULT '',
  `country` varchar(50) DEFAULT '',
  `post_address` varchar(100) DEFAULT '',
  `post_address_no` varchar(100) DEFAULT '',
  `post_latitude` decimal(10,8) DEFAULT NULL,
  `post_longitude` decimal(11,8) DEFAULT NULL,
  `post_city` varchar(50) DEFAULT '',
  `post_state` varchar(50) DEFAULT '',
  `post_country` varchar(50) DEFAULT '',
  `post_zip` varchar(10) DEFAULT '',
  `phone` varchar(30) DEFAULT '',
  `fax` varchar(30) DEFAULT '',
  `email` varchar(75) DEFAULT '',
  `homepage` varchar(100) DEFAULT '',
  `comment` text DEFAULT NULL,
  `bank_no` varchar(50) DEFAULT '',
  `bank_bic` varchar(11) DEFAULT '',
  `vat_no` varchar(30) DEFAULT '',
  `invoice_email` varchar(75) DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `email_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `crn` varchar(50) DEFAULT '',
  `iban` varchar(100) DEFAULT '',
  `photo` varchar(255) NOT NULL DEFAULT '',
  `color` char(6) NOT NULL DEFAULT '000000',
  PRIMARY KEY (`id`),
  KEY `addressbook_id` (`addressbook_id`),
  KEY `addressbook_id_2` (`addressbook_id`),
  KEY `link_id` (`link_id`),
  KEY `link_id_2` (`link_id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_companies`
--

LOCK TABLES `ab_companies` WRITE;
/*!40000 ALTER TABLE `ab_companies` DISABLE KEYS */;
INSERT INTO `ab_companies` VALUES (1,NULL,1,3,'Smith Inc','','Kalverstraat','1',NULL,NULL,'1012 NX','Amsterdam','Noord-Holland','NL','Kalverstraat','1',NULL,NULL,'Amsterdam','Noord-Brabant','NL','1012 NX','+31 (0) 10 - 1234567','+31 (0) 1234567','info@smith.demo','http://www.smith.demo','Just a demo company','','','NL 1234.56.789.B01','',1579529586,1579529586,1,1,0,'','','','000000'),(2,NULL,1,3,'ACME Corporation','','1111 Broadway','',NULL,NULL,'10019','New York','NY','US','1111 Broadway','',NULL,NULL,'New York','NY','US','10019','(555) 123-4567','(555) 123-4567','info@acme.demo','http://www.acme.demo','The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]','','','US 1234.56.789.B01','',1579529586,1579529586,1,1,0,'','','','000000'),(3,NULL,1,4,'ACME Rocket Powered Products','','1111 Broadway','',NULL,NULL,'10019','New York','NY','US','1111 Broadway','',NULL,NULL,'New York','NY','US','10019','(555) 123-4567','(555) 123-4567','info@acmerpp.demo','http://www.acmerpp.demo','The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]','','','US 1234.56.789.B01','',1579529586,1579529586,1,1,0,'','','','000000');
/*!40000 ALTER TABLE `ab_companies` ENABLE KEYS */;
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
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `initials` varchar(10) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `suffix` varchar(50) NOT NULL DEFAULT '',
  `sex` enum('M','F') NOT NULL DEFAULT 'M',
  `birthday` date DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `email2` varchar(100) NOT NULL DEFAULT '',
  `email3` varchar(100) NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT 0,
  `department` varchar(100) NOT NULL DEFAULT '',
  `function` varchar(50) NOT NULL DEFAULT '',
  `home_phone` varchar(30) NOT NULL DEFAULT '',
  `work_phone` varchar(30) NOT NULL DEFAULT '',
  `fax` varchar(30) NOT NULL DEFAULT '',
  `work_fax` varchar(30) NOT NULL DEFAULT '',
  `cellular` varchar(30) NOT NULL DEFAULT '',
  `cellular2` varchar(30) NOT NULL DEFAULT '',
  `homepage` varchar(255) DEFAULT NULL,
  `country` varchar(50) NOT NULL DEFAULT '',
  `state` varchar(50) NOT NULL DEFAULT '',
  `city` varchar(50) NOT NULL DEFAULT '',
  `zip` varchar(10) NOT NULL DEFAULT '',
  `address` varchar(100) NOT NULL DEFAULT '',
  `address_no` varchar(100) NOT NULL DEFAULT '',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `salutation` varchar(100) NOT NULL DEFAULT '',
  `email_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `go_user_id` int(11) NOT NULL DEFAULT 0,
  `photo` varchar(255) NOT NULL DEFAULT '',
  `action_date` int(11) NOT NULL DEFAULT 0,
  `url_linkedin` varchar(100) DEFAULT NULL,
  `url_facebook` varchar(100) DEFAULT NULL,
  `url_twitter` varchar(100) DEFAULT NULL,
  `skype_name` varchar(100) DEFAULT NULL,
  `color` char(6) NOT NULL DEFAULT '000000',
  PRIMARY KEY (`id`),
  KEY `addressbook_id` (`addressbook_id`),
  KEY `email` (`email`),
  KEY `email2` (`email2`),
  KEY `email3` (`email3`),
  KEY `last_name` (`last_name`),
  KEY `go_user_id` (`go_user_id`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_contacts`
--

LOCK TABLES `ab_contacts` WRITE;
/*!40000 ALTER TABLE `ab_contacts` DISABLE KEYS */;
INSERT INTO `ab_contacts` VALUES (1,'719364e4-855f-54c7-8e03-d20460f77b83',1,4,'System','','Administrator','','','','M',NULL,'admin@intermesh.localhost','','',0,'','','','','','','','',NULL,'','','','','','',NULL,NULL,NULL,1579529571,1579601720,1,'Dear System',1,0,1,'1.jpg',0,NULL,NULL,NULL,NULL,'000000'),(2,'e7b7df34-e2b7-5d86-a429-4a430163805e',1,3,'John','','Smith','','','','M',NULL,'john@smith.demo','','',1,'','CEO','','','','','06-12345678','',NULL,'NL','Noord-Holland','Amsterdam','1012 NX','Kalverstraat','1',NULL,NULL,NULL,1579529586,1579529586,1,'Dear Mr. Smith',1,0,0,'addressbook/photos/3/con_2.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','echo123','000000'),(3,'4abeedd8-c256-58c6-98eb-67c3c835b40d',1,3,'Wile','E.','Coyote','','','','M',NULL,'wile@acme.demo','','',2,'','CEO','','','','','06-12345678','',NULL,'US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1579529586,1579529586,1,'Dear Mr. Coyote',1,25,0,'addressbook/photos/3/con_3.jpg',0,'http://www.linkedin.com','http://www.facebook.com','http://www.twitter.com','test','000000'),(4,'a6a3e204-ecae-53c8-8e41-eaea6bf8696b',1,4,'Elmer','','Fudd','','','','M',NULL,'elmer@intermesh.localhost','','',3,'','CEO','','','','','06-12345678','','','US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1579529586,1601299706,1,'Dear Elmer',1,0,2,'4.jpg',0,NULL,NULL,NULL,NULL,'000000'),(5,'06b72fce-d130-59a2-aafd-8e3a107d22c9',1,4,'Demo','','User','','','','M',NULL,'test@intermesh.localhost','','',3,'','CEO','','','','','06-12345678','','','US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1579529587,1602829979,1,'Dear Demo',1,0,3,'5.jpg',0,NULL,NULL,NULL,NULL,'000000'),(6,'9a259250-41df-51ed-a23f-83373ebffae9',1,4,'Linda','','Smith','','','','M',NULL,'linda@acmerpp.demo','','',3,'','CEO','','','','','06-12345678','',NULL,'US','NY','New York','10019','1111 Broadway','',NULL,NULL,NULL,1579529587,1579529587,1,'Dear Linda',1,0,4,'',0,NULL,NULL,NULL,NULL,'000000');
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
  `name` varchar(255) NOT NULL DEFAULT '',
  `parameters` varchar(1023) NOT NULL DEFAULT '',
  `value` varchar(1023) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_default_email_account_templates`
--

LOCK TABLES `ab_default_email_account_templates` WRITE;
/*!40000 ALTER TABLE `ab_default_email_account_templates` DISABLE KEYS */;
INSERT INTO `ab_default_email_account_templates` VALUES (1,1),(3,1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_default_email_templates`
--

LOCK TABLES `ab_default_email_templates` WRITE;
/*!40000 ALTER TABLE `ab_default_email_templates` DISABLE KEYS */;
INSERT INTO `ab_default_email_templates` VALUES (1,1),(2,1);
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
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `content` longblob NOT NULL,
  `extension` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ab_email_templates`
--

LOCK TABLES `ab_email_templates` WRITE;
/*!40000 ALTER TABLE `ab_email_templates` DISABLE KEYS */;
INSERT INTO `ab_email_templates` VALUES (1,1,0,'Default',8,'Message-ID: <8a5fce7ad7ee617fa260b9d3f12ce0c5@localhost>\r\nDate: Mon, 20 Jan 2020 15:12:47 +0100\r\nFrom: \r\nMIME-Version: 1.0\r\nContent-Type: multipart/alternative;\r\n boundary=\"_=_swift_v4_1579529567_7fd5e288635ef701920e3e3663e979f2_=_\"\r\n\r\n\r\n--_=_swift_v4_1579529567_7fd5e288635ef701920e3e3663e979f2_=_\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n{salutation},\r\n\r\n{body}\r\n\r\nBest regards\r\n\r\n\r\n{user:name}\r\n{usercompany:name}\r\n\r\n--_=_swift_v4_1579529567_7fd5e288635ef701920e3e3663e979f2_=_\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n{salutation},<br />\r\n<br />\r\n{body}<br />\r\n<br />\r\nBest regards<br />\r\n<br />\r\n<br />\r\n{user:name}<br />\r\n{usercompany:name}<br />\r\n\r\n--_=_swift_v4_1579529567_7fd5e288635ef701920e3e3663e979f2_=_--\r\n',''),(2,1,1,'Letter',9,'PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"�X�H���Bk�RbG����	+��73z�yb����r�� �<�t�p>�[0�������v��\ZA��SH���]57�J�d���+���r��!�Q�NS�+��6������d�&c鑴�8���G�S��s�<C��q����PKvՎ��\0\0\0�\0\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlŐ�N�0��<E�;K�M��i���x��FJ�*-}{��;Q��&qK������ݧ�����|!V��5UƟ\n�~x��H�|�<r@���n��5��\"�{�C!��\\)�\r:���S��� �o8)�k���C�:�U@1��1-�٭�ƭ�P��42���N~���N���B�C/؋Wr0	t����2ˤ\Z��;\\�a����D�\\�G�疚`ߠo�;�]d�o��\'�2jq-�\r�QW�rs�[��߿�~u����PK�I��\0\0w\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�V[o�0~߯����B�MM+Ƅ@B����!^۲���g����d	c���>�������6!�3\Z�޵k[��,�t�?�&W}ے\nh�Q�;$��/w�P�A���T�����CǑa��׌#���L$��Tl����\")��	q|��:	`jߗZ�8�;�Ѐ���q}7<ηSH�� ��\r�!%�Z\Z\r�ɗ�V���A��?\n3d8b٘Q%)����B[���s�*6��QY�R\"-61m�5a�cJC�q�9\\%�a�o3��1��@�P\Z��gj-�J#B �Hb8Y�GTts!#L����7*(��R�-%��f/#@7Z�>�u��:����:�\\-����S8��{^�M�x��=�H}�G��^��y��ɛ��۠��@�_e�\r��(;�@�ݾ�c�3TH���Q-�HYsL��\\{�_(��$��EX�-M�L�6q�Oڅu��/�=�V1�ժg��B��\'1���`�m�k����ҥ��v� m�U)��A�������?�D����5�����W����2���n��߄�KU���*���&�,�ʚb\Z�.4!0�tr���+�n]Z�\"MV��i�9�����N�����c:�T=�-ޮF\rҌFh�޲�����(h�F��D��i䐾���k:���X[�ܖL�����T��=Z�;eS~���PKT̓�\0\0E\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�W[o�0~�WDy^�tE�5P\r���b���Ib�%�O��i�;����6n�`}�{|>��Ή�����H��X��,�a\0�j�U>?]��^��E�Z�,\\�\r��g�u�4�$(��&zVF%� �IN��:��2�Y�)tM؍0��@,�(����e�H��4y��w\\�IO#���k^�~��}�+)z\\��ZV\ZM�Z�)Z^I�\Z����3�(����(w��[g���/5[��l�4W��Ɋ�Yh��Qz\Z\r������z�I��=o�n_4�1}M%5�\n8Z�\Z�`a4�(ڣར���8�c��\\���Ed\0�G�9�&� �Qp�a�5z�>Ɵt�3\",��.7�n�>6%��]�D�E$��S�k)�o��L3n5�d�+G�Q���g7ڜ $�bB��v���9�!@s\Z,u���̸��}q(���t�����|����pv��B�n������=�����/�\rX��0������,���j%Y�h���������rP��$�fe~�W�L_N\\UR���x�*���b1F�һ&�=*�\Z��\Z�7�\n7F�\r��7�ϋ-u��e%�[��t8�K\"Z����o�_�i�\r4����\\/[�+��Μ��\\����kr�bڄ+��G�R0�6�}�<yF*�]��\\F}�mJ��;PKm�Ø\0\0�\r\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��טd���)�n�f霨XH�����-4�;������b��1�:��)�)��;v�.7�H(���wБ$�w�5�\0\r�\".u���&u+S�c���G+1��H�8\ZO^�Қ���4�2���Wܞ���_z�/!{����b}_sz�C�Q��bo^~<�T���z�7n^��M;�Mq�0侟��6�Y�z�ͤ�L�z�^�$�\0PKI���\0\0\0j\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xml}��N� ��}�����M�v��]����w��mh�p��^�[un����C1ݪ&ـu��%�A	h��R�J�����8�u͛VC�v�д�(�a���h[�KpIiǄ)��{�0vb\r��,:4��U܇Ү��⃯\0_r�x^s�q/L�`D{e-���M�C\n�w�f��J����+�_���Y8vr��@u]�uy�B~�_�O���U	@U�W3a�{�� `������~1CUpLRr��тNX>ft��V�?������jV���׆Q����g�p��a�K	���?E��j����4O�(F&,��\ZG>8b�?��Ҹ�P���U_PKt�G\0\0�\0\0PK\0\0�D�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0�D�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0�D�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0�D�BvՎ��\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0�D�B�I��\0\0w\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0�D�BT̓�\0\0E\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0H\0\0word/styles.xmlPK\0\0\0�D�Bm�Ø\0\0�\r\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0)\0\0word/document.xmlPK\0\0\0�D�BI���\0\0\0j\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0docProps/app.xmlPK\0\0\0�D�Bt�G\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\"\0\0docProps/core.xmlPK\0\0\0�D�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0\0\0\0\0','docx');
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
  `name` varchar(32) DEFAULT NULL,
  `sql` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies` (`companies`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `error_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `error_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `subject` varchar(100) DEFAULT NULL,
  `message_path` varchar(255) DEFAULT NULL,
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
  `temp_pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
-- Table structure for table `bm_bookmarks`
--

DROP TABLE IF EXISTS `bm_bookmarks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bm_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(64) NOT NULL,
  `content` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `public_icon` tinyint(1) NOT NULL DEFAULT 1,
  `open_extern` tinyint(1) NOT NULL DEFAULT 1,
  `behave_as_module` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) NOT NULL,
  `show_in_startmenu` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bm_categories`
--

LOCK TABLES `bm_categories` WRITE;
/*!40000 ALTER TABLE `bm_categories` DISABLE KEYS */;
INSERT INTO `bm_categories` VALUES (1,1,27,'General',0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `order_id_prefix` varchar(50) DEFAULT NULL,
  `order_id_length` int(11) NOT NULL DEFAULT 6,
  `show_statuses` varchar(100) DEFAULT NULL,
  `next_id` int(11) NOT NULL DEFAULT 0,
  `default_vat` double NOT NULL DEFAULT 19,
  `currency` varchar(10) DEFAULT NULL,
  `order_csv_template` text DEFAULT NULL,
  `item_csv_template` text DEFAULT NULL,
  `country` char(2) NOT NULL,
  `call_after_days` tinyint(4) NOT NULL DEFAULT 0,
  `sender_email` varchar(100) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_books`
--

LOCK TABLES `bs_books` WRITE;
/*!40000 ALTER TABLE `bs_books` DISABLE KEYS */;
INSERT INTO `bs_books` VALUES (1,1,'Quotes',11,'Q%y',6,NULL,2,19,'€',NULL,NULL,'NL',3,NULL,NULL,0,0,0,0,0,43,0,0,0,0,0,0,0,14),(2,1,'Orders',16,'O%y',6,NULL,2,19,'€',NULL,NULL,'NL',0,NULL,NULL,0,0,0,0,0,44,0,0,0,0,0,0,0,14),(3,1,'Invoices',21,'I%y',6,NULL,2,19,'€',NULL,NULL,'NL',0,NULL,NULL,0,0,0,0,0,45,0,0,0,0,0,0,0,14);
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
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  `content` longblob NOT NULL,
  `extension` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `vat` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `book_id` (`expense_book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `supplier` varchar(100) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `language` varchar(10) NOT NULL DEFAULT '',
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_payments`
--

LOCK TABLES `bs_order_payments` WRITE;
/*!40000 ALTER TABLE `bs_order_payments` DISABLE KEYS */;
INSERT INTO `bs_order_payments` VALUES (1,1,1579529588,20999.95,'Status: Sent'),(2,2,1579529588,38999.89,'Status: Sent'),(3,3,1579529588,20999.95,'Status: Waiting for payment'),(4,4,1579529588,38999.89,'Status: Waiting for payment'),(5,5,1579529588,20999.95,'Status: Waiting for payment'),(6,6,1579529588,38999.89,'Status: Waiting for payment');
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
INSERT INTO `bs_order_status_history` VALUES (1,1,1,1,1579529588,0,'billing/notifications/1/202001/1/1579529588.eml',NULL),(2,2,1,1,1579529588,0,'billing/notifications/1/202001/2/1579529588.eml',NULL),(3,3,5,1,1579529588,0,'billing/notifications/2/202001/3/1579529588.eml',NULL),(4,4,5,1,1579529588,0,'billing/notifications/2/202001/4/1579529588.eml',NULL),(5,5,9,1,1579529588,0,'billing/notifications/3/202001/5/1579529588.eml',NULL),(6,6,9,1,1579529588,0,'billing/notifications/3/202001/6/1579529588.eml',NULL);
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
  `color` varchar(6) NOT NULL DEFAULT 'FFFFFF',
  `required_status_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `apply_extra_cost` tinyint(1) DEFAULT 0,
  `extra_cost_min_value` double DEFAULT NULL,
  `extra_cost_percentage` double DEFAULT NULL,
  `email_bcc` varchar(100) DEFAULT NULL,
  `email_owner` tinyint(1) NOT NULL DEFAULT 0,
  `ask_to_notify_customer` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_order_statuses`
--

LOCK TABLES `bs_order_statuses` WRITE;
/*!40000 ALTER TABLE `bs_order_statuses` DISABLE KEYS */;
INSERT INTO `bs_order_statuses` VALUES (1,1,0,0,0,0,'FFFFFF',0,12,0,NULL,NULL,'',0,1),(2,1,0,0,0,0,'FFFFFF',0,13,0,NULL,NULL,'',0,1),(3,1,0,0,0,0,'FFFFFF',0,14,0,NULL,NULL,'',0,1),(4,1,0,0,0,0,'FFFFFF',0,15,0,NULL,NULL,'',0,1),(5,2,0,0,0,0,'FFFFFF',0,17,0,NULL,NULL,'',0,1),(6,2,0,0,0,0,'FFFFFF',0,18,0,NULL,NULL,'',0,1),(7,2,0,0,0,0,'FFFFFF',0,19,0,NULL,NULL,'',0,1),(8,2,0,0,0,0,'FFFFFF',0,20,0,NULL,NULL,'',0,1),(9,3,0,0,0,0,'FFFFFF',0,22,0,NULL,NULL,'',0,1),(10,3,0,0,0,0,'FFFFFF',0,23,0,NULL,NULL,'',0,1),(11,3,0,0,0,0,'FFFFFF',0,24,0,NULL,NULL,'',0,1),(12,3,0,0,0,0,'FFFFFF',0,25,0,NULL,NULL,'',0,1);
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
  `order_id` varchar(20) NOT NULL DEFAULT '',
  `po_id` varchar(50) NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `btime` int(11) NOT NULL DEFAULT 0,
  `ptime` int(11) NOT NULL DEFAULT 0,
  `costs` double NOT NULL DEFAULT 0,
  `subtotal` double NOT NULL DEFAULT 0,
  `vat` double DEFAULT NULL,
  `total` double NOT NULL DEFAULT 0,
  `authcode` varchar(50) DEFAULT NULL,
  `frontpage_text` text DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL DEFAULT '',
  `customer_to` varchar(255) NOT NULL,
  `customer_salutation` varchar(100) DEFAULT NULL,
  `customer_contact_name` varchar(50) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_address_no` varchar(50) DEFAULT NULL,
  `customer_zip` varchar(20) DEFAULT NULL,
  `customer_city` varchar(50) DEFAULT NULL,
  `customer_state` varchar(50) DEFAULT NULL,
  `customer_country` char(2) NOT NULL,
  `customer_vat_no` varchar(50) DEFAULT NULL,
  `customer_crn` varchar(50) DEFAULT '',
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_extra` varchar(255) NOT NULL DEFAULT '',
  `webshop_id` int(11) NOT NULL DEFAULT 0,
  `recur_type` varchar(10) NOT NULL DEFAULT '',
  `payment_method` varchar(50) NOT NULL DEFAULT '',
  `recurred_order_id` int(11) NOT NULL DEFAULT 0,
  `reference` varchar(100) NOT NULL DEFAULT '',
  `order_bonus_points` int(11) DEFAULT NULL,
  `pagebreak` tinyint(1) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) DEFAULT NULL,
  `for_warehouse` tinyint(1) NOT NULL DEFAULT 0,
  `dtime` int(11) NOT NULL DEFAULT 0,
  `total_paid` double NOT NULL DEFAULT 0,
  `due_date` int(11) DEFAULT NULL,
  `other_shipping_address` tinyint(1) NOT NULL DEFAULT 0,
  `shipping_to` varchar(255) DEFAULT NULL,
  `shipping_salutation` varchar(100) DEFAULT NULL,
  `shipping_address` varchar(100) DEFAULT NULL,
  `shipping_address_no` varchar(50) DEFAULT NULL,
  `shipping_zip` varchar(20) DEFAULT NULL,
  `shipping_city` varchar(50) DEFAULT NULL,
  `shipping_state` varchar(50) DEFAULT NULL,
  `shipping_country` char(2) DEFAULT NULL,
  `shipping_extra` varchar(255) DEFAULT NULL,
  `telesales_agent` int(11) DEFAULT NULL,
  `fieldsales_agent` int(11) DEFAULT NULL,
  `extra_costs` double NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `book_id` (`book_id`),
  KEY `status_id` (`status_id`),
  KEY `recurred_order_id` (`recurred_order_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_orders`
--

LOCK TABLES `bs_orders` WRITE;
/*!40000 ALTER TABLE `bs_orders` DISABLE KEYS */;
INSERT INTO `bs_orders` VALUES (1,0,1,1,1,1,'Q20000001','',1,2,1579529588,1579529588,1,1579529588,1579529588,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms',NULL,'Kalverstraat','1','1012 NX','Amsterdam',NULL,'NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(2,0,1,1,1,1,'Q20000002','',2,3,1579529588,1579529588,1,1579529588,1579529588,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(3,0,5,2,1,1,'O20000001','',1,2,1579529588,1579529588,1,1579529588,1579529588,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms',NULL,'Kalverstraat','1','1012 NX','Amsterdam',NULL,'NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(4,0,5,2,1,1,'O20000002','',2,3,1579529588,1579529588,1,1579529588,1579529588,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(5,0,9,3,1,1,'I20000001','',1,2,1579529588,1579529588,1,1579529588,1579529588,7000,20999.95,0,20999.95,NULL,'','Smith Inc','Smith Inc','Dear Mr / Ms',NULL,'Kalverstraat','1','1012 NX','Amsterdam',NULL,'NL','NL 1234.56.789.B01','','info@smith.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,20999.95,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0),(6,0,9,3,1,1,'I20000002','',2,3,1579529588,1579529588,1,1579529588,1579529588,13000,38999.89,0,38999.89,NULL,'','ACME Corporation','ACME Corporation','Dear Mr / Ms',NULL,'1111 Broadway','','10019','New York',NULL,'US','US 1234.56.789.B01','','info@acme.demo','',0,'','',0,'',NULL,0,0,NULL,0,0,38999.89,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text DEFAULT NULL,
  `short_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`language_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `type` varchar(15) NOT NULL DEFAULT 'text',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`product_option_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `value` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`product_option_value_id`,`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `image` varchar(255) NOT NULL DEFAULT '',
  `cost_price` double NOT NULL DEFAULT 0,
  `list_price` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `total_price` double NOT NULL DEFAULT 0,
  `supplier_company_id` int(11) NOT NULL DEFAULT 0,
  `supplier_product_id` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `required_products` varchar(255) NOT NULL DEFAULT '',
  `stock_min` int(11) NOT NULL DEFAULT 0,
  `article_id` varchar(190) NOT NULL DEFAULT '',
  `unit` varchar(255) NOT NULL DEFAULT '',
  `unit_stock` varchar(255) NOT NULL DEFAULT '',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) DEFAULT NULL,
  `tracking_code` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  `extra_cost_item_text` varchar(200) DEFAULT NULL,
  `email_subject` varchar(100) DEFAULT NULL,
  `email_template` longtext DEFAULT NULL,
  `screen_template` text DEFAULT NULL,
  `pdf_template_id` int(11) NOT NULL DEFAULT 0,
  `doc_template_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`language_id`,`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL,
  `percentage` double NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  `title` varchar(50) DEFAULT NULL,
  `right_col` text DEFAULT NULL,
  `left_col` text DEFAULT NULL,
  `margin_top` int(11) NOT NULL DEFAULT 30,
  `margin_bottom` int(11) NOT NULL DEFAULT 30,
  `margin_left` int(11) NOT NULL DEFAULT 30,
  `margin_right` int(11) NOT NULL DEFAULT 30,
  `page_format` varchar(20) DEFAULT NULL,
  `stationery_paper` varchar(255) DEFAULT NULL,
  `footer` text DEFAULT NULL,
  `closing` text DEFAULT NULL,
  `number_name` varchar(30) DEFAULT NULL,
  `reference_name` varchar(30) DEFAULT NULL,
  `date_name` varchar(30) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
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
  `html_table` text DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bs_templates`
--

LOCK TABLES `bs_templates` WRITE;
/*!40000 ALTER TABLE `bs_templates` DISABLE KEYS */;
INSERT INTO `bs_templates` VALUES (1,'Quotes',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,1,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(2,'Orders',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,2,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0),(3,'Invoices',NULL,'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo','{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}',30,30,30,30,NULL,NULL,'Footer text','<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>','Invoice no.','Reference','Invoice date',NULL,0,0,0,1,1,1,1,0,3,0,0,30,30,30,365,1,0,0,NULL,0,1,0,1,0,0,0,1,0,0,0);
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
  `code` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `color` varchar(6) NOT NULL,
  PRIMARY KEY (`user_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  `start_hour` tinyint(4) NOT NULL DEFAULT 0,
  `end_hour` tinyint(4) NOT NULL DEFAULT 0,
  `background` varchar(6) DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 1800,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `shared_acl` tinyint(1) NOT NULL DEFAULT 0,
  `show_bdays` tinyint(1) NOT NULL DEFAULT 0,
  `show_completed_tasks` tinyint(1) NOT NULL DEFAULT 1,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `project_id` int(11) NOT NULL DEFAULT 0,
  `tasklist_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `show_holidays` tinyint(1) NOT NULL DEFAULT 1,
  `enable_ics_import` tinyint(1) NOT NULL DEFAULT 0,
  `ics_import_url` varchar(512) NOT NULL DEFAULT '',
  `tooltip` varchar(127) NOT NULL DEFAULT '',
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_calendars`
--

LOCK TABLES `cal_calendars` WRITE;
/*!40000 ALTER TABLE `cal_calendars` DISABLE KEYS */;
INSERT INTO `cal_calendars` VALUES (1,1,1,71,'System Administrator',0,0,NULL,1800,0,0,0,1,'',0,0,14,1,0,'','',4),(2,1,2,94,'Elmer Fudd',0,0,NULL,1800,0,0,0,1,'',0,0,28,1,0,'','',39),(3,1,3,100,'Demo User',0,0,NULL,1800,0,0,0,1,'',0,0,33,1,0,'','',124),(4,1,4,106,'Linda Smith',0,0,NULL,1800,0,0,0,1,'',0,0,38,1,0,'','',38),(5,2,1,111,'Road Runner Room',0,0,NULL,1800,0,0,0,1,'',0,0,41,1,0,'','',1),(6,2,1,112,'Don Coyote Room',0,0,NULL,1800,0,0,0,1,'',0,0,42,1,0,'','',22);
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
  `name` varchar(255) NOT NULL,
  `color` char(6) NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
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
  `timezone` varchar(64) NOT NULL DEFAULT '',
  `all_day_event` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(100) NOT NULL DEFAULT '',
  `repeat_end_time` int(11) NOT NULL DEFAULT 0,
  `reminder` int(11) DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `busy` tinyint(1) NOT NULL DEFAULT 1,
  `status` varchar(20) NOT NULL DEFAULT 'NEEDS-ACTION',
  `resource_event_id` int(11) NOT NULL DEFAULT 0,
  `private` tinyint(1) NOT NULL DEFAULT 0,
  `rrule` varchar(100) NOT NULL DEFAULT '',
  `background` char(6) NOT NULL DEFAULT 'ebf1e2',
  `files_folder_id` int(11) NOT NULL,
  `read_only` tinyint(1) NOT NULL DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `exception_for_event_id` int(11) NOT NULL DEFAULT 0,
  `recurrence_id` varchar(20) NOT NULL DEFAULT '',
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
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_events`
--

LOCK TABLES `cal_events` WRITE;
/*!40000 ALTER TABLE `cal_events` DISABLE KEYS */;
INSERT INTO `cal_events` VALUES (1,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',3,3,1598977800,1598979600,'Europe/Amsterdam',0,'t3','','ACME NY Office',0,NULL,1579529587,1600075267,1,1,'NEEDS-ACTION',0,0,'FREQ=WEEKLY;BYDAY=TU','EBF1E2',0,0,NULL,0,'',1),(2,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',4,4,1598977800,1598979600,'Europe/Amsterdam',0,'t3','','ACME NY Office',0,NULL,1579529587,1600075267,1,1,'NEEDS-ACTION',0,0,'FREQ=WEEKLY;BYDAY=TU','EBF1E2',0,0,NULL,0,'',0),(3,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',2,2,1598977800,1598979600,'Europe/Amsterdam',0,'t3','','ACME NY Office',0,NULL,1579529587,1600075267,1,1,'NEEDS-ACTION',0,0,'FREQ=WEEKLY;BYDAY=TU','EBF1E2',0,0,NULL,0,'',0),(4,'2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc',3,3,1579604400,1579608000,'Europe/Amsterdam',0,'Meet Wile',NULL,'ACME NY Office',0,NULL,1579529587,1579529587,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(5,'2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc',4,4,1579604400,1579608000,'Europe/Amsterdam',0,'Meet Wile','','ACME NY Office',0,NULL,1579529587,1579529587,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(6,'2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc',2,2,1579604400,1579608000,'Europe/Amsterdam',0,'Meet Wile','','ACME NY Office',0,NULL,1579529588,1579529587,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(7,'9b5cabdf-12f2-5e6e-bfdf-5e50914836dd',3,3,1579611600,1579615200,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(8,'9b5cabdf-12f2-5e6e-bfdf-5e50914836dd',4,4,1579611600,1579615200,'Europe/Amsterdam',0,'MT Meeting','','ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(9,'9b5cabdf-12f2-5e6e-bfdf-5e50914836dd',2,2,1579611600,1579615200,'Europe/Amsterdam',0,'MT Meeting','','ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(10,'43b24986-a34d-5f4f-87d0-b8412ac21515',4,4,1579687200,1579690800,'Europe/Amsterdam',0,'Project meeting',NULL,'ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(11,'43b24986-a34d-5f4f-87d0-b8412ac21515',3,3,1579687200,1579690800,'Europe/Amsterdam',0,'Project meeting','','ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(12,'b1575a45-5b63-51ca-a8d9-b127b0d7aa8c',4,4,1579694400,1579698000,'Europe/Amsterdam',0,'Meet John',NULL,'ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(13,'b1575a45-5b63-51ca-a8d9-b127b0d7aa8c',3,3,1579694400,1579698000,'Europe/Amsterdam',0,'Meet John','','ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(14,'50ac853d-9a22-5403-98bf-95c3669d06dc',4,4,1579705200,1579708800,'Europe/Amsterdam',0,'MT Meeting',NULL,'ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(15,'50ac853d-9a22-5403-98bf-95c3669d06dc',3,3,1579705200,1579708800,'Europe/Amsterdam',0,'MT Meeting','','ACME NY Office',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(16,'79860a30-0c5a-52d2-824e-f93f98c4cc9b',4,4,1579590000,1579593600,'Europe/Amsterdam',0,'Rocket testing',NULL,'ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(17,'79860a30-0c5a-52d2-824e-f93f98c4cc9b',3,3,1579590000,1579593600,'Europe/Amsterdam',0,'Rocket testing','','ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(18,'6e2dab85-5126-5245-a0c2-bdd4a1e45775',4,4,1579615200,1579618800,'Europe/Amsterdam',0,'Blast impact test',NULL,'ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(19,'6e2dab85-5126-5245-a0c2-bdd4a1e45775',3,3,1579615200,1579618800,'Europe/Amsterdam',0,'Blast impact test','','ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(20,'092895d0-87de-588c-82c6-94d6ea174c09',4,4,1579629600,1579633200,'Europe/Amsterdam',0,'Test range extender',NULL,'ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(21,'092895d0-87de-588c-82c6-94d6ea174c09',3,3,1579629600,1579633200,'Europe/Amsterdam',0,'Test range extender','','ACME Testing fields',0,NULL,1579529588,1579529588,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(28,'e21a9540-eff9-525b-a92a-e85052ffe327',3,3,1598276700,1598281200,'Europe/Amsterdam',0,'test resource','','',0,NULL,1598355792,1598356242,3,1,'CONFIRMED',0,0,'FREQ=WEEKLY;BYDAY=MO','EBF1E2',0,0,NULL,0,'',1),(29,'51fd5897-b763-551a-84db-b5cede1fca26',6,3,1598276700,1598281200,'Europe/Amsterdam',0,'test resource',NULL,'',0,NULL,1598355792,1598356242,3,1,'NEEDS-ACTION',28,0,'FREQ=WEEKLY;BYDAY=MO','FF6666',0,0,NULL,0,'',1),(30,'42ac0ab9-2994-5592-a549-ecef3947bfb1',3,1,1598246100,1598247000,'Europe/Amsterdam',0,'test2','','',0,NULL,1598358294,1598361744,1,1,'CONFIRMED',0,0,'FREQ=DAILY;COUNT=3','EBF1E2',0,0,NULL,0,'',1),(37,'470ecefd-928b-53b8-b334-dc3b6ec0b008',3,1,1598453100,1598454900,'Europe/Amsterdam',0,'m1','','',0,NULL,1598362341,1598363040,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(47,'0109bab9-9422-5bff-b8d6-cd10a781bb7f',3,3,1598954400,1598955300,'Europe/Amsterdam',0,'t1','','',1599083940,NULL,1598880366,1598880371,3,1,'CONFIRMED',0,0,'FREQ=DAILY;UNTIL=20200902T000000','EBF1E2',0,0,NULL,0,'',1),(48,'3f46e34f-c01d-5b0e-ac33-0606cfc78d48',3,3,1598954400,1598955300,'Europe/Amsterdam',0,'t1','','',0,NULL,1598880371,1598880366,3,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(49,'c278e04a-1a9a-5287-95ed-581218f03d4d',3,3,1599587100,1599588000,'Europe/Amsterdam',0,'t2','','',1599688740,NULL,1598880468,1598881053,3,1,'CONFIRMED',0,0,'FREQ=DAILY;UNTIL=20200909T000000','EBF1E2',0,0,NULL,0,'',1),(50,'1590985c-bfb2-5cc1-b4f4-44744c12176c',3,3,1599587100,1599588000,'Europe/Amsterdam',0,'t2','','',0,NULL,1598881053,1598881016,3,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(51,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',3,3,1600278300,1600280100,'Europe/Amsterdam',0,'t2',NULL,'ACME NY Office',0,NULL,1598881255,1598881255,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,1,'',1),(52,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',4,4,1600278300,1600280100,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881255,1598881255,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,2,'',0),(53,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',2,2,1600278300,1600280100,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881255,1598881255,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,3,'',0),(54,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',3,3,1600451100,1600452900,'Europe/Amsterdam',0,'t2',NULL,'ACME NY Office',0,NULL,1598881262,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,1,'',1),(55,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',4,4,1600451100,1600452900,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881262,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,2,'',0),(56,'6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5',2,2,1600451100,1600452900,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881262,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,3,'',0),(57,'dd9e7955-446c-5fd7-b23b-0c90f2316495',3,3,1599673500,1599674400,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881274,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,0,'',1),(58,'dd9e7955-446c-5fd7-b23b-0c90f2316495',2,2,1599673500,1599674400,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881274,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,0,'',0),(59,'dd9e7955-446c-5fd7-b23b-0c90f2316495',4,4,1599673500,1599674400,'Europe/Amsterdam',0,'t2','','ACME NY Office',0,NULL,1598881274,1598881262,3,1,'NEEDS-ACTION',0,0,'','EBF1E2',0,0,NULL,0,'',0),(60,'416f3737-d8bc-5646-a0fc-7e152fad42df',3,3,1598891400,1598892300,'Europe/Amsterdam',0,'t3','','',1598997540,NULL,1598882437,1598882597,3,1,'CONFIRMED',0,0,'FREQ=DAILY;UNTIL=20200901T000000','EBF1E2',0,0,NULL,0,'',1),(61,'e563b8b6-f820-585b-a6d7-d17954e2778c',3,3,1598891400,1598892300,'Europe/Amsterdam',0,'t3','','',0,NULL,1598882597,1598882437,3,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(62,'9a11da7f-fea1-50e1-86be-cfdd77e84544',3,3,1598853600,1598854500,'Europe/Amsterdam',0,'d1','','',1598997540,NULL,1598958447,1598958452,3,1,'CONFIRMED',0,0,'FREQ=DAILY;UNTIL=20200901T000000','EBF1E2',0,0,NULL,0,'',1),(63,'fbcb0307-2e55-54a5-b517-b4b52ac680ba',6,3,1598853600,1598854500,'Europe/Amsterdam',0,'d1',NULL,'',1598997540,NULL,1598958447,1598958452,3,1,'NEEDS-ACTION',62,0,'FREQ=DAILY;UNTIL=20200901T000000','FF6666',0,0,NULL,0,'',1),(64,'79920eb6-3728-54cc-9faf-ef61e98a5d78',3,3,1598944500,1598945400,'Europe/Amsterdam',0,'d1','','',0,NULL,1598958452,1600075257,1,1,'CONFIRMED',0,0,'FREQ=WEEKLY;BYDAY=TU','EBF1E2',0,0,NULL,0,'',1),(65,'fbcb0307-2e55-54a5-b517-b4b52ac680ba',6,3,1598944500,1598945400,'Europe/Amsterdam',0,'d1','','',0,NULL,1598958452,1600075257,1,1,'NEEDS-ACTION',64,0,'FREQ=WEEKLY;BYDAY=TU','FF6666',0,0,NULL,0,'',1),(66,'6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc',3,1,1600331400,1600335900,'Europe/Amsterdam',0,'sadasdsa','','',0,NULL,1600155496,1600155496,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(67,'6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc',2,2,1600331400,1600335900,'Europe/Amsterdam',0,'sadasdsa','','',0,NULL,1600155496,1600155496,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(68,'6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc',4,4,1600331400,1600335900,'Europe/Amsterdam',0,'sadasdsa','','',0,NULL,1600155496,1600155496,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(69,'7280f773-a43b-56e2-a4d9-42445afa6a68',3,1,1600422300,1600427700,'Europe/Amsterdam',0,'saSasaS','','',0,NULL,1600155504,1600155504,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',1),(70,'7280f773-a43b-56e2-a4d9-42445afa6a68',2,2,1600422300,1600427700,'Europe/Amsterdam',0,'saSasaS','','',0,NULL,1600155504,1600155504,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(71,'7280f773-a43b-56e2-a4d9-42445afa6a68',4,4,1600422300,1600427700,'Europe/Amsterdam',0,'saSasaS','','',0,NULL,1600155504,1600155504,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(72,'7280f773-a43b-56e2-a4d9-42445afa6a68',1,1,1600422300,1600427700,'Europe/Amsterdam',0,'saSasaS','','',0,NULL,1600155504,1600155504,1,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0),(79,'8d2cc7a7-2977-5500-b63e-071659dd778d',2,2,1601289000,1601292600,'Europe/Amsterdam',0,'meet Elmer',NULL,'',0,NULL,1601299926,1601299944,2,1,'CONFIRMED',0,0,'','EBF1E2',0,0,NULL,0,'',0);
/*!40000 ALTER TABLE `cal_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cal_events_declined`
--

DROP TABLE IF EXISTS `cal_events_declined`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cal_events_declined` (
  `uid` varchar(190) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`uid`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_exceptions`
--

LOCK TABLES `cal_exceptions` WRITE;
/*!40000 ALTER TABLE `cal_exceptions` DISABLE KEYS */;
INSERT INTO `cal_exceptions` VALUES (1,1,1599582600,51,3,3,1598881255,1598882597),(2,2,1599582600,52,3,3,1598881255,1598882597),(3,3,1599582600,53,3,3,1598881255,1598882597),(4,1,1599755400,54,3,3,1598881262,1598882597),(5,2,1599755400,55,3,3,1598881262,1598882597),(6,3,1599755400,56,3,3,1598881262,1598882597);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_group_admins`
--

LOCK TABLES `cal_group_admins` WRITE;
/*!40000 ALTER TABLE `cal_group_admins` DISABLE KEYS */;
INSERT INTO `cal_group_admins` VALUES (2,1);
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
  `name` varchar(50) DEFAULT NULL,
  `fields` varchar(255) NOT NULL DEFAULT '',
  `show_not_as_busy` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'NEEDS-ACTION',
  `last_modified` varchar(20) NOT NULL DEFAULT '',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_participants`
--

LOCK TABLES `cal_participants` WRITE;
/*!40000 ALTER TABLE `cal_participants` DISABLE KEYS */;
INSERT INTO `cal_participants` VALUES (1,1,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(2,1,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(3,2,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(4,2,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(5,1,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(6,3,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(7,3,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(8,3,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(9,2,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(10,1,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(11,2,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(12,3,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(13,4,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(14,4,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(15,5,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(16,5,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(17,4,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(18,6,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(19,6,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(20,6,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(21,5,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(22,4,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(23,5,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(24,6,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(25,7,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(26,7,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(27,8,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(28,8,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(29,7,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(30,9,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(31,9,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(32,9,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(33,8,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(34,7,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(35,8,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(36,9,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(37,10,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(38,10,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(39,11,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(40,11,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(41,10,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(42,11,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(43,12,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(44,12,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(45,13,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(46,13,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(47,12,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(48,13,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(49,14,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(50,14,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(51,15,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(52,15,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(53,14,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(54,15,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(55,16,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(56,16,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(57,17,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(58,17,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(59,16,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(60,17,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(61,18,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(62,18,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(63,19,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(64,19,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(65,18,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(66,19,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(67,20,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(68,20,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(69,21,'User, Demo','demo@acmerpp.demo',3,5,'NEEDS-ACTION','',0,''),(70,21,'Smith, Linda','linda@acmerpp.demo',4,6,'ACCEPTED','',1,''),(71,20,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(72,21,'Smith, John','john@smith.demo',0,2,'NEEDS-ACTION','',0,''),(114,51,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(115,51,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(116,51,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(117,51,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(118,52,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(119,52,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(120,52,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(121,52,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(122,53,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(123,53,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(124,53,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(125,53,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(126,54,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(127,54,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(128,54,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(129,54,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(130,55,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(131,55,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(132,55,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(133,55,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(134,56,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(135,56,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(136,56,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(137,56,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(138,57,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(139,57,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(140,58,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(141,58,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(142,57,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(143,58,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(144,57,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(145,59,'Coyote, Wile E.','wile@acme.demo',0,3,'NEEDS-ACTION','',0,''),(146,59,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(147,59,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(148,59,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(149,58,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(150,66,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(151,66,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(152,67,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(153,67,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(154,66,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(155,68,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(156,68,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(157,68,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(158,67,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(159,69,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(160,69,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(161,70,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(162,70,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(163,69,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(164,71,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(165,71,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(166,71,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(167,70,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(168,69,'Administrator, System','admin@intermesh.localhost',1,1,'NEEDS-ACTION','',0,''),(169,72,'Administrator, System','admin@intermesh.localhost',1,1,'NEEDS-ACTION','',0,''),(170,72,'Fudd, Elmer','elmer@acmerpp.demo',2,4,'NEEDS-ACTION','',0,''),(171,72,'User, Demo','demo@acmerpp.demo',3,5,'ACCEPTED','',1,''),(172,72,'Smith, Linda','linda@acmerpp.demo',4,6,'NEEDS-ACTION','',0,''),(173,70,'Administrator, System','admin@intermesh.localhost',1,1,'NEEDS-ACTION','',0,''),(174,71,'Administrator, System','admin@intermesh.localhost',1,1,'NEEDS-ACTION','',0,'');
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
  `background` char(6) NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `show_statuses` tinyint(1) NOT NULL DEFAULT 1,
  `check_conflict` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_settings`
--

LOCK TABLES `cal_settings` WRITE;
/*!40000 ALTER TABLE `cal_settings` DISABLE KEYS */;
INSERT INTO `cal_settings` VALUES (1,NULL,'EBF1E2',1,1,1),(2,NULL,'EBF1E2',2,1,1),(3,NULL,'EBF1E2',3,1,1),(4,NULL,'EBF1E2',4,1,1);
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
  `name` varchar(50) DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 1800,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `merge` tinyint(1) NOT NULL DEFAULT 0,
  `owncolor` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cal_views`
--

LOCK TABLES `cal_views` WRITE;
/*!40000 ALTER TABLE `cal_views` DISABLE KEYS */;
INSERT INTO `cal_views` VALUES (1,1,'Everyone',1800,109,0,1),(2,1,'Everyone (Merge)',1800,110,1,1);
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
  `background` char(6) NOT NULL DEFAULT 'CCFFCC',
  PRIMARY KEY (`view_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `col_1` varchar(255) NOT NULL DEFAULT '',
  `col_2` varchar(255) NOT NULL DEFAULT '',
  `col_3` varchar(50) NOT NULL DEFAULT '',
  `col_4` text DEFAULT NULL,
  `col_5` varchar(50) NOT NULL DEFAULT '',
  `col_6` varchar(50) NOT NULL DEFAULT '',
  `col_7` datetime DEFAULT NULL,
  `col_8` double DEFAULT NULL,
  `col_9` varchar(50) NOT NULL DEFAULT '',
  `col_10` varchar(50) NOT NULL DEFAULT '',
  `col_11` varchar(50) NOT NULL DEFAULT '',
  `col_12` varchar(50) NOT NULL DEFAULT '',
  `col_13` tinyint(1) NOT NULL DEFAULT 0,
  `col_14` varchar(50) NOT NULL DEFAULT '',
  `col_15` text DEFAULT NULL,
  `col_16` varchar(50) NOT NULL DEFAULT '',
  `col_17` text DEFAULT NULL,
  `col_18` date DEFAULT NULL,
  `col_19` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ab_companies`
--

LOCK TABLES `cf_ab_companies` WRITE;
/*!40000 ALTER TABLE `cf_ab_companies` DISABLE KEYS */;
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
  `col_20` varchar(255) NOT NULL DEFAULT '',
  `col_21` varchar(255) NOT NULL DEFAULT '',
  `col_22` varchar(50) NOT NULL DEFAULT '',
  `col_23` text DEFAULT NULL,
  `col_24` varchar(50) NOT NULL DEFAULT '',
  `col_25` varchar(50) NOT NULL DEFAULT '',
  `col_26` datetime DEFAULT NULL,
  `col_27` double DEFAULT NULL,
  `col_28` varchar(50) NOT NULL DEFAULT '',
  `col_29` varchar(50) NOT NULL DEFAULT '',
  `col_30` varchar(50) NOT NULL DEFAULT '',
  `col_31` varchar(50) NOT NULL DEFAULT '',
  `col_32` tinyint(1) NOT NULL DEFAULT 0,
  `col_33` varchar(50) NOT NULL DEFAULT '',
  `col_34` text DEFAULT NULL,
  `col_35` varchar(50) NOT NULL DEFAULT '',
  `col_36` text DEFAULT NULL,
  `col_37` date DEFAULT NULL,
  `col_38` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ab_contacts`
--

LOCK TABLES `cf_ab_contacts` WRITE;
/*!40000 ALTER TABLE `cf_ab_contacts` DISABLE KEYS */;
INSERT INTO `cf_ab_contacts` VALUES (2,'','','',NULL,'','',NULL,NULL,'','','','',0,'',NULL,'',NULL,NULL,''),(3,'','','',NULL,'','',NULL,NULL,'','','','',0,'',NULL,'',NULL,NULL,''),(4,'','','',NULL,'','',NULL,NULL,'','','','',0,'',NULL,'',NULL,NULL,''),(5,'','','',NULL,'','',NULL,NULL,'','','','',0,'',NULL,'',NULL,NULL,'');
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
  `name` varchar(100) NOT NULL DEFAULT '',
  `field_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `col_39` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `col_40` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_bs_products`
--

LOCK TABLES `cf_bs_products` WRITE;
/*!40000 ALTER TABLE `cf_bs_products` DISABLE KEYS */;
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
  `col_42` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_cal_calendars`
--

LOCK TABLES `cf_cal_calendars` WRITE;
/*!40000 ALTER TABLE `cf_cal_calendars` DISABLE KEYS */;
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
  `col_41` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_cal_events`
--

LOCK TABLES `cf_cal_events` WRITE;
/*!40000 ALTER TABLE `cf_cal_events` DISABLE KEYS */;
INSERT INTO `cf_cal_events` VALUES (1,''),(2,''),(3,''),(5,''),(6,''),(8,''),(9,''),(11,''),(13,''),(15,''),(17,''),(19,''),(21,''),(28,''),(29,'sfdsfsdfd'),(30,''),(37,''),(47,''),(48,''),(49,''),(50,''),(51,''),(52,''),(53,''),(54,''),(55,''),(56,''),(57,''),(58,''),(59,''),(60,''),(61,''),(62,''),(63,''),(64,''),(65,''),(66,''),(67,''),(68,''),(69,''),(70,''),(71,''),(72,'');
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
  `extends_model` varchar(100) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) DEFAULT NULL,
  `sort_index` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type` (`extends_model`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_categories`
--

LOCK TABLES `cf_categories` WRITE;
/*!40000 ALTER TABLE `cf_categories` DISABLE KEYS */;
INSERT INTO `cf_categories` VALUES (1,'GO\\Addressbook\\Model\\Company',77,'Demo Custom fields',0),(2,'GO\\Addressbook\\Model\\Contact',78,'Demo Custom fields',1),(3,'GO\\Billing\\Model\\Order',79,'Demo Custom fields',2),(4,'GO\\Billing\\Model\\Product',80,'Demo Custom fields',3),(5,'GO\\Calendar\\Model\\Event',81,'Demo Custom fields',4),(6,'GO\\Calendar\\Model\\Calendar',82,'Demo Custom fields',5),(7,'GO\\Files\\Model\\File',83,'Demo Custom fields',6),(8,'GO\\Files\\Model\\Folder',84,'Demo Custom fields',7),(9,'GO\\Notes\\Model\\Note',85,'Demo Custom fields',8),(10,'GO\\Projects2\\Model\\TimeEntry',86,'Demo Custom fields',9),(11,'GO\\Projects2\\Model\\Project',87,'Demo Custom fields',10),(12,'GO\\Tasks\\Model\\Task',88,'Demo Custom fields',11),(13,'GO\\Tickets\\Model\\Ticket',89,'Demo Custom fields',12),(14,'GO\\Base\\Model\\User',90,'Demo Custom fields',13);
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
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `model_type_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`block_id`,`model_id`,`model_type_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `model_name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`,`model_name`,`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) NOT NULL,
  `datatype` varchar(100) NOT NULL DEFAULT 'GO_Customfields_Customfieldtype_Text',
  `sort_index` int(11) NOT NULL DEFAULT 0,
  `function` varchar(255) DEFAULT NULL,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `required_condition` varchar(255) NOT NULL DEFAULT '',
  `validation_regex` varchar(255) NOT NULL DEFAULT '',
  `helptext` varchar(100) NOT NULL DEFAULT '',
  `multiselect` tinyint(1) NOT NULL DEFAULT 0,
  `max` int(11) NOT NULL DEFAULT 0,
  `nesting_level` tinyint(4) NOT NULL DEFAULT 0,
  `treemaster_field_id` int(11) NOT NULL DEFAULT 0,
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT 0,
  `height` int(11) NOT NULL DEFAULT 0,
  `number_decimals` tinyint(4) NOT NULL DEFAULT 2,
  `unique_values` tinyint(1) NOT NULL DEFAULT 0,
  `max_length` int(5) NOT NULL DEFAULT 50,
  `addressbook_ids` varchar(255) NOT NULL DEFAULT '',
  `extra_options` text DEFAULT NULL,
  `prefix` varchar(32) NOT NULL DEFAULT '',
  `suffix` varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `type` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fields`
--

LOCK TABLES `cf_fields` WRITE;
/*!40000 ALTER TABLE `cf_fields` DISABLE KEYS */;
INSERT INTO `cf_fields` VALUES (1,1,'Company','GO\\Addressbook\\Customfieldtype\\Company',0,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(2,1,'Contact','GO\\Addressbook\\Customfieldtype\\Contact',1,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(3,1,'Infotext','GO\\Customfields\\Customfieldtype\\Infotext',2,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(4,1,'Encrypted text','GO\\Customfields\\Customfieldtype\\EncryptedText',3,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(5,1,'User','GO\\Customfields\\Customfieldtype\\User',4,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(6,1,'Treeselect','GO\\Customfields\\Customfieldtype\\Treeselect',5,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(7,1,'Date time','GO\\Customfields\\Customfieldtype\\Datetime',6,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(8,1,'Number','GO\\Customfields\\Customfieldtype\\Number',7,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(9,1,'Text (Readonly)','GO\\Customfields\\Customfieldtype\\ReadonlyText',8,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(10,1,'Heading','GO\\Customfields\\Customfieldtype\\Heading',9,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(11,1,'Select','GO\\Customfields\\Customfieldtype\\Select',10,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(12,1,'Text','GO\\Customfields\\Customfieldtype\\Text',11,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(13,1,'Checkbox','GO\\Customfields\\Customfieldtype\\Checkbox',12,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(14,1,'Function','GO\\Customfields\\Customfieldtype\\FunctionField',13,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(15,1,'Textarea','GO\\Customfields\\Customfieldtype\\Textarea',14,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(16,1,'User group','GO\\Customfields\\Customfieldtype\\UserGroup',15,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(17,1,'HTML','GO\\Customfields\\Customfieldtype\\Html',16,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(18,1,'Date','GO\\Customfields\\Customfieldtype\\Date',17,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(19,1,'File','GO\\Files\\Customfieldtype\\File',18,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(20,2,'Company','GO\\Addressbook\\Customfieldtype\\Company',19,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(21,2,'Contact','GO\\Addressbook\\Customfieldtype\\Contact',20,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(22,2,'Infotext','GO\\Customfields\\Customfieldtype\\Infotext',21,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(23,2,'Encrypted text','GO\\Customfields\\Customfieldtype\\EncryptedText',22,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(24,2,'User','GO\\Customfields\\Customfieldtype\\User',23,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(25,2,'Treeselect','GO\\Customfields\\Customfieldtype\\Treeselect',24,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(26,2,'Date time','GO\\Customfields\\Customfieldtype\\Datetime',25,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(27,2,'Number','GO\\Customfields\\Customfieldtype\\Number',26,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(28,2,'Text (Readonly)','GO\\Customfields\\Customfieldtype\\ReadonlyText',27,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(29,2,'Heading','GO\\Customfields\\Customfieldtype\\Heading',28,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(30,2,'Select','GO\\Customfields\\Customfieldtype\\Select',29,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(31,2,'Text','GO\\Customfields\\Customfieldtype\\Text',30,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(32,2,'Checkbox','GO\\Customfields\\Customfieldtype\\Checkbox',31,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(33,2,'Function','GO\\Customfields\\Customfieldtype\\FunctionField',32,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(34,2,'Textarea','GO\\Customfields\\Customfieldtype\\Textarea',33,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(35,2,'User group','GO\\Customfields\\Customfieldtype\\UserGroup',34,NULL,0,'','','',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(36,2,'HTML','GO\\Customfields\\Customfieldtype\\Html',35,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(37,2,'Date','GO\\Customfields\\Customfieldtype\\Date',36,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(38,2,'File','GO\\Files\\Customfieldtype\\File',37,NULL,0,'','','',0,0,0,0,0,0,2,0,255,'',NULL,'',''),(39,3,'Custom','GO\\Customfields\\Customfieldtype\\Text',38,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(40,4,'Custom','GO\\Customfields\\Customfieldtype\\Text',39,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(41,5,'Custom','GO\\Customfields\\Customfieldtype\\Text',40,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(42,6,'Custom','GO\\Customfields\\Customfieldtype\\Text',41,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(43,7,'Custom','GO\\Customfields\\Customfieldtype\\Text',42,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(44,8,'Custom','GO\\Customfields\\Customfieldtype\\Text',43,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(45,9,'Custom','GO\\Customfields\\Customfieldtype\\Text',44,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(46,10,'Custom','GO\\Customfields\\Customfieldtype\\Text',45,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(47,11,'Custom','GO\\Customfields\\Customfieldtype\\Text',46,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(48,12,'Custom','GO\\Customfields\\Customfieldtype\\Text',47,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(49,13,'Custom','GO\\Customfields\\Customfieldtype\\Text',48,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'',''),(50,14,'Custom','GO\\Customfields\\Customfieldtype\\Text',49,NULL,0,'','','Some help text for this field',0,0,0,0,0,0,2,0,50,'',NULL,'','');
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
  `col_43` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fs_files`
--

LOCK TABLES `cf_fs_files` WRITE;
/*!40000 ALTER TABLE `cf_fs_files` DISABLE KEYS */;
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
  `col_44` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_fs_folders`
--

LOCK TABLES `cf_fs_folders` WRITE;
/*!40000 ALTER TABLE `cf_fs_folders` DISABLE KEYS */;
INSERT INTO `cf_fs_folders` VALUES (9,''),(11,''),(12,''),(13,''),(17,''),(21,''),(26,''),(31,''),(36,'');
/*!40000 ALTER TABLE `cf_fs_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_go_users`
--

DROP TABLE IF EXISTS `cf_go_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_go_users` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `col_50` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_go_users`
--

LOCK TABLES `cf_go_users` WRITE;
/*!40000 ALTER TABLE `cf_go_users` DISABLE KEYS */;
INSERT INTO `cf_go_users` VALUES (1,''),(2,''),(3,'');
/*!40000 ALTER TABLE `cf_go_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_no_notes`
--

DROP TABLE IF EXISTS `cf_no_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_no_notes` (
  `model_id` int(11) NOT NULL DEFAULT 0,
  `col_45` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_no_notes`
--

LOCK TABLES `cf_no_notes` WRITE;
/*!40000 ALTER TABLE `cf_no_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `cf_no_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cf_pr2_hours`
--

DROP TABLE IF EXISTS `cf_pr2_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cf_pr2_hours` (
  `model_id` int(11) NOT NULL,
  `col_46` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_pr2_hours`
--

LOCK TABLES `cf_pr2_hours` WRITE;
/*!40000 ALTER TABLE `cf_pr2_hours` DISABLE KEYS */;
INSERT INTO `cf_pr2_hours` VALUES (1,''),(2,'');
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
  `col_47` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_pr2_projects`
--

LOCK TABLES `cf_pr2_projects` WRITE;
/*!40000 ALTER TABLE `cf_pr2_projects` DISABLE KEYS */;
INSERT INTO `cf_pr2_projects` VALUES (2,'');
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
  `text` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `field_id` (`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_select_options`
--

LOCK TABLES `cf_select_options` WRITE;
/*!40000 ALTER TABLE `cf_select_options` DISABLE KEYS */;
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
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `col_48` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ta_tasks`
--

LOCK TABLES `cf_ta_tasks` WRITE;
/*!40000 ALTER TABLE `cf_ta_tasks` DISABLE KEYS */;
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
  `col_49` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_ti_tickets`
--

LOCK TABLES `cf_ti_tickets` WRITE;
/*!40000 ALTER TABLE `cf_ti_tickets` DISABLE KEYS */;
INSERT INTO `cf_ti_tickets` VALUES (1,''),(2,''),(3,''),(6,'');
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
  `name` varchar(100) NOT NULL,
  `sort` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`,`field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cf_tree_select_options`
--

LOCK TABLES `cf_tree_select_options` WRITE;
/*!40000 ALTER TABLE `cf_tree_select_options` DISABLE KEYS */;
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
  `name` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `co_categories`
--

LOCK TABLES `co_categories` WRITE;
/*!40000 ALTER TABLE `co_categories` DISABLE KEYS */;
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
  `comments` mediumtext DEFAULT NULL,
  `category_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `link_id` (`model_id`,`model_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `co_comments`
--

LOCK TABLES `co_comments` WRITE;
/*!40000 ALTER TABLE `co_comments` DISABLE KEYS */;
INSERT INTO `co_comments` VALUES (1,2,4,1,1579529586,1579529586,'The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate which produces every product type imaginable, no matter how elaborate or extravagant - none of which ever work as desired or expected. In the Road Runner cartoon Beep, Beep, it was referred to as \"Acme Rocket-Powered Products, Inc.\" based in Fairfield, New Jersey. Many of its products appear to be produced specifically for Wile E. Coyote; for example, the Acme Giant Rubber Band, subtitled \"(For Tripping Road Runners)\".',0),(2,2,4,1,1579529586,1579529586,'Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat, which had been used on Elmer Fudd and Sam Sheepdog (which doesn\'t maul its intended victim); and Acme Bumblebees in one-fifth bottles (which sting Wile E. Coyote). The Wild Cat was used in the shorts Don\'t Give Up the Sheep and A Mutt in a Rut, while the bees were used in the short Zoom and Bored.',0),(3,3,2,1,1579529586,1579529586,'Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.',0),(4,3,2,1,1579529586,1579529586,'In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.',0),(5,1,9,1,1579529588,1579529588,'Scheduled call at 23-01-2020 15:13',0),(6,2,9,1,1579529588,1579529588,'Scheduled call at 23-01-2020 15:13',0);
/*!40000 ALTER TABLE `co_comments` ENABLE KEYS */;
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
  `type` varchar(4) DEFAULT NULL,
  `host` varchar(100) DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT 0,
  `deprecated_use_ssl` tinyint(1) NOT NULL DEFAULT 0,
  `novalidate_cert` tinyint(1) NOT NULL DEFAULT 0,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
  `imap_encryption` char(3) NOT NULL,
  `imap_allow_self_signed` tinyint(1) NOT NULL DEFAULT 1,
  `mbroot` varchar(30) NOT NULL DEFAULT '',
  `sent` varchar(100) DEFAULT 'Sent',
  `drafts` varchar(100) DEFAULT 'Drafts',
  `trash` varchar(100) NOT NULL DEFAULT 'Trash',
  `spam` varchar(100) NOT NULL DEFAULT 'Spam',
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_encryption` char(3) NOT NULL,
  `smtp_allow_self_signed` tinyint(1) NOT NULL DEFAULT 0,
  `smtp_username` varchar(50) DEFAULT NULL,
  `smtp_password` varchar(512) NOT NULL DEFAULT '',
  `password_encrypted` tinyint(4) NOT NULL DEFAULT 0,
  `ignore_sent_folder` tinyint(1) NOT NULL DEFAULT 0,
  `sieve_port` int(11) NOT NULL,
  `sieve_usetls` tinyint(1) NOT NULL DEFAULT 1,
  `check_mailboxes` text DEFAULT NULL,
  `do_not_mark_as_read` tinyint(1) NOT NULL DEFAULT 0,
  `signature_below_reply` tinyint(1) NOT NULL DEFAULT 0,
  `full_reply_headers` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_accounts`
--

LOCK TABLES `em_accounts` WRITE;
/*!40000 ALTER TABLE `em_accounts` DISABLE KEYS */;
INSERT INTO `em_accounts` VALUES (1,1,120,NULL,'mailserver',143,0,0,'admin@intermesh.localhost','{GOCRYPT2}def502005a175456aa4ddc9a90990ffb17d49c869f9bb186404c230465fff70a55ac897b0da41281b4dc6d521dd35500a142b90ea8aabe1e4f1bab0e0a69b56c82103cfea9589c63843d5371e23d100c7759d5a4fac20a76e56f423a035e','',0,'','Sent','Drafts','Trash','Spam','mailserver',25,'',0,'','',2,0,4190,1,'INBOX',0,0,0),(2,3,126,NULL,'mailserver',143,0,0,'test@intermesh.localhost','{GOCRYPT2}def5020057b7e97e9a476f5cf631cd8d90d6d5c3eeb43d8e186c52258a0dbc101d67c9df63639c195bcacb0155d3e548649c69f6a0b42c9a9bc76677e54dffdbad6d923ac49f727f917135c66db82f6055f81f0ea47f39a2db710be9','',0,'','Sent','Drafts','Trash','Spam','mailserver',25,'',0,'','',2,0,4190,1,'INBOX',0,0,0),(3,2,128,NULL,'mailserver',143,0,0,'elmer@intermesh.localhost','{GOCRYPT2}def502007c0ea6f63d8d859eb603bf192039ef1c6085cc89a2e8195108f830f7a134608ad4aa6024af162a64c653dd2f400c41ba1b2bc5e57d3ad87b185389d22736606eea6c3023cf5381c4b46a37f5b22526c26e344ddbf8044507','',0,'','Sent','Drafts','Trash','Spam','mailserver',25,'',0,'','',2,0,4190,1,'INBOX',0,0,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `signature` text DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_aliases`
--

LOCK TABLES `em_aliases` WRITE;
/*!40000 ALTER TABLE `em_aliases` DISABLE KEYS */;
INSERT INTO `em_aliases` VALUES (1,1,'Administrator, System','admin@intermesh.localhost','',1),(2,2,'User, Demo','test@intermesh.localhost','',1),(3,3,'Fudd, Elmer','elmer@intermesh.localhost','',1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_contacts_last_mail_times`
--

LOCK TABLES `em_contacts_last_mail_times` WRITE;
/*!40000 ALTER TABLE `em_contacts_last_mail_times` DISABLE KEYS */;
INSERT INTO `em_contacts_last_mail_times` VALUES (5,2,1601299750);
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
  `field` varchar(20) DEFAULT NULL,
  `keyword` varchar(100) DEFAULT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `mark_as_read` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) DEFAULT NULL,
  `subscribed` enum('0','1') NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `delimiter` char(1) NOT NULL DEFAULT '',
  `sort_order` tinyint(4) NOT NULL DEFAULT 0,
  `msgcount` int(11) NOT NULL DEFAULT 0,
  `unseen` int(11) NOT NULL DEFAULT 0,
  `auto_check` enum('0','1') NOT NULL DEFAULT '0',
  `can_have_children` tinyint(1) NOT NULL,
  `no_select` tinyint(1) DEFAULT NULL,
  `sort` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  `color` varchar(6) NOT NULL,
  `account_id` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `em_labels`
--

LOCK TABLES `em_labels` WRITE;
/*!40000 ALTER TABLE `em_labels` DISABLE KEYS */;
INSERT INTO `em_labels` VALUES (1,'Label 1','$label1','7A7AFF',1,1),(2,'Label 2','$label2','59BD59',1,1),(3,'Label 3','$label3','FFBD59',1,1),(4,'Label 4','$label4','FF5959',1,1),(5,'Label 5','$label5','BD7ABD',1,1),(6,'Label 1','$label1','7A7AFF',2,1),(7,'Label 2','$label2','59BD59',2,1),(8,'Label 3','$label3','FFBD59',2,1),(9,'Label 4','$label4','FF5959',2,1),(10,'Label 5','$label5','BD7ABD',2,1),(11,'Label 1','$label1','7A7AFF',3,1),(12,'Label 2','$label2','59BD59',3,1),(13,'Label 3','$label3','FFBD59',3,1),(14,'Label 4','$label4','FF5959',3,1),(15,'Label 5','$label5','BD7ABD',3,1);
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
INSERT INTO `em_links` VALUES (1,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_5e25b575ca4b9.eml/demo.eml',1579529589,1579529589,1,7,'<1368777188.5195e1e479413@localhost>'),(2,1,'\"User, Demo\" <demo@group-office.com>','\"Elmer\" <elmer@group-office.com>','Rocket 2000 development plan',1368777188,'email/fromfile/demo_5e25b575d1d62.eml/demo.eml',1579529589,1579529589,1,7,'<1368777188.5195e1e479413@localhost>'),(3,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_5e25b575d4f25.eml/demo2.eml',1579529589,1579529589,1,7,'<1368777986.5195e5020b17e@localhost>'),(4,1,'\"User, Demo\" <demo@group-office.com>','\"User, Demo\" <demo@group-office.com>','Just a demo message',1368777986,'email/fromfile/demo2_5e25b575d7f8c.eml/demo2.eml',1579529589,1579529589,1,7,'<1368777986.5195e5020b17e@localhost>');
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
  `new` enum('0','1') NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `from` varchar(100) DEFAULT NULL,
  `size` int(11) NOT NULL,
  `udate` int(11) NOT NULL,
  `attachments` enum('0','1') NOT NULL,
  `flagged` enum('0','1') NOT NULL,
  `answered` enum('0','1') NOT NULL,
  `forwarded` tinyint(1) NOT NULL,
  `priority` tinyint(4) NOT NULL,
  `to` varchar(255) DEFAULT NULL,
  `serialized_message_object` mediumtext NOT NULL,
  PRIMARY KEY (`folder_id`,`uid`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `folder_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`folder_name`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_folders`
--

LOCK TABLES `emp_folders` WRITE;
/*!40000 ALTER TABLE `emp_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `emp_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fb_acl`
--

DROP TABLE IF EXISTS `fb_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fb_acl` (
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`acl_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fb_acl`
--

LOCK TABLES `fb_acl` WRITE;
/*!40000 ALTER TABLE `fb_acl` DISABLE KEYS */;
INSERT INTO `fb_acl` VALUES (2,131),(3,130);
/*!40000 ALTER TABLE `fb_acl` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `extension` varchar(20) NOT NULL,
  `cls` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`extension`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_filehandlers`
--

LOCK TABLES `fs_filehandlers` WRITE;
/*!40000 ALTER TABLE `fs_filehandlers` DISABLE KEYS */;
INSERT INTO `fs_filehandlers` VALUES (1,'docx','GO\\Assistant\\Filehandler\\Assistant');
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
  `name` varchar(190) NOT NULL,
  `locked_user_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `size` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `extension` varchar(20) NOT NULL,
  `expire_time` int(11) NOT NULL DEFAULT 0,
  `random_code` char(11) DEFAULT NULL,
  `delete_when_expired` tinyint(1) NOT NULL DEFAULT 0,
  `content_expire_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `folder_id` (`folder_id`),
  KEY `name` (`name`),
  KEY `extension` (`extension`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_files`
--

LOCK TABLES `fs_files` WRITE;
/*!40000 ALTER TABLE `fs_files` DISABLE KEYS */;
INSERT INTO `fs_files` VALUES (1,25,'Demo letter.docx',0,0,1579529586,1579529586,1,4312,1,NULL,'docx',0,NULL,0,NULL),(2,31,'empty.docx',0,0,1579529589,1579529589,1,3726,1,NULL,'docx',0,NULL,0,NULL),(3,31,'noperson.jpg',0,0,1579529589,1579529589,1,3015,1,NULL,'jpg',0,NULL,0,NULL),(4,31,'empty.odt',0,0,1579529589,1579529589,1,6971,1,NULL,'odt',0,NULL,0,NULL),(5,31,'wecoyote.png',0,0,1579529589,1579529589,1,39495,1,NULL,'png',0,NULL,0,NULL),(6,31,'Demo letter.docx',0,0,1579529589,1579529589,1,4312,1,NULL,'docx',0,NULL,0,NULL),(7,21,'folder.png',0,0,1579529589,1579529570,1,611,1,NULL,'png',0,NULL,0,NULL),(8,21,'project.png',0,0,1579529589,1579529570,1,3231,1,NULL,'png',0,NULL,0,NULL),(9,17,'Functionele eisen software en hardware poortinstructie.docx',0,0,1598348781,1598348781,1,15053,1,NULL,'docx',0,NULL,0,NULL),(10,31,'sdfdsf.zip',0,0,1598349334,1598349334,3,47812,3,NULL,'zip',0,NULL,0,NULL),(11,17,'Rutger.zip',0,0,1600154435,1600154435,1,14391,1,NULL,'zip',0,NULL,0,NULL),(12,31,'test.zip',0,0,1600154990,1600154990,1,48524,1,NULL,'zip',0,NULL,0,NULL),(13,51,'documents-6.4-license.txt',0,0,1600691986,1600691986,1,522,1,NULL,'txt',0,NULL,0,NULL),(14,12,'dsfsd.zip',0,0,1600691993,1600691993,1,579,1,NULL,'zip',0,NULL,0,NULL),(15,12,'Test.docx',0,0,1602146196,1602146196,1,3726,1,NULL,'docx',0,NULL,0,NULL);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(190) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT 1,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `quota_user_id` int(11) NOT NULL DEFAULT 0,
  `readonly` tinyint(1) NOT NULL DEFAULT 0,
  `cm_state` text DEFAULT NULL,
  `apply_state` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `parent_id` (`parent_id`),
  KEY `parent_id_2` (`parent_id`,`name`),
  KEY `visible` (`visible`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_folders`
--

LOCK TABLES `fs_folders` WRITE;
/*!40000 ALTER TABLE `fs_folders` DISABLE KEYS */;
INSERT INTO `fs_folders` VALUES (1,1,0,'notes',0,45,NULL,1,1579529569,1579529587,1,1,1,NULL,0),(1,2,1,'General',0,45,NULL,1,1579529569,1579529569,1,1,1,NULL,0),(1,3,0,'project_templates',0,53,NULL,1,1579529570,1579529570,1,1,1,NULL,0),(1,4,3,'Projects folder',0,53,NULL,1,1579529570,1579529570,1,1,1,NULL,0),(1,5,3,'Standard project',0,54,NULL,1,1579529570,1579529570,1,1,1,NULL,0),(1,6,0,'tickets',0,62,NULL,1,1579529571,1579529571,1,1,1,NULL,0),(1,7,6,'0-IT',0,62,NULL,1,1579529571,1579529571,1,1,1,NULL,0),(1,8,6,'0-Sales',0,63,NULL,1,1579529571,1579529571,1,1,1,NULL,0),(1,9,0,'addressbook',0,69,NULL,1,1579529571,1579529587,1,1,1,NULL,0),(1,10,9,'Users',0,69,NULL,1,1579529571,1579529571,1,1,1,NULL,0),(1,11,0,'users',0,35,NULL,1,1579529571,1579529587,1,1,1,NULL,0),(1,12,11,'admin',1,70,NULL,1,1579529571,1602146196,1,1,1,NULL,0),(1,13,0,'calendar',0,71,NULL,1,1579529572,1579529588,1,1,1,NULL,0),(1,14,13,'System Administrator',0,71,NULL,1,1579529572,1579529572,1,1,1,NULL,0),(1,15,0,'tasks',0,72,NULL,1,1579529572,1579529587,1,1,1,NULL,0),(1,16,15,'System Administrator',0,72,NULL,1,1579529572,1579529572,1,1,1,NULL,0),(1,17,12,'Public',1,73,'',1,1579529572,1600154435,1,1,0,NULL,0),(1,18,0,'billing',0,35,NULL,1,1579529581,1579529588,1,1,1,NULL,0),(1,19,18,'stationery-papers',0,0,NULL,1,1579529581,1579529581,1,1,0,NULL,0),(1,20,0,'projects2',0,35,NULL,1,1579529582,1579529582,1,1,1,NULL,0),(1,21,20,'template-icons',0,47,NULL,1,1579529582,1579529570,1,1,0,NULL,0),(1,22,9,'Customers',0,7,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,23,22,'contacts',0,7,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,24,23,'C',0,7,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,25,24,'Coyote, Wile E (3)',0,7,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(2,26,11,'elmer',1,92,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,27,9,'Elmer Fudd',0,93,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,28,13,'Elmer Fudd',0,94,NULL,1,1579529586,1579529586,1,1,1,NULL,0),(1,29,1,'Elmer Fudd',0,95,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,30,15,'Elmer Fudd',0,96,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(3,31,11,'demo',1,98,NULL,1,1579529587,1600154990,1,1,1,NULL,0),(1,32,9,'Demo User',0,99,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,33,13,'Demo User',0,100,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,34,1,'Demo User',0,101,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,35,15,'Demo User',0,102,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(4,36,11,'linda',1,104,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,37,9,'Linda Smith',0,105,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,38,13,'Linda Smith',0,106,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,39,1,'Linda Smith',0,107,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,40,15,'Linda Smith',0,108,NULL,1,1579529587,1579529587,1,1,1,NULL,0),(1,41,13,'Road Runner Room',0,111,NULL,1,1579529588,1579529588,1,1,1,NULL,0),(1,42,13,'Don Coyote Room',0,112,NULL,1,1579529588,1579529588,1,1,1,NULL,0),(1,43,18,'Quotes',0,11,NULL,1,1579529588,1579529588,1,1,1,NULL,0),(1,44,18,'Orders',0,16,NULL,1,1579529588,1579529588,1,1,1,NULL,0),(1,45,18,'Invoices',0,21,NULL,1,1579529588,1579529588,1,1,1,NULL,0),(1,46,0,'public',0,115,NULL,1,1579529589,1579529589,1,1,1,NULL,0),(1,47,46,'site',0,115,NULL,1,1579529589,1579529589,1,1,1,NULL,0),(1,48,47,'1',0,115,NULL,1,1579529589,1579529589,1,1,1,NULL,0),(1,49,48,'files',0,115,NULL,1,1579529589,1579529589,1,1,1,NULL,0),(1,50,0,'log',0,35,NULL,1,1579531597,1579531597,1,1,1,NULL,0),(1,51,12,'New folder',0,0,NULL,1,1600691980,1600691986,1,1,0,NULL,0);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `arg1` varchar(255) NOT NULL,
  `arg2` varchar(255) NOT NULL,
  `mtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) NOT NULL,
  `path` text NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
INSERT INTO `fs_shared_root_folders` VALUES (1,26),(1,31),(1,36),(3,17);
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
  `comments` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  `acl_id` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  `extension` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_templates`
--

LOCK TABLES `fs_templates` WRITE;
/*!40000 ALTER TABLE `fs_templates` DISABLE KEYS */;
INSERT INTO `fs_templates` VALUES (1,1,'Microsoft Word document',36,'PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.rels��MKA���C��l+����\"Bo\"�������3i���A\n�P��Ǽy���m���N���AêiAq0Ѻ0jx�=/`�/�W>��J�\\*�ބ�aI���L�41q��!fOR�<b\"���qݶ��2��1��j�[���H�76z�$�&f^�\\��8.Nyd�`�y�q�j4�x]h�{�8��S4G�A�y�Y8X���(�[Fw�i4o|˼�l�^�͢����PK��#�\0\0\0=\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.rels��M\n�0���\"�ަU��nDp+�\01���6	�(z{�Z(����}�1/__��]�m��,I��Q�Ҧp(��%��I��NR\\	�v���Dn�yP-�2$֡����^R,}ÝT\'� ����O&�U��ʀ�7���m]k���=\Z\Z���n�H��A��>.?��|m\r������������?@��������I��wPK�/0��\0\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/settings.xmlE�K�0D��\"����S����Bk�RbG����	+��73z��+E��\"#��f�� �<�t�p>�0��������l7����%�>�����jn����)Ȃ3ReW.)h��f\'.C.ܣH��h�έl\n#AW/?��Lm��#i�i��\ZQO�rTε����m��]�/PKe��\"�\0\0\0�\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xml���N�0��<E�;K�Mպ		q�\0���]#%q���=Y۝(�@�%��������Y�c`C���B\n��j�O��8��o���K+9 ���n�ʆ|d��=���m�]����:���Pp�7�5���L�ӡ�j]�*����ܚ��얮qK�.�F����ρ�r7����r�q���x#�@�Ϛl%�B�q��å\ZF���L���C0p�xn�	��>�#�E��֬�,YF-���0�u�-77���-�������7PK��Z]\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/styles.xml�TQo�0~߯��Nh�5T��	��н�A�9��s\Z诟�j��x������;���6�\Z�JF�s�f�X%\\n\"��|l�Y@dBI���=���;��xI�\"b��z����(��ݭ�����ل�2�6*F\"�>a���pɆu Z�I�ʍ+�؝v��``c@�����{����\\C.l��,,�qk��*Qi׿�_\n��b��5J�nkTES��qu�r�&;����˚�t\n�r�jyt�P�yĖ<s�̱�T��)b3�rL�L�7!���΃H��Hґ,ey����������u�֖1��	�g[�ĥOyk:?��5m�KS������@�J�Ӓu�5�l��l_�\n���`���������g�WC�Kx��h\\Gw�Z�kD��k�uA9��[�a|���p���}/Z��h�3��5�~�9������F��t����	�\'{\Zl\r�#���О��p�d�&\0Ơ����?��l3.q�g+7��������wL�g���5�v*�^��]f�l�zG�PKՔq�\0\0�\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/document.xml�R�n�0��+\"���T�F.��VH�0�&�d{#{!Я�M�K�ś�̾&��\\�N��B���4e	X���Uξ�%K<	[�rv�6�U�(O,%������l�e\rF��Qҡǒ&M�e�$��.g5Q�q�\'M�����x���{��4]pZP��ת�C����F����-��q(��`��]_#����\'�uƌ�΅�C˿�l;����G,�16�g�naOW\rI���Ι� e|�⣢{�7��\"R$uJ�6c%ډ\nb� ��?��ù,��pm4u6{O��S�$�H�&R�*	�_O#�Nt5�b\ZJ�\'9U����A���dݨ�	��2Bwl4v理=J�}����ʅu�!\r�v�c��`����_�PK���ly\0\06\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/app.xml��=k�0ཿ��TcL�BC�h�t3�tN��sp�}�����xx��v�Sq���wy�)�)��;v�)פH(���wБ+$��-�\0\r�\".u�6�&u+S�c���G+1��H�8\Z�^�Қ���4�2���W�\\���_��!{�����qz�S�Q��bo^4�T���z�7n^��u;�Mq�0�gPH�,[�f3�������#�PK(��\0\0\0h\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0docProps/core.xmlm�[O�0���Kﷶ D�m\\h���D���������2���&\Z���}��𖋽�8/��-�@�H�V�u���P��\r\n��E}S\n˄q��$�,��g�Vh�e{��}	��qt-�\\|����9Vx��I��шN�F�J��A�(��cZP��*��8�t\0��UxHFr��H�}_�Ӂ����}��2<5�:}�\0T�\'5x�&�v��9y�><����:��,��5%lv�ncdS���<���W���ҹ.V��1K�8��TiMJ|9����PK��i\0\0\0\0PK\0\0H�B\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0[Content_Types].xml��1O�0�����+JBI: 1B�0#c_�Ķ|����sh#���X���}�瓋�f�5x�֔�<�YFZ�M[���6�b�jQ�[���`ɺ�5�(;fց����A���;!�E�\"�/��&�	i��*�	終d%|���?z�gqe��{Ad�L8�k)��k�>��)V�\Z��30�=��z��)+_e$�74B�\\�П�l�h	S��漕�H~t���l����x���&��>�mх�w���O`:�6�r�p�CN��c��*���8�A��Ė��b�������\rPKc�a*\0\0^\0\0PK\0\0\0H�B��#�\0\0\0=\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0_rels/.relsPK\0\0\0H�B�/0��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/_rels/document.xml.relsPK\0\0\0H�Be��\"�\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0!\0\0word/settings.xmlPK\0\0\0H�B��Z]\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0word/fontTable.xmlPK\0\0\0H�BՔq�\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0F\0\0word/styles.xmlPK\0\0\0H�B���ly\0\06\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0word/document.xmlPK\0\0\0H�B(��\0\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0S\0\0docProps/app.xmlPK\0\0\0H�B��i\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0s	\0\0docProps/core.xmlPK\0\0\0H�Bc�a*\0\0^\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\n\0\0[Content_Types].xmlPK\0\0\0\0	\0	\0<\0\0<\0\0\0\0','docx'),(2,1,'Open-Office Text document',37,'PK\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0mimetypeapplication/vnd.oasis.opendocument.textPK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/statusbar/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0Configurations2/accelerator/current.xml\0PK\0\0\0\0\0\0\0\0\0\0\0PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/floater/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/progressbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/menubar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/toolbar/PK\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/images/Bitmaps/PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0content.xml�V�n!��+F,�c\']$S��JQ�JIMZuK��iyL���_�1N2	�7���s��s/ov�[�\rS���TbE�\\������\nܬ>,U�0L+�p\'��%VҺ�±���l\r:-+�3�D����J�T�*EWa�1vϳ���-��\\�Ǟp�S�����F}.�c��)�Q���e���E�=�bǙ�[���ma����r��\Z.���a��#��4(�!��/f�b��VP�r��ش$ى\'���A�p�l���]OH�7Hg�F\0��{I��$)W ����\n޻��qw�-r�����fm�6#:�+��R=!�P��|�	�q��߄��Y�8~�ǣ�J�&��-�C�t��tl|/�� \\�8=�\r�L����o�@G0{\\2i,�Ge\ZF��0�F^�]K5�6 �4�Q�q)�T���љ9��`5\\��B�@��A���bnV�x��pǾ�\Z<�2L���%��;?T0���G�*��.Aq����5�no�e}�wD��bw�H3y�vi_��R��^��̘s���Я�x@�L*�`2�0{c�x�&8e�!�:�T��!Ob��\"�q���2<o����>&z,B�0�r�/Ug�AK�N��k�0e��yg�v(��=+���󲸟g\'���qp`Z�6R��e$>�#O�&�wQ|x���˴�PK�\0=@�\0\0s	\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\n\0\0\0styles.xml�YK��6��W*�m˻����E�����׀�h�	E\n$e�����D˒W�H�=, �p��<���7��MvD**�]O�ф�D��o�?��n�7�?��\rM�*I����F�6s�rĻ��|%��j�qN�J\'+Q�7�B�U�V����-s�[���������-s�;��\Z����M��1v�^1�(y�5��3ʿ�E���j6��jZ]M�����r9��p���d�+Mf��L��i<�9�x,>�B�e�&r�i��\'^U����mL�dX��\r�|�ޫt�{��po�u6����; ��޶� �I���t��~!D�lp	j�.�������앤�Ȁ=9˞`�4y�р/�\";�Ѥ.!Aي�{_�6��\'�$a�����fy⾍�,5yO��?\"�<�@0y֜��]�3.������ɑhÏ��I�Ȳ��rT\';,��$��<�߀��\0��êUE�z��?�g�_9���\Z�H�3�\Z�4��4ra�Ǟ�\r.Y݋��\Z�V�\"�I�y�oTH�A�)8�T��p**��hͧW	��!:D\r�Au%H8�ڎ2!�W���a]ܞe��)+$�X�\'�=2k�08GEu�\\��`��((���B�}��#\\jat@hДǊY�a���XK��)\r.מbʁ����3���((O��R�Vƃ�����E�L��n�\r�Ӕ����U�&�OiYB}��Hѯ�4^ڮ1̷%��gv!%�������DC�C_������DP���V��M��ǋ�ԯ��\'Պ<��#�t>F�pGhC��Ь�֬Gy5&�\Z?Dg\nL���phԂ#��lf����6\'wE�]:���}����(�RHOn����M�f�q�`�6e�_������@��G�q}��Qj7�vV�K�rj��n�I�cʑ��� \\�0��:,�H��+\Z#a��ZH�&蠐C1\\(��U���:�a���_)�[�3s�7���P���O)�i4X(��V\n�A2��u*�/�� ���B�8E�P��ې�#,|Z�?�Ez��PI˱�z&+L׽^خۮ����V\r9^�$kcۍ��ƘU���-Aᨯ��rq����2�W�c3�i�@�3>���C��IH~�����>}ܷ�i�r�31ʌ��s���c|�;.L)|A�J��Xm�%���Ƀ�H���[�����|8�z�[s�*\nwqx[�5�њ��m��}���^��n�A�Q+��^��\n_+Y�ڽ!z��VwG�,���5�92�%�9�ɯzk��95��5P�$�o�$i�kq���.���⺹P\\�.�/���Bq-/W<����B�\\h����\rݖ�>�&\rխm#�6�}��y�Q��Ҡ��F�\n���Ci;/���g\nF�˛�GHx:����EZ}j������rَG��Si���F�4�ih251��Zi��<9A&M�\'������ݣ�M��QO�Ng)M��2��t��	��̼��Wg�X+j$$�����B��{q��v���Ne\rih���\0D9�7�1ϖv�_3(Rxq�\Z��<�m���Ck\'���\'��=<xc�N},8�\\*��b��KHV��1����8�Q8��s�?TF��؏Yx�`�TPy��Vr�\Z��z�H:;�1�!\Z|G������oPK�E�}\0\0�\0\0PK\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0meta.xml<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<office:document-meta xmlns:office=\"urn:oasis:names:tc:opendocument:xmlns:office:1.0\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\" xmlns:meta=\"urn:oasis:names:tc:opendocument:xmlns:meta:1.0\" xmlns:ooo=\"http://openoffice.org/2004/office\" office:version=\"1.1\"><office:meta><meta:generator>OpenOffice.org/2.4$Linux OpenOffice.org_project/680m17$Build-9310</meta:generator><meta:initial-creator>Merijn Schering</meta:initial-creator><meta:creation-date>2008-08-26T09:26:02</meta:creation-date><meta:editing-cycles>0</meta:editing-cycles><meta:editing-duration>PT0S</meta:editing-duration><meta:user-defined meta:name=\"Info 1\"/><meta:user-defined meta:name=\"Info 2\"/><meta:user-defined meta:name=\"Info 3\"/><meta:user-defined meta:name=\"Info 4\"/><meta:document-statistic meta:table-count=\"0\" meta:image-count=\"0\" meta:object-count=\"0\" meta:page-count=\"1\" meta:paragraph-count=\"0\" meta:word-count=\"0\" meta:character-count=\"0\"/></office:meta></office:document-meta>PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Thumbnails/thumbnail.png��s���b``���p	�[8؀������{�8�T�y{i#\'�ρ\r|?�?���t�C��Ûw��~�2��9K&xrrV��o�ʓ����Ԏ_y2cTpTp�����3\n�*L���~.��\0PK�׃�|\0\0\0�\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0settings.xml�YQs�8~�_��;Jo�ʴ��챥����7��ձ2�S�ߟ�N�K	~�Mlɒ�믫����T��~^��@Fb~�=N������?�q6�h��1]Q�5-Qg�]�f���K�h\"S�j\n�jꠉ	�Ͷ���M�,{��x��Z\'�ju�\\�/�(�����Uվ�,\rP̢�����oU!�\"�!;�UvQ�]V������o\\S�Z?l�o]�\n��J�!6�9����x������k^Ѿ�{~�z_�`�m��uBo\"��V�+�p�}��\"��z�^����(ԋ\"ᗍ�Ɵ�d��|Qx�z��H��.GR�Ag��Ԗ�)\"&���)��\'��\n�1�}�g����Wb�T\"�\n�]_G��C�!ׇy�nUiI��L0_����Q����\'S�Uє��\\��O��V�h_�4\Z��/�D�Qk���_�8ٿ�	Iڎ���}a�vY�Q��׎�Sc�h�����&��y�/ b9����d���3���I6a\n�N��C�=û��?�A�{��T���o<Ti<��1%���ryLB����%�-N�z��p�\\��n$�&3�G(tO�t񯤬��8��L�url���~�t��J���xW�A�p��Q�Y���N����P�[��f.|e�0�(���n(QlS<��z[S��	&�^��#[tp���]��D{��~�1QGa�A�2�N�C�A��\'o1�7�F勰͙xV�t�tƃ�[zt������p$����#`!\n��AS��?A��.n�t}[�u��d��z�O�~T o�f��5%�˄���\r)��Q����U����|�b\nt�Swt߾���\"Щ�4�y4�c��U����o�ã�W�e�/�m�$-�]�����TJ�&���S�`M����������J��l9���°���[T���EY����nw�?��.[�BL���V�I�d ȭ�.�R��1pV�����D��^�ȍ�L���M�[�I߶Ӿk*�N�W�6����ɂ��W0��ZKSTQ��E\'W�uAUB5�+���_���D��9.��D;L�p�{*��:f\",h�����H��fk�6�)ҋ{&R��س�P#0u�L0O8�f��M��&}rt6��oY��bkL������	\nyj�T�\\Nu�Q��,x�KL���ީ����2��F����t��нXQ��$R�����j;o��|���L��PKt����\0\0h\0\0PK\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0META-INF/manifest.xml��Kj�0@�=���V�U1q-��&���f��W��6��X;	��F#�h��[S�0���Oͣ��)�k7v�c�^����aa����Ӡ�����HѵHS��\"��Z��^%��ۯ��ɴ|�.�A���x�.2�5�|�	�h��;�7GWs�h�,.��dL����B�%�My�n�c�� �Y\'�@,���`��(U�q:b�bqW�`<0�R�O �G?F�r7=�^�ޛbpmaD���-*긓��_PrS�4I7�Z��O�HN�z����b��K|0H�c-2��x��d7�!ɧa�87|��\"s�ϩ]����PK5b�9>\0\0J\0\0PK\0\0\0\0\0\0K;\Z9^�2\'\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0mimetypePK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0M\0\0\0Configurations2/statusbar/PK\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\'\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/accelerator/current.xmlPK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0\0Configurations2/floater/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\Z\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0Configurations2/popupmenu/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0J\0\0Configurations2/progressbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/menubar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/toolbar/PK\0\0\0\0\0\0K;\Z9\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Configurations2/images/Bitmaps/PK\0\0\0\0K;\Z9�\0=@�\0\0s	\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0-\0\0content.xmlPK\0\0\0\0K;\Z9�E�}\0\0�\0\0\n\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0styles.xmlPK\0\0\0\0\0\0K;\Z9�g��\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0meta.xmlPK\0\0\0\0K;\Z9�׃�|\0\0\0�\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0Thumbnails/thumbnail.pngPK\0\0\0\0K;\Z9t����\0\0h\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0settings.xmlPK\0\0\0\0K;\Z95b�9>\0\0J\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0�\0\0META-INF/manifest.xmlPK\0\0\0\0\0\0�\0\07\0\0\0\0','odt');
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
  `path` varchar(255) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `size_bytes` bigint(20) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fs_versions`
--

LOCK TABLES `fs_versions` WRITE;
/*!40000 ALTER TABLE `fs_versions` DISABLE KEYS */;
/*!40000 ALTER TABLE `fs_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_acl`
--

DROP TABLE IF EXISTS `go_acl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_acl` (
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `level` tinyint(4) NOT NULL DEFAULT 10,
  PRIMARY KEY (`acl_id`,`user_id`,`group_id`),
  KEY `acl_id` (`acl_id`,`user_id`),
  KEY `acl_id_2` (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_acl`
--

LOCK TABLES `go_acl` WRITE;
/*!40000 ALTER TABLE `go_acl` DISABLE KEYS */;
INSERT INTO `go_acl` VALUES (1,0,1,50),(1,1,0,50),(2,0,1,50),(2,0,2,10),(2,1,0,50),(3,0,1,50),(3,0,3,10),(3,1,0,50),(4,0,1,50),(4,0,3,10),(4,1,0,50),(4,3,0,10),(5,0,1,50),(5,0,3,30),(5,1,0,50),(6,0,1,50),(6,0,3,30),(6,1,0,50),(7,0,1,50),(7,0,3,30),(7,1,0,50),(8,0,1,50),(8,0,3,10),(8,1,0,50),(9,0,1,50),(9,0,3,10),(9,1,0,50),(10,0,1,50),(10,0,3,10),(10,1,0,50),(10,3,0,10),(11,0,1,50),(11,1,0,50),(11,2,0,30),(11,3,0,30),(12,0,1,50),(12,0,2,50),(12,1,0,50),(13,0,1,50),(13,0,2,50),(13,1,0,50),(14,0,1,50),(14,0,2,50),(14,1,0,50),(15,0,1,50),(15,0,2,50),(15,1,0,50),(16,0,1,50),(16,1,0,50),(16,2,0,30),(16,3,0,30),(17,0,1,50),(17,0,2,50),(17,1,0,50),(18,0,1,50),(18,0,2,50),(18,1,0,50),(19,0,1,50),(19,0,2,50),(19,1,0,50),(20,0,1,50),(20,0,2,50),(20,1,0,50),(21,0,1,50),(21,1,0,50),(21,2,0,30),(21,3,0,30),(22,0,1,50),(22,0,2,50),(22,1,0,50),(23,0,1,50),(23,0,2,50),(23,1,0,50),(24,0,1,50),(24,0,2,50),(24,1,0,50),(25,0,1,50),(25,0,2,50),(25,1,0,50),(26,0,1,50),(26,0,3,10),(26,1,0,50),(26,3,0,10),(27,0,1,50),(27,0,3,10),(27,1,0,50),(28,0,1,50),(28,0,3,10),(28,1,0,50),(28,3,0,10),(29,0,1,50),(29,0,3,10),(29,1,0,50),(29,3,0,10),(30,0,1,50),(30,1,0,50),(31,0,1,50),(31,0,3,10),(31,1,0,50),(31,3,0,10),(33,0,1,50),(33,0,3,10),(33,1,0,50),(33,3,0,10),(34,0,1,50),(34,0,3,10),(34,1,0,50),(34,3,0,10),(35,0,1,50),(35,0,3,10),(35,1,0,50),(35,3,0,10),(36,0,1,50),(36,0,3,10),(36,1,0,50),(37,0,1,50),(37,0,3,10),(37,1,0,50),(38,0,1,50),(38,0,3,10),(38,1,0,50),(38,3,0,10),(39,0,1,50),(39,1,0,50),(39,3,0,10),(40,0,1,50),(40,0,3,10),(40,1,0,50),(40,3,0,10),(41,0,1,50),(41,0,3,10),(41,1,0,50),(41,3,0,10),(42,0,1,50),(42,0,3,10),(42,1,0,50),(42,3,0,10),(43,0,1,50),(43,1,0,50),(44,0,1,50),(44,0,3,10),(44,1,0,50),(44,3,0,10),(45,0,1,50),(45,0,2,10),(45,1,0,50),(46,0,1,50),(46,0,3,10),(46,1,0,50),(46,3,0,10),(47,0,1,50),(47,0,3,10),(47,1,0,50),(47,3,0,10),(48,0,1,50),(48,1,0,50),(49,0,1,50),(49,1,0,50),(50,0,1,50),(50,1,0,50),(51,0,1,50),(51,1,0,50),(52,0,1,50),(52,1,0,50),(53,0,1,50),(53,0,2,10),(53,1,0,50),(54,0,1,50),(54,0,2,10),(54,1,0,50),(55,0,1,50),(55,0,3,10),(55,1,0,50),(55,3,0,10),(56,0,1,50),(56,0,3,10),(56,1,0,50),(56,3,0,10),(57,0,1,50),(57,0,3,10),(57,1,0,50),(57,3,0,10),(58,0,1,50),(58,0,3,10),(58,1,0,50),(58,3,0,10),(59,0,1,50),(59,0,3,10),(59,1,0,50),(59,3,0,10),(60,0,1,50),(60,0,3,10),(60,1,0,50),(60,3,0,10),(61,0,1,50),(61,0,3,10),(61,1,0,50),(61,3,0,10),(62,0,1,50),(62,0,2,30),(62,1,0,50),(62,2,0,50),(62,3,0,50),(63,0,1,50),(63,0,2,30),(63,1,0,50),(64,0,1,50),(64,0,3,10),(64,1,0,50),(64,3,0,10),(65,0,1,50),(65,1,0,50),(66,0,1,50),(66,1,0,50),(67,0,1,50),(67,0,3,10),(67,1,0,50),(67,3,0,10),(68,0,1,50),(68,0,3,50),(68,1,0,50),(69,0,1,50),(69,0,3,10),(69,1,0,50),(70,0,1,50),(70,1,0,50),(71,0,1,50),(71,1,0,50),(72,0,1,50),(72,1,0,50),(73,0,1,50),(73,1,0,50),(74,0,1,50),(74,1,0,50),(75,0,1,50),(75,1,0,50),(75,2,0,50),(75,3,0,50),(76,0,1,50),(76,1,0,50),(77,0,1,50),(77,0,3,30),(77,1,0,50),(78,0,1,50),(78,0,3,30),(78,1,0,50),(79,0,1,50),(79,0,3,30),(79,1,0,50),(80,0,1,50),(80,0,3,30),(80,1,0,50),(81,0,1,50),(81,0,3,30),(81,1,0,50),(82,0,1,50),(82,0,3,30),(82,1,0,50),(83,0,1,50),(83,0,3,30),(83,1,0,50),(84,0,1,50),(84,0,3,30),(84,1,0,50),(85,0,1,50),(85,0,3,30),(85,1,0,50),(86,0,1,50),(86,0,3,30),(86,1,0,50),(87,0,1,50),(87,0,3,30),(87,1,0,50),(88,0,1,50),(88,0,3,30),(88,1,0,50),(89,0,1,50),(89,0,3,30),(89,1,0,50),(90,0,1,50),(90,0,3,30),(90,1,0,50),(91,0,1,50),(91,0,3,50),(91,1,0,50),(91,2,0,50),(92,0,1,50),(92,2,0,50),(93,0,1,50),(93,2,0,50),(94,0,1,50),(94,0,3,10),(94,2,0,50),(95,0,1,50),(95,2,0,50),(96,0,1,50),(96,2,0,50),(97,0,1,50),(97,0,3,50),(97,1,0,50),(97,3,0,50),(98,0,1,50),(98,3,0,50),(99,0,1,50),(99,3,0,50),(100,0,1,50),(100,0,3,10),(100,3,0,50),(101,0,1,50),(101,3,0,50),(102,0,1,50),(102,3,0,50),(103,0,1,50),(103,0,3,50),(103,1,0,50),(103,4,0,50),(104,0,1,50),(104,4,0,50),(105,0,1,50),(105,4,0,50),(106,0,1,50),(106,0,3,10),(106,3,0,50),(106,4,0,50),(107,0,1,50),(107,4,0,50),(108,0,1,50),(108,4,0,50),(109,0,1,50),(109,0,3,10),(109,1,0,50),(110,0,1,50),(110,0,3,10),(110,1,0,50),(111,0,1,50),(111,0,3,10),(111,1,0,40),(111,2,0,40),(112,0,1,50),(112,0,3,10),(112,1,0,40),(112,2,0,40),(113,0,1,50),(113,0,3,10),(113,1,0,50),(113,3,0,10),(114,0,1,50),(114,0,3,10),(114,1,0,50),(114,3,0,10),(115,0,1,50),(115,1,0,50),(116,0,1,50),(116,0,2,10),(116,1,0,50),(117,0,1,50),(117,0,2,10),(117,1,0,50),(118,0,1,50),(118,0,3,30),(118,1,0,50),(119,0,1,50),(119,1,0,50),(120,0,1,50),(120,1,0,50),(121,0,1,50),(121,0,3,10),(121,1,0,50),(121,3,0,10),(122,0,1,50),(122,0,3,10),(122,1,0,50),(122,3,0,10),(123,0,1,50),(123,1,0,50),(125,0,1,50),(125,1,0,50),(126,0,1,50),(126,1,0,50),(126,3,0,50),(127,0,1,50),(127,1,0,50),(128,0,1,50),(128,2,0,50),(129,0,1,50),(129,0,3,10),(129,1,0,50),(129,3,0,10),(130,0,1,50),(130,3,0,50),(131,0,1,50),(131,2,0,50),(132,0,1,50),(132,0,3,10),(132,1,0,50),(133,0,1,50),(133,0,3,50),(133,1,0,50);
/*!40000 ALTER TABLE `go_acl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_acl_items`
--

DROP TABLE IF EXISTS `go_acl_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_acl_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `description` varchar(50) DEFAULT NULL,
  `mtime` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_acl_items`
--

LOCK TABLES `go_acl_items` WRITE;
/*!40000 ALTER TABLE `go_acl_items` DISABLE KEYS */;
INSERT INTO `go_acl_items` VALUES (1,1,'go_groups.acl_id',1579529571),(2,1,'go_groups.acl_id',1579529587),(3,1,'go_groups.acl_id',1579529587),(4,1,'go_modules.acl_id',1602829979),(5,1,'ab_addressbooks.acl_id',1579529587),(6,1,'ab_addressbooks.acl_id',1579529587),(7,1,'ab_addressbooks.acl_id',1579529587),(8,1,'ab_email_templates.acl_id',1579529587),(9,1,'ab_email_templates.acl_id',1579529587),(10,1,'go_modules.acl_id',1602829979),(11,1,'bs_books.acl_id',1579529588),(12,1,'bs_order_statuses.acl_id',1579529587),(13,1,'bs_order_statuses.acl_id',1579529587),(14,1,'bs_order_statuses.acl_id',1579529587),(15,1,'bs_order_statuses.acl_id',1579529587),(16,1,'bs_books.acl_id',1579529588),(17,1,'bs_order_statuses.acl_id',1579529587),(18,1,'bs_order_statuses.acl_id',1579529587),(19,1,'bs_order_statuses.acl_id',1579529587),(20,1,'bs_order_statuses.acl_id',1579529587),(21,1,'bs_books.acl_id',1579529588),(22,1,'bs_order_statuses.acl_id',1579529587),(23,1,'bs_order_statuses.acl_id',1579529587),(24,1,'bs_order_statuses.acl_id',1579529587),(25,1,'bs_order_statuses.acl_id',1579529587),(26,1,'go_modules.acl_id',1602829979),(27,1,'bm_categories.acl_id',1579529587),(28,1,'go_modules.acl_id',1602829979),(29,1,'go_modules.acl_id',1602829979),(30,1,'go_modules.acl_id',1579529571),(31,1,'go_modules.acl_id',1602829979),(33,1,'go_modules.acl_id',1602829979),(34,1,'go_modules.acl_id',1602829979),(35,1,'go_modules.acl_id',1602829979),(36,1,'fs_templates.acl_id',1579529587),(37,1,'fs_templates.acl_id',1579529587),(38,1,'go_modules.acl_id',1602829979),(39,1,'go_modules.acl_id',1602829979),(40,1,'go_modules.acl_id',1602829979),(41,1,'go_modules.acl_id',1602829979),(42,1,'go_modules.acl_id',1602829979),(43,1,'go_modules.acl_id',1579529571),(44,1,'go_modules.acl_id',1602829979),(45,1,'no_categories.acl_id',1579529587),(46,1,'go_modules.acl_id',1602829979),(47,1,'go_modules.acl_id',1602829979),(48,1,'pr2_types.acl_id',1579529571),(49,1,'pr2_types.acl_book',1579529571),(50,1,'pr2_statuses.acl_id',1579529571),(51,1,'pr2_statuses.acl_id',1579529571),(52,1,'pr2_statuses.acl_id',1579529571),(53,1,'pr2_templates.acl_id',1579529587),(54,1,'pr2_templates.acl_id',1579529587),(55,1,'go_modules.acl_id',1602829979),(56,1,'go_modules.acl_id',1602829979),(57,1,'go_modules.acl_id',1602829979),(58,1,'go_modules.acl_id',1602829979),(59,1,'go_modules.acl_id',1602829979),(60,1,'go_modules.acl_id',1602829979),(61,1,'go_modules.acl_id',1602829979),(62,1,'ti_types.acl_id',1579529588),(63,1,'ti_types.acl_id',1579529587),(64,1,'go_modules.acl_id',1602829979),(65,1,'go_modules.acl_id',1579529571),(66,1,'go_modules.acl_id',1579529571),(67,1,'go_modules.acl_id',1602829979),(68,1,'go_users.acl_id',1579529587),(69,1,'ab_addressbooks.acl_id',1579529587),(70,1,'fs_folders.acl_id',1579529571),(71,1,'cal_calendars.acl_id',1579529572),(72,1,'ta_tasklists.acl_id',1579529572),(73,1,'fs_folders.acl_id',1598348984),(74,1,'addressbook_export',1579529581),(75,1,'ti_types.search_cache_acl_id',1579529590),(76,1,'ti_types.search_cache_acl_id',1579529590),(77,1,'cf_categories.acl_id',1579529587),(78,1,'cf_categories.acl_id',1579529587),(79,1,'cf_categories.acl_id',1579529587),(80,1,'cf_categories.acl_id',1579529587),(81,1,'cf_categories.acl_id',1579529587),(82,1,'cf_categories.acl_id',1579529587),(83,1,'cf_categories.acl_id',1579529587),(84,1,'cf_categories.acl_id',1579529587),(85,1,'cf_categories.acl_id',1579529587),(86,1,'cf_categories.acl_id',1579529587),(87,1,'cf_categories.acl_id',1579529587),(88,1,'cf_categories.acl_id',1579529587),(89,1,'cf_categories.acl_id',1579529587),(90,1,'cf_categories.acl_id',1579529587),(91,2,'go_users.acl_id',1579529587),(92,2,'fs_folders.acl_id',1579529586),(93,2,'ab_addressbooks.acl_id',1579529586),(94,2,'cal_calendars.acl_id',1579529587),(95,2,'no_categories.acl_id',1579529587),(96,2,'ta_tasklists.acl_id',1579529587),(97,3,'go_users.acl_id',1579529587),(98,3,'fs_folders.acl_id',1579529587),(99,3,'ab_addressbooks.acl_id',1579529587),(100,3,'cal_calendars.acl_id',1579529587),(101,3,'no_categories.acl_id',1579529587),(102,3,'ta_tasklists.acl_id',1579529587),(103,4,'go_users.acl_id',1579529587),(104,4,'fs_folders.acl_id',1579529587),(105,4,'ab_addressbooks.acl_id',1579529587),(106,3,'cal_calendars.acl_id',1598880371),(107,4,'no_categories.acl_id',1579529587),(108,4,'ta_tasklists.acl_id',1579529587),(109,1,'cal_views.acl_id',1579529588),(110,1,'cal_views.acl_id',1579529588),(111,1,'cal_calendars.acl_id',1598355762),(112,1,'cal_calendars.acl_id',1598355762),(113,1,'go_modules.acl_id',1602829979),(114,1,'go_modules.acl_id',1602829979),(115,1,'site_sites.acl_id',1579529589),(116,1,'su_announcements.acl_id',1579529589),(117,1,'su_announcements.acl_id',1579529589),(118,1,'pr2_types.acl_id',1579529589),(119,1,'pr2_types.acl_book',1579529589),(120,1,'em_accounts.acl_id',1579531570),(121,1,'go_modules.acl_id',1602829979),(122,1,'go_modules.acl_id',1602829979),(123,1,'ab_addresslists.acl_id',1591260411),(125,1,'go_modules.acl_id',1598880341),(126,3,'em_accounts.acl_id',1601299009),(127,1,'go_modules.acl_id',1600072554),(128,2,'em_accounts.acl_id',1601299724),(129,1,'go_modules.acl_id',1602829979),(130,3,'fb_acl',1601299898),(131,2,'fb_acl',1601299907),(132,1,'go_modules.acl_id',1602146175),(133,1,'go_groups.acl_id',1602829965);
/*!40000 ALTER TABLE `go_acl_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_address_format`
--

DROP TABLE IF EXISTS `go_address_format`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_address_format` (
  `id` int(11) NOT NULL,
  `format` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `data` text DEFAULT NULL,
  `model_name` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `key` varchar(190) NOT NULL DEFAULT '',
  `content` longtext DEFAULT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`key`),
  KEY `mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `footprint` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `in_use` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_footprint` (`footprint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(64) DEFAULT NULL,
  `iso_code_2` char(2) NOT NULL DEFAULT '',
  `iso_code_3` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `minutes` varchar(100) NOT NULL DEFAULT '1',
  `hours` varchar(100) NOT NULL DEFAULT '1',
  `monthdays` varchar(100) NOT NULL DEFAULT '*',
  `months` varchar(100) NOT NULL DEFAULT '*',
  `weekdays` varchar(100) NOT NULL DEFAULT '*',
  `years` varchar(100) NOT NULL DEFAULT '*',
  `job` varchar(255) NOT NULL,
  `runonce` tinyint(1) NOT NULL DEFAULT 0,
  `nextrun` int(11) NOT NULL DEFAULT 0,
  `lastrun` int(11) NOT NULL DEFAULT 0,
  `completedat` int(11) NOT NULL DEFAULT 0,
  `error` text DEFAULT NULL,
  `autodestroy` tinyint(1) NOT NULL DEFAULT 0,
  `params` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron`
--

LOCK TABLES `go_cron` WRITE;
/*!40000 ALTER TABLE `go_cron` DISABLE KEYS */;
INSERT INTO `go_cron` VALUES (1,'Calendar publisher',1,'0','*','*','*','*','*','GO\\Calendar\\Cron\\CalendarPublisher',0,1579532400,0,0,NULL,0,'[]'),(2,'Contract Expiry Notification Cron',1,'2','7','*','*','*','*','GO\\Projects2\\Cron\\IncomeNotification',0,1579586520,0,0,NULL,0,'[]'),(3,'Close inactive tickets',1,'0','2','*','*','*','*','GO\\Tickets\\Cron\\CloseInactive',0,1579568400,0,0,NULL,0,'[]'),(4,'Ticket reminders',1,'*/5','*','*','*','*','*','GO\\Tickets\\Cron\\Reminder',0,1579529700,0,0,NULL,0,'[]'),(5,'Import tickets from IMAP',1,'0,5,10,15,20,25,30,35,40,45,50,55','*','*','*','*','*','GO\\Tickets\\Cron\\ImportImap',0,1579529700,0,0,NULL,0,'[]'),(6,'Sent tickets due reminder',1,'0','1','*','*','*','*','GO\\Tickets\\Cron\\DueMailer',0,1579564800,0,0,NULL,0,'[]'),(7,'Email Reminders',1,'*/5','*','*','*','*','*','GO\\Base\\Cron\\EmailReminders',0,1579529700,0,0,NULL,0,'[]'),(8,'Calculate disk usage',1,'0','0','*','*','*','*','GO\\Base\\Cron\\CalculateDiskUsage',0,1579561200,0,0,NULL,0,'[]');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_cron_users`
--

LOCK TABLES `go_cron_users` WRITE;
/*!40000 ALTER TABLE `go_cron_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_cron_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_db_sequence`
--

DROP TABLE IF EXISTS `go_db_sequence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_db_sequence` (
  `seq_name` varchar(50) NOT NULL DEFAULT '',
  `nextid` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`seq_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_db_sequence`
--

LOCK TABLES `go_db_sequence` WRITE;
/*!40000 ALTER TABLE `go_db_sequence` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_db_sequence` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_groups`
--

DROP TABLE IF EXISTS `go_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `admin_only` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_groups`
--

LOCK TABLES `go_groups` WRITE;
/*!40000 ALTER TABLE `go_groups` DISABLE KEYS */;
INSERT INTO `go_groups` VALUES (1,'Admins',1,1,0),(2,'Everyone',1,2,0),(3,'Internal',1,3,0),(4,'Managable',1,133,0);
/*!40000 ALTER TABLE `go_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_link_descriptions`
--

DROP TABLE IF EXISTS `go_link_descriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_link_descriptions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`model_id`,`model_type_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_ab_companies`
--

LOCK TABLES `go_links_ab_companies` WRITE;
/*!40000 ALTER TABLE `go_links_ab_companies` DISABLE KEYS */;
INSERT INTO `go_links_ab_companies` VALUES (1,0,1,9,'',1579529588),(1,0,3,9,'',1579529588),(1,0,5,9,'',1579529588),(1,0,7,8,'',1579529588),(2,0,2,9,'',1579529588),(2,0,2,12,'',1579529589),(2,0,3,12,'',1579529589),(2,0,4,9,'',1579529588),(2,0,6,9,'',1579529588),(2,0,8,8,'',1579529588);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_ab_contacts`
--

LOCK TABLES `go_links_ab_contacts` WRITE;
/*!40000 ALTER TABLE `go_links_ab_contacts` DISABLE KEYS */;
INSERT INTO `go_links_ab_contacts` VALUES (2,0,1,9,'',1579529588),(2,0,1,11,'',1579529589),(2,0,2,13,'',1579529589),(2,0,3,9,'',1579529588),(2,0,4,13,'',1579529589),(2,0,5,9,'',1579529588),(2,0,7,8,'',1579529588),(2,0,10,7,'',1579529588),(2,0,12,7,'',1579529588),(2,0,14,7,'',1579529588),(2,0,16,7,'',1579529588),(2,0,18,7,'',1579529588),(2,0,20,7,'',1579529588),(3,0,1,7,'',1579529587),(3,0,1,13,'',1579529589),(3,0,2,9,'',1579529588),(3,0,2,11,'',1579529589),(3,0,2,12,'',1579529589),(3,0,3,12,'',1579529589),(3,0,3,13,'',1579529589),(3,0,4,7,'',1579529588),(3,0,4,8,'',1579529588),(3,0,4,9,'',1579529588),(3,0,5,8,'',1579529588),(3,0,6,8,'',1579529588),(3,0,6,9,'',1579529588),(3,0,7,7,'',1579529588),(3,0,8,8,'',1579529588),(3,0,51,7,'',1598881255),(3,0,54,7,'',1598881262);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_bs_orders`
--

LOCK TABLES `go_links_bs_orders` WRITE;
/*!40000 ALTER TABLE `go_links_bs_orders` DISABLE KEYS */;
INSERT INTO `go_links_bs_orders` VALUES (1,0,1,4,'',1579529588),(1,0,2,2,'',1579529588),(1,0,7,8,'',1579529588),(2,0,2,4,'',1579529588),(2,0,3,2,'',1579529588),(2,0,8,8,'',1579529588),(3,0,1,4,'',1579529588),(3,0,2,2,'',1579529588),(4,0,2,4,'',1579529588),(4,0,3,2,'',1579529588),(5,0,1,4,'',1579529588),(5,0,2,2,'',1579529588),(6,0,2,4,'',1579529588),(6,0,3,2,'',1579529588);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_cal_events`
--

LOCK TABLES `go_links_cal_events` WRITE;
/*!40000 ALTER TABLE `go_links_cal_events` DISABLE KEYS */;
INSERT INTO `go_links_cal_events` VALUES (1,0,3,2,'',1579529587),(4,0,3,2,'',1579529588),(7,0,3,2,'',1579529588),(10,0,2,2,'',1579529588),(12,0,2,2,'',1579529588),(14,0,2,2,'',1579529588),(16,0,2,2,'',1579529588),(18,0,2,2,'',1579529588),(20,0,2,2,'',1579529588),(20,0,4,8,'',1579529588),(20,0,5,8,'',1579529588),(20,0,6,8,'',1579529588),(51,0,3,2,'',1598881255),(54,0,3,2,'',1598881262);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_em_links`
--

LOCK TABLES `go_links_em_links` WRITE;
/*!40000 ALTER TABLE `go_links_em_links` DISABLE KEYS */;
INSERT INTO `go_links_em_links` VALUES (1,0,3,2,'',1579529589),(2,0,2,2,'',1579529589),(3,0,3,2,'',1579529589),(4,0,2,2,'',1579529589);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_fs_folders`
--

LOCK TABLES `go_links_fs_folders` WRITE;
/*!40000 ALTER TABLE `go_links_fs_folders` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_fs_folders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_go_users`
--

DROP TABLE IF EXISTS `go_links_go_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_go_users` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_go_users`
--

LOCK TABLES `go_links_go_users` WRITE;
/*!40000 ALTER TABLE `go_links_go_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_go_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_links_no_notes`
--

DROP TABLE IF EXISTS `go_links_no_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_links_no_notes` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_no_notes`
--

LOCK TABLES `go_links_no_notes` WRITE;
/*!40000 ALTER TABLE `go_links_no_notes` DISABLE KEYS */;
INSERT INTO `go_links_no_notes` VALUES (1,0,2,2,'',1579529589),(2,0,3,2,'',1579529589);
/*!40000 ALTER TABLE `go_links_no_notes` ENABLE KEYS */;
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
INSERT INTO `go_links_pr2_projects` VALUES (2,0,2,4,'',1579529589),(2,0,3,2,'',1579529589),(3,0,2,4,'',1579529589),(3,0,3,2,'',1579529589);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_ta_tasks`
--

LOCK TABLES `go_links_ta_tasks` WRITE;
/*!40000 ALTER TABLE `go_links_ta_tasks` DISABLE KEYS */;
INSERT INTO `go_links_ta_tasks` VALUES (4,0,3,2,'',1579529588),(4,0,20,7,'',1579529588),(5,0,3,2,'',1579529588),(5,0,20,7,'',1579529588),(6,0,3,2,'',1579529588),(6,0,20,7,'',1579529588),(7,0,1,4,'',1579529588),(7,0,1,9,'',1579529588),(7,0,2,2,'',1579529588),(8,0,2,4,'',1579529588),(8,0,2,9,'',1579529588),(8,0,3,2,'',1579529588);
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
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_links_ti_tickets`
--

LOCK TABLES `go_links_ti_tickets` WRITE;
/*!40000 ALTER TABLE `go_links_ti_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_links_ti_tickets` ENABLE KEYS */;
UNLOCK TABLES;


--
-- Table structure for table `go_mail_counter`
--

DROP TABLE IF EXISTS `go_mail_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_mail_counter` (
  `host` varchar(100) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`host`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_mail_counter`
--

LOCK TABLES `go_mail_counter` WRITE;
/*!40000 ALTER TABLE `go_mail_counter` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_mail_counter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_model_types`
--

DROP TABLE IF EXISTS `go_model_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_model_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_model_types`
--

LOCK TABLES `go_model_types` WRITE;
/*!40000 ALTER TABLE `go_model_types` DISABLE KEYS */;
INSERT INTO `go_model_types` VALUES (1,'GO\\Files\\Model\\Folder'),(2,'GO\\Addressbook\\Model\\Contact'),(3,'GO\\Base\\Model\\User'),(4,'GO\\Addressbook\\Model\\Company'),(5,'GO\\Comments\\Model\\Comment'),(6,'GO\\Files\\Model\\File'),(7,'GO\\Calendar\\Model\\Event'),(8,'GO\\Tasks\\Model\\Task'),(9,'GO\\Billing\\Model\\Order'),(10,'GO\\Tickets\\Model\\Ticket'),(11,'GO\\Notes\\Model\\Note'),(12,'GO\\Projects2\\Model\\Project'),(13,'GO\\Savemailas\\Model\\LinkedEmail'),(14,'GO\\Addressbook\\Model\\Addresslist');
/*!40000 ALTER TABLE `go_model_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_modules`
--

DROP TABLE IF EXISTS `go_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_modules` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `admin_menu` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_modules`
--

LOCK TABLES `go_modules` WRITE;
/*!40000 ALTER TABLE `go_modules` DISABLE KEYS */;
INSERT INTO `go_modules` VALUES ('addressbook',306,0,0,4,1),('admin2userlogin',0,34,1,125,1),('advancedprojectsearch',0,33,0,122,1),('assistant',0,37,0,132,1),('billing',300,1,0,10,1),('bookmarks',18,2,0,26,1),('calendar',174,3,0,28,1),('comments',14,4,0,29,1),('cron',0,5,1,30,1),('customfields',101,6,0,31,1),('defaultsite',0,32,0,114,1),('documenttemplates',0,8,0,33,1),('email',97,9,0,34,1),('files',109,10,0,35,1),('freebusypermissions',4,36,0,129,1),('gota',0,11,0,38,1),('groups',0,12,1,39,1),('hoursapproval2',0,13,0,40,1),('lavenderprofiles',125,14,0,41,1),('leavedays',27,15,0,42,1),('log',0,35,1,127,1),('modules',0,16,1,43,1),('notes',27,17,0,44,1),('projectattachmentdownloader',0,18,0,46,1),('projects2',363,19,0,47,1),('savemailas',8,20,0,55,1),('search',0,21,0,56,1),('sieve',0,22,0,57,1),('site',10,31,0,113,1),('smime',6,32,0,121,1),('summary',17,23,0,58,1),('sync',36,24,0,59,1),('tasks',55,25,0,60,1),('tickets',149,26,0,61,1),('timeregistration2',0,27,0,64,1),('tools',0,28,1,65,1),('users',0,29,1,66,1),('workflow',39,30,0,67,1);
/*!40000 ALTER TABLE `go_modules` ENABLE KEYS */;
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
  `name` varchar(100) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `vtime` int(11) NOT NULL DEFAULT 0,
  `snooze_time` int(11) NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT 0,
  `text` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
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
  PRIMARY KEY (`reminder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `export_columns` text DEFAULT NULL,
  `orientation` enum('V','H') NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT 1,
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) NOT NULL,
  `sql` text NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_saved_search_queries`
--

LOCK TABLES `go_saved_search_queries` WRITE;
/*!40000 ALTER TABLE `go_saved_search_queries` DISABLE KEYS */;
/*!40000 ALTER TABLE `go_saved_search_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_search_cache`
--

DROP TABLE IF EXISTS `go_search_cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_search_cache` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `model_type_id` int(11) NOT NULL DEFAULT 0,
  `model_name` varchar(100) DEFAULT NULL,
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `mtime` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`model_id`,`model_type_id`),
  KEY `acl_id` (`acl_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_search_cache`
--

LOCK TABLES `go_search_cache` WRITE;
/*!40000 ALTER TABLE `go_search_cache` DISABLE KEYS */;
INSERT INTO `go_search_cache` VALUES (1,1,'files','notes','notes',1,'GO\\Files\\Model\\Folder','Folder,notes',1579529587,45,'Folder'),(1,1,'addressbook','Administrator, System (Users)','',2,'GO\\Addressbook\\Model\\Contact','Contact,Administrator, System (Users),719364e4-855f-54c7-8e03-d20460f77b83,System,M,admin@intermesh.localhost,Dear System,1.jpg',1579601720,69,'Contact'),(1,1,'base','Administrator, System','',3,'GO\\Base\\Model\\User','User,Administrator, System,admin,$2y$10$Y1i2xnss1gKemszuWRkOOeGxtVmIURE/EcmG61A4mbl0ybUjZZVbC,42c29bbb2cc67376d0a9f6e185f7ec2b,System,admin@intermesh.localhost,dmY,-,G:i,.,€,Europe/Amsterdam,summary,en,Group-Office,last_name,;,\",crypt',1602829868,68,'User'),(1,1,'addressbook','Smith Inc (Customers)','',4,'GO\\Addressbook\\Model\\Company','Company,Smith Inc (Customers),Smith Inc,Kalverstraat,1,1012 NX,Amsterdam,Noord-Holland,NL,Noord-Brabant,+31 (0) 10 - 1234567,+31 (0) 1234567,info@smith.demo,http://www.smith.demo,Just a demo company,NL 1234.56.789.B01',1579529586,7,'Company'),(1,1,'comments','The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...','',5,'GO\\Comments\\Model\\Comment','Comment,The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...,7,The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate which produces every product type imaginable, no matte',1579529586,7,'Comment'),(1,1,'files','Demo letter.docx','addressbook/Customers/contacts/C/Coyote, Wile E (3)/Demo letter.docx',6,'GO\\Files\\Model\\File','File,Demo letter.docx,addressbook/Customers/contacts/C/Coyote, Wile E (3)/Demo letter.docx,docx',1579529586,7,'File'),(3,1,'calendar','t3 (01-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t3 (01-09-2020, Demo User),1598977800,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t3,ACME NY Office,NEEDS-ACTION,FREQ=DAILY,EBF1E2',1598977800,100,'Event'),(1,1,'tasks','Feed the dog','',8,'GO\\Tasks\\Model\\Task','Task,Feed the dog,8cd99b3a-8113-50ab-bfeb-ad63eea9eb74,NEEDS-ACTION',1579529588,102,'Task'),(1,1,'billing','Q20000001','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,Q20000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo',1579529588,11,'Invoice/Quote'),(1,1,'tickets','Malfunctioning rockets','Coyote, Wile E. (ACME Corporation)',10,'GO\\Tickets\\Model\\Ticket','Ticket,Malfunctioning rockets,Coyote, Wile E. (ACME Corporation),75,202000001,ACME Corporation,Wile,E.,wile@acme.demo',1579529588,75,'Ticket'),(2,1,'notes','Laws and rules','',11,'GO\\Notes\\Model\\Note','Note,Laws and rules,As in other cartoons, the Road Runner and the coyote follow the laws of cartoon physics. For example, the Road Runner has the ability to enter the painted image of a cave, while the coyote cannot (unless there is an opening through whi',1579529589,45,'Note'),(1,1,'projects2','Demo','| Demo | Demo',12,'GO\\Projects2\\Model\\Project','Project,Demo, | Demo | Demo,Just a placeholder for sub projects.,1',1579529589,118,'Project'),(1,1,'savemailas','Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',13,'GO\\Savemailas\\Model\\LinkedEmail','Email,Rocket 2000 development plan,1368777188,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,\"User, Demo\" <demo@group-office.com>,\"Elmer\" <elmer@group-office.com>,email/fromfile/demo_5e25b575ca4b9.eml/demo.eml,<1368777188.',1368777188,7,'Email'),(1,1,'addressbook','Test','',14,'GO\\Addressbook\\Model\\Addresslist','Address list,Test,Dear sir/madam',1591260411,123,'Address list'),(1,2,'files','General','notes/General',1,'GO\\Files\\Model\\Folder','Folder,General,notes/General',1579529569,45,'Folder'),(1,2,'addressbook','Smith, John (Smith Inc) (Customers)','',2,'GO\\Addressbook\\Model\\Contact','Contact,Smith, John (Smith Inc) (Customers),e7b7df34-e2b7-5d86-a429-4a430163805e,John,M,john@smith.demo,CEO,06-12345678,NL,Noord-Holland,Amsterdam,1012 NX,Kalverstraat,1,Dear Mr. Smith,addressbook/photos/3/con_2.jpg,http://www.linkedin.com,http://www.face',1579529586,7,'Contact'),(2,2,'base','Fudd, Elmer','',3,'GO\\Base\\Model\\User','User,Fudd, Elmer,elmer,$2y$10$rwR3mPGnbKGEnY2uUxRWFugQFXTXY64gMn8S80kP0twTXVs4z83gS,095cfa9d1218171b6ef5096cc5df41af,Elmer,elmer@intermesh.localhost,elmer@acmerpp.demo,dmY,-,G:i,.,€,Europe/Amsterdam,summary,en,Group-Office,last_name,;,\",crypt',1601299920,91,'User'),(1,2,'addressbook','ACME Corporation (Customers)','',4,'GO\\Addressbook\\Model\\Company','Company,ACME Corporation (Customers),ACME Corporation,1111 Broadway,10019,New York,NY,US,(555) 123-4567,info@acme.demo,http://www.acme.demo,The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as',1579529586,7,'Company'),(1,2,'comments','Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...','',5,'GO\\Comments\\Model\\Comment','Comment,Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...,7, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat, which had been used on Elmer Fudd and Sam Sheepdog (which doesn\'t mau',1579529586,7,'Comment'),(1,2,'files','empty.docx','users/demo/empty.docx',6,'GO\\Files\\Model\\File','File,empty.docx,users/demo/empty.docx,docx',1579529589,98,'File'),(4,2,'calendar','t3 (01-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,t3 (01-09-2020, Linda Smith),1598977800,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t3,ACME NY Office,NEEDS-ACTION,FREQ=WEEKLY;BYDAY=TU,EBF1E2',1598977800,106,'Event'),(1,2,'tasks','Feed the dog','',8,'GO\\Tasks\\Model\\Task','Task,Feed the dog,a16aa03d-483e-5344-afd7-bb0d523b99b6,NEEDS-ACTION',1579529588,108,'Task'),(1,2,'billing','Q20000002','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,Q20000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo',1579529588,11,'Invoice/Quote'),(1,2,'tickets','Can I speed up my rockets?','Coyote, Wile E. (ACME Corporation)',10,'GO\\Tickets\\Model\\Ticket','Ticket,Can I speed up my rockets?,Coyote, Wile E. (ACME Corporation),75,202000002,ACME Corporation,Wile,E.,admin@intermesh.localhost,test@intermesh.localhost',1600072580,75,'Ticket'),(3,2,'notes','Wile E. Coyote and Bugs Bunny','',11,'GO\\Notes\\Model\\Note','Note,Wile E. Coyote and Bugs Bunny,Wile E. Coyote has also unsuccessfully attempted to catch and eat Bugs Bunny in another series of cartoons. In these cartoons, the coyote takes on the guise of a self-described \"super genius\" and speaks with a smooth, ge',1579529589,45,'Note'),(1,2,'projects2','[001] Develop Rocket 2000','| Demo | Demo/[001] Develop Rocket 2000',12,'GO\\Projects2\\Model\\Project','Project,[001] Develop Rocket 2000, | Demo | Demo/[001] Develop Rocket 2000,Better range and accuracy,Demo/[001] Develop Rocket 2000,2',1583163112,118,'Project'),(1,2,'savemailas','Rocket 2000 development plan','From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>',13,'GO\\Savemailas\\Model\\LinkedEmail','Email,Rocket 2000 development plan,1368777188,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,\"User, Demo\" <demo@group-office.com>,\"Elmer\" <elmer@group-office.com>,email/fromfile/demo_5e25b575d1d62.eml/demo.eml,<1368777188.',1368777188,7,'Email'),(1,3,'files','project_templates','project_templates',1,'GO\\Files\\Model\\Folder','Folder,project_templates',1579529570,53,'Folder'),(1,3,'addressbook','Coyote, Wile E. (ACME Corporation) (Customers)','',2,'GO\\Addressbook\\Model\\Contact','Contact,Coyote, Wile E. (ACME Corporation) (Customers),4abeedd8-c256-58c6-98eb-67c3c835b40d,Wile,E.,M,wile@acme.demo,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Mr. Coyote,addressbook/photos/3/con_3.jpg,http://www.linkedin.com,http://www.faceb',1579529586,7,'Contact'),(3,3,'base','User, Demo','',3,'GO\\Base\\Model\\User','User, Demo,demo,$2y$10$EKsdVwZe7jpSWBkEdwMAc.8TiArVy2TRzL5Iua8erO0WjqMHEn8Pm,476b3a8d391908d9c3774563a039082e,Demo,test@intermesh.localhost,dmY,-,G:i,.,€,Europe/Amsterdam,summary,en,Group-Office,last_name,;,\",crypt',1601299890,97,'User'),(1,3,'addressbook','ACME Rocket Powered Products (Users)','',4,'GO\\Addressbook\\Model\\Company','Company,ACME Rocket Powered Products (Users),ACME Rocket Powered Products,1111 Broadway,10019,New York,NY,US,(555) 123-4567,info@acmerpp.demo,http://www.acmerpp.demo,The name Acme became popular for businesses by the 1920s, when alphabetized business tele',1579529586,69,'Company'),(1,3,'comments','Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...','',5,'GO\\Comments\\Model\\Comment','Comment,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...,7,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodi',1579529586,7,'Comment'),(1,3,'files','noperson.jpg','users/demo/noperson.jpg',6,'GO\\Files\\Model\\File','File,noperson.jpg,users/demo/noperson.jpg,jpg',1579529589,98,'File'),(2,3,'calendar','t3 (01-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,t3 (01-09-2020, Elmer Fudd),1598977800,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t3,ACME NY Office,NEEDS-ACTION,FREQ=WEEKLY;BYDAY=TU,EBF1E2',1598977800,94,'Event'),(1,3,'tasks','Feed the dog','',8,'GO\\Tasks\\Model\\Task','Task,Feed the dog,4ea7fe25-b4bd-57be-8582-8bcc7bf412ac,NEEDS-ACTION',1579529588,96,'Task'),(1,3,'billing','O20000001','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,O20000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo',1579529588,16,'Invoice/Quote'),(3,3,'tickets','Test from demo','User, Demo (ACME Rocket Powered Products)',10,'GO\\Tickets\\Model\\Ticket','Ticket,Test from demo,User, Demo (ACME Rocket Powered Products),76,202000003,ACME Rocket Powered Products,Demo,demo@acmerpp.demo,test@intermesh.localhost',1598357606,76,'Ticket'),(1,3,'projects2','[001] Develop Rocket Launcher','| Demo | Demo/[001] Develop Rocket Launcher',12,'GO\\Projects2\\Model\\Project','Project,[001] Develop Rocket Launcher, | Demo | Demo/[001] Develop Rocket Launcher,Better range and accuracy,Demo/[001] Develop Rocket Launcher,1',1579529589,118,'Project'),(1,3,'savemailas','Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',13,'GO\\Savemailas\\Model\\LinkedEmail','Email,Just a demo message,1368777986,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>,\"User,email/fromfile/demo2_5e25b575d4f25.eml/demo2.eml,<1368777986.5195e5020b17e@localhost>',1368777986,7,'Email'),(1,4,'files','Projects folder','project_templates/Projects folder',1,'GO\\Files\\Model\\Folder','Folder,Projects folder,project_templates/Projects folder',1579529570,53,'Folder'),(1,4,'addressbook','Fudd, Elmer (ACME Rocket Powered Products) (Users)','',2,'GO\\Addressbook\\Model\\Contact','Contact,Fudd, Elmer (ACME Rocket Powered Products) (Users),a6a3e204-ecae-53c8-8e41-eaea6bf8696b,Elmer,M,elmer@intermesh.localhost,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Elmer,4.jpg',1601299706,69,'Contact'),(4,4,'base','Smith, Linda','',3,'GO\\Base\\Model\\User','User,Smith, Linda,linda,$2y$10$A/vRanQ5F2Hv8pt7Vu32U.8Kx76ANICQ0cxpdjZkqSHvqRU3Ihlti,07fe960e914c4cd8f2b5b62192e03cb7,Linda,linda@acmerpp.demo,dmY,-,G:i,.,€,Europe/Amsterdam,summary,en,Group-Office,last_name,;,\",crypt',1579529587,103,'User'),(1,4,'comments','In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...','',5,'GO\\Comments\\Model\\Comment','Comment,In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...,7, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was origina',1579529586,7,'Comment'),(1,4,'files','empty.odt','users/demo/empty.odt',6,'GO\\Files\\Model\\File','File,empty.odt,users/demo/empty.odt,odt',1579529589,98,'File'),(3,4,'calendar','Meet Wile (21-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Meet Wile (21-01-2020, Demo User),1579604400,2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2',1579604400,100,'Event'),(1,4,'tasks','Prepare meeting','',8,'GO\\Tasks\\Model\\Task','Task,Prepare meeting,482d669b-349a-5b13-9f1e-ca44ed4d56d1,NEEDS-ACTION',1579529588,102,'Task'),(1,4,'billing','O20000002','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,O20000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo',1579529588,16,'Invoice/Quote'),(1,4,'savemailas','Just a demo message','From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>',13,'GO\\Savemailas\\Model\\LinkedEmail','Email,Just a demo message,1368777986,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>,\"User,email/fromfile/demo2_5e25b575d7f8c.eml/demo2.eml,<1368777986.5195e5020b17e@localhost>',1368777986,7,'Email'),(1,5,'files','Standard project','project_templates/Standard project',1,'GO\\Files\\Model\\Folder','Folder,Standard project,project_templates/Standard project',1579529570,54,'Folder'),(1,5,'addressbook','User, Demo (ACME Rocket Powered Products) (Users)','',2,'GO\\Addressbook\\Model\\Contact','Contact,User, Demo (ACME Rocket Powered Products) (Users),06b72fce-d130-59a2-aafd-8e3a107d22c9,Demo,M,test@intermesh.localhost,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Demo,5.jpg',1602829979,69,'Contact'),(1,5,'comments','Scheduled call at 23-01-2020 15:13','',5,'GO\\Comments\\Model\\Comment','Comment,Scheduled call at 23-01-2020 15:13,11',1579529588,11,'Comment'),(1,5,'files','wecoyote.png','users/demo/wecoyote.png',6,'GO\\Files\\Model\\File','File,wecoyote.png,users/demo/wecoyote.png,png',1579529589,98,'File'),(4,5,'calendar','Meet Wile (21-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Meet Wile (21-01-2020, Linda Smith),1579604400,2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2',1579604400,106,'Event'),(1,5,'tasks','Prepare meeting','',8,'GO\\Tasks\\Model\\Task','Task,Prepare meeting,b46a3177-9d96-503d-be2d-267e6f38ce56,NEEDS-ACTION',1579529588,108,'Task'),(1,5,'billing','I20000001','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,I20000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo',1579529588,21,'Invoice/Quote'),(1,6,'files','tickets','tickets',1,'GO\\Files\\Model\\Folder','Folder,tickets',1579529571,62,'Folder'),(1,6,'addressbook','Smith, Linda (ACME Rocket Powered Products) (Users)','',2,'GO\\Addressbook\\Model\\Contact','Contact,Smith, Linda (ACME Rocket Powered Products) (Users),9a259250-41df-51ed-a23f-83373ebffae9,Linda,M,linda@acmerpp.demo,CEO,06-12345678,US,NY,New York,10019,1111 Broadway,Dear Linda',1579529587,69,'Contact'),(1,6,'comments','Scheduled call at 23-01-2020 15:13','',5,'GO\\Comments\\Model\\Comment','Comment,Scheduled call at 23-01-2020 15:13,11',1579529588,11,'Comment'),(1,6,'files','Demo letter.docx','users/demo/Demo letter.docx',6,'GO\\Files\\Model\\File','File,Demo letter.docx,users/demo/Demo letter.docx,docx',1579529589,98,'File'),(2,6,'calendar','Meet Wile (21-01-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,Meet Wile (21-01-2020, Elmer Fudd),1579604400,2b5e4aa6-cd51-5d4f-962f-51f7ca755fbc,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2',1579604400,94,'Event'),(1,6,'tasks','Prepare meeting','',8,'GO\\Tasks\\Model\\Task','Task,Prepare meeting,948c2faf-30b7-51fb-9050-d59f9787cf7b,NEEDS-ACTION',1579529588,96,'Task'),(1,6,'billing','I20000002','',9,'GO\\Billing\\Model\\Order','Invoice/Quote,I20000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo',1579529588,21,'Invoice/Quote'),(3,6,'tickets','t3','User, Demo (ACME Rocket Powered Products)',10,'GO\\Tickets\\Model\\Ticket','Ticket,t3,User, Demo (ACME Rocket Powered Products),76,202000006,ACME Rocket Powered Products,Demo,demo@acmerpp.demo,test@intermesh.localhost',1600072650,76,'Ticket'),(1,7,'files','folder.png','projects2/template-icons/folder.png',6,'GO\\Files\\Model\\File','File,folder.png,projects2/template-icons/folder.png,png',1579529570,47,'File'),(3,7,'calendar','MT Meeting (21-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,MT Meeting (21-01-2020, Demo User),1579611600,9b5cabdf-12f2-5e6e-bfdf-5e50914836dd,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2',1579611600,100,'Event'),(1,7,'tasks','Call: Smith Inc (Q20000001)','',8,'GO\\Tasks\\Model\\Task','Task,Call: Smith Inc (Q20000001),7a6ca641-3dae-5eed-a5fc-77d39f13debc,NEEDS-ACTION',1579529588,72,'Task'),(1,8,'files','project.png','projects2/template-icons/project.png',6,'GO\\Files\\Model\\File','File,project.png,projects2/template-icons/project.png,png',1579529570,47,'File'),(4,8,'calendar','MT Meeting (21-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,MT Meeting (21-01-2020, Linda Smith),1579611600,9b5cabdf-12f2-5e6e-bfdf-5e50914836dd,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2',1579611600,106,'Event'),(1,8,'tasks','Call: ACME Corporation (Q20000002)','',8,'GO\\Tasks\\Model\\Task','Task,Call: ACME Corporation (Q20000002),78218b4f-7698-5b94-b38f-5d4b51ba512a,NEEDS-ACTION',1579529588,72,'Task'),(1,9,'files','addressbook','addressbook',1,'GO\\Files\\Model\\Folder','Folder,addressbook',1579529587,69,'Folder'),(1,9,'files','Functionele eisen software en hardware poortinstructie.docx','users/admin/Public/Functionele eisen software en hardware poortinstructie.docx',6,'GO\\Files\\Model\\File','File,Functionele eisen software en hardware poortinstructie.docx,users/admin/Public/Functionele eisen software en hardware poortinstructie.docx,docx',1598348781,73,'File'),(2,9,'calendar','MT Meeting (21-01-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,MT Meeting (21-01-2020, Elmer Fudd),1579611600,9b5cabdf-12f2-5e6e-bfdf-5e50914836dd,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2',1579611600,94,'Event'),(1,10,'files','Users','addressbook/Users',1,'GO\\Files\\Model\\Folder','Folder,Users,addressbook/Users',1579529571,69,'Folder'),(3,10,'files','sdfdsf.zip','users/demo/sdfdsf.zip',6,'GO\\Files\\Model\\File','File,sdfdsf.zip,users/demo/sdfdsf.zip,zip',1598349334,98,'File'),(4,10,'calendar','Project meeting (22-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Project meeting (22-01-2020, Linda Smith),1579687200,43b24986-a34d-5f4f-87d0-b8412ac21515,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2',1579687200,106,'Event'),(1,11,'files','users','users',1,'GO\\Files\\Model\\Folder','Folder,users',1579529587,35,'Folder'),(1,11,'files','Rutger.zip','users/admin/Public/Rutger.zip',6,'GO\\Files\\Model\\File','File,Rutger.zip,users/admin/Public/Rutger.zip,zip',1600154435,73,'File'),(3,11,'calendar','Project meeting (22-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Project meeting (22-01-2020, Demo User),1579687200,43b24986-a34d-5f4f-87d0-b8412ac21515,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2',1579687200,100,'Event'),(1,12,'files','admin','users/admin',1,'GO\\Files\\Model\\Folder','Folder,admin,users/admin',1600691980,70,'Folder'),(1,12,'files','test.zip','users/demo/test.zip',6,'GO\\Files\\Model\\File','File,test.zip,users/demo/test.zip,zip',1600154990,98,'File'),(4,12,'calendar','Meet John (22-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Meet John (22-01-2020, Linda Smith),1579694400,b1575a45-5b63-51ca-a8d9-b127b0d7aa8c,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2',1579694400,106,'Event'),(1,13,'files','calendar','calendar',1,'GO\\Files\\Model\\Folder','Folder,calendar',1579529588,71,'Folder'),(1,13,'files','documents-6.4-license.txt','users/admin/New folder/documents-6.4-license.txt',6,'GO\\Files\\Model\\File','File,documents-6.4-license.txt,users/admin/New folder/documents-6.4-license.txt,txt',1600691986,70,'File'),(3,13,'calendar','Meet John (22-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Meet John (22-01-2020, Demo User),1579694400,b1575a45-5b63-51ca-a8d9-b127b0d7aa8c,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2',1579694400,100,'Event'),(1,14,'files','System Administrator','calendar/System Administrator',1,'GO\\Files\\Model\\Folder','Folder,System Administrator,calendar/System Administrator',1579529572,71,'Folder'),(1,14,'files','dsfsd.zip','users/admin/dsfsd.zip',6,'GO\\Files\\Model\\File','File,dsfsd.zip,users/admin/dsfsd.zip,zip',1600691993,70,'File'),(4,14,'calendar','MT Meeting (22-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,MT Meeting (22-01-2020, Linda Smith),1579705200,50ac853d-9a22-5403-98bf-95c3669d06dc,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2',1579705200,106,'Event'),(1,15,'files','tasks','tasks',1,'GO\\Files\\Model\\Folder','Folder,tasks',1579529587,72,'Folder'),(1,15,'files','Test.docx','users/admin/Test.docx',6,'GO\\Files\\Model\\File','File,Test.docx,users/admin/Test.docx,docx',1602146196,70,'File'),(3,15,'calendar','MT Meeting (22-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,MT Meeting (22-01-2020, Demo User),1579705200,50ac853d-9a22-5403-98bf-95c3669d06dc,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2',1579705200,100,'Event'),(1,16,'files','System Administrator','tasks/System Administrator',1,'GO\\Files\\Model\\Folder','Folder,System Administrator,tasks/System Administrator',1579529572,72,'Folder'),(4,16,'calendar','Rocket testing (21-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Rocket testing (21-01-2020, Linda Smith),1579590000,79860a30-0c5a-52d2-824e-f93f98c4cc9b,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2',1579590000,106,'Event'),(1,17,'files','Public','users/admin/Public',1,'GO\\Files\\Model\\Folder','Folder,Public,users/admin/Public',1598348976,73,'Folder'),(3,17,'calendar','Rocket testing (21-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Rocket testing (21-01-2020, Demo User),1579590000,79860a30-0c5a-52d2-824e-f93f98c4cc9b,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2',1579590000,100,'Event'),(1,18,'files','billing','billing',1,'GO\\Files\\Model\\Folder','Folder,billing',1579529588,35,'Folder'),(4,18,'calendar','Blast impact test (21-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Blast impact test (21-01-2020, Linda Smith),1579615200,6e2dab85-5126-5245-a0c2-bdd4a1e45775,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2',1579615200,106,'Event'),(1,19,'files','stationery-papers','billing/stationery-papers',1,'GO\\Files\\Model\\Folder','Folder,stationery-papers,billing/stationery-papers',1579529581,35,'Folder'),(3,19,'calendar','Blast impact test (21-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Blast impact test (21-01-2020, Demo User),1579615200,6e2dab85-5126-5245-a0c2-bdd4a1e45775,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2',1579615200,100,'Event'),(1,20,'files','projects2','projects2',1,'GO\\Files\\Model\\Folder','Folder,projects2',1579529570,35,'Folder'),(4,20,'calendar','Test range extender (21-01-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,Test range extender (21-01-2020, Linda Smith),1579629600,092895d0-87de-588c-82c6-94d6ea174c09,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2',1579629600,106,'Event'),(1,21,'files','template-icons','projects2/template-icons',1,'GO\\Files\\Model\\Folder','Folder,template-icons,projects2/template-icons',1579529570,47,'Folder'),(3,21,'calendar','Test range extender (21-01-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,Test range extender (21-01-2020, Demo User),1579629600,092895d0-87de-588c-82c6-94d6ea174c09,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2',1579629600,100,'Event'),(1,22,'files','Customers','addressbook/Customers',1,'GO\\Files\\Model\\Folder','Folder,Customers,addressbook/Customers',1579529586,7,'Folder'),(1,23,'files','contacts','addressbook/Customers/contacts',1,'GO\\Files\\Model\\Folder','Folder,contacts,addressbook/Customers/contacts',1579529586,7,'Folder'),(1,24,'files','C','addressbook/Customers/contacts/C',1,'GO\\Files\\Model\\Folder','Folder,C,addressbook/Customers/contacts/C',1579529586,7,'Folder'),(1,25,'files','Coyote, Wile E (3)','addressbook/Customers/contacts/C/Coyote, Wile E (3)',1,'GO\\Files\\Model\\Folder','Folder,Coyote, Wile E (3),addressbook/Customers/contacts/C/Coyote',1579529586,7,'Folder'),(2,26,'files','elmer','users/elmer',1,'GO\\Files\\Model\\Folder','Folder,elmer,users/elmer',1579529586,92,'Folder'),(1,27,'files','Elmer Fudd','addressbook/Elmer Fudd',1,'GO\\Files\\Model\\Folder','Folder,Elmer Fudd,addressbook/Elmer Fudd',1579529586,93,'Folder'),(1,28,'files','Elmer Fudd','calendar/Elmer Fudd',1,'GO\\Files\\Model\\Folder','Folder,Elmer Fudd,calendar/Elmer Fudd',1579529586,94,'Folder'),(3,28,'calendar','test resource (24-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,test resource (24-08-2020, Demo User),1598276700,e21a9540-eff9-525b-a92a-e85052ffe327,Europe/Amsterdam,test resource,CONFIRMED,FREQ=WEEKLY;BYDAY=MO,EBF1E2',1598276700,100,'Event'),(1,29,'files','Elmer Fudd','notes/Elmer Fudd',1,'GO\\Files\\Model\\Folder','Folder,Elmer Fudd,notes/Elmer Fudd',1579529587,95,'Folder'),(3,29,'calendar','test resource (24-08-2020, Don Coyote Room)','',7,'GO\\Calendar\\Model\\Event','Event,test resource (24-08-2020, Don Coyote Room),1598276700,51fd5897-b763-551a-84db-b5cede1fca26,Europe/Amsterdam,test resource,NEEDS-ACTION,FREQ=WEEKLY;BYDAY=MO,FF6666,sfdsfsdfd',1598276700,112,'Event'),(1,30,'files','Elmer Fudd','tasks/Elmer Fudd',1,'GO\\Files\\Model\\Folder','Folder,Elmer Fudd,tasks/Elmer Fudd',1579529587,96,'Folder'),(1,30,'calendar','test2 (24-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,test2 (24-08-2020, Demo User),1598246100,42ac0ab9-2994-5592-a549-ecef3947bfb1,Europe/Amsterdam,test2,CONFIRMED,FREQ=DAILY;COUNT=3,EBF1E2',1598246100,100,'Event'),(3,31,'files','demo','users/demo',1,'GO\\Files\\Model\\Folder','Folder,demo,users/demo',1579529589,98,'Folder'),(1,32,'files','Demo User','addressbook/Demo User',1,'GO\\Files\\Model\\Folder','Folder,Demo User,addressbook/Demo User',1579529587,99,'Folder'),(1,33,'files','Demo User','calendar/Demo User',1,'GO\\Files\\Model\\Folder','Folder,Demo User,calendar/Demo User',1579529587,100,'Folder'),(1,34,'files','Demo User','notes/Demo User',1,'GO\\Files\\Model\\Folder','Folder,Demo User,notes/Demo User',1579529587,101,'Folder'),(1,35,'files','Demo User','tasks/Demo User',1,'GO\\Files\\Model\\Folder','Folder,Demo User,tasks/Demo User',1579529587,102,'Folder'),(4,36,'files','linda','users/linda',1,'GO\\Files\\Model\\Folder','Folder,linda,users/linda',1579529587,104,'Folder'),(1,37,'files','Linda Smith','addressbook/Linda Smith',1,'GO\\Files\\Model\\Folder','Folder,Linda Smith,addressbook/Linda Smith',1579529587,105,'Folder'),(1,37,'calendar','m1 (26-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,m1 (26-08-2020, Demo User),1598453100,470ecefd-928b-53b8-b334-dc3b6ec0b008,Europe/Amsterdam,m1,CONFIRMED,EBF1E2',1598453100,100,'Event'),(1,38,'files','Linda Smith','calendar/Linda Smith',1,'GO\\Files\\Model\\Folder','Folder,Linda Smith,calendar/Linda Smith',1579529587,106,'Folder'),(1,39,'files','Linda Smith','notes/Linda Smith',1,'GO\\Files\\Model\\Folder','Folder,Linda Smith,notes/Linda Smith',1579529587,107,'Folder'),(1,40,'files','Linda Smith','tasks/Linda Smith',1,'GO\\Files\\Model\\Folder','Folder,Linda Smith,tasks/Linda Smith',1579529587,108,'Folder'),(1,41,'files','Road Runner Room','calendar/Road Runner Room',1,'GO\\Files\\Model\\Folder','Folder,Road Runner Room,calendar/Road Runner Room',1579529588,111,'Folder'),(1,42,'files','Don Coyote Room','calendar/Don Coyote Room',1,'GO\\Files\\Model\\Folder','Folder,Don Coyote Room,calendar/Don Coyote Room',1579529588,112,'Folder'),(1,43,'files','Quotes','billing/Quotes',1,'GO\\Files\\Model\\Folder','Folder,Quotes,billing/Quotes',1579529588,11,'Folder'),(1,44,'files','Orders','billing/Orders',1,'GO\\Files\\Model\\Folder','Folder,Orders,billing/Orders',1579529588,16,'Folder'),(1,45,'files','Invoices','billing/Invoices',1,'GO\\Files\\Model\\Folder','Folder,Invoices,billing/Invoices',1579529588,21,'Folder'),(1,46,'files','public','public',1,'GO\\Files\\Model\\Folder','Folder,public',1579529589,115,'Folder'),(1,47,'files','site','public/site',1,'GO\\Files\\Model\\Folder','Folder,site,public/site',1579529589,115,'Folder'),(3,47,'calendar','t1 (01-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t1 (01-09-2020, Demo User),1598954400,0109bab9-9422-5bff-b8d6-cd10a781bb7f,Europe/Amsterdam,t1,CONFIRMED,FREQ=DAILY,EBF1E2',1598954400,100,'Event'),(1,48,'files','1','public/site/1',1,'GO\\Files\\Model\\Folder','Folder,1,public/site/1',1579529589,115,'Folder'),(3,48,'calendar','t1 (01-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t1 (01-09-2020, Demo User),1598954400,3f46e34f-c01d-5b0e-ac33-0606cfc78d48,Europe/Amsterdam,t1,CONFIRMED,EBF1E2',1598954400,100,'Event'),(1,49,'files','files','public/site/1/files',1,'GO\\Files\\Model\\Folder','Folder,files,public/site/1/files',1579529589,115,'Folder'),(3,49,'calendar','t2 (08-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (08-09-2020, Demo User),1599587100,c278e04a-1a9a-5287-95ed-581218f03d4d,Europe/Amsterdam,t2,CONFIRMED,FREQ=DAILY,EBF1E2',1599587100,100,'Event'),(1,50,'files','log','log',1,'GO\\Files\\Model\\Folder','Folder,log',1579531597,35,'Folder'),(3,50,'calendar','t2 (08-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (08-09-2020, Demo User),1599587100,1590985c-bfb2-5cc1-b4f4-44744c12176c,Europe/Amsterdam,t2,CONFIRMED,EBF1E2',1599587100,100,'Event'),(1,51,'files','New folder','users/admin/New folder',1,'GO\\Files\\Model\\Folder','Folder,New folder,users/admin/New folder',1600691980,70,'Folder'),(3,51,'calendar','t2 (16-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (16-09-2020, Demo User),1600278300,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600278300,100,'Event'),(4,52,'calendar','t2 (16-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (16-09-2020, Linda Smith),1600278300,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600278300,106,'Event'),(2,53,'calendar','t2 (16-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (16-09-2020, Elmer Fudd),1600278300,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600278300,94,'Event'),(3,54,'calendar','t2 (18-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (18-09-2020, Demo User),1600451100,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600451100,100,'Event'),(4,55,'calendar','t2 (18-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (18-09-2020, Linda Smith),1600451100,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600451100,106,'Event'),(2,56,'calendar','t2 (18-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (18-09-2020, Elmer Fudd),1600451100,6e5e2300-8e4f-5ad3-abe9-b2a13faab2e5,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1600451100,94,'Event'),(3,57,'calendar','t2 (09-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (09-09-2020, Demo User),1599673500,dd9e7955-446c-5fd7-b23b-0c90f2316495,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1599673500,100,'Event'),(2,58,'calendar','t2 (09-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (09-09-2020, Elmer Fudd),1599673500,dd9e7955-446c-5fd7-b23b-0c90f2316495,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1599673500,94,'Event'),(4,59,'calendar','t2 (09-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,t2 (09-09-2020, Linda Smith),1599673500,dd9e7955-446c-5fd7-b23b-0c90f2316495,Europe/Amsterdam,t2,ACME NY Office,NEEDS-ACTION,EBF1E2',1599673500,106,'Event'),(3,60,'calendar','t3 (31-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t3 (31-08-2020, Demo User),1598891400,416f3737-d8bc-5646-a0fc-7e152fad42df,Europe/Amsterdam,t3,CONFIRMED,FREQ=DAILY,EBF1E2',1598891400,100,'Event'),(3,61,'calendar','t3 (31-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,t3 (31-08-2020, Demo User),1598891400,e563b8b6-f820-585b-a6d7-d17954e2778c,Europe/Amsterdam,t3,CONFIRMED,EBF1E2',1598891400,100,'Event'),(3,62,'calendar','d1 (31-08-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,d1 (31-08-2020, Demo User),1598853600,9a11da7f-fea1-50e1-86be-cfdd77e84544,Europe/Amsterdam,d1,CONFIRMED,FREQ=DAILY,EBF1E2',1598853600,100,'Event'),(3,63,'calendar','d1 (31-08-2020, Don Coyote Room)','',7,'GO\\Calendar\\Model\\Event','Event,d1 (31-08-2020, Don Coyote Room),1598853600,fbcb0307-2e55-54a5-b517-b4b52ac680ba,Europe/Amsterdam,d1,NEEDS-ACTION,FREQ=DAILY,FF6666',1598853600,112,'Event'),(3,64,'calendar','d1 (01-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,d1 (01-09-2020, Demo User),1598944500,79920eb6-3728-54cc-9faf-ef61e98a5d78,Europe/Amsterdam,d1,CONFIRMED,FREQ=DAILY,EBF1E2',1598944500,100,'Event'),(3,65,'calendar','d1 (01-09-2020, Don Coyote Room)','',7,'GO\\Calendar\\Model\\Event','Event,d1 (01-09-2020, Don Coyote Room),1598944500,fbcb0307-2e55-54a5-b517-b4b52ac680ba,Europe/Amsterdam,d1,NEEDS-ACTION,FREQ=DAILY,FF6666',1598944500,112,'Event'),(1,66,'calendar','sadasdsa (17-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,sadasdsa (17-09-2020, Demo User),1600331400,6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc,Europe/Amsterdam,sadasdsa,CONFIRMED,EBF1E2',1600331400,100,'Event'),(2,67,'calendar','sadasdsa (17-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,sadasdsa (17-09-2020, Elmer Fudd),1600331400,6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc,Europe/Amsterdam,sadasdsa,CONFIRMED,EBF1E2',1600331400,94,'Event'),(4,68,'calendar','sadasdsa (17-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,sadasdsa (17-09-2020, Linda Smith),1600331400,6884e9b1-787b-5b0c-8ac1-5fbcffbbeefc,Europe/Amsterdam,sadasdsa,CONFIRMED,EBF1E2',1600331400,106,'Event'),(1,69,'calendar','saSasaS (18-09-2020, Demo User)','',7,'GO\\Calendar\\Model\\Event','Event,saSasaS (18-09-2020, Demo User),1600422300,7280f773-a43b-56e2-a4d9-42445afa6a68,Europe/Amsterdam,saSasaS,CONFIRMED,EBF1E2',1600422300,100,'Event'),(2,70,'calendar','saSasaS (18-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,saSasaS (18-09-2020, Elmer Fudd),1600422300,7280f773-a43b-56e2-a4d9-42445afa6a68,Europe/Amsterdam,saSasaS,CONFIRMED,EBF1E2',1600422300,94,'Event'),(4,71,'calendar','saSasaS (18-09-2020, Linda Smith)','',7,'GO\\Calendar\\Model\\Event','Event,saSasaS (18-09-2020, Linda Smith),1600422300,7280f773-a43b-56e2-a4d9-42445afa6a68,Europe/Amsterdam,saSasaS,CONFIRMED,EBF1E2',1600422300,106,'Event'),(1,72,'calendar','saSasaS (18-09-2020, System Administrator)','',7,'GO\\Calendar\\Model\\Event','Event,saSasaS (18-09-2020, System Administrator),1600422300,7280f773-a43b-56e2-a4d9-42445afa6a68,Europe/Amsterdam,saSasaS,CONFIRMED,EBF1E2',1600422300,71,'Event'),(2,79,'calendar','meet Elmer (28-09-2020, Elmer Fudd)','',7,'GO\\Calendar\\Model\\Event','Event,meet Elmer (28-09-2020, Elmer Fudd),1601289000,8d2cc7a7-2977-5500-b63e-071659dd778d,Europe/Amsterdam,meet Elmer,CONFIRMED,EBF1E2',1601289000,94,'Event');
/*!40000 ALTER TABLE `go_search_cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_search_sync`
--

DROP TABLE IF EXISTS `go_search_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_search_sync` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(50) NOT NULL DEFAULT '',
  `last_sync_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_settings`
--

LOCK TABLES `go_settings` WRITE;
/*!40000 ALTER TABLE `go_settings` DISABLE KEYS */;
INSERT INTO `go_settings` VALUES (0,'go_addressbook_export','74'),(0,'projects_bill_item_template','{project_name}: {registering_user_name} worked {units} hours on {date}'),(0,'projects_detailed_printout_on','true'),(0,'projects_payout_item_template','{project_name}: {description} of {responsible_user_name} worked {units} hours in {days} days\n\nTotal: {total_price}. (You can use custom fields of the manager in this template with {col_x})'),(0,'projects_summary_bill_item_template','{project_name} {description} at {registering_user_name}\nUnits:{units}\nDays:{days}'),(0,'projects_summary_payout_item_template','{project_name} {description} of {responsible_user_name}\nUnits: {units}, Days: {days}'),(0,'site_template_publish_date_1','20200611'),(0,'tickets_bill_item_template','{date} #{number} rate: {rate_name}\n{subject}'),(0,'upgrade_mtime','20201117'),(0,'uuid_namespace','11476b1c-2a16-4e21-8d7a-c858410d0a5f'),(0,'version','280'),(1,'comments_disable_orig_company','0'),(1,'comments_disable_orig_contact','0'),(1,'comments_enable_read_more','0'),(1,'email_accounts_tree','[\"root\",\"YWNjb3VudF8x\",\"Zl8xX0lOQk9Y\",\"Zl8xX0lOQk9YL1NwYW0=\",\"Zl8xX0lOQk9YL1tURVNU\",\"Zl8xX0lOQk9YL1tURVNUXSBicmFja2V0cw==\",\"Zl8xX0lOQk9YL0RyYWZ0cw==\",\"Zl8xX0lOQk9YL1NlbnQ=\",\"Zl8xX0lOQk9YL1Rlc3Q=\",\"Zl8xX1NlbnQ=\",\"Zl8xX0RyYWZ0cw==\",\"Zl8xX1RyYXNo\",\"Zl8xX1NwYW0=\",\"Zl8xX1Rlc3RtYXAgaW4gYWRtaW4=\",\"YWNjb3VudF8y\",\"Zl8yX0lOQk9Y\",\"Zl8yX1NlbnQ=\",\"Zl8yX0RyYWZ0cw==\",\"Zl8yX1RyYXNo\",\"Zl8yX1NwYW0=\",\"Zl8yX1Rlc3RtYXAgaW4gdGVzdA==\"]'),(1,'email_always_request_notification','0'),(1,'email_always_respond_to_notifications','0'),(1,'email_font_size','12px'),(1,'email_show_bcc','0'),(1,'email_show_cc','1'),(1,'email_skip_unknown_recipients','0'),(1,'email_use_plain_text_markup','0'),(1,'files_shared_cache_ctime','1600154982'),(1,'ms_books','3,6,5,7,1,2,4'),(1,'ms_calendars','1'),(1,'ms_categories',''),(1,'ms_pm-status-filter','1,2,3'),(1,'ms_pr2_statuses',''),(1,'ms_ti-types-grid','1,2'),(1,'ms_users-groups-panel','1,2,3'),(1,'projects2_tree_state','[\"root\",1,2,3]'),(2,'comments_disable_orig_company','0'),(2,'comments_disable_orig_contact','0'),(2,'comments_enable_read_more','0'),(2,'email_always_request_notification','0'),(2,'email_always_respond_to_notifications','0'),(2,'email_font_size','12px'),(2,'email_show_bcc','0'),(2,'email_show_cc','1'),(2,'email_skip_unknown_recipients','0'),(2,'email_use_plain_text_markup','0'),(2,'ms_calendars','2'),(3,'comments_disable_orig_company','0'),(3,'comments_disable_orig_contact','0'),(3,'comments_enable_read_more','0'),(3,'email_always_request_notification','0'),(3,'email_always_respond_to_notifications','0'),(3,'email_font_size','12px'),(3,'email_show_bcc','0'),(3,'email_show_cc','1'),(3,'email_skip_unknown_recipients','0'),(3,'email_use_plain_text_markup','0'),(3,'files_shared_cache_ctime','1598348921'),(3,'ms_books','1,2,3,4,6'),(3,'ms_calendars','3'),(3,'ms_ti-types-grid','2');
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
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` text DEFAULT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_state`
--

LOCK TABLES `go_state` WRITE;
/*!40000 ALTER TABLE `go_state` DISABLE KEYS */;
INSERT INTO `go_state` VALUES (1,'ab-addresslist-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A82%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A274%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A274%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A274%5Egroup%3Ds%253AaddresslistGroupName'),(1,'adv-pr2-search-dialog','o%3Awidth%3Dn%253A1043%5Eheight%3Dn%253A803'),(1,'calendar-state','s%3A%7B%22displayType%22%3A%22days%22%2C%22days%22%3A7%7D'),(1,'em-pnl-north','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Alabels%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Afrom%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Ds%2525253Ato%25255Ewidth%25253Dn%2525253A200%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A120%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A120%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A65%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253ADESC'),(1,'em-pnl-west','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aicon%25255Ewidth%25253Dn%2525253A46%255Eo%25253Aid%25253Ds%2525253Alabels%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Amessage%25255Ewidth%25253Dn%2525253A204%255Eo%25253Aid%25253Ds%2525253Aarrival%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Adate%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Asize%25255Ewidth%25253Dn%2525253A65%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253ADESC'),(1,'go-checker-panel','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A28%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A310%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A46%5Egroup%3Ds%253Atype'),(1,'go-module-panel-log','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A381%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A152%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A152'),(1,'go-module-panel-modules','o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A1000%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Asort_order%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Apackage%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Aname%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Apackage'),(1,'list-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A1160%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(1,'open-modules','a%3As%253Aaddressbook%5Es%253Acalendar%5Es%253Aemail%5Es%253Afiles%5Es%253Amodules%5Es%253Asummary%5Es%253Atickets%5Es%253Ausers%5Es%253Agroups%5Es%253Aadmin2userlogin%5Es%253Alog'),(1,'pm-tasks','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A120%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A25%5Egroup%3Ds%253Aparent_description%5Ecollapsed%3Db%253A1'),(1,'pr2-advanced-search-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aicon%25255Ewidth%25253Dn%2525253A42%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A10%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A11%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A12%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A13%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A14%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Acol_47%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1'),(1,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A30%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A652%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(1,'ti-types-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A20%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A170%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Agroup_name'),(1,'tr-entry-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A60%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A60%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A60%255Eo%25253Aid%25253Ds%2525253Aproject%25255Ewidth%25253Dn%2525253A300%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A150%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(2,'list-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A1160%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(2,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A30%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A652%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(3,'calendar_event_dialog','o%3Awidth%3Dn%253A620%5Eheight%3Dn%253A450'),(3,'ha2-approve-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A150%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A200%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A200%5Egroup%3Ds%253Auser_name'),(3,'list-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A1160%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),(3,'open-modules','a%3As%253Acalendar%5Es%253Aemail%5Es%253Asummary%5Es%253Agroups'),(3,'su-tasks-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A30%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A652%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),(3,'ti-types-grid','o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A20%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A170%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Agroup_name');
/*!40000 ALTER TABLE `go_state` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `go_users`
--

DROP TABLE IF EXISTS `go_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `digest` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `recovery_email` varchar(100) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `date_format` varchar(20) NOT NULL DEFAULT 'dmY',
  `date_separator` char(1) NOT NULL DEFAULT '-',
  `time_format` varchar(10) NOT NULL DEFAULT 'G:i',
  `thousands_separator` varchar(1) NOT NULL DEFAULT '.',
  `decimal_separator` varchar(1) NOT NULL DEFAULT ',',
  `currency` char(3) NOT NULL DEFAULT '',
  `logins` int(11) NOT NULL DEFAULT 0,
  `lastlogin` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `max_rows_list` tinyint(4) NOT NULL DEFAULT 20,
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/Amsterdam',
  `start_module` varchar(50) NOT NULL DEFAULT 'summary',
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `theme` varchar(20) NOT NULL DEFAULT 'Default',
  `first_weekday` tinyint(4) NOT NULL DEFAULT 0,
  `sort_name` varchar(20) NOT NULL DEFAULT 'first_name',
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `mute_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_reminder_sound` tinyint(1) NOT NULL DEFAULT 0,
  `mute_new_mail_sound` tinyint(1) NOT NULL DEFAULT 0,
  `show_smilies` tinyint(1) NOT NULL DEFAULT 1,
  `auto_punctuation` tinyint(1) NOT NULL DEFAULT 0,
  `list_separator` char(3) NOT NULL DEFAULT ';',
  `text_separator` char(3) NOT NULL DEFAULT '"',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `disk_quota` bigint(20) DEFAULT NULL,
  `disk_usage` bigint(20) NOT NULL DEFAULT 0,
  `mail_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_emails` tinyint(1) NOT NULL DEFAULT 0,
  `password_type` varchar(20) NOT NULL DEFAULT 'crypt',
  `holidayset` varchar(10) DEFAULT NULL,
  `sort_email_addresses_by_time` tinyint(1) NOT NULL DEFAULT 0,
  `no_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_users`
--

LOCK TABLES `go_users` WRITE;
/*!40000 ALTER TABLE `go_users` DISABLE KEYS */;
INSERT INTO `go_users` VALUES (1,'admin','$2y$10$Y1i2xnss1gKemszuWRkOOeGxtVmIURE/EcmG61A4mbl0ybUjZZVbC','42c29bbb2cc67376d0a9f6e185f7ec2b',1,'System','','Administrator','admin@intermesh.localhost','admin@intermesh.localhost',68,'dmY','-','G:i','.',',','€',29,1602829868,1579529571,30,'Europe/Amsterdam','summary','en','Group-Office',1,'last_name',1602829868,1,0,0,0,1,0,';','\"',0,1000,196280,0,0,0,'crypt','en',0,0,1579529571,0),(2,'elmer','$2y$10$rwR3mPGnbKGEnY2uUxRWFugQFXTXY64gMn8S80kP0twTXVs4z83gS','095cfa9d1218171b6ef5096cc5df41af',1,'Elmer','','Fudd','elmer@intermesh.localhost','elmer@acmerpp.demo',91,'dmY','-','G:i','.',',','€',1,1601299920,1579529586,30,'Europe/Amsterdam','summary','en','Group-Office',1,'last_name',1601299920,2,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'crypt','en',0,0,1579529586,0),(3,'demo','$2y$10$EKsdVwZe7jpSWBkEdwMAc.8TiArVy2TRzL5Iua8erO0WjqMHEn8Pm','476b3a8d391908d9c3774563a039082e',1,'Demo','','User','test@intermesh.localhost','test@intermesh.localhost',97,'dmY','-','G:i','.',',','€',4,1601299890,1579529587,30,'Europe/Amsterdam','summary','en','Group-Office',1,'last_name',1601299890,3,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'crypt','en',0,0,1579529587,0),(4,'linda','$2y$10$A/vRanQ5F2Hv8pt7Vu32U.8Kx76ANICQ0cxpdjZkqSHvqRU3Ihlti','07fe960e914c4cd8f2b5b62192e03cb7',1,'Linda','','Smith','linda@acmerpp.demo','linda@acmerpp.demo',103,'dmY','-','G:i','.',',','€',0,0,1579529587,30,'Europe/Amsterdam','summary','en','Group-Office',1,'last_name',1579529587,1,0,0,0,1,0,';','\"',0,1000,0,0,0,0,'crypt','en',0,0,1579529587,0);
/*!40000 ALTER TABLE `go_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`%`*/ /*!50003 TRIGGER `test` BEFORE INSERT ON `go_users` FOR EACH ROW set NEW.lastlogin = NOW() */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `go_users_groups`
--

DROP TABLE IF EXISTS `go_users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `go_users_groups` (
  `group_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_users_groups`
--

LOCK TABLES `go_users_groups` WRITE;
/*!40000 ALTER TABLE `go_users_groups` DISABLE KEYS */;
INSERT INTO `go_users_groups` VALUES (1,1),(2,1),(2,2),(2,3),(2,4),(3,1),(3,2),(3,3),(3,4);
/*!40000 ALTER TABLE `go_users_groups` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `go_working_weeks`
--

LOCK TABLES `go_working_weeks` WRITE;
/*!40000 ALTER TABLE `go_working_weeks` DISABLE KEYS */;
INSERT INTO `go_working_weeks` VALUES (2,8,8,8,8,8,0,0),(3,8,8,8,8,8,0,0);
/*!40000 ALTER TABLE `go_working_weeks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ld_credit_types`
--

DROP TABLE IF EXISTS `ld_credit_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ld_credit_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `credit_doesnt_expired` tinyint(1) NOT NULL DEFAULT 0,
  `sort_index` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_credits`
--

LOCK TABLES `ld_credits` WRITE;
/*!40000 ALTER TABLE `ld_credits` DISABLE KEYS */;
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
  `description` varchar(50) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL DEFAULT 0,
  `ld_credit_type_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_leave_days`
--

LOCK TABLES `ld_leave_days` WRITE;
/*!40000 ALTER TABLE `ld_leave_days` DISABLE KEYS */;
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
  `comments` varchar(50) NOT NULL DEFAULT '0',
  `manager_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ld_year_credits`
--

LOCK TABLES `ld_year_credits` WRITE;
/*!40000 ALTER TABLE `ld_year_credits` DISABLE KEYS */;
/*!40000 ALTER TABLE `ld_year_credits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lp_profiles`
--

DROP TABLE IF EXISTS `lp_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lp_profiles` (
  `full_group_name` varchar(100) NOT NULL,
  `abbreviated_group_name` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `ABN` text NOT NULL,
  `return_to_sender_details` text NOT NULL,
  `members_with_cards` int(11) NOT NULL DEFAULT 0,
  `first_reference_to_group` varchar(100) NOT NULL,
  `subsequent_reference_to_group` varchar(100) NOT NULL,
  `the_before_abbreviation` varchar(100) NOT NULL,
  `signatory_name` varchar(100) NOT NULL,
  `signatory_title` varchar(100) NOT NULL,
  `signatory_organization_name` varchar(100) NOT NULL,
  `signatory_use_photo` tinyint(1) NOT NULL,
  `signatory_photo` text NOT NULL,
  `group_size` varchar(100) NOT NULL,
  `product_name_for_gold_card` varchar(100) NOT NULL,
  `product_name_for_platinum_card` varchar(100) NOT NULL,
  `revenue_disclaimer` text NOT NULL,
  `customer_relationship_database_disclaimer` text NOT NULL,
  `privacy_disclaimer` text NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `logo` text NOT NULL,
  `group_name` text NOT NULL,
  `group_phone` text NOT NULL,
  `group_email` text NOT NULL,
  `kessler_name` text NOT NULL,
  `kessler_phone` text NOT NULL,
  `kessler_email` text NOT NULL,
  `web_tile_specs` text NOT NULL,
  `web_banner_specs` text NOT NULL,
  `insert_specs` varchar(255) NOT NULL,
  `insertPublication_name` text NOT NULL,
  `insertDelivery_details` text NOT NULL,
  `initial_refreshfiles_supplied` varchar(32) NOT NULL DEFAULT 'N',
  `follow_up_refresh_files_supplied` varchar(32) NOT NULL DEFAULT 'N',
  `number_of_lives_supplied` int(11) NOT NULL DEFAULT 0,
  `approve_lives_directly` tinyint(1) NOT NULL,
  `data_instructions` text NOT NULL,
  `seed_contact` text NOT NULL,
  `seed_address1` text NOT NULL,
  `seed_address2` text NOT NULL,
  `seed_suburb` text NOT NULL,
  `seed_postcode` text NOT NULL,
  `product_name_for_sbs_card` text NOT NULL,
  `product_name_for_gold_charge_card` text NOT NULL,
  `male_female_ratio` double DEFAULT NULL,
  `median_member_age` text NOT NULL,
  `median_income` text NOT NULL,
  `other_member_info` text NOT NULL,
  `signatory_photo2` text NOT NULL,
  `web_address` text NOT NULL,
  `username_password` text NOT NULL,
  `product_name_for_platinum_edge_card` text NOT NULL,
  `product_name_for_platinum_charge_card` text NOT NULL,
  `product_name_for_business_accelerator_card` text NOT NULL,
  `product_name_for_student_credit_card` text NOT NULL,
  `refer_to_members_as` text NOT NULL,
  `member_number_reference` text NOT NULL,
  `title_inclusion_dm_packs` text NOT NULL,
  `return_to_sender_address_oe` text NOT NULL,
  `branding_requirements` text NOT NULL,
  `include_amex_logo_outer` text NOT NULL,
  `include_amex_logo_letter` text NOT NULL,
  `logo_colour_letter` text NOT NULL,
  `logo_colour_outer` text NOT NULL,
  `most_common_webtile_pos` text NOT NULL,
  `material_quantity` int(11) NOT NULL DEFAULT 0,
  `material_specs` text NOT NULL,
  `material_cost` double NOT NULL,
  `edm_format` text NOT NULL,
  `edm_cost` double NOT NULL,
  `how_many_lives` varchar(100) NOT NULL DEFAULT '0',
  `include_postnomials_address_line` text NOT NULL,
  `data_segmentation_opportunities` text NOT NULL,
  `total_group_size` int(11) NOT NULL DEFAULT 0,
  `mailable_dm_members` text NOT NULL,
  `mailable_edm_members` text NOT NULL,
  `mailable_enews_members` text NOT NULL,
  `ceo_name` text NOT NULL,
  `available_channel_dm` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_edm` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_inserts` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_event_sponsorship` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_new_member_kits` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_msd` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_enewsletter` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_member_renewals` tinyint(1) NOT NULL DEFAULT 0,
  `available_channel_other` tinyint(1) NOT NULL DEFAULT 0,
  `product_name_for_business_card` text NOT NULL,
  `groups_actively_uploading` text NOT NULL,
  `material_delivery_details` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lp_profiles`
--

LOCK TABLES `lp_profiles` WRITE;
/*!40000 ALTER TABLE `lp_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `lp_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `no_categories`
--

DROP TABLE IF EXISTS `no_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `no_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `no_categories`
--

LOCK TABLES `no_categories` WRITE;
/*!40000 ALTER TABLE `no_categories` DISABLE KEYS */;
INSERT INTO `no_categories` VALUES (1,1,45,'General',2),(2,2,95,'Elmer Fudd',29),(3,3,101,'Demo User',34),(4,4,107,'Linda Smith',39);
/*!40000 ALTER TABLE `no_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `no_notes`
--

DROP TABLE IF EXISTS `no_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `no_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `password` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `no_notes`
--

LOCK TABLES `no_notes` WRITE;
/*!40000 ALTER TABLE `no_notes` DISABLE KEYS */;
INSERT INTO `no_notes` VALUES (1,1,2,1579529589,1579529589,1,'Laws and rules','As in other cartoons, the Road Runner and the coyote follow the laws of cartoon physics. For example, the Road Runner has the ability to enter the painted image of a cave, while the coyote cannot (unless there is an opening through which he can fall). Sometimes, however, this is reversed, and the Road Runner can burst through a painting of a broken bridge and continue on his way, while the Coyote will instead enter the mirage painting and fall down the precipice of the cliff where the bridge is out. Sometimes the coyote is allowed to hang in midair until he realizes that he is about to plummet into a chasm (a process occasionally referred to elsewhere as Road-Runnering or Wile E. Coyote moment). The coyote can overtake rocks (or cannons) which fall earlier than he does, and end up being squashed by them. If a chase sequence happens upon a cliff, the Road Runner is not affected by gravity, whereas the Coyote will realize his error eventually and fall to the ground below. A chase sequence that happens upon railroad tracks will always result in the Coyote being run over by a train. If the Coyote uses an explosive (for instance, dynamite) that is triggered by a mechanism that is supposed to force the explosive in a forward motion toward its target, the actual mechanism itself will always shoot forward, leaving the explosive behind to detonate in the Coyote\'s face. Similarly, a complex apparatus that is supposed to propel an object like a boulder or steel ball forward, or trigger a trap, will not work on the Road Runner, but always will on the Coyote. For instance, the Road Runner can jump up and down on the trigger of a large animal trap and eat bird seed off from it, going completely unharmed and not setting off the trap; when the Coyote places the tiniest droplet of oil on the trigger, the trap snaps shut on him without fail. At certain times, the Coyote may don an exquisite Acme costume or propulsion device that briefly allows him to catch up to the Road Runner. This will always result in him losing track of his proximity to large cliffs or walls, and the Road Runner will dart around an extremely sharp turn on a cliff, but the Coyote will rocket right over the edge and fall to the ground.\n\nIn his book Chuck Amuck: The Life and Times Of An Animated Cartoonist,[13] Chuck Jones claimed that he and the artists behind the Road Runner and Wile E. cartoons adhered to some simple but strict rules:\n\nThe Road Runner cannot harm the Coyote except by going \"beep, beep.\"\nNo outside force can harm the Coyote — only his own ineptitude or the failure of Acme products. Trains and trucks were the exception from time to time.\nThe Coyote could stop anytime — if he were not a fanatic. (Repeat: \"A fanatic is one who redoubles his effort when he has forgotten his aim.\" — George Santayana).\nDialogue must never be used, except \"beep, beep\" and yowling in pain. (This rule, however, was violated in some cartoons.)\nThe Road Runner must stay on the road — for no other reason than that he\'s a roadrunner. This rule was broken in Beep, Beep, in a sequence where Wile E. chased the Road Runner into a cactus mine. And also in Fastest with the Mostestwhen Coyote lures Road Runner to the edge of a cliff.\nAll action must be confined to the natural environment of the two characters — the southwest American desert.\nAll (or at least almost all) tools, weapons, or mechanical conveniences must be obtained from the Acme Corporation. There were sometimes exceptions when the Coyote obtained other items from the desert such as boulders to use in his attempts.\nWhenever possible, make gravity the Coyote\'s greatest enemy (e.g., falling off a cliff).\nThe Coyote is always more humiliated than harmed by his failures.\nThe audience\'s sympathy must remain with the Coyote.\nThe Coyote is not allowed to catch or eat the Road Runner, unless he escapes from the grasp. (The robot that the Coyote created in The Solid Tin Coyote caught the Road Runner so this does not break this rule. The Coyote does catch the Road Runner in Soup or Sonic but is too small to eat him. There is also two CGI shorts on The Looney Tunes Show were he caught the bird, but was not able to eat him because the Road Runner got away in both shorts.)',0,''),(2,1,3,1579529589,1579529589,1,'Wile E. Coyote and Bugs Bunny','Wile E. Coyote has also unsuccessfully attempted to catch and eat Bugs Bunny in another series of cartoons. In these cartoons, the coyote takes on the guise of a self-described \"super genius\" and speaks with a smooth, generic upper-class accent provided by Mel Blanc. While he is incredibly intelligent, he is limited by technology and his own short-sighted arrogance, and is thus often easily outsmarted, a somewhat physical symbolism of \"street smarts\" besting \"book smarts\".\n\nIn one short (Hare-Breadth Hurry, 1963), Bugs Bunny — with the help of \"speed pills\" — even stands in for Road Runner, who has \"sprained a giblet\", and carries out the duties of outsmarting the hungry scavenger. That is the only Bugs Bunny/Wile E. Coyote short in which the coyote does not speak. As usual Wile E. Coyote ends up falling down a canyon. In a later, made-for-TV short, which had a young Elmer Fudd chasing a young Bugs Bunny, Elmer also falls down a canyon. On the way down he is overtaken by Wile E. Coyote who shows a sign telling Elmer to get out of the way for someone who is more experienced in falling.',0,'');
/*!40000 ALTER TABLE `no_notes` ENABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
INSERT INTO `pr2_employee_activity_rate` VALUES (1,2,56.5);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_employees`
--

LOCK TABLES `pr2_employees` WRITE;
/*!40000 ALTER TABLE `pr2_employees` DISABLE KEYS */;
INSERT INTO `pr2_employees` VALUES (2,NULL,1579529589,1583163016,99.8,60),(3,NULL,1579529589,1579529589,80,40),(4,NULL,1579529589,1579529589,90,45);
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
INSERT INTO `pr2_expense_budgets` VALUES (1,'Machinery',10000,0,1579529589,1579529589,NULL,NULL,2,'','',1,'',NULL);
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
INSERT INTO `pr2_expenses` VALUES (1,2,3000,21,1579529589,'','Rocket fuel',1579529589,NULL),(2,2,2000,21,1579529589,'','Fuse machine',1579529589,1);
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
  `comments` text DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_hours`
--

LOCK TABLES `pr2_hours` WRITE;
/*!40000 ALTER TABLE `pr2_hours` DISABLE KEYS */;
INSERT INTO `pr2_hours` VALUES (1,2,511,0,1583132400,8.5166666666667,'',99.8,60,0,NULL,1583163121,1583163135,2,NULL,0,0,0),(2,2,1,0,1583163060,0.016666666666667,'',565,60,0,NULL,1583163148,1583163148,2,1,0,0,0);
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
  `description` varchar(255) NOT NULL DEFAULT '',
  `amount` double NOT NULL,
  `is_invoiced` tinyint(1) NOT NULL DEFAULT 0,
  `invoiceable` tinyint(1) NOT NULL DEFAULT 0,
  `period_start` int(11) NOT NULL DEFAULT 0,
  `period_end` int(11) NOT NULL DEFAULT 0,
  `paid_at` int(11) NOT NULL DEFAULT 0,
  `invoice_at` int(11) NOT NULL,
  `invoice_number` varchar(45) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL,
  `project_id` int(11) NOT NULL,
  `reference_no` varchar(64) NOT NULL DEFAULT '',
  `comments` text DEFAULT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `is_contract` tinyint(1) NOT NULL DEFAULT 0,
  `contract_repeat_amount` int(11) NOT NULL DEFAULT 1,
  `contract_repeat_freq` varchar(10) NOT NULL DEFAULT '',
  `contract_end` int(11) NOT NULL DEFAULT 0,
  `contract_end_notification_days` int(11) NOT NULL DEFAULT 10,
  `contract_end_notification_active` tinyint(1) NOT NULL DEFAULT 0,
  `contract_end_notification_template` int(11) DEFAULT NULL,
  `contract_end_notification_sent` int(11) DEFAULT NULL,
  `contact` varchar(190) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL DEFAULT '',
  `customer` varchar(201) DEFAULT '',
  `description` text DEFAULT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `threshold_mails` varchar(45) DEFAULT NULL,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `start_time` int(11) NOT NULL DEFAULT 0,
  `due_time` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `contact` varchar(150) DEFAULT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `responsible_user_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `path` varchar(255) NOT NULL DEFAULT '',
  `income_type` smallint(2) NOT NULL DEFAULT 1,
  `status_id` int(11) DEFAULT NULL,
  `type_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `parent_project_id` int(11) NOT NULL DEFAULT 0,
  `default_distance` double DEFAULT NULL,
  `travel_costs` double NOT NULL DEFAULT 0,
  `reference_no` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `responsible_user_id` (`responsible_user_id`),
  KEY `fk_pr2_projects_pr2_statuses1_idx` (`status_id`),
  KEY `fk_pr2_projects_pr2_types1_idx` (`type_id`),
  KEY `fk_pr2_projects_pr2_templates1_idx` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_projects`
--

LOCK TABLES `pr2_projects` WRITE;
/*!40000 ALTER TABLE `pr2_projects` DISABLE KEYS */;
INSERT INTO `pr2_projects` VALUES (1,1,118,'Demo','','Just a placeholder for sub projects.',0,1579529589,1579529589,NULL,1,1579529589,0,0,NULL,0,0,0,0,'Demo',1,1,2,1,0,NULL,0,''),(2,1,118,'[001] Develop Rocket 2000','','Better range and accuracy',2,1579529589,1583163112,NULL,1,1579474800,1582153200,3,'',0,0,0,0,'Demo/[001] Develop Rocket 2000',2,1,2,2,1,NULL,0,''),(3,1,118,'[001] Develop Rocket Launcher','','Better range and accuracy',2,1579529589,1579529589,NULL,1,1579529589,1582207989,3,NULL,0,0,0,0,'Demo/[001] Develop Rocket Launcher',1,1,2,2,1,NULL,0,'');
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
INSERT INTO `pr2_resource_activity_rate` VALUES (1,2,2,56.5);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_resources`
--

LOCK TABLES `pr2_resources` WRITE;
/*!40000 ALTER TABLE `pr2_resources` DISABLE KEYS */;
INSERT INTO `pr2_resources` VALUES (2,2,0,99.8,60,0,0),(2,3,100,80,40,0,0),(2,4,16,90,45,0,0),(3,3,16,80,40,0,0);
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
  `code` varchar(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `units` double NOT NULL,
  `description` text DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_billable` tinyint(1) NOT NULL DEFAULT 1,
  `is_always_billable` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_standard_tasks`
--

LOCK TABLES `pr2_standard_tasks` WRITE;
/*!40000 ALTER TABLE `pr2_standard_tasks` DISABLE KEYS */;
INSERT INTO `pr2_standard_tasks` VALUES (1,'1','test',1,'',0,1,0);
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
  `name` varchar(50) NOT NULL DEFAULT '',
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `filterable` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_tree` tinyint(1) NOT NULL DEFAULT 1,
  `make_invoiceable` tinyint(1) NOT NULL DEFAULT 0,
  `not_for_postcalculation` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_statuses`
--

LOCK TABLES `pr2_statuses` WRITE;
/*!40000 ALTER TABLE `pr2_statuses` DISABLE KEYS */;
INSERT INTO `pr2_statuses` VALUES (1,'Ongoing',0,0,1,1,0,0,50),(2,'None',0,0,1,1,0,0,51),(3,'Complete',1,0,1,0,0,0,52);
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
  `description` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `has_children` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) NOT NULL DEFAULT '',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `fields` varchar(255) NOT NULL DEFAULT '',
  `icon` varchar(255) NOT NULL DEFAULT '',
  `project_type` tinyint(4) NOT NULL DEFAULT 0,
  `default_income_email_template` int(11) DEFAULT NULL,
  `default_status_id` int(11) NOT NULL,
  `default_type_id` int(11) DEFAULT NULL,
  `use_name_template` tinyint(1) NOT NULL DEFAULT 0,
  `name_template` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pr2_templates_pr2_types1_idx` (`default_type_id`),
  KEY `fk_pr2_templates_pr2_statuses1_idx` (`default_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_templates`
--

LOCK TABLES `pr2_templates` WRITE;
/*!40000 ALTER TABLE `pr2_templates` DISABLE KEYS */;
INSERT INTO `pr2_templates` VALUES (1,1,'Projects folder',53,4,'','projects2/template-icons/folder.png',0,NULL,2,1,0,''),(2,1,'Standard project',54,5,'responsible_user_id,date,expenses,income,customer,status,default_distance,contact,budget_fees,travel_costs,reference_no','projects2/template-icons/project.png',1,NULL,1,1,0,'%y-{autoid}');
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
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `time_offset` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT '0',
  `reminder` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `new_template_id` int(11) NOT NULL DEFAULT 0,
  `template_id` int(11) NOT NULL,
  `for_manager` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_pr2_templates_events_pr2_templates1_idx` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `acl_book` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pr2_types`
--

LOCK TABLES `pr2_types` WRITE;
/*!40000 ALTER TABLE `pr2_types` DISABLE KEYS */;
INSERT INTO `pr2_types` VALUES (1,'Default',1,48,49),(2,'Demo',1,118,119);
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
  `menu_slug` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `label` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `display_children` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `target` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_sites`
--

LOCK TABLES `site_sites` WRITE;
/*!40000 ALTER TABLE `site_sites` DISABLE KEYS */;
INSERT INTO `site_sites` VALUES (1,'Default site',1,1579529589,1579529589,'*','defaultsite',0,0,'/','',115,'',49);
/*!40000 ALTER TABLE `site_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smi_certs`
--

DROP TABLE IF EXISTS `smi_certs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smi_certs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cert` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smi_certs`
--

LOCK TABLES `smi_certs` WRITE;
/*!40000 ALTER TABLE `smi_certs` DISABLE KEYS */;
INSERT INTO `smi_certs` VALUES (1,1,'info@nuw.biz','-----BEGIN CERTIFICATE-----\nMIIE5TCCA82gAwIBAgIMOC9gsvqNp2NGiY1pMA0GCSqGSIb3DQEBCwUAMF0xCzAJ\nBgNVBAYTAkJFMRkwFwYDVQQKExBHbG9iYWxTaWduIG52LXNhMTMwMQYDVQQDEypH\nbG9iYWxTaWduIFBlcnNvbmFsU2lnbiAxIENBIC0gU0hBMjU2IC0gRzMwHhcNMTgw\nMjIwMDg1MzExWhcNMjEwMjIwMDg1MzExWjA0MRUwEwYDVQQDDAxpbmZvQG51dy5i\naXoxGzAZBgkqhkiG9w0BCQEWDGluZm9AbnV3LmJpejCCASIwDQYJKoZIhvcNAQEB\nBQADggEPADCCAQoCggEBANo6YqCcdn/TGLrr+hFf800T7mdNvXDI5IFyy6mx3R4q\nk6Qtq5LbkGPQpaGpQweLQabFzDl+bC36jHUf7bbmvsG0VeMxiTpvrni+Qy+9sfCL\nokmBKRHGdR8jC8mb4TTXRbF5nZiwvBG2uTVY55CvXYfEumlhoR21oNje/y3DVF+J\nuTpxJvo/JuKzOrZexFGLBuoo9k+M6aluGK0VqJQACf9j28R1oxPSBX6+qzSv9gd7\nzBj2xHHBLe9e5QYW9Lw+1CA6QL4g6JPPlrmrahhvJvp9DYN9vm3/9qzIs+r6ohU/\nDsY667hCxVi2dav3z6e/yHYSP5UncZMohJtNP/pw3hsCAwEAAaOCAcwwggHIMA4G\nA1UdDwEB/wQEAwIFoDCBngYIKwYBBQUHAQEEgZEwgY4wTQYIKwYBBQUHMAKGQWh0\ndHA6Ly9zZWN1cmUuZ2xvYmFsc2lnbi5jb20vY2FjZXJ0L2dzcGVyc29uYWxzaWdu\nMXNoYTJnM29jc3AuY3J0MD0GCCsGAQUFBzABhjFodHRwOi8vb2NzcDIuZ2xvYmFs\nc2lnbi5jb20vZ3NwZXJzb25hbHNpZ24xc2hhMmczMEwGA1UdIARFMEMwQQYJKwYB\nBAGgMgEoMDQwMgYIKwYBBQUHAgEWJmh0dHBzOi8vd3d3Lmdsb2JhbHNpZ24uY29t\nL3JlcG9zaXRvcnkvMAkGA1UdEwQCMAAwRAYDVR0fBD0wOzA5oDegNYYzaHR0cDov\nL2NybC5nbG9iYWxzaWduLmNvbS9nc3BlcnNvbmFsc2lnbjFzaGEyZzMuY3JsMBcG\nA1UdEQQQMA6BDGluZm9AbnV3LmJpejAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYB\nBQUHAwQwHQYDVR0OBBYEFJUCnqxwm3M6W5Denk8we+ua2ixyMB8GA1UdIwQYMBaA\nFJYnwsKl9xz4Anp6ZK9jbzLSmMKxMA0GCSqGSIb3DQEBCwUAA4IBAQBVl5GVAj2A\nr4E4yqLIvNrnpQJrRyJIszvuVdKBxQJzbhgZMFff4MTdO6IFrlM8GFp2r0nbomcT\nWi/HoJ3pbTvjM7vAGTJ0iErfrKxkaQjaiMCTvx2gSbSBfiWe2l13CjNni055mwq6\nV2aM0H5euMBExhgohcJBmoupQ1+12X4Ur7PX4JjZVYn0CQJZbxE01jXyliMcZTMD\nOdmXgztVPOOpEZ0yH7jOwgWcMzTMeqItT1XS6Nndfi2MrXbY7U4rrqIGIJUvvmW9\nQ42syPiVcHenjmxwkCy1SO2LE7izu9CCdbkH29oJnWfuE2mfO0HfqnGDRvA2el9I\nd8WzFGin+s1m\n-----END CERTIFICATE-----'),(2,1,'p.stirnberg@ruhrmail.de','-----BEGIN CERTIFICATE-----\nMIIFBjCCA+6gAwIBAgIMV189tcXOiR8vAlQZMA0GCSqGSIb3DQEBCwUAMF0xCzAJ\nBgNVBAYTAkJFMRkwFwYDVQQKExBHbG9iYWxTaWduIG52LXNhMTMwMQYDVQQDEypH\nbG9iYWxTaWduIFBlcnNvbmFsU2lnbiAxIENBIC0gU0hBMjU2IC0gRzMwHhcNMTkx\nMjE4MDcxNjQ4WhcNMjAxMjE4MDcxNjQ4WjBKMSAwHgYDVQQDDBdwLnN0aXJuYmVy\nZ0BydWhybWFpbC5kZTEmMCQGCSqGSIb3DQEJARYXcC5zdGlybmJlcmdAcnVocm1h\naWwuZGUwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDEV5RAhBxjaVwf\n9sVdKafmaPRjd0NhXEYWBjY5U/pxLznCam2ujMSepNfp1RIK48cuVUrzYsbzBD+V\nkJgtc7wRFeXF7gw7KEh6U2yEDDa6w7geFeNAO0Y3XqR4tRw1KN0Qk1TV5GcpQKs+\ntVzpL6cFqqKjgz0iDO3TRXOv01RnC8gkbBaXDPG0CELs3Hh6FC7p9tFEyi4IXnJT\nOIb1gnF2xaI4k/vLG3SCYVf12NoJaGuZuS7uMBSG6kKDxvcWKPE1N9OiiCNgMrgY\nkQ/oeIfZjyn5VZ20WVmNweSXZurGroZzfQ+zwFhUHH0jFnkytQ1uqp6nGxQ2J9aZ\ncHOZQU5dAgMBAAGjggHXMIIB0zAOBgNVHQ8BAf8EBAMCBaAwgZ4GCCsGAQUFBwEB\nBIGRMIGOME0GCCsGAQUFBzAChkFodHRwOi8vc2VjdXJlLmdsb2JhbHNpZ24uY29t\nL2NhY2VydC9nc3BlcnNvbmFsc2lnbjFzaGEyZzNvY3NwLmNydDA9BggrBgEFBQcw\nAYYxaHR0cDovL29jc3AyLmdsb2JhbHNpZ24uY29tL2dzcGVyc29uYWxzaWduMXNo\nYTJnMzBMBgNVHSAERTBDMEEGCSsGAQQBoDIBKDA0MDIGCCsGAQUFBwIBFiZodHRw\nczovL3d3dy5nbG9iYWxzaWduLmNvbS9yZXBvc2l0b3J5LzAJBgNVHRMEAjAAMEQG\nA1UdHwQ9MDswOaA3oDWGM2h0dHA6Ly9jcmwuZ2xvYmFsc2lnbi5jb20vZ3NwZXJz\nb25hbHNpZ24xc2hhMmczLmNybDAiBgNVHREEGzAZgRdwLnN0aXJuYmVyZ0BydWhy\nbWFpbC5kZTAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwQwHwYDVR0jBBgw\nFoAUlifCwqX3HPgCenpkr2NvMtKYwrEwHQYDVR0OBBYEFIwHwBjHbPgth+Bvm6hO\nesQ/SrlyMA0GCSqGSIb3DQEBCwUAA4IBAQBg6rzqiUrihEGSb21yWQZBvIFI9va4\nFaAgMH8pXKy5vqTzwftv8F62i/6OsADFdct2XBvoISsMZ98hhnyie3+Mq/jebKUv\naSM5N4E0VyO7cx9OV3c+vstCw5gD3hamGtsZfI0fi0P6bd1A1bfJUjDW1yovBVyu\nR+efytY+LVg5rXHxR1Ttqc5p0GxiyIWSQOlzlsepbsszzfYLJt0skGOdii9+UmMR\naMJC5QdXpB2QiEoOAOWw/v64vvhTqhVpw5D73CnR5Beis98yD1Qhg8RvzBVl+wh/\nQvvjaA9xM23m5NZr/GYYEb+PkPyWIMEDjh9mrvvBt99kv1v+f1BQ3XWd\n-----END CERTIFICATE-----');
/*!40000 ALTER TABLE `smi_certs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `smi_pkcs12`
--

DROP TABLE IF EXISTS `smi_pkcs12`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `smi_pkcs12` (
  `account_id` int(11) NOT NULL,
  `cert` blob DEFAULT NULL,
  `always_sign` tinyint(1) NOT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `smi_pkcs12`
--

LOCK TABLES `smi_pkcs12` WRITE;
/*!40000 ALTER TABLE `smi_pkcs12` DISABLE KEYS */;
/*!40000 ALTER TABLE `smi_pkcs12` ENABLE KEYS */;
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
  `title` varchar(50) DEFAULT NULL,
  `content` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `due_time` (`due_time`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_announcements`
--

LOCK TABLES `su_announcements` WRITE;
/*!40000 ALTER TABLE `su_announcements` DISABLE KEYS */;
INSERT INTO `su_announcements` VALUES (1,1,116,0,1579529589,1579529589,'Submit support ticket','Anyone can submit tickets to the support system here:<br /><br /><a href=\"http://localhost:6280/modules/site/index.php?r=tickets/externalpage/newTicket\">http://localhost:6280/modules/site/index.php?r=tickets/externalpage/newTicket</a><br /><br />Anonymous ticket posting can be disabled in the ticket module settings.'),(2,1,117,0,1579529589,1579529589,'Welcome to Group-Office','This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!');
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_latest_read_announcement_records`
--

LOCK TABLES `su_latest_read_announcement_records` WRITE;
/*!40000 ALTER TABLE `su_latest_read_announcement_records` DISABLE KEYS */;
INSERT INTO `su_latest_read_announcement_records` VALUES (1,2,1579529589),(2,2,1579529589),(3,2,1579529589);
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
  `text` text DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_notes`
--

LOCK TABLES `su_notes` WRITE;
/*!40000 ALTER TABLE `su_notes` DISABLE KEYS */;
INSERT INTO `su_notes` VALUES (1,NULL),(2,NULL),(3,NULL);
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
  `title` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `summary` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `su_visible_calendars`
--

LOCK TABLES `su_visible_calendars` WRITE;
/*!40000 ALTER TABLE `su_visible_calendars` DISABLE KEYS */;
INSERT INTO `su_visible_calendars` VALUES (1,1),(2,2),(3,3),(4,4);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_addressbook_user`
--

LOCK TABLES `sync_addressbook_user` WRITE;
/*!40000 ALTER TABLE `sync_addressbook_user` DISABLE KEYS */;
INSERT INTO `sync_addressbook_user` VALUES (1,1,1),(5,2,1),(6,3,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_calendar_user`
--

LOCK TABLES `sync_calendar_user` WRITE;
/*!40000 ALTER TABLE `sync_calendar_user` DISABLE KEYS */;
INSERT INTO `sync_calendar_user` VALUES (1,1,1),(2,2,1),(3,3,1);
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
  `manufacturer` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `software_version` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `uri` varchar(128) DEFAULT NULL,
  `UTC` enum('0','1') NOT NULL,
  `vcalendar_version` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_note_categories_user`
--

LOCK TABLES `sync_note_categories_user` WRITE;
/*!40000 ALTER TABLE `sync_note_categories_user` DISABLE KEYS */;
INSERT INTO `sync_note_categories_user` VALUES (1,1,1),(2,2,1),(3,3,1);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_settings`
--

LOCK TABLES `sync_settings` WRITE;
/*!40000 ALTER TABLE `sync_settings` DISABLE KEYS */;
INSERT INTO `sync_settings` VALUES (1,0,0,0,0,1,1,0,0),(2,0,0,0,0,0,1,0,0),(3,0,0,0,0,2,1,0,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sync_tasklist_user`
--

LOCK TABLES `sync_tasklist_user` WRITE;
/*!40000 ALTER TABLE `sync_tasklist_user` DISABLE KEYS */;
INSERT INTO `sync_tasklist_user` VALUES (1,1,1),(2,2,1),(3,3,1);
/*!40000 ALTER TABLE `sync_tasklist_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ta_categories`
--

DROP TABLE IF EXISTS `ta_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ta_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_portlet_tasklists`
--

LOCK TABLES `ta_portlet_tasklists` WRITE;
/*!40000 ALTER TABLE `ta_portlet_tasklists` DISABLE KEYS */;
INSERT INTO `ta_portlet_tasklists` VALUES (1,1),(2,2),(3,3),(4,4);
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
  `reminder_time` varchar(10) NOT NULL DEFAULT '0',
  `remind` tinyint(1) NOT NULL DEFAULT 0,
  `default_tasklist_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_settings`
--

LOCK TABLES `ta_settings` WRITE;
/*!40000 ALTER TABLE `ta_settings` DISABLE KEYS */;
INSERT INTO `ta_settings` VALUES (1,0,'0',0,1),(2,0,'0',0,2),(3,0,'0',0,3),(4,0,'0',0,4);
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
  `name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `version` int(10) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasklists`
--

LOCK TABLES `ta_tasklists` WRITE;
/*!40000 ALTER TABLE `ta_tasklists` DISABLE KEYS */;
INSERT INTO `ta_tasklists` VALUES (1,'System Administrator',1,72,16,3),(2,'Elmer Fudd',2,96,30,3),(3,'Demo User',3,102,35,3),(4,'Linda Smith',4,108,40,3);
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
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `repeat_end_time` int(11) NOT NULL DEFAULT 0,
  `reminder` int(11) NOT NULL DEFAULT 0,
  `rrule` varchar(100) NOT NULL DEFAULT '',
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1,
  `percentage_complete` tinyint(4) NOT NULL DEFAULT 0,
  `project_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `list_id` (`tasklist_id`),
  KEY `rrule` (`rrule`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ta_tasks`
--

LOCK TABLES `ta_tasks` WRITE;
/*!40000 ALTER TABLE `ta_tasks` DISABLE KEYS */;
INSERT INTO `ta_tasks` VALUES (1,'8cd99b3a-8113-50ab-bfeb-ad63eea9eb74',3,1,1579529588,1579529588,1,1579529588,1579702388,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(2,'a16aa03d-483e-5344-afd7-bb0d523b99b6',4,1,1579529588,1579529588,1,1579529588,1579615988,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(3,'4ea7fe25-b4bd-57be-8582-8bcc7bf412ac',2,1,1579529588,1579529588,1,1579529588,1579615988,0,'Feed the dog',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(4,'482d669b-349a-5b13-9f1e-ca44ed4d56d1',3,1,1579529588,1579529588,1,1579529588,1579615988,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(5,'b46a3177-9d96-503d-be2d-267e6f38ce56',4,1,1579529588,1579529588,1,1579529588,1579615988,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(6,'948c2faf-30b7-51fb-9050-d59f9787cf7b',2,1,1579529588,1579529588,1,1579529588,1579615988,0,'Prepare meeting',NULL,'NEEDS-ACTION',0,0,'',0,0,1,0,0),(7,'7a6ca641-3dae-5eed-a5fc-77d39f13debc',1,1,1579529588,1579529588,1,1579788788,1579788788,0,'Call: Smith Inc (Q20000001)','','NEEDS-ACTION',0,1579788788,'',0,0,1,0,0),(8,'78218b4f-7698-5b94-b38f-5d4b51ba512a',1,1,1579529588,1579529588,1,1579788788,1579788788,0,'Call: ACME Corporation (Q20000002)','','NEEDS-ACTION',0,1579788788,'',0,0,1,0,0);
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
  `name` varchar(50) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `content` text DEFAULT NULL,
  `attachments` varchar(500) NOT NULL DEFAULT '',
  `is_note` tinyint(1) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `rate_id` int(11) NOT NULL DEFAULT 0,
  `rate_amount` double NOT NULL DEFAULT 0,
  `rate_hours` double NOT NULL DEFAULT 0,
  `rate_name` varchar(50) NOT NULL DEFAULT '',
  `rate_cost_code` varchar(50) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_messages`
--

LOCK TABLES `ti_messages` WRITE;
/*!40000 ALTER TABLE `ti_messages` DISABLE KEYS */;
INSERT INTO `ti_messages` VALUES (1,1,0,1,0,0,'My rocket always circles back right at me? How do I aim right?','',0,0,1579529588,1579529588,0,0,0,'',NULL,NULL),(2,1,0,1,0,0,'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.','',1,2,1579529588,1579529588,0,0,0,'',NULL,NULL),(3,1,-1,1,1,0,'Gee I don\'t know how that can happen. I\'ll send you some new ones!','',0,2,1579529588,1579529588,0,0,0,'',NULL,NULL),(4,2,0,1,0,0,'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?','',0,0,1579356788,1579356788,0,0,0,'',NULL,NULL),(5,2,0,1,0,0,'Please respond faster. Can\'t you see this ticket is marked in red?','',0,0,1579529588,1579529588,0,0,0,'',NULL,NULL),(6,2,0,1,0,0,'Assigned to: Administrator, System','',1,1,1579601715,1579601715,0,0,0,'',NULL,NULL),(7,2,0,1,0,0,'yiouyhio','',0,1,1579601719,1579601719,0,0,0,'',NULL,1),(8,3,0,2,0,0,'Test from demo','',0,3,1598357578,1598357578,0,0,0,'',NULL,0),(9,3,0,2,0,0,'dgfdsfdsf','',0,3,1598357606,1598357606,0,0,0,'',NULL,1),(11,6,0,2,0,0,'t3','',0,3,1598357969,1598357969,0,0,0,'',NULL,0),(12,6,0,2,0,0,'t4','',0,3,1598357977,1598357977,0,0,0,'',NULL,1),(13,2,0,1,0,0,'No cc???','',0,1,1600072446,1600072446,0,0,0,'',NULL,1),(14,2,0,1,0,0,'ccc????','',0,1,1600072580,1600072580,0,0,0,'',NULL,1),(15,6,0,2,0,0,'Assigned to: Administrator, System','',1,1,1600072643,1600072643,0,0,0,'',NULL,NULL),(16,6,0,2,0,0,'dfewfwefew','',0,1,1600072650,1600072650,0,0,0,'',NULL,1);
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
  `name` varchar(50) NOT NULL,
  `amount` double NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
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
  `from_email` varchar(100) NOT NULL,
  `from_name` varchar(100) NOT NULL,
  `use_alternative_url` tinyint(1) NOT NULL DEFAULT 0,
  `alternative_url` varchar(100) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL,
  `default_type` int(11) NOT NULL DEFAULT 0,
  `logo` varchar(50) NOT NULL,
  `customer_message` text NOT NULL,
  `response_message` text NOT NULL,
  `notify_contact` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(20) DEFAULT NULL,
  `expire_days` int(11) NOT NULL DEFAULT 0,
  `never_close_status_id` int(11) DEFAULT NULL,
  `disable_reminder_assigned` tinyint(1) NOT NULL DEFAULT 0,
  `disable_reminder_unanswered` tinyint(1) NOT NULL DEFAULT 0,
  `enable_external_page` tinyint(1) NOT NULL DEFAULT 0,
  `allow_anonymous` tinyint(1) NOT NULL DEFAULT 0,
  `external_page_css` text DEFAULT NULL,
  `leave_type_blank_by_default` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket_msg` text DEFAULT NULL,
  `assigned_to` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_to_msg` text DEFAULT NULL,
  `notify_agent` tinyint(1) NOT NULL DEFAULT 0,
  `notify_agent_msg` text DEFAULT NULL,
  `notify_due_date` tinyint(1) NOT NULL DEFAULT 0,
  `notify_due_date_msg` text DEFAULT NULL,
  `manager_reopen_ticket_only` tinyint(1) NOT NULL DEFAULT 0,
  `show_close_confirm` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_settings`
--

LOCK TABLES `ti_settings` WRITE;
/*!40000 ALTER TABLE `ti_settings` DISABLE KEYS */;
INSERT INTO `ti_settings` VALUES (1,'admin@intermesh.localhost','Customer Support',1,'http://localhost:6280/modules/site/index.php?r=tickets/externalpage/ticket','{SUBJECT}',1,'groupoffice.png','This is our support system. Please enter your contact information and describe your problem.','Thank you for contacting us. We have received your question and created a ticket for you. we will respond as soon as possible. For future reference, your question has been assigned the following ticket number: {TICKET_NUMBER}.',0,'en',0,NULL,0,0,1,1,NULL,0,0,NULL,0,NULL,0,NULL,0,NULL,0,0);
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
  `name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
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
  `name` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `autoreply` tinyint(1) NOT NULL DEFAULT 0,
  `default_template` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_created_for_client` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_mail_for_agent` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_claim_notification` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
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
  `ticket_number` varchar(16) DEFAULT NULL,
  `ticket_verifier` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `agent_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `company` varchar(100) NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT 0,
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `phone` varchar(50) NOT NULL DEFAULT '',
  `subject` varchar(100) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `unseen` int(1) NOT NULL DEFAULT 1,
  `group_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `last_response_time` int(11) NOT NULL DEFAULT 0,
  `cc_addresses` varchar(1024) NOT NULL DEFAULT '',
  `cuser_id` int(11) NOT NULL DEFAULT 0,
  `due_date` int(11) DEFAULT NULL,
  `due_reminder_sent` tinyint(1) NOT NULL DEFAULT 0,
  `last_agent_response_time` int(11) NOT NULL DEFAULT 0,
  `last_contact_response_time` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `user_id` (`user_id`),
  KEY `status_id` (`status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_tickets`
--

LOCK TABLES `ti_tickets` WRITE;
/*!40000 ALTER TABLE `ti_tickets` DISABLE KEYS */;
INSERT INTO `ti_tickets` VALUES (1,'202000001',40404453,1,-1,1,1,0,3,'ACME Corporation',2,'Wile','E.','Coyote','wile@acme.demo','','Malfunctioning rockets',1579529588,1579529588,1,0,0,0,0,1579529588,'',1,NULL,0,1579529588,1579529588),(2,'202000002',51330507,1,0,1,1,1,3,'ACME Corporation',2,'Wile','E.','Coyote','admin@intermesh.localhost','','Can I speed up my rockets?',1579356788,1600072580,1,0,0,0,0,1600072580,'test@intermesh.localhost',1,NULL,0,1600072580,1579529588),(3,'202000003',88532598,1,0,2,3,0,5,'ACME Rocket Powered Products',3,'Demo','','User','demo@acmerpp.demo','','Test from demo',1598357578,1598357606,3,0,1,0,0,1598357606,'test@intermesh.localhost',3,NULL,0,1598357578,1598357606),(6,'202000006',50754012,1,0,2,3,1,5,'ACME Rocket Powered Products',3,'Demo','','User','demo@acmerpp.demo','','t3',1598357969,1600072650,1,0,0,0,0,1600072650,'test@intermesh.localhost',3,NULL,0,1600072650,1598357977);
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
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `show_statuses` varchar(100) DEFAULT NULL,
  `show_from_others` tinyint(1) NOT NULL DEFAULT 0,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `email_on_new` text DEFAULT NULL,
  `email_to_agent` tinyint(1) NOT NULL DEFAULT 0,
  `custom_sender_field` tinyint(1) NOT NULL DEFAULT 0,
  `sender_name` varchar(64) DEFAULT NULL,
  `sender_email` varchar(128) DEFAULT NULL,
  `publish_on_site` tinyint(1) NOT NULL DEFAULT 0,
  `type_group_id` int(11) DEFAULT NULL,
  `email_account_id` int(11) NOT NULL DEFAULT 0,
  `enable_templates` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket` tinyint(1) NOT NULL DEFAULT 0,
  `new_ticket_msg` text DEFAULT NULL,
  `assigned_to` tinyint(1) NOT NULL DEFAULT 0,
  `assigned_to_msg` text DEFAULT NULL,
  `notify_agent` tinyint(1) NOT NULL DEFAULT 0,
  `notify_agent_msg` text DEFAULT NULL,
  `search_cache_acl_id` int(11) NOT NULL DEFAULT 0,
  `email_on_new_msg` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ti_types`
--

LOCK TABLES `ti_types` WRITE;
/*!40000 ALTER TABLE `ti_types` DISABLE KEYS */;
INSERT INTO `ti_types` VALUES (1,'IT',NULL,1,62,NULL,0,7,NULL,0,0,NULL,NULL,1,0,0,0,0,NULL,0,NULL,0,NULL,75,NULL),(2,'Sales',NULL,1,63,NULL,0,8,NULL,0,0,NULL,NULL,0,0,0,0,0,NULL,0,NULL,0,NULL,76,NULL);
/*!40000 ALTER TABLE `ti_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_action_types`
--

DROP TABLE IF EXISTS `wf_action_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_action_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT 'Approve only',
  `class_name` varchar(64) NOT NULL DEFAULT 'GO_Workflow_Action_Approve',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_action_types`
--

LOCK TABLES `wf_action_types` WRITE;
/*!40000 ALTER TABLE `wf_action_types` DISABLE KEYS */;
INSERT INTO `wf_action_types` VALUES (1,'Approve only','GO\\Workflow\\Action\\Approve'),(2,'Approve, then Copy / Move file','GO\\Workflow\\Action\\Copy'),(3,'Approve, then Workflow history in PDF','GO\\Workflow\\Action\\HistoryPdf'),(4,'Approve, then Workflow history in copy PDF','GO\\Workflow\\Action\\HistoryPdfInCopy');
/*!40000 ALTER TABLE `wf_action_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_approvers`
--

DROP TABLE IF EXISTS `wf_approvers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_approvers` (
  `step_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_approvers`
--

LOCK TABLES `wf_approvers` WRITE;
/*!40000 ALTER TABLE `wf_approvers` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_approvers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_approvers_groups`
--

DROP TABLE IF EXISTS `wf_approvers_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_approvers_groups` (
  `step_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_approvers_groups`
--

LOCK TABLES `wf_approvers_groups` WRITE;
/*!40000 ALTER TABLE `wf_approvers_groups` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_approvers_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_models`
--

DROP TABLE IF EXISTS `wf_models`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_models` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `process_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `due_time` int(11) NOT NULL,
  `shift_due_time` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_models`
--

LOCK TABLES `wf_models` WRITE;
/*!40000 ALTER TABLE `wf_models` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_models` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_processes`
--

DROP TABLE IF EXISTS `wf_processes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_processes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_processes`
--

LOCK TABLES `wf_processes` WRITE;
/*!40000 ALTER TABLE `wf_processes` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_processes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_required_approvers`
--

DROP TABLE IF EXISTS `wf_required_approvers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_required_approvers` (
  `process_model_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `reminder_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`process_model_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_required_approvers`
--

LOCK TABLES `wf_required_approvers` WRITE;
/*!40000 ALTER TABLE `wf_required_approvers` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_required_approvers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_step_history`
--

DROP TABLE IF EXISTS `wf_step_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_step_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_model_id` int(11) NOT NULL,
  `step_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_step_history`
--

LOCK TABLES `wf_step_history` WRITE;
/*!40000 ALTER TABLE `wf_step_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_step_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_steps`
--

DROP TABLE IF EXISTS `wf_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_steps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `process_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `due_in` int(11) NOT NULL,
  `email_alert` tinyint(1) NOT NULL DEFAULT 0,
  `popup_alert` tinyint(1) NOT NULL DEFAULT 0,
  `all_must_approve` tinyint(1) NOT NULL DEFAULT 0,
  `action_type_id` int(11) NOT NULL DEFAULT 0,
  `copy_to_folder` varchar(256) DEFAULT NULL,
  `keep_original_copy` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_steps`
--

LOCK TABLES `wf_steps` WRITE;
/*!40000 ALTER TABLE `wf_steps` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wf_triggers`
--

DROP TABLE IF EXISTS `wf_triggers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wf_triggers` (
  `model_type_id` int(11) NOT NULL,
  `model_attribute` varchar(100) NOT NULL DEFAULT '',
  `model_attribute_value` varchar(100) NOT NULL DEFAULT '',
  `process_id` int(11) NOT NULL,
  PRIMARY KEY (`model_type_id`,`model_attribute`,`model_attribute_value`,`process_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wf_triggers`
--

LOCK TABLES `wf_triggers` WRITE;
/*!40000 ALTER TABLE `wf_triggers` DISABLE KEYS */;
/*!40000 ALTER TABLE `wf_triggers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-02-09  8:31:31
