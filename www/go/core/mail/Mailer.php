<?php

namespace go\core\mail;

use go\core\App;
use go\core\model\SmtpAccount;

/**
 * Sends mail messages
 * 
 * @example
 * ````
 * $message = go()->getMailer()->compose();
 * $message->setTo()->setFrom()->setBody()->send();
 * ```
 */
class Mailer {

	/**
	 * @var \Swift_Mailer
	 */
	private $swift;

	/**
	 * @var SmtpAccount
	 */
	private $smtpAccount;
	/**
	 * Create a new mail message
	 * @return \go\core\mail\Message
	 */
	public function compose() {
		return new Message($this);
	}

	/**
	 * Provide SMTP account. If omited the system notification settings will be used.
	 * 
	 * @param SmtpAccount $account
	 * @return $this
	 */
	public function setSmtpAccount(SmtpAccount $account) {
		$this->smtpAccount = $account;

		return $this;
	}

	public function send($message) {
		
		if(!empty(go()->getSettings()->debugEmail)){
			$message->setTo(go()->getSettings()->debugEmail);
			$message->setBcc(array());
			$message->setCc(array());
			go()->warn("E-mail debugging is enabled in the Group-Office configuration. All emails are send to: ".go()->getSettings()->debugEmail);
		}
		
		return $this->swift()->send($message);
	}
	
	private function swift() {
		if (!is_object($this->swift)) {
			 $this->swift = new \Swift_Mailer($this->getTransport());
		}
		return $this->swift;
  }
  
	private function getTransport() {

		if(isset($this->smtpAccount)) {
			$o = new \Swift_SmtpTransport(
				$this->smtpAccount->hostname, 
				$this->smtpAccount->port, 
				$this->smtpAccount->encryption
			);
			if(!empty($this->smtpAccount->username)){
				$o->setUsername($this->smtpAccount->username)
					->setPassword($this->smtpAccount->decryptPassword());
			}		
			
			if(!$this->smtpAccount->verifyCertificate) {
				$o->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
			}
			
			return $o;
		}

		$o = new \Swift_SmtpTransport(
			go()->getSettings()->smtpHost, 
			go()->getSettings()->smtpPort, 
			go()->getSettings()->smtpEncryption
		);
		if(!empty(go()->getSettings()->smtpUsername)){
			$o->setUsername(go()->getSettings()->smtpUsername)
				->setPassword(go()->getSettings()->decryptSmtpPassword());
		}		
		
		if(!go()->getSettings()->smtpEncryptionVerifyCertificate) {
			$o->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
		}
		
		return $o;
	}
}
