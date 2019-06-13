<?php
/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id: Number.php 7962 2011-08-24 14:48:45Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package GO.base.util
 */

/**
 * Http client using curl.
 */

namespace GO\Base\Util;


class HttpClient{
	
	private $_curl;
	private $_cookieFile;
	
	private $_lastDownloadUrl;
	
	public $lastHeaders=array();
	
	/**
	 * Key value array of params that will be sent with each request.
	 * 
	 * @var array 
	 */
	public $baseParams;
	
	public function __construct(){
		
		$this->baseParams=array();
		
		if(!function_exists('curl_init'))
			throw new \Exception("Could not initialized HTTP client because PHP is configured withour CURL support.");
		
		$this->_curl = curl_init();
		
		$cookieFile = \GO::user() ? 'cookie_'.\GO::user()->id.'.txt' : 'cookie_0.txt';
		$this->_cookieFile = \GO::config()->tmpdir.$cookieFile;
		
		
		curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookieFile);
		curl_setopt($this->_curl, CURLOPT_COOKIEFILE, $this->_cookieFile);

		//for self-signed certificates
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, false);
		@curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($this->_curl, CURLOPT_ENCODING, "UTF-8");
		
		if(!empty(\GO::config()->curl_proxy))
			curl_setopt($this->_curl, CURLOPT_PROXY, \GO::config()->curl_proxy);
		
		$this->setCurlOption(CURLOPT_USERAGENT, "Group-Office HttpClient ".\GO::config()->version. " (curl)");
		
		//set ajax header for Group-Office
		$this->setCurlOption(CURLOPT_HTTPHEADER, array("X-Requested-With: XMLHttpRequest"));

	}
	
	/**
	 * Set additional curl options. See php.net for details.
	 * 
	 * @param int $option
	 * @param mixed $value 
	 */
	public function setCurlOption($option, $value){
		curl_setopt($this->_curl, $option,$value);
	}
	
	/**
	 * Make a POST request to any URL
	 * 
	 * @param StringHelper $url
	 * @param StringHelper $params POST parameters
	 * @return StringHelper Response of the server.
	 * @throws Exception 
	 */
	public function request($url, $params=array()){
		
		$this->_initRequest($url, $params);
		
		@curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
		
		$response = curl_exec($this->_curl);
		
		$error = curl_error($this->_curl);
		if(!empty($error))
			throw new \Exception("curl error: ".$error);
		
		return $response;		
	}	
	
	
	
	
	public function readHeader($ch, $header){
		if(preg_match('/([\w-]+): (.*)/i', $header, $matches))		
			$this->lastHeaders[$matches[1]] = trim($matches[2]);
		
		return strlen($header);
	}
	
	private function _initRequest($url, $params){
		$params = array_merge($this->baseParams, $params);
		
		$this->lastHeaders=array();
		
		curl_setopt($this->_curl, CURLOPT_URL,$url);
		curl_setopt($this->_curl, CURLOPT_POST, !empty($params));
		if(!empty($params))
			curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $params);
	}
	
	public function getLastDownloadedFilename(){
		
		if(isset($this->lastHeaders['Content-Disposition']) && preg_match('/filename="(.*)"/', $this->lastHeaders['Content-Disposition'], $matches))
			return $matches[1];
		
		$filename = \GO\Base\Fs\File::utf8Basename($this->_lastDownloadUrl);
		
		if(!empty($filename))
			return $filename;
		
		return false;
		
	}
	
	/**
	 * Download a file
	 * 
	 * @param StringHelper $url
	 * @param \GO\Base\Fs\File $outputFile
	 * @param array $params
	 * @return boolean
	 */
	public function downloadFile($url, \GO\Base\Fs\File $outputFile, $params=array()){
		
		$this->_lastDownloadUrl = $url;
		
		$this->_initRequest($url, $params);
		
		$fp = fopen($outputFile->path(), 'w');			
		

		curl_setopt($this->_curl, CURLOPT_FILE, $fp);
		
		curl_setopt($this->_curl, CURLOPT_HEADERFUNCTION, array($this,'readHeader'));

		
		$response = curl_exec($this->_curl);
		
		fclose($fp);

		
		if($outputFile->size())
		{
			return true;
		}else
		{
			return false;
		}
	}
	
	public function getHttpCode(){
		return curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);		
	}

    public function getContentType(){
        return curl_getinfo($this->_curl, CURLINFO_CONTENT_TYPE);
    }
	
	/**
	 * Login to a Group-Office installation
	 * 
	 * @param StringHelper $baseUrl eg. http://customer.group-office.com/
	 * @param StringHelper $username
	 * @param StringHelper $password
	 * @return boolean
	 * @throws Exception 
	 */
	public function groupofficeLogin($baseUrl, $username, $password){
		$postfields =array(
			'username'=>$username,
			'password'=>$password
		);

		\GO::debug("Request: ".$baseUrl.'?r=auth/login');
		
		$response =  $this->request($baseUrl.'?r=auth/login', $postfields);
		
		\GO::debug("Response: ".$response);
		
		$response = json_decode($response, true);
		
//		var_dump($response);

		if(!isset($response['success']) || !$response['success'])
		{
			\GO::debug($response);
			$feedback = "Could not connect to ".\GO::config()->product_name." installation at ".$baseUrl;
			if(isset($response['feedback']))
				$feedback .= "\n\n".$response['feedback'];
			else
				$feedback .= "\n\n".var_export($response, true);
			
			throw new \Exception($feedback);
		}
		
		$this->baseParams['security_token']=$response['security_token'];
		
		return true;
	}
	
	public function __destruct(){
		if($this->_curl)
			curl_close($this->_curl);
		
		if(file_exists($this->_cookieFile))
			unlink($this->_cookieFile);
	}
}
