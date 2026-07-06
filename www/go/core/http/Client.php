<?php

namespace go\core\http;

use CurlHandle;
use Exception as CoreException;
use go\core\exception\Forbidden;
use go\core\fs\File;
use go\core\util\JSON;

/**
 * Simple HTTP client
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Client
{
	/**
	 * Base URI for all requests
	 */
	public string $baseUri = "";

	/**
	 * Default parameters for POST requests
	 */
	public array $baseParams = [];

	private array $lastHeaders = [];
	protected array $headers = [];
	protected ?string $lastBody;

	protected CurlHandle|false $curl;

	/**
	 * Only allow URL's that resolve to a global IP address.
	 *
	 * See https://www.php.net/manual/en/filter.constants.php#constant.filter-flag-global-range
	 * @var bool
	 */
	public bool $globalRangeOnly = false;

	/**
	 * @return false|CurlHandle
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	private function getCurl()
	{
		if (!isset($this->curl)) {
			$this->curl = curl_init();
			$this->setOption(CURLOPT_FOLLOWLOCATION, true)
				->setOption(CURLOPT_ENCODING, "UTF-8")
				->setOption(CURLOPT_USERAGENT, "GroupOffice HttpClient " . go()->getVersion() . " (curl)")
				->setOption(CURLOPT_CONNECTTIMEOUT, 5)
				->setOption(CURLOPT_TIMEOUT, 360)
				->setOption(CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS) // Only allow HTTP(s) to prevent unsafe  protocols like file:///
				->setOption(CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
		}
		return $this->curl;
	}

	/**
	 * Set cUrl option
	 *
	 * @see https://www.php.net/manual/en/function.curl-setopt.php
	 * @param int $option
	 * @param mixed $value
	 * @return Client
	 */
	public function setOption(int $option, mixed $value): static
	{
		curl_setopt($this->getCurl(), $option, $value);
		return $this;
	}

	/**
	 * Set an HTTP header for the request.
	 *
	 * @param string $name The name of the header.
	 * @param string $value The value of the header.
	 * @return Client Returns the current instance of the Client for method chaining.
	 */
	public function setHeader(string $name, string $value): Client
	{
		$this->headers[$name] = $value;
		return $this;
	}

	/**
	 * Unset an HTTP header for the request.
	 *
	 * @param string $name
	 * @return void
	 */
	public function unsetHeader(string $name)
	{
		unset($this->headers[$name]);
	}

	/**
	 * @throws Forbidden
	 */
	private function initRequest($path) : void
	{
		$url = $this->baseUri . $path;

		$resolve = $this->checkUri($url);
		if($resolve !== false) {
			$this->setOption(CURLOPT_RESOLVE, [$resolve]);
		}

		$this->lastHeaders = [];
		$this->setOption(CURLOPT_URL, $url)
			->setOption(CURLOPT_RETURNTRANSFER, true)
			->setOption(CURLOPT_HEADERFUNCTION, function ($curl, $header) {
				if (preg_match('/([\w-]+): (.*)/i', $header, $matches)) {
					$this->lastHeaders[strtolower($matches[1])] = trim($matches[2]);
				}
				return strlen($header);
			});

		$headers = $this->getHeadersForCurl();
		if (!empty($headers)) {
			$this->setOption(CURLOPT_HTTPHEADER, $headers);
		}
	}

	/**
	 * When globalRangeOnly is true, only allow URL's that resolve to a global IP address.
	 *
	 * It returns the IP to harden security if an attacker controls DNS somehow.
	 * curl_exec() does its own DNS lookup, an attacker controlling DNS (short TTL, rebinding) can return a
	 * different IP the second time. So we'll use CURLOPT_RESOLVE later to pin this IP.
	 *
	 * @param $uri
	 * @return string|false
	 * @throws Forbidden
	 */
	private function checkUri($uri) : string|false {

		if(!$this->globalRangeOnly) {
			return false;
		}

		$parsed = parse_url($uri);
		$ip = gethostbyname($parsed['host']);
		$retVal = filter_var($ip, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_GLOBAL_RANGE]);

		if($retVal === false) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		$port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);

		return $parsed['host'] . ":" . $port. ":" . $ip;
	}

	/**
	 * Perform GET request
	 *
	 * @return array{status: int, body:string, headers: array, requestHeaders: array, info:array}
	 *  - status: HTTP status code
	 *  - body: HTTP response body
	 *  - headers: HTTP response headers. Key is header name in lower case, value is header value.
	 *  - requestHeaders: HTTP request headers
	 *  - info: cURL info array
	 * @throws CoreException
	 */
	public function get(string $url): array
	{
		$this->initRequest($url);

		$this->setOption(CURLOPT_POST, false);

		$body = curl_exec($this->getCurl());

		$error = curl_error($this->getCurl());
		if (!empty($error)) {
			throw new CoreException($error);
		}

		$info = curl_getinfo($this->getCurl());
		return [
			"requestHeaders" => $this->headers,
			'status' => $info['http_code'],
			'info' => $info,
			'headers' => $this->lastHeaders,
			'body' => $body
		];
	}

	/**
	 * Make POST request with JSON body
	 *
	 * The response body will be JSON decoded automatically.
	 *
	 * @param string $url
	 * @param array $data
	 * @return array{status: int, body:array, headers: array, requestHeaders: array, info:array}
	 * @throws CoreException
	 */
	public function postJson(string $url, array $data): array
	{
		$str = JSON::encode($data);

		$this->setHeader('Content-Type', 'application/json');
		$this->setHeader('Content-Length', strlen($str));
		$this->setHeader('Accept', 'application/json');

		$response = $this->post($url, $str);
		$response['body'] = JSON::decode($response['body'], true);

		$this->unsetHeader("Content-Type");
		$this->unsetHeader("Content-Length");
		$this->unsetHeader("Accept");
		return $response;
	}

	/**
	 * Make a POST request
	 *
	 * @param string $url
	 * @param array|string $data Array of HTTP post fields or string for RAW body.
	 * @return array{status: int, body:string, headers: array, requestHeaders: array, info:array}
	 * @throws CoreException
	 */
	public function post(string $url, $data): array
	{
		if (is_array($data)) {
			$data = array_merge($this->baseParams, $data);
			$this->setOption(CURLOPT_CUSTOMREQUEST, "POST");
		} else {
			$this->setOption(CURLOPT_POST, true);
		}

		$this->request($url, $data);

		$info = curl_getinfo($this->getCurl());

		$this->setOption(CURLOPT_POSTFIELDS, "");

		return [
			"requestHeaders" => $this->headers,
			'status' => $info['http_code'],
			'info' => $info,
			'headers' => $this->lastHeaders,
			'body' => $this->lastBody
		];
	}

	protected function request($path, $data)
	{
		$this->initRequest($path);
		$this->setOption(CURLOPT_POSTFIELDS, $data);

		$body = curl_exec($this->getCurl());
		$this->lastBody = $body;
		$status = curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE);

		$error = curl_error($this->getCurl());
		if (!empty($error)) {
			throw new CoreException($error . ', HTTP Status: ' . $status);
		}
		$this->lastBody = $body;
		return $this;
	}

	public function body() {

		return $this->lastBody;
	}

	public function responseHeaders($name) {
		if($name === null) {
			return $this->lastHeaders;
		}
		return @$this->lastHeaders[$name];
	}

	/**
	 * Download a URL to a file
	 *
	 * Be careful. Don't allow user input here because it can also download file:///etc/passwd for example!
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
		if (!empty($error)) {
			throw new CoreException($error);
		}

		if (isset($this->lastHeaders['content-disposition'])) {
			preg_match('/filename="(.*)"/', $this->lastHeaders['content-disposition'], $matches);
			return [
				"name" => $matches[1] ?? "unknown",
				"type" => $this->lastHeaders['content-type'] ?? "application/octet-stream"
			];
		} else {
			return ["name" => "unknown", "type" => $this->lastHeaders['content-type'] ?? "application/octet-stream"];
		}

	}

	/**
	 * Close the connection
	 *
	 * @deprecated
	 * @return void
	 */
	public function close(): void
	{

	}

	protected function getHeadersForCurl(): array
	{
		$s = [];
		foreach ($this->headers as $key => $value) {
			$s[] = $key . ': ' . $value;
		}

		return $s;
	}
}