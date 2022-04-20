<?php
// run in parallel:
// php lock.php & php lock.php
// the scripts will wait for the preceeding one to finish
require ('../www/GO.php');
go()->getDebugger()->output = true;
go()->getDebugger()->enable(true);


$lock = new \go\core\util\Lock("Test");
$lock->lock();

for($i = 0; $i < 2; $i++) {
	echo $i ."\n";
	sleep(1);
}

$lock->unlock();