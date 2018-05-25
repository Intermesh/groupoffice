<?php
/***********************************************
* File      :   backend/combined/combined.php
* Project   :   Z-Push
* Descr     :   Combines several backends. Each type of message
*               (Emails, Contacts, Calendar, Tasks) can be handled by
*               a separate backend.
*               As the CombinedBackend is a subclass of the default Backend
*               class, it returns by that the supported AS version is 2.5.
*               The method GetSupportedASVersion() could be implemented
*               here, checking the version with all backends.
*               But still, the lowest version in common must be
*               returned, even if some backends support a higher version.
*
* Created   :   29.11.2010
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

//include the CombinedBackend's own config file
require_once("backend/combined/config.php");

class BackendCombined extends Backend implements ISearchProvider {
    public $config;
    public $backends;
    private $activeBackend;
    private $activeBackendID;
    private $numberChangesSink;
    private $logon_done = false;

    /**
     * Constructor of the combined backend
     *
     * @access public
     */
    public function __construct() {
        parent::__construct();
        $this->config = BackendCombinedConfig::GetBackendCombinedConfig();

        $backend_values = array_unique(array_values($this->config['folderbackend']));
        foreach ($backend_values as $i) {
            ZPush::IncludeBackend($this->config['backends'][$i]['name']);
            $this->backends[$i] = new $this->config['backends'][$i]['name']();
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined %d backends loaded.", count($this->backends)));
    }

    /**
     * Authenticates the user on each backend
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     */
    public function Logon($username, $domain, $password) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->Logon('%s', '%s',***))", $username, $domain));
        if(!is_array($this->backends)){
            return false;
        }
        foreach ($this->backends as $i => $b){
            $u = $username;
            $d = $domain;
            $p = $password;
            if(isset($this->config['backends'][$i]['users'])){
                if(!isset($this->config['backends'][$i]['users'][$username])){
                    unset($this->backends[$i]);
                    continue;
                }
                if(isset($this->config['backends'][$i]['users'][$username]['username']))
                    $u = $this->config['backends'][$i]['users'][$username]['username'];
                if(isset($this->config['backends'][$i]['users'][$username]['password']))
                    $p = $this->config['backends'][$i]['users'][$username]['password'];
                if(isset($this->config['backends'][$i]['users'][$username]['domain']))
                    $d = $this->config['backends'][$i]['users'][$username]['domain'];
            }
            if($this->backends[$i]->Logon($u, $d, $p) == false){
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->Logon() failed on %s ", $this->config['backends'][$i]['name']));
                return false;
            }
        }

        $this->logon_done = true;
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->Logon() success");
        return true;
    }

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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->Setup('%s', '%s', '%s', '%s')", $store, Utils::PrintAsString($checkACLonly), $folderid, Utils::PrintAsString($readonly)));
        if(!is_array($this->backends)){
            return false;
        }
        foreach ($this->backends as $i => $b){
            $u = $store;
            if(isset($this->config['backends'][$i]['users']) && isset($this->config['backends'][$i]['users'][$store]['username'])){
                $u = $this->config['backends'][$i]['users'][$store]['username'];
            }
            if($this->backends[$i]->Setup($u, $checkACLonly, $folderid, $readonly) == false){
                ZLog::Write(LOGLEVEL_WARN, "Combined->Setup() failed");
                return false;
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->Setup() success");
        return true;
    }

    /**
     * Logs off each backend
     *
     * @access public
     * @return boolean
     */
    public function Logoff() {
        // If no Logon in done, omit Logoff
        if (!$this->logon_done)
            return true;

        ZLog::Write(LOGLEVEL_DEBUG, "Combined->Logoff()");
        foreach ($this->backends as $i => $b){
            $this->backends[$i]->Logoff();
        }
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->Logoff() success");
        return true;
    }

    /**
     * Returns an array of SyncFolder types with the entire folder hierarchy
     * from all backends combined
     *
     * provides AS 1.0 compatibility
     *
     * @access public
     * @return array SYNC_FOLDER
     */
    public function GetHierarchy(){
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetHierarchy()");
        $ha = array();
        foreach ($this->backends as $i => $b){
            if(!empty($this->config['backends'][$i]['subfolder'])){
                $f = new SyncFolder();
                $f->serverid = $i.$this->config['delimiter'].'0';
                $f->parentid = '0';
                $f->displayname = $this->config['backends'][$i]['subfolder'];
                $f->type = SYNC_FOLDER_TYPE_OTHER;
                $ha[] = $f;
            }
            $h = $this->backends[$i]->GetHierarchy();
            if(is_array($h)){
                foreach($h as $j => $f){
                    $h[$j]->serverid = $i.$this->config['delimiter'].$h[$j]->serverid;
                    if($h[$j]->parentid != '0' || !empty($this->config['backends'][$i]['subfolder'])){
                        $h[$j]->parentid = $i.$this->config['delimiter'].$h[$j]->parentid;
                    }
                    if(isset($this->config['folderbackend'][$h[$j]->type]) && $this->config['folderbackend'][$h[$j]->type] != $i){
                        $h[$j]->type = SYNC_FOLDER_TYPE_OTHER;
                    }
                }
                $ha = array_merge($ha, $h);
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetHierarchy() success");
        return $ha;
    }

    /**
     * Returns the importer to process changes from the mobile
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object(ImportChanges)
     */
    public function GetImporter($folderid = false) {
        if($folderid !== false) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->GetImporter() Content: ImportChangesCombined:('%s')", $folderid));

            // get the contents importer from the folder in a backend
            // the importer is wrapped to check foldernames in the ImportMessageMove function
            $backend = $this->GetBackend($folderid);
            if($backend === false)
                return false;
            $importer = $backend->GetImporter($this->GetBackendFolder($folderid));
            if($importer){
                return new ImportChangesCombined($this, $folderid, $importer);
            }
            return false;
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetImporter() -> Hierarchy: ImportChangesCombined()");
            //return our own hierarchy importer which send each change to the right backend
            return new ImportChangesCombined($this);
        }
    }

    /**
     * Returns the exporter to send changes to the mobile
     * the exporter from right backend for contents exporter and our own exporter for hierarchy exporter
     *
     * @param string        $folderid (opt)
     *
     * @access public
     * @return object(ExportChanges)
     */
    public function GetExporter($folderid = false){
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->GetExporter('%s')", $folderid));
        if($folderid){
            $backend = $this->GetBackend($folderid);
            if($backend == false)
                return false;
            return $backend->GetExporter($this->GetBackendFolder($folderid));
        }
        return new ExportChangesCombined($this);
    }

    /**
     * Sends an e-mail
     * This messages needs to be saved into the 'sent items' folder
     *
     * @param SyncSendMail  $sm     SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm) {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->SendMail()");
        // Convert source folderid
        if (isset($sm->source->folderid)) {
            $sm->source->folderid = $this->GetBackendFolder($sm->source->folderid);
        }
        foreach ($this->backends as $i => $b){
            if($this->backends[$i]->SendMail($sm) == true){
                return true;
            }
        }
        return false;
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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->Fetch('%s', '%s', CPO)", $folderid, $id));
        $backend = $this->GetBackend($folderid);
        if($backend == false)
            return false;
        return $backend->Fetch($this->GetBackendFolder($folderid), $id, $contentparameters);
    }

    /**
     * Returns the waste basket
     * If the wastebasket is set to one backend, return the wastebasket of that backend
     * else return the first waste basket we can find
     *
     * @access public
     * @return string
     */
    function GetWasteBasket(){
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetWasteBasket()");

        if (isset($this->activeBackend)) {
            if (!$this->activeBackend->GetWasteBasket())
                return false;
            else
                return $this->activeBackendID . $this->config['delimiter'] . $this->activeBackend->GetWasteBasket();
        }

        return false;
    }

    /**
     * Returns the content of the named attachment as stream.
     * There is no way to tell which backend the attachment is from, so we try them all
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->GetAttachmentData('%s')", $attname));
        foreach ($this->backends as $i => $b) {
            try {
                $attachment = $this->backends[$i]->GetAttachmentData($attname);
                if ($attachment instanceof SyncItemOperationsAttachment)
                    return $attachment;
            }
            catch (StatusException $s) {
                // backends might throw StatusExceptions if it's not their attachment
            }
        }
        throw new StatusException("Combined->GetAttachmentData(): no backend found", SYNC_ITEMOPERATIONSSTATUS_INVALIDATT);
    }

    /**
     * Processes a response to a meeting request.
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
        $backend = $this->GetBackend($folderid);
        if($backend === false)
            return false;
        return $backend->MeetingResponse($requestid, $this->GetBackendFolder($folderid), $response);
    }


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
        $backend = $this->GetBackend($folderid);
        if($backend === false)
            return false;
        return $backend->EmptyFolder($this->GetBackendFolder($folderid), $includeSubfolders);
    }


    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->HasChangesSink()"));

        $this->numberChangesSink = 0;

        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i]->HasChangesSink()) {
                $this->numberChangesSink++;
            }
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->HasChangesSink - Number ChangesSink found: %d", $this->numberChangesSink));

        return true;
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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->ChangesSinkInitialize('%s')", $folderid));

        $backend = $this->GetBackend($folderid);
        if($backend === false) {
            // if not backend is found we return true, we don't want this to cause an error
            return true;
        }

        if ($backend->HasChangesSink()) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->ChangesSinkInitialize('%s') is supported, initializing", $folderid));
            return $backend->ChangesSinkInitialize($this->GetBackendFolder($folderid));
        }

        // if the backend doesn't support ChangesSink, we also return true so we don't get an error
        return true;
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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->ChangesSink(%d)", $timeout));

        $notifications = array();
        if ($this->numberChangesSink == 0) {
            ZLog::Write(LOGLEVEL_DEBUG, "BackendCombined doesn't include any Sinkable backends");
        } else {
            $time_each = $timeout / $this->numberChangesSink;
            foreach ($this->backends as $i => $b) {
                if ($this->backends[$i]->HasChangesSink()) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCombined->ChangesSink - Calling in '%s' with %d", get_class($b), $time_each));

                    $notifications_backend = $this->backends[$i]->ChangesSink($time_each);
                    //preppend backend delimiter
                    for ($c = 0; $c < count($notifications_backend); $c++) {
                        $notifications_backend[$c] = $i . $this->config['delimiter'] . $notifications_backend[$c];
                    }
                    $notifications = array_merge($notifications, $notifications_backend);
                }
            }
        }

        return $notifications;
    }

    /**
     * Finds the correct backend for a folder
     *
     * @param string        $folderid       combinedid of the folder
     *
     * @access public
     * @return object
     */
    public function GetBackend($folderid){
        $pos = strpos($folderid, $this->config['delimiter']);
        if($pos === false)
            return false;
        $id = substr($folderid, 0, $pos);
        if(!isset($this->backends[$id]))
            return false;

        $this->activeBackend = $this->backends[$id];
        $this->activeBackendID = $id;
        return $this->backends[$id];
    }

    /**
     * Returns an understandable folderid for the backend
     *
     * @param string        $folderid       combinedid of the folder
     *
     * @access public
     * @return string
     */
    public function GetBackendFolder($folderid){
        $pos = strpos($folderid, $this->config['delimiter']);
        if($pos === false)
            return false;
        return substr($folderid,$pos + strlen($this->config['delimiter']));
    }

    /**
     * Returns backend id for a folder
     *
     * @param string        $folderid       combinedid of the folder
     *
     * @access public
     * @return object
     */
    public function GetBackendId($folderid){
        $pos = strpos($folderid, $this->config['delimiter']);
        if($pos === false)
            return false;
        return substr($folderid, 0, $pos);
    }

    /**
     * Indicates which AS version is supported by the backend.
     * Return the lowest version supported by the backends used.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        $version = ZPush::ASV_14;
        foreach ($this->backends as $i => $b) {
            $subversion = $this->backends[$i]->GetSupportedASVersion();
            if ($subversion < $version) {
                $version = $subversion;
            }
        }
        return $version;
    }

    /**
     * Returns the BackendCombined as it implements the ISearchProvider interface
     * This could be overwritten by the global configuration
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider() {
        return $this;
    }


    /*-----------------------------------------------------------------------------------------
    -- ISearchProvider
    ------------------------------------------------------------------------------------------*/
    /**
     * Indicates if a search type is supported by this SearchProvider
     * It supports all the search types, searches are delegated.
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->SupportsType('%s')", $searchtype));
        $i = $this->getSearchBackend($searchtype);

        return $i !== false;
    }


    /**
     * Queries the LDAP backend
     *
     * @param string                        $searchquery        string to be searched for
     * @param string                        $searchrange        specified searchrange
     * @param SyncResolveRecipientsPicture  $searchpicture      limitations for picture
     *
     * @access public
     * @return array        search results
     * @throws StatusException
     */
    public function GetGALSearchResults($searchquery, $searchrange, $searchpicture) {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetGALSearchResults()");
        $i = $this->getSearchBackend(ISearchProvider::SEARCH_GAL);

        $result = false;
        if ($i !== false) {
            $result = $this->backends[$i]->GetGALSearchResults($searchquery, $searchrange, $searchpicture);
        }

        return $result;
    }

    /**
     * Searches for the emails on the server
     *
     * @param ContentParameter $cpo
     *
     * @return array
     */
    public function GetMailboxSearchResults($cpo) {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->GetMailboxSearchResults()");
        $i = $this->getSearchBackend(ISearchProvider::SEARCH_MAILBOX);

        $result = false;
        if ($i !== false) {
            //Convert $cpo GetSearchFolderid
            $cpo->SetSearchFolderid($this->GetBackendFolder($cpo->GetSearchFolderid()));
            $result = $this->backends[$i]->GetMailboxSearchResults($cpo, $i . $this->config['delimiter']);
        }

        return $result;
    }


    /**
    * Terminates a search for a given PID
    *
    * @param int $pid
    *
    * @return boolean
    */
    public function TerminateSearch($pid) {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->TerminateSearch()");
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                $this->backends[$i]->TerminateSearch($pid);
            }
        }

        return true;
    }


    /**
     * Disconnects backends
     *
     * @access public
     * @return boolean
     */
    public function Disconnect() {
        ZLog::Write(LOGLEVEL_DEBUG, "Combined->Disconnect()");
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                $this->backends[$i]->Disconnect();
            }
        }

        return true;
    }


    /**
     * Returns the first backend that support a search type
     *
     * @param string    $searchtype
     *
     * @access private
     * @return string
     */
    private function getSearchBackend($searchtype) {
        foreach ($this->backends as $i => $b) {
            if ($this->backends[$i] instanceof ISearchProvider) {
                if ($this->backends[$i]->SupportsType($searchtype)) {
                    return $i;
                }
            }
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Combined->getSearchBackend('%s') No support found!", $searchtype));

        return false;
    }

    /**
     * Returns the email address and the display name of the user. Used by autodiscover and caldav.
     *
     * @param string        $username           The username
     *
     * @access public
     * @return Array
     */
    public function GetUserDetails($username) {
        // Find a backend that can provide the information
        foreach ($this->backends as $backend) {
            if (method_exists($backend, "GetUserDetails")) {
                return $backend->GetUserDetails($username);
            }
        }
        return parent::GetUserDetails($username);
    }
}
