<?php

use go\core\model\Settings;

if($_SERVER['REQUEST_METHOD'] == "POST") {
	header('Location: ../');
	exit();	
}
require('../vendor/autoload.php');
require('header.php');

if(go()->getConfig()['servermanager']) {
	$cls = go()->getConfig()['cache'];
	go()->setCache(new $cls);
    $cmd = "php ".\go\core\Environment::get()->getInstallFolder() .'/go/modules/community/multi_instance/oninstall.php '.go()->getConfig()['servermanager']. ' '.explode(':',$_SERVER['HTTP_HOST'])[0];
	exec($cmd, $output, $ret);

    if($ret != 0) {
        \go\core\ErrorHandler::log("Error after Multi instance install: " . $cmd ." " . implode("\n",$output));
    }
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


