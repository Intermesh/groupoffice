
--
-- Tabelstructuur voor tabel `files_node`
--

DROP TABLE IF EXISTS `files_node`;
CREATE TABLE `files_node` (
  `id` int(11) NOT NULL,
  `storageId` int(11) NOT NULL,
  `blobId` binary(40) DEFAULT NULL COMMENT 'When blobId is NULL then deletedAt OR isDirectory need to be set\n',
  `parentId` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `ownedBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `deletedAt` datetime DEFAULT NULL,
  `deletedBy` int(11) DEFAULT NULL,
  `isDirectory` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_node_user`
--

DROP TABLE IF EXISTS `files_node_user`;
CREATE TABLE `files_node_user` (
  `nodeId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `bookmarked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `touchedAt` datetime DEFAULT NULL COMMENT 'Determine the recent touched files'
) ENGINE=InnoDB DEFAULT;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_share`
--

DROP TABLE IF EXISTS `files_share`;
CREATE TABLE `files_share` (
  `id` int(11) NOT NULL,
  `token` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenPassword` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tokenExpiresAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_storage`
--

DROP TABLE IF EXISTS `files_storage`;
CREATE TABLE `files_storage` (
  `id` int(11) NOT NULL COMMENT 'Only id''s of files_node''s that are "Folders"(blobId is NULL)',
  `aclId` int(11) NOT NULL,
  `quota` int(11) NOT NULL DEFAULT '0',
  `usage` int(11) NOT NULL DEFAULT '0',
  `ownedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_version`
--

DROP TABLE IF EXISTS `files_version`;
CREATE TABLE `files_version` (
  `id` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `files_node`
--
ALTER TABLE `files_node`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `parentId_UNIQUE` (`parentId`,`name`),
  ADD KEY `fk_files_node_core_blob1_idx` (`blobId`),
  ADD KEY `fk_files_node_files_node1_idx` (`parentId`),
  ADD KEY `fk_files_node_files_storage1_idx` (`storageId`);

--
-- Indexen voor tabel `files_node_user`
--
ALTER TABLE `files_node_user`
  ADD PRIMARY KEY (`nodeId`,`userId`),
  ADD KEY `fk_files_node_has_core_user_core_user1_idx` (`userId`),
  ADD KEY `fk_files_node_has_core_user_files_node1_idx` (`nodeId`);

--
-- Indexen voor tabel `files_share`
--
ALTER TABLE `files_share`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `files_storage`
--
ALTER TABLE `files_storage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_files_storage_files_node1_idx` (`id`),
  ADD KEY `fk_files_storage_core_acl1_idx` (`aclId`);

--
-- Indexen voor tabel `files_version`
--
ALTER TABLE `files_version`
  ADD PRIMARY KEY (`id`,`blobId`),
  ADD KEY `fk_files_version_core_blob1_idx` (`blobId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `files_node`
--
ALTER TABLE `files_node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `files_node`
--
ALTER TABLE `files_node`
  ADD CONSTRAINT `fk_files_node_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_files_node1` FOREIGN KEY (`parentId`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_files_storage1` FOREIGN KEY (`storageId`) REFERENCES `files_storage` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_node_user`
--
ALTER TABLE `files_node_user`
  ADD CONSTRAINT `fk_files_node_has_core_user_core_user1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_has_core_user_files_node1` FOREIGN KEY (`nodeId`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_share`
--
ALTER TABLE `files_share`
  ADD CONSTRAINT `fk_files_share_files_node1` FOREIGN KEY (`id`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_storage`
--
ALTER TABLE `files_storage`
  ADD CONSTRAINT `fk_files_storage_core_acl1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_storage_files_node1` FOREIGN KEY (`id`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_version`
--
ALTER TABLE `files_version`
  ADD CONSTRAINT `fk_files_version_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_version_files_node1` FOREIGN KEY (`id`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;


CREATE VIEW `files_file` AS
SELECT `files_node`.*,`core_blob`.`size` as byteSize,`core_blob`.`type` as mimeType, 'test' AS metaData
FROM `files_node`
LEFT JOIN `core_blob` ON `files_node`.`blobId` = `core_blob`.`id`
WHERE `files_node`.`isDirectory` = 0;

CREATE VIEW `files_folder` AS
SELECT `id`, `storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `comment`, `deletedAt`, `deletedBy`, `bookmarked`, `touchedAt`
FROM `files_node`
INNER JOIN `files_node_user` ON `files_node`.`id` = `files_node_user`.`nodeId`
WHERE `files_node`.`isDirectory` = 1;