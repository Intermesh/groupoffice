<?php
require(__DIR__ . "/vendor/autoload.php");

use go\core\App;
use go\core\fs\Blob;

App::get();

GO()->setAuthState(new \go\core\cli\State());


$ctrl = new \go\modules\community\dev\controller\Language();

$ctrl->import(["path" => "lang.csv"]);