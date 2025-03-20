<?php

namespace go\modules\community\email\model;

use go\core\acl\model\AclItemEntity;

class Email extends AclItemEntity {
	const defaultProps = ["id", "blobId", "threadId", "mailboxIds", "keywords", "size",
		"receivedAt", "messageId", "inReplyTo", "references", "sender", "from",
		"to", "cc", "bcc", "replyTo", "subject", "sentAt", "hasAttachment", "attachments",
		"preview"];//, "bodyValues", "textBody", "htmlBody", "attachments"];

	/** @var int From IMAP backend to match (move to email_map because its different per mailbox) */
	protected ?string $uid;
	public function uid() {
		return $this->uid;
	}

	/** @var string binary(20) id to raw RFC5322 message */
	public ?string $blobId;

	/** @var string Id of thread this mail belogns to (immutable) */
	public ?int $threadId;

	/** @var array<string,bool> Set of mailbox ids the email belongs to. */
	public ?\stdClass $mailboxIds;

	/** @var array<string,bool> $draft, $seen, $flagged, $answered, $forwarded, $phishing, $junk, $notjunk. */
	private ?\stdClass $dkeywords;
	private ?string $keywords;

	/** @var int The size in bytes (immutabke) */
	public ?int $size;

	/** @var string Date in UTC when the mail was created on the server  (IMAP internal date) */
	protected ?string $receivedAt;

	protected ?string $sender;

	protected ?string $from;

	protected ?string $to;

	protected ?string $cc;

	protected ?string $bcc;

	protected ?string $replyTo;

	/** @var string Subject */
	public ?string $subject;

	/** @var string DateTime Date */
	protected ?string $sentAt;

	/** @var EmailBodyPart full MIME structure of body */
	private $_bodyStructure;

	/** @var array<string,EmailBodyValue> map of partId to BodyValue (`text/*` parts only) */
	private $_bodyValues;
// If alternative versions available preference for `text/html` of `text/plain`
	/** @var EmailBodyPart[] list of `text/plain`,`text/html`,images/*` */
	private $_textBody;

	/** @var EmailBodyPart[] list of `text/plain`,`text/html`,images/*` */
	private $_htmlBody;

	/** @var string only if not in textBody or textHtml and not `multipart/*` */
	protected ?string $attachments;

	/** @var boolean true if there is 1 attachement that is not inline or embedded */
	public ?bool $hasAttachment;

	/** @var string up to 255 bytes of summarising body text */
	public ?string $preview;

	/** @var boolean true after loading an parsing the RFC822 body */
	private ?bool $bodyLoaded = false;


	//header fields (all immutable)
	/** @var string[] Message-ID */
	public ?\stdClass $messageId;

	/** @var string[] In-Reply-To */
	public ?\stdClass $inReplyTo;

	/** @var string[] References */
	public ?\stdCLass $references;

	static protected function relations(): array
	{
		return [
			'mailboxIds' => base\Model::many()->from('email_map')->column('mailboxId'),
			'messageId' => base\Model::many()->from('email_id_map')->where(['type' => "messageId"])->column('messageId'),
			'inReplyTo' => base\Model::many()->from('email_id_map')->where(['type' => "inReplyTo"])->column('messageId'),
			'references' => base\Model::many()->from('email_id_map')->where(['type' => "references"])->column('messageId')
		];
	}

