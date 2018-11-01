<?php

namespace go\core\webclient;

use GO;
use go\core\App;
use go\core\Environment;
use go\core\fs\File;
use go\core\Language;
use go\modules\core\core\model\Settings;
use go\modules\core\modules\model\Module;

class Extjs3 {
	
	public function flushCache() {
		return App::get()->getDataFolder()->getFolder('clientscripts')->delete();
	}

	/**
	 * 
	 * @param string $theme
	 * @return File
	 */
	public function getCSSFile($theme = 'Paper') {

		$cacheFile = GO()->getDataFolder()->getFile('clientscripts/' . $theme . '/style.css');
		
		
		if (GO()->getDebugger()->enabled || !$cacheFile->exists()) {
//		if (!$cacheFile->exists()) {
			$modules = Module::getInstalled();
			$css = "";
			foreach ($modules as $module) {

				if (isset($module->package)) {
					$folder = $module->module()->getFolder();
					$file = $folder->getFile('views/extjs3/themes/default/style.css');
					if ($file->exists()) {
						$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";						
					}

					$file = $folder->getFile('views/extjs3/themes/' . $theme . '/style.css');
					if ($file->exists()) {
						$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";						
					}
				}

				//old path
				$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $module->name);
				$file = $folder->getFile('themes/Default/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";
				}

				$file = $folder->getFile('themes/' . $theme . '/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";					
				}
			}
			
			$cacheFile->putContents($css);
		}
		return $cacheFile;
	}
	
	
	private function replaceCssUrl($css, File $file){
		
		$baseurl = str_replace(Environment::get()->getInstallFolder()->getPath() . '/', Settings::get()->URL, $file->getFolder()->getPath()).'/';
		
		return preg_replace_callback('/url[\s]*\(([^\)]*)\)/iU', 
			function($matches) use($baseurl) { 
				return 'url('.$baseurl.trim(stripslashes($matches[1]),'\'" ').')';
			}, $css);
		 //return preg_replace('/url[\s]*\(([^\)]*)\)/ieU', "GO\Base\View\Extjs3::_replaceUrlCallback('$1', \$baseurl)", $css);
	}
	
	
	/**
	 * 
	 * @return File
	 */
	public function getLanguageJS() {
		
		$iso = \GO()->getLanguage()->getIsoCode();
	
		
		$cacheFile = GO()->getDataFolder()->getFile('clientscripts/lang_'.$iso.'.js');

		if (GO()->getDebugger()->enabled || !$cacheFile->exists()) {
//		if (!$cacheFile->exists()) {

			$str = "var GO = GO || {};\n";

			$extjsLang = \GO()->getLanguage()->t("extjs_lang");
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
			$l = \GO()->getLanguage()->getAllLanguage();
			$l['iso'] = $iso;

			$str .= 'GO.lang = ' . json_encode($l) . ";\n";
			
			$str .= "GO.lang.holidaySets = " . json_encode(\GO\Base\Model\Holiday::getAvailableHolidayFiles()) .";\n";
			
			$cacheFile->putContents($str);
		}
		
		return $cacheFile;
	}

}
