CREATE TABLE `bookmarks_category` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `createdBy` int(11) DEFAULT NULL,
    `aclId` int(11) NOT NULL,
    `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `aclId` (`aclId`),
    KEY `createdBy` (`createdBy`),
    CONSTRAINT `bookmarks_category_acl_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
    CONSTRAINT `bookmarks_category_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookmarks_bookmark` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `categoryId` int(11) NOT NULL,
    `createdBy` int(11) DEFAULT NULL,
    `name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `logo` BINARY(40) DEFAULT NULL,
    `openExtern` tinyint(1) NOT NULL DEFAULT 1,
    `behaveAsModule` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `createdBy` (`createdBy`),
    KEY `categoryId` (`categoryId`),
    KEY `core_blob_bookmark_logo_idx` (`logo`),
    CONSTRAINT `bookmarks_bookmark_ibfk_1` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`) ON DELETE SET NULL,
    CONSTRAINT `bookmarks_bookmark_ibfk_2` FOREIGN KEY (`categoryId`) REFERENCES `bookmarks_category` (`id`),
    CONSTRAINT `core_blob_bookmark_logo` FOREIGN KEY (`logo`) REFERENCES `core_blob` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;