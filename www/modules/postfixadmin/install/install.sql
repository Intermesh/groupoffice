-- phpMyAdmin SQL Dump
-- version 2.6.0-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generatie Tijd: 20 Aug 2008 om 15:59
-- Server versie: 5.0.32
-- PHP Versie: 5.2.0-8+etch11
-- 
-- Database: `servermanager`
-- 

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `pa_aliases`
--

DROP TABLE IF EXISTS `pa_aliases`;
CREATE TABLE IF NOT EXISTS `pa_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `address` varchar(190) default NULL,
  `goto` text,
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`),
  unique `address` (`address`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Aliases';

-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `pa_domains`
--

DROP TABLE IF EXISTS `pa_domains`;
CREATE TABLE IF NOT EXISTS `pa_domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `domain` varchar(190) default NULL,
  `description` varchar(255) default NULL,
  `max_aliases` int(10) NOT NULL default '0',
  `max_mailboxes` int(10) NOT NULL default '0',
  `total_quota` bigint(20) NOT NULL default '0',
  `default_quota` bigint(20) NOT NULL default '0',
  `transport` VARCHAR( 255 ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'virtual',
  `backupmx` tinyint(1) NOT NULL default '0',
  `ctime` int(11) NOT NULL default '0',
  `mtime` int(11) NOT NULL default '0',
  `active` BOOLEAN NOT NULL DEFAULT '1',
  `acl_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  unique `domain` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Domains';


-- --------------------------------------------------------

--
-- Tabel structuur voor tabel `pa_mailboxes`
--

DROP TABLE IF EXISTS `pa_mailboxes`;
CREATE TABLE IF NOT EXISTS `pa_mailboxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain_id` int(11) NOT NULL,
  `go_installation_id` varchar(50) default NULL,
  `username` varchar(190) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `maildir` varchar(255) default NULL,
	`homedir` VARCHAR(255) default NULL,
  `quota` bigint(20) NOT NULL default '0',
  `ctime` int(11) NOT NULL,
  `mtime` int(11) NOT NULL,
  `active` BOOLEAN NOT NULL DEFAULT '1',
  `usage` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  unique `username` (`username`),
  KEY `go_installation_id` (`go_installation_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Postfix Admin - Virtual Mailboxes';

