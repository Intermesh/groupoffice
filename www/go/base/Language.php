<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */

/**
 * Translates variables into localized strings
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base
 */
 

namespace GO\Base;

use Exception;
use GO;
use GO\Base\Fs\Folder;
use GO\Base\Util\ArrayUtil;
use GO\Base\Util\StringHelper;


class Language{
	
	private $_langIso='en';
	private $_lang;
	
	
	public function __construct() {
		$this->setLanguage();
	}
	
	/**
	 * Set the language to translate into. Clears the cached language strings too.
	 * 
	 * @param StringHelper $isoCode Leave empty to set the default user language.
	 * @return StringHelper Old ISO code that was set.
	 */
	public function setLanguage($isoCode=false){
		
		$oldIso = $this->_langIso;
		
		if(!$isoCode){
			if(isset($_REQUEST['SET_LANGUAGE'])){
				$this->_langIso=$_REQUEST['SET_LANGUAGE'];
//			}elseif(GO::user()){
//				$this->_langIso=GO::user()->language;
			}else{
				$this->_langIso=$this->_getDefaultLanguage();
			}
		}else
		{
			$this->_langIso=$isoCode;
		}
		
		//validate given language string
		if(!preg_match('/^[a-z_-]+$/i', $this->_langIso)){
			throw new Exception("Invalid language iso code given (".$this->_langIso);
		}
		
		if($oldIso!=$this->_langIso)
			$this->_lang=array();
		
		return $oldIso;
	}
	
	private function _getDefaultLanguage(){
		$browserLanguages=array();
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$browserLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		}

