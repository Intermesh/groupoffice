<?php

//TODO: build in memory to skip duplicate strings

use go\core\App;
use go\core\cli\State;
use go\core\Environment;
use go\core\fs\File;
use go\core\Language;

require(__DIR__ . "/vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

GO()->getLanguage()->setLanguage("nl");

//for checking arrays() in english translation
$en = new Language();
$en->setLanguage("en");

//to check if intermesh has defined it as a core translation
$nl = new Language();
$nl->setLanguage("nl");

class JsFile {

	/**
	 *
	 * @var File
	 */
	private $file;

	public function __construct(File $file) {
		$this->file = $file;
	}

	public function getStrings() {
		$content = $this->file->getContents();

		preg_match_all('/[\s\+\(\[:=]t\s*\(\s*[\'"](.+?)[\'"]\s*[\),]/', $content, $matches);
//		if(!empty($matches[1]))
//var_dump($matches);
		return array_map('stripslashes', $matches[1]);
	}

}

header("Content-Type: text/plain");
$rootFolder = Environment::get()->getInstallFolder();

$coreFiles = $rootFolder->getFolder("views/Extjs3/javascript")->find('/.*\.js/', false);

$handle = fopen('php://output', 'w+');

fputcsv($handle, [
		"package",
		"module",
		"EN",
		GO()->getLanguage()->getIsoCode(),
		"source"
]);

$data = [];

$strings = [];
foreach ($coreFiles as $file) {

	//echo $file->getPath() ."\n\n\n";
	$jsFile = new JsFile($file);
	$strings = array_merge($strings, $jsFile->getStrings());
}

$strings = array_unique($strings);

writeStrings("core", "core", $strings, $file->getRelativePath($rootFolder));

function writeStrings($package, $module, $strings, $relPath) {
	
	global $en, $nl, $rootFolder, $handle, $data;

	foreach ($strings as $string) {
		
		$isCore = $nl->translationExists($string);

		$enTranslation = $en->t($string, $package, $module);

		$translated = GO()->t($string, $package, $module);
		if (is_array($enTranslation)) {
			foreach ($enTranslation as $key => $stringItem) {
				$translatedItem = $translated[$key] ?? "";
	
				$fields = [
						$isCore ? "core" : $package,
						$isCore ? "core" : $module,
						$string . '[' . $key . ']',
						$translatedItem == $stringItem ? "" : $translatedItem,
						$relPath
				];
				fputcsv($handle, $fields);
			}
		} else {

			$fields = [
					$isCore ? "core" : $package,
					$isCore ? "core" : $module,
					$string,
					$translated == $string ? "" : $translated,
					$relPath
			];

			fputcsv($handle, $fields);
		}
	}
}


$packageFolders = $rootFolder->getFolder("go/modules")->getFolders();
$packageFolders[] = $rootFolder->getFolder("modules");

foreach($packageFolders as $packageFolder) {
	foreach($packageFolder->getFolders() as $moduleFolder) {
		$files = $moduleFolder->find("/.*\.js/");
		$strings = [];
		foreach($files as $file) {
			$jsFile = new JsFile($file);
			$strings = array_merge($strings, $jsFile->getStrings());
		}
		
		$strings = array_unique($strings);
		$package = $packageFolder->getName();
		if($package == "modules") {
			$package = "legacy";
		}
		writeStrings($package, $moduleFolder->getName(), $strings, $file->getRelativePath($rootFolder));
	}
}