<?php

namespace go\core\mail;

use go\core\model\SmtpAccount;
use GO\Email\Model\Account;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

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
	 * @var PHPMailer
	 */
	private $mail;

	/**
	 * @var SmtpAccount
	 */
	private $smtpAccount;

	/**
	 * @var Account
	 */
	private $emailAccount;

	/**
	 * Create a new mail message
	 * @return Message
	 */
	public function compose(): Message
	{
		$message = new Message($this);

		if($this->emailAccount) {
			$alias = $this->emailAccount->getDefaultAlias();
			$message->setFrom($alias->email, $alias->name);
		}
		return $message;
	}

	/**
	 * Provide SMTP account. If omited the system notification settings will be used.
	 * 
	 * @param SmtpAccount $account
	 * @return $this
	 */
	public function setSmtpAccount(SmtpAccount $account) {
		$this->smtpAccount = $account;

		$this->emailAccount = null;
		return $this;
	}

	/**
	 * Set an e-mail account for sending
	 *
	 * @param Account $account
	 * @return $this
	 */
	public function setEmailAccount(Account $account) {
		$this->emailAccount = $account;
		$this->smtpAccount = null;


		return $this;
	}

	private function prepareMessage(Message $message) {
		if(!empty(go()->getConfig()['debugEmail'])){
			$message->setTo(go()->getConfig()['debugEmail']);
			$message->setBcc(array());
			$message->setCc(array());
			go()->warn("E-mail debugging is enabled in the Group-Office configuration. All emails are send to: ".go()->getConfig()['debugEmail']);
		}

		$this->initTransport();

		$this->applyMessage($message);
	}

	/**
	 * Send the given Message like it would be sent in a mail client.
	 *
	 * All recipients (with the exception of Bcc) will be able to see the other
	 * recipients this message was sent to.
	 *
	 * Recipient/sender data will be retrieved from the Message object.
	 *
	 *
	 */
	public function send(Message $message): bool
	{
		$this->prepareMessage($message);

		return !!$this->mail->send();
	}


	public function toStream(Message $message) {
		$this->prepareMessage($message);

		$this->mail->preSend();
		$mime =  $this->mail->getSentMIMEMessage();

		$stream = fopen('php://memory','r+');
		fwrite($stream, $mime);
		rewind($stream);

		return $stream;

	}

	public function lastError(): string
	{
		return $this->mail->ErrorInfo;
	}

	private function initTransport() {

		$this->mail = new PHPMailer();
		$this->mail->isSMTP();
		$this->mail->SMTPAutoTLS = false;
		$this->mail->XMailer = 'Group-Office';

		if(isset($this->smtpAccount)) {

			$this->mail->Host = $this->smtpAccount->hostname;                     //Set the SMTP server to send through
			$this->mail->SMTPSecure = $this->smtpAccount->encryption;
			$this->mail->Port = $this->smtpAccount->port;

			if (!empty(go()->getSettings()->smtpUsername)) {
				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = go()->getSettings()->smtpUsername;                     //SMTP username
				$this->mail->Password = go()->getSettings()->decryptSmtpPassword();                               //SMTP password
			}
			
			if(!$this->smtpAccount->verifyCertificate) {
				$this->disableSSLVerification();
			}
		} else if (isset($this->emailAccount)) {
			$this->mail->SMTPSecure = $this->emailAccount->smtp_encryption;
			$this->mail->Host = $this->emailAccount->smtp_host;
			$this->mail->Port = $this->emailAccount->smtp_port;

			$cltAcct = null;
			if (go()->getModule('community', 'oauth2client')) {
				$cltAcct = go()->getDbConnection()->select('token')
					->from('oauth2client_account')
					->where(['accountId' => $this->emailAccount->id])
					->single();
			}
			if ($cltAcct) {
				$this->mail->AuthType = 'XOAUTH2';

				throw new \LogicException("XOAUTH2 TODO!");


			} else if (!empty($account->smtp_username)){

				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = $account->smtp_username;                     //SMTP username
				$this->mail->Password = $account->decryptSmtpPassword();
			}

		} else {
			$this->mail->Host = go()->getSettings()->smtpHost;                     //Set the SMTP server to send through
			$this->mail->SMTPSecure = go()->getSettings()->smtpEncryption;
			$this->mail->Port = go()->getSettings()->smtpPort;

			if (!empty(go()->getSettings()->smtpUsername)) {
				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = go()->getSettings()->smtpUsername;                     //SMTP username
				$this->mail->Password = go()->getSettings()->decryptSmtpPassword();                               //SMTP password
			}

			if (!go()->getSettings()->smtpEncryptionVerifyCertificate) {
				$this->disableSSLVerification();
			}
		}

		$this->mail->getSMTPInstance()->setTimeout(go()->getSettings()->smtpTimeout);

	}

	private function disableSSLVerification() {
		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);
	}

	/**
	 * @throws Exception
	 */
	private function applyMessage(Message $message)
	{
		$this->mail->setFrom($message->getFrom()->getEmail(), $message->getFrom()->getName());
		foreach($message->getTo() as $a) {
			$this->mail->addAddress($a->getEmail(), $a->getEmail());
		}
		foreach($message->getBcc() as $a) {
			$this->mail->addBCC($a->getEmail(), $a->getEmail());
		}
		foreach($message->getCc() as $a) {
			$this->mail->addCC($a->getEmail(), $a->getEmail());
		}

		foreach($message->getReplyTo() as $a) {
			$this->mail->addReplyTo($a->getEmail(), $a->getEmail());
		}

		$this->mail->Subject = $message->getSubject();

		$this->mail->MessageDate = $message->getDate();

		if($message->getId())
			$this->mail->MessageID = $message->getId();

		$this->mail->Priority = $message->getPriority();
		foreach($message->getReferences() as $ref) {
			// TODO test
			$this->mail->addCustomHeader("References", $ref);
		}

		foreach($message->getAttachments() as $attachment) {
			if($attachment->getInline()) {
				$this->mail->addStringEmbeddedImage(
					stream_get_contents($attachment->getStream()),
					$attachment->getId(),
					$attachment->getFilename(),
					PHPMailer::ENCODING_BASE64,
					$attachment->getContentType()
				);
			} else {
				$this->mail->addStringAttachment(
					stream_get_contents($attachment->getStream()),
					$attachment->getFilename(),
					PHPMailer::ENCODING_BASE64,
					$attachment->getContentType()
				);
			}
		}

		if($message->getSender())
			$this->mail->Sender = (string) $message->getSender();

		$this->mail->Body = $message->getBody();
		$this->mail->ContentType = $message->getContentType();

		if($message->getAlternateBody()) {
			$this->mail->AltBody = $message->getAlternateBody();
		}
	}
}
