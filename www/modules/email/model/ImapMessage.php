<?php

namespace GO\Email\Model;

use go\core\util\DateTime;
use go\core\util\StringUtil;

/**
 * A message from the imap server
 * 
 * @package GO.modules.email
 * 
 * @property \GO\Base\Mail\EmailRecipients $to
 * @property \GO\Base\Mail\EmailRecipients $cc
 * @property \GO\Base\Mail\EmailRecipients $bcc
 * @property \GO\Base\Mail\EmailRecipients $from
 * @property \GO\Base\Mail\EmailRecipients $reply_to
 * @property string $subject
 * @property int $uid
 * @property int $size
 * @property string $internal_date Date received
 * @property string $date Date sent
 * @property int $udate Unix time stamp sent
 * @property int $internal_udate Unix time stamp received
 * @property string $x_priority
 * @property string $message_id
 * @property string $content_type
 * @property array $content_type_attributes
 * @property string $disposition_notification_to
 * @property string $content_transfer_encoding
 * @property string $charset
 * @property bool $seen
 * @property bool $flagged
 * @property bool $answered
 * @property bool $forwarded
 * @property Account $account
 * @property String $mailbox
 */
class ImapMessage extends ComposerMessage {
	
	/**
	 *
	 * @var \GO\Base\Mail\Imap 
	 */
	public $account;
	
	/**
	 * By default the message will be marked as read when fetched.
	 * Set to true to leave it as unseen.
	 * 
	 * @var boolean 
	 */
	public $peek=true;
	
	
	/**
	 * To avoid memory problems we truncate extreme body lengths
	 * @var int 
	 */
	public $maxBodySize=256000;

	private $_cache;
	
	
	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return ImapMessage
	 */
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	/**
	 * Find's messages in a given mailbox
	 * 
	 * @param Account $account
	 * @param StringHelper $mailbox
	 * @param int $start
	 * @param int $limit
	 * @param sring $sortField See constants in \GO\Base\Mail\Imap::SORT_*
	 * @param boolean $descending Sort descending
	 * @param StringHelper $query
	 * @param StringHelper $searchIn In what folder(s) are we searching ('current', 'all', 'recursive')
	 * @return array
	 */
	public function find(Account $account, $mailbox="INBOX", $start=0, $limit=50, $sortField=\GO\Base\Mail\Imap::SORT_DATE , $descending=true, $query='ALL', $searchIn='current'){
		
//		$mailbox = trim($mailbox);
		
		if(empty($mailbox)) {
			$mailbox="INBOX";
		}
		
		$results=array();
		if($searchIn=="all") {
			foreach ($account->getRootMailboxes(false, true) as $mailbox) {
			
				//only search visable mailboxes not subscriptions
				if(!$mailbox->isVisible() || $mailbox->noselect)
					continue;

				$results = array_merge($results, $this->find($account, $mailbox->name, $start, $limit, $sortField, $descending, $query, 'recursive'));
			}
			return $results;
		}
		
		/** @var $imap \GO\Base\Mail\Imap */
		$imap = $account->openImapConnection($mailbox);
		$headersSet = $imap->get_message_headers_set($start, $limit, $sortField , $descending, $query);
		foreach($headersSet as $uid=>$headers){
			$message = ImapMessage::model()->createFromHeaders(
							$account, $mailbox, $headers);	
			
			$results[]=$message;
		}
		\GO::debug($mailbox);
		//find recursive in subfolders
		if($searchIn==='recursive') {
			
			$mailboxObj = new ImapMailbox($account, array('name'=>$mailbox));
			
			$children = $mailboxObj->getChildren(true, false);
			foreach($children as $child) {
				
				//only search visible mailboxes not subscriptions
				if(!$child->isVisible() || $child->noselect)
					continue;

				$results = array_merge($results, $this->find($account, $child->name, $start, $limit, $sortField, $descending, $query, 'recursive'));
			}
		}
		
		return $results;
		
	}
	
	/**
	 * Get an unique messageID
	 * 
	 * @return StringHelper
	 */
	public function getUniqueID(){
		if(empty($this->message_id)){
			
			$from = $this->from->getAddress();	
			return $from["email"].'-'.$this->udate;

		}else{
			return $this->message_id;
		}
	}
	
