<?php


require('header.php');

if(!empty($_POST)) {
	header('Location: test.php');
	exit();	
}

?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
				<h2>Thank you!</h2>
				<p>For installing Group-Office.</p>
	
				<label>
					<input name="accept" type="checkbox" required />
					I accept the AGPL license.
				</label>
		</fieldset>

		<button name="submitButton" type="submit">Continue</button>
	</form>

</section>

<?php
require('footer.php');

