<?php

namespace go\modules\community\marketplaceserver\controller;

use go\core\jmap\EntityController;
use go\core\jmap\exception\InvalidArguments;
use go\core\jmap\exception\StateMismatch;
use go\core\util\ArrayObject;
use go\modules\community\marketplaceserver\model;

class Customer extends EntityController
{
    /**
     * The class name of the entity this controller is for.
     *
     * @return string
     */
    protected function entityClass(): string
    {
        return model\Customer::class;
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
     * Manager action: enable/disable a customer's account (toggles the linked
     * User.enabled — which the customer API gates on). Enabling also stamps the
     * account verified if it never was, so an admin can activate an account
     * whose owner never clicked the e-mail link.
     *
     * @param array $params {customerId, enabled}
     * @return ArrayObject
     * @throws \Exception
     */
    public function setEnabled($params)
    {
        $this->assertManage();

        $customer = model\Customer::findById((string) ($params['customerId'] ?? ''));
        if (!$customer) {
            throw new \go\core\exception\NotFound();
        }
        $user = \go\core\model\User::findById((int) $customer->userId);
        if (!$user) {
            throw new \go\core\exception\NotFound();
        }

        $enabled = !empty($params['enabled']);
        $user->enabled = $enabled;
        if (!$user->save()) {
            throw new \Exception('Could not update user: ' . $user->getValidationErrorsAsString());
        }
        if ($enabled && $customer->verifiedAt === null) {
            $customer->verifiedAt = new \go\core\util\DateTime();
            $customer->save();
        }

        return new ArrayObject(['success' => true, 'enabled' => $enabled]);
    }

    /**
     * Manager action: re-send the verification e-mail to a customer whose owner
     * never received / lost it.
     *
     * @param array $params {customerId}
     * @return ArrayObject
     * @throws \Exception
     */
    public function resendVerification($params)
    {
        $this->assertManage();

        $customer = model\Customer::findById((string) ($params['customerId'] ?? ''));
        if (!$customer) {
            throw new \go\core\exception\NotFound();
        }
        $user = \go\core\model\User::findById((int) $customer->userId);
        if (!$user) {
            throw new \go\core\exception\NotFound();
        }

        // Only for accounts still awaiting first verification. Re-sending to an
        // already-verified (but admin-disabled) account would hand its owner a
        // link that redeem() deliberately won't honour for reactivation — to
        // re-enable such an account the admin uses "Enable account" instead.
        if ($customer->verifiedAt !== null) {
            return new ArrayObject(['success' => true, 'skipped' => true]);
        }

        \go\modules\community\marketplaceserver\lib\VerificationMailer::send($user);

        return new ArrayObject(['success' => true]);
    }

    /**
     * Gate a manager-only action (admins always pass).
     *
     * @return void
     * @throws \go\core\exception\Forbidden
     */
    private function assertManage(): void
    {
        $module = \go\core\App::get()->getModule('community', 'marketplaceserver');
        if (!$module || empty($module->getUserRights()->mayManage)) {
            throw new \go\core\exception\Forbidden();
        }
    }
}
