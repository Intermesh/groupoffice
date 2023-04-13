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

//Create the app with the database connection
App::get()->setAuthState(new State());


if(Request::get()->getMethod() == "OPTIONS") {
	Response::get()
		->sendHeaders()
		->output();
	exit();
}

if(!App::get()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
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
ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
ini_set("max_execution_time", PushDispatcher::MAX_LIFE_TIME + 10);

Response::get()
	->setHeader("Cache-Control", "no-cache")
	->setContentType(" text/event-stream")
	->setHeader("Pragma", "no-cache")
	->setHeader("Connection", "keep-alive")
	->setHeader("X-Accel-Buffering", "no")
	->output();

try {
// Client may specify 'types' and a 'ping' interval
	(new PushDispatcher($_GET['types']))->start($_GET['ping'] ?? 10);
} catch(Throwable $e) {
	echo "event: error\n";
	echo 'data: ' . $e->getMessage(). "\n\n";

	ErrorHandler::logException($e);
}