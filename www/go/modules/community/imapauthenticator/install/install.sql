
CREATE TABLE `imapauth_server` (
  `id` int(11) NOT NULL,
  `imapHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imapPort` int(11) NOT NULL DEFAULT '143',
  `imapEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `imapValidateCertificate` tinyint(1) NOT NULL DEFAULT '1',
  `removeDomainFromUsername` tinyint(1) NOT NULL DEFAULT '0',
  `smtpHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtpPort` int(11) NOT NULL DEFAULT '587',
  `smtpUsername` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPassword` varchar(512) COLLATE ascii_bin DEFAULT NULL,
  `smtpUseUserCredentials` tinyint(1) NOT NULL DEFAULT '0',
  `smtpEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpValidateCertificate` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `imapauth_server_domain`
--

CREATE TABLE `imapauth_server_domain` (
  `id` int(11) NOT NULL,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `imapauth_server_group`
--

CREATE TABLE `imapauth_server_group` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `imapauth_server_group`
--



--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `imapauth_server`
--
ALTER TABLE `imapauth_server`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serverId` (`serverId`);

--
-- Indexen voor tabel `imapauth_server_group`
--
ALTER TABLE `imapauth_server_group`
  ADD PRIMARY KEY (`serverId`,`groupId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `imapauth_server`
--
ALTER TABLE `imapauth_server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT voor een tabel `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  ADD CONSTRAINT `imapauth_server_domain_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE;

ALTER TABLE `imapauth_server_group` ADD FOREIGN KEY (`serverId`) REFERENCES `imapauth_server`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT; ALTER TABLE `imapauth_server_group` ADD FOREIGN KEY (`groupId`) REFERENCES `core_group`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;