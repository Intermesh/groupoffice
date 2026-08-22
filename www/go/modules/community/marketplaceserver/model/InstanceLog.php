<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Mapping;

/**
 * Records the last time a customer's GO instance (identified by hostname)
 * checked in against the page API. Manager-only lookup for support/telemetry
 * — instances themselves write to this via the page API, not JMAP.
 */
class InstanceLog extends Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $customerId;

    /**
     * @var string
     */
    public $hostname;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $lastSeenAt;

    /**
     * Whether this instance currently holds a SEAT (received at least one
     * seat-mode license on its last check-in). Only seat-holders count toward the
     * customer's seat limit; hostname-only instances are logged for telemetry but
     * do not consume a seat.
     *
     * @var bool
     */
    public $consumesSeat = false;

    /**
     * Fired once per instance whose seat is actively released after the
     * inactivity window (by {@see releaseStaleSeats()}, driven by the daily
     * MarketplaceServerReleaseSeats cron). Listeners get ($customerId, $hostname)
     * and can react — e.g. notify the customer their seat was freed. Purely a
     * notification hook: enforcement does NOT depend on it (the /license path
     * still computes seat availability from lastSeenAt within the window), so a
     * missed cron run never grants or withholds a seat incorrectly.
     */
    const EVENT_SEAT_RELEASED = 'seatreleased';

    public static function getClientName(): string
    {
        return 'MarketplaceServerInstanceLog';
    }

    /**
     * Clear the seat flag on every instance that has not checked in within the
     * activity window, firing {@see EVENT_SEAT_RELEASED} for each. Idempotent:
     * a row already at consumesSeat=0 is skipped, so re-running never re-fires.
     * This makes seat release an explicit, observable event on a schedule rather
     * than a side effect only ever computed lazily at the next /license call.
     *
     * @param int $activityDays the inactivity window in days
     * @return int number of seats released
     * @throws \Exception
     */
    public static function releaseStaleSeats(int $activityDays): int
    {
        $cutoff = (new \DateTime())->sub(new \DateInterval('P' . max(1, $activityDays) . 'D'));
        $released = 0;
        $stale = self::find()
            ->where('consumesSeat', '=', true)
            ->andWhere('lastSeenAt', '<', $cutoff);
        foreach ($stale as $log) {
            $log->consumesSeat = false;
            if ($log->save()) {
                $released++;
                self::fireEvent(self::EVENT_SEAT_RELEASED, (int) $log->customerId, (string) $log->hostname);
            }
        }
        return $released;
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_instance_log', 'il');
    }

    /**
     * Manager-only — instances write here via the page API (system path),
     * not JMAP.
     *
     * @return int
     * @throws \Exception
     */
    public function getPermissionLevel(): int
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if (!$module) {
            return 0;
        }
        return !empty($module->getUserRights()->mayManage) ? Acl::LEVEL_MANAGE : 0;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function canCreate(): bool
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        return $module && !empty($module->getUserRights()->mayManage);
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['il.hostname'];
    }
}
