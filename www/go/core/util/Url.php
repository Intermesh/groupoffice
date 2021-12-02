<?php
namespace go\core\util;

use go\core\data\ArrayableInterface;

/**
 * URL Object
 * 
 * Used to contruct URL's
 */
class Url implements ArrayableInterface{
	
	private $location;
	
	private $params;
	
	/**
	 * Create an URL object
	 * 
	 * @param string $location eg. http://localhost/ or /relative
	 * @param array $params
	 */
	public function __construct($location, array $params = []) {
		$this->location = $location;
		$this->params = $params;
	}
	
	/**
	 * Adds a query paramter
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function addParam($name, $value) {
		$this->params[$name] = $value;
	}
	
	/**
	 * Remove query parameter
	 * 
	 * @param string $name
	 */
	public function removeParam($name) {
		unset($this->params[$name]);
	}
	
	public function __toString() {
	
		$url = $this->location;
		
		if (!empty($this->params)) {
			
			$sep = '?';

			foreach ($this->params as $key => $value) {				
				$url .= $sep.$key . '=' . urlencode($value);				
				$sep ='&';
			}
		}

		return $url;
	}
	
	public function toArray(array $properties = null): array
	{
		return (string) $this;
	}
	
	/**
	 * Parse URL with params into URL object
	 * 
	 * @param string $url
	 * @return \self
	 */
	public static function parse($url) {
		
		$pos = strpos($url, '?');
		if(!$pos) {
			return new self($url);
		} else {
			$components = parse_url($url, PHP_URL_QUERY);
			$params = parse_str($components['query']);
			
			return new self(substr($url,0, $pos), $params);
		}
		
		
	}
}
