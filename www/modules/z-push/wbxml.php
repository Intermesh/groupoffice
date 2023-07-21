#!/usr/bin/env php
<?php
/***********************************************
 * File      :   z-push-top.php
 * Project   :   Z-Push
 * Descr     :   Shows realtime information about
 *               connected devices and active
 *               connections in a top-style format.
 *
 * Created   :   07.09.2011
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

define("ZPUSH_VERSION", "2.6.1");
define("ZPUSH_DIR", __DIR__ . "/vendor/z-push/");

require(ZPUSH_DIR . 'vendor/autoload.php');
require("backend/go/autoload.php");

define('ZPUSH_CONFIG', __DIR__ . '/config.php');

include_once(ZPUSH_CONFIG);

ZPush::CheckConfig();
Request::Initialize();
ZLog::Initialize();


/**
 *
 * WBXML-OUT: AwFqAAAZRUoDMQABUQAURwAZSgMxAAFSAABQA0VtYWlsAAFNAzI1MjUAAVIDbS9JTkJPWAABABlTAAJUA01lcmlqbiwgcGxlYXNlIGFkZCBtZSB0byB5b3VyIExpbmtlZEluIG5ldHdvcmsAAU8DMjAyMy0wNi0xMlQyMTo1NjoyNC4wMDBaAAFRA01lcmlqbiBTY2hlcmluZwABUgMxAAFVAzEAAQAWVQMwAAEAGVQDdGVzdCAxMjMgcHJldmlldy4uLgABVQMwAAEAAlgDIll1bGl5YSBCYWtobmlldmEgKE1pa2l0ZW5rbykiIDxpbnZpdGF0aW9uc0BsaW5rZWRpbi5jb20+AAEBAQAZTQMwLTEAAVYDMQABAQEB
 WBXML-IN : AwFqAAAZRUYDQThBMEZBNUItRDE2Mi00NUEyLTlGQUEtM0UxODg5MDUyMTgzAAFHSEkAAFADRW1haWwAAVIDbS9JTkJPWAABABlLA3RvOiJUZXN0IiBPUiBjYzoiVGVzdCIgT1IgZnJvbToiVGVzdCIgT1Igc3ViamVjdDoiVGVzdCIgT1IgIlRlc3QiAAEBTE0DMC05OQABDgEBAQE=

 *
 */


$wbxml = "AwFqAAAZRUoDMQABUQAURwAZSgMxAAFSAABQA0VtYWlsAAFNAzI1MjUAAVIDbS9JTkJPWAABABlTAAJUA01lcmlqbiwgcGxlYXNlIGFkZCBtZSB0byB5b3VyIExpbmtlZEluIG5ldHdvcmsAAU8DMjAyMy0wNi0xMlQyMTo1NjoyNC4wMDBaAAFRA01lcmlqbiBTY2hlcmluZwABUgMxAAFVAzEAAQAWVQMwAAEAGVQDdGVzdCAxMjMgcHJldmlldy4uLgABVQMwAAEAAlgDIll1bGl5YSBCYWtobmlldmEgKE1pa2l0ZW5rbykiIDxpbnZpdGF0aW9uc0BsaW5rZWRpbi5jb20+AAEBAQAZTQMwLTEAAVYDMQABAQEB";
$decoder = new WBXMLDecoder(StringStreamWrapper::Open(base64_decode($wbxml)));
while($el = $decoder->getElement()) {

	var_dump($el);
}