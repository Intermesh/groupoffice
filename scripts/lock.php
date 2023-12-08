<?php
// run in parallel:
// php lock.php & php lock.php
// the scripts will wait for the preceeding one to finish
require ('../www/GO.php');
go()->getDebugger()->output = true;
go()->getDebugger()->enable(true);

//echo "Start";
$lock = new \go\core\util\Lock("jmap-set-lock");
//go()->getDebugger()->debugTiming("start");
//
//for($i = 0; $i < 1000; $i++) {
//	$lock->lock();
//	$lock->unlock();
//}
//go()->getDebugger()->debugTiming("end");

$lock->lock();

echo "Lock aquired by " .getmypid(). "!\n";

// 1 sec longer than timeout
sleep(5);