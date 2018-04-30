
--
-- Tabelstructuur voor tabel `files_node`
--

DROP TABLE IF EXISTS `files_node`;
CREATE TABLE `files_node` (
  `id` int(11) NOT NULL,
  `storageId` int(11) NOT NULL,
  `blobId` binary(40) DEFAULT NULL COMMENT 'When blobId is NULL then deletedAt OR isDirectory need to be set\n',
  `parentId` int(11) NOT NULL,
  `name` varchar(190) NOT NULL,
  `createdAt` datetime NOT NULL,
  `modifiedAt` datetime NOT NULL,
  `ownedBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `deletedAt` datetime DEFAULT NULL,
  `deletedBy` int(11) DEFAULT NULL,
  `aclId` int(11) NOT NULL,
  `isLocked` tinyint(11) NOT NULL DEFAULT 0,
  `isDirectory` tinyint(1) UNSIGNED NOT NULL DEFAULT '1',
  `token` binary(40) DEFAULT NULL,
  `tokenExpiresAt` datetime DEFAULT NULL
) ENGINE=InnoDB;

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
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_storage`
--

DROP TABLE IF EXISTS `files_storage`;
CREATE TABLE `files_storage` (
  `id` int(11) NOT NULL COMMENT 'Only id''s of files_node''s that are "Folders"(blobId is NULL)',
  `quota` int(11) NOT NULL DEFAULT '0',
  `usage` int(11) NOT NULL DEFAULT '0',
  `userId` int(11) DEFAULT NULL
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `files_version`
--

DROP TABLE IF EXISTS `files_version`;
CREATE TABLE `files_version` (
  `id` int(11) NOT NULL,
  `nodeId` int(11) NOT NULL,
  `blobId` binary(40) NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy` int(11) DEFAULT NULL
) ENGINE=InnoDB;

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

-- Indexen voor tabel `files_storage`
--
ALTER TABLE `files_storage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_files_storage_files_node1_idx` (`id`),
	ADD UNIQUE KEY `userId_UNIQUE` (`userId`);

--
-- Indexen voor tabel `files_version`
--
ALTER TABLE `files_version`
  ADD PRIMARY KEY (`id`,`blobId`),
  ADD KEY `fk_files_version_core_blob1_idx` (`blobId`),
  ADD KEY `fk_files_version_core_node1_idx` (`nodeId`);

ALTER TABLE `files_version`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT voor een tabel `files_node`
--
ALTER TABLE `files_node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `files_storage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Beperkingen voor tabel `files_node`
--
ALTER TABLE `files_node`
  ADD CONSTRAINT `fk_files_node_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_files_node1` FOREIGN KEY (`parentId`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_files_storage1` FOREIGN KEY (`storageId`) REFERENCES `files_storage` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

ALTER TABLE `files_version`
  ADD CONSTRAINT `fk_files_node_files_version1` FOREIGN KEY (`nodeId`) REFERENCES `files_node` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
--
-- Beperkingen voor tabel `files_node_user`
--
ALTER TABLE `files_node_user`
  ADD CONSTRAINT `fk_files_node_has_core_user_core_user1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_node_has_core_user_files_node1` FOREIGN KEY (`nodeId`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_storage`
--
ALTER TABLE `files_storage`
  ADD CONSTRAINT `fk_files_storage_core_user1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Beperkingen voor tabel `files_version`
--
ALTER TABLE `files_version`
  ADD CONSTRAINT `fk_files_version_core_blob1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_files_version_files_node1` FOREIGN KEY (`id`) REFERENCES `files_node` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;


-- first root node (must exist)
SET foreign_key_checks = 0;
INSERT INTO `files_node` (`id`, `storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `comment`, `aclId`, `isLocked`, `isDirectory`) 
  VALUES (-1, 0, 0, 'root', NOW(), NOW(), 1, 'Disk root', 0, 1, 1);
UPDATE `files_node` SET id = 0 WHERE id = -1;
SET foreign_key_checks = 1;

-- Global storage quota for GO
INSERT INTO `files_storage` (`id`, `quota`, `usage`, `userId`) VALUES ('1', '1073741824', '0', NULL);

-- demo data
INSERT INTO `files_storage` (`id`, `quota`, `usage`, `userId`) VALUES ('2', '1073741824', '0', 1);
INSERT INTO `files_node` (`storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `aclId`, `isDirectory`) 
VALUES ('2', '0', 'Admin home', now(), now(), '1', '1', '1', '1');
INSERT INTO `files_node` (`storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `aclId`, `isLocked`) VALUES ('2', '1', 'first folder', now(), now(), '1', '1', '1', '0');
INSERT INTO `files_node` (`storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `aclId`, `isLocked`) VALUES ('2', '1', 'second folder', now(), now(), '1', '1', '1', '0');
INSERT INTO `files_node` (`storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `aclId`, `isLocked`) VALUES ('2', '1', 'thirth folder', now(), now(), '1', '1', '1', '0');
INSERT INTO `files_node` (`storageId`, `parentId`, `name`, `createdAt`, `modifiedAt`, `ownedBy`, `modifiedBy`, `aclId`, `isLocked`) VALUES ('2', '3', 'sub of secondr', now(), now(), '1', '1', '1', '0');


CREATE VIEW `files_nodeview` AS
SELECT n.*, b.size as byteSize, b.type as contentType FROM files_node n LEFT JOIN core_blob b ON n.blobId = b.id; 