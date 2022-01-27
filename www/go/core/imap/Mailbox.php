<?php

namespace go\core\imap;

use DateTimeZone;
use Exception;
use go\core\data\Model;
use go\core\mail\RecipientList;
use go\core\util\StringUtil;
use Swift_ByteStream_FileByteStream;
use Swift_Message;


/**
 * Mailbox object
 * 
 * Handles all mailbox related IMAP functions
 * 
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * //Get root folders
 * $mailbox = new \go\core\imap\Mailbox($connection);

  $mailboxes = $mailbox->getChildren();

  foreach($mailboxes as $mailbox){
  $response['mailboxes'][] = [
  'name' => $mailbox->name,
  'unseenCount' => $mailbox->getUnseenCount(),
  'messagesCount' => $mailbox->getMessagesCount(),
  'uidnext' => $mailbox->getUidnext()
  ];
  }
 * 
 * 
 * //Fetch messages
 * 
 * $mailbox = Mailbox::findByName($connection, "INBOX");

  $messages = $mailbox->getMessages('DATE', true,1,0);

  foreach($messages as $message){
  $response['data'] = $message->toArray('subject,from,to,body,attachments');
 * 	}
 * 
 * ```````````````````````````````````````````````````````````````````````````
 *
 * @property boolean $noSelect Mailbox is virtual and can't be selected
 * @property-read Connection $connection
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Mailbox extends Model {
	
	private $flags = [];
	private $unseenCount;
	private $highestModSeq;
	private $uidValidity;
	private $messagesCount;
	private $recent;
	private $uidNext;
	

	/**
	 *
	 * @var Connection 
	 */
	private $connection;
	
	/**
	 * Name of the mailbox
	 * 
	 * @var string 
	 */
	private $name;
	
	/**
	 * Reference. In most cases this is a namespace.
	 * 
	 * @var string 
	 */
	private $reference = "";
	
	/**
	 * Mailbox delimiter
	 * 
	 * Usually "/" or "."
	 * 
	 * @var string 
	 */
	private $delimiter;
	
	/**
	 * Attributes that should be mime decoded
	 * @var string[] 
	 */
	private static $mimeDecodeAttributes = ['subject'];
	
	
	/**
	 * Constructor
	 * 
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection) {
		parent::__construct();

		$this->connection = $connection;
	}
	
	protected function getConnection(){
		return $this->connection;
	}
	
	/**
	 * Name of the mailbox
	 * 
	 * @var string 
	 */
	public function getName(){
		return $this->name;
	}
	
	
	
	/**
	 * Renames the mailbox 
	 * 
	 * @param string $newName
	 * @return boolean
	 * @throws Exception
	 */
	public function setName($newName){
		$command = 'RENAME "' . Utils::escape(Utils::utf7Encode($this->name)) . '" "' . Utils::escape(Utils::utf7Encode($newName)) . '"';

		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();
		
		if(!$response['success']) {
			throw new \Exception($response['status']);
		}
		
		return true;
	}

	/**
	 * Reference. In most cases this is a namespace.
	 * 
	 * @var string 
	 */
	protected function getReference(){
		return $this->reference;
	}

	
	/**
	 * Mailbox delimiter
	 * 
	 * Usually "/" or "."
	 * 
	 * @var string 
	 */
	protected function getDelimiter(){
		return $this->delimiter;
	}

	
	/**
	 * Array of mailbox flags
	 * 
	 * They are all lower case and with a leading slash eg.:
	 * 
	 * noinferiors, haschildren, noselect, hasnochildren
	 * 
	 * @var string[]
	 */
	protected function getFlags(){
		return $this->flags;
	}


	/**
	 * True if this mailbox was selected using the SELECT mailbox command
	 * 
	 * @var boolean 
	 */
	public function getSelected() {
		return $this->connection->getSelectedMailbox() == $this->name;
	}

	

	public function __toString() {
		return $this->name;
	}

	/**
	 * Finds a mailbox by name
	 * 
	 * eg. 
	 * 
	 * $mailbox = Mailbox::findByName($conn, "INBOX");
	 * 
	 * @param string $name
	 * @param string $reference
	 * @return Mailbox|boolean
	 */
	public static function findByName(Connection $connection, $name, $reference = "") {

		if (!$connection->isAuthenticated()) {
			$connection->authenticate();
		}

		$cmd = 'LIST "' . Utils::escape(Utils::utf7Encode($reference)) . '" "' . Utils::escape(Utils::utf7Encode($name)) . '"';

		$connection->sendCommand($cmd);

		$response = $connection->getResponse();

		if ($response['success']) {

			if (!isset($response['data'][0][0])) {
				return false;
			}

			return self::createFromImapListResponse($connection, $response['data'][0][0]);
		} else {
			return false;
		}
	}

	/**
	 * Not used directly.
	 * 
	 * When you use a find command this function is used to create mailboxes from
	 * the IMAP response.
	 * 
	 * @param Connection $connection
	 * @param string $responseLine
	 * @return Mailbox
	 */
	private static function createFromImapListResponse(Connection $connection, $responseLine) {

		//eg. "* LIST (\HasNoChildren) "/" Trash"
		// * LSUB () "." "Ongewenste e-mail"

		$lineParts = str_getcsv($responseLine, ' ', '"');

//		var_dump($lineParts);

		$mailbox = new Mailbox($connection);

		$mailbox->name = array_pop($lineParts);
		$mailbox->delimiter = array_pop($lineParts);

		while ($part = array_pop($lineParts)) {

			$flag = strtolower(trim($part, '\()'));
			if ($flag == 'list' || $flag == 'lsub') {
				break;
			}
			if (!empty($flag)) {
				$mailbox->flags[] = $flag;
			}
		}

		return $mailbox;
	}

	/**
	 * Get the mailbox status
	 * 
	 * Only messages and unseen are feteched
	 * 
	 * @link https://tools.ietf.org/html/rfc3501#section-6.3.10
	 * @link https://tools.ietf.org/html/rfc4551#section-3.6
	 * 
	 * @return array eg. ['messages'=> 1, 'unseen' => 1]
	 * @throws Exception
	 */
	public function getStatus($props = ['messages', 'uidnext', 'unseen','highestmodseq']) {

//		if(!isset($this->_status)){

		if ($this->selected) {
			$this->connection->unselectMailbox();
		}
		
		$props = array_map("strtoupper", $props);

		$cmd = 'STATUS "' . Utils::escape(Utils::utf7Encode($this->name)) . '" (' . implode(' ', $props) . ')';

		$this->connection->sendCommand($cmd);

		$response = $this->connection->getResponse();

		if (!$response['success']) {
			return false;
		}

		//remove mailbox name
		$str = str_replace(Utils::escape(Utils::utf7Encode($this->name)), '', $response['data'][0][0]);
		$parts = explode(' ', $str);

		$status = ['mailbox' => $parts[1]];

//			var_dump($parts);

		for ($i = 3, $c = count($parts); $i < $c; $i++) {

			$name = trim($parts[$i], ' ()');

			$i++;
			
			if(!$parts[$i]) {
				
				throw new \Exception("Expected another part in the array: ".var_export($parts, true));
			}

			$value = trim($parts[$i], ' ()');

			$status[strtolower($name)] = intval($value);
		}


//		}

		return $status;
	}

	/**
	 * Get the UID validity
	 * 
	 * {@see http://tools.ietf.org/html/rfc4551}
	 * 
	 * @return int
	 */
	public function getUidValidity() {
		if (!isset($this->uidValidity)) {
			$this->select();
		}

		return $this->uidValidity;
	}

	/**
	 * Get number of recent messages
	 * 
	 * 
	 * @return int
	 */
	public function getRecent() {
		if (!isset($this->recent)) {
			$this->select();
		}

		return $this->recent;
	}

	/**
	 * Get the highest mod sequence
	 * 
	 * {@see http://tools.ietf.org/html/rfc4551}
	 * 
	 * @todo Implement examine?
	 * @return int
	 */
	public function getHighestModSeq() {
		if (!isset($this->highestModSeq)) {
			$this->select();
		}

		return $this->highestModSeq;
	}

	/**
	 * Get the number of unseen messages
	 * 
	 * @return int
	 */
	public function getUnseenCount() {

		if (!isset($this->unseenCount)) {
			$status = $this->getStatus(['UNSEEN']);

			$this->unseenCount = $status['UNSEEN'];
		}

		return $this->unseenCount;
	}

	/**
	 * Get the next uid for the mailbox
	 * 
	 * @return int
	 */
	public function getUidnext() {
		if (!isset($this->uidNext)) {
			$status = $this->getStatus(['UIDNEXT']);

			$this->uidNext = $status['UIDNEXT'];
		}

		return $this->uidNext;
	}

	/**
	 * Get the number of messages
	 * 
	 * @return int
	 */
	public function getMessagesCount() {

		if (!isset($this->messagesCount)) {

			if (!$this->noSelect) {
				$status = $this->getStatus(['MESSAGES']);
				
//				if(!isset($status['MESSAGES'])) {
//					throw new \Exception("Expected MESSSAGES in status. ".var_export($status, true));
//				}

				$this->messagesCount = $status ? $status['messages'] : 0;
			} else {
				$this->messagesCount = 0;
			}
		}

		return $this->messagesCount;
	}

	/**
	 * Returns true if this mailbox can't be selected
	 * 
	 * Unselectable folders can be namespaces for example.
	 * 
	 * @return boolean
	 */
	public function getNoSelect() {
		return in_array('noselect', $this->flags);
	}

	/**
	 * Get children mailboxes	  
	 * 
	 * @param bool $subscribedOnly	
	 * @return Mailbox[]
	 */
	public function getChildren($subscribedOnly = true) {


		$listCmd = $subscribedOnly ? 'LSUB' : 'LIST';

//		if($listSubscribed && $this->has_capability("LIST-EXTENDED"))
//		$listCmd = "LIST (SUBSCRIBED)";
//			$listCmd = "LIST";

		$pattern = !isset($this->name) ? '%' : Utils::escape(Utils::utf7Encode($this->name . $this->delimiter)) . '%';


		if (!$this->connection->isAuthenticated()) {
			$this->connection->authenticate();
		}

		$cmd = $listCmd . ' "' . Utils::escape(Utils::utf7Encode($this->reference)) . '" "' . $pattern . '"';		
		$this->connection->sendCommand($cmd);

		$response = $this->connection->getResponse();

		$mailboxes = [];
		while ($responseLine = array_shift($response['data'])) {
			$mailboxes[] = self::createFromImapListResponse($this->connection, $responseLine[0]);
		}

		return $mailboxes;
	}
	
	
	public static function getAll($subscribedOnly = true) {
		
	}

	/**
	 * Return a nested array of UID by thread
	 * @return array
	 * 
	 * eg [ 1 => [], 2 => [3=>[],4=>[]]]
	 * 
	 */
	public function threadSort() {
		

		//REFERENCES of ORDERED SUBJECT
		//A4 UID THREAD REFERENCES UTF-8 ALL\r\n'",
		// * THREAD ((3)(9)(13 14)(15))(2)(1)((4)(5)(7))(6)(8)(10)(11)(12)(16 (17 19)(18))\r\n'",
		//A4 OK Thread completed.\r\n'",


		if (!$this->selected) {
			$this->select();
		}

		$command = 'UID THREAD REFERENCES UTF-8 ALL';


		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();


		$str = substr($response['data'][0][0], 9); //Take off THREAD

		return $this->parseThreadResponse($str);
	}

	private $threadUidIndex = [];

	/**
	 * Parses: 

	  (11)(12)(16(17 19)(18))(20)

	  Into:

	 * array(4) {
	  [11]=>
	  array(0) {
	  }
	  [12]=>
	  array(0) {
	  }
	  [16]=>
	  array(2) {
	  [17]=>
	  array(2) {
	  [19]=>
	  array(1) {
	  [22]=>
	  array(0) {
	  }
	  }
	  [21]=>
	  array(0) {
	  }
	  }
	  [18]=>
	  array(0) {
	  }
	  }
	  [20]=>
	  array(0) {
	  }
	  }
	 * 
	 * @param type $str
	 * @param $highestLevelUid Every thread group has the higherst level UID as group ID. And index of every uid to this group is set in _threadIndex
	 * @return type
	 */
	private function parseThreadResponse($str, $recursive = false, $highestLevelUid = false) {


		$str = str_replace(array(' )', ' ) ', ')', ' (', ' ( ', '( '), array(')', ')', ')', '(', '(', '('), $str);
//		var_dump($str);
//		$str = substr($str, 8); //Take off THREAD

		$tokens = str_split($str);

//		var_dump($tokens);

		$threads = [];

		$level = 0;


		$closeLevels = 0;

		while ($token = array_shift($tokens)) {
			echo $token . "\n";

			switch ($token) {


				case ' ':
					$closeLevels++;


				case '(':
					$level++;

//					echo "New level $level\n";


					if ($level == 1) {
						$threadUid = "";
						while (($token = array_shift($tokens)) !== false && (is_numeric($token) /* || $token ==' ' */)) {

//							echo 'T:'.$token.'-';

							$threadUid .= $token;
						}

//						var_dump($threadUid);

						if (!empty($threadUid)) {

							$threads[$threadUid] = [];



							$this->threadUidIndex[$threadUid] = $highestLevelUid ? $highestLevelUid : $threadUid;
//							$this->_threadUidIndex[$threadUid]
//						echo "New thread: ".$threadUid."\n";
						} else {
							//(11)(12)(16(17(19 22 23)(21))(18))(20)((24)(25))
							//this happens with 24 and 25. They are grouped without a parent.							

							$level--;
						}

						array_unshift($tokens, $token);

						$subStr = "";
					} else {
						$subStr .= $token;
					}

					break;

				case ')':

					$level -= $closeLevels;

					$closeLevels = 0;

					$level--;
					if ($level == -1) {
						//happens with extra ()
						//((1)(9)(10(11)(12))(13)(14))(2)(3)(4)((5)(6(32)(33)))((7)(8))(34)(15 16 17(18 19(21 25 26 27(28)(31))(24))(20))(22)(23)((29)(30))
						//happens on (2) here.
						$level = 0;
					}

//					echo "New level $level\n";					

					if (isset($subStr)) {
						if ($level == 0) {
							$subStr .= $token;

							if (!$recursive) {

//								var_dump($subStr);
								preg_match_all('/\d+/', $subStr, $matches);

								$threads[$threadUid] = array_merge([$threadUid], $matches[0]);
								foreach ($threads[$threadUid] as $uid) {
									$this->threadUidIndex[$uid] = $threadUid;
								}

								rsort($threads[$threadUid]);
							} else {
								$threads[$threadUid] = $this->parseThreadResponse($subStr, $highestLevelUid ? $highestLevelUid : $threadUid);
							}
						} else {
							$subStr .= $token;
						}
					}

					break;


				default:

//					echo "Add to sub\n";

					$subStr .= $token;
					break;
			}
		}
		return $threads;
	}

	/**
	 * Get messages from this mailbox
	 * 
	 * @return Message[]
	 */
	public function getMessages($sort = 'DATE', $reverse = true, $threaded = false, $limit = 10, $offset = 0, $filter = 'ALL', $returnProperties = "") {
		$uids = $this->serverSideSort($sort, $reverse, $filter);
//		var_dump($uids);

		if (!count($uids)) {
			return array();
		}

		//even though the uid list is sorted UID FETCH does not return it sorted.
		$uidIndex = array_flip($uids);


		if ($limit > 0) {

			if (!$threaded) {
				$uids = array_slice($uids, $offset, $limit);
			} else {
				$threads = $this->threadSort();

//				var_dump($threads);
//				var_dump($this->_threadUidIndex);
				//A single UID of each unique thread
				$threadUids = [];
				$count = 0;
				$max = $offset + $limit; //stop if we have enough.
				foreach ($uids as $uid) {
					$threadUid = $this->threadUidIndex[$uid];
					if (!isset($threadUids[$threadUid])) {
						$threadUids[$threadUid] = $uid;
						$count++;

						if ($count == $max) {
							break;
						}
					}
				}
				$uids = array_slice(array_values($threadUids), 0, $limit);
			}
		}
		$messages = $this->getMessagesUnsorted($uids, $returnProperties);
		$sorted = [];
		while ($message = array_shift($messages)) {

			if (isset($threads)) {
				$message->thread = $threads[$this->threadUidIndex[$message->uid]];
			}

			$sorted[$uidIndex[$message->uid]] = $message;
		}
		ksort($sorted);

		return $sorted;
	}

	/**
	 * Get message objects without sorting
	 * 
	 * @param array|StringUtil $uidSequence array of uids or sequence like 1:*
	 * @param $returnProperties "SUBJECT", "FROM", "DATE", "CONTENT-TYPE", "X-PRIORITY", "TO", "CC", "BCC", "REPLY-TO", "DISPOSITION-NOTIFICATION-TO", "MESSAGE-ID", "REFERENCES", "IN-REPLY-TO"
	 * @param int $changedSinceModSeq Modsequence to get only changed messages
	 * @param int $changedSinceModSeq Modsequence to get only changed messages
	 * @return Message[]
	 */
	public function getMessagesUnsorted($uidSequence, $returnProperties = "", $changedSinceModSeq = null) {

		if (empty($uidSequence)) {
			return [];
		}

		if (is_array($uidSequence)) {
			$uidSequence = implode(',', $uidSequence);
		}

		$responses = $this->getMessageHeaders($uidSequence, $returnProperties, $changedSinceModSeq);

		$messages = [];

		while ($response = array_shift($responses)) {

			if (substr($response[0], 0, 4) == '* OK') {
				//not a message. but when changedSince is given it may reply with:
				//* OK [HIGHESTMODSEQ 1] Highest
			} else {
				$messages[] = $this->createMessageFromImapResponse($response[0]);
			}
		}

		return $messages;
	}
	
	
	/**
	 * 
	 * @param Mailbox $this
	 * @param array $response
	 * @return Message
	 */
	private function createMessageFromImapResponse($data) {
		
		
		/*
		 * "* 5803 FETCH (UID 5803 FLAGS (\Seen) INTERNALDATE "29-Oct-2010 19:35:58 +0200" RFC822.SIZE 160930 BODYSTRUCTURE (((("text" "plain" ("charset" "iso-8859-1") NIL NIL "quoted-printable" 1943 71 NIL NIL NIL NIL)("text" "html" ("charset" "iso-8859-1") NIL NIL "quoted-printable" 7882 240 NIL NIL NIL NIL) "alternative" ("boundary" "Boundary_(ID_wiFdZdlPJCz29PTdAdSf0w)") NIL NIL NIL)("image" "jpeg" ("name" "image001.jpg") "<pub_res_bestanden/image001.jpg>" "pub_res_bestanden/image001.jpg" "base64" 6126 NIL ("attachment" ("filename" "image001.jpg")) NIL "image001.jpg") "related" ("type" "multipart/alternative" "boundary" "Boundary_(ID_mBWaS1C822o4BLUHPZtcVg)") NIL NIL NIL)("text" "html" ("name" "Kandidatenoverzicht_ReoPlus_nov10.htm" "charset" "us-ascii") NIL I:\Frontoffice_ReoPlus\mailing\kandidatenlijst aan detabureaus\Kandidatenoverzicht_ReoPlus_nov10.htm "base64" 142170 1823 NIL ("attachment" ("filename" "Kandidatenoverzicht_ReoPlus_nov10.htm")) NIL NIL) "mixed" ("boundary" "Boundary_(ID_OF/cBsTfVK4gbVsbFd1O1Q)") NIL NIL NIL) BODY[HEADER.FIELDS (SUBJECT FROM DATE CONTENT-TYPE X-PRIORITY TO CC BCC REPLY-TO DISPOSITION-NOTIFICATION-TO MESSAGE-ID REFERENCES IN-REPLY-TO)] Date: Fri, 29 Oct 2010 16:19:22 +0200
From: Info@example.com
Subject: Test nov-10
To: mschering@intermesh.nl
Message-id: <78CD4FC49F907A4A887B046CC215385E566BDA@test.nl>
Content-type: multipart/mixed; boundary="Boundary_(ID_OF/cBsTfVK4gbVsbFd1O1Q)"
		 */
		//headers will be splitted off here
		
		$start = strpos($data, 'UID');
		$end = strpos($data, 'BODY['); //match BODY[HEADER.FIELDS or BODYSTRUCTURE because they need different parsing
		
		if($end === false) {
			$end = strlen($data);
		}
		
		$fetchResponse = trim(substr($data, $start, $end - $start - 1));
		
		$attr = $this->parseFetchResponse($fetchResponse);//, self::_parseHeaders($headerStr));
		
		if(preg_match('/BODY\[[^\]]+\] "(.*)"/s', $data, $matches)) {
			$attr = array_merge($attr, $this->parseHeaders(trim($matches[1])));
		}

//		$attr = array_merge(
//				self::_parseFetchResponse($fetchResponse), self::_parseHeaders($headerStr));

		$message = new Message($this, $attr['uid']);

		unset($attr['uid']);

		foreach ($attr as $prop => $value) {
			if ($message->hasWritableProperty($prop)) {

				if (in_array($prop, self::$mimeDecodeAttributes)) {				
					$value = Utils::mimeHeaderDecode($value);					
				}

				if ($prop == 'to' || $prop == 'cc' || $prop == 'bcc') {
					$list = new RecipientList($value);
					$value = $list->toArray();
				}


				if ($prop == 'from' || $prop == 'replyTo' || $prop == 'displayNotificationTo') {
					$list = new RecipientList($value);
					$value = isset($list[0]) ? $list[0] : null;
				}

				$message->$prop = $value;
			}else
			{
//				trigger_error("Undefined property ".$prop." found in IMAP response for fetch message");
			}
		}
		
		if(!isset($message->date)) {
			//can happen due to parse error
			$message->date = $message->internaldate;
		}
		
		if(isset($message->messageId)){
			//remove non ascii chars. Incredimail sends invalid chars.
			$message->messageId = trim(preg_replace('/[[:^print:]]/', '', $message->messageId),'<> ');
		}
		
		if(isset($message->inReplyTo)){
			//remove non ascii chars. Incredimail sends invalid chars.
			$message->inReplyTo = trim(preg_replace('/[[:^print:]]/', '', $message->inReplyTo),'<> ');
		}
		
		if(isset($message->references)){
			//remove non ascii chars. Incredimail sends invalid chars.
			$message->references = preg_replace('/[[:^print:]]/', '', $message->references);
			
			//remove whitespaces
			$message->references = preg_replace('/[\s,]+/',',',$message->references);
			
			//explode id's
			$message->references = trim(str_replace('><', ',', $message->references),'<> ');
			$message->references = str_replace(',,', ',', $message->references); //wierd stuff:
			
			/*
			 * References: <DUB124-W490C3E1C3A57E495104C6FF34A0@phx.gbl>
			 *  <,<714ec0acc17243a33f40b25f663b03f5@intermesh.group-office.com> <>>
			 *  <DUB124-W4084488AD7C09D18C33FE8F3300@phx.gbl>,<A5CC2BCF-A755-4471-AB58-0BEBECF918D7@intermesh.nl>
			 */			
			
			if(empty($message->references)){
				$message->references = [];
			}else
			{
				$message->references = array_unique(explode(',',$message->references));
			}
		}else
		{
			$message->references = [];
		}

		return $message;
	}
	
	
	
