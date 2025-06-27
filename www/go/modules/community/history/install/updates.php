<?php
$updates['202102051329'][] = "ALTER TABLE `history_log_entry` ADD INDEX(`entityId`);";
$updates['202104261531'][] = "alter table history_log_entry modify entityId int default null null;";

$updates['202104261531'][] = "alter table history_log_entry
	add remoteIp varchar(50) null;";

$updates['202109101136'][] = "create index history_log_entry_createdAt_index
	on history_log_entry (createdAt);";

$updates['202111050931'][] = "update history_log_entry set removeAcl = 0;";

// user login and logout created unneeded acl's
$updates['202204131553'][] = "delete from core_acl where id in (
    select aclId
    from ( select aclId
    from history_log_entry l
        inner join core_acl a
    on a.id=l.aclId
        inner join core_entity e on e.id=l.entityTypeId
    where a.usedIn='history_log_entry.aclId' and l.removeAcl = false and clientName='User'
) as a

);";


$updates['202205101146'][] = "alter table history_log_entry
    add requestId varchar(190) default null;";


$updates['202205161600'][] = "alter table history_log_entry
    drop foreign key fk_log_entry_core_user;";


$updates['202205161600'][] = "alter table history_log_entry
    drop foreign key fk_log_entry_core_entity1;";


$updates['202205161600'][] = "update core_setting set name='deleteAfterDays', value = value * 365 where name='deleteAfterYears';";


$updates['202205161600'][] = "alter table history_log_entry
    modify entityId varchar(100) collate ascii_bin null;";

$updates['202306151226'][] = "create index history_log_entry_removeAcl_action_index
    on history_log_entry (removeAcl, action);";


$updates['202306151226'][] = "alter table history_log_entry
    drop foreign key fk_log_entry_core_acl1;";

$updates['202306151226'][] = "alter table history_log_entry
    add constraint fk_log_entry_core_acl1
        foreign key (aclId) references core_acl (id)
            on update cascade on delete set null;";


$updates['202506122116'][] = function() {
	$aclId =\go\core\model\Module::findByName("community", "history", null)->getShadowAclId();
	go()->getDbConnection()
		->update("history_log_entry", ['aclId' => $aclId], (new \go\core\db\Query())->where("aclId", "=", null))
		->execute();
};