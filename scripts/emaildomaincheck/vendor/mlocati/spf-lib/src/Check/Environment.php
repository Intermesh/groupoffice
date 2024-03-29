<?php

declare(strict_types=1);

namespace SPFLib\Check;

use IPLib\Address\AddressInterface;
use IPLib\Factory;
use SPFLib\Exception\InvalidIPAddressException;

/**
 * Class that holds the environment values to be used for the check.
 */
class Environment
{
    /**
     * The value to be used for the checker domain.
     *
     * @var string
     */
    public const UNKNOWN_CHECKER_DOMAIN = 'unknown';

    /**
     * The IP address of the SMTP client that is emitting the email.
     *
     * @var \IPLib\Address\AddressInterface|null
     */
    private $clientIP;

    /**
     * The domain name that was provided to the SMTP server via the HELO or EHLO SMTP verb.
     *
     * @var string
     */
    private $heloDomain;

    /**
     * The email address specified in the "MAIL FROM" MTA command.
     *
     * @var string
     */
    private $mailFrom;

    /**
     * The name of the receiving MTA.
     * This SHOULD be a fully qualified domain name, but if one does not exist (as when the checking is done by a Mail User Agent (MUA))
     * or if policy restrictions dictate otherwise, the word "unknown" SHOULD be substituted.
     * The domain name can be different from the name found in the MX record that the client MTA used to locate the receiving MTA.
     *
     * @var string
     */
    private $checkerDomain;

    /**
     * Initialize the instance.
     *
     * @param \IPLib\Address\AddressInterface|string|null $clientIP the IP address of the SMTP client that is emitting the email
     * @param string $heloDomain the domain specified in the "HELO" (or "EHLO") MTA command (if NULL we'll use the domain of $mailFrom)
     * @param string $mailFrom email the address specified in the "MAIL FROM" MTA command
     * @param string $checkerDomain the fully qualified name of the host checking the SPF record
     *
     * @throws \SPFLib\Exception\InvalidIPAddressException if $clientIP is not empty and doesn't represent a valid IP address
     */
    public function __construct($clientIP, string $heloDomain, string $mailFrom = '', string $checkerDomain = self::UNKNOWN_CHECKER_DOMAIN)
    {
        if ($clientIP === null || $clientIP === '') {
            $this->clientIP = null;
        } elseif ($clientIP instanceof AddressInterface) {
            $this->clientIP = $clientIP;
        } else {
            $address = Factory::addressFromString($clientIP);
            if ($address === null) {
                throw new InvalidIPAddressException($clientIP);
            }
            $this->clientIP = $address;
        }
        $this->heloDomain = $heloDomain;
        $this->mailFrom = $mailFrom;
        $this->checkerDomain = $checkerDomain;
    }

    /**
     * Get the IP address of the SMTP client that is emitting the email.
     */
    public function getClientIP(): ?AddressInterface
    {
        return $this->clientIP;
    }

    /**
     * Get the domain name that was provided to the SMTP server via the HELO or EHLO SMTP verb.
     */
    public function getHeloDomain(): string
    {
        return $this->heloDomain;
    }

    /**
     * Get the email address specified in the "MAIL FROM" MTA command.
     */
    public function getMailFrom(): string
    {
        return $this->mailFrom;
    }

    /**
     * Get the domain after the '@' character of the email address specified in the "MAIL FROM" MTA command.
     */
    public function getMailFromDomain(): string
    {
        $mailFrom = $this->getMailFrom();
        $atPosition = strpos($mailFrom, '@');

        return $atPosition === false ? '' : substr($mailFrom, $atPosition + 1);
    }

    /**
     * Get the name of the receiving MTA.
     */
    public function getCheckerDomain(): string
    {
        return $this->checkerDomain;
    }
}
