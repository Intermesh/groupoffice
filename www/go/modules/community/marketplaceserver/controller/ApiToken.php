<?php

namespace go\modules\community\marketplaceserver\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\marketplaceserver\lib\TokenAuth;
use go\modules\community\marketplaceserver\model;

class ApiToken extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass(): string
    {
        return model\ApiToken::class;
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
     * @return ArrayObject
     * @throws InvalidArguments
     * @throws StateMismatch
     */
    public function set($params)
    {
        return $this->defaultSet($params);
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

    /**
     * Manager-only: issue a fresh API token for an EXISTING customer. Used for
     * offline / invoice-based sales (where the customer didn't self-register) or
     * to re-issue a lost token. Returns the plaintext exactly once; only the
     * hash is stored. (The former self-service `generate` is gone — the server
     * has no customer UI; self-service tokens come from registration.)
     *
     * @param array $params ['customerId' => int, 'name' => string]
     * @return \ArrayObject
     * @throws \go\core\exception\Forbidden
     * @throws \Exception
     */
    public function issue($params)
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }

        $customer = model\Customer::findById((string) ($params['customerId'] ?? ''));
        if (!$customer) {
            throw new \Exception('Customer not found');
        }

        $plain = TokenAuth::generateToken();
        $token = new model\ApiToken();
        $token->customerId = $customer->id;
        $token->name = ($params['name'] ?? '') !== '' ? $params['name'] : 'Admin-issued';
        $token->assignTokenHash(TokenAuth::hash($plain));
        if (!$token->save()) {
            throw new \Exception('Could not save token');
        }

        return new \ArrayObject(['id' => $token->id, 'token' => $plain]);
    }
}
