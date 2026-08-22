<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * A bearer token a customer's instance uses to authenticate against the
 * public page API (license checks, release downloads, telemetry). Only the
 * hash is ever persisted/serialized — the plaintext token is shown once at
 * generation time by the controller and never stored.
 */
class ApiToken extends Entity
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
     * Never exposed over JMAP — server-side only, use peekTokenHash()/assignTokenHash().
     *
     * @var string
     */
    protected $tokenHash;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $lastUsedAt;

    /**
     * @var bool
     */
    public $revoked = false;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $createdAt;

    public static function getClientName(): string
    {
        return 'MarketplaceServerApiToken';
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_api_token', 'tk');
    }

    /**
     * Server-side only — used by the page API auth lookup and the generate
     * controller. Not getX()-named so never exposed over JMAP.
     *
     * @return string|null
     */
    public function peekTokenHash(): ?string
    {
        return $this->tokenHash;
    }

    /**
     * @param string $hash
     * @return void
     */
    public function assignTokenHash(string $hash): void
    {
        $this->tokenHash = $hash;
    }

    /**
     * Bound the number of live tokens per customer. Every /login mints a fresh
     * token (the old plaintext is unrecoverable server-side), so without pruning
     * they accumulate forever, growing the table and the set of valid credentials.
     * Delete this customer's already-revoked tokens, plus any auto-issued
     * 'Login'/'Registration' token idle past $unusedDays — a long-idle auto-token
     * is a superseded login, and a client still holding a valid one simply logs
     * in again. Best-effort: a failure here must never break the login itself.
     *
     * @param int $customerId
     * @param int $unusedDays
     * @return void
     */
    public static function pruneStale(int $customerId, int $unusedDays = 90): void
    {
        try {
            $pdo = go()->getDbConnection()->getPDO();
            $cutoff = (new \DateTime())->sub(new \DateInterval('P' . $unusedDays . 'D'))->format('Y-m-d H:i:s');

            $pdo->prepare('DELETE FROM `marketplaceserver_api_token` WHERE `customerId` = ? AND `revoked` = 1')
                ->execute([$customerId]);

            // Idle auto-tokens (COALESCE lastUsedAt→createdAt older than the cutoff).
            // A just-issued token has createdAt = now, so it is never caught here.
            $pdo->prepare(
                "DELETE FROM `marketplaceserver_api_token` WHERE `customerId` = ? "
                . "AND `name` IN ('Login', 'Registration') "
                . "AND COALESCE(`lastUsedAt`, `createdAt`) < ?"
            )->execute([$customerId, $cutoff]);
        } catch (\Throwable $e) {
            \go\core\ErrorHandler::logException($e);
        }
    }

    /**
     * A non-manager may only create a token tied to THEIR OWN customer row —
     * the standard JMAP create path applies the client-submitted `customerId`
     * before this runs, so without the ownership check a user could mint a
     * token against another customer's row. Managers may create for anyone.
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
        if (empty($this->customerId)) {
            return false;
        }
        $customer = Customer::findById((string) $this->customerId);
        return $customer && $customer->userId === $uid;
    }

    /**
     * Owner-scoped via the parent customer's userId; managers get MANAGE on
     * every row.
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
        if ($uid !== null && !empty($this->customerId)) {
            $customer = Customer::findById((string) $this->customerId);
            if ($customer && $customer->userId === $uid) {
                return Acl::LEVEL_MANAGE;
            }
        }
        return 0;
    }

    /**
     * Restrict list/query results to tokens owned (via customer) by the
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
            ->join('marketplaceserver_customer', 'aclc', 'aclc.id = tk.customerId')
            ->andWhere(['aclc.userId' => $uid]);
    }
}
