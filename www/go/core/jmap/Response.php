<?php
namespace go\core\jmap;

/**
 * JMAP Response object
 * 
 * Uses application/json and formats every method output according to the JSON spec.
 */
class Response extends \go\core\http\Response {
	
	private $clientCallId;
	
	private $methodName;
	
	private $data = [];
	
	public function __construct() {
		parent::__construct();
		$this->setHeader('Content-Type', 'application/json;charset=utf-8');
		$this->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
		$this->setHeader('Pragma', 'no-cache');
	}

	protected function sendSecurityHeaders()
	{
		//no headers needed for JMAP
		$this->sendCorsHeaders();
	}

	/**
	 * Output a response
	 * 
	 * @param array $responseData eg. ['resultName, ['data']];
	 */
	public function addResponse($responseData = null) {		
		$this->data[] = [$this->methodName,  $responseData, $this->clientCallId];
		
		if($this->methodName != "community/dev/Debugger/get") {
			go()->getDebugger()->debug("response:",0, false);
			go()->getDebugger()->debug($responseData,0, false);
		}			
	}
	
	/**
	 * Output an error
	 * 
	 * @param array $responseData eg. ['resultName, ['data']];
	 */
	public function addError($responseData = null) {		
		$this->data[] = ["error",  $responseData, $this->clientCallId];
	}
	
	/**
	 * 
	 * Array of responses.
	 * 
	 * [
	 *	['resultName, ['data'], 'clientCallId'],
	 *  ['resultName, ['data'], 'clientCallId'],
	 * ]
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * The client call ID is passed by the router. It needs to be appended to 
	 * every response.
	 * 
	 * @param string $clientCallId
	 */
	public function setClientCall($methodName, $clientCallId) {
		$this->clientCallId = $clientCallId;
		$this->methodName = $methodName;
	}
	
	public function output($data = null) {
		
		if(isset($data)) {
			$this->addResponse($data);
		}
	
		return parent::output($this->data);
	}
}
