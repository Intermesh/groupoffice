<?php
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` DROP FOREIGN KEY `googleauth_secret_user`; ";
$updates['202012210922'][] = "ALTER TABLE `googleauth_secret` ADD CONSTRAINT `googleauth_secret_user` FOREIGN KEY (`userId`) REFERENCES `core_user`(`id`) ON DELETE CASCADE ON UPDATE RESTRICT;";

$updates['202107090900'][] = "insert ignore into core_acl_group select aclId, '2', 10 from core_module where package='community' and name ='googleauthenticator'";
$updates['202209191423'][] = "alter table googleauth_secret
    add verified bool default false not null;";

$updates['202209191423'][] = "update googleauth_secret
    set verified = true;";

$updates['202211211602'][] = "rename table googleauth_secret to otp_secret;";
$updates['202211211602'][] = "UPDATE core_auth_method t SET t.id = 'otpauthenticator' WHERE t.id LIKE 'googleauthenticator'";
$updates['202211211602'][] = "UPDATE core_module t SET t.name = 'otp', version=0 WHERE t.name = 'googleauthenticator';";
