<?php

declare(strict_types=1);

namespace SPFLib\Exception;

use SPFLib\Exception;

/**
 * Exception thrown when more that one SPF record has been found.
 */
class MultipleSPFRecordsException extends Exception
{
    /**
     * The name of the domain that has multiple SPF records.
     *
     * @var string
     */
    private $domain;

    /**
     * The multiple SPF records found.
     *
     * @var string[]
     */
    private $records;

    /**
     * Initialize the instance.
     *
     * @param string $domain the name of the domain that has multiple SPF records
     * @param string[] $records the multiple SPF records found
     */
    public function __construct(string $domain, array $records)
    {
        parent::__construct("The domain {$domain} has more that one SPF record.");
        $this->domain = $domain;
        $this->records = $records;
    }

    /**
     * Get the name of the domain that has multiple SPF records.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Get the multiple SPF records found.
     *
     * @var string[]
     */
    public function getRecords(): array
    {
        return $this->records;
    }
}
