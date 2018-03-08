<div class="reset-password-page page">
	<div class="wrapper">

		<?php if (\Site::notifier()->hasMessage('success')): ?>
			<h2><?php echo \GO::t("Password changed", "defaultsite"); ?></h2>
		<?php else: ?>
			<h2><?php echo \GO::t("Your password is expired", "defaultsite"); ?></h2>
		<?php endif; ?>

		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(); ?>

		<?php if (\Site::notifier()->hasMessage('success')): ?>
			<div class="notification success"><?php echo \Site::notifier()->getMessage('success') ?></div>
			<div class="button-bar">
				<a id="reset-login-button" class="button" href="<?php echo \Site::urlManager()->createUrl('/site/account/login',array('ref'=>$ref)); ?>"><?php echo \GO::t("Login", "defaultsite"); ?></a>
				<div class="clear"></div>
			</div>
		<?php else: ?>

			<?php if (\Site::notifier()->hasMessage('error')): ?>
				<div class="notification error"><?php echo \Site::notifier()->getMessage('error'); ?></div>
			<?php endif; ?>

			<table class="table-reset-password">	
				<tr>
					<td colspan="2"><?php echo \GO::t("You are required to change your password.", "defaultsite"); ?></td>
				</tr>
				<tr>
					<td><label><?php echo \GO::t("Current password", "defaultsite"); ?> *:</label></td>
					<td><input type="password" name="current_password" required="true" /></td>
				</tr>
				<tr>
					<td><?php echo $form->label($user, 'password'); ?></td>
					<td><input type="password" name="password" required="true" /></td>
				</tr>	
				<tr>
					<td><?php echo $form->label($user, 'passwordConfirm',array('required'=>true)); ?></td>
					<td><input type="password" name="confirm" required="true" /></td>
				</tr>	
			</table>
			<div class="button-bar">
				<?php echo $form->submitButton(\GO::t("Submit", "defaultsite")); ?><?php echo $form->resetButton('Reset'); ?>
				<div class="clear"></div>
			</div>
				
		<?php endif; ?>

		<?php echo $form->endForm(); ?>
	</div>
</div>
