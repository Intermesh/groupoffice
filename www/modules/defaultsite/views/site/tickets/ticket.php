<?php \Site::scripts()->registerCssFile(\Site::file('css/ticket.css')); ?>

<div class="external-ticket-page ticket">
	<div class="wrapper">
		
		<?php if(\GO::user()): ?>
				&lt;&lt;&nbsp;<a id="back-to-overview-button" href="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist'); ?>"><?php echo \GO::t("Back to Overview", "defaultsite"); ?></a>			
		<?php endif; ?>
		
		<h2><?php echo \GO::t("Ticket", "defaultsite").' '. $ticket->ticket_number; ?></h2>
		
<!--		<h3><?php echo \GO::t("Contact Info", "defaultsite"); ?></h3>
		
		<table class="table-contactinformation">
			<tr>
				<td><?php echo \GO::t("First Name", "defaultsite"); ?></td>
				<td><?php echo $ticket->first_name; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Middle Name", "defaultsite"); ?></td>
				<td><?php echo $ticket->middle_name; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Last Name", "defaultsite"); ?></td>
				<td><?php echo $ticket->last_name; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Email", "defaultsite"); ?></td>
				<td><?php echo $ticket->email; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Phone", "defaultsite"); ?></td>
				<td><?php echo $ticket->phone; ?></td>
			</tr>
		</table>
		-->
		<h3><?php echo \GO::t("Ticket information", "defaultsite"); ?></h3>
		
		<table class="table-ticketinformation">
			<tr>
				<td><?php echo \GO::t("Subject", "defaultsite"); ?></td>
				<td><?php echo $ticket->subject; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Type", "defaultsite"); ?></td>
				<td><?php echo $ticket->type->name; ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Status", "defaultsite"); ?></td>
				<td>
					<?php if(!$ticket->isNew && !$ticket->isClosed()): ?>
						<form method="POST">
							<input type="hidden" value="close" name="close" />
							<?php echo $ticket->status_id?$ticket->getStatusName():\GO::t("Open", "defaultsite"); ?> [<input title="<?php echo \GO::t("Close this ticket. You cannot respond to this ticket anymore when you have closed it.", "defaultsite"); ?>" type="submit" id="close-ticket-button"  class="" value="<?php echo \GO::t("Close", "defaultsite"); ?>" />]
						</form>
					<?php else: ?>
						<?php echo $ticket->status_id?$ticket->getStatusName():\GO::t("Open", "defaultsite"); ?>
					<?php  endif; ?>
				</td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Priority", "defaultsite"); ?></td>
				<td><?php echo $ticket->priority?\GO::t("Yes"):\GO::t("No"); ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Date", "defaultsite"); ?></td>
				<td><?php echo $ticket->getAttribute("ctime","formatted"); ?></td>
			</tr>
			<tr>
				<td><?php echo \GO::t("Agent", "defaultsite"); ?></td>
				<td><?php echo $ticket->agent?$ticket->agent->name:''; ?></td>
			</tr>
			
<!--			
			Example for adding custom field.
			<tr>
				<td><?php //echo $ticket->getCustomfieldsRecord()->getAttributeLabelWithoutCategoryName('col_58'); ?></td>
				<td><?php //echo $ticket->getCustomfieldsRecord()->col_58; ?></td>
			</tr>
-->
			
		</table>

			
		<h3><?php echo \GO::t("Discussion", "defaultsite"); ?></h3>
		
		<table class="table-ticketmessages">
			
			<?php foreach($messages as $i => $message): ?>
			<tr class="<?php echo($i%2)?'even':'odd'; ?>">
				<td>
					<span class="ticketmessage-name"><?php echo $message->posterName; ?></span><span class="ticketmessage-time"><?php echo $message->getAttribute("ctime","formatted"); ?></span>
					<p class="ticketmessage-message"><?php echo $message->getAttribute("content","html"); ?></p>

					<?php if (!empty($message->attachments)): ?>
						<span class="ticketmessage-files"><?php echo \GO::t("Files", "defaultsite"); ?>:</span>
							<?php foreach ($message->getFiles() as $file => $obj): ?>
								<a target="_blank" href="<?php echo \Site::urlManager()->createUrl('tickets/site/downloadAttachment',array('file'=>$obj->id,'ticket_number'=>$ticket->ticket_number,'ticket_verifier'=>$ticket->ticket_verifier)); ?>">
									<?php echo $file; ?>
								</a>
							<?php endforeach; ?>
					<?php endif; ?>

				<?php if($message->has_status): ?>
					<p><strong>Status</strong>: <?php echo \GO\Tickets\Model\Status::getName($message->status_id); ?></p>
				<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
			
		</table>	
			
		<?php if(!$ticket->isClosed()): ?>
		
			<?php $form = new \GO\Site\Widget\Form(); ?>
			<?php echo $form->beginForm(false,false,array('enctype'=>'multipart/form-data')); ?>
		
			<?php $uploader = new \GO\Site\Widget\Plupload\Widget(); ?>
		
			<h3><?php echo \GO::t("Your message", "defaultsite"); ?></h3>

			<?php echo $form->textArea($new_message,'content',array('required'=>true)); ?>
			<?php echo $uploader->render(); ?>
			<div class="button-bar">
				<?php echo $form->submitButton(\GO::t("Add Comment", "defaultsite"),array('id'=>'submit-ticket-button', 'class'=>'button')); ?>
			</div>
			<?php echo $form->endForm(); ?>
		<?php endif; ?>
	</div>
</div>
