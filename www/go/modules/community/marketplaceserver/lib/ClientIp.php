<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Resolves the real client IP for rate-limiting the public auth endpoints. Pure
 * — no GO deps.
 *
 * By default the transport IP (REMOTE_ADDR) is used verbatim: the attacker
 * controls X-Forwarded-For, so trusting it blindly lets a caller forge a fresh
 * IP per request and defeat the per-IP cap. When the deployment sits behind a
 * known reverse proxy, list its address(es) as trusted; only THEN is the
 * right-most X-Forwarded-For entry that is not itself a trusted proxy taken as
 * the client IP.
 */
class ClientIp
{
    /**
     * @param string $remoteAddr the transport peer ($_SERVER['REMOTE_ADDR'])
     * @param string|null $forwardedFor the X-Forwarded-For header value, if any
     * @param array<string> $trustedProxies exact proxy IPs allowed to set XFF
     * @return string the client IP to rate-limit on
     */
    public static function resolve(string $remoteAddr, ?string $forwardedFor, array $trustedProxies): string
    {
        $remoteAddr = trim($remoteAddr);
        if ($remoteAddr === '') {
            return '0.0.0.0';
        }
        // Direct connection (or REMOTE_ADDR is not one we trust): never believe XFF.
        if (!in_array($remoteAddr, $trustedProxies, true)) {
            return $remoteAddr;
        }
        if ($forwardedFor === null || trim($forwardedFor) === '') {
            return $remoteAddr;
        }
        // REMOTE_ADDR is a trusted proxy: walk XFF right-to-left and return the
        // first entry that is not itself a trusted proxy — that is the closest
        // hop we can no longer vouch for, i.e. the real client.
        $parts = array_map('trim', explode(',', $forwardedFor));
        for ($i = count($parts) - 1; $i >= 0; $i--) {
            if ($parts[$i] === '') {
                continue;
            }
            if (!in_array($parts[$i], $trustedProxies, true)) {
                return $parts[$i];
            }
        }
        return $remoteAddr;
    }
}
