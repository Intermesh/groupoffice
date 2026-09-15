<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * An append-only audit record of a single marketplace event (a download, a paid
 * purchase, a refund, a registration, a grant/revoke, ...). Written by the system
 * through {@see record()} as a side-effect of the event's own code path — never
 * created/edited over JMAP. Manager-only to read.
 */
class Activity extends Entity
{
    const TYPE_DOWNLOAD = 'download';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_REFUND = 'refund';
    const TYPE_SUBSCRIPTION_CANCELED = 'subscription_canceled';
    const TYPE_REGISTER = 'register';
    const TYPE_VERIFY = 'verify';
    const TYPE_GRANT = 'grant';
    const TYPE_REVOKE = 'revoke';
    const TYPE_RESTORE = 'restore';

    /**
     * The column set {@see record()} accepts, so a stray key can never reach the
     * insert. `type` + `createdAt` are always set by record() itself.
     */
    const FIELDS = ['customerId', 'productId', 'moduleName', 'version', 'hostname', 'amount', 'currency', 'ref', 'ip', 'detail'];

    /**
     * @var int
     */
    public $id;

    /**
     * @var \go\core\util\DateTime
     */
    public $createdAt;

    /**
     * @var string
     */
    public $type;

    /**
     * @var int|null
     */
    public $customerId;

    /**
     * @var int|null
     */
    public $productId;

    /**
     * @var string|null
     */
    public $moduleName;

    /**
     * @var string|null
     */
    public $version;

    /**
     * @var string|null
     */
    public $hostname;

    /**
     * Amount in the currency's minor unit (e.g. cents), for purchase/refund.
     *
     * @var int|null
     */
    public $amount;

    /**
     * @var string|null
     */
    public $currency;

    /**
     * External reference (payment-intent / subscription / session id).
     *
     * @var string|null
     */
    public $ref;

    /**
     * @var string|null
     */
    public $ip;

    /**
     * @var string|null
     */
    public $detail;

    public static function getClientName(): string
    {
        return 'MarketplaceServerActivity';
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_activity', 'a');
    }

    /**
     * Record one activity row. A pure side-effect of the caller's own action, so
     * it writes directly (no logged-in user / ACL) and NEVER throws — an audit
     * write must not break the download/purchase/etc. it is recording. Unknown
     * keys are dropped; `type` + `createdAt` are always set.
     *
     * @param string $type one of the TYPE_* constants
     * @param array<string, mixed> $fields subset of {@see FIELDS}
     * @return void
     */
    public static function record(string $type, array $fields = []): void
    {
        try {
            $row = ['type' => $type, 'createdAt' => new \DateTime()];
            foreach (self::FIELDS as $col) {
                if (array_key_exists($col, $fields) && $fields[$col] !== null && $fields[$col] !== '') {
                    $row[$col] = $fields[$col];
                }
            }
            go()->getDbConnection()->insert('marketplaceserver_activity', $row)->execute();
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }
    }

    /**
     * @return \go\core\orm\Filters
     * @throws \Exception
     */
    protected static function defineFilters(): Filters
    {
        return parent::defineFilters()
            ->add('type', function (Criteria $criteria, $value) {
                if (!empty($value)) {
                    $criteria->where(['type' => $value]);
                }
            })
            // Multi-select event-type filter (the side-panel checkbox group): an
            // array of TYPE_* values → type IN (...). `createdAt` (date range) and
            // `text` come free from the parent + textFilterColumns().
            ->add('actions', function (Criteria $criteria, $value) {
                if (!empty($value) && is_array($value)) {
                    $criteria->where('type', 'IN', array_values($value));
                }
            })
            ->add('customerId', function (Criteria $criteria, $value) {
                if (!empty($value)) {
                    $criteria->where(['customerId' => $value]);
                }
            });
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['a.moduleName', 'a.ref', 'a.hostname', 'a.detail'];
    }

    /**
     * Manager-only, read-only: the whole log is an admin audit surface, and no
     * one — including managers — creates or edits rows via the API (only the
     * system writes, through record()).
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
        return !empty($module->getUserRights()->mayManage) ? Acl::LEVEL_READ : 0;
    }

    /**
     * Never creatable over the API — the log is system-written only.
     *
     * @return bool
     */
    protected function canCreate(): bool
    {
        return false;
    }

    /**
     * Restrict list/query to managers; everyone else sees nothing.
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
        return $query->andWhere('1 = 0');
    }
}
