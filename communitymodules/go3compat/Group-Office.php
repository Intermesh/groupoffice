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
 * @version $Id: Group-Office.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This class holds the main configuration options of Group-Office
 * Don't modify this file. The values defined here are just default values.
 * They are overwritten by the configuration options in local/config.php.
 * To edit these options use install.php.
 *
 * @copyright Copyright Intermesh
 * @version $Id: Group-Office.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @since Group-Office 1.0
 * @package go.basic
 * @access public
 */
//throw new Exception('hier');



global $GO_CONFIG, $GO_INCLUDES, $GO_MODULES, $GO_SECURITY, $GO_LANGUAGE, $GO_EVENTS, $GO_THEME, $lang;

$root = dirname(__FILE__).'/';
require_once($root.'functions.inc.php');
require_once($root.'classes/base/config.class.inc.php');

//preload classes before session so they can be stored in the session
if ( isset( $GO_INCLUDES ) ) {

	//load configuration before session start because otherwise objects may be incomplete.
	//We rather start the session before creating GO_CONFIG because we can save the location
	//of config.php in the session. Otherwise we'll have to search for it every time.
	$GO_CONFIG = new GO_CONFIG();

	while ( $include = array_shift( $GO_INCLUDES ) ) {
		require_once( $include );
	}
}



if(!defined('GO_NO_SESSION') && session_id()==''){
	//start session
	session_name('groupoffice');
	session_start();
}

if(!isset($GO_CONFIG))
	$GO_CONFIG = new GO_CONFIG();




if(!$GO_CONFIG->enabled)
{
	die('<h1>Disabled</h1>This Group-Office installation has been disabled');
}



if($GO_CONFIG->debug)
{
	$_SESSION['connect_count']=0;
	$_SESSION['query_count']=0;
	
	if(class_exists('GO')){
		$trace = debug_backtrace();
		GO::debug("Warning: old Group-Office.php included\n".var_export($trace[0], true)."\n");
	}	
}


if(function_exists('mb_internal_encoding'))
	mb_internal_encoding("UTF-8");


if(!isset($_SESSION['DIR_CHECK']))
{
	$_SESSION['DIR_CHECK'] = $GO_CONFIG->root_path;
}elseif($_SESSION['DIR_CHECK'] != $GO_CONFIG->root_path)
{
	go_log(LOG_DEBUG, 'Session root path check failed. Stored root path in session: '.
	$_SESSION['DIR_CHECK'].' doesn\'t match the configured one: '.$GO_CONFIG->root_path);

	session_destroy();
	unset($_SESSION);
	$_SESSION['timezone']=$GO_CONFIG->default_timezone;
}

$GO_CONFIG->set_default_session();

require_once($GO_CONFIG->class_path.'base/exceptions.class.inc.php');
require_once($GO_CONFIG->class_path.'base/security.class.inc.php');
require_once($GO_CONFIG->class_path.'base/modules.class.inc.php');
require_once($GO_CONFIG->class_path.'base/language.class.inc.php');
require_once($GO_CONFIG->class_path.'base/events.class.inc.php');

if(!is_int($_SESSION['GO_SESSION']['timezone']))
{
	//set user timezone setting after user class is loaded
	date_default_timezone_set($_SESSION['GO_SESSION']['timezone']);
}
//after date_default_timezone otherwise date function might raise an error
//if(!class_exists('GO'))
//	go_debug('['.date('Y-m-d G:i').'] Start of new request: '.$_SERVER['REQUEST_URI']);
//go_debug($_POST);
if($GO_CONFIG->debug){
	function groupoffice_error_handler($errno, $errstr, $errfile, $errline, $errcontext) {

		// if error has been supressed with an @
    /*
		 * Doesn't seem to work
		 * if (error_reporting() == 0) {
        return;
    }*/

		$err_str = "PHP error: $errfile:$errline $errstr ($errno)";

		global $GO_CONFIG;

		if($GO_CONFIG->debug_display_errors){
			print $err_str;
			print php_sapi_name()=='cli' ? "\n" : '<br />';
		}

    go_debug($err_str);
	}

	error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
	ini_set('display_errors', "on");
	
	//set_error_handler("groupoffice_error_handler", E_ALL);
}


$GO_LANGUAGE = new GO_LANGUAGE();

$GO_MODULES = new GO_MODULES();
$GO_SECURITY = new GO_SECURITY();
if(!defined('NOTINSTALLED'))
{
	$GO_MODULES->load_modules();
}

/*
 * License checking for pro modules. Don't remove it or Group-Office will fail
 * to load!
 */
if((PHP_SAPI != 'cli' || PHP_SAPI == '') && file_exists($GO_CONFIG->root_path.'modules/professional/check.php')){
	require_once($GO_CONFIG->root_path.'modules/professional/check.php');
	check_license();
}

$GO_EVENTS = new GO_EVENTS();

//if($GO_CONFIG->session_inactivity_timeout>0){
//	$now = time();
//	if(isset($_SESSION['last_activity']) && $_SESSION['last_activity']+$GO_CONFIG->session_inactivity_timeout<$now){
//		$GO_SECURITY->logout();
//	}elseif(isset($_POST['task']) && $_POST['task']!='checker')//don't update on the automatic checker function that runs every 2 mins.
//	{
//		$_SESSION['last_activity']=$now;
//	}
//}


if (!empty($_REQUEST['SET_LANGUAGE'])){
		$GO_LANGUAGE->set_language($_REQUEST['SET_LANGUAGE']);
}

require($GO_LANGUAGE->get_base_language_file('common'));
//require($GO_LANGUAGE->get_base_language_file('filetypes'));

if ( $GO_CONFIG->log ) {
	$username = isset($_SESSION['GO_SESSION']['username']) ? $_SESSION['GO_SESSION']['username'] : 'notloggedin';
	openlog('[Group-Office]['.date('Ymd G:i').']['.$username.']', LOG_PERROR, LOG_USER);
}

if($GO_SECURITY->user_id>0)
{
	$GO_CONFIG->tmpdir=$GO_CONFIG->tmpdir.$GO_SECURITY->user_id.'/';
}


unset($type);

define('GO_LOADED', true);

//undo magic quotes if magic_quotes_gpc is enabled. It should be disabled!
if (get_magic_quotes_gpc() && !class_exists('GO'))
{
	
	function stripslashes_array($data) {
		if (is_array($data)){
			foreach ($data as $key => $value){
				$data[$key] = stripslashes_array($value);
			}
			return $data;
		}else{
			return stripslashes($data);
		}
	}

	$_REQUEST=stripslashes_array($_REQUEST);
	$_GET=stripslashes_array($_GET);
	$_POST=stripslashes_array($_POST);
	$_COOKIE=stripslashes_array($_COOKIE);
}

umask(0);


if($GO_SECURITY->user_id && !isset($_SESSION['GO_SESSION']['go37compat'])){
	require_once($GO_CONFIG->root_path.'go3compat/classes/base/users.class.inc.php');
	$GO_USERS = new GO_USERS();
	$user = $GO_USERS->get_user($GO_SECURITY->user_id);
	$GO_USERS->update_session($user);
	
	$GO_MODULES->load_modules();
	
	$_SESSION['GO_SESSION']['go37compat']=true;
}


//todo this would be the new code:

//if(!class_exists('GO')){
//	$root = dirname(__FILE__).'/';
//	require_once($root.'go/GO.php');
//	GO::init();
//}
