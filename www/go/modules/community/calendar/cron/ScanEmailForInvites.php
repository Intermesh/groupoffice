<?php

namespace go\modules\community\calendar\cron;

use go\core\orm\exception\SaveException;
use GO\Email\Model\Account;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\calendar\model\Scheduler;

/**
 * docker compose exec --user www-data groupoffice ./www/cli.php core/System/runCron --module=calendar --package=community --name=ScanEmailForInvites --debug
 */
class ScanEmailForInvites extends CronJob {
	
	public function enableUserAndGroupSupport(){ return false; }

	public function getLabel(){
		return go()->t('Scan e-mail for invites','calendar', 'community');
	}

	public function getDescription(){
		return go()->t('Auto add invites and updates to calendar when enabled','calendar', 'community');
	}
	
	/**
	 * This Cron job need a few parameters to be setup
	 * account_id
	 * mailbox (optional)
	 * @param CronJobSchedule $schedule
	 */
	public function run(CronJobSchedule $schedule) {

		go()->log("Start scanning email for invites..");

		$this->findAccountsAndProcess();
		
	}

	private function findAccountsAndProcess() {

		$settings = go()->getDbConnection()
			->select('userId, autoUpdateInvitations, autoAddInvitations,lastProcessedUid,lastProcessed,markReadAndFileAutoAdd,markReadAndFileAutoUpdate, u.email')
			->from('calendar_preferences', 't')
			->join('core_user', 'u', 'u.id = t.userId')
			->where('autoUpdateInvitations', '=',1)
			->orWhere('autoAddInvitations', '=', 1)->fetchMode(\PDO::FETCH_OBJ);

		foreach($settings as $setting) {

			$ifMethod = !$setting->autoUpdateInvitations ? 'REQUEST' : (!$setting->autoAddInvitations ? 'REPLY' : null);
			$account = Account::model()->findSingleByAttributes(['user_id'=>$setting->userId, 'username' => $setting->email]);

			if(!$account) continue; // the user its email address is not found or not owned by the user

			if(empty($setting->lastProcessed)) {
				$setting->lastProcessed = date('d-M-Y');
				go()->getDbConnection()->update('calendar_preferences',['lastProcessed' => $setting->lastProcessed], ['userId'=>$setting->userId]);
			}

			//only find unseen messages since the last process date
			$messages = \GO\Email\Model\ImapMessage::model()
				->find($account,'INBOX',0,50,'ARRIVAL',false,'SINCE "' . $setting->lastProcessed . '" UNSEEN');


			foreach($messages as $message) {
				if ($message->uid <= $setting->lastProcessedUid) {
					continue;
				}

				$itip = Scheduler::handleIMIP($message, $ifMethod);

				if(!$itip) {
					// skip message without invite
					continue;
				}

				if($itip['alreadyProcessed']) {
					go()->log('ALREADY processed: '.$itip['event']->title);
				} else if(!empty($itip['event'])) {
					go()->log('invite processed: '.$itip['event']->title);
					$this->updateAlerts($itip, $setting->userId, $message->from->getAddress());
				}
				if(!empty($itip['event']) &&
					(($setting->markReadAndFileAutoAdd && $itip['method'] === 'REQUEST') ||
					($setting->markReadAndFileAutoUpdate && $itip['method'] === 'REPLY'))
				) {
					// handled! now check if needs archiving.
					$this->markReadAndArchive($account,$message);
				}
			}

			if(!empty($message)) { // if there is at least one message found
				go()->getDbConnection()->update('calendar_preferences',[
					'lastProcessed' => explode(' ', $message->internal_date, 2)[0],
					'lastProcessedUid' => $message->uid
				], ['userId'=>$setting->userId])
					->execute();
			}

			go()->debug("Finished scanning for invites");
		}
	}

	private function markReadAndArchive(Account $account, \GO\Email\Model\ImapMessage $message) {
		$conn = $account->openImapConnection('INBOX');
		$conn->set_message_flag([$message->uid], '\Seen'); // mark seen
		$conn->move([$message->uid], 'trash'); // move to trash
		go()->log('invite archived: '.$message->uid);
	}

	private function updateAlerts($itip, $userId, $from) {
		$alert = $itip['event']->createAlert(new \DateTime(), strtolower($itip['method']), $userId)
			->setData(['type' => 'assigned', 'from' => $from]);
		if (!$alert->save()) {
			throw new SaveException($alert);
		}
	}
}