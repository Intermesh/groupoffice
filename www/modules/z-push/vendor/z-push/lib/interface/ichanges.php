<?php
/***********************************************
* File      :   ichanges.php
* Project   :   Z-Push
* Descr     :   Generic IChanges interface. This interface can
*               not be implemented alone.
*               IImportChanges and IExportChanges interfaces
*               inherit from this interface
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

interface IChanges {
    /**
     * Constructor
     *
     * @throws StatusException
     */

    /**
     * Initializes the state and flags
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean      status flag
     * @throws StatusException
     */
    public function Config($state, $flags = 0);

    /**
     * Configures additional parameters used for content synchronization
     *
     * @param ContentParameters         $contentparameters
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function ConfigContentParameters($contentparameters);

    /**
     * Reads and returns the current state
     *
     * @access public
     * @return string
     */
    public function GetState();

    /**
     * Sets the states from move operations.
     * When src and dst state are set, a MOVE operation is being executed.
     *
     * @param mixed         $srcState
     * @param mixed         (opt) $dstState, default: null
     *
     * @access public
     * @return boolean
     */
    public function SetMoveStates($srcState, $dstState = null);

    /**
     * Gets the states of special move operations.
     *
     * @access public
     * @return array(0 => $srcState, 1 => $dstState)
     */
    public function GetMoveStates();
}
