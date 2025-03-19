<?php

use go\modules\community\calendar\model\Scheduler;

class MailStore extends Store implements ISearchProvider {

	
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
     * @param string        $folderid       id of the parent folder
     * @param string        $oldid          if empty -> new folder created, else folder is to be renamed
     * @param string        $displayname    new folder name (to be created, or to be renamed to)
     * @param int           $type           folder type
     *
     * @access public
     * @return boolean                      status
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
	public function ChangeFolder($folderid, $oldid, $displayname, $type) {
		ZLog::Write(LOGLEVEL_INFO, sprintf("goMail->ChangeFolder('%s','%s','%s','%s')", $folderid, $oldid, $displayname, $type));

		try {
			// go to parent mailbox
			//$this->imap_reopenFolder($folderid);

			$imap = $this->_imapLogon();

			//remove m/ from the combined stuff
			if (!empty($folderid)) {
				$folderid = substr($folderid, 2);

				// build name for new mailboxBackendMaildir
				$newname = $folderid . $imap->get_mailbox_delimiter() . $displayname;
			} else {
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
				ZLog::Write(LOGLEVEL_INFO, "Create: " . $newname);
				$csts = $imap->create_folder($newname);
			}
			if ($csts) {

				//refresh cached folder list
				$this->GetFolderList();

				return $this->StatFolder($newname);
			} else
				return false;
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2MAIL::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}
	}

