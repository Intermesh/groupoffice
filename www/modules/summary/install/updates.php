<?php
$updates["201206191645"][] = "ALTER TABLE `su_announcements` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT;";
$updates["201207051232"][] = "ALTER TABLE `su_rss_feeds` CHANGE `summary` `summary` TINYINT( 1 ) NOT NULL DEFAULT '0'";

$updates["201306040852"][] = "ALTER TABLE `su_announcements` ADD `acl_id` INT NOT NULL;";
$updates["201306040853"][]='script:share_existing_announcements.php';

$updates["201408061500"][]= "CREATE TABLE IF NOT EXISTS `su_latest_read_announcement_records` (
  `user_id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL DEFAULT '0',
	`announcement_ctime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

$updates['201610281650'][] = 'ALTER TABLE `su_announcements` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_announcements` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `su_latest_read_announcement_records` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_latest_read_announcement_records` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `su_notes` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_notes` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `su_rss_feeds` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_rss_feeds` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `su_visible_calendars` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_visible_calendars` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `su_visible_lists` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `su_visible_lists` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';


$updates['202102111534'][] = "delete from su_notes where user_id not in (select id from core_user);";
$updates['202102111534'][] = "delete from su_rss_feeds where user_id not in (select id from core_user);";
$updates['202102111534'][] = "delete from su_visible_calendars where user_id not in (select id from core_user);";
$updates['202102111534'][] = "delete from su_visible_calendars where calendar_id not in (select id from cal_calendars);";


$updates['202102111534'][] = "alter table su_notes
	add constraint su_notes_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";

$updates['202102111534'][] = "alter table su_rss_feeds
	add constraint su_rss_feeds_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";

$updates['202102111534'][] = "alter table su_visible_calendars
	add constraint su_visible_calendars_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";

$updates['202102111534'][] = "CREATE TABLE IF NOT EXISTS `su_visible_lists` ( `user_id` int(11) NOT NULL, `tasklist_id` int(11) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

$updates['202102111534'][] = "alter table su_visible_lists
	add constraint su_visible_lists_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";


$updates['202102111534'][] = "delete from su_latest_read_announcement_records where user_id not in (select id from core_user);";
$updates['202102111534'][] = "delete from su_latest_read_announcement_records where announcement_id not in (select id from su_announcements);";

$updates['202102111534'][] = "alter table su_latest_read_announcement_records
	add constraint su_latest_read_announcement_records_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;";


$updates['202102111534'][] = "alter table su_latest_read_announcement_records
	add constraint su_latest_read_announcement_records_su_announcements_id_fk
		foreign key (announcement_id) references su_announcements (id)
			on delete cascade;";




