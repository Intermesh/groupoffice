<?php
/***********************************************
* File      :   syncobjectbrokenexception.php
* Project   :   Z-Push
* Descr     :   Indicates that an object was identified as broken.
*               The SyncObject may be available for further analisis
*
* Created   :   06.02.2012
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

class SyncObjectBrokenException extends ZPushException {
    protected $defaultLogLevel = LOGLEVEL_WARN;
    private $syncObject;

    /**
     * Returns the SyncObject which caused this Exception (if set)
     *
     * @access public
     * @return SyncObject
     */
    public function GetSyncObject() {
        return isset($this->syncObject) ? $this->syncObject : false;
    }

    /**
     * Sets the SyncObject which caused the exception so it can be later retrieved
     *
     * @param SyncObject    $syncobject
     *
     * @access public
     * @return boolean
     */
    public function SetSyncObject($syncobject) {
        $this->syncObject = $syncobject;
        return true;
    }
}
