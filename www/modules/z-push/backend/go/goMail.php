<?php
require_once(__DIR__."/goMailInvite.php");

class goMail extends GoBaseBackendDiff {

	
	/**
	 * Constants for attachment encodings because sending them as strings didn't
	 * work on old clients. They corrupted the string base64 became bae64.
	 */
	const ENCODING_BASE64=1;
	const ENCODING_QP=2;
	const ENCODING_NONE=3;
	
	private $_emailFolders = array();
	
	   /**
     * Creates or modifies a folder
     * The folder type is ignored in IMAP, as all folders are Email folders
     *
     * @param StringHelper        $folderid       id of the parent folder
     * @param StringHelper        $oldid          if empty -> new folder created, else folder is to be renamed
     * @param StringHelper        $displayname    new folder name (to be created, or to be renamed to)
     * @param int           $type           folder type
     *
     * @access public
     * @return boolean                      status
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
		ZLog::Write(LOGLEVEL_INFO, sprintf("goMail->ChangeFolder('%s','%s','%s','%s')", $folderid, $oldid, $displayname, $type));

		// go to parent mailbox
		//$this->imap_reopenFolder($folderid);

		$imap = $this->_imapLogon();
		
		//remove m/ from the combined stuff
		if(!empty($folderid)){
			$folderid = substr($folderid, 2);

			// build name for new mailboxBackendMaildir        
			$newname = $folderid . $imap->get_mailbox_delimiter() . $displayname;
		}else
		{
			$newname = $displayname;
		}

		$csts = false;
		// if $id is set => rename mailbox, otherwise create
		if ($oldid) {
			// rename doesn't work properly with IMAP
			// the activesync client doesn't support a 'changing ID'
			// TODO this would be solved by implementing hex ids (Mantis #459)
			
//			$oldid = substr($oldid,2);
//			$csts = $imap->rename_folder($oldid, $newname);
			
		} else {
			ZLog::Write(LOGLEVEL_INFO, "Create: ".$newname);
			$csts = $imap->create_folder($newname);
		}
		if ($csts) {

			//refresh cached folder list
			$this->GetFolderList();

			return $this->StatFolder($newname);
		}
		else
			return false;
	}

	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return \SyncTask
	 */
	public function GetMessage($folderid, $id, $contentparameters) {
		//TODO: implement truncation
		$truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
		
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::truncsize = '.$truncsize);
		
		//TODO: implement MIME mails
		//$mimesupport = $contentparameters->GetMimeSupport();

		if (!$this->_imapLogon($folderid))
			return false;

		$mailbox = $this->_replaceDotWithServerDelimiter($folderid);
		$imapAccount = $this->getImapAccount();
		if(!$imapAccount)
			return false;
		
		// Hack to make Inbox also work
		if ($folderid == 'Inbox')
			$mailbox = 'INBOX';

		$message = new SyncMail(); // Create new syncmail object
		
		//attachements that are too big to send over.
		$this->_tooBigAttachments=array();
						
		
		$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($imapAccount, $mailbox, $id);
		if ($imapMessage) {

			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference(), array(SYNC_BODYPREFERENCE_MIME, SYNC_BODYPREFERENCE_PLAIN, SYNC_BODYPREFERENCE_HTML));		
			ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::bpReturnType = '.$bpReturnType);

			 if (Request::GetProtocolVersion() >= 12.0) {

				$message->asbody = new SyncBaseBody();
				$asBodyData = null;
				switch ($bpReturnType) {
					case SYNC_BODYPREFERENCE_PLAIN:
						$asBodyData = $imapMessage->getPlainBody();
						$asBodyData .= $this->_getTooBigAttachmentsString();
	
						break;
					case SYNC_BODYPREFERENCE_HTML:
						$asBodyData = \GO\Base\Util\StringHelper::normalizeCrlf($imapMessage->getHtmlBody()).nl2br($this->_getTooBigAttachmentsString());
						break;
					case SYNC_BODYPREFERENCE_MIME:
						//we load the mime message and create a new mime string since we can't trust the IMAP source. It often contains wrong encodings that will crash the 
						//sync. eg. incredimail.
						$source = $imapMessage->getSource();
						
						if(!strpos($source, "pkcs7-mime")){
							ZLog::Write(LOGLEVEL_DEBUG, 'Recreating MIME source');
							
							try{
								$sendMessage = \GO\Base\Mail\Message::newInstance()->loadMimeMessage($source, true);
								$asBodyData = $sendMessage->toString();
							} catch (Exception $e){
								ZLog::Write(LOGLEVEL_ERROR, "Failed to recreate mime source. Falling back to original mime. Subject: ".$imapMessage->subject." Exception: ".$e->getMessage());
								$asBodyData = $source;
							}
							
						}  else {
							
							ZLog::Write(LOGLEVEL_DEBUG, 'Passing through orignal IMAP MIME source for SMIME');
							$asBodyData = $source;
						}						
						break;
					case SYNC_BODYPREFERENCE_RTF:
						ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->GetMessage RTF Format NOT CHECKED");
						$asBodyData = base64_encode(\GO\Base\Util\StringHelper::normalizeCrlf($imapMessage->getPlainBody()).$this->_getTooBigAttachmentsString());
						break;
				}


				 //attachments are not necessary when using mime
				 //
				 //Attachments probably need to be sent even with MIME type:
				 //http://talk.sonymobile.com/t5/Xperia-Z1-Compact/Z1-Compact-Problem-With-EAS/m-p/866755#11220
				 //zpush_always_send_attachments config setting is for testing this carefully.
				 if($bpReturnType!=SYNC_BODYPREFERENCE_MIME || !empty(\GO::config()->zpush_always_send_attachments)){
					 $message->asattachments = $this->_getASAttachments($imapMessage,$id,$mailbox, $bpReturnType != SYNC_BODYPREFERENCE_PLAIN ? $asBodyData : null, $message);
				 }

				// truncate body, if requested
				//MS: Not sure if !empty($truncsize) is needed here. Testing for Robert S.
				if(!empty($truncsize) && $bpReturnType != SYNC_BODYPREFERENCE_MIME && strlen($asBodyData) > $truncsize) {
					$asBodyData = Utils::Utf8_truncate($asBodyData, $truncsize);
					$message->asbody->truncated = 1;
				}else {
					$message->asbody->truncated = 0;
				}
				$message->asbody->data = version_compare(ZPUSH_VERSION, '2.3', '<') ? $asBodyData : StringStreamWrapper::Open($asBodyData);
				$message->asbody->type = $bpReturnType;
//				$message->nativebodytype = $bpReturnType; //This casued outlook 2013 to fail!!
				$message->asbody->estimatedDataSize = strlen($asBodyData);

				$bpo = $contentparameters->BodyPreference($message->asbody->type);
				if (Request::GetProtocolVersion() >= 14.0 && $bpo->GetPreview()) {
					if(!isset($plainBody))
						$plainBody = $imapMessage->getPlainBody();
					
					$textPreview = isset($plainBody) ? $plainBody : Utils::ConvertHtmlToText($message->asbody->data);
					
					$message->asbody->preview = Utils::Utf8_truncate($textPreview, $bpo->GetPreview());
				} 
			}else {
				
				
				$message->bodytruncated = 0;
				/* BEGIN fmbiete's contribution r1528, ZP-320 */
				if ($bpReturnType == SYNC_BODYPREFERENCE_MIME) {
					$mail = $imapMessage->getSource();
//					if (strlen($mail) > $truncsize) {
//						$message->mimedata = Utils::Utf8_truncate($mail, $truncsize);
//						$message->mimetruncated = 1;
//					} else {
						$message->mimetruncated = 0;
						$message->mimedata = $mail;
//					}
					$message->mimesize = strlen($message->mimedata);
				} else {
					//attachments are not needed for MIME
					$message->attachments = $this->_getNormalAttachments($imapMessage,$id,$mailbox);
									
					$plainBody = $imapMessage->getPlainBody();
					
					$plainBody .= $this->_getTooBigAttachmentsString();
					
					// truncate body, if requested
					if (strlen($plainBody) > $truncsize) {
						$message->body = Utils::Utf8_truncate($plainBody, $truncsize);
						$message->bodytruncated = 1;
					} else {
						$message->body = $plainBody;
						$message->bodytruncated = 0;
					}
					$message->bodysize = strlen($message->body);
				}
			}

			$message->datereceived = $imapMessage->udate;
			$message->messageclass = "IPM.Note";
			$message->subject = $imapMessage->subject;
			$message->read = $imapMessage->seen ? 1 : 0;
			$message->from = (string) $imapMessage->from;

			// Addressing block
			$message->to = $imapMessage->to->getArray();
			$message->cc = $imapMessage->cc->getArray();
			$message->bcc = $imapMessage->bcc->getArray();
			$message->reply_to =  $imapMessage->reply_to->getArray();
			
			$firstTo = $imapMessage->to->getAddress();
			
			if($firstTo)
				$message->displayto=$firstTo['personal'];
			
			// End of addressing block

			if (isset($imapMessage->x_priority)) {
				$mimeImportance =  $imapMessage->x_priority;
				if ($mimeImportance > 3)
						$message->importance = 0;
				if ($mimeImportance == 3)
						$message->importance = 1;
				if ($mimeImportance < 3)
						$message->importance = 2;
			}else
			{
				$message->importance = 1;
			}
			
			$message->internetcpid = INTERNET_CPID_UTF8;
			if (Request::GetProtocolVersion() >= 12.0) {
					$message->contentclass = "urn:content-classes:message";
			}

			//don't flag as read right IS ALREADY DEFAULT
//			$imapMessage->peek = true;
			unset($imapMessage);
		}
		
