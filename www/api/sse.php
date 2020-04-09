<?php
/**
 * EventSource endpoint
 * 
 * For details visit the link below
 * 
 * @link https://jmap.io/spec-core.html#event-source
 */

use go\core\App;
use go\core\db\Query;
use go\core\jmap\State;
use go\core\orm\EntityType;

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

//for servers with session.autostart
@session_write_close();

//Check availability
if(!go()->getConfig()['core']['general']['sseEnabled'] || (function_exists("xdebug_is_debugger_active") && xdebug_is_debugger_active())) {
	// Service Unavailable
	http_response_code(503);
	echo "Server Sent Events not available";
	exit();
}
$CHECK_INTERVAL = go()->getDebugger()->enabled ? 5 : 30;
const MAX_LIFE_TIME = 120;

ini_set("max_execution_time", MAX_LIFE_TIME + 10);

//Hard code debug to false to prevent spamming of log.
App::get()->getDebugger()->enabled = false;

header('Cache-Control: no-cache');
header('Pragma: no-cache');
header("Content-Type: text/event-stream");
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no');

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);



$ping = $_GET['ping'] ?? 10;
$sleeping = 0;


function sendMessage($type, $data) {
	echo "event: $type\n";
	echo 'data: ' . json_encode($data);
	echo "\n\n";
	
	while(ob_get_level() > 0) {
		ob_end_flush();
	}
	
	flush();	
}
sendMessage('ping', []);

$query = new Query();
//Client may specify 
if(isset($_GET['types'])) {
	$entityNames = explode(",", $_GET['types']);
	$query->where('e.name', 'IN', $entityNames);
}
$entities = EntityType::findAll($query);
$map = [];
foreach($entities as $e) {
	$map[$e->getName()] = $e->getClassName();
}

function checkChanges() {
	global $map;
	
	$state = [];
	foreach ($map as $name => $cls) {		
		$cls::entityType()->clearCache();
		$state[$name] = $cls::getState();
	}
	// sendMessage('ping', $state);
	return $state;
}

$changes = checkChanges();


function diff($old, $new) {

		$diff = [];
		
		foreach ($new as $key => $value) {
			if (!isset($old[$key]) || $old[$key] !== $value) {
				$diff[$key] = $value;
			}
		}

		return $diff;
	}
	
	//sendMessage('ping', []);
for($i = 0; $i < MAX_LIFE_TIME; $i += $CHECK_INTERVAL) {

	// break the loop if the client aborted the connection (closed the page)
	if(connection_aborted()) {
		break;
	}

//	sendMessage('test', [$sleeping, $ping]);
	if ($sleeping >= $ping) {
		$sleeping = 0;
		sendMessage('ping', []);
	}
	
	$new = checkChanges();
	$diff = diff($changes, $new);
	if(!empty($diff)) {
		$sleeping = 0;
		sendMessage('state', $diff);
		$changes = $new;
	}

	go()->getDbConnection()->disconnect();
	$sleeping += $CHECK_INTERVAL;
	sleep($CHECK_INTERVAL);
}