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
use Jasny\PhpdocParser\PhpdocParser;
use Jasny\PhpdocParser\Set\PhpDocumentor;
use Jasny\PhpdocParser\Tag\Summery;
use ReflectionClass;
use ReflectionMethod;
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
class Router
{

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
		
		// A method may not change the authenticated user for the next methods in this request. For example the WOPI
		// controller authenticates with a token from the query string.
		$authState = go()->getAuthState();
		$legacyUserId = $_SESSION['GO_SESSION']['user_id'] ?? null;

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
			if(go()->getAuthState() !== $authState) {
				$this->restoreAuthState($authState, $legacyUserId);
			}

			if($method != "community/dev/Debugger/get") {
				go()->getDebugger()->groupEnd();
			}
		}
	}

	/**
	 * Restore the auth state after a method replaced it
	 *
	 * The legacy session copies the user ID from the auth state into the PHP session. Restore that too, otherwise the
	 * user would stay logged in on the next requests.
	 *
	 * @param \go\core\auth\State|null $authState The state before the method was called
	 * @param int|string|null $legacyUserId The legacy session user ID before the method was called
	 */
	private function restoreAuthState(?\go\core\auth\State $authState, int|string|null $legacyUserId): void
	{
		ErrorHandler::log("JMAP method changed the authenticated user. Restored the previous state.");

		go()->setAuthState($authState ?? new State());

		if(isset($_SESSION['GO_SESSION'])) {
			if(isset($legacyUserId)) {
				$_SESSION['GO_SESSION']['user_id'] = $legacyUserId;
			} else {
				unset($_SESSION['GO_SESSION']['user_id']);
			}
		}
	}

	/**
	 * Descriptions of the methods that most entities implement. Their doc blocks are generic, like "Handles the Foo entity's Foo/get command".
	 */
	const STANDARD_METHODS = [
		'get' => 'Get entities by ID. Standard JMAP method, see https://jmap.io/spec-core.html#get',
		'query' => 'Search entities with a filter and sort. Returns IDs. Standard JMAP method, see https://jmap.io/spec-core.html#query',
		'set' => 'Create, update and destroy entities. Updates are patches. Standard JMAP method, see https://jmap.io/spec-core.html#set',
		'changes' => 'Get the IDs of entities that were created, updated or destroyed since a state. Standard JMAP method, see https://jmap.io/spec-core.html#changes',
		'export' => 'Export entities to a file. Takes the parameters of Foo/get and an "extension" like "csv". Returns the file in "blob".',
		'import' => 'Import a file that was uploaded as a blob. Takes "blobId" and optional "values" to set on each entity. Runs in the background and returns "pid".',
		'exportColumns' => 'Get the columns that can be exported for an "extension" like "csv".',
		'importCSVMapping' => 'Get the column mapping for importing the CSV file of "blobId", or a saved mapping by "id".',
		'merge' => 'Merge the entities of "ids" into the first one. The others are destroyed.'
	];

	/**
	 * Get the controller class that handles the JMAP methods of an entity, like Note/get.
	 *
	 * @param EntityType $entityType
	 * @return string The class name. It may not exist.
	 */
	public static function entityControllerClass(EntityType $entityType): string
	{
		// Slightly less ugly hack - more and more old style ActiveRecord models need JMAP treatment.
		switch ($entityType->getName()) {
			case "Project":
				if (go()->getModule('business', 'projects')) {
					return "go\\modules\\business\\projects\\controller\\Project";
				} else {
					return 'GO\\Projects2\\Controller\\ProjectEntityController';
				}
			case "Account":
				return "go\\modules\\community\\email\\controller\\Account";
			case "Income":
				return 'GO\\Projects2\\Controller\\IncomeEntityController';
			case "TimeEntry":
				return 'GO\\Projects2\\Controller\\TimeEntryEntityController';
			default:
				return str_ireplace("model", "controller", $entityType->getClassName());
		}
	}

	/**
	 * Check if a class may be routed to as a JMAP controller
	 *
	 * Controllers in a "cli" namespace are for the command line only and only JMAP controllers authenticate the user
	 * in their constructor.
	 *
	 * @param string $controllerClass
	 * @return bool
	 */
	public static function isRoutableController(string $controllerClass): bool
	{
		if (stripos('\\' . $controllerClass . '\\', '\\cli\\') !== false) {
			return false;
		}

		return class_exists($controllerClass) && is_subclass_of($controllerClass, \go\core\Controller::class);
	}

	/**
	 * Get the JMAP methods of an entity, for example "get", "query" and "set" for Note/get, Note/query and Note/set.
	 *
	 * These are the public methods of the controller from {@see entityControllerClass()}. The standard methods are
	 * described by {@see STANDARD_METHODS}, others by their doc block.
	 *
	 * @param EntityType $entityType
	 * @return array<string, string> Method name => description
	 */
	public static function getEntityMethods(EntityType $entityType): array
	{
		$controllerClass = self::entityControllerClass($entityType);
		if (!self::isRoutableController($controllerClass)) {
			return [];
		}

		$parser = new PhpdocParser(PhpDocumentor::tags()->with([new Summery()]));

		$methods = [];
		foreach ((new ReflectionClass($controllerClass))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			if ($method->isStatic() || str_starts_with($method->getName(), '__') || $method->getDeclaringClass()->getName() === \go\core\Controller::class) {
				continue;
			}

			$name = $method->getName();

			// Method names are case insensitive in PHP. For example User has importCsvMapping.
			$standard = array_change_key_case(self::STANDARD_METHODS)[strtolower($name)] ?? null;
			if (isset($standard)) {
				$methods[$name] = $standard;
				continue;
			}

			$doc = $method->getDocComment();
			try {
				$meta = $doc ? $parser->parse($doc) : [];
			} catch (Throwable) {
				$meta = [];
			}
			$methods[$name] = trim($meta['description'] ?? '');
		}

		ksort($methods);

		return $methods;
	}

	/**
	 * @throws Exception
	 * @throws CoreException
	 */
	private function findControllerAction(string $method): array
	{
		$parts = explode('/', $method);

		// The parts are used for the class name, so a "\" could route to any class
		foreach ($parts as $part) {
			if (!preg_match('/^[a-z0-9_]+$/i', $part)) {
				throw new Exception(400, 'Bad request. Invalid method "' . $method . '"');
			}
		}

		if (count($parts) == 2) {
			//eg. "note/query"
			$entityType = EntityType::findByName($parts[0]);
			if (!$entityType) {
				throw new Exception(400, 'Bad request. Entity type "' . $parts[0] . '"  not found');
			}

			$controllerClass = self::entityControllerClass($entityType);
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

		if (!self::isRoutableController($controllerClass)) {
			throw new Exception(400, 'Bad request. Controller "' . $controllerClass . '"  not found');
		}

		// Magic methods like __construct are public too
		if (str_starts_with($controllerMethod, '__') || !method_exists($controllerClass, $controllerMethod)) {
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
