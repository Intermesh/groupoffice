<?php
/***********************************************
* File      :   diffbackend.php
* Project   :   Z-Push
* Descr     :   This is the abstract differential backend.
*               By implementing this backends there is no need
*               to worry about importers and exporters. Also
*               tracking incremental changes is not necessary,
*               as the DiffState used by the DiffBackend offers
*               this functionality by comparing the list of objects
*               available "last time" with the list of objects
*               available "now".
*               Please note that the differential mechanism
*               can consume a considerable amount of memory and cpu
*               power when synchronizing folders with many items.
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

abstract class BackendDiff extends Backend {
    protected $store;

    /**
     * Setup the backend to work on a specific store or checks ACLs there.
     * If only the $store is submitted, all Import/Export/Fetch/Etc operations should be
     * performed on this store (switch operations store).
     * If the ACL check is enabled, this operation should just indicate the ACL status on
     * the submitted store, without changing the store for operations.
     * For the ACL status, the currently logged on user MUST have access rights on
     *  - the entire store - admin access if no folderid is sent, or
     *  - on a specific folderid in the store (secretary/full access rights)
     *
     * The ACLcheck MUST fail if a folder of the authenticated user is checked!
     *
     * @param string        $store              target store, could contain a "domain\user" value
     * @param boolean       $checkACLonly       if set to true, Setup() should just check ACLs
     * @param string        $folderid           if set, only ACLs on this folderid are relevant
     * @param boolean       $readonly           if set, the folder needs at least read permissions
     *
     * @access public
     * @return boolean
     */
    public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false) {
        $this->store = $store;

        // we don't know if and how diff backends implement the "admin" check, but this will disable it for the webservice
        // backends which want to implement this, need to overwrite this method explicitely. For more info see https://jira.zarafa.com/browse/ZP-462
        if ($store == "SYSTEM" && $checkACLonly == true)
            return false;

        return true;
    }

    /**
     * Returns an array of SyncFolder types with the entire folder hierarchy
     * on the server (the array itself is flat, but refers to parents via the 'parent' property
     *
     * provides AS 1.0 compatibility
     *
     * @access public
     * @return array SYNC_FOLDER
     */
    function GetHierarchy() {
        $folders = array();

        $fl = $this->GetFolderList();
        if (is_array($fl))
            foreach($fl as $f)
                $folders[] = $this->GetFolder($f['id']);

        return $folders;
    }

    /**
     * Returns the importer to process changes from the mobile
     * If no $folderid is given, hierarchy importer is expected
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object(ImportChanges)
     * @throws StatusException
     */
    public function GetImporter($folderid = false) {
        return new ImportChangesDiff($this, $folderid);
    }

    /**
     * Returns the exporter to send changes to the mobile
     * If no $folderid is given, hierarchy exporter is expected
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object(ExportChanges)
     * @throws StatusException
     */
    public function GetExporter($folderid = false) {
        return new ExportChangesDiff($this, $folderid);
    }

    /**
     * Returns all available data of a single message
     *
     * @param string            $folderid
     * @param string            $id
     * @param ContentParameters $contentparameters flag
     *
     * @access public
     * @return object(SyncObject)
     * @throws StatusException
     */
    public function Fetch($folderid, $id, $contentparameters) {
        // override truncation
        $contentparameters->SetTruncation(SYNC_TRUNCATION_ALL);
        $msg = $this->GetMessage($folderid, $id, $contentparameters);
        if ($msg === false)
            throw new StatusException("BackendDiff->Fetch('%s','%s'): Error, unable retrieve message from backend", SYNC_STATUS_OBJECTNOTFOUND);
        return $msg;
    }

    /**
     * Processes a response to a meeting request.
     * CalendarID is a reference and has to be set if a new calendar item is created
     *
     * @param string        $requestid      id of the object containing the request
     * @param string        $folderid       id of the parent folder of $requestid
     * @param string        $response
     *
     * @access public
     * @return string       id of the created/updated calendar obj
     * @throws StatusException
     */
    public function MeetingResponse($requestid, $folderid, $response) {
        throw new StatusException(sprintf("BackendDiff->MeetingResponse('%s','%s','%s'): Error, this functionality is not supported by the diff backend", $requestid, $folderid, $response), SYNC_MEETRESPSTATUS_MAILBOXERROR);
    }

    /**----------------------------------------------------------------------------------------------------------
     * Abstract DiffBackend methods
     *
     * Need to be implemented in the actual diff backend
     */

    /**
     * Returns a list (array) of folders, each entry being an associative array
     * with the same entries as StatFolder(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * @access protected
     * @return array/boolean        false if the list could not be retrieved
     */
    public abstract function GetFolderList();

    /**
     * Returns an actual SyncFolder object with all the properties set. Folders
     * are pretty simple, having only a type, a name, a parent and a server ID.
     *
     * @param string        $id           id of the folder
     *
     * @access public
     * @return object   SyncFolder with information
     */
    public abstract function GetFolder($id);

    /**
     * Returns folder stats. An associative array with properties is expected.
     *
     * @param string        $id             id of the folder
     *
     * @access public
     * @return array
     *          Associative array(
     *              string  "id"            The server ID that will be used to identify the folder. It must be unique, and not too long
     *                                      How long exactly is not known, but try keeping it under 20 chars or so. It must be a string.
     *              string  "parent"        The server ID of the parent of the folder. Same restrictions as 'id' apply.
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the folder has not changed. In practice this means that 'mod' can be equal to the folder name
     *                                      as this is the only thing that ever changes in folders. (the type is normally constant)
     *          )
     */
    public abstract function StatFolder($id);

    /**
     * Creates or modifies a folder
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
    public abstract function ChangeFolder($folderid, $oldid, $displayname, $type);

    /**
     * Deletes a folder
     *
     * @param string        $id
     * @param string        $parent         is normally false
     *
     * @access public
     * @return boolean                      status - false if e.g. does not exist
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     */
    public abstract function DeleteFolder($id, $parentid);

    /**
     * Returns a list (array) of messages, each entry being an associative array
     * with the same entries as StatMessage(). This method should return stable information; ie
     * if nothing has changed, the items in the array must be exactly the same. The order of
     * the items within the array is not important though.
     *
     * The $cutoffdate is a date in the past, representing the date since which items should be shown.
     * This cutoffdate is determined by the user's setting of getting 'Last 3 days' of e-mail, etc. If
     * the cutoffdate is ignored, the user will not be able to select their own cutoffdate, but all
     * will work OK apart from that.
     *
     * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
     *
     * @access public
     * @return array/false                  array with messages or false if folder is not available
     */
    public abstract function GetMessageList($folderid, $cutoffdate);

    /**
     * Returns the actual SyncXXX object type. The '$folderid' of parent folder can be used.
     * Mixing item types returned is illegal and will be blocked by the engine; ie returning an Email object in a
     * Tasks folder will not do anything. The SyncXXX objects should be filled with as much information as possible,
     * but at least the subject, body, to, from, etc.
     *
     * @param string            $folderid           id of the parent folder
     * @param string            $id                 id of the message
     * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
     *
     * @access public
     * @return object/false                 false if the message could not be retrieved
     */
    public abstract function GetMessage($folderid, $id, $contentparameters);

    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
     * @access public
     * @return array or boolean if fails
     *          Associative array(
     *              string  "id"            Server unique identifier for the message. Again, try to keep this short (under 20 chars)
     *              int     "flags"         simply '0' for unread, '1' for read
     *              long    "mod"           This is the modification signature. It is any arbitrary string which is constant as long as
     *                                      the message has not changed. As soon as this signature changes, the item is assumed to be completely
     *                                      changed, and will be sent to the PDA as a whole. Normally you can use something like the modification
     *                                      time for this field, which will change as soon as the contents have changed.
     *          )
     */
    public abstract function StatMessage($folderid, $id);

    /**
     * Called when a message has been changed on the mobile. The new message must be saved to disk.
     * The return value must be whatever would be returned from StatMessage() after the message has been saved.
     * This way, the 'flags' and the 'mod' properties of the StatMessage() item may change via ChangeMessage().
     * This method will never be called on E-mail items as it's not 'possible' to change e-mail items. It's only
     * possible to set them as 'read' or 'unread'.
     *
     * @param string              $folderid            id of the folder
     * @param string              $id                  id of the message
     * @param SyncXXX             $message             the SyncObject containing a message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return array                        same return value as StatMessage()
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public abstract function ChangeMessage($folderid, $id, $message, $contentParameters);

    /**
     * Changes the 'read' flag of a message on disk. The $flags
     * parameter can only be '1' (read) or '0' (unread). After a call to
     * SetReadFlag(), GetMessageList() should return the message with the
     * new 'flags' but should not modify the 'mod' parameter. If you do
     * change 'mod', simply setting the message to 'read' on the mobile will trigger
     * a full resync of the item from the server.
     *
     * @param string              $folderid            id of the folder
     * @param string              $id                  id of the message
     * @param int                 $flags               read flag of the message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public abstract function SetReadFlag($folderid, $id, $flags, $contentParameters);

    /**
     * Called when the user has requested to delete (really delete) a message. Usually
     * this means just unlinking the file its in or somesuch. After this call has succeeded, a call to
     * GetMessageList() should no longer list the message. If it does, the message will be re-sent to the mobile
     * as it will be seen as a 'new' item. This means that if this method is not implemented, it's possible to
     * delete messages on the PDA, but as soon as a sync is done, the item will be resynched to the mobile
     *
     * @param string              $folderid             id of the folder
     * @param string              $id                   id of the message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public abstract function DeleteMessage($folderid, $id, $contentParameters);

    /**
     * Called when the user moves an item on the PDA from one folder to another. Whatever is needed
     * to move the message on disk has to be done here. After this call, StatMessage() and GetMessageList()
     * should show the items to have a new parent. This means that it will disappear from GetMessageList()
     * of the sourcefolder and the destination folder will show the new message
     *
     * @param string              $folderid            id of the source folder
     * @param string              $id                  id of the message
     * @param string              $newfolderid         id of the destination folder
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_MOVEITEMSSTATUS_* exceptions
     */
    public abstract function MoveMessage($folderid, $id, $newfolderid, $contentParameters);

}
