<?php

namespace go\core\mail;

use DateTimeInterface;
use go\core\fs\Blob;
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
 */
class Message {

	/**
	 * @var Mailer
	 */
	private $mailer;

	/**
	 * @var DateTimeInterface
	 */
	private $date;
	/**
	 * @var int
	 */
	private $priority;

	/**
	 * @var string
	 */
	private $body;
	/**
	 * @var string
	 */
	private $alternateBody;
	/**
	 * @var string
	 */
	private $subject;


	private $to = [];
	private $cc = [];
	private $bcc = [];
	private $attachments = [];

	private $headers = [];
	/**
	 * @var Address
	 */
	private $from;

	/**
	 * @var Address[]
	 */
	private $replyTo = [];
	/**
	 * @var Address
	 */
	private $sender;
	/**
	 * @var string
	 */
	private $id;
	/**
	 * @var array
	 */
	private $references = [];
	/**
	 * @var string
	 */
	private $bodyContentType;

	public function __construct(Mailer $mailer) {
		$this->mailer = $mailer;
		$this->setFrom(go()->getSettings()->systemEmail, go()->getSettings()->title);
	}


	public function getId() : ?string
	{
		return $this->id;
	}

	public function setId(string $id): Message
	{
		$this->id = $id;
		return $this;
	}

	public function setReferences(...$references): Message
	{
		$this->references = $references;
		return $this;
	}

	public function getReferences(): array
	{
		return $this->references;
	}

	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}

	public function getHeaders(): array
	{
		return $this->headers;
	}

	private function normalizeRecipients(array $addresses) {
		foreach($addresses as &$address) {
			if(is_string($address)) {
				$address = new Address($address);
			}
		}
		return $addresses;
	}

	public function setTo(...$addresses): Message
	{
		$this->to = $this->normalizeRecipients($addresses);
		return $this;
	}

	/**
	 * @return Address[]
	 */
	public function getTo(): array
	{
		return $this->to;
	}

	public function addTo(...$addresses): Message
	{
		$this->to = array_merge($this->to, $this->normalizeRecipients($addresses));
		return $this;
	}

	public function setBcc(...$addresses): Message
	{
		$this->bcc = $this->normalizeRecipients($addresses);
		return $this;
	}


	/**
	 * @return Address[]
	 */
	public function getBcc(): array
	{
		return $this->bcc;
	}

	public function addBcc(...$addresses): Message
	{
		$this->bcc = array_merge($this->bcc, $this->normalizeRecipients($addresses));
		return $this;
	}

	public function setCc(...$addresses): Message
	{
		$this->cc = $this->normalizeRecipients($addresses);
		return $this;
	}

	/**
	 * @return Address[]
	 */
	public function getCc(): array
	{
		return $this->cc;
	}

	public function addCc(...$addresses): Message
	{
		$this->cc = array_merge($this->cc, $this->normalizeRecipients($addresses));
		return $this;
	}

	public function setFrom(string $address, ?string $name = null): Message
	{
		$this->from = new Address($address, $name);
		return $this;
	}

	public function getFrom(): Address
	{
		return $this->from;
	}

	/**
	 *
	 */
	public function setReplyTo(...$addresses): Message
	{
		$this->replyTo = $this->normalizeRecipients($addresses);

		return $this;
	}

	/**
	 * @return Address[]
	 */
	public function getReplyTo(): array
	{
		return $this->replyTo;
	}

	public function setDate(DateTimeInterface $dateTime): Message
	{
		$this->date = $dateTime;
		return $this;
	}

	public function getDate(): DateTimeInterface
	{
		return $this->date ?? new DateTime();
	}

	public function setPriority(int $priority): Message
	{
		$this->priority = $priority;
		return $this;
	}

	public function getPriority(): ?int
	{
		return $this->priority;
	}

	public function setSender(string $address, ?string $name = null): Message
	{
		$this->sender = new Address($address, $name);

		return $this;
	}

	public function getSender(): ?Address
	{
		return $this->sender;
	}


	public function setBody(string $body, string $contentType = 'text/plain'): Message
	{
		$this->body = $body;
		$this->bodyContentType = $contentType;

		return $this;
	}

	public function getBody(): string
	{
		return $this->body;
	}

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

	public function getAlternateBody(): ?string
	{
		return $this->alternateBody;
	}

	/**
	 * Send this Message like it would be sent in a mail client.
	 *
	 * All recipients (with the exception of Bcc) will be able to see the other
	 * recipients this message was sent to.
 * The return value is the number of recipients who were accepted for
	 * delivery.
	 *
	 * @param array|null $failedRecipients An array of failures by-reference
	 *
	 * @return int The number of successful recipients. Can be 0 which indicates failure
	 */
	public function send(): bool
	{
		return $this->mailer->send($this);
	}

	public function setSubject(string $subject): Message
	{
		$this->subject = $subject;
		return $this;
	}

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
	 */
	public function addBlob(Blob $blob, $name = null): Message
	{
		$this->attach(Attachment::fromBlob($blob)->setFilename($name ?? $blob->name));
		return $this;
	}

	/**
	 * @param Attachment ...$attachments
	 */
	public function attach(...$attachments): Message
	{
		$this->attachments = array_merge($this->attachments, $attachments);

		return $this;
	}

	public function embed(Attachment $attachment): string
	{
		$this->attach($attachment);

		return "cid:" . $attachment->getId();
	}

	/**
	 * @return Attachment[]
	 */
	public function getAttachments() : array {
		return $this->attachments;
	}

	/**
	 * @return Mailer
	 */
	public function getMailer(): Mailer
	{
		return $this->mailer;
	}

	public function toStream() {
		return $this->mailer->toStream($this);
	}
}
