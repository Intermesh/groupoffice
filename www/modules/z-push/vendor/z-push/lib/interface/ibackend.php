<?php
/***********************************************
* File      :   ibackend.php
* Project   :   Z-Push
* Descr     :   All Z-Push backends must implement this interface.
*               It describes all generic methods expected to be
*               implemented by a backend.
*               The next abstraction layer is the default Backend
*               implementation which already implements some generics.
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

interface IBackend {
    const HIERARCHYNOTIFICATION = 'hierarchynotification';
    /**
     * Returns a IStateMachine implementation used to save states
     *
     * @access public
     * @return boolean/object       if false is returned, the default Statemachine is
     *                              used else the implementation of IStateMachine
     */
    public function GetStateMachine();

    /**
     * Returns a ISearchProvider implementation used for searches
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider();

    /**
     * Indicates which AS version is supported by the backend.
     * Depending on this value the supported AS version announced to the
     * mobile device is set.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion();

    /**
     * Authenticates the user
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     * @throws FatalException   e.g. some required libraries are unavailable
     */
    public function Logon($username, $domain, $password);

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
    public function Setup($store, $checkACLonly = false, $folderid = false, $readonly = false);

    /**
     * Logs off
     * non critical operations closing the session should be done here
     *
     * @access public
     * @return boolean
     */
    public function Logoff();

    /**
     * Returns an array of SyncFolder types with the entire folder hierarchy
     * on the server (the array itself is flat, but refers to parents via the 'parent' property
     *
     * provides AS 1.0 compatibility
     *
     * @access public
     * @return array SYNC_FOLDER
     */
    public function GetHierarchy();

    /**
     * Returns the importer to process changes from the mobile
     * If no $folderid is given, hierarchy data will be imported
     * With a $folderid a content data will be imported
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object       implements IImportChanges
     * @throws StatusException
     */
    public function GetImporter($folderid = false);

    /**
     * Returns the exporter to send changes to the mobile
     * If no $folderid is given, hierarchy data should be exported
     * With a $folderid a content data is expected
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object       implements IExportChanges
     * @throws StatusException
     */
    public function GetExporter($folderid = false);

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * Basically two things can be done
     *      1) Send the message to an SMTP server as-is
     *      2) Parse the message, and send it some other way
     *
     * @param SyncSendMail        $sm         SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm);

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
    public function Fetch($folderid, $id, $contentparameters);

    /**
     * Returns the waste basket
     *
     * The waste basked is used when deleting items; if this function returns a valid folder ID,
     * then all deletes are handled as moves and are sent to the backend as a move.
     * If it returns FALSE, then deletes are handled as real deletes
     *
     * @access public
     * @return string
     */
    public function GetWasteBasket();

    /**
     * Returns the content of the named attachment as stream. The passed attachment identifier is
     * the exact string that is returned in the 'AttName' property of an SyncAttachment.
     * Any information necessary to locate the attachment must be encoded in that 'attname' property.
     * Data is written directly - 'print $data;'
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname);

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
    public function EmptyFolder($folderid, $includeSubfolders = true);

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
    public function MeetingResponse($requestid, $folderid, $response);

    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink();

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
    public function ChangesSinkInitialize($folderid);

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
    public function ChangesSink($timeout = 30);

    /**
     * Applies settings to and gets informations from the device
     *
     * @param SyncObject    $settings (SyncOOF, SyncUserInformation, SyncRightsManagementTemplates possible)
     *
     * @access public
     * @return SyncObject   $settings
     */
    public function Settings($settings);

    /**
     * Resolves recipients
     *
     * @param SyncObject        $resolveRecipients
     *
     * @access public
     * @return SyncObject       $resolveRecipients
     */
    public function ResolveRecipients($resolveRecipients);

    /**
     * Returns the email address and the display name of the user. Used by autodiscover.
     *
     * @param string        $username           The username
     *
     * @access public
     * @return Array
     */
    public function GetUserDetails($username);

    /**
     * Returns the username of the currently active user
     *
     * @access public
     * @return Array
     */
    public function GetCurrentUsername();

    /**
     * Indicates if the Backend supports folder statistics.
     *
     * @access public
     * @return boolean
     */
    public function HasFolderStats();

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
    public function GetFolderStat($store, $folderid);

    /**
     * Returns the policy name for the user.
     * If the backend returns false, the 'default' policy is used.
     * If the backend returns any other name than 'default' the policygroup with
     * that name (defined in the policies.ini file) will be applied for this user.
     *
     * @access public
     * @return string|boolean
     */
    public function GetUserPolicyName();

    /**
     * Returns the backend ID of the folder of the KOE GAB.
     *
     * @param string $foldername
     *
     * @access public
     * @return string|boolean
     */
    public function GetKoeGabBackendFolderId($foldername);

    /**
     * Returns a KoeSignatures object.
     *
     * @access public
     * @return KoeSignatures
     */
    public function GetKoeSignatures();

    /**
     * Returns information about the user's store:
     * number of folders, store size, full name, email address.
     *
     * @access public
     * @return UserStoreInfo
     */
    public function GetUserStoreInfo();
}
