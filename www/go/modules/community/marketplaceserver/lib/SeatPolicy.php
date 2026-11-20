<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Decides whether a customer's instance may hold a seat, given the seat limit and
 * how many OTHER instances currently hold one. Pure — no GO deps. Seat accounting
 * (which hosts are active, within what window) is the caller's job; this is just
 * the arithmetic so it can be unit-tested in isolation.
 */
class SeatPolicy
{
    /**
     * @param int $activeOtherSeats distinct OTHER hostnames currently holding a
     *   seat within the activity window (excluding the requesting host)
     * @param bool $hostAlreadyHoldsSeat whether the requesting host already holds
     *   a seat within the window (renewals never consume a new seat)
     * @param int $maxInstances the customer's seat limit; 0 (or less) = unlimited
     * @return bool true when the requesting host may hold a seat
     */
    public static function allows(int $activeOtherSeats, bool $hostAlreadyHoldsSeat, int $maxInstances): bool
    {
        if ($maxInstances <= 0) {
            return true;                 // unlimited
        }
        if ($hostAlreadyHoldsSeat) {
            return true;                 // already counted — renewing its own seat
        }
        return $activeOtherSeats < $maxInstances;
    }
}
