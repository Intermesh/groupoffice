<?php

namespace go\modules\community\email\model;

use go\core\mail;

class ImapBackend {
	const HeaderFields = 'FROM TO SUBJECT DATE CC BCC REPLY-TO IN-REPLY-TO SENDER REFERENCES MESSAGE-ID';
	/**
	 * The items we fetch to build an index // BODY.PEEK[1]<0.512>
	 */
	const Descriptor = ['UID', 'FLAGS', 'INTERNALDATE','RFC822.SIZE', 'PREVIEW', 'BODY.PEEK[HEADER.FIELDS ('.self::HeaderFields.')]'];

	/** @var array<string,string> known IMAP flags to parse to JMAP keywords */
	protected static $knownFlags = [
		'\\Draft' => '$draft',
		'\\Seen' => '$seen',
		'\\Flagged' => '$flagged',
		'\\Answered' => '$answered',
		'\\Recent' => false,
		'\\Deleted' => false // Email should never show in JMAP
	];
	//other keywords
	//forwarded
	//MDNSent
	//Junk
	//NotJunk
	//Phishing


	protected static $parseFlags = [
		'$draft' => '\\Draft',
		'$seen' => '\\Seen',
		'$flagged' => '\\Flagged',
		'$answered' => '\\Answered',
	];

	static $roleMap = [
		'inbox' => 'inbox',
		'drafts' => 'drafts',
		'draft' => 'drafts',
		'draft messages' => 'drafts',
		'bulk' => 'spam',
		'bulk mail' => 'spam',
		'junk' => 'spam',
		'junk mail' => 'spam',
		'spam' => 'spam',
		'spam mail' => 'spam',
		'spam messages' => 'spam',
		'archive' => 'archive',
		'sent' => 'sent',
		'sent items' => 'sent',
		'sent messages' => 'sent',
		'deleted messages' => 'trash',
		'trash' => 'trash',
		'\\inbox' => 'inbox',
		'\\trash' => 'trash',
		'\\sent' => 'sent',
		'\\junk' => 'spam',
		'\\spam' => 'spam',
		'\\archive' => 'archive',
		'\\drafts' => 'drafts',
	];

	/** @var self singleton */
	private static $instance;

	/** @var mail\Imap */
	public $imap;

	private $config;

	private $currentMailbox;

	/**
	 * When the adapter is fetching mail do not syncback to the server. we are in reading mode
	 */
	private $isFetching = false;

	private EmailAccount $account;



	private function __construct(mail\Imap $protocol) {
		$this->imap = $protocol;
	}

	/** @return self|false */
	static public function connect($dsn, $account) {
		//none numeric?
		if (!isset(self::$instance)) {
			//mb_regex_encoding("UTF-8");
			$imap = new mail\Imap($dsn->host, $dsn->port, $dsn->encryption);
			if ($imap->login($dsn->user, $dsn->pass)) {
				self::$instance = new self($imap);
				self::$instance->account = $account;
				//$imap->sendRequest('ENABLE QRESYNC');
			} else {
				throw new \RuntimeException('Imap Authentication failed');
			}
		}
		if(self::$instance->isFetching) { // todo: remove when imap update goes before local
			return false; // dont sync back
		}
		return self::$instance;
	}

	function cmd() {
		return new ImapCommand($this);
	}

	private function setMailbox($json) {
		// check account
		$cmd = self::connect()->cmd();
		// sync folders to minimise the race time
		foreach($cmd->list() as $name => $params) {
			$imailbox = $cmd->examine($name);
			$mailbox = Mailbox::find()->where(['uid' => $imailbox->uidvalidity])->fetch();
			// update folders on imap server first.
			if($json->destroy[$mailbox->id]) {
				$result = $cmd->delete($name);
			}
			if($json->update[$mailbox->id]) {
				$result = $cmd->rename($name, $json->update[$mailbox->id]->name);
			}
		}
		foreach($json->create as $values) {
			$result = $cmd->create($values->name);
			$mailbox = Mailbox::create($values);
			$mailbox->imapSyncProps($result);
			$mailbox->save();
		}

		// copied from Email::save()
		// if($adapter = self::adapter()) {
		// 	if($this->isNew) {
		// 		//todo : prefix with parents if parentId. find delimiter
		// 		$adapter->createFolder($this->name);
		// 	} else {
		// 		if(isset($this->name) || isset($this->parentId)) {
		// 			$adapter->renameFolder($this->name);
		// 		}
		// 		if(isset($this->isSubscribed)) {
		// 			$adapter->subscribeFolder($this->name);
		// 		}
		// 	}
		// }


		// put stuff in notCreated/updated/destroyed if the imap server failed to make the change.
		$result = Api::set(Mailbox::class, $json);
	}


