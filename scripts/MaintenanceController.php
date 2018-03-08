<?php

namespace GO\Email\Controller;


class Maintenance extends \GO\Base\Controller\AbstractController {
	
	// SETTINGS
	private static $_REMOVE_DUPLICATES=false;
	private static $_HOSTNAME='mail.hostname.com';
	private static $_DOMAIN_NAME='domain.com';
	private static $_MEM_LIMIT=1000; // PHP Memory limit in Mb
	private static $_N_MESSAGES_PER_ITERATION=100; // How many messages to read at once.
	private static $_SUBDIR_SEPARATOR='.';
	//private static $_EMAIL_ACCOUNT='john@domain.com'; // Uncomment this to only clean up this email account.
	
	// LOG FILE HANDLERS
	private static $_LOG_FILE=null;
	private static $_DOMAIN_LOG_FILE=null;
	private static $_DELETED_LOG_FILE=null;
	
	
	
	
	protected function actionRemoveDuplicateEmails($params) {
		
		\GO::setMemoryLimit(self::$_MEM_LIMIT);
		\GO::setMaxExecutionTime(0);
		
		if (!\GO::user()->isAdmin())
			throw new AccessDeniedException();

		if (!empty(self::$_EMAIL_ACCOUNT))
			$params['email'] = self::$_EMAIL_ACCOUNT;
		
		
		self::$_DELETED_LOG_FILE = new \GO\Files\Fs\UserLogFile('removeDuplicateEmails_DELETED_IDS_'.self::$_DOMAIN_NAME.'_');
		if (!empty(self::$_REMOVE_DUPLICATES) && self::$_REMOVE_DUPLICATES!=='false')
			self::$_DELETED_LOG_FILE->log('The following messages have been deleted:');
		else
			self::$_DELETED_LOG_FILE->log('Dry run. The following messages would have been deleted if this weren\'t a dry run:');
		
		
		if (!empty($params['email']) && \GO\Base\Util\StringHelper::validate_email($params['email'])) {
		
		
			$this->_handleEmailAccount($params['email']);
			
			
		} elseif (!isset($params['email'])) {

			
			self::$_DOMAIN_LOG_FILE = new \GO\Files\Fs\UserLogFile('removeDuplicateEmails_DOMAIN_'.self::$_DOMAIN_NAME.'_');
			
			$domainModel = \GO\Postfixadmin\Model\Domain::model()->findSingleByAttribute('domain',self::$_DOMAIN_NAME);
			if (empty($domainModel)) {
				$this->_domainLog('ERROR: Could not find database entry for domain "'.self::$_DOMAIN_NAME.'"');
				throw new Exception('ERROR: Could not find database entry for domain "'.self::$_DOMAIN_NAME.'"');
			}
			
			if (empty($domainModel->mailboxes) || !($domainModel->mailboxes->rowCount()>0)) {
				$this->_domainLog('ERROR: Could not find any mailbox entries for domain "'.self::$_DOMAIN_NAME.'"');
				throw new Exception('ERROR: Could not find any mailbox entries for domain "'.self::$_DOMAIN_NAME.'"');
			}
			
			foreach ($domainModel->mailboxes as $mailboxModel) {
				$this->_handleEmailAccount($mailboxModel->username);
			}
			
			
		}
		
	}
	
	
	private function _handleEmailAccount($emailAddress) {
		
		self::$_LOG_FILE = new \GO\Files\Fs\UserLogFile('removeDuplicateEmails_'.self::$_DOMAIN_NAME.'_'.$emailAddress.'_');
		
		$accountModel = $this->_findAccountByEmailAndHost($emailAddress, self::$_HOSTNAME);
		if (!$accountModel) {
			$this->_log('ERROR: Could not find account with email "'.$emailAddress.'" and host name "'.self::$_HOSTNAME.'".');
			throw new Exception('ERROR: Could not find account with email "'.$emailAddress.'" and host name "'.self::$_HOSTNAME.'".');
		}

		$this->_log("[".self::$_HOSTNAME.":".$emailAddress."] === E-mail account opened.");

		if (self::$_REMOVE_DUPLICATES)
			$this->_log("[".self::$_HOSTNAME.":".$emailAddress."] === Removing duplicate emails.");
		else
			$this->_log("[".self::$_HOSTNAME.":".$emailAddress."] === Dry run. Showing duplicates but not removing them.");

		$mailboxesArr = $this->_getAllMailboxes($accountModel);

		foreach ($mailboxesArr as $mailboxModel) {

			$parentName = $mailboxModel->getParentName();
			$mailboxFullName = !empty($parentName) ? $parentName.self::$_SUBDIR_SEPARATOR.$mailboxModel->getBaseName() : $mailboxModel->getBaseName();

			if (strtolower($mailboxFullName)!='dovecot'.self::$_SUBDIR_SEPARATOR.'sieve')
				$this->_removeDuplicates($accountModel, $mailboxFullName);

		}
		
	}
	
	
	private function _removeDuplicates(\GO\Email\Model\Account $accountModel,$mailboxName) {
		
		if (!\GO::user()->isAdmin())
			throw new AccessDeniedException();
		
		$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") Looking up messages in mailbox '".$mailboxName."'...");

		$messageNr = 0;
		
		$messages = \GO\Email\Model\ImapMessage::model()->find(
						$accountModel, 
						$mailboxName,
						0,//$params['start'], 
						99,//$params['limit'], 
						\GO\Base\Mail\Imap::SORT_DATE,//$sortField , 
						true//$params['dir']!='ASC'
						);
	
