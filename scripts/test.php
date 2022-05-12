<?php

use go\core\http\Client;

chdir(__DIR__);
require ('../www/GO.php');

go()->setAuthState(new \go\core\auth\TemporaryState(1));

$pid = getmypid();
for($i = 0; $i<10; $i++) {
	echo $pid.':'. \go\core\model\User::entityType()->getHighestModSeq() .':'.$i ."\n";
	$user = new \go\core\model\User();
	$user->username = $user->displayName = uniqid();
	$user->email = $user->username.'@delete.local';
	if (!$user->save()) {
		throw new \go\core\orm\exception\SaveException($user);
	}

	\go\core\model\User::delete($user->primaryKeyValues());


	\go\core\orm\EntityType::push();


}

