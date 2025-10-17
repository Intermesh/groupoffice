<?php
use go\modules\community\maildomains\install\Migrator;

$updates['202403281030'][] = function() {

	if(\go\core\model\Module::isInstalled('legacy', 'postfixadmin')) {
		$m = new Migrator();
		$m->migrate();
	}
};


$updates['202510171328'][] = "UPDATE community_maildomains_mailbox SET maildir = SUBSTRING(maildir, 1, LENGTH(maildir) - 1), homedir = SUBSTRING(homedir, 1, LENGTH(homedir) - 1)";
