<?php

namespace go\modules\community\davclient\model;

class HttpClient extends \go\core\http\Client
{

	//public $baseHeaders;

	public function __construct($uri, $headers)
	{
		$this->baseUri = $uri;
		$this->headers = $headers;
	}

	protected function getHeadersForCurl(): array
	{
		$s =[];
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

	public function PROPFIND($path, $data) {
		return $this
			->setOption(CURLOPT_CUSTOMREQUEST, "PROPFIND")
			->request($path, '<?xml version="1.0"?>'.$data);
	}

	public function REPORT($path, $data) {
		return $this
			->setOption(CURLOPT_CUSTOMREQUEST, "REPORT")
			->request($path,'<?xml version="1.0"?>'.$data);
	}

	public function PUT($path, $data) {
		return $this
			->setOption(CURLOPT_CUSTOMREQUEST, "PUT")
			->request($path, $data);
	}
}