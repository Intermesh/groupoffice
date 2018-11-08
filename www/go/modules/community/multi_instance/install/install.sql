DROP TABLE IF EXISTS `multi_instance_instance`;
CREATE TABLE IF NOT EXISTS `multi_instance_instance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hostname` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `deletedAt` datetime DEFAULT NULL,
  `removeAt` datetime DEFAULT NULL,
  `adminDisplayName` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adminEmail` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userCount` int(11) DEFAULT NULL,
  `usersMax` int(11) DEFAULT NULL,
  `loginCount` int(11) DEFAULT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  `storageUsage` bigint(20) DEFAULT NULL,
  `storageQuota` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hostname` (`hostname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
