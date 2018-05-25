<?php
/***********************************************
* File      :   exporter.php
* Project   :   Z-Push
* Descr     :   This is a generic class that is
*               used by both the proxy importer
*               (for outgoing messages) and our
*               local importer (for incoming
*               messages). Basically all shared
*               conversion data for converting
*               to and from MAPI objects is in here.
*
* Created   :   14.02.2011
*
* Copyright 2007 - 2013, 2015 - 2016 Zarafa Deutschland GmbH
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


/**
 * This is our ICS exporter which requests the actual exporter from ICS and makes sure
 * that the ImportProxies are used.
 */

class ExportChangesICS implements IExportChanges{
    private $folderid;
    private $store;
    private $session;
    private $restriction;
    private $contentparameters;
    private $flags;
    private $exporterflags;
    private $exporter;
    private $moveSrcState;
    private $moveDstState;

    /**
     * Constructor
     *
     * @param mapisession       $session
     * @param mapistore         $store
     * @param string             (opt)
     *
     * @access public
     * @throws StatusException
     */
    public function __construct($session, $store, $folderid = false) {
        // Open a hierarchy or a contents exporter depending on whether a folderid was specified
        $this->session = $session;
        $this->folderid = $folderid;
        $this->store = $store;
        $this->restriction = false;

        try {
            if($folderid) {
                $entryid = mapi_msgstore_entryidfromsourcekey($store, $folderid);
            }
            else {
                $storeprops = mapi_getprops($this->store, array(PR_IPM_SUBTREE_ENTRYID, PR_IPM_PUBLIC_FOLDERS_ENTRYID));
                if (ZPush::GetBackend()->GetImpersonatedUser() == 'system') {
                    $entryid = $storeprops[PR_IPM_PUBLIC_FOLDERS_ENTRYID];
                }
                else {
                    $entryid = $storeprops[PR_IPM_SUBTREE_ENTRYID];
                }
            }

            $folder = false;
            if ($entryid) {
                $folder = mapi_msgstore_openentry($this->store, $entryid);
                if (!$folder) {
                    ZLog::Write(LOGLEVEL_WARN, sprintf("ExportChangesICS(): Error, mapi_msgstore_openentry() failed: 0x%08X", mapi_last_hresult()));
                }
            }

            // Get the actual ICS exporter
            if ($folder) {
                if ($folderid) {
                    $this->exporter = mapi_openproperty($folder, PR_CONTENTS_SYNCHRONIZER, IID_IExchangeExportChanges, 0 , 0);
                }
                else {
                    $this->exporter = mapi_openproperty($folder, PR_HIERARCHY_SYNCHRONIZER, IID_IExchangeExportChanges, 0 , 0);
                }
            }
            else {
                $this->exporter = false;
            }
        }
        catch (MAPIException $me) {
            $this->exporter = false;
            // We return the general error SYNC_FSSTATUS_CODEUNKNOWN (12) which is also SYNC_STATUS_FOLDERHIERARCHYCHANGED (12)
            // if this happened while doing content sync, the mobile will try to resync the folderhierarchy
            throw new StatusException(sprintf("ExportChangesICS('%s','%s','%s'): Error, unable to open folder: 0x%X", $session, $store, Utils::PrintAsString($folderid), mapi_last_hresult()), SYNC_FSSTATUS_CODEUNKNOWN);
        }
    }

