<?php

namespace go\core\http;

use CurlHandle;
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
class Client
{

	protected CurlHandle|false $curl;

	public $baseUri;
	protected $lastBody;
	public array $baseParams = [];

	private array $lastHeaders = [];
	protected $headers = [];

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
				->setOption(CURLOPT_USERAGENT, "Group-Office HttpClient " . go()->getVersion() . " (curl)")
				->setOption(CURLOPT_CONNECTTIMEOUT, 5)
				->setOption(CURLOPT_TIMEOUT, 360);
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
	public function setOption(int $option, $value): static
	{
		curl_setopt($this->getCurl(), $option, $value);
		return $this;
	}

	public function setHeader(string $name, string $value): Client
	{
		$this->headers[$name] = $value;
		return $this;
	}

	public function unsetHeader(string $name)
	{
		unset($this->headers[$name]);
	}

	private function initRequest($path)
	{
		$this->lastHeaders = [];
		$this->setOption(CURLOPT_URL, $this->baseUri.$path)
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
	 * Perform GET request
	 *
	 * @return array{status: int, body:string, headers: array, requestHeaders: array, info:array}
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
	 * POST JSON body
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
	 * @return void
	 */
	public function close(): void
	{
		curl_close($this->curl);
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