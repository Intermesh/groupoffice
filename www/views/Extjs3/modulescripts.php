<?php

use go\core\App;

require(__DIR__ . "/../../vendor/autoload.php");
App::get();

$load_modules = GO::modules()->getAllModules();

$GO_SCRIPTS_JS = "";

foreach ($load_modules as $module) {
	if(!$module->enabled) {
		continue;
	}
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

Response::get()->setContentType("application/javascript; charset=utf8");
Response::get()->sendHeaders();

header("Expires: " . date("D, j M Y H:i:s", strtotime("+1 year")));
header('Cache-Control: PRIVATE');
header('Modified-At: '.date('D, j M Y H:i:s'));
header_remove('Pragma');

echo $GO_SCRIPTS_JS;
