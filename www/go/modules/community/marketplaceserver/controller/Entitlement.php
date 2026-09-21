<?php

namespace go\modules\community\marketplaceserver\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\marketplaceserver\model;

class Entitlement extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass(): string
    {
        return model\Entitlement::class;
    }

    /**
     * @param array<string, mixed> $params
     * @return ArrayObject
     * @throws InvalidArguments
     */
    public function query($params)
    {
        return $this->defaultQuery($params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ArrayObject
     * @throws \Exception
     */
    public function get($params)
    {
        return $this->defaultGet($params);
    }

    /**
     * @param array<string, mixed> $params
     * @return ArrayObject
     * @throws InvalidArguments
     * @throws StateMismatch
     */
    public function set($params)
    {
        return $this->defaultSet($params);
    }

    /**
     * @param array<string, mixed> $params
     * @return array|ArrayObject
     * @throws InvalidArguments
     */
    public function changes($params)
    {
        return $this->defaultChanges($params);
    }
}
