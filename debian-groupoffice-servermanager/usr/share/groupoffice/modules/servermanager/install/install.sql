
--
-- Database: `groupofficecom`
--

-- --------------------------------------------------------

--
-- Table structure for table `sm_automatic_invoices`
--

DROP TABLE IF EXISTS `sm_automatic_invoices`;
CREATE TABLE IF NOT EXISTS `sm_automatic_invoices` (
  `id` int(11) NOT NULL,
  `enable_invoicing` tinyint(1) NOT NULL DEFAULT '0',
  `discount_price` double NOT NULL DEFAULT '0',
  `discount_description` varchar(255) DEFAULT 'Discount',
  `discount_percentage` double NOT NULL DEFAULT '0',
  `invoice_timespan` int(11) NOT NULL DEFAULT '1',
  `next_invoice_time` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_address` varchar(255) NOT NULL,
  `customer_address_no` varchar(10) NOT NULL,
  `customer_zip` varchar(45) NOT NULL,
  `customer_state` varchar(255) DEFAULT NULL,
  `customer_country` varchar(45) NOT NULL,
  `customer_vat` varchar(255) DEFAULT NULL,
  `customer_city` varchar(255) NOT NULL,
  `installation_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sm_automatic_invoices_sm_installations1` (`installation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_auto_email`
--

DROP TABLE IF EXISTS `sm_auto_email`;
CREATE TABLE IF NOT EXISTS `sm_auto_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  `days` int(11) NOT NULL DEFAULT '0',
  `mime` text,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sm_installations`
--

DROP TABLE IF EXISTS `sm_installations`;
CREATE TABLE IF NOT EXISTS `sm_installations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `max_users` int(11) NOT NULL,
  `trial_days` int(11) NOT NULL DEFAULT '30',
  `lastlogin` int(11) DEFAULT NULL,
  `comment` text,
  `features` varchar(255) DEFAULT NULL,
  `mail_domains` varchar(255) DEFAULT NULL,
  `admin_email` varchar(100) DEFAULT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'ignore',
  `token` varchar(100) DEFAULT NULL,
  `file_storage_usage` bigint(20) NOT NULL DEFAULT '0',
  `database_usage` BIGINT(20) NOT NULL DEFAULT '0',
  `mailbox_usage` BIGINT(20) NOT NULL DEFAULT '0',
  `quota` bigint(20) NOT NULL DEFAULT '0',
  `total_logins` int(11) NOT NULL DEFAULT '0',
  `acl_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=90 ;

-- --------------------------------------------------------

--
-- Table structure for table `sm_installation_modules`
--

DROP TABLE IF EXISTS `sm_installation_modules`;
CREATE TABLE IF NOT EXISTS `sm_installation_modules` (
  `name` varchar(100) NOT NULL,
  `installation_id` int(11) NOT NULL,
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `usercount` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`,`installation_id`),
  KEY `fk_sm_installation_modules_sm_installations1` (`installation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_installation_users`
--

DROP TABLE IF EXISTS `sm_installation_users`;
CREATE TABLE IF NOT EXISTS `sm_installation_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL DEFAULT '',
  `installation_id` int(11) NOT NULL,
  `used_modules` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `lastlogin` int(11) DEFAULT NULL,
  `enabled` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`user_id`,`installation_id`),
  KEY `fk_sm_installation_users_sm_installations1` (`installation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_installation_user_modules`
--

DROP TABLE IF EXISTS `sm_installation_user_modules`;
CREATE TABLE IF NOT EXISTS `sm_installation_user_modules` (
  `user_id` int(11) NOT NULL,
  `module_id` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`,`module_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_module_prices`
--

DROP TABLE IF EXISTS `sm_module_prices`;
CREATE TABLE IF NOT EXISTS `sm_module_prices` (
  `module_name` varchar(45) NOT NULL,
  `price_per_month` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`module_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_new_trials`
--

DROP TABLE IF EXISTS `sm_new_trials`;
CREATE TABLE IF NOT EXISTS `sm_new_trials` (
  `name` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(20) DEFAULT NULL,
  `key` varchar(100) DEFAULT NULL,
  `ctime` int(11) NOT NULL,
  PRIMARY KEY (`name`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sm_usage_history`
--

DROP TABLE IF EXISTS `sm_usage_history`;
CREATE TABLE IF NOT EXISTS `sm_usage_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ctime` int(11) NOT NULL,
  `count_users` int(11) NOT NULL DEFAULT '0',
  `database_usage` double NOT NULL DEFAULT '0',
  `file_storage_usage` double NOT NULL DEFAULT '0',
  `mailbox_usage` double NOT NULL DEFAULT '0',
  `total_logins` int(11) NOT NULL DEFAULT '0',
  `installation_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_sm_usage_history_sm_installations1` (`installation_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=70 ;

-- --------------------------------------------------------

--
-- Table structure for table `sm_user_prices`
--

DROP TABLE IF EXISTS `sm_user_prices`;
CREATE TABLE IF NOT EXISTS `sm_user_prices` (
  `max_users` int(11) NOT NULL,
  `price_per_month` double NOT NULL,
  PRIMARY KEY (`max_users`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sm_automatic_invoices`
--
ALTER TABLE `sm_automatic_invoices`
  ADD CONSTRAINT `fk_sm_automatic_invoices_sm_installations1` FOREIGN KEY (`installation_id`) REFERENCES `sm_installations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sm_installation_modules`
--
ALTER TABLE `sm_installation_modules`
  ADD CONSTRAINT `fk_sm_installation_modules_sm_installations1` FOREIGN KEY (`installation_id`) REFERENCES `sm_installations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sm_installation_users`
--
ALTER TABLE `sm_installation_users`
  ADD CONSTRAINT `fk_sm_installation_users_sm_installations1` FOREIGN KEY (`installation_id`) REFERENCES `sm_installations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `sm_usage_history`
--
ALTER TABLE `sm_usage_history`
  ADD CONSTRAINT `fk_sm_usage_history_sm_installations1` FOREIGN KEY (`installation_id`) REFERENCES `sm_installations` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
