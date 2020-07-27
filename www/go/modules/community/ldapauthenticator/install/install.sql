
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ldapauth_server`
--

CREATE TABLE `ldapauth_server` (
  `id` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '389',
  `encryption` enum('ssl','tls') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `ldapVerifyCertificate` BOOLEAN NOT NULL DEFAULT TRUE,
  `followReferrals` BOOLEAN NOT NULL DEFAULT TRUE,
  `protocolVersion` TINYINT UNSIGNED NOT NULL DEFAULT '3',
  `username` VARCHAR(190) NULL DEFAULT NULL,
  `password` VARCHAR(512) NULL COLLATE ascii_bin DEFAULT NULL,
  `usernameAttribute` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uid',
  `peopleDN` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ou=people,dc=example,dc=com',
  `groupsDN` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT 'ou=groups,dc=example,dc=com',
  `imapHostname` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imapPort` int(11) NOT NULL DEFAULT '143',
  `imapEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `imapValidateCertificate` tinyint(1) NOT NULL DEFAULT '1',
  `imapUseEmailForUsername` BOOLEAN NOT NULL DEFAULT FALSE,
  `loginWithEmail` tinyint(1) NOT NULL DEFAULT '0',
  `smtpHostname` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPort` int(11) NOT NULL DEFAULT '587',
  `smtpUsername` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPassword` varchar(512) COLLATE ascii_bin DEFAULT NULL,
  `smtpUseUserCredentials` tinyint(1) NOT NULL DEFAULT '0',
  `smtpEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpValidateCertificate` tinyint(1) NOT NULL DEFAULT '1',
  `syncUsers` BOOLEAN NOT NULL DEFAULT FALSE,
  `syncUsersQuery` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `syncGroups` BOOLEAN NOT NULL DEFAULT FALSE, 
  `syncGroupsQuery` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB;
--

-- Gegevens worden geëxporteerd voor tabel `ldapauth_server`
--

------------------------------------------------------

--
-- Tabelstructuur voor tabel `ldapauth_server_domain`
--

CREATE TABLE `ldapauth_server_domain` (
  `id` int(11) NOT NULL,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*'
) ENGINE=InnoDB;

--
-- Gegevens worden geëxporteerd voor tabel `ldapauth_server_domain`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ldapauth_server_group`
--

CREATE TABLE `ldapauth_server_group` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL
) ENGINE=InnoDB;

--
-- Gegevens worden geëxporteerd voor tabel `ldapauth_server_group`
--



--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `ldapauth_server`
--
ALTER TABLE `ldapauth_server`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `ldapauth_server_domain`
--
ALTER TABLE `ldapauth_server_domain`
  ADD PRIMARY KEY (`id`),
  ADD KEY `serverId` (`serverId`);

--
-- Indexen voor tabel `ldapauth_server_group`
--
ALTER TABLE `ldapauth_server_group`
  ADD PRIMARY KEY (`serverId`,`groupId`),
  ADD KEY `groupId` (`groupId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `ldapauth_server`
--
ALTER TABLE `ldapauth_server`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT voor een tabel `ldapauth_server_domain`
--
ALTER TABLE `ldapauth_server_domain`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `ldapauth_server_domain`
--
ALTER TABLE `ldapauth_server_domain`
  ADD CONSTRAINT `ldapauth_server_domain_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `ldapauth_server_group`
--
ALTER TABLE `ldapauth_server_group`
  ADD CONSTRAINT `ldapauth_server_group_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ldapauth_server_group_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE;


CREATE TABLE `ldapauth_server_group_sync` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`serverId`,`groupId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `ldapauth_server_group_sync_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ldapauth_server_group_sync_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `ldapauth_server_user_sync` (
 `serverId` int(11) NOT NULL,
 `userId` int(11) NOT NULL,
 PRIMARY KEY (`serverId`,`userId`),
 KEY `ldapauth_server_user_sync_ibfk_1` (`userId`),
 CONSTRAINT `ldapauth_server_user_sync_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
 CONSTRAINT `ldapauth_server_user_sync_ibfk_2` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
