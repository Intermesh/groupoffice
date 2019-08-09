<?php

namespace go\modules\community\dev\controller;

use go\core\Controller;
use go\core\Environment;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\Response;
use go\core\Language as LangModel;
use function GO;

class Language extends Controller {

	private $handle;
	private $en;
	private $nl;
	
	const DELIMITER = ';';
	const ENCLOSURE = '"';

	protected function authenticate() {  
    if (!GO()->getAuthState()->isAuthenticated()) {			
      throw new Exception(401, "Unauthorized");
		}  	
	}

	public function export($params) {
		GO()->getLanguage()->setLanguage($params['language']);

//for checking arrays() in english translation
		$this->en = new LangModel();
		$this->en->setLanguage("en");

//to check if intermesh has defined it as a core translation
		$this->nl = new LangModel();
		$this->nl->setLanguage("nl");

		$rootFolder = Environment::get()->getInstallFolder();

		$coreFiles = $rootFolder->getFolder("views/Extjs3/javascript")->find('/.*\.js/', false);
		$coreFiles = array_merge($coreFiles, $rootFolder->getFolder("go/core/views/extjs3")->find('/.*\.js/', false));

		$csvFile = File::tempFile('csv');

		$this->handle = $csvFile->open('w+');

		fputcsv($this->handle, [
				"package",
				"module",
				"EN",
				GO()->getLanguage()->getIsoCode(),
				"source"
		], self::DELIMITER, self::ENCLOSURE);

		$core = [];
		foreach ($coreFiles as $file) {
			$core = array_merge($core, $this->getStringsFromJS($file));
		}

		$core = array_unique($core);

		$packageFolders = $rootFolder->getFolder("go/modules")->getFolders();
		$packageFolders[] = $rootFolder->getFolder("modules");

		foreach ($packageFolders as $packageFolder) {
			foreach ($packageFolder->getFolders() as $moduleFolder) {
				
				if(!$moduleFolder->exists()){
					//broken symlink
					continue;
				}
				
				$files = $moduleFolder->find("/.*\.js/");
				$strings = [];
				foreach ($files as $file) {
					$strings = array_merge($strings, $this->getStringsFromJS($file));
				}

				$strings = array_unique($strings);
				$package = $packageFolder->getName();
				if ($package == "modules") {
					$package = "legacy";
				}
				$modStrings = [];
				foreach ($strings as $string) {
					if ($this->nl->translationExists($string)) {

						//save core strings for later.
						if (!in_array($string, $core)) {
							$core[] = $string;
						}
					} else {
						$modStrings[] = $string;
					}
				}

				$this->writeStrings($package, $moduleFolder->getName(), $modStrings, $file->getRelativePath($rootFolder));
			}
		}

		$this->writeStrings("core", "core", $core, "*");

		//todo, this is refsactored in master
		$blob = Blob::fromTmp($csvFile);
		$blob->type = "text/csv";
		$blob->name = "lang.csv";
		$blob->save();

		Response::get()->addResponse(["blobId" => $blob->id]);
	}

	private function getStringsFromJS(File $file) {
		$content = $file->getContents();

		preg_match_all('/[\s\+\(\[:=]t\s*\(\s*[\'"](.+?)[\'"]\s*[\),]/', $content, $matches);
//		if(!empty($matches[1]))
//var_dump($matches);
		return array_map('stripslashes', $matches[1]);
	}

