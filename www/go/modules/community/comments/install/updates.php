<?php

$updates['201813281400'][] = 'RENAME TABLE `co_comments` TO `comments_comment`;';

$updates['201813281400'][] = 'ALTER TABLE `comments_comment` CHANGE `category_id` `categoryId` INT(11) DEFAULT NULL;';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` CHANGE `user_id` `createdBy` INT(11) DEFAULT NULL;';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` CHANGE `model_id` `entityId` INT(11) NOT NULL;';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` CHANGE `model_type_id` `entityTypeId` INT(11) NOT NULL;';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` ADD `modSeq` INT NOT NULL AFTER `id`, ADD INDEX (`modSeq`);';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` ADD `modifiedBy` INT(11) NOT NULL DEFAULT \'0\',ADD `createdAt` DATETIME NULL DEFAULT NULL AFTER `modifiedBy`, ADD `modifiedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`, ADD `deletedAt` DATETIME NULL DEFAULT NULL AFTER `createdAt`;';
$updates['201813281400'][] = 'UPDATE comments_comment SET createdAt = from_unixtime(ctime), modifiedAt = from_unixtime(mtime);';
$updates['201813281400'][] = 'ALTER TABLE `comments_comment` DROP `ctime`, DROP `mtime`;';

//TODO:::

/**
 * 
 * 
 * --
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
 * 
 */

//
//
//-- phpMyAdmin SQL Dump
//-- version 4.5.4.1deb2ubuntu2
//-- http://www.phpmyadmin.net
//--
//-- Host: localhost
//-- Gegenereerd op: 11 apr 2018 om 12:11
//-- Serverversie: 5.7.21-0ubuntu0.16.04.1
//-- PHP-versie: 7.0.28-0ubuntu0.16.04.1
//
//SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
//SET time_zone = "+00:00";
//
//--
//-- Database: `go63_localhost`
//--
//
//-- --------------------------------------------------------
//
//--
//-- Tabelstructuur voor tabel `comments_settings`
//--
//
//CREATE TABLE `comments_settings` (
//  `userId` int(11) NOT NULL,
//  `enableQuickAdd` tinyint(1) NOT NULL DEFAULT '1'
//) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
//
//--
//-- Indexen voor geëxporteerde tabellen
//--
//
//--
//-- Indexen voor tabel `comments_settings`
//--
//ALTER TABLE `comments_settings`
//  ADD PRIMARY KEY (`userId`);
//
//--
//-- Beperkingen voor geëxporteerde tabellen
//--
//
//--
//-- Beperkingen voor tabel `comments_settings`
//--
//ALTER TABLE `comments_settings`
//  ADD CONSTRAINT `comments_settings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE;

