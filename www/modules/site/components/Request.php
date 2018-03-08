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
 * Description of file
 *
 * @package GO.
 * @copyright Copyright Intermesh
 * @version $Id Request.php 2012-06-07 14:59:00 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Components;


class Request
{

	private $_requestUri;
	private $_pathInfo;
	private $_scriptUrl;
	private $_baseUrl;
	private $_hostInfo;

	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		//if(strpos($url,'/')===0)
		//$url=$this->getHostInfo().$url;
		header('Location: ' . $url, true, $statusCode);
		if ($terminate)
			exit();
	}

	public function getPathInfo()
	{
		if ($this->_pathInfo === null)
		{
			$pathInfo = $this->getRequestUri();

			if (($pos = strpos($pathInfo, '?')) !== false)
				$pathInfo = substr($pathInfo, 0, $pos);

			$pathInfo = $this->decodePathInfo($pathInfo);

			$scriptUrl = $this->getScriptUrl();
			$baseUrl = $this->getBaseUrl();

			if (strpos($pathInfo, $scriptUrl) === 0)
				$pathInfo = substr($pathInfo, strlen($scriptUrl));
			else if ($baseUrl === '' || strpos($pathInfo, $baseUrl) === 0)
				$pathInfo = substr($pathInfo, strlen($baseUrl));
			else if (strpos($_SERVER['PHP_SELF'], $scriptUrl) === 0)
				$pathInfo = substr($_SERVER['PHP_SELF'], strlen($scriptUrl));
			else
				throw new \Exception('HttpRequest is unable to determine the path info of the request.');

//echo $pathInfo;
			$this->_pathInfo = trim($pathInfo, '/');
		}
		return $this->_pathInfo;
	}

	protected function decodePathInfo($pathInfo)
	{
		$pathInfo = urldecode($pathInfo);

		// is it UTF-8?
		// http://w3.org/International/questions/qa-forms-utf-8.html
		if (preg_match('%^(?:
				[\x09\x0A\x0D\x20-\x7E]            # ASCII
			| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
			| \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
			| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
			| \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
			| \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
			| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
			| \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
			)*$%xs', $pathInfo))
		{
			return $pathInfo;
		}
		else
		{
			return utf8_encode($pathInfo);
		}
	}

	public function getRequestUri()
	{
		if ($this->_requestUri === null)
		{
			if (isset($_SERVER['HTTP_X_REWRITE_URL'])) // IIS
				$this->_requestUri = $_SERVER['HTTP_X_REWRITE_URL'];
			else if (isset($_SERVER['REQUEST_URI']))
			{
				$this->_requestUri = $_SERVER['REQUEST_URI'];
				if (!empty($_SERVER['HTTP_HOST']))
				{
					if (strpos($this->_requestUri, $_SERVER['HTTP_HOST']) !== false)
						$this->_requestUri = preg_replace('/^\w+:\/\/[^\/]+/', '', $this->_requestUri);
				}
				else
					$this->_requestUri = preg_replace('/^(http|https):\/\/[^\/]+/i', '', $this->_requestUri);
			}
			else if (isset($_SERVER['ORIG_PATH_INFO']))	// IIS 5.0 CGI
			{
				$this->_requestUri = $_SERVER['ORIG_PATH_INFO'];
				if (!empty($_SERVER['QUERY_STRING']))
					$this->_requestUri.='?' . $_SERVER['QUERY_STRING'];
			}
			else
				throw new \Exception('HttpRequest is unable to determine the request URI.');
		}

		return $this->_requestUri;
	}

	//public function getBaseUrl($absolute=false)
	public function getBaseUrl()
	{
		if ($this->_baseUrl === null)
			$this->_baseUrl = rtrim(dirname($this->getScriptUrl()), '\\/');
		return $this->_baseUrl;
	}

	public function getScriptUrl()
	{
		if ($this->_scriptUrl === null)
		{
			$scriptName = basename($_SERVER['SCRIPT_FILENAME']);
			if (basename($_SERVER['SCRIPT_NAME']) === $scriptName)
				$this->_scriptUrl = $_SERVER['SCRIPT_NAME'];
			else if (basename($_SERVER['PHP_SELF']) === $scriptName)
				$this->_scriptUrl = $_SERVER['PHP_SELF'];
			else if (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $scriptName)
				$this->_scriptUrl = $_SERVER['ORIG_SCRIPT_NAME'];
			else if (($pos = strpos($_SERVER['PHP_SELF'], '/' . $scriptName)) !== false)
				$this->_scriptUrl = substr($_SERVER['SCRIPT_NAME'], 0, $pos) . '/' . $scriptName;
			else if (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0)
				$this->_scriptUrl = str_replace('\\', '/', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME']));
			else
				throw new \Exception('Request is unable to determine the entry script URL.');
		}
		return $this->_scriptUrl;
	}

	public function getHostInfo()
	{
		if ($this->_hostInfo === null)
		{
			$http = 'http'; //TODO: https if secure connection
			$https = (isset($_SERVER["HTTPS"]) && ($_SERVER["HTTPS"] == "on" || $_SERVER["HTTPS"] == "1")) || !empty($_SERVER["HTTP_X_SSL_REQUEST"]);
			
			if($https)
				$http.='s';
			
			if (isset($_SERVER['HTTP_HOST']))
				$this->_hostInfo = $http . '://' . $_SERVER['HTTP_HOST'];
			else
			{
				$this->_hostInfo = $http . '://' . $_SERVER['SERVER_NAME'];
			}
			
			if ((!$https && $_SERVER["SERVER_PORT"] != "80") || ($https && $_SERVER["SERVER_PORT"] != "443")) 
				$this->_hostInfo .= ":".$_SERVER["SERVER_PORT"];
		}
		return $this->_hostInfo;
	}

}

?>
