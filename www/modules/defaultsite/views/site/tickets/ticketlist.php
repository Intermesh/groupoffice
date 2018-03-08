<?php \Site::scripts()->registerCssFile(\Site::file('css/ticket.css')); ?>

<div class="external-ticket-page ticketlist">
	<div class="wrapper">
	
		<h2><?php echo \GO::t('ticketList','defaultsite'); ?></h2>

		<a id="new-ticket-button" href="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/newticket'); ?>" class="button"><?php echo \GO::t('ticketNewTicket','defaultsite'); ?></a>
				
		<?php $pager = new \GO\Site\Widget\Pager(array(
			'previousPageClass'=>'pagination-arrow-right',
			'nextPageClass'=>'pagination-arrow-left',
			'store'=>$ticketstore
			)); 
		?>
				
		<h3><?php echo \GO::t('ticketYourTickets','defaultsite'); ?></h3> 
		
		<span id="ticket-filter"><?php echo \GO::t('ticketFilter','defaultsite'); ?>
			<select onchange="window.location = this.options[this.selectedIndex].value;">
				<option><?php echo \GO::t('selectOne','defaultsite'); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'all')); ?>"><?php echo \GO::t('ticketFilterAll','defaultsite'); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'openprogress')); ?>"><?php echo \GO::t('ticketFilterOpenInProgress','defaultsite'); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'open')); ?>"><?php echo \GO::t('ticketFilterOpen','defaultsite'); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'progress')); ?>"><?php echo \GO::t('ticketFilterInProgress','defaultsite'); ?></option>
				<option value="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist',array('filter'=>'closed')); ?>"><?php echo \GO::t('ticketFilterClose','defaultsite'); ?></option>
			</select>
		</span>
		
		<table class="table-ticketlist">
			<tr>
				<th><?php echo \GO::t('ticketNumber','defaultsite'); ?></th>
				<th><?php echo \GO::t('ticketSubject','defaultsite'); ?></th>
				<th><?php echo \GO::t('ticketStatus','defaultsite'); ?></th>
				<th><?php echo \GO::t('ticketAgent','defaultsite'); ?></th>
				<th><?php echo \GO::t('ticketCreated','defaultsite'); ?></th>
			</tr>
			<tr>
				<th colspan="5"><?php $pager->render(); ?></th>
			</tr>

			<?php if(!$pager->getItems()): ?>
				<tr><td colspan="5"><?php echo \GO::t('ticketNoneFound','defaultsite'); ?></td></tr>
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
