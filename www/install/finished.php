<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	header('Location: ../');
	exit();	
}
require('../vendor/autoload.php');
require('header.php');
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


