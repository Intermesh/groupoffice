<?php \Site::scripts()->registerCssFile(\Site::file('css/ticket.css')); ?>

<div class="external-ticket-page newticket ticket">
	<div class="wrapper">
		
			<?php if(\GO::user()): ?>
				&lt;&lt;&nbsp;<a id="back-to-overview-button" href="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist'); ?>"><?php echo \GO::t("Back to Overview", "defaultsite"); ?></a>			
			<?php endif; ?>

		
		<h2><?php echo \GO::t("New Ticket", "defaultsite"); ?></h2>
		

		
		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(false,false,array('enctype'=>'multipart/form-data')); ?>

		<h3><?php echo \GO::t("Contact Info", "defaultsite"); ?></h3>
		
		<table class="table-contactinformation">
			<tr>
				<td><?php echo $form->label($ticket, 'first_name',array('label'=>\GO::t("First Name", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'first_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'middle_name',array('label'=>\GO::t("Middle Name", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'middle_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'last_name',array('label'=>\GO::t("Last Name", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'last_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'email',array('label'=>\GO::t("Email", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'email'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'phone',array('label'=>\GO::t("Phone", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'phone'); ?></td>
			</tr>
		</table>
		
		<h3><?php echo \GO::t("Ticket information", "defaultsite"); ?></h3>
		
		<table class="table-ticketinformation">
			<tr>
				<td><?php echo $form->label($ticket, 'subject',array('label'=>\GO::t("Subject", "defaultsite"))); ?></td>
				<td><?php echo $form->textField($ticket, 'subject'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'type_id',array('label'=>\GO::t("Type", "defaultsite"))); ?></td>
				<td><?php echo $form->dropDownList($ticket, 'type_id', $form->listData($ticketTypes, 'id', 'name')); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'status',array('label'=>\GO::t("Status", "defaultsite"))); ?></td>
				<td><?php echo \GO::t("Open", "defaultsite"); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'priority',array('label'=>\GO::t("Priority", "defaultsite"))); ?></td>
				<td><?php echo $form->checkBox($ticket, 'priority'); ?></td>
			</tr>
			
			<!--			
			Example on how to add a custom field
			<tr>
				<td><?php // echo $form->label($ticket->customfieldsRecord, 'col_58'); ?></td>
				<td><?php // echo $form->textField($ticket->customfieldsRecord, 'col_58'); ?></td>
			</tr>
			-->
			
		</table>
		
		
		<?php if(!$ticket->isClosed()): ?>
		
			<?php $uploader = new \GO\Site\Widget\Plupload\Widget(); ?>
		
			<h3><?php echo \GO::t("Your message", "defaultsite"); ?></h3>

			<?php echo $form->textArea($message,'content',array('required'=>true)); ?>
			<?php echo $uploader->render(); ?>
			<div class="button-bar">
				<?php echo $form->submitButton(\GO::t("Add Comment", "defaultsite"),array('id'=>'submit-ticket-button', 'class'=>'button')); ?>
			</div>
		<?php endif; ?>

		<?php echo $form->endForm(); ?>
	</div>
</div>
