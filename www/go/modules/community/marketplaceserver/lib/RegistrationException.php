<?php

namespace go\modules\community\marketplaceserver\lib;

/**
 * Raised by {@see Registrar} for expected registration failures (bad input,
 * duplicate account). Carries a machine code so the page endpoint can map it to
 * an HTTP status + a uniform, non-enumerating message.
 */
class RegistrationException extends \Exception
{
    const INVALID = 1;
    const DUPLICATE = 2;
    const DISABLED = 3;

    /**
     * @var int
     */
    public $reason;

    /**
     * @param string $message
     * @param int $reason one of the self::* constants
     */
    public function __construct(string $message, int $reason = self::INVALID)
    {
        parent::__construct($message);
        $this->reason = $reason;
    }
}
