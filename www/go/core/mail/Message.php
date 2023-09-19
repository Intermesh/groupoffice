<?php

namespace go\core\mail;

use DateTimeInterface;
use Exception;
use GO\Base\Mail\EmailRecipients;
use GO\Base\Mail\MimeDecode;
use go\core\ErrorHandler;
use go\core\fs\Blob;
use go\core\http\Request;
use go\core\util\DateTime;

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
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Intermesh BV
 */
class Message {


	private ?Mailer $mailer;
	private ?DateTimeInterface $date = null;
	private ?int $priority = null;
	private string $body = "";
	private ?string $alternateBody = null;
	private string $subject = "";
	private array $to = [];
	private array $cc = [];
	private array $bcc = [];
	private array $attachments = [];

	private array $headers = [];
	private ?Address $from = null;

	/**
	 * @var Address[]
	 */
	private array $replyTo = [];
	private ?Address $sender = null;

	private ?string $id = null;
	/**
	 * @var array
	 */
	private array $references = [];
	private ?string $bodyContentType = null;
	private ?string $inReplyTo = null;

	private ?string $smimeCertificate = null;

	private ?string $smimePrivateKey = null;

	private ?array $smimeEncryptRecipientCertificates = null;

	private ?string $smimeExtraCertsFile = null;

	private ?string $smimePassword = null;
	private array $readReceiptTo = [];

	public function __construct() {
		$this->setFrom(go()->getSettings()->systemEmail, go()->getSettings()->title);
	}


	/**
	 * Get message ID
	 *
	 * Note: without angular brackets
	 *
	 * @return string|null
	 * @throws Exception
	 */
	public function getId() : ?string
	{
		if(!isset($this->id)) {
			try {
				$this->id = bin2hex(random_bytes(16)) . '@' . Request::get()->getHost();
			}catch(Exception $e) {

				// no random source found
				$this->id = uniqid((string) mt_rand(), true) . '@' . Request::get()->getHost();
			}
		}
		return $this->id;
	}

	/**
	 * Set message ID
	 *
	 * Note: without angular brackets
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.8
	 * @param string $id
	 * @return $this
	 */

	public function setId(string $id): Message
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Set references.
	 *
	 * Note: without angular brackets
	 *
	 * The message identifier(s) of other message(s) to which the current
	 * message may be related.  In RFC 2822, the definition was changed
	 * to say that this header field contains a list of all Message-IDs
	 * of messages in the preceding reply chain.  Defined as standard by
	 *
	 * @see https://www.rfc-editor.org/rfc/rfc4021#page-11
	 * @param string ...$references
	 * @return $this
	 */
	public function setReferences(string ...$references): Message
	{
		$this->references = $references;
		return $this;
	}

	/**
	 * Get references
	 *
	 * @see setReferences()
	 *
	 * @param string ...$references
	 * @return $this
	 */
	public function getReferences(): array
	{
		return $this->references;
	}

	/**
	 * Set the message ID this message is a reply to
	 *
	 * Note: without angular brackets
	 *
	 * @param string $messageId
	 * @return $this
	 */

	public function setInReplyTo(string $messageId): Message
	{
		$this->inReplyTo = $messageId;
		return $this;
	}

	/**
	 * Get the message ID this message is a reply to
	 *
	 * Note: without angular brackets
	 *
	 * @return string
	 */
	public function getInReplyTo() : ?string {
		return $this->inReplyTo;
	}

	/**
	 * Set a custom header
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 */

	public function setHeader(string $name, string $value) {
		$this->headers[$name] = $value;
	}

	/**
	 * Get custom headers
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	private function normalizeRecipients(array $addresses): array
	{
		foreach($addresses as &$address) {
			if(is_string($address)) {
				$address = new Address($address);
			} else if (!($address instanceof Address)) {
				throw new \InvalidArgumentException("Address must be a string or \go\core\mail\Address");
			}
		}
		return $addresses;
	}

	/**
	 * Set to addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function setTo(...$addresses): Message
	{
		$this->to = $this->normalizeRecipients($addresses);
		return $this;
	}

	/**
	 * Get to addresses
	 *
	 * @return Address[]
	 */
	public function getTo(): array
	{
		return $this->to;
	}

	/**
	 * Add "To" addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function addTo(...$addresses): Message
	{
		$this->to = array_merge($this->to, $this->normalizeRecipients($addresses));
		return $this;
	}

	/**
	 * Set Bcc addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */

