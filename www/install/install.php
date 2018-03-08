<?php

//echo "CONFIG: ".str_replace('<?php' ,'', file_get_contents('/var/www/trunk/config.php'));

use go\core\App;
use go\core\cli\State;

require(__DIR__ . "/../vendor/autoload.php");

require('header.php');

//\GO::getDbConnection()->query("DROP DATABASE 63_test");
//\GO::getDbConnection()->query("CREATE DATABASE 63_test");
//\GO::getDbConnection()->query("USE 63_test");



$stmt = \GO::getDbConnection()->query("SHOW TABLES");
$hasTables = $stmt->rowCount() > 0;
unset($stmt);

if ($_SERVER['REQUEST_METHOD'] == "POST") {

	if (isset($_POST['upgrade'])) {
		redirect('upgrade.php');
	}


	if ($hasTables) {
		trigger_error("Installation aborted because the database is not empty!", E_USER_ERROR);
		exit();
	}

	if ($_POST['password1'] != $_POST['password2'])
		\GO\Base\Html\Input::setError('password1', "The passwords didn't match");

	\GO\Base\Html\Error::checkRequired();

	if (!\GO\Base\Html\Input::hasErrors()) {

		try {



//Create the app with the database connection
			App::get()->setAuthState(new State());

			$admin = ['displayName' => "System Administrator",
			'username' => $_POST['username'],
			'password' => $_POST['password1'],
			'email' => $_POST['email']];
			
			\GO::config()->webmaster_email = $_POST['email'];

			App::get()->getInstaller()->install($admin, [new \go\modules\community\notes\Module(), new \go\modules\community\googleauthenticator\Module()]);


			//install not yet refactored modules
			\GO::$ignoreAclPermissions = true;
			$modules = \GO::modules()->getAvailableModules();

			foreach($modules as $moduleClass){
				
				$moduleController = new $moduleClass;
				if($moduleController instanceof \go\core\module\Base) {
					continue;
				}
				if($moduleController->autoInstall() && $moduleController->isInstallable()){
					$module = new \GO\Base\Model\Module();
					$module->name=$moduleController->name();
					if(!$module->save()) {
						throw new \Exception("Could not save module ". $module->name);
					}
				}
			}
			
			\GO::config()->register_user_groups = \GO::t("Internal");
			\GO::config()->register_visible_user_groups = \GO::t("Internal");


			\GO::config()->noreply_email = '';

			\GO::config()->save();

	
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
		} catch (\Exception $e) {
			printHead();
			errorMessage($e->getMessage());

			echo nl2br($e->getTraceAsString());

			printFoot();
			exit();
		}
	}
}

printHead();
?>
<h1>Installation</h1>

<?php
if ($hasTables) {

	if (!\GO\Base\Db\Utils::tableExists('core_user')) {
		errorMessage("Your database is not empty and doesn't contain a valid " . \GO::config()->product_name . " database. Please use an empty database for a fresh install.");
	} else {
		?>
		<p><?php echo \GO::config()->product_name; ?> successfully connected to your database!<br />
			A previous version has been detected. Press continue to perform an upgrade. <b>Warning:</b> This can take a long time! Make sure you press continue only once and check the browser loading status.</p>
		<input type="hidden" name="upgrade" value="1" />
		<?php
		continueButton();
	}
} else {
	?>
	<p>
	<?php echo \GO::config()->product_name; ?> successfully connected to your database!<br />
		Enter the administrator account details and click on 'Continue' to create the database for <?php echo \GO::config()->product_name; ?>. This can take some time. Don't interrupt this process.
	</p>
	<h2>Administrator</h2>
	<?php
	\GO\Base\Html\Input::render(array(
			"label" => "Username",
			"name" => "username",
			"required" => true
	));

	\GO\Base\Html\Input::render(array(
			"label" => "Password",
			"name" => "password1",
			"required" => true,
			"type" => "password"
	));
	\GO\Base\Html\Input::render(array(
			"label" => "Confirm password",
			"name" => "password2",
			"required" => true,
			"type" => "password"
	));

	\GO\Base\Html\Input::render(array(
			"label" => "Email",
			"name" => "email",
			"required" => true
	));

	continueButton();
}
?>
<?php
printFoot();
