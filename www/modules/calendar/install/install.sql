-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_calendars`
--

DROP TABLE IF EXISTS `cal_calendars`;
CREATE TABLE IF NOT EXISTS `cal_calendars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT '1',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `start_hour` tinyint(4) NOT NULL DEFAULT '0',
  `end_hour` tinyint(4) NOT NULL DEFAULT '0',
  `background` varchar(6) DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT '1800',
  `public` tinyint(1) NOT NULL DEFAULT '0',
  `shared_acl` tinyint(1) NOT NULL DEFAULT '0',
  `show_bdays` tinyint(1) NOT NULL DEFAULT '0',
  `show_completed_tasks` tinyint(1) NOT NULL DEFAULT '1',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `project_id` int(11) NOT NULL DEFAULT '0',
  `tasklist_id` int(11) NOT NULL DEFAULT '0',
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `show_holidays` tinyint(1) NOT NULL DEFAULT '1',
	`enable_ics_import` tinyint(1) NOT NULL DEFAULT '0',
	`ics_import_url` varchar(512) NOT NULL DEFAULT '',
	`tooltip` varchar(127) NOT NULL DEFAULT '',
  `version` INT UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_categories`
--

DROP TABLE IF EXISTS `cal_categories`;
CREATE TABLE IF NOT EXISTS `cal_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` char(6) NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL,
	`acl_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_events`
--

DROP TABLE IF EXISTS `cal_events`;
CREATE TABLE IF NOT EXISTS `cal_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `calendar_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `start_time` int(11) NOT NULL DEFAULT '0',
  `end_time` int(11) NOT NULL DEFAULT '0',
  `timezone` VARCHAR(64) NOT NULL DEFAULT '',
  `all_day_event` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(150) NOT NULL,
  `description` text,
  `location` varchar(100) NOT NULL DEFAULT '',
  `repeat_end_time` int(11) NOT NULL DEFAULT '0',
  `reminder` int(11) NULL DEFAULT NULL,
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
	`muser_id` int(11) NOT NULL DEFAULT '0',
  `busy` tinyint(1) NOT NULL DEFAULT '1',
  `status` varchar(20) NOT NULL DEFAULT 'NEEDS-ACTION',
  `resource_event_id` int(11) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `rrule` varchar(100) NOT NULL DEFAULT '',
  `background` char(6) NOT NULL DEFAULT 'ebf1e2',
  `files_folder_id` int(11) NOT NULL,
  `read_only` tinyint(1) NOT NULL DEFAULT '0',
  `category_id` int(11) NULL,
  `exception_for_event_id` int(11) NOT NULL DEFAULT '0',
  `recurrence_id` varchar(20) NOT NULL DEFAULT '',
	`is_organizer` BOOLEAN NOT NULL DEFAULT '1',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_events_declined`
--

DROP TABLE IF EXISTS `cal_events_declined`;
CREATE TABLE IF NOT EXISTS `cal_events_declined` (
  `uid` varchar(190) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`uid`,`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_exceptions`
--

DROP TABLE IF EXISTS `cal_exceptions`;
CREATE TABLE IF NOT EXISTS `cal_exceptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `exception_event_id` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`muser_id` int(11) NOT NULL DEFAULT '0',
	`ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_groups`
--

DROP TABLE IF EXISTS `cal_groups`;
CREATE TABLE IF NOT EXISTS `cal_groups` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `fields` varchar(255) NOT NULL DEFAULT '',
  `show_not_as_busy` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_group_admins`
--

DROP TABLE IF EXISTS `cal_group_admins`;
CREATE TABLE IF NOT EXISTS `cal_group_admins` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_participants`
--

DROP TABLE IF EXISTS `cal_participants`;
CREATE TABLE IF NOT EXISTS `cal_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
	`contact_id` INT NOT NULL DEFAULT  '0',
  `status` varchar(50) NOT NULL DEFAULT 'NEEDS-ACTION',
  `last_modified` varchar(20) NOT NULL DEFAULT '',
  `is_organizer` tinyint(1) NOT NULL DEFAULT '0',
  `role` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_settings`
--

DROP TABLE IF EXISTS `cal_settings`;
CREATE TABLE IF NOT EXISTS `cal_settings` (
  `user_id` int(11) NOT NULL,
  `reminder` int(11) NULL DEFAULT NULL,
  `background` char(6) NOT NULL DEFAULT 'EBF1E2',
  `calendar_id` int(11) NOT NULL DEFAULT '0',
  `show_statuses` BOOLEAN NOT NULL DEFAULT TRUE,
  `check_conflict` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`user_id`),
  KEY `calendar_id` (`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_views`
--

DROP TABLE IF EXISTS `cal_views`;
CREATE TABLE IF NOT EXISTS `cal_views` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  `time_interval` int(11) NOT NULL DEFAULT '1800',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `merge` tinyint(1) NOT NULL DEFAULT '0',
  `owncolor` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_views_calendars`
--

DROP TABLE IF EXISTS `cal_views_calendars`;
CREATE TABLE IF NOT EXISTS `cal_views_calendars` (
  `view_id` int(11) NOT NULL DEFAULT '0',
  `calendar_id` int(11) NOT NULL DEFAULT '0',
  `background` char(6) NOT NULL DEFAULT 'CCFFCC',
  PRIMARY KEY (`view_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cal_visible_tasklists`
--

DROP TABLE IF EXISTS `cal_visible_tasklists`;
CREATE TABLE IF NOT EXISTS `cal_visible_tasklists` (
  `calendar_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY (`calendar_id`,`tasklist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `cal_calendar_user_colors`;
CREATE TABLE IF NOT EXISTS `cal_calendar_user_colors` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  `color` varchar(6) NOT NULL,
  PRIMARY KEY (`user_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tablestructure for table `cal_views_groups`
--

CREATE  TABLE IF NOT EXISTS `cal_views_groups` (
  `view_id` INT NOT NULL ,
  `group_id` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`view_id`, `group_id`) )
ENGINE = InnoDB;

--
-- Tabelstructuur voor tabel `cf_cal_calendars`
--

DROP TABLE IF EXISTS `cf_cal_calendars`;
CREATE TABLE IF NOT EXISTS `cf_cal_calendars` (
  `model_id` int(11) NOT NULL,
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_cal_events`
--

DROP TABLE IF EXISTS `cf_cal_events`;
CREATE TABLE IF NOT EXISTS `cf_cal_events` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


--
-- Tabelstructuur voor tabel `go_links_cal_events`
--

DROP TABLE IF EXISTS `go_links_cal_events`;
CREATE TABLE IF NOT EXISTS `go_links_cal_events` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY `model_id` (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `cal_views_groups`;
CREATE  TABLE IF NOT EXISTS `cal_views_groups` (
  `view_id` INT NOT NULL ,
  `group_id` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`view_id`, `group_id`) )
ENGINE = InnoDB;


--
-- Tabelstructuur voor tabel `su_visible_calendars`
--

CREATE TABLE IF NOT EXISTS `su_visible_calendars` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`calendar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `cal_calendars` ADD INDEX(`user_id`);