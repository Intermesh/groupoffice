<?php
require('../vendor/autoload.php');
\go\core\App::get();

if($_SERVER['REQUEST_METHOD'] == "POST" || go()->getConfig()['core']['general']['servermanager']) {
	header('Location: configfile.php');
	exit();	
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
			
			<h2><?= go()->t("System requirements test"); ?></h2>
			
			<?php 
			require('gotest.php'); 
			$ok = output_system_test();
			?>
            <button class="primary right" name="submitButton" type="submit" <?php echo $ok ? "" : "disabled"; ?>><?= go()->t('Continue'); ?></button>
		</fieldset>


	</form>

</section>

<?php
require('footer.php');

