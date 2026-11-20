<?php

use go\core\webclient\Startup;

/**
 * Copyright Intermesh
 *
 * This file is part of GroupOffice. You should have received a copy of the
 * GroupOffice license along with GroupOffice. See the file /LICENSE.TXT
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


try {
  //initialize autoloading of library
  require('GO.php');  
	


	//check if GO is installed
	if(empty($_REQUEST['r']) && PHP_SAPI!='cli'){

		Startup::run();
	}

	GO::router()->runController();

} catch(Throwable $e) {
	Startup::handleException($e);
}
