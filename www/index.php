<?php

use go\core\model\Token;
use go\core\http\Request;
use go\core\ErrorHandler;

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

/**
 * This file loads the web client
 */


function errorHander($e) {
	if(!Request::get()->isXHR() && (empty($_REQUEST['r']) || $_REQUEST['r'] != 'maintenance/upgrade')) {
		
		$msg = ErrorHandler::logException($e);

		if(go()->getDebugger()->enabled) {

			echo "Showing error message because debug is enabled. Normally we would have redirected to install. I you're doing a freah install and your database is empty then you can safely ignore this.:\n\n";
			echo $msg;
			echo '<a href="/install">Click here to launch the installer</a>';
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


try {
  //initialize autoloading of library
  require('GO.php');  
	
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

		setcookie('accessToken', $token->accessToken, null, "/", Request::get()->getHost(), false, false);

		date_default_timezone_set($old);
	}

	//check if GO is installed
	if(empty($_REQUEST['r']) && PHP_SAPI!='cli'){	
		
		if(GO::user() && isset($_SESSION['GO_SESSION']['after_login_url'])){
			$url = GO::session()->values['after_login_url'];
			unset(GO::session()->values['after_login_url']);
			header('Location: '.$url);
			exit();
		}
		
		if(go()->getSettings()->databaseVersion != go()->getVersion()) {
			header('Location: '.GO::config()->host.'install/upgrade.php');				
			exit();
		}
	}

} catch(Error $e) {
  errorHander($e);  
} catch(Exception $e) {
  errorHander($e);  
}


GO::router()->runController();
