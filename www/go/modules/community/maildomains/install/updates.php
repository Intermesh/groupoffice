<?php
use go\modules\community\maildomains\install\Migrator;

$updates['202403281030'][] = function() {

	if(\go\core\model\Module::isInstalled('legacy', 'postfixadmin')) {
		$m = new Migrator();
		$m->migrate();
	}
};