<?php
/***********************************************
 * File      :   backend/combined/importer.php
 * Project   :   Z-Push
 * Descr     :   Importer class for the combined backend.
 *
 * Created   :   11.05.2010
 *
 * Copyright 2007 - 2016 Zarafa Deutschland GmbH
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Consult LICENSE file for details
 ************************************************/

class GoImporter implements IImportChanges {
    private $backend;
    private $folderid;
    private $icc;

	/**
	 * Constructor of the ImportChangesCombined class
	 *
	 * @param object $backend
	 * @param string $folderid
	 * @param object $importer
	 *
	 * @access public
	 */
	public function __construct(&$backend, $folderid = false, $icc = false) {
		$this->backend = $backend;
		$this->folderid = $folderid;
		$this->icc = &$icc;
	}

	/**
	 * Loads objects which are expected to be exported with the state
	 * Before importing/saving the actual message from the mobile, a conflict detection should be done
	 *
	 * @param ContentParameters         $contentparameters         class of objects
	 * @param string                    $state
	 *
	 * @access public
	 * @return boolean
	 * @throws StatusException
	 */
	public function LoadConflicts($contentparameters, $state) {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->LoadConflicts() icc not configured");
			return false;
		}
		$this->icc->LoadConflicts($contentparameters, $state);
	}

	/**
	 * Imports a single message
	 *
	 * @param string        $id
	 * @param SyncObject    $message
	 *
	 * @access public
	 * @return boolean/string               failure / id of message
	 */
	public function ImportMessageChange($id, $message) {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->ImportMessageChange() icc not configured");
			return false;
		}
		return $this->icc->ImportMessageChange($id, $message);
	}

	/**
	 * Imports a deletion. This may conflict if the local object has been modified.
	 *
	 * @param string        $id
	 * @param boolean       $asSoftDelete   (opt) if true, the deletion is exported as "SoftDelete", else as "Remove" - default: false
	 *
	 * @access public
	 * @return boolean
	 */
	public function ImportMessageDeletion($id, $asSoftDelete = false) {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->ImportMessageDeletion() icc not configured");
			return false;
		}
		return $this->icc->ImportMessageDeletion($id, $asSoftDelete);
	}

	/**
	 * Imports a change in 'read' flag
	 * This can never conflict
	 *
	 * @param string        $id
	 * @param int           $flags
	 *
	 * @access public
	 * @return boolean
	 */
	public function ImportMessageReadFlag($id, $flags, $categories = array()) {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->ImportMessageReadFlag() icc not configured");
			return false;
		}
		return $this->icc->ImportMessageReadFlag($id, $flags);
	}

	/**
	 * Imports a move of a message. This occurs when a user moves an item to another folder
	 *
	 * @param string        $id
	 * @param string        $newfolder
	 *
	 * @access public
	 * @return boolean
	 */
	public function ImportMessageMove($id, $newfolder) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesCombined->ImportMessageMove('%s', '%s')", $id, $newfolder));
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->ImportMessageMove icc not configured");
			return false;
		}
		if($this->backend->GetBackendId($this->folderid) != $this->backend->GetBackendId($newfolder)){
			ZLog::Write(LOGLEVEL_WARN, "ImportChangesCombined->ImportMessageMove() cannot move message between two backends");
			return false;
		}
		return $this->icc->ImportMessageMove($id, $this->backend->GetBackendFolder($newfolder));
	}


	/**----------------------------------------------------------------------------------------------------------
	 * Methods to import hierarchy
	 */

	/**
	 * Imports a change on a folder
	 *
	 * @param object        $folder         SyncFolder
	 *
	 * @access public
	 * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
	 */
	public function ImportFolderChange($folder) {
		$id = $folder->serverid;
		$parent = $folder->parentid;
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesCombined->ImportFolderChange() id: '%s', parent: '%s'", $id, $parent));
		if($parent == '0') {
			if($id) {
				$backendid = $this->backend->GetBackendId($id);
			}
			else {
				$backendid = $this->backend->config['rootcreatefolderbackend'];
			}
		}
		else {
			$backendid = $this->backend->GetBackendId($parent);
			$folder->parentid = $this->backend->GetBackendFolder($parent);
		}

		if(!empty($this->backend->config['backends'][$backendid]['subfolder']) && $id == $backendid.$this->backend->config['delimiter'].'0') {
			ZLog::Write(LOGLEVEL_WARN, "ImportChangesCombined->ImportFolderChange() cannot change static folder");
			return false;
		}

		if($id != false) {
			if($backendid != $this->backend->GetBackendId($id)) {
				ZLog::Write(LOGLEVEL_WARN, "ImportChangesCombined->ImportFolderChange() cannot move folder between two backends");
				return false;
			}
			$id = $this->backend->GetBackendFolder($id);
		}

		if($id == "GroupOfficeTasks") {
			return false;
		}

		$this->icc = $this->backend->getBackend($backendid.$this->backend->config['delimiter'].$id)->GetImporter();
		$resFolder = $this->icc->ImportFolderChange($folder);
		ZLog::Write(LOGLEVEL_DEBUG, 'ImportChangesCombined->ImportFolderChange() success');
		$folder->serverid = $backendid . $this->backend->config['delimiter'] . $resFolder->serverid;
		// TODO Check if move folder is supported ($parent is different). This is tricky, because you could tell e.g. a CardDAV folder to be moved to the trash of the IMAP backend on the mobile.
		return $folder;
	}

	/**
	 * Imports a folder deletion
	 *
	 * @param SyncFolder    $folder         at least "serverid" needs to be set
	 *
	 * @access public
	 * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
	 */
	public function ImportFolderDeletion($folder) {
		$id = $folder->serverid;
		$parent = isset($folder->parentid) ? $folder->parentid : false;
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportChangesCombined->ImportFolderDeletion('%s', '%s'), $id, $parent"));
		$backendid = $this->backend->GetBackendId($id);
		if(!empty($this->backend->config['backends'][$backendid]['subfolder']) && $id == $backendid.$this->backend->config['delimiter'].'0') {
			ZLog::Write(LOGLEVEL_WARN, "ImportChangesCombined->ImportFolderDeletion() cannot change static folder");
			return false; //we can not change a static subfolder
		}

		$backend = $this->backend->GetBackend($id);
		$id = $this->backend->GetBackendFolder($id);

		if($parent != '0')
			$parent = $this->backend->GetBackendFolder($parent);

		$this->icc = $backend->GetImporter();
		$folder->serverid = $id;
		$folder->parentid = $parent;
		$res = $this->icc->ImportFolderDeletion($folder);
		ZLog::Write(LOGLEVEL_DEBUG, 'ImportChangesCombined->ImportFolderDeletion() success');
		return $res;
	}


	/**
	 * Initializes the state and flags
	 *
	 * @param string        $state
	 * @param int           $flags
	 *
	 * @access public
	 * @return boolean      status flag
	 */
	public function Config($state, $flags = 0) {
		ZLog::Write(LOGLEVEL_DEBUG, 'ImportChangesCombined->Config(...)');
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->Config() icc not configured");
			return false;
		}
		$this->icc->Config($state, $flags);
		ZLog::Write(LOGLEVEL_DEBUG, 'ImportChangesCombined->Config() success');
	}


	/**
	 * Configures additional parameters used for content synchronization
	 *
	 * @param ContentParameters         $contentparameters
	 *
	 * @access public
	 * @return boolean
	 * @throws StatusException
	 */
	public function ConfigContentParameters($contentparameters) {
		ZLog::Write(LOGLEVEL_DEBUG, "ImportChangesCombined->ConfigContentParameters()");
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->ConfigContentParameters() icc not configured");
			return false;
		}
		$this->icc->ConfigContentParameters($contentparameters);
		ZLog::Write(LOGLEVEL_DEBUG, "ImportChangesCombined->ConfigContentParameters() success");
	}

	/**
	 * Reads and returns the current state
	 *
	 * @access public
	 * @return string
	 */
	public function GetState() {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->GetState() icc not configured");
			return false;
		}
		return $this->icc->GetState();
	}

	/**
	 * Sets the states from move operations.
	 * When src and dst state are set, a MOVE operation is being executed.
	 *
	 * @param mixed         $srcState
	 * @param mixed         (opt) $dstState, default: null
	 *
	 * @access public
	 * @return boolean
	 */
	public function SetMoveStates($srcState, $dstState = null) {
		ZLog::Write(LOGLEVEL_DEBUG, "ImportChangesCombined->SetMoveStates()");
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->SetMoveStates() icc not configured");
			return false;
		}
		$this->icc->SetMoveStates($srcState, $dstState);
		ZLog::Write(LOGLEVEL_DEBUG, "ImportChangesCombined->SetMoveStates() success");
	}

	/**
	 * Gets the states of special move operations.
	 *
	 * @access public
	 * @return array(0 => $srcState, 1 => $dstState)
	 */
	public function GetMoveStates() {
		if (!$this->icc) {
			ZLog::Write(LOGLEVEL_ERROR, "ImportChangesCombined->GetMoveStates() icc not configured");
			return false;
		}
		return $this->icc->GetMoveStates();
	}
}


