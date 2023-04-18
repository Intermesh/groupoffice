<?php
class ChangeImporter extends ChangeState implements IImportChanges {

	//private $conflict;



	/**
	 * Loads objects which are expected to be exported with the state
	 * Before importing/saving the actual message from the mobile, a conflict detection should be done
	 *
	 * @param ContentParameters $contentparameters
	 * @param string $state
	 */
	public function LoadConflicts($contentparameters, $state) : bool
	{

//		$this->conflict = [
//			'loaded' => false,
//			'params' => $contentparameters,
//			'state' => $state
//		];
//
//		ZLog::Write(LOGLEVEL_DEBUG, "ChangeImporter->LoadConflicts(): will be loaded later if necessary");
		return true;
	}

	/**
	 * Imports a single message from mobile
	 *
	 * @param string $id
	 * @param SyncObject $object
	 * @return boolean | string               failure | id of message
	 * @throws StatusException
	 */
	public function ImportMessageChange($id, $object)
	{
		if($id) {
			// See if there's a conflict
			$conflict = $this->isConflict("change", $this->folderId, $id);

			// Update client state if this is an update
			$this->updateState("change", [
				'id' => $id,
				'parent' => $this->folderId,
				'mod' => 0,
				'flags' => $object->read ?? 0
			]);

			if($conflict && $this->flags == SYNC_CONFLICT_OVERWRITE_PIM)
				// in these cases the status SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT should be returned, so the mobile client can inform the end user
				throw new StatusException(sprintf("ChangeImporter->ImportMessageChange('%s','%s'): Conflict detected. Data from PIM will be dropped! Server overwrites PIM. User is informed.", $id, get_class($object)),
					SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT, null, LOGLEVEL_INFO);
		}

		// the actual change being made here
		$change = $this->store->ChangeMessage($this->folderId, $id, $object, $this->contentParams);

		if(!is_array($change))
			throw new StatusException(sprintf("ChangeImporter->ImportMessageChange('%s','%s'): unknown error in store", $id, get_class($object)), SYNC_STATUS_SYNCCANNOTBECOMPLETED);

		// Record the state of the message
		$this->updateState("change", $change);

		return $change["id"];
	}

	/**
	 * Imports a deletion. This may conflict if the local object has been modified.
	 *
	 * @param string $id
	 * @param boolean $asSoftDelete (opt) if true, the deletion is exported as "SoftDelete", else as "Remove" - default: false
	 */
	public function ImportMessageDeletion($id, $asSoftDelete = false): bool
	{
		// See if there's a conflict
		$conflict = $this->isConflict("delete", $this->folderId, $id);

		// Update client state
		$this->updateState("delete", ['id' => $id]);

		// If there is a conflict, and the server 'wins', then return without performing the change
		// this will cause the exporter to 'see' the overriding item as a change, and send it back to the PIM
		if($conflict && $this->flags == SYNC_CONFLICT_OVERWRITE_PIM) {
			ZLog::Write(LOGLEVEL_INFO, sprintf("ChangeImporter->ImportMessageDeletion('%s'): Conflict detected. Data from PIM will be dropped! Object was deleted.", $id));
			return false;
		}

		$stat = $this->store->DeleteMessage($this->folderId, $id, $this->contentParams);
		if(!$stat)
			throw new StatusException(sprintf("ChangeImporter->ImportMessageDeletion('%s'): Unknown error in store", $id), SYNC_STATUS_OBJECTNOTFOUND);

		return true;
	}

	/**
	 * Imports a change in 'read' flag
	 * This can never conflict
	 *
	 * @param string $id
	 * @param int $flags
	 * @param array $categories
	 */
	public function ImportMessageReadFlag($id, $flags, $categories = []): bool
	{
		// Update client state
		$this->updateState("flags", [
			'id' => $id,
			'flags' => $flags
		]);

		$stat = $this->store->SetReadFlag($this->folderId, $id, $flags, $this->contentParams);
		if (!$stat)
			throw new StatusException(sprintf("ChangeImporter->ImportMessageReadFlag('%s','%s'): Error, unable retrieve message from store", $id, $flags), SYNC_STATUS_OBJECTNOTFOUND);

		return true;
	}

	/**
	 * Imports a move of a message. This occurs when a user moves an item to another folder
	 *
	 * @param string $id
	 * @param string $newfolder destination folder
	 */
	public function ImportMessageMove($id, $newfolder): bool
	{
		return $this->store->MoveMessage($this->folderId, $id, $newfolder, $this->contentParams);
	}

	/**
	 * Imports a change on a folder
	 *
	 * @param SyncFolder $folder SyncFolder
	 *
	 * @access public
	 * @return boolean | SyncObject           status | object with the at least the serverid of the folder set
	 * @throws StatusException
	 */
	public function ImportFolderChange($folder)
	{
		$id = $folder->serverid;

		if($id) { // not new
			$this->updateState("change", [
				'id' => $id,
				'mod' => $folder->displayname,
				'parent' => $folder->parentid,
				'flags' => $folder->type
			]);
		}

		$stat = $this->store->ChangeFolder($folder->parentid, $id, $folder->displayname, $folder->type);

		if($stat)
			$this->updateState("change", $stat);

		$folder->serverid = $stat["id"]; // creationId to Id?
		return $folder;
	}

	/**
	 * Imports a folder deletion
	 *
	 * @param SyncFolder $folder at least "serverid" needs to be set
	 *
	 * @access public
	 * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
	 * @throws StatusException
	 */
	public function ImportFolderDeletion($folder)
	{
		$id = $folder->serverid;
		$parent = isset($folder->parentid) ? $folder->parentid : false;

		// check the foldertype
		$folder = $this->store->GetFolder($id);
		if (isset($folder->type) && Utils::IsSystemFolder($folder->type))
			throw new StatusException(sprintf("ChangeImporter->ImportFolderDeletion('%s','%s'): Error deleting system/default folder", $id, $parent), SYNC_FSSTATUS_SYSTEMFOLDER);

		$ret = $this->store->DeleteFolder($id, $parent);
		if (!$ret)
			throw new StatusException(sprintf("ChangeImporter->ImportFolderDeletion('%s','%s'): can not be deleted", $id, $parent), SYNC_FSSTATUS_FOLDERDOESNOTEXIST);

		$this->updateState("delete", ['id' => $id]);

		return true;
	}

	/**
	 * Returns TRUE if the given ID conflicts with the given operation. This is only true in the following situations:
	 *   - Changed here and changed there
	 *   - Changed here and deleted there
	 *   - Deleted here and changed there
	 * Any other combination of operations can be done (e.g. change flags & move or move & delete)
	 *
	 * @param string        $type of change
	 * @param string        $folderid
	 * @param string        $id
	 */
	protected function isConflict($type, $folderid, $id) : bool {
		$stat = $this->store->StatMessage($folderid, $id);

		if(!$stat) {
			// Message is gone
			return ($type === "change") ?
				true : // deleted here, but changed there
				false; // all other remote changes still result in a delete (no conflict)
		}

		foreach($this->state as $state) {
			if($state["id"] == $id) {
				$oldstat = $state;
				break;
			}
		}

		if(!isset($oldstat)) {
			return false; // New message, can never conflict
		}

		if($stat["mod"] != $oldstat["mod"]) {
			// Changed here
			return ($type === "delete" || $type === "change") ?
				true : // changed here, but deleted there -> conflict, or changed here and changed there -> conflict
				false; // changed here, and other remote changes (move or flags)
		}
		return false;
	}
}