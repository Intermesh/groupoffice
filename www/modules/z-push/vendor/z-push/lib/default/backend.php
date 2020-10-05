<?php
/***********************************************
* File      :   backend.php
* Project   :   Z-Push
* Descr     :   This is what C++ people
*               (and PHP5) would call an
*               abstract class. The
*               backend module itself is
*               responsible for converting any
*               necessary types and formats.
*
*               If you wish to implement a new
*               backend, all you need to do is
*               to subclass the following class
*               (or implement an IBackend)
*               and place the subclassed file in
*               the backend/yourBackend directory. You can
*               then use your backend by
*               specifying it in the config.php file
*
* Created   :   01.10.2007
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

abstract class Backend implements IBackend {
    protected $permanentStorage;
    protected $stateStorage;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
    }

    /**
     * Returns a IStateMachine implementation used to save states
     * The default StateMachine should be used here, so, false is fine
     *
     * @access public
     * @return boolean/object
     */
    public function GetStateMachine() {
        return false;
    }

    /**
     * Returns a ISearchProvider implementation used for searches
     * the SearchProvider is just a stub
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider() {
        return new SearchProvider();
    }

    /**
     * Indicates which AS version is supported by the backend.
     * By default AS version 2.5 (ASV_25) is returned (Z-Push 1 standard).
     * Subclasses can overwrite this method to set another AS version
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_25;
    }

    /*********************************************************************
     * Methods to be implemented
     *
     * public function Logon($username, $domain, $password);
     * public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false);
     * public function Logoff();
     * public function GetHierarchy();
     * public function GetImporter($folderid = false);
     * public function GetExporter($folderid = false);
     * public function SendMail($sm);
     * public function Fetch($folderid, $id, $contentparameters);
     * public function GetWasteBasket();
     * public function GetAttachmentData($attname);
     * public function MeetingResponse($requestid, $folderid, $response);
     *
     */

    /**
     * Deletes all contents of the specified folder.
     * This is generally used to empty the trash (wastebasked), but could also be used on any
     * other folder.
     *
     * @param string        $folderid
     * @param boolean       $includeSubfolders      (opt) also delete sub folders, default true
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function EmptyFolder($folderid, $includeSubfolders = true) {
        return false;
    }

    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        return false;
    }

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBacken->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if there is any problem with that folder
     */
     public function ChangesSinkInitialize($folderid) {
         return false;
     }

    /**
     * The actual ChangesSink.
     * For max. the $timeout value this method should block and if no changes
     * are available return an empty array.
     * If changes are available a list of folderids is expected.
     *
     * @param int           $timeout        max. amount of seconds to block
     *
     * @access public
     * @return array
     */
    public function ChangesSink($timeout = 30) {
        return array();
    }

    /**
     * Applies settings to and gets informations from the device
     *
     * @param SyncObject    $settings (SyncOOF, SyncUserInformation, SyncRightsManagementTemplates possible)
     *
     * @access public
     * @return SyncObject   $settings
     */
    public function Settings($settings) {
        if ($settings instanceof SyncOOF) {
            $isget = !empty($settings->bodytype);
            $settings = new SyncOOF();
            if ($isget) {
                //oof get
                $settings->oofstate = 0;
                $settings->Status = SYNC_SETTINGSSTATUS_SUCCESS;
            } else {
                //oof set
                $settings->Status = SYNC_SETTINGSSTATUS_PROTOCOLLERROR;
            }
        }
        if ($settings instanceof SyncUserInformation) {
            $settings->Status = SYNC_SETTINGSSTATUS_SUCCESS;
            if (Request::GetProtocolVersion() >= 14.1) {
                $account = new SyncAccount();
                $emailaddresses = new SyncEmailAddresses();
                $emailaddresses->smtpaddress[] = ZPush::GetBackend()->GetUserDetails(Request::GetUser())['emailaddress'];
                $emailaddresses->primarysmtpaddress = ZPush::GetBackend()->GetUserDetails(Request::GetUser())['emailaddress'];
                $account->emailaddresses = $emailaddresses;
                $settings->accounts[] = $account;
            }
            else {
                $settings->emailaddresses = array(ZPush::GetBackend()->GetUserDetails(Request::GetUser())['emailaddress']);
            }

            $settings->emailaddresses = array(ZPush::GetBackend()->GetUserDetails(Request::GetUser())['emailaddress']);

        }
        if ($settings instanceof SyncRightsManagementTemplates) {
            $settings->Status = SYNC_COMMONSTATUS_IRMFEATUREDISABLED;
        }
        return $settings;
    }

    /**
     * Resolves recipients
     *
     * @param SyncObject        $resolveRecipients
     *
     * @access public
     * @return SyncObject       $resolveRecipients
     */
    public function ResolveRecipients($resolveRecipients) {
        $r = new SyncResolveRecipients();
        $r->status = SYNC_RESOLVERECIPSSTATUS_PROTOCOLERROR;
        return $r;
    }

    /**
     * Returns the email address and the display name of the user. Used by autodiscover.
     *
     * @param string        $username           The username
     *
     * @access public
     * @return Array
     */
    public function GetUserDetails($username) {
        return array('emailaddress' => $username, 'fullname' => $username);
    }

    /**
     * Returns the username and store of the currently active user
     *
     * @access public
     * @return Array
     */
    public function GetCurrentUsername() {
        return $this->GetUserDetails(Request::GetUser());
    }

    /**
     * Indicates if the Backend supports folder statistics.
     *
     * @access public
     * @return boolean
     */
    public function HasFolderStats() {
        return false;
    }

    /**
     * Returns a status indication of the folder.
     * If there are changes in the folder, the returned value must change.
     * The returned values are compared with '===' to determine if a folder needs synchronization or not.
     *
     * @param string $store         the store where the folder resides
     * @param string $folderid      the folder id
     *
     * @access public
     * @return string
     */
    public function GetFolderStat($store, $folderid) {
        // As this is not implemented, the value returned will change every hour.
        // This will only be called if HasFolderStats() returns true.
        return "not implemented-".gmdate("Y-m-d-H");
    }

    /**
     * Returns a KoeSignatures object.
     *
     * @access public
     * @return KoeSignatures
     */
    public function GetKoeSignatures() {
        return new KoeSignatures();
    }

    /**
     * Returns information about the user's store:
     * number of folders, store size, full name, email address.
     *
     * @access public
     * @return UserStoreInfo
     */
    public function GetUserStoreInfo() {
        return new UserStoreInfo();
    }

    /**----------------------------------------------------------------------------------------------------------
     * Protected methods for BackendStorage
     *
     * Backends can use a permanent and a state related storage to save additional data
     * used during the synchronization.
     *
     * While permament storage is bound to the device and user, state related data works linked
     * to the regular states (and its counters).
     *
     * Both consist of a StateObject, while the backend can decide what to save in it.
     *
     * Before using $this->permanentStorage and $this->stateStorage the initilize methods have to be
     * called from the backend.
     *
     * Backend->LogOff() must call $this->SaveStorages() so the data is written to disk!
     *
     * These methods are an abstraction layer for StateManager->Get/SetBackendStorage()
     * which can also be used independently.
     */

    /**
     * Loads the permanent storage data of the user and device
     *
     * @access protected
     * @return
     */
    protected function InitializePermanentStorage() {
        if (!isset($this->permanentStorage)) {
            try {
                $this->permanentStorage = ZPush::GetDeviceManager()->GetStateManager()->GetBackendStorage(StateManager::BACKENDSTORAGE_PERMANENT);
            }
            catch (StateNotYetAvailableException $snyae) {
                $this->permanentStorage = new StateObject();
            }
            catch(StateNotFoundException $snfe) {
                $this->permanentStorage = new StateObject();
            }
        }
    }

   /**
     * Loads the state related storage data of the user and device
     * All data not necessary for the next state should be removed
     *
     * @access protected
     * @return
     */
    protected function InitializeStateStorage() {
        if (!isset($this->stateStorage)) {
            try {
                $this->stateStorage = ZPush::GetDeviceManager()->GetStateManager()->GetBackendStorage(StateManager::BACKENDSTORAGE_STATE);
            }
            catch (StateNotYetAvailableException $snyae) {
                $this->stateStorage = new StateObject();
            }
            catch(StateNotFoundException $snfe) {
                $this->stateStorage = new StateObject();
            }
        }
    }

   /**
     * Saves the permanent and state related storage data of the user and device
     * if they were loaded previousily
     * If the backend storage is used this should be called
     *
     * @access protected
     * @return
     */
    protected function SaveStorages() {
        if (isset($this->permanentStorage)) {
            try {
                ZPush::GetDeviceManager()->GetStateManager()->SetBackendStorage($this->permanentStorage, StateManager::BACKENDSTORAGE_PERMANENT);
            }
            catch (StateNotYetAvailableException $snyae) { }
            catch(StateNotFoundException $snfe) { }
        }
        if (isset($this->stateStorage)) {
            try {
                ZPush::GetDeviceManager()->GetStateManager()->SetBackendStorage($this->stateStorage, StateManager::BACKENDSTORAGE_STATE);
            }
            catch (StateNotYetAvailableException $snyae) { }
            catch(StateNotFoundException $snfe) { }
        }
    }

    /**
     * Returns the policy name for the user.
     * If the backend returns false, the 'default' policy is used.
     * If the backend returns any other name than 'default' the policygroup with
     * that name (defined in the policies.ini file) will be applied for this user.
     *
     * @access public
     * @return string|boolean
     */
    public function GetUserPolicyName() {
        return false;
    }

    /**
     * Returns the backend ID of the folder of the KOE GAB.
     *
     * @param string $foldername
     *
     * @access public
     * @return string|boolean
     */
    public function GetKoeGabBackendFolderId($foldername) {
        return false;
    }
}
