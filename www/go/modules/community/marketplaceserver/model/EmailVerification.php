<?php

namespace go\modules\community\marketplaceserver\model;

use go\core\auth\TemporaryState;
use go\core\jmap\Entity;
use go\core\model\User;
use go\core\orm\Mapping;
use go\core\util\DateTime;
use go\modules\community\marketplaceserver\lib\TokenAuth;

/**
 * A single-use, hashed, expiring e-mail verification token. Internal only —
 * never exposed over JMAP (no controller, not registered in Module.js). The
 * plaintext leaves the server exactly once, inside the verification link; only
 * its SHA-256 hash is stored.
 */
class EmailVerification extends Entity
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $userId;

    /**
     * @var string
     */
    public $tokenHash;

    /**
     * @var \go\core\util\DateTime
     */
    public $expiresAt;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $usedAt;

    /**
     * @var ?\go\core\util\DateTime
     */
    public $createdAt;

    /**
     * @return \go\core\orm\Mapping
     * @throws \ReflectionException
     */
    protected static function defineMapping(): Mapping
    {
        return parent::defineMapping()
            ->addTable('marketplaceserver_verification', 'v');
    }

    /**
     * Issue a fresh verification token for a user and return the plaintext (for
     * the e-mail link). Any prior unused tokens for the user are invalidated so
     * only the newest link works.
     *
     * @param int $userId
     * @param int $ttlHours
     * @return string the plaintext token (store nowhere; email it)
     * @throws \Exception
     */
    public static function issue(int $userId, int $ttlHours = 48): string
    {
        // issue() runs from the UNAUTHENTICATED register endpoint (fresh + duplicate
        // re-send paths). Saving entities under a null auth state trips core code
        // that dereferences the current user, so run under an elevated system state.
        return self::withSystemState(function () use ($userId, $ttlHours) {
            // invalidate previous outstanding tokens for this user
            foreach (self::find()->where(['userId' => $userId, 'usedAt' => null]) as $old) {
                $old->usedAt = new DateTime();
                $old->save();
            }

            $plain = TokenAuth::generateToken();
            $v = new self();
            $v->userId = $userId;
            $v->tokenHash = TokenAuth::hash($plain);
            $expires = new DateTime();
            $expires->add(new \DateInterval('PT' . ($ttlHours * 60) . 'M'));
            $v->expiresAt = $expires;
            if (!$v->save()) {
                throw new \Exception('Could not issue verification token: ' . $v->getValidationErrorsAsString());
            }
            return $plain;
        });
    }

    /**
     * Run a callable under an elevated system (admin) auth state, restoring the
     * previous state afterwards. Required because these token operations are
     * triggered by UNAUTHENTICATED public endpoints (register / verify), where
     * saving a User or ACL-scoped read would otherwise dereference a null auth
     * state. A public request has no prior state and setAuthState() cannot take
     * null, so we only restore a real prior state (the request ends right after).
     *
     * @param callable $fn
     * @return mixed
     */
    private static function withSystemState(callable $fn)
    {
        $previousState = go()->getAuthState();
        go()->setAuthState((new TemporaryState())->setUserId(1));
        try {
            return $fn();
        } finally {
            // Restore the prior state, or a userless guest state, so the admin
            // elevation never leaks into later request code.
            go()->setAuthState($previousState ?? new TemporaryState());
        }
    }

    /**
     * Redeem a plaintext token: if valid + unused + unexpired, mark it used,
     * enable the user, and return the user. Returns null on any failure (no
     * information about which check failed, to avoid enumeration).
     *
     * @param string $plain
     * @return \go\core\model\User|null the now-enabled user, or null
     * @throws \Exception
     */
    public static function redeem(string $plain): ?User
    {
        if ($plain === '') {
            return null;
        }
        // Runs from the UNAUTHENTICATED verify endpoint (the e-mail link). Elevate:
        // User::findById is ACL-scoped (would hide the row under a null auth state)
        // and enabling+saving the User trips the same current-user dereference.
        return self::withSystemState(function () use ($plain) {
            $v = self::find()->where(['tokenHash' => TokenAuth::hash($plain), 'usedAt' => null])->single();
            if (!$v) {
                return null;
            }
            if ($v->expiresAt->getTimestamp() < (new DateTime())->getTimestamp()) {
                return null;
            }

            $user = User::findById((int) $v->userId);
            if (!$user) {
                return null;
            }

            $v->usedAt = new DateTime();
            $v->save();

            $customer = Customer::find()->where(['userId' => (int) $user->id])->single();
            $firstVerification = !$customer || $customer->verifiedAt === null;

            // Auto-enable ONLY on the first verification. A previously-verified
            // account that an admin later disabled must NOT be reactivated by
            // re-verifying (that would be a disable-bypass).
            if (!$user->enabled && $firstVerification) {
                $user->enabled = true;
                if (!$user->save()) {
                    throw new \Exception('Could not enable user: ' . $user->getValidationErrorsAsString());
                }
            }

            // Stamp the account as verified (first time only) so the API can tell
            // "never verified" apart from "verified but later disabled".
            if ($customer && $customer->verifiedAt === null) {
                $customer->verifiedAt = new DateTime();
                $customer->save();
            }

            return $user;
        });
    }
}
