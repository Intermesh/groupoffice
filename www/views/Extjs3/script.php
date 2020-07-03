<?php
require(__DIR__ . "/../../vendor/autoload.php");

\go\core\App::get();
$cacheFile = \go\core\App::get()->getDataFolder()->getFile('clientscripts/all.js');

$cacheFile->output(true, true, [
	'Content-Encoding' => 'gzip',
	"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s")
]);