	public function setBcc(...$addresses): Message
	{
		$this->bcc = $this->normalizeRecipients($addresses);
		return $this;
	}


	/**
	 * Get Bcc addresses
	 *
	 * @return Address[]
	 */
	public function getBcc(): array
	{
		return $this->bcc;
	}

	/**
	 * Add Bcc addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function addBcc(...$addresses): Message
	{
		$this->bcc = array_merge($this->bcc, $this->normalizeRecipients($addresses));
		return $this;
	}

	/**
	 * Set CC addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function setCc(...$addresses): Message
	{
		$this->cc = $this->normalizeRecipients($addresses);
		return $this;
	}

	/**
	 * Get CC addresses
	 *
	 * @return Address[]
	 */
	public function getCc(): array
	{
		return $this->cc;
	}


	/**
	 * Add CC addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function addCc(...$addresses): Message
	{
		$this->cc = array_merge($this->cc, $this->normalizeRecipients($addresses));
		return $this;
	}


	/**
	 * Set addresses to request a read notification for
	 *
	 * @param ...$addresses
	 * @return $this
	 */
	public function setReadReceiptTo (...$addresses): Message
	{
		$this->readReceiptTo = $this->normalizeRecipients($addresses);
		return $this;
	}

	/**
	 *
	 * Get addresses to request a read notification for
	 *
	 * @return Address[]
	 */
	public function getReadReceiptTo(): array
	{
		return $this->readReceiptTo;
	}


	/**
	 * Add addresses to request a read notification for
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function addReadReceiptTo(...$addresses): Message
	{
		$this->readReceiptTo = array_merge($this->readReceiptTo, $this->normalizeRecipients($addresses));
		return $this;
	}

	/**
	 * Set from address
	 *
	 * @param string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function setFrom(string $address, ?string $name = null): Message
	{
		$this->from = new Address($address, $name);
		return $this;
	}

	/**
	 * Get from address
	 *
	 * @return Address
	 */
	public function getFrom(): Address
	{
		return $this->from;
	}

	/**
	 * Set Replt-To addresses
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.5
	 * @param string|Address ...$addresses
	 * @return $this
	 */
	public function setReplyTo(...$addresses): Message
	{
		$this->replyTo = $this->normalizeRecipients($addresses);

		return $this;
	}

	/**
	 * Ge Reply-To addresses
	 *
	 * @return Address[]
	 */
	public function getReplyTo(): array
	{
		return $this->replyTo;
	}

	/**
	 * Set message date
	 *
	 * @param DateTimeInterface $dateTime
	 * @return $this
	 */
	public function setDate(DateTimeInterface $dateTime): Message
	{
		$this->date = $dateTime;
		return $this;
	}

	/**
	 * Get message date
	 * @return DateTimeInterface
	 */
	public function getDate(): DateTimeInterface
	{
		return $this->date ?? new DateTime();
	}

	/**
	 * Set priority
	 *
	 * Options: null (default), 1 = High, 3 = Normal, 5 = low.
	 * When null, the header is not set at all.
	 *
	 * @see https://www.rfc-editor.org/rfc/rfc4021#section-2.1.54
	 * @param int $priority
	 * @return $this
	 */
	public function setPriority(int $priority): Message
	{
		$this->priority = $priority;
		return $this;
	}

	/**
	 * Get priority
	 *
	 * @see setPriority()
	 * @return int|null
	 */
	public function getPriority(): ?int
	{
		return $this->priority;
	}

	/**
	 * Set sender
	 *
	 * Specifies the mailbox of the agent responsible for the actual
	 * transmission of the message.  Defined as standard by RFC 822.
	 *
	 * @link https://www.rfc-editor.org/rfc/rfc4021#section-2.1.3
	 * @param string $address
	 * @param string|null $name
	 * @return $this
	 */
	public function setSender(string $address, ?string $name = null): Message
	{
		$this->sender = new Address($address, $name);

		return $this;
	}


	/**
	 * Get sender
	 *
	 * @see setSender()
	 * @return Address|null
	 */
	public function getSender(): ?Address
	{
		return $this->sender;
	}


	/**
	 * Set body of message
	 *
	 * @param string $body
	 * @param string $contentType eg. text/html or text/plain
	 * @return $this
	 */
	public function setBody(string $body, string $contentType = 'text/plain'): Message
	{
		$this->body = $body;
		$this->bodyContentType = $contentType;

		return $this;
	}

	/**
	 * Get body
	 *
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * Get body content type
	 *
	 * eg. text/html or text/plain
	 *
	 * @return string
	 */
	public function getContentType(): string {
		return $this->bodyContentType;
	}

