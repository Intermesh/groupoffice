<?php
$updates['202012210922'][] = "ALTER TABLE `otp_secret` DROP FOREIGN KEY `otp_secret_user`; ";
$updates['202012210922'][] = "ALTER TABLE `otp_secret` ADD CONSTRAINT `otp_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['202107090900'][] = "insert ignore into core_acl_group select aclId, '2', 10 from core_module where package='community' and name ='otp'";

$updates['202209191423'][] = "alter table otp_secret
    add verified bool default false not null;";

$updates['202209191423'][] = "update otp_secret
    set verified = true;";