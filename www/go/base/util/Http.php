<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Common utilities
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base.util 
 */

namespace GO\Base\Util;


class Http {

	public static function checkUrlForHttp($url,$https=false){
		
		$hasHttp = preg_match('|^http(s)?://.*|i', $url);
		
		if(!$hasHttp){
			$tmpUrl = 'http';
			if($https)
				$tmpUrl .= 's';
			$tmpUrl .= '://'.$url;
			$url = $tmpUrl;
		}
		
		return $url;
	}
		
	/**
	 * Get information about the browser currently using Group-Office.
	 * 
	 * @return array('userAgent','name','version','platform','pattern')
	 */
	public static function getBrowser() {

		if (!isset($_SERVER['HTTP_USER_AGENT'])) {
			return array(
					'userAgent' => '',
					'name' => 'OTHER',
					'version' => 0,
					'platform' => 'Unknown',
					'pattern' => ''
			);
		}

		$u_agent = $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version = "";
		$ub = "";

		//First get the platform?
		if (preg_match('/linux/i', $u_agent)) {
			$platform = 'Linux';
		} elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
			$platform = 'Mac';
		} elseif (preg_match('/windows|win32/i', $u_agent)) {
			$platform = 'Windows';
		}

		// Next get the name of the useragent yes seperately and for good reason
		if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		} elseif (preg_match('/Trident/i', $u_agent)) { // this condition is for IE11
			$bname = 'Internet Explorer';
			$ub = "rv";
		} elseif (preg_match('/Firefox/i', $u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		} elseif (preg_match('/Chrome/i', $u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
		} elseif (preg_match('/Safari/i', $u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		} elseif (preg_match('/Opera/i', $u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		} elseif (preg_match('/Netscape/i', $u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}

		// finally get the correct version number
		// Added "|:"
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
						')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}

		// see how many we have
		$i = count($matches['browser']);
		
		if ($i != 1 && !empty($ub)) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
				$version = isset($matches['version'][0])?$matches['version'][0]:null;
			} else {
				$version =  isset($matches['version'][1])?$matches['version'][1]:null;
			}
		} else {
			$version = isset($matches['version'][0])?$matches['version'][0]:null;
		}

		// check if we have a number
		if ($version == null || $version == "") {
			$version = "?";
		}

		$browser = array(
				'userAgent' => $u_agent,
				'name' => $bname,
				'version' => $version,
				'platform' => $platform,
				'pattern' => $pattern
		);

		return $browser;
	}

	
	/**
	 * CAUTION: EXPERIMENTAL AND NOT RELIABLE
	 * 
	 * Returns geographical data of the connected client
	 * 
	 * @return array
	 */
	public static function getGeoData(){
		$ip = self::getClientIp(); 
		$json  = file_get_contents("https://freegeoip.net/json/$ip");
		return json_decode($json ,true);
	}
	
	/**
	 * Get the ip-address of the client
	 * 
	 * @return string
	 */
	public static function getClientIp() {
		
    $ipaddress = '';

		if (getenv('HTTP_CLIENT_IP')) {
			$ipaddress = getenv('HTTP_CLIENT_IP');
		} else if (getenv('HTTP_X_FORWARDED_FOR')) {
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		} else if (getenv('HTTP_X_FORWARDED')) {
			$ipaddress = getenv('HTTP_X_FORWARDED');
		} else if (getenv('HTTP_FORWARDED_FOR')) {
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		} else if (getenv('HTTP_FORWARDED')) {
			$ipaddress = getenv('HTTP_FORWARDED');
		} else if (getenv('REMOTE_ADDR')) {
			$ipaddress = getenv('REMOTE_ADDR');
		} else {
			$ipaddress = 'UNKNOWN';
		}
		
		return $ipaddress;
	}

	/**
	 * Check if the current user is using internet explorer
	 * 
	 * @return boolean 
	 */
	public static function isInternetExplorer() {
		$b = self::getBrowser();

		return $b['name'] == 'MSIE';
	}
	
	/**
	 * Check if this request SSL secured
	 * 
	 * @return boolean
	 */
	public static function isHttps(){
		return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'],'off');
	}
	
	
	/**
	 * Output the right headers for outputting file data to a browser.
	 * 
	 * @param \GO\Base\Fs\File $file Use \GO\Base\Fs\MemoryFile for outputting variables
	 * @param boolean $inline
	 * @param boolean $cache Cache the file for one day in the browser.
	 * @param array $extraHeaders  Key value array for extra headers
	 */
	public static function outputDownloadHeaders(\GO\Base\Fs\File $file, $inline=true, $cache=false, $extraHeaders=array()) {
		
		header('Content-Transfer-Encoding: binary');		
		
		$disposition = $inline ? 'inline' : 'attachment';

		if ($cache) {
			header("Expires: " . date("D, j M Y G:i:s ", time() + 86400) . 'GMT'); //expires in 1 day
			header('Cache-Control: cache');
			header('Pragma: cache');
		}
		if (Http::isInternetExplorer()) {
			header('Content-Type: application/download');
			header('Content-Disposition: '.$disposition.'; filename="' .rawurlencode($file->name()). '"');

			if (!$cache) {
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
		} else {
			header('Content-Type: ' .$file->mimeType());
			header('Content-Disposition: '.$disposition.'; filename="' . $file->name() . '"');

			if (!$cache) {
				header('Pragma: no-cache');
			}
		}
		
		if($file->exists()){			
			if($file->extension()!='zip') //Don't set content lenght for zip files because gzip of apache will corrupt the download. http://www.heath-whyte.info/david/computers/corrupted-zip-file-downloads-with-php
				header('Content-Length: ' . $file->size());
			
			header("Last-Modified: " . gmdate("D, d M Y H:i:s", $file->mtime())." GMT");
			//Made very large downloads fail
			//header("ETag: " . $file->md5Hash());
		}
		
		foreach($extraHeaders as $header=>$value){
			header($header.': '.$value);
		}
	}
	
	/**
	 * Download a file to the client
	 * 
	 * @param \GO\Base\Fs\File $file Use \GO\Base\Fs\MemoryFile for outputting variables
	 * @param boolean $inline
	 * @param boolean $cache Cache the file for one day in the browser.
	 * @param array $extraHeaders  Key value array for extra headers
	 */
	public static function downloadFile(\GO\Base\Fs\File $file, $inline=true, $cache=false, $extraHeaders=array()){
		self::outputDownloadHeaders($file, $inline, $cache, $extraHeaders);
		$file->output();
	}
	
	/**
	 * Check if the request was a post request.
	 * 
	 * @return boolean 
	 */
	public static function isPostRequest(){
		return isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST';
	}
	
	/**
	 * Reset the post array
	 */
	public static function resetPostRequest(){
		unset($_POST);
	}
	
	/**
	 * Check if this was a HTTP multipart request
	 * 
	 * @return boolean
	 */
	public static function isMultipartRequest(){
		return isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'],'multipart/form-data')!==false;
	}
	
	/**
	 * Check if the request was made with ajax.
	 * 
	 * @return boolean 
	 */
	public static function isAjaxRequest($withExtjsIframeHack=true){
		//dirty hack with $_FILES for extjs iframe file upload
		
		if(!empty($_REQUEST['ajax'])){
			return true;
		}
		
		
		if($withExtjsIframeHack && self::isMultipartRequest()){
			return true;
		}

		if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"){
			return true;
		}
		
		if(\GO::request()->isJson()){
			return true;
		}
		
		return false;
	}
	
	/**
	 * Unset a cookie
	 * 
	 * @param StringHelper $name 
	 */
	public static function unsetCookie($name){
		SetCookie($name,"",time()-3600,\GO::config()->host,"",!empty($_SERVER['HTTPS']),true);
		unset($_COOKIE[$name]);
	}
	
	/**
	 * Set a cookie
	 * 
	 * @param StringHelper $name
	 * @param StringHelper $value
	 * @param StringHelper $expireTime Defaults to one month
	 */
	public static function setCookie($name, $value, $expireTime=2592000){
		$_COOKIE[$name] = $value;
		SetCookie($name,$value,time()+$expireTime,\GO::config()->host,"",!empty($_SERVER['HTTPS']),true);
	}
	
	/**
	 * Get cookie value, returns null when none found
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function getCookie($name){
		return isset($_COOKIE[$name])?$_COOKIE[$name]:null;		
	}
	
	/**
	 * Add GET parameters to a URL
	 *
	 * @param StringHelper $url
	 * @param array $params
	 * @param boolean $htmlspecialchars
	 * @return StringHelper 
	 */
	public static function addParamsToUrl($url,array $params, $htmlspecialchars=true) {
		$amp = $htmlspecialchars ? '&amp;' : '&';
		if (strpos($url, '?') === false) {
			$url .= '?';
		} else {
			$url .= $amp;
		}
		$first=true;
		foreach($params as $key=>$value){
			if($first)
				$first=false;
			else			
				$url .=$amp;
			
			$url .= urlencode($key).'='.urlencode($value);
		}
		
		return $url;
	}	
	
//	Basic auth interferes with new framework because it uses the same Authorization header.
//	public static function basicAuth(){
//		if (!isset($_SERVER['PHP_AUTH_USER']) && !Http::isAjaxRequest() && PHP_SAPI != 'cli') {
//			header('WWW-Authenticate: Basic realm="'.\GO::config()->product_name.'"');
//			header('HTTP/1.0 401 Unauthorized');
//			
//			
//			throw new \GO\Base\Exception\AccessDenied();
//			exit;
//		}
//	}
}
