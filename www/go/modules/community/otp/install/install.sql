-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `otp_secret`
--

CREATE TABLE `otp_secret` (
  `userId` int(11) NOT NULL,
  `secret` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `verified` bool default false not null
) ENGINE=InnoDB;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `otp_secret`
--
ALTER TABLE `otp_secret`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `user` (`userId`);

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `otp_secret`
--
ALTER TABLE `otp_secret`
    ADD CONSTRAINT `otp_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

