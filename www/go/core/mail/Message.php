<?php

namespace go\core\mail;

use Exception;
use GO;
use GO\Base\Fs\File;
use go\core\fs\Blob;
use Swift_ByteStream_FileByteStream;

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
		$headers->addTextHeader("X-Mailer", "Group-Office");
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
	public function send(&$failedRecipients = null): int
	{
		return $this->mailer->send($this, $failedRecipients);
	}

	public function setSubject($subject): Message
	{
		$this->getHeaders();
		return parent::setSubject($subject);
	}

	/**
	 * Provide Blob. Blob attachment will be returned.
	 *
	 * @param Blob $blob
	 * @param null $name
	 * @return static
	 */
	public function addBlob(Blob $blob, $name = null): Message
	{
		$this->attach(Attachment::fromBlob($blob)->setFilename($name ?? $blob->name));
		return $this;
	}

	/**
	 * @return Mailer
	 */
	public function getMailer(): Mailer
	{
		return $this->mailer;
	}


	public function toTmpFile($path = null): File
	{
		if(!isset($path)) {
			$path = 'email/' . date('mY') . '/sent_' . go()->getAuthState()->getUserId() . '-' . uniqid(time()) . '.eml';
		}

		$file = new File(GO::config()->file_storage_path . $path);
		$file->parent()->create();

		$fbs = new Swift_ByteStream_FileByteStream($file->path(), true);
		$this->toByteStream($fbs);

		if (!$file->exists()) {
			throw new Exception("Failed to save email to file!");
		}

		return $file;
	}

}
