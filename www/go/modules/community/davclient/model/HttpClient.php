<?php

namespace go\modules\community\davclient\model;

class HttpClient extends \go\core\http\Client
{

	public $baseUri;
	public $baseHeaders;

	public function __construct($uri, $headers)
	{
		$this->baseHeaders = $headers;
		$this->baseUri = $uri;
	}

	protected function getHeadersForCurl(): array
	{
		$s = $this->baseHeaders;
		foreach($this->headers as $key => $value) {
			$s[] = $key.': ' . $value;
		}

		return $s;
	}

	public function statusCode() {
		if(!empty($this->curl))
			return curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
		return null;
	}

	public function PROPFIND($path = '', $data) {
		$this->setOption(CURLOPT_CUSTOMREQUEST, "PROPFIND");
	}

	public function REPORT($path = '', $data) {
		$this->setOption(CURLOPT_CUSTOMREQUEST, "REPORT");
	}
}