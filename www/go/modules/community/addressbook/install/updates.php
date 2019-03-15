<?php
$updates = [];

$updates['201811272011'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};
$updates['201811272011'][] = "DROP TABLE addressbook_contact_custom_fields";

$updates['201811272011'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->run();
};

$updates['201901191547'][] = "CREATE TABLE IF NOT EXISTS `addressbook_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultAddressBookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `defaultAddressBookId` (`defaultAddressBookId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";

$updates['201901191547'][] = "ALTER TABLE `addressbook_user_settings`
  ADD CONSTRAINT `addressbook_user_settings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addressbook_user_settings_ibfk_2` FOREIGN KEY (`defaultAddressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE SET NULL;";

$updates['201903141536'][] = "CREATE TABLE `addressbook_contact_filter` (
  `id` int(11) NOT NULL,
  `name` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `createdBy` int(11) NOT NULL,
  `filter` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `aclId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";


$updates['201903141536'][] = "ALTER TABLE `addressbook_contact_filter`
  ADD PRIMARY KEY (`id`),
  ADD KEY `aclid` (`aclId`),
  ADD KEY `createdBy` (`createdBy`);";


$updates['201903141536'][] = "ALTER TABLE `addressbook_contact_filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";

$updates['201903141536'][] = "ALTER TABLE `addressbook_contact_filter`
  ADD CONSTRAINT `addressbook_contact_filter_ibfk_1` FOREIGN KEY (`aclId`) REFERENCES `core_acl` (`id`);";