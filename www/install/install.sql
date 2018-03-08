ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cf_go_users` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------



CREATE TABLE IF NOT EXISTS `go_acl` (
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `group_id` int(11) NOT NULL DEFAULT '0',
  `level` tinyint(4) NOT NULL DEFAULT '10',
  PRIMARY KEY (`acl_id`,`user_id`,`group_id`),
  KEY `acl_id` (`acl_id`,`user_id`),
  KEY `acl_id_2` (`acl_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE IF NOT EXISTS `go_acl_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `description` varchar(50) DEFAULT NULL,
	`mtime` INT NOT NULL DEFAULT  '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_address_format` (
  `id` int(11) NOT NULL,
  `format` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `go_cache` (
  `user_id` int(11) NOT NULL,
  `key` varchar(190) NOT NULL DEFAULT '',
  `content` longtext,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`key`),
  KEY `mtime` (`mtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `go_countries` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) DEFAULT NULL,
  `iso_code_2` char(2) NOT NULL DEFAULT '',
  `iso_code_3` char(3) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_db_sequence` (
  `seq_name` varchar(50) NOT NULL DEFAULT '',
  `nextid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`seq_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL,
  `admin_only` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_holidays` (
  `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
   `date` DATE NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `region` varchar(10) NOT NULL DEFAULT '',
	`free_day` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `region` (`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_links_go_users` (
  `id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY `model_id` (`id`,`model_id`,`model_type_id`),
  KEY `id` (`id`,`folder_id`),
  KEY `ctime` (`ctime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_link_descriptions` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_link_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11)  NOT NULL DEFAULT '0',
  `model_id` int(11)  NOT NULL DEFAULT '0',
  `model_type_id` int(11)  NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `link_id` (`model_id`,`model_type_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT '',
  `model` varchar(255) NOT NULL DEFAULT '',
  `model_id` varchar(255) NOT NULL DEFAULT '',
  `ctime` int(11) NOT NULL,
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(45) NOT NULL DEFAULT '',
  `controller_route` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(20) NOT NULL DEFAULT '',
  `message` varchar(255) NOT NULL DEFAULT '',
  `jsonData` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_mail_counter` (
  `host` varchar(100) NOT NULL DEFAULT '',
  `date` date NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`host`),
  KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_model_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_modules` (
  `id` varchar(50) NOT NULL DEFAULT '',
  `version` int(11) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `admin_menu` tinyint(1) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
	`enabled` BOOLEAN NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `model_id` int(11) NOT NULL,
  `model_type_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `time` int(11) NOT NULL,
  `vtime` int(11) NOT NULL DEFAULT '0',
  `snooze_time` int(11) NOT NULL,
  `manual` tinyint(1) NOT NULL DEFAULT '0',
  `text` text,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_reminders_users` (
  `reminder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `mail_sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`reminder_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_saved_search_queries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sql` text NOT NULL,
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_search_cache` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `model_id` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` varchar(255) NOT NULL DEFAULT '',
  `model_type_id` int(11) NOT NULL DEFAULT '0',
  `model_name` varchar(100) DEFAULT NULL,
  `keywords` varchar(255) NOT NULL DEFAULT '',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  PRIMARY KEY (`model_id`,`model_type_id`),
  KEY `acl_id` (`acl_id`),
	INDEX name( `name` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_search_sync` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL DEFAULT '',
  `last_sync_time` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_settings` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `value` LONGTEXT,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_state` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` text,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `digest` VARCHAR( 255 ) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `recovery_email` varchar(100) NOT NULL,
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `date_format` varchar(20) NOT NULL DEFAULT 'dmY',
  `date_separator` char(1) NOT NULL DEFAULT '-',
  `time_format` varchar(10) NOT NULL DEFAULT 'G:i',
  `thousands_separator` varchar(1) NOT NULL DEFAULT '.',
  `decimal_separator` varchar(1) NOT NULL DEFAULT ',',
  `currency` char(3) NOT NULL DEFAULT '',
  `logins` int(11) NOT NULL DEFAULT '0',
  `lastlogin` int(11) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `max_rows_list` tinyint(4) NOT NULL DEFAULT '20',
  `timezone` varchar(50) NOT NULL DEFAULT 'Europe/Amsterdam',
  `start_module` varchar(50) NOT NULL DEFAULT 'summary',
  `language` varchar(20) NOT NULL DEFAULT 'en',
  `theme` varchar(20) NOT NULL DEFAULT 'Default',
  `first_weekday` tinyint(4) NOT NULL DEFAULT '0',
  `sort_name` varchar(20) NOT NULL DEFAULT 'first_name',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `muser_id` int(11) NOT NULL DEFAULT '0',
  `mute_sound` tinyint(1) NOT NULL DEFAULT '0',
  `mute_reminder_sound` tinyint(1) NOT NULL DEFAULT '0',
  `mute_new_mail_sound` tinyint(1) NOT NULL DEFAULT '0',
  `show_smilies` tinyint(1) NOT NULL DEFAULT '1',
  `auto_punctuation` tinyint(1) NOT NULL DEFAULT '0',
  `list_separator` char(3) NOT NULL DEFAULT ';',
  `text_separator` char(3) NOT NULL DEFAULT '"',
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `disk_quota` BIGINT NULL,
  `disk_usage` BIGINT NOT NULL DEFAULT '0',
  `mail_reminders` tinyint(1) NOT NULL DEFAULT '0',
  `popup_reminders` tinyint(1) NOT NULL DEFAULT '0',
  `popup_emails` tinyint(1) NOT NULL DEFAULT '0',
  `password_type` varchar(20) NOT NULL DEFAULT 'crypt',
	`holidayset` VARCHAR( 10 ) NULL,
	`sort_email_addresses_by_time` TINYINT(1) NOT NULL DEFAULT '0',
	`no_reminders` TINYINT(1) NOT NULL DEFAULT '0',
	`last_password_change` int(11) NOT NULL DEFAULT '0',
  `force_password_change` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_users_groups` (
  `group_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `go_advanced_searches` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(255) NOT NULL DEFAULT '',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`acl_id` int(11) NOT NULL DEFAULT '0',
	`data` TEXT NULL,
	`model_name` VARCHAR(100) NOT NULL DEFAULT '',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `minutes` varchar(100) NOT NULL DEFAULT '1',
  `hours` varchar(100) NOT NULL DEFAULT '1',
  `monthdays` varchar(100) NOT NULL DEFAULT '*',
  `months` varchar(100) NOT NULL DEFAULT '*',
  `weekdays` varchar(100) NOT NULL DEFAULT '*',
  `years` varchar(100) NOT NULL DEFAULT '*',
  `job` varchar(255) NOT NULL,
  `runonce` tinyint(1) NOT NULL DEFAULT '0',
  `nextrun` int(11) NOT NULL DEFAULT '0',
  `lastrun` int(11) NOT NULL DEFAULT '0',
  `completedat` int(11) NOT NULL DEFAULT '0',
  `error` TEXT NULL ,
  `autodestroy` BOOLEAN NOT NULL DEFAULT FALSE,
	`params` text NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `go_cron_groups` (
  `cronjob_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------


CREATE TABLE IF NOT EXISTS `go_cron_users` (
  `cronjob_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`cronjob_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS `go_cf_setting_tabs` (
  `cf_category_id` int(11) NOT NULL,
  PRIMARY KEY (`cf_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tabelstructuur voor tabel `go_working_weeks`
--

DROP TABLE IF EXISTS `go_working_weeks`;
CREATE TABLE IF NOT EXISTS `go_working_weeks` (
	`user_id` int(11) NOT NULL DEFAULT '0',
	`mo_work_hours` DOUBLE NOT NULL DEFAULT '8',
	`tu_work_hours` DOUBLE NOT NULL DEFAULT '8',
	`we_work_hours` DOUBLE NOT NULL DEFAULT '8',
	`th_work_hours` DOUBLE NOT NULL DEFAULT '8',
	`fr_work_hours` DOUBLE NOT NULL DEFAULT '8',
	`sa_work_hours` DOUBLE NOT NULL DEFAULT '0',
	`su_work_hours` DOUBLE NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `go_saved_exports`
--

CREATE TABLE IF NOT EXISTS `go_saved_exports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `view` varchar(255) NOT NULL,
  `export_columns` text,
  `orientation` enum('V','H') NOT NULL DEFAULT 'V',
  `include_column_names` tinyint(1) NOT NULL DEFAULT '1',
  `use_db_column_names` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

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
  `in_use` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `go_clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_footprint` (`footprint`);

ALTER TABLE `go_clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;