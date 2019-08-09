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
		//$this->setLanguage();
	}
	
	/**
	 * Set the language to translate into. Clears the cached language strings too.
	 * 
	 * @param StringHelper $isoCode Leave empty to set the default user language.
	 * @return StringHelper Old ISO code that was set.
	 */
	public function setLanguage($isoCode = null){		
		if(!isset($isoCode) || \GO()->getLanguage()->hasLanguage($isoCode)) {
			return \GO()->getLanguage()->setLanguage($isoCode);	
		}

		return $this->_langIso;
		
	}
	
	
	
	/**
	 * @return StringHelper Language ISO code. eg. en,nl or en_UK
	 */
	public function getLanguage(){
		return 	\GO()->getLanguage()->getIsoCode();
	}
	
	
	/**
	 * Check if language is supported
	 * 
	 * @param StringHelper $langIso
	 * @return boolean 
	 */
	public function hasLanguage($langIso){
		return 	\GO()->getLanguage()->hasLanguage($langIso);
	}
	
	/**
	 * Translates a language variable name into the local language.
	 * 
	 * Note: You can use \GO::t() instead. It's a shorter alias.
	 * 
	 * @param String $name Name of the translation variable
	 * @param String $module Name of the module to find the translation
	 * @param String $package Only applies if module is set to 'base'
	 * @param boolean $found Pass by reference to determine if the language variable was found in the language file.
	 */
	public function getTranslation($name, $module='core',$package = 'core', &$found=false) {		
		return GO()->t($name, $package, $module);
	}
		
	/**
	 * Get all supported languages.
	 * 
	 * @return array array('en'=>'English');
	 */
	public function getLanguages(){
		return \GO()->getLanguage()->getLanguages();
	}
	
	/**
	 * Get all countries
	 * 
	 * @return array array('nl'=>'The Netherlands');
	 */
	public function getCountries(){
		return GO()->t('countries');
	}
	
}
