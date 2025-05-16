<?php

namespace go\core;

use Exception;
use go\core\cache\None;
use go\core\fs\File;
use go\core\jmap\Request;
use go\core\model\Module;
use go\core\model\User;

class Language {
	/**
	 *
	 * @var string eg "en-US".
	 */
	private $isoCode;
	private $data = [];

	/**
	 * Replace {product_name} with Group-Office or $config['product_name']
	 *
	 * Exporting language disables this.
	 *
	 * @var bool
	 */
	private $replaceProductName = true;


	public function initExport() {
		$this->replaceProductName = false;
		$this->data = [];
		go()->setCache(new None());
	}


	
	/**
	 * Get ISO code with underscore separator for region
	 * 
	 * @return string eg. "en" or "en_UK"
	 */
	public function getIsoCode(): string
	{
		if(isset($_GET['SET_LANGUAGE'])) {

			if($_GET['SET_LANGUAGE'] == "") {

				$this->unsetCookie();
				unset($_GET['SET_LANGUAGE']);
			} else if($this->hasLanguage($_GET['SET_LANGUAGE'])) {

				$this->isoCode = $_GET['SET_LANGUAGE'];
				$this->setCookie();
				unset($_GET['SET_LANGUAGE']);
				return $this->isoCode;
			}
		}

		if(!isset($this->isoCode)) {
			if(isset($_COOKIE['GO_LANGUAGE']) && $this->hasLanguage($_COOKIE['GO_LANGUAGE'])) {
				$this->isoCode = $_COOKIE['GO_LANGUAGE'];
			} else {
				$this->isoCode = go()->getAuthState() && go()->getAuthState()->isAuthenticated() ? go()->getAuthState()->getUser(['language'])->language : $this->getBrowserLanguage();
			}
		}
		return $this->isoCode;
	}

	private function setCookie() {
		if(empty($_COOKIE['GO_LANGUAGE']) || $_COOKIE['GO_LANGUAGE'] != $this->isoCode) {
			$_COOKIE['GO_LANGUAGE'] = $this->isoCode;
			setcookie("GO_LANGUAGE", $this->isoCode, time() + (10 * 365 * 24 * 60 * 60), "/", Request::get()->getHost(), Request::get()->isHttps(), true);
		}
	}

	public function unsetCookie() {
		unset($_COOKIE['GO_LANGUAGE']);
		setcookie("GO_LANGUAGE", "", -1, "/", Request::get()->getHost(), Request::get()->isHttps(), true);
	}


	public function getTextDirection(): string
	{
		if(in_array($this->getIsoCode(), ['ar', 'he', 'ur'])) {
			return 'rtl';
		}
		return 'ltr';
	}


	/**
	 * Set new language
	 *
	 * @param string|null $isoCode when not given it's detected from the browser
	 * @return string|false Old language setting
	 */
	public function setLanguage(?string $isoCode = null) {
		$old = $this->getIsoCode();
		if(!isset($isoCode)) {
			$isoCode = $this->getBrowserLanguage();
		}
		
		if(!$this->hasLanguage($isoCode)) {
			go()->debug("Invalid language given ".$isoCode);
			return false;
		}
		
		if($isoCode != $this->isoCode) {
			$this->isoCode = $isoCode;
			$this->data = [];
		}

		return $old;
		
	}
	
	private function getBrowserLanguage(){

		
		$browserLanguages= Request::get()->getAcceptLanguages();
		foreach($browserLanguages as $lang){
			$lang = str_replace('-','_',explode(';', $lang)[0]);
			if($this->hasLanguage($lang)){
				return $lang;
			}
			
			if(($pos = strpos($lang, "_"))) {
				$lang = substr($lang, 0, $pos);
				if($this->hasLanguage($lang)){
					return $lang;
				}
			}
		}
		
		return go()->getSettings()->language; // from settings if we cant determine
	}

	private $af;
	public function getAddressFormat(string $isoCode) : string {

		$isoCode = strtoupper($isoCode);

		if(!isset($this->af)) {
			require(\go\core\Environment::get()->getInstallFolder() . '/language/addressformats.php');
			/** @noinspection PhpUndefinedVariableInspection */
			$this->af = $af;
		}

		return isset($this->af[$isoCode]) ? $this->af[$isoCode] : $this->af['default'];
	}

	private static $defaultCountryIso;

	public static function isDefaultCountry($countryCode): bool
	{
		return self::defaultCountry() == $countryCode;
	}

	/**
	 * Get default country ISO code
	 *
	 * @return string
	 */
	public static function defaultCountry() : string {
		if(!isset(self::$defaultCountryIso)) {
			$user = go()->getAuthState() ? go()->getAuthState()->getUser(['timezone']) : null;
			if(!$user) {
				$user = User::findById(1, ['timezone']);
			}
			self::$defaultCountryIso = $user->getCountry();
		}
		return self::$defaultCountryIso;
	}


