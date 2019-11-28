<?php

namespace go\core\mail;

use go\core\fs\Blob;

/**
 * A mail message to send
 * 
 * The From header defaults to the system settings e-mail and title.
 * 
 * @example
 * ````
 * $message = go()->getMailer()->compose();
 * $message->setTo()->setFrom()->setBody()->send();
 * ```
 */
class Message extends \Swift_Message {

	private $mailer;

	public function __construct(Mailer $mailer) {
		$this->mailer = $mailer;
		parent::__construct();
		$this->setFrom(go()->getSettings()->systemEmail,go()->getSettings()->title);
	}

	/**
	 * 
	 * @param \go\core\mail\Mailer $mailer
	 * @return int Number of successful recipients.
	 * 
	 */
	public function send(Mailer $mailer = null) {
		return $this->mailer->send($this);
	}

	public function setSubject($subject) {
		$this->getHeaders();
		return parent::setSubject($subject);
	}

	/**
	 * Provide Blob. Blob attachment will be returned.
	 * 
	 * @param Blob $blob
	 * @return static
	 */
	public function addBlob(Blob $blob) {
		$this->attach(Attachment::fromBlob($blob)->setFilename($blob->name));
		return $this;
	}

}
