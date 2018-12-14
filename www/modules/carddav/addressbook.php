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
 * @author WilmarVB <wilmar@intermesh.nl>
 * @author Merijn Schering <mschering@intermesh.nl>
 */


//session writing doesn't make any sense because carddav clients relogin on each request
define("GO_NO_SESSION", true);

// settings
require('../../GO.php');

if (!\GO::modules()->isInstalled('dav')){
	$msg = 'DAV module not installed. Install it at Start menu -> Apps.';
	
	trigger_error($msg, E_USER_WARNING);
	
	exit($msg);
}

if (!\GO::modules()->isInstalled('carddav')){
	$msg = 'CardDAV module not installed. Install it at Start menu -> Apps.';
	
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
$authBackend->checkModuleAccess='carddav';

$principalBackend = new \GO\Dav\DavAcl\PrincipalBackend();
$carddavBackend = new \GO\CardDAV\AddressbooksBackend();

// Setting up the directory tree //
$nodes = array(
		new Sabre\DAVACL\PrincipalCollection($principalBackend),
		new Sabre\CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
);


/* Initializing server */
$server = new Sabre\DAV\Server($nodes);
$server->debugExceptions = \GO::config()->debug;
$server->on('exception', function($e){
	\GO::debug((string) $e);
});

/* Server Plugins */
$server->addPlugin(new Sabre\DAV\Auth\Plugin($authBackend, \GO::config()->product_name));
$server->addPlugin(new Sabre\CardDAV\Plugin());
$server->addPlugin(new Sabre\DAVACL\Plugin());

//baseUri can also be /carddav/ with:
//Alias /carddav/ /path/to/addressbook.php
$baseUri = strpos($_SERVER['REQUEST_URI'], 'addressbook.php') ? \GO::config()->host . 'modules/carddav/addressbook.php/' : '/carddav/';
$server->setBaseUri($baseUri);

// Support for html frontend
$browser = new Sabre\DAV\Browser\Plugin(false);
$server->addPlugin($browser);

// And off we go!
$server->exec();