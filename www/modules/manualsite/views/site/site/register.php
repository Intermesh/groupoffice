<div class="register-page page">
	<div class="wrapper">
		<h2><?php echo \GO::t("Register", "defaultsite"); ?></h2>								
			
		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(false,false,array('id'=>'register')); ?>
		
		<?php echo \GO::t("Fill out this form and click on 'Ok' to register. The fields marked with a * are required.", "defaultsite"); ?>
		
		<h3><?php echo \GO::t("Contact Details", "defaultsite"); ?></h3>
		<table class="table-registration-contact">
			<tr>
				<td><?php echo $form->label($user, 'first_name'); ?></td>
				<td><?php echo $form->textField($user, 'first_name'); ?><?php echo $form->error($user, 'first_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($user, 'middle_name'); ?></td>
				<td><?php echo $form->textField($user, 'middle_name'); ?><?php echo $form->error($user, 'middle_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($user, 'last_name'); ?></td>
				<td><?php echo $form->textField($user, 'last_name'); ?><?php echo $form->error($user, 'last_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'sex'); ?></td>
				<td><?php echo $form->radioButtonList($contact,'sex',array('M'=>'Male','F'=>'Female'),array('template'=>'{input} {label}')); ?><?php echo $form->error($contact, 'sex'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($user, 'email'); ?></td>
				<td><?php echo $form->emailField($user, 'email'); ?><?php echo $form->error($user, 'email'); ?></li></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'cellular'); ?></td>
				<td><?php echo $form->textField($contact, 'cellular'); ?><?php echo $form->error($contact, 'cellular'); ?></li></td>
		</tr>
			</table>


			<h3><?php echo \GO::t("Company Details", "defaultsite"); ?></h3>
			<table class="table-registration-company">
				<tr>
					<td><?php echo $form->label($company, 'name'); ?></td>
					<td><?php echo $form->textField($company, 'name'); ?><?php echo $form->error($company, 'name'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'vat_no'); ?></td>
					<td><?php echo $form->textField($company, 'vat_no'); ?><?php echo $form->error($company, 'vat_no'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($contact, 'department'); ?></td>
					<td><?php echo $form->textField($contact, 'department'); ?><?php echo $form->error($contact, 'department'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($contact, 'function'); ?></td>
					<td><?php echo $form->textField($contact, 'function'); ?><?php echo $form->error($contact, 'function'); ?></td>																
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'phone'); ?></td>
					<td><?php echo $form->textField($company, 'phone'); ?><?php echo $form->error($company, 'phone'); ?></td>
				</tr>
			</table>


			<h3><?php echo \GO::t("Address", "defaultsite"); ?></h3>
			<table class="table-registration-address">
				<tr>
					<td><?php echo $form->label($company, 'address'); ?></td>
					<td><?php echo $form->textField($company, 'address'); ?><?php echo $form->error($company, 'address'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'address_no'); ?></td>
					<td><?php echo $form->textField($company, 'address_no'); ?><?php echo $form->error($company, 'address_no'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'zip'); ?></td>
					<td><?php echo $form->textField($company, 'zip'); ?><?php echo $form->error($company, 'zip'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'city'); ?></td>
					<td><?php echo $form->textField($company, 'city'); ?><?php echo $form->error($company, 'city'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'state'); ?></td>
					<td><?php echo $form->textField($company, 'state'); ?><?php echo $form->error($company, 'state'); ?></td>	
				</tr>
				<tr>
					<td><?php echo $form->label($company, 'country'); ?>
					<td><?php echo $form->dropDownList($company, 'country', \GO::language()->getCountries()); ?><?php echo $form->error($company, 'country'); ?></td>
				</tr>
<!--				<tr>
					<td><?php echo $form->label($company, 'postAddressIsEqual'); ?></td>
					<td><?php echo $form->checkBox($company, 'postAddressIsEqual'); ?></td>
			</tr>-->
		</table>


			<h3><?php echo \GO::t("Username and Password", "defaultsite"); ?></h3>
			<table class="table-registration-user">
				<tr>
					<td><?php echo $form->label($user, 'username'); ?></td>
					<td><?php echo $form->textField($user, 'username'); ?><?php echo $form->error($user, 'username'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($user, 'password'); ?></td>
					<td><?php echo $form->passwordField($user, 'password'); ?><?php echo $form->error($user, 'password'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($user, 'passwordConfirm'); ?></td>
					<td><?php echo $form->passwordField($user, 'passwordConfirm', array('autocomplete'=>'off')); ?><?php echo $form->error($user, 'passwordConfirm'); ?></td>
			</tr>
		</table>
		
		<div class="button-bar">
			<?php echo $form->submitbutton(\GO::t("Register", "defaultsite"),array('class'=>'button','id'=>'register-submit-button')); ?>
			<div class="clear"></div>
		</div>
		
		<?php echo $form->endForm(); ?>
	</div>
</div>
