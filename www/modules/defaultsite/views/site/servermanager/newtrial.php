
<?php $form = new \GO\Site\Widget\Form(); 
			echo $form->beginForm(); ?>

<div class="row formrow">					
	<?php echo $form->label($model, 'name'); ?>
	<?php echo $form->textField($model, 'name'); ?>
	<span ><?php echo '.'.\GO::config()->servermanager_wildcard_domain; ?></span>
	<?php echo $form->error($model, 'name'); ?>
</div>
<div class="row formrow">
	<?php echo $form->label($model, 'title'); ?>
	<?php echo $form->textField($model, 'title'); ?>
	<?php echo $form->error($model, 'title'); ?>
</div>		

<div class="row formrow">
	<?php echo $form->label($model, 'first_name'); ?>
	<?php echo $form->textField($model, 'first_name'); ?>
	<?php echo $form->error($model, 'first_name'); ?>
</div>	

<div class="row formrow">
	<?php echo $form->label($model, 'last_name'); ?>
	<?php echo $form->textField($model, 'last_name'); ?>
	<?php echo $form->error($model, 'last_name'); ?>
</div>	

<div class="row formrow">
	<?php echo $form->label($model, 'email'); ?>
	<?php echo $form->textField($model, 'email'); ?>
	<?php echo $form->error($model, 'email'); ?>
</div>	

<div class="row buttons">
	<?php echo $form->submitButton('Create trial!'); ?>
	<?php echo $form->resetButton('Reset'); ?>
</div>

<div style="clear:both"></div>
<?php echo $form->endForm(); ?>
