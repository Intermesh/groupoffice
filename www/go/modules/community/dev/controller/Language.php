<?php

namespace go\modules\community\dev\controller;

use go\core\cache\None;
use go\core\Controller;
use go\core\fs\Blob;
use go\core\fs\File;
use go\core\http\Client;
use go\core\jmap\Response;
use go\core\Language as LangModel;
use go\modules\community\dev\model\LanguageScanner;
use function GO;

/**
 *
 */
class Language extends Controller {

	private $handle;
	private $en;
	
	const DELIMITER = ';';
	const ENCLOSURE = '"';


	private bool $translate = false;
	private bool $missingOnly = false;

	public function __construct()
	{
		// Authenticates the user
		parent::__construct();

		//for checking arrays() in english translation
		$this->en = new LangModel();
		$this->en->initExport();
		$this->en->setLanguage("en");
	}

	/**
	 * Export language from source files
	 *
	 * Run this command to export language. LibreTranslate runs locally and
	 * will be used to machine translate the missing string:
	 *
	 * ```
	 * docker compose exec groupoffice php www/cli.php community/dev/Language/export --language=nl --translate --missingOnly | tee nl-missing.csv
	 * ```
	 *
	 * Leave out the modules of customers with "skipPackages", a comma separated list of packages. For legacy modules that
	 * link to another repository, use the name of that folder:
	 *
	 * ```
	 * docker compose exec groupoffice php www/cli.php community/dev/Language/export --language=it --missingOnly --skipPackages=agya,udo,duurzaamgebouw > it-missing.csv
	 * ```
	 *
	 * Import language file:
	 * ```
	 * docker compose exec groupoffice php www/cli.php community/dev/Language/import --path=lang.csv
	 * ```
	 *
	 * @apiPermission Module access: exports the translatable strings from the source files and their translations. No user data is read.
	 * @param $params
	 * @return void
	 * @throws \Exception
	 */
	public function export($params) {

		$this->translate = !empty($params['translate']);
		$this->missingOnly = !empty($params['missingOnly']);

		go()->getLanguage()->initExport();
		go()->getLanguage()->setLanguage($params['language']);

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
		], self::DELIMITER,
			self::ENCLOSURE,
		""
		);

		// Strings from JS, TS and PHP. Strings that core or more than one module uses go to core.
		$skipPackages = empty($params['skipPackages']) ? [] : array_map('trim', explode(',', $params['skipPackages']));
		$scanner = new LanguageScanner($skipPackages);
		foreach ($scanner->getStrings() as $target => $strings) {
			[$package, $module] = explode('/', $target);
			$this->writeStrings($package, $module, $strings);
		}

		// Not exported, usually because the package and module arguments of t() are swapped
		if (!empty($scanner->unknownTargets) && defined('STDERR')) {
			fwrite(STDERR, "Unknown package/module:\n" . implode("\n", array_unique($scanner->unknownTargets)) . "\n");
		}

		if(empty($params['output'])) {
			$blob = Blob::fromTmp($csvFile);
			$blob->type = "text/csv";
			$blob->name = "lang.csv";
			$blob->save();

			Response::get()->addResponse(["blobId" => $blob->id]);
		}
	}

	/**
	 * @var array<string, array> "package/module" => translations of the language file
	 */
	private array $fileTranslations = [];

	private function fileTranslations(string $package, string $module): array
	{
		return $this->fileTranslations[$package . '/' . $module] ??= go()->getLanguage()->getFileTranslations(go()->getLanguage()->getIsoCode(), $package, $module);
	}

	/**
	 * Write the strings of a module with their translations to the export file
	 *
	 * @apiPermission Module access: helper of export() that only writes translations of the given strings. No user data is read.
	 */
	function writeStrings($package, $module, $strings) {

		foreach ($strings as $string) {
			$enTranslation = $this->en->t($string, $package, $module);

			// The language file itself, because getTranslation() returns the English text when the file has none
			$translated = $this->fileTranslations($package, $module)[$string] ?? null;

			// Lookup falls back to core, so a string that core translates is not missing in a module
			if ($this->missingOnly && !isset($translated) && $package !== 'core' && isset($this->fileTranslations('core', 'core')[$string])) {
				continue;
			}
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
						fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE, "");
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
					fputcsv($this->handle, $fields, self::DELIMITER, self::ENCLOSURE, "");
				}
			}
		}
	}


	/**
	 * Machine translate a string with LibreTranslate when export() is called with "translate"
	 *
	 * @apiPermission Module access: called directly it returns the given string, because only export() enables translation on its own controller instance.
	 */
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

		if($response['status'] != 200) {
			throw new \Exception("Error translating string: ".var_export($response['body'], true));
		}

		return isset($response['body']['translatedText']) ? $response['body']['translatedText'] : "";
	}


	


}
