<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: calendar.php 5573 2010-08-13 14:38:32Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//session writing doesn't make any sense because
define("GO_NO_SESSION", true);


// settings
require('../../GO.php');

//require_once \GO::config()->root_path.'go/vendor/SabreDAV/lib/Sabre/autoload.php';


// Authentication backend
$authBackend = new \GO\Dav\Auth\BasicBackend();


if (!\GO::modules()->isInstalled('dav')){
	$msg = 'DAV module not installed. Install it at Start menu -> Apps.';
	
	trigger_error($msg, E_USER_WARNING);
	
	exit($msg);
}

$root = new \GO\Dav\Fs\RootDirectory();

//$root = new \GO\Dav\Fs\Directory("projects2");

//$tree = new \GO\Dav\ObjectTree($root);

// The rootnode needs in turn to be passed to the server class
$server = new Sabre\DAV\Server($root);
$server->debugExceptions=\GO::config()->debug;

$server->on('exception', function($e){
	\GO::debug((string) $e);
});

//baseUri can also be /webdav/ with:
//Alias /webdav/ /path/to/files.php
$baseUri = strpos($_SERVER['REQUEST_URI'],'files.php') ? \GO::config()->host . 'modules/dav/files.php/' : '/webdav/';
$server->setBaseUri($baseUri);


$tmpDir = \GO::config()->getTempFolder()->parent()->createChild('dav',false);
$locksDir = $tmpDir->createChild('locksdb', false);
$locksDir->create();

// Support for LOCK and UNLOCK
//$lockBackend = new Sabre\DAV\Locks\Backend\FS($locksDir->path());
$lockBackend = new Sabre\DAV\Locks\Backend\PDO(\GO::getDbConnection());
$lockBackend->tableName = 'dav_locks';
$lockPlugin = new Sabre\DAV\Locks\Plugin($lockBackend);
$server->addPlugin($lockPlugin);

// Support for html frontend
$browser = new Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

// Automatically guess (some) contenttypes, based on extesion
$server->addPlugin(new \Sabre\DAV\Browser\GuessContentType());

$auth = new Sabre\DAV\Auth\Plugin($authBackend,\GO::config()->product_name);
$server->addPlugin($auth);

// Temporary file filter
//$tempFF = new Sabre\DAV\TemporaryFileFilterPlugin($tmpDir->path());
//
//// Add regex for Office lock files
//$tempFF->temporaryFilePatterns[] = '/^~\$.*$/';

//$server->addPlugin($tempFF);

// And off we go!

if(go()->getDebugger()->enabled) {
	$server->on("exception", function($e) {
		\go\core\ErrorHandler::logException($e);
	});
}

$server->exec();
