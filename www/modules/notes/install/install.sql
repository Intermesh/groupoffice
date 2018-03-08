--
-- Tabelstructuur voor tabel `cf_no_notes`
--

DROP TABLE IF EXISTS `cf_no_notes`;
CREATE TABLE IF NOT EXISTS `cf_no_notes` (
  `model_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `go_links_no_notes`
--

DROP TABLE IF EXISTS `go_links_no_notes`;
CREATE TABLE IF NOT EXISTS `go_links_no_notes` (
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



-- Tabelstructuur voor tabel `no_categories`
--

DROP TABLE IF EXISTS `no_categories`;
CREATE TABLE IF NOT EXISTS `no_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `no_notes`
--

DROP TABLE IF EXISTS `no_notes`;
CREATE TABLE IF NOT EXISTS `no_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `ctime` int(11) NOT NULL DEFAULT '0',
  `mtime` int(11) NOT NULL DEFAULT '0',
	`muser_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(100) DEFAULT NULL,
  `content` text,
  `files_folder_id` int(11) NOT NULL DEFAULT '0',
	`password` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------