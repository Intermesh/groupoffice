<?php

namespace go\modules\community\webpush\model;

class JWT {
	static function create(string $audience, string $subject, string $privateKey): string
	{
		$header  = Encryption::base64UrlEncode('{"typ":"JWT","alg":"ES256"}');
		$payload = Encryption::base64UrlEncode(json_encode([
			'aud' => $audience,
			'exp' => time() + 43200, // equal margin of error between 0 and 24h
			'sub' => $subject,
		], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

		$input = "$header.$payload";

		// Import private key
		$pem = self::buildPrivateKeyPem($privateKey);
		$key = openssl_pkey_get_private($pem);
		if (!$key || !openssl_sign($input, $signature, $key, OPENSSL_ALGO_SHA256)) {
			go()->debug(openssl_error_string());
			throw new \RuntimeException('Failed to sign VAPID JWT');
		}

		return "$input.".Encryption::base64UrlEncode(self::derToRaw($signature));
	}

	// Convert DER-encoded ECDSA signature to raw 64-byte r||s
	private static function derToRaw(string $der): string
	{
		// DER: 0x30 <len> 0x02 <rlen> <r> 0x02 <slen> <s>
		$offset = 2; // skip SEQUENCE header
		$offset++; // skip 0x02
		$rLen = ord($der[$offset++]);
		$r    = substr($der, $offset, $rLen);
		$offset += $rLen;
		$offset++; // skip 0x02
		$sLen = ord($der[$offset++]);
		$s    = substr($der, $offset, $sLen);

		// r and s may have a leading 0x00 (DER positive integer padding) — strip it, then pad to 32
		return str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT)
			. str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);
	}


	private static function buildPrivateKeyPem(string $privateKey): string
	{
		$ecPrivKey = "\x30\x25"
			. "\x02\x01\x01"
			. "\x04\x20" . $privateKey;

		$inner = "\x02\x01\x00"                                        // version
			. "\x30\x13"                                             // algorithmIdentifier
			. "\x06\x07\x2a\x86\x48\xce\x3d\x02\x01"               // OID ecPublicKey
			. "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"           // OID prime256v1
			. "\x04\x27" . $ecPrivKey;                              // privateKey octet string

		$der = "\x30" . chr(strlen($inner)) . $inner;

		return "-----BEGIN PRIVATE KEY-----\n"
			. base64_encode($der)
			. "\n-----END PRIVATE KEY-----";
	}

//	private static function buildPrivateKeyPem(string $publicKey, string $privateKey): string
//	{
//		// ECPrivateKey DER (SEC1): SEQUENCE { version=1, privateKey, [0] OID prime256v1, [1] publicKey }
//		$oid = "\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07"; // prime256v1
//		$privDer = "\x02\x01\x01"                      // version = 1
//			. "\x04\x20" . $privateKey                  // private key (32 bytes)
//			. "\xa0\x0a" . $oid                         // [0] curve OID
//			. "\xa1\x44\x03\x42\x00" . $publicKey;      // [1] public key (65 bytes uncompressed)
//
//		$seq = "\x30" . chr(strlen($privDer)) . $privDer;
//
//		return "-----BEGIN EC PRIVATE KEY-----\n"
//			. base64_encode($seq)
//			. "\n-----END EC PRIVATE KEY-----\n";
//	}
}