<?php
$updates["201404021715"][]="ALTER TABLE `dbx_users` CHANGE `delta_cursor` `delta_cursor` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL ;";

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `dbx_users` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `dbx_users` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';