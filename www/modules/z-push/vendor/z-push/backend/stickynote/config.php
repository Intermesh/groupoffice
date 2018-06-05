<?php
/***********************************************
* File      :   config.php
* Project   :   Z-Push
* Descr     :   Stickynote backend configuration file
*
* Created   :   8/29/2017
*
* Copyright 2017 Karl Denninger
*
* Karl Denninger releases this code under AGPLv3.
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

// ************************
// BackendStickyNote settings
// NOTE that StickyNote does NOT perform any actual login verification.
//
// YOU ARE WARNED THAT YOU MUST HAVE AT LEAST ONE OTHER BACK END DEFINED THAT
// DOES ACTUALLY CHECK PASSWORDS, OR YOU HAVE ***ZERO*** SECURITY ON THIS
// BACKEND!  To enforce your reading this notice (and hopefully paying 
// attention to it, the backend will NOT run unless you comment out the LAST
// parameter in this list.
//
// You must ALSO read and follow the REQUIREMENTS file to set up 
// the roles and database schema required.  Do that BEFORE configuring 
// the below parameters (yes, they must match!)
//
// ************************

// The Postgresql server (IP number or name)
define('STICKYNOTE_SERVER', 'localhost');

// Postgresql server port (5432 is Postgres default)
define('STICKYNOTE_PORT', '5432');

// The database on the server
define('STICKYNOTE_DATABASE', 'stickynote');

// The username to use for the role
define('STICKYNOTE_USER', 'stickynote');

// The password to use for the role, if any
define('STICKYNOTE_PASSWORD', 'stickynote');

// If defined then a delete REALLY DELETES; if not it marks the item deleted
// in the database but DOES NOT physically remove it.
//define('STICKYNOTE_REALLYDELETE', 'true');

// You MUST comment this out or the code will not run
define('STICKYNOTE_MUSTNOTBESET', 'true');

