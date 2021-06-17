<?php

namespace go\core\jmap;

use Exception as CoreException;
use GO;
use GO\Base\Util\Number;
use go\core\App;
use go\core\ErrorHandler;
use go\core\http\Exception;
use go\core\jmap\exception\InvalidResultReference;
use go\core\orm\EntityType;
use JsonSerializable;
use go\core\http;

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

	private $clientCallId;

	public function getClientCallId() {
		return $this->clientCallId;
	}

	public function error($type, $status, $detail) {	
		$r = http\Response::get();
		$r->setStatus($status, $detail);
		$r->sendHeaders();
		$r->output([
			"type" => $type,
			"status" => $status,
			"detail" => $detail
		]);
	}

	/**
	 * Run the router
	 * 
	 * Takes the request and loops through each method call. For each method a 
	 * controller method is looked up.
	 * 
	 * community/notes/Note/get maps to go\modules\community\notes\controller\Note::get()
	 */
	public function run() {	

		$body = Request::get()->getBody();
		
		if(!is_array($body)) {
			return $this->error("urn:ietf:params:jmap:error:notRequest", 400, "The request parsed as JSON but did not match the type signature of the Request object.");
			throw new Exception(400, 'Bad request');
		}

		while($method = array_shift($body)) {
			$this->callMethod($method);
		}

		Response::get()->sendHeaders();
		Response::get()->output();
	}

	private function callMethod(array $body) {
		if (count($body) != 3) {
			throw new Exception(400, 'Bad request');
		}

		list($method, $params, $clientCallId) = $body;

		Response::get()->setClientCall($method, $clientCallId);
		
		if($method != "community/dev/Debugger/get") {
			//App::get()->debug("Processing method " . $method . ", call ID: " . $clientCallId);
			go()->getDebugger()->group($method .',  ID: '. $clientCallId );				
			go()->getDebugger()->debug("request:");
			go()->getDebugger()->debug($params);			
		}
		
		try {
			$response = $this->callAction($method, $params);
			
			if(isset($response)) {
				Response::get()->addResponse($response);
			}
			
		} catch(InvalidResultReference $e) {
			$error = ["message" => $e->getMessage()];

			if(go()->getDebugger()->enabled) {
				//only in debug mode, may contain sensitive information
				$error["debugMessage"] = ErrorHandler::logException($e);
				$error["trace"] = explode("\n", $e->getTraceAsString());
			}
		
			Response::get()->addResponse([
					'error', $error
			]);
		} catch (\Throwable $e) {
			$error = ["message" => $e->getMessage()];
			
			if(go()->getDebugger()->enabled) {
				//only in debug mode, may contain sensitive information
				$error["debugMessage"] = ErrorHandler::logException($e);
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

	private function findControllerAction($method) {
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

//	/**
//	 * JMAP doesn't support passing multiple account ID's. We need this in Group-Office
//	 * so we offer this feature with the accountIds parameter. To be backwards compatible we translate "accountId" to the "accountIds" array.
//	 * @param type $params
//	 * @return type
//	 */
//	private function normalizeAccountId($params) {
//		if(isset($params->accountId)) {
//			$params->accountIds = [$params->accountId];
//		}
//		
//		return $params;
//	}

	/**
	 * Runs controller method 
	 * 
	 * community/notes/Note/get maps to go\modules\community\notes\controller\Note::get()
	 * 
	 * @param string $methodName
	 * @params array $routerParams A merge of route and query params
	 * @throws Exception
	 * @return JsonSerializable
	 */
	protected function callAction($method, $params) {

		// Special testing method that echoes the params
		if($method == "Core/echo") {
			return $params;
		}

		$controllerMethod = $this->findControllerAction($method);
		$controller = new $controllerMethod[0];

		$params = $this->resolveResultReferences($params);

		return call_user_func([$controller, $controllerMethod[1]], $params);
	}

	/**
	 * @link http://jmap.io/spec-core.html#references-to-previous-method-results
	 * @param type $params
	 */
	private function resolveResultReferences($params) {
		foreach ($params as $name => $resultReference) {
			if (substr($name, 0, 1) == '#') {
				$result = $this->findResultOf($resultReference['resultOf']);

				$params[substr($name, 1)] = $this->resolvePath(explode('/', trim($resultReference['path'], ' /')), $result[1]);
				unset($params[$name]);
			}
		}

		return $params;
	}

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

	private function resolvePath($pathParts, $result) {
		$arrayMode = false;
		while ($part = array_shift($pathParts)) {
			if ($part == '*') {
				foreach ($result as $val) {
					$ret[] = $this->resolvePath($pathParts, $val);
				}
				return $ret;
			}
			
			if(is_object($result)) {
				$result = $result->jsonSerialize();
			}
			
			if (!isset($result[$part])) {
				throw new InvalidResultReference("Could not resolve path part " . $part);
			}

			$result = $result[$part];
		}

		return $result;
	}

}
