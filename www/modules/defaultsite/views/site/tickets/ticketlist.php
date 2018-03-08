<?php \Site::scripts()->registerCssFile(\Site::file('css/ticket.css')); ?>

<div class="external-ticket-page ticketlist">
	<div class="wrapper">
	
		<h2><?php echo \GO::t("Ticketlist", "defaultsite"); ?></h2>

		<a id="new-ticket-button" href="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/newticket'); ?>" class="button"><?php echo \GO::t("New Ticket", "defaultsite"); ?></a>
				
		<?php $pager = new \GO\Site\Widget\Pager(array(
			'previousPageClass'=>'pagination-arrow-right',
			'nextPageClass'=>'pagination-arrow-left',
			'store'=>$ticketstore
			)); 
		?>
				
		<h3><?php echo \GO::t("Your tickets", "defaultsite"); ?></h3> 
		
		<span id="ticket-filter"><?php echo \GO::t("Filter", "defaultsite"); ?>
			<select onchange="window.location = this.options[this.selectedIndex].value;">
				<option><?php echo \GO::t("Select one", "defaultsite"); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'all')); ?>"><?php echo \GO::t("All", "defaultsite"); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'openprogress')); ?>"><?php echo \GO::t("Open and in progress", "defaultsite"); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'open')); ?>"><?php echo \GO::t("Open", "defaultsite"); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'progress')); ?>"><?php echo \GO::t("In progress", "defaultsite"); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'closed')); ?>"><?php echo \GO::t("Closed", "defaultsite"); ?></option>
			</select>
		</span>
		
		<table class="table-ticketlist">
			<tr>
				<th><?php echo \GO::t("Ticket no.", "defaultsite"); ?></th>
				<th><?php echo \GO::t("Subject", "defaultsite"); ?></th>
				<th><?php echo \GO::t("Status", "defaultsite"); ?></th>
				<th><?php echo \GO::t("Agent", "defaultsite"); ?></th>
				<th><?php echo \GO::t("Created", "defaultsite"); ?></th>
			</tr>
			<tr>
				<th colspan="5"><?php $pager->render(); ?></th>
			</tr>

			<?php if(!$pager->getItems()): ?>
				<tr><td colspan="5"><?php echo \GO::t("No tickets found", "defaultsite"); ?></td></tr>
			<?php else: ?>
				<?php foreach($pager->getItems() as $i => $ticket): ?>
					<tr class="<?php echo($i%2)?'even':'odd'; ?>">
						<td><?php echo '<a href="'.\Site::urlManager()->createUrl("tickets/externalpage/ticket",array("ticket_number"=>$ticket->ticket_number,"ticket_verifier"=>$ticket->ticket_verifier)).'">'.$ticket->ticket_number.'</a>'; ?></td>
						<td><?php echo $ticket->subject; ?></td>
						<td><?php echo $ticket->getStatusName(); ?></td>
						<td><?php echo $ticket->agent?$ticket->agent->name:""; ?></td>
						<td><?php echo $ticket->getAttribute("ctime","formatted"); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</table>

	</div>
</div>
