<?php
/**
 * Runs Token::oldLogin() in a new process, because a session can't be started after PHPUnit wrote output.
 *
 * Usage: php oldLoginSession.php <user id in the session or 0> <user id of the token>
 *
 * Prints JSON with the session ID before and after and the user in the session.
 */

use go\core\App;
use go\core\Environment;
use go\core\model\Token;
use go\core\util\ArrayObject;

ob_start();

$autoLoader = require(__DIR__ . '/../../../../../www/vendor/autoload.php');
$autoLoader->add('go\\', __DIR__ . '/../../../../');

$config = [];
require(__DIR__ . '/../../../../config.php');

App::get();
$c = new ArrayObject(go()->getConfig());
$c->mergeRecursive($config);
go()->setConfig($c->getArray());

/**
 * oldLogin() does nothing on the command line
 */
class OldLoginWebEnvironment extends Environment
{
	public function isCli(): bool
	{
		return false;
	}
}

$property = new ReflectionProperty(\go\core\Singleton::class, 'instances');
$instances = $property->getValue();
$instances[Environment::class] = (new ReflectionClass(OldLoginWebEnvironment::class))->newInstanceWithoutConstructor();
$property->setValue(null, $instances);

$savePath = sys_get_temp_dir() . '/go-oldlogin-sessions';
if (!is_dir($savePath)) {
	mkdir($savePath, 0700);
}
ini_set('session.save_path', $savePath);
ini_set('session.use_cookies', '0');
ini_set('session.cache_limiter', '');
session_start();

$sessionUserId = (int) $argv[1];
$_SESSION['GO_SESSION'] = $sessionUserId ? ['user_id' => $sessionUserId] : [];

$before = session_id();

$token = new Token();
$token->userId = (int) $argv[2];
$token->accessToken = 'oldlogintest';
$method = new ReflectionMethod(Token::class, 'oldLogin');
$method->invoke($token);

$result = [
	'before' => $before,
	'after' => session_id(),
	'userId' => $_SESSION['GO_SESSION']['user_id'] ?? null
];
session_destroy();

ob_end_clean();
echo json_encode($result);
