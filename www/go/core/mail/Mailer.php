<?php

namespace go\core\mail;

use go\core\model\SmtpAccount;
use GO\Email\Model\Account;
use go\modules\community\oauth2client\model\Oauth2Client;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;

/**
 * Sends mail messages
 * 
 * @example
 * ````
 * $message = go()->getMailer()->compose();
 * $message->setTo()->setFrom()->setBody()->send();
 * ```
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Intermesh BV
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
		$message = new Message();
		$message->setMailer($this);

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

	public function hasAccount() : bool {
		return isset($this->account);
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

		if($message->isSmimeSinged()) {
			$this->mail->smimeSign(
				$message->getSmimeCertificate(),
				$message->getSmimePrivateKey(),
				$message->getSmimePassword(),
				$message->getSmimeExtraCertsFile()
			);
		}

		if($message->isSmimeEncrypted()) {
			$this->mail->smimeEncrypt($message->getSmimeEncryptRecipientCertificates());
		}
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


	/**
	 * Output message to a readable stream
	 *
	 * @param Message $message
	 * @return false|resource
	 * @throws Exception
	 */
	public function toStream(Message $message) {
		$this->prepareMessage($message);

		$this->mail->preSend();
		$mime =  $this->mail->getSentMIMEMessage();

		$stream = fopen('php://memory','r+');
		fwrite($stream, $mime);
		rewind($stream);

		return $stream;
	}

	/**
	 * Output message to string
	 *
	 * @param Message $message
	 * @return string
	 * @throws Exception
	 */
	public function toString(Message $message) :string {
		$this->prepareMessage($message);

		$this->mail->preSend();
		return $this->mail->getSentMIMEMessage();
	}

	public function lastError(): string
	{
		return $this->mail->ErrorInfo;
	}

	private function initTransport() {

		$this->mail = new PHPMailer();
		$this->mail->setSMTPInstance(new PHPMailerSMTP());
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
				$cltAcct = go()->getDbConnection()->select('refreshToken,oauth2ClientId')
					->from('oauth2client_account', 'a')
					->where(['accountId' => $this->emailAccount->id])
					->single();

			}
			if ($cltAcct) {

				$this->mail->SMTPAuth = true;
				$this->mail->AuthType = 'XOAUTH2';

				$client = Oauth2Client::findById($cltAcct['oauth2ClientId']);

				$provider = $client->getProvider();

				$this->mail->setOAuth(new OAuth([
					'provider' => $provider,
					'clientId' => $client->clientId,
					'clientSecret' => $client->clientSecret,
					'refreshToken' => $cltAcct['refreshToken'],
					'userName' => $this->emailAccount->username,
				]));

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
			$this->mail->addAddress($a->getEmail(), $a->getName());
		}
		foreach($message->getBcc() as $a) {
			$this->mail->addBCC($a->getEmail(), $a->getName());
		}
		foreach($message->getCc() as $a) {
			$this->mail->addCC($a->getEmail(), $a->getName());
		}

		foreach($message->getReplyTo() as $a) {
			$this->mail->addReplyTo($a->getEmail(), $a->getName());
		}

		$this->mail->Subject = $message->getSubject();

		$this->mail->MessageDate = $message->getDate()->format("D, j M Y H:i:s O");


		if($message->getId())
			$this->mail->MessageID = '<' . $message->getId() . '>';

		$this->mail->Priority = $message->getPriority();

		$refs = $message->getReferences();
		if(count($refs)) {
			$refStr = "<" . implode("> <", $refs) . ">";
			$this->mail->addCustomHeader("References", $refStr);
		}

		if($message->getInReplyTo()) {
			$this->mail->addCustomHeader('In-Reply-To', "<" . $message->getInReplyTo() . ">");
		}

		foreach($message->getAttachments() as $attachment) {

			// TODO, it seems PHPmailer is not so memory efficient. We can just as well add
			// attachments as string because the lib also reads the full file into a string before encoding.
			// Symfony mailer does this more memory efficient by using a stream filter. We might need to switch.

			if($attachment->getInline()) {
				$this->mail->addStringEmbeddedImage(
					$attachment->getString(),
					$attachment->getId(),
					$attachment->getFilename(),
					PHPMailer::ENCODING_BASE64,
					$attachment->getContentType()
				);
			} else {
				$this->mail->addStringAttachment(
					$attachment->getString(),
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
		$this->mail->CharSet = PHPMailer::CHARSET_UTF8;

		if($message->getAlternateBody()) {
			$this->mail->AltBody = $message->getAlternateBody();
		}
	}
}
