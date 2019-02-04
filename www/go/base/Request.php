<?php

namespace GO\Base;

class Request {
	
	public $post;
	
	public $get;
	
	/**
	 * The request headers
	 * 
	 * @var string[] 
	 */
	private $_headers;
	
	public function __construct() {
		if($this->isJson()){
			$this->post = json_decode(file_get_contents('php://input'), true);
			
			// Check if the post is filled with an array. Otherwise make it an empty array.
			if(!is_array($this->post))
				$this->post = array();
			
		}else
		{
			$this->post=$_POST;
		}
		
		$this->get=$_GET;		
	}
	
	public function getContentType() {
		if (PHP_SAPI == 'cli') {
			return 'cli';
		} else {
			return isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
		}
	}

	public function isJson() {
		return isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], 'application/json') !== false;
	}

	/**
	 * Check if this request SSL secured
	 * 
	 * @return boolean
	 */
	public function isHttps() {
		if(!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
			return true;
		}
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Check if this request is an XmlHttpRequest
	 * 
	 * @return boolean
	 */
	public function isAjax(){
		return Util\Http::isAjaxRequest();
	}
	
	/**
	 * Return true if this is a HTTP post
	 * 
	 * @return boolean
	 */
	public function isPost(){
		return $_SERVER['REQUEST_METHOD']==='POST';
	}
	
	/**
	 * Get the request method
	 * 
	 * @param string PUT, POST, DELETE, GET, PATCH, HEAD
	 */
	public function getMethod() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
	
	/**
	 * Get the route for the router
	 * 
	 * This is the path between index.php and the query parameters with trailing and leading slashes trimmed.
	 * 
	 * In this example:
	 * 
	 * /index.php/some/route?queryParam=value
	 * 
	 * The route would be "some/route"
	 * 
	 * @param string|null
	 */
	public function getRoute() {
		return isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : null;
	}
	
		/**
	 * Get the request headers as a key value array. The header names are in lower case.
	 * 
	 * Example:
	 * 
	 * <code>
	 * [
	 * 'accept' => 'application/json',
	 * 'accept-aanguage' => 'en-us'
	 * ]
	 * </code>
	 * 
	 * @return array
	 */
	public function getHeaders() {		
		
		if(!function_exists('apache_request_headers'))
		{
			return [];
		}
		
		if (!isset($this->headers)) {
			$this->_headers = array_change_key_case(apache_request_headers(),CASE_LOWER);			
		}
		return $this->_headers;
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
	
}
