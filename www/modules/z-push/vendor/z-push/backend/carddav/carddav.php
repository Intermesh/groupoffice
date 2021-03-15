<?php
/***********************************************
* File      :   carddav.php
* Project   :   Z-Push
* Descr     :   This backend is for carddav servers.
*
* Created   :   16.03.2013
*
* Copyright 2013 - 2016 Francisco Miguel Biete
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

// config file
require_once("backend/carddav/config.php");

class BackendCardDAV extends BackendDiff implements ISearchProvider {

    private $domain = '';
    private $username = '';
    private $url = null;
    /**
     * @var carddav_backend
     */
    private $server = null;
    private $default_url = null;
    private $gal_url = null;

    // Android only supports synchronizing 1 AddressBook per account, this is the foldername for Z-Push
    private $foldername = "contacts";

    // We can have multiple addressbooks, but the mobile device will only see one (all of them merged)
    private $addressbooks;

    private $changessinkinit;
    private $contactsetag;
    private $sinkdata;

    /**
     * Constructor
     *
     */
    public function __construct() {
        if (!function_exists("curl_init")) {
            throw new FatalException("BackendCardDAV(): php-curl is not found", 0, null, LOGLEVEL_FATAL);
        }

        $this->addressbooks = array();
        $this->changessinkinit = false;
        $this->contactsetag = array();
        $this->sinkdata = array();
    }

    /**
     * Authenticates the user - NOT EFFECTIVELY IMPLEMENTED
     * Normally some kind of password check would be done here.
     * Alternatively, the password could be ignored and an Apache
     * authentication via mod_auth_* could be done
     *
     * @param string        $username
     * @param string        $domain
     * @param string        $password
     *
     * @access public
     * @return boolean
     */
    public function Logon($username, $domain, $password) {
        $this->url = CARDDAV_PROTOCOL . '://' . CARDDAV_SERVER . ':' . CARDDAV_PORT . str_replace("%d", $domain, str_replace("%u", $username, CARDDAV_PATH));
        $this->default_url = CARDDAV_PROTOCOL . '://' . CARDDAV_SERVER . ':' . CARDDAV_PORT . str_replace("%d", $domain, str_replace("%u", $username, CARDDAV_DEFAULT_PATH));
        if (defined('CARDDAV_GAL_PATH')) {
            $this->gal_url = CARDDAV_PROTOCOL . '://' . CARDDAV_SERVER . ':' . CARDDAV_PORT . str_replace("%d", $domain, str_replace("%u", $username, CARDDAV_GAL_PATH));
        }
        else {
            $this->gal_url = false;
        }
        $this->server = new carddav_backend($this->url, CARDDAV_URL_VCARD_EXTENSION);
        $this->server->set_auth($username, $password);

        if (($connected = $this->server->check_connection())) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->Logon(): User '%s' is authenticated on '%s'", $username, $this->url));
            $this->username = $username;
            $this->domain = $domain;

            // Autodiscover all the addressbooks
            $this->discoverAddressbooks();
        }
        else {
            //TODO: get error message
            $error = '';
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->Logon(): User '%s' failed to authenticate on '%s': %s", $username, $this->url, $error));
            $this->server = null;
        }

        return $connected;
    }

    /**
     * Logs off
     *
     * @access public
     * @return boolean
     */
    public function Logoff() {
        if ($this->server != null) {
            $this->server->disconnect();
            unset($this->server);
        }

        $this->SaveStorages();

        unset($this->contactsetag);
        unset($this->sinkdata);
        unset($this->addressbooks);

        ZLog::Write(LOGLEVEL_DEBUG, "BackendCardDAV->Logoff(): disconnected from CARDDAV server");

        return true;
    }

    /**
     * Sends an e-mail
     * Not implemented here
     *
     * @param SyncSendMail  $sm     SyncSendMail object
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function SendMail($sm) {
        return false;
    }

    /**
     * Returns the waste basket
     * Not implemented here
     *
     * @access public
     * @return string
     */
    public function GetWasteBasket() {
        return false;
    }

    /**
     * Returns the content of the named attachment as stream
     * Not implemented here
     *
     * @param string        $attname
     *
     * @access public
     * @return SyncItemOperationsAttachment
     * @throws StatusException
     */
    public function GetAttachmentData($attname) {
        return false;
    }

    /**
     * Indicates if the backend has a ChangesSink.
     * A sink is an active notification mechanism which does not need polling.
     * The CardDAV backend simulates a sink by polling revision dates from the vcards
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        return true;
    }

    /**
     * The folder should be considered by the sink.
     * Folders which were not initialized should not result in a notification
     * of IBackend->ChangesSink().
     *
     * @param string        $folderid
     *
     * @access public
     * @return boolean      false if found can not be found
     */
    public function ChangesSinkInitialize($folderid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangesSinkInitialize(): folderid '%s'", $folderid));

        // We don't need the actual cards, we only need to get the changes since this moment
        $init_ok = true;
        foreach ($this->addressbooks as $addressbook) {
            try {
                $this->server->set_url($addressbook);
                $this->sinkdata[$addressbook] = $this->server->do_sync(true, false, CARDDAV_SUPPORTS_SYNC);
            }
            catch (Exception $ex) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangesSinkInitialize - Error doing the initial sync for '%s': %s", $addressbook, $ex->getMessage()));
                $init_ok = false;
            }

            if ($this->sinkdata[$addressbook] === false) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangesSinkInitialize - Error initializing the sink for '%s'", $addressbook));
                $init_ok = false;
            }

            if (CARDDAV_SUPPORTS_SYNC) {
                // we don't need to store the sinkdata if the carddav server supports native sync
                unset($this->sinkdata[$addressbook]);
            }
        }

        $this->changessinkinit = $init_ok;

        return $this->changessinkinit;
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
        $notifications = array();
        $stopat = time() + $timeout - 1;
        $changed = false;

        //We can get here and the ChangesSink not be initialized yet
        if (!$this->changessinkinit) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangesSink - Not initialized ChangesSink, sleep and exit"));
            // We sleep and do nothing else
            sleep($timeout);
            return $notifications;
        }

        // only check once to reduce pressure in the DAV server
        foreach ($this->addressbooks as $addressbook) {
            $vcards = false;
            try {
                $this->server->set_url($addressbook);
                $vcards = $this->server->do_sync(false, false, CARDDAV_SUPPORTS_SYNC);
            }
            catch (Exception $ex) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangesSink - Error resyncing vcards: %s", $ex->getMessage()));
            }

            if ($vcards === false) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangesSink - Error getting the changes"));
                return false;
            }
            else {
                $xml_vcards = new SimpleXMLElement($vcards);

                if (CARDDAV_SUPPORTS_SYNC) {
                    if (count($xml_vcards->element) > 0) {
                        $changed = true;
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangesSink - Changes detected"));
                    }
                }
                else {
                    $xml_sinkdata = new SimpleXMLElement($this->sinkdata[$addressbook]);
                    if (count($xml_vcards->element) != count($xml_sinkdata->element)) {
                        // If the number of cards is different, we know for sure, there are changes
                        $changed = true;
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangesSink - Changes detected"));
                    }
                    else {
                        // If it's the same we need to check vcard to vcard, or the original strings
                        if (strcmp($this->sinkdata[$addressbook], $vcards) != 0) {
                            $changed = true;
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangesSink - Changes detected"));
                        }
                    }
                    unset($xml_sinkdata);
                }

                unset($vcards);
                unset($xml_vcards);
            }

            if ($changed) {
                $notifications[] = $this->foldername;
            }
        }

        // Wait to timeout
        if (empty($notifications)) {
            while ($stopat > time()) {
                sleep(1);
            }
        }

        return $notifications;
    }

    /**----------------------------------------------------------------------------------------------------------
     * implemented DiffBackend methods
     */

    /**
     * Returns a list (array) of folders.
     * In simple implementations like this one, probably just one folder is returned.
     *
     * @access public
     * @return array
     */
    public function GetFolderList() {
        ZLog::Write(LOGLEVEL_DEBUG, 'BackendCardDAV::GetFolderList()');

        // The mobile will only see one
        $addressbooks = array();
        $addressbook = $this->StatFolder($this->foldername);
        $addressbooks[] = $addressbook;

        return $addressbooks;
    }

    /**
     * Returns an actual SyncFolder object
     *
     * @param string        $id           id of the folder
     *
     * @access public
     * @return object       SyncFolder with information
     */
    public function GetFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::GetFolder('%s')", $id));

        $addressbook = false;

        if ($id == $this->foldername) {
            $addressbook = new SyncFolder();
            $addressbook->serverid = $id;
            $addressbook->parentid = "0";
            $addressbook->displayname = str_replace("%d", $this->domain, str_replace("%u", $this->username, CARDDAV_CONTACTS_FOLDER_NAME));
            $addressbook->type = SYNC_FOLDER_TYPE_CONTACT;
        }

        return $addressbook;
    }

    /**
     * Returns folder stats. An associative array with properties is expected.
     *
     * @param string        $id             id of the folder
     *
     * @access public
     * @return array
     */
    public function StatFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::StatFolder('%s')", $id));

        $addressbook = $this->GetFolder($id);

        $stat = array();
        $stat["id"] = $id;
        $stat["parent"] = $addressbook->parentid;
        $stat["mod"] = $addressbook->displayname;

        return $stat;
    }

    /**
     * Creates or modifies a folder
     * Not implemented here
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
    public function ChangeFolder($folderid, $oldid, $displayname, $type) {
        return false;
    }

    /**
     * Deletes a folder
     * Not implemented here
     *
     * @param string        $id
     * @param string        $parent         is normally false
     *
     * @access public
     * @return boolean                      status - false if e.g. does not exist
     * @throws StatusException              could throw specific SYNC_FSSTATUS_* exceptions
     *
     */
    public function DeleteFolder($id, $parentid) {
        return false;
    }

    /**
     * Returns a list (array) of messages
     *
     * @param string        $folderid       id of the parent folder
     * @param long          $cutoffdate     timestamp in the past from which on messages should be returned
     *
     * @access public
     * @return array/false  array with messages or false if folder is not available
     */
    public function GetMessageList($folderid, $cutoffdate) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetMessageList('%s', '%s')", $folderid, $cutoffdate));

        $messages = array();

        foreach ($this->addressbooks as $addressbook) {
            $addressbookId = $this->convertAddressbookUrl($addressbook);

            $vcards = false;
            try {
                // We don't need the actual vcards here, we only need a list of all them
                // This petition is always "initial", and we don't "include_vcards"
                $this->server->set_url($addressbook);
                $vcards = $this->server->do_sync(true, false, CARDDAV_SUPPORTS_SYNC);
            }
            catch (Exception $ex) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetMessageList - Error getting the vcards in '%s': %s", $addressbook, $ex->getMessage()));
            }

            if ($vcards === false) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetMessageList - Error getting the vcards"));
            }
            else {
                $xml_vcards = new SimpleXMLElement($vcards);
                foreach ($xml_vcards->element as $vcard) {
                    $id = $addressbookId . "-" . $vcard->id->__toString();
                    $this->contactsetag[$id] = $vcard->etag->__toString();
                    $messages[] = $this->StatMessage($folderid, $id);
                }
            }
        }

        return $messages;
    }

    /**
     * Returns the actual SyncXXX object type.
     *
     * @param string            $folderid           id of the parent folder
     * @param string            $id                 id of the message
     * @param ContentParameters $contentparameters  parameters of the requested message (truncation, mimesupport etc)
     *
     * @access public
     * @return object/false     false if the message could not be retrieved
     */
    public function GetMessage($folderid, $id, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetMessage('%s', '%s')", $folderid, $id));

        $message = false;
        $addressbookId = $this->getAddressbookIdFromVcard($id);
        $vcardId = $this->getVcardId($id);
        $addressbookUrl = $this->getAddressbookFromId($addressbookId);

        if ($addressbookUrl !== false) {
            $xml_vcard = false;
            try {
                $this->server->set_url($addressbookUrl);
                $xml_vcard = $this->server->get_xml_vcard($vcardId);
            }
            catch (Exception $ex) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetMessage - Error getting vcard '%s' in '%s': %s", $vcardId, $addressbookId, $ex->getMessage()));
            }

            if ($xml_vcard !== false) {
                $truncsize = Utils::GetTruncSize($contentparameters->GetTruncation());
                $xml_data = new SimpleXMLElement($xml_vcard);
                $message = $this->ParseFromVCard($xml_data->element[0]->vcard->__toString(), $truncsize);
            }
        }

        if ($message === false) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetMessage(): vCard not found"));
        }

        return $message;
    }


    /**
     * Returns message stats, analogous to the folder stats from StatFolder().
     *
     * @param string        $folderid       id of the folder
     * @param string        $id             id of the message
     *
     * @access public
     * @return array
     */
    public function StatMessage($folderid, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->StatMessage('%s', '%s')", $folderid, $id));

        $message = array();
        if (!isset($this->contactsetag[$id])) {
            $addressbookId = $this->getAddressbookIdFromVcard($id);
            $vcardId = $this->getVcardId($id);
            $addressbookUrl = $this->getAddressbookFromId($addressbookId);
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->StatMessage - No contactsetag found, getting vcard '%s' in '%s'", $vcardId, $addressbookId));
            if ($addressbookUrl !== false) {
                $xml_vcard = false;
                try {
                    $this->server->set_url($addressbookUrl);
                    $xml_vcard = $this->server->get_xml_vcard($vcardId);
                }
                catch (Exception $ex) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->StatMessage - Error getting vcard '%s' in '%s': %s", $vcardId, $addressbookId, $ex->getMessage()));
                }

                if ($xml_vcard !== false) {
                    $vcard = new SimpleXMLElement($xml_vcard);
                    $this->contactsetag[$id] = $vcard->element[0]->etag->__toString();
                    unset($vcard);
                }
                unset($xml_vcard);
            }
        }
        $message["mod"] = $this->contactsetag[$id];
        $message["id"] = $id;
        $message["flags"] = 1;

        return $message;
    }

    /**
     * Called when a message has been changed on the mobile.
     * This functionality is not available for emails.
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
    public function ChangeMessage($folderid, $id, $message, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangeMessage('%s', '%s')", $folderid, $id));

        $vcard_text = $this->ParseToVCard($message);

        if ($vcard_text === false) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangeMessage - Error converting message to vCard"));
        }
        else {
            ZLog::Write(LOGLEVEL_WBXML, sprintf("BackendCardDAV->ChangeMessage - vCard\n%s\n", $vcard_text));

            $updated = false;
            if (strlen($id) == 0) {
                //no id, new vcard
                try {
                    $addressbookId = $this->getAddressbookFromUrl($this->default_url);
                    if ($addressbookId === false) {
                        $addressbookId = $this->getAddressbookFromUrl($this->addressbooks[0]);
                        $this->server->set_url($this->addressbooks[0]);
                    }
                    else {
                        $this->server->set_url($this->default_url);
                    }

                    $updated = $this->server->add($vcard_text);
                    if ($updated !== false) {
                        $id = $addressbookId . "-" . $updated;
                    }
                }
                catch (Exception $ex) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangeMessage - Error adding vcard '%s' : %s", $id, $ex->getMessage()));
                }
            }
            else {
                //id, update vcard

                $vcardId = $this->getVcardId($id);
                $addressbookUrl = $this->getAddressbookFromId($this->getAddressbookIdFromVcard($id));

                if ($addressbookUrl !== false) {
                    try {
                        $this->server->set_url($addressbookUrl);
                        $updated = $this->server->update($vcard_text, $vcardId);
                    }
                    catch (Exception $ex) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangeMessage - Error updating vcard '%s' : %s", $id, $ex->getMessage()));
                    }
                }
            }

            if ($updated !== false) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->ChangeMessage - vCard updated"));
            }
            else {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->ChangeMessage - vCard not updated"));
            }
        }

        return $this->StatMessage($folderid, $id);
    }

    /**
     * Changes the 'read' flag of a message on disk
     * Not implemented here
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
    public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
        return false;
    }

    /**
     * Called when the user has requested to delete (really delete) a message
     *
     * @param string              $folderid             id of the folder
     * @param string              $id                   id of the message
     * @param ContentParameters   $contentParameters
     *
     * @access public
     * @return boolean                      status of the operation
     * @throws StatusException              could throw specific SYNC_STATUS_* exceptions
     */
    public function DeleteMessage($folderid, $id, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->DeleteMessage('%s', '%s')", $folderid, $id));

        $deleted = false;

        $vcardId = $this->getVcardId($id);
        $addressbookUrl = $this->getAddressbookFromId($this->getAddressbookIdFromVcard($id));

        if ($addressbookUrl !== false) {
            try {
                $this->server->set_url($addressbookUrl);
                $deleted = $this->server->delete($vcardId);
            }
            catch (Exception $ex) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->DeleteMessage - Error deleting vcard: %s", $ex->getMessage()));
            }
        }

        if ($deleted) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->DeleteMessage - vCard deleted"));
        }
        else {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->DeleteMessage - cannot delete vCard"));
        }

        return $deleted;
    }

    /**
     * Called when the user moves an item on the PDA from one folder to another
     * Not implemented here
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
    public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
        return false;
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
        // TODO:
        return false;
    }


    /**
     * Indicates which AS version is supported by the backend.
     *
     * @access public
     * @return string       AS version constant
     */
    public function GetSupportedASVersion() {
        return ZPush::ASV_14;
    }


    /**
     * Returns the BackendCardDAV as it implements the ISearchProvider interface
     * This could be overwritten by the global configuration
     *
     * @access public
     * @return object       Implementation of ISearchProvider
     */
    public function GetSearchProvider() {
        return $this;
    }


    /**----------------------------------------------------------------------------------------------------------
     * public ISearchProvider methods
     */

    /**
     * Indicates if a search type is supported by this SearchProvider
     * Currently only the type ISearchProvider::SEARCH_GAL (Global Address List) is implemented
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype) {
        if ($this->gal_url !== false) {
            return ($searchtype == ISearchProvider::SEARCH_GAL);
        }
        else {
            return false;
        }
    }


    /**
     * Queries the CardDAV backend
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
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetGALSearchResults(%s, %s)", $searchquery, $searchrange));
        if ($this->gal_url !== false && $this->server !== false) {
            // Don't search if the length is < 5, we are typing yet
            if (strlen($searchquery) < CARDDAV_GAL_MIN_LENGTH) {
                return false;
            }

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetGALSearchResults searching: %s", $this->gal_url));
            try {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetGALSearchResults server is null? %d", $this->server == null));
                $this->server->set_url($this->gal_url);
                $vcards = $this->server->search_vcards(str_replace("<", "", str_replace(">", "", $searchquery)), 15, true, false,
                            defined('CARDDAV_SUPPORTS_FN_SEARCH') ? CARDDAV_SUPPORTS_FN_SEARCH : false);
            }
            catch (Exception $e) {
                $vcards = false;
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetGALSearchResults : Error in search %s", $e->getMessage()));
            }
            if ($vcards === false) {
                ZLog::Write(LOGLEVEL_ERROR, "BackendCardDAV->GetGALSearchResults : Error in search query. Search aborted");
                return false;
            }

            $xml_vcards = new SimpleXMLElement($vcards);
            unset($vcards);

            // range for the search results, default symbian range end is 50, wm 99,
            // so we'll use that of nokia
            $rangestart = 0;
            $rangeend = 50;

            if ($searchrange != '0') {
                $pos = strpos($searchrange, '-');
                $rangestart = substr($searchrange, 0, $pos);
                $rangeend = substr($searchrange, ($pos + 1));
            }
            $items = array();

            // TODO the limiting of the searchresults could be refactored into Utils as it's probably used more than once
            $querycnt = $xml_vcards->count();
            //do not return more results as requested in range
            $querylimit = (($rangeend + 1) < $querycnt) ? ($rangeend + 1) : ($querycnt == 0 ? 1 : $querycnt);
            $items['range'] = $rangestart.'-'.($querylimit - 1);
            $items['searchtotal'] = $querycnt;

            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV->GetGALSearchResults : %s entries found, returning %s to %s", $querycnt, $rangestart, $querylimit));

            $i = 0;
            $rc = 0;
            foreach ($xml_vcards->element as $xml_vcard) {
                if ($i >= $rangestart && $i < $querylimit) {
                    $contact = $this->ParseFromVCard($xml_vcard->vcard->__toString());
                    if ($contact === false) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendCardDAV->GetGALSearchResults : error converting vCard to AS contact\n%s\n", $xml_vcard->vcard->__toString()));
                    }
                    else {
                        $items[$rc][SYNC_GAL_EMAILADDRESS] = $contact->email1address;
                        if (isset($contact->fileas)) {
                            $items[$rc][SYNC_GAL_DISPLAYNAME] = $contact->fileas;
                        }
                        else if (isset($contact->firstname) || isset($contact->middlename) || isset($contact->lastname)) {
                            $items[$rc][SYNC_GAL_DISPLAYNAME] = $contact->firstname . (isset($contact->middlename) ? " " . $contact->middlename : "") . (isset($contact->lastname) ? " " . $contact->lastname : "");
                        }
                        else {
                            $items[$rc][SYNC_GAL_DISPLAYNAME] = $contact->email1address;
                        }
                        if (isset($contact->firstname)) {
                            $items[$rc][SYNC_GAL_FIRSTNAME] = $contact->firstname;
                        }
                        else {
                            $items[$rc][SYNC_GAL_FIRSTNAME] = "";
                        }
                        if (isset($contact->lastname)) {
                            $items[$rc][SYNC_GAL_LASTNAME] = $contact->lastname;
                        }
                        else {
                            $items[$rc][SYNC_GAL_LASTNAME] = "";
                        }
                        if (isset($contact->businessphonenumber)) {
                            $items[$rc][SYNC_GAL_PHONE] = $contact->businessphonenumber;
                        }
                        if (isset($contact->homephonenumber)) {
                            $items[$rc][SYNC_GAL_HOMEPHONE] = $contact->homephonenumber;
                        }
                        if (isset($contact->mobilephonenumber)) {
                            $items[$rc][SYNC_GAL_MOBILEPHONE] = $contact->mobilephonenumber;
                        }
                        if (isset($contact->title)) {
                            $items[$rc][SYNC_GAL_TITLE] = $contact->title;
                        }
                        if (isset($contact->companyname)) {
                            $items[$rc][SYNC_GAL_COMPANY] = $contact->companyname;
                        }
                        if (isset($contact->department)) {
                            $items[$rc][SYNC_GAL_OFFICE] = $contact->department;
                        }
                        if (isset($contact->nickname)) {
                            $items[$rc][SYNC_GAL_ALIAS] = $contact->nickname;
                        }
                        unset($contact);
                        $rc++;
                    }
                }
                $i++;
            }

            unset($xml_vcards);
            return $items;
        }
        else {
            unset($xml_vcards);
            return false;
        }
    }

    /**
     * Searches for the emails on the server
     *
     * @param ContentParameter $cpo
     *
     * @return array
     */
    public function GetMailboxSearchResults($cpo) {
        return false;
    }

    /**
    * Terminates a search for a given PID
    *
    * @param int $pid
    *
    * @return boolean
    */
    public function TerminateSearch($pid) {
        return true;
    }

    /**
     * Disconnects from CardDAV
     *
     * @access public
     * @return boolean
     */
    public function Disconnect() {
        return true;
    }


    /**----------------------------------------------------------------------------------------------------------
     * private vcard-specific internals
     */


    /**
     * Escapes a string
     *
     * @param string        $data           string to be escaped
     *
     * @access private
     * @return string
     */
    private function escape($data) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = $this->escape($val);
            }
            return $data;
        }
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
        $data = str_replace(array('\\', ';', ',', "\n"), array('\\\\', '\\;', '\\,', '\\n'), $data);
        return $data;
    }

    /**
     * Un-escapes a string
     *
     * @param string        $data           string to be un-escaped
     *
     * @access private
     * @return string
     */
    private function unescape($data) {
        $data = str_replace(array('\\\\', '\\;', '\\,', '\\n','\\N'),array('\\', ';', ',', "\n", "\n"),$data);
        return $data;
    }

    /**
     * Converts the vCard into SyncContact.
     * See RFC 6350 for vCard format details.
     *
     * @param string        $data           string with the vcard
     * @param int           $truncsize      truncate size requested
     * @return SyncContact
     */
    private function ParseFromVCard($data, $truncsize = -1) {
        ZLog::Write(LOGLEVEL_WBXML, sprintf("BackendCardDAV->ParseFromVCard : vCard\n%s\n", $data));

        $types = array ('dom' => 'type', 'intl' => 'type', 'postal' => 'type', 'parcel' => 'type', 'home' => 'type', 'work' => 'type',
            'pref' => 'type', 'voice' => 'type', 'fax' => 'type', 'msg' => 'type', 'cell' => 'type', 'pager' => 'type',
            'bbs' => 'type', 'modem' => 'type', 'car' => 'type', 'isdn' => 'type', 'video' => 'type',
            'aol' => 'type', 'applelink' => 'type', 'attmail' => 'type', 'cis' => 'type', 'eworld' => 'type',
            'internet' => 'type', 'ibmmail' => 'type', 'mcimail' => 'type',
            'powershare' => 'type', 'prodigy' => 'type', 'tlx' => 'type', 'x400' => 'type',
            'gif' => 'type', 'cgm' => 'type', 'wmf' => 'type', 'bmp' => 'type', 'met' => 'type', 'pmb' => 'type', 'dib' => 'type',
            'pict' => 'type', 'tiff' => 'type', 'pdf' => 'type', 'ps' => 'type', 'jpeg' => 'type', 'qtime' => 'type',
            'mpeg' => 'type', 'mpeg2' => 'type', 'avi' => 'type',
            'wave' => 'type', 'aiff' => 'type', 'pcm' => 'type',
            'x509' => 'type', 'pgp' => 'type', 'text' => 'value', 'inline' => 'value', 'url' => 'value', 'cid' => 'value', 'content-id' => 'value',
            '7bit' => 'encoding', '8bit' => 'encoding', 'quoted-printable' => 'encoding', 'base64' => 'encoding',
        );

        // Parse the vcard
        $message = new SyncContact();

        $data = str_replace("\x00", '', $data);
        $data = str_replace("\r\n", "\n", $data);
        $data = str_replace("\r", "\n", $data);
        $data = preg_replace('/(\n)([ \t])/i', '', $data);

        $lines = explode("\n", $data);

        $vcard = array();
        foreach ($lines as $line) {
            if (trim($line) == '')
                continue;
            $pos = strpos($line, ':');
            if ($pos === false)
                continue;

            $field = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            $fieldparts = preg_split('/(?<!\\\\)(\;)/i', $field, -1, PREG_SPLIT_NO_EMPTY);

            // The base type
            $type = strtolower(array_shift($fieldparts));

            // We do not care about visually grouping properties together, so strip groups off (see RFC 6350 § 3.3)
            if (preg_match('#^[a-z0-9\\-]+\\.(.+)$#i', $type, $matches)) {
                $type = $matches[1];
            }

            // Parse all field values
            $fieldvalue = array();
            foreach ($fieldparts as $fieldpart) {
                if (preg_match('/([^=]+)=(.+)/', $fieldpart, $matches)) {
                    $fieldName = strtolower($matches[1]);
                    if (!in_array($fieldName, array('value', 'type', 'encoding', 'language')))
                        continue;
                    if (isset($fieldvalue[$fieldName]) && is_array($fieldvalue[$fieldName])) {
                        if ($fieldName == 'type') {
                            $fieldvalue[$fieldName] = array_merge($fieldvalue[$fieldName], array_map('strtolower', preg_split('/(?<!\\\\)(\,)/i', $matches[2], -1, PREG_SPLIT_NO_EMPTY)));
                        } else {
                            $fieldvalue[$fieldName] = array_merge($fieldvalue[$fieldName], preg_split('/(?<!\\\\)(\,)/i', $matches[2], -1, PREG_SPLIT_NO_EMPTY));
                        }
                    } else {
                        if ($fieldName == 'type') {
                            $fieldvalue[$fieldName] = array_map('strtolower', preg_split('/(?<!\\\\)(\,)/i', $matches[2], -1, PREG_SPLIT_NO_EMPTY));
                        } else {
                            $fieldvalue[$fieldName] = preg_split('/(?<!\\\\)(\,)/i', $matches[2], -1, PREG_SPLIT_NO_EMPTY);
                        }
                    }
                } else {
                    if (!isset($types[strtolower($fieldpart)]))
                        continue;
                    $fieldvalue[$types[strtolower($fieldpart)]][] = $fieldpart;
                }
            }

            //
            switch ($type) {
                case 'categories':
                //case 'nickname':
                    $val = preg_split('/(\s)*(\\\)?\,(\s)*/i', $value);
                    break;
                default:
                    $val = preg_split('/(?<!\\\\)(\;)/i', $value);
                    break;
            }
            if (isset($fieldvalue['encoding'][0])) {
                switch (strtolower($fieldvalue['encoding'][0])) {
                    case 'q':
                    case 'quoted-printable':
                        foreach ($val as $i => $v) {
                            $val[$i] = quoted_printable_decode($v);
                        }
                        break;
                    case 'b':
                    case 'base64':
                        foreach ($val as $i => $v) {
                            $val[$i] = base64_decode($v);
                        }
                        break;
                }
            } else {
                foreach ($val as $i => $v) {
                    $val[$i] = $this->unescape($v);
                }
            }
            $fieldvalue['val'] = $val;
            $vcard[$type][] = $fieldvalue;
        }

        if (isset($vcard['email'][0]['val'][0]))
            $message->email1address = $vcard['email'][0]['val'][0];
        if (isset($vcard['email'][1]['val'][0]))
            $message->email2address = $vcard['email'][1]['val'][0];
        if (isset($vcard['email'][2]['val'][0]))
            $message->email3address = $vcard['email'][2]['val'][0];

        if (isset($vcard['tel'])) {
            foreach ($vcard['tel'] as $tel) {
                if (!isset($tel['type'])) {
                    $tel['type'] = array();
                }
                if (in_array('car', $tel['type'])) {
                    $message->carphonenumber = $tel['val'][0];
                }
                elseif (in_array('pager', $tel['type'])) {
                    $message->pagernumber = $tel['val'][0];
                }
                elseif (in_array('cell', $tel['type'])) {
                    $message->mobilephonenumber = $tel['val'][0];
                }
                elseif (in_array('main', $tel['type'])) {
                    $message->companymainphone = $tel['val'][0];
                }
                elseif (in_array('assistant', $tel['type'])) {
                    $message->assistnamephonenumber = $tel['val'][0];
                }
                elseif (in_array('text', $tel['type'])) {
                    $message->mms = $tel['val'][0];
                }
                elseif (in_array('home', $tel['type'])) {
                    if (in_array('fax', $tel['type'])) {
                        $message->homefaxnumber = $tel['val'][0];
                    }
                    elseif (empty($message->homephonenumber)) {
                        $message->homephonenumber = $tel['val'][0];
                    }
                    else {
                        $message->home2phonenumber = $tel['val'][0];
                    }
                }
                elseif (in_array('work', $tel['type'])) {
                    if (in_array('fax', $tel['type'])) {
                        $message->businessfaxnumber = $tel['val'][0];
                    }
                    elseif (empty($message->businessphonenumber)) {
                        $message->businessphonenumber = $tel['val'][0];
                    }
                    else {
                        $message->business2phonenumber = $tel['val'][0];
                    }
                }
                elseif (empty($message->homephonenumber)) {
                    $message->homephonenumber = $tel['val'][0];
                }
                elseif (empty($message->home2phonenumber)) {
                    $message->home2phonenumber = $tel['val'][0];
                }
                else {
                    $message->radiophonenumber = $tel['val'][0];
                }
            }
        }

        //;;street;city;state;postalcode;country
        if (isset($vcard['adr'])) {
            foreach ($vcard['adr'] as $adr) {
                if (empty($adr['type'])) {
                    $a = 'other';
                }
                elseif (in_array('home', $adr['type'])) {
                    $a = 'home';
                }
                elseif (in_array('work', $adr['type'])) {
                    $a = 'business';
                }
                else {
                    $a = 'other';
                }
                if (!empty($adr['val'][2])) {
                    $b=$a.'street';
                    $message->$b = $adr['val'][2];
                }
                if (!empty($adr['val'][3])) {
                    $b=$a.'city';
                    $message->$b = $adr['val'][3];
                }
                if (!empty($adr['val'][4])) {
                    $b=$a.'state';
                    $message->$b = $adr['val'][4];
                }
                if (!empty($adr['val'][5])) {
                    $b=$a.'postalcode';
                    $message->$b = $adr['val'][5];
                }
                if (!empty($adr['val'][6])) {
                    $b=$a.'country';
                    $message->$b = $adr['val'][6];
                }
            }
        }

        if (!empty($vcard['fn'][0]['val'][0]))
            $message->fileas = $vcard['fn'][0]['val'][0];
        if (!empty($vcard['n'][0]['val'][0]))
            $message->lastname = $vcard['n'][0]['val'][0];
        if (!empty($vcard['n'][0]['val'][1]))
            $message->firstname = $vcard['n'][0]['val'][1];
        if (!empty($vcard['n'][0]['val'][2]))
            $message->middlename = $vcard['n'][0]['val'][2];
        if (!empty($vcard['n'][0]['val'][3]))
            $message->title = $vcard['n'][0]['val'][3];
        if (!empty($vcard['n'][0]['val'][4]))
            $message->suffix = $vcard['n'][0]['val'][4];
        if (!empty($vcard['nickname'][0]['val'][0]))
            $message->nickname = $vcard['nickname'][0]['val'][0];
        if (!empty($vcard['bday'][0]['val'][0])) {
            $tz = date_default_timezone_get();
            date_default_timezone_set('UTC');
            $message->birthday = strtotime($vcard['bday'][0]['val'][0]);
            date_default_timezone_set($tz);
        }
        if (!empty($vcard['org'][0]['val'][0]))
            $message->companyname = $vcard['org'][0]['val'][0];
        if (!empty($vcard['note'][0]['val'][0])) {
            if (Request::GetProtocolVersion() >= 12.0) {
                $message->asbody = new SyncBaseBody();
                $message->asbody->type = SYNC_BODYPREFERENCE_PLAIN;
                $data = $vcard['note'][0]['val'][0];
                if ($truncsize > 0 && $truncsize < strlen($data)) {
                    $message->asbody->truncated = 1;
                    $data = Utils::Utf8_truncate($data, $truncsize);
                }
                else {
                    $message->asbody->truncated = 0;
                }
                $message->asbody->data = StringStreamWrapper::Open($data);
                $message->asbody->estimatedDataSize = strlen($data);
                unset($data);
            }
            else {
                $message->body = $vcard['note'][0]['val'][0];
                if ($truncsize > 0 && $truncsize < strlen($message->body)) {
                    $message->bodytruncated = 1;
                    $message->body = Utils::Utf8_truncate($message->body, $truncsize);
                }
                else {
                    $message->bodytruncated = 0;
                }
                $message->bodysize = strlen($message->body);
            }
        }

        // Support both ROLE and TITLE (RFC 6350 § 6.6.1 / § 6.6.2) as mapped to JobTitle
        if (!empty($vcard['role'][0]['val'][0]))
            $message->jobtitle = $vcard['role'][0]['val'][0];
        if (!empty($vcard['title'][0]['val'][0]))
            $message->jobtitle = $vcard['title'][0]['val'][0];

        if (!empty($vcard['url'][0]['val'][0]))
            $message->webpage = $vcard['url'][0]['val'][0];
        if (!empty($vcard['categories'][0]['val']))
            $message->categories = $vcard['categories'][0]['val'];

        if (!empty($vcard['photo'][0]['val'][0]))
            $message->picture = base64_encode($vcard['photo'][0]['val'][0]);

        return $message;
    }

    /**
     * Convert a SyncObject into vCard.
     *
     * @param SyncContact           $message        AS Contact
     * @return string               vcard text
     */
    private function ParseToVCard($message) {
        // http://tools.ietf.org/html/rfc6350
        $mapping = array(
            'fileas' => 'FN',
            'lastname;firstname;middlename;title;suffix' => 'N',
            'email1address' => 'EMAIL;PREF=1',
            'email2address' => 'EMAIL;PREF=2',
            'email3address' => 'EMAIL;PREF=3',
            'businessphonenumber' => 'TEL;TYPE=WORK,VOICE',
            'business2phonenumber' => 'TEL;TYPE=WORK,VOICE',
            'businessfaxnumber' => 'TEL;TYPE=WORK,FAX',
            'homephonenumber' => 'TEL;TYPE=HOME,VOICE',
            'home2phonenumber' => 'TEL;TYPE=HOME,VOICE',
            'homefaxnumber' => 'TEL;TYPE=HOME,FAX',
            'mobilephonenumber' => 'TEL;TYPE=CELL',
            'carphonenumber' => 'TEL;TYPE=CAR',
            'pagernumber' => 'TEL;TYPE=PAGER',
            'companymainphone' => 'TEL;TYPE=WORK,MAIN',
            'mms' => 'TEL;TYPE=TEXT',
            'radiophonenumber' => 'TEL;TYPE=RADIO,VOICE',
            'assistnamephonenumber' => 'TEL;TYPE=ASSISTANT,VOICE',
            ';;businessstreet;businesscity;businessstate;businesspostalcode;businesscountry' => 'ADR;TYPE=WORK',
            ';;homestreet;homecity;homestate;homepostalcode;homecountry' => 'ADR;TYPE=HOME',
            ';;otherstreet;othercity;otherstate;otherpostalcode;othercountry' => 'ADR',
            'companyname' => 'ORG',
            'body' => 'NOTE',
            'jobtitle' => 'ROLE',
            'webpage' => 'URL',
            'nickname' => 'NICKNAME'
        );

        $data = "BEGIN:VCARD\nVERSION:3.0\nPRODID:Z-Push\n";
        foreach ($mapping as $k => $v) {
            $val = '';
            $ks = explode(';', $k);
            foreach ($ks as $i) {
                if (!empty($message->$i))
                    $val .= $this->escape($message->$i);
                $val.=';';
            }
            if ($k == 'body' && isset($message->asbody->data)) {
                $val = stream_get_contents($message->asbody->data);
            }
            if (empty($val) || preg_match('/^(\;)+$/', $val) == 1)
                continue;
            // Support newlines in values
            $val = str_replace("\n", "\\n", $val);

            // Remove trailing ;
            $val = rtrim($val, ";");
            // Clean full name from emailaddress
            if (substr($k, 0, 5) == 'email') {
                $val = preg_replace(array('/.*</', '/>.*/'), array('', ''), $val);
            }
            if (strlen($val) > 50) {
                $data .= $v.":\n\t".substr(chunk_split($val, 50, "\n\t"), 0, -1);
            }
            else {
                $data .= $v.':'.$val."\n";
            }
        }
        if (!empty($message->categories))
            $data .= 'CATEGORIES:'.implode(',', $message->categories)."\n";
        if (!empty($message->picture))
            $data .= 'PHOTO;ENCODING=BASE64;TYPE=JPEG:'."\n\t".substr(chunk_split($message->picture, 50, "\n\t"), 0, -1);
        if (isset($message->birthday))
            $data .= 'BDAY:'.date('Y-m-d', $message->birthday)."\n";
        $data .= "END:VCARD";

        // http://en.wikipedia.org/wiki/VCard
        // TODO: add support for v4.0
        // not supported: anniversary, assistantname, children, department, officelocation, spouse, rtf

        return $data;
    }


    /**
     * Discover all the addressbooks collections for a user under a root.
     *
     */
    private function discoverAddressbooks() {
        unset($this->addressbooks);
        $this->addressbooks = array();
        $raw = $this->server->get(false, false, true);
        if ($raw !== false) {
            $xml = new SimpleXMLElement($raw);
            foreach ($xml->addressbook_element as $response) {
                if ($this->gal_url !== false) {
                    if (strcmp(urldecode($response->url), $this->gal_url) == 0) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::discoverAddressbooks() Ignoring GAL addressbook '%s'", $this->gal_url));
                        continue;
                    }
                }
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::discoverAddressbooks() Found addressbook '%s'", urldecode($response->url)));
                $this->addressbooks[] = urldecode($response->url);
            }
            unset($xml);
        }
    }

    /**
     * Returns de addressbookId of a vcard.
     *  The vcardId sent to the device is formed as [addressbookId]-[vcardId]
     *
     * @param string $vcardId       vcard ID in device.
     * @return addressbookId
     */
    private function getAddressbookIdFromVcard($vcardId) {
        $parts = explode("-", $vcardId);

        return $parts[0];
    }

    /**
     * Returns de vcard id stored in the carddav server.
     *
     * @param string $vcardId            vcard ID in device
     * @return vcard id in carddav server
     */
    private function getVcardId($vcardId) {
        $parts = explode("-", $vcardId);

        $id = "";
        for ($i = 1; $i < count($parts); $i++) {
            if ($i > 1) {
                $id .= "-";
            }
            $id .= $parts[$i];
        }

        return $id;
    }

    /**
     * Convert an addressbook url into a zpush id.
     *
     * @param string $addressbookUrl    AddressBook URL
     * @return id or false
     */
    private function convertAddressbookUrl($addressbookUrl) {
        $this->InitializePermanentStorage();

        // check if this addressbookUrl was converted before
        $addressbookId = $this->getAddressbookFromUrl($addressbookUrl);

        // nothing found, so generate a new id and put it in the cache
        if ($addressbookId === false) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::convertAddressbookUrl('%s') New addressbook", $addressbookUrl));
            // generate addressbookId and add it to the mapping
            $addressbookId = sprintf('%04x%04x', mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ));

            // addressbookId to addressbookUrl mapping
            if (!isset($this->permanentStorage->fmAidAurl))
                $this->permanentStorage->fmAidAurl = array();

            $a = $this->permanentStorage->fmAidAurl;
            $a[$addressbookId] = $addressbookUrl;
            $this->permanentStorage->fmAidAurl = $a;

            // addressbookUrl to addressbookId mapping
            if (!isset($this->permanentStorage->fmAurlAid))
                $this->permanentStorage->fmAurlAid = array();

            $b = $this->permanentStorage->fmAurlAid;
            $b[$addressbookUrl] = $addressbookId;
            $this->permanentStorage->fmAurlAid = $b;
        }

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::convertAddressbookUrl('%s') = %s", $addressbookUrl, $addressbookId));

        return $addressbookId;
    }

    /**
     * Get the URL of an addressbook zpush id.
     *
     * @param string $addressbookId     AddressBook Z-Push based ID
     * @return url or false
     */
    private function getAddressbookFromId($addressbookId) {
        $this->InitializePermanentStorage();

        $addressbookUrl = false;

        if (isset($this->permanentStorage->fmAidAurl)) {
            if (isset($this->permanentStorage->fmAidAurl[$addressbookId])) {
                $addressbookUrl = $this->permanentStorage->fmAidAurl[$addressbookId];
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::getAddressbookFromId('%s') = %s", $addressbookId, $addressbookUrl));
            }
            else {
                ZLog::Write(LOGLEVEL_WARN, sprintf("BackendCardDAV::getAddressbookFromId('%s') = %s", $addressbookId, 'not found'));
            }
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::getAddressbookFromId('%s') = %s", $addressbookId, 'not initialized!'));
        }

        return $addressbookUrl;
    }

    /**
     * Get the zpush id of an addressbook.
     *
     * @param string $addressbookUrl    AddressBook URL
     * @return id or false
     */
    private function getAddressbookFromUrl($addressbookUrl) {
        $this->InitializePermanentStorage();

        $addressbookId = false;

        if (isset($this->permanentStorage->fmAurlAid)) {
            if (isset($this->permanentStorage->fmAurlAid[$addressbookUrl])) {
                $addressbookId = $this->permanentStorage->fmAurlAid[$addressbookUrl];
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::getAddressbookFromUrl('%s') = %s", $addressbookUrl, $addressbookId));
            }
            else {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::getAddressbookFromUrl('%s') = %s", $addressbookUrl, 'not found'));
            }
        }
        else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendCardDAV::getAddressbookFromUrl('%s') = %s", $addressbookUrl, 'not initialized!'));
        }

        return $addressbookId;
    }

}
