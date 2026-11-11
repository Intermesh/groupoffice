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

	/**
	 * Check if a URL is a path on this server, so it's safe to redirect to after a login (no open redirect)
	 *
	 * Only paths starting with a single "/" are accepted. "//example.com" or "/\example.com" are refused because browsers
	 * treat them as another host, and so are URLs with a scheme, backslashes or control characters.
	 *
	 * @param string $url
	 * @return bool
	 */
	public static function isLocalPath(string $url): bool
	{
		return str_starts_with($url, '/')
			&& !str_starts_with($url, '//')
			&& !str_contains($url, '\\')
			&& !preg_match('/[\x00-\x1f\x7f]/', $url);
	}

	/**
	 * Check if an absolute http(s) URL has the same scheme, host and port as another URL
	 *
	 * Use it to check that a URL from a request points to a known server, instead of comparing the start of the
	 * strings: "https://example.com.evil.tld" starts with "https://example.com". URLs with a user name, white space,
	 * backslashes or control characters are refused because they are parsed differently by browsers and curl.
	 *
	 * @param string $url The URL to check
	 * @param string $origin The known URL, for example the URL of Group-Office. Only its scheme, host and port are used.
	 * @return bool
	 */
	public static function isSameOrigin(string $url, string $origin): bool
	{
		$a = self::parseOrigin($url);
		return $a !== null && $a === self::parseOrigin($origin);
	}

	/**
	 * Get "scheme://host:port" of an absolute http(s) URL with the default port filled in, or null if it's not valid
	 */
	private static function parseOrigin(string $url): ?string
	{
		if(preg_match('/[\x00-\x20\x7f\\\\]/', $url)) {
			return null;
		}

		$parts = parse_url($url);
		if($parts === false || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
			return null;
		}

		$scheme = strtolower($parts['scheme'] ?? '');
		if($scheme !== 'http' && $scheme !== 'https') {
			return null;
		}

		$port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

		return $scheme . '://' . rtrim(strtolower($parts['host']), '.') . ':' . $port;
	}
}
