CREATE TABLE `marketplace_repository` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(190) NOT NULL,
  `url` varchar(512) NOT NULL,
  `package` varchar(64) DEFAULT NULL,
  `token` text,
  `publicKey` text,
  `licenseJwt` text,
  `lastSyncAt` datetime DEFAULT NULL,
  `lastError` varchar(512) DEFAULT NULL,
  `keyMismatch` tinyint(1) NOT NULL DEFAULT 0,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB;

CREATE TABLE `marketplace_repository_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `repositoryId` int(11) NOT NULL,
  `moduleName` varchar(190) NOT NULL,
  `version` varchar(50) NOT NULL,
  `downloadedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `repoModule` (`repositoryId`, `moduleName`),
  CONSTRAINT `marketplace_repository_module_ibfk_1` FOREIGN KEY (`repositoryId`) REFERENCES `marketplace_repository` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
