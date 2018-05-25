<?php
/***********************************************
* File      :   sqlstatemachine/config.php
* Project   :   Z-Push
* Descr     :   configuration file for the
*               SqlStateMachine backend.
*
* Created   :   19.01.2016
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

/**
 * More information about the configuration on https://wiki.z-hub.io/x/xIAa
 *
 * STATE_SQL_ENGINE:    the DB engine
 * STATE_SQL_SERVER:    the DB server URI or IP
 * STATE_SQL_PORT:      the DB server port
 * STATE_SQL_DATABASE:  the DB name
 * STATE_SQL_USER:      username to DB
 * STATE_SQL_PASSWORD:  STATE_SQL_USER's password to DB
 * STATE_SQL_OPTIONS:   array with options needed
 */
define('STATE_SQL_ENGINE', 'mysql');
define('STATE_SQL_SERVER', 'localhost');
define('STATE_SQL_PORT', '3306');
define('STATE_SQL_DATABASE', 'zpush');
define('STATE_SQL_USER', 'root');
define('STATE_SQL_PASSWORD', '');
define('STATE_SQL_OPTIONS', serialize(array(PDO::ATTR_PERSISTENT => true)));

