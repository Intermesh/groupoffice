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
 * @author Michael de Hart <mdhartg@intermesh.nl>
 */

use go\core\App;
use go\core\dav\auth\BasicBackend;
use go\core\dav\davacl\PrincipalBackend;
use go\core\ErrorHandler;
use go\modules\community\calendar\model\CalDAVBackend;
use go\modules\community\carddav\Backend as CardDAVBackend;
use Sabre\CardDAV;
use Sabre\CalDAV;
use Sabre\DAV\Auth;
use Sabre\DAV\Browser;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;
use Sabre\DAVACL\Plugin as AclPlugin;
use Sabre\DAVACL\PrincipalCollection;

require(__DIR__ . "/../../../vendor/autoload.php");

//Create the app with the database connection
App::get();

// allow 2 minutes for vcard generation
go()->getEnvironment()->setMaxExecutionTime(120);
//baseUri can also be /carddav/ with:
//Alias /carddav/ /path/to/addressbook.php
if(strpos($_SERVER['REQUEST_URI'], 'index.php')) {
	$path = parse_url(go()->getSettings()->URL, PHP_URL_PATH);
	$baseUri =  $path . 'go/core/dav/index.php/';
} else
{
	$baseUri = '/dav/';
}

$authBackend = new BasicBackend();
$authBackend->checkModulePermission('community', 'carddav');

$principalBackend = new PrincipalBackend();
$caldavBackend = new CalDAVBackend();
$carddavBackend = new CardDAVBackend();

// Setting up the directory tree //
$nodes = array(
	new PrincipalCollection($principalBackend),
	new CardDAV\AddressBookRoot($principalBackend, $carddavBackend),
	new CalDAV\CalendarRoot($principalBackend, $caldavBackend)
);


go()->getDebugger()->setRequestId("DAV " . ($_SERVER['REQUEST_METHOD'] ?? ""));

/* Initializing server */
$server = new Server($nodes);
$server->setBaseUri($baseUri);
$server->debugExceptions = go()->getDebugger()->enabled;
$server->on('exception', function($e){
	if(!($e instanceof NotAuthenticated)) {
		ErrorHandler::logException($e);
	}
});
/* Server Plugins */
$server->addPlugin(new Auth\Plugin($authBackend));
$server->addPlugin(new CardDAV\Plugin());
$server->addPlugin(new CalDAV\Plugin());
$aclPlugin = new AclPlugin();
$aclPlugin->allowUnauthenticatedAccess = false;
$server->addPlugin($aclPlugin);
$server->addPlugin(new CalDAV\Schedule\Plugin());
$server->addPlugin(new go\core\dav\schedule\IMipPlugin(go()->getSettings()->systemEmail));
$server->addPlugin(new Browser\Plugin(false)); // Support for html frontend

// And off we go!
$server->start();