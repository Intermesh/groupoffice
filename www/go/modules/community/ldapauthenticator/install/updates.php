<?php
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `imapHostname` `imapHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201806260910'][] = "ALTER TABLE `ldapauth_server` CHANGE `smtpHostname` `smtpHostname` VARCHAR(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;";
$updates['201807260937'][] = "ALTER TABLE `ldapauth_server` ADD `username` VARCHAR(512) COLLATE ascii_bin NULL DEFAULT NULL AFTER `encryption`, ADD `password` VARCHAR(512) COLLATE ascii_bin NULL DEFAULT NULL AFTER `username`;";
$updates['201807260937'][] = "ALTER TABLE `ldapauth_server` CHANGE `smtpPassword` `smtpPassword` VARCHAR(512) CHARACTER SET ascii COLLATE ascii_bin NULL DEFAULT NULL;";

$updates['201905241020'][] = "ALTER TABLE `ldapauth_server` CHANGE `removeDomainFromUsername` `loginWithEmail` TINYINT(1) NOT NULL DEFAULT '0';";
$updates['201905241020'][] = "ALTER TABLE `ldapauth_server` ADD `ldapVerifyCertificate` BOOLEAN NOT NULL DEFAULT TRUE AFTER `encryption`;";

$updates['201906011900'][] = "ALTER TABLE `ldapauth_server` ADD `syncUsers` BOOLEAN NOT NULL DEFAULT FALSE AFTER `smtpValidateCertificate`, ADD `syncUsersQuery` VARCHAR(190) NOT NULL DEFAULT '(objectClass=inetOrgPerson)' AFTER `syncUsers`, ADD `syncGroups` BOOLEAN NOT NULL DEFAULT FALSE AFTER `syncUsersQuery`, ADD `syncGroupsQuery` VARCHAR(190) NOT NULL DEFAULT '(objectClass=Group)' AFTER `syncGroups`;";
$updates['201906011900'][] = "CREATE TABLE `ldapauth_server_group_sync` (
  `serverId` int(11) NOT NULL,
  `groupId` int(11) NOT NULL,
  PRIMARY KEY (`serverId`,`groupId`),
  KEY `groupId` (`groupId`),
  CONSTRAINT `ldapauth_server_group_sync_ibfk_1` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ldapauth_server_group_sync_ibfk_2` FOREIGN KEY (`groupId`) REFERENCES `core_group` (`id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['201906011900'][] = "CREATE TABLE `ldapauth_server_user_sync` (
  `serverId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`serverId`,`userId`),
  KEY `ldapauth_server_user_sync_ibfk_1` (`userId`),
  CONSTRAINT `ldapauth_server_user_sync_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ldapauth_server_user_sync_ibfk_2` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";


$updates['201910220909'][] = "CREATE TABLE IF NOT EXISTS `ldapauth_server_user_sync` (
  `serverId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`serverId`,`userId`),
  KEY `ldapauth_server_user_sync_ibfk_1` (`userId`),
  CONSTRAINT `ldapauth_server_user_sync_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ldapauth_server_user_sync_ibfk_2` FOREIGN KEY (`serverId`) REFERENCES `ldapauth_server` (`id`) ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$updates['202007021707'][] = "ALTER TABLE `ldapauth_server` ADD `imapUseEmailForUsername` BOOLEAN NOT NULL DEFAULT FALSE AFTER `imapValidateCertificate`;";

$updates['202007021707'][] = "ALTER TABLE `ldapauth_server` ADD `followReferrals` BOOLEAN NOT NULL DEFAULT TRUE AFTER `ldapVerifyCertificate`, ADD `protocolVersion` TINYINT UNSIGNED NOT NULL DEFAULT '3' AFTER `followReferrals`;";