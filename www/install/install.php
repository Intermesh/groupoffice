<?php

//echo "CONFIG: ".str_replace('<?php' ,'', file_get_contents('/var/www/trunk/config.php'));

require('header.php');

$stmt = \GO::getDbConnection()->query("SHOW TABLES");
$hasTables = $stmt->rowCount()>0;


if($_SERVER['REQUEST_METHOD']=="POST"){
	
	if(isset($_POST['upgrade'])){
		redirect('upgrade.php');
	}
	
	
	if($hasTables){
		trigger_error("Installation aborted because the database is not empty!", E_USER_ERROR);
		exit();
	}
	
	if($_POST['password1']!=$_POST['password2'])
		\GO\Base\Html\Input::setError ('password1', "The passwords didn't match");
	
	\GO\Base\Html\Error::checkRequired();
	
	if(!\GO\Base\Html\Input::hasErrors()){
		
		try{
			\GO::$ignoreAclPermissions=true;

			\GO\Base\Util\SQL::executeSqlFile('install.sql');

			$dbVersion = \GO\Base\Util\Common::countUpgradeQueries("updates.php");

			\GO::config()->save_setting('version', $dbVersion);
			\GO::config()->save_setting('upgrade_mtime', \GO::config()->mtime);

			$adminGroup = new \GO\Base\Model\Group();
			$adminGroup->id=1;
			$adminGroup->name = \GO::t('group_admins');
			if(!$adminGroup->save()) {
				throw new \Exception("Could not save admin group");
			}

			$everyoneGroup = new \GO\Base\Model\Group();
			$everyoneGroup->id=2;
			$everyoneGroup->name = \GO::t('group_everyone');
			if(!$everyoneGroup->save()) {
				throw new \Exception("Could not save everyone group");
			}

			$internalGroup = new \GO\Base\Model\Group();
			$internalGroup->id=3;
			$internalGroup->name = \GO::t('group_internal');
			if(!$internalGroup->save()) {
				throw new \Exception("Could not save internal group");
			}

			\GO::config()->register_user_groups=\GO::t('group_internal');
			\GO::config()->register_visible_user_groups=\GO::t('group_internal');

			$modules = \GO::modules()->getAvailableModules();

			foreach($modules as $moduleClass){
				$moduleController = new $moduleClass;
				if($moduleController->autoInstall() && $moduleController->isInstallable()){
					$module = new \GO\Base\Model\Module();
					$module->id=$moduleController->id();
					if(!$module->save()) {
						throw new \Exception("Could not save module ". $module->id);
					}
				}
			}

			$admin = new \GO\Base\Model\User();
			$admin->first_name = \GO::t('system');
			$admin->last_name = \GO::t('admin');
			$admin->username=$_POST['username'];
			$admin->password=$_POST['password1'];
			$admin->email=\GO::config()->webmaster_email=$_POST['email'];


			\GO::config()->noreply_email='';

			\GO::config()->save();

			//disable password validation
			\GO::config()->password_validate=false;	

			if(!$admin->save()) {
				throw new \Exception("Could not save admin user");
			}

			$adminGroup->addUser($admin->id);

			$admin->checkDefaultModels();


			//module code here because we need the user and the module for this
			if(\GO::modules()->files){
				$folder = \GO\Files\Model\Folder::model()->findByPath('users/'.$admin->username.'/Public', true);
				$folder->visible=true;
				$acl = $folder->setNewAcl();
				$acl->addGroup(\GO::config()->group_everyone, \GO\Base\Model\Acl::DELETE_PERMISSION);
				$folder->save();
				if(!$folder->save()) {
					throw new \Exception("Could not save home folder");
				}
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

			redirect('finished.php');
		}
		catch(\Exception $e) {
			printHead();
			errorMessage($e->getMessage());
			
//			echo nl2br($e->getTraceAsString());
			
			printFoot();
			exit();			
		}
	}
}

printHead();

?>
<h1>Installation</h1>

<?php
if($hasTables){
	
	if(!\GO\Base\Db\Utils::tableExists('go_users')){
		errorMessage("Your database is not empty and doesn't contain a valid ".\GO::config()->product_name." database. Please use an empty database for a fresh install.");
	}else
	{
		?>
		<p><?php echo \GO::config()->product_name; ?> successfully connected to your database!<br />
		A previous version has been detected. Press continue to perform an upgrade. <b>Warning:</b> This can take a long time! Make sure you press continue only once and check the browser loading status.</p>
		<input type="hidden" name="upgrade" value="1" />
		<?php
		continueButton();
	}
}else{
	?>
	<p>
	<?php echo \GO::config()->product_name; ?> successfully connected to your database!<br />
	Enter the administrator account details and click on 'Continue' to create the database for <?php echo \GO::config()->product_name; ?>. This can take some time. Don't interrupt this process.
	</p>
	<h2>Administrator</h2>
	<?php
	
	\GO\Base\Html\Input::render(array(
		"label"=>"Username",
		"name"=>"username",
		"required"=>true
	));
	
	\GO\Base\Html\Input::render(array(
		"label"=>"Password",
		"name"=>"password1",
		"required"=>true,
		"type"=>"password"
	));
	\GO\Base\Html\Input::render(array(
		"label"=>"Confirm password",
		"name"=>"password2",
		"required"=>true,
		"type"=>"password"
	));
	
	\GO\Base\Html\Input::render(array(
		"label"=>"Email",
		"name"=>"email",
		"required"=>true
	));
	
	continueButton();
}

?>
<?php


printFoot();