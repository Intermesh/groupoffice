-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Nov 27, 2020 at 12:02 PM
-- Server version: 10.5.4-MariaDB-1:10.5.4+maria~focal
-- PHP Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `groupoffice_phpunit`
--

-- --------------------------------------------------------

--
-- Table structure for table `abr_relation`
--

CREATE TABLE `abr_relation` (
  `id` int(11) NOT NULL,
  `parent_model_type_id` int(11) NOT NULL,
  `parent_model_id` int(11) NOT NULL,
  `child_model_type_id` int(11) NOT NULL,
  `child_model_id` int(11) NOT NULL,
  `relationgroup_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abr_relation`
--

INSERT INTO `abr_relation` (`id`, `parent_model_type_id`, `parent_model_id`, `child_model_type_id`, `child_model_id`, `relationgroup_id`) VALUES
(1, 4, 1, 4, 7, 2),
(2, 4, 1, 4, 1, 4),
(3, 4, 1, 4, 34, 2),
(8, 4, 1, 4, 33, 2),
(9, 4, 34, 4, 185, 3),
(10, 4, 63, 4, 63, 4),
(11, 4, 64, 4, 64, 4),
(12, 4, 64, 4, 73, 2),
(13, 4, 64, 4, 66, 2),
(14, 4, 64, 4, 65, 2),
(15, 4, 64, 4, 74, 2),
(17, 4, 71, 4, 85, 2),
(20, 4, 71, 4, 39, 2),
(21, 4, 71, 4, 41, 2),
(22, 4, 71, 4, 42, 2),
(23, 4, 71, 4, 75, 2),
(25, 4, 71, 4, 52, 2),
(26, 4, 71, 4, 76, 2),
(27, 4, 71, 4, 77, 2),
(28, 4, 71, 4, 53, 2),
(29, 4, 71, 4, 46, 2),
(31, 4, 71, 4, 79, 2),
(32, 4, 71, 4, 80, 2),
(33, 4, 71, 4, 81, 2),
(34, 4, 71, 4, 82, 2),
(35, 4, 71, 4, 83, 2),
(36, 4, 71, 4, 84, 2),
(37, 4, 71, 4, 86, 2),
(38, 4, 71, 4, 87, 2),
(39, 4, 71, 4, 50, 2),
(40, 4, 71, 4, 51, 2),
(41, 4, 71, 4, 67, 2),
(42, 4, 71, 4, 90, 2),
(43, 4, 71, 4, 47, 2),
(67, 4, 10, 4, 10, 2),
(70, 4, 68, 4, 68, 4),
(71, 4, 68, 4, 70, 2),
(73, 4, 68, 4, 11, 2),
(74, 4, 68, 4, 10, 2),
(75, 4, 68, 4, 12, 2),
(76, 4, 68, 4, 31, 2),
(77, 4, 68, 4, 13, 2),
(78, 4, 70, 4, 14, 3),
(79, 4, 70, 4, 95, 3),
(80, 4, 70, 4, 15, 3),
(81, 4, 70, 4, 96, 3),
(82, 4, 70, 4, 16, 3),
(83, 4, 70, 4, 97, 3),
(84, 4, 70, 4, 18, 3),
(85, 4, 70, 4, 19, 3),
(86, 4, 70, 4, 17, 3),
(87, 4, 70, 4, 21, 3),
(88, 4, 70, 4, 22, 3),
(89, 4, 70, 4, 23, 3),
(90, 4, 70, 4, 98, 3),
(91, 4, 70, 4, 26, 3),
(93, 4, 70, 4, 99, 3),
(94, 4, 70, 4, 24, 3),
(96, 4, 70, 4, 70, 2),
(97, 4, 70, 4, 27, 3),
(98, 4, 10, 4, 32, 3),
(99, 4, 10, 4, 100, 3),
(100, 4, 10, 4, 101, 3),
(101, 4, 10, 4, 102, 3),
(102, 4, 10, 4, 103, 3),
(103, 4, 10, 4, 104, 3),
(104, 4, 10, 4, 105, 3),
(105, 4, 10, 4, 106, 3),
(106, 4, 10, 4, 107, 3),
(107, 4, 10, 4, 108, 3),
(108, 4, 10, 4, 109, 3),
(109, 4, 10, 4, 110, 3),
(110, 4, 11, 4, 11, 2),
(111, 4, 11, 4, 186, 3),
(112, 4, 11, 4, 211, 3),
(113, 4, 11, 4, 194, 3),
(114, 4, 11, 4, 111, 3),
(115, 4, 11, 4, 28, 3),
(116, 4, 11, 4, 112, 3),
(117, 4, 11, 4, 113, 3),
(118, 4, 11, 4, 114, 3),
(119, 4, 11, 4, 115, 3),
(120, 4, 11, 4, 29, 3),
(121, 4, 12, 4, 12, 2),
(122, 4, 12, 4, 116, 3),
(123, 4, 12, 4, 117, 3),
(124, 4, 12, 4, 118, 3),
(125, 4, 12, 4, 119, 3),
(126, 4, 12, 4, 120, 3),
(127, 4, 12, 4, 121, 3),
(128, 4, 12, 4, 122, 3),
(129, 4, 12, 4, 123, 3),
(130, 4, 70, 4, 25, 3),
(131, 4, 189, 4, 189, 4),
(133, 4, 189, 4, 57, 2),
(134, 4, 189, 4, 3, 2),
(141, 4, 71, 4, 71, 4),
(142, 4, 70, 4, 20, 3),
(143, 4, 1, 4, 179, 2),
(145, 4, 63, 4, 216, 2),
(146, 4, 0, 4, 63, 4),
(147, 4, 0, 4, 63, 4),
(152, 4, 189, 4, 171, 2),
(154, 4, 189, 4, 197, 2),
(155, 4, 70, 4, 219, 3),
(157, 4, 52, 4, 208, 3),
(158, 4, 52, 4, 203, 3),
(159, 4, 52, 4, 204, 3),
(160, 4, 52, 4, 205, 3),
(161, 4, 52, 4, 206, 3),
(162, 4, 52, 4, 207, 3),
(163, 4, 52, 4, 209, 3),
(165, 2, 0, 4, 129, 3),
(168, 4, 189, 4, 227, 2),
(169, 4, 227, 4, 61, 3),
(170, 4, 227, 4, 129, 3),
(171, 4, 227, 4, 228, 3),
(172, 4, 227, 4, 169, 3),
(173, 4, 227, 4, 223, 3),
(174, 4, 71, 4, 174, 2),
(175, 4, 71, 4, 45, 2),
(176, 4, 71, 4, 91, 2),
(177, 4, 71, 4, 166, 2),
(178, 4, 71, 4, 92, 2),
(179, 4, 71, 4, 93, 2),
(180, 4, 71, 4, 44, 2),
(181, 4, 71, 4, 231, 2),
(182, 4, 71, 4, 191, 2),
(183, 4, 71, 4, 48, 2),
(184, 4, 71, 4, 49, 2),
(185, 4, 71, 4, 172, 2),
(186, 4, 71, 4, 192, 2),
(187, 4, 71, 4, 170, 2),
(188, 4, 71, 4, 181, 2),
(189, 4, 71, 4, 232, 2),
(190, 4, 71, 4, 93, 2),
(191, 4, 71, 4, 78, 2),
(192, 4, 71, 4, 94, 2),
(193, 4, 71, 4, 233, 2),
(194, 4, 71, 4, 234, 2),
(195, 4, 71, 4, 54, 2),
(196, 4, 71, 4, 55, 2),
(197, 4, 34, 4, 235, 3),
(198, 4, 236, 4, 236, 4),
(199, 4, 64, 4, 238, 2),
(200, 4, 64, 4, 237, 2),
(201, 4, 64, 4, 239, 2),
(202, 4, 10, 4, 240, 3),
(203, 4, 11, 4, 241, 3),
(204, 4, 11, 4, 242, 3),
(205, 4, 11, 4, 243, 3),
(206, 4, 11, 4, 244, 3),
(207, 4, 11, 4, 188, 3),
(208, 4, 13, 4, 190, 3),
(209, 4, 189, 4, 56, 2),
(210, 4, 13, 4, 245, 3),
(211, 4, 189, 4, 250, 2),
(212, 4, 250, 4, 58, 3),
(213, 4, 250, 4, 59, 3),
(214, 4, 250, 4, 247, 3),
(215, 4, 250, 4, 187, 3),
(216, 4, 250, 4, 248, 3),
(217, 4, 250, 4, 249, 3),
(218, 4, 250, 4, 62, 3),
(219, 4, 197, 4, 252, 3),
(220, 4, 197, 4, 253, 3),
(221, 4, 197, 4, 251, 3),
(222, 4, 171, 4, 255, 3),
(223, 4, 171, 4, 254, 3),
(225, 4, 3, 4, 196, 3),
(226, 2, 0, 4, 256, 2),
(227, 4, 64, 4, 256, 2),
(228, 4, 38, 4, 38, 4),
(229, 4, 64, 4, 259, 2),
(230, 4, 7, 4, 35, 3),
(231, 4, 71, 4, 261, 2),
(232, 4, 64, 4, 263, 2),
(233, 4, 265, 4, 265, 4);

-- --------------------------------------------------------

--
-- Table structure for table `abr_relationgroup`
--

CREATE TABLE `abr_relationgroup` (
  `id` int(11) NOT NULL,
  `parent_label` varchar(255) NOT NULL,
  `child_label` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `abr_relationgroup`
--

INSERT INTO `abr_relationgroup` (`id`, `parent_label`, `child_label`) VALUES
(2, 'Tier 1', 'Tier 2'),
(3, 'Tier 2', 'Tier 3'),
(4, 'Sony', 'Tier 1');

-- --------------------------------------------------------

--
-- Table structure for table `ab_addressbooks`
--

CREATE TABLE `ab_addressbooks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `default_salutation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `users` tinyint(1) NOT NULL DEFAULT 0,
  `create_folder` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_addressbooks`
--

INSERT INTO `ab_addressbooks` (`id`, `user_id`, `name`, `acl_id`, `default_salutation`, `files_folder_id`, `users`, `create_folder`) VALUES
(1, 1, 'Prospects', 15, 'Dear {first_name}', 39, 0, 0),
(2, 1, 'Suppliers', 16, 'Dear {first_name}', 0, 0, 0),
(3, 1, 'Customers', 17, 'Dear {first_name}', 0, 0, 0),
(4, 1, 'Users', 86, 'Dear {first_name}', 18, 1, 0),
(5, 2, 'Elmer Fudd', 89, 'Dear {first_name}', 21, 0, 0),
(6, 3, 'Demo User', 94, 'Dear {first_name}', 26, 0, 0),
(7, 4, 'Linda Smith', 99, 'Dear {first_name}', 30, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ab_addresslists`
--

CREATE TABLE `ab_addresslists` (
  `id` int(11) NOT NULL,
  `addresslist_group_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_salutation` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_addresslists`
--

INSERT INTO `ab_addresslists` (`id`, `addresslist_group_id`, `user_id`, `acl_id`, `name`, `default_salutation`, `ctime`, `mtime`) VALUES
(1, NULL, 1, 119, 'Newsletter', 'Geachte heer/mevrouw', 1562610294, 1562610294),
(2, NULL, 1, 120, 'Release notes', 'Geachte heer/mevrouw', 1562610317, 1562610336);

-- --------------------------------------------------------

--
-- Table structure for table `ab_addresslist_companies`
--

CREATE TABLE `ab_addresslist_companies` (
  `addresslist_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_addresslist_companies`
--

INSERT INTO `ab_addresslist_companies` (`addresslist_id`, `company_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `ab_addresslist_contacts`
--

CREATE TABLE `ab_addresslist_contacts` (
  `addresslist_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_addresslist_contacts`
--

INSERT INTO `ab_addresslist_contacts` (`addresslist_id`, `contact_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(2, 1),
(2, 2),
(2, 3),
(2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `ab_addresslist_group`
--

CREATE TABLE `ab_addresslist_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_companies`
--

CREATE TABLE `ab_companies` (
  `id` int(11) NOT NULL,
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
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_companies`
--

INSERT INTO `ab_companies` (`id`, `link_id`, `user_id`, `addressbook_id`, `name`, `name2`, `address`, `address_no`, `latitude`, `longitude`, `zip`, `city`, `state`, `country`, `post_address`, `post_address_no`, `post_latitude`, `post_longitude`, `post_city`, `post_state`, `post_country`, `post_zip`, `phone`, `fax`, `email`, `homepage`, `comment`, `bank_no`, `bank_bic`, `vat_no`, `invoice_email`, `ctime`, `mtime`, `muser_id`, `email_allowed`, `files_folder_id`, `crn`, `iban`, `photo`, `color`) VALUES
(1, NULL, 1, 3, 'Smith Inc', '', 'Kalverstraat', '1', NULL, NULL, '1012 NX', 'Amsterdam', 'Noord-Holland', 'NL', 'Kalverstraat', '1', NULL, NULL, 'Amsterdam', 'Noord-Brabant', 'NL', '1012 NX', '+31 (0) 10 - 1234567', '+31 (0) 1234567', 'info@smith.demo', 'http://www.smith.demo', 'Just a demo company', '', '', 'NL 1234.56.789.B01', '', 1561972053, 1561972053, 1, 1, 0, '', '', '', 'F4F4F4'),
(2, NULL, 1, 3, 'ACME Corporation', '', '1111 Broadway', '', NULL, NULL, '10019', 'New York', 'NY', 'US', '1111 Broadway', '', NULL, NULL, 'New York', 'NY', 'US', '10019', '(555) 123-4567', '(555) 123-4567', 'info@acme.demo', 'http://www.acme.demo', 'The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]', '', '', 'US 1234.56.789.B01', '', 1561972054, 1561972054, 1, 1, 0, '', '', '', '000000'),
(3, NULL, 1, 4, 'ACME Rocket Powered Products', '', '1111 Broadway', '', NULL, NULL, '10019', 'New York', 'NY', 'US', '1111 Broadway', '', NULL, NULL, 'New York', 'NY', 'US', '10019', '(555) 123-4567', '(555) 123-4567', 'info@acmerpp.demo', 'http://www.acmerpp.demo', 'The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]', '', '', 'US 1234.56.789.B01', '', 1561972055, 1561972055, 1, 1, 0, '', '', '', '000000'),
(4, NULL, 1, 10000, 'Orphaned Company', '', 'Kalverstraat', '1', NULL, NULL, '1012 NX', 'Amsterdam', 'Noord-Holland', 'NL', 'Kalverstraat', '1', NULL, NULL, 'Amsterdam', 'Noord-Brabant', 'NL', '1012 NX', '+31 (0) 10 - 1234567', '+31 (0) 1234567', 'info@smith.demo', 'http://www.smith.demo', 'Just a demo company', '', '', 'NL 1234.56.789.B01', '', 1561972053, 1561972053, 1, 1, 0, '', '', '', '000000');

-- --------------------------------------------------------

--
-- Table structure for table `ab_contacts`
--

CREATE TABLE `ab_contacts` (
  `id` int(11) NOT NULL,
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
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '000000'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_contacts`
--

INSERT INTO `ab_contacts` (`id`, `uuid`, `user_id`, `addressbook_id`, `first_name`, `middle_name`, `last_name`, `initials`, `title`, `suffix`, `sex`, `birthday`, `email`, `email2`, `email3`, `company_id`, `department`, `function`, `home_phone`, `work_phone`, `fax`, `work_fax`, `cellular`, `cellular2`, `homepage`, `country`, `state`, `city`, `zip`, `address`, `address_no`, `latitude`, `longitude`, `comment`, `ctime`, `mtime`, `muser_id`, `salutation`, `email_allowed`, `files_folder_id`, `go_user_id`, `photo`, `action_date`, `url_linkedin`, `url_facebook`, `url_twitter`, `skype_name`, `color`) VALUES
(1, '04d1b2d9-f7ec-531d-b58e-ad314c70ec56', 1, 3, 'John', '', 'Smith', '', '', '', 'M', NULL, 'john@smith.demo', '', '', 1, 'Management', 'CEO', '', '', '', '', '06-12345678', '', '', 'NL', 'Noord-Holland', 'Amsterdam', '1012 NX', 'Kalverstraat', '1', NULL, NULL, '', 1561972053, 1570702804, 1, 'Dear Mr. Smith', 1, 46, 0, 'addressbook/photos/3/con_1.jpg', 0, 'http://www.linkedin.com', 'http://www.facebook.com', 'http://www.twitter.com', 'echo123', 'A3A3A3'),
(2, 'c1042689-977c-5ed9-b4bd-c7a75d8c9eb4', 1, 3, 'Wile', 'E.', 'Coyote', '', '', '', 'M', NULL, 'wile@acme.demo', '', '', 2, '', 'CEO', '', '', '', '', '06-12345678', '', '', 'US', 'NY', 'New York', '10019', '1111 Broadway', '', NULL, NULL, '', 1561972054, 1563544477, 1, 'Dear Mr. Coyote', 1, 17, 0, 'addressbook/photos/3/con_2.jpg', 0, 'http://www.linkedin.com', 'http://www.facebook.com', 'http://www.twitter.com', 'test', '000000'),
(3, 'afa6fc35-4b0d-501f-afe5-329fe1f74370', 1, 4, 'System', '', 'Administrator', '', '', '', 'M', NULL, 'admin@intermesh.localhost', '', '', 3, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, '', 1561972055, 1571047810, 1, 'Dear System', 1, 0, 1, '', 0, '', '', '', '', '000000'),
(4, 'f304612d-11ad-5fe2-ac2c-022d6a613472', 1, 4, 'Elmer', '', 'Fudd', '', '', '', 'M', NULL, 'elmer@group-office.com', '', '', 3, '', 'CEO', '', '', '', '', '06-12345678', '', NULL, 'US', 'NY', 'New York', '10019', '1111 Broadway', '', NULL, NULL, NULL, 1561972055, 1562657999, 1, 'Dear Elmer', 1, 0, 2, '4.jpg', 0, NULL, NULL, NULL, NULL, '000000'),
(5, 'd6000469-ad98-5256-bd93-cb653cdde744', 1, 4, 'Demo', '', 'User', '', '', '', 'M', NULL, 'demo@acmerpp.demo', 'demo@group-office.com', '', 3, '', 'CEO', '', '', '', '', '06-12345678', '', NULL, 'US', 'NY', 'New York', '10019', '1111 Broadway', '', NULL, NULL, NULL, 1561972056, 1562249536, 1, 'Dear Demo', 1, 0, 3, '', 0, NULL, NULL, NULL, NULL, '000000'),
(6, '5ed8a0d7-8e5d-5b56-8642-29e2aabdeb4d', 1, 4, 'Linda', '', 'Smith', '', '', '', 'M', NULL, 'linda@acmerpp.demo', '', '', 3, '', 'CEO', '', '', '', '', '06-12345678', '', NULL, 'US', 'NY', 'New York', '10019', '1111 Broadway', '', NULL, NULL, NULL, 1561972058, 1561972058, 1, 'Dear Linda', 1, 0, 4, '', 0, NULL, NULL, NULL, NULL, '000000'),
(7, 'ebab8d17-c68e-5c5e-9dce-76f70b326b3f', 1, 1, 'Read', '', 'Only', '', '', '', 'M', NULL, '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, '', 1562226096, 1562226096, 1, 'Dear Read', 1, 0, 0, '', 0, '', '', '', '', '000000'),
(8, 'f5d69d89-114f-5333-a972-b88f6de70969', 1, 1, 'piet', '', 'test', '', '', '', 'M', NULL, '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, '', 1572537426, 1572537426, 1, 'Dear piet', 1, 0, 0, '', 0, '', '', '', '', '000000'),
(9, '04d1b2d9-f7ec-531d-b58e-ad314c70ec563', 1, 100000, 'John', '', 'Orphan', '', '', '', 'M', NULL, 'john@smith.demo', '', '', 1, 'Management', 'CEO', '', '', '', '', '06-12345678', '', '', 'NL', 'Noord-Holland', 'Amsterdam', '1012 NX', 'Kalverstraat', '1', NULL, NULL, '', 1561972053, 1570702804, 1, 'Dear Mr. Smith', 1, 46, 0, 'addressbook/photos/3/con_1.jpg', 0, 'http://www.linkedin.com', 'http://www.facebook.com', 'http://www.twitter.com', 'echo123', '000000'),
(10, '04d1b2d9-f7ec-531d-b58e-ad314c70ec563', 1, 3, ';;ART-test;info@art-test.com;;;;;;;;;;;', '', '', '', '', '', 'M', NULL, 'john@smith.demo', '', '', 1, 'Management', 'CEO', '', '', '', '', '06-12345678', '', '', 'NL', 'Noord-Holland', 'Amsterdam', '1012 NX', 'Kalverstraat', '1', NULL, NULL, '', 1561972053, 1570702804, 1, 'Dear Mr. Smith', 1, 46, 0, 'addressbook/photos/3/con_1.jpg', 0, 'http://www.linkedin.com', 'http://www.facebook.com', 'http://www.twitter.com', 'echo123', '000000'),
(11, 'b0508fa8-3b51-55be-8cf3-1e5845a5f381', 1, 23432, 'Bastard', 'the', 'Orphan', '', '', '', 'M', NULL, '', '', '', 0, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', NULL, NULL, '', 1606124892, 1606124912, 1, 'Dear Bastard', 1, 0, 0, '', 0, '', '', '', '', '000000');

-- --------------------------------------------------------

--
-- Table structure for table `ab_contacts_vcard_props`
--

CREATE TABLE `ab_contacts_vcard_props` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `parameters` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(1023) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_portlet_birthdays`
--

CREATE TABLE `ab_portlet_birthdays` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_search_queries`
--

CREATE TABLE `ab_search_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `companies` tinyint(1) NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_sent_mailings`
--

CREATE TABLE `ab_sent_mailings` (
  `id` int(11) NOT NULL,
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
  `temp_pass` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_sent_mailing_companies`
--

CREATE TABLE `ab_sent_mailing_companies` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT 0,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `campaigns_opened` tinyint(1) NOT NULL DEFAULT 0,
  `has_error` tinyint(1) NOT NULL DEFAULT 0,
  `error_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_sent_mailing_contacts`
--

CREATE TABLE `ab_sent_mailing_contacts` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `campaigns_opened` tinyint(1) NOT NULL DEFAULT 0,
  `has_error` tinyint(1) NOT NULL DEFAULT 0,
  `error_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ab_settings`
--

CREATE TABLE `ab_settings` (
  `user_id` int(11) NOT NULL,
  `default_addressbook_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ab_settings`
--

INSERT INTO `ab_settings` (`user_id`, `default_addressbook_id`) VALUES
(1, 1),
(2, 5),
(3, 6),
(4, 7);

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_address`
--

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

--
-- Dumping data for table `addressbook_address`
--

INSERT INTO `addressbook_address` (`contactId`, `type`, `street`, `street2`, `zipCode`, `city`, `state`, `country`, `countryCode`, `latitude`, `longitude`) VALUES
(7, 'home', '', '', '', '', '', NULL, NULL, '0.00000000', '0.00000000'),
(8, 'home', '', '', '', '', '', NULL, NULL, '0.00000000', '0.00000000'),
(12, 'visit', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Holland', 'Netherlands', 'NL', NULL, NULL),
(12, 'postal', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Brabant', 'Netherlands', 'NL', NULL, NULL),
(13, 'visit', '1111 Broadway', NULL, '10019', 'New York', 'NY', 'United States', 'US', NULL, NULL),
(13, 'postal', '1111 Broadway', NULL, '10019', 'New York', 'NY', 'United States', 'US', NULL, NULL),
(1, 'home', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Holland', 'Netherlands', 'NL', '0.00000000', '0.00000000'),
(2, 'home', '1111 Broadway', '', '10019', 'New York', 'NY', 'United States', 'US', '0.00000000', '0.00000000'),
(10, 'home', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Holland', 'Netherlands', 'NL', '0.00000000', '0.00000000'),
(14, 'visit', '1111 Broadway', NULL, '10019', 'New York', 'NY', 'United States', 'US', NULL, NULL),
(14, 'postal', '1111 Broadway', NULL, '10019', 'New York', 'NY', 'United States', 'US', NULL, NULL),
(3, 'home', '', '', '', '', '', NULL, NULL, '0.00000000', '0.00000000'),
(4, 'home', '1111 Broadway', '', '10019', 'New York', 'NY', 'United States', 'US', '0.00000000', '0.00000000'),
(5, 'home', '1111 Broadway', '', '10019', 'New York', 'NY', 'United States', 'US', '0.00000000', '0.00000000'),
(6, 'home', '1111 Broadway', '', '10019', 'New York', 'NY', 'United States', 'US', '0.00000000', '0.00000000'),
(15, 'visit', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Holland', 'Netherlands', 'NL', NULL, NULL),
(15, 'postal', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Brabant', 'Netherlands', 'NL', NULL, NULL),
(9, 'home', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', 'Noord-Holland', 'Netherlands', 'NL', '0.00000000', '0.00000000'),
(11, 'home', '', '', '', '', '', NULL, NULL, '0.00000000', '0.00000000');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_addressbook`
--

CREATE TABLE `addressbook_addressbook` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `salutationTemplate` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook_addressbook`
--

INSERT INTO `addressbook_addressbook` (`id`, `name`, `aclId`, `createdBy`, `filesFolderId`, `salutationTemplate`) VALUES
(1, 'Prospects', 15, 1, 39, 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),
(3, 'Customers', 17, 1, NULL, 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),
(4, 'Users', 86, 1, 18, 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),
(5, '__ORPHANED__', 142, 1, NULL, 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'),
(6, 'Test', 146, 1, NULL, 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}==\"M\"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_contact`
--

CREATE TABLE `addressbook_contact` (
  `id` int(11) NOT NULL,
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
  `nameBank` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `BIC` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook_contact`
--

INSERT INTO `addressbook_contact` (`id`, `addressBookId`, `createdBy`, `createdAt`, `modifiedAt`, `modifiedBy`, `goUserId`, `prefixes`, `initials`, `firstName`, `middleName`, `lastName`, `suffixes`, `salutation`, `gender`, `notes`, `isOrganization`, `name`, `IBAN`, `registrationNumber`, `vatNo`, `vatReverseCharge`, `debtorNumber`, `photoBlobId`, `language`, `jobTitle`, `department`, `filesFolderId`, `uid`, `vcardBlobId`, `uri`, `color`, `nameBank`, `BIC`) VALUES
(1, 3, 1, '2019-07-01 09:07:33', '2019-10-10 10:20:04', 1, NULL, '', '', 'John', '', 'Smith', '', 'Dear Mr. Smith', 'M', '', 0, 'John Smith', '', '', NULL, 0, NULL, 0x34363631353432656130643635353364313830616463353033323733626565646465303935383130, NULL, 'CEO', 'Management', 46, '1@localhost:63', NULL, '1@localhost:63.vcf', 'A3A3A3', NULL, NULL),
(2, 3, 1, '2019-07-01 09:07:34', '2019-07-19 13:54:37', 1, NULL, '', '', 'Wile', 'E.', 'Coyote', '', 'Dear Mr. Coyote', 'M', '', 0, 'Wile E. Coyote', '', '', NULL, 0, NULL, 0x36376361353062376665663961396538313936643939666330633066363263343133643166383336, NULL, 'CEO', '', 17, '2@localhost:63', NULL, '2@localhost:63.vcf', NULL, NULL, NULL),
(3, 4, 1, '2019-07-01 09:07:35', '2019-10-14 10:10:10', 1, 1, '', '', 'System', '', 'Administrator', '', 'Dear System', 'M', '', 0, 'System Administrator', '', '', NULL, 0, NULL, NULL, NULL, '', '', NULL, '3@localhost:63', NULL, '3@localhost:63.vcf', NULL, NULL, NULL),
(4, 4, 1, '2019-07-01 09:07:35', '2019-07-09 07:39:59', 1, 2, '', '', 'Elmer', '', 'Fudd', '', 'Dear Elmer', 'M', '', 0, 'Elmer Fudd', '', '', NULL, 0, NULL, NULL, NULL, 'CEO', '', NULL, '4@localhost:63', NULL, '4@localhost:63.vcf', NULL, NULL, NULL),
(5, 4, 1, '2019-07-01 09:07:36', '2019-07-04 14:12:16', 1, 3, '', '', 'Demo', '', 'User', '', 'Dear Demo', 'M', '', 0, 'Demo User', '', '', NULL, 0, NULL, NULL, NULL, 'CEO', '', NULL, '5@localhost:63', NULL, '5@localhost:63.vcf', NULL, NULL, NULL),
(6, 4, 1, '2019-07-01 09:07:38', '2019-07-01 09:07:38', 1, 4, '', '', 'Linda', '', 'Smith', '', 'Dear Linda', 'M', '', 0, 'Linda Smith', '', '', NULL, 0, NULL, NULL, NULL, 'CEO', '', NULL, '6@localhost:63', NULL, '6@localhost:63.vcf', NULL, NULL, NULL),
(7, 1, 1, '2019-07-04 07:41:36', '2019-07-04 07:41:36', 1, NULL, '', '', 'Read', '', 'Only', '', 'Dear Read', 'M', '', 0, 'Read Only', '', '', NULL, 0, NULL, NULL, NULL, '', '', NULL, '7@localhost:63', NULL, '7@localhost:63.vcf', NULL, NULL, NULL),
(8, 1, 1, '2019-10-31 15:57:06', '2019-10-31 15:57:06', 1, NULL, '', '', 'piet', '', 'test', '', 'Dear piet', 'M', '', 0, 'piet test', '', '', NULL, 0, NULL, NULL, NULL, '', '', NULL, '8@localhost:63', NULL, '8@localhost:63.vcf', NULL, NULL, NULL),
(9, 5, 1, '2019-07-01 09:07:33', '2019-10-10 10:20:04', 1, NULL, '', '', 'John', '', 'Orphan', '', 'Dear Mr. Smith', 'M', '', 0, 'John Orphan', '', '', NULL, 0, NULL, 0x34363631353432656130643635353364313830616463353033323733626565646465303935383130, NULL, 'CEO', 'Management', 46, '9@localhost:63', NULL, '9@localhost:63.vcf', NULL, NULL, NULL),
(10, 3, 1, '2019-07-01 09:07:33', '2019-10-10 10:20:04', 1, NULL, '', '', ';;ART-test;info@art-test.com;;;;;;;;;;;', '', '', '', 'Dear Mr. Smith', 'M', '', 0, ';;ART-test;info@art-test.com;;;;;;;;;;;', '', '', NULL, 0, NULL, 0x34363631353432656130643635353364313830616463353033323733626565646465303935383130, NULL, 'CEO', 'Management', 46, '10@localhost:63', NULL, '10@localhost:63.vcf', NULL, NULL, NULL),
(11, 5, 1, '2020-11-23 09:48:12', '2020-11-23 09:48:32', 1, NULL, '', '', 'Bastard', 'the', 'Orphan', '', 'Dear Bastard', 'M', '', 0, 'Bastard the Orphan', '', '', NULL, 0, NULL, NULL, NULL, '', '', NULL, '11@localhost:63', NULL, '11@localhost:63.vcf', NULL, NULL, NULL),
(12, 3, 1, '2019-07-01 09:07:33', '2020-11-27 12:01:42', 1, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 'Just a demo company', 1, 'Smith Inc', '', '', 'NL 1234.56.789.B01', 0, NULL, NULL, NULL, NULL, NULL, NULL, '12@localhost:63', NULL, '12@localhost:63.vcf', 'F4F4F4', NULL, NULL),
(13, 3, 1, '2019-07-01 09:07:34', '2020-11-27 12:01:42', 1, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 'The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]', 1, 'ACME Corporation', '', '', 'US 1234.56.789.B01', 0, NULL, NULL, NULL, NULL, NULL, NULL, '13@localhost:63', NULL, '13@localhost:63.vcf', NULL, NULL, NULL),
(14, 4, 1, '2019-07-01 09:07:35', '2020-11-27 12:01:42', 1, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 'The name Acme became popular for businesses by the 1920s, when alphabetized business telephone directories such as the Yellow Pages began to be widespread. There were a flood of businesses named Acme (some of these still survive[1]). For example, early Sears catalogues contained a number of products with the \"Acme\" trademark, including anvils, which are frequently used in Warner Bros. cartoons.[2]', 1, 'ACME Rocket Powered Products', '', '', 'US 1234.56.789.B01', 0, NULL, NULL, NULL, NULL, NULL, NULL, '14@localhost:63', NULL, '14@localhost:63.vcf', NULL, NULL, NULL),
(15, 5, 1, '2019-07-01 09:07:33', '2020-11-27 12:01:42', 1, NULL, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, 'Just a demo company', 1, 'Orphaned Company', '', '', 'NL 1234.56.789.B01', 0, NULL, NULL, NULL, NULL, NULL, NULL, '15@localhost:63', NULL, '15@localhost:63.vcf', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_contact_custom_fields`
--

CREATE TABLE `addressbook_contact_custom_fields` (
  `id` int(11) NOT NULL,
  `Company` int(11) DEFAULT NULL,
  `Contact` int(11) DEFAULT NULL,
  `File` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Checkbox` tinyint(1) NOT NULL DEFAULT 0,
  `Number` double DEFAULT NULL,
  `User` int(11) DEFAULT NULL,
  `HTML` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Infotext` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Heading` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Select` int(11) DEFAULT NULL,
  `Treeselect` int(11) DEFAULT NULL,
  `Textarea` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `Function` double DEFAULT NULL,
  `Text` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Company_1` int(11) DEFAULT NULL,
  `Contact_1` int(11) DEFAULT NULL,
  `File_1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Checkbox_1` tinyint(1) NOT NULL DEFAULT 0,
  `Number_1` double DEFAULT NULL,
  `User_1` int(11) DEFAULT NULL,
  `HTML_1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Infotext_1` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Heading_1` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `Select_1` int(11) DEFAULT NULL,
  `Treeselect_1` int(11) DEFAULT NULL,
  `Textarea_1` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Date_1` date DEFAULT NULL,
  `Function_1` double DEFAULT NULL,
  `Text_1` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook_contact_custom_fields`
--

INSERT INTO `addressbook_contact_custom_fields` (`id`, `Company`, `Contact`, `File`, `Checkbox`, `Number`, `User`, `HTML`, `Infotext`, `Heading`, `Select`, `Treeselect`, `Textarea`, `Date`, `Function`, `Text`, `Company_1`, `Contact_1`, `File_1`, `Checkbox_1`, `Number_1`, `User_1`, `HTML_1`, `Infotext_1`, `Heading_1`, `Select_1`, `Treeselect_1`, `Textarea_1`, `Date_1`, `Function_1`, `Text_1`) VALUES
(1, NULL, NULL, '', 0, NULL, NULL, '', '', '', 1, 200093, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(2, NULL, NULL, '', 0, NULL, NULL, '', '', '', 200091, 100007, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(3, NULL, NULL, '', 0, NULL, NULL, '', '', '', 3, NULL, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(4, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(5, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(6, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(7, NULL, NULL, '', 0, NULL, NULL, '', '', '', NULL, NULL, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(8, NULL, NULL, '', 0, NULL, NULL, '', '', '', NULL, NULL, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(11, NULL, NULL, '', 0, NULL, NULL, '', '', '', 200092, 200094, '', NULL, 0, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(12, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(13, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, ''),
(14, NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, '', 0, NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_contact_group`
--

CREATE TABLE `addressbook_contact_group` (
  `contactId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_contact_star`
--

CREATE TABLE `addressbook_contact_star` (
  `contactId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL DEFAULT 0,
  `starred` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_date`
--

CREATE TABLE `addressbook_date` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'birthday',
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_email_address`
--

CREATE TABLE `addressbook_email_address` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook_email_address`
--

INSERT INTO `addressbook_email_address` (`contactId`, `type`, `email`) VALUES
(12, 'work', 'info@smith.demo'),
(13, 'work', 'info@acme.demo'),
(1, 'work', 'john@smith.demo'),
(2, 'work', 'wile@acme.demo'),
(10, 'work', 'john@smith.demo'),
(14, 'work', 'info@acmerpp.demo'),
(3, 'work', 'admin@intermesh.localhost'),
(4, 'work', 'elmer@group-office.com'),
(5, 'work', 'demo@acmerpp.demo'),
(5, 'work', 'demo@group-office.com'),
(6, 'work', 'linda@acmerpp.demo'),
(15, 'work', 'info@smith.demo'),
(9, 'work', 'john@smith.demo');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_group`
--

CREATE TABLE `addressbook_group` (
  `id` int(11) NOT NULL,
  `addressBookId` int(11) NOT NULL,
  `name` varchar(190) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_phone_number`
--

CREATE TABLE `addressbook_phone_number` (
  `contactId` int(11) NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

--
-- Dumping data for table `addressbook_phone_number`
--

INSERT INTO `addressbook_phone_number` (`contactId`, `type`, `number`) VALUES
(12, 'work', '+31 (0) 10 - 1234567'),
(12, 'fax', '+31 (0) 1234567'),
(13, 'work', '(555) 123-4567'),
(13, 'fax', '(555) 123-4567'),
(1, 'mobile', '06-12345678'),
(2, 'mobile', '06-12345678'),
(10, 'mobile', '06-12345678'),
(14, 'work', '(555) 123-4567'),
(14, 'fax', '(555) 123-4567'),
(4, 'mobile', '06-12345678'),
(5, 'mobile', '06-12345678'),
(6, 'mobile', '06-12345678'),
(15, 'work', '+31 (0) 10 - 1234567'),
(15, 'fax', '+31 (0) 1234567'),
(9, 'mobile', '06-12345678');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_url`
--

CREATE TABLE `addressbook_url` (
  `contactId` int(11) NOT NULL,
  `type` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addressbook_url`
--

INSERT INTO `addressbook_url` (`contactId`, `type`, `url`) VALUES
(12, 'homepage', 'http://www.smith.demo'),
(13, 'homepage', 'http://www.acme.demo'),
(1, 'facebook', 'http://www.facebook.com'),
(1, 'linkedin', 'http://www.linkedin.com'),
(1, 'twitter', 'http://www.twitter.com'),
(1, 'skype', 'echo123'),
(2, 'facebook', 'http://www.facebook.com'),
(2, 'linkedin', 'http://www.linkedin.com'),
(2, 'twitter', 'http://www.twitter.com'),
(2, 'skype', 'test'),
(10, 'facebook', 'http://www.facebook.com'),
(10, 'linkedin', 'http://www.linkedin.com'),
(10, 'twitter', 'http://www.twitter.com'),
(10, 'skype', 'echo123'),
(14, 'homepage', 'http://www.acmerpp.demo'),
(15, 'homepage', 'http://www.smith.demo'),
(9, 'facebook', 'http://www.facebook.com'),
(9, 'linkedin', 'http://www.linkedin.com'),
(9, 'twitter', 'http://www.twitter.com'),
(9, 'skype', 'echo123');

-- --------------------------------------------------------

--
-- Table structure for table `addressbook_user_settings`
--

CREATE TABLE `addressbook_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultAddressBookId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `bm_bookmarks`
--

CREATE TABLE `bm_bookmarks` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT 0,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_icon` tinyint(1) NOT NULL DEFAULT 1,
  `open_extern` tinyint(1) NOT NULL DEFAULT 1,
  `behave_as_module` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bm_bookmarks`
--

INSERT INTO `bm_bookmarks` (`id`, `category_id`, `user_id`, `name`, `content`, `description`, `logo`, `public_icon`, `open_extern`, `behave_as_module`) VALUES
(1, 1, 1, 'Google Search', 'http://www.google.com', 'Search the web', 'icons/viewmag.png', 1, 1, 0),
(2, 1, 1, 'Wikipedia', 'http://www.wikipedia.com', 'The Free Encyclopedia', 'icons/agt_web.png', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bm_categories`
--

CREATE TABLE `bm_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `acl_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `show_in_startmenu` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bm_categories`
--

INSERT INTO `bm_categories` (`id`, `user_id`, `acl_id`, `name`, `show_in_startmenu`) VALUES
(1, 1, 38, 'General', 0);

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks_bookmark`
--

CREATE TABLE `bookmarks_bookmark` (
  `id` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `openExtern` tinyint(1) NOT NULL DEFAULT 1,
  `behaveAsModule` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookmarks_bookmark`
--

INSERT INTO `bookmarks_bookmark` (`id`, `categoryId`, `createdBy`, `name`, `content`, `description`, `logo`, `openExtern`, `behaveAsModule`) VALUES
(1, 1, 1, 'Google Search', 'http://www.google.com', 'Search the web', 'c8b1af19b629a3db38ddf35adf9ea77782b943df', 1, 0),
(2, 1, 1, 'Wikipedia', 'http://www.wikipedia.com', 'The Free Encyclopedia', 'c675cc29c676cead50074077b488b9cf72a5c58f', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks_category`
--

CREATE TABLE `bookmarks_category` (
  `id` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookmarks_category`
--

INSERT INTO `bookmarks_category` (`id`, `createdBy`, `aclId`, `name`) VALUES
(1, 1, 38, 'General');

-- --------------------------------------------------------

--
-- Table structure for table `bs_batchjobs`
--

CREATE TABLE `bs_batchjobs` (
  `id` int(11) NOT NULL DEFAULT 0,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `from_status_id` int(11) NOT NULL DEFAULT 0,
  `to_status_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_batchjob_orders`
--

CREATE TABLE `bs_batchjob_orders` (
  `batchjob_id` int(11) NOT NULL DEFAULT 0,
  `order_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_books`
--

CREATE TABLE `bs_books` (
  `id` int(11) NOT NULL,
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
  `default_due_days` int(11) NOT NULL DEFAULT 14
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_books`
--

INSERT INTO `bs_books` (`id`, `user_id`, `name`, `acl_id`, `order_id_prefix`, `order_id_length`, `show_statuses`, `next_id`, `default_vat`, `currency`, `order_csv_template`, `item_csv_template`, `country`, `call_after_days`, `sender_email`, `sender_name`, `is_purchase_orders_book`, `backorder_status_id`, `delivered_status_id`, `reversal_status_id`, `addressbook_id`, `files_folder_id`, `allow_delete`, `import_status_id`, `auto_paid_status`, `import_notify_customer`, `import_duplicate_to_book`, `import_duplicate_status_id`, `show_sales_agents`, `default_due_days`) VALUES
(1, 1, 'Quotes', 22, 'Q%y', 6, NULL, 2, 19, '€', NULL, NULL, '', 3, NULL, NULL, 0, 0, 0, 0, 0, 36, 0, 0, 0, 0, 0, 0, 0, 14),
(2, 1, 'Orders', 27, 'O%y', 6, NULL, 2, 19, '€', NULL, NULL, '', 0, NULL, NULL, 0, 0, 0, 0, 0, 37, 0, 0, 0, 0, 0, 0, 0, 14),
(3, 1, 'Invoices', 32, 'I%y', 6, NULL, 2, 19, '€', NULL, NULL, '', 0, NULL, NULL, 0, 0, 0, 0, 0, 38, 0, 0, 0, 0, 0, 0, 0, 14);

-- --------------------------------------------------------

--
-- Table structure for table `bs_category_languages`
--

CREATE TABLE `bs_category_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_cost_codes`
--

CREATE TABLE `bs_cost_codes` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_doc_templates`
--

CREATE TABLE `bs_doc_templates` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longblob NOT NULL,
  `extension` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_doc_templates`
--

INSERT INTO `bs_doc_templates` (`id`, `book_id`, `user_id`, `name`, `content`, `extension`) VALUES
(1, 1, 1, 'Invoice', 0x504b0304140000080000efbfbd60efbfbd425eefbfbd320c2700000027000000080000006d696d65747970656170706c69636174696f6e2f766e642e6f617369732e6f70656e646f63756d656e742e74657874504b0304140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd210000180000005468756d626e61696c732f7468756d626e61696c2e706e67efbfbd504e470d0a1a0a0000000d49484452000000efbfbd0000010008020000007a41efbfbdefbfbd000021efbfbd4944415478efbfbdefbfbd77401447efbfbdc7b7efbfbd5eefbfbdefbfbd1c70efbfbddebb020a220808efbfbdefbfbdc782efbfbd684c3131efbfbd29efbfbd79efbfbd7dd3935fefbfbd29efbfbdefbfbd184befbfbd44efbfbdefbfbd68efbfbd5863057b14efbfbd053c44efbfbdefbfbd38eeb8b277efbfbdefbfbd421518efbfbd4c10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3373efbfbd5f66efbfbd79667660efbfbdefbfbd7a0c00efbfbdefbfbdefbfbdefbfbd15007a35efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbdefbfbd4357efbfbdd3bcefbfbd37efbfbdefbfbd55efbfbdefbfbd5befbfbdefbfbdceb6efbfbdefbfbdefbfbd25efbfbd07efbfbd0e78efbfbdefbfbdefbfbdefbfbd725befbfbd35efbfbdefbfbd3722efbfbd3eefbfbd5f3c547aefbfbd0cd787cc8edfb6efbfbd48efbfbdefbfbd714fefbfbdefbfbd27efbfbd2defbfbdefbfbdefbfbd7cefbfbd4b5fefbfbd5f7b2b5d44efbfbd26efbfbd7eefbfbdefbfbdefbfbdefbfbd676fefbfbd70efbfbdefbfbdefbfbd375cefbfbd7defbfbd3e23efbfbdefbfbd6defbfbdefbfbd2e2fefbfbdefbfbdefbfbd14efbfbdefbfbdefbfbd63efbfbdefbfbdefbfbdd48aefbfbd687f0eefbfbd34efbfbdefbfbdefbfbd083934dd9332efbfbdefbfbd24efbfbdefbfbdefbfbd7c2b37efbfbd39efbfbdefbfbd5851efbfbd255e7cefbfbd74c3a727efbfbdefbfbd4cefbfbd0b78e0b1a2efbfbd6038efbfbdefbfbd7cefbfbdefbfbd0c59efbfbd5aefbfbdefbfbdefbfbd2bc888efbfbdefbfbdc39fefbfbdefbfbdefbfbd7cefbfbdefbfbd794befbfbd2b22315defbfbdefbfbd7eefbfbd57efbfbd1840566defbfbd46efbfbd0cefbfbdd0967defbfbd3874efbfbd6befbfbdefbfbdefbfbd2defbfbdefbfbd0a282619efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbd5fefbfbdefbfbdefbfbd45efbfbd4e516f7fefbfbdefbfbd0a3cefbfbdefbfbd7fefbfbd5eefbfbdefbfbdefbfbd35efbfbdefbfbd78efbfbd60efbfbdefbfbd1f1f5befbfbd132321efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7237efbfbdefbfbdefbfbdefbfbd72395defbfbd79efbfbd25daa3efbfbd48efbfbd61efbfbdefbfbd0fefbfbdefbfbd3aefbfbd657943efbfbd3b5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd31efbfbd35472ed88e4f1c10efbfbd60efbfbd61efbfbd52efbfbdefbfbdefbfbd64efbfbdcc877d122befbfbd3761efbfbdefbfbd7074efbfbdefbfbd6c46efbfbdefbfbdefbfbd03efbfbdefbfbd1fefbfbd2f4015efbfbdefbfbd67efbfbd5f53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd11c298efbfbd6b5f2cefbfbdefbfbdefbfbd3f6f28efbfbdefbfbdefbfbdca9a72efbfbdcea76d6360efbfbdefbfbdefbfbd1eefbfbd7065efbfbd7edc8943efbfbdefbfbdcbaeefbfbdefbfbdefbfbdefbfbd27efbfbd6367efbfbdefbfbdefbfbdefbfbd2aefbfbd2049efbfbd202cefbfbd78efbfbd240f65237d13efbfbdefbfbd7aefbfbdde85efbfbd19ce89e3939cefbfbd6befbfbdefbfbd4c0eefbfbd64efbfbd18efbfbdefbfbd215b71efbfbd66efbfbd707befbfbddbaa36efbfbd61efbfbd38efbfbd76435b7defbfbd504b0534efbfbd30efbfbd2430efbfbd305952efbfbdefbfbd39efbfbd46185a19efbfbd1e0c3f2458efbfbd0fefbfbd65c68a4fcdbbefbfbd671a3e1273cf980e16efbfbdefbfbdefbfbd39cb8c151f183fefbfbd07efbfbdefbfbdd585efbfbdefbfbdefbfbd2c316cefbfbdefbfbdefbfbd5811efbfbdefbfbd21efbfbddf9eefbfbdefbfbd59efbfbdefbfbd5873cab2efbfbd5befbfbd0e5c3fd3bcefbfbdefbfbdefbfbd6fefbfbdefbfbd52efbfbd17efbfbdefbfbd7fefbfbd3befbfbd2aefbfbd295cefbfbdefbfbdefbfbdefbfbd4fefbfbd451c18efbfbd77efbfbd0b4f35efbfbd6726efbfbd36efbfbd47193fefbfbd33efbfbdefbfbdefbfbd1961efbfbd5c3eefbfbd33efbfbdefbfbdefbfbd4341efbfbd693719efbfbdefbfbdefbfbdefbfbd77efbfbd5c2cefbfbdefbfbd69efbfbdefbfbd47793fefbfbdefbfbd27efbfbd55efbfbd3e4fefbfbdefbfbd07efbfbd1b017d0028401f000a0befbfbdefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbd5e4c69efbfbd05efbfbdefbfbdefbfbd0e54070defbfbd637775514b06efbfbdefbfbd554b0befbfbd2cefbfbd10efbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd79efbfbd6cefbfbd18efbfbdefbfbd2b72efbfbd505defbfbd5defbfbdefbfbd0befbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd716e45efbfbd72efbfbd4c71efbfbdd4903fefbfbd2cefbfbdefbfbdefbfbd75efbfbdefbfbd0f17efbfbdefbfbd0aefbfbdefbfbddcb8d390efbfbd71efbfbd58efbfbd54efbfbdefbfbd44efbfbd5a3d685876efbfbdefbfbd25efbfbd047cefbfbdefbfbd5eefbfbd74efbfbd3979efbfbd4c1befbfbdefbfbd76553fd289efbfbdefbfbdefbfbd09efbfbdefbfbd7aefbfbdefbfbd63626a5523efbfbd776060383f28354644625a077f2722df98efbfbdefbfbdc7afefbfbdefbfbdefbfbdefbfbd76d0803c6058efbfbd0fefbfbd47efbfbdefbfbd563aefbfbdefbfbd347b021b3befbfbdd4bfefbfbd3eefbfbdefbfbd39efbfbd435653efbfbdefbfbd18efbfbd5853efbfbd32efbfbdefbfbd77efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbd0a1b6d03efbfbd78efbfbdefbfbdefbfbd393eefbfbdefbfbd36efbfbd50efbfbdefbfbdefbfbdefbfbd33424c75efbfbdefbfbd6defbfbd4225d9b5efbfbd546c6850efbfbd0eefbfbdefbfbdefbfbdefbfbd5b6b1326efbfbd48361defbfbd1befbfbdefbfbdefbfbd5217efbfbdefbfbd16efbfbd6325efbfbdefbfbd246defbfbdefbfbd09efbfbdefbfbdefbfbd4239efbfbd6c407b2cd487efbfbdd6aaefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6e55efbfbd04293befbfbd62efbfbd2a654258efbfbdefbfbd3e485070efbfbd3aefbfbd734d4e77dcb746efbfbd7fefbfbdefbfbd4a75efbfbd33633c48efbfbdefbfbdd59f6defbfbd1c58efbfbdefbfbdd3ad74daac51351f6fefbfbd29efbfbde98489efbfbdd9bb59efbfbdefbfbdefbfbd6b7e66efbfbd7dc68fefbfbdd4b5357eefbfbdecbaa539efbfbd57efbfbdefbfbddd9f7befbfbd643defbfbdefbfbdefbfbdefbfbdefbfbd663f7eefbfbd587aefbfbdefbfbdcb9cefbfbdefbfbd4eefbfbd2fefbfbdefbfbd72efbfbd2fefbfbd55efbfbdc4bbefbfbde6968aefbfbd63085ad7bd3fefbfbd43efbfbd65efbfbdefbfbdefbfbd56506cefbfbd24efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd637d78efbfbd534a35efbfbdefbfbd31efbfbdefbfbd3f3cefbfbd4d6aefbfbd5aefbfbdefbfbdefbfbdefbfbd49efbfbdefbfbd2867efbfbddfb2efbfbdefbfbd52efbfbdefbfbdefbfbd536831efbfbd2408efbfbd60d88745efbfbd6b49efbfbdefbfbdefbfbdefbfbd44193eefbfbd437befbfbd441346efbfbdefbfbd2aefbfbd5befbfbddfbc28efbfbdefbfbdefbfbd57300709efbfbdefbfbd1aefbfbd71efbfbd6a5461efbfbd1ec3ba32efbfbdefbfbd5c48efbfbdefbfbd68efbfbd56efbfbd32d5934ddeaeefbfbd60efbfbdefbfbdefbfbd33403b2cefbfbd07efbfbd69387cefbfbdefbfbdefbfbd45efbfbd2c0cefbfbd0c1e6148efbfbd057b24747804c49befbfbd2b341579d98764efbfbdefbfbd47efbfbd7cefbfbdefbfbdefbfbd4befbfbd62773e213b6868efbfbd47efbfbd48141aefbfbdefbfbd30efbfbdefbfbd30efbfbd50efbfbdefbfbdefbfbd12efbfbd257befbfbdefbfbd5d0b68efbfbdefbfbd0eefbfbd35efbfbdd5a0efbfbdefbfbd0f46efbfbd6032efbfbdefbfbdefbfbd6aefbfbd2eefbfbdefbfbd431eefbfbd7021efbfbdefbfbdefbfbdefbfbd5c70365befbfbd36187f6f5556efbfbde9a4a6efbfbd08efbfbd4eefbfbd7143354e14723c4353221d29efbfbdefbfbd64286befbfbdefbfbd07efbfbd6125163e5fd8be09efbfbd056b571559efbfbd7cefbfbd22efbfbd796befbfbd38efbfbd1577efbfbdc3a309efbfbdefbfbd461aefbfbdefbfbd0e0fefbfbdefbfbd3953633e7aefbfbdefbfbdefbfbd25733d3eefbfbdefbfbdefbfbdefbfbd0856efbfbd67efbfbd5e65efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6fefbfbdefbfbdefbfbdefbfbd174409efbfbd0747efbfbd2cefbfbd32efbfbd1435d8a9efbfbdefbfbd741defbfbdefbfbd7a2fefbfbd50dfa6efbfbdefbfbd2f5579570cefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdd988efbfbdefbfbd5439efbfbdefbfbdefbfbdefbfbd5d19efbfbd18ce90efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd456c702aefbfbdefbfbd123c23efbfbd27163e5fefbfbdefbfbd4a5aefbfbdefbfbd08303d5fefbfbdefbfbdefbfbd273b646befbfbd7cefbfbdefbfbdefbfbd4befbfbdefbfbd26efbfbd6ad890efbfbdcc83efbfbdefbfbdefbfbddb9911efbfbd476a1aefbfbd48efbfbd63efbfbd0c6218efbfbdefbfbdefbfbdefbfbd2defbfbdefbfbd36efbfbd4b32efbfbd6010efbfbd7defbfbdefbfbdefbfbd2defbfbd24efbfbd0befbfbd20efbfbdefbfbd6d1b76efbfbd57efbfbd30efbfbdefbfbddcb6474cefbfbd2fefbfbd58efbfbd315fefbfbdefbfbd70efbfbdefbfbd74206c6326efbfbd1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd745befbfbdefbfbdefbfbd3a0c1cefbfbd38efbfbdefbfbdefbfbdefbfbdefbfbd66efbfbdefbfbd505dc99306efbfbdefbfbd5d2defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd17041defbfbd5e4fefbfbdefbfbd1f66d781efbfbdefbfbd7a0a375c52efbfbd1915182d20584c21457a66065eefbfbd52efbfbd02efbfbdefbfbd034037efbfbd1f66efbfbd01efbfbd0e1fefbfbd4cefbfbd4c03305ddd9fefbfbdefbfbd3070261e04efbfbdefbfbdefbfbdefbfbd34efbfbdefbfbdefbfbd181b66703eefbfbdefbfbd0224efbfbdefbfbdefbfbd4b64efbfbdefbfbdefbfbd103befbfbd36efbfbdefbfbdefbfbdefbfbd15efbfbdefbfbd7e5eefbfbdefbfbd2fefbfbddcbfefbfbd6d1a776cefbfbd14efbfbdefbfbd46efbfbd68efbfbd3defbfbd35efbfbdefbfbd7eefbfbdefbfbd644056efbfbdefbfbdefbfbd7d2eefbfbdefbfbd0cefbfbd0befbfbdefbfbd74efbfbd1a0fefbfbd4cefbfbdefbfbd6defbfbd07efbfbdefbfbd59efbfbdefbfbd3c21efbfbdefbfbdefbfbd3c4f4aefbfbd315aefbfbd10efbfbdefbfbd1811efbfbdefbfbd5befbfbd331e626a35652b62efbfbd3641efbfbd35efbfbdefbfbd520235efbfbd2d0757efbfbd3befbfbd46efbfbd63efbfbd33efbfbdefbfbd301cefbfbdefbfbd6defbfbd2125efbfbd50efbfbd303465efbfbd10efbfbd1d6cefbfbd76771a11efbfbdefbfbdd9910b171aefbfbd0cefbfbd1e693e3b2aefbfbd644defbfbd0befbfbdefbfbd062c7cefbfbd7023efbfbd5befbfbd0aefbfbd1defbfbd47efbfbd29efbfbdefbfbdefbfbd64efbfbdefbfbd35efbfbd6f0766efbfbd0128401f000aefbfbdefbfbd41176d5eefbfbd6befbfbdefbfbd2befbfbdefbfbd3eefbfbdefbfbd5d50efbfbdc5af162eefbfbd3defbfbdefbfbdefbfbd6f1cefbfbd0e4a15efbfbd6c08efbfbd776f7aefbfbd1defbfbdefbfbd3fefbfbd58efbfbd7eefbfbdc290efbfbdefbfbd2eefbfbdefbfbdefbfbd7873efbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4e570c5aefbfbd5c0aefbfbd5eefbfbd2a2a5aefbfbdefbfbd7f450b4aefbfbdefbfbd625cefbfbd24efbfbdefbfbd306368efbfbd7922efbfbdefbfbd46c3b4efbfbd071c0b6fefbfbdefbfbdefbfbdefbfbd2defbfbd1c45efbfbdefbfbdefbfbd61efbfbdefbfbdefbfbd72efbfbdefbfbdefbfbdd49befbfbd0b48efbfbdefbfbdefbfbd18efbfbd54efbfbdefbfbdefbfbd5c6e5cefbfbdefbfbd2876efbfbd44760aefbfbdefbfbd3fefbfbd58efbfbd0fefbfbdefbfbdefbfbdefbfbd2fefbfbd391ed0bcc38f716cefbfbd7fefbfbd7befbfbd7613efbfbde4a39b4fefbfbd324e5bcf8169efbfbd0f367fefbfbdefbfbdefbfbdc6bcefbfbdefbfbdcb93efbfbdefbfbd79efbfbd05efbfbd35efbfbd400f03efbfbd01efbfbd02efbfbd01efbfbd007d00287a521f380eefbfbdefbfbd5fefbfbdefbfbdefbfbd7d600fefbfbd1f1b566defbfbd4a0befbfbdefbfbdefbfbdefbfbd73376eefbfbdefbfbdefbfbdefbfbdefbfbd54efbfbdefbfbdefbfbdefbfbdefbfbd19efbfbd133a791defbfbd2d066defbfbdefbfbd6975efbfbdefbfbdcda53f6e4f7c69efbfbdefbfbdefbfbd5befbfbd13efbfbdcc8defbfbddaba25efbfbd5a21efbfbdefbfbd3c29efbfbd066defbfbd68efbfbd41efbfbddf8cefbfbdefbfbd0fefbfbdefbfbdefbfbd3165efbfbd04efbfbdefbfbd03efbfbd12efbfbdefbfbd3defbfbd29efbfbd165eefbfbd3eefbfbdefbfbd6fefbfbdefbfbd23efbfbd4d69efbfbd13efbfbd43efbfbdefbfbdefbfbdefbfbd527d74efbfbd54dbac3cefbfbdefbfbddcb1efbfbd27efbfbd7f3fdf8fefbfbd7559efbfbd31efbfbd2eefbfbdefbfbdefbfbd256b3f2a1aefbfbd64efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbdc986efbfbd58cd86efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0074efbfbd15efbfbd5e53efbfbdefbfbdefbfbd57707b3befbfbdefbfbdefbfbd0163efbfbd2e15efbfbde497a4efbfbdefbfbd44efbfbd73efbfbdefbfbd623a7eefbfbdefbfbdefbfbd4d3fefbfbdefbfbdefbfbdefbfbdefbfbd0462db9ac284efbfbdcfa538efbfbd77efbfbd2aefbfbdefbfbde1879eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd67efbfbd57efbfbd48efbfbd4e5a3f7defbfbdefbfbdefbfbd1527efbfbd6add9fefbfbdefbfbdefbfbdefbfbd35173cefbfbd636427efbfbd43efbfbd73efbfbd7e2cefbfbdefbfbd74efbfbd2966efbfbdefbfbdd1a0efbfbdefbfbdd79e7c77efbfbd2fefbfbd03dd8befbfbdefbfbddf9d76efbfbdefbfbd0f7e57c496efbfbdefbfbd6f45efbfbd6235c2aa337befbfbd1e5e7f38efbfbd1eefbfbd6840470b0f24efbfbdefbfbd2b58efbfbd7c21efbfbd41efbfbd472b366d2870efbfbd60083c7defbfbdefbfbd25efbfbdefbfbd71665704e7aaba16276c02efbfbdefbfbdefbfbd6eefbfbd34efbfbd3145efbfbd2defbfbd18efbfbde78b8253efbfbd732a19efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3335efbfbd1566efbfbd7839efbfbd18efbfbdefbfbd1c18efbfbd2b355aefbfbd424173efbfbdefbfbdd99befbfbdefbfbd3cd2b852efbfbd69691a4631efbfbd64efbfbdefbfbdefbfbd59efbfbdefbfbd38efbfbd0defbfbd0defbfbd7aefbfbdefbfbdefbfbd10efbfbdefbfbd106befbfbdefbfbd704b1fefbfbd6678d687371defbfbd6158732b6d7e371aefbfbdefbfbd34efbfbd13585478c7ab3befbfbdefbfbd11635f29efbfbdefbfbdefbfbd347bd392efbfbd6362efbfbdefbfbdefbfbd1b0cefbfbd52efbfbd25efbfbdefbfbd4defbfbdefbfbd4e48efbfbd3c666f5cefbfbdefbfbd463aefbfbdefbfbd67efbfbdefbfbdefbfbd59efbfbdefbfbd2fefbfbdefbfbd6cefbfbd6235efbfbd5eefbfbdefbfbdefbfbd66efbfbd372e56efbfbdefbfbdefbfbdefbfbdefbfbd58efbfbdefbfbdefbfbd70410c2d242a42df91efbfbd63efbfbdefbfbd30efbfbd1d4befbfbd42efbfbdefbfbd1a2cefbfbdefbfbd43efbfbd06efbfbd01efbfbdefbfbd0228401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbdefbfbd4a1fefbfbdefbfbdefbfbdefbfbd7defbfbd78efbfbdefbfbd64efbfbdefbfbd67efbfbd58efbfbdefbfbd2edeb7efbfbd3e547cefbfbd2c62467fefbfbdefbfbdefbfbd44efbfbd50efbfbd7b775cd5a3efbfbd6b572defbfbd3cd4b864efbfbd5eecac97efbfbdefbfbdefbfbd1cefbfbdefbfbd5defbfbd77efbfbdefbfbdefbfbd1060efbfbd1d3eefbfbd5cefbfbdefbfbd19efbfbd443aefbfbd5a2fefbfbdd6af4c17efbfbdefbfbdefbfbdefbfbdefbfbd614cefbfbdefbfbd181b49efbfbdefbfbd10efbfbd21575befbfbd4a1fefbfbd6be7988466efbfbd0f276fdca4efbfbdefbfbd24efbfbd7a7240efbfbdefbfbd62efbfbdefbfbd285c6cefbfbd1cefbfbd376e35efbfbd7f7cefbfbddf93efbfbd0679efbfbd14efbfbdefbfbd0cefbfbd3667efbfbd31efbfbd76efbfbdefbfbdefbfbd7f6d3a70efbfbdefbfbdefbfbdefbfbd2f08efbfbd2c5b7146efbfbd312aefbfbdefbfbdefbfbd6b5defbfbd78c6bc38efbfbd6f3befbfbdefbfbd48efbfbd59efbfbdefbfbdefbfbd74efbfbdefbfbdefbfbdefbfbd2378efbfbdefbfbddf97efbfbd7befbfbd0c6befbfbdefbfbdefbfbd18560cefbfbd5b0befbfbdefbfbd5e7738efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbd6665243877725f5aefbfbdefbfbd376fefbfbdefbfbd2eefbfbd1befbfbd54260f0fefbfbddebe78efbfbd0a5defbfbd7661efbfbd362befbfbdefbfbd0261efbfbdefbfbd2defbfbd05efbfbd217e363aefbfbdefbfbdefbfbd0855efbfbdefbfbdefbfbd627aefbfbdefbfbd44efbfbd68efbfbd19efbfbd59efbfbd5fefbfbdd89f64efbfbd07473853580d4d0aefbfbd3aefbfbd5e4fd3846d60efbfbdefbfbdefbfbdefbfbdefbfbd1f1a4861efbfbdefbfbd23efbfbd077769efbfbd5eefbfbdc7b43466efbfbd00efbfbdefbfbd501eefbfbdefbfbdefbfbdefbfbd7740efbfbdefbfbd2f12efbfbd05efbfbdefbfbd1aefbfbdefbfbd7d573aefbfbd461fefbfbdefbfbd3eefbfbd6e39efbfbd3aefbfbdeb9cb830efbfbdefbfbdefbfbdefbfbd6aefbfbdefbfbdd7ae302cefbfbd75efbfbd72efbfbdefbfbd7befbfbdefbfbd2b177befbfbdefbfbd3943265951efbfbd0713efbfbdefbfbdefbfbd71efbfbd39efbfbd2773efbfbd29efbfbdefbfbd0170efbfbdefbfbd4a1ded868d5e39efbfbdefbfbdefbfbdefbfbd55efbfbdefbfbd42efbfbd40efbfbdefbfbd2befbfbd01efbfbd1aefbfbdefbfbd075d76787befbfbdefbfbdefbfbd1d3333efbfbd4307efbfbd1defbfbdc29c42efbfbd41417cefbfbddba3efbfbd29efbfbdefbfbdefbfbd4c6d45092b2cccbeefbfbdefbfbd65efbfbd29efbfbdefbfbd0b5561efbfbdefbfbdefbfbd58745f53734b6eefbfbdcea92b57efbfbd35efbfbdefbfbdefbfbd6defbfbdefbfbd73d7a8efbfbd4cefbfbd21efbfbdefbfbdefbfbd77efbfbd56efbfbdefbfbdefbfbdefbfbd11efbfbdefbfbdefbfbdefbfbd0576efbfbdefbfbd5fefbfbd1639dbbb52ccbb64d3abefbfbd6f1defbfbdefbfbdefbfbd5470efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0cefbfbd0c18184c1befbfbdefbfbd0e3755efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6cefbfbdefbfbdefbfbdefbfbd7eefbfbdefbfbd3fefbfbd6aefbfbdefbfbd2aefbfbdefbfbdefbfbd382fefbfbdefbfbdefbfbd3fefbfbd5c1cefbfbdefbfbd42efbfbdefbfbdefbfbd4cefbfbdefbfbd7975efbfbdefbfbd11efbfbdefbfbdefbfbd2befbfbd1e1defbfbd57283a31d59768efbfbdefbfbdefbfbd6067efbfbdefbfbdefbfbd6eefbfbd0576efbfbd7defbfbd106cefbfbdefbfbdefbfbdefbfbdefbfbd72efbfbd263c29efbfbdefbfbdefbfbdefbfbdefbfbd24efbfbd51532255efbfbd3870efbfbdefbfbdefbfbd34efbfbdefbfbdd3aaefbfbd5427efbfbdefbfbd71efbfbdefbfbdefbfbd44efbfbd775656dc907a0eefbfbd7864efbfbdefbfbdefbfbd6eefbfbd12efbfbdefbfbd4e41efbfbd6cefbfbdd88e2728efbfbdefbfbdefbfbdefbfbd0d3d69011b27387cefbfbd5cefbfbd23efbfbdefbfbdefbfbd1f1241efbfbd720b155eefbfbdefbfbdefbfbd18efbfbdefbfbdefbfbd0716d8b12e3eefbfbdefbfbdefbfbd090747efbfbdefbfbd41efbfbd7cefbfbdefbfbdefbfbdefbfbd79efbfbd7defbfbd1836383c73efbfbd21efbfbdefbfbdefbfbd22213befbfbdefbfbdefbfbd7defbfbd05efbfbdc69c49efbfbdefbfbdefbfbd796f27efbfbdefbfbdefbfbd27efbfbdefbfbdefbfbd6121efbfbdefbfbd10efbfbdefbfbdefbfbd61efbfbd265322661fefbfbd07efbfbd103f31efbfbdefbfbdd8b1461f24efbfbdefbfbd7ceab7aa2266efbfbdefbfbd3533efbfbd5f7c536defbfbd72efbfbd09efbfbdefbfbdefbfbdefbfbd62efbfbd3662efbfbd173f2025efbfbd6befbfbdefbfbd72efbfbdefbfbd6d772543141cd0a9efbfbd0e39efbfbd61efbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd32efbfbdefbfbdefbfbdefbfbd69c39aefbfbd36efbfbdefbfbd734befbfbd6f72c89aefbfbdefbfbd764eefbfbdefbfbd4251247145efbfbd1defbfbdefbfbd7924545defbfbdefbfbd00efbfbd36efbfbd4556efbfbdefbfbd3318d4abefbfbd0d3b4eefbfbd63efbfbdefbfbd720d60efbfbd20efbfbd396befbfbd052cefbfbd7519efbfbdefbfbdd2a91aefbfbd6eefbfbd3defbfbd714b1befbfbd4cefbfbdefbfbd77efbfbdefbfbd03d788efbfbd21561803efbfbdefbfbdefbfbd693befbfbd4765efbfbd18efbfbdefbfbdefbfbd6e13efbfbdefbfbd26efbfbdefbfbd7eefbfbdefbfbd226eefbfbd4befbfbd2905efbfbdefbfbdefbfbdefbfbd1247efbfbd494fefbfbdefbfbdefbfbdefbfbd114166274cefbfbd56126cefbfbdd5afefbfbdefbfbdefbfbdefbfbdc7833defbfbdefbfbd57efbfbdefbfbd31efbfbdefbfbd4defbfbd3c274d54efbfbd73efbfbdefbfbdefbfbd293105efbfbd36efbfbdefbfbd272defbfbd4defbfbd2c2cefbfbd6cefbfbd2eefbfbdefbfbd6b557cefbfbdefbfbd5959efbfbd24efbfbdefbfbd3873efbfbdefbfbd43efbfbdefbfbd6fefbfbdefbfbdefbfbd6bcaab493fefbfbdefbfbd5775efbfbd7cefbfbdefbfbd2d30efbfbdefbfbdefbfbd52efbfbdefbfbd3fefbfbd2a50efbfbd14efbfbd7befbfbdefbfbdd3a6efbfbd6befbfbd6aefbfbdefbfbdefbfbdefbfbd3106efbfbdefbfbdefbfbd2a2429473f567d45efbfbdefbfbd74eeafa9efbfbd51efbfbd0e766876efbfbd701c53d384efbfbdefbfbdefbfbd2f29efbfbd7aefbfbdefbfbd09efbfbdefbfbdefbfbd2465722defbfbd40efbfbd1875755a2eefbfbdefbfbd4aefbfbdefbfbdefbfbd7512706e69efbfbd64efbfbdefbfbdefbfbdefbfbdefbfbd6a25efbfbdefbfbd6f2a15efbfbdefbfbd62201fefbfbd56efbfbd1fefbfbdefbfbdefbfbdefbfbd1ec685efbfbdefbfbd41efbfbd6919efbfbd66efbfbdefbfbd427e7defbfbdefbfbd4eefbfbdefbfbd38efbfbd0fefbfbd4a6fefbfbd1defbfbdefbfbd0c5d1016efbfbd31efbfbd5e4defbfbd3fefbfbdefbfbdd69fefbfbdefbfbd3cefbfbd0c7f7d7befbfbdefbfbd3fefbfbdefbfbd181b355c7befbfbd52efbfbdefbfbdefbfbd79efbfbdcd81cfbcefbfbdefbfbd7c2a40efbfbdefbfbdefbfbd3defbfbd50794d517d7466efbfbdefbfbdefbfbd57efbfbdefbfbdefbfbd44efbfbdc5bb753d4067efbfbd3e3445efbfbd7defbfbd4befbfbd6a4b7847d99cefbfbd7befbfbd3e74efbfbdc888d29c53efbfbdefbfbd0c1b5b5a3470445c770c27efbfbd193a71efbfbd481befbfbdefbfbdefbfbd16efbfbdef98b7efbfbd13d6b90f67efbfbd362cefbfbdefbfbd5e6aefbfbd62efbfbdefbfbd237016efbfbdefbfbd53efbfbdefbfbd2363efbfbddfaf733d3cefbfbd62434e5d6befbfbd63efbfbd2e07efbfbdefbfbd20311defbfbd0cefbfbd07efbfbd2f0ef2b4a9ae636b6befbfbdd58666efbfbdefbfbd2a2f68701864efbfbd25efbfbdefbfbd40efbfbdefbfbd6c425befbfbd7738efbfbd06efbfbdefbfbd4a72efbfbd2971efbfbd565a4defbfbd4f641c3f525c45efbfbdefbfbd46c6a15e7befbfbd461fefbfbd67efbfbd34cfa6efbfbdefbfbd593eefbfbd6defbfbd6cefbfbdefbfbd2f007a16efbfbdefbfbdefbfbdefbfbd66efbfbd1fefbfbd6351efbfbdefbfbdefbfbd61efbfbd6879efbfbd343cefbfbdefbfbd1e623befbfbdefbfbdefbfbdefbfbd5e7a766befbfbdefbfbdefbfbd646718efbfbdefbfbd6d30efbfbd4963efbfbd6befbfbdefbfbd2befbfbdefbfbdefbfbdefbfbd60efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdda86efbfbdc6b0efbfbd5f7ec48f654aefbfbd392211efbfbdefbfbdc7ab7a0b0807efbfbd27397e464aefbfbd4509efbfbd32547befbfbd04664d34efbfbd3e0befbfbd75efbfbd5363df89efbfbdefbfbdefbfbd55d7a90defbfbdefbfbdefbfbd6a7c3b1cefbfbd7fefbfbdefbfbd4befbfbdefbfbd41efbfbd23050e3c2153efbfbd20efbfbd0cefbfbdefbfbdc69aefbfbdefbfbd16efbfbd3befbfbdefbfbdefbfbd11efbfbde6b69957626f7cefbfbdefbfbddaa8efbfbd7a72d78303efbfbd411cd79a2defbfbd38efbfbd5976efbfbdefbfbd1aefbfbdc7804defbfbd36efbfbdefbfbd275b6504efbfbd2d5870efbfbd7befbfbdefbfbdefbfbd75efbfbd0f7d5fefbfbdefbfbdefbfbd600eefbfbd1befbfbd3cefbfbdefbfbdefbfbd5cefbfbdefbfbdc98fccbc730207efbfbd3060efbfbd3e70efbfbdefbfbd2828efbfbdefbfbd1b68efbfbd39efbfbddba062370a1c1bcba54a6d775510efbfbd51efbfbd7befbfbd50efbfbdefbfbd290b4defbfbdefbfbdefbfbd4defbfbd4848efbfbdefbfbdefbfbd502fefbfbd7760efbfbd3eefbfbdefbfbd5c7dc3a53d17efbfbdefbfbd0e74efbfbd607a1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5639305977efbfbd42efbfbd10efbfbd47efbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd5612367a1aefbfbdefbfbdefbfbdefbfbd0656efbfbdefbfbd3c36efbfbd38efbfbdcf9e4942efbfbdefbfbd37efbfbdefbfbd51efbfbdefbfbd76efbfbd1fefbfbd1118557befbfbd68efbfbd4aefbfbdefbfbd25efbfbd79efbfbdefbfbd02efbfbdefbfbdefbfbd575aefbfbd6a59c981efbfbdefbfbd3266deb7efbfbdefbfbd40efbfbdefbfbd2b2d38efbfbd67efbfbdefbfbd73efbfbd29efbfbdefbfbd69efbfbd4a0b65efbfbd3b64efbfbd29efbfbdefbfbdefbfbdefbfbd4defbfbd2cefbfbd3e4defbfbd427c53efbfbd09efbfbdefbfbdefbfbd5a007d3cefbfbd74efbfbd6e512b77efbfbd6aefbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd0340711fefbfbdefbfbd3c3410efbfbdefbfbd2b6aefbfbdefbfbd2edcb55f1515efbfbd266e191730efbfbdefbfbd4a37efbfbd7578efbfbdefbfbd53efbfbd61efbfbdefbfbdefbfbdefbfbd501f74c99e1fefbfbd1cefbfbd42efbfbd63efbfbdefbfbdefbfbd52efbfbd40636a69352eefbfbdefbfbd725defbfbd4e681a1aefbfbdd185041b2befbfbdefbfbdefbfbdefbfbd42efbfbdefbfbd2038efbfbdefbfbdefbfbdefbfbd737e4f5defbfbdefbfbd35efbfbd0b10d789efbfbdefbfbd3c5c2eefbfbd310cefbfbdefbfbdefbfbd58767fefbfbdefbfbd0671efbfbd4e527f217f674d72efbfbdefbfbdefbfbd4d25691318efbfbd665d514d43033a21efbfbdefbfbd31efbfbdefbfbd522befbfbd4b753eefbfbdefbfbdefbfbd2815efbfbdefbfbd322e505c2fefbfbd35efbfbdefbfbd1aefbfbdefbfbd525e2fefbfbd327defbfbd76efbfbd5131efbfbd51cd87efbfbd3deea5a8efbfbd01efbfbdefbfbdefbfbd13efbfbdefbfbd39efbfbd360d0a2406efbfbdefbfbdefbfbd31efbfbd0b60efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd05efbfbdefbfbd430fefbfbdefbfbd7fcbb800707fefbfbdefbfbd0befbfbdefbfbdefbfbdefbfbd2fefbfbd3eefbfbd42efbfbdefbfbdefbfbdefbfbd40efbfbd63efbfbd3eefbfbdefbfbdefbfbd7e3c27efbfbd1fed8f9d38efbfbd1a203eefbfbdefbfbd316aefbfbdefbfbdefbfbd57efbfbdefbfbd17efbfbd13efbfbdefbfbd5fefbfbdefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd3eefbfbd3eefbfbdefbfbdd595efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd52efbfbdefbfbd19efbfbd5472e4b79b644654efbfbd263cefbfbd475021efbfbdefbfbdefbfbdefbfbdefbfbd17efbfbd21cca3efbfbd48195eefbfbd48efbfbdefbfbd5c5d19efbfbd792cefbfbd07efbfbd67efbfbd48efbfbd76ccbc7ec68defbfbdefbfbdefbfbdc387655d516defbfbd2f3aefbfbd4befbfbd2b6f65efbfbdefbfbdefbfbd5defbfbd3aefbfbd09efbfbd297a43efbfbd057a2befbfbdefbfbd1eefbfbd4f45310e72efbfbd4e4cefbfbd3605d4ab722cefbfbd6b0fefbfbd7f2cefbfbd4763efbfbdefbfbd333a3f371b272f71efbfbdefbfbd3255efbfbd37efbfbd220befbfbdefbfbdefbfbdefbfbdefbfbd6547efbfbd5defbfbd38efbfbdd69e0a7d3cefbfbdefbfbd48efbfbdefbfbd277002efbfbd7147efbfbdefbfbd327defbfbdefbfbdefbfbdefbfbd5aefbfbd4dd1a97375efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7befbfbdefbfbd3fe999b4caad07efbfbdefbfbd69220befbfbd6ae79fb205efbfbdefbfbd4aefbfbd73654b0befbfbd0175252e57efbfbd6aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd327d100eefbfbd53efbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdc4b9434cefbfbd0133265b51545befbfbd14774eefbfbd3ec6b0efbfbd7aefbfbdefbfbd1378efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd012fefbfbd3702efbfbd750005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f00efbfbd65efbfbdefbfbdefbfbd1c5befbfbdefbfbd556edca429efbfbd02dc94efbfbdefbfbdefbfbd3a420e4dc68eefbfbdefbfbd49efbfbd1e14efbfbdefbfbd55efbfbd771cefbfbd450d77efbfbdefbfbd5676efbfbd2355efbfbd68efbfbd31082fefbfbd79efbfbd6cefbfbd4d6a74efbfbd1ec3a9efbfbd29efbfbd54efbfbdd6a5efbfbd54efbfbd27efbfbd4f72631aefbfbd345eefbfbd74efbfbd6e5cefbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd3de39795efbfbd4cefbfbd0eefbfbdefbfbdefbfbd5b6b1326efbfbd725aefbfbdefbfbdefbfbd3defbfbdefbfbd36cd8061efbfbdefbfbd02efbfbdefbfbd4aefbfbdefbfbdefbfbd0a64efbfbd3b5eefbfbd58efbfbd4e567051efbfbd1921efbfbd2cefbfbdefbfbd1a2fefbfbd5c57efbfbd6cefbfbd622aefbfbd2f60efbfbd3e08efbfbdefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbd37efbfbd5f76efbfbd6fefbfbd33137d6e1fefbfbd683b7e28efbfbdefbfbd3aefbfbd657943efbfbdefbfbd5a726c77efbfbdefbfbd573d7fefbfbdefbfbd24d0a124efbfbd0a7eefbfbdefbfbdefbfbd6fefbfbd4e4a602b6936efbfbdefbfbdefbfbd73efbfbd331d7edc90efbfbd28efbfbdefbfbd61245b48efbfbd5fefbfbdefbfbd6b194f7eefbfbd7aefbfbd5675efbfbdefbfbd55efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd61efbfbdefbfbd10efbfbdefbfbd21efbfbdc78eefbfbd23efbfbd03efbfbdefbfbd6fe7a587efbfbdefbfbdefbfbd3628efbfbd555922efbfbddb8aefbfbd4aefbfbd0f6f7e6d3b21efbfbd6857efbfbdefbfbd796104efbfbd33efbfbd4d5fefbfbdefbfbd6c7f636100efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbd24efbfbd7adca26949062defbfbdefbfbdefbfbd62efbfbd5befbfbd3441ca8e1fefbfbdcc9cefbfbddc9f7b323b77efbfbd0653efbfbdefbfbdefbfbdefbfbdefbfbd48efbfbdefbfbd125defbfbd4e7966efbfbd07766befbfbdefbfbdefbfbd4862c6baefbfbd3b7815e7b9a64eefbfbd39efbfbdefbfbd1646efbfbdefbfbd5d70efbfbdefbfbddcbeefbfbd65efbfbdefbfbd6b25efbfbd10efbfbd57efbfbdefbfbd5defbfbdefbfbdefbfbd752a3d46efbfbdefbfbdefbfbd0cefbfbdefbfbd71efbfbd62efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3d5befbfbdefbfbd11efbfbd050cefbfbdefbfbd4a6e6b031a1befbfbd6c1cc3b91e6e65efbfbdefbfbdefbfbdefbfbdefbfbd0d62601a41506a42efbfbd66efbfbd46efbfbd5e4935caabefbfbd293649efbfbd18efbfbdefbfbd69efbfbdefbfbd3f3c28efbfbd07efbfbdefbfbd4aefbfbd5c5176efbfbd504befbfbdefbfbdefbfbdefbfbdefbfbd3cefbfbd5e7c636f4defbfbd235ed585efbfbd72efbfbd14efbfbdefbfbd1127efbfbd2a3defbfbd2cefbfbdefbfbd603cefbfbdefbfbd643f723459efbfbd69efbfbd0539efbfbdefbfbd353eefbfbd41efbfbdefbfbdefbfbd3eefbfbd7268efbfbd1a6aefbfbdefbfbdefbfbd301764efbfbdefbfbdefbfbdefbfbd57efbfbdc9b11eefbfbd5315efbfbd38017653efbfbd52efbfbd16efbfbd38efbfbd70efbfbd3e2cefbfbdefbfbdd0a46874efbfbd43efbfbdefbfbd70efbfbd4d6a5415efbfbd0cefbfbd713358736037efbfbddb9defbfbdefbfbdefbfbd60efbfbd3e182eefbfbd4f3d6defbfbd7defbfbd292961efbfbd6933e985a7305ddd9fefbfbdefbfbd5163efbfbddd9b7eefbfbdefbfbd4c0c5befbfbdefbfbdefbfbdefbfbd4befbfbd35efbfbd4d74317c66efbfbdefbfbd3cefbfbd5defbfbd5671efbfbd4aefbfbd68efbfbd2c57c38defbfbd7e7fefbfbd39efbfbdefbfbdefbfbd5ed98409c3bcefbfbdefbfbd53efbfbdefbfbd31efbfbd6f18efbfbd1e1fefbfbd4eefbfbd26efbfbd1f694a4ac2b4257bce89071b4befbfbd46efbfbd5b1261ce991eddb6d4a9631734efbfbd2aefbfbd4aefbfbdefbfbdefbfbdefbfbdefbfbd11c69438efbfbdefbfbd443f0cefbfbd3eefbfbdefbfbdefbfbd30e3a7a6222fefbfbdefbfbd4c183a72efbfbdefbfbd761a48301937efbfbdefbfbdefbfbd2aefbfbd6fefbfbd03efbfbd7befbfbd53efbfbd366662efbfbd3d5dc98defbfbdefbfbd71671aefbfbd2b63efbfbd05efbfbd49efbfbd6159efbfbd7fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd063b7579d6ba721f782cefbfbd07efbfbdefbfbdefbfbd5a0fefbfbd67efbfbdefbfbdefbfbdefbfbdefbfbd5befbfbd7e7c3175efbfbd65efbfbdefbfbd173e7c3c10efbfbd5fefbfbdefbfbdefbfbd7f5aefbfbdefbfbd787766efbfbd7cefbfbd64efbfbd474563efbfbd192539501d34d48f7defbfbd15efbfbd08efbfbd6a6b77644defbfbdefbfbdefbfbdefbfbdefbfbd3f1defbfbd4c79efbfbd646157efbfbd5932efbfbdefbfbd532d68efbfbd7f39efbfbdefbfbd390b1e1b24223befbfbdefbfbd5fefbfbd49efbfbdefbfbd7f2609efbfbd57efbfbdefbfbdefbfbdefbfbd696fefbfbdefbfbd4c3defbfbd20efbfbdefbfbdefbfbdefbfbd073b70efbfbd6cefbfbdd5af4fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbddaaa33efbfbd771cefbfbdefbfbdefbfbd1fefbfbd4cefbfbdefbfbd6befbfbd5fefbfbdccb11b10123172efbfbd1cefbfbdc6975f59efbfbd45efbfbd06efbfbd6fefbfbd67efbfbd28efbfbd2fefbfbdefbfbdefbfbdefbfbd51efbfbdefbfbd3b25efbfbdefbfbd777cefbfbdefbfbd5c3c24efbfbdefbfbddcbc5349db87172c7befbfbdefbfbd776fefbfbd7aefbfbdefbfbd34efbfbdefbfbdefbfbd45efbfbd16efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1aefbfbdefbfbd6913efbfbd1e5d79efbfbdefbfbdefbfbdefbfbdefbfbd45efbfbd3f7d56efbfbdefbfbdcba96aefbfbdefbfbd1d552248efbfbdd5b07defbfbd236747efbfbd3defbfbde48c94efbfbd6c26efbfbd0874cb98efbfbd77efbfbdefbfbd1b1e030309ddb259efbfbd554c5aefbfbdefbfbdefbfbdee899f25efbfbdefbfbd31efbfbdefbfbdefbfbd11efbfbd3cefbfbdefbfbdefbfbd514e0f1356efbfbd43796defbfbdefbfbd3a1f6fc7aaefbfbd5fefbfbdefbfbd1befbfbdefbfbdefbfbd3328efbfbdefbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd7d7520efbfbd191656efbfbdefbfbdefbfbd6e63efbfbd2805efbfbdc789efbfbdefbfbdefbfbdefbfbdefbfbd01efbfbddd97585cd5895aefbfbd69614c4c2b1aefbfbd5074efbfbd1533efbfbdefbfbd6b2817efbfbdefbfbdefbfbd292f1cefbfbd1a3424efbfbdefbfbd52efbfbd7839efbfbdefbfbdefbfbdefbfbd073f7aefbfbdefbfbd0eefbfbdefbfbdefbfbdefbfbd6a69690d4defbfbdefbfbdefbfbd31efbfbdefbfbdefbfbdefbfbd305cefbfbd48efbfbdefbfbd6a4317efbfbdefbfbd087eefbfbd77617b0e0cefbfbdefbfbd1aefbfbd5aefbfbd5071efbfbd486344efbfbd17efbfbdefbfbdefbfbd630f62efbfbdefbfbd5befbfbd7e65efbfbdefbfbdefbfbd44efbfbd26efbfbdefbfbd7cefbfbd2defbfbdefbfbdefbfbdefbfbdefbfbd43efbfbd18efbfbdefbfbdefbfbd1f595fefbfbd53efbfbdefbfbd4defbfbd44efbfbdefbfbdefbfbdefbfbd6defbfbdefbfbd68efbfbdefbfbd7e67efbfbd1cefbfbdefbfbd61735f5defbfbd72efbfbdefbfbd5319efbfbdefbfbdd88e56efbfbdefbfbd05eb97b5efbfbd79efbfbd7973efbfbdefbfbddaaaefbfbdc79f5cefbfbd28301937efbfbd4c6eefbfbd66efbfbdefbfbdefbfbd0d7a3556efbfbdefbfbdd2a6efbfbd76efbfbd3e79390a6f31efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd374640efbfbdefbfbd63efbfbd79efbfbdefbfbd2aefbfbd7330031f5b14efbfbdd395efbfbd49efbfbdefbfbd07efbfbd551e5befbfbdefbfbdefbfbd1b5eefbfbd7b7cefbfbdefbfbdefbfbdefbfbd306f63efbfbdefbfbd656362efbfbd4cefbfbdefbfbdefbfbd61efbfbd3eefbfbd2148efbfbd17efbfbd14efbfbd08190263efbfbdefbfbd0f37efbfbd1cefbfbd45efbfbdefbfbd5a0d26efbfbd3321efbfbd3eefbfbd15efbfbd102befbfbdefbfbd39cf8f337aefbfbd035aefbfbd4d7176efbfbdefbfbd55efbfbd20efbfbdefbfbd142befbfbd1139631836efbfbdefbfbd737defbfbd57efbfbd58efbfbdefbfbd4fefbfbd3e322d6959efbfbd2befbfbdefbfbd6fefbfbd73efbfbdefbfbd79efbfbdefbfbd3eefbfbdefbfbdefbfbddb8337efbfbd385955efbfbdefbfbdefbfbdefbfbdefbfbd03efbfbdefa6a77defbfbdefbfbdefbfbd27efbfbd5e74efbfbdefbfbdc592efbfbd6a087fefbfbdefbfbd51efbfbdefbfbd5777efbfbdefbfbd763f56efbfbd235aefbfbdeebd9fefbfbd41efbfbd31224631efbfbdefbfbd3053efbfbdefbfbd1c5730efbfbd29efbfbdefbfbd34efbfbd68efbfbd3aefbfbdefbfbdefbfbd4fefbfbd79efbfbdefbfbdefbfbdefbfbd3830efbfbd47efbfbd421fefbfbd6f7cefbfbddabcefbfbdefbfbdc59eefbfbd20efbfbd31efbfbd60efbfbd19efbfbd1befbfbdefbfbd2961efbfbd0f2b4ce190b8efbfbd59efbfbdefbfbd143305efbfbd1befbfbd13efbfbd245d1eefbfbd7048efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7301efbfbd4cefbfbdefbfbd6e72d7af3eefbfbd102666efbfbdefbfbd63efbfbdefbfbd32780fefbfbd07efbfbd1b7d5defbfbd7fefbfbdd58f1a23d8be66673e23efbfbdefbfbdefbfbd2303efbfbdefbfbd75efbfbdefbfbdefbfbdefbfbdefbfbd7962efbfbd65efbfbdd08aefbfbd3cefbfbd5415efbfbd48efbfbdefbfbd1dd783efbfbdefbfbd5d5aefbfbdefbfbd04efbfbd38efbfbdd9b9efbfbd44440f55d9aaefbfbdefbfbdefbfbdefbfbdefbfbd432441ca8eefbfbdefbfbdefbfbd46103d61efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd163befbfbdefbfbd157933efbfbd3815efbfbdefbfbd06efbfbd6eefbfbd244fefbfbdefbfbdefbfbd5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdde9015efbfbd3e7950302aefbfbd72efbfbdefbfbd2befbfbdefbfbdefbfbd2c49296974efbfbdefbfbdefbfbd15efbfbd7239efbfbdefbfbdefbfbd4971165defbfbdefbfbdefbfbd0a5aefbfbdefbfbd5d07443b50efbfbdefbfbd09efbfbdefbfbd2befbfbd62efbfbd18dfbdefbfbd48082eefbfbdefbfbd2874efbfbd545fefbfbd2a644c2f17efbfbdefbfbd54efbfbd35efbfbd31efbfbdefbfbdefbfbdefbfbd304defbfbdefbfbdefbfbd170812efbfbdefbfbdefbfbd296b7cefbfbdefbfbd375f1737efbfbdefbfbdefbfbd78efbfbd3768efbfbd5aefbfbd63efbfbd57efbfbd32efbfbdefbfbd0defbfbd7627583cefbfbdefbfbd42efbfbdefbfbdefbfbd576859762e3c7defbfbd28efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbd607befbfbd5eefbfbd50795b72efbfbd5aefbfbdefbfbd631aefbfbdefbfbd5fefbfbdefbfbdcc8e387defbfbd4a71efbfbdefbfbdefbfbdefbfbdefbfbd69efbfbdefbfbd5471efbfbdefbfbd7636176b4a4a64efbfbd30efbfbd03efbfbd7eefbfbd15efbfbdefbfbdefbfbd2fefbfbd35efbfbdefbfbdefbfbde7b7a67b0647654e7cefbfbd5defbfbdefbfbd1834efbfbdefbfbdefbfbd6c4cefbfbdefbfbd01cdb3cf86efbfbd366fefbfbd7a4defbfbd140d7864efbfbd00efbfbd5e74efbfbdefbfbdefbfbd64efbfbd74efbfbdefbfbdefbfbd6defbfbdefbfbd74efbfbd5c735aefbfbd10efbfbd67efbfbdefbfbdefbfbd58efbfbd62efbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbd26efbfbd3defbfbd58efbfbdefbfbdefbfbd19efbfbdea9db57a68efbfbd541f5defbfbd4f041e6e7aefbfbd5f0fefbfbd5a401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f00efbfbdefbfbd07efbfbdefbfbd48efbfbd567c7eefbfbd0000000049454e44efbfbd4260efbfbd504b0304140008080800efbfbd60efbfbd420000000000000000000000000b000000636f6e74656e742e786d6cefbfbd1defbfbd72dbb8efbfbdefbfbd5fefbfbd72efbfbd7defbfbd2cefbfbdefbfbdefbfbdefbfbd76efbfbdd8bbdd9924efbfbdefbfbd286d673aefbfbd0c4c4232efbfbdefbfbd15efbfbd2cefbfbdefbfbd7defbfbdefbfbdefbfbd57efbfbd4b7a005eccbb48efbfbd12efbfbd4defbfbdefbfbd48efbfbd39efbfbdefbfbd5f40efbfbd7eefbfbddd83efbfbd0cefbfbd31efbfbdefbfbdefbfbd2d255dd1a401efbfbd4cdfb2efbfbdefbfbd52efbfbdefbfbdefbfbd5eefbfbd49dfad7eefbfbdefbfbdefbfbd6c6c132f2cefbfbddcb9efbfbd63efbfbdefbfbd7b0cefbfbd1f00efbfbd4717efbfbdefbfbd52efbfbd116fefbfbd236ad385efbfbd5c4c17efbfbd5cefbfbd01efbfbd62efbfbd451a7a21efbfbd0a47287b741aefbfbd0befbfbd3436efbfbd0fefbfbd2932efbfbdefbfbdefbfbdefbfbdefbfbd3b0befbfbd34efbfbd45d0be2932efbfbd05efbfbdefbfbdefbfbd377e53efbfbd07efbfbdefbfbd1b1fefbfbdefbfbd06efbfbdefbfbd392a1e1cefbfbdefbfbd7929efbfbd31162c5475efbfbdefbfbd2befbfbdefbfbde293adefbfbdefbfbdefbfbd7355efbfbd2604efbfbd095cefbfbd23efbfbdefbfbdefbfbd4c153befbfbd6f46555defbfbdefbfbd18efbfbdefbfbd0c35efbfbdefbfbdc3a649efbfbd76efbfbd2d26efbfbd45efbfbd182a68efbfbdefbfbd6f1b5befbfbdefbfbdefbfbd4234efbfbd1d22efbfbd6d430067efbfbd3befbfbdefbfbdefbfbd7768efbfbd715defbfbdefbfbd2a743253efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2d10efbfbdefbfbd5e1c36232aefbfbdefbfbd41633643efbfbd34efbfbdefbfbdefbfbd09efbfbd1c21745041efbfbdefbfbd692335efbfbdefbfbdefbfbdefbfbdd782efbfbdefbfbd3049efbfbdefbfbdefbfbdefbfbd2672efbfbd44efbfbd5b2634efbfbdefbfbd55efbfbdefbfbdefbfbd3d37efbfbdefbfbdefbfbd206805efbfbdefbfbdefbfbdefbfbd0930efbfbd2aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd2760efbfbd30efbfbd6c7befbfbd21efbfbd4932efbfbd2befbfbdefbfbdd3b14a70efbfbd13efbfbd0866efbfbd3c60efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd3aefbfbdefbfbdefbfbd6763efbfbd2defbfbdefbfbd52502067efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd36efbfbd7f23652279efbfbd41efbfbd730621efbfbdefbfbd211401efbfbdefbfbdefbfbdefbfbd08efbfbdefbfbd72efbfbdc48defbfbd44efbfbdefbfbd3cefbfbd267968efbfbdefbfbd3c600272572440efbfbd106062efbfbd29efbfbd08efbfbd4566efbfbd4cefbfbdefbfbdefbfbd13efbfbd77efbfbd7fefbfbd3240efbfbdefbfbd52efbfbd3658efbfbd1f2c52efbfbdefbfbd604edc8766efbfbd714befbfbdefbfbd4d7ec59c57efbfbdefbfbd0e59efbfbdefbfbdefbfbd3fefbfbd7c4eefbfbd0cefbfbd75efbfbd532aefbfbd1befbfbd2a4ed8a1efbfbd533519efbfbd40efbfbd37efbfbdc4b2efbfbd4defbfbdefbfbd5eefbfbdefbfbd37191eefbfbdefbfbd39efbfbd4befbfbd6fefbfbdefbfbd78254007607d10656330efbfbd761eefbfbdefbfbd1f51efbfbdefbfbd3fefbfbd60efbfbd0169efbfbd59efbfbdefbfbdefbfbd5befbfbd00200e10efbfbd455e0622efbfbdefbfbd09efbfbdefbfbd1e113b34efbfbdefbfbd1cefbfbdefbfbd14efbfbd101e64efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4677efbfbdefbfbd5e426c387308efbfbdefbfbdceb42d3478efbfbd3c3aefbfbdefbfbdefbfbd5007efbfbd4aefbfbd4b604b04efbfbd67efbfbd1103efbfbdefbfbdefbfbdefbfbd3418201defbfbdefbfbdefbfbdefbfbd4fefbfbd12efbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd03efbfbd5d01efbfbd5342503c5eefbfbd35efbfbddb94efbfbdefbfbdefbfbdefbfbd2d22efbfbdefbfbd57efbfbd240defbfbd401eefbfbdefbfbd52efbfbd1c321a473b066265efbfbd29efbfbd75124f153f733673efbfbdefbfbd61efbfbd5f44791870632cefbfbd4d0e20efbfbd60efbfbd6cefbfbd6f38efbfbdefbfbd2d5eefbfbd4c1563664c6cefbfbd6158efbfbd22efbfbdefbfbd42247111efbfbd42efbfbdefbfbd57efbfbd791d1c5d76efbfbdd297efbfbd4364762b1849efbfbd75efbfbd48e5aa944cefbfbdde9cefbfbdefbfbdefbfbd0d07efbfbd4447efbfbd11efbfbd3219efbfbdefbfbd76627f043b7216623cefbfbdefbfbd3fefbfbdefbfbdefbfbdefbfbd39281defbfbd67efbfbdefbfbdce8eefbfbdefbfbd55e7948e14efbfbdefbfbdefbfbd75efbfbd0e27efbfbd6cd69eefbfbdefbfbd330875361fefbfbdefbfbd48efbfbd69efbfbd2328efbfbdefbfbd5248efbfbdefbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd3defbfbd577a05efbfbdefbfbd71efbfbdefbfbd50efbfbdcc8defbfbd08efbfbd15efbfbdefbfbdefbfbdefbfbd0d6706270f466f7d6261223b7803efbfbdefbfbdefbfbd3defbfbd1e25efbfbdefbfbdefbfbd64efbfbdefbfbd4171efbfbdefbfbd67efbfbdefbfbdefbfbdefbfbd320ed880efbfbdefbfbd6d0defbfbdefbfbdc4bfefbfbdefbfbdefbfbdc7b80e18efbfbdefbfbd6e4cd0bbefbfbdefbfbd0344d096efbfbdefbfbd2eefbfbdefbfbd017e4e22efbfbdefbfbd21efbfbd5f30efbfbdefbfbd124a13efbfbd14efbfbd292e00efbfbd0f0a03efbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7c384defbfbd3f7b0c602befbfbdefbfbd143fefbfbdefbfbdd4baefbfbd1befbfbd6f74efbfbd3eefbfbd5eefbfbd2e23efbfbd61efbfbd280025efbfbd416aefbfbd0706753d60efbfbdefbfbdca8765e8a0905732efbfbd171227030e7e28efbfbd260472322fd09befbfbd40efbfbd1a7befbfbd015defbfbd18636eefbfbdefbfbd157eefbfbd2eefbfbd3a5defbfbd27efbfbd74efbfbd5cd8a363efbfbdefbfbd1eefbfbd730b11efbfbd13efbfbdefbfbdefbfbdefbfbd380c7fd08d58efbfbd2d31c48aefbfbd0befbfbd6735efbfbd7127efbfbd2befbfbd1f2e1421d3a860efbfbd5eefbfbdd6bf76efbfbdd99b47efbfbdefbfbdefbfbdc2a27befbfbdefbfbd4b69efbfbd1cefbfbdefbfbdc8bf4c754c7a37efbfbd67113eefbfbdefbfbdefbfbd01efbfbd1cefbfbd6d77680b435658efbfbdefbfbdefbfbdefbfbd6304efbfbdefbfbdefbfbd792672efbfbdefbfbd28efbfbd59efbfbdefbfbdefbfbdefbfbdefbfbd0961efbfbd4a06efbfbd3e6defbfbd594eefbfbdefbfbd16efbfbdefbfbd5715efbfbd32efbfbdefbfbdefbfbdefbfbd7455efbfbd1f55efbfbd776a58efbfbdefbfbd58efbfbdefbfbdefbfbd3cd682efbfbdefbfbdefbfbdefbfbddfa2efbfbd75efbfbdefbfbd0e4defbfbdefbfbd36efbfbd71efbfbdefbfbdefbfbd1eefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbdefbfbd53efbfbdefbfbdefbfbd29efbfbd757e524eefbfbdefbfbdefbfbd297a372defbfbd05efbfbd62213cefbfbd274b293e39efbfbd422a65efbfbdefbfbdefbfbd4b0befbfbdefbfbd070d7eefbfbd5513200aefbfbd2defbfbd142eefbfbd4250efbfbdefbfbd14efbfbd47efbfbdec9ea77738efbfbd553aefbfbdcebbefbfbdefbfbdefbfbd797befbfbdefbfbd45efbfbd1a3d00efbfbd2befbfbd50efbfbd1befbfbd4eefbfbd61efbfbdefbfbd41efbfbdefbfbd7c383d167befbfbd18efbfbd30efbfbd4eefbfbd4cefbfbdefbfbd2d75efbfbd2b5d26efbfbdefbfbd2322efbfbd7c5defbfbd71223aefbfbd1d27efbfbd71efbfbd01efbfbd75efbfbd78efbfbd7f5e4aefbfbd7eefbfbd066d1cefbfbdefbfbd0504efbfbdefbfbdefbfbd3e1a75013f5f58efbfbdd78b1a473f54efbfbdefbfbd1c75efbfbdc7b546511c12efbfbd5cefbfbd37efbfbd0defbfbd61efbfbd34efbfbd2f24efbfbd26efbfbd0defbfbd39efbfbdefbfbdefbfbd33efbfbd0576efbfbdefbfbdefbfbdc8a86230efbfbdefbfbd3353647b3e71efbfbd6aefbfbdefbfbd46275b65efbfbd20efbfbdefbfbd1332efbfbdefbfbd62efbfbdefbfbd48efbfbd67c99aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6b34133d4fefbfbd4c0959efbfbd206befbfbdefbfbdefbfbd1811efbfbd6fefbfbdc899efbfbd67efbfbd65efbfbdefbfbd2fefbfbdefbfbdefbfbd35efbfbdefbfbd57efbfbd057b7aefbfbdefbfbddc94efbfbd6e1aefbfbd66efbfbdefbfbd5cefbfbd2a39efbfbd4eefbfbdefbfbd2e08efbfbdefbfbd11efbfbdd5ba36efbfbd5befbfbd2701efbfbd612aefbfbd7defbfbd136cefbfbd0e6d0defbfbdefbfbdefbfbd70efbfbd5befbfbd31efbfbdefbfbd28efbfbd440ed0b6efbfbd27efbfbd531c2defbfbdefbfbdcf9defbfbd0d0f47efbfbd5defbfbd33efbfbdefbfbd62efbfbdefbfbd54efbfbd4b2a3cefbfbd0f3fefbfbd447a541fefbfbdefbfbdefbfbdefbfbd6aefbfbd3defbfbdefbfbd516706efbfbd02efbfbd25efbfbd625defbfbd677aefbfbdefbfbdefbfbd01efbfbdefbfbd7426e6bfa3efbfbd007defbfbd0cefbfbd1e7c4defbfbdefbfbd4a763a63efbfbdefbfbdefbfbd4f3aefbfbdefbfbd510747efbfbd5cefbfbd53cfa8efbfbdefbfbd29efbfbdefbfbd48efbfbdefbfbdefbfbd5a6d453015efbfbdeab4835a17efbfbd3503efbfbdefbfbd3e741fefbfbd7f24363aefbfbd23efbfbd512befbfbd140eefbfbdefbfbd2cefbfbd6aefbfbdefbfbd1cefbfbd55efbfbd307eefbfbdefbfbd2aefbfbdefbfbd5b16efbfbd562c164e193a64efbfbdefbfbdefbfbdefbfbd64efbfbd703cd185373cefbfbd2befbfbdefbfbdc2994167efbfbd7d16571fd785efbfbdefbfbdefbfbdefbfbd3eefbfbd5d5432efbfbd46efbfbdefbfbd59efbfbd49efbfbd151aefbfbdefbfbdefbfbdefbfbdefbfbd5eefbfbd59176fefbfbd7eefbfbd522b593d0168774d725defbfbd24da81583befbfbd2677424fefbfbd2e5e02efbfbd1877efbfbd636cefbfbd7c775b1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbdefbfbd60efbfbdefbfbd73efbfbd75efbfbd5260670cefbfbd4fefbfbd1defbfbd76efbfbdefbfbd4f2b1825efbfbdefbfbd46efbfbdefbfbdefbfbdefbfbd383c4b49d78cefbfbddeb5dbae68efbfbd24efbfbdc58eefbfbdcaa8efbfbd23efbfbd16efbfbd1327efbfbd20efbfbd17ce94efbfbdefbfbdefbfbd6546efbfbdefbfbd6139efbfbd51efbfbd7849efbfbdefbfbdefbfbd43efbfbdefbfbd62efbfbd76efbfbdefbfbdefbfbdefbfbd2432efbfbdefbfbd4befbfbd66c8b1cdacefbfbdefbfbdefbfbd58efbfbdefbfbd7c0b7adba5efbfbd63efbfbd6defbfbdefbfbd71efbfbd2eefbfbd5626efbfbdc28e6aefbfbd0b60efbfbd097e1b20efbfbdefbfbdefbfbd49efbfbddc89efbfbd5c68efbfbd4d616f7c7347efbfbd2aefbfbd641038efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd7b9245743efbfbd1b1229efbfbdefbfbd0e7b4fefbfbd290e0ec49065efbfbdefbfbd41efbfbdefbfbdefbfbd63efbfbd41efbfbdefbfbdefbfbd03efbfbd49efbfbd7428efbfbd1f1d67471911efbfbdefbfbd2adfa1efbfbd62efbfbdefbfbdefbfbd39efbfbdefbfbdefbfbd5575efbfbd22efbfbdefbfbd1b1defbfbd06efbfbdefbfbd16efbfbd28efbfbdefbfbd3defbfbd06e19b8b77ed889fefbfbd7b771216efbfbdefbfbd7cc2b1346234efbfbd41efbfbdefbfbd215209efbfbd5cefbfbdefbfbd3aefbfbdefbfbd2dc2abefbfbd08efbfbd6d116eefbfbd08efbfbdefbfbdefbfbd56efbfbdefbfbd532303efbfbdefbfbd3d69efbfbdefbfbd61397cefbfbd0656efbfbd1558efbfbdefbfbdefbfbdefbfbdefbfbd7279c489741c7065efbfbdefbfbdefbfbd0c25463c4b13efbfbd0defbfbd57efbfbdefbfbdefbfbdefbfbd67efbfbd1473efbfbd55efbfbdefbfbd034defbfbd387c1b1defbfbdefbfbdefbfbd40efbfbd3b0278efbfbd1332efbfbd566f31634a2d216aefbfbdefbfbd5fefbfbd1720efbfbdefbfbdefbfbdefbfbdefbfbd3c7879efbfbdc38f6f07efbfbdefbfbd62330c0947efbfbdefbfbdefbfbd090927efbfbdefbfbd6779efbfbd5cefbfbdefbfbdefbfbd0357efbfbd2eefbfbd2b4befbfbd4fd69303efbfbd4e1054efbfbdefbfbdefbfbdefbfbddf8110efbfbdc780efbfbd42efbfbd3441efbfbd12efbfbdefbfbdefbfbdefbfbd23efbfbdefbfbd321fefbfbdefbfbd5fefbfbdefbfbdddbcefbfbdefbfbd5aefbfbd051d66d89531efbfbdefbfbd4aefbfbd2b25efbfbd51efbfbd2f68efbfbdefbfbd7762e98991efbfbd34efbfbdefbfbd48efbfbd3e33efbfbdefbfbd5608efbfbd0e2362215eefbfbd22efbfbd7319efbfbdefbfbd05efbfbdefbfbdefbfbdefbfbd7defbfbd53d99b2d307a206628efbfbd680939efbfbdefbfbd73efbfbd79d48eefbfbd78efbfbd76efbfbdefbfbdefbfbdefbfbd2eefbfbd5fc3bf7c0f0e71684f5d5a2d4208507c5c05efbfbdefbfbd39631213efbfbd73efbfbd2518efbfbd5cefbfbdefbfbdefbfbdefbfbdefbfbd57efbfbd6726efbfbd7bc49eefbfbd607e203eefbfbd7defbfbd665023efbfbd4c0c2a5275efbfbd6fefbfbd467cefbfbdefbfbd3ce4a899331935735c137fefbfbdefbfbd05efbfbdefbfbdefbfbd01504b070860efbfbdefbfbd01750a0000efbfbd630000504b0304140008080800efbfbd60efbfbd420000000000000000000000000a0000007374796c65732e786d6cefbfbd3ddb92efbfbd36efbfbdefbfbdefbfbd2b584aefbfbd79efbfbd245277efbfbdefbfbd6c391e7b37551eefbfbd154f36efbfbd531009495c53efbfbdefbfbdefbfbd46efbfbd6cefbfbd653f637f727fefbfbd3440efbfbd02efbfbd004969264eefbfbd2aefbfbd021a40efbfbdd1b835efbfbdefbfbdefbfbdefbfbdefbfbdd59e70103aefbfbd77efbfbd33efbfbdc39eefbfbd3dcbb71d6f73efbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbdefbfbd6befbfbdefbfbd4bdbb70e3befbfbd457a18efbfbd5c1c6aefbfbdefbfbd0befbfbd71efbfbd4defbfbd10784b1fefbfbd4eefbfbdefbfbdefbfbd0eefbfbdefbfbdefbfbd5aefbfbd7befbfbdefbfbd464b117a49efbfbdefbfbd4b6867efbfbdefbfbd29efbfbdefbfbd3aefbfbdcf916c6302efbfbd6aefbfbd56efbfbd235360efbfbdefbfbd1defbfbdefbfbd6c63020b3c15efbfbdefbfbd7defbfbdefbfbdcfa1efbfbdefbfbd7defbfbdefbfbd777b1439192cefbfbd5defbfbdefbfbd72efbfbdefbfbd46efbfbd7e39181cefbfbdefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd582c16035aefbfbd206c2570efbfbd43efbfbd5228efbfbd1a6017efbfbdefbfbdc281efbfbd37061c76efbfbd2324efbfbd1fefbfbd1551efbfbd0eefbfbd150eefbfbd59efbfbd22efbfbdefbfbd6aefbfbdefbfbdefbfbdd688efbfbd4d096befbfbd2d0aefbfbd75efbfbd02efbfbdefbfbd3befbfbdefbfbdefbfbd3befbfbdc5b63b146d4b64321fefbfbd4325efbfbdefbfbdefbfbdefbfbd5917efbfbdefbfbdefbfbd580436efbfbd2a2b70efbfbdefbfbd64efbfbdefbfbd627befbfbdefbfbd135449efbfbdefbfbd4029efbfbdefbfbd70381ec4bf05efbfbd6325efbfbd3170221c08efbfbd5625efbfbdefbfbd5c2be1b8bf2b621aefbfbd1903efbfbdefbfbdefbfbd1351530e1d10efbfbd4b7befbfbd0c02efbfbdefbfbdefbfbd2841642defa0803b66625eefbfbd68e7969b17efbfbde5a09befbfbdefbfbd0b4101efbfbdefbfbd004c0d145d7f72efbfbdefbfbd5eefbfbd73560b60efbfbd11007543754d28efbfbdefbfbd2a1b18efbfbd01efbfbd49efbfbd06447276efbfbdefbfbd2671efbfbd6befbfbdefbfbd011130553006efbfbdefbfbd3d0e1c52efbfbd5cefbfbd6cefbfbdefbfbd2165efbfbd6138efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd37efbfbdefbfbd61d3833025efbfbdefbfbd5b3eefbfbdefbfbd7defbfbd7befbfbdefbfbdc2baefbfbd2d37efbfbdefbfbd3eefbfbd1b49efbfbd16efbfbd26efbfbdefbfbdefbfbd7e057f4befbfbd500361efbfbdefbfbdefbfbd603befbfbd3defbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd04efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd24efbfbdefbfbd067b4025efbfbd71efbfbdef9097efbfbdefbfbd3befbfbd05efbfbdefbfbdefbfbd0227efbfbd50471c4aefbfbdefbfbdefbfbd673d3cefbfbd56efbfbdefbfbd1b5423efbfbdefbfbdefbfbd30efbfbd51efbfbd6c5c53efbfbdefbfbdefbfbdefbfbd726cefbfbd7d465eefbfbdefbfbdefbfbd3930efbfbdefbfbd52efbfbd0b600b18efbfbd254fefbfbdefbfbd72efbfbdefbfbd740d5a166a1fefbfbd51efbfbdefbfbd09efbfbdefbfbdefbfbd0c5c2742efbfbd41efbfbd0defbfbdefbfbd0508efbfbdefbfbdefbfbdc3a313efbfbdefbfbdefbfbdefbfbd3eefbfbd20efbfbdefbfbdefbfbd2c11efbfbd24efbfbd21efbfbdd4a0efbfbd2059791c1f72efbfbd6defbfbd4607efbfbd45efbfbdefbfbd67efbfbdefbfbd2640efbfbdefbfbd63efbfbd382cefbfbdefbfbdefbfbd03705341efbfbd34efbfbdefbfbdefbfbd08efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd7cefbfbdefbfbdefbfbd69efbfbd692defbfbd2defbfbdefbfbdefbfbd0e03efbfbdefbfbdefbfbdefbfbd37efbfbd61efbfbd30efbfbdefbfbd15d69fefbfbdefbfbd11efbfbdefbfbd3aefbfbd49580fefbfbdefbfbd0267efbfbd6fefbfbdefbfbdefbfbdefbfbd274e2defbfbd36efbfbd55efbfbd4f044defbfbd00166616efbfbd7e73efbfbd45efbfbd32ceb940efbfbdd189efbfbd7a1cefbfbd46efbfbd4150efbfbd3d0a10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd10efbfbd640cefbfbd1eefbfbdefbfbd7e0cefbfbdefbfbdefbfbd36314fefbfbdefbfbd2aefbfbd08efbfbdefbfbd3002efbfbdefbfbd780defbfbdefbfbd096e3befbfbd16373d37d0a3554a511cefbfbdefbfbd643e252b04efbfbdefbfbdefbfbdefbfbd1aefbfbd214e640cefbfbd13e882bf0fefbfbd26efbfbdefbfbdefbfbdefbfbd13efbfbd73efbfbd1c420c5cefbfbdefbfbd6c6377efbfbd7eefbfbdefbfbd4154efbfbd16efbfbdefbfbd6fefbfbdefbfbd61efbfbd235aefbfbd226f73401b28efbfbd5c5a60efbfbdefbfbd1605efbfbd141f3fefbfbd2821efbfbd74efbfbd14efbfbdefbfbd5b33deb00e78efbfbd6fefbfbdcfbcefbfbd75efbfbd6b3cefbfbdefbfbdefbfbd2e49efbfbdefbfbdefbfbdefbfbd4eefbfbdda826eefbfbd3aefbfbdefbfbd7129db9231efbfbdefbfbdd3bd4aefbfbd01efbfbd6c4fefbfbd2defbfbdefbfbd7cefbfbdefbfbdc8b671efbfbd536cefbfbd05efbfbdefbfbdefbfbd4928efbfbdd4acefbfbdefbfbdefbfbdefbfbd43efbfbd21efbfbd600807efbfbd7450efbfbd7aefbfbdefbfbd2aefbfbdefbfbd0eefbfbdefbfbd470619efbfbd47efbfbdefbfbdefbfbd6c1769efbfbdefbfbd0343efbfbd56efbfbd40efbfbd04d1954c416defbfbdefbfbd74efbfbdd5a578eebea842efbfbdefbfbd13efbfbd2aefbfbdefbfbd3befbfbd5851efbfbd307708592d0befbfbd0e391eefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbdefbfbd21efbfbd66405aefbfbd431c290befbfbdefbfbdc5a2efbfbdc48befbfbdefbfbd1f10efbfbd27efbfbd050e39efbfbdefbfbd62efbfbdefbfbdefbfbd1e582713777a7028efbfbdefbfbdefbfbd17efbfbdefbfbd7aefbfbd6f70efbfbd25efbfbd6a62677503efbfbd03efbfbdefbfbdefbfbd19efbfbdefbfbd46efbfbdefbfbd2b75075c7c2e0a43400f2cefbfbdefbfbd43efbfbd0158efbfbd1772291d1d30efbfbdefbfbd57efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7cefbfbd596fefbfbd45efbfbd55efbfbdefbfbdefbfbdc4865526efbfbdefbfbd520befbfbdefbfbd4650efbfbd1a457800efbfbd3fefbfbdefbfbdc7956fefbfbd78efbfbdefbfbd54efbfbd2e3aefbfbd67084defbfbd2eefbfbd1528487603efbfbd623d2b52efbfbdefbfbdefbfbd33421be68486efbfbdefbfbdefbfbd7eefbfbd5c2e0cefbfbd5aefbfbd197fefbfbdefbfbd16efbfbd36efbfbd7aefbfbd2811efbfbdefbfbdefbfbd2ac6b54301cc8d60efbfbd7b1a034eefbfbd13276603efbfbd58efbfbd5144364cefbfbdefbfbdefbfbd7c3439cf8316efbfbd04efbfbd3f20371b7f25ce8446efbfbd1eefbfbd1eefbfbd7b44efbfbdefbfbdefbfbd2a05efbfbd63cb9aefbfbdefbfbdefbfbd4526efbfbd646e2befbfbd4861efbfbd1a37103fefbfbdefbfbd516c0b0c42332eefbfbd2232efbfbd52efbfbd1eefbfbdefbfbd4c0bcc9cefbfbd3132daba09efbfbdefbfbd6a2eefbfbdefbfbd02efbfbdd98cefbfbddd9aefbfbd29efbfbdefbfbd321674efbfbdefbfbd660b5defbfbd7cefbfbd4e41efbfbd15efbfbde7b4ba09efbfbd47efbfbd3c1fefbfbd3aefbfbdc7ab09efbfbdefbfbd00efbfbd16efbfbdefbfbdefbfbdefbfbdef9f873072efbfbd27efbfbd456f03431d212cefbfbdefbfbd6f4b4b064a57efbfbdefbfbdefbfbd09efbfbd2e28efbfbd4befbfbd1befbfbd3fefbfbdefbfbddbb85a6eefbfbd5727efbfbd12efbfbdefbfbd56efbfbdefbfbd02efbfbdefbfbd1f3a11efbfbdefbfbd25efbfbdd9a016efbfbdefbfbdefbfbdefbfbd09efbfbd6374efbfbd03efbfbd01efbfbdefbfbd201501efbfbdefbfbd1b12efbfbd14540307efbfbdefbfbd0729efbfbdc582efbfbd3a09373320102fefbfbd63221aefbfbd1743302d3b47efbfbd17761b1f6075efbfbd44efbfbd2a621defbfbd742969efbfbdd989efbfbd5033efbfbdefbfbdefbfbdefbfbd2062054c0354efbfbdefbfbdefbfbd3defbfbdd3a9efbfbd5546efbfbdefbfbdefbfbdefbfbd78da9fefbfbdefbfbd4557efbfbd2cefbfbdefbfbdefbfbdefbfbd1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4016efbfbdefbfbd64de9270efbfbd1e6b16797b0aefbfbd65613a32efbfbd7a5168efbfbdefbfbd447cefbfbd4fefbfbd74efbfbd64efbfbdefbfbd35efbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd22efbfbd53efbfbd59efbfbd1511efbfbd62efbfbd56efbfbd64790634efbfbd47efbfbd39571a50efbfbdefbfbd773cefbfbd3811206249efbfbd02311befbfbd24efbfbd5b55efbfbd12efbfbdefbfbd3b2eefbfbd200151efbfbd68efbfbd07efbfbd3f2219efbfbd52efbfbd29efbfbdefbfbdefbfbdefbfbd54efbfbd07efbfbd48efbfbd08efbfbdefbfbd73efbfbdefbfbdcfa1efbfbd756b6fefbfbdefbfbd571fefbfbd4838efbfbd6eefbfbd036befbfbdefbfbdc482efbfbd763fefbfbd0e47efbfbddd8fefbfbd2d32efbfbd5b5564efbfbdefbfbd4165efbfbdefbfbd54efbfbdefbfbd58efbfbd721befbfbd15efbfbd2cefbfbd04efbfbd0fefbfbdefbfbdefbfbd1e64dcb7efbfbd36efbfbdefbfbdefbfbd3472d0b2efbfbd1d37efbfbd6f7613efbfbd4eefbfbd057b581d4aefbfbdefbfbd701e4471efbfbdcb9fefbfbd28efbfbdce96efbfbd6969efbfbdefbfbdefbfbdefbfbdefbfbd012befbfbdefbfbd27efbfbdefbfbd437cefbfbd26543c04efbfbdefbfbdefbfbd00efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5fefbfbdd8a3efbfbd4b41efbfbd54efbfbdefbfbdefbfbd08444defbfbdefbfbd6a270e401eefbfbd3a165fefbfbd2c42efbfbd4369224827efbfbd49efbfbd14efbfbd1defbfbdefbfbd4eefbfbd75d691efbfbdefbfbdefbfbd0f07efbfbd646c13efbfbdefbfbdefbfbdefbfbd50efbfbd4aefbfbd404b58efbfbdefbfbd05583a07efbfbdce87efbfbdefbfbdefbfbdefbfbd3c5befbfbd1e19584373efbfbd66efbfbd6267efbfbdefbfbd74171f5712efbfbd3f71774e7eefbfbd073e16efbfbdefbfbdefbfbdefbfbd12704defbfbdefbfbd1defbfbd46efbfbdefbfbd4612efbfbdefbfbd6868efbfbd3c6befbfbd077aefbfbd2e1427efbfbdefbfbdcd92efbfbd3872d885efbfbd13efbfbd37efbfbd2defbfbdefbfbd40efbfbd5d1727efbfbd630108efbfbdefbfbd635b4f7a0befbfbdefbfbd2aefbfbd2712efbfbdefbfbdefbfbd20efbfbd55efbfbd2a7e3d48efbfbd552c10efbfbdefbfbd73150139efbfbd3ecf982cefbfbdefbfbd60efbfbd48710fefbfbd6953efbfbd2defbfbd465aefbfbdefbfbd16efbfbd3417efbfbd5fefbfbdefbfbdefbfbd0755efbfbdefbfbd64efbfbd3cefbfbd7eefbfbdefbfbdefbfbd4fde94efbfbd3004efbfbd41efbfbdefbfbdefbfbd3e38efbfbdefbfbd27efbfbd09efbfbd10efbfbd3d7befbfbdefbfbd714d2f03efbfbdefbfbd27efbfbd32efbfbd38efbfbd2705107b0966efbfbdefbfbdefbfbdefbfbd0816d78920efbfbd5e266b7aefbfbd05efbfbdefbfbdefbfbd6fefbfbd51efbfbd171975efbfbd22efbfbdefbfbd5f64efbfbdc98befbfbd3a7defbfbd51672f32efbfbdefbfbd45465defbfbdc8a8c6b0efbfbd61077937efbfbd70efbfbdefbfbd15efbfbd77735847196cefbfbd02efbfbdefbfbd105befbfbdefbfbd79efbfbd5b1874efbfbdde9a12efbfbd11efbfbd6470efbfbdefbfbd2f3cefbfbdefbfbd0e4c23efbfbd225aefbfbd6a62efbfbd5e770eefbfbd38efbfbdefbfbd1a164e0fefbfbdefbfbdefbfbd41154e0a18efbfbdefbfbd624cefbfbdefbfbd2b0cefbfbdefbfbd0d53633133efbfbdefbfbd5946efbfbdefbfbdefbfbd764cefbfbdefbfbd04efbfbd2befbfbdefbfbd23687c59efbfbd26efbfbdefbfbdefbfbd044d2e4befbfbd6c3e1b5fefbfbdefbfbdefbfbd65095aefbfbdefbfbdefbfbdefbfbd1234efbfbd244146df981befbfbdefbfbd1234efbfbd2c4123d0b9efbfbd12efbfbdefbfbd2c41efbfbdefbfbdefbfbdefbfbd6e3b1d11744fefbfbd6c6676efbfbd15efbfbd6a31efbfbd201b4d2159ddafefbfbdefbfbd21efbfbd17efbfbdefbfbdefbfbd4267efbfbdefbfbd6befbfbd41cc830e27efbfbd6fefbfbd116a36efbfbdefbfbd3c6064efbfbdefbfbd544fefbfbd3d1057efbfbd0aefbfbd30efbfbd7e3eefbfbdefbfbdefbfbd39efbfbd3649efbfbdefbfbdefbfbdefbfbdefbfbd6b0f795a70efbfbdefbfbd56454aefbfbd365defbfbd7e14c2b2542763efbfbd2115281d24efbfbdefbfbdefbfbd33294538efbfbdefbfbd57efbfbd2fefbfbd04efbfbd57efbfbdefbfbd7a39efbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd15efbfbd0400646a13efbfbdefbfbdefbfbdefbfbdefbfbd4fefbfbdefbfbd5628efbfbdefbfbd583aefbfbd285befbfbd3f10efbfbdefbfbd5defbfbd7968efbfbdefbfbd26627defbfbdefbfbdefbfbdd98cefbfbdefbfbdefbfbd5fefbfbdefbfbdefbfbd1869efbfbdefbfbd51efbfbdefbfbd40efbfbdefbfbd0160efbfbd7f53efbfbd23efbfbdefbfbd3defbfbd0cefbfbd71611e6356efbfbd1037efbfbdefbfbddc9c32435441efbfbdefbfbd5119efbfbdefbfbdefbfbdefbfbd00efbfbdefbfbd17efbfbdefbfbd703c5b3440efbfbd7354efbfbdefbfbd62321f3540efbfbd4defbfbd06381b16efbfbd6e161328efbfbdefbfbdefbfbd3165efbfbdefbfbd6c6b7fefbfbd4736391fd58972efbfbdefbfbdefbfbd193fefbfbdefbfbd35417cc5a3efbfbd2a3e1eefbfbd142647e3ac9cefbfbd5eefbfbd07481e63efbfbd52efbfbd79efbfbdefbfbd76efbfbd2d14efbfbdefbfbd27efbfbd48efbfbdefbfbd271aefbfbd01efbfbdefbfbdefbfbd5d3374efbfbdd8b6efbfbdefbfbd796a2cc6a54cefbfbdd7a973efbfbdefbfbd23efbfbd5cefbfbd6befbfbd07efbfbd3cefbfbdefbfbdefbfbdefbfbd3f20efbfbdefbfbdefbfbd2eefbfbdefbfbd37c5b3efbfbd65efbfbd7e597fefbfbd46efbfbd2fefbfbd3fefbfbdefbfbdefbfbd4b6befbfbdd194c5950defbfbdefbfbd7920efbfbdefbfbd5b62efbfbdefbfbd147825efbfbdefbfbdefbfbd01efbfbd5befbfbdefbfbdefbfbd31efbfbd7842efbfbd5cefbfbdefbfbdefbfbdefbfbdc2985d7861625c606162efbfbdefbfbdefbfbd587d61625c606132efbfbd0f4defbfbdefbfbd5345efbfbd24efbfbdefbfbd7d421f57efbfbdefbfbd2defbfbdefbfbd36efbfbdefbfbd0573efbfbd706a3cefbfbdefbfbdefbfbd69efbfbdefbfbd095aefbfbdefbfbd2e0d5e09efbfbd5f3a6835efbfbd1befbfbdefbfbd12efbfbd5f3b6c35efbfbd1befbfbdefbfbd122e4befbfbd7619d79befbfbd72efbfbd55efbfbdefbfbd65255d6eefbfbdefbfbddc93efbfbd161370efbfbd7d5230efbfbd121cefbfbd65efbfbdefbfbd32efbfbd764eefbfbdefbfbd53efbfbdefbfbd17efbfbd316ed78f3c17efbfbdefbfbdefbfbd07efbfbd1aefbfbd34efbfbd3fefbfbd4b49efbfbd34efbfbd007b7b62efbfbd6f4fca9fefbfbdefbfbd4f4fefbfbdefbfbddb91efbfbd777eefbfbd45efbfbdefbfbdefbfbdefbfbdefbfbd29efbfbd4ade8fefbfbd4fefbfbd724b64efbfbdefbfbd0defbfbd64efbfbd74592eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd61efbfbdefbfbd7c58efbfbdefbfbd6e6cefbfbdefbfbd7773efbfbdefbfbd306a7a454625efbfbd63efbfbdefbfbdefbfbd13efbfbdefbfbdefbfbdefbfbdefbfbd20746cefbfbd4befbfbd4363efbfbd58efbfbd62efbfbdd9bb66614a78396fefbfbdcab91a27672fefbfbd7259425fefbfbd09efbfbd0eefbfbdefbfbdefbfbd14efbfbd26efbfbdefbfbdefbfbd011768efbfbdd8981aefbfbd1aefbfbd6430efbfbdefbfbdefbfbdefbfbd7a17efbfbdefbfbdefbfbd5b57efbfbd2566efbfbdefbfbdefbfbd1f2408395fefbfbd7aefbfbd02efbfbd3c09cda4efbfbdefbfbdefbfbd65efbfbd2eefbfbd5e4b0dcfbdefbfbdefbfbd7aefbfbd5fefbfbd08154aefbfbd5950efbfbd69237d3eefbfbd57efbfbdefbfbd64efbfbdefbfbdefbfbdefbfbd6813372d5a4d730b451d6aefbfbdc9ae43efbfbd66d5ab3fefbfbd54efbfbd5cefbfbdefbfbdefbfbdefbfbd1defbfbd70efbfbdd18a24efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd66efbfbdefbfbdefbfbd4f67efbfbdefbfbdefbfbd571769efbfbde4bc9befbfbddc901543efbfbdefbfbd41454aefbfbdefbfbd3b17efbfbd6332efbfbd537362efbfbdefbfbdefbfbdefbfbd6514efbfbd2cefbfbdefbfbd01efbfbdefbfbd16efbfbd1b2defbfbd3a0d6e3a377f15efbfbd2fefbfbd20efbfbdefbfbdefbfbd49efbfbdefbfbdefbfbd3fefbfbdefbfbd15efbfbdefbfbd25efbfbd382eefbfbd62efbfbd2e4defbfbd7cefbfbdefbfbdefbfbdefbfbd0defbfbdefbfbd3270efbfbd3fefbfbdefbfbd432fcbaeefbfbd0553efbfbdc7976defbfbdefbfbdefbfbd2aefbfbd05efbfbdefbfbd1aefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbdefbfbdefbfbd17efbfbd3e17efbfbd76efbfbdefbfbdefbfbd67efbfbdefbfbd2117efbfbd75465fefbfbd6254efbfbd215eefbfbd35efbfbdefbfbdefbfbd7aefbfbd791d0cefbfbdefbfbd43efbfbd63efbfbd6befbfbd232befbfbd6146efbfbdefbfbdefbfbd5cefbfbdefbfbdefbfbd65efbfbd02efbfbdefbfbdefbfbdc3956c2b702e1373efbfbd174a43efbfbdefbfbdefbfbdefbfbdefbfbd1e2a435e22cc92efbfbd1b5476efbfbdefbfbd5defbfbd36efbfbdefbfbd0fefbfbd5defbfbdefbfbd243c56efbfbdefbfbd10efbfbd6d7c75efbfbdefbfbdefbfbd76efbfbd0cefbfbd525f072e2652dea36529efbfbd4befbfbddd8aefbfbd17efbfbdefbfbd641d7a4befbfbdefbfbd1620efbfbdefbfbd17efbfbd212f12efbfbd0cefbfbd2f51efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4b67efbfbd5defbfbd02536defbfbd56efbfbd60efbfbdefbfbd33735defbfbd78efbfbd5d233912efbfbdefbfbdefbfbd346befbfbdefbfbd4eefbfbd214aefbfbd60efbfbd337a0540efbfbdefbfbd255aefbfbd1e12efbfbdefbfbdefbfbd74364a2eefbfbd6eefbfbdefbfbd39efbfbd3f5defbfbdefbfbd3931efbfbd28efbfbd03efbfbdefbfbdefbfbd43efbfbd5fefbfbdd28cefbfbd41142027efbfbd5a3a053d4f34efbfbd2e48416fefbfbdefbfbd3461efbfbd3a7eefbfbd37efbfbd2aefbfbdefbfbd5befbfbdefbfbdefbfbdd4b7efbfbd192336efbfbd73efbfbdefbfbd3524efbfbd1541efbfbd4c21efbfbd5151efbfbd0aefbfbd38efbfbd0a6a2c3049040c0eefbfbdefbfbd0038efbfbd4c4a00637cefbfbd6cefbfbdefbfbd6e56efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2210efbfbd43453d4b07efbfbdefbfbdefbfbd5962efbfbd1d7a3e63efbfbddfa4250021efbfbdefbfbdefbfbd496aefbfbdefbfbd70267c2eefbfbd7f53efbfbdefbfbdefbfbd210d28efbfbd68312f00426befbfbdefbfbdefbfbd1046efbfbd2e2defbfbd04efbfbdefbfbd3a48efbfbd63457c2b18efbfbd53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd01efbfbd78efbfbdefbfbdefbfbd2c38efbfbd5b7a20efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd477cefbfbd5f580065145521efbfbd7d49efbfbdefbfbdefbfbdefbfbd5b620902efbfbd4e60efbfbd24590cefbfbd0803efbfbd4cefbfbdefbfbd1c5befbfbdefbfbd1c0524efbfbd6d62efbfbd7defbfbd7eefbfbd18efbfbdefbfbdefbfbd292805efbfbd0c2249401ad9bcefbfbd241cefbfbdefbfbdefbfbd1d1e4f4457efbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd6b12efbfbdc7ab6a1d5e13efbfbd7e35efbfbd3728efbfbdefbfbdc28e243464efbfbdefbfbd35efbfbdefbfbd60451aefbfbdefbfbdefbfbdefbfbd10efbfbd29efbfbd4fefbfbddf91060e4aefbfbd7befbfbd0aefbfbdec8c97efbfbd5d0819efbfbd521aefbfbdefbfbde68f887341efbfbd1723efbfbd32efbfbdefbfbd79efbfbdefbfbd5372064c5e1fefbfbd5aefbfbdefbfbd5e01efbfbd0300efbfbdefbfbdefbfbd1f3fefbfbdefbfbdefbfbd1defbfbd6c47006bda80efbfbdefbfbd1804efbfbdefbfbd46efbfbdefbfbd26efbfbdefbfbd247a00efbfbdefbfbdefbfbdc7bd5b5fefbfbdefbfbdefbfbdefbfbde3a78214efbfbdefbfbd2fefbfbdefbfbdefbfbdd1b1efbfbdc6a0115aefbfbdefbfbd30efbfbdddae5c646b4a2defbfbdefbfbd68323c212f62efbfbd5d6c4554456e7aefbfbd212007efbfbdefbfbd5befbfbd752defbfbdefbfbdefbfbd0d1a6347efbfbd3cefbfbdefbfbd2fefbfbd16efbfbd53efbfbd4a494aefbfbdefbfbd7fefbfbd2aefbfbdefbfbd4b46440cefbfbd2aefbfbd20efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd76efbfbd7fefbfbd693312efbfbd20efbfbd0e70187eefbfbd7245efbfbdefbfbd5c7763efbfbdefbfbddf9cefbfbdd895efbfbd4427efbfbd4ed89a54efbfbdefbfbd13efbfbd08efbfbd0202efbfbd7cefbfbd4defbfbd2376efbfbd6c500e215c43efbfbd14efbfbd777befbfbd222cefbfbd073325efbfbdefbfbd03efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6d161cefbfbdefbfbd0b14efbfbd5351efbfbd68efbfbdefbfbd38efbfbd09efbfbdefbfbdefbfbd41efbfbdefbfbd79efbfbdc7b84c6cefbfbd6a524d59efbfbdefbfbd52efbfbdefbfbdefbfbdefbfbd58efbfbd0d7e506defbfbd56efbfbdefbfbd5defbfbd41efbfbd1f333d60d7ad20efbfbdefbfbd2d4befbfbd09efbfbd7defbfbd23efbfbd48efbfbdefbfbd5e15efbfbdefbfbdefbfbdefbfbd194965efbfbd1f1aefbfbd3d4befbfbd7f50efbfbd72c6b077efbfbd29efbfbdefbfbd09efbfbdefbfbd58efbfbd7e1714efbfbd442651efbfbdefbfbdefbfbd17efbfbdefbfbd050defbfbd1a30efbfbdefbfbd47550535efbfbdefbfbd071eefbfbd67efbfbdefbfbd30efbfbd5befbfbd2b79374626717d2befbfbdefbfbd42efbfbd1fefbfbdefbfbdefbfbdefbfbd2c172e057a7cefbfbdefbfbd31efbfbd32efbfbdefbfbd39321eefbfbdefbfbd743e3417efbfbd390904efbfbd55efbfbd23103104195aefbfbdefbfbd7cefbfbd32efbfbd62cf961defbfbd23efbfbdefbfbd6defbfbd33efbfbdefbfbdefbfbdefbfbd51efbfbd4aefbfbd1befbfbd634aefbfbd585249efbfbd51117befbfbd333b53efbfbd355465efbfbd66550041dfa5efbfbd391defbfbd1a3f63efbfbd1505efbfbdefbfbd5600272befbfbdefbfbd49efbfbdefbfbd58293a312004efbfbdefbfbdefbfbd73efbfbdefbfbd01efbfbd7befbfbd1747efbfbd3d7a5619634aefbfbd507f07d5a7efbfbd1a3d2befbfbd663103efbfbd29efbfbdefbfbd64efbfbdefbfbd77efbfbddc9befbfbdefbfbd3526efbfbdefbfbd164fefbfbdefbfbdefbfbdefbfbd511a0b1a4aefbfbdefbfbdefbfbd1a0b1aefbfbd58103c4539740eefbfbd0aefbfbd66efbfbdefbfbdefbfbd346a380fefbfbdefbfbd1d76efbfbd271cefbfbd343befbfbdefbfbd5876e2948d16efbfbd14d1bf376befbfbd5defbfbd00efbfbdefbfbdefbfbd32efbfbdefbfbd32efbfbdd5865840efbfbd67440e0befbfbd1f3d4b3ecaaaefbfbd743a1370efbfbd49efbfbd18efbfbd2d2eefbfbd5acbaa392eefbfbd23efbfbdefbfbdefbfbd386aefbfbdefbfbd5cefbfbd5068efbfbdefbfbdefbfbdefbfbdefbfbdd699efbfbdefbfbdefbfbdefbfbd6defbfbd2e46c3ba20efbfbdefbfbd58efbfbdefbfbd526f20efbfbd2e3806efbfbd43efbfbdefbfbdefbfbd281252d99941efbfbd1b63efbfbdefbfbd552a50efbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbdefbfbd1174efbfbd2d55efbfbd00efbfbdefbfbd19efbfbdefbfbd607eefbfbdefbfbd4437efbfbdefbfbd783aefbfbdefbfbd70efbfbd7221efbfbd7b1aefbfbdefbfbdefbfbd3cefbfbdefbfbdefbfbd3140efbfbdefbfbd40efbfbd0b29efbfbd0703efbfbdefbfbd457559377f7fefbfbdefbfbd50efbfbdefbfbd48efbfbdefbfbd49efbfbdefbfbd17dbbe75efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbd07504b070836efbfbdefbfbd4d2913000005efbfbd0000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c00000073657474696e67732e786d6cefbfbd5aefbfbd6e234915efbfbdefbfbd29efbfbd05120832efbfbdefbfbdefbfbdefbfbdefbfbd64efbfbd76efbfbdefbfbdefbfbdefbfbd6d3b317051efbfbd2edb9d545735efbfbdd5b11defbfbdd49bd995766159400209efbfbdefbfbd5d76efbfbdefbfbd4770efbfbdce8c34efbfbdefbfbd7eefbfbd7eefbfbd3defbfbd76efbfbdefbfbdefbfbd26efbfbd7649efbfbd224eefbfbd55efbfbdefbfbd3975efbfbd77efbfbd53efbfbdcf9eefbfbd4defbfbd74efbfbd6defbfbd60742b127b12efbfbd2c61efbfbd31dda0efbfbdefbfbd484defbfbd2e3fefbfbd3cefbfbdefbfbdefbfbd33efbfbd6e1b1a4eefbfbd4c734d4cefbfbdefbfbdefbfbd39efbfbd21efbfbd124cefbfbd4e72efbfbd782befbfbdefbfbd34c990633849efbfbd4cefbfbd24efbfbdefbfbd6416efbfbdefbfbdd392efbfbd47274365efbfbd6fefbfbdc4a0efbfbd5befbfbd2eefbfbd567265efbfbdefbfbdefbfbd3defbfbd3e61766725efbfbd482456c2a7efbfbd433546efbfbd46e7b1aa46efbfbd6fefbfbd62efbfbdefbfbd28121346efbfbd09efbfbdc5a3d1b5efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd226fefbfbd261ed9beefbfbdc3b5efbfbdefbfbdefbfbdefbfbd0a461fefbfbd06c7a6efbfbdefbfbdefbfbdefbfbd6befbfbdefbfbdefbfbd08efbfbd4cefbfbd19efbfbd77efbfbdc8a479df9d53efbfbdf18a8d91caacefbfbdefbfbd133eefbfbdefbfbd0961efbfbd13d98e3e5befbfbd2fefbfbdefbfbd6273efbfbdefbfbd65efbfbd6d183aefbfbd4e14efbfbdefbfbd341a5fefbfbd4fefbfbd1e363aefbfbdefbfbdcb8eefbfbd57575767efbfbd5eefbfbdefbfbd5e05efbfbd106338efbfbd45efbfbdefbfbdefbfbd3b1a5aefbfbd11efbfbd6864efbfbdefbfbd2eefbfbd4defbfbd3e4dd9acefbfbdefbfbd3cefbfbdefbfbd34efbfbd6d44efbfbd47efbfbd5f36efbfbdefbfbd6c501defbfbdefbfbd7eefbfbd59efbfbd032cefbfbd03efbfbd610f1eefbfbdefbfbd7defbfbdefbfbd521d6e1befbfbdefbfbd22efbfbdefbfbd6fefbfbdefbfbdd88b45efbfbd37efbfbd67efbfbd3b2d55efbfbd36efbfbd670e3cefbfbd6811efbfbdefbfbd6409efbfbd2e3cefbfbd43efbfbdefbfbdefbfbd39223270efbfbd6d0b65efbfbd18efbfbdcc9cefbfbdefbfbdefbfbdd98437193355107537dabaefbfbdefbfbd7301520e0defbfbdefbfbdd38cefbfbd26efbfbdefbfbdd48befbfbdefbfbd62efbfbd7461597defbfbd2f59efbfbd71664f5e7b2c3aefbfbdefbfbdefbfbdefbfbd2a2658efbfbd58efbfbdefbfbdefbfbdefbfbd0c4befbfbdefbfbdefbfbd6d74efbfbdefbfbd780c58efbfbd0740717c7c391d7defbfbdda884371efbfbd5fefbfbd6a0920efbfbd2befbfbd321e4eefbfbdefbfbd75efbfbd454628efbfbdefbfbd3a38efbfbdefbfbdd38eefbfbd5c7a174517504e42252aefbfbdefbfbd12411aefbfbd32efbfbdefbfbd31efbfbdefbfbd000cefbfbdefbfbd19efbfbdefbfbdefbfbd50264b43c6b4efbfbd40efbfbd4c42efbfbd1d6defbfbdefbfbd1c0befbfbd5defbfbd0121efbfbd4a350059efbfbdefbfbdefbfbdccac62efbfbdefbfbd2d1a0b3323efbfbd207b19efbfbd6044efbfbd6c61efbfbd3a70efbfbd4116efbfbd4cefbfbd30efbfbd0569625816efbfbd4befbfbdefbfbd5d7befbfbd46efbfbdefbfbd1424dca92c1befbfbdefbfbdefbfbd12efbfbd51115aefbfbd1d1befbfbd42605befbfbdefbfbd2c61efbfbd730455efbfbdefbfbd4c6477efbfbd7b2572115a6aefbfbd05efbfbdefbfbd3454efbfbd4214d892efbfbd093707efbfbd13efbfbd033978632d6550640f22efbfbd27efbfbdefbfbd4f5662efbfbd6eefbfbd106befbfbd1a2f47efbfbdefbfbd3935efbfbd2fefbfbd2befbfbd16efbfbd0f50efbfbd7eefbfbdefbfbd1fefbfbdefbfbd63efbfbd72efbfbdefbfbd52efbfbd1befbfbd73efbfbdefbfbd4e73efbfbd42efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd17efbfbd6612efbfbdefbfbdc2a10d45efbfbd2b76673cefbfbdefbfbd6414efbfbd5befbfbdefbfbd323aefbfbdefbfbdefbfbd09efbfbdefbfbdefbfbdefbfbd1e57efbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbd683f51efbfbd67efbfbdefbfbd5a77efbfbdefbfbdd88eefbfbdefbfbdefbfbdefbfbd796635efbfbdefbfbdefbfbdcda303efbfbd79efbfbd3f6cefbfbdefbfbd27efbfbd3d3eefbfbd58efbfbdefbfbd5aefbfbdefbfbdefbfbd18efbfbd7b075d74543e3cefbfbd277aefbfbd5f253573efbfbd6a6407efbfbd3871efbfbdefbfbdefbfbd5fefbfbd47efbfbd68efbfbd5177efbfbdefbfbd682fefbfbdefbfbd38efbfbd74efbfbdefbfbd360b672defbfbdefbfbddcadefbfbdefbfbdefbfbd4e227f523eefbfbdefbfbd0a4eefbfbd495a300eefbfbd74efbfbd342b4437efbfbd4953efbfbdefbfbd6fefbfbdefbfbdefbfbd46efbfbdefbfbd54e5b0964d65efbfbdefbfbdefbfbdefbfbdefbfbd425befbfbd56efbfbdefbfbdefbfbdefbfbd5aefbfbd6c34552b5510efbfbd76523b6a34562eefbfbd2befbfbd4aefbfbd5e5633efbfbdefbfbd68efbfbd54efbfbdd48a4aefbfbd52efbfbd65efbfbdefbfbd463651574f7951efbfbdefbfbdefbfbd191954efbfbd321b670defbfbdefbfbd39efbfbd1befbfbdefbfbdefbfbd50efbfbd4f6f427cefbfbd100a2cefbfbd20efbfbdefbfbd61efbfbd4c634204442c5e4defbfbdefbfbdefbfbdefbfbdefbfbd7013efbfbd2cefbfbdefbfbd39efbfbd7429e6a8a8efbfbd70efbfbd57efbfbd5c073aefbfbd3d1d1b595d67efbfbdefbfbd0c475aefbfbdefbfbd2603153527efbfbd0cefbfbdefbfbd0defbfbdefbfbd204056efbfbdefbfbd34334d74efbfbd01efbfbdefbfbdefbfbdc7acefbfbd71582befbfbdefbfbd6ed7b538efbfbdefbfbd1117efbfbdefbfbd52e790af0ad8afefbfbdefbfbd21efbfbd32480befbfbd15efbfbd00efbfbd2befbfbd690322efbfbd48efbfbdefbfbdefbfbd12efbfbd515eefbfbd12efbfbdefbfbd09efbfbd0c67d0b6efbfbd6276efbfbd49efbfbdefbfbd0aefbfbdefbfbdefbfbd2518efbfbdefbfbdefbfbd60efbfbd53efbfbdefbfbd584a3029c7905eefbfbdefbfbd66efbfbd0cefbfbd784c10efbfbd7cefbfbd2c24efbfbd1fefbfbdd88d13043aefbfbd437ccf8cdb85efbfbd11efbfbd567370efbfbdefbfbd6326efbfbdc790efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbdefbfbd52efbfbd0e74efbfbdefbfbd3aefbfbddea2efbfbdefbfbd11efbfbd5c12efbfbdefbfbdefbfbd36efbfbd4d584fefbfbdefbfbd30742b181a71efbfbdefbfbd0ae7b6b00aefbfbdefbfbdefbfbd3278efbfbdefbfbd05287eefbfbdefbfbdefbfbdefbfbd32efbfbdefbfbd2115efbfbd5a4803efbfbd2a0b114d545f1920530134efbfbd3076efbfbd5d10efbfbdefbfbdefbfbdefbfbd4462732e6a2c404bdc8f0072efbfbd4eefbfbdefbfbd1cefbfbd427c2c4303c5882b027146efbfbd11efbfbd12efbfbd3d60efbfbd34efbfbd1a26121811747cefbfbdefbfbdefbfbdefbfbdefbfbd6860efbfbd42efbfbd2e24efbfbddeb061efbfbdefbfbd25efbfbdefbfbd6c4b4a4f13efbfbdc4835c6f1e0defbfbdefbfbd38160defbfbdefbfbd4d0b20616a01efbfbd2befbfbd430f15efbfbd6defbfbd5a594defbfbdefbfbd5679efbfbd02efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbd5326efbfbd4025efbfbd2a03efbfbdefbfbd104921056a6e74efbfbd52efbfbdefbfbd497324efbfbd77efbfbdefbfbdefbfbdefbfbd32efbfbdefbfbdefbfbd1defbfbd3eefbfbdefbfbd56efbfbd19efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd150c38efbfbdefbfbdefbfbd45efbfbdefbfbdefbfbdefbfbd03efbfbd4aefbfbd0d4c7447660e0aefbfbdefbfbdefbfbdefbfbd224d13efbfbdefbfbd2061efbfbddb8509efbfbd216111efbfbdefbfbd48efbfbd062241efbfbd05efbfbd2e4c5aefbfbd7d4befbfbd44efbfbd0077754a18efbfbdefbfbd7220efbfbd1b1befbfbdefbfbd43efbfbdefbfbdefbfbd044369efbfbd1165efbfbdefbfbdefbfbdefbfbd62efbfbd22efbfbd40efbfbd3b0f7c39efbfbdefbfbd41710c444b2ed5b82befbfbdefbfbdefbfbdefbfbdefbfbd156defbfbd1432efbfbd1569efbfbdefbfbdefbfbdefbfbd2aefbfbd22144cefbfbdefbfbd06786cefbfbdefbfbd1137013defbfbd3d770ed18e7befbfbdefbfbdefbfbd26432768efbfbdefbfbdefbfbd15efbfbdefbfbd483d28efbfbd78efbfbd6c08efbfbd29efbfbdefbfbd1150efbfbdefbfbdd08befbfbdefbfbd32655defbfbdefbfbdefbfbd7fefbfbd24efbfbdefbfbdefbfbdefbfbdcdbb2fefbfbdefbfbdefbfbdefbfbdefbfbddb95efbfbdefbfbdca833fefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd5defbfbdefbfbd07efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7eefbfbd7b7fefbfbd3fefbfbdefbfbd5fefbfbdefbfbdefbfbdefbfbdc5a7efbfbdefbfbd2befbfbdefbfbd33efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbdc59b60efbfbd5e30efbfbd20187e140c3f0eefbfbdefbfbd04c3bf07c397efbfbdefbfbdefbfbd6078195cefbfbd0a2eefbfbd082eefbfbd0a2eefbfbd195cefbfbd0eefbfbd7c16efbfbdefbfbd3c78efbfbdefbfbd19efbfbd06efbfbdefbfbdefbfbd593fefbfbdefbfbdefbfbd7fefbfbdefbfbd775fefbfbd7b7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdc7beefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1c0cefbfbd0fefbfbd1f06c3afefbfbdcb97efbfbdefbfbdefbfbdefbfbd2fefbfbdefbfbdefbfbdefbfbd5c7c3e0c0c39efbfbdefbfbd37efbfbd541622efbfbdefbfbd374917560a313a14efbfbdefbfbd2a67efbfbd3561efbfbd745472efbfbd3aefbfbd680fefbfbdefbfbd3b0defbfbd77f388ba88efbfbdc485efbfbdefbfbdca83efbfbdefbfbdefbfbd0befbfbdefbfbd38efbfbd22efbfbd71696739efbfbd3b5a5eefbfbd6d46efbfbdefbfbdefbfbd4defbfbd33efbfbd15efbfbd683725efbfbd0050efbfbd58d89eefbfbd37efbfbdefbfbd1f7359efbfbd5739685defbfbdefbfbd3cefbfbdefbfbd14efbfbd27efbfbd0defbfbdefbfbdefbfbdefbfbd38efbfbd3defbfbdefbfbdefbfbdefbfbd56efbfbdefbfbdefbfbdefbfbd3976efbfbdefbfbd4befbfbd396336efbfbdefbfbdd383efbfbd3104efbfbd0f20080b6c07efbfbdefbfbd4b64787642efbfbdefbfbdefbfbd625964005defbfbd2defbfbdefbfbdefbfbd7d2defbfbdefbfbd513a4d7fefbfbd64efbfbd0b5253df8659efbfbdefbfbdefbfbdefbfbdcab41771efbfbdefbfbd01504b0708efbfbdefbfbd08efbfbd7b080000efbfbd2b0000504b0304140008080800efbfbd60efbfbd42000000000000000000000000080000006d6574612e786d6cefbfbdefbfbd4d6fefbfbd3010efbfbdefbfbdefbfbd2b2cefbfbd573026efbfbd345642efbfbd3defbfbdefbfbd55efbfbd5befbfbd59efbfbdefbfbd2ac79eefbfbdefbfbdefbfbd46c684efbfbddfafefbfbd14efbfbd7aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd58efbfbd13efbfbd4a6aefbfbdefbfbd48107a0814efbfbd42efbfbd6cefbfbdefbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd5aefbfbdefbfbdefbfbd0315efbfbdefbfbd4750efbfbd3fefbfbd65c8a5efbfbdefbfbdefbfbdefbfbdefbfbd571b4535efbfbd6445153b42452defbfbdefbfbd0435efbfbdefbfbd394defbfbd46efbfbdefbfbd5e48efbfbd6fefbfbdefbfbdd69614efbfbd69efbfbd661168efbfbd61efbfbd5aefbfbd70171d51efbfbd27efbfbdefbfbd4defbfbd51efbfbd6328efbfbdefbfbd506112103cefbfbdefbfbdefbfbd6b4defbfbdefbfbddc92efbfbd7a6aefbfbdefbfbdefbfbd5d14efbfbd31efbfbdefbfbd473a334214efbfbd0defbfbdefbfbd05760eefbfbd65efbfbd4942efbfbdefbfbd43efbfbdefbfbdefbfbdefbfbd475e3a6eefbfbdefbfbdefbfbdefbfbd3b33192830efbfbd6aefbfbd3ec99defbfbd5f1defbfbdefbfbd200cefbfbd20efbfbd79efbfbdefbfbd7e7fefbfbd7b77efbfbd761befbfbd19efbfbd561a7d006e711cefbfbdefbfbdefbfbdefbfbd5b2d0befbfbd476b7c51722d38efbfbdefbfbd16efbfbd3e48303b794019efbfbd40efbfbdc69d6aefbfbd4b6118efbfbd2defbfbddaa21c2c2a5cd0b8efbfbdefbfbd292400efbfbd74efbfbd4aefbfbd43efbfbdefbfbd78efbfbdefbfbddb964a5aefbfbd0aefbfbd1befbfbd3a3d32efbfbdefbfbdefbfbdefbfbd7b3eefbfbd01efbfbd38efbfbd3cefbfbd19efbfbd5cefbfbd7defbfbdefbfbdceadefbfbd775befbfbdefbfbddda0efbfbd2eefbfbddc9225efbfbd121aefbfbd43efbfbd39efbfbd4e342410efbfbdefbfbdefbfbd24d992efbfbdefbfbd09efbfbdefbfbdefbfbd644f75efbfbdefbfbdefbfbdefbfbd051e69e287b11fefbfbd2d2134efbfbd2849efbfbdefbfbd0c1d665c5befbfbd602aefbfbd5906efbfbd2aefbfbdefbfbd4f5defbfbd49efbfbd0befbfbd7003efbfbdefbfbdefbfbd1fefbfbdefbfbd2a25713c54efbfbd08efbfbdd3a236efbfbd20efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd78befbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd3eefbfbd4730efbfbd3defbfbdefbfbd5c16efbfbdefbfbdefbfbdefbfbd78efbfbd7b5cefbfbd33187950efbfbdefbfbdeea0b26eefbfbdefbfbdefbfbd4f1f77655defbfbdefbfbd4aefbfbd3addb25d013ed7b5efbfbd1b6fefbfbdefbfbdefbfbd3cefbfbdefbfbd47311c44efbfbd6bdfbe4befbfbdefbfbdefbfbd64efbfbd0cefbfbd0c2befbfbdefbfbd58efbfbdefbfbd46efbfbdefbfbdefbfbd761079efbfbd786eefbfbdefbfbdefbfbdefbfbd6835efbfbdefbfbd7b07efbfbd5c5aefbfbd4aefbfbd5defbfbd4b3271241e26efbfbd2befbfbd0befbfbd4b0562efbfbd767fefbfbdefbfbdefbfbd43efbfbd3522efbfbd61efbfbd75efbfbdefbfbd3a2c6e317cefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7f504b0708efbfbd31efbfbd36efbfbd020000efbfbd050000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c0000006d616e69666573742e726466cd93efbfbd6eefbfbd301044efbfbd7cefbfbd65efbfbd60efbfbdefbfbdefbfbd023914efbfbd5cefbfbd5fefbfbd1aefbfbd58052fefbfbd2e25efbfbd7d5d27efbfbdefbfbd1cefbfbd2acda1efbfbd5defbfbd66efbfbd68efbfbdefbfbdefbfbd380eefbfbd433b34606befbfbdefbfbd1967efbfbd2aefbfbdefbfbdefbfbd7cefbfbd3e79efbfbdefbfbd26dab8efbfbdefbfbd5eefbfbd1defbfbd6aefbfbdefbfbdefbfbd6a7e20efbfbd2a21efbfbd6549efbfbdefbfbd14efbfbd5eefbfbd6559efbfbdefbfbd1045efbfbd7845efbfbdefbfbd25794c2cc6bcefbfbd180b1eefbfbd46efbfbdefbfbd443eefbfbd7defbfbdefbfbd0d66efbfbd7910efbfbd25efbfbd4e3aefbfbd39efbfbd303befbfbdefbfbdefbfbd3a50efbfbdefbfbd44efbfbd094cda864cefbfbd02efbfbdefbfbd282defbfbd10efbfbd2629efbfbdefbfbd7ddc82efbfbd476defbfbdefbfbd102defbfbdefbfbd7fefbfbd6331efbfbd0e12efbfbdefbfbdefbfbd7375efbfbdefbfbdefbfbdefbfbd5f355260efbfbdefbfbdefbfbdefbfbdefbfbd2222efbfbdefbfbdefbfbd3f105e76efbfbdefbfbd7defbfbdefbfbde3a793efbfbdefbfbd0cefbfbd46efbfbdefbfbd7aefbfbdefbfbd7b0defbfbd3fefbfbdefbfbd56efbfbd4735efbfbd27504b0708efbfbd3defbfbdefbfbd00010000efbfbd030000504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f706f7075706d656e752f504b0304140000080000efbfbd60efbfbd420000000000000000000000001f000000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b0304140000080000efbfbd60efbfbd420000000000000000000000001c000000436f6e66696775726174696f6e73322f70726f67726573736261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f746f6f6c6261722f504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f666c6f617465722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f6d656e756261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000027000000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f7374617475736261722f504b0304140008080800efbfbd60efbfbd42000000000000000000000000150000004d4554412d494e462f6d616e69666573742e786d6cefbfbd54efbfbd6eefbfbd2010efbfbdefbfbd2b2cefbfbdefbfbdefbfbdcda9427172efbfbdefbfbd2f483fefbfbde2b583040befbfbd25efbfbdefbfbdefbfbd386a1e55efbfbd2a567defbfbdefbfbdefbfbdefbfbd081636efbfbdefbfbdefbfbdefbfbd1162321e1befbfbdc29f5905efbfbd7d6befbfbd6fefbfbdefbfbdefbfbdefbfbd7e65efbfbdefbfbd6aefbfbd14efbfbd0e12efbfbd4b50efbfbd394cd7b46139efbfbdefbfbd2aefbfbd24513948efbfbdefbfbdefbfbd01efbfbdefbfbd3a3b40efbfbd3fefbfbdefbfbd74efbfbdefbfbd0cefbfbdefbfbd7655efbfbdefbfbd3a63efbfbd2eefbfbd71efbfbdefbfbdefbfbd6c6d1d141d1a26efbfbd486e6507efbfbd51350d011aefbfbd42efbfbd462b2a3071c496efbfbd0defbfbd7befbfbdefbfbdefbfbd444cefbfbdefbfbd3f64efbfbdefbfbdefbfbdefbfbd24efbfbd12efbfbdefbfbdefbfbd07efbfbd540f62efbfbdefbfbd52efbfbd1e69efbfbd57efbfbd71efbfbd78742eefbfbdefbfbd2cefbfbd44efbfbdefbfbdefbfbd3c2d10efbfbd1d5aefbfbdefbfbd01efbfbdefbfbd49efbfbd6b3cefbfbdefbfbd03efbfbd53504fefbfbd35efbfbd3c76efbfbdefbfbdefbfbd4cefbfbdefbfbd42690d164aefbfbdefbfbd39c6bf2fefbfbd7f5a0f3eefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6114df885f7fefbfbdefbfbd0b504b0708efbfbd5cefbfbd4a1a0100003e040000504b01021400140000080000efbfbd60efbfbd425eefbfbd320c27000000270000000800000000000000000000000000000000006d696d6574797065504b01021400140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd21000018000000000000000000000000004d0000005468756d626e61696c732f7468756d626e61696c2e706e67504b01021400140008080800efbfbd60efbfbd4260efbfbdefbfbd01750a0000efbfbd6300000b000000000000000000000000006d220000636f6e74656e742e786d6c504b01021400140008080800efbfbd60efbfbd4236efbfbdefbfbd4d2913000005efbfbd00000a000000000000000000000000001b2d00007374796c65732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbdefbfbd08efbfbd7b080000efbfbd2b00000c000000000000000000000000007c40000073657474696e67732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd31efbfbd36efbfbd020000efbfbd0500000800000000000000000000000000314900006d6574612e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd3defbfbdefbfbd00010000efbfbd0300000c00000000000000000000000000efbfbd4b00006d616e69666573742e726466504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000254d0000436f6e66696775726174696f6e73322f706f7075706d656e752f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001f000000000000000000000000005d4d0000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001c00000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f70726f67726573736261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f746f6f6c6261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a000000000000000000000000000a4e0000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000424e0000436f6e66696775726174696f6e73322f666c6f617465722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000784e0000436f6e66696775726174696f6e73322f6d656e756261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000002700000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f7374617475736261722f504b01021400140008080800efbfbd60efbfbd42efbfbd5cefbfbd4a1a0100003e04000015000000000000000000000000002b4f00004d4554412d494e462f6d616e69666573742e786d6c504b0506000000001100110070040000efbfbd5000000000, 'odt');
INSERT INTO `bs_doc_templates` (`id`, `book_id`, `user_id`, `name`, `content`, `extension`) VALUES
(2, 2, 1, 'Invoice', 0x504b0304140000080000efbfbd60efbfbd425eefbfbd320c2700000027000000080000006d696d65747970656170706c69636174696f6e2f766e642e6f617369732e6f70656e646f63756d656e742e74657874504b0304140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd210000180000005468756d626e61696c732f7468756d626e61696c2e706e67efbfbd504e470d0a1a0a0000000d49484452000000efbfbd0000010008020000007a41efbfbdefbfbd000021efbfbd4944415478efbfbdefbfbd77401447efbfbdc7b7efbfbd5eefbfbdefbfbd1c70efbfbddebb020a220808efbfbdefbfbdc782efbfbd684c3131efbfbd29efbfbd79efbfbd7dd3935fefbfbd29efbfbdefbfbd184befbfbd44efbfbdefbfbd68efbfbd5863057b14efbfbd053c44efbfbdefbfbd38eeb8b277efbfbdefbfbd421518efbfbd4c10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3373efbfbd5f66efbfbd79667660efbfbdefbfbd7a0c00efbfbdefbfbdefbfbdefbfbd15007a35efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbdefbfbd4357efbfbdd3bcefbfbd37efbfbdefbfbd55efbfbdefbfbd5befbfbdefbfbdceb6efbfbdefbfbdefbfbd25efbfbd07efbfbd0e78efbfbdefbfbdefbfbdefbfbd725befbfbd35efbfbdefbfbd3722efbfbd3eefbfbd5f3c547aefbfbd0cd787cc8edfb6efbfbd48efbfbdefbfbd714fefbfbdefbfbd27efbfbd2defbfbdefbfbdefbfbd7cefbfbd4b5fefbfbd5f7b2b5d44efbfbd26efbfbd7eefbfbdefbfbdefbfbdefbfbd676fefbfbd70efbfbdefbfbdefbfbd375cefbfbd7defbfbd3e23efbfbdefbfbd6defbfbdefbfbd2e2fefbfbdefbfbdefbfbd14efbfbdefbfbdefbfbd63efbfbdefbfbdefbfbdd48aefbfbd687f0eefbfbd34efbfbdefbfbdefbfbd083934dd9332efbfbdefbfbd24efbfbdefbfbdefbfbd7c2b37efbfbd39efbfbdefbfbd5851efbfbd255e7cefbfbd74c3a727efbfbdefbfbd4cefbfbd0b78e0b1a2efbfbd6038efbfbdefbfbd7cefbfbdefbfbd0c59efbfbd5aefbfbdefbfbdefbfbd2bc888efbfbdefbfbdc39fefbfbdefbfbdefbfbd7cefbfbdefbfbd794befbfbd2b22315defbfbdefbfbd7eefbfbd57efbfbd1840566defbfbd46efbfbd0cefbfbdd0967defbfbd3874efbfbd6befbfbdefbfbdefbfbd2defbfbdefbfbd0a282619efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbd5fefbfbdefbfbdefbfbd45efbfbd4e516f7fefbfbdefbfbd0a3cefbfbdefbfbd7fefbfbd5eefbfbdefbfbdefbfbd35efbfbdefbfbd78efbfbd60efbfbdefbfbd1f1f5befbfbd132321efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7237efbfbdefbfbdefbfbdefbfbd72395defbfbd79efbfbd25daa3efbfbd48efbfbd61efbfbdefbfbd0fefbfbdefbfbd3aefbfbd657943efbfbd3b5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd31efbfbd35472ed88e4f1c10efbfbd60efbfbd61efbfbd52efbfbdefbfbdefbfbd64efbfbdcc877d122befbfbd3761efbfbdefbfbd7074efbfbdefbfbd6c46efbfbdefbfbdefbfbd03efbfbdefbfbd1fefbfbd2f4015efbfbdefbfbd67efbfbd5f53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd11c298efbfbd6b5f2cefbfbdefbfbdefbfbd3f6f28efbfbdefbfbdefbfbdca9a72efbfbdcea76d6360efbfbdefbfbdefbfbd1eefbfbd7065efbfbd7edc8943efbfbdefbfbdcbaeefbfbdefbfbdefbfbdefbfbd27efbfbd6367efbfbdefbfbdefbfbdefbfbd2aefbfbd2049efbfbd202cefbfbd78efbfbd240f65237d13efbfbdefbfbd7aefbfbdde85efbfbd19ce89e3939cefbfbd6befbfbdefbfbd4c0eefbfbd64efbfbd18efbfbdefbfbd215b71efbfbd66efbfbd707befbfbddbaa36efbfbd61efbfbd38efbfbd76435b7defbfbd504b0534efbfbd30efbfbd2430efbfbd305952efbfbdefbfbd39efbfbd46185a19efbfbd1e0c3f2458efbfbd0fefbfbd65c68a4fcdbbefbfbd671a3e1273cf980e16efbfbdefbfbdefbfbd39cb8c151f183fefbfbd07efbfbdefbfbdd585efbfbdefbfbdefbfbd2c316cefbfbdefbfbdefbfbd5811efbfbdefbfbd21efbfbddf9eefbfbdefbfbd59efbfbdefbfbd5873cab2efbfbd5befbfbd0e5c3fd3bcefbfbdefbfbdefbfbd6fefbfbdefbfbd52efbfbd17efbfbdefbfbd7fefbfbd3befbfbd2aefbfbd295cefbfbdefbfbdefbfbdefbfbd4fefbfbd451c18efbfbd77efbfbd0b4f35efbfbd6726efbfbd36efbfbd47193fefbfbd33efbfbdefbfbdefbfbd1961efbfbd5c3eefbfbd33efbfbdefbfbdefbfbd4341efbfbd693719efbfbdefbfbdefbfbdefbfbd77efbfbd5c2cefbfbdefbfbd69efbfbdefbfbd47793fefbfbdefbfbd27efbfbd55efbfbd3e4fefbfbdefbfbd07efbfbd1b017d0028401f000a0befbfbdefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbd5e4c69efbfbd05efbfbdefbfbdefbfbd0e54070defbfbd637775514b06efbfbdefbfbd554b0befbfbd2cefbfbd10efbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd79efbfbd6cefbfbd18efbfbdefbfbd2b72efbfbd505defbfbd5defbfbdefbfbd0befbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd716e45efbfbd72efbfbd4c71efbfbdd4903fefbfbd2cefbfbdefbfbdefbfbd75efbfbdefbfbd0f17efbfbdefbfbd0aefbfbdefbfbddcb8d390efbfbd71efbfbd58efbfbd54efbfbdefbfbd44efbfbd5a3d685876efbfbdefbfbd25efbfbd047cefbfbdefbfbd5eefbfbd74efbfbd3979efbfbd4c1befbfbdefbfbd76553fd289efbfbdefbfbdefbfbd09efbfbdefbfbd7aefbfbdefbfbd63626a5523efbfbd776060383f28354644625a077f2722df98efbfbdefbfbdc7afefbfbdefbfbdefbfbdefbfbd76d0803c6058efbfbd0fefbfbd47efbfbdefbfbd563aefbfbdefbfbd347b021b3befbfbdd4bfefbfbd3eefbfbdefbfbd39efbfbd435653efbfbdefbfbd18efbfbd5853efbfbd32efbfbdefbfbd77efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbd0a1b6d03efbfbd78efbfbdefbfbdefbfbd393eefbfbdefbfbd36efbfbd50efbfbdefbfbdefbfbdefbfbd33424c75efbfbdefbfbd6defbfbd4225d9b5efbfbd546c6850efbfbd0eefbfbdefbfbdefbfbdefbfbd5b6b1326efbfbd48361defbfbd1befbfbdefbfbdefbfbd5217efbfbdefbfbd16efbfbd6325efbfbdefbfbd246defbfbdefbfbd09efbfbdefbfbdefbfbd4239efbfbd6c407b2cd487efbfbdd6aaefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6e55efbfbd04293befbfbd62efbfbd2a654258efbfbdefbfbd3e485070efbfbd3aefbfbd734d4e77dcb746efbfbd7fefbfbdefbfbd4a75efbfbd33633c48efbfbdefbfbdd59f6defbfbd1c58efbfbdefbfbdd3ad74daac51351f6fefbfbd29efbfbde98489efbfbdd9bb59efbfbdefbfbdefbfbd6b7e66efbfbd7dc68fefbfbdd4b5357eefbfbdecbaa539efbfbd57efbfbdefbfbddd9f7befbfbd643defbfbdefbfbdefbfbdefbfbdefbfbd663f7eefbfbd587aefbfbdefbfbdcb9cefbfbdefbfbd4eefbfbd2fefbfbdefbfbd72efbfbd2fefbfbd55efbfbdc4bbefbfbde6968aefbfbd63085ad7bd3fefbfbd43efbfbd65efbfbdefbfbdefbfbd56506cefbfbd24efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd637d78efbfbd534a35efbfbdefbfbd31efbfbdefbfbd3f3cefbfbd4d6aefbfbd5aefbfbdefbfbdefbfbdefbfbd49efbfbdefbfbd2867efbfbddfb2efbfbdefbfbd52efbfbdefbfbdefbfbd536831efbfbd2408efbfbd60d88745efbfbd6b49efbfbdefbfbdefbfbdefbfbd44193eefbfbd437befbfbd441346efbfbdefbfbd2aefbfbd5befbfbddfbc28efbfbdefbfbdefbfbd57300709efbfbdefbfbd1aefbfbd71efbfbd6a5461efbfbd1ec3ba32efbfbdefbfbd5c48efbfbdefbfbd68efbfbd56efbfbd32d5934ddeaeefbfbd60efbfbdefbfbdefbfbd33403b2cefbfbd07efbfbd69387cefbfbdefbfbdefbfbd45efbfbd2c0cefbfbd0c1e6148efbfbd057b24747804c49befbfbd2b341579d98764efbfbdefbfbd47efbfbd7cefbfbdefbfbdefbfbd4befbfbd62773e213b6868efbfbd47efbfbd48141aefbfbdefbfbd30efbfbdefbfbd30efbfbd50efbfbdefbfbdefbfbd12efbfbd257befbfbdefbfbd5d0b68efbfbdefbfbd0eefbfbd35efbfbdd5a0efbfbdefbfbd0f46efbfbd6032efbfbdefbfbdefbfbd6aefbfbd2eefbfbdefbfbd431eefbfbd7021efbfbdefbfbdefbfbdefbfbd5c70365befbfbd36187f6f5556efbfbde9a4a6efbfbd08efbfbd4eefbfbd7143354e14723c4353221d29efbfbdefbfbd64286befbfbdefbfbd07efbfbd6125163e5fd8be09efbfbd056b571559efbfbd7cefbfbd22efbfbd796befbfbd38efbfbd1577efbfbdc3a309efbfbdefbfbd461aefbfbdefbfbd0e0fefbfbdefbfbd3953633e7aefbfbdefbfbdefbfbd25733d3eefbfbdefbfbdefbfbdefbfbd0856efbfbd67efbfbd5e65efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6fefbfbdefbfbdefbfbdefbfbd174409efbfbd0747efbfbd2cefbfbd32efbfbd1435d8a9efbfbdefbfbd741defbfbdefbfbd7a2fefbfbd50dfa6efbfbdefbfbd2f5579570cefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdd988efbfbdefbfbd5439efbfbdefbfbdefbfbdefbfbd5d19efbfbd18ce90efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd456c702aefbfbdefbfbd123c23efbfbd27163e5fefbfbdefbfbd4a5aefbfbdefbfbd08303d5fefbfbdefbfbdefbfbd273b646befbfbd7cefbfbdefbfbdefbfbd4befbfbdefbfbd26efbfbd6ad890efbfbdcc83efbfbdefbfbdefbfbddb9911efbfbd476a1aefbfbd48efbfbd63efbfbd0c6218efbfbdefbfbdefbfbdefbfbd2defbfbdefbfbd36efbfbd4b32efbfbd6010efbfbd7defbfbdefbfbdefbfbd2defbfbd24efbfbd0befbfbd20efbfbdefbfbd6d1b76efbfbd57efbfbd30efbfbdefbfbddcb6474cefbfbd2fefbfbd58efbfbd315fefbfbdefbfbd70efbfbdefbfbd74206c6326efbfbd1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd745befbfbdefbfbdefbfbd3a0c1cefbfbd38efbfbdefbfbdefbfbdefbfbdefbfbd66efbfbdefbfbd505dc99306efbfbdefbfbd5d2defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd17041defbfbd5e4fefbfbdefbfbd1f66d781efbfbdefbfbd7a0a375c52efbfbd1915182d20584c21457a66065eefbfbd52efbfbd02efbfbdefbfbd034037efbfbd1f66efbfbd01efbfbd0e1fefbfbd4cefbfbd4c03305ddd9fefbfbdefbfbd3070261e04efbfbdefbfbdefbfbdefbfbd34efbfbdefbfbdefbfbd181b66703eefbfbdefbfbd0224efbfbdefbfbdefbfbd4b64efbfbdefbfbdefbfbd103befbfbd36efbfbdefbfbdefbfbdefbfbd15efbfbdefbfbd7e5eefbfbdefbfbd2fefbfbddcbfefbfbd6d1a776cefbfbd14efbfbdefbfbd46efbfbd68efbfbd3defbfbd35efbfbdefbfbd7eefbfbdefbfbd644056efbfbdefbfbdefbfbd7d2eefbfbdefbfbd0cefbfbd0befbfbdefbfbd74efbfbd1a0fefbfbd4cefbfbdefbfbd6defbfbd07efbfbdefbfbd59efbfbdefbfbd3c21efbfbdefbfbdefbfbd3c4f4aefbfbd315aefbfbd10efbfbdefbfbd1811efbfbdefbfbd5befbfbd331e626a35652b62efbfbd3641efbfbd35efbfbdefbfbd520235efbfbd2d0757efbfbd3befbfbd46efbfbd63efbfbd33efbfbdefbfbd301cefbfbdefbfbd6defbfbd2125efbfbd50efbfbd303465efbfbd10efbfbd1d6cefbfbd76771a11efbfbdefbfbdd9910b171aefbfbd0cefbfbd1e693e3b2aefbfbd644defbfbd0befbfbdefbfbd062c7cefbfbd7023efbfbd5befbfbd0aefbfbd1defbfbd47efbfbd29efbfbdefbfbdefbfbd64efbfbdefbfbd35efbfbd6f0766efbfbd0128401f000aefbfbdefbfbd41176d5eefbfbd6befbfbdefbfbd2befbfbdefbfbd3eefbfbdefbfbd5d50efbfbdc5af162eefbfbd3defbfbdefbfbdefbfbd6f1cefbfbd0e4a15efbfbd6c08efbfbd776f7aefbfbd1defbfbdefbfbd3fefbfbd58efbfbd7eefbfbdc290efbfbdefbfbd2eefbfbdefbfbdefbfbd7873efbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4e570c5aefbfbd5c0aefbfbd5eefbfbd2a2a5aefbfbdefbfbd7f450b4aefbfbdefbfbd625cefbfbd24efbfbdefbfbd306368efbfbd7922efbfbdefbfbd46c3b4efbfbd071c0b6fefbfbdefbfbdefbfbdefbfbd2defbfbd1c45efbfbdefbfbdefbfbd61efbfbdefbfbdefbfbd72efbfbdefbfbdefbfbdd49befbfbd0b48efbfbdefbfbdefbfbd18efbfbd54efbfbdefbfbdefbfbd5c6e5cefbfbdefbfbd2876efbfbd44760aefbfbdefbfbd3fefbfbd58efbfbd0fefbfbdefbfbdefbfbdefbfbd2fefbfbd391ed0bcc38f716cefbfbd7fefbfbd7befbfbd7613efbfbde4a39b4fefbfbd324e5bcf8169efbfbd0f367fefbfbdefbfbdefbfbdc6bcefbfbdefbfbdcb93efbfbdefbfbd79efbfbd05efbfbd35efbfbd400f03efbfbd01efbfbd02efbfbd01efbfbd007d00287a521f380eefbfbdefbfbd5fefbfbdefbfbdefbfbd7d600fefbfbd1f1b566defbfbd4a0befbfbdefbfbdefbfbdefbfbd73376eefbfbdefbfbdefbfbdefbfbdefbfbd54efbfbdefbfbdefbfbdefbfbdefbfbd19efbfbd133a791defbfbd2d066defbfbdefbfbd6975efbfbdefbfbdcda53f6e4f7c69efbfbdefbfbdefbfbd5befbfbd13efbfbdcc8defbfbddaba25efbfbd5a21efbfbdefbfbd3c29efbfbd066defbfbd68efbfbd41efbfbddf8cefbfbdefbfbd0fefbfbdefbfbdefbfbd3165efbfbd04efbfbdefbfbd03efbfbd12efbfbdefbfbd3defbfbd29efbfbd165eefbfbd3eefbfbdefbfbd6fefbfbdefbfbd23efbfbd4d69efbfbd13efbfbd43efbfbdefbfbdefbfbdefbfbd527d74efbfbd54dbac3cefbfbdefbfbddcb1efbfbd27efbfbd7f3fdf8fefbfbd7559efbfbd31efbfbd2eefbfbdefbfbdefbfbd256b3f2a1aefbfbd64efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbdc986efbfbd58cd86efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0074efbfbd15efbfbd5e53efbfbdefbfbdefbfbd57707b3befbfbdefbfbdefbfbd0163efbfbd2e15efbfbde497a4efbfbdefbfbd44efbfbd73efbfbdefbfbd623a7eefbfbdefbfbdefbfbd4d3fefbfbdefbfbdefbfbdefbfbdefbfbd0462db9ac284efbfbdcfa538efbfbd77efbfbd2aefbfbdefbfbde1879eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd67efbfbd57efbfbd48efbfbd4e5a3f7defbfbdefbfbdefbfbd1527efbfbd6add9fefbfbdefbfbdefbfbdefbfbd35173cefbfbd636427efbfbd43efbfbd73efbfbd7e2cefbfbdefbfbd74efbfbd2966efbfbdefbfbdd1a0efbfbdefbfbdd79e7c77efbfbd2fefbfbd03dd8befbfbdefbfbddf9d76efbfbdefbfbd0f7e57c496efbfbdefbfbd6f45efbfbd6235c2aa337befbfbd1e5e7f38efbfbd1eefbfbd6840470b0f24efbfbdefbfbd2b58efbfbd7c21efbfbd41efbfbd472b366d2870efbfbd60083c7defbfbdefbfbd25efbfbdefbfbd71665704e7aaba16276c02efbfbdefbfbdefbfbd6eefbfbd34efbfbd3145efbfbd2defbfbd18efbfbde78b8253efbfbd732a19efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3335efbfbd1566efbfbd7839efbfbd18efbfbdefbfbd1c18efbfbd2b355aefbfbd424173efbfbdefbfbdd99befbfbdefbfbd3cd2b852efbfbd69691a4631efbfbd64efbfbdefbfbdefbfbd59efbfbdefbfbd38efbfbd0defbfbd0defbfbd7aefbfbdefbfbdefbfbd10efbfbdefbfbd106befbfbdefbfbd704b1fefbfbd6678d687371defbfbd6158732b6d7e371aefbfbdefbfbd34efbfbd13585478c7ab3befbfbdefbfbd11635f29efbfbdefbfbdefbfbd347bd392efbfbd6362efbfbdefbfbdefbfbd1b0cefbfbd52efbfbd25efbfbdefbfbd4defbfbdefbfbd4e48efbfbd3c666f5cefbfbdefbfbd463aefbfbdefbfbd67efbfbdefbfbdefbfbd59efbfbdefbfbd2fefbfbdefbfbd6cefbfbd6235efbfbd5eefbfbdefbfbdefbfbd66efbfbd372e56efbfbdefbfbdefbfbdefbfbdefbfbd58efbfbdefbfbdefbfbd70410c2d242a42df91efbfbd63efbfbdefbfbd30efbfbd1d4befbfbd42efbfbdefbfbd1a2cefbfbdefbfbd43efbfbd06efbfbd01efbfbdefbfbd0228401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbdefbfbd4a1fefbfbdefbfbdefbfbdefbfbd7defbfbd78efbfbdefbfbd64efbfbdefbfbd67efbfbd58efbfbdefbfbd2edeb7efbfbd3e547cefbfbd2c62467fefbfbdefbfbdefbfbd44efbfbd50efbfbd7b775cd5a3efbfbd6b572defbfbd3cd4b864efbfbd5eecac97efbfbdefbfbdefbfbd1cefbfbdefbfbd5defbfbd77efbfbdefbfbdefbfbd1060efbfbd1d3eefbfbd5cefbfbdefbfbd19efbfbd443aefbfbd5a2fefbfbdd6af4c17efbfbdefbfbdefbfbdefbfbdefbfbd614cefbfbdefbfbd181b49efbfbdefbfbd10efbfbd21575befbfbd4a1fefbfbd6be7988466efbfbd0f276fdca4efbfbdefbfbd24efbfbd7a7240efbfbdefbfbd62efbfbdefbfbd285c6cefbfbd1cefbfbd376e35efbfbd7f7cefbfbddf93efbfbd0679efbfbd14efbfbdefbfbd0cefbfbd3667efbfbd31efbfbd76efbfbdefbfbdefbfbd7f6d3a70efbfbdefbfbdefbfbdefbfbd2f08efbfbd2c5b7146efbfbd312aefbfbdefbfbdefbfbd6b5defbfbd78c6bc38efbfbd6f3befbfbdefbfbd48efbfbd59efbfbdefbfbdefbfbd74efbfbdefbfbdefbfbdefbfbd2378efbfbdefbfbddf97efbfbd7befbfbd0c6befbfbdefbfbdefbfbd18560cefbfbd5b0befbfbdefbfbd5e7738efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbd6665243877725f5aefbfbdefbfbd376fefbfbdefbfbd2eefbfbd1befbfbd54260f0fefbfbddebe78efbfbd0a5defbfbd7661efbfbd362befbfbdefbfbd0261efbfbdefbfbd2defbfbd05efbfbd217e363aefbfbdefbfbdefbfbd0855efbfbdefbfbdefbfbd627aefbfbdefbfbd44efbfbd68efbfbd19efbfbd59efbfbd5fefbfbdd89f64efbfbd07473853580d4d0aefbfbd3aefbfbd5e4fd3846d60efbfbdefbfbdefbfbdefbfbdefbfbd1f1a4861efbfbdefbfbd23efbfbd077769efbfbd5eefbfbdc7b43466efbfbd00efbfbdefbfbd501eefbfbdefbfbdefbfbdefbfbd7740efbfbdefbfbd2f12efbfbd05efbfbdefbfbd1aefbfbdefbfbd7d573aefbfbd461fefbfbdefbfbd3eefbfbd6e39efbfbd3aefbfbdeb9cb830efbfbdefbfbdefbfbdefbfbd6aefbfbdefbfbdd7ae302cefbfbd75efbfbd72efbfbdefbfbd7befbfbdefbfbd2b177befbfbdefbfbd3943265951efbfbd0713efbfbdefbfbdefbfbd71efbfbd39efbfbd2773efbfbd29efbfbdefbfbd0170efbfbdefbfbd4a1ded868d5e39efbfbdefbfbdefbfbdefbfbd55efbfbdefbfbd42efbfbd40efbfbdefbfbd2befbfbd01efbfbd1aefbfbdefbfbd075d76787befbfbdefbfbdefbfbd1d3333efbfbd4307efbfbd1defbfbdc29c42efbfbd41417cefbfbddba3efbfbd29efbfbdefbfbdefbfbd4c6d45092b2cccbeefbfbdefbfbd65efbfbd29efbfbdefbfbd0b5561efbfbdefbfbdefbfbd58745f53734b6eefbfbdcea92b57efbfbd35efbfbdefbfbdefbfbd6defbfbdefbfbd73d7a8efbfbd4cefbfbd21efbfbdefbfbdefbfbd77efbfbd56efbfbdefbfbdefbfbdefbfbd11efbfbdefbfbdefbfbdefbfbd0576efbfbdefbfbd5fefbfbd1639dbbb52ccbb64d3abefbfbd6f1defbfbdefbfbdefbfbd5470efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0cefbfbd0c18184c1befbfbdefbfbd0e3755efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6cefbfbdefbfbdefbfbdefbfbd7eefbfbdefbfbd3fefbfbd6aefbfbdefbfbd2aefbfbdefbfbdefbfbd382fefbfbdefbfbdefbfbd3fefbfbd5c1cefbfbdefbfbd42efbfbdefbfbdefbfbd4cefbfbdefbfbd7975efbfbdefbfbd11efbfbdefbfbdefbfbd2befbfbd1e1defbfbd57283a31d59768efbfbdefbfbdefbfbd6067efbfbdefbfbdefbfbd6eefbfbd0576efbfbd7defbfbd106cefbfbdefbfbdefbfbdefbfbdefbfbd72efbfbd263c29efbfbdefbfbdefbfbdefbfbdefbfbd24efbfbd51532255efbfbd3870efbfbdefbfbdefbfbd34efbfbdefbfbdd3aaefbfbd5427efbfbdefbfbd71efbfbdefbfbdefbfbd44efbfbd775656dc907a0eefbfbd7864efbfbdefbfbdefbfbd6eefbfbd12efbfbdefbfbd4e41efbfbd6cefbfbdd88e2728efbfbdefbfbdefbfbdefbfbd0d3d69011b27387cefbfbd5cefbfbd23efbfbdefbfbdefbfbd1f1241efbfbd720b155eefbfbdefbfbdefbfbd18efbfbdefbfbdefbfbd0716d8b12e3eefbfbdefbfbdefbfbd090747efbfbdefbfbd41efbfbd7cefbfbdefbfbdefbfbdefbfbd79efbfbd7defbfbd1836383c73efbfbd21efbfbdefbfbdefbfbd22213befbfbdefbfbdefbfbd7defbfbd05efbfbdc69c49efbfbdefbfbdefbfbd796f27efbfbdefbfbdefbfbd27efbfbdefbfbdefbfbd6121efbfbdefbfbd10efbfbdefbfbdefbfbd61efbfbd265322661fefbfbd07efbfbd103f31efbfbdefbfbdd8b1461f24efbfbdefbfbd7ceab7aa2266efbfbdefbfbd3533efbfbd5f7c536defbfbd72efbfbd09efbfbdefbfbdefbfbdefbfbd62efbfbd3662efbfbd173f2025efbfbd6befbfbdefbfbd72efbfbdefbfbd6d772543141cd0a9efbfbd0e39efbfbd61efbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd32efbfbdefbfbdefbfbdefbfbd69c39aefbfbd36efbfbdefbfbd734befbfbd6f72c89aefbfbdefbfbd764eefbfbdefbfbd4251247145efbfbd1defbfbdefbfbd7924545defbfbdefbfbd00efbfbd36efbfbd4556efbfbdefbfbd3318d4abefbfbd0d3b4eefbfbd63efbfbdefbfbd720d60efbfbd20efbfbd396befbfbd052cefbfbd7519efbfbdefbfbdd2a91aefbfbd6eefbfbd3defbfbd714b1befbfbd4cefbfbdefbfbd77efbfbdefbfbd03d788efbfbd21561803efbfbdefbfbdefbfbd693befbfbd4765efbfbd18efbfbdefbfbdefbfbd6e13efbfbdefbfbd26efbfbdefbfbd7eefbfbdefbfbd226eefbfbd4befbfbd2905efbfbdefbfbdefbfbdefbfbd1247efbfbd494fefbfbdefbfbdefbfbdefbfbd114166274cefbfbd56126cefbfbdd5afefbfbdefbfbdefbfbdefbfbdc7833defbfbdefbfbd57efbfbdefbfbd31efbfbdefbfbd4defbfbd3c274d54efbfbd73efbfbdefbfbdefbfbd293105efbfbd36efbfbdefbfbd272defbfbd4defbfbd2c2cefbfbd6cefbfbd2eefbfbdefbfbd6b557cefbfbdefbfbd5959efbfbd24efbfbdefbfbd3873efbfbdefbfbd43efbfbdefbfbd6fefbfbdefbfbdefbfbd6bcaab493fefbfbdefbfbd5775efbfbd7cefbfbdefbfbd2d30efbfbdefbfbdefbfbd52efbfbdefbfbd3fefbfbd2a50efbfbd14efbfbd7befbfbdefbfbdd3a6efbfbd6befbfbd6aefbfbdefbfbdefbfbdefbfbd3106efbfbdefbfbdefbfbd2a2429473f567d45efbfbdefbfbd74eeafa9efbfbd51efbfbd0e766876efbfbd701c53d384efbfbdefbfbdefbfbd2f29efbfbd7aefbfbdefbfbd09efbfbdefbfbdefbfbd2465722defbfbd40efbfbd1875755a2eefbfbdefbfbd4aefbfbdefbfbdefbfbd7512706e69efbfbd64efbfbdefbfbdefbfbdefbfbdefbfbd6a25efbfbdefbfbd6f2a15efbfbdefbfbd62201fefbfbd56efbfbd1fefbfbdefbfbdefbfbdefbfbd1ec685efbfbdefbfbd41efbfbd6919efbfbd66efbfbdefbfbd427e7defbfbdefbfbd4eefbfbdefbfbd38efbfbd0fefbfbd4a6fefbfbd1defbfbdefbfbd0c5d1016efbfbd31efbfbd5e4defbfbd3fefbfbdefbfbdd69fefbfbdefbfbd3cefbfbd0c7f7d7befbfbdefbfbd3fefbfbdefbfbd181b355c7befbfbd52efbfbdefbfbdefbfbd79efbfbdcd81cfbcefbfbdefbfbd7c2a40efbfbdefbfbdefbfbd3defbfbd50794d517d7466efbfbdefbfbdefbfbd57efbfbdefbfbdefbfbd44efbfbdc5bb753d4067efbfbd3e3445efbfbd7defbfbd4befbfbd6a4b7847d99cefbfbd7befbfbd3e74efbfbdc888d29c53efbfbdefbfbd0c1b5b5a3470445c770c27efbfbd193a71efbfbd481befbfbdefbfbdefbfbd16efbfbdef98b7efbfbd13d6b90f67efbfbd362cefbfbdefbfbd5e6aefbfbd62efbfbdefbfbd237016efbfbdefbfbd53efbfbdefbfbd2363efbfbddfaf733d3cefbfbd62434e5d6befbfbd63efbfbd2e07efbfbdefbfbd20311defbfbd0cefbfbd07efbfbd2f0ef2b4a9ae636b6befbfbdd58666efbfbdefbfbd2a2f68701864efbfbd25efbfbdefbfbd40efbfbdefbfbd6c425befbfbd7738efbfbd06efbfbdefbfbd4a72efbfbd2971efbfbd565a4defbfbd4f641c3f525c45efbfbdefbfbd46c6a15e7befbfbd461fefbfbd67efbfbd34cfa6efbfbdefbfbd593eefbfbd6defbfbd6cefbfbdefbfbd2f007a16efbfbdefbfbdefbfbdefbfbd66efbfbd1fefbfbd6351efbfbdefbfbdefbfbd61efbfbd6879efbfbd343cefbfbdefbfbd1e623befbfbdefbfbdefbfbdefbfbd5e7a766befbfbdefbfbdefbfbd646718efbfbdefbfbd6d30efbfbd4963efbfbd6befbfbdefbfbd2befbfbdefbfbdefbfbdefbfbd60efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdda86efbfbdc6b0efbfbd5f7ec48f654aefbfbd392211efbfbdefbfbdc7ab7a0b0807efbfbd27397e464aefbfbd4509efbfbd32547befbfbd04664d34efbfbd3e0befbfbd75efbfbd5363df89efbfbdefbfbdefbfbd55d7a90defbfbdefbfbdefbfbd6a7c3b1cefbfbd7fefbfbdefbfbd4befbfbdefbfbd41efbfbd23050e3c2153efbfbd20efbfbd0cefbfbdefbfbdc69aefbfbdefbfbd16efbfbd3befbfbdefbfbdefbfbd11efbfbde6b69957626f7cefbfbdefbfbddaa8efbfbd7a72d78303efbfbd411cd79a2defbfbd38efbfbd5976efbfbdefbfbd1aefbfbdc7804defbfbd36efbfbdefbfbd275b6504efbfbd2d5870efbfbd7befbfbdefbfbdefbfbd75efbfbd0f7d5fefbfbdefbfbdefbfbd600eefbfbd1befbfbd3cefbfbdefbfbdefbfbd5cefbfbdefbfbdc98fccbc730207efbfbd3060efbfbd3e70efbfbdefbfbd2828efbfbdefbfbd1b68efbfbd39efbfbddba062370a1c1bcba54a6d775510efbfbd51efbfbd7befbfbd50efbfbdefbfbd290b4defbfbdefbfbdefbfbd4defbfbd4848efbfbdefbfbdefbfbd502fefbfbd7760efbfbd3eefbfbdefbfbd5c7dc3a53d17efbfbdefbfbd0e74efbfbd607a1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5639305977efbfbd42efbfbd10efbfbd47efbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd5612367a1aefbfbdefbfbdefbfbdefbfbd0656efbfbdefbfbd3c36efbfbd38efbfbdcf9e4942efbfbdefbfbd37efbfbdefbfbd51efbfbdefbfbd76efbfbd1fefbfbd1118557befbfbd68efbfbd4aefbfbdefbfbd25efbfbd79efbfbdefbfbd02efbfbdefbfbdefbfbd575aefbfbd6a59c981efbfbdefbfbd3266deb7efbfbdefbfbd40efbfbdefbfbd2b2d38efbfbd67efbfbdefbfbd73efbfbd29efbfbdefbfbd69efbfbd4a0b65efbfbd3b64efbfbd29efbfbdefbfbdefbfbdefbfbd4defbfbd2cefbfbd3e4defbfbd427c53efbfbd09efbfbdefbfbdefbfbd5a007d3cefbfbd74efbfbd6e512b77efbfbd6aefbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd0340711fefbfbdefbfbd3c3410efbfbdefbfbd2b6aefbfbdefbfbd2edcb55f1515efbfbd266e191730efbfbdefbfbd4a37efbfbd7578efbfbdefbfbd53efbfbd61efbfbdefbfbdefbfbdefbfbd501f74c99e1fefbfbd1cefbfbd42efbfbd63efbfbdefbfbdefbfbd52efbfbd40636a69352eefbfbdefbfbd725defbfbd4e681a1aefbfbdd185041b2befbfbdefbfbdefbfbdefbfbd42efbfbdefbfbd2038efbfbdefbfbdefbfbdefbfbd737e4f5defbfbdefbfbd35efbfbd0b10d789efbfbdefbfbd3c5c2eefbfbd310cefbfbdefbfbdefbfbd58767fefbfbdefbfbd0671efbfbd4e527f217f674d72efbfbdefbfbdefbfbd4d25691318efbfbd665d514d43033a21efbfbdefbfbd31efbfbdefbfbd522befbfbd4b753eefbfbdefbfbdefbfbd2815efbfbdefbfbd322e505c2fefbfbd35efbfbdefbfbd1aefbfbdefbfbd525e2fefbfbd327defbfbd76efbfbd5131efbfbd51cd87efbfbd3deea5a8efbfbd01efbfbdefbfbdefbfbd13efbfbdefbfbd39efbfbd360d0a2406efbfbdefbfbdefbfbd31efbfbd0b60efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd05efbfbdefbfbd430fefbfbdefbfbd7fcbb800707fefbfbdefbfbd0befbfbdefbfbdefbfbdefbfbd2fefbfbd3eefbfbd42efbfbdefbfbdefbfbdefbfbd40efbfbd63efbfbd3eefbfbdefbfbdefbfbd7e3c27efbfbd1fed8f9d38efbfbd1a203eefbfbdefbfbd316aefbfbdefbfbdefbfbd57efbfbdefbfbd17efbfbd13efbfbdefbfbd5fefbfbdefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd3eefbfbd3eefbfbdefbfbdd595efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd52efbfbdefbfbd19efbfbd5472e4b79b644654efbfbd263cefbfbd475021efbfbdefbfbdefbfbdefbfbdefbfbd17efbfbd21cca3efbfbd48195eefbfbd48efbfbdefbfbd5c5d19efbfbd792cefbfbd07efbfbd67efbfbd48efbfbd76ccbc7ec68defbfbdefbfbdefbfbdc387655d516defbfbd2f3aefbfbd4befbfbd2b6f65efbfbdefbfbdefbfbd5defbfbd3aefbfbd09efbfbd297a43efbfbd057a2befbfbdefbfbd1eefbfbd4f45310e72efbfbd4e4cefbfbd3605d4ab722cefbfbd6b0fefbfbd7f2cefbfbd4763efbfbdefbfbd333a3f371b272f71efbfbdefbfbd3255efbfbd37efbfbd220befbfbdefbfbdefbfbdefbfbdefbfbd6547efbfbd5defbfbd38efbfbdd69e0a7d3cefbfbdefbfbd48efbfbdefbfbd277002efbfbd7147efbfbdefbfbd327defbfbdefbfbdefbfbdefbfbd5aefbfbd4dd1a97375efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7befbfbdefbfbd3fe999b4caad07efbfbdefbfbd69220befbfbd6ae79fb205efbfbdefbfbd4aefbfbd73654b0befbfbd0175252e57efbfbd6aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd327d100eefbfbd53efbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdc4b9434cefbfbd0133265b51545befbfbd14774eefbfbd3ec6b0efbfbd7aefbfbdefbfbd1378efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd012fefbfbd3702efbfbd750005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f00efbfbd65efbfbdefbfbdefbfbd1c5befbfbdefbfbd556edca429efbfbd02dc94efbfbdefbfbdefbfbd3a420e4dc68eefbfbdefbfbd49efbfbd1e14efbfbdefbfbd55efbfbd771cefbfbd450d77efbfbdefbfbd5676efbfbd2355efbfbd68efbfbd31082fefbfbd79efbfbd6cefbfbd4d6a74efbfbd1ec3a9efbfbd29efbfbd54efbfbdd6a5efbfbd54efbfbd27efbfbd4f72631aefbfbd345eefbfbd74efbfbd6e5cefbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd3de39795efbfbd4cefbfbd0eefbfbdefbfbdefbfbd5b6b1326efbfbd725aefbfbdefbfbdefbfbd3defbfbdefbfbd36cd8061efbfbdefbfbd02efbfbdefbfbd4aefbfbdefbfbdefbfbd0a64efbfbd3b5eefbfbd58efbfbd4e567051efbfbd1921efbfbd2cefbfbdefbfbd1a2fefbfbd5c57efbfbd6cefbfbd622aefbfbd2f60efbfbd3e08efbfbdefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbd37efbfbd5f76efbfbd6fefbfbd33137d6e1fefbfbd683b7e28efbfbdefbfbd3aefbfbd657943efbfbdefbfbd5a726c77efbfbdefbfbd573d7fefbfbdefbfbd24d0a124efbfbd0a7eefbfbdefbfbdefbfbd6fefbfbd4e4a602b6936efbfbdefbfbdefbfbd73efbfbd331d7edc90efbfbd28efbfbdefbfbd61245b48efbfbd5fefbfbdefbfbd6b194f7eefbfbd7aefbfbd5675efbfbdefbfbd55efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd61efbfbdefbfbd10efbfbdefbfbd21efbfbdc78eefbfbd23efbfbd03efbfbdefbfbd6fe7a587efbfbdefbfbdefbfbd3628efbfbd555922efbfbddb8aefbfbd4aefbfbd0f6f7e6d3b21efbfbd6857efbfbdefbfbd796104efbfbd33efbfbd4d5fefbfbdefbfbd6c7f636100efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbd24efbfbd7adca26949062defbfbdefbfbdefbfbd62efbfbd5befbfbd3441ca8e1fefbfbdcc9cefbfbddc9f7b323b77efbfbd0653efbfbdefbfbdefbfbdefbfbdefbfbd48efbfbdefbfbd125defbfbd4e7966efbfbd07766befbfbdefbfbdefbfbd4862c6baefbfbd3b7815e7b9a64eefbfbd39efbfbdefbfbd1646efbfbdefbfbd5d70efbfbdefbfbddcbeefbfbd65efbfbdefbfbd6b25efbfbd10efbfbd57efbfbdefbfbd5defbfbdefbfbdefbfbd752a3d46efbfbdefbfbdefbfbd0cefbfbdefbfbd71efbfbd62efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3d5befbfbdefbfbd11efbfbd050cefbfbdefbfbd4a6e6b031a1befbfbd6c1cc3b91e6e65efbfbdefbfbdefbfbdefbfbdefbfbd0d62601a41506a42efbfbd66efbfbd46efbfbd5e4935caabefbfbd293649efbfbd18efbfbdefbfbd69efbfbdefbfbd3f3c28efbfbd07efbfbdefbfbd4aefbfbd5c5176efbfbd504befbfbdefbfbdefbfbdefbfbdefbfbd3cefbfbd5e7c636f4defbfbd235ed585efbfbd72efbfbd14efbfbdefbfbd1127efbfbd2a3defbfbd2cefbfbdefbfbd603cefbfbdefbfbd643f723459efbfbd69efbfbd0539efbfbdefbfbd353eefbfbd41efbfbdefbfbdefbfbd3eefbfbd7268efbfbd1a6aefbfbdefbfbdefbfbd301764efbfbdefbfbdefbfbdefbfbd57efbfbdc9b11eefbfbd5315efbfbd38017653efbfbd52efbfbd16efbfbd38efbfbd70efbfbd3e2cefbfbdefbfbdd0a46874efbfbd43efbfbdefbfbd70efbfbd4d6a5415efbfbd0cefbfbd713358736037efbfbddb9defbfbdefbfbdefbfbd60efbfbd3e182eefbfbd4f3d6defbfbd7defbfbd292961efbfbd6933e985a7305ddd9fefbfbdefbfbd5163efbfbddd9b7eefbfbdefbfbd4c0c5befbfbdefbfbdefbfbdefbfbd4befbfbd35efbfbd4d74317c66efbfbdefbfbd3cefbfbd5defbfbd5671efbfbd4aefbfbd68efbfbd2c57c38defbfbd7e7fefbfbd39efbfbdefbfbdefbfbd5ed98409c3bcefbfbdefbfbd53efbfbdefbfbd31efbfbd6f18efbfbd1e1fefbfbd4eefbfbd26efbfbd1f694a4ac2b4257bce89071b4befbfbd46efbfbd5b1261ce991eddb6d4a9631734efbfbd2aefbfbd4aefbfbdefbfbdefbfbdefbfbdefbfbd11c69438efbfbdefbfbd443f0cefbfbd3eefbfbdefbfbdefbfbd30e3a7a6222fefbfbdefbfbd4c183a72efbfbdefbfbd761a48301937efbfbdefbfbdefbfbd2aefbfbd6fefbfbd03efbfbd7befbfbd53efbfbd366662efbfbd3d5dc98defbfbdefbfbd71671aefbfbd2b63efbfbd05efbfbd49efbfbd6159efbfbd7fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd063b7579d6ba721f782cefbfbd07efbfbdefbfbdefbfbd5a0fefbfbd67efbfbdefbfbdefbfbdefbfbdefbfbd5befbfbd7e7c3175efbfbd65efbfbdefbfbd173e7c3c10efbfbd5fefbfbdefbfbdefbfbd7f5aefbfbdefbfbd787766efbfbd7cefbfbd64efbfbd474563efbfbd192539501d34d48f7defbfbd15efbfbd08efbfbd6a6b77644defbfbdefbfbdefbfbdefbfbdefbfbd3f1defbfbd4c79efbfbd646157efbfbd5932efbfbdefbfbd532d68efbfbd7f39efbfbdefbfbd390b1e1b24223befbfbdefbfbd5fefbfbd49efbfbdefbfbd7f2609efbfbd57efbfbdefbfbdefbfbdefbfbd696fefbfbdefbfbd4c3defbfbd20efbfbdefbfbdefbfbdefbfbd073b70efbfbd6cefbfbdd5af4fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbddaaa33efbfbd771cefbfbdefbfbdefbfbd1fefbfbd4cefbfbdefbfbd6befbfbd5fefbfbdccb11b10123172efbfbd1cefbfbdc6975f59efbfbd45efbfbd06efbfbd6fefbfbd67efbfbd28efbfbd2fefbfbdefbfbdefbfbdefbfbd51efbfbdefbfbd3b25efbfbdefbfbd777cefbfbdefbfbd5c3c24efbfbdefbfbddcbc5349db87172c7befbfbdefbfbd776fefbfbd7aefbfbdefbfbd34efbfbdefbfbdefbfbd45efbfbd16efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1aefbfbdefbfbd6913efbfbd1e5d79efbfbdefbfbdefbfbdefbfbdefbfbd45efbfbd3f7d56efbfbdefbfbdcba96aefbfbdefbfbd1d552248efbfbdd5b07defbfbd236747efbfbd3defbfbde48c94efbfbd6c26efbfbd0874cb98efbfbd77efbfbdefbfbd1b1e030309ddb259efbfbd554c5aefbfbdefbfbdefbfbdee899f25efbfbdefbfbd31efbfbdefbfbdefbfbd11efbfbd3cefbfbdefbfbdefbfbd514e0f1356efbfbd43796defbfbdefbfbd3a1f6fc7aaefbfbd5fefbfbdefbfbd1befbfbdefbfbdefbfbd3328efbfbdefbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd7d7520efbfbd191656efbfbdefbfbdefbfbd6e63efbfbd2805efbfbdc789efbfbdefbfbdefbfbdefbfbdefbfbd01efbfbddd97585cd5895aefbfbd69614c4c2b1aefbfbd5074efbfbd1533efbfbdefbfbd6b2817efbfbdefbfbdefbfbd292f1cefbfbd1a3424efbfbdefbfbd52efbfbd7839efbfbdefbfbdefbfbdefbfbd073f7aefbfbdefbfbd0eefbfbdefbfbdefbfbdefbfbd6a69690d4defbfbdefbfbdefbfbd31efbfbdefbfbdefbfbdefbfbd305cefbfbd48efbfbdefbfbd6a4317efbfbdefbfbd087eefbfbd77617b0e0cefbfbdefbfbd1aefbfbd5aefbfbd5071efbfbd486344efbfbd17efbfbdefbfbdefbfbd630f62efbfbdefbfbd5befbfbd7e65efbfbdefbfbdefbfbd44efbfbd26efbfbdefbfbd7cefbfbd2defbfbdefbfbdefbfbdefbfbdefbfbd43efbfbd18efbfbdefbfbdefbfbd1f595fefbfbd53efbfbdefbfbd4defbfbd44efbfbdefbfbdefbfbdefbfbd6defbfbdefbfbd68efbfbdefbfbd7e67efbfbd1cefbfbdefbfbd61735f5defbfbd72efbfbdefbfbd5319efbfbdefbfbdd88e56efbfbdefbfbd05eb97b5efbfbd79efbfbd7973efbfbdefbfbddaaaefbfbdc79f5cefbfbd28301937efbfbd4c6eefbfbd66efbfbdefbfbdefbfbd0d7a3556efbfbdefbfbdd2a6efbfbd76efbfbd3e79390a6f31efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd374640efbfbdefbfbd63efbfbd79efbfbdefbfbd2aefbfbd7330031f5b14efbfbdd395efbfbd49efbfbdefbfbd07efbfbd551e5befbfbdefbfbdefbfbd1b5eefbfbd7b7cefbfbdefbfbdefbfbdefbfbd306f63efbfbdefbfbd656362efbfbd4cefbfbdefbfbdefbfbd61efbfbd3eefbfbd2148efbfbd17efbfbd14efbfbd08190263efbfbdefbfbd0f37efbfbd1cefbfbd45efbfbdefbfbd5a0d26efbfbd3321efbfbd3eefbfbd15efbfbd102befbfbdefbfbd39cf8f337aefbfbd035aefbfbd4d7176efbfbdefbfbd55efbfbd20efbfbdefbfbd142befbfbd1139631836efbfbdefbfbd737defbfbd57efbfbd58efbfbdefbfbd4fefbfbd3e322d6959efbfbd2befbfbdefbfbd6fefbfbd73efbfbdefbfbd79efbfbdefbfbd3eefbfbdefbfbdefbfbddb8337efbfbd385955efbfbdefbfbdefbfbdefbfbdefbfbd03efbfbdefa6a77defbfbdefbfbdefbfbd27efbfbd5e74efbfbdefbfbdc592efbfbd6a087fefbfbdefbfbd51efbfbdefbfbd5777efbfbdefbfbd763f56efbfbd235aefbfbdeebd9fefbfbd41efbfbd31224631efbfbdefbfbd3053efbfbdefbfbd1c5730efbfbd29efbfbdefbfbd34efbfbd68efbfbd3aefbfbdefbfbdefbfbd4fefbfbd79efbfbdefbfbdefbfbdefbfbd3830efbfbd47efbfbd421fefbfbd6f7cefbfbddabcefbfbdefbfbdc59eefbfbd20efbfbd31efbfbd60efbfbd19efbfbd1befbfbdefbfbd2961efbfbd0f2b4ce190b8efbfbd59efbfbdefbfbd143305efbfbd1befbfbd13efbfbd245d1eefbfbd7048efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7301efbfbd4cefbfbdefbfbd6e72d7af3eefbfbd102666efbfbdefbfbd63efbfbdefbfbd32780fefbfbd07efbfbd1b7d5defbfbd7fefbfbdd58f1a23d8be66673e23efbfbdefbfbdefbfbd2303efbfbdefbfbd75efbfbdefbfbdefbfbdefbfbdefbfbd7962efbfbd65efbfbdd08aefbfbd3cefbfbd5415efbfbd48efbfbdefbfbd1dd783efbfbdefbfbd5d5aefbfbdefbfbd04efbfbd38efbfbdd9b9efbfbd44440f55d9aaefbfbdefbfbdefbfbdefbfbdefbfbd432441ca8eefbfbdefbfbdefbfbd46103d61efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd163befbfbdefbfbd157933efbfbd3815efbfbdefbfbd06efbfbd6eefbfbd244fefbfbdefbfbdefbfbd5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdde9015efbfbd3e7950302aefbfbd72efbfbdefbfbd2befbfbdefbfbdefbfbd2c49296974efbfbdefbfbdefbfbd15efbfbd7239efbfbdefbfbdefbfbd4971165defbfbdefbfbdefbfbd0a5aefbfbdefbfbd5d07443b50efbfbdefbfbd09efbfbdefbfbd2befbfbd62efbfbd18dfbdefbfbd48082eefbfbdefbfbd2874efbfbd545fefbfbd2a644c2f17efbfbdefbfbd54efbfbd35efbfbd31efbfbdefbfbdefbfbdefbfbd304defbfbdefbfbdefbfbd170812efbfbdefbfbdefbfbd296b7cefbfbdefbfbd375f1737efbfbdefbfbdefbfbd78efbfbd3768efbfbd5aefbfbd63efbfbd57efbfbd32efbfbdefbfbd0defbfbd7627583cefbfbdefbfbd42efbfbdefbfbdefbfbd576859762e3c7defbfbd28efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbd607befbfbd5eefbfbd50795b72efbfbd5aefbfbdefbfbd631aefbfbdefbfbd5fefbfbdefbfbdcc8e387defbfbd4a71efbfbdefbfbdefbfbdefbfbdefbfbd69efbfbdefbfbd5471efbfbdefbfbd7636176b4a4a64efbfbd30efbfbd03efbfbd7eefbfbd15efbfbdefbfbdefbfbd2fefbfbd35efbfbdefbfbdefbfbde7b7a67b0647654e7cefbfbd5defbfbdefbfbd1834efbfbdefbfbdefbfbd6c4cefbfbdefbfbd01cdb3cf86efbfbd366fefbfbd7a4defbfbd140d7864efbfbd00efbfbd5e74efbfbdefbfbdefbfbd64efbfbd74efbfbdefbfbdefbfbd6defbfbdefbfbd74efbfbd5c735aefbfbd10efbfbd67efbfbdefbfbdefbfbd58efbfbd62efbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbd26efbfbd3defbfbd58efbfbdefbfbdefbfbd19efbfbdea9db57a68efbfbd541f5defbfbd4f041e6e7aefbfbd5f0fefbfbd5a401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f00efbfbdefbfbd07efbfbdefbfbd48efbfbd567c7eefbfbd0000000049454e44efbfbd4260efbfbd504b0304140008080800efbfbd60efbfbd420000000000000000000000000b000000636f6e74656e742e786d6cefbfbd1defbfbd72dbb8efbfbdefbfbd5fefbfbd72efbfbd7defbfbd2cefbfbdefbfbdefbfbdefbfbd76efbfbdd8bbdd9924efbfbdefbfbd286d673aefbfbd0c4c4232efbfbdefbfbd15efbfbd2cefbfbdefbfbd7defbfbdefbfbdefbfbd57efbfbd4b7a005eccbb48efbfbd12efbfbd4defbfbdefbfbd48efbfbd39efbfbdefbfbd5f40efbfbd7eefbfbddd83efbfbd0cefbfbd31efbfbdefbfbdefbfbd2d255dd1a401efbfbd4cdfb2efbfbdefbfbd52efbfbdefbfbdefbfbd5eefbfbd49dfad7eefbfbdefbfbdefbfbd6c6c132f2cefbfbddcb9efbfbd63efbfbdefbfbd7b0cefbfbd1f00efbfbd4717efbfbdefbfbd52efbfbd116fefbfbd236ad385efbfbd5c4c17efbfbd5cefbfbd01efbfbd62efbfbd451a7a21efbfbd0a47287b741aefbfbd0befbfbd3436efbfbd0fefbfbd2932efbfbdefbfbdefbfbdefbfbdefbfbd3b0befbfbd34efbfbd45d0be2932efbfbd05efbfbdefbfbdefbfbd377e53efbfbd07efbfbdefbfbd1b1fefbfbdefbfbd06efbfbdefbfbd392a1e1cefbfbdefbfbd7929efbfbd31162c5475efbfbdefbfbd2befbfbdefbfbde293adefbfbdefbfbdefbfbd7355efbfbd2604efbfbd095cefbfbd23efbfbdefbfbdefbfbd4c153befbfbd6f46555defbfbdefbfbd18efbfbdefbfbd0c35efbfbdefbfbdc3a649efbfbd76efbfbd2d26efbfbd45efbfbd182a68efbfbdefbfbd6f1b5befbfbdefbfbdefbfbd4234efbfbd1d22efbfbd6d430067efbfbd3befbfbdefbfbdefbfbd7768efbfbd715defbfbdefbfbd2a743253efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2d10efbfbdefbfbd5e1c36232aefbfbdefbfbd41633643efbfbd34efbfbdefbfbdefbfbd09efbfbd1c21745041efbfbdefbfbd692335efbfbdefbfbdefbfbdefbfbdd782efbfbdefbfbd3049efbfbdefbfbdefbfbdefbfbd2672efbfbd44efbfbd5b2634efbfbdefbfbd55efbfbdefbfbdefbfbd3d37efbfbdefbfbdefbfbd206805efbfbdefbfbdefbfbdefbfbd0930efbfbd2aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd2760efbfbd30efbfbd6c7befbfbd21efbfbd4932efbfbd2befbfbdefbfbdd3b14a70efbfbd13efbfbd0866efbfbd3c60efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd3aefbfbdefbfbdefbfbd6763efbfbd2defbfbdefbfbd52502067efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd36efbfbd7f23652279efbfbd41efbfbd730621efbfbdefbfbd211401efbfbdefbfbdefbfbdefbfbd08efbfbdefbfbd72efbfbdc48defbfbd44efbfbdefbfbd3cefbfbd267968efbfbdefbfbd3c600272572440efbfbd106062efbfbd29efbfbd08efbfbd4566efbfbd4cefbfbdefbfbdefbfbd13efbfbd77efbfbd7fefbfbd3240efbfbdefbfbd52efbfbd3658efbfbd1f2c52efbfbdefbfbd604edc8766efbfbd714befbfbdefbfbd4d7ec59c57efbfbdefbfbd0e59efbfbdefbfbdefbfbd3fefbfbd7c4eefbfbd0cefbfbd75efbfbd532aefbfbd1befbfbd2a4ed8a1efbfbd533519efbfbd40efbfbd37efbfbdc4b2efbfbd4defbfbdefbfbd5eefbfbdefbfbd37191eefbfbdefbfbd39efbfbd4befbfbd6fefbfbdefbfbd78254007607d10656330efbfbd761eefbfbdefbfbd1f51efbfbdefbfbd3fefbfbd60efbfbd0169efbfbd59efbfbdefbfbdefbfbd5befbfbd00200e10efbfbd455e0622efbfbdefbfbd09efbfbdefbfbd1e113b34efbfbdefbfbd1cefbfbdefbfbd14efbfbd101e64efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4677efbfbdefbfbd5e426c387308efbfbdefbfbdceb42d3478efbfbd3c3aefbfbdefbfbdefbfbd5007efbfbd4aefbfbd4b604b04efbfbd67efbfbd1103efbfbdefbfbdefbfbdefbfbd3418201defbfbdefbfbdefbfbdefbfbd4fefbfbd12efbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd03efbfbd5d01efbfbd5342503c5eefbfbd35efbfbddb94efbfbdefbfbdefbfbdefbfbd2d22efbfbdefbfbd57efbfbd240defbfbd401eefbfbdefbfbd52efbfbd1c321a473b066265efbfbd29efbfbd75124f153f733673efbfbdefbfbd61efbfbd5f44791870632cefbfbd4d0e20efbfbd60efbfbd6cefbfbd6f38efbfbdefbfbd2d5eefbfbd4c1563664c6cefbfbd6158efbfbd22efbfbdefbfbd42247111efbfbd42efbfbdefbfbd57efbfbd791d1c5d76efbfbdd297efbfbd4364762b1849efbfbd75efbfbd48e5aa944cefbfbdde9cefbfbdefbfbdefbfbd0d07efbfbd4447efbfbd11efbfbd3219efbfbdefbfbd76627f043b7216623cefbfbdefbfbd3fefbfbdefbfbdefbfbdefbfbd39281defbfbd67efbfbdefbfbdce8eefbfbdefbfbd55e7948e14efbfbdefbfbdefbfbd75efbfbd0e27efbfbd6cd69eefbfbdefbfbd330875361fefbfbdefbfbd48efbfbd69efbfbd2328efbfbdefbfbd5248efbfbdefbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd3defbfbd577a05efbfbdefbfbd71efbfbdefbfbd50efbfbdcc8defbfbd08efbfbd15efbfbdefbfbdefbfbdefbfbd0d6706270f466f7d6261223b7803efbfbdefbfbdefbfbd3defbfbd1e25efbfbdefbfbdefbfbd64efbfbdefbfbd4171efbfbdefbfbd67efbfbdefbfbdefbfbdefbfbd320ed880efbfbdefbfbd6d0defbfbdefbfbdc4bfefbfbdefbfbdefbfbdc7b80e18efbfbdefbfbd6e4cd0bbefbfbdefbfbd0344d096efbfbdefbfbd2eefbfbdefbfbd017e4e22efbfbdefbfbd21efbfbd5f30efbfbdefbfbd124a13efbfbd14efbfbd292e00efbfbd0f0a03efbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7c384defbfbd3f7b0c602befbfbdefbfbd143fefbfbdefbfbdd4baefbfbd1befbfbd6f74efbfbd3eefbfbd5eefbfbd2e23efbfbd61efbfbd280025efbfbd416aefbfbd0706753d60efbfbdefbfbdca8765e8a0905732efbfbd171227030e7e28efbfbd260472322fd09befbfbd40efbfbd1a7befbfbd015defbfbd18636eefbfbdefbfbd157eefbfbd2eefbfbd3a5defbfbd27efbfbd74efbfbd5cd8a363efbfbdefbfbd1eefbfbd730b11efbfbd13efbfbdefbfbdefbfbdefbfbd380c7fd08d58efbfbd2d31c48aefbfbd0befbfbd6735efbfbd7127efbfbd2befbfbd1f2e1421d3a860efbfbd5eefbfbdd6bf76efbfbdd99b47efbfbdefbfbdefbfbdc2a27befbfbdefbfbd4b69efbfbd1cefbfbdefbfbdc8bf4c754c7a37efbfbd67113eefbfbdefbfbdefbfbd01efbfbd1cefbfbd6d77680b435658efbfbdefbfbdefbfbdefbfbd6304efbfbdefbfbdefbfbd792672efbfbdefbfbd28efbfbd59efbfbdefbfbdefbfbdefbfbdefbfbd0961efbfbd4a06efbfbd3e6defbfbd594eefbfbdefbfbd16efbfbdefbfbd5715efbfbd32efbfbdefbfbdefbfbdefbfbd7455efbfbd1f55efbfbd776a58efbfbdefbfbd58efbfbdefbfbdefbfbd3cd682efbfbdefbfbdefbfbdefbfbddfa2efbfbd75efbfbdefbfbd0e4defbfbdefbfbd36efbfbd71efbfbdefbfbdefbfbd1eefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbdefbfbd53efbfbdefbfbdefbfbd29efbfbd757e524eefbfbdefbfbdefbfbd297a372defbfbd05efbfbd62213cefbfbd274b293e39efbfbd422a65efbfbdefbfbdefbfbd4b0befbfbdefbfbd070d7eefbfbd5513200aefbfbd2defbfbd142eefbfbd4250efbfbdefbfbd14efbfbd47efbfbdec9ea77738efbfbd553aefbfbdcebbefbfbdefbfbdefbfbd797befbfbdefbfbd45efbfbd1a3d00efbfbd2befbfbd50efbfbd1befbfbd4eefbfbd61efbfbdefbfbd41efbfbdefbfbd7c383d167befbfbd18efbfbd30efbfbd4eefbfbd4cefbfbdefbfbd2d75efbfbd2b5d26efbfbdefbfbd2322efbfbd7c5defbfbd71223aefbfbd1d27efbfbd71efbfbd01efbfbd75efbfbd78efbfbd7f5e4aefbfbd7eefbfbd066d1cefbfbdefbfbd0504efbfbdefbfbdefbfbd3e1a75013f5f58efbfbdd78b1a473f54efbfbdefbfbd1c75efbfbdc7b546511c12efbfbd5cefbfbd37efbfbd0defbfbd61efbfbd34efbfbd2f24efbfbd26efbfbd0defbfbd39efbfbdefbfbdefbfbd33efbfbd0576efbfbdefbfbdefbfbdc8a86230efbfbdefbfbd3353647b3e71efbfbd6aefbfbdefbfbd46275b65efbfbd20efbfbdefbfbd1332efbfbdefbfbd62efbfbdefbfbd48efbfbd67c99aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6b34133d4fefbfbd4c0959efbfbd206befbfbdefbfbdefbfbd1811efbfbd6fefbfbdc899efbfbd67efbfbd65efbfbdefbfbd2fefbfbdefbfbdefbfbd35efbfbdefbfbd57efbfbd057b7aefbfbdefbfbddc94efbfbd6e1aefbfbd66efbfbdefbfbd5cefbfbd2a39efbfbd4eefbfbdefbfbd2e08efbfbdefbfbd11efbfbdd5ba36efbfbd5befbfbd2701efbfbd612aefbfbd7defbfbd136cefbfbd0e6d0defbfbdefbfbdefbfbd70efbfbd5befbfbd31efbfbdefbfbd28efbfbd440ed0b6efbfbd27efbfbd531c2defbfbdefbfbdcf9defbfbd0d0f47efbfbd5defbfbd33efbfbdefbfbd62efbfbdefbfbd54efbfbd4b2a3cefbfbd0f3fefbfbd447a541fefbfbdefbfbdefbfbdefbfbd6aefbfbd3defbfbdefbfbd516706efbfbd02efbfbd25efbfbd625defbfbd677aefbfbdefbfbdefbfbd01efbfbdefbfbd7426e6bfa3efbfbd007defbfbd0cefbfbd1e7c4defbfbdefbfbd4a763a63efbfbdefbfbdefbfbd4f3aefbfbdefbfbd510747efbfbd5cefbfbd53cfa8efbfbdefbfbd29efbfbdefbfbd48efbfbdefbfbdefbfbd5a6d453015efbfbdeab4835a17efbfbd3503efbfbdefbfbd3e741fefbfbd7f24363aefbfbd23efbfbd512befbfbd140eefbfbdefbfbd2cefbfbd6aefbfbdefbfbd1cefbfbd55efbfbd307eefbfbdefbfbd2aefbfbdefbfbd5b16efbfbd562c164e193a64efbfbdefbfbdefbfbdefbfbd64efbfbd703cd185373cefbfbd2befbfbdefbfbdc2994167efbfbd7d16571fd785efbfbdefbfbdefbfbdefbfbd3eefbfbd5d5432efbfbd46efbfbdefbfbd59efbfbd49efbfbd151aefbfbdefbfbdefbfbdefbfbdefbfbd5eefbfbd59176fefbfbd7eefbfbd522b593d0168774d725defbfbd24da81583befbfbd2677424fefbfbd2e5e02efbfbd1877efbfbd636cefbfbd7c775b1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbdefbfbd60efbfbdefbfbd73efbfbd75efbfbd5260670cefbfbd4fefbfbd1defbfbd76efbfbdefbfbd4f2b1825efbfbdefbfbd46efbfbdefbfbdefbfbdefbfbd383c4b49d78cefbfbddeb5dbae68efbfbd24efbfbdc58eefbfbdcaa8efbfbd23efbfbd16efbfbd1327efbfbd20efbfbd17ce94efbfbdefbfbdefbfbd6546efbfbdefbfbd6139efbfbd51efbfbd7849efbfbdefbfbdefbfbd43efbfbdefbfbd62efbfbd76efbfbdefbfbdefbfbdefbfbd2432efbfbdefbfbd4befbfbd66c8b1cdacefbfbdefbfbdefbfbd58efbfbdefbfbd7c0b7adba5efbfbd63efbfbd6defbfbdefbfbd71efbfbd2eefbfbd5626efbfbdc28e6aefbfbd0b60efbfbd097e1b20efbfbdefbfbdefbfbd49efbfbddc89efbfbd5c68efbfbd4d616f7c7347efbfbd2aefbfbd641038efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd7b9245743efbfbd1b1229efbfbdefbfbd0e7b4fefbfbd290e0ec49065efbfbdefbfbd41efbfbdefbfbdefbfbd63efbfbd41efbfbdefbfbdefbfbd03efbfbd49efbfbd7428efbfbd1f1d67471911efbfbdefbfbd2adfa1efbfbd62efbfbdefbfbdefbfbd39efbfbdefbfbdefbfbd5575efbfbd22efbfbdefbfbd1b1defbfbd06efbfbdefbfbd16efbfbd28efbfbdefbfbd3defbfbd06e19b8b77ed889fefbfbd7b771216efbfbdefbfbd7cc2b1346234efbfbd41efbfbdefbfbd215209efbfbd5cefbfbdefbfbd3aefbfbdefbfbd2dc2abefbfbd08efbfbd6d116eefbfbd08efbfbdefbfbdefbfbd56efbfbdefbfbd532303efbfbdefbfbd3d69efbfbdefbfbd61397cefbfbd0656efbfbd1558efbfbdefbfbdefbfbdefbfbdefbfbd7279c489741c7065efbfbdefbfbdefbfbd0c25463c4b13efbfbd0defbfbd57efbfbdefbfbdefbfbdefbfbd67efbfbd1473efbfbd55efbfbdefbfbd034defbfbd387c1b1defbfbdefbfbdefbfbd40efbfbd3b0278efbfbd1332efbfbd566f31634a2d216aefbfbdefbfbd5fefbfbd1720efbfbdefbfbdefbfbdefbfbdefbfbd3c7879efbfbdc38f6f07efbfbdefbfbd62330c0947efbfbdefbfbdefbfbd090927efbfbdefbfbd6779efbfbd5cefbfbdefbfbdefbfbd0357efbfbd2eefbfbd2b4befbfbd4fd69303efbfbd4e1054efbfbdefbfbdefbfbdefbfbddf8110efbfbdc780efbfbd42efbfbd3441efbfbd12efbfbdefbfbdefbfbdefbfbd23efbfbdefbfbd321fefbfbdefbfbd5fefbfbdefbfbdddbcefbfbdefbfbd5aefbfbd051d66d89531efbfbdefbfbd4aefbfbd2b25efbfbd51efbfbd2f68efbfbdefbfbd7762e98991efbfbd34efbfbdefbfbd48efbfbd3e33efbfbdefbfbd5608efbfbd0e2362215eefbfbd22efbfbd7319efbfbdefbfbd05efbfbdefbfbdefbfbdefbfbd7defbfbd53d99b2d307a206628efbfbd680939efbfbdefbfbd73efbfbd79d48eefbfbd78efbfbd76efbfbdefbfbdefbfbdefbfbd2eefbfbd5fc3bf7c0f0e71684f5d5a2d4208507c5c05efbfbdefbfbd39631213efbfbd73efbfbd2518efbfbd5cefbfbdefbfbdefbfbdefbfbdefbfbd57efbfbd6726efbfbd7bc49eefbfbd607e203eefbfbd7defbfbd665023efbfbd4c0c2a5275efbfbd6fefbfbd467cefbfbdefbfbd3ce4a899331935735c137fefbfbdefbfbd05efbfbdefbfbdefbfbd01504b070860efbfbdefbfbd01750a0000efbfbd630000504b0304140008080800efbfbd60efbfbd420000000000000000000000000a0000007374796c65732e786d6cefbfbd3ddb92efbfbd36efbfbdefbfbdefbfbd2b584aefbfbd79efbfbd245277efbfbdefbfbd6c391e7b37551eefbfbd154f36efbfbd531009495c53efbfbdefbfbdefbfbd46efbfbd6cefbfbd653f637f727fefbfbd3440efbfbd02efbfbd004969264eefbfbd2aefbfbd021a40efbfbdd1b835efbfbdefbfbdefbfbdefbfbdefbfbdd59e70103aefbfbd77efbfbd33efbfbdc39eefbfbd3dcbb71d6f73efbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbdefbfbd6befbfbdefbfbd4bdbb70e3befbfbd457a18efbfbd5c1c6aefbfbdefbfbd0befbfbd71efbfbd4defbfbd10784b1fefbfbd4eefbfbdefbfbdefbfbd0eefbfbdefbfbdefbfbd5aefbfbd7befbfbdefbfbd464b117a49efbfbdefbfbd4b6867efbfbdefbfbd29efbfbdefbfbd3aefbfbdcf916c6302efbfbd6aefbfbd56efbfbd235360efbfbdefbfbd1defbfbdefbfbd6c63020b3c15efbfbdefbfbd7defbfbdefbfbdcfa1efbfbdefbfbd7defbfbdefbfbd777b1439192cefbfbd5defbfbdefbfbd72efbfbdefbfbd46efbfbd7e39181cefbfbdefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd582c16035aefbfbd206c2570efbfbd43efbfbd5228efbfbd1a6017efbfbdefbfbdc281efbfbd37061c76efbfbd2324efbfbd1fefbfbd1551efbfbd0eefbfbd150eefbfbd59efbfbd22efbfbdefbfbd6aefbfbdefbfbdefbfbdd688efbfbd4d096befbfbd2d0aefbfbd75efbfbd02efbfbdefbfbd3befbfbdefbfbdefbfbd3befbfbdc5b63b146d4b64321fefbfbd4325efbfbdefbfbdefbfbdefbfbd5917efbfbdefbfbdefbfbd580436efbfbd2a2b70efbfbdefbfbd64efbfbdefbfbd627befbfbdefbfbd135449efbfbdefbfbd4029efbfbdefbfbd70381ec4bf05efbfbd6325efbfbd3170221c08efbfbd5625efbfbdefbfbd5c2be1b8bf2b621aefbfbd1903efbfbdefbfbdefbfbd1351530e1d10efbfbd4b7befbfbd0c02efbfbdefbfbdefbfbd2841642defa0803b66625eefbfbd68e7969b17efbfbde5a09befbfbdefbfbd0b4101efbfbdefbfbd004c0d145d7f72efbfbdefbfbd5eefbfbd73560b60efbfbd11007543754d28efbfbdefbfbd2a1b18efbfbd01efbfbd49efbfbd06447276efbfbdefbfbd2671efbfbd6befbfbdefbfbd011130553006efbfbdefbfbd3d0e1c52efbfbd5cefbfbd6cefbfbdefbfbd2165efbfbd6138efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd37efbfbdefbfbd61d3833025efbfbdefbfbd5b3eefbfbdefbfbd7defbfbd7befbfbdefbfbdc2baefbfbd2d37efbfbdefbfbd3eefbfbd1b49efbfbd16efbfbd26efbfbdefbfbdefbfbd7e057f4befbfbd500361efbfbdefbfbdefbfbd603befbfbd3defbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd04efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd24efbfbdefbfbd067b4025efbfbd71efbfbdef9097efbfbdefbfbd3befbfbd05efbfbdefbfbdefbfbd0227efbfbd50471c4aefbfbdefbfbdefbfbd673d3cefbfbd56efbfbdefbfbd1b5423efbfbdefbfbdefbfbd30efbfbd51efbfbd6c5c53efbfbdefbfbdefbfbdefbfbd726cefbfbd7d465eefbfbdefbfbdefbfbd3930efbfbdefbfbd52efbfbd0b600b18efbfbd254fefbfbdefbfbd72efbfbdefbfbd740d5a166a1fefbfbd51efbfbdefbfbd09efbfbdefbfbdefbfbd0c5c2742efbfbd41efbfbd0defbfbdefbfbd0508efbfbdefbfbdefbfbdc3a313efbfbdefbfbdefbfbdefbfbd3eefbfbd20efbfbdefbfbdefbfbd2c11efbfbd24efbfbd21efbfbdd4a0efbfbd2059791c1f72efbfbd6defbfbd4607efbfbd45efbfbdefbfbd67efbfbdefbfbd2640efbfbdefbfbd63efbfbd382cefbfbdefbfbdefbfbd03705341efbfbd34efbfbdefbfbdefbfbd08efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd7cefbfbdefbfbdefbfbd69efbfbd692defbfbd2defbfbdefbfbdefbfbd0e03efbfbdefbfbdefbfbdefbfbd37efbfbd61efbfbd30efbfbdefbfbd15d69fefbfbdefbfbd11efbfbdefbfbd3aefbfbd49580fefbfbdefbfbd0267efbfbd6fefbfbdefbfbdefbfbdefbfbd274e2defbfbd36efbfbd55efbfbd4f044defbfbd00166616efbfbd7e73efbfbd45efbfbd32ceb940efbfbdd189efbfbd7a1cefbfbd46efbfbd4150efbfbd3d0a10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd10efbfbd640cefbfbd1eefbfbdefbfbd7e0cefbfbdefbfbdefbfbd36314fefbfbdefbfbd2aefbfbd08efbfbdefbfbd3002efbfbdefbfbd780defbfbdefbfbd096e3befbfbd16373d37d0a3554a511cefbfbdefbfbd643e252b04efbfbdefbfbdefbfbdefbfbd1aefbfbd214e640cefbfbd13e882bf0fefbfbd26efbfbdefbfbdefbfbdefbfbd13efbfbd73efbfbd1c420c5cefbfbdefbfbd6c6377efbfbd7eefbfbdefbfbd4154efbfbd16efbfbdefbfbd6fefbfbdefbfbd61efbfbd235aefbfbd226f73401b28efbfbd5c5a60efbfbdefbfbd1605efbfbd141f3fefbfbd2821efbfbd74efbfbd14efbfbdefbfbd5b33deb00e78efbfbd6fefbfbdcfbcefbfbd75efbfbd6b3cefbfbdefbfbdefbfbd2e49efbfbdefbfbdefbfbdefbfbd4eefbfbdda826eefbfbd3aefbfbdefbfbd7129db9231efbfbdefbfbdd3bd4aefbfbd01efbfbd6c4fefbfbd2defbfbdefbfbd7cefbfbdefbfbdc8b671efbfbd536cefbfbd05efbfbdefbfbdefbfbd4928efbfbdd4acefbfbdefbfbdefbfbdefbfbd43efbfbd21efbfbd600807efbfbd7450efbfbd7aefbfbdefbfbd2aefbfbdefbfbd0eefbfbdefbfbd470619efbfbd47efbfbdefbfbdefbfbd6c1769efbfbdefbfbd0343efbfbd56efbfbd40efbfbd04d1954c416defbfbdefbfbd74efbfbdd5a578eebea842efbfbdefbfbd13efbfbd2aefbfbdefbfbd3befbfbd5851efbfbd307708592d0befbfbd0e391eefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbdefbfbd21efbfbd66405aefbfbd431c290befbfbdefbfbdc5a2efbfbdc48befbfbdefbfbd1f10efbfbd27efbfbd050e39efbfbdefbfbd62efbfbdefbfbdefbfbd1e582713777a7028efbfbdefbfbdefbfbd17efbfbdefbfbd7aefbfbd6f70efbfbd25efbfbd6a62677503efbfbd03efbfbdefbfbdefbfbd19efbfbdefbfbd46efbfbdefbfbd2b75075c7c2e0a43400f2cefbfbdefbfbd43efbfbd0158efbfbd1772291d1d30efbfbdefbfbd57efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7cefbfbd596fefbfbd45efbfbd55efbfbdefbfbdefbfbdc4865526efbfbdefbfbd520befbfbdefbfbd4650efbfbd1a457800efbfbd3fefbfbdefbfbdc7956fefbfbd78efbfbdefbfbd54efbfbd2e3aefbfbd67084defbfbd2eefbfbd1528487603efbfbd623d2b52efbfbdefbfbdefbfbd33421be68486efbfbdefbfbdefbfbd7eefbfbd5c2e0cefbfbd5aefbfbd197fefbfbdefbfbd16efbfbd36efbfbd7aefbfbd2811efbfbdefbfbdefbfbd2ac6b54301cc8d60efbfbd7b1a034eefbfbd13276603efbfbd58efbfbd5144364cefbfbdefbfbdefbfbd7c3439cf8316efbfbd04efbfbd3f20371b7f25ce8446efbfbd1eefbfbd1eefbfbd7b44efbfbdefbfbdefbfbd2a05efbfbd63cb9aefbfbdefbfbdefbfbd4526efbfbd646e2befbfbd4861efbfbd1a37103fefbfbdefbfbd516c0b0c42332eefbfbd2232efbfbd52efbfbd1eefbfbdefbfbd4c0bcc9cefbfbd3132daba09efbfbdefbfbd6a2eefbfbdefbfbd02efbfbdd98cefbfbddd9aefbfbd29efbfbdefbfbd321674efbfbdefbfbd660b5defbfbd7cefbfbd4e41efbfbd15efbfbde7b4ba09efbfbd47efbfbd3c1fefbfbd3aefbfbdc7ab09efbfbdefbfbd00efbfbd16efbfbdefbfbdefbfbdefbfbdef9f873072efbfbd27efbfbd456f03431d212cefbfbdefbfbd6f4b4b064a57efbfbdefbfbdefbfbd09efbfbd2e28efbfbd4befbfbd1befbfbd3fefbfbdefbfbddbb85a6eefbfbd5727efbfbd12efbfbdefbfbd56efbfbdefbfbd02efbfbdefbfbd1f3a11efbfbdefbfbd25efbfbdd9a016efbfbdefbfbdefbfbdefbfbd09efbfbd6374efbfbd03efbfbd01efbfbdefbfbd201501efbfbdefbfbd1b12efbfbd14540307efbfbdefbfbd0729efbfbdc582efbfbd3a09373320102fefbfbd63221aefbfbd1743302d3b47efbfbd17761b1f6075efbfbd44efbfbd2a621defbfbd742969efbfbdd989efbfbd5033efbfbdefbfbdefbfbdefbfbd2062054c0354efbfbdefbfbdefbfbd3defbfbdd3a9efbfbd5546efbfbdefbfbdefbfbdefbfbd78da9fefbfbdefbfbd4557efbfbd2cefbfbdefbfbdefbfbdefbfbd1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4016efbfbdefbfbd64de9270efbfbd1e6b16797b0aefbfbd65613a32efbfbd7a5168efbfbdefbfbd447cefbfbd4fefbfbd74efbfbd64efbfbdefbfbd35efbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd22efbfbd53efbfbd59efbfbd1511efbfbd62efbfbd56efbfbd64790634efbfbd47efbfbd39571a50efbfbdefbfbd773cefbfbd3811206249efbfbd02311befbfbd24efbfbd5b55efbfbd12efbfbdefbfbd3b2eefbfbd200151efbfbd68efbfbd07efbfbd3f2219efbfbd52efbfbd29efbfbdefbfbdefbfbdefbfbd54efbfbd07efbfbd48efbfbd08efbfbdefbfbd73efbfbdefbfbdcfa1efbfbd756b6fefbfbdefbfbd571fefbfbd4838efbfbd6eefbfbd036befbfbdefbfbdc482efbfbd763fefbfbd0e47efbfbddd8fefbfbd2d32efbfbd5b5564efbfbdefbfbd4165efbfbdefbfbd54efbfbdefbfbd58efbfbd721befbfbd15efbfbd2cefbfbd04efbfbd0fefbfbdefbfbdefbfbd1e64dcb7efbfbd36efbfbdefbfbdefbfbd3472d0b2efbfbd1d37efbfbd6f7613efbfbd4eefbfbd057b581d4aefbfbdefbfbd701e4471efbfbdcb9fefbfbd28efbfbdce96efbfbd6969efbfbdefbfbdefbfbdefbfbdefbfbd012befbfbdefbfbd27efbfbdefbfbd437cefbfbd26543c04efbfbdefbfbdefbfbd00efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5fefbfbdd8a3efbfbd4b41efbfbd54efbfbdefbfbdefbfbd08444defbfbdefbfbd6a270e401eefbfbd3a165fefbfbd2c42efbfbd4369224827efbfbd49efbfbd14efbfbd1defbfbdefbfbd4eefbfbd75d691efbfbdefbfbdefbfbd0f07efbfbd646c13efbfbdefbfbdefbfbdefbfbd50efbfbd4aefbfbd404b58efbfbdefbfbd05583a07efbfbdce87efbfbdefbfbdefbfbdefbfbd3c5befbfbd1e19584373efbfbd66efbfbd6267efbfbdefbfbd74171f5712efbfbd3f71774e7eefbfbd073e16efbfbdefbfbdefbfbdefbfbd12704defbfbdefbfbd1defbfbd46efbfbdefbfbd4612efbfbdefbfbd6868efbfbd3c6befbfbd077aefbfbd2e1427efbfbdefbfbdcd92efbfbd3872d885efbfbd13efbfbd37efbfbd2defbfbdefbfbd40efbfbd5d1727efbfbd630108efbfbdefbfbd635b4f7a0befbfbdefbfbd2aefbfbd2712efbfbdefbfbdefbfbd20efbfbd55efbfbd2a7e3d48efbfbd552c10efbfbdefbfbd73150139efbfbd3ecf982cefbfbdefbfbd60efbfbd48710fefbfbd6953efbfbd2defbfbd465aefbfbdefbfbd16efbfbd3417efbfbd5fefbfbdefbfbdefbfbd0755efbfbdefbfbd64efbfbd3cefbfbd7eefbfbdefbfbdefbfbd4fde94efbfbd3004efbfbd41efbfbdefbfbdefbfbd3e38efbfbdefbfbd27efbfbd09efbfbd10efbfbd3d7befbfbdefbfbd714d2f03efbfbdefbfbd27efbfbd32efbfbd38efbfbd2705107b0966efbfbdefbfbdefbfbdefbfbd0816d78920efbfbd5e266b7aefbfbd05efbfbdefbfbdefbfbd6fefbfbd51efbfbd171975efbfbd22efbfbdefbfbd5f64efbfbdc98befbfbd3a7defbfbd51672f32efbfbdefbfbd45465defbfbdc8a8c6b0efbfbd61077937efbfbd70efbfbdefbfbd15efbfbd77735847196cefbfbd02efbfbdefbfbd105befbfbdefbfbd79efbfbd5b1874efbfbdde9a12efbfbd11efbfbd6470efbfbdefbfbd2f3cefbfbdefbfbd0e4c23efbfbd225aefbfbd6a62efbfbd5e770eefbfbd38efbfbdefbfbd1a164e0fefbfbdefbfbdefbfbd41154e0a18efbfbdefbfbd624cefbfbdefbfbd2b0cefbfbdefbfbd0d53633133efbfbdefbfbd5946efbfbdefbfbdefbfbd764cefbfbdefbfbd04efbfbd2befbfbdefbfbd23687c59efbfbd26efbfbdefbfbdefbfbd044d2e4befbfbd6c3e1b5fefbfbdefbfbdefbfbd65095aefbfbdefbfbdefbfbdefbfbd1234efbfbd244146df981befbfbdefbfbd1234efbfbd2c4123d0b9efbfbd12efbfbdefbfbd2c41efbfbdefbfbdefbfbdefbfbd6e3b1d11744fefbfbd6c6676efbfbd15efbfbd6a31efbfbd201b4d2159ddafefbfbdefbfbd21efbfbd17efbfbdefbfbdefbfbd4267efbfbdefbfbd6befbfbd41cc830e27efbfbd6fefbfbd116a36efbfbdefbfbd3c6064efbfbdefbfbd544fefbfbd3d1057efbfbd0aefbfbd30efbfbd7e3eefbfbdefbfbdefbfbd39efbfbd3649efbfbdefbfbdefbfbdefbfbdefbfbd6b0f795a70efbfbdefbfbd56454aefbfbd365defbfbd7e14c2b2542763efbfbd2115281d24efbfbdefbfbdefbfbd33294538efbfbdefbfbd57efbfbd2fefbfbd04efbfbd57efbfbdefbfbd7a39efbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd15efbfbd0400646a13efbfbdefbfbdefbfbdefbfbdefbfbd4fefbfbdefbfbd5628efbfbdefbfbd583aefbfbd285befbfbd3f10efbfbdefbfbd5defbfbd7968efbfbdefbfbd26627defbfbdefbfbdefbfbdd98cefbfbdefbfbdefbfbd5fefbfbdefbfbdefbfbd1869efbfbdefbfbd51efbfbdefbfbd40efbfbdefbfbd0160efbfbd7f53efbfbd23efbfbdefbfbd3defbfbd0cefbfbd71611e6356efbfbd1037efbfbdefbfbddc9c32435441efbfbdefbfbd5119efbfbdefbfbdefbfbdefbfbd00efbfbdefbfbd17efbfbdefbfbd703c5b3440efbfbd7354efbfbdefbfbd62321f3540efbfbd4defbfbd06381b16efbfbd6e161328efbfbdefbfbdefbfbd3165efbfbdefbfbd6c6b7fefbfbd4736391fd58972efbfbdefbfbdefbfbd193fefbfbdefbfbd35417cc5a3efbfbd2a3e1eefbfbd142647e3ac9cefbfbd5eefbfbd07481e63efbfbd52efbfbd79efbfbdefbfbd76efbfbd2d14efbfbdefbfbd27efbfbd48efbfbdefbfbd271aefbfbd01efbfbdefbfbdefbfbd5d3374efbfbdd8b6efbfbdefbfbd796a2cc6a54cefbfbdd7a973efbfbdefbfbd23efbfbd5cefbfbd6befbfbd07efbfbd3cefbfbdefbfbdefbfbdefbfbd3f20efbfbdefbfbdefbfbd2eefbfbdefbfbd37c5b3efbfbd65efbfbd7e597fefbfbd46efbfbd2fefbfbd3fefbfbdefbfbdefbfbd4b6befbfbdd194c5950defbfbdefbfbd7920efbfbdefbfbd5b62efbfbdefbfbd147825efbfbdefbfbdefbfbd01efbfbd5befbfbdefbfbdefbfbd31efbfbd7842efbfbd5cefbfbdefbfbdefbfbdefbfbdc2985d7861625c606162efbfbdefbfbdefbfbd587d61625c606132efbfbd0f4defbfbdefbfbd5345efbfbd24efbfbdefbfbd7d421f57efbfbdefbfbd2defbfbdefbfbd36efbfbdefbfbd0573efbfbd706a3cefbfbdefbfbdefbfbd69efbfbdefbfbd095aefbfbdefbfbd2e0d5e09efbfbd5f3a6835efbfbd1befbfbdefbfbd12efbfbd5f3b6c35efbfbd1befbfbdefbfbd122e4befbfbd7619d79befbfbd72efbfbd55efbfbdefbfbd65255d6eefbfbdefbfbddc93efbfbd161370efbfbd7d5230efbfbd121cefbfbd65efbfbdefbfbd32efbfbd764eefbfbdefbfbd53efbfbdefbfbd17efbfbd316ed78f3c17efbfbdefbfbdefbfbd07efbfbd1aefbfbd34efbfbd3fefbfbd4b49efbfbd34efbfbd007b7b62efbfbd6f4fca9fefbfbdefbfbd4f4fefbfbdefbfbddb91efbfbd777eefbfbd45efbfbdefbfbdefbfbdefbfbdefbfbd29efbfbd4ade8fefbfbd4fefbfbd724b64efbfbdefbfbd0defbfbd64efbfbd74592eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd61efbfbdefbfbd7c58efbfbdefbfbd6e6cefbfbdefbfbd7773efbfbdefbfbd306a7a454625efbfbd63efbfbdefbfbdefbfbd13efbfbdefbfbdefbfbdefbfbdefbfbd20746cefbfbd4befbfbd4363efbfbd58efbfbd62efbfbdd9bb66614a78396fefbfbdcab91a27672fefbfbd7259425fefbfbd09efbfbd0eefbfbdefbfbdefbfbd14efbfbd26efbfbdefbfbdefbfbd011768efbfbdd8981aefbfbd1aefbfbd6430efbfbdefbfbdefbfbdefbfbd7a17efbfbdefbfbdefbfbd5b57efbfbd2566efbfbdefbfbdefbfbd1f2408395fefbfbd7aefbfbd02efbfbd3c09cda4efbfbdefbfbdefbfbd65efbfbd2eefbfbd5e4b0dcfbdefbfbdefbfbd7aefbfbd5fefbfbd08154aefbfbd5950efbfbd69237d3eefbfbd57efbfbdefbfbd64efbfbdefbfbdefbfbdefbfbd6813372d5a4d730b451d6aefbfbdc9ae43efbfbd66d5ab3fefbfbd54efbfbd5cefbfbdefbfbdefbfbdefbfbd1defbfbd70efbfbdd18a24efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd66efbfbdefbfbdefbfbd4f67efbfbdefbfbdefbfbd571769efbfbde4bc9befbfbddc901543efbfbdefbfbd41454aefbfbdefbfbd3b17efbfbd6332efbfbd537362efbfbdefbfbdefbfbdefbfbd6514efbfbd2cefbfbdefbfbd01efbfbdefbfbd16efbfbd1b2defbfbd3a0d6e3a377f15efbfbd2fefbfbd20efbfbdefbfbdefbfbd49efbfbdefbfbdefbfbd3fefbfbdefbfbd15efbfbdefbfbd25efbfbd382eefbfbd62efbfbd2e4defbfbd7cefbfbdefbfbdefbfbdefbfbd0defbfbdefbfbd3270efbfbd3fefbfbdefbfbd432fcbaeefbfbd0553efbfbdc7976defbfbdefbfbdefbfbd2aefbfbd05efbfbdefbfbd1aefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbdefbfbdefbfbd17efbfbd3e17efbfbd76efbfbdefbfbdefbfbd67efbfbdefbfbd2117efbfbd75465fefbfbd6254efbfbd215eefbfbd35efbfbdefbfbdefbfbd7aefbfbd791d0cefbfbdefbfbd43efbfbd63efbfbd6befbfbd232befbfbd6146efbfbdefbfbdefbfbd5cefbfbdefbfbdefbfbd65efbfbd02efbfbdefbfbdefbfbdc3956c2b702e1373efbfbd174a43efbfbdefbfbdefbfbdefbfbdefbfbd1e2a435e22cc92efbfbd1b5476efbfbdefbfbd5defbfbd36efbfbdefbfbd0fefbfbd5defbfbdefbfbd243c56efbfbdefbfbd10efbfbd6d7c75efbfbdefbfbdefbfbd76efbfbd0cefbfbd525f072e2652dea36529efbfbd4befbfbddd8aefbfbd17efbfbdefbfbd641d7a4befbfbdefbfbd1620efbfbdefbfbd17efbfbd212f12efbfbd0cefbfbd2f51efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4b67efbfbd5defbfbd02536defbfbd56efbfbd60efbfbdefbfbd33735defbfbd78efbfbd5d233912efbfbdefbfbdefbfbd346befbfbdefbfbd4eefbfbd214aefbfbd60efbfbd337a0540efbfbdefbfbd255aefbfbd1e12efbfbdefbfbdefbfbd74364a2eefbfbd6eefbfbdefbfbd39efbfbd3f5defbfbdefbfbd3931efbfbd28efbfbd03efbfbdefbfbdefbfbd43efbfbd5fefbfbdd28cefbfbd41142027efbfbd5a3a053d4f34efbfbd2e48416fefbfbdefbfbd3461efbfbd3a7eefbfbd37efbfbd2aefbfbdefbfbd5befbfbdefbfbdefbfbdd4b7efbfbd192336efbfbd73efbfbdefbfbd3524efbfbd1541efbfbd4c21efbfbd5151efbfbd0aefbfbd38efbfbd0a6a2c3049040c0eefbfbdefbfbd0038efbfbd4c4a00637cefbfbd6cefbfbdefbfbd6e56efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2210efbfbd43453d4b07efbfbdefbfbdefbfbd5962efbfbd1d7a3e63efbfbddfa4250021efbfbdefbfbdefbfbd496aefbfbdefbfbd70267c2eefbfbd7f53efbfbdefbfbdefbfbd210d28efbfbd68312f00426befbfbdefbfbdefbfbd1046efbfbd2e2defbfbd04efbfbdefbfbd3a48efbfbd63457c2b18efbfbd53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd01efbfbd78efbfbdefbfbdefbfbd2c38efbfbd5b7a20efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd477cefbfbd5f580065145521efbfbd7d49efbfbdefbfbdefbfbdefbfbd5b620902efbfbd4e60efbfbd24590cefbfbd0803efbfbd4cefbfbdefbfbd1c5befbfbdefbfbd1c0524efbfbd6d62efbfbd7defbfbd7eefbfbd18efbfbdefbfbdefbfbd292805efbfbd0c2249401ad9bcefbfbd241cefbfbdefbfbdefbfbd1d1e4f4457efbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd6b12efbfbdc7ab6a1d5e13efbfbd7e35efbfbd3728efbfbdefbfbdc28e243464efbfbdefbfbd35efbfbdefbfbd60451aefbfbdefbfbdefbfbdefbfbd10efbfbd29efbfbd4fefbfbddf91060e4aefbfbd7befbfbd0aefbfbdec8c97efbfbd5d0819efbfbd521aefbfbdefbfbde68f887341efbfbd1723efbfbd32efbfbdefbfbd79efbfbdefbfbd5372064c5e1fefbfbd5aefbfbdefbfbd5e01efbfbd0300efbfbdefbfbdefbfbd1f3fefbfbdefbfbdefbfbd1defbfbd6c47006bda80efbfbdefbfbd1804efbfbdefbfbd46efbfbdefbfbd26efbfbdefbfbd247a00efbfbdefbfbdefbfbdc7bd5b5fefbfbdefbfbdefbfbdefbfbde3a78214efbfbdefbfbd2fefbfbdefbfbdefbfbdd1b1efbfbdc6a0115aefbfbdefbfbd30efbfbdddae5c646b4a2defbfbdefbfbd68323c212f62efbfbd5d6c4554456e7aefbfbd212007efbfbdefbfbd5befbfbd752defbfbdefbfbdefbfbd0d1a6347efbfbd3cefbfbdefbfbd2fefbfbd16efbfbd53efbfbd4a494aefbfbdefbfbd7fefbfbd2aefbfbdefbfbd4b46440cefbfbd2aefbfbd20efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd76efbfbd7fefbfbd693312efbfbd20efbfbd0e70187eefbfbd7245efbfbdefbfbd5c7763efbfbdefbfbddf9cefbfbdd895efbfbd4427efbfbd4ed89a54efbfbdefbfbd13efbfbd08efbfbd0202efbfbd7cefbfbd4defbfbd2376efbfbd6c500e215c43efbfbd14efbfbd777befbfbd222cefbfbd073325efbfbdefbfbd03efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6d161cefbfbdefbfbd0b14efbfbd5351efbfbd68efbfbdefbfbd38efbfbd09efbfbdefbfbdefbfbd41efbfbdefbfbd79efbfbdc7b84c6cefbfbd6a524d59efbfbdefbfbd52efbfbdefbfbdefbfbdefbfbd58efbfbd0d7e506defbfbd56efbfbdefbfbd5defbfbd41efbfbd1f333d60d7ad20efbfbdefbfbd2d4befbfbd09efbfbd7defbfbd23efbfbd48efbfbdefbfbd5e15efbfbdefbfbdefbfbdefbfbd194965efbfbd1f1aefbfbd3d4befbfbd7f50efbfbd72c6b077efbfbd29efbfbdefbfbd09efbfbdefbfbd58efbfbd7e1714efbfbd442651efbfbdefbfbdefbfbd17efbfbdefbfbd050defbfbd1a30efbfbdefbfbd47550535efbfbdefbfbd071eefbfbd67efbfbdefbfbd30efbfbd5befbfbd2b79374626717d2befbfbdefbfbd42efbfbd1fefbfbdefbfbdefbfbdefbfbd2c172e057a7cefbfbdefbfbd31efbfbd32efbfbdefbfbd39321eefbfbdefbfbd743e3417efbfbd390904efbfbd55efbfbd23103104195aefbfbdefbfbd7cefbfbd32efbfbd62cf961defbfbd23efbfbdefbfbd6defbfbd33efbfbdefbfbdefbfbdefbfbd51efbfbd4aefbfbd1befbfbd634aefbfbd585249efbfbd51117befbfbd333b53efbfbd355465efbfbd66550041dfa5efbfbd391defbfbd1a3f63efbfbd1505efbfbdefbfbd5600272befbfbdefbfbd49efbfbdefbfbd58293a312004efbfbdefbfbdefbfbd73efbfbdefbfbd01efbfbd7befbfbd1747efbfbd3d7a5619634aefbfbd507f07d5a7efbfbd1a3d2befbfbd663103efbfbd29efbfbdefbfbd64efbfbdefbfbd77efbfbddc9befbfbdefbfbd3526efbfbdefbfbd164fefbfbdefbfbdefbfbdefbfbd511a0b1a4aefbfbdefbfbdefbfbd1a0b1aefbfbd58103c4539740eefbfbd0aefbfbd66efbfbdefbfbdefbfbd346a380fefbfbdefbfbd1d76efbfbd271cefbfbd343befbfbdefbfbd5876e2948d16efbfbd14d1bf376befbfbd5defbfbd00efbfbdefbfbdefbfbd32efbfbdefbfbd32efbfbdd5865840efbfbd67440e0befbfbd1f3d4b3ecaaaefbfbd743a1370efbfbd49efbfbd18efbfbd2d2eefbfbd5acbaa392eefbfbd23efbfbdefbfbdefbfbd386aefbfbdefbfbd5cefbfbd5068efbfbdefbfbdefbfbdefbfbdefbfbdd699efbfbdefbfbdefbfbdefbfbd6defbfbd2e46c3ba20efbfbdefbfbd58efbfbdefbfbd526f20efbfbd2e3806efbfbd43efbfbdefbfbdefbfbd281252d99941efbfbd1b63efbfbdefbfbd552a50efbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbdefbfbd1174efbfbd2d55efbfbd00efbfbdefbfbd19efbfbdefbfbd607eefbfbdefbfbd4437efbfbdefbfbd783aefbfbdefbfbd70efbfbd7221efbfbd7b1aefbfbdefbfbdefbfbd3cefbfbdefbfbdefbfbd3140efbfbdefbfbd40efbfbd0b29efbfbd0703efbfbdefbfbd457559377f7fefbfbdefbfbd50efbfbdefbfbd48efbfbdefbfbd49efbfbdefbfbd17dbbe75efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbd07504b070836efbfbdefbfbd4d2913000005efbfbd0000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c00000073657474696e67732e786d6cefbfbd5aefbfbd6e234915efbfbdefbfbd29efbfbd05120832efbfbdefbfbdefbfbdefbfbdefbfbd64efbfbd76efbfbdefbfbdefbfbdefbfbd6d3b317051efbfbd2edb9d545735efbfbdd5b11defbfbdd49bd995766159400209efbfbdefbfbd5d76efbfbdefbfbd4770efbfbdce8c34efbfbdefbfbd7eefbfbd7eefbfbd3defbfbd76efbfbdefbfbdefbfbd26efbfbd7649efbfbd224eefbfbd55efbfbdefbfbd3975efbfbd77efbfbd53efbfbdcf9eefbfbd4defbfbd74efbfbd6defbfbd60742b127b12efbfbd2c61efbfbd31dda0efbfbdefbfbd484defbfbd2e3fefbfbd3cefbfbdefbfbdefbfbd33efbfbd6e1b1a4eefbfbd4c734d4cefbfbdefbfbdefbfbd39efbfbd21efbfbd124cefbfbd4e72efbfbd782befbfbdefbfbd34c990633849efbfbd4cefbfbd24efbfbdefbfbd6416efbfbdefbfbdd392efbfbd47274365efbfbd6fefbfbdc4a0efbfbd5befbfbd2eefbfbd567265efbfbdefbfbdefbfbd3defbfbd3e61766725efbfbd482456c2a7efbfbd433546efbfbd46e7b1aa46efbfbd6fefbfbd62efbfbdefbfbd28121346efbfbd09efbfbdc5a3d1b5efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd226fefbfbd261ed9beefbfbdc3b5efbfbdefbfbdefbfbdefbfbd0a461fefbfbd06c7a6efbfbdefbfbdefbfbdefbfbd6befbfbdefbfbdefbfbd08efbfbd4cefbfbd19efbfbd77efbfbdc8a479df9d53efbfbdf18a8d91caacefbfbdefbfbd133eefbfbdefbfbd0961efbfbd13d98e3e5befbfbd2fefbfbdefbfbd6273efbfbdefbfbd65efbfbd6d183aefbfbd4e14efbfbdefbfbd341a5fefbfbd4fefbfbd1e363aefbfbdefbfbdcb8eefbfbd57575767efbfbd5eefbfbdefbfbd5e05efbfbd106338efbfbd45efbfbdefbfbdefbfbd3b1a5aefbfbd11efbfbd6864efbfbdefbfbd2eefbfbd4defbfbd3e4dd9acefbfbdefbfbd3cefbfbdefbfbd34efbfbd6d44efbfbd47efbfbd5f36efbfbdefbfbd6c501defbfbdefbfbd7eefbfbd59efbfbd032cefbfbd03efbfbd610f1eefbfbdefbfbd7defbfbdefbfbd521d6e1befbfbdefbfbd22efbfbdefbfbd6fefbfbdefbfbdd88b45efbfbd37efbfbd67efbfbd3b2d55efbfbd36efbfbd670e3cefbfbd6811efbfbdefbfbd6409efbfbd2e3cefbfbd43efbfbdefbfbdefbfbd39223270efbfbd6d0b65efbfbd18efbfbdcc9cefbfbdefbfbdefbfbdd98437193355107537dabaefbfbdefbfbd7301520e0defbfbdefbfbdd38cefbfbd26efbfbdefbfbdd48befbfbdefbfbd62efbfbd7461597defbfbd2f59efbfbd71664f5e7b2c3aefbfbdefbfbdefbfbdefbfbd2a2658efbfbd58efbfbdefbfbdefbfbdefbfbd0c4befbfbdefbfbdefbfbd6d74efbfbdefbfbd780c58efbfbd0740717c7c391d7defbfbdda884371efbfbd5fefbfbd6a0920efbfbd2befbfbd321e4eefbfbdefbfbd75efbfbd454628efbfbdefbfbd3a38efbfbdefbfbdd38eefbfbd5c7a174517504e42252aefbfbdefbfbd12411aefbfbd32efbfbdefbfbd31efbfbdefbfbd000cefbfbdefbfbd19efbfbdefbfbdefbfbd50264b43c6b4efbfbd40efbfbd4c42efbfbd1d6defbfbdefbfbd1c0befbfbd5defbfbd0121efbfbd4a350059efbfbdefbfbdefbfbdccac62efbfbdefbfbd2d1a0b3323efbfbd207b19efbfbd6044efbfbd6c61efbfbd3a70efbfbd4116efbfbd4cefbfbd30efbfbd0569625816efbfbd4befbfbdefbfbd5d7befbfbd46efbfbdefbfbd1424dca92c1befbfbdefbfbdefbfbd12efbfbd51115aefbfbd1d1befbfbd42605befbfbdefbfbd2c61efbfbd730455efbfbdefbfbd4c6477efbfbd7b2572115a6aefbfbd05efbfbdefbfbd3454efbfbd4214d892efbfbd093707efbfbd13efbfbd033978632d6550640f22efbfbd27efbfbdefbfbd4f5662efbfbd6eefbfbd106befbfbd1a2f47efbfbdefbfbd3935efbfbd2fefbfbd2befbfbd16efbfbd0f50efbfbd7eefbfbdefbfbd1fefbfbdefbfbd63efbfbd72efbfbdefbfbd52efbfbd1befbfbd73efbfbdefbfbd4e73efbfbd42efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd17efbfbd6612efbfbdefbfbdc2a10d45efbfbd2b76673cefbfbdefbfbd6414efbfbd5befbfbdefbfbd323aefbfbdefbfbdefbfbd09efbfbdefbfbdefbfbdefbfbd1e57efbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbd683f51efbfbd67efbfbdefbfbd5a77efbfbdefbfbdd88eefbfbdefbfbdefbfbdefbfbd796635efbfbdefbfbdefbfbdcda303efbfbd79efbfbd3f6cefbfbdefbfbd27efbfbd3d3eefbfbd58efbfbdefbfbd5aefbfbdefbfbdefbfbd18efbfbd7b075d74543e3cefbfbd277aefbfbd5f253573efbfbd6a6407efbfbd3871efbfbdefbfbdefbfbd5fefbfbd47efbfbd68efbfbd5177efbfbdefbfbd682fefbfbdefbfbd38efbfbd74efbfbdefbfbd360b672defbfbdefbfbddcadefbfbdefbfbdefbfbd4e227f523eefbfbdefbfbd0a4eefbfbd495a300eefbfbd74efbfbd342b4437efbfbd4953efbfbdefbfbd6fefbfbdefbfbdefbfbd46efbfbdefbfbd54e5b0964d65efbfbdefbfbdefbfbdefbfbdefbfbd425befbfbd56efbfbdefbfbdefbfbdefbfbd5aefbfbd6c34552b5510efbfbd76523b6a34562eefbfbd2befbfbd4aefbfbd5e5633efbfbdefbfbd68efbfbd54efbfbdd48a4aefbfbd52efbfbd65efbfbdefbfbd463651574f7951efbfbdefbfbdefbfbd191954efbfbd321b670defbfbdefbfbd39efbfbd1befbfbdefbfbdefbfbd50efbfbd4f6f427cefbfbd100a2cefbfbd20efbfbdefbfbd61efbfbd4c634204442c5e4defbfbdefbfbdefbfbdefbfbdefbfbd7013efbfbd2cefbfbdefbfbd39efbfbd7429e6a8a8efbfbd70efbfbd57efbfbd5c073aefbfbd3d1d1b595d67efbfbdefbfbd0c475aefbfbdefbfbd2603153527efbfbd0cefbfbdefbfbd0defbfbdefbfbd204056efbfbdefbfbd34334d74efbfbd01efbfbdefbfbdefbfbdc7acefbfbd71582befbfbdefbfbd6ed7b538efbfbdefbfbd1117efbfbdefbfbd52e790af0ad8afefbfbdefbfbd21efbfbd32480befbfbd15efbfbd00efbfbd2befbfbd690322efbfbd48efbfbdefbfbdefbfbd12efbfbd515eefbfbd12efbfbdefbfbd09efbfbd0c67d0b6efbfbd6276efbfbd49efbfbdefbfbd0aefbfbdefbfbdefbfbd2518efbfbdefbfbdefbfbd60efbfbd53efbfbdefbfbd584a3029c7905eefbfbdefbfbd66efbfbd0cefbfbd784c10efbfbd7cefbfbd2c24efbfbd1fefbfbdd88d13043aefbfbd437ccf8cdb85efbfbd11efbfbd567370efbfbdefbfbd6326efbfbdc790efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbdefbfbd52efbfbd0e74efbfbdefbfbd3aefbfbddea2efbfbdefbfbd11efbfbd5c12efbfbdefbfbdefbfbd36efbfbd4d584fefbfbdefbfbd30742b181a71efbfbdefbfbd0ae7b6b00aefbfbdefbfbdefbfbd3278efbfbdefbfbd05287eefbfbdefbfbdefbfbdefbfbd32efbfbdefbfbd2115efbfbd5a4803efbfbd2a0b114d545f1920530134efbfbd3076efbfbd5d10efbfbdefbfbdefbfbdefbfbd4462732e6a2c404bdc8f0072efbfbd4eefbfbdefbfbd1cefbfbd427c2c4303c5882b027146efbfbd11efbfbd12efbfbd3d60efbfbd34efbfbd1a26121811747cefbfbdefbfbdefbfbdefbfbdefbfbd6860efbfbd42efbfbd2e24efbfbddeb061efbfbdefbfbd25efbfbdefbfbd6c4b4a4f13efbfbdc4835c6f1e0defbfbdefbfbd38160defbfbdefbfbd4d0b20616a01efbfbd2befbfbd430f15efbfbd6defbfbd5a594defbfbdefbfbd5679efbfbd02efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbd5326efbfbd4025efbfbd2a03efbfbdefbfbd104921056a6e74efbfbd52efbfbdefbfbd497324efbfbd77efbfbdefbfbdefbfbdefbfbd32efbfbdefbfbdefbfbd1defbfbd3eefbfbdefbfbd56efbfbd19efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd150c38efbfbdefbfbdefbfbd45efbfbdefbfbdefbfbdefbfbd03efbfbd4aefbfbd0d4c7447660e0aefbfbdefbfbdefbfbdefbfbd224d13efbfbdefbfbd2061efbfbddb8509efbfbd216111efbfbdefbfbd48efbfbd062241efbfbd05efbfbd2e4c5aefbfbd7d4befbfbd44efbfbd0077754a18efbfbdefbfbd7220efbfbd1b1befbfbdefbfbd43efbfbdefbfbdefbfbd044369efbfbd1165efbfbdefbfbdefbfbdefbfbd62efbfbd22efbfbd40efbfbd3b0f7c39efbfbdefbfbd41710c444b2ed5b82befbfbdefbfbdefbfbdefbfbdefbfbd156defbfbd1432efbfbd1569efbfbdefbfbdefbfbdefbfbd2aefbfbd22144cefbfbdefbfbd06786cefbfbdefbfbd1137013defbfbd3d770ed18e7befbfbdefbfbdefbfbd26432768efbfbdefbfbdefbfbd15efbfbdefbfbd483d28efbfbd78efbfbd6c08efbfbd29efbfbdefbfbd1150efbfbdefbfbdd08befbfbdefbfbd32655defbfbdefbfbdefbfbd7fefbfbd24efbfbdefbfbdefbfbdefbfbdcdbb2fefbfbdefbfbdefbfbdefbfbdefbfbddb95efbfbdefbfbdca833fefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd5defbfbdefbfbd07efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7eefbfbd7b7fefbfbd3fefbfbdefbfbd5fefbfbdefbfbdefbfbdefbfbdc5a7efbfbdefbfbd2befbfbdefbfbd33efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbdc59b60efbfbd5e30efbfbd20187e140c3f0eefbfbdefbfbd04c3bf07c397efbfbdefbfbdefbfbd6078195cefbfbd0a2eefbfbd082eefbfbd0a2eefbfbd195cefbfbd0eefbfbd7c16efbfbdefbfbd3c78efbfbdefbfbd19efbfbd06efbfbdefbfbdefbfbd593fefbfbdefbfbdefbfbd7fefbfbdefbfbd775fefbfbd7b7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdc7beefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1c0cefbfbd0fefbfbd1f06c3afefbfbdcb97efbfbdefbfbdefbfbdefbfbd2fefbfbdefbfbdefbfbdefbfbd5c7c3e0c0c39efbfbdefbfbd37efbfbd541622efbfbdefbfbd374917560a313a14efbfbdefbfbd2a67efbfbd3561efbfbd745472efbfbd3aefbfbd680fefbfbdefbfbd3b0defbfbd77f388ba88efbfbdc485efbfbdefbfbdca83efbfbdefbfbdefbfbd0befbfbdefbfbd38efbfbd22efbfbd71696739efbfbd3b5a5eefbfbd6d46efbfbdefbfbdefbfbd4defbfbd33efbfbd15efbfbd683725efbfbd0050efbfbd58d89eefbfbd37efbfbdefbfbd1f7359efbfbd5739685defbfbdefbfbd3cefbfbdefbfbd14efbfbd27efbfbd0defbfbdefbfbdefbfbdefbfbd38efbfbd3defbfbdefbfbdefbfbdefbfbd56efbfbdefbfbdefbfbdefbfbd3976efbfbdefbfbd4befbfbd396336efbfbdefbfbdd383efbfbd3104efbfbd0f20080b6c07efbfbdefbfbd4b64787642efbfbdefbfbdefbfbd625964005defbfbd2defbfbdefbfbdefbfbd7d2defbfbdefbfbd513a4d7fefbfbd64efbfbd0b5253df8659efbfbdefbfbdefbfbdefbfbdcab41771efbfbdefbfbd01504b0708efbfbdefbfbd08efbfbd7b080000efbfbd2b0000504b0304140008080800efbfbd60efbfbd42000000000000000000000000080000006d6574612e786d6cefbfbdefbfbd4d6fefbfbd3010efbfbdefbfbdefbfbd2b2cefbfbd573026efbfbd345642efbfbd3defbfbdefbfbd55efbfbd5befbfbd59efbfbdefbfbd2ac79eefbfbdefbfbdefbfbd46c684efbfbddfafefbfbd14efbfbd7aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd58efbfbd13efbfbd4a6aefbfbdefbfbd48107a0814efbfbd42efbfbd6cefbfbdefbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd5aefbfbdefbfbdefbfbd0315efbfbdefbfbd4750efbfbd3fefbfbd65c8a5efbfbdefbfbdefbfbdefbfbdefbfbd571b4535efbfbd6445153b42452defbfbdefbfbd0435efbfbdefbfbd394defbfbd46efbfbdefbfbd5e48efbfbd6fefbfbdefbfbdd69614efbfbd69efbfbd661168efbfbd61efbfbd5aefbfbd70171d51efbfbd27efbfbdefbfbd4defbfbd51efbfbd6328efbfbdefbfbd506112103cefbfbdefbfbdefbfbd6b4defbfbdefbfbddc92efbfbd7a6aefbfbdefbfbdefbfbd5d14efbfbd31efbfbdefbfbd473a334214efbfbd0defbfbdefbfbd05760eefbfbd65efbfbd4942efbfbdefbfbd43efbfbdefbfbdefbfbdefbfbd475e3a6eefbfbdefbfbdefbfbdefbfbd3b33192830efbfbd6aefbfbd3ec99defbfbd5f1defbfbdefbfbd200cefbfbd20efbfbd79efbfbdefbfbd7e7fefbfbd7b77efbfbd761befbfbd19efbfbd561a7d006e711cefbfbdefbfbdefbfbdefbfbd5b2d0befbfbd476b7c51722d38efbfbdefbfbd16efbfbd3e48303b794019efbfbd40efbfbdc69d6aefbfbd4b6118efbfbd2defbfbddaa21c2c2a5cd0b8efbfbdefbfbd292400efbfbd74efbfbd4aefbfbd43efbfbdefbfbd78efbfbdefbfbddb964a5aefbfbd0aefbfbd1befbfbd3a3d32efbfbdefbfbdefbfbdefbfbd7b3eefbfbd01efbfbd38efbfbd3cefbfbd19efbfbd5cefbfbd7defbfbdefbfbdceadefbfbd775befbfbdefbfbddda0efbfbd2eefbfbddc9225efbfbd121aefbfbd43efbfbd39efbfbd4e342410efbfbdefbfbdefbfbd24d992efbfbdefbfbd09efbfbdefbfbdefbfbd644f75efbfbdefbfbdefbfbdefbfbd051e69e287b11fefbfbd2d2134efbfbd2849efbfbdefbfbd0c1d665c5befbfbd602aefbfbd5906efbfbd2aefbfbdefbfbd4f5defbfbd49efbfbd0befbfbd7003efbfbdefbfbdefbfbd1fefbfbdefbfbd2a25713c54efbfbd08efbfbdd3a236efbfbd20efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd78befbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd3eefbfbd4730efbfbd3defbfbdefbfbd5c16efbfbdefbfbdefbfbdefbfbd78efbfbd7b5cefbfbd33187950efbfbdefbfbdeea0b26eefbfbdefbfbdefbfbd4f1f77655defbfbdefbfbd4aefbfbd3addb25d013ed7b5efbfbd1b6fefbfbdefbfbdefbfbd3cefbfbdefbfbd47311c44efbfbd6bdfbe4befbfbdefbfbdefbfbd64efbfbd0cefbfbd0c2befbfbdefbfbd58efbfbdefbfbd46efbfbdefbfbdefbfbd761079efbfbd786eefbfbdefbfbdefbfbdefbfbd6835efbfbdefbfbd7b07efbfbd5c5aefbfbd4aefbfbd5defbfbd4b3271241e26efbfbd2befbfbd0befbfbd4b0562efbfbd767fefbfbdefbfbdefbfbd43efbfbd3522efbfbd61efbfbd75efbfbdefbfbd3a2c6e317cefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7f504b0708efbfbd31efbfbd36efbfbd020000efbfbd050000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c0000006d616e69666573742e726466cd93efbfbd6eefbfbd301044efbfbd7cefbfbd65efbfbd60efbfbdefbfbdefbfbd023914efbfbd5cefbfbd5fefbfbd1aefbfbd58052fefbfbd2e25efbfbd7d5d27efbfbdefbfbd1cefbfbd2acda1efbfbd5defbfbd66efbfbd68efbfbdefbfbdefbfbd380eefbfbd433b34606befbfbdefbfbd1967efbfbd2aefbfbdefbfbdefbfbd7cefbfbd3e79efbfbdefbfbd26dab8efbfbdefbfbd5eefbfbd1defbfbd6aefbfbdefbfbdefbfbd6a7e20efbfbd2a21efbfbd6549efbfbdefbfbd14efbfbd5eefbfbd6559efbfbdefbfbd1045efbfbd7845efbfbdefbfbd25794c2cc6bcefbfbd180b1eefbfbd46efbfbdefbfbd443eefbfbd7defbfbdefbfbd0d66efbfbd7910efbfbd25efbfbd4e3aefbfbd39efbfbd303befbfbdefbfbdefbfbd3a50efbfbdefbfbd44efbfbd094cda864cefbfbd02efbfbdefbfbd282defbfbd10efbfbd2629efbfbdefbfbd7ddc82efbfbd476defbfbdefbfbd102defbfbdefbfbd7fefbfbd6331efbfbd0e12efbfbdefbfbdefbfbd7375efbfbdefbfbdefbfbdefbfbd5f355260efbfbdefbfbdefbfbdefbfbdefbfbd2222efbfbdefbfbdefbfbd3f105e76efbfbdefbfbd7defbfbdefbfbde3a793efbfbdefbfbd0cefbfbd46efbfbdefbfbd7aefbfbdefbfbd7b0defbfbd3fefbfbdefbfbd56efbfbd4735efbfbd27504b0708efbfbd3defbfbdefbfbd00010000efbfbd030000504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f706f7075706d656e752f504b0304140000080000efbfbd60efbfbd420000000000000000000000001f000000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b0304140000080000efbfbd60efbfbd420000000000000000000000001c000000436f6e66696775726174696f6e73322f70726f67726573736261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f746f6f6c6261722f504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f666c6f617465722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f6d656e756261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000027000000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f7374617475736261722f504b0304140008080800efbfbd60efbfbd42000000000000000000000000150000004d4554412d494e462f6d616e69666573742e786d6cefbfbd54efbfbd6eefbfbd2010efbfbdefbfbd2b2cefbfbdefbfbdefbfbdcda9427172efbfbdefbfbd2f483fefbfbde2b583040befbfbd25efbfbdefbfbdefbfbd386a1e55efbfbd2a567defbfbdefbfbdefbfbdefbfbd081636efbfbdefbfbdefbfbdefbfbd1162321e1befbfbdc29f5905efbfbd7d6befbfbd6fefbfbdefbfbdefbfbdefbfbd7e65efbfbdefbfbd6aefbfbd14efbfbd0e12efbfbd4b50efbfbd394cd7b46139efbfbdefbfbd2aefbfbd24513948efbfbdefbfbdefbfbd01efbfbdefbfbd3a3b40efbfbd3fefbfbdefbfbd74efbfbdefbfbd0cefbfbdefbfbd7655efbfbdefbfbd3a63efbfbd2eefbfbd71efbfbdefbfbdefbfbd6c6d1d141d1a26efbfbd486e6507efbfbd51350d011aefbfbd42efbfbd462b2a3071c496efbfbd0defbfbd7befbfbdefbfbdefbfbd444cefbfbdefbfbd3f64efbfbdefbfbdefbfbdefbfbd24efbfbd12efbfbdefbfbdefbfbd07efbfbd540f62efbfbdefbfbd52efbfbd1e69efbfbd57efbfbd71efbfbd78742eefbfbdefbfbd2cefbfbd44efbfbdefbfbdefbfbd3c2d10efbfbd1d5aefbfbdefbfbd01efbfbdefbfbd49efbfbd6b3cefbfbdefbfbd03efbfbd53504fefbfbd35efbfbd3c76efbfbdefbfbdefbfbd4cefbfbdefbfbd42690d164aefbfbdefbfbd39c6bf2fefbfbd7f5a0f3eefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6114df885f7fefbfbdefbfbd0b504b0708efbfbd5cefbfbd4a1a0100003e040000504b01021400140000080000efbfbd60efbfbd425eefbfbd320c27000000270000000800000000000000000000000000000000006d696d6574797065504b01021400140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd21000018000000000000000000000000004d0000005468756d626e61696c732f7468756d626e61696c2e706e67504b01021400140008080800efbfbd60efbfbd4260efbfbdefbfbd01750a0000efbfbd6300000b000000000000000000000000006d220000636f6e74656e742e786d6c504b01021400140008080800efbfbd60efbfbd4236efbfbdefbfbd4d2913000005efbfbd00000a000000000000000000000000001b2d00007374796c65732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbdefbfbd08efbfbd7b080000efbfbd2b00000c000000000000000000000000007c40000073657474696e67732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd31efbfbd36efbfbd020000efbfbd0500000800000000000000000000000000314900006d6574612e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd3defbfbdefbfbd00010000efbfbd0300000c00000000000000000000000000efbfbd4b00006d616e69666573742e726466504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000254d0000436f6e66696775726174696f6e73322f706f7075706d656e752f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001f000000000000000000000000005d4d0000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001c00000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f70726f67726573736261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f746f6f6c6261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a000000000000000000000000000a4e0000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000424e0000436f6e66696775726174696f6e73322f666c6f617465722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000784e0000436f6e66696775726174696f6e73322f6d656e756261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000002700000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f7374617475736261722f504b01021400140008080800efbfbd60efbfbd42efbfbd5cefbfbd4a1a0100003e04000015000000000000000000000000002b4f00004d4554412d494e462f6d616e69666573742e786d6c504b0506000000001100110070040000efbfbd5000000000, 'odt');
INSERT INTO `bs_doc_templates` (`id`, `book_id`, `user_id`, `name`, `content`, `extension`) VALUES
(3, 3, 1, 'Invoice', 0x504b0304140000080000efbfbd60efbfbd425eefbfbd320c2700000027000000080000006d696d65747970656170706c69636174696f6e2f766e642e6f617369732e6f70656e646f63756d656e742e74657874504b0304140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd210000180000005468756d626e61696c732f7468756d626e61696c2e706e67efbfbd504e470d0a1a0a0000000d49484452000000efbfbd0000010008020000007a41efbfbdefbfbd000021efbfbd4944415478efbfbdefbfbd77401447efbfbdc7b7efbfbd5eefbfbdefbfbd1c70efbfbddebb020a220808efbfbdefbfbdc782efbfbd684c3131efbfbd29efbfbd79efbfbd7dd3935fefbfbd29efbfbdefbfbd184befbfbd44efbfbdefbfbd68efbfbd5863057b14efbfbd053c44efbfbdefbfbd38eeb8b277efbfbdefbfbd421518efbfbd4c10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3373efbfbd5f66efbfbd79667660efbfbdefbfbd7a0c00efbfbdefbfbdefbfbdefbfbd15007a35efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbdefbfbd4357efbfbdd3bcefbfbd37efbfbdefbfbd55efbfbdefbfbd5befbfbdefbfbdceb6efbfbdefbfbdefbfbd25efbfbd07efbfbd0e78efbfbdefbfbdefbfbdefbfbd725befbfbd35efbfbdefbfbd3722efbfbd3eefbfbd5f3c547aefbfbd0cd787cc8edfb6efbfbd48efbfbdefbfbd714fefbfbdefbfbd27efbfbd2defbfbdefbfbdefbfbd7cefbfbd4b5fefbfbd5f7b2b5d44efbfbd26efbfbd7eefbfbdefbfbdefbfbdefbfbd676fefbfbd70efbfbdefbfbdefbfbd375cefbfbd7defbfbd3e23efbfbdefbfbd6defbfbdefbfbd2e2fefbfbdefbfbdefbfbd14efbfbdefbfbdefbfbd63efbfbdefbfbdefbfbdd48aefbfbd687f0eefbfbd34efbfbdefbfbdefbfbd083934dd9332efbfbdefbfbd24efbfbdefbfbdefbfbd7c2b37efbfbd39efbfbdefbfbd5851efbfbd255e7cefbfbd74c3a727efbfbdefbfbd4cefbfbd0b78e0b1a2efbfbd6038efbfbdefbfbd7cefbfbdefbfbd0c59efbfbd5aefbfbdefbfbdefbfbd2bc888efbfbdefbfbdc39fefbfbdefbfbdefbfbd7cefbfbdefbfbd794befbfbd2b22315defbfbdefbfbd7eefbfbd57efbfbd1840566defbfbd46efbfbd0cefbfbdd0967defbfbd3874efbfbd6befbfbdefbfbdefbfbd2defbfbdefbfbd0a282619efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbd5fefbfbdefbfbdefbfbd45efbfbd4e516f7fefbfbdefbfbd0a3cefbfbdefbfbd7fefbfbd5eefbfbdefbfbdefbfbd35efbfbdefbfbd78efbfbd60efbfbdefbfbd1f1f5befbfbd132321efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7237efbfbdefbfbdefbfbdefbfbd72395defbfbd79efbfbd25daa3efbfbd48efbfbd61efbfbdefbfbd0fefbfbdefbfbd3aefbfbd657943efbfbd3b5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd31efbfbd35472ed88e4f1c10efbfbd60efbfbd61efbfbd52efbfbdefbfbdefbfbd64efbfbdcc877d122befbfbd3761efbfbdefbfbd7074efbfbdefbfbd6c46efbfbdefbfbdefbfbd03efbfbdefbfbd1fefbfbd2f4015efbfbdefbfbd67efbfbd5f53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd11c298efbfbd6b5f2cefbfbdefbfbdefbfbd3f6f28efbfbdefbfbdefbfbdca9a72efbfbdcea76d6360efbfbdefbfbdefbfbd1eefbfbd7065efbfbd7edc8943efbfbdefbfbdcbaeefbfbdefbfbdefbfbdefbfbd27efbfbd6367efbfbdefbfbdefbfbdefbfbd2aefbfbd2049efbfbd202cefbfbd78efbfbd240f65237d13efbfbdefbfbd7aefbfbdde85efbfbd19ce89e3939cefbfbd6befbfbdefbfbd4c0eefbfbd64efbfbd18efbfbdefbfbd215b71efbfbd66efbfbd707befbfbddbaa36efbfbd61efbfbd38efbfbd76435b7defbfbd504b0534efbfbd30efbfbd2430efbfbd305952efbfbdefbfbd39efbfbd46185a19efbfbd1e0c3f2458efbfbd0fefbfbd65c68a4fcdbbefbfbd671a3e1273cf980e16efbfbdefbfbdefbfbd39cb8c151f183fefbfbd07efbfbdefbfbdd585efbfbdefbfbdefbfbd2c316cefbfbdefbfbdefbfbd5811efbfbdefbfbd21efbfbddf9eefbfbdefbfbd59efbfbdefbfbd5873cab2efbfbd5befbfbd0e5c3fd3bcefbfbdefbfbdefbfbd6fefbfbdefbfbd52efbfbd17efbfbdefbfbd7fefbfbd3befbfbd2aefbfbd295cefbfbdefbfbdefbfbdefbfbd4fefbfbd451c18efbfbd77efbfbd0b4f35efbfbd6726efbfbd36efbfbd47193fefbfbd33efbfbdefbfbdefbfbd1961efbfbd5c3eefbfbd33efbfbdefbfbdefbfbd4341efbfbd693719efbfbdefbfbdefbfbdefbfbd77efbfbd5c2cefbfbdefbfbd69efbfbdefbfbd47793fefbfbdefbfbd27efbfbd55efbfbd3e4fefbfbdefbfbd07efbfbd1b017d0028401f000a0befbfbdefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbd5e4c69efbfbd05efbfbdefbfbdefbfbd0e54070defbfbd637775514b06efbfbdefbfbd554b0befbfbd2cefbfbd10efbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd79efbfbd6cefbfbd18efbfbdefbfbd2b72efbfbd505defbfbd5defbfbdefbfbd0befbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd716e45efbfbd72efbfbd4c71efbfbdd4903fefbfbd2cefbfbdefbfbdefbfbd75efbfbdefbfbd0f17efbfbdefbfbd0aefbfbdefbfbddcb8d390efbfbd71efbfbd58efbfbd54efbfbdefbfbd44efbfbd5a3d685876efbfbdefbfbd25efbfbd047cefbfbdefbfbd5eefbfbd74efbfbd3979efbfbd4c1befbfbdefbfbd76553fd289efbfbdefbfbdefbfbd09efbfbdefbfbd7aefbfbdefbfbd63626a5523efbfbd776060383f28354644625a077f2722df98efbfbdefbfbdc7afefbfbdefbfbdefbfbdefbfbd76d0803c6058efbfbd0fefbfbd47efbfbdefbfbd563aefbfbdefbfbd347b021b3befbfbdd4bfefbfbd3eefbfbdefbfbd39efbfbd435653efbfbdefbfbd18efbfbd5853efbfbd32efbfbdefbfbd77efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbd0a1b6d03efbfbd78efbfbdefbfbdefbfbd393eefbfbdefbfbd36efbfbd50efbfbdefbfbdefbfbdefbfbd33424c75efbfbdefbfbd6defbfbd4225d9b5efbfbd546c6850efbfbd0eefbfbdefbfbdefbfbdefbfbd5b6b1326efbfbd48361defbfbd1befbfbdefbfbdefbfbd5217efbfbdefbfbd16efbfbd6325efbfbdefbfbd246defbfbdefbfbd09efbfbdefbfbdefbfbd4239efbfbd6c407b2cd487efbfbdd6aaefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6e55efbfbd04293befbfbd62efbfbd2a654258efbfbdefbfbd3e485070efbfbd3aefbfbd734d4e77dcb746efbfbd7fefbfbdefbfbd4a75efbfbd33633c48efbfbdefbfbdd59f6defbfbd1c58efbfbdefbfbdd3ad74daac51351f6fefbfbd29efbfbde98489efbfbdd9bb59efbfbdefbfbdefbfbd6b7e66efbfbd7dc68fefbfbdd4b5357eefbfbdecbaa539efbfbd57efbfbdefbfbddd9f7befbfbd643defbfbdefbfbdefbfbdefbfbdefbfbd663f7eefbfbd587aefbfbdefbfbdcb9cefbfbdefbfbd4eefbfbd2fefbfbdefbfbd72efbfbd2fefbfbd55efbfbdc4bbefbfbde6968aefbfbd63085ad7bd3fefbfbd43efbfbd65efbfbdefbfbdefbfbd56506cefbfbd24efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd637d78efbfbd534a35efbfbdefbfbd31efbfbdefbfbd3f3cefbfbd4d6aefbfbd5aefbfbdefbfbdefbfbdefbfbd49efbfbdefbfbd2867efbfbddfb2efbfbdefbfbd52efbfbdefbfbdefbfbd536831efbfbd2408efbfbd60d88745efbfbd6b49efbfbdefbfbdefbfbdefbfbd44193eefbfbd437befbfbd441346efbfbdefbfbd2aefbfbd5befbfbddfbc28efbfbdefbfbdefbfbd57300709efbfbdefbfbd1aefbfbd71efbfbd6a5461efbfbd1ec3ba32efbfbdefbfbd5c48efbfbdefbfbd68efbfbd56efbfbd32d5934ddeaeefbfbd60efbfbdefbfbdefbfbd33403b2cefbfbd07efbfbd69387cefbfbdefbfbdefbfbd45efbfbd2c0cefbfbd0c1e6148efbfbd057b24747804c49befbfbd2b341579d98764efbfbdefbfbd47efbfbd7cefbfbdefbfbdefbfbd4befbfbd62773e213b6868efbfbd47efbfbd48141aefbfbdefbfbd30efbfbdefbfbd30efbfbd50efbfbdefbfbdefbfbd12efbfbd257befbfbdefbfbd5d0b68efbfbdefbfbd0eefbfbd35efbfbdd5a0efbfbdefbfbd0f46efbfbd6032efbfbdefbfbdefbfbd6aefbfbd2eefbfbdefbfbd431eefbfbd7021efbfbdefbfbdefbfbdefbfbd5c70365befbfbd36187f6f5556efbfbde9a4a6efbfbd08efbfbd4eefbfbd7143354e14723c4353221d29efbfbdefbfbd64286befbfbdefbfbd07efbfbd6125163e5fd8be09efbfbd056b571559efbfbd7cefbfbd22efbfbd796befbfbd38efbfbd1577efbfbdc3a309efbfbdefbfbd461aefbfbdefbfbd0e0fefbfbdefbfbd3953633e7aefbfbdefbfbdefbfbd25733d3eefbfbdefbfbdefbfbdefbfbd0856efbfbd67efbfbd5e65efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6fefbfbdefbfbdefbfbdefbfbd174409efbfbd0747efbfbd2cefbfbd32efbfbd1435d8a9efbfbdefbfbd741defbfbdefbfbd7a2fefbfbd50dfa6efbfbdefbfbd2f5579570cefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdd988efbfbdefbfbd5439efbfbdefbfbdefbfbdefbfbd5d19efbfbd18ce90efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd456c702aefbfbdefbfbd123c23efbfbd27163e5fefbfbdefbfbd4a5aefbfbdefbfbd08303d5fefbfbdefbfbdefbfbd273b646befbfbd7cefbfbdefbfbdefbfbd4befbfbdefbfbd26efbfbd6ad890efbfbdcc83efbfbdefbfbdefbfbddb9911efbfbd476a1aefbfbd48efbfbd63efbfbd0c6218efbfbdefbfbdefbfbdefbfbd2defbfbdefbfbd36efbfbd4b32efbfbd6010efbfbd7defbfbdefbfbdefbfbd2defbfbd24efbfbd0befbfbd20efbfbdefbfbd6d1b76efbfbd57efbfbd30efbfbdefbfbddcb6474cefbfbd2fefbfbd58efbfbd315fefbfbdefbfbd70efbfbdefbfbd74206c6326efbfbd1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbd745befbfbdefbfbdefbfbd3a0c1cefbfbd38efbfbdefbfbdefbfbdefbfbdefbfbd66efbfbdefbfbd505dc99306efbfbdefbfbd5d2defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd17041defbfbd5e4fefbfbdefbfbd1f66d781efbfbdefbfbd7a0a375c52efbfbd1915182d20584c21457a66065eefbfbd52efbfbd02efbfbdefbfbd034037efbfbd1f66efbfbd01efbfbd0e1fefbfbd4cefbfbd4c03305ddd9fefbfbdefbfbd3070261e04efbfbdefbfbdefbfbdefbfbd34efbfbdefbfbdefbfbd181b66703eefbfbdefbfbd0224efbfbdefbfbdefbfbd4b64efbfbdefbfbdefbfbd103befbfbd36efbfbdefbfbdefbfbdefbfbd15efbfbdefbfbd7e5eefbfbdefbfbd2fefbfbddcbfefbfbd6d1a776cefbfbd14efbfbdefbfbd46efbfbd68efbfbd3defbfbd35efbfbdefbfbd7eefbfbdefbfbd644056efbfbdefbfbdefbfbd7d2eefbfbdefbfbd0cefbfbd0befbfbdefbfbd74efbfbd1a0fefbfbd4cefbfbdefbfbd6defbfbd07efbfbdefbfbd59efbfbdefbfbd3c21efbfbdefbfbdefbfbd3c4f4aefbfbd315aefbfbd10efbfbdefbfbd1811efbfbdefbfbd5befbfbd331e626a35652b62efbfbd3641efbfbd35efbfbdefbfbd520235efbfbd2d0757efbfbd3befbfbd46efbfbd63efbfbd33efbfbdefbfbd301cefbfbdefbfbd6defbfbd2125efbfbd50efbfbd303465efbfbd10efbfbd1d6cefbfbd76771a11efbfbdefbfbdd9910b171aefbfbd0cefbfbd1e693e3b2aefbfbd644defbfbd0befbfbdefbfbd062c7cefbfbd7023efbfbd5befbfbd0aefbfbd1defbfbd47efbfbd29efbfbdefbfbdefbfbd64efbfbdefbfbd35efbfbd6f0766efbfbd0128401f000aefbfbdefbfbd41176d5eefbfbd6befbfbdefbfbd2befbfbdefbfbd3eefbfbdefbfbd5d50efbfbdc5af162eefbfbd3defbfbdefbfbdefbfbd6f1cefbfbd0e4a15efbfbd6c08efbfbd776f7aefbfbd1defbfbdefbfbd3fefbfbd58efbfbd7eefbfbdc290efbfbdefbfbd2eefbfbdefbfbdefbfbd7873efbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4e570c5aefbfbd5c0aefbfbd5eefbfbd2a2a5aefbfbdefbfbd7f450b4aefbfbdefbfbd625cefbfbd24efbfbdefbfbd306368efbfbd7922efbfbdefbfbd46c3b4efbfbd071c0b6fefbfbdefbfbdefbfbdefbfbd2defbfbd1c45efbfbdefbfbdefbfbd61efbfbdefbfbdefbfbd72efbfbdefbfbdefbfbdd49befbfbd0b48efbfbdefbfbdefbfbd18efbfbd54efbfbdefbfbdefbfbd5c6e5cefbfbdefbfbd2876efbfbd44760aefbfbdefbfbd3fefbfbd58efbfbd0fefbfbdefbfbdefbfbdefbfbd2fefbfbd391ed0bcc38f716cefbfbd7fefbfbd7befbfbd7613efbfbde4a39b4fefbfbd324e5bcf8169efbfbd0f367fefbfbdefbfbdefbfbdc6bcefbfbdefbfbdcb93efbfbdefbfbd79efbfbd05efbfbd35efbfbd400f03efbfbd01efbfbd02efbfbd01efbfbd007d00287a521f380eefbfbdefbfbd5fefbfbdefbfbdefbfbd7d600fefbfbd1f1b566defbfbd4a0befbfbdefbfbdefbfbdefbfbd73376eefbfbdefbfbdefbfbdefbfbdefbfbd54efbfbdefbfbdefbfbdefbfbdefbfbd19efbfbd133a791defbfbd2d066defbfbdefbfbd6975efbfbdefbfbdcda53f6e4f7c69efbfbdefbfbdefbfbd5befbfbd13efbfbdcc8defbfbddaba25efbfbd5a21efbfbdefbfbd3c29efbfbd066defbfbd68efbfbd41efbfbddf8cefbfbdefbfbd0fefbfbdefbfbdefbfbd3165efbfbd04efbfbdefbfbd03efbfbd12efbfbdefbfbd3defbfbd29efbfbd165eefbfbd3eefbfbdefbfbd6fefbfbdefbfbd23efbfbd4d69efbfbd13efbfbd43efbfbdefbfbdefbfbdefbfbd527d74efbfbd54dbac3cefbfbdefbfbddcb1efbfbd27efbfbd7f3fdf8fefbfbd7559efbfbd31efbfbd2eefbfbdefbfbdefbfbd256b3f2a1aefbfbd64efbfbdefbfbd25efbfbdefbfbdefbfbdefbfbdc986efbfbd58cd86efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0074efbfbd15efbfbd5e53efbfbdefbfbdefbfbd57707b3befbfbdefbfbdefbfbd0163efbfbd2e15efbfbde497a4efbfbdefbfbd44efbfbd73efbfbdefbfbd623a7eefbfbdefbfbdefbfbd4d3fefbfbdefbfbdefbfbdefbfbdefbfbd0462db9ac284efbfbdcfa538efbfbd77efbfbd2aefbfbdefbfbde1879eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd67efbfbd57efbfbd48efbfbd4e5a3f7defbfbdefbfbdefbfbd1527efbfbd6add9fefbfbdefbfbdefbfbdefbfbd35173cefbfbd636427efbfbd43efbfbd73efbfbd7e2cefbfbdefbfbd74efbfbd2966efbfbdefbfbdd1a0efbfbdefbfbdd79e7c77efbfbd2fefbfbd03dd8befbfbdefbfbddf9d76efbfbdefbfbd0f7e57c496efbfbdefbfbd6f45efbfbd6235c2aa337befbfbd1e5e7f38efbfbd1eefbfbd6840470b0f24efbfbdefbfbd2b58efbfbd7c21efbfbd41efbfbd472b366d2870efbfbd60083c7defbfbdefbfbd25efbfbdefbfbd71665704e7aaba16276c02efbfbdefbfbdefbfbd6eefbfbd34efbfbd3145efbfbd2defbfbd18efbfbde78b8253efbfbd732a19efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3335efbfbd1566efbfbd7839efbfbd18efbfbdefbfbd1c18efbfbd2b355aefbfbd424173efbfbdefbfbdd99befbfbdefbfbd3cd2b852efbfbd69691a4631efbfbd64efbfbdefbfbdefbfbd59efbfbdefbfbd38efbfbd0defbfbd0defbfbd7aefbfbdefbfbdefbfbd10efbfbdefbfbd106befbfbdefbfbd704b1fefbfbd6678d687371defbfbd6158732b6d7e371aefbfbdefbfbd34efbfbd13585478c7ab3befbfbdefbfbd11635f29efbfbdefbfbdefbfbd347bd392efbfbd6362efbfbdefbfbdefbfbd1b0cefbfbd52efbfbd25efbfbdefbfbd4defbfbdefbfbd4e48efbfbd3c666f5cefbfbdefbfbd463aefbfbdefbfbd67efbfbdefbfbdefbfbd59efbfbdefbfbd2fefbfbdefbfbd6cefbfbd6235efbfbd5eefbfbdefbfbdefbfbd66efbfbd372e56efbfbdefbfbdefbfbdefbfbdefbfbd58efbfbdefbfbdefbfbd70410c2d242a42df91efbfbd63efbfbdefbfbd30efbfbd1d4befbfbd42efbfbdefbfbd1a2cefbfbdefbfbd43efbfbd06efbfbd01efbfbdefbfbd0228401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbdefbfbd4a1fefbfbdefbfbdefbfbdefbfbd7defbfbd78efbfbdefbfbd64efbfbdefbfbd67efbfbd58efbfbdefbfbd2edeb7efbfbd3e547cefbfbd2c62467fefbfbdefbfbdefbfbd44efbfbd50efbfbd7b775cd5a3efbfbd6b572defbfbd3cd4b864efbfbd5eecac97efbfbdefbfbdefbfbd1cefbfbdefbfbd5defbfbd77efbfbdefbfbdefbfbd1060efbfbd1d3eefbfbd5cefbfbdefbfbd19efbfbd443aefbfbd5a2fefbfbdd6af4c17efbfbdefbfbdefbfbdefbfbdefbfbd614cefbfbdefbfbd181b49efbfbdefbfbd10efbfbd21575befbfbd4a1fefbfbd6be7988466efbfbd0f276fdca4efbfbdefbfbd24efbfbd7a7240efbfbdefbfbd62efbfbdefbfbd285c6cefbfbd1cefbfbd376e35efbfbd7f7cefbfbddf93efbfbd0679efbfbd14efbfbdefbfbd0cefbfbd3667efbfbd31efbfbd76efbfbdefbfbdefbfbd7f6d3a70efbfbdefbfbdefbfbdefbfbd2f08efbfbd2c5b7146efbfbd312aefbfbdefbfbdefbfbd6b5defbfbd78c6bc38efbfbd6f3befbfbdefbfbd48efbfbd59efbfbdefbfbdefbfbd74efbfbdefbfbdefbfbdefbfbd2378efbfbdefbfbddf97efbfbd7befbfbd0c6befbfbdefbfbdefbfbd18560cefbfbd5b0befbfbdefbfbd5e7738efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbd6665243877725f5aefbfbdefbfbd376fefbfbdefbfbd2eefbfbd1befbfbd54260f0fefbfbddebe78efbfbd0a5defbfbd7661efbfbd362befbfbdefbfbd0261efbfbdefbfbd2defbfbd05efbfbd217e363aefbfbdefbfbdefbfbd0855efbfbdefbfbdefbfbd627aefbfbdefbfbd44efbfbd68efbfbd19efbfbd59efbfbd5fefbfbdd89f64efbfbd07473853580d4d0aefbfbd3aefbfbd5e4fd3846d60efbfbdefbfbdefbfbdefbfbdefbfbd1f1a4861efbfbdefbfbd23efbfbd077769efbfbd5eefbfbdc7b43466efbfbd00efbfbdefbfbd501eefbfbdefbfbdefbfbdefbfbd7740efbfbdefbfbd2f12efbfbd05efbfbdefbfbd1aefbfbdefbfbd7d573aefbfbd461fefbfbdefbfbd3eefbfbd6e39efbfbd3aefbfbdeb9cb830efbfbdefbfbdefbfbdefbfbd6aefbfbdefbfbdd7ae302cefbfbd75efbfbd72efbfbdefbfbd7befbfbdefbfbd2b177befbfbdefbfbd3943265951efbfbd0713efbfbdefbfbdefbfbd71efbfbd39efbfbd2773efbfbd29efbfbdefbfbd0170efbfbdefbfbd4a1ded868d5e39efbfbdefbfbdefbfbdefbfbd55efbfbdefbfbd42efbfbd40efbfbdefbfbd2befbfbd01efbfbd1aefbfbdefbfbd075d76787befbfbdefbfbdefbfbd1d3333efbfbd4307efbfbd1defbfbdc29c42efbfbd41417cefbfbddba3efbfbd29efbfbdefbfbdefbfbd4c6d45092b2cccbeefbfbdefbfbd65efbfbd29efbfbdefbfbd0b5561efbfbdefbfbdefbfbd58745f53734b6eefbfbdcea92b57efbfbd35efbfbdefbfbdefbfbd6defbfbdefbfbd73d7a8efbfbd4cefbfbd21efbfbdefbfbdefbfbd77efbfbd56efbfbdefbfbdefbfbdefbfbd11efbfbdefbfbdefbfbdefbfbd0576efbfbdefbfbd5fefbfbd1639dbbb52ccbb64d3abefbfbd6f1defbfbdefbfbdefbfbd5470efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0cefbfbd0c18184c1befbfbdefbfbd0e3755efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6cefbfbdefbfbdefbfbdefbfbd7eefbfbdefbfbd3fefbfbd6aefbfbdefbfbd2aefbfbdefbfbdefbfbd382fefbfbdefbfbdefbfbd3fefbfbd5c1cefbfbdefbfbd42efbfbdefbfbdefbfbd4cefbfbdefbfbd7975efbfbdefbfbd11efbfbdefbfbdefbfbd2befbfbd1e1defbfbd57283a31d59768efbfbdefbfbdefbfbd6067efbfbdefbfbdefbfbd6eefbfbd0576efbfbd7defbfbd106cefbfbdefbfbdefbfbdefbfbdefbfbd72efbfbd263c29efbfbdefbfbdefbfbdefbfbdefbfbd24efbfbd51532255efbfbd3870efbfbdefbfbdefbfbd34efbfbdefbfbdd3aaefbfbd5427efbfbdefbfbd71efbfbdefbfbdefbfbd44efbfbd775656dc907a0eefbfbd7864efbfbdefbfbdefbfbd6eefbfbd12efbfbdefbfbd4e41efbfbd6cefbfbdd88e2728efbfbdefbfbdefbfbdefbfbd0d3d69011b27387cefbfbd5cefbfbd23efbfbdefbfbdefbfbd1f1241efbfbd720b155eefbfbdefbfbdefbfbd18efbfbdefbfbdefbfbd0716d8b12e3eefbfbdefbfbdefbfbd090747efbfbdefbfbd41efbfbd7cefbfbdefbfbdefbfbdefbfbd79efbfbd7defbfbd1836383c73efbfbd21efbfbdefbfbdefbfbd22213befbfbdefbfbdefbfbd7defbfbd05efbfbdc69c49efbfbdefbfbdefbfbd796f27efbfbdefbfbdefbfbd27efbfbdefbfbdefbfbd6121efbfbdefbfbd10efbfbdefbfbdefbfbd61efbfbd265322661fefbfbd07efbfbd103f31efbfbdefbfbdd8b1461f24efbfbdefbfbd7ceab7aa2266efbfbdefbfbd3533efbfbd5f7c536defbfbd72efbfbd09efbfbdefbfbdefbfbdefbfbd62efbfbd3662efbfbd173f2025efbfbd6befbfbdefbfbd72efbfbdefbfbd6d772543141cd0a9efbfbd0e39efbfbd61efbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd32efbfbdefbfbdefbfbdefbfbd69c39aefbfbd36efbfbdefbfbd734befbfbd6f72c89aefbfbdefbfbd764eefbfbdefbfbd4251247145efbfbd1defbfbdefbfbd7924545defbfbdefbfbd00efbfbd36efbfbd4556efbfbdefbfbd3318d4abefbfbd0d3b4eefbfbd63efbfbdefbfbd720d60efbfbd20efbfbd396befbfbd052cefbfbd7519efbfbdefbfbdd2a91aefbfbd6eefbfbd3defbfbd714b1befbfbd4cefbfbdefbfbd77efbfbdefbfbd03d788efbfbd21561803efbfbdefbfbdefbfbd693befbfbd4765efbfbd18efbfbdefbfbdefbfbd6e13efbfbdefbfbd26efbfbdefbfbd7eefbfbdefbfbd226eefbfbd4befbfbd2905efbfbdefbfbdefbfbdefbfbd1247efbfbd494fefbfbdefbfbdefbfbdefbfbd114166274cefbfbd56126cefbfbdd5afefbfbdefbfbdefbfbdefbfbdc7833defbfbdefbfbd57efbfbdefbfbd31efbfbdefbfbd4defbfbd3c274d54efbfbd73efbfbdefbfbdefbfbd293105efbfbd36efbfbdefbfbd272defbfbd4defbfbd2c2cefbfbd6cefbfbd2eefbfbdefbfbd6b557cefbfbdefbfbd5959efbfbd24efbfbdefbfbd3873efbfbdefbfbd43efbfbdefbfbd6fefbfbdefbfbdefbfbd6bcaab493fefbfbdefbfbd5775efbfbd7cefbfbdefbfbd2d30efbfbdefbfbdefbfbd52efbfbdefbfbd3fefbfbd2a50efbfbd14efbfbd7befbfbdefbfbdd3a6efbfbd6befbfbd6aefbfbdefbfbdefbfbdefbfbd3106efbfbdefbfbdefbfbd2a2429473f567d45efbfbdefbfbd74eeafa9efbfbd51efbfbd0e766876efbfbd701c53d384efbfbdefbfbdefbfbd2f29efbfbd7aefbfbdefbfbd09efbfbdefbfbdefbfbd2465722defbfbd40efbfbd1875755a2eefbfbdefbfbd4aefbfbdefbfbdefbfbd7512706e69efbfbd64efbfbdefbfbdefbfbdefbfbdefbfbd6a25efbfbdefbfbd6f2a15efbfbdefbfbd62201fefbfbd56efbfbd1fefbfbdefbfbdefbfbdefbfbd1ec685efbfbdefbfbd41efbfbd6919efbfbd66efbfbdefbfbd427e7defbfbdefbfbd4eefbfbdefbfbd38efbfbd0fefbfbd4a6fefbfbd1defbfbdefbfbd0c5d1016efbfbd31efbfbd5e4defbfbd3fefbfbdefbfbdd69fefbfbdefbfbd3cefbfbd0c7f7d7befbfbdefbfbd3fefbfbdefbfbd181b355c7befbfbd52efbfbdefbfbdefbfbd79efbfbdcd81cfbcefbfbdefbfbd7c2a40efbfbdefbfbdefbfbd3defbfbd50794d517d7466efbfbdefbfbdefbfbd57efbfbdefbfbdefbfbd44efbfbdc5bb753d4067efbfbd3e3445efbfbd7defbfbd4befbfbd6a4b7847d99cefbfbd7befbfbd3e74efbfbdc888d29c53efbfbdefbfbd0c1b5b5a3470445c770c27efbfbd193a71efbfbd481befbfbdefbfbdefbfbd16efbfbdef98b7efbfbd13d6b90f67efbfbd362cefbfbdefbfbd5e6aefbfbd62efbfbdefbfbd237016efbfbdefbfbd53efbfbdefbfbd2363efbfbddfaf733d3cefbfbd62434e5d6befbfbd63efbfbd2e07efbfbdefbfbd20311defbfbd0cefbfbd07efbfbd2f0ef2b4a9ae636b6befbfbdd58666efbfbdefbfbd2a2f68701864efbfbd25efbfbdefbfbd40efbfbdefbfbd6c425befbfbd7738efbfbd06efbfbdefbfbd4a72efbfbd2971efbfbd565a4defbfbd4f641c3f525c45efbfbdefbfbd46c6a15e7befbfbd461fefbfbd67efbfbd34cfa6efbfbdefbfbd593eefbfbd6defbfbd6cefbfbdefbfbd2f007a16efbfbdefbfbdefbfbdefbfbd66efbfbd1fefbfbd6351efbfbdefbfbdefbfbd61efbfbd6879efbfbd343cefbfbdefbfbd1e623befbfbdefbfbdefbfbdefbfbd5e7a766befbfbdefbfbdefbfbd646718efbfbdefbfbd6d30efbfbd4963efbfbd6befbfbdefbfbd2befbfbdefbfbdefbfbdefbfbd60efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdda86efbfbdc6b0efbfbd5f7ec48f654aefbfbd392211efbfbdefbfbdc7ab7a0b0807efbfbd27397e464aefbfbd4509efbfbd32547befbfbd04664d34efbfbd3e0befbfbd75efbfbd5363df89efbfbdefbfbdefbfbd55d7a90defbfbdefbfbdefbfbd6a7c3b1cefbfbd7fefbfbdefbfbd4befbfbdefbfbd41efbfbd23050e3c2153efbfbd20efbfbd0cefbfbdefbfbdc69aefbfbdefbfbd16efbfbd3befbfbdefbfbdefbfbd11efbfbde6b69957626f7cefbfbdefbfbddaa8efbfbd7a72d78303efbfbd411cd79a2defbfbd38efbfbd5976efbfbdefbfbd1aefbfbdc7804defbfbd36efbfbdefbfbd275b6504efbfbd2d5870efbfbd7befbfbdefbfbdefbfbd75efbfbd0f7d5fefbfbdefbfbdefbfbd600eefbfbd1befbfbd3cefbfbdefbfbdefbfbd5cefbfbdefbfbdc98fccbc730207efbfbd3060efbfbd3e70efbfbdefbfbd2828efbfbdefbfbd1b68efbfbd39efbfbddba062370a1c1bcba54a6d775510efbfbd51efbfbd7befbfbd50efbfbdefbfbd290b4defbfbdefbfbdefbfbd4defbfbd4848efbfbdefbfbdefbfbd502fefbfbd7760efbfbd3eefbfbdefbfbd5c7dc3a53d17efbfbdefbfbd0e74efbfbd607a1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5639305977efbfbd42efbfbd10efbfbd47efbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd5612367a1aefbfbdefbfbdefbfbdefbfbd0656efbfbdefbfbd3c36efbfbd38efbfbdcf9e4942efbfbdefbfbd37efbfbdefbfbd51efbfbdefbfbd76efbfbd1fefbfbd1118557befbfbd68efbfbd4aefbfbdefbfbd25efbfbd79efbfbdefbfbd02efbfbdefbfbdefbfbd575aefbfbd6a59c981efbfbdefbfbd3266deb7efbfbdefbfbd40efbfbdefbfbd2b2d38efbfbd67efbfbdefbfbd73efbfbd29efbfbdefbfbd69efbfbd4a0b65efbfbd3b64efbfbd29efbfbdefbfbdefbfbdefbfbd4defbfbd2cefbfbd3e4defbfbd427c53efbfbd09efbfbdefbfbdefbfbd5a007d3cefbfbd74efbfbd6e512b77efbfbd6aefbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd0340711fefbfbdefbfbd3c3410efbfbdefbfbd2b6aefbfbdefbfbd2edcb55f1515efbfbd266e191730efbfbdefbfbd4a37efbfbd7578efbfbdefbfbd53efbfbd61efbfbdefbfbdefbfbdefbfbd501f74c99e1fefbfbd1cefbfbd42efbfbd63efbfbdefbfbdefbfbd52efbfbd40636a69352eefbfbdefbfbd725defbfbd4e681a1aefbfbdd185041b2befbfbdefbfbdefbfbdefbfbd42efbfbdefbfbd2038efbfbdefbfbdefbfbdefbfbd737e4f5defbfbdefbfbd35efbfbd0b10d789efbfbdefbfbd3c5c2eefbfbd310cefbfbdefbfbdefbfbd58767fefbfbdefbfbd0671efbfbd4e527f217f674d72efbfbdefbfbdefbfbd4d25691318efbfbd665d514d43033a21efbfbdefbfbd31efbfbdefbfbd522befbfbd4b753eefbfbdefbfbdefbfbd2815efbfbdefbfbd322e505c2fefbfbd35efbfbdefbfbd1aefbfbdefbfbd525e2fefbfbd327defbfbd76efbfbd5131efbfbd51cd87efbfbd3deea5a8efbfbd01efbfbdefbfbdefbfbd13efbfbdefbfbd39efbfbd360d0a2406efbfbdefbfbdefbfbd31efbfbd0b60efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd05efbfbdefbfbd430fefbfbdefbfbd7fcbb800707fefbfbdefbfbd0befbfbdefbfbdefbfbdefbfbd2fefbfbd3eefbfbd42efbfbdefbfbdefbfbdefbfbd40efbfbd63efbfbd3eefbfbdefbfbdefbfbd7e3c27efbfbd1fed8f9d38efbfbd1a203eefbfbdefbfbd316aefbfbdefbfbdefbfbd57efbfbdefbfbd17efbfbd13efbfbdefbfbd5fefbfbdefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd3eefbfbd3eefbfbdefbfbdd595efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd52efbfbdefbfbd19efbfbd5472e4b79b644654efbfbd263cefbfbd475021efbfbdefbfbdefbfbdefbfbdefbfbd17efbfbd21cca3efbfbd48195eefbfbd48efbfbdefbfbd5c5d19efbfbd792cefbfbd07efbfbd67efbfbd48efbfbd76ccbc7ec68defbfbdefbfbdefbfbdc387655d516defbfbd2f3aefbfbd4befbfbd2b6f65efbfbdefbfbdefbfbd5defbfbd3aefbfbd09efbfbd297a43efbfbd057a2befbfbdefbfbd1eefbfbd4f45310e72efbfbd4e4cefbfbd3605d4ab722cefbfbd6b0fefbfbd7f2cefbfbd4763efbfbdefbfbd333a3f371b272f71efbfbdefbfbd3255efbfbd37efbfbd220befbfbdefbfbdefbfbdefbfbdefbfbd6547efbfbd5defbfbd38efbfbdd69e0a7d3cefbfbdefbfbd48efbfbdefbfbd277002efbfbd7147efbfbdefbfbd327defbfbdefbfbdefbfbdefbfbd5aefbfbd4dd1a97375efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7befbfbdefbfbd3fe999b4caad07efbfbdefbfbd69220befbfbd6ae79fb205efbfbdefbfbd4aefbfbd73654b0befbfbd0175252e57efbfbd6aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd327d100eefbfbd53efbfbdefbfbdefbfbdefbfbd18efbfbdefbfbdc4b9434cefbfbd0133265b51545befbfbd14774eefbfbd3ec6b0efbfbd7aefbfbdefbfbd1378efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd012fefbfbd3702efbfbd750005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f0005efbfbd034001efbfbd0050efbfbd3e0014efbfbd0f00efbfbd65efbfbdefbfbdefbfbd1c5befbfbdefbfbd556edca429efbfbd02dc94efbfbdefbfbdefbfbd3a420e4dc68eefbfbdefbfbd49efbfbd1e14efbfbdefbfbd55efbfbd771cefbfbd450d77efbfbdefbfbd5676efbfbd2355efbfbd68efbfbd31082fefbfbd79efbfbd6cefbfbd4d6a74efbfbd1ec3a9efbfbd29efbfbd54efbfbdd6a5efbfbd54efbfbd27efbfbd4f72631aefbfbd345eefbfbd74efbfbd6e5cefbfbdefbfbd25efbfbdefbfbdefbfbdefbfbd3de39795efbfbd4cefbfbd0eefbfbdefbfbdefbfbd5b6b1326efbfbd725aefbfbdefbfbdefbfbd3defbfbdefbfbd36cd8061efbfbdefbfbd02efbfbdefbfbd4aefbfbdefbfbdefbfbd0a64efbfbd3b5eefbfbd58efbfbd4e567051efbfbd1921efbfbd2cefbfbdefbfbd1a2fefbfbd5c57efbfbd6cefbfbd622aefbfbd2f60efbfbd3e08efbfbdefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbd37efbfbd5f76efbfbd6fefbfbd33137d6e1fefbfbd683b7e28efbfbdefbfbd3aefbfbd657943efbfbdefbfbd5a726c77efbfbdefbfbd573d7fefbfbdefbfbd24d0a124efbfbd0a7eefbfbdefbfbdefbfbd6fefbfbd4e4a602b6936efbfbdefbfbdefbfbd73efbfbd331d7edc90efbfbd28efbfbdefbfbd61245b48efbfbd5fefbfbdefbfbd6b194f7eefbfbd7aefbfbd5675efbfbdefbfbd55efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd61efbfbdefbfbd10efbfbdefbfbd21efbfbdc78eefbfbd23efbfbd03efbfbdefbfbd6fe7a587efbfbdefbfbdefbfbd3628efbfbd555922efbfbddb8aefbfbd4aefbfbd0f6f7e6d3b21efbfbd6857efbfbdefbfbd796104efbfbd33efbfbd4d5fefbfbdefbfbd6c7f636100efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbd24efbfbd7adca26949062defbfbdefbfbdefbfbd62efbfbd5befbfbd3441ca8e1fefbfbdcc9cefbfbddc9f7b323b77efbfbd0653efbfbdefbfbdefbfbdefbfbdefbfbd48efbfbdefbfbd125defbfbd4e7966efbfbd07766befbfbdefbfbdefbfbd4862c6baefbfbd3b7815e7b9a64eefbfbd39efbfbdefbfbd1646efbfbdefbfbd5d70efbfbdefbfbddcbeefbfbd65efbfbdefbfbd6b25efbfbd10efbfbd57efbfbdefbfbd5defbfbdefbfbdefbfbd752a3d46efbfbdefbfbdefbfbd0cefbfbdefbfbd71efbfbd62efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3d5befbfbdefbfbd11efbfbd050cefbfbdefbfbd4a6e6b031a1befbfbd6c1cc3b91e6e65efbfbdefbfbdefbfbdefbfbdefbfbd0d62601a41506a42efbfbd66efbfbd46efbfbd5e4935caabefbfbd293649efbfbd18efbfbdefbfbd69efbfbdefbfbd3f3c28efbfbd07efbfbdefbfbd4aefbfbd5c5176efbfbd504befbfbdefbfbdefbfbdefbfbdefbfbd3cefbfbd5e7c636f4defbfbd235ed585efbfbd72efbfbd14efbfbdefbfbd1127efbfbd2a3defbfbd2cefbfbdefbfbd603cefbfbdefbfbd643f723459efbfbd69efbfbd0539efbfbdefbfbd353eefbfbd41efbfbdefbfbdefbfbd3eefbfbd7268efbfbd1a6aefbfbdefbfbdefbfbd301764efbfbdefbfbdefbfbdefbfbd57efbfbdc9b11eefbfbd5315efbfbd38017653efbfbd52efbfbd16efbfbd38efbfbd70efbfbd3e2cefbfbdefbfbdd0a46874efbfbd43efbfbdefbfbd70efbfbd4d6a5415efbfbd0cefbfbd713358736037efbfbddb9defbfbdefbfbdefbfbd60efbfbd3e182eefbfbd4f3d6defbfbd7defbfbd292961efbfbd6933e985a7305ddd9fefbfbdefbfbd5163efbfbddd9b7eefbfbdefbfbd4c0c5befbfbdefbfbdefbfbdefbfbd4befbfbd35efbfbd4d74317c66efbfbdefbfbd3cefbfbd5defbfbd5671efbfbd4aefbfbd68efbfbd2c57c38defbfbd7e7fefbfbd39efbfbdefbfbdefbfbd5ed98409c3bcefbfbdefbfbd53efbfbdefbfbd31efbfbd6f18efbfbd1e1fefbfbd4eefbfbd26efbfbd1f694a4ac2b4257bce89071b4befbfbd46efbfbd5b1261ce991eddb6d4a9631734efbfbd2aefbfbd4aefbfbdefbfbdefbfbdefbfbdefbfbd11c69438efbfbdefbfbd443f0cefbfbd3eefbfbdefbfbdefbfbd30e3a7a6222fefbfbdefbfbd4c183a72efbfbdefbfbd761a48301937efbfbdefbfbdefbfbd2aefbfbd6fefbfbd03efbfbd7befbfbd53efbfbd366662efbfbd3d5dc98defbfbdefbfbd71671aefbfbd2b63efbfbd05efbfbd49efbfbd6159efbfbd7fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd063b7579d6ba721f782cefbfbd07efbfbdefbfbdefbfbd5a0fefbfbd67efbfbdefbfbdefbfbdefbfbdefbfbd5befbfbd7e7c3175efbfbd65efbfbdefbfbd173e7c3c10efbfbd5fefbfbdefbfbdefbfbd7f5aefbfbdefbfbd787766efbfbd7cefbfbd64efbfbd474563efbfbd192539501d34d48f7defbfbd15efbfbd08efbfbd6a6b77644defbfbdefbfbdefbfbdefbfbdefbfbd3f1defbfbd4c79efbfbd646157efbfbd5932efbfbdefbfbd532d68efbfbd7f39efbfbdefbfbd390b1e1b24223befbfbdefbfbd5fefbfbd49efbfbdefbfbd7f2609efbfbd57efbfbdefbfbdefbfbdefbfbd696fefbfbdefbfbd4c3defbfbd20efbfbdefbfbdefbfbdefbfbd073b70efbfbd6cefbfbdd5af4fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbddaaa33efbfbd771cefbfbdefbfbdefbfbd1fefbfbd4cefbfbdefbfbd6befbfbd5fefbfbdccb11b10123172efbfbd1cefbfbdc6975f59efbfbd45efbfbd06efbfbd6fefbfbd67efbfbd28efbfbd2fefbfbdefbfbdefbfbdefbfbd51efbfbdefbfbd3b25efbfbdefbfbd777cefbfbdefbfbd5c3c24efbfbdefbfbddcbc5349db87172c7befbfbdefbfbd776fefbfbd7aefbfbdefbfbd34efbfbdefbfbdefbfbd45efbfbd16efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1aefbfbdefbfbd6913efbfbd1e5d79efbfbdefbfbdefbfbdefbfbdefbfbd45efbfbd3f7d56efbfbdefbfbdcba96aefbfbdefbfbd1d552248efbfbdd5b07defbfbd236747efbfbd3defbfbde48c94efbfbd6c26efbfbd0874cb98efbfbd77efbfbdefbfbd1b1e030309ddb259efbfbd554c5aefbfbdefbfbdefbfbdee899f25efbfbdefbfbd31efbfbdefbfbdefbfbd11efbfbd3cefbfbdefbfbdefbfbd514e0f1356efbfbd43796defbfbdefbfbd3a1f6fc7aaefbfbd5fefbfbdefbfbd1befbfbdefbfbdefbfbd3328efbfbdefbfbdefbfbdefbfbdefbfbd79efbfbdefbfbd7d7520efbfbd191656efbfbdefbfbdefbfbd6e63efbfbd2805efbfbdc789efbfbdefbfbdefbfbdefbfbdefbfbd01efbfbddd97585cd5895aefbfbd69614c4c2b1aefbfbd5074efbfbd1533efbfbdefbfbd6b2817efbfbdefbfbdefbfbd292f1cefbfbd1a3424efbfbdefbfbd52efbfbd7839efbfbdefbfbdefbfbdefbfbd073f7aefbfbdefbfbd0eefbfbdefbfbdefbfbdefbfbd6a69690d4defbfbdefbfbdefbfbd31efbfbdefbfbdefbfbdefbfbd305cefbfbd48efbfbdefbfbd6a4317efbfbdefbfbd087eefbfbd77617b0e0cefbfbdefbfbd1aefbfbd5aefbfbd5071efbfbd486344efbfbd17efbfbdefbfbdefbfbd630f62efbfbdefbfbd5befbfbd7e65efbfbdefbfbdefbfbd44efbfbd26efbfbdefbfbd7cefbfbd2defbfbdefbfbdefbfbdefbfbdefbfbd43efbfbd18efbfbdefbfbdefbfbd1f595fefbfbd53efbfbdefbfbd4defbfbd44efbfbdefbfbdefbfbdefbfbd6defbfbdefbfbd68efbfbdefbfbd7e67efbfbd1cefbfbdefbfbd61735f5defbfbd72efbfbdefbfbd5319efbfbdefbfbdd88e56efbfbdefbfbd05eb97b5efbfbd79efbfbd7973efbfbdefbfbddaaaefbfbdc79f5cefbfbd28301937efbfbd4c6eefbfbd66efbfbdefbfbdefbfbd0d7a3556efbfbdefbfbdd2a6efbfbd76efbfbd3e79390a6f31efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd374640efbfbdefbfbd63efbfbd79efbfbdefbfbd2aefbfbd7330031f5b14efbfbdd395efbfbd49efbfbdefbfbd07efbfbd551e5befbfbdefbfbdefbfbd1b5eefbfbd7b7cefbfbdefbfbdefbfbdefbfbd306f63efbfbdefbfbd656362efbfbd4cefbfbdefbfbdefbfbd61efbfbd3eefbfbd2148efbfbd17efbfbd14efbfbd08190263efbfbdefbfbd0f37efbfbd1cefbfbd45efbfbdefbfbd5a0d26efbfbd3321efbfbd3eefbfbd15efbfbd102befbfbdefbfbd39cf8f337aefbfbd035aefbfbd4d7176efbfbdefbfbd55efbfbd20efbfbdefbfbd142befbfbd1139631836efbfbdefbfbd737defbfbd57efbfbd58efbfbdefbfbd4fefbfbd3e322d6959efbfbd2befbfbdefbfbd6fefbfbd73efbfbdefbfbd79efbfbdefbfbd3eefbfbdefbfbdefbfbddb8337efbfbd385955efbfbdefbfbdefbfbdefbfbdefbfbd03efbfbdefa6a77defbfbdefbfbdefbfbd27efbfbd5e74efbfbdefbfbdc592efbfbd6a087fefbfbdefbfbd51efbfbdefbfbd5777efbfbdefbfbd763f56efbfbd235aefbfbdeebd9fefbfbd41efbfbd31224631efbfbdefbfbd3053efbfbdefbfbd1c5730efbfbd29efbfbdefbfbd34efbfbd68efbfbd3aefbfbdefbfbdefbfbd4fefbfbd79efbfbdefbfbdefbfbdefbfbd3830efbfbd47efbfbd421fefbfbd6f7cefbfbddabcefbfbdefbfbdc59eefbfbd20efbfbd31efbfbd60efbfbd19efbfbd1befbfbdefbfbd2961efbfbd0f2b4ce190b8efbfbd59efbfbdefbfbd143305efbfbd1befbfbd13efbfbd245d1eefbfbd7048efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7301efbfbd4cefbfbdefbfbd6e72d7af3eefbfbd102666efbfbdefbfbd63efbfbdefbfbd32780fefbfbd07efbfbd1b7d5defbfbd7fefbfbdd58f1a23d8be66673e23efbfbdefbfbdefbfbd2303efbfbdefbfbd75efbfbdefbfbdefbfbdefbfbdefbfbd7962efbfbd65efbfbdd08aefbfbd3cefbfbd5415efbfbd48efbfbdefbfbd1dd783efbfbdefbfbd5d5aefbfbdefbfbd04efbfbd38efbfbdd9b9efbfbd44440f55d9aaefbfbdefbfbdefbfbdefbfbdefbfbd432441ca8eefbfbdefbfbdefbfbd46103d61efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd163befbfbdefbfbd157933efbfbd3815efbfbdefbfbd06efbfbd6eefbfbd244fefbfbdefbfbdefbfbd5befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdde9015efbfbd3e7950302aefbfbd72efbfbdefbfbd2befbfbdefbfbdefbfbd2c49296974efbfbdefbfbdefbfbd15efbfbd7239efbfbdefbfbdefbfbd4971165defbfbdefbfbdefbfbd0a5aefbfbdefbfbd5d07443b50efbfbdefbfbd09efbfbdefbfbd2befbfbd62efbfbd18dfbdefbfbd48082eefbfbdefbfbd2874efbfbd545fefbfbd2a644c2f17efbfbdefbfbd54efbfbd35efbfbd31efbfbdefbfbdefbfbdefbfbd304defbfbdefbfbdefbfbd170812efbfbdefbfbdefbfbd296b7cefbfbdefbfbd375f1737efbfbdefbfbdefbfbd78efbfbd3768efbfbd5aefbfbd63efbfbd57efbfbd32efbfbdefbfbd0defbfbd7627583cefbfbdefbfbd42efbfbdefbfbdefbfbd576859762e3c7defbfbd28efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbd607befbfbd5eefbfbd50795b72efbfbd5aefbfbdefbfbd631aefbfbdefbfbd5fefbfbdefbfbdcc8e387defbfbd4a71efbfbdefbfbdefbfbdefbfbdefbfbd69efbfbdefbfbd5471efbfbdefbfbd7636176b4a4a64efbfbd30efbfbd03efbfbd7eefbfbd15efbfbdefbfbdefbfbd2fefbfbd35efbfbdefbfbdefbfbde7b7a67b0647654e7cefbfbd5defbfbdefbfbd1834efbfbdefbfbdefbfbd6c4cefbfbdefbfbd01cdb3cf86efbfbd366fefbfbd7a4defbfbd140d7864efbfbd00efbfbd5e74efbfbdefbfbdefbfbd64efbfbd74efbfbdefbfbdefbfbd6defbfbdefbfbd74efbfbd5c735aefbfbd10efbfbd67efbfbdefbfbdefbfbd58efbfbd62efbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbd26efbfbd3defbfbd58efbfbdefbfbdefbfbd19efbfbdea9db57a68efbfbd541f5defbfbd4f041e6e7aefbfbd5f0fefbfbd5a401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f000aefbfbd07efbfbd02efbfbd01efbfbd007d0028401f00efbfbdefbfbd07efbfbdefbfbd48efbfbd567c7eefbfbd0000000049454e44efbfbd4260efbfbd504b0304140008080800efbfbd60efbfbd420000000000000000000000000b000000636f6e74656e742e786d6cefbfbd1defbfbd72dbb8efbfbdefbfbd5fefbfbd72efbfbd7defbfbd2cefbfbdefbfbdefbfbdefbfbd76efbfbdd8bbdd9924efbfbdefbfbd286d673aefbfbd0c4c4232efbfbdefbfbd15efbfbd2cefbfbdefbfbd7defbfbdefbfbdefbfbd57efbfbd4b7a005eccbb48efbfbd12efbfbd4defbfbdefbfbd48efbfbd39efbfbdefbfbd5f40efbfbd7eefbfbddd83efbfbd0cefbfbd31efbfbdefbfbdefbfbd2d255dd1a401efbfbd4cdfb2efbfbdefbfbd52efbfbdefbfbdefbfbd5eefbfbd49dfad7eefbfbdefbfbdefbfbd6c6c132f2cefbfbddcb9efbfbd63efbfbdefbfbd7b0cefbfbd1f00efbfbd4717efbfbdefbfbd52efbfbd116fefbfbd236ad385efbfbd5c4c17efbfbd5cefbfbd01efbfbd62efbfbd451a7a21efbfbd0a47287b741aefbfbd0befbfbd3436efbfbd0fefbfbd2932efbfbdefbfbdefbfbdefbfbdefbfbd3b0befbfbd34efbfbd45d0be2932efbfbd05efbfbdefbfbdefbfbd377e53efbfbd07efbfbdefbfbd1b1fefbfbdefbfbd06efbfbdefbfbd392a1e1cefbfbdefbfbd7929efbfbd31162c5475efbfbdefbfbd2befbfbdefbfbde293adefbfbdefbfbdefbfbd7355efbfbd2604efbfbd095cefbfbd23efbfbdefbfbdefbfbd4c153befbfbd6f46555defbfbdefbfbd18efbfbdefbfbd0c35efbfbdefbfbdc3a649efbfbd76efbfbd2d26efbfbd45efbfbd182a68efbfbdefbfbd6f1b5befbfbdefbfbdefbfbd4234efbfbd1d22efbfbd6d430067efbfbd3befbfbdefbfbdefbfbd7768efbfbd715defbfbdefbfbd2a743253efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2d10efbfbdefbfbd5e1c36232aefbfbdefbfbd41633643efbfbd34efbfbdefbfbdefbfbd09efbfbd1c21745041efbfbdefbfbd692335efbfbdefbfbdefbfbdefbfbdd782efbfbdefbfbd3049efbfbdefbfbdefbfbdefbfbd2672efbfbd44efbfbd5b2634efbfbdefbfbd55efbfbdefbfbdefbfbd3d37efbfbdefbfbdefbfbd206805efbfbdefbfbdefbfbdefbfbd0930efbfbd2aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd2760efbfbd30efbfbd6c7befbfbd21efbfbd4932efbfbd2befbfbdefbfbdd3b14a70efbfbd13efbfbd0866efbfbd3c60efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd3aefbfbdefbfbdefbfbd6763efbfbd2defbfbdefbfbd52502067efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd36efbfbd7f23652279efbfbd41efbfbd730621efbfbdefbfbd211401efbfbdefbfbdefbfbdefbfbd08efbfbdefbfbd72efbfbdc48defbfbd44efbfbdefbfbd3cefbfbd267968efbfbdefbfbd3c600272572440efbfbd106062efbfbd29efbfbd08efbfbd4566efbfbd4cefbfbdefbfbdefbfbd13efbfbd77efbfbd7fefbfbd3240efbfbdefbfbd52efbfbd3658efbfbd1f2c52efbfbdefbfbd604edc8766efbfbd714befbfbdefbfbd4d7ec59c57efbfbdefbfbd0e59efbfbdefbfbdefbfbd3fefbfbd7c4eefbfbd0cefbfbd75efbfbd532aefbfbd1befbfbd2a4ed8a1efbfbd533519efbfbd40efbfbd37efbfbdc4b2efbfbd4defbfbdefbfbd5eefbfbdefbfbd37191eefbfbdefbfbd39efbfbd4befbfbd6fefbfbdefbfbd78254007607d10656330efbfbd761eefbfbdefbfbd1f51efbfbdefbfbd3fefbfbd60efbfbd0169efbfbd59efbfbdefbfbdefbfbd5befbfbd00200e10efbfbd455e0622efbfbdefbfbd09efbfbdefbfbd1e113b34efbfbdefbfbd1cefbfbdefbfbd14efbfbd101e64efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd13efbfbd4677efbfbdefbfbd5e426c387308efbfbdefbfbdceb42d3478efbfbd3c3aefbfbdefbfbdefbfbd5007efbfbd4aefbfbd4b604b04efbfbd67efbfbd1103efbfbdefbfbdefbfbdefbfbd3418201defbfbdefbfbdefbfbdefbfbd4fefbfbd12efbfbdefbfbdefbfbdefbfbd75efbfbdefbfbd03efbfbd5d01efbfbd5342503c5eefbfbd35efbfbddb94efbfbdefbfbdefbfbdefbfbd2d22efbfbdefbfbd57efbfbd240defbfbd401eefbfbdefbfbd52efbfbd1c321a473b066265efbfbd29efbfbd75124f153f733673efbfbdefbfbd61efbfbd5f44791870632cefbfbd4d0e20efbfbd60efbfbd6cefbfbd6f38efbfbdefbfbd2d5eefbfbd4c1563664c6cefbfbd6158efbfbd22efbfbdefbfbd42247111efbfbd42efbfbdefbfbd57efbfbd791d1c5d76efbfbdd297efbfbd4364762b1849efbfbd75efbfbd48e5aa944cefbfbdde9cefbfbdefbfbdefbfbd0d07efbfbd4447efbfbd11efbfbd3219efbfbdefbfbd76627f043b7216623cefbfbdefbfbd3fefbfbdefbfbdefbfbdefbfbd39281defbfbd67efbfbdefbfbdce8eefbfbdefbfbd55e7948e14efbfbdefbfbdefbfbd75efbfbd0e27efbfbd6cd69eefbfbdefbfbd330875361fefbfbdefbfbd48efbfbd69efbfbd2328efbfbdefbfbd5248efbfbdefbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd3defbfbd577a05efbfbdefbfbd71efbfbdefbfbd50efbfbdcc8defbfbd08efbfbd15efbfbdefbfbdefbfbdefbfbd0d6706270f466f7d6261223b7803efbfbdefbfbdefbfbd3defbfbd1e25efbfbdefbfbdefbfbd64efbfbdefbfbd4171efbfbdefbfbd67efbfbdefbfbdefbfbdefbfbd320ed880efbfbdefbfbd6d0defbfbdefbfbdc4bfefbfbdefbfbdefbfbdc7b80e18efbfbdefbfbd6e4cd0bbefbfbdefbfbd0344d096efbfbdefbfbd2eefbfbdefbfbd017e4e22efbfbdefbfbd21efbfbd5f30efbfbdefbfbd124a13efbfbd14efbfbd292e00efbfbd0f0a03efbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7c384defbfbd3f7b0c602befbfbdefbfbd143fefbfbdefbfbdd4baefbfbd1befbfbd6f74efbfbd3eefbfbd5eefbfbd2e23efbfbd61efbfbd280025efbfbd416aefbfbd0706753d60efbfbdefbfbdca8765e8a0905732efbfbd171227030e7e28efbfbd260472322fd09befbfbd40efbfbd1a7befbfbd015defbfbd18636eefbfbdefbfbd157eefbfbd2eefbfbd3a5defbfbd27efbfbd74efbfbd5cd8a363efbfbdefbfbd1eefbfbd730b11efbfbd13efbfbdefbfbdefbfbdefbfbd380c7fd08d58efbfbd2d31c48aefbfbd0befbfbd6735efbfbd7127efbfbd2befbfbd1f2e1421d3a860efbfbd5eefbfbdd6bf76efbfbdd99b47efbfbdefbfbdefbfbdc2a27befbfbdefbfbd4b69efbfbd1cefbfbdefbfbdc8bf4c754c7a37efbfbd67113eefbfbdefbfbdefbfbd01efbfbd1cefbfbd6d77680b435658efbfbdefbfbdefbfbdefbfbd6304efbfbdefbfbdefbfbd792672efbfbdefbfbd28efbfbd59efbfbdefbfbdefbfbdefbfbdefbfbd0961efbfbd4a06efbfbd3e6defbfbd594eefbfbdefbfbd16efbfbdefbfbd5715efbfbd32efbfbdefbfbdefbfbdefbfbd7455efbfbd1f55efbfbd776a58efbfbdefbfbd58efbfbdefbfbdefbfbd3cd682efbfbdefbfbdefbfbdefbfbddfa2efbfbd75efbfbdefbfbd0e4defbfbdefbfbd36efbfbd71efbfbdefbfbdefbfbd1eefbfbdefbfbd22efbfbdefbfbdefbfbdefbfbdefbfbd53efbfbdefbfbdefbfbd29efbfbd757e524eefbfbdefbfbdefbfbd297a372defbfbd05efbfbd62213cefbfbd274b293e39efbfbd422a65efbfbdefbfbdefbfbd4b0befbfbdefbfbd070d7eefbfbd5513200aefbfbd2defbfbd142eefbfbd4250efbfbdefbfbd14efbfbd47efbfbdec9ea77738efbfbd553aefbfbdcebbefbfbdefbfbdefbfbd797befbfbdefbfbd45efbfbd1a3d00efbfbd2befbfbd50efbfbd1befbfbd4eefbfbd61efbfbdefbfbd41efbfbdefbfbd7c383d167befbfbd18efbfbd30efbfbd4eefbfbd4cefbfbdefbfbd2d75efbfbd2b5d26efbfbdefbfbd2322efbfbd7c5defbfbd71223aefbfbd1d27efbfbd71efbfbd01efbfbd75efbfbd78efbfbd7f5e4aefbfbd7eefbfbd066d1cefbfbdefbfbd0504efbfbdefbfbdefbfbd3e1a75013f5f58efbfbdd78b1a473f54efbfbdefbfbd1c75efbfbdc7b546511c12efbfbd5cefbfbd37efbfbd0defbfbd61efbfbd34efbfbd2f24efbfbd26efbfbd0defbfbd39efbfbdefbfbdefbfbd33efbfbd0576efbfbdefbfbdefbfbdc8a86230efbfbdefbfbd3353647b3e71efbfbd6aefbfbdefbfbd46275b65efbfbd20efbfbdefbfbd1332efbfbdefbfbd62efbfbdefbfbd48efbfbd67c99aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6b34133d4fefbfbd4c0959efbfbd206befbfbdefbfbdefbfbd1811efbfbd6fefbfbdc899efbfbd67efbfbd65efbfbdefbfbd2fefbfbdefbfbdefbfbd35efbfbdefbfbd57efbfbd057b7aefbfbdefbfbddc94efbfbd6e1aefbfbd66efbfbdefbfbd5cefbfbd2a39efbfbd4eefbfbdefbfbd2e08efbfbdefbfbd11efbfbdd5ba36efbfbd5befbfbd2701efbfbd612aefbfbd7defbfbd136cefbfbd0e6d0defbfbdefbfbdefbfbd70efbfbd5befbfbd31efbfbdefbfbd28efbfbd440ed0b6efbfbd27efbfbd531c2defbfbdefbfbdcf9defbfbd0d0f47efbfbd5defbfbd33efbfbdefbfbd62efbfbdefbfbd54efbfbd4b2a3cefbfbd0f3fefbfbd447a541fefbfbdefbfbdefbfbdefbfbd6aefbfbd3defbfbdefbfbd516706efbfbd02efbfbd25efbfbd625defbfbd677aefbfbdefbfbdefbfbd01efbfbdefbfbd7426e6bfa3efbfbd007defbfbd0cefbfbd1e7c4defbfbdefbfbd4a763a63efbfbdefbfbdefbfbd4f3aefbfbdefbfbd510747efbfbd5cefbfbd53cfa8efbfbdefbfbd29efbfbdefbfbd48efbfbdefbfbdefbfbd5a6d453015efbfbdeab4835a17efbfbd3503efbfbdefbfbd3e741fefbfbd7f24363aefbfbd23efbfbd512befbfbd140eefbfbdefbfbd2cefbfbd6aefbfbdefbfbd1cefbfbd55efbfbd307eefbfbdefbfbd2aefbfbdefbfbd5b16efbfbd562c164e193a64efbfbdefbfbdefbfbdefbfbd64efbfbd703cd185373cefbfbd2befbfbdefbfbdc2994167efbfbd7d16571fd785efbfbdefbfbdefbfbdefbfbd3eefbfbd5d5432efbfbd46efbfbdefbfbd59efbfbd49efbfbd151aefbfbdefbfbdefbfbdefbfbdefbfbd5eefbfbd59176fefbfbd7eefbfbd522b593d0168774d725defbfbd24da81583befbfbd2677424fefbfbd2e5e02efbfbd1877efbfbd636cefbfbd7c775b1befbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd65efbfbdefbfbd60efbfbdefbfbd73efbfbd75efbfbd5260670cefbfbd4fefbfbd1defbfbd76efbfbdefbfbd4f2b1825efbfbdefbfbd46efbfbdefbfbdefbfbdefbfbd383c4b49d78cefbfbddeb5dbae68efbfbd24efbfbdc58eefbfbdcaa8efbfbd23efbfbd16efbfbd1327efbfbd20efbfbd17ce94efbfbdefbfbdefbfbd6546efbfbdefbfbd6139efbfbd51efbfbd7849efbfbdefbfbdefbfbd43efbfbdefbfbd62efbfbd76efbfbdefbfbdefbfbdefbfbd2432efbfbdefbfbd4befbfbd66c8b1cdacefbfbdefbfbdefbfbd58efbfbdefbfbd7c0b7adba5efbfbd63efbfbd6defbfbdefbfbd71efbfbd2eefbfbd5626efbfbdc28e6aefbfbd0b60efbfbd097e1b20efbfbdefbfbdefbfbd49efbfbddc89efbfbd5c68efbfbd4d616f7c7347efbfbd2aefbfbd641038efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd7b9245743efbfbd1b1229efbfbdefbfbd0e7b4fefbfbd290e0ec49065efbfbdefbfbd41efbfbdefbfbdefbfbd63efbfbd41efbfbdefbfbdefbfbd03efbfbd49efbfbd7428efbfbd1f1d67471911efbfbdefbfbd2adfa1efbfbd62efbfbdefbfbdefbfbd39efbfbdefbfbdefbfbd5575efbfbd22efbfbdefbfbd1b1defbfbd06efbfbdefbfbd16efbfbd28efbfbdefbfbd3defbfbd06e19b8b77ed889fefbfbd7b771216efbfbdefbfbd7cc2b1346234efbfbd41efbfbdefbfbd215209efbfbd5cefbfbdefbfbd3aefbfbdefbfbd2dc2abefbfbd08efbfbd6d116eefbfbd08efbfbdefbfbdefbfbd56efbfbdefbfbd532303efbfbdefbfbd3d69efbfbdefbfbd61397cefbfbd0656efbfbd1558efbfbdefbfbdefbfbdefbfbdefbfbd7279c489741c7065efbfbdefbfbdefbfbd0c25463c4b13efbfbd0defbfbd57efbfbdefbfbdefbfbdefbfbd67efbfbd1473efbfbd55efbfbdefbfbd034defbfbd387c1b1defbfbdefbfbdefbfbd40efbfbd3b0278efbfbd1332efbfbd566f31634a2d216aefbfbdefbfbd5fefbfbd1720efbfbdefbfbdefbfbdefbfbdefbfbd3c7879efbfbdc38f6f07efbfbdefbfbd62330c0947efbfbdefbfbdefbfbd090927efbfbdefbfbd6779efbfbd5cefbfbdefbfbdefbfbd0357efbfbd2eefbfbd2b4befbfbd4fd69303efbfbd4e1054efbfbdefbfbdefbfbdefbfbddf8110efbfbdc780efbfbd42efbfbd3441efbfbd12efbfbdefbfbdefbfbdefbfbd23efbfbdefbfbd321fefbfbdefbfbd5fefbfbdefbfbdddbcefbfbdefbfbd5aefbfbd051d66d89531efbfbdefbfbd4aefbfbd2b25efbfbd51efbfbd2f68efbfbdefbfbd7762e98991efbfbd34efbfbdefbfbd48efbfbd3e33efbfbdefbfbd5608efbfbd0e2362215eefbfbd22efbfbd7319efbfbdefbfbd05efbfbdefbfbdefbfbdefbfbd7defbfbd53d99b2d307a206628efbfbd680939efbfbdefbfbd73efbfbd79d48eefbfbd78efbfbd76efbfbdefbfbdefbfbdefbfbd2eefbfbd5fc3bf7c0f0e71684f5d5a2d4208507c5c05efbfbdefbfbd39631213efbfbd73efbfbd2518efbfbd5cefbfbdefbfbdefbfbdefbfbdefbfbd57efbfbd6726efbfbd7bc49eefbfbd607e203eefbfbd7defbfbd665023efbfbd4c0c2a5275efbfbd6fefbfbd467cefbfbdefbfbd3ce4a899331935735c137fefbfbdefbfbd05efbfbdefbfbdefbfbd01504b070860efbfbdefbfbd01750a0000efbfbd630000504b0304140008080800efbfbd60efbfbd420000000000000000000000000a0000007374796c65732e786d6cefbfbd3ddb92efbfbd36efbfbdefbfbdefbfbd2b584aefbfbd79efbfbd245277efbfbdefbfbd6c391e7b37551eefbfbd154f36efbfbd531009495c53efbfbdefbfbdefbfbd46efbfbd6cefbfbd653f637f727fefbfbd3440efbfbd02efbfbd004969264eefbfbd2aefbfbd021a40efbfbdd1b835efbfbdefbfbdefbfbdefbfbdefbfbdd59e70103aefbfbd77efbfbd33efbfbdc39eefbfbd3dcbb71d6f73efbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbd7fefbfbdefbfbdefbfbd6befbfbdefbfbd4bdbb70e3befbfbd457a18efbfbd5c1c6aefbfbdefbfbd0befbfbd71efbfbd4defbfbd10784b1fefbfbd4eefbfbdefbfbdefbfbd0eefbfbdefbfbdefbfbd5aefbfbd7befbfbdefbfbd464b117a49efbfbdefbfbd4b6867efbfbdefbfbd29efbfbdefbfbd3aefbfbdcf916c6302efbfbd6aefbfbd56efbfbd235360efbfbdefbfbd1defbfbdefbfbd6c63020b3c15efbfbdefbfbd7defbfbdefbfbdcfa1efbfbdefbfbd7defbfbdefbfbd777b1439192cefbfbd5defbfbdefbfbd72efbfbdefbfbd46efbfbd7e39181cefbfbdefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbd582c16035aefbfbd206c2570efbfbd43efbfbd5228efbfbd1a6017efbfbdefbfbdc281efbfbd37061c76efbfbd2324efbfbd1fefbfbd1551efbfbd0eefbfbd150eefbfbd59efbfbd22efbfbdefbfbd6aefbfbdefbfbdefbfbdd688efbfbd4d096befbfbd2d0aefbfbd75efbfbd02efbfbdefbfbd3befbfbdefbfbdefbfbd3befbfbdc5b63b146d4b64321fefbfbd4325efbfbdefbfbdefbfbdefbfbd5917efbfbdefbfbdefbfbd580436efbfbd2a2b70efbfbdefbfbd64efbfbdefbfbd627befbfbdefbfbd135449efbfbdefbfbd4029efbfbdefbfbd70381ec4bf05efbfbd6325efbfbd3170221c08efbfbd5625efbfbdefbfbd5c2be1b8bf2b621aefbfbd1903efbfbdefbfbdefbfbd1351530e1d10efbfbd4b7befbfbd0c02efbfbdefbfbdefbfbd2841642defa0803b66625eefbfbd68e7969b17efbfbde5a09befbfbdefbfbd0b4101efbfbdefbfbd004c0d145d7f72efbfbdefbfbd5eefbfbd73560b60efbfbd11007543754d28efbfbdefbfbd2a1b18efbfbd01efbfbd49efbfbd06447276efbfbdefbfbd2671efbfbd6befbfbdefbfbd011130553006efbfbdefbfbd3d0e1c52efbfbd5cefbfbd6cefbfbdefbfbd2165efbfbd6138efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd37efbfbdefbfbd61d3833025efbfbdefbfbd5b3eefbfbdefbfbd7defbfbd7befbfbdefbfbdc2baefbfbd2d37efbfbdefbfbd3eefbfbd1b49efbfbd16efbfbd26efbfbdefbfbdefbfbd7e057f4befbfbd500361efbfbdefbfbdefbfbd603befbfbd3defbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd04efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd24efbfbdefbfbd067b4025efbfbd71efbfbdef9097efbfbdefbfbd3befbfbd05efbfbdefbfbdefbfbd0227efbfbd50471c4aefbfbdefbfbdefbfbd673d3cefbfbd56efbfbdefbfbd1b5423efbfbdefbfbdefbfbd30efbfbd51efbfbd6c5c53efbfbdefbfbdefbfbdefbfbd726cefbfbd7d465eefbfbdefbfbdefbfbd3930efbfbdefbfbd52efbfbd0b600b18efbfbd254fefbfbdefbfbd72efbfbdefbfbd740d5a166a1fefbfbd51efbfbdefbfbd09efbfbdefbfbdefbfbd0c5c2742efbfbd41efbfbd0defbfbdefbfbd0508efbfbdefbfbdefbfbdc3a313efbfbdefbfbdefbfbdefbfbd3eefbfbd20efbfbdefbfbdefbfbd2c11efbfbd24efbfbd21efbfbdd4a0efbfbd2059791c1f72efbfbd6defbfbd4607efbfbd45efbfbdefbfbd67efbfbdefbfbd2640efbfbdefbfbd63efbfbd382cefbfbdefbfbdefbfbd03705341efbfbd34efbfbdefbfbdefbfbd08efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbd7cefbfbdefbfbdefbfbd69efbfbd692defbfbd2defbfbdefbfbdefbfbd0e03efbfbdefbfbdefbfbdefbfbd37efbfbd61efbfbd30efbfbdefbfbd15d69fefbfbdefbfbd11efbfbdefbfbd3aefbfbd49580fefbfbdefbfbd0267efbfbd6fefbfbdefbfbdefbfbdefbfbd274e2defbfbd36efbfbd55efbfbd4f044defbfbd00166616efbfbd7e73efbfbd45efbfbd32ceb940efbfbdd189efbfbd7a1cefbfbd46efbfbd4150efbfbd3d0a10efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd10efbfbd640cefbfbd1eefbfbdefbfbd7e0cefbfbdefbfbdefbfbd36314fefbfbdefbfbd2aefbfbd08efbfbdefbfbd3002efbfbdefbfbd780defbfbdefbfbd096e3befbfbd16373d37d0a3554a511cefbfbdefbfbd643e252b04efbfbdefbfbdefbfbdefbfbd1aefbfbd214e640cefbfbd13e882bf0fefbfbd26efbfbdefbfbdefbfbdefbfbd13efbfbd73efbfbd1c420c5cefbfbdefbfbd6c6377efbfbd7eefbfbdefbfbd4154efbfbd16efbfbdefbfbd6fefbfbdefbfbd61efbfbd235aefbfbd226f73401b28efbfbd5c5a60efbfbdefbfbd1605efbfbd141f3fefbfbd2821efbfbd74efbfbd14efbfbdefbfbd5b33deb00e78efbfbd6fefbfbdcfbcefbfbd75efbfbd6b3cefbfbdefbfbdefbfbd2e49efbfbdefbfbdefbfbdefbfbd4eefbfbdda826eefbfbd3aefbfbdefbfbd7129db9231efbfbdefbfbdd3bd4aefbfbd01efbfbd6c4fefbfbd2defbfbdefbfbd7cefbfbdefbfbdc8b671efbfbd536cefbfbd05efbfbdefbfbdefbfbd4928efbfbdd4acefbfbdefbfbdefbfbdefbfbd43efbfbd21efbfbd600807efbfbd7450efbfbd7aefbfbdefbfbd2aefbfbdefbfbd0eefbfbdefbfbd470619efbfbd47efbfbdefbfbdefbfbd6c1769efbfbdefbfbd0343efbfbd56efbfbd40efbfbd04d1954c416defbfbdefbfbd74efbfbdd5a578eebea842efbfbdefbfbd13efbfbd2aefbfbdefbfbd3befbfbd5851efbfbd307708592d0befbfbd0e391eefbfbdefbfbdefbfbdefbfbdefbfbd39efbfbdefbfbd21efbfbd66405aefbfbd431c290befbfbdefbfbdc5a2efbfbdc48befbfbdefbfbd1f10efbfbd27efbfbd050e39efbfbdefbfbd62efbfbdefbfbdefbfbd1e582713777a7028efbfbdefbfbdefbfbd17efbfbdefbfbd7aefbfbd6f70efbfbd25efbfbd6a62677503efbfbd03efbfbdefbfbdefbfbd19efbfbdefbfbd46efbfbdefbfbd2b75075c7c2e0a43400f2cefbfbdefbfbd43efbfbd0158efbfbd1772291d1d30efbfbdefbfbd57efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7cefbfbd596fefbfbd45efbfbd55efbfbdefbfbdefbfbdc4865526efbfbdefbfbd520befbfbdefbfbd4650efbfbd1a457800efbfbd3fefbfbdefbfbdc7956fefbfbd78efbfbdefbfbd54efbfbd2e3aefbfbd67084defbfbd2eefbfbd1528487603efbfbd623d2b52efbfbdefbfbdefbfbd33421be68486efbfbdefbfbdefbfbd7eefbfbd5c2e0cefbfbd5aefbfbd197fefbfbdefbfbd16efbfbd36efbfbd7aefbfbd2811efbfbdefbfbdefbfbd2ac6b54301cc8d60efbfbd7b1a034eefbfbd13276603efbfbd58efbfbd5144364cefbfbdefbfbdefbfbd7c3439cf8316efbfbd04efbfbd3f20371b7f25ce8446efbfbd1eefbfbd1eefbfbd7b44efbfbdefbfbdefbfbd2a05efbfbd63cb9aefbfbdefbfbdefbfbd4526efbfbd646e2befbfbd4861efbfbd1a37103fefbfbdefbfbd516c0b0c42332eefbfbd2232efbfbd52efbfbd1eefbfbdefbfbd4c0bcc9cefbfbd3132daba09efbfbdefbfbd6a2eefbfbdefbfbd02efbfbdd98cefbfbddd9aefbfbd29efbfbdefbfbd321674efbfbdefbfbd660b5defbfbd7cefbfbd4e41efbfbd15efbfbde7b4ba09efbfbd47efbfbd3c1fefbfbd3aefbfbdc7ab09efbfbdefbfbd00efbfbd16efbfbdefbfbdefbfbdefbfbdef9f873072efbfbd27efbfbd456f03431d212cefbfbdefbfbd6f4b4b064a57efbfbdefbfbdefbfbd09efbfbd2e28efbfbd4befbfbd1befbfbd3fefbfbdefbfbddbb85a6eefbfbd5727efbfbd12efbfbdefbfbd56efbfbdefbfbd02efbfbdefbfbd1f3a11efbfbdefbfbd25efbfbdd9a016efbfbdefbfbdefbfbdefbfbd09efbfbd6374efbfbd03efbfbd01efbfbdefbfbd201501efbfbdefbfbd1b12efbfbd14540307efbfbdefbfbd0729efbfbdc582efbfbd3a09373320102fefbfbd63221aefbfbd1743302d3b47efbfbd17761b1f6075efbfbd44efbfbd2a621defbfbd742969efbfbdd989efbfbd5033efbfbdefbfbdefbfbdefbfbd2062054c0354efbfbdefbfbdefbfbd3defbfbdd3a9efbfbd5546efbfbdefbfbdefbfbdefbfbd78da9fefbfbdefbfbd4557efbfbd2cefbfbdefbfbdefbfbdefbfbd1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4016efbfbdefbfbd64de9270efbfbd1e6b16797b0aefbfbd65613a32efbfbd7a5168efbfbdefbfbd447cefbfbd4fefbfbd74efbfbd64efbfbdefbfbd35efbfbd49efbfbdefbfbdefbfbdefbfbdefbfbd22efbfbd53efbfbd59efbfbd1511efbfbd62efbfbd56efbfbd64790634efbfbd47efbfbd39571a50efbfbdefbfbd773cefbfbd3811206249efbfbd02311befbfbd24efbfbd5b55efbfbd12efbfbdefbfbd3b2eefbfbd200151efbfbd68efbfbd07efbfbd3f2219efbfbd52efbfbd29efbfbdefbfbdefbfbdefbfbd54efbfbd07efbfbd48efbfbd08efbfbdefbfbd73efbfbdefbfbdcfa1efbfbd756b6fefbfbdefbfbd571fefbfbd4838efbfbd6eefbfbd036befbfbdefbfbdc482efbfbd763fefbfbd0e47efbfbddd8fefbfbd2d32efbfbd5b5564efbfbdefbfbd4165efbfbdefbfbd54efbfbdefbfbd58efbfbd721befbfbd15efbfbd2cefbfbd04efbfbd0fefbfbdefbfbdefbfbd1e64dcb7efbfbd36efbfbdefbfbdefbfbd3472d0b2efbfbd1d37efbfbd6f7613efbfbd4eefbfbd057b581d4aefbfbdefbfbd701e4471efbfbdcb9fefbfbd28efbfbdce96efbfbd6969efbfbdefbfbdefbfbdefbfbdefbfbd012befbfbdefbfbd27efbfbdefbfbd437cefbfbd26543c04efbfbdefbfbdefbfbd00efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd5fefbfbdd8a3efbfbd4b41efbfbd54efbfbdefbfbdefbfbd08444defbfbdefbfbd6a270e401eefbfbd3a165fefbfbd2c42efbfbd4369224827efbfbd49efbfbd14efbfbd1defbfbdefbfbd4eefbfbd75d691efbfbdefbfbdefbfbd0f07efbfbd646c13efbfbdefbfbdefbfbdefbfbd50efbfbd4aefbfbd404b58efbfbdefbfbd05583a07efbfbdce87efbfbdefbfbdefbfbdefbfbd3c5befbfbd1e19584373efbfbd66efbfbd6267efbfbdefbfbd74171f5712efbfbd3f71774e7eefbfbd073e16efbfbdefbfbdefbfbdefbfbd12704defbfbdefbfbd1defbfbd46efbfbdefbfbd4612efbfbdefbfbd6868efbfbd3c6befbfbd077aefbfbd2e1427efbfbdefbfbdcd92efbfbd3872d885efbfbd13efbfbd37efbfbd2defbfbdefbfbd40efbfbd5d1727efbfbd630108efbfbdefbfbd635b4f7a0befbfbdefbfbd2aefbfbd2712efbfbdefbfbdefbfbd20efbfbd55efbfbd2a7e3d48efbfbd552c10efbfbdefbfbd73150139efbfbd3ecf982cefbfbdefbfbd60efbfbd48710fefbfbd6953efbfbd2defbfbd465aefbfbdefbfbd16efbfbd3417efbfbd5fefbfbdefbfbdefbfbd0755efbfbdefbfbd64efbfbd3cefbfbd7eefbfbdefbfbdefbfbd4fde94efbfbd3004efbfbd41efbfbdefbfbdefbfbd3e38efbfbdefbfbd27efbfbd09efbfbd10efbfbd3d7befbfbdefbfbd714d2f03efbfbdefbfbd27efbfbd32efbfbd38efbfbd2705107b0966efbfbdefbfbdefbfbdefbfbd0816d78920efbfbd5e266b7aefbfbd05efbfbdefbfbdefbfbd6fefbfbd51efbfbd171975efbfbd22efbfbdefbfbd5f64efbfbdc98befbfbd3a7defbfbd51672f32efbfbdefbfbd45465defbfbdc8a8c6b0efbfbd61077937efbfbd70efbfbdefbfbd15efbfbd77735847196cefbfbd02efbfbdefbfbd105befbfbdefbfbd79efbfbd5b1874efbfbdde9a12efbfbd11efbfbd6470efbfbdefbfbd2f3cefbfbdefbfbd0e4c23efbfbd225aefbfbd6a62efbfbd5e770eefbfbd38efbfbdefbfbd1a164e0fefbfbdefbfbdefbfbd41154e0a18efbfbdefbfbd624cefbfbdefbfbd2b0cefbfbdefbfbd0d53633133efbfbdefbfbd5946efbfbdefbfbdefbfbd764cefbfbdefbfbd04efbfbd2befbfbdefbfbd23687c59efbfbd26efbfbdefbfbdefbfbd044d2e4befbfbd6c3e1b5fefbfbdefbfbdefbfbd65095aefbfbdefbfbdefbfbdefbfbd1234efbfbd244146df981befbfbdefbfbd1234efbfbd2c4123d0b9efbfbd12efbfbdefbfbd2c41efbfbdefbfbdefbfbdefbfbd6e3b1d11744fefbfbd6c6676efbfbd15efbfbd6a31efbfbd201b4d2159ddafefbfbdefbfbd21efbfbd17efbfbdefbfbdefbfbd4267efbfbdefbfbd6befbfbd41cc830e27efbfbd6fefbfbd116a36efbfbdefbfbd3c6064efbfbdefbfbd544fefbfbd3d1057efbfbd0aefbfbd30efbfbd7e3eefbfbdefbfbdefbfbd39efbfbd3649efbfbdefbfbdefbfbdefbfbdefbfbd6b0f795a70efbfbdefbfbd56454aefbfbd365defbfbd7e14c2b2542763efbfbd2115281d24efbfbdefbfbdefbfbd33294538efbfbdefbfbd57efbfbd2fefbfbd04efbfbd57efbfbdefbfbd7a39efbfbdefbfbdefbfbdefbfbdefbfbd39efbfbd15efbfbd0400646a13efbfbdefbfbdefbfbdefbfbdefbfbd4fefbfbdefbfbd5628efbfbdefbfbd583aefbfbd285befbfbd3f10efbfbdefbfbd5defbfbd7968efbfbdefbfbd26627defbfbdefbfbdefbfbdd98cefbfbdefbfbdefbfbd5fefbfbdefbfbdefbfbd1869efbfbdefbfbd51efbfbdefbfbd40efbfbdefbfbd0160efbfbd7f53efbfbd23efbfbdefbfbd3defbfbd0cefbfbd71611e6356efbfbd1037efbfbdefbfbddc9c32435441efbfbdefbfbd5119efbfbdefbfbdefbfbdefbfbd00efbfbdefbfbd17efbfbdefbfbd703c5b3440efbfbd7354efbfbdefbfbd62321f3540efbfbd4defbfbd06381b16efbfbd6e161328efbfbdefbfbdefbfbd3165efbfbdefbfbd6c6b7fefbfbd4736391fd58972efbfbdefbfbdefbfbd193fefbfbdefbfbd35417cc5a3efbfbd2a3e1eefbfbd142647e3ac9cefbfbd5eefbfbd07481e63efbfbd52efbfbd79efbfbdefbfbd76efbfbd2d14efbfbdefbfbd27efbfbd48efbfbdefbfbd271aefbfbd01efbfbdefbfbdefbfbd5d3374efbfbdd8b6efbfbdefbfbd796a2cc6a54cefbfbdd7a973efbfbdefbfbd23efbfbd5cefbfbd6befbfbd07efbfbd3cefbfbdefbfbdefbfbdefbfbd3f20efbfbdefbfbdefbfbd2eefbfbdefbfbd37c5b3efbfbd65efbfbd7e597fefbfbd46efbfbd2fefbfbd3fefbfbdefbfbdefbfbd4b6befbfbdd194c5950defbfbdefbfbd7920efbfbdefbfbd5b62efbfbdefbfbd147825efbfbdefbfbdefbfbd01efbfbd5befbfbdefbfbdefbfbd31efbfbd7842efbfbd5cefbfbdefbfbdefbfbdefbfbdc2985d7861625c606162efbfbdefbfbdefbfbd587d61625c606132efbfbd0f4defbfbdefbfbd5345efbfbd24efbfbdefbfbd7d421f57efbfbdefbfbd2defbfbdefbfbd36efbfbdefbfbd0573efbfbd706a3cefbfbdefbfbdefbfbd69efbfbdefbfbd095aefbfbdefbfbd2e0d5e09efbfbd5f3a6835efbfbd1befbfbdefbfbd12efbfbd5f3b6c35efbfbd1befbfbdefbfbd122e4befbfbd7619d79befbfbd72efbfbd55efbfbdefbfbd65255d6eefbfbdefbfbddc93efbfbd161370efbfbd7d5230efbfbd121cefbfbd65efbfbdefbfbd32efbfbd764eefbfbdefbfbd53efbfbdefbfbd17efbfbd316ed78f3c17efbfbdefbfbdefbfbd07efbfbd1aefbfbd34efbfbd3fefbfbd4b49efbfbd34efbfbd007b7b62efbfbd6f4fca9fefbfbdefbfbd4f4fefbfbdefbfbddb91efbfbd777eefbfbd45efbfbdefbfbdefbfbdefbfbdefbfbd29efbfbd4ade8fefbfbd4fefbfbd724b64efbfbdefbfbd0defbfbd64efbfbd74592eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd61efbfbdefbfbd7c58efbfbdefbfbd6e6cefbfbdefbfbd7773efbfbdefbfbd306a7a454625efbfbd63efbfbdefbfbdefbfbd13efbfbdefbfbdefbfbdefbfbdefbfbd20746cefbfbd4befbfbd4363efbfbd58efbfbd62efbfbdd9bb66614a78396fefbfbdcab91a27672fefbfbd7259425fefbfbd09efbfbd0eefbfbdefbfbdefbfbd14efbfbd26efbfbdefbfbdefbfbd011768efbfbdd8981aefbfbd1aefbfbd6430efbfbdefbfbdefbfbdefbfbd7a17efbfbdefbfbdefbfbd5b57efbfbd2566efbfbdefbfbdefbfbd1f2408395fefbfbd7aefbfbd02efbfbd3c09cda4efbfbdefbfbdefbfbd65efbfbd2eefbfbd5e4b0dcfbdefbfbdefbfbd7aefbfbd5fefbfbd08154aefbfbd5950efbfbd69237d3eefbfbd57efbfbdefbfbd64efbfbdefbfbdefbfbdefbfbd6813372d5a4d730b451d6aefbfbdc9ae43efbfbd66d5ab3fefbfbd54efbfbd5cefbfbdefbfbdefbfbdefbfbd1defbfbd70efbfbdd18a24efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd66efbfbdefbfbdefbfbd4f67efbfbdefbfbdefbfbd571769efbfbde4bc9befbfbddc901543efbfbdefbfbd41454aefbfbdefbfbd3b17efbfbd6332efbfbd537362efbfbdefbfbdefbfbdefbfbd6514efbfbd2cefbfbdefbfbd01efbfbdefbfbd16efbfbd1b2defbfbd3a0d6e3a377f15efbfbd2fefbfbd20efbfbdefbfbdefbfbd49efbfbdefbfbdefbfbd3fefbfbdefbfbd15efbfbdefbfbd25efbfbd382eefbfbd62efbfbd2e4defbfbd7cefbfbdefbfbdefbfbdefbfbd0defbfbdefbfbd3270efbfbd3fefbfbdefbfbd432fcbaeefbfbd0553efbfbdc7976defbfbdefbfbdefbfbd2aefbfbd05efbfbdefbfbd1aefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbdefbfbdefbfbd17efbfbd3e17efbfbd76efbfbdefbfbdefbfbd67efbfbdefbfbd2117efbfbd75465fefbfbd6254efbfbd215eefbfbd35efbfbdefbfbdefbfbd7aefbfbd791d0cefbfbdefbfbd43efbfbd63efbfbd6befbfbd232befbfbd6146efbfbdefbfbdefbfbd5cefbfbdefbfbdefbfbd65efbfbd02efbfbdefbfbdefbfbdc3956c2b702e1373efbfbd174a43efbfbdefbfbdefbfbdefbfbdefbfbd1e2a435e22cc92efbfbd1b5476efbfbdefbfbd5defbfbd36efbfbdefbfbd0fefbfbd5defbfbdefbfbd243c56efbfbdefbfbd10efbfbd6d7c75efbfbdefbfbdefbfbd76efbfbd0cefbfbd525f072e2652dea36529efbfbd4befbfbddd8aefbfbd17efbfbdefbfbd641d7a4befbfbdefbfbd1620efbfbdefbfbd17efbfbd212f12efbfbd0cefbfbd2f51efbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4b67efbfbd5defbfbd02536defbfbd56efbfbd60efbfbdefbfbd33735defbfbd78efbfbd5d233912efbfbdefbfbdefbfbd346befbfbdefbfbd4eefbfbd214aefbfbd60efbfbd337a0540efbfbdefbfbd255aefbfbd1e12efbfbdefbfbdefbfbd74364a2eefbfbd6eefbfbdefbfbd39efbfbd3f5defbfbdefbfbd3931efbfbd28efbfbd03efbfbdefbfbdefbfbd43efbfbd5fefbfbdd28cefbfbd41142027efbfbd5a3a053d4f34efbfbd2e48416fefbfbdefbfbd3461efbfbd3a7eefbfbd37efbfbd2aefbfbdefbfbd5befbfbdefbfbdefbfbdd4b7efbfbd192336efbfbd73efbfbdefbfbd3524efbfbd1541efbfbd4c21efbfbd5151efbfbd0aefbfbd38efbfbd0a6a2c3049040c0eefbfbdefbfbd0038efbfbd4c4a00637cefbfbd6cefbfbdefbfbd6e56efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd2210efbfbd43453d4b07efbfbdefbfbdefbfbd5962efbfbd1d7a3e63efbfbddfa4250021efbfbdefbfbdefbfbd496aefbfbdefbfbd70267c2eefbfbd7f53efbfbdefbfbdefbfbd210d28efbfbd68312f00426befbfbdefbfbdefbfbd1046efbfbd2e2defbfbd04efbfbdefbfbd3a48efbfbd63457c2b18efbfbd53efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd01efbfbd78efbfbdefbfbdefbfbd2c38efbfbd5b7a20efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd477cefbfbd5f580065145521efbfbd7d49efbfbdefbfbdefbfbdefbfbd5b620902efbfbd4e60efbfbd24590cefbfbd0803efbfbd4cefbfbdefbfbd1c5befbfbdefbfbd1c0524efbfbd6d62efbfbd7defbfbd7eefbfbd18efbfbdefbfbdefbfbd292805efbfbd0c2249401ad9bcefbfbd241cefbfbdefbfbdefbfbd1d1e4f4457efbfbdefbfbdefbfbd75efbfbdefbfbdefbfbd6b12efbfbdc7ab6a1d5e13efbfbd7e35efbfbd3728efbfbdefbfbdc28e243464efbfbdefbfbd35efbfbdefbfbd60451aefbfbdefbfbdefbfbdefbfbd10efbfbd29efbfbd4fefbfbddf91060e4aefbfbd7befbfbd0aefbfbdec8c97efbfbd5d0819efbfbd521aefbfbdefbfbde68f887341efbfbd1723efbfbd32efbfbdefbfbd79efbfbdefbfbd5372064c5e1fefbfbd5aefbfbdefbfbd5e01efbfbd0300efbfbdefbfbdefbfbd1f3fefbfbdefbfbdefbfbd1defbfbd6c47006bda80efbfbdefbfbd1804efbfbdefbfbd46efbfbdefbfbd26efbfbdefbfbd247a00efbfbdefbfbdefbfbdc7bd5b5fefbfbdefbfbdefbfbdefbfbde3a78214efbfbdefbfbd2fefbfbdefbfbdefbfbdd1b1efbfbdc6a0115aefbfbdefbfbd30efbfbdddae5c646b4a2defbfbdefbfbd68323c212f62efbfbd5d6c4554456e7aefbfbd212007efbfbdefbfbd5befbfbd752defbfbdefbfbdefbfbd0d1a6347efbfbd3cefbfbdefbfbd2fefbfbd16efbfbd53efbfbd4a494aefbfbdefbfbd7fefbfbd2aefbfbdefbfbd4b46440cefbfbd2aefbfbd20efbfbdefbfbdefbfbdefbfbdefbfbd3befbfbd76efbfbd7fefbfbd693312efbfbd20efbfbd0e70187eefbfbd7245efbfbdefbfbd5c7763efbfbdefbfbddf9cefbfbdd895efbfbd4427efbfbd4ed89a54efbfbdefbfbd13efbfbd08efbfbd0202efbfbd7cefbfbd4defbfbd2376efbfbd6c500e215c43efbfbd14efbfbd777befbfbd222cefbfbd073325efbfbdefbfbd03efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6d161cefbfbdefbfbd0b14efbfbd5351efbfbd68efbfbdefbfbd38efbfbd09efbfbdefbfbdefbfbd41efbfbdefbfbd79efbfbdc7b84c6cefbfbd6a524d59efbfbdefbfbd52efbfbdefbfbdefbfbdefbfbd58efbfbd0d7e506defbfbd56efbfbdefbfbd5defbfbd41efbfbd1f333d60d7ad20efbfbdefbfbd2d4befbfbd09efbfbd7defbfbd23efbfbd48efbfbdefbfbd5e15efbfbdefbfbdefbfbdefbfbd194965efbfbd1f1aefbfbd3d4befbfbd7f50efbfbd72c6b077efbfbd29efbfbdefbfbd09efbfbdefbfbd58efbfbd7e1714efbfbd442651efbfbdefbfbdefbfbd17efbfbdefbfbd050defbfbd1a30efbfbdefbfbd47550535efbfbdefbfbd071eefbfbd67efbfbdefbfbd30efbfbd5befbfbd2b79374626717d2befbfbdefbfbd42efbfbd1fefbfbdefbfbdefbfbdefbfbd2c172e057a7cefbfbdefbfbd31efbfbd32efbfbdefbfbd39321eefbfbdefbfbd743e3417efbfbd390904efbfbd55efbfbd23103104195aefbfbdefbfbd7cefbfbd32efbfbd62cf961defbfbd23efbfbdefbfbd6defbfbd33efbfbdefbfbdefbfbdefbfbd51efbfbd4aefbfbd1befbfbd634aefbfbd585249efbfbd51117befbfbd333b53efbfbd355465efbfbd66550041dfa5efbfbd391defbfbd1a3f63efbfbd1505efbfbdefbfbd5600272befbfbdefbfbd49efbfbdefbfbd58293a312004efbfbdefbfbdefbfbd73efbfbdefbfbd01efbfbd7befbfbd1747efbfbd3d7a5619634aefbfbd507f07d5a7efbfbd1a3d2befbfbd663103efbfbd29efbfbdefbfbd64efbfbdefbfbd77efbfbddc9befbfbdefbfbd3526efbfbdefbfbd164fefbfbdefbfbdefbfbdefbfbd511a0b1a4aefbfbdefbfbdefbfbd1a0b1aefbfbd58103c4539740eefbfbd0aefbfbd66efbfbdefbfbdefbfbd346a380fefbfbdefbfbd1d76efbfbd271cefbfbd343befbfbdefbfbd5876e2948d16efbfbd14d1bf376befbfbd5defbfbd00efbfbdefbfbdefbfbd32efbfbdefbfbd32efbfbdd5865840efbfbd67440e0befbfbd1f3d4b3ecaaaefbfbd743a1370efbfbd49efbfbd18efbfbd2d2eefbfbd5acbaa392eefbfbd23efbfbdefbfbdefbfbd386aefbfbdefbfbd5cefbfbd5068efbfbdefbfbdefbfbdefbfbdefbfbdd699efbfbdefbfbdefbfbdefbfbd6defbfbd2e46c3ba20efbfbdefbfbd58efbfbdefbfbd526f20efbfbd2e3806efbfbd43efbfbdefbfbdefbfbd281252d99941efbfbd1b63efbfbdefbfbd552a50efbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbdefbfbd1174efbfbd2d55efbfbd00efbfbdefbfbd19efbfbdefbfbd607eefbfbdefbfbd4437efbfbdefbfbd783aefbfbdefbfbd70efbfbd7221efbfbd7b1aefbfbdefbfbdefbfbd3cefbfbdefbfbdefbfbd3140efbfbdefbfbd40efbfbd0b29efbfbd0703efbfbdefbfbd457559377f7fefbfbdefbfbd50efbfbdefbfbd48efbfbdefbfbd49efbfbdefbfbd17dbbe75efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbd07504b070836efbfbdefbfbd4d2913000005efbfbd0000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c00000073657474696e67732e786d6cefbfbd5aefbfbd6e234915efbfbdefbfbd29efbfbd05120832efbfbdefbfbdefbfbdefbfbdefbfbd64efbfbd76efbfbdefbfbdefbfbdefbfbd6d3b317051efbfbd2edb9d545735efbfbdd5b11defbfbdd49bd995766159400209efbfbdefbfbd5d76efbfbdefbfbd4770efbfbdce8c34efbfbdefbfbd7eefbfbd7eefbfbd3defbfbd76efbfbdefbfbdefbfbd26efbfbd7649efbfbd224eefbfbd55efbfbdefbfbd3975efbfbd77efbfbd53efbfbdcf9eefbfbd4defbfbd74efbfbd6defbfbd60742b127b12efbfbd2c61efbfbd31dda0efbfbdefbfbd484defbfbd2e3fefbfbd3cefbfbdefbfbdefbfbd33efbfbd6e1b1a4eefbfbd4c734d4cefbfbdefbfbdefbfbd39efbfbd21efbfbd124cefbfbd4e72efbfbd782befbfbdefbfbd34c990633849efbfbd4cefbfbd24efbfbdefbfbd6416efbfbdefbfbdd392efbfbd47274365efbfbd6fefbfbdc4a0efbfbd5befbfbd2eefbfbd567265efbfbdefbfbdefbfbd3defbfbd3e61766725efbfbd482456c2a7efbfbd433546efbfbd46e7b1aa46efbfbd6fefbfbd62efbfbdefbfbd28121346efbfbd09efbfbdc5a3d1b5efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd226fefbfbd261ed9beefbfbdc3b5efbfbdefbfbdefbfbdefbfbd0a461fefbfbd06c7a6efbfbdefbfbdefbfbdefbfbd6befbfbdefbfbdefbfbd08efbfbd4cefbfbd19efbfbd77efbfbdc8a479df9d53efbfbdf18a8d91caacefbfbdefbfbd133eefbfbdefbfbd0961efbfbd13d98e3e5befbfbd2fefbfbdefbfbd6273efbfbdefbfbd65efbfbd6d183aefbfbd4e14efbfbdefbfbd341a5fefbfbd4fefbfbd1e363aefbfbdefbfbdcb8eefbfbd57575767efbfbd5eefbfbdefbfbd5e05efbfbd106338efbfbd45efbfbdefbfbdefbfbd3b1a5aefbfbd11efbfbd6864efbfbdefbfbd2eefbfbd4defbfbd3e4dd9acefbfbdefbfbd3cefbfbdefbfbd34efbfbd6d44efbfbd47efbfbd5f36efbfbdefbfbd6c501defbfbdefbfbd7eefbfbd59efbfbd032cefbfbd03efbfbd610f1eefbfbdefbfbd7defbfbdefbfbd521d6e1befbfbdefbfbd22efbfbdefbfbd6fefbfbdefbfbdd88b45efbfbd37efbfbd67efbfbd3b2d55efbfbd36efbfbd670e3cefbfbd6811efbfbdefbfbd6409efbfbd2e3cefbfbd43efbfbdefbfbdefbfbd39223270efbfbd6d0b65efbfbd18efbfbdcc9cefbfbdefbfbdefbfbdd98437193355107537dabaefbfbdefbfbd7301520e0defbfbdefbfbdd38cefbfbd26efbfbdefbfbdd48befbfbdefbfbd62efbfbd7461597defbfbd2f59efbfbd71664f5e7b2c3aefbfbdefbfbdefbfbdefbfbd2a2658efbfbd58efbfbdefbfbdefbfbdefbfbd0c4befbfbdefbfbdefbfbd6d74efbfbdefbfbd780c58efbfbd0740717c7c391d7defbfbdda884371efbfbd5fefbfbd6a0920efbfbd2befbfbd321e4eefbfbdefbfbd75efbfbd454628efbfbdefbfbd3a38efbfbdefbfbdd38eefbfbd5c7a174517504e42252aefbfbdefbfbd12411aefbfbd32efbfbdefbfbd31efbfbdefbfbd000cefbfbdefbfbd19efbfbdefbfbdefbfbd50264b43c6b4efbfbd40efbfbd4c42efbfbd1d6defbfbdefbfbd1c0befbfbd5defbfbd0121efbfbd4a350059efbfbdefbfbdefbfbdccac62efbfbdefbfbd2d1a0b3323efbfbd207b19efbfbd6044efbfbd6c61efbfbd3a70efbfbd4116efbfbd4cefbfbd30efbfbd0569625816efbfbd4befbfbdefbfbd5d7befbfbd46efbfbdefbfbd1424dca92c1befbfbdefbfbdefbfbd12efbfbd51115aefbfbd1d1befbfbd42605befbfbdefbfbd2c61efbfbd730455efbfbdefbfbd4c6477efbfbd7b2572115a6aefbfbd05efbfbdefbfbd3454efbfbd4214d892efbfbd093707efbfbd13efbfbd033978632d6550640f22efbfbd27efbfbdefbfbd4f5662efbfbd6eefbfbd106befbfbd1a2f47efbfbdefbfbd3935efbfbd2fefbfbd2befbfbd16efbfbd0f50efbfbd7eefbfbdefbfbd1fefbfbdefbfbd63efbfbd72efbfbdefbfbd52efbfbd1befbfbd73efbfbdefbfbd4e73efbfbd42efbfbdefbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbd17efbfbd6612efbfbdefbfbdc2a10d45efbfbd2b76673cefbfbdefbfbd6414efbfbd5befbfbdefbfbd323aefbfbdefbfbdefbfbd09efbfbdefbfbdefbfbdefbfbd1e57efbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd68efbfbd683f51efbfbd67efbfbdefbfbd5a77efbfbdefbfbdd88eefbfbdefbfbdefbfbdefbfbd796635efbfbdefbfbdefbfbdcda303efbfbd79efbfbd3f6cefbfbdefbfbd27efbfbd3d3eefbfbd58efbfbdefbfbd5aefbfbdefbfbdefbfbd18efbfbd7b075d74543e3cefbfbd277aefbfbd5f253573efbfbd6a6407efbfbd3871efbfbdefbfbdefbfbd5fefbfbd47efbfbd68efbfbd5177efbfbdefbfbd682fefbfbdefbfbd38efbfbd74efbfbdefbfbd360b672defbfbdefbfbddcadefbfbdefbfbdefbfbd4e227f523eefbfbdefbfbd0a4eefbfbd495a300eefbfbd74efbfbd342b4437efbfbd4953efbfbdefbfbd6fefbfbdefbfbdefbfbd46efbfbdefbfbd54e5b0964d65efbfbdefbfbdefbfbdefbfbdefbfbd425befbfbd56efbfbdefbfbdefbfbdefbfbd5aefbfbd6c34552b5510efbfbd76523b6a34562eefbfbd2befbfbd4aefbfbd5e5633efbfbdefbfbd68efbfbd54efbfbdd48a4aefbfbd52efbfbd65efbfbdefbfbd463651574f7951efbfbdefbfbdefbfbd191954efbfbd321b670defbfbdefbfbd39efbfbd1befbfbdefbfbdefbfbd50efbfbd4f6f427cefbfbd100a2cefbfbd20efbfbdefbfbd61efbfbd4c634204442c5e4defbfbdefbfbdefbfbdefbfbdefbfbd7013efbfbd2cefbfbdefbfbd39efbfbd7429e6a8a8efbfbd70efbfbd57efbfbd5c073aefbfbd3d1d1b595d67efbfbdefbfbd0c475aefbfbdefbfbd2603153527efbfbd0cefbfbdefbfbd0defbfbdefbfbd204056efbfbdefbfbd34334d74efbfbd01efbfbdefbfbdefbfbdc7acefbfbd71582befbfbdefbfbd6ed7b538efbfbdefbfbd1117efbfbdefbfbd52e790af0ad8afefbfbdefbfbd21efbfbd32480befbfbd15efbfbd00efbfbd2befbfbd690322efbfbd48efbfbdefbfbdefbfbd12efbfbd515eefbfbd12efbfbdefbfbd09efbfbd0c67d0b6efbfbd6276efbfbd49efbfbdefbfbd0aefbfbdefbfbdefbfbd2518efbfbdefbfbdefbfbd60efbfbd53efbfbdefbfbd584a3029c7905eefbfbdefbfbd66efbfbd0cefbfbd784c10efbfbd7cefbfbd2c24efbfbd1fefbfbdd88d13043aefbfbd437ccf8cdb85efbfbd11efbfbd567370efbfbdefbfbd6326efbfbdc790efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd74efbfbdefbfbd52efbfbd0e74efbfbdefbfbd3aefbfbddea2efbfbdefbfbd11efbfbd5c12efbfbdefbfbdefbfbd36efbfbd4d584fefbfbdefbfbd30742b181a71efbfbdefbfbd0ae7b6b00aefbfbdefbfbdefbfbd3278efbfbdefbfbd05287eefbfbdefbfbdefbfbdefbfbd32efbfbdefbfbd2115efbfbd5a4803efbfbd2a0b114d545f1920530134efbfbd3076efbfbd5d10efbfbdefbfbdefbfbdefbfbd4462732e6a2c404bdc8f0072efbfbd4eefbfbdefbfbd1cefbfbd427c2c4303c5882b027146efbfbd11efbfbd12efbfbd3d60efbfbd34efbfbd1a26121811747cefbfbdefbfbdefbfbdefbfbdefbfbd6860efbfbd42efbfbd2e24efbfbddeb061efbfbdefbfbd25efbfbdefbfbd6c4b4a4f13efbfbdc4835c6f1e0defbfbdefbfbd38160defbfbdefbfbd4d0b20616a01efbfbd2befbfbd430f15efbfbd6defbfbd5a594defbfbdefbfbd5679efbfbd02efbfbdefbfbd14efbfbdefbfbdefbfbdefbfbdefbfbd5326efbfbd4025efbfbd2a03efbfbdefbfbd104921056a6e74efbfbd52efbfbdefbfbd497324efbfbd77efbfbdefbfbdefbfbdefbfbd32efbfbdefbfbdefbfbd1defbfbd3eefbfbdefbfbd56efbfbd19efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd150c38efbfbdefbfbdefbfbd45efbfbdefbfbdefbfbdefbfbd03efbfbd4aefbfbd0d4c7447660e0aefbfbdefbfbdefbfbdefbfbd224d13efbfbdefbfbd2061efbfbddb8509efbfbd216111efbfbdefbfbd48efbfbd062241efbfbd05efbfbd2e4c5aefbfbd7d4befbfbd44efbfbd0077754a18efbfbdefbfbd7220efbfbd1b1befbfbdefbfbd43efbfbdefbfbdefbfbd044369efbfbd1165efbfbdefbfbdefbfbdefbfbd62efbfbd22efbfbd40efbfbd3b0f7c39efbfbdefbfbd41710c444b2ed5b82befbfbdefbfbdefbfbdefbfbdefbfbd156defbfbd1432efbfbd1569efbfbdefbfbdefbfbdefbfbd2aefbfbd22144cefbfbdefbfbd06786cefbfbdefbfbd1137013defbfbd3d770ed18e7befbfbdefbfbdefbfbd26432768efbfbdefbfbdefbfbd15efbfbdefbfbd483d28efbfbd78efbfbd6c08efbfbd29efbfbdefbfbd1150efbfbdefbfbdd08befbfbdefbfbd32655defbfbdefbfbdefbfbd7fefbfbd24efbfbdefbfbdefbfbdefbfbdcdbb2fefbfbdefbfbdefbfbdefbfbdefbfbddb95efbfbdefbfbdca833fefbfbd7defbfbdefbfbdefbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd5defbfbdefbfbd07efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7eefbfbd7b7fefbfbd3fefbfbdefbfbd5fefbfbdefbfbdefbfbdefbfbdc5a7efbfbdefbfbd2befbfbdefbfbd33efbfbdefbfbdefbfbdefbfbdefbfbd4befbfbdc59b60efbfbd5e30efbfbd20187e140c3f0eefbfbdefbfbd04c3bf07c397efbfbdefbfbdefbfbd6078195cefbfbd0a2eefbfbd082eefbfbd0a2eefbfbd195cefbfbd0eefbfbd7c16efbfbdefbfbd3c78efbfbdefbfbd19efbfbd06efbfbdefbfbdefbfbd593fefbfbdefbfbdefbfbd7fefbfbdefbfbd775fefbfbd7b7defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdc7beefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1c0cefbfbd0fefbfbd1f06c3afefbfbdcb97efbfbdefbfbdefbfbdefbfbd2fefbfbdefbfbdefbfbdefbfbd5c7c3e0c0c39efbfbdefbfbd37efbfbd541622efbfbdefbfbd374917560a313a14efbfbdefbfbd2a67efbfbd3561efbfbd745472efbfbd3aefbfbd680fefbfbdefbfbd3b0defbfbd77f388ba88efbfbdc485efbfbdefbfbdca83efbfbdefbfbdefbfbd0befbfbdefbfbd38efbfbd22efbfbd71696739efbfbd3b5a5eefbfbd6d46efbfbdefbfbdefbfbd4defbfbd33efbfbd15efbfbd683725efbfbd0050efbfbd58d89eefbfbd37efbfbdefbfbd1f7359efbfbd5739685defbfbdefbfbd3cefbfbdefbfbd14efbfbd27efbfbd0defbfbdefbfbdefbfbdefbfbd38efbfbd3defbfbdefbfbdefbfbdefbfbd56efbfbdefbfbdefbfbdefbfbd3976efbfbdefbfbd4befbfbd396336efbfbdefbfbdd383efbfbd3104efbfbd0f20080b6c07efbfbdefbfbd4b64787642efbfbdefbfbdefbfbd625964005defbfbd2defbfbdefbfbdefbfbd7d2defbfbdefbfbd513a4d7fefbfbd64efbfbd0b5253df8659efbfbdefbfbdefbfbdefbfbdcab41771efbfbdefbfbd01504b0708efbfbdefbfbd08efbfbd7b080000efbfbd2b0000504b0304140008080800efbfbd60efbfbd42000000000000000000000000080000006d6574612e786d6cefbfbdefbfbd4d6fefbfbd3010efbfbdefbfbdefbfbd2b2cefbfbd573026efbfbd345642efbfbd3defbfbdefbfbd55efbfbd5befbfbd59efbfbdefbfbd2ac79eefbfbdefbfbdefbfbd46c684efbfbddfafefbfbd14efbfbd7aefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbdefbfbdefbfbdefbfbdefbfbd58efbfbd13efbfbd4a6aefbfbdefbfbd48107a0814efbfbd42efbfbd6cefbfbdefbfbdefbfbd7eefbfbdefbfbdefbfbdefbfbdefbfbd5aefbfbdefbfbdefbfbd0315efbfbdefbfbd4750efbfbd3fefbfbd65c8a5efbfbdefbfbdefbfbdefbfbdefbfbd571b4535efbfbd6445153b42452defbfbdefbfbd0435efbfbdefbfbd394defbfbd46efbfbdefbfbd5e48efbfbd6fefbfbdefbfbdd69614efbfbd69efbfbd661168efbfbd61efbfbd5aefbfbd70171d51efbfbd27efbfbdefbfbd4defbfbd51efbfbd6328efbfbdefbfbd506112103cefbfbdefbfbdefbfbd6b4defbfbdefbfbddc92efbfbd7a6aefbfbdefbfbdefbfbd5d14efbfbd31efbfbdefbfbd473a334214efbfbd0defbfbdefbfbd05760eefbfbd65efbfbd4942efbfbdefbfbd43efbfbdefbfbdefbfbdefbfbd475e3a6eefbfbdefbfbdefbfbdefbfbd3b33192830efbfbd6aefbfbd3ec99defbfbd5f1defbfbdefbfbd200cefbfbd20efbfbd79efbfbdefbfbd7e7fefbfbd7b77efbfbd761befbfbd19efbfbd561a7d006e711cefbfbdefbfbdefbfbdefbfbd5b2d0befbfbd476b7c51722d38efbfbdefbfbd16efbfbd3e48303b794019efbfbd40efbfbdc69d6aefbfbd4b6118efbfbd2defbfbddaa21c2c2a5cd0b8efbfbdefbfbd292400efbfbd74efbfbd4aefbfbd43efbfbdefbfbd78efbfbdefbfbddb964a5aefbfbd0aefbfbd1befbfbd3a3d32efbfbdefbfbdefbfbdefbfbd7b3eefbfbd01efbfbd38efbfbd3cefbfbd19efbfbd5cefbfbd7defbfbdefbfbdceadefbfbd775befbfbdefbfbddda0efbfbd2eefbfbddc9225efbfbd121aefbfbd43efbfbd39efbfbd4e342410efbfbdefbfbdefbfbd24d992efbfbdefbfbd09efbfbdefbfbdefbfbd644f75efbfbdefbfbdefbfbdefbfbd051e69e287b11fefbfbd2d2134efbfbd2849efbfbdefbfbd0c1d665c5befbfbd602aefbfbd5906efbfbd2aefbfbdefbfbd4f5defbfbd49efbfbd0befbfbd7003efbfbdefbfbdefbfbd1fefbfbdefbfbd2a25713c54efbfbd08efbfbdd3a236efbfbd20efbfbdefbfbd76efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdd78befbfbdefbfbdefbfbdefbfbd07efbfbdefbfbd3eefbfbd4730efbfbd3defbfbdefbfbd5c16efbfbdefbfbdefbfbdefbfbd78efbfbd7b5cefbfbd33187950efbfbdefbfbdeea0b26eefbfbdefbfbdefbfbd4f1f77655defbfbdefbfbd4aefbfbd3addb25d013ed7b5efbfbd1b6fefbfbdefbfbdefbfbd3cefbfbdefbfbd47311c44efbfbd6bdfbe4befbfbdefbfbdefbfbd64efbfbd0cefbfbd0c2befbfbdefbfbd58efbfbdefbfbd46efbfbdefbfbdefbfbd761079efbfbd786eefbfbdefbfbdefbfbdefbfbd6835efbfbdefbfbd7b07efbfbd5c5aefbfbd4aefbfbd5defbfbd4b3271241e26efbfbd2befbfbd0befbfbd4b0562efbfbd767fefbfbdefbfbdefbfbd43efbfbd3522efbfbd61efbfbd75efbfbdefbfbd3a2c6e317cefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7f504b0708efbfbd31efbfbd36efbfbd020000efbfbd050000504b0304140008080800efbfbd60efbfbd420000000000000000000000000c0000006d616e69666573742e726466cd93efbfbd6eefbfbd301044efbfbd7cefbfbd65efbfbd60efbfbdefbfbdefbfbd023914efbfbd5cefbfbd5fefbfbd1aefbfbd58052fefbfbd2e25efbfbd7d5d27efbfbdefbfbd1cefbfbd2acda1efbfbd5defbfbd66efbfbd68efbfbdefbfbdefbfbd380eefbfbd433b34606befbfbdefbfbd1967efbfbd2aefbfbdefbfbdefbfbd7cefbfbd3e79efbfbdefbfbd26dab8efbfbdefbfbd5eefbfbd1defbfbd6aefbfbdefbfbdefbfbd6a7e20efbfbd2a21efbfbd6549efbfbdefbfbd14efbfbd5eefbfbd6559efbfbdefbfbd1045efbfbd7845efbfbdefbfbd25794c2cc6bcefbfbd180b1eefbfbd46efbfbdefbfbd443eefbfbd7defbfbdefbfbd0d66efbfbd7910efbfbd25efbfbd4e3aefbfbd39efbfbd303befbfbdefbfbdefbfbd3a50efbfbdefbfbd44efbfbd094cda864cefbfbd02efbfbdefbfbd282defbfbd10efbfbd2629efbfbdefbfbd7ddc82efbfbd476defbfbdefbfbd102defbfbdefbfbd7fefbfbd6331efbfbd0e12efbfbdefbfbdefbfbd7375efbfbdefbfbdefbfbdefbfbd5f355260efbfbdefbfbdefbfbdefbfbdefbfbd2222efbfbdefbfbdefbfbd3f105e76efbfbdefbfbd7defbfbdefbfbde3a793efbfbdefbfbd0cefbfbd46efbfbdefbfbd7aefbfbdefbfbd7b0defbfbd3fefbfbdefbfbd56efbfbd4735efbfbd27504b0708efbfbd3defbfbdefbfbd00010000efbfbd030000504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f706f7075706d656e752f504b0304140000080000efbfbd60efbfbd420000000000000000000000001f000000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b0304140000080000efbfbd60efbfbd420000000000000000000000001c000000436f6e66696775726174696f6e73322f70726f67726573736261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f746f6f6c6261722f504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f666c6f617465722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000018000000436f6e66696775726174696f6e73322f6d656e756261722f504b0304140000080000efbfbd60efbfbd4200000000000000000000000027000000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b0304140000080000efbfbd60efbfbd420000000000000000000000001a000000436f6e66696775726174696f6e73322f7374617475736261722f504b0304140008080800efbfbd60efbfbd42000000000000000000000000150000004d4554412d494e462f6d616e69666573742e786d6cefbfbd54efbfbd6eefbfbd2010efbfbdefbfbd2b2cefbfbdefbfbdefbfbdcda9427172efbfbdefbfbd2f483fefbfbde2b583040befbfbd25efbfbdefbfbdefbfbd386a1e55efbfbd2a567defbfbdefbfbdefbfbdefbfbd081636efbfbdefbfbdefbfbdefbfbd1162321e1befbfbdc29f5905efbfbd7d6befbfbd6fefbfbdefbfbdefbfbdefbfbd7e65efbfbdefbfbd6aefbfbd14efbfbd0e12efbfbd4b50efbfbd394cd7b46139efbfbdefbfbd2aefbfbd24513948efbfbdefbfbdefbfbd01efbfbdefbfbd3a3b40efbfbd3fefbfbdefbfbd74efbfbdefbfbd0cefbfbdefbfbd7655efbfbdefbfbd3a63efbfbd2eefbfbd71efbfbdefbfbdefbfbd6c6d1d141d1a26efbfbd486e6507efbfbd51350d011aefbfbd42efbfbd462b2a3071c496efbfbd0defbfbd7befbfbdefbfbdefbfbd444cefbfbdefbfbd3f64efbfbdefbfbdefbfbdefbfbd24efbfbd12efbfbdefbfbdefbfbd07efbfbd540f62efbfbdefbfbd52efbfbd1e69efbfbd57efbfbd71efbfbd78742eefbfbdefbfbd2cefbfbd44efbfbdefbfbdefbfbd3c2d10efbfbd1d5aefbfbdefbfbd01efbfbdefbfbd49efbfbd6b3cefbfbdefbfbd03efbfbd53504fefbfbd35efbfbd3c76efbfbdefbfbdefbfbd4cefbfbdefbfbd42690d164aefbfbdefbfbd39c6bf2fefbfbd7f5a0f3eefbfbdefbfbd71efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6114df885f7fefbfbdefbfbd0b504b0708efbfbd5cefbfbd4a1a0100003e040000504b01021400140000080000efbfbd60efbfbd425eefbfbd320c27000000270000000800000000000000000000000000000000006d696d6574797065504b01021400140000080000efbfbd60efbfbd422cefbfbdefbfbd53efbfbd210000efbfbd21000018000000000000000000000000004d0000005468756d626e61696c732f7468756d626e61696c2e706e67504b01021400140008080800efbfbd60efbfbd4260efbfbdefbfbd01750a0000efbfbd6300000b000000000000000000000000006d220000636f6e74656e742e786d6c504b01021400140008080800efbfbd60efbfbd4236efbfbdefbfbd4d2913000005efbfbd00000a000000000000000000000000001b2d00007374796c65732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbdefbfbd08efbfbd7b080000efbfbd2b00000c000000000000000000000000007c40000073657474696e67732e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd31efbfbd36efbfbd020000efbfbd0500000800000000000000000000000000314900006d6574612e786d6c504b01021400140008080800efbfbd60efbfbd42efbfbd3defbfbdefbfbd00010000efbfbd0300000c00000000000000000000000000efbfbd4b00006d616e69666573742e726466504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000254d0000436f6e66696775726174696f6e73322f706f7075706d656e752f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001f000000000000000000000000005d4d0000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001c00000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f70726f67726573736261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000efbfbd4d0000436f6e66696775726174696f6e73322f746f6f6c6261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a000000000000000000000000000a4e0000436f6e66696775726174696f6e73322f746f6f6c70616e656c2f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000424e0000436f6e66696775726174696f6e73322f666c6f617465722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000001800000000000000000000000000784e0000436f6e66696775726174696f6e73322f6d656e756261722f504b01021400140000080000efbfbd60efbfbd420000000000000000000000002700000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b01021400140000080000efbfbd60efbfbd420000000000000000000000001a00000000000000000000000000efbfbd4e0000436f6e66696775726174696f6e73322f7374617475736261722f504b01021400140008080800efbfbd60efbfbd42efbfbd5cefbfbd4a1a0100003e04000015000000000000000000000000002b4f00004d4554412d494e462f6d616e69666573742e786d6c504b0506000000001100110070040000efbfbd5000000000, 'odt');

-- --------------------------------------------------------

--
-- Table structure for table `bs_expenses`
--

CREATE TABLE `bs_expenses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `expense_book_id` int(11) NOT NULL DEFAULT 0,
  `category_id` int(11) NOT NULL DEFAULT 0,
  `supplier` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_no` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `btime` int(11) DEFAULT 0,
  `ptime` int(11) DEFAULT NULL,
  `subtotal` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `invoice_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_expenses`
--

INSERT INTO `bs_expenses` (`id`, `user_id`, `expense_book_id`, `category_id`, `supplier`, `invoice_no`, `ctime`, `mtime`, `btime`, `ptime`, `subtotal`, `vat`, `invoice_id`) VALUES
(1, 1, 1, 0, '', '0', 1571047599, 1571047664, 1571004000, 1571004000, 57851.24, 12148.76, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bs_expense_books`
--

CREATE TABLE `bs_expense_books` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_expense_books`
--

INSERT INTO `bs_expense_books` (`id`, `user_id`, `acl_id`, `name`, `currency`, `vat`) VALUES
(1, 1, 134, 'Expesnse', '€', 21);

-- --------------------------------------------------------

--
-- Table structure for table `bs_expense_categories`
--

CREATE TABLE `bs_expense_categories` (
  `id` int(11) NOT NULL,
  `expense_book_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_expense_categories`
--

INSERT INTO `bs_expense_categories` (`id`, `expense_book_id`, `name`) VALUES
(1, 1, 'Internet');

-- --------------------------------------------------------

--
-- Table structure for table `bs_items`
--

CREATE TABLE `bs_items` (
  `id` int(11) NOT NULL,
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
  `extra_cost_status_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bs_items`
--

INSERT INTO `bs_items` (`id`, `order_id`, `product_id`, `description`, `unit_cost`, `unit_price`, `unit_list`, `unit_total`, `amount`, `vat`, `vat_code`, `discount`, `sort_order`, `cost_code`, `tracking_code`, `markup`, `order_at_supplier`, `order_at_supplier_company_id`, `amount_delivered`, `note`, `unit`, `item_group_id`, `extra_cost_status_id`) VALUES
(1, 1, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(2, 1, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 4, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(3, 2, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(4, 2, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 10, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(5, 3, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(6, 3, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 4, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(7, 4, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(8, 4, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 10, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(9, 5, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(10, 5, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 4, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(11, 6, 2, 'Rocket Launcher 1000. Required to launch rockets.', 3000, 8999.99, 8999.99, 8999.99, 1, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0),
(12, 6, 1, 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', 1000, 2999.99, 2999.99, 2999.99, 10, 0, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, NULL, '', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bs_item_product_option`
--

CREATE TABLE `bs_item_product_option` (
  `item_id` int(11) NOT NULL,
  `product_option_value_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_languages`
--

CREATE TABLE `bs_languages` (
  `id` int(11) NOT NULL,
  `language` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_languages`
--

INSERT INTO `bs_languages` (`id`, `language`, `name`) VALUES
(1, 'en', 'Default');

-- --------------------------------------------------------

--
-- Table structure for table `bs_numbers`
--

CREATE TABLE `bs_numbers` (
  `book_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `next_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_orders`
--

CREATE TABLE `bs_orders` (
  `id` int(11) NOT NULL,
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
  `extra_costs` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_orders`
--

INSERT INTO `bs_orders` (`id`, `project_id`, `status_id`, `book_id`, `language_id`, `user_id`, `order_id`, `po_id`, `company_id`, `contact_id`, `ctime`, `mtime`, `muser_id`, `btime`, `ptime`, `costs`, `subtotal`, `vat`, `total`, `authcode`, `frontpage_text`, `customer_name`, `customer_to`, `customer_salutation`, `customer_contact_name`, `customer_address`, `customer_address_no`, `customer_zip`, `customer_city`, `customer_state`, `customer_country`, `customer_vat_no`, `customer_crn`, `customer_email`, `customer_extra`, `webshop_id`, `recur_type`, `payment_method`, `recurred_order_id`, `reference`, `order_bonus_points`, `pagebreak`, `files_folder_id`, `cost_code`, `for_warehouse`, `dtime`, `total_paid`, `due_date`, `other_shipping_address`, `shipping_to`, `shipping_salutation`, `shipping_address`, `shipping_address_no`, `shipping_zip`, `shipping_city`, `shipping_state`, `shipping_country`, `shipping_extra`, `telesales_agent`, `fieldsales_agent`, `extra_costs`) VALUES
(1, 0, 1, 1, 1, 1, 'Q19000001', '', 12, 1, 1561972062, 1561972062, 1, 1561972062, 1561972062, 7000, 20999.95, 0, 20999.95, NULL, '', 'Smith Inc', 'Smith Inc', 'Dear Mr / Ms', NULL, 'Kalverstraat', '1', '1012 NX', 'Amsterdam', NULL, 'NL', 'NL 1234.56.789.B01', '', 'info@smith.demo', '', 0, '', '', 0, '', NULL, 0, 0, NULL, 0, 0, 20999.95, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0),
(2, 0, 1, 1, 1, 1, 'Q19000002', '', 13, 2, 1561972062, 1561972063, 1, 1561972062, 1561972063, 13000, 38999.89, 0, 38999.89, NULL, '', 'ACME Corporation', 'ACME Corporation', 'Dear Mr / Ms', NULL, '1111 Broadway', '', '10019', 'New York', NULL, 'US', 'US 1234.56.789.B01', '', 'info@acme.demo', '', 0, '', '', 0, '', NULL, 0, 0, NULL, 0, 0, 38999.89, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0),
(3, 0, 5, 2, 1, 1, 'O19000001', '', 12, 1, 1561972063, 1561972063, 1, 1561972063, 1561972063, 7000, 20999.95, 0, 20999.95, NULL, '', 'Smith Inc', 'Smith Inc', 'Dear Mr / Ms', NULL, 'Kalverstraat', '1', '1012 NX', 'Amsterdam', NULL, 'NL', 'NL 1234.56.789.B01', '', 'info@smith.demo', '', 0, '', '', 0, '', NULL, 0, 0, NULL, 0, 0, 20999.95, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0),
(4, 0, 5, 2, 1, 1, 'O19000002', '', 13, 2, 1561972063, 1561972064, 1, 1561972063, 1561972064, 13000, 38999.89, 0, 38999.89, NULL, '', 'ACME Corporation', 'ACME Corporation', 'Dear Mr / Ms', NULL, '1111 Broadway', '', '10019', 'New York', NULL, 'US', 'US 1234.56.789.B01', '', 'info@acme.demo', '', 0, '', '', 0, '', NULL, 0, 0, NULL, 0, 0, 38999.89, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0),
(5, 0, 9, 3, 1, 1, 'I19000001', '', 12, 1, 1561972064, 1562249995, 1, 1561932000, 1561932000, 7000, 20999.95, 0, 20999.95, NULL, '', 'Smith Inc', 'Smith Inc', 'Dear Mr / Ms', '', 'Kalverstraat', '1', '1012 NX', 'Amsterdam', '', 'NL', 'NL 1234.56.789.B01', '', 'info@smith.demo', '', 0, '', '', 0, '', NULL, 0, 0, '', 0, 0, 20999.95, 1562191200, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0),
(6, 0, 9, 3, 1, 1, 'I19000002', '', 13, 2, 1561972064, 1561972064, 1, 1561972064, 1561972064, 13000, 38999.89, 0, 38999.89, NULL, '', 'ACME Corporation', 'ACME Corporation', 'Dear Mr / Ms', NULL, '1111 Broadway', '', '10019', 'New York', NULL, 'US', 'US 1234.56.789.B01', '', 'info@acme.demo', '', 0, '', '', 0, '', NULL, 0, 0, NULL, 0, 0, 38999.89, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bs_orders_custom_fields`
--

CREATE TABLE `bs_orders_custom_fields` (
  `id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_orders_custom_fields`
--

INSERT INTO `bs_orders_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, '');

-- --------------------------------------------------------

--
-- Table structure for table `bs_order_item_groups`
--

CREATE TABLE `bs_order_item_groups` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Item Group',
  `summarize` tinyint(1) NOT NULL DEFAULT 0,
  `show_individual_prices` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `bs_order_payments`
--

CREATE TABLE `bs_order_payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `date` int(11) NOT NULL,
  `amount` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_order_payments`
--

INSERT INTO `bs_order_payments` (`id`, `order_id`, `date`, `amount`, `description`) VALUES
(1, 1, 1561972062, 20999.95, 'Status: Sent'),
(2, 2, 1561972063, 38999.89, 'Status: Sent'),
(3, 3, 1561972063, 20999.95, 'Status: Waiting for payment'),
(4, 4, 1561972064, 38999.89, 'Status: Waiting for payment'),
(5, 5, 1561972064, 20999.95, 'Status: Waiting for payment'),
(6, 6, 1561972064, 38999.89, 'Status: Waiting for payment');

-- --------------------------------------------------------

--
-- Table structure for table `bs_order_statuses`
--

CREATE TABLE `bs_order_statuses` (
  `id` int(11) NOT NULL,
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
  `ask_to_notify_customer` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_order_statuses`
--

INSERT INTO `bs_order_statuses` (`id`, `book_id`, `max_age`, `payment_required`, `remove_from_stock`, `read_only`, `color`, `required_status_id`, `acl_id`, `apply_extra_cost`, `extra_cost_min_value`, `extra_cost_percentage`, `email_bcc`, `email_owner`, `ask_to_notify_customer`) VALUES
(1, 1, 0, 0, 0, 0, 'FFFFFF', 0, 23, 0, NULL, NULL, '', 0, 1),
(2, 1, 0, 0, 0, 0, 'FFFFFF', 0, 24, 0, NULL, NULL, '', 0, 1),
(3, 1, 0, 0, 0, 0, 'FFFFFF', 0, 25, 0, NULL, NULL, '', 0, 1),
(4, 1, 0, 0, 0, 0, 'FFFFFF', 0, 26, 0, NULL, NULL, '', 0, 1),
(5, 2, 0, 0, 0, 0, 'FFFFFF', 0, 28, 0, NULL, NULL, '', 0, 1),
(6, 2, 0, 0, 0, 0, 'FFFFFF', 0, 29, 0, NULL, NULL, '', 0, 1),
(7, 2, 0, 0, 0, 0, 'FFFFFF', 0, 30, 0, NULL, NULL, '', 0, 1),
(8, 2, 0, 0, 0, 0, 'FFFFFF', 0, 31, 0, NULL, NULL, '', 0, 1),
(9, 3, 0, 0, 0, 0, 'FFFFFF', 0, 33, 0, NULL, NULL, '', 0, 1),
(10, 3, 0, 0, 0, 0, 'FFFFFF', 0, 34, 0, NULL, NULL, '', 0, 1),
(11, 3, 0, 0, 0, 0, 'FFFFFF', 0, 35, 0, NULL, NULL, '', 0, 1),
(12, 3, 0, 0, 0, 0, 'FFFFFF', 0, 36, 0, NULL, NULL, '', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `bs_order_status_history`
--

CREATE TABLE `bs_order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `notified` tinyint(1) NOT NULL DEFAULT 0,
  `notification_email` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bs_order_status_history`
--

INSERT INTO `bs_order_status_history` (`id`, `order_id`, `status_id`, `user_id`, `ctime`, `notified`, `notification_email`, `comments`) VALUES
(1, 1, 1, 1, 1561972062, 0, 'billing/notifications/1/201907/1/1561972062.eml', NULL),
(2, 2, 1, 1, 1561972063, 0, 'billing/notifications/1/201907/2/1561972063.eml', NULL),
(3, 3, 5, 1, 1561972063, 0, 'billing/notifications/2/201907/3/1561972063.eml', NULL),
(4, 4, 5, 1, 1561972064, 0, 'billing/notifications/2/201907/4/1561972064.eml', NULL),
(5, 5, 9, 1, 1561972064, 0, 'billing/notifications/3/201907/5/1561972064.eml', NULL),
(6, 6, 9, 1, 1561972064, 0, 'billing/notifications/3/201907/6/1561972064.eml', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bs_products`
--

CREATE TABLE `bs_products` (
  `id` int(11) NOT NULL,
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
  `tracking_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_products`
--

INSERT INTO `bs_products` (`id`, `sort_order`, `category_id`, `image`, `cost_price`, `list_price`, `vat`, `total_price`, `supplier_company_id`, `supplier_product_id`, `stock`, `required_products`, `stock_min`, `article_id`, `unit`, `unit_stock`, `files_folder_id`, `cost_code`, `tracking_code`) VALUES
(1, 0, 0, '', 1000, 2999.99, 0, 2999.99, 13, NULL, 0, '', 0, '12345', 'pcs', '', 0, NULL, NULL),
(2, 0, 0, '', 3000, 8999.99, 0, 8999.99, 13, NULL, 0, '', 0, '234567', 'pcs', '', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bs_products_custom_fields`
--

CREATE TABLE `bs_products_custom_fields` (
  `id` int(11) NOT NULL DEFAULT 0,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_products_custom_fields`
--

INSERT INTO `bs_products_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, '');

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_categories`
--

CREATE TABLE `bs_product_categories` (
  `id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_languages`
--

CREATE TABLE `bs_product_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_product_languages`
--

INSERT INTO `bs_product_languages` (`language_id`, `product_id`, `name`, `description`, `short_description`) VALUES
(1, 1, 'Master Rocket 1000', 'Master Rocket 1000. The ultimate rocket to blast rocky mountains.', ''),
(1, 2, 'Rocket Launcher 1000', 'Rocket Launcher 1000. Required to launch rockets.', '');

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_option`
--

CREATE TABLE `bs_product_option` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `type` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_option_language`
--

CREATE TABLE `bs_product_option_language` (
  `product_option_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_option_value`
--

CREATE TABLE `bs_product_option_value` (
  `id` int(11) NOT NULL,
  `product_option_id` int(11) NOT NULL,
  `value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_product_option_value_language`
--

CREATE TABLE `bs_product_option_value_language` (
  `product_option_value_id` int(11) NOT NULL,
  `language_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_status_languages`
--

CREATE TABLE `bs_status_languages` (
  `language_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extra_cost_item_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_subject` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_template` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `screen_template` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_template_id` int(11) NOT NULL DEFAULT 0,
  `doc_template_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_status_languages`
--

INSERT INTO `bs_status_languages` (`language_id`, `status_id`, `name`, `extra_cost_item_text`, `email_subject`, `email_template`, `screen_template`, `pdf_template_id`, `doc_template_id`) VALUES
(1, 1, 'Sent', NULL, 'Your Invoice has status Sent', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 1, 1),
(1, 2, 'Accepted', NULL, 'Your Invoice has status Accepted', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Accepted.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 1, 1),
(1, 3, 'Lost', NULL, 'Your Invoice has status Lost', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Lost.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 1, 1),
(1, 4, 'In process', NULL, 'Your Invoice has status In process', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status In process.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 1, 1),
(1, 5, 'Waiting for payment', NULL, 'Your Invoice has status Waiting for payment', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Waiting for payment.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 2, 2),
(1, 6, 'Reminder sent', NULL, 'Your Invoice has status Reminder sent', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Reminder sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 2, 2),
(1, 7, 'Paid', NULL, 'Your Invoice has status Paid', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Paid.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 2, 2),
(1, 8, 'Credit', NULL, 'Your Invoice has status Credit', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Credit.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 2, 2),
(1, 9, 'Waiting for payment', NULL, 'Your Invoice has status Waiting for payment', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Waiting for payment.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 3, 3),
(1, 10, 'Reminder sent', NULL, 'Your Invoice has status Reminder sent', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Reminder sent.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 3, 3),
(1, 11, 'Paid', NULL, 'Your Invoice has status Paid', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Paid.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 3, 3),
(1, 12, 'Credit', NULL, 'Your Invoice has status Credit', '%customer_salutation%,<br />\n<br />\nYour Invoice is in status Credit.<br />\n<br />\nWith kind regards,<br />\n<br />\nSome company;', NULL, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `bs_tax_rates`
--

CREATE TABLE `bs_tax_rates` (
  `id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bs_templates`
--

CREATE TABLE `bs_templates` (
  `id` int(11) NOT NULL,
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
  `show_cost_code` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bs_templates`
--

INSERT INTO `bs_templates` (`id`, `name`, `title`, `right_col`, `left_col`, `margin_top`, `margin_bottom`, `margin_left`, `margin_right`, `page_format`, `stationery_paper`, `footer`, `closing`, `number_name`, `reference_name`, `date_name`, `logo`, `logo_width`, `logo_height`, `show_supplier_product_id`, `show_nett_total_price`, `show_nett_unit_price`, `show_summary_totals`, `show_vat`, `show_units`, `book_id`, `logo_top`, `logo_left`, `left_col_top`, `left_col_left`, `right_col_top`, `right_col_left`, `show_amounts`, `logo_only_first_page`, `use_html_table`, `html_table`, `repeat_header`, `show_gross_unit_price`, `show_unit_cost`, `show_gross_total_price`, `show_date_sent`, `show_page_numbers`, `show_total_paid`, `show_reference`, `show_product_number`, `show_item_id`, `show_cost_code`) VALUES
(1, 'Quotes', NULL, 'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo', '{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}', 30, 30, 30, 30, NULL, NULL, 'Footer text', '<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>', 'Invoice no.', 'Reference', 'Invoice date', NULL, 0, 0, 0, 1, 1, 1, 1, 0, 1, 0, 0, 30, 30, 30, 365, 1, 0, 0, NULL, 0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0),
(2, 'Orders', NULL, 'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo', '{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}', 30, 30, 30, 30, NULL, NULL, 'Footer text', '<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>', 'Invoice no.', 'Reference', 'Invoice date', NULL, 0, 0, 0, 1, 1, 1, 1, 0, 2, 0, 0, 30, 30, 30, 365, 1, 0, 0, NULL, 0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0),
(3, 'Invoices', '', 'Example Company\n1111 Broadway\n10900 NY\n\ntel. (555) 1234567\nfax. (555) 1234567\nemail: info@example.demo\nurl: http://www.example.demo', '{customer_to}\n{formatted_address}\n{customer_vat_no}\n{customer_extra}\n\n{order_data}\n\nDue: {due_date}', 30, 30, 30, 30, '', '', 'Footer text', '<gotpl if=\"fully_paid\">Thanks, the incoice has been paid.</gotpl>\n<gotpl if=\"partially_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is not fully paid. The outstanding amount is {to_be_paid}.\n</gotpl>\n<gotpl if=\"nothing_paid\">\nWhile checking our financial records, It occurred to me the attached invoice addressed to you is still not paid.\n</gotpl>', 'Invoice no.', 'Reference', 'Invoice date', NULL, 0, 0, 0, 1, 1, 1, 1, 0, 3, 0, 0, 30, 30, 30, 365, 1, 0, 0, NULL, 0, 1, 0, 1, 0, 0, 0, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bs_tracking_codes`
--

CREATE TABLE `bs_tracking_codes` (
  `id` int(11) NOT NULL,
  `costcode_id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_calendars`
--

CREATE TABLE `cal_calendars` (
  `id` int(11) NOT NULL,
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
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_calendars`
--

INSERT INTO `cal_calendars` (`id`, `group_id`, `user_id`, `acl_id`, `name`, `start_hour`, `end_hour`, `background`, `time_interval`, `public`, `shared_acl`, `show_bdays`, `show_completed_tasks`, `comment`, `project_id`, `tasklist_id`, `files_folder_id`, `show_holidays`, `enable_ics_import`, `ics_import_url`, `tooltip`, `version`) VALUES
(1, 1, 1, 70, 'System Administrator', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 10, 1, 0, '', '', 4),
(2, 1, 2, 90, 'Elmer Fudd', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 22, 1, 0, '', '', 4),
(3, 1, 3, 95, 'Demo User', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 27, 1, 0, '', '', 10),
(4, 1, 4, 100, 'Linda Smith', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 31, 1, 0, '', '', 10),
(5, 2, 1, 104, 'Road Runner Room', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 33, 1, 0, '', '', 1),
(6, 2, 1, 105, 'Don Coyote Room', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 34, 1, 0, '', '', 1),
(8, 1, 6, 129, 'foo', 0, 0, NULL, 1800, 0, 0, 0, 1, '', 0, 0, 42, 1, 0, '', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cal_calendars_custom_fields`
--

CREATE TABLE `cal_calendars_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_calendars_custom_fields`
--

INSERT INTO `cal_calendars_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, ''),
(8, '');

-- --------------------------------------------------------

--
-- Table structure for table `cal_calendar_user_colors`
--

CREATE TABLE `cal_calendar_user_colors` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_categories`
--

CREATE TABLE `cal_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_events`
--

CREATE TABLE `cal_events` (
  `id` int(11) NOT NULL,
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
  `is_organizer` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_events`
--

INSERT INTO `cal_events` (`id`, `uuid`, `calendar_id`, `user_id`, `start_time`, `end_time`, `timezone`, `all_day_event`, `name`, `description`, `location`, `repeat_end_time`, `reminder`, `ctime`, `mtime`, `muser_id`, `busy`, `status`, `resource_event_id`, `private`, `rrule`, `background`, `files_folder_id`, `read_only`, `category_id`, `exception_for_event_id`, `recurrence_id`, `is_organizer`) VALUES
(1, '08daba0e-9ef3-59c1-933a-03ca01a8ad22', 3, 3, 1562054400, 1562058000, 'Europe/Amsterdam', 0, 'Project meeting', NULL, 'ACME NY Office', 0, NULL, 1561972058, 1561972058, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(2, '08daba0e-9ef3-59c1-933a-03ca01a8ad22', 4, 4, 1562054400, 1562058000, 'Europe/Amsterdam', 0, 'Project meeting', NULL, 'ACME NY Office', 0, NULL, 1561972058, 1561972058, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(3, '08daba0e-9ef3-59c1-933a-03ca01a8ad22', 2, 2, 1562054400, 1562058000, 'Europe/Amsterdam', 0, 'Project meeting', NULL, 'ACME NY Office', 0, NULL, 1561972058, 1561972058, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(4, '6c4dbd0f-a214-59e2-851a-aa287656176e', 3, 3, 1562061600, 1562065200, 'Europe/Amsterdam', 0, 'Meet Wile', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(5, '6c4dbd0f-a214-59e2-851a-aa287656176e', 4, 4, 1562061600, 1562065200, 'Europe/Amsterdam', 0, 'Meet Wile', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(6, '6c4dbd0f-a214-59e2-851a-aa287656176e', 2, 2, 1562061600, 1562065200, 'Europe/Amsterdam', 0, 'Meet Wile', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(7, '4d87f73c-7bf0-5c90-b450-2689c963888e', 3, 3, 1562068800, 1562072400, 'Europe/Amsterdam', 0, 'MT Meeting', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(8, '4d87f73c-7bf0-5c90-b450-2689c963888e', 4, 4, 1562068800, 1562072400, 'Europe/Amsterdam', 0, 'MT Meeting', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(9, '4d87f73c-7bf0-5c90-b450-2689c963888e', 2, 2, 1562068800, 1562072400, 'Europe/Amsterdam', 0, 'MT Meeting', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(10, 'ccc9622a-e00d-5643-99f2-7c2dc88494b1', 4, 4, 1562144400, 1562148000, 'Europe/Amsterdam', 0, 'Project meeting', NULL, 'ACME NY Office', 0, NULL, 1561972059, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(11, 'ccc9622a-e00d-5643-99f2-7c2dc88494b1', 3, 3, 1562144400, 1562148000, 'Europe/Amsterdam', 0, 'Project meeting', NULL, 'ACME NY Office', 0, NULL, 1561972060, 1561972059, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(12, '71d09bf8-00e9-5c29-9889-b4db5de35525', 4, 4, 1562151600, 1562155200, 'Europe/Amsterdam', 0, 'Meet John', NULL, 'ACME NY Office', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(13, '71d09bf8-00e9-5c29-9889-b4db5de35525', 3, 3, 1562151600, 1562155200, 'Europe/Amsterdam', 0, 'Meet John', NULL, 'ACME NY Office', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(14, '379d9f35-23dd-5c09-8da6-2c1ae967386c', 4, 4, 1562162400, 1562166000, 'Europe/Amsterdam', 0, 'MT Meeting', NULL, 'ACME NY Office', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(15, '379d9f35-23dd-5c09-8da6-2c1ae967386c', 3, 3, 1562162400, 1562166000, 'Europe/Amsterdam', 0, 'MT Meeting', NULL, 'ACME NY Office', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(16, '20ef5123-708b-590e-9d95-6c963b0accb4', 4, 4, 1562047200, 1562050800, 'Europe/Amsterdam', 0, 'Rocket testing', NULL, 'ACME Testing fields', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(17, '20ef5123-708b-590e-9d95-6c963b0accb4', 3, 3, 1562047200, 1562050800, 'Europe/Amsterdam', 0, 'Rocket testing', NULL, 'ACME Testing fields', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(18, 'f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa', 4, 4, 1562072400, 1562076000, 'Europe/Amsterdam', 0, 'Blast impact test', NULL, 'ACME Testing fields', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(19, 'f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa', 3, 3, 1562072400, 1562076000, 'Europe/Amsterdam', 0, 'Blast impact test', NULL, 'ACME Testing fields', 0, NULL, 1561972060, 1561972060, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(20, '0212980f-bfec-55a8-8fba-b3232a097705', 4, 4, 1562086800, 1562090400, 'Europe/Amsterdam', 0, 'Test range extender', NULL, 'ACME Testing fields', 0, NULL, 1561972061, 1561972061, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1),
(21, '0212980f-bfec-55a8-8fba-b3232a097705', 3, 3, 1562086800, 1562090400, 'Europe/Amsterdam', 0, 'Test range extender', NULL, 'ACME Testing fields', 0, NULL, 1561972061, 1561972061, 1, 1, 'CONFIRMED', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 0),
(22, 'd4153d3b-0988-59af-b3f9-d2129564ef76', 1, 1, 1572537600, 1572541200, 'Europe/Amsterdam', 0, 'mnbmhb', '', '', 0, NULL, 1572537446, 1572610878, 1, 1, 'NEEDS-ACTION', 0, 0, '', 'EBF1E2', 0, 0, NULL, 0, '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cal_events_custom_fields`
--

CREATE TABLE `cal_events_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_events_custom_fields`
--

INSERT INTO `cal_events_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, ''),
(7, ''),
(8, ''),
(9, ''),
(10, ''),
(11, ''),
(12, ''),
(13, ''),
(14, ''),
(15, ''),
(16, ''),
(17, ''),
(18, ''),
(19, ''),
(20, ''),
(21, ''),
(22, '');

-- --------------------------------------------------------

--
-- Table structure for table `cal_events_declined`
--

CREATE TABLE `cal_events_declined` (
  `uid` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_exceptions`
--

CREATE TABLE `cal_exceptions` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL DEFAULT 0,
  `time` int(11) NOT NULL DEFAULT 0,
  `exception_event_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `muser_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_groups`
--

CREATE TABLE `cal_groups` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fields` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `show_not_as_busy` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_groups`
--

INSERT INTO `cal_groups` (`id`, `user_id`, `name`, `fields`, `show_not_as_busy`) VALUES
(1, 1, 'Calendars', '', 0),
(2, 1, 'Meeting rooms', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `cal_group_admins`
--

CREATE TABLE `cal_group_admins` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_group_admins`
--

INSERT INTO `cal_group_admins` (`group_id`, `user_id`) VALUES
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `cal_participants`
--

CREATE TABLE `cal_participants` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NEEDS-ACTION',
  `last_modified` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `is_organizer` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_participants`
--

INSERT INTO `cal_participants` (`id`, `event_id`, `name`, `email`, `user_id`, `contact_id`, `status`, `last_modified`, `is_organizer`, `role`) VALUES
(1, 1, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(2, 1, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(3, 2, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(4, 2, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(5, 1, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(6, 3, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(7, 3, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(8, 3, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(9, 2, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(10, 1, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(11, 2, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(12, 3, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(13, 4, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(14, 4, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(15, 5, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(16, 5, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(17, 4, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(18, 6, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(19, 6, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(20, 6, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(21, 5, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(22, 4, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(23, 5, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(24, 6, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(25, 7, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(26, 7, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(27, 8, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(28, 8, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(29, 7, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(30, 9, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(31, 9, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'ACCEPTED', '', 1, ''),
(32, 9, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'NEEDS-ACTION', '', 0, ''),
(33, 8, 'Elmer Fudd', 'elmer@acmerpp.demo', 2, 4, 'NEEDS-ACTION', '', 0, ''),
(34, 7, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(35, 8, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(36, 9, 'Wile E. Coyote', 'wile@acme.demo', 0, 2, 'NEEDS-ACTION', '', 0, ''),
(37, 10, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(38, 10, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(39, 11, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(40, 11, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(41, 10, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(42, 11, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(43, 12, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(44, 12, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(45, 13, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(46, 13, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(47, 12, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(48, 13, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(49, 14, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(50, 14, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(51, 15, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(52, 15, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(53, 14, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(54, 15, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(55, 16, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(56, 16, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(57, 17, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(58, 17, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(59, 16, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(60, 17, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(61, 18, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(62, 18, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(63, 19, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(64, 19, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(65, 18, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(66, 19, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(67, 20, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(68, 20, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(69, 21, 'Demo User', 'demo@acmerpp.demo', 3, 5, 'NEEDS-ACTION', '', 0, ''),
(70, 21, 'Linda Smith', 'linda@acmerpp.demo', 4, 6, 'ACCEPTED', '', 1, ''),
(71, 20, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, ''),
(72, 21, 'John Smith', 'john@smith.demo', 0, 1, 'NEEDS-ACTION', '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `cal_settings`
--

CREATE TABLE `cal_settings` (
  `user_id` int(11) NOT NULL,
  `reminder` int(11) DEFAULT NULL,
  `background` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `show_statuses` tinyint(1) NOT NULL DEFAULT 1,
  `check_conflict` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_settings`
--

INSERT INTO `cal_settings` (`user_id`, `reminder`, `background`, `calendar_id`, `show_statuses`, `check_conflict`) VALUES
(1, NULL, 'EBF1E2', 1, 1, 1),
(2, NULL, 'EBF1E2', 2, 1, 1),
(3, NULL, 'EBF1E2', 3, 1, 1),
(4, NULL, 'EBF1E2', 4, 1, 1),
(6, NULL, 'EBF1E2', 8, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cal_views`
--

CREATE TABLE `cal_views` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT 1800,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `merge` tinyint(1) NOT NULL DEFAULT 0,
  `owncolor` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_views`
--

INSERT INTO `cal_views` (`id`, `user_id`, `name`, `time_interval`, `acl_id`, `merge`, `owncolor`) VALUES
(1, 1, 'Everyone', 1800, 102, 0, 1),
(2, 1, 'Everyone (Merge)', 1800, 103, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cal_views_calendars`
--

CREATE TABLE `cal_views_calendars` (
  `view_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `background` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CCFFCC'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cal_views_groups`
--

CREATE TABLE `cal_views_groups` (
  `view_id` int(11) NOT NULL,
  `group_id` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cal_views_groups`
--

INSERT INTO `cal_views_groups` (`view_id`, `group_id`) VALUES
(1, '2'),
(2, '2');

-- --------------------------------------------------------

--
-- Table structure for table `cal_visible_tasklists`
--

CREATE TABLE `cal_visible_tasklists` (
  `calendar_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_ab_companies`
--

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
  `Text` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cf_ab_companies`
--

INSERT INTO `cf_ab_companies` (`model_id`, `Company`, `Contact`, `File`, `Checkbox`, `Number`, `User`, `HTML`, `Infotext`, `Heading`, `Select`, `Treeselect`, `Textarea`, `Date`, `Function`, `Text`) VALUES
(1, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, ''),
(2, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, ''),
(3, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `cf_ab_contacts`
--

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
  `multiselect` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cf_ab_contacts`
--

INSERT INTO `cf_ab_contacts` (`model_id`, `Company`, `Contact`, `File`, `Checkbox`, `Number`, `User`, `HTML`, `Infotext`, `Heading`, `Select`, `Treeselect`, `Textarea`, `Date`, `Function`, `Text`, `Treeselect1`, `Treeselect2`, `multiselect`) VALUES
(1, '', '', '', 0, NULL, '', '', '', '', 'Option 1', '1:O 2', '', NULL, 0, '', '3:O 2.1', '', 'Option 1|Option 4'),
(2, '', '', '', 0, NULL, '', '', '', '', 'Option 2', '2:O 1', '', NULL, 0, '', '6:O 1.2', '7:O 1.2.3', NULL),
(3, '', '', '', 0, NULL, '', '', '', '', 'Option 3', '', '', NULL, 0, '', '', '', 'Option 1|Option 2|Option 3|Option 4'),
(4, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, '', '', '', NULL),
(5, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, '', '', '', NULL),
(6, '', '', '', 0, NULL, '', NULL, '', '', '', '', NULL, NULL, NULL, '', '', '', NULL),
(7, '', '', '', 0, NULL, '', '', '', '', '', '', '', NULL, 0, '', '', '', NULL),
(8, '', '', '', 0, NULL, '', '', '', '', '', '', '', NULL, 0, '', '', '', ''),
(11, '', '', '', 0, NULL, '', '', '', '', 'Removed option 4', '2:O 1', '', NULL, 0, '', '6:O 1.2', '8:Removed option O 1.2.4', '');

-- --------------------------------------------------------

--
-- Table structure for table `cf_blocks`
--

CREATE TABLE `cf_blocks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `field_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_categories`
--

CREATE TABLE `cf_categories` (
  `id` int(11) NOT NULL,
  `extends_model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_index` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_disable_categories`
--

CREATE TABLE `cf_disable_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_enabled_blocks`
--

CREATE TABLE `cf_enabled_blocks` (
  `block_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_enabled_categories`
--

CREATE TABLE `cf_enabled_categories` (
  `model_id` int(11) NOT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_fields`
--

CREATE TABLE `cf_fields` (
  `id` int(11) NOT NULL,
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
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_select_tree_options`
--

CREATE TABLE `cf_select_tree_options` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cf_tree_select_options`
--

CREATE TABLE `cf_tree_select_options` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cf_tree_select_options`
--

INSERT INTO `cf_tree_select_options` (`id`, `parent_id`, `field_id`, `name`, `sort`) VALUES
(1, 0, 26, 'O 2', 0),
(2, 0, 26, 'O 1', 0),
(4, 1, 26, 'O 2.2', 0),
(5, 2, 26, 'O 1.1', 0),
(6, 2, 26, 'O 1.2', 0),
(7, 6, 26, 'O 1.2.3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `comments_attachment`
--

CREATE TABLE `comments_attachment` (
  `commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `comments_comment`
--

CREATE TABLE `comments_comment` (
  `id` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `text` mediumtext CHARACTER SET utf8mb4 DEFAULT NULL,
  `section` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments_comment`
--

INSERT INTO `comments_comment` (`id`, `createdAt`, `entityId`, `entityTypeId`, `createdBy`, `modifiedBy`, `modifiedAt`, `text`, `section`) VALUES
(1, '2019-07-01 09:07:34', 13, 16, 1, NULL, '2019-07-01 09:07:34', 'The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate which produces every product type imaginable, no matter how elaborate or extravagant - none of which ever work as desired or expected. In the Road Runner cartoon Beep, Beep, it was referred to as \"Acme Rocket-Powered Products, Inc.\" based in Fairfield, New Jersey. Many of its products appear to be produced specifically for Wile E. Coyote; for example, the Acme Giant Rubber Band, subtitled \"(For Tripping Road Runners)\".', NULL),
(2, '2019-07-01 09:07:34', 13, 16, 1, NULL, '2019-07-01 09:07:34', 'Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat, which had been used on Elmer Fudd and Sam Sheepdog (which doesn\'t maul its intended victim); and Acme Bumblebees in one-fifth bottles (which sting Wile E. Coyote). The Wild Cat was used in the shorts Don\'t Give Up the Sheep and A Mutt in a Rut, while the bees were used in the short Zoom and Bored.', NULL),
(3, '2019-07-01 09:07:34', 2, 16, 1, NULL, '2019-07-01 09:07:34', 'Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon characters from a series of Looney Tunes and Merrie Melodies cartoons. The characters (a coyote and Greater Roadrunner) were created by animation director Chuck Jones in 1948 for Warner Bros., while the template for their adventures was the work of writer Michael Maltese. The characters star in a long-running series of theatrical cartoon shorts (the first 16 of which were written by Maltese) and occasional made-for-television cartoons.', NULL),
(4, '2019-07-01 09:07:34', 2, 16, 1, NULL, '2019-07-01 09:07:34', 'In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube Goldberg) and elaborate plans to pursue his quarry. It was originally meant to parody chase cartoons like Tom and Jerry, but became popular in its own right, much to Jones\' chagrin.', NULL),
(5, '2019-07-01 09:07:42', 1, 17, 1, NULL, '2019-07-01 09:07:42', 'Scheduled call at 04-07-2019 11:07', NULL),
(6, '2019-07-01 09:07:43', 2, 17, 1, NULL, '2019-07-01 09:07:43', 'Scheduled call at 04-07-2019 11:07', NULL),
(7, '2019-07-18 11:30:08', 1, 16, 1, NULL, '2019-07-18 11:30:08', 'Dit is een test', NULL),
(8, '2019-07-18 11:30:26', 13, 16, 1, NULL, '2019-07-18 11:30:26', 'Test bij een bedrijf', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments_comment_image`
--

CREATE TABLE `comments_comment_image` (
  `commentId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments_comment_label`
--

CREATE TABLE `comments_comment_label` (
  `labelId` int(11) NOT NULL,
  `commentId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `comments_comment_label`
--

INSERT INTO `comments_comment_label` (`labelId`, `commentId`) VALUES
(1, 7),
(2, 8);

-- --------------------------------------------------------

--
-- Table structure for table `comments_label`
--

CREATE TABLE `comments_label` (
  `id` int(11) NOT NULL,
  `name` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `color` char(6) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '243a80'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comments_label`
--

INSERT INTO `comments_label` (`id`, `name`, `color`) VALUES
(1, 'Blauw', '243a80'),
(2, 'Groen', '243a80');

-- --------------------------------------------------------

--
-- Table structure for table `core_acl`
--

CREATE TABLE `core_acl` (
  `id` int(11) NOT NULL,
  `ownedBy` int(11) DEFAULT NULL,
  `usedIn` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `entityTypeId` int(11) DEFAULT NULL,
  `entityId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_acl`
--

INSERT INTO `core_acl` (`id`, `ownedBy`, `usedIn`, `modifiedAt`, `entityTypeId`, `entityId`) VALUES
(1, 1, 'core_group.aclId', '2019-07-01 09:06:23', 4, 1),
(2, 1, 'core_group.aclId', '2019-07-01 09:07:37', 4, 2),
(3, 1, 'core_group.aclId', '2019-07-01 09:07:38', 4, 3),
(4, 1, 'core_group.aclId', '2019-07-01 09:06:23', 4, 4),
(5, 1, 'core_module.aclId', '2020-11-27 12:01:47', 6, 1),
(6, 1, 'core_module.aclId', '2019-07-01 09:06:23', NULL, NULL),
(7, 1, 'core_module.aclId', '2019-07-01 09:06:24', NULL, NULL),
(8, 1, 'core_module.aclId', '2019-07-01 09:06:24', NULL, NULL),
(9, 1, 'core_module.aclId', '2019-07-01 09:06:24', NULL, NULL),
(10, 1, 'core_module.aclId', '2019-07-01 09:06:24', NULL, NULL),
(11, 1, 'core_module.aclId', '2019-07-01 09:06:24', NULL, NULL),
(12, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 8),
(13, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 9),
(14, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 10),
(15, 1, 'ab_addressbooks.acl_id', '2019-07-04 07:41:23', 14, 39),
(16, 1, 'ab_addressbooks.acl_id', '2019-07-01 09:07:38', NULL, NULL),
(17, 1, 'ab_addressbooks.acl_id', '2019-07-01 09:07:38', 14, 13),
(18, 1, 'ab_email_templates.acl_id', '2019-07-01 09:07:38', NULL, NULL),
(19, 1, 'ab_email_templates.acl_id', '2019-07-01 09:07:38', NULL, NULL),
(20, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 11),
(21, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 12),
(22, 1, 'bs_books.acl_id', '2019-07-01 09:07:42', 14, 36),
(23, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 1),
(24, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 2),
(25, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 3),
(26, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 4),
(27, 1, 'bs_books.acl_id', '2019-07-01 09:07:43', 14, 37),
(28, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 5),
(29, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 6),
(30, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 7),
(31, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 8),
(32, 1, 'bs_books.acl_id', '2019-07-01 09:07:44', 14, 38),
(33, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 9),
(34, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 10),
(35, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 11),
(36, 1, 'bs_order_statuses.acl_id', '2019-07-01 09:07:37', 39, 12),
(37, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 13),
(38, 1, 'bm_categories.acl_id', '2019-07-01 09:07:38', NULL, NULL),
(39, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 14),
(40, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 15),
(41, 1, 'core_module.aclId', '2019-07-01 09:06:27', 6, 16),
(43, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 18),
(44, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 19),
(45, 1, 'core_module.aclId', '2019-07-01 09:07:38', 14, 7),
(46, 1, 'fs_templates.acl_id', '2019-07-01 09:07:38', 42, 1),
(47, 1, 'fs_templates.acl_id', '2019-07-01 09:07:38', 42, 2),
(48, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 21),
(49, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 22),
(50, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 23),
(51, 1, 'core_module.aclId', '2019-07-01 09:07:38', 14, 12),
(52, 1, 'pr2_types.acl_id', '2019-07-01 09:06:29', 44, 1),
(53, 1, 'pr2_types.acl_book', '2019-07-01 09:06:29', NULL, NULL),
(54, 1, 'pr2_statuses.acl_id', '2019-07-01 09:06:29', 43, 1),
(55, 1, 'pr2_statuses.acl_id', '2019-07-01 09:06:29', 43, 2),
(56, 1, 'pr2_statuses.acl_id', '2019-07-01 09:06:29', 43, 3),
(57, 1, 'pr2_templates.acl_id', '2019-07-01 09:07:37', 42, 1),
(58, 1, 'pr2_templates.acl_id', '2019-07-01 09:07:37', 42, 2),
(59, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 25),
(60, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 26),
(61, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 27),
(62, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 28),
(63, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 29),
(64, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 30),
(65, 1, 'ti_types.acl_id', '2019-07-01 09:07:45', 44, 1),
(66, 1, 'ti_types.acl_id', '2019-07-01 09:07:37', 44, 2),
(67, 1, 'core_module.aclId', '2019-07-01 09:07:38', 6, 31),
(68, 1, 'core_module.aclId', '2019-07-01 09:06:31', 6, 32),
(69, 1, 'go_settings', '2019-07-01 09:06:47', NULL, NULL),
(70, 1, 'cal_calendars.acl_id', '2019-07-01 09:06:47', 14, 9),
(71, 1, 'ti_types.search_cache_acl_id', '2019-07-01 09:07:51', NULL, NULL),
(72, 1, 'ti_types.search_cache_acl_id', '2019-07-01 09:07:52', NULL, NULL),
(74, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 1),
(75, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 2),
(76, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 3),
(77, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 4),
(78, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 5),
(79, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 6),
(80, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 7),
(81, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 8),
(82, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 9),
(83, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 10),
(84, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 11),
(85, 1, 'core_customfields_field_set.aclId', '2019-07-01 09:07:38', 3, 12),
(86, 1, 'ab_addressbooks.acl_id', '2019-07-01 09:07:38', 14, 18),
(87, 1, 'core_group.aclId', '2019-07-01 09:07:35', 4, 5),
(88, 2, 'fs_folders.acl_id', '2019-07-01 09:07:35', 14, 20),
(89, 2, 'ab_addressbooks.acl_id', '2019-07-01 09:07:36', 14, 21),
(90, 2, 'cal_calendars.acl_id', '2019-07-01 09:07:38', 14, 22),
(91, 2, 'ta_tasklists.acl_id', '2019-07-01 09:07:36', 46, 1),
(92, 1, 'core_group.aclId', '2019-07-01 09:07:36', 4, 6),
(93, 3, 'fs_folders.acl_id', '2019-07-01 09:07:36', 14, 25),
(94, 3, 'ab_addressbooks.acl_id', '2019-07-01 09:07:37', 14, 26),
(95, 3, 'cal_calendars.acl_id', '2019-07-01 09:07:38', 14, 27),
(96, 3, 'ta_tasklists.acl_id', '2019-07-01 09:07:37', 46, 2),
(97, 1, 'core_group.aclId', '2019-07-01 09:07:37', 4, 7),
(98, 4, 'fs_folders.acl_id', '2019-07-01 09:07:38', 14, 29),
(99, 4, 'ab_addressbooks.acl_id', '2019-07-01 09:07:38', 14, 30),
(100, 4, 'cal_calendars.acl_id', '2019-07-01 09:07:38', 14, 31),
(101, 4, 'ta_tasklists.acl_id', '2019-07-01 09:07:38', 46, 3),
(102, 1, 'cal_views.acl_id', '2019-07-01 09:07:41', 41, 1),
(103, 1, 'cal_views.acl_id', '2019-07-01 09:07:41', 41, 2),
(104, 1, 'cal_calendars.acl_id', '2019-07-01 09:07:41', 14, 33),
(105, 1, 'cal_calendars.acl_id', '2019-07-01 09:07:41', 14, 34),
(106, 1, 'ta_tasklists.acl_id', '2019-07-01 09:07:41', 46, 4),
(107, 1, 'core_module.aclId', '2019-07-01 09:07:45', 6, 34),
(108, 1, 'core_module.aclId', '2019-07-01 09:07:45', 6, 35),
(109, 1, 'su_announcements.acl_id', '2019-07-01 09:07:45', 45, 1),
(110, 1, 'su_announcements.acl_id', '2019-07-01 09:07:45', 45, 2),
(111, 1, 'pr2_types.acl_id', '2019-07-01 09:07:46', 44, 2),
(112, 1, 'pr2_types.acl_book', '2019-07-01 09:07:46', NULL, NULL),
(113, 3, 'em_accounts.acl_id', '2019-07-04 14:13:09', 30, 1),
(114, 2, 'em_accounts.acl_id', '2019-07-01 09:07:47', 30, 2),
(115, 4, 'em_accounts.acl_id', '2019-07-01 09:07:48', 30, 3),
(116, 1, 'core_module.aclId', '2019-07-04 12:54:24', 6, 36),
(117, 1, 'pa_domains.acl_id', '2019-07-04 12:54:40', 48, 1),
(118, 1, 'em_accounts.acl_id', '2019-07-05 15:02:58', 30, 4),
(119, 1, 'ab_addresslists.acl_id', '2019-07-08 18:24:54', NULL, NULL),
(120, 1, 'ab_addresslists.acl_id', '2019-07-08 18:25:17', NULL, NULL),
(121, 1, 'pa_domains.acl_id', '2019-07-09 13:02:17', 48, 2),
(122, 1, 'core_module.aclId', '2019-07-09 14:09:10', 6, 37),
(123, 1, 'core_group.aclId', '2019-07-09 14:10:21', NULL, NULL),
(124, 1, 'em_accounts.acl_id', '2019-07-09 14:10:21', NULL, NULL),
(127, 1, 'core_group.aclId', '2019-07-18 07:15:24', 4, 9),
(128, 6, 'em_accounts.acl_id', '2019-07-18 07:15:25', 30, 6),
(129, 6, 'cal_calendars.acl_id', '2019-07-18 07:15:25', 14, 42),
(130, 1, 'fs_folders.acl_id', '2019-07-18 07:22:41', 14, 43),
(131, 1, 'pa_domains.acl_id', '2019-09-10 09:14:32', 48, 3),
(132, 1, 'pa_domains.acl_id', '2019-09-10 09:36:28', 48, 4),
(133, 1, 'pa_domains.acl_id', '2019-09-16 11:37:46', 48, 5),
(134, 1, 'bs_expense_books.acl_id', '2019-10-14 10:06:07', 38, 1),
(141, 1, 'core_module.aclId', '2020-10-01 11:22:07', 6, 40),
(142, 1, 'addressbook_addressbook.aclId', '2020-11-27 12:01:18', 33, 5),
(143, 1, 'core_entity.defaultAclId', '2020-11-27 12:01:38', NULL, NULL),
(144, 1, 'readonly', '2020-11-27 12:01:40', NULL, NULL),
(145, 1, 'core_module.aclId', '2020-11-27 12:01:48', NULL, NULL),
(146, 1, 'addressbook_addressbook.aclId', '2020-11-27 12:01:50', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_acl_group`
--

CREATE TABLE `core_acl_group` (
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL DEFAULT 0,
  `level` tinyint(4) NOT NULL DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_acl_group`
--

INSERT INTO `core_acl_group` (`aclId`, `groupId`, `level`) VALUES
(2, 2, 10),
(3, 3, 10),
(4, 4, 10),
(5, 2, 10),
(12, 3, 10),
(13, 3, 10),
(14, 3, 10),
(15, 3, 10),
(18, 3, 10),
(19, 3, 10),
(20, 3, 10),
(21, 3, 10),
(37, 3, 10),
(38, 3, 10),
(39, 3, 10),
(40, 3, 10),
(43, 3, 10),
(44, 3, 10),
(45, 3, 10),
(46, 3, 10),
(47, 3, 10),
(48, 3, 10),
(49, 3, 10),
(50, 3, 10),
(51, 3, 10),
(57, 2, 10),
(58, 2, 10),
(59, 3, 10),
(60, 3, 10),
(61, 3, 10),
(62, 3, 10),
(63, 3, 10),
(64, 3, 10),
(67, 3, 10),
(86, 3, 10),
(87, 5, 10),
(90, 3, 10),
(92, 6, 10),
(95, 3, 10),
(97, 7, 10),
(100, 3, 10),
(102, 3, 10),
(103, 3, 10),
(104, 3, 10),
(105, 3, 10),
(107, 3, 10),
(108, 3, 10),
(109, 2, 10),
(110, 2, 10),
(116, 3, 10),
(123, 2, 10),
(127, 2, 10),
(127, 9, 10),
(143, 2, 10),
(144, 2, 10),
(16, 3, 30),
(17, 3, 30),
(22, 5, 30),
(22, 6, 30),
(27, 5, 30),
(27, 6, 30),
(32, 5, 30),
(32, 6, 30),
(65, 2, 30),
(66, 2, 30),
(74, 3, 30),
(75, 3, 30),
(76, 3, 30),
(77, 3, 30),
(78, 3, 30),
(79, 3, 30),
(80, 3, 30),
(81, 3, 30),
(82, 3, 30),
(83, 3, 30),
(84, 3, 30),
(85, 3, 30),
(111, 3, 30),
(104, 5, 40),
(105, 5, 40),
(1, 1, 50),
(2, 1, 50),
(3, 1, 50),
(4, 1, 50),
(5, 1, 50),
(6, 1, 50),
(7, 1, 50),
(8, 1, 50),
(9, 1, 50),
(10, 1, 50),
(11, 1, 50),
(12, 1, 50),
(13, 1, 50),
(14, 1, 50),
(15, 1, 50),
(16, 1, 50),
(17, 1, 50),
(18, 1, 50),
(19, 1, 50),
(20, 1, 50),
(21, 1, 50),
(22, 1, 50),
(23, 1, 50),
(23, 2, 50),
(24, 1, 50),
(24, 2, 50),
(25, 1, 50),
(25, 2, 50),
(26, 1, 50),
(26, 2, 50),
(27, 1, 50),
(28, 1, 50),
(28, 2, 50),
(29, 1, 50),
(29, 2, 50),
(30, 1, 50),
(30, 2, 50),
(31, 1, 50),
(31, 2, 50),
(32, 1, 50),
(33, 1, 50),
(33, 2, 50),
(34, 1, 50),
(34, 2, 50),
(35, 1, 50),
(35, 2, 50),
(36, 1, 50),
(36, 2, 50),
(37, 1, 50),
(38, 1, 50),
(39, 1, 50),
(40, 1, 50),
(41, 1, 50),
(43, 1, 50),
(44, 1, 50),
(45, 1, 50),
(46, 1, 50),
(47, 1, 50),
(48, 1, 50),
(49, 1, 50),
(50, 1, 50),
(51, 1, 50),
(52, 1, 50),
(53, 1, 50),
(54, 1, 50),
(55, 1, 50),
(56, 1, 50),
(57, 1, 50),
(58, 1, 50),
(59, 1, 50),
(60, 1, 50),
(61, 1, 50),
(62, 1, 50),
(63, 1, 50),
(64, 1, 50),
(65, 1, 50),
(65, 5, 50),
(65, 6, 50),
(66, 1, 50),
(67, 1, 50),
(68, 1, 50),
(69, 1, 50),
(70, 1, 50),
(71, 1, 50),
(71, 5, 50),
(71, 6, 50),
(72, 1, 50),
(74, 1, 50),
(75, 1, 50),
(76, 1, 50),
(77, 1, 50),
(78, 1, 50),
(79, 1, 50),
(80, 1, 50),
(81, 1, 50),
(82, 1, 50),
(83, 1, 50),
(84, 1, 50),
(85, 1, 50),
(86, 1, 50),
(87, 1, 50),
(88, 1, 50),
(88, 5, 50),
(89, 1, 50),
(89, 5, 50),
(90, 1, 50),
(90, 5, 50),
(91, 1, 50),
(91, 5, 50),
(92, 1, 50),
(93, 1, 50),
(93, 6, 50),
(94, 1, 50),
(94, 6, 50),
(95, 1, 50),
(95, 6, 50),
(96, 1, 50),
(96, 6, 50),
(97, 1, 50),
(98, 1, 50),
(98, 7, 50),
(99, 1, 50),
(99, 7, 50),
(100, 1, 50),
(100, 7, 50),
(101, 1, 50),
(101, 7, 50),
(102, 1, 50),
(103, 1, 50),
(104, 1, 50),
(105, 1, 50),
(106, 1, 50),
(107, 1, 50),
(108, 1, 50),
(109, 1, 50),
(110, 1, 50),
(111, 1, 50),
(112, 1, 50),
(113, 1, 50),
(113, 4, 50),
(113, 6, 50),
(114, 1, 50),
(114, 5, 50),
(115, 1, 50),
(115, 7, 50),
(116, 1, 50),
(117, 1, 50),
(118, 1, 50),
(118, 4, 50),
(119, 1, 50),
(120, 1, 50),
(121, 1, 50),
(122, 1, 50),
(123, 1, 50),
(124, 1, 50),
(127, 1, 50),
(128, 1, 50),
(128, 9, 50),
(129, 1, 50),
(129, 9, 50),
(130, 1, 50),
(131, 1, 50),
(132, 1, 50),
(133, 1, 50),
(134, 1, 50),
(141, 1, 50),
(142, 1, 50),
(143, 1, 50),
(144, 1, 50),
(145, 1, 50),
(146, 1, 50);

-- --------------------------------------------------------

--
-- Table structure for table `core_acl_group_changes`
--

CREATE TABLE `core_acl_group_changes` (
  `id` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `grantModSeq` int(11) NOT NULL,
  `revokeModSeq` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_acl_group_changes`
--

INSERT INTO `core_acl_group_changes` (`id`, `aclId`, `groupId`, `grantModSeq`, `revokeModSeq`) VALUES
(1, 2, 2, 0, NULL),
(2, 3, 3, 0, NULL),
(3, 4, 4, 0, NULL),
(4, 5, 2, 0, NULL),
(5, 12, 3, 0, NULL),
(6, 13, 3, 0, NULL),
(7, 14, 3, 0, NULL),
(8, 15, 3, 0, NULL),
(9, 18, 3, 0, NULL),
(10, 19, 3, 0, NULL),
(11, 20, 3, 0, NULL),
(12, 21, 3, 0, NULL),
(13, 37, 3, 0, NULL),
(14, 38, 3, 0, NULL),
(15, 39, 3, 0, NULL),
(16, 40, 3, 0, NULL),
(17, 43, 3, 0, NULL),
(18, 44, 3, 0, NULL),
(19, 45, 3, 0, NULL),
(20, 46, 3, 0, NULL),
(21, 47, 3, 0, NULL),
(22, 48, 3, 0, NULL),
(23, 49, 3, 0, NULL),
(24, 50, 3, 0, NULL),
(25, 51, 3, 0, NULL),
(26, 57, 2, 0, NULL),
(27, 58, 2, 0, NULL),
(28, 59, 3, 0, NULL),
(29, 60, 3, 0, NULL),
(30, 61, 3, 0, NULL),
(31, 62, 3, 0, NULL),
(32, 63, 3, 0, NULL),
(33, 64, 3, 0, NULL),
(34, 67, 3, 0, NULL),
(35, 86, 3, 0, NULL),
(36, 87, 5, 0, NULL),
(37, 90, 3, 0, NULL),
(38, 92, 6, 0, NULL),
(39, 95, 3, 0, NULL),
(40, 97, 7, 0, NULL),
(41, 100, 3, 0, NULL),
(42, 102, 3, 0, NULL),
(43, 103, 3, 0, NULL),
(44, 104, 3, 0, NULL),
(45, 105, 3, 0, NULL),
(46, 107, 3, 0, NULL),
(47, 108, 3, 0, NULL),
(48, 109, 2, 0, NULL),
(49, 110, 2, 0, NULL),
(50, 116, 3, 0, NULL),
(51, 123, 2, 0, NULL),
(52, 127, 2, 0, NULL),
(53, 127, 9, 0, NULL),
(54, 143, 2, 0, NULL),
(55, 144, 2, 0, NULL),
(56, 16, 3, 0, NULL),
(57, 17, 3, 0, NULL),
(58, 22, 5, 0, NULL),
(59, 22, 6, 0, NULL),
(60, 27, 5, 0, NULL),
(61, 27, 6, 0, NULL),
(62, 32, 5, 0, NULL),
(63, 32, 6, 0, NULL),
(64, 65, 2, 0, NULL),
(65, 66, 2, 0, NULL),
(66, 74, 3, 0, NULL),
(67, 75, 3, 0, NULL),
(68, 76, 3, 0, NULL),
(69, 77, 3, 0, NULL),
(70, 78, 3, 0, NULL),
(71, 79, 3, 0, NULL),
(72, 80, 3, 0, NULL),
(73, 81, 3, 0, NULL),
(74, 82, 3, 0, NULL),
(75, 83, 3, 0, NULL),
(76, 84, 3, 0, NULL),
(77, 85, 3, 0, NULL),
(78, 111, 3, 0, NULL),
(79, 104, 5, 0, NULL),
(80, 105, 5, 0, NULL),
(81, 1, 1, 0, NULL),
(82, 2, 1, 0, NULL),
(83, 3, 1, 0, NULL),
(84, 4, 1, 0, NULL),
(85, 5, 1, 0, NULL),
(86, 6, 1, 0, NULL),
(87, 7, 1, 0, NULL),
(88, 8, 1, 0, NULL),
(89, 9, 1, 0, NULL),
(90, 10, 1, 0, NULL),
(91, 11, 1, 0, NULL),
(92, 12, 1, 0, NULL),
(93, 13, 1, 0, NULL),
(94, 14, 1, 0, NULL),
(95, 15, 1, 0, NULL),
(96, 16, 1, 0, NULL),
(97, 17, 1, 0, NULL),
(98, 18, 1, 0, NULL),
(99, 19, 1, 0, NULL),
(100, 20, 1, 0, NULL),
(101, 21, 1, 0, NULL),
(102, 22, 1, 0, NULL),
(103, 23, 1, 0, NULL),
(104, 23, 2, 0, NULL),
(105, 24, 1, 0, NULL),
(106, 24, 2, 0, NULL),
(107, 25, 1, 0, NULL),
(108, 25, 2, 0, NULL),
(109, 26, 1, 0, NULL),
(110, 26, 2, 0, NULL),
(111, 27, 1, 0, NULL),
(112, 28, 1, 0, NULL),
(113, 28, 2, 0, NULL),
(114, 29, 1, 0, NULL),
(115, 29, 2, 0, NULL),
(116, 30, 1, 0, NULL),
(117, 30, 2, 0, NULL),
(118, 31, 1, 0, NULL),
(119, 31, 2, 0, NULL),
(120, 32, 1, 0, NULL),
(121, 33, 1, 0, NULL),
(122, 33, 2, 0, NULL),
(123, 34, 1, 0, NULL),
(124, 34, 2, 0, NULL),
(125, 35, 1, 0, NULL),
(126, 35, 2, 0, NULL),
(127, 36, 1, 0, NULL),
(128, 36, 2, 0, NULL),
(129, 37, 1, 0, NULL),
(130, 38, 1, 0, NULL),
(131, 39, 1, 0, NULL),
(132, 40, 1, 0, NULL),
(133, 41, 1, 0, NULL),
(134, 43, 1, 0, NULL),
(135, 44, 1, 0, NULL),
(136, 45, 1, 0, NULL),
(137, 46, 1, 0, NULL),
(138, 47, 1, 0, NULL),
(139, 48, 1, 0, NULL),
(140, 49, 1, 0, NULL),
(141, 50, 1, 0, NULL),
(142, 51, 1, 0, NULL),
(143, 52, 1, 0, NULL),
(144, 53, 1, 0, NULL),
(145, 54, 1, 0, NULL),
(146, 55, 1, 0, NULL),
(147, 56, 1, 0, NULL),
(148, 57, 1, 0, NULL),
(149, 58, 1, 0, NULL),
(150, 59, 1, 0, NULL),
(151, 60, 1, 0, NULL),
(152, 61, 1, 0, NULL),
(153, 62, 1, 0, NULL),
(154, 63, 1, 0, NULL),
(155, 64, 1, 0, NULL),
(156, 65, 1, 0, NULL),
(157, 65, 5, 0, NULL),
(158, 65, 6, 0, NULL),
(159, 66, 1, 0, NULL),
(160, 67, 1, 0, NULL),
(161, 68, 1, 0, NULL),
(162, 69, 1, 0, NULL),
(163, 70, 1, 0, NULL),
(164, 71, 1, 0, NULL),
(165, 71, 5, 0, NULL),
(166, 71, 6, 0, NULL),
(167, 72, 1, 0, NULL),
(168, 74, 1, 0, NULL),
(169, 75, 1, 0, NULL),
(170, 76, 1, 0, NULL),
(171, 77, 1, 0, NULL),
(172, 78, 1, 0, NULL),
(173, 79, 1, 0, NULL),
(174, 80, 1, 0, NULL),
(175, 81, 1, 0, NULL),
(176, 82, 1, 0, NULL),
(177, 83, 1, 0, NULL),
(178, 84, 1, 0, NULL),
(179, 85, 1, 0, NULL),
(180, 86, 1, 0, NULL),
(181, 87, 1, 0, NULL),
(182, 88, 1, 0, NULL),
(183, 88, 5, 0, NULL),
(184, 89, 1, 0, NULL),
(185, 89, 5, 0, NULL),
(186, 90, 1, 0, NULL),
(187, 90, 5, 0, NULL),
(188, 91, 1, 0, NULL),
(189, 91, 5, 0, NULL),
(190, 92, 1, 0, NULL),
(191, 93, 1, 0, NULL),
(192, 93, 6, 0, NULL),
(193, 94, 1, 0, NULL),
(194, 94, 6, 0, NULL),
(195, 95, 1, 0, NULL),
(196, 95, 6, 0, NULL),
(197, 96, 1, 0, NULL),
(198, 96, 6, 0, NULL),
(199, 97, 1, 0, NULL),
(200, 98, 1, 0, NULL),
(201, 98, 7, 0, NULL),
(202, 99, 1, 0, NULL),
(203, 99, 7, 0, NULL),
(204, 100, 1, 0, NULL),
(205, 100, 7, 0, NULL),
(206, 101, 1, 0, NULL),
(207, 101, 7, 0, NULL),
(208, 102, 1, 0, NULL),
(209, 103, 1, 0, NULL),
(210, 104, 1, 0, NULL),
(211, 105, 1, 0, NULL),
(212, 106, 1, 0, NULL),
(213, 107, 1, 0, NULL),
(214, 108, 1, 0, NULL),
(215, 109, 1, 0, NULL),
(216, 110, 1, 0, NULL),
(217, 111, 1, 0, NULL),
(218, 112, 1, 0, NULL),
(219, 113, 1, 0, NULL),
(220, 113, 4, 0, NULL),
(221, 113, 6, 0, NULL),
(222, 114, 1, 0, NULL),
(223, 114, 5, 0, NULL),
(224, 115, 1, 0, NULL),
(225, 115, 7, 0, NULL),
(226, 116, 1, 0, NULL),
(227, 117, 1, 0, NULL),
(228, 118, 1, 0, NULL),
(229, 118, 4, 0, NULL),
(230, 119, 1, 0, NULL),
(231, 120, 1, 0, NULL),
(232, 121, 1, 0, NULL),
(233, 122, 1, 0, NULL),
(234, 123, 1, 0, NULL),
(235, 124, 1, 0, NULL),
(236, 127, 1, 0, NULL),
(237, 128, 1, 0, NULL),
(238, 128, 9, 0, NULL),
(239, 129, 1, 0, NULL),
(240, 129, 9, 0, NULL),
(241, 130, 1, 0, NULL),
(242, 131, 1, 0, NULL),
(243, 132, 1, 0, NULL),
(244, 133, 1, 0, NULL),
(245, 134, 1, 0, NULL),
(246, 141, 1, 0, NULL),
(247, 142, 1, 0, NULL),
(248, 143, 1, 0, NULL),
(249, 144, 1, 0, NULL),
(256, 145, 1, 1, NULL),
(257, 146, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_auth_allow_group`
--

CREATE TABLE `core_auth_allow_group` (
  `id` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  `ipPattern` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'IP Address. Wildcards can be used where * matches anything and ? matches exactly one character'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `core_auth_method`
--

CREATE TABLE `core_auth_method` (
  `id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `moduleId` int(11) NOT NULL,
  `sortOrder` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_auth_method`
--

INSERT INTO `core_auth_method` (`id`, `moduleId`, `sortOrder`) VALUES
('password', 1, 1),
('googleauthenticator', 9, 2),
('imap', 37, 3);

-- --------------------------------------------------------

--
-- Table structure for table `core_auth_password`
--

CREATE TABLE `core_auth_password` (
  `userId` int(11) NOT NULL,
  `password` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_auth_password`
--

INSERT INTO `core_auth_password` (`userId`, `password`) VALUES
(1, '$2y$10$/nmOuAWDynoKAxaJtwqVvuaB0fRYSYPm5N8XMGWzigq.mku0ImR8K'),
(2, '$2y$10$wNLG7JPpkgPaKRuB0tZN8eV2MsJfc1ZS23./PvmQFX1cFcQZCC3eG'),
(3, '$2y$10$YTbnf9LECDMzXT5dVxVtxujxzsaQUTygkOR62tC9W4VMXZSxMzQ3e'),
(4, '$2y$10$h0KajOpoLnj4KqESX9pVBOkI/p/R2ZmAykibv2j/f8IZN5fgCmA1e'),
(6, 'nT6uJzatS4wNc');

-- --------------------------------------------------------

--
-- Table structure for table `core_auth_token`
--

CREATE TABLE `core_auth_token` (
  `loginToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `accessToken` varchar(100) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `userId` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `lastActiveAt` datetime NOT NULL,
  `remoteIpAddress` varchar(100) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `userAgent` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `passedMethods` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_auth_token`
--

INSERT INTO `core_auth_token` (`loginToken`, `accessToken`, `userId`, `createdAt`, `expiresAt`, `lastActiveAt`, `remoteIpAddress`, `userAgent`, `passedMethods`) VALUES
('5d1dad7de0e98ebefa1e1f737556faa2874d7554b1154', '5d1dad7e1cf6a530fe2ea6722ce75026b5ecda2077493', 2, '2019-07-04 07:40:45', '2019-07-11 08:07:00', '2019-07-04 08:07:00', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d1de0321e180b789399ead2188fb0bebd983928dbeed', '5d1de03241760fa76f41df980c99dde664942eb0bd137', 1, '2019-07-04 11:17:06', '2019-07-11 13:17:10', '2019-07-04 13:17:10', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d1df6ed0f7886999fce866af14a8aae32dac41e9f4a6', '5d1df6ed3d8f5f8d1ff4d3615f3466d417c34a16e2692', 1, '2019-07-04 12:54:05', '2019-07-11 12:54:05', '2019-07-04 12:54:05', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d1e09257f4754152f0044aae20f00e11beb8ea0a934a', '5d1e0925a1da6cc498765775a22f011cf0fe4a02f6c55', 1, '2019-07-04 14:11:49', '2019-07-11 15:10:10', '2019-07-04 15:10:10', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d1f668e70bf716231091dd6cbf029afaa0a318ed1674', '5d1f668e92ea1a6122af58d04a888d5e3e376e5eb47db', 1, '2019-07-05 15:02:38', '2019-07-12 15:08:46', '2019-07-05 15:08:46', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d234976698970a94b60a96816edfe516bd7bf956e847', '5d2349768ff2f4e8b97724b3f09e67541add6b4882299', 1, '2019-07-08 13:47:34', '2019-07-15 13:47:34', '2019-07-08 13:47:34', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d238a64487b71900f8a184157e7d534980f2fdf5688c', '5d238a646c752850b65651a42b28d8f987e1698e226d0', 1, '2019-07-08 18:24:36', '2019-07-15 18:24:36', '2019-07-08 18:24:36', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d2440808057392fb34623a8d078cafbe77263b8c9890', '5d244080a2c6d6e25ebb9bcc5c98199fcad8a218138e8', 1, '2019-07-09 07:21:36', '2019-07-16 07:39:57', '2019-07-09 07:39:57', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d248ef4e20f5811e641a6f209d52c2b9bbb8527e41e9', '5d248ef520fdcd2ce62fb5f79df8e23d639c8df6c1d58', 1, '2019-07-09 12:56:20', '2019-07-16 12:56:21', '2019-07-09 12:56:20', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0', 'password'),
('5d24904d06b9d0f27a85f1c8bdc0e19e61fb5c28b8acb', '5d24904d29b15b03c4cdccc8f9cbddca58f122dba7067', 1, '2019-07-09 13:02:05', '2019-07-16 13:02:05', '2019-07-09 13:02:05', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0', 'password'),
('5d24a0542e3dd6e89aed8585961c4cf7f7a00a4c8eb4c', '5d24a05453a95b5868f5e9fd97ed856baad6ad255bfa4', 1, '2019-07-09 14:10:28', '2019-07-16 14:10:28', '2019-07-09 14:10:28', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d24a08e57979dbcf23b6b4a5ec5ddf4bbed18fb6b4c0', '5d24a08e7c67ba88aeea2724578d6f5d04eb1f606674b', 5, '2019-07-09 14:11:26', '2019-07-16 15:07:46', '2019-07-09 15:07:46', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:67.0) Gecko/20100101 Firefox/67.0', 'imap'),
('5d26fcf09a7a60bf1f36c0e8fcebad402593f747c53d4', '5d26fcf0a845e194430a6f87068bfaa3c9a599ab3ca3b', 4, '2019-07-11 09:10:08', '2019-07-18 09:10:59', '2019-07-11 09:10:08', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d301d4b3d484511b63bf47b7db68b2c65b4d5aca9671', NULL, 6, '2019-07-18 07:18:35', '2019-07-18 07:28:35', '2019-07-18 07:18:35', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'imap'),
('5d301dda7657fc5ad9c1c514ca947fc6f0c3842c98cf8', '5d301ddaa1b8aec157ee3a21d136868573cede87766f3', 1, '2019-07-18 07:20:58', '2019-07-25 07:20:58', '2019-07-18 07:20:58', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d302471ee15a6ce9e678bfc5f3e9f211b5cf50d49bbf', '5d30247215852e1c161a4ef829a0bb6324d8940c56366', 1, '2019-07-18 07:49:05', '2019-07-25 07:49:06', '2019-07-18 07:49:05', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d3058174380f482b068a2a25e5b0bf2a8ee6c4e276c4', '5d3058175fc2870d4d0b8518ec146401110a200ee6e98', 1, '2019-07-18 11:29:27', '2019-07-25 11:29:27', '2019-07-18 11:29:27', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d31caa2bb186dede5e21297d25633faf45a3777eadc1', '5d31caa2debf2806c08819170172b2e09faf9987e480c', 1, '2019-07-19 13:50:26', '2019-07-26 13:56:02', '2019-07-19 13:56:02', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Safari/605.1.15', 'password'),
('5d528c5bb3b2ab1131e5e5e5f208541899c442b538f41', '5d528c5bd78a05ae7fe27c74f344a635ba416b3e25645', 1, '2019-08-13 10:09:31', '2019-08-20 10:14:31', '2019-08-13 10:14:31', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d52ce474b410e6c5854d1185841b6fcb2ac39f13b48c', '5d52ce476bd0ce6130a3ff0a637ac5b937a9ef7364b60', 1, '2019-08-13 14:50:47', '2019-08-20 14:50:47', '2019-08-13 14:50:47', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d554d71894a7c355167994061ba02886ea09dc0694e5', '5d554d7192247ac0e6c966aa8346cc98f9d6a85090c61', 1, '2019-08-15 12:17:53', '2019-08-22 12:17:53', '2019-08-15 12:17:53', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d77695e00861ccac1a17dd461c1ba85c7cd4433662b7', '5d77695e24eda21e5a55fe1d1fb733a3a6fe79ddcc369', 1, '2019-09-10 09:14:06', '2019-09-17 09:14:06', '2019-09-10 09:14:06', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d7f761132004433eb503d3869da9cffb6221538ba537', '5d7f76115d8dbbb6e8b1d3d9427c590bc7430edff3a71', 1, '2019-09-16 11:46:25', '2019-09-23 11:46:25', '2019-09-16 11:46:25', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d83816b5f842beddee0a388d19875ee3285856b1b988', '5d83816b9080cef40e8d49b11d08bff92edf60d5b7d4c', 1, '2019-09-19 13:23:55', '2019-09-26 13:23:55', '2019-09-19 13:23:55', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.2 Safari/605.1.15', 'password'),
('5d8cb20647b3bdcec734dbb46a993a2a1c8d42f2b14fc', '5d8cb206707c787346e011750babb31073209ec904efb', 1, '2019-09-26 12:41:42', '2019-10-03 12:41:42', '2019-09-26 12:41:42', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0 Safari/605.1.15', 'password'),
('5d9f05603977ff34f90f58182e44e6a3067788a42717b', '5d9f056066374691d2b341bdf06ea03ced1dfdf84676e', 1, '2019-10-10 10:18:08', '2019-10-17 12:00:12', '2019-10-10 12:00:12', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5da4486cdb78997447f3dabb38956cd5c6f450649d01d', '5da4486d25eab2fe7d0b4e6e93ffb5d2f1b8a6250d2cf', 1, '2019-10-14 10:05:32', '2019-10-21 12:08:02', '2019-10-14 12:08:02', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5da595a6db3a784e935d16946d954bdb812f4ed65a2f3', '5da595a71e3be2a20e9ffad35e94973aece64684d46e8', 1, '2019-10-15 09:47:18', '2019-10-22 10:12:44', '2019-10-15 10:12:44', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5dad73c8cbd67b01bda0475a617f08669edd9fd1e770c', '5dad73c9175d225195fd7e9233c6368f1e935489dedf5', 1, '2019-10-21 09:00:56', '2019-10-28 09:00:57', '2019-10-21 09:00:56', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5dbb02b13f54d00608e00fe87d9f717dadd97de7aaec8', '5dbb02b1793e7433b989b8fa302b409f3b53dbd450291', 1, '2019-10-31 15:50:09', '2019-11-07 17:05:38', '2019-10-31 17:05:38', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5dbc17eaa977cbaf74084bc4703f54dca850e78b371ea', '5dbc17eadd2bce3af7468f4a2cb66a4280189f66ec639', 1, '2019-11-01 11:32:58', '2019-11-08 13:21:27', '2019-11-01 13:21:27', '172.29.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.2 Safari/605.1.15', 'password'),
('5fbb84ab88f48f429f6e1d81baf947c166f876f94f242', '5fbb84aba60e752d9d50866a6f51354de8ff3f755b3e4', 1, '2020-11-23 09:45:15', '2020-11-30 09:45:15', '2020-11-23 09:45:15', '172.18.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.1 Safari/605.1.15', 'password');

-- --------------------------------------------------------

--
-- Table structure for table `core_blob`
--

CREATE TABLE `core_blob` (
  `id` binary(40) NOT NULL,
  `type` varchar(129) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` bigint(20) NOT NULL DEFAULT 0,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `staleAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_blob`
--

INSERT INTO `core_blob` (`id`, `type`, `size`, `name`, `createdAt`, `modifiedAt`, `staleAt`) VALUES
(0x33643237393366373965383762633366636663633661373863386135666561653162663562613139, 'image/png', 470, 'Group-Office', '2019-07-18 07:15:45', '2019-07-18 07:15:45', NULL),
(0x34336332663064383135306534376330303531623138623136656564353264333835356337303064, 'image/png', 467, 'Group-Office', '2019-07-09 07:22:25', '2019-07-09 07:22:25', NULL),
(0x34363631353432656130643635353364313830616463353033323733626565646465303935383130, 'image/jpeg', 3015, '16064784785fc0ea8eafc50.jpg', '2020-11-27 12:01:15', '2020-11-27 12:01:18', NULL),
(0x36376361353062376665663961396538313936643939666330633066363263343133643166383336, 'image/jpeg', 12647, '16064784765fc0ea8c1f7b3.jpg', '2020-11-27 12:01:16', '2020-11-27 12:01:16', NULL),
(0x63363735636332396336373663656164353030373430373762343838623963663732613563353866, 'image/png', 907, 'agt_web.png', '2020-11-27 12:01:37', '2019-11-29 12:59:55', '2020-11-27 13:01:37'),
(0x63386231616631396236323961336462333864646633356164663965613737373832623934336466, 'image/png', 937, 'viewmag.png', '2020-11-27 12:01:37', '2019-11-29 12:59:55', '2020-11-27 13:01:37');

-- --------------------------------------------------------

--
-- Table structure for table `core_change`
--

CREATE TABLE `core_change` (
  `id` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `destroyed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_change`
--

INSERT INTO `core_change` (`id`, `entityId`, `entityTypeId`, `modSeq`, `aclId`, `createdAt`, `destroyed`) VALUES
(1, 41, 6, 1, 145, '2020-11-27 12:01:48', 0),
(2, 6, 33, 1, 146, '2020-11-27 12:01:51', 0);

-- --------------------------------------------------------

--
-- Table structure for table `core_change_user`
--

CREATE TABLE `core_change_user` (
  `userId` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `core_change_user_modseq`
--

CREATE TABLE `core_change_user_modseq` (
  `userId` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `highestModSeq` int(11) NOT NULL DEFAULT 0,
  `lowestModSeq` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `core_cron_job`
--

CREATE TABLE `core_cron_job` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expression` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `nextRunAt` datetime DEFAULT NULL,
  `lastRunAt` datetime DEFAULT NULL,
  `runningSince` datetime DEFAULT NULL,
  `lastError` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_cron_job`
--

INSERT INTO `core_cron_job` (`id`, `moduleId`, `description`, `name`, `expression`, `enabled`, `nextRunAt`, `lastRunAt`, `runningSince`, `lastError`) VALUES
(1, 1, 'Garbage collection', 'GarbageCollection', '0 0 * * *', 1, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_customfields_field`
--

CREATE TABLE `core_customfields_field` (
  `id` int(11) NOT NULL,
  `fieldSetId` int(11) NOT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `databaseName` varchar(190) CHARACTER SET utf8mb4 DEFAULT NULL,
  `type` varchar(100) CHARACTER SET utf8mb4 NOT NULL DEFAULT 'Text',
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `required` tinyint(1) NOT NULL DEFAULT 0,
  `relatedFieldCondition` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `conditionallyHidden` tinyint(1) NOT NULL DEFAULT 0,
  `conditionallyRequired` tinyint(1) NOT NULL DEFAULT 0,
  `hint` varchar(190) CHARACTER SET utf8mb4 DEFAULT NULL,
  `exclude_from_grid` tinyint(1) NOT NULL DEFAULT 0,
  `unique_values` tinyint(1) NOT NULL DEFAULT 0,
  `prefix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `suffix` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `options` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hiddenInGrid` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_customfields_field`
--

INSERT INTO `core_customfields_field` (`id`, `fieldSetId`, `modSeq`, `createdAt`, `modifiedAt`, `deletedAt`, `name`, `databaseName`, `type`, `sortOrder`, `required`, `relatedFieldCondition`, `conditionallyHidden`, `conditionallyRequired`, `hint`, `exclude_from_grid`, `unique_values`, `prefix`, `suffix`, `options`, `hiddenInGrid`) VALUES
(1, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:29', NULL, 'Company', 'Company_1', 'Contact', 0, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190,\"isOrganization\":true}', 1),
(2, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:30', NULL, 'Contact', 'Contact_1', 'Contact', 1, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190,\"isOrganization\":false}', 1),
(3, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'File', 'File_1', 'File', 2, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(4, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Checkbox', 'Checkbox_1', 'Checkbox', 3, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(5, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Number', 'Number_1', 'Number', 4, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(6, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'User', 'User_1', 'User', 5, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(7, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'HTML', 'HTML_1', 'Html', 6, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(8, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:20', NULL, 'Infotext', 'Infotext_1', 'Notes', 7, 0, '', 0, 0, '', 0, 0, '', '', '{\"formNotes\":\"Infotext\"}', 1),
(9, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:20', NULL, 'Heading', 'Heading_1', 'Notes', 8, 0, '', 0, 0, '', 0, 0, '', '', '{\"formNotes\":\"Heading\"}', 1),
(10, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Select', 'Select_1', 'Select', 9, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(11, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:22', NULL, 'Treeselect', 'Treeselect_1', 'Select', 10, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(12, 1, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:23', NULL, 'Textarea', 'Textarea_1', 'TextArea', 11, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(13, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Date', 'Date_1', 'Date', 12, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(14, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Function', 'Function_1', 'FunctionField', 13, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(15, 1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Text', 'Text_1', 'Text', 14, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(16, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:31', NULL, 'Company', 'Company', 'Contact', 15, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190,\"isOrganization\":true}', 1),
(17, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:32', NULL, 'Contact', 'Contact', 'Contact', 16, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190,\"isOrganization\":false}', 1),
(18, 2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'File', 'File', 'File', 17, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(19, 2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Checkbox', 'Checkbox', 'Checkbox', 18, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(20, 2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'Number', 'Number', 'Number', 19, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(21, 2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'User', 'User', 'User', 20, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(22, 2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 'HTML', 'HTML', 'Html', 21, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(23, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:24', NULL, 'Infotext', 'Infotext', 'Notes', 22, 0, '', 0, 0, '', 0, 0, '', '', '{\"formNotes\":\"Infotext\"}', 1),
(24, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:24', NULL, 'Heading', 'Heading', 'Notes', 23, 0, '', 0, 0, '', 0, 0, '', '', '{\"formNotes\":\"Heading\"}', 1),
(25, 2, NULL, '2019-07-01 09:07:32', '2020-11-23 09:48:58', NULL, 'Select', 'Select', 'Select', 24, 0, '', 0, 0, '', 0, 0, '', '', '{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\"}', 1),
(26, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:26', NULL, 'Treeselect', 'Treeselect', 'Select', 25, 0, '', 0, 0, '', 0, 0, '', '', '{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":false,\"numberDecimals\":\"2\",\"function\":\"\"}', 1),
(27, 2, NULL, '2019-07-01 09:07:32', '2020-11-27 12:01:29', NULL, 'Textarea', 'Textarea', 'TextArea', 26, 0, '', 0, 0, '', 0, 0, '', '', NULL, 1),
(28, 2, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Date', 'Date', 'Date', 27, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(29, 2, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Function', 'Function', 'FunctionField', 28, 0, '', 0, 0, '', 0, 0, '', '', '{\"maxLength\":190}', 1),
(30, 2, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Text', 'Text', 'Text', 29, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(31, 3, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 30, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(32, 4, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 31, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(33, 5, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 32, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(34, 6, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 33, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(35, 7, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 34, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(36, 8, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 35, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(37, 9, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 36, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(38, 10, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 37, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(39, 11, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 38, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(40, 12, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 'Custom', 'Custom', 'Text', 39, 0, '', 0, 0, 'Some help text for this field', 0, 0, '', '', NULL, 1),
(43, 2, NULL, '2019-08-13 10:13:13', '2020-11-27 12:01:29', NULL, 'Multiselect', 'multiselect', 'MultiSelect', 42, 0, '', 0, 0, '', 0, 0, '', '', '{\"validationRegex\":\"\",\"addressbookIds\":\"\",\"maxLength\":190,\"height\":\"100\",\"multiselect\":true,\"numberDecimals\":\"2\",\"function\":\"\"}', 1);

-- --------------------------------------------------------

--
-- Table structure for table `core_customfields_field_set`
--

CREATE TABLE `core_customfields_field_set` (
  `id` int(11) NOT NULL,
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
  `columns` tinyint(4) NOT NULL DEFAULT 2
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_customfields_field_set`
--

INSERT INTO `core_customfields_field_set` (`id`, `modSeq`, `createdAt`, `modifiedAt`, `deletedAt`, `entityId`, `aclId`, `name`, `description`, `sortOrder`, `filter`, `isTab`, `columns`) VALUES
(1, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 16, 74, 'Demo Custom fields', NULL, 0, NULL, 0, 2),
(2, NULL, '2019-07-01 09:07:32', '2019-07-01 09:07:32', NULL, 16, 75, 'Demo Custom fields', NULL, 1, NULL, 0, 2),
(3, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 17, 76, 'Demo Custom fields', NULL, 2, NULL, 0, 2),
(4, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 18, 77, 'Demo Custom fields', NULL, 3, NULL, 0, 2),
(5, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 19, 78, 'Demo Custom fields', NULL, 4, NULL, 0, 2),
(6, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 20, 79, 'Demo Custom fields', NULL, 5, NULL, 0, 2),
(7, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 21, 80, 'Demo Custom fields', NULL, 6, NULL, 0, 2),
(8, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 14, 81, 'Demo Custom fields', NULL, 7, NULL, 0, 2),
(9, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 22, 82, 'Demo Custom fields', NULL, 8, NULL, 0, 2),
(10, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 23, 83, 'Demo Custom fields', NULL, 9, NULL, 0, 2),
(11, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 24, 84, 'Demo Custom fields', NULL, 10, NULL, 0, 2),
(12, NULL, '2019-07-01 09:07:33', '2019-07-01 09:07:33', NULL, 25, 85, 'Demo Custom fields', NULL, 11, NULL, 0, 2);

-- --------------------------------------------------------

--
-- Table structure for table `core_customfields_multiselect_43`
--

CREATE TABLE `core_customfields_multiselect_43` (
  `id` int(11) NOT NULL,
  `optionId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `core_customfields_multiselect_43`
--

INSERT INTO `core_customfields_multiselect_43` (`id`, `optionId`) VALUES
(1, 4),
(1, 7),
(3, 4),
(3, 5),
(3, 7),
(3, 200095);

-- --------------------------------------------------------

--
-- Table structure for table `core_customfields_select_option`
--

CREATE TABLE `core_customfields_select_option` (
  `id` int(11) NOT NULL,
  `fieldId` int(11) NOT NULL,
  `parentId` int(11) DEFAULT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_customfields_select_option`
--

INSERT INTO `core_customfields_select_option` (`id`, `fieldId`, `parentId`, `text`) VALUES
(1, 25, NULL, 'Option 1'),
(3, 25, NULL, 'Option 3'),
(4, 43, NULL, 'Option 1'),
(5, 43, NULL, 'Option 2'),
(7, 43, NULL, 'Option 4'),
(100001, 26, NULL, 'O 2'),
(100002, 26, NULL, 'O 1'),
(100004, 26, 100001, 'O 2.2'),
(100005, 26, 100002, 'O 1.1'),
(100006, 26, 100002, 'O 1.2'),
(100007, 26, 100006, 'O 1.2.3'),
(200091, 25, NULL, '** Missing ** Option 2'),
(200092, 25, NULL, '** Missing ** Removed option 4'),
(200093, 26, NULL, '** Missing ** O 2.1'),
(200094, 26, NULL, '** Missing ** Removed option O 1.2.4'),
(200095, 43, NULL, '** Missing ** Option 3');

-- --------------------------------------------------------

--
-- Table structure for table `core_email_template`
--

CREATE TABLE `core_email_template` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `core_email_template_attachment`
--

CREATE TABLE `core_email_template_attachment` (
  `id` int(11) NOT NULL,
  `emailTemplateId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inline` tinyint(1) NOT NULL DEFAULT 0,
  `attachment` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `core_entity`
--

CREATE TABLE `core_entity` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `clientName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `highestModSeq` int(11) NOT NULL DEFAULT 0,
  `defaultAclId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_entity`
--

INSERT INTO `core_entity` (`id`, `moduleId`, `name`, `clientName`, `highestModSeq`, `defaultAclId`) VALUES
(1, 1, 'CronJobSchedule', 'CronJobSchedule', 0, NULL),
(2, 1, 'Field', 'Field', 0, NULL),
(3, 1, 'FieldSet', 'FieldSet', 0, 143),
(4, 1, 'Group', 'Group', 0, NULL),
(5, 1, 'Link', 'Link', 0, NULL),
(6, 1, 'Module', 'Module', 1, NULL),
(7, 1, 'Search', 'Search', 0, NULL),
(8, 1, 'User', 'User', 0, NULL),
(9, 1, 'Method', 'Method', 0, NULL),
(10, 1, 'Token', 'Token', 0, NULL),
(11, 1, 'Blob', 'Blob', 0, NULL),
(12, 8, 'Note', 'Note', 0, NULL),
(13, 8, 'NoteBook', 'NoteBook', 0, NULL),
(14, 20, 'Folder', 'Folder', 0, NULL),
(16, 10, 'Contact', 'Contact', 0, NULL),
(17, 12, 'Order', 'Order', 0, NULL),
(18, 12, 'Product', 'Product', 0, NULL),
(19, 14, 'Event', 'Event', 0, NULL),
(20, 14, 'Calendar', 'Calendar', 0, NULL),
(21, 20, 'File', 'File', 0, NULL),
(22, 24, 'Project', 'Project', 0, NULL),
(23, 24, 'TimeEntry', 'TimeEntry', 0, NULL),
(24, 29, 'Task', 'Task', 0, NULL),
(25, 30, 'Ticket', 'Ticket', 0, NULL),
(26, 15, 'Comment', 'Comment', 0, NULL),
(27, 25, 'LinkedEmail', 'LinkedEmail', 0, NULL),
(28, 34, 'Content', 'Content', 0, NULL),
(29, 34, 'Site', 'Site', 0, NULL),
(30, 19, 'Account', 'Account', 0, NULL),
(32, 37, 'Server', 'ImapAuthServer', 0, NULL),
(33, 10, 'AddressBook', 'AddressBook', 1, NULL),
(34, 1, 'EmailTemplate', 'EmailTemplate', 0, NULL),
(35, 1, 'EntityFilter', 'EntityFilter', 0, NULL),
(36, 1, 'SmtpAccount', 'SmtpAccount', 0, NULL),
(37, 12, 'Book', 'Book', 0, NULL),
(38, 12, 'ExpenseBook', 'ExpenseBook', 0, NULL),
(39, 12, 'OrderStatus', 'OrderStatus', 0, NULL),
(40, 14, 'Category', 'Category', 0, NULL),
(41, 14, 'View', 'View', 0, NULL),
(42, 20, 'Template', 'Template', 0, NULL),
(43, 24, 'Status', 'Status', 0, NULL),
(44, 24, 'Type', 'Type', 0, NULL),
(45, 27, 'Announcement', 'Announcement', 0, NULL),
(46, 29, 'Tasklist', 'Tasklist', 0, NULL),
(47, 30, 'TicketGroup', 'TicketGroup', 0, NULL),
(48, 36, 'Domain', 'Domain', 0, NULL),
(49, 1, 'AuthAllowGroup', 'AuthAllowGroup', 0, NULL),
(50, 1, 'OauthClient', 'OauthClient', 0, NULL),
(51, 10, 'Group', 'AddressBookGroup', 0, NULL),
(52, 13, 'Bookmark', 'Bookmark', 0, NULL),
(53, 13, 'Category', 'BookmarksCategory', 0, NULL),
(54, 15, 'Label', 'CommentLabel', 0, NULL),
(55, 1, 'Acl', 'Acl', 1, NULL),
(56, 41, 'A', 'A', 0, NULL),
(57, 41, 'B', 'B', 0, NULL),
(58, 41, 'C', 'C', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_entity_filter`
--

CREATE TABLE `core_entity_filter` (
  `id` int(11) NOT NULL,
  `entityTypeId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `filter` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `type` enum('fixed','variable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `core_group`
--

CREATE TABLE `core_group` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) DEFAULT NULL,
  `isUserGroupFor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_group`
--

INSERT INTO `core_group` (`id`, `name`, `createdBy`, `aclId`, `isUserGroupFor`) VALUES
(1, 'Admins', 1, 1, NULL),
(2, 'Everyone', 1, 2, NULL),
(3, 'Internal', 1, 3, NULL),
(4, 'admin', 1, 4, 1),
(5, 'elmer', 1, 87, 2),
(6, 'demo', 1, 92, 3),
(7, 'linda', 1, 97, 4),
(9, 'foo@intermesh.localhost', 1, 127, 6);

-- --------------------------------------------------------

--
-- Table structure for table `core_group_default_group`
--

CREATE TABLE `core_group_default_group` (
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_group_default_group`
--

INSERT INTO `core_group_default_group` (`groupId`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `core_link`
--

CREATE TABLE `core_link` (
  `id` int(11) NOT NULL,
  `fromEntityTypeId` int(11) NOT NULL,
  `fromId` int(11) NOT NULL,
  `toEntityTypeId` int(11) NOT NULL,
  `toId` int(11) NOT NULL,
  `description` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `folderId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_link`
--

INSERT INTO `core_link` (`id`, `fromEntityTypeId`, `fromId`, `toEntityTypeId`, `toId`, `description`, `createdAt`, `deletedAt`, `modSeq`, `folderId`) VALUES
(1, 16, 2, 19, 1, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(2, 19, 1, 16, 2, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(3, 16, 2, 19, 4, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(4, 19, 4, 16, 2, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(5, 16, 2, 19, 7, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(6, 19, 7, 16, 2, '', '2019-07-01 09:07:39', NULL, NULL, NULL),
(7, 16, 1, 19, 10, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(8, 19, 10, 16, 1, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(9, 16, 1, 19, 12, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(10, 19, 12, 16, 1, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(11, 16, 1, 19, 14, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(12, 19, 14, 16, 1, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(13, 16, 1, 19, 16, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(14, 19, 16, 16, 1, '', '2019-07-01 09:07:40', NULL, NULL, NULL),
(15, 16, 1, 19, 18, '', '2019-07-01 09:07:41', NULL, NULL, NULL),
(16, 19, 18, 16, 1, '', '2019-07-01 09:07:41', NULL, NULL, NULL),
(17, 16, 1, 19, 20, '', '2019-07-01 09:07:41', NULL, NULL, NULL),
(18, 19, 20, 16, 1, '', '2019-07-01 09:07:41', NULL, NULL, NULL),
(19, 24, 4, 16, 2, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(20, 16, 2, 24, 4, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(21, 24, 4, 19, 20, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(22, 19, 20, 24, 4, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(23, 24, 5, 16, 2, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(24, 16, 2, 24, 5, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(25, 24, 5, 19, 20, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(26, 19, 20, 24, 5, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(27, 24, 6, 16, 2, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(28, 16, 2, 24, 6, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(29, 24, 6, 19, 20, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(30, 19, 20, 24, 6, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(31, 17, 1, 16, 1, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(32, 16, 1, 17, 1, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(33, 17, 1, 16, 12, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(34, 16, 12, 17, 1, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(35, 24, 7, 17, 1, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(36, 17, 1, 24, 7, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(37, 24, 7, 16, 1, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(38, 16, 1, 24, 7, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(39, 24, 7, 16, 12, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(40, 16, 12, 24, 7, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(41, 17, 2, 16, 2, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(42, 16, 2, 17, 2, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(43, 17, 2, 16, 13, '', '2019-07-01 09:07:42', NULL, NULL, NULL),
(44, 16, 13, 17, 2, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(45, 24, 8, 17, 2, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(46, 17, 2, 24, 8, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(47, 24, 8, 16, 2, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(48, 16, 2, 24, 8, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(49, 24, 8, 16, 13, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(50, 16, 13, 24, 8, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(51, 17, 3, 16, 1, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(52, 16, 1, 17, 3, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(53, 17, 3, 16, 12, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(54, 16, 12, 17, 3, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(55, 17, 4, 16, 2, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(56, 16, 2, 17, 4, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(57, 17, 4, 16, 13, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(58, 16, 13, 17, 4, '', '2019-07-01 09:07:43', NULL, NULL, NULL),
(59, 17, 5, 16, 1, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(60, 16, 1, 17, 5, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(61, 17, 5, 16, 12, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(62, 16, 12, 17, 5, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(63, 17, 6, 16, 2, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(64, 16, 2, 17, 6, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(65, 17, 6, 16, 13, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(66, 16, 13, 17, 6, '', '2019-07-01 09:07:44', NULL, NULL, NULL),
(67, 22, 2, 16, 13, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(68, 16, 13, 22, 2, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(69, 22, 2, 16, 2, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(70, 16, 2, 22, 2, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(71, 22, 3, 16, 13, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(72, 16, 13, 22, 3, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(73, 22, 3, 16, 2, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(74, 16, 2, 22, 3, '', '2019-07-01 09:07:46', NULL, NULL, NULL),
(75, 27, 1, 16, 2, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(76, 16, 2, 27, 1, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(77, 27, 2, 16, 1, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(78, 16, 1, 27, 2, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(79, 27, 3, 16, 2, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(80, 16, 2, 27, 3, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(81, 27, 4, 16, 1, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(82, 16, 1, 27, 4, '', '2019-07-01 09:07:47', NULL, NULL, NULL),
(83, 16, 7, 16, 6, NULL, '2019-07-04 07:41:59', NULL, NULL, NULL),
(84, 16, 6, 16, 7, NULL, '2019-07-04 07:41:59', NULL, NULL, NULL),
(85, 27, 5, 22, 1, '', '2019-07-11 09:10:24', NULL, NULL, NULL),
(86, 22, 1, 27, 5, '', '2019-07-11 09:10:24', NULL, NULL, NULL),
(87, 22, 2, 27, 5, NULL, '2019-07-11 09:10:37', NULL, NULL, NULL),
(88, 27, 5, 22, 2, NULL, '2019-07-11 09:10:37', NULL, NULL, NULL),
(89, 27, 6, 22, 1, '', '2019-07-11 09:11:37', NULL, NULL, NULL),
(90, 22, 1, 27, 6, '', '2019-07-11 09:11:37', NULL, NULL, NULL),
(91, 22, 3, 27, 6, NULL, '2019-07-11 09:11:51', NULL, NULL, NULL),
(92, 27, 6, 22, 3, NULL, '2019-07-11 09:11:51', NULL, NULL, NULL),
(93, 27, 7, 16, 1, '', '2019-10-10 10:20:20', NULL, NULL, NULL),
(94, 16, 1, 27, 7, '', '2019-10-10 10:20:20', NULL, NULL, NULL),
(95, 27, 8, 16, 1, '', '2019-10-10 10:21:15', NULL, NULL, NULL),
(96, 16, 1, 27, 8, '', '2019-10-10 10:21:15', NULL, NULL, NULL),
(97, 22, 5, 16, 13, '', '2019-10-14 10:11:16', NULL, NULL, NULL),
(98, 16, 13, 22, 5, '', '2019-10-14 10:11:16', NULL, NULL, NULL),
(99, 27, 9, 22, 5, '', '2019-10-14 10:11:17', NULL, NULL, NULL),
(100, 22, 5, 27, 9, '', '2019-10-14 10:11:17', NULL, NULL, NULL),
(101, 24, 9, 22, 3, NULL, '2019-10-31 15:56:07', NULL, NULL, NULL),
(102, 22, 3, 24, 9, NULL, '2019-10-31 15:56:07', NULL, NULL, NULL),
(103, 24, 9, 22, 2, NULL, '2019-10-31 15:56:07', NULL, NULL, NULL),
(104, 22, 2, 24, 9, NULL, '2019-10-31 15:56:07', NULL, NULL, NULL),
(105, 22, 3, 16, 8, NULL, '2019-10-31 15:57:07', NULL, NULL, NULL),
(106, 16, 8, 22, 3, NULL, '2019-10-31 15:57:07', NULL, NULL, NULL),
(107, 19, 22, 22, 2, NULL, '2019-10-31 15:57:27', NULL, NULL, NULL),
(108, 22, 2, 19, 22, NULL, '2019-10-31 15:57:27', NULL, NULL, NULL),
(109, 24, 10, 22, 2, NULL, '2019-10-31 15:58:42', NULL, NULL, NULL),
(110, 22, 2, 24, 10, NULL, '2019-10-31 15:58:42', NULL, NULL, NULL),
(111, 24, 10, 22, 3, NULL, '2019-10-31 15:58:42', NULL, NULL, NULL),
(112, 22, 3, 24, 10, NULL, '2019-10-31 15:58:42', NULL, NULL, NULL),
(113, 24, 11, 22, 2, NULL, '2019-10-31 16:00:03', NULL, NULL, NULL),
(114, 22, 2, 24, 11, NULL, '2019-10-31 16:00:03', NULL, NULL, NULL),
(115, 24, 11, 22, 3, NULL, '2019-10-31 16:00:03', NULL, NULL, NULL),
(116, 22, 3, 24, 11, NULL, '2019-10-31 16:00:03', NULL, NULL, NULL),
(117, 24, 12, 22, 2, NULL, '2019-10-31 16:00:57', NULL, NULL, NULL),
(118, 22, 2, 24, 12, NULL, '2019-10-31 16:00:57', NULL, NULL, NULL),
(119, 24, 12, 22, 3, NULL, '2019-10-31 16:00:57', NULL, NULL, NULL),
(120, 22, 3, 24, 12, NULL, '2019-10-31 16:00:57', NULL, NULL, NULL),
(121, 24, 13, 22, 3, NULL, '2019-10-31 16:09:23', NULL, NULL, NULL),
(122, 22, 3, 24, 13, NULL, '2019-10-31 16:09:23', NULL, NULL, NULL),
(123, 16, 1, 16, 12, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(124, 16, 12, 16, 1, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(125, 16, 2, 16, 13, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(126, 16, 13, 16, 2, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(127, 16, 10, 16, 12, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(128, 16, 12, 16, 10, NULL, '2020-11-27 12:01:16', NULL, NULL, NULL),
(129, 16, 3, 16, 14, NULL, '2020-11-27 12:01:17', NULL, NULL, NULL),
(130, 16, 14, 16, 3, NULL, '2020-11-27 12:01:17', NULL, NULL, NULL),
(131, 16, 4, 16, 14, NULL, '2020-11-27 12:01:17', NULL, NULL, NULL),
(132, 16, 14, 16, 4, NULL, '2020-11-27 12:01:17', NULL, NULL, NULL),
(133, 16, 5, 16, 14, NULL, '2020-11-27 12:01:18', NULL, NULL, NULL),
(134, 16, 14, 16, 5, NULL, '2020-11-27 12:01:18', NULL, NULL, NULL),
(135, 16, 6, 16, 14, NULL, '2020-11-27 12:01:18', NULL, NULL, NULL),
(136, 16, 14, 16, 6, NULL, '2020-11-27 12:01:18', NULL, NULL, NULL),
(137, 16, 9, 16, 12, NULL, '2020-11-27 12:01:19', NULL, NULL, NULL),
(138, 16, 12, 16, 9, NULL, '2020-11-27 12:01:19', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_module`
--

CREATE TABLE `core_module` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `package` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `admin_menu` tinyint(1) NOT NULL DEFAULT 0,
  `aclId` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `modifiedAt` datetime DEFAULT NULL,
  `modSeq` int(11) DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_module`
--

INSERT INTO `core_module` (`id`, `name`, `package`, `version`, `sort_order`, `admin_menu`, `aclId`, `enabled`, `modifiedAt`, `modSeq`, `deletedAt`) VALUES
(1, 'core', 'core', 188, 0, 0, 5, 1, NULL, NULL, NULL),
(8, 'notes', 'community', 46, 210, 0, 12, 1, NULL, NULL, NULL),
(9, 'googleauthenticator', 'community', 0, 100, 0, 13, 1, NULL, NULL, NULL),
(10, 'addressbook', 'community', 55, 209, 0, 14, 1, '2019-07-01 09:06:25', NULL, NULL),
(11, 'assistant', NULL, 0, 110, 0, 20, 1, '2019-07-01 09:06:26', NULL, NULL),
(12, 'billing', NULL, 319, 111, 0, 21, 1, '2019-07-01 09:06:26', NULL, NULL),
(13, 'bookmarks', 'community', 8, 212, 0, 37, 1, '2019-07-01 09:06:27', NULL, NULL),
(14, 'calendar', NULL, 184, 113, 0, 39, 1, '2019-07-01 09:06:27', NULL, NULL),
(15, 'comments', 'community', 24, 114, 0, 40, 1, '2019-07-01 09:06:27', NULL, NULL),
(16, 'cron', NULL, 0, 115, 1, 41, 1, '2019-07-01 09:06:27', NULL, NULL),
(18, 'documenttemplates', NULL, 0, 117, 0, 43, 1, '2019-07-01 09:06:28', NULL, NULL),
(19, 'email', NULL, 104, 118, 0, 44, 1, '2019-10-10 10:18:43', NULL, NULL),
(20, 'files', NULL, 128, 119, 0, 45, 1, '2019-07-01 09:06:28', NULL, NULL),
(21, 'hoursapproval2', NULL, 0, 120, 0, 48, 1, '2019-07-01 09:06:28', NULL, NULL),
(22, 'intermeshtrials', NULL, 0, 121, 0, 49, 1, '2019-07-01 09:06:28', NULL, NULL),
(23, 'leavedays', NULL, 27, 122, 0, 50, 1, '2019-07-01 09:06:28', NULL, NULL),
(24, 'projects2', NULL, 384, 123, 0, 51, 1, '2019-07-01 09:06:28', NULL, NULL),
(25, 'savemailas', NULL, 12, 124, 0, 59, 1, '2019-07-01 09:06:29', NULL, NULL),
(26, 'sieve', NULL, 0, 125, 0, 60, 1, '2019-07-01 09:06:30', NULL, NULL),
(27, 'summary', NULL, 17, 126, 0, 61, 1, '2019-07-01 09:06:30', NULL, NULL),
(28, 'sync', NULL, 49, 127, 0, 62, 1, '2019-07-01 09:06:30', NULL, NULL),
(29, 'tasks', NULL, 60, 128, 0, 63, 1, '2019-07-01 09:06:30', NULL, NULL),
(30, 'tickets', NULL, 161, 129, 0, 64, 1, '2019-07-01 09:06:30', NULL, NULL),
(31, 'timeregistration2', NULL, 0, 130, 0, 67, 1, '2019-07-01 09:06:31', NULL, NULL),
(32, 'tools', NULL, 0, 131, 1, 68, 1, '2019-07-01 09:06:31', NULL, NULL),
(34, 'site', NULL, 18, 132, 0, 107, 1, '2019-07-01 09:07:45', NULL, NULL),
(35, 'defaultsite', NULL, 0, 133, 0, 108, 1, '2019-07-01 09:07:45', NULL, NULL),
(36, 'postfixadmin', NULL, 39, 133, 0, 116, 1, '2019-07-04 12:54:24', NULL, NULL),
(37, 'imapauthenticator', 'community', 1, 100, 0, 122, 1, NULL, NULL, NULL),
(40, 'log', NULL, 0, 136, 1, 141, 1, '2020-10-01 11:22:07', NULL, NULL),
(41, 'test', 'community', 0, 213, 0, 145, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `core_oauth_access_token`
--

CREATE TABLE `core_oauth_access_token` (
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `expiryDateTime` datetime DEFAULT NULL,
  `userIdentifier` int(11) NOT NULL,
  `clientId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `core_oauth_client`
--

CREATE TABLE `core_oauth_client` (
  `id` int(11) NOT NULL,
  `identifier` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `isConfidential` tinyint(1) NOT NULL,
  `name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `redirectUri` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(128) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `core_search`
--

CREATE TABLE `core_search` (
  `id` int(11) NOT NULL,
  `entityId` int(11) NOT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 DEFAULT NULL,
  `entityTypeId` int(11) NOT NULL,
  `keywords` varchar(750) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filter` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `aclId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `core_search`
--

INSERT INTO `core_search` (`id`, `entityId`, `moduleId`, `name`, `description`, `entityTypeId`, `keywords`, `filter`, `modifiedAt`, `aclId`) VALUES
(1, 1, 20, 'project_templates', 'project_templates', 14, 'Folder,project_templates', NULL, '2019-02-06 19:13:08', 57),
(2, 2, 20, 'Projects folder', 'project_templates/Projects folder', 14, 'Folder,Projects folder,project_templates/Projects folder', NULL, '2019-07-01 09:06:29', 57),
(3, 3, 20, 'Standard project', 'project_templates/Standard project', 14, 'Folder,Standard project,project_templates/Standard project', NULL, '2019-07-01 09:06:29', 58),
(4, 4, 20, 'tickets', 'tickets', 14, 'Folder,tickets', NULL, '2019-05-31 12:26:43', 71),
(5, 7, 20, 'billing', 'billing', 14, 'Folder,billing', NULL, '2019-02-25 11:06:18', 45),
(6, 8, 20, 'stationery-papers', 'billing/stationery-papers', 14, 'Folder,stationery-papers,billing/stationery-papers', NULL, '2019-07-01 09:06:47', 45),
(7, 9, 20, 'calendar', 'calendar', 14, 'Folder,calendar', NULL, '2019-07-18 07:15:25', 70),
(8, 10, 20, 'System Administrator', 'calendar/System Administrator', 14, 'Folder,System Administrator,calendar/System Administrator', NULL, '2019-07-01 09:06:47', 70),
(9, 11, 20, 'projects2', 'projects2', 14, 'Map,projects2', NULL, '2018-12-20 08:25:04', 45),
(10, 12, 20, 'template-icons', 'projects2/template-icons', 14, 'Folder,template-icons,projects2/template-icons', NULL, '2019-01-08 10:16:35', 51),
(12, 1, 10, 'John Smith', 'Customers - CEO', 16, 'John Smith  john@smith.demo 0612345678 Netherlands Noord-Holland Amsterdam 1012 NX 1:O 2 3:O 2.1', 'isContact', '2020-11-27 12:01:16', 17),
(14, 1, 15, 'The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...', '', 26, 'Comment,The company is never clearly defined in Road Runner cartoons but appears to be a conglomerate...,17,The company is never clearly defined in Road Runner cartoons but appears to be a', NULL, '2019-07-01 09:07:34', 40),
(15, 2, 15, 'Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...', '', 26, 'Comment,Sometimes, Acme can also send living creatures through the mail, though that isn\'t done very...,17, though that isn\'t done very often. Two examples of this are the Acme Wild-Cat,', NULL, '2019-07-01 09:07:34', 40),
(16, 2, 10, 'Wile E. Coyote', 'Customers - CEO', 16, 'Wile E. Coyote  wile@acme.demo 0612345678 United States NY New York 10019 2:O 1 6:O 1.2 7:O 1.2.3', 'isContact', '2020-11-27 12:01:16', 17),
(17, 3, 15, 'Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...', '', 26, 'Comment,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of cartoon...,17,Wile E. Coyote (also known simply as \"The Coyote\") and The Road Runner are a duo of', NULL, '2019-07-01 09:07:34', 40),
(18, 4, 15, 'In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...', '', 26, 'Comment,In each episode, instead of animal senses and cunning, Wile E. Coyote uses absurdly complex...,17, Wile E. Coyote uses absurdly complex contraptions (sometimes in the manner of Rube', NULL, '2019-07-01 09:07:34', 40),
(19, 13, 20, 'addressbook', 'addressbook', 14, 'Folder addressbook', NULL, '2020-11-27 12:01:40', 144),
(20, 14, 20, 'Customers', 'addressbook/Customers', 14, 'Folder,Customers,addressbook/Customers', NULL, '2019-02-06 19:13:42', 17),
(21, 15, 20, 'contacts', 'addressbook/Customers/contacts', 14, 'Map,contacts,addressbook/Customers/contacts', NULL, '2019-10-10 10:20:04', 17),
(22, 16, 20, 'C', 'addressbook/Customers/contacts/C', 14, 'Folder,C,addressbook/Customers/contacts/C', NULL, '2019-07-01 09:07:34', 17),
(23, 17, 20, 'Wile E. Coyote (2)', 'addressbook/Customers/contacts/C/Wile E. Coyote (2)', 14, 'Folder,Wile E. Coyote (2),addressbook/Customers/contacts/C/Wile E. Coyote (2)', NULL, '2020-01-10 14:38:33', 17),
(24, 18, 20, 'Users', 'addressbook/Users', 14, 'Folder,Users,addressbook/Users', NULL, '2019-07-01 09:07:35', 86),
(25, 3, 10, 'System Administrator', 'Users', 16, 'System Administrator  admin@intermesh.localhost', 'isContact', '2020-11-27 12:01:17', 86),
(26, 1, 20, 'Demo letter.docx', 'addressbook/Customers/contacts/C/Wile E. Coyote (2)/Demo letter.docx', 21, 'File,Demo letter.docx,addressbook/Customers/contacts/C/Wile E. Coyote (2)/Demo letter.docx,docx', NULL, '2020-01-10 14:38:33', 17),
(27, 19, 20, 'users', 'users', 14, 'Folder,users', NULL, '2019-07-18 07:15:09', 45),
(28, 20, 20, 'elmer', 'users/elmer', 14, 'Folder,elmer,users/elmer', NULL, '2019-07-01 09:07:35', 88),
(29, 4, 10, 'Elmer Fudd', 'Users - CEO', 16, 'Elmer Fudd  elmer@group-office.com 0612345678 United States NY New York 10019', 'isContact', '2020-11-27 12:01:17', 86),
(31, 21, 20, 'Elmer Fudd', 'addressbook/Elmer Fudd', 14, 'Folder,Elmer Fudd,addressbook/Elmer Fudd', NULL, '2019-07-01 09:07:36', 89),
(32, 22, 20, 'Elmer Fudd', 'calendar/Elmer Fudd', 14, 'Folder,Elmer Fudd,calendar/Elmer Fudd', NULL, '2019-07-01 09:07:36', 90),
(33, 23, 20, 'tasks', 'tasks', 14, 'Folder,tasks', NULL, '2019-02-28 15:07:18', 91),
(34, 24, 20, 'Elmer Fudd', 'tasks/Elmer Fudd', 14, 'Folder,Elmer Fudd,tasks/Elmer Fudd', NULL, '2019-07-01 09:07:36', 91),
(35, 25, 20, 'demo', 'users/demo', 14, 'Folder,demo,users/demo', NULL, '2019-02-06 19:13:52', 93),
(36, 5, 10, 'Demo User', 'Users - CEO', 16, 'Demo User  demo@acmerpp.demo demo@group-office.com 0612345678 United States NY New York 10019', 'isContact', '2020-11-27 12:01:17', 86),
(37, 26, 20, 'Demo User', 'addressbook/Demo User', 14, 'Folder,Demo User,addressbook/Demo User', NULL, '2019-07-01 09:07:37', 94),
(38, 27, 20, 'Demo User', 'calendar/Demo User', 14, 'Folder,Demo User,calendar/Demo User', NULL, '2019-07-01 09:07:37', 95),
(39, 28, 20, 'Demo User', 'tasks/Demo User', 14, 'Folder,Demo User,tasks/Demo User', NULL, '2019-07-01 09:07:37', 96),
(40, 29, 20, 'linda', 'users/linda', 14, 'Folder,linda,users/linda', NULL, '2019-07-01 09:07:38', 98),
(41, 6, 10, 'Linda Smith', 'Users - CEO', 16, 'Linda Smith  linda@acmerpp.demo 0612345678 United States NY New York 10019', 'isContact', '2020-11-27 12:01:18', 86),
(42, 30, 20, 'Linda Smith', 'addressbook/Linda Smith', 14, 'Folder,Linda Smith,addressbook/Linda Smith', NULL, '2019-07-01 09:07:38', 99),
(43, 31, 20, 'Linda Smith', 'calendar/Linda Smith', 14, 'Folder,Linda Smith,calendar/Linda Smith', NULL, '2019-07-01 09:07:38', 100),
(44, 32, 20, 'Linda Smith', 'tasks/Linda Smith', 14, 'Folder,Linda Smith,tasks/Linda Smith', NULL, '2019-07-01 09:07:38', 101),
(45, 1, 14, 'Project meeting (02-07-2019, Demo User)', '', 19, 'Event,Project meeting (02-07-2019, Demo User),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:38', 95),
(46, 2, 14, 'Project meeting (02-07-2019, Linda Smith)', '', 19, 'Event,Project meeting (02-07-2019, Linda Smith),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:38', 100),
(47, 3, 14, 'Project meeting (02-07-2019, Elmer Fudd)', '', 19, 'Event,Project meeting (02-07-2019, Elmer Fudd),@1562054400,08daba0e-9ef3-59c1-933a-03ca01a8ad22,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:38', 90),
(48, 4, 14, 'Meet Wile (02-07-2019, Demo User)', '', 19, 'Event,Meet Wile (02-07-2019, Demo User),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 95),
(49, 5, 14, 'Meet Wile (02-07-2019, Linda Smith)', '', 19, 'Event,Meet Wile (02-07-2019, Linda Smith),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 100),
(50, 6, 14, 'Meet Wile (02-07-2019, Elmer Fudd)', '', 19, 'Event,Meet Wile (02-07-2019, Elmer Fudd),@1562061600,6c4dbd0f-a214-59e2-851a-aa287656176e,Europe/Amsterdam,Meet Wile,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 90),
(51, 7, 14, 'MT Meeting (02-07-2019, Demo User)', '', 19, 'Event,MT Meeting (02-07-2019, Demo User),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 95),
(52, 8, 14, 'MT Meeting (02-07-2019, Linda Smith)', '', 19, 'Event,MT Meeting (02-07-2019, Linda Smith),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 100),
(53, 9, 14, 'MT Meeting (02-07-2019, Elmer Fudd)', '', 19, 'Event,MT Meeting (02-07-2019, Elmer Fudd),@1562068800,4d87f73c-7bf0-5c90-b450-2689c963888e,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 90),
(54, 10, 14, 'Project meeting (03-07-2019, Linda Smith)', '', 19, 'Event,Project meeting (03-07-2019, Linda Smith),@1562144400,ccc9622a-e00d-5643-99f2-7c2dc88494b1,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 100),
(55, 11, 14, 'Project meeting (03-07-2019, Demo User)', '', 19, 'Event,Project meeting (03-07-2019, Demo User),@1562144400,ccc9622a-e00d-5643-99f2-7c2dc88494b1,Europe/Amsterdam,Project meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:39', 95),
(56, 12, 14, 'Meet John (03-07-2019, Linda Smith)', '', 19, 'Event,Meet John (03-07-2019, Linda Smith),@1562151600,71d09bf8-00e9-5c29-9889-b4db5de35525,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 100),
(57, 13, 14, 'Meet John (03-07-2019, Demo User)', '', 19, 'Event,Meet John (03-07-2019, Demo User),@1562151600,71d09bf8-00e9-5c29-9889-b4db5de35525,Europe/Amsterdam,Meet John,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 95),
(58, 14, 14, 'MT Meeting (03-07-2019, Linda Smith)', '', 19, 'Event,MT Meeting (03-07-2019, Linda Smith),@1562162400,379d9f35-23dd-5c09-8da6-2c1ae967386c,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 100),
(59, 15, 14, 'MT Meeting (03-07-2019, Demo User)', '', 19, 'Event,MT Meeting (03-07-2019, Demo User),@1562162400,379d9f35-23dd-5c09-8da6-2c1ae967386c,Europe/Amsterdam,MT Meeting,ACME NY Office,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 95),
(60, 16, 14, 'Rocket testing (02-07-2019, Linda Smith)', '', 19, 'Event,Rocket testing (02-07-2019, Linda Smith),@1562047200,20ef5123-708b-590e-9d95-6c963b0accb4,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 100),
(61, 17, 14, 'Rocket testing (02-07-2019, Demo User)', '', 19, 'Event,Rocket testing (02-07-2019, Demo User),@1562047200,20ef5123-708b-590e-9d95-6c963b0accb4,Europe/Amsterdam,Rocket testing,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 95),
(62, 18, 14, 'Blast impact test (02-07-2019, Linda Smith)', '', 19, 'Event,Blast impact test (02-07-2019, Linda Smith),@1562072400,f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 100),
(63, 19, 14, 'Blast impact test (02-07-2019, Demo User)', '', 19, 'Event,Blast impact test (02-07-2019, Demo User),@1562072400,f0a1e129-9fed-5b71-8a3b-9fabc77cc3aa,Europe/Amsterdam,Blast impact test,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:40', 95),
(64, 20, 14, 'Test range extender (02-07-2019, Linda Smith)', '', 19, 'Event,Test range extender (02-07-2019, Linda Smith),@1562086800,0212980f-bfec-55a8-8fba-b3232a097705,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:41', 100),
(65, 21, 14, 'Test range extender (02-07-2019, Demo User)', '', 19, 'Event,Test range extender (02-07-2019, Demo User),@1562086800,0212980f-bfec-55a8-8fba-b3232a097705,Europe/Amsterdam,Test range extender,ACME Testing fields,CONFIRMED,EBF1E2', NULL, '2019-07-01 09:07:41', 95),
(66, 33, 20, 'Road Runner Room', 'calendar/Road Runner Room', 14, 'Folder,Road Runner Room,calendar/Road Runner Room', NULL, '2019-07-01 09:07:41', 104),
(67, 34, 20, 'Don Coyote Room', 'calendar/Don Coyote Room', 14, 'Folder,Don Coyote Room,calendar/Don Coyote Room', NULL, '2019-07-01 09:07:41', 105),
(68, 35, 20, 'System Administrator', 'tasks/System Administrator', 14, 'Folder,System Administrator,tasks/System Administrator', NULL, '2019-07-01 09:07:41', 106),
(69, 1, 29, 'Feed the dog', '', 24, 'Task,Feed the dog,69afb1ee-4123-5162-a477-c9256d9d6d0f,NEEDS-ACTION', NULL, '2019-07-01 09:07:41', 96),
(70, 2, 29, 'Feed the dog', '', 24, 'Task,Feed the dog,9a2db52a-912b-521d-94d6-336a72e0a531,NEEDS-ACTION', NULL, '2019-07-01 09:07:41', 101),
(71, 3, 29, 'Feed the dog', '', 24, 'Task,Feed the dog,ee97359f-e341-5292-b9d9-63be3f9b3db9,NEEDS-ACTION', NULL, '2019-07-01 09:07:41', 91),
(72, 4, 29, 'Prepare meeting', '', 24, 'Task,Prepare meeting,511706f1-b2b6-51d8-9925-af89876d42f8,NEEDS-ACTION', NULL, '2019-07-01 09:07:41', 96),
(73, 5, 29, 'Prepare meeting', '', 24, 'Task,Prepare meeting,9374d8e8-3dcc-5586-a23e-a0885e667e6d,NEEDS-ACTION', NULL, '2019-07-01 09:07:42', 101),
(74, 6, 29, 'Prepare meeting', '', 24, 'Task,Prepare meeting,dd44ae8c-8528-597c-aed1-e9f8bc5e21ee,NEEDS-ACTION', NULL, '2019-07-01 09:07:42', 91),
(75, 1, 12, 'Q19000001', 'Smith Inc', 17, 'Invoice/Quote,Q19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo', NULL, '2019-07-01 09:07:42', 22),
(76, 36, 20, 'Quotes', 'billing/Quotes', 14, 'Folder,Quotes,billing/Quotes', NULL, '2019-07-01 09:07:42', 22),
(77, 7, 29, 'Call: Smith Inc (Q19000001)', '', 24, 'Task,Call: Smith Inc (Q19000001),da0f4e45-4aa8-5a7a-8035-5a5742fd4285,NEEDS-ACTION', NULL, '2019-07-01 09:07:42', 106),
(78, 5, 15, 'Scheduled call at 04-07-2019 11:07', '', 26, 'Comment,Scheduled call at 04-07-2019 11:07,22', NULL, '2019-07-01 09:07:42', 40),
(79, 2, 12, 'Q19000002', 'ACME Corporation', 17, 'Invoice/Quote,Q19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo', NULL, '2019-07-01 09:07:43', 22),
(80, 8, 29, 'Call: ACME Corporation (Q19000002)', '', 24, 'Task,Call: ACME Corporation (Q19000002),92b31122-f509-595e-bf5e-d7364cda75a7,NEEDS-ACTION', NULL, '2019-07-01 09:07:43', 106),
(81, 6, 15, 'Scheduled call at 04-07-2019 11:07', '', 26, 'Comment,Scheduled call at 04-07-2019 11:07,22', NULL, '2019-07-01 09:07:43', 40),
(82, 3, 12, 'O19000001', 'Smith Inc', 17, 'Invoice/Quote,O19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo', NULL, '2019-07-01 09:07:43', 27),
(83, 37, 20, 'Orders', 'billing/Orders', 14, 'Folder,Orders,billing/Orders', NULL, '2019-07-01 09:07:43', 27),
(84, 4, 12, 'O19000002', 'ACME Corporation', 17, 'Invoice/Quote,O19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo', NULL, '2019-07-01 09:07:44', 27),
(85, 5, 12, 'I19000001', 'Smith Inc', 17, 'Invoice/Quote,I19000001,Smith Inc,Dear Mr / Ms,Kalverstraat,1,1012 NX,Amsterdam,NL,NL 1234.56.789.B01,info@smith.demo', NULL, '2019-07-04 14:19:55', 32),
(86, 38, 20, 'Invoices', 'billing/Invoices', 14, 'Folder,Invoices,billing/Invoices', NULL, '2019-07-01 09:07:44', 32),
(87, 6, 12, 'I19000002', 'ACME Corporation', 17, 'Invoice/Quote,I19000002,ACME Corporation,Dear Mr / Ms,1111 Broadway,10019,New York,US,US 1234.56.789.B01,info@acme.demo', NULL, '2019-07-01 09:07:44', 32),
(88, 1, 30, 'Malfunctioning rockets', 'Wile E. Coyote (ACME Corporation)', 25, 'Ticket,Malfunctioning rockets,Wile E. Coyote (ACME Corporation),71,201900001,ACME Corporation,Wile,E.,Coyote,wile@acme.demo', NULL, '2019-07-01 09:07:45', 71),
(89, 2, 30, 'Can I speed up my rockets?', 'Wile E. Coyote (ACME Corporation)', 25, 'Ticket,Can I speed up my rockets?,Wile E. Coyote (ACME Corporation),71,201900002,ACME Corporation,Wile,E.,Coyote,wile@acme.demo', NULL, '2019-07-01 09:07:45', 71),
(90, 2, 20, 'noperson.jpg', 'users/demo/noperson.jpg', 21, 'File,noperson.jpg,users/demo/noperson.jpg,jpg', NULL, '2019-07-01 09:07:45', 93),
(91, 3, 20, 'empty.docx', 'users/demo/empty.docx', 21, 'File,empty.docx,users/demo/empty.docx,docx', NULL, '2019-07-01 09:07:45', 93),
(92, 4, 20, 'wecoyote.png', 'users/demo/wecoyote.png', 21, 'File,wecoyote.png,users/demo/wecoyote.png,png', NULL, '2019-07-01 09:07:45', 93),
(93, 5, 20, 'Demo letter.docx', 'users/demo/Demo letter.docx', 21, 'File,Demo letter.docx,users/demo/Demo letter.docx,docx', NULL, '2019-07-01 09:07:45', 93),
(94, 6, 20, 'empty.odt', 'users/demo/empty.odt', 21, 'File,empty.odt,users/demo/empty.odt,odt', NULL, '2019-07-01 09:07:45', 93),
(95, 1, 24, 'Demo', '| Demo | Demo', 22, 'Project,Demo, | Demo | Demo,Just a placeholder for sub projects.,1', NULL, '2019-10-10 12:00:15', 111),
(96, 2, 24, '[001] Develop Rocket 2000', '| Demo | Demo/[001] Develop Rocket 2000', 22, 'Project,[001] Develop Rocket 2000, | Demo | Demo/[001] Develop Rocket 2000,Better range and accuracy,Demo/[001] Develop Rocket 2000,1', NULL, '2019-10-10 12:00:15', 111),
(97, 3, 24, '[001] Develop Rocket Launcher', '| Demo | Demo/[001] Develop Rocket Launcher', 22, 'Project,[001] Develop Rocket Launcher, | Demo | Demo/[001] Develop Rocket Launcher,Better range and accuracy,Demo/[001] Develop Rocket Launcher,1', NULL, '2019-07-01 09:07:46', 111),
(98, 7, 20, 'project.png', 'projects2/template-icons/project.png', 21, 'File,project.png,projects2/template-icons/project.png,png', NULL, '2019-01-08 10:16:32', 51),
(99, 8, 20, 'folder.png', 'projects2/template-icons/folder.png', 21, 'File,folder.png,projects2/template-icons/folder.png,png', NULL, '2019-01-08 10:16:32', 51),
(100, 1, 25, 'Rocket 2000 development plan', 'From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>', 27, 'Email,Rocket 2000 development plan,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,@1368777188,\"User, Demo\" <demo@group-office.com>,\"Elmer\"', NULL, '2019-07-01 09:07:47', 17),
(101, 2, 25, 'Rocket 2000 development plan', 'From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>', 27, 'Email,Rocket 2000 development plan,From: \"User, Demo\" <demo@group-office.com>\nTo: \"Elmer\" <elmer@group-office.com>,@1368777188,\"User, Demo\" <demo@group-office.com>,\"Elmer\"', NULL, '2019-07-01 09:07:47', 17),
(102, 3, 25, 'Just a demo message', 'From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>', 27, 'Email,Just a demo message,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\"', NULL, '2019-07-01 09:07:47', 17),
(103, 4, 25, 'Just a demo message', 'From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\" <demo@group-office.com>', 27, 'Email,Just a demo message,From: \"User, Demo\" <demo@group-office.com>\nTo: \"User, Demo\"', NULL, '2019-07-01 09:07:47', 17),
(104, 39, 20, 'Prospects', 'addressbook/Prospects', 14, 'Folder,Prospects,addressbook/Prospects', NULL, '2019-07-04 07:41:24', 15),
(105, 7, 10, 'Read Only', 'Prospects', 16, 'Read Only ', 'isContact', '2020-11-27 12:01:15', 15),
(109, 5, 25, 'test', 'From: \"Demo User\" <demo@group-office.com>\nTo: \"Demo User\" <demo@group-office.com>', 27, 'Email,test,From: \"Demo User\" <demo@group-office.com>\nTo: \"Demo User\" <demo@group-office.com>,@1562249681,\"Demo User\"', NULL, '2019-07-11 09:10:24', 111),
(110, 6, 25, 'test', 'From: \"Alexander Hu\" <hu.alexander@web.de>\nTo: linda@group-office.com', 27, 'Email,test,From: \"Alexander Hu\" <hu.alexander@web.de>\nTo: linda@group-office.com,@1531483030,\"Alexander Hu\"', NULL, '2019-07-11 09:11:37', 111),
(112, 42, 20, 'foo', 'calendar/foo', 14, 'Folder,foo,calendar/foo', NULL, '2019-07-18 07:15:25', 129),
(113, 43, 20, 'admin', 'users/admin', 14, 'Folder,admin,users/admin', NULL, '2019-07-18 07:22:41', 130),
(114, 44, 20, 'Reports', 'users/admin/Reports', 14, 'Folder,Reports,users/admin/Reports', NULL, '2019-07-18 07:22:41', 130),
(115, 7, 15, 'Dit is een test', '', 26, 'Comment,Dit is een test,17', NULL, '2019-07-18 11:30:08', 40),
(116, 8, 15, 'Test bij een bedrijf', '', 26, 'Comment,Test bij een bedrijf,17', NULL, '2019-07-18 11:30:26', 40),
(117, 4, 24, 't1', '| Default | Demo/t1', 22, 'Project,t1, | Default | Demo/t1,Demo/t1,1', NULL, '2019-08-13 14:52:05', 52),
(118, 45, 20, 'S', 'addressbook/Customers/contacts/S', 14, 'Map,S,addressbook/Customers/contacts/S', NULL, '2019-10-10 10:20:04', 17),
(119, 46, 20, 'John Smith (1)', 'addressbook/Customers/contacts/S/John Smith (1)', 14, 'Map,John Smith (1),addressbook/Customers/contacts/S/John Smith (1)', NULL, '2019-10-10 10:20:04', 17),
(120, 9, 20, 'Hi Hubert.eml', 'addressbook/Customers/contacts/S/John Smith (1)/Hi Hubert.eml', 21, 'Bestand,Hi Hubert.eml,addressbook/Customers/contacts/S/John Smith (1)/Hi Hubert.eml,eml', NULL, '2019-10-10 10:20:09', 17),
(121, 7, 25, 'Hi Hubert', 'From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost', 27, 'E-mail,Hi Hubert,From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost,@1570435819,\"Intermesh\"', NULL, '2019-10-10 10:20:20', 17),
(122, 8, 25, 'test image attachment', 'From: \"System Administrator\" <admin@intermesh.localhost>\nTo: \"Merijn Schering\" <admin@intermesh.localhost>', 27, 'E-mail,test image attachment,From: \"System Administrator\" <admin@intermesh.localhost>\nTo: \"Merijn Schering\" <admin@intermesh.localhost>,@1567515504,\"System Administrator\"', NULL, '2019-10-10 10:21:15', 17),
(123, 47, 20, 'Demo', 'projects2/Demo', 14, 'Map,Demo,projects2/Demo', NULL, '2019-10-10 12:00:15', 111),
(124, 48, 20, '[001] Develop Rocket 2000', 'projects2/Demo/[001] Develop Rocket 2000', 14, 'Map,[001] Develop Rocket 2000,projects2/Demo/[001] Develop Rocket 2000', NULL, '2019-10-10 12:00:15', 111),
(125, 10, 20, 'lang.csv', 'projects2/Demo/[001] Develop Rocket 2000/lang.csv', 21, 'Bestand,lang.csv,projects2/Demo/[001] Develop Rocket 2000/lang.csv,csv', NULL, '2019-10-10 12:00:22', 111),
(126, 5, 24, 'Hi Hubert', 'ACME Corporation | Default | Hi Hubert', 22, 'Project,Hi Hubert,ACME Corporation | Default | Hi Hubert,ACME Corporation,System Administrator (Users),1', NULL, '2019-10-14 10:11:16', 52),
(127, 9, 25, 'Hi Hubert', 'From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost', 27, 'E-mail,Hi Hubert,From: \"Intermesh\" <admin@intermesh.localhost>\nTo: admin@intermesh.localhost,@1570435819,\"Intermesh\"', NULL, '2019-10-14 10:11:17', 52),
(128, 9, 29, 'ghjgj', '', 24, 'Taak,ghjgj,a5e3930e-4249-50ea-ac4b-4e00601dc116,NEEDS-ACTION', NULL, '2019-10-31 15:56:07', 106),
(129, 8, 10, 'piet test', 'Prospects', 16, 'piet test ', 'isContact', '2020-11-27 12:01:15', 15),
(130, 22, 14, 'mnbmhb (31-10-2019, System Administrator)', '', 19, 'Afspraak,mnbmhb (31-10-2019, System Administrator),@1572537600,d4153d3b-0988-59af-b3f9-d2129564ef76,Europe/Amsterdam,mnbmhb,CONFIRMED,EBF1E2', NULL, '2019-10-31 15:57:26', 70),
(131, 10, 29, 't1', '', 24, 'Taak,t1,78738742-a351-5271-b1aa-b850902b6dc3,NEEDS-ACTION', NULL, '2019-10-31 15:58:41', 106),
(132, 11, 29, 't3', '', 24, 'Taak,t3,5bcf1d4c-2a23-57b9-a3b0-e2b723ffb6d3,NEEDS-ACTION', NULL, '2019-10-31 16:00:03', 106),
(133, 12, 29, 'ghjgjhgjh1', '', 24, 'Taak,ghjgjhgjh1,6872de0c-9e71-53c2-9611-03a438197e19,NEEDS-ACTION', NULL, '2019-10-31 16:00:54', 106),
(134, 13, 29, 't4', '', 24, 'Taak,t4,4c2934cd-16dc-5925-9763-854eeea13dc5,NEEDS-ACTION', NULL, '2019-10-31 16:09:23', 106),
(135, 11, 10, 'Bastard the Orphan', '__ORPHANED__', 16, 'Bastard the Orphan  2:O 1 6:O 1.2 8:Removed option O 1.2.4', 'isContact', '2020-11-27 12:01:19', 142),
(136, 12, 10, 'Smith Inc', 'Customers', 16, 'Smith Inc  info@smith.demo +310101234567 +3101234567 Netherlands Noord-Holland Amsterdam 1012 NX Noord-Brabant just a demo company', 'isOrganization', '2019-07-01 09:07:33', 17),
(137, 13, 10, 'ACME Corporation', 'Customers', 16, 'ACME Corporation  info@acme.demo 5551234567 United States NY New York 10019 the name acme became popular for businesses by 1920s when alphabetized business telephone directories such as', 'isOrganization', '2019-07-01 09:07:34', 17),
(138, 10, 10, ';;ART-test;info@art-test.com;;;;;;;;;;;', 'Customers - CEO', 16, ';;ART-test;info@art-test.com;;;;;;;;;;;  john@smith.demo 0612345678 Netherlands Noord-Holland Amsterdam 1012 NX', 'isContact', '2019-10-10 10:20:04', 17),
(139, 14, 10, 'ACME Rocket Powered Products', 'Users', 16, 'ACME Rocket Powered Products  info@acmerpp.demo 5551234567 United States NY New York 10019 the name acme became popular for businesses by 1920s when alphabetized business telephone', 'isOrganization', '2019-07-01 09:07:35', 86),
(140, 15, 10, 'Orphaned Company', '__ORPHANED__', 16, 'Orphaned Company  info@smith.demo +310101234567 +3101234567 Netherlands Noord-Holland Amsterdam 1012 NX Noord-Brabant just a demo company', 'isOrganization', '2019-07-01 09:07:33', 142),
(141, 9, 10, 'John Orphan', '__ORPHANED__ - CEO', 16, 'John Orphan  john@smith.demo 0612345678 Netherlands Noord-Holland Amsterdam 1012 NX', 'isContact', '2019-10-10 10:20:04', 142);

-- --------------------------------------------------------

--
-- Table structure for table `core_setting`
--

CREATE TABLE `core_setting` (
  `moduleId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_setting`
--

INSERT INTO `core_setting` (`moduleId`, `name`, `value`) VALUES
(1, 'cacheClearedAt', '1606478508'),
(1, 'databaseVersion', '6.4.192'),
(1, 'debugEmail', NULL),
(1, 'defaultAuthenticationDomain', NULL),
(1, 'language', 'en'),
(1, 'locale', 'af_ZA.UTF-8'),
(1, 'loginMessage', 'Thank you for trying Group-Office! You can login with:<br><ul><li>Username: demo, Password: demo</li><li>Username: elmer, Password: demo</li><li>Username: linda, Password: demo</li></ul><br>Select your language below to make sure the demo starts in your language.<br>This demo is reset every day at midnight Central European Time.'),
(1, 'loginMessageEnabled', ''),
(1, 'logoId', NULL),
(1, 'maintenanceMode', ''),
(1, 'passwordMinLength', '6'),
(1, 'primaryColor', NULL),
(1, 'smtpEncryption', 'tls'),
(1, 'smtpEncryptionVerifyCertificate', '1'),
(1, 'smtpHost', 'localhost'),
(1, 'smtpPassword', NULL),
(1, 'smtpPort', '587'),
(1, 'smtpUsername', NULL),
(1, 'systemEmail', 'admin@intermesh.dev'),
(1, 'title', 'Group-Office'),
(1, 'URL', 'http://localhost:63/');

-- --------------------------------------------------------

--
-- Table structure for table `core_smtp_account`
--

CREATE TABLE `core_smtp_account` (
  `id` int(11) NOT NULL,
  `moduleId` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT 587,
  `username` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `encryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `verifyCertificate` tinyint(1) NOT NULL DEFAULT 1,
  `fromName` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fromEmail` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `core_user`
--

CREATE TABLE `core_user` (
  `id` int(11) NOT NULL,
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
  `popup_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `popup_emails` tinyint(1) NOT NULL DEFAULT 0,
  `holidayset` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_email_addresses_by_time` tinyint(1) NOT NULL DEFAULT 0,
  `no_reminders` tinyint(1) NOT NULL DEFAULT 0,
  `last_password_change` int(11) NOT NULL DEFAULT 0,
  `force_password_change` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_user`
--

INSERT INTO `core_user` (`id`, `username`, `displayName`, `avatarId`, `enabled`, `email`, `recoveryEmail`, `recoveryHash`, `recoverySendAt`, `lastLogin`, `createdAt`, `modifiedAt`, `dateFormat`, `shortDateInList`, `timeFormat`, `thousandsSeparator`, `decimalSeparator`, `currency`, `loginCount`, `max_rows_list`, `timezone`, `start_module`, `language`, `theme`, `firstWeekday`, `sort_name`, `muser_id`, `mute_sound`, `mute_reminder_sound`, `mute_new_mail_sound`, `show_smilies`, `auto_punctuation`, `listSeparator`, `textSeparator`, `files_folder_id`, `disk_quota`, `disk_usage`, `mail_reminders`, `popup_reminders`, `popup_emails`, `holidayset`, `sort_email_addresses_by_time`, `no_reminders`, `last_password_change`, `force_password_change`) VALUES
(1, 'admin', 'System Administrator', NULL, 1, 'admin@intermesh.localhost', 'admin@intermesh.localhost', NULL, NULL, '2020-11-23 09:45:15', '2019-07-01 09:06:23', '2020-11-23 09:45:15', 'd-m-Y', 1, 'G:i', '.', ',', '€', 38, 20, 'Europe/Amsterdam', 'summary', 'en', 'Paper', 1, 'first_name', 0, 0, 0, 0, 1, 0, ';', '\"', 0, NULL, 382096, 0, 0, 0, NULL, 0, 0, 0, 0),
(2, 'elmer', 'Elmer Fudd', NULL, 1, 'elmer@group-office.com', 'elmer@acmerpp.demo', NULL, NULL, NULL, '2019-07-01 09:07:35', '2019-07-01 11:07:48', 'd-m-Y', 1, 'G:i', '.', ',', '€', 0, 30, 'Europe/Amsterdam', 'summary', 'en', 'Paper', 1, 'displayName', 1, 0, 0, 0, 1, 0, ';', '\"', 0, 1000, 0, 0, 0, 0, 'en', 0, 0, 0, 0),
(3, 'demo', 'Demo User', NULL, 1, 'demo@group-office.com', 'demo@acmerpp.demo', NULL, NULL, NULL, '2019-07-01 09:07:36', '2019-07-01 11:07:47', 'd-m-Y', 1, 'G:i', '.', ',', '€', 0, 30, 'Europe/Amsterdam', 'summary', 'en', 'Paper', 1, 'displayName', 1, 0, 0, 0, 1, 0, ';', '\"', 0, 1000, 0, 0, 0, 0, 'en', 0, 0, 0, 0),
(4, 'linda', 'Linda Smith', NULL, 1, 'linda@group-office.com', 'linda@acmerpp.demo', NULL, NULL, NULL, '2019-07-01 09:07:37', '2019-07-01 11:07:49', 'd-m-Y', 1, 'G:i', '.', ',', '€', 0, 30, 'Europe/Amsterdam', 'summary', 'en', 'Paper', 1, 'displayName', 1, 0, 0, 0, 1, 0, ';', '\"', 0, 1000, 0, 0, 0, 0, 'en', 0, 0, 0, 0),
(6, 'foo@intermesh.localhost', 'foo', NULL, 1, 'foo@intermesh.localhost', 'foo@intermesh.localhost', NULL, NULL, '2019-07-18 07:16:31', '2019-07-18 07:15:24', '2019-07-18 07:16:31', 'd-m-Y', 1, 'G:i', '.', ',', '€', 2, 20, 'Europe/Amsterdam', 'summary', 'en_uk', 'Paper', 1, 'first_name', 0, 0, 0, 0, 1, 0, ';', '\"', 0, NULL, 0, 0, 0, 0, NULL, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `core_user_custom_fields`
--

CREATE TABLE `core_user_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_user_custom_fields`
--

INSERT INTO `core_user_custom_fields` (`id`) VALUES
(2),
(3),
(4);

-- --------------------------------------------------------

--
-- Table structure for table `core_user_default_group`
--

CREATE TABLE `core_user_default_group` (
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_user_default_group`
--

INSERT INTO `core_user_default_group` (`groupId`) VALUES
(2);

-- --------------------------------------------------------

--
-- Table structure for table `core_user_group`
--

CREATE TABLE `core_user_group` (
  `groupId` int(11) NOT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `core_user_group`
--

INSERT INTO `core_user_group` (`groupId`, `userId`) VALUES
(1, 1),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 6),
(3, 2),
(3, 3),
(3, 4),
(3, 6),
(4, 1),
(5, 2),
(6, 3),
(7, 4),
(9, 6);

-- --------------------------------------------------------

--
-- Table structure for table `email_default_email_account_templates`
--

CREATE TABLE `email_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_default_email_templates`
--

CREATE TABLE `email_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_default_email_templates`
--

INSERT INTO `email_default_email_templates` (`user_id`, `template_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `emp_folders`
--

CREATE TABLE `emp_folders` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_accounts`
--

CREATE TABLE `em_accounts` (
  `id` int(11) NOT NULL,
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
  `full_reply_headers` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `em_accounts`
--

INSERT INTO `em_accounts` (`id`, `user_id`, `acl_id`, `type`, `host`, `port`, `deprecated_use_ssl`, `novalidate_cert`, `username`, `password`, `imap_encryption`, `imap_allow_self_signed`, `mbroot`, `sent`, `drafts`, `trash`, `spam`, `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_allow_self_signed`, `smtp_username`, `smtp_password`, `password_encrypted`, `ignore_sent_folder`, `sieve_port`, `sieve_usetls`, `check_mailboxes`, `do_not_mark_as_read`, `signature_below_reply`, `full_reply_headers`) VALUES
(1, 3, 113, NULL, 'imap.group-office.com', 143, 0, 0, 'demo@group-office.com', '{GOCRYPT2}def50200e98d0636001956342ad2325748ba44e412d3d6899825cbd801496797b88c8bae22185babe846fff61b2436b3cba2eeaf7736196b729628a95eeaf6ff44a1c33d5ecac3e02aa709d71eb4a6ea1a9122357470cd34c4ce4ea1', 'tls', 1, '', 'Sent', 'Drafts', 'Trash', 'Spam', 'smtp.group-office.com', 587, 'tls', 0, '', '', 2, 0, 4190, 1, 'INBOX', 0, 0, 0),
(2, 2, 114, NULL, 'imap.group-office.com', 143, 0, 0, 'elmer@group-office.com', '{GOCRYPT2}def502000d03ba9b03d08744b04862883944c924a8e85c9e37b3d7300e66f3182336c798d2d86ad7db9559a9a9562b287afa751c435bbbb361b944fe7fb78fcd9313eda1dc43738ccce61b7de00290cc5508574a343ebdbea9701515', 'tls', 1, '', 'Sent', 'Drafts', 'Trash', 'Spam', 'smtp.group-office.com', 587, 'tls', 0, NULL, '', 2, 0, 4190, 1, 'INBOX', 0, 0, 0),
(3, 4, 115, NULL, 'imap.group-office.com', 143, 0, 0, 'linda@group-office.com', '{GOCRYPT2}def5020084f74e63b9d75c6a3246093a961163283b117a95809297f43408e94e4257de33c896fc5c7eed81c220160e205a8da32d77607d4bedd65b917cda5ceadeeb6c8928a2852386a0da641d3af966b5899cb6f754c3b4871d3e58', 'tls', 1, '', 'Sent', 'Drafts', 'Trash', 'Spam', 'smtp.group-office.com', 587, 'tls', 0, NULL, '', 2, 0, 4190, 1, 'INBOX', 0, 0, 0),
(4, 1, 118, NULL, 'mailserver', 143, 0, 0, 'admin@intermesh.localhost', '{GOCRYPT2}def502008e24c8b8e3bc1847ab80ca5a89b1515ebc866ab68b107981ed0e7eb685ebb633a2f9d646031202b03ab5668f8b4d4ef0147afbe50d89711b0ab7db82009267b6fa4c4a0233eb656ebe0f899a22dff0ffb6c10744830195150ff6', '', 0, '', 'Sent', 'Drafts', 'Trash', 'Spam', 'localhost', 25, '', 0, '', '', 2, 0, 4190, 1, 'INBOX', 0, 0, 0),
(6, 6, 128, NULL, 'mailserver', 143, 0, 0, 'foo@intermesh.localhost', '{GOCRYPT2}def50200c2a8afd349706d94ca12fc974b045764740c4c0d50d10a40f4360116a56739a7b462875b1b94d873138be7e7917ff37007fd3b68931111f9bbc2c7daa7e5128bafe7e9f2b53baee03aa0268c47d947fd1d7b08c49c6c', '', 1, '', 'Sent', 'Drafts', 'Trash', 'Spam', 'mailserver', 25, '', 1, '', '', 2, 0, 4190, 1, 'INBOX', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `em_accounts_collapsed`
--

CREATE TABLE `em_accounts_collapsed` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_accounts_sort`
--

CREATE TABLE `em_accounts_sort` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_aliases`
--

CREATE TABLE `em_aliases` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `em_aliases`
--

INSERT INTO `em_aliases` (`id`, `account_id`, `name`, `email`, `signature`, `default`) VALUES
(1, 1, 'Demo User', 'demo@group-office.com', '', 1),
(2, 2, 'Elmer Fudd', 'elmer@group-office.com', '', 1),
(3, 3, 'Linda Smith', 'linda@group-office.com', '', 1),
(4, 4, 'Admin', 'admin@intermesh.localhost', '', 1),
(6, 6, 'foo', 'foo@intermesh.localhost', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `em_contacts_last_mail_times`
--

CREATE TABLE `em_contacts_last_mail_times` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_mail_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `em_contacts_last_mail_times`
--

INSERT INTO `em_contacts_last_mail_times` (`contact_id`, `user_id`, `last_mail_time`) VALUES
(5, 1, 1562249681);

-- --------------------------------------------------------

--
-- Table structure for table `em_filters`
--

CREATE TABLE `em_filters` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `field` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keyword` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `folder` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `mark_as_read` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_folders`
--

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
  `sort` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_folders_expanded`
--

CREATE TABLE `em_folders_expanded` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_labels`
--

CREATE TABLE `em_labels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flag` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(6) COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_id` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `em_labels`
--

INSERT INTO `em_labels` (`id`, `name`, `flag`, `color`, `account_id`, `default`) VALUES
(1, 'Label 1', '$label1', '7A7AFF', 1, 1),
(2, 'Label 2', '$label2', '59BD59', 1, 1),
(3, 'Label 3', '$label3', 'FFBD59', 1, 1),
(4, 'Label 4', '$label4', 'FF5959', 1, 1),
(5, 'Label 5', '$label5', 'BD7ABD', 1, 1),
(6, 'Label 1', '$label1', '7A7AFF', 2, 1),
(7, 'Label 2', '$label2', '59BD59', 2, 1),
(8, 'Label 3', '$label3', 'FFBD59', 2, 1),
(9, 'Label 4', '$label4', 'FF5959', 2, 1),
(10, 'Label 5', '$label5', 'BD7ABD', 2, 1),
(11, 'Label 1', '$label1', '7A7AFF', 3, 1),
(12, 'Label 2', '$label2', '59BD59', 3, 1),
(13, 'Label 3', '$label3', 'FFBD59', 3, 1),
(14, 'Label 4', '$label4', 'FF5959', 3, 1),
(15, 'Label 5', '$label5', 'BD7ABD', 3, 1),
(16, 'Label 1', '$label1', '7A7AFF', 4, 1),
(17, 'Label 2', '$label2', '59BD59', 4, 1),
(18, 'Label 3', '$label3', 'FFBD59', 4, 1),
(19, 'Label 4', '$label4', 'FF5959', 4, 1),
(20, 'Label 5', '$label5', 'BD7ABD', 4, 1),
(26, 'Label 1', '$label1', '7A7AFF', 6, 1),
(27, 'Label 2', '$label2', '59BD59', 6, 1),
(28, 'Label 3', '$label3', 'FFBD59', 6, 1),
(29, 'Label 4', '$label4', 'FF5959', 6, 1),
(30, 'Label 5', '$label5', 'BD7ABD', 6, 1);

-- --------------------------------------------------------

--
-- Table structure for table `em_links`
--

CREATE TABLE `em_links` (
  `id` int(11) NOT NULL,
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
  `uid` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `em_links`
--

INSERT INTO `em_links` (`id`, `user_id`, `from`, `to`, `subject`, `time`, `path`, `ctime`, `mtime`, `muser_id`, `acl_id`, `uid`) VALUES
(1, 1, '\"User, Demo\" <demo@group-office.com>', '\"Elmer\" <elmer@group-office.com>', 'Rocket 2000 development plan', 1368777188, 'email/fromfile/demo_5d19cd632a254.eml/demo.eml', 1561972067, 1561972067, 1, 17, '<1368777188.5195e1e479413@localhost>'),
(2, 1, '\"User, Demo\" <demo@group-office.com>', '\"Elmer\" <elmer@group-office.com>', 'Rocket 2000 development plan', 1368777188, 'email/fromfile/demo_5d19cd633af8d.eml/demo.eml', 1561972067, 1561972067, 1, 17, '<1368777188.5195e1e479413@localhost>'),
(3, 1, '\"User, Demo\" <demo@group-office.com>', '\"User, Demo\" <demo@group-office.com>', 'Just a demo message', 1368777986, 'email/fromfile/demo2_5d19cd63486b5.eml/demo2.eml', 1561972067, 1561972067, 1, 17, '<1368777986.5195e5020b17e@localhost>'),
(4, 1, '\"User, Demo\" <demo@group-office.com>', '\"User, Demo\" <demo@group-office.com>', 'Just a demo message', 1368777986, 'email/fromfile/demo2_5d19cd6354ce5.eml/demo2.eml', 1561972067, 1561972067, 1, 17, '<1368777986.5195e5020b17e@localhost>'),
(5, 1, '\"Demo User\" <demo@group-office.com>', '\"Demo User\" <demo@group-office.com>', 'test', 1562249681, 'email/1/1642_1562249681.eml', 1562836224, 1562836224, 1, 111, '<bf39601e1bb9fb8b1626175afa1db673@localhost>'),
(6, 4, '\"Alexander Hu\" <hu.alexander@web.de>', 'linda@group-office.com', 'test', 1531483030, 'email/3/3263_1531483030.eml', 1562836297, 1562836297, 4, 111, '<!&!AAAAAAAAAAAYAAAAAAAAAENzv3v0LbBEsQn1midpPTjCgAAAEAAAABNm4QSxPCRClBCd7ODYwJkBAAAAAA==@web.de>'),
(7, 1, '\"Intermesh\" <admin@intermesh.localhost>', 'admin@intermesh.localhost', 'Hi Hubert', 1570435819, 'email/4/777_1570435819.eml', 1570702820, 1570702820, 1, 17, '<2311b1b961ae168315d64a6a7879d459@office.group-office.com>'),
(8, 1, '\"System Administrator\" <admin@intermesh.localhost>', '\"Merijn Schering\" <admin@intermesh.localhost>', 'test image attachment', 1567515504, 'email/4/54_1567515504.eml', 1570702875, 1570702875, 1, 17, '<cec5d0203e766866cdaef2efe4427306@office.group-office.com>'),
(9, 1, '\"Intermesh\" <admin@intermesh.localhost>', 'admin@intermesh.localhost', 'Hi Hubert', 1570435819, 'email/4/777_1570435819.eml', 1571047877, 1571047877, 1, 52, '<2311b1b961ae168315d64a6a7879d459@office.group-office.com>');

-- --------------------------------------------------------

--
-- Table structure for table `em_messages_cache`
--

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
  `serialized_message_object` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `em_portlet_folders`
--

CREATE TABLE `em_portlet_folders` (
  `account_id` int(11) NOT NULL,
  `folder_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_bookmarks`
--

CREATE TABLE `fs_bookmarks` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_filehandlers`
--

CREATE TABLE `fs_filehandlers` (
  `user_id` int(11) NOT NULL,
  `extension` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cls` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_files`
--

CREATE TABLE `fs_files` (
  `id` int(11) NOT NULL,
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
  `content_expire_date` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fs_files`
--

INSERT INTO `fs_files` (`id`, `folder_id`, `name`, `locked_user_id`, `status_id`, `ctime`, `mtime`, `muser_id`, `size`, `user_id`, `comment`, `extension`, `expire_time`, `random_code`, `delete_when_expired`, `content_expire_date`) VALUES
(1, 17, 'Demo letter.docx', 0, 0, 1561972054, 1578667113, 1, 4312, 1, NULL, 'docx', 0, NULL, 0, NULL),
(2, 25, 'noperson.jpg', 0, 0, 1561972065, 1561972065, 1, 3015, 1, NULL, 'jpg', 0, NULL, 0, NULL),
(3, 25, 'empty.docx', 0, 0, 1561972065, 1561972065, 1, 3726, 1, NULL, 'docx', 0, NULL, 0, NULL),
(4, 25, 'wecoyote.png', 0, 0, 1561972066, 1561972065, 1, 39495, 1, NULL, 'png', 0, NULL, 0, NULL),
(5, 25, 'Demo letter.docx', 0, 0, 1561972066, 1561972065, 1, 4312, 1, NULL, 'docx', 0, NULL, 0, NULL),
(6, 25, 'empty.odt', 0, 0, 1561972066, 1561972065, 1, 6971, 1, NULL, 'odt', 0, NULL, 0, NULL),
(7, 12, 'project.png', 0, 0, 1561972066, 1546942592, 1, 3231, 1, NULL, 'png', 0, NULL, 0, NULL),
(8, 12, 'folder.png', 0, 0, 1561972067, 1546942592, 1, 611, 1, NULL, 'png', 0, NULL, 0, NULL),
(9, 46, 'Hi Hubert.eml', 0, 0, 1570702809, 1570702809, 1, 96437, 1, NULL, 'eml', 0, NULL, 0, NULL),
(10, 48, 'lang.csv', 0, 0, 1570708822, 1570708822, 1, 219986, 1, NULL, 'csv', 0, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fs_files_custom_fields`
--

CREATE TABLE `fs_files_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fs_files_custom_fields`
--

INSERT INTO `fs_files_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, ''),
(7, ''),
(8, ''),
(9, ''),
(10, '');

-- --------------------------------------------------------

--
-- Table structure for table `fs_folders`
--

CREATE TABLE `fs_folders` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `id` int(11) NOT NULL,
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
  `apply_state` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fs_folders`
--

INSERT INTO `fs_folders` (`user_id`, `id`, `parent_id`, `name`, `visible`, `acl_id`, `comment`, `thumbs`, `ctime`, `mtime`, `muser_id`, `quota_user_id`, `readonly`, `cm_state`, `apply_state`) VALUES
(1, 1, 0, 'project_templates', 0, 45, NULL, 1, 1561971989, 1561971989, 1, 1, 1, NULL, 0),
(1, 2, 1, 'Projects folder', 0, 57, NULL, 1, 1561971989, 1561971989, 1, 1, 1, NULL, 0),
(1, 3, 1, 'Standard project', 0, 58, NULL, 1, 1561971989, 1561971989, 1, 1, 1, NULL, 0),
(1, 4, 0, 'tickets', 0, 45, NULL, 1, 1561971990, 1561971990, 1, 1, 1, NULL, 0),
(1, 5, 4, '0-IT', 0, 65, NULL, 1, 1561971990, 1561971990, 1, 1, 1, NULL, 0),
(1, 6, 4, '0-Sales', 0, 66, NULL, 1, 1561971990, 1561971990, 1, 1, 1, NULL, 0),
(1, 7, 0, 'billing', 0, 45, NULL, 1, 1561972007, 1561972064, 1, 1, 1, NULL, 0),
(1, 8, 7, 'stationery-papers', 0, 0, NULL, 1, 1561972007, 1561972007, 1, 1, 0, NULL, 0),
(1, 9, 0, 'calendar', 0, 45, NULL, 1, 1561972007, 1563434125, 6, 1, 1, NULL, 0),
(1, 10, 9, 'System Administrator', 0, 70, NULL, 1, 1561972007, 1561972007, 1, 1, 1, NULL, 0),
(1, 11, 0, 'projects2', 0, 45, NULL, 1, 1561972008, 1570708815, 1, 1, 1, NULL, 0),
(1, 12, 11, 'template-icons', 0, 51, NULL, 1, 1561972008, 1546942595, 1, 1, 0, NULL, 0),
(1, 13, 0, 'addressbook', 0, 45, NULL, 1, 1561972054, 1606478500, 1, 1, 1, NULL, 0),
(1, 14, 13, 'Customers', 0, 17, NULL, 1, 1561972054, 1561972054, 1, 1, 1, NULL, 0),
(1, 15, 14, 'contacts', 0, 17, NULL, 1, 1561972054, 1570702804, 1, 1, 1, NULL, 0),
(1, 16, 15, 'C', 0, 17, NULL, 1, 1561972054, 1561972054, 1, 1, 1, NULL, 0),
(1, 17, 16, 'Wile E. Coyote (2)', 0, 17, NULL, 1, 1561972054, 1578667113, 1, 1, 1, NULL, 0),
(1, 18, 13, 'Users', 0, 86, NULL, 1, 1561972055, 1561972055, 1, 1, 1, NULL, 0),
(1, 19, 0, 'users', 0, 45, NULL, 1, 1561972055, 1563434561, 1, 1, 1, NULL, 0),
(2, 20, 19, 'elmer', 1, 88, NULL, 1, 1561972055, 1561972055, 1, 1, 1, NULL, 0),
(1, 21, 13, 'Elmer Fudd', 0, 89, NULL, 1, 1561972056, 1561972056, 1, 1, 1, NULL, 0),
(1, 22, 9, 'Elmer Fudd', 0, 90, NULL, 1, 1561972056, 1561972056, 1, 1, 1, NULL, 0),
(1, 23, 0, 'tasks', 0, 45, NULL, 1, 1561972056, 1561972061, 1, 1, 1, NULL, 0),
(1, 24, 23, 'Elmer Fudd', 0, 91, NULL, 1, 1561972056, 1561972056, 1, 1, 1, NULL, 0),
(3, 25, 19, 'demo', 1, 93, NULL, 1, 1561972056, 1549480432, 1, 1, 1, NULL, 0),
(1, 26, 13, 'Demo User', 0, 94, NULL, 1, 1561972057, 1561972057, 1, 1, 1, NULL, 0),
(1, 27, 9, 'Demo User', 0, 95, NULL, 1, 1561972057, 1561972057, 1, 1, 1, NULL, 0),
(1, 28, 23, 'Demo User', 0, 96, NULL, 1, 1561972057, 1561972057, 1, 1, 1, NULL, 0),
(4, 29, 19, 'linda', 1, 98, NULL, 1, 1561972057, 1561972058, 1, 1, 1, NULL, 0),
(1, 30, 13, 'Linda Smith', 0, 99, NULL, 1, 1561972058, 1561972058, 1, 1, 1, NULL, 0),
(1, 31, 9, 'Linda Smith', 0, 100, NULL, 1, 1561972058, 1561972058, 1, 1, 1, NULL, 0),
(1, 32, 23, 'Linda Smith', 0, 101, NULL, 1, 1561972058, 1561972058, 1, 1, 1, NULL, 0),
(1, 33, 9, 'Road Runner Room', 0, 104, NULL, 1, 1561972061, 1561972061, 1, 1, 1, NULL, 0),
(1, 34, 9, 'Don Coyote Room', 0, 105, NULL, 1, 1561972061, 1561972061, 1, 1, 1, NULL, 0),
(1, 35, 23, 'System Administrator', 0, 106, NULL, 1, 1561972061, 1561972061, 1, 1, 1, NULL, 0),
(1, 36, 7, 'Quotes', 0, 22, NULL, 1, 1561972062, 1561972062, 1, 1, 1, NULL, 0),
(1, 37, 7, 'Orders', 0, 27, NULL, 1, 1561972063, 1561972063, 1, 1, 1, NULL, 0),
(1, 38, 7, 'Invoices', 0, 32, NULL, 1, 1561972064, 1561972064, 1, 1, 1, NULL, 0),
(1, 39, 13, 'Prospects', 0, 15, NULL, 1, 1562226084, 1562226084, 1, 1, 1, NULL, 0),
(6, 42, 9, 'foo', 0, 129, NULL, 1, 1563434125, 1563434125, 6, 1, 1, NULL, 0),
(1, 43, 19, 'admin', 1, 130, NULL, 1, 1563434561, 1563434561, 1, 1, 1, NULL, 0),
(1, 44, 43, 'Reports', 0, 0, NULL, 1, 1563434561, 1563434561, 1, 1, 0, NULL, 0),
(1, 45, 15, 'S', 0, 17, NULL, 1, 1570702804, 1570702804, 1, 1, 1, NULL, 0),
(1, 46, 45, 'John Smith (1)', 0, 17, NULL, 1, 1570702804, 1570702809, 1, 1, 1, NULL, 0),
(1, 47, 11, 'Demo', 0, 111, NULL, 1, 1570708814, 1570708815, 1, 1, 1, NULL, 0),
(1, 48, 47, '[001] Develop Rocket 2000', 0, 111, NULL, 1, 1570708815, 1570708822, 1, 1, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `fs_folders_custom_fields`
--

CREATE TABLE `fs_folders_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fs_folders_custom_fields`
--

INSERT INTO `fs_folders_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, ''),
(7, ''),
(8, ''),
(9, ''),
(10, ''),
(11, ''),
(12, ''),
(13, ''),
(14, ''),
(15, ''),
(16, ''),
(17, ''),
(18, ''),
(19, ''),
(20, ''),
(21, ''),
(22, ''),
(23, ''),
(24, ''),
(25, ''),
(26, ''),
(27, ''),
(28, ''),
(29, ''),
(30, ''),
(31, ''),
(32, ''),
(33, ''),
(34, ''),
(35, ''),
(36, ''),
(37, ''),
(38, ''),
(39, ''),
(42, ''),
(43, ''),
(44, ''),
(45, ''),
(46, ''),
(47, ''),
(48, '');

-- --------------------------------------------------------

--
-- Table structure for table `fs_folder_pref`
--

CREATE TABLE `fs_folder_pref` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_new_files`
--

CREATE TABLE `fs_new_files` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_notifications`
--

CREATE TABLE `fs_notifications` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_notification_messages`
--

CREATE TABLE `fs_notification_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `modified_user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `arg1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `arg2` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_shared_cache`
--

CREATE TABLE `fs_shared_cache` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_shared_root_folders`
--

CREATE TABLE `fs_shared_root_folders` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_statuses`
--

CREATE TABLE `fs_statuses` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_status_history`
--

CREATE TABLE `fs_status_history` (
  `id` int(11) NOT NULL DEFAULT 0,
  `link_id` int(11) NOT NULL DEFAULT 0,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fs_templates`
--

CREATE TABLE `fs_templates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  `extension` char(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fs_templates`
--

INSERT INTO `fs_templates` (`id`, `user_id`, `name`, `acl_id`, `content`, `extension`) VALUES
(1, 1, 'Microsoft Word document', 46, 0x504b03041400080808000248efbfbd420000000000000000000000000b0000005f72656c732f2e72656c73efbfbdefbfbd4d4b03410cefbfbdefbfbdefbfbd1543efbfbdefbfbd6c2befbfbdefbfbdefbfbdefbfbd22426f22efbfbd07efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd073369efbfbdefbfbdefbfbd410aefbfbd50efbfbdefbfbdc7bc79efbfbdefbfbd1cefbfbd6defbfbdefbfbdefbfbd4eefbfbdefbfbdefbfbd41c3aa69417130d1ba306a78efbfbd3d2f1f60efbfbd2fefbfbd573eefbfbdefbfbd4aefbfbd5c2aefbfbdde84efbfbd611249efbfbdefbfbdefbfbd4cefbfbd343171efbfbdefbfbd21664f52efbfbd3c6222efbfbdefbfbdefbfbd71ddb6efbfbdefbfbd7f32efbfbdefbfbd31efbfbdefbfbd6aefbfbd5befbfbd02efbfbdefbfbd48efbfbd37367a16efbfbd24efbfbd26665eefbfbd5cefbfbdefbfbd382e154e7964efbfbd60efbfbd79efbfbd71efbfbd6a34efbfbd0c785d68efbfbd7befbfbd380cefbfbdefbfbd533447efbfbd41efbfbd79efbfbd593858efbfbdefbfbdefbfbd28efbfbd5b4677efbfbd69346f7ccbbcefbfbd6cefbfbd5eefbfbdcda2efbfbdefbfbd1befbfbdefbfbd504b0708efbfbdefbfbd0123efbfbd0000003d020000504b03041400080808000248efbfbd420000000000000000000000001c000000776f72642f5f72656c732f646f63756d656e742e786d6c2e72656c73efbfbdefbfbd4d0aefbfbd3010efbfbdefbfbdefbfbd22efbfbddea65510efbfbdefbfbd6e44702befbfbd0031efbfbdefbfbdefbfbd3609efbfbd287a7b03efbfbd5a28efbfbdefbfbdefbfbdefbfbd7defbfbd312f5f5fefbfbdefbfbd5defbfbd076defbfbdefbfbd2c49efbfbdefbfbd51efbfbdd2a6117028efbfbdefbfbd25efbfbdefbfbd49efbfbdefbfbd4e525c09efbfbd76efbfbdefbfbd1b1304efbfbd446eefbfbd79502defbfbd3224d6a1efbfbdefbfbdefbfbdefbfbd5e522c7dc39d5427efbfbd20efbfbdefbfbdefbfbdefbfbd4f06140326efbfbd5502efbfbdefbfbdca80efbfbd37efbfbdefbfbdefbfbd6d5d6befbfbd1befbfbdefbfbd3d1a1aefbfbdefbfbdefbfbd6e1defbfbd48efbfbdefbfbd4112efbfbdefbfbd013e2e3fefbfbdefbfbd7c6d0defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3f40efbfbdefbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd49efbfbd07efbfbd1677504b0708efbfbd2f30efbfbdefbfbd00000013020000504b03041400080808000248efbfbd4200000000000000000000000011000000776f72642f73657474696e67732e786d6c45efbfbd4b0eefbfbd300c44efbfbdefbfbd22efbfbd1eefbfbdefbfbdefbfbd53efbfbdefbfbdefbfbd02efbfbd01426befbfbd526247efbfbdefbfbdefbfbdefbfbd092befbfbdefbfbd37337aefbfbdefbfbd2b45efbfbdefbfbd2223efbfbdefbfbd66efbfbdefbfbd20efbfbd3cefbfbd74efbfbd703e1defbfbd1b30efbfbdefbfbdefbfbd10efbfbdefbfbdefbfbd1b05efbfbdefbfbd6c37efbfbdefbfbdefbfbdefbfbd25efbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbd6a6eefbfbdefbfbdefbfbdefbfbd29c882335265572e2968efbfbdefbfbd66272e432edca348efbfbdefbfbd68efbfbdcead6c0a2341572f3fefbfbdefbfbd4c6defbfbdefbfbd2369efbfbd691cefbfbd1f18efbfbd1a1e514fefbfbd7254ceb5efbfbd0cefbfbdefbfbdefbfbd6d7fefbfbdefbfbd5defbfbd2f504b070865efbfbdefbfbd22efbfbd000000efbfbd000000504b03041400080808000248efbfbd4200000000000000000000000012000000776f72642f666f6e745461626c652e786d6cefbfbdefbfbdefbfbd4eefbfbd300cefbfbdefbfbd3c45efbfbd3b4befbfbd014dd5ba090971efbfbd00efbfbd01efbfbdefbfbd5d23257115efbfbdefbfbdefbfbd3d59db9d28efbfbd40efbfbd25efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd59efbfbd636043efbfbdefbfbd0fefbfbd420aefbfbdefbfbd6aefbfbd4fefbfbdefbfbd38efbfbdefbfbd6fefbfbdefbfbd08efbfbd064b1e2b3920efbfbdefbfbdefbfbd6eefbfbdca867c64efbfbdefbfbd3defbfbdefbfbdefbfbd6defbfbd5defbfbd14efbfbd161defbfbdefbfbd3aefbfbdefbfbdefbfbd507010efbfbd37efbfbd1435efbfbdefbfbdefbfbd4cefbfbdd3a1efbfbd6a5d14efbfbd2aefbfbdefbfbdefbfbdefbfbddc9aefbfbdefbfbdec96ae714b14efbfbd2eefbfbd46efbfbdefbfbdefbfbdefbfbdcf81efbfbd7237efbfbd13efbfbdefbfbdefbfbd72efbfbd71efbfbdefbfbd15efbfbd782307efbfbd40efbfbd1018cf9a1e6c25efbfbd42efbfbd710eefbfbdefbfbdc3a51a46efbfbdefbfbdefbfbd4cefbfbdefbfbdefbfbdefbfbd433070efbfbd786eefbfbd09efbfbd03efbfbd3eefbfbd23efbfbd45efbfbdefbfbdd6acefbfbd2c59462defbfbdefbfbdefbfbd30efbfbd1375efbfbd2d3737efbfbdefbfbdefbfbd2defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd37504b0708efbfbdefbfbd5a5d010100000c030000504b03041400080808000248efbfbd420000000000000000000000000f000000776f72642f7374796c65732e786d6cefbfbd54516fefbfbd30107edfafefbfbdefbfbd4e0368efbfbd143554efbfbdefbfbd0209efbfbdefbfbdd0bd1fefbfbd41efbfbd39efbfbdefbfbd731ae8af9f1defbfbd6aefbfbdefbfbd0678efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3befbfbdefbfbdefbfbd3613efbfbd0b1aefbfbd4a46efbfbd73efbfbd6601efbfbd58255c6e22efbfbdefbfbd7c6cefbfbd59401664024249efbfbdefbfbd0eefbfbd3d0cefbfbdefbfbd1703efbfbd3befbfbd14efbfbd7849efbfbd2262efbfbdefbfbd7a10efbfbd14efbfbdefbfbd01efbfbd28efbfbdefbfbdddadefbfbdefbfbdefbfbdefbfbdefbfbdd984efbfbd32efbfbd362a4622efbfbd3e1361efbfbdefbfbdefbfbd1970c98675c2a05aefbfbd49efbfbdca8d2befbfbd18d89d76efbfbdefbfbd60606340efbfbdefbfbdefbfbdefbfbdefbfbd7befbfbdefbfbdefbfbdefbfbd0f5c432e6cefbfbdefbfbd112c2cefbfbd716befbfbdefbfbd2a5169d7bfefbfbd5f0aefbfbdefbfbd62efbfbdefbfbd354aefbfbd6e6b10544553efbfbdefbfbd7175efbfbd72efbfbd263befbfbdefbfbdefbfbdefbfbdcb9aefbfbd740aefbfbd72efbfbd6a7974efbfbd1b50efbfbd79c4963c73efbfbdccb108efbfbd5406efbfbdefbfbd296233efbfbd721b4cefbfbd4cefbfbd3721efbfbd1d11070fefbfbd0fefbfbdce830548efbfbd17efbfbd48d2912c6579efbfbd12efbfbdefbfbd05efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd75efbfbdd69631efbfbdefbfbd09efbfbd1b675befbfbdc4a54f796b3a3fefbfbdefbfbd356defbfbd4b5319efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd40efbfbd4aefbfbdd39275efbfbd35efbfbd6cefbfbdefbfbd6c5fefbfbd0a08efbfbdefbfbdefbfbd601fefbfbdefbfbdefbfbd0eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd67efbfbd5743efbfbd4b78efbfbdefbfbd685c4777efbfbd065aefbfbd6b44efbfbd1cefbfbd6b06efbfbd754139efbfbdefbfbd5befbfbd61137cefbfbdefbfbdefbfbd701017efbfbdefbfbdefbfbd7d2f5aefbfbdefbfbd68efbfbd33efbfbdefbfbd35efbfbd7eefbfbd39efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7f46efbfbdefbfbd74efbfbdefbfbdefbfbdefbfbd09efbfbd19277b1a6c0defbfbd23efbfbdefbfbdefbfbdd09eefbfbdefbfbd1d70efbfbd64efbfbd2600c6a0efbfbdefbfbdefbfbd1708efbfbd3f14efbfbdefbfbd6c332e71efbfbd672b37efbfbd1befbfbd1c7fefbfbdefbfbdefbfbd1cefbfbd18efbfbdefbfbd774cefbfbd67efbfbd19efbfbdefbfbd35efbfbd762a13efbfbd5eefbfbdefbfbd5d0366efbfbd6cefbfbd7a47efbfbd7f504b0708d59471efbfbd18020000efbfbd070000504b03041400080808000248efbfbd4200000000000000000000000011000000776f72642f646f63756d656e742e786d6cefbfbd52efbfbd6eefbfbd3010efbfbdefbfbd2b22efbfbdefbfbdefbfbd54efbfbd46042eefbfbdefbfbd5648efbfbd0f30efbfbd26efbfbd647b237b21d0afefbfbd4d1eefbfbd4befbfbdc59befbfbdccbe26efbfbdefbfbd5cefbfbd4eefbfbdefbfbd42efbfbdefbfbdefbfbd34650958efbfbdefbfbdefbfbd55cebe0f1fefbfbd254b3c095b08efbfbd16727605efbfbd36efbfbd55efbfbd15284f062c25efbfbdefbfbdefbfbd19efbfbdefbfbdefbfbd6cefbfbd650d46efbfbdefbfbd51d2a1c79226124defbfbd65efbfbd24efbfbdefbfbd192e673551efbfbd71efbfbd274defbfbd011befbfbd12efbfbd1114efbfbdefbfbd78efbfbdefbfbdefbfbd7befbfbdefbfbd345d70075a50efbfbdefbfbdd7aaefbfbd43efbfbdefbfbd7fefbfbdefbfbd460fefbfbdefbfbdefbfbdefbfbd2defbfbdefbfbd7128efbfbdefbfbd60efbfbdefbfbd5d5f23efbfbd1defbfbdefbfbdefbfbd2716efbfbd75c68cefbfbdce8513efbfbd43cbbfefbfbd6c3befbfbdefbfbdefbfbdefbfbd472cefbfbd3136efbfbd67efbfbd6e614f570d49efbfbdefbfbdefbfbdce99efbfbd20657cefbfbde2a3a27befbfbd377fefbfbdefbfbd22521e24754aefbfbd3663250b17da890a62efbfbd20efbfbdefbfbd3fefbfbdefbfbdc3b92cefbfbdefbfbd701f6d3475367b4f17efbfbdefbfbd53efbfbd24efbfbd48efbfbd2652efbfbd2a1109efbfbd5f4f23efbfbd4e740735efbfbd62041a4aefbfbd273955efbfbd0fefbfbdefbfbdefbfbd41efbfbdefbfbdefbfbd640edda8efbfbd09efbfbd02efbfbd3242776c3476e790863d4aefbfbd7defbfbd04efbfbdefbfbdefbfbdca8575efbfbd210defbfbd76efbfbd63efbfbdefbfbd60041fefbfbdefbfbdefbfbdefbfbd5fefbfbd02504b0708efbfbdefbfbdefbfbd6c7901000036030000504b03041400080808000248efbfbd4200000000000000000000000010000000646f6350726f70732f6170702e786d6cefbfbdefbfbd3d6befbfbd301006e0bdbfc288efbfbdefbfbd54634c08efbfbd4243efbfbd146807efbfbd7433efbfbd744e14efbfbd0fefbfbd7370efbfbd7defbfbd16efbfbdefbfbd1defbfbdefbfbd7878efbfbdefbfbd76efbfbd5371efbfbdefbfbdefbfbd771d79efbfbd1829efbfbd29efbfbdefbfbd3b76efbfbd7f29d7a44828efbfbdefbfbdefbfbd77d0912b24efbfbd150fefbfbd2defbfbd00110defbfbd220b2e75efbfbd1836efbfbd2675022b53efbfbd63efbfbdefbfbdefbfbd472b31efbfbdefbfbd48efbfbd381a05efbfbd5eefbfbd161cd29aefbfbdefbfbdc282efbfbd34efbfbd32efbfbdefbfbdefbfbd57efbfbd5cefbfbdefbfbdefbfbd5fefbfbdefbfbd217befbfbdefbfbd1eefbfbdefbfbd1b0befbfbd717a1befbfbd5308efbfbd5112efbfbdefbfbd626f0e115e7f34efbfbd54efbfbdefbfbdefbfbd7aefbfbd376e5eefbfbdefbfbd753befbfbd4d71efbfbd30efbfbd6750481befbfbd2c5befbfbd6633efbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd23efbfbd05504b070828efbfbd0eefbfbd00000068010000504b03041400080808000248efbfbd4200000000000000000000000011000000646f6350726f70732f636f72652e786d6c6defbfbd5b4fefbfbd3018efbfbdefbfbdefbfbd154befb7b62044efbfbd6d5c68efbfbdefbfbdefbfbd44efbfbdefbfbdefbfbdefbfbdefbfbd1cefbfbdefbfbdefbfbdefbfbd32efbfbdefbfbdefbfbd03261aefbfbdefbfbdefbfbd7defbfbdefbfbdf0968bbdefbfbd1d382fefbfbdefbfbd102d08efbfbd400befbfbd48efbfbd56efbfbd75efbfbdefbfbdefbfbd50efbfbd03efbfbd0def8c860a1defbfbdefbfbd457d530acb8471efbfbdefbfbd051724efbfbd2cefbfbdefbfbd67efbfbd56681befbfbd65187befbfbd05efbfbd7d11091defbfbd0fefbfbd140f71742defbfbd5c7cefbfbd16efbfbdefbfbdefbfbd39561078efbfbd03efbfbd49efbfbdefbfbdd1884eefbfbd46efbfbd4aefbfbdefbfbdefbfbd41efbfbd080c1d28efbfbdefbfbd635a50efbfbdefbfbd2a190e16efbfbdefbfbd38efbfbd177400efbfbdefbfbd5578484672efbfbdefbfbd48efbfbd7d5fefbfbdd381efbfbdefbfbdefbfbdefbfbd7defbfbdefbfbd323c35efbfbd3a7defbfbd0054efbfbd2735130e78efbfbd26efbfbd0276efbfbdefbfbd3979efbfbd3e3cefbfbdefbfbdefbfbdefbfbd103aefbfbdefbfbd2cefbfbdefbfbd35256c76efbfbd6e630b6453efbfbd7fefbfbdefbfbd3cefbfbdefbfbdefbfbd57efbfbdefbfbdefbfbdd2b92e56efbfbdefbfbd314befbfbd38efbfbdefbfbd54694d4a7c390eefbfbdefbfbdefbfbdefbfbd1f504b0708efbfbdefbfbd1b691f01000004020000504b03041400080808000248efbfbd42000000000000000000000000130000005b436f6e74656e745f54797065735d2e786d6cefbfbdefbfbd314fefbfbd3010efbfbdefbfbdefbfbdefbfbdefbfbd2b4a1c181042493a203142efbfbd3023635f12efbfbdc4b67cefbfbdefbfbdefbfbdefbfbd73682304efbfbd11efbfbdefbfbd58efbfbdefbfbdefbfbd7defbfbde7938befbfbd66efbfbd3578efbfbdd694efbfbd3cefbfbd5902465aefbfbd4d5befbfbdefbfbdefbfbd36efbfbd62efbfbd6a51efbfbd5b07efbfbdefbfbdefbfbd60c9ba10efbfbd35efbfbd283b180466d681efbfbdefbfbdefbfbdefbfbd4104efbfbdefbfbdefbfbd3b21efbfbd450befbfbd22efbfbd2fefbfbdefbfbd26efbfbd0969efbfbd1eefbfbd2aefbfbd09e7b58264257cefbfbd1303efbfbdefbfbd3f7aefbfbd677165efbfbdefbfbd7b4164efbfbd4c38efbfbd6b2902efbfbdefbfbd6befbfbd3eefbfbdefbfbd1d2956efbfbd1aefbfbdefbfbd331230efbfbd3defbfbdefbfbd7aefbfbdefbfbd292b5f06026524efbfbd37344208efbfbd5cefbfbdd09fefbfbd6cefbfbd680953efbfbdefbfbde6bc95efbfbd487e74efbfbdefbfbdefbfbd6cefbfbdefbfbdefbfbdefbfbd78efbfbdefbfbdefbfbd1926efbfbdefbfbd3eefbfbd6d0f7fd185efbfbd7716efbfbdefbfbdefbfbd4f1b603a19efbfbd36efbfbd72efbfbd70efbfbd434eefbfbdefbfbd63efbfbdefbfbd2a15efbfbdefbfbdefbfbd38efbfbd411fefbfbdefbfbdc496efbfbdefbfbd620cefbfbdefbfbd1fefbfbdefbfbd1217051fefbfbdefbfbdefbfbd0d504b070863efbfbd612a0100005e040000504b010214001400080808000248efbfbd42efbfbdefbfbd0123efbfbd0000003d0200000b00000000000000000000000000000000005f72656c732f2e72656c73504b010214001400080808000248efbfbd42efbfbd2f30efbfbdefbfbd000000130200001c0000000000000000000000000012010000776f72642f5f72656c732f646f63756d656e742e786d6c2e72656c73504b010214001400080808000248efbfbd4265efbfbdefbfbd22efbfbd000000efbfbd000000110000000000000000000000000021020000776f72642f73657474696e67732e786d6c504b010214001400080808000248efbfbd42efbfbdefbfbd5a5d010100000c030000120000000000000000000000000005030000776f72642f666f6e745461626c652e786d6c504b010214001400080808000248efbfbd42d59471efbfbd18020000efbfbd0700000f0000000000000000000000000046040000776f72642f7374796c65732e786d6c504b010214001400080808000248efbfbd42efbfbdefbfbdefbfbd6c79010000360300001100000000000000000000000000efbfbd060000776f72642f646f63756d656e742e786d6c504b010214001400080808000248efbfbd4228efbfbd0eefbfbd00000068010000100000000000000000000000000053080000646f6350726f70732f6170702e786d6c504b010214001400080808000248efbfbd42efbfbdefbfbd1b691f01000004020000110000000000000000000000000073090000646f6350726f70732f636f72652e786d6c504b010214001400080808000248efbfbd4263efbfbd612a0100005e0400001300000000000000000000000000efbfbd0a00005b436f6e74656e745f54797065735d2e786d6c504b050600000000090009003c0200003c0c00000000, 'docx'),
(2, 1, 'Open-Office Text document', 47, 0x504b03041400000000004b3b1a395eefbfbd320c2700000027000000080000006d696d65747970656170706c69636174696f6e2f766e642e6f617369732e6f70656e646f63756d656e742e74657874504b03041400000000004b3b1a390000000000000000000000001a000000436f6e66696775726174696f6e73322f7374617475736261722f504b03041400080008004b3b1a3900000000000000000000000027000000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c0300504b0708000000000200000000000000504b03041400000000004b3b1a3900000000000000000000000018000000436f6e66696775726174696f6e73322f666c6f617465722f504b03041400000000004b3b1a390000000000000000000000001a000000436f6e66696775726174696f6e73322f706f7075706d656e752f504b03041400000000004b3b1a390000000000000000000000001c000000436f6e66696775726174696f6e73322f70726f67726573736261722f504b03041400000000004b3b1a3900000000000000000000000018000000436f6e66696775726174696f6e73322f6d656e756261722f504b03041400000000004b3b1a3900000000000000000000000018000000436f6e66696775726174696f6e73322f746f6f6c6261722f504b03041400000000004b3b1a390000000000000000000000001f000000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b03041400080008004b3b1a390000000000000000000000000b000000636f6e74656e742e786d6cefbfbd56efbfbd6e1b2114efbfbdefbfbd2b462cefbfbd1b63275d2453efbfbdefbfbd4a51efbfbd4a49174d5a754befbfbdefbfbd69794cefbfbdefbfbdefbfbd7f5f1eefbfbd314e3209efbfbd37efbfbdefbfbdefbfbd73efbfbdefbfbd73012f6f76efbfbd175befbfbd0d53efbfbd06efbfbdefbfbd1c14546245efbfbd5cefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd0adcac3e2c55efbfbd304c2befbfbd7027efbfbdefbfbd2556d2baefbfbdc2b1efbfbdefbfbdefbfbd6c0d3a2d2befbfbd0c33efbfbd44efbfbdefbfbdefbfbdefbfbd4aefbfbd540eefbfbd2a455761efbfbd183176cfb3efbfbd01efbfbdefbfbd2defbfbdefbfbd5cefbfbdc79e70efbfbd53efbfbdefbfbd01efbfbdefbfbdefbfbd467d2eefbfbd63efbfbdefbfbd29efbfbd51efbfbdefbfbdefbfbd65efbfbdefbfbdefbfbd45efbfbd3defbfbd62c799efbfbd5befbfbdefbfbdefbfbd6d0561efbfbdefbfbdefbfbdefbfbd72efbfbdefbfbd1a2eefbfbdefbfbdefbfbd61efbfbd1d0befbfbd23efbfbdefbfbd340f28efbfbd21efbfbdefbfbd2f66efbfbd62efbfbdefbfbd035650efbfbd72efbfbdefbfbdd8b424d98927efbfbdefbfbdefbfbd4116efbfbd70efbfbd6cefbfbdefbfbd1defbfbd5d4f48efbfbd374867efbfbd4600efbfbdefbfbd7b49efbfbdefbfbdefbfbd24295720efbfbdefbfbdefbfbdefbfbd0adebbefbfbdefbfbd717f77efbfbd052d72efbfbdefbfbdefbfbd13efbfbdefbfbd666defbfbd36233aefbfbd2befbfbdefbfbd523d211eefbfbd50efbfbdefbfbd7cefbfbd09efbfbd71efbfbdefbfbddf84efbfbdefbfbd59efbfbd13387e13efbfbd11c7a3efbfbd4aefbfbd26efbfbdefbfbd2defbfbd43efbfbd74efbfbdefbfbd746c7c2fefbfbdefbfbd205cefbfbd383defbfbd0defbfbd4cefbfbdefbfbdefbfbdefbfbd016fefbfbd4047307b1f5c32692cefbfbd47651a46efbfbdefbfbd30efbfbd460f5eefbfbd5d4b35efbfbd3620efbfbd34efbfbd51efbfbd7129efbfbd54efbfbdefbfbdefbfbd04d1990339efbfbdefbfbd1760355cefbfbdefbfbd4203efbfbd40efbfbdefbfbd41efbfbdefbfbdefbfbd626e56efbfbd78efbfbdefbfbd7011c7beefbfbd1a3cefbfbd324cefbfbdefbfbdefbfbd0f25efbfbd04efbfbd3b3f035430efbfbdefbfbdefbfbd47efbfbd2aefbfbdefbfbd192e064171efbfbdefbfbdefbfbdefbfbd35efbfbd6e6fefbfbd657defbfbd7744efbfbdefbfbd62770eefbfbd48337f7902efbfbd76695f1cefbfbdefbfbd52efbfbd10efbfbd5eefbfbdefbfbdcc9873efbfbdefbfbdefbfbd7fd0afefbfbd7840efbfbd4c2aefbfbd6032efbfbd307b63efbfbd78efbfbd263865efbfbd21efbfbd3aefbfbd54efbfbd0cefbfbd214f62efbfbdefbfbd22efbfbd71efbfbd1fefbfbdefbfbd323c6fefbfbdefbfbdefbfbdefbfbd1b3e267a192c42efbfbd30efbfbd72efbfbd2f5567efbfbd1b414befbfbd4e17efbfbdefbfbd6befbfbd301d65efbfbdefbfbd7967efbfbd760528efbfbdefbfbd3d2befbfbdefbfbdefbfbdf3b2b89f6727efbfbdefbfbdefbfbd7170605aefbfbd3652efbfbdefbfbd65243eefbfbd234fefbfbd26efbfbd77517c78efbfbd0befbfbdefbfbdcbb4efbfbd0f504b0708efbfbd003d40efbfbd02000073090000504b03041400080008004b3b1a390000000000000000000000000a0000007374796c65732e786d6cefbfbd594befbfbdefbfbd3610efbfbdefbfbd57182aefbfbd1b6dcbbbefbfbdefbfbdefbfbdefbfbd0645efbfbdefbfbd05efbfbd1cefbfbdefbfbdd780efbfbd68efbfbd09450a2465efbfbdefbfbdefbfbd19efbfbdefbfbd44cb9257efbfbd48efbfbd3d2c20efbfbd70efbfbdefbfbd3cefbfbdefbfbdefbfbd37efbfbdefbfbd4d76442a2aefbfbd5d144fefbfbdd184efbfbd44efbfbdefbfbd6fefbfbd7f3fefbfbdefbfbd6eefbfbd37efbfbd3fefbfbd16efbfbd0d4defbfbd2a1549efbfbd13efbfbdefbfbdefbfbd0746efbfbd043673efbfbd72c4bbefbfbdefbfbd7c25efbfbdefbfbd6aefbfbd714eefbfbd4a272b5110efbfbd37efbfbd42efbfbd55efbfbd56efbfbdefbfbdefbfbdefbfbd2d73efbfbd5befbfbdefbfbd1eefbfbdefbfbdefbfbd1eefbfbdefbfbdefbfbdefbfbd2d73efbfbd3befbfbdefbfbd1aefbfbdefbfbdefbfbdefbfbd4defbfbdefbfbd1b3176efbfbd5e31efbfbd11281179efbfbd35efbfbdefbfbdefbfbd33cabfefbfbd45efbfbdefbfbdefbfbd6a36efbfbdefbfbd6a5a5d4defbfbdefbfbdefbfbdefbfbdefbfbd7239efbfbdefbfbd0670efbfbdefbfbd15efbfbd64efbfbd2b4d66efbfbd11efbfbd4cefbfbdefbfbd693cefbfbd39efbfbd782c3eefbfbd1b42efbfbd65efbfbd2672efbfbd69efbfbdefbfbd275e55efbfbdefbfbdefbfbdefbfbd6d074cefbfbd6458efbfbdefbfbd0defbfbd7cefbfbddeab74efbfbd7befbfbdefbfbd706fefbfbd7536efbfbdefbfbdefbfbdefbfbd3b20efbfbd7fefbfbddeb6efbfbd20efbfbd0cef91a91249efbfbdefbfbdefbfbd74efbfbdefbfbd7e214403efbfbd6c70096aefbfbd2eefbfbdefbfbdefbfbdefbfbd0eefbfbdefbfbdefbfbdec95a4efbfbdc8803d39cb9e60efbfbd34161779efbfbdd1802fefbfbd0107223b13efbfbdd1a42e2141d98aefbfbd7b5fefbfbd3602efbfbdefbfbd062704efbfbd2461efbfbdefbfbdefbfbdefbfbdefbfbd6679e2be8defbfbdeea28f142c35794fefbfbdefbfbd3f22efbfbd3cefbfbd403079d69cefbfbdefbfbd5defbfbd332eefbfbdefbfbdefbfbdefbfbdefbfbd16efbfbdc99168c38fefbfbdefbfbd1349efbfbdc8b2efbfbdefbfbd7214542710143b2cefbfbdefbfbd24efbfbdefbfbd3cefbfbddf80efbfbdefbfbd00efbfbdefbfbdc3aa5545efbfbd7aefbfbdefbfbd3fefbfbd67efbfbd5f39efbfbdefbfbdefbfbd1aefbfbd48efbfbd33efbfbd1aefbfbd34efbfbd1fefbfbd341b7261efbfbdeeba8ec79eefbfbd0d2e59dd8befbfbdefbfbd1aefbfbd56efbfbd22efbfbd49efbfbd79efbfbd6f5448efbfbd41efbfbd2938efbfbd54efbfbdefbfbd702a2a04efbfbd15efbfbd687f17cda75709efbfbdefbfbd211e3a440defbfbd02417525481538efbfbdda8e3221efbfbd57efbfbdefbfbdefbfbd615ddc9e65efbfbd1918efbfbd292b24efbfbd58efbfbd27efbfbd3d326befbfbd3038474575efbfbd5cefbfbdefbfbd60efbfbdefbfbd2828efbfbdefbfbdefbfbd42efbfbd7d1cefbfbdefbfbd235c6a61744068d09408c78a59efbfbd61efbfbdefbfbdefbfbd584befbfbdefbfbd13290d2ed79e62ca81efbfbdefbfbdefbfbd14efbfbd33efbfbdefbfbdefbfbd280c284fefbfbdefbfbd52efbfbd56111ec683efbfbd18efbfbdefbfbdefbfbdefbfbd45efbfbd4cefbfbd0cefbfbd6eefbfbd0defbfbdd394efbfbdefbfbd19efbfbdefbfbd55efbfbd0826efbfbd4f6959427defbfbd08efbfbd48d1afefbfbd345e14daae31ccb725efbfbdefbfbd126776211125efbfbd12efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd4443efbfbd435fefbfbdefbfbd16efbfbd1318efbfbdefbfbdefbfbd4450efbfbdefbfbdefbfbd56efbfbdefbfbd4defbfbdefbfbdc78befbfbdd4afefbfbdefbfbd27d58a3cefbfbd0befbfbd23efbfbd743e46efbfbd1e70476843efbfbd11efbfbdd0acefbfbdd6ac47793526efbfbd1a3f4467030a4cefbfbd1defbfbdefbfbd7068d48223efbfbdefbfbd146c66efbfbd180cefbfbdefbfbdefbfbd362718197745efbfbd135d3aefbfbd15efbfbdefbfbd7defbfbdefbfbdefbfbd070f07efbfbd0f28efbfbd52484f6eefbfbdefbfbdefbfbdefbfbd4defbfbd66efbfbd71efbfbd1660efbfbd3665efbfbd105fefbfbdefbfbd06efbfbdefbfbdefbfbdefbfbd40efbfbdefbfbd47efbfbd717defbfbdefbfbd516a1537efbfbd7656efbfbd4befbfbd72186aefbfbd17efbfbd6eefbfbd49efbfbd63ca91efbfbdefbfbdefbfbd205cefbfbd3015efbfbdefbfbd3a2cefbfbd4814efbfbdefbfbd082b1a23610cefbfbd07efbfbd5a48efbfbd1726e8a0904304315c2813efbfbdefbfbd55efbfbdefbfbdefbfbd3aefbfbd61efbfbdefbfbdefbfbd5f0829efbfbd165befbfbd3373efbfbd3719efbfbdefbfbdefbfbd50efbfbd0befbfbd0fefbfbd4f29efbfbd69345828efbfbdefbfbd18560aefbfbd4132efbfbdefbfbd752aefbfbd2fefbfbdefbfbd20efbfbd07efbfbdefbfbd42efbfbd3845efbfbd50efbfbdefbfbddb90efbfbd232c7c5aefbfbd3fefbfbd457aefbfbdefbfbd5049cbb1efbfbd7a03262b4cd7bd5ed8aedbaeefbfbdefbfbdefbfbdefbfbd560b0d395eefbfbd246b63db8defbfbdefbfbdc69855efbfbdefbfbd1eefbfbd2d41e1a8afefbfbdefbfbd7271efbfbdefbfbdefbfbdefbfbd32efbfbd57efbfbd6333efbfbd086903efbfbd40efbfbd333eefbfbdefbfbdefbfbd43efbfbdefbfbd49487eefbfbdefbfbdefbfbdefbfbdefbfbd3e1d7ddcb7efbfbd69efbfbd72efbfbd3331ca8cefbfbd11efbfbd73efbfbdefbfbdefbfbd637cefbfbd3b2e4c297c41efbfbd034aefbfbd1fefbfbd586defbfbd0c25efbfbdefbfbdefbfbdefbfbdc983efbfbd48efbfbdefbfbdefbfbd5befbfbdefbfbd08efbfbdefbfbdefbfbd7c38efbfbd067aefbfbd5b73efbfbd2a0a7771785befbfbd35efbfbd11d19aefbfbdefbfbd0e6defbfbdefbfbd7defbfbdefbfbdefbfbd5e0b18efbfbdefbfbdefbfbdefbfbd6eefbfbd7f41efbfbd512befbfbdefbfbd5eefbfbd15efbfbd0a5f2b59efbfbddabd217aefbfbd18efbfbd1156774707efbfbd2cefbfbdefbfbdefbfbd35efbfbd3932efbfbd150c25efbfbd39efbfbdc9af7a6befbfbdefbfbd3935efbfbdefbfbd3550efbfbd1b24efbfbd106fefbfbd240c691fefbfbd6b71efbfbdefbfbdefbfbd2e14efbfbdefbfbdefbfbde2bab9505cefbfbd2e14efbfbd2f17efbfbdefbfbdefbfbd42712d2f14573cefbfbdefbfbdefbfbd1defbfbd42efbfbd5c68efbfbdefbfbdefbfbdefbfbd0ddd96efbfbd3eefbfbd260d01d5ad6d23efbfbd36efbfbd7defbfbdefbfbd79efbfbd51efbfbd0eefbfbdd2a0efbfbd17efbfbd46efbfbd0aefbfbdefbfbdefbfbd43693b2f08efbfbdefbfbdefbfbd67060a46efbfbd1fcb9b13efbfbd4748783a04efbfbdefbfbd03efbfbdefbfbd455a047d6a06efbfbdefbfbd1befbfbdefbfbdefbfbdefbfbd72d98e47efbfbdefbfbd530b69efbfbdefbfbdefbfbd46efbfbd34efbfbd13697f68323531efbfbdefbfbd5a69efbfbdefbfbd3c3941264defbfbd27efbfbdefbfbdefbfbd16efbfbdefbfbd0fefbfbddda3efbfbd4defbfbdefbfbd510f4fefbfbd4e6729154defbfbdefbfbd32efbfbdefbfbd74efbfbd0eefbfbd0919efbfbdefbfbdccbcefbfbdefbfbd5767efbfbd582b010b6a2424efbfbdefbfbdefbfbdefbfbdefbfbd42c295efbfbdefbfbd7b711defbfbdefbfbd76efbfbdefbfbdefbfbd4e16650d69681eefbfbd19efbfbdefbfbd004439efbfbd37efbfbd31cf9676efbfbd5f3328527871efbfbd1aefbfbdefbfbd3cefbfbd6defbfbdefbfbdefbfbd436b0227efbfbdefbfbdefbfbd27efbfbdefbfbd3d3c7863efbfbd4e7d2c38efbfbd5c2aefbfbdefbfbdefbfbd62efbfbdefbfbd4b4856ef869befbfbdefbfbd31efbfbdefbfbdefbfbdefbfbd38efbfbd7f5138efbfbdefbfbd73efbfbd3f5446efbfbd19efbfbdd88f5978efbfbd60efbfbd54501b79efbfbdefbfbd561372efbfbd1a19efbfbdefbfbd7aefbfbd483a3befbfbd0831efbfbd211a047c47efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6f504b0708efbfbd45efbfbd7d060000efbfbd1f0000504b03041400000000004b3b1a39efbfbd67efbfbdefbfbd0e0400000e040000080000006d6574612e786d6c3c3f786d6c2076657273696f6e3d22312e302220656e636f64696e673d225554462d38223f3e0a3c6f66666963653a646f63756d656e742d6d65746120786d6c6e733a6f66666963653d2275726e3a6f617369733a6e616d65733a74633a6f70656e646f63756d656e743a786d6c6e733a6f66666963653a312e302220786d6c6e733a786c696e6b3d22687474703a2f2f7777772e77332e6f72672f313939392f786c696e6b2220786d6c6e733a64633d22687474703a2f2f7075726c2e6f72672f64632f656c656d656e74732f312e312f2220786d6c6e733a6d6574613d2275726e3a6f617369733a6e616d65733a74633a6f70656e646f63756d656e743a786d6c6e733a6d6574613a312e302220786d6c6e733a6f6f6f3d22687474703a2f2f6f70656e6f66666963652e6f72672f323030342f6f666669636522206f66666963653a76657273696f6e3d22312e31223e3c6f66666963653a6d6574613e3c6d6574613a67656e657261746f723e4f70656e4f66666963652e6f72672f322e34244c696e7578204f70656e4f66666963652e6f72675f70726f6a6563742f3638306d3137244275696c642d393331303c2f6d6574613a67656e657261746f723e3c6d6574613a696e697469616c2d63726561746f723e4d6572696a6e205363686572696e673c2f6d6574613a696e697469616c2d63726561746f723e3c6d6574613a6372656174696f6e2d646174653e323030382d30382d32365430393a32363a30323c2f6d6574613a6372656174696f6e2d646174653e3c6d6574613a65646974696e672d6379636c65733e303c2f6d6574613a65646974696e672d6379636c65733e3c6d6574613a65646974696e672d6475726174696f6e3e505430533c2f6d6574613a65646974696e672d6475726174696f6e3e3c6d6574613a757365722d646566696e6564206d6574613a6e616d653d22496e666f2031222f3e3c6d6574613a757365722d646566696e6564206d6574613a6e616d653d22496e666f2032222f3e3c6d6574613a757365722d646566696e6564206d6574613a6e616d653d22496e666f2033222f3e3c6d6574613a757365722d646566696e6564206d6574613a6e616d653d22496e666f2034222f3e3c6d6574613a646f63756d656e742d737461746973746963206d6574613a7461626c652d636f756e743d223022206d6574613a696d6167652d636f756e743d223022206d6574613a6f626a6563742d636f756e743d223022206d6574613a706167652d636f756e743d223122206d6574613a7061726167726170682d636f756e743d223022206d6574613a776f72642d636f756e743d223022206d6574613a6368617261637465722d636f756e743d2230222f3e3c2f6f66666963653a6d6574613e3c2f6f66666963653a646f63756d656e742d6d6574613e504b03041400080008004b3b1a39000000000000000000000000180000005468756d626e61696c732f7468756d626e61696c2e706e67efbfbd0cefbfbd73efbfbdefbfbdefbfbd626060efbfbdefbfbdefbfbd700902efbfbd5b1918181938d880efbfbdefbfbdefbfbdefbfbd1918efbfbdefbfbd7befbfbd38efbfbd54efbfbd797b692327efbfbd01cf810d7c3fefbfbd3fefbfbdefbfbdefbfbd74efbfbd43efbfbdefbfbdc39b77efbfbdefbfbd7eefbfbd32efbfbdefbfbd394b2678727256efbfbdefbfbd6fefbfbdca93efbfbdefbfbdefbfbdefbfbdd48e5f79326354705470efbfbd05efbfbdefbfbdefbfbdefbfbd330aefbfbd2a0b4cefbfbd0cefbfbdefbfbd7e2eefbfbd12efbfbd00504b0708efbfbdd783efbfbd7c000000efbfbd020000504b03041400080008004b3b1a390000000000000000000000000c00000073657474696e67732e786d6cefbfbd595173efbfbd380c7eefbfbd5fefbfbdefbfbd3b054a6fefbfbdcab4efbfbd04efbfbdecb1a5efbfbd01efbfbdefbfbdefbfbd37efbfbd08efbfbdd5b132efbfbd53efbfbddf9fefbfbd4e0befbfbd4b097eefbfbd4d6cc992efbfbdef9394ebafabefbfbdefbfbdefbfbdefbfbd5411efbfbd1befbfbd7e5eefbfbdefbfbd40041846627eefbfbd3d4eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3fefbfbd7136efbfbd0268efbfbd18efbfbd31085d51efbfbd352d5167efbfbd5defbfbd66efbfbdefbfbdefbfbd4befbfbd682253efbfbd6a0a16efbfbd6aeaa08909efbfbdcdb6efbfbdefbfbdefbfbd4defbfbd2c7befbfbdefbfbd78efbfbdefbfbd165a27efbfbd6a75efbfbd5cefbfbd2f1befbfbd28efbfbdefbfbdefbfbdefbfbdefbfbd55d5beefbfbd2c0d50cca2efbfbdefbfbdefbfbdefbfbdefbfbd6f5521efbfbd22efbfbd213befbfbd557651efbfbd5d56efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6f5c53efbfbd5a1b3f6cefbfbd6f5defbfbd0aefbfbdefbfbd4aefbfbd2136efbfbd39efbfbd1fefbfbdefbfbdefbfbd78efbfbdefbfbdefbfbd12efbfbdefbfbdefbfbd6b5ed1beefbfbd7b7eefbfbd7a5f02efbfbd60efbfbd6defbfbdefbfbd75426f22efbfbdefbfbd56efbfbdefbfbdefbfbd2befbfbd70efbfbd7defbfbdefbfbd22efbfbdefbfbd7aefbfbd5eefbfbdefbfbdefbfbdefbfbd28d48b22e1978defbfbdc69fefbfbd64efbfbd03efbfbd7c5178efbfbd7aefbfbdefbfbd48efbfbdefbfbd052e471052efbfbd4167efbfbdefbfbd1cd496efbfbd29220726efbfbdefbfbdefbfbd291cefbfbdefbfbd27efbfbd12efbfbd0aefbfbd31efbfbd7defbfbd67efbfbdefbfbdefbfbdefbfbd5762efbfbd542211efbfbd0aefbfbd5d5f1547efbfbdefbfbd43efbfbd21d78779efbfbd176e1d556949efbfbdefbfbd4c305f1c7fefbfbdefbfbdefbfbdefbfbd51efbfbdefbfbdefbfbdefbfbd2753efbfbd0855d194efbfbdefbfbd5cefbfbdefbfbd4fefbfbdefbfbd56efbfbd685fefbfbd341aefbfbdefbfbd2fefbfbd44efbfbd516befbfbdefbfbdefbfbd5fefbfbd38d9bf10efbfbd0949da8eefbfbd05efbfbdefbfbd7d61efbfbd7659efbfbd5116efbfbdefbfbdd78e14efbfbd5363efbfbd106808efbfbdefbfbd1e1cefbfbdefbfbd050fefbfbd26efbfbdefbfbd79efbfbd172f206239efbfbdefbfbdefbfbd07efbfbd64efbfbdefbfbdefbfbd33efbfbdefbfbdefbfbd493661140aefbfbd05060b4eefbfbdefbfbd43efbfbd183d02c3bbefbfbdefbfbd3fefbfbd41efbfbd7befbfbdefbfbd541befbfbdefbfbd06efbfbd6f043c540f693c05efbfbdefbfbd3125efbfbdefbfbdefbfbd72794c42efbfbdefbfbdefbfbd7f13efbfbd251cefbfbd2d4eefbfbd7aefbfbdefbfbd70efbfbd5cefbfbdefbfbd6e24efbfbd2633efbfbd470128744fefbfbd7417f1afa4ac05efbfbdefbfbd38efbfbdefbfbd4cefbfbd75726cefbfbdefbfbd1befbfbd011c7eefbfbd74efbfbdefbfbd4aefbfbdefbfbdefbfbd785712efbfbd41efbfbd70efbfbdefbfbd51efbfbd0c5902efbfbdefbfbd19efbfbd4eefbfbdefbfbdefbfbd14efbfbd50efbfbd5befbfbd1befbfbd6604142e7c65efbfbd301cefbfbd28efbfbdefbfbdefbfbd6e2851136c533cefbfbdefbfbd7a5b0b53efbfbdefbfbd1d0926efbfbd5eefbfbdefbfbd235b147470efbfbdefbfbdefbfbd5defbfbdefbfbd447befbfbd04efbfbd7eefbfbd310b514761efbfbd41efbfbd32efbfbd4eefbfbd43efbfbd4116efbfbdefbfbd276f31efbfbd37efbfbd46e58bb0cd997856efbfbd74efbfbd741dc683efbfbd5b7a7415efbfbd10efbfbdefbfbdefbfbdefbfbdefbfbd7024efbfbdefbfbdefbfbdefbfbd2360210aefbfbd13efbfbd41045304efbfbdefbfbd3f41efbfbd071defbfbd2e6eefbfbd747d5befbfbd750befbfbdefbfbd64efbfbdefbfbd7aefbfbd4f12efbfbd7e54206fefbfbd66efbfbd17efbfbd3525efbfbdcb841befbfbd17efbfbdefbfbd0d2906efbfbdefbfbd51efbfbdefbfbdefbfbdefbfbd55efbfbdefbfbdefbfbdefbfbd7cefbfbd620a1b1774efbfbd537774dfbeefbfbdefbfbd18efbfbd22d0a9efbfbd34efbfbd79341714efbfbd63efbfbdefbfbd1055efbfbdefbfbdefbfbdefbfbd6fefbfbdc3a3efbfbd57efbfbd65efbfbd2f02efbfbd6d08efbfbd242defbfbd5defbfbdefbfbdefbfbdefbfbdc28fefbfbd544aefbfbd2613efbfbd0618efbfbdefbfbd185319efbfbd604defbfbdefbfbd1fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd12efbfbd4aefbfbdefbfbd6c39efbfbdefbfbdefbfbd06c2b0efbfbdefbfbdefbfbd5b5419efbfbdefbfbd0eefbfbd4559efbfbdefbfbdefbfbdefbfbd6e0777efbfbd3fefbfbdefbfbd2e5befbfbd12424cefbfbdefbfbd10efbfbd0456efbfbd49efbfbd6420c8ad14efbfbd2eefbfbd5205031eefbfbdefbfbd031d317056efbfbdefbfbd19efbfbdefbfbdefbfbd44efbfbdefbfbd145eefbfbdc88defbfbd4cefbfbd05efbfbd18efbfbd4defbfbd5befbfbd49dfb6d3be1e6b2aefbfbd4eefbfbd57efbfbd36efbfbdefbfbdefbfbdefbfbdc982efbfbd08efbfbd5730efbfbdefbfbd5a4b535451efbfbdefbfbd452757efbfbd754112554235efbfbd2befbfbdefbfbdefbfbd5fefbfbd0fefbfbdefbfbd44efbfbdefbfbd392eefbfbd0eefbfbd443b4c04efbfbd1d70efbfbd7b2aefbfbdefbfbd3a66222c68efbfbdefbfbd0c1fefbfbdefbfbdefbfbd48efbfbdefbfbd666befbfbd36efbfbd29d28b7b2652efbfbdefbfbd12d8b31befbfbd50233075efbfbd0b4c301b4f38efbfbd1c0266efbfbdefbfbd4defbfbdefbfbd267d727436efbfbdefbfbd6f59efbfbdefbfbd626b4cefbfbdefbfbdefbfbdefbfbdefbfbd1704efbfbd15090a796aefbfbd54efbfbd175c4e75efbfbd51efbfbd1cefbfbd2c78efbfbd4b4cefbfbdefbfbdefbfbddea9efbfbdefbfbd1cefbfbdefbfbd32efbfbdefbfbd46efbfbdefbfbdefbfbdefbfbd7406efbfbd0618efbfbdd0bd5851efbfbd06efbfbd0b245213efbfbdefbfbdefbfbdefbfbdefbfbd6a3b6fefbfbdefbfbd7c0cefbfbdefbfbdefbfbd4cefbfbdefbfbd1f504b070874efbfbdefbfbdefbfbdefbfbd040000681e0000504b03041400080008004b3b1a39000000000000000000000000150000004d4554412d494e462f6d616e69666573742e786d6cefbfbdefbfbd4b6aefbfbd301040efbfbd3defbfbdefbfbdefbfbd56efbfbd553171022defbfbd04efbfbd0126efbfbdefbfbd11efbfbd6614efbfbdefbfbd570eefbfbdefbfbd36efbfbdefbfbd583b09efbfbdefbfbd4623efbfbd68efbfbdefbfbd5b53efbfbd30efbfbdefbfbdefbfbd134fcda3efbfbdefbfbd29efbfbd6b3776efbfbd63efbfbd5eefbfbdefbfbdefbfbdefbfbd6161efbfbdefbfbd01efbfbdefbfbdd3a0efbfbdefbfbd1cefbfbdefbfbdefbfbd48d1b51e4853efbfbdefbfbd22efbfbdefbfbd5a1fefbfbdefbfbd5e25efbfbdefbfbddbafefbfbdefbfbdc9b47cefbfbd2eefbfbd411befbfbdefbfbdefbfbd78efbfbd2e32efbfbd35efbfbd7c08efbfbd0908efbfbd6805efbfbdefbfbd3befbfbd37475773efbfbd6818efbfbd2c2eefbfbdefbfbd644c1defbfbdefbfbdefbfbdefbfbd42efbfbd25efbfbd4d79efbfbd6eefbfbd63efbfbdefbfbd20efbfbd59120327efbfbd402cefbfbd07efbfbdefbfbd60efbfbdefbfbd2855efbfbd713a62efbfbd62715711efbfbd603c3016efbfbd071f52efbfbd4f2015efbfbd473f46efbfbd72373defbfbd5e0cefbfbdde9b62706d6144efbfbdefbfbdefbfbd2d042aeab893efbfbdefbfbd5f507253efbfbd344937efbfbd5aefbfbd18efbfbd4fefbfbd484eefbfbd7a13efbfbdefbfbdefbfbdefbfbd62efbfbdefbfbd4b7c3048efbfbd632d32efbfbdefbfbd78efbfbdefbfbd64370eefbfbd21c9a76113efbfbd38377cefbfbdefbfbd2273efbfbd10cfa95defbfbd1fefbfbdefbfbdefbfbd13504b07083562efbfbd393e0100004a070000504b010214001400000000004b3b1a395eefbfbd320c27000000270000000800000000000000000000000000000000006d696d6574797065504b010214001400000000004b3b1a390000000000000000000000001a000000000000000000000000004d000000436f6e66696775726174696f6e73322f7374617475736261722f504b010214001400080008004b3b1a390000000002000000000000002700000000000000000000000000efbfbd000000436f6e66696775726174696f6e73322f616363656c657261746f722f63757272656e742e786d6c504b010214001400000000004b3b1a390000000000000000000000001800000000000000000000000000efbfbd000000436f6e66696775726174696f6e73322f666c6f617465722f504b010214001400000000004b3b1a390000000000000000000000001a0000000000000000000000000012010000436f6e66696775726174696f6e73322f706f7075706d656e752f504b010214001400000000004b3b1a390000000000000000000000001c000000000000000000000000004a010000436f6e66696775726174696f6e73322f70726f67726573736261722f504b010214001400000000004b3b1a390000000000000000000000001800000000000000000000000000efbfbd010000436f6e66696775726174696f6e73322f6d656e756261722f504b010214001400000000004b3b1a390000000000000000000000001800000000000000000000000000efbfbd010000436f6e66696775726174696f6e73322f746f6f6c6261722f504b010214001400000000004b3b1a390000000000000000000000001f00000000000000000000000000efbfbd010000436f6e66696775726174696f6e73322f696d616765732f4269746d6170732f504b010214001400080008004b3b1a39efbfbd003d40efbfbd020000730900000b000000000000000000000000002d020000636f6e74656e742e786d6c504b010214001400080008004b3b1a39efbfbd45efbfbd7d060000efbfbd1f00000a00000000000000000000000000efbfbd0400007374796c65732e786d6c504b010214001400000000004b3b1a39efbfbd67efbfbdefbfbd0e0400000e0400000800000000000000000000000000efbfbd0b00006d6574612e786d6c504b010214001400080008004b3b1a39efbfbdd783efbfbd7c000000efbfbd0200001800000000000000000000000000efbfbd0f00005468756d626e61696c732f7468756d626e61696c2e706e67504b010214001400080008004b3b1a3974efbfbdefbfbdefbfbdefbfbd040000681e00000c00000000000000000000000000efbfbd10000073657474696e67732e786d6c504b010214001400080008004b3b1a393562efbfbd393e0100004a0700001500000000000000000000000000efbfbd1500004d4554412d494e462f6d616e69666573742e786d6c504b0506000000000f000f00efbfbd030000371700000000, 'odt');

-- --------------------------------------------------------

--
-- Table structure for table `fs_versions`
--

CREATE TABLE `fs_versions` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `size_bytes` bigint(20) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `googleauth_secret`
--

CREATE TABLE `googleauth_secret` (
  `userId` int(11) NOT NULL,
  `secret` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `googleauth_secret`
--

INSERT INTO `googleauth_secret` (`userId`, `secret`, `createdAt`) VALUES
(6, 'DKZH26YEIBAHLX26', '2019-07-18 07:16:08');

-- --------------------------------------------------------

--
-- Table structure for table `go_address_format`
--

CREATE TABLE `go_address_format` (
  `id` int(11) NOT NULL,
  `format` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_advanced_searches`
--

CREATE TABLE `go_advanced_searches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `data` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_cache`
--

CREATE TABLE `go_cache` (
  `user_id` int(11) NOT NULL,
  `key` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mtime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_cf_setting_tabs`
--

CREATE TABLE `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_clients`
--

CREATE TABLE `go_clients` (
  `id` int(11) NOT NULL,
  `footprint` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `last_active` int(11) NOT NULL,
  `in_use` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_countries`
--

CREATE TABLE `go_countries` (
  `id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iso_code_2` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `iso_code_3` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_cron`
--

CREATE TABLE `go_cron` (
  `id` int(11) NOT NULL,
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
  `params` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_cron`
--

INSERT INTO `go_cron` (`id`, `name`, `active`, `minutes`, `hours`, `monthdays`, `months`, `weekdays`, `years`, `job`, `runonce`, `nextrun`, `lastrun`, `completedat`, `error`, `autodestroy`, `params`) VALUES
(1, 'Calendar publisher', 1, '0', '*', '*', '*', '*', '*', 'GO\\Calendar\\Cron\\CalendarPublisher', 0, 1561975200, 0, 0, NULL, 0, '[]'),
(2, 'Contract Expiry Notification Cron', 1, '2', '7', '*', '*', '*', '*', 'GO\\Projects2\\Cron\\IncomeNotification', 0, 1562050920, 0, 0, NULL, 0, '[]'),
(3, 'Close inactive tickets', 1, '0', '2', '*', '*', '*', '*', 'GO\\Tickets\\Cron\\CloseInactive', 0, 1562032800, 0, 0, NULL, 0, '[]'),
(4, 'Ticket reminders', 1, '*/5', '*', '*', '*', '*', '*', 'GO\\Tickets\\Cron\\Reminder', 0, 1561972200, 0, 0, NULL, 0, '[]'),
(5, 'Import tickets from IMAP', 1, '0,5,10,15,20,25,30,35,40,45,50,55', '*', '*', '*', '*', '*', 'GO\\Tickets\\Cron\\ImportImap', 0, 1561972200, 0, 0, NULL, 0, '[]'),
(6, 'Sent tickets due reminder', 1, '0', '1', '*', '*', '*', '*', 'GO\\Tickets\\Cron\\DueMailer', 0, 1562029200, 0, 0, NULL, 0, '[]'),
(7, 'Email Reminders', 1, '*/5', '*', '*', '*', '*', '*', 'GO\\Base\\Cron\\EmailReminders', 0, 1561972200, 0, 0, NULL, 0, '[]'),
(8, 'Calculate disk usage', 1, '0', '0', '*', '*', '*', '*', 'GO\\Base\\Cron\\CalculateDiskUsage', 0, 1562025600, 0, 0, NULL, 0, '[]');

-- --------------------------------------------------------

--
-- Table structure for table `go_cron_groups`
--

CREATE TABLE `go_cron_groups` (
  `cronjob_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_cron_users`
--

CREATE TABLE `go_cron_users` (
  `cronjob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_holidays`
--

CREATE TABLE `go_holidays` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `region` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `free_day` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_holidays`
--

INSERT INTO `go_holidays` (`id`, `date`, `name`, `region`, `free_day`) VALUES
(1, '2019-01-01', 'New Years Day', 'en', 1),
(2, '2019-01-06', 'Twelfth Day', 'en', 1),
(3, '2019-05-01', 'May Day', 'en', 1),
(4, '2019-08-15', 'Assumption Day', 'en', 1),
(5, '2019-10-03', 'German Unification Day', 'en', 1),
(6, '2019-10-31', 'Reformation Day', 'en', 1),
(7, '2019-11-01', 'All Saints\' Day', 'en', 1),
(8, '2019-12-25', 'Christmas Day', 'en', 1),
(9, '2019-12-26', 'Boxing Day', 'en', 1),
(10, '2019-03-04', 'Shrove Monday', 'en', 1),
(11, '2019-03-05', 'Shrove Tuesday', 'en', 1),
(12, '2019-03-06', 'Ash Wednesday', 'en', 1),
(13, '2019-04-19', 'Good Friday', 'en', 1),
(14, '2019-04-21', 'Easter Sunday', 'en', 1),
(15, '2019-04-22', 'Easter Monday', 'en', 1),
(16, '2019-05-30', 'Ascension Day', 'en', 1),
(17, '2019-06-09', 'Whit Sunday', 'en', 1),
(18, '2019-06-10', 'Whit Monday', 'en', 1),
(19, '2019-06-20', 'Feast of Corpus Christi', 'en', 1),
(20, '2019-11-20', 'Penance Day', 'en', 1);

-- --------------------------------------------------------

--
-- Table structure for table `go_links_ab_addresslists`
--

CREATE TABLE `go_links_ab_addresslists` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_ab_companies`
--

CREATE TABLE `go_links_ab_companies` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_ab_contacts`
--

CREATE TABLE `go_links_ab_contacts` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_bs_orders`
--

CREATE TABLE `go_links_bs_orders` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_cal_events`
--

CREATE TABLE `go_links_cal_events` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_em_links`
--

CREATE TABLE `go_links_em_links` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_fs_files`
--

CREATE TABLE `go_links_fs_files` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_fs_folders`
--

CREATE TABLE `go_links_fs_folders` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_pr2_projects`
--

CREATE TABLE `go_links_pr2_projects` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_ta_tasks`
--

CREATE TABLE `go_links_ta_tasks` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_links_ti_tickets`
--

CREATE TABLE `go_links_ti_tickets` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_link_descriptions`
--

CREATE TABLE `go_link_descriptions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_link_folders`
--

CREATE TABLE `go_link_folders` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `model_id` int(11) NOT NULL DEFAULT 0,
  `model_type_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_log`
--

CREATE TABLE `go_log` (
  `id` int(11) NOT NULL,
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
  `jsonData` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_log`
--

INSERT INTO `go_log` (`id`, `user_id`, `username`, `model`, `model_id`, `ctime`, `user_agent`, `ip`, `controller_route`, `action`, `message`, `jsonData`) VALUES
(1, 1, 'admin', 'GO\\Files\\Model\\File', '1', 1606124737, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.1 Safari/605.1.15', '172.18.0.1', 'files/folder/list', 'propedit', 'addressbook/Customers/contacts/C/Wile E. Coyote (2)/Demo letter.docx', '{\"mtime\":[1561972054,1578667113]}'),
(2, 1, 'admin', 'GO\\Files\\Model\\Folder', '17', 1606124737, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.1 Safari/605.1.15', '172.18.0.1', 'files/folder/list', 'update', 'addressbook/Customers/contacts/C/Wile E. Coyote (2)', '{\"mtime\":[1561972055,1578667113]}'),
(3, 1, 'admin', 'GO\\Addressbook\\Model\\Contact', '11', 1606124893, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.1 Safari/605.1.15', '172.18.0.1', 'addressbook/contact/submit', 'add', 'Bastard the Orphan\nCustomers', '{\"uuid\":\"b0508fa8-3b51-55be-8cf3-1e5845a5f381\",\"user_id\":1,\"addressbook_id\":3,\"first_name\":\"Bastard\",\"middle_name\":\"the\",\"last_name\":\"Orphan\",\"initials\":\"\",\"title\":\"\",\"suffix\":\"\",\"sex\":\"M\",\"email\":\"\",\"email2\":\"\",\"email3\":\"\",\"company_id\":0,\"department\":\"\",\"function\":\"\",\"home_phone\":\"\",\"work_phone\":\"\",\"fax\":\"\",\"work_fax\":\"\",\"cellular\":\"\",\"cellular2\":\"\",\"country\":\"\",\"state\":\"\",\"city\":\"\",\"zip\":\"\",\"address\":\"\",\"address_no\":\"\",\"ctime\":1606124892,\"mtime\":1606124892,\"muser_id\":1,\"salutation\":\"Dear Bastard\",\"email_allowed\":1,\"files_folder_id\":0,\"go_user_id\":0,\"photo\":\"\",\"action_date\":0,\"color\":\"000000\",\"homepage\":\"\",\"url_linkedin\":\"\",\"url_facebook\":\"\",\"url_twitter\":\"\",\"skype_name\":\"\",\"comment\":\"\",\"id\":11}'),
(4, 1, 'admin', 'GO\\Addressbook\\Model\\Contact', '11', 1606124912, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.1 Safari/605.1.15', '172.18.0.1', 'addressbook/contact/submit', 'update', 'Bastard the Orphan\nCustomers', '{\"mtime\":[1606124892,1606124912],\"Select (Select)\":[\"\",\"Removed option 4\"]}');

-- --------------------------------------------------------

--
-- Table structure for table `go_reminders`
--

CREATE TABLE `go_reminders` (
  `id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` int(11) NOT NULL,
  `vtime` int(11) NOT NULL DEFAULT 0,
  `snooze_time` int(11) NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT 0,
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_reminders_users`
--

CREATE TABLE `go_reminders_users` (
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mail_sent` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_saved_exports`
--

CREATE TABLE `go_saved_exports` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `export_columns` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `orientation` enum('V','H') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT 1,
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_saved_search_queries`
--

CREATE TABLE `go_saved_search_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sql` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_search_sync`
--

CREATE TABLE `go_search_sync` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `module` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `last_sync_time` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `go_settings`
--

CREATE TABLE `go_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_settings`
--

INSERT INTO `go_settings` (`user_id`, `name`, `value`) VALUES
(0, 'go_addressbook_export', '69'),
(0, 'projects_bill_item_template', '{project_name}: {registering_user_name} worked {units} hours on {date}'),
(0, 'projects_detailed_printout_on', 'true'),
(0, 'projects_payout_item_template', '{project_name}: {description} of {responsible_user_name} worked {units} hours in {days} days\n\nTotal: {total_price}. (You can use custom fields of the manager in this template with {col_x})'),
(0, 'projects_summary_bill_item_template', '{project_name} {description} at {registering_user_name}\nUnits:{units}\nDays:{days}'),
(0, 'projects_summary_payout_item_template', '{project_name} {description} of {responsible_user_name}\nUnits: {units}, Days: {days}'),
(0, 'tickets_bill_item_template', '{date} #{number} rate: {rate_name}\n{subject}'),
(0, 'uuid_namespace', 'ffd75afa-f2e0-4a70-9b98-f3b6bce0b967'),
(1, 'email_accounts_tree', '[\"root\",\"Zl8xX0lOQk9Y\",\"Zl8xX0lOQk9YLlNwYW0=\",\"Zl8xX0lOQk9YLmdpYW50\",\"Zl8xX0lOQk9YLlNlbnQ=\",\"Zl8xX0lOQk9YLnRlc3Q=\",\"Zl8xX0lOQk9YLnRlc3QuVGVzdE1haWw=\",\"Zl8xX0lOQk9YLlRlc3R5\",\"Zl8xX0lOQk9YLlRyYXNo\",\"Zl8xX1NlbnQ=\",\"Zl8xX0RyYWZ0cw==\",\"Zl8xX1RyYXNo\",\"Zl8xX1NwYW0=\",\"Zl8xX2RhamU=\",\"Zl8xX0hkZXNr\",\"Zl8xX0hlbGVu\",\"Zl8xX2h1c3RlbnNhZnQ=\",\"Zl8xX1Jvb3Q=\",\"Zl8xX1NBTEVTLUxlYWRz\",\"Zl8xX1RFU1QgQU4=\",\"Zl8xX3Rlc3QwMw==\",\"Zl8xX3Rlc3RhcmU=\",\"Zl8xX1RJQUdv\",\"Zl8xX1RvRG8=\",\"Zl8xX3Zwc2xhYl9vcmc=\",\"YWNjb3VudF80\",\"Zl80X0lOQk9Y\",\"Zl80X0lOQk9YL3Rlc3Q=\",\"Zl80X1NlbnQ=\",\"Zl80X1RyYXNo\",\"Zl80X1NwYW0=\",\"Zl80X09tc29yZ1xWYXJkT21zb3Jnc2tvbnRvciBOb3Jya8O2cGluZw==\",\"Zl80X3Rlc3Q=\"]'),
(1, 'email_always_request_notification', '0'),
(1, 'email_always_respond_to_notifications', '0'),
(1, 'email_font_size', '14px'),
(1, 'email_show_bcc', '0'),
(1, 'email_show_cc', '0'),
(1, 'email_skip_unknown_recipients', '0'),
(1, 'email_sort_email_addresses_by_time', '0'),
(1, 'email_use_plain_text_markup', '0'),
(1, 'GO\\Projects2\\Report\\ProjectsReport', '[]'),
(1, 'GO\\Projects2\\Report\\ProjectsReportLarge', '[]'),
(1, 'ms_3order_statuses', '12,11,10,9'),
(1, 'ms_books', '3,6,5,7,1,2,4'),
(1, 'ms_calendars', '1'),
(1, 'ms_pm-status-filter', '1,2,3'),
(1, 'ms_pr2_statuses', ''),
(1, 'ms_ti-types-grid', '1,2'),
(1, 'pr2_all_incomes_end', '0'),
(1, 'pr2_all_incomes_start_date', '0'),
(1, 'pr2_invoiceable_end', '1568549984'),
(1, 'pr2_invoiceable_start_date', '0'),
(1, 'projects2_tree_state', '[\"root\",1,2,3,4,5]'),
(2, 'email_always_request_notification', '0'),
(2, 'email_always_respond_to_notifications', '0'),
(2, 'email_font_size', '14px'),
(2, 'email_show_bcc', '0'),
(2, 'email_show_cc', '0'),
(2, 'email_skip_unknown_recipients', '0'),
(2, 'email_sort_email_addresses_by_time', '0'),
(2, 'email_use_plain_text_markup', '0'),
(2, 'ms_books', '5'),
(2, 'ms_ta-taskslists', '1'),
(2, 'tasks_filter', 'active'),
(3, 'email_always_request_notification', '0'),
(3, 'email_always_respond_to_notifications', '0'),
(3, 'email_font_size', '14px'),
(3, 'email_show_bcc', '0'),
(3, 'email_show_cc', '0'),
(3, 'email_skip_unknown_recipients', '0'),
(3, 'email_sort_email_addresses_by_time', '0'),
(3, 'email_use_plain_text_markup', '0'),
(4, 'email_always_request_notification', '0'),
(4, 'email_always_respond_to_notifications', '0'),
(4, 'email_font_size', '14px'),
(4, 'email_show_bcc', '0'),
(4, 'email_show_cc', '0'),
(4, 'email_skip_unknown_recipients', '0'),
(4, 'email_sort_email_addresses_by_time', '0'),
(4, 'email_use_plain_text_markup', '0'),
(4, 'ms_pm-status-filter', ''),
(4, 'ms_pr2_statuses', ''),
(4, 'projects2_tree_state', '[\"root\",1,2,3]'),
(5, 'email_always_request_notification', '0'),
(5, 'email_always_respond_to_notifications', '0'),
(5, 'email_font_size', '14px'),
(5, 'email_show_bcc', '0'),
(5, 'email_show_cc', '0'),
(5, 'email_skip_unknown_recipients', '0'),
(5, 'email_sort_email_addresses_by_time', '0'),
(5, 'email_use_plain_text_markup', '0'),
(6, 'email_always_request_notification', '0'),
(6, 'email_always_respond_to_notifications', '0'),
(6, 'email_font_size', '14px'),
(6, 'email_show_bcc', '0'),
(6, 'email_show_cc', '0'),
(6, 'email_skip_unknown_recipients', '0'),
(6, 'email_sort_email_addresses_by_time', '0'),
(6, 'email_use_plain_text_markup', '0');

-- --------------------------------------------------------

--
-- Table structure for table `go_state`
--

CREATE TABLE `go_state` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_state`
--

INSERT INTO `go_state` (`user_id`, `name`, `value`) VALUES
(1, 'addressbook-window-new-contact', 'o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A820%5Eheight%3Dn%253A640'),
(1, 'bs-items-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aitem_group_name%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aamount%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Aunit%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A250%255Eo%25253Aid%25253Ds%2525253Acost_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Atracking_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_cost%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_price%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Aunit_total%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Aunit_list%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Ds%2525253Atotal-price%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Aitem_total%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Avat%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Avat_code%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Adiscount%25255Ewidth%25253Dn%2525253A50%255Eo%25253Aid%25253Ds%2525253Amarkup%25255Ewidth%25253Dn%2525253A80%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A16%25255Ewidth%25253Dn%2525253A120%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Ds%2525253Anote%25255Ewidth%25253Dn%2525253A250%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Aitem_group_name'),
(1, 'calendar-state', 's%3A%7B%22displayType%22%3A%22days%22%2C%22days%22%3A7%2C%22calendars%22%3A%5B1%5D%2C%22view_id%22%3A0%2C%22group_id%22%3A1%7D'),
(1, 'entity-grid-selected-link', 'a%3As%253AContact'),
(1, 'go-checker-panel', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A28%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A105%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A330.75%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A80%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A54.25%5Egroup%3Ds%253Atype'),
(1, 'go-email-west', 'o%3Acollapsed%3Db%253A0%5Ewidth%3Dn%253A698'),
(1, 'go-module-panel-modules', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A1000%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Asort_order%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Ds%2525253Apackage%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A35.875%5Esort%3Do%253Afield%253Ds%25253Aname%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Apackage'),
(1, 'list-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A90%255Eo%25253Aid%25253Ds%2525253Alistview-calendar-name-heading%25255Ewidth%25253Dn%2525253A1288%5Esort%3Do%253Afield%253Ds%25253Astart_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),
(1, 'open-modules', 'a%3As%253Aaddressbook%5Es%253Abilling%5Es%253Acustomfields%5Es%253Abookmarks%5Es%253Acalendar%5Es%253Aemail%5Es%253Afiles%5Es%253Ahoursapproval2%5Es%253Aleavedays%5Es%253Aprojects2%5Es%253Asummary%5Es%253Atasks%5Es%253Atickets%5Es%253Atimeregistration2%5Es%253Apostfixadmin'),
(1, 'pm-tasks', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A196%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A56%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A35.875%5Egroup%3Ds%253Aparent_description%5Ecollapsed%3Db%253A1'),
(1, 'saveas-filebrowserfs-east-panel', 'o%3Acollapsed%3Db%253A1'),
(1, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),
(1, 'ti-types-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A35%255Eo%25253Aid%25253Ds%2525253Aname%25255Ewidth%25253Dn%2525253A224%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Egroup%3Ds%253Agroup_name'),
(1, 'tr-entry-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A1%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Ds%2525253Aproject%25255Ewidth%25253Dn%2525253A300%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A200%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A150%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A8%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A9%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adate%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Aday'),
(2, 'entity-grid-selected-link', 'a%3As%253AContact'),
(2, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),
(3, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),
(4, 'entity-grid-selected-link', 'a%3As%253ALinkedEmail'),
(4, 'pm-tasks', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Ds%2525253Adescription%25255Ewidth%25253Dn%2525253A196%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A98%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A56%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A5%25255Ewidth%25253Dn%2525253A63%255Eo%25253Aid%25253Dn%2525253A6%25255Ewidth%25253Dn%2525253A100%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A7%25255Ewidth%25253Dn%2525253A35.875%5Egroup%3Ds%253Aparent_description%5Ecollapsed%3Db%253A1'),
(4, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),
(5, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name'),
(6, 'su-tasks-grid', 'o%3Acolumns%3Da%253Ao%25253Aid%25253Dn%2525253A0%25255Ewidth%25253Dn%2525253A40%255Eo%25253Aid%25253Ds%2525253Atask-portlet-name-col%25255Ewidth%25253Dn%2525253A687%255Eo%25253Aid%25253Dn%2525253A2%25255Ewidth%25253Dn%2525253A100%255Eo%25253Aid%25253Dn%2525253A3%25255Ewidth%25253Dn%2525253A150%25255Ehidden%25253Db%2525253A1%255Eo%25253Aid%25253Dn%2525253A4%25255Ewidth%25253Dn%2525253A50%25255Ehidden%25253Db%2525253A1%5Esort%3Do%253Afield%253Ds%25253Adue_time%255Edirection%253Ds%25253AASC%5Egroup%3Ds%253Atasklist_name');

-- --------------------------------------------------------

--
-- Table structure for table `go_templates`
--

CREATE TABLE `go_templates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `type` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `content` longblob NOT NULL,
  `extension` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_templates`
--

INSERT INTO `go_templates` (`id`, `user_id`, `type`, `name`, `acl_id`, `content`, `extension`) VALUES
(1, 1, 0, 'Default', 18, 0x4d6573736167652d49443a203c3839613531386432333735386166633334333364636362633331656639626466406c6f63616c686f73743e0d0a446174653a204d6f6e2c203031204a756c20323031392030393a30363a3236202b303030300d0a46726f6d3a200d0a4d494d452d56657273696f6e3a20312e300d0a436f6e74656e742d547970653a206d756c7469706172742f616c7465726e61746976653b0d0a20626f756e646172793d225f3d5f73776966745f313536313937313938365f66323632326535323031613938363135663735383435386366376233363234615f3d5f220d0a0d0a0d0a2d2d5f3d5f73776966745f313536313937313938365f66323632326535323031613938363135663735383435386366376233363234615f3d5f0d0a436f6e74656e742d547970653a20746578742f706c61696e3b20636861727365743d5554462d380d0a436f6e74656e742d5472616e736665722d456e636f64696e673a2071756f7465642d7072696e7461626c650d0a0d0a7b73616c75746174696f6e7d2c0d0a0d0a7b626f64797d0d0a0d0a4265737420726567617264730d0a0d0a0d0a7b757365723a6e616d657d0d0a7b75736572636f6d70616e793a6e616d657d0d0a0d0a2d2d5f3d5f73776966745f313536313937313938365f66323632326535323031613938363135663735383435386366376233363234615f3d5f0d0a436f6e74656e742d547970653a20746578742f68746d6c3b20636861727365743d5554462d380d0a436f6e74656e742d5472616e736665722d456e636f64696e673a2071756f7465642d7072696e7461626c650d0a0d0a7b73616c75746174696f6e7d2c3c6272202f3e0d0a3c6272202f3e0d0a7b626f64797d3c6272202f3e0d0a3c6272202f3e0d0a4265737420726567617264733c6272202f3e0d0a3c6272202f3e0d0a3c6272202f3e0d0a7b757365723a6e616d657d3c6272202f3e0d0a7b75736572636f6d70616e793a6e616d657d3c6272202f3e0d0a0d0a2d2d5f3d5f73776966745f313536313937313938365f66323632326535323031613938363135663735383435386366376233363234615f3d5f2d2d0d0a, ''),
(2, 1, 1, 'Letter', 19, 0x504b0304140008080800efbfbd44efbfbd420000000000000000000000000b0000005f72656c732f2e72656c73efbfbdefbfbd4d4b03410cefbfbdefbfbdefbfbd1543efbfbdefbfbd6c2befbfbdefbfbdefbfbdefbfbd22426f22efbfbd07efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd073369efbfbdefbfbdefbfbd410aefbfbd50efbfbdefbfbdc7bc79efbfbdefbfbd1cefbfbd6defbfbdefbfbdefbfbd4eefbfbdefbfbdefbfbd41c3aa69417130d1ba306a78efbfbd3d2f1f60efbfbd2fefbfbd573eefbfbdefbfbd4aefbfbd5c2aefbfbdde84efbfbd611249efbfbdefbfbdefbfbd4cefbfbd343171efbfbdefbfbd21664f52efbfbd3c6222efbfbdefbfbdefbfbd71ddb6efbfbdefbfbd7f32efbfbdefbfbd31efbfbdefbfbd6aefbfbd5befbfbd02efbfbdefbfbd48efbfbd37367a16efbfbd24efbfbd26665eefbfbd5cefbfbdefbfbd382e154e7964efbfbd60efbfbd79efbfbd71efbfbd6a34efbfbd0c785d68efbfbd7befbfbd380cefbfbdefbfbd533447efbfbd41efbfbd79efbfbd593858efbfbdefbfbdefbfbd28efbfbd5b4677efbfbd69346f7ccbbcefbfbd6cefbfbd5eefbfbdcda2efbfbdefbfbd1befbfbdefbfbd504b0708efbfbdefbfbd0123efbfbd0000003d020000504b0304140008080800efbfbd44efbfbd420000000000000000000000001c000000776f72642f5f72656c732f646f63756d656e742e786d6c2e72656c73efbfbdefbfbd4d0aefbfbd3010efbfbdefbfbdefbfbd22efbfbddea65510efbfbdefbfbd6e44702befbfbd0031efbfbdefbfbdefbfbd3609efbfbd287a7b03efbfbd5a28efbfbdefbfbdefbfbdefbfbd7defbfbd312f5f5fefbfbdefbfbd5defbfbd076defbfbdefbfbd2c49efbfbdefbfbd51efbfbdd2a6117028efbfbdefbfbd25efbfbdefbfbd49efbfbdefbfbd4e525c09efbfbd76efbfbdefbfbd1b1304efbfbd446eefbfbd79502defbfbd3224d6a1efbfbdefbfbdefbfbdefbfbd5e522c7dc39d5427efbfbd20efbfbdefbfbdefbfbdefbfbd4f06140326efbfbd5502efbfbdefbfbdca80efbfbd37efbfbdefbfbdefbfbd6d5d6befbfbd1befbfbdefbfbd3d1a1aefbfbdefbfbdefbfbd6e1defbfbd48efbfbdefbfbd4112efbfbdefbfbd013e2e3fefbfbdefbfbd7c6d0defbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3f40efbfbdefbfbdefbfbdefbfbd17efbfbdefbfbdefbfbdefbfbd49efbfbd07efbfbd1677504b0708efbfbd2f30efbfbdefbfbd00000013020000504b0304140008080800efbfbd44efbfbd4200000000000000000000000011000000776f72642f73657474696e67732e786d6c45efbfbd4b0eefbfbd300c44efbfbdefbfbd22efbfbd1e1258efbfbd48efbfbdefbfbd02efbfbd01426befbfbd526247efbfbdefbfbdefbfbdefbfbd092befbfbdefbfbd37337aefbfbdee95a27962efbfbdefbfbdefbfbdefbfbd72efbfbdefbfbd20efbfbd3cefbfbd74efbfbd703e1defbfbd5b30efbfbdefbfbdefbfbd10efbfbdefbfbdefbfbd1b05efbfbd76efbfbdefbfbd1a41efbfbdefbfbd12531f48efbfbdefbfbdefbfbd5d3537efbfbd4a7fefbfbd1464efbfbd19efbfbdefbfbd2befbfbd14efbfbdefbfbd72efbfbd13efbfbd2117efbfbd51efbfbd4e53efbfbd2befbfbdefbfbd36efbfbdefbfbdefbfbdefbfbdefbfbd1fefbfbd64efbfbd2663e991b4efbfbd3807efbfbd0706efbfbdefbfbd47efbfbd53efbfbd1cefbfbd73efbfbd3c43efbfbdefbfbd71efbfbd1fefbfbd7fefbfbdefbfbd0b504b070876d58eefbfbdefbfbd000000efbfbd000000504b0304140008080800efbfbd44efbfbd4200000000000000000000000012000000776f72642f666f6e745461626c652e786d6cc590efbfbd4eefbfbd300cefbfbdefbfbd3c45efbfbd3b4befbfbd014defbfbdefbfbd6912efbfbdefbfbd01efbfbd0378efbfbdefbfbd464aefbfbd2a0e2d7d7befbfbdefbfbd3b51efbfbdefbfbd26714befbfbdefbfbdefbfbdefbfbdefbfbdefbfbddda7efbfbdefbfbdefbfbdefbfbdefbfbd7c211f56efbfbd14efbfbd3555c69f0aefbfbd7e78efbfbdefbfbd48efbfbd117c05efbfbd3c167240efbfbdefbfbdefbfbd6eefbfbdefbfbd35efbfbdefbfbd22efbfbd7befbfbd4321efbfbd18efbfbd5c29efbfbd0d3aefbfbd15efbfbdefbfbd53efbfbdefbfbdefbfbd20efbfbd6f3829efbfbd6befbfbdefbfbdefbfbd431fefbfbd3aefbfbd1e55400b31efbfbdefbfbd312defbfbdd9adefbfbdc6adefbfbd50efbfbdefbfbd3432efbfbdefbfbdefbfbd4e7e0eefbfbdefbfbdefbfbd4eefbfbdefbfbd07efbfbd421fefbfbd43162fd88b577230097403efbfbdefbfbdefbfbdefbfbd1632cba41aefbfbdefbfbd193b5cefbfbd61efbfbdefbfbdefbfbdefbfbd44efbfbd5cefbfbd1d040347efbfbde7969a60dfa06fefbfbd3befbfbd5d64efbfbd6fefbfbdefbfbd27efbfbd326a712defbfbd0defbfbd1f5157efbfbd7273efbfbd5befbfbdefbfbddfbfefbfbd7e75efbfbdefbfbdefbfbdefbfbd17504b0708efbfbd49efbfbdefbfbd0301000077030000504b0304140008080800efbfbd44efbfbd420000000000000000000000000f000000776f72642f7374796c65732e786d6cefbfbd565b6fefbfbd30147edfafefbfbdefbfbdefbfbdefbfbd42efbfbd4d4d2bc6844042efbfbd12efbfbdefbfbd0fefbfbd215e1ddbb2efbfbd06efbfbdefbfbd67efbfbdefbfbdefbfbdefbfbd640963efbfbd01efbfbdefbfbd3eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd3621efbfbd0b1212331aefbfbddeb56b5befbfbdefbfbd2cefbfbd7413efbfbd3fefbfbd26577ddb920a6804efbfbd5114efbfbd3b24efbfbdefbfbd2f77efbfbd50efbfbd1d41efbfbdefbfbdefbfbd540eefbfbdefbfbdefbfbdefbfbdefbfbd43c79161efbfbd12efbfbdd78c23efbfbdefbfbdefbfbd4c24efbfbdefbfbd546cefbfbdefbfbdefbfbdefbfbd0b162229efbfbdefbfbd09717cefbfbdefbfbd3a09606adf97075aefbfbd38efbfbd023befbfbdd080efbfbdefbfbdefbfbd717d3707011b013cceb75348efbfbdefbfbd1720efbfbdefbfbd0defbfbd2125efbfbd5a1a0defbfbdc997efbfbd56efbfbdefbfbdefbfbd41efbfbdefbfbd3f0a33643862d99851251829efbfbdefbfbdefbfbdefbfbd425befbfbdefbfbd0befbfbd73efbfbd2a36efbfbdefbfbd185159efbfbd52222d36316defbfbd3561efbfbd634a43efbfbd71efbfbd395c25efbfbd61efbfbd6f33efbfbdefbfbd0c310eefbfbdefbfbd400cefbfbd501a02efbfbdefbfbd676a2defbfbd4a234220efbfbd48623859efbfbd47541e74731021234cefbfbdefbfbdefbfbdefbfbd372a28efbfbdefbfbd52efbfbd2d25efbfbd030befbfbd662f2340375aefbfbdc2913eefbfbd75efbfbdefbfbd3aefbfbdefbfbdefbfbdefbfbd3aefbfbd5c2defbfbd05efbfbdefbfbdefbfbd5338efbfbdefbfbd7b5e13efbfbd4d11efbfbd78efbfbdefbfbd3defbfbd1548147defbfbd47efbfbdefbfbd155eefbfbdefbfbd79efbfbdefbfbdc99befbfbdefbfbddba0efbfbdefbfbd40efbfbd5f0265efbfbd0defbfbdefbfbd19283befbfbd40efbfbdddbeefbfbd0c63efbfbd335448efbfbdefbfbdefbfbd512defbfbd4859734cefbfbdefbfbd5c7befbfbd5f28efbfbdefbfbd24efbfbdefbfbd4558efbfbd072d4defbfbd4cefbfbd3671efbfbd4fda8575efbfbdefbfbd2fefbfbd3defbfbd5631efbfbd1dd5aa67efbfbdefbfbd42efbfbdefbfbd2731efbfbd10efbfbdefbfbd60efbfbd6defbfbd6befbfbdefbfbdefbfbdefbfbd15d2a516efbfbdefbfbd76efbfbdc2a06defbfbd5529efbfbdefbfbd41efbfbdefbfbdefbfbdefbfbd12efbfbdefbfbd12efbfbd3fefbfbd44efbfbdefbfbdefbfbdefbfbd35efbfbdefbfbdefbfbdefbfbdefbfbd5716efbfbdefbfbdefbfbdefbfbd7f32efbfbdefbfbdefbfbd6eefbfbdefbfbddf84efbfbd1c4b55efbfbdefbfbdefbfbd2aefbfbdefbfbdefbfbd7f17112602efbfbd2cefbfbdca9a621aefbfbd2e1b34213006efbfbd741772efbfbdefbfbd06efbfbd0e2befbfbd6e5d5aefbfbd224d56efbfbdefbfbd69efbfbd39efbfbd7fefbfbdefbfbdefbfbdefbfbd4eefbfbd10efbfbdefbfbdefbfbdefbfbd633aefbfbd543defbfbd2ddeae460dd28c4668efbfbddeb2efbfbdefbfbdefbfbdefbfbdefbfbd2868efbfbd46efbfbdefbfbd44efbfbdefbfbd69e490beefbfbdefbfbdefbfbd6b3aefbfbdefbfbdefbfbd585befbfbddc964c1befbfbd1fefbfbdefbfbdefbfbdefbfbd1b54efbfbdefbfbd3d5aefbfbd3b65537eefbfbdefbfbdefbfbd504b070854cc9314efbfbd020000450c0000504b0304140008080800efbfbd44efbfbd4200000000000000000000000011000000776f72642f646f63756d656e742e786d6cefbfbd575b6fefbfbd30147eefbfbd5744795eefbfbd744505efbfbd3513500defbfbdefbfbdefbfbd62efbfbd19efbfbdefbfbd4962efbfbd25efbfbd4fefbfbdefbfbd69efbfbd1d3befbfbdefbfbdefbfbdefbfbd366eefbfbd607defbfbd7b7c3eefbfbdefbfbdce89efbfbdefbfbdefbfbdefbfbdefbfbd4811efbfbdefbfbd58efbfbdefbfbd2c1c1fefbfbd6100efbfbd6aefbfbd553e0b3f5defbfbdefbfbd5eefbfbdefbfbd45efbfbd18115aefbfbd2c5cefbfbd0defbfbdefbfbd67efbfbd75efbfbd34efbfbd24280cefbfbd0cefbfbd267a16564625efbfbd1620efbfbd1d494eefbfbdefbfbd3aefbfbd11efbfbd32efbfbd59efbfbd29744dd88d30efbfbdefbfbd402cefbfbd28efbfbd061defbfbd12efbfbdefbfbd65efbfbd48efbfbdefbfbd3479efbfbd0eefbfbd775cefbfbd491c4f2303efbfbdefbfbdefbfbd6b0b5eefbfbd7eefbfbdefbfbd7defbfbd2b297a5cefbfbd10efbfbd5a1b561a4defbfbd5aefbfbd08295a5e49efbfbd1aefbfbd19efbfbd0f08efbfbdefbfbd33efbfbd281fefbfbdefbfbd0cefbfbdefbfbd2877efbfbdefbfbd5b67efbfbdefbfbdefbfbd2f355befbfbdefbfbd6c1e0befbfbd3457efbfbd1610efbfbdc98aefbfbd5968efbfbd11efbfbd517a1a0defbfbdefbfbdefbfbdefbfbdefbfbdefbfbd7aefbfbd49efbfbdefbfbd3d6fefbfbd6e5f34efbfbd317d4d25041f35efbfbd0a385aefbfbd1a0cefbfbd606134efbfbd28daa3e0bda2efbfbd1eefbfbdefbfbd3813efbfbd63efbfbdefbfbd175cefbfbdefbfbdefbfbd456400efbfbd47efbfbd39efbfbd26efbfbd20efbfbd5170efbfbd61efbfbd1d357aefbfbd3ec69f0e74efbfbd0333222cefbfbdefbfbd2e37efbfbd6eefbfbd3e3625efbfbdefbfbd5defbfbd44efbfbd134524efbfbdefbfbd53efbfbd6b291fefbfbd6fefbfbd0802efbfbd4c18336e35efbfbd64efbfbd2b47efbfbd51efbfbdefbfbdefbfbd6737da9c2024efbfbd6242efbfbdefbfbd76efbfbdefbfbdefbfbd39efbfbd211740731a2c75efbfbdefbfbdefbfbdccb8efbfbdefbfbd7d7128efbfbdefbfbdefbfbd0274efbfbdefbfbdefbfbdefbfbdefbfbd7cefbfbdefbfbdefbfbdefbfbd7076efbfbdefbfbd42efbfbd6eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd1d3defbfbdefbfbdefbfbdefbfbdefbfbd2fefbfbd0d580c0cefbfbdefbfbd30efbfbdefbfbdefbfbdefbfbdefbfbd1eefbfbd2cefbfbdefbfbdefbfbd6a1b2559efbfbd68efbfbdefbfbdefbfbdefbfbdefbfbdefbfbd08efbfbdefbfbdefbfbd7250efbfbdefbfbd061724efbfbd66657eefbfbd0b57efbfbd4c5f4e5c5552efbfbdefbfbdefbfbd78efbfbd2aefbfbdefbfbdefbfbd0b62027f3146efbfbdd2bb26efbfbd3d2aefbfbd1aefbfbdefbfbd1aefbfbd37efbfbd0a374601efbfbd0defbfbdefbfbd0c37efbfbd0ccf8b2d1375efbfbd191defbfbd6525efbfbd5befbfbdefbfbd743806efbfbd4b225aefbfbdefbfbdefbfbdefbfbd6f0eefbfbd5fefbfbd0b69efbfbd0d34efbfbdefbfbdefbfbdefbfbd5c2f5befbfbd2befbfbdefbfbd19ce9cefbfbd16efbfbd5cefbfbdefbfbdefbfbdefbfbd6b72efbfbd62da842befbfbdefbfbd0547efbfbd5230efbfbd36efbfbd7defbfbd3c79462aefbfbd5defbfbdefbfbd5c467defbfbd106d4aefbfbdefbfbd3b504b07086d1eefbfbdc398020000efbfbd0d0000504b0304140008080800efbfbd44efbfbd4200000000000000000000000010000000646f6350726f70732f6170702e786d6cefbfbdefbfbd3d6befbfbd301006e0bdbfc288efbfbdefbfbd14d7981064efbfbdefbfbdefbfbd29efbfbd0e6eefbfbd6614e99ca8581f48efbfbdefbfbdefbfbdefbfbdefbfbd2d34efbfbd3b1eefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd62efbfbdefbfbd0c3119efbfbd3aefbfbdefbfbd1829efbfbd29efbfbdefbfbd3b76efbfbd7f2e37efbfbd4828efbfbdefbfbdefbfbd77d0910b24efbfbd1377efbfbd35efbfbd00110defbfbd220b2e75efbfbd18efbfbdefbfbd2675022b53efbfbd63efbfbdefbfbdefbfbd472b31efbfbdefbfbd48efbfbd381a054f5eefbfbd161cd29aefbfbdefbfbdc282efbfbd34efbfbd32efbfbdefbfbdefbfbd57dc9eefbfbdefbfbdefbfbd5f7aefbfbd2f217befbfbdefbfbd1eefbfbdefbfbd1b0b627d5f737a1defbfbd4308efbfbd5112efbfbdefbfbd626f0e115e7e3cefbfbd54efbfbdefbfbdefbfbd7aefbfbd376e5eefbfbdefbfbd4d3befbfbd4d71efbfbd30e4be9fefbfbdefbfbd36efbfbd59efbfbd7aefbfbdcda4efbfbd4cefbfbd7aefbfbd5eefbfbd24efbfbd00504b0708491befbfbdefbfbdefbfbd0000006a010000504b0304140008080800efbfbd44efbfbd4200000000000000000000000011000000646f6350726f70732f636f72652e786d6c7defbfbdefbfbd4eefbfbd2014efbfbdefbfbd7defbfbdefbfbdefbfbd16efbfbdefbfbd4defbfbd76efbfbdefbfbd5defbfbdefbfbdefbfbd19efbfbd77efbfbdefbfbd6d68efbfbd0470efbfbdefbfbd5eefbfbd5b756eefbfbdefbfbdefbfbdefbfbd4331ddaa26d98075efbfbdefbfbd25efbfbd19410968efbfbdefbfbd52efbfbd4aefbfbdefbfbdefbfbdefbfbd13efbfbd38efbfbd75cd9b5643efbfbd76efbfbdd0b4efbfbd28efbfbd61efbfbdefbfbdefbfbd685b03efbfbd4b70491069c78429efbfbdefbfbd7befbfbd3076620defbfbdefbfbd2c103a34efbfbdefbfbd55dc87d2aeefbfbdefbfbde283af005f1272efbfbd15785e73efbfbd712f4cefbfbd60447b652d06efbfbdefbfbdefbfbd4d14efbfbd0243030aefbfbd77efbfbd6614efbfbdefbfbd4aefbfbdefbfbdefbfbdefbfbd2b0eefbfbd5fefbfbd07efbfbdefbfbd5938760672efbfbdefbfbd40755defbfbd7579efbfbd427eefbfbd5fefbfbd0f4fefbfbdefbfbdefbfbd55094055efbfbd573361efbfbd7befbfbdefbfbd2060efbfbdefbfbd0eefbfbdefbfbdefbfbdefbfbd7e314355704c5272efbfbdefbfbdd1824e583e6674efbfbd11efbfbd56efbfbd3fefbfbdefbfbdefbfbdefbfbdefbfbdefbfbd6a0e56efbfbdefbfbdefbfbdd78651efbfbdefbfbdefbfbdefbfbd67efbfbd70efbfbdefbfbd61efbfbd4b09efbfbdefbfbdefbfbd143f45efbfbdefbfbd6aefbfbdefbfbdefbfbdefbfbd344fefbfbd2846262cefbfbdefbfbd1a1f473e3862160b1befbfbd3fefbfbdefbfbdd2b8efbfbd50efbfbdefbfbdefbfbd15555f504b070874efbfbd150747010000efbfbd020000504b0304140008080800efbfbd44efbfbd42000000000000000000000000130000005b436f6e74656e745f54797065735d2e786d6cefbfbdefbfbd314fefbfbd3010efbfbdefbfbdefbfbdefbfbdefbfbd2b4a1c181042493a203142efbfbd3023635f12efbfbdc4b67cefbfbdefbfbdefbfbdefbfbd73682304efbfbd11efbfbdefbfbd58efbfbdefbfbdefbfbd7defbfbde7938befbfbd66efbfbd3578efbfbdd694efbfbd3cefbfbd5902465aefbfbd4d5befbfbdefbfbdefbfbd36efbfbd62efbfbd6a51efbfbd5b07efbfbdefbfbdefbfbd60c9ba10efbfbd35efbfbd283b180466d681efbfbdefbfbdefbfbdefbfbd4104efbfbdefbfbdefbfbd3b21efbfbd450befbfbd22efbfbd2fefbfbdefbfbd26efbfbd0969efbfbd1eefbfbd2aefbfbd09e7b58264257cefbfbd1303efbfbdefbfbd3f7aefbfbd677165efbfbdefbfbd7b4164efbfbd4c38efbfbd6b2902efbfbdefbfbd6befbfbd3eefbfbdefbfbd1d2956efbfbd1aefbfbdefbfbd331230efbfbd3defbfbdefbfbd7aefbfbdefbfbd292b5f06026524efbfbd37344208efbfbd5cefbfbdd09fefbfbd6cefbfbd680953efbfbdefbfbde6bc95efbfbd487e74efbfbdefbfbdefbfbd6cefbfbdefbfbdefbfbdefbfbd78efbfbdefbfbdefbfbd1926efbfbdefbfbd3eefbfbd6d0f7fd185efbfbd7716efbfbdefbfbdefbfbd4f1b603a19efbfbd36efbfbd72efbfbd70efbfbd434eefbfbdefbfbd63efbfbdefbfbd2a15efbfbdefbfbdefbfbd38efbfbd411fefbfbdefbfbdc496efbfbdefbfbd620cefbfbdefbfbd1fefbfbdefbfbd1217051fefbfbdefbfbdefbfbd0d504b070863efbfbd612a0100005e040000504b01021400140008080800efbfbd44efbfbd42efbfbdefbfbd0123efbfbd0000003d0200000b00000000000000000000000000000000005f72656c732f2e72656c73504b01021400140008080800efbfbd44efbfbd42efbfbd2f30efbfbdefbfbd000000130200001c0000000000000000000000000012010000776f72642f5f72656c732f646f63756d656e742e786d6c2e72656c73504b01021400140008080800efbfbd44efbfbd4276d58eefbfbdefbfbd000000efbfbd000000110000000000000000000000000021020000776f72642f73657474696e67732e786d6c504b01021400140008080800efbfbd44efbfbd42efbfbd49efbfbdefbfbd0301000077030000120000000000000000000000000005030000776f72642f666f6e745461626c652e786d6c504b01021400140008080800efbfbd44efbfbd4254cc9314efbfbd020000450c00000f0000000000000000000000000048040000776f72642f7374796c65732e786d6c504b01021400140008080800efbfbd44efbfbd426d1eefbfbdc398020000efbfbd0d0000110000000000000000000000000029070000776f72642f646f63756d656e742e786d6c504b01021400140008080800efbfbd44efbfbd42491befbfbdefbfbdefbfbd0000006a0100001000000000000000000000000000000a0000646f6350726f70732f6170702e786d6c504b01021400140008080800efbfbd44efbfbd4274efbfbd150747010000efbfbd0200001100000000000000000000000000220b0000646f6350726f70732f636f72652e786d6c504b01021400140008080800efbfbd44efbfbd4263efbfbd612a0100005e0400001300000000000000000000000000efbfbd0c00005b436f6e74656e745f54797065735d2e786d6c504b050600000000090009003c020000130e00000000, 'docx');

-- --------------------------------------------------------

--
-- Table structure for table `go_working_weeks`
--

CREATE TABLE `go_working_weeks` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `mo_work_hours` double NOT NULL DEFAULT 8,
  `tu_work_hours` double NOT NULL DEFAULT 8,
  `we_work_hours` double NOT NULL DEFAULT 8,
  `th_work_hours` double NOT NULL DEFAULT 8,
  `fr_work_hours` double NOT NULL DEFAULT 8,
  `sa_work_hours` double NOT NULL DEFAULT 0,
  `su_work_hours` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `go_working_weeks`
--

INSERT INTO `go_working_weeks` (`user_id`, `mo_work_hours`, `tu_work_hours`, `we_work_hours`, `th_work_hours`, `fr_work_hours`, `sa_work_hours`, `su_work_hours`) VALUES
(1, 8, 8, 8, 8, 8, 0, 0),
(2, 8, 8, 8, 8, 8, 0, 0),
(3, 8, 8, 8, 8, 8, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `imapauth_server`
--

CREATE TABLE `imapauth_server` (
  `id` int(11) NOT NULL,
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
  `smtpValidateCertificate` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imapauth_server`
--

INSERT INTO `imapauth_server` (`id`, `imapHostname`, `imapPort`, `imapEncryption`, `imapValidateCertificate`, `removeDomainFromUsername`, `smtpHostname`, `smtpPort`, `smtpUsername`, `smtpPassword`, `smtpUseUserCredentials`, `smtpEncryption`, `smtpValidateCertificate`) VALUES
(3, 'mailserver', 143, NULL, 0, 0, 'mailserver', 25, '', '{GOCRYPT2}def50200bac6f5dd846bf022d00646f7a38f9e0af13331b25d1b452917ee9b42891a63d9339ace472a0475f12665a8e976a5586ee73d22305cb66b097f8d64d46f00f7893ce8219b4abe2afe410ce470f71eda59', 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `imapauth_server_domain`
--

CREATE TABLE `imapauth_server_domain` (
  `id` int(11) NOT NULL,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imapauth_server_domain`
--

INSERT INTO `imapauth_server_domain` (`id`, `serverId`, `name`) VALUES
(3, 3, 'intermesh.localhost');

-- --------------------------------------------------------

--
-- Table structure for table `imapauth_server_group`
--

CREATE TABLE `imapauth_server_group` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imapauth_server_group`
--

INSERT INTO `imapauth_server_group` (`serverId`, `groupId`) VALUES
(3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ld_credits`
--

CREATE TABLE `ld_credits` (
  `ld_year_credit_id` int(11) NOT NULL,
  `ld_credit_type_id` int(11) NOT NULL,
  `n_hours` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ld_credits`
--

INSERT INTO `ld_credits` (`ld_year_credit_id`, `ld_credit_type_id`, `n_hours`) VALUES
(1, 1, 200);

-- --------------------------------------------------------

--
-- Table structure for table `ld_credit_types`
--

CREATE TABLE `ld_credit_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `credit_doesnt_expired` tinyint(1) NOT NULL DEFAULT 0,
  `sort_index` int(11) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ld_credit_types`
--

INSERT INTO `ld_credit_types` (`id`, `name`, `description`, `credit_doesnt_expired`, `sort_index`, `active`) VALUES
(1, 'Holidays', 'Holidays', 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ld_leave_days`
--

CREATE TABLE `ld_leave_days` (
  `id` int(11) NOT NULL,
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
  `ld_credit_type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ld_leave_days`
--

INSERT INTO `ld_leave_days` (`id`, `user_id`, `first_date`, `last_date`, `from_time`, `n_hours`, `n_nat_holiday_hours`, `description`, `ctime`, `mtime`, `status`, `ld_credit_type_id`) VALUES
(1, 1, 1572562800, 1572649200, '01:00:00', 0, 8, 'Test 1', 1572608007, 1572608007, 0, 1),
(2, 1, 1573513200, 1573513200, '01:00:00', 8, 0, 'test2', 1572608032, 1572608037, 2, 1),
(3, 1, 1573599600, 1573599600, '01:00:00', 8, 0, '', 1572608045, 1572608048, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ld_year_credits`
--

CREATE TABLE `ld_year_credits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `year` int(4) NOT NULL DEFAULT 0,
  `comments` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `manager_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ld_year_credits`
--

INSERT INTO `ld_year_credits` (`id`, `user_id`, `year`, `comments`, `manager_user_id`) VALUES
(1, 1, 2019, '0', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notes_note`
--

CREATE TABLE `notes_note` (
  `id` int(11) NOT NULL,
  `noteBookId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filesFolderId` int(11) DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes_note_book`
--

CREATE TABLE `notes_note_book` (
  `id` int(11) NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filesFolderId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes_note_custom_fields`
--

CREATE TABLE `notes_note_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes_note_image`
--

CREATE TABLE `notes_note_image` (
  `noteId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes_user_settings`
--

CREATE TABLE `notes_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultNoteBookId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pa_aliases`
--

CREATE TABLE `pa_aliases` (
  `id` int(11) NOT NULL,
  `domain_id` int(11) NOT NULL,
  `address` varchar(190) DEFAULT NULL,
  `goto` text DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `active` enum('0','1') NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Aliases';

--
-- Dumping data for table `pa_aliases`
--

INSERT INTO `pa_aliases` (`id`, `domain_id`, `address`, `goto`, `ctime`, `mtime`, `active`) VALUES
(1, 1, 'admin@intermesh.localhost', 'admin@intermesh.localhost', 1562244891, 1562244891, '0'),
(2, 1, 'test@intermesh.localhost', 'test@intermesh.localhost', 1562244900, 1562244900, '0'),
(3, 1, 'schering@intermesh.localhost', 'admin@intermesh.localhost', 1562593674, 1562593674, '1'),
(4, 1, 'merijn@intermesh.localhost', 'admin@intermesh.localhost', 1562593679, 1562593679, '1'),
(5, 1, 'foo@intermesh.localhost', 'foo@intermesh.localhost', 1562673376, 1562673376, '0'),
(6, 1, 'zoidberg@intermesh.localhost', 'zoidberg@intermesh.localhost', 1562677035, 1562677035, '0'),
(7, 2, 'zoidberg@planetexpress.com', 'zoidberg@planetexpress.com', 1562677348, 1562677348, '0'),
(8, 3, 't3@1', 't3@1', 1568106872, 1568106872, '0'),
(9, 3, 't4@1', 't4@1', 1568107447, 1568107447, '0'),
(10, 3, 't5@1', 't5@1', 1568108068, 1568108068, '0'),
(11, 1, 't6@intermesh.localhost', 't6@intermesh.localhost', 1568108151, 1568108151, '0'),
(12, 4, 't7@', 't7@', 1568108188, 1568108188, '0'),
(13, 1, 'demo2@intermesh.localhost', 'demo2@intermesh.localhost', 1568108312, 1568108312, '0'),
(14, 1, 't12@intermesh.localhost', 't12@intermesh.localhost', 1568632558, 1568632558, '0'),
(15, 5, 'tom-ketil.sundseth@tbb.no', 'tom-ketil.sundseth@tbb.no', 1568633866, 1568633866, '0'),
(16, 5, 'tom-ketil@tbb.no', 'tom-ketil.sundseth@tbb.no', 1568633866, 1568633866, '0'),
(17, 5, 'vidar.storloes@tbb.no', 'vidar.storloes@tbb.no', 1568634312, 1568634312, '0'),
(18, 5, 'vidar@tbb.no', 'vidar.storloes@tbb.no', 1568634312, 1568634312, '0'),
(19, 5, 'tommy.nielsen@tbb.no', 'tommy.nielsen@tbb.no', 1568634313, 1568634313, '0'),
(20, 5, 'tommy@tbb.no', 'tommy.nielsen@tbb.no', 1568634313, 1568634313, '0'),
(21, 5, 'tom.erik.hermansen@tbb.no', 'tom.erik.hermansen@tbb.no', 1568634314, 1568634314, '0'),
(22, 5, 'tom.erik@tbb.no', 'tom.erik.hermansen@tbb.no', 1568634314, 1568634314, '0'),
(23, 5, 'thomas.syvertsen@tbb.no', 'thomas.syvertsen@tbb.no', 1568634314, 1568634314, '0'),
(24, 5, 'thomas.malmo@tbb.no', 'thomas.malmo@tbb.no', 1568634315, 1568634315, '0'),
(25, 5, 'thomas.johansen@tbb.no', 'thomas.johansen@tbb.no', 1568634315, 1568634315, '0'),
(26, 5, 'terje.sorum@tbb.no', 'terje.sorum@tbb.no', 1568634316, 1568634316, '0'),
(27, 5, 'terje@tbb.no', 'terje.sorum@tbb.no', 1568634316, 1568634316, '0'),
(28, 5, 'svein.storoy@tbb.no', 'svein.storoy@tbb.no', 1568634316, 1568634316, '0'),
(29, 5, 'svein@tbb.no', 'svein.storoy@tbb.no', 1568634316, 1568634316, '0'),
(30, 5, 'stian.trebekk@tbb.no', 'stian.trebekk@tbb.no', 1568634317, 1568634317, '0'),
(31, 5, 'stian@tbb.no', 'stian.trebekk@tbb.no', 1568634317, 1568634317, '0'),
(32, 5, 'steve.hermansen@tbb.no', 'steve.hermansen@tbb.no', 1568634317, 1568634317, '0'),
(33, 5, 'steve@tbb.no', 'steve.hermansen@tbb.no', 1568634317, 1568634317, '0'),
(34, 5, 'staale.lilledahl@tbb.no', 'staale.lilledahl@tbb.no', 1568634318, 1568634318, '0'),
(35, 5, 'steffen.kristensen@tbb.no', 'steffen.kristensen@tbb.no', 1568634319, 1568634319, '0'),
(36, 5, 'steffen@tbb.no', 'steffen.kristensen@tbb.no', 1568634319, 1568634319, '0'),
(37, 5, 'stig.borgersrud@tbb.no', 'stig.borgersrud@tbb.no', 1568634319, 1568634319, '0'),
(38, 5, 'stig@tbb.no', 'stig.borgersrud@tbb.no', 1568634319, 1568634319, '0'),
(39, 5, 'jan-egil.ottosen@tbb.no', 'jan-egil.ottosen@tbb.no', 1568634320, 1568634320, '0'),
(40, 5, 'jan-egil@tbb.no', 'jan-egil.ottosen@tbb.no', 1568634320, 1568634320, '0'),
(41, 5, 'roy.lilliedahl@tbb.no', 'roy.lilliedahl@tbb.no', 1568634320, 1568634320, '0'),
(42, 5, 'ronnie.husberg@tbb.no', 'ronnie.husberg@tbb.no', 1568634321, 1568634321, '0'),
(43, 5, 'ronnie@tbb.no', 'ronnie.husberg@tbb.no', 1568634321, 1568634321, '0'),
(44, 5, 'per.bjerkengen@tbb.no', 'per.bjerkengen@tbb.no', 1568634321, 1568634321, '0'),
(45, 5, 'per@tbb.no', 'per.bjerkengen@tbb.no', 1568634321, 1568634321, '0'),
(46, 5, 'oivind.johnsen@tbb.no', 'oivind.johnsen@tbb.no', 1568634322, 1568634322, '0'),
(47, 5, 'oivindj@tbb.no', 'oivind.johnsen@tbb.no', 1568634322, 1568634322, '0'),
(48, 5, 'odd-egil.torgersen@tbb.no', 'odd-egil.torgersen@tbb.no', 1568634322, 1568634322, '0'),
(49, 5, 'odd-egil@tbb.no', 'odd-egil.torgersen@tbb.no', 1568634322, 1568634322, '0'),
(50, 5, 'monica.loftskjær@tbb.no', 'monica.loftskjær@tbb.no', 1568634323, 1568634323, '0'),
(51, 5, 'monica@tbb.no', 'monica.loftskjær@tbb.no', 1568634323, 1568634323, '0'),
(52, 5, 'martin.olsen@tbb.no', 'martin.olsen@tbb.no', 1568634323, 1568634323, '0'),
(53, 5, 'martin@tbb.no', 'martin.olsen@tbb.no', 1568634323, 1568634323, '0'),
(54, 5, 'lars.sletta@tbb.no', 'lars.sletta@tbb.no', 1568634324, 1568634324, '0'),
(55, 5, 'lars@tbb.no', 'lars.sletta@tbb.no', 1568634324, 1568634324, '0'),
(56, 5, 'lars.kristiansen@tbb.no', 'lars.kristiansen@tbb.no', 1568634324, 1568634324, '0'),
(57, 5, 'ken.henriksen@tbb.no', 'ken.henriksen@tbb.no', 1568634325, 1568634325, '0'),
(58, 5, 'ken@tbb.no', 'ken.henriksen@tbb.no', 1568634325, 1568634325, '0'),
(59, 5, 'jan-tore.borresen@tbb.no', 'jan-tore.borresen@tbb.no', 1568634325, 1568634325, '0'),
(60, 5, 'jan-tore@tbb.no', 'jan-tore.borresen@tbb.no', 1568634325, 1568634325, '0'),
(61, 5, 'thomas.simonsen@tbb.no', 'thomas.simonsen@tbb.no', 1568634325, 1568634325, '0'),
(62, 5, 'giedrius.navagruckas@tbb.no', 'giedrius.navagruckas@tbb.no', 1568634326, 1568634326, '0'),
(63, 5, 'giedrius@tbb.no', 'giedrius.navagruckas@tbb.no', 1568634326, 1568634326, '0'),
(64, 5, 'frode.pedersen@tbb.no', 'frode.pedersen@tbb.no', 1568634326, 1568634326, '0'),
(65, 5, 'frode@tbb.no', 'frode.pedersen@tbb.no', 1568634326, 1568634326, '0'),
(66, 5, 'finn.poulsen@tbb.no', 'finn.poulsen@tbb.no', 1568634326, 1568634326, '0'),
(67, 5, 'espen.simonsen@tbb.no', 'espen.simonsen@tbb.no', 1568634327, 1568634327, '0'),
(68, 5, 'elisabeth.thun@tbb.no', 'elisabeth.thun@tbb.no', 1568634327, 1568634327, '0'),
(69, 5, 'dace.pedersen@tbb.no', 'dace.pedersen@tbb.no', 1568634328, 1568634328, '0'),
(70, 5, 'dace@tbb.no', 'dace.pedersen@tbb.no', 1568634328, 1568634328, '0'),
(71, 5, 'carl.wiborg@tbb.no', 'carl.wiborg@tbb.no', 1568634328, 1568634328, '0'),
(72, 5, 'carl@tbb.no', 'carl.wiborg@tbb.no', 1568634328, 1568634328, '0'),
(73, 5, 'borre.lilledahl@tbb.no', 'borre.lilledahl@tbb.no', 1568634328, 1568634328, '0'),
(74, 5, 'borre@tbb.no', 'borre.lilledahl@tbb.no', 1568634328, 1568634328, '0'),
(75, 5, 'benjamin.vinland@tbb.no', 'benjamin.vinland@tbb.no', 1568634329, 1568634329, '0'),
(76, 5, 'frode.johnsen@tbb.no', 'frode.johnsen@tbb.no', 1568634329, 1568634329, '0'),
(77, 5, 'einar.kristensen@tbb.no', 'einar.kristensen@tbb.no', 1568634329, 1568634329, '0'),
(78, 5, 'mayvellyn.musken@tbb.no', 'mayvellyn.musken@tbb.no', 1568634330, 1568634330, '0'),
(79, 5, 'rodrigo.almario@tbb.no', 'rodrigo.almario@tbb.no', 1568634330, 1568634330, '0'),
(80, 5, 'dejan.ogorelica@tbb.no', 'dejan.ogorelica@tbb.no', 1568634330, 1568634330, '0'),
(81, 5, 'jenderi.salazar@tbb.no', 'jenderi.salazar@tbb.no', 1568634331, 1568634331, '0'),
(82, 5, 'ania.wentowska@tbb.no', 'ania.wentowska@tbb.no', 1568634331, 1568634331, '0'),
(83, 5, 'bjorn.alknes@tbb.no', 'bjorn.alknes@tbb.no', 1568634332, 1568634332, '0'),
(84, 5, 'richard.bjornstad@tbb.no', 'richard.bjornstad@tbb.no', 1568634332, 1568634332, '0'),
(85, 5, 'terje.carlsen@tbb.no', 'terje.carlsen@tbb.no', 1568634332, 1568634332, '0'),
(86, 5, 'kim.hjemstad@tbb.no', 'kim.hjemstad@tbb.no', 1568634333, 1568634333, '0'),
(87, 5, 'roger.haaland@tbb.no', 'roger.haaland@tbb.no', 1568634333, 1568634333, '0'),
(88, 5, 'oivind.lindbaek@tbb.no', 'oivind.lindbaek@tbb.no', 1568634333, 1568634333, '0'),
(89, 5, 'jakub jan.markowicz@tbb.no', 'jakub jan.markowicz@tbb.no', 1568634334, 1568634334, '0'),
(90, 5, 'jakub.markowicz@tbb.no', 'jakub jan.markowicz@tbb.no', 1568634334, 1568634334, '0'),
(91, 5, 'emmannouil.nikolaras@tbb.no', 'emmannouil.nikolaras@tbb.no', 1568634334, 1568634334, '0'),
(92, 5, 'boye.sandbakken@tbb.no', 'boye.sandbakken@tbb.no', 1568634335, 1568634335, '0'),
(93, 5, 'rupert.sheridan@tbb.no', 'rupert.sheridan@tbb.no', 1568634335, 1568634335, '0'),
(94, 5, 'oskar.sjodal@tbb.no', 'oskar.sjodal@tbb.no', 1568634335, 1568634335, '0'),
(95, 5, 'oskar.sjødal@tbb.no', 'oskar.sjodal@tbb.no', 1568634335, 1568634335, '0'),
(96, 5, 'jonas.synnestvedt@tbb.no', 'jonas.synnestvedt@tbb.no', 1568634336, 1568634336, '0'),
(97, 5, 'sander.solvberg@tbb.no', 'sander.solvberg@tbb.no', 1568634336, 1568634336, '0'),
(98, 5, 'arne.sorensen@tbb.no', 'arne.sorensen@tbb.no', 1568634336, 1568634336, '0'),
(99, 5, 'andrzej.walenttek@tbb.no', 'andrzej.walenttek@tbb.no', 1568634337, 1568634337, '0'),
(100, 1, 't13@intermesh.localhost', 't13@intermesh.localhost', 1568799892, 1568799892, '0'),
(101, 1, 't14@intermesh.localhost', 't14@intermesh.localhost', 1568799932, 1568799932, '0'),
(102, 1, 't16@intermesh.localhost', 't16@intermesh.localhost', 1568800247, 1568800247, '0'),
(103, 1, 't17@intermesh.localhost', 't17@intermesh.localhost', 1568801006, 1568801006, '0'),
(104, 1, 't18@intermesh.localhost', 't18@intermesh.localhost', 1568982413, 1568982413, '0'),
(105, 1, 't19@intermesh.localhost', 't19@intermesh.localhost', 1568982656, 1568982656, '0'),
(106, 1, 't21@intermesh.localhost', 't21@intermesh.localhost', 1568983553, 1568983553, '0'),
(107, 1, 't23@intermesh.localhost', 't23@intermesh.localhost', 1568983703, 1568983703, '0'),
(108, 1, 't24@intermesh.localhost', 't24@intermesh.localhost', 1568993496, 1568993496, '0'),
(109, 1, 't25@intermesh.localhost', 't25@intermesh.localhost', 1568993541, 1568993541, '0'),
(110, 1, 't26@intermesh.localhost', 't26@intermesh.localhost', 1568993554, 1568993554, '0'),
(111, 1, 't27@intermesh.localhost', 't27@intermesh.localhost', 1568993577, 1568993577, '0'),
(112, 1, 't31@intermesh.localhost', 't31@intermesh.localhost', 1569500801, 1569500801, '0'),
(113, 1, 't43@intermesh.localhost', 't43@intermesh.localhost', 1570548993, 1570548993, '0'),
(114, 1, 't100@intermesh.localhost', 't100@intermesh.localhost', 1573485965, 1573485965, '0'),
(115, 1, 'Y39@intermesh.localhost', 'Y39@intermesh.localhost', 1573749826, 1573749826, '0');

-- --------------------------------------------------------

--
-- Table structure for table `pa_domains`
--

CREATE TABLE `pa_domains` (
  `id` int(11) NOT NULL,
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
  `acl_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Domains';

--
-- Dumping data for table `pa_domains`
--

INSERT INTO `pa_domains` (`id`, `user_id`, `domain`, `description`, `max_aliases`, `max_mailboxes`, `total_quota`, `default_quota`, `transport`, `backupmx`, `ctime`, `mtime`, `active`, `acl_id`) VALUES
(1, 1, 'intermesh.localhost', '', 0, 0, 9461760, 524288, 'virtual', 0, 1562244880, 1569501718, 1, 117),
(2, 1, 'planetexpress.com', '', 0, 0, 10485760, 524288, 'virtual', 0, 1562677337, 1562677337, 1, 121),
(3, 1, '1', NULL, 0, 0, 10485760, 524288, 'virtual', 0, 1568106872, 1568106872, 1, 131),
(4, 1, '', NULL, 0, 0, 10485760, 524288, 'virtual', 0, 1568108188, 1568108188, 1, 132),
(5, 1, 'tbb.no', '', 0, 0, 0, 524288, 'virtual', 0, 1568633866, 1568634400, 1, 133);

-- --------------------------------------------------------

--
-- Table structure for table `pa_mailboxes`
--

CREATE TABLE `pa_mailboxes` (
  `id` int(11) NOT NULL,
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
  `usage` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Mailboxes';

--
-- Dumping data for table `pa_mailboxes`
--

INSERT INTO `pa_mailboxes` (`id`, `domain_id`, `go_installation_id`, `username`, `password`, `name`, `maildir`, `homedir`, `quota`, `ctime`, `mtime`, `active`, `usage`) VALUES
(1, 1, NULL, 'admin@intermesh.localhost', '$5$rounds=5000$rbJe.xCJoG2EBXAK$4Cd06/EO8WSB5JfPOwDV0.e2LEBoqjbLGsYtV9Bt475', 'admin', 'intermesh.localhost/admin/Maildir/', 'intermesh.localhost/admin/', 524288, 1562244891, 1562244891, 1, 0),
(2, 1, NULL, 'test@intermesh.localhost', '$5$rounds=5000$IpOuuHcOdWsKPwo6$8csK9rJdcU3yS7cWBWyx5RBF.eqDxht5mm9PsPWN9/C', 'test', 'intermesh.localhost/test/Maildir/', 'intermesh.localhost/test/', 524288, 1562244900, 1562244900, 1, 0),
(3, 1, NULL, 'foo@intermesh.localhost', '$5$rounds=5000$rW2JjSOjEDOnM5qP$ktCJ87QMiO60OhtvDplWxiCTphUdwN/P.AT8L2HuyV6', 'foo', 'intermesh.localhost/foo/Maildir/', 'intermesh.localhost/foo/', 524288, 1562673383, 1562673383, 1, 0),
(4, 1, NULL, 'zoidberg@intermesh.localhost', '$5$rounds=5000$uDFVh0gmRSij5wKg$LsgyJsafOe.9YVtHD9W1v2H8eRuInBXch3qliFSp9m6', 'zoidberg', 'intermesh.localhost/zoidberg/Maildir/', 'intermesh.localhost/zoidberg/', 524288, 1562677037, 1562677037, 1, 0),
(5, 2, NULL, 'zoidberg@planetexpress.com', '$5$rounds=5000$TkCJ6cFPenIyvntP$8/hc1lfYtbj2KKQVK7VWDdWVfipPalcwJFTkXVyS9k8', 'zoidberg', 'planetexpress.com/zoidberg/Maildir/', 'planetexpress.com/zoidberg/', 524288, 1562677348, 1562677348, 1, 0),
(6, 3, NULL, 't3@1', '$5$rounds=5000$JI2EHfQGxOAJpi90$gRoBc35O6fe4rKShumFLLQ8sYj38SOtFgXfqGzeGf98', 't3', '1/t3/Maildir/', '1/t3/', 524288, 1568106872, 1568106872, 1, 0),
(7, 3, NULL, 't4@1', '$5$rounds=5000$5B1CnU/I0C6HYEK5$ycjmo2wGygqUAsr/Jk4p5jCEoXUlhQrYxXyNLThZ/A8', 't4', '1/t4/Maildir/', '1/t4/', 524288, 1568107447, 1568107447, 1, 0),
(8, 3, NULL, 't5@1', '$5$rounds=5000$goQX1WR4b98b1KqG$cdQf.IEm5RWiBC7DzjleprGB4n/oCTPE2nRXCYYe4l5', 't5', '1/t5/Maildir/', '1/t5/', 524288, 1568108068, 1568108068, 1, 0),
(9, 1, NULL, 't6@intermesh.localhost', '$5$rounds=5000$ZUV9Gg/QM0Cxd.TX$zIy4z9U6wOxWz3yrQtC/t7.bi/.B9ZwdQ1eLLC4TI00', 't6', 'intermesh.localhost/t6/Maildir/', 'intermesh.localhost/t6/', 524288, 1568108151, 1568108151, 1, 0),
(10, 4, NULL, 't7@', '$5$rounds=5000$PkeHsIAYV5claS1z$MB8YhWkXU7EK9kg/zPvlT7Jiv8vSh7LTiHaBQ/KK4X3', 't7', '/t7/Maildir/', '/t7/', 524288, 1568108188, 1568108188, 1, 0),
(11, 1, NULL, 'demo2@intermesh.localhost', '$5$rounds=5000$queE91lYHB1DHUQ3$3zNDxh0kaH6boNBX6b6cKfiaplpXTpSi8m08TLbvMy8', 'Demo User', 'intermesh.localhost/demo2/Maildir/', 'intermesh.localhost/demo2/', 524288, 1568108674, 1568108674, 1, 0),
(12, 1, NULL, 't12@intermesh.localhost', '$5$rounds=5000$qVTdj9Nq/IChDwAP$9NQI4n4iZhrv/jf6RBa2WI1z.6l8Z4FVydr3gyvpp3/', 't12', 'intermesh.localhost/t12/Maildir/', 'intermesh.localhost/t12/', 524288, 1568632558, 1568632558, 1, 0),
(13, 5, NULL, 'tom-ketil.sundseth@tbb.no', '$5$rounds=5000$wDhJ4/R.QeBnyLpY$KX0vc2Texuo4JlzuJDJczpnIAJpzuheWHszzMcM4k/D', 'Tom Ketil Sundseth', 'tbb.no/tom-ketil.sundseth/Maildir/', 'tbb.no/tom-ketil.sundseth/', 524288, 1568633866, 1568633866, 1, 0),
(14, 5, NULL, 'vidar.storloes@tbb.no', '$5$rounds=5000$OGZUF8/N3tb6ZzKO$K6/.mhIRZ./m2Qn0geFA.uzC06PzmmV0cd1qD9GZeL1', 'Vidar Storløs', 'tbb.no/vidar.storloes/Maildir/', 'tbb.no/vidar.storloes/', 524288, 1568634312, 1568634312, 1, 0),
(15, 5, NULL, 'tommy.nielsen@tbb.no', '$5$rounds=5000$svt0OKOFLgVW6NkW$0yzz/dMv4NQrrM.3.1uKmU9pZNJGDB861I/RfLXq7J7', 'Tommy-Nicolai Nielsen', 'tbb.no/tommy.nielsen/Maildir/', 'tbb.no/tommy.nielsen/', 524288, 1568634313, 1568634313, 1, 0),
(16, 5, NULL, 'tom.erik.hermansen@tbb.no', '$5$rounds=5000$TXxjhf9ay5eiIM35$O6iwABHX0Dgaoqlmxhi6qHJZbsQ5EPCIQ4afCKLTrG1', 'Tom Erik Hermansen', 'tbb.no/tom.erik.hermansen/Maildir/', 'tbb.no/tom.erik.hermansen/', 524288, 1568634314, 1568634314, 1, 0),
(17, 5, NULL, 'thomas.syvertsen@tbb.no', '$5$rounds=5000$waEoGguVEnP/R8Bp$D.JDvT6QEXFcIyH5KZ8QOusNb2XViktentSZzWB0SM0', 'Thomas Syvertsen', 'tbb.no/thomas.syvertsen/Maildir/', 'tbb.no/thomas.syvertsen/', 524288, 1568634314, 1568634314, 1, 0),
(18, 5, NULL, 'thomas.malmo@tbb.no', '$5$rounds=5000$LnMvbHcWTGf32Zio$x7a2yJB/Y3trfvvWF6YNwSZtA2N9Jtx.GTGcgIrYDw1', 'Thomas Malmo', 'tbb.no/thomas.malmo/Maildir/', 'tbb.no/thomas.malmo/', 524288, 1568634315, 1568634315, 1, 0),
(19, 5, NULL, 'thomas.johansen@tbb.no', '$5$rounds=5000$HVpR7E/tIf.g.8uP$rOR2wjlqhLsI2ipYEB7RxIZzTzajxv49hHwoh0Cl3a5', 'Thomas Johansen', 'tbb.no/thomas.johansen/Maildir/', 'tbb.no/thomas.johansen/', 524288, 1568634315, 1568634315, 1, 0),
(20, 5, NULL, 'terje.sorum@tbb.no', '$5$rounds=5000$aEyDOMtockLDoU50$QA0FiauPwCry/j/aI4IdC3Rd0cPqZryPibuJ5rHMF30', 'Terje Sørum', 'tbb.no/terje.sorum/Maildir/', 'tbb.no/terje.sorum/', 524288, 1568634316, 1568634316, 1, 0),
(21, 5, NULL, 'svein.storoy@tbb.no', '$5$rounds=5000$Xw7zqdQD.vNdwBue$jkMhM6ERfi3d7S8pFWl7Bp3ItfX8YjuTInwzFU8B4oA', 'Svein-Martin Storøy', 'tbb.no/svein.storoy/Maildir/', 'tbb.no/svein.storoy/', 524288, 1568634316, 1568634316, 1, 0),
(22, 5, NULL, 'stian.trebekk@tbb.no', '$5$rounds=5000$gK7RyNt8DN77w/el$Y9NcV/cYk2tUMFDz4imZXqe.zCLK9OZuaiYkF9Guca6', 'Stian Trebekk', 'tbb.no/stian.trebekk/Maildir/', 'tbb.no/stian.trebekk/', 524288, 1568634317, 1568634317, 1, 0),
(23, 5, NULL, 'steve.hermansen@tbb.no', '$5$rounds=5000$H6h5ddTwJUwS7fUi$PCRc614u26B6PdNWAxdlHFUSL4bH8JFJMGWyeKiaPT/', 'Steve Romsdal Hermansen', 'tbb.no/steve.hermansen/Maildir/', 'tbb.no/steve.hermansen/', 524288, 1568634317, 1568634317, 1, 0),
(24, 5, NULL, 'staale.lilledahl@tbb.no', '$5$rounds=5000$SviITAyPgvmXhLBX$6wSVjiNc.ScfnI2DALsq2vQU6j8oNyLQWm6TqYQu5V1', 'Ståle Lilledahl', 'tbb.no/staale.lilledahl/Maildir/', 'tbb.no/staale.lilledahl/', 524288, 1568634318, 1568634318, 1, 0),
(25, 5, NULL, 'steffen.kristensen@tbb.no', '$5$rounds=5000$ZIB5RCnC9wURq3aK$gXBztJvobTy19CkzNuN8l7wxOEXRasAh.Xbq55z0FI4', 'Steffen Kristensen', 'tbb.no/steffen.kristensen/Maildir/', 'tbb.no/steffen.kristensen/', 524288, 1568634319, 1568634319, 1, 0),
(26, 5, NULL, 'stig.borgersrud@tbb.no', '$5$rounds=5000$/8EoUZAGtl2B22VN$6onX97tWyS/zh50z5OtCTF3rgjkxSbjPNKAZg8QRL94', 'Stig-Arild Borgersrud', 'tbb.no/stig.borgersrud/Maildir/', 'tbb.no/stig.borgersrud/', 524288, 1568634319, 1568634319, 1, 0),
(27, 5, NULL, 'jan-egil.ottosen@tbb.no', '$5$rounds=5000$5ex58IVSPTEkxYur$Pst2rGi.cHF94dKgkltPQmgiwAcT52GP04FtwFY.Sb2', 'Jan Egil Ottosen', 'tbb.no/jan-egil.ottosen/Maildir/', 'tbb.no/jan-egil.ottosen/', 524288, 1568634320, 1568634320, 1, 0),
(28, 5, NULL, 'roy.lilliedahl@tbb.no', '$5$rounds=5000$0SCImYT.jIteYc6o$MP49eoSnyq3Gp/V5ZdD8f/ma8QXWZUW6lBAihG1LAS6', 'Roy Lilliedahl', 'tbb.no/roy.lilliedahl/Maildir/', 'tbb.no/roy.lilliedahl/', 524288, 1568634320, 1568634320, 1, 0),
(29, 5, NULL, 'ronnie.husberg@tbb.no', '$5$rounds=5000$fZTwLEXWRJOLQIiG$g8vVucjzsZjLmrXfVdHic0.wNU2jMca0SIRraJ6GEI3', 'Ronnie Husberg', 'tbb.no/ronnie.husberg/Maildir/', 'tbb.no/ronnie.husberg/', 524288, 1568634321, 1568634321, 1, 0),
(30, 5, NULL, 'per.bjerkengen@tbb.no', '$5$rounds=5000$ZY4jWyS7LOhYXtjT$NdScPKiiByWxi1CQw93UccrNFdrZPMHVXDnL3YW8dA3', 'Per Olav Bjerkengen', 'tbb.no/per.bjerkengen/Maildir/', 'tbb.no/per.bjerkengen/', 524288, 1568634321, 1568634321, 1, 0),
(31, 5, NULL, 'oivind.johnsen@tbb.no', '$5$rounds=5000$Dlnsq1Mi.S5nch5W$q0tAZAfi9uoaWdZy9OMzuS7TIJkoTxHy5LFrx1fcpj9', 'Øivind Johnsen', 'tbb.no/oivind.johnsen/Maildir/', 'tbb.no/oivind.johnsen/', 524288, 1568634322, 1568634322, 1, 0),
(32, 5, NULL, 'odd-egil.torgersen@tbb.no', '$5$rounds=5000$9B4q7t0AUbmfUccT$Ffw/NhahVCtoSYipMwqcg4.8X4vAXsgohuIknQoaBP5', 'Odd-Egil Torgersen', 'tbb.no/odd-egil.torgersen/Maildir/', 'tbb.no/odd-egil.torgersen/', 524288, 1568634323, 1568634323, 1, 0),
(33, 5, NULL, 'monica.loftskjær@tbb.no', '$5$rounds=5000$x1fXo41RDOV4vy4a$72c4Kj8Sr0yst0XaAf9iTwPARazg5eZaaapUNNh1gb3', 'Monica Loftskjær', 'tbb.no/monica.loftskjær/Maildir/', 'tbb.no/monica.loftskjær/', 524288, 1568634417, 1568634417, 1, 0),
(34, 5, NULL, 'martin.olsen@tbb.no', '$5$rounds=5000$3wYIBnVF.Ig4qWN/$gFBodXk5qd9CZJ0S6qbKf5.pqxQyNa2oaMuyNV3co10', 'Martin Olsen', 'tbb.no/martin.olsen/Maildir/', 'tbb.no/martin.olsen/', 524288, 1568634419, 1568634419, 1, 0),
(35, 5, NULL, 'lars.sletta@tbb.no', '$5$rounds=5000$JIOUaswvML8T/QlE$AxdRqGiywNgfY9Fh.vGZyfP0H0BWf5FZVjGLFvUga6D', 'Lars Erik Sletta', 'tbb.no/lars.sletta/Maildir/', 'tbb.no/lars.sletta/', 524288, 1568634424, 1568634424, 1, 0),
(36, 5, NULL, 'lars.kristiansen@tbb.no', '$5$rounds=5000$6iAat5lOZO4YIkCf$7pByn0d9Pd0CW3.aVesnQs2qPC/lmc.LYQBa8Bv.iJ4', 'Lars Kristiansen', 'tbb.no/lars.kristiansen/Maildir/', 'tbb.no/lars.kristiansen/', 524288, 1568634424, 1568634424, 1, 0),
(37, 5, NULL, 'ken.henriksen@tbb.no', '$5$rounds=5000$.j6XX7aRlS3CSACF$PiOVX62zDmNcNoUCs5GvbX6Qa06.mFFDR8CviVLiaw4', 'Ken Nicolai Henriksen', 'tbb.no/ken.henriksen/Maildir/', 'tbb.no/ken.henriksen/', 524288, 1568634425, 1568634425, 1, 0),
(38, 5, NULL, 'jan-tore.borresen@tbb.no', '$5$rounds=5000$vBcIFU5aRjPYPxRA$JQ5aymKouAVsuBPzibf83kr/Dyj4BbGFsP8thkFKnV4', 'Jan-Tore Børresen', 'tbb.no/jan-tore.borresen/Maildir/', 'tbb.no/jan-tore.borresen/', 524288, 1568634426, 1568634426, 1, 0),
(39, 5, NULL, 'thomas.simonsen@tbb.no', '$5$rounds=5000$jCHQ2w62DGgGiabs$v3tN2X51ubX.xdEfcwtiYUs8yhiW.QA37KHmDTBPw/B', 'Thomas Raasoch Simonsen', 'tbb.no/thomas.simonsen/Maildir/', 'tbb.no/thomas.simonsen/', 524288, 1568634426, 1568634426, 1, 0),
(40, 5, NULL, 'giedrius.navagruckas@tbb.no', '$5$rounds=5000$krGbQ.zCRShNb45/$Ks0Sis3owE8V4zvJc5JRBmRrzFnH0/Z74PrTUGCWAF4', 'Giedrius Navagruckas', 'tbb.no/giedrius.navagruckas/Maildir/', 'tbb.no/giedrius.navagruckas/', 524288, 1568634427, 1568634427, 1, 0),
(41, 5, NULL, 'frode.pedersen@tbb.no', '$5$rounds=5000$87i8XtXVEc6ORgAs$KlqELjoJe2xa9nLzCjYS/5DgQSOGtXGI/nAp8Zjz/a8', 'Frode Berg Pedersen', 'tbb.no/frode.pedersen/Maildir/', 'tbb.no/frode.pedersen/', 524288, 1568634427, 1568634427, 1, 0),
(42, 5, NULL, 'finn.poulsen@tbb.no', '$5$rounds=5000$qbLDFluTEhNn.KaS$.xOaliqgNTnNxbRDWViZbmuAjWJajbfrAs6dQyGwxc7', 'Finn Poulsen', 'tbb.no/finn.poulsen/Maildir/', 'tbb.no/finn.poulsen/', 524288, 1568634428, 1568634428, 1, 0),
(43, 5, NULL, 'espen.simonsen@tbb.no', '$5$rounds=5000$IiJ9YgiaZAzlqzJU$l0k98UPDLsdK/tBxbqIw8b.Q6iserrWSSa.MPi8Skc5', 'Espen Simonsen', 'tbb.no/espen.simonsen/Maildir/', 'tbb.no/espen.simonsen/', 524288, 1568634429, 1568634429, 1, 0),
(44, 5, NULL, 'elisabeth.thun@tbb.no', '$5$rounds=5000$CObXYKr/JdNzQ3vG$zxt2qoO6VVWB6ybcSa7bYKLLDpWCJhsKFU9R7dzopU8', 'Elisabeth Thun', 'tbb.no/elisabeth.thun/Maildir/', 'tbb.no/elisabeth.thun/', 524288, 1568634429, 1568634429, 1, 0),
(45, 5, NULL, 'dace.pedersen@tbb.no', '$5$rounds=5000$ufrL//EBjsHvbXC8$wZ4NFrVWIFeYvVVoFneRBiXv5N.RWXBKtxjgXOSao1/', 'Dace Pedersen', 'tbb.no/dace.pedersen/Maildir/', 'tbb.no/dace.pedersen/', 524288, 1568634430, 1568634430, 1, 0),
(46, 5, NULL, 'carl.wiborg@tbb.no', '$5$rounds=5000$amSVBaLynzyNp20K$tvG7vAreJFqSH2b5CeE6LSipm1n2mHHU1cBKavw34hD', 'Carl Andre Wiborg', 'tbb.no/carl.wiborg/Maildir/', 'tbb.no/carl.wiborg/', 524288, 1568634430, 1568634430, 1, 0),
(47, 5, NULL, 'borre.lilledahl@tbb.no', '$5$rounds=5000$t9/l2MrKzR5SOvYt$SLlii5wQ5on4OzsdwgbXgFH2Vw8cVJEGN1q6eeSAX1A', 'Børre Lilledahl', 'tbb.no/borre.lilledahl/Maildir/', 'tbb.no/borre.lilledahl/', 524288, 1568634432, 1568634432, 1, 0),
(48, 5, NULL, 'benjamin.vinland@tbb.no', '$5$rounds=5000$FkzHJJyw4JlVP2r7$5jM8klXCSeiy4IFO9c0Wd7RrnLEFEYkkwyXfltQlqR7', 'Benjamin Vinland Årrestad', 'tbb.no/benjamin.vinland/Maildir/', 'tbb.no/benjamin.vinland/', 524288, 1568634437, 1568634437, 1, 0),
(49, 5, NULL, 'frode.johnsen@tbb.no', '$5$rounds=5000$RElew580VHZiUjPe$F8yV5DzCuF2paGamPl3qJhwzBqr4sEIUxSSeHFoyl/3', 'Frode Gullbekk Johnsen', 'tbb.no/frode.johnsen/Maildir/', 'tbb.no/frode.johnsen/', 524288, 1568634437, 1568634437, 1, 0),
(50, 5, NULL, 'einar.kristensen@tbb.no', '$5$rounds=5000$2BPAGkbV.Er19Xt.$B2h3jg6M2CIlHsZqSFI0t6MHNuh313iUNtybdUmpmZ5', 'Einar Kristensen', 'tbb.no/einar.kristensen/Maildir/', 'tbb.no/einar.kristensen/', 524288, 1568634438, 1568634438, 1, 0),
(51, 5, NULL, 'mayvellyn.musken@tbb.no', '$5$rounds=5000$51J5Da5zOtRh4UzB$9qZ89CWOKw/1IiChtRfXo422FuwYcRuxeO2wNDUIFI1', 'Mayvellyn Malacas Musken', 'tbb.no/mayvellyn.musken/Maildir/', 'tbb.no/mayvellyn.musken/', 524288, 1568634438, 1568634438, 1, 0),
(52, 5, NULL, 'rodrigo.almario@tbb.no', '$5$rounds=5000$zTfFqLCJvDt5tMHJ$amqIyljoS6oD3WrJbULYsymDoCdKKwRH8Cj6wINQbD.', 'Almario Rodrigo Arturo Martinez', 'tbb.no/rodrigo.almario/Maildir/', 'tbb.no/rodrigo.almario/', 524288, 1568634439, 1568634439, 1, 0),
(53, 5, NULL, 'dejan.ogorelica@tbb.no', '$5$rounds=5000$iFGHU9o6BkqH7zaZ$rV0yFLaLESSIOYmGxv2iaRW7v4xYUIOXPg7y4bXoTTD', 'Dejan Ogorelica', 'tbb.no/dejan.ogorelica/Maildir/', 'tbb.no/dejan.ogorelica/', 524288, 1568634439, 1568634439, 1, 0),
(54, 5, NULL, 'jenderi.salazar@tbb.no', '$5$rounds=5000$7.pBnD8heUmP2Y4H$hCZ9JwAp5WwHon8pSEoHJSevoYcOOI5fyVk9oiLBf8D', 'J`enderi C O Ryan Salazar', 'tbb.no/jenderi.salazar/Maildir/', 'tbb.no/jenderi.salazar/', 524288, 1568634440, 1568634440, 1, 0),
(55, 5, NULL, 'ania.wentowska@tbb.no', '$5$rounds=5000$ODf5luvL7ka6PUn3$1VSxCRxxYaHVM1u/bl.3glG3pFP/RlPOvinLtGcplT/', 'Ania Wentowska', 'tbb.no/ania.wentowska/Maildir/', 'tbb.no/ania.wentowska/', 524288, 1568634440, 1568634440, 1, 0),
(56, 5, NULL, 'bjorn.alknes@tbb.no', '$5$rounds=5000$tXS9a.LUYbH6OPI4$I709KOsz.4mRjZAIRTJFVHn3wMGPLmEK7SQcRalr0A.', 'Bjørn Alknes', 'tbb.no/bjorn.alknes/Maildir/', 'tbb.no/bjorn.alknes/', 524288, 1568634441, 1568634441, 1, 0),
(57, 5, NULL, 'richard.bjornstad@tbb.no', '$5$rounds=5000$UcRg7AhP4RZ4R.iV$LSiMnT2AeRegG0UVtJAtir0Bxm7iwKJ42xDDSbAnSM.', 'Richard Bjørnstad', 'tbb.no/richard.bjornstad/Maildir/', 'tbb.no/richard.bjornstad/', 524288, 1568634441, 1568634441, 1, 0),
(58, 5, NULL, 'terje.carlsen@tbb.no', '$5$rounds=5000$zE40P3H05w31bNi1$z/bTZM6Fwka3ht/scSrIbe0bJ5NdhVdGv49NUR8FRM7', 'Terje Carlsen', 'tbb.no/terje.carlsen/Maildir/', 'tbb.no/terje.carlsen/', 524288, 1568634442, 1568634442, 1, 0),
(59, 5, NULL, 'kim.hjemstad@tbb.no', '$5$rounds=5000$sV0Tq/1DUgjTraOp$lO.b62m6BTimkM8Kfz9bH2AHaiHIf6NZHaSJwwIyQZ2', 'Kim Hjemstad', 'tbb.no/kim.hjemstad/Maildir/', 'tbb.no/kim.hjemstad/', 524288, 1568634443, 1568634443, 1, 0),
(60, 5, NULL, 'roger.haaland@tbb.no', '$5$rounds=5000$XfYfjCjbvkkENuKx$JmU4FyUG5MMxB.LerTBnAKELbGLniRfAQIS5f6xEyt4', 'Roger Haaland', 'tbb.no/roger.haaland/Maildir/', 'tbb.no/roger.haaland/', 524288, 1568634443, 1568634443, 1, 0),
(61, 5, NULL, 'oivind.lindbaek@tbb.no', '$5$rounds=5000$j6Gw.jZErh50H0.k$0cvvrpiTKB4kAijsL3h2GCayO4gHFk3AzunAUCnXjPD', 'Øivind Lukas Lindbæk', 'tbb.no/oivind.lindbaek/Maildir/', 'tbb.no/oivind.lindbaek/', 524288, 1568634444, 1568634444, 1, 0),
(62, 5, NULL, 'jakub jan.markowicz@tbb.no', '$5$rounds=5000$p2UO20q015EIhAHp$LhHG3LZ02NDd7PojBpXw5Of7ZuC76yhg3lrY8SxxKs4', 'Jakub Jan Markowicz', 'tbb.no/jakub jan.markowicz/Maildir/', 'tbb.no/jakub jan.markowicz/', 524288, 1568634444, 1568634444, 1, 0),
(63, 5, NULL, 'emmannouil.nikolaras@tbb.no', '$5$rounds=5000$EtU2tXhklZVwD4kg$B4S.MOn03srFz/VEu9dbdpD3bQOenp.ugwy5fEeG.z4', 'Emmannouil Nikolaras', 'tbb.no/emmannouil.nikolaras/Maildir/', 'tbb.no/emmannouil.nikolaras/', 524288, 1568634446, 1568634446, 1, 0),
(64, 5, NULL, 'boye.sandbakken@tbb.no', '$5$rounds=5000$Hp5CKNKXQN/M6eZU$Vz4/aYhasFpZE7FbXVpjJ/M6AlC5kBmvAqt7sNbPoh3', 'Boye Sandbakken', 'tbb.no/boye.sandbakken/Maildir/', 'tbb.no/boye.sandbakken/', 524288, 1568634451, 1568634451, 1, 0),
(65, 5, NULL, 'rupert.sheridan@tbb.no', '$5$rounds=5000$cz9V7k52HF.3bamZ$xxU/BEEshBuqIVlUG7doApYH2gVaa2qzB/EIGnSucA.', 'Rupert Ridewood Sheridan', 'tbb.no/rupert.sheridan/Maildir/', 'tbb.no/rupert.sheridan/', 524288, 1568634451, 1568634451, 1, 0),
(66, 5, NULL, 'oskar.sjodal@tbb.no', '$5$rounds=5000$0puYdoFBePsUdwFZ$IDqmOuqSVvqaczuAH8dEHBW7uNXU.ktqGKaVI.lhfV6', 'Per Oskar Sjødal', 'tbb.no/oskar.sjodal/Maildir/', 'tbb.no/oskar.sjodal/', 524288, 1568634452, 1568634452, 1, 0),
(67, 5, NULL, 'jonas.synnestvedt@tbb.no', '$5$rounds=5000$IYtMXUlx7KWRqjOO$JfdovnZb7g3XH6LnV/GeiOklNwtvAD5GlXFrkW1ZYM3', 'Jonas Synnestvedt', 'tbb.no/jonas.synnestvedt/Maildir/', 'tbb.no/jonas.synnestvedt/', 524288, 1568634452, 1568634452, 1, 0),
(68, 5, NULL, 'sander.solvberg@tbb.no', '$5$rounds=5000$iaX6Dm8DFeC4g5Mf$dPgnnmi.JF2x9dgfPrALeLTmNcipddxZOeRuuP4LDk1', 'Sander Sølvberg', 'tbb.no/sander.solvberg/Maildir/', 'tbb.no/sander.solvberg/', 524288, 1568634453, 1568634453, 1, 0),
(69, 5, NULL, 'arne.sorensen@tbb.no', '$5$rounds=5000$fdhFnMXfDBrXoQBa$Px.LQERlfyd6pzM7ofzhRdzG4cH8NtiO7PNuzjbjJr6', 'Arne Sørensen', 'tbb.no/arne.sorensen/Maildir/', 'tbb.no/arne.sorensen/', 524288, 1568634453, 1568634453, 1, 0),
(70, 5, NULL, 'andrzej.walenttek@tbb.no', '$5$rounds=5000$RRHuvoo5hMTRNk/Q$3RD3qE8eU2ulgbUcWUTTWowtR.VJUOsg3AUcAEcwrM1', 'Andrzej Walenttek', 'tbb.no/andrzej.walenttek/Maildir/', 'tbb.no/andrzej.walenttek/', 524288, 1568634454, 1568634454, 1, 0),
(71, 1, NULL, 't13@intermesh.localhost', '$5$rounds=5000$71R4nLF8y5jXV9VK$/Z1Pk5f3PQL2TosM8Upok2lBFLGExj.BZddhofCyJuB', 't13', 'intermesh.localhost/t13/Maildir/', 'intermesh.localhost/t13/', 524288, 1568799892, 1568799892, 1, 0),
(72, 1, NULL, 't14@intermesh.localhost', '$5$rounds=5000$IhZqndeQN.hVM6NS$xsuHVbD9hwwwKae6wd7FBL8t.IULjzBoVs4EXzZhQI6', 't14', 'intermesh.localhost/t14/Maildir/', 'intermesh.localhost/t14/', 524288, 1568799932, 1568799932, 1, 0),
(73, 1, NULL, 't16@intermesh.localhost', '$5$rounds=5000$OOdtklFAn5WJWlDo$1/54M224FPhlIL30p9Eh/6R.UeczUBl6vuCO4xTMzCD', 't16', 'intermesh.localhost/t16/Maildir/', 'intermesh.localhost/t16/', 524288, 1568800247, 1568800247, 1, 0),
(74, 1, NULL, 't17@intermesh.localhost', '$5$rounds=5000$C9WgT1lkHBOa2n7o$yd66FqlbUi1H/oqNBruitG3hYJ24lmbQhsWCGRyQhNC', 't17', 'intermesh.localhost/t17/Maildir/', 'intermesh.localhost/t17/', 524288, 1568801006, 1568801006, 1, 0),
(75, 1, NULL, 't18@intermesh.localhost', '$5$rounds=5000$8LH1Bgn0RzW/OVtd$.jwqLysLvZg/A8zyBDW1qnt.nb97DD08dzWJ0Cint0A', 't18', 'intermesh.localhost/t18/Maildir/', 'intermesh.localhost/t18/', 524288, 1568982413, 1568982413, 1, 0),
(76, 1, NULL, 't19@intermesh.localhost', '$5$rounds=5000$e2hQ3eMNiITtzqAi$cYl/GLINPg5lwEd0wvxJDFevg9Gf6.AeTpubRryp/u/', 't19', 'intermesh.localhost/t19/Maildir/', 'intermesh.localhost/t19/', 524288, 1568982656, 1568982656, 1, 0),
(77, 1, NULL, 't21@intermesh.localhost', '$5$rounds=5000$b2k6NeUbLnWjBqlR$JQbyFBlKsUo.sUpC3cYICPhcHRxbeWJsNw4pKG8qOB7', 't21', 'intermesh.localhost/t21/Maildir/', 'intermesh.localhost/t21/', 524288, 1568983553, 1568983553, 1, 0),
(78, 1, NULL, 't23@intermesh.localhost', '$5$rounds=5000$9wMOXjZjlXqH53RH$XpWb2L3nJCjKj8U.44bEFWqcjX3mSjMdhFohN3I7sD6', 't23', 'intermesh.localhost/t23/Maildir/', 'intermesh.localhost/t23/', 524288, 1568983703, 1568983703, 1, 0),
(79, 1, NULL, 't24@intermesh.localhost', '$5$rounds=5000$ybx8TNTseTrXb1oo$wEXS32.K9vpR8XXzqVncL9sQTatVzNYDsUYMD8zjo.4', 't24', 'intermesh.localhost/t24/Maildir/', 'intermesh.localhost/t24/', 524288, 1568993496, 1568993496, 1, 0),
(80, 1, NULL, 't25@intermesh.localhost', '$5$rounds=5000$UQHvgUFEYtkmOyzs$BHLGFFra3D5yjbuwvRUiZg4A3/M044zuobHf24QzTJ9', 't25', 'intermesh.localhost/t25/Maildir/', 'intermesh.localhost/t25/', 524288, 1568993541, 1568993541, 1, 0),
(81, 1, NULL, 't26@intermesh.localhost', '$5$rounds=5000$PhOlpWvNuJxt.FpV$.GZs3wVl.dG4Xu4EqiGibTcyKBMHoV649NIOTYX6dx5', 't26', 'intermesh.localhost/t26/Maildir/', 'intermesh.localhost/t26/', 524288, 1568993554, 1568993554, 1, 0),
(82, 1, NULL, 't27@intermesh.localhost', '$5$rounds=5000$SugEXFig9zh91jtV$1hISkQXrnp63UVpaILoNREQOdaLktOUggN2KAtNiQI6', 't27', 'intermesh.localhost/t27/Maildir/', 'intermesh.localhost/t27/', 524288, 1568993577, 1568993577, 1, 0),
(83, 1, NULL, 't31@intermesh.localhost', '$5$rounds=5000$.feYGrC.YiMdITlY$EPQ7LubevftYSxZf0VlDPn/LBJLfILu8s1EHbpPs4p8', 't31', 'intermesh.localhost/t31/Maildir/', 'intermesh.localhost/t31/', 524288, 1569500801, 1569500801, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_default_resources`
--

CREATE TABLE `pr2_default_resources` (
  `template_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `budgeted_units` double NOT NULL DEFAULT 0,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  `apply_internal_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `apply_external_overtime` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_employees`
--

CREATE TABLE `pr2_employees` (
  `user_id` int(11) NOT NULL,
  `closed_entries_time` int(11) DEFAULT NULL,
  `ctime` int(11) DEFAULT NULL,
  `mtime` int(11) DEFAULT NULL,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_employees`
--

INSERT INTO `pr2_employees` (`user_id`, `closed_entries_time`, `ctime`, `mtime`, `external_fee`, `internal_fee`) VALUES
(1, NULL, 1565707867, 1565707867, 0, 0),
(2, NULL, 1561972066, 1561972066, 120, 60),
(3, NULL, 1561972066, 1561972066, 80, 40),
(4, NULL, 1561972066, 1561972066, 90, 45);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_employee_activity_rate`
--

CREATE TABLE `pr2_employee_activity_rate` (
  `activity_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `external_rate` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_expenses`
--

CREATE TABLE `pr2_expenses` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `nett` double NOT NULL DEFAULT 0,
  `vat` double NOT NULL DEFAULT 0,
  `date` int(11) NOT NULL DEFAULT 0,
  `invoice_id` varchar(100) NOT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `mtime` int(11) NOT NULL,
  `expense_budget_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pr2_expenses`
--

INSERT INTO `pr2_expenses` (`id`, `project_id`, `nett`, `vat`, `date`, `invoice_id`, `description`, `mtime`, `expense_budget_id`) VALUES
(1, 2, 3000, 21, 1561972066, '1234', 'Rocket fuel', 1561972066, NULL),
(2, 2, 2000, 21, 1561972066, '1235', 'Fuse machine', 1561972066, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_expense_budgets`
--

CREATE TABLE `pr2_expense_budgets` (
  `id` int(11) NOT NULL,
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
  `contact_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_expense_budgets`
--

INSERT INTO `pr2_expense_budgets` (`id`, `description`, `nett`, `vat`, `ctime`, `mtime`, `supplier_company_id`, `budget_category_id`, `project_id`, `comments`, `id_number`, `quantity`, `unit_type`, `contact_id`) VALUES
(1, 'Machinery', 10000, 0, 1561972066, 1561972066, NULL, NULL, 2, '', '', 1, '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_hours`
--

CREATE TABLE `pr2_hours` (
  `id` int(11) NOT NULL,
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
  `travel_costs` double NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_hours`
--

INSERT INTO `pr2_hours` (`id`, `user_id`, `duration`, `type`, `date`, `units`, `comments`, `external_fee`, `internal_fee`, `status`, `income_id`, `ctime`, `mtime`, `project_id`, `standard_task_id`, `task_id`, `travel_distance`, `travel_costs`) VALUES
(1, 1, 534, 0, 1565676000, 8.9, '', 0, 0, 0, NULL, 1565708049, 1565708049, 4, NULL, 0, 50, 50);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_hours_custom_fields`
--

CREATE TABLE `pr2_hours_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_hours_custom_fields`
--

INSERT INTO `pr2_hours_custom_fields` (`id`, `Custom`) VALUES
(1, '');

-- --------------------------------------------------------

--
-- Table structure for table `pr2_income`
--

CREATE TABLE `pr2_income` (
  `id` int(11) NOT NULL,
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
  `contact` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_income_items`
--

CREATE TABLE `pr2_income_items` (
  `id` int(11) NOT NULL,
  `income_id` int(11) NOT NULL,
  `amount` double NOT NULL DEFAULT 0,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_portlet_statuses`
--

CREATE TABLE `pr2_portlet_statuses` (
  `user_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_projects`
--

CREATE TABLE `pr2_projects` (
  `id` int(11) NOT NULL,
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
  `reference_no` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_projects`
--

INSERT INTO `pr2_projects` (`id`, `user_id`, `acl_id`, `name`, `customer`, `description`, `company_id`, `ctime`, `mtime`, `threshold_mails`, `muser_id`, `start_time`, `due_time`, `contact_id`, `contact`, `files_folder_id`, `responsible_user_id`, `calendar_id`, `event_id`, `path`, `income_type`, `status_id`, `type_id`, `template_id`, `parent_project_id`, `default_distance`, `travel_costs`, `reference_no`) VALUES
(1, 1, 111, 'Demo', '', 'Just a placeholder for sub projects.', NULL, 1561972066, 1570708815, NULL, 1, 1561972066, 0, NULL, NULL, 47, 0, 0, 0, 'Demo', 1, 1, 2, 1, 0, NULL, 0, ''),
(2, 1, 111, '[001] Develop Rocket 2000', '', 'Better range and accuracy', 13, 1561972066, 1570708815, NULL, 1, 1561932000, 1564610400, 2, '', 48, 0, 0, 0, 'Demo/[001] Develop Rocket 2000', 1, 1, 2, 2, 1, 10, 10, ''),
(3, 1, 111, '[001] Develop Rocket Launcher', '', 'Better range and accuracy', 13, 1561972066, 1561972066, NULL, 1, 1561972066, 1564650466, 2, NULL, 0, 0, 0, 0, 'Demo/[001] Develop Rocket Launcher', 1, 1, 2, 2, 1, NULL, 0, ''),
(4, 1, 52, 't1', '', '', NULL, 1565707915, 1565707925, NULL, 1, 1565647200, 0, NULL, '', 0, 1, 0, 0, 'Demo/t1', 1, 1, 1, 2, 1, 50, 50, ''),
(5, 1, 52, 'Hi Hubert', 'ACME Corporation', '', 13, 1571047876, 1571047876, NULL, 1, 1571004000, 0, NULL, 'System Administrator (Users)', 0, 1, 0, 0, 'Hi Hubert', 1, 1, 1, 2, 0, NULL, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `pr2_projects_custom_fields`
--

CREATE TABLE `pr2_projects_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_projects_custom_fields`
--

INSERT INTO `pr2_projects_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, '');

-- --------------------------------------------------------

--
-- Table structure for table `pr2_resources`
--

CREATE TABLE `pr2_resources` (
  `project_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `budgeted_units` double NOT NULL DEFAULT 0,
  `external_fee` double NOT NULL DEFAULT 0,
  `internal_fee` double NOT NULL DEFAULT 0,
  `apply_internal_overtime` tinyint(1) NOT NULL DEFAULT 0,
  `apply_external_overtime` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_resources`
--

INSERT INTO `pr2_resources` (`project_id`, `user_id`, `budgeted_units`, `external_fee`, `internal_fee`, `apply_internal_overtime`, `apply_external_overtime`) VALUES
(2, 1, 0, 0, 0, 0, 0),
(2, 2, 16, 120, 60, 0, 0),
(2, 3, 100, 80, 40, 0, 0),
(2, 4, 16, 90, 45, 0, 0),
(3, 1, 0, 0, 0, 0, 0),
(3, 3, 16, 80, 40, 0, 0),
(4, 1, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_resource_activity_rate`
--

CREATE TABLE `pr2_resource_activity_rate` (
  `activity_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `external_rate` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_standard_tasks`
--

CREATE TABLE `pr2_standard_tasks` (
  `id` int(11) NOT NULL,
  `code` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `units` double NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_billable` tinyint(1) NOT NULL DEFAULT 1,
  `is_always_billable` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_standard_tasks`
--

INSERT INTO `pr2_standard_tasks` (`id`, `code`, `name`, `units`, `description`, `disabled`, `is_billable`, `is_always_billable`) VALUES
(1, '3', 'ccc', 1, '', 0, 1, 0),
(2, '1', 'aaa', 1, '', 0, 1, 0),
(3, '2', 'bbb', 0, '', 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_statuses`
--

CREATE TABLE `pr2_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `complete` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `filterable` tinyint(1) NOT NULL DEFAULT 1,
  `show_in_tree` tinyint(1) NOT NULL DEFAULT 1,
  `make_invoiceable` tinyint(1) NOT NULL DEFAULT 0,
  `not_for_postcalculation` tinyint(1) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_statuses`
--

INSERT INTO `pr2_statuses` (`id`, `name`, `complete`, `sort_order`, `filterable`, `show_in_tree`, `make_invoiceable`, `not_for_postcalculation`, `acl_id`) VALUES
(1, 'Ongoing', 0, 0, 1, 1, 0, 0, 54),
(2, 'None', 0, 0, 1, 1, 0, 0, 55),
(3, 'Complete', 1, 0, 1, 0, 0, 0, 56);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_tasks`
--

CREATE TABLE `pr2_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `percentage_complete` tinyint(4) NOT NULL DEFAULT 0,
  `duration` double NOT NULL DEFAULT 60,
  `due_date` int(11) DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int(11) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `has_children` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_tasks`
--

INSERT INTO `pr2_tasks` (`id`, `project_id`, `user_id`, `percentage_complete`, `duration`, `due_date`, `description`, `sort_order`, `parent_id`, `has_children`) VALUES
(1, 2, 3, 0, 480, NULL, 'Design', 1, 0, 1),
(2, 2, 3, 100, 480, NULL, 'Functional design', 2, 1, 0),
(3, 2, 3, 50, 480, NULL, 'Technical design', 3, 1, 0),
(4, 2, 3, 0, 480, NULL, 'Implementation', 4, 0, 1),
(5, 2, 3, 0, 240, NULL, 'Models', 5, 4, 0),
(6, 2, 3, 0, 120, NULL, 'Controllers', 6, 4, 0),
(7, 2, 3, 0, 360, NULL, 'Views', 7, 4, 0),
(8, 2, 3, 0, 480, NULL, 'Testing', 8, 0, 1),
(9, 2, 2, 0, 480, NULL, 'GUI', 9, 8, 0),
(10, 2, 2, 0, 480, NULL, 'Security', 10, 8, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_templates`
--

CREATE TABLE `pr2_templates` (
  `id` int(11) NOT NULL,
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
  `show_in_tree` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_templates`
--

INSERT INTO `pr2_templates` (`id`, `user_id`, `name`, `acl_id`, `files_folder_id`, `fields`, `icon`, `project_type`, `default_income_email_template`, `default_status_id`, `default_type_id`, `use_name_template`, `name_template`, `show_in_tree`) VALUES
(1, 1, 'Projects folder', 57, 2, '', 'projects2/template-icons/folder.png', 0, NULL, 2, 1, 0, '', 1),
(2, 1, 'Standard project', 58, 3, 'responsible_user_id,expenses,customer,default_distance,contact,budget_fees,travel_costs', 'projects2/template-icons/project.png', 1, NULL, 1, 1, 0, '%y-{autoid}', 0);

-- --------------------------------------------------------

--
-- Table structure for table `pr2_templates_events`
--

CREATE TABLE `pr2_templates_events` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_offset` int(11) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `reminder` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL,
  `new_template_id` int(11) NOT NULL DEFAULT 0,
  `template_id` int(11) NOT NULL,
  `for_manager` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_timers`
--

CREATE TABLE `pr2_timers` (
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `starttime` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pr2_types`
--

CREATE TABLE `pr2_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT 0,
  `acl_book` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pr2_types`
--

INSERT INTO `pr2_types` (`id`, `name`, `user_id`, `acl_id`, `acl_book`) VALUES
(1, 'Default', 1, 52, 53),
(2, 'Demo', 1, 111, 112);

-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE `site_content` (
  `id` int(11) NOT NULL,
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
  `content_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'markdown'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_content_custom_fields`
--

CREATE TABLE `site_content_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_menu`
--

CREATE TABLE `site_menu` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `menu_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_menu_item`
--

CREATE TABLE `site_menu_item` (
  `menu_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_children` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `target` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_multifile_files`
--

CREATE TABLE `site_multifile_files` (
  `model_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_sites`
--

CREATE TABLE `site_sites` (
  `id` int(11) NOT NULL,
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
  `files_folder_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `site_sites_custom_fields`
--

CREATE TABLE `site_sites_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `su_announcements`
--

CREATE TABLE `su_announcements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `acl_id` int(11) NOT NULL,
  `due_time` int(11) NOT NULL DEFAULT 0,
  `ctime` int(11) NOT NULL DEFAULT 0,
  `mtime` int(11) NOT NULL DEFAULT 0,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `su_announcements`
--

INSERT INTO `su_announcements` (`id`, `user_id`, `acl_id`, `due_time`, `ctime`, `mtime`, `title`, `content`) VALUES
(1, 1, 109, 0, 1561972065, 1561972065, 'Submit support ticket', 'Anyone can submit tickets to the support system here:<br /><br /><a href=\"https://localhost:63/modules/site/index.php?r=tickets/externalpage/newTicket\">https://localhost:63/modules/site/index.php?r=tickets/externalpage/newTicket</a><br /><br />Anonymous ticket posting can be disabled in the ticket module settings.'),
(2, 1, 110, 0, 1561972065, 1561972065, 'Welcome to GroupOffice', 'This is a demo announcements that administrators can set.<br />Have a look around.<br /><br />We hope you\'ll enjoy Group-Office as much as we do!');

-- --------------------------------------------------------

--
-- Table structure for table `su_latest_read_announcement_records`
--

CREATE TABLE `su_latest_read_announcement_records` (
  `user_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL DEFAULT 0,
  `announcement_ctime` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `su_latest_read_announcement_records`
--

INSERT INTO `su_latest_read_announcement_records` (`user_id`, `announcement_id`, `announcement_ctime`) VALUES
(1, 2, 1561972065),
(2, 2, 1561972065),
(3, 2, 1561972065),
(4, 2, 1561972065),
(5, 2, 1561972065),
(6, 2, 1561972065);

-- --------------------------------------------------------

--
-- Table structure for table `su_notes`
--

CREATE TABLE `su_notes` (
  `user_id` int(11) NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `su_notes`
--

INSERT INTO `su_notes` (`user_id`, `text`) VALUES
(1, NULL),
(2, NULL),
(3, NULL),
(4, NULL),
(5, NULL),
(6, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `su_rss_feeds`
--

CREATE TABLE `su_rss_feeds` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `su_visible_calendars`
--

CREATE TABLE `su_visible_calendars` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `su_visible_calendars`
--

INSERT INTO `su_visible_calendars` (`user_id`, `calendar_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 7),
(6, 8);

-- --------------------------------------------------------

--
-- Table structure for table `su_visible_lists`
--

CREATE TABLE `su_visible_lists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_addressbook_user`
--

CREATE TABLE `sync_addressbook_user` (
  `addressBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_calendar_user`
--

CREATE TABLE `sync_calendar_user` (
  `calendar_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_calendar` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_devices`
--

CREATE TABLE `sync_devices` (
  `id` int(11) NOT NULL DEFAULT 0,
  `manufacturer` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `software_version` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `uri` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `UTC` enum('0','1') COLLATE utf8mb4_unicode_ci NOT NULL,
  `vcalendar_version` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_note_categories_user`
--

CREATE TABLE `sync_note_categories_user` (
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_category` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_settings`
--

CREATE TABLE `sync_settings` (
  `user_id` int(11) NOT NULL DEFAULT 0,
  `addressbook_id` int(11) NOT NULL DEFAULT 0,
  `calendar_id` int(11) NOT NULL DEFAULT 0,
  `tasklist_id` int(11) NOT NULL DEFAULT 0,
  `note_category_id` int(11) NOT NULL DEFAULT 0,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `server_is_master` tinyint(1) NOT NULL DEFAULT 1,
  `max_days_old` tinyint(4) NOT NULL DEFAULT 0,
  `delete_old_events` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_tasklist_user`
--

CREATE TABLE `sync_tasklist_user` (
  `tasklist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `default_tasklist` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sync_user_note_book`
--

CREATE TABLE `sync_user_note_book` (
  `noteBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ta_categories`
--

CREATE TABLE `ta_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ta_portlet_tasklists`
--

CREATE TABLE `ta_portlet_tasklists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ta_portlet_tasklists`
--

INSERT INTO `ta_portlet_tasklists` (`user_id`, `tasklist_id`) VALUES
(2, 1),
(3, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ta_settings`
--

CREATE TABLE `ta_settings` (
  `user_id` int(11) NOT NULL,
  `reminder_days` int(11) NOT NULL DEFAULT 0,
  `reminder_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `remind` tinyint(1) NOT NULL DEFAULT 0,
  `default_tasklist_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ta_settings`
--

INSERT INTO `ta_settings` (`user_id`, `reminder_days`, `reminder_time`, `remind`, `default_tasklist_id`) VALUES
(1, 0, '0', 0, 4),
(2, 0, '0', 0, 1),
(3, 0, '0', 0, 2),
(4, 0, '0', 0, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ta_tasklists`
--

CREATE TABLE `ta_tasklists` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT 0,
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ta_tasklists`
--

INSERT INTO `ta_tasklists` (`id`, `name`, `user_id`, `acl_id`, `files_folder_id`, `version`) VALUES
(1, 'Elmer Fudd', 2, 91, 24, 3),
(2, 'Demo User', 3, 96, 28, 3),
(3, 'Linda Smith', 4, 101, 32, 3),
(4, 'System Administrator', 1, 106, 35, 8);

-- --------------------------------------------------------

--
-- Table structure for table `ta_tasks`
--

CREATE TABLE `ta_tasks` (
  `id` int(11) NOT NULL,
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
  `project_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ta_tasks`
--

INSERT INTO `ta_tasks` (`id`, `uuid`, `tasklist_id`, `user_id`, `ctime`, `mtime`, `muser_id`, `start_time`, `due_time`, `completion_time`, `name`, `description`, `status`, `repeat_end_time`, `reminder`, `rrule`, `files_folder_id`, `category_id`, `priority`, `percentage_complete`, `project_id`) VALUES
(1, '69afb1ee-4123-5162-a477-c9256d9d6d0f', 2, 1, 1561972061, 1561972061, 1, 1561972061, 1562144861, 0, 'Feed the dog', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(2, '9a2db52a-912b-521d-94d6-336a72e0a531', 3, 1, 1561972061, 1561972061, 1, 1561972061, 1562058461, 0, 'Feed the dog', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(3, 'ee97359f-e341-5292-b9d9-63be3f9b3db9', 1, 1, 1561972061, 1561972061, 1, 1561972061, 1562058461, 0, 'Feed the dog', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(4, '511706f1-b2b6-51d8-9925-af89876d42f8', 2, 1, 1561972061, 1561972061, 1, 1561972061, 1562058461, 0, 'Prepare meeting', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(5, '9374d8e8-3dcc-5586-a23e-a0885e667e6d', 3, 1, 1561972062, 1561972062, 1, 1561972062, 1562058462, 0, 'Prepare meeting', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(6, 'dd44ae8c-8528-597c-aed1-e9f8bc5e21ee', 1, 1, 1561972062, 1561972062, 1, 1561972062, 1562058462, 0, 'Prepare meeting', NULL, 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(7, 'da0f4e45-4aa8-5a7a-8035-5a5742fd4285', 4, 1, 1561972062, 1561972062, 1, 1562231262, 1562231262, 0, 'Call: Smith Inc (Q19000001)', '', 'NEEDS-ACTION', 0, 1562231262, '', 0, 0, 1, 0, 0),
(8, '92b31122-f509-595e-bf5e-d7364cda75a7', 4, 1, 1561972063, 1561972063, 1, 1562231263, 1562231263, 0, 'Call: ACME Corporation (Q19000002)', '', 'NEEDS-ACTION', 0, 1562231263, '', 0, 0, 1, 0, 0),
(9, 'a5e3930e-4249-50ea-ac4b-4e00601dc116', 4, 1, 1572537367, 1572537367, 1, 1572476400, 1572476400, 0, 'ghjgj', '', 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(10, '78738742-a351-5271-b1aa-b850902b6dc3', 4, 1, 1572537521, 1572537521, 1, 1572476400, 1572476400, 0, 't1', '', 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(11, '5bcf1d4c-2a23-57b9-a3b0-e2b723ffb6d3', 4, 1, 1572537603, 1572537603, 1, 1572476400, 1572476400, 0, 't3', '', 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(12, '6872de0c-9e71-53c2-9611-03a438197e19', 4, 1, 1572537654, 1572537654, 1, 1572476400, 1572476400, 0, 'ghjgjhgjh1', '', 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0),
(13, '4c2934cd-16dc-5925-9763-854eeea13dc5', 4, 1, 1572538163, 1572538163, 1, 1572476400, 1572476400, 0, 't4', '', 'NEEDS-ACTION', 0, 0, '', 0, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ta_tasks_custom_fields`
--

CREATE TABLE `ta_tasks_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ta_tasks_custom_fields`
--

INSERT INTO `ta_tasks_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, ''),
(3, ''),
(4, ''),
(5, ''),
(6, ''),
(7, ''),
(8, ''),
(9, ''),
(10, ''),
(11, ''),
(12, ''),
(13, '');

-- --------------------------------------------------------

--
-- Table structure for table `test_a`
--

CREATE TABLE `test_a` (
  `id` int(11) NOT NULL,
  `propA` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_a_has_many`
--

CREATE TABLE `test_a_has_many` (
  `id` int(11) NOT NULL,
  `aId` int(11) NOT NULL,
  `propOfHasManyA` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_a_has_one`
--

CREATE TABLE `test_a_has_one` (
  `id` int(11) NOT NULL,
  `aId` int(11) NOT NULL,
  `propA` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_a_map`
--

CREATE TABLE `test_a_map` (
  `aId` int(11) NOT NULL,
  `anotherAId` int(11) NOT NULL,
  `description` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `test_b`
--

CREATE TABLE `test_b` (
  `id` int(11) NOT NULL,
  `propB` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cId` int(11) DEFAULT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_c`
--

CREATE TABLE `test_c` (
  `id` int(11) NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `test_d`
--

CREATE TABLE `test_d` (
  `id` int(11) NOT NULL,
  `propD` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `ti_groups`
--

CREATE TABLE `ti_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `acl_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ti_messages`
--

CREATE TABLE `ti_messages` (
  `id` int(11) NOT NULL,
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
  `template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_messages`
--

INSERT INTO `ti_messages` (`id`, `ticket_id`, `status_id`, `type_id`, `has_status`, `has_type`, `content`, `attachments`, `is_note`, `user_id`, `ctime`, `mtime`, `rate_id`, `rate_amount`, `rate_hours`, `rate_name`, `rate_cost_code`, `template_id`) VALUES
(1, 1, 0, 1, 0, 0, 'My rocket always circles back right at me? How do I aim right?', '', 0, 0, 1561972065, 1561972065, 0, 0, 0, '', NULL, NULL),
(2, 1, 0, 1, 0, 0, 'Haha, good thing he doesn\'t know Accelleratii Incredibus designed this rocket and he can\'t read this note.', '', 1, 2, 1561972065, 1561972065, 0, 0, 0, '', NULL, NULL),
(3, 1, -1, 1, 1, 0, 'Gee I don\'t know how that can happen. I\'ll send you some new ones!', '', 0, 2, 1561972065, 1561972065, 0, 0, 0, '', NULL, NULL),
(4, 2, 0, 1, 0, 0, 'The rockets are too slow to hit my fast moving target. Is there a way to speed them up?', '', 0, 0, 1561799265, 1561799265, 0, 0, 0, '', NULL, NULL),
(5, 2, 0, 1, 0, 0, 'Please respond faster. Can\'t you see this ticket is marked in red?', '', 0, 0, 1561972065, 1561972065, 0, 0, 0, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ti_rates`
--

CREATE TABLE `ti_rates` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT 0,
  `cost_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ti_settings`
--

CREATE TABLE `ti_settings` (
  `id` int(11) NOT NULL,
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
  `show_close_confirm` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_settings`
--

INSERT INTO `ti_settings` (`id`, `from_email`, `from_name`, `use_alternative_url`, `alternative_url`, `subject`, `default_type`, `logo`, `customer_message`, `response_message`, `notify_contact`, `language`, `expire_days`, `never_close_status_id`, `disable_reminder_assigned`, `disable_reminder_unanswered`, `enable_external_page`, `allow_anonymous`, `external_page_css`, `leave_type_blank_by_default`, `new_ticket`, `new_ticket_msg`, `assigned_to`, `assigned_to_msg`, `notify_agent`, `notify_agent_msg`, `notify_due_date`, `notify_due_date_msg`, `manager_reopen_ticket_only`, `show_close_confirm`) VALUES
(1, 'admin@intermesh.dev', 'Group-Office Customer Support', 1, 'https://localhost:63/modules/site/index.php?r=tickets/externalpage/ticket', '{SUBJECT}', 1, 'groupoffice.png', 'This is our support system. Please enter your contact information and describe your problem.', 'Thank you for contacting us. We have received your question and created a ticket for you. we will respond as soon as possible. For future reference, your question has been assigned the following ticket number: {TICKET_NUMBER}.', 0, 'en', 0, NULL, 0, 0, 1, 1, NULL, 0, 0, NULL, 1, '{AGENT} just picked up your ticket. We\'ll keep you up to date about our progress.', 1, 'Number: {NUMBER}\nSubject: {SUBJECT}\nCreated by: {CREATEDBY}\nCompany: {COMPANY}\n\n\nURL: {LINK}\n\n\n{MESSAGE}', 0, NULL, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ti_statuses`
--

CREATE TABLE `ti_statuses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_statuses`
--

INSERT INTO `ti_statuses` (`id`, `name`, `user_id`, `type_id`) VALUES
(1, 'In progress', 1, NULL),
(2, 'Not resolved', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ti_templates`
--

CREATE TABLE `ti_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `autoreply` tinyint(1) NOT NULL DEFAULT 0,
  `default_template` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_created_for_client` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_mail_for_agent` tinyint(1) NOT NULL DEFAULT 0,
  `ticket_claim_notification` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_templates`
--

INSERT INTO `ti_templates` (`id`, `name`, `content`, `user_id`, `autoreply`, `default_template`, `ticket_created_for_client`, `ticket_mail_for_agent`, `ticket_claim_notification`) VALUES
(1, 'Default response', 'Dear sir/madam\nThank you for your response,\n{MESSAGE}\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.', 1, 0, 1, 0, 0, 0),
(2, 'Default ticket created by client', 'Dear sir/madam\nWe have received your question and a ticket has been created.\nWe will respond as soon as possible.\nThe message you sent to us was:\n---------------------------------------------------------------------------\n{MESSAGE}\n---------------------------------------------------------------------------\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.', 1, 1, 0, 0, 0, 0),
(3, 'Default ticket created for client', 'Dear sir/madam\nWe have created a ticket for you.\nWe will respond as soon as possible.\nThe ticket is about:\n---------------------------------------------------------------------------\n{MESSAGE}\n---------------------------------------------------------------------------\nPlease do not reply to this email. You must go to the following page to reply:\n{LINK}\nBest regards,\n{NAME}.', 1, 0, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ti_tickets`
--

CREATE TABLE `ti_tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ticket_verifier` int(11) NOT NULL DEFAULT 0,
  `priority` int(11) NOT NULL DEFAULT 1,
  `status_id` int(11) NOT NULL DEFAULT 0,
  `type_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `agent_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) DEFAULT NULL,
  `company` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `company_id` int(11) DEFAULT NULL,
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
  `last_contact_response_time` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_tickets`
--

INSERT INTO `ti_tickets` (`id`, `ticket_number`, `ticket_verifier`, `priority`, `status_id`, `type_id`, `user_id`, `agent_id`, `contact_id`, `company`, `company_id`, `first_name`, `middle_name`, `last_name`, `email`, `phone`, `subject`, `ctime`, `mtime`, `muser_id`, `files_folder_id`, `unseen`, `group_id`, `order_id`, `last_response_time`, `cc_addresses`, `cuser_id`, `due_date`, `due_reminder_sent`, `last_agent_response_time`, `last_contact_response_time`) VALUES
(1, '201900001', 77510612, 1, -1, 1, 1, 0, 2, 'ACME Corporation', 13, 'Wile', 'E.', 'Coyote', 'wile@acme.demo', '', 'Malfunctioning rockets', 1561972064, 1561972065, 1, 0, 0, 0, 0, 1561972065, '', 1, NULL, 0, 1561972065, 1561972065),
(2, '201900002', 17512177, 1, 0, 1, 1, 0, 2, 'ACME Corporation', 13, 'Wile', 'E.', 'Coyote', 'wile@acme.demo', '', 'Can I speed up my rockets?', 1561799265, 1561972065, 1, 0, 1, 0, 0, 1561972065, '', 1, NULL, 0, 1561972065, 1561972065);

-- --------------------------------------------------------

--
-- Table structure for table `ti_tickets_custom_fields`
--

CREATE TABLE `ti_tickets_custom_fields` (
  `id` int(11) NOT NULL,
  `Custom` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_tickets_custom_fields`
--

INSERT INTO `ti_tickets_custom_fields` (`id`, `Custom`) VALUES
(1, ''),
(2, '');

-- --------------------------------------------------------

--
-- Table structure for table `ti_types`
--

CREATE TABLE `ti_types` (
  `id` int(11) NOT NULL,
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
  `email_on_new_msg` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ti_types`
--

INSERT INTO `ti_types` (`id`, `name`, `description`, `user_id`, `acl_id`, `show_statuses`, `show_from_others`, `files_folder_id`, `email_on_new`, `email_to_agent`, `custom_sender_field`, `sender_name`, `sender_email`, `publish_on_site`, `type_group_id`, `email_account_id`, `enable_templates`, `new_ticket`, `new_ticket_msg`, `assigned_to`, `assigned_to_msg`, `notify_agent`, `notify_agent_msg`, `search_cache_acl_id`, `email_on_new_msg`) VALUES
(1, 'IT', NULL, 1, 65, NULL, 0, 5, NULL, 0, 0, NULL, NULL, 1, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 71, NULL),
(2, 'Sales', NULL, 1, 66, NULL, 0, 6, NULL, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, NULL, 0, NULL, 0, NULL, 72, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ti_type_groups`
--

CREATE TABLE `ti_type_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_index` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abr_relation`
--
ALTER TABLE `abr_relation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `relationgroup` (`relationgroup_id`);

--
-- Indexes for table `abr_relationgroup`
--
ALTER TABLE `abr_relationgroup`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ab_addressbooks`
--
ALTER TABLE `ab_addressbooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ab_addresslists`
--
ALTER TABLE `ab_addresslists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ab_addresslist_companies`
--
ALTER TABLE `ab_addresslist_companies`
  ADD PRIMARY KEY (`addresslist_id`,`company_id`);

--
-- Indexes for table `ab_addresslist_contacts`
--
ALTER TABLE `ab_addresslist_contacts`
  ADD PRIMARY KEY (`addresslist_id`,`contact_id`);

--
-- Indexes for table `ab_addresslist_group`
--
ALTER TABLE `ab_addresslist_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ab_companies`
--
ALTER TABLE `ab_companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressbook_id` (`addressbook_id`),
  ADD KEY `addressbook_id_2` (`addressbook_id`),
  ADD KEY `link_id` (`link_id`),
  ADD KEY `link_id_2` (`link_id`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `ab_contacts`
--
ALTER TABLE `ab_contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressbook_id` (`addressbook_id`),
  ADD KEY `email` (`email`),
  ADD KEY `email2` (`email2`),
  ADD KEY `email3` (`email3`),
  ADD KEY `last_name` (`last_name`),
  ADD KEY `go_user_id` (`go_user_id`),
  ADD KEY `uuid` (`uuid`);

--
-- Indexes for table `ab_contacts_vcard_props`
--
ALTER TABLE `ab_contacts_vcard_props`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indexes for table `ab_portlet_birthdays`
--
ALTER TABLE `ab_portlet_birthdays`
  ADD PRIMARY KEY (`user_id`,`addressbook_id`);

--
-- Indexes for table `ab_search_queries`
--
ALTER TABLE `ab_search_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companies` (`companies`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ab_sent_mailings`
--
ALTER TABLE `ab_sent_mailings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ab_sent_mailing_companies`
--
ALTER TABLE `ab_sent_mailing_companies`
  ADD PRIMARY KEY (`sent_mailing_id`,`company_id`);

--
-- Indexes for table `ab_sent_mailing_contacts`
--
ALTER TABLE `ab_sent_mailing_contacts`
  ADD PRIMARY KEY (`sent_mailing_id`,`contact_id`);

--
-- Indexes for table `ab_settings`
--
ALTER TABLE `ab_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `addressbook_address`
--
ALTER TABLE `addressbook_address`
  ADD KEY `contactId` (`contactId`);

--
-- Indexes for table `addressbook_addressbook`
--
ALTER TABLE `addressbook_addressbook`
  ADD PRIMARY KEY (`id`),
  ADD KEY `acid` (`aclId`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `addressbook_contact`
--
ALTER TABLE `addressbook_contact`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goUserId` (`goUserId`),
  ADD KEY `owner` (`createdBy`),
  ADD KEY `photoBlobId` (`photoBlobId`),
  ADD KEY `addressBookId` (`addressBookId`),
  ADD KEY `modifiedBy` (`modifiedBy`),
  ADD KEY `vcardBlobId` (`vcardBlobId`),
  ADD KEY `isOrganization` (`isOrganization`);

--
-- Indexes for table `addressbook_contact_custom_fields`
--
ALTER TABLE `addressbook_contact_custom_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_6` (`User_1`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_10` (`Select_1`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_11` (`Treeselect_1`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_21` (`User`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_25` (`Select`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_26` (`Treeselect`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_1` (`Company_1`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_2` (`Contact_1`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_16` (`Company`),
  ADD KEY `addressbook_contact_custom_fields_ibfk_go_17` (`Contact`);

--
-- Indexes for table `addressbook_contact_group`
--
ALTER TABLE `addressbook_contact_group`
  ADD PRIMARY KEY (`contactId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

--
-- Indexes for table `addressbook_contact_star`
--
ALTER TABLE `addressbook_contact_star`
  ADD PRIMARY KEY (`contactId`,`userId`),
  ADD KEY `addressbook_contact_star_ibfk_2` (`userId`);

--
-- Indexes for table `addressbook_date`
--
ALTER TABLE `addressbook_date`
  ADD KEY `contactId` (`contactId`);

--
-- Indexes for table `addressbook_email_address`
--
ALTER TABLE `addressbook_email_address`
  ADD KEY `contactId` (`contactId`);

--
-- Indexes for table `addressbook_group`
--
ALTER TABLE `addressbook_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addressBookId` (`addressBookId`);

--
-- Indexes for table `addressbook_phone_number`
--
ALTER TABLE `addressbook_phone_number`
  ADD KEY `contactId` (`contactId`);

--
-- Indexes for table `addressbook_url`
--
ALTER TABLE `addressbook_url`
  ADD KEY `contactId` (`contactId`);

--
-- Indexes for table `addressbook_user_settings`
--
ALTER TABLE `addressbook_user_settings`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `defaultAddressBookId` (`defaultAddressBookId`);

--
-- Indexes for table `bm_bookmarks`
--
ALTER TABLE `bm_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `bm_categories`
--
ALTER TABLE `bm_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `show_in_startmenu` (`show_in_startmenu`);

--
-- Indexes for table `bookmarks_bookmark`
--
ALTER TABLE `bookmarks_bookmark`
  ADD PRIMARY KEY (`id`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `categoryId` (`categoryId`);

--
-- Indexes for table `bookmarks_category`
--
ALTER TABLE `bookmarks_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `createdBy` (`createdBy`);

--
-- Indexes for table `bs_batchjobs`
--
ALTER TABLE `bs_batchjobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `bs_batchjob_orders`
--
ALTER TABLE `bs_batchjob_orders`
  ADD PRIMARY KEY (`batchjob_id`,`order_id`);

--
-- Indexes for table `bs_books`
--
ALTER TABLE `bs_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_category_languages`
--
ALTER TABLE `bs_category_languages`
  ADD PRIMARY KEY (`language_id`,`category_id`);

--
-- Indexes for table `bs_cost_codes`
--
ALTER TABLE `bs_cost_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `bs_doc_templates`
--
ALTER TABLE `bs_doc_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_expenses`
--
ALTER TABLE `bs_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`expense_book_id`,`category_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `bs_expense_books`
--
ALTER TABLE `bs_expense_books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_expense_categories`
--
ALTER TABLE `bs_expense_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`expense_book_id`);

--
-- Indexes for table `bs_items`
--
ALTER TABLE `bs_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `bs_item_product_option`
--
ALTER TABLE `bs_item_product_option`
  ADD PRIMARY KEY (`item_id`,`product_option_value_id`);

--
-- Indexes for table `bs_languages`
--
ALTER TABLE `bs_languages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `language` (`language`);

--
-- Indexes for table `bs_numbers`
--
ALTER TABLE `bs_numbers`
  ADD PRIMARY KEY (`book_id`,`type`);

--
-- Indexes for table `bs_orders`
--
ALTER TABLE `bs_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `recurred_order_id` (`recurred_order_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indexes for table `bs_orders_custom_fields`
--
ALTER TABLE `bs_orders_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_order_item_groups`
--
ALTER TABLE `bs_order_item_groups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `bs_order_payments`
--
ALTER TABLE `bs_order_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_order_statuses`
--
ALTER TABLE `bs_order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `bs_order_status_history`
--
ALTER TABLE `bs_order_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`,`status_id`);

--
-- Indexes for table `bs_products`
--
ALTER TABLE `bs_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Indexes for table `bs_products_custom_fields`
--
ALTER TABLE `bs_products_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_product_categories`
--
ALTER TABLE `bs_product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `bs_product_languages`
--
ALTER TABLE `bs_product_languages`
  ADD PRIMARY KEY (`language_id`,`product_id`);

--
-- Indexes for table `bs_product_option`
--
ALTER TABLE `bs_product_option`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_product_option_language`
--
ALTER TABLE `bs_product_option_language`
  ADD PRIMARY KEY (`product_option_id`,`language_id`);

--
-- Indexes for table `bs_product_option_value`
--
ALTER TABLE `bs_product_option_value`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_product_option_value_language`
--
ALTER TABLE `bs_product_option_value_language`
  ADD PRIMARY KEY (`product_option_value_id`,`language_id`);

--
-- Indexes for table `bs_status_languages`
--
ALTER TABLE `bs_status_languages`
  ADD PRIMARY KEY (`language_id`,`status_id`);

--
-- Indexes for table `bs_tax_rates`
--
ALTER TABLE `bs_tax_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bs_templates`
--
ALTER TABLE `bs_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `bs_tracking_codes`
--
ALTER TABLE `bs_tracking_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cal_calendars`
--
ALTER TABLE `cal_calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `group_id` (`group_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cal_calendars_custom_fields`
--
ALTER TABLE `cal_calendars_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cal_calendar_user_colors`
--
ALTER TABLE `cal_calendar_user_colors`
  ADD PRIMARY KEY (`user_id`,`calendar_id`);

--
-- Indexes for table `cal_categories`
--
ALTER TABLE `cal_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `calendar_id` (`calendar_id`);

--
-- Indexes for table `cal_events`
--
ALTER TABLE `cal_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `start_time` (`start_time`),
  ADD KEY `end_time` (`end_time`),
  ADD KEY `repeat_end_time` (`repeat_end_time`),
  ADD KEY `rrule` (`rrule`),
  ADD KEY `calendar_id` (`calendar_id`),
  ADD KEY `busy` (`busy`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `uuid` (`uuid`),
  ADD KEY `resource_event_id` (`resource_event_id`),
  ADD KEY `recurrence_id` (`recurrence_id`),
  ADD KEY `exception_for_event_id` (`exception_for_event_id`);

--
-- Indexes for table `cal_events_custom_fields`
--
ALTER TABLE `cal_events_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cal_events_declined`
--
ALTER TABLE `cal_events_declined`
  ADD PRIMARY KEY (`uid`,`email`);

--
-- Indexes for table `cal_exceptions`
--
ALTER TABLE `cal_exceptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `cal_groups`
--
ALTER TABLE `cal_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cal_group_admins`
--
ALTER TABLE `cal_group_admins`
  ADD PRIMARY KEY (`group_id`,`user_id`);

--
-- Indexes for table `cal_participants`
--
ALTER TABLE `cal_participants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`,`user_id`);

--
-- Indexes for table `cal_settings`
--
ALTER TABLE `cal_settings`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `calendar_id` (`calendar_id`);

--
-- Indexes for table `cal_views`
--
ALTER TABLE `cal_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cal_views_calendars`
--
ALTER TABLE `cal_views_calendars`
  ADD PRIMARY KEY (`view_id`,`calendar_id`);

--
-- Indexes for table `cal_views_groups`
--
ALTER TABLE `cal_views_groups`
  ADD PRIMARY KEY (`view_id`,`group_id`);

--
-- Indexes for table `cal_visible_tasklists`
--
ALTER TABLE `cal_visible_tasklists`
  ADD PRIMARY KEY (`calendar_id`,`tasklist_id`);

--
-- Indexes for table `cf_ab_companies`
--
ALTER TABLE `cf_ab_companies`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `cf_ab_contacts`
--
ALTER TABLE `cf_ab_contacts`
  ADD PRIMARY KEY (`model_id`);

--
-- Indexes for table `cf_blocks`
--
ALTER TABLE `cf_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `cf_categories`
--
ALTER TABLE `cf_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`extends_model`);

--
-- Indexes for table `cf_disable_categories`
--
ALTER TABLE `cf_disable_categories`
  ADD PRIMARY KEY (`model_id`,`model_name`);

--
-- Indexes for table `cf_enabled_blocks`
--
ALTER TABLE `cf_enabled_blocks`
  ADD PRIMARY KEY (`block_id`,`model_id`,`model_type_name`);

--
-- Indexes for table `cf_enabled_categories`
--
ALTER TABLE `cf_enabled_categories`
  ADD PRIMARY KEY (`model_id`,`model_name`,`category_id`);

--
-- Indexes for table `cf_fields`
--
ALTER TABLE `cf_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`category_id`);

--
-- Indexes for table `cf_select_tree_options`
--
ALTER TABLE `cf_select_tree_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`,`field_id`);

--
-- Indexes for table `cf_tree_select_options`
--
ALTER TABLE `cf_tree_select_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`,`field_id`);

--
-- Indexes for table `comments_attachment`
--
ALTER TABLE `comments_attachment`
  ADD PRIMARY KEY (`commentId`,`blobId`),
  ADD KEY `fk_comments_attachment_comments_comment1_idx` (`commentId`),
  ADD KEY `fk_comments_attachment_core_blob1_idx` (`blobId`);

--
-- Indexes for table `comments_comment`
--
ALTER TABLE `comments_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comments_comment_core_entity_type_idx` (`entityId`),
  ADD KEY `fk_comments_comment_core_user1_idx` (`createdBy`),
  ADD KEY `fk_comments_comment_core_user2_idx` (`modifiedBy`),
  ADD KEY `section` (`section`);

--
-- Indexes for table `comments_comment_image`
--
ALTER TABLE `comments_comment_image`
  ADD PRIMARY KEY (`commentId`,`blobId`),
  ADD KEY `blobId` (`blobId`);

--
-- Indexes for table `comments_comment_label`
--
ALTER TABLE `comments_comment_label`
  ADD PRIMARY KEY (`labelId`,`commentId`),
  ADD KEY `fk_comments_label_has_comments_comment_comments_comment1_idx` (`commentId`),
  ADD KEY `fk_comments_label_has_comments_comment_comments_label1_idx` (`labelId`);

--
-- Indexes for table `comments_label`
--
ALTER TABLE `comments_label`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `core_acl`
--
ALTER TABLE `core_acl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `core_acl_ibfk_1` (`entityTypeId`),
  ADD KEY `ownedBy` (`ownedBy`);

--
-- Indexes for table `core_acl_group`
--
ALTER TABLE `core_acl_group`
  ADD PRIMARY KEY (`aclId`,`groupId`),
  ADD KEY `level` (`level`),
  ADD KEY `groupId` (`groupId`);

--
-- Indexes for table `core_acl_group_changes`
--
ALTER TABLE `core_acl_group_changes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclId` (`aclId`,`groupId`),
  ADD KEY `group` (`groupId`);

--
-- Indexes for table `core_auth_allow_group`
--
ALTER TABLE `core_auth_allow_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupId` (`groupId`);

--
-- Indexes for table `core_auth_method`
--
ALTER TABLE `core_auth_method`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moduleId_sortOrder` (`moduleId`,`sortOrder`),
  ADD KEY `moduleId` (`moduleId`);

--
-- Indexes for table `core_auth_password`
--
ALTER TABLE `core_auth_password`
  ADD PRIMARY KEY (`userId`);

--
-- Indexes for table `core_auth_token`
--
ALTER TABLE `core_auth_token`
  ADD PRIMARY KEY (`loginToken`),
  ADD KEY `userId` (`userId`),
  ADD KEY `accessToken` (`accessToken`);

--
-- Indexes for table `core_blob`
--
ALTER TABLE `core_blob`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staleAt` (`staleAt`);

--
-- Indexes for table `core_change`
--
ALTER TABLE `core_change`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `entityTypeId` (`entityTypeId`),
  ADD KEY `entityId` (`entityId`);

--
-- Indexes for table `core_change_user`
--
ALTER TABLE `core_change_user`
  ADD PRIMARY KEY (`userId`,`entityId`,`entityTypeId`),
  ADD KEY `entityTypeId` (`entityTypeId`);

--
-- Indexes for table `core_change_user_modseq`
--
ALTER TABLE `core_change_user_modseq`
  ADD PRIMARY KEY (`userId`,`entityTypeId`),
  ADD KEY `entityTypeId` (`entityTypeId`);

--
-- Indexes for table `core_cron_job`
--
ALTER TABLE `core_cron_job`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`),
  ADD KEY `moduleId` (`moduleId`);

--
-- Indexes for table `core_customfields_field`
--
ALTER TABLE `core_customfields_field`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`fieldSetId`),
  ADD KEY `modSeq` (`modSeq`);

--
-- Indexes for table `core_customfields_field_set`
--
ALTER TABLE `core_customfields_field_set`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entityId` (`entityId`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `modSeq` (`modSeq`);

--
-- Indexes for table `core_customfields_multiselect_43`
--
ALTER TABLE `core_customfields_multiselect_43`
  ADD PRIMARY KEY (`id`,`optionId`),
  ADD KEY `optionId` (`optionId`);

--
-- Indexes for table `core_customfields_select_option`
--
ALTER TABLE `core_customfields_select_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `field_id` (`fieldId`),
  ADD KEY `parentId` (`parentId`);

--
-- Indexes for table `core_email_template`
--
ALTER TABLE `core_email_template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `aclId` (`aclId`),
  ADD KEY `moduleId` (`moduleId`);

--
-- Indexes for table `core_email_template_attachment`
--
ALTER TABLE `core_email_template_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `templateId` (`emailTemplateId`),
  ADD KEY `blobId` (`blobId`);

--
-- Indexes for table `core_entity`
--
ALTER TABLE `core_entity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clientName` (`clientName`),
  ADD UNIQUE KEY `name` (`name`,`moduleId`) USING BTREE,
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `defaultAclId` (`defaultAclId`);

--
-- Indexes for table `core_entity_filter`
--
ALTER TABLE `core_entity_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclid` (`aclId`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `entityTypeId` (`entityTypeId`);

--
-- Indexes for table `core_group`
--
ALTER TABLE `core_group`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `isUserGroupFor` (`isUserGroupFor`),
  ADD KEY `aclId` (`aclId`);

--
-- Indexes for table `core_group_default_group`
--
ALTER TABLE `core_group_default_group`
  ADD PRIMARY KEY (`groupId`);

--
-- Indexes for table `core_link`
--
ALTER TABLE `core_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fromEntityId` (`fromEntityTypeId`,`fromId`,`toEntityTypeId`,`toId`) USING BTREE,
  ADD KEY `toEntity` (`toEntityTypeId`),
  ADD KEY `fromEntityTypeId` (`fromEntityTypeId`),
  ADD KEY `fromId` (`fromId`),
  ADD KEY `toEntityTypeId` (`toEntityTypeId`),
  ADD KEY `toId` (`toId`);

--
-- Indexes for table `core_module`
--
ALTER TABLE `core_module`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `aclId` (`aclId`);

--
-- Indexes for table `core_oauth_access_token`
--
ALTER TABLE `core_oauth_access_token`
  ADD PRIMARY KEY (`identifier`),
  ADD KEY `userIdentifier` (`userIdentifier`),
  ADD KEY `clientId` (`clientId`);

--
-- Indexes for table `core_oauth_client`
--
ALTER TABLE `core_oauth_client`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `core_search`
--
ALTER TABLE `core_search`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `entityId` (`entityId`,`entityTypeId`),
  ADD KEY `acl_id` (`aclId`),
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `entityTypeId` (`entityTypeId`),
  ADD KEY `filter` (`filter`),
  ADD KEY `keywords` (`keywords`);

--
-- Indexes for table `core_setting`
--
ALTER TABLE `core_setting`
  ADD PRIMARY KEY (`moduleId`,`name`);

--
-- Indexes for table `core_smtp_account`
--
ALTER TABLE `core_smtp_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `moduleId` (`moduleId`),
  ADD KEY `aclId` (`aclId`);

--
-- Indexes for table `core_user`
--
ALTER TABLE `core_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `fk_user_avatar_id_idx` (`avatarId`);

--
-- Indexes for table `core_user_custom_fields`
--
ALTER TABLE `core_user_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `core_user_default_group`
--
ALTER TABLE `core_user_default_group`
  ADD PRIMARY KEY (`groupId`);

--
-- Indexes for table `core_user_group`
--
ALTER TABLE `core_user_group`
  ADD PRIMARY KEY (`groupId`,`userId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `email_default_email_account_templates`
--
ALTER TABLE `email_default_email_account_templates`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `email_default_email_templates`
--
ALTER TABLE `email_default_email_templates`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `emp_folders`
--
ALTER TABLE `emp_folders`
  ADD PRIMARY KEY (`folder_id`,`user_id`);

--
-- Indexes for table `em_accounts`
--
ALTER TABLE `em_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `em_accounts_collapsed`
--
ALTER TABLE `em_accounts_collapsed`
  ADD PRIMARY KEY (`account_id`,`user_id`);

--
-- Indexes for table `em_accounts_sort`
--
ALTER TABLE `em_accounts_sort`
  ADD PRIMARY KEY (`account_id`,`user_id`);

--
-- Indexes for table `em_aliases`
--
ALTER TABLE `em_aliases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `em_contacts_last_mail_times`
--
ALTER TABLE `em_contacts_last_mail_times`
  ADD PRIMARY KEY (`contact_id`,`user_id`),
  ADD KEY `last_mail_time` (`last_mail_time`);

--
-- Indexes for table `em_filters`
--
ALTER TABLE `em_filters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `em_folders`
--
ALTER TABLE `em_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `em_folders_expanded`
--
ALTER TABLE `em_folders_expanded`
  ADD PRIMARY KEY (`folder_id`,`user_id`);

--
-- Indexes for table `em_labels`
--
ALTER TABLE `em_labels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `em_links`
--
ALTER TABLE `em_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`user_id`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `em_messages_cache`
--
ALTER TABLE `em_messages_cache`
  ADD PRIMARY KEY (`folder_id`,`uid`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `em_portlet_folders`
--
ALTER TABLE `em_portlet_folders`
  ADD PRIMARY KEY (`account_id`,`folder_name`,`user_id`);

--
-- Indexes for table `fs_bookmarks`
--
ALTER TABLE `fs_bookmarks`
  ADD PRIMARY KEY (`folder_id`,`user_id`);

--
-- Indexes for table `fs_filehandlers`
--
ALTER TABLE `fs_filehandlers`
  ADD PRIMARY KEY (`user_id`,`extension`);

--
-- Indexes for table `fs_files`
--
ALTER TABLE `fs_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_id_2` (`folder_id`,`name`),
  ADD KEY `folder_id` (`folder_id`),
  ADD KEY `name` (`name`),
  ADD KEY `extension` (`extension`),
  ADD KEY `mtime` (`mtime`),
  ADD KEY `expire_time` (`expire_time`);

--
-- Indexes for table `fs_files_custom_fields`
--
ALTER TABLE `fs_files_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_folders`
--
ALTER TABLE `fs_folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_id_3` (`parent_id`,`name`),
  ADD KEY `name` (`name`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `visible` (`visible`);

--
-- Indexes for table `fs_folders_custom_fields`
--
ALTER TABLE `fs_folders_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_folder_pref`
--
ALTER TABLE `fs_folder_pref`
  ADD PRIMARY KEY (`folder_id`,`user_id`);

--
-- Indexes for table `fs_new_files`
--
ALTER TABLE `fs_new_files`
  ADD KEY `file_id` (`file_id`,`user_id`);

--
-- Indexes for table `fs_notifications`
--
ALTER TABLE `fs_notifications`
  ADD PRIMARY KEY (`folder_id`,`user_id`);

--
-- Indexes for table `fs_notification_messages`
--
ALTER TABLE `fs_notification_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`,`status`);

--
-- Indexes for table `fs_shared_cache`
--
ALTER TABLE `fs_shared_cache`
  ADD PRIMARY KEY (`user_id`,`folder_id`);

--
-- Indexes for table `fs_shared_root_folders`
--
ALTER TABLE `fs_shared_root_folders`
  ADD PRIMARY KEY (`user_id`,`folder_id`);

--
-- Indexes for table `fs_statuses`
--
ALTER TABLE `fs_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fs_status_history`
--
ALTER TABLE `fs_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `link_id` (`link_id`);

--
-- Indexes for table `fs_templates`
--
ALTER TABLE `fs_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fs_versions`
--
ALTER TABLE `fs_versions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `googleauth_secret`
--
ALTER TABLE `googleauth_secret`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `user` (`userId`);

--
-- Indexes for table `go_address_format`
--
ALTER TABLE `go_address_format`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_advanced_searches`
--
ALTER TABLE `go_advanced_searches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_cache`
--
ALTER TABLE `go_cache`
  ADD PRIMARY KEY (`user_id`,`key`),
  ADD KEY `mtime` (`mtime`);

--
-- Indexes for table `go_cf_setting_tabs`
--
ALTER TABLE `go_cf_setting_tabs`
  ADD PRIMARY KEY (`cf_category_id`);

--
-- Indexes for table `go_clients`
--
ALTER TABLE `go_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_footprint` (`footprint`);

--
-- Indexes for table `go_countries`
--
ALTER TABLE `go_countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_cron`
--
ALTER TABLE `go_cron`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nextrun_active` (`nextrun`,`active`);

--
-- Indexes for table `go_cron_groups`
--
ALTER TABLE `go_cron_groups`
  ADD PRIMARY KEY (`cronjob_id`,`group_id`);

--
-- Indexes for table `go_cron_users`
--
ALTER TABLE `go_cron_users`
  ADD PRIMARY KEY (`cronjob_id`,`user_id`);

--
-- Indexes for table `go_holidays`
--
ALTER TABLE `go_holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `region` (`region`);

--
-- Indexes for table `go_links_ab_addresslists`
--
ALTER TABLE `go_links_ab_addresslists`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_ab_companies`
--
ALTER TABLE `go_links_ab_companies`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_ab_contacts`
--
ALTER TABLE `go_links_ab_contacts`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_bs_orders`
--
ALTER TABLE `go_links_bs_orders`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_cal_events`
--
ALTER TABLE `go_links_cal_events`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_em_links`
--
ALTER TABLE `go_links_em_links`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_fs_files`
--
ALTER TABLE `go_links_fs_files`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_fs_folders`
--
ALTER TABLE `go_links_fs_folders`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_pr2_projects`
--
ALTER TABLE `go_links_pr2_projects`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_ta_tasks`
--
ALTER TABLE `go_links_ta_tasks`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_links_ti_tickets`
--
ALTER TABLE `go_links_ti_tickets`
  ADD PRIMARY KEY (`id`,`model_id`,`model_type_id`),
  ADD KEY `id` (`id`,`folder_id`),
  ADD KEY `ctime` (`ctime`);

--
-- Indexes for table `go_link_descriptions`
--
ALTER TABLE `go_link_descriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_link_folders`
--
ALTER TABLE `go_link_folders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `link_id` (`model_id`,`model_type_id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `go_log`
--
ALTER TABLE `go_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_reminders`
--
ALTER TABLE `go_reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `go_reminders_users`
--
ALTER TABLE `go_reminders_users`
  ADD PRIMARY KEY (`reminder_id`,`user_id`),
  ADD KEY `user_id_time` (`user_id`,`time`);

--
-- Indexes for table `go_saved_exports`
--
ALTER TABLE `go_saved_exports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_saved_search_queries`
--
ALTER TABLE `go_saved_search_queries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `go_search_sync`
--
ALTER TABLE `go_search_sync`
  ADD PRIMARY KEY (`user_id`,`module`);

--
-- Indexes for table `go_settings`
--
ALTER TABLE `go_settings`
  ADD PRIMARY KEY (`user_id`,`name`);

--
-- Indexes for table `go_state`
--
ALTER TABLE `go_state`
  ADD PRIMARY KEY (`user_id`,`name`);

--
-- Indexes for table `go_templates`
--
ALTER TABLE `go_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `go_working_weeks`
--
ALTER TABLE `go_working_weeks`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `imapauth_server`
--
ALTER TABLE `imapauth_server`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serverId` (`serverId`);

--
-- Indexes for table `imapauth_server_group`
--
ALTER TABLE `imapauth_server_group`
  ADD PRIMARY KEY (`serverId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

--
-- Indexes for table `ld_credits`
--
ALTER TABLE `ld_credits`
  ADD PRIMARY KEY (`ld_year_credit_id`,`ld_credit_type_id`);

--
-- Indexes for table `ld_credit_types`
--
ALTER TABLE `ld_credit_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ld_leave_days`
--
ALTER TABLE `ld_leave_days`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ld_year_credits`
--
ALTER TABLE `ld_year_credits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes_note`
--
ALTER TABLE `notes_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`createdBy`),
  ADD KEY `category_id` (`noteBookId`);

--
-- Indexes for table `notes_note_book`
--
ALTER TABLE `notes_note_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclId` (`aclId`);

--
-- Indexes for table `notes_note_custom_fields`
--
ALTER TABLE `notes_note_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes_note_image`
--
ALTER TABLE `notes_note_image`
  ADD PRIMARY KEY (`noteId`,`blobId`),
  ADD KEY `blobId` (`blobId`);

--
-- Indexes for table `notes_user_settings`
--
ALTER TABLE `notes_user_settings`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `defaultNoteBookId` (`defaultNoteBookId`);

--
-- Indexes for table `pa_aliases`
--
ALTER TABLE `pa_aliases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `address` (`address`),
  ADD KEY `domain_id` (`domain_id`);

--
-- Indexes for table `pa_domains`
--
ALTER TABLE `pa_domains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`);

--
-- Indexes for table `pa_mailboxes`
--
ALTER TABLE `pa_mailboxes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `username` (`username`),
  ADD KEY `go_installation_id` (`go_installation_id`);

--
-- Indexes for table `pr2_default_resources`
--
ALTER TABLE `pr2_default_resources`
  ADD PRIMARY KEY (`template_id`,`user_id`),
  ADD KEY `fk_pm_user_fees_pm_templates1_idx` (`template_id`);

--
-- Indexes for table `pr2_employees`
--
ALTER TABLE `pr2_employees`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `pr2_employee_activity_rate`
--
ALTER TABLE `pr2_employee_activity_rate`
  ADD PRIMARY KEY (`activity_id`,`employee_id`),
  ADD KEY `fk_pr2_employee_activity_idx` (`employee_id`);

--
-- Indexes for table `pr2_expenses`
--
ALTER TABLE `pr2_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `fk_pr2_expenses_pr2_expense_budgets1_idx` (`expense_budget_id`),
  ADD KEY `fk_pr2_expenses_pr2_projects1_idx` (`project_id`);

--
-- Indexes for table `pr2_expense_budgets`
--
ALTER TABLE `pr2_expense_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contact_id` (`contact_id`);

--
-- Indexes for table `pr2_hours`
--
ALTER TABLE `pr2_hours`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `income_id` (`income_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_pr2_hours_pr2_projects1_idx` (`project_id`),
  ADD KEY `fk_pr2_hours_pr2_standard_tasks1_idx` (`standard_task_id`);

--
-- Indexes for table `pr2_hours_custom_fields`
--
ALTER TABLE `pr2_hours_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr2_income`
--
ALTER TABLE `pr2_income`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr2_income_items`
--
ALTER TABLE `pr2_income_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr2_portlet_statuses`
--
ALTER TABLE `pr2_portlet_statuses`
  ADD PRIMARY KEY (`user_id`,`status_id`);

--
-- Indexes for table `pr2_projects`
--
ALTER TABLE `pr2_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `responsible_user_id` (`responsible_user_id`),
  ADD KEY `fk_pr2_projects_pr2_statuses1_idx` (`status_id`),
  ADD KEY `fk_pr2_projects_pr2_types1_idx` (`type_id`),
  ADD KEY `fk_pr2_projects_pr2_templates1_idx` (`template_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `pr2_projects_custom_fields`
--
ALTER TABLE `pr2_projects_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr2_resources`
--
ALTER TABLE `pr2_resources`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `fk_pm_user_fees_pm_projects1_idx` (`project_id`);

--
-- Indexes for table `pr2_resource_activity_rate`
--
ALTER TABLE `pr2_resource_activity_rate`
  ADD PRIMARY KEY (`activity_id`,`employee_id`,`project_id`),
  ADD KEY `fk_pr2_resource_activity_idx` (`project_id`,`employee_id`);

--
-- Indexes for table `pr2_standard_tasks`
--
ALTER TABLE `pr2_standard_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pr2_statuses`
--
ALTER TABLE `pr2_statuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sort_order` (`sort_order`);

--
-- Indexes for table `pr2_tasks`
--
ALTER TABLE `pr2_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `pr2_templates`
--
ALTER TABLE `pr2_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pr2_templates_pr2_types1_idx` (`default_type_id`),
  ADD KEY `fk_pr2_templates_pr2_statuses1_idx` (`default_status_id`);

--
-- Indexes for table `pr2_templates_events`
--
ALTER TABLE `pr2_templates_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pr2_templates_events_pr2_templates1_idx` (`template_id`);

--
-- Indexes for table `pr2_timers`
--
ALTER TABLE `pr2_timers`
  ADD PRIMARY KEY (`project_id`,`user_id`),
  ADD KEY `project_id` (`user_id`,`starttime`);

--
-- Indexes for table `pr2_types`
--
ALTER TABLE `pr2_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_content`
--
ALTER TABLE `site_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`,`site_id`);

--
-- Indexes for table `site_content_custom_fields`
--
ALTER TABLE `site_content_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_menu`
--
ALTER TABLE `site_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_menu_item`
--
ALTER TABLE `site_menu_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_multifile_files`
--
ALTER TABLE `site_multifile_files`
  ADD PRIMARY KEY (`model_id`,`field_id`,`file_id`);

--
-- Indexes for table `site_sites`
--
ALTER TABLE `site_sites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `domain` (`domain`);

--
-- Indexes for table `site_sites_custom_fields`
--
ALTER TABLE `site_sites_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `su_announcements`
--
ALTER TABLE `su_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `due_time` (`due_time`);

--
-- Indexes for table `su_latest_read_announcement_records`
--
ALTER TABLE `su_latest_read_announcement_records`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `su_notes`
--
ALTER TABLE `su_notes`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `su_rss_feeds`
--
ALTER TABLE `su_rss_feeds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `su_visible_calendars`
--
ALTER TABLE `su_visible_calendars`
  ADD PRIMARY KEY (`user_id`,`calendar_id`);

--
-- Indexes for table `su_visible_lists`
--
ALTER TABLE `su_visible_lists`
  ADD PRIMARY KEY (`user_id`,`tasklist_id`);

--
-- Indexes for table `sync_addressbook_user`
--
ALTER TABLE `sync_addressbook_user`
  ADD PRIMARY KEY (`addressBookId`,`userId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `sync_calendar_user`
--
ALTER TABLE `sync_calendar_user`
  ADD PRIMARY KEY (`calendar_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sync_devices`
--
ALTER TABLE `sync_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sync_note_categories_user`
--
ALTER TABLE `sync_note_categories_user`
  ADD PRIMARY KEY (`category_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sync_settings`
--
ALTER TABLE `sync_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `sync_tasklist_user`
--
ALTER TABLE `sync_tasklist_user`
  ADD PRIMARY KEY (`tasklist_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sync_user_note_book`
--
ALTER TABLE `sync_user_note_book`
  ADD PRIMARY KEY (`noteBookId`,`userId`),
  ADD KEY `user` (`userId`);

--
-- Indexes for table `ta_categories`
--
ALTER TABLE `ta_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ta_portlet_tasklists`
--
ALTER TABLE `ta_portlet_tasklists`
  ADD PRIMARY KEY (`user_id`,`tasklist_id`);

--
-- Indexes for table `ta_settings`
--
ALTER TABLE `ta_settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `ta_tasklists`
--
ALTER TABLE `ta_tasklists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ta_tasks`
--
ALTER TABLE `ta_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`tasklist_id`),
  ADD KEY `rrule` (`rrule`),
  ADD KEY `uuid` (`uuid`);

--
-- Indexes for table `ta_tasks_custom_fields`
--
ALTER TABLE `ta_tasks_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_a`
--
ALTER TABLE `test_a`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  ADD PRIMARY KEY (`id`,`aId`),
  ADD KEY `aId` (`aId`);

--
-- Indexes for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  ADD PRIMARY KEY (`id`,`aId`),
  ADD KEY `aId` (`aId`);

--
-- Indexes for table `test_a_map`
--
ALTER TABLE `test_a_map`
  ADD PRIMARY KEY (`aId`,`anotherAId`),
  ADD KEY `anotherAId` (`anotherAId`);

--
-- Indexes for table `test_b`
--
ALTER TABLE `test_b`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cId` (`cId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `test_c`
--
ALTER TABLE `test_c`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `test_d`
--
ALTER TABLE `test_d`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_groups`
--
ALTER TABLE `ti_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_messages`
--
ALTER TABLE `ti_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_rates`
--
ALTER TABLE `ti_rates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_settings`
--
ALTER TABLE `ti_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_statuses`
--
ALTER TABLE `ti_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_templates`
--
ALTER TABLE `ti_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_tickets`
--
ALTER TABLE `ti_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `unseen_type_id_agent_id` (`unseen`,`type_id`,`agent_id`);

--
-- Indexes for table `ti_tickets_custom_fields`
--
ALTER TABLE `ti_tickets_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ti_types`
--
ALTER TABLE `ti_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `ti_type_groups`
--
ALTER TABLE `ti_type_groups`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abr_relation`
--
ALTER TABLE `abr_relation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=234;

--
-- AUTO_INCREMENT for table `abr_relationgroup`
--
ALTER TABLE `abr_relationgroup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ab_addressbooks`
--
ALTER TABLE `ab_addressbooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ab_addresslists`
--
ALTER TABLE `ab_addresslists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ab_addresslist_group`
--
ALTER TABLE `ab_addresslist_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ab_companies`
--
ALTER TABLE `ab_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ab_contacts`
--
ALTER TABLE `ab_contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `ab_contacts_vcard_props`
--
ALTER TABLE `ab_contacts_vcard_props`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ab_search_queries`
--
ALTER TABLE `ab_search_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ab_sent_mailings`
--
ALTER TABLE `ab_sent_mailings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `addressbook_addressbook`
--
ALTER TABLE `addressbook_addressbook`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `addressbook_contact`
--
ALTER TABLE `addressbook_contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `addressbook_group`
--
ALTER TABLE `addressbook_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_bookmarks`
--
ALTER TABLE `bm_bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bm_categories`
--
ALTER TABLE `bm_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bookmarks_bookmark`
--
ALTER TABLE `bookmarks_bookmark`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookmarks_category`
--
ALTER TABLE `bookmarks_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bs_books`
--
ALTER TABLE `bs_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bs_cost_codes`
--
ALTER TABLE `bs_cost_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_doc_templates`
--
ALTER TABLE `bs_doc_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bs_expenses`
--
ALTER TABLE `bs_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bs_expense_books`
--
ALTER TABLE `bs_expense_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bs_expense_categories`
--
ALTER TABLE `bs_expense_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bs_items`
--
ALTER TABLE `bs_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `bs_languages`
--
ALTER TABLE `bs_languages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bs_orders`
--
ALTER TABLE `bs_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bs_order_item_groups`
--
ALTER TABLE `bs_order_item_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_order_payments`
--
ALTER TABLE `bs_order_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bs_order_statuses`
--
ALTER TABLE `bs_order_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `bs_order_status_history`
--
ALTER TABLE `bs_order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bs_products`
--
ALTER TABLE `bs_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bs_product_categories`
--
ALTER TABLE `bs_product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_product_option`
--
ALTER TABLE `bs_product_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_product_option_value`
--
ALTER TABLE `bs_product_option_value`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_tax_rates`
--
ALTER TABLE `bs_tax_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bs_templates`
--
ALTER TABLE `bs_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bs_tracking_codes`
--
ALTER TABLE `bs_tracking_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cal_calendars`
--
ALTER TABLE `cal_calendars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cal_categories`
--
ALTER TABLE `cal_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cal_events`
--
ALTER TABLE `cal_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `cal_exceptions`
--
ALTER TABLE `cal_exceptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cal_groups`
--
ALTER TABLE `cal_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cal_participants`
--
ALTER TABLE `cal_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `cal_views`
--
ALTER TABLE `cal_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cf_blocks`
--
ALTER TABLE `cf_blocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cf_categories`
--
ALTER TABLE `cf_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cf_fields`
--
ALTER TABLE `cf_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cf_tree_select_options`
--
ALTER TABLE `cf_tree_select_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comments_comment`
--
ALTER TABLE `comments_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comments_label`
--
ALTER TABLE `comments_label`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `core_acl`
--
ALTER TABLE `core_acl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `core_acl_group_changes`
--
ALTER TABLE `core_acl_group_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=258;

--
-- AUTO_INCREMENT for table `core_auth_allow_group`
--
ALTER TABLE `core_auth_allow_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_change`
--
ALTER TABLE `core_change`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `core_cron_job`
--
ALTER TABLE `core_cron_job`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `core_customfields_field`
--
ALTER TABLE `core_customfields_field`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `core_customfields_field_set`
--
ALTER TABLE `core_customfields_field_set`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `core_customfields_select_option`
--
ALTER TABLE `core_customfields_select_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=200096;

--
-- AUTO_INCREMENT for table `core_email_template`
--
ALTER TABLE `core_email_template`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_email_template_attachment`
--
ALTER TABLE `core_email_template_attachment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_entity`
--
ALTER TABLE `core_entity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `core_entity_filter`
--
ALTER TABLE `core_entity_filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_group`
--
ALTER TABLE `core_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `core_link`
--
ALTER TABLE `core_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `core_module`
--
ALTER TABLE `core_module`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `core_oauth_client`
--
ALTER TABLE `core_oauth_client`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_search`
--
ALTER TABLE `core_search`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- AUTO_INCREMENT for table `core_smtp_account`
--
ALTER TABLE `core_smtp_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `core_user`
--
ALTER TABLE `core_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `em_accounts`
--
ALTER TABLE `em_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `em_aliases`
--
ALTER TABLE `em_aliases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `em_filters`
--
ALTER TABLE `em_filters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `em_labels`
--
ALTER TABLE `em_labels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `em_links`
--
ALTER TABLE `em_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `fs_files`
--
ALTER TABLE `fs_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fs_folders`
--
ALTER TABLE `fs_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `fs_notification_messages`
--
ALTER TABLE `fs_notification_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fs_templates`
--
ALTER TABLE `fs_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fs_versions`
--
ALTER TABLE `fs_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `go_advanced_searches`
--
ALTER TABLE `go_advanced_searches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `go_clients`
--
ALTER TABLE `go_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `go_cron`
--
ALTER TABLE `go_cron`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `go_holidays`
--
ALTER TABLE `go_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `go_link_folders`
--
ALTER TABLE `go_link_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `go_log`
--
ALTER TABLE `go_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `go_reminders`
--
ALTER TABLE `go_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `go_saved_exports`
--
ALTER TABLE `go_saved_exports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `go_templates`
--
ALTER TABLE `go_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `imapauth_server`
--
ALTER TABLE `imapauth_server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ld_credit_types`
--
ALTER TABLE `ld_credit_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ld_leave_days`
--
ALTER TABLE `ld_leave_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ld_year_credits`
--
ALTER TABLE `ld_year_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notes_note`
--
ALTER TABLE `notes_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `notes_note_book`
--
ALTER TABLE `notes_note_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `pa_aliases`
--
ALTER TABLE `pa_aliases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `pa_domains`
--
ALTER TABLE `pa_domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pa_mailboxes`
--
ALTER TABLE `pa_mailboxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `pr2_expenses`
--
ALTER TABLE `pr2_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pr2_expense_budgets`
--
ALTER TABLE `pr2_expense_budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pr2_hours`
--
ALTER TABLE `pr2_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pr2_income`
--
ALTER TABLE `pr2_income`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pr2_income_items`
--
ALTER TABLE `pr2_income_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pr2_projects`
--
ALTER TABLE `pr2_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pr2_standard_tasks`
--
ALTER TABLE `pr2_standard_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pr2_statuses`
--
ALTER TABLE `pr2_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pr2_tasks`
--
ALTER TABLE `pr2_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pr2_templates`
--
ALTER TABLE `pr2_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pr2_templates_events`
--
ALTER TABLE `pr2_templates_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pr2_types`
--
ALTER TABLE `pr2_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `site_content`
--
ALTER TABLE `site_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_menu`
--
ALTER TABLE `site_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_menu_item`
--
ALTER TABLE `site_menu_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `site_sites`
--
ALTER TABLE `site_sites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `su_announcements`
--
ALTER TABLE `su_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `su_rss_feeds`
--
ALTER TABLE `su_rss_feeds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_calendar_user`
--
ALTER TABLE `sync_calendar_user`
  MODIFY `calendar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_note_categories_user`
--
ALTER TABLE `sync_note_categories_user`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sync_tasklist_user`
--
ALTER TABLE `sync_tasklist_user`
  MODIFY `tasklist_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ta_categories`
--
ALTER TABLE `ta_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ta_tasklists`
--
ALTER TABLE `ta_tasklists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ta_tasks`
--
ALTER TABLE `ta_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `test_a`
--
ALTER TABLE `test_a`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_c`
--
ALTER TABLE `test_c`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `ti_groups`
--
ALTER TABLE `ti_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ti_messages`
--
ALTER TABLE `ti_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ti_rates`
--
ALTER TABLE `ti_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ti_settings`
--
ALTER TABLE `ti_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ti_statuses`
--
ALTER TABLE `ti_statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ti_templates`
--
ALTER TABLE `ti_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ti_tickets`
--
ALTER TABLE `ti_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ti_types`
--
ALTER TABLE `ti_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ti_type_groups`
--
ALTER TABLE `ti_type_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abr_relation`
--
ALTER TABLE `abr_relation`
  ADD CONSTRAINT `relationgroup` FOREIGN KEY (`relationgroup_id`) REFERENCES `abr_relationgroup` (`id`);

--
-- Constraints for table `addressbook_address`
--
ALTER TABLE `addressbook_address`
  ADD CONSTRAINT `addressbook_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_addressbook`
--
ALTER TABLE `addressbook_addressbook`
  ADD CONSTRAINT `addressbook_addressbook_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `addressbook_addressbook_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_addressbook_ibfk_3` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `addressbook_contact`
--
ALTER TABLE `addressbook_contact`
  ADD CONSTRAINT `addressbook_contact_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_2` FOREIGN KEY (`photoBlobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `addressbook_contact_ibfk_3` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_ibfk_4` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_ibfk_5` FOREIGN KEY (`goUserId`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_ibfk_6` FOREIGN KEY (`vcardBlobId`) REFERENCES `core_blob` (`id`);

--
-- Constraints for table `addressbook_contact_custom_fields`
--
ALTER TABLE `addressbook_contact_custom_fields`
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_1` FOREIGN KEY (`Company_1`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_10` FOREIGN KEY (`Select_1`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_11` FOREIGN KEY (`Treeselect_1`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_16` FOREIGN KEY (`Company`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_17` FOREIGN KEY (`Contact`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_2` FOREIGN KEY (`Contact_1`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_21` FOREIGN KEY (`User`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_25` FOREIGN KEY (`Select`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_26` FOREIGN KEY (`Treeselect`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `addressbook_contact_custom_fields_ibfk_go_6` FOREIGN KEY (`User_1`) REFERENCES `core_user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `addressbook_contact_group`
--
ALTER TABLE `addressbook_contact_group`
  ADD CONSTRAINT `addressbook_contact_group_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `addressbook_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_contact_star`
--
ALTER TABLE `addressbook_contact_star`
  ADD CONSTRAINT `addressbook_contact_star_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_contact_star_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_date`
--
ALTER TABLE `addressbook_date`
  ADD CONSTRAINT `addressbook_date_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_email_address`
--
ALTER TABLE `addressbook_email_address`
  ADD CONSTRAINT `addressbook_email_address_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_group`
--
ALTER TABLE `addressbook_group`
  ADD CONSTRAINT `addressbook_group_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`);

--
-- Constraints for table `addressbook_phone_number`
--
ALTER TABLE `addressbook_phone_number`
  ADD CONSTRAINT `addressbook_phone_number_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_url`
--
ALTER TABLE `addressbook_url`
  ADD CONSTRAINT `addressbook_url_ibfk_1` FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `addressbook_user_settings`
--
ALTER TABLE `addressbook_user_settings`
  ADD CONSTRAINT `addressbook_user_settings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_user_settings_ibfk_2` FOREIGN KEY (`defaultAddressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookmarks_bookmark`
--
ALTER TABLE `bookmarks_bookmark`
  ADD CONSTRAINT `bookmarks_bookmark_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookmarks_bookmark_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `bookmarks_category` (`id`);

--
-- Constraints for table `bookmarks_category`
--
ALTER TABLE `bookmarks_category`
  ADD CONSTRAINT `bookmarks_category_acl_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `bookmarks_category_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bookmarks_category_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bs_orders`
--
ALTER TABLE `bs_orders`
  ADD CONSTRAINT `bs_orders_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bs_orders_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bs_orders_custom_fields`
--
ALTER TABLE `bs_orders_custom_fields`
  ADD CONSTRAINT `bs_orders_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `bs_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bs_products_custom_fields`
--
ALTER TABLE `bs_products_custom_fields`
  ADD CONSTRAINT `bs_products_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `bs_products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cal_calendars_custom_fields`
--
ALTER TABLE `cal_calendars_custom_fields`
  ADD CONSTRAINT `cal_calendars_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cal_calendars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cal_events_custom_fields`
--
ALTER TABLE `cal_events_custom_fields`
  ADD CONSTRAINT `cal_events_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `cal_events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments_attachment`
--
ALTER TABLE `comments_attachment`
  ADD CONSTRAINT `fk_comments_attachment_comments_comment1` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comments_attachment_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `comments_comment`
--
ALTER TABLE `comments_comment`
  ADD CONSTRAINT `fk_comments_comment_core_user1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comments_comment_core_user2` FOREIGN KEY (`modifiedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `comments_comment_image`
--
ALTER TABLE `comments_comment_image`
  ADD CONSTRAINT `comments_comment_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `comments_comment_image_ibfk_2` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments_comment_label`
--
ALTER TABLE `comments_comment_label`
  ADD CONSTRAINT `fk_comments_label_has_comments_comment_comments_comment1` FOREIGN KEY (`commentId`) REFERENCES `comments_comment` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comments_label_has_comments_comment_comments_label1` FOREIGN KEY (`labelId`) REFERENCES `comments_label` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `core_acl`
--
ALTER TABLE `core_acl`
  ADD CONSTRAINT `core_acl_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_acl_ibfk_2` FOREIGN KEY (`ownedBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `core_acl_group`
--
ALTER TABLE `core_acl_group`
  ADD CONSTRAINT `core_acl_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_acl_group_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_acl_group_changes`
--
ALTER TABLE `core_acl_group_changes`
  ADD CONSTRAINT `all` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_auth_allow_group`
--
ALTER TABLE `core_auth_allow_group`
  ADD CONSTRAINT `core_auth_allow_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_auth_method`
--
ALTER TABLE `core_auth_method`
  ADD CONSTRAINT `core_auth_method_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_auth_password`
--
ALTER TABLE `core_auth_password`
  ADD CONSTRAINT `core_auth_password_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_change`
--
ALTER TABLE `core_change`
  ADD CONSTRAINT `core_change_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `core_change_user`
--
ALTER TABLE `core_change_user`
  ADD CONSTRAINT `core_change_user_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_user_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_change_user_modseq`
--
ALTER TABLE `core_change_user_modseq`
  ADD CONSTRAINT `core_change_user_modseq_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_change_user_modseq_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_cron_job`
--
ALTER TABLE `core_cron_job`
  ADD CONSTRAINT `core_cron_job_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_customfields_field`
--
ALTER TABLE `core_customfields_field`
  ADD CONSTRAINT `core_customfields_field_ibfk_1` FOREIGN KEY (`fieldSetId`) REFERENCES `core_customfields_field_set` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_customfields_field_set`
--
ALTER TABLE `core_customfields_field_set`
  ADD CONSTRAINT `core_customfields_field_set_ibfk_1` FOREIGN KEY (`entityId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_customfields_field_set_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

--
-- Constraints for table `core_customfields_multiselect_43`
--
ALTER TABLE `core_customfields_multiselect_43`
  ADD CONSTRAINT `core_customfields_multiselect_43_ibfk_1` FOREIGN KEY (`id`) REFERENCES `addressbook_contact_custom_fields` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_customfields_multiselect_43_ibfk_2` FOREIGN KEY (`optionId`) REFERENCES `core_customfields_select_option` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_customfields_select_option`
--
ALTER TABLE `core_customfields_select_option`
  ADD CONSTRAINT `core_customfields_select_option_ibfk_1` FOREIGN KEY (`fieldId`) REFERENCES `core_customfields_field` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_email_template`
--
ALTER TABLE `core_email_template`
  ADD CONSTRAINT `core_email_template_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `core_email_template_ibfk_2` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_email_template_attachment`
--
ALTER TABLE `core_email_template_attachment`
  ADD CONSTRAINT `core_email_template_attachment_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `core_email_template_attachment_ibfk_2` FOREIGN KEY (`emailTemplateId`) REFERENCES `core_email_template` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_entity`
--
ALTER TABLE `core_entity`
  ADD CONSTRAINT `core_entity_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_entity_ibfk_2` FOREIGN KEY (`defaultAclId`) REFERENCES `core_acl` (`id`);

--
-- Constraints for table `core_entity_filter`
--
ALTER TABLE `core_entity_filter`
  ADD CONSTRAINT `core_entity_filter_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `core_entity_filter_ibfk_2` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_group`
--
ALTER TABLE `core_group`
  ADD CONSTRAINT `core_group_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  ADD CONSTRAINT `core_group_ibfk_2` FOREIGN KEY (`isUserGroupFor`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_group_default_group`
--
ALTER TABLE `core_group_default_group`
  ADD CONSTRAINT `core_group_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_link`
--
ALTER TABLE `core_link`
  ADD CONSTRAINT `fromEntity` FOREIGN KEY (`fromEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `toEntity` FOREIGN KEY (`toEntityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_module`
--
ALTER TABLE `core_module`
  ADD CONSTRAINT `acl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

--
-- Constraints for table `core_oauth_access_token`
--
ALTER TABLE `core_oauth_access_token`
  ADD CONSTRAINT `core_oauth_access_token_ibfk_2` FOREIGN KEY (`userIdentifier`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_oauth_access_token_ibfk_3` FOREIGN KEY (`clientId`) REFERENCES `core_oauth_client` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_search`
--
ALTER TABLE `core_search`
  ADD CONSTRAINT `core_search_ibfk_1` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_search_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_setting`
--
ALTER TABLE `core_setting`
  ADD CONSTRAINT `module` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_smtp_account`
--
ALTER TABLE `core_smtp_account`
  ADD CONSTRAINT `core_smtp_account_ibfk_1` FOREIGN KEY (`moduleId`) REFERENCES `core_module` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_smtp_account_ibfk_2` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

--
-- Constraints for table `core_user`
--
ALTER TABLE `core_user`
  ADD CONSTRAINT `fk_user_avatar_id` FOREIGN KEY (`avatarId`) REFERENCES `core_blob` (`id`) ON UPDATE NO ACTION;

--
-- Constraints for table `core_user_custom_fields`
--
ALTER TABLE `core_user_custom_fields`
  ADD CONSTRAINT `core_user_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_user_default_group`
--
ALTER TABLE `core_user_default_group`
  ADD CONSTRAINT `core_user_default_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `core_user_group`
--
ALTER TABLE `core_user_group`
  ADD CONSTRAINT `core_user_group_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `core_user_group_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fs_files_custom_fields`
--
ALTER TABLE `fs_files_custom_fields`
  ADD CONSTRAINT `fs_files_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_files` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fs_folders_custom_fields`
--
ALTER TABLE `fs_folders_custom_fields`
  ADD CONSTRAINT `fs_folders_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_folders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `googleauth_secret`
--
ALTER TABLE `googleauth_secret`
  ADD CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`);

--
-- Constraints for table `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  ADD CONSTRAINT `imapauth_server_domain_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imapauth_server_group`
--
ALTER TABLE `imapauth_server_group`
  ADD CONSTRAINT `imapauth_server_group_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `imapauth_server_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes_note`
--
ALTER TABLE `notes_note`
  ADD CONSTRAINT `notes_note_ibfk_1` FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book` (`id`);

--
-- Constraints for table `notes_note_book`
--
ALTER TABLE `notes_note_book`
  ADD CONSTRAINT `notes_note_book_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

--
-- Constraints for table `notes_note_custom_fields`
--
ALTER TABLE `notes_note_custom_fields`
  ADD CONSTRAINT `notes_note_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes_note_image`
--
ALTER TABLE `notes_note_image`
  ADD CONSTRAINT `notes_note_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `notes_note_image_ibfk_2` FOREIGN KEY (`noteId`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes_user_settings`
--
ALTER TABLE `notes_user_settings`
  ADD CONSTRAINT `notes_user_settings_ibfk_1` FOREIGN KEY (`defaultNoteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_user_settings_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pr2_employee_activity_rate`
--
ALTER TABLE `pr2_employee_activity_rate`
  ADD CONSTRAINT `fk_pr2_employee_activity` FOREIGN KEY (`employee_id`) REFERENCES `pr2_employees` (`user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `pr2_expense_budgets`
--
ALTER TABLE `pr2_expense_budgets`
  ADD CONSTRAINT `pr2_expense_budgets_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pr2_hours_custom_fields`
--
ALTER TABLE `pr2_hours_custom_fields`
  ADD CONSTRAINT `pr2_hours_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pr2_hours` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pr2_projects`
--
ALTER TABLE `pr2_projects`
  ADD CONSTRAINT `pr2_projects_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pr2_projects_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `addressbook_contact` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pr2_projects_custom_fields`
--
ALTER TABLE `pr2_projects_custom_fields`
  ADD CONSTRAINT `pr2_projects_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `pr2_projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pr2_resource_activity_rate`
--
ALTER TABLE `pr2_resource_activity_rate`
  ADD CONSTRAINT `fk_pr2_resource_activity` FOREIGN KEY (`project_id`,`employee_id`) REFERENCES `pr2_resources` (`project_id`, `user_id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `site_content_custom_fields`
--
ALTER TABLE `site_content_custom_fields`
  ADD CONSTRAINT `site_content_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_content` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `site_sites_custom_fields`
--
ALTER TABLE `site_sites_custom_fields`
  ADD CONSTRAINT `site_sites_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_sites` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sync_addressbook_user`
--
ALTER TABLE `sync_addressbook_user`
  ADD CONSTRAINT `sync_addressbook_user_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sync_user_note_book`
--
ALTER TABLE `sync_user_note_book`
  ADD CONSTRAINT `sync_user_note_book_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ta_tasks_custom_fields`
--
ALTER TABLE `ta_tasks_custom_fields`
  ADD CONSTRAINT `ta_tasks_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ta_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_a_has_many`
--
ALTER TABLE `test_a_has_many`
  ADD CONSTRAINT `test_a_has_many_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_a_has_one`
--
ALTER TABLE `test_a_has_one`
  ADD CONSTRAINT `test_a_has_one_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `test_a_map`
--
ALTER TABLE `test_a_map`
  ADD CONSTRAINT `test_a_map_ibfk_1` FOREIGN KEY (`aId`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_a_map_ibfk_2` FOREIGN KEY (`anotherAId`) REFERENCES `test_a` (`id`);

--
-- Constraints for table `test_b`
--
ALTER TABLE `test_b`
  ADD CONSTRAINT `test_b_ibfk_1` FOREIGN KEY (`id`) REFERENCES `test_a` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_b_ibfk_2` FOREIGN KEY (`cId`) REFERENCES `test_c` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `test_b_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ti_tickets_custom_fields`
--
ALTER TABLE `ti_tickets_custom_fields`
  ADD CONSTRAINT `ti_tickets_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ti_tickets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
