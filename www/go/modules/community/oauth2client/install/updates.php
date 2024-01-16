<?php

use go\modules\community\oauth2client\model\Authenticator;

$updates['202208031450'][] = "INSERT INTO `oauth2client_default_client` (`name`,`authenticationMethod`,`imapHost`,`imapPort`,`imapEncryption`,`smtpHost`,`smtpPort`,`smtpEncryption`) VALUES
	 ('Azure','Azure','outlook.office365.com',993,'ssl','smtp.office365.com',587,'tls');";
$updates['202208041158'][] = "ALTER TABLE `oauth2client_account` MODIFY COLUMN `token` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL NULL;";
$updates['202208110845'][] = "ALTER TABLE `oauth2client_account` MODIFY COLUMN `refreshToken` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL NULL;";

$updates['202311051328'][] = "alter table oauth2client_oauth2client
    add openId bool default false;";

$updates['202311051328'][] = "create index oauth2client_oauth2client_openId_index
    on oauth2client_oauth2client (openId);";


$updates['202311271619'][] = "create table if not exists oauth2client_openid_user
(
    userId   int          not null
        primary key,
    clientId int unsigned not null,
    constraint oauth2client_openid_user_core_user_id_fk
        foreign key (userId) references core_user (id)
            on delete cascade,
    constraint oauth2client_openid_user_oauth2client_oauth2client_id_fk
        foreign key (clientId) references oauth2client_oauth2client (id)
            on delete cascade
);

";

$updates['202311271619'][] = function() {
	Authenticator::register();
};