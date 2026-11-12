<?php

namespace go\modules\community\marketplace\lib;

/**
 * A non-200 response from a marketplace server. Carries the HTTP status and the
 * server's machine-readable `code` (e.g. "verifyRequired") so the client can
 * branch — the human message is the server's own error text, ready to show.
 */
class ApiException extends \Exception
{
    /**
     * @var int
     */
    public int $status;

    /**
     * Server-supplied machine code (e.g. "verifyRequired", "disabled",
     * "invalidToken"), or null when the response had none.
     *
     * @var ?string
     */
    public ?string $errorCode;

    /**
     * @param string $message
     * @param int $status
     * @param string|null $errorCode
     */
    public function __construct(string $message, int $status = 0, ?string $errorCode = null)
    {
        parent::__construct($message);
        $this->status = $status;
        $this->errorCode = $errorCode;
    }
}
