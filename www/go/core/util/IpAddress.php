<?php

namespace go\core\util;

/**
 * IP address helpers
 *
 * Supports IPv4 and IPv6. IPv4-mapped IPv6 addresses (::ffff:192.0.2.1) are treated as IPv4.
 */
class IpAddress
{
	/**
	 * Check if an IP address is in a range
	 *
	 * @param string $ip eg. "192.0.2.10" or "2001:db8::1"
	 * @param string $range A single address or CIDR notation. eg. "10.0.0.1", "10.0.0.0/8" or "2001:db8::/32"
	 */
	public static function matches(string $ip, string $range): bool
	{
		[$network, $bits] = array_pad(explode('/', trim($range), 2), 2, null);

		$ipBin = self::toBinary($ip);
		$networkBin = self::toBinary($network);
		if ($ipBin === null || $networkBin === null || strlen($ipBin) !== strlen($networkBin)) {
			return false;
		}

		$maxBits = strlen($networkBin) * 8;
		if ($bits === null) {
			$bits = $maxBits;
		} else if (ctype_digit($bits) && (int) $bits <= $maxBits) {
			$bits = (int) $bits;
		} else {
			return false;
		}

		$wholeBytes = intdiv($bits, 8);
		if ($wholeBytes > 0 && strncmp($ipBin, $networkBin, $wholeBytes) !== 0) {
			return false;
		}

		$remainingBits = $bits % 8;
		if ($remainingBits === 0) {
			return true;
		}

		$mask = (0xFF << (8 - $remainingBits)) & 0xFF;

		return (ord($ipBin[$wholeBytes]) & $mask) === (ord($networkBin[$wholeBytes]) & $mask);
	}

	/**
	 * Check if an IP address is in any of the ranges
	 *
	 * @param string[] $ranges {@see matches()}
	 */
	public static function inRanges(string $ip, array $ranges): bool
	{
		foreach ($ranges as $range) {
			if (self::matches($ip, (string) $range)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return ?string Binary address: 4 bytes for IPv4, 16 for IPv6. Null if it's not a valid address.
	 */
	private static function toBinary(string $ip): ?string
	{
		$binary = @inet_pton(trim($ip));
		if ($binary === false) {
			return null;
		}

		// IPv4-mapped IPv6 address
		if (strlen($binary) === 16 && strncmp($binary, "\0\0\0\0\0\0\0\0\0\0\xff\xff", 12) === 0) {
			return substr($binary, 12);
		}

		return $binary;
	}
}
