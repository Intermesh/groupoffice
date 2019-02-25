DROP TABLE IF EXISTS `bm_bookmarks`;
CREATE TABLE IF NOT EXISTS `bm_bookmarks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  `content` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `public_icon` tinyint(1) NOT NULL DEFAULT '1',
  `open_extern` tinyint(1) NOT NULL DEFAULT '1',
  `behave_as_module` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `bm_categories`
--

DROP TABLE IF EXISTS `bm_categories`;
CREATE TABLE IF NOT EXISTS `bm_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `acl_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
	`show_in_startmenu` BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;
ALTER TABLE `bm_categories` ADD INDEX `show_in_startmenu` (`show_in_startmenu`);
-- --------------------------------------------------------