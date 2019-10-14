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
		$blob = Blob::fromTmp($csvFile->getPath());
		$blob->type = "text/csv";
		$blob->name = "lang.csv";
		$blob->modified = time();
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

	public function import($params) {
		$sourceFile = new File($params['path']);
		$file = File::tempFile('csv');
		if(!$sourceFile->copy($file)) {
			throw new \Exception("Could not copy file to temporary directory");
		}
		
		if(!$file->convertToUtf8()) {
			throw new \Exception("Could not convert to UTF-8");
		}

		if (!$file->exists()) {
			throw new \Exception("File not found " . $params['path']);
		}
		$this->handle = $file->open("r");

		if (!$this->handle) {
			throw new \Exception("Could not open " . $params['path']);
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
				} else {
					$langFilePath = "go/modules/" . $package . "/" . $module . "/language/" . $lang . ".php";
				}
				
				
				$file = $rootFolder->getFile($langFilePath);
				
				if ($file->exists()) {
					$existingTranslations = require($file->getPath());
					
					$translations = array_merge($existingTranslations, $translations);
				}
				echo "Writing: ".$langFilePath."\n";
				
				$file->putContents("<?php\nreturn ".var_export($translations, true).";\n");
			}
		}
		
		$file->delete();
		
	}

}
