<?php

namespace go\core\mail;

use go\core\ErrorHandler;
use go\core\http\Request;
use go\core\model\SmtpAccount;
use go\core\model\User;
use go\core\util\StringUtil;
use GO\Email\Model\Account;
use go\modules\community\history\model\LogEntry;
use go\modules\community\oauth2client\model\Oauth2Client;
use League\OAuth2\Client\Provider\Google;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\OAuth;
use PHPMailer\PHPMailer\SMTP;

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

	private PHPMailer $mail;
	private ?SmtpAccount $smtpAccount = null;
	private ?Account $emailAccount = null;
	/**
	 * @var true
	 */
	private bool $sent = false;

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
	public function setSmtpAccount(SmtpAccount $account): static
	{
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
	public function setEmailAccount(Account $account): static
	{
		$this->emailAccount = $account;
		$this->smtpAccount = null;

		return $this;
	}

	/**
	 * Check if the mailer is configured with a user e-mail account
	 *
	 * @return bool
	 */
	public function hasAccount() : bool {
		return isset($this->emailAccount);
	}

	private function prepareMessage(Message $message): void
	{
		if(!empty(go()->getConfig()['debugEmail'])){
			$message->setTo(go()->getConfig()['debugEmail']);
			$message->setBcc('');
			$message->setCc('');
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
	 * @throws Exception
	 */
	public function send(Message $message) : void
	{

		$message->setMailer($this);
		$this->prepareMessage($message);

		$this->log($message);

		$this->mail->send();
		$this->sent = true;
	}

	/**
	 * Output message to a readable stream
	 *
	 * @param Message $message
	 * @return false|resource
	 * @throws Exception
	 */
	public function toStream(Message $message) {

		if(!$this->sent) {
			$this->prepareMessage($message);

			$this->mail->preSend();
		}
		$mime = $this->mail->getSentMIMEMessage();

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

		if(!$this->sent) {
			$this->prepareMessage($message);

			$this->mail->preSend();
		}
		return $this->mail->getSentMIMEMessage();
	}

	public function lastError(): string
	{
		return isset($this->mail) ? $this->mail->ErrorInfo : "";
	}

	private function initTransport() {

		$this->mail = new PHPMailer();
		if(go()->getConfig()['mailerDebugLevel'] > 0) {
			$this->mail->SMTPDebug = min(intval(go()->getConfig()['mailerDebugLevel']), SMTP::DEBUG_LOWLEVEL);
			$this->mail->Debugoutput = function($msg, $level)  {
				go()->debug($msg);
			};
		}
		$this->mail->setSMTPInstance(new PHPMailerSMTP());
		$this->mail->isSMTP();
		$this->mail->SMTPAutoTLS = false;
		$this->mail->XMailer = 'Group-Office';

		if(isset($this->smtpAccount)) {

			$this->mail->Host = $this->smtpAccount->hostname;                     //Set the SMTP server to send through
			$this->mail->SMTPSecure = $this->smtpAccount->encryption;
			$this->mail->Port = $this->smtpAccount->port;

			if (!empty($this->smtpAccount->username)) {
				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = $this->smtpAccount->username;                     //SMTP username
				$this->mail->Password = $this->smtpAccount->decryptPassword();                               //SMTP password
			}
			
			if(!$this->smtpAccount->verifyCertificate) {
				$this->disableSSLVerification();
			}
		} else if (isset($this->emailAccount)) {

			if($this->emailAccount->smtp_allow_self_signed) {
				$this->disableSSLVerification();
			}

			if($this->emailAccount->smtp_encryption == 'starttls') {
				$this->emailAccount->smtp_encryption = 'tls';
			}

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
					'userName' => !empty($this->emailAccount->smtp_username) ? $this->emailAccount->smtp_username : $this->emailAccount->username,
				]));

			} else if (!empty($this->emailAccount->force_smtp_login)){
				// weird name force_smtp_login but it means use the IMAP credentials
				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = $this->emailAccount->username;                     //SMTP username
				$this->mail->Password = $this->emailAccount->decryptPassword();

			}else if (!empty($this->emailAccount->smtp_username)){
				$this->mail->SMTPAuth = true;                                   //Enable SMTP authentication
				$this->mail->Username = $this->emailAccount->smtp_username;                     //SMTP username
				$this->mail->Password = $this->emailAccount->decryptSmtpPassword();
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

		//speeds up multiple sends.
		$this->mail->SMTPKeepAlive = true;

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
	private function applyMessage(Message $message): void
	{
		// important to set before using encodeHeader in disposition-notification-to below.
		$this->mail->CharSet = PHPMailer::CHARSET_UTF8;

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

		$readReceiptTo = $message->getReadReceiptTo();
		if(count($readReceiptTo)) {
			$headerValue = [];
			foreach ($readReceiptTo as $a) {
				$headerValue[] = $this->mail->addrFormat([$a->getEmail(), $a->getName()]);
			}

			$this->mail->addCustomHeader("Disposition-Notification-To", implode(", ", $headerValue));
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
					$attachment->getEncoding(),
					$attachment->getContentType()
				);
			} else {
				$this->mail->addStringAttachment(
					$attachment->getString(),
					$attachment->getFilename(),
					$attachment->getEncoding(),
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

		foreach ($message->getHeaders() as $name => $value) {
			$this->mail->addCustomHeader($name, $value);
		}
	}

	private function log(Message $message): void
	{

		$logMsg = "From: " . $message->getFrom()->getEmail().
			", To: ". implode(',', array_map(function($a) { return $a->getEmail(); }, $message->getTo())).
			", Subject: ".$message->getSubject().
			", Id: ".$message->getId();

		go()->debug("Sending e-mail: ". $logMsg);

		if(!go()->getModule('community', 'history'))
			return;

		$log = new LogEntry();

		if($this->smtpAccount) {
			$log->setEntity($this->smtpAccount);
		} else if($this->emailAccount) {
			$log->setEntity($this->emailAccount);
		} else {
			$user = go()->getAuthState()->getUser();
			if(isset($user)) {
				$log->setEntity($user);
			} else{
				$log->entityTypeId = User::entityType()->getId();
			}
		}

		$log->description = StringUtil::cutString($logMsg, 384, false, "");

		$log->setAction('email');
		$log->changes = null;
		if(!$log->save()){
			ErrorHandler::log("Failed to write e-mail log");
		}
	}
}
