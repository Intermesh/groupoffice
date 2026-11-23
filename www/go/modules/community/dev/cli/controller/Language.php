<?php

namespace go\modules\community\dev\cli\controller;

use go\core\Controller;
use go\core\data\convert\Spreadsheet;
use go\core\Environment;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\jmap\Response;
use go\core\Language as LangModel;
use go\modules\community\dev\model\LanguageFile;
use function GO;

class Language extends Controller {

	private $handle;
	private $en;
	private $nl;
	
	private $delimiter = ';';
	const ENCLOSURE = '"';

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
	 * docker compose exec groupoffice-64 php www/cli.php community/dev/Language/import --path=lang.csv
	 * ```
	 *
	 * Existing translations are changed in place and new ones are added at the end of the file, so the file keeps its
	 * formatting. Translations whose placeholders ({count}, {{contact.name}}, %s), HTML tags or line breaks differ from
	 * the English text are skipped.
	 * 
	 * @param array $params
	 * @throws \Exception
	 */
	public function import($params) {


		extract($this->checkParams($params, ['path']));

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

		$this->delimiter = Spreadsheet::sniffDelimiter($file);

		$this->handle = $file->open("r");

		if (!$this->handle) {
			throw new \Exception("Could not open " . $path);
		}

		$headers = fgetcsv($this->handle,0, $this->delimiter, self::ENCLOSURE, "");


		if (!$headers) {
			throw new \Exception("Could not read CSV");
		}
		if (count($headers) < 4) {
			throw new \Exception("Invalid CSV file (Header count < 4): First record: ".var_export($headers, true));
		}

		$lang = strtolower($headers[3]);

		$english = new LangModel();
		$english->initExport();
		$english->setLanguage('en');

		$data = [];

		
		while ($record = fgetcsv($this->handle, 0, $this->delimiter, self::ENCLOSURE, "")) {
			
			try {
				list($package, $module, $en, $translation) = $record;
			} catch(\Exception $e) {
				echo "ERROR: Could not read record: " . $e->getMessage().' : '. var_export($record, true) ."\n\n";
			}			

			if (empty($translation)) {
				continue;
			}

			// "Text[key]" is an item of an array translation, like the names of the months. Only when the English
			// translation is an array: other texts can contain brackets too.
			$arrayKey = null;
			$englishText = null;
			if (preg_match("/(.*)\[(.*)\]$/", $en, $matches)) {
				$englishArray = $english->getTranslation($matches[1], $package, $module) ?? $english->getTranslation($matches[1]);
				if (is_array($englishArray)) {
					$arrayKey = $matches;
					$englishText = $englishArray[$matches[2]] ?? $en;
				}
			}
			if (!isset($arrayKey)) {
				// Old keys like "recoveryMailBody" have their English text in en.php
				$englishText = $english->getTranslation($en, $package, $module) ?? $english->getTranslation($en) ?? $en;
			}

			// The placeholders, tags and line breaks must be kept, or the text breaks in the user interface
			if (!is_string($englishText) || !LanguageFile::hasSameTokens($englishText, $translation)) {
				echo "SKIPPED: placeholders differ: " . $package . "/" . $module . ": " . $en . " => " . $translation . "\n";
				continue;
			}

			if (!isset($data[$package])) {
				$data[$package] = [];
			}

			if (!isset($data[$package][$module])) {
				$data[$package][$module] = [];
			}

			if (isset($arrayKey)) {
				$data[$package][$module][$arrayKey[1]][$arrayKey[2]] = $translation;
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
				$moduleFolder = $langfile->getFolder()->getParent();

				if(!$moduleFolder->exists()) {
					echo "MODULE: " . $package . "/" . $module ." does not exist.\n";
					continue;
				}
				
				// Existing entries are changed in place and new ones added, so the rest of the file stays as it is
				echo "Writing: ".$langFilePath."\n";
				$languageFile = new LanguageFile($langfile->getPath());
				$languageFile->set($translations);
				foreach ($languageFile->warnings as $warning) {
					echo "WARNING: " . $langFilePath . ": " . $warning . "\n";
				}
				if (!$languageFile->save()) {
					throw new \Exception("Could not write " . $langFilePath);
				}
			}
		}
		
		$file->delete();
	}



	/**
	 * docker compose exec groupoffice php www/cli.php community/dev/Language/export --language=nl --translate --missingOnly | tee nl-missing.csv
	 *
	 * @param $params
	 * @return void
	 */
	public function export($params) {
		$params['output'] = true;
		$c = new \go\modules\community\dev\controller\Language();
		$c->export($params);
	}

}
