<?php
define("ZPUSH_VERSION", "2.4.2");
define("ZPUSH_DIR", __DIR__ . "/vendor/z-push/");

require(ZPUSH_DIR . 'vendor/autoload.php');
require("backend/go/config.php");
require("backend/go/goSyncUtils.php");
require("backend/go/goImporter.php");
require("backend/go/goExporter.php");

require('backend/go/goBaseBackendDiff.php');
require('backend/go/GoImapStreamWrapper.php');
require('backend/go/goCalendar.php');
require('backend/go/goContact.php');
require('backend/go/goMail.php');
require('backend/go/goNote.php');
require('backend/go/goTask.php');

require('backend/go/go.php');


define('ZPUSH_CONFIG', __DIR__ . '/config.php');


require(ZPUSH_DIR . "index.php");
