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