	static function rules(): array
	{
		$immutable = ['immutable'=>true];
		return [
			'blobId'=>['threadId', 'readonly'=>true],
			'uid'=>$immutable,
			'size'=>$immutable,
			'receivedAt'=>$immutable,
			'messageId'=>$immutable,
			'inReplyTo'=>$immutable,
			'references'=>$immutable,
			'sender'=>$immutable,
			'from'=>$immutable,
			'to'=>$immutable,
			'cc'=>$immutable,
			'bcc'=>$immutable,
			'replyTo'=>$immutable,
			'subject'=>$immutable,
			'sentAt'=>$immutable,
			'attachments'=>$immutable,
			'hasAttachment'=>$immutable,
			'preview'=>$immutable,
			'_htmlBody'=>$immutable,
			'_textBody'=>$immutable,
			'_bodyStructure'=>$immutable,
			'_bodyValues'=>$immutable,
			'headers'=>['wanted'=> fn($model) => !$model->isNew],
			'header:from'=>['wanted' => fn($model) => !isset($model->from)],
			'header:*'=>['wanted' => fn($model) => !isset($model->star)],
			'parsedheaders'=>['parsedform' => 'not'],
			'header:content-*'=>['wanted'=>fn($model) => !$model->isNew],
			'bodyStructure' =>['wanted' => fn($model) =>
				!isset($model->textBody) && !isset($model->htmlBody) && !isset($model->attachments)
			],
			// bodyStructure most not have headerfield that is already in this object
			// textBody and htmlBody MUST be 1 bodyPart of correct content-type

		];
	}

	// getters and settings

	public function setUid($uid)
	{
		$this->uid = $uid;
	}

	public function setReceivedAt($val)
	{
		$this->receivedAt = strtotime($val);
	}

	public function setSentAt($val)
	{
		$this->sentAt = strtotime($val);
	}

	public function getBodyStructure()
	{
		$this->loadBody();
		return $this->_bodyStructure;
	}
	public function setBodyStructure($val)
	{
		$this->bodyLoaded = true;
		$this->_bodyStructure = $val;
	}

	public function getBodyValues()
	{
		$this->loadBody();
		return $this->_bodyValues;
	}
	public function setBodyValues($val)
	{
		$this->bodyLoaded = true;
		$this->_bodyValues = $val;
	}

	public function getTextBody()
	{
		$this->loadBody();
		return $this->_textBody;
	}

	public function getHtmlBody()
	{
		$this->loadBody();
		return $this->_htmlBody;
	}

	public function getAttachments()
	{
		if(isset($this->attachments))
			return json_decode($this->attachments);
	}
	public function setAttachments($val)
	{
		$this->attachments = json_encode($val);
	}

	public function getKeywords()
	{
		if(!isset($this->keywords)) {
			$this->keywords = self::find()->select('keywords')->where('id = :id',['id'=>$this->id])->scalar();
		}
		if(!isset($this->dkeywords)) {
			$this->dkeywords = json_decode($this->keywords);
		}
		return $this->dkeywords;
		// if(is_object($this->keywords))
		// 	return (array)$this->keywords;
		// return array_fill_keys(explode(';', $this->keywords), true);
	}

	public function setKeywords($v) {
		if($this->isNew) {
			$this->keywords = json_encode((object)$v);
			return;
		}
		$kw = $this->getKeywords();
		foreach($v as $word => $true) {
			if($true) {
				$kw[$word] = true;
			} else if(isset($kw[$word])) {
				unset($kw[$word]);
			}
		}
		$this->keywords = json_encode((object)$kw);
	}

	public function __isset($name) {
		if($name === 'keywords' && !isset($this->keywords) && !$this->isNew) {
			$this->getKeywords();
			//$this->keywords = self::find()->select('keywords')->where('id = :id',['id'=>$this->id])->scalar();
		}
		return isset($this->$name);
	}

	public function toData():array {
		$d = parent::toData();
		if(isset($this->dkeywords)) {
			$d['keywords'] = json_encode($this->dkeywords);
		}
		return $d;
	}

	static function ids($args) {
		$query = parent::ids($args);
		if(self::$collapseThreads) {
			$query->groupBy('threadId');
		}
		return $query;
	}

	public function getTo() {
		if(isset($this->to))
			return $this->asAddresses($this->to);
	}

	public function getFrom() {
		if(isset($this->from))
			return $this->asAddresses($this->from);
	}

	public function setHeaders($val) {
		if($this->isNew)
			foreach($val as $header => $value) {
				$this->{$header} = $value;
			}
	}

	public function setFrom($val) {
		if(is_string($val)) {
			$this->from = $val;
		} else {
			$from = [];
			foreach($val as $addr) {
				$from[] = isset($addr->name) ? $addr->name . ' <'.$addr->email.'>' : $addr->email;
			}
			$this->from = implode(',', $from);
		}
	}

