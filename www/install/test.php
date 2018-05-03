<?php
require('../vendor/autoload.php');

if($_SERVER['REQUEST_METHOD'] == "POST") {
	header('Location: configfile.php');
	exit();	
}

require('header.php');
?>

<section>
	<form method="POST" action="" onsubmit="submitButton.disabled = true;">
		<fieldset>
			
			<h2>System requirements test</h2>
			
			<?php 
			require('gotest.php'); 
			$ok = output_system_test();
			?>
			
		</fieldset>

		<button name="submitButton" type="submit" <?php echo $ok ? "" : "disabled"; ?>>Continue</button>
	</form>

</section>

<?php
require('footer.php');