	private function setEmail($json) {

		// https://www.ietf.org/rfc/rfc9051.html#name-store-command
		// Creating a draft
		// Changing the keywords of an Email (e.g., unread/flagged status)
		// Adding/removing an Email to/from Mailboxes (moving a message)
		// Deleting Emails


		// check account
		// sync email to minimise the race time
		// update email on imap server
		$conn = self::connect()->cmd();

		// destroy
		$groupedUids = Email::find()->select('mailboxId, uid')->innerJoin('email_map', 'fk = id')->andWhereIn('id', $json->destroy)->fetchGrouped();
		foreach($groupedUids as $mailboxId => $uids) {
			$conn->open($mailboxId)->store($uids)->flags('+\\Deleted')->exec();
		}
		// update (flags or move)
		if(!empty($json->update)) {
			$uidMailbox = Email::find()->select('id, uid, GROUP_CONCAT(mailboxId)')->innerJoin('email_map', 'fk = id')
				->andWhereIn('id', array_keys($json->update))
				->groupBy('id')
				->fetchGrouped(\PDO::FETCH_UNIQUE);
			foreach($json->update as $id => $patch) {
				$uid = $uidMailbox[$id]['uid'];
				$mailboxIds = explode(',',$uidMailbox[$id]['mailboxIds']);
				$email = Email::query();
				$conn->select($mailboxIds[0])->for($uid);
				if(isset($patch->mailboxIds)) {
					// move
					$conn->move($patch->mailboxIds[0]);

				}
				if(isset($patch->keywords)) {
					$conn->flags($patch->keywords);
					// store -> flags
				}
			}
		}
		if(!empty($json->create)) {
			//$names[$email->mailboxIds[0]]
			$conn->open('drafts');
			foreach($json->create as $values) {
				$email = self::emailAppend(function($mail) use($conn) {
					return $conn
						->append(new MimeHelper($mail))
						->flags(self::parseFlags($mail))
						->exec();
				}, $values);
				// $email->save();
			}
		}

		$result = Api::set(Email::class, $json);

		// todo

		return $result;
	}



	static function emailAppend($maker, $values) {
		$service = server()->account()->service('mail');
		$me = Email::create($values);
		$uuid = Crypto::UUIDv4();
		$me->threadId = Thread::byMessage($me); // slow on full fetch, can build threads later
		$me->messageId = (object)[sprintf('<%s@%s>', $uuid, $service->host)];

		//build RFC_882 for adapter
		$mime = $maker($me);
		$me->uid = $mime->uid;
		$me->hasAttachment = $mime->hasAttachments;
		$me->size = mb_strlen($mime->encoded, '8bit');
		$me->blobId = 'mail.'.$uuid;
		return $me;
	}

	static private function mailboxMap($entities) {
		$mailboxIds = [];
		foreach($entities as $entity) {
			$mailboxIds = array_merge($mailboxIds, $entity->mailboxIds);
		}
		$return = Mailbox::find()->andWhereIn('id', $mailboxIds)->fetchKeyPair('id', 'name');
	}

	private static function parseFlags($email) {
		$flags = [];
		foreach($email->getKeywords() as $keyword => $true) {
			if(isset(self::$parseFlags[$keyword])) {
				$flags[] = self::$parseFlags[$keyword];
			}
		}
		return $flags;
	}

	// public function select($mailbox) {
	// 	if($this->currentMailbox !== $mailbox) {
	// 		$this->currentMailbox = $mailbox;
	// 		$this->imap->select($mailbox->name, [$mailbox->uid(), $mailbox->highestModSeq()]);
	// 	}
	// 	return $this;
	// }

