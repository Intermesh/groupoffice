<?php
require(__DIR__ . "/../../vendor/autoload.php");

\go\core\App::get();

header('Content-Type: application/javascript');
if(!GO()->getDebugger()->enabled) {
	header('Content-Encoding: gzip');
	$cacheFile = \go\core\App::get()->getDataFolder()->getFolder('clientscripts')->create()->getFile('all.js');
} else
{
	$cacheFile = \go\core\App::get()->getTmpFolder()->getFile('debug.js');
}


readfile($cacheFile->getPath());
