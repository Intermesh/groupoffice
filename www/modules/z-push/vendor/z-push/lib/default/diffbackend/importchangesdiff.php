<?php
/***********************************************
* File      :   importchangesdiff.php
* Project   :   Z-Push
* Descr     :   IImportChanges implementation using
*               the differential engine
*
* Created   :   02.01.2012
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

class ImportChangesDiff extends DiffState implements IImportChanges {
    private $folderid;

    /**
     * Constructor
     *
     * @param object        $backend
     * @param string        $folderid
     *
     * @access public
     * @throws StatusException
     */
    public function __construct($backend, $folderid = false) {
        $this->backend = $backend;
        $this->folderid = $folderid;
    }

    /**
     * Would load objects which are expected to be exported with this state
     * The DiffBackend implements conflict detection on the fly
     *
     * @param ContentParameters         $contentparameters         class of objects
     * @param string                    $state
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function LoadConflicts($contentparameters, $state) {
        // changes are detected on the fly
        return true;
    }

    /**
     * Imports a single message
     *
     * @param string        $id
     * @param SyncObject    $message
     *
     * @access public
     * @return boolean/string - failure / id of message
     * @throws StatusException
     */
    public function ImportMessageChange($id, $message) {
        //do nothing if it is in a dummy folder
        if ($this->folderid == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageChange('%s','%s'): can not be done on a dummy folder", $id, get_class($message)), SYNC_STATUS_SYNCCANNOTBECOMPLETED);

        if($id) {
            // See if there's a conflict
            $conflict = $this->isConflict("change", $this->folderid, $id);

            // Update client state if this is an update
            $change = array();
            $change["id"] = $id;
            $change["mod"] = 0; // dummy, will be updated later if the change succeeds
            $change["parent"] = $this->folderid;
            $change["flags"] = (isset($message->read)) ? $message->read : 0;
            $this->updateState("change", $change);

            if($conflict && $this->flags == SYNC_CONFLICT_OVERWRITE_PIM)
                // in these cases the status SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT should be returned, so the mobile client can inform the end user
                throw new StatusException(sprintf("ImportChangesDiff->ImportMessageChange('%s','%s'): Conflict detected. Data from PIM will be dropped! Server overwrites PIM. User is informed.", $id, get_class($message)), SYNC_STATUS_CONFLICTCLIENTSERVEROBJECT, null, LOGLEVEL_INFO);
        }

        $stat = $this->backend->ChangeMessage($this->folderid, $id, $message, $this->contentparameters);

        if(!is_array($stat))
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageChange('%s','%s'): unknown error in backend", $id, get_class($message)), SYNC_STATUS_SYNCCANNOTBECOMPLETED);

        // Record the state of the message
        $this->updateState("change", $stat);

        return $stat["id"];
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
        //do nothing if it is in a dummy folder
        if ($this->folderid == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageDeletion('%s'): can not be done on a dummy folder", $id), SYNC_STATUS_SYNCCANNOTBECOMPLETED);

        // See if there's a conflict
        $conflict = $this->isConflict("delete", $this->folderid, $id);

        // Update client state
        $change = array();
        $change["id"] = $id;
        $this->updateState("delete", $change);

        // If there is a conflict, and the server 'wins', then return without performing the change
        // this will cause the exporter to 'see' the overriding item as a change, and send it back to the PIM
        if($conflict && $this->flags == SYNC_CONFLICT_OVERWRITE_PIM) {
            ZLog::Write(LOGLEVEL_INFO, sprintf("ImportChangesDiff->ImportMessageDeletion('%s'): Conflict detected. Data from PIM will be dropped! Object was deleted.", $id));
            return false;
        }

        $stat = $this->backend->DeleteMessage($this->folderid, $id, $this->contentparameters);
        if(!$stat)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageDeletion('%s'): Unknown error in backend", $id), SYNC_STATUS_OBJECTNOTFOUND);

        return true;
    }

    /**
     * Imports a change in 'read' flag
     * This can never conflict
     *
     * @param string        $id
     * @param int           $flags - read/unread
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageReadFlag($id, $flags) {
        //do nothing if it is a dummy folder
        if ($this->folderid == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageReadFlag('%s','%s'): can not be done on a dummy folder", $id, $flags), SYNC_STATUS_SYNCCANNOTBECOMPLETED);

        // Update client state
        $change = array();
        $change["id"] = $id;
        $change["flags"] = $flags;
        $this->updateState("flags", $change);

        $stat = $this->backend->SetReadFlag($this->folderid, $id, $flags, $this->contentparameters);
        if (!$stat)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageReadFlag('%s','%s'): Error, unable retrieve message from backend", $id, $flags), SYNC_STATUS_OBJECTNOTFOUND);

        return true;
    }

    /**
     * Imports a move of a message. This occurs when a user moves an item to another folder
     *
     * @param string        $id
     * @param int           $flags - read/unread
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageMove($id, $newfolder) {
        // don't move messages from or to a dummy folder (GetHierarchy compatibility)
        if ($this->folderid == SYNC_FOLDER_TYPE_DUMMY || $newfolder == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportMessageMove('%s'): can not be done on a dummy folder", $id), SYNC_MOVEITEMSSTATUS_CANNOTMOVE);

        return $this->backend->MoveMessage($this->folderid, $id, $newfolder, $this->contentparameters);
    }


    /**
     * Imports a change on a folder
     *
     * @param object        $folder     SyncFolder
     *
     * @access public
     * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
     * @throws StatusException
     */
    public function ImportFolderChange($folder) {
        $id = $folder->serverid;
        $parent = $folder->parentid;
        $displayname = $folder->displayname;
        $type = $folder->type;

        //do nothing if it is a dummy folder
        if ($parent == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportFolderChange('%s'): can not be done on a dummy folder", $id), SYNC_FSSTATUS_SERVERERROR);

        if($id) {
            $change = array();
            $change["id"] = $id;
            $change["mod"] = $displayname;
            $change["parent"] = $parent;
            $change["flags"] = 0;
            $this->updateState("change", $change);
        }

        $stat = $this->backend->ChangeFolder($parent, $id, $displayname, $type);

        if($stat)
            $this->updateState("change", $stat);

        $folder->serverid = $stat["id"];
        return $folder;
    }

    /**
     * Imports a folder deletion
     *
     * @param SyncFolder    $folder         at least "serverid" needs to be set
     *
     * @access public
     * @return int          SYNC_FOLDERHIERARCHY_STATUS
     * @throws StatusException
     */
    public function ImportFolderDeletion($folder) {
        $id = $folder->serverid;
        $parent = isset($folder->parentid) ? $folder->parentid : false;
        //do nothing if it is a dummy folder
        if ($parent == SYNC_FOLDER_TYPE_DUMMY)
            throw new StatusException(sprintf("ImportChangesDiff->ImportFolderDeletion('%s','%s'): can not be done on a dummy folder", $id, $parent), SYNC_FSSTATUS_SERVERERROR);

        // check the foldertype
        $folder = $this->backend->GetFolder($id);
        if (isset($folder->type) && Utils::IsSystemFolder($folder->type))
            throw new StatusException(sprintf("ImportChangesDiff->ImportFolderDeletion('%s','%s'): Error deleting system/default folder", $id, $parent), SYNC_FSSTATUS_SYSTEMFOLDER);

        $ret = $this->backend->DeleteFolder($id, $parent);
        if (!$ret)
            throw new StatusException(sprintf("ImportChangesDiff->ImportFolderDeletion('%s','%s'): can not be deleted", $id, $parent), SYNC_FSSTATUS_FOLDERDOESNOTEXIST);

        $change = array();
        $change["id"] = $id;

        $this->updateState("delete", $change);

        return true;
    }
}
