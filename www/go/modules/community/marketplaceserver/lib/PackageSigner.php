<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Detached RS256/SHA-256 signature over a package's bytes, signed with the same
 * private key that signs licenses. Pure — no GO deps. The client verifies the
 * downloaded ZIP against the repository's pinned public key BEFORE extracting,
 * so a compromised/spoofed server (or broken TLS) cannot deliver arbitrary PHP
 * to be unpacked and executed. Verification lives in the client module
 * (\go\modules\community\marketplace\lib\PackageSigner::verify).
 */
class PackageSigner
{
    const ALGORITHM = 'RS256-SHA256';

    /**
     * @param string $data raw package bytes
     * @param string $privateKeyPem PEM private key
     * @return string base64 signature
     * @throws \RuntimeException when OpenSSL signing fails
     */
    public static function sign(string $data, string $privateKeyPem): string
    {
        $signature = '';
        if (!openssl_sign($data, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('OpenSSL package signing failed: ' . openssl_error_string());
        }
        return base64_encode($signature);
    }
}
