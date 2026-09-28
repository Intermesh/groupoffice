<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\ClientIp;
use PHPUnit\Framework\TestCase;

final class ClientIpTest extends TestCase
{
    public function testDirectConnectionIgnoresForwardedFor(): void
    {
        // No trusted proxies: XFF is attacker-controlled and must be ignored.
        $this->assertSame('203.0.113.9', ClientIp::resolve('203.0.113.9', '1.2.3.4', []));
        $this->assertSame('203.0.113.9', ClientIp::resolve('203.0.113.9', null, ['10.0.0.1']));
    }

    public function testTrustedProxyUsesClientFromForwardedFor(): void
    {
        $this->assertSame(
            '198.51.100.7',
            ClientIp::resolve('10.0.0.1', '198.51.100.7', ['10.0.0.1'])
        );
    }

    public function testWalksPastChainedTrustedProxies(): void
    {
        // client, then two proxies we trust — the real client is the left-most untrusted.
        $this->assertSame(
            '198.51.100.7',
            ClientIp::resolve('10.0.0.1', '198.51.100.7, 10.0.0.2, 10.0.0.1', ['10.0.0.1', '10.0.0.2'])
        );
    }

    public function testTrustedProxyWithoutForwardedForFallsBack(): void
    {
        $this->assertSame('10.0.0.1', ClientIp::resolve('10.0.0.1', null, ['10.0.0.1']));
        $this->assertSame('10.0.0.1', ClientIp::resolve('10.0.0.1', '', ['10.0.0.1']));
    }

    public function testEmptyRemoteAddr(): void
    {
        $this->assertSame('0.0.0.0', ClientIp::resolve('', 'anything', []));
    }
}
