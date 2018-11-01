--
-- Tabelstructuur voor tabel `ab_addressbooks`
--

DROP TABLE IF EXISTS `ab_addressbooks`;
CREATE TABLE IF NOT EXISTS `ab_addressbooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `default_salutation` varchar(255) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `users` tinyint(1) NOT NULL DEFAULT '0',
	`create_folder` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_addresslists`
--

DROP TABLE IF EXISTS `ab_addresslists`;
CREATE TABLE IF NOT EXISTS `ab_addresslists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
	`addresslist_group_id` INT DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `default_salutation` varchar(50) DEFAULT NULL,
	`ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_addresslist_companies`
--

DROP TABLE IF EXISTS `ab_addresslist_companies`;
CREATE TABLE IF NOT EXISTS `ab_addresslist_companies` (
  `addresslist_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_addresslist_contacts`
--

DROP TABLE IF EXISTS `ab_addresslist_contacts`;
CREATE TABLE IF NOT EXISTS `ab_addresslist_contacts` (
  `addresslist_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`addresslist_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_companies`
--

DROP TABLE IF EXISTS `ab_companies`;
CREATE TABLE IF NOT EXISTS `ab_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `addressbook_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT '',
  `name2` varchar(100) DEFAULT '',
  `address` varchar(100) DEFAULT '',
  `address_no` varchar(100) DEFAULT '',
	`latitude` DECIMAL(10,8) NULL,
	`longitude` DECIMAL(11,8) NULL,
  `zip` varchar(10) DEFAULT '',
  `city` varchar(50) DEFAULT '',
  `state` varchar(50) DEFAULT '',
  `country` varchar(50) DEFAULT '',
  `post_address` varchar(100) DEFAULT '',
  `post_address_no` varchar(100) DEFAULT '',
	`post_latitude` DECIMAL(10,8) NULL,
	`post_longitude` DECIMAL(11,8) NULL,
  `post_city` varchar(50) DEFAULT '',
  `post_state` varchar(50) DEFAULT '',
  `post_country` varchar(50) DEFAULT '',
  `post_zip` varchar(10) DEFAULT '',
  `phone` varchar(30) DEFAULT '',
  `fax` varchar(30) DEFAULT '',
  `email` varchar(75) DEFAULT '',
  `homepage` varchar(100) DEFAULT '',
  `comment` text,
  `bank_no` varchar(50) DEFAULT '',
  `bank_bic` varchar(11) DEFAULT '',
  `vat_no` varchar(30) DEFAULT '',
  `invoice_email` varchar(75) DEFAULT '',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `muser_id` int(11) NOT NULL DEFAULT '0',
  `email_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_contacts`
--

DROP TABLE IF EXISTS `ab_contacts`;
CREATE TABLE IF NOT EXISTS `ab_contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL DEFAULT '0',
  `addressbook_id` int(11) NOT NULL DEFAULT '0',
  `first_name` varchar(50) NOT NULL DEFAULT '',
  `middle_name` varchar(50) NOT NULL DEFAULT '',
  `last_name` varchar(50) NOT NULL DEFAULT '',
  `initials` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(50) NOT NULL DEFAULT '',
  `suffix` varchar(50) NOT NULL DEFAULT '',
  `sex` enum('M','F') NOT NULL DEFAULT 'M',
  `birthday` date DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `email2` varchar(100) NOT NULL DEFAULT '',
  `email3` varchar(100) NOT NULL DEFAULT '',
  `company_id` int(11) NOT NULL DEFAULT '0',
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
	`latitude` DECIMAL(10,8) NULL,
	`longitude` DECIMAL(11,8) NULL,
  `comment` text,
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
  `muser_id` int(11) NOT NULL DEFAULT '0',
  `salutation` varchar(100) NOT NULL DEFAULT '',
  `email_allowed` tinyint(1) NOT NULL DEFAULT '1',
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  `go_user_id` int(11) NOT NULL DEFAULT '0',
  `photo` varchar(255) NOT NULL DEFAULT '',
  `action_date` int(11) NOT NULL DEFAULT '0',
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_default_email_templates`
--

DROP TABLE IF EXISTS `ab_default_email_templates`;
CREATE TABLE IF NOT EXISTS `ab_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_default_email_templates`
--

