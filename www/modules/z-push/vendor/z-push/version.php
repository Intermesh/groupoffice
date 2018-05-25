<?php
/***********************************************
* File      :   version.php
* Project   :   Z-Push
* Descr     :   version number
*
* Created   :   18.04.2008
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

if (!defined("ZPUSH_VERSION")) {
    $path = escapeshellarg(dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
    $branch = trim(exec("hash git 2>/dev/null && cd $path >/dev/null 2>&1 && git branch --no-color 2>/dev/null | sed -e '/^[^*]/d' -e \"s/* \(.*\)/\\1/\""));
    $version = exec("hash git 2>/dev/null && cd $path >/dev/null 2>&1 && git describe  --always 2>/dev/null");
    if ($branch && $version) {
        define("ZPUSH_VERSION", $branch .'-'. $version);
    }
    else {
        define("ZPUSH_VERSION", "GIT");
    }
}