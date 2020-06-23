<?php
require('../vendor/autoload.php');

if(!empty($_SERVER['REQUEST_METHOD'] == 'POST')) {
	header('Location: test.php');
	exit();	
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
				<h2><?= go()->t('Install Group-Office'); ?></h2>
				<p><?= go()->t('Thank you for installing Group-Office.'); ?></p>
		</fieldset>

		<button name="submitButton" type="submit"><?= go()->t('Continue'); ?></button>
	</form>

</section>

<?php
require('footer.php');

