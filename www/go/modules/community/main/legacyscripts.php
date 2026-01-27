<?php


$dir = dirname(__FILE__, 5) . DIRECTORY_SEPARATOR;
require($dir . 'GO.php');

\go\core\App::get();

use go\core\fs\File;

function readScriptsTxt(string $path, string $prefix) {
	if (file_exists($path)) {
		$data = file_get_contents($path);
		$lines = array_map('trim', explode("\n", $data));

		foreach ($lines as $line) {
			if (!empty($line)) {
				$file = new File( $prefix. $line);
				if($file->exists()) {
					yield $file;
				}
			}
		}
	}
}

$rootFolder = go()->getEnvironment()->getInstallFolder();


$scripts = [
	'Ext.namespace("GO");' .
	'GO.settings = ' . json_encode(\go\core\webclient\Extjs3::get()->clientSettings()) . ';' .
	'GO.language = "' . go()->getLanguage()->getIsoCode() . '";' .
	'GO.calltoTemplate = "' . GO::config()->callto_template . '";' .
	'GO.calltoOpenWindow = ' . (GO::config()->callto_open_window ? "true" : "false") . ';' .
	'window.name="' . GO::getId() . '";' .
	"GO.version='".go()->getVersion()."';"
];

$scripts[] = new File(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/javascript/namespaces.js');
//for t() function to auto detect module package and name
$scripts[] = "go.module='core';go.package='core';";

foreach(readScriptsTxt(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/javascript/scripts.txt', go()->getEnvironment()->getInstallFolder() . "/") as $s) {
	$scripts[] = new File($s);
}

$load_modules = GO::modules()->getAllModules(true);
if (!empty($load_modules))
foreach ($load_modules as $module) {

	$pkg = $module->package ? $module->package : "legacy";
	$scripts[] = 'Ext.ns("'.($module->package ? 'go.modules.' . $module->package . '.' . $module->name : 'GO.' . $module->name).'"); '.
		"go.module = '" . $module->name . "'; ".
		"go.package = '" . $pkg . "'; ".
		"go.Translate.setModule('" . $pkg . "', '" .$module->name . "'); ";

	$prefix = go()->getEnvironment()->getInstallPath() . "/";
	if ($module->moduleManager instanceof \go\core\Module) {
		$prefix .= dirname(str_replace("\\", "/", get_class($module->moduleManager))) . "/views/extjs3/";
		$scriptsFile = $module->moduleManager->path() . 'views/extjs3/scripts.txt';

		//fallback to old dir
		$modulePath = go()->getEnvironment()->getInstallFolder() . '/modules/' . $module->moduleManager->getName() . '/';
	} else {
		$scriptsFile = false;
		$modulePath = $module->moduleManager->path();
	}

	if (!$scriptsFile || !file_exists($scriptsFile)) {
		$scriptsFile = $modulePath . 'scripts.txt';
		if (!file_exists($scriptsFile))
			$scriptsFile = $modulePath . 'views/Extjs3/scripts.txt';

	}

	foreach(readScriptsTxt($scriptsFile, $prefix) as $s) {
		$scripts[] = new File($s);
	}
}


header("Content-Type: text/javascript");
header("Expires: " . date("D, j M Y G:i:s ", time() + 86400 * 30) . 'GMT'); //expires in 1 day
header('Cache-Control: cache');
header('Pragma: cache');

foreach($scripts as $script) {
	if ($script instanceof File) {
		echo "// " . $script->getRelativePath(go()->getEnvironment()->getInstallFolder()) . "\n";
	}
}

echo "\n\n// ----------------- //\n\n";

foreach($scripts as $script) {
	if($script instanceof File) {
		echo "\n\n// ".$script->getRelativePath(go()->getEnvironment()->getInstallFolder())."\n\n";

		echo $script->getContents();
	} else {
		echo "\n\n // inline\n\n";

		echo $script;
	}
}


