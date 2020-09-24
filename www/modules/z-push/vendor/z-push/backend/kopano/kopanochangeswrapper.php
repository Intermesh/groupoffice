<?php
/***********************************************
 * File      :   kopanochangeswrapper.php
 * Project   :   Z-Push
 * Descr     :   This class fullfills the IImportChanges
 *               and IExportChanges interfaces.
 *               It instantiates the ReplyBackImExporter or
 *               the ICS Importer or Exporter depending on the need.
 *               The class decides only when the states are set what needs
 *               to be done. If there are states from the ReplyBackImExporter
 *               or the user lacks write permissions on the folder a
 *               ReplyBackImExporter will be initialized, else defauld ICS.
 *
 * Created   :   25.04.2016
 *
 * Copyright 2016 Zarafa Deutschland GmbH
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

class KopanoChangesWrapper implements IImportChanges, IExportChanges {
    const IMPORTER = 1;
    const EXPORTER = 2;

    // hold a static list of wrappers for stores & folders
    static private $wrappers = array();
    static private $backend;

    private $preparedAs;
    private $current;
    private $session;
    private $store;
    private $folderid;
    private $replyback;
    private $ownFolder;
    private $state;
    private $moveSrcState;
    private $moveDstState;

    /**
     * Sets the backend to be used by the wrappers. This is used to check for permissions.
     * The calls made are not part of IBackend, but are implemented by BackendZarafa only.
     *
     * @param IBackend $backend
     */
    static public function SetBackend($backend) {
        self::$backend = $backend;
    }

    /**
     * Gets a wrapper for a folder in a store.
     * $session needs to be set in order to create a new wrapper. If no session is set and
     * the wrapper does not already exist, false is returned.
     *
     * @param string $storeName
     * @param resource $session
     * @param resource $store
     * @param string $folderid
     * @param boolean $ownFolder
     *
     * @access public
     * @return KopanoChangesWrapper | boolean
     */
    static public function GetWrapper($storeName, $session, $store, $folderid, $ownFolder) {
        // if existing exporter is used by Ping we need to discard it so it's fully reconfigured (ZP-1169)
        if (isset(self::$wrappers[$storeName][$folderid]) && self::$wrappers[$storeName][$folderid]->hasDiscardDataFlag()) {
            ZLog::Write(LOGLEVEL_DEBUG, "KopanoChangesWrapper::GetWrapper(): Found existing notification check exporter. Reinitializing.");
            unset(self::$wrappers[$storeName][$folderid]);
        }

        // check early due to the folderstats
        if (isset(self::$wrappers[$storeName][$folderid])) {
            return self::$wrappers[$storeName][$folderid];
        }

        if (!isset(self::$wrappers[$storeName])) {
            self::$wrappers[$storeName] = array();
        }

        if (!isset(self::$wrappers[$storeName][$folderid]) && $session) {
            self::$wrappers[$storeName][$folderid] = new KopanoChangesWrapper($session, $store, $folderid, $ownFolder);
        }
        else {
            return false;
        }

        return self::$wrappers[$storeName][$folderid];
    }

    /**
     * Constructor
     *
     * @param mapisession       $session
     * @param mapistore         $store
     * @param string            $folderid
     * @param boolean           $ownFolder
     *
     * @access public
     * @throws StatusException
     */
    public function __construct($session, $store, $folderid, $ownFolder) {
        $this->preparedAs = null;
        $this->session = $session;
        $this->store = $store;
        $this->folderid = $folderid;
        $this->ownFolder = $ownFolder;

        $this->replyback = null;
        $this->current = null;
        $this->state = null;
        $this->didMove = false;
        $this->moveSrcState = false;
        $this->moveDstState = false;
    }

    /**
     * Indicates if the wrapper is requested to be an exporter or an importer.
     *
     * @param int $type     either KopanoChangesWrapper::IMPORTER or KopanoChangesWrapper::EXPORTER
     *
     * @access public
     * @return void
     */
    public function Prepare($type) {
        $this->preparedAs = $type;
    }

    /**
     * Indicates if the wrapper has a ReplyBackImExporter.
     *
     * @access public
     * @return boolean
     */
    public function HasReplyBackExporter() {
        return !! $this->replyback;
    }

    /**
     * Indicates if the ReplyBackImExporter is the im/exporter currently being wrapped.
     *
     * @access private
     * @return boolean
     */
    private function isReplyBackExporter() {
        return $this->current == $this->replyback;
    }

    /**
     * Indicates if the current IChanges object is an ICS exporter and has the BACKEND_DISCARD_DATA flag configured.
     * These are used to verify notifications e.g. in Ping.
     *
     * @access private
     * @return boolean
     */
    private function hasDiscardDataFlag() {
        if (isset($this->current) && $this->current instanceof ExportChangesICS && $this->current->HasDiscardDataFlag()) {
            return true;
        }

        return false;
    }

    /**
     * Initializes the correct importer, exporter or ReplyBackImExporter.
     * The wrapper needs to be prepared as importer or exporter before.
     * If the user lacks permissions, a ReplyBackImExporter will be instantiated and used.
     *
     * @access private
     * @return void
     * @throws StatusException, FatalNotImplementedException
     */
    private function init() {
        if ($this->preparedAs == self::IMPORTER) {
            if (!($this->current instanceof ImportChangesICS)) {
                // check if the user has permissions to import to this folderid
                if (!$this->ownFolder && !self::$backend->HasSecretaryACLs($this->store, $this->folderid)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("KopanoChangesWrapper->init(): Importer: missing permissions on folderid: '%s'. Working with ReplyBackImExporter.", Utils::PrintAsString($this->folderid)));
                    $this->replyback = $this->getReplyBackImExporter();
                    $this->current = $this->replyback;
                }
                else if (!empty($this->moveSrcState)) {
                    ZLog::Write(LOGLEVEL_DEBUG, "KopanoChangesWrapper->init(): Importer: Move state available. Working with ReplyBackImExporter.");
                    $this->replyback = $this->getReplyBackImExporter();
                    $this->current = $this->replyback;
                }
                // permissions ok, instanciate an ICS importer
                else {
                    $this->current = new ImportChangesICS($this->session, $this->store, hex2bin($this->folderid));
                }
            }
        }
        else if ($this->preparedAs == self::EXPORTER){
            if (!($this->current instanceof ExportChangesICS)) {
                // if there was something imported on a read-only folder, we need to reply back the changes
                $states = isset($this->state) ? $this->state->GetReplyBackState() : false;
                if ($this->replyback || !empty($states) || !empty($this->moveSrcState)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("KopanoChangesWrapper->init(): Exporter: read-only folder with folderid: '%s'. Working with ReplyBackImExporter.", Utils::PrintAsString($this->folderid)));
                    if (!$this->replyback) {
                        $this->replyback = $this->getReplyBackImExporter();
                    }
                    $this->current = $this->replyback;
                }
                else {
                    // check if the user has permissions to export from this folderid
                    if (!$this->ownFolder && !self::$backend->HasReadACLs($this->store, $this->folderid)) {
                        throw new StatusException(sprintf("KopanoChangesWrapper->init(): Exporter: missing read permissions on folderid: '%s'.", Utils::PrintAsString($this->folderid)), SYNC_STATUS_FOLDERHIERARCHYCHANGED);
                    }
                    $this->current = new ExportChangesICS($this->session, $this->store, hex2bin($this->folderid));
                }
            }
        }
        else {
            throw new FatalNotImplementedException("KopanoChangesWrapper->init(): KopanoChangesWrapper was not prepared as importer or exporter.");
        }
    }

    /**
     * Returns a new ReplyBackImExporter() for the wrapper.
     *
     * @access private
     * @return ReplyBackImExporter
     */
    private function getReplyBackImExporter() {
        return new ReplyBackImExporter($this->session, $this->store, hex2bin($this->folderid));
    }

    /**----------------------------------------------------------------------------------------------------------
     * IChanges
     */

    /**
     * Initializes the state and flags.
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean      status flag
     * @throws StatusException
     */
    public function Config($state, $flags = 0) {
        // if there is an ICS state, it will remain untouched in the ReplyBackState object
        $this->state = ReplyBackState::FromState($state);

        $this->init();

        $config = false;
        if ($this->isReplyBackExporter() || !empty($this->moveSrcState)) {
            $config = $this->current->Config($this->state->GetReplyBackState(), $flags);
        }
        else {
            $config = $this->current->Config($this->state->GetICSState(), $flags);
        }
        $this->current->SetMoveStates($this->moveSrcState, $this->moveDstState);
    }

    /**
     * Configures additional parameters used for content synchronization.
     *
     * @param ContentParameters         $contentparameters
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ConfigContentParameters($contentparameters) {
        $this->init();
        return $this->current->ConfigContentParameters($contentparameters);
    }

    /**
     * Reads and returns the current state.
     *
     * @access public
     * @return string
     */
    public function GetState() {
        $newState = $this->current->GetState();
        if ($this->isReplyBackExporter()) {
            $this->state->SetReplyBackState($newState);
        }
        else {
            $this->state->SetICSState($newState);
        }
        return ReplyBackState::ToState($this->state);
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
        $this->moveSrcState = $srcState;
        $this->moveDstState = $dstState;
        return true;
    }

    /**
     * Gets the states of special move operations.
     *
     * @access public
     * @return array(0 => $srcState, 1 => $dstState)
     */
    public function GetMoveStates() {
        return $this->current->GetMoveStates();
    }

    /**----------------------------------------------------------------------------------------------------------
     * IImportChanges - pass everything directly through to $this->current
     */

    /**
     * Loads objects which are expected to be exported with the state.
     * Before importing/saving the actual message from the mobile, a conflict detection should be done.
     *
     * @param ContentParameters         $contentparameters
     * @param string                    $state
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function LoadConflicts($contentparameters, $state) {
        return $this->current->LoadConflicts($contentparameters, $state);
    }


    /**
     * Imports a single message.
     *
     * @param string        $id
     * @param SyncObject    $message
     *
     * @access public
     * @return boolean/string               failure / id of message
     * @throws StatusException
     */
    public function ImportMessageChange($id, $message) {
        return $this->current->ImportMessageChange($id, $message);
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
        return $this->current->ImportMessageDeletion($id);
    }

    /**
     * Imports a change in 'read' flag.
     * This can never conflict.
     *
     * @param string        $id
     * @param int           $flags
     * @param array         $categories
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageReadFlag($id, $flags, $categories = array()) {
        return $this->current->ImportMessageReadFlag($id, $flags, $categories);
    }

    /**
     * Imports a move of a message. This occurs when a user moves an item to another folder.
     *
     * @param string        $id
     * @param string        $newfolder      destination folder
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ImportMessageMove($id, $newfolder) {
        $this->didMove = true;
        // When we setup the $current importer, we didn't know what we needed to do, so we look only at the src folder for permissions.
        // Now the $newfolder could be read only as well. So we need to check it's permissions and then switch to a ReplyBackImExporter if it's r/o.
        if (!$this->isReplyBackExporter()) {

            // check if the user has permissions on the destination folder
            $dststore = self::$backend->GetMAPIStoreForFolderId(ZPush::GetAdditionalSyncFolderStore($newfolder), $newfolder);
            if (!self::$backend->HasSecretaryACLs($dststore, $newfolder)) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("KopanoChangesWrapper->ImportMessageMove(): destination folderid '%s' is missing permissions. Switching to ReplyBackImExporter.", Utils::PrintAsString($newfolder)));
                $this->replyback = $this->getReplyBackImExporter();
                $this->current = $this->replyback;

                $this->current->SetMoveStates($this->moveSrcState, $this->moveDstState);
                if (isset($this->state)) {
                    $this->current->Config($this->state->GetReplyBackState());
                }
            }
        }

        return $this->current->ImportMessageMove($id, $newfolder);
    }

    /**
     * Implement interfaces which are never used.
     */

    /**
     * Imports a change on a folder.
     *
     * @param object        $folder         SyncFolder
     *
     * @access public
     * @return boolean/SyncObject           status/object with the ath least the serverid of the folder set
     * @throws StatusException
     */
    public function ImportFolderChange($folder) {
        return false;
    }

    /**
     * Imports a folder deletion.
     *
     * @param SyncFolder    $folder         at least "serverid" needs to be set
     *
     * @access public
     * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
     * @throws StatusException
    */
    public function ImportFolderDeletion($folder) {
        return false;
    }


    /**----------------------------------------------------------------------------------------------------------
     * IExportChanges  - pass everything directly through to $this->current
     */

    /**
     * Initializes the Exporter where changes are synchronized to.
     *
     * @param IImportChanges    $importer
     *
     * @access public
     * @return boolean
     */
    public function InitializeExporter(&$importer) {
        return $this->current->InitializeExporter($importer);
    }

    /**
     * Returns the amount of changes to be exported.
     *
     * @access public
     * @return int
     */
    public function GetChangeCount() {
        return $this->current->GetChangeCount();
    }

    /**
     * Synchronizes a change. The previously imported messages are now retrieved from the backend.
     * and sent back to the mobile.
     *
     * @access public
     * @return array
     */
    public function Synchronize() {
        return $this->current->Synchronize();
    }
}