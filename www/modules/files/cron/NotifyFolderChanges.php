<?php


namespace GO\Files\Cron;

use GO;
use GO\Base\Cron\AbstractCron;
use GO\Base\Cron\CronJob;
use GO\Base\Db\FindParams;
use GO\Files\Model\FolderNotification;
use GO\Files\Model\FolderNotificationMessage;


class NotifyFolderChanges extends AbstractCron {

	public function enableUserAndGroupSupport() {
		return false;
	}

	public function getLabel() {
		return GO::t("Send notification email when something changes in subscribed files folder", "files");
	}

	public function getDescription() {
		return GO::t("Send an email to the user when a folder is changed", "files");
	}

	/**
	 * The code that needs to be called when the cron is running
	 * 
	 * If $this->enableUserAndGroupSupport() returns TRUE then the run function 
	 * will be called for each $user. (The $user parameter will be given)
	 * 
	 * If $this->enableUserAndGroupSupport() returns FALSE then the 
	 * $user parameter is null and the run function will be called only once.
	 * 
	 * @param CronJob $cronJob
	 */
	public function run(CronJob $cronJob) {
		
		$findParams= FindParams::newInstance()->distinct()->select('user_id');
		$notificationMessagesPerUser = FolderNotificationMessage::model()->find($findParams);
				
		foreach($notificationMessagesPerUser as $notificationMessage){
			FolderNotification::model()->notifyUser($notificationMessage->user_id);
		}
	}

}
