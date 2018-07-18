<?php
require(__DIR__ . "/../../vendor/autoload.php");

\go\core\App::get();
header('Content-Type: text/css');    
//header('Content-Encoding: gzip');
		
$webclient = new \go\core\webclient\Extjs3();
readfile($webclient->getCSSFile()->getPath());