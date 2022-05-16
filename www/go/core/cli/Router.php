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
 * docker-compose exec --user www-data groupoffice php cli.php community/addressbook/migrate/run
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

			//array_shift($argv);
			self::$args = array();
			$count = count($argv);
			if ($count > 1) {
				for ($i = 1; $i < $count; $i++) {
					$arg = $argv[$i];
					if (substr($arg, 0, 2) == '--') {
						$eqPos = strpos($arg, '=');
						if ($eqPos === false) {
							$key = substr($arg, 2);
							self::$args[$key] = self::$args[$key] ?? true;
						} else {
							$key = substr($arg, 2, $eqPos - 2);
							self::$args[$key] = substr($arg, $eqPos + 1);
						}
					} else if (substr($arg, 0, 1) == '-') {
						if (substr($arg, 2, 1) == '=') {
							$key = substr($arg, 1, 1);
							self::$args[$key] = substr($arg, 3);
						} else {
							$chars = str_split(substr($arg, 1));
							foreach ($chars as $char) {
								$key = $char;
								self::$args[$key] = self::$args[$key] ?? true;
							}
						}
					} else {
						self::$args[] = $arg;
					}
				}
			}
		}
		
		return self::$args;
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
	 * @throws ReflectionException
	 */
	private function getMethodParams($controller, $methodName): array
	{

		$method = new ReflectionMethod($controller, $methodName);
		$rParams = $method->getParameters();

		$methodArgs = [];

		foreach ($rParams as $param) {
			$arg = ['isOptional' => $param->isOptional(), 'default' => $param->isOptional() ? $param->getDefaultValue() : null];
			$methodArgs[$param->getName()] = $arg;
		}

		return $methodArgs;
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

		//call method with all parameters from the $_REQUEST object.
		$methodArgs = [];

		$paramNames = [];

		foreach ($this->getMethodParams($controller, $methodName) as $paramName => $paramMeta) {
			if (!isset($requestParams[$paramName]) && !$paramMeta['isOptional']) {
				throw new InvalidArguments("Bad request. Missing argument '" . $paramName . "' for action method '" . get_class($controller) . "->" . $methodName . "'");
			}

			$methodArgs[] = $requestParams[$paramName] ?? $paramMeta['default'];
			unset($requestParams[$paramName]);

			$paramNames[] = $paramName . ($paramMeta['isOptional'] ? ' = ' . var_export($paramMeta['default'], true) : "*" );
		}

		unset($requestParams['c']);
		unset($requestParams['debug']);

		if(!empty($requestParams)) {
			throw new InvalidArguments("Bad request. The following parameters are not supported: " . implode(",", array_keys($requestParams))."\n\nSupported are: \n- " . implode("\n- ",  $paramNames));
		}

		call_user_func_array([$controller, $methodName], $methodArgs);
	}

}
