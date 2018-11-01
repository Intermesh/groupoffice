<?php
/***********************************************
* File      :   config.php
* Project   :   Z-Push
* Descr     :   Kopano backend configuration file
*
* Created   :   27.11.2012
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

// ************************
//  BackendKopano settings
// ************************

// Defines the server to which we want to connect.
//
// Depending on your setup, it might be advisable to change the lines below to one defined with your
// default socket location.
// Normally "default:" points to the default setting ("file:///var/run/kopano/server.sock")
// Examples: define("MAPI_SERVER", "default:");
//           define("MAPI_SERVER", "http://localhost:236/kopano");
//           define("MAPI_SERVER", "https://localhost:237/kopano");
//           define("MAPI_SERVER", "file:///var/run/kopano/server.sock");
// If you are using ZCP >= 7.2.0, set it to the zarafa location, e.g.
//           define("MAPI_SERVER", "http://localhost:236/zarafa");
//           define("MAPI_SERVER", "https://localhost:237/zarafa");
//           define("MAPI_SERVER", "file:///var/run/zarafad/server.sock");
// For ZCP versions prior to 7.2.0 the socket location is different (http(s) sockets are the same):
//           define("MAPI_SERVER", "file:///var/run/zarafa");

define('MAPI_SERVER', 'default:');

// Read-Only shared folders
//   When trying to write a change on a read-only folder this data is dropped and replaced on the device of the user.
//   Enabling the option below, sends an email to the user notifying that this happened (default enabled).
//   If this is disabled, the data will be dropped silently and will be lost.
//   The template of the email sent can be customized here. The placeholders can also be used in the subject.
define('READ_ONLY_NOTIFY_LOST_DATA', true);
// String to mark the data changed by the user (that he is trying to save)
define('READ_ONLY_NOTIFY_YOURDATA', 'Your data');
// Email template to be sent to the user
define('READ_ONLY_NOTIFY_SUBJECT', "Z-Push: Writing operation not permitted - data reset");
define('READ_ONLY_NOTIFY_BODY', <<<END
Dear **USERFULLNAME**,

on **DATE** at **TIME** you've tried to save a data in the folder '**FOLDERNAME**' on your device '**MOBILETYPE**' ID: '**MOBILEDEVICEID**'.

This operation was not successful, as you lack write access to this folder.
Your data has been dropped and replaced with the original data on your device to ensure data integrity.

Below is a copy of the data you tried to save. If you want your changes to be stored permanently you should forward this email to a person with write access to this folder asking to perform these changes again.
**DIFFERENCES**

If you have questions about this email, please contact your e-mail administrator.

Sincerely,
Your Z-Push system
END
         );
// Format of the **DATE** and **TIME** placeholders - more information on formats, see http://php.net/manual/en/function.strftime.php
define('READ_ONLY_NOTIFY_DATE_FORMAT', "%d.%m.%Y");
define('READ_ONLY_NOTIFY_TIME_FORMAT', "%H:%M:%S");
