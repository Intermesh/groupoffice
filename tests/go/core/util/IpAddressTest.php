<?php
namespace go\core\util;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IpAddressTest extends TestCase
{
	public static function matching(): array
	{
		return [
			['10.1.2.3', '10.0.0.0/8'],
			['10.255.255.255', '10.0.0.0/8'],
			['172.16.5.5', '172.16.0.0/12'],
			['172.31.255.255', '172.16.0.0/12'],
			['192.168.1.77', '192.168.1.77'],
			['192.168.1.77', '192.168.1.77/32'],
			['192.168.1.200', '192.168.1.128/25'],
			['203.0.113.9', '0.0.0.0/0'],
			['2001:db8::1', '2001:db8::/32'],
			['2001:db8:ffff::1', '2001:db8::/32'],
			['::1', '::1'],
			['fd12:3456::1', 'fc00::/7'],
			// IPv4 mapped IPv6 addresses are IPv4
			['::ffff:10.1.2.3', '10.0.0.0/8'],
			['10.1.2.3', '::ffff:10.1.2.3'],
			[' 10.1.2.3 ', ' 10.0.0.0/8 '],
		];
	}

	#[DataProvider('matching')]
	public function testMatches(string $ip, string $range)
	{
		$this->assertTrue(IpAddress::matches($ip, $range), "$ip should be in $range");
	}

	public static function notMatching(): array
	{
		return [
			['11.0.0.1', '10.0.0.0/8'],
			['172.32.0.1', '172.16.0.0/12'],
			['172.15.255.255', '172.16.0.0/12'],
			['192.168.1.78', '192.168.1.77'],
			['192.168.1.127', '192.168.1.128/25'],
			['2001:db9::1', '2001:db8::/32'],
			['10.1.2.3', '2001:db8::/32'],
			['2001:db8::1', '10.0.0.0/8'],
			// invalid input never matches
			['not an ip', '10.0.0.0/8'],
			['10.1.2.3', 'not a range'],
			['10.1.2.3', '10.0.0.0/33'],
			['10.1.2.3', '10.0.0.0/-1'],
			['10.1.2.3', '10.0.0.0/abc'],
			['10.1.2.3', ''],
			['', '10.0.0.0/8'],
		];
	}

	#[DataProvider('notMatching')]
	public function testDoesNotMatch(string $ip, string $range)
	{
		$this->assertFalse(IpAddress::matches($ip, $range), "$ip should not be in $range");
	}

	public function testInRanges()
	{
		$this->assertTrue(IpAddress::inRanges('192.168.1.5', ['10.0.0.0/8', '192.168.0.0/16']));
		$this->assertFalse(IpAddress::inRanges('8.8.8.8', ['10.0.0.0/8', '192.168.0.0/16']));
		$this->assertFalse(IpAddress::inRanges('8.8.8.8', []));
	}
}
