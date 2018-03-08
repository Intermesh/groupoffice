<?php


namespace GO\Dropbox\Cron;

use Exception;
use GO;
use GO\Base\Cron\AbstractCron;
use GO\Base\Cron\CronJob;
use GO\Base\Mail\Mailer;
use GO\Base\Mail\Message;
use GO\Base\Util\StringHelper;
use GO\Dropbox\Model\DropboxClient;
use GO\Dropbox\Model\DropboxUser;

class Sync extends AbstractCron {
	
	/**
	 * Return true or false to enable the selection fo users and groups for 
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
	 * @return StringHelper
	 */
	public function getLabel(){
		return "Dropbox sync";
	}
	
	/**
	 * Get the unique name of the Cronjob
	 * 
	 * @return StringHelper
	 */
	public function getDescription(){
		return "";
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
	
		$stmt = DropboxUser::model()->find(\GO\Base\Db\FindParams::newInstance()->select());

		foreach($stmt as $dropboxUser){
			
			try {
				DropboxClient::setDropboxUser($dropboxUser);
				DropboxClient::syncDropboxToGO();
				DropboxClient::syncGOToDropbox();
			} catch(Exception $e){
				\GO::debug("Dropbox: Sync failed for user ".$dropboxUser->user->username.": ".$e->getMessage());				
				$this->_sendMessage($dropboxUser, $e);
			}
			
		}
	}
	
	
	private function _sendMessage($dropboxUser, $e){
		
		\GO::debug("Dropbox: Sending failure e-mail to ".$dropboxUser->user->email);
		
		$message = Message::newInstance();
		$message->setSubject(GO::t('syncFailed','dropbox'));				

		$body="Error: ".$e->getMessage();

		$message->setBody($body,'text/plain');
		$message->setFrom(GO::config()->webmaster_email,GO::config()->title);
		$message->addTo($dropboxUser->user->email, $dropboxUser->user->name);

		Mailer::newGoInstance()->send($message);
		
	}
}