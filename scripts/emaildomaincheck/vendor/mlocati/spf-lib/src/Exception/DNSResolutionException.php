<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown by the DNS resolver when it can't retrieve the DNS TXT records for a domain.
 */
class DNSResolutionException extends Exception
{
    /**
     * The domain name for which we couldn't retrieve the TXT records.
     *
     * @var string
     */
    private $domain;

    /**
     * Initialize the instance.
     *
     * @param string $domain the domain name for which we couldn't retrieve the TXT records
     * @param string $message the error description
     */
    public function __construct(string $domain, string $message)
    {
        parent::__construct($message);
        $this->domain = $domain;
    }

    /**
     * Get the domain name for which we couldn't retrieve the TXT records.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }
}
