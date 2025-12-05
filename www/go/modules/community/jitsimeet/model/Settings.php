<?php

namespace go\modules\community\jitsimeet\model;

use go\core;

class Settings extends core\Settings
{
	public string $videoUri = 'https://meet.jit.si/';

	public bool $videoJwtEnabled = false;

	protected string $videoJwtSecret = '';

	public function setVideoJwtSecret(string $secret): void
	{
		$this->videoJwtSecret = $secret;
	}

	public function getVideoJwtSecret() {
		return null;
	}

	public string $videoJwtAppId = '';

	private static function encode($data) {
		return strtr(base64_encode($data), ['+'=>'-', '/'=>'_', '='=>'']);
	}

	public function createJwtToken( string $room): string {
		$jwt=[
			self::encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])), //header
			self::encode(json_encode(['aud' => $this->videoJwtAppId, 'iss' => $this->videoJwtAppId, 'room' => $room, 'exp' => strtotime('+30 days')])) //payload
		];
		$jwt[] = self::encode(hash_hmac('sha256', implode('.',$jwt), $this->videoJwtSecret, true)); // sign
		return implode('.',$jwt);
	}



	public function save(): bool
	{
		$this->videoUri = rtrim($this->videoUri, '/') . '/';
		return parent::save();
	}
}