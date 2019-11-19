<?php
namespace go\core\jmap;

use go\core\util\JSON;

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
	}
	
	/**
	 * Output a response
	 * 
	 * @param array $responseData eg. ['resultName, ['data']];
	 * @return type
	 * @throws Exception
	 */
	public function addResponse($responseData = null) {		
		$data = [$this->methodName,  $responseData, $this->clientCallId];
		$this->data[] = $data;
		echo JSON::encode($data);
		flush();
		
		if($this->methodName != "community/dev/Debugger/get") {
			go()->getDebugger()->debug("response:");
			go()->getDebugger()->debug($responseData);	
		}			
	}
	
	/**
	 * Output an error
	 * 
	 * @param array $responseData eg. ['resultName, ['data']];
	 * @return type
	 * @throws Exception
	 */
	public function addError($responseData = null) {		
		$data = ["error",  $responseData, $this->clientCallId];
		$this->data[] = $data;
		echo JSON::encode($data);
		flush();
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
	
		//return parent::output($this->data);
	}
}
