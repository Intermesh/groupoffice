-- phpMyAdmin SQL Dump
-- version 4.6.6deb4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Gegenereerd op: 23 mrt 2018 om 10:38
-- Serverversie: 10.1.26-MariaDB-0+deb9u1
-- PHP-versie: 7.0.27-0+deb9u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `intermesh_group_office_com`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ldapauth_server`
--

CREATE TABLE `ldapauth_server` (
  `id` int(11) NOT NULL,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` int(11) NOT NULL DEFAULT '389',
  `encryption` enum('ssl','tls') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `usernameAttribute` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uid',
  `peopleDN` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ou=people,dc=example,dc=com',
  `groupsDN` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT 'ou=groups,dc=example,dc=com',
	`imapHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imapPort` int(11) NOT NULL DEFAULT '143',
  `imapEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT 'tls',
  `imapValidateCertificate` tinyint(1) NOT NULL DEFAULT '1',
  `removeDomainFromUsername` tinyint(1) NOT NULL DEFAULT '0',
  `smtpHostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtpPort` int(11) NOT NULL DEFAULT '587',
  `smtpUsername` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpPassword` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpUseUserCredentials` tinyint(1) NOT NULL DEFAULT '0',
  `smtpEncryption` enum('tls','ssl') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtpValidateCertificate` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `ldapauth_server`
--

INSERT INTO `ldapauth_server` (`id`, `hostname`, `port`, `encryption`, `usernameAttribute`, `peopleDN`, `groupsDN`) VALUES
(1, '10.0.2.2', 32769, NULL, 'uid', 'ou=people,dc=planetexpress,dc=com', '');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ldapauth_server_domain`
--

CREATE TABLE `ldapauth_server_domain` (
  `id` int(11) NOT NULL,
  `serverId` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '*'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Gegevens worden geëxporteerd voor tabel `ldapauth_server_domain`
--

INSERT INTO `ldapauth_server_domain` (`id`, `serverId`, `name`) VALUES
(1, 1, 'ldap.localhost');

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
