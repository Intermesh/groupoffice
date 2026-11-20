<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Validates the `hostname` a client asks a license to be bound to. Pure — no GO
 * deps. The client sends its own running host; the server signs a JWT bound to
 * exactly that host. Without this, a customer could request `hostname=*` (or any
 * wildcard) and receive a signed license that {@see \go\modules\community\marketplace\lib\LicenseVerifier}
 * treats as valid on EVERY host — defeating per-instance binding and enabling
 * unlimited redistribution of one paid entitlement.
 *
 * A bound hostname must therefore be a single, concrete host name (optionally
 * with a port). Wildcards, comma lists, whitespace and empty values are rejected
 * so the signed `hostname` claim can never widen beyond one instance.
 */
class HostnameValidator
{
    /**
     * @param string $hostname the client-supplied host to bind the license to
     * @return bool true when it is a single concrete hostname safe to sign
     */
    public static function isValid(string $hostname): bool
    {
        $hostname = trim($hostname);
        if ($hostname === '' || strlen($hostname) > 253) {
            return false;
        }
        // A single concrete host: one or more DNS labels (letters/digits/hyphen,
        // not hyphen-bounded), optional :port. No '*', no comma, no whitespace —
        // those are exactly the shapes that would widen the binding.
        return (bool) preg_match(
            '/^(?=.{1,253}(?::\d{1,5})?$)'
            . '[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?'
            . '(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*'
            . '(?::\d{1,5})?$/i',
            $hostname
        );
    }
}
