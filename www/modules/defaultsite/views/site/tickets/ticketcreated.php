<h1><?php echo \GO::t("We received your ticket", "defaultsite"); ?></h1>
<p><?php echo \GO::t("Thank you, we received your ticket and we will keep you informed about the status per e-mail.", "defaultsite"); ?></p>


<?php echo '<a href="'.\Site::urlManager()->createUrl("tickets/externalpage/ticket",array("ticket_number"=>$ticket->ticket_number,"ticket_verifier"=>$ticket->ticket_verifier)).'">'.\GO::t("Go to ticket", "defaultsite").'</a>'; ?>
