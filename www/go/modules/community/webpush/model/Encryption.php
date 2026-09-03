<?php

namespace go\modules\community\webpush\model;

class Encryption {
	public static function encrypt(string $payload, string $userPublicKey, string $userAuthToken): array
	{
		$userPublicKey  = self::base64UrlDecode($userPublicKey);  // 65 bytes: 0x04 || x || y
		$userAuthToken  = self::base64UrlDecode($userAuthToken);  // 16 bytes
		$salt           = random_bytes(16);

		// Generate ephemeral local key pair
		$localKey     = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
		$localDetails = openssl_pkey_get_details($localKey);
		if (!$localDetails || $localDetails['type'] !== OPENSSL_KEYTYPE_EC) {
			throw new \RuntimeException('Failed to generate EC key');
		}
		$localPublicKey = "\x04"
			. str_pad($localDetails['ec']['x'], 32, "\0", STR_PAD_LEFT)
			. str_pad($localDetails['ec']['y'], 32, "\0", STR_PAD_LEFT);

		// ECDH
		$userKeyHandle = openssl_pkey_get_public(self::buildPublicKeyPem($userPublicKey));
		if (!$userKeyHandle) {
			throw new \RuntimeException('Failed to parse user public key: ' . openssl_error_string());
		}
		$sharedSecret = openssl_pkey_derive($userKeyHandle, $localKey);
		if ($sharedSecret === false) {
			throw new \RuntimeException(openssl_error_string());
		}
		$sharedSecret = str_pad($sharedSecret, 32, "\0", STR_PAD_LEFT);

		// RFC 8291 key derivation
		$ikm = self::hkdf($userAuthToken, $sharedSecret, "WebPush: info\0" . $userPublicKey . $localPublicKey, 32);
		$contentEncryptionKey = self::hkdf($salt, $ikm, "Content-Encoding: aes128gcm\0", 16);
		$nonce = self::hkdf($salt, $ikm, "Content-Encoding: nonce\0", 12);

		// Pad payload: minimum 1 byte delimiter (0x02),
		$payload .= "\x02";
		if (strlen($payload) < 3993) { // pad to 3993 for privacy
			$payload = str_pad($payload, 3993, "\0");
		}

		$tag = '';
		$cipherText = openssl_encrypt($payload, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);

		// aes128gcm content coding header: salt || rs (4 bytes) || keylen (1 byte) || localPublicKey
		$header = $salt . pack('N', 4096) . pack('C', strlen($localPublicKey)) . $localPublicKey;

		return [
			'body'    => $header . $cipherText . $tag,
			'headers' => [
				'Content-Encoding: aes128gcm',
				'Content-Type: application/octet-stream',
				'Content-Length: ' . strlen($header . $cipherText . $tag),
			],
		];
	}

	private static function hkdf(string $salt, string $ikm, string $info, int $length): string
	{
		$prk = hash_hmac('sha256', $ikm, $salt, true);
		return substr(hash_hmac('sha256', $info . "\1", $prk, true), 0, $length);
	}

	private static function buildPublicKeyPem(string $publicKey): string
	{
		$oid       = "\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
		$bitString = "\x03" . chr(strlen($publicKey) + 1) . "\0" . $publicKey;
		$spki      = "\x30" . chr(strlen($oid) + strlen($bitString)) . $oid . $bitString;
		return "-----BEGIN PUBLIC KEY-----\n" . base64_encode($spki) . "\n-----END PUBLIC KEY-----\n";
	}

	static function base64UrlDecode(string $data): string
	{
		return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
	}

	static function base64UrlEncode(string $data): string
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}
}