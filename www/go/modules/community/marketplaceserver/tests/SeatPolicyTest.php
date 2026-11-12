<?php

namespace go\modules\community\marketplaceserver\tests;

use go\modules\community\marketplaceserver\lib\SeatPolicy;
use PHPUnit\Framework\TestCase;

final class SeatPolicyTest extends TestCase
{
    public function testUnlimitedAlwaysAllows(): void
    {
        $this->assertTrue(SeatPolicy::allows(999, false, 0));
        $this->assertTrue(SeatPolicy::allows(999, false, -1));
    }

    public function testExistingSeatHolderAlwaysRenews(): void
    {
        // Already holds a seat → allowed even when others already fill the limit.
        $this->assertTrue(SeatPolicy::allows(5, true, 1));
    }

    public function testNewHostUnderLimitAllowed(): void
    {
        $this->assertTrue(SeatPolicy::allows(0, false, 1));
        $this->assertTrue(SeatPolicy::allows(2, false, 3));
    }

    public function testNewHostAtOrOverLimitRefused(): void
    {
        $this->assertFalse(SeatPolicy::allows(1, false, 1));
        $this->assertFalse(SeatPolicy::allows(3, false, 3));
        $this->assertFalse(SeatPolicy::allows(5, false, 3));
    }
}
