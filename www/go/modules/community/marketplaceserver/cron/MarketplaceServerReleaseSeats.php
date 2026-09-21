<?php

namespace go\modules\community\marketplaceserver\cron;

use go\core\ErrorHandler;
use go\core\model\CronJob;
use go\core\model\CronJobSchedule;
use go\modules\community\marketplaceserver\lib\RateLimiter;
use go\modules\community\marketplaceserver\model\InstanceLog;
use go\modules\community\marketplaceserver\model\Settings;

/**
 * The module's daily housekeeping job. Two independent chores:
 *
 * 1. Free the seats of instances that stopped checking in past the configured
 *    inactivity window, firing InstanceLog::EVENT_SEAT_RELEASED for each so the
 *    release is observable (e.g. to notify the customer) rather than only ever
 *    inferred lazily at the next /license call.
 * 2. Prune the rate-limiter ledger. Every /license, /download, /signature and
 *    /checkout call writes a row there (RateLimiter::hitKey), so on a busy
 *    server it is the fastest-growing table in the module — and the only other
 *    caller of prune() is opportunistic, from /register and /login, which a
 *    closed registration never even reaches.
 *
 * Each chore is caught separately: a failure in one must not skip the other.
 *
 * Enforcement does NOT depend on this cron — the /license endpoint always
 * recomputes seat availability from lastSeenAt within the window — so a skipped
 * run is harmless; it only delays the notification event and the tidy-up of the
 * consumesSeat flag.
 *
 * Cron class names are globally unique in core_cron_job, so this is prefixed with
 * the module name.
 */
class MarketplaceServerReleaseSeats extends CronJob
{
    /**
     * @param \go\core\model\CronJobSchedule $schedule
     * @return void
     */
    public function run(CronJobSchedule $schedule): void
    {
        try {
            $days = Settings::get()->getSeatActivityDays();
            $released = InstanceLog::releaseStaleSeats($days);
            if ($released > 0) {
                go()->debug('marketplaceserver: released ' . $released . ' stale seat(s)');
            }
        } catch (\Throwable $e) {
            ErrorHandler::logException($e);
        }

        try {
            $pruned = RateLimiter::prune(24);
            if ($pruned > 0) {
                go()->debug('marketplaceserver: pruned ' . $pruned . ' rate-limiter row(s)');
            }
        } catch (\Throwable $e) {
            ErrorHandler::logException($e);
        }
    }
}