		//Uses lots of mem:
		//ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::MESSAGE = '.var_export($message,true));
		
		return $message;
	}
	
	/**
	 * Get the attachments for a text based email
	 * 
	 * @param \GO\Email\Model\ImapMessage $imapMessage
	 * @param int $msgId
	 * @param String $mailbox
	 * @return array attachment array
	 */
	private function _getNormalAttachments($imapMessage,$msgId,$mailbox){
		$nAttachments = array();
		$attachments = $imapMessage->getAttachments();
		
		foreach ($attachments as $attachment) {
			if (!$attachment->isInline()) {
				
				if($attachment->size>$this->_getMaxAttachmentSize()){
					array_push($this->_tooBigAttachments, array('name'=>$attachment->name,'size'=>$attachment->size));
				}else
				{
					$attach = new SyncAttachment();
					$attach->attsize = $attachment->size;
					$attach->displayname = $attachment->name;
					$attach->attname = $this->_encodeFileReference($msgId,$attachment,$mailbox);
					$attach->attmethod = 1;
					$attach->attoid = $attachment->isInline()?$attachment->content_id:NULL;
					array_push($nAttachments, $attach);
				}
			}
		}
		return $nAttachments;
	}
	
	
	private $_tooBigAttachments=array();
	
	private function _getTooBigAttachmentsString(){
		if(!count($this->_tooBigAttachments))
		{
			return '';
		}  else {
			$str = "\r\n\r\n".GO::t("The following attachments were too big to synchronize", "sync").":\r\n";
			
			foreach($this->_tooBigAttachments as $a){
				$str .= '- '.$a['name'].' ('.\GO\Base\Util\Number::formatSize($a['size']).")\r\n";
			}
			
			return $str;
		}
	}
	
	/**
	 * Get the attachments for a HTML based email
	 * 
	 * @param \GO\Email\Model\ImapMessage $imapMessage
	 * @param int $msgId
	 * @param String $mailbox
	 * @return array attachment array
	 */
	private function _getASAttachments($imapMessage,$msgId,$mailbox, $body, &$output){
		$asAttachments = array();
		$attachments = $imapMessage->getAttachments();
		
		foreach ($attachments as $attachment) {
			if (is_calendar($attachment)) {
				ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendIMAP->GetMessage(): text/calendar part found, trying to convert"));
				$output->meetingrequest = new SyncMeetingRequest();
				parse_meeting_calendar($attachment, $output, $mailbox == $this->getImapAccount()->sent);
			} else if($attachment->size>$this->_getMaxAttachmentSize()){
				array_push($this->_tooBigAttachments, array('name'=>$attachment->name,'size'=>$attachment->size));
			} else {
				$attach = new SyncBaseAttachment();
				$attach->displayname = $attachment->name;
				$attach->filereference = $this->_encodeFileReference($msgId,$attachment,$mailbox);
				$attach->method = 1;
				$attach->estimatedDataSize = $attachment->getEstimatedSize();

				$inline = false;
				if (!empty($attachment->content_id) && isset($body)) {
					$inline = $attachment->isInline() && strpos($body, $attachment->content_id) !== false;
				}

				$attach->contentid = $inline ? $attachment->content_id : NULL;
				$attach->isinline = $inline;

				array_push($asAttachments, $attach);
			}
		}
		
		return $asAttachments;
	}
	
	private function _getMaxAttachmentSize(){		
		if(empty(GO::config()->zpush2_max_attachmentsize))
			GO::config()->zpush2_max_attachmentsize=104857600;
		
		return GO::config()->zpush2_max_attachmentsize;
	}
	
	private function _encodeFileReference($msgId, $attachment, $mailbox){
		
		switch($attachment->encoding){
			
			case 'base64':
				$enc = self::ENCODING_BASE64;
				break;
			
			case 'quoted-printable':
				$enc = self::ENCODING_QP;
				break;
			default:
				$enc = self::ENCODING_NONE;
				break;
		}
		
		
		$encodeString = $msgId . ':' . $attachment->number . ':' . $enc . ':' . $mailbox;
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->_encodeFileReference(' . $encodeString .')');
		$return = $encodeString;
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->_encodeFileReference RETURNS:' . $return .')');
		
		return $return; 
	}
	
	private function _decodeFileReference($encodedFileReference){
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->_decodeFileReference(' . $encodedFileReference .')');
		
		if(strpos($encodedFileReference, ':')!==false){
			$return = $encodedFileReference;
		}else{
			$return = base64_decode($encodedFileReference);
		}
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->_decodeFileReference RETURNS:' . $return .')');
		
		return $return;
	}
	
