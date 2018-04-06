<?php
if($_SERVER['REQUEST_METHOD'] == "POST") {
	header('Location: ../');
	exit();	
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
				<h2>Installation complete!</h2>
				<p>Thank you for installing Group-Office.</p>				
		</fieldset>

		<button name="submitButton" type="submit">Continue</button>
	</form>

</section>

<?php
require('footer.php');