	public function getSender()
	{
		if(isset($this->sender))
			return $this->asAddresses($this->sender);
	}

	public function getCc()
	{
		if(isset($this->cc))
			return $this->asAddresses($this->cc);
	}

	public function getBcc()
	{
		if(isset($this->bcc))
			return $this->asAddresses($this->bcc);
	}

	public function getMessageId() {
		return $this->messageId ?? (object)[];
	}

	public function references() {
		return $this->references ?? (object)[];
	}

	public function inReplyTo() {
		return $this->inReplyTo ?? (object)[];
	}

	public function getSentAt()
	{
		if(isset($this->sentAt))
			return $this->asDate($this->sentAt);
	}

	public function getReceivedAt()
	{
		if(isset($this->receivedAt))
			return $this->asDate($this->receivedAt);
	}

	public function date() {
		return $this->sentAt;
	}


	static function defineFilters()
	{
		return parent::defineFilters()
		if (!empty($condition->inMailboxes)) {
			$query->innerJoin('email_map', '`id` = `fk`')
				->andWhereIn('email_map.mailBoxId', $condition->inMailboxes);
		}
	}

//	public function populate(array $vars)
//	{
//		parent::populate($vars);
//
//		return $this;
//	}


	/**
	 * If we fetch body properties we need to select to uid to fetch it from IMAP
	 * @param array $props
	 * @return string[]
	 */
	static public function fetchProps($props) {
		if(empty($props)) {
			$props = self::defaultProps;
		}
		foreach (['htmlBody', 'textBody', 'attachments', 'bodyStructure', 'bodyValues'] as $p) {
			if (in_array($p, $props)) {
				$props[] = 'uid'; // needed for imap fetch
				break;
			}
		}
		return $props;
	}

	/**
	 * Get the body parts (only IMAP backend for now)
	 */
	private function loadBody()
	{
		if ($this->bodyLoaded) {
			return;
		}
		try {
			$cmd = ImapBackend::connect()->cmd();


			$mailbox = Mailbox::find()
				->leftJoin('email_map m', 'm.mailboxId = t.id')
				->where('m.fk = '.$this->id)
				->fetch();

			$cmd->open($mailbox->name);

			list($this->_bodyStructure, $this->_bodyValues) = $cmd->fetchBody($this->uid);
//			$this->_bodyStructure = $cmd->getBodyStructure($this, $this->uid);
//			$this->_bodyValues = $cmd->getBodyValues($this->uid); // of previouse get structure

			$this->_htmlBody = [];
			$this->_textBody = [];
			$_attachments = [];

			$this->parseStructure([$this->_bodyStructure], 'mixed', false, $this->_htmlBody, $this->_textBody, $_attachments);
		} catch(\RuntimeException $e) {
			$this->_htmlBody = [$e->getMessage()];
			// TODO: let user know email body didn't exist? if so
		}
		$this->bodyLoaded = true;
	}

	private function asText($val)
	{
		return trim($val);
	}

	/**
	 * This is not fully spec conform. See mimeDecode::mime_extract_rfc2822_address
	 * @param string $emails as JSON
	 * @return EmailAddress[]|null
	 */
	private function asAddresses($emails)
	{
		if (!$emails) {
			return null;
		}
		$res = [];
		$addrs = str_getcsv($emails);
		foreach ($addrs as $item) {
			$item = trim($item);
			$groupPos = strpos($item, ':');
			if ($groupPos !== false) {
				$item = substr($item, $groupPos + 1);
				$group = 'todo';
			}
			preg_match('/(?P<name>[^<]+)\s*<(?P<email>.*)>/', $item, $parts);
			$res[] = [
				'name' => $parts['name'] ?? null,
				'email' => rtrim(ltrim(empty($parts) ? trim($item) : $parts['email'], '<'), '>')
			];
		}
		return $res;
	}

	private function asDate($val)
	{
		return gmdate('Y-m-d\TH:i:s', $val) . 'Z';
	}

	private function asMessageIds($val)
	{
		$ids = preg_split('/\r\n/', trim($val));
		return array_map(fn($n) => rtrim(ltrim($n, '<'), '>'), $ids);
	}