	public function append(Email $email) {

		$mime = new MimeHelper($email);

		$flags = self::parseFlags($email);

		$mailboxes = Mailbox::find()->andWhereIn('id', array_keys((array)$email->mailboxIds));
		$uid = false;
		foreach($mailboxes as $mailbox) {
			$uid = $this->imap->append($mailbox->name, $mime->encode(), $flags);
			break; // TODO use copy for the other mailboxes
		}
		$mime->uid = $uid;

		return $uid ? $mime : false;
	}

	public function setFlags($keywords, $uid) {
		$addflags = [];
		$removeflags = [];
		foreach($keywords as $keyword => $enable) {
			if(isset(self::$parseFlags[$keyword])) {
				if($enable) {
					$addflags[] = self::$parseFlags[$keyword];
				} else {
					$removeflags[] = self::$parseFlags[$keyword];
				}

			}
		}
		$success = true;
		if(!empty($addflags)) {
			$success &= $this->imap->uidStore($addflags, $uid, '+');
		}
		if(!empty($removeflags)) {
			$success &= $this->imap->uidStore($removeflags, $uid, '-');
		}
		return $success;
	}

	/**
	 * @return string RFC822 MIME
	 */
	public function fetch($uid) {
		return $this->imap->fetch(['RFC822'], $uid, null, true);
	}

	//copied from Account
	// public function sync($type = null) {
	// 	if (!isset($this->apps->{$type})) {
	// 		return;
	// 	}
	// 	$app = $this->apps->{$type};

	// 	switch ($app->adapter) {
	// 		case Service::IMAP:
	// 			$adapter = \dw\mail\ImapSource::connect();
	// 			$adapter->fill();
	// 			break;
	// 	}
	// }

	/**
	 * slow resync (but check for condstore)
	 * enhance: send both command (dont wait for response)
	 */
	public function sync($mailboxId) {
		$this->isFetching = true;
		$mailbox = Mailbox::find()->where(['id' => $mailboxId])->fetch();
		$imailbox = $this->imap->examine($mailbox->name);

		// TODO: imailbox could be deleted
		$newMails = [];
		$syncable = $imailbox['uidvalidity'] == $mailbox->uid(); // same mailbox
		if($syncable) { // fetch new mail only
			if($imailbox['uidnext'] != $mailbox->uidnext()) {
				$newMails = $this->imap->fetch(self::Descriptor, $mailbox->uidnext(), INF, true); // new
			}
		} else { // full refetch of mailbox
			// delete all mail that is in this mailbox
			go()->getDbConnection()->delete('email_email')
				->join('email_map', 'fk = id', 'LEFT')
				->where("mailboxId = $mailbox->id")->exec();
			$newMails = $this->imap->fetch(self::Descriptor, 1, INF, true); // all
			$mailbox->setUid($imailbox['uidvalidity']);
		}

		// If CONDSTORE extension, only fetch new flags
		$capabilities = $this->service->capabilities;
		$vanished = [];
		if(in_array('CONDSTORE',$capabilities)) {
			$this->imap->sendRequest('ENABLE QRESYNC'); // CHUCK NORRIS
			$response = $this->imap->fetch(['FLAGS', 'UID'], 1, $mailbox->uidnext(), true, ['CHANGEDSINCE '.$mailbox->highestModSeq(). ' VANISHED']); // new
			foreach($response as $k => $v) {
				if($v === null) {
					if(strpos($k, ':')) {
						list($from, $till) = explode(':',$k);
						for($i = $from; $i <= $till; $i++) {
							$vanished[] = $i;
						}
					} else {
						$vanished[] = $k;
					}
				} else {
					$existingFlags[$k] = $v;
				}
			}
			//$uids = $this->imap->search(['ALL'], true);
			// delete all mail not in UIDs
			if(!empty($vanished)) {
				go()->getDbConnection()->delete('email_email')
					->join('email_map', 'fk = id', 'left')
					->where("mailboxId = $mailbox->id")
					->andWhereIn('uid', $vanished)->execute();
			}
		} else if($syncable) {
			$existingFlags = $this->imap->fetch(['FLAGS', 'UID'], 1, $mailbox->uidnext(), true); // all
		}

		// insert new mail
		$newMailIds = [];
		foreach($newMails as $item) {
			$email = $this->parseFetchedEmail($item);
			if($email->save() === true){
				$newMailIds[] = $email->id;
			}
		}
		go()->getDbConnection()->insert('email_map', ['fk'], $newMailIds, ['mailboxId' => $mailbox->id]);


		// TODO flags and expunged when no CONDSTORE extension available
		//$localUids = Email::find()->fetchKeyPair('uid', 'id');
		foreach($existingFlags as $change) {
			// update flag
			go()->getDbConnection()->update('email_email')
				->set(['keywords' => json_encode($this->readFlags($change['FLAGS'], $hasAtt))])
				->where(['uid' => $change['UID']])->exec();
		}

		// update mailbox highestmodseq
		$mailbox->imapSyncProps($imailbox['uidnext'], $imailbox['highestmodseq'])->save();
		return ['success'=>true, 'new'=>count($newMails), 'flags'=>count($existingFlags), 'vanished'=>count($vanished)];
	}

