<?php
namespace go\core\util;

use go\core\data\ArrayableInterface;

/**
 * URL Object
 * 
 * Used to construct URL's
 */
class Url implements ArrayableInterface {
	
	private $location;
	
	private $params;
	
	/**
	 * Create a URL object
	 * 
	 * @param string $location eg. http://localhost/ or /relative
	 * @param array $params
	 */
	public function __construct(string $location, array $params = []) {
		$this->location = $location;
		$this->params = $params;
	}
	
	/**
	 * Adds a query paramter
	 * 
	 * @param string $name
	 * @param string $value
	 */
	public function addParam(string $name, string $value) {
		$this->params[$name] = $value;
	}
	
	/**
	 * Remove query parameter
	 * 
	 * @param string $name
	 */
	public function removeParam(string $name) {
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
	
	public function toArray(array $properties = null): array|null
	{
		return (string) $this;
	}
	
	/**
	 * Parse URL with params into URL object
	 * 
	 * @param string $url
	 * @return self
	 */
	public static function parse($url): Url
	{
		
		$pos = strpos($url, '?');
		if(!$pos) {
			return new self($url);
		} else {
			$components = parse_url($url, PHP_URL_QUERY);
			parse_str($components['query'], $params);
			
			return new self(substr($url,0, $pos), $params);
		}
		
		
	}
}
