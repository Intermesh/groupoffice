<?php
/***********************************************
* File      :   bodypreference.php
* Project   :   Z-Push
* Descr     :   Holds body preference data
*
* Created   :   18.04.2012
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

class BodyPreference extends StateObject {
    protected $unsetdata = array(   'truncationsize' => false,
                                    'allornone' => false,
                                    'preview' => false,
                                );

    /**
     * expected magic getters and setters
     *
     * GetTruncationSize() + SetTruncationSize()
     * GetAllOrNone() + SetAllOrNone()
     * GetPreview() + SetPreview()
     */

    /**
     * Indicates if this object has values
     *
     * @access public
     * @return boolean
     */
    public function HasValues() {
        return (count($this->data) > 0);
    }
}
