<?php

namespace go\core\cli;

use Exception;
use go\core\exception\NotFound;
use go\core\jmap\exception\InvalidArguments;
use go\core\RouterInterface;
use ReflectionMethod;
use function str_split;

class Router implements RouterInterface {

	/**
	 * Parse command line arguments in named variables.
	 * 
	 * eg.
	 * php index.php -r=maintenenance/upgrade --someParam=value
	 * 
	 * will return array('r'=>'maintenance/upgrade','someParam'=>'value');
	 * 
	 * @return array 
	 */
	private function parseArgs() {
		global $argv;

		//array_shift($argv);
		$out = array();
		$count = count($argv);
		if ($count > 1) {
			for ($i = 1; $i < $count; $i++) {
				$arg = $argv[$i];
				if (substr($arg, 0, 2) == '--') {
					$eqPos = strpos($arg, '=');
					if ($eqPos === false) {
						$key = substr($arg, 2);
						$out[$key] = isset($out[$key]) ? $out[$key] : true;
					} else {
						$key = substr($arg, 2, $eqPos - 2);
						$out[$key] = substr($arg, $eqPos + 1);
					}
				} else if (substr($arg, 0, 1) == '-') {
					if (substr($arg, 2, 1) == '=') {
						$key = substr($arg, 1, 1);
						$out[$key] = substr($arg, 3);
					} else {
						$chars = str_split(substr($arg, 1));
						foreach ($chars as $char) {
							$key = $char;
							$out[$key] = isset($out[$key]) ? $out[$key] : true;
						}
					}
				} else {
					$out[] = $arg;
				}
			}
		}
		return $out;
	}

	public function run() {
		$args = $this->parseArgs();

		if (!isset($args[0])) {
			throw new Exception("Invalid arguments. Usage: cli.php package/modulename/controller/method --arg1=foo");
		}
		
		$path = array_shift($args);
		$parts = explode('/', $path);

		$controllerCls = 'go\\modules\\' . $parts[0] . '\\' . $parts[1] . '\\controller\\' . $parts[2];

		if (!class_exists($controllerCls)) {
			throw new NotFound("Route: " . $path . "  not found.");
		}	

		$ctrl = new $controllerCls;
		
		if(!($ctrl instanceof Controller)) {
			throw new Exception($controllerCls . " is not a go\core\cli\Controller class.");
		}
		

		if (!method_exists($ctrl, $parts[3])) {
			throw new Exception("Method '" .  $parts[3] . "' doesn't exist in controller '" . $controllerCls . "'");
		}
		
		$this->callMethod($ctrl, $parts[3], $args);
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