	public function loopBoxes() {
		$mailboxes = $this->imap->listMailbox('', '*');
		foreach ($mailboxes as $name => $params) {
//var_dump($params['flags']);
			if (in_array('\\NoSelect', $params['flags'])) {
				continue;
			}
			$imailbox = $this->imap->examine($name);

		}

		//if (in_array('\\HasChildren', $params['flags'])) {
		//	 children are co
		//}
	}

	/**
	 *
	 * @param int $accountId
	 * @return void
	 * @throws \go\core\db\DbException
	 */
	private function clearCache() {
		$accountId = (int)$this->account->id;
		go()->getDbConnection()->query('DELETE FROM email_email WHERE accountId = '.$accountId)->execute();
		go()->getDbConnection()->query('DELETE FROM email_thread WHERE accountId = '.$accountId)->execute();
		go()->getDbConnection()->query('SET FOREIGN_KEY_CHECKS = 0')->execute();
		go()->getDbConnection()->query('DELETE FROM email_mailbox WHERE accountId = '.$accountId)->execute();
		go()->getDbConnection()->query('SET FOREIGN_KEY_CHECKS = 1')->execute();
	}

	/**
	 * First fetch all IMAP mail
	 */
	public function fill() {
		$this->isFetching = true;

		$this->clearCache();

		$success = true;
		try {
			go()->getDbConnection()->beginTransaction();
			$mailboxes = $this->imap->listMailbox('', '*');

			ksort($mailboxes); // make sure parent comes before child
			$parentMap = [];
			//EmailBodyPart::$jsonProps[EmailBodyPart::class] = ['type', 'size', 'name', 'blobId'];

			foreach ($mailboxes as $name => $params) {
//var_dump($params['flags']);
				if (in_array('\\NoSelect', $params['flags'])) {
					continue;
				}
				$imailbox = $this->imap->examine($name);

				$mailbox = new Mailbox();
				$mailbox->accountId = $this->account->id;
				//$mailbox->mustBeOnlyMailbox = true;
				//$mailbox->highestUID = $imailbox['uidvalidity'];
				$parts = explode($params['delim'], $name);
				$mailbox->name = array_pop($parts);
				if(!empty($parts)) {
					$mailbox->parentId = $parentMap[array_pop($parts)];
				}
				$mailbox->role = $this->parseMailboxFlags($mailbox->name,$params['flags']);
				//$mailbox->sortOrder = $mailbox->role ?? 99;
				$mailbox->setUid($imailbox['uidvalidity']); // = mailbox id from IMAP server
				$mailbox->imapSyncProps($imailbox['uidnext'], isset($imailbox['highestmodseq']) ? $imailbox['highestmodseq'] : 0);

				$mailbox->save();
				$ids = $this->fillMessages($mailbox);

				if(!empty($ids))
					go()->getDbConnection()->insert('email_map', $ids, ['fk', 'mailboxId'])->execute();

				$parentMap[$mailbox->name] = $mailbox->id;
			}
			go()->getDbConnection()->commit();
		} catch (\PDOException $e) {
			echo $e->getMessage();
			echo $e->getTraceAsString();
			go()->getDbConnection()->rollback();
			$success = false;
		} catch (\Exception $e) {
			$success = false;
			echo $e->getMessage();
			echo $e->getTraceAsString();
		}
		$this->isFetching = true;

		return $success;
	}

