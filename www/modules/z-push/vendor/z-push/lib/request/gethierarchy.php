<?php
/***********************************************
* File      :   gethierarchy.php
* Project   :   Z-Push
* Descr     :   Provides the GETHIERARCHY command
*
* Created   :   16.02.2012
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

class GetHierarchy extends RequestProcessor {

    /**
     * Handles the GetHierarchy command
     * simply returns current hierarchy of all folders
     *
     * @param int       $commandCode
     *
     * @access public
     * @return boolean
     */
    public function Handle($commandCode) {
        try {
            $folders = self::$backend->GetHierarchy();
            if (!$folders || empty($folders))
                throw new StatusException("GetHierarchy() did not return any data.");

            // TODO execute $data->Check() to see if SyncObject is valid

        }
        catch (StatusException $ex) {
            return false;
        }

        self::$encoder->StartWBXML();
        self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDERS);
        foreach ($folders as $folder) {
            self::$encoder->startTag(SYNC_FOLDERHIERARCHY_FOLDER);
            $folder->Encode(self::$encoder);
            self::$encoder->endTag();
        }
        self::$encoder->endTag();

        // save hierarchy for upcoming syncing
        return self::$deviceManager->InitializeFolderCache($folders);
    }
}
