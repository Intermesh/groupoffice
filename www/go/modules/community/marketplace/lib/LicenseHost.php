<?php

namespace go\modules\community\marketplace\lib;

/**
 * The hostname a license is requested for and checked against. Every path (the
 * web refresh, the cron, and the runtime isLicensed() gate) must agree on it, or
 * a license bound to one name flips to unlicensed when the instance is reached
 * through another (an IP, an alias, a port-mapped docker host).
 */
final class LicenseHost
{
    /**
     * Lowercase, trimmed, without a port or a trailing root dot.
     *
     * @param string $host
     * @return string
     */
    public static function normalize(string $host): string
    {
        $host = strtolower(trim($host));
        if ($host !== '' && $host[0] !== '[') {
            $host = (string) preg_replace('/:\d+$/', '', $host);
        }
        return rtrim($host, '.');
    }

    /**
     * This instance's host, taken from the configured Group-Office URL rather
     * than from the request, so it is the same under the web and the cron.
     *
     * @return string
     */
    public static function current(): string
    {
        $host = parse_url((string) go()->getSettings()->URL, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            $host = \go\core\http\Request::get()->getHost();
        }
        return self::normalize((string) $host);
    }
}
