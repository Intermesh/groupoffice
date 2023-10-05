<?php


ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core;
use go\core\App;
use go\core\cli\controller\System;
use go\core\cli\State;
use GO\Base\Model\Module;
use go\core\db\Table;
use go\core\model\User;
use go\core\orm\Property;
use GO\Demodata\Controller\DemodataController;

const INSTALL_NEW = 0;
const INSTALL_UPGRADE = 1;
const INSTALL_NONE = 2;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

$dataFolder = new \go\core\fs\Folder(__DIR__ . '/data');

require(__DIR__ . "/config.php");
$config['file_storage_path'] = $dataFolder->getPath();
$config['tmpdir'] = $dataFolder->getFolder('tmp')->getPath();

try {
	//for autoload
	App::get();
	$c = new core\util\ArrayObject(go()->getConfig());
	$c->mergeRecursive($config);
	go()->setConfig($c->getArray());

	// Install new if db doesn't exist otherwise use existing
	if(!isset($installDb)) {
		$installDb = !go()->isInstalled() ? INSTALL_NEW : INSTALL_NONE;
	}

	if($installDb == INSTALL_NEW || $installDb == INSTALL_UPGRADE) {
		core\fs\FileSystemObject::allowRootFolderDelete();
		$dataFolder->delete();
		core\fs\FileSystemObject::allowRootFolderDelete(false);
		$dataFolder->create();


		//connect to server without database
		$pdo = new PDO('mysql:host='. $config['db_host'], $config['db_user'], $config['db_pass']);

		try {
			echo "Dropping database 'groupoffice-phpunit'\n";
			$pdo->query("DROP DATABASE groupoffice_phpunit");
		}catch(\Exception $e) {

		}

		GO::clearCache(); //legacy

		go()->getCache()->flush(false);
		go()->getDatabase()->clearCache();
		Property::clearCache();
		Property::clearCachedRelationStmts();


		echo "Creating database 'groupoffice-phpunit'\n";
		$pdo->query("CREATE DATABASE groupoffice_phpunit");
		$pdo = null;
	}


	if($installDb == INSTALL_NEW) {

	  echo "Running install\n";
		$admin = [
				'displayName' => "System Administrator",
				'username' => "admin",
				'password' => "adminsecret",
				'email' => "admin@intermesh.mailserver"
		];

		$installer = go()->getInstaller();
		$installer->install($admin);




		\go\modules\community\test\Module::get()->install();

		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();
			if ($moduleController instanceof core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				Module::install($moduleController->name());
			}
		}

		GO::$ignoreAclPermissions = false;

		// fake URL
		go()->getSettings()->URL = "http://localhost";

//		echo "Installing demo data\n";
//
//		$ctrl = new System();
//		$ctrl->demo();

		echo "Done\n\n";
	} else if($installDb == INSTALL_UPGRADE) {
    echo "Running upgrade: ";
	  $importCmd = 'mysql -h ' .  escapeshellarg($config['db_host']) . ' -u '.escapeshellarg($config['db_user']) . ' -p'.escapeshellarg($config['db_pass']).' groupoffice_phpunit < ' . __DIR__ . '/upgradetest/go67.sql';
    echo "Running: " . $importCmd . "\n";
	  system($importCmd);

	  $copyCmd = 'cp -r ' . __DIR__ . '/upgradetest/go67data/* ' . $dataFolder->getPath();
	  echo "Running: " . $copyCmd . "\n";
	  system($copyCmd);

	  system('chown -R www-data:www-data ' . $dataFolder->getPath());


	  go()->getInstaller()->upgrade();

    $mod = \go\modules\community\test\Module::get();
    if(!$mod->isInstalled(false)) {
	    $mod->install();
    }

  } else {
		echo "Using existing database 'groupoffice_phpunit'\n";
	}
	go()->rebuildCache();

	go()->setAuthState(new State());


	go()->getSettings()->systemEmail = 'admin@intermesh.localhost';
	go()->getSettings()->smtpHost = 'mailserver';
	go()->getSettings()->smtpPort = 25;
	go()->getSettings()->smtpEncryption = null;
	go()->getSettings()->save();

} catch (Exception $e) {
	echo $e;
	throw $e;
}

// debugger
//go()->getDebugger()->enabled = true;
//go()->getDebugger()->output = true;

echo "Done\n\n\n";
