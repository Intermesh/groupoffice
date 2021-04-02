<?php

use go\core\model\Settings;

if($_SERVER['REQUEST_METHOD'] == "POST") {
	header('Location: ../');
	exit();	
}
require('../vendor/autoload.php');
require('header.php');

if(go()->getConfig()['core']['general']['servermanager']) {
	$cls = go()->getConfig()['cache'];
	go()->setCache(new $cls);
	exec("php ".\go\core\Environment::get()->getInstallFolder() .'/go/modules/community/multi_instance/oninstall.php '.go()->getConfig()['core']['general']['servermanager']. ' '.explode(':',$_SERVER['HTTP_HOST'])[0], $output, $ret);
	Settings::flushCache();
    go()->rebuildCache();
}

?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
            <h2>Installation complete!</h2>
            <p>Thank you for installing Group-Office.</p>

            <button class="primary right" name="submitButton" type="submit"><?= go()->t('Continue'); ?></button>
		</fieldset>


	</form>

</section>

<?php
require('footer.php');


