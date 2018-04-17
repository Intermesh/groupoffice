<?php
namespace go\core\http;

use Exception;
use go\core\Singleton;
use stdClass;


/**
 * The HTTP request class.
 *
 * <p>Example:</p>
 * ```````````````````````````````````````````````````````````````````````````
 * $var = IFW::app()->request()->queryParams['someVar'];
 * 
 * //Get the JSON or XML data
 * $var = IFW::app()->request()->payload['somevar'];
 * ```````````````````````````````````````````````````````````````````````````
 *
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Request extends Singleton{

	/**
	 * The body of the request. Only JSON is supported at the moment.
	 * 
	 * @var mixed[]
	 */
	private $body;	

	
	/**
	 * The request headers
	 * 
	 * @var string[] 
	 */
	private $headers;
	
	/**
	 * Get all query parameters of this request
	 * 
	 * @return array ['paramName' => 'value']
	 */
	public function getQueryParams() {
		return $_GET;
	}

	
	
	/**
	 * Get the values of the Accept header in lower case
	 * 
	 * @param string[]
	 */
	public function getAccept() {

		if(empty($_SERVER['HTTP_ACCEPT'])) {
			return [];
		}
		
		$accept = explode(',', strtolower($_SERVER['HTTP_ACCEPT']));		
		return array_map('trim', $accept);				
	}
	
	/**
	 * Get the accepted languages sent by the request in lower case
	 * 
	 * @return string[]
	 */
	public function getAcceptLanguages() {
		if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return [];
		}
		
		$accept = explode(',', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']));		
		return array_map('trim', $accept);				
	}
	
	public function getHostname() {
		$protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
		$path = explode('/',$_SERVER['REQUEST_URI']);
		array_pop($path); // pop filename and querystring
		return $protocol . $_SERVER['HTTP_HOST'] . implode('/',$path);
	}

	/**
	 * Get the request headers as a key value array. The header names are in lower case.
	 * 
	 * Example:
	 * 
	 * ```````````````````````````````````````````````````````````````````````````
	 * [
	 * 'accept' => 'application/json',
	 * 'accept-language' => 'en-us'
	 * ]
	 * ```````````````````````````````````````````````````````````````````````````
	 * 
	 * @return array
	 */
	public function getHeaders() {		
		
		if(!function_exists('apache_request_headers'))
		{
			return [];
		}
		
		if (!isset($this->headers)) {
			$this->headers = array_change_key_case(apache_request_headers(),CASE_LOWER);			
		}
		return $this->headers;
	}
	
	/**
	 * Get request header value
	 * 
	 * @param string $name
	 * @param string
	 */
	public function getHeader($name) {
		$name = strtolower($name);
		$headers = $this->getHeaders();
		return isset($headers[$name]) ? $headers[$name] : null;
	}

	/**
	 * Get the request payload
	 * 
	 * The data send in the body of the request. 
	 * We support:
	 * 
	 * #application/x-www-form-urlencoded
	 * #multipart/form-data
	 * #application/json
	 * #application/xml or text/xml
	 * 
	 * @return stdClass
	 */
	public function getBody() {
		if (!isset($this->body)) {			
			//If it's a form post (application/x-www-form-urlencoded or multipart/form-data) with HTML then PHP already built the data
			if(!empty($_POST)) {
				$this->body = $_POST;
			}else if($this->isJson())
			{				
				if(empty($this->getRawBody())) {
					$this->body = [];
				}else {
					$this->body = json_decode($this->getRawBody(), true);

					// Check if the post is filled with an array. Otherwise make it an empty array.
					if(!isset($this->body)){				
						throw new Exception("JSON decoding error: '".json_last_error_msg()."'.\n\nJSON data from client: \n\n".var_export($this->getRawBody(), true));
					}
				}
			}
		}

		return $this->body;
	}
	
	/**
	 * Get raw request body as string.
	 * @param string
	 */
	public function getRawBody() {
		if(!isset($this->rawBody)) {
			$this->rawBody = file_get_contents('php://input');
		}
		
		return $this->rawBody;
	}

	/**
	 * Get's the content type header
	 *
	 * @param string
	 */
	public function getContentType() {
		return isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
	}

	/**
	 * Get the request method in upper case
	 * 
	 * @param string PUT, POST, DELETE, GET, PATCH, HEAD
	 */
	public function getMethod() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * Check if the request posted a JSON body
	 *
	 * @return boolean
	 */
	public function isJson() {
		return strpos($this->getContentType(), 'application/json') !== false;
	}
	
	/**
	 * Check if the request posted a JSON body
	 *
	 * @return boolean
	 */
	private function isXml() {
		return strpos($this->getContentType(), '/xml') !== false;
	}

	/**
	 * Check if this request SSL secured
	 *
	 * @return boolean
	 */
	public function isHttps() {
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off');
	}
  
  /**
   * Check if this request is an XMLHttpRequest
   *    * 
   * @return boolean
   */
  public function isXHR() {
    return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest";
  }
}
