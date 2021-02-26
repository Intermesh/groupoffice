<?php
$updates["201110311023"][]="ALTER TABLE `sync_settings` CHANGE `server_is_master` `server_is_master` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201110311023"][]="UPDATE sync_settings SET server_is_master=1";
$updates["201110311023"][]="ALTER TABLE `sync_settings` CHANGE `delete_old_events` `delete_old_events` BOOLEAN NOT NULL DEFAULT '1'";
$updates["201110311023"][]="UPDATE sync_settings SET delete_old_events=0 where delete_old_events=1";
$updates["201110311023"][]="UPDATE sync_settings SET delete_old_events=1 where delete_old_events=2";


$updates["201110311023"][]="ALTER TABLE `sync_settings` DROP `sync_private` ";

$updates["201111201453"][]="ALTER TABLE `sync_settings` CHANGE `note_category_id` `note_category_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201111201453"][]="ALTER TABLE `sync_settings` CHANGE `account_id` `account_id` INT( 11 ) NOT NULL DEFAULT '0'";
$updates["201201311433"][]="ALTER TABLE `sync_settings` CHANGE `tasklist_id` `tasklist_id` INT( 11 ) NOT NULL DEFAULT '0'";

$updates["201202291605"][]="CREATE TABLE IF NOT EXISTS `sync_calendar_user` (
  `calendar_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `default_calendar` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`calendar_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

$updates["201203011215"][]="ALTER TABLE `sync_tasklist_user` CHANGE `tasklist_id` `tasklist_id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201203011215"][]="ALTER TABLE `sync_tasklist_user` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT '0';";
$updates["201203011215"][]="ALTER TABLE `sync_tasklist_user` CHANGE `default_tasklist` `default_tasklist` tinyint(1) NOT NULL DEFAULT '0';";

$updates["201203011215"][]="ALTER TABLE `sync_addressbook_user` CHANGE `addressbook_id` `addressbook_id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201203011215"][]="ALTER TABLE `sync_addressbook_user` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT '0';";
$updates["201203011215"][]="ALTER TABLE `sync_addressbook_user` CHANGE `default_addressbook` `default_addressbook` tinyint(1) NOT NULL DEFAULT '0';";

$updates["201203011215"][]="ALTER TABLE `sync_note_categories_user` CHANGE `category_id` `category_id` int(11) NOT NULL AUTO_INCREMENT;";
$updates["201203011215"][]="ALTER TABLE `sync_note_categories_user` CHANGE `user_id` `user_id` int(11) NOT NULL DEFAULT '0';";
$updates["201203011215"][]="ALTER TABLE `sync_note_categories_user` CHANGE `default_category` `default_category` tinyint(1) NOT NULL DEFAULT '0';";

$updates["201203011330"][]="INSERT IGNORE INTO sync_calendar_user (calendar_id, user_id, default_calendar) SELECT calendar_id, user_id,1 FROM sync_settings;";

$updates["201309121639"][]="ALTER TABLE  `sync_calendar_user` ADD INDEX (  `user_id` )";
$updates["201309121639"][]="ALTER TABLE  `sync_addressbook_user` ADD INDEX (  `user_id` )";
$updates["201309121639"][]="ALTER TABLE  `sync_tasklist_user` ADD INDEX (  `user_id` )";
$updates["201309121639"][]="ALTER TABLE  `sync_note_categories_user` ADD INDEX (  `user_id` )";

$updates['201610281650'][] = 'ALTER TABLE `sync_addressbook_user` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_addressbook_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `sync_calendar_user` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_calendar_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `sync_devices` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_devices` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `sync_note_categories_user` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_note_categories_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `sync_settings` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `sync_tasklist_user` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `sync_tasklist_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['201802031500'][] = 'RENAME TABLE `sync_note_categories_user` TO `sync_user_note_book`;';

$updates['201802031500'][] = 'ALTER TABLE `sync_user_note_book` DROP INDEX `user_id`';
$updates['201802031500'][] = 'ALTER TABLE `sync_user_note_book` CHANGE `category_id` `noteBookId` INT(11) NOT NULL;';
$updates['201802031500'][] = 'ALTER TABLE `sync_user_note_book` CHANGE `user_id` `userId` INT(11) NOT NULL;';
$updates['201802031500'][] = 'ALTER TABLE `sync_user_note_book` CHANGE `default_category` `isDefault` TINYINT(1) NOT NULL DEFAULT \'0\';';

$updates['201802031500'][] = 'delete FROM `sync_user_note_book` WHERE userId not in (select id from core_user);';
$updates['201802031500'][] = 'ALTER TABLE `sync_user_note_book` ADD  CONSTRAINT `sync_user_note_book_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;';


$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` DROP INDEX `user_id`;";

$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` CHANGE `default_addressbook` `isDefault` BOOLEAN NOT NULL DEFAULT FALSE;";
$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` CHANGE `user_id` `userId` INT(11) NOT NULL;";
$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` CHANGE `addressbook_id` `addressBookId` INT(11) NOT NULL;";
//$updates['201901191547'][] = "DELETE FROM `sync_addressbook_user` where addressBookId not in (select id from addressbook_addressbook);";
$updates['201901191547'][] = "DELETE FROM `sync_addressbook_user` where userId not in (select id from core_user);";

//$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` ADD FOREIGN KEY (`addressBookId`) REFERENCES `addressbook_addressbook`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
$updates['201901191547'][] = "ALTER TABLE `sync_addressbook_user` ADD FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['202102081135'][] = function() {

	echo "Resync contacts on all devices\n";

	try {
		\GO\Sync\SyncModule::requireZPush();

		$devices = \ZPush::GetStateMachine()->GetAllDevices();
		/** @var ASDevice $device */
		foreach ($devices as $device) {
			$users = \ZPushAdmin::ListUsers($device);
			foreach ($users as $user) {
				echo "Resync $user - $device\n";
				\ZPushAdmin::ResyncFolder($user, $device, 'c/GroupOfficeContacts');
			}
		}
	} catch(Exception $e) {
		echo "Z-push not loaded: " . $e->getMessage() . "\n";
	}
};