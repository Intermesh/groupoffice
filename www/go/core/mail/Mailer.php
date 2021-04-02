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

	/**
	 * Send the given Message like it would be sent in a mail client.
	 *
	 * All recipients (with the exception of Bcc) will be able to see the other
	 * recipients this message was sent to.
	 *
	 * Recipient/sender data will be retrieved from the Message object.
	 *
	 * The return value is the number of recipients who were accepted for
	 * delivery.
	 *
	 * @param array $failedRecipients An array of failures by-reference
	 *
	 * @return int The number of successful recipients. Can be 0 which indicates failure
	 */
	public function send($message, &$failedRecipients = null) {
		
		if(!empty(go()->getConfig()['debugEmail'])){
			$message->setTo(go()->getConfig()['debugEmail']);
			$message->setBcc(array());
			$message->setCc(array());
			go()->warn("E-mail debugging is enabled in the Group-Office configuration. All emails are send to: ".go()->getConfig()['debugEmail']);
		}
		
		return $this->swift()->send($message, $failedRecipients);
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
