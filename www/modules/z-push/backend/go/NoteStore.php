<?php

use go\core\model\Acl;
use go\core\util\StringUtil;
use go\modules\community\notes\model\Note;



class NoteStore extends Store {

	public function DeleteMessage($folderid, $id, $contentparameters) {

		if(!go()->getAuthState()->getUser(['syncSettings'])->syncSettings->allowDeletes) {
			ZLog::Write(LOGLEVEL_DEBUG, 'Deleting by sync is disabled in user settings');
			throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
		}

		try {
			ZLog::Write(LOGLEVEL_DEBUG, 'goNote->DeleteMessage(' . $folderid . ',' . $id . ')');
			$note = Note::findById($id);

			if ($note && $note->hasPermissionLevel(Acl::LEVEL_DELETE)) {
				return $note->delete($note->primaryKeyValues());
			} else {
				return true;
			}
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Note::EXCEPTION ~~ ' .  $e->getMessage());
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
	 * @param int $folderid
	 * @param int $id
	 * @param array $contentparameters
	 * @return \SyncNote
	 */
	public function GetMessage($folderid, $id, $contentparameters) {

		try {
			ZLog::Write(LOGLEVEL_DEBUG, 'goNote->GetMessage(' . $folderid . ',' . $id . ')');
			$note = Note::findById($id);

			if (!$note) {
				return false;
			}

			if (!$note->hasPermissionLevel(Acl::LEVEL_READ)) {
				return false;
			}

			$message = new SyncNote();

			$bpReturnType = GoSyncUtils::getBodyPreferenceMatch($contentparameters->GetBodyPreference());

			if (Request::GetProtocolVersion() >= 12.0) {
				$sbBody = new SyncBaseBody();

				$asBodyData = StringUtil::normalizeCrlf($note->content);

				if ($bpReturnType == SYNC_BODYPREFERENCE_HTML) {
					$sbBody->type = SYNC_BODYPREFERENCE_HTML;
					$asBodyData = $note->content;
				} else {

					$sbBody->type = SYNC_BODYPREFERENCE_PLAIN;
					$asBodyData = StringUtil::htmlToText($note->content);
				}
				ZLog::Write(LOGLEVEL_DEBUG, $asBodyData);

				$sbBody->estimatedDataSize = strlen($asBodyData);
				$sbBody->data = StringStreamWrapper::Open($asBodyData);
				$sbBody->truncated = 0;

				$message->asbody = $sbBody;

			} else {
				$message->body = StringUtil::normalizeCrlf($note->content);
				$message->bodysize = strlen($message->body);
				$message->bodytruncated = 0;

			}

			$message->lastmodified = $note->modifiedAt->format('U');
			$message->subject = $note->name;


			return $message;
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Note::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}
	}

	/**
	 * Save the information from the phone to Group-Office.
	 * 
	 * Direction: PHONE -> SERVER
	 * 
	 * @param int $folderid
	 * @param int $id
	 * @param \SyncNote $message
	 * @return array
	 */
	public function ChangeMessage($folderid, $id, $message, $contentParameters) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goNote->ChangeMessage('.$folderid.','.$id.')');

		try {

			$note = Note::findById($id);

			if(!$note) {
				$note = new Note ();
				$note->noteBookId = $folderid;//(new \go\core\db\Query)->selectSingleValue('noteBookId')->from('sync_user_note_book')->where(['userId' => go()->getUserId()])->orderBy(['isDefault' => 'DESC'])->single();
			}

			if(!$note->hasPermissionLevel(Acl::LEVEL_WRITE)) {
				throw new StatusException("Access denied", SYNC_ITEMOPERATIONSSTATUS_DL_ACCESSDENIED);
			}

			$note->content = "";

			if(isset($message->asbody) && isset($message->asbody->type)){
				switch($message->asbody->type){
					case SYNC_BODYPREFERENCE_PLAIN:
						$note->content = StringUtil::textToHtml(stream_get_contents($message->asbody->data));
						break;
					case SYNC_BODYPREFERENCE_HTML:
						$note->content = stream_get_contents($message->asbody->data);
						break;
					case SYNC_BODYPREFERENCE_RTF:
						$rtfparser = new z_RTF();
						$rtfparser->loadrtf(base64_decode(stream_get_contents($message->asbody->data)));
						$rtfparser->output("html");
						$rtfparser->parse();
						$note->content = $rtfparser->out;
						break;
				}
			}

			if(!empty($message->subject))
			{
				$note->name	=  $message->subject;
			} else if($note->isNew()) {
				$note->name = StringUtil::cutString(strip_tags($note->content), 20);
			}

			$note->cutPropertiesToColumnLength();

			if(!$note->save()){
				ZLog::Write(LOGLEVEL_WARN, 'ZPUSH2NOTE::Could not save ' . $note->id);
				ZLog::Write(LOGLEVEL_WARN, var_export($note->getValidationErrors(), true));
				throw new StatusException("Could not save note", SYNC_STATUS_SERVERERROR);
			}
		} catch(Exception $e) {
			ZLog::Write(LOGLEVEL_FATAL, 'Note::EXCEPTION ~~ ' .  $e->getMessage());
			ZLog::Write(LOGLEVEL_DEBUG, $e->getTraceAsString());
			return false;
		}


		return $this->StatMessage($folderid, $note->id);

	}
			
	/**
	 * Get the status of an item
	 * 
	 * @param int $folderid
	 * @param int $id
	 * @return array
	 */
	public function StatMessage($folderid, $id) {
		ZLog::Write(LOGLEVEL_DEBUG, 'goNote->StatMessage('.$folderid.','.$id.')');
		
		return Note::find()
						->select('id,unix_timestamp(modifiedAt) AS `mod`, "1" AS `flags`')
						->fetchMode(PDO::FETCH_ASSOC)
						->where(['id' => $id])->single();	
	}
	
	/**
	 * Get the list of the items that need to be synced
	 * 
	 * @param int $folderid
	 * @param int $cutoffdate
	 * @return array
	 */
	public function GetMessageList($folderid, $cutoffdate) {		
		ZLog::Write(LOGLEVEL_DEBUG, 'goNote->GetMessageList('.$folderid.','.$cutoffdate.')');
		//if(!go()->getUser()->hasModule('notes')) {
		//TODO refactor
		if (!\GO::modules()->notes) {
			return [];
		}
		$query = Note::find()
						->select('id,unix_timestamp(modifiedAt) AS `mod`, "1" AS `flags`')
						->fetchMode(PDO::FETCH_ASSOC)
						->where('noteBookId', '=', $folderid);
//						->join("sync_user_note_book", 's', 'n.noteBookId = s.noteBookId')
//						->where(['s.userId' => go()->getUserId(), 'password' => ""]);
//		ZLog::Write(LOGLEVEL_DEBUG, $query->debugQueryString);
		$notes = $query->all();	
		
		
		
//		ZLog::Write(LOGLEVEL_DEBUG, var_export($notes, true));
		
		return $notes;
	}
	
	/**
	 * Get the syncFolder that is attached to the given id
	 * 
	 * @param int $id
	 * @return \SyncFolder
	 */
	public function GetFolder($id) {

		$notebook = \go\modules\community\notes\model\NoteBook::findById($id);
		if(!$notebook) {
			ZLog::Write(LOGLEVEL_WARN, "Note folder '$id' not found");
			return false;
		}

		$folder = new SyncFolder();
		$folder->serverid = $id;
		$folder->parentid = "0";
		$folder->displayname = $notebook->name;
		$folder->type = SYNC_FOLDER_TYPE_NOTE;

		return $folder;
	}

	/**
	 * Get a list of folders that are located in the current folder
	 * 
	 * @return array
	 */
	public function GetFolderList() {
		$folders = array();
		$notebooks = \go\modules\community\notes\model\NoteBook::find()
			->selectSingleValue('nb.id')
			->join("sync_user_note_book", "u", "u.noteBookId = nb.id")
			->andWhere('u.userId', '=', go()->getAuthState()->getUserId())
			->filter([
				"permissionLevel" => Acl::LEVEL_READ
			])->all();

		foreach($notebooks as $id) {
			$folder = $this->StatFolder($id);
			$folders[] = $folder;
		}

		return $folders;
	}
	
	public function getNotification($folder=null) {
		ZLog::Write(LOGLEVEL_DEBUG,'goNote->getNotification('.$folder.')');
		$stmt = Note::find()
				->fetchMode(PDO::FETCH_ASSOC)
				->select('COALESCE(count(*), 0) AS count, COALESCE(max(modifiedAt), 0) AS modifiedAt')
				->where('n.noteBookId = :noteBookId')
				->createStatement();

		$stmt->bindValue(':noteBookId', $folder, PDO::PARAM_INT);
		$stmt->execute();
		$record = $stmt->fetch();

		$newstate = 'M'.$record['modifiedAt'].':C'.$record['count'];
		ZLog::Write(LOGLEVEL_DEBUG,'goNote->getNotification('.$folder.') State: '.$newstate);

		return $newstate;
	}
	
}
