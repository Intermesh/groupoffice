<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Manages URL rewrite rules for SEO shiny url's
 *
 * @package GO.sites
 * @copyright Copyright Intermesh
 * @version $Id UrlManager.php 2012-06-06 15:23:04 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Components;


class UrlManager
{

	const GET_FORMAT='get';
	const PATH_FORMAT='path';
	
	/**
	 * @var array the URL rules (pattern=>route).
	 */
	public $rules;
	
	public $homeRoute='';
	
	/**
	 * @var boolean whether to enable strict URL parsing.
	 * This property is only effective when urlFormat is 'path'.
	 * If it is set true, then an incoming URL must match one of the rules URL rules.
	 * Otherwise, it will be treated as an invalid request and trigger a 404 HTTP exception.
	 * Defaults to false.
	 */
	public $useStrictParsing = false;
	
	/**
	 * @var StringHelper the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar = 'r';
	
	/**
	 * @var boolean whether routes are case-sensitive. Defaults to true. By setting this to false,
	 * the route in the incoming request will be turned to lower case first before further processing.
	 * As a result, you should follow the convention that you use lower case when specifying
	 * controller mapping and action mapping. Also, the directory names for organizing controllers should
	 * be in lower case.
	 */
	public $caseSensitive = false;
	
	/**
	 * @var StringHelper the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Defaults to empty.
	 */
	public $urlSuffix = '';
	
	/**
	 * @var boolean whether to append GET parameters to the path info part. Defaults to true.
	 * This property is only effective when urlFormat is 'path' and is mainly used when
	 * creating URLs. When it is true, GET parameters will be appended to the path info and
	 * separate from each other using slashes. If this is false, GET parameters will be in query part.
	 */
	public $appendParams = true; //true when get params need to be seperated by slashes in url
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in a rule before using it to create a URL. Defaults to false, meaning
	 * a rule will be used for creating a URL only if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	public $matchValue = false;
	
	
	/**
	 * @var boolean whether to show entry script name in the constructed URL. Defaults to true.
	 */
	public $showScriptName=true;
	
	private $_urlFormat = self::PATH_FORMAT;
	private $_rules = array(); //URL rules
	private $_baseUrl;

	
	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		//Check if we have mod rewrite enabled for pretty URL's
		$this->_urlFormat = \Site::model()->mod_rewrite ? self::PATH_FORMAT : self::GET_FORMAT;
		
		$this->processRules();

