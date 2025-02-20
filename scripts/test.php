<?php
require (dirname(__DIR__) . '/www/GO.php');

$version = phpversion("sourceGuardian");

$val = version_compare($version, '16.0.0', '>=');

var_dump($val);