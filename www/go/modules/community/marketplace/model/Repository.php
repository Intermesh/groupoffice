<?php

namespace go\modules\community\marketplace\model;

use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Mapping;
use go\core\orm\Query;
use go\core\util\Crypt;
use go\core\util\DateTime;
use go\core\validate\ErrorCode;
use go\modules\community\marketplace\lib\ApiClient;
use go\modules\community\marketplace\lib\LicenseVerifier;

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
     * (go/modules/{package}/{module}). Pinned from the server's /info on save —
     * NOT the display name. Read-only through the API ({@see getPackage()}).
     *
     * @var string|null
     */
    protected $package;

    /**
     * Encrypted API token. Protected + no getX() so it is never serialized to
     * the browser nor stored in cleartext (same pattern as Settings password).
     *
     * @var string|null
     */
    protected $token;

    /**
     * Pinned RS256 public key (PEM). Protected with no getter or setter: only
     * {@see pinServer()} writes it, so the API cannot re-pin the key that every
     * downloaded package and license is verified against.
     *
     * @var string|null
     */
    protected $publicKey;

    /**
     * Cached license JWT. Written only through {@see storeLicense()}, which
     * verifies it first.
     *
     * @var string|null
     */
    protected $licenseJwt;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $lastSyncAt;

    /**
     * @var string|null
     */
    public $lastError;

    /**
     * Set when the server's /info key stops matching the pinned key. Read-only
     * through the API ({@see getKeyMismatch()}).
     *
     * @var bool
     */
    protected $keyMismatch = false;

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
     * Packages a repository may never install into: a marketplace server must not
     * be able to overwrite Group-Office's own modules.
     */
    const RESERVED_PACKAGES = ['core', 'community', 'business', 'legacy'];

    /**
     * @return string|null
     */
    public function getPackage(): ?string
    {
        return $this->package;
    }

    /**
     * @return bool
     */
    public function getKeyMismatch(): bool
    {
        return (bool) $this->keyMismatch;
    }

    /**
     * @return bool
     */
    public function hasLicense(): bool
    {
        return !empty($this->licenseJwt) && !empty($this->publicKey);
    }

    /**
     * Server-side only — deliberately NOT getPublicKey() (auto-exposed).
     *
     * @return string
     */
    public function pinnedPublicKey(): string
    {
        return (string) $this->publicKey;
    }

    /**
     * @param string $host
     * @return \go\modules\community\marketplace\lib\LicenseVerifier
     */
    public function licenseVerifier(string $host): LicenseVerifier
    {
        return new LicenseVerifier((string) $this->licenseJwt, (string) $this->publicKey, $host);
    }

    /**
     * True when $package is a name a repository may install into.
     *
     * @param string $package
     * @return bool
     */
    public static function isAllowedPackage(string $package): bool
    {
        return preg_match('/^[a-z0-9_]+$/', $package) === 1
            && !in_array($package, self::RESERVED_PACKAGES, true);
    }

    /**
     * Fetch the server's /info with the stored token and pin its package, name
     * and signing key. Runs on create and whenever the URL or token changes, so
     * re-entering a working token is the one way to accept a rotated key.
     *
     * @return void
     * @throws \Exception when the server is unreachable, the token is refused or
     *   the server's package is not allowed
     */
    public function pinServer(): void
    {
        $info = (new ApiClient($this))->info();
        $package = (string) ($info['package'] ?? '');
        if (!self::isAllowedPackage($package)) {
            throw new \Exception(go()->t("This marketplace server publishes into a package that is not allowed", 'community', 'marketplace') . ': "' . $package . '"');
        }
        $publicKey = (string) ($info['publicKey'] ?? '');
        if ($publicKey === '') {
            throw new \Exception(go()->t("This marketplace server did not provide a signing key", 'community', 'marketplace'));
        }
        if (!empty($this->package) && $this->package !== $package) {
            // Another server (or package) now: what was downloaded and licensed
            // from the old one says nothing about this one.
            $this->downloadedModules = [];
            $this->licenseJwt = null;
        }
        $this->package = $package;
        $this->publicKey = $publicKey;
        $this->name = (string) ($info['name'] ?? '') !== '' ? (string) $info['name'] : $package;
        $this->keyMismatch = false;
    }

    /**
     * Replace the cached license, but only with a token that verifies against the
     * pinned key for this repository's package and this host. A broken or empty
     * response keeps the previous license.
     *
     * @param string $jwt
     * @param string $host
     * @return void
     * @throws \Exception when the token does not verify
     */
    public function storeLicense(string $jwt, string $host): void
    {
        $verifier = new LicenseVerifier($jwt, (string) $this->publicKey, $host);
        if ($jwt === '' || !$verifier->isValidFor((string) $this->package)) {
            throw new \Exception(go()->t("The marketplace server returned a license that could not be verified", 'community', 'marketplace'));
        }
        $this->licenseJwt = $jwt;
        $this->lastSyncAt = new DateTime();
        $this->lastError = null;
        $this->keyMismatch = false;
    }

    /**
     * @return void
     */
    public function flagKeyMismatch(): void
    {
        $this->keyMismatch = true;
        $this->setLastError('Signing key changed');
    }

    /**
     * @param string|null $message
     * @return void
     */
    public function setLastError(?string $message): void
    {
        $this->lastError = $message === null ? null : mb_substr($message, 0, 500);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function internalValidate()
    {
        if ($this->isNew() || $this->isModified(['url', 'token'])) {
            if (empty($this->token)) {
                $this->setValidationError('token', ErrorCode::REQUIRED, go()->t("Enter the API token", 'community', 'marketplace'));
            } else {
                try {
                    $this->pinServer();
                } catch (\Throwable $e) {
                    $this->setValidationError('token', ErrorCode::INVALID_INPUT, $e->getMessage());
                }
            }
        }

        if (!$this->hasValidationErrors() && $this->isModified(['package']) && !empty($this->package)) {
            $other = self::find()->where(['package' => $this->package]);
            if (!$this->isNew()) {
                $other->andWhere('id', '!=', $this->id);
            }
            if ($other->single()) {
                $this->setValidationError('url', ErrorCode::UNIQUE, go()->t("Another repository already installs into this package", 'community', 'marketplace') . ': "' . $this->package . '"');
            }
        }

        parent::internalValidate();
    }

    /**
     * Repositories install and run code, so only admins manage them. A user
     * with the module's manage right may look at them (catalog, account,
     * checkout) but not change or download anything.
     *
     * @return int
     * @throws \Exception
     */
    protected function internalGetPermissionLevel(): int
    {
        if (go()->getAuthState() && go()->getAuthState()->isAdmin()) {
            return Acl::LEVEL_MANAGE;
        }
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if (!$module) {
            return 0;
        }
        return !empty($module->getUserRights()->mayManage) ? Acl::LEVEL_READ : 0;
    }

    /**
     * The other half of the pair above. For a jmap\Entity the base
     * applyAclToQuery() is a no-op, so without this `query` would hand a user
     * who has the module but not its manage right every repository's id, name
     * and URL — the level only gates get/set.
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
        $state = go()->getAuthState();
        if ($state && $state->isAdmin()) {
            return $query;
        }
        $module = \go\core\App::get()->getModule('community', 'marketplace');
        if ($module && !empty($module->getUserRights()->mayManage)) {
            return $query;
        }
        return $query->andWhere('1 = 0');
    }

    /**
     * @return bool
     * @throws \Exception
     */
    protected function canCreate(): bool
    {
        return go()->getAuthState() && go()->getAuthState()->isAdmin();
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['r.name', 'r.url'];
    }
}
