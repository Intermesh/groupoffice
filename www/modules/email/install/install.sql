
--
-- Tabelstructuur voor tabel `emp_folders`
--

DROP TABLE IF EXISTS `emp_folders`;
CREATE TABLE IF NOT EXISTS `emp_folders` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_accounts`
--

DROP TABLE IF EXISTS `em_accounts`;
CREATE TABLE IF NOT EXISTS `em_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(4) DEFAULT NULL,
  `host` varchar(100) DEFAULT NULL,
  `port` int(11) NOT NULL DEFAULT '0',
  `deprecated_use_ssl` tinyint(1) NOT NULL DEFAULT '0',
  `novalidate_cert` tinyint(1) NOT NULL DEFAULT '0',
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(512) DEFAULT NULL,
	`imap_encryption` char(3) NOT NULL,
  `imap_allow_self_signed` tinyint(1) NOT NULL DEFAULT '1',
  `mbroot` varchar(30) NOT NULL DEFAULT '',
  `sent` varchar(100) DEFAULT 'Sent',
  `drafts` varchar(100) DEFAULT 'Drafts',
  `trash` varchar(100) NOT NULL DEFAULT 'Trash',
  `spam` varchar(100) NOT NULL DEFAULT 'Spam',
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) NOT NULL,
  `smtp_encryption` char(3) NOT NULL,
	`smtp_allow_self_signed` tinyint(1) NOT NULL DEFAULT '0',
  `smtp_username` varchar(50) DEFAULT NULL,
  `smtp_password` varchar(512) NOT NULL DEFAULT '',
  `password_encrypted` tinyint(4) NOT NULL DEFAULT '0',
  `ignore_sent_folder` tinyint(1) NOT NULL DEFAULT '0',
  `sieve_port` int(11) NOT NULL,
  `sieve_usetls` tinyint(1) NOT NULL DEFAULT '1',
  `check_mailboxes` text,
  `do_not_mark_as_read` tinyint(1) NOT NULL DEFAULT '0',
	`signature_below_reply` tinyint(1) NOT NULL DEFAULT '0',
	`full_reply_headers` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_accounts_collapsed`
--

DROP TABLE IF EXISTS `em_accounts_collapsed`;
CREATE TABLE IF NOT EXISTS `em_accounts_collapsed` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_accounts_sort`
--

DROP TABLE IF EXISTS `em_accounts_sort`;
CREATE TABLE IF NOT EXISTS `em_accounts_sort` (
  `account_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_aliases`
--

DROP TABLE IF EXISTS `em_aliases`;
CREATE TABLE IF NOT EXISTS `em_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `signature` text,
  `default` BOOLEAN NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_filters`
--

DROP TABLE IF EXISTS `em_filters`;
CREATE TABLE IF NOT EXISTS `em_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL DEFAULT '0',
  `field` varchar(20) DEFAULT NULL,
  `keyword` varchar(100) DEFAULT NULL,
  `folder` varchar(100) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `mark_as_read` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_folders`
--

DROP TABLE IF EXISTS `em_folders`;
CREATE TABLE IF NOT EXISTS `em_folders` (
  `id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `subscribed` enum('0','1') NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `delimiter` char(1) NOT NULL DEFAULT '',
  `sort_order` tinyint(4) NOT NULL DEFAULT '0',
  `msgcount` int(11) NOT NULL DEFAULT '0',
  `unseen` int(11) NOT NULL DEFAULT '0',
  `auto_check` enum('0','1') NOT NULL DEFAULT '0',
  `can_have_children` tinyint(1) NOT NULL,
  `no_select` tinyint(1) DEFAULT NULL,
  `sort` longtext,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_folders_expanded`
--

DROP TABLE IF EXISTS `em_folders_expanded`;
CREATE TABLE IF NOT EXISTS `em_folders_expanded` (
  `folder_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`folder_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_links`
--

DROP TABLE IF EXISTS `em_links`;
CREATE TABLE IF NOT EXISTS `em_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `from` varchar(255) DEFAULT NULL,
  `to` text,
  `subject` varchar(255) DEFAULT NULL,
  `time` int(11) NOT NULL DEFAULT '0',
  `path` varchar(255) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL DEFAULT '0',
	`muser_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL,
  `uid` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `account_id` (`user_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `em_messages_cache`
--

DROP TABLE IF EXISTS `em_messages_cache`;
CREATE TABLE IF NOT EXISTS `em_messages_cache` (
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
) ENGINE=InnoDB;

-- --------------------------------------------------------


--
-- Tabelstructuur voor tabel `go_links_em_links`
--

DROP TABLE IF EXISTS `go_links_em_links`;
CREATE TABLE IF NOT EXISTS `go_links_em_links` (
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

DROP TABLE IF EXISTS `em_portlet_folders`;
CREATE TABLE IF NOT EXISTS `em_portlet_folders` (
  `account_id` int(11) NOT NULL,
	`folder_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`account_id`,`folder_name`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `em_contacts_last_mail_times`;
CREATE TABLE IF NOT EXISTS `em_contacts_last_mail_times` (
  `contact_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_mail_time` int(11) NOT NULL,
  PRIMARY KEY (`contact_id`,`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `em_labels`;
CREATE TABLE IF NOT EXISTS `em_labels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `flag` varchar(100) NOT NULL,
  `color` varchar(6) NOT NULL,
  `account_id` int(11) NOT NULL,
  `default` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



DROP TABLE IF EXISTS `email_default_email_templates`;
CREATE TABLE IF NOT EXISTS `email_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_default_email_templates`
--

DROP TABLE IF EXISTS `email_default_email_account_templates`;
CREATE TABLE IF NOT EXISTS `email_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB;


ALTER TABLE `em_contacts_last_mail_times` ADD INDEX(`last_mail_time`);