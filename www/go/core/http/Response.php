<?php

namespace go\core\http;

use DateTime;
use DateTime as CoreDateTime;
use Exception as CoreException;
use go\core\http;
use go\core\jmap\ProblemDetails;
use go\core\jmap\SetError;
use go\core\Singleton;
use go\core\util\StringUtil;
use go\core\util\JSON;
use go\core\webclient\CSP;

/**
 * Response Object
 * 
 * An object with a data array that the API can encode.
 * Currently JSON (default) and XML encoding is supported.
 * 
 * The minimal response will be:
 *
 * (JSON)
 * ```````````````````````````````````````````````````````````````````````````
 * ['success' => true]
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * or
 * 
 * (XML)
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * <message>
 * 	<success type="boolean">true</success>
 * </message>
 * 
 * ```````````````````````````````````````````````````````````````````````````
 * 
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Response extends Singleton{

	protected function __construct()
	{
		parent::__construct();

		$this->sendCorsHeaders();
	}

	/**
	 * HTTP version
	 * 
	 * @var string 
	 */
	public $httpVersion = '1.1';
	
	/**
	 * Key value array with headers
	 * @var array 
	 */
	protected $headers = [];
	
	/**
	 * The modified at header
	 * 
	 * @var CoreDateTime
	 */
	private $modifiedAt;
	
	/**
	 * Enable HTTP caching
	 * 
	 * @var boolean 
	 */
	public $enableCache = true;


	/**
	 * Bitmask for JSON encoding options
	 *
	 * @var int
	 * @see https://www.php.net/manual/en/function.json-encode.php
	 */
	public $jsonOptions = 0;

	/**
	 * Set the Content-Type header
	 * @param string $contentType eg. "text/html"
	 * @return $this
	 */
	public function setContentType(string $contentType) : self {
		return $this->setHeader('Content-Type', $contentType);
	}

	private $cspNonce;

	/**
	 * @throws CoreException
	 */
	public function getCspNonce(): string
	{
		if(!isset($this->cspNonce)) {
			$this->cspNonce = hash("sha256", random_bytes(16));
		}

		return $this->cspNonce;
	}

	/**
	 * Updates a HTTP header.
	 *
	 * The case-sensitity of the name value must be retained as-is.
	 *
	 * If the header already existed, it will be overwritten.
	 *
	 * @param string $name
	 * @param string|StringUtil[] $value
	 * @return $this
	 */
	public function setHeader(string $name, $value): self
	{
		$lcname = strtolower($name);

		if (!is_array($value)) {
			$value = [$value];
		}

		$this->headers[$lcname] = [$name, $value];	
		
		return $this;
	}

	/**
	 * Remove an HTTP header
	 * 
	 * @param string $name
	 * @return $this;
	 */
	public function removeHeader(string $name): self
	{

		$name = strtolower($name);

		if (!headers_sent()) {
			header_remove($name);
		}

		unset($this->headers[$name]);
		return $this;
	}

	/**
	 * Get the header value
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function getHeader(string $name) {
		$name = strtolower($name);

		if (!isset($this->headers[$name])) {
			return null;
		} else {
			return $this->headers[$name][1];
		}
	}

	/**
	 * Check if header is set
	 *
	 * @param $name
	 * @return bool
	 */
	public function hasHeader($name): bool
	{
		$name = strtolower($name);

		return isset($this->headers[$name]);
	}

	/**
	 * Set HTTP status header
	 *
	 * @param int $httpCode
	 * @param string|null $text Status text. May not contain new lines in headers.
	 */
	public function setStatus(int $httpCode, string $text = null): Response
	{
		if (!isset($text)) {
			$text = http\Exception::$codes[$httpCode];
		}

		if (substr(PHP_SAPI, 0, 3) == 'cgi') {
			$status = "Status: " . $httpCode . " " . $text;
		} else
		{
			$protocol = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/' . $this->httpVersion;
			$status = $protocol . " " . $httpCode . " " . $text;
		}
		header($status);

		return $this;
	}

	/**
	 * Redirect to another URL
	 * 
	 * @param string $url
	 */
	public function redirect(string $url) {
		$this->setHeader('location', $url);
		exit();
	}

	/**
	 * Set Modified At header and enable HTTP caching
	 * @param DateTime $modifiedAt
	 * @return Response
	 */
	public function setModifiedAt(DateTime $modifiedAt): Response
	{
		$this->modifiedAt = $modifiedAt;
		return $this->setHeader('Last-Modified', $this->modifiedAt->format('D, d M Y H:i:s') . ' GMT');
	}

	private $etag;

	/**
	 * Set ETag header and enable HTTP caching
	 *
	 * @param string $etag
	 * @return self
	 */
	public function setETag(string $etag): self
	{
		$this->etag = $etag;
		return $this->setHeader('ETag', $this->etag);
	}

	/**
	 * Set tyhe Expires header
	 *
	 * @param CoreDateTime|null $expires
	 * @return self
	 */
	public function setExpires(DateTime $expires = null): self
	{
		return $this->setHeader("Expires", $expires->format('D, d M Y H:i:s'));
	}

	/**
	 * Set cookie
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $options eg.
	 *
	 * ```
	 * [
	 * 'expires' => $this->expiresAt->format("U"),
	 * "path" => "/",
	 * "samesite" => "Lax",
	 * "domain" => Request::get()->getHost(),
	 * "httpOnly" => true
	 * ]
	 * ```
	 *
	 */
	public function setCookie(string $name, string $value, array $options = []): self
	{

		if(version_compare(phpversion(), "7.3.0") > -1) {
			setcookie($name, $value, $options);
		} else{
			if(!isset($options['path'])) {
				$options['path'] = "";
			}
			if(isset($options['samesite'])) {
				$options['path'] .= '; samesite=' . $options['samesite'];
			}
			setcookie($name, $value, $options['expires'] ?? 0, $options['path'] ?? "", $options['domain'] ?? "", $options['secure'] ?? false, $options['httponly'] ?? false);

		}

		return $this;

	}

	/**
	 * Check if the client cache is up to date
	 * 
	 * If the If-Modified-Since or If-None-Match headers are sent and they match
	 * a http 304 not modified status will be sent and it will exit.
	 * 
	 * NOTE: this function is disabled when IFW\Debugger is enabled.
	 * 
	 * @return boolean
	 */
	public function isCached(): bool
	{

		if(!$this->enableCache) {
			return false;
		}
		
		//get the HTTP_IF_MODIFIED_SINCE header if set
		$ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? false;
		//get the HTTP_IF_NONE_MATCH header if set (etag: unique file hash)
		$etagHeader = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? trim($_SERVER['HTTP_IF_NONE_MATCH']) : false;

		return (isset($this->modifiedAt) && $ifModifiedSince >= $this->modifiedAt->format('U')) || isset($this->etag) && $etagHeader == $this->etag;
	}

	/**
	 * Stop running if client has up to date cache.
	 */
	public function abortIfCached() {
		if ($this->isCached()) {
			$this->setStatus(304);
			exit();
		}
	}

	protected function sendCorsHeaders() {
		$origins = go()->getSettings()->getCorsAllowOrigin();
		if(!empty($origins) && isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $origins)) {
			$this->setHeader('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
			$this->setHeader('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, DELETE, OPTIONS');
			$this->setHeader('Access-Control-Allow-Headers', 'Origin, Content-Type, Authorization, X-File-Name, X-File-LastModified, X-CSRF-Token, Cookie');
			$this->setHeader('Access-Control-Max-Age', "1728000");
			$this->setHeader('Access-Control-Allow-Credentials', 'true');
		}
	}

	public function sendDocumentSecurityHeaders(): self {
		$frameAncestors = go()->getConfig()['frameAncestors'];

		if(empty($frameAncestors)) {
			$this->setHeader("X-Frame-Options", "SAMEORIGIN");
		}

		$this->setHeader("Content-Security-Policy", Csp::get());
		$this->setHeader("X-Content-Type-Options","nosniff");
		$this->setHeader("Strict-Transport-Security","max-age=31536000");
		$this->setHeader("X-XSS-Protection", "1;mode=block");
		$this->setHeader('X-Robots-Tag', 'noindex');
		$this->setHeader('Referrer-Policy', 'same-origin');


		return $this;
	}

	/**
	 * Send the headers to output
	 * @return self
	 */
	public function sendHeaders() : self {
		foreach ($this->headers as $h) {
			foreach ($h[1] as $v) {				// go()->debug($h[0] . ': '. $v);
				header($h[0] . ': ' . $v);
			}
		}
		return $this;
	}

	/**
	 * Output headers and body
	 * 
	 * @param string|array|null $data
	 */
	public function output($data = null) {
		if (isset($data)) {
			if(is_array($data)) {
				if (!$this->getHeader('content-type')) {
					$this->setContentType('application/json; charset=UTF-8');
				}

				try {
					$data = JSON::encode($data, $this->jsonOptions);

				} catch(\Throwable $e) {
					$error = new ProblemDetails(SetError::ERROR_SERVER_FAIL, 500, $e->getMessage());
					$data = JSON::encode($error, $this->jsonOptions);
				}
			}
			if(!headers_sent())
				$this->sendHeaders();
			echo $data;
		} else if(!headers_sent()){
			$this->sendHeaders();
		}
	}

}