	/**
	 *
	 * @param Account $account
	 * @param int $uid 
	 */
	public function findByUid(Account $account, $mailbox, $uid) {

		$cacheKey='email:'.$account->id.':'.$mailbox.':'.$uid;
		
		$cachedMessage = isset($this->_cache[$cacheKey]) ? $this->_cache[$cacheKey] : false;//\GO::cache()->get($cacheKey);

		if ($cachedMessage) {

			$imap = $cachedMessage->account->openImapConnection($mailbox);
			return $cachedMessage;
		} else {
			$imapMessage = new ImapMessage();
			$imapMessage->account=$account;
			
			$imap = $account->openImapConnection($mailbox);

			$attributes = $imap->get_message_header($uid, true);

			if (!$attributes)
				return false;

			$attributes['uid']=$uid;
			$attributes['mailbox'] = $mailbox;

			$imapMessage->setAttributes($attributes);

			$this->_cache[$cacheKey]=$imapMessage;
			
			return $imapMessage;
		}		
	}
	
	public function clearMessagesCache(){
		$this->_cache=array();
	}

	
	public function createFromHeaders($account, $mailbox, $headers){
		$imapMessage = new ImapMessage();
		
		$imapMessage->account = $account;
		$headers['mailbox'] = $mailbox;

		$imapMessage->setAttributes($headers);

		return $imapMessage;
	}
	
	public function getAttributes($formatted=false){
		if(!$formatted)
			return $this->attributes;
		
		$attributes = $this->attributes;
		
		$from = $this->from->getAddress();
		$attributes['from']= $from["personal"];
		$attributes['sender'] = $from["email"];
		
		$attributes['to']=(string) $this->to;
		$attributes['cc']=(string) $this->cc;
		$attributes['bcc']=(string) $this->bcc;
		$attributes['reply_to']=(string) $this->reply_to;

		$attributes["date"]=\GO\Base\Util\Date::get_timestamp($this->udate, false);
		$attributes["date_time"]=date(\GO::user()->time_format, $this->udate);

		$attributes["arrival"]=\GO\Base\Util\Date::get_timestamp($this->internal_udate, false);
		$attributes["arrival_time"]=date(\GO::user()->time_format, $this->internal_udate);
		
		return $attributes;
	}

	/**
	 * Save the message source to a file.
	 * 
	 * @param StringHelper $path
	 * @return boolean 
	 */
	public function saveToFile($path) {
		$imap = $this->account->openImapConnection($this->mailbox);

		return $imap->save_to_file($this->uid, $path);
	}
	
	/**
	 *
	 * @return \GO\Base\Mail\Imap 
	 */
	public function getImapConnection(){
		return $this->account->openImapConnection($this->mailbox);
	}
	
	private $_struct;
	
	private function _getStruct()
	{
		if (!isset($this->_struct)) {
			
			$this->_struct = $this->getImapConnection()->get_message_structure($this->uid);
			
			if(count($this->_struct)==1) {
				$headerCt = explode('/', $this->content_type);

				if(count($headerCt)==2) {
					//if there's only one part the IMAP server always seems to return the type as text/plain even though the headers say text/html
					//so use the header's content type.

					if($this->_struct[1]['subtype']=='plain'){
						$this->_struct[1]['type']=$headerCt[0];
						$this->_struct[1]['subtype']=$headerCt[1];
					}

					if(!empty($this->content_transfer_encoding) &&
						(empty($this->_struct[1]['encoding']) || $this->_struct[1]['encoding']=='7bit' || $this->_struct[1]['encoding']=='8bit')){
						$this->_struct[1]['encoding']=$this->content_transfer_encoding;
					}

					if(!empty($this->charset) && $this->_struct[1]['charset']=='us-ascii'){
						//$this->_struct[1]['charset']=$this->charset;
					}
				}
			}

			//get a default charset to decode filenames of attachments that don't have
			//that value
			if(!empty($this->_struct[1]['charset']))
				$this->defaultCharset = strtolower($this->_struct[1]['charset']);
			
		}
		return $this->_struct;
	}
	
	private $_htmlBody;
	private $_plainBody;
	
	private $_plainParts;
	private $_htmlParts;
	