		if(isset($this->rules[""]))
			$this->homeRoute=$this->rules[""];
	}

	/**
	 * Processes the URL rules.
	 *  TODO: cache these url rules into memory
	 */
	protected function processRules()
	{
		if (empty($this->rules) || $this->_urlFormat === self::GET_FORMAT)
			return;

		foreach ($this->rules as $pattern => $route)
			$this->_rules[] = new UrlRule($route, $pattern);
	}

	/**
	 * Constructs a URL.
	 * @param StringHelper $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * If the name is '#', the corresponding value will be treated as an anchor
	 * and will be appended at the end of the URL.
	 * @param StringHelper $ampersand the token separating name-value pairs in the URL. Defaults to '&'.
	 * @return StringHelper the constructed URL
	 */
	public function createUrl($route, $params = array(), $ampersand = '&')
	{
		unset($params[$this->routeVar]);
		foreach ($params as $i => $param)
			if ($param === null)
				$params[$i] = '';

		if (isset($params['#']))
		{
			$anchor = '#' . $params['#'];
			unset($params['#']);
		}
		else
			$anchor = '';
		$route = trim($route, '/');

		foreach ($this->_rules as $i => $rule)
		{

			if (($url = $rule->createUrl($this, $route, $params, $ampersand)) !== false)
			{
				if ($rule->hasHostInfo)
					return $url === '' ? '/' . $anchor : $url . $anchor;
				else
					return $this->getBaseUrl() . '/' . $url . $anchor;
			}
		}
		return $this->createUrlDefault($route, $params, $ampersand) . $anchor;
	}

	/**
	 * Creates a URL based on default settings.
	 * @param StringHelper $route the controller and the action (e.g. article/read)
	 * @param array $params list of GET parameters
	 * @param StringHelper $ampersand the token separating name-value pairs in the URL.
	 * @return StringHelper the constructed URL
	 */
	protected function createUrlDefault($route, $params, $ampersand)
	{		
		if($this->_urlFormat===self::PATH_FORMAT)
		{
			$url = rtrim($this->getBaseUrl() . '/' . $route, '/');
			if ($this->appendParams)
			{
				$url = rtrim($url . '/' . $this->createPathInfo($params, '/', '/'), '/');
				return $route === '' ? $url : $url . $this->urlSuffix;
			}
			else
			{
				if ($route !== '')
					$url.=$this->urlSuffix;
				$query = $this->createPathInfo($params, '=', $ampersand);
				return $query === '' ? $url : $url . '?' . $query;
			}
		}
		else
		{
			$url=$this->getBaseUrl();
			if(!$this->showScriptName)
				$url.='/';
			if($route!=='')
			{
				$url.='?'.$this->routeVar.'='.$route;
				if(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
					$url.=$ampersand.$query;
			}
			else if(($query=$this->createPathInfo($params,'=',$ampersand))!=='')
				$url.='?'.$query;
			return $url;
		}
	}

	/**
	 * Returns the base URL of the application.
	 * @return StringHelper the base URL of the application (the part after host name and before query string).
	 * If {@link showScriptName} is true, it will include the script name part.
	 * Otherwise, it will not, and the ending slashes are stripped off.
	 */
	public function getBaseUrl()
	{
		if ($this->_baseUrl !== null)
			return $this->_baseUrl;
		else
		{
			$this->_baseUrl = \Site::request()->getBaseUrl();
			
			if($this->showScriptName && $this->_urlFormat===self::GET_FORMAT)
				$this->_baseUrl.='/index.php';
			
			return $this->_baseUrl;
		}
	}
	
	public function getHomeUrl() {
		$url = \Site::model()->ssl ? 'https://' : 'http://';
		
		$domain = \Site::model()->domain == '*' ? $_SERVER['SERVER_NAME'] : \Site::model()->domain;
		
		if(\Site::model()->ssl && $_SERVER['SERVER_PORT'] !== 443 || !\Site::model()->ssl && $_SERVER['SERVER_PORT'] !== 80) {
			$domain = rtrim($domain, '/') . ':' . $_SERVER['SERVER_PORT'] . '/';
		}

		$url .= $domain.rtrim($this->getBaseUrl(),'/');
		return $url;
	}

	/**
	 * Parses the user request.
	 * @param Request $request the request component
	 * @return StringHelper the route (controllerID/actionID) and perhaps GET parameters in path format.
	 */
	public function parseUrl($request)
	{
		if($this->_urlFormat===self::PATH_FORMAT)
		{
			$rawPathInfo = $request->getPathInfo();
			$pathInfo = $this->removeUrlSuffix($rawPathInfo, $this->urlSuffix);

			foreach ($this->_rules as $i => $rule)
			{
				if (($r = $rule->parseUrl($this, $request, $pathInfo, $rawPathInfo)) !== false)
					return isset($_GET[$this->routeVar]) ? $_GET[$this->routeVar] : $r;
			}
			$par = explode("/", $pathInfo, 4);
			if(isset($par[3]))
				$this->parsePathInfo($par[3]);
			return $pathInfo;
		}else if(isset($_REQUEST[$this->routeVar]))
			return $_REQUEST[$this->routeVar];
		else
			return $this->homeRoute;
	}

	/**
	 * Removes the URL suffix from path info.
	 * @param StringHelper $pathInfo path info part in the URL
	 * @param StringHelper $urlSuffix the URL suffix to be removed
	 * @return StringHelper path info with URL suffix removed.
	 */
	public function removeUrlSuffix($pathInfo, $urlSuffix)
	{
		if ($urlSuffix !== '' && substr($pathInfo, -strlen($urlSuffix)) === $urlSuffix)
			return substr($pathInfo, 0, -strlen($urlSuffix));
		else
			return $pathInfo;
	}

	/**
	 * Creates a path info based on the given parameters.
	 * @param array $params list of GET parameters
	 * @param StringHelper $equal the separator between name and value
	 * @param StringHelper $ampersand the separator between name-value pairs
	 * @param StringHelper $key this is used internally.
	 * @return StringHelper the created path info
	 */
	public function createPathInfo($params, $equal, $ampersand, $key = null)
	{
		$pairs = array();
		foreach ($params as $k => $v)
		{
			if ($key !== null)
				$k = $key . '[' . $k . ']';

			if (is_array($v))
				$pairs[] = $this->createPathInfo($v, $equal, $ampersand, $k);
			else
				$pairs[] = urlencode($k) . $equal . urlencode($v);
		}
		return implode($ampersand, $pairs);
	}

	public function parsePathInfo($pathInfo)
	{
		if ($pathInfo === '')
			return;
		$segs = explode('/', $pathInfo . '/');
		$n = count($segs);
		for ($i = 0; $i < $n - 1; $i+=2)
		{
			$key = $segs[$i];
			if ($key === '')
				continue;
			$value = $segs[$i + 1];
			if (($pos = strpos($key, '[')) !== false && ($m = preg_match_all('/\[(.*?)\]/', $key, $matches)) > 0)
			{
				$name = substr($key, 0, $pos);
				for ($j = $m - 1; $j >= 0; --$j)
				{
					if ($matches[1][$j] === '')
						$value = array($value);
					else
						$value = array($matches[1][$j] => $value);
				}
				if (isset($_GET[$name]) && is_array($_GET[$name]))
					$value = self::mergeArray($_GET[$name], $value);
				$_REQUEST[$name] = $_GET[$name] = $value;
			}
			else
				$_REQUEST[$key] = $_GET[$key] = $value;
		}
	}

	/**
	 * Merge 2 arrays recusifly. if the same keys accur overwrite.
	 * TODO: place this in a helper class
	 */
	public static function mergeArray($a, $b)
	{
		$args = func_get_args();
		$res = array_shift($args);
		while (!empty($args))
		{
			$next = array_shift($args);
			foreach ($next as $k => $v)
			{
				if (is_integer($k))
					isset($res[$k]) ? $res[] = $v : $res[$k] = $v;
				else if (is_array($v) && isset($res[$k]) && is_array($res[$k]))
					$res[$k] = self::mergeArray($res[$k], $v);
				else
					$res[$k] = $v;
			}
		}
		return $res;
	}
	
	/**
	 * Checks if the url includes the http/https/ftp :// tag
	 * If it doesn't include that then it adds http:// to the url
	 * 
	 * @param StringHelper $url
	 * @return StringHelper
	 */
	public function addHttp($url) {
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
		return $url;
	}
}

