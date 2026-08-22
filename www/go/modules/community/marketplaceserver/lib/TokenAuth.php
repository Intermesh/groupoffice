<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * API token primitives. Plaintext tokens are shown once at creation; only the
 * SHA-256 hash is stored (marketplaceserver_api_token.tokenHash). Pure — no GO dependencies.
 */
class TokenAuth
{
    /**
     * @return string e.g. "marketplaceserver_" + 40 hex chars
     */
    public static function generateToken(): string
    {
        return 'marketplaceserver_' . bin2hex(random_bytes(20));
    }

    public static function hash(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Extract the token from an Authorization header value.
     *
     * @param string|null $header e.g. "Bearer marketplaceserver_..."
     */
    public static function parseBearer(?string $header): ?string
    {
        if ($header === null || !preg_match('/^Bearer\s+(\S+)$/i', trim($header), $m)) {
            return null;
        }
        return $m[1];
    }
}
