<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown when too manu DNS IP lookups have been performed.
 */
class TooManyDNSVoidLookupsException extends Exception
{
    /**
     * The maximum number of DNS IP lookups that returned zero addresses.
     *
     * @var int
     */
    private $maxVoidDnsLookups;

    /**
     * Initialize the instance.
     */
    public function __construct(int $maxVoidDnsLookups)
    {
        parent::__construct("Too many DNS IP lookups that returned zero addresses have been performed (max limit is {$maxVoidDnsLookups})");
        $this->maxVoidDnsLookups = $maxVoidDnsLookups;
    }

    /**
     * Get maximum number of DNS IP lookups that returned zero addresses.
     */
    public function getMaxVoidDnsLookups(): int
    {
        return $this->maxVoidDnsLookups;
    }
}
