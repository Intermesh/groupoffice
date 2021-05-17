<?php
require('../vendor/autoload.php');
\go\core\App::get();
\go()->setCache(new \go\core\cache\None());

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);




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



$passwordMatch = true;

if (!empty($_POST)) {

    go()->getSettings()->license = !empty($_POST['licenseDenied']) ? null : $_POST['license'];
    go()->getSettings()->licenseDenied = !empty($_POST['licenseDenied']);
    try {
	    go()->getSettings()->save();

	    header("Location: upgrade.php");
	    exit();
    }
    catch(Exception $e) {
	    $error = $e->getMessage();
    }

} else{
    if(!empty(go()->getSettings()->license) && !\go\modules\business\license\model\License::isValid()) {
	    $error = \go\modules\business\license\model\License::$validationError;
    }
}

require('header.php');
?>
<script>
function getLicense() {
    window.open('https://www.group-office.com/30-day-trial?hostname=' + document.domain + '&version=<?= go()->getMajorVersion() ?>' , '_blank');
}

function noThanks() {
	document.forms[0].licenseDenied.value = "1";
	document.forms[0].submit();
}
</script>
	<section>
		<form method="POST" action="" onsubmit="submitButton.disabled = true;">
			<fieldset>
				<h2>Install license</h2>
				<p>Install your purchased license or try all the features for free and obtain a 60 day trial license from <a target="_blank" class="normal-link" href="https://www.group-office.com">www.group-office.com</a>. Register for an account and get your license now.</p>

                <button type="button" class="primary" onclick="getLicense()">Get license now</button>

                <?php
                if(isset($error)) {
                    echo '<p class="error">' . $error . '</p>';
                }
                ?>
				<p>
					<label>License key</label>
					<textarea style="height: 100px" name="license" required><?= $_POST['license'] ?? go()->getSettings()->license; ?></textarea>
				</p>

                <input type="hidden" name="licenseDenied" value="0" />

                <button type="button" onclick="noThanks()">No thanks</button>
				<button class="right primary" name="submitButton" type="submit">Install</button>
			</fieldset>


		</form>

	</section>

<?php
require('footer.php');
