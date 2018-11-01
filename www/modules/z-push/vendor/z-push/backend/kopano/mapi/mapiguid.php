<?php
/*
 * Copyright 2005 - 2016  Zarafa B.V. and its licensors
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
 */

define('IID_IStream',                           makeguid("{0000000c-0000-0000-c000-000000000046}"));
define('IID_IMAPITable',                        makeguid("{00020301-0000-0000-c000-000000000046}"));
define('IID_IMessage',                          makeguid("{00020307-0000-0000-c000-000000000046}"));
define('IID_IExchangeExportChanges',            makeguid("{a3ea9cc0-d1b2-11cd-80fc-00aa004bba0b}"));
define('IID_IExchangeImportContentsChanges',    makeguid("{f75abfa0-d0e0-11cd-80fc-00aa004bba0b}"));
define('IID_IExchangeImportHierarchyChanges',   makeguid("{85a66cf0-d0e0-11cd-80fc-00aa004bba0b}"));

define('PSETID_Appointment',                    makeguid("{00062002-0000-0000-C000-000000000046}"));
define('PSETID_Task',                           makeguid("{00062003-0000-0000-C000-000000000046}"));
define('PSETID_Address',                        makeguid("{00062004-0000-0000-C000-000000000046}"));
define('PSETID_Common',                         makeguid("{00062008-0000-0000-C000-000000000046}"));
define('PSETID_Log',                            makeguid("{0006200A-0000-0000-C000-000000000046}"));
define('PSETID_Note',                           makeguid("{0006200E-0000-0000-C000-000000000046}"));
define('PSETID_Meeting',                        makeguid("{6ED8DA90-450B-101B-98DA-00AA003F1305}"));
define('PSETID_Archive',                        makeguid("{72E98EBC-57D2-4AB5-B0AA-D50A7B531CB9}"));

define('PS_MAPI',                               makeguid("{00020328-0000-0000-C000-000000000046}"));
define('PS_PUBLIC_STRINGS',                     makeguid("{00020329-0000-0000-C000-000000000046}"));
define('PS_INTERNET_HEADERS',                   makeguid("{00020386-0000-0000-c000-000000000046}"));

define('MUIDECSAB',                             makeguid("{50A921AC-D340-48ee-B319-FBA753304425}"));

// Kopano Contact Provider GUIDs
define('MUIDZCSAB',                             makeguid("{30047F72-92E3-DA4F-B86A-E52A7FE46571}"));

// sk added for Z-Push
define ('PSETID_AirSync',                       makeguid("{71035549-0739-4DCB-9163-00F0580DBBDF}"));
