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
 * @version $Id: index.php 8246 2011-10-05 13:55:38Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

$root = dirname(__FILE__).'/';

try {
  //initialize autoloading of library
  require_once('GO.php');
  //\GO::init();
} catch(\PDOException $e) {
  
  if(!\go\core\http\Request::get()->isXHR() && (empty($_REQUEST['r']) || $_REQUEST['r'] != 'maintenance/upgrade')) {
    header('Location: install/');				
    exit();
  } else
  {
    throw $e;
  }
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
	
	if(\GO::user() && isset($_SESSION['GO_SESSION']['after_login_url'])){
		$url = \GO::session()->values['after_login_url'];
		unset(\GO::session()->values['after_login_url']);
		header('Location: '.$url);
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

\GO::router()->runController();