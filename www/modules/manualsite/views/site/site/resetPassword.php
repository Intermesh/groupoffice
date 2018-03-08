<div class="reset-password-page page">
	<div class="wrapper">

		<?php if (Site::notifier()->hasMessage('success')): ?>
			<h2><?php echo \GO::t("Password changed", "defaultsite"); ?></h2>
		<?php else: ?>
			<h2><?php echo \GO::t("Change password", "defaultsite"); ?></h2>
		<?php endif; ?>

		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(); ?>

		<?php if (Site::notifier()->hasMessage('success')): ?>
			<div class="notification success"><?php echo Site::notifier()->getMessage('success') ?></div>
			<div class="button-bar">
				<a id="reset-login-button" class="button" href="<?php echo Site::urlManager()->createUrl('/site/account/login'); ?>"><?php echo \GO::t("Login", "defaultsite"); ?></a>
				<div class="clear"></div>
			</div>
		<?php else: ?>

			<?php if (Site::notifier()->hasMessage('error')): ?>
				<div class="notification error"><?php echo Site::notifier()->getMessage('error'); ?></div>
			<?php endif; ?>

			<table class="table-reset-password">	
				<tr>
					<td colspan="2"><?php echo \GO::t("Use the below form to change your password", "defaultsite"); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($user, 'password'); ?></td>
					<td><?php echo $form->passwordField($user, 'password'); ?><?php echo $form->error($user, 'password'); ?></td>
				</tr>
				<tr>
					<td><?php echo $form->label($user, 'passwordConfirm'); ?></td>
					<td><?php echo $form->passwordField($user, 'passwordConfirm'); ?><?php echo $form->error($user, 'passwordConfirm'); ?></td>
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
