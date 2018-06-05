<?php
/***********************************************
* File      :   ipcmemcached/config.php
* Project   :   Z-Push
* Descr     :   Configuration file for the
*               memcache IPC provider.
*
* Created   :   02.05.2016
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

// Comma separated list of available memcache servers.
// Servers can be added as 'hostname:port,otherhost:port'
define('MEMCACHED_SERVERS','localhost:11211');

// Memcached down indicator
// In case memcached is not available, a lock file will be written to disk
define('MEMCACHED_DOWN_LOCK_FILE', '/tmp/z-push-memcache-down');
// indicates how long the lock file will be maintained (in seconds)
define('MEMCACHED_DOWN_LOCK_EXPIRATION', 30);

// Prefix to used for keys
define('MEMCACHED_PREFIX', 'z-push-ipc');

// Connection timeout in ms
define('MEMCACHED_TIMEOUT', 100);

// Mutex timeout (in seconds)
define('MEMCACHED_MUTEX_TIMEOUT', 5);

// Waiting time before re-trying to aquire mutex (in ms), must be higher than 0
define('MEMCACHED_BLOCK_WAIT', 10);
