<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\db\Criteria;
use go\core\fs\Blob;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;
use go\core\orm\Query;

/**
 * A published version of a module: the zip is stored as a blob and served to
 * licensed instances via the download endpoint. Manager-only — customers
 * never see this entity directly (they hit the page API instead).
 */
class Release extends Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * The module-type Product this is a release of. The relation is explicit
     * (a release always belongs to exactly one module product); collections and
     * subscriptions reference releases indirectly through their member modules.
     *
     * @var int
     */
    public $productId;

    /**
     * Denormalised module name, auto-derived from the linked product in
     * internalValidate() — never edited directly. Kept on the row because the
     * download/license path keys on it (go/modules/{package}/{moduleName}).
     *
     * @var string
     */
    public $moduleName;

    /**
     * @var string
     */
    public $version;

    /**
     * Target Group-Office version BRANCH (major version), e.g. "6.8", "25",
     * "26". A GO module is branch-specific, so the client downloads the release
     * matching its own branch (go()->getMajorVersion()), not a "minimum". The
     * selectable branches are configured in the module settings.
     *
     * @var string
     */
    public $goVersion;

    /**
     * @var string|null
     */
    public $changelog;

    /**
     * @var string
     */
    public $blobId;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $publishedAt;

    /**
     * @var bool
     */
    public $active = true;

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
        return 'MarketplaceServerRelease';
    }

    /**
     * True when a release targeting branch $branch is compatible with a client
     * running Group-Office version $clientVersion. Matches on the major-version
     * BRANCH at a dot boundary, so it is robust no matter how many segments
     * either side carries (App::getMajorVersion() strips only the LAST segment,
     * so "25.0.5" would otherwise become "25.0" and never equal a "25" branch):
     *
     *   branch "6.8", client "6.8.175"  -> true  (client starts with "6.8.")
     *   branch "25",  client "25.0.5"   -> true  (client starts with "25.")
     *   branch "25",  client "25"       -> true  (equal)
     *   branch "6.8", client "6.8"      -> true  (equal — client sent its branch)
     *   branch "6.8", client "25.0.5"   -> false
     *
     * A blank branch or client version matches nothing (the caller decides the
     * fallback).
     *
     * @param string $branch the release's target branch (e.g. "6.8", "25")
     * @param string $clientVersion the client's running GO version or branch
     * @return bool
     */
    public static function branchMatches(string $branch, string $clientVersion): bool
    {
        $branch = trim($branch);
        $clientVersion = trim($clientVersion);
        if ($branch === '' || $clientVersion === '') {
            return false;
        }
        return $branch === $clientVersion
            || strpos($clientVersion, $branch . '.') === 0
            || strpos($branch, $clientVersion . '.') === 0;
    }

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_release', 'r');
    }

    /**
     * Manager-only entity — customers download releases through the page API,
     * never through the JMAP entity directly.
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
     * Bind the release to a module-type product and keep the denormalised
     * moduleName in sync with it. A release may only belong to a product of
     * type "module" (collections/subscriptions don't have their own releases).
     *
     * @return void
     */
    protected function internalValidate()
    {
        $product = $this->productId ? Product::findById((string) $this->productId) : null;
        if (!$product) {
            $this->setValidationError('productId', \go\core\validate\ErrorCode::INVALID_INPUT, 'A module product is required');
        } elseif ($product->type !== Product::TYPE_MODULE) {
            $this->setValidationError('productId', \go\core\validate\ErrorCode::INVALID_INPUT, 'Releases can only be attached to a module product');
        } elseif (empty($product->moduleName)) {
            // Defensive: a module product should always carry a moduleName (its
            // own internalValidate enforces this), but guard against legacy rows
            // so the error points at the real cause (the product) rather than at
            // this derived NOT NULL column.
            $this->setValidationError('productId', \go\core\validate\ErrorCode::INVALID_INPUT, 'The selected module product has no module name set');
        } else {
            // keep the denormalised module name in lock-step with the product
            $this->moduleName = $product->moduleName;
        }
        if (empty($this->goVersion)) {
            $this->setValidationError('goVersion', \go\core\validate\ErrorCode::INVALID_INPUT, 'A Group-Office version branch is required');
        }
        // Validate the uploaded package ZIP at publish time (server-side), so a
        // broken / mis-rooted / path-traversing archive is refused here instead
        // of only failing on the customer's machine during extraction. Only when
        // the blob is new or changed and the module name is known (a bad product
        // is already flagged above) — validating an unchanged blob on every save
        // would needlessly re-open the ZIP.
        if (!empty($this->blobId) && !empty($this->moduleName)
            && ($this->isNew() || $this->isModified(['blobId']))) {
            $blob = Blob::findById($this->blobId);
            if (!$blob) {
                $this->setValidationError('blobId', \go\core\validate\ErrorCode::INVALID_INPUT, 'The uploaded package file could not be found');
            } else {
                $err = \go\modules\community\marketplaceserver\lib\PackageValidator::validateZipFile(
                    $blob->getFile()->getPath(),
                    $this->moduleName
                );
                if ($err !== null) {
                    $this->setValidationError('blobId', \go\core\validate\ErrorCode::INVALID_INPUT, $err);
                }
            }
        }
        // Default the publish timestamp to "now" on create when the caller left
        // it empty, so every release records when it went live regardless of the
        // code path (dialog, API, CLI).
        if ($this->isNew() && empty($this->publishedAt)) {
            $this->publishedAt = new \go\core\util\DateTime();
        }

        parent::internalValidate();
    }

    /**
     * Clear staleAt on the release blob so it isn't garbage collected while
     * still referenced.
     *
     * @return bool
     */
    protected function internalSave(): bool
    {
        if (!parent::internalSave()) {
            return false;
        }
        if (!empty($this->blobId)) {
            $blob = Blob::findById($this->blobId);
            if ($blob && isset($blob->staleAt)) {
                $blob->staleAt = null;
                $blob->save();
            }
        }
        return true;
    }

    /**
     * @return \go\core\orm\Filters
     * @throws \Exception
     */
    protected static function defineFilters(): Filters
    {
        return parent::defineFilters()
            ->add('productId', function (Criteria $criteria, $value) {
                if (!empty($value)) {
                    $criteria->where(['productId' => $value]);
                }
            })
            // Keep only the newest release per (module, GO branch): the row for
            // which no other release of the same product+goVersion is newer, by
            // publishedAt with id as a deterministic tie-break (and a fallback when
            // publishedAt is null). Lets the admin grid collapse a long version
            // history to just the current build per branch.
            ->add('latest', function (Criteria $criteria, $value, Query $query) {
                if (empty($value)) {
                    return;
                }
                $criteria->where(
                    "NOT EXISTS (SELECT 1 FROM marketplaceserver_release r2 "
                    . "WHERE r2.productId = r.productId AND r2.goVersion = r.goVersion AND ("
                    . "COALESCE(r2.publishedAt, '1000-01-01') > COALESCE(r.publishedAt, '1000-01-01') "
                    . "OR (COALESCE(r2.publishedAt, '1000-01-01') = COALESCE(r.publishedAt, '1000-01-01') AND r2.id > r.id)))"
                );
            });
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['r.moduleName', 'r.version', 'r.goVersion'];
    }
}
