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

if ($participant->status == \GO\Calendar\Model\Participant::STATUS_ACCEPTED) {
	?>
	<p><?php echo \GO::t('eventAccepted', 'calendar'); ?></p>		
	<?php
	}else
	{
	?>
	<p><?php echo \GO::t('eventDeclined', 'calendar'); ?></p>
	<?php
}

if ($event) {
	?>
	<p><?php echo sprintf(\GO::t('eventScheduledIn', 'calendar'), $event->calendar->name, $participant->statusName); ?></p>
	<?php
}
$this->render('externalFooter');