	function writeStrings($package, $module, $strings, $relPath) {

		foreach ($strings as $string) {
			$enTranslation = $this->en->t($string, $package, $module);

			$translated = GO()->t($string, $package, $module);
			if (is_array($enTranslation)) {
				foreach ($enTranslation as $key => $stringItem) {
					$translatedItem = $translated[$key] ?? "";

					$fields = [
							$package,
							$module,
							$string . '[' . $key . ']',
							$translatedItem == $stringItem ? "" : $translatedItem,
							$relPath
					];
					fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE);
				}
			} else {
				
				if(!is_scalar($translated)) {
					throw new \Exception("Invalid language in ".$package."/".$module.": ".$string);
				}
				$fields = [
						$package,
						$module,
						$string,
						$translated == $string ? "" : $translated,
						$relPath
				];

				fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE);
			}
		}
	}

	/**
	 * To import run:
	 * 
	 * ```
	 * php cli.php community/dev/Language/import --path=/path/to/lang.csv
	 * ```
	 * 
	 * or with docker compose:
	 * 
	 * First put lang.csv in root of GO source
	 * 
	 * ```
	 * docker-compose exec --user root groupoffice-master php www/cli.php community/dev/Language/import --path=lang.csv
	 * ```
	 * 
	 * @param type $params
	 * @throws \Exception
	 */
	public function import($path) {
		$sourceFile = new File($path);
		$file = File::tempFile('csv');
		if(!$sourceFile->copy($file)) {
			throw new \Exception("Could not copy file to temporary directory");
		}
		
		if(!$file->convertToUtf8()) {
			throw new \Exception("Could not convert to UTF-8");
		}
		if (!$file->exists()) {
			throw new \Exception("File not found " . $path);
		}
		$this->handle = $file->open("r");

		if (!$this->handle) {
			throw new \Exception("Could not open " . $path);
		}

		$headers = fgetcsv($this->handle,0, self::DELIMITER, self::ENCLOSURE);


		if (!$headers) {
			throw new \Exception("Could not read CSV");
		}
		if (count($headers) < 5) {
			throw new \Exception("Invalid CSV file (Header count != 5): First record: ".var_export($headers, true));
		}

		$lang = strtolower($headers[3]);

		$data = [];

		
		while ($record = fgetcsv($this->handle, 0, self::DELIMITER, self::ENCLOSURE)) {
			
			try {
				list($package, $module, $en, $translation, $source) = $record;
			} catch(\Exception $e) {
				echo "ERROR: Could not read record: ". var_export($record, true) ."\n\n";
			}
			

			if (empty($translation)) {
				continue;
			}

			if (!isset($data[$package])) {
				$data[$package] = [];
			}

			if (!isset($data[$package][$module])) {
				$data[$package][$module] = [];
			}

			if (preg_match("/(.*)\[(.*)\]/", $en, $matches)) {
				$data[$package][$module][$matches[1]][$matches[2]] = $translation;
			} else {
				$data[$package][$module][$en] = $translation;
			}
		}
		
		$rootFolder = Environment::get()->getInstallFolder();

		foreach ($data as $package => $modules) {

			foreach ($modules as $module => $translations) {
				if ($package == "legacy") {
					$langFilePath = "modules/" . $module . "/language/" . $lang . ".php";
				} else if($package == "core"){
					$langFilePath = "go/core/language/" . $lang . ".php";
				}else {
					$langFilePath = "go/modules/" . $package . "/" . $module . "/language/" . $lang . ".php";
				}
				
				
				$langfile = $rootFolder->getFile($langFilePath);
				
				if ($langfile->exists()) {
					$existingTranslations = require($langfile->getPath());
					
					$translations = array_merge($existingTranslations, $translations);
				}
				echo "Writing: ".$langFilePath."\n";
				
				$langfile->putContents("<?php\nreturn ".var_export($translations, true).";\n");
			}
		}
		
		$file->delete();
	}





	/**
	 * 
	 * docker-compose exec groupoffice-master php /usr/local/share/groupoffice/cli.php community/dev/Language/convertModule --name=inventaire
	 * 
	 */
	public function convertModule($name) {
		$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $name);
		if(!$folder->exists()) {
			throw new \Exception("Folder for module ". $name ." does not exist");
		}

		$langFile = $folder->getFile('language/en.php');
		if(!$langFile->exists()) {
			throw new \Exception("Language file for module ". $name ." does not exist");
		}

		

		$bakLangFile = $folder->getFile('language/en.php.bak');
		if(!$bakLangFile->exists()) {
			$langFile->copy($bakLangFile);
			require($bakLangFile);
			$modLang = isset($l) ? $l : [];

			$newLang = [
				'name' => $modLang['name'] ?? $name,
				'description' => $modLang['description'] ?? ""
			];

			$langFile->putContents("<?php\nreturn " . var_export($newLang, true) . ";");
			
		} else{
			require($bakLangFile);
			$modLang = isset($l) ? $l : [];
		}

		require(dirname(__DIR__) . '/resources/oldcommonlang.php');
		$commonLang = $l;
		unset($l);

		
		$jsFiles = $folder->find("/.*\.js/", false, true);

		foreach($jsFiles as $jsFile) {
			$this->replaceJs($jsFile, $name, $modLang, $commonLang);
		}

		$phpFiles = $folder->find("/.*\.php/", false, true);

		foreach($phpFiles as $phpFile) {
			$this->replacePhp($phpFile, $name, $modLang, $commonLang);
		}
	}

	private function replacePhp(File $file, $moduleName, array $modLang, array $commonLang) {
		$content = $file->getContents();

		$content = preg_replace_callback('/GO::t\(([\'"a-zA-Z0-9_]+)\)/', function($matches) use ($commonLang) {				
			$key = trim($matches[1], '"\'');
			$str = $commonLang[$key] ?? $key;

			return 'GO()->t("' . str_replace('"', '\"', $str) . '")';
		}, $content);	
		
		$content = preg_replace_callback('/GO::t\(([\'"a-zA-Z0-9_]+)\s*,\s*([\'"a-zA-Z0-9_]+)\)/', function($matches) use ($modLang) {				
			$key = trim($matches[1], '"\'');
			$str = $modLang[$key] ?? $key;

			return 'GO()->t("' . str_replace('"', '\"', $str) . '", "legacy", "' . trim($matches[2], '"\'') . '")';			
		}, $content);	
		
		$file->putContents($content);

	}

	private function replaceJs(File $file, $moduleName, array $modLang, array $commonLang) {
		$js = $file->getContents();

		$js = preg_replace_callback("/GO\.lang\[([^\]]+)\]/", function($matches) use ($commonLang) {

			$key = trim($matches[1], '"\'');

			$str = $commonLang[$key] ?? $key;

			return 't("' . str_replace('"', '\"', $str) . '")';
		}, $js);


		$js = preg_replace_callback("/GO\.lang\.([a-z0-9_]+)/i", function($matches) use ($commonLang) {

			$key = trim($matches[1], '"\'');

			$str = $commonLang[$key] ?? $key;

			return 't("' . str_replace('"', '\"', $str) . '")';
		}, $js);


		$js = preg_replace_callback("/GO\." . $moduleName . "\.lang\[([^\]]+)\]/", function($matches) use ($modLang) {

			$key = trim($matches[1], '"\'');

			$str = $modLang[$key] ?? $key;

			return 't("' . str_replace('"', '\"', $str) . '")';
		}, $js);


		$js = preg_replace_callback("/GO\." . $moduleName . "\.lang\.([a-z0-9_]+)/i", function($matches) use ($modLang) {

			$key = trim($matches[1], '"\'');

			$str = $modLang[$key] ?? $key;

			return 't("' . str_replace('"', '\"', $str) . '")';
		}, $js);


		// echo $js ."\n\n\n-----\n\n\n";

		$file->putContents($js);

	}

	
