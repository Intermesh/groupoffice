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
     * Clock-skew tolerance (seconds) applied ONLY to firebase/php-jwt's
     * informational `iat` guard. The license JWT carries no `exp`/`nbf`, and the
     * real per-module expiry is enforced separately in {@see unexpired()} — so a
     * generous leeway here can never extend a license past its expiry; it only
     * stops a marketplace server whose wall clock runs ahead of this client from
     * making a freshly-issued token look "not yet valid" (a future `iat`), which
     * would otherwise flip every paid module to unlicensed until the clocks align.
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
        $this->hostname = $hostname;
        $this->now = $now ?? time();
        // Widen the iat leeway just for THIS decode, then restore it, so we never
        // change JWT verification tolerance for the rest of GO (OpenID, etc.).
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = max($previousLeeway, self::CLOCK_SKEW_LEEWAY_SECONDS);
        try {
            $this->claims = JWT::decode($jwt, new Key($publicKeyPem, 'RS256'));
        } catch (\Throwable $e) {
            $this->claims = null;   // tampered/garbage → unlicensed
        } finally {
            JWT::$leeway = $previousLeeway;
        }
    }

    /**
     * @throws never
     */
    public function has(string $package, string $module): bool
    {
        if ($this->claims === null) {
            return false;
        }
        if (!isset($this->claims->package) || $this->claims->package !== $package) {
            return false;
        }
        if (!$this->hostAllowed()) {
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

    private function unexpired(object $entry): bool
    {
        $exp = $entry->expiresAt ?? null;
        return $exp === null || $exp >= $this->now;
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
            $allowed = trim($allowed);
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
