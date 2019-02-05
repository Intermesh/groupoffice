<?php

namespace go\core\imap;

use DateTime;
use Exception;
use go\core\data\Model;
use go\core\mail\Recipient;


/**
 * Message object
 * 
 * Represents an IMAP message
 *
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Message extends Model {

	const XPRIORITY_HIGH = 1;
	const XPRIORITY_NORMAL = 3;
	CONST XPRIORITY_LOW = 5;

	/**
	 *
	 * @var Mailbox 
	 */
	public $mailbox;

	/**
	 * UID on IMAP server
	 * 
	 * @var string 
	 */
	public $uid;

	/**
	 * Flags
	 * 
	 * eg. \Seen \Recent $Forwarded
	 * 
	 * @var array 
	 */
	public $flags;

	/**
	 * The date it arrived on the server
	 * 
	 * @var DateTime 
	 */
	public $internaldate;

	/**
	 * Size in bytes
	 * 
	 * @var int 
	 */
	public $size;

	/**
	 * The time from the Date header field.
	 * 
	 * @var DateTime 
	 */
	public $date;

	/**
	 * The from address
	 * 
	 * @var Recipient 
	 */
	public $from;

	/**
	 * The Subject
	 * 
	 * @var string 
	 */
	public $subject;

	/**
	 * The to recipients
	 * 
	 * @var Recipient[] 
	 */
	public $to;

	/**
	 * The cc recipients
	 * 
	 * @var Recipient[] 
	 */
	public $cc;

	/**
	 * The bcc recipients
	 * 
	 * @var Recipient[] 
	 */
	public $bcc;

	/**
	 * The to recipients
	 * 
	 * @var Recipient[] 
	 */
	public $replyTo;

	/**
	 * Content type header
	 * 
	 * eg. text/plain; charset=utf-8
	 * 
	 * @var string 
	 */
	public $contentType;

	/**
	 * Message-ID header
	 * 
	 * eg. "8f803852b52b786691c667ed2976e62e@intermesh.group-office.com"
	 * 
	 * Note: The angle bracket pair (<>) is stripped off
	 * 
	 * @var string 
	 */
	public $messageId;
	
	/**
	 * List of message ID's
	 * 
	 * eg.
	 * ``````````````````````````````````````````````````````````````
	 * [
	 *	"8f803852b52b786691c667ed2976e62e@intermesh.group-office.com",
	 *	"8f803852b52b786691c667ed2976e62e@intermesh.group-office.com"
	 * ]
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * Note: The angle bracket pair (<>) is stripped off
	 * 
	 * @var string[] 
	 */
	public $references;
	
	/**
	 * In-Reply-To header
	 * 
	 * eg. "8f803852b52b786691c667ed2976e62e@intermesh.group-office.com"
	 * 
	 * Note: The angle bracket pair (<>) is stripped off
	 * 
	 * @var string 
	 */
	public $inReplyTo;

	/**
	 * X Priority header
	 * 
	 * 
	 * 
	 * @var string 
	 */
	public $xPriority = self::XPRIORITY_NORMAL;

	/**
	 * Array of thread UIDs,
	 * @var array 
	 */
	public $thread;

