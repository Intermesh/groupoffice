<?php

namespace go\core\cli;

use Exception;
use go\core\Controller;
use go\core\exception\NotFound;
use go\core\jmap\exception\InvalidArguments;
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
	public static function parseArgs() {
		
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
							self::$args[$key] = isset(self::$args[$key]) ? self::$args[$key] : true;
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
								self::$args[$key] = isset(self::$args[$key]) ? self::$args[$key] : true;
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

	public function run() {
		$args = $this->parseArgs();

		if (!isset($args[0])) {
			throw new Exception("Invalid arguments. Usage: cli.php package/modulename/controller/method --arg1=foo");
		}
		
		$path = array_shift($args);
		$parts = explode('/', $path);

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

	private function getMethodParams($controller, $methodName) {	

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
	 * @param string $methodName
	 * @params array $routerParams A merge of route and query params
	 */
	private function callMethod(Controller $controller, $methodName, array $requestParams) {

		//call method with all parameters from the $_REQUEST object.
		$methodArgs = [];
		foreach ($this->getMethodParams($controller, $methodName) as $paramName => $paramMeta) {
			if (!isset($requestParams[$paramName]) && !$paramMeta['isOptional']) {
				throw new InvalidArguments("Bad request. Missing argument '" . $paramName . "' for action method '" . get_class($controller) . "->" . $methodName . "'");
			}

			$methodArgs[] = isset($requestParams[$paramName]) ? $requestParams[$paramName] : $paramMeta['default'];
		}

		call_user_func_array([$controller, $methodName], $methodArgs);
	}

}
