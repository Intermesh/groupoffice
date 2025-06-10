<?php

namespace go\modules\community\email\model;

use go\core\acl\model\AclItemEntity;
use go\core\db\Criteria;
use go\core\db\Query;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Relation;

class Email extends AclItemEntity {

	public ?int $id;
	/**
	 * If there are no properties provided in Email/get these should be used
	 */
	const defaultProperties = ["id", "blobId", "threadId", "mailboxIds", "keywords", "size",
		"receivedAt", "messageId", "inReplyTo", "references", "sender", "from",
		"to", "cc", "bcc", "replyTo", "subject", "sentAt", "hasAttachment", "attachments",
		"preview"];//, "bodyValues", "textBody", "htmlBody", "attachments"];

	/** @var int From IMAP backend to match (move to email_map because its different per mailbox) */
	protected ?string $uid;
	public function uid() {
		return $this->uid;
	}

	public ?int $accountId;

	/** @var string binary(20) id to raw RFC5322 message */
	public ?string $blobId;

	/** @var string Id of thread this mail belogns to (immutable) */
	public ?int $threadId;

	/** @var array<string,bool> Set of mailbox ids the email belongs to. */
	public ?array $mailboxIds;

	/** @var array<string,bool> $draft, $seen, $flagged, $answered, $forwarded, $phishing, $junk, $notjunk. */
	protected ?string $keywords;
	protected ?bool $seen;
	protected ?bool $answered;
	protected ?bool $flagged;

	/** @var int The size in bytes (immutabke) */
	public ?int $size;

	/** @var \DateTime Date in UTC when the mail was created on the server  (IMAP internal date) */
	public $receivedAt;

	public ?array $sender;

	public ?array $from;

	public ?array $to;

	public ?array $cc;

	public ?array $bcc;

	public ?array $replyTo;

	/** @var string Subject */
	public $subject;

	/** @var \DateTime Date */
	public $sentAt;

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
	public $attachments;

	/** @var boolean true if there is 1 attachment that is not inline or embedded */
	public ?bool $hasAttachment;

	/** @var string up to 255 bytes of summarising body text */
	public ?string $preview;

	/** @var boolean true after loading an parsing the RFC822 body */
	private ?bool $bodyLoaded = false;


	//header fields (all immutable)
	/** @var string[] Message-ID */
	public ?array $messageId;

	/** @var string[] In-Reply-To */
	public ?array $inReplyTo;

