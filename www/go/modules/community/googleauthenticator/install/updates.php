<?php
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` DROP FOREIGN KEY `googleauth_secret_user`; ";
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` ADD CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['202107090900'][] = "insert ignore into core_acl_group select aclId, '2', 10 from core_module where package='community' and name ='googleauthenticator'";
