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
class Client {

  private CurlHandle|false $curl;

  public array $baseParams = [];

  private array $lastHeaders = [];

	/**
	 * Only allow URL's that resolve to a global IP address, also for redirects.
	 *
	 * Use it for URL's that come from users. See https://www.php.net/manual/en/filter.constants.php#constant.filter-flag-global-range
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
	 * @return false|CurlHandle
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
			$this->setOption( CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS); // Only allow HTTP(s) to prevent unsafe  protocols like file:///
			$this->setOption(CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
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

	public function unsetHeader(string $name) {
		unset($this->headers[$name]);
	}

  private function initRequest($url) {
    $this->lastHeaders = [];
    $this->prepareUrl($url);
  }

	/**
	 * Set the URL and options for one request. Called again for every redirect.
	 *
	 * @throws Forbidden
	 */
  private function prepareUrl(string $url): void
  {
	  $resolve = $this->checkUri($url);
	  if($resolve !== false) {
		  // pin the checked address, so a second DNS lookup by curl can't return another one
		  $this->setOption(CURLOPT_RESOLVE, [$resolve]);
		  // curl doesn't check redirect targets, so they're followed in exec()
		  $this->setOption(CURLOPT_FOLLOWLOCATION, false);
	  }

	  if(isset($this->maxResponseSize)) {
		  $max = $this->maxResponseSize;
		  $this->setOption(CURLOPT_NOPROGRESS, false);
		  $this->setOption(CURLOPT_XFERINFOFUNCTION, function ($curl, $downloadSize, $downloaded) use ($max) {
			  // a non-zero return value aborts the transfer
			  return ($downloadSize > $max || $downloaded > $max) ? 1 : 0;
		  });
	  }

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
	 * It returns the curl "resolve" entry to pin the checked IP address.
	 *
	 * @param string $uri
	 * @return string|false
	 * @throws Forbidden
	 */
	private function checkUri(string $uri) : string|false {

		if(!$this->globalRangeOnly) {
			return false;
		}

		$parsed = parse_url($uri);

		if(empty($parsed['host']) || !in_array(strtolower($parsed['scheme'] ?? ''), ['http', 'https'])) {
			throw new Forbidden("Invalid URI: " . $uri);
		}

		$ips = $this->resolveHost(trim($parsed['host'], '[]'));
		if(empty($ips)) {
			throw new Forbidden("Can't resolve host for URI: " . $uri);
		}
		foreach($ips as $ip) {
			if (!$this->isAllowedAddress($ip)) {
				throw new Forbidden("Invalid URI: " . $uri);
			}
		}

		$port = $parsed['port'] ?? (strtolower($parsed['scheme']) === 'https' ? 443 : 80);

		return $parsed['host'] . ":" . $port. ":" . $ips[0];
	}

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
		return filter_var($ip, FILTER_VALIDATE_IP, ['flags' => FILTER_FLAG_GLOBAL_RANGE]) !== false;
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
	 * @throws CoreException
	 */
  public function get(string $url): array
  {
    $this->initRequest($url);

	  $this->setOption(CURLOPT_POST, false);
		
    $body = $this->exec();
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
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

  	$response =  $this->post($url, $str);
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
  	if(is_array($data)) {
		  $data = array_merge($this->baseParams, $data);
		  $this->setOption(CURLOPT_CUSTOMREQUEST, "POST");
	  } else{
		  $this->setOption(CURLOPT_POST, true);
	  }
    
    $this->initRequest($url);
		$this->setOption(CURLOPT_POSTFIELDS, $data);
		
    $body = curl_exec($this->getCurl());

		$status = curl_getinfo($this->getCurl(), CURLINFO_HTTP_CODE);
		
		$error = curl_error($this->getCurl());
		if(!empty($error)) {
      throw new CoreException($error .', HTTP Status: ' . $status);
    }

	  $info = curl_getinfo($this->getCurl());

	  $this->setOption(CURLOPT_POSTFIELDS, "");

	  return [
			"requestHeaders" => $this->headers,
		  'status' => $info['http_code'],
		  'info' => $info,
      'headers' => $this->lastHeaders,
      'body' => $body
    ];
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
  public function close(): void
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