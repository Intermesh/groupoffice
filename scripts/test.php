<?php

use go\core\http\Client;

chdir(__DIR__);
require ('../www/GO.php');

go()->setAuthState(new \go\core\auth\TemporaryState(1));

//go()->getDebugger()->enable(true);
//go()->getDebugger()->output = true;

$ids = go()->getDbConnection()
	->select('id')
	->from('core_user')
	->orderBy(['lastLogin'=>'desc'])
	->limit(800)
	->where('id > 1')
	->all();

$i = 0;
while(true) {
	echo $i++;
	echo "\n";
	go()->getDbConnection()->beginTransaction();
	\go\core\model\User::delete(['id' => $ids]);
	go()->getDbConnection()->rollBack();
	\go\core\orm\EntityType::push();
}

foreach($ids as $id) {
	\go\core\model\User::delete(['id' => $id]);
}


echo "Done\n";

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

//	$ids[] = $user->id;

	\go\core\model\User::delete(['id' => $user->id]);

//	\go\core\orm\EntityType::push();

}


echo "Done\n";


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

