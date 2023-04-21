<?php
class ChangeExporter extends ChangeState implements IExportChanges {

	private $changes;
	private $changeCount;

	private $importer;

	private $iter;


	public function GetChangeCount(): int
	{
		return $this->changeCount;
	}

	/**
	 * Sets the importer where the exporter will sent its changes to
	 * This exporter should also be ready to accept calls after this
	 *
	 * @param object        &$importer Implementation of IImportChanges
	 * @throws StatusException
	 */
	public function InitializeExporter(&$importer)
	{
		$this->iter = 0;
		$this->importer = $importer;

		// if no state fetch all (folders / objects)
		if(!isset($this->state) || !$this->state)
			$this->state = [];

		$changes = $this->getChanges($this->folderId);
		$this->changeCount = count($changes);
		$this->changes = $changes;

		ZLog::Write(LOGLEVEL_INFO, sprintf("ChangeExporter->InitializeExporter(): Found '%d' changes for '%s'", $this->changeCount, $this->folderId ?? 'hierarchy' ));
	}

	/**
	 * Synchronizes a change to the configured importer
	 * @return array | false
	 */
	public function Synchronize()
	{
		if($this->iter < $this->changeCount) {
			$this->folderId == false ?
				$this->synchronizeFolder($this->changes[$this->iter]) :
				$this->synchronizeObject($this->changes[$this->iter]);
			$this->iter++;

			return [
				'steps' => $this->changeCount,
				'progress' => $this->iter
			];
		}

		return false;
	}

	private function synchronizeFolder($change) {

		switch($change["type"]) {
			case "change":
				$folder = $this->store->GetFolder($change["id"]);
				$stat = $this->store->StatFolder($change["id"]);

				if(!$folder)
					return;

				if($this->importer->ImportFolderChange($folder))
					$this->updateState("change", $stat);
				break;
			case "delete":
				if($this->importer->ImportFolderDeletion(SyncFolder::GetObject($change["id"])))
					$this->updateState("delete", $change);
				break;
		}


	}

	private function synchronizeObject($change) {

		switch($change["type"]) {
			case "change":
				// Note: because 'parseMessage' and 'statMessage' are two seperate
				// calls, we have a chance that the message has changed between both
				// calls. This may cause our algorithm to 'double see' changes.

				$stat = $this->store->StatMessage($this->folderId, $change["id"]);
				$message = $this->store->GetMessage($this->folderId, $change["id"], $this->contentParams);

				// copy the flag to the message
				$message->flags = (isset($change["flags"])) ? $change["flags"] : 0;

				if($stat && $message) {
					if($this->importer->ImportMessageChange($change["id"], $message) == true)
						$this->updateState("change", $stat);
				}
				break;
			case "delete":
				if($this->importer->ImportMessageDeletion($change["id"]) == true)
					$this->updateState("delete", $change);
				break;
			case "flags":
				if($this->importer->ImportMessageReadFlag($change["id"], $change["flags"]) == true)
					$this->updateState("flags", $change);
				break;
			case "move":
				if($this->importer->ImportMessageMove($change["id"], $change["parent"]) == true)
					$this->updateState("move", $change);
				break;
		}

	}

	// old diff changes $new is the fetched list of folders or objects
	private function getChanges($folderId) {

		// TODO: can overide this function in the backend.
		// @see updateState() and Synchronize() to see how the state is written en the changes are applied.
		if(method_exists($this->store, 'getChanges')) {
			return $this->store->getChanges();
		}

		// BELOW IS THE OLD diffChanges :-(

		if($folderId) {
			ZLog::Write(LOGLEVEL_DEBUG,sprintf("ChangeExporter->getChanges(): Initializing message diff engine. '%d' messages in state", count($this->state)));
			// Get our lists - state (old)  and msglist (new)
			$new = $this->store->GetMessageList($folderId, $this->cutoffdate);
			// if the folder was deleted, no information is available anymore. A hierarchysync should be executed
			if($new === false)
				throw new StatusException("ChangeExporter->getChanges(): Error, no message list available from the backend", SYNC_STATUS_FOLDERHIERARCHYCHANGED, null, LOGLEVEL_INFO);

		}
		else {
			ZLog::Write(LOGLEVEL_DEBUG, "ChangeExporter->getChanges(): Initializing folder diff engine");

			$new = $this->store->GetFolderList();
			if($new === false)
				throw new StatusException("ChangeExporter->getChanges(): error, no folders available from the backend", SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_WARN);

		}

		$changes = $old = array();


		// create associative array of old items with id as key
		foreach($this->state as &$item) {
			$old[$item['id']] = true;
		}

		// iterate through new items to identify new or changed items
		foreach($new as &$item) {
			$id = $item['id'];
			$change = ['id' => $id, 'type' => 'change'];

			if (!isset($old[$id])) {
				// Message in new seems to be new (add)
				$change['flags'] = SYNC_NEWMESSAGE;
				$changes[] = $change;
			} else {
				// Both messages are still available, compare states
				$old_item =& $old[$id];

				if(isset($old_item["flags"], $item["flags"]) && $old_item["flags"] != $item["flags"]) {
					// Flags changed
					$change["type"] = "flags";
					$change["flags"] = $item["flags"];
					$changes[] = $change;
				}

				if((isset($old_item["answered"], $item["answered"]) && $old_item["answered"] != $item["answered"]) || // 'answered' changed
					(isset($old_item["forwarded"], $item["forwarded"]) && $old_item["forwarded"] != $item["forwarded"]) || // 'forwarded' changed
					(isset($old_item["star"], $item["star"]) && $old_item["star"] != $item["star"]) || // 'flagged' aka 'FollowUp' aka 'starred' changed
					(isset($old_item['mod'], $item['mod']) && $old_item['mod'] != $item['mod']) || // message modified
					(isset($old_item['mod']) xor isset($item['mod']))) // modified date missing
				{
					$changes[] = $change;
				}

				// unset in $old, so $old contains only the deleted items
				unset($old[$id]);
			}
		}

		// now $old contains only deleted items
		foreach($old as $id => &$item) {
			// Message in state seems to have disappeared (delete)
			$changes[] = [
				"type" => "delete",
				"id"   => $id,
			];
		}

		return $changes;
	}
}