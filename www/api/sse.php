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

require("../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

//Hard code debug to false to prevent spamming of log.
App::get()->getDebugger()->enabled = false;

header('Cache-Control: no-cache');
header('Pragma: no-cache');
//header('Connection: keep-alive');
header("Content-Type: text/event-stream");

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);

const CHECK_INTERVAL = 5;
const MAX_LIFE_TIME = 120;

$ping = $_GET['ping'] ?? 10;
$sleeping = 0;

//Client may specify 
if(isset($_GET['types'])) {
	$entityNames = explode(",", $_GET['types']);
	$types = \go\core\orm\EntityType::namesToIds($entityNames);
} else
{
	$types = [];
}

function checkChanges() {
	global $types;
	
	$entities = (new Query)
						->select('clientName,highestModSeq')
						->from('core_entity')
						->where('highestModSeq', '!=', null);
	
	if(!empty($types)) {
		$entities->andWhere('id', 'IN', $types);
	}
	
	$state = [];
	foreach ($entities as $r) {
		$state[$r['clientName']] = (int) $r['highestModSeq'];
	}
	
	return $state;
}

$changes = checkChanges();

function sendMessage($type, $data) {
	echo "event: $type\n";
	echo 'data: ' . json_encode($data);
	echo "\n\n";
	
	if(ob_get_level() > 0) {
		ob_flush();
	}
	
	flush();	
}

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
for($i = 0; $i < MAX_LIFE_TIME; $i += CHECK_INTERVAL) {

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
	$sleeping += CHECK_INTERVAL;
	sleep(CHECK_INTERVAL);
}