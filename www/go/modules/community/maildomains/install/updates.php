<?php
use go\modules\community\maildomains\install\Migrator;

$updates['202403281030'][] = function() {

	if(\go\core\model\Module::isInstalled('legacy', 'postfixadmin')) {
		$m = new Migrator();
		$m->migrate();
	}
};


$updates['202510171328'][] = "UPDATE community_maildomains_mailbox SET maildir = SUBSTRING(maildir, 1, LENGTH(maildir) - 1), homedir = SUBSTRING(homedir, 1, LENGTH(homedir) - 1)";


$updates['202611101507'][] = "update community_maildomains_alias set active=1 where active >1;";

$updates['202611161200'][] = "alter table community_maildomains_domain add defaultAutoExpunge varchar(20) default '30d' not null comment 'Default autoExpunge for new mailboxes' after defaultQuota;";
$updates['202611161200'][] = "alter table community_maildomains_domain add defaultFts tinyint(1) default 0 not null comment 'Default full text search for new mailboxes' after defaultAutoExpunge;";