	private $_bodyPartNumbers;
	
	
	private function _loadBodyParts(){
		
		if(!isset($this->_bodyPartNumbers)){
			$this->_bodyPartNumbers=array();
		
			$imap = $this->getImapConnection();
			$struct = $this->_getStruct();
			
			$hasAlternative = $imap->has_alternative_body($struct);

			$this->_plainParts = $imap->find_body_parts($struct,'text', 'plain');
			$this->_htmlParts = $imap->find_body_parts($struct,'text', 'html');
			
			if(!$hasAlternative && count($this->_htmlParts['parts']) && count($this->_plainParts['parts'])){
				//this is not very neat but we found some text attachments as body parts. Let's correct that.
				if($this->_plainParts['parts'][0]['number']>$this->_htmlParts['parts'][0]['number']){
					$this->_plainParts=array('parts'=>array(), 'text_found'=>false);
				} else	{
					$this->_htmlParts=array('parts'=>array(), 'text_found'=>false);
				}
			}

			for($i=0,$max=count($this->_plainParts['parts']);$i<$max;$i++){				
				if(empty($this->_plainParts['parts'][$i]['charset'])) {
					$this->_plainParts['parts'][$i]['charset'] = $this->defaultCharset;
				}
				if($this->_plainParts['parts'][$i]['type']=='text') {
					$this->_bodyPartNumbers[] = $this->_plainParts['parts'][$i]['number'];
				}
			}
			for($i=0,$max=count($this->_htmlParts['parts']);$i<$max;$i++){
				if(empty($this->_htmlParts['parts'][$i]['charset'])) {
					$this->_htmlParts['parts'][$i]['charset'] = $this->defaultCharset;
				}
				if($this->_htmlParts['parts'][$i]['type']=='text') {
					$this->_bodyPartNumbers[] = $this->_htmlParts['parts'][$i]['number'];
				}
			}
		}
	}
	
	/**
	 * Unset the flags when we wakeup from the cache. We can't know if the flags have been changed.
	 * When they are accessed they are fetched from the IMAP server in getSeen. 
	 * getFlag is not implemented because there was no need for it.
	 */
	public function __wakeup() {
		unset($this->seen);
		unset($this->flag);
		
		//refresh the account model because the password may have been changed
		$this->account = Account::model()->findByPk($this->account->id);
	}
	
	protected function getSeen(){
		if(isset($this->attributes['seen'])){
			return $this->attributes['seen'];
		}
		//when a message is retrieved from cache, we don't know if the seen flag has been changed.
		//so when this is requested we fetch it from the IMAP server.
		$imap = $this->getImapConnection();
		$attributes = $imap->get_message_header($this->uid, true);
		$this->setAttributes($attributes);

		return $this->attributes['seen'];
	}

	public function getHtmlBody($asText=false,$noMaxBodySize=false){				
		if(!isset($this->_htmlBody)){
			$imap = $this->getImapConnection();		
			$this->_loadBodyParts();
			
			$this->_htmlBody='';
			if($this->_htmlParts['text_found']){ //check if we found a html body
				foreach($this->_htmlParts['parts'] as $htmlPart){
					if($htmlPart['type']=='text'){

						if(!empty($this->_htmlBody)) {
							$this->_htmlBody .= '<br />';
						}
						
						$maxBodySize = $noMaxBodySize ? false : $this->maxBodySize;
						
						$htmlPartStr = $imap->get_message_part_decoded($this->uid, $htmlPart['number'],$htmlPart['encoding'], $htmlPart['charset'],$this->peek,false);
						$htmlPartStr = \GO\Base\Util\StringHelper::convertLinks($htmlPartStr);
						$htmlPartStr = \GO\Base\Util\StringHelper::sanitizeHtml($htmlPartStr);
						
						$this->_bodyTruncated = $imap->max_read;
						
						$this->_htmlBody .= $htmlPartStr;
					} else{
						$attachment = $this->getAttachment($htmlPart['number']);
						
						if(!$attachment){
							continue;
						}
					
						$attachment->content_id='go-autogen-'.$htmlPart['number'];
						$this->_htmlBody .= $this->buildAttachmentHtml($attachment,$htmlPart);
					}
				}
			}

			if(empty($this->_htmlBody) && !$asText){
				$this->_htmlBody = $this->getPlainBody(true,$noMaxBodySize);			
			}
		}
		
		if($asText){
			$htmlToText = new  \GO\Base\Util\Html2Text($this->_htmlBody);
			return $htmlToText->get_text();
		}
		
		return $this->_htmlBody;
	}
	
