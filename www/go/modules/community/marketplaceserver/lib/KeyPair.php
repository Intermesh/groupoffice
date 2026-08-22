<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * RSA keypair generation for license signing. Pure — no GO dependencies.
 */
class KeyPair
{
    /**
     * Generate a 2048-bit RSA keypair.
     *
     * @return array{private: string, public: string} PEM strings
     * @throws \RuntimeException when OpenSSL fails
     */
    public static function generate(): array
    {
        $res = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);
        if ($res === false || !openssl_pkey_export($res, $privatePem)) {
            throw new \RuntimeException('OpenSSL keypair generation failed: ' . openssl_error_string());
        }
        $details = openssl_pkey_get_details($res);
        if ($details === false || empty($details['key'])) {
            throw new \RuntimeException('OpenSSL public key export failed');
        }

        return ['private' => $privatePem, 'public' => $details['key']];
    }
}
