
--
-- Tabel structuur voor tabel `su_announcements`
--

DROP TABLE IF EXISTS `su_announcements`;
CREATE TABLE IF NOT EXISTS `su_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL default '0',
  `acl_id` int(11) NOT NULL,
  `due_time` int(11) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) NOT NULL default '0',
  `title` varchar(50) default NULL,
  `content` text,
  PRIMARY KEY  (`id`),
  KEY `due_time` (`due_time`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `su_notes`
--

DROP TABLE IF EXISTS `su_notes`;
CREATE TABLE IF NOT EXISTS `su_notes` (
  `user_id` int(11) NOT NULL,
  `text` text,
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `su_rss_feeds`
--

DROP TABLE IF EXISTS `su_rss_feeds`;
CREATE TABLE IF NOT EXISTS `su_rss_feeds` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(255) default NULL,
  `summary` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB ;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `su_visible_lists`
--

CREATE TABLE IF NOT EXISTS `su_visible_lists` (
  `user_id` int(11) NOT NULL,
  `tasklist_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`tasklist_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `su_visible_calendars`
--

CREATE TABLE IF NOT EXISTS `su_visible_calendars` (
  `user_id` int(11) NOT NULL,
  `calendar_id` int(11) NOT NULL,
  PRIMARY KEY  (`user_id`,`calendar_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `su_latest_read_announcement_records`
--

DROP TABLE IF EXISTS `su_latest_read_announcement_records`;
CREATE TABLE IF NOT EXISTS `su_latest_read_announcement_records` (
  `user_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL DEFAULT '0',
	`announcement_ctime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB;


alter table su_notes
    add constraint su_notes_core_user_id_fk
        foreign key (user_id) references core_user (id)
            on delete cascade;

alter table su_rss_feeds
    add constraint su_rss_feeds_core_user_id_fk
        foreign key (user_id) references core_user (id)
            on delete cascade;

alter table su_visible_calendars
    add constraint su_visible_calendars_core_user_id_fk
        foreign key (user_id) references core_user (id)
            on delete cascade;

alter table su_visible_lists
    add constraint su_visible_lists_core_user_id_fk
        foreign key (user_id) references core_user (id)
            on delete cascade;

alter table su_latest_read_announcement_records
    add constraint su_latest_read_announcement_records_core_user_id_fk
        foreign key (user_id) references core_user (id)
            on delete cascade;

alter table su_latest_read_announcement_records
    add constraint su_latest_read_announcement_records_su_announcements_id_fk
        foreign key (announcement_id) references su_announcements (id)
            on delete cascade;