DROP TABLE IF EXISTS `ab_default_email_account_templates`;
CREATE TABLE IF NOT EXISTS `ab_default_email_account_templates` (
  `account_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`account_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_email_templates`
--

DROP TABLE IF EXISTS `ab_email_templates`;
CREATE TABLE IF NOT EXISTS `ab_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `acl_id` int(11) NOT NULL DEFAULT '0',
  `content` longblob NOT NULL,
  `extension` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_sent_mailings`
--

DROP TABLE IF EXISTS `ab_sent_mailings`;
CREATE TABLE IF NOT EXISTS `ab_sent_mailings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message_path` varchar(255) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  `addresslist_id` int(11) NOT NULL,
  `alias_id` int(11) NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `sent` int(11) DEFAULT '0',
	`errors` int(11) DEFAULT '0',
	`opened` int(11) DEFAULT '0',
	`campaign_id` int(11) NOT NULL DEFAULT '0',
	`campaigns_status_id` int(11) NOT NULL DEFAULT '0',
	`temp_pass` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_search_queries`
--

DROP TABLE IF EXISTS `ab_search_queries`;
CREATE TABLE IF NOT EXISTS `ab_search_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `companies` tinyint(1) NOT NULL,
  `name` varchar(32) DEFAULT NULL,
  `sql` text,
  PRIMARY KEY (`id`),
  KEY `companies` (`companies`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------



--
-- Tabelstructuur voor tabel `cf_ab_companies`
--

DROP TABLE IF EXISTS `cf_ab_companies`;
CREATE TABLE IF NOT EXISTS `cf_ab_companies` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `cf_ab_contacts`
--

DROP TABLE IF EXISTS `cf_ab_contacts`;
CREATE TABLE IF NOT EXISTS `cf_ab_contacts` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------



--
-- Tabelstructuur voor tabel `go_links_ab_companies`
--

DROP TABLE IF EXISTS `go_links_ab_companies`;
CREATE TABLE IF NOT EXISTS `go_links_ab_companies` (
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

--
-- Tabelstructuur voor tabel `go_links_ab_contacts`
--

DROP TABLE IF EXISTS `go_links_ab_contacts`;
CREATE TABLE IF NOT EXISTS `go_links_ab_contacts` (
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




--
-- Tabelstructuur voor tabel `ml_default_templates`
--

DROP TABLE IF EXISTS `ab_default_email_templates`;
CREATE TABLE IF NOT EXISTS `ab_default_email_templates` (
  `user_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_sendmailing_companies`
--

DROP TABLE IF EXISTS `ab_sent_mailing_companies`;
CREATE TABLE IF NOT EXISTS `ab_sent_mailing_companies` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT '0',
  `company_id` int(11) NOT NULL DEFAULT '0',
	`sent` tinyint(1) NOT NULL DEFAULT '0',
	`campaigns_opened` tinyint(1) NOT NULL DEFAULT '0',
	`has_error` tinyint(1) NOT NULL DEFAULT '0',
	`error_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_sent_mailing_contacts`
--

DROP TABLE IF EXISTS `ab_sent_mailing_contacts`;
CREATE TABLE IF NOT EXISTS `ab_sent_mailing_contacts` (
  `sent_mailing_id` int(11) NOT NULL DEFAULT '0',
  `contact_id` int(11) NOT NULL DEFAULT '0',
	`sent` tinyint(1) NOT NULL DEFAULT '0',
	`campaigns_opened` tinyint(1) NOT NULL DEFAULT '0',
	`has_error` tinyint(1) NOT NULL DEFAULT '0',
	`error_description` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sent_mailing_id`,`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ab_contacts_vcard_props`
--

DROP TABLE IF EXISTS `ab_contacts_vcard_props`;
CREATE TABLE IF NOT EXISTS `ab_contacts_vcard_props` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
	`parameters` varchar(1023) NOT NULL DEFAULT '',
  `value` VARCHAR( 1023 ) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
	KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


--
-- Tabelstructuur voor tabel `ab_portlet_birthdays`
--
DROP TABLE IF EXISTS `ab_portlet_birthdays`;
CREATE TABLE IF NOT EXISTS `ab_portlet_birthdays` (
  `user_id` int(11) NOT NULL,
  `addressbook_id` int(11) NOT NULL,
  PRIMARY KEY (`user_id`,`addressbook_id`)
) ENGINE=InnoDB;


--
-- Tabelstructuur voor tabel `ab_settings`
--
DROP TABLE IF EXISTS `ab_settings`;
CREATE TABLE IF NOT EXISTS `ab_settings` (
  `user_id` int(11) NOT NULL,
  `default_addressbook_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Tabelstructuur voor tabel `go_links_ab_addresslists`
--

DROP TABLE IF EXISTS `go_links_ab_addresslists`;
CREATE TABLE IF NOT EXISTS `go_links_ab_addresslists` (
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

--
-- Tabelstructuur voor tabel `ab_addresslist_group`
--

CREATE TABLE `ab_addresslist_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `ab_addresslist_group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ab_addresslist_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;