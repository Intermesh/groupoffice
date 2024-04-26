<?php

if(!empty($argv[1])) {
	$config = '/etc/groupoffice/multi_instance/'.$argv[1].'/config.php';
	if(!file_exists($config)) {
		exit("Could not find servermanager config file");
	}
	define("GO_CONFIG_FILE", $config);
	echo "Using config file: $config\n";
}

define("ZPUSH_VERSION", "2.7.1");
define("ZPUSH_DIR", dirname(__DIR__, 2) . "/go/modules/community/activesync/Z-Push/src/");
define('ZPUSH_CONFIG', __DIR__ . '/config.php');
require(ZPUSH_DIR . 'vendor/autoload.php');