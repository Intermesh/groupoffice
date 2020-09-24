<?php
define("ZPUSH_VERSION", "2.5.2");
define("ZPUSH_DIR", __DIR__ . "/vendor/z-push/");

require(ZPUSH_DIR . 'vendor/autoload.php');
require("backend/go/autoload.php");

define('ZPUSH_CONFIG', __DIR__ . '/config.php');

require(ZPUSH_DIR . "index.php");
