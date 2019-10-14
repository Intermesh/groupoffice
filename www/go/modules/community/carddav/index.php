<?php

use go\core\App;
use go\core\dav\auth\BasicBackend;
use go\core\dav\davacl\PrincipalBackend;
use go\modules\community\carddav\Backend;
use Sabre\CardDAV\AddressBookRoot;
use Sabre\CardDAV\Plugin as CardDAVPlugin;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Browser\Plugin;
use Sabre\DAV\Server;
use Sabre\DAVACL\Plugin as AclPlugin;
use Sabre\DAVACL\PrincipalCollection;

require(__DIR__ . "/../../../../vendor/autoload.php");

//Create the app with the database connection
App::get();
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */

$authBackend = new BasicBackend();
$authBackend->checkModulePermission('community', 'carddav');

$principalBackend = new PrincipalBackend();
$carddavBackend = new Backend();

// Setting up the directory tree //
$nodes = array(
		new PrincipalCollection($principalBackend),
		new AddressBookRoot($principalBackend, $carddavBackend),
);


/* Initializing server */
$server = new Server($nodes);
$server->debugExceptions = go()->getDebugger()->enabled;
$server->on('exception', function($e){
	go()->debug((string) $e);
});


/* Server Plugins */
$server->addPlugin(new AuthPlugin($authBackend));
$server->addPlugin(new CardDAVPlugin());
$aclPlugin = new AclPlugin();
$aclPlugin->allowUnauthenticatedAccess = false;
$server->addPlugin($aclPlugin);

//baseUri can also be /carddav/ with:
//Alias /carddav/ /path/to/addressbook.php
if(strpos($_SERVER['REQUEST_URI'], 'index.php')) {
	$path = parse_url(go()->getSettings()->URL, PHP_URL_PATH);
	$baseUri =  $path . 'go/modules/community/carddav/index.php/';
} else
{
	$baseUri = '/carddav/';	
}

$server->setBaseUri($baseUri);

// Support for html frontend
$browser = new Plugin(false);
$server->addPlugin($browser);

// And off we go!
$server->exec();