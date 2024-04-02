<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown when too many DNS queries have been performed.
 */
class TooManyDNSLookupsException extends Exception
{
    /**
     * The maximum number of DNS queries that can be performed.
     *
     * @var int
     */
    private $maxDnsLookups;

    /**
     * Initialize the instance.
     */
    public function __construct(int $maxDnsLookups)
    {
        parent::__construct("Too many DNS lookups have been performed (max limit is {$maxDnsLookups})");
        $this->maxDnsLookups = $maxDnsLookups;
    }

    /**
     * Get the maximum number of DNS queries that can be performed.
     */
    public function getMaxDnsLookups(): int
    {
        return $this->maxDnsLookups;
    }
}
