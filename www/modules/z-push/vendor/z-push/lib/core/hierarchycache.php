<?php
/***********************************************
* File      :   hierarchycache.php
* Project   :   Z-Push
* Descr     :   HierarchyCache implementation
*
* Created   :   18.08.2011
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

class HierarchyCache {
    private $changed = false;
    protected $cacheById;
    private $cacheByIdOld;

    /**
     * Constructor of the HierarchyCache
     *
     * @access public
     * @return
     */
    public function __construct() {
        $this->cacheById = array();
        $this->cacheByIdOld = $this->cacheById;
        $this->changed = true;
    }

    /**
     * Indicates if the cache was changed
     *
     * @access public
     * @return boolean
     */
    public function IsStateChanged() {
        return $this->changed;
    }

    /**
     * Copy current CacheById to memory
     *
     * @access public
     * @return boolean
     */
    public function CopyOldState() {
        $this->cacheByIdOld = $this->cacheById;
        return true;
    }

    /**
     * Returns the SyncFolder object for a folder id
     * If $oldstate is set, then the data from the previous state is returned
     *
     * @param string    $serverid
     * @param boolean   $oldstate       (optional) by default false
     *
     * @access public
     * @return SyncObject/boolean       false if not found
     */
    public function GetFolder($serverid, $oldState = false) {
        if (!$oldState && array_key_exists($serverid, $this->cacheById)) {
            return $this->cacheById[$serverid];
        }
        else if ($oldState && array_key_exists($serverid, $this->cacheByIdOld)) {
            return $this->cacheByIdOld[$serverid];
        }
        return false;
    }

    /**
     * Adds a folder to the HierarchyCache
     *
     * @param SyncObject    $folder
     *
     * @access public
     * @return boolean
     */
    public function AddFolder($folder) {
        ZLog::Write(LOGLEVEL_DEBUG, "HierarchyCache: AddFolder() serverid: {$folder->serverid} displayname: {$folder->displayname}");

        // on update the $folder does most of the times not contain a type
        // we copy the value in this case to the new $folder object
        if (isset($this->cacheById[$folder->serverid]) && (!isset($folder->type) || $folder->type == false) && isset($this->cacheById[$folder->serverid]->type)) {
            $folder->type = $this->cacheById[$folder->serverid]->type;
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("HierarchyCache: AddFolder() is an update: used type '%s' from old object", $folder->type));
        }

        // add/update
        $this->cacheById[$folder->serverid] = $folder;
        $this->changed = true;

        return true;
    }

    /**
     * Removes a folder to the HierarchyCache
     *
     * @param string    $serverid           id of folder to be removed
     *
     * @access public
     * @return boolean
     */
    public function DelFolder($serverid) {
        $ftype = $this->GetFolder($serverid);

        ZLog::Write(LOGLEVEL_DEBUG, sprintf("HierarchyCache: DelFolder() serverid: '%s' - type: '%s'", $serverid, $ftype->type));
        unset($this->cacheById[$serverid]);
        $this->changed = true;
        return true;
    }

    /**
     * Imports a folder array to the HierarchyCache
     *
     * @param array     $folders            folders to the HierarchyCache
     *
     * @access public
     * @return boolean
     */
    public function ImportFolders($folders) {
        if (!is_array($folders))
            return false;

        $this->cacheById = array();

        foreach ($folders as $folder) {
            if (!isset($folder->type))
                continue;
            $this->AddFolder($folder);
        }
        return true;
    }

    /**
     * Exports all folders from the HierarchyCache
     *
     * @param boolean   $oldstate           (optional) by default false
     *
     * @access public
     * @return array
     */
    public function ExportFolders($oldstate = false) {
        if ($oldstate === false)
            return $this->cacheById;
        else
            return $this->cacheByIdOld;
    }

    /**
     * Returns all folder objects which were deleted in this operation
     *
     * @access public
     * @return array        with SyncFolder objects
     */
    public function GetDeletedFolders() {
        // diffing the OldCacheById with CacheById we know if folders were deleted
        return array_diff_key($this->cacheByIdOld, $this->cacheById);
    }

    /**
     * Returns some statistics about the HierarchyCache
     *
     * @access public
     * @return string
     */
    public function GetStat() {
        return sprintf("HierarchyCache is %s - Cached objects: %d", ((isset($this->cacheById))?"up":"down"), ((isset($this->cacheById))?count($this->cacheById):"0"));
    }

    /**
     * Removes internal data from the object, so this data can not be exposed.
     *
     * @access public
     * @return boolean
     */
    public function StripData() {
        unset($this->changed);
        unset($this->cacheByIdOld);
        foreach ($this->cacheById as $id => $folder) {
            $folder->StripData();
        }
        return true;
    }

    /**
     * Returns objects which should be persistent
     * called before serialization
     *
     * @access public
     * @return array
     */
    public function __sleep() {
        return array("cacheById");
    }

}
