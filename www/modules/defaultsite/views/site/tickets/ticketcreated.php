<h1><?php echo \GO::t('ticketCreatedTitle','defaultsite'); ?></h1>
<p><?php echo \GO::t('ticketCreatedText','defaultsite'); ?></p>


<?php echo '<a href="'.\Site::urlManager()->createUrl("tickets/externalpage/ticket",array("ticket_number"=>$ticket->ticket_number,"ticket_verifier"=>$ticket->ticket_verifier)).'">'.\GO::t('gotoTicket','defaultsite').'</a>'; ?>