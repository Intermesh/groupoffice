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
use go\core\dav\auth\BasicBackend;
use go\core\ErrorHandler;
use GO\Dav\Fs\RootDirectory;
use GO\Dav\Locks\LocksBackend;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Browser\Plugin;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\Locks\Plugin as LockPlugin;

define("GO_NO_SESSION", true);

// settings
require('../../GO.php');

// Authentication backend
$authBackend = new BasicBackend();

if (!\GO::modules()->isInstalled('dav')){
	$msg = 'DAV module not installed. Install it at Start menu -> Apps.';
	
	trigger_error($msg, E_USER_WARNING);
	
	exit($msg);
}

$root = new RootDirectory();

// The rootnode needs in turn to be passed to the server class
$server = new Sabre\DAV\Server($root);
$server->debugExceptions=\GO::config()->debug;

go()->getDebugger()->setRequestId("WebDAV " . ($_SERVER['REQUEST_METHOD'] ?? ""));

$server->on('exception', function($e){
	if(!($e instanceof NotAuthenticated) && !($e instanceof NotFound)) {
		ErrorHandler::logException($e);
	}
});

//baseUri can also be /webdav/ with:
//Alias /webdav/ /path/to/files.php
$baseUri = strpos($_SERVER['REQUEST_URI'],'files.php') ? \GO::config()->host . 'modules/dav/files.php/' : '/webdav/';
$server->setBaseUri($baseUri);

// Support for LOCK and UNLOCK
if(empty(go()->getConfig()['webdavEnableLocks'])) {
	$lockBackend = new Sabre\DAV\Locks\Backend\PDO(\GO::getDbConnection());
	$lockBackend->tableName = 'dav_locks';
} else {
	$lockBackend = new LocksBackend($server);
}

$lockPlugin = new LockPlugin($lockBackend);
$server->addPlugin($lockPlugin);

// Support for html frontend
$browser = new Plugin();
$server->addPlugin($browser);

// Automatically guess (some) contenttypes, based on extesion
$server->addPlugin(new \Sabre\DAV\Browser\GuessContentType());

//$server->addPlugin(new \Sabre\DAV\TemporaryFileFilterPlugin((string) go()->getTmpFolder()->getFolder('sabredav')));

$auth = new AuthPlugin($authBackend);
$server->addPlugin($auth);

if(go()->getDebugger()->enabled) {
	$server->on("exception", function($e) {
		ErrorHandler::logException($e);
	});
}

$server->start();
