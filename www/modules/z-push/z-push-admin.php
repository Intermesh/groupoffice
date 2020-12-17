#!/usr/bin/env php
<?php

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

define('ZPUSH_CONFIG', __DIR__ . '/config.php');
require_once 'vendor/z-push/vendor/autoload.php';
require_once ("backend/go/autoload.php");
require_once 'vendor/z-push/z-push-admin.php';
