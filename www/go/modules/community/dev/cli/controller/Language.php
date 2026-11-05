<?php

namespace go\modules\community\dev\cli\controller;

use go\core\Controller;
use go\core\data\convert\Spreadsheet;
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
				$moduleFolder = $langfile->getFolder()->getParent();

				if(!$moduleFolder->exists()) {
					echo "MODULE: " . $package . "/" . $module ." does not exist.\n";
					continue;
				}
				
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
