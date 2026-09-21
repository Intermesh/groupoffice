<?php

namespace go\modules\community\marketplace\lib;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Offline verification of a marketplace license JWT. Pure — no GO/DB deps, so
 * it unit-tests without a database. Given the cached JWT, the repository's
 * pinned RS256 public key, and the current hostname, decide whether a given
 * package/module is licensed and unexpired.
 */
class LicenseVerifier
{
    /**
     * Clock-skew tolerance (seconds) applied to firebase/php-jwt's own time
     * checks. It stops a marketplace server whose wall clock runs ahead of this
     * client from making a freshly-issued token look "not yet valid" (a future
     * `iat`), which would otherwise flip every paid module to unlicensed until the
     * clocks align. The library applies the same leeway to `exp`, so the token's
     * own expiry is re-checked strictly in {@see tokenUnexpired()}; the leeway can
     * never extend a license.
     */
    private const CLOCK_SKEW_LEEWAY_SECONDS = 86400;

    /**
     * @var object|null decoded claims, or null if signature/decode failed
     */
    private ?object $claims = null;

    private string $hostname;

    private int $now;

    public function __construct(string $jwt, string $publicKeyPem, string $hostname, ?int $now = null)
    {
        $this->hostname = LicenseHost::normalize($hostname);
        $this->now = $now ?? time();
        // Widen the iat leeway just for THIS decode, then restore it, so we never
        // change JWT verification tolerance for the rest of GO (OpenID, etc.).
        // The library's clock is pinned to ours for the same reason.
        $previousLeeway = JWT::$leeway;
        $previousTimestamp = JWT::$timestamp;
        JWT::$leeway = max($previousLeeway, self::CLOCK_SKEW_LEEWAY_SECONDS);
        JWT::$timestamp = $this->now;
        try {
            $this->claims = JWT::decode($jwt, new Key($publicKeyPem, 'RS256'));
        } catch (\Throwable $e) {
            $this->claims = null;   // tampered/garbage → unlicensed
        } finally {
            JWT::$leeway = $previousLeeway;
            JWT::$timestamp = $previousTimestamp;
        }
    }

    /**
     * True when the token itself is usable for $package on this host: a valid
     * signature, the right package, this host, and a token-level `exp` that has
     * not passed. Checked before a freshly fetched token replaces the cached one.
     *
     * @param string $package
     * @return bool
     */
    public function isValidFor(string $package): bool
    {
        return $this->claims !== null
            && isset($this->claims->package) && $this->claims->package === $package
            && $this->tokenUnexpired()
            && $this->hostAllowed();
    }

    /**
     * @throws never
     */
    public function has(string $package, string $module): bool
    {
        if (!$this->isValidFor($package)) {
            return false;
        }
        $licenses = $this->claims->licenses ?? null;
        if (!$licenses) {
            return false;
        }
        $wildcard = $package . '/*';
        $exact = $package . '/' . $module;
        foreach ([$exact, $wildcard] as $key) {
            if (isset($licenses->$key) && $this->unexpired($licenses->$key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * The token must carry an `exp` and it must not have passed. A token without
     * one (issued by a server that predates the expiry) is refused, otherwise it
     * would license its modules forever once the instance stops refreshing.
     *
     * @return bool
     */
    private function tokenUnexpired(): bool
    {
        $exp = $this->claims->exp ?? null;
        return is_int($exp) && $exp >= $this->now;
    }

    /**
     * A grant is live while it has no expiry, or an INTEGER one that has not
     * passed. The int check matters: PHP 8 compares a non-numeric string against
     * an int as strings, so a malformed `expiresAt` would read as "greater than
     * now" and license the module forever. Only a holder of the server's private
     * key could produce one, so this is defence in depth — but it is the same
     * rule tokenUnexpired() already applies to the token's own `exp`.
     *
     * @param object $entry
     * @return bool
     */
    private function unexpired(object $entry): bool
    {
        $exp = $entry->expiresAt ?? null;
        if ($exp === null) {
            return true;
        }
        return is_int($exp) && $exp >= $this->now;
    }

    /**
     * Hostname binding with the same wildcard semantics as
     * business/license::validateHostname (comma list, leading '*').
     */
    private function hostAllowed(): bool
    {
        $licensed = $this->claims->hostname ?? '';
        if ($licensed === '') {
            // Fail CLOSED on an unbound license. The server always signs a single
            // concrete host (HostnameValidator), and this client always knows its
            // own host — so an empty `hostname` claim is never legitimate. Treating
            // it as "valid everywhere" would turn any future bug or alternate
            // code path that signs a host-less JWT into a universal license.
            return false;
        }
        foreach (explode(',', $licensed) as $allowed) {
            $allowed = LicenseHost::normalize($allowed);
            if ($allowed === '') {
                continue;
            }
            if ($allowed[0] === '*') {
                // A wildcard must carry a real dot-anchored suffix (e.g.
                // "*.example.com"). A bare "*" (suffix "") — or "*." with an empty
                // label — would make str_ends_with() match EVERY host, turning one
                // license into a universal one. Require the suffix to start with a
                // dot and name at least one further label. The server also refuses
                // to sign such a hostname (HostnameValidator); this is defence in
                // depth on already-cached JWTs.
                $suffix = substr($allowed, 1);          // ".example.com"
                if (strlen($suffix) > 1 && $suffix[0] === '.'
                    && str_ends_with($this->hostname, $suffix)) {
                    return true;
                }
            } elseif ($allowed === $this->hostname) {
                return true;
            }
        }
        return false;
    }
}
