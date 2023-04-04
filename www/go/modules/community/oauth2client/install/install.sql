CREATE TABLE IF NOT EXISTS `oauth2client_oauth2client` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(190) NOT NULL,
    `defaultClientId` INT(11) UNSIGNED DEFAULT NULL,
    `clientId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `clientSecret` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `projectId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `oauth2client_oauth2client`
    ADD KEY `defaultClientId` (`defaultClientId`);

ALTER TABLE `oauth2client_oauth2client`
    ADD CONSTRAINT `oauth2client_oauth2client_ibfk_1` FOREIGN KEY (`defaultClientId`) REFERENCES `oauth2client_default_client` (`id`) ON DELETE CASCADE;


CREATE TABLE IF NOT EXISTS `oauth2client_default_client` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(190) NOT NULL,
    `authenticationMethod` VARCHAR(20) NOT NULL,
    `imapHost` VARCHAR(190) NOT NULL,
    `imapPort` SMALLINT UNSIGNED NOT NULL,
    `imapEncryption` VARCHAR(10) DEFAULT '',
    `smtpHost` VARCHAR(190) NOT NULL,
    `smtpPort` SMALLINT UNSIGNED NOT NULL,
    `smtpEncryption` VARCHAR(10) DEFAULT ''

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `oauth2client_default_client` (`id`,`authenticationMethod`,`name`,`imapHost`,`imapPort`,`imapEncryption`,`smtpHost`,`smtpPort`,`smtpEncryption`)
VALUES (1, 'GoogleOauth2', 'Google','imap.gmail.com',993, 'ssl','smtp.gmail.com',465, 'ssl'), (2,'Azure','Azure','outlook.office365.com',993,'ssl','smtp.office365.com',587,'tls');

CREATE TABLE IF NOT EXISTS `oauth2client_account` (
     `accountId` INT(11) NOT NULL,
     `oauth2ClientId` INT(11) UNSIGNED NOT NULL,
     `token` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `refreshToken` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `expires` INT(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `oauth2client_account` ADD INDEX (`accountId`);

ALTER TABLE `oauth2client_account` ADD PRIMARY KEY(`accountId`);

ALTER TABLE `oauth2client_account`
    ADD CONSTRAINT `oauth2client_account_ibfk_1` FOREIGN KEY (`oauth2ClientId`) REFERENCES `oauth2client_oauth2client` (`id`) ON DELETE CASCADE;

ALTER TABLE `oauth2client_account`
    ADD CONSTRAINT `oauth2client_account_ibfk_2` FOREIGN KEY (`accountId`) REFERENCES `em_accounts` (`id`) ON DELETE CASCADE;
