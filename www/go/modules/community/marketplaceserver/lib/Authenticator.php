<?php

namespace go\modules\community\marketplaceserver\lib;

use go\core\auth\TemporaryState;
use go\core\model\User;
use go\modules\community\marketplaceserver\model\ApiToken;
use go\modules\community\marketplaceserver\model\Customer;

/**
 * Password login for an EXISTING marketplace customer. There is no server GO
 * session for customers — "login" means: prove e-mail + password, then mint a
 * fresh API token the client can store (the previous token's plaintext is only
 * ever a hash server-side, so it can't be handed back).
 *
 * Security:
 *   - failures are deliberately generic (never reveal e-mail vs password);
 *   - a dummy hash check runs when the user is unknown, to blunt timing-based
 *     account enumeration;
 *   - the account must be verified + enabled (else a typed AuthException lets
 *     the endpoint answer "verify your e-mail" / "account disabled");
 *   - token creation runs under an elevated system state (like Registrar),
 *     because saving entities from the UNAUTHENTICATED endpoint would otherwise
 *     dereference a null auth state.
 */
class Authenticator
{
    /** A valid bcrypt hash of a random string — compared against when the user is unknown (timing equalisation). */
    const DUMMY_HASH = '$2y$12$RFzPmgxC7hvE5DNIaMDQ4ekQO/G/PfeTfiwqGrhADhaw4BF0f3mhm';

    /**
     * @param string $email e-mail or username
     * @param string $password plaintext
     * @return array{token: string, customer: \go\modules\community\marketplaceserver\model\Customer, user: \go\core\model\User}
     * @throws \go\modules\community\marketplaceserver\lib\AuthException expected login failures
     * @throws \Throwable unexpected failure
     */
    public static function login(string $email, string $password): array
    {
        $email = trim($email);
        if ($email === '' || $password === '') {
            throw new AuthException('Invalid login credentials', AuthException::INVALID);
        }

        // Load the password hash explicitly (protected, not fetched by default).
        $user = User::find(['id', 'username', 'email', 'displayName', 'enabled', 'password'])
            ->where(['email' => $email])
            ->orWhere(['username' => $email])
            ->single();

        // Verify against the stored hash DIRECTLY. Never route through the core
        // session-login stack (User::checkPassword → Authenticate::passwordLogin):
        // that would (a) authenticate EVERY GO account, not just customers,
        // (b) bypass 2FA, and (c) write fail2ban failure lines keyed on a
        // spoofable client IP. Dummy-verify unknown users to blunt timing
        // enumeration.
        $passwordOk = $user
            ? $user->passwordVerify($password)
            : password_verify($password, self::DUMMY_HASH);

        // Customer is ACL-scoped and the token save needs a state → elevate.
        $previousState = go()->getAuthState();
        go()->setAuthState((new TemporaryState())->setUserId(1));
        try {
            // Require an EXISTING marketplace customer. Never authenticate an
            // arbitrary GO user, and never find-or-create one here — otherwise
            // /login becomes a credential oracle for the whole instance and mints
            // tokens (and Customer rows) for staff/admins.
            $customer = $user
                ? Customer::find()->where(['userId' => (int) $user->id])->single()
                : null;

            if (!$user || !$passwordOk || !$customer) {
                throw new AuthException('Invalid login credentials', AuthException::INVALID);
            }

            if (!$user->enabled) {
                if ($customer->verifiedAt !== null) {
                    throw new AuthException('This account has been disabled. Please contact support.', AuthException::DISABLED);
                }
                throw new AuthException('Your account is not verified yet. Please verify your e-mail.', AuthException::NOT_VERIFIED);
            }

            $plain = TokenAuth::generateToken();
            $token = new ApiToken();
            $token->customerId = (int) $customer->id;
            $token->name = 'Login';
            $token->assignTokenHash(TokenAuth::hash($plain));
            if (!$token->save()) {
                throw new \Exception('Could not issue token: ' . $token->getValidationErrorsAsString());
            }

            // Keep the per-customer token set from growing without bound: every
            // login issues a new token, so drop revoked + long-idle ones now.
            ApiToken::pruneStale((int) $customer->id);

            return ['token' => $plain, 'customer' => $customer, 'user' => $user];
        } finally {
            // Always drop back — restore the prior state, or a userless guest
            // state, so admin elevation never leaks into later request code.
            go()->setAuthState($previousState ?? new TemporaryState());
        }
    }
}
