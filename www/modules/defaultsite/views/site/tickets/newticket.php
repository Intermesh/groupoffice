<?php \Site::scripts()->registerCssFile(\Site::file('css/ticket.css')); ?>

<div class="external-ticket-page newticket ticket">
	<div class="wrapper">
		
			<?php if(\GO::user()): ?>
				&lt;&lt;&nbsp;<a id="back-to-overview-button" href="<?php echo \Site::urlManager()->createUrl('tickets/externalpage/ticketlist'); ?>"><?php echo \GO::t('ticketBackToList','defaultsite'); ?></a>			
			<?php endif; ?>

		
		<h2><?php echo \GO::t('ticketNewTicket','defaultsite'); ?></h2>
		

		
		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(false,false,array('enctype'=>'multipart/form-data')); ?>

		<h3><?php echo \GO::t('ticketContactInfo','defaultsite'); ?></h3>
		
		<table class="table-contactinformation">
			<tr>
				<td><?php echo $form->label($ticket, 'first_name',array('label'=>\GO::t('ticketFirstname','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'first_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'middle_name',array('label'=>\GO::t('ticketMiddlename','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'middle_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'last_name',array('label'=>\GO::t('ticketLastname','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'last_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'email',array('label'=>\GO::t('ticketEmail','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'email'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'phone',array('label'=>\GO::t('ticketPhone','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'phone'); ?></td>
			</tr>
		</table>
		
		<h3><?php echo \GO::t('ticketInfo','defaultsite'); ?></h3>
		
		<table class="table-ticketinformation">
			<tr>
				<td><?php echo $form->label($ticket, 'subject',array('label'=>\GO::t('ticketSubject','defaultsite'))); ?></td>
				<td><?php echo $form->textField($ticket, 'subject'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'type_id',array('label'=>\GO::t('ticketType','defaultsite'))); ?></td>
				<td><?php echo $form->dropDownList($ticket, 'type_id', $form->listData($ticketTypes, 'id', 'name')); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'status',array('label'=>\GO::t('ticketStatus','defaultsite'))); ?></td>
				<td><?php echo \GO::t('ticketStatusOpen','defaultsite'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($ticket, 'priority',array('label'=>\GO::t('ticketPriority','defaultsite'))); ?></td>
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
		
			<h3><?php echo \GO::t('ticketYourMessage','defaultsite'); ?></h3>

			<?php echo $form->textArea($message,'content',array('required'=>true)); ?>
			<?php echo $uploader->render(); ?>
			<div class="button-bar">
				<?php echo $form->submitButton(\GO::t('ticketAddComment','defaultsite'),array('id'=>'submit-ticket-button', 'class'=>'button')); ?>
			</div>
		<?php endif; ?>

		<?php echo $form->endForm(); ?>
	</div>
</div>