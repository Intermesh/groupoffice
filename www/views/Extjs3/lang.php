<?php

use go\core\App;
use go\core\webclient\Extjs3;
require(__DIR__ . "/../../vendor/autoload.php");
App::get();

if(isset($_GET['lang'])) {
	GO()->getLanguage()->setLanguage($_GET['lang']);
}

header('Content-Type: application/javascript; charset=utf8');
$webclient = new Extjs3();
$webclient->getLanguageJS()->output(true, true, [
	"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s")
]);
