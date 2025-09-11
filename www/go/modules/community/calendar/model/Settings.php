<?php

namespace go\modules\community\calendar\model;

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

	private static function createJwtToken($appId, $room, $secret): string {
		$jwt=[
			self::encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])), //header
			self::encode(json_encode(['aud' => $appId, 'iss' => $appId, 'room' => $room, 'exp' => time() + 3600, 'context' => [
				'user' => [
					'name' => 'groupoffice',
				],
			]])) //payload
		];
		$jwt[] = self::encode(hash_hmac('sha256', implode('.',$jwt), $secret, true)); // sign
		return implode('.',$jwt);
	}

	static function generateRoom(): string {
		$settings = self::get();
		$room =  bin2hex(random_bytes(5));
		return $room . ($settings->videoJwtEnabled ? '?jwt=' .self::createJwtToken($settings->videoJwtAppId, $room, $settings->videoJwtSecret) :'');
	}

	public function save(): bool
	{
		$this->videoUri = rtrim($this->videoUri, '/') . '/';
		return parent::save();
	}
}