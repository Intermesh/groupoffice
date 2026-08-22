<?php

namespace go\modules\community\marketplaceserver\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\util\ArrayObject;
use go\modules\community\marketplaceserver\model;

/**
 * Read-only JMAP controller for the activity/audit log. There is intentionally no
 * set() — rows are written only by the system through {@see model\Activity::record()}.
 */
class Activity extends EntityController
{
    /**
     * @return string
     */
    protected function entityClass(): string
    {
        return model\Activity::class;
    }

    /**
     * @param $params
     * @return ArrayObject
     * @throws InvalidArguments
     */
    public function query($params)
    {
        return $this->defaultQuery($params);
    }

    /**
     * @param $params
     * @return ArrayObject
     * @throws \Exception
     */
    public function get($params)
    {
        return $this->defaultGet($params);
    }

    /**
     * @param $params
     * @return array|ArrayObject
     * @throws InvalidArguments
     */
    public function changes($params)
    {
        return $this->defaultChanges($params);
    }
}
