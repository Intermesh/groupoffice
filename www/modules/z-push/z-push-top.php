#!/usr/bin/php
<?php
define("ZPUSH_VERSION", "2.4.4");
define("ZPUSH_DIR", __DIR__ . "/vendor/z-push/");

require(ZPUSH_DIR . 'vendor/autoload.php');
require("backend/go/autoload.php");

define('ZPUSH_CONFIG', __DIR__ . '/config.php');

$scriptPath = $_SERVER['SCRIPT_FILENAME'];

if(strstr($scriptPath, '/home/govhosts')) {

	$parts = explode('/', $scriptPath);
	$hostname = $parts[3];

	$config = '/etc/groupoffice/'.$hostname.'/config.php';

	if(!file_exists($config)) {
		exit("Could not find servermanager config file");
	}

	define("GO_CONFIG_FILE", $config);

	echo "Using config file: $config\n";
}

require(ZPUSH_DIR . "z-push-top.php");