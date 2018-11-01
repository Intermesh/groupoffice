<?php
/***********************************************
 * File      :   replybackstate.php
 * Project   :   Z-Push
 * Descr     :   Holds the state of the ReplyBackImExporter
 *               and also the ICS state to continue on later
 *
 * Created   :   25.04.2016
 *
 * Copyright 2016 Zarafa Deutschland GmbH
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

class ReplyBackState extends StateObject {
    protected $unsetdata = array(
            'replybackstate' => array(),
            'icsstate' => "",
    );

    /**
     * Returns a ReplyBackState from a state.
     *
     * @param mixed $state
     * @return ReplyBackState
     */
    static public function FromState($state) {
        if (strpos($state, 'ReplyBackState') !== false) {
            return unserialize($state);
        }
        else {
            $s = new ReplyBackState();
            $s->SetICSState($state);
            $s->SetReplyBackState(array());
            return $s;
        }
    }

    /**
     * Gets the state from a ReplyBackState object.
     *
     * @param mixed $state
     */
    static public function ToState($state) {
        $rbs = $state->GetReplyBackState();
        if (!empty($rbs)) {
            return serialize($state);
        }
        else {
            return $state->GetICSState();
        }
    }
}