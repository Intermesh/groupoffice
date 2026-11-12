<?php

namespace go\modules\community\marketplace\lib;

/**
 * Verifies a detached RS256/SHA-256 signature over a downloaded package against
 * the repository's pinned public key. Pure — no GO/DB deps, so it unit-tests
 * without a database. The server side that produces the signature is
 * \go\modules\community\marketplaceserver\lib\PackageSigner::sign.
 *
 * The download controller calls this BEFORE extracting the ZIP: only a package
 * signed by the private key that matches the pinned public key is trusted, so a
 * spoofed/compromised server (or broken TLS) cannot deliver arbitrary PHP to be
 * unpacked into go/modules and executed.
 */
class PackageSigner
{
    /**
     * @param string $data raw package bytes as downloaded
     * @param string $signatureB64 base64 signature from the server's /signature endpoint
     * @param string $publicKeyPem the repository's pinned RS256 public key (PEM)
     * @return bool true only when the signature verifies against the public key
     */
    public static function verify(string $data, string $signatureB64, string $publicKeyPem): bool
    {
        if ($signatureB64 === '' || $publicKeyPem === '') {
            return false;
        }
        $signature = base64_decode($signatureB64, true);
        if ($signature === false || $signature === '') {
            return false;
        }
        // openssl_verify returns 1 (valid), 0 (invalid) or -1 (error) — only 1 is trust.
        return openssl_verify($data, $signature, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1;
    }
}
