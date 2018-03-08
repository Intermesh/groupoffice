<?php

use go\core\App;
use go\core\cli\State;

require(__DIR__ . "/../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

App::get()->getDbConnection()->query("DROP DATABASE 63_test");
App::get()->getDbConnection()->query("create DATABASE 63_test");
App::get()->getDbConnection()->query("use 63_test");

App::get()->getInstaller()->install();