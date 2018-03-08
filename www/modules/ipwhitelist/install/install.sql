-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `wl_ip_addresses`
--

DROP TABLE IF EXISTS `wl_ip_addresses`;
CREATE TABLE IF NOT EXISTS `wl_ip_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
	`group_id` int(11) NOT NULL,
  `ip_address` varchar(64) NOT NULL,
  `description` varchar(64) NOT NULL,
  `ctime` int(11) NOT NULL DEFAULT '0',
	`mtime` int(11) NOT NULL DEFAULT '0',
	`user_id` int(11) NOT NULL DEFAULT '0',
	`muser_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY `id` (`id`),
	KEY `group_id` (`group_id`),
	KEY `user_id` (`user_id`),
	KEY `muser_id` (`muser_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `wl_enabled_groups`
--

DROP TABLE IF EXISTS `wl_enabled_groups`;
CREATE TABLE IF NOT EXISTS `wl_enabled_groups` (
	`group_id` int(11) NOT NULL,
	PRIMARY KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;