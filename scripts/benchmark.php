<?php
require("../www/vendor/autoload.php");

use go\core\App;

App::get();
go()->setCache(new \go\core\cache\None());
//
$start = go()->getDebugger()->getTimeStamp();

for($i = 0; $i < 10; $i++) {


	foreach (go()->getDatabase()->getTables() as $table) {
		echo ".";
	}
}

$end = go()->getDebugger()->getTimeStamp();

echo "\n\nTook: " . ($end-$start) ."ms\n\n";