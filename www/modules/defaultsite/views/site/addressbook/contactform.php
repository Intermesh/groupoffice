<div class="register-page page">
	<div class="wrapper">
		<h2><?php echo \GO::t("Contact form", "defaultsite"); ?></h2>								
			
		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(false,false,array('id'=>'contact')); ?>
		
		<?php echo \GO::t("Fill out this form and click on 'Ok' to register. The fields marked with a * are required.", "defaultsite"); ?>
		
		<?php 
		

		// Find the first addressbook that's available:
		 $addressbook = \GO\Addressbook\Model\Addressbook::model()->findSingle();

		// Find addressbook by id:
		// $addressbook = \GO\Addressbook\Model\Addressbook::model()->findByPk(11);

		// Find addressbook by the name attribute:
		// $addressbook = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('name','Example');

		// Use the addressbook model to populate the hidden field:
		 echo $form->hiddenField($addressbook,'id',array('value'=>$addressbook->id)); 

		// Just set the correct addressbook id in the hidden field manually:
		// echo $form->hiddenField($addressbook,'id',array('value'=>11));

		//$addressbook = \GO\Addressbook\Model\Addressbook::model()->findSingleByAttribute('name','Example');
		
		echo $form->hiddenField($addressbook,'id',array('value'=>$addressbook->id)); 
		

		?>
		
		<h3><?php echo \GO::t("Contact Details", "defaultsite"); ?></h3>
		<table class="table-registration-contact">
			<tr>
				<td><?php echo $form->label($contact, 'first_name'); ?></td>
				<td><?php echo $form->textField($contact, 'first_name'); ?><?php echo $form->error($contact, 'first_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'middle_name'); ?></td>
				<td><?php echo $form->textField($contact, 'middle_name'); ?><?php echo $form->error($contact, 'middle_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'last_name'); ?></td>
				<td><?php echo $form->textField($contact, 'last_name'); ?><?php echo $form->error($contact, 'last_name'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'sex'); ?></td>
				<td><?php echo $form->radioButtonList($contact,'sex',array('M'=>'Male','F'=>'Female'),array('horizontal'=>true,'template'=>'{input} {label}')); ?><?php echo $form->error($contact, 'sex'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($contact, 'email'); ?></td>
				<td><?php echo $form->emailField($contact, 'email'); ?><?php echo $form->error($contact, 'email'); ?></li></td>
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
					<td><?php //echo $form->label($company, 'postAddressIsEqual'); ?></td>
					<td><?php //echo $form->checkBox($company, 'postAddressIsEqual'); ?></td>
			</tr>-->
<!--				<tr>
					<td><label for="Newsletter_1">Newsletter 1</label></td>
					<td><input type="checkbox" name="Addresslist[Test Adreslijst]" value="checked" /></td>
				</tr>
				<tr>
					<td><label for="Newsletter_2">Newsletter 2</label></td>
					<td><input type="checkbox" name="Addresslist[Test adreslijst 2]" value="checked" /></td>
				</tr>-->
		</table>

		
		<div class="button-bar">
			<?php echo $form->submitbutton(\GO::t("Register", "defaultsite"),array('class'=>'button','id'=>'register-submit-button')); ?>
			<div class="clear"></div>
		</div>
		
		<?php echo $form->endForm(); ?>
	</div>
</div>
