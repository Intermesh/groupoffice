<?php

use go\core\webclient\Extjs3;

require(__DIR__ . "/../../vendor/autoload.php");

\go\core\App::get();
		
$webclient = Extjs3::get();
$webclient->getCSSFile()->output(true, true, [
	"Expires" => (new DateTime("1 year"))->format("D, j M Y H:i:s")
]);
