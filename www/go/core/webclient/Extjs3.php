<?php

namespace go\core\webclient;

use GO;
use go\core\App;
use go\core\Environment;
use go\core\fs\File;
use go\core\Language;
use go\core\model\Settings;
use go\core\model\Module;
use go\core\SingletonTrait;

class Extjs3 {

	use SingletonTrait;


	
	public function flushCache() {
		return App::get()->getDataFolder()->getFolder('clientscripts')->delete();
	}

	private $cssFile;

	/**
	 * 
	 * @param string $theme
	 * @return File
	 */
	public function getCSSFile($theme = 'Paper') {

		if(isset($this->cssFile)) {
			return $this->cssFile;
		}

		$cacheFile = go()->getDataFolder()->getFile('clientscripts/' . $theme . '/style.css');
		$debug = go()->getDebugger()->enabled && $cacheFile->exists();
		if ($debug || !$cacheFile->exists()) {
			$modules = Module::getInstalled();
			$css = "";
			$modifiedAt = null;
			foreach ($modules as $module) {

				if (isset($module->package)) {

          $folder = $module->module()->getFolder();

          $file = $folder->getFile('views/extjs3/themes/' . $theme . '/style.css');
          if ($file->exists()) {
            $css .= $this->replaceCssUrl($file->getContents(),$file)."\n";

            if($debug && $file->getModifiedAt() > $modifiedAt) {
            	$modifiedAt = $file->getModifiedAt();
            }
            continue;
          }


					$file = $folder->getFile('views/extjs3/themes/default/style.css');
					if ($file->exists()) {
						$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";

						if($debug && $file->getModifiedAt() > $modifiedAt) {
							$modifiedAt = $file->getModifiedAt();
						}
					}


				}

				//old path
				$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $module->name);
				$file = $folder->getFile('themes/Default/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";
					if($debug && $file->getModifiedAt() > $modifiedAt) {
						$modifiedAt = $file->getModifiedAt();
					}
				}

				$file = $folder->getFile('themes/' . $theme . '/style.css');
				if ($file->exists()) {
					$css .= $this->replaceCssUrl($file->getContents(),$file)."\n";
					if($debug && $file->getModifiedAt() > $modifiedAt) {
						$modifiedAt = $file->getModifiedAt();
					}
				}
			}

			if(!$debug || $modifiedAt > $cacheFile->getModifiedAt()) {
				$cacheFile->putContents($css);
			}
		}
		$this->cssFile = $cacheFile;
		return $cacheFile;
	}
	
	
	private function replaceCssUrl($css, File $file){
		
		$baseurl = str_replace(Environment::get()->getInstallFolder()->getPath() . '/', Settings::get()->URL, $file->getFolder()->getPath()).'/';
		
		$css = preg_replace_callback('/url[\s]*\(([^\)]*)\)/iU', 
			function($matches) use($baseurl) { 
				return 'url('.$baseurl.trim(stripslashes($matches[1]),'\'" ').')';
			}, $css);

		$css = str_replace("sourceMappingURL=", "sourceMappingURL=".$baseurl, $css);

		return $css;
		 //return preg_replace('/url[\s]*\(([^\)]*)\)/ieU', "GO\Base\View\Extjs3::_replaceUrlCallback('$1', \$baseurl)", $css);
	}
	
	
	/**
	 * 
	 * @return File
	 */
	public function getLanguageJS() {
		
		$iso = \go()->getLanguage()->getIsoCode();
	
		
		$cacheFile = go()->getDataFolder()->getFile('clientscripts/lang_'.$iso.'.js');

		if (!$cacheFile->exists()) {
//		if (!$cacheFile->exists()) {

			$str = "var GO = GO || {};\n";

			$extjsLang = \go()->getLanguage()->t("extjs_lang");
			if ($extjsLang == 'extjs_lang')
				$extjsLang = $iso;

			$viewRoot = Environment::get()->getInstallFolder()->getFolder('views/Extjs3');

			$extLang = $viewRoot->getFile('javascript/ext-locale/ext-lang-' . $extjsLang . '.js');
			if ($extLang->exists()) {
				$str .= $extLang->getContents();
			}

			require(Environment::get()->getInstallFolder()->getFile('language/languages.php'));
			$str .= "GO.Languages=[];\n";

			foreach ($languages as $code => $name) {
				$str .= 'GO.Languages.push(["' . $code . '","' . $name . '"]);' . "\n";
			}

			//Put all lang vars in js		
			$l = \go()->getLanguage()->getAllLanguage();
			$l['iso'] = $iso;

			$str .= 'GO.lang = ' . json_encode($l) . ";\n";
			
			$str .= "GO.lang.holidaySets = " . json_encode(\GO\Base\Model\Holiday::getAvailableHolidayFiles()) .";\n";
			
			$cacheFile->putContents($str);
		}
		
		return $cacheFile;
	}

}
