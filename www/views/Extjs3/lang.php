<?php
require(__DIR__ . "/../../vendor/autoload.php");
\go\core\App::get();

if(isset($_GET['lang'])) {
	\go\core\Language::get()->setLanguage($_GET['lang']);
}

header('Content-Type: application/javascript; charset=utf8');
$webclient = new \go\core\webclient\Extjs3();
readfile($webclient->getLanguageJS()->getPath());
