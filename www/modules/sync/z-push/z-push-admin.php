#!/usr/bin/php
<?php
/**
 * This is a wrapper for z-push admin.php that detects the right group-office config file.
 */

require(dirname(__FILE__).'/../../../go/base/util/Cli.php');

$scriptPath = $_SERVER['SCRIPT_FILENAME'];////\GO\Base\Util\Cli::getScriptPath();

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

chdir(dirname(dirname(__DIR__)).'/z-push');

require('z-push-admin.php');
