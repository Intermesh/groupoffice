<?php
$updates = [];

$updates['201811272011'][] = function() {
	\go\core\db\Utils::runSQLFile(\GO()->getEnvironment()->getInstallFolder()->getFile("go/modules/community/addressbook/install/install.sql"));
};
$updates['201811272011'][] = "DROP TABLE addressbook_contact_custom_fields";

$updates['201904021547'][] = function() {
	$m = new go\modules\community\addressbook\install\Migrate63to64();
	$m->run();
};

$updates['201904021547'][] = "CREATE TABLE IF NOT EXISTS `addressbook_user_settings` (
  `userId` int(11) NOT NULL,
  `defaultAddressBookId` int(11) DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `defaultAddressBookId` (`defaultAddressBookId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;";

//$updates['201901191547'][] = "ALTER TABLE `addressbook_user_settings`
//  ADD CONSTRAINT `addressbook_user_settings_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`) ON DELETE CASCADE,
//  ADD CONSTRAINT `addressbook_user_settings_ibfk_2` FOREIGN KEY (`defaultAddressBookId`) REFERENCES `addressbook_addressbook` (`id`) ON DELETE SET NULL;";






// bestgroen ab -4 in core_module !