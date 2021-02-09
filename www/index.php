<?php

use go\core\auth\model\Token;
use go\core\exception\ConfigurationException;
use go\core\http\Request;
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: index.php 8246 2011-10-05 13:55:38Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//$root = dirname(__FILE__).'/';

try {
  //initialize autoloading of library
  require_once('GO.php');
  //\GO::init();


if(!empty($_POST['accessToken'])) {
	$old = date_default_timezone_get();
	date_default_timezone_set('UTC');
	//used for direct token login from multi_instance module
	//this token is used in default_scripts.inc.php too
	$token = Token::find()->where('accessToken', '=', $_POST['accessToken'])->single();
	if($token) {
		$token->setAuthenticated();
	} else
	{
		unset($_POST['accessToken']);
	}

	date_default_timezone_set($old);
}


//try with HTTP auth
//if(!\GO::user() && !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])){
//	if(\GO::session()->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {	
//		//security token not requireed when using basic auth.
//		$_REQUEST['security_token'] = \GO::session()->securityToken();
//	}
//}

//check if GO is installed
if(empty($_REQUEST['r']) && PHP_SAPI!='cli'){	
	
	if(GO::user() && isset($_SESSION['GO_SESSION']['after_login_url'])){
		$url = GO::session()->values['after_login_url'];
		unset(GO::session()->values['after_login_url']);
		header('Location: '.$url);
		exit();
	}
	
	if(GO()->getSettings()->databaseVersion != GO()->getVersion()) {
		header('Location: '.GO::config()->host.'install/upgrade.php');				
		exit();
	}
	
//	$installed=true;
//	if(!\GO::config()->get_config_file() || empty(\GO::config()->db_user)){			
//		$installed=false;
//	}else
//	{
//		$stmt = \GO::getDbConnection()->query("SHOW TABLES");
//		if(!$stmt->rowCount())
//			$installed=false;
//	}
//	if(!$installed){
//		header('Location: '.\GO::config()->host.'install/');				
//		exit();
//	}

//	//check for database upgrades
//	$mtime = \GO::config()->get_setting('upgrade_mtime');
//
//	if($mtime!=\GO::config()->mtime)
//	{
//		\GO::infolog("Running system update");
//		header('Location: '.\GO::url('maintenance/upgrade'));
//		exit();
//	}
}

} catch(Exception $e) {
  
  if(($e instanceof PDOException || $e instanceof ConfigurationException) &&  !Request::get()->isXHR() && (empty($_REQUEST['r']) || $_REQUEST['r'] != 'maintenance/upgrade')) {

	  $msg = \go\core\ErrorHandler::logException($e);

	  if(go()->getDebugger()->enabled) {

		  echo "DEBUGGER: Showing error message because debug is enabled. Normally we would have redirected to install. I you're doing a freah install and your database is empty then you can safely ignore this.:<br /><br />";
		  echo $msg;
		  echo "<pre>" . $e->getTraceAsString() . "</pre>";
		  echo '<br /><br /><a href="install">Click here to launch the installer</a>';
		  exit();
	  }

    header('Location: install/');				
    exit();
  } else
  {
		echo "<h1>Fatal error</h1>";
		echo "<pre>";
    echo $e->getMessage();		
		//echo $e->getTraceAsString();
		echo "</pre>";
  }
}

GO::router()->runController();
