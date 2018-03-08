<?php
/** 
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: language.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.basic
 */


/**
 * This class is used to include language files according to the user's preference
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: language.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @package go.basic
 * @since Group-Office 1.0
 */

class GO_LANGUAGE extends db {
	/**
	 * The current language setting
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $language;

	/**
	 * The path to the common language files
	 *
	 * @var     StringHelper
	 * @access  private
	 */
	var $language_path;

	/**
		* The default language
		*
		* @var     StringHelper
		* @access  private
		*/
	var $default_language;

	/**
	 * Constructor. Initialises language setting and checks in following order:
	 * User preference (Session), Browser language setting, default language
	 *
	 * @access public
	 * @return StringHelper 	language code (See developer guidelines for codes)
	 */
	function __construct() {

		parent::__construct();

		
		global $GO_CONFIG;

		$this->language_path = $GLOBALS['GO_CONFIG']->root_path.'go3compat/'.$GLOBALS['GO_CONFIG']->language_path.'/';
		$this->default_language = $GLOBALS['GO_CONFIG']->language;

		if (!empty($_SESSION['GO_SESSION']['language'])) {
			$this->language = $_SESSION['GO_SESSION']['language'];
		}elseif(isset($_COOKIE['GO_LANGUAGE']) && $this->set_language($_COOKIE['GO_LANGUAGE']))
		{
			return $this->language;
				
		}elseif (isset ($_SERVER['HTTP_ACCEPT_LANGUAGE']))
		{
			$browser_languages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			if($this->set_language($browser_languages[0]))
			{
				return $this->language;					
			}else
			{
				$this->set_language($this->default_language);
				return $this->language;
			}
		}

		
	}

	/**
	 *	Set the language for this browser session
	 *
	 *	@param  $language	The language code (See developer guidelines for codes)
	 * @access public
	 * @return string	language code
	 */
	function set_language($language) {

		go_debug($language);
		if($this->language==$language)
			return true;

		global $lang, $GO_LANGUAGE;

		$f = file_exists($this->language_path.'common/'.$language.'.inc.php');
		if(!$f){
			require($this->language_path.'languages.inc.php');
			if(isset($language_aliases[$language])){
				$language=$language_aliases[$language];
				$f = file_exists($this->language_path.'common/'.$language.'.inc.php');
			}
		}

		if($f)
		{
			$this->language=$_SESSION['GO_SESSION']['language']=$language;

//			if(!isset($_COOKIE['GO_LANGUAGE']) || $_COOKIE['GO_LANGUAGE']!=$this->language)
//			{
//				$_COOKIE['GO_LANGUAGE']=$this->language;
//				SetCookie("GO_LANGUAGE",$language,time()+3600*24*30,"/",'',!empty($_SERVER['HTTPS']),true);
//			}
			if(is_object($GO_LANGUAGE))
				require($GLOBALS['GO_LANGUAGE']->get_base_language_file('common'));
				
			return true;
		}else
		{
			return false;
		}

	}

	/**
	 *	Get's a language file from the framework (Not a module)
	 *
	 *	@param  $section	The section to fetch language for. (See dirs in 'language')
	 * @access public
	 * @return string	Full path to the language file
	 */
	function get_base_language_file($section, $language=null) {
		global $GO_CONFIG;
		
		if(!isset($language))
			$language = $this->language;
		
		$file = $this->language_path.$section.'/'.$language.'.inc.php';
		if (file_exists($file)) {
			return $file;
		} else {
			return $this->get_fallback_base_language_file($section);
		}
	}

	/**
	 *	Get's the default language file from the framework (Not a module).
	 * This is always included before the prefered language file.
	 * If the prefered language file misses some strings they will be
	 * defined by the default language.
	 *
	 *	@param  $section	The section to fetch language for. (See dirs in 'language')
	 * @access public
	 * @return string	Full path to the fallback language file
	 */
	function get_fallback_base_language_file($section) {
		global $GO_CONFIG;

		$file = $this->language_path.$section."/en.inc.php";
		if (file_exists($file)) {
			return $file;
		} else {
			return false;
		}
	}

