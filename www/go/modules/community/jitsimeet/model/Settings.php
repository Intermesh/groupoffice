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

	// Optional JaaS (8x8) authentication configuration.
	public bool $jaasEnabled = false;
	public string $jaasAppId = '';
	public string $jaasKeyId = '';
	protected string $jaasPrivateKeyPath = '';

	public function setJaasPrivateKeyPath(string $path): void
	{
		$this->jaasPrivateKeyPath = $path;
	}

	public function getJaasPrivateKeyPath()
	{
		return null;
	}



	/**
	 * Generate a JaaS JWT using RS256.
	 *
	 * @param string $room Meeting room identifier
	 * @param bool $moderator Whether the participant is a moderator
	 */
	public function createJaasJwtToken(string $room, bool $moderator = false): string
	{
		if (!$this->jaasEnabled) {
			throw new \RuntimeException('JaaS authentication is not enabled');
		}

		if ($this->jaasAppId === '' || $this->jaasKeyId === '' || $this->jaasPrivateKeyPath === '') {
			throw new \RuntimeException('JaaS configuration is incomplete');
		}

		if (!is_file($this->jaasPrivateKeyPath) || !is_readable($this->jaasPrivateKeyPath)) {
			throw new \RuntimeException('JaaS private key file is not readable');
		}

		$privateKey = file_get_contents($this->jaasPrivateKeyPath);
		if ($privateKey === false) {
			throw new \RuntimeException('Unable to read JaaS private key');
		}

		$now = time();

		$payload = [
			'aud' => 'jitsi',
			'iss' => 'chat',
			'sub' => $this->jaasAppId,
			'room' => $room,
			'iat' => $now,
			'nbf' => $now,
			'exp' => $now + (30 * 24 * 3600),
			'context' => [
				'user' => [
					'moderator' => $moderator ? 'true' : 'false'
				]
			]
		];

		return \Firebase\JWT\JWT::encode(
			$payload,
			$privateKey,
			'RS256',
			$this->jaasAppId . '/' . $this->jaasKeyId
		);
	}

	private static function encode($data) {
		return strtr(base64_encode($data), ['+'=>'-', '/'=>'_', '='=>'']);
	}

	public function createJwtToken( string $room): string {
		$jwt=[
			self::encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'])), //header
			self::encode(json_encode([
				'aud' => $this->videoJwtAppId,
				'iss' => $this->videoJwtAppId,
				'room' => $room,
				'exp' => strtotime('+30 days'),
				'sub' => parse_url($this->videoUri, PHP_URL_HOST)
			])) //payload
		];
		$jwt[] = self::encode(hash_hmac('sha256', implode('.',$jwt), $this->videoJwtSecret, true)); // sign
		return implode('.',$jwt);
	}

	/**
	 * Link to join a new meeting in a random room, with a JWT when the server requires one
	 *
	 * @return string
	 */
	public function createMeetingUri(): string
	{
		$room = core\util\StringUtil::random(8);

		if ($this->jaasEnabled) {
			return $this->videoUri . $this->jaasAppId . '/' . $room
				. '?jwt=' . $this->createJaasJwtToken($room, true);
		}

		$uri = $this->videoUri . $room;
		if ($this->videoJwtEnabled) {
			$uri .= '?jwt=' . $this->createJwtToken($room);
		}
		return $uri;
	}



	public function save(): bool
	{
		$this->videoUri = rtrim($this->videoUri, '/') . '/';
		return parent::save();
	}
}