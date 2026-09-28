<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * The one rule for reading an expiry date stored by this module. PURE (no GO
 * deps) so it is unit-testable.
 *
 * An expiry that an admin typed is a DATE: the dialogs use `xtype: 'datefield'`,
 * so the value lands in the database at 00:00 of that day. "Expires 2026-12-31"
 * plainly means the license lives through all of 31 December, so a midnight value
 * counts up to the START OF THE NEXT DAY. Comparing such a value directly against
 * now() kills the grant a whole day before the date the UI shows.
 *
 * A value that carries a time of day did NOT come from a date picker — it is a
 * gateway's own period end (a Stripe subscription) and is exact, so it is used
 * verbatim. Never round that one up: it would hand out a free extra day of a
 * paid subscription on every renewal boundary.
 *
 * {@see \go\modules\community\marketplaceserver\model\Product::isAvailable()}
 * applies the same "inclusive through the end of the day" rule to
 * `availableUntil`, which is always date-only.
 */
class ExpiryDate
{
    /**
     * The moment the grant stops being valid: valid while now < cutoff.
     *
     * @param \DateTimeInterface|null $expiresAt null = perpetual
     * @return int|null unix timestamp, or null when perpetual
     */
    public static function cutoff(?\DateTimeInterface $expiresAt): ?int
    {
        if ($expiresAt === null) {
            return null;
        }
        $ts = $expiresAt->getTimestamp();

        return $expiresAt->format('H:i:s') === '00:00:00' ? $ts + 86400 : $ts;
    }

    /**
     * @param \DateTimeInterface|null $expiresAt
     * @param int|null $now unix timestamp, defaults to time()
     * @return bool true when the grant has run out
     */
    public static function hasLapsed(?\DateTimeInterface $expiresAt, ?int $now = null): bool
    {
        $cutoff = self::cutoff($expiresAt);

        return $cutoff !== null && ($now ?? time()) >= $cutoff;
    }
}
