<?php

namespace go\modules\community\marketplaceserver\lib\payment;

/**
 * Verifies a Stripe webhook signature (the `Stripe-Signature` header) against the
 * raw request body and the endpoint's signing secret. Pure — no GO/HTTP deps, so
 * it unit-tests without a network or a database.
 *
 * Stripe signs `"{t}.{payload}"` with HMAC-SHA256 keyed by the endpoint secret and
 * sends `t=<unix>,v1=<hexmac>[,v1=<hexmac>...]`. We recompute the MAC, compare in
 * constant time against every provided v1, and reject a timestamp outside the
 * tolerance window to blunt replay. See https://stripe.com/docs/webhooks/signatures.
 */
class StripeSignature
{
    /** Max age (seconds) of the signed timestamp we still accept. */
    const DEFAULT_TOLERANCE = 300;

    /**
     * @param string $payload the exact raw request body
     * @param string|null $header the Stripe-Signature header value
     * @param string $secret the endpoint signing secret (whsec_...)
     * @param int $now current unix time (injectable for tests)
     * @param int $tolerance max accepted age of the signed timestamp in seconds
     * @return bool true only when a v1 signature matches and the timestamp is fresh
     */
    public static function verify(
        string $payload,
        ?string $header,
        string $secret,
        int $now,
        int $tolerance = self::DEFAULT_TOLERANCE
    ): bool {
        if ($header === null || $header === '' || $secret === '') {
            return false;
        }
        [$timestamp, $signatures] = self::parseHeader($header);
        if ($timestamp === null || empty($signatures)) {
            return false;
        }
        // Replay guard: reject a timestamp too far from now (past OR future).
        if (abs($now - $timestamp) > $tolerance) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        foreach ($signatures as $candidate) {
            if (hash_equals($expected, $candidate)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Parse `t=...,v1=...,v1=...` into [int timestamp|null, string[] v1 signatures].
     *
     * @param string $header
     * @return array{0: int|null, 1: array<string>}
     */
    private static function parseHeader(string $header): array
    {
        $timestamp = null;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            [$k, $v] = $kv;
            if ($k === 't' && ctype_digit($v)) {
                $timestamp = (int) $v;
            } elseif ($k === 'v1' && $v !== '') {
                $signatures[] = $v;
            }
        }
        return [$timestamp, $signatures];
    }
}
