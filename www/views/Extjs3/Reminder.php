<?php
extract($data);

$head="<style>#container{width:300px;}</style>";

require("externalHeader.php");
?>
		<div id="reminderText">
		<?php
			$reminders= $count==1 ? \GO::t('oneReminder') : sprintf(\GO::t('nReminders'), $count);
			
			if($count>0)
				echo '<p>'.sprintf(\GO::t('youHaveReminders'), $reminders, \GO::config()->product_name).'</p>';
			
			echo $html;

		?>
		</div>
<?php
require("externalFooter.php");
?>