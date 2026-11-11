<?php

namespace go\core\http;

use CurlHandle;
use Exception as CoreException;
use go\core\exception\Forbidden;
use go\core\fs\File;
use go\core\util\IpAddress;
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
	 * Maximum number of redirects that are followed when {@see $globalRangeOnly} is true.
	 *
	 * Curl can't check the address of every redirect, so the client follows them itself in that case.
	 */
	public int $maxRedirects = 5;

	/**
	 * Abort the request when the response body is larger than this number of bytes. Null for no limit.
	 */
	public ?int $maxResponseSize = null;

	/**
	 * The IP address checked by {@see checkUri()} for the current request
	 */
	private ?string $connectedIp = null;

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
		$this->lastHeaders = [];
		$this->prepareUrl($this->baseUri . $path);
	}

	/**
	 * Set the URL and options for one request. Called again for every redirect.
	 *
	 * @throws Forbidden
	 */
	private function prepareUrl(string $url) : void
	{
		$connectTo = $this->checkUri($url);
		if($connectTo !== false) {
			// Connect to the checked address whatever host name curl reads from the URL
			$this->setOption(CURLOPT_CONNECT_TO, [$connectTo]);
			// curl doesn't check redirect targets, so they're followed in exec()
			$this->setOption(CURLOPT_FOLLOWLOCATION, false);
		}

		if(isset($this->maxResponseSize)) {
			$max = $this->maxResponseSize;
			$this->setOption(CURLOPT_NOPROGRESS, false)
				->setOption(CURLOPT_XFERINFOFUNCTION, function ($curl, $downloadSize, $downloaded) use ($max) {
					// a non-zero return value aborts the transfer
					return ($downloadSize > $max || $downloaded > $max) ? 1 : 0;
				});
		}

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
	 * Run the prepared request.
	 *
	 * When {@see $globalRangeOnly} is true, redirects are followed here and every new location is checked like the first
	 * URL. Otherwise curl follows them.
	 *
	 * @param callable|null $onRedirect Called after preparing the request for a redirect location, for example to empty an output file
	 * @return string|bool The body or true when the body is written to a file
	 * @throws CoreException
	 */
	private function exec(?callable $onRedirect = null): string|bool
	{
		$redirects = 0;
		while(true) {
			$body = curl_exec($this->getCurl());

			if(!$this->globalRangeOnly || curl_errno($this->getCurl())) {
				return $body;
			}

			$this->checkConnectedIp();

			$status = curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE);
			$location = curl_getinfo($this->getCurl(), CURLINFO_REDIRECT_URL);
			if($status < 300 || $status >= 400 || empty($location)) {
				return $body;
			}

			if(++$redirects > $this->maxRedirects) {
				throw new CoreException("Too many redirects");
			}

			$this->prepareUrl($location);
			if(isset($onRedirect)) {
				$onRedirect();
			}
		}
	}

	/**
	 * When globalRangeOnly is true, only allow URL's that resolve to a global IP address.
	 *
	 * It returns a CURLOPT_CONNECT_TO entry for the checked IP to harden security if an attacker controls DNS somehow.
	 * curl_exec() does its own DNS lookup, an attacker controlling DNS (short TTL, rebinding) can return a
	 * different IP the second time. curl may also read another host from the URL than parse_url() does, for example
	 * when it's percent encoded. The entry makes curl connect to the checked IP for any host name.
	 *
	 * @param string $uri
	 * @return string|false
	 * @throws Forbidden
	 */
	private function checkUri(string $uri) : string|false {

		$this->connectedIp = null;

		if(!$this->globalRangeOnly) {
			return false;
		}

		// Control characters, white space and backslashes are parsed differently by parse_url() and curl
		if(preg_match('/[\x00-\x20\x7f\\\\]/', $uri)) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		$parsed = parse_url($uri);

		if(empty($parsed['host']) || !in_array(strtolower($parsed['scheme'] ?? ''), ['http', 'https'])) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		$host = $this->normalizeHost($parsed['host']);
		if($host === null) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		// check if the IP
		$ips = $this->resolveHost($host);
		if(empty($ips)) {
			throw new Forbidden("Can't resolve host for URI: " . $uri);
		}
		$firstIP = $ips[0];
		foreach($ips as $ip) {
			if (!$this->isAllowedAddress($ip)) {
				throw new Forbidden("Invalid URI: " . $uri);
			}
		}

		$port = $parsed['port'] ?? (strtolower($parsed['scheme']) === 'https' ? 443 : 80);
		if($port < 1 || $port > 65535) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		$this->connectedIp = $firstIP;

		return "::" . (str_contains($firstIP, ':') ? "[" . $firstIP . "]" : $firstIP) . ":" . $port;
	}

	/**
	 * Get the host name or IP address the way curl will use it.
	 *
	 * curl decodes percent encoding and reads numbers like "2130706433", "0x7f.1" or "127.1" as IPv4 addresses. Only
	 * plain host names, dotted IPv4 addresses and IPv6 addresses in brackets are accepted.
	 *
	 * @param string $host The host from parse_url()
	 * @return string|null The host name in lower case or IP address. Null if it's not valid.
	 */
	private function normalizeHost(string $host): ?string
	{
		$host = strtolower(rawurldecode($host));

		if(str_starts_with($host, '[') && str_ends_with($host, ']')) {
			$ip = substr($host, 1, -1);
			return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false ? $ip : null;
		}

		if(filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
			return $host;
		}

		if(preg_match('/[^\x21-\x7e]/', $host)) {
			if(!function_exists('idn_to_ascii')) {
				return null;
			}
			$host = idn_to_ascii($host);
			if($host === false) {
				return null;
			}
		}

		$host = rtrim($host, '.');
		$label = '[a-z0-9_]([a-z0-9_-]{0,61}[a-z0-9_])?';
		if(!preg_match('/^' . $label . '(\.' . $label . ')*$/', $host)) {
			return null;
		}

		// A numeric last label makes curl read the host as an IPv4 address
		$lastLabel = substr(strrchr('.' . $host, '.'), 1);
		if(preg_match('/^(0x[0-9a-f]*|[0-9]+)$/', $lastLabel)) {
			return null;
		}

		return $host;
	}

	/**
	 * Check that curl connected to the IP address that was checked by {@see checkUri()}
	 *
	 * @throws Forbidden
	 */
	private function checkConnectedIp(): void
	{
		if(!isset($this->connectedIp)) {
			return;
		}
		$primaryIp = curl_getinfo($this->getCurl(), CURLINFO_PRIMARY_IP);
		if(!empty($primaryIp) && !IpAddress::matches($primaryIp, $this->connectedIp)) {
			throw new Forbidden("Connected to an address that wasn't checked");
		}
	}

	/**
	 * Address ranges that are refused besides the ones that aren't in the global range of filter_var().
	 *
	 * NAT64 addresses can reach any IPv4 address, including private ones, through a NAT64 gateway.
	 */
	private const BLOCKED_RANGES = ['64:ff9b::/96', '64:ff9b:1::/48'];

	/**
	 * Check if requests to this IP address are allowed when {@see $globalRangeOnly} is true.
	 *
	 * Private, loopback, link-local (like cloud metadata services) and reserved addresses are refused.
	 *
	 * @param string $ip
	 * @return bool
	 */
	protected function isAllowedAddress(string $ip): bool
	{
		return filter_var($ip, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_GLOBAL_RANGE]) !== false
			&& !IpAddress::inRanges($ip, self::BLOCKED_RANGES);
	}

	/**
	 * Get the IP addresses of a host name
	 *
	 * @param string $host
	 * @return string[]
	 */
	protected function resolveHost(string $host): array {
		if(filter_var($host, FILTER_VALIDATE_IP)) {
			return [$host];
		}
		$records = dns_get_record($host, DNS_A + DNS_AAAA);
		if ($records === false) {
			return [];
		}
		return array_map(fn($r) => $r['type'] === 'AAAA' ? $r['ipv6'] : $r['ip'], $records);
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

		$body = $this->exec();

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
		$this->checkConnectedIp();
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

		try {
			// Don't keep the body of a redirect response in the file
			$this->exec(function () use ($fp) {
				ftruncate($fp, 0);
				rewind($fp);
				$this->setOption(CURLOPT_FILE, $fp);
			});
		} finally {
			fclose($fp);
		}

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