


-- --------------------------------------------------------



-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_devices`
--

DROP TABLE IF EXISTS `sync_devices`;
CREATE TABLE IF NOT EXISTS `sync_devices` (
  `id` int(11) NOT NULL DEFAULT '0',
  `manufacturer` varchar(50) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `software_version` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `uri` varchar(128) DEFAULT NULL,
  `UTC` enum('0','1') NOT NULL,
  `vcalendar_version` varchar(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------



-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_note_categories_user`
--

DROP TABLE IF EXISTS `sync_note_categories_user`;
CREATE TABLE IF NOT EXISTS `sync_note_categories_user` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `default_category` tinyint(1) NOT NULL DEFAULT'0',
  PRIMARY KEY (`category_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_settings`
--

DROP TABLE IF EXISTS `sync_settings`;
CREATE TABLE IF NOT EXISTS `sync_settings` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `addressbook_id` int(11) NOT NULL DEFAULT '0',
  `calendar_id` int(11) NOT NULL DEFAULT '0',
  `tasklist_id` int(11) NOT NULL DEFAULT '0',
  `note_category_id` int(11) NOT NULL DEFAULT '0',
  `account_id` int(11) NOT NULL DEFAULT '0',
  `server_is_master` tinyint(1) NOT NULL DEFAULT '1',
  `max_days_old` tinyint(4) NOT NULL DEFAULT '0',
  `delete_old_events` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_tasklist_user`
--

DROP TABLE IF EXISTS `sync_tasklist_user`;
CREATE TABLE IF NOT EXISTS `sync_tasklist_user` (
  `tasklistId` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL DEFAULT '0',
  `isDefault` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tasklistId`,`userId`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_calendar_user`
--

DROP TABLE IF EXISTS `sync_calendar_user`;
CREATE TABLE IF NOT EXISTS `sync_calendar_user` (
	`calendar_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `default_calendar` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`calendar_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------



--
-- Database: `intermesh_group_office_com`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `sync_user_note_book`
--

CREATE TABLE `sync_user_note_book` (
  `noteBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB;

--
-- Gegevens worden geëxporteerd voor tabel `sync_user_note_book`
--

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `sync_user_note_book`
--
ALTER TABLE `sync_user_note_book`
  ADD PRIMARY KEY (`noteBookId`,`userId`),
  ADD KEY `user` (`userId`);

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `sync_user_note_book`
--
ALTER TABLE `sync_user_note_book`
  ADD CONSTRAINT `sync_user_note_book_user` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;




CREATE TABLE `sync_addressbook_user` (
  `addressBookId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `isDefault` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


ALTER TABLE `sync_addressbook_user`
  ADD PRIMARY KEY (`addressbookId`,`userId`),
  ADD KEY `userId` (`userId`);



ALTER TABLE `sync_addressbook_user`
  ADD CONSTRAINT `sync_addressbook_user_ibfk_1` FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sync_addressbook_user_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;


alter table sync_tasklist_user
    add constraint sync_tasklist_user_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade;

alter table sync_tasklist_user
	add constraint sync_tasklist_user_tasks_tasklist_id_fk
		foreign key (tasklistId) references tasks_tasklist (id)
			on delete cascade;