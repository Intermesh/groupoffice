<?php

namespace go\modules\community\marketplaceserver\lib;

use go\core\ErrorHandler;
use go\core\model\User;
use go\modules\community\marketplaceserver\model\EmailVerification;

/**
 * Single place that issues a verification token and e-mails the link. Used by
 * self-registration, the re-send endpoint, and the admin "resend" action, so the
 * link format + copy live in one spot. Best-effort: a mail failure is logged, not
 * thrown (the account exists; verification can always be re-requested).
 */
class VerificationMailer
{
    /**
     * Issue a fresh verification token for the user and e-mail the link.
     *
     * @param \go\core\model\User $user
     * @return void
     */
    public static function send(User $user): void
    {
        $plain = EmailVerification::issue((int) $user->id);
        $base = rtrim((string) (go()->getSettings()->URL ?? ''), '/');
        $url = $base . '/api/page.php/community/marketplaceserver/verify?token=' . urlencode($plain);

        try {
            go()->getMailer()->compose()
                ->setTo($user->email, $user->displayName)
                ->setSubject('Verify your marketplace account')
                ->setBody(
                    "Welcome to the marketplace!\n\n" .
                    "Please verify your account by opening this link:\n" . $url . "\n\n" .
                    "The link expires in 48 hours.\n"
                )
                ->send();
        } catch (\Throwable $e) {
            ErrorHandler::logException($e);
        }
    }
}
