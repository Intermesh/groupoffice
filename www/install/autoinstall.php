#!/usr/bin/php
<?php
/**
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @copyright Copyright Intermesh
 * @version $Id: autoinstall.php 21321 2017-07-18 09:53:11Z wsmits $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
$root = dirname(dirname(__FILE__)).'/';

if(PHP_SAPI=='cli'){	
	//on the command line you can pass -c=/path/to/config.php to set the config file.
	
	require_once($root.'go/base/util/Cli.php');
	
	$args = \GO\Base\Util\Cli::parseArgs();
	
	if(isset($args['c'])){
		define("GO_CONFIG_FILE", $args['c']);
	}
}

try{
$exampleUsage = 'sudo -u www-data php /var/www/trunk/www/install/autoinstall.php --adminusername=admin --adminpassword=admin --adminemail=admin@intermesh.dev --modules="email,addressbook,files"';
$requiredArgs = array('adminusername','adminpassword','adminemail');

foreach($requiredArgs as $ra){
	if(empty($args[$ra])){
		throw new Exception($ra." must be supplied.\n\nExample usage:\n\n".$exampleUsage."\n\n");
	}
}

chdir(dirname(__FILE__));
require('../GO.php');

\GO::setIgnoreAclPermissions();

$stmt = \GO::getDbConnection()->query("SHOW TABLES");
if ($stmt->rowCount())
	throw new Exception("Automatic installation of Group-Office aborted because database is not empty");
else
	echo "Database connection established. Database is empty\n";

\GO\Base\Util\SQL::executeSqlFile('install.sql');

$dbVersion = \GO\Base\Util\Common::countUpgradeQueries("updates.php");

\GO::config()->save_setting('version', $dbVersion);
\GO::config()->save_setting('upgrade_mtime', \GO::config()->mtime);

$adminGroup = new \GO\Base\Model\Group();
$adminGroup->id = 1;
$adminGroup->name = \GO::t('group_admins');
$adminGroup->save();

$everyoneGroup = new \GO\Base\Model\Group();
$everyoneGroup->id = 2;
$everyoneGroup->name = \GO::t('group_everyone');
$everyoneGroup->save();

$internalGroup = new \GO\Base\Model\Group();
$internalGroup->id = 3;
$internalGroup->name = \GO::t('group_internal');
$internalGroup->save();

//\GO::config()->register_user_groups = \GO::t('group_internal');
//\GO::config()->register_visible_user_groups = \GO::t('group_internal');

$modules = \GO::modules()->getAvailableModules();

if(isset($args['modules'])){
	$installModules = explode(',', $args['modules']);
	
}elseif(!empty(\GO::config()->allowed_modules)){
	$installModules=explode(',',\GO::config()->allowed_modules);
}

if(isset($installModules)){
	$installModules[]="modules";
	$installModules[]="users";
	$installModules[]="groups";
}

foreach ($modules as $moduleClass) {
	$moduleController = new $moduleClass;
	if($moduleController->isInstallable()){
		if ((!isset($installModules) && $moduleController->autoInstall()) || (isset($installModules) && in_array($moduleController->id(), $installModules))) {

			echo "Installing module ".$moduleController->id()."\n";

			$module = new \GO\Base\Model\Module();
			$module->id = $moduleController->id();
			$module->save();
		}
	}
}

$admin = new \GO\Base\Model\User();
$admin->first_name = \GO::t('system');
$admin->last_name = \GO::t('admin');
$admin->username = $args['adminusername'];
$admin->password = $args['adminpassword'];
$admin->email = \GO::config()->webmaster_email = $args['adminemail'];

\GO::config()->save();

//disable password validation
\GO::config()->password_validate=false;		

$admin->save();

$adminGroup->addUser($admin->id);

$admin->checkDefaultModels();



//module code here because we need the user and the module for this
if(\GO::modules()->files){
	$folder = \GO\Files\Model\Folder::model()->findByPath('users/'.$admin->username.'/Public', true);
	$folder->visible=true;
	$acl = $folder->setNewAcl();
	$acl->addGroup(\GO::config()->group_everyone, \GO\Base\Model\Acl::DELETE_PERMISSION);
	$folder->save();
}


//Insert default cronjob record for email reminders
$cron = new \GO\Base\Cron\CronJob();

$cron->name = 'Email Reminders';
$cron->active = true;
$cron->runonce = false;
$cron->minutes = '*/5'; // Every 5 minutes
$cron->hours = '*';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Base\Cron\EmailReminders';

$cron->save();

$cron = new \GO\Base\Cron\CronJob();

$cron->name = 'Calculate disk usage';
$cron->active = true;
$cron->runonce = false;
$cron->minutes = '0';
$cron->hours = '0';
$cron->monthdays = '*';
$cron->months = '*';
$cron->weekdays = '*';
$cron->job = 'GO\Base\Cron\CalculateDiskUsage';

$cron->save();

\GO\Base\Observable::cacheListeners();

echo "Database created successfully\n";
}catch(Exception $e){
	
	echo $e->getMessage()."\n";
	
	exit(1);
}