<?php
namespace go\core\http;

use go\core\fs\File;

class Client {

  private $curl;

  public $baseParams = [];

  private $lastHeaders = [];

  private function getCurl() {
    if(!isset($this->curl)) {

      if(!extension_loaded('curl')) {
        throw new \Exception("The cUrl extension for PHP isn't installed. This is required for Group-Office.");
      }

      $this->curl = curl_init();
      $this->setOption(CURLOPT_FOLLOWLOCATION, true);
      $this->setOption(CURLOPT_ENCODING, "UTF-8");
      $this->setOption(CURLOPT_USERAGENT, "Group-Office HttpClient " . go()->getVersion() . " (curl)");
	
    }
    return $this->curl;
  }

  public function setOption($option, $value) {
    return curl_setopt($this->getCurl(), $option, $value);
  }

  private function initRequest($url) {
    $this->lastHeaders = [];
    $this->setOption(CURLOPT_URL, $url);
    $this->setOption(CURLOPT_RETURNTRANSFER, true);
    $this->setOption(CURLOPT_HEADERFUNCTION, function($curl, $header) {
      if(preg_match('/([\w-]+): (.*)/i', $header, $matches)) {
        $this->lastHeaders[strtolower($matches[1])] = trim($matches[2]);
      }
		
		  return strlen($header);
    });

  }

  /**
   * Perform GET request
   * 
   * @return array ['status' => 200, 'body' => string, 'headers' => []]
   */
  public function get($url) { 
    $this->initRequest($url);
		
    $body = curl_exec($this->getCurl());
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new \Exception($error);
    }

    return [
      'status' => curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE),
      'headers' => $this->lastHeaders,
      'body' => $body
    ];
  }

  public function post($url, array $params) {
    $params = array_merge($this->baseParams, $params);
    
    $this->initRequest($url);

    $this->setOption(CURLOPT_POST, true);
		$this->setOption(CURLOPT_POSTFIELDS, $params);
		
    $body = curl_exec($this->getCurl());
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new \Exception($error);
    }

    return [
      'status' => curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE),
      'headers' => $this->lastHeaders,
      'body' => $body
    ];
  }


  public function download($url, File $file) {
    $fp = $file->open('w');

    $this->initRequest($url);
    $this->setOption(CURLOPT_FILE, $fp);

    curl_exec($this->getCurl());
    fclose($fp);

    $error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new \Exception($error);
    }

    // var_dump($this->lastHeaders);

    if(isset($this->lastHeaders['content-disposition'])) {
      preg_match('/filename="(.*)"/', $this->lastHeaders['content-disposition'], $matches);
      return [
        "name" => $matches[1] ?? "unknown",
        "type" => $this->lastHeaders['content-type'] ?? "application/octet-stream"
      ];
    } else{
      return ["name"=> "unknown", "type" => $this->lastHeaders['content-type'] ?? "application/octet-stream"];
    }

  }

  public function close() 
  {
    return curl_close($this->curl);
  }
}