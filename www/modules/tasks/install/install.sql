--
-- Tabelstructuur voor tabel `cf_ta_tasks`
--

DROP TABLE IF EXISTS `cf_ta_tasks`;
CREATE TABLE IF NOT EXISTS `cf_ta_tasks` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `go_links_ta_tasks`
--

DROP TABLE IF EXISTS `go_links_ta_tasks`;
CREATE TABLE IF NOT EXISTS `go_links_ta_tasks` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY `model_id` (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB;

-- --------------------------------------------------------






--
-- Tabelstructuur voor tabel `ta_categories`
--

DROP TABLE IF EXISTS `ta_categories`;
CREATE TABLE IF NOT EXISTS `ta_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ta_portlet_tasklists`
--

DROP TABLE IF EXISTS `ta_portlet_tasklists`;
CREATE TABLE IF NOT EXISTS `ta_portlet_tasklists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`tasklist_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ta_settings`
--

DROP TABLE IF EXISTS `ta_settings`;
CREATE TABLE IF NOT EXISTS `ta_settings` (
  `user_id` int(11) NOT NULL,
  `reminder_days` int(11) NOT NULL DEFAULT '0',
  `reminder_time` varchar(10) NOT NULL DEFAULT '0',
  `remind` tinyint(1) NOT NULL DEFAULT '0',
  `default_tasklist_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ta_tasklists`
--

DROP TABLE IF EXISTS `ta_tasklists`;
CREATE TABLE IF NOT EXISTS `ta_tasklists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `version` INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ta_tasks`
--

DROP TABLE IF EXISTS `ta_tasks`;
CREATE TABLE IF NOT EXISTS `ta_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `tasklist_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
	`muser_id` int(11) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL,
  `due_time` int(11) NOT NULL,
  `completion_time` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  `status` varchar(20) DEFAULT NULL,
  `repeat_end_time` int(11) NOT NULL DEFAULT '0',
  `reminder` int(11) NOT NULL DEFAULT '0',
  `rrule` varchar(100) NOT NULL DEFAULT '',
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '1',
	`percentage_complete` TINYINT NOT NULL DEFAULT '0',
	`project_id` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `list_id` (`tasklist_id`),
  KEY `rrule` (`rrule`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB;


--
-- Tabelstructuur voor tabel `su_visible_lists`
--

CREATE TABLE IF NOT EXISTS `su_visible_lists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`tasklist_id`)
) ENGINE=InnoDB;