-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Oct 11, 2018 at 08:23 AM
-- Server version: 10.3.9-MariaDB-1:10.3.9+maria~bionic
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `groupoffice`
--

-- --------------------------------------------------------

--
-- Table structure for table `pages_page`
--

CREATE TABLE `pages_page` (
  `id` int(11) NOT NULL,
  `siteId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `pageName` varchar(190) NOT NULL DEFAULT 'page',
  `content` text DEFAULT NULL,
  `sortOrder` int(11) NOT NULL,
  `plainContent` text DEFAULT NULL,
  `slug` varchar(190) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pages_site`
--

CREATE TABLE `pages_site` (
  `id` int(11) NOT NULL,
  `siteName` varchar(190) NOT NULL,
  `fileFolderId` int(11) NOT NULL DEFAULT 1,
  `aclId` int(11) NOT NULL,
  `modifiedBy` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `documentFormat` varchar(190) NOT NULL DEFAULT 'html',
  `slug` varchar(190) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pages_page`
--
ALTER TABLE `pages_page`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug_UNIQUE` (`slug`),
  ADD KEY `fk_pages_page_pages_site1_idx` (`siteId`);

--
-- Indexes for table `pages_site`
--
ALTER TABLE `pages_site`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siteName_UNIQUE` (`siteName`),
  ADD UNIQUE KEY `slug_UNIQUE` (`slug`),
  ADD KEY `fk_pages_site_1_idx` (`aclId`),
  ADD KEY `fk_site_file_idx` (`fileFolderId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pages_page`
--
ALTER TABLE `pages_page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pages_site`
--
ALTER TABLE `pages_site`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pages_page`
--
ALTER TABLE `pages_page`
  ADD CONSTRAINT `fk_pages_page_pages_site1` FOREIGN KEY (`siteId`) REFERENCES `pages_site` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pages_site`
--
ALTER TABLE `pages_site`
  ADD CONSTRAINT `fk_site_acl` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_site_file` FOREIGN KEY (`fileFolderId`) REFERENCES `fs_folders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;