<?php
extract($data);

$head="<style>#container{width:300px;}</style>";

require("externalHeader.php");
?>
		<div id="reminderText">
		<?php
			$reminders= $count==1 ? \GO::t("1 reminder") : sprintf(\GO::t("%s reminders"), $count);
			
			if($count>0)
				echo '<p>'.sprintf(\GO::t("You have %s in %s."), $reminders, \GO::config()->product_name).'</p>';
			
			echo $html;

		?>
		</div>
<?php
require("externalFooter.php");
?>