	/**
	 * Format an address
	 *
	 * @param array $address array with address, city, zipCode and state
	 * @param string|null $countryCode
	 * @param boolean|null $showCountry When null it will be false if the country is the system default
	 * @return string
	 */
	public function formatAddress(array $address, ?string $countryCode, ?bool $showCountry = null) : string
	{
		if(empty($countryCode)) {
			$countryCode = self::defaultCountry();
		}

		// street and street2 still used for backwards compatibility
		if (empty($address['address']) && empty($address['street']) && empty($address['zipCode']) && empty($address['city']) && empty($address['state'])) {
			return "";
		}

		$format = go()->getLanguage()->getAddressFormat($countryCode);

		$format = str_replace('{address}', $address['address'] ?? $address['street'] ?? "", $format);
		$format = str_replace('{address_no}', $address['street2'] ?? "", $format);
		$format = str_replace('{city}', $address['city'] ?? "", $format);
		$format = str_replace('{zip}', $address['zipCode'] ?? "", $format);
		$format = str_replace('{state}', $address['state'] ?? "", $format);

		if (is_null($showCountry)) {
			$showCountry = !self::isDefaultCountry($countryCode);
		}

		if (!$showCountry) {
			$format = str_replace('{country}', "", $format);
		}else{
			$countries = go()->t('countries');
			$country = $countries[$countryCode] ?? "";
			$format = str_replace('{country}', $country, $format);
		}

		$format = preg_replace("/\s\n/", "\n", $format);
		return trim(preg_replace("/[\n]+/", "\n", $format));
	}

	public string $defaultPackage = "core";
	public string $defaultModule = "core";

	/**
	 * Translates a language variable name into the local language.
	 *
	 * @see App::t() For shorter access
	 *
	 * @param string $str String to translate
	 * @param string|null $package The module package name. Defaults to {@see Language::$defaultPackage}
	 * @param string|null $module Name of the module. Defaults to {@see Language::$defaultModule}
 */
	public function t(string $str, string|null $package = null, string|null $module = null) {

		if($package == null) {
			$package = $this->defaultPackage;
		}

		if($module == null) {
			$module = $this->defaultModule;
		}

		$this->loadSection($package, $module);
		
		//fallback on core lang
		if(!isset($this->data[$package]) || !isset($this->data[$package][$module]) || ($package != "core" && $module != "core" && !isset($this->data[$package][$module][$str]))) {
			return $this->t($str, "core", "core");
		}
		
		return $this->data[$package][$module][$str] ?? $this->replaceBrand($str);
	}


	/**
	 * Get the translation of a string or null if it doesn't exist
	 *
	 * @param string $str
	 * @param string|null $package
	 * @param string|null $module
	 * @return string|null
	 */
	public function getTranslation(string $str, ?string $package = null, ?string $module = null) : null|string|array {
		if($package == null) {
			$package = $this->defaultPackage;
		}

		if($module == null) {
			$module = $this->defaultModule;
		}

		$this->loadSection($package, $module);

		return $this->data[$package][$module][$str] ?? null;
	}
	
	public function translationExists($str, $package = 'core', $module = 'core') {
		$this->loadSection($package, $module);
		
		return isset($this->data[$package]) && isset($this->data[$package][$module]) && isset($this->data[$package][$module][$str]);
	}


	private function loadSection($package = 'core', $module = 'core') {
		if (!isset($this->data[$package])) {
			$this->data[$package] = [];
		} 
		
		$isoCode = $this->getIsoCode();

		if(!isset($this->data[$package][$module])) {

			$cacheKey = $isoCode .'-'.$package.'-'.$module;

			$this->data[$package][$module] = go()->getCache()->get($cacheKey);
			if($this->data[$package][$module] !== null) {
				return;
			}
			
			$langData = new util\ArrayObject();
			
			//Get english default
			$file = $this->findLangFile('en',$package, $module);
			if ($file->exists()) {
				$langData = new util\ArrayObject($this->loadFile($file));
			} else {
				go()->warn('No default(en) language file for module "'.$package.'/'.$module.'" defined.');
			}

			//overwirte english with actual language
			if ($isoCode != 'en') {
				$file = $this->findLangFile($isoCode, $package, $module);
				if ($file->exists()) {
					$langData->mergeRecursive($this->loadFile($file));
				}
			}

			$file = $this->findLangOverride($isoCode, $package, $module);
			if ($file && $file->exists()) {
				$langData->mergeRecursive($this->loadFile($file));
			}

			foreach ($langData as $key => $translation) {
				
				if(!is_string($translation)) {
					continue;
				}
							
				//branding
				$langData[$key]  = $this->replaceBrand($langData[$key]);
			}

			$this->data[$package][$module] = $langData->getArray();
			go()->getCache()->set($cacheKey, $this->data[$package][$module]);
		}
	}

	private function replaceBrand($str) {
		$productName = $this->replaceProductName ? go()->getConfig()['product_name'] : "{product_name}";
		return str_replace(
			[
				"{product_name}",
				"GroupOffice",
				"Group-Office",
				"Group Office"
			],
			[
				$productName,
				$productName,
				$productName,
				$productName

			], $str);
	}
	
	private function loadFile($file) {
		
		try {
			$langData = require($file);
		} catch(\ParseError $e) {
			ErrorHandler::logException($e);
			$langData = [];
		}
		if(!is_array($langData)){
			throw new \Exception("Invalid language file  " . $file);
		}
		
		return $langData;
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
		} else if($package == "core" && $module == "core") {
			$folder = Environment::get()->getInstallFolder()->getFolder('go/core/language');
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

		if(Installer::isInProgress()) {
			return false;
		}
		$admin = User::findById(1, ['homeDir']);

		$folder = go()->getDataFolder()->getFolder($admin->homeDir. '/language/' . $package . '/' .$module);
		
		return $folder->getFile($lang . '.php');
	}

	

	/**
	 * Get all supported languages.
	 * 
	 * @return array array('en'=>'English');
	 */
	public function getLanguages() {
		$languages = go()->getCache()->get('languages');
		if($languages !== null) {
			return $languages;
		}

		require(Environment::get()->getInstallFolder() . '/language/languages.php');
		asort($languages);

		go()->getCache()->set('languages', $languages);

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
