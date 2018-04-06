<?php
require('../vendor/autoload.php');

use GO\Base\Cron\CronJob;
use GO\Base\Model\Module as Module2;
use GO\Base\Observable;
use go\core\App;
use go\core\jmap\State;
use go\core\module\Base;
use go\modules\community\googleauthenticator\Module as Module3;
use go\modules\community\notes\Module;



if (!empty($_POST)) {

	if ($_POST['password'] == $_POST['passwordConfirm']) {

		App::get()->setAuthState(new State());

		$admin = [
				'displayName' => "System Administrator",
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'email' => $_POST['email']
						];

		App::get()->getInstaller()->install($admin, [new Module(), new Module3()]);


		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = new $moduleClass;
			if ($moduleController instanceof Base) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
				$module = new Module2();
				$module->name = $moduleController->name();
				if (!$module->save()) {
					throw new Exception("Could not save module " . $module->name);
				}
			}
		}


		//Insert default cronjob record for email reminders
		$cron = new CronJob();

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

		$cron = new CronJob();

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

		Observable::cacheListeners();
		
		header("Location: finished.php");
		exit();
	}
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
			<h2>Create an administrator account</h2>
			<p>
				<input type="email" name="email" value="<?= $_POST['email'] ?? ""; ?>" required />
				<label>E-mail</label>
			</p>
			<p>
				<input type="text" name="username" value="<?= $_POST['username'] ?? "admin"; ?>" required />
				<label>Username</label>
			</p>
			<p>
				<input type="password" name="password" pattern=".{6,}" value="<?= $_POST['password']; ?>" title="Minimum length is 6 chars" required />				
				<label>Password</label>
			</p>

			<p>
				<input type="password" name="passwordConfirm" value="<?= $_POST['passwordConfirm']; ?>" required />
				<label>Confirm</label>
			</p>
		</fieldset>

		<button name="submitButton" type="submit">Install</button>
	</form>

</section>

<?php
require('footer.php');
