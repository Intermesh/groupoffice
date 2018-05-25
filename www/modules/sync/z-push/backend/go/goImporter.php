<?php
/***********************************************
* File      :   backend/combined/importer.php
* Project   :   Z-Push
* Descr     :   Importer class for the combined backend.
*
* Created   :   11.05.2010
*
* Copyright 2007 - 2012 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation with the following additional
* term according to sec. 7:
*
* According to sec. 7 of the GNU Affero General Public License, version 3,
* the terms of the AGPL are supplemented with the following terms:
*
* "Zarafa" is a registered trademark of Zarafa B.V.
* "Z-Push" is a registered trademark of Zarafa Deutschland GmbH
* The licensing of the Program under the AGPL does not imply a trademark license.
* Therefore any rights, title and interest in our trademarks remain entirely with us.
*
* However, if you propagate an unmodified version of the Program you are
* allowed to use the term "Z-Push" to indicate that you distribute the Program.
* Furthermore you may use our trademarks where it is necessary to indicate
* the intended purpose of a product or service provided you use it in accordance
* with honest practices in industrial or commercial matters.
* If you want to propagate modified versions of the Program under the name "Z-Push",
* you may only do so if you have a written permission by Zarafa Deutschland GmbH
* (to acquire a permission please contact Zarafa at trademark@zarafa.com).
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

class GoImporter implements IImportChanges {
    private $backend;
    private $folderid;
    private $icc;

    /**
     * Constructor of the GoImporter class
     *
     * @param object $backend
     * @param StringHelper $folderid
     * @param object $importer
     *
     * @access public
     */
    public function __construct(&$backend, $folderid = false, $icc = false) {
        $this->backend = $backend;
        $this->folderid = $folderid;
        $this->icc = &$icc;
    }

    /**
     * Loads objects which are expected to be exported with the state
     * Before importing/saving the actual message from the mobile, a conflict detection should be done
     *
     * @param ContentParameters         $contentparameters         class of objects
     * @param StringHelper                    $state
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function LoadConflicts($contentparameters, $state) {
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->LoadConflicts() icc not configured");
            return false;
        }
        $this->icc->LoadConflicts($contentparameters, $state);
    }

    /**
     * Imports a single message
     *
     * @param StringHelper        $id
     * @param SyncObject    $message
     *
     * @access public
     * @return boolean/string               failure / id of message
     */
    public function ImportMessageChange($id, $message) {
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->ImportMessageChange() icc not configured");
            return false;
        }
        return $this->icc->ImportMessageChange($id, $message);
    }

    /**
     * Imports a deletion. This may conflict if the local object has been modified
     *
     * @param StringHelper        $id
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageDeletion($id, $asSoftDelete = false) {
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->ImportMessageDeletion() icc not configured");
            return false;
        }
        return $this->icc->ImportMessageDeletion($id, $asSoftDelete);
    }

    /**
     * Imports a change in 'read' flag
     * This can never conflict
     *
     * @param StringHelper        $id
     * @param int           $flags
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageReadFlag($id, $flags) {
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->ImportMessageReadFlag() icc not configured");
            return false;
        }
        return $this->icc->ImportMessageReadFlag($id, $flags);
    }

    /**
     * Imports a move of a message. This occurs when a user moves an item to another folder
     *
     * @param StringHelper        $id
     * @param StringHelper        $newfolder
     *
     * @access public
     * @return boolean
     */
    public function ImportMessageMove($id, $newfolder) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImporter->ImportMessageMove('%s', '%s')", $id, $newfolder));
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->ImportMessageMove icc not configured");
            return false;
        }
        if($this->backend->GetBackendId($this->folderid) != $this->backend->GetBackendId($newfolder)){
            ZLog::Write(LOGLEVEL_WARN, "GoImporter->ImportMessageMove() cannot move message between two backends");
            return false;
        }
        return $this->icc->ImportMessageMove($id, $this->backend->GetBackendFolder($newfolder));
    }


    /**----------------------------------------------------------------------------------------------------------
     * Methods to import hierarchy
     */

    /**
     * Imports a change on a folder
     *
     * @param object        $folder         SyncFolder
     *
     * @access public
     * @return boolean/string               status/id of the folder
     */
    public function ImportFolderChange($folder) {
        $id = $folder->serverid;
        $parent = $folder->parentid;
        ZLog::Write(LOGLEVEL_DEBUG, "GoImporter->ImportFolderChange()");
        if($parent == '0') {
            if($id) {
                $backendid = $this->backend->GetBackendId($id);
            }
            else {
                $backendid = $this->backend->config['rootcreatefolderbackend'];
            }
        }
        else {
            $backendid = $this->backend->GetBackendId($parent);
            $parent = $this->backend->GetBackendFolder($parent);
        }

        if(!empty($this->backend->config['backends'][$backendid]['subfolder']) && $id == $backendid.$this->backend->config['delimiter'].'0') {
            ZLog::Write(LOGLEVEL_WARN, "GoImporter->ImportFolderChange() cannot change static folder");
            return false;
        }

        if($id != false) {
            if($backendid != $this->backend->GetBackendId($id)) {
                ZLog::Write(LOGLEVEL_WARN, "GoImporter->ImportFolderChange() cannot move folder between two backends");
                return false;
            }
            $id = $this->backend->GetBackendFolder($id);
        }
				
				ZLog::Write(LOGLEVEL_DEBUG, "ImportFolderChange backend: ".$backendid);

        $this->icc = $this->backend->getBackend($backendid)->GetImporter();
        $res = $this->icc->ImportFolderChange($folder);
				
				if(!$res){
					ZLog::Write(LOGLEVEL_DEBUG, 'GoImporter->ImportFolderChange() failed for '.$backendid);
					return false;
				}
        ZLog::Write(LOGLEVEL_DEBUG, 'GoImporter->ImportFolderChange() success');
				$folder->serverid = $backendid . $this->backend->config['delimiter'] . $res->serverid;
        return $folder;
    }

   /**
     * Imports a folder deletion
     *
     * @param SyncFolder    $folder         at least "serverid" needs to be set
     *
     * @access public
     * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
     */
    public function ImportFolderDeletion($folder) {
		 $id = $folder->BackendId;
        $parent = isset($folder->parentid) ? $folder->parentid : false;
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("GoImporter->ImportFolderDeletion('%s', '%s'), $id, $parent"));
        $backendid = $this->backend->GetBackendId($id);
        if(!empty($this->backend->config['backends'][$backendid]['subfolder']) && $id == $backendid.$this->backend->config['delimiter'].'0') {
            ZLog::Write(LOGLEVEL_WARN, "GoImporter->ImportFolderDeletion() cannot change static folder");
            return false; //we can not change a static subfolder
        }

        $backend = $this->backend->GetBackend($id);
        $id = $this->backend->GetBackendFolder($id);

        if($parent != '0')
            $parent = $this->backend->GetBackendFolder($parent);

        $this->icc = $backend->GetImporter();
		  $folder->serverid = $id;
        $folder->parentid = $parent;
        $res = $this->icc->ImportFolderDeletion($folder);
        ZLog::Write(LOGLEVEL_DEBUG, 'GoImporter->ImportFolderDeletion() success');
        return $res;
    }


    /**
     * Initializes the state and flags
     *
     * @param StringHelper        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean      status flag
     */
    public function Config($state, $flags = 0) {
        ZLog::Write(LOGLEVEL_DEBUG, 'GoImporter->Config(...)');
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->Config() icc not configured");
            return false;
        }
        $this->icc->Config($state, $flags);
        ZLog::Write(LOGLEVEL_DEBUG, 'GoImporter->Config() success');
    }

    /**
     * Reads and returns the current state
     *
     * @access public
     * @return StringHelper
     */
    public function GetState() {
        if (!$this->icc) {
            ZLog::Write(LOGLEVEL_ERROR, "GoImporter->GetState() icc not configured");
            return false;
        }
        return $this->icc->GetState();
    }
		
	/**
	 * Configures additional parameters used for content synchronization
	 *
	 * @param ContentParameters         $contentparameters
	 *
	 * @access public
	 * @return boolean
	 * @throws StatusException
	 */
	public function ConfigContentParameters($contentparameters) {
	   ZLog::Write(LOGLEVEL_DEBUG, "GoImporter->ConfigContentParameters()");
	   if (!$this->icc) {
		   ZLog::Write(LOGLEVEL_ERROR, "GoImporter->ConfigContentParameters() icc not configured");
		   return false;
	   }
	   $this->icc->ConfigContentParameters($contentparameters);
	   ZLog::Write(LOGLEVEL_DEBUG, "GoImporter->ConfigContentParameters() success");
	}

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
	public function SetMoveStates($srcState, $dstState = null) {
	   ZLog::Write(LOGLEVEL_DEBUG, "GoImporter->SetMoveStates()");
	   if (!$this->icc) {
		   ZLog::Write(LOGLEVEL_ERROR, "GoImporter->SetMoveStates() icc not configured");
		   return false;
	   }
	   $this->icc->SetMoveStates($srcState, $dstState);
	   ZLog::Write(LOGLEVEL_DEBUG, "GoImporter->SetMoveStates() success");
	}

	/**
	 * Gets the states of special move operations.
	 *
	 * @access public
	 * @return array(0 => $srcState, 1 => $dstState)
	 */
	public function GetMoveStates() {
	   if (!$this->icc) {
		   ZLog::Write(LOGLEVEL_ERROR, "GoImporter->GetMoveStates() icc not configured");
		   return false;
	   }
	   return $this->icc->GetMoveStates();
	}

}


