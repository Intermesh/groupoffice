<?php

namespace go\core\webclient;

use GO;
use go\core\Environment;
use go\core\fs\File;
use go\core\Language;
use go\modules\core\modules\model\Module;

class Extjs3 {

	/**
	 * 
	 * @param string $theme
	 * @return File
	 */
	public function getCSSFile($theme = 'Paper') {

		$cacheFile = GO()->getDataFolder()->getFile('clientscript/' . $theme . '/style.css');

		if (GO()->getDebugger()->enabled || !$cacheFile->exists()) {
//		if (!$cacheFile->exists()) {
			$modules = Module::getInstalled();

			foreach ($modules as $module) {

				if (isset($module->package)) {
					$folder = $module->module()->getFolder();
					$file = $folder->getFile('views/extjs3/themes/default/style.css');
					if ($file->exists()) {
						$cacheFile->putContents($file->getContents(), FILE_APPEND);
					}

					$file = $folder->getFile('views/extjs3/themes/' . $theme . '/style.css');
					if ($file->exists()) {
						$cacheFile->putContents($file->getContents(), FILE_APPEND);
						continue;
					}
				}


				//old path

				$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $module->name);
				$file = $folder->getFile('themes/Default/style.css');
				if ($file->exists()) {
					$cacheFile->putContents($file->getContents(), FILE_APPEND);
				}

				$file = $folder->getFile('themes/' . $theme . '/style.css');
				if ($file->exists()) {
					$cacheFile->putContents($file->getContents(), FILE_APPEND);
				}
			}
		}
		return $cacheFile;
	}
	
	
	/**
	 * 
	 * @return File
	 */
	public function getLanguageJS() {
		
		$iso = Language::get()->getIsoCode();
	
		
		$cacheFile = GO()->getDataFolder()->getFile('clientscript/lang_'.$iso.'.js');

		if (GO()->getDebugger()->enabled || !$cacheFile->exists()) {
//		if (!$cacheFile->exists()) {

			$str = "var GO = GO || {};\n";

			$extjsLang = Language::get()->t("extjs_lang");
			if ($extjsLang == 'extjs_lang')
				$extjsLang = $iso;

			$viewRoot = Environment::get()->getInstallFolder()->getFolder('views/Extjs3');

			$extLang = $viewRoot->getFile('ext/src/locale/ext-lang-' . $extjsLang . '.js');
			if ($extLang->exists()) {
				$str .= $extLang->getContents();
			}

			require(Environment::get()->getInstallFolder()->getFile('language/languages.php'));
			$str .= "GO.Languages=[];\n";

			foreach ($languages as $code => $name) {
				$str .= 'GO.Languages.push(["' . $code . '","' . $name . '"]);' . "\n";
			}

			//Put all lang vars in js		
			$l = Language::get()->getAllLanguage();
			$l['iso'] = $iso;

			$str .= 'GO.lang = ' . json_encode($l) . ";\n";

			$cacheFile->putContents($str);
		}
		
		return $cacheFile;
	}

}
