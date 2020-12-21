-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `googleauth_secret`
--

CREATE TABLE `googleauth_secret` (
  `userId` int(11) NOT NULL,
  `secret` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL
) ENGINE=InnoDB;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `googleauth_secret`
--
ALTER TABLE `googleauth_secret`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `user` (`userId`);

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `googleauth_secret`
--
ALTER TABLE `googleauth_secret`
    ADD CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;