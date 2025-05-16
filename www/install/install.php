<?php
use GO\Base\Cron\CronJob;
use GO\Base\Model\Module;
use GO\Base\Observable;
use go\core\ErrorHandler;
use go\core\event\EventEmitterTrait;
use go\core\mail\Util;
use go\core\App;
use go\core;
use go\core\model\User;

require('../vendor/autoload.php');
App::get();


header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);

// needed for invalid studio modules when upgrading for 6.5. They need to be patched before auto loaded by the event
// system.
go()->disableEvents();
require("gotest.php");

if(!systemIsOk()) {
	header("Location: test.php");
	exit();
}

function dbIsEmpty(): bool
{
	//global $pdo;
	/* @var $pdo PDO; */
	
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

    try{

        if ($_POST['password'] != $_POST['passwordConfirm']) {
            throw new Exception(go()->t("The passwords didn't match"));
        }

        if(!preg_match(User::USERNAME_REGEX, $_POST['username'])) {
            throw new Exception(go()->t("You have invalid characters in the username") . " (a-z, 0-9, -, _, ., @).");
        }

        if(!Util::validateEmail($_POST['email'])) {
	        throw new Exception(go()->t("You entered an invalid e-mail address"));
        }

        if(strlen($_POST['password']) < 6) {
	        throw new Exception(go()->t("Minimum password length is 6 chars"));
        }

		App::get()->setAuthState(new core\auth\TemporaryState());

		$admin = [
				'displayName' => "System Administrator",
				'username' => $_POST['username'],
				'password' => $_POST['password'],
				'email' => $_POST['email'],
                'language' => $_POST['language']
		];

		App::get()->getInstaller()->install($admin);

		//install not yet refactored modules
		GO::$ignoreAclPermissions = true;
		$modules = GO::modules()->getAvailableModules();

		foreach ($modules as $moduleClass) {

			$moduleController = $moduleClass::get();
			if ($moduleController instanceof core\Module) {
				continue;
			}
			if ($moduleController->autoInstall() && $moduleController->isInstallable()) {
			    try {
                    Module::install($moduleController->name());
                }
                catch(Exception $e) {
			        //could be a license error due to an unlicensed module depending
                  //on a licensed module
			        ErrorHandler::logException($e);
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

		if(!$cron->save()) {
			var_dump($cron->getValidationErrors());
			throw new Exception("Could not save email reminders cron");
		}

		$cron = new CronJob();

		$cron->name = 'Calculate disk usage';
		$cron->active = true;
		$cron->runonce = false;
		$cron->minutes = '1';
		$cron->hours = '1';
		$cron->monthdays = '*';
		$cron->months = '*';
		$cron->weekdays = '*';
		$cron->job = 'GO\Base\Cron\CalculateDiskUsage';

		if(!$cron->save()) {
			var_dump($cron->getValidationErrors());
			throw new Exception("Could not save calculate disk usage cron");
		}

		Observable::cacheListeners();


		User::findById(1)->legacyOnSave();

		header("Location: finished.php");
		exit();
	}
	catch(Exception $e) {
        $error = $e->getMessage();

        echo $e->getTraceAsString();
	}

}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;this.getElementsByTagName('fieldset')[0].classList.add('mask');">

		<fieldset>

            <div class="mask-message">
                <div class="x-mask-loading"></div>
                Installing...
            </div>

            <h2>Create an administrator account</h2>

            <?php
            if(!empty($error)) {
                echo '<p class="error">' . $error . '</p>';
            }
            ?>

			<p>Please fill in the details for the administrative account and press "Install".</p>

			<p>
				<label>E-mail</label>
				<input type="email" name="email" value="<?= $_POST['email'] ?? ""; ?>" required>
				
			</p>
			<p>
				<label>Username</label>
				<input type="text" autocomplete="username" name="username" pattern="[A-Za-z0-9\-_@\.]+" title="<?= htmlentities(go()->t("You have invalid characters in the username") . " (a-z, 0-9, -, _, ., @)"); ?>" value="<?= $_POST['username'] ?? "admin"; ?>" required />
			</p>

			
			<p>
				<label>Password</label>
				<input type="password" name="password" autocomplete="new-password" pattern=".{6,}" value="<?= $_POST['password'] ?? ""; ?>" title="Minimum length is 6 chars" required>
			</p>

			<p>
				<label>Confirm</label>
				<input type="password" name="passwordConfirm" autocomplete="new-password" pattern=".{6,}" title="Minimum length is 6 chars"  value="<?= $_POST['passwordConfirm'] ?? ""; ?>" required>
			</p>

            <p>
                <label>Language</label>
                <select name="language">

                <?php
                foreach(go()->getLanguage()->getLanguages() as $iso => $language) {
                    ?>
                    <option value="<?= $iso; ?>" <?= go()->getLanguage()->getIsoCode() == $iso ? "selected" : ""; ?>><?= htmlspecialchars($language); ?></option>
                    <?php
                }

                ?>

                </select>
            </p>


            <button class="right primary" name="submitButton" type="submit">Install</button>
		</fieldset>


	</form>

</section>

<?php
require('footer.php');
