<?php
$updates['202603021502'][] = 'ALTER TABLE `em_folders_favorites` ADD `userId` INT(11) NOT NULL AFTER `id`;';
$updates['202603021502'][] = 'ALTER TABLE `em_folders_favorites` ADD CONSTRAINT `em_folders_favorites_user_id` FOREIGN KEY (`userId`) REFERENCES `core_user` (`id`);';