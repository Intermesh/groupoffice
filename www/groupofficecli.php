#!/usr/bin/env php
<?php
$root = dirname(__FILE__) . '/';
//chdir($root);
//on the command line you can pass -c=/path/to/config.php to set the config file.
require_once($root . 'go/base/util/Cli.php');

$args = \GO\Base\Util\Cli::parseArgs();

if (isset($args['c'])) {
	define("GO_CONFIG_FILE", $args['c']);
}



require_once($root.'vendor/autoload.php');

//Initialize new framework
use go\core\App;
use go\core\jmap\State;

App::get()->setAuthState(new State());

if(!empty($args['debug'])) {
	go()->getDebugger()->output = true;
	go()->getDebugger()->enable(false);
	GO::config()->debug = true;
}



//initialize autoloading of library
require_once($root.'vendor/autoload.php');
require_once($root . 'go/GO.php');
\GO::init();

if (!isset($args['q']))
	echo "\nGroup-Office CLI - Copyright Intermesh BV.\n\n";

if (PHP_SAPI != 'cli')
	exit("ERROR: This script must be run on the command line\n\n");

if (empty($args['r'])) {

	echo "ERROR: You must pass a controller route to use the command line script.\n" .
	"eg.:\n\n" .
	"sudo -u www-data php index.php -c=/path/to/config.php -r=maintenance/upgrade --param=value\n\n";
	exit();
} elseif (isset($args['u'])) {

	$password = isset($args['p']) ? $args['p'] : \GO\Base\Util\Cli::passwordPrompt("Enter password for user ".$args['u'].":");

	$user = \GO::session()->login($args['u'], $password);
	if (!$user) {
		echo "Login failed for user " . $args['u'] . "\n";
		exit(1);
	}
	unset($args['u']);
}

\GO::router()->runController($args);
