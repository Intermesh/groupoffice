<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\ExpiryDate;
use PHPUnit\Framework\TestCase;

/**
 * The date-only vs. exact-timestamp expiry rule. Pure — no DB, no GO context.
 */
final class ExpiryDateTest extends TestCase
{
    private function ts(string $s): int
    {
        return (new \DateTime($s))->getTimestamp();
    }

    public function testPerpetualHasNoCutoff(): void
    {
        $this->assertNull(ExpiryDate::cutoff(null));
        $this->assertFalse(ExpiryDate::hasLapsed(null));
    }

    public function testDateOnlyRunsThroughTheEndOfThatDay(): void
    {
        $expires = new \DateTime('2026-12-31 00:00:00');

        $this->assertSame($this->ts('2027-01-01 00:00:00'), ExpiryDate::cutoff($expires));
        // The whole of the last day is still covered — this is the bug the rule fixes.
        $this->assertFalse(ExpiryDate::hasLapsed($expires, $this->ts('2026-12-31 00:00:00')));
        $this->assertFalse(ExpiryDate::hasLapsed($expires, $this->ts('2026-12-31 23:59:59')));
        $this->assertTrue(ExpiryDate::hasLapsed($expires, $this->ts('2027-01-01 00:00:00')));
    }

    public function testExactTimestampIsUsedVerbatim(): void
    {
        // A Stripe period end, not a date picker: rounding it up would give away
        // a free day of a paid subscription on every renewal.
        $expires = new \DateTime('2026-12-31 14:30:00');

        $this->assertSame($expires->getTimestamp(), ExpiryDate::cutoff($expires));
        $this->assertFalse(ExpiryDate::hasLapsed($expires, $this->ts('2026-12-31 14:29:59')));
        $this->assertTrue(ExpiryDate::hasLapsed($expires, $this->ts('2026-12-31 14:30:00')));
    }

    public function testImmutableDatesAreAccepted(): void
    {
        $expires = new \DateTimeImmutable('2026-07-20 00:00:00');

        $this->assertSame($this->ts('2026-07-21 00:00:00'), ExpiryDate::cutoff($expires));
    }
}
