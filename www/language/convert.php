<?php
use go\core\App;
use go\core\jmap\Router;
use go\core\jmap\Request;
use go\core\jmap\State;

require(__DIR__ . "/../vendor/autoload.php");

//Create the app with the database connection
App::get();

$languageFolder = \go\core\Environment::get()->getInstallFolder()->getFolder('language');

require(\go\core\Environment::get()->getInstallFolder() . '/language/languages.php');

foreach($languages as $iso => $name) {
	$commonFile = $languageFolder->getFile('common/'. $iso .'.php');
					
	if(!$commonFile->exists()) {
		echo "Skipping ". $commonFile . "\n";
		continue;
	}
					
	$lang = require($commonFile->getPath());
	
	$lpFile = $languageFolder->getFile('lostpassword/'. $iso .'.php');
	if($lpFile->exists()) {		
		$lang = array_merge($lang, require($lpFile));
	}
	
	$l = [];
	$countriesFile = $languageFolder->getFile('countries/'. $iso .'.php');
	if($countriesFile->exists()) {
		require($countriesFile);
		$lang['countries'] = $l;
	}
	
	$l = [];
	$fileTypesFile = $languageFolder->getFile('filetypes/'. $iso .'.php');
	if($fileTypesFile->exists()) {
		require($fileTypesFile);
		$lang['filetypes'] = $l;
	}
	
	$newFile = \go\core\Environment::get()->getInstallFolder()->getFile('go/modules/core/core/language/'.$iso .'.php');
	
	$newFile->putContents("<?php\nreturn ".var_export($lang, true).";\n");
}
