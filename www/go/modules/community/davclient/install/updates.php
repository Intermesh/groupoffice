<?php

$updates['202502040946'][] = 'ALTER TABLE `davclient_davaccount` CHANGE COLUMN `password` `password` VARCHAR(255) NULL DEFAULT NULL ;';
$updates['202503111342'][] = function(){
	\go\modules\community\davclient\cron\RefreshDav::install("*/5 * * * *");
};

$updates['202506161040'][] = "alter table davclient_calendar
    add lastSync DATETIME null,
    add lastError text null";

$updates['202602031051'][] = "alter table davclient_davaccount
    add verifySSL bool default true not null;";

$updates['202610191200'][] = "alter table davclient_davaccount add ownerId int null after aclId;";
// owner is the creator of the first synchronised calendar
$updates['202610191200'][] = "update davclient_davaccount a
    join davclient_calendar dc on dc.davaccountId = a.id
        and dc.id = (select min(dc2.id) from davclient_calendar dc2 where dc2.davaccountId = a.id)
    join calendar_calendar c on c.id = dc.id
    set a.ownerId = c.createdBy
    where c.createdBy in (select id from core_user);";
$updates['202610191200'][] = "update davclient_davaccount set ownerId = 1 where ownerId is null;"; // no calendars: fall back to admin
$updates['202610191200'][] = "alter table davclient_davaccount modify ownerId int not null,
    add constraint fk_davclient_davaccount_core_user foreign key (ownerId) references core_user (id);";
