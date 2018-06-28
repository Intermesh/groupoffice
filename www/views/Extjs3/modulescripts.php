<?php

use go\core\App;

require(__DIR__ . "/../../vendor/autoload.php");
App::get();


header('Content-Type: application/javascript; charset=utf8');
$load_modules = GO::modules()->getAllModules();

$GO_SCRIPTS_JS = "";

foreach ($load_modules as $module) {
	if (file_exists($module->moduleManager->path() . 'scripts.inc.php')) {
		require($module->moduleManager->path() . 'scripts.inc.php');
	}
	if (file_exists($module->moduleManager->path() . 'views/Extjs3/scripts.inc.php')) {
		require($module->moduleManager->path() . 'views/Extjs3/scripts.inc.php');
	}
	if (file_exists($module->moduleManager->path() . 'views/extjs3/scripts.inc.php')) {
		require($module->moduleManager->path() . 'views/extjs3/scripts.inc.php');
	}
}

echo $GO_SCRIPTS_JS;
