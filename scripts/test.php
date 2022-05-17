<?php

use go\core\http\Client;

chdir(__DIR__);
require ('../www/GO.php');

go()->setAuthState(new \go\core\auth\TemporaryState(1));

go()->getDebugger()->enable(true);
go()->getDebugger()->output = true;
go()->getDbConnection()->debug = true;
$ids = go()->getDbConnection()
	->selectSingleValue('id')
	->from('core_user')
	->where("email like '%@delete.local'")
	->all();

foreach($ids as $id)
\go\core\model\User::delete(['id' => $id]);


echo "Done\n";