	/**
	 * 
	 * @param $attachment
	 * @param [] $partInfo
	 * 
	 * @return string
	 */
	private function buildAttachmentHtml($attachment, $partInfo){
		$html = '';

		if($partInfo['type'] == 'image'){
			$html .= '<img alt="'.htmlspecialchars($partInfo['name']).'" src="cid:'.$attachment->content_id.'" style="display:block;margin:10px 0;" />';
		} else {
			$html .= '<a alt="'.htmlspecialchars($partInfo['name']).'" href="cid:'.$attachment->content_id.'" style="display:block;margin:10px 0;">'.htmlspecialchars($partInfo['name']).'</a>';
		}
	
		return $html;
	}
	
	
	public function getPlainBody($asHtml=false,$noMaxBodySize=false){

		$inlineImages=array();
		
		if(!isset($this->_plainBody)){
			$imap = $this->getImapConnection();		
			$this->_loadBodyParts();

			$this->_plainBody='';
			if($this->_plainParts['text_found']){ //check if we found a plain body

				foreach($this->_plainParts['parts'] as $plainPart){
					if($plainPart['type']=='text'){

						if(!empty($this->_plainBody))
							$this->_plainBody.= "\n";
						$maxBodySize = $noMaxBodySize ? false : $this->maxBodySize;

						$this->_plainBody .= $imap->get_message_part_decoded($this->uid, $plainPart['number'],$plainPart['encoding'], $plainPart['charset'],$this->peek, $maxBodySize);
						$this->_bodyTruncated = $imap->max_read;
						
					} else {
						if($asHtml){
							//we have to put in this tag and replace it after we convert the text to html. Otherwise this html get's convert into htmlspecialchars.
							$this->_plainBody.='{inline_'.count($inlineImages).'}';
							
							$attachment = $this->getAttachment($plainPart['number']);
							
							if(!$attachment){
								continue;
							}
		
							$attachment->content_id='go-autogen-'.$plainPart['number'];
							$inlineImages[]=$this->buildAttachmentHtml($attachment,$plainPart);
						}
					}
				}
			}
		} else {
			foreach($this->_plainParts['parts'] as $plainPart){
				if($plainPart['type']!='text'){					
					if($asHtml){					
						$attachment = $this->getAttachment($plainPart['number']);
						if(!$attachment){
							continue;
						}

						$attachment->content_id='go-autogen-'.$plainPart['number'];
						$inlineImages[]=$this->buildAttachmentHtml($attachment,$plainPart);
					}
				}
			}
		}
		
		$this->_plainBody = \GO\Base\Util\StringHelper::normalizeCrlf($this->_plainBody);
		
		$this->extractUuencodedAttachments($this->_plainBody);
				
		if($asHtml){
			$body = $this->_plainBody;			
			$body = \GO\Base\Util\StringHelper::text_to_html($body);
			
			for($i=0,$max=count($inlineImages);$i<$max;$i++){
				$body=str_replace('{inline_'.$i.'}', $inlineImages[$i], $body);
			}
			return '<div class="msg">' . $body . '</div>';
		} else {
			if(empty($this->_plainBody)){
				return $this->getHtmlBody(true,$noMaxBodySize);
			} else {				
				return $this->_plainBody;
			}
		}
	}
	
	public function createTempFilesForAttachments($inlineOnly=false){
		$atts = $this->getAttachments();
		
		foreach($atts as $a){
			if(!$inlineOnly || $a->isInline()){
				$a->createTempFile();
			}
		}
	}
	
	
	private $_imapAttachmentsLoaded=false;
	
