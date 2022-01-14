CREATE TABLE IF NOT EXISTS `oauth2client_oauth2client` (
    `accountId` INT(11) NOT NULL,
    `clientId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `clientSecret` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `projectId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `token` VARCHAR(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `refreshToken` VARCHAR(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `expires` INT(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `oauth2client_oauth2client`
    ADD PRIMARY KEY (`accountId`),
    ADD KEY `accountId` (`accountId`);

ALTER TABLE `oauth2client_oauth2client`
    ADD CONSTRAINT `oauth2_accounts_ibfk_1` FOREIGN KEY (`accountId`) REFERENCES `em_accounts` (`id`) ON DELETE CASCADE;


CREATE TABLE IF NOT EXISTS `oauth2client_default_client` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `authenticationMethod` VARCHAR(20) NOT NULL,
    `imapHost` VARCHAR(190) NOT NULL,
    `imapPort` SMALLINT UNSIGNED NOT NULL,
    `imapEncryption` VARCHAR(10) DEFAULT '',
    `smtpHost` VARCHAR(190) NOT NULL,
    `smtpPort` SMALLINT UNSIGNED NOT NULL,
    `smtpEncryption` VARCHAR(10) DEFAULT ''

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `oauth2client_default_client` (`id`,`authenticationMethod`,`imapHost`,`imapPort`,`imapEncryption`,`smtpHost`,`smtpPort`,`smtpEncryption`)
VALUES (1, 'GoogleOauth2', 'imap.gmail.com',993, 'ssl','smtp.gmail.com',465, 'ssl');

ALTER TABLE `em_accounts` ADD CONSTRAINT `em_account_defaultclt_ibfk_1` FOREIGN KEY (`default_client_id`) REFERENCES `oauth2client_default_client` (`id`) ON DELETE RESTRICT;