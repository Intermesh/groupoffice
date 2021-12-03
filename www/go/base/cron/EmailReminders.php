<?php

namespace GO\Base\Cron;


use go\core\ErrorHandler;
use go\core\model\Alert;
use go\core\model\User;
use go\core\orm\exception\SaveException;
use go\core\util\DateTime;

class EmailReminders extends AbstractCron {

	/**
	 * Return true or false to enable the selection for users and groups for 
	 * this cronjob.
	 * 
	 * CAUTION: This will give the run() function a different behaviour. 
	 *					Please see the documentation of the run() function 
	 *					to see what is different.
	 */
	public function enableUserAndGroupSupport(){
		return false;
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getLabel(){
		return \GO::t("Email reminders", "email");
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return String
	 */
	public function getDescription(){
		return \GO::t("This cron handles the email reminders", "email");
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
	public function run(CronJob $cronJob){
		
		\GO::session()->runAsRoot();
		$usersStmt = \GO\Base\Model\User::model()->findByAttribute('mail_reminders', 1);
		while ($userModel = $usersStmt->fetch()) {
			
			\GO::debug("Sending mail reminders to ".$userModel->username);
			
			$remindersStmt = \GO\Base\Model\Reminder::model()->find(
				\GO\Base\Db\FindParams::newInstance()
					->joinModel(array(
						'model' => 'GO\Base\Model\ReminderUser',
						'localTableAlias' => 't',
						'localField' => 'id',
						'foreignField' => 'reminder_id',
						'tableAlias' => 'ru'								
					))
					->criteria(
						\GO\Base\Db\FindCriteria::newInstance()
							->addCondition('user_id', $userModel->id, '=', 'ru')
							->addCondition('time', time(), '<', 'ru')
							->addCondition('mail_sent', '0', '=', 'ru')
					)
			);

			while ($reminderModel = $remindersStmt->fetch()) {
//					$relatedModel = $reminderModel->getRelatedModel();

//					var_dump($relatedModel->name);

//					$modelName = $relatedModel ? $relatedModel->localizedName : \GO::t("Unknown");
				$subject = \GO::t("Reminder").': '.$reminderModel->name;

				$time = !empty($reminderModel->vtime) ? $reminderModel->vtime : $reminderModel->time;

				date_default_timezone_set($userModel->timezone);

				$body = \GO::t("Time").': '.date($userModel->completeDateFormat.' '.$userModel->time_format,$time)."\n";
				$body .= \GO::t("Name").': '.str_replace('<br />',',',$reminderModel->name)."\n";

//					date_default_timezone_set(\GO::user()->timezone);

				$message = \GO\Base\Mail\Message::newInstance($subject, $body);
				$message->addFrom(\GO::config()->noreply_email,\GO::config()->title);
				$message->addTo($userModel->email,$userModel->name);
				\GO\Base\Mail\Mailer::newGoInstance()->send($message, $failedRecipients);
				
				if(!empty($failedRecipients))
					\go\core\ErrorHandler::log ("Reminder mail failed for recipient: ".implode(',', $failedRecipients));

				$reminderUserModelSend = \GO\Base\Model\ReminderUser::model()
					->findSingleByAttributes(array(
						'user_id' => $userModel->id,
						'reminder_id' => $reminderModel->id
					));
				$reminderUserModelSend->mail_sent = 1;
				$reminderUserModelSend->save();
			}

			date_default_timezone_set(\GO::user()->timezone);

			$alerts = Alert::find()
				->where('triggerAt', '<=', new DateTime("now", new \DateTimeZone("UTC")))
				->where('sendMail', '=', true);

			foreach($alerts as $alert) {
				try {
					$subject = \go()->t("Alert") . ': ' . $alert->getTitle();

					$user = User::findById($alert->userId, ['id', 'displayName', 'email', 'timezone', 'dateFormat', 'timeFormat']);

					go()->getLanguage()->setLanguage($user->language);

					//$body = go()->t("Time") . ': ' . $alert->triggerAt->toUserFormat(true, $user) . "\n\n";
					$body = $alert->getBody() ."\n\n";
					$body .= $alert->findEntity()->getURL();

//					date_default_timezone_set(\GO::user()->timezone);

					go()
						->getMailer()
						->compose()
						->setSubject($subject)
						->setBody($body)
						->setTo($user->email, $user->displayName)
						->send();

					$alert->sendMail = false;
					if(!$alert->save()) {
						throw new SaveException($alert);
					}

				} catch(\Throwable $e) {

					ErrorHandler::logException($e, "Failed sending alert e-mail ". $alert->id);
				}
			}

			go()->getLanguage()->setLanguage(go()->getAuthState()->getUser(['language'])->language);
		}
	}
	
}