	/**
	 *
	 * @return ImapMessageAttachment [] 
	 */
	public function &getAttachments(): array
	{
		if(!$this->_imapAttachmentsLoaded){			
			
			$this->_imapAttachmentsLoaded=true;
			
			$imap = $this->getImapConnection();
			$this->_loadBodyParts();
			
			$parts = $imap->find_message_attachments($this->_getStruct(), $this->_bodyPartNumbers);
			
			$uniqueNames = array();
			
			foreach ($parts as $part) {
				$a = new ImapMessageAttachment();
				$a->setImapParams($this->account, $this->mailbox, $this->uid);
				
				if (empty($part['name']) || $part['name'] == 'false') {
					if (!empty($part['subject'])) {
						$a->name = \GO\Base\Fs\File::stripInvalidChars(\GO\Base\Mail\Utils::mimeHeaderDecode($part['subject'])) . '.eml';
					} elseif ($part['type'] == 'message') {
						$a->name = isset($part['description']) ? \GO\Base\Fs\File::stripInvalidChars($part['description']) . '.eml' : 'message.eml';
					} elseif ($part['subtype'] == 'calendar') {
						$a->name = \GO::t("Appointment", "email") . '.ics';
					} else {
						if ($part['type'] == 'text') {
							$a->name = $part['subtype'] . '.txt';
						} else {
							$a->name = $part['type'] . '-' . $part['subtype'];
						}
					}
				} else {
					$a->name = \GO\Base\Fs\File::stripInvalidChars(\GO\Base\Mail\Utils::mimeHeaderDecode($part['name']));
					
					if(!empty($part['filename'])){//} && empty($extension)){
						$a->name = \GO\Base\Fs\File::stripInvalidChars(\GO\Base\Mail\Utils::mimeHeaderDecode($part['filename']));
					}
				}
				
				$i=1;
				
				$a->name = !empty($a->name) ? $a->name : \GO::t("no name", "email");
				
				$file = new \GO\Base\Fs\File($a->name);
				while(in_array($a->name, $uniqueNames)){
					$a->name = $file->nameWithoutExtension().' ('.$i.').'.$file->extension();
					$i++;
				}
				$uniqueNames[]=$a->name;
				
				$a->disposition = isset($part['disposition']) ? strtolower($part['disposition']) : '';
				$a->number = $part['number'];
				$a->content_id='';
				if (!empty($part["id"])) {
					//when an image has an id it belongs somewhere in the text we gathered above so replace the
					//source id with the correct link to display the image.

					$tmp_id = $part["id"];
					if (strpos($tmp_id,'>')) {
						$tmp_id = substr($part["id"], 1,-1);
					}
					$id = $tmp_id;
					$a->content_id=  \GO\Base\Util\StringHelper::clean_utf8($id);
				}
							
				$a->mime=$part['type'] . '/' . $part['subtype'];
				
				$a->index=count($this->attachments);
				$a->size=intval($part['size']);
				$a->encoding = $part['encoding'];
				$a->charset = !empty($part['charset']) ? $part['charset'] : $this->charset;
				
				$this->addAttachment($a);
			}			
		}	
		
		return $this->attachments;
	}

	/**
	 * Generate a URL for removing all attachments from this message
	 *
	 * @return string
	 */
	public function getDeleteAllAttachmentsUrl(): string
	{
		$params = array(
			"account_id" => $this->account->id,
			"mailbox" => $this->mailbox,
			"uid" => $this->uid
		);

		return \GO::url('email/message/deleteAllAttachments', $params);

	}

	/**
	 * Generate a URL for zipping all attachments
	 *
	 * @return string
	 */
	public function getZipOfAttachmentsUrl(): string
	{
		$params = array(
			"account_id" => $this->account->id,
			"mailbox" => $this->mailbox,
			"uid" => $this->uid
		);

		return \GO::url('email/message/zipAllAttachments', $params);
	}
	
	/**
	 * Get the source of this message
	 * 
	 * @return String
	 */
	public function getSource(){
		$imap = $this->getImapConnection();
		$str = $imap->get_message_part($this->uid, 'HEADER', true) . "\r\n\r\n".$imap->get_message_part($this->uid, 'TEXT', true);
		return $str;
	}
	
	/**
	 * Get the VCALENDAR object as SabreDav vobject component
	 * 
	 * @return Sabre\VObject\Component 
	 */
	public function getInvitationVcalendar(){

		$attachments = $this->getAttachments();
			
		foreach($attachments as $attachment){			
			if($attachment->isVcalendar()){
				$data = $this->getImapConnection()->get_message_part_decoded($this->uid, $attachment->number, $attachment->encoding);
				$data = trim(StringUtil::normalizeCrlf($data));
				try {
					$vcalendar = \GO\Base\VObject\Reader::read($data);
					if($vcalendar && isset($vcalendar->vevent[0]))
						return $vcalendar;
				}
				catch(\Exception $e) {
					\GO::debug("VObject parser error: ". $e->getMessage());	
				}
			}
		}
		return false;
	}
	
