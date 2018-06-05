<?php
/***********************************************
* File      :   iipcprovider.php
* Project   :   Z-Push
* Descr     :   Interface for interprocess communication
*               providers for different purposes
*
* Created   :   20.10.2011
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

interface IIpcProvider
{
    /**
     * Constructor
     *
	 * @param int $type
	 * @param int $allocate
	 * @param string $class
	 */
    public function __construct($type, $allocate, $class);

    /**
     * Reinitializes the IPC data. If the provider has no way of performing
     * this action, it should return 'false'.
     *
     * @access public
     * @return boolean
     */
    public function ReInitIPC();

    /**
     * Cleans up the IPC data block.
     *
     * @access public
     * @return boolean
     */
    public function Clean();

    /**
     * Indicates if the IPC is active.
     *
     * @access public
     * @return boolean
     */
    public function IsActive();

    /**
     * Blocks the class mutex.
     * Method blocks until mutex is available!
     * ATTENTION: make sure that you *always* release a blocked mutex!
     *
     * @access public
     * @return boolean
     */
    public function BlockMutex();

    /**
     * Releases the class mutex.
     * After the release other processes are able to block the mutex themselves.
     *
     * @access public
     * @return boolean
     */
    public function ReleaseMutex();

    /**
     * Indicates if the requested variable is available in IPC data.
     *
     * @param int   $id     int indicating the variable
     *
     * @access public
     * @return boolean
     */
    public function HasData($id = 2);

    /**
     * Returns the requested variable from IPC data.
     *
     * @param int   $id     int indicating the variable
     *
     * @access public
     * @return mixed
     */
    public function GetData($id = 2);

    /**
     * Writes the transmitted variable to IPC data.
     * Subclasses may never use an id < 2!
     *
     * @param mixed $data   data which should be saved into IPC data
     * @param int   $id     int indicating the variable (bigger than 2!)
     *
     * @access public
     * @return boolean
     */
    public function SetData($data, $id = 2);
}
