<?php

namespace go\core\mail;

use go\core\App;

/**
 * Sends mail messages
 * 
 * @example
 * ````
 * $message = GO()->getMailer()->compose();
 * $message->setTo()->setFrom()->setBody()->send();
 * ```
 */
class Mailer {

	private $swift;
	
	/**
	 * Create a new mail message
	 * @return \go\core\mail\Message
	 */
	public function compose() {
		return new Message($this);
	}
	
	public function send($message) {
		
		if(!empty(GO()->getSettings()->debugEmail)){
			$message->setTo(GO()->getSettings()->debugEmail);
			$message->setBcc(array());
			$message->setCc(array());
			GO()->warn("E-mail debugging is enabled in the Group-Office config.php file. All emails are send to: ".GO()->getSettings()->debugEmail);
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
		$o = new \Swift_SmtpTransport(
			GO()->getSettings()->smtpHost, 
			GO()->getSettings()->smtpPort, 
			GO()->getSettings()->smtpEncryption
		);
		if(!empty(GO()->getSettings()->smtpUsername)){
			$o->setUsername(GO()->getSettings()->smtpUsername)
				->setPassword(GO()->getSettings()->smtpPassword);
		}
		
		
		if(!GO()->getSettings()->smtpEncryptionVerifyCertificate) {
			$o->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name'  => false)));
		}
		
		return $o;
	}
}
