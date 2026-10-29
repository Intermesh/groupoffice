<?php
namespace go\core\model;

use go\core\http\Response;

/**
 * Records the cookies instead of sending them
 */
class CookieRecordingResponse extends Response
{
	public array $cookies = [];

	public function setCookie(string $name, string $value, array $options = []): Response
	{
		$this->cookies[] = [$name, $value, $options];

		return $this;
	}
}
