<?php

namespace go\api;

use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
	private $url = 'http://host.docker.internal:6680/api/page.php'; // TODO: Get from URL? This is

	/**
	 * There's some bad stuff in the URL
	 *
	 * @return void
	 */
	public function testXss()
	{
		$badUrl = rawurlencode("<img src=1 onerror=alert(document.domain)>");
		$testUrls = [
			'',
			'core/',
			'community/test/'
		];
		foreach($testUrls as $testUrl) {
			$cmd  = "curl -s -X GET ". $this->url . '/' . $testUrl . $badUrl;

			$data = shell_exec($cmd);
			$this->assertEquals("Bad request, only alpha numeric _/ characters are allowed in the path.", $data);
		}
	}

	/**
	 * The module is there, the controller method is not...
	 *
	 * @return void
	 */
	public function testPageNotFound()
	{
		$testUrls = ['community/test/B/'];
		foreach($testUrls as $testUrl) {
			$cmd  = "curl -s -X GET ". $this->url . '/' . $testUrl;

			$data = shell_exec($cmd);
			$this->assertEquals("method 'pageB' not found in 'go\modules\community\\test\Module'", $data);
		}
	}
}