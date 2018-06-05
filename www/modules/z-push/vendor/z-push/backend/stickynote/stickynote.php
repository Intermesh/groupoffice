<?php
/***********************************************
* File      :   stickynote.php
* Project   :   PHP-Push
* Descr     :   This backend is based on 'Vcarddir' and implements a 
*           :   StickyNote interface against a Postgres server
*
* Created   :   8/28/2017
*
* Copyright 2017 Karl Denninger
*
* Karl Denninger released this code as AGPLv3 here (ZP)
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
* Consult the REQUIREMENTS file for details on installation and setup
************************************************/

// config file
require_once("backend/stickynote/config.php");

class BackendStickyNote extends BackendDiff {
    /**
     * @var StickyNoteClient
     */
    private $_stickynote;
    private $_collection = array();
    private $_user;
    private $_domain;
    private $_dbconn;
    private $_result;

    private $_changessinkinit;
    private $_sinkdata;

    /**
     * Constructor
     */
    public function __construct() {
        if (!function_exists("pg_connect")) {
            throw new FatalException("BackendStickyNote(): Postgres extension php-pgsql not found", 0, null, LOGLEVEL_FATAL);
        }

        $this->_changessinkinit = false;
        $this->_sinkdata = 0;
    }

    /**
     * Login to the StickyNote backend
     * NOTE: There is no ACTUAL authentication performed!  You MUST have another
     * ACTUAL authenticating backend defined, such as IMAP, which checks 
     * passwords.  We simply accept what you give us, and ignore the password.
     *
     * @see IBackend::Logon()
     */
    public function Logon($username, $domain, $password) {
        if (defined('STICKYNOTE_MUSTNOTBESET'))
            throw new FatalException("BackendStickyNote(): Configuration file has not been set up; review REQUIREMENTS and edit config.php.", 0, NULL, LOGLEVEL_FATAL);

        $this->_user = $username;
        $this->_domain = $domain;

        $_connstring = sprintf("host='%s' port='%s' dbname='%s' user='%s' password='%s'", STICKYNOTE_SERVER, STICKYNOTE_PORT, STICKYNOTE_DATABASE, STICKYNOTE_USER, STICKYNOTE_PASSWORD);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->pg_conn(): '%s'", $_connstring));

        $this->_dbconn = pg_connect($_connstring);
        if ($this->_dbconn == false)
            throw new FatalException("BackendStickyNote(): Connection to Postgres backend failed", 0, NULL, LOGLEVEL_FATAL);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->Logon(): User '%s' Domain '%s'  accepted on StickyNote", $username, $domain));
        return true;
    }

    /**
     * The connections to Stickynote require that we shut down the Postgres
     * connection if a valid one exists.  So do that.
     * @see IBackend::Logoff()
     */
    public function Logoff() {
        if ($this->_dbconn != null) {
            pg_close($this->_dbconn);
            $this->_dbconn = false;
        }
        $this->SaveStorages();

        ZLog::Write(LOGLEVEL_DEBUG, "BackendStickyNote->Logoff(): disconnected from Postgres server");

        unset($this->_sinkdata);

        return true;
    }

    /**
     * StickyNote doesn't need to handle SendMail
     * @see IBackend::SendMail()
     */
    public function SendMail($sm) {
        return false;
    }

    /**
     * No attachments in StickyNote
     * @see IBackend::GetAttachmentData()
     */
    public function GetAttachmentData($attname) {
        return false;
    }

    /**
     * Deletes are always permanent deletes. Messages doesn't get moved.
     * @see IBackend::GetWasteBasket()
     */
    public function GetWasteBasket() {
        return false;
    }

    /**
     * Get a list of all the folders we are going to sync.
     * There's only one....
     * @see BackendDiff::GetFolderList()
     */
    public function GetFolderList() {
        $folders = array();
        $folder = $this->StatFolder("N");
        $folders[] = $folder;
        return $folders;
    }

