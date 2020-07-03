<?php

use go\core\App;
use go\core\webclient\Extjs3;

require(__DIR__ . "/../../vendor/autoload.php");

App::get();


$theme = $_GET['theme'] ?? 'Paper';
$webclient = Extjs3::get();
$webclient->getCSSFile($theme)->output(true, true, [
	"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s")
]);
