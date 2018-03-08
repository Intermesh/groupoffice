<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: invitation.php 7752 2011-07-26 13:48:43Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
//require_once(\GO::config()->root_path."Group-Office.php");

extract($data);


$this->render('externalHeader');
?>
<p><?php echo \GO::t('r_u_sure','addressbook'); ?></p>
<form method="POST">
	<input type="hidden" name="contact_id" value="<?php echo $contact_id; ?>" />
	<input type="hidden" name="token" value="<?php echo $token; ?>" />
	<input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
	<input type="hidden" name="sure" value="1" />
	<input type="submit" value="<?php echo \GO::t('cmdOk'); ?>" />
</form>
<?php
$this->render('externalFooter');

