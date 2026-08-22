CREATE TABLE `marketplaceserver_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('module','collection') NOT NULL DEFAULT 'module',
  `moduleName` varchar(190) DEFAULT NULL,
  `title` varchar(190) NOT NULL,
  `description` text,
  `stripePriceId` varchar(190) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `availableUntil` datetime DEFAULT NULL,
  `sortOrder` int(11) NOT NULL DEFAULT 0,
  `logoBlobId` binary(40) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `logoBlobId` (`logoBlobId`),
  CONSTRAINT `marketplaceserver_product_ibfk_1` FOREIGN KEY (`logoBlobId`) REFERENCES `core_blob` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_product_module` (
  `productId` int(11) NOT NULL,
  `moduleName` varchar(190) NOT NULL,
  PRIMARY KEY (`productId`, `moduleName`),
  CONSTRAINT `marketplaceserver_product_module_ibfk_1` FOREIGN KEY (`productId`) REFERENCES `marketplaceserver_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_release` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productId` int(11) NOT NULL,
  `moduleName` varchar(190) NOT NULL,
  `version` varchar(50) NOT NULL,
  `goVersion` varchar(20) NOT NULL,
  `changelog` text,
  `blobId` binary(40) NOT NULL,
  `publishedAt` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `productVersionBranch` (`productId`, `goVersion`, `version`),
  KEY `blobId` (`blobId`),
  KEY `moduleName` (`moduleName`),
  CONSTRAINT `marketplaceserver_release_ibfk_1` FOREIGN KEY (`blobId`) REFERENCES `core_blob` (`id`),
  CONSTRAINT `marketplaceserver_release_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `marketplaceserver_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `companyName` varchar(190) DEFAULT NULL,
  `verifiedAt` datetime DEFAULT NULL,
  `maxInstances` int(11) NOT NULL DEFAULT 1,
  `stripeCustomerId` varchar(190) DEFAULT NULL,
  `notes` text,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userId` (`userId`),
  CONSTRAINT `marketplaceserver_customer_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_api_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerId` int(11) NOT NULL,
  `tokenHash` char(64) NOT NULL,
  `name` varchar(190) DEFAULT NULL,
  `lastUsedAt` datetime DEFAULT NULL,
  `revoked` tinyint(1) NOT NULL DEFAULT 0,
  `createdAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tokenHash` (`tokenHash`),
  KEY `customerId` (`customerId`),
  CONSTRAINT `marketplaceserver_api_token_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `marketplaceserver_customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_entitlement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerId` int(11) NOT NULL,
  `productId` int(11) NOT NULL,
  `expiresAt` datetime DEFAULT NULL,
  `revokedAt` datetime DEFAULT NULL,
  `source` enum('manual','stripe','free') NOT NULL DEFAULT 'manual',
  `bindingMode` enum('seats','hostname') NOT NULL DEFAULT 'seats',
  `boundHostname` varchar(190) DEFAULT NULL,
  `stripeSubscriptionId` varchar(190) DEFAULT NULL,
  `stripePaymentIntentId` varchar(190) DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  `modifiedBy` int(11) DEFAULT NULL,
  `modifiedAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customerProduct` (`customerId`, `productId`),
  KEY `customerId` (`customerId`),
  KEY `productId` (`productId`),
  KEY `stripePaymentIntentId` (`stripePaymentIntentId`),
  KEY `stripeSubscriptionId` (`stripeSubscriptionId`),
  CONSTRAINT `marketplaceserver_entitlement_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `marketplaceserver_customer` (`id`) ON DELETE CASCADE,
  CONSTRAINT `marketplaceserver_entitlement_ibfk_2` FOREIGN KEY (`productId`) REFERENCES `marketplaceserver_product` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_instance_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerId` int(11) NOT NULL,
  `hostname` varchar(190) NOT NULL,
  `lastSeenAt` datetime DEFAULT NULL,
  `consumesSeat` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customerHost` (`customerId`, `hostname`),
  CONSTRAINT `marketplaceserver_instance_log_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `marketplaceserver_customer` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_verification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `tokenHash` char(64) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `usedAt` datetime DEFAULT NULL,
  `createdAt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tokenHash` (`tokenHash`),
  KEY `userId` (`userId`),
  CONSTRAINT `marketplaceserver_verification_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `marketplaceserver_reg_attempt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `createdAt` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `createdAt` (`createdAt`)
) ENGINE=InnoDB;

-- Append-only audit trail of marketplace activity (downloads, purchases,
-- refunds, subscription changes, registrations, verifications, grants/revokes).
-- customerId is SET NULL on customer delete so the history survives; productId
-- carries no FK so a deleted product doesn't erase past activity.
CREATE TABLE `marketplaceserver_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdAt` datetime NOT NULL,
  `type` varchar(32) NOT NULL,
  `customerId` int(11) DEFAULT NULL,
  `productId` int(11) DEFAULT NULL,
  `moduleName` varchar(190) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `hostname` varchar(190) DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `ref` varchar(190) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `customerId` (`customerId`),
  KEY `createdAt` (`createdAt`),
  CONSTRAINT `marketplaceserver_activity_ibfk_1` FOREIGN KEY (`customerId`) REFERENCES `marketplaceserver_customer` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;
