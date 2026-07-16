<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\ArrayObject;

/**
 * Grants a customer the right to use a product — either created manually by
 * a manager or synced from a Stripe subscription. `expiresAt` null means
 * perpetual (e.g. a one-off purchase); a set date is checked by the license
 * endpoint to decide whether the grant is still active.
 */
class Entitlement extends Entity
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
     * @var int
     */
    public $productId;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $expiresAt;

    /**
     * When set, this grant is REVOKED and contributes nothing from that moment —
     * honoured immediately by every server path (/license, /catalog, /download,
     * /account), independent of expiresAt. This is the kill switch: a manager
     * revokes a grant and the next license refresh on the client (hours, not a
     * day, thanks to the more frequent RefreshLicenses cron) drops the module.
     * The row is kept (not deleted) for audit. Null = active.
     *
     * @var ?\go\core\util\DateTime
     */
    public $revokedAt;

    const SOURCE_MANUAL = 'manual';
    const SOURCE_STRIPE = 'stripe';
    const SOURCE_FREE = 'free';

    /**
     * @var string
     */
    public $source = 'manual';

    const BINDING_SEATS = 'seats';
    const BINDING_HOSTNAME = 'hostname';

    /**
     * How this grant binds to the customer's instances:
     *   - 'seats'    — licensed on any of the customer's active instances up to
     *                  the customer's seat limit (Customer::maxInstances).
     *   - 'hostname' — licensed only on the single host in {@see $boundHostname}
     *                  (pinned on first /license use), independent of seats.
     *
     * @var string
     */
    public $bindingMode = self::BINDING_SEATS;

    /**
     * The host this grant is pinned to in 'hostname' mode. NULL = not yet pinned;
     * the first /license request for a hostname-mode grant pins it (trust on first
     * use). A manager clears it to allow re-pinning to a new host.
     *
     * @var string|null
     */
    public $boundHostname;

    /**
     * @var string|null
     */
    public $stripeSubscriptionId;

    /**
     * The gateway payment-intent id behind a one-off paid grant, so a later refund
     * (charge.refunded) can be linked back to this entitlement and revoke it.
     *
     * @var string|null
     */
    public $stripePaymentIntentId;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $createdAt;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $modifiedAt;

    /**
     * @var int|null
     */
    public $createdBy;

    /**
     * @var int|null
     */
    public $modifiedBy;

    public static function getClientName(): string
    {
        return 'MarketplaceServerEntitlement';
    }

    /**
     * Whether this grant is currently revoked (the kill switch is engaged).
     *
     * @return bool
     */
    public function isRevoked(): bool
    {
        return $this->revokedAt !== null;
    }

    /**
     * Audit MANUAL admin actions (create / revoke / restore via the JMAP UI). The
     * automated grant paths (grantFree / grantPaid / webhook revoke) write directly
     * to the table and never call save(), so they never reach here — there is no
     * double logging; they record their own purchase/refund/download events.
     *
     * @return bool
     */
    protected function internalSave(): bool
    {
        $isNew = $this->isNew();
        $revokedModified = $this->isModified(['revokedAt']);
        $nowRevoked = $this->revokedAt !== null;

        if (!parent::internalSave()) {
            return false;
        }

        if ($isNew) {
            Activity::record(Activity::TYPE_GRANT, [
                'customerId' => (int) $this->customerId,
                'productId' => (int) $this->productId,
            ]);
        } elseif ($revokedModified) {
            Activity::record($nowRevoked ? Activity::TYPE_REVOKE : Activity::TYPE_RESTORE, [
                'customerId' => (int) $this->customerId,
                'productId' => (int) $this->productId,
            ]);
        }
        return true;
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_entitlement', 'e');
    }

    /**
     * @return \go\core\orm\Filters
     * @throws \Exception
     */
    protected static function defineFilters(): Filters
    {
        return parent::defineFilters()
            ->add('customerId', function (Criteria $criteria, $value) {
                if (!empty($value)) {
                    $criteria->where(['customerId' => $value]);
                }
            });
    }

    /**
     * Enable grid sorting on the RELATED product and customer columns (they are
     * relations, not columns of this table), by joining them and mapping the
     * client sort key to the joined column — the framework's documented pattern
     * (see Entity::sort). Customer sorts by companyName (the label the grid shows
     * when present); product by its title.
     *
     * @param \go\core\orm\Query $query
     * @param \go\core\util\ArrayObject $sort
     * @return \go\core\orm\Query
     * @throws \Exception
     */
    public static function sort(Query $query, ArrayObject $sort): Query
    {
        if (isset($sort['product'])) {
            $query->join('marketplaceserver_product', 'sortprod', 'sortprod.id = e.productId', 'LEFT');
            $sort->renameKey('product', 'sortprod.title');
        }
        if (isset($sort['customer'])) {
            $query->join('marketplaceserver_customer', 'sortcust', 'sortcust.id = e.customerId', 'LEFT');
            $sort->renameKey('customer', 'sortcust.companyName');
        }
        return parent::sort($query, $sort);
    }

    /**
     * Dialog convenience: admin picks a user; find-or-create the customer.
     *
     * @param int $userId
     * @return void
     * @throws \Exception
     */
    public function setUserId(int $userId): void
    {
        $this->customerId = Customer::findOrCreateForUser($userId)->id;
    }

    /**
     * Record that a customer acquired a FREE product (on download). A free
     * module is still a licensed grant — noting it makes it show up in the
     * customer's "My account" and, for a free module that belongs to a
     * collection, contributes to that collection reading as owned.
     *
     * Runs as a system side-effect of the token-authenticated download page
     * (there is no logged-in GO user to satisfy canCreate()), so it writes
     * directly rather than through the permission-gated save() path.
     *
     * If a grant already exists: leave an active one as-is, but REVIVE a lapsed
     * one to perpetual free — re-acquiring a free product (e.g. via a newly-free
     * collection) must actually re-license it, not silently no-op on the old
     * expired row.
     *
     * @param int $customerId
     * @param int $productId
     * @return void
     * @throws \Exception
     */
    public static function grantFree(int $customerId, int $productId): void
    {
        // Pass DateTime OBJECTS, not formatted strings: the query builder runs
        // each value through Column::castToDb(), which calls ->format() on
        // datetime columns — a string there fatals with "Call to a member
        // function format() on string".
        $now = new \DateTime();
        $exists = self::find()
            ->where(['customerId' => $customerId, 'productId' => $productId])
            ->single();
        if ($exists) {
            // Revive a lapsed OR revoked grant to perpetual free — re-acquiring a
            // free product must actually re-license it (and lift a prior revoke),
            // not silently no-op on the old dead row.
            $lapsed = $exists->expiresAt !== null && $exists->expiresAt->getTimestamp() < time();
            if ($lapsed || $exists->revokedAt !== null) {
                go()->getDbConnection()->update('marketplaceserver_entitlement', [
                    'expiresAt' => null,
                    'revokedAt' => null,
                    'source' => self::SOURCE_FREE,
                    'modifiedAt' => $now,
                ], ['id' => $exists->id])->execute();
            }
            return;
        }
        try {
            go()->getDbConnection()->insert('marketplaceserver_entitlement', [
                'customerId' => $customerId,
                'productId' => $productId,
                'source' => self::SOURCE_FREE,
                'expiresAt' => null,
                'createdAt' => $now,
                'modifiedAt' => $now,
            ])->execute();
        } catch (\Throwable $e) {
            // UNIQUE(customerId, productId): a concurrent free download inserted
            // the grant between our find and insert. That is exactly the desired
            // end state (one perpetual free grant), so treat it as success.
            if (!self::isDuplicateKey($e)) {
                throw $e;
            }
        }
    }

    /**
     * Grant (or renew) a PAID product for a customer from a verified payment
     * webhook. Upserts the single (customer, product) grant to active: sets the
     * new expiry (null = perpetual for a one-off purchase), records the gateway
     * source + subscription id, and clears any prior revoke/expiry. Runs as a
     * system side-effect of the signature-verified webhook (no logged-in GO user),
     * so it writes directly rather than through the permission-gated save() path.
     * Race-safe: the insert tolerates a concurrent duplicate.
     *
     * @param int $customerId
     * @param int $productId
     * @param int|null $expiresAt unix ts, or null = perpetual
     * @param string|null $subscriptionId the gateway subscription id, if any
     * @param string|null $paymentRef the gateway payment-intent id (for refund linkage)
     * @param string $source one of SOURCE_* (defaults to stripe)
     * @return void
     * @throws \Exception
     */
    public static function grantPaid(int $customerId, int $productId, ?int $expiresAt, ?string $subscriptionId = null, ?string $paymentRef = null, string $source = self::SOURCE_STRIPE): void
    {
        $now = new \DateTime();
        $expires = $expiresAt !== null ? (new \DateTime())->setTimestamp($expiresAt) : null;
        $fields = [
            'expiresAt' => $expires,
            'revokedAt' => null,
            'source' => $source,
            'stripeSubscriptionId' => $subscriptionId,
            'stripePaymentIntentId' => $paymentRef,
            'modifiedAt' => $now,
        ];

        $exists = self::find()
            ->where(['customerId' => $customerId, 'productId' => $productId])
            ->single();
        if ($exists) {
            go()->getDbConnection()->update('marketplaceserver_entitlement', $fields, ['id' => $exists->id])->execute();
            return;
        }
        try {
            go()->getDbConnection()->insert('marketplaceserver_entitlement', array_merge($fields, [
                'customerId' => $customerId,
                'productId' => $productId,
                'createdAt' => $now,
            ]))->execute();
        } catch (\Throwable $e) {
            if (!self::isDuplicateKey($e)) {
                throw $e;
            }
            // Concurrent insert won the race; ensure it reflects THIS payment.
            $row = self::find()->where(['customerId' => $customerId, 'productId' => $productId])->single();
            if ($row) {
                go()->getDbConnection()->update('marketplaceserver_entitlement', $fields, ['id' => $row->id])->execute();
            }
        }
    }

    /**
     * Revoke every grant tied to a gateway subscription id (subscription ended /
     * canceled). Sets revokedAt so the kill switch engages on every server path;
     * the rows are kept for audit. Idempotent.
     *
     * @param string $subscriptionId
     * @return int number of grants revoked
     * @throws \Exception
     */
    public static function revokeBySubscription(string $subscriptionId): int
    {
        return self::revokeByColumn('stripeSubscriptionId', $subscriptionId);
    }

    /**
     * Revoke every grant tied to a gateway payment-intent id (a one-off purchase
     * that was fully refunded). Sets revokedAt; rows kept for audit. Idempotent.
     *
     * @param string $paymentRef
     * @return int number of grants revoked
     * @throws \Exception
     */
    public static function revokeByPaymentRef(string $paymentRef): int
    {
        return self::revokeByColumn('stripePaymentIntentId', $paymentRef);
    }

    /**
     * Set revokedAt on every active grant whose $column equals $value. The column
     * name is a fixed internal constant (never user input), so it is safe to
     * interpolate; the value is bound.
     *
     * @param string $column
     * @param string $value
     * @return int
     * @throws \Exception
     */
    private static function revokeByColumn(string $column, string $value): int
    {
        if ($value === '') {
            return 0;
        }
        $stmt = go()->getDbConnection()->getPDO()->prepare(
            'UPDATE `marketplaceserver_entitlement` SET `revokedAt` = NOW(), `modifiedAt` = NOW() '
            . 'WHERE `' . $column . '` = ? AND `revokedAt` IS NULL'
        );
        $stmt->execute([$value]);
        return $stmt->rowCount();
    }

    /**
     * Whether a DB exception is a duplicate-unique-key violation (SQLSTATE 23000
     * / MySQL error 1062), used to make the free-grant insert idempotent under a
     * race.
     *
     * @param \Throwable $e
     * @return bool
     */
    private static function isDuplicateKey(\Throwable $e): bool
    {
        if ($e instanceof \PDOException && $e->getCode() === '23000') {
            return true;
        }
        $prev = $e->getPrevious();
        if ($prev instanceof \PDOException && $prev->getCode() === '23000') {
            return true;
        }
        return strpos($e->getMessage(), '1062') !== false
            || stripos($e->getMessage(), 'Duplicate entry') !== false;
    }

    /**
     * Pin a hostname-mode grant to a host on first use (trust on first use),
     * race-safe: the UPDATE only writes when still unpinned, so the first
     * concurrent /license wins. Runs as a system side-effect of the
     * token-authenticated license endpoint (no logged-in GO user), so it writes
     * directly rather than through the permission-gated save() path.
     *
     * @param int $entitlementId
     * @param string $hostname
     * @return string the EFFECTIVE bound hostname after the attempt (the winner's)
     * @throws \Exception
     */
    public static function pinHostname(int $entitlementId, string $hostname): string
    {
        $pdo = go()->getDbConnection()->getPDO();
        $upd = $pdo->prepare(
            'UPDATE `marketplaceserver_entitlement` SET `boundHostname` = ?, `modifiedAt` = NOW() '
            . 'WHERE `id` = ? AND `boundHostname` IS NULL'
        );
        $upd->execute([$hostname, $entitlementId]);

        $sel = $pdo->prepare('SELECT `boundHostname` FROM `marketplaceserver_entitlement` WHERE `id` = ?');
        $sel->execute([$entitlementId]);
        return (string) $sel->fetchColumn();
    }

    /**
     * Managers may create/manage entitlements; a customer may only ever read
     * their own (granted by a manager or by Stripe sync — never self-service).
     *
     * @return bool
     * @throws \Exception
     */
    protected function canCreate(): bool
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        return $module && !empty($module->getUserRights()->mayManage);
    }

    /**
     * @return int
     * @throws \Exception
     */
    protected function internalGetPermissionLevel(): int
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if ($module && !empty($module->getUserRights()->mayManage)) {
            return Acl::LEVEL_MANAGE;
        }
        if ($this->isNew()) {
            return $this->canCreate() ? Acl::LEVEL_CREATE : 0;
        }
        $uid = go()->getUserId();
        if ($uid !== null && !empty($this->customerId)) {
            $customer = Customer::findById((string) $this->customerId);
            if ($customer && $customer->userId === $uid) {
                return Acl::LEVEL_READ;
            }
        }
        return 0;
    }

    /**
     * Restrict list/query results to entitlements owned (via customer) by the
     * current user unless they're a marketplace manager.
     *
     * @param \go\core\orm\Query $query
     * @param int $level
     * @param int|null $userId
     * @param int[]|null $groups
     * @return \go\core\orm\Query
     * @throws \Exception
     */
    public static function applyAclToQuery(Query $query, int $level = Acl::LEVEL_READ, int $userId = null, array $groups = null): Query
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if ($module && !empty($module->getUserRights()->mayManage)) {
            return $query;
        }
        $uid = $userId ?? go()->getUserId();
        if ($uid === null) {
            return $query->andWhere('1 = 0');
        }
        return $query
            ->join('marketplaceserver_customer', 'aclc', 'aclc.id = e.customerId')
            ->andWhere(['aclc.userId' => $uid]);
    }
}
