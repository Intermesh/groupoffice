<?php

namespace go\core\jmap;

use Exception as CoreException;
use go\core\db\DbException;
use go\core\ErrorHandler;
use go\core\exception\JsonPointerException;
use go\core\fs\File;
use go\core\http\Exception;
use go\core\jmap\exception\CannotCalculateChanges;
use go\core\jmap\exception\InvalidResultReference;
use go\core\orm\EntityType;
use go\core\util\ArrayObject;
use go\core\util\JSON;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use ReflectionClass;
use Throwable;

/**
 * JMAP compatible router
 * 
 * If an entity is registered in {@see EntityType} then you can make calls like:
 * 
 * Note/get
 * 
 * Otherwise you can use the long method:
 * 
 * community/notes/Note/get
 * 
 * core/Notify/mail
 * 
 * http://jmap.io/spec-core.html#making-an-api-request
 */
class Router {
//	public function error($type, $status, $detail) {
//		$r = http\Response::get();
//		$r->setStatus($status, $detail);
//		$r->sendHeaders();
//		$r->output([
//			"type" => $type,
//			"status" => $status,
//			"detail" => $detail
//		]);
//		exit();
//	}
	/**
	 * @var File
	 */
	private $logFile;

	/**
	 * Run the router
	 *
	 * Takes the request and loops through each method call. For each method a
	 * controller method is looked up.
	 *
	 * community/notes/Note/get maps to go\modules\community\notes\controller\Note::get()
	 * @throws JsonException
	 * @throws Exception
	 */
	public function run(?array $requests = null, bool $output = true) {

		if(!isset($requests)) {
			$requests = Request::get()->getBody();
		}

		while($method = array_shift($requests)) {
			$this->callMethod($method);
		}

		if($output) {
			Response::get()->output();
		}
	}

	/**
	 * Set file to log all requests to
	 *
	 * @param $filename
	 * @return void
	 */
	public function setLogFile(string $filename) {
		$this->logFile = new File($filename);
	}

	/**
	 * Calls the controller method
	 *
	 * @throws Exception
	 */
	private function callMethod(array $body) {
		if (count($body) != 3) {
			throw new Exception(400, 'Bad request. Supply 3 arguments, method, params and call id');
		}

		list($method, $params, $clientCallId) = $body;

		Response::get()->setClientCall($method, $clientCallId);

		go()->getDebugger()->setRequestId("JMAP " . $method);
		
		if($method != "community/dev/Debugger/get") {
			//App::get()->debug("Processing method " . $method . ", call ID: " . $clientCallId);
			go()->getDebugger()->group($method .',  ID: '. $clientCallId);
			go()->getDebugger()->debug("request:",0, false);
			go()->getDebugger()->debug($params, 0, false);
		}
		
		try {
			$response = $this->callAction($method, $params);
			
			if(isset($response)) {
				Response::get()->addResponse($response);
			}
			
		} catch (Throwable $e) {

			if(!($e instanceof CannotCalculateChanges)) {
				$debugMessage = ErrorHandler::logException($e);
			}

			$msg = $e->getMessage();

			if($e instanceof Exception) {
				switch($e->getCode()) {
					case 401:
						$type = "unauthorized";
						break;
					case 403:
						$type = "forbidden";
						break;

					default:
						$type = "serverFail";
				}
			} else {
				$type = lcfirst((new ReflectionClass($e))->getShortName());
				if($e instanceof DbException) {
					$msg = "Database exception. Check server logs for details.";
				}
			}
			// convert jmap classes to set error response
			// https://jmap.io/spec-core.html#errors
			$error = [
				"message" => $msg,
				"type" => $type
			];
			
			if(isset($debugMessage) && go()->getDebugger()->enabled) {
				//only in debug mode, may contain sensitive information
				$error["debugMessage"] = $debugMessage;
				$previous = $e->getPrevious();
				if($previous) {
					$error['previous'] = $previous->getMessage();
				}
				$error["trace"] = explode("\n", $e->getTraceAsString());
			}
			
			Response::get()->addError($error);
		} finally {
			if($method != "community/dev/Debugger/get") {
				go()->getDebugger()->groupEnd();
			}
		}
	}

