DROP TABLE IF EXISTS `bookmarks_bookmark`;
DROP TABLE IF EXISTS `bookmarks_category`;
CREATE TABLE IF NOT EXISTS `bookmarks_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdBy` int(11) NOT NULL,
  `aclId` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `aclId` (`aclId`),
  KEY `createdBy` (`createdBy`),
  CONSTRAINT `bookmarks_category_acl_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`),
  CONSTRAINT `bookmarks_category_user_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`)
) ENGINE=InnoDB ;

CREATE TABLE IF NOT EXISTS `bookmarks_bookmark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryId` int(11) NOT NULL,
  `createdBy` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `content` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `logo` blob DEFAULT NULL,
  `openExtern` tinyint(1) NOT NULL DEFAULT '1',
  `behaveAsModule` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `createdBy` (`createdBy`),
  KEY `categoryId` (`categoryId`),
  CONSTRAINT `bookmarks_category_ibfk_1` FOREIGN KEY (`categoryId`) REFERENCES `bookmarks_category` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_user_ibfk_2` FOREIGN KEY (`createdBy`) REFERENCES `core_user` (`id`)
) ENGINE=InnoDB ;