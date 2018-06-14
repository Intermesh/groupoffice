<?php
require('../vendor/autoload.php');

if(!empty($_POST)) {
	header('Location: test.php');
	exit();	
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
				<h2>Thank you!</h2>
				<p>Thank you for installing Group-Office groupware. After reading and accepting the license click "Continue".</p>
	
				<label>
					<input name="accept" type="checkbox" required />
					I accept the <a target="_blank" href="../LICENSE.TXT">Group-Office groupware license</a>.
				</label>
		</fieldset>

		<button name="submitButton" type="submit">Continue</button>
	</form>

</section>

<?php
require('footer.php');

