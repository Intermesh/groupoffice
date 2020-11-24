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

	/**
	 * @var Mailer
	 */
	private $mailer;

	public function __construct(Mailer $mailer) {
		$this->mailer = $mailer;
		parent::__construct();
		$this->setFrom(go()->getSettings()->systemEmail,go()->getSettings()->title);

    $headers = $this->getHeaders();
    $headers->addTextHeader("X-Group-Office-Title", go()->getSettings()->title);
	}

	/**
	 * Send this Message like it would be sent in a mail client.
	 *
	 * All recipients (with the exception of Bcc) will be able to see the other
	 * recipients this message was sent to.

	 * The return value is the number of recipients who were accepted for
	 * delivery.
	 *
	 * @param array $failedRecipients An array of failures by-reference
	 *
	 * @return int The number of successful recipients. Can be 0 which indicates failure
	 */
	public function send(&$failedRecipients = null) {
		return $this->mailer->send($this, $failedRecipients);
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

	/**
	 * @return Mailer
	 */
	public function getMailer() {
		return $this->mailer;
	}

}
