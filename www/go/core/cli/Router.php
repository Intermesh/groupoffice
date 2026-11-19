<?php

namespace go\core\cli;

use Exception;
use go\core\Controller;
use go\core\exception\NotFound;
use go\core\jmap\exception\InvalidArguments;
use ReflectionException;
use ReflectionMethod;
use function str_split;

/**
 * CLI Router
 * 
 * You can run a CLI controller method like this:
 * 
 * ```
 * php cli.php package/modulename/controller/method --arg1=foo
 * ```
 * 
 * Or with Docker Compose:
 * 
 * ```
 * docker compose exec --user www-data groupoffice php cli.php community/addressbook/migrate/run
 * ```
 * 
 * Core controllers can be accessed with core/ControllerName
 */
class Router {
	
	private static $args;

	/**
	 * Parse command line arguments in named variables.
	 * 
	 * eg.
	 * php index.php -r=maintenenance/upgrade --someParam=value -c=/path/to/config.php
	 * 
	 * will return array('r'=>'maintenance/upgrade','someParam'=>'value');
	 * 
	 * @return array 
	 */
	public static function parseArgs(): array
	{
		if(!isset(self::$args)) {
			global $argv;

			self::$args = self::parse(array_slice($argv ?? [], 1));
		}

		return self::$args;
	}

	/**
	 * Parse a list of command line arguments into named variables.
	 *
	 * Options take a value after "=" or a space: `--name=value`, `--name value`, `-c=value` or `-c value`.
	 * When the next argument starts with "-", the option is a flag and gets the value true. So a negative
	 * number must be given with "=": `--offset=-1`. Combined short flags like `-qv` never take a value.
	 *
	 * Arguments that aren't options are added with numeric keys. The first one is the route.
	 *
	 * @param string[] $argv The arguments without the script name
	 * @return array
	 */
	public static function parse(array $argv): array
	{
		$args = [];
		$count = count($argv);
		for ($i = 0; $i < $count; $i++) {
			$arg = $argv[$i];
			$nextIsValue = isset($argv[$i + 1]) && substr($argv[$i + 1], 0, 1) != '-';
			if (substr($arg, 0, 2) == '--') {
				$eqPos = strpos($arg, '=');
				if ($eqPos === false) {
					$key = substr($arg, 2);
					$args[$key] = $nextIsValue ? $argv[++$i] : ($args[$key] ?? true);
				} else {
					$key = substr($arg, 2, $eqPos - 2);
					$args[$key] = substr($arg, $eqPos + 1);
				}
			} else if (substr($arg, 0, 1) == '-') {
				if (substr($arg, 2, 1) == '=') {
					$key = substr($arg, 1, 1);
					$args[$key] = substr($arg, 3);
				} else if (strlen($arg) == 2 && $nextIsValue) {
					$args[substr($arg, 1)] = $argv[++$i];
				} else {
					$chars = str_split(substr($arg, 1));
					foreach ($chars as $char) {
						$args[$char] = $args[$char] ?? true;
					}
				}
			} else {
				$args[] = $arg;
			}
		}

		return $args;
	}

	/**
	 * @throws InvalidArguments
	 * @throws NotFound
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function run() {
		$args = $this->parseArgs();

		if (!isset($args[0])) {
			throw new InvalidArguments("Invalid arguments. Usage: cli.php package/modulename/controller/method --arg1=foo");
		}
		
		$path = array_shift($args);

		go()->getDebugger()->setRequestId('cli: ' . $path);

		$parts = explode('/', $path);

		if(!isset($parts[2]) || $parts[0] != 'core' && !isset($parts[3])) {
			throw new InvalidArguments("the path parameter must have 3 components if starts with core/ or 4 components.");
		}

		if($parts[0] == 'core') {
			$controllerCls = 'go\\core\\cli\\controller\\' . $parts[1];
			$method = $parts[2];
		} else{

			$controllerCls = 'go\\modules\\' . $parts[0] . '\\' . $parts[1] . '\\cli\\controller\\' . $parts[2];
			$method = $parts[3];
		}

		if (!class_exists($controllerCls)) {
			throw new NotFound("Route: " . $path . " (Class ".$controllerCls.")  not found.");
		}	

		$ctrl = new $controllerCls;
		
		if (!method_exists($ctrl, $method)) {
			throw new NotFound("Route: " . $path . " (Method ".$controllerCls."::".$method.")  not found.");
		}
		
		$this->callMethod($ctrl, $method, $args);
	}


	/**
	 * Runs controller method with URL query and route params.
	 *
	 * For an explanation about route params {@see Router::routeParams}
	 *
	 * @param Controller $controller
	 * @param string $methodName
	 * @param array $requestParams A merge of route and query params
	 * @throws InvalidArguments
	 * @throws ReflectionException
	 */
	private function callMethod(Controller $controller, string $methodName, array $requestParams) {

		call_user_func([$controller, $methodName], $requestParams);
	}

}
