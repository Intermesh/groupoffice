<?php

namespace go\modules\community\marketplace\model;

use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Mapping;
use go\core\util\Crypt;

class Repository extends Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * Human-readable label (the server's title, set from /info during validate).
     *
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $url;

    /**
     * The server's package (e.g. "sf") that its modules install into
     * (go/modules/{package}/{module}). Set from the server's /info during
     * validate — NOT the display name. Used by the download/extract path.
     *
     * @var string|null
     */
    public $package;

    /**
     * Encrypted API token. Protected + no getX() so it is never serialized to
     * the browser nor stored in cleartext (same pattern as Settings password).
     *
     * @var string|null
     */
    protected $token;

    /**
     * Pinned RS256 public key (PEM).
     *
     * @var string|null
     */
    public $publicKey;

    /**
     * Cached license JWT.
     *
     * @var string|null
     */
    public $licenseJwt;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $lastSyncAt;

    /**
     * @var string|null
     */
    public $lastError;

    /**
     * Set when the server's /info key stops matching the pinned key.
     *
     * @var bool
     */
    public $keyMismatch = false;

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
     * Downloaded module -> version tracking.
     *
     * @var \go\modules\community\marketplace\model\RepositoryModule[]
     */
    public $downloadedModules = [];

    public static function getClientName(): string
    {
        return 'MarketplaceRepository';
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplace_repository', 'r')
            ->addArray('downloadedModules', RepositoryModule::class, ['id' => 'repositoryId']);
    }

    /**
     * Encrypt the token on write. A blank value means "unchanged" so the
     * settings dialog does not wipe the stored token (the field is never
     * populated on load because it is write-only).
     *
     * @param string|null $value blank = keep existing (write-only)
     * @return void
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function setToken(?string $value): void
    {
        if ($value === null || $value === '') {
            return;
        }
        $this->token = Crypt::encrypt($value);
    }

    /**
     * Server-side only — deliberately NOT getToken() (auto-exposed).
     *
     * @return string|null
     * @throws \Exception
     */
    public function decryptToken(): ?string
    {
        return empty($this->token) ? null : Crypt::decrypt($this->token);
    }

    /**
     * Safe boolean the UI can read without exposing the token.
     *
     * @return bool
     */
    public function getTokenConfigured(): bool
    {
        return !empty($this->token);
    }

    /**
     * Repositories are manager/admin tooling only. Mirror marketplaceserver
     * Release: mayManage -> MANAGE else 0. (System Settings is admin-gated, but
     * the entity is the source of truth per the entity-permissions rule.)
     *
     * @return int
     * @throws \Exception
     */
    public function getPermissionLevel(): int
    {
        $module = \go\core\App::get()->getModule('community', 'marketplace');
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
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        return $module && !empty($module->getUserRights()->mayManage);
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['r.name', 'r.url'];
    }
}
