<?php


$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `cf_pm_presidents` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `cf_pm_presidents` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_pm_presidents` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `go_links_pm_presidents` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281650'][] = 'ALTER TABLE `pm_parties` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `pm_parties` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
$updates['201610281650'][] = 'ALTER TABLE `pm_presidents` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `pm_presidents` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';
