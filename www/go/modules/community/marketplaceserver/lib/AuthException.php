<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Expected, non-500 login failures. The reason lets the endpoint choose a
 * status + (deliberately generic, non-enumerating) message.
 */
class AuthException extends \Exception
{
    /** Bad e-mail/password (kept generic on purpose — never says which). */
    const INVALID = 1;

    /** Correct credentials, but the account's e-mail was never verified. */
    const NOT_VERIFIED = 2;

    /** Correct credentials, but the account was verified then disabled/suspended. */
    const DISABLED = 3;

    /**
     * @var int
     */
    public int $reason;

    /**
     * @param string $message
     * @param int $reason
     */
    public function __construct(string $message, int $reason = self::INVALID)
    {
        parent::__construct($message);
        $this->reason = $reason;
    }
}