	/**
	 * The plain-text message body. This body can be read by mail clients that can't display the normal body.
	 *
	 * @param string $body
	 * @return $this
	 */
	public function setAlternateBody(string $body): Message
	{
		$this->alternateBody = $body;
		return $this;
	}

	/**
	 * Get alternate plain text body
	 *
	 * @return string|null
	 */
	public function getAlternateBody(): ?string
	{
		return $this->alternateBody;
	}

	/**
	 * Send the message via the mailer
	 */
	public function send(): bool
	{
		return $this->getMailer()->send($this);
	}

	/**
	 * Set the message subject
	 *
	 * @param string $subject
	 * @return $this
	 */
	public function setSubject(string $subject): Message
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * Get message subject
	 *
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

	/**
	 * Provide Blob. Blob attachment will be returned.
	 *
	 * @param Blob $blob
	 * @param null $name
	 * @return static
	 * @throws Exception
	 */
	public function addBlob(Blob $blob, $name = null): Message
	{
		$this->attach(Attachment::fromBlob($blob)->setFilename($name ?? $blob->name));
		return $this;
	}

	/**
	 * Attach files
	 *
	 * @param Attachment ...$attachments
	 */
	public function attach(Attachment ...$attachments): Message
	{
		$this->attachments = array_merge($this->attachments, $attachments);

		return $this;
	}

	/**
	 * Embed files
	 *
	 * it will return the cid:.. to use in the <img src="cid:..> tag
	 *
	 * @param Attachment $attachment
	 * @return string
	 */
	public function embed(Attachment $attachment): string
	{
		$attachment->setInline(true);
		$this->attach($attachment);

		return "cid:" . $attachment->getId();
	}

	/**
	 * Get attachments
	 *
	 * @return Attachment[]
	 */
	public function getAttachments() : array {
		return $this->attachments;
	}

	/**
	 * Get the mailer for sending the message
	 *
	 * @return Mailer
	 */
	public function getMailer(): Mailer
	{
		if(!isset($this->mailer)) {
			$this->mailer = go()->getMailer();
		}
		return $this->mailer;
	}

	/**
	 * Set mailer for sending the message
	 *
	 * @param Mailer $mailer
	 * @return $this
	 */
	public function setMailer(Mailer $mailer): Message
	{
		$this->mailer = $mailer;
		return $this;
	}

	/**
	 * Output message as stream
	 * @return false|resource
	 * @throws Exception
	 */
	public function toStream() {
		return $this->getMailer()->toStream($this);
	}

	/**
	 * Output message as string
	 *
	 * @return string
	 * @throws Exception
	 */
	public function toString(): string
	{
		return $this->getMailer()->toString($this);
	}

	/**
	 * Sign the message using SMIME
	 * @param string $certificate The X.509 certificate used to digitally sign input_filename.
	 * @param string $privateKey the private key corresponding to certificate.
	 * @param string $password the password for the private key
	 * @param string $extraCertsFile The file that contains extra certificates that can be used to verify the message
	 *
	 * @return void
	 */
	public function smimeSign(string $certificate, string $privateKey, string $password, string $extraCertsFile = null) {
		$this->smimeCertificate = $certificate;
		$this->smimePrivateKey = $privateKey;
		$this->smimePassword = $password;
		$this->smimeExtraCertsFile = $extraCertsFile;
	}

	/**
	 * Check if this message is signed
	 *
	 * @return bool
	 */
	public function isSmimeSinged() : bool {
		return isset($this->smimeCertificate);
	}

	public function getSmimeCertificate() : ?string {
		return $this->smimeCertificate;
	}

	public function getSmimePrivateKey() : ?string {
		return $this->smimePrivateKey;
	}

	public function getSmimePassword() : ?string {
		return $this->smimePassword;
	}

	public function getSmimeExtraCertsFile() : ?string {
		return $this->smimeExtraCertsFile;
	}

	/**
	 * Encrypt the message
	 *
	 * @param string[] $recipientCertifcates The certificate string data to use for encrypting the data
	 * @return void
	 */
	public function smimeEncrypt(array $recipientCertifcates) {
		$this->smimeEncryptRecipientCertificates = $recipientCertifcates;
	}

	public function isSmimeEncrypted() : bool {
		return isset($this->smimeEncryptRecipientCertificates);
	}

	public function getSmimeEncryptRecipientCertificates() : ?array {
		return $this->smimeEncryptRecipientCertificates;
	}



}