	/**
	 *	Get's a language file from a module
	 *
	 *	@param  $module_id	The module to fetch language for.
	 * @access public
	 * @return string	Full path to the language file
	 */
	function get_language_file($module_id, $language=null) {
		global $GO_CONFIG;

		/*
		 The new language file location is inside the language folder in the
		 modules folder. So we create the absolute path to the file and check
		 if this file exists.
		 */
		if(!isset($language))
			$language = $this->language;

		$module_path3 = $GLOBALS['GO_CONFIG']->root_path.'go3compat/modules/'.$module_id;
		$module_path_new = $GLOBALS['GO_CONFIG']->module_path.$module_id;
		$file3 = $module_path3.'/language/'.$language.'.inc.php';
		$file_new = $module_path_new.'/language/'.$language.'.inc.php';

		if (file_exists($file3)) {
			return $file3;
		} else if (file_exists($file_new)) {
			return $file_new;
		} else{
			return $this->get_fallback_language_file($module_id);
		}
	}

	function require_language_file($module_id, $language=null){
		global $GO_LANGUAGE, $lang, $GO_EVENTS;

		$lang_file = $this->get_language_file($module_id, $language);
		
		if(!$lang_file)
			return false;
		
		require($lang_file);
			
		$args=array($module_id, $language);
		$GLOBALS['GO_EVENTS']->fire_event('require_language_file', $args);
	}

	/**
	 *	Get's the prefered language file.
	 *
	 *	@param  $section	The section to fetch language for. (See dirs in 'language')
	 * @access public
	 * @return string	Full path to the language file
	 */
	function get_fallback_language_file($module_id) {
		global $GO_CONFIG;

		$module_path3 = $GLOBALS['GO_CONFIG']->root_path.'go3compat/modules/'.$module_id;
		$module_path_new = $GLOBALS['GO_CONFIG']->module_path.$module_id;
		$file3 = $module_path3.'/language/en.inc.php';
		$file_new = $module_path_new.'/language/en.inc.php';

		if (file_exists($file3)) {
			return $file3;
		} else if (file_exists($file_new)) {
			return $file_new;
		} else {
			return false;
		}
	}
	
	function get_all()
	{
		global $lang, $GO_MODULES, $GO_LANGUAGE;
		
		foreach($GLOBALS['GO_MODULES']->modules as $module)
		{
			$lang_file = $this->get_language_file($module['id']);
			if($lang_file)
				require($lang_file);
		}	
	}

	function get_address_formats()
	{
		$sql = "SELECT * FROM go_iso_address_format";
		$this->query($sql);
		return $this->num_rows();
	}

	function get_address_format_by_iso($iso)
	{
		$sql = "SELECT i.*,f.format FROM go_iso_address_format i INNER JOIN go_address_format f ON f.id=i.address_format_id WHERE `iso`='$iso'";
		$this->query($sql);
		return $this->next_record();
	}

	function get_address_format($id)
	{
		$sql = "SELECT format FROM go_address_format WHERE id=$id";
		$this->query($sql);
		$this->next_record();
		return $this->f('format');
	}

	/**
	 * Returns the formatted address extracted from the given company/contact/user
	 * record and an existing address format.
	 * @param DB_record $c Must be either a record from ab_companies, ab_contacts
	 * or go_users
	 * @param Int format_id id to the go_address_format table. This isn't used if
	 * the first argument, the record, contains an address_format field.
	 * @return String The formatted address.
	 */
	public function format_address($record,$format_id=1) {
		$values = array('address_no', 'address', 'zip', 'city', 'state', 'country');

		if (!isset($record['address_format'])) {
			$f = $this->get_address_format($format_id);
			$formatted_address = $f['format'];
		} else {
			$formatted_address = $record['address_format'];
		}

		foreach($values as $val)
			$formatted_address = str_replace('{'.$val.'}', $record[$val], $formatted_address);

		return $formatted_address;
	}
}
