<?php
/**
 * EventSource endpoint
 * 
 * For details visit the link below
 * 
 * @link https://jmap.io/spec-core.html#event-source
 */

use go\core\App;
use go\core\jmap\State;
use go\core\model\PushDispatcher;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

if(!App::get()->setAuthState(new State())->getAuthState()->isAuthenticated()) {
	http_response_code(401);
	exit("Unauthorized.");
}

//for servers with session.autostart
@session_write_close();

//Check availability
if(!go()->getConfig()['sseEnabled'] || (function_exists("xdebug_is_debugger_active") && xdebug_is_debugger_active())) {
	// Service Unavailable
	http_response_code(503);
	echo "Server Sent Events not available";
	exit();
}
ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);
ini_set("max_execution_time", PushDispatcher::MAX_LIFE_TIME + 10);

header('Cache-Control: no-cache');
header('Pragma: no-cache');
header("Content-Type: text/event-stream");
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

// Client may specify 'types' and a 'ping' interval
(new PushDispatcher($_GET['types']))->start($_GET['ping'] ?? 10);