<?php

$updates['201610281650'][] = 'SET foreign_key_checks = 0;';

$updates['201610281650'][] = 'ALTER TABLE `fb_acl` ENGINE=InnoDB;';
$updates['201610281650'][] = 'ALTER TABLE `fb_acl` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';

$updates['201610281659'][] = 'SET foreign_key_checks = 1;';

$updates['202102111524'][] = 'delete from fb_acl where user_id not in (select id from core_user);';

$updates['202102111524'][] = 'alter table fb_acl
	add constraint fb_acl_core_user_id_fk
		foreign key (user_id) references core_user (id)
			on delete cascade;';


$updates['202102111524'][] = "delete from fb_acl where acl_id not in (select id from core_acl);";

$updates['202305151438'][] = 'alter table fb_acl
    add constraint fb_acl_core_acl_id_fk
        foreign key (acl_id) references core_acl (id) on delete cascade;
';