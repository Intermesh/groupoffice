<?php

namespace go\modules\community\dev\controller;

use go\core\cache\None;
use go\core\Controller;
use go\core\Environment;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\http\Client;
use go\core\jmap\Response;
use go\core\Language as LangModel;
use function GO;

class Language extends Controller {

	private $handle;
	private $en;
	private $nl;
	
	const DELIMITER = ';';
	const ENCLOSURE = '"';


	private bool $translate = false;
	private bool $missingOnly = false;

	protected function authenticate() {
    if (!go()->getAuthState()->isAuthenticated()) {			
      throw new Exception(401, "Unauthorized");
		}  	
	}

	public function __construct()
	{
		//for checking arrays() in english translation
		$this->en = new LangModel();
		$this->en->initExport();
		$this->en->setLanguage("en");

		//to check if intermesh has defined it as a core translation
		$this->nl = new LangModel();
		$this->nl->initExport();
		$this->nl->setLanguage("nl");
	}

	public function export($params) {

		$this->translate = !empty($params['translate']);
		$this->missingOnly = !empty($params['missingOnly']);

		go()->getLanguage()->initExport();
		go()->getLanguage()->setLanguage($params['language']);

		$rootFolder = Environment::get()->getInstallFolder();

		$coreFiles = $rootFolder->getFolder("views/Extjs3/javascript")->find('/.*\.js/', false);
		$coreFiles = array_merge($coreFiles, $rootFolder->getFolder("go/core/views/extjs3")->find('/.*\.js/', false));

		if(empty($params['output'])) {
			$csvFile = File::tempFile('csv');
			$this->handle = $csvFile->open('w+');
		} else {
			$this->handle = fopen("php://output", "w+");
		}

		//add UTF-8 BOM char for excel to recognize UTF-8 in the CSV
		fputs($this->handle, chr(239) . chr(187) . chr(191));

		fputcsv($this->handle, [
				"package",
				"module",
				"EN",
				go()->getLanguage()->getIsoCode()
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
				
				if($moduleFolder->isLink() && !$moduleFolder->getLinkTarget()){
					//broken symlink
					continue;
				}
				
				$files = $moduleFolder->find(['regex' => "/^.*(\.js|\.ts)$/", "exclude" => '(node_modules|vendor)'] );
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

				$this->writeStrings($package, $moduleFolder->getName(), $modStrings);
			}
		}

		$this->writeStrings("core", "core", $core);

		if(empty($params['output'])) {
			$blob = Blob::fromTmp($csvFile);
			$blob->type = "text/csv";
			$blob->name = "lang.csv";
			$blob->save();

			Response::get()->addResponse(["blobId" => $blob->id]);
		}
	}

	/**
	 * This will parse t("Some text") function calls to find new strings to translate
	 *
	 * @param File $file
	 * @return array
	 */
	private function getStringsFromJS(File $file) {
		$content = $file->getContents();

		preg_match_all('/[\s\+\(\[:=]t\s*\(\s*[\'"](.+?)[\'"]\s*[\),]/', $content, $matches);
//		if(!empty($matches[1]))
//var_dump($matches);
		return array_map('stripslashes', $matches[1]);
	}

	function writeStrings($package, $module, $strings) {

		foreach ($strings as $string) {
			$enTranslation = $this->en->t($string, $package, $module);

			$translated = go()->getLanguage()->getTranslation($string, $package, $module);
			if (is_array($enTranslation)) {
				foreach ($enTranslation as $key => $stringItem) {
					$translatedItem = isset($translated) ? $translated[$key] ?? null : null;

					$fields = [
							$package,
							$module,
							$string . '[' . $key . ']',
							!isset($translatedItem) ? $this->translateAPI($stringItem) : $translatedItem
					];
					if(!$this->missingOnly || !isset($translatedItem) ) {
						fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE);
					}
				}
			} else {
				
				if(!is_scalar($translated) && $translated != null) {
					throw new \Exception("Invalid language in ".$package."/".$module.": ".$string);
				}
				$fields = [
						$package,
						$module,
						$string,
						$translated ?? $this->translateAPI($string)
				];

				if(!$this->missingOnly || $translated == null) {
					fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE);
				}
			}
		}
	}


	function translateAPI($string) {

		if(!$this->translate) {
			return $string;
		}
//		echo "Translating string: ".$string."\n";

		$client = new Client();
		$client->setOption(CURLOPT_CONNECTTIMEOUT, 120);
		$client->setHeader("Connection", "close");
		$response = $client->postJson("http://libretranslate:5010/translate", [
			"q" => $string,
			"source" => "en",
			"target" => go()->getLanguage()->getIsoCode(),
			"format" => "text",
			"alternatives" => 0
			]);

		return isset($response['body']['translatedText']) ? $response['body']['translatedText'] : "";
	}


	


}
