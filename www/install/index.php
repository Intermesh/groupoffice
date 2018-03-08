<?php
require('header.php');
if($_SERVER['REQUEST_METHOD']=='POST'){
	redirect("license.php");
}


printHead();

?>
<h1>Thank you for installing <?php echo \GO::config()->product_name; ?>!</h1>
<p>This page checks if your system meets the requirements to run <?php echo \GO::config()->product_name; ?>.</p>
<p>If this page prints errors or warnings, please visit this page for more information: <a target="_blank" href="https://www.group-office.com/wiki/Installation">https://www.group-office.com/wiki/Installation</a></p>

<h2>System test</h2>
<?php

require('gotest.php');

if(!output_system_test())
{
	echo '<p style="color: red;">Because of a fatal error in your system setup the installation can\'t continue. Please fix the errors above first.</p>';
}else
{	
	echo continueButton();
}

printFoot();