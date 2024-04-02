<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;
use SPFLib\Macro\MacroString\Chunk\Placeholder;

/**
 * Exception thrown by the expansion of a macro string when it requires an environment value that isn't set.
 */
class MissingEnvironmentValueException extends Exception
{
    /**
     * The value of one Placeholder::ML_... constants that identifies which environment value is missing (case-insensitive).
     *
     * @var string
     */
    private $environmentValueIdentifier;

    /**
     * Initialize the instance.
     *
     * @param string $environmentValueIdentifier the value of one Placeholder::ML_... constants that identifies which environment value is missing (case-insensitive)
     */
    public function __construct(string $environmentValueIdentifier)
    {
        switch (strtolower($environmentValueIdentifier)) {
            case Placeholder::ML_SENDER:
                $message = 'Missing the email address used in "MAIL FROM" or "HELO"';
                break;
            case Placeholder::ML_SENDER_LOCAL_PART:
                $message = 'Missing the local-part of the email address used in "MAIL FROM" or "HELO"';
                break;
            case Placeholder::ML_SENDER_DOMAIN:
                $message = 'Missing the domain of the email address used in "MAIL FROM" or "HELO"';
                break;
            case Placeholder::ML_DOMAIN:
                $message = 'Missing the domain that contains the current SPF record';
                break;
            case Placeholder::ML_IP:
            case Placeholder::ML_IP_TYPE:
            case Placeholder::ML_SMTP_CLIENT_IP:
                $message = 'Missing the IP address of the SMTP client that is emitting the mail';
                break;
            case Placeholder::ML_IP_VALIDATED_DOMAIN:
                $message = 'Missing the validated domain name of the IP address of the SMTP client that is emitting the mail (do not use)';
                break;
            case Placeholder::ML_HELO_DOMAIN:
                $message = 'Missing the HELO/EHLO domain';
                break;
            case Placeholder::ML_CHECKER_DOMAIN:
                $message = 'Mssing the domain name of host performing the check';
                break;
            default:
                $message = "Missing unrecognized environment value ('{$environmentValueIdentifier}')";
                break;
        }
        parent::__construct($message);
        $this->environmentValueIdentifier = $environmentValueIdentifier;
    }

    /**
     * Get the value of one Placeholder::ML_... constants that identifies which environment value is missing (case-insensitive).
     */
    public function getEnvironmentValueIdentifier(): string
    {
        return $this->environmentValueIdentifier;
    }
}