//	private function _extractBodyStructure($line){
//		$startpos = strpos($line, "BODYSTRUCTURE");
//		
//		if($startpos){
//			//We know BODY[ comes next or end. because this is defined in Mailbox::_buildFetchProps
//			$endpos = strpos($line, 'BODY[');
//			
//			if(!$endpos){
//				$endpos = strlen($line)-$startpos;
//			}
//			
//			$this->_bodyStructureStr = substr($line, $startpos, $endpos);
//		}
//	}

	private function parseFetchResponse($response) {

		$attr = [];

		$start = strpos($response, 'UID');
		$end = strpos($response, 'BODY'); //match BODY[HEADER.FIELDS or BODYSTRUCTURE because they need different parsing
		
		if($end === false) {
			$end = strlen($response);
		}else
		{
			$end--; //Not sure about this!
		}
		
		if(strpos($response, 'BODYSTRUCTURE')){
			$attr['bodyStructureStr'] = $response;
		}

		$line = trim(substr($response, $start, $end - $start ));
		
		
		$arr = str_getcsv($line, ' ');


		for ($i = 0, $c = count($arr); $i < $c; $i++) {
			$name = StringUtil::lowerCamelCasify($arr[$i]);
			
			$valueIndex = $i + 1;
			if(!isset($arr[$valueIndex])){
				$value = null;
				
				
				throw new \Exception("Value not found for ".$name);
			}else
			{
				$value = $arr[$valueIndex];
			}
			
			

			if ($name == 'rfc822.size') {
				$name = 'size';

				$value = intval($value);
			}

			if (substr($value, 0, 1) == '(') {
				$values = [];

				do {
					$i++;
					
					if(!isset($arr[$i])) {
						go()->debug("Abnormal structure encountered: ".$response);
						break;
					}

					$values[] = trim($arr[$i], '()');
				} while (substr($arr[$i], -1, 1) != ')' && $i < $c);


				$attr[$name] = $values;
			} else {

				$attr[$name] = $value;

				$i++;
			}
		}
		
		if (isset($attr['internaldate'])) {
			$attr['internaldate'] = new \IFW\Util\DateTime($attr['internaldate']);
			$attr['internaldate']->setTimezone(new DateTimeZone("UTC"));
		}

		return $attr;
	}

	private function parseHeaders($headers) {

		$attr = [];

		$headers = str_replace("\r", "", trim($headers));
		$headers = preg_replace("/\n[\s]/", "", $headers);
		
		if(!empty($headers)){

			$lines = explode("\n", $headers);

			foreach ($lines as $line) {
				$parts = explode(':', $line);

				$name = StringUtil::lowerCamelCasify(array_shift($parts));

				$attr[$name] = trim(implode(':', $parts));
			}

			if (isset($attr['date'])) {
				//sometimes headers contain some extra stuff between ()
				$attr['date']=preg_replace('/\([^\)]*\)/','', $attr['date']);
				//$attr['date'] = gmdate(\IFW\Util\DateTime::FORMAT_API, strtotime($attr['date']));
				try {
					$attr['date'] = new \IFW\Util\DateTime($attr['date']);
					$attr['date']->setTimezone(new DateTimeZone("UTC"));
				} catch(\Exception $e) {
					go()->debug("Failed to parse date: ".$e->getMessage());
					
					unset($attr['date']); //we'll use internaldate later instead
				}
			}

			

			if (isset($attr['xPriority'])) {
				$attr['xPriority'] = intval($attr['xPriority']);
			}
		}

		return $attr;
	}
	
	

