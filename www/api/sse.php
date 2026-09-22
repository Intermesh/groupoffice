<?php
/**
 * EventSource endpoint
 * 
 * For details visit the link below
 * 
 * @link https://jmap.io/spec-core.html#event-source
 */

use go\core\App;
use go\core\ErrorHandler;
use go\core\jmap\Request;
use go\core\jmap\Response;
use go\core\jmap\State;
use go\core\model\PushDispatcher;

require("../vendor/autoload.php");

//for legacy session object
define("GO_NO_SESSION", true);

//Create the app with the database connection
$authState = new State();
App::get()->setAuthState($authState);


if(Request::get()->getMethod() == "OPTIONS") {
	Response::get()
		->sendHeaders()
		->output();
	exit();
}

if(!App::get()->getAuthState()->isAuthenticated()) {
	Response::get()
		->setStatus(401)
		->output();

	exit();
}



//for servers with session.autostart
@session_write_close();


//Check availability
if(!go()->getConfig()['sseEnabled']) {
	// Service Unavailable

	Response::get()
		->setStatus(503, "Server Sent Events not available")
		->output();
	exit();
}



// The client registers the entity types it wants with a POST and then connects with a plain
// GET. Passing them in the query string made the URL grow with every registered entity until it
// hit the web server's request line limit.
//
// CSRF is enforced by go\core\jmap\State for any non GET request that authenticates with the
// access token cookie, so this needs no check of its own.
if(Request::get()->getMethod() == "POST") {
	$types = json_decode(file_get_contents('php://input'), true);

	if(!is_array($types)) {
		Response::get()
			->setStatus(400, "Expected a JSON array of entity names")
			->output();
		exit();
	}

	if(!PushDispatcher::storeSubscription($authState->getToken(), $types)) {
		Response::get()
			->setStatus(500, "Failed to store the subscription")
			->output();
		exit();
	}

	Response::get()
		->setStatus(204)
		->output();
	exit();
}

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
ini_set("max_execution_time", PushDispatcher::MAX_LIFE_TIME + 30);

Response::get()
	->setHeader("Cache-Control", "no-cache")
	->setContentType(" text/event-stream")
	->setHeader("Pragma", "no-cache")
	->setHeader("Connection", "keep-alive")
	->setHeader("X-Accel-Buffering", "no")
	->output();

try {
	// Clients from before the POST registration still pass 'types' in the query string.
	$types = !empty($_GET['types'])
		? explode(',', $_GET['types'])
		: PushDispatcher::loadSubscription($authState->getToken());

	if(empty($types)) {
		// Either nothing was registered, or the registration was empty. Neither may fall through
		// to PushDispatcher's unfiltered behaviour, which watches every entity type on the
		// system. The web client always registers a non empty list before it connects.
		echo "event: exception\n";
		echo 'data: ' . json_encode("No entity types registered. POST a JSON array of entity names to this URL first.") . "\n\n";
		exit();
	}

	// Client may specify a 'ping' interval
	(new PushDispatcher($types))->start($_GET['ping'] ?? 10);
} catch(Throwable $e) {
	echo "event: exception\n";
	echo 'data: ' . $e->getMessage(). "\n\n";

	ErrorHandler::logException($e);
}