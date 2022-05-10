<?php
namespace go\core\http;

use Exception as CoreException;
use go\core\fs\File;
use go\core\util\JSON;

/**
 * Simple HTTP client
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Client {

  private $curl;

  public $baseParams = [];

  private $lastHeaders = [];

	/**
	 * @return false|resource
	 * @noinspection PhpMissingReturnTypeInspection
	 */
  private function getCurl() {
    if(!isset($this->curl)) {
      $this->curl = curl_init();
      $this->setOption(CURLOPT_FOLLOWLOCATION, true);
      $this->setOption(CURLOPT_ENCODING, "UTF-8");
      $this->setOption(CURLOPT_USERAGENT, "Group-Office HttpClient " . go()->getVersion() . " (curl)");

	    $this->setOption(CURLOPT_CONNECTTIMEOUT, 5);
	    $this->setOption(CURLOPT_TIMEOUT, 360);
    }
    return $this->curl;
  }

	/**
	 * Set cUrl option
	 *
	 * @param int $option
	 * @param mixed $value
	 * @return bool
	 */
  public function setOption(int $option, $value): bool
  {
    return curl_setopt($this->getCurl(), $option, $value);
	}

	private $headers = [];

	public function setHeader(string $name, string $value): Client
	{
		$this->headers[$name] = $value;

		return $this;
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


	  $headers = $this->getHeadersForCurl();
	  if(!empty($headers)) {
		  $this->setOption(CURLOPT_HTTPHEADER, $headers);
	  }

  }

	/**
	 * Perform GET request
	 *
	 * @return array ['status' => 200, 'body' => string, 'headers' => []]
	 * @throws CoreException
	 */
  public function get(string $url): array
  {
    $this->initRequest($url);
		
    $body = curl_exec($this->getCurl());
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new CoreException($error);
    }

    return [
      'status' => curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE),
      'headers' => $this->lastHeaders,
      'body' => $body
    ];
  }

	/**
	 * POST JSON body
	 *
	 * @param string $url
	 * @param array $data
	 * @return array
	 * @throws CoreException
	 */
  public function postJson(string $url, array $data): array
  {
  	$str = JSON::encode($data);

		$this->setHeader('Content-Type', 'application/json; charset=utf-8');
	  $this->setHeader('Content-Length', strlen($str));

  	$response =  $this->post($url, $str);
  	$response['body'] = JSON::decode($response['body']);

  	return $response;
  }

	/**
	 * Make a POST request
	 *
	 * @param string $url
	 * @param array|string $data Array of HTTP post fields or string for RAW body.
	 * @return array
	 * @throws CoreException
	 */
  public function post(string $url, $data): array
  {
  	if(is_array($data)) {
		  $data = array_merge($this->baseParams, $data);
		  $this->setOption(CURLOPT_CUSTOMREQUEST, "POST");
	  } else{
		  $this->setOption(CURLOPT_POST, true);
	  }
    
    $this->initRequest($url);
		$this->setOption(CURLOPT_POSTFIELDS, $data);
		
    $body = curl_exec($this->getCurl());
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new CoreException($error);
    }

    return [
      'status' => curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE),
      'headers' => $this->lastHeaders,
      'body' => $body
    ];
  }

	/**
	 * Download an URL to a file
	 *
	 * @param string $url
	 * @param File $file
	 * @return array
	 * @throws CoreException
	 */
  public function download(string $url, File $file): array
  {
    $fp = $file->open('w');

    $this->initRequest($url);
    $this->setOption(CURLOPT_FILE, $fp);

    curl_exec($this->getCurl());
    fclose($fp);

    $error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new CoreException($error);
    }

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

	/**
	 * Close the connection
	 *
	 * @return void
	 */
  public function close() 
  {
     curl_close($this->curl);
  }

	private function getHeadersForCurl(): array
	{
		$s = [];
		foreach($this->headers as $key => $value) {
			$s[] = $key.': ' . $value;
		}

		return $s;
	}
}