<?php
/***********************************************
* File      :   isearchprovider.php
* Project   :   Z-Push
* Descr     :   The ISearchProvider interface for searching
*               functionalities on the mobile.
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

interface ISearchProvider {
    const SEARCH_GAL = "GAL";
    const SEARCH_MAILBOX = "MAILBOX";
    const SEARCH_DOCUMENTLIBRARY = "DOCUMENTLIBRARY";

    /**
     * Constructor
     *
     * @throws StatusException, FatalException
     */

    /**
     * Indicates if a search type is supported by this SearchProvider
     * Currently only the type SEARCH_GAL (Global Address List) is implemented
     *
     * @param string        $searchtype
     *
     * @access public
     * @return boolean
     */
    public function SupportsType($searchtype);

    /**
     * Searches the GAL.
     *
     * @param string                        $searchquery        string to be searched for
     * @param string                        $searchrange        specified searchrange
     * @param SyncResolveRecipientsPicture  $searchpicture      limitations for picture
     *
     * @access public
     * @return array        search results
     * @throws StatusException
     */
    public function GetGALSearchResults($searchquery, $searchrange, $searchpicture);

    /**
    * Searches for the emails on the server
    *
    * @param ContentParameter $cpo
    *
    * @return array
    */
    public function GetMailboxSearchResults($cpo);

    /**
    * Terminates a search for a given PID
    *
    * @param int $pid
    *
    * @return boolean
    */
    public function TerminateSearch($pid);


    /**
     * Disconnects from the current search provider
     *
     * @access public
     * @return boolean
     */
    public function Disconnect();
}
