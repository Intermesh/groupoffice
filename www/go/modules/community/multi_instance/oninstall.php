<?php
//TODO this should be a command line controller instead.

use go\core\App;
use go\core\cli\State;
use go\modules\community\multi_instance\model\Instance;

if(!empty($argv[1])) {
	define('GO_CONFIG_FILE', $argv[1]);
}
chdir(__DIR__);
require("../../../../vendor/autoload.php");

//Create the app with the database connection
App::get()->setAuthState(new State());

if(!\go\core\Environment::get()->isCli()) {
	
	return;
}

$instanceName = $argv[2];

$instance = Instance::find()->where('hostname','=',$instanceName)->single();
$instance->onInstall();