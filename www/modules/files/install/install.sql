

--
-- Tabelstructuur voor tabel `fs_files`
--

DROP TABLE IF EXISTS `fs_files`;
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
-- Tabelstructuur voor tabel `fs_folders`
--

DROP TABLE IF EXISTS `fs_folders`;

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


ALTER TABLE `fs_files`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folder_id_2` (`folder_id`,`name`),
  ADD KEY `folder_id` (`folder_id`),
  ADD KEY `name` (`name`),
  ADD KEY `extension` (`extension`);

ALTER TABLE `fs_folders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parent_id_3` (`parent_id`,`name`),
  ADD KEY `name` (`name`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `visible` (`visible`);


ALTER TABLE `fs_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `fs_folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fs_new_files`
--

DROP TABLE IF EXISTS `fs_new_files`;
CREATE TABLE IF NOT EXISTS `fs_new_files` (
  `file_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  KEY `file_id` (`file_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fs_notifications`
--

DROP TABLE IF EXISTS `fs_notifications`;
CREATE TABLE IF NOT EXISTS `fs_notifications` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB;



--
-- Tabelstructuur voor tabel `fs_shared_cache`
--

DROP TABLE IF EXISTS `fs_shared_cache`;
CREATE TABLE IF NOT EXISTS `fs_shared_cache` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` text NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fs_statuses`
--

DROP TABLE IF EXISTS `fs_statuses`;
CREATE TABLE IF NOT EXISTS `fs_statuses` (
  `id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fs_status_history`
--

DROP TABLE IF EXISTS `fs_status_history`;
CREATE TABLE IF NOT EXISTS `fs_status_history` (
  `id` int(11) NOT NULL DEFAULT '0',
  `link_id` int(11) NOT NULL DEFAULT '0',
  `status_id` int(11) NOT NULL DEFAULT '0',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`id`),
  KEY `link_id` (`link_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `fs_templates`
--

DROP TABLE IF EXISTS `fs_templates`;
CREATE TABLE IF NOT EXISTS `fs_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `acl_id` int(11) NOT NULL,
  `content` mediumblob NOT NULL,
  `extension` char(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB ;

-- --------------------------------------------------------



--
-- Tabelstructuur voor tabel `go_links_fs_files`
--

DROP TABLE IF EXISTS `go_links_fs_files`;
CREATE TABLE IF NOT EXISTS `go_links_fs_files` (
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
-- Tabelstructuur voor tabel `go_links_fs_folders`
--

DROP TABLE IF EXISTS `go_links_fs_folders`;
CREATE TABLE IF NOT EXISTS `go_links_fs_folders` (
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

DROP TABLE IF EXISTS `fs_versions`;
CREATE TABLE IF NOT EXISTS `fs_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '1',
  `size_bytes` BIGINT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB ;

DROP TABLE IF EXISTS `fs_folder_pref`;
CREATE TABLE IF NOT EXISTS `fs_folder_pref` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `thumbs` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `fs_notification_messages`;
CREATE TABLE IF NOT EXISTS `fs_notification_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `modified_user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `arg1` varchar(255) NOT NULL,
  `arg2` varchar(255) NOT NULL,
  `mtime` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`, `status`)
) ENGINE=InnoDB ;

DROP TABLE IF EXISTS `fs_bookmarks`;
CREATE TABLE IF NOT EXISTS `fs_bookmarks` (
	`folder_id` int(11) NOT NULL,
	`user_id` int(11) NOT NULL,
	PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `fs_filehandlers`;
CREATE TABLE IF NOT EXISTS `fs_filehandlers` (
  `user_id` int(11) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `cls` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`extension`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `fs_shared_root_folders`;
CREATE TABLE IF NOT EXISTS `fs_shared_root_folders` (
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`folder_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `fs_files_custom_fields` (
 `id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 CONSTRAINT `fs_files_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_files` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `fs_folders_custom_fields` (
 `id` int(11) NOT NULL,
 PRIMARY KEY (`id`),
 CONSTRAINT `fs_folders_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `fs_folders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


ALTER TABLE `fs_files` ADD INDEX(`mtime`);
ALTER TABLE `fs_files` ADD INDEX(`content_expire_date`);
