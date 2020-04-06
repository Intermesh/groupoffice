--
-- Tabelstructuur voor tabel `dav_events`
--

DROP TABLE IF EXISTS `dav_events`;
CREATE TABLE IF NOT EXISTS `dav_events` (
  `id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `data` text NOT NULL,
  `uri` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `dav_tasks`
--

DROP TABLE IF EXISTS `dav_tasks`;
CREATE TABLE IF NOT EXISTS `dav_tasks` (
  `id` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `data` text NOT NULL,
  `uri` varchar(512) CHARACTER SET ascii COLLATE ascii_bin NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `uri` (`uri`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `dav_calendar_changes`;
CREATE TABLE dav_calendar_changes (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB;