<?php

namespace go\modules\community\marketplace\model;

use go\core\orm\Property;
use go\core\orm\Mapping;

class RepositoryModule extends Property
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $repositoryId;

    /**
     * @var string
     */
    public $moduleName;

    /**
     * @var string
     */
    public $version;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $downloadedAt;

    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()->addTable('marketplace_repository_module');
    }
}
