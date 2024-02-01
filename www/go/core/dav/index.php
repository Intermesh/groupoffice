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
use go\core\model\Module;
use go\modules\community\calendar\model\CalDAVBackend;
use go\modules\community\carddav\Backend as CardDAVBackend;
use Sabre\CardDAV;
use Sabre\CalDAV;
use Sabre\DAV\Auth;
use Sabre\DAV\Browser;
use Sabre\DAV\Exception\NotAuthenticated;
use Sabre\DAV\Server;
use Sabre\DAVACL;

require(__DIR__ . "/../../../vendor/autoload.php");

//Create the app with the database connection
App::get();

// allow 2 minutes for vcard generation
go()->getEnvironment()->setMaxExecutionTime(120);
go()->getDebugger()->setRequestId("DAV " . ($_SERVER['REQUEST_METHOD'] ?? ""));

$principalBackend = new PrincipalBackend();
/* Initializing server with directory tree */
$server = new Server([
	new go\core\dav\davacl\PrincipalCollection($principalBackend),
	new CardDAV\AddressBookRoot($principalBackend, new CardDAVBackend()),
	new CalDAV\CalendarRoot($principalBackend, new CalDAVBackend())
]);
// Alias /dav/ /path/to/dav/index.php
$server->setBaseUri(stripos($_SERVER['REQUEST_URI'], basename(__FILE__)) ? __FILE__ : '/dav/');
$server->debugExceptions = go()->getDebugger()->enabled;
$server->on('exception', function($e){
	if(!($e instanceof NotAuthenticated)) {
		ErrorHandler::logException($e);
	}
});
/* Server Plugins */
$server->addPlugin(new Auth\Plugin(new BasicBackend()));
$server->addPlugin(new CardDAV\Plugin());
$server->addPlugin(new CalDAV\Plugin());
$aclPlugin = new DAVACL\Plugin();
$aclPlugin->allowUnauthenticatedAccess = false;
$server->addPlugin($aclPlugin);
$server->addPlugin(new CalDAV\Schedule\Plugin());
$server->addPlugin(new go\core\dav\schedule\IMipPlugin(go()->getSettings()->systemEmail));
$server->addPlugin(new Browser\Plugin(false)); // Support for html frontend

// And off we go!
$server->start();