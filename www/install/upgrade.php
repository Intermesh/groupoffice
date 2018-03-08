<?php
require('header.php');

if($_SERVER['REQUEST_METHOD']=="POST")
		redirect('finished.php');

printHead();

?>
<h1>Upgrading</h1>
<?php

$mc = new \GO\Core\Controller\MaintenanceController();
$mc->run("upgrade",array(),false);

continueButton();

printFoot();