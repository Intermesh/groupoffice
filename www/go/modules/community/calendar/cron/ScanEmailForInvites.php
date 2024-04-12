<?php

namespace go\modules\community\calendar\cron;

use GO\Email\Model\Account;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\calendar\model\Scheduler;

class ScanEmailForInvites extends CronJob {
	
	public function enableUserAndGroupSupport(){ return false; }

	public function getLabel(){
		return \GO::t('Scan e-mail for invites','calendar', 'community');
	}

	public function getDescription(){
		return \GO::t('Auto add invites and updates to calendar when enabled','calendar', 'community');
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

			$ifMethod = !$setting->autoUpdateInvitations ? 'REQUEST' : (!$setting->autoAddInvitations ? 'UPDATE' : null);
			$account = Account::model()->findSingleByAttributes(['user_id'=>$setting->userId, 'username' => $setting->email]);

			if(!$account) continue; // the user its email address is not found or not owned by the user

			if(empty($setting->lastProcessed)) {
				$setting->lastProcessed = date('d-M-Y');
				go()->getDbConnection()->update('calendar_preferences',['lastProcessed' => $setting->lastProcessed], ['userId'=>$setting->userId]);
			}

			$messages = \GO\Email\Model\ImapMessage::model()
				->find($account,'INBOX',0,50,'ARRIVAL',false,'SINCE "' . $setting->lastProcessed . '"');


			foreach($messages as $message) {
				if ($message->uid <= $setting->lastProcessedUid) {
					continue;
				}

				$itip = Scheduler::handleIMIP($message, $ifMethod);
				if(!empty($itip['event'])) {
					go()->log('invite processed: '.$itip['event']->title);
				}
				if($itip && !empty($itip['event']) &&
					(($setting->markReadAndFileAutoAdd && $itip['method'] === 'REQUEST') ||
					($setting->markReadAndFileAutoUpdate && $itip['method'] === 'UPDATE'))
				) {
					// handled! now check if needs archiving.
					$this->markReadAndArchive($account,$message);
				}
			}

			if(!empty($message)) { // if there is at least one message found
				go()->getDbConnection()->update('calendar_preferences',[
					'lastProcessed' => explode(' ', $message->internal_date, 2)[0],
					'lastProcessedUid' => $message->uid
				], ['userId'=>$setting	->userId]);
			}
		}
	}

	private function markReadAndArchive(Account $account, \GO\Email\Model\ImapMessage $message) {
		$conn = $account->openImapConnection('INBOX');
		$conn->set_message_flag([$message->uid], '\Seen'); // mark seen
		$conn->move([$message->uid], 'trash'); // move to trash
		go()->log('invite archived: '.$message->uid);
	}
}