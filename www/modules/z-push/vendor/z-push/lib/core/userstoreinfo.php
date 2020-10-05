<?php
/***********************************************
* File      :   userstoreinfo.php
* Project   :   Z-Push
* Descr     :   Contains information about user and his store.
*
* Created   :   14.05.2012
*
* Copyright 2007 - 2018 Zarafa Deutschland GmbH
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
*************************************************/

class UserStoreInfo {
    private $foldercount;
    private $storesize;
    private $fullname;
    private $emailaddress;

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct() {
        $this->foldercount = 0;
        $this->storesize = 0;
        $this->fullname = null;
        $this->emailaddress = null;
    }

    /**
     * Sets data for the user's store.
     *
     * @param int $foldercount
     * @param int $storesize
     * @param string $fullname
     * @param string $emailaddress
     *
     * @access public
     * @return void
     */
    public function SetData($foldercount, $storesize, $fullname, $emailaddress) {
        $this->foldercount = $foldercount;
        $this->storesize = $storesize;
        $this->fullname = $fullname;
        $this->emailaddress = $emailaddress;
    }

    /**
     * Returns the number of folders in user's store.
     *
     * @access public
     * @return int
     */
    public function GetFolderCount() {
        return $this->foldercount;
    }

    /**
     * Returns the user's store size in bytes.
     *
     * @access public
     * @return int
     */
    public function GetStoreSize() {
        return $this->storesize;
    }

    /**
     * Returns the fullname of the user.
     *
     * @access public
     * @return string
     */
    public function GetFullName() {
        return $this->fullname;
    }

    /**
     * Returns the email address of the user.
     *
     * @access public
     * @return string
     */
    public function GetEmailAddress() {
        return $this->emailaddress;
    }
}