	private function parseMailboxFlags($name, $flags) {
		if (strtoupper($name) === 'INBOX') {
			return 'inbox';
		}
		foreach ($flags as $flag) {
			$lflag = strtolower($flag);
			if (isset(self::$roleMap[$lflag])) {
				return self::$roleMap[$lflag];
			}
		}
		return null;
	}

	private function fillMessages($mailbox) {
		$messages = $this->imap->fetch(self::Descriptor, 1, INF); // ['RFC822']
		$ids = [];
		foreach ($messages as $item) {
			$email = $this->parseFetchedEmail($item);
			if($email->save()){
				$ids[] = [$email->id, $mailbox->id];
			}
		}
		$mailbox->setHighestUID(isset($item['UID']) ? $item['UID'] : 0);// Highest UID
		return $ids;
	}

	private function parseFetchedEmail($item) {
		// only works for default descriptor
		//find first?
		// $decoder = new \Mail_mimeDecode($item['BODY.PEEK[HEADER.FIELDS'][11]);
		//$decoded = $decoder->decode();
		$email = (new Email)->setValues([
			'uid'=>$item['UID'],
			'accountId' => $this->account->id,
			'receivedAt' => \DateTime::createFromFormat('d-M-Y H:i:s O+', $item['INTERNALDATE']), // "19-Mar-2020 17:54:09 +0100"
			'size' => (int) $item['RFC822.SIZE'],
			'keywords' =>$this->readFlags($item['FLAGS'], $hasAttachements),
			'hasAttachment' => $hasAttachements, // || $this->hasAttachment($item["BODYSTRUCTURE"]);
			'preview' =>$item['PREVIEW'], // $this->parsePreview($item);
		]);
		$email->setHeaders($this->decodeHeaders($item['BODY[HEADER.FIELDS'][11],$email));
		$email->threadId = Thread::byMessage($email);

		$this->currentEmail = $email;
		//$email->setAttachments($this->parseAttachments($item["BODYSTRUCTURE"]));

		return $email;
	}

	private function decodeHeaders($input, $owner) {
		// read headers
		$headers = [];
		//$input = preg_replace("/\r?\n/", "\r\n", $item['BODY[HEADER.FIELDS'][11]); // fix scrappy newlines?
		// wrapping space should only get removed if the trailing item on previous line is an encoded character
		$input = preg_replace("/=\r\n(\t| )+/", '=', $input); // unwrap multiline headers
		$input = preg_replace("/\r\n(\t| )+/", ' ', $input);

		$header = strtok(trim($input), "\r\n");
		while($header !== false) {
			list($name, $value) = explode(':',\mb_decode_mimeheader($header), 2);

			$key = strtolower($name);
			if(in_array($key, ['message-id','in-reply-to','references'])) {
				$headers[self::$wantedHeaders[$key]] = array_map(function($i) {
					return trim($i,'<>');
				},preg_split("/\s+/", $value, -1, PREG_SPLIT_NO_EMPTY));
			} elseif(in_array($key, ['from','to','reply-to', 'sender', 'cc','bcc'])) {

				$headers[self::$wantedHeaders[$key]] = [];
				foreach (explode(',', $value) as $entry) {
					$entry = trim($entry);
					if (preg_match('/^(?:"?([^"]*)"?\s)?<?([^<>]+)>?$/', $entry, $parts)) {
						$headers[self::$wantedHeaders[$key]][] = (new EmailAddress($owner))->setValues([
							'name' => trim($parts[1] ?? ''),
							'email' => trim($parts[2]),
						]);
					}
				}

			} elseif(isset(self::$wantedHeaders[$key])) {
				$headers[self::$wantedHeaders[$key]] = trim($value);
			} elseif($key === 'date') {
				// "Fri, 14 Sep 2018 17:00:46 +0200 (CEST)" | "Tue, 18 Sep 2018 15:00:59 +0200"
				$date = \DateTime::createFromFormat('D, d M Y H:i:s O+', trim($value));
				if($date === false) {
					// 08 Mar 2018 21:18:05 +0800
					$date = \DateTime::createFromFormat('d M Y H:i:s O', trim($value));
				}
				$headers['sentAt'] = $date;
			}

			$header = strtok("\r\n");
		}
		return $headers;
	}

