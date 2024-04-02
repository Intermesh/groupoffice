<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown when a value doesn't represent a valid IPv4/IPv6 address.
 */
class InvalidIPAddressException extends Exception
{
    /**
     * The the wrong IP address.
     *
     * @var string|mixed
     */
    private $wrongIPAddress;

    /**
     * Initialize the instance.
     *
     * @param string|mixed $wrongIPAddress the wrong IP address
     */
    public function __construct($wrongIPAddress)
    {
        $type = gettype($wrongIPAddress);
        parent::__construct($type === 'string' ? "'{$wrongIPAddress}' is not a valid IPv4/IPv6 address" : "Expected a string to represent an address, received a {$type}");
        $this->wrongIPAddress = $wrongIPAddress;
    }

    /**
     * Get the wrong IP address.
     */
    public function getWrongIPAddress(): string
    {
        return $this->wrongIPAddress;
    }
}
