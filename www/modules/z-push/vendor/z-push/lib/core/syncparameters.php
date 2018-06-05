<?php
/***********************************************
* File      :   syncparameters.php
* Project   :   Z-Push
* Descr     :   Transportation container for
*               requested content parameters and information
*               about the container and states
*
* Created   :   11.04.2011
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

class SyncParameters extends StateObject {
    const DEFAULTOPTIONS = "DEFAULT";
    const EMAILOPTIONS = "EMAIL";
    const CALENDAROPTIONS = "CALENDAR";
    const CONTACTOPTIONS = "CONTACTS";
    const NOTEOPTIONS = "NOTES";
    const TASKOPTIONS = "TASKS";
    const SMSOPTIONS = "SMS";

    private $synckeyChanged = false;
    private $confirmationChanged = false;
    private $currentCPO = self::DEFAULTOPTIONS;

    protected $unsetdata = array(
                                    'uuid' => false,
                                    'uuidcounter' => false,
                                    'uuidnewcounter' => false,
                                    'counterconfirmed' => false,
                                    'folderid' => false,
                                    'backendfolderid' => false,
                                    'referencelifetime' => 10,
                                    'lastsynctime' => false,
                                    'referencepolicykey' => true,
                                    'pingableflag' => false,
                                    'contentclass' => false,
                                    'deletesasmoves' => false,
                                    'conversationmode' => false,
                                    'windowsize' => 5,
                                    'contentparameters' => array(),
                                    'foldersynctotal' => false,
                                    'foldersyncremaining' => false,
                                    'folderstat' => false,
                                    'folderstattimeout' => false,
                                    'movestate' => false,
                                );

    /**
     * SyncParameters constructor
     */
    public function __construct() {
        // initialize ContentParameters for the current option
        $this->checkCPO();
    }


    /**
     * SyncKey methods
     *
     * The current and next synckey is saved as uuid and counter
     * so partial and ping can access the latest states.
     */

    /**
     * Returns the latest SyncKey of this folder
     *
     * @access public
     * @return string/boolean       false if no uuid/counter available
     */
    public function GetSyncKey() {
        if (isset($this->uuid) && isset($this->uuidCounter))
            return StateManager::BuildStateKey($this->uuid, $this->uuidCounter);

        return false;
    }

    /**
     * Sets the the current synckey.
     * This is done by parsing it and saving uuid and counter.
     * By setting the current key, the "next" key is obsolete
     *
     * @param string    $synckey
     *
     * @access public
     * @return boolean
     */
    public function SetSyncKey($synckey) {
        list($this->uuid, $this->uuidCounter) = StateManager::ParseStateKey($synckey);

        // remove newSyncKey
        unset($this->uuidNewCounter);

        // the counter has been requested (and that way confirmed)
        if ($this->counterconfirmed == false) {
            $this->counterconfirmed = true;
            $this->confirmationChanged = true;
        }

        return true;
    }

    /**
     * Indicates if this folder has a synckey
     *
     * @access public
     * @return booleans
     */
    public function HasSyncKey() {
        return (isset($this->uuid) && isset($this->uuidCounter));
    }

    /**
     * Sets the the next synckey.
     * This is done by parsing it and saving uuid and next counter.
     * if the folder has no synckey until now (new sync), the next counter becomes current asl well.
     *
     * @param string    $synckey
     *
     * @access public
     * @throws FatalException       if the uuids of current and next do not match
     * @return boolean
     */
    public function SetNewSyncKey($synckey) {
        list($uuid, $uuidNewCounter) = StateManager::ParseStateKey($synckey);
        if (!$this->HasSyncKey()) {
            $this->uuid = $uuid;
            $this->uuidCounter = $uuidNewCounter;
        }
        else if ($uuid !== $this->uuid)
            throw new FatalException("SyncParameters->SetNewSyncKey(): new SyncKey must have the same UUID as current SyncKey");

        $this->uuidNewCounter = $uuidNewCounter;
        $this->counterconfirmed = false;
        $this->confirmationChanged = true;
        $this->synckeyChanged = true;
    }

    /**
     * Returns the next synckey
     *
     * @access public
     * @return string/boolean       returns false if uuid or counter are not available
     */
    public function GetNewSyncKey() {
        if (isset($this->uuid) && isset($this->uuidNewCounter))
            return StateManager::BuildStateKey($this->uuid, $this->uuidNewCounter);

        return false;
    }

    /**
     * Indicates if the folder has a next synckey
     *
     * @access public
     * @return boolean
     */
    public function HasNewSyncKey() {
        return (isset($this->uuid) && isset($this->uuidNewCounter));
    }

    /**
     * Return the latest synckey.
     * When this is called the new key becomes the current key (if a new key is available).
     * The current key is then returned.
     *
     * @param boolean $confirmedOnly    indicates if only confirmed states should be considered, default: false
     *
     * @access public
     * @return string
     */
    public function GetLatestSyncKey($confirmedOnly = false) {
        // New becomes old if available - if $confirmedOnly then the counter needs to be confirmed
        if ($this->HasUuidNewCounter() && (($confirmedOnly && $this->counterconfirmed) || !$confirmedOnly)) {
            $this->uuidCounter = $this->uuidNewCounter;
            unset($this->uuidNewCounter);
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncParameters->GetLatestSyncKey(): '%s'", $this->GetSyncKey()));
        return $this->GetSyncKey();
    }

    /**
     * Removes the saved SyncKey of this folder
     *
     * @access public
     * @return boolean
     */
    public function RemoveSyncKey() {
        if (isset($this->uuid))
            unset($this->uuid);

        if (isset($this->uuidCounter))
            unset($this->uuidCounter);

        if (isset($this->uuidNewCounter))
            unset($this->uuidNewCounter);

        ZLog::Write(LOGLEVEL_DEBUG, "SyncParameters->RemoveSyncKey(): saved sync key removed");
        return true;
    }

    /**
     * Overwrite GetBackendFolderId() because on old profiles, this will not be set.
     *
     * @access public
     * @return string
     */
    public function GetBackendFolderId() {
        if ($this->backendfolderid) {
            return $this->backendfolderid;
        }
        else {
            return $this->GetFolderId();
        }
    }

    /**
     * CPO methods
     *
     * A sync request can have several options blocks. Each block is saved into an own CPO object
     *
     */

    /**
     * Returns the a specified CPO
     *
     * @param string    $options    (opt) If not specified, the default Options (CPO) will be used
     *                              Valid option SyncParameters::SMSOPTIONS (string "SMS")
     *
     * @access public
     * @return ContentParameters object
     */
    public function GetCPO($options = self::DEFAULTOPTIONS) {
        $options = strtoupper($options);
        $this->isValidType($options);
        $options = $this->normalizeType($options);

        $this->checkCPO($options);

        // copy contentclass and conversationmode to the CPO
        $this->contentParameters[$options]->SetContentClass($this->contentclass);
        $this->contentParameters[$options]->SetConversationMode($this->conversationmode);

        return $this->contentParameters[$options];
    }

    /**
     * Use the submitted CPO type for next setters/getters
     *
     * @param string    $options    (opt) If not specified, the default Options (CPO) will be used
     *                              Valid option SyncParameters::SMSOPTIONS (string "SMS")
     *
     * @access public
     * @return
     */
    public function UseCPO($options = self::DEFAULTOPTIONS) {
        $options = strtoupper($options);
        $this->isValidType($options);

        // remove potential old default CPO if available
        if (isset($this->contentParameters[self::DEFAULTOPTIONS]) && $options != self::DEFAULTOPTIONS && $options !== self::SMSOPTIONS) {
            $a = $this->contentParameters;
            unset($a[self::DEFAULTOPTIONS]);
            $this->contentParameters = $a;
            ZLog::Write(LOGLEVEL_DEBUG, "SyncParameters->UseCPO(): removed existing DEFAULT CPO as it is obsolete");
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncParameters->UseCPO('%s')", $options));
        $this->currentCPO = $options;
        $this->checkCPO($this->currentCPO);
    }


    /**
     * Indicates if the confirmation status changed for the SyncKey.
     *
     * @access public
     * @return boolean
     */
    public function HasConfirmationChanged() {
        return $this->confirmationChanged;
    }

    /**
     * Indicates if a exporter run is required. This is the case if the given folderstat is different from the saved one
     * or when the expiration time expired.
     *
     * @param string $currentFolderStat
     * @param boolean $doLog
     *
     * @access public
     * @return boolean
     */
    public function IsExporterRunRequired($currentFolderStat, $doLog = false) {
        // if the backend returned false as folderstat, we have to run the exporter
        if ($currentFolderStat === false || $this->confirmationChanged)  {
            $run = true;
        }
        else {
            // check if the folderstat differs from the saved one or expired
            $run = ! ($this->HasFolderStat() && $currentFolderStat === $this->GetFolderStat() && time() < $this->GetFolderStatTimeout());
        }
        if ($doLog) {
            $expDate = ($this->HasFolderStatTimeout()) ? date('Y-m-d H:i:s', $this->GetFolderStatTimeout()) : "not set";
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncParameters->IsExporterRunRequired(): %s - current: %s - saved: %s - expiring: %s", Utils::PrintAsString($run), Utils::PrintAsString($currentFolderStat), Utils::PrintAsString($this->GetFolderStat()), $expDate));
        }
        return $run;
    }

    /**
     * Checks if a CPO is correctly inicialized and inicializes it if necessary
     *
     * @param string    $options    (opt) If not specified, the default Options (CPO) will be used
     *                              Valid option SyncParameters::SMSOPTIONS (string "SMS")
     *
     * @access private
     * @return boolean
     */
    private function checkCPO($options = self::DEFAULTOPTIONS) {
        $this->isValidType($options);

        if (!isset($this->contentParameters[$options])) {
            $a = $this->contentParameters;
            $a[$options] = new ContentParameters();
            $this->contentParameters = $a;
        }

        return true;
    }

    /**
     * Checks if the requested option type is available
     *
     * @param string $options   CPO type
     *
     * @access private
     * @return boolean
     * @throws FatalNotImplementedException
     */
     private function isValidType($options) {
         if ($options !== self::DEFAULTOPTIONS &&
                        $options !== self::EMAILOPTIONS &&
                        $options !== self::CALENDAROPTIONS &&
                        $options !== self::CONTACTOPTIONS &&
                        $options !== self::NOTEOPTIONS &&
                        $options !== self::TASKOPTIONS &&
                        $options !== self::SMSOPTIONS)
            throw new FatalNotImplementedException(sprintf("SyncParameters->isAllowedType('%s') ContentParameters is invalid. Such type is not available.", $options));

        return true;
    }

    /**
     * Normalizes the requested option type and returns it as
     * default option if no default is available
     *
     * @param string $options   CPO type
     *
     * @access private
     * @return string
     * @throws FatalNotImplementedException
     */
     private function normalizeType($options) {
        // return the requested CPO as it is defined
        if (isset($this->contentParameters[$options]))
            return $options;

        $returnCPO = $options;
        // return email, calendar, contact or note CPO as default CPO if there no explicit default CPO defined
        if ($options == self::DEFAULTOPTIONS && !isset($this->contentParameters[self::DEFAULTOPTIONS])) {

            if (isset($this->contentParameters[self::EMAILOPTIONS]))
                $returnCPO = self::EMAILOPTIONS;
            elseif (isset($this->contentParameters[self::CALENDAROPTIONS]))
                $returnCPO = self::CALENDAROPTIONS;
            elseif (isset($this->contentParameters[self::CONTACTOPTIONS]))
                $returnCPO = self::CONTACTOPTIONS;
            elseif (isset($this->contentParameters[self::NOTEOPTIONS]))
                $returnCPO = self::NOTEOPTIONS;
            elseif (isset($this->contentParameters[self::TASKOPTIONS]))
                $returnCPO = self::TASKOPTIONS;

            return $returnCPO;
        }
        // something unexpected happened, just return default, empty in the worst case
        else {
            ZLog::Write(LOGLEVEL_WARN, "SyncParameters->normalizeType(): no DEFAULT CPO available, creating empty CPO");
            $this->checkCPO(self::DEFAULTOPTIONS);
            return self::DEFAULTOPTIONS;
        }
    }


    /**
     * PHP magic to implement any getter, setter, has and delete operations
     * on an instance variable.
     *
     * NOTICE: All magic getters and setters of this object which are not defined in the unsetdata array are passed to the current CPO.
     *
     * Methods like e.g. "SetVariableName($x)" and "GetVariableName()" are supported
     *
     * @access public
     * @return mixed
     */
    public function __call($name, $arguments) {
        $lowname = strtolower($name);
        $operator = substr($lowname, 0,3);
        $var = substr($lowname,3);

        if (array_key_exists($var, $this->unsetdata)) {
            return parent::__call($name, $arguments);
        }

        return $this->contentParameters[$this->currentCPO]->__call($name, $arguments);
    }


    /**
     * un/serialization methods
     */

    /**
     * Called before the StateObject is serialized
     *
     * @access protected
     * @return boolean
     */
    protected function preSerialize() {
        parent::preSerialize();

        if ($this->changed === true && ($this->synckeyChanged || $this->lastsynctime === false))
            $this->lastsynctime = time();

        return true;
    }

    /**
     * Called after the StateObject was unserialized
     *
     * @access protected
     * @return boolean
     */
    protected function postUnserialize() {
        // init with the available CPO or default
        $availableCPO = $this->normalizeType(self::DEFAULTOPTIONS);
        $this->UseCPO($availableCPO);

        return true;
    }
}
