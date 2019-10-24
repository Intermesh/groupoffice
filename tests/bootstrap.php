<?php

ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core;
use go\core\App;
use go\core\cli\State;
use GO\Base\Model\Module;

$installDb = true;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

$dataFolder = new \go\core\fs\Folder(__DIR__ . '/data');


$config = parse_ini_file(__DIR__ . '/config.ini', true);
$config['general']['dataPath'] = $dataFolder->getPath();
$config['general']['tmpPath'] = $dataFolder->getFolder('tmp')->getPath();
$config['general']["cache"] = \go\core\cache\Disk::class;
$config['branding']['name'] = 'Group-Office';

if($installDb) {
	$dataFolder->delete();
	$dataFolder->create();

	//connect to server without database
	$dsn = \go\core\db\Utils::parseDSN($config['db']['dsn']);	
	$pdo = new PDO('mysql:host='. $dsn['options']['host'], $config['db']['username'], $config['db']['password']);

	try {
		echo "Dropping database 'groupoffice-phpunit'\n";
		$pdo->query("DROP DATABASE groupoffice_phpunit");
	}catch(\Exception $e) {

	}

	echo "Installing database 'groupoffice-phpunit'\n";
	$pdo->query("CREATE DATABASE groupoffice_phpunit");
	$pdo = null;
}

//Install fresh DB

try {
	App::get()->setConfig(["core" => $config]);
	
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
				new go\modules\community\test\Module(),
				new go\modules\community\addressbook\Module(),
				]);


		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = new $moduleClass;
			if ($moduleController instanceof core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				$module = new Module();
				$module->name = $moduleController->name();
				if (!$module->save()) {
					throw new \Exception("Could not save module " . $module->name);
				}
			}
		}
		GO::$ignoreAclPermissions = false;

		echo "Done\n\n";
	}

	GO()->setAuthState(new State());

} catch (Exception $e) {
	echo $e;
	throw $e;
}
