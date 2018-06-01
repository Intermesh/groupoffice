<div class="login-page page">
	<div class="wrapper">
		<h2><?php echo \GO::t("Login", "defaultsite"); ?></h2>								
		<?php $form = new \GO\Site\Widget\Form(); ?>
		<?php echo $form->beginForm(); ?>
		
		<?php if (Site::notifier()->hasMessage('error')): ?>
			<div class="notification error"><?php echo Site::notifier()->getMessage('error'); ?></div>
		<?php endif; ?>
			
		<table class="table-login">
			<tr>
				<td><?php echo $form->label($model, 'username'); ?></td>
				<td><?php echo $form->textField($model, 'username', array('autofocus' => "autofocus")); ?><?php echo $form->error($model, 'username'); ?></td>
			</tr>
			<tr>
				<td><?php echo $form->label($model, 'password'); ?></td>
				<td><?php echo $form->passwordField($model, 'password'); ?><?php echo $form->error($model, 'password'); ?></td>
			</tr>
			
			<?php if(Site::notifier()->hasMessage('error')): ?>
				<tr>
					<td><?php echo Site::notifier()->getMessage('error'); ?></td>
				</tr>
			<?php endif; ?>
				
			<tr>
				<td><label><?php echo \GO::t("Remember me", "defaultsite"); ?></label></td>
				<td><input type="checkbox" name="rememberMe" value="rememberMe"></tr>
			</tr>
		</table>
			
		<div class="button-bar">
			<input class="button" type="submit" id="submit-login-button" value="Login">
			<a id="recover-login-button" href="<?php echo Site::urlManager()->createUrl('/site/account/recoverpassword'); ?> " class="button"><?php echo \GO::t("Lost password?", "defaultsite"); ?></a>
			<div class="clear"></div>
		</div>
		
		<?php echo $form->endForm(); ?>
	</div>
</div>
