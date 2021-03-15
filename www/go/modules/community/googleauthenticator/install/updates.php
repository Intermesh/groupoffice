<?php
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` DROP FOREIGN KEY `googleauth_secret_user`; ";
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` ADD CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";
