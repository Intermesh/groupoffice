<?php

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `fav_addressbook` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fav_addressbook` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fav_calendar` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fav_calendar` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `fav_tasklist` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fav_tasklist` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';
