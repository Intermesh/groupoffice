<div class="recover-password-page page">
	<div class="wrapper">
		<h2><?php echo \GO::t("Forgot password?", "site"); ?></h2>					

		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(); ?>

		<?php if (\Site::notifier()->hasMessage('success')): ?>
			<div class="notification success"><?php echo \Site::notifier()->getMessage('success') ?></div>
		<?php else: ?>

			<?php if (\Site::notifier()->hasMessage('error')): ?>
				<div class="notification error"><?php echo \Site::notifier()->getMessage('error'); ?></div>
			<?php endif; ?>

			<table class="table-login">
				<tr>
					<td colspan="2"><?php echo \GO::t("Fill in your email address in the form below, and you'll recieve a link to recover your password within minutes.", "site"); ?></td>
				</tr>
				<tr>
					<td><label><?php echo \GO::t("Email", "defaultsite"); ?></label></td>
					<td><input type="text" name="email" /></td>
				</tr>	
			</table>

			<div class="button-bar">
				<?php echo $form->submitButton(\GO::t("Submit", "defaultsite"), array('id' => 'submit-forgotpassword-button', 'class' => 'button')); ?>
				<div class="clear"></div>
			</div>

		<?php endif; ?>

		<?php echo $form->endForm(); ?>
	</div>
</div>