	/**
	 * Get the item object that needs to be synced to the phone.
	 * This information will be send to the phone.
	 * 
	 * Direction: SERVER -> PHONE
	 * 
	 * @param string $folderid
	 * @param int $id
	 * @param SyncParameters $contentparameters
	 * @return \SyncMail
	 */
	public function GetMessage($folderid, $id, $contentparameters) {

		try {
			$truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());

			ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::truncsize = ' . $truncsize);

			//TODO: implement MIME mails
			//$mimesupport = $contentparameters->GetMimeSupport();

			if (!$this->_imapLogon($folderid))
				return false;

			$mailbox = $this->_replaceDotWithServerDelimiter($folderid);
			$imapAccount = $this->getImapAccount();
			if (!$imapAccount)
				return false;

			// Hack to make Inbox also work
			if ($folderid == 'Inbox')
				$mailbox = 'INBOX';

			$message = new SyncMail(); // Create new syncmail object

			//attachements that are too big to send over.
			$this->_tooBigAttachments = array();


			$imapMessage = \GO\Email\Model\ImapMessage::model()->findByUid($imapAccount, $mailbox, $id);
			if ($imapMessage) {

				$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference(), array(SYNC_BODYPREFERENCE_MIME, SYNC_BODYPREFERENCE_PLAIN, SYNC_BODYPREFERENCE_HTML));
				ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::bpReturnType = ' . $bpReturnType);

				if (Request::GetProtocolVersion() >= 12.0) {

					$message->asbody = new SyncBaseBody();
					$asBodyData = null;
					switch ($bpReturnType) {
						case SYNC_BODYPREFERENCE_PLAIN:
							$asBodyData = $imapMessage->getPlainBody();
							$asBodyData .= $this->_getTooBigAttachmentsString();

							break;
						case SYNC_BODYPREFERENCE_HTML:
							$asBodyData = \GO\Base\Util\StringHelper::normalizeCrlf($imapMessage->getHtmlBody()) . nl2br($this->_getTooBigAttachmentsString());
							break;
						case SYNC_BODYPREFERENCE_MIME:
							//we load the mime message and create a new mime string since we can't trust the IMAP source. It often contains wrong encodings that will crash the
							//sync. eg. incredimail.
							$source = $imapMessage->getSource();

							if (!strpos($source, "pkcs7-mime")) {
								ZLog::Write(LOGLEVEL_DEBUG, 'Recreating MIME source');

								try {
									$sendMessage = \GO\Base\Mail\Message::newInstance()->loadMimeMessage($source, true);
									$asBodyData = $sendMessage->toString();
								} catch (Exception $e) {
									ZLog::Write(LOGLEVEL_ERROR, "Failed to recreate mime source. Falling back to original mime. Subject: " . $imapMessage->subject . " Exception: " . $e->getMessage());
									$asBodyData = $source;
								}

							} else {

								ZLog::Write(LOGLEVEL_DEBUG, 'Passing through orignal IMAP MIME source for SMIME');
								$asBodyData = $source;
							}
							break;
						case SYNC_BODYPREFERENCE_RTF:
							ZLog::Write(LOGLEVEL_DEBUG, "BackendIMAP->GetMessage RTF Format NOT CHECKED");
							$asBodyData = base64_encode(\GO\Base\Util\StringHelper::normalizeCrlf($imapMessage->getPlainBody()) . $this->_getTooBigAttachmentsString());
							break;
					}


					//attachments are not necessary when using mime
					//
					//Attachments probably need to be sent even with MIME type:
					//http://talk.sonymobile.com/t5/Xperia-Z1-Compact/Z1-Compact-Problem-With-EAS/m-p/866755#11220
					//zpush_always_send_attachments config setting is for testing this carefully.
//				 if($bpReturnType!=SYNC_BODYPREFERENCE_MIME || !empty(\GO::config()->zpush_always_send_attachments)){
					$message->asattachments = $this->_getASAttachments($imapMessage, $id, $mailbox, $bpReturnType != SYNC_BODYPREFERENCE_PLAIN ? $asBodyData : null);
//				 }

					// truncate body, if requested
					//MS: Not sure if !empty($truncsize) is needed here. Testing for Robert S.
					if (!empty($truncsize) && $bpReturnType != SYNC_BODYPREFERENCE_MIME && strlen($asBodyData) > $truncsize) {
						$asBodyData = Utils::Utf8_truncate($asBodyData, $truncsize);
						$message->asbody->truncated = 1;
					} else {
						$message->asbody->truncated = 0;
					}
					$message->asbody->data = version_compare(ZPUSH_VERSION, '2.3', '<') ? $asBodyData : StringStreamWrapper::Open($asBodyData);
					$message->asbody->type = $bpReturnType;
//				$message->nativebodytype = $bpReturnType; //This casued outlook 2013 to fail!!
					$message->asbody->estimatedDataSize = strlen($asBodyData);

					$bpo = $contentparameters->BodyPreference($message->asbody->type);
					if (Request::GetProtocolVersion() >= 14.0 && $bpo->GetPreview()) {
						if (!isset($plainBody))
							$plainBody = $imapMessage->getPlainBody();

						$textPreview = isset($plainBody) ? $plainBody : Utils::ConvertHtmlToText($message->asbody->data);

						$message->asbody->preview = Utils::Utf8_truncate($textPreview, $bpo->GetPreview());
					}
				} else {


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
						$message->attachments = $this->_getNormalAttachments($imapMessage, $id, $mailbox);

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
				$message->from = (string)$imapMessage->from;

				// Addressing block
				$message->to = $imapMessage->to->getArray();
				$message->cc = $imapMessage->cc->getArray();
				$message->bcc = $imapMessage->bcc->getArray();
				$message->reply_to = $imapMessage->reply_to->getArray();

				$firstTo = $imapMessage->to->getAddress();

				if ($firstTo)
					$message->displayto = $firstTo['personal'];

				// End of addressing block

				if (isset($imapMessage->x_priority)) {
					$mimeImportance = $imapMessage->x_priority;
					if ($mimeImportance > 3)
						$message->importance = 0;
					if ($mimeImportance == 3)
						$message->importance = 1;
					if ($mimeImportance < 3)
						$message->importance = 2;
				} else {
					$message->importance = 1;
				}

				$message->internetcpid = INTERNET_CPID_UTF8;
				if (Request::GetProtocolVersion() >= 12.0) {
					$message->contentclass = "urn:content-classes:message";
				}

				if($imapMessage->mailbox != $imapAccount->sent) {
					$this->processCalendarInvite($message, $imapMessage);
				}

				$imapMessage->autoLink();

				unset($imapMessage);
			}

			//Uses lots of mem:
			//ZLog::Write(LOGLEVEL_DEBUG, 'goMail->GetMessage::MESSAGE = '.var_export($message,true));

			return $message;
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2MAIL::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}
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
	private function _getASAttachments($imapMessage,$msgId,$mailbox, $body){
		$asAttachments = array();
		$attachments = $imapMessage->getAttachments();
		
		foreach ($attachments as $attachment) {

			if($attachment->isVcalendar()) {
				continue; // handled by meeting response
			}
			
			if($attachment->size>$this->_getMaxAttachmentSize()){
				array_push($this->_tooBigAttachments, array('name'=>$attachment->name,'size'=>$attachment->size));
			}else
			{
				$attach = new SyncBaseAttachment();
				$attach->displayname = $attachment->name;
				$attach->filereference = $this->_encodeFileReference($msgId,$attachment,$mailbox);
				$attach->method = 1;
				$attach->estimatedDataSize = $attachment->getEstimatedSize();

				if(!isset($body)) {
					$inline = false;
				} else{
					$inline = $attachment->isInline() && !empty($attachment->content_id) && strpos($body, $attachment->content_id) !== false;
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
			throw new StatusException("Malformed attachment name '$attname' in GetAttachmentData!", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
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
			throw new StatusException("Unable to connect to IMAP server in GetAttachmentData!", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
		
		$tmpfile = \GO\Base\Fs\File::tempFile();
		$this->_tmpFiles[]=$tmpfile;
		

		if(!$imap->save_to_file($uid, $tmpfile->path(), $part, $enc)){
			throw new StatusException("Failed to save attachment", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
		}
		
		$fp = fopen($tmpfile->path(),'r');
		
		if(!$fp){
			throw new StatusException("Could not open attachment file stream", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
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
	 * @param string $folderid
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

		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2MAIL::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
		}

		return $this->StatMessage($folderid, $id);
	}
	
	/**
	 * Flag a message as read
	 * 
	 * @param string $folderid
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
	 * @param string $folderid
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
	 * @param string $folderid
	 * @param int $id
	 * @param string $newfolderid
	 * @return boolean
	 */
	public function MoveMessage($folderid, $id, $newfolderid, $contentparameters) {
		
		ZLog::Write(LOGLEVEL_DEBUG, "goMail::MoveMessage($folderid, $id, $newfolderid)");
		
		$imap = $this->_imapLogon($folderid);
		if(!$imap)
			return false;

		$uidnext = $imap->get_uidnext();
		
		if(!$imap->move(array($id), $this->_replaceDotWithServerDelimiter($newfolderid))) {
			return false;
		}
		ZLog::Write(LOGLEVEL_DEBUG, "goMail::MoveMessage() = " . $uidnext);

		return $uidnext . "";
	}
	
	/**
	 * Get the status of an item
	 * 
	 * @param string $folderid
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
	 * @param string $folderid
	 * @param type $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {

//		\GO\Base\Mail\ImapBase::$debug = true;
//		GO()->getDebugger()->enable();

		try {
			ZLog::Write(LOGLEVEL_DEBUG, "GetMessageList($folderid, $cutoffdate)");
			$messages = array();

			if (GO::modules()->email) {
				if ($imap = $this->_imapLogon($folderid)) {
					//Don't fetch all messages with an empty cutoffdate because
					//this may kill the server. Default to two weeks.
					if (empty($cutoffdate)) {
						ZLog::Write(LOGLEVEL_DEBUG, "empty cutoff");
						$headers = $imap->get_flags();
					} else {
						ZLog::Write(LOGLEVEL_DEBUG, 'Client sent cutoff date for email: ' . date("j-M-Y", $cutoffdate));
						$uids = $imap->search('SINCE ' . date("j-M-Y", $cutoffdate));
						if (empty($uids)) {
							return [];
						}
//						$uidRange = min($uids) . ':*';
						ZLog::Write(LOGLEVEL_DEBUG, 'GET UID RANGE: ' . implode(", ", $uids). ', count: '. count($uids));
						$headers = $imap->get_flags($uids);
					}

					if ($headers === false) {
						ZLog::Write(LOGLEVEL_ERROR, "IMAP returned error response" . $imap->last_error());
						return [];
					}

					ZLog::Write(LOGLEVEL_DEBUG, "message count:" . count($headers));

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
		} catch (\Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'ZPUSH2MAIL::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return [];
		}
	}

	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param string $id
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
			if(!empty($oldMessageFolderID) && substr($oldMessageFolderID, 0, 2) == 'm/')
				$oldMessageFolderID = substr($oldMessageFolderID, 2);
			
			$imapAccount = $this->getImapAccount();
			if(!$imapAccount){
				
				ZLog::Write(LOGLEVEL_ERROR, 'NO IMAP account set for this user. No meeting request was sent.');
				return true; //if we throuw status exception here then iphone will fail with a wbxmlexception
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, 'beforeloadmime');
//			ZLog::Write(LOGLEVEL_DEBUG, var_export($sm->mime, true));
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
					$sendMessage->setInReplyTo($refImapMessage->message_id);
					$sendMessage->setReferences($refImapMessage->message_id);
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
							$att = \go\core\mail\Attachment::fromPath($file->path(),$file->mimeType());
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
			$sendMessage->getMailer()->setEmailAccount($imapAccount);
			ZLog::Write(LOGLEVEL_DEBUG, 'beforesend');
			$sendMessage->send();
			ZLog::Write(LOGLEVEL_DEBUG, 'goMail->SendMail()~~SEND~~'.$success);
			//if a sent items folder is set in the account then save it to the imap folder

			$imapAccount->saveToSentItems($sendMessage);

			ZLog::Write(LOGLEVEL_DEBUG, 'MAIL IS SENT SUCCESSFULLY::::::::'.$success);
		}
		catch (Exception $e){
			ZLog::Write(LOGLEVEL_FATAL, 'goMail->SendMail() ~~ ERROR:'.$e);
			
			throw new StatusException($e->getMessage(), SYNC_COMMONSTATUS_MAILSUBMISSIONFAILED);
		}

		ZLog::Write(LOGLEVEL_DEBUG, 'endsend: '.var_export($success, true));
		
		return true;
	}


	/**
	 * Microsoft AQS
	 *
	 * https://support.microsoft.com/en-us/office/search-mail-and-people-in-outlook-com-88108edf-028e-4306-b87e-7400bbb40aa7?ui=en-us&rs=en-us&ad=us
	 * @param string $text
	 * @return void
	 */
	private function parseSearchFreeText(string $text) {
		//to:"Test" OR cc:"Test" OR from:"Test" OR subject:"Test" OR "Test"
		//

		if(preg_match('/"([^"]+)"/', $text, $matches)) {
			return $matches[1];
		} else{
			return $text;
		}

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

		$searchwords = $this->parseSearchFreeText($searchwords);
		
		$searchFolder = $cpo->GetSearchFolderid(); // RESULTS IN "m/INBOX" OR "m/Concepten"
		if(!$searchFolder) {
			//happens when searching "All folders" on iphone but we don't support this yet.
			$searchFolder = 'INBOX';
		} else {

			if(!empty($searchFolder) && substr($searchFolder, 0, 2) == 'm/')
				$searchFolder = substr($searchFolder, 2);// REMOVE THE "m/" from the folder id
		}
		
		// Build the imap search query
		$searchData = $cpo->GetData();

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

    ZLog::Write(LOGLEVEL_INFO,'QUERY ~~ '.var_export($query,true) . ' range: ' . $rangestart.' - '. $rangeend);

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

  public function SupportsType($searchtype)
  {
      return ($searchtype == ISearchProvider::SEARCH_MAILBOX);
  }

  public function GetGALSearchResults($searchquery, $searchrange, $searchpicture)
  {
      return false;
  }

  public function TerminateSearch($pid)
  {
      return true;
  }

  public function Disconnect()
  {
      // Don't close the mailbox, we will need it open in the Backend methods
      return true;
  }

	private function processCalendarInvite(SyncMail $message, \GO\Email\Model\ImapMessage $imapMessage)
	{
		$vcalendar = $imapMessage->getInvitationVcalendar();
		if(!$vcalendar) {
			return;
		}

		Scheduler::handleIMIP($imapMessage);

		$message->meetingrequest = new SyncMeetingRequest();
		$method = $vcalendar->method ? strtolower($vcalendar->method->getValue()) : "request";
		$vevent = $vcalendar->vevent[0];

		switch ($method) {
			case "cancel":
				$message->messageclass = "IPM.Schedule.Meeting.Canceled";
				$message->meetingrequest->meetingmessagetype = 2;
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Event canceled, removing calendar object");

				break;
			case "declinecounter":
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Declining a counter is not implemented.");
			case "counter":
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Counter received");
				$message->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
				break;
			case "reply":

				$message->meetingrequest->meetingmessagetype = 2;

				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Reply received");

				$attendee = $vevent->attendee;
				$status = strtolower($attendee->partstat ?? "needs-action");

				// Only set messageclass for replies changing my calendar object
				switch ($status) {
					case "accepted":
						$message->messageclass = "IPM.Schedule.Meeting.Resp.Pos";
						ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Update attendee -> accepted");
						break;
					case "needs-action":
						$message->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
						ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Update attendee -> needs-action");
						break;
					case "tentative":
						$message->messageclass = "IPM.Schedule.Meeting.Resp.Tent";
						ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Update attendee -> tentative");
						break;
					case "declined":
						$message->messageclass = "IPM.Schedule.Meeting.Resp.Neg";
						ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Update attendee -> declined");
						break;
					default:
						ZLog::Write(LOGLEVEL_WARN, sprintf("MailStore::processCalendarInvite() - Unknown reply status <%s>, please report it to the developers"));
						$message->messageclass = "IPM.Appointment";
						break;
				}

				$message->meetingrequest->disallownewtimeproposal = "1";
				break;
			case "request":
				$message->meetingrequest->meetingmessagetype = 1;
				$message->messageclass = "IPM.Schedule.Meeting.Request";
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): New request");
					break;
			case "add":
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Add method is not implemented.");
				$message->messageclass = "IPM.Appointment";
				break;
			case "publish":
				ZLog::Write(LOGLEVEL_DEBUG, "MailStore::processCalendarInvite(): Publish method is not a meeting request.");
				$message->messageclass = "IPM.Appointment";
				break;
			default:
				ZLog::Write(LOGLEVEL_WARN, sprintf("MailStore::processCalendarInvite() - Unknown method <%s>, please report it to the developers", $method));
				$message->messageclass = "IPM.Appointment";
				break;
		}

		if(isset($vevent->dtstamp)) {
			$message->meetingrequest->dtstamp = $vevent->dtstamp->getDateTime()->format("U");
		}
		$message->meetingrequest->globalobjid = base64_encode($vevent->uid);
		$message->meetingrequest->starttime = $vevent->dtstart->getDateTime()->format("U");
		$message->meetingrequest->alldayevent = $vevent->dtstart->hasTime();

		if(isset($vevent->dtend)) {
			$message->meetingrequest->endtime = $vevent->dtend->getDateTime()->format("U");
		} else if(isset($vevent->duration)) {
			try {
				$message->meetingrequest->endtime = $vevent->dtstart->getDateTime()->add(new DateInterval($vevent->duration));
			} catch(Exception $e) {
				ZLog::Write(LOGLEVEL_WARN, "Failed to add duration: " . $vevent->duration ." :" . $e->getMessage());
				$message->meetingrequest->endtime = $message->meetingrequest->starttime + 3600;
			}
		}else {
			$message->meetingrequest->endtime = $message->meetingrequest->starttime + 3600;
		}

		if(isset($vevent->organizer)) {
			$message->meetingrequest->organizer = str_ireplace("MAILTO:", "",$vevent->organizer);
		}

		if(isset($vevent->location)) {
			$message->meetingrequest->location = (string)$vevent->location;
		}
		if(!empty($vevent->CLASS)) {
			$privacy = array_flip(\go\modules\community\calendar\model\ICalendarHelper::$privacyMap)[(string)$vevent->CLASS] ?? 'public';
		}else {
			$privacy = "public";
		}

		$message->meetingrequest->sensitivity = ['public'=> '0', 'private' => '2', 'secret'=> '3'][$privacy];

		$timezone = !empty((string)$vevent->DTSTART['TZID']) ? (string)$vevent->DTSTART['TZID'] : 'Etc/UTC';
		$message->meetingrequest->timezone = CalendarConvertor::mstzFromTZID($timezone);

		$message->contentclass = "urn:content-classes:calendarmessage";

		// guess instancetype by checking for recurrence rules or ids
		if (isset($vevent->RRULE)) {
			$message->meetingrequest->instancetype = 1;
		}
		elseif (isset($vevent->{"RECURRENCE-ID"})) {
			$message->meetingrequest->instancetype = 2;
		}
		else {
			$message->meetingrequest->instancetype = 0;
		}

		$message->meetingrequest->responserequested = 1;

		//busy
		$message->meetingrequest->busystatus = "2";

		$message->meetingrequest->disallownewtimeproposal = "1";

// get intended busystatus
//$props = $ical->GetPropertiesByPath('VEVENT/X-MICROSOFT-CDO-INTENDEDSTATUS');
//if (count($props) == 1) {
//	switch ($props[0]->Value()) {
//		case "FREE":
//			$output->meetingrequest->busystatus = "0";
//			break;
//		case "TENTATIVE":
//			$output->meetingrequest->busystatus = "1";
//			break;
//		case "BUSY":
//			$output->meetingrequest->busystatus = "2";
//			break;
//		case "OOF":
//			$output->meetingrequest->busystatus = "3";
//			break;
//	}
//}
//elseif (count($props = $ical->GetPropertiesByPath('VEVENT/TRANSP')) == 1) {
//	switch ($props[0]->Value()) {
//		case "TRANSPARENT":
//			$output->meetingrequest->busystatus = "0";
//			break;
//		case "OPAQUE":
//			$output->meetingrequest->busystatus = "2";
//			break;
//	}
//}
//else {
//	$output->meetingrequest->busystatus = 2;
//}

//// is counter allowed
//$props = $ical->GetPropertiesByPath('VEVENT/X-MICROSOFT-DISALLOW-COUNTER');
//if (count($props) > 0) {
//	switch ($props[0]->Value()) {
//		case "TRUE":
//			$output->meetingrequest->disallownewtimeproposal = "1";
//			break;
//		case "FALSE":
//			$output->meetingrequest->disallownewtimeproposal = "0";
//			break;
//	}
//}
//
//// use reminder with smallest interval
//$props = $ical->GetPropertiesByPath('VEVENT/VALARM/TRIGGER');
//if (count($props) > 0) {
//	foreach ($props as $vAlarmTrigger) {
//		$vAlarmTriggerValue = $vAlarmTrigger->Value();
//		if ($vAlarmTriggerValue[0] == "-") {
//			$reminderSeconds = new DateInterval(substr($vAlarmTriggerValue, 1));
//			$reminderSeconds = $reminderSeconds->format("%s") + $reminderSeconds->format("%i") * 60 + $reminderSeconds->format("%h") * 3600 + $reminderSeconds->format("%d") * 86400;
//			if (!isset($output->meetingrequest->reminderSeconds) || $output->meetingrequest->reminder > $reminderSeconds) {
//				$output->meetingrequest->reminder = $reminderSeconds;
//			}
//		}
//	}
//}

	}

}