    /**
     * Returning a SyncFolder
     * @see BackendDiff::GetFolder()
     */
    public function GetFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->GetFolder('%s')", $id));
        $folder = new SyncFolder();
        $folder->parentid = "0";
        $folder->displayname = "Notes";
        $folder->serverid = $id;
        $folder->type = SYNC_FOLDER_TYPE_NOTE;
        return $folder;
    }

    /**
     * Returns information on the folder.
     * @see BackendDiff::StatFolder()
     */
    public function StatFolder($id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->StatFolder('%s')", $id));

    // Return the Display Name of the folder.  No parent, as there's only one.

        $folder = array();
        $folder["mod"] = "Notes";
        $folder["id"] = $id;
        $folder["parent"] = "0";
        return $folder;
    }

    /**
     * ChangeFolder is not supported under StickyNote
     * @see BackendDiff::ChangeFolder()
     */
    public function ChangeFolder($folderid, $oldid, $displayname, $type) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangeFolder('%s','%s','%s','%s')", $folderid, $oldid, $displayname, $type));
        return false;
    }

    /**
     * DeleteFolder is not supported under StickyNote
     * @see BackendDiff::DeleteFolder()
     */
    public function DeleteFolder($id, $parentid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->DeleteFolder('%s','%s')", $id, $parentid));
        return false;
    }

    /**
     * Get a list of all the messages.
     * @see BackendDiff::GetMessageList()
     */
    public function GetMessageList($folderid, $cutoffdate) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->GetMessageList('%s','%s')", $folderid, $cutoffdate));
        $messages = array();

        $_param = array();
        if ($cutoffdate) {
            array_push($_param, $this->_user, $this->_domain, $cutoffdate);
            $this->_result = pg_query_params($this->_dbconn, "select ordinal, extract(epoch from modified)::integer from note where modified <= timestamptz 'epoch' + $3 * interval '1 second' and login=$1 and domain=$2 and deleted is false", $_param);
        } else {
            array_push($_param, $this->_user, $this->_domain);
            $this->_result = pg_query_params($this->_dbconn, "select ordinal, extract(epoch from modified)::integer from note where login=$1 and domain=$2 and deleted is false", $_param);
        }
        if (pg_result_status($this->_result) != PGSQL_TUPLES_OK) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendStickyNote->GetMessageList(Failed to return a valid message list, Postgres error [%s])", pg_last_error($this->_dbconn)));
        } else {    
            $_affected = pg_affected_rows($this->_result);
            for ($_count = 0; $_count < $_affected; $_count++) {
                $message = array();
                $message["id"] = pg_fetch_result($this->_result, $_count, 0);
                $message["mod"] = pg_fetch_result($this->_result, $_count, 1);
                $message["flags"] = 1;    // Always mark as 'seen'

                $messages[] = $message;
            }
        }
        pg_free_result($this->_result);
        return $messages;
    }

    /**
     * Get a SyncObject by its ID (in this case, a SyncNote)
     * @see BackendDiff::GetMessage()
     */
    public function GetMessage($folderid, $id, $contentparameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->GetMessage('%s','%s')", $folderid,  $id));

        // Look up the message in the database

        $_params = array();
        array_push($_params, $id, $this->_user, $this->_domain);

        $this->_result = pg_query_params($this->_dbconn, "select *, extract(epoch from modified)::integer as changed from note where ordinal = $1 and login = $2 and domain = $3 and deleted is false", $_params);
        if (pg_result_status($this->_result) != PGSQL_TUPLES_OK) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->GetMessage(FAILED query for '%s','%s')", $folderid,  $id));
            return false;
        }
        if (pg_affected_rows($this->_result) != 1) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->GetMessage(FAILED lookup for '%s','%s')", $folderid,  $id));
            return false;
        }

        // Build the packet for a StickyNote

        $_content = pg_fetch_result($this->_result, 0, "content");

        $message = new SyncNote();
        $message->asbody = new SyncBaseBody();

        $message->asbody->type = 2;
        $message->asbody->estimatedDataSize = strlen($_content);
        $message->asbody->data = StringStreamWrapper::Open($_content);
        unset($_content);
        $message->subject = pg_fetch_result($this->_result, 0, "subject");
        $message->lastmodified = pg_fetch_result($this->_result, 0, "changed");
        $message->type = 'IPM.StickyNote';
        pg_free_result($this->_result);
        unset($_params);

        // Get categories, if any, for this note and add them to the SyncObject

        $_params = array();
        array_push($_params, $id);
        $this->_result = pg_query_params($this->_dbconn, "select tag from categories where ordinal=$1", $_params);
        $_affected = pg_affected_rows($this->_result);
        if ($_affected > 0) {
            $_categories = array();
            for ($_count = 0; $_count < $_affected; $_count++) {
                $_categories[] = pg_fetch_result($this->_result, $_count, 0);
            }
            $message->categories = $_categories;
        }
        pg_free_result($this->_result);

        return $message;
    }

    /**
     * Return id, flags and mod of a messageid
     * @see BackendDiff::StatMessage()
     */
    public function StatMessage($folderid, $id) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->StatMessage('%s','%s')", $folderid,  $id));
       
        $message = array();
        $_params = array();
        array_push($_params, $id, $this->_user, $this->_domain);
        $this->_result = pg_query_params($this->_dbconn, "select extract(epoch from modified)::integer from note where ordinal=$1 and login=$2 and domain=$3 and deleted is false", $_params);
        if (pg_result_status($this->_result) != PGSQL_TUPLES_OK) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->StatMessage(Stat call failed for '%s')", $id));
            return $message;
        } 
        if (!pg_num_rows($this->_result)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->StatMessage(Stat for empty note '%s')", $id));
            return $message;
        } 
        $message['mod'] = pg_fetch_result($this->_result, 0, 0);
        pg_free_result($this->_result);
        $message['id'] = $id;
        $message['flags'] = "1";
        return $message;
    }

    /**
     * Change/Add a message with contents received from ActiveSync
     * @see BackendDiff::ChangeMessage()
     */
    public function ChangeMessage($folderid, $id, $message, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangeMessage('%s','%s')", $folderid,  $id));
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangeMessage(Message '%s')", $message));

        // If we have a null ID then it's a new note; allocate an ordinal for 
        // it. Then insert into the database and return the stat pointer for it.
        // If we get an ID then it's an update; perform it and return stat 
        // pointer.
        // 
        $_contents = stream_get_contents($message->asbody->data, 1024000);
        if (!$id) {
            $this->_result = pg_query($this->_dbconn, "select nextval('ordinal')");
            if (pg_result_status($this->_result) != PGSQL_TUPLES_OK) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Cannot get new sequence number for item')"));
                return false;
            } 
            $id = pg_fetch_result($this->_result, 0, 0);
            pg_free_result($this->_result);

            $this->_result = pg_query($this->_dbconn, "Begin");
            if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Transaction start failure!')"));
                pg_free_result($this->_result);
                return false;
            }
            pg_free_result($this->_result);

            $_params = array();
            array_push($_params, $id, $message->subject, $_contents, $this->_user, $this->_domain);
            $this->_result = pg_query_params($this->_dbconn, "insert into note (ordinal, subject, content, login, domain) values ($1, $2, $3, $4, $5)", $_params);
            if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Cannot insert new item; fail!')"));
                pg_free_result($this->_result);
                $this->_result = pg_query($this->_dbconn, "Rollback");
                pg_free_result($this->_result);
                return false;
            }
            if (pg_affected_rows($this->_result) == 1) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangeMessage('Insert of item %s (subj '%s') succeded')", $id, $message->subject));
            } else {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Insert of item %s (subj '%s') failed')", $id, $message->subject));
                pg_free_result($this->_result);
                $this->_result = pg_query($this->_dbconn, "Rollback");
                pg_free_result($this->_result);
                return false;
            }
            unset ($_params);
            pg_free_result($this->_result);
            if ($message->categories) {
                foreach ($message->categories as $_category) {
                    $_params = array();
                    array_push($_params, $id, $_category);
                    $this->_result = pg_query_params($this->_dbconn, "insert into categories (ordinal, tag) values ($1, $2)", $_params);
                    if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Cannot insert category for item; fail!')"));
                        pg_free_result($this->_result);
                        $this->_result = pg_query($this->_dbconn, "Rollback");
                        pg_free_result($this->_result);
                        return(false);
                    }
                    pg_free_result($this->_result);
                }
                unset ($_category);
            }
        } else {
            $_params = array();
            array_push($_params, $message->subject, $_contents, $id, $this->_user, $this->_domain);
            $this->_result = pg_query_params($this->_dbconn, "update note set subject=$1, content=$2, modified=now() where ordinal=$3 and login=$4 and domain=$5", $_params);
            if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Update of item %s failed!')", $id));
            }
            if (pg_affected_rows($this->_result) == 1) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangeMessage('Update of item %s (subj '%s') succeded')", $id, $message->subject));
            } else {
                ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Update of item %s (subj '%s') failed (credential mismatch)')", $id, $message->subject));
            }
            pg_free_result($this->_result);
            unset ($_params);
            if ($message->categories) {
                $_params = array();
                array_push($_params, $id);
                $this->_result = pg_query_params($this->_dbconn, "delete from categories where ordinal=$1", $_params);
                if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                    ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Cannot clear category for item; fail!')"));
                    pg_free_result($this->_result);
                    $this->_result = pg_query($this->_dbconn, "Rollback");
                    pg_free_result($this->_result);
                    return(false);
                }
                unset ($_params);
                foreach ($message->categories as $_category) {
                    $_params = array();
                    array_push($_params, $id, $_category);
                    $this->_result = pg_query_params($this->_dbconn, "insert into categories (ordinal, tag) values ($1, $2)", $_params);
                    if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
                        ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Cannot insert category for item; fail!')"));
                        pg_free_result($this->_result);
                        $this->_result = pg_query($this->_dbconn, "Rollback");
                        pg_free_result($this->_result);
                        return(false);
                    }
                    pg_free_result($this->_result);
                }
            unset ($_category);
            }
        } 
        $this->_result = pg_query($this->_dbconn, "COMMIT");
        if (pg_result_status($this->_result) != PGSQL_COMMAND_OK) {
            ZLog::Write(LOGLEVEL_ERROR, sprintf("BackendStickyNote->ChangeMessage('Transaction commit FAIL!')"));
            pg_free_result($this->_result);
            return false;
        }
        pg_free_result($this->_result);
        return $this->StatMessage($folderid, $id);
    }

    /**
     * Change the read flag is not supported.
     * @see BackendDiff::SetReadFlag()
     */
    public function SetReadFlag($folderid, $id, $flags, $contentParameters) {
        return false;
    }

    /**
     * Delete a message from the StickyNote server.
     * @see BackendDiff::DeleteMessage()
     */
    public function DeleteMessage($folderid, $id, $contentParameters) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->DeleteMessage('%s','%s')", $folderid,  $id));
        $_params = array();
        array_push($_params, $id, $this->_user, $this->_domain);
        //
        // Relation (foreign key) constraint deletes category entries when
        // the parent is removed.
        //
        if (defined('STICKYNOTE_REALLYDELETE')) {
            $this->_result = pg_query_params($this->_dbconn, "delete from note where ordinal=$1 and login=$2 and domain=$3");
            if (pg_affected_rows($this->_result) != 1) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->DeleteMessage('%s','%s' failed)", $folderid,  $id));
                pg_free_result($this->_result);
                return false;
            }
        } else {
            $this->_result = pg_query_params($this->_dbconn, "update note set deleted = true where ordinal=$1 and login=$2 and domain=$3", $_params);
            if (pg_affected_rows($this->_result) != 1) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->DeleteMessage('%s','%s' failed)", $folderid,  $id));
                pg_free_result($this->_result);
                return false;
            }
        }
        pg_free_result($this->_result);
        return true;
    }

    /**
     * Move a message is not supported by StickyNote.
     * @see BackendDiff::MoveMessage()
     */
    public function MoveMessage($folderid, $id, $newfolderid, $contentParameters) {
        return false;
    }

    /*
     * Tell the upper layers that we have a ChangesSink.  It's simple since we
     * can easily track the last modification time of any item for a given user,
     * so we can tell the system very quickly when something has changed.
     *
     * @access public
     * @return boolean
     */
    public function HasChangesSink() {
        return true;
    }

    /*
     * Initialize the sink for a folder (there's only one)
     *
     * @param string    $folderid
     *
     * @access public
     * return boolean    Always true since there's only one folder
     */
    public function ChangesSinkInitialize($folderid) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangesSinkInitialize(): folderid '%s'", $folderid));
        $this->_changessinkinit = true;
        return $this->_changessinkinit;
    }

    /*
     * The Changesink.
     * If there's nothing to return in changes, wait 30 seconds. Otherwise
     * send back the FolderId -- there's only one.
     *
     * @param int    $timeout    How long to block if specified
     *
     * @access public
     * @return array
     */
    public function ChangesSink($timeout = 30) {
        $_notifications = array();

        // Apparently this can get called before we've initialized, which in
        // our case wouldn't matter, but for consistency return nothing if
        // that happens - or if it gets called before the database is connected.

        if ((!$this->_changessinkinit) || ($this->_dbconn == false)) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangesSink - Not initialized ChangesSink, sleep and exit"));
            sleep($timeout);
            return $_notifications;
        }
        $_param = array();
        array_push($_param, $this->_user, $this->_domain);
        $this->_result = pg_query_params($this->_dbconn, "select extract(epoch from modified)::integer from note where login=$1 and domain=$2 and deleted is false order by modified desc limit 1", $_param);
        if (pg_result_status($this->_result) != PGSQL_TUPLES_OK) {
            ZLog::Write(LOGLEVEL_WARN, sprintf("BackendStickyNote->ChangesSink(Failed to return a valid change list)"));
        } else {    
            if (pg_affected_rows($this->_result) == 1) {
                ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangesSink(There are valid notes stored)"));
                $_lastchange = pg_fetch_result($this->_result, 0, 0);
                if ($_lastchange != $this->_sinkdata) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("BackendStickyNote->ChangesSink(Change noted; tell the upper layers)"));
                    array_push($_notifications, "N");
                    $this->_sinkdata = $_lastchange;
                }
            }
        }
        pg_free_result($this->_result);
        if (empty($_notifications)) {
            sleep($timeout);
        }
        return($_notifications);
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

}
