<?php
define("ZPUSH_VERSION", "2.7.1");
define("ZPUSH_DIR", dirname(__DIR__, 2) . "/go/modules/community/activesync/Z-Push/src/");
define('ZPUSH_CONFIG', __DIR__ . '/config.php');
require(ZPUSH_DIR . 'vendor/autoload.php');