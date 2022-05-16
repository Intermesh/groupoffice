<?php

use go\core\http\Client;

chdir(__DIR__);
require ('../www/GO.php');

go()->setAuthState(new \go\core\auth\TemporaryState(1));

ini_set("memory_limit", "256M");
//ini_set("max_execution_time", 20);

$ids = [];
$pid = getmypid();
for($i = 0; $i<1000; $i++) {
	echo $pid.':'. \go\core\model\User::entityType()->getHighestModSeq() .':'.$i ."\n";
	$user = new \go\core\model\User();
	$user->username = $user->displayName = uniqid();
	$user->email = $user->username.'@delete.local';
	if (!$user->save()) {
		throw new \go\core\orm\exception\SaveException($user);
	}

	$ids[] = $user->id;

//	\go\core\orm\EntityType::push();

}


echo "Deleting the users now\n";

\go\core\model\User::delete(['id' => $ids]);






//$fakeChanges = [];
//
//for($i = 0; $i > -1000; $i--) {
//	$fakeChanges[] = [$i, null, false];
//}
//foreach(\go\core\orm\EntityType::findAll() as $entityType) {
//	$entityType->changes($fakeChanges);
//}
//
//\go\core\orm\EntityType::push();
//go()->getDebugger()->output = true;
//
//go()->getDebugger()->debugTiming("end");

echo "Done\n";

