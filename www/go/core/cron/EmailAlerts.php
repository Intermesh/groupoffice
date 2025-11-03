<?php
namespace go\core\cron;

use go\core\model\Alert;
use go\core\model\CronJobSchedule;
use go\core\model\CronJob;


class EmailAlerts extends CronJob {


	public function getLabel(){
		return go()->t('Send alerts as an email');
	}

	public function getDescription(){
		return go()->t('User defined alert for calendar events and tasks are send by e-mail to the users\' mailbox');
	}

	public function run(CronJobSchedule $schedule) {

		$alerts = Alert::find()->filter(['toBeEmailed'=>true])->where(['tag'=>'1']);
		go()->debug('sending email alerts');
		foreach($alerts as $alert) {
			try {
				$alert->sendMail();
				go()->debug('Message send');
			} catch(\Exception $e) {
				go()->debug('Not send');
			}
		}

	}
}