//	private function sortUids($uidIndex, $uidsToSort){
//		
//		usort($uidsToSort, function($a, $b) use($uidIndex){
//			return $uidIndex[$b] - $uidIndex[$a];
//		});
//		
//		return $uidsToSort;
//		
//	}

	/**
	 * Fetch a single message
	 * 
	 * @param int $uid
	 * @param boolean $noFetchProps Don't fetch properties from IMAP server. Only the UID will be known to the Message object
	 * @return Message
	 */
	public function getMessage($uid, $noFetchProps = false, $returnProperties = "") {
		$uid = (int) $uid;

		$this->select();

		if (!$noFetchProps) {
			$responses = $this->getMessageHeaders($uid, $returnProperties);

			if (empty($responses[0][0])) {
				return false;
			}

			return $this->createMessageFromImapResponse($responses[0][0]);
		} else {
			return new Message($this, $uid);
		}
	}

	/**
	 * Select this mailbox on the IMAP server
	 * 
	 * @return boolean
	 */
	public function select() {
		if ($this->selected) {
			return true;
		}
		$responses = $this->connection->selectMailbox($this->name);

		if ($responses['success']) {
			foreach ($responses['data'] as $response) {
				//* 13477 EXISTS
				if (preg_match('/\* ([0-9]+) EXISTS/', $response[0], $matches)) {
					$this->messagesCount = (int) $matches[1];
				} elseif (preg_match('/\* ([0-9]+) RECENT/', $response[0], $matches)) {
					$this->recent = (int) $matches[1];
				}
				//			elseif(preg_match('/\* OK \[UNSEEN ([0-9]+)\]/',$response[0], $matches)){
				//				//"* OK [UNSEEN 13338] First unseen."
				//				$this->_status['unseen'] = $matches[1];
				//			}
				elseif (preg_match('/\* OK \[UIDVALIDITY ([0-9]+)\]/', $response[0], $matches)) {
					//"* OK [UNSEEN 13338] First unseen."
					$this->uidValidity = (int) $matches[1];
				} elseif (preg_match('/\* OK \[UIDNEXT ([0-9]+)\]/', $response[0], $matches)) {
					//"* OK [UNSEEN 13338] First unseen."
					$this->uidNext = (int) $matches[1];
				} elseif (preg_match('/\* OK \[HIGHESTMODSEQ ([0-9]+)\]/', $response[0], $matches)) {
					//"* OK [UNSEEN 13338] First unseen."
					$this->highestModSeq = (int) $matches[1];
				}
			}
		}

		return $responses['success'];
	}

	
	/**
	 * Get an array of UIDS sorted by the server.
	 * 
	 * @param string $sort 'DATE", 'ARRIVAL', 'SUBJECT', 'FROM'
	 * @param boolean $reverse
	 * @param string $filter
	 * 
	 * @return array|boolean UID list ['1','2']
	 */
	private function serverSideSort($sort = 'DATE', $reverse = true, $filter = "ALL") {

		if (!$this->selected) {
			if(!$this->select()) {
				return false;
			}
		}

		$command = 'UID SORT (' . $sort . ') UTF-8 ' . $filter;

		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();

		if (!$response['success']) {
			return false;
		}


		$uids = [];

		while ($line = array_shift($response['data'][0])) {
			$str = trim(str_replace('* SORT', '', $line));
			if (!empty($str)) {
				$vals = explode(" ", $str);
				$uids = array_merge($uids, $vals);
			}
		}

		if ($reverse) {
			$uids = array_reverse($uids);
		}

		return $uids;
	}

	/**
	 * Search the mailbox for UID's.
	 * 
	 * @param string $filter
	 * @return boolean
	 */
	public function search($filter = "ALL") {


		if (!$this->selected) {
			if(!$this->select()) {
				return false;
			}
		}

		$command = 'UID SEARCH ' . $filter;


		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();
		
//		var_dump($response);

		if (!$response['success']) {
			return false;
		}


		$uids = [];

		while ($line = array_shift($response['data'][0])) {
			$str = trim(str_replace('* SEARCH', '', $line));
			if (!empty($str)) {
				$vals = explode(" ", $str);
				$uids = array_merge($uids, $vals);
			}
		}
		return $uids;
	}

	private function buildFetchProps($returnProperties = "") {
//		$returnProperties = array_map([$this, '_decamelCasify'], $returnProperties);

		$props = '';

		$availableFields = [
			'FLAGS',
			'INTERNALDATE',
			'SIZE',
			'BODYSTRUCTURE' //Important that this one comes last for parsing in Message model
		];

		foreach ($availableFields as $field) {
			if (empty($returnProperties) || in_array($field, $returnProperties)) {
				$props .= $field . ' ';
			}
		}

		$props = str_replace('SIZE', 'RFC822.SIZE', trim($props));

		$availableBodyProps = ["SUBJECT", "FROM", "DATE", "CONTENT-TYPE", "X-PRIORITY", "TO", "CC", "BCC", "REPLY-TO", "DISPOSITION-NOTIFICATION-TO", "MESSAGE-ID", "REFERENCES", "IN-REPLY-TO"];

		$bodyProps = "";

		foreach ($availableBodyProps as $field) {
			if (empty($returnProperties) || in_array($field, $returnProperties)) {
				$bodyProps .= $field . ' ';
			}
		}

		if (!empty($bodyProps)) {
			$props .= ' BODY.PEEK[HEADER.FIELDS (' . trim($bodyProps) . ')]';
		}


		return $props;
	}

	private function getMessageHeaders($uidSequence, $returnProperties = "", $changedSinceModSeq = null) {

		if (!$this->selected) {
			$this->select();
		}

		$command = 'UID FETCH ' . $uidSequence . ' (' . $this->buildFetchProps($returnProperties) . ')';

		if (isset($changedSinceModSeq)) {
			$command .= ' (CHANGEDSINCE ' . (int) $changedSinceModSeq . ')';
		}

		$this->connection->sendCommand($command);
		$res = $this->connection->getResponse();

		if (!$res['success']) {
			throw new \Exception($this->connection->lastCommandStatus);
		}
		
		return $res['data'];
	}

	/**
	 * @todo
	 */
	public function getAcl() {

		$mailbox = Utils::escape(Utils::utf7Encode($this->name));

		$command = 'GETACL "' . $mailbox . '"';
		$this->connection->sendCommand($command);
//		$response = $this->get_response(false, true);
//
//		$ret = array();
//
//		foreach($response as $line)
//		{
//			if($line[0]=='*' && $line[1]=='ACL' && count($line)>3){
//				for($i=3,$max=count($line);$i<$max;$i+=2){
//					$ret[]=array('identifier'=>$line[$i],'permissions'=>$line[$i+1]);
//				}
//			}
//		}
//
//		return $ret;
	}

	public function setAcl($identifier, $permissions) {

//		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
//		$this->clean($mailbox, 'mailbox');
//
//		$command = "SETACL \"$mailbox\" $identifier $permissions\r\n";
//		//throw new \Exception($command);
//		$this->send_command($command);
//
//		$response = $this->get_response();
//
//		return $this->check_response($response);
	}

	public function deleteAcl($identifier) {
//		$mailbox = $this->utf7_encode($this->_escape( $mailbox));
//		$this->clean($mailbox, 'mailbox');
//
//		$command = "DELETEACL \"$mailbox\" $identifier\r\n";
//		$this->send_command($command);
//		$response = $this->get_response();
//		return $this->check_response($response);
	}

	public function toArray(array $properties = null): array
	{
		return parent::toArray($properties);
	}

	/**
	 * Set or clear flags on messages in this mailbox 
	 * 
	 * Flags can be:
	 *
	 * \Seen
	 * \Answered
	 * \Flagged
	 * \Deleted
	 * $Forwarded
	 * other custom flags
	 *
	 * @param string $uidSequence UID's eg: 1:* OR 1:5 OR 1,2,3
	 * @param array $flags
	 * @param boolean $clear
	 * @return boolean
	 */
	public function setFlags($uidSequence, array $flags, $clear = false) {

		$this->select();

		$uidSequence = $this->parseUidSequence($uidSequence);

		$sign = $clear ? '-' : '+';

		$command = "UID STORE " . $uidSequence . " " . $sign . "FLAGS.SILENT (";

		$command .= implode(' ', $flags);

		$command .= ")";

		$this->connection->sendCommand($command);
		
		$response = $this->connection->getResponse();

		return $response['success'];
	}

	/**
	 * Expunge the mailbox
	 * 
	 * Will delete all messages in the mailbox with the \Deleted flag set.
	 * 
	 * @return boolean
	 */
	public function expunge() {
		$this->connection->sendCommand("EXPUNGE");
		$response = $this->connection->getResponse();
		return $response['success'];
	}

	/**
	 * Append a message to a mailbox
	 *
	 * @param Swift_Message $message
	 * @param string $flags {@see setFlags()}
	 * @return boolean
	 */
	public function appendMessage(Swift_Message $message, array $flags = null) {

		$tmpfile = IFW::app()->getAuth()->getTempFolder()->getFile(uniqid(time()));

		$is = new Swift_ByteStream_FileByteStream($tmpfile->getPath(), true);
		$message->toByteStream($is);

		unset($message);
		unset($is);


		if (!$this->appendStart($tmpfile->getSize(), $flags)) {
			return false;
		}

		$fp = fopen($tmpfile->getPath(), 'r');

		while ($line = fgets($fp, 1024)) {
			if (!$this->appendFeed($line)) {
				return false;
			}
		}

		fclose($fp);
		$tmpfile->delete();


		$this->appendFeed("\r\n");

		return $this->appendEnd();
	}

	/**
	 * End's a line by line append operation
	 *
	 * @return boolean
	 */
	private function appendEnd() {
		$result = $this->connection->getResponse();
		
//		\go()->debug($result);
		
		//A96 OK [APPENDUID 1291728129 165] Append completed.
		
		if(preg_match('/\[APPENDUID [0-9]+ ([0-9]+)\]/', $result['status'], $matches)) {
			return (int) $matches[1];
		}else{
			return $result['success'];
		}
	}

	/**
	 * Feed data when after append_start is called to start an append operation
	 *
	 * @param string $str
	 * @return boolean
	 */
	private function appendFeed($str) {
		return $this->connection->fputs($str);
	}

	/**
	 * Start an append operation. Data can be fed line by line with append_feed
	 * after this function is called.
	 * 	 
	 * @param int $size
	 * @param array $flags eg. ["\Seen"]
	 * @return boolean
	 */
	private function appendStart($size, array $flags = null) {
		//Select mailbox first so we can predict the UID.
		$this->select();

		$flags = isset($flags) ? implode(' ',$flags) : '';
		
		$command = 'APPEND "' . Utils::escape(Utils::utf7Encode($this->name)) . '" (' . $flags. ') {' . intval($size) . "}";

		$this->connection->sendCommand($command);

		$result = $this->connection->readLine();

		if (substr($result, 0, 1) == '+') {
			return true;
		} else {
			return false;
		}
	}
	
	private function parseUidSequence($uidSequence) {
		if (is_array($uidSequence)) {
			$uidSequence = implode(',', $uidSequence);
		}
		
		return $uidSequence;
	}
	
	/**
	 * Converts an uid sequence string to an array of sinle UID'
	 * 
	 * eg. 1,2:4,6 becomes [1,2,3,4,6]
	 * 
	 * @param string $uidSequence
	 * @return array
	 */
	private function expandUidSequence($uidSequence) {
		$parts = explode(',', $uidSequence);
		
		$uids = [];
		
		foreach($parts as $part) {
			$startEnd = explode(':', $part);
			
			$start = $startEnd[0];
			$end = isset($startEnd[1]) ? $startEnd[1] : $startEnd[0];
		
			
			for($i = $start; $i<=$end; $i++) {
				$uids[] = $i;
			}
		}
		
		return $uids;
	}
	
	/**
	 * Copy a message from the currently selected mailbox to another mailbox
	 * 
	 * Returns false on failure or uidSequence an array of new UID's with the old
	 * UID's as key. eg. [123 => 3232, 2323 => 32434]
	 * {@link https://tools.ietf.org/html/rfc2359#section-4.3}
	 *
	 * @param string|array $uidSequence
	 * @param string $targetMailbox
	 * @return array|boolean
	 */
	public function copyMessages($uidSequence, $targetMailbox) {
		$uidSequence = $this->parseUidSequence($uidSequence);
		
		$this->select();
	
		$command = "UID COPY $uidSequence \"".Utils::escape(Utils::utf7Encode($targetMailbox))."\"";
		
		$this->connection->sendCommand($command);
		$response = $this->connection->getResponse();
		
		if(!$response['success']) {
			return false;
		}

		//Try to match the new UID sequence
		//"'> A4 UID COPY 92 "Trash"'"
		//"'< A4 OK [COPYUID 1407933079 92 60] Copy completed.'"

		if(preg_match('/\[COPYUID [0-9]+ ([0-9,:]+) ([0-9,:]+)\]/', $response['status'], $matches)){				
			
			$sourceUids = $this->expandUidSequence($matches[1]);
			$targetUids = $this->expandUidSequence($matches[2]);
			
			return  array_combine($sourceUids, $targetUids);
		}
		
		//var_dump($response);
		
		return $response['success'];
	}
	
	
	
	/**
	 * Move a message from the currently selected mailbox to another mailbox
	 * 
	 * Returns false on failure or uidSequence of new messages in target mailbox 
	 * like 3956:3958 or 3956. 
	 * {@link https://tools.ietf.org/html/rfc2359#section-4.3}
	 *
	 * @param string|array $uidSequence
	 * @param string $targetMailbox
	 * @param boolean $expunge
	 * @param string|boolean 
	 */
	public function moveMessages($uidSequence, $targetMailbox, $expunge = true) {
		
		$newUidSequence = $this->copyMessages($uidSequence, $targetMailbox);
		if($newUidSequence === false) {
			return false;
		}

		if(!$this->deleteMessages($uidSequence, $expunge)) {
			return false;
		}
		
		return $newUidSequence;
	}
	
	/**
	 * Delete messages from the currently selected mailbox
	 *
	 * @param string|array $uidSequence
	 * @param boolean $expunge
	 * @return boolean
	 */
	public function deleteMessages($uidSequence, $expunge = true) {
		$status = $this->setFlags($uidSequence, ['\Deleted', '\Seen']);
		if(!$status) {
			return false;
		}

		return !$expunge || $this->expunge();
	}
	
	/**
	 * Subscribe this mailbox
	 * 
	 * @return boolean
	 */
	public function subscribe() {
		$command = 'SUBSCRIBE "' . Utils::escape(Utils::utf7Encode($this->name)) . '"';

		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();
		
		if(!$response['success']) {
			throw new \Exception($response['status']);
		}
		
		return true;
	}
	
	/**
	 * Unsubscribe this mailbox
	 * 
	 * @return boolean
	 */
	public function unsubscribe() {
		$command = 'UNSUBSCRIBE "' . Utils::escape(Utils::utf7Encode($this->name)) . '"';

		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();
		
		if(!$response['success']) {
			throw new \Exception($response['status']);
		}
		
		return true;
	}
	
	/**
	 * Delete this folder
	 * 
	 * @return boolean
	 * @throws Exception
	 */
	public function delete() {
		
		$this->unsubscribe();
		
		$command = 'DELETE "' . Utils::escape(Utils::utf7Encode($this->name)) . '"';

		$this->connection->sendCommand($command);

		$response = $this->connection->getResponse();
		
		if(!$response['success']) {
			throw new \Exception($response['status']);
		}
		
		return true;
	}
}