	// FROM TO SUBJECT DATE CC BCC REPLY-TO IN-REPLY-TO REFERENCES MESSAGE-ID
	private static $wantedHeaders = [
		'message-id' => 'messageId',
		'in-reply-to' => 'inReplyTo',
		'references' => 'references',
		'sender' => 'sender',
		'from' => 'from',
		'to' => 'to', 'cc' => 'cc', 'bcc' => 'bcc',
		'reply-to' => 'replyTo',
		'subject' => 'subject',
		//'sentAt' => 'date'
	];

	private function readFlags($flags, &$hasAttachment)
	{
		$keywords = [];
		$hasAttachment = false;
		foreach ($flags as $value) {
			if (isset(self::$knownFlags[$value])) {
				if (self::$knownFlags[$value] !== false) {
					$keywords[self::$knownFlags[$value]] = true;
				}
			} else if (strtolower($value) === '$hasattachment') { // dovecot 2.3.2
				$hasAttachment = true;
			} else if (substr($value, 0, 1) === '$') {
				$keywords[strtolower($value)] = true;
			}
		}

		return (object)$keywords;
	}

	/**
	 * Grab EmailBodyPart[] from bodystructure
	 */
	private function parseAttachments($structure, $partId = '1') {
		$attachments = [];
		if (is_array($structure[0])) { // multipart
			$i = 0;
			$type = $structure[count($structure) - 5];
			while (is_array($structure[0])) {
				$i++;
				$subPartId = ($partId === '1') ? $i : "$partId.$i";
				$attachments += $this->parseAttachments(array_shift($structure), $subPartId);
			}
		} else {
			$type = array_shift($structure);
			$subtype = array_shift($structure);
			$params = array_shift($structure);
			$cid = array_shift($structure);
			$description = array_shift($structure);
			$encoding = array_shift($structure);
			$size = array_shift($structure);
			if ($type === 'message' && $subtype == 'rfc822') {
				return $attachments;
				// $envelope = array_shift($structure);
				// $bodyStructure = array_shift($structure);
				// $lines = array_shift($structure);
			}
			if ($type === 'text') {
				$lines = array_shift($structure);
			}
			$md5 = array_shift($structure);
			$disposition = array_shift($structure);

			if($type !== 'multipart' && (is_array($disposition) && $disposition[0] === 'attachment')) {
				$part = new EmailBodyPart();
				$part->type = $type . '/' . $subtype;
				$part->size = $size;
				$dispositionAttachment = array_shift($disposition);
				$params = array_shift($disposition);
				$part->blobId = 'mail.'.$this->currentEmail->uid().'-'.$partId;
				if (is_array($params)) {
					while ($key = array_shift($params)) {
						switch ($key) {
							case 'filename':
								$part->name = array_shift($params);
								break;
							default: array_shift($params);
						}
					}
				}
				$attachments[] = $part;
			}
		}
		return $attachments;
	}

	public function getBodyValues($uid) {
		$parts = ['UID'];
		foreach ($this->textBodyParts as $nb => $enc) {
			$parts[] = 'BODY.PEEK[' . $nb . ']';
		}
		$response = $this->imap->fetch($parts, $uid, null, true);
		$parts = [];
		foreach ($this->textBodyParts as $nb => $enc) {
			$parts[$nb] = ['value' => ImapSource::decodeBody($response["BODY[$nb]"], $enc[0], $enc[1])];
		}
		return $parts;
	}

