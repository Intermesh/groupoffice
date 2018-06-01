<?php
/***********************************************
* File      :   replacenullcharfilter.php
* Project   :   Z-Push
* Descr     :   Filters null characters out of a stream.
*
* Created   :   11.09.2015
*
* Copyright 2015 - 2016 Zarafa Deutschland GmbH
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

class ReplaceNullcharFilter extends php_user_filter {

    /**
     * This method is called whenever data is read from or written to the attached stream.
     *
     * @see php_user_filter::filter()
     *
     * @param resource      $in
     * @param resource      $out
     * @param int           $consumed
     * @param boolean       $closing
     *
     * @access public
     * @return int
     *
     */
    function filter($in, $out, &$consumed, $closing) {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = str_replace("\0", "", $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }
        return PSFS_PASS_ON;
    }
}
