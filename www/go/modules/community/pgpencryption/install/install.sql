CREATE TABLE IF NOT EXISTS `pgp_public_key`
(
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contactId` INT(11)      NOT NULL,
    `email`     VARCHAR(190) NOT NULL,
    `key`       BLOB         NOT NULL,
    `createdAt` DATETIME     NOT NULL,
    `expiresAt` DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`contactId`) REFERENCES `addressbook_contact` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `pgp_private_key`
(
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId`      INT(11)      NOT NULL,
    `email`       VARCHAR(190) NOT NULL,
    `publicKeyId` INT UNSIGNED NOT NULL UNIQUE,
    `key`         BLOB         NOT NULL,
    `createdAt`   DATETIME     NOT NULL,
    `expiresAt`   DATETIME     NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`publicKeyId`) REFERENCES `pgp_public_key` (`id`) ON DELETE CASCADE
);