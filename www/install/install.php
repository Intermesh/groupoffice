<?php
require('../vendor/autoload.php');

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);


require("gotest.php");
if(!systemIsOk()) {
	header("Location: test.php");
	exit();
}


use GO\Base\Cron\CronJob;
use GO\Base\Model\Module;
use GO\Base\Observable;
use go\modules\community\bookmarks\Module as BookmarksModule;
use go\modules\community\comments\Module as CommentsModule;
use go\core\App;
use go\core\jmap\State;
use go\core;
use go\modules\community\googleauthenticator\Module as GAModule;
use go\modules\community\notes\Module as NotesModule;
use go\modules\community\addressbook\Module as AddressBookModule;


function dbIsEmpty() {
	//global $pdo;
	/* @var $pdo \PDO; */
	
	$stmt = App::get()->getDbConnection()->query("SHOW TABLES");
	$stmt->execute();
	
	$empty = !$stmt->fetch();
	$stmt->closeCursor();
	
	return $empty;
}

if(!dbIsEmpty()) {
	header("Location: upgrade.php");
	exit();
}

$passwordMatch = true;
				
if (!empty($_POST)) {

	if ($_POST['password'] == $_POST['passwordConfirm']) {
		
//		go()->getDbConnection()->exec("DROP DATABASE test");
//		go()->getDbConnection()->exec("CREATE DATABASE test");
//		go()->getDbConnection()->exec("USE test");

		App::get()->setAuthState(new core\auth\TemporaryState());

		$admin = [
				'displayName' => "System Administrator",
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'email' => $_POST['email']
		];

		App::get()->getInstaller()->install($admin, [
				AddressBookModule::get(),
				NotesModule::get(),
				GAModule::get(),
				CommentsModule::get(),
				BookmarksModule::get()
				]);

		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();
			if ($moduleController instanceof core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
                if(!Module::install($moduleController->name())) {
                    throw new \Exception("Could not save module " . $moduleController->name());
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
	
				
		\go\core\model\User::findById(1)->legacyOnSave();
		
		
		if(go()->getConfig()['core']['general']['servermanager']) {
			exec("php ".\go\core\Environment::get()->getInstallFolder() .'/go/modules/community/multi_instance/oninstall.php '.go()->getConfig()['core']['general']['servermanager']. ' '.explode(':',$_SERVER['HTTP_HOST'])[0], $output, $ret);
		}		

		header("Location: finished.php");
		exit();
	} else
	{
		$passwordMatch = false;
	}
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
			<h2>Create an administrator account</h2>
			<p>Please fill in the details for the administrative account and press "Install".</p>
			<p>
				<label>E-mail</label>
				<input type="email" name="email" value="<?= $_POST['email'] ?? ""; ?>" required />
				
			</p>
			<p>
				<label>Username</label>
				<input type="text" name="username" value="<?= $_POST['username'] ?? "admin"; ?>" required />				
			</p>
			
			<?php
			if(!$passwordMatch) {
				echo '<p class="error">The passwords didn\'t match</p>';
			}
			?>
			
			<p>
				<label>Password</label>
				<input type="password" name="password" pattern=".{6,}" value="<?= $_POST['password'] ?? ""; ?>" title="Minimum length is 6 chars" required />								
			</p>

			<p>
				<label>Confirm</label>
				<input type="password" name="passwordConfirm" pattern=".{6,}" title="Minimum length is 6 chars"  value="<?= $_POST['passwordConfirm'] ?? ""; ?>" required />				
			</p>
		</fieldset>

		<button name="submitButton" type="submit">Install</button>
	</form>

</section>

<?php
require('footer.php');