//	/**
//	 * Get the message body for a HTML/MIME message
//	 * 
//	 * @param \GO\Email\Model\ImapMessage $imapMessage
//	 * @param int $sbReturnType
//	 * @return \SyncBaseBody
//	 */
//	private function _getASBody($imapMessage, $sbReturnType=SYNC_BODYPREFERENCE_HTML){
//		
//		$sbBody = new SyncBaseBody();
//		$sbBody->type = $sbReturnType;
//
//		if ($sbReturnType == SYNC_BODYPREFERENCE_HTML){
//			$sbBody->data = str_replace("\n", "\r\n", str_replace("\r", "", $imapMessage->getHtmlBody())); //Utils::ConvertCodepageStringToUtf8($message->internetcpid, $body);
//		}else if($sbReturnType == SYNC_BODYPREFERENCE_MIME){
//			$sbBody->data = $imapMessage->getSource();
//		}		
//		else{
//			$sbBody->data = $imapMessage->getPlainBody(); //w2u($body); 
//		}
//
//		$sbBody->estimatedDataSize = strlen($sbBody->data);
//		$sbBody->truncated = 0;
//		return $sbBody;
//	}
	
	
//	public function GetAttachmentData($attname) {
//		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetAttachmentData(' . $attname .')');
//		
//		$attname = $this->_decodeFileReference($attname);
//		$attparts = explode(":", $attname);
//		if(count($attparts)!=4)
//		{
//			ZLog::Write(LOGLEVEL_ERROR, "Malformed attachment name '$attname' in GetAttachmentData!");
//			throw new StatusException("Malformed attachment name '$attname' in GetAttachmentData!");
//		}
//		
//		list($uid, $part, $encoding, $mailbox) = explode(":", $attname);
//
//		if (empty($mailbox))
//			$mailbox = 'INBOX';
//
//		$imap = $this->_imapLogon($mailbox);
//		if(!$imap)
//			throw new StatusException("Unable to connect to IMAP server in GetAttachmentData!");
//		
//		include_once('backend/go/GoImapStreamWrapper.php');
//		$attachment = new SyncItemOperationsAttachment();
//		$attachment->data = GoImapStreamWrapper::Open($imap, $uid, $part, $encoding);
//		return $attachment;
//	}
	

	
	/**
	 * Holds temporary attachments
	 * @var type 
	 */
	private $_tmpFiles=array();
	public function GetAttachmentData($attname) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetAttachmentData(' . $attname .')');
		
		/**
		 * We had a problem with corrupted filereference or attname values in getattachmentdata in our backend. It only happens on some phones which use a GET parameter for the attachmentName.
		 * 
		 * Z-push filters the attachment name in lib/request/request.php on line 122 incorrectly.
		 * It does:
		 * 
		 * self::$attachmentName = self::filterEvilInput($_GET["AttachmentName"], self::HEX_EXTENDED);
		 * 
		 * Which strips all non hex chars. So our name INBOX becomes "B". We use <UID>:<ATTID>:<MAILBOXNAME> as a filereference just like the stock imap backend does.
		 * 
		 * As a work around we process the get parameter ourselves.
		 * 
		 * More info: http://z-push.sourceforge.net/phpbb/viewtopic.php?f=7&t=2379
		 */
		if(isset($_GET["AttachmentName"])){
			ZLog::Write(LOGLEVEL_DEBUG, 'Using GET parameter as attname: ' . $_GET["AttachmentName"]);
			$attname = $_GET["AttachmentName"];
		}
		
		$attname = $this->_decodeFileReference($attname);
		$attparts = explode(":", $attname);
		if(count($attparts)!=4)
		{
			ZLog::Write(LOGLEVEL_ERROR, "Malformed attachment name '$attname' in GetAttachmentData!");
			throw new StatusException("Malformed attachment name '$attname' in GetAttachmentData!");
		}
		
		list($uid, $part, $encoding, $mailbox) = explode(":", $attname);
		
		
		switch($encoding){
			
			case "bae64"://for backwards compatibility
			case "base64"://for backwards compatibility
			case self::ENCODING_BASE64:
				$enc = "base64";
				break;
			
			case 'quoted-printable': //for backwards compatibility
			case self::ENCODING_QP:
				$enc = 'quoted-printable';
				break;
			default:
				$enc = '';
				break;
		}

		if (empty($mailbox))
			$mailbox = 'INBOX';

		$imap = $this->_imapLogon($mailbox);
		if(!$imap)
			throw new StatusException("Unable to connect to IMAP server in GetAttachmentData!");
		
		$tmpfile = \GO\Base\Fs\File::tempFile();
		$this->_tmpFiles[]=$tmpfile;
		

		if(!$imap->save_to_file($uid, $tmpfile->path(), $part, $enc, true)){
			throw new StatusException("Failed to save attachment");
		}
		
		$fp = fopen($tmpfile->path(),'r');
		
		if(!$fp){
			throw new StatusException("Could not open attachment file stream");
		}
	
		$attachment = new SyncItemOperationsAttachment();
		$attachment->data=$fp;
		$attachment->contenttype=$tmpfile->mimeType();
		return $attachment;
	}
	
	public function Logoff() {
		
		foreach($this->_tmpFiles as $file){
			$file->delete();
		}
		
		return parent::Logoff();
	}
	
	/**
	 * Save the information from the phone to Group-Office.
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * Note: Not implemented because a mail message cannot be changed
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param \SyncTask $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message,$contentParameters) {
		ZLog::Write(LOGLEVEL_DEBUG, '(NOT IMPEMENTED) ZPUSH2MAIL::ChangeMessage');

		try{
			if (isset($message->flag)) {
			   ZLog::Write(LOGLEVEL_DEBUG, sprintf("goMail->ChangeMessage('Setting flag')"));

			   $imap = $this->_imapLogon($folderid);
				if(!$imap)
					return false;

			   if (isset($message->flag->flagstatus) && $message->flag->flagstatus == 2) {
				   ZLog::Write(LOGLEVEL_DEBUG, "Set On FollowUp -> IMAP Flagged");
				   $status = $imap->set_message_flag(array($id), "\Flagged", false);
				  // $status = @imap_setflag_full($this->mbox, $id, "\\Flagged",ST_UID);
			   }
			   else {
				   ZLog::Write(LOGLEVEL_DEBUG, "Clearing Flagged");
				   $status = $imap->set_message_flag(array($id), "\Flagged", true);
				   //$status = @imap_clearflag_full ( $this->mbox, $id, "\\Flagged", ST_UID);
			   }

			   if ($status) {
				   ZLog::Write(LOGLEVEL_DEBUG, "Flagged changed");
			   }
			   else {
				   ZLog::Write(LOGLEVEL_DEBUG, "Flagged failed");
			   }
		   }

		}
		catch(Exception $e){
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2MAIL::EXCEPTION ~~ '.(string)$e);
		}

		return $this->StatMessage($folderid, $id);
	}
	
	public function GetWasteBasket() {
		return false;
	}
	
	/**
	 * Flag a message as read
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param int $flags
	 * @return boolean
	 */
	public function SetReadFlag($folderid, $id, $flags, $contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail::SetReadFlag('.$folderid.': '.$id.')');
		$clear = false; // set as "Seen" (read)
		
		if($flags == 0)
			$clear = true; // set as "Unseen" (unread)

		$imap = $this->_imapLogon($folderid);
		if(!$imap)
			return false;

		//Set on IMAP server
		$status = $imap->set_message_flag(array($id), "\Seen", $clear);
	
		return $status;
	}

	/**
	 * Delete a message so it will be moved to the trashcan
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @return boolean
	 */
	public function DeleteMessage($folderid, $id, $contentparameters) {
		
		ZLog::Write(LOGLEVEL_DEBUG, "goMail::DeleteMessage($folderid, $id)");
	
		try{
			if(!$imap = $this->_imapLogon($folderid))
				return false;

			$imapAccount = $this->getImapAccount();
			if(!$imapAccount)
				return false;
			
			if (!empty($imapAccount->trash) && $folderid != $imapAccount->trash) {
				$imap->set_message_flag(array($id), "\Seen");
				return $imap->move(array($id), $imapAccount->trash);
			} else {
				return $imap->delete(array($id));
			}
		}
		catch(Exception $e){
			ZLog::Write(LOGLEVEL_ERROR, "Skipping delete of read-only mail item");
		}
	}

	/**
	 * Move a mailmessage to an other folder.
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @param StringHelper $newfolderid
	 * @return boolean
	 */
	public function MoveMessage($folderid, $id, $newfolderid, $contentparameters) {
		
		ZLog::Write(LOGLEVEL_DEBUG, "goMail::MoveMessage($folderid, $id, $newfolderid)");
		
		$imap = $this->_imapLogon($folderid);
		if(!$imap)
			return false;
		
		return $imap->move(array($id), $this->_replaceDotWithServerDelimiter($newfolderid));
	}
	
	/**
	 * Get the status of an item
	 * 
	 * @param StringHelper $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {
		
		ZLog::Write(LOGLEVEL_DEBUG, "StatMessage($folderid, $id)");
		$imap = $this->_imapLogon($folderid);
		if (!$imap)
			return false;

		$mailbox = $this->_replaceDotWithServerDelimiter($folderid);

		//$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($this->getImapAccount(), $mailbox, $id);

		$headers = $imap->get_flags($id);
		if($headers == false) {
			return false;
		}

		$stat = false;
		
		if ($header = array_shift($headers)) {
			
			$stat = array();
			$stat["mod"] = $header['date'];
			$stat["id"] = $header['uid'];
			// 'flagged' aka 'FollowUp' aka 'starred'
			$stat["star"] = in_array("\Flagged", $header['flags']);
			// 'seen' aka 'read' is the only flag we want to know about
			$stat["flags"] = in_array("\Seen", $header['flags']);

		}

		return $stat;
	}

	/**
	 * Get the list of the items that need to be synced
	 * 
	 * @param StringHelper $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

//		\GO\Base\Mail\ImapBase::$debug = true;
//		GO()->getDebugger()->enable();

		ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");
		$messages = array();

		if (GO::modules()->email) {
			if ($imap = $this->_imapLogon($folderid)) {
				//Don't fetch all messages with an empty cutoffdate because
				//this may kill the server. Default to two weeks.
				if (empty($cutoffdate))
				{
					ZLog::Write(LOGLEVEL_DEBUG, "empty cutoff");
					$headers = $imap->get_flags();
				} else
				{
					ZLog::Write(LOGLEVEL_DEBUG, 'Client sent cutoff date for calendar: ' . date("j-M-Y", $cutoffdate));
					$uids = $imap->search('SINCE ' . date("j-M-Y", $cutoffdate));
					if(empty($uids)) {
						return [];
					}
					$headers = $imap->get_flags(min($uids).':*');
				}

				if(!$headers) {
					ZLog::Write(LOGLEVEL_ERROR, "IMAP returned error reponse" . $imap->last_error());
					return [];
				}
				
				
				ZLog::Write(LOGLEVEL_DEBUG, "message count:".count($headers));
					
				
				
				/* Create messages array */
				foreach ($headers as $header) {
					
					$message = array();
					$message["mod"] = $header['date'];
					$message["id"] = $header['uid'];
					// 'flagged' aka 'FollowUp' aka 'starred'
					$message["star"] = in_array("\Flagged", $header['flags']);
					// 'seen' aka 'read' is the only flag we want to know about
					$message["flags"] = in_array("\Seen", $header['flags']);

					$messages[] = $message;
				}
			}
		}
		return $messages;
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param StringHelper $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {
		
		ZLog::Write(LOGLEVEL_DEBUG, "GetFolder($id)");

		if(empty($this->_emailFolders)) {
			$this->GetFolderList();
		}

		if (!isset($this->_emailFolders[$id])) {
			ZLog::Write(LOGLEVEL_WARN, "E-mail folder '$id' not found");
			return false;
		}


		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->displayname = $this->_emailFolders[$id]['displayname'];
		$folder->type = $this->_emailFolders[$id]['type'];
		$folder->parentid = $this->_emailFolders[$id]['parentid'];

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 * 
	 * @return array
	 */
	public function GetFolderList() {

		ZLog::Write(LOGLEVEL_DEBUG, "GetFolderList()");
		

		$imapAccount = $this->getImapAccount();
		if (!$imapAccount) {
			return array();
		}
		
		$this->_emailFolders = array(); // Clear the email folders array
		
		$box = array(
						'id'=>'INBOX',
						'type'=>SYNC_FOLDER_TYPE_INBOX,
						'displayname'=> 'INBOX',
						"parentid"=>"0",
						"mod"=>'INBOX'
				);
		
		$this->_emailFolders[$box['id']] = $box;
		
		$folders = array(
				$box
		);

		if ($this->_imapLogon() && $imapAccount) { // TODO: CHECK ADDED ACCOUNT CHECK
			$mailboxes = $imapAccount->getAllMailboxes(false, false);



			$mailboxes = array_reverse($mailboxes);

			foreach ($mailboxes as $mailbox) {
				
				//If the mailbox has the noselect option then don't sent it to the phone
				if($mailbox->noselect)
					continue; // Go to next record in the foreach
				
				//INBOX is already added by default.
				//Some devices will behave strange when there's no INBOX
				if(strtoupper($mailbox->name) == "INBOX"){
					continue;
				}
				
				$box = array();

				if (!$this->_server_delimiter)
					$this->_server_delimiter = $mailbox->delimiter;

				// cut off serverstring
				$box["id"] = $mailbox->name;

				//don't sync spam folder
//				if (stripos($box["id"], 'spam') !== false)
//					continue;

				if (stripos($box["id"], '[Gmail]') !== false)
					continue;

				// always use "." as folder delimiter
				$box["id"] = str_replace($mailbox->delimiter, ".", $box["id"]);
				$box["displayname"] = $box["mod"] = $mailbox->getBaseName();
				//$box["displayname"] = $mailbox->getDisplayName();
				$box["parentid"] = str_replace($mailbox->delimiter, ".", $mailbox->getParentName());
				if (!$box['parentid'])
					$box['parentid'] = "0";

				switch ($mailbox->name) {
					case 'INBOX':
						$box['type'] = SYNC_FOLDER_TYPE_INBOX;
						break;
					case $imapAccount->sent:
						$box['type'] = SYNC_FOLDER_TYPE_SENTMAIL;
						break;
					case $imapAccount->drafts:
						$box['type'] = SYNC_FOLDER_TYPE_DRAFTS;
						break;
					case $imapAccount->trash:
						$box['type'] = SYNC_FOLDER_TYPE_WASTEBASKET;
						break;
					default:
						$box['type'] = SYNC_FOLDER_TYPE_OTHER;
						break;
				}
				$this->_emailFolders[$box['id']] = $box;
				
			
			}
		}
		
		//ZLog::Write(LOGLEVEL_DEBUG, var_export($folders, true));
		
		return array_values($this->_emailFolders);
		
	}
	
	
 /**
	* Sends an e-mail
	* This messages needs to be saved into the 'sent items' folder
	*
	* @param SyncSendMail  $sm     SyncSendMail object
	*
	* @access public
	* @return boolean
	* @throws StatusException
	*/
	public function SendMail($sm){
		
		$success=false;
		
		ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()');
//		ZLog::Write(LOGLEVEL_DEBUG, '$sm = '.var_export($sm,true));
		try{
			
			$smart = false;
			if(isset($sm->replacemime)){
			 ZLog::Write(LOGLEVEL_DEBUG, 'SMARTFORWARD');
			 $smart = true;
			}

			$forward = isset($sm->forwardflag) && !empty($sm->forwardflag)?true:false;
			$reply = isset($sm->replyflag) && !empty($sm->replyflag)?true:false;
			// Always save message to sent folder.
			$saveInSent = true; //isset($sm->saveinsent) && !empty($sm->saveinsent)?true:false;
			$oldMessageUID = isset($sm->source->itemid) && !empty($sm->source->itemid)?$sm->source->itemid:false;
			$oldMessageFolderID = isset($sm->source->folderid) && !empty($sm->source->folderid)?$sm->source->folderid:false;

			 // REMOVE THE "m/" from the folder id
			if(!empty($oldMessageFolderID))
				$oldMessageFolderID = substr($oldMessageFolderID, 2);
			
			$imapAccount = $this->getImapAccount();
			if(!$imapAccount){
				
				ZLog::Write(LOGLEVEL_ERROR, 'NO IMAP account set for this user. No meeting request was sent.');
				return true; //if we throuw status exception here then iphone will fail with a wbxmlexception
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'beforeloadmime');
			$sendMessage = \GO\Base\Mail\Message::newInstance()->loadMimeMessage($sm->mime);
			
			//free up memory
			unset($sm->mime);
			
			// Mark old mail as Forwarded
			if($forward){
				ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~FORWARD');
				$imap = $imapAccount->openImapConnection($oldMessageFolderID);
				$imap->set_message_flag(array($oldMessageUID), "\$Forwarded");
			}

			// Mark old mail as Answered
			if($reply){
				ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~REPLY');
				$imap = $imapAccount->openImapConnection($oldMessageFolderID);
				$imap->set_message_flag(array($oldMessageUID), "\Answered");
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'afterforwardreply');
			
			$refImapMessage = false;
			if($oldMessageUID && $oldMessageFolderID) {
				ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~INCLUDE OLD MESSAGE');
				$refImapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($imapAccount, $oldMessageFolderID, $oldMessageUID);
				
				if($refImapMessage) {
					$headers = $sendMessage->getHeaders();
					$headers->addTextHeader('In-Reply-To', $refImapMessage->message_id);
					$headers->addTextHeader('References', $refImapMessage->message_id);
				}
			}
			
	//		// Include the old message to this new one./
			if($refImapMessage && !$smart) {
				ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~INCLUDE OLD MESSAGE');
				$refImapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($imapAccount, $oldMessageFolderID, $oldMessageUID);
				
				if($refImapMessage){
					$refImapMessage->createTempFilesForAttachments();

					$body = $sendMessage->getBody();
					$body .= "\r\n\r\n";
					$body .= $refImapMessage->getHtmlBody();

					$sendMessage->setHtmlAlternateBody($body);

					// Only attach the attachments of the old message when we forward the mail
					if($forward){
						ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~ATTACH OLD ATTACHMENTS');
						$attachments = $refImapMessage->getAttachments();
						//re-attach attachments
						foreach ($attachments as $attachment) {		
							$file = new \GO\Base\Fs\File(GO::config()->tmpdir.$attachment->getTempFile());				
							$att = Swift_Attachment::fromPath($file->path(),$file->mimeType());
							$sendMessage->attach($att);			
						}
					}
				}else
				{
					ZLog::Write(LOGLEVEL_WARN, 'Could not find IMAP message for reply or forward!');
				}
			}
		
			// Implement to always set the GO alias to sent emails
			$alias = $imapAccount->getDefaultAlias();
			$sendMessage->setFrom($alias->email, $alias->name);
			ZLog::Write(LOGLEVEL_DEBUG, 'beforesend');
			$mailer = \GO\Base\Mail\Mailer::newGoInstance(\GO\Email\Transport::newGoInstance($imapAccount));
			$failedRecipients=array();
			$success = $mailer->send($sendMessage, $failedRecipients);
			ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~SEND~~'.$success);
			ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~FAILED RECIPIENTS~~'.var_export($failedRecipients,true));
			//if a sent items folder is set in the account then save it to the imap folder
			if($success && $imapAccount->sent && $saveInSent) {
				$imap = $imapAccount->openImapConnection($imapAccount->sent);
				$imap->append_message($imapAccount->sent, $sendMessage->toString(), "\Seen");
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'MAIL IS SENT SUCCESSFULLY::::::::'.$success);
		}
		catch (Exception $e){
			ZLog::Write(LOGLEVEL_FATAL, 'goMail->SendMail() ~~ ERROR:'.$e);
			
			throw new StatusException($e->getMessage());
		}
		
		if(!$success){
			throw new StatusException("Could not send mail. Please check the logs.");
		}
		
		ZLog::Write(LOGLEVEL_DEBUG, 'endsend: '.var_export($success, true));
		
		return true;
	}
	
 /**
	* Searches for the emails on the server
	*
	* @param ContentParameter $cpo
	*
  * TODO: IMPLEMENT SEARCH IN SPECIFIC PARTS (SUBJECT, TO, FROM) 
	* @return array
	*/
	public function GetMailboxSearchResults($cpo) {
		ZLog::Write(LOGLEVEL_INFO,'ZPUSH2Search->GetMailboxSearchResults($cpo '.var_export($cpo,true).')');
		
		$imapAccount = $this->getImapAccount();
		if(!$imapAccount)
			return false;
												
		$searchwords = $cpo->GetSearchFreeText();
		// split the search on whitespache and look for every word
//		$searchwords = preg_split("/\W+/", $searchwords);
		
		$searchFolder = $cpo->GetSearchFolderid(); // RESULTS IN "m/INBOX" OR "m/Concepten"
		if(!$searchFolder) {
			//happens when searching "All folders" on iphone but we don't support this yet.
			$searchFolder = 'INBOX';
		} else {
			$searchFolder = substr($searchFolder, 2); // REMOVE THE "m/" from the folder id
		}
		
		// Build the imap search query
		$searchData = $cpo->GetData();
//		$imapSearchQuery = new \GO\Email\Model\ImapSearchQuery();
//		
//		foreach($searchwords as $word){
//			$imapSearchQuery->addSearchWord($word, \GO\Email\Model\ImapSearchQuery::FROM);
//			$imapSearchQuery->addSearchWord($word, \GO\Email\Model\ImapSearchQuery::SUBJECT);
//			$imapSearchQuery->addSearchWord($word, \GO\Email\Model\ImapSearchQuery::TO);
//			$imapSearchQuery->addSearchWord($word, \GO\Email\Model\ImapSearchQuery::CC);
//		}
//		
		
		
		$query = 'OR OR OR FROM "'.$searchwords.'" SUBJECT "'.$searchwords.'" TO "'.$searchwords.'" CC "'.$searchwords.'"';
		
		if(isset($searchData['searchdatereceivedgreater']) && $searchData['searchdatereceivedgreater']){
			$searchGreater = strtotime($cpo->GetSearchValueGreater());
//			$imapSearchQuery->searchSince($searchGreater); // $searchGreater
			
			$query .= ' SINCE '.date('d-M-Y',$searchGreater);
		}
////		
		if(isset($searchData['searchdatereceivedless']) && $searchData['searchdatereceivedless']){
			$searchLess = strtotime($cpo->GetSearchValueLess());
//			$imapSearchQuery->searchBefore($searchLess); // $searchLess
			$query .= ' BEFORE '.date('d-M-Y',$searchLess);
		}

//		$query = $imapSearchQuery->getImapSearchQuery();
		
//		$query = ' FROM "kadaster"  OR TO "kadaster"  OR CC "kadaster"  OR SUBJECT "kadaster" BEFORE 28-Feb-2013 SINCE 28-Aug-2012 ';
//		
		ZLog::Write(LOGLEVEL_INFO,'QUERY ~~ '.var_export($query,true));
		
		$maxPageSize = 30;
		
		$searchrange = $cpo->GetSearchRange();
		$rangestart = 0;
		$rangeend = $maxPageSize;

		if($searchrange != '0') {
			$pos = strpos($searchrange, '-');
			$rangestart = substr($searchrange, 0, $pos);
			$rangeend = substr($searchrange, ($pos + 1));
		}
		
		if($rangeend-$rangestart>$maxPageSize){
			$rangeend=$rangestart+$maxPageSize;
		}
		

		$messages = \GO\Email\Model\ImapMessage::model()->find(
					$imapAccount, 
					$searchFolder,
					$rangestart, 
					$rangeend-$rangestart,
					\GO\Base\Mail\Imap::SORT_DATE, 
					true, 
					$query);
		
		$items = array();
		$items['searchtotal'] = $imapAccount->getImapConnection()->sort_count;
    $items["range"] = $rangestart.'-'.$rangeend;
		
		foreach($messages as $message){
			$items[] = array(
					'class' => 'Email',
					'longid' => 'm/'.$searchFolder.':'.$message->uid,
					'folderid' => 'm/'.$searchFolder
			);
		}		
		return $items;
	}
	



	public function getNotification($f='INBOX') {
		ZLog::Write(LOGLEVEL_DEBUG,'goMail->getNotification('.$f.')');
		
		$state="M:0-U:0";
	
		if ($imap = $this->_imapLogon($f)) {
			
			$status = $imap->get_status($f);
			if ($status) {
				$state = "M:" . $status['messages'] . "-U:" . $status['unseen'];
			}

			ZLog::Write(LOGLEVEL_DEBUG,'goMail->getNotification('.$f.') State: '.$state);

			//disconnect from imap because otherwise we may exceed the max number of connections
			$imap->disconnect();
		}
		return $state;
	}

}

