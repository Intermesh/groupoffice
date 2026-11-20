<?php

namespace go\modules\community\marketplace\tests;

use go\modules\community\marketplace\lib\LicenseHost;
use PHPUnit\Framework\TestCase;

/**
 * Host normalisation. Every path (web refresh, cron, runtime gate) runs the
 * hostname through this, so a disagreement here flips a licensed instance to
 * unlicensed.
 */
final class LicenseHostTest extends TestCase
{
    public function testLowercasesTrimsAndDropsTrailingDot(): void
    {
        $this->assertSame('go.example.com', LicenseHost::normalize('  GO.Example.com.  '));
    }

    public function testDropsPort(): void
    {
        $this->assertSame('go.example.com', LicenseHost::normalize('go.example.com:8443'));
    }

    /**
     * A bracketed IPv6 literal carries colons of its own, so only a port AFTER
     * the closing bracket may be cut - and it must be cut, or the same instance
     * normalises to two different hosts depending on which path asked.
     */
    public function testKeepsIpv6AddressButDropsItsPort(): void
    {
        $this->assertSame('[::1]', LicenseHost::normalize('[::1]:8080'));
        $this->assertSame('[::1]', LicenseHost::normalize('[::1]'));
        $this->assertSame('[2001:db8::1]', LicenseHost::normalize('[2001:DB8::1]:443'));
    }
}
