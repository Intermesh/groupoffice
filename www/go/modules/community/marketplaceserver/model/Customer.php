<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\ArrayObject;
use go\core\util\StringUtil;
use go\modules\community\addressbook\model\Contact;

/**
 * A marketplace customer: the billing/entitlement anchor for a GO user. The
 * user's addressbook profile Contact ({@see contact()}) is the source of truth
 * for name/email/company/address (CRM); `companyName` is a denormalised copy
 * for grid display only.
 *
 * Customers are provisioned by self-registration (the Registrar) or by an
 * admin — NOT lazily by any logged-in server user. The customer never logs into
 * the server; they act through the community/marketplace client over the public API.
 */
class Customer extends Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * Denormalised company name for admin grid display. Source of truth is the
     * linked profile Contact; kept here to avoid a per-row join in listings.
     *
     * @var string|null
     */
    public $companyName;

    /**
     * When the account's e-mail was first verified (the user enabled). NULL =
     * never verified. Lets the API tell "not verified yet" apart from
     * "verified but later disabled/suspended by an admin".
     *
     * @var ?\go\core\util\DateTime
     */
    public $verifiedAt;

    /**
     * Max concurrent instances (distinct active hostnames) that may hold
     * seat-mode licenses at once. 0 = unlimited. A hostname-mode entitlement is
     * governed by its own pin and does NOT draw from this pool.
     *
     * @var int
     */
    public $maxInstances = 1;

    /**
     * @var string|null
     */
    public $stripeCustomerId;

    /**
     * @var string|null
     */
    public $notes;

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

    /**
     * Pending value for the virtual `enabled` API property (see getEnabled /
     * setEnabled). NOT a column — the flag physically lives on the linked
     * core_user and is applied there in internalSave(). Null until read or set.
     *
     * @var bool|null
     */
    protected $enabled = null;

    public static function getClientName(): string
    {
        return 'MarketplaceServerCustomer';
    }

    /**
     * A display label for pickers/combos: the company name, else the owning user's
     * name/e-mail, else "#id". Exposed as the API `displayName` property so a
     * customer combo has one always-populated field to show (companyName is
     * optional and the user's name lives one relation hop away). The user lookup
     * only runs when there is no companyName.
     *
     * @return string
     * @throws \Exception
     */
    public function getDisplayName(): string
    {
        if (!empty($this->companyName)) {
            return (string) $this->companyName;
        }
        $user = $this->userId
            ? \go\core\model\User::findById((string) $this->userId, ['displayName', 'email', 'username'])
            : null;
        if ($user) {
            return (string) ($user->displayName ?: ($user->email ?: $user->username));
        }
        return '#' . $this->id;
    }

    /**
     * Virtual `enabled` API property: the linked account's enabled flag. It
     * physically lives on core_user (not this table); exposed here — via GO's
     * getter/setter API-property mechanism — so the admin dialog can toggle it as
     * a plain saved form field. Read lazily; applied to the user on save.
     *
     * @return bool
     * @throws \Exception
     */
    public function getEnabled(): bool
    {
        if ($this->enabled === null) {
            $user = $this->userId
                ? \go\core\model\User::findById((string) $this->userId, ['id', 'enabled'], true)
                : null;
            $this->enabled = $user ? (bool) $user->enabled : false;
        }
        return $this->enabled;
    }

    /**
     * @param mixed $value truthy = enable the linked account
     * @return void
     */
    public function setEnabled($value): void
    {
        $this->enabled = (bool) $value;
    }

    /**
     * Apply the virtual `enabled` flag to the linked core_user (the flag lives
     * there, not on this table). Mirrors controller Customer::setEnabled,
     * including the verifiedAt stamp on the first enable so an admin can activate
     * a never-verified account. verifiedAt is set BEFORE parent::internalSave so
     * it persists in the same write as the customer row.
     *
     * Only touches the user when `enabled` was actually set/changed via the API
     * (dialog), so unrelated saves (findOrCreateForUser, verifiedAt-only saves
     * from the controller) are unaffected.
     *
     * @return bool
     * @throws \Exception
     */
    protected function internalSave(): bool
    {
        if ($this->enabled !== null && !empty($this->userId)) {
            // Full fetch (not just ['enabled']): User::save() validates the whole
            // record, so it needs every field loaded — mirrors controller setEnabled.
            $user = \go\core\model\User::findById((string) $this->userId);
            if ($user && (bool) $user->enabled !== $this->enabled) {
                $user->enabled = $this->enabled;
                if (!$user->save()) {
                    $this->setValidationError('enabled', \go\core\validate\ErrorCode::INVALID_INPUT,
                        'Could not update the linked user account: ' . $user->getValidationErrorsAsString());
                    return false;
                }
                if ($this->enabled && $this->verifiedAt === null) {
                    $this->verifiedAt = new \go\core\util\DateTime();
                }
            }
        }

        return parent::internalSave();
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_customer', 'c');
    }

    /**
     * Custom `text` filter: the denormalised companyName is often empty, so a
     * plain textFilterColumns(['companyName']) makes most customers unsearchable.
     * Join the owning user so the admin can find a customer by person name /
     * username too (Entitlements panel left list, Customer grid search), built on
     * StringUtil + %…% wrap + an empty guard.
     *
     * @return \go\core\orm\Filters
     * @throws \Exception
     */
    protected static function defineFilters(): Filters
    {
        return parent::defineFilters()
            ->addText('text', function (Criteria $criteria, $comparator, $value, Query $query) {
                if (empty($value)) {
                    return;
                }
                // The framework wraps a `text` filter value in an ARRAY before
                // calling us (Filters::applyCondition: `$value = [$value]`), so a
                // typed search arrives as e.g. ['fuel pump']. Tokenise EACH element
                // — passing the array straight to explodeSearchExpression() (which
                // requires a string) is a TypeError.
                $terms = is_array($value) ? $value : [$value];
                $words = [];
                foreach ($terms as $term) {
                    foreach (StringUtil::explodeSearchExpression((string) $term) as $w) {
                        $words[] = $w;
                    }
                }
                if (empty($words)) {
                    return;
                }
                $query->join('core_user', 'u', 'u.id = c.userId', 'LEFT');
                foreach ($words as $word) {
                    $like = '%' . $word . '%';
                    $criteria->andWhere(
                        (new Criteria())
                            ->where('c.companyName', 'LIKE', $like)
                            ->orWhere('u.displayName', 'LIKE', $like)
                            ->orWhere('u.username', 'LIKE', $like)
                    );
                }
            });
    }

    /**
     * Enable grid sorting on the related USER column (the grid shows the owning
     * user's display name, which lives on core_user, not this table) by joining
     * core_user and mapping the client sort key to it — the framework's documented
     * pattern (see Entity::sort).
     *
     * @param \go\core\orm\Query $query
     * @param \go\core\util\ArrayObject $sort
     * @return \go\core\orm\Query
     * @throws \Exception
     */
    public static function sort(Query $query, ArrayObject $sort): Query
    {
        if (isset($sort['user'])) {
            $query->join('core_user', 'sortu', 'sortu.id = c.userId', 'LEFT');
            $sort->renameKey('user', 'sortu.displayName');
        }
        return parent::sort($query, $sort);
    }

    /**
     * The CRM Contact backing this customer — the profile contact of the linked
     * GO user (source of truth for name/email/company/address). Null for legacy
     * rows or before the user's addressbook profile exists.
     *
     * @return \go\modules\community\addressbook\model\Contact|null
     * @throws \Exception
     */
    public function contact(): ?Contact
    {
        return $this->userId ? Contact::findForUser($this->userId) : null;
    }

    /**
     * A non-manager may only provision a customer row for THEMSELVES — the
     * standard JMAP create path applies client-submitted `userId` before this
     * runs, so without the ownership check a user could squat another user's
     * row (unique userId + findOrCreateForUser reuse = billing hijack).
     * Managers may legitimately provision any user's row (e.g. via
     * Entitlement::setUserId).
     *
     * @return bool
     * @throws \Exception
     */
    protected function canCreate(): bool
    {
        $uid = go()->getUserId();
        if ($uid === null) {
            return false;
        }
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if ($module && !empty($module->getUserRights()->mayManage)) {
            return true;
        }
        return $this->userId === $uid;
    }

    /**
     * Owner-scoped: managers get MANAGE on every row, the user themselves
     * gets READ on their own row, everyone else gets nothing.
     *
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
        if ($uid !== null && $this->userId === $uid) {
            return Acl::LEVEL_READ;
        }
        return 0;
    }

    /**
     * Restrict list/query results to the user's own customer row unless
     * they're a marketplace manager.
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
        return $query->andWhere(['c.userId' => $uid]);
    }

    /**
     * Find or create the Customer row for a user. Used by the Registrar and by
     * admin token issuance — not a customer-facing self-service path (the server
     * has no customer UI).
     *
     * @param int $userId
     * @return \go\modules\community\marketplaceserver\model\Customer
     * @throws \Exception
     */
    public static function findOrCreateForUser(int $userId): self
    {
        $customer = self::find()->where(['userId' => $userId])->single();
        if ($customer) {
            return $customer;
        }
        $customer = new self();
        $customer->userId = $userId;
        if (!$customer->save()) {
            throw new \Exception('Could not create customer: ' . var_export($customer->getValidationErrors(), true));
        }
        return $customer;
    }
}
