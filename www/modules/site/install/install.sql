-- --------------------------------------------------------

--
-- Table structure for table `site_sites`
--

CREATE TABLE IF NOT EXISTS `site_sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `domain` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `ssl` tinyint(1) NOT NULL DEFAULT '0',
  `mod_rewrite` tinyint(1) NOT NULL DEFAULT '0',
  `mod_rewrite_base_path` varchar(50) NOT NULL DEFAULT '/',
  `base_path` varchar(100) NOT NULL DEFAULT '',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `language` varchar(10) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `domain` (`domain`)
) ENGINE=InnoDB;


-- --------------------------------------------------------

--
-- Table structure for table `site_content`
--

CREATE TABLE IF NOT EXISTS `site_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `ptime` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `meta_title` varchar(100) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `content` text,
  `status` int(11) NOT NULL DEFAULT '1',
  `parent_id` int(11) DEFAULT NULL,
  `site_id` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `template` varchar(255) DEFAULT NULL,
  `default_child_template` varchar(255) DEFAULT NULL,
  `content_type` VARCHAR( 20 ) NOT NULL DEFAULT  'markdown',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`,`site_id`)
) ENGINE=InnoDB;



CREATE TABLE `site_content_custom_fields` (
   `id` int(11) NOT NULL,
   PRIMARY KEY (`id`),
   CONSTRAINT `site_content_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_content` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `site_sites_custom_fields` (
    `id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `site_sites_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `site_sites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `site_multifile_files`
--

CREATE TABLE IF NOT EXISTS `site_multifile_files` (
  `model_id` int(11) NOT NULL,
  `field_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`,`field_id`,`file_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Table structure for table `site_menu`
--

CREATE TABLE IF NOT EXISTS `site_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `menu_slug` varchar(255) NOT NULL,
  `label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


-- --------------------------------------------------------

--
-- Table structure for table `site_menu_item`
--

CREATE TABLE IF NOT EXISTS `site_menu_item` (
  `menu_id` int(11) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `label` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `display_children` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `target` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;