//	public $contentTransferEncoding;

	/**
	 * Send a notification to this address
	 * 
	 * @var Recipient 
	 */
	public $dispositionNotificationTo;	
	
	private $structure;
	private $body;
	private $quote;

	/**
	 * The structure of the message
	 * @var string 
	 */
	private $bodyStructureStr;

	public function __construct(Mailbox $mailbox, $uid) {
		parent::__construct();

		$this->mailbox = $mailbox;
		$this->uid = (int) $uid;
	}
	
	/**
	 * Find a message by uid
	 * 
	 * @param \IFW\Db\Connection $connection
	 * @param string $mailboxName
	 * @param int $uid
	 * @return self
	 */
	public static function findByUid(\IFW\Db\Connection $connection, $mailboxName, $uid) {
		$mailbox = Mailbox::findByName($connection, $mailboxName);
		return $mailbox->getMessage($uid);
	}
	
	/**
	 * 
	 * @param string $str
	 */
	public function setBodyStructureStr($str){
		$this->bodyStructureStr = $str;
	}	
	
	private function getBodyStructureStr() {
		
		//$this->_bodyStructureStr may also have been set by _parseFetchResponse
		if(!isset($this->bodyStructureStr)){
		
			$conn = $this->mailbox->connection;

			if(!$this->mailbox->selected) {
				$this->mailbox->select();
			}

			$command = "UID FETCH " . $this->uid . " BODYSTRUCTURE";
			$conn->sendCommand($command);
			$response = $conn->getResponse();

			if(!isset($response['data'][0][0])){
//				throw new \Exception("No structure returned");
				return false;
			}

			$this->bodyStructureStr = $response['data'][0][0];
		}

		return $this->bodyStructureStr;
		
	}

	/**
	 * Get's the message structure object
	 * 
	 * @return Structure
	 */
	public function getStructure() {
		
		if (!isset($this->structure)) {
			$struct =  $this->getBodyStructureStr();
			
			if(!$struct) {
				return false;
			}
			$this->structure = new Structure($this, $struct);
		}

		return $this->structure;
	}

	/**
	 * Get the full MIME source
	 * 
	 * @param resource $filePointer
	 * @param string
	 * @throws Exception
	 */
	public function getSource($filePointer = null){		
		
		if(isset($filePointer)){
			
			if(!is_resource($filePointer)){
				throw new Exception("Invalid file pointer given");
			}
			
			$streamer = new Streamer($filePointer);
		}else
		{
			$streamer = null;
		}		
		
		$str = $this->fetchPartData("HEADER", true, $streamer);		
		$str .= $this->fetchPartData("TEXT", true, $streamer);
		
		return $str;		
	}
	
	/**
	 * Get's the body as string.
	 * 
	 * It also appends all inline attachments that are not found in the body by content-id.
	 * 
	 * @see https://msdn.microsoft.com/en-us/library/gg672007%28v=exchg.80%29.aspx
	 * @param Part[] $parts
	 * @param string
	 */
	private function findBody(array $parts, $asHtml=true) {		
		
		$part = array_shift($parts);

		if(!$part) {
			return "";
		}	
	
		switch($part->getContentType()) {
			
			case 'text/html':
			case 'text/plain':
				
				if($part instanceof MultiPart) {
					throw new \Exception("MultiPart with text??? ".var_export($part, true));
				}
				
				if($asHtml) {
					$body = $part->toHtml();

					//append all inline attachments that do not occur in the body
					foreach($parts as $part) {						
						if(method_exists($part, 'toHtml') &&  $part->disposition != 'attachment' && (empty($part->id) || strpos($body, $part->id)===false)) {
							$partStr = $part->toHtml();
							if($partStr) {
								$body .= $partStr;
							}
						}
					}
				}  else {
					return $part->toText();
				}
				
//				var_dump($body);
				return $body;				
				
			case  'multipart/alternative':							
				
				//filter out alternative body we don't want				
				$discard = $asHtml ? 'text/plain' : 'text/html';				
				$filteredParts = [];
				$found = false;
				foreach($part->parts as $part) {
					if($found || $part->getContentType() != $discard) {
						$filteredParts[] = $part;
					}else
					{
						$found = true;
					}
				}
				
				return $this->findBody($filteredParts, $asHtml);
				
			case  'multipart/related':
			case  'multipart/mixed':
			default:
				if(!empty($part->parts)) {
					return $this->findBody($part->parts);
				}else
				{
					return "";
				}
			
		}
	}

	/**
	 * Returns body in HTML
	 * 
	 * @param string
	 */
	public function getBody($asHtml = true) {

		if (!isset($this->body)) {
			
//			var_dump($this->getStructure()->toArray());
			if (empty($this->getStructure()->parts)) {
				GO()->debug("No body parts found in message!");
				return false;
			}			
	
			$this->body = $this->findBody($this->getStructure()->parts,$asHtml);
			
		}
		
		return $this->body;
	}

	/**
	 * Get attachment parts
	 * 
	 * @return SinglePart[]
	 */
	public function getAttachments() {

		$attachments = $this->getStructure()->findPartsBy(function(Part $part){
			return !empty($part->getFilename());
		});

//		if ($this->getStructure()->parts[0]->subtype == 'alternative') {
//			return [];
//		}
//
//		if (count($this->getStructure()->parts) == 1 && $this->getStructure()->parts[0]->type == 'multipart') {
//			$parts = $this->getStructure()->parts[0]->parts;
//		} else {
//			$parts = $this->getStructure()->parts;
//		}
//
//		foreach ($parts as $part) {
//			if ($part->partNumber != "1" && $part->type != "multipart") {
//				$attachments[] = $part;
//			}
//		}

		return $attachments;
	}

	/**
	 * True if message is answered
	 * 
	 * @return boolean
	 */
	public function getAnswered() {
		return in_array('\\Answered', $this->flags);
	}
	
	/**
	 * Set this message as answered
	 * 
	 * @param boolean $value
	 */
	public function setAnswered($value) {
		$this->mailbox->setFlags($this->uid, ['\\Answered'], !$value);
	}

	/**
	 * True if message is forwarded
	 * 
	 * @return boolean
	 */
	public function getForwarded() {
		return in_array('$Forwarded', $this->flags);
	}
	
	/**
	 * Set forwarded flag
	 * 
	 * @param boolean $value
	 */
	public function setForwarded($value) {
		$this->mailbox->setFlags($this->uid, ['$Forwarded'], !$value);
	}

	/**
	 * True if message is viewed
	 * 
	 * @return boolean
	 */
	public function getSeen() {
		return in_array('\Seen', $this->flags);
	}
	
	/**
	 * Set the seen flag
	 * 
	 * @param boolean $value
	 */
	public function setSeen($value) {
		$this->mailbox->setFlags($this->uid, ['\\Seen'], !$value);
	}
	
	/**
	 * True if message is flagged
	 * 
	 * @return boolean
	 */
	public function getFlagged() {
		return in_array('\Flagged', $this->flags);
	}
	
	/**
	 * Set the "\Flagged" flag
	 * 
	 * @param boolean $value
	 */
	public function setFlagged($value) {
		$this->mailbox->setFlags($this->uid, ['\\Flagged'], !$value);
	}
	
	/**
	 * True if message is marked deleted
	 * 
	 * @return boolean
	 */
	public function getDeleted() {
		return in_array('\Deleted', $this->flags);
	}
	
	
	/**
	 * Get the data of a body part
	 * 
	 * @param boolean $peek Don't mark message as read
	 * @param Streamer $streamer
	 * @param string|boolean Returns boolean if streamer is given and operation was successful
	 */
	public function fetchPartData($partNumber, $peek = true, Streamer $streamer = null) {
		
		$peek_str = $peek ? '.PEEK' : '';

		$command = "UID FETCH " . $this->uid . " BODY" . $peek_str . "[" . $partNumber. "]";

		$conn = $this->mailbox->connection;		

		$conn->sendCommand($command);
		$response = $conn->getResponse($streamer);
		
		if(!$response['success']){
			throw new Exception("Could not fetch data: ".$conn->lastCommandStatus);
		}
		
		if(!preg_match('/BODY\[[^\]]+\] "(.*)"/s', $response['data'][0][0], $matches)){				
			
			//throw new \Exception("Invalid data from IMAP");
			return null;
		}		
		return $matches[1];	
	}
}
