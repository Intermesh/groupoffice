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
        if (!model\Customer::isCustomerAccount($user)) {
            // A customer row can point at any account; the switch must never
            // reach a staff or admin login.
            throw new \go\core\exception\Forbidden('Only marketplace customer accounts can be enabled or disabled here.');
        }

        $enabled = !empty($params['enabled']);
        $user->enabled = $enabled;
        if (!$user->save()) {
            throw new \Exception('Could not update user: ' . $user->getValidationErrorsAsString());
        }
        if ($enabled && $customer->verifiedAt === null) {
            $customer->verifiedAt = new \go\core\util\DateTime();
            if (!$customer->save()) {
                // The account IS enabled by now (saved above), but the grid
                // optimistically shows verifiedAt from this response — reporting
                // success here would leave the UI claiming a stamp the row never got.
                throw new \Exception('Could not update customer: ' . $customer->getValidationErrorsAsString());
            }
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

        $sent = \go\modules\community\marketplaceserver\lib\VerificationMailer::send($user);
        if ($sent === null) {
            // Not a customer account awaiting verification: nothing was sent.
            return new ArrayObject(['success' => true, 'skipped' => true]);
        }
        if ($sent === false) {
            // Mail delivery failed. The manager is standing in front of this
            // button — say so instead of reporting a send that did not happen.
            throw new \Exception(go()->t(
                'The verification e-mail could not be sent. Check the e-mail settings and the server log.',
                'community',
                'marketplaceserver'
            ));
        }

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
