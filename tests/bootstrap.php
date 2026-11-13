<?php


ini_set('error_reporting', E_ALL); 
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

use go\core;
use go\core\App;
use go\core\cli\controller\System;
use go\core\cli\State;
use GO\Base\Model\Module;
use go\core\db\Connection;
use go\core\db\Table;
use go\core\model\User;
use go\core\orm\Property;
use GO\Demodata\Controller\DemodataController;

const INSTALL_NEW = 0;
const INSTALL_UPGRADE = 1;
const INSTALL_NONE = 2;

$autoLoader = require(__DIR__ . "/../www/vendor/autoload.php");
$autoLoader->add('go\\', __DIR__);

$dataFolder = new \go\core\fs\Folder( '/tmp/phpunit');

require(__DIR__ . "/config.php");
$config['file_storage_path'] = $dataFolder->getPath();
$config['tmpdir'] = $dataFolder->getFolder('tmp')->getPath();

try {
	//for autoload
	App::get();

	// debugger
	//go()->getDebugger()->enabled = true;
	//go()->getDebugger()->output = true;

	$c = new core\util\ArrayObject(go()->getConfig());
	$c->mergeRecursive($config);
	go()->setConfig($c->getArray());
	// The legacy config always ends these with a slash, and legacy code appends paths to them
	\GO::config()->file_storage_path = rtrim($config['file_storage_path'], '/') . '/';
	\GO::config()->tmpdir = rtrim($config['tmpdir'], '/') . '/';

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
		echo "MySQL host: ".  $config['db_host'] ."\n\n";

		$pdo = new PDO('mysql:host='. $config['db_host'], $config['db_user'], $config['db_pass']);

		try {

			// only for testing. It speeds up but never use this for production.
			$pdo->exec("SET GLOBAL innodb_flush_log_at_trx_commit = 2");
		} catch (\Exception $e) {
			echo "Could not relax InnoDB flushing: " . $e->getMessage() . "\n";
		}

		try {
			echo "Dropping database 'groupoffice_phpunit'\n";
			$pdo->query("DROP DATABASE groupoffice_phpunit");
		}catch(\Exception $e) {
			echo $e->getMessage() ."\n";
		}

		GO::clearCache(); //legacy

		go()->getCache()->flush(false);
		go()->getDatabase()->clearCache();
		Property::clearCache();
		Property::clearCachedRelationStmts();


		echo "Creating database 'groupoffice_phpunit'\n";
		$pdo->query("CREATE DATABASE groupoffice_phpunit");
		$pdo = null;
	}


	if($installDb == INSTALL_NEW) {

	  echo "Running install\n";
		$admin = [
			'displayName' => "System Administrator",
			'username' => "admin",
			'password' => "adminsecret",
			'email' => "admin@intermesh.mailserver",
			'language' => 'nl'
		];

		try {

			// only for testing. It speeds up but never use this for production.
			go()->getDbConnection()->exec("SET GLOBAL innodb_flush_log_at_trx_commit = 2");
		} catch (\Exception $e) {
			echo "Could not relax InnoDB flushing: " . $e->getMessage() . "\n";
		}

		$installer = go()->getInstaller();
		$installer->install($admin);

		$success = \go\modules\community\test\Module::get()->install();
		if(!$success) {
			throw new Exception("Could not install test module");
		}
		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();
			if ($moduleController instanceof core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				Module::install($moduleController->name(), false, $moduleController::getDefaultSortOrder());
			}
		}

		GO::$ignoreAclPermissions = false;

		// fake URL
		go()->getSettings()->URL = "http://localhost";
		echo "Done\n\n";
	} else if($installDb == INSTALL_UPGRADE) {
        echo "Running upgrade: ";
		$importCmd = 'mysql --skip-ssl -h ' .  escapeshellarg($config['db_host']) . ' -u '.escapeshellarg($config['db_user']) . ' -p'.escapeshellarg($config['db_pass']).' groupoffice_phpunit < ' . __DIR__ . '/upgradetest/go250.sql';
        echo "Running: " . $importCmd . "\n";
	  system($importCmd);

	  $copyCmd = 'cp -r ' . __DIR__ . '/upgradetest/250data/* ' . $dataFolder->getPath();
	  echo "Running: " . $copyCmd . "\n";
	  system($copyCmd);

	  system('chown -R www-data:www-data ' . $dataFolder->getPath());

		// for moving wopi module
		go()->getDbConnection()->exec("update core_module set package='community' where name='wopi'");

	  go()->getInstaller()->upgrade();

    $mod = \go\modules\community\test\Module::get();
    if(!$mod->isInstalled(false)) {
	    $success = $mod->install();

			if(!$success) {
				throw new Exception("Could not install test module");
			}
    }

  } else {
		echo "Using existing database 'groupoffice_phpunit'\n";
	}
	// The pwned module checks every new password with an online service, which makes tests slow and dependent on the network
	$pwned = \go\core\model\Module::findByName('community', 'pwned');
	if($pwned) {
		$pwned->enabled = false;
		if(!$pwned->save()) {
			throw new Exception("Could not disable the pwned module");
		}
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



echo "Done\n\n\n";
