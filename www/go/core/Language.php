<?php

namespace go\core;

use Exception;
use go\core\fs\File;
use go\core\module\Base;
use go\core\util\ArrayObject;
use function GO;

class Language extends Singleton {

	private $isoCode = "en";
	private $data = [];

	protected function __construct() {
		parent::__construct();
		$this->isoCode = $this->getBrowserLanguage();		
	}
	
	/**
	 * Get ISO code with underscore separator for region
	 * 
	 * @return string eg. "en" or "en_UK"
	 */
	public function getIsoCode() {
		return $this->isoCode;
	}
	
	private function getBrowserLanguage(){
		
		$browserLanguages= jmap\Request::get()->getAcceptLanguages();
		foreach($browserLanguages as $lang){
			$lang = str_replace('-','_',explode(';', $lang)[0]);
			if($this->hasLanguage($lang)){
				return $lang;
			}
		}
		
		return GO()->getSettings()->language; // from settings if we cant determine
	}

	/**
	 * Translates a language variable name into the local language.
	 * 
	 * @param String $str String to translate
	 * @param String $moduleName Name of the module to find the translation
	 * @param String $coreSection Only applies if module is set to 'base'
	 */
	public function t($str, $moduleName = 'core', $coreSection = 'common') {

		$this->loadSection($moduleName, $coreSection);

		if ($moduleName == 'core') {
			if (isset($this->data[$moduleName][$coreSection][$str])) {
				return $this->data[$moduleName][$coreSection][$str];
			} 
			return $str;			
		}
		
		if (isset($this->data[$moduleName][$str])) {
			return $this->data[$moduleName][$str];
		} 
		
		return $str;
	}


	private function loadSection($moduleName = 'core', $basesection = 'common') {
		if (!isset($this->data[$moduleName]) || ($moduleName == 'core' && !isset($this->data[$moduleName][$basesection]))) {

			$langData = new ArrayObject();
			//Get english default
			$file = $this->findLangFile('en', $moduleName, $basesection);
			if ($file->exists()) {
				$langData->mergeRecurive(require($file));
			}

			//overwirte english with actual language
			if ($this->isoCode != 'en') {
				$file = $this->findLangFile($this->isoCode, $moduleName, $basesection);
				if ($file->exists()) {
					$langData->mergeRecurive(require($file));
				}
			}

			$file = $this->findLangOverride($this->isoCode, $moduleName, $basesection);
			if ($file->exists()) {
				$langData->mergeRecurive(require($file));
			}

			if ($moduleName == 'core') {
				$this->data[$moduleName][$basesection] = $langData;
			} else {
				$this->data[$moduleName] = $langData;
			}
		}
	}
	
	private function hasLanguage($lang) {
		return $this->findLangFile($lang, 'core', 'common')->exists();
	}

	/**
	 * 
	 * @param string $lang
	 * @param string $moduleName
	 * @param string $basesection
	 * @return File
	 * @throws Exception
	 */
	private function findLangFile($lang, $moduleName, $basesection) {
		if ($moduleName == 'core')
			$folder = Environment::get()->getInstallFolder()->getFolder('language/' . $basesection );
		else {
			$module = Base::findByName($moduleName);
			if(!$module) {
				throw new Exception("Module $moduleName not found!");
			}
			
			$folder = $module->getFolder()->getFolder('language');			
		}

		return $folder->getFile($lang . '.php');
	}

	/**
	 * 
	 * @param string $lang
	 * @param string $module
	 * @param string $basesection
	 * @return File
	 */
	private function findLangOverride($lang, $module, $basesection) {

		$folder = GO()->getDataFolder()->getFolder('users/admin/lang/' . $lang);
		$filename = $module == 'core' ? 'core_' . $basesection . '.php' : $module . '.php';

		$file = $folder->getFile($filename);

		if ($file->exists())
			return $file;


		$folder = GO()->getDataFolder()->getFolder('users/admin/lang');

		$file = $folder->getFile($filename);

		return $file;
	}

	

	/**
	 * Get all supported languages.
	 * 
	 * @return array array('en'=>'English');
	 */
	public function getLanguages() {
		require(Environment::get()->getInstallFolder() . '/language/languages.php');
		asort($languages);
		return $languages;
	}

	/**
	 * Get all countries
	 * 
	 * @return array array('nl'=>'The Netherlands');
	 */
	public function getCountries() {
		$this->loadSection('core', 'countries');
		asort($this->data['core']['countries']);
		return $this->data['core']['countries'];
	}

}