//convert lang files
//	<?php
//
//define('GO_CONFIG_FILE', "/media/sf_Projects/groupoffice-6.2/config.php");
//
//require('../www/GO.php');
//
//GO::language()->setLanguage('en');
//
//chdir(dirname(__dir__));
//
//
//$cmd = 'find ./www/modules -path '.escapeshellarg("*/language/*.php");
//exec($cmd, $files);
//
//$map = array();
//$count = 0;
//foreach ($files as $file62) {
//	
//	if(basename($file62) == 'en.php') {
//		continue;
//	}
//	
//	echo $file62."\n";
//
//	$parts = explode("/", $file62);
//	$module = $parts[count($parts) - 3];
//
//	if ($module == "language") {
//		$module = $parts[count($parts) - 2];
//	}
//
//	
//
//	$file63 = str_replace('6.2', '6.3', realpath($file62));
//	
//	if(!file_exists($file63)) {
//		continue;
//	}
//	
//	$l = [];
//	require($file62);
//	$old = $l;
//
//	$l = $bak = require($file63);
//
//	foreach ($old as $key => $value) {
//		if($key == "description" || $key == "name") {
//			continue;
//		}
//		$newKey = GO::t($key, $module);
//		
//		if(!is_string($newKey) || strpos($newKey, "\n") !== false) {
//			continue;
//		}
//		if (!isset($l[$newKey])) {
//			$l[$newKey] = $value;
//		}
//	}
//	
//	var_dump(array_diff(array_keys($l), array_keys($bak)));
//	
//	$data = "<?php\nreturn " . var_export($l, true) . ";";			
//	file_put_contents($file63, $data);
//}
	


}
