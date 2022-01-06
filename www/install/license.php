<?php
require('../vendor/autoload.php');

use go\core\event\EventEmitterTrait;
use go\core\model\Module;
use go\modules\business\studio\Module as StudioModule;
use go\core\App;

App::get();
go()->setCache(new \go\core\cache\None());

// needed for invalid studio modules when upgrading for 6.5. They need to be patched before auto loaded by the event
// system.
EventEmitterTrait::$disableEvents = true;

if(Module::isInstalled("business", "studio")) {
	$studioError = StudioModule::patch65to66();
}

ini_set('zlib.output_compression', 0);
ini_set('implicit_flush', 1);





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
				<p>Try the extra features for free and obtain a 60 day trial license from  <a target="_blank" class="normal-link" href="https://www.group-office.com">www.group-office.com</a>. Register for an account and get your license now. By purchasing a license you will get support and extra features. Find out more on our website.</p>

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