/**
 * The ImportHierarchyChangesCombinedWrap class wraps the importer given in ExportChangesCombined->Config.
 * It prepends the backendid to all folderids and checks foldertypes.
 */

class ImportHierarchyChangesCombinedWrap {
	private $ihc;
	private $backend;
	private $backendid;

	/**
	 * Constructor of the ImportChangesCombined class
	 *
	 * @param string $backendid
	 * @param object $backend
	 * @param object $ihc
	 *
	 * @access public
	 */
	public function __construct($backendid, &$backend, &$ihc) {
		ZLog::Write(LOGLEVEL_DEBUG, "ImportHierarchyChangesCombinedWrap->__construct('$backendid',...)");
		$this->backendid = $backendid;
		$this->backend =& $backend;
		$this->ihc = &$ihc;
	}

	/**
	 * Imports a change on a folder
	 *
	 * @param object        $folder         SyncFolder
	 *
	 * @access public
	 * @return boolean/string               status/id of the folder
	 */
	public function ImportFolderChange($folder) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportHierarchyChangesCombinedWrap->ImportFolderChange('%s')", $folder->serverid));
		$folder->serverid = $this->backendid.$this->backend->config['delimiter'].$folder->serverid;
		if($folder->parentid != '0' || !empty($this->backend->config['backends'][$this->backendid]['subfolder'])){
			$folder->parentid = $this->backendid.$this->backend->config['delimiter'].$folder->parentid;
		}
		if(isset($this->backend->config['folderbackend'][$folder->type]) && $this->backend->config['folderbackend'][$folder->type] != $this->backendid){
			ZLog::Write(LOGLEVEL_DEBUG, sprintf("not using folder: '%s' ('%s')", $folder->displayname, $folder->serverid));
			return true;
		}
		ZLog::Write(LOGLEVEL_DEBUG, "ImportHierarchyChangesCombinedWrap->ImportFolderChange() success");
		return $this->ihc->ImportFolderChange($folder);
	}

	/**
	 * Imports a folder deletion
	 *
	 * @param SyncFolder    $folder         at least "serverid" needs to be set
	 *
	 * @access public
	 *
	 * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
	 */
	public function ImportFolderDeletion($folder) {
		ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportHierarchyChangesCombinedWrap->ImportFolderDeletion('%s')", $folder->serverid));
		$folder->serverid = $this->backendid . $this->backend->config['delimiter'] . $folder->serverid;
		return $this->ihc->ImportFolderDeletion($folder);
	}
}
