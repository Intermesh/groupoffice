<?php

namespace go\modules\community\webpush\model;

class Vapid {

	public static function createVapidKeys(): array
	{
		$key = openssl_pkey_new([
			'curve_name'       => 'prime256v1',
			'private_key_type' => OPENSSL_KEYTYPE_EC,
		]);
		if (!$key) {
			throw new \ErrorException('Failed to generate EC key pair.');
		}

		$details = openssl_pkey_get_details($key);
		if (!$details) {
			throw new \ErrorException('Failed to extract key details.');
		}

		// Uncompressed point: 0x04 || x (32 bytes) || y (32 bytes)
		$publicKey = "\x04"
			. str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)
			. str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

		$privateKey = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);

		return [
			'pub'  => Encryption::base64UrlEncode($publicKey), // always used like this
			'priv' => base64_encode($privateKey), // needs decoding for jwt
		];
	}

}