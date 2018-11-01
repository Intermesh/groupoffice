<?php

ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core\App;
use go\core\cli\State;

$installDb = true;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

$dataFolder = new \go\core\fs\Folder(__DIR__ . '/data');


$config = parse_ini_file(__DIR__ . '/config.ini', true);
$config['general']['dataPath'] = $dataFolder->getPath();
$config['general']['tmpPath'] = $dataFolder->getFolder('tmp')->getPath();
$config['general']["cache"] = \go\core\cache\Disk::class;

if($installDb) {
	$dataFolder->delete();
	$dataFolder->create();

	//connect to server without database
	$dsn = \go\core\db\Utils::parseDSN($config['db']['dsn']);	
	$pdo = new PDO('mysql:host='. $dsn['options']['host'], $config['db']['username'], $config['db']['password']);

	try {
		$pdo->query("DROP DATABASE groupoffice_phpunit");
	}catch(\Exception $e) {

	}
	$pdo->query("CREATE DATABASE groupoffice_phpunit");
}

//Install fresh DB

try {
	App::get()->setConfig($config)->setAuthState(new State());
	
	if($installDb) {
		$admin = [
				'displayName' => "System Administrator",
				'username' => "admin",
				'password' => "adminsecret",
				'email' => "admin@intermesh.mailserver"
		];

		$installer = new \go\core\Installer();	
		$installer->install($admin, [
				new go\modules\community\notes\Module(),
				new go\modules\community\test\Module()
				]);
	}
} catch (Exception $e) {
	echo $e;
	throw $e;
}
