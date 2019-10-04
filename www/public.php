<?php

use go\core\App;
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
 * @version $Id: public.php 16414 2013-12-05 13:13:44Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This file is a proxy for the /public alias defined in 
 * /etc/apache2/sites-enabled/000-groupoffice
 * 
 * It solves the problem that /public should lead to different home directories
 * on the different servermanager installations.
 */

require(__DIR__ . "/vendor/autoload.php");
App::get();

$file = go()->getDataFolder()->getFile(rawurldecode($_SERVER['REQUEST_URI']));

if (!$file->exists()) {
	header('HTTP/1.0 404 Not found');

	echo 'Not found: ' . $_SERVER['REQUEST_URI'];
	exit();
}

$file->output();
