-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notes_note`
--

CREATE TABLE `notes_note` (
  `id` int(11) NOT NULL,
  `noteBookId` int(11) NOT NULL,
  `createdBy` int(11) NULL,
  `modifiedBy` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `content` text,
  `filesFolderId` int(11) DEFAULT NULL,
  `password` varchar(255) DEFAULT '',
  `createdAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notes_note_book`
--

CREATE TABLE `notes_note_book` (
  `id` int(11) NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `createdBy` int(11) NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `filesFolderId` int(11) DEFAULT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `notes_note_custom_fields`
--

CREATE TABLE `notes_note_custom_fields` (
  `id` int(11) NOT NULL
) ENGINE=InnoDB;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `notes_note`
--
ALTER TABLE `notes_note`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`createdBy`),
  ADD KEY `category_id` (`noteBookId`);
--
-- Indexen voor tabel `notes_note_book`
--
ALTER TABLE `notes_note_book`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclId` (`aclId`);

--
-- Indexen voor tabel `notes_note_custom_fields`
--
ALTER TABLE `notes_note_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `notes_note`
--
ALTER TABLE `notes_note`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;
--
-- AUTO_INCREMENT voor een tabel `notes_note_book`
--
ALTER TABLE `notes_note_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `notes_note`
--
ALTER TABLE `notes_note`
  ADD CONSTRAINT `notes_note_ibfk_1` FOREIGN KEY (`noteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE RESTRICT;

--
-- Beperkingen voor tabel `notes_note_book`
--
ALTER TABLE `notes_note_book`
  ADD CONSTRAINT `notes_note_book_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);

--
-- Beperkingen voor tabel `notes_note_custom_fields`
--
ALTER TABLE `notes_note_custom_fields`
  ADD CONSTRAINT `notes_note_custom_fields_ibfk_1` FOREIGN KEY (`id`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE;

CREATE TABLE `notes_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultNoteBookId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `notes_user_settings`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `defaultNoteBookId` (`defaultNoteBookId`);


ALTER TABLE `notes_user_settings`
  ADD CONSTRAINT `notes_user_settings_ibfk_1` FOREIGN KEY (`defaultNoteBookId`) REFERENCES `notes_note_book` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_user_settings_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;


CREATE TABLE `notes_note_image` (
  `noteId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `notes_note_image`
  ADD PRIMARY KEY (`noteId`,`blobId`),
  ADD KEY `blobId` (`blobId`);

ALTER TABLE `notes_note_image`
  ADD CONSTRAINT `notes_note_image_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  ADD CONSTRAINT `notes_note_image_ibfk_2` FOREIGN KEY (`noteId`) REFERENCES `notes_note` (`id`) ON DELETE CASCADE;

ALTER TABLE `notes_note_book` ADD FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;
ALTER TABLE `notes_note` ADD FOREIGN KEY (`createdBy`) REFERENCES `core_user`(`id`) ON DELETE SET NULL ON UPDATE RESTRICT;