	/** @var string[] References */
	public ?array $references;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
			->addTable('email_email', 'e')
			->add('mailboxIds', Relation::scalar('email_map', 'mailboxId')->keys(['id' => 'fk']))
			->add('messageId', Relation::scalar('email_id','messageId')->keys(['id' => 'fk'])->constants(['type'=>'messageId']))
			->add('inReplyTo', Relation::scalar('email_id','messageId')->keys(['id' => 'fk'])->constants(['type'=>'inReplyTo']))
			->add('references', Relation::scalar('email_id','messageId')->keys(['id' => 'fk'])->constants(['type'=>'references']))
			->add('sender', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'sender']))
			->add('from', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'from']))
			->add('to', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'to']))
			->add('cc', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'cc']))
			->add('bcc', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'bcc']))
			->add('replyTo', Relation::array(EmailAddress::class)->keys(['id'=>'fk'])->constants(['type'=>'replyTo']));
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('mailboxId', function(Criteria $criteria, $value, Query $query) {
				$query->join('email_map', 'map', 'map.fk = id');
				$criteria->where('map.mailboxId', '=', $value);
			})->add('accountId', function(Criteria $criteria, $value) {
				$criteria->where('accountId', '=', $value);
			})->add('inMailboxes', function(Criteria $criteria, $value, $query) {
				$query->join('email_map', '`id` = `fk`');
				$criteria->andWhere('email_map.mailboxId', '=', $value);
			});
	}

	public function getKeywords(){
		$kw = [];
		if($this->seen) $kw['$seen'] = true;
		if($this->answered) $kw['$answered'] = true;
		if($this->flagged) $kw['$flagged'] = true;
		return array_merge(json_decode($this->keywords,true), $kw);
	}

	private $kw;
	public function setKeywords($v) {
		$this->kw = $v;
		$kw = (array)$v;
		//set 3 indexed keywords when keywords change
		foreach(['flagged', 'seen', 'answered'] as $indexedKeyword) {
			$w = '$'.$indexedKeyword;
			if(isset($kw[$w])) {
				$this->{$indexedKeyword} = !empty($kw[$w]);
				unset($kw[$w]);
			}
		}
		foreach($kw as $word => $true) {
			if($true) {
				$kw[$word] = true;
			} else if(isset($kw[$word])) {
				unset($kw[$word]);
			}
		}

		$this->keywords = json_encode((object)$kw);
	}

	protected function internalSave(): bool
	{
		// when fetching from backend. We are updating our index.
		// all other save requests go to the backend and sync back
		if($this->backend()->isFetching())
			return parent::internalSave();

		$success = true;
		if(!$this->isNew()) {
			// we may only: change mailbox, set flags
			if($this->isModified(['keywords','flagged','seen','answered'])) {
				// compare old to new and change on backend first.
				$success &= $this->backend()->select($this->firstMailbox()->name)->setFlags($this->kw, $this->uid);
			}
			if($this->isModified('mailboxIds')) {
				// todo
				$success &= $this->backend()->copy('test', $this->uid);
			}
		}
		foreach($this->mailboxIds as $mailboxId) {
			$success &= $this->backend()->fetchChanges($mailboxId)['success'];
		}
		return $success;
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

	public function __isset($name) {
		if($name === 'keywords' && !isset($this->keywords) && !$this->isNew()) {
			$this->getKeywords();
			//$this->keywords = self::find()->select('keywords')->where('id = :id',['id'=>$this->id])->scalar();
		}
		return isset($this->$name);
	}

	public function setHeaders($val) {
		if($this->isNew())
			foreach($val as $header => $value) {
				$this->{$header} = $value;
			}
	}

	public function date() {
		return $this->sentAt;
	}

	/**
	 * If we fetch body properties we need to select to uid to fetch it from IMAP
	 * @param array $props
	 * @return string[]
	 */
//	static public function fetchProps($props) {
//		if(empty($props)) {
//			$props = self::defaultProps;
//		}
//		foreach (['htmlBody', 'textBody', 'attachments', 'bodyStructure', 'bodyValues'] as $p) {
//			if (in_array($p, $props)) {
//				$props[] = 'uid'; // needed for imap fetch
//				break;
//			}
//		}
//		return $props;
//	}

	private $_account;
	private function backend() {
		if(!isset($this->_account))
			$this->_account = EmailAccount::findById($this->accountId);
		return $this->_account->backend();
	}

	/**
	 * Because we only support IMAP backend the first mailbox is also the only mailbox
	 * @return Mailbox|mixed|null
	 * @throws \Exception
	 */
	private function firstMailbox() {
		return Mailbox::find()
			->join('email_map','m', 'm.mailboxId = box.id', 'LEFT')
			->where('m.fk = '.$this->id)
			->single();
	}

	public function toArray(?array $properties = self::defaultProperties): array|null
	{
		return parent::toArray($properties);
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
			list($this->_bodyStructure, $this->_bodyValues) = $this->backend()
				->select($this->firstMailbox()->name)
				->fetchBody($this->uid);

			$this->_htmlBody = [];
			$this->_textBody = [];
			$this->attachments = [];

			$this->parseStructure([$this->_bodyStructure], 'mixed', false, $this->_htmlBody, $this->_textBody, $this->attachments);
		} catch(\Exception $e) {
			// generated a body value showing a readable error
			$this->_htmlBody = [['type'=>'text/error', 'partId'=>'1']];
			$this->_bodyValues = ['1'=>['value'=>$e->getMessage().'<br>'.$e->getFile(). ':'.$e->getLine()]];
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


	protected static function aclEntityClass(): string
	{
		return EmailAccount::class;
	}

	protected static function aclEntityKeys(): array
	{
		return ['accountId'=>'id'];
	}
}
