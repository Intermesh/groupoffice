<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\db\Criteria;
use go\core\jmap\Entity;
use go\core\model\Acl;
use go\core\orm\Filters;
use go\core\orm\Mapping;

/**
 * A sellable item in the marketplace catalog: a single module or a bundled
 * collection of modules. `modules` lists the module names granted by owning this
 * product (for type=module it's normally just the product's own moduleName; for
 * type=collection it's the bundle).
 *
 * Public catalog: any authenticated user may read active/inactive products
 * (the storefront filters on `active` itself); only module managers may
 * create/update/delete.
 */
class Product extends Entity
{
    const TYPE_MODULE = 'module';
    const TYPE_COLLECTION = 'collection';

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $type = self::TYPE_MODULE;

    /**
     * Module name for type=module.
     *
     * @var string|null
     */
    public $moduleName;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $stripePriceId;

    /**
     * @var float|null
     */
    public $price;

    /**
     * @var string
     */
    public $currency = 'EUR';

    /**
     * @var bool
     */
    public $active = true;

    /**
     * Optional availability window end. After this datetime the product is no
     * longer offered to NEW customers — hidden from the catalog and its download
     * refused — while existing owners (with an entitlement) keep access. Null =
     * always available while `active`.
     *
     * @var ?\go\core\util\DateTime
     */
    public $availableUntil;

    /**
     * @var int
     */
    public $sortOrder = 0;

    /**
     * Member module names for type=collection.
     *
     * @var array<string>
     */
    public $modules = [];

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
     * Logo/icon image for this product (references core_blob). Shown in the
     * client catalog card; served publicly via Module::pageProductLogo().
     *
     * @var string|null
     */
    public $logoBlobId;

    public static function getClientName(): string
    {
        return 'MarketplaceServerProduct';
    }

    /**
     * Whether the product is currently offered to NEW customers: active and
     * within its availability window. Existing owners bypass this (their
     * entitlement already grants access).
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        // availableUntil is edited as a DATE (stored at midnight); treat it as
        // inclusive through the END of that day so "available until Jul 20" keeps
        // the product available for all of Jul 20 (+86400 = start of the next day).
        return $this->active
            && ($this->availableUntil === null
                || time() < $this->availableUntil->getTimestamp() + 86400);
    }

    /**
     * @return bool
     */
    protected function internalSave(): bool
    {
        if (!parent::internalSave()) {
            return false;
        }
        // Keep the referenced logo blob out of garbage collection.
        if (!empty($this->logoBlobId)) {
            $blob = \go\core\fs\Blob::findById($this->logoBlobId);
            if ($blob && isset($blob->staleAt)) {
                $blob->staleAt = null;
                $blob->save();
            }
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
            ->addTable('marketplaceserver_product', 'p')
            ->addScalar('modules', 'marketplaceserver_product_module', ['id' => 'productId']);
    }

    /**
     * Managers get full CRUD; any authenticated user may read (public catalog).
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
        if (!empty($module->getUserRights()->mayManage)) {
            return Acl::LEVEL_MANAGE;
        }
        return go()->getUserId() !== null ? Acl::LEVEL_READ : 0;
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
            });
    }

    /**
     * @return array<int,string>
     */
    protected static function textFilterColumns(): array
    {
        return ['p.title', 'p.moduleName'];
    }

    /**
     * Coerce the NOT NULL columns to their schema defaults when the client sends
     * null/empty (an empty numberfield submits null; the DB columns are
     * `sortOrder INT NOT NULL DEFAULT 0` and `currency VARCHAR(3) NOT NULL
     * DEFAULT 'EUR'`, so a null insert violates the constraint). Guards every
     * write path, not just the dialog.
     *
     * @return void
     */
    protected function internalValidate()
    {
        if ($this->sortOrder === null || $this->sortOrder === '') {
            $this->sortOrder = 0;
        }
        if (empty($this->currency)) {
            $this->currency = 'EUR';
        }
        // A module product's moduleName is the folder name the download/license
        // path keys on, and every Release derives its (NOT NULL) moduleName from
        // it — so it is mandatory here. Without this guard a module product could
        // be saved blank and later break release creation with a confusing
        // "moduleName is required" error on the Release instead of the Product.
        if ($this->type === self::TYPE_MODULE && empty($this->moduleName)) {
            $this->setValidationError('moduleName', \go\core\validate\ErrorCode::REQUIRED, 'A module name is required for a module product');
        }

        parent::internalValidate();
    }
}