	static function decodeBody($value, $encoding, $charset) {
		if ($encoding == 'quoted-printable') {
			$value = quoted_printable_decode($value); //$value = iconv_mime_decode($value, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, $enc[1]);
		} else if ($encoding == 'base64') {
			$value = base64_decode($value);
		}
		if($charset === 'iso-8859-1') { // if us-ascii dont encode
			$value = mb_convert_encoding($value, 'UTF-8', $charset);
		}
		return trim($value);
	}

	public function getBodyStructure(Email $email, $uid) {
		$response = $this->imap->fetch(['BODYSTRUCTURE'], $uid, null, true);
		$this->currentEmail = $email;
		return $this->parseBodyStructure($response, 'message/rfc822');
	}

	private $currentEmail;

	private $textBodyParts = [];



	// public function createMailbox() {
	// 	$mailbox = new Mailbox();
	// 	$mailbox->accountId = server()->account()->id;
	// 	$mailbox->mustBeOnlyMailbox = true;
	// 	return $mailbox;
	// }

//	public function sync() {
//		$namespace = $this->imap->requestAndResponse('NAMESPACE');
////$delimiter = $namespace[0][1][0][1];
//		$mailboxes = $this->imap->listMailbox('', '*');
//		$this->imap->sendRequest('ENABLE QRESYNC'); // CHUCK NORRIS
//		server()->db()->begin();
//		try {
//			foreach ($mailboxes as $name => $params) {
//				//var_dump($params);
//				$imapMailbox = $this->imap->select($name);
////find Mailbox in db
//				$mailbox = Mailbox::load($imapMailbox['uidvalidity']);
//				if (empty($mailbox)) {
//					$mailbox = $this->createMailbox();
//					$mailbox->id = $imapMailbox['uidvalidity'];
//				}
//
//				$mailbox->name = $name;
//				$mailbox->setFlags($params['flags']);
//				$mailbox->delimiter = $params['delim'];
//
//				if (in_array('\\NoSelect', $params['flags'])) {
//					continue;
//				}
//
//				$mailbox->save();
//				if ($this->syncMailbox($mailbox, $imapMailbox['highestmodseq'])) {
//					$this->highestUID = $imapMailbox['uidnext'] - 1;
//					$mailbox->emailHighestModSeq = $imapMailbox['highestmodseq'];
//					$mailbox->save();
//				}
//			}
//			server()->db()->commit();
//		} catch (\Exception $e) {
//			server()->db()->rollback();
//			echo $e->getMessage();
//			echo $e->getTraceAsString();
//		}
//	}



	public function downloadAttachment($uid, $partId) {
		$data = $this->imap->fetch(["BODY[$partId]", "BODY[$partId.MIME]"], $uid, null, true);

		$decoder = new \Mail_mimeDecode($data["BODY[$partId.MIME]"].$data["BODY[$partId]"]);
		$decoded = $decoder->decode(['include_bodies'=>true, 'decode_bodies'=>true]);
		return (object)[
			'type' => $decoded->ctype_primary.'/'.$decoded->ctype_secondary,
			'body' => $decoded->body,
			'disposition' => $decoded->disposition,
			'filename' => $decoded->d_parameters['filename']
		];
	}

//	private function syncMailbox($mailbox, $modseq) {
//		$data = [];
//		$success = true;
//
//		if (true || empty($mailbox->highestUID)) { // fetch all
//			$mailbox->highestUID = 1;
//			$data = $this->imap->fetch(['FLAGS', 'RFC822.HEADER', 'RFC822.SIZE'], $mailbox->highestUID, INF);
//		} else if (!empty($this->highestModSeq()) && $mailbox->highestModSeq() < $modseq) { // we'll QRESYNC
//			$uids = $this->imap->qresync($mailbox->name, $mailbox->uid(), $mailbox->highestModSeq());
//			$data = $this->imap->fetch('FLAGS BODY[HEADER.FIELDS (SUBJECT DATE FROM)]', $uids, INF);
//		}
//
//		foreach ($data as $uid => $item) {
//			var_dump($item);
//			$email = new Email(); // TODO could be not new
//			$email->fromImap($item);
//			$email->addToMailbox($mailbox); // many many
//			$email->save(); // todo sync error?
//		}
//		return $success;
//	}

}
