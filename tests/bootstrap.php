<?php

ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core;
use go\core\App;
use go\core\cli\State;
use GO\Base\Model\Module;
use GO\Demodata\Controller\DemodataController;

const INSTALL_NEW = 0;
const INSTALL_UPGRADE = 1;
const INSTALL_NONE = 2;

$installDb = INSTALL_NEW;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

$dataFolder = new \go\core\fs\Folder(__DIR__ . '/data');


$config = parse_ini_file(__DIR__ . '/config.ini', true);
$config['general']['dataPath'] = $dataFolder->getPath();
$config['general']['tmpPath'] = $dataFolder->getFolder('tmp')->getPath();
$config['general']["cache"] = \go\core\cache\Apcu::class;
$config['branding']['name'] = 'Group-Office';

if($installDb == INSTALL_NEW || $installDb == INSTALL_UPGRADE) {
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

	echo "Creating database 'groupoffice-phpunit'\n";
	$pdo->query("CREATE DATABASE groupoffice_phpunit");
	$pdo = null;
}

//Install fresh DB
App::get(); //for autoload
try {
	go()->setConfig(["core" => $config]);
	//App::get()->getCache()->flush(false);
	
	if($installDb == INSTALL_NEW) {

	  echo "Running install\n";
		$admin = [
				'displayName' => "System Administrator",
				'username' => "admin",
				'password' => "adminsecret",
				'email' => "admin@intermesh.mailserver"
		];

		$installer = go()->getInstaller();
		$installer->install($admin, [
				new go\modules\community\notes\Module(),
				new go\modules\community\test\Module(),
				new go\modules\community\addressbook\Module(),
				new go\modules\community\comments\Module(),
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

		echo "Installing demo data\n";

		$c = new DemodataController();
		$c->run('create');

		echo "Done\n\n";
	} else if($installDb == INSTALL_UPGRADE) {
    echo "Running upgrade: ";
	  $importCmd = 'mysql -h ' .  escapeshellarg($dsn['options']['host']) . ' -u '.escapeshellarg($config['db']['username']) . ' -p'.escapeshellarg($config['db']['password']).' groupoffice_phpunit < ' . __DIR__ . '/upgradetest/go63.sql';
    echo "Running: " . $importCmd . "\n";
	  system($importCmd);

	  $copyCmd = 'cp -r ' . __DIR__ . '/upgradetest/go63data/* ' . $dataFolder->getPath();
	  echo "Running: " . $copyCmd . "\n";
	  system($copyCmd);

	  system('chown -R www-data:www-data ' . $dataFolder->getPath());


	  go()->getInstaller()->upgrade();

    $mod = new \go\modules\community\test\Module();
    $mod->install();

  }

	go()->setAuthState(new State());

} catch (Exception $e) {
	echo $e;
	throw $e;
}
