-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `comments_comment`
--

CREATE TABLE `comments_comment` (
  `id` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `categoryId` int(11) DEFAULT NULL,
	`entityId` int(11) NOT NULL,
	`entityTypeId` int(11) NOT NULL,
  `comment` text,
  `createdBy` int(11) DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `comments_category`
--
CREATE TABLE `comments_category` (
  `id` int(11) NOT NULL,
  `modSeq` int(11) NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexen voor tabel `comments_comment`
--
ALTER TABLE `comments_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modSeq` (`modSeq`),
  ADD KEY `createdBy` (`createdBy`),
  ADD KEY `entityTypeId` (`entityTypeId`),
  ADD KEY `categoryId` (`categoryId`);

--
-- Indexen voor tabel `comments_category`
--
ALTER TABLE `comments_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modSeq` (`modSeq`),
  ADD KEY `createdBy` (`createdBy`);

--
-- AUTO_INCREMENT voor een tabel `comments_comment`
--
ALTER TABLE `comments_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `comments_category`
--
ALTER TABLE `comments_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Beperkingen voor tabel `comments_comment`
--
ALTER TABLE `comments_comment`
  ADD CONSTRAINT `commentsCommentCategoryId` FOREIGN KEY (`categoryId`) REFERENCES `comments_category` (`id`),
  ADD CONSTRAINT `commentsCommentEntity` FOREIGN KEY (`entityTypeId`) REFERENCES `core_entity` (`id`);