    /**
     * Configures the exporter
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function Config($state, $flags = 0) {
        $this->exporterflags = 0;
        $this->flags = $flags;

        // this should never happen
        if ($this->exporter === false || is_array($state))
            throw new StatusException("ExportChangesICS->Config(): Error, exporter not available", SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_ERROR);

        // change exporterflags if we are doing a ContentExport
        if($this->folderid) {
            $this->exporterflags |= SYNC_NORMAL | SYNC_READ_STATE;

            // Initial sync, we don't want deleted items. If the initial sync is chunked
            // we check the change ID of the syncstate (0 at initial sync)
            // On subsequent syncs, we do want to receive delete events.
            if(strlen($state) == 0 || bin2hex(substr($state,4,4)) == "00000000") {
                if (!($this->flags & BACKEND_DISCARD_DATA))
                    ZLog::Write(LOGLEVEL_DEBUG, "ExportChangesICS->Config(): synching inital data");
                $this->exporterflags |= SYNC_NO_SOFT_DELETIONS | SYNC_NO_DELETIONS;
            }
        }

        if($this->flags & BACKEND_DISCARD_DATA) {
            $this->exporterflags |= SYNC_CATCHUP;
            $this->exporterflags |= SYNC_STATE_READONLY;
        }

        // Put the state information in a stream that can be used by ICS
        $stream = mapi_stream_create();
        if(strlen($state) == 0)
            $state = hex2bin("0000000000000000");

        if (!($this->flags & BACKEND_DISCARD_DATA))
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ExportChangesICS->Config() initialized with state: 0x%s", bin2hex($state)));

        mapi_stream_write($stream, $state);
        $this->statestream = $stream;
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
    public function ConfigContentParameters($contentparameters){
        $filtertype = $contentparameters->GetFilterType();
        switch($contentparameters->GetContentClass()) {
            case "Email":
                $this->restriction = ($filtertype || !Utils::CheckMapiExtVersion('7')) ? MAPIUtils::GetEmailRestriction(Utils::GetCutOffDate($filtertype)) : false;
                break;
            case "Calendar":
                $this->restriction = ($filtertype || !Utils::CheckMapiExtVersion('7')) ? MAPIUtils::GetCalendarRestriction($this->store, Utils::GetCutOffDate($filtertype)) : false;
                break;
            default:
            case "Contacts":
            case "Tasks":
                $this->restriction = false;
                break;
        }

        $this->contentParameters = $contentparameters;
    }


    /**
     * Sets the importer the exporter will sent it's changes to
     * and initializes the Exporter
     *
     * @param object        &$importer  Implementation of IImportChanges
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function InitializeExporter(&$importer) {
        // Because we're using ICS, we need to wrap the given importer to make it suitable to pass
        // to ICS. We do this in two steps: first, wrap the importer with our own PHP importer class
        // which removes all MAPI dependency, and then wrap that class with a C++ wrapper so we can
        // pass it to ICS

        // this should never happen!
        if($this->exporter === false || !isset($this->statestream) || !isset($this->flags) || !isset($this->exporterflags) ||
            ($this->folderid && !isset($this->contentParameters)) )
            throw new StatusException("ExportChangesICS->InitializeExporter(): Error, exporter or essential data not available", SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_ERROR);

        // PHP wrapper
        $phpwrapper = new PHPWrapper($this->session, $this->store, $importer, $this->folderid);

        // with a folderid we are going to get content
        if($this->folderid) {
            $phpwrapper->ConfigContentParameters($this->contentParameters);

            // ICS c++ wrapper
            $mapiimporter = mapi_wrap_importcontentschanges($phpwrapper);
            $includeprops = false;
        }
        else {
            $mapiimporter = mapi_wrap_importhierarchychanges($phpwrapper);
            $includeprops = array(PR_SOURCE_KEY, PR_DISPLAY_NAME);
        }

        if (!$mapiimporter)
            throw new StatusException(sprintf("ExportChangesICS->InitializeExporter(): Error, mapi_wrap_import_*_changes() failed: 0x%X", mapi_last_hresult()), SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_WARN);

        $ret = mapi_exportchanges_config($this->exporter, $this->statestream, $this->exporterflags, $mapiimporter, $this->restriction, $includeprops, false, 1);
        if(!$ret)
            throw new StatusException(sprintf("ExportChangesICS->InitializeExporter(): Error, mapi_exportchanges_config() failed: 0x%X", mapi_last_hresult()), SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_WARN);

        $changes = mapi_exportchanges_getchangecount($this->exporter);
        if($changes || !($this->flags & BACKEND_DISCARD_DATA))
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("ExportChangesICS->InitializeExporter() successfully. %d changes ready to sync for '%s'.", $changes, ($this->folderid)?bin2hex($this->folderid) : 'hierarchy'));

        return $ret;
    }

    /**
     * Indicates if the exporter was configured with the BACKEND_DISCARD_DATA flag.
     *
     * @access public
     * @return boolean
     */
    public function HasDiscardDataFlag() {
        if (isset($this->flags) && $this->flags & BACKEND_DISCARD_DATA) {
            return true;
        }
        return false;
    }

    /**
     * Reads the current state from the Exporter
     *
     * @access public
     * @return string
     * @throws StatusException
     */
    public function GetState() {
        $error = false;
        if(!isset($this->statestream) || $this->exporter === false)
            $error = true;

        if($error === true || mapi_exportchanges_updatestate($this->exporter, $this->statestream) != true )
            throw new StatusException(sprintf("ExportChangesICS->GetState(): Error, state not available or unable to update: 0x%X", mapi_last_hresult()), (($this->folderid)?SYNC_STATUS_FOLDERHIERARCHYCHANGED:SYNC_FSSTATUS_CODEUNKNOWN), null, LOGLEVEL_WARN);

        mapi_stream_seek($this->statestream, 0, STREAM_SEEK_SET);

        $state = "";
        while(true) {
            $data = mapi_stream_read($this->statestream, 4096);
            if(strlen($data))
                $state .= $data;
            else
                break;
        }

        return $state;
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
        return array($this->moveSrcState, $this->moveDstState);
    }

    /**
     * Returns the amount of changes to be exported
     *
     * @access public
     * @return int
     */
     public function GetChangeCount() {
        if ($this->exporter)
            return mapi_exportchanges_getchangecount($this->exporter);
        else
            return 0;
    }

    /**
     * Synchronizes a change
     *
     * @access public
     * @return array
     */
    public function Synchronize() {
        if ($this->flags & BACKEND_DISCARD_DATA) {
            ZLog::Write(LOGLEVEL_WARN, 'ExportChangesICS->Synchronize(): not supported in combination with the BACKEND_DISCARD_DATA flag.');
            return false;
        }

        if ($this->exporter) {
            return mapi_exportchanges_synchronize($this->exporter);
        }
            return false;
    }
}
