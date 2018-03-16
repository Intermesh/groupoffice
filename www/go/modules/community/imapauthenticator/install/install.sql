

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `imapauth_server`
--

CREATE TABLE `imapauth_server` (
  `id` int(11) NOT NULL,
  `imapHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imapPort` int(11) NOT NULL DEFAULT '143',
  `imapEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `imapValidateCertificate` tinyint(1) NOT NULL DEFAULT '1',
  `removeDomainFromUsername` tinyint(1) NOT NULL DEFAULT '0',
  `createEmailAccount` tinyint(1) NOT NULL DEFAULT '1',
  `smtpHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtpPort` int(11) NOT NULL DEFAULT '587',
  `smtpUsername` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPassword` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpUseImapCredentials` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `imapauth_server`
--

INSERT INTO `imapauth_server` (`id`, `imapHostname`, `imapPort`, `imapEncryption`, `imapValidateCertificate`, `removeDomainFromUsername`, `createEmailAccount`, `smtpHostname`, `smtpPort`, `smtpUsername`, `smtpPassword`, `smtpUseImapCredentials`) VALUES
(1, 'localhost', 143, NULL, 1, 0, 1, 'localhost', 587, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `imapauth_server_domain`
--

CREATE TABLE `imapauth_server_domain` (
  `id` int(11) NOT NULL,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `imapauth_server_domain`
--

INSERT INTO `imapauth_server_domain` (`id`, `serverId`, `name`) VALUES
(1, 1, '*');

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
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `imapauth_server`
--
ALTER TABLE `imapauth_server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT voor een tabel `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `imapauth_server_domain`
--
ALTER TABLE `imapauth_server_domain`
  ADD CONSTRAINT `imapauth_server_domain_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `imapauth_server` (`id`) ON DELETE CASCADE;

