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
App::get()->setAuthState(new State());

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

//Check availability
if(!go()->getConfig()['sseEnabled']) {
	// Service Unavailable

	Response::get()
		->setStatus(503, "Server Sent Events not available")
		->output();
	exit();
}

// The client may specify the entity 'types' to watch. The web client POSTs them as a JSON array:
// in the query string they grew with every installed entity until the URL hit the web server's
// request line limit (414). GET with ?types= is still accepted.
//
// CSRF is enforced by go\core\jmap\State for any non GET request that authenticates with the
// access token cookie, so the POST needs no check of its own.
if(Request::get()->getMethod() == "POST") {
	// Decoded without $associative, so a JSON object stays an object and is refused
	$types = json_decode((string) file_get_contents('php://input'));

	if(!is_array($types) || count(array_filter($types, 'is_string')) !== count($types)) {
		Response::get()
			->setStatus(400, "Expected a JSON array of entity names")
			->output();
		exit();
	}
} else {
	$types = !empty($_GET['types']) ? explode(',', $_GET['types']) : [];
}

//for servers with session.autostart
@session_write_close();

ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
ini_set("max_execution_time", PushDispatcher::MAX_LIFE_TIME + 30);
@ob_end_flush();

Response::get()
	->setHeader("Cache-Control", "no-cache")
	->setContentType("text/event-stream")
	->setHeader("Pragma", "no-cache")
	->setHeader("Connection", "keep-alive")
	->setHeader("X-Accel-Buffering", "no")
	->output();


try {
	(new PushDispatcher($types))
		->start();
} catch(Throwable $e) {
	echo "event: exception\n";
	echo 'data: ' . get_class($e). "\n\n";

	ErrorHandler::logException($e);
}