		$nDuplicates = 0;
	
		while (!empty($messages) && count($messages)>0) {

			$messageIdsArr = array();

			// ORDER MESSAGES BY MESSAGE ID
			foreach($messages as $k => $messageModel){
				if (empty($messageIdsArr[$messageModel->message_id]))
					$messageIdsArr[$messageModel->message_id] = array();
				$messageIdsArr[$messageModel->message_id][] = $messageModel->uid;
			}

			// SHOW AND REMOVE DUPLICATE MESSAGES
			foreach ($messageIdsArr as $messageId => $uidsArr) {
				if (count($uidsArr)>1) { // IF THERE ARE MORE THAN 1 VERSION WITH THE CURRENT $messageId, LOG IT.
					$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") Message ID: ".$messageId." occurs ".count($uidsArr)." times.");
					foreach ($uidsArr as $copyNr => $uid) {
						$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($accountModel, $mailboxName, $uid);
						$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") UID #".$uid.": [".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][".$imapMessage->from."][".$imapMessage->subject."]");
						$response = $imapMessage->toOutputArray(true,false,false);
	//					$this->_log(var_export($response,true));
	//					$this->_log("");

						if ($copyNr>0) {

							if ($imapMessage->udate<mktime(18,0,0,07,26,2013)) {
								if (!empty(self::$_REMOVE_DUPLICATES)) {
									$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") UID #".$uid.": [".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][".$imapMessage->from."][".$imapMessage->subject."] Deleting duplicate... ");
									if ($imapMessage->delete()) {
										$this->_log("Success.");
										self::$_DELETED_LOG_FILE->log("[".$accountModel->host.":".$accountModel->username."][".$mailboxName."][MESSAGEID:".$messageId."][UID:".$uid."][UDATEUNIX:".$imapMessage->udate."][UDATEFORMATTED:".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][FROM:".$imapMessage->from."][SUBJECT:".$imapMessage->subject."]");
									} else {
										$this->_log("Failed.");
									}
								} else {
									$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") UID #".$uid.": [".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][".$imapMessage->from."][".$imapMessage->subject."] To be deleted, but not during this dry run... ");
									self::$_DELETED_LOG_FILE->log("[".$accountModel->host.":".$accountModel->username."][".$mailboxName."][MESSAGEID:".$messageId."][UID:".$uid."][UDATEUNIX:".$imapMessage->udate."][UDATEFORMATTED:".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][FROM:".$imapMessage->from."][SUBJECT:".$imapMessage->subject."]");
								}
							} else {
								$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") UID #".$uid.": [".\GO\Base\Util\Date::get_timestamp($imapMessage->udate)."][".$imapMessage->from."][".$imapMessage->subject."] Not deleting this message, because it is from after July 26, 2013, 18:00 o'clock.");
							}

							$nDuplicates++;
						}

					}

				}

			}
		
			$messageNr+=self::$_N_MESSAGES_PER_ITERATION;
			
			$messages = \GO\Email\Model\ImapMessage::model()->find(
				$accountModel, 
				$mailboxName,
				$messageNr,//$params['start'], 
				self::$_N_MESSAGES_PER_ITERATION,//$params['limit'], 
				\GO\Base\Mail\Imap::SORT_DATE,//$sortField , 
				true//$params['dir']!='ASC'
				);
			
			
		}
		
		$this->_log("[".$accountModel->host.":".$accountModel->username."] === (".$mailboxName.") ".$nDuplicates." duplicates counted in this mailbox.");
		
	}
	
	
	/**
	 * Find an account by e-mail address and host name.
	 *
	 * @param StringHelper $email
	 * @param StringHelper $hostName
	 * @return \GO\Email\Model\Account
	 */
	private function _findAccountByEmailAndHost($email,$hostName){
		
		$joinCriteria = \GO\Base\Db\FindCriteria::newInstance()
						->addRawCondition('t.id', 'a.account_id');

		$findParams = \GO\Base\Db\FindParams::newInstance()
						->single()
						->join(\GO\Email\Model\Alias::model()->tableName(), $joinCriteria,'a')
						->criteria(
							\GO\Base\Db\FindCriteria::newInstance()
								->addCondition('email', $email,'=','a')
								->addCondition('host', $hostName,'=','t')
						);

		return \GO\Email\Model\Account::model()->find($findParams);
	}
	
	
		/**
	 *
	 * @return \GO\Email\Model\ImapMailbox 
	 */
	public function _getAllMailboxes(\GO\Email\Model\Account $accountModel){
		$imap = $accountModel->openImapConnection();
		
		$folders = $imap->list_folders(false, false,'','*',true);
		
		//$node= array('name'=>'','children'=>array());
		
		$rootMailboxes = array();
				
		foreach($folders as $folder){
			$mailbox = new \GO\Email\Model\ImapMailbox($accountModel,$folder);
			$rootMailboxes[]=$mailbox;			
		}
		
		return $rootMailboxes;
	}
	
	
	private function _log($string) {
		echo($string.'<br />');
		\GO::debug($string);
		self::$_LOG_FILE->log($string);
	}
	
	private function _domainLog($string) {
		echo($string.'<br />');
		\GO::debug($string);
		self::$_DOMAIN_LOG_FILE->log($string);
	}	
}

?>
