<?php
/***********************************************
* File      :   iexportchanges.php
* Project   :   Z-Push
* Descr     :   IExportChanges interface. It's responsible for
*               exporting (sending) data, content and hierarchy changes.
*               This interface extends the IChanges interface.
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

interface IExportChanges extends IChanges {
    /**
     * Sets the importer where the exporter will sent its changes to
     * This exporter should also be ready to accept calls after this
     *
     * @param object        &$importer      Implementation of IImportChanges
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function InitializeExporter(&$importer);

    /**
     * Returns the amount of changes to be exported
     *
     * @access public
     * @return int
     */
    public function GetChangeCount();

    /**
     * Synchronizes a change to the configured importer
     *
     * @access public
     * @return array        with status information
     */
    public function Synchronize();
}