	/**
	 * Delete the message from the IMAP server
	 * 
	 * @return boolean
	 */
	public function delete(){
		return $this->getImapConnection()->delete(array($this->uid));
						
	}


	/**
	 * Remove all attachments from current message
	 *
	 * @return bool
	 */
	public function deleteAttachments(): bool
	{
		$atts = $this->getAttachments();
		// No attachments, just return false and the original message will be left as is
		if(count($atts) === 0) {
			return false;
		}

		$bIsPlain = !$this->_htmlParts['text_found'];
		$swiftMsg = new \Swift_Message();
		$swiftMsg->setTo($this->to->getAddresses());
		$swiftMsg->setFrom($this->from->getAddresses());
		$swiftMsg->setCc($this->cc->getAddresses());
		$swiftMsg->setBcc($this->bcc->getAddresses());
		$swiftMsg->setSubject($this->subject);
		$swiftMsg->setDate(new DateTime($this->date));
		$swiftMsg->setContentType($this->content_type);
		if(!$bIsPlain) {
			$swiftMsg->setBody($this->getHtmlBody(), 'text/html');
		} else {
			$swiftMsg->setBody($this->getPlainBody(), 'text/plain');
		}
		while ($att = array_shift($atts)) {
			if ($att->disposition == 'attachment' || empty($att->content_id)) {
				$str = $this->addPartString($att, $bIsPlain);
				$swiftMsg->addPart($str, ($bIsPlain ? 'text/plain' : 'text/html'));
			}
		}

		$this->getImapConnection()->append_message($this->mailbox, $swiftMsg, '\Seen');

		return true;
	}


	/**
	 * @param ImapMessageAttachment $att
	 * @param bool $bIsPlain
	 * @return string
	 */
	private function addPartString(ImapMessageAttachment $att, bool $bIsPlain): string
	{
		$str = '';
		if($bIsPlain) {

			$str .= "--\r\nThe attachment '" . $att->name . "' was manually removed.";
			// Leaving this commented out for a while. Perhaps in the future, the wish is to show somewhat more helpful info...
//			$str .= "--\r\n" .
//				"Deleted: " . $att->name . "\r\n" .
//				"--\r\n" .
//				"You deleted an attachment from this message. The original MIME headers for the attachment were:\r\n" .
//				"Content-Type: " . $att->mime . "; name=\"" . $att->name . "\"\r\n" .
//				"Content-Disposition: " . $att->disposition . "; filename=\"" . $att->name . "\"\r\n" .
//				"Content-Transfer-Encoding: " . $att->encoding;
		} else {
			$str .= "<hr/>The attachment <strong>" . $att->name . "</strong> was manually removed.";

//			$str .= '<hr/>' .
//				'Deleted: ' . $att->name . '<br>' .
//				'<hr/>' .
//				'You deleted an attachment from this message. The original MIME headers for the attachment were:<br>' .
//				'Content-Type: ' . $att->mime . '; name="' . $att->name . '"<br>' .
//				'Content-Disposition: ' . $att->disposition . '; filename="' . $att->name . '"<br>' .
//				'Content-Transfer-Encoding: ' . $att->encoding;
		}
//		if (isset($this->content_id)) {
//			$str .= '<br>Content-ID: ' . $att->content_id . '<br>' .
//				'X-Attachment-Id:' . $att->content_id;
//			$str .= "\r\nContent-ID: " . $att->content_id . "\r\n" .
//				"X-Attachment-Id: " . $att->content_id;
//		}
		return $str;
	}


	/**
	 * Returns an array with linked item objects.
	 */
	public function getLinks(){
		
		$foundLinks = array();
		
		if(\GO::modules()->savemailas){
			$linkedEmailModels = \GO\Savemailas\Model\LinkedEmail::model()->findByAttributes(array('uid'=>$this->getUniqueID()));
			foreach($linkedEmailModels as $linkedEmailModel){
				$stmt = \GO\Base\Model\SearchCacheRecord::model()->findLinks($linkedEmailModel);
				foreach($stmt as $rec){
					$foundLinks[] = $rec;
				}
			}
		}
		
		return $foundLinks;
	}
	
}
