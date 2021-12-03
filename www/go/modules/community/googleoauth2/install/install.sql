CREATE TABLE IF NOT EXISTS `oauth2_accounts` (
    `accountId` INT(11) NOT NULL,
    `clientId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `clientSecret` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
    `projectId` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `oauth2_accounts`
    ADD KEY `accountId` (`accountId`);

ALTER TABLE `oauth2_accounts`
    ADD CONSTRAINT `oauth2_accounts_ibfk_1` FOREIGN KEY (`accountId`) REFERENCES `em_accounts` (`id`) ON DELETE CASCADE;
