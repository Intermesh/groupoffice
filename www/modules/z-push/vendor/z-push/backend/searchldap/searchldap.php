<?php
/***********************************************
* File      :   searchLDAP.php
* Project   :   Z-Push
* Descr     :   A ISearchProvider implementation to
*               query a ldap server for GAL
*               information.
*
* Created   :   03.08.2010
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

require_once("backend/searchldap/config.php");

class BackendSearchLDAP implements ISearchProvider {
    private $connection;

    /**
     * Initializes the backend to perform the search
     * Connects to the LDAP server using the values from the configuration
     *
     *
     * @access public
     * @return
     * @throws StatusException
     */
    public function __construct() {
        if (!function_exists("ldap_connect")) {
            throw new StatusException("BackendSearchLDAP(): php-ldap is not installed. Search aborted.", SYNC_SEARCHSTATUS_STORE_SERVERERROR, null, LOGLEVEL_FATAL);
        }

        // Connect
        if (defined('LDAP_SERVER_URI')) {
            $this->connection = @ldap_connect(LDAP_SERVER_URI);
            @ldap_set_option($this->connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        }
        else {
            $this->connection = false;
            throw new StatusException("BackendSearchLDAP(): No LDAP server URI defined! Search aborted.", SYNC_SEARCHSTATUS_STORE_CONNECTIONFAILED, null, LOGLEVEL_ERROR);
        }

        // Authenticate
        if (constant('ANONYMOUS_BIND') === true) {
            if(! @ldap_bind($this->connection)) {
                $this->connection = false;
                throw new StatusException("BackendSearchLDAP(): Could not bind anonymously to server! Search aborted.", SYNC_SEARCHSTATUS_STORE_CONNECTIONFAILED, null, LOGLEVEL_ERROR);
            }
        }
        else if (constant('LDAP_BIND_USER') != "") {
            if(! @ldap_bind($this->connection, LDAP_BIND_USER, LDAP_BIND_PASSWORD)) {
                $this->connection = false;
                throw new StatusException(sprintf("BackendSearchLDAP(): Could not bind to server with user '%s' and specified password! Search aborted.", LDAP_BIND_USER), SYNC_SEARCHSTATUS_STORE_ACCESSDENIED, null, LOGLEVEL_ERROR);
            }
        }
        else {
            // it would be possible to use the users login and password to authenticate on the LDAP server
            // the main $backend has to keep these values so they could be used here
            $this->connection = false;
            throw new StatusException("BackendSearchLDAP(): neither anonymous nor default bind enabled. Other options not implemented.", SYNC_SEARCHSTATUS_STORE_CONNECTIONFAILED, null, LOGLEVEL_ERROR);
        }
    }

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
        return ($searchtype == ISearchProvider::SEARCH_GAL);
    }


    /**
     * Queries the LDAP backend.
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
        global $ldap_field_map;
        if (isset($this->connection) && $this->connection !== false) {
            $searchfilter = str_replace("SEARCHVALUE", $searchquery, LDAP_SEARCH_FILTER);
            $result = @ldap_search($this->connection, LDAP_SEARCH_BASE, $searchfilter);
            if (!$result) {
                ZLog::Write(LOGLEVEL_ERROR, "BackendSearchLDAP: Error in search query. Search aborted");
                return false;
            }

            // get entry data as array
            $searchresult = ldap_get_entries($this->connection, $result);

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
            $querycnt = $searchresult['count'];
            //do not return more results as requested in range
            $querylimit = (($rangeend + 1) < $querycnt) ? ($rangeend + 1) : $querycnt;
            $items['range'] = $rangestart.'-'.($querylimit-1);
            $items['searchtotal'] = $querycnt;

            $rc = 0;
            for ($i = $rangestart; $i < $querylimit; $i++) {
                foreach ($ldap_field_map as $key=>$value ) {
                    if (isset($searchresult[$i][$value])) {
                        if (is_array($searchresult[$i][$value]))
                            $items[$rc][$key] = $searchresult[$i][$value][0];
                        else
                            $items[$rc][$key] = $searchresult[$i][$value];
                    }
                }
                // fallback to displayname if firstname and lastname not set
                if (LDAP_SEARCH_NAME_FALLBACK && (!isset($items[$rc][SYNC_GAL_LASTNAME]) && !isset($items[$rc][SYNC_GAL_FIRSTNAME])) && isset($items[$rc][SYNC_GAL_DISPLAYNAME])) {
                    $items[$rc][SYNC_GAL_LASTNAME] = $items[$rc][SYNC_GAL_DISPLAYNAME];
                }
                $rc++;
            }

            return $items;
        }
        else
            return false;
    }

    /**
     * Searches for the emails on the server
     *
     * @param ContentParameter $cpo
     *
     * @return array
     */
    public function GetMailboxSearchResults($cpo) {
        return array();
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
     * Disconnects from LDAP
     *
     * @access public
     * @return boolean
     */
    public function Disconnect() {
        if ($this->connection)
            @ldap_close($this->connection);

        return true;
    }
}