/**
 * The ImportHierarchyChangesCombinedWrap class wraps the importer given in ExportChangesCombined->Config.
 * It prepends the backendid to all folderids and checks foldertypes.
 */

class ImportHierarchyChangesCombinedWrap {
    private $ihc;
    private $backend;
    private $backendid;

    /**
     * Constructor of the GoImporter class
     *
     * @param StringHelper $backendid
     * @param object $backend
     * @param object $ihc
     *
     * @access public
     */
    public function __construct($backendid, &$backend, &$ihc) {
        ZLog::Write(LOGLEVEL_DEBUG, "ImportHierarchyChangesCombinedWrap->ImportHierarchyChangesCombinedWrap('$backendid',...)");
        $this->backendid = $backendid;
        $this->backend =& $backend;
        $this->ihc = &$ihc;
    }

    /**
     * Imports a change on a folder
     *
     * @param object        $folder         SyncFolder
     *
     * @access public
     * @return boolean/string               status/id of the folder
     */
    public function ImportFolderChange($folder) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportHierarchyChangesCombinedWrap->ImportFolderChange('%s')", $folder->serverid));
        $folder->serverid = $this->backendid.$this->backend->config['delimiter'].$folder->serverid;
        if($folder->parentid != '0' || !empty($this->backend->config['backends'][$this->backendid]['subfolder'])){
            $folder->parentid = $this->backendid.$this->backend->config['delimiter'].$folder->parentid;
        }
        if(isset($this->backend->config['folderbackend'][$folder->type]) && $this->backend->config['folderbackend'][$folder->type] != $this->backendid){
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("not using folder: '%s' ('%s')", $folder->displayname, $folder->serverid));
            return true;
        }
        ZLog::Write(LOGLEVEL_DEBUG, "ImportHierarchyChangesCombinedWrap->ImportFolderChange() success");
        return $this->ihc->ImportFolderChange($folder);
    }

    /**
     * Imports a folder deletion
     *
     * @param StringHelper        $id
     *
     * @access public
     *
     * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
     */
    public function ImportFolderDeletion($folder) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("ImportHierarchyChangesCombinedWrap->ImportFolderDeletion('%s')", $folder->serverid));
        $folder->serverid = $this->backendid . $this->backend->config['delimiter'] . $folder->serverid;
        return $this->ihc->ImportFolderDeletion($folder);
    }
}
