<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * 
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: calendar.php 22576 2017-10-19 14:08:17Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

//session writing doesn't make any sense because
define("GO_NO_SESSION", true);

// settings
require('../../GO.php');
//\GO::config()->debug = true;

//if(\GO::config()->debug){
//	\GO::debug(file_get_contents('php://input'));
//
//	\GO::debug($_POST);
//	\GO::debug($_SERVER);
//}

if (!\GO::modules()->isInstalled('dav')){
	$msg = 'DAV module not installed. Install it at Start menu -> Apps.';
	
	trigger_error($msg, E_USER_WARNING);
	
	exit($msg);
}

if (!\GO::modules()->isInstalled('caldav')){
	$msg = 'CalDAV module not installed. Install it at Start menu -> Apps.';
	
	trigger_error($msg, E_USER_WARNING);
	
	exit($msg);
}

/* Backends */
//if(empty(\GO::config()->webdav_auth_basic)) {
//	$authBackend = new \GO\Dav\Auth\Backend();
//}else
//{
	$authBackend = new \GO\Dav\Auth\BasicBackend();
//}
$authBackend->checkModulePermission('legacy','caldav');
$calendarBackend = new \GO\Caldav\CalendarsBackend();
$principalBackend = new \GO\Dav\DavAcl\PrincipalBackend();

$tree = array(
		new \Sabre\CalDAV\Principal\Collection($principalBackend),
		new \Sabre\CalDAV\CalendarRoot($principalBackend, $calendarBackend),
);

$server = new Sabre\DAV\Server($tree);

$server->debugExceptions = \GO::config()->debug;

$server->on('exception', function($e){
	\GO::debug((string) $e);
});

//baseUri can also be /caldav/ with:
//Alias /caldav/ /path/to/calendar.php
$baseUri = strpos($_SERVER['REQUEST_URI'], 'calendar.php') ? \GO::config()->host . 'modules/caldav/calendar.php/' : '/caldav/';
$server->setBaseUri($baseUri);


// Authentication plugin
$authPlugin = new Sabre\DAV\Auth\Plugin($authBackend);
$server->addPlugin($authPlugin);

// CalDAV plugin
$caldavPlugin = new Sabre\CalDAV\Plugin();
$server->addPlugin($caldavPlugin);

// ACL plugin
$aclPlugin = new Sabre\DAVACL\Plugin();
$aclPlugin->allowUnauthenticatedAccess = false;
$server->addPlugin($aclPlugin);

$server->addPlugin(
    new Sabre\CalDAV\Schedule\Plugin()
);
$imipPlugin = new \GO\Caldav\Schedule\IMipPlugin();
$server->addPlugin($imipPlugin);

//WebDAV Sync
//$server->addPlugin(
//    new Sabre\DAV\Sync\Plugin()
//);

// Support for html frontend
$browser = new Sabre\DAV\Browser\Plugin();
$server->addPlugin($browser);

// And off we go!
$server->exec();