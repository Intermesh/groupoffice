<?php

namespace go\modules\community\marketplaceserver\lib;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\marketplaceserver\Module;
use go\modules\community\marketplaceserver\model\ApiToken;
use go\modules\community\marketplaceserver\model\Customer;

/**
 * Provisions a marketplace customer account in one atomic step:
 * User (locked) + profile Contact (created by addressbook on user save) +
 * Customer + first API token. Pure provisioning — no HTTP, no rate-limiting,
 * no e-mail (those live in the page endpoint and {@see Verification}).
 *
 * Security-critical: this is public-facing user creation. Rules enforced here:
 *   - the new User is created DISABLED (cannot log in until e-mail verified);
 *   - it is placed ONLY in the locked "Marketplace Customers" group, which has
 *     zero access to this admin module — caller-supplied groups/rights are never
 *     consulted;
 *   - the whole thing is one transaction: any failure rolls everything back.
 */
class Registrar
{
    /**
     * @param string $email
     * @param string $name display name (falls back to the e-mail)
     * @param string $password plaintext; hashed by core User
     * @param string|null $companyName denormalised onto the Customer
     * @return array{customer: \go\modules\community\marketplaceserver\model\Customer, user: \go\core\model\User, token: string}
     * @throws \go\modules\community\marketplaceserver\lib\RegistrationException expected input/duplicate failures
     * @throws \Throwable unexpected failure (already rolled back)
     */
    public static function register(string $email, string $name, string $password, ?string $companyName = null): array
    {
        $email = trim($email);
        $name = trim($name);
        $companyName = $companyName !== null ? trim($companyName) : null;

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            throw new RegistrationException('A valid e-mail address is required', RegistrationException::INVALID);
        }
        if (strlen($password) < 10) {
            throw new RegistrationException('Password must be at least 10 characters', RegistrationException::INVALID);
        }
        if (mb_strtolower($password) === mb_strtolower($email)) {
            throw new RegistrationException('Password must not be the same as your e-mail address', RegistrationException::INVALID);
        }
        if ($name === '') {
            $name = $email;
        }

        // Duplicate check (by e-mail or username). The endpoint maps DUPLICATE to
        // a uniform "check your e-mail" response so account existence never leaks.
        $existing = User::find()
            ->where(['email' => $email])
            ->orWhere(['username' => $email])
            ->single();
        if ($existing) {
            throw new RegistrationException('An account with this e-mail already exists', RegistrationException::DUPLICATE);
        }

        $groupId = Module::ensureCustomerGroup();

        // Public registration runs UNAUTHENTICATED, but saving a User triggers core
        // validation (User::validatePasswordChange → Module::getUserRights) that
        // dereferences the current auth state — which is null here. Provision under
        // an elevated system (admin) state, exactly as core does for system
        // operations (see go/core/Installer.php). This only lets the framework save
        // succeed; WHAT gets created stays constrained by this Registrar (a locked
        // user in the locked group only). Restore the prior state afterwards (a
        // public request has none, and setAuthState() cannot take null, so we only
        // restore when there was a real prior state — the request ends right after).
        $previousState = go()->getAuthState();
        go()->setAuthState((new TemporaryState())->setUserId(1));

        $db = go()->getDbConnection();
        $db->beginTransaction();
        try {
            $user = new User();
            $user->username = $email;
            $user->email = $email;
            $user->recoveryEmail = $email;
            $user->displayName = $name;
            $user->enabled = false;              // locked until e-mail verified
            $user->setPassword($password);       // core hashing — never store plain
            $user->addGroup($groupId);           // locked group ONLY; ignore any caller groups
            if (!$user->save()) {
                throw new RegistrationException('Could not create user: ' . $user->getValidationErrorsAsString());
            }
            // (addressbook created the user's profile Contact during save(); the
            // company is kept denormalised on the Customer — linking a full
            // organisation Contact is deferred to the billing phase.)

            $customer = new Customer();
            $customer->userId = (int) $user->id;
            $customer->companyName = $companyName !== '' ? $companyName : null;
            if (!$customer->save()) {
                throw new RegistrationException('Could not create customer: ' . $customer->getValidationErrorsAsString());
            }

            $plain = TokenAuth::generateToken();
            $token = new ApiToken();
            $token->customerId = (int) $customer->id;
            $token->name = 'Registration';
            $token->assignTokenHash(TokenAuth::hash($plain));
            if (!$token->save()) {
                throw new RegistrationException('Could not create token: ' . $token->getValidationErrorsAsString());
            }

            $db->commit();

            return ['customer' => $customer, 'user' => $user, 'token' => $plain];
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        } finally {
            // Restore the prior state, or a userless guest state, so the admin
            // elevation never leaks into later request code.
            go()->setAuthState($previousState ?? new TemporaryState());
        }
    }
}