	/**
	 * Decompose MIME into the textBody, htmlBody and attachements
	 * @param EmailBodyPart[] $parts
	 * @param string $multipartType
	 * @param bool $inAlternative
	 * @param EmailBodyPart[] $htmlBody
	 * @param EmailBodyPart[] $textBody
	 * @param EmailBodyPart[] $attachments
	 */
	private function parseStructure($parts, $multipartType, $inAlternative, &$htmlBody, &$textBody, &$attachments)
	{
// For multipartType == alternative
		$textLength = $textBody ? count($textBody) : -1;
		$htmlLength = $htmlBody ? count($htmlBody) : -1;

		for ($i = 0; $i < count($parts); $i++) {

			$part = $parts[$i];
// If multipart/related, only the first part can be inline
// If a text part with a filename, and not the first item in the
// multipart, assume it is an attachment
			$isInline = $part->isInline() &&
				($i === 0 || ($multipartType != "related" &&
						($part->isInlineMedia() || !$part->name ) ) );

			if ($part->isMultipart()) {
				$subMultiType = $part->subType();
				$inAlt = $inAlternative ?: ($subMultiType == 'alternative');
				$this->parseStructure($part->subParts, $subMultiType, $inAlt, $htmlBody, $textBody, $attachments);
			} else if ($isInline) {
				if ($multipartType === 'alternative') {
					switch ($part->type) {
						case 'text/plain':
							$textBody[] = $part;
							break;
						case 'text/html':
							$htmlBody[] = $part;
							break;
						default:
							$attachments[] = $part;
							break;
					}
					continue;
				} else if ($inAlternative) {
					if ($part->type === 'text/plain') {
						$htmlBody = null;
					}
					if ($part->type === 'text/html') {
						$textBody = null;
					}
				}
//if ($textBody) {
				$textBody[] = $part;
//}
//if ($htmlBody) {
				$htmlBody[] = $part;
//}
				if ((!$textBody || !$htmlBody ) && $part->isInlineMedia()) {
					$attachments[] = $part;
				}
			} else {
				$attachments[] = $part;
			}
		}

		if ($multipartType === 'alternative' && $textBody && $htmlBody) {
// Found HTML part only
			if ($textLength == count($textBody) && $htmlLength != count($htmlBody)) {
				for ($i = $htmlLength; $i < count($htmlBody); $i++) {
					$textBody[] = $htmlBody[$i];
				}
			}
// Found plain text part only
			if ($htmlLength == count($htmlBody) && $textLength != count($textBody)) {
				for ($i = $textLength; $i < count($textBody); $i++) {
					$htmlBody[] = $textBody[$i];
				}
			}
		}
	}

	public function internalSave(): bool
	{
		if ($this->isNew()) {


		} else if(isset($this->dkeywords)) {
			$me = go()->getDbConnection()->query()->select('uid, keywords, email_map.mailboxId as mailboxId')->from(self::from())
				->innerJoin('email_map', '`id` = `fk`')->where(['id' => $this->id])
				->prepare()->fetch();

			// todo: remove flasgs also
			// $mailbox = Mailbox::find()->where(['id' => $me->mailboxId])->fetch();
			// if(!ImapBackend::connect()->select($mailbox)->setFlags($this->getKeywords(), $me->uid)){
			// 	return false;
			// }
			// // merge into true only keywords
			// $keywords = (object)array_filter(
			// 	array_merge(json_decode($me->keywords,true), json_decode($this->keywords, true)),
			// 	fn($v) => $v === true
			// );
			// $this->keywords = json_encode($keywords);
			//$success = parent::save();
			// if($modified) {
			// 	$modified->keywords = $keywords;
			// }
			// return $modified;
		}

		// if($this->mailboxIds) {
		// 	foreach($this->mailboxIds as $id) {
		// 		EmailMap::add($this, $id);
		// 	}
		// }
		return parent::internalSave();
	}

	private function makePreview()
	{
		$text = $this->textBody;
		return substr($text, 0, 256);
	}

}
