<?php
/***********************************************
* File      :   diffstate.php
* Project   :   Z-Push
* Descr     :   This is the differential engine.
*               We do a standard differential
*               change detection by sorting both
*               lists of items by their unique id,
*               and then traversing both arrays
*               of items at once. Changes can be
*               detected by comparing items at
*               the same position in both arrays.
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

class DiffState implements IChanges {
    protected $syncstate;
    protected $backend;
    protected $flags;
    protected $contentparameters;
    protected $cutoffdate;
    protected $moveSrcState;
    protected $moveDstState;

    /**
     * Initializes the state
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean status flag
     * @throws StatusException
     */
    public function Config($state, $flags = 0) {
        if ($state == "")
            $state = array();

        if (!is_array($state))
            throw new StatusException("Invalid state", SYNC_FSSTATUS_CODEUNKNOWN);

        $this->syncstate = $state;
        $this->flags = $flags;
        return true;
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
        $this->contentparameters = $contentparameters;

        $filtertype = $contentparameters->GetFilterType();
        switch($contentparameters->GetContentClass()) {
            case "Email":
            case "Calendar":
                $this->cutoffdate = ($filtertype === false) ? 0 : Utils::GetCutOffDate($filtertype);
                break;
            case "Contacts":
            case "Tasks":
            case "Notes":
            default:
                $this->cutoffdate = false;
                break;
        }
    }

    /**
     * Returns state
     *
     * @access public
     * @return string
     * @throws StatusException
     */
    public function GetState() {
        if (!isset($this->syncstate) || !is_array($this->syncstate))
            throw new StatusException("DiffState->GetState(): Error, state not available", SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_WARN);

        return $this->syncstate;
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
        $this->moveSrcState = $srcState;
        $this->moveDstState = $dstState;
        return true;
    }

    /**
     * Gets the states of special move operations.
     *
     * @access public
     * @return array(0 => $srcState, 1 => $dstState)
    */
    public function GetMoveStates() {
        return array($this->moveSrcState, $this->moveDstState);
    }


    /**----------------------------------------------------------------------------------------------------------
     * DiffState specific stuff
     */

    /**
     * Differential mechanism
     * Compares the current syncstate to the sent $new
     *
     * @param array        $new
     *
     * @access protected
     * @return array
     */
    protected function getDiffTo($new) {
        $changes = $old = array();

        // create associative array of old items with id as key
        foreach($this->syncstate as &$item) {
            $old[$item['id']] =& $item;
        }

        // iterate through new items to identify new or changed items
        foreach($new as &$item) {
            $id = $item['id'];
            $change = array("id" => $id);

            if (!isset($old[$id])) {
                // Message in new seems to be new (add)
                $change["type"] = "change";
                $change['flags'] = SYNC_NEWMESSAGE;
                $changes[] = $change;
            } else {
                $old_item =& $old[$id];

                // Both messages are still available, compare flags and mod
                if(isset($old_item["flags"]) && isset($item["flags"]) && $old_item["flags"] != $item["flags"]) {
                    // Flags changed
                    $change["type"] = "flags";
                    $change["flags"] = $item["flags"];
                    $changes[] = $change;
                }

                // @see https://jira.z-hub.io/browse/ZP-955
                if (isset($old_item['mod']) && isset($item['mod'])) {
                    if ($old_item['mod'] != $item['mod']) {
                        $change["type"] = "change";
                        $changes[] = $change;
                    }
                }
                else if (isset($old_item['mod']) || isset($item['mod'])) {
                    $change["type"] = "change";
                    $changes[] = $change;
                }

                // unset in $old, so $old contains only the deleted items
                unset($old[$id]);
            }
        }

        // now $old contains only deleted items
        foreach($old as $id => &$item) {
            // Message in state seems to have disappeared (delete)
            $changes[] = array(
                "type" => "delete",
                "id"   => $id,
            );
        }

        return $changes;
    }

    /**
     * Update the state to reflect changes
     *
     * @param string        $type of change
     * @param array         $change
     *
     *
     * @access protected
     * @return
     */
    protected function updateState($type, $change) {
        // Change can be a change or an add
        if($type == "change") {
            for($i=0; $i < count($this->syncstate); $i++) {
                if($this->syncstate[$i]["id"] == $change["id"]) {
                    $this->syncstate[$i] = $change;
                    return;
                }
            }
            // Not found, add as new
            $this->syncstate[] = $change;
        } else {
            for($i=0; $i < count($this->syncstate); $i++) {
                // Search for the entry for this item
                if($this->syncstate[$i]["id"] == $change["id"]) {
                    if($type == "flags") {
                        // Update flags
                        $this->syncstate[$i]["flags"] = $change["flags"];
                    } else if($type == "delete") {
                        // Delete item
                        array_splice($this->syncstate, $i, 1);
                    }
                    return;
                }
            }
        }
    }

    /**
     * Returns TRUE if the given ID conflicts with the given operation. This is only true in the following situations:
     *   - Changed here and changed there
     *   - Changed here and deleted there
     *   - Deleted here and changed there
     * Any other combination of operations can be done (e.g. change flags & move or move & delete)
     *
     * @param string        $type of change
     * @param string        $folderid
     * @param string        $id
     *
     * @access protected
     * @return
     */
    protected function isConflict($type, $folderid, $id) {
        $stat = $this->backend->StatMessage($folderid, $id);

        if(!$stat) {
            // Message is gone
            if($type == "change")
                return true; // deleted here, but changed there
            else
                return false; // all other remote changes still result in a delete (no conflict)
        }

        foreach($this->syncstate as $state) {
            if($state["id"] == $id) {
                $oldstat = $state;
                break;
            }
        }

        if(!isset($oldstat)) {
            // New message, can never conflict
            return false;
        }

        if($stat["mod"] != $oldstat["mod"]) {
            // Changed here
            if($type == "delete" || $type == "change")
                return true; // changed here, but deleted there -> conflict, or changed here and changed there -> conflict
            else
                return false; // changed here, and other remote changes (move or flags)
        }
    }

}
