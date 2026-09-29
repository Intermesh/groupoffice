<?php

namespace go\modules\community\email\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;

/**
 * @property string $smtpReply DSN (Delivery Status Notification) response from SMTP
 * @property string $delivered represents whether the email has been successfully delivered ('queued', 'yes','no','unknown')
 * @property string $displayed MDN (Mail Delivery Notification) response ('unknown','yes')
 */
interface DeliveryStatus
{
}

/**
 * @property Address $mailFrom The email address to use as the return address in the SMTP submission
 * @property Address[] $rcptTo The email addresses to send the message to, and any RCPT TO parameters to pass with the recipient
 */
interface Envelope
{
}

/**
 * @property string $email The email address being represented by the object
 * @property Object|null $parameters Any parameters to send with the email address
 */
interface Address
{
}

class EmailSubmission extends Entity
{

	static $onSuccessUpdateEmail;

	const SPending = 1; // may be possible
	const SFinal = 2; // cannot be cancelled
	const SCanceled = 3; // is canceled

	/** @var int identity associated with this submission */
	public ?int $identityId;

	/** @var int mail to send */
	public ?int $emailId;

	/** @var int set by server (email referenced in emailId) */
	public ?int $threadId;

	/** @var string the SMTP envelope data json encoded */
	protected ?string $envelope;

	/** @var string UTCDate server set the date the email will be released for delivery */
	public ?string $sendAt;

	/** @var string server-set whether the submission may be cancelled */
	public ?string $undoStatus;

	/** @var array<string,DeliveryStatus>|null server-set status for each recipient */
	public ?object $deliveryStatus;

	/** @var string[] server-set list if blob ids for Delivery Status Notification */
	public ?array $dsnBlobIds;

	/** @var string[] server-set list if blob ids for Message Disposition Notification */
	public ?array $mdnBlobIds;



	protected static function defineFilters(data\DbQuery $query, $condition)
	{
		return parent::defineFilters()
			->add('emailId', function($crit, $v) {
				$crit->andWhereIn('emailId', $v);
			}
			->add('threadId', function($crit, $v) {
				$crit->andWhereIn('threadIds', $v);
			})
			->add('undoStatus', function($crit, $v) {
				$crit->andWhere('undoStatus = :undo', ['undo' => $v]);
			})
			->addDateTime("sendAt", function (Criteria $criteria, $comparator, $value) {
				$criteria->where('sendAt', $comparator, $value);
			});
	}

	public function setEnvelope($data)
	{
		$this->envelope = json_encode($data);
	}

	/** @return Envelope $envelope */
	public function getEnvelope()
	{
		if (empty($this->envelope)) {
			return $this->generateEnvelope();
		}
		return json_decode($this->envelope);
	}

	/**
	 * @return Envelope|object
	 */
	private function generateEnvelope()
	{
		$e = $this->email();
		$from = $e->getSender() ?? $e->getFrom();
		$mailFrom = (object)['email' => $from[0]['email']];
		$rcptTo = [];
		foreach ([$e->getTo(), $e->getCc(), $e->getBcc()] as $to) {
			if (!$to) continue;
			foreach ($to as $addr) {
				$rcptTo[] = (object)['email' => $addr['email']]; // removes name
			}
		}
		return (object)['mailFrom' => $mailFrom, 'rcptTo' => $rcptTo];

	}

	private $email;

	private function email()
	{
		if (!isset($this->email)) {
			$this->email = Email::find(['threadId', 'to', 'cc', 'bcc', 'uid', 'from', 'sender'])->where(['id'=>$this->emailId])->single();
		}
		return $this->email;
	}

	public function setEmailId($id)
	{
		$this->emailId = $id;
		$this->threadId = $this->email()->threadId;
		//set Envelope
	}

	public function internalSave($cid = null)
	{
		if (!isset($this->sendAt)) {
			$this->send(); // could throw a runtime exception
			$this->sendAt = date('Y-m-d H:i:s');
		}
		if (($result = parent::save()) !== true) {
			return $result;
		}

		if (isset(self::$onSuccessUpdateEmail->$cid)) {
			$patch = self::$onSuccessUpdateEmail->$cid;
			Email::open($this->emailId)->apply($patch)->save();
		}

		return true;
	}

	public function send()
	{
		$imap = ImapBackend::connect();
		$mailbox = Mailbox::find()->where(['role' => Role::Draft])->single();
		$imap->select($mailbox);
		$data = $imap->fetch($this->email()->uid());

		$envelope = $this->getEnvelope();

		$smtp = go()->getMailer()->smtp(1);
		$smtp->mail($envelope->mailFrom->email);
		foreach ($envelope->rcptTo as $address) {
			$smtp->rcpt($address->email);
		}
		$smtp->data($data);
	}

}
