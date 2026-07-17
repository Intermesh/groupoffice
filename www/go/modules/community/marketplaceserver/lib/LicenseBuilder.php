<?php

namespace go\modules\community\marketplaceserver\lib;

use Firebase\JWT\JWT;

/**
 * Resolves entitlements into the licenses claim map and signs the license JWT.
 * Pure — callers map DB rows to plain arrays.
 */
class LicenseBuilder
{
    /**
     * @param string $package e.g. "sf"
     * @param array<array{type: string, modules: array<string>, expiresAt: int|null, permitted?: bool}> $entitlements
     *   expiresAt as unix timestamp or null = perpetual. Optional `permitted`
     *   (default true) lets the caller apply per-entitlement instance binding
     *   (seat/hostname) for a specific host: a row with `permitted` false
     *   contributes nothing, so its modules stay out of that host's license.
     * @param int|null $now unix timestamp, defaults to time()
     * @return array<string, array{expiresAt: int|null}> e.g. ["sf/chat" => ["expiresAt" => null]]
     */
    public static function resolveLicenses(string $package, array $entitlements, ?int $now = null): array
    {
        $now = $now ?? time();
        $licenses = [];

        foreach ($entitlements as $e) {
            if (array_key_exists('permitted', $e) && !$e['permitted']) {
                continue;
            }
            $exp = $e['expiresAt'];
            if ($exp !== null && $exp < $now) {
                continue;
            }
            $keys = array_map(fn(string $m) => $package . '/' . $m, $e['modules']);

            foreach ($keys as $key) {
                if (!array_key_exists($key, $licenses)) {
                    $licenses[$key] = ['expiresAt' => $exp];
                    continue;
                }
                $current = $licenses[$key]['expiresAt'];
                if ($current !== null && ($exp === null || $exp > $current)) {
                    $licenses[$key]['expiresAt'] = $exp;
                }
            }
        }

        return $licenses;
    }

    /**
     * @param array<string, array{expiresAt: int|null}> $licenses
     * @return string signed RS256 JWT
     */
    public static function build(
        string $issuer,
        int $customerId,
        string $hostname,
        string $package,
        array $licenses,
        string $privateKeyPem,
        ?int $now = null
    ): string {
        return JWT::encode([
            'iss' => $issuer,
            'sub' => $customerId,
            'hostname' => $hostname,
            'package' => $package,
            'iat' => $now ?? time(),
            'licenses' => $licenses,
        ], $privateKeyPem, 'RS256');
    }
}