		foreach($browserLanguages as $lang){
			//turn "en-US;q=0.8" into "en_US"
			$lang = str_replace('-', '_', $lang);
			$lang = explode(';', $lang)[0];
			
			if($this->hasLanguage($lang)) {
				return $lang;
			}
			
			if(strpos($lang, '_')) {
				$lang = explode('_', $lang)[0];
				if($this->hasLanguage($lang)) {
					return $lang;
				}

			}
		}
		
			
		return GO::config()->language;		
	}
	
	/**
	 * @return StringHelper Language ISO code. eg. en,nl or en_UK
	 */
	public function getLanguage(){
		return $this->_langIso;
	}
	
	public function getScript() {

		$language = $this->_langIso;

		$str = "var GO = GO || {};\n";

		$extjsLang = GO::t("extjs_lang");
		if ($extjsLang == 'extjs_lang')
			$extjsLang = $language;

		$view_root_path = GO::config()->root_path . 'views/Extjs3/';


		if (file_exists($view_root_path . 'ext/src/locale/ext-lang-' . $extjsLang . '.js')) {
			$str .= file_get_contents($view_root_path . 'ext/src/locale/ext-lang-' . $extjsLang . '.js');
		}

		require(GO::config()->root_path . 'language/languages.php');
		$str .= "GO.Languages=[];\n";

		foreach ($languages as $code => $language) {
			$str .= 'GO.Languages.push(["' . $code . '","' . $language . '"]);' . "\n";
		}

		//Put all lang vars in js		
		$l = $this->getAllLanguage();
		$l['iso'] = $this->_langIso;

		$str .= 'GO.lang = ' . json_encode($l, JSON_PRETTY_PRINT) . ";\n";
//		$str .= 'GO.lang.countries=' . json_encode($l['base']['countries'], JSON_PRETTY_PRINT) . ";\n";
//		unset($l['base']);
//
//		foreach ($l as $module => $langVars) {
//			$str .= 'Ext.ns("GO.' . $module . '");' . "\n";
//			$str .= 'GO.' . $module . '.lang=' . json_encode($langVars, JSON_PRETTY_PRINT) . ';' . "\n";
//		}

		return $str;
	}

	/**
	 * Check if language is supported
	 * 
	 * @param StringHelper $langIso
	 * @return boolean 
	 */
	public function hasLanguage($langIso){
		return $this->_find_file($langIso, 'base', 'common');
	}
	
	/**
	 * Translates a language variable name into the local language.
	 * 
	 * Note: You can use \GO::t() instead. It's a shorter alias.
	 * 
	 * @param String $name Name of the translation variable
	 * @param String $module Name of the module to find the translation
	 * @param String $basesection Only applies if module is set to 'base'
	 * @param boolean $found Pass by reference to determine if the language variable was found in the language file.
	 */
	public function getTranslation($name, $module='base',$basesection='common', &$found=false){
		
		$this->_loadSection($module, $basesection);		
		
		if($module=='base'){
			if(isset($this->_lang[$module][$basesection][$name])){
				$found=true;
				$translation=$this->_lang[$module][$basesection][$name];
			}else
			{
				$found = false;
				return $name;
			}
		}else
		{
			if(isset($this->_lang[$module][$name])){
				$found=true;
				$translation=$this->_lang[$module][$name];
			}else
			{
				$found = false;
				$translation=$name;
			}
		}
		
		return str_replace('{product_name}',GO::config()->product_name,$translation);
	}
	
	private function _replaceProductName($l){
		foreach($l as $key=>$value)
			$l[$key]=str_replace('{product_name}',GO::config()->product_name,$value);
		return $l;
	}
	
	private function _loadSection($module='base',$basesection='common'){
		if(!isset($this->_lang[$module]) || ($module=='base' && !isset($this->_lang[$module][$basesection]))){
			
			$file = $this->_find_file('en', $module, $basesection);
			if($file) {
				$ret = require($file);
				if(is_array($ret)) {
					$l = $ret;
				}
			}
			
			//$langcode = \GO::user() ? \GO::user()->language : \GO::config()->language;
			$defaultLang=isset($l) ? $l : array();
			unset($l);
			
			if($this->_langIso!='en')
			{
				$file = $this->_find_file($this->_langIso, $module, $basesection);
				if($file){
					$ret = require($file);
					
					if(is_array($ret)) {						
						$l = $ret;						
					}
					if(isset($l)){
						$defaultLang = ArrayUtil::mergeRecurive($defaultLang, $l);
						unset($l);
					}
				}
			}		
			
			$file = $this->_find_override_file($this->_langIso, $module, $basesection);
			if($file){
				$ret = require($file);
				if(is_array($ret)) {
					$l = $ret;
				}
				if(isset($l)){
					$defaultLang = ArrayUtil::mergeRecurive($defaultLang, $l);
					unset($l);
				}
			}
			

			if($module=='base'){
				$this->_lang[$module][$basesection]=$this->_replaceProductName($defaultLang);
			}else
			{
				$this->_lang[$module]=$this->_replaceProductName($defaultLang);
			}
			
		}
	}
	
	private function _find_file($lang, $module, $basesection){
		if($module=='base')
			$dir=GO::config()->root_path.'language/'.$basesection.'/';
		else {
//			$dir=GO::config()->root_path.'modules/'.$module.'/language/';
			
			//for refactored modules
			if(GO::modules()->$module) {
				$dir=GO::modules()->$module->path.'language/';
			} else
			{
				$dir=GO::config()->root_path.'modules/'.$module.'/language/';
			}
			
			
		}
				
		$file = $dir.$lang.'.php';
		
		if(file_exists($file))
			return $file;
		else
			return false;
	}
	
	private function _find_override_file($lang, $module, $basesection){
		
		$dir=GO::config()->file_storage_path.'users/admin/lang/'.$lang.'/';		
		$filename = $module=='base' ? 'base_'.$basesection.'.php' : $module.'.php';
						
		$file = $dir.$filename;
		
		if(file_exists($file))
			return $file;
		

		$dir=GO::config()->file_storage_path.'users/admin/lang/';		

		$file = $dir.$filename;

		if(file_exists($file))
			return $file;			
		
		
		return false;
	}
	
	
	public function getAllLanguage(){
		$folder = new Folder(GO::config()->root_path.'language');
		$items = $folder->ls();
		foreach($items as $folder){
			if($folder instanceof Folder){
				$this->_loadSection('base', $folder->name());
			}
		}
		
		//always load users lang for settings panels
		$this->_loadSection('users');
		
		$modules = GO::modules()->getAllModules(true);			
		while ($module=array_shift($modules)) {
			$this->_loadSection($module->name);
		}
		
		return $this->_lang;
	}
	
	/**
	 * Get all supported languages.
	 * 
	 * @return array array('en'=>'English');
	 */
	public function getLanguages(){
		require(GO::config()->root_path.'language/languages.php');
		asort($languages);
		return $languages;
	}
	
	/**
	 * Get all countries
	 * 
	 * @return array array('nl'=>'The Netherlands');
	 */
	public function getCountries(){
		$this->_loadSection('base','countries');
		asort($this->_lang['base']['countries']);
		return $this->_lang['base']['countries'];
	}
	
}