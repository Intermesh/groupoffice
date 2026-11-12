<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    public function testIpv4IsCountedPerAddress(): void
    {
        $this->assertSame('203.0.113.7', RateLimiter::ipBucket('203.0.113.7'));
    }

    /**
     * Every address in one /64 lands in the same bucket, so rotating through a
     * customer's own IPv6 prefix does not reset the limit.
     */
    public function testIpv6IsCountedPerSlash64(): void
    {
        $a = RateLimiter::ipBucket('2001:db8:1:2:3:4:5:6');
        $b = RateLimiter::ipBucket('2001:db8:1:2:ffff:ffff:ffff:ffff');
        $this->assertSame($a, $b);
        $this->assertSame('2001:db8:1:2::/64', $a);
        $this->assertNotSame($a, RateLimiter::ipBucket('2001:db8:1:3::1'));
    }
}
