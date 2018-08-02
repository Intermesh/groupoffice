<?php

namespace go\core;

use Exception;
use go\core\fs\File;
use go\core\jmap\Request;
use go\modules\core\modules\model\Module;

class Language extends Singleton {

	/**
	 *
	 * @var string eg "en-US".
	 */
	private $isoCode;
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
	
	public function setLanguage($isoCode = null) {
		
		if(!isset($isoCode)) {
			$isoCode = $this->getBrowserLanguage();
		}
		
		if(!$this->hasLanguage($isoCode)) {
			throw new \Exception("Invalid language given ".$isoCode);
		}
		
		if($isoCode != $this->isoCode) {
			$this->isoCode = $isoCode;
			$this->data = [];
		}
		
	}
	
	private function getBrowserLanguage(){
		
		if(isset($_GET['SET_LANGUAGE']) && $this->hasLanguage($_GET['SET_LANGUAGE'])) {
			setcookie('GO_LANGUAGE', $_GET['SET_LANGUAGE']);
			return $_GET['SET_LANGUAGE'];
		}
		
		if(isset($_COOKIE['GO_LANGUAGE'])) {
			return $_COOKIE['GO_LANGUAGE'];
		}
		
		$browserLanguages= Request::get()->getAcceptLanguages();
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
	 * @param String $module Name of the module to find the translation
	 * @param String $package Only applies if module is set to 'base'
	 */
	public function t($str, $package = 'core', $module = 'core') {

		$this->loadSection($package, $module);
		
		if(!isset($this->data[$package]) || !isset($this->data[$package][$module])) {
			return t($str);
		}
		
		return $this->data[$package][$module][$str] ?? $str;
	}


	private function loadSection($package = 'core', $module = 'core') {
		if (!isset($this->data[$package])) {
			$this->data[$package] = [];
		} 
		
		if(!isset($this->data[$package][$module])) {

			$langData = [];
			//Get english default
			$file = $this->findLangFile('en',$package, $module);
			if ($file->exists()) {
				$langData = require($file);
			}

			//overwirte english with actual language
			if ($this->isoCode != 'en') {
				$file = $this->findLangFile($this->isoCode, $package, $module);
				if ($file->exists()) {
					$langData = array_merge($langData, require($file));
				}
			}

			$file = $this->findLangOverride($this->isoCode, $package, $module);
			if ($file->exists()) {
				$langData = array_merge($langData, require($file));
			}

            foreach ($langData as $key => $translation) {
                $langData[$key] = str_replace('{product_name}', \GO::config()->product_name, $translation);
            }

			$this->data[$package][$module] = $langData;			
		}
	}
	
	public function hasLanguage($lang) {
		return $this->findLangFile($lang, 'core', 'core')->exists();
	}

	/**
	 * 
	 * @param string $lang
	 * @param string $module
	 * @param string $basesection
	 * @return File
	 * @throws Exception
	 */
	private function findLangFile($lang, $package, $module) {
		
		if($package == "legacy") {
			$folder = Environment::get()->getInstallFolder()->getFolder('modules/' . $module .'/language');
		} else
		{
			$folder = Environment::get()->getInstallFolder()->getFolder('go/modules/' . $package . '/' . $module .'/language');
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
	private function findLangOverride($lang, $package, $module) {

		$folder = GO()->getDataFolder()->getFolder('users/admin/language/' . $package . '/' .$module);
		
		return $folder->getFile($lang . '.php');
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

	
	
	
	public function getAllLanguage(){
		$modules = Module::find();
		foreach($modules as $module) {
			$this->loadSection($module->package  ?? "legacy", $module->name);
		}
		
		return $this->data;
	}
}