	/**
	 * @throws Exception
	 * @throws CoreException
	 */
	private function findControllerAction(string $method): array
	{
		$parts = explode('/', $method);

		if (count($parts) == 2) {
			//eg. "note/query"
			$entityType = EntityType::findByName($parts[0]);
			if (!$entityType) {
				throw new Exception(400, 'Bad request. Entity type "' . $parts[0] . '"  not found');
			}

			//Very ugly hack
			if($entityType->getName() == "Project") {
				// JH Added to the ugly hack. Need a bit of JMAP for the old projects as well
				if(go()->getModule('business', 'projects')) {
					$controllerClass = "go\\modules\\business\\projects\\controller\\Project";
				} else {
					$controllerClass = 'GO\\Projects2\\Controller\\ProjectEntityController';
				}
			} else {
				$controllerClass = str_ireplace("model", "controller", $entityType->getClassName());
			}
			$controllerMethod = $parts[1];
		} else if($parts[0] == "core") {
			$controllerMethod = array_pop($parts);
			array_splice($parts, -1, 0, 'controller');

			$controllerClass = 'go\\' . implode('\\', $parts);
			
		} else {
			// With namespace: community/notes/Note/query
			$controllerMethod = array_pop($parts);
			array_splice($parts, -1, 0, 'controller');

			$controllerClass = 'go\\modules\\' . implode('\\', $parts);
		}

		if (!class_exists($controllerClass)) {
			throw new Exception(400, 'Bad request. Controller "' . $controllerClass . '"  not found');
		}

		if (!method_exists($controllerClass, $controllerMethod)) {
			throw new Exception(400, 'Bad request. Method "' . $controllerMethod . '" not found in controller "' . $controllerClass . '".');
		}

		return [$controllerClass, $controllerMethod];
	}


	/**
	 * Runs controller method
	 *
	 * community/notes/Note/get maps to go\modules\community\notes\controller\Note::get()
	 *
	 * @param string $method
	 * @param ?array $params
	 * @return JsonSerializable | array | ArrayObject
	 * @throws Exception|InvalidResultReference
	 * @params array $routerParams A merge of route and query params
	 */
	protected function callAction(string $method, ?array $params)
	{
		// Special testing method that echoes the params
		if($method == "Core/echo") {
			return $params;
		}
		
		$this->logAction($method, $params);

		$controllerMethod = $this->findControllerAction($method);
		$controller = new $controllerMethod[0];

		if(empty($params)) {
			$params = [];
		} else if(!is_array($params)) {
			throw new InvalidArgumentException("params argument should be an object with {key: value}");
		}

		$params = $this->resolveResultReferences($params);

		return call_user_func([$controller, $controllerMethod[1]], $params);
	}

	/**
	 * Resolves JMAP result references for parameters
	 *
	 * It also recurses into the "filter" parameters to resolve. This is not according to the JMAP spec but very handy :)
	 *
	 * @link https://jmap.io/spec-core.html#references-to-previous-method-results
	 * @param array $params
	 * @return array
	 * @throws InvalidResultReference
	 */
	private function resolveResultReferences(array $params) : array {
		foreach ($params as $name => $possibleResultReference) {
			if (substr($name, 0, 1) == '#') {
				$params[substr($name, 1)] = $this->resolveResultReference($possibleResultReference);
				unset($params[$name]);
			} else if(is_array($possibleResultReference)) {
				$params[$name] = $this->resolveResultReferences($possibleResultReference);
			}
		}

		return $params;
	}

	/**
	 * @throws InvalidResultReference
	 */
	private function resolveResultReference($resultReference) {
		$result = $this->findResultOf($resultReference['resultOf']);

		try {
			return JSON::get($result[1], $resultReference['path']);
		}catch(JsonPointerException $e) {
			throw new InvalidResultReference($e->getMessage());
		}
	}

	/**
	 * @throws InvalidResultReference
	 */
	private function findResultOf($resultOf) {
		$results = Response::get()->getData();
		
		foreach ($results as $result) {
			if ($resultOf == $result[2]) {
				
				if(!empty($result['error'])){
					throw new InvalidResultReference("The method you are referring to returned an error. (". $resultOf .")");
				}
				
				return $result;
			}
		}

		throw new InvalidResultReference("Client call id ".$resultOf." does not exist.");
	}


	private function logAction(string $method)
	{
		if(!isset($this->logFile)) {
			return;
		}

		$line = '[' . date('Y-m-d H:i:s') . ']['.(go()->getUserId() ?? "-").']['.(Request::get()->getRemoteIpAddress() ?? "-").'] '.$method;
		$this->logFile->putContents($line . "\n", FILE_APPEND);
	}

}
