<?php


//$dir = dirname(__FILE__, 5) . DIRECTORY_SEPARATOR;
require('../../GO.php');

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


// Settings of the current user. Kept out of the minified file, which all users with the same modules share.
$userScript =
	'Ext.namespace("GO");' .
	'GO.settings = ' . json_encode(\go\core\webclient\Extjs3::get()->clientSettings()) . ';' .
	'GO.language = "' . go()->getLanguage()->getIsoCode() . '";' .
	'GO.calltoTemplate = "' . GO::config()->callto_template . '";' .
	'GO.calltoOpenWindow = ' . (GO::config()->callto_open_window ? "true" : "false") . ';' .
	'window.name="' . GO::getId() . '";' .
	"GO.version='".go()->getVersion()."';";

$scripts = [];
$scripts[] = new File(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/javascript/namespaces.js');
//for t() function to auto detect module package and name
$scripts[] = "go.module='core';go.package='core';";

foreach(readScriptsTxt(go()->getEnvironment()->getInstallFolder() . '/views/Extjs3/javascript/scripts.txt', go()->getEnvironment()->getInstallFolder() . "/") as $s) {
	$scripts[] = new File($s);
}

foreach(readScriptsTxt(go()->getEnvironment()->getInstallFolder() . '/go/core/views/extjs3/scripts.txt', go()->getEnvironment()->getInstallFolder() . "/go/core/views/extjs3/") as $s) {
	$scripts[] = new File($s);
}

$load_modules = GO::modules()->getAllModules(true);
if (!empty($load_modules))
foreach ($load_modules as $module) {

	if($module->name === "core") {
		continue;
	}

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
// The client requests this with a cache key (version, cache rebuild time and user), so it can be cached for 30 days.
// It contains the settings of the user, so proxies may not cache it.
header('Cache-Control: private, max-age=' . (86400 * 30));
// Set by the session start in go\core\App
header_remove('Pragma');
header_remove('Expires');

if(go()->getDebugger()->enabled) {
	// Unminified with the path of every file, so errors point to the right file and line
	echo "// inline\n\n" . $userScript . "\n\n";

	foreach ($scripts as $script) {
		if ($script instanceof File) {
			echo "// " . $script->getRelativePath(go()->getEnvironment()->getInstallFolder()) . "\n";
		}
	}

	echo "\n\n// ----------------- //\n\n";

	foreach ($scripts as $script) {
		if ($script instanceof File) {
			echo "\n\n// " . $script->getRelativePath(go()->getEnvironment()->getInstallFolder()) . "\n\n";

			echo $script->getContents();
		} else {
			echo "\n\n // inline\n\n";

			echo $script;
		}
	}
	exit();
}

// The scripts depend on the modules the user can use. Users with the same modules share one minified file. A cache
// rebuild, like after an upgrade or module installation, deletes the folder.
$key = md5(implode("\n", array_map(fn($script) => $script instanceof File ? $script->getPath() : $script, $scripts)));
$cacheFile = go()->getDataFolder()->getFolder('cache/clientscripts')->create()->getFile('legacy-' . $key . '.js');

if (!$cacheFile->exists()) {
	$minify = new \MatthiasMullie\Minify\JS();
	foreach ($scripts as $script) {
		// A file may end without ";", so it could be joined with the next script
		$minify->add($script instanceof File ? $script->getContents() . "\n;" : $script);
	}

	// Write to a temporary file first, so another request never reads a half written file
	$tmpFile = $cacheFile->getPath() . '.' . uniqid() . '.tmp';
	file_put_contents($tmpFile, $minify->minify());
	rename($tmpFile, $cacheFile->getPath());
}

echo $userScript . "\n";
readfile($cacheFile->